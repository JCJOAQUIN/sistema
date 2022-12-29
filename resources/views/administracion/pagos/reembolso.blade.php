@section('data')
	@php 
		$user		=	App\User::find($request->idRequest); 
		$account	=	App\Account::find($request->account); 
		$project	=	App\Project::find($request->idProject); 
		$docs		=	0;
		$taxes		=	0;
	@endphp
	@if($request->refunds->first()->idRequisition != "")
		@component('components.labels.not-found', ["variant" => "note", "title" => ""])
			@component('components.labels.label', ["label" => "<span class=\"icon-bullhorn\"></span> Esta solicitud viene de la requisición #".$request->refunds->first()->idRequisition."."]) @endcomponent
			@component('components.labels.label', ["label" => "FOLIO: ".$request->new_folio]) @endcomponent
			@if($request->refunds->first()->requisitionRequest->idProject == 75)
				@component('components.labels.label', ["label" => "SUBPROYECTO/CÓDIGO WBS: ".$request->refunds->first()->requisitionRequest->requisition->wbs->proyectNumber.' '.$request->refunds->first()->requisitionRequest->requisition->wbs->proyectName."."]) @endcomponent
			@endif
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable	=
		[
			["Folio",			$request->folio],
			["Título y fecha:",	htmlentities($request->refunds->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->refunds->first()->datetitle)->format('d-m-Y')],
			["Solicitante",		$requestUser->name!="" ? $requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name : "---"],
			["Elaborado por",	$elaborateUser->name!="" ? $elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name : "---"],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL SOLICITANTE"]) @endcomponent
	@php
		$bankDescription	=	"";
		$alias				=	"";
		$cardNumber			=	"";
		$clabeNumber		=	"";
		$bankAccount		=	"";
		foreach ($request->refunds as $refund)
		{		
			$refundRequest		=	$refund->paymentMethod->method!=null ? $refund->paymentMethod->method : "---";
			$refundReference	=	$refund->reference!=null ? htmlentities($refund->reference) : "---";
			$refundCurrency		=	$refund->currency!=null ? $refund->currency : "---";
			$refundTotal		=	"$".number_format($refund->total,2);
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$refund->idUsers)->get() as $bank)
			{
				if ($refund->idEmployee == $bank->idEmployee)
				{
					$bankDescription	=	$bank->description!=null ? $bank->description : "---";
					$alias				=	$bank->alias!=null ? $bank->alias : '---';
					$cardNumber			=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
					$clabeNumber		=	$bank->clabe!=null ? $bank->clabe : '---';
					$bankAccount		=	$bank->account!=null ? $bank->account : '---';
				}
				else
				{
					$bankDescription	=	"---";
					$alias				=	"---";
					$cardNumber			=	"---";
					$clabeNumber		=	"---";
					$bankAccount		=	"---";
				}
			}
		}
		$modelTable	=
		[
			"Forma de pago"		=>	$refundRequest,
			"Referencia"		=>	$refundReference,
			"Tipo de moneda"	=>	$refundCurrency,
			"Importe"			=>	$refundTotal,
			"Banco"				=>	$bankDescription,
			"Alias"				=>	$alias,
			"Número de tarjeta"	=>	$cardNumber,
			"CLABE"				=>	$clabeNumber,
			"Número de cuenta"	=>	$bankAccount,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "RELACIÓN DE DOCUMENTOS"]) @endcomponent
	@php
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$subtotalFinal	=	$ivaFinal = $totalFinal = 0;
		$countConcept	=	1;
		$modelHead		=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Concepto"],
				["value"	=>	"Clasificación del gasto"],
				["value"	=>	"Tipo de Documento/No. Factura"],
				["value"	=>	"Fiscal"],
				["value"	=>	"Subtotal"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"],
				["value"	=>	"Documento(s)"]
			]
		];
		foreach (App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
		{
			$subtotalFinal	+=	$refundDetail->amount;
			$ivaFinal		+=	$refundDetail->tax;
			$totalFinal		+=	$refundDetail->sAmount;
			$taxes2			=	0;
			$retentions2	=	0;
			if (isset($refundDetail->account))
			{
				$detailAccount	=	$refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.")";
			}
			foreach ($refundDetail->taxes as $tax)
			{
				$taxes2	+=	$tax->amount;
			}
			foreach ($refundDetail->retentions as $ret)
			{
				$retentions2 +=$ret->amount;
			}
			if (App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
			{
				$labelGral	=	[];
				foreach (App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
				{
					$labelGral[]	=	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d',($doc->datepath != "" ? $doc->datepath : $doc->date))->format('d-m-Y')];
					$labelGral[]	=	["label"	=>	$doc->name != '' ? "$doc->name" : "Otro"];
					$labelGral[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/refounds/'.$doc->path)."\""." title=\"".$doc->path."\""
					];
				}
			}
			else
			{
				$labelGral	=	["label"	=>	"---"];
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept!="" ? $countConcept : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->concept!="" ? htmlentities($refundDetail->concept) : "---"]
				],
				[
					"content"	=>	["label"	=>	$detailAccount!="" ? $detailAccount : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->document!="" ? $refundDetail->document : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->taxPayment==1 ? "si" : "no"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->amount!="" ? "$".number_format($refundDetail->amount,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->tax!="" ? "$".number_format($refundDetail->tax,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$taxes2!="" ? "$".number_format($taxes2,2) : "$0.00"]
				],
				[
					"content"	=>	["label"	=>	$retentions2!="" ? "$".number_format($retentions2,2) : "$0.00"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->sAmount!="" ? "$".number_format($refundDetail->sAmount,2) : "---"]
				],
				[
					"content"	=>	$labelGral
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
	<div class="totales">
		@php
			$retentionConcept2 = 0;
			if ($totalFinal!=0)
			{
				$subtotalValue	=	"$".number_format($subtotalFinal,2);
				$ivaTotal		=	"$".number_format($ivaFinal,2);
				$total			=	"$".number_format($totalFinal,2);
			}
			if(isset($request))
			{
				foreach($request->refunds->first()->refundDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
					foreach ($detail->retentions as $ret)
					{
						$retentionConcept2 = $retentionConcept2 + $ret->amount;
					}
				}
			}
			$model	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"h-10 py-2 subtotal", "label"	=>	$subtotalValue, "attributeEx"	=>	"id=\"subtotal\""]]],
				["label"	=>	"IVA:", 				"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"h-10 py-2 ivaTotal", "label"	=>	$ivaTotal, "attributeEx"	=>	"id=\"iva\""]]],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($taxes,2)]]],
				["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($retentionConcept2,2), "attributeEx"	=>	"name=\"amountRetentions\""]]],
				["label"	=>	"Total:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"h-10 py-2", "label"	=>	$total, "attributeEx"	=>	"id=\"total\" name=\"total\""]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $model])@endcomponent
	</div>
	@if($request->idCheck != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
		@php
			if ($request->idEnterpriseR!="")
			{
				$labelDescription	=	"";
				$enterprise			=	App\Enterprise::find($request->idEnterpriseR)->name;
				$direccion			=	isset($request->reviewedDirection->name) ? $request->reviewedDirection->name : "";
				$department			=	isset(App\Department::find($request->idDepartamentR)->name) ? App\Department::find($request->idDepartamentR)->name : "";
				$reviewAccount		=	App\Account::find($request->accountR);
				if(isset($reviewAccount->account))
				{
					$accountDescription	=	$reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")";
				}
				else
				{
					$accountDescription	=	"Varias";
				}
				if (count($request->labels))
				{
					foreach($request->labels as $label)
					{
						$labelDescription	.=	$label->description.", ";
					}
				}
				else
				{
					$labelDescription	=	"Sin etiqueta";
				}
			}
			$modelTable	=
			[
				"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
				"Nombre de la Empresa"		=>	$enterprise,
				"Nombre de la Dirección"	=>	$direccion,
				"Nombre del Departamento"	=>	$department,
				"Clasificación del gasto"	=>	$accountDescription,
				"Nombre del Proyecto"		=>	isset($request->reviewedProject->proyectName) ? $request->reviewedProject->proyectName : "",
				"Etiquetas"					=>	$labelDescription,
				"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : $comment	=	htmlentities($request->checkComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
			@slot('classEx')
				employee-details
			@endslot
		@endcomponent
		@if($request->idEnterpriseR!="")
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS Y RECLASIFICACIÓN ASIGNADA"]) @endcomponent
			@php
				$modelHead		=	[];
				$body			=	[];
				$modelBody		=	[];
				$countConcept	=	1;
				$modelHead		=
				[
					[
						["value"	=>	"#"],
						["value"	=>	"Concepto"],
						["value"	=>	"Clasificación de gasto"],
						["value"	=>	"Etiquetas"]
					]
				];
				foreach (App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
				{
					$labels	=	"";
					if (count($refundDetail->labels))
					{
						$counter	=	0;
						foreach($refundDetail->labels as $label)
						{
							$counter++;
							$labels	.=	$label->label->description.($counter<count($refundDetail->labels) ? ", " : "");
						}
					}
					else
					{
						$labels	=	"Sin etiqueta";
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$countConcept!="" ? $countConcept : "---"]
						],
						[
							"content"	=>	["label"	=>	$refundDetail->concept!="" ? htmlentities($refundDetail->concept) : "---"]
						],
						[
							"content"	=>	["label"	=>	$refundDetail->accountR->account!="" ? $refundDetail->accountR->account." - ".$refundDetail->accountR->description." (".$refundDetail->accountR->content.")" : "---"]
						],
						[
							"content"	=>	["label"	=>	$labels]
						],
					];
					$modelBody[]	=	$body;
					$countConcept++;
				}
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot('classEx')
					mt-4
				@endslot
				@slot('attribteEx')
					id="table"
				@endslot
				@slot('attributeExBody')
					id="tbody-conceptsNew"
				@endslot
				@slot('classExBody')
					request-validate
				@endslot
			@endcomponent
		@endif
	@endif
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name!="" ? $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name : "---",
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$ret = 0;
		$payments			=	App\Payment::where('idFolio',$request->folio)->get();
		$total				=	$request->refunds->first()->total;
		$iva				=	$request->refunds->first()->refundDetail()->sum('tax');
		$subtotal			=	$request->refunds->first()->refundDetail()->sum('amount');
		$subtotalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal') : 0;
		$retentionPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('retention') : 0;
		$totalPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount') : 0;
		$ivaPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva') : 0;
		$taxPagado			=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$tax				=	0;
		foreach($request->refunds->first()->refundDetail as $detail)
		{
			foreach($detail->taxes as $item)
			{
				$tax	+=	$item->amount;
			}
			foreach($detail->retentions as $item)
			{
				$ret += $item->amount;
			}
		}
	@endphp
	@if($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead		=	[];
			$body			=	[];
			$modelBody		=	[];
			$modelHead		=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"],
					["value"	=>	""]
				]
			];
			foreach ($request->paymentsRequest as $pay)
			{
				$componentsExt	=	[];
				if (count($pay->documentsPayments))
				{
					foreach ($pay->documentsPayments as $doc)
					{
						$componentsExt[]	=
						[
							"kind"			=>	"components.Buttons.button",
							"variant"		=>	"dark-red",
							"label"			=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""."title=\"".$doc->path."\""
						];
					}
				}
				else
				{
					$componentsExt	=
						[
							["kind"	=>	"components.labels.label",	"label"	=>	"Sin documento"]
						];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account!="" ? $pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")" : "---"]
					],
					[
						"content"	=>	["label"	=>	$pay->amount!="" ? '$'.number_format($pay->amount,2) : "---"]
					],
					[
						"content"	=>	$componentsExt
					],
					[
						"content"	=>	["label"	=>	$pay->paymentDate !="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y') : "---"]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
								"classEx"		=>	"follow-btn"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\"",
								"classEx"		=>	"idpayment"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
				table
			@endslot
		@endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@endcomponent
	@endif
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTax\" value=\"".round(($tax)-$taxPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTotal\" value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaSubtotal\" value=\"".round(($subtotal)-$subtotalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaIva\" value=\"".round(($iva)-$ivaPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaRetention\" value=\"".round(($ret)-$retentionPagado,2)."\""]) @endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
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
