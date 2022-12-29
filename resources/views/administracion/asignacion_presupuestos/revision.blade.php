@extends('layouts.child_module')

@switch($request->kind)
	@case(1)
		@include('administracion.asignacion_presupuestos.compra')
		@break

	@case(9)
		@include('administracion.asignacion_presupuestos.reembolso')
		@break
@endswitch
@section('pay-form')
	@component('components.labels.title-divisor')  DATOS DEL PRESUPUESTO @endcomponent
	@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route('budget.store')."\" id=\"container-alta\"","files" => true])
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type  = "hidden" 
				name  = "idfolio" 
				value = "{{ $request->folio }}"
			@endslot
		@endcomponent
		@component('components.containers.container-approval')
			@slot('attributeExButton') name="status" value="1" id="aprobar" @endslot
			@slot('attributeExButtonTwo') name="status" value="0" id="rechazar" @endslot
		@endcomponent
		<div id="commentContent" class="hidden">
			@component('components.labels.label') Comentarios (opcional) @endcomponent
			@component("components.inputs.text-area")
				@slot('attributeEx')
					cols = "90" 
					rows = "10" 
					name = "budgetComment"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx')
					enviar mr-2 w-48 md:w-auto
				@endslot
				@slot('attributeEx')
					type  = "submit" 
					name  = "enviar" 
					value = "ENVIAR PRESUPUESTO"
				@endslot
				ENVIAR PRESUPUESTO
			@endcomponent
			@component("components.buttons.button", ["variant" => "reset"])
				@slot('attributeEx')
					type="button"
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				@slot('classEx') 
					load-actioner w-48 md:w-auto text-center
				@endslot
				@slot('buttonElement')
					a
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$.validate(
		{});
		$(document).on('change','input[name="status"]',function()
		{
			$("#commentContent").slideDown("slow");
		})
		.on('click','.enviar',function (e)
		{
			if($('input[name="status"]:checked').length <1)
			{
				e.preventDefault();
				swal({
					title: "Error",
					text: "Debe seleccionar al menos un estado.",
					icon: "error",
				});
			}
		});
	</script>
@append
