@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DE WBS @endcomponent
		@component("components.labels.subtitle") Para agregar un WBS nuevo es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label", ["label" => "Seleccione el proyecto:"]) @endcomponent
				@component("components.inputs.select",["attributeEx" => "name=\"projects[]\" multiple=\"multiple\" id=\"multiple-projects\" data-validation=\"required\"", "classEx" =>  "js-projects removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["label" => "Código:"]) @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						readonly 
						id = "inputCode" 
						name = "code" 
						placeholder = "Ingrese el código" 
						data-validation = "server" 
						data-validation-url = "{{ route('wbs.code.validation') }}"
					@endslot
				@endcomponent				
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["label" => "Nombre:"]) @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						readonly 
						id = "inputName" 
						name = "name" 
						placeholder = "Ingrese el nombre" 
						data-validation = "required"
					@endslot
				@endcomponent
			</div>
		@endcomponent	
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "Registrar"]) @endcomponent
			@component("components.buttons.button", ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\"", "label" => "Borrar campos", "classEx" => "btn-delete-form"]) @endcomponent
		</div>
	@endcomponent	
@endsection

@section('scripts')
	<script>
		function validate()
		{
			$.validate(
			{
				modules : 'security',
				form	: '#container-alta',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		}
		$(document).ready(function()
		{
			validate();
			generalSelect({'selector': '.js-projects', 'model': 24});

			$(document).on('change', '.js-projects', function()
			{
				if($('option:selected',this).val() != "" && $('option:selected',this).val() != undefined)
				{
					val = $('option:selected',this).val();
					$('#inputCode').removeAttr('readonly').attr("data-validation-req-params", '{"proyect_id":"'+val+'"}');
					$('#inputName').removeAttr('readonly');			
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title       : "Limpiar formulario",
					text        : "¿Confirma que desea limpiar el formulario?",
					icon        : "warning",
					buttons     : ["Cancelar","OK"],
					dangerMode  : true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						
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
