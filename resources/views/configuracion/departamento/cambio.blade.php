@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR DEPARTAMENTO @endcomponent
	@component("components.labels.subtitle") Para editar el departamento es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('department.update',$department->id)."\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				<p>
					@component("components.labels.label") Nombre: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="text" name="name" placeholder="Ingrese el nombre" value="{{ $department->name }}" data-validation="server" data-validation-url="{{ url('configuration/department/validate') }}" data-validation-req-params="{{ json_encode(array('oldName'=>$department->name)) }}"
						@endslot
					@endcomponent
				</p>
			</div>
			<div class="col-span-2">
				<p>
					@component("components.labels.label") Detalles: @endcomponent
					@component("components.inputs.text-area", ["classEx" => "description"])
						@slot("attributeEx")
							name="details" id="description" placeholder="Ingrese los detalles" rows="6" data-validation="required"
						@endslot
						{{ $department->details }}
					@endcomponent
				</p>
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" name="enviar" 
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
	$.validate(
	{
		modules: 'security',
		form   : '#container-alta',
		onError: function($form)
		{
			swal('', '{{ Lang::get("messages.form_error") }}', 'error');
		}
	});
</script>
@endsection