@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') Buscador de Vehículos @endcomponent
	@component('components.forms.searchForm', ["variant" => "default","attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Marca: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="brand" placeholder="Ingrese el nombre de la marca" @isset($brand) value="{{ $brand }}" @endisset
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Modelo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="model" placeholder="Ingrese el modelo" @isset($model) value="{{ $model }}" @endisset
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Estado: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vehicle_status" placeholder="Ingrese el estado" @isset($vehicle_status) value="{{ $vehicle_status }}" @endisset
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Número de serie: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="serial_number" placeholder="Ingrese el número de serie" @isset($serial_number) value="{{ $serial_number }}" @endisset
				@endslot
			@endcomponent
		</div>
		@if(count($vehicles)>0)
			@slot('export')
				@component('components.buttons.button', ["variant" => "success"])
					@slot('classEx')
						export
					@endslot
					@slot('attributeEx')
						type="submit" formaction="{{ route('vehicle.export') }}"
					@endslot
					<span>Exportar resultado</span> <span class='icon-file-excel'></span> 
				@endcomponent
			@endslot
		@endif
	@endcomponent
	@if(count($vehicles)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "ID"],
					["value" => "Modelo"],
					["value" => "Marca"],
					["value" => "Propietario"],
					["value" => "Estado"],
					["value" => "Acción"]
				]
			];
			foreach($vehicles as $v)
			{
				$body = [
					[
						"content"	=>
						[
							"label" => $v->id
						]
					],
					[
						"content"	=>
						[
							"label" => htmlentities($v->model)
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($v->brand).' '.htmlentities($v->sub_brand)
						]
					],
					[
						"content" =>
						[
							"label" => $v->dataOwnerExternal()->exists() ? $v->dataOwnerExternal->fullName() : "No hay datos"
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($v->vehicle_status)
						]
					],
				];
				array_push($body,[ "content" => 
					[ 
						"kind" 			=> "components.buttons.button",
						"variant" 		=> "success",
						"buttonElement"	=> "a",
						"attributeEx"	=> "title=\"Editar vehículo\" type=\"button\" href=\"".route('vehicle.edit',$v->id)."\"",
						"label"			=> "<span class=\"icon-pencil\"></span>"
					]
				]);
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table',[
			"modelBody" 		=> $modelBody,
			"modelHead" 		=> $modelHead,
			"attributeExBody"	=> "id=\"payments\""
		])
		@endcomponent
		{{ $vehicles->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript"></script>
@endsection