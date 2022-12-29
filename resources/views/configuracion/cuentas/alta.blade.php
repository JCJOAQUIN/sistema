@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") NUEVA CUENTA @endcomponent
	@component("components.labels.subtitle") Para agregar una cuenta nueva es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", [ "attributeEx" => "id=\"form-content\"" ])
		@component("components.containers.container-form", ["classEx" => "accounts-form"])
			<div class="col-span-2 selectEnterprise">
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
					}
				@endphp
				@component("components.labels.label") Empresa: @endcomponent
				@component("components.inputs.select", ["options" => $options])
					@slot("attributeEx")
						name="enterprise_id" data-validation="required"
					@endslot
					@slot("classEx")
						js-enterprises removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de cuenta: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="account"
						placeholder="Ingrese el número de cuenta"
						data-validation="server"
						data-validation-url="{{ route('account.validation') }}"
						data-validation-req-params="{{ json_encode(array('enterprise_id'=>0)) }}"
					@endslot
					@slot("classEx")
						account removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre de cuenta: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="description"
						id="description"
						placeholder="Ingrese el nombre de la cuenta"
						rows="3"
						data-validation="required"
					@endslot
					@slot("classEx")
						description removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de Gasto: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="content"
						id="content" 
						placeholder="Ingrese el tipo de gasto" 
						rows="3"
						data-validation="required"
					@endslot
					@slot("classEx")
						content removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Saldo: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" 
						name="balance" 
						id="balance" 
						placeholder="$0.00"
						data-validation="required"
					@endslot
					@slot("classEx")
						balance removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Visible: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("attributeEx")
							type="radio" 
							name="visible" 
							id="novisible" 
							value="0"
							checked="checked"
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("attributeEx")
							type="radio" 
							name="visible" 
							id="visible" 
							value="1"
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"])
					@slot("attributeEx")
						type="submit" 
						name="add" 
						id="add"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
	@endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('account.store')."\" id=\"container-alta\""])
		@Table([
			"modelHead" =>
			[
				[
					["value" => "Empresa"],
					["value" => "Número de cuenta"],
					["value" => "Cuenta"],
					["value" => "Tipo de gasto"],
					["value" => "Saldo"],
					["value" => "Acción"],
				]
			],
			"modelBody"			=> [],
			"attributeEx"		=> "id=\"table-show\"",
			"attributeExBody"	=> "id=\"body\"",
		])
		@endTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button")
				@slot("attributeEx")
					type="submit" name="enviar"
				@endslot
				REGISTRAR
			@endcomponent
			@component("components.buttons.button", ["variant" => "reset"])
				@slot("attributeEx")
					type="reset" name="borrar"
				@endslot
				@slot("classEx")
					btn-delete-form
				@endslot
				Borrar campos
			@endcomponent
		</div>	
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			validateAccount();
			$.validate(
			{
				modules		: 'security',
				form  		: '#container-alta',
				onSuccess	: function($form)
				{
					rows = $('#body .tr').length
					if(rows > 0)
					{
						return true;
					}
					else
					{
						swal('', 'Agregue mínimo una cuenta.', 'error');
						return false;
					}
				}
			});
			@php
				$selects = collect(
					[
						[
							"identificator"          => ".js-enterprises", 
							"placeholder"            => "Seleccione la empresa", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			
			$('input[name="balance"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('.account').numeric({ negative : false, decimal : false });
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
						$('.removeselect').val(null).trigger('change');
						$('.removeselect').removeClass('error').removeClass('valid');
						$('.account').removeAttr('style');
						$('.form-error').remove();
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-enterprises').parent().find('.form-error').remove();
				enterpriseid = $('select[name="enterprise_id"] option:selected').val();
				$('input[name="account"]').attr('data-validation-req-params','{"enterprise_id":'+enterpriseid+'}').val('');
			})
			.on('change','[name="visible"]',function()
			{
				$('.radioValidation').addClass('invisible');
			})
			.on('click','.delete-item', function() 
			{
				$(this).parents('.tr').remove();
				cuentas	= $('#body .tr').length;
			})
			.on('focusout','.balance,.account',function()
			{
				if($(this).val() < -0 || !($.isNumeric($(this).val())))
				{
					$(this).val('');
				}
			});
		});

		function validateAccount()
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
					account			= $('.accounts-form').find('.account').val();
					accValid		= $('[name="account"], .description, .content').hasClass('valid');
					balance 		= $('#balance').val();
					description		= $('textarea[id="description"]').val();
					content 		= $('textarea[id="content"]').val();
					enterprise 		= $('.js-enterprises option:selected').text();
					identerprise	= $('.js-enterprises option:selected').val();
					id_enterprise	= $('.js-enterprises').val();
					selectable		= $('input[name="visible"]:checked').val();
					accEnt			= false;
					$('#body .tr').each(function()
					{
						account_tr		= $(this).find('[name="account[]"]').val();
						enterprise_tr	= $(this).find('[name="idEnterprise[]"]').val();
						if(account_tr == account && enterprise_tr == identerprise)
						{
							accEnt = true;
						}
					});					 
					if(accEnt)
					{
						swal('','El número de cuenta ya ha sido agregado previamente con está empresa.','error');
						$('.account').removeClass('valid').addClass('error');
						return false;
					}
					else if(accValid)
					{
						@php
						$modelBody = [];
							$modelHead =
							[
								["value" => "Empresa", "show" => "true"],
								["value" => "Número de cuenta", "show" => "true"],
								["value" => "Cuenta"],
								["value" => "Tipo de gasto"],
								["value" => "Saldo"],
								["value" => "Acción"],
							];
							$modelBody[] = 
							[
								"classEx" => "tr",
								[
									"show" => "true",
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx" 	=> "enterprise_class"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idEnterprise[]\"",
										]
									]
								],
								[
									"show" => "true",
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "account_class"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "description_class"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"hidden\" name=\"description[]\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "content_class"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"hidden\" name=\"content[]\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "balance_class"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"hidden\" name=\"balance[]\"",
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"hidden\" name=\"selectable[]\"",
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 			=> "components.buttons.button",
											"classEx" 		=> "delete-item",
											"attributeEx"	=> "type=\"button\"",
											"label"			=> "<span class=\"icon-x delete-span\"></span>",
											"variant"		=> "red"
										]
									]
								]
							];
							
							$table = view('components.tables.table',[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody, 
									"noHead"	=> "true"
								])->render();
						@endphp
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						accounts = $(table);
						accounts.find('.enterprise_class').text(enterprise);
						accounts.find('[name="idEnterprise[]"]').val(identerprise);
						accounts.find('.account_class').text(account);
						accounts.find('[name="account[]"]').val(account);
						accounts.find('.description_class').text(description);
						accounts.find('[name="description[]"]').val(description);
						accounts.find('.content_class').text(content);
						accounts.find('[name="content[]"]').val(content);
						accounts.find('.balance_class').text(balance);
						accounts.find('[name="balance[]"]').val(balance);
						accounts.find('[name="selectable[]"]').val(selectable);
						$('#body').append(accounts);
						$('.account').val('');
						$('.account').removeClass('valid');
						$('.account').removeClass('error');
						$('#balance').removeClass('error');
						$('#balance').removeClass('valid');
						$('#balance').val('');
						$('.js-enterprises').val(null).trigger('change').removeClass('error');
						$('textarea[id="description"]').val('').removeClass('valid');
						$('textarea[id="content"]').val('').removeClass('valid');
						$('.account').val('').removeClass('valid');
						$('.account').val('').removeClass('error');
						$('#novisible').prop('checked', true);
						cuentas	= $('#body .tr').length;
						if (cuentas > 0) 
						{
							$('#table-show').show();
						}
					}
					else
					{
						swal('', 'Espere a que se verifique el dato ingresado.', 'warning');
						return false;
					}
					return false;
				}
			});
		}
	</script>
@endsection