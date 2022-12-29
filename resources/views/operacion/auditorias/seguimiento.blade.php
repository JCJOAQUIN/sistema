@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor")
		BUSCAR REGISTROS
	@endcomponent
	@php
		$values = ["folio" => $folio, "minDate" => $min_date, "maxDate" => $max_date];
		$hidden = ['enterprise', 'name'];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot('contentEx')
			<div class="col-span-2">
				@component("components.labels.label")
					Proyecto:
				@endcomponent
				@php
					$optionsProy =  collect();
					
					if(isset($project_id))
					{
						$project = App\Project::find($project_id)->first();
						$optionsProy = $optionsProy->concat([["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsProy])
					@slot('attributeEx')
						name="project_id[]" 
						multiple="multiple" 
						id="project_id"
					@endslot
					@slot('classEx')
						js-project
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					WBS:
				@endcomponent
				@php
					$optionsWBS =  collect();

					if(isset($project_id) && isset($wbs_id))
					{
						$long = count($wbs_id);
						for ($i=0; $i < $long ; $i++) { 
							$wbs = App\CatCodeWBS::find($wbs_id[$i]);
							$optionsWBS = $optionsWBS->concat([["value" => $wbs->id, "description" => $wbs->code_wbs, "selected" => "selected"]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsWBS])
					@slot('attributeEx')
						name="wbs_id[]" 
						id="wbs_id"
						multiple="multiple"
					@endslot
					@slot('classEx')
						js-wbs
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Contratista:
				@endcomponent
				@php
					$optionsContracts =  collect();

					if(isset($contractor_id))
					{
						$long = count($contractor_id);
						for ($i=0; $i < $long ; $i++) { 
							$contract = App\Contractor::find($contractor_id[$i]);
							$optionsContracts = $optionsContracts->concat([["value" => $contract->id, "description" => $contract->name, "selected" => "selected"]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsContracts])
					@slot('attributeEx')
						name="contractor_id[]" 
						multiple="multiple" 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo de Auditoría:
				@endcomponent
				@php
					$optionsTypeA = collect();

					if(isset($type_audit))
					{
						$long = count($type_audit);
						for ($i=0; $i < $long ; $i++) { 
							$optionsTypeA = $optionsTypeA->concat(
							[
								[
									"value" => 1,
									"description" => "Interna",
									"selected" => ($type_audit[$i] == 1 ? "selected" : "")
								],
								[
									"value" => 2,
									"description" => "Externa",
									"selected" => ($type_audit[$i] == 2 ? "selected" : "")
								]
							]);
						}
					}
					else {
						$optionsTypeA = $optionsTypeA->concat(
						[
							["value" => 1, "description" => "Interna"],
							["value" => 2, "description" => "Externa"]
						]);
					}
				@endphp
				@component('components.inputs.select',["options" =>	$optionsTypeA])
					@slot('attributeEx')
						name="type_audit[]" 
						multiple="multiple"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Auditor Líder:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="auditor" 
						value="{{ isset($auditor) ? $auditor : '' }}" 
						placeholder="Ingrese un nombre"
					@endslot
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@if(count($audits)>0)
		@php
			
			$modelHead =
			[
				[
					["value"	=> "Folio", "rowspan" => 2],
					["value"	=> "Fecha", "rowspan" => 2],
					["value"	=> "Proyecto", "rowspan" => 2],
					["value"	=> "Frente/WBS", "rowspan" => 2],
					["value"	=> "Contratista", "rowspan" => 2],
					["value"	=> "Auditor Líder", "rowspan" => 2],
					["value"	=> "Personas", "rowspan" => 2],
					["value"	=> "Estado", "rowspan" => 2],
					["value" 	=> "Factores de severidad", "colspan" => 3],
					["value"	=> "IAS", "rowspan" => 2],
					["value"	=> "Tipo de Auditoría", "rowspan" => 2],
					["value"	=> "Acción", "rowspan" => 2]
				],
				[
					["value"	=> "1/3"],
					["value"	=> "1"],
					["value"	=> "3"]
				]
			];
			$modelBody = [];
			foreach($audits as $audit)
			{
				$body =
				[
					"classEx" => "tr",
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->id
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d', $audit->date)->format('d-m-Y')
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->projectData->proyectName
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => ($audit->wbsData()->exists() ? $audit->wbsData->code_wbs : '' )
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => htmlentities($audit->contractorData->name),
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => htmlentities($audit->auditor),
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->people_involved
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->statusAverage()
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->countDangerousnessOneThird()
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->countDangerousnessOne()
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->countDangerousnessThree()
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							
							[
								"label" => (is_numeric( $audit->ias ) && floor( $audit->ias ) != $audit->ias ? $audit->ias : round($audit->ias, 0))
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"label" => $audit->typeAudit()
							]
						]
					],
					[
						"classEx" => "td", 
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Editar\" title=\"Editar\" href=\"".route('audits.edit',$audit->id)."\"",
								"variant"		=> "primary",	
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							$audit->project_id == 124 || $audit->project_id == 126 ?
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"classEx"		=> "follow-btn",
								"attributeEx"   => ($audit->project_id == 124 ? "alt=\"Formato de Tula\" title=\"Formato de Tula\" href=\"".route('audits.export.tula',$audit->id)."\"" : ($audit->project_id == 126 ? "alt=\"Formato de Tula\" title=\"Formato de Tula\" href=\"".route('audits.export.tula',$audit->id)."\"" : '')),
								"variant" 		=> "dark-red",
								"label"			=> "<span class=\"icon-pdf\"></span>"
							] 
							:
							[
								"label" => ""
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Formato de PIM\" title=\"Formato de PIM\" href=\"".route('audits.export.pim',$audit->id)."\"",
								"variant" 		=> "dark-red",
								"label"			=> "<span class=\"icon-pdf\"></span>"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
		{{$audits->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found")@endcomponent
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
			$('.datepicker').datepicker({ dateFormat: "yy-mm-dd" });

			@php
				$selects = collect([
					[
						"identificator"          => '[name="type_audit[]"]', 
						"placeholder"            => "Seleccione el tipo de auditoría", 
						"maxSelection" 			 => "2"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent

			generalSelect({'selector': '#project_id', 'model': 14});
			generalSelect({'selector': '#wbs_id','depends':'#project_id', 'model':1, 'maxSelection': -1});
			generalSelect({'selector': '[name="contractor_id[]"]', 'model': 50, 'maxSelection': -1});
		});
	</script>
@endsection