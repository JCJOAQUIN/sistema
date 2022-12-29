@section('data')
	@php 
		$user		=	App\User::find($request->idRequest);
		$enterprise	=	App\Enterprise::find($request->idEnterprise);
		$area		=	App\Area::find($request->idArea);
		$department	=	App\Department::find($request->idDepartment);
		$account	=	App\Account::find($request->account);
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
			["Título y fecha:",	htmlentities($request->loan->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loan->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable, "title" => "Detalles de la Solicitud"]) @endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL SOLICITANTE"]) @endcomponent
	@php
		foreach($request->loan as $loan)
		{
			foreach(App\User::where('id',$loan->idUsers)->get() as $user)
			{
				$nameComplete = $user->name." ".$user->last_name." ".$user->scnd_last_name;
			}
			$payment	=	$loan->paymentMethod->method!="" ? $loan->paymentMethod->method : "---";
			$reference	=	$loan->reference!="" ? htmlentities($loan->reference) : "---";
			$amout		=	'$ '.number_format($loan->amount,2);
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
			{
				if ($loan->idEmployee == $bank->idEmployee)
				{
					$bankDescription	=	$bank->description!=null ?$bank->description : "---";
					$aliasBank			=	$bank->alias!=null ? $bank->alias : '---';
					$numerCard			=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
					$bankClabe			=	$bank->clabe!=null ? $bank->clabe : '---';
					$bankAccount		=	$bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable	=
		[
			"Nombre"			=>	$nameComplete,
			"Forma de pago"		=>	$payment,
			"Referencia"		=>	$reference,
			"Importe"			=>	$amout,
			"Banco"				=>	$bankDescription,
			"Alias"				=>	$aliasBank,
			"Número de tarjeta"	=>	$numerCard,
			"CLABE"				=>	$bankClabe,
			"Número de cuenta"	=>	$bankAccount,
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])
		@slot('classEx')
			employee-details
		@endslot
	@endcomponent
	@php
		foreach ($request->loan as $loan)
		{
			$idUser	=	$loan->idUsers;
		}
	@endphp
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="employee_number" id="efolio" placeholder="Ingrese el número de empleado" value="{{$idUser}}"
		@endslot
		@slot('classEx')
			employee_number
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$reviewAccount		=	App\Account::find($request->accountR);
		$labelDescription	=	"";
		if (count($request->labels))
		{
			$counter	=	1;
			foreach ($request->labels as $label)
			{
				$labelDescription	.=	$label->description.($counter<count($request->labels) ? (", ") : "");
				$counter++;
			}
		}
		else
		{
			$labelDescription	=	"Sin etiqueta";
		}
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	$reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")",
			"Etiquetas"					=>	$labelDescription,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		]
	@endphp
	@component('components.templates.outputs.table-detail-single', ['modelTable' => $modelTable])
		@slot('classEx')
			employee-details
		@endslot
	@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE AUTORIZACIÓN"]) @endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name!="" ? $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name : "---",
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			]
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
			@slot('classEx')
				employee-details
			@endslot
		@endcomponent
	@endif
	@php
		$payments 		=	App\Payment::where('idFolio',$request->folio)->get();
		$total 			=	$request->loan->first()->amount;
		$totalPagado 	=	0;
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->loan->first()->amount;
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
				}
				else
				{
					$componentBtnDoc	=	["label"	=>	"Sin documento"];
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
						"content"	=>	$componentBtnDoc
					],
					[
						"content"	=>	["label"	=>	$pay->paymentDate!="" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y') : "---"]
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
				["label"	=>	"Total pagado",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta",		"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@component('components.inputs.input-text',	["attributeEx"	=>	"type=\"hidden\"	id=\"restaSubtotal\"	value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
	@component('components.inputs.input-text',	["attributeEx"	=>	"type=\"hidden\"	id=\"restaTotal\"		value=\"".round(($total)-$totalPagado,2)."\""]) @endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
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
		$('.card_number,.destination_account,.destination_key,.employee_number').numeric(false);    // números
		$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
	});
</script>
@endsection
