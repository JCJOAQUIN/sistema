@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCADOR DE EMPLEADOS @endcomponent
	@component('components.forms.searchForm',['variant' => 'default', 'attributeEx' => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Folio: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="folio" id="input-search" placeholder="Ingrese un folio" value="{{ isset($folio) ? $folio : '' }}"
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
		@if(count($pending)>0)
			@slot('export')
			@component('components.buttons.button', ["variant" => "success"])
				@slot('classEx')
					export
				@endslot
				@slot('attributeEx')
					type="submit" formaction="{{ route('administration.employee.pending-export') }}"
				@endslot
				<span>Exportar a Excel</span> <span class='icon-file-excel'></span>
			@endcomponent
			@endslot
		@endif
	@endcomponent
	@if(count($pending)>0)
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
					["value" => "Acción"],
				]
			];

			foreach($pending as $p)
			{
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
							"variant"		=> "success",
							"buttonElement"	=> "a",
							"attributeEx"	=> "type=\"button\" href=\"".route('administration.employee.edit-employee',['employee_id'=>$p->employee_id,'request_model'=>$p->folio])."\"",
							"label"			=> "<span class=\"icon-pencil\"></span>"
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
		{{  $pending->appends($_GET)->links() }}
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