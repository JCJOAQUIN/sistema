@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('labels.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DE ETIQUETAS @endcomponent
		@component("components.labels.subtitle") Para agregar una etiqueta nueva es necesario colocar el siguiente campo: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") 
					Descripción: 
				@endcomponent
				@component("components.inputs.input-text", ["classEx" => "label"])
					@slot("attributeEx")
						type = "text" 
						name = "description" 
						placeholder = "Ingrese la descripción" 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"]) 
					@slot("attributeEx")
						type = "button" 
						name = "add" 
						id	 = "add"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor') LISTA DE ETIQUETAS @endcomponent
		@AlwaysVisibleTable([
			"modelHead" 		=> ["Etiqueta", "Acción"],
			"modelBody" 		=> [],
			"attributeExBody" 	=> "id=\"body\"",
			"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\"",
			"variant"			=> "default"
		])@endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button") 
				@slot("attributeEx")
					type = "submit" 
					name = "enviar"
				@endslot
				Registrar
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				modules	: 'security',
				form	: '#container-alta',
				onSuccess : function($form)
				{	
					count = $('#body .tr').length;
					if(count>0)
					{
						return true;
					}
					else
					{	
						labelD  = $('input[name="description"]').addClass('error');
						swal('', 'Agregue mínimo una etiqueta', 'error');
						return false;
					}
				}
			});	
		}
		
		$(document).ready(function()
		{
			validate();

			$('.account').numeric(false);
			$(document).on('click','#add',function()
			{
				labelD  = $('input[name="description"]').val().trim();
				if (labelD == '')
				{
					$('input[name="description"]').removeClass('valid').addClass('error'); 
					swal('', 'Por favor llene el campo necesario', 'error');
				}
				else if($('[name="description"]').val().length > 200)
				{
					$('[name="description"]').removeClass('valid').addClass('error');
					swal('','Por favor ingrese menos de 200 caracteres.','error');
				}
				else
				{
					$('#add').prop('disabled','disabled')
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route('labels.validation') }}',
						data		: { 'description':labelD },
						success		: function(response)
						{	
							if (response['valid'] == false && response['message'] == 'Esta etiqueta ya existe.')
							{
								$('input[name="description"]').addClass('error');
								swal('', 'La etiqueta ingresada ya existe, por favor verifique.', 'error');
							}
							if (response['valid'] == true)
							{
								if ($('#body .tr').length == 0) 
								{
									$('input[name="description"]').addClass('valid'); 
									@php 
										$modelHead =	["Etiqueta", "Acción"];
										$modelBody = 
										[
											[
												"classEx" => "tr",
												[
													"content" => 
													[
														[
															"kind" 		=> "components.labels.label",
															"classEx"   => "class_label"	
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
									labl = $(table);

									labl.find('.class_label').text(labelD);
									labl.find('[name="description[]"]').val(labelD);

									$("#table-show").removeAttr('style');

									$('#body').append(labl);

									$('input[name="description"]').val('');
									$('.label').val('');
									$('.label').removeClass('error');
									$('.label').removeClass('valid');
								}
								else
								{
									yes = 0;
									$('#body .tr').each(function()
									{
										exist = $(this).find("[name='description[]']").val();
										if (labelD == exist) 
										{
											yes++;
										}
									});
									if (yes == 0) 
									{
										$('input[name="description"]').addClass('valid'); 
										@php 
											$modelHead = ["Etiqueta", "Acción"];
											$modelBody = 
											[
												[
													"classEx" => "tr",
													[
														"content" => 
														[
															[
																"kind" 		=> "components.labels.label",
																"classEx" 	=> "class_label"	
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
										labl = $(table);

										labl.find('.class_label').text(labelD);
										labl.find('[name="description[]"]').val(labelD);

										$("#table-show").removeAttr('style');

										$('#body').append(labl);

										$('input[name="description"]').val('');
										$('.label').val('');
										$('.label').removeClass('error');
										$('.label').removeClass('valid');
									}
									else
									{
										swal('', 'Ya ha agregado esta etiqueta en la lista', 'error');
										labelD  = $('input[name="description"]').addClass('error'); 
									}
								}
							}
							$('#add').prop('disabled',false);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#add').prop('disabled',false);
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
			})
		});
	</script>
@endsection
