@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") NUEVO DEPARTAMENTO @endcomponent
	@component("components.labels.subtitle") Para agregar un departamento nuevo es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", [ "attributeEx" => "id=\"form-content\"" ])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "name"])
					@slot("attributeEx")
						type="text" 
						name="name" 
						placeholder="Ingrese el nombre" 
						data-validation="server" 
						data-validation-url="{{ route('department.validation') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Detalles: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "description"])
					@slot("attributeEx")
						name="detail" 
						id="description"
						data-validation="required"
						placeholder="Ingrese los detalles" 
						rows="3"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2", "attributeEx" => "type=\"submit\" name=\"add\" id=\"add\"", "label" => "<span class=\"icon-plus\"></span>Agregar"]) @endcomponent				
			</div>
		@endcomponent
	@endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('department.store')."\" id=\"container-alta\""])
		@php
			$modelHead = ["Nombre", "Descripción", "Acción"];	
		@endphp
		@AlwaysVisibleTable([
				"modelHead"			=> $modelHead,
				"modelBody"			=> [],
				"variant"			=> "default",
				"attributeExBody"	=> "id=\"body\"",
				"attributeEx"		=> "id=\"table-show\""
			])
		@endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" 
					name="enviar"
				@endslot
				REGISTRAR
			@endcomponent
			@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"])
				@slot("attributeEx")
					type="reset" 
					name="borrar"
				@endslot
				Borrar campos
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
			validateDepartment();
			$.validate(
			{
				modules		: 'security',
				form		: '#container-alta',
				onSuccess	: function($form)
				{
					rows	= $('#body .tr').length;
					name	= $('[name="name"]').val();
					detail	= $('[name="detail"]').val();
					if(name == "" && detail == "")
					{
						if(rows > 0)
						{
							return true;
						}
						else
						{
							swal('', 'Agregue mínimo un departamento.', 'error');
							return false;
						}
					}
					else
					{
						swal('', 'Tiene datos sin agregar.', 'error');
						$('[name="name"],[name="detail"]').removeClass('valid');
						return false;
					}
				}
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
						$('.name, .description').val('');
						$('.name, .description').removeClass('error').removeClass('valid');
						$('.name').removeAttr('style');
						$('.form-error').remove();
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','.delete-item', function() 
			{
				$(this).parents('.tr').remove();
			});
		});

		function validateDepartment()
		{
			$.validate(
			{
				modules: 'security',
				form   : '#form-content',
				onError: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess: function()
				{
					name		= $('.name').val().trim();
					error		= $('.name').hasClass('error');
					description = $('textarea[id="description"]').val();
					department	= false;
					$('input[name="name[]"').each(function()
					{
						if($(this).val() == name)
						{
							department = true;
						}
					});
					if (department) 
					{
						swal('', 'Por favor ingrese un departamento diferente.', 'error');
						$('.name').removeClass('valid').addClass('error');
						return false;
					}
					else
					{
						@php
							$modelHead = ["Nombre", "Descripción", "Acción"];
							$modelBody[] = 
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind"		=> "components.labels.label",
											"classEx" 	=> "class_name"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"name[]\" data-validation=\"required\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"		=> "components.labels.label",
											"classEx" 	=> "class_description"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"details[]\" data-validation=\"required\" placeholder=\"Detalles\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"classEx"		=> "delete-item",
											"attributeEx"	=> "type=\"button\"",
											"label"			=> "<span class=\"icon-x\"></span>",
											"variant" 		=> "red"
										]
									]
								]
							];
							$table = view("components.tables.alwaysVisibleTable",[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead" => true,
								"variant" => "default"
							])->render();
						@endphp
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						depto = $(table);
						depto = rowColor('#body', depto);
						depto.find(".class_name").text(name);
						depto.find("[name='name[]']").val(name);
						depto.find(".class_description").text(description);
						depto.find("[name='details[]']").val(description);
						$('#body').append(depto);
						$('.name').val('');
						$('.name').removeClass('valid');
						$('.name').removeClass('error');
						$('textarea[id="description"]').val('');
						$('textarea[id="description"]').removeClass('error').removeClass('valid');
						rows	= $('#body .tr').length;
						if (rows > 0) 
						{
							$('#table-show').show();
						}
					}
					return false;
				}
			});
		}
	</script>
@endsection
