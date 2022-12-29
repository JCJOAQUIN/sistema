@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
		@php
			$values = ['folio' => $folio, 'name' => $name, 'minDate' => $mindate, 'maxDate' => $maxdate];
		@endphp
		@component('components.forms.searchForm',
			[
				"attributeEx"	=> "id=\"formsearch\"",
				"values"		=> $values,
				"hidden"		=> ['enterprise']
			])
			@slot('contentEx')
				<div class="col-span-2">
					@component("components.labels.label") Título: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="text" 
							name="titleRequest"
							id="input-search" 
							placeholder="Ingrese el título" 
							value="{{ isset($titleRequest) ? $titleRequest : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$options = collect();
						foreach(App\CatTypePayroll::orderName()->get() as $t)
						{
							if(isset($type) && $type == $t->id)
							{
								$options = $options->concat([["value" => $t->id, "selected" => "selected", "description" => $t->description]]);
							}
							else
							{
								$options = $options->concat([["value" => $t->id, "description" => $t->description]]);
							}
						}
					@endphp
					@component("components.labels.label") Tipo de nómina: @endcomponent
					@component("components.inputs.select", [
						"options" 		=> $options, 
						"classEx" 		=> "js-type",
						"attributeEx"	=> "title=\"Tipo\" name=\"type\" multiple"
					]) 
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$options = collect();
						$condition = [4  => "Administrativa", 11 => "Obra"];
						foreach($condition as $key => $item)
						{
							if(isset($department) && $department== $key)
							{
								$options = $options->concat([["value" => $key, "selected" => "selected", "description" => $item]]);
							}
							else
							{
								$options = $options->concat([["value" => $key, "description" => $item]]);
							}
						}
					@endphp
					@component("components.labels.label") Departamento: @endcomponent
					@component("components.inputs.select", [
						"options" 		=> $options, 
						"classEx" 		=> "js-department",
						"attributeEx" 	=> "name=\"department\" multiple"
					])
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$options = collect();
						$condition = [1  => "Fiscal", 2 => "No Fiscal", "3" => "Nom35"];
						foreach($condition as $key => $item)
						{
							if(isset($fiscal) && in_array($key, $fiscal))
							{
								$options = $options->concat([["value" => $key, "selected" => "selected", "description" => $item]]);
							}
							else
							{
								$options = $options->concat([["value" => $key, "description" => $item]]);
							}
						}
					@endphp
					@component("components.labels.label") Categoría: @endcomponent
					@component("components.inputs.select", [
						"options" 		=> $options,
						"classEx" 		=> "js-fiscal", 
						"attributeEx" 	=> "name=\"fiscal[]\" multiple"
					])
					@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$options	= collect();
						$emp 		= App\RealEmployee::where('id',$idEmployee)->first();
						if(isset($idEmployee) && in_array($emp->id, $idEmployee))
						{
							$options = $options->concat([["value" => $emp->id, "selected" => "selected", "description" => $emp->fullName()]]);
						}
					@endphp
					@component("components.labels.label") Empleado: @endcomponent
					@component("components.inputs.select", [
						"options" 		=> $options,
						"classEx" 		=> "js-employee",
						"attributeEx" 	=> "name=\"idEmployee[]\" multiple"
					])
					@endcomponent
				</div>
			@endslot
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Tipo"],
					["value" => "Categoría"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Fecha de elaboración"],
					["value" => "Acciones"]
				]
			];
			foreach($requests as $request)
			{
				$departmentId = $request->idDepartment == 4 ? 'Administrativa' : 'Obra';
				$body = 
				[
					[
						"content" => 
						[
							"label" => $request->folio
						]
					],
					[
						"content" => 
						[
							"label" => $request->nominasReal->first()->title != null ? htmlentities($request->nominasReal->first()->title) : 'No hay'
						]
					],
					[
						"content" => 
						[
							"label" => $request->nominasReal->first()->typePayroll->description
						]
					],
					[
						"content" => 
						[
							"label" => $departmentId." - ".$request->nominasReal->first()->typeNomina()
						]
					],
					[
						"content" => 
						[
							"label" => $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name : 'No hay'
						]
					],
					[
						"content" => 
						[
							"label" => $request->elaborateUser()->exists() ? $request->elaborateUser->name.' '.$request->elaborateUser->last_name : 'No hay'
						]
					],
					[
						"content" => 
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i')
						]
					],
					[
						"content" => 
						[
							["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "alt=\"Editar nómina\" title=\"Editar nómina\" href=\"".route("nomina.nomina-create",$request->folio)."\"", "label" => "<span class=\"icon-pencil\"></span>"],
							["kind" => "components.buttons.button", "buttonElement" => "a", "attributeEx" => "alt=\"Obtener cálculo\" title=\"Obtener cálculo\" href=\"".route("nomina.nomina-precalculate",$request->folio)."\"", "label" => "<span class=\"icon-menu\"></span>"]	
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelBody" => $modelBody, "modelHead" => $modelHead])
			@slot("attributeEx")
				id="table"
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-department",
					"placeholder"				=> "Seleccione un departamento",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-fiscal",
					"placeholder"				=> "Seleccione la categoria",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione tipo de nómina",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',['selects' => $selects]) @endcomponent
		generalSelect({'selector':'.js-employee','model':20});
	});
</script>
@endsection
