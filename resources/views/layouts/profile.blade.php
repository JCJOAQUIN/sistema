@extends('layouts.child_module')

@section('title', $title)

@section('data')
	<div class="container-full">
		<p>¡Hola, {{ Auth::user()->name }}!</p>
		<div class="sm:text-center text-left my-5">
			A continuación encontrará un resumen de su perfil y podrá modificar algunos datos básicos:
		</div>
		@php
			$modelTable = 
			[
				["Nombre:", Auth::user()->name." ".Auth::user()->last_name." ".Auth::user()->scnd_last_name],
				["Correo electrónico:", Auth::user()->email],
				["Contraseña:", view('components.buttons.button', ['label' => 'Modificar contraseña', 'attributeEx' => "href=\"".route('profile.password')."\"", 'buttonElement' =>'a', 'variant' => 'red'])->render()],
				["Teléfono", view('components.inputs.input-text', ['attributeEx' => 'type="text" name="phone" placeholder="Ingrese el teléfono" value="'.Auth::user()->phone.'" data-validation="phone"', 'classEx' => 'phone text-right'])->render()],
				["Extensión:", view('components.inputs.input-text', ['attributeEx' => 'type="text" name="extension" placeholder="Ingrese la extensión" value="'.Auth::user()->extension.'"', 'classEx' => 'phone text-right'])->render()],
				["Dirección:", Auth::user()->area->name],
				["Departamento:", (Auth::user()->departament != '' ? Auth::user()->departament->name : '')],
				["Recibir Email:", '<div class="flex justify-end p-0 space-x-2">'.view('components.buttons.button-approval', ['attributeEx' => 'name="mails" id="no" value="0" '.(Auth::user()->notification == 0 ? 'checked' : ''), 'slot' => 'No', 'classExContainer' => 'inline-block'])->render().view('components.buttons.button-approval', ['attributeEx' => 'name="mails" id="si" value="1" '.(Auth::user()->notification == 1 ? 'checked' : ''), 'slot' => 'Si', 'classExContainer' => 'inline-block'])->render().'</div>']
			];
		@endphp
		@component('components.forms.form',['methodEx' => 'PUT'])
			@slot('attributeEx')
				action="{{ route('profile.update', Auth::user()->id) }}" method="POST" id="container-alta"
			@endslot
			@slot("componentsEx")
				@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Datos personales", "classEx" => "mt-6"]) @endcomponent
				@component('components.labels.title-divisor') EMPRESAS @endcomponent
				<div class="flex flex-wrap justify-around my-4">
					@foreach(Auth::user()->enterprise->where('status', 'ACTIVE') as $enterprise)
						<div class="lg:w-1/3 md:w-1/2 w-full text-center p-5">
							<div class="w-24 h-24 bg-no-repeat bg-contain bg-center mx-auto rounded-full border-4 border-red-400" style="background-image: url({{ url('images/enterprise/'.$enterprise->path) }});"></div>
							<div class="text-red-400 pt-4 pb-2">{{ $enterprise->name }}</div>
							<div class="text-gray-500 break-words">{{ $enterprise->details }}</div>
						</div>
					@endforeach
				</div>
				@component('components.labels.title-divisor') CUENTAS BANCARIAS @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component("components.labels.label") Banco: @endcomponent
						@component("components.inputs.select", ["options" => collect(), "classEx" => "bank"]) @endcomponent
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
				@component('components.labels.not-found', ['text' => 'No se han encontrado cuentas registradas', 'classEx' => 'profile-no-accounts'.(Auth::user()->employee->where('visible',1)->count() > 0 ? ' hidden' : '')]) @endcomponent
				@php
					$modelHead = [ "Banco", "Alias", "Número de tarjeta", "CLABE interbancaria", "Número de cuenta", "Acciones"];
					$modelBody = [];
					foreach(Auth::user()->employee->where('visible',1) as $emp)
					{
						$body = [
							[
								"content" => 
								[
									["label" => $emp->bank->description],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idEmployee[]\" value=\"".$emp->idEmployee."\"", "classEx" => "idEmployee"],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"bank[]\" value=\"".$emp->idBanks."\""],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"banksNames[]\" value=\"".$emp->bank->description."\""]
								]
							],
							[
								"content" => 
								[
									["label" => ($emp->alias == "" ? "---" : $emp->alias)],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"alias[]\" value=\"".$emp->alias."\""]
								]
							],
							[
								"content" => 
								[
									["label" => ($emp->cardNumber == "" ? "---" : $emp->cardNumber)],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"card[]\" value=\"".$emp->cardNumber."\""]
								]
							],
							[
								"content" =>
								[
									["label" => ($emp->clabe == "" ? "---" : $emp->clabe)],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"clabe[]\" value=\"".$emp->clabe."\""]
								]
							],
							[
								"content" =>
								[
									["label" => ($emp->account == "" ? "---" : $emp->account)],
									["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"account[]\" value=\"".$emp->account."\""]
								]
							],
							[
								"content" =>
								[
									["kind" => "components.buttons.button", "variant" => "red", "attributeEx" => "type=\"button\"", "classEx" => "delete-item", "label" => "<span class=\"icon-x\"></span>"]
								]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				<div class="bank_accounts{{ (Auth::user()->employee->where('visible',1)->count() > 0 ? '' : ' hidden') }}">
					@AlwaysVisibleTable(["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeExBody" => "id=\"banks-body\"", 'classEx' => 'header-account-table']) @endAlwaysVisibleTable
				</div>
				<div id="delete"></div>
			@endslot
			<div class="text-center mb-6">
				@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "GUARDAR CAMBIOS"]) @endcomponent
			</div>
		@endcomponent
	</div>
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form     : '#container-alta',
				modeles  : 'logic',
				onSuccess: function($form)
				{
					flag = false;
					$("#banks-body .tr").each(function(i, v)
					{
						card		= $(this).find('.card').val();
						clabe		= $(this).find('.clabe').val();
						account 	= $(this).find('.account').val();
						if (card == "" && clabe == "" && account == "")
						{
							flag = true;
						}
					});
					if(flag == true)
					{
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
						return false;
					}
					else
					{
						return true;
					}
				}
			});
			$('.phone,.extension,.card,.clabe,.account').numeric(false);
			generalSelect({'selector': '.bank', 'model': 27});
			$(document).on('click','.delete-item', function()
			{
				value = $(this).parent().parent().parent().find('.idEmployee').val();
				del = $('<input type="hidden" name="delete[]">').val(value);
				$('#delete').append(del);
				$(this).parent().parent().parent().remove();
				tempCount = $('#banks-body .tr').length;
				if(tempCount==0)
				{
					$('.bank_accounts').hide();
					$('.profile-no-accounts').show();
				}
			})
			.on('click','#add',function()
			{
				$('.card, .clabe, .account, .bank').removeClass('error').removeClass('valid');
				card       = $(this).parent().parent().parent().find('.card').val();
				clabe      = $(this).parent().parent().parent().find('.clabe').val();
				account    = $(this).parent().parent().parent().find('.account').val();
				bank       = $(this).parent().parent().parent().find('.bank').val();
				alias      = $(this).parent().parent().parent().find('.alias').val();
				bankName   = $(this).parent().parent().parent().find('.bank :selected').text();
				minLength  = 5;
				maxLength  = 15;
				mainArray  = [];
				clabeArray = [];
				i          = 0;
				if(bank == '')
				{
					swal('', 'Seleccione un banco, por favor.', 'error');
					$('.bank').addClass('error');
				}
				else
				{
					$.ajax(
					{
						type	: "post",
						url 	: "{{url('profile/validate')}}",
						data	: 
						{
							'account_number'  : account,
							'bank_description': bank,
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
								$("[name='idEmployee[]']").each( function()
								{
									bankTable    	= $(this).parent("td").parent("tr").find("[name='banksNames[]']").val();
									accountNumber	= $(this).parent("td").parent("tr").find("[name='account[]']").val();
									if(accountNumber != "")
									{
										mainArray[i] = [bankTable+accountNumber];
										i++;
									}
								});
								for(j = 0; j<mainArray.length; j++)
								{
									if(mainArray[j]== bankName+account)
									{
										swal('', 'El número de cuenta: '+account+' asociada a: '+bankName+' ya se encuentra registrada, por favor ingrese un número de cuenta o banco diferente', 'error');
										return false;
									}
								}
								i = 0;
								$("[name='clabe[]']").each( function()
								{
									if($(this).val() != "")
									{
										clabeArray[i] = $(this).val();
										i++;
									}
								});
								for (j = 0; j < clabeArray.length; j++)
								{
									if (clabeArray[j] == clabe)
									{
										swal("", "La clabe interbancaria: "+clabe+" ya se encuentra registrada, favor de ingresar una diferente", "error");
										return false;
									}
								}
								if(bank.length>0)
								{
									if (card == "" && clabe == "" && account == "")
									{
										$('.card, .clabe, .account').addClass('error');
										swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
									}
									else 
									{
										if($('.card').hasClass('error') || $('.clabe').hasClass('error') || $('.account').hasClass('error'))
										{
											swal('', 'Por favor ingrese datos correctos.', 'error');
										}
										else if(card != "" && ($('.card').hasClass('error') || card.length!=16))
										{
											swal('', 'Por favor, debe ingresar 16 dígitos del número de tarjeta.', 'error');
											$('.card').addClass('error');
										}
										else if(clabe != "" && ($('.clabe').hasClass('error') || clabe.length!=18))
										{
											swal('', 'Por favor, debe ingresar 18 dígitos de la CLABE.', 'error');
											$('.clabe').addClass('error');
										}
										else if(account != "" && ($('.account').hasClass('error') || (account.length<minLength || account.length>maxLength)))
										{
											swal('', 'Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.', 'error');
											$('.account').addClass('error');
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
																	["kind" => "components.labels.label", "classEx" => "bankname_class"],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idEmployee[]\" value=\"x\""],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"bank[]\""],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"banksNames[]\""]
																]
															],
															[
																"content" =>
																[
																	["kind" => "components.labels.label", "classEx" => "alias_class"],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"alias[]\""]
																]
															],
															[
																"content" =>
																[
																	["kind" => "components.labels.label", "classEx" => "card_class"],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"card[]\""]
																]
															],
															[
																"content" =>
																[
																	["kind" => "components.labels.label", "classEx" => "clabe_class"],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"clabe[]\""]
																]
															],
															[
																"content" =>
																[
																	["kind" => "components.labels.label", "classEx" => "account_class"],
																	["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"account[]\""]
																]
															],
															[
																"content" =>
																[
																	["kind" => "components.buttons.button", "variant" => "red", "attributeEx" => "type=\"button\"", "classEx" => "delete-item", "label" => "<span class=\"icon-x\"></span>"]
																]
															]
														]
													],
													"themeBody" => "striped",
													"noHead"	=> true,
												])->render();
												$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
											@endphp
											table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
											bankRow = $(table);
											bankRow = rowColor('#rqTypeCategoryBody', bankRow);
											bankRow.find('.bankname_class').text(bankName);
											bankRow.find('[name="bank[]"]').val(bank);
											bankRow.find('.alias_class').text(alias =='' ? '---' : alias);
											bankRow.find('[name="alias[]"]').val(alias);
											bankRow.find('.card_class').text(card =='' ? '---' : card);
											bankRow.find('[name="card[]"]').val(card);
											bankRow.find('.clabe_class').text(clabe =='' ? '---' : clabe);
											bankRow.find('[name="clabe[]"]').val(clabe);
											bankRow.find('.account_class').text(account =='' ? '---' : account);
											bankRow.find('[name="account[]"]').val(account);
											$('#banks-body').append(bankRow);
											$('.card, .clabe, .account,.alias').removeClass('valid').val('');
											$('.bank').removeClass('error');
											$('.bank').val(0).trigger("change");
											$('.profile-no-accounts').hide();
											$('.bank_accounts').show();
										}
									}
								}
								else
								{
									swal('', 'Seleccione un banco, por favor.', 'error');
									$('.bank').addClass('error');
								}
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					});
				}
			});
		});
	</script>
@endsection
