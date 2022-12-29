@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") NUEVA DIRECCIÓN @endcomponent
	@component("components.labels.subtitle") Para agregar una dirección nueva es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('area.store')."\" "])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "name"])
					@slot("attributeEx")
						type="text" 
						name="name" 
						placeholder="Ingrese el nombre" 
						data-validation="server"
						data-validation-url="{{ route("area.validationname") }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Responsable: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "responsable"])
					@slot("attributeEx")
						type="text" 
						name="responsable"
						placeholder="Ingrese el responsable"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "description"])
					@slot("attributeEx")
						name="detail" 
						id="description" 
						placeholder="Ingrese la descripción"
						rows="6"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button") 
				@slot("attributeEx")
					type="submit" name="enviar"
				@endslot
				REGISTRAR
			@endcomponent
			@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
				@slot("attributeEx")
					type="reset" name="borra"
				@endslot
				BORRAR CAMPOS
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
				modules: 'security',
				form   : '#container-alta',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
			$(document).on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $('#container-alta');
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
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			});
		});
	</script>
@endsection
