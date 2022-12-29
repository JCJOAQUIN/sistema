@extends('layouts.child_module')
@section('data')
	<div class="mx-auto w-full md:w-1/2 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center bg-gray-100 py-4 rounded rounded-lg">
		@component("components.labels.label", ["classEx" => "font-semibold"]) Tipo de Conciliación: @endcomponent
		@component('components.buttons.button-link', ["variant" => "red"])
			@slot('attributeEx')
				href="{{ route('payments.conciliation-normal.create') }}"
			@endslot
			Normal
		@endcomponent
		@component('components.buttons.button-link', ["variant" => "reset"])
			@slot('attributeEx')
				href="{{ route('payments.conciliation-nomina.create') }}"
			@endslot
			De nómina
		@endcomponent
	</div>
@endsection