@extends('layouts.child_module')

@section('css')
	<style>
		.container-percent:after
		{
			content: '%';
			position: absolute;
			top: 0.5rem;
			right: 0.3rem;
			color: #6e6e6e;
		}
		.container-uma:after
		{
			content: 'UMA';
			position: absolute;
			top: 0.5rem;
			right: 0.4rem;
			color: #6e6e6e;
		}
		.container-uma-as:after
		{
			content: 'UMA * año de servicio';
			position: absolute;
			top: 0.5rem;
			right: 0.4rem;
			color: #6e6e6e;
		}
		.container-pes:after
		{
			content: 'pesos';
			position: absolute;
			top: 0.5rem;
			right: 0.4rem;
			color: #6e6e6e;
		}
		.container-day:after
		{
			content: 'días';
			position: absolute;
			top: 0.5rem;
			right: 0.4rem;
			color: #6e6e6e;
		}
		.container-percent input:valid
		{
			background-position: right 1.3rem center;
		}
		.container-uma input:valid
		{
			background-position: right 2.8rem center;
		}
		.container-uma-as input:valid
		{
			background-position: right 10.8rem center;
		}
		.container-pes input:valid
		{
			background-position: right 3.3rem center;
		}
		.container-day input:valid
		{
			background-position: right 2.5rem center;
		}
	</style>
@endsection

