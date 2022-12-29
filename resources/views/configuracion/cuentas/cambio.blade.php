@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") EDITAR CUENTA @endcomponent
	@component("components.labels.subtitle") Para editar la cuenta es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('account.update', $account->idAccAcc)."\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->get() as $enterprise) 
					{
						if($account->idEnterprise == $enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
						}
					}
				@endphp
				@component("components.labels.label") Cuenta: @endcomponent
				@component("components.inputs.select", ["options" => $options, "attributeEx" => "data-validation=\"required\" id=\"multiple-enterprises\" name=\"enterprise_id\"", "classEx" => "js-enterprises"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número de cuenta: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "account"])
					@slot("attributeEx")
						type="text" 
						name="account" 
						placeholder="Ingrese el número de cuenta" 
						value="{{ $account->account }}" 
						data-validation="server" 
						data-validation-url="{{ route('account.validation') }}" 
						data-validation-req-params="{{ json_encode(array('oldNumber'=>$account->idAccAcc,'enterprise_id'=>$account->idEnterprise)) }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre de cuenta: @endcomponent
				@component("components.inputs.text-area", ["classEx" => "description"])
					@slot("attributeEx")
						name="description" 
						id="description" 
						placeholder="Ingrese el nombre de la cuenta" 
						rows="3" 
						data-validation="required"
					@endslot
					{{$account->description}}
				@endcomponent
			</div>
			<div class="col-span-2">
				<p>
					@component("components.labels.label") Tipo de gasto: @endcomponent
					@component("components.inputs.text-area", ["classEx" => "content"])
						@slot("attributeEx")
							name="content" 
							id="content" 
							placeholder="Ingrese el tipo de gasto" 
							rows="3" 
							data-validation="required"
						@endslot
						{{$account->content}}
					@endcomponent
				</p>
			</div>
			<div class="col-span-2">
				<p>
					@component("components.labels.label") Saldo: @endcomponent
					@component("components.inputs.input-text", ["classEx" => "balance"])
						@slot("attributeEx")
							type="text" 
							data-validation="required" 
							name="balance" 
							id="balance" 
							placeholder="$0.00" 
							value="{{ $account->balance }}"
						@endslot
					@endcomponent
				</p>
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Visible: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval", ["label" => "No"])
						@slot("attributeEx")
							type="radio" 
							name="selectable" 
							id="novisible" 
							value="0" 
							@if($account->selectable == 0) 
								checked	= "true" 
							@endif
						@endslot
					@endcomponent
					@component("components.buttons.button-approval", ["label" => "Sí"])
						@slot("attributeEx")
							type="radio" 
							name="selectable" 
							id="visible" 
							value= "1" 
							@if($account->selectable == 1) 
								checked	= "true"
							@endif
						@endslot
					@endcomponent
				</div>
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
			@component("components.buttons.button", ["classEx" => "enviar"])
				@slot("attributeEx")
					type="submit" 
					name="enviar" 
				@endslot 
				ACTUALIZAR
			@endcomponent
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
				Regresar
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
		$.validate(
		{
			modules: 'security',
			form   : '#container-alta',
			onError: function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			}
		});

		$('input[name="balance"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('.account').numeric({ negative : false, decimal : false });
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
		$(document).on('change','.js-enterprises',function()
		{
			enterpriseid = $('select[name="enterprise_id"] option:selected').val();
			if(enterpriseid===undefined)
			{
				enterpriseid=0;
			}
			account = $('input[name="account"]').attr('data-validation-req-params');
			oldAccount = JSON.parse(account);
			$('input[name="account"]').attr('data-validation-req-params','{"enterprise_id":'+enterpriseid+',"oldNumber":'+oldAccount.oldNumber+'}').val('');
		})
		.on('click','.enviar', function()
		{
			enterprise 	= $('.js-enterprises').val();
			account 	= $('.account').val();
			description = $('.description').val();
			content 	= $('.content').val();
			balance 	= $('.balance').val();
			selectable 	= $('[name="selectable"]').val();
			if (enterprise == "" || account == "" || description == "" || content == "" || balance == "" || selectable == null)
			{
				if (enterprise == "") 
				{
					$('.js-enterprises').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
				}
				if (account == "") 
				{
					$('.account').addClass('error');
				}
				if (balance == "")
				{
					$('.balance').addClass('error');
				} 
				if(description == "")
				{
					$('.description').addClass('error');
				}
				if(content == "")
				{
					$('.content').addClass('error');
				}
				swal('', 'Por favor llene los campos necesarios', 'error');
			}
			else if (account == 0 && balance == 0)
			{
				swal('','El número de cuenta y el saldo no pueden ser cero', 'error');
				$('.account').removeClass('valid').addClass('error');
				$('.balance').removeClass('valid').addClass('error');
				return false;
			}
			else if(account == 0)
			{
				swal('','El número de cuenta no puede ser cero', 'error');
				$('.account').removeClass('valid').addClass('error');
				return false;
			}
		})
		.on('focusout','.balance,.account',function()
		{
			if($(this).val() < -0 || !($.isNumeric($(this).val())))
			{
				$(this).val('');
			}
		});
	});
</script>
@endsection