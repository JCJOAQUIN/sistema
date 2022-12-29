@section('data')
	@php 
		$taxes		=	0;
		$retentions	=	0;
	@endphp	
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$requestAccount	=	App\Account::find($request->adjustment->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:", 					htmlentities($request->adjustment->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->datetitle)->format('d-m-Y')],
			["Comentarios:", 						$request->adjustment->first()->commentaries!="" ? htmlentities($request->adjustment->first()->commentaries) : '---'],
			["Solicitante:", 						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name ],
			["Elaborado por:", 						$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen:", 					App\Enterprise::find($request->adjustment->first()->idEnterpriseOrigin)->name],
			["Empresa Destino:", 					App\Enterprise::find($request->adjustment->first()->idEnterpriseDestiny)->name],
			["Dirección Destino:", 					App\Area::find($request->adjustment->first()->idAreaDestiny)->name],
			["Departamento Destino:", 				App\Department::find($request->adjustment->first()->idDepartamentDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto Destino:", 					App\Project::find($request->adjustment->first()->idProjectDestiny)->proyectName]
		];
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles de la Solicitud de {{ $request->requestkind->kind }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE ORIGEN"]) @endcomponent
	@foreach ($request->adjustment->first()->adjustmentFolios as $af)
		@php
			$modelTable	=
			[
				["Empresa:",					$af->requestModel->reviewedEnterprise->name],
				["Dirección:",					$af->requestModel->reviewedDirection->name],
				["Departamento:",				$af->requestModel->reviewedDepartment->name],
				["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' - '. $af->requestModel->accountsReview->description.' ('. $af->requestModel->accountsReview->content.")" : 'Varias'],
				["Proyecto:",					$af->requestModel->reviewedProject->proyectName ],
			]
		@endphp
	@endforeach
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable])
		@slot('classEx') 
			mt-4
		@endslot
		@slot('title')
			FOLIO #{{ $af->idFolio }}
		@endslot
	@endcomponent	
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PEDIDO"]) @endcomponent
	@php
		$modelHead			=	[];
		$body				=	[];
		$modelBody			=	[];
		$countConcept 		=	1;
		$taxesConcept		=	0;
		$retentionConcept	=	0;
		$modelHead			=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Solicitud de"],
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
		foreach ($request->adjustment->first()->adjustmentFolios as $detail)
		{
			switch ($detail->requestModel->kind)
			{
				case '1':
					foreach ($detail->requestModel->purchases->first()->detailPurchase as $detpurchase)
					{
						foreach ($detpurchase->taxes as $tax)
						{
							$taxesConcept+=$tax->amount;
						}
						$body	=
						[
							[
								"content"	=>	["label"	=>	$countConcept]
							],
							[
								"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
							],
							[
								"content"	=>	["label"	=>	$detpurchase->quantity]
							],
							[
								"content"	=>	["label"	=>	$detpurchase->unit]
							],
							[
								"content"	=>	["label"	=>	htmlentities($detpurchase->description)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detpurchase->unitPrice,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detpurchase->tax,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($taxesConcept,2)]
							],
							[	
								"content"	=>	["label"	=>	"$".number_format($taxesConcept,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detpurchase->amount,2)]
							],
						];
						$countConcept++;
						$modelBody[]	=	$body;
					}
					break;
				case '3':
					foreach ($detail->requestModel->expenses->first()->expensesDetail as $detexpenses)
					{
						foreach ($detexpenses->taxes as $tax)
						{
							$taxesConcept+=$tax->amount;
						}
						foreach ($detexpenses->retentions as $ret)
						{
							$retentionConcept+=$ret->amount;
						}
						$body	=
						[
							[
								"content"	=>	["label"	=>	$countConcept]
							],
							[
								"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
							],
							[
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	htmlentities($detexpenses->description)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detexpenses->unitPrice,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detexpenses->tax,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($taxesConcept,2)]
							],
							[	
								"content"	=>	["label"	=>	"$".number_format($retentionConcept,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detexpenses->amount,2)]
							],
						];
						$countConcept++;
						$modelBody[]	=	$body;
					}
					break;
				case '9':
					foreach ($detail->requestModel->refunds->first()->refundDetail as $detrefund)
					{
						foreach ($detrefund->taxes as $tax)
						{
							$taxesConcept+=$tax->amount;
						}
						$body	=
						[
							[
								"content"	=>	["label"	=>	$countConcept]
							],
							[
								"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
							],
							[
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	htmlentities($detrefund->concept)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detrefund->unitPrice,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detrefund->tax,2)]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($taxesConcept,2)]
							],
							[	
								"content"	=>	["label"	=>	"$ 0.00"]
							],
							[
								"content"	=>	["label"	=>	"$".number_format($detrefund->amount,2)]
							],
						];
						$countConcept++;
						$modelBody[]	=	$body;
					}
					break;
			}
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4 text-center"]) @endcomponent
	@php		
		foreach($request->adjustment->first()->detailAdjustment as $detail)
		{
			foreach($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
		}
		$model	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"subtotal\"",	"label"	=>	"$".number_format($request->adjustment->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountAA\"",	"label"	=>	"$".number_format($request->adjustment->first()->additionalTax,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$".number_format($request->adjustment->first()->retention,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totaliva\"",	"label"	=>	"$".number_format($request->adjustment->first()->tax,2)]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"total\"",		"label"	=>	"$".number_format($request->adjustment->first()->amount,2)]]],
		];
	@endphp
	@component('components.templates.outputs.form-details',['modelTable'=>$model])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent	
	@php
		$modelTable =
		[
			"Tipo de moneda"	=>	$request->adjustment->first()->currency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->adjustment->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$".number_format($request->adjustment->first()->amount,2)
		];
	@endphp	
	@component('components.templates.outputs.table-detail-single', ['modelTable'	=>	$modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->adjustment->first()->documentsAdjustment)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach ($request->adjustment->first()->documentsAdjustment as $doc)
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
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')]
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
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead"	=>	$modelHead, "modelBody"	=>	$modelBody])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$requestAccount	=	App\Account::find($request->adjustment->first()->idAccAccDestinyR);
		$coments = $request->checkComment == "" ? "Sin comentarios" : $request->checkComment;	
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->adjustment->first()->idEnterpriseDestinyR)->name,
			"Nombre de la Dirección de Destino"		=>	App\Area::find($request->adjustment->first()->idAreaDestinyR)->name,
			"Nombre del Departamento de Destino"	=>	App\Department::find($request->adjustment->first()->idDepartamentDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account."-".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Destino	"		=>	App\Project::find($request->adjustment->first()->idProjectDestinyR)->proyectName,
			"Comentarios"							=>	htmlentities($coments),
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent	
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$coments	=	$request->authorizeComment == "" ? "Sin comentarios" : $request->authorizeComment;
			$modelTable	=
			[
				'Autorizó'		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				'Comentarios'	=>	htmlentities($coments)
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@endif
	@php
		$payments			=	App\Payment::where('idFolio',$request->folio)->get();	
		$subtotal			=	$request->adjustment->first()->subtotales;
		$tax				=	$request->adjustment->first()->additionalTax;
		$retention			=	$request->adjustment->first()->retention;
		$iva				=	$request->adjustment->first()->tax;
		$total				=	$request->adjustment->first()->amount;
		$totalPagado		=	0;
		$totalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;;
		$subtotalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal_real') : 0;
		$taxPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$retentionPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('retention_real') : 0;
		$ivaPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva_real') : 0;
	@endphp
	@if ($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor',  ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
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
					["value"	=>	"Fecha"],
					["value"	=>	"Acción"],
				]
			];
			foreach ($request->paymentsRequest as $pay)
			{
				$buttonComponents	=	[];
				if (count($pay->documentsPayments)>0)
				{
					foreach ($pay->documentsPayments as $doc)
					{
						$buttonComponents[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"buttonElement"	=>	"a",
							"label"			=>	"PDF",
							"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""." title=\"".$doc->path."\""
						];
					}
				}
				else
				{
					$buttonComponents	=	["label"	=>	"Sin documento"];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	"$".number_format($pay->amount,2)]
					],
					[
						"content"	=>	$buttonComponents
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
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
								"classEx"		=>	"follow-btn"
							]
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4 table"]) @endcomponent
		@php
			$model	=
			[
				["label"	=>	"Total pagado:",	"inputsEx" => [["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"totalPagado\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx" => [["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"resta\"",		"classEx"	=>	"h-10 py-2",	"label"	=>	"$".number_format(($total)-$totalPagado,2)]]]
			]
		@endphp
		@component('components.templates.outputs.form-details',['modelTable'=>$model])@endcomponent
	@endif
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" id="restaTotal" value="{{ round(($total)-$totalPagado,2) }}"
		@endslot
	@endcomponent
	{{-- Habilitar en caso de que se llegue a utilizar, razon de porque no lo agrego, es porque veo que no manejan ni el iva ni el subtotal aquo --}}
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" id="restaTax" value="{{ round(($tax)-$taxPagado,2) }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" id="restaRetention" value="{{ round(($retention)-$retentionPagado,2) }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" id="restaSubtotal" value="{{ round(($subtotal)-$subtotalPagado,2) }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" id="restaIva" value="{{ round(($iva)-$ivaPagado,2) }}"
		@endslot
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
		$(function(){
			$('.datepicker').datepicker({dateFormat : 'dd-mm-yy',});
		});
	});
</script>
@endsection 