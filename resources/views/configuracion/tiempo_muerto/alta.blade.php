@extends('layouts.child_module')

@section('data')
	@if(isset($request))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('risk-time-category.update', $request->id)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('risk-time-category.store')."\" method=\"POST\" id=\"container-alta\""])
	@endisset
			@component('components.labels.title-divisor') DATOS DE CATEGORÍAS DEL TIEMPO MUERTO @endcomponent
			@component("components.labels.subtitle") Para {{ (isset($request)) ? "editar la categoría" : "agregar una categoría nueva" }} es necesario colocar el siguiente campo: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component("components.labels.label", ["label" => "Categoría:"]) @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							placeholder = "Ingrese la categoría" 
							type  = "text" 
							value = "{{isset($request) ? $request->name : '' }}"
							name  = "name"
							data-validation = "server"
							data-validation-url="{{ route('risk-time-category.validation') }}"
							@if(isset($request))
								data-validation-req-params="{{ json_encode(array('oldCategory' => $request->id)) }}"
							@endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@isset($request)
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
					@component('components.buttons.button', ["variant"=>"primary"])
						@slot('attributeEx')
							type = "submit"
						@endslot
						Actualizar
					@endcomponent
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
			@else
				<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
					@component('components.buttons.button', ["variant"=>"primary"])
						@slot('attributeEx')
							type = "submit"
						@endslot
						Registrar
					@endcomponent
				</div>
			@endisset
		@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
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
		});
	</script>
@endsection