@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR COMUNICADO @endcomponent
	@component("components.labels.subtitle") Para editar el comunicado es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route("releases.update.release",$release->idreleases)."\" id=\"formsearch\""])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="title" class="input-text-large" data-validation="required" placeholder="Ingrese el título" value="{{ $release->title }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contenido: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="content" data-validation="required" placeholder="Ingrese el contenido" rows="10"
					@endslot
					{{$release->content}}
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" 
					name="send" 
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
				REGRESAR
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