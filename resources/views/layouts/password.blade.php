@extends('layouts.child_module')

@section('title', $title)

@section('data')
	@component('components.forms.form',['methodEx' => 'POST', 'attributeEx'=>'id="form-container"'])
		@component('components.containers.container-form')
			<div class="col-span-4 md:col-span-2 mb-4 space-y-2">
				@component("components.labels.label") Contraseña actual: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"password\" name=\"password_current\" data-validation=\"required\"", "classEx" => ($errors->has('password') ? ' is-invalid' : '')]) @endcomponent
			</div>
			<div class="col-span-4 md:col-span-2 mb-4 space-y-2">
				@component("components.labels.label") Nueva contraseña: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"password\" name=\"password\" data-validation=\"required\"", "classEx" => ($errors->has('password') ? ' is-invalid' : '')]) @endcomponent
			</div>
			<div class="col-span-4 md:col-span-2 mb-4 space-y-2">
				@component("components.labels.label") Confirmar contraseña: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"password\" name=\"password_confirmation\" data-validation=\"required\"", "classEx" => ($errors->has('password_confirmation') ? ' is-invalid' : '')]) @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component("components.buttons.button")
				@slot('attributeEx')
					type=”submit”
				@endslot
				Cambiar contraseña
			@endcomponent
			@component("components.buttons.button", ["variant" => "reset", "buttonElement" => "a"])
				@slot('classEx')
					load-actioner
					w-48
					md:w-auto
					text-center
				@endslot
				@slot('attributeEx')
					@isset($option_id)
						href="{{ url(App\Module::find($child_id)->url) }}"
					@else
						href="{{ url(App\Module::find($id)->url) }}"
					@endisset
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form     : '#form-container',
				onError: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		});
	</script>
@endsection