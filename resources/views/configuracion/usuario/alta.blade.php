@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('user.store')."\" method=\"post\" id=\"container-alta\""])
		@component("components.labels.title-divisor") Datos de usuario @endcomponent
		@component("components.labels.subtitle") Para agregar un usuario nuevo es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""]) 
			<div class="col-span-2">
				<div class="py-2">
					@component("components.labels.label",["label" => "Nombre(s):"]) @endcomponent
					@php
						isset($new_user) ? $value = $new_user->name : $value = "";
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el nombre(s)\" type=\"text\" name=\"name\" data-validation=\"required\" value=\"$value\"", "classEx" =>  "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Apellido paterno:"]) @endcomponent
					@php
						isset($new_user) ? $value=$new_user->last_name : $value="";
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el apellido paterno\" type=\"text\" name=\"last_name\" data-validation=\"required\" value=\"$value\"", "classEx" =>  "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Apellido materno (opcional):"]) @endcomponent
					@php
						isset($new_user) ? $value=$new_user->scnd_last_name : $value="";
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "placeholder=\"Ingrese el apellido materno\" type=\"text\" name=\"scnd_last_name\" value=\"$value\"", "classEx" =>  "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Seleccione una opción:"]) @endcomponent
					<div class="flex space-x-2">
						@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" checked name=\"gender\" id=\"hombre\" value=\"hombre\"", "label" => "Hombre"]) @endcomponent
						@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" name=\"gender\" id=\"mujer\" value=\"mujer\"", "label" => "Mujer"]) @endcomponent
					</div>
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Teléfono (opcional):"]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"phone\" placeholder=\"Ingrese el teléfono\" data-validation=\"phone\"", "classEx" =>  "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Extensión (opcional):"]) @endcomponent
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"extension\" placeholder=\"Ingrese la extensión\"", "classEx" =>  "input-text extension"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Correo electrónico:"]) @endcomponent
					@php
						isset($new_user) ? $value=$new_user->email : $value="";
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" name=\"email\" placeholder=\"Ingrese el corre electrónico\" data-validation=\"required email server\" data-validation-url=\"".url('configuration/user/validate')."\" value=\"$value\"", "classEx" =>  "input-text"]) @endcomponent
				</div>
			</div>
			<div class="col-span-2">
				<div class="py-2">
					@component("components.labels.label",["label" => "Agregue la empresa:"]) @endcomponent
					@php
						$options = collect();
						foreach($enterprises as $enterprise)
						{
							if(isset($new_user) && $new_user->cash == $enterprise->id)
							{
								$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"enterprises[]\" multiple=\"multiple\" id=\"multiple-enterprises\" data-validation=\"required\"", "classEx" =>  "js-enterprises removeselect"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Agregue una dirección:"]) @endcomponent
					@php
						$options = collect();
						foreach($areas as $area)
						{
							if(isset($new_user) && $new_user->area_id == $area->id)
							{
								$options = $options->concat([["value" => $area->id, "description" => $area->name, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $area->id, "description" => $area->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"area_id\" multiple=\"multiple\" id=\"multiple-areas\" data-validation=\"required\"", "classEx" =>  "js-areas removeselect"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Agregue un departamento (opcional):"]) @endcomponent
					@php
						$options = collect();
						foreach($departments as $department)
						{
							if(isset($new_user) && $new_user->departament_id == $department->id)
							{
								$options = $options->concat([["value" => $department->id, "description" => $department->name, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
							}
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"department_id\" multiple=\"multiple\" id=\"multiple-departments\"", "classEx" =>  "js-departments removeselect"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Puesto:"]) @endcomponent
					@php
						isset($new_user) ? $value = $new_user->position : $value = "";
					@endphp
					@component("components.inputs.input-text",["attributeEx" => "type=\"text\" placeholder=\"Ingrese el puesto\" name=\"position\" data-validation=\"required\" data-validation-depends-on=\"usertype\" value=\"$value\"", "classEx" =>  "input-text position"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Sección de ticket que puede revisar (opcional):"]) @endcomponent
					@php
						$options = collect();
						foreach($sections as $section)
						{
							$options = $options->concat([["value" => $section->idsectionTickets, "description" => $section->section]]);
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"section_id[]\" multiple=\"multiple\" id=\"multiple-section\"", "classEx" =>  "js-sections removeselect"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label",["label" => "Empleado relacionado al usuario:"]) @endcomponent
					@php
						$options = collect();
						if(isset($new_user))
						{
							$options = $options->concat(
							[
								[
									"value"			=> $new_user->real_employee_id, 
									"description"	=> $new_user->fullName(), 
									"selected"		=> "selected"
								]
							]);
						}
					@endphp
					@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"real_employee_id\" multiple=\"multiple\" data-validation=\"required\"", "classEx" =>  "js-real_employee"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.inputs.switch", ["attributeEx" => "name=\"cash\" type=\"checkbox\" value=\"1\" id=\"cash\""]) Caja chica @endcomponent
				</div>
				<div class="py-2 hidden">
					@component("components.labels.label", ["label" => "Cantidad:"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"cash_amount\" placeholder=\"Ingrese la cantidad\" data-validation=\"required\" data-validation-depends-on=\"cash\"", "classEx" => "input-text cash_amount"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.inputs.switch", ["attributeEx" => "name=\"adglobal\" type=\"checkbox\" value=\"1\" id=\"adglobal\""]) Personal ADGlobal @endcomponent
				</div>
			</div>
		@endcomponent

		@component('components.labels.title-divisor')    CUENTAS BANCARIAS @endcomponent
		@component("components.containers.container-form", ["attributeEx" => "id=\"banks\""])
			<div class="col-span-2">
				@component("components.labels.label") Banco: @endcomponent
				@component("components.inputs.select", ["options" => [], "classEx" => "bank"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese el alias\"", "classEx" => "alias"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * Número de tarjeta: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese el número de tarjeta\" data-validation=\"tarjeta\"", "classEx" => "card"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * CLABE interbancaria: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese la CLABE\" data-validation=\"clabe\"", "classEx" => "clabe"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * Número de cuenta @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese la cuenta bancaria\" data-validation=\"cuenta\"", "classEx" => "account"]) @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component("components.labels.label") *Para agregar una cuenta nueva es necesario colocar al menos uno de los campos. @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"add\" id=\"add\"", "classEx" => "add2", "label" => "<span class=\"icon-plus\"></span> Agregar"]) @endcomponent
			</div>
		@endcomponent
		@php
			$modelHead = [ "Banco", "Alias", "Número de tarjeta", "CLABE interbancaria", "Número de cuenta", "Acciones"];
			$modelBody = [];
		@endphp
		<div class="hidden bank_accounts">
			@AlwaysVisibleTable(["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeExBody" => "id=\"banks-body\""]) @endAlwaysVisibleTable
		</div>
	
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-10 mb-6">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "CREAR USUARIO", "classEx" => "btn-red"]) @endcomponent
			@component("components.buttons.button", ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\"", "label" => "Borrar campos", "classEx" => "btn-delete-form"]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
		$.validate(
		{
			form		: '#container-alta',
			modules		: 'security, logic',
			onSuccess	: function($form)
			{
				gender = $('input[name="gender"]').is(':checked');
				if(gender == false)
				{
					swal('', 'Debe seleccionar el género (Hombre/Mujer)', 'error');
					return false;
				}
				else
				{
					swal("Cargando, espere a ser redireccionado",{
						icon: '{{ url('images/loading.svg') }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
			
			},
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			}
		});
		$('.phone,.extension').numeric(false);
		$('input[name="phone"]').numeric(false);
		$('.cash_amount').numeric({negative: false, altDecimal: ".", decimalPlaces: 2 });
		$('.card,.clabe,.account').numeric(false);
		@ScriptSelect(
		[
			"selects" =>
			[
				[
					"identificator"          => ".js-banks", 
					"placeholder"            => "Seleccione el banco", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],	
				[
					"identificator"          => ".js-kindbank", 
					"placeholder"            => "Seleccione el banco", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],	
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa", 
					"language"				 => "es",
				],	
				[
					"identificator"          => ".js-areas", 
					"placeholder"            => "Seleccione la dirección", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-departments", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-departmentsRA", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
				],
				[
					"identificator"          => ".js-sections", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
				]
			]
		]) @endScriptSelect
		generalSelect({'selector': '.js-real_employee', 'model': 20});
		generalSelect({'selector': '.bank', 'model': 27});
		@if(Auth::user()->id == 43)
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator"          => "[name=\"separator\"]", 
						"placeholder"            => "Seleccione el separador", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			]) @endScriptSelect
		@endif
		$(document).on('click','.delete-item', function()
		{
			$(this).parents('tr').remove();
		})
		.on('change','input[type="checkbox"]',function()
		{
			if(this.checked)
			{
				$(this).parents('li').children('input[type="checkbox"]').prop('checked',true);
			}
			var checked = $(this).prop("checked"),
				father = $(this).parent();
			father.find('input[type="checkbox"]').prop({
				checked: checked
			});
			function checkSiblings(check)
			{
				var parent = check.parent().parent(),
					all = true;
				check.siblings().each(function() 
				{
					return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
				});
				if (all && checked) 
				{
					$(this).parents('li').children('input[type="checkbox"]').prop('checked',true);
					parent.children('input[type="checkbox"]').prop({
						checked: checked
					});
					checkSiblings(parent);
				}
				else if(all && !checked)
				{
					parent.children('input[type="checkbox"]').prop("checked",checked);
					parent.children('input[type="checkbox"]').prop((parent.find('input[type="checkbox"]').length < 0));
					checkSiblings(parent);
				}
				else
				{
					check.parent("li").children('input[type="checkbox"]').prop('checked',false);
				}
			} 
			checkSiblings(father);
		})
		.on('change','#cash',function()
		{
			if($(this).is(':checked'))
			{
				$('.cash_amount').parent('div').stop(true,true).slideDown();
			}
			else
			{
				$('.cash_amount').parent('div').stop(true,true).slideUp();
			}
		})
		.on('change','input[type="checkbox"].newmodules',function()
		{
			if ($(this).val() != 127 && $(this).val() != 101) 
			{
				if ($(this).is(':checked')) 
				{
					$('#idmodule').val($(this).val());
					$('#myModal').show();
					$('.addpermission,.exitAddPermission').show();
					$('.exitUpdatePermission,.updatepermission').hide();
					@ScriptSelect(
					[
						"selects" =>
						[
							[
								"identificator"          => ".js-enterprises-permission", 
								"placeholder"            => "Seleccione una o varias empresas", 
								"language"				 => "es",
							],
							[
								"identificator"          => ".js-departments-permission", 
								"placeholder"            => "Seleccione uno o varios departamentos", 
								"language"				 => "es",
							],
						]
					]) @endScriptSelect
				}
				else
				{
					idmodule = 'module_'+$(this).val();
					$('#body-admin tr').find('.'+idmodule).empty();
					$('.addpermission,.exitAddPermission').show();
					$('.exitUpdatePermission,.updatepermission').hide();
				}
			}
			
		})
		.on('click','.exitAddPermission',function()
		{
			$('#myModal').hide();
			idmodule = 'module_'+$('#idmodule').val();
			$('#body-admin tr').find('#'+idmodule).prop('checked',false);	
			$('.js-enterprises-permission,.js-departments-permission').val(null).trigger('change');
		})
		.on('click','.exitUpdatePermission',function()
		{
			$('.js-enterprises-permission,.js-departments-permission').val(null).trigger('change');
			$('#myModal').hide();
		})
		.on('click','.addpermission',function()
		{
			if ($('.js-enterprises-permission option:selected').length>0 && $('.js-departments-permission option:selected').length>0) 
			{
				idmodule = 'module_'+$('#idmodule').val();
				btn 	= $('<button class="follow-btn editModule" type="button"><span class="icon-pencil"></span></button>');
				enterprises = $('<span></span>');
				$('.js-enterprises-permission option:selected').each(function()
				{
					enterprises.append($('<input type="hidden" class="enterprises" name="enterprises_'+idmodule+'[]" value="'+$(this).val()+'">'));
				});
				departments= $('<span></span>');
				$('.js-departments-permission option:selected').each(function()
				{
					departments.append($('<input type="hidden" class="departments" name="departments_'+idmodule+'[]" value="'+$(this).val()+'">'));
				});
				$('#body-admin tr').find('.'+idmodule).append(btn);
				$('#body-admin tr').find('.'+idmodule).append(enterprises);
				$('#body-admin tr').find('.'+idmodule).append(departments);
				$('.js-enterprises-permission,.js-departments-permission').val(null).trigger('change');
				$('#myModal').hide();
			}
			else
			{
				swal('', 'Debe seleccionar al menos una empresa y un departamento.', 'error');
			}
		})
		.on('click','.updatepermission',function()
		{
			if ($('.js-enterprises-permission option:selected').length>0 && $('.js-departments-permission option:selected').length>0) 
			{
				idmodule = 'module_'+$('#idmodule').val();
				$('#body-admin tr').find('.'+idmodule).empty();

				btn 	= $('<button class="follow-btn editModule" type="button"><span class="icon-pencil"></span></button>');

				enterprises = $('<span></span>');
				$('.js-enterprises-permission option:selected').each(function()
				{
					enterprises.append($('<input type="hidden" class="enterprises" name="enterprises_'+idmodule+'[]" value="'+$(this).val()+'">'));
				});

				departments= $('<span></span>');
				$('.js-departments-permission option:selected').each(function()
				{
					departments.append($('<input type="hidden" class="departments" name="departments_'+idmodule+'[]" value="'+$(this).val()+'">'));
				});

				$('#body-admin tr').find('.'+idmodule).append(btn);
				$('#body-admin tr').find('.'+idmodule).append(enterprises);
				$('#body-admin tr').find('.'+idmodule).append(departments);
				$('.js-enterprises-permission,.js-departments-permission').val(null).trigger('change');
				$('#myModal').hide();
			}
			else
			{
				swal('', 'Debe seleccionar al menos una empresa y un departamento.', 'error');
			}
		})
		.on('click','.editModule',function()
		{
			$('.js-enterprises-permission,.js-departments-permission').val(null).trigger('change');
			$('.addpermission,.exitAddPermission').hide();
			$('.exitUpdatePermission,.updatepermission').show();
			arrayEnterprises = [];
			arrayDepartments = [];
			$('#idmodule').val($(this).parents('td').find('.newmodules').val());
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator"          => ".js-enterprises-permission", 
						"placeholder"            => "Seleccione una o varias empresas", 
						"language"				 => "es",
					],
					[
						"identificator"          => ".js-departments-permission", 
						"placeholder"            => "Seleccione uno o varios departamentos", 
						"language"				 => "es",
					],
				]
			]) @endScriptSelect
			$(this).parents('td').find('.enterprises').each(function()
			{
				arrayEnterprises.push($(this).val());
			});
			$(this).parents('td').find('.departments').each(function()
			{
				arrayDepartments.push($(this).val());
			});
			$('.js-enterprises-permission').val(arrayEnterprises).trigger('change');
			$('.js-departments-permission').val(arrayDepartments).trigger('change');
			$('#myModal').show();
		})
		.on('click','.btn-delete-form',function(e)
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
					$('#body').html('');
					$('.removeselect').val(null).trigger('change');
					$('#banks-body').empty();
					$('.cash_amount').stop(true,true).slideUp();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','#add',function()
		{
			alias		= $('.alias').val();
			card		= $('.card').val();
			clabe		= $('.clabe').val();
			account		= $('.account').val();
			bankid		= $('.bank').val();
			bankName	= $('.bank :selected').text();

			$.ajax(
			{
				type	: "post",
				url 	: "{{url('profile/validate')}}",
				data	: 
				{
					'account_number'  : account,
					'bank_description': bankid,
					'clabe_interbanck': clabe
				},
				success : function (data)
				{
					if (data === '1')
					{
						$('.account').addClass('error');
						swal("", "La cuenta: "+account+" asociada a: "+bankName+" ya está registrada en el sistema, favor de ingresar una diferente","error");
						return false;
					}
					else if(data === '2')
					{
						$('.clabe').addClass('error');
						swal("", "La clabe interbancaria: "+clabe+" ya está registrada en el sistema, favor de ingresar una diferente","error");
						return false;
					}
					else if(data === '3')
					{
						$('.clabe, .account').addClass('error');
						swal("", "La clabe interbancaria: "+clabe+" y la cuenta: "+account+" asociada a: "+bankName+" ya están registradas en el sistema, favor de ingresar Cuenta y Clabe Interbancaria diferentes","error");
						return false;
					}
					else if (data === '4')
					{
						$(this).parents('#banks').find('.card-number, .clabe, .account').removeClass("error valid");
						clabe_tr  = bankAccount_tr = card_tr = true;
						$("#banks-body .tr").each(function(i,v)
						{
							var currentRow	=	$(this).closest(".tr");
							bank_tr		=	currentRow.find(".bankname_class").text().trim(); // bank
							account_tr 	= $(this).find("[name='account[]']").val();
							if((clabe == $(this).find("[name='clabe[]']").val()) && (clabe != ""))
							{
								clabe_tr = false;
							}
							else if((bankName+" "+account) == (bank_tr+" "+account_tr) && (account != ""))
							{
								bankAccount_tr = false;
							}
							else if((card == $(this).find("[name='card[]']").val()) && (card != ""))
							{
								card_tr = false;
							}
						});
						if(clabe_tr == false)
						{
							swal("", "Esta clabe ya ha sido registrada anteriormente", "error");
							return false;
						}
						else if(bankAccount_tr == false)
						{
							swal("", "Esta cuenta bancaria y banco ya han sido registrados anteriormente", "error");
							return false;
						}
						else if(card_tr == false)
						{
							swal("", "Esta tarjeta ya ha sido registrada anteriormente", "error");
							return false;
						}

						if(bankid.length>0)
						{
							if (card == "" && clabe == "" && account == "")
							{
								$('.card, .clabe, .account').addClass('error');
								swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
							}
							else if (alias == "")
							{
								$(".alias").addClass("error");
								swal("", "Debe ingresar todos los campos requeridos", "error");
							}
							else if(clabe != "" && ($(".clabe").hasClass("error") || clabe.length!=18))
							{
								swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
								$(".clabe").addClass("error");
							}
							else if(card != "" && ($(".card-number").hasClass("error") || card.length!=16))
							{
								swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
								$(".card-number").addClass("error");
							}
							else if(account != "" && ($(".account").hasClass("error") || (account.length>15 || account.length<5)))
							{
								swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
								$(".account").addClass("error");
							}
							else
							{
								@php
									$table = view("components.tables.alwaysVisibleTable",[
										"modelHead" => [ "Banco", "Alias", "Número de tarjeta", "CLABE interbancaria", "Número de cuenta", "Acciones"],
										"modelBody" => 
										[
											[
												"classEx" => "tr",
												[
													"content" =>
													[
														["label" => "<label class=\"bankname_class\"></label>"],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" class=\"idEmployee\" name=\"idEmployee[]\" value=\"x\""],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"bank[]\""]
													]
												],
												[
													"content" =>
													[
														["label" => "<label class=\"alias_class\"></label>"],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"alias[]\""]
													]
												],
												[
													"content" =>
													[
														["label" => "<label class=\"card_class\"></label>"],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"card[]\""]
													]
												],
												[
													"content" =>
													[
														["label" => "<label class=\"clabe_class\"></label>"],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"clabe[]\""]
													]
												],
												[
													"content" =>
													[
														["label" => "<label class=\"account_class\"></label>"],
														["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"account[]\""]
													]
												],
												[
													"content" =>
													[
														["kind" => "components.buttons.button", "variant" => "red", "label" => "<span class=\"icon-x delete-span\"></span>", "attributeEx" => "type=\"button\"", "classEx" => "delete-item"]
													]
												]
											]
										],
										"themeBody" => "striped",
										"noHead"	=> true,
										"attributeExBody" => "id=\"body\"",
									])->render();
									$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
								@endphp
								table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
								bank = $(table);
								bank = rowColor('#banks-body', bank);
								bank.find('.bankname_class').text(bankName);
								bank.find('[name="bank[]"]').val(bankid);
								bank.find('.alias_class').text(alias =='' ? '---' : alias);
								bank.find('[name="alias[]"]').val(alias);
								bank.find('.card_class').text(card =='' ? '---' : card);
								bank.find('[name="card[]"]').val(card);
								bank.find('.clabe_class').text(clabe =='' ? '---' : clabe);
								bank.find('[name="clabe[]"]').val(clabe);
								bank.find('.account_class').text(account =='' ? '---' : account);
								bank.find('[name="account[]"]').val(account);
								$('#banks-body').append(bank);
								$('.bank_accounts').show();
								$('.card, .clabe, .account, .alias').removeClass('valid error').val('');
								$('.bank').val(0).trigger("change");
							}
						}
						else
						{
							swal('', 'Seleccione un banco, por favor', 'error');
							$('.bank').addClass('error');
						}
					}
				}
			});
		})
		@if(Auth::user()->id == 43)
			.on('change','#csv',function(e)
			{
				label		= $(this).next('label');
				fileName	= e.target.value.split( '\\' ).pop();
				if(fileName)
				{
					label.find('span').html(fileName);
				}
				else
				{
					label.html(labelVal);
				}
			});
		@endif
		function getEntDep($value)
		{	
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("configuration/user/getentdep") }}',
				data : {'module_id':$value},
				success:function(data)
				{
					$('.module_'+$value).append(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
		}
	});
</script>
@endsection
