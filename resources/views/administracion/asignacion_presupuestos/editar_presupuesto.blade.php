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
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		Datos del presupuesto
	@endcomponent
	@if($request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 18)
		@component("components.labels.not-found", ["variant" => "alert"])
			@slot("slot")
				La solicitud se encuentra en estatus 
				<span class="font-bold text-white"> «{{$request->statusrequest->description}}» </span>
				y ya no puede ser modificada.
			@endslot
		@endcomponent
	@endif
	@component("components.forms.form",["methodEx" => "PUT","attributeEx" => "method=\"POST\" action=\"".route('budget.update',$folio)."\" id=\"container-alta\"","files" => true])
		<div class="flex flex-row justify-center mt-3 mb-6">
			@component('components.containers.container-approval')
				@slot('attributeExButton') name="status" value="1" id="aprobar" @if($request->budget->status == 1) checked @endif @if($request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 18) disabled @endif @endslot
				@slot('attributeExButtonTwo') name="status" value="0" id="rechazar" @if($request->budget->status == 0) checked @endif @if($request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 18) disabled @endif @endslot
			@endcomponent
		</div>
		<div id="commentContent">
			<div class="flex flex-col items-center">
				@component('components.labels.label') 
					@slot('classEx') text-center @endslot 
					Comentarios (opcional)
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						cols="90" rows="10" name="budgetComment"
						@if($request->status == 10 || $request->status == 11 || $request->status == 12 || $request->status == 18) disabled @endif 
					@endslot
					{{$request->budget->comment}}
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@if($request->status != 10 && $request->status != 11 && $request->status != 12 && $request->status != 18)
				@component("components.buttons.button",["variant" => "primary"])
					@slot('classEx')
						enviar mr-2 w-48 md:w-auto text-center
					@endslot
					@slot('attributeEx')
						type="submit" name="enviar" value="ENVIAR PRESUPUESTO"
					@endslot
					ACTUALIZAR PRESUPUESTO
				@endcomponent
			@endif
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
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$.validate();
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
