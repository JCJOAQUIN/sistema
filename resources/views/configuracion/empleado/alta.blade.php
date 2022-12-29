@extends('layouts.child_module')
@section('data')
	@if(isset($employee))
		@component("components.forms.form", ["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('employee.update',$employee->id)."\" id=\"employee_form\""])
	@else
		@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('employee.store')."\" id=\"employee_form\""])
	@endif
	@php
		$employee_config = false;
	@endphp
		@include('configuracion.empleado.parcial')
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button", ["classEx" => "updateBtn btn_disable", "variant" => "primary"])
				@slot("attributeEx")
					type="submit" id="create_employee"
				@endslot
				@if(isset($employee)) ACTUALIZAR @else REGISTRAR @endif
			@endcomponent
			@if(!isset($employee))
				@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
					@slot("attributeEx")
						type = "reset" 
						name = "borra"
					@endslot
					Borrar campos
				@endcomponent
			@else 
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
			@endif
		</div>		
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form	: '#employee_form',
				modules	: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					swal(
					{
						icon				: '{{ asset(getenv("LOADING_IMG")) }}',
						button             	: false,
						closeOnClickOutside	: false,
						closeOnEsc         	: false
					});
					if($('[name="rfc"]').val() != '' && $('#tax_regime option:selected').val() == undefined)
					{
						$('#tax_regime').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
						swal('', 'Por favor ingrese un régimen fiscal.', 'error');
						return false;
					}
					flagDocs = false;
					$('#other_documents').find('.path ').each(function()
					{
						if($(this).val() == "")	
						{
							swal('', 'Por favor agregue los documentos faltantes en la sección "OTROS DOCUMENTOS".', 'error');
							flagDocs = true;
							return false;
						}
					});
					if(flagDocs)
					{
						return false;
					}
				}
			});
			@php
				$selects = collect(
					[
						[
							"identificator"				=> "#tax_regime",
							"placeholder"				=> "Seleccione el régimen fiscal",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_payment_way\"]",
							"placeholder"				=> "Seleccione la forma de pago",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_periodicity\"]",
							"placeholder"				=> "Seleccione la periodicidad",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_status_employee\"]",
							"placeholder"				=> "Seleccione el estado del empleado",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"regime_employee\"]",
							"placeholder"				=> "Seleccione el régimen",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_type_employee\"]",
							"placeholder"				=> "Seleccione el tipo de trabajador",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_project\"]",
							"placeholder"				=> "Seleccione el Proyecto/Contrato",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_enterprise\"],[name=\"work_enterprise_old\"]",
							"placeholder"				=> "Seleccione la empresa",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_department\"]",
							"placeholder"				=> "Sleccione el departamento",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_direction\"]",
							"placeholder"				=> "Seleccione la dirección",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],					
						[
							"identificator"				=> "[name=\"work_status_imss\"]",
							"placeholder"				=> "Seleccione el estado de IMSS",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"sys_user\"]",
							"placeholder"				=> "¿Es usuario del sistema?",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_infonavit_discount_type\"]",
							"placeholder"				=> "Seleccione el tipo de descuento",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[
							"identificator"				=> "[name=\"work_alimony_discount_type\"]",
							"placeholder"				=> "Seleccione el tipo de descuento",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						]
					]
				);
			@endphp	
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '[name="cp"]', 'model': 2});
			generalSelect({'selector': '.bank', 'model': 28});
			generalSelect({'selector': '.js-projects', 'model': 21});
			generalSelect({'selector': '[name="state"], [name="work_state"]', 'model': 31});
			generalSelect({'selector': '[name="work_place[]"]', 'model': 38, 'maxSelection': -1});
			generalSelect({'selector': '[name="work_account"]', 'depends': '[name="work_enterprise"]', 'model': 4});
			generalSelect({'selector': '[name="work_subdepartment[]"]', 'model': 39, 'maxSelection': -1});
			generalSelect({'selector': '[name=\"work_employer_register\"]', 'depends': '[name=\"work_enterprise\"]', 'model': 47});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22, 'maxSelection': -1 });
			oldEmail = $('[name="email"]').val();
			$('.account,.clabe').numeric(false);
			$('input[name="cp"]').numeric({ negative:false});
			$('input[name="work_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_net_income"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_viatics"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_camping"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_nomina"]').numeric({negative:false});
			$('input[name="work_bonus"]').numeric({negative:false});
			$('input[name="work_infonavit_credit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_infonavit_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('.clabe,.account,.card').numeric({ decimal: false, negative:false});
			$('input[name="clabe"]').numeric({ decimal: false, negative:false});
			$('input[name="account"]').numeric({ decimal: false, negative:false});
			$('input[name="card"]').numeric({ decimal: false, negative:false});
			$('input[name="work_alimony_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('[name="imss"]').mask('0000000000-0',{placeholder: "Ingrese el # IMSS"});
			$('[name="work_income_date"],[name="work_imss_date"],[name="work_down_date"],[name="work_ending_date"],[name="work_reentry_date"],[name="work_income_date_old"]').datepicker({ dateFormat: "dd-mm-yy" });
			$(document).on('change','[name="work_enterprise"]',function()
			{
				$('[name="work_account"]').html('');
				$('[name="work_employer_register"]').html('');
				
				$('input[name="work_down_date"]').val(null);
				generalSelect({'selector': '[name="work_account"]', 'depends': '[name="work_enterprise"]', 'model': 4});
				generalSelect({'selector': '[name=\"work_employer_register\"]', 'depends': '[name=\"work_enterprise\"]', 'model': 47});				
			})
			.on('click','.updateBtn',function()
			{
				if($('input[name="rfc"]').hasClass('error'))
				{
					swal('', 'Por favor ingrese un RFC válido.', 'error');
					return false;
				}
				else
				{
					return true;
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				console.log(form);
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
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						$('input[name="name"]').val("");
						$('input[name="last_name"]').val("");
						$('input[name="scnd_last_name"]').val("");
						$('input[name="curp"]').val("");
						$('input[name="rfc"]').val("");
						$('input[name="imss"]').val("");
						$('input[name="email"]').val("");
						$('input[name="phone"]').val("");
						$('input[name="street"]').val("");
						$('input[name="number"]').val("");
						$('input[name="colony"]').val("");
						$('input[name="city"]').val("");
						$('input[name="work_position"]').val("");
						$('input[name="work_income_date"]').val("");
						$('input[name="work_imss_date"]').val("");
						$('input[name="work_sdi"]').val("");
						$('input[name="work_income_date_old"]').val("");
						$('input[name="work_nomina"]').val("");
						$('input[name="work_bonus"]').val("");
						$('.pathActioner').parent('div').removeClass('image_success');
						$('.path').val("");
						$('#bank-data-register').children("#bodyEmployee").children('.tr').remove();
						$('#bank-data-register-alimony').children("#bodyAlimony").children('.tr').remove();
						$('#bank-data-register').parent('div').addClass('hidden');
						$('#bank-data-register-alimony').parent('div').addClass('hidden');
						$('#not-found-accounts').removeClass('hidden');
						$('#other_documents').children('.docs-p').remove();
						$('.alimony-container').addClass('hidden');
						$('#accounts-alimony').addClass('hidden');
						$('.infonavit-container').stop(true,true).fadeOut();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('focusout','[name="email"]',function()
			{
				email  = $(this).val();
				object = $(this);

				$.ajax(
				{
					type		: 'post',
					url			: '{{route('employee.email')}}',
					data		: { 'email':email,
									'oldEmail':oldEmail
								  },
					success		: function(data)
					{
						if (data != "") 
						{
							if (object.parent('p').find(".help-block").length == 0) 
							{
								$('input[name="email"]').addClass(data['class']);
								if (data['message'] != "" && data['message'] != undefined) 
								{
									object.parent('p').append('<span class="help-block form-error">'+data['message']+'</span>');
								}
							}
						}
						else
						{
							object.removeClass('error').addClass(data['class']);
						}
					}
				});

			})
			.on('focusout','[name="work_alimony_discount"]', function()
			{
				percentAlimony();
			})
			.on('change','[name="work_alimony_discount_type"]', function()
			{
				percentAlimony();
			})
			.on('change','[name="work_nomina"]',function()
			{
				nomina	= Number($(this).val());
				$('[name="work_bonus"]').val(100-nomina);
			})
			.on('change','[name="work_bonus"]',function()
			{
				bonos	= Number($(this).val());
				$('[name="work_nomina"]').val(100-bonos);
			})
			.on('input','.alias',function()
			{
				if($(this).val() != "")
				{
					$('.alias').addClass('valid').removeClass('error');
				}
				else
				{
					$('.alias').addClass('error').removeClass('valid');
				}
			})
			.on('change','.clabe',function()
			{
				clabe	= $(this).val();
				$('.clabe').removeClass('valid').removeClass('error');
				flag	= false;
				if(clabe != '')
				{ 
					$.ajax(
					{
						type    : 'post',
						url     : '{{ route("employee.clabe.validate") }}',
						data    : {'clabe':clabe},
						success : function(data)
						{
							if(data.length > 0  )
							{
								swal("","El número de clabe ya se encuentra registrado.","error");
								$('.clabe').removeClass('valid').addClass('error');
								$('.clabe').val('');
								flag = true;
							}
						}
					});
				}
			})
			.on('change','.card',function()
			{
				card	= $(this).val();
				$('.card').removeClass('valid').removeClass('error');
				flag	= false;
				if(card != '')
				{ 
					$.ajax(
					{
						type    : 'post',
						url     : '{{ route("employee.card.validate") }}',
						data    : {'card':card},
						success : function(data)
						{
							if(data.length > 0  )
							{
								swal("","El número de tarjeta ya se encuentra registrado.","error");
								$('.card').removeClass('valid').addClass('error');
								$('.card').val('');
								flag = true;
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.card').removeClass('valid').removeClass('error');
							$('.card').val('');
						}
					});
				}
			})
			.on('focusout','.account',function()
			{
				container = $(this).parents('.class-banks');
				bankid	  = container.find('.bank option:selected').val();
				account	  = container.find('.account').val();
				$(this).removeClass('valid').removeClass('error');
				flag = false;
				if(account != '' && bankid != undefined)
				{
					$.ajax(
					{
						type    : 'post',
						url     : '{{ route("employee.account.validate") }}',
						data    : {'account':account,'bankid':bankid},
						success : function(data)
						{
							if(data.length > 0  )
							{
								swal("","El número de cuenta ya se encuentra registrado en este banco.","error");
								container.find('.account').removeClass('valid').addClass('error').val('');
								flag = true;
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							container.find('.account').removeClass('valid').addClass('error').val('');
						}
					});
				}
			})
			.on('change','.bank',function()
			{
				container = $(this).parents('.class-banks');
				bankid	  = container.find('.bank option:selected').val();
				account	  = container.find('.account').val();
				$(this).removeClass('valid').removeClass('error');
				flag = false;
				if(account != '' && bankid != undefined)
				{
					$.ajax(
					{
						type    : 'post',
						url     : '{{ route("employee.account.validate") }}',
						data    : {'account':account,'bankid':bankid},
						success : function(data)
						{
							if(data.length > 0  )
							{
								swal("","El número de cuenta ya se encuentra registrado en este banco.","error");
								container.find('.account').removeClass('valid').addClass('error').val('');
								flag = true;
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							container.find('.account').removeClass('valid').addClass('error').val('');
						}
					});
				}
			})
			.on('click','#add-bank',function()
            {				
				$('.content-bank').find('.bank').parent().find('.form-error').remove();
				$('.content-bank').find('.error').removeClass('error');
                alias       = $('.content-bank').find('.alias').val();
                bankid      = $('.content-bank').find('.bank option:selected').val();
                bankName    = $('.content-bank').find('.bank option:selected').text();
                clabe       = $('.content-bank').find('.clabe').val();
                account     = $('.content-bank').find('.account').val();
                card        = $('.content-bank').find('.card').val();
                branch      = $('.content-bank').find('.branch_office').val();
				if(alias == "" || bankid == undefined)
				{
					if(alias == "")
					{
						$('.content-bank').find('.alias').addClass('error');
					}
					if( bankid == undefined)
					{
						$('.content-bank').find('.bank').addClass('error');
						$('.content-bank').find('.bank').parent().append("<span class='form-error bank-span-error'>Este campo es obligatorio</span>");
					}
					swal('', 'Por favor ingrese los campos requeridos.', 'error');
				}
				else
				{
					if (card == "" && clabe == "" && account == "")
					{
						$('.content-bank').find('.card').removeClass('valid').addClass('error');
						$('.content-bank').find('.clabe').removeClass('valid').addClass('error');
						$('.content-bank').find('.account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if(clabe != "" && clabe.length != 18)
					{
						swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
					}
					else if(card != "" && card.length!=16)
					{
						swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
					}
					else if(account != "" && (account.length>15 || account.length<5))
					{
						swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
					}
                    else 
					{
						flag = false;
						$('#bodyEmployee .tr').each(function()
						{
							name_account 	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							name_card		= $(this).find('[name="card[]"]').val();

							if(clabe!= "" && name_clabe !="" && clabe == name_clabe)
							{
								swal('','La CLABE ya se encuentra registrada para este empleado.','error');
								$('.content-bank').find('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(card != '' && name_card != '' && card == name_card)
							{
								swal('','El número de tarjeta ya se encuentra registrado para este empleado','error');
								$('.content-bank').find('.card').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de cuenta ya se encuentra registrada para este empleado.','error');
								$('.content-bank').find('.account').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						$('#bodyAlimony .tr').each(function()
						{
							name_account	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_card		= $(this).find('[name="card[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							if(clabe != "" && name_clabe != "" && clabe == name_clabe && bankid == name_bank)
							{
								swal('','La CLABE ya se encuentra registrada en la sección de pensión alimenticia, por favor verifique sus datos.','error');
								$('.content-bank').find('clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada en la sección de pensión alimenticia, por favor verifique sus datos.','error');
								$('.content-bank').find('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
							if(card != "" && name_card != "" && card == name_card && bankid == name_bank)
							{
								swal('','El número de Tarjeta ya se encuentra registrada en la sección de pensión alimenticia, por favor verifique sus datos.','error');
								$('.content-bank').find('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						if(!flag)
						{
							@php
								$modelHead = 
								[
									"Alias",
									"Banco",
									"Clabe",
									"Cuenta",
									"Tarjeta",
									"Sucursal",
									"Acción"
								];
								$modelBody = 
								[
									[
										"classEx" => "tr",
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "aliasC",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "aliasI",
													"attributeEx"	=> "type=\"hidden\" name=\"alias[]\" ",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "beneficiaryI",
													"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\" ",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "type_accountI",
													"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"1\" ",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "bankName",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "idEmployee",
													"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\" ",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "bankI",
													"attributeEx"	=> "type=\"hidden\" name=\"bank[]\" ",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "clabeC",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "clabeI",
													"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "accountC",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "accountI",
													"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "cardC",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "cardI",
													"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind" 		=> "components.labels.label",
													"classEx" 	=> "branchC",
													"label"		=> "",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "branchI",
													"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.buttons.button",
													"classEx"		=> "delete-bank",
													"attributeEx"	=> "type=\"button\"",
													"label"			=> "<span class=\"icon-x\"></span>",
													"variant"		=> "dark-red",
												]
											]
										],
									]
								];

								$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead"	=> true,
								])->render();

								$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank = $(table);
							bank = rowColor('#bank-data-register #bodyEmployee', bank);
							bank.find('div').each(function()
							{
								$(this).find(".aliasC").text(alias);
								$(this).find(".aliasI").val(alias);
								$(this).find(".bankName").text(bankName);
								$(this).find(".bankI").val(bankid);
								$(this).find(".clabeC").text(clabe != "" ? clabe : "---");
								$(this).find(".clabeI").val(clabe);
								$(this).find(".accountC").text(account != "" ? account : "---");
								$(this).find(".accountI").val(account);
								$(this).find(".cardC").text(card != "" ? card : "---");
								$(this).find(".cardI").val(card);
								$(this).find(".branchC").text(branch != "" ? branch : "---");
								$(this).find(".branchI").val(branch);
							})
							$('#bank-data-register #bodyEmployee').append(bank);
							$('.content-bank').find('.card').removeClass('error').removeClass('valid').val('');
							$('.content-bank').find('.clabe').removeClass('error').removeClass('valid').val('');
							$('.content-bank').find('.account').removeClass('error').removeClass('valid').val('');
							$('.content-bank').find('.alias').removeClass('error').removeClass('valid').val('');
							$('.content-bank').find('.branch_office').removeClass('error').removeClass('valid').val('');
							$('.content-bank').find('.bank').val(0).trigger("change");
							$('#bank-data-register').parent().removeClass('hidden');
							$('#not-found-accounts').addClass('hidden');
						}
                    }
				}
            })
			.on('click','#add-bank-alimony',function()
			{
				$('.bank-span-error').remove();
				$('.content-bank-alimony').find('.error').removeClass('error');
				beneficiary	= $('.content-bank-alimony').find('.beneficiary').val();
				alias		= $('.content-bank-alimony').find('.alias').val();
				bankid		= $('.content-bank-alimony').find('.bank option:selected').val();
				bankName	= $('.content-bank-alimony').find('.bank option:selected').text();
				clabe		= $('.content-bank-alimony').find('.clabe').val();
				account		= $('.content-bank-alimony').find('.account').val();
				card		= $('.content-bank-alimony').find('.card').val();
				branch		= $('.content-bank-alimony').find('.branch_office').val();
				if(alias == "" || beneficiary == "" || bankid == undefined)
				{
					if(alias == "")
					{
						$('.content-bank-alimony').find('.alias').addClass('error');
					}
					if(beneficiary == "")
					{
						$('.content-bank-alimony').find('.beneficiary').addClass('error');
					}
					if( bankid == undefined)
					{
						$('.content-bank-alimony').find('.bank').addClass('error');
						$('.content-bank-alimony').find('.bank').parent().append("<span class='form-error bank-span-error'>Este campo es obligatorio</span>");
					}
					swal('', 'Por favor ingrese los campos requeridos.', 'error');
				}
				else
				{
					if (card == "" && clabe == "" && account == "")
					{
						$('.content-bank-alimony').find('.card').removeClass('valid').addClass('error');
						$('.content-bank-alimony').find('.clabe').removeClass('valid').addClass('error');
						$('.content-bank-alimony').find('.account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if(clabe != "" && clabe.length != 18)
					{
						swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
					}
					else if(card != "" && card.length != 16)
					{
						swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
					}
					else if(account != "" && (account.length>15 || account.length<5))
					{
						swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");						
					}
					else  
					{
						flag = false;
						$('#bodyEmployee .tr').each(function()
						{
							name_account	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_card		= $(this).find('[name="card[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							if(clabe != "" && name_clabe != "" && clabe == name_clabe && bankid == name_bank)
							{
								swal('','La CLABE ya se encuentra registrada en la cuentas bancarias del empleado, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada en la cuentas bancarias del empleado, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.account').removeClass('valid').addClass('error');
								flag = true;
							}
							if(card != "" && name_card != "" && card == name_card && bankid == name_bank)
							{
								swal('','El número de Tarjeta ya se encuentra registrada en la cuentas bancarias del empleado, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.card').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						$('#bodyAlimony .tr').each(function()
						{
							name_account	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_card		= $(this).find('[name="card[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							if(clabe != "" && name_clabe != "" && clabe == name_clabe && bankid == name_bank)
							{
								swal('','La CLABE ya se encuentra registrada para este beneficiario, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada para este beneficiario, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.account').removeClass('valid').addClass('error');
								flag = true;
							}
							if(card != "" && name_card != "" && card == name_card && bankid == name_bank)
							{
								swal('','El número de Tarjeta ya se encuentra registrada para este beneficiario, por favor verifique sus datos.','error');
								$('.content-bank-alimony').find('.card').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						if(!flag)
						{
							@php
								$modelHead = ["Beneficiario","Alias","Banco","Clabe","Cuenta","Tarjeta","Sucursal","Acción"];
								$modelBody= 
								[
									[
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "beneficiaryC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iBeneficiary",
													"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\"",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "type_accountI",
													"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"2\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "aliasC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iAlias",
													"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "bankName",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "idEmployee",
													"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iBank",
													"attributeEx"	=> "type=\"hidden\" name=\"bank[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "clabeC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iClabe",
													"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "accountC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iAccount",
													"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "cardC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iCard",
													"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"		=> "components.labels.label",
													"classEx"	=> "branchC",
													"label"		=> "",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "iBranch",
													"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
												],
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.buttons.button",
													"classEx"		=> "delete-bank",
													"variant"		=> "dark-red",
													"attributeEx"	=> "type=\"button\"",
													"label"			=> "<span class=\"icon-x\"></span>"
												],
											]
										],
									],
								];
								$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead" 	=> "true"
								])->render();
								$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank = $(table);
							bank = rowColor('#bank-data-register-alimony #bodyAlimony', bank);
							bank.find('div').each(function()
							{
								$(this).find(".beneficiaryC").text(beneficiary);
								$(this).find(".iBeneficiary").val(beneficiary);
								$(this).find(".aliasC").text(alias);
								$(this).find(".iAlias").val(alias);
								$(this).find(".bankName").text(bankName);
								$(this).find(".iBank").val(bankid);
								$(this).find(".clabeC").text(clabe != "" ? clabe : "---");
								$(this).find(".iClabe").val(clabe);
								$(this).find(".accountC").text(account != "" ? account : "---");
								$(this).find(".iAccount").val(account);
								$(this).find(".cardC").text(card != "" ? card : "---");
								$(this).find(".iCard").val(card);
								$(this).find(".branchC").text(branch != "" ? branch : "---");
								$(this).find(".iBranch").val(branch);
							})
							$('#bank-data-register-alimony #bodyAlimony').append(bank);
							$('.card, .clabe, .account, .alias, .beneficiary, .branch_office').removeClass('error').removeClass('valid').val('');
							$('.bank').val(0).trigger("change"); 
							$('#bank-data-register-alimony').parent().removeClass('hidden');
							$('#not-found-accounts-alimony').addClass('hidden');
						}
					}	
				}
			})
			.on('click','.delete-bank', function()
			{
				$(this).parents('.tr').remove();
			})
			.on('change','#infonavit',function()
			{
				if($(this).is(':checked'))
				{
					$('.infonavit-container').stop(true,true).fadeIn();
				}
				else
				{
					$('.infonavit-container').stop(true,true).fadeOut();
				}
				@php
					$selects = collect(
						[
							[
								"identificator"				=> "[name=\"work_infonavit_discount_type\"]",
								"placeholder"				=> "Sleccione el tipo de descuento",
								"language"					=> "es",
								"maximumSelectionLength"	=> "1",
							]
						]
					);
				@endphp	
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('change','#alimony',function()
			{
				if($(this).is(':checked'))
				{
					$('.alimony-container').removeClass('hidden');
					@if (isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '')
						$('.content-bank-alimony').find('.disabled-alimony').prop('disabled', false);
					@endif
					@php
						$selects = collect([
							[
								"identificator"          => "[name=\"work_alimony_discount_type\"]", 
								"placeholder"            => "Seleccione el tipo de descuento",
								"maximumSelectionLength" => "1"
							],
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects]) @endcomponent
					$('#accounts-alimony').removeClass('hidden');
					generalSelect({'selector': '.bank', 'model': 28});
				}
				else
				{
					$('.alimony-container').addClass('hidden');
					$('#accounts-alimony').addClass('hidden');
				}
			})
			.on('change','#edit_data',function()
			{
				if($(this).is(':checked'))
				{
					swal({
						title		: "Habilitar edición de información laboral",
						text		: "¿Desea habilitar la edición de la información laboral?",
						icon		: "warning",
						buttons		:
						{
							cancel:
							{
								text		: "Cancelar",
								value		: null,
								visible		: true,
								closeModal	: true,
							},
							confirm:
							{
								text		: "Habilitar",
								value		: true,
								closeModal	: true,
							}
						},
						dangerMode	: true,
					})
					.then((a) => {
						if (a)
						{
							$('.laboral-data').prop('disabled',false);
							@if (isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->infonavitCredit != '')
								$('#infonavit').prop('checked', true);
							@endif
							@if (isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '')
								$('#alimony').prop('checked', true);
								$('.content-bank-alimony').find('.disabled-alimony').prop('disabled', false);
							@endif
							
						}
						else
						{
							$('#edit_data').prop('checked',false);
							$('.laboral-data').prop('disabled',true);
						}
					});
				}
				else
				{
					swal({
						title		: "Deshabilitar edición de información laboral",
						text		: "Si deshabilita la edición, las modificaciones realizadas en INFORMACIÓN LABORAL no serán guardadas",
						icon		: "warning",
						buttons		:
						{
							cancel:
							{
								text		: "Cancelar",
								value		: null,
								visible		: true,
								closeModal	: true,
							},
							confirm:
							{
								text		: "Deshabilitar",
								value		: true,
								closeModal	: true,
							}
						},
						dangerMode	: true,
					})
					.then((a) => {
						if (a)
						{
							$('.laboral-data').prop('disabled',true);
							$('#infonavit').prop('checked', false);
							$('#alimony').prop('checked', false);
						}
						else
						{
							$('#edit_data').prop('checked',true);
							$('.laboral-data').prop('disabled',false);
						}
					});
				}
			})
			.on('change','[name="work_project"]',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null)
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.select_father').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
							}
							else
							{
								$('.js-code_wbs').html('');
								$('.select_father').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs').html('');
					$('.select_father').removeClass('block').addClass('hidden');				
				}
			})
			.on('change','.pathActioner',function(e)
			{
				filename     = $(this);
				uploadedName = $(this).parent('.uploader-content').siblings('.path');
				extention    = /\.pdf/i;
				if(filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo pdf', 'warning');
					$(this).val('');
				}
				else if(this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());

					$('.disable-button').prop('disabled', true);

					$('.btn_disable').attr('disabled', true);	
					$.ajax(
					{
						type       : 'post',
						url        : '{{ route("requisition.upload") }}',
						data       : formData,
						contentType: false,
						processData: false,
						success    : function(r)
						{
							if(r.error == 'DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');
								
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
							$('.btn_disable').attr('disabled', false);
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							
						}
					})
				}
			})
			.on('click','#add_document',function()
			{
				@php
					
					$options = collect();
					$docskind = ["Aviso de retención por crédito Infonavit","Estado de cuenta","Cursos de capacitación","Carta de recomendación","Certificado médico","Identificación","Hoja de expediente"];

					foreach($docskind as $kind)
					{
						$options = $options->concat([["value" => $kind, "description" => $kind]]);
					}

					$newDoc = view('components.documents.upload-files',[
						"attributeExInput" 		=> "name=\"path\" accept=\".pdf\"",
						"classExInput" 			=> "input-text pathActioner",
						"attributeExRealPath" 	=> "name=\"path_other_document[]\"",
						"classExRealPath" 		=> "path path_other_document",
						"componentsExUp" => 
						[
							["kind" => "components.labels.label", "label" => "Tipo de documento:"],
							["kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"name_other_document[]\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "nameDocument mb-6 removeselect"],
						],
						"classExDelete"			=> "delete_other_doc",	
					])->render();
				@endphp
				newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				containerNewDoc = $(newDoc);
				$('#other_documents').append(containerNewDoc);

				@php
					$selects = collect(
						[
							[
								"identificator"				=> "[name=\"name_other_document[]\"]",
								"placeholder"				=> "Seleccione el tipo de documento",
								"maximumSelectionLength"	=> "1",
							]
						]
					);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('click','.delete_other_doc',function()
			{
				$(this).parents('.docs-p').remove();
			})
		});

		function percentAlimony()
		{
			$('[name="work_alimony_discount"]').removeClass('valid').removeClass('error');
			prueba = $('[name="work_alimony_discount_type"] option:selected').val();
			if(prueba != undefined && prueba == 2 && $('[name="work_alimony_discount"]').val() > 100)
			{
				swal('','El porcentaje no debe ser mayor a 100%','error');
				setTimeout(function(){
					$('[name="work_alimony_discount"]').parent().append('<span class="help-block form-error">El porcentaje no debe ser mayor a 100%</span>');
					$('[name="work_alimony_discount"]').removeClass('valid').addClass('error');
				}, 250);
			}
			else
			{
				$('[name="work_alimony_discount"]').parent().find('.form-error').remove();
			}
		}
	</script>
@endsection
