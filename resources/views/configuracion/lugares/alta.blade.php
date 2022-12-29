@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('places.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DE LUGARES DE TRABAJO @endcomponent
		@component("components.labels.subtitle") Para agregar un lugar nuevo es necesario colocar el siguiente campo: @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component("components.labels.label")Lugar de trabajo: @endcomponent
					@component("components.inputs.input-text", ["classEx" => "label"])
						@slot("attributeEx")
							type = "text" 
							name = "places" 
							placeholder = "Ingrese el lugar de trabajo"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"]) 
						@slot("attributeEx")
							type = "button" 
							name = "add" 
							id 	 = "add"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar</span>
					@endcomponent
				</div>
			@endcomponent
		@component('components.labels.title-divisor') LISTA DE LUGARES DE TRABAJO @endcomponent<br>
		@AlwaysVisibleTable([
			"modelHead" 		=> ["Lugar", "Acción"],
			"modelBody"		 	=> [],
			"attributeExBody"	=> "id=\"body\"",
			"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\"",
			"variant"			=> "default"
		])@endAlwaysVisibleTable
		<div class="text-center">
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
		$(document).ready(function()
		{
			validate();
			$('.account').numeric(false);
			$(document).on('click','#add',function()
			{ 
				$(this).attr('disabled','disabled');
				label  	= $('.label').val().trim();
				place  	= $('input[name="places"]').val().trim();
				if (place == '')
				{
					$(this).attr('disabled', false);
					$('input[name="places"]').removeClass('valid').addClass('error'); 
					swal('', 'Por favor llene el campo necesario', 'error');
				}
				else
				{
					$.ajax(
					{
						type   : 'post',
						url    : '{{ route('places.validation') }}',
						data   : { 'places':place },
						success: function(response)
						{
							if (response['valid'] == false && response['message'] == 'Ya existe este lugar de trabajo.')
							{
								$('#add').attr('disabled', false);
								$('input[name="places"]').addClass('error');
								swal('', 'El lugar de trabajo ya existe, por favor verifique sus datos', 'error');
							}
							if (response['valid'] == true)
							{
								yes = 0;
								$('#body .tr').each(function(){
									exist = $(this).find("[name='places[]']").val();
									if (label == exist) 
									{
										yes++;
									}
								});
								if(yes == 0)
								{
									@php 
										$modelHead =	["Lugar", "Acción"];
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
															"attributeEx" 	=> "type=\"hidden\" name=\"places[]\""
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
									plce = $(table);
									plce.find('.class_label').text(place);
									plce.find('[name="places[]"]').val(place);
									$("#table-show").removeAttr('style');
									$('#body').append(plce);
									$('input[name="places"]').val('');
									$('.label').val('');
									$('.label').removeClass('error');
									$('.label').removeClass('valid');
									$('#add').attr('disabled', false);
								}
								else
								{
									$('input[name="places"]').removeClass('valid').addClass('error');
									swal('', 'Ya se ha agregado este lugar en la lista', 'error');
									$('#add').attr('disabled', false);
								}
							}
						},
						error : function()
						{
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
			})
		});
		function validate()
		{
			$.validate(
			{
				modules  : 'security',
				form     : '#container-alta',
				onSuccess: function($form)
				{
					cuentas	= $('#body .tr').length;
					if(cuentas>0)
					{
						return true;
					}
					else
					{	
						labelD  = $('input[name="places"]').removeClass('valid').addClass('error');
						swal('', 'Agregar mínimo un lugar de trabajo', 'error');
						return false;
					}
				}
			});
		}
	</script>
@endsection
