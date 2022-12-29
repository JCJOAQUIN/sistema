@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('provider.store')."\" method=\"POST\" id=\"container-alta\""])
		@component('components.labels.title-divisor') DATOS DEL PROVEEDOR @endcomponent
		@component("components.labels.subtitle") Para agregar un proveedor nuevo es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Razón Social: @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "reason"
						placeholder = "Ingrese la razón social" 
						data-validation = "length server" 
						data-validation-length = "max150"
						data-validation-url = "{{ route('provider.validation') }}"
					@endslot
				@endcomponent

				@slot("classEx")
					remove
				@endslot
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Calle: @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "address"
						placeholder = "Ingrese la calle" 
						data-validation = "required length" 
						data-validation-length = "max100"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Número: @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "number"
						placeholder = "Ingrese el número" 
						data-validation = "required length" 
						data-validation-length = "max45"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Colonia: @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "colony" 
						placeholder = "Ingrese la colonia" 
						data-validation = "required length" 
						data-validation-length = "max70"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Código Postal: @endcomponent

				@component("components.inputs.select")
					@slot("attributeEx")
						name = "cp" 
						id = "cp"
						data-validation = "required" 
						multiple = "multiple"
						placeholder = "Ingrese el código postal"
					@endslot

					@slot("classEx")
						removeselect
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Ciudad: @endcomponent

				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text" 
						name = "city"
						placeholder = "Ingrese la ciudad" 
						data-validation = "required length" 
						data-validation-length = "max70"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent

				@php
					$options =  collect();
					foreach(App\State::orderName()->get() as $state)
					{
						$options = $options->concat([["value" => $state->idstate, "description" => $state->description]]);
					}
				@endphp
				
				@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"state\" multiple=\"multiple\" data-validation=\"required\"", "classEx" =>  "js-state removeselect"]) @endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") RFC: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text" 
						name = "rfc"
						placeholder = "Ingrese el RFC"  
						data-validation = "rfc server"
						data-validation-url = "{{ route('provider.validation') }}"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Teléfono: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text" 
						name = "phone" 
						placeholder = "Ingrese el teléfono"
						data-validation = "number"
					@endslot

					@slot("classEx")
						phone
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Contacto: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text" 
						name = "contact" 
						placeholder = "Ingrese el contacto"
						data-validation = "required"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Beneficiario: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text" 
						name = "beneficiary" 
						placeholder = "Ingrese el beneficiario"
						data-validation = "required"
					@endslot

					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Otro (opcional): @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text" 
						name = "other" 
						placeholder = "Ingrese otro dato"
					@endslot
				@endcomponent
			</div>
		@endcomponent

		@component('components.labels.title-divisor') CLASIFICACIÓN @endcomponent

		@component("components.containers.container-form", ["classEx" => "formClasification"])
			<div class="col-span-2 md:col-span-4">
				@component("components.labels.label") Estatus: @endcomponent

				@php
					$options = collect();

					$options = $options->concat([["description" => "Sin validar", "selected" => "selected", "value" => 2],["value" => 1, "description" => "Validado"],["value" => 0, "description" => "Lista negra"]]);
				@endphp

				@component("components.inputs.select",["options" => $options, "attributeEx" => "id=\"clasification\" name=\"status\"", "classEx" =>  "custom-select"]) @endcomponent
			</div>

			<div class="col-span-2 md:col-span-4">
				@component("components.labels.label") Comentario: @endcomponent

				@component("components.inputs.text-area")
					@slot("attributeEx")
						id = "clasification_comment"
						readonly
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Comprobantes: @endcomponent
			</div>
			
			<div class="hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2 documents_clasification"></div>

			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						id   = "add_documents_clasification" 
						type = "button"
						disabled
					@endslot
					@slot('classEx')
						mt-4
					@endslot
					<span class="icon-plus"></span> 
					<span>Agregar Documento</span>
				@endcomponent
			</div>
		@endcomponent
		
		@component('components.labels.title-divisor') CUENTAS BANCARIAS @endcomponent

		@component('components.labels.label') 
			@slot("classEx")
				text-center
			@endslot
			Para agregar una cuenta nueva es necesario colocar los siguientes campos 
		@endcomponent

		@component("components.containers.container-form")

			<div class="col-span-2">
				@component("components.labels.label") Banco: @endcomponent

				@php
					$options =  collect();
				@endphp
				
				@component("components.inputs.select",["options" => $options, "attributeEx" => "name=\"bank\" multiple=\"multiple\"", "classEx" =>  "js-bank removeselect"]) @endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese el alias" 
					@endslot

					@slot("classEx")
						alias
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Cuenta bancaria: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese la cuenta bancaria" 
						data-validation = "cuenta"
					@endslot

					@slot("classEx")
						account
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label") Sucursal: @endcomponent

				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese la sucursal"
					@endslot

					@slot("classEx")
						branch_office
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Referencia: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese la referencia"
					@endslot

					@slot("classEx")
						reference
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") CLABE: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese la CLABE"
					@endslot
					@slot("classEx")
						clabe
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Moneda: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([
						["value" => "MXN", "description"  => "MXN"],
						["value" => "USD", "description"  => "USD"],
						["value" => "EUR", "description"  => "EUR"],
						["value" => "Otro", "description" => "Otro"]]);
				@endphp
				@component("components.inputs.select",["options" => $options, "classEx" =>  "custom-select currency removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") IBAN: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese el IBAN" 
						data-validation = "iban"
					@endslot
					@slot("classEx")
						iban
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") BIC/SWIFT: @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese el BIC/SWIFT" 
						data-validation = "bic_swift"
					@endslot
					@slot("classEx")
						bic_swift
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Convenio (opcional): @endcomponent
				@component("components.inputs.input-text") 
					@slot("attributeEx")
						type = "text"
						placeholder = "Ingrese el convenio"
					@endslot

					@slot("classEx")
						agreement
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						id	 = "add"
						name = "add"
						type = "button"
					@endslot
					@slot('classEx')
						mt-4 add2
					@endslot
					<span class="icon-plus"></span> 
					<span>Agregar cuenta bancaria</span>
				@endcomponent
			</div>
		@endcomponent
		@AlwaysVisibleTable([
			"modelHead" 		=> ["Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acción"],
			"modelBody" 		=> [],
			"attributeExBody" 	=> "id=\"body\"",
			"attributeEx"		=> "id=\"table-show\" style=\"display:none !important;\"",
		])@endAlwaysVisibleTable
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary"])
				@slot('attributeEx')
					type = "submit" 
					name = "enviar"
				@endslot
				@slot('classEx')
					enviar
				@endslot
					Registrar
			@endcomponent
			
			@component('components.buttons.button',["variant" => "reset"])
				@slot('attributeEx')
					type = "reset"
					name = "borra"
				@endslot
				@slot('classEx')
					borrar
				@endslot
					BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		function validate()
		{
			$.validate(
			{
				form		: '#container-alta',
				modules		: 'security',
				onError 	: function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}','error');
				},
				onSuccess	: function($form)
				{
					ClassData = $('[name="clasificationData"]').val();
					comment   = $('#clasification_comment').val();
					status 	  = $('#clasification option:selected').val();

					if(comment == '' && status != 2)
					{
						swal('','Se requiere un comentario en la sección de clasificación.','error');
						return false;
					}
					else if(status != 2 && comment != '')
					{
						if(ClassData == undefined)
						{
							swal('','Es requerido al menos un documento en la sección de Clasificación.','error');
							return false;

						}
					}
				}
			});
		}

		function myCallback()
		{
			if(flag)
			{
				@php
					$modelHead 	= ["Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acción"];
					$modelBody =
					[
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_bank"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"providerBank[]\"",
										"classEx"		=> "providerBank"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"bank[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_alias"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"alias[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_account"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"account[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_branch_office"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"branch_office[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_reference"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"reference[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_clabe"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"clabe[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_currency"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"currency[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_iban"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"iban[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_bic_swift"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"bic_swift[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx" 		=> "class_agreement"	
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"agreement[]\""
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
						"noHead"	=> true
					])->render();
				@endphp

				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';

				prov = $(table);

				prov.find('.class_bank').text(bankName);
				prov.find('[name="providerBank[]"]').val("x");
				prov.find('[name="bank[]"]').val(bank);	

				prov.find('.class_alias').text(alias);
				prov.find('[name="alias[]"]').val(alias);
				
				prov.find('.class_account').text(account != '' ? account : '---');
				prov.find('[name="account[]"]').val(account);

				prov.find('.class_branch_office').text(branch_office != '' ? branch_office : '---');
				prov.find('[name="branch_office[]"]').val(branch_office);

				prov.find('.class_reference').text(reference != '' ? reference : '---');
				prov.find('[name="reference[]"]').val(reference);

				prov.find('.class_clabe').text(clabe != '' ? clabe : '---');
				prov.find('[name="clabe[]"]').val(clabe);

				prov.find('.class_currency').text(currency);
				prov.find('[name="currency[]"]').val(currency);

				prov.find('.class_iban').text(iban != '' ? iban : '---');
				prov.find('[name="iban[]"]').val(iban);

				prov.find('.class_bic_swift').text(bic_swift != '' ? bic_swift : '---');
				prov.find('[name="bic_swift[]"]').val(bic_swift);

				prov.find('.class_agreement').text(agreement !='' ? agreement : '---');
				prov.find('[name="agreement[]"]').val(agreement);
				
				$("#table-show").removeAttr('style');

				$('#body').append(prov);

				$('.clabe, .account,.iban,.bic_swift').removeClass('valid').val('');
				$('.branch_office,.reference,.agreement,.alias,.iban,.bic_swift').val('');
				$(this).parents('tbody').find('.error').removeClass('error');
				$('.js-bank,.currency').val(0).trigger("change");
				$("#add").prop('disabled',false);
			}
		}
		
		$(document).ready(function()
		{
			validate();

			$('.account').numeric(false);
			$('.clabe').numeric(false);
			$('input[name="phone"]').numeric(false);
			generalSelect({'selector': '.js-bank', 'model': 28});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-state", 
						"placeholder"            => "Seleccione el estado", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "#clasification", 
						"placeholder"            => "Seleccione el estatus", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".currency", 
						"placeholder"            => "Seleccione el tipo de moneda", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			generalSelect({'selector':'#cp', 'model':2});

			$(document).on('click','#add',function()
			{
				$(this).prop('disabled',true);
				bankName		= $('.js-bank :selected').text();
				bank			= $('.js-bank').val();
				account			= $('.account').val();
				branch_office	= $('.branch_office').val();
				reference		= $('.reference').val().trim();
				clabe			= $('.clabe').val();
				currency		= $('.currency').val();
				agreement		= $('.agreement').val();
				alias 			= $('.alias').val();
				iban			= $('.iban').val();
				bic_swift 		= $('.bic_swift').val();
				minLength 		= 5;
				maxLength 		= 15;

				$('.alias,.account,.clabe,.currency').removeClass('error');
				$('.js-bank').removeClass('error');

				if(bank.length>0)
				{
					if(alias == "" && currency == "" && account == "" && clabe == "")
					{
						$('.alias,.account,.clabe,.currency').addClass('error');
						swal('', 'Debe ingresar todos los campos requeridos', 'error');
						$("#add").prop('disabled',false);
					}
					else
					{
						if (alias == "")
						{
							$('.alias').addClass('error');
							swal('', 'Debe ingresar todos los campos requeridos', 'error');
							$("#add").prop('disabled',false);
						}
						else if (account == "" && clabe == "")
						{
							$('.account,.clabe').removeClass('valid').addClass('error');
							swal('', 'Debe ingresar al menos cuenta Bancaria o CLABE', 'error');
							$("#add").prop('disabled',false);
						}
						else if(clabe != "" && ($('.clabe').hasClass('error') || clabe.length!=18))
						{
							swal('', 'Por favor, debe ingresar 18 dígitos de la CLABE.', 'error');
							$('.clabe').addClass('error');
							$("#add").prop('disabled',false);
						}
						else if(account != "" && ($('.account').hasClass('error') || (account.length<minLength || account.length>maxLength)))
						{
							swal('', 'Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.', 'error');
							$('.account').addClass('error');
							$("#add").prop('disabled',false);
						}
						else if(currency == "")
						{
							$('.currency').addClass('error');
							swal('', 'Debe ingresar todos los campos requeridos', 'error');
							$("#add").prop('disabled',false);
						}
						else if($(this).parents('div').find('.account').hasClass('error') || $(this).parents('div').find('.clabe').hasClass('error'))
						{
							swal('', 'Por favor ingrese datos correctos', 'error');
							$("#add").prop('disabled',false);
						}
						else
						{
							flag = true;
							$('#body .tr').each(function(i)
							{
								tbank    = $(this).find('input[name="bank[]"]').val();
								taccount = $(this).find('input[name="account[]"]').val();
								tclabe   = $(this).find('input[name="clabe[]"]').val();

								if(account!= '' || clabe=='')
								{
									if(bank == tbank && taccount == account)
									{
										swal('','El número de cuenta ya se encuentra registrado.','error');
										$('.account').removeClass('valid').addClass('error');
										flag = false;
										$("#add").prop('disabled',false);
									}	
								}
								else if(clabe != '' || account=='')
								{
									if(tclabe == clabe)
									{
										swal('','La CLABE ya se encuentra registrada.','error');
										$('.clabe').removeClass('valid').addClass('error');
										flag = false;
										$("#add").prop('disabled',false);
									}	
								}
								else if(account!= '' && clabe!='')
								{
									if(bank == tbank && taccount == account)
									{
										swal('','El número de cuenta ya se encuentra registrado.','error');
										$('.account').removeClass('valid').addClass('error');
										flag = false;
										$("#add").prop('disabled',false);
									}
									if(tclabe == clabe)
									{
										swal('','La CLABE ya se encuentra registrada.','error');
										$('.clabe').removeClass('valid').addClass('error');
										flag = false;
										$("#add").prop('disabled',false);
									}
								}
							});
							$.ajax(
							{
								type : 'post',
								url : '{{route('provider.validateAccount')}}',
								data : {'bank': bank, 'account': account,'clabe':clabe},
								success : function(data)
								{
									if(data['exists'] == "true")
									{
										swal('',data['message'],'error');
										flag = false;
										$("#add").prop('disabled',false);
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
								}
							}).done(function(data)
							{
								if(flag)
								{
									myCallback();
								}
							});
						}
						
					}
				}
				else
				{
					swal('', 'Seleccione un banco, por favor', 'error');
					$('.js-bank').addClass('error');
					$("#add").prop('disabled',false);
				}
			})
			.on('change', '#clasification', function()
			{
				if($('option:selected',this).val() != "" && $('option:selected',this).val() != undefined)
				{
					val = $('option:selected',this).val();
					if(val != '2')
					{
						$('#clasification_comment').removeAttr('readonly').attr('placeholder', 'Ingrese el comentario');
						$('#add_documents_clasification').removeAttr('disabled');
					}
					if(val == '2')
					{
						$('#clasification_comment').attr('readonly','readonly').removeAttr('placeholder');
						$('#add_documents_clasification').attr('disabled','disabled');
						$('#clasification_comment').val('');
						$('.formClasification').children('.documents_clasification').children('.docs-p').remove();
					}		
				}
			})
			.on('click','#add_documents_clasification',function()
			{
				comm	= $('#clasification_comment').val();
				clasif	= $('#clasification option:selected').val();

				if(comm == '' || clasif == '')
				{
					return swal('', 'El comentario y el estatus no pueden ir vacíos.', 'warning');
				}
				else
				{
					@php
					$docs_provider = view('components.documents.upload-files',[
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExDelete"			=> "delete-doc",
						"attributeExDelete"		=> "type=\"button\"",
						"classExRealPath"		=> "path"
					])->render();
					$provider = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $docs_provider));
					@endphp
					newDocProvider = '{!!preg_replace("/(\r)*(\n)*/", "", $provider)!!}';
					$('.documents_clasification').removeClass('hidden').append(newDocProvider);

				}
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('.path');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData	= new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route('provider.upload')}} ',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');

								doc			= 'no';
								comm		= $('#clasification_comment').val();
								clasif		= $('#clasification option:selected').val();

								
								$('.path').each(function(i,v)
								{	
									if($(this).val() == '')
									{
										doc = '';
									}
								});
								if(doc != '' && comm != '' && clasif != '')
								{
									obj				= new Object;
									obj.doc			= new Object;
									obj.comm		= comm;
									obj.clasif		= clasif;
									$('.path').each(function(i,v)
									{
										obj.doc[i] = $(this).val();
									});
									clasification	= JSON.stringify(obj);

									input = ($('<input type="hidden" name="clasificationData">').val(clasification));
									$('.documents_clasification').append(input);
								}
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
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
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				actioner		= $(this);
				uploadedName	= $(this).parents('.docs-p').find('.path');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route('provider.upload')}}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
			})
			.on('click','.delete-item', function()
			{
				$(this).parents('.tr').remove();

				rows	= $('#body .tr').length;
				if (rows <= 0) 
				{
					$('#table-show').hide();
				}
			})
			.on('click','.borrar',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: true,
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						$('.formClasification').children('.documents_clasification').children('.docs-p').remove();
						$("#clasification").val(2).change();
						$('#clasification_comment').attr('readonly','readonly');
						$('#add_documents_clasification').attr('disabled','disabled');
												
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
		});
	</script>
@endsection
