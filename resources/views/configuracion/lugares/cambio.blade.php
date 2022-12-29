@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR LUGAR @endcomponent
	@component("components.labels.subtitle") Para editar el lugar es necesario colocar el siguiente campo: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('places.update', $places->id)."\" method=\"POST\" id=\"container-alta\""])
		@csrf
		@slot("methodEx")
			PUT
		@endslot
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Lugar de trabajo:"]) @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text"
						name = "places"
						placeholder = "Ingrese el lugar de trabajo" 
						value = "{{ $places->place }}" 
						data-validation = "required server" 
						data-validation-url = "{{ route('places.validation') }}" 
						data-validation-req-params = "{{ json_encode(array('oldPlace'=>$places->id)) }}"
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
				}
			});
		});
	</script>
@endsection
