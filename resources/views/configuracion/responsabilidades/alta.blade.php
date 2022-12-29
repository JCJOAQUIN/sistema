@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('responsibility.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DE RESPONSABILIDADES @endcomponent
		@component("components.labels.subtitle") Para agregar una responsabilidad nueva es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Responsabilidad: 
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "responsibility"
						placeholder = "Ingrese la responsabilidad"
					@endslot

					@slot('classEx')
						name
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Descripción:
				@endcomponent

				@component("components.inputs.text-area")
					@slot('attributeEx')
						name = "description" 
						id = "description" 
						placeholder = "Ingrese la descripción"
						rows = "6"
					@endslot
					@slot('classEx')
						description
					@endslot
				@endcomponent 
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"]) 
					@slot("attributeEx")
						type = "button" 
						name = "add" 
						id   = "add"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
		@AlwaysVisibleTable([
			"modelHead" 		=> ["Responsabilidad", "Descripción", "Acción"],
			"modelBody" 		=> [],
			"attributeExBody" 	=> "id=\"body\"",
			"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\"",
			"variant"			=> "default"
		])@endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button") 
				@slot("attributeEx")
					type="submit" 
					name="enviar"
				@endslot
				Registrar
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
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				modules: 'security',
				form: '#container-alta',
				onSuccess : function($form)
				{
					respons	= $('#body .tr').length;
					if(respons > 0)
					{
						return true;
					}
					else
					{
						$('input[name="responsibility"]').addClass('error');
						$('textarea[id="description"]').addClass('error');
						swal('', 'Agregue mínimo una responsabilidad', 'error');
						return false;
					}
				}
			});
		}
		$(document).ready(function() 
		{
			validate();
			$(document).on('click','#add',function()
			{
				that = $(this);
				that.prop('disabled',true);
				resp = $('input[name="responsibility"]').val();
				description = $('textarea[id="description"]').val();
				if(resp == '' && description == '')
				{
					$('input[name="responsibility"]').addClass('error');
					$('textarea[id="description"]').addClass('error');
					swal('', 'Llene los campos necesarios', 'error');
					that.prop('disabled',false);
				}
				else if(resp == '')
				{
					$('input[name="responsibility"]').addClass('error');
					swal('', 'Llene el campo de responsabilidad', 'error');
					that.prop('disabled',false);
				}
				else if(description == '')
				{
					$('textarea[id="description"]').addClass('error');
					swal('', 'Llene el campo de descripción', 'error');
					that.prop('disabled',false);
				}
				else
				{
					responsibility = $('input[name="responsibility"]').val();
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('responsibility.validation') }}',
						data	:  { 'responsibility' : responsibility },
						success : function(response)
						{
							if (response['valid'] == false)
							{
								$('input[name="responsibility"]').addClass('error');
								swal('', 'Esta responsabilidad ya existe, por favor ingrese una diferente.', 'error');
							}
							if(response['valid'] == true)
							{
								flagResponsibility	=	false;
								if ($('.class_responsibility').length>0)
								{
									$('.class_responsibility').each(function (i,v)
									{
										$('#body .tr').each(function (i,v)
										{
											responsibilityName = $(this).find("[name='responsibility[]']").val();
											if(responsibility == responsibilityName)
											{
												flagResponsibility	=	true;
											}
										})
									})
									if (flagResponsibility == true)
									{
										$('.name').removeClass('valid').addClass('error');
										swal('', 'El nombre de la responsabilidad ya ha sido agregado anteriormente', 'error');
									}
									else
									{
										addResponsibility();
									}
								}
								else
								{
									addResponsibility();
								}
							}
							that.prop('disabled',false);
						},
						error : function()
						{
							that.prop('disabled',false);
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					});
				}
			})
			.on('click','.delete-item', function() 
			{
				$(this).parents('.tr').remove();
				rows = $('#body .tr').length;
				if (rows <= 0) 
				{
					$('#table-show').hide();
				}
			});
		});
		function addResponsibility()
		{
			@php 
				$modelHead = ["Responsabilidad", "Descripción", "Acción"];
				$modelBody = 
				[
					[
						"classEx" => "tr",
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx" 	=> "class_responsibility"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"responsibility[]\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx" 	=> "class_description"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"description[]\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "type=\"button\"",
									"classEx"		=> "delete-item",
									"label"			=> "<span class=\"icon-x delete-span\"></span>",
									"variant"		=> "red"
								]
							]
						]
					]
				];
				$table = view('components.tables.alwaysVisibleTable',
				[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"noHead"	=> true,
					"variant"	=> "default"
				])->render();
			@endphp
			table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
			resp = $(table);
			resp.find('.class_responsibility').text(responsibility);
			resp.find('[name="responsibility[]"]').val(responsibility);
			resp.find('.class_description').text(description);
			resp.find('[name="description[]"]').val(description);
			$("#table-show").removeAttr('style');
			$('#body').append(resp);
			$('.name').val('');
			$('.name').removeClass('valid');
			$('.name').removeClass('error');
			$('textarea[id="description"]').val('');
			$('textarea[id="description"]').removeClass('valid');
			$('textarea[id="description"]').removeClass('error');
		}
	</script>
@endsection