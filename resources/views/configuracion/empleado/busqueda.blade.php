@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR EMPLEADOS @endcomponent
	@component('components.forms.form',["attributeEx" => "method=\"GET\" id=\"formsearch\""])
		@csrf
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")Nombre:@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="name" value="{{ isset($name) ? $name : '' }}" placeholder="Ingrese el nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")CURP:@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="curp" value="{{ isset($curp) ? $curp : '' }}" placeholder="Ingrese el CURP"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")Empresa:@endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $e)
					{
						if(isset($enterprise) && $e->id == $enterprise)
						{
							$options = $options->concat([["value" => $e->id, "description" => strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35).'...' : $e->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $e->id, "description" => strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35).'...' : $e->name]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-enterprises", "options" => $options])
					@slot("attributeEx")
						name="enterprise"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")Proyecto:@endcomponent
				@php
					$options = collect();
					foreach(App\Project::orderName()->where('status',1)->get() as $p)
					{
						if(isset($project) && $p->idproyect == $project)
						{
							$options = $options->concat([["value" => $p->idproyect, "description" => $p->proyectName, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $p->idproyect, "description" => $p->proyectName]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-projects", "options" => $options])
					@slot("attributeEx")
						name="project"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")Estado:@endcomponent
				@php
					$options = collect();
					$statusName = 
					[
						"1" => "Activo",
						"2" => "Baja parcial",
						"3" => "Baja definitiva",
						"4" => "Suspensión",
						"5" => "Boletinado",
					];
					foreach($statusName as $key => $p)
					{
						if(isset($status) && $status==$key)
						{
							$options = $options->concat([["value" => $key, "description" => $p, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $key, "description" => $p]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "laboral-data", "options" => $options])
					@slot("attributeEx")
						name="status"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
		@if(count($employees) > 0)
			<div class="float-right">
				@component("components.buttons.button", ["classEx" => "export", "variant" => "success"]) 
					@slot("attributeEx")
						type='submit'  formaction="{{ route('employee.export') }}" formmethod="post"
					@endslot
					<span class='icon-file-excel'></span> Plantilla con empleados
				@endcomponent
			</div>
			<div class="float-right">
				@component("components.buttons.button", ["classEx" => "export", "variant" => "success"]) 
					@slot("attributeEx")
						type='submit'  formaction="{{ route('employee.export.catalogs') }}" formmethod="post"
					@endslot
					<span class='icon-file-excel'></span> Catálogos para plantilla
				@endcomponent
			</div>
			<div class="float-right">
				@component("components.buttons.button", ["classEx" => "export", "variant" => "success"]) 
					@slot("attributeEx")
						type='submit'  formaction="{{ route('employee.export.complete') }}" formmethod="post"
					@endslot
					<span class='icon-file-excel'></span> Reporte de empleados
				@endcomponent
			</div>
			<div class="float-right">
				@component("components.buttons.button", ["classEx" => "export", "variant" => "success"]) 
					@slot("attributeEx")
						type='submit'  formaction="{{ route('employee.export-movement') }}" formmethod="post"
					@endslot
					<span class='icon-file-excel'></span> Movimientos IMSS
				@endcomponent
			</div>
			@php
				$modelHead =
				[
					[
						["value" => "ID"],
						["value" => "Nombre"],
						["value" => "CURP"],
						["value" => "Empresa"],
						["value" => "Acción"]
					]
				];
				$modelBody = [];
				foreach($employees as $employee)
				{
					$body = 
					[
						[
							"content" =>
							[
								["label" => $employee->id],
							]
						],
						[
							"content" =>
							[
								["label" => htmlentities($employee->fullName())],
							]
						],
						[
							"content" =>
							[
								["label" => $employee->curp],
							]
						],
						[
							"content" =>
							[
								["label" => $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->enterprises()->exists() ? $employee->workerDataVisible->first()->enterprises->name : 'Sin empresa' : 'Sin empresa'],
							]
						],
						[
							"content" =>
							[
								[
									"kind"				=> "components.buttons.button",
									"variant" 			=> "success",
									"buttonElement"		=> "a",
									"attributeEx"		=> "href=\"".route('employee.edit',$employee->id)."\" alt=\"Editar\" title=\"Editar\"",
									"label"				=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind"				=> "components.buttons.button",
									"buttonElement"		=> "a",
									"attributeEx"		=> "href=\"".route('employee.historic',$employee->id)."\" alt=\"Historial\" title=\"Historial\"",
									"label"				=> "<span class=\"icon-history\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			@endphp
			@Table(["attributeEx" => "id=\"table\"", "modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
			
			{{ $employees->appends(['name'=> $name,'curp'=> $curp,'enterprise'=>$enterprise,'status'=>$status,'project'=>$project])->render() }}
		@else
			@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) Resultado no encontrado: @endcomponent
		@endif
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@php
				$selects = collect(
					[
						[	
							"identificator"				=> ".js-enterprises",
							"placeholder"				=> "Seleccione la empresa",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						],
						[	
							"identificator"				=> "[name=\"status\"]",
							"placeholder"				=> "Seleccione un estado",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
						]
					]
				);
			@endphp	
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-projects', 'model': 21});
		});
	</script>
@endsection
