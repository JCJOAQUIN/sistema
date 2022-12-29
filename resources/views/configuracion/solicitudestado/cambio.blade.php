@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('status.update', $status->idrequestStatus)."\" method=\"POST\" id=\"container-alta\""])
		@slot("methodEx") PUT @endslot
		@component("components.labels.title-divisor") EDITAR ESTADO @endcomponent
		@component("components.labels.subtitle") Para editar el estado es necesario colocar el siguiente campo: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Descripción:"]) @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "description" 
						placeholder = "Ingrese la descripción" 
						value = "{{ $status->description }}" 
						data-validation = "length" 
						data-validation-length = "min1"
					@endslot
				@endcomponent
            </div>
		@endcomponent

		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
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
