@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
		@php
			$values	=
			[
				'minDate'	=>	isset($mindate) ? $mindate : '',
				'maxDate'	=>	isset($maxdate) ? $maxdate : '',
			];
			$hidden	=
			[
				'enterprise',
				'folios',
				'name'
			];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')
						Solicitante:
					@endcomponent
					@php
						foreach (App\User::whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc')->get() as $user)
						{
							if (isset($users_id) && $users_id == $user->id)
							{
								$optionUsers[]	=
								[
									"value"			=>	$user->id,
									"description"	=>	$user->fullName(),
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionUsers[]	=
								[
									"value"			=>	$user->id,
									"description"	=>	$user->fullName(),
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionUsers])
						@slot('attributeEx')
							name="users_id[]" multiple="multiple"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Empresa:
					@endcomponent
					@php
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
						{
							if (isset($enterprise_id) && $enterprise_id == $enterprise->id)
							{
								$optionEnterprise[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionEnterprise[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionEnterprise])
						@slot('attributeEx')
							name="enterprise_id[]" multiple="multiple"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Departamento:
					@endcomponent
					@php
						foreach (App\Department::orderName()->where('status','ACTIVE')->get() as $department)
						{
							if (isset($department_id) && $department_id == $department->id)
							{
								$optionDepartment[]	=
								[
									"value"			=>	$department->id,
									"description"	=>	$department->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionDepartment[]	=
								[
									"value"			=>	$department->id,
									"description"	=>	$department->name
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionDepartment])
						@slot('attributeEx')
							name="department_id[]" multiple="multiple"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Proyecto:
					@endcomponent
					@php
					foreach (App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
					{
						if (isset($project_id) && $project_id == $project->idproyect)
						{
							$optionProject[]	=
							[
								"value"			=>	$project->idproyect,
								"description"	=>	$project->proyectName,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionProject[]	=
							[
								"value"			=>	$project->idproyect,
								"description"	=>	$project->proyectName
							];
						}
					}
					@endphp
					@component('components.inputs.select', ["options" => $optionProject])
						@slot('attributeEx')
							name="project_id[]" multiple="multiple"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Periodicidad:
					@endcomponent
					@php
						if (isset($periodicity) && $periodicity == 1)
						{
							$optionPeriodicity[]	=	["value"	=>	1,"description"	=>	"Semanal",	"selected"	=>	"selected"];
							$optionPeriodicity[]	=	["value"	=>	2,	"description"	=>	"Mensual"];
						}
						else if (isset($periodicity) && $periodicity == 2)
						{
							$optionPeriodicity[]	=	["value"	=>	1,	"description"	=>	"Semanal"];
							$optionPeriodicity[]	=	["value"	=>	2,	"description"	=>	"Mensual", "selected"	=>	"selected"];
						}
						else
						{
							$optionPeriodicity[]	=	["value"	=>	1,	"description"	=>	"Semanal"];
							$optionPeriodicity[]	=	["value"	=>	2,	"description"	=>	"Mensual"];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionPeriodicity])
						@slot('attributeEx')
							name="periodicity" multiple="multiple"
						@endslot
					@endcomponent
				</div>
			@endslot
		@endcomponent
	</div>
	@if(count($budgets) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"ID"],
					["value"	=>	"Empresa"],
					["value"	=>	"Departamento"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Periodicidad"],
					["value"	=>	"Elaborado por"],
					["value"	=>	"Fecha de elaboración"],
					["value"	=>	"Acción"]
				]
			];
			foreach($budgets as $budget)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$budget->id],
					],
					[
						"content"	=>	["label"	=>	$budget->enterprise->name],
					],
					[
						"content"	=>	["label"	=>	$budget->department->name],
					],
					[
						"content"	=>	["label"	=>	$budget->project->proyectName],
					],
					[
						"content"	=>	["label"	=>	$budget->periodicityData()],
					],
					[
						"content"	=>	["label"	=>	$budget->user->fullName()],
					],
					[
						"content"	=>	["label"	=>	$budget->created_at->format('d-m-Y')],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"href=\"".route('budget.administration.edit',$budget->id)."\"",
								"label"			=>	"<span class=\"icon-pencil\"></span>",
								"buttonElement"	=>	"a"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"attributeEx"	=>	"href=\"".route('budget.administration.download',$budget->id)."\"",
								"label"			=>	"<span class=\"icon-file-download\"></span>",
								"buttonElement"	=>	"a"
							]
						],
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		
		<div class="text-center">
			{{ $budgets->appends(['mindate' => $mindate,'maxdate' => $maxdate,'enterprise_id'=>$enterprise_id,'department_id'=>$department_id,'project_id'=>$project_id,'periodicity'=>$periodicity,'users_id'=>$users_id])->render() }}
		</div>
	@else
		@component('components.labels.not-found')@endcomponent
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
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
			});
			@php
				$selects = collect([
					[
						"identificator"				=> "[name=\"enterprise_id[]\"]",
						"placeholder"				=> "Seleccione la empresa",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"project_id[]\"]",
						"placeholder"				=> "Seleccione el proyecto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"department_id[]\"]",
						"placeholder"				=> "Seleccione el departamento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"periodicity\"]",
						"placeholder"				=> "Seleccione la periodicidad",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "[name=\"users_id[]\"]",
						"placeholder"				=> "Seleccione el solicitante",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		});
	</script>
@endsection
