@extends('layouts.child_module')
@section('data')
	@isset($request)
		@component("components.forms.form", ["attributeEx" => "action=\"".route('project-stages.update', $request->id)."\" method=\"POST\" id=\"container-alta\""])
		@slot("methodEx") PUT @endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('project-stages.store')."\" method=\"POST\" id=\"container-alta\""])
	@endisset
		@component('components.labels.title-divisor') DATOS DE FASES DEL PROYECTO @endcomponent
		@component("components.labels.subtitle") Para {{ (isset($request)) ? "editar la fase" : "agregar una fase nueva" }} es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Fase: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						name = "name"
						placeholder = "Ingrese la fase"
						value = "{{isset($request) ? $request->name : ''}}"
						data-validation="server"
						data-validation-url="{{ route("project-stage.validation") }}"
						@isset($request) data-validation-req-params="{{ json_encode(array('oldStage' => $request->id)) }}" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						type = "text"
						name = "description"
						rows = "8"
						data-validation = "required" 
						placeholder = "Ingrese la descripción"
					@endslot
					@isset($request)
						{{$request->description}}
					@endisset
				@endcomponent
			</div>
		@endcomponent
		
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
					@endslot
					@isset($request) ACTUALIZAR @else REGISTRAR @endisset
				@endcomponent
				@isset($request)
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
							name = "borrar"
						@endslot
						Borrar campos
					@endcomponent
				@endisset
			</div>
		
	@endcomponent
@endsection
@section('scripts')
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				form        : '#container-alta',
				modules		: 'security',
				onError		: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{	
					if($('[name="description"]').val().length > 300)
					{
						$('[name="description"]').removeClass('valid').addClass('error');
						swal('','Por favor ingrese una descripción menor de 300 caracteres.','error');
						return false;
					}
					else
					{
						$('[name="description"]').removeClass('valid').removeClass('error');	
						return true;
					}
				}
			});
		}
		$(document).ready(function()
		{
			validate();
		});
	</script>
@endsection
