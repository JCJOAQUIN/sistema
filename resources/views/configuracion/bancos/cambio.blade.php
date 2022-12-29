@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('banks.update',$accountBank->idbanksAccounts)."\" id=\"container-alta\" method=\"post\"", "methodEx" => "PUT"])		
		@component("components.labels.title-divisor") Detalles de cuentas bancarias @endcomponent
		@component("components.labels.subtitle") Para editar la cuenta es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						if($enterprise->id == $accountBank->idEnterprise)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
						}
					}
				@endphp
				@component("components.labels.label") Empresa: @endcomponent
				@component("components.inputs.select", ["options" => $options,"classEx" => "js-enterprises removeselect", "attributeEx" => "data-validation=\"required\" name=\"enterprise_id\""])
				@endcomponent
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					if(isset($accountBank) && $accountBank->idAccAcc)
					{
						$options = $options->concat(
						[
							[
								"value"			=>	$accountBank->idAccAcc,
								"selected"		=>	"selected", 
								"description"	=>	$accountBank->accounts->account." ".$accountBank->accounts->description." ".$accountBank->accounts->content
							]
						]);
					}
				@endphp
				@component("components.labels.label") Clasificación del Gasto: @endcomponent
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-accounts removeselect", "attributeEx" => "data-validation=\"required\" name=\"account_id\""])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Banco: @endcomponent
				@php
					$options = collect();
					if(isset($accountBank) && $accountBank->idBanks)
					{
						$options = $options->concat([["value" => $accountBank->bank->idBanks, "selected" => "selected", "description" => $accountBank->bank->description]]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-bank removeselect", "attributeEx" => "data-validation=\"required\" name=\"bank_id\""])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large alias"])
					@slot("attributeEx")
						type="text" name="alias" placeholder="Ingrese el alias" value="{{ $accountBank->alias }}" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de cuenta: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large account"])
					@slot("attributeEx")
						name="account" id="account" placeholder="Ingrese el número de cuenta" data-validation="server" data-validation-url="{{ route('banks.validateAccount') }}" data-validation-req-params="{{ json_encode(array('oldAccount'=>$accountBank->idbanksAccounts, 'bank_id' => $accountBank->idBanks)) }}" value="{{ $accountBank->account }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Sucursal (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large branch_office"])
					@slot("attributeEx")
						type="text" name="branch" placeholder="Ingrese la sucursal" value="{{ $accountBank->branch }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Referencia (opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large reference"])
					@slot("attributeEx")
						type="text" name="reference" placeholder="Ingrese la referencia" value="{{ $accountBank->reference }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") CLABE: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large clabe"])
					@slot("attributeEx")
						type="text" name="clabe" id="clabe" placeholder="Ingrese la CLABE" data-validation="server" data-validation-url="{{ route("banks.validateClabe") }}" data-validation-req-params="{{ json_encode(array('oldClabe' => $accountBank->idbanksAccounts)) }}" value="{{ $accountBank->clabe }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de moneda: @endcomponent
				@php
					$options = collect();
					$value = ["MXN", "EUR", "USD", "Otro"];
					foreach($value as $item)
					{		
						$options = $options->concat(
						[
							[
								"value"			=>	$item,
								"selected"		=>	(isset($accountBank) && $item==$accountBank->currency) ? "selected" : "", 
								"description"	=>	$item,
							]
						]);		
										
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "input-text-large currency"])
					@slot("attributeEx")
						name="currency" value="{{ $accountBank->currency }}" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Convenio  (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text-large agreement"])
					@slot("attributeEx")
						type="text" name="agreement" placeholder="Ingrese el convenio" value="{{ $accountBank->agreement }}"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["attributeEx" => "type=\"submit\" name=\"enviar\" id=\"enviar\"", "label" => "ACTUALIZAR"]) @endcomponent
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
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			@ScriptSelect(
			[
				"selects" =>
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
					[
						"identificator"          => ".js-bank", 
						"placeholder"            => "Seleccione un banco", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			]) @endScriptSelect
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':3});
			generalSelect({'selector': '.js-bank', 'model': 27});
			$('.account,.clabe').numeric({ negative : false, decimal : false });
			$(document)
			.on('change','[name="bank_id"]',function(e)
			{
				$("#account").attr("data-validation-req-params", JSON.stringify({'bank_id':$('option:selected', this).val()}));
			})
			.on('change focusout','#account',function(e)
			{
				account = $(this).val();
				bank = $('[name="bank_id"] option:selected').val();
				$(this).attr('data-validation-req-params', JSON.stringify({'oldAccount':{{$accountBank->account}}, 'oldBank':{{$accountBank->idBanks}}, 'account':account, 'bank_id':bank}));
			})
			.on('change','#clabe',function(e)
			{
				e.preventDefault();
				$('input[name="clabe"]').empty();
				$clabe = $(this).val();
				if ($clabe != "")
				{
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route("banks.validateClabe") }}',
						data	: {'clabe': $clabe},
						success	: function(data)
						{
							if(data == "true")
							{
								$('input[name="clabe"]').val('');
								swal('','Esta CLABE ya se encuentra registrada', 'error');
							}
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('input[name="clabe"]').val('');
						}
					})
				}
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
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
						$('.input-text-large').val("");
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
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else if(clabe == '' && account != ''){
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});
		});
	</script>
@endsection
