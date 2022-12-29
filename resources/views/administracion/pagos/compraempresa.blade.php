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
		$requestAccountOrigin	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOrigin);
		$requestAccount			=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestiny);
		$modelTable				=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->purchaseEnterprise->first()->title).' - '.Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",					$request->purchaseEnterprise->first()->numberOrder!="" ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
			["Fiscal:",								$request->taxPayment == 1 ? "Sí" : "No"],
			["Solicitante:",						$requestUser->name.' '.$requestUser->last_name.' '.$requestUser->scnd_last_name],
			["Elaborado por:",						$elaborateUser->name.' '.$elaborateUser->last_name.' '.$elaborateUser->scnd_last_name],
			["Empresa Origen:",						App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOrigin)->name],
			["Dirección Origen:",					App\Area::find($request->purchaseEnterprise->first()->idAreaOrigin)->name],
			["Departamento Origen:",				App\Department::find($request->purchaseEnterprise->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen:",		$requestAccountOrigin->account.' - '.$requestAccountOrigin->description.' ('.$requestAccountOrigin->content.')'],
			["Proyecto Origen:",					App\Project::find($request->purchaseEnterprise->first()->idProjectOrigin)->proyectName],
			["Empresa Destino:",					App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account.' - '.$requestAccount->description.' ('.$requestAccount->content.')'],
			["Proyecto Destino:",					App\Project::find($request->purchaseEnterprise->first()->idProjectDestiny)->proyectName]
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable])
		@slot('classEx')
			mb-4
		@endslot
		@slot('title')
			Detalles de la Solicitud de {{ $request->requestkind->kind }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PEDIDO"]) @endcomponent
	@php
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
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$taxesConcept		=	0;
			$retentionConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept	+=	$tax->amount;
			}
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
					"content"	=>	["label"	=>	$detail->quantity]
				],
				[
					"content"	=>	["label"	=>	$detail->unit]
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->amount,2)]
				]
			];
			$countConcept++;
			$modelBody[] = $body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
	<div class="totales2">
		@php
			foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
				foreach ($detail->retentions as $ret)
				{
					$retentions += $ret->amount;
				}
			}
			$model	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"subtotal\"",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",")]]],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountAA\"",	"label"	=>	"$ ".number_format($taxes,2)]]],
				["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$ ".number_format($retentions,2)]]],
				["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totaliva\"",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",")]]],
				["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"total\" id=\"input-extrasmall\"",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",")]]]
			]
		@endphp
		@component('components.templates.outputs.form-details', ['modelTable' => $model])
			@slot('attributeExComment')
				name="note" readonly="readonly"
			@endslot
			@slot('textNotes')
				{{ htmlentities($request->purchaseEnterprise->first()->notes) }}
			@endslot
		@endcomponent
	</div>
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		if ($request->purchaseEnterprise->first()->idbanksAccounts != "")
		{
			$bankDescription	=	$request->purchaseEnterprise->first()->banks->bank->description;
			$bankAlias			=	$request->purchaseEnterprise->first()->banks->alias;
			$bankAccount		=	$request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
			$bankClabe			=	$request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
			$bankBranch			=	$request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
			$bankReference		=	$request->purchaseEnterprise->first()->banks->reference != "" ? $request->purchaseEnterprise->first()->banks->reference : "---";
		}
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->purchaseEnterprise->first()->typeCurrency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->purchaseEnterprise->first()->paymentMethod->method,
			"Banco"				=>	$bankDescription,
			"Alias"				=>	$bankAlias,
			"Cuenta"			=>	$bankAccount,
			"Clabe"				=>	$bankClabe,
			"Sucursal"			=>	$bankBranch,
			"Referencia"		=>	$bankReference,
			"Importe a pagar"	=>	"$".number_format($request->purchaseEnterprise->first()->amount,2)
		 ];	
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->purchaseEnterprise->first()->documentsPurchase)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach ($request->purchaseEnterprise->first()->documentsPurchase as $doc)
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
		$requestAccount			=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOriginR);
		$requestAccountDestiny	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->purchaseEnterprise->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->purchaseEnterprise->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->purchaseEnterprise->first()->idProjectOriginR)->proyectName ,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestinyR)->name ,
			"Clasificación del Gasto de Destino"	=>	$requestAccountDestiny->account." - ".$requestAccountDestiny->description." (".$requestAccountDestiny->content.")",
			"Nombre del Proyecto de Destino"		=>	App\Project::find($request->purchaseEnterprise->first()->idProjectDestinyR)->proyectName,
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS ASIGNADAS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$descriptionTicket	=	"";
			if (count($detail->labels))
			{
				foreach ($detail->labels as $label)
				{
					$descriptionTicket	.=	$label->label->description.", ";
				}
			}
			else
			{
				$descriptionTicket	=	"Sin etiqueta";
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
					"content"	=>	["label"	=>	$descriptionTicket]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew"
		@endslot
		@slot('classExBody')
			class="request-validate"
		@endslot
	@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : $request->authorizeComment
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments			=	App\Payment::where('idFolio',$request->folio)->get();
		$total				=	$request->purchaseEnterprise->first()->amount;
		$iva				=	$request->purchaseEnterprise->first()->tax;
		$subtotal			=	$request->purchaseEnterprise->first()->subtotales;
		$totalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount') : 0;
		$subtotalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal') : 0;
		$ivaPagado 			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva') : 0;
		$taxPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$retentionPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('retention_real') : 0;
		$tax				=	0;
		$retention			=	0;
		foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			foreach($detail->taxes as $item)
			{
				$tax		+= $item->amount;
			}
			foreach($detail->retentions as $item)
			{
				$retention	+=	$item->amount;
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
						"content"	=>	["label"	=>	$pay->accounts->account." - ".$pay->accounts->description." (".$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($pay->amount,2)]
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
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"label"			=>	"<span class='icon-search'></span>",
							"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
							"classEx"		=>	"follow-btn"
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"classEx"		=>	"idpayment",
							"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\""
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			]
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
	@endif
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTotal\" value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaSubtotal\" value=\"".round(($subtotal)-$subtotalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaIva\" value=\"".round(($iva)-$ivaPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTax\" value=\"".round(($tax)-$taxPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaRetention\" value=\"".round(($retention)-$retentionPagado,2)."\""]) @endcomponent
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
