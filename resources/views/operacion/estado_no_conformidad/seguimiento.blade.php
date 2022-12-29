@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "BUSCAR ESTADO DE NO CONFORMIDADES"]) @endcomponent
		@php
			$values	=	['minDate'	=>	isset($mindate) ? $mindate : '','maxDate'	=>	isset($maxdate) ? $maxdate : '',];
			$hidden	=	['enterprise','name','folio'];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values, "hidden" => $hidden])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Proyecto"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($project_id) && $project_id !="")
						{
							$projects	=	App\Project::find($project_id);
							$options	=	$options->concat([["value"	=>	$projects->idproyect,	"description"	=>	$projects->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-project", "attributeEx" => "name=\"project_id\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Código WBS"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($project_id) && $project_id != '' && isset($code_wbs) && $code_wbs !='')
						{
							$wbsCode	=	App\CatCodeWBS::find($code_wbs);
							$options	=	$options->concat([["value"	=>	$wbsCode->id,	"description"	=>	$wbsCode->code_wbs,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-code_wbs", "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Descripción de la actividad"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"description\" id=\"input-search\" placeholder=\"Ingrese una descripción\" value=\"".(isset($description) ? $description : '')."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo de acción"]) @endcomponent
					@php
						$options	=	collect();
						$actionData	=	['1'=>'No conformidad','2'=>'Acción correctiva','3'=>'Oportunidad de mejora'];
						foreach ($actionData as $key => $action)
						{
							if (isset($type_of_action) && $type_of_action == $key)
							{
								$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$action,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$action]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-action removeselect", "attributeEx" => "id=\"type_of_action\" name=\"type_of_action\" multiple=\"multiple\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Estatus"]) @endcomponent
					@php
						$options	=	collect();
						$statusData	=	['1'=>'Activo','2'=>'En proceso','3'=>'Finalizado'];
						foreach ($statusData as $key => $statusOption)
						{
							if (isset($status) && $status == $key)
							{
								$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$statusOption,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$statusOption]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options,"classEx" => "js-status removeselect", "attributeEx" => "id=\"status\" name=\"status\" multiple=\"multiple\""]) @endcomponent
				</div>
			@endslot
			@slot('export')
				@if (count($status_no_conformity) > 0)
					@component('components.buttons.button',
					[
						"variant"		=>	"success",
						"attributeEx"	=>	"type=\"submit\" formaction=\"".route('status-nc.export')."\"",
						"classEx"		=>	"export",
						"label"			=>	"Exportar a Excel <span class='icon-file-excel'></span>"
					]) @endcomponent
					@component('components.buttons.button',
					[
						"variant"		=>	"dark-red",
						"attributeEx"	=>	"type=\"submit\" formaction=\"".route('status-nc.export-pdf')."\"",
						"classEx"		=>	"export",
						"label"			=>	"Exportar a PDF <span class='icon-pdf'></span>"
					]) @endcomponent
				@endif
			@endslot
		@endcomponent
	</div>
	@if (count($status_no_conformity) > 0)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Proyecto"],
					["value"	=>	"Código WBS"],
					["value"	=>	"Descripción"],
					["value"	=>	"Fecha"],
					["value"	=>	"Tipo de acción"],
					["value"	=>	"Estatus"],
					["value"	=>	"Fecha de cierre"],
					["value"	=>	"Observaciones"],
					["value"	=>	"Editar"]
				]
			];
			foreach($status_no_conformity as $ncstatus)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$ncstatus->projectData->proyectName !="" ? $ncstatus->projectData->proyectName : "---"]
					],
					[
						"content"	=>	["label"	=>	$ncstatus->wbs_id == "" ? "Sin código" : $ncstatus->wbsData->code_wbs]
					],
					[
						"show"		=>	"true",
						"content"	=>	["label"	=>	$ncstatus->description!="" ? (strlen($ncstatus->description) >= 35 ? substr(strip_tags(htmlentities($ncstatus->description)),0,150).'...' : htmlentities($ncstatus->description)) : "---"]
					],
					[
						"content"	=>	["label"	=>	$ncstatus->date != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$ncstatus->date)->format('d-m-Y') : '---']
					],
					[
						"content"	=>	["label"	=>	$ncstatus->typeAction()!="" ? $ncstatus->typeAction() : "---"]
					],
					[
						"content"	=>	["label"	=>	$ncstatus->statusData()!="" ? $ncstatus->statusData() : "---"]
					],
					[
						"content"	=>	["label"	=>	$ncstatus->close_date!="" ? $ncstatus->close_date : "---"]
					],
					[
						"content"	=>	["label"	=>	$ncstatus->observations!="" ? htmlentities($ncstatus->observations) : "---"]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"alt=\"Editar Estado de no conformidad\" title=\"Editar Estado de no conformidad\" href=\"".route('status-nc.edit',$ncstatus->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mt-4"]) @endcomponent
		{{ $status_no_conformity->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found', ["attributeEx" => "id=\"not-found\""]) @endcomponent
	@endif
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			generalSelect({'selector':'.js-project', 'option_id':{{$option_id}},'model':41});
			generalSelect({'selector':'.js-code_wbs', 'depends': '.js-project', 'model':22});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-action", 
						"placeholder"            => "Seleccione un tipo de acción", 
						"language"				 => "es"
					],
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione un estatus", 
						"language"				 => "es"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(document).on('change', '[name="project_id"]',function()
			
			{
				$('[name="code_wbs"]').html('');
			})
		});
	</script>
@endsection