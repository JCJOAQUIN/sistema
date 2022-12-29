@extends('layouts.child_module')
@section('data')
	<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
		A continuación podrá ver el movimiento dado de alta anteriormente:
	</div>

	@component('components.labels.title-divisor') DATOS DEL MOVIMIENTO @endcomponent
	<div class="pb-6">
		@component("components.tables.table-request-detail.container",["variant" => "simple"])
			@php
				$modelTable = 
				[
					"Empresa"=> $movement->enterprise->name,
					"Cuenta"=> $movement->accounts->account.' '.$movement->accounts->description,
					"Tipo de Movimiento"=> $movement->movementType,
					"Importe"=> "$".number_format($movement->amount,2),
					"Fecha del movimiento"=> Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$movement->movementDate)->format('d-m-Y'),
					"Descripción"=> htmlentities($movement->description),
					"Comentarios"=> isset($movement->commentaries) ? htmlentities($movement->commentaries) : 'Sin comentarios.',
				]
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
		@endcomponent
	</div>
	<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
		@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
			@slot("attributeEx")
				@if(isset($option_id)) 
					href="{{ url(App\Module::find($option_id)->url) }}" 
				@else 
					href="{{ url(App\Module::find($child_id)->url) }}" 
				@endif 
			@endslot
			@slot('classEx')
				load-actioner
			@endslot
			REGRESAR 
		@endcomponent
	</div>
@endsection