@section('data')
	@php 
		$taxes		=	0;
		$retentions	=	0;
		$user		=	App\User::find($request->idRequest);
		$enterprise	=	App\Enterprise::find($request->idEnterprise);
		$area		=	App\Area::find($request->idArea);
		$department	=	App\Department::find($request->idDepartment);
		$account	=	App\Account::find($request->account);
		$state		=	App\State::find($request->purchases->first()->provider->state_idstate);
		$project	=	App\Project::find($request->idProject);
	@endphp
	@if($request->purchases->first()->idRequisition != "")
		@component('components.labels.not-found', ["variant" => "note", "attributeEx" => "error_request", "title" => ""])
			<div>
				@component('components.labels.label', ["label" => "Esta solicitud viene de la requisición # ".$request->purchases->first()->idRequisition]) @endcomponent
				@component('components.labels.label', ["label" => "<span class=\"icon-bullhorn\"></span> FOLIO: ".$request->new_folio]) @endcomponent
				@if ($request->purchases->first()->requisitionRequest->idProject == 75)
					@component('components.labels.label', ["label" => "SUBPROYECTO/CÓDIGO WBS: ".$request->purchases->first()->requisitionRequest->requisition->code_wbs]) @endcomponent
				@endif
			</div>
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable		=
		[
			["Folio:",				$request->folio],
			["Título y fecha:",		htmlentities($request->purchases->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchases->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",	$request->purchases->first()->numberOrder!="" ? htmlentities($request->purchases->first()->numberOrder) : "---" ],
			["Fiscal:",				$request->taxPayment == 0 ? "No" : "Sí"],
			["Solicitante:",		isset($requestUser) && $requestUser->fullname() !="" ? $requestUser->fullname() : "---"],
			["Elaborado por:",		isset($elaborateUser) && $elaborateUser->fullname() !="" ? $elaborateUser->fullname() : "---"]
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable, "classEx" => "mt-4", "title" => "Detalles de la Solicitud"]) @endcomponent
	@if($request->purchases->first()->idRequisition != "")
		@php
			$modelTable	=
			[
				["Folio:",					$request->purchases->first()->idRequisition  !="" ? $request->purchases->first()->idRequisition : "---"],
				["Tipo de requisición:",	$request->purchases->first()->requisitionRequest->requisition->typeRequisition->name !="" ? $request->purchases->first()->requisitionRequest->requisition->typeRequisition->name : "---"],
				["Proyecto:",				$request->purchases->first()->requisitionRequest->requestProject->projectCode!="" ? $request->purchases->first()->requisitionRequest->requestProject->projectCode : "---"],
				["WBS:",					$request->purchases->first()->requisitionRequest->requisition->code_wbs != '' ? $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs : "---"],
				["EDT:",					$request->purchases->first()->requisitionRequest->requisition->code_edt != '' ? $request->purchases->first()->requisitionRequest->requisition->edt->code." ".$request->purchases->first()->requisitionRequest->requisition->edt->description : "---"],
				["Número:",					$request->purchases->first()->requisitionRequest->requisition->number],
			];
		@endphp
		@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "classEx" => "mt-4", "title" => "Detalles de la Requisición"]) @endcomponent
	@endif
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PROVEEDOR"]) @endcomponent
	@php
		$modelTable =
		[
			"Razón Social"	=>	$request->purchases->first()->provider->businessName !="" ? $request->purchases->first()->provider->businessName : "---",
			"RFC"			=>	$request->purchases->first()->provider->rfc !="" ? $request->purchases->first()->provider->rfc : "---",
			"Teléfono"		=>	$request->purchases->first()->provider->phone !="" ? $request->purchases->first()->provider->phone : "---",
			"Calle"			=>	$request->purchases->first()->provider->address !="" ? $request->purchases->first()->provider->address : "---",
			"Número"		=>	$request->purchases->first()->provider->number !="" ? $request->purchases->first()->provider->number : "---"	,
			"Colonia"		=>	$request->purchases->first()->provider->colony !="" ? $request->purchases->first()->provider->colony : "---"	,
			"CP"			=>	$request->purchases->first()->provider->postalCode !="" ? $request->purchases->first()->provider->postalCode : "---",
			"Ciudad"		=>	$request->purchases->first()->provider->city !="" ? $request->purchases->first()->provider->city : "---",
			"Estado"		=>	App\State::find($request->purchases->first()->provider->state_idstate)->description !="" ? App\State::find($request->purchases->first()->provider->state_idstate)->description : "---",
			"Contacto"		=>	$request->purchases->first()->provider->contact !="" ? $request->purchases->first()->provider->contact : "---",
			"Beneficiario"	=>	$request->purchases->first()->provider->beneficiary !="" ? $request->purchases->first()->provider->beneficiary : "---",
			"Otro"			=>	$request->purchases->first()->provider->commentaries !="" ? htmlentities($request->purchases->first()->provider->commentaries) : "---"
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Banco"],
				["value"	=>	"Alias"],
				["value"	=>	"Cuenta"],
				["value"	=>	"Sucursal"],
				["value"	=>	"Referencia"],
				["value"	=>	"CLABE"],
				["value"	=>	"Moneda"],
				["value"	=>	"IBAN"],
				["value"	=>	"BIC/SWIFT"],
				["value"	=>	"Convenio"]
			]
		];
		foreach ($request->purchases->first()->provider->providerData->providerBank as $bank) 
		{
			$classRow	=	$request->purchases->first()->provider_has_banks_id == $bank->id ? "marktr" : "";
			$body	=
			[
				"classEx"	=>	$classRow,
				[
					"content"	=>	["label"	=>	$bank->bank->description !="" ? $bank->bank->description : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->alias !="" ? $bank->alias : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->account !="" ? $bank->account : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->branch !="" ? $bank->branch : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->reference !="" ? $bank->reference : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->clabe !="" ? $bank->clabe : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->currency !="" ? $bank->currency : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->iban!='' ? $bank->iban : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->bic_swift!='' ? $bank->bic_swift : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->agreement!="" ? $bank->agreement : "---"]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table2\""]) @endcomponent
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
				["value"	=>	"Descripción"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Unidad"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"]
			]
		];
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			$taxesConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept	+=	$tax->amount;
			}
			$retentionConcept	=	0;
			foreach ($detail->retentions as $ret)
			{
				$retentionConcept	+=	$ret->amount;
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	$detail->description !="" ? htmlentities($detail->description) : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->quantity !="" ? $detail->quantity : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->unit !="" ? $detail->unit : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->unitPrice !="" ? "$ ".number_format($detail->unitPrice,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->tax !="" ? "$ ".number_format($detail->tax,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$taxesConcept !="" ? "$ ".number_format($taxesConcept,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$retentionConcept !="" ? "$ ".number_format($retentionConcept,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->amount !="" ? "$ ".number_format($detail->amount,2) : "---"]
				],
			];
			$modelBody[] = $body;
			$countConcept++;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\""]) @endcomponent
	@php
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes	+=	$tax->amount;
			}
		}
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			foreach ($detail->retentions as $ret)
			{
				$retentions	+=	$ret->amount;
			}
		}
		$model	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"id=\"subtotalTable\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto dicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountAA\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountR\"",		"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"totaliva\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->tax,2,".",",")]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"total\"",		"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->amount,2,".",",")]]]
		];
	@endphp
	@component('components.templates.outputs.form-details',['modelTable'=>$model])
		@slot('attributeExComment')
			name="note" readonly="readonly"
		@endslot
		@slot('textNotes')
			{{$request->purchases->first()->notes}}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	$request->purchases->first()->reference !="" ? htmlentities($request->purchases->first()->reference) : "---",
			"Tipo de moneda"				=>	$request->purchases->first()->typeCurrency !="" ? $request->purchases->first()->typeCurrency : "---",
			"Fecha de pago"					=>	$request->PaymentDate!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y') : "---",
			"Forma de pago"					=>	$request->purchases->first()->paymentMode !="" ? $request->purchases->first()->paymentMode : "---",
			"Estado de factura"				=>	$request->purchases->first()->billStatus !="" ? $request->purchases->first()->billStatus : "---",
			"Importe a pagar"				=>	$request->purchases->first()->amount !="" ? "$ ".number_format($request->purchases->first()->amount,2) : "---"
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->purchases->first()->documents)>0)
		{
			$modelHead	=	["Tipo de documento", "Documento", "Fecha"];
			foreach ($request->purchases->first()->documents as $doc)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$doc->name],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y')],
					]
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documento"];
			$body	=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$reviewAccount	=	App\Account::find($request->accountR);
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->fullname() != "" ? $request->reviewedUser->fullname() : "---",
			"Nombre de la Empresa"		=>	isset($request->idEnterpriseR) && App\Enterprise::find($request->idEnterpriseR)->name!="" ? App\Enterprise::find($request->idEnterpriseR)->name : "---",
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name !="" ? $request->reviewedDirection->name : "---",
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name !="" ? App\Department::find($request->idDepartamentR)->name : "---",
			"Clasificación del gasto"	=>	isset($reviewAccount->account) && $reviewAccount->account !="" ? $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "No hay",
			"Nombre del Proyecto"		=>	$request->reviewedProject->proyectName !="" ? $request->reviewedProject->proyectName : "---",
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@if($request->idEnterpriseR!="")
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS ASIGNADAS"]) @endcomponent
	@php
		$countConcept		=	1;
		$labelDescription	=	"";
		$modelHead			=	[];
		$body				=	[];
		$modelBody			=	[];
		$modelHead			=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Descripción"],
				["value"	=>	"Etiquetadas"]
			]
		];
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			$labelDescription	=	"";
			if (count($detail->labels))
			{
				foreach ($detail->labels as $label)
				{
					$labelDescription	.=	$label->label->description.", ";
				}
			}
			else
			{
				$labelDescription = "Sin etiqueta";
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	$detail->quantity == "" && $detail->unit == "" ? "---" : $detail->quantity." ".$detail->unit]
				],
				[
					"content"	=>	["label"	=>	$detail->description !="" ? htmlentities($detail->description) : "---"]
				],
				[
					"content"	=>	["label"	=>	$labelDescription]
				]
			];
			$modelBody[]	=	$body;
			$countConcept++;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
	@endif
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment)
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments			=	App\Payment::where('idFolio',$request->folio)->get();
		$subtotal			=	$request->purchases->first()->subtotales;
		$iva				=	$request->purchases->first()->tax;
		$total				=	$request->purchases->first()->amount;
		$tax				=	0;
		$retention			=	0;
		$totalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
		$subtotalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal_real') : 0;
		$taxPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$retentionPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('retention_real') : 0;
		$ivaPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva_real') : 0;
		foreach($request->purchases->first()->detailPurchase as $detail)
		{
			foreach($detail->taxes as $item)
			{
				$tax	+=	$item->amount;
			}
		}
		foreach($request->purchases->first()->detailPurchase as $detail)
		{
			foreach($detail->retentions as $item)
			{
				$retention	+=	$item->amount;
			}
		}
	@endphp
	@if($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead	= 
			[
				[
					["value" => "Cuenta"],
					["value" => "Cantidad"],
					["value" => "Documento"],
					["value" => "Fecha"]
				]
			];
			foreach($request->paymentsRequest as $pay)
			{ 
				$body = 
				[
					[
						"content" => 
						[
							"label" => $pay->accounts->account!="" && $pay->accounts->description!="" && $pay->accounts->content!="" ? $pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")" : "---"
						]
					],
					[
						"content" =>
						[
							"label" => $pay->amount !=""? '$ '.number_format($pay->amount,2) : "---"
						]
					],
				];
				if($pay->documentsPayments()->exists())
				{
					$docsContent = [];
					foreach($pay->documentsPayments as $doc)
					{
						$docsContent['content'][] = 
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "dark-red",
							"buttonElement" => "a",
							"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
							"label"			=> 'PDF'
						];
					}
				}
				else 
				{
					$docsContent['content'] = 
					[
						"label" => "Sin documento"
					];
				}
				$body[] = $docsContent;
				$body[] =  
				[ 
					"content" => 
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$model	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			]
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $model])@endcomponent
	@endif
	@if($request->purchases->first()->partialPayment()->exists())
		@if($request->purchases->first()->partialPayment->where('date_delivery',null)->count()>0)
			<input type="hidden" data-restaSubtotal="{{ round(($subtotal)-$subtotalPagado,2) }}" id="restaSubtotal" value="0">
			<input type="hidden" data-restaIva="{{ round(($iva)-$ivaPagado,2) }}" id="restaIva" value="0">
			<input type="hidden" data-restaTax="{{ round(($tax)-$taxPagado,2) }}" id="restaTax" value="0">
			<input type="hidden" data-restaRetention="{{ round(($retention)-$retentionPagado,2) }}" id="restaRetention" value="0">
			<input type="hidden" data-restaTotal="{{ round(($total)-$totalPagado,2) }}" id="restaTotal" value="0">
		@else
			<input type="hidden" id="restaSubtotal" value="{{ round(($subtotal)-$subtotalPagado,2) }}">
			<input type="hidden" id="restaIva" value="{{ round(($iva)-$ivaPagado,2) }}">
			<input type="hidden" id="restaTax" value="{{ round(($tax)-$taxPagado,2) }}">
			<input type="hidden" id="restaRetention" value="{{ round(($retention)-$retentionPagado,2) }}">
			<input type="hidden" id="restaTotal" value="{{ round(($total)-$totalPagado,2) }}">
		@endif
	@else
		<input type="hidden" id="restaSubtotal"		value="{{ round(($subtotal)-$subtotalPagado,2) }}">
		<input type="hidden" id="restaIva"			value="{{ round(($iva)-$ivaPagado,2) }}">
		<input type="hidden" id="restaTax"			value="{{ round(($tax)-$taxPagado,2) }}">
		<input type="hidden" id="restaRetention"	value="{{ round(($retention)-$retentionPagado,2) }}">
		<input type="hidden" id="restaTotal"		value="{{ round(($total)-$totalPagado,2) }}">
	@endif
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" value=\"".round($total,2)."\"", "classEx" => "amount_purchase"]) @endcomponent
@endsection 
@section('scripts') 
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}"> 
<script src="{{ asset('js/jquery-ui.js') }}"></script> 
<script src="{{ asset('js/jquery.numeric.js') }}"></script> 
<script src="{{ asset('js/datepicker.js') }}"></script> 
<script type="text/javascript">
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
