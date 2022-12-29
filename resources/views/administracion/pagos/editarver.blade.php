@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor', ["label" => "BUSCAR PAGOS"]) @endcomponent
		@php
			$values	=
			[
				'enterprise_option_id'	=>	isset($option_id) ? $option_id : "",
				'folio'					=>	isset($folio) ? $folio : '',
				'enterprise_id'			=>	isset($enterpriseid) ? $enterpriseid : "",
				'minDate'				=>	isset($mindate) ? $mindate : '',
				'maxDate'				=>	isset($maxdate) ? $maxdate : '',
				'name'					=>	isset($name) ? $name : '',
			];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label', ["label" =>	"Cuenta:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($account) && isset($enterpriseid) && $account != null)
						{
							$accountData	=	App\Account::find($account);
							$options		=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account." - ".$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "classEx"	=>	"js-account removeselect", "attributeEx" => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo de solicitud:"]) @endcomponent
					@php 
						foreach(App\RequestKind::whereIn('idrequestkind',[1,2,3,5,8,9,11,12,13,14,15,16,17])->orderBy('kind','ASC')->get() as $k)
						{
							if (isset($kind) && $kind == $k->idrequestkind)
							{
								$optionsKind[]	=	["value"	=>	$k->idrequestkind,	"description"	=>	$k->kind, "selected" => 'selected'];
							}
							else
							{
								$optionsKind[]	=	["value"	=>	$k->idrequestkind,	"description"	=>	$k->kind];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsKind, "classEx" => "js-kind", "attributeEx" => "name=\"kind\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Empleado:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($idnominaEmployee) && $idnominaEmployee !="")
						{
							$employeeData	=	App\RealEmployee::find($idnominaEmployee);
							$options		=	$options->concat([["value"	=>	$employeeData->id,	"description"	=>	$employeeData->fullName(),	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "classEx" => "js-employee", "attributeEx" => "multiple=\"multiple\" name=\"idnominaEmployee\""]) @endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					@component('components.buttons.button', ["variant" => "success", "attributeEx" => "type=\"submit\" formaction=\"".route('payments.paymentedit-export')."\"", "classEx" => "export mt-4", "label" => "Exportar a Excel <span class=\"icon-file-excel\"></span>"]) @endcomponent
				@endslot
			@endif
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Tipo de solicitud"],
					["value"	=>	"Empresa"],
					["value"	=>	"Solicitante"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Fecha de pago"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Importe"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($requests as $request)
			{
				$requestName	=	"No hay solicitante";
				if ($request->idRequest != "")
				{
					foreach (App\User::where('id',$request->idRequest)->get() as $user)
					{
						$requestName	=	$user->fullName();
					}
				}
				foreach (App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{
					$elaborateName	=	$elaborate->fullName();
				}
				$accountNumber	=	"Varias";
				if (isset($request->accountsReview->account))
				{
					$accountNumber	=	$request->accounts->account.' - '.$request->accounts->description.' ('.$request->accounts->content.")";
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$request->folio]
					],
					[
						"content"	=>	["label"	=>	$request->requestkind->kind]
					],
					[
						"content"	=>	["label"	=>	$request->requestEnterprise->name]
					],
					[
						"content"	=>	["label"	=>	$requestName]
					],
					[
						"content"	=>	["label"	=>	$elaborateName]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->paymentDate)->format('d-m-Y')]
					],
					[
						"content"	=>	["label"	=>	$accountNumber]
					],
					[
						"content"	=>	["label"	=>	"$".number_format($request->amount,2)]
					],
					[
						"classEx"	=>	"items-center",
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" alt=\"Ver Pago\" title=\"Ver Pago\" href=\"".route('payments.viewpayment',$request->idpayment)."\"",
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"label"			=>	"<span class='icon-pencil'></span>",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" alt=\"Editar Pago\" title=\"Editar Pago\" href=\"".route('payments.showpayment',$request->idpayment)."\"",
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"label"			=>	"<span class='icon-bin'></span>",
								"attributeEx"	=>	"type=\"button\" title=\"Eliminar Pago\" data-payment-id=\"$request->idpayment\"",
								"classEx" 		=> "deletePayment"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"idnominaEmployee\" value=\"".$request->idnominaEmployee."\""
							]
						]
					],
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found', ["attributeEx" => "id=\"not-found\""]) @endcomponent
	@endif
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		generalSelect({'selector': '.js-employee', 'model': 20});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-kind",
					"placeholder"				=> "Seleccione el tipo de solicitud",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('change','.js-enterprise',function()
		{
			$('.js-account').empty();
		})
		.on('click','.deletePayment',function()
		{
			payment_id = $(this).attr('data-payment-id');
			url	=	'{{url('/administration/payments/delete/')}}/'+payment_id+'/pay';
			$('#container-alta').attr('action',url).submit();
		});
	});
</script>
@endsection
