@extends('layouts.child_module')
@section('title', $title)
@section('data')
	@if (Auth::user()->id==43 || Auth::user()->id==11 || Auth::user()->id==16) 
		<h4>Acciones: </h4>
		<div class="content-start items-center justify-center text-center w-full grid grid-cols-12 mb-4">
			@component('components.buttons.button-secondary')
				@slot('classEx')
					lg:col-span-3
					md:col-span-6
					col-span-12
				@endslot
				@slot('href'){{ url('/suggestions/view') }}@endslot
				Ver
			@endcomponent
		</div>
	@endif
	@component("components.forms.form", ["attributeEx" => "action=\"".route('suggestions.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
		@component("components.labels.title-divisor") NUEVA SUGERENCIA @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Asunto: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						id="subject"
						name="subject"
						placeholder="Ingrese el asunto"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Comentario/Sugerencia: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="suggestion"
						rows="4"
						placeholder="Ingrese el comentario o sugerencia"
						data-validation="required"
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="text-center">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="send"
				@endslot
				ENVIAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$('.content').each(function()
			{
				var $this = $(this);
				var t = $this.text();
				$this.html(t.replace('&lt','<').replace('&gt', '>'));
			});
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					return false;
				},
			});
		});
	</script>
@endsection
