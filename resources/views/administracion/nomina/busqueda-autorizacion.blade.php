@extends('layouts.child_module')
  
@section('data')
	@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
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
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						name="titleRequest"
						id="input-search"
						placeholder="Ingrese un título"
						value="{{ isset($titleRequest) ? $titleRequest : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de nómina: @endcomponent
				@php
					$optionNom = [];
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						if(isset($type) && $type == $t->id)
						{
							$optionNom[] = ["value" => $t->id, "description" => $t->description, "selected" => "selected"];
						}
						else
						{
							$optionNom[] = ["value" => $t->id, "description" => $t->description];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionNom])
					@slot('attributeEx')
						title="Tipo" 
						name="type" 
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-type
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@php
					$optionDepartament = [];
					if(isset($department) && $department== 4)
					{
						$optionDepartament[] = ["value" => "4", "description" => "Administrativa", "selected" => "selected"];
					}
					else
					{
						$optionDepartament[] = ["value" => "4", "description" => "Administrativa"];
					}
					if(isset($department) && $department== 11)
					{
						$optionDepartament[] = ["value" => "11", "description" => "Obra", "selected" => "selected"];
					}
					else 
					{
						$optionDepartament[] = ["value" => "11", "description" => "Obra"];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionDepartament])
					@slot('attributeEx')
						title="Departamento" 
						name="department"
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-department
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal: @endcomponent
				@php
					$optionFiscal = [];
					if(isset($fiscal) && in_array(1, $fiscal))
					{
						$optionFiscal[] = ["value" => "1", "description" => "Fiscal", "selected"=> "selected"];
					}
					else
					{
						$optionFiscal[] = ["value" => "1", "description" => "Fiscal"];
					}
					if(isset($fiscal) && in_array(2, $fiscal))
					{
						$optionFiscal[] = ["value" => "2", "description" => "No Fiscal", "selected"=> "selected"];
					}
					else
					{
						$optionFiscal[] = ["value" => "2", "description" => "No Fiscal"];
					}
					if(isset($fiscal) && in_array(3, $fiscal))
					{
						$optionFiscal[] = ["value" => "3", "description" => "Nom35", "selected"=> "selected"];
					}
					else
					{
						$optionFiscal[] = ["value" => "3", "description" => "Nom35"];
					}
				@endphp
				@component('components.inputs.select', [ "options" => $optionFiscal])
					@slot('attributeEx')
						title="Fiscal/No Fiscal/Nom35"
						name="fiscal[]"
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-fiscal
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empleado: @endcomponent
				@php
					$optionEmployee = [];
					$emp 			= App\RealEmployee::where('id', $idEmployee)->first();
					if(isset($idEmployee) && in_array($emp->id, $idEmployee))
					{
						$optionEmployee[] = ["value" => $emp->id, "description" => $emp->fullName(), "selected" => "selected"];	
					}
				@endphp
				@component('components.inputs.select', [ "options" => $optionEmployee])
					@slot('attributeEx')
						title="Empleado"
						name="idEmployee[]"
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-employee
					@endslot
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@if(count($requests) > 0)	
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Categoría"],
					["value" => "Tipo"],
					["value" => "Solicitante"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];

			foreach($requests as $request)
			{ 
				$departmentId = $request->idDepartment == 4 ? 'Administrativa' : 'Obra';
				$body = [
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
							"label" => $departmentId.' - '.$request->nominasReal->first()->typeNomina()
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
							"label" => $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name : 'No hay'
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
							"kind"          => "components.buttons.button", 
							"variant"		=> "success", 
							"label" 		=> "<span class=\"icon-pencil\"></span>",
							"buttonElement"	=> "a", 
							"attributeEx"	=> "alt=\"Revisar nómina\" title=\"Revisar nómina\" href=\"".route('nomina.nomina-authorization',$request->folio)."\""
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])
			@slot('attributeEx')
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
			$selects = collect ([
				[
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione el tipo de nómina",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-department",
					"placeholder"				=> "Seleccione el departamento",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-fiscal",
					"placeholder"				=> "Seleccione la categoria",
				]
			]);	
		@endphp	
		@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-employee','model':20});
	});
</script>
@endsection
