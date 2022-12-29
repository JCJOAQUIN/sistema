@section('data')
	@php 
		$user		=	App\User::find($request->idRequest); 
		$account	=	App\Account::find($request->account); 
		$project	=	App\Project::find($request->idProject); 
		$docs		=	0;
		$taxes		=	0;
		$taxes3		=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable		=
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	htmlentities($request->expenses->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->expenses->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name]
		]
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL SOLICITANTE"]) @endcomponent
	@php
		foreach ($request->expenses as $expense)
		{
			$metodPayment		=	$expense->paymentMethod->method;
			$referenceExpense	=	$expense->reference;
			$typeCurrency		=	$expense->currency;
			if (isset($request))
			{
				foreach ($request->expenses->first()->expensesDetail as $detail)
				{
					foreach ($detail->taxes as $tax)
					{
						$taxes3	=	$tax->amount;
					}
				}
			}
		}
		foreach ($request->expenses as $expense) 
		{
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() as $bank)
			{
				if ($expense->idEmployee == $bank->idEmployee)
				{
					$bankDescription	=	$bank->description!=null ? $bank->description : "---";
					$bankAlias			=	$bank->alias!=null ? $bank->alias : "---";
					$bankCard			=	$bank->cardNumber!=null ? $bank->cardNumber : "---";
					$bankClabe			=	$bank->clabe!=null ? $bank->clabe : "---";
					$bankAccount		=	$bank->account!=null ? $bank->account : "---";
				}
			}
		}
		$modelTable	=
		[
			"Forma de pago"		=>	$metodPayment!=null ? $metodPayment : "---",
			"Referencia"		=>	$referenceExpense!=null ? htmlentities($referenceExpense) : "---",
			"Tipo de moneda"	=>	$typeCurrency,
			"Importe"			=>	$expense->total!=null ? "$ ".number_format($expense->total,2) : "$ 0.00",
			"Banco"				=>	isset($bankDescription) ? $bankDescription : "---",
			"Alias"				=>	isset($bankAlias) ? $bankAlias : "---",
			"Número de tarjeta"	=>	isset($bankCard) ? $bankCard : "---",
			"CLABE"				=>	isset($bankClabe) ? $bankClabe : "---",
			"Número de cuenta"	=>	isset($bankAccount) ? $bankAccount : "---"
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "RELACIÓN DE DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	
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
				["value"	=>	"Importe"],
				["value"	=>	"Documento"]
			]
		];
		$subtotalFinal	=	$ivaFinal	=	$totalFinal	=	0;
		$countConcept	=	1;
		foreach (App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
		{
			$subtotalFinal	+=	$expensesDetail->amount;
			$ivaFinal		+=	$expensesDetail->tax;
			$totalFinal		+=	$expensesDetail->sAmount;
			$taxes2	=	0;
			foreach ($expensesDetail->taxes as $tax)
			{
				$taxes2	+=	$tax->amount;
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->concept!="" ? $expensesDetail->concept : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($expensesDetail->account) ? $expensesDetail->account->account.' - '.$expensesDetail->account->description.' ('.$expensesDetail->account->content.")" : "---"]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->document!="" ? $expensesDetail->document : "---"]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->taxPayment==1 ? "Sí" : "No"]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->amount!="" ? "$".number_format($expensesDetail->amount,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->tax!="" ? "$".number_format($expensesDetail->tax,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$taxes2!="" ? "$".number_format($taxes2,2) : "---"]
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->sAmount!="" ? "$".number_format($expensesDetail->sAmount,2) : "---"]
				],
			];
			if (App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get()->count()>0) {
				$labelGral	=	[];
				foreach (App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get() as $doc)
				{
					$labelGral[]	=	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y')];
					$labelGral[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/expenses/'.$doc->path)."\""." title=\"".$doc->path."\""
					];
				}
				$body[]	=
				[
					"content"	=>	$labelGral
				];
			}
			else
			{
				$body[] = 
				[
					"content"	=>	"---"
				];
			}
			$modelBody[]	=	$body;
			$countConcept++;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="body"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
	<div>
		@php
			if ($totalFinal!=0)
			{
				$valueSubtotal	=	"$".number_format($subtotalFinal,2);
				$valueIva		=	"$".number_format($ivaFinal,2);
				$valueTotal		=	"$".number_format($totalFinal,2);
			}
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
				}
			}
			if(isset($request->expenses))
			{
				foreach($request->expenses as $expense)
				{
					$valueRenitegro	=	"$".number_format($expense->reintegro,2);
					$valueReembolso	=	"$".number_format($expense->reembolso,2);
				}
			}
			$modelTable	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 subtotal",	"label"	=>	$valueSubtotal,					"attributeEx"	=>	"id=\"subtotal\""]]],
				["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 ivaTotal",	"label"	=>	$valueIva]]],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 amountAA",	"label"	=>	"$".number_format($taxes,2),	"attributeEx"	=>	"name=\"amountAA\""]]],
				["label"	=>	"Reintegro:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 reintegro",	"label"	=>	$valueRenitegro,				"attributeEx"	=>	"name=\"reintegro\""]]],
				["label"	=>	"Reembolso:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 reembolso",	"label"	=>	$valueReembolso,				"attributeEx"	=>	"name=\"reembolso\""]]],
				["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2 total",		"label"	=>	$valueTotal]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
			@slot('classEx')
				totales
			@endslot
		@endcomponent
	</div>
	@if ($request->idCheck != "")
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DE REVISIÓN
		@endcomponent
		@php
			if ($request->idEnterpriseR!="")
			{
				$enterpriceName	=	App\Enterprise::find($request->idEnterpriseR)->name;
				$directionName	=	$request->reviewedDirection->name;
				$departmentName	=	App\Department::find($request->idDepartamentR)->name;
				$reviewAccount	=	App\Account::find($request->accountR);
				$nameProject	=	$request->reviewedProject->proyectName;
				if(isset($reviewAccount->account))
				{
					$acountDescription	=	$reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")";
				}
				else
				{
					$acountDescription	=	"Varias";
				} 
				if (count($request->labels)>0)
				{
					$counter	=	0;
					foreach($request->labels as $label)
					{
						$counter++;
						$labelDescription	.=	($counter<count($request->labels)) ? $label->description.", " : "";
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
				"Nombre de la Empresa"		=>	$enterpriceName,
				"Nombre de la Dirección"	=>	$directionName,
				"Nombre del Departamento"	=>	$departmentName,
				"Clasificación del gasto"	=>	$acountDescription,
				"Nombre del Proyecto"		=>	$nameProject,
				"Etiquetas"					=>	$labelDescription,
				"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
		@if ($request->idEnterpriseR!="")
			@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS Y RECLASIFICACIÓN ASIGNADA"]) @endcomponent
			@php
				$modelHead			=	[];
				$body				=	[];
				$modelBody			=	[];
				$modelHead			=
				[
					[
						["value"	=>	"#"],
						["value"	=>	"Concepto"],
						["value"	=>	"Importe"],
						["value"	=>	"Etiquetas"]
					]
				];
				$countConcept = 1;
				foreach (App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
				{
					$labelDescriptions	=	"";
					$subtotalFinal	+=	$expensesDetail->amount;
					$ivaFinal		+=	$expensesDetail->tax;
					$totalFinal		+=	$expensesDetail->sAmount;
					if (count($expensesDetail->labels))
					{
						$counter	=	0;
						foreach($expensesDetail->labels as $label)
						{
							$counter++;
							$labelDescriptions	.=	$label->label->description.($counter<count($expensesDetail->labels) ? ", " : "");
						}
					}
					else
					{
						$labelDescriptions	=	"Sin etiqueta";
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$countConcept]
						],
						[
							"content"	=>	["label"	=>	htmlentities($expensesDetail->concept)]
						],
						[
							"content"	=>	["label"	=>	$expensesDetail->accountR->account.' - '.$expensesDetail->accountR->description.' ('.$expensesDetail->accountR->content.")"]
						],
						[
							"content"	=>	["label"	=>	$labelDescriptions]
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
				@slot('attributeEx')
					id="table"
				@endslot
			@endcomponent
		@endif
	@endif
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
	@if ($request->idAuthorize != "")
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable, "classEx" => "employee-details"]) @endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->expenses->first()->total;
		$iva			=	$request->expenses->first()->expensesDetail()->sum('tax');
		$subtotal		=	$request->expenses->first()->expensesDetail()->sum('amount');
		$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount') : 0;
		$subtotalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal') : 0;
		$ivaPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva') : 0;
		$taxPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('tax_real') : 0;
		$tax			=	0;
		if(isset($request))
		{
			foreach($request->expenses->first()->expensesDetail as $detail)
			{
				foreach($detail->taxes as $item)
				{
					$tax	+=	$item->amount;
				}
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
					["value"	=>	"Acción"]
				]
			];
			foreach ($request->paymentsRequest as $pay)
			{
				$componentsExt	=	[];
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
				$body	=
				[
					[
						"content"	=>
						[
							"label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"
						]
					],
					[
						"content"	=>
						[
							"label"	=>	"$".number_format($pay->amount,2)
						]
					],
					[
						"content"	=>	$componentsExt
					],
					[
						"content"	=>
						[
							"label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
								"classEx"		=>	"follow-btn"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\"",
								"classEx"		=>	"idpayment"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
				table
			@endslot
		@endcomponent
		@php
			foreach($request->expenses as $expense)
			{
				if($expense->reembolso > 0)
				{
					$rest	=	"$ ".number_format($expense->reembolso-$totalPagado,2);
				}
				elseif($expense->reintegro > 0)
				{
					$rest	=	"$ ".number_format($expense->reintegro-$totalPagado ,2);
				}
			}
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	$rest]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@foreach($request->expenses as $expense)
		@if($expense->reembolso > 0)
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="restaTax" value="{{ round(($tax)-$taxPagado,2) }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden"
					id="restaIva"
					value="0"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="restaTotal" value="{{ round($expense->reembolso-$totalPagado,2) }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="restaSubtotal" value="{{ round($expense->reembolso-$totalPagado,2) }}"
				@endslot
			@endcomponent
		@elseif($expense->reintegro > 0)
			@component('components.inputs.input-text')
				@slot('attributeEx')
					id="restaTax"
					type="hidden"
					value="{{ round(($tax)-$taxPagado,2) }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					id="restaIva" type="hidden" value="0"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					id="restaTotal" type="hidden" value="{{ round($expense->reintegro,2) }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					id="restaSubtotal" type="hidden" value="{{ round($expense->reintegro,2) }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					id="codeIsTrue" type="hidden" value="1"
				@endslot
			@endcomponent
		@endif
	@endforeach
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
