@extends('layouts.child_module')

@section('data')
	@php
		$check_employee = App\RealEmployee::where('curp',$employee_edit->curp)->get();
	@endphp
	@if (count($check_employee) > 0)
		@component('components.forms.form',
		[
			"attributeEx"	=> "id=\"employee_form\" method=\"POST\" action=\"".route('administration.employee.update-employee',['employee'=>$check_employee->first()->id])."\"",
			"methodEx"		=> "PUT"
		])
	@else
		@component('components.forms.form',
		[
			"attributeEx"	=> "id=\"employee_form\" method=\"POST\" action=\"".route('administration.employee.approved-employee')."\"",
			"methodEx"		=> "PUT"
		])
	@endif
			@include('configuracion.empleado.parcial')
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="folio" value="{{ $request_model->folio }}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="employee" value="{{ $employee_edit->id }}"
				@endslot
			@endcomponent
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@if (count($check_employee) > 0)
					@component('components.buttons.button',["variant" => "primary"])
						@slot('attributeEx')
							type="submit" name="send"
						@endslot
						@slot('classEx')
							btn_disable
						@endslot
						ACTUALIZAR EMPLEADO
					@endcomponent
				@else
					@component('components.buttons.button',["variant" => "secondary"])
						@slot('attributeEx')
							type="submit" name="send"
						@endslot
						@slot('classEx')
							btn_disable
						@endslot
						CREAR NUEVO EMPLEADO
					@endcomponent
				@endif
				@component('components.buttons.button',["variant" => "reset", "buttonElement" => "a"])
					@slot('attributeEx')
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form	: '#employee_form',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					flagEmployee = true;
					if($('.form_other_doc').length > 0)
					{
						$('.form_other_doc').each(function(i,v)
						{
							name_other_document = $(this).find('[name="name_other_document[]"] option:selected').val();
							path_other_document = $(this).find('[name="path_other_document[]"]').val();
	
							if (name_other_document == "" || name_other_document == undefined || path_other_document == "") 
							{
								flagEmployee = false;
							}
						});
					}
					rq_qualified_employee 		= $('[name="qualified_employee"]:checked').val();
					rq_doc_birth_certificate	= $('[name="doc_birth_certificate"]').val();
					rq_doc_proof_of_address		= $('[name="doc_proof_of_address"]').val();
					rq_doc_nss					= $('[name="doc_nss"]').val();
					rq_doc_ine					= $('[name="doc_ine"]').val();
					rq_doc_curp					= $('[name="doc_curp"]').val();
					rq_doc_rfc					= $('[name="doc_rfc"]').val();
					rq_doc_cv					= $('[name="doc_cv"]').val();
					rq_doc_proof_of_studies		= $('[name="doc_proof_of_studies"]').val();
					rq_doc_professional_license	= $('[name="doc_professional_license"]').val();
					rq_doc_requisition			= $('[name="doc_requisition"]').val();
	
					if (rq_qualified_employee == "1" && (rq_doc_birth_certificate == "" || rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_curp == "" || rq_doc_rfc == "" || rq_doc_cv == "" || rq_doc_proof_of_studies == ""|| rq_doc_professional_license == "" || rq_doc_requisition == "")) 
					{
						flagEmployee = false;
					}
					else if(rq_qualified_employee != "1" && (rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_rfc == "" || rq_doc_requisition == ""))
					{
						flagEmployee = false;
					}
					if (!flagEmployee) 
					{
						swal('', 'Por favor cargue los documentos restantes', 'error');
						return false;
					}
					if($('[name="rfc"]').val() != '' && $('#tax_regime').val() == '')
					{
						$('#tax_regime').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
						swal('', 'Por favor ingrese un régimen fiscal.', 'error');
						return false;
					}
					else
					{
						return true;
					}
				}		
			});
			
			$('.tr-remove').each(function(i,v)
			{
				url		= $(this).find('a').attr('href');
				path	= $(this).find('.class-path').val();
				name	= $(this).find('.name-doc').text().trim();

				@php
					$docs = view('components.documents.upload-files',[
						"classEx"				=> "form_other_doc",
						"classExContainer"		=> "image_success",
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
						"classExDelete"			=> "delete_other_doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"path_other_document[]\"",
						"classExRealPath"		=> "path path_other_document",
						"componentsExUp"		=>
						[
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "name_other_document",
								"attributeEx"	=> "name=\"name_other_document[]\" multiple data-validation=\"required\"" 
							]
						]
					])->render();
				@endphp
				docEmployee = '{!!preg_replace("/(\r)*(\n)*/", "", $docs)!!}';
				doc			= $(docEmployee);
				doc.find('[name="path_other_document[]"]').val(path);
				if(name == "Aviso de retención por crédito Infonavit")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'))
				}
				if(name == "Estado de cuenta")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Estado de cuenta">Estado de cuenta</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Estado de cuenta">Estado de cuenta</value>'))
				}
				if(name == "Cursos de capacitación")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Cursos de capacitación">Cursos de capacitación</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Cursos de capacitación">Cursos de capacitación</value>'))
				}
				if(name == "Carta de recomendación")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Carta de recomendación">Carta de recomendación</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Carta de recomendación">Carta de recomendación</value>'))
				}
				if(name == "Identificación")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Identificación">Identificación</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Identificación">Identificación</value>'))
				}
				if(name == "Hoja de expediente")
				{
					doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Hoja de expediente">Hoja de expediente</value>'));
				}
				else
				{
					doc.find('[name="name_other_document[]"]').append($('<option value="Hoja de expediente">Hoja de expediente</value>'))
				}
				$('#other_documents').append(doc);
				@ScriptSelect([
						"selects" => 
						[	
							[
								"identificator"          => "[name=\"name_other_document[]\"]", 
								"language"				 => "es",
								"placeholder"            => "Seleccione el tipo de documento", 
								"maximumSelectionLength" => "1"
							]
						]
					])
				@endScriptSelect
			});

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

			@ScriptSelect(
			[
				"selects" => 
				[	
					[
						"identificator"          => "[name=\"work_infonavit_discount_type\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el tipo de descuento", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_payment_way\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione la forma de pago", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_employer_register\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el registro patronal", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_periodicity\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione la periodicidad", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_status_employee\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el estado del empleado", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_type_employee\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el tipo de trabajador", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_state\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el estado", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_enterprise_old\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_direction\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione la dirección", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_status_imss\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el status de IMSS", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"sys_user\"]", 
						"language"				 => "es",
						"placeholder"            => "¿Es usuario del sistema?",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"computer_required\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione una opción", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_enterprise\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione una empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_department\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione una empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"regime_employee\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "#tax_regime", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el régimen fiscal",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_alimony_discount_type\"]", 
						"language"				 => "es",
						"placeholder"            => "Seleccione el tipo de descuento", 
						"maximumSelectionLength" => "1"
					]
				]
			])@endScriptSelect
			generalSelect({'selector':'#cp', 'model':2});
			generalSelect({'selector':'[name="state"]', 'model': 31});
			generalSelect({'selector':'[name="work_account"]', 'depends':'[name="work_enterprise"]', 'model':4});
			generalSelect({'selector':'[name="work_place[]"]', 'model':38});
			generalSelect({'selector':'[name="work_subdepartment[]"]', 'model': 39});
			generalSelect({'selector': '.bank', 'model': 28});
			generalSelect({'selector': '.js-projects', 'model': 24});
			generalSelect({'selector': '[name="work_wbs[]"]', 'depends':'.js-projects', 'model':1});
			generalSelect({'selector': '[name="work_account"]', 'depends':'[name="work_enterprise"]', 'model':4});
			generalSelect({'selector': '[name="work_employer_register"]','depends':'[name="work_enterprise"]', 'model':47});

			$('[name="imss"]').mask('0000000000-0',{placeholder: "__________-_"});
			$('[name="work_income_date"],[name="work_imss_date"],[name="work_down_date"],[name="work_ending_date"],[name="work_reentry_date"],[name="work_income_date_old"]').datepicker({ dateFormat: "dd-mm-yy" });
			$(document).on('change','[name="work_enterprise"]',function()
			{
				$('[name="work_account"]').val('').trigger('change');
				$('[name="work_employer_register"]').val('').trigger('change');
			})
			.on('change','[name="work_alimony_discount"]',function()
			{
				work_alimony_discount_type = $('[name="work_alimony_discount_type"] option:selected').val();
				if (work_alimony_discount_type == 2) 
				{
					if ($(this).val() > 100) 
					{
						$(this).val('');
						swal('','El porcentaje no puede ser mayor de 100','error');
					}
				}
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
				clabe	= $(this).parents('.class-banks').find('.clabe').val();
				$('.clabe').removeClass('valid').removeClass('error');
				flag	= false;
				if(clabe != "" && clabe != undefined)
				{ 
					$.ajax(
					{
						type    : 'post',
						url     : '{{ url("/configuration/employee/clabe/validate") }}',
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
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.clabe').removeClass('valid').removeClass('error');
							$('.clabe').val('');
						}
					});
				}
			})
			.on('change','.card',function()
			{
				card	= $(this).parents('.class-banks').find('.card').val();
				$('.card').removeClass('valid').removeClass('error');
				flag	= false;
				if(card != '' && card != undefined)
				{ 
					$.ajax(
					{
						type    : 'post',
						url     : '{{ url("configuration/employee/card/validate") }}',
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
			.on('change','.account,.bank',function()
			{
				bankid      = $(this).parents('.class-banks').find('.bank').val();
				account     = $(this).parents('.class-banks').find('.account').val();
				$('.account').removeClass('valid').removeClass('error');
				flag = false;
				if(account != '' && bankid != "")
				{  
					$.ajax(
					{
						type    : 'post',
						url     : '{{ url("/configuration/employee/account/validate") }}',
						data    : {'account':account,'bankid':bankid},
						success : function(data)
						{
							if(data.length > 0  )
							{
								swal("","El número de cuenta ya se encuentra registrado en este banco.","error");
								$('.account').removeClass('valid').addClass('error');
								$('.account').val('');
								flag = true;
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('.account').removeClass('valid').removeClass('error');
							$('.account').val('');
						}
					});
				}
			})
			.on('click','#add-bank',function()
            {				
                alias       = $(this).parents('.content-bank').find('.alias').val();
                bankid      = $(this).parents('.content-bank').find('.bank').val();
                bankName    = $(this).parents('.content-bank').find('.bank :selected').text();
                clabe       = $(this).parents('.content-bank').find('.clabe').val();
                account     = $(this).parents('.content-bank').find('.account').val();
                card        = $(this).parents('.content-bank').find('.card').val();
                branch      = $(this).parents('.content-bank').find('.branch_office').val();

                if(alias == "")
                {
                    swal('', 'Por favor ingrese un alias', 'error');
                    $('.alias').addClass('error');
                }
                else if(bankid.length>0)
                {
                    if (card == "" && clabe == "" && account == "")
					{
						$('.card, .clabe, .account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if (alias == "")
					{
						$(".alias").addClass("error");
						swal("", "Debe ingresar todos los campos requeridos", "error");
					}
					else if(clabe != "" && ($(this).parents('.content-bank').find(".clabe").hasClass("error") || clabe.length!=18))
					{
						swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
						$(this).parents('.content-bank').find(".clabe").addClass("error");
					}
					else if(card != "" && ($(this).parents('.content-bank').find(".card-number").hasClass("error") || card.length!=16))
					{
						swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
						$(this).parents('.content-bank').find(".card-number").addClass("error");
					}
					else if(account != "" && ($(this).parents('.content-bank').find(".account").hasClass("error") || (account.length>15 || account.length<5)))
					{
						swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
						$(this).parents('.content-bank').find(".account").addClass("error");
					}
                    else 
					{
						flag = false;
						$('#bodyEmployee .tr-employee-edit').each(function()
						{
							name_account 	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							name_card		= $(this).find('[name="card[]"]').val();

							if(clabe!= "" && name_clabe !="" && clabe == name_clabe)
							{
								swal('','La CLABE ya se encuentra registrada para este empleado.','error');
								$('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(card != '' && name_card != '' && card == name_card)
							{
								swal('','El número de tarjeta ya se encuentra registrado para este empleado','error');
								$('.card').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada para este empleado.','error');
								$('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						
						if(!flag)
						{
							@php
								$body		= [];
								$modelBody	= [];
								$modelHead	= [ "Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];

								$body = [ "classEx"	=> "tr-employee-edit",
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\""
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"1\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
												"classEx"		=> "idEmployee"
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"card[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"branch[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"          => "components.buttons.button",
												"variant"       => "red",
												"label"         => "<span class=\"icon-x\"></span>",
												"attributeEx"   => "type=\"button\"",
												"classEx"		=> "delete-bank"
											]
										]
									]
								];
								$modelBody[] = $body;
								$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead"    => true
								])->render();
							@endphp
							table	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank	= $(table);
							bank = rowColor('#bodyEmployee',bank);
							bank.find('[name="alias[]"]').parent().prepend(alias == '' ? '---' : alias);
							bank.find('[name="alias[]"]').val(alias);
							bank.find('[name="bank[]"]').parent().prepend(bankName);
							bank.find('[name="bank[]"]').val(bankid);
							bank.find('[name="clabe[]"]').parent().prepend(clabe == '' ? '---' : clabe);
							bank.find('[name="clabe[]"]').val(clabe);
							bank.find('[name="account[]"]').parent().prepend(account == '' ? '---' : account);
							bank.find('[name="account[]"]').val(account);
							bank.find('[name="card[]"]').parent().prepend(card == '' ? '---' : card);
							bank.find('[name="card[]"]').val(card);
							bank.find('[name="branch[]"]').parent().prepend(branch == '' ? '---' : branch);
							bank.find('[name="branch[]"]').val(branch);
							$('#bodyEmployee').append(bank);
							$('.card, .clabe, .account, .alias, .branch_office').removeClass('error').removeClass('valid').val('');
							$('.bank').val(null).trigger("change");
						}
                    }
                }
                else
                {
                    swal('', 'Seleccione un banco, por favor', 'error');
                    $('.bank').addClass('error');
                }
            })
			.on('click','#add-bank-alimony',function()
			{
				beneficiary	= $(this).parents('.content-bank-alimony').find('.beneficiary').val();
				alias		= $(this).parents('.content-bank-alimony').find('.alias').val();
				bankid		= $(this).parents('.content-bank-alimony').find('.bank').val();
				bankName	= $(this).parents('.content-bank-alimony').find('.bank :selected').text();
				clabe		= $(this).parents('.content-bank-alimony').find('.clabe').val();
				account		= $(this).parents('.content-bank-alimony').find('.account').val();
				card		= $(this).parents('.content-bank-alimony').find('.card').val();
				branch		= $(this).parents('.content-bank-alimony').find('.branch_office').val();

				if(alias == "" || beneficiary == "")
				{
					if(alias == "")
					{
						$(this).parents('.content-bank-alimony').find('.alias').addClass('error');
					}
					if(beneficiary == "")
					{
						$(this).parents('.content-bank-alimony').find('.beneficiary').addClass('error');
					}
					swal('', 'Por favor ingrese un beneficiario y un alias', 'error');	
				}
				else if(bankid.length>0)
				{
					if (card == "" && clabe == "" && account == "")
					{
						$('.card, .clabe, .account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if (alias == "")
					{
						$(".alias").addClass("error");
						swal("", "Debe ingresar todos los campos requeridos", "error");
					}
					else if(clabe != "" && ($(this).parents('.content-bank-alimony').find(".clabe").hasClass("error") || clabe.length!=18))
					{
						swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
						$(this).parents('.content-bank-alimony').find(".clabe").addClass("error");
					}
					else if(card != "" && ($(this).parents('.content-bank-alimony').find(".card-number").hasClass("error") || card.length!=16))
					{
						swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
						$(this).parents('.content-bank-alimony').find(".card-number").addClass("error");
					}
					else if(account != "" && ($(this).parents('.content-bank-alimony').find(".account").hasClass("error") || (account.length>15 || account.length<5)))
					{
						swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
						$(this).parents('.content-bank-alimony').find(".account").addClass("error");
					}
					else  
					{
						flag = false;
						$('#bodyAlimony .tr-employee-edit').each(function()
						{
							name_account	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();

							if(clabe!= "" && name_clabe!= "" &&clabe == name_clabe)
							{
								swal('','La CLABE ya se encuentra registrada para este beneficiario.','error');
								$('clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada para este beneficiario.','error');
								$('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
						})
						if(!flag)
						{
							@php
								$body		= [];
								$modelBody	= [];
								$modelHead	= ["Beneficiario","Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
								
								$body = [ "classEx"	=> "tr-employee-edit",
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\""
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"2\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
												"classEx"		=> "idEmployee"
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"card[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"branch[]\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind"          => "components.buttons.button",
												"variant"       => "red",
												"label"         => "<span class=\"icon-x\"></span>",
												"attributeEx"   => "type=\"button\"",
												"classEx"		=> "delete-bank"
											]
										]
									]
								];
								$modelBody[]	= $body;
								$table			= view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead"    => true
								])->render();
							@endphp
							table	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank	= $(table);
							bank = rowColor('#bodyAlimony',bank);
							bank.find('[name="beneficiary[]"]').parent().prepend(beneficiary == '' ? '---' : beneficiary);
							bank.find('[name="beneficiary[]"]').val(beneficiary);
							bank.find('[name="alias[]"]').parent().prepend(alias == '' ? '---' : alias);
							bank.find('[name="alias[]"]').val(alias);
							bank.find('[name="bank[]"]').parent().prepend(bankName);
							bank.find('[name="bank[]"]').val(bankid);
							bank.find('[name="clabe[]"]').parent().prepend(clabe == '' ? '---' : clabe);
							bank.find('[name="clabe[]"]').val(clabe);
							bank.find('[name="account[]"]').parent().prepend(account == '' ? '---' : account);
							bank.find('[name="account[]"]').val(account);
							bank.find('[name="card[]"]').parent().prepend(card == '' ? '---' : card);
							bank.find('[name="card[]"]').val(card);
							bank.find('[name="branch[]"]').parent().prepend(branch == '' ? '---' : branch);
							bank.find('[name="branch[]"]').val(branch);
							$('#bodyAlimony').append(bank);
							$('.card, .clabe, .account, .alias, .beneficiary, .branch_office').removeClass('error').removeClass('valid').val('');
							$('.bank').val(null).trigger("change"); 
						}
					}	
				}
				else
				{
					swal('', 'Seleccione un banco, por favor', 'error');
					$('.bank').addClass('error');
				}
			})
			.on('click','.delete-bank', function()
			{
				$(this).parents('.tr-employee-edit').remove();
			})
			.on('change','#infonavit',function()
			{
				if($(this).is(':checked'))
				{
					$('.infonavit-container').stop(true,true).fadeIn();
					@ScriptSelect(
					[
						"selects" => 
						[	
							[
								"identificator"          => "[name=\"work_infonavit_discount_type\"]", 
								"language"				 => "es",
								"placeholder"            => "Seleccione el tipo de descuento", 
								"maximumSelectionLength" => "1"
							]
						]
					])@endScriptSelect
				}
				else
				{
					$('.infonavit-container').stop(true,true).fadeOut();
				}
			})
			.on('change','#alimony',function()
			{
				if($(this).is(':checked'))
				{
					$('.alimony-container').stop(true,true).fadeIn();
					$('#accounts-alimony').stop(true,true).fadeIn();
					@ScriptSelect(
					[
						"selects" => 
						[	
							[
								"identificator"          => "[name=\"work_alimony_discount_type\"]", 
								"language"				 => "es",
								"placeholder"            => "Seleccione el tipo de descuento", 
								"maximumSelectionLength" => "1"
							]
						]
					])@endScriptSelect
					generalSelect({'selector': '.bank', 'model': 28});
				}
				else
				{
					$('.alimony-container').stop(true,true).fadeOut();
					$('#accounts-alimony').stop(true,true).fadeOut();
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
						text		: "Si deshabilita la edición las modificaciones realizadas en INFORMACIÓN LABORAL no serán guardadas",
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
				project_id = $('option:selected',this).val();
				if (project_id != undefined) 
				{
					$('[name="work_wbs[]"]').empty();
					$('.select_father').show();
					$.each(generalSelectProject, function(i,v)
					{
						if(project_id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.select_father').show();
								generalSelect({'selector': '[name="work_wbs[]"]', 'depends':'.js-projects', 'model':1});
							}
							else
							{
								$('.select_father').hide();
							}
						}
					});
				}
				else
				{
					$('[name="work_wbs[]"]').empty();
					$('.select_father').hide();
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
					$('.btn_disable').attr('disabled', true);	
					$.ajax(
					{
						type       : 'post',
						url        : '{{ url("/administration/requisition/upload") }}',
						data       : formData,
						contentType: false,
						processData: false,
						success    : function(r)
						{
							if(r.error == 'DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
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
					$docs = view('components.documents.upload-files',[
						"classEx"				=> "form_other_doc",
						"classExInput"			=> "pathActioner",
						"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
						"classExDelete"			=> "delete_other_doc",
						"attributeExRealPath"	=> "type=\"hidden\" name=\"path_other_document[]\"",
						"classExRealPath"		=> "path path_other_document",
						"componentsExUp"		=>
						[
							[
								"kind" => "components.labels.label", 
								"label" => "Seleccione el tipo de documento:"
							],
							[
								"kind" 			=> "components.inputs.select",
								"classEx" 		=> "name_other_document",
								"attributeEx"	=> "name=\"name_other_document[]\" multiple data-validation=\"required\"" 
							]
						]
					])->render();
				@endphp
				docEmployee = '{!!preg_replace("/(\r)*(\n)*/", "", $docs)!!}';
				doc			= $(docEmployee);
				doc.find('[name="name_other_document[]"]').append($('<option value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'))
					.append($('<option value="Estado de cuenta">Estado de cuenta</value>'))
					.append($('<option value="Cursos de capacitación">Cursos de capacitación</value>'))
					.append($('<option value="Carta de recomendación">Carta de recomendación</value>'))
					.append($('<option value="Identificación">Identificación</value>'))
					.append($('<option value="Hoja de expediente">Hoja de expediente</value>'));
				
				$('#other_documents').append(doc);
				@ScriptSelect([
						"selects" => 
						[	
							[
								"identificator"          => "[name=\"name_other_document[]\"]", 
								"language"				 => "es",
								"placeholder"            => "Seleccione el tipo de documento", 
								"maximumSelectionLength" => "1"
							]
						]
					])
				@endScriptSelect
			})
			.on('click','.delete_other_doc',function()
			{
				path				= $(this).parents('.docs-p').find('[name="path_other_document[]"]').val();
				div_deleted_docs	= "<input type='hidden' name='delete_other_documents[]' value='"+path+"'>";
				$('#other_documents').append(div_deleted_docs);
				$(this).parents('.docs-p').remove();
			})
		});
	</script>
@endsection