@section('data')
	@php
		$categoryParam		= '';
		$subCategoryParam	= '';
		$flag 				= '';
	@endphp
	@component("components.labels.subtitle") Para editar los parametros es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('parameter.update')."\" method=\"POST\" id=\"parameter_form\""])
		@foreach(App\Parameter::all()->sortBy(function($item) {return $item->category.'-'.$item->sub_category.'-'.$item->parameter_name;}) as $categItem)
			@if($categoryParam != $categItem['category'])
				@if ($flag != '') @endcomponent @endif
				<div class="text-center col-span-2 md:col-span-4">
					@php
						$categoryParam	= $categItem['category'];
					@endphp
					@component('components.labels.title-divisor') {{$categoryParam}} @endcomponent
				</div>
				@if ($flag != '') @component("components.containers.container-form") @endif
			@endif
				
			@if($subCategoryParam != $categItem['sub_category'] && $categItem['sub_category'] != '')
				@endcomponent
				<div class="text-left col-span-2 md:col-span-4">
					@php
						$subCategoryParam	= $categItem['sub_category'];
					@endphp

					@component("components.labels.subtitle")
						{{$subCategoryParam}}
					@endcomponent
				</div>
				@component("components.containers.container-form")
			@endif
				
			@if($flag == "") 
				@component("components.containers.container-form")
			@endif	

			<div class="col-span-2 inp">
				@component("components.labels.label")
					@if($categItem['prefix'] != '')
						{{ $categItem['description'] }}
						( {{$categItem['prefix']}} ):
					@else
						{{ $categItem['description'] }}:		
					@endif
				@endcomponent
				@php
					$additionalClass = '';
					if($categItem['suffix'] != '')
					{
						switch ($categItem['suffix'])
						{
							case '%':
								$additionalClass = 'container-percent';
								break;
							case 'UMA':
								$additionalClass = 'container-uma';
							break;
							case 'UMA * año de servicio':
								$additionalClass = 'container-uma-as';
								break;
							case 'pesos':
								$additionalClass = 'container-pes';
								break;
							case 'días':
								$additionalClass = 'container-day';
								break;
						}
					}
				@endphp
				<div class="relative {{$additionalClass}}">
					@component("components.inputs.input-text")
						@slot('attributeEx')
							type  = "text"
							name  = "parameter[{{$categItem['parameter_name']}}]" 
							value = "{{ $categItem['parameter_value']}}" 
							@if($categItem['validation'] != '') 
								{!!$categItem['validation']!!}
							@endif
						@endslot
						@if($categItem['suffix'] != '' && $categItem['suffix'] == '%')
							@slot('classEx')
								porc
							@endslot
						@endif
					@endcomponent
				</div>
			</div>

			@php $flag = $categItem['category'] @endphp

			@if ($flag != $categItem['category'])
				@endcomponent
			@endif

			@if ($loop->last)
				@endcomponent
			@endif
		@endforeach

		@php
			$modelHead = ["Años de servicios","Días de vacaciones"];
			foreach(App\ParameterVacation::all() as $vac)
			{
				$body = 
				[
					[
						"classEx" 	=> "grid content-center",
						"content"   =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $vac->text
							]
						]
					],
					[
						"classEx" 	=> "p-2",
						"content"   =>
						[
							[
								"kind"  		=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"text\" name=\"paramVac[$vac->id]\" value=\"$vac->days\" data-validation=\"number required\"",
								"classEx"		=> "border-gray-300"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp

		@component('components.labels.title-divisor') Tablas de vacaciones @endcomponent
		@AlwaysVisibleTable(["modelHead" => $modelHead, "modelBody" => $modelBody, "variant" => "default", "attributeEx" => "id=\"table\""]) @endAlwaysVisibleTable
	

		@component('components.labels.title-divisor') LÍMITES PARA CÁLCULO DE ISR @endcomponent

		@php
			$isrArr = [
				['Semanal -7 días','2. Tarifa aplicable cuando hagan pagos que correspondan a un periodo de 7 días.',7],
				['Quincenal','4. Tarifa aplicable cuando hagan pagos que correspondan a un periodo de 15 días.',15],
				['Mensual','5. Tarifa aplicable para el cálculo de los pagos provisionales mensuales.',30]
			];
			$subsidyArr = [
				['Semanal -7 días','2. Subsidio aplicable cuando hagan pagos que correspondan a un periodo de 7 días.',7],
				['Quincenal','4. Subsidio aplicable cuando hagan pagos que correspondan a un periodo de 15 días.',15],
				['Mensual','5. Subsidio aplicable cuando hagan pagos que correspondan a un periodo Mensual.',30]
			];
		@endphp

		@foreach($isrArr as $i)

			@component("components.labels.subtitle")
				{{ $i[0] }}
			@endcomponent

			@component("components.labels.label")
				{{ $i[1] }} 
				@slot('classEx')
					font-bold
					pt-4
				@endslot
			@endcomponent

			@php
				$modelHeadLP = 
				[
					[
						"label"   => "Límite inferior",
						"classEx" => "text-sm grid content-center"
					],
					[
						"label"   => "Límite superior (vacío significa que es «En adelante»)",
						"classEx" => "text-sm"
					],
					[
						"label"   => "Cuota fija",
						"classEx" => "text-sm grid content-center"
					], 
					[
						"label"   => "% SobreExcedente",
						"classEx" => "text-sm grid content-center"
					]
				];

				foreach(App\ParameterISR::where('lapse',$i[2])->get() as $isr)
				{
					$body = 
					[
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramIsrInf[$isr->id]\"  value=\"$isr->inferior\" data-validation=\"number required\" data-validation-allowing=\"float\"",
									"classEx"		=> "border-gray-300"
								]
							]
						],
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramIsrSup[$isr->id]\"  value=\"$isr->superior\" data-validation=\"number required\" data-validation-allowing=\"float\" data-validation-optional=\"true\"",
									"classEx"		=> "border-gray-300"
								]
							]
						],
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramIsrQuo[$isr->id]\"  value=\"$isr->quota\" data-validation=\"number required\" data-validation-allowing=\"float\"",
									"classEx"		=> "border-gray-300"
								]
							]
						],
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramIsrExc[$isr->id]\"  value=\"$isr->excess\" data-validation=\"number required\" data-validation-allowing=\"float\"",
									"classEx"		=> "border-gray-300"
								]
							]
						]

					];
					$modelBodyLP[] = $body;
				}
			@endphp

			@AlwaysVisibleTable(["modelHead" => $modelHeadLP, "modelBody" => $modelBodyLP,"variant" => "default", "attributeEx" => "id=\"tableLP\""]) @endAlwaysVisibleTable
		@endforeach
			

		@component('components.labels.title-divisor') LÍMITES PARA SUBSIDIO @endcomponent
		@foreach($subsidyArr as $i)
			@component("components.labels.subtitle")
				{{$i[0]}}
			@endcomponent

			@component("components.labels.label")
				{{ $i[1] }} 
				@slot('classEx')
					font-bold
					pt-4
				@endslot
			@endcomponent
			
			@php
				$modelHeadLS = 
				[
					[
						"label"   => "Límite inferior",
						"classEx" => "text-sm grid content-center"
					],
					[
						"label"   => "Límite superior (vacío significa que es «En adelante»)",
						"classEx" => "text-sm"
					],
					[
						"label"   => "Subsidio",
						"classEx" => "text-sm grid content-center"
					]
				];

				foreach(App\ParameterSubsidy::where('lapse',$i[2])->get() as $sub)
				{
					$body = 
					[
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramSubInf[$sub->id]\"  value=\"$sub->inferior\" data-validation=\"number required\" data-validation-allowing=\"float\"",
									"classEx"		=> "border-gray-300"
								]
							]
						],
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramSubSup[$sub->id]\"  value=\"$sub->superior\" data-validation=\"number required\" data-validation-allowing=\"float\" data-validation-optional=\"true\"",
									"classEx"		=> "border-gray-300"
								]
							]
						],
						[
							"classEx" 	=> "p-2",
							"content"   =>
							[
								[
									"kind"  		=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"text\" name=\"paramSubSub[$sub->id]\"  value=\"$sub->subsidy\" data-validation=\"number required\" data-validation-allowing=\"float\"",
									"classEx"		=> "border-gray-300"
								]
							]
						]
					];
					$modelBodyLS[] = $body;
				}
			@endphp

			@AlwaysVisibleTable(["modelHead" => $modelHeadLS, "modelBody" => $modelBodyLS, "variant" => "default", "attributeEx" => "id=\"tableLS\""])@endAlwaysVisibleTable
		@endforeach
		
		<div class="text-center">
			@component('components.buttons.button', ["variant"=>"primary"])
				@slot('attributeEx')
					type = "submit"
				@endslot
				Actualizar
			@endcomponent
		</div>
	@endcomponent
@endsection



@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		function validate()
		{
			$.validate(
			{
				form	: '#parameter_form',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					flag = true;
					$('.porc').each(function(i,v)
					{
						if($(this).val() > 100)
						{
							$(this).parent('div').removeClass('has-success').addClass('has-error');
							$(this).removeClass('valid').addClass('error');
							if ($(this).parent('div').find(".help-block").length == 0) 
							{
								$(this).parent().append('<span class="help-block form-error">El valor no puede ser mayor a 100</span>');
							}
							swal('', '{{ Lang::get("messages.form_error") }}', 'error');
							flag = false;
						}
					});

					if(flag == false)
					{
						return false;
					}
					else
					{
						return true;
					}
				}
			});
		}

		$(document).ready(function()
		{
			validate();
			$("form#parameter_form :input").each(function(){
				input = $(this);
				
				if(input.attr('name') && input.attr('name') !== '_token')
				{
					$('input[name="'+input.attr('name')+'"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
				}
			});
		});
	</script>
@endsection
