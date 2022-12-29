@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR DIRECCIÓN @endcomponent
	@component("components.labels.subtitle") Para editar la dirección es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('area.update', $area->id)."\"", "methodEx" => "PUT"])
		@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "name"])
					@slot("attributeEx")
						type="text" 
						name="name" 
						placeholder="Ingrese el nombre" 
						value="{{ $area->name }}" 
						data-validation="server" 
						data-validation-url="{{ route('area.validation') }}" 
						data-validation-req-params="{{ json_encode(array('oldDirection'=>$area->id)) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Responsable: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "responsable"])
					@slot("attributeEx")
						type="text" 
						name="responsable" 
						value="{{ $area->responsable }}" 
						placeholder="Ingrese el responsable" 
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "description"])
					@slot("attributeEx")
						name="details" 
						id="description" 
						placeholder="Ingrese la descripción" 
						rows="6" 
						data-validation="required"
					@endslot
					{{$area->details}}
				@endcomponent
			</div> 
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" 
					name="enviar"
				@endslot
				ACTUALIZAR
			@endcomponent
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
				Regresar
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() 
	{
		$.validate(
		{
			modules		: 'security',
			form: '#container-alta',
			onError	: function($form)
			{
				swal("", "Por favor llene adecuadamente todos los campos que son obligatorios.", "error");
			}
		});
	});
</script>
@endsection