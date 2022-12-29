@section('data')
	@php 
		$user				=	App\User::find($request->idRequest);
		$enterprise			=	App\Enterprise::find($request->idEnterprise);
		$area				=	App\Area::find($request->idArea);
		$department			=	App\Department::find($request->idDepartment);
		$account			=	App\Account::find($request->account);
		$enterpriseSelected	=	$areaSelected = $departmentSelected = $userSelected = $projectSelected = '';
		$docs				=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable =
		[
			["Folio:",			$request->folio],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Título y fecha:",	htmlentities($request->resource->first()->title).' - '.Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y')]
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable, "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL SOLICITANTE"]) @endcomponent
	@php
		$modelTable	=	[];
		foreach($request->resource as $resource)
		{
			$modelTable	=
			[
				"Forma de pago"		=>	$resource->paymentMethod->method!="" ? $resource->paymentMethod->method : "---",
				"Referencia"		=>	$resource->reference!="" ? htmlentities($resource->reference) : "---",
				"Tipo de moneda"	=>	$resource->currency!="" ? $resource->currency : "---",
				"Importe"			=>	$resource->total!="" ? "$".number_format($resource->total,2) : "---",
			];
		}
		foreach($request->resource as $resource)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$resource->idUsers)->get() as $bank)
			{
				if($resource->idEmployee == $bank->idEmployee)
				{
					$modelTable	=
					[
						"Banco"				=>	$bank->description!=null ? $bank->description : "---",
						"Alias"				=>	$bank->alias!=null ? $bank->alias : "---" ,
						"Número de tarjeta"	=>	$bank->cardNumber!=null ? $bank->cardNumber : "---",
						"CLABE"				=>	$bank->clabe!=null ? $bank->clabe : "---",
						"Número de cuenta"	=>	$bank->account!=null ? $bank->account : "---",
					];
				}
			}
		}
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "RELACIÓN DE DOCUMENTOS"]) @endcomponent
	@php
		$body		=	[];
		$modelHead	=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Concepto"],
				["value"	=>	"Clasificación de gasto"],
				["value"	=>	"Importe"]
			]
		];
		$subtotalFinal	=	$ivaFinal = $totalFinal = 0;
		$countConcept	=	1;
		foreach($request->resource->first()->resourceDetail as $resourceDetail)
		{
			$totalFinal	+= $resourceDetail->amount;
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept!="" ? $countConcept : "---"]
				],
				[
					"content"	=>	["label"	=>	$resourceDetail->concept!="" ? htmlentities($resourceDetail->concept) : "---"]
				],
				[
					"content"	=>	["label"	=>	$resourceDetail->accounts->account!="" ? $resourceDetail->accounts->account.'  - '.$resourceDetail->accounts->description.' ('.$resourceDetail->accounts->content.")" : "---"]
				],
				[
					"content"	=>	["label"	=>	$resourceDetail->amount!="" ? "$".number_format($resourceDetail->amount,2) : "$ 0.00"]
				]
			];
			$modelBody[]	=	$body;
			$countConcept++;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody"	=>	$modelBody, "classEx" => "mt-4"]) @endcomponent
	@php
		$totalValue	=	"";
		if($totalFinal!=0)
		{
			$totalValue	=	number_format($totalFinal,2);
		}
		$model	=
		[
			["label"	=>	"TOTAL:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".$totalValue]]]
		];
	@endphp
	@component('components.templates.outputs.form-details',["modelTable"	=>	$model])@endcomponent
	<div id="invisible"></div>
	@if($request->idCheck != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
		@php
			if($request->idEnterpriseR!="")
			{
				$descriptions		=	"";
				$projectName		=	$request->reviewedProject->proyectName;
				$departamentName	=	App\Department::find($request->idDepartamentR)->name;
				$directionName		=	$request->reviewedDirection->name;
				$enterpriseName		=	App\Enterprise::find($request->idEnterpriseR)->name;
				$reviewAccount		=	App\Account::find($request->accountR);
				if(isset($reviewAccount->account)) 
				{
					$revAcount		=	$reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")";
				}
				else
				{
					$revAcount		=	"Varias";
				}
				if (count($request->labels))
				{
					foreach($request->labels as $label)
					{
						$descriptions	.=	$label->description.", ";
					}
				}
				else
				{
					$descriptions	=	"Sin etiqueta";
				}
			}
			$modelTable	=
			[
				"Revisó"					=>	$request->reviewedUser->name!="" ? $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name : "---",
				"Nombre de la Empresa"		=>	$enterpriseName!="" ? $enterpriseName : "---",
				"Nombre de la Dirección"	=>	$directionName!="" ? $directionName : "---",
				"Nombre del Departamento"	=>	$departamentName!="" ? $departamentName : "---",
				'Clasificación del gasto'	=>	$revAcount!="" ? $revAcount : "---",
				"Nombre del Proyecto"		=>	$projectName!="" ? $projectName : "---",
				"Etiquetas"					=>	$descriptions!="" ? $descriptions : "---",
				"Comentarios"				=>	$request->checkComment != "" ? htmlentities($request->checkComment) : "Sin comentarios"
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable"	=>	$modelTable])@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "RELACIÓN DE DOCUMENTOS APROBADOS"]) @endcomponent
		@php
			$body		=	[];
			$modelHead	=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Concepto"],
					["value"	=>	"Clasificación de gasto"],
					["value"	=>	"Importe"]
				]
			];
			$subtotalFinal	=	$ivaFinal = $totalFinal = 0;
			$countConcept	=	1;
			foreach ($request->resource->first()->resourceDetail as $resourceDetail)
			{
				$totalFinal	+= $resourceDetail->amount;
				$body	=
				[
					[
						"content"	=>	["label"	=>	$countConcept!="" ? $countConcept : "---"]
					],
					[
						"content"	=>	["label"	=>	$resourceDetail->concept!="" ? htmlentities($resourceDetail->concept) : "---"]
					],
					[
						"content"	=>	["label"	=>	$resourceDetail->accountsReview->account!="" ? $resourceDetail->accountsReview->account.' - '.$resourceDetail->accountsReview->description.' ('.$resourceDetail->accountsReview->content.")" : "---"]
					],
					[
						"content"	=>	["label"	=>	$resourceDetail->amount!="" ? '$'.number_format($resourceDetail->amount,2) : "$ 0.00"]
					]
				];
				$modelBody[]	=	$body;
				$countConcept++;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
	@endif
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$comentaries	=	"Sin comentarios";
			if($request->authorizeComment != "")
			{
				$comentaries	=	htmlentities($request->authorizeComment);
			}
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name!="" ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : "---",
				"Comentarios"	=>	$comentaries,
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable"	=>	$modelTable]) @endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->resource->first()->total;
		$iva			=	0;
		$subtotal		=	0;
		$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount') : 0;
		$subtotalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal') : 0;
		$ivaPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva') : 0;
	@endphp
	@if($request->paymentsRequest()->exists())
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$body		=	[];
			$modelHead	=	[];
			$modelBody	=	[];
			$modelHead	=
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
				$componentDoc	=	[];
				if (count($pay->documentsPayments))
				{
					foreach ($pay->documentsPayments as $doc)
					{
						$componentDoc[] =
						[
							"kind" 			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"label" 		=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"target=\"_blank\" title=\"".$doc->path."\""." href=".asset('docs/payments/'.$doc->path)
						];
					}
				}
				else
				{
					$componentDoc	=
					[
						[
							"kind"	=>	"components.labels.label",
							"label"	=>	"Sin documento"
						]
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account!="" ? $pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")" : "---"]
					],
					[
						"content"	=>	["label"	=>	$pay->amount!="" ? "$".number_format($pay->amount,2) : "---"]
					],
					[
						"content"	=>	$componentDoc
						
					],
					[
						"content"	=>	["label"	=>	$pay->paymentDate!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y') : "---"]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"label"			=>	"<span class='icon-search'></span>",
							"classEx"		=>	"follow-btn",
							"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-payment=\"".$pay->idpayment."\" data-target=\"#viewPayment\" data-target=\"#viewPayment\""
						],
						[
							"kind"			=>	"components.inputs.input-text",
							"classEx"		=>	"idpayment",
							"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\""
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead"	=>	$modelHead, "modelBody"	=>	$modelBody, "classEx" => "mt-4"]) @endcomponent
		@php
			$model	=
			[
				["label"	=>	"Total pagado",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totalPagado\"",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta",		"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"resta\"",		"label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			]
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable"=>$model])@endcomponent
	@endif
	@component('components.inputs.input-text', ["attributeEx"	=>	"type=\"hidden\" id=\"restaTotal\" 		value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx"	=>	"type=\"hidden\" id=\"restaSubtotal\" 	value=\"".round(($subtotal)-$subtotalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx"	=>	"type=\"hidden\" id=\"restaIva\" 		value=\"".round(($iva)-$ivaPagado,2)."\""]) @endcomponent
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
		$('.amount,.descuento').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="exchange_rate"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
	});
</script>
@endsection
