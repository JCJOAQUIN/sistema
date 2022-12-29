@section('data')
	@php
		$taxes		=	0;
		$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser			=	App\User::find($request->idRequest);
		$elaborateUser			=	App\User::find($request->idElaborate);
		$requestAccountOrigin	=	App\Account::find($request->groups->first()->idAccAccOrigin);
		$requestAccount			=	App\Account::find($request->groups->first()->idAccAccDestiny);
		$modelTable	=
		[
			["Folio:",								$request->folio ,],
			["Título y fecha:",						htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",					$request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---' ,],
			["Fiscal",								$request->taxPayment == 1 ? "Sí" : "No"],
			["Tipo de Operación:",					$request->groups->first()->operationType],
			["Solicitante:",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",						$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen:",						App\Enterprise::find($request->groups->first()->idEnterpriseOrigin)->name],
			["Dirección Origen:",					App\Area::find($request->groups->first()->idAreaOrigin)->name],
			["Departamento Origen:",				App\Department::find($request->groups->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen:",		$requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
			["Proyecto Origen:",					App\Project::find($request->groups->first()->idProjectOrigin)->proyectName ],
			["Empresa Destino:",					App\Enterprise::find($request->groups->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"]
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('title')
			Detalles de la Solicitud de {{ $request->requestkind->kind }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PROVEEDOR"]) @endcomponent
	@php
		$modelTable	=
		[
			"Razón Social"	=>	$request->groups->first()->provider->businessName !="" ? $request->groups->first()->provider->businessName : "---",
			"RFC"			=>	$request->groups->first()->provider->rfc !="" ? $request->groups->first()->provider->rfc : "---",
			"Teléfono"		=>	$request->groups->first()->provider->phone !="" ? $request->groups->first()->provider->phone : "---",
			"Calle"			=>	$request->groups->first()->provider->address !="" ? $request->groups->first()->provider->address : "---",
			"Número"		=>	$request->groups->first()->provider->number !="" ? $request->groups->first()->provider->number : "---",
			"Colonia"		=>	$request->groups->first()->provider->colony !="" ? $request->groups->first()->provider->colony : "---",
			"CP"			=>	$request->groups->first()->provider->postalCode !="" ? $request->groups->first()->provider->postalCode : "---",
			"Ciudad"		=>	$request->groups->first()->provider->city !="" ? $request->groups->first()->provider->city : "---",
			"Estado"		=>	App\State::find($request->groups->first()->provider->state_idstate)->description !="" ? App\State::find($request->groups->first()->provider->state_idstate)->description : "---",
			"Contacto"		=>	$request->groups->first()->provider->contact !="" ? $request->groups->first()->provider->contact : "---",
			"Beneficiario"	=>	$request->groups->first()->provider->beneficiary !="" ? $request->groups->first()->provider->beneficiary : "---",
			"Otro"			=>	$request->groups->first()->provider->commentaries !="" ? $request->groups->first()->provider->commentaries : "---",
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
		foreach ($request->groups->first()->provider->providerBank as $bank)
		{
			$marktr	=	"";
			if ($request->groups->first()->provider_has_banks_id == $bank->id)
			{
				$marktr	=	"marktr";
			}
			$body	=
			[
				"classEx"	=>	$marktr,
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
					"content"	=>	["label"	=>	$bank->iban=='' ? $bankIban	=	"---" : $bankIban	=	$bank->iban]
				],
				[
					"content"	=>	["label"	=>	$bank->bic_swift=='' ? $bankBic	=	"---" : $bankBic	=	$bank->bic_swift]
				],
				[
					"content"	=>	["label"	=>	$bank->agreement=='' ? $bankAgreement	=	"---" : $bankAgreement	=	$bank->agreement]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
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
		foreach ($request->groups->first()->detailGroups as $detail)
		{
			$taxesConcept		=	0;
			$retentionConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept+=$tax->amount;
			}
			foreach ($detail->retentions as $ret)
			{
				$retentionConcept+=$ret->amount;
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept !="" ? $countConcept : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->quantity !="" ? $detail->quantity : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->unit !="" ? $detail->unit : "---"]
				],
				[
					"content"	=>	["label"	=>	$detail->description !="" ? htmlentities($detail->description) : "---"]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($detail->unitPrice,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($detail->tax,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($taxesConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($retentionConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($detail->amount,2)]
				],
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="table"
		@endslot
	@endcomponent
	<div class="totales2">
		@php
			foreach ($request->groups->first()->detailGroups as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes	+=	$tax->amount;
				}
				foreach ($detail->retentions as $ret)
				{
					$retentions	+=	$ret->amount;
				}
			}
			$modelTable	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,".",",")]]],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$ ".number_format($taxes,2)]]],
				["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$ ".number_format($retentions,2)]]],
				["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totaliva\"",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,".",",")]]],
				["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"total\"",		"label"	=>	"$ ".number_format($request->groups->first()->amount,2,".",",")]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('textNotes')
			{{ htmlentities($request->groups->first()->notes) }}
		@endslot
		@endcomponent
		@slot('attributeExComment')
			name="note" placeholder="Ingrese la nota" readonly="readonly"
		@endslot
	</div>
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	$request->groups->first()->reference !="" ? htmlentities($request->groups->first()->reference) : "---",
			"Tipo de moneda"				=>	$request->groups->first()->typeCurrency !="" ? $request->groups->first()->typeCurrency : "---",
			"Fecha de pago"					=>	$request->PaymentDate!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y') : "---",
			"Forma de pago"					=>	$request->groups->first()->paymentMethod->method !="" ? $request->groups->first()->paymentMethod->method : "---",
			"Estado de factura"				=>	$request->groups->first()->statusBill !="" ? $request->groups->first()->statusBill : "---",
			"Importe a pagar"				=>	$request->groups->first()->amount !="" ? "$".number_format($request->groups->first()->amount,2) : "---"
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
		@slot('classEx')
			employee-details
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->groups->first()->documentsGroups)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach ($request->groups->first()->documentsGroups as $doc)
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
								"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')],
					]
				];
			$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead		=	["Documento"];
			$modelBody[]	=	["label"	=>	"NO HAY DOCUMENTOS"];
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$requestAccount			=	App\Account::find($request->groups->first()->idAccAccOriginR);
		$requestAccountDestity	=	App\Account::find($request->groups->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->groups->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->groups->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->groups->first()->idProjectOriginR)->proyectName,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccountDestity->account." - ".$requestAccountDestity->description." (".$requestAccountDestity->content.")",
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable, "classEx" => "employee-details"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS ASIGNADAS"]) @endcomponent
	@php
		$modelHead			=	[];
		$body				=	[];
		$modelBody			=	[];
		$descriptions		=	"";
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach ($request->groups->first()->detailGroups as $detail)
		{
			if (count($detail->labels))
			{
				$counter	=	0;
				foreach ($detail->labels as $label)
				{
					$counter++;
					$descriptions	.=	$label->label->description.($counter<count($detail->labels) ? ", " : "");
				}
			}
			else
			{
				$descriptions	="Sin etiqueta";
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$detail->quantity." ".$detail->unit]
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)]
				],
				[
					"content"	=>	["label"	=>	$descriptions]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			request-validate
			mt-4
		@endslot
	@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments			=	App\Payment::where('idFolio',$request->folio)->get();
		$subtotal			=	$request->groups->first()->subtotales;
		$iva				=	$request->groups->first()->tax;
		$total				=	$request->groups->first()->amount;
		$totalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
		$subtotalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal_real') : 0;
		$ivaPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva_real') : 0;
		$taxPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$retentionPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('retention_real') : 0;
		$tax				=	0;
		$retention			=	0;
		foreach($request->groups->first()->detailGroups as $detail)
		{
			foreach($detail->taxes as $item)
			{
				$tax += $item->amount;
			}
			foreach($detail->retentions as $item)
			{
				$retention += $item->amount;
			}
		}
	@endphp
	@if($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead			=	[];
			$body				=	[];
			$modelBody			=	[];
			$modelHead			=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($request->paymentsRequest as $pay)
			{
				$componentBtnDoc	=	[];
				foreach ($pay->documentsPayments as $doc)
				{
					$componentBtnDoc[]	=
					[
						"kind"			=>	"components.Buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""."title=\"".$doc->path."\""
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	'$'.number_format($pay->amount,2)]
					],
					[
						"content"	=>	$componentBtnDoc
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\"",
								"classEx"		=>	"idpayment"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
								"classEx"		=>	"follow-btn"
							]
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx" => "h-10 py-2",	"label"	=>	"$".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx" => "h-10 py-2",	"label"	=>	"$".number_format(($total)-$totalPagado,2)]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@component('components.inputs.input-text')
		@slot('attributeEx')
		type="hidden" id="restaTotal" value="{{ round(($total)-$totalPagado,2) }}" @endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
		type="hidden" id="restaSubtotal" value="{{ round(($total)-$totalPagado,2) }}" @endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
		type="hidden" id="restaTax" value="{{ round(($tax)-$taxPagado,2) }}" @endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
		type="hidden" id="restaRetention" value="{{ round(($retention)-$retentionPagado,2) }}" @endslot
	@endcomponent
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
			$('.datepicker').datepicker({ dateFormat : 'dd-mm-yy' });
		});
	});
</script>
@endsection 
