@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") NUEVO COMUNICADO @endcomponent
	@component("components.labels.subtitle") Para agregar un comunicado nuevo es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form",["attributeEx" => "action=\"".route('releases.store')."\" id=\"container-alta\" method=\"POST\""])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="title" data-validation="required" placeholder="Ingrese el título"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contenido: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="content" data-validation="required" placeholder="Ingrese el contenido" rows="10"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit"
					name="send"
				@endslot
				REGISTRAR
			@endcomponent
			@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
				@slot("attributeEx")
					type="reset"
					name="borrar"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			validation();
			$(function() 
			{
				$( ".datepicker" ).datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			});
			$(document).on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
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
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			});
		});
		function validation()
		{
			$.validate(
			{
				form		: '#container-alta',
				modules		: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					return true;
				}
			});
		}
	</script>
@endsection