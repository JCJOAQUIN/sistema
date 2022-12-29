@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR @endcomponent
	@SearchForm(["variant" => "default"])
		@php
			$options = collect();
			if(isset($category))
			{
				$category = App\CatWarehouseType::find($category);
				$options = $options->concat(
				[
					[
						"value"			=> $category->id, 
						"description"	=> $category->description, 
						"selected"		=> "selected"
					]
				]);
			}
		@endphp
		<div class="col-span-2">
			@component("components.labels.label") Categoría: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-cat @endslot
				@slot("attributeEx") title="Categoría" name="cat" multiple="multiple" @endslot
			@endcomponent
		</div>
		@php
			$options = collect();
			$place = App\Place::find($place_id);
			if(isset($place))
			{
				$options = $options->concat([["value" => $place->id, "description" => $place->place, "selected" => "selected"]]);
			}
		@endphp
		<div class="col-span-2">
			@component("components.labels.label") Ubicación @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-places @endslot
				@slot("attributeEx") name="place_id" multiple="multiple" @endslot
			@endcomponent
		</div>
		@php
			$options = collect();
			foreach(App\Enterprise::orderName()->get() as $enterprise)
			{
				$options = $options->concat(
				[
					[
						"value"			=> $enterprise->id, 
						"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, 
						"selected"		=> ((isset($enterpriseid) && $enterprise->id == $enterpriseid) ? "selected" : "")
					]
				]);
			}
		@endphp
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@component("components.inputs.select", ["options" => $options])
				@slot("classEx") js-enterprises @endslot
				@slot("attributeEx") title="Empresa" name="idEnterprise"multiple="multiple" @endslot
			@endcomponent
		</div>			
		<div class="col-span-2">
			@component("components.labels.label") Cuenta: @endcomponent
			@component("components.inputs.select", ["options" => []])
				@slot("classEx") js-accounts removeselect @endslot
				@slot("attributeEx") name="account_id" multiple="multiple" @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Concepto: @endcomponent
			@component("components.inputs.input-text")
				@slot("classEx") input-all @endslot
				@slot("attributeEx") placeholder="Ingrese el concepto" @isset($concept) value={{htmlentities($concept)}} @endif name="concept" @endslot
			@endcomponent
		</div>
		@php
			$inputs = 
			[
				[
					"input_classEx"		=> "input-text-date datepicker",
					"input_attributeEx" => "autocomplete=\"off\" title=\"Desde\" type=\"text\" name=\"mindate\" step=\"1\" placeholder=\"Desde\" id=\"mindate\" @isset($mindate) value=\"".$mindate."\" @endisset"
				],
				[
					"input_classEx"		=> "input-text-date datepicker",
					"input_attributeEx" => "title=\"Hasta\" type=\"text\" name=\"maxdate\" step=\"1\" placeholder=\"Hasta\" id=\"maxdate\" autocomplete=\"off\"  @isset($maxdate) value=\"".$maxdate."\" @endisset"
				]
			];
		@endphp
		<div class="col-span-2">
			@component("components.labels.label") Rango de fechas: @endcomponent
			@component("components.inputs.range-input", ["inputs" => $inputs]) @endcomponent
		</div>
		@slot('export')
			@if(count($deliveries) > 0)
				<div class="float-right">		
					@component("components.buttons.button", ["variant" => "success"])
						@slot("attributeEx") formaction="{{route('warehouse.delivery.report.export')}}" type="submit" @endslot
						<span>Exportar a Excel</span><span class="icon-file-excel"></span>
					@endcomponent
				</div>
			@endif
		@endslot
	@endSearchForm
	@if(count($deliveries) > 0)
		@php
			$modelHead = 
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Título"],
					["value"	=>	"Fecha"],
					["value"	=>	"Total"],
					["value"	=>	"Acciones"]
				]
			];
			$body = [];
			$modelBody = [];
			foreach($deliveries as $delivery)
			{
				$body = 
				[
					[
						"content" =>
						[
							[
								"label"			=> $delivery->stat->requestModel->folio
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"			=> htmlentities($delivery->stat->title),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"			=> Carbon\Carbon::createFromFormat('Y-m-d', $delivery->stat->datetitle)->format('d-m-Y')
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"			=> $delivery->stat->total != "" ? "$ ".$delivery->stat->total : "$ 0.00" 
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"variant"		=> "red",
								"attributeEx"	=> "title=\"Descargar reporte en PDF\" href=\"".route('warehouse.delivery.report.download',['id' => $delivery->idStat])."\"",
								"label"			=> "PDF"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@else
		@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) @endcomponent
	@endif
	{{ $deliveries->appends($_GET)->links() }}
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			generalSelect({'selector':'.js-cat', 'model':56});
			generalSelect({'selector':'.js-places', 'model':38});
			@ScriptSelect(
			[
				"selects" => 
				[
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					]
				]
			]) @endScriptSelect	
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			warehouseType	= $('.js-cat option:selected').val();
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType':warehouseType});
			$(document).on('change','.js-enterprises,.js-cat',function()
			{
				enterprise = $('.js-enterprises option:selected').val()
				warehouseType = $('.js-cat option:selected').val()
				$('.js-accounts').empty();
				generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType':warehouseType});
			});
		});
	</script>
@endsection