@extends('layouts.child_module')
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') Buscar solicitudes @endcomponent
		@php
			$values = 
			[
				'enterprise_option_id' => $option_id, 
				'enterprise_id'        => $enterpriseid, 
				'folio'                => $folio, 
				'name'                 => $name, 
				'minDate'              => $mindate, 
				'maxDate'              => $maxdate
			];
		@endphp  
		@component('components.forms.searchForm', ["values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')Proveedor:@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type        = "text" 
							name        = "provider" 
							id          = "input-search" 
							placeholder = "Ingrese un proveedor" 
							value       = "{{ isset($provider) ? $provider : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Cuenta:@endcomponent
					@php
						$optionsAccount = collect();
						if(isset($enterpriseid))
						{
							foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)
							->where('selectable',1)
							->get() as $acc)
							{
								$description    = $acc->account."-".$acc->description."(".$acc->content.")";
								$optionsAccount = $optionsAccount->concat([['value'=>$acc->idAccAcc, 'description'=>$description]]);
							}
						}
						if(isset($account))
						{
							$accountSelected = collect($optionsAccount->where('value', $account)->first())->put('selected', 'selected');
							$optionsAccount  = $optionsAccount->concat($optionsAccount->where('value', $account)->push($accountSelected));
						}
					@endphp
					@component('components.inputs.select', 
						[
							'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\"", 
							'classEx'     => "js-account removeselect", 
							"options"     => $optionsAccount
						]
					)
					@endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					@component('components.labels.label')
						@component('components.buttons.button',["variant" => "success"])
							@slot('attributeEx')
								type       = "submit"
								formaction = "{{ route('purchase.export.authorization') }}"
							@endslot
							@slot('label')
								<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
							@endslot
						@endcomponent
					@endcomponent
				@endslot
			@endif
		@endcomponent
		@if(count($requests) > 0)
			@php
				$body 			= [];
				$modelBody		= [];
				$modelHead = 
				[
					[
						["value" => "Folio"],
						["value" => "Título"],
						["value" => "Solicitante"],
						["value" => "Empresa"],
						["value" => "Clasificación del gasto"],
						["value" => "Fecha de revisión"],
						["value" => "Acción"],
					]
				];
				foreach($requests as $request)
				{
					$body = 
					[
						[
							"content" => 
							[
								"label" => $request->new_folio != null ? $request->new_folio : $request->folio
							]
						],
						[
							"content" => 
							[
								"label" => $request->purchases()->exists() && $request->purchases->first()->title != null ? htmlentities($request->purchases->first()->title) : 'No hay'
							]
						],
						[
							"content" =>
							[
								"label" => $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay'
							]
						],
						[
							"content" =>
							[
								"label" => isset($request->reviewedEnterprise->name) ? $request->reviewedEnterprise->name : "No hay"
							]
						],
						[
							"content" => 
							[
								"label" => isset($request->accountsReview->account) ? $request->accountsReview->account.' '.$request->accountsReview->description : "No hay"
							]
						],
						[
							"content" =>
							[
								"label" => $request->fDate->format('d-m-Y')
							]
						],
						[
							"content" =>
							[
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"variant"       => "success",
									"label"         => "<span class=\"icon-pencil\"></span>",
									"classEx"       => "follow-btn load-actioner",
									"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=".route('purchase.authorization.edit',$request->folio)
								]
							]
						]
					];
					array_push($modelBody, $body);
				}
			@endphp
			@component('components.tables.table',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
				@slot('classEx')
					text-center
				@endslot
			@endcomponent
			{{$requests->appends($_GET)->links()}}
		@else
			@component("components.labels.not-found")@endcomponent
		@endif
@endsection

@section('scripts')

<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.js-account', 'depends':'.js-enterprise', 'model':10});
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprise", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-type", 
					"placeholder"            => "Seleccione un tipo", 
					"maximumSelectionLength" => "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])
		@endcomponent
	});
</script> 
@endsection

