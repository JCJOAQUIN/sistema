@extends('layouts.child_module')
@section('data')
@if(isset($bank_account))
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('bank.acount.update',$bank_account->id)."\" id=\"container-alta\"", "methodEx" => "PUT"])
@else
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('bank.acount.store')."\" id=\"container-alta\""])
@endif
		@component("components.labels.subtitle") Para {{ (isset($bank_account)) ? "editar la cuenta" : "agregar una cuenta nueva" }} es necesario colocar los siguientes campos: @endcomponent
		@component('components.labels.title-divisor')    DATOS DE CUENTA BANCARIA @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "form-control"])
					@slot("attributeEx")
						type="text" name="alias"  data-validation="required" placeholder="Ingrese el alias" @if(isset($bank_account)) value="{{ $bank_account->alias }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"type_currency\""]) Estado: @endcomponent
				@php
					$options = collect();
					$values = ["1" => "Activa", "2" => "Bloqueada", "3" => "Cerrada"];
					foreach($values as $key => $value)
					{
						if(isset($bank_account) && $bank_account->status == $key)
						{
							$options = $options->concat([["value" => $key, "description" => $value, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $key, "description" => $value]]);	
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "form-control removeselect"])
					@slot("attributeEx")
						name="status" id="status" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"enterprise_id\""]) Empresa: @endcomponent
				@php
					$options = collect();						
					foreach(App\Enterprise::orderName()->get() as $enterprise)
					{
						if(isset($bank_account) && $bank_account->id_enterprise == $enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);	
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "form-control js-enterprises removeselect"])
					@slot("attributeEx")
						name="enterprise_id" id="enterprise_id" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"account_id\""]) Clasificación de Gasto: @endcomponent
				@php
					$options = collect();	
					if(isset($bank_account))
					{					
						foreach(App\Account::where('idEnterprise',$bank_account->id_enterprise)->where(function($q){$q->where('account','LIKE','1102%')->orWhere('account','LIKE','1103%')->orWhere('account','LIKE','1104%');})->where('selectable',1)->get() AS $accAcc)
						{
							$description = $accAcc->account." - ".$accAcc->description." (".$accAcc->content.")";
							if($bank_account->id_accounting_account == $accAcc->idAccAcc)
							{
								$options = $options->concat([["value" => $accAcc->idAccAcc, "description" => $description, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $accAcc->idAccAcc, "description" => $description]]);	
							}
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "form-control js-accounts removeselect"])
					@slot("attributeEx")
						name="account_id" id="account_id" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"bank_id\""]) Banco: @endcomponent
				@php
					$options = collect();
					if(isset($bank_account) && isset($bank_account->id_bank))		
					{
						$bank=App\Banks::find($bank_account->id_bank);
						$options = $options->concat([["value" => $bank->idBanks, "description" => $bank->description, "selected" => "selected"]]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "form-control removeselect"])
					@slot("attributeEx")
						name="bank_id" id="bank_id" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"type_currency\""]) Moneda: @endcomponent
				@php
					$options = collect();					
					$values = ["MXN", "USD", "EUR", "Otro"];
					foreach($values as $value)
					{
						if(isset($bank_account) && $bank_account->currency == $value)
						{
							$options = $options->concat([["value" => $value, "description" => $value, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $value, "description" => $value]]);	
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "form-control removeselect"])
					@slot("attributeEx")
						name="type_currency" id="type_currency" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "clabe"]) **CLABE: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "form-control clabe"])
					@slot("attributeEx")
						type="text" name="clabe" id="clabe" data-validation="server" data-validation-url="{{ route('bank.acount.validate.clabe') }}" @if(isset($bank_account)) value="{{$bank_account->clabe}}" data-validation-req-params="{{json_encode(array('oldClabe'=>$bank_account->clabe))}}" @endif placeholder="Ingrese la CLABE"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "account"]) **Cuenta bancaria: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "form-control account"])
					@slot("attributeEx")
						type="text" name="account" id="account" data-validation="server" data-validation-url="{{ route('bank.acount.validate.account') }}" @if(isset($bank_account)) value="{{$bank_account->account}}" data-validation-req-params="{{json_encode(array('oldAccount'=>$bank_account->account))}}" @endif placeholder="Ingrese la cuenta bancaria"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "kind"]) Tipo: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "form-control"])
					@slot("attributeEx")
						rows="5" name="kind" id="kind" data-validation="required" placeholder="Ingrese el tipo"
					@endslot
					@if(isset($bank_account)){{$bank_account->kind}}@endif 
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label", ["attributeEx" => "for=\"description\""]) Descripción: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "form-control"])
					@slot("attributeEx")
						rows="5" name="description" id="description" data-validation="required" placeholder="Ingrese la descripción"
					@endslot
					@if(isset($bank_account)){{$bank_account->description}}@endif
				@endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component("components.labels.subtitle") **Debe ingresar al menos un campo @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button") 
				@slot("attributeEx") 
					type="submit" 
					name="enviar" 
				@endslot 
				@if(isset($bank_account))
					ACTUALIZAR
				@else
					REGISTRAR
				@endif
			@endcomponent
			@if(!isset($bank_account))
				@component("components.buttons.button", ["variant" => "reset", "buttonElement" => "a", "classEx" => "btn-delete-form"]) 
					@slot("attributeEx") 
						type="reset" 
						name="borrar" 
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{asset('js/jquery.mask.js')}}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{
			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError   : function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('#clabe').val() == '' && $('#account').val() == '')
					{
						$('[name="clabe"],[name="account"]').removeClass('valid').addClass('error');
						swal('', 'Agregue al menos uno de estos campos: CLABE/Cuenta', 'error');
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
						],
						[
							"identificator"          => "#type_currency", 
							"placeholder"            => "Seleccione el tipo de moneda", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => "#status", 
							"placeholder"            => "Seleccione el estado", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '#bank_id', 'model': 28});
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 33});
			$('.account,.clabe').numeric({ negative : false, decimal : false });
			$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
			$(document).on('change','[name="enterprise_id"]',function()
			{
				$('[name="account_id"]').empty();
				
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
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('focusout','#clabe,#account',function()
			{
				if(!($.isNumeric($(this).val())))
				{
					$(this).val('');
				}
			});
		});
	</script>
@endsection