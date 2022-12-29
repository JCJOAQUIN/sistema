@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('client.update', $client->idClient)."\" id=\"container-alta\" method=\"post\"", "methodEx" => "PUT"])		
		@component("components.labels.title-divisor") DATOS DEL CLIENTE @endcomponent
		@component("components.labels.subtitle") Para editar el cliente es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Razón Social: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="reason" placeholder="Ingrese la razón social" data-validation="server" ata-validation="server" data-validation-length="max150" data-validation-url="{{ route('client.validation.reason') }}" data-validation-req-params="{{json_encode(array('oldReason'=>$client->businessName))}}" value="{{ $client->businessName }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Calle: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						input-text
					@endslot
					@slot("attributeEx")
						type="text" name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100" value="{{ $client->address }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Número: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						
					@endslot
					@slot("attributeEx")
						type="text" name="number" placeholder="Ingrese el número" data-validation="required length" data-validation-length="max45" value="{{ $client->number }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Colonia: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
					@endslot
					@slot("attributeEx")
						type="text" name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70" value="{{ $client->colony }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código Postal: @endcomponent
				@php
					$options = collect();				
					$options = $options->concat(
					[
						[
							"value" => $client->postalCode,
							"selected" => (isset($client->postalCode) ? "selected" : ""), 
							"description" => $client->postalCode,
						]
					]);
					$attributeEx = "name=\"cp\" data-validation=\"required\"";
					$classEx = "js-zip_code";
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
						type="text" name="city" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70" value="{{ $client->city }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado: @endcomponent
				@php
					$options = collect();
					foreach(App\State::orderName()->get() as $state)
					{
						if($state->idstate== $client->state_idstate)
						{
							$options = $options->concat([["value" => $state->idstate, "description" => $state->description, "selected" => "selected"]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options, "classEx" => "js-state remove"])
					@slot("attributeEx")
						name="state" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">				
				@component("components.labels.label") RFC: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="server" value="{{ $client->rfc }}" data-validation-url="{{ route('client.validation.rfc') }}" data-validation-req-params="{{json_encode(array('oldRfc'=>$client->rfc))}}"
					@endslot
				@endcomponent				
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Teléfono (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text phone"])
					@slot("attributeEx")
						id="input-small" type="text" name="phone" placeholder="Ingrese el teléfono" data-validation="phone" value="{{ $client->phone }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contacto: @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="contact" placeholder="Ingrese el nombre del contacto" data-validation="required" value="{{ $client->contact }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Correo Electrónico (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "remove input-text"])
					@slot("attributeEx")
						type="text" name="email" placeholder="Ingrese el correo electrónico" data-validation="email" value="{{ $client->email }}" data-validation-optional="true"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Otro (Opcional): @endcomponent
				@component("components.inputs.input-text", ["classEx" => "input-text"])
					@slot("attributeEx")
						type="text" name="other" placeholder="Ingrese otro dato" value="{{ $client->commentaries }}"
					@endslot
				@endcomponent
			</div>		
		@endcomponent
		<div class="flex flex-wrap justify-center w-full space-x-2 py-4">
			@component("components.buttons.button", ["classEx" => "enviar", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "ACTUALIZAR"]) @endcomponent
			@php
				if(isset($option_id)) 
				{
					$href= url(getUrlRedirect($option_id));
				}
				else
				{
					$href= url(getUrlRedirect($child_id));
				}
			@endphp
			@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>
    @endcomponent

@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			
			$.validate(
			{
				form		: '#container-alta',
				modules		: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess	: function($form)
				{
					return true;
				}
			});
			$('input[name="phone"]').numeric(false);
			generalSelect({'selector':'.js-state','model':31});
			generalSelect({'selector':'.js-zip_code','model':2});
		});
	</script>
@endsection
