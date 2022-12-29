@extends('layouts.child_module')

@section('data')
	@if(isset($job_position))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('job-positions.update', $job_position->id)."\" method=\"POST\" id=\"container-alta\""])
		@slot('methodEx') PUT @endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('job-positions.store')."\" method=\"POST\" id=\"container-alta\""])
	@endif
			@component('components.labels.title-divisor') DATOS DEL PUESTO DE TRABAJO @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($job_position)) ? "editar el puesto" : "agregar un puesto nuevo" }} es necesario colocar los siguientes campos: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label")
						Nombre:
					@endcomponent
					@component("components.inputs.input-text")
						@slot('attributeEx')
							type = "text" 
							name = "name"
							placeholder = "Ingrese el nombre" 
							data-validation = "server" 
							data-validation-url = "{{ route('job-positions.validation') }}" 
							@if(isset($job_position))
								value = "{{ $job_position->name }}"  
								data-validation-req-params = "{{ json_encode(array('oldJob'=>$job_position->id)) }}" 
							@endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$options = collect();
						foreach(App\JobPosition::all() as $jp)
						{
							if(isset($job_position) && $job_position->immediate_boss == $jp->id)
							{
								$options = $options->concat([["value" => $jp->id, "description" => $jp->name, "selected" => "selected"]]);
							}
							else {
								$options = $options->concat([["value" => $jp->id, "description" => $jp->name]]);
							}
						}
					@endphp
					@component("components.labels.label")
						Jefe inmediato (Opcional):
					@endcomponent
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"immediate_boss\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Descripción del puesto:
					@endcomponent
					@component("components.inputs.text-area")
						@slot('attributeEx')
							name = "description" 
							placeholder = "Ingrese la descripción del puesto" 
							data-validation = "required"
						@endslot
						@if(isset($job_position)) 
							{{ $job_position->description }} 
						@endif
					@endcomponent
				</div>
			@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\""]) 
				@if(isset($job_position)) 
					Actualizar
				@else 
					Registrar
				@endif
			@endcomponent
			@if(!isset($job_position))
				@component("components.buttons.button", ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borrar\"", "label" => "Borrar campos", "classEx" => "btn-delete-form"]) @endcomponent
			@else
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
			@endif
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
						"identificator"          => '[name="immediate_boss"]', 
						"placeholder"            => "Seleccione el jefe inmediato", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			$(document).on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$('[name="immediate_boss"]').trigger('change').val(null);
					}
					else
					{
						swal.close();
					}
				});
			});
		});
		function validate()
		{
			$.validate(
			{
				modules	: 'security',
				form	: '#container-alta',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		}
	</script>
@endsection