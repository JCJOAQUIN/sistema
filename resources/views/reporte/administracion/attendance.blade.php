@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		svg
		{
			fill: currentColor;
			width: 1.4em;
		}
	</style>
@endsection

@section('data')
	@component("components.labels.title-divisor") BUSCAR ASISTENCIAS @endcomponent
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","variant" => "default"])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Rango de Fechas: @endcomponent
				@php
					$mindate	= isset($requestSearch->mindate) ? $requestSearch->mindate : '';
					$maxdate	= isset($requestSearch->maxdate) ? $requestSearch->maxdate : '';
					$inputs		= 
					[
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"mindate\" placeholder=\"Desde\" value=\"".$mindate."\" data-validation=\"required\"",
						],
						[
							"input_classEx"		=> "input-text-date datepicker",
							"input_attributeEx"	=> "name=\"maxdate\" placeholder=\"Hasta\" value=\"".$maxdate."\" data-validation=\"required\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empleado: @endcomponent
				@php
					$options = collect();
					if (isset($requestSearch->employee) && $requestSearch->employee != "")
					{
						foreach(App\RealEmployee::whereIn('id',$requestSearch->employee)->get() as $emp)
						{
							$description = $emp->fullName();
							if (isset($requestSearch->employee) && in_array($emp->id,$requestSearch->employee))
							{
								$options = $options->concat([["value"=>$emp->id, "selected"=>"selected", "description"=>$description]]);
							}
						}
					}
					$attributeEx	= "name=\"employee[]\" title=\"Empleado\" multiple=\"multiple\"";
					$classEx		= "js-employee";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $e)
					{
						$description = strlen($e->name) >= 35 ? substr(strip_tags($e->name),0,35)."..." : $e->name;
						if(isset($requestSearch->enterprise) && in_array($e->id, $requestSearch->enterprise))
						{
							$options = $options->concat([["value"=>$e->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$e->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"enterprise[]\" title=\"Empresa\" multiple=\"multiple\"";
					$classEx		= "js-enterprise";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $d)
					{
						$description = $d->name;
						if(isset($requestSearch->department) && in_array($d->id, $requestSearch->department))
						{
							$options = $options->concat([["value"=>$d->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$d->id, "description"=>$description]]);
						}
					}
					$attributeEx	= "name=\"department[]\" title=\"Departamento\" multiple=\"multiple\"";
					$classEx		= "js-department";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($requestSearch->project) && $requestSearch->project != "") 
					{
						foreach(App\Project::whereIn('idproyect',$requestSearch->project)->orderName()->get() as $p)
						{
							$options = $options->concat([["value"=>$p->idproyect, "selected"=>"selected", "description"=>$p->proyectName]]);
						}
					}
					$attributeEx	= "name=\"project[]\" title=\"Proyecto\" multiple=\"multiple\"";
					$classEx		= "js-project project";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Asistencia: @endcomponent
				@php
					$options = collect();
					
					if(isset($requestSearch->attendance) && $requestSearch->attendance == 1)
					{
						$options = $options->concat([["value"=> "1", "selected"=>"selected", "description" => "Con asistencias"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "1", "description"=> "Con asistencias"]]);
					}

					if(isset($requestSearch->attendance) && $requestSearch->attendance == 0)
					{
						$options = $options->concat([["value"=> "0", "selected"=>"selected", "description" => "Sin asistencias"]]);
					}
					else
					{
						$options = $options->concat([["value"=> "0", "description"=> "Sin asistencias"]]);
					}
					$attributeEx	= "name=\"attendance\" title=\"Asistencia\" multiple=\"multiple\"";
					$classEx		= "js-attendance";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
		@endslot
		@if (count($attendances) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.attendance.excel') }} @endslot
							@slot('label')
								<span>Exportar</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if(isset($attendances) && count($attendances) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "ID Empleado"],
					["value"	=> "Nombre"],
					["value"	=> "Empresa"],
					["value"	=> "Proyecto"],
					["value"	=> "Asistencias"]
				]
			];

			foreach ($attendances as $att)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $att->id
						]
					],
					[
						"content" =>
						[
							"label" => $att->name.' '.$att->last_name.' '.$att->scnd_last_name
						]
					],
					[
						"content" =>
						[
							"label" => $att->enterprise
						]
					],

					[
						"content" =>
						[
							"label" => $att->project
						]
					],
					[
						"content" =>
						[
							"label" =>  $att->num_attendances
						]
					]
				];

				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
		{{ $attendances->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	$.ajaxSetup(
	{
		headers:
		{
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"			=> ".js-department",
					"placeholder"			=> "Seleccione un departamento",
					"languaje"				=> "es",
				],
				[
					"identificator"			=> ".js-typepayroll",
					"placeholder"			=> "Seleccione un tipo",
					"languaje"				=> "es",

				],
				[
					"identificator"			=> ".js-attendance",
					"placeholder"			=> "Seleccione las asistencias",
					"languaje"				=> "es",
					"maximumSelectionLength" => "1"

				],
				[
					"identificator"			=> ".js-enterprise",
					"placeholder"			=> "Seleccione una empresa",
					"languaje"				=> "es",

				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		generalSelect({'selector':'[name="employee[]"]','model': 20, 'maxSelection': -1});
		generalSelect({'selector':'[name="project[]"]','model': 21, 'maxSelection': -1 });
	});
</script> 
@endsection


