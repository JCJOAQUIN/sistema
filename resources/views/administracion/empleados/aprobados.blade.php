@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCADOR DE EMPLEADOS @endcomponent
	@component('components.forms.searchForm',["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Folio: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="folio" placeholder="Ingrese el folio" value="{{ isset($folio) ? $folio : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Nombre: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="name" placeholder="Ingrese el nombre" value="{{ isset($name) ? $name : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') CURP: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="curp" placeholder="Ingrese un CURP" value="{{ isset($curp) ? $curp : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Puesto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="position" placeholder="Ingrese una posición" value="{{ isset($position) ? $position : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Proyecto: @endcomponent
			@php
				$projects = [];
				if(isset($project_id))
				{
					$proj = App\Project::whereIn('status',[1,2])
						->where('idproyect',$project_id)
						->first();
					if(isset($proj) && $proj != '')
					{
						$projects[] = ["value" => $proj->idproyect, "description" => $proj->proyectName, "selected" => "selected"];
					}
				}
			@endphp
			@component('components.inputs.select',['options' => $projects])
				@slot('attributeEx')
					title="Proyecto" name="project_id[]" multiple="multiple"
				@endslot
				@slot('classEx')
					js-project
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@if(count($approved)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Folio"],
					["value" => "Nombre"],
					["value" => "CURP"],
					["value" => "Puesto"],
					["value" => "Proyecto"],
					["value" => "Fecha de admisión"],
					["value" => "Origen"],
					["value" => "Acción"]
				]
			];
			foreach($approved as $p)
			{
				$check_employee = App\RealEmployee::where('curp',$p->curp)->get();
				if (count($check_employee)>0) 
				{
					$employee = $check_employee->first()->id;
				}
				else
				{
					$employee = "";
				}
				$kind = "";
				if($p->kind == 4)
				{
					$kind = "Solicitud de personal";
				}
				elseif($p->kind == 19)
				{
					$kind = "Requisiciones";
				}
				$fullName = $p->last_name.' '.$p->scnd_last_name.' '.$p->name;
				$body = 
				[
					[
						"content" =>
						[
							"label" => $p->folio
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($fullName),
						]
					],
					[
						"content" =>
						[
							"label" => $p->curp
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($p->position),
						]
					],
					[
						"content" =>
						[
							"label" => $p->proyectName
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d', $p->admissionDate)->format('d-m-Y')
						]
					],
					[
						"content" =>
						[
							"label" => $kind
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement"	=> "a",
							"attributeEx"	=> "type=\"button\" href=\"".($employee != "" ? route('administration.employee.approved-view',$employee) : '#')."\"",
							"label"			=> "<span class=\"icon-search\"></span>"
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
		@endcomponent
		{{  $approved->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			generalSelect({'selector' : '.js-project', 'model' : 21, 'maxSelection' : 15});
		});
	</script>
@endsection