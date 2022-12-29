@extends('layouts.child_module')

@section('data')
	@if (isset($blueprints))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('blueprints.update', $blueprints->id)."\" method=\"POST\" id=\"container-alta\""])
			@slot("methodEx") PUT @endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('blueprints.store')."\" method=\"POST\" id=\"container-alta\""])
	@endif
			@component('components.labels.title-divisor') DATOS DEL PLANO @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($blueprints)) ? "editar el plano" : "agregar un plano nuevo" }} es necesario colocar los siguientes campos: @endcomponent
			@component('components.containers.container-form')

				<div class="col-span-2">
					@component('components.labels.label')
						Nombre del plano:
					@endcomponent

					@component('components.inputs.input-text')
						@slot('attributeEx')
							type = "text"
							name = "blueprint_name"
							placeholder = "Ingrese el nombre del plano" 
							data-validation = "server" 
							data-validation-url = "{{ route('blueprints.validation') }}"
							@if(isset($blueprints)) 
								data-validation-req-params = "{{ json_encode(array('oldBlueprints'=>$blueprints->name)) }}" 
								value = "{{$blueprints->name}}"
							@endif
						@endslot

						@slot('classEx')
							blueprintName
						@endslot
					@endcomponent
				</div>

				<div class="col-span-2">
					@component('components.labels.label')
						Contrato:
					@endcomponent

					@php
						$options =  collect();
						foreach(App\Contract::orderBy('name','asc')->get() as $contract)
						{
							if(isset($blueprints) && $blueprints->contract_id == $contract->id)
							{
								$options = $options->concat([['value'=>$contract->id, "selected" => "selected", 'description'=>$contract->number . "-" . $contract->name]]);
							}
							else 
							{
								$options = $options->concat([['value'=>$contract->id, 'description'=>$contract->number . "-" . $contract->name]]);										
							}
						}	
					@endphp

					@component("components.inputs.select",["options" => $options]) 
						@slot("attributeEx") 
							name = "contract_id" 
							multiple = "multiple"
							data-validation = "required" 
						@endslot

						@slot("classEx")
							js-contract removeselect
						@endslot						
					@endcomponent

				</div>

				<div class="col-span-2">
					@component('components.labels.label')
						WBS:
					@endcomponent

					@if (isset($blueprints) && !empty($blueprints->contract_id))
						@php
							$contract	=	App\Contract::find($blueprints->contract_id,['id'])->wbs;
						@endphp
					@endif

					@if (isset($blueprints) && !empty($blueprints->contract_id) && count($contract) > 0)
						@php 
							$optionsWBS =  collect();
							foreach($contract as $contract_wbs)
							{
								if(isset($blueprints) && $blueprints->wbs_id == $contract_wbs->id)
								{
									$optionsWBS = $optionsWBS->concat([['value'=>$contract_wbs->id, "selected" => "selected", 'description'=>$contract_wbs->code_wbs]]);
								}
								else 
								{
									$optionsWBS = $optionsWBS->concat([['value'=>$contract_wbs->id, 'description'=>$contract_wbs->code_wbs]]);										
								}
							}
						@endphp
					@endif

					@isset($optionsWBS)
						@component("components.inputs.select",["options" => $optionsWBS]) 
					@else
						@component("components.inputs.select") 
					@endif
							@slot("attributeEx") 
								name = "contract_wbs" 
								multiple = "multiple"
								data-validation = "required" 
							@endslot

							@slot("classEx")
								js-wbs removeselect
							@endslot						
						@endcomponent
				</div>				
			@endcomponent

			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
						name = "send"
					@endslot

					@isset($blueprints)
						Actualizar
					@else
						Registrar
					@endisset
				@endcomponent

				@isset($blueprints)
					@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
						@slot('attributeEx')
							@if(isset($option_id)) 
								href="{{ url(getUrlRedirect($option_id)) }}" 
							@else 
								href="{{ url(getUrlRedirect($child_id)) }}" 
							@endif 
						@endslot
						Regresar
					@endcomponent
				@else
					@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
						@slot("attributeEx")
							type = "reset" 
							name = "borra"
						@endslot
						Borrar campos
					@endcomponent
				@endisset
			</div>
		@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			validate();
			@php
				$selects = collect([
					[
						"identificator"          	=> ".js-contract", 
						"placeholder"            	=> "Seleccione el contrato", 
						"maximumSelectionLength" 	=> "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			generalSelect({'selector':'.js-wbs', 'depends':'.js-contract', 'model':30});

			$(document).on('change','.js-contract', function()
			{
				$(this).parent('.has-error').find('.form-error').remove();
				$(this).removeClass('error');
				$('.js-wbs option').remove();
			})
			.on('change','.js-wbs',function()
			{
				$(this).parent('.has-error').find('.form-error').remove();
				$(this).removeClass('error');
			})
			.on('click','.btn-delete-form',function(e){
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) => {
					if(willClean) {
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					} else {
						swal.close();
					}
				});
			});
		});
		function validate()
		{
			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		}
	</script>
@endsection