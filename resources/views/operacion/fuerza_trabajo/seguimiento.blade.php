@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR REGISTROS @endcomponent
	@component('components.forms.searchForm',["variant" => "default", "attributeEx" => "id=\"formsearch\""])
		<div class="col-span-2">
			@component('components.labels.label') Proyecto: @endcomponent
			@php
				$optionProject = [];
				if(isset($project_id))
				{
					$projs = App\Project::whereIn('status',[1,2])->whereIn('idproyect',$project_id)->orderBy('proyectName','asc')->get();
					foreach ($projs as $proj)
					{
						$optionProject[] = ['value' => $proj->idproyect, 'description' => $proj->proyectName, 'selected' => 'selected'];
					}
				}
			@endphp
			@component('components.inputs.select',["options" => $optionProject])
				@slot('attributeEx')
					title="Proyecto" id="project_id" name="project_id[]" multiple="multiple"
				@endslot
				@slot('classEx')
					js-projects
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') WBS: @endcomponent
			@php
				$optionWBS = [];
				if(isset($project_id) && isset($wbs_id))
				{	 
					$codes = App\CatCodeWBS::whereIn('id',$wbs_id)->orderBy('code_wbs','asc')->get();
					foreach ($codes as $code)
					{
						$optionWBS[] = ['value' => $code->id, 'description' => $code->code_wbs, 'selected' => 'selected'];
					}
				}
			@endphp
			@component('components.inputs.select',["options" => $optionWBS])
				@slot('attributeEx')
					title="Código WBS" id="wbs_id" name="wbs_id[]" multiple="multiple"
				@endslot
				@slot('classEx')
					js-code_wbs
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Localización: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="location_wbs" id="input-search" placeholder="Ingrese la localización" value="{{ isset($location_wbs) ? $location_wbs : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Descripción: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="description" id="input-search" placeholder="Ingrese una descripción" value="{{ isset($description) ? $description : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha en que deben estar en obra: @endcomponent
			@php
				$min = isset($min_date) ? $min_date : '';
				$max = isset($max_date) ? $max_date : '';
				$inputs = [
					[
						"input_classEx"		=> "datepicker",
						"input_attributeEx" => "name=\"min_date\" step=\"1\" placeholder=\"Desde\" value=\"".$min."\"",
					],
					[
						"input_classEx"		=> "datepicker",
						"input_attributeEx" => "name=\"max_date\" step=\"1\" placeholder=\"Hasta\" value=\"".$max."\"",
					]
				];
			@endphp
			@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
		</div>
		@if(count($work_forces)>0)
			@slot('export')
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component('components.buttons.button',["variant" => "success"])
							@slot('attributeEx')
								type="submit"
								formaction="{{ route('work-force.export') }}"
							@endslot
							@slot('label')
								<span>Exportar a Excel</span>
								<span class="icon-file-excel"></span>
							@endslot
							@slot('classEx')
								export
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($work_forces)>0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Proyecto"],
					["value" => "WBS"],
					["value" => "Localización"],
					["value" => "Fecha"],
					["value" => "Descripción de Actividad"],
					["value" => "Contratista/Subcontratista"],
					["value" => "Fuerza de Trabajo"],
					["value" => "Total de Trabajadores"],
					["value" => "Horas Hombre por Día"],
					["value" => "Acción"]
				]
			];
			foreach($work_forces as $wf)
			{
				$body = [
					[
						"content"	=>
						[
							"label" => $wf->projectData->proyectName
						]
					],
					[
						"content" =>
						[
							"label" => $wf->wbsData()->exists() ? $wf->wbsData->code_wbs : '---'
						]
					],
					[
						"content" =>
						[
							"label" => strlen($wf->location) >= 100 ? substr(strip_tags($wf->location),0,100).'...' : $wf->location
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d',$wf->date)->format('d-m-Y')
						]
					],
					[
						"content" =>
						[
							"label" => strlen($wf->description) >= 100 ? substr(strip_tags($wf->description),0,100).'...' : $wf->description 
						]
					],
					[
						"content" =>
						[
							"label" => $wf->provider
						]
					],
					[
						"content" =>
						[
							"label" => strlen($wf->work_force) >=100 ? substr(strip_tags($wf->work_force),0,100).'...' : $wf->work_force
						]
					],
					[
						"content" =>
						[
							"label" => $wf->total_workers
						]
					],
					[
						"content" =>
						[
							"label" => $wf->man_hours_per_day 
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "success",
							"buttonElement"	=> "a",
							"attributeEx"	=> "type=\"button\" href=\"".route('work-force.edit',$wf->id)."\"",
							"label"			=> "<span class=\"icon-pencil\"></span>"	
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table',[
			"modelBody"			=> $modelBody,
			"modelHead"			=> $modelHead,
			"attributeExBody"	=> "id=\"body_work_force\""
		])
		@endcomponent
		{{ $work_forces->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			generalSelect({'selector': '.js-projects', 'model': 17, 'option_id':{{$option_id}} });
			generalSelect({'selector': '#wbs_id', 'depends': '.js-projects', 'model': 1});
			$('.datepicker').datepicker({ dateFormat: "dd-mm-yy" });

			$(document).on('change','#project_id',function()
			{
				$('#wbs_id').html('');
				project_id = $('#project_id option:selected').val();
				if (project_id != undefined) 
				{
					$.each(generalSelectProject, function(i,v)
					{
						if(project_id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.js-code_wbs').removeAttr('disabled');
							}
							else
							{
								$('.js-code_wbs').attr('disabled',true);
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs').removeAttr('disabled');
				}
			})
		});
	</script>
@endsection