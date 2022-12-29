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
<div class="p-4">
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PRESUPUESTO
	@endcomponent
	@php
		$modelTable =
		[
			"Estatus " => $request->budget->status == 1 ? "Autorizado" : "Rechazado",
			"Por " => $request->budget->budgetUser->name." ".$request->budget->budgetUser->last_name." ".$request->budget->budgetUser->scnd_last_name,
			"Comentarios " => $request->budget->comment == "" ? "Sin comentarios" : htmlentities($request->budget->comment),
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
</div>
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
		@component("components.buttons.button", ["variant" => "reset"])
			@slot('attributeEx')
				type="button" 
				@if(isset($option_id))
					href="{{ url(getUrlRedirect($option_id)) }}"
					href="{{ searchRedirect($option_id, "", url(App\Module::find($option_id)->url)) }}" 
				@else 
					href="{{ searchRedirect($child_id, "", url(App\Module::find($child_id)->url)) }}" 
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

@endsection
