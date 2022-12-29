@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('client.store')."\" id=\"container-alta\" method=\"POST\""])
		@component("components.labels.title-divisor") DATOS DEL CLIENTE @endcomponent
		@component("components.labels.subtitle") Para agregar un cliente nuevo es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Razón Social: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="reason" placeholder="Ingrese la razón social" data-validation="server" data-validation-url="{{ route('client.validation.reason') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Calle: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						inut-text
					@endslot
					@slot("attributeEx")
						type="text" name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="number" placeholder="Ingrese el número" data-validation="required length" data-validation-length="max45"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Colonia: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código Postal: @endcomponent
				@php
					$options = collect();
					$attributeEx = "name=\"cp\" data-validation=\"required\"";
					$classEx = "js-zip_code removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => $classEx, "attributeEx" => $attributeEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ciudad: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="city" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent
				@php
					$options = collect();
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-state removeselect"])
					@slot("attributeEx")
						id="state" name="state" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") RFC: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="server" data-validation-url="{{ route('client.validation.rfc') }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Teléfono (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text phone"])
					@slot("attributeEx")
						id="input-small" type="text" name="phone" placeholder="Ingrese el teléfono" data-validation="phone"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contacto: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="contact" placeholder="Ingrese el contacto" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Correo Electrónico (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="email" placeholder="Ingrese el correo electrónico" data-validation="email" data-validation-optional="true"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Otro (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text"])
					@slot("attributeEx")
						type="text" name="other" placeholder="Ingrese otro dato"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="text-center my-4">
			@component("components.buttons.button", ["classEx" => "enviar", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "REGISTRAR"]) @endcomponent
			@component("components.buttons.button", ["variant" => "reset", "classEx" => "btn-delete-form", "attributeEx" => "type=\"reset\" name=\"borrar\"", "label" => "Borrar campos"]) @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			generalSelect({'selector':'.js-state','model':31});
			generalSelect({'selector':'.js-zip_code','model':2});
			validation();
			$('input[name="phone"]').numeric(false);
		});
		function validation()
		{
			$.validate(
			{
				form   : '#container-alta',
				modules: 'security',
				onError: function($form)
				{
					reason = $('[name="reason"]').val().trim();
					
					if($('[name="rfc"]').val() != "" && $('[name="rfc"]').hasClass('error'))
					{
						swal('', 'Ingresa un RFC válido.', 'error');
					}
					else 
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					}
				},
				onSuccess	: function($form)
				{
					return true;
				}
			});
		}
	</script>
@endsection

