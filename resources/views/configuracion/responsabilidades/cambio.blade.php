@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') DATOS DE RESPONSABILIDADES @endcomponent
	@component("components.labels.subtitle") Para editar la responsabilidad es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('responsibility.update',$responsibility->id)."\" method=\"POST\" id=\"container-alta\""])
		@slot('methodEx') PUT @endslot
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Responsabilidad: 
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "responsibility" 
						value = "{{ $responsibility->responsibility }}" 
						data-validation = "server"
						placeholder = "Ingrese la responsabilidad" 
						data-validation-url = "{{ route('responsibility.validation') }}" 
						data-validation-req-params = "{{ json_encode(array('oldRespo'=>$responsibility->responsibility)) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Descripción:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name = "description" 
						id = "description"
						data-validation = "required"
						placeholder = "Ingrese la descripción" 
						rows = "6"
					@endslot
					{{ $responsibility->description }}
				@endcomponent 
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 py-4">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "Actualizar"]) @endcomponent
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
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">		
		$(document).ready(function() 
		{
			$.validate(
			{
				modules : 'security',
				form	: '#container-alta',
				onError : function($form)
				{
					respons = $('[name="responsibility"]').val();
					description = $('[name="description"]').val();
					if (respons == '' && description == '')
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
						$('[name="responsibility"]').removeClass('valid').addClass('error');
						$('textarea[id="description"]').addClass('error');
						return false;
					}
					else if(respons == '')
					{ 
						swal('', 'Por favor llene el campo de responsabilidad.', 'error');
						$('[name="responsibility"]').removeClass('valid').addClass('error');
						return false;
					}
					else if(description == '')
					{
						swal('', 'Por favor llene el campo de descripción.', 'error');
						$('textarea[id="description"]').addClass('error');
						return false;
					}
					else
					{
						swal('', 'Esta responsabilidad ya existe, por favor ingrese una diferente.', 'error');
					}
				}
			});
		});
	</script>
@endsection