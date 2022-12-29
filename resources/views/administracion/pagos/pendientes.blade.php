@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR PAGOS @endcomponent
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
					<div>
						@component('components.labels.label')
							Cuenta:
						@endcomponent
						@php
							$options	=	collect();
							if (isset($account) && isset($enterpriseid) && $account != null)
							{
								$accountData	=	App\Account::find($account);
								$options		=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account.' - '.$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('classEx')
								js-account
								removeselect
							@endslot
							@slot('attributeEx')
								title="Cuenta"
								multiple="multiple"
								name="account"
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					<div>
						@component('components.labels.label')
							Tipo de solicitud:
						@endcomponent
						@php
							foreach (App\RequestKind::orderBy('kind','asc')->whereIn('idrequestkind',[1,2,3,5,8,9,11,12,13,14,15,16,17])->orderBy('kind','ASC')->get() as $k)
							{
								if (isset($kind) && $kind == $k->idrequestkind)
								{
									$optionRequest[]	=	['value' => $k->idrequestkind, "description" => $k->kind, "selected" => 'selected'] ;
								}
								else
								{
									$optionRequest[]	=	['value' => $k->idrequestkind, "description" => $k->kind, $k->kind];
								}
							}
						@endphp
						@component('components.inputs.select', ['options' => $optionRequest])
							@slot('classEx')
								js-kind
							@endslot
							@slot('attributeEx')
								name="kind"
								multiple="multiple"
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="col-span-2 type_nomina" @if(!isset($type_nomina) && (isset($kind) && $kind != 16)) style="display: none;"@endif>
					<div>
						@component('components.labels.label')
							Tipo de Nómina:
						@endcomponent
						@php
							$optionKind[]	=	["value" => "1", "description" => "Fiscal"];
							isset($type_nomina) && in_array(1,$type_nomina) ? $optionKind[0]["selected"] = "selected" : "";
							$optionKind[]	=	["value" => "2", "description" => "No fiscal"];
							isset($type_nomina) && in_array(2,$type_nomina) ? $optionKind[1]["selected"] = "selected" : "";
						@endphp
						@component('components.inputs.select', ["options" => $optionKind])
							@slot('classEx')
								removeselect js-nomina
							@endslot
							@slot('attributeEx')
								multiple="multiple" name="type_nomina[]"
							@endslot
						@endcomponent
					</div>
				</div>
			@endslot
			@if (count($requests) > 0)
				@slot('export')
					@component('components.buttons.button',['variant' => 'success'])
						@slot('classEx')
							mt-4
						@endslot
						@slot('attributeEx')
							type="submit" formaction="{{ route('payments.export') }}"
						@endslot
						@slot('slot')
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endslot
			@endif
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$modelHead	=	
			[
				[
					['value'	=>	'Folio'],
					['value'	=>	'Tipo de solicitud'],
					['value'	=>	'Título'],
					['value'	=>	'Empresa'],
					['value'	=>	'Solicitante'],
					['value'	=>	'Fecha de autorización'],
					['value'	=>	'Clasificación del gasto'],
					['value'	=>	'Importe'],
					['value'	=>	'Acción']
				]
			];
			foreach ($requests as $request)
			{
				switch ($request->kind)
				{
					case '1':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->purchases->first()->amount;
							$titleRequest	=	$request->purchases->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '2':
							$resta        	= 0;
							$totalPagado  	= $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			= $request->nominas->first()->amount;
							$titleRequest	= $request->nominas->first()->title;
							$resta 			= $total-$totalPagado;
						break;
					case '3':
							$restaTemp		= 0;
							$totalPagado	= $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							if($request->expenses->first()->reembolso>0)
							{
								$total	=	$request->expenses->first()->reembolso;
							}
							elseif($request->expenses->first()->reintegro>0)
							{
								$total	=	$request->expenses->first()->reintegro;
							}
							else
							{
								$total	=	0;
							}
							$restaTemp   	=	$total-$totalPagado;
							$titleRequest	=	$request->expenses->first()->title;
						if($request->expenses->first()->reembolso>0)
							$resta	=	$restaTemp;
						elseif($request->expenses->first()->reintegro>0)
							$resta	=	-$restaTemp;
						else
							$resta	=	0;
						break;
					case '5':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->loan->first()->amount;
							$titleRequest	=	$request->loan->first()->title;
							$resta			=	$total-$totalPagado;
						break;
					case '8':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->resource->first()->total;
							$titleRequest	=	$request->resource->first()->title;
							$resta			=	$total-$totalPagado;
						break;
					case '9':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->refunds->first()->total;
							$titleRequest	=	$request->refunds->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '11':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->adjustment->first()->amount;
							$titleRequest	=	$request->adjustment->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '12':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->loanEnterprise->first()->amount;
							$titleRequest	=	$request->loanEnterprise->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '13':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->purchaseEnterprise->first()->amount;
							$titleRequest	=	$request->purchaseEnterprise->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '14':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->groups->first()->amount;
							$titleRequest	=	$request->groups->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '15':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
							$total			=	$request->movementsEnterprise->first()->amount;
							$titleRequest	=	$request->movementsEnterprise->first()->title;
							$resta			=	($total)-$totalPagado;
						break;
					case '16':
							$resta			=	0;
							$totalPagado	=	$request->paymentsRequest()->exists() ? round($request->paymentsRequest->sum('amount_real'),2) : 0;
							$total			=	round($request->nominasReal->first()->amount,2);
							$titleRequest	=	$request->nominasReal->first()->title;
							$resta			=	round($total-$totalPagado,2);
						break;
				}
				if (isset($request->accountsReview->account))
				{
					$accountDescription	=	$request->accountsReview->account.' - '.$request->accountsReview->description.' ('.$request->accountsReview->content.")";
				}
				elseif (isset($request->accountsReview->account) == false && isset($request->accounts->account))
				{
					$accountDescription	=	$request->accounts->account.' - '.$request->accounts->description.' ('.$request->accounts->content.")";
				}
				else
				{
					$accountDescription	=	"Varias";
				}
				$body	=
				[
					[
						"content"	=>
						[
							"label" =>	$request->folio
						]
					],
					[
						"content"	=>
						[
							"label"	=>	$request->requestkind->kind
						]
					],
					[
						"content"	=>
						[
							"label"	=>	htmlentities($titleRequest),
						]
					],
					[
						"content"	=>
						[
							"label"	=>	$request->kind != 2 && $request->kind != 11 && $request->kind != 12 && $request->kind != 13 && $request->kind != 14 && $request->kind != 15 && $request->kind != 16  ? $request->reviewedEnterprise->name : 'Varias'
						]
					],
					[
						"content"	=>
						[
							"label"	=>	$request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name
						]
					],
					[
						"content"	=>
						[
							"label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->authorizeDate)->format('d-m-Y')
						]
					],
					[
						"content"	=>
						[
							"label"	=>	$accountDescription
						]
					],
					[
						"content"	=>
						[
							"label"	=>	"$".number_format($resta,2)
						]
					],
					[
						"content"	=>
						[
							"kind" 			=>	"components.buttons.button",
							"variant"		=>	"success",
							"label" 		=>	"<span class='icon-pencil'></span>",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('payments.review.edit',$request->folio)."\""
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead"	=>	$modelHead, "modelBody"	=>	$modelBody]) @endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found',["classEx" => "id=\"not-found\""]) @endcomponent
	@endif
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model': 10});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-kind",
					"placeholder"				=> "Seleccione el tipo de solicitud",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprise",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> "[name=\"type_nomina[]\"]",
					"placeholder"				=> "Seleccione el tipo de nómina",
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
		.on('change','.js-kind',function()
		{
			kind = $('option:selected','.js-kind').val();
			if (kind == 16) 
			{
				$('.type_nomina').show();
				@php
					$selects = collect([
						[
							"identificator"				=> "[name=\"type_nomina[]\"]",
							"placeholder"				=> "Seleccione el tipo de nómina",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			}
			else
			{
				$('.type_nomina').hide();
			}
		});
	});
</script>
@endsection
