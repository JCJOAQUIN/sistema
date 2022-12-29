@section('data')
	@php
		$taxes = $retentions = $totalPagado = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable	=
		[
			["Folio:",						$request->folio],
			["Título y fecha:",				$request->purchaseRecord->title." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseRecord->datetitle)->format('d-m-Y')],
			["Número de Orden:",			$request->purchaseRecord->numberOrder!="" ? $request->purchaseRecord->numberOrder : '---' ],
			["Fiscal:",						$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",				$request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			["Elaborado por:",				$request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
			["Empresa:",					$request->requestEnterprise->name],
			["Dirección:",					$request->requestDirection->name],
			["Departamento:",				$request->requestDepartment->name],
			["Clasificación del gasto:",	$request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"],
			["Proyecto:",					$request->requestProject->proyectName],
			["Proveedor:",					$request->purchaseRecord->provider],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles del Registro de Compra
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PEDIDO"]) @endcomponent
	@php
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$countConcept	=	1;
		$modelHead		=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Unidad"],
				["value"	=>	"Descripción"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"]
			]
		];
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$taxesConcept = $detail->taxes()->sum('amount');
			$retentionConcept = $detail->retentions()->sum('amount');
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept],
				],
				[
					"content"	=>	["label"	=>	$detail->quantity],
				],
				[
					"content"	=>	["label"	=>	$detail->unit],
				],
				[
					"content"	=>	["label"	=>	$detail->description],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->total,2)],
				],
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
	@php
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"subtotal\"",	"label"	=>	"$ ".number_format($request->purchaseRecord->subtotal,2)]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountAA\"",	"label"	=>	"$ ".number_format($request->purchaseRecord->amount_taxes,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$ ".number_format($request->purchaseRecord->amount_retention,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totaliva\"",	"label"	=>	"$ ".number_format($request->purchaseRecord->tax,2)]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"total\" id=\"input-extrasmall\"",	"label"	=>	"$ ".number_format($request->purchaseRecord->total,2)]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			name="note" readonly="readonly"
		@endslot
		@slot('textNotes')
			{{ $request->purchaseRecord->notes }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Empresa"						=>	$request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '',
			"Cuenta"						=>	$request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '',
			"Referencia/Número de factura"	=>	isset($request->purchaseRecord->reference) ? $request->purchaseRecord->reference : "---",
			"Tipo de moneda"				=>	isset($request->purchaseRecord->typeCurrency) ? $request->purchaseRecord->typeCurrency : "---",
			"Fecha de pago"					=>	$request->PaymentDate!='' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y') : "---",
			"Forma de pago"					=>	isset($request->purchaseRecord->paymentMethod) ? $request->purchaseRecord->paymentMethod : "---",
			"Estatus de factura"			=>	isset($request->purchaseRecord->billStatus) ? $request->purchaseRecord->billStatus : "---",
			"Importe a pagar"				=>	"$ ".number_format($request->purchaseRecord->total,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Responsable"],
				["value"	=>	"Nombre en Tarjeta"],
				["value"	=>	"Número de Tarjeta"],
				["value"	=>	"Status"],
				["value"	=>	"Principal/Adicional"]
			]
		];
		if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		{
			$t			=	App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user		=	App\User::find($t->assignment);
			$status		=	$principal	=	'';
			$classRow	=	"";
			switch ($t->status) 
			{
				case 1:
					$status = 'Vigente';
					break;
				case 2:
					$status = 'Bloqueada';
					break;
				case 3:
					$status = 'Cancelada';
					break;
				default:
					break;
			}
			switch ($t->principal_aditional) 
			{
				case 1:
					$principal = 'Principal';
					break;
				case 2:
					$principal = 'Adicional';
					break;
				default:
					break;
			}
			if ($t->idcreditCard == $request->purchaseRecord->idcreditCard)
			{
				$classRow	=	"marktr";
			}
			$body	=
			[
				"classEx"	=>	$classRow,
				[
					"content"	=>	["label"	=>	$user->name." ".$user->last_name." ".$user->scnd_last_name],
				],
				[
					"content"	=>	["label"	=>	$t->name_credit_card],
				],
				[
					"content"	=>	["label"	=>	$t->credit_card],
				],
				[
					"content"	=>	["label"	=>	$status],
				],
				[
					"content"	=>	["label"	=>	$principal],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id=view-credit-cards
			@if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial") style="display: block;" @else style="display: none;" @endif
		@endslot
		@slot('attributeExBody')
			id=body-credit-cards
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->purchaseRecord->documents)>0)
		{
			$modelHead	=	["Documentos", "Fecha"];
			foreach($request->purchaseRecord->documents as $doc)
			{
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/purchase-record/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')]
						]
					],
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documentos"];
			$body		=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$reviewAccount	=	App\Account::find($request->accountR);
		$modelTable		=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "No hay",
			"Nombre del Proyecto"		=>	$request->reviewedProject->proyectName,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios " : $request->checkComment,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS ASIGNADAS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$labelDescription	=	"";
			if ($detail->labels()->exists())
			{
				$counter	=	0;
				foreach ($detail->labels as $label)
				{
					$counter++;
					$labelDescription	.=	$label->label->description.($counter<count($detail->labels) ? ", " : "");
				}
			}
			else
			{
				$labelDescription	=	"---";
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$detail->quantity." ".$detail->unit],
				],
				[
					"content"	=>	["label"	=>	$detail->description],
				],
				[
					"content"	=>	["label"	=>	$labelDescription],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : $request->authorizeComment,
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$total = $request->purchaseRecord->total;
	@endphp
	@if($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"]
				]
			];
			foreach($request->paymentsRequest as $pay)
			{
				$totalPagado += $pay->amount;
				$componentExtButton[]	=
				[
					"kind"			=>	"components.buttons.button",
					"variant"		=>	"dark-red",
					"label"			=>	"PDF",
					"buttonElement"	=>	"a",
					"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""." title=\"".$doc->path."\""
				];
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"],
					],
					[
						"content"	=>	["label"	=>	"$ ".$totalPagado],
					],
					[
						"content"	=>	$componentExtButton,
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4 table"]) @endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@endcomponent
	@endif
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$(function()
		{
			$('.datepicker').datepicker(
			{
				dateFormat : 'dd-mm-yy',
			});
		});
	});
</script>
@endsection
