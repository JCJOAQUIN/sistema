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
		$requestAccountOrigin	=	App\Account::find($request->movementsEnterprise->first()->idAccAccOrigin);
		$requestAccount			=	App\Account::find($request->movementsEnterprise->first()->idAccAccDestiny);
		$modelTable				=
		[
			["Folio",							$request->folio],
			["Título y fecha",					htmlentities($request->movementsEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->datetitle)->format('d-m-Y')],
			["Fiscal",							$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por",					$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen",					App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOrigin)->name],
			["Clasificación del Gasto Origen",	$requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
			["Empresa Destino",					App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud de ".$request->requestkind->kind]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->movementsEnterprise->first()->typeCurrency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->movementsEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$".number_format($request->movementsEnterprise->first()->amount,2)
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->movementsEnterprise->first()->documentsMovements)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach ($request->movementsEnterprise->first()->documentsMovements as $doc)
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
						
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')]
					],
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documento"];
			$body		=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" =>"mt-4"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$requestAccountOrigin	=	App\Account::find($request->movementsEnterprise->first()->idAccAccOriginR);
		$requestAccount			=	App\Account::find($request->movementsEnterprise->first()->idAccAccDestinyR);
		$modelTable				=
		[
			"Revisó"								=>	isset($request->reviewedUser->name) && $request->reviewedUser->name!="" ? $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name : "---",
			"Nombre de la Empresa de Origen"		=>	isset($request->movementsEnterprise->first()->idEnterpriseOriginR) ? App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOriginR)->name : "---",
			"Clasificación del Gasto de Origen"		=>	isset($requestAccountOrigin->account) && $requestAccountOrigin->account!="" ? $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")" : "---",
			"Nombre de la Empresa de Destino"		=>	$request->movementsEnterprise->first()->idEnterpriseDestinyR !="" ? App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseDestinyR)->name : "---",
			"Clasificación del Gasto de Destino"	=>	isset($requestAccount->account) && $requestAccount->account!="" ? $requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")" : "---",
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable =
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->movementsEnterprise->first()->amount;
		$totalPagado	=	0;
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
					["value"	=>	"Fecha"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($request->paymentsRequest as $pay)
			{
				if (count($pay->documentsPayments)>0)
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
							"attributeEx"	=>	"target='_blank' href=".asset('docs/payments/'.$doc->path)."title=".$doc->path
						];
					}
				}
				else
				{
					$componentsExt	=	["label"	=>	"Sin documento"];
				}
				$body =
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
							"label"	=>	'$'.number_format($pay->amount,2)
						]
					],
					[
						"content"	=>	$componentsExt
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					],
					[
						"content"	=>
						[
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=>	"secondary",
								"label" 		=> "<span class='icon-search'></span>",
								"attributeEx" 	=> "type=\"button\" data-toggle=\"modal\"	data-target=\"#viewPayment\" data-payment=\"".$pay->idpayment."\"",
								"classEx" 		=> "follow-btn"
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx" 		=> "idpayment",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$pay->idpayment."\""
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
			@endslot
		@endcomponent
		@php
			$modelTable =
			[
				["label"	=>	"Total pagado",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label" => "$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta",		"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label" => "$ ".number_format(($total)-$totalPagado,2)]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaTotal\" value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"restaSubtotal\" value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
@endsection 
@section('scripts') 
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}"> 
<script src="{{ asset('js/jquery-ui.js') }}"></script> 
<script src="{{ asset('js/jquery.numeric.js') }}"></script> 
<script src="{{ asset('js/datepicker.js') }}"></script> 
<script type="text/javascript">
	$(document).ready(function()
	{
		$('input[name="exchange_rate"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$(function()
		{
			$('.datepicker').datepicker(
			{
				dateFormat : 'dd-mm-yy'
			});
		});
	});
</script>
@endsection 