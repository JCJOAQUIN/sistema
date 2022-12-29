@extends('layouts.child_module')
@section('data')
	@isset($discipline)
        @component("components.forms.form", ["attributeEx" => "action=\"".route('discipline.update',$discipline->id)."\" method=\"POST\" id=\"container-alta\""])
		@slot('methodEx')
			PUT
		@endslot
    @else
        @component("components.forms.form", ["attributeEx" => "action=\"".route('discipline.store')."\" method=\"POST\" id=\"container-alta\""])
    @endisset
			@component('components.labels.title-divisor') DATOS DE DISCIPLINA @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($discipline)) ? "editar la disciplina" : "agregar una disciplina nueva" }} es necesario colocar los siguientes campos: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label")
						Indicador:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text" 
							name="indicator" 
							placeholder="Ingrese el indicador" 
							data-validation="server" 
							data-validation-url="{{ route("discipline.indicator") }}" 
							@if(isset($discipline)) 
								value="{{ $discipline->indicator }}"  
								data-validation-req-params="{{json_encode(array('oldIndicator'=>$discipline->id))}}" 
							@endif
						@endslot

						@slot('classEx')
							indicator
						@endslot
					@endcomponent
				</div>

				<div class="col-span-2">
					@component("components.labels.label")
						Descripción:
					@endcomponent

					@component("components.inputs.text-area")
						@slot('attributeEx')
							data-validation="required" 
							name="descriptiondiscipline" 
							id="description" 
							placeholder="Ingrese la descripción" 
							rows="6"
						@endslot

						@slot('classEx')
							descriptiondiscipline
						@endslot
						@if(isset($discipline))
							{{ $discipline->name }}
						@endif
					@endcomponent
				</div>
			@endcomponent


			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
                @component('components.buttons.button', ["variant"=>"primary"])
                    @slot('attributeEx')
                        type = "submit"
						name="enviar" 
                    @endslot
					@if(isset($discipline)) Actualizar @else Registrar @endif
					@slot('classEx')
						send
					@endslot
                @endcomponent
				@if(isset($discipline)) 
					@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
						@slot('attributeEx')
							@if(isset($option_id)) 
								href="{{ url(getUrlRedirect($option_id)) }}" 
							@else 
								href="{{ url(getUrlRedirect($child_id)) }}"
							@endif 
						@endslot
						@slot('classEx')
							load-actioner
						@endslot
						REGRESAR
					@endcomponent
				@else 
					@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
						@slot("attributeEx")
							type = "reset" 
							name = "borra"
						@endslot
						Borrar campos
					@endcomponent
				@endif
			</div>
		@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">

		function validation()
		{
			$.validate(
			{
				form    : '#container-alta',
				modules : 'security',
				onError : function($form)
				{
					if($('input[name="indicator"]').hasClass('error'))
					{
						swal('', 'Por favor ingrese un indicador válido.', 'error');
					}
					else
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					}
				}
			});
		}

		$(document).ready(function()
		{
			validation();
		});
	</script>
@endsection