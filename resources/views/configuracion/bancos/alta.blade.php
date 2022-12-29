@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('banks.store')."\" id=\"container-alta\""])
		@component("components.labels.title-divisor") Detalles de cuentas bancarias @endcomponent
		@component("components.labels.subtitle") Para agregar una cuenta nueva es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						$options =  $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
					}
				@endphp
				@component("components.labels.label") Empresa: @endcomponent
				@component("components.inputs.select", ["options" => $options, "attributeEx" => "data-validation=\"required\" name=\"enterprise_id\"", "classEx" => "js-enterprises removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Clasificación del gasto: @endcomponent
				@php
					$options = collect();
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => "data-validation=\"required\" name=\"account_id\"", "classEx" => "js-accounts removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
				@endphp
				@component("components.labels.label") Banco: @endcomponent
				@component("components.inputs.select", ["options" => $options, "attributeEx" => "data-validation=\"required\" name=\"bank_id\"", "classEx" => "js-bank removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "alias", "attributeEx" => "type=\"text\" name=\"alias\" placeholder=\"Ingrese el alias\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Cuenta bancaria: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "account", "attributeEx" => "type=\"text\" name=\"account\" id=\"account\" placeholder=\"Ingrese la cuenta bancaria\" data-validation=\"server\" data-validation-url=\"".route('banks.validateAccount')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Sucursal (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "branch_office", "attributeEx" => "type=\"text\" name=\"branch\" placeholder=\"Ingrese la sucursal\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Referencia (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "reference", "attributeEx" => "type=\"text\" name=\"reference\" placeholder=\"Ingrese la referencia\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") CLABE: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "clabe", "attributeEx" => "type=\"text\" name=\"clabe\" id=\"clabe\" placeholder=\"Ingrese la CLABE\" data-validation=\"server\" data-validation-url=\"".(route('banks.validateClabe'))."\" "]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de moneda: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value" => "MXN", "description" => "MXN"]]);
					$options = $options->concat([["value" => "EUR", "description" => "EUR"]]);
					$options = $options->concat([["value" => "USD", "description" => "USD"]]);
					$options = $options->concat([["value" => "Otro", "description" => "Otro"]]);
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "currency removeselect", "attributeEx" => "name=\"currency\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Convenio (opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "agreement", "attributeEx" => "type=\"text\" name=\"agreement\" placeholder=\"Ingrese el convenio\""]) @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["attributeEx" => "type=\"submit\" name=\"enviar\" id=\"enviar\"", "label" => "REGISTRAR"]) @endcomponent
			@component("components.buttons.button", ["variant" => "reset", "classEx" => "btn-delete-form", "attributeEx" => "type=\"reset\" name=\"borrar\"", "label" => "Borrar campos"]) @endcomponent
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
			@php
				$selects = collect(
					[
						[
							"identificator"          => ".js-enterprises", 
							"placeholder"            => "Seleccione la empresa", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => "[name=\"currency\"]", 
							"placeholder"            => "Seleccione el tipo de moneda", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".js-accounts", 
							"placeholder"            => "Seleccione la clasificación del gasto", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						],
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':3});
			generalSelect({'selector':'.js-bank', 'model':27});
			$('.account,.clabe').numeric({ negative : false, decimal : false });
			$(document)
			.on('change','[name="bank_id"]',function(e)
			{
				$("#account").attr("data-validation-req-params", JSON.stringify({'bank_id':$('option:selected', this).val()}));
			})
			.on('change focusout','#account',function(e)
			{
				bank = $('[name="bank_id"] option:selected').val();
				$(this).attr("data-validation-req-params", JSON.stringify({'bank_id':bank}));
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
			})		
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal
				({
					title : "Limpiar formulario",
					text : "¿Confirma que desea limpiar el formulario?",
					icon : "warning",
					buttons : ["Cancelar","OK"],
					dangerMode : true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				})
			})
			$.validate(
			{		
				form: '#container-alta',
				modules : 'security',
				onError : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');	
				},
				onSuccess : function($form)
				{				
					clabe = $('#clabe').val();
					account = $('#account').val();
					if(clabe == '' && account == '')
					{
						$('input[name="account"]').removeClass('valid').addClass('error');
						$('input[name="clabe"]').removeClass('valid').addClass('error');
						swal('', 'Por favor llene el campo CLABE o Número de cuenta ', 'error');
						return false;
					}
					else if(clabe != '' && account == ''){
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else if(clabe == '' && account != ''){
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});
		});
	</script>
@endsection
