@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR ETIQUETA @endcomponent
	@component("components.labels.subtitle") Para editar la etiqueta es necesario colocar el siguiente campo: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('labels.update', $label->idlabels)."\" method=\"POST\" id=\"container-alta\""])
		@slot("methodEx")
			PUT
		@endslot
		@component("components.containers.container-form")
        	<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Descripción:"]) @endcomponent
				
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "description" 
						placeholder = "Ingrese la descripción" 
						value = "{{ $label->description }}" 
						data-validation = "server" 
						data-validation-url = "{{ route('labels.validation') }}" 
						data-validation-req-params = "{{ json_encode(array('oldLabel'=>$label->description)) }}"
					@endslot
				@endcomponent
            </div>
		@endcomponent

		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "Actualizar"]) @endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('attributeEx')
					type = "button"
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
				modules	: 'security',
				form	: '#container-alta',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{	
					if($('[name="description"]').val().length > 200)
					{
						$('[name="description"]').removeClass('valid').addClass('error');
						swal('','Por favor ingrese menos de 200 caracteres.','error');
						return false;
					}
					else
					{
						$('[name="description"]').removeClass('valid').removeClass('error');	
						return true;
					}
				}
			});
		});
	</script>
@endsection
