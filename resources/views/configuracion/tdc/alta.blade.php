@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("credit-card.store")."\" id=\"container-alta\""])
		@component("components.labels.title-divisor") DATOS DE TARJETA DE CRÉDITO @endcomponent
		@component("components.labels.subtitle") Para agregar una tarjeta de crédito nueva es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
					}
					$attributeEx = "multiple=\"multiple\" name=\"enterprise_id\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Clasificación del gasto: @endcomponent
				@php
					$options = collect();
					$attributeEx = "multiple=\"multiple\" name=\"account_id\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Banco: @endcomponent
				@php
					$options = collect();
					$attributeEx = "multiple=\"multiple\" name=\"bank_id\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
 				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Principal \ Adicional: @endcomponent
				@php
					$options = collect(
						[
							["value" => "1", "description" => "Principal"],
							["value" => "2", "description" => "Adicional"],
						]
					);
					$attributeEx = "multiple=\"multiple\" name=\"principal_aditional\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de tarjeta principal (solo si es adicional): @endcomponent
				@php
					$options = collect();
					foreach(App\CreditCards::where('principal_aditional',1)->get() as $tdcp)
					{
						$options = $options->concat([["value" => $tdcp->idcreditCard, "description" => $tdcp->credit_card]]);
					}
					$attributeEx = "multiple=\"multiple\" name=\"principal_card_id\" data-validation=\"required\" disabled=\"disabled\"";
					$classEx = "form-control removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre (el que viene en la tarjeta): @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="name_credit_card"
						placeholder="Ingrese el nombre"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="alias"
						placeholder="Ingrese el alias"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Asignación (responsable): @endcomponent
				@php
					$options = collect();
					$attributeEx = "multiple=\"multiple\" name=\"assignment\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de tarjeta de crédito: @endcomponent
				@component('components.inputs.input-text')
					@slot("classEx")
						credit-card
					@endslot
					@slot("attributeEx")
						type="text"
						name="credit_card"
						placeholder="Ingrese el número de tarjeta"
						data-validation="server"
						data-validation-url="{{ route('credit-card.validation') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent
				@php
					$options = collect(
						[
							["value" => "1", "description" => "Vigente"],
							["value" => "2", "description" => "Bloqueada"],
							["value" => "3", "description" => "Cancelada"],
						]
					);
					$attributeEx = "multiple=\"multiple\" name=\"status\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de Crédito: @endcomponent
				@php
					$options = collect(
						[
							["value" => "1", "description" => "Personal"],
							["value" => "2", "description" => "Empresarial"],
							["value" => "3", "description" => "Ágil"],
							["value" => "4", "description" => "Otro"],
						]
					);
					$attributeEx = "multiple=\"multiple\" name=\"type_credit\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						hidden
					@endslot
					@slot("attributeEx")
						type="text"
						name="type_credit_other"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha de corte: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="cutoff_date"
						readonly="readonly"
						placeholder="Seleccione la fecha de corte"
						data-validation="required"
					@endslot
					@slot("classEx")
						datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha de pago: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="payment_date"
						readonly="readonly"
						placeholder="Seleccione la fecha de pago"
						data-validation="required"
					@endslot
					@slot("classEx")
						datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Límite de crédito: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text"
						name="limit_credit"
						placeholder="Ingrese el límite de crédito"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de moneda: @endcomponent
				@php
					$options = collect(
						[
							["value"=>"MXN", "description"=>"MXN"],
							["value"=>"USD", "description"=>"USD"],
							["value"=>"EUR", "description"=>"EUR"],
							["value"=>"Otro", "description"=>"Otro"],
						]
					);

					$attributeEx = "name=\"type_currency\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "form-control removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
		@endcomponent
		<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx")
					type="submit"
					name="enviar"
				@endslot
				REGISTRAR
			@endcomponent
			@component('components.buttons.button',["variant" => "reset"])
				@slot("attributeEx")
					type="reset"
					name="borrar"
				@endslot
				@slot("classEx")
					delete-form
				@endslot
				BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{asset('js/jquery.mask.js')}}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
		$.validate(
		{
			form	:	'#container-alta',
			modules	:	'security',
			onError :	function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				return false;
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button             	: false,
					closeOnClickOutside	: false,
					closeOnEsc         	: false
				});
			}
		});
		@php
			$selects = collect(
				[
					[
						"identificator"          => "[name=\"enterprise_id\"]",
						"placeholder"            => "Seleccione la empresa",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"principal_aditional\"]",
						"placeholder"            => "Seleccione la opción",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"principal_card_id\"]",
						"placeholder"            => "Seleccione el número de tarjeta",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"type_currency\"]",
						"placeholder"            => "Seleccione el tipo de moneda",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"type_credit\"]",
						"placeholder"            => "Seleccione el tipo de crédito",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"status\"]",
						"placeholder"            => "Seleccione el estado",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"year\"]",
						"placeholder"            => "Seleccione el año",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"month\"]",
						"placeholder"            => "Seleccione el mes",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		generalSelect({'selector':'[name="account_id"]', 'depends':'[name="enterprise_id"]', 'model':10});
		generalSelect({'selector':'[name="bank_id"]', 'model':27});
		generalSelect({'selector':'[name=\"assignment\"]', 'model':36});

		$('.account,.clabe').numeric({ negative : false, decimal : false });
		$('input[name="limit_credit"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
		$('[name="credit_card"],[name="principal_card"]');
		$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
		$(document).on('change','[name="enterprise_id"]',function()
		{
			$('[name="account_id"]').empty();
			generalSelect({'selector':'[name="account_id"]', 'depends':'[name="enterprise_id"]', 'model':10});
		})
		.on('change','[name="principal_aditional"]',function()
		{
			$('[name="credit_card"]').removeClass('valid');
			principalAditional	=	$("option:selected", this).val();
			bankOption			=	$("[name='bank_id'] option:selected").val();
			if (principalAditional != undefined)
			{
				if (bankOption != undefined)
				{
					$('.help-block').addClass('hidden');
					$('[name="credit_card"]').attr('data-validation-req-params', '{"bank_id": '+bankOption+', "principal_id": '+principalAditional+'}').removeClass('error');
				}
				else
				{
					$('.help-block').addClass('hidden');
					$('[name="credit_card"]').attr('data-validation-req-params', '{"principal_id": '+principalAditional+'}').removeClass('error');
				}
				if ($(this).val() == 2)
				{
					$('[name="principal_card_id"]').prop('disabled',false);
				}
				else
				{
					$('[name="principal_card_id"]').prop('disabled',true);
					$('[name="principal_card_id"]').val(null).trigger('change').removeClass('error');
				}
			}
		})
		.on('change','[name="bank_id"]',function()
		{
			bankOption			=	$("option:selected", this).val();
			principalAditional	=	$("[name='principal_aditional'] option:selected").val();

			if (bankOption != undefined)
			{
				if (principalAditional != undefined)
				{
					$('.help-block').addClass('hidden');
					$('[name="credit_card"]').attr('data-validation-req-params', '{"bank_id": '+bankOption+', "principal_id": '+principalAditional+'}').removeClass('error').removeAttr('style').val('');
				}
				else
				{
					$('[name="credit_card"]').removeAttr('data-validation-req-params', '{"bank_id": '+bankOption+'}');
				}
			}
		})
		.on('click','.delete-form',function(e)
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
					$('.removeselect').trigger('change').val(null);
					form[0].reset();
					$('#body').html('');
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','[name="limit_credit"]',function()
		{
			if (Number($(this).val()) == "0")
			{
				swal('','El límite de crédito no puede ser 0','error');
				$(this).val('');
			}
		});
	});
</script>
@endsection