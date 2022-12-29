@extends('layouts.child_module')
  
@section('data')
		@Form(["attributeEx" => "action=\"".route('property.search')."\" method=\"get\""])
			@component("components.labels.title-divisor") BUSCAR INMUEBLES @endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label")
						Inmueble:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx") new-input-text @endslot
						@slot("attributeEx") type="text" name="property" placeholder="Ingrese el nombre" @if(isset($property)) value="{{ $property }}" @endif @endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Ubicación:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx") new-input-text @endslot
						@slot("attributeEx") type="text" name="location" placeholder="Ingrese la ubicación" @if(isset($location)) value="{{ $location }}" @endif @endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Tipo de Inmueble:
					@endcomponent
					@php
						$options = collect();
						$options = $options->concat(
						[
							[
								"value" => "Propio", "description" => "Propio", "selected" => ((isset($type_property) && $type_property == "Propio") ? 'selected' : '')
							],
							[
								"value" => "Renta", "description" => "Renta", "selected" => ((isset($type_property) && $type_property == "Renta") ? 'selected' : '')
							]
						]);
					@endphp
					
					@component("components.inputs.select", ["options" => $options])
						@slot("classEx") removeselect form-control @endslot
						@slot("attributeEx") name="type_property" multiple="multiple" @endslot
					@endcomponent
				</div>
				@php
					$options = collect();
					$options = $options->concat(
					[
						[
							"value" => "Comercial", "description" => "Comercial", "selected" => ((isset($use_property) && $use_property == "Comercial") ? 'selected' : '')
						],
						[
							"value" => "Habitacional", "description" => "Habitacional", "selected" => ((isset($use_property) && $use_property == "Habitacional") ? 'selected' : '')
						]
					]);
				@endphp
				<div class="col-span-2">
					@component("components.labels.label")
						Uso de Inmueble:
					@endcomponent
					@component("components.inputs.select", ["options" => $options])
						@slot("classEx") removeselect form-control @endslot
						@slot("attributeEx") name="use_property" multiple="multiple" @endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.buttons.button-search")
						BUSCAR
						@slot("attributeEx") type="submit" @endslot
					@endcomponent
				</div>
			@endcomponent
		@endForm
		@if(count($properties)>0)
		<div class="float-right">
			@component("components.buttons.button", ["variant" => "success"])
				@slot("classEx") export @endslot
				@slot("attributeEx") type='submit'  formaction="{{ route('property.export') }}" @endslot
				<span class="icon-file-excel"></span><span>Exportar resultado</span>
			@endcomponent
		</div>
			@php
				$modelHead = 
				[
					[
						["value"	=>	"ID"],
						["value"	=>	"Inmueble"],
						["value"	=>	"Ubicación"],
						["value"	=>	"Tipo de Inmueble"],
						["value"	=>	"Uso de Inmueble"],
						["value"	=>	"Acción"],
					]
				];
				$body = [];
				$modelBody = [];
				foreach($properties as $e)
				{
					$body = 
					[
						[
							"content" =>
							[
								"label" => $e->id 
							]
						],
						[
							"content" =>
							[
								"label" => htmlentities($e->property)
							]
						],
						[
							"content" =>
							[
								"label" => htmlentities($e->location)
							]
						],
						[
							"content" =>
							[
								"label" => $e->type_property 
							]
						],
						[
							"content" =>
							[
								"label" => $e->use_property
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "success",
									"buttonElement"	=> "a",
									"attributeEx"	=> "href=\"".route('property.edit',$e->id)."\"",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			@endphp
			
			@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot("classEx")
				@endslot
				@slot("attributeExBody")
					id="payments"
				@endslot
			@endcomponent
			
			{{ $properties->appends($_GET)->links() }}
		@else
			@component("components.labels.not-found", ["variant" => "error"]) @endcomponent
		@endif
@endsection

@section('scripts')
	
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator" => "[name=\"type_property\"]",
						"placeholder"	=> "Seleccione el tipo de inmueble",
					],
					[
						"identificator" => "[name=\"use_property\"]",
						"placeholder"	=> "Seleccione el uso de inmueble",
					]
				]
			])
			@endScriptSelect
		});
	</script>

	@endsection