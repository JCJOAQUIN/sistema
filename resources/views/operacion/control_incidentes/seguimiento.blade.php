@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR INCIDENTES @endcomponent
	@component("components.forms.form", ["attributeEx" => "id=\"formsearch\""])
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					$projectSelected = "";
					if(isset($project_id))
					{							
						$projectSelected = App\Project::find($project_id);
						$options = $options->concat([["value" => $project_id, "selected" => "selected", "description" => $projectSelected->proyectName]]);
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-projects", "attributeEx" => "title=\"Proyecto\" name=\"project_id\" id=\"project_id\" multiple=\"multiple\"", "options" => $options])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") WBS: @endcomponent
				@php
					$options = collect();
					if(isset($project_id) && isset($wbs_id))
					{
						$wbsSelected = App\CatCodeWBS::whereIn('id', $wbs_id)->orderBy('code_wbs','asc')->get();
						foreach ($wbsSelected as $wbs) 
						{
							$options = $options->concat([["value" => $wbs->id, "selected" => "selected", "description" => $wbs->code_wbs]]);	
						}
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-wbs", "attributeEx" => "title=\"Código WBS\" name=\"wbs_id[]\" id=\"wbs_id\" multiple=\"multiple\"".(isset($project_id) ? ($projectSelected->codeWBS()->exists() ? "" : " disabled=\"disabled\"") : " disabled=\"disabled\""), "options" => $options])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Localización: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="location_wbs"
						@if (isset($location_wbs)) value="{{ $location_wbs }}" @endif
						placeholder="Ingrese la localización"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción de incidente: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="description"
						@if (isset($description)) value="{{ $description }}" @endif
						placeholder="Ingrese la localización"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rango de fecha: @endcomponent
				@php						
					$inputs= [
						[
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($mindate) ? $mindate : '')."\"",
						],
						[
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($maxdate) ? $maxdate : '')."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Trabajador: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="employee"
						@if (isset($employee)) value="{{ $employee }}" @endif
						placeholder="Ingrese un nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nivel de impacto: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([
						["value" => "1", "selected" => ((isset($impact_level) && $impact_level == "1") ? "selected" : ""), "description" => "Bajo"],
						["value" => "2", "selected" => ((isset($impact_level) && $impact_level == "2") ? "selected" : ""), "description" => "Moderado"],
						["value" => "3", "selected" => ((isset($impact_level) && $impact_level == "3") ? "selected" : ""), "description" => "Grave"]
					]);
				@endphp
				@component("components.inputs.select", ["classEx" => "impact_level", "attributeEx" => "name=\"impact_level\" multiple=\"multiple\"", "options" => $options])
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
		@if (count($incidents) > 0)
			<div class="text-right">
				@component("components.buttons.button", ["variant" => "success"])
					@slot("attributeEx")
						type="submit"
						formaction="{{ route('incident-control.excel')}}"
					@endslot
					@slot("classEx")
						export
					@endslot
					@slot("slot")
						<span>Exportar a Excel</span>
						<span class="icon-file-excel"></span>
					@endslot
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if (count($incidents) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "Proyecto"],
					["value" => "WBS"],
					["value" => "Localización"],
					["value" => "Fecha"],
					["value" => "Trabajador"],
					["value" => "Descripción"],
					["value" => "Recomendación"],
					["value" => "Acción"]
				]
			];
			$modelBody = [];
			foreach ($incidents as $incident)
			{
				$modelBody [] = 
				[
					[
						"content" =>
						[
							"label" => (isset($incident->requestProject->proyectName) ? $incident->requestProject->proyectName : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->wbs_id) ? $incident->wbs->code_wbs : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->location) ? $incident->location : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->date_incident) ? Carbon\Carbon::createFromFormat('Y-m-d', $incident->date_incident)->format('d-m-Y') : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->employee) ? $incident->employee : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->description) ? (strlen($incident->description) >= 100 ? substr(strip_tags($incident->description),0,100).'...' : $incident->description) : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => (isset($incident->recommendation) ? (strlen($incident->recommendation) >= 100 ? substr(strip_tags($incident->recommendation),0,100).'...' : $incident->recommendation) : "No hay"),
						],
					],
					[
						"content" =>
						[
							[
								"kind" => "components.buttons.button",
								"buttonElement" => "a",
								"attributeEx" => "title=\"Editar Incidente\" href=\"".route("incident-control.edit", $incident->id)."\"",
								"variant" => "success",
								"label" => "<span class='icon-pencil'></span>",
							],
							[
								"kind" => "components.buttons.button", 
								"classEx" => "delete-incident",
								"attributeEx" => "title=\"Eliminar Incidente\" href=\"".route("incident-control.delete", $incident->id)."\" type=\"button\"",
								"variant" => "red",
								"label" => "<span class='icon-bin'></span>",
							],
						],
					],
				];
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
		@endcomponent	
		{{ $incidents->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".impact_level", 
						"placeholder"            => "Seleccione el nivel", 
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-projects', 'model': 17, 'option_id':{{$option_id}} });
			generalSelect({'selector': '.js-wbs', 'depends': '.js-projects', 'model': 22});

			$(document).on('change', '.js-projects',function()
			{
				idproject = $('.js-projects option:selected').val();  
				$('.js-wbs').html('');
				if (idproject != undefined && idproject != null)
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(idproject == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.js-wbs').prop('disabled', false);
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
							}
							else
							{
								$('.js-wbs').html('').prop('disabled', true);
							}					
						}
					});
				}
				else
				{
					$('.js-wbs').html('').prop('disabled', true);
				}
			})
			.on('click','.delete-incident',function(e)
			{
				e.preventDefault();
				href = $(this).attr('href');
				swal({
					title		: "Eliminar Incidente",
					text		: "¿Confirma que desea eliminar el registro del incidente?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((deleteIncident) =>
				{
					if(deleteIncident)
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						form = $('<form></form>').attr('action',href).attr('method','post').append('@csrf');
						$(document.body).append(form);
						form.submit();
					}
					else
					{
						swal.close();
					}
				});
			});	
		});
	</script>
@endsection