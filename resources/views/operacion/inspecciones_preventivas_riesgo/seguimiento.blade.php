@extends('layouts.child_module')
@section('data')
    @component("components.labels.title-divisor") BUSCAR INSPECCIONES PREVENTIVAS DE RIESGO @endcomponent
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
				@component("components.labels.label") Código WBS: @endcomponent
				@php
					$options = collect();
					if(isset($project_id) && isset($code_wbs))
					{
						$wbsSelected = App\CatCodeWBS::find($code_wbs);
						$options = $options->concat([["value" => $code_wbs, "selected" => "selected", "description" => $wbsSelected->code_wbs]]);
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-code_wbs", "attributeEx" => "title=\"Código WBS\" name=\"code_wbs\" id=\"wbs_id\" multiple=\"multiple\"".(isset($project_id) ? ($projectSelected->codeWBS()->exists() ? "" : " disabled=\"disabled\"") : " disabled=\"disabled\""), "options" => $options])
				@endcomponent
			</div>
            <div class="col-span-2">
				@component("components.labels.label") Contratista: @endcomponent
				@php
					$options = collect();
					if(isset($contractor))
					{
						$contractorSelected = App\Contractor::find($contractor);
						$options = $options->concat([["value" => $contractor, "selected" => "selected", "description" => $contractorSelected->name]]);
					}
				@endphp
				@component("components.inputs.select", ["classEx" => "js-contractor", "attributeEx" => "title=\"Contratista\" name=\"contractor\" id=\"contractor_id\" multiple=\"multiple\"", "options" => $options])
				@endcomponent
			</div>
            <div class="col-span-2">
				@component("components.labels.label") Lugar/Área: @endcomponent
				@component("components.inputs.input-text")
                    @slot("attributeEx")
                        name="area"
                        id="input-search"
                        placeholder="Ingrese el lugar/área"
                        value="{{ isset($area) ? $area : '' }}"
                    @endslot
                @endcomponent
			</div>
            <div class="col-span-2">
				@component("components.labels.label") Rango de fechas: @endcomponent
				@php						
					$inputs = 
                    [
						[
							"input_attributeEx" => "name=\"start_date\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($start_date) ? $start_date : '')."\"",
						],
						[
							"input_attributeEx" => "name=\"end_date\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($end_date) ? $end_date : '')."\"",
						]
					];
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs]) @endcomponent
			</div>
            <div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
        @endcomponent
        @if (count($preventives) > 0)
			<div class="flex justify-end text-right">
				@component("components.buttons.button", ["variant" => "success"])
					@slot("attributeEx")
						type="submit"
						formaction="{{ route('preventive.export')}}"
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
    @if(count($preventives) > 0)
        @php
            $modelHead = 
            [
                [
                    ["value" => "ID"],
                    ["value" => "Proyecto"],
                    ["value" => "Código WBS"],
                    ["value" => "Contratista"],
                    ["value" => "Lugar/Área"],
                    ["value" => "Fecha"],
                    ["value" => "Supervisor SSPA"],
                    ["value" => "Responsable SSPA"],
                    ["value" => "Acción"]
                ]
            ];
            $modelBody = [];
            foreach ($preventives as $index => $preventive)
            {
                $modelBody [] = 
                [
                    [
                        "content" =>
                        [
                            "label" => $preventive->id,
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => $preventive->project->proyectName,
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => (($preventive->wbs_id == "") ? "Sin código" : $preventive->codeWBS->code_wbs),
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => (($preventive->contractor_id == "") ? "Sin contratista" : $preventive->contractorData->name),
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => htmlentities($preventive->area),
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d', $preventive->date)->format('d-m-Y'),
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => htmlentities($preventive->supervisor_name),
                        ],
                    ],
                    [
                        "content" =>
                        [
                            "label" => htmlentities($preventive->responsible_name),
                        ],
                    ],
                ];
                $buttons = 
                [
                    [
                        "kind"          => "components.buttons.button",
                        "buttonElement" => "a",
                        "classEx"       => "load-actioner",
                        "attributeEx"   => "alt=\"Editar inspección preventiva\" title=\"Editar inspección preventiva\" href=\"".route("preventive.edit", $preventive->id)."\"",
                        "variant"       => "success",
                        "label"         => "<span class='icon-pencil'></span>",
                    ],
                ];
                if($preventive->project_id == 124 || $preventive->project_id == 126)
				{				
                    $buttons[] =
                    [
                        "kind"          => "components.buttons.button",                        
                        "buttonElement" => "a",
                        "attributeEx"   => (($preventive->project_id == 124) ? ("title=\"Formato de Tula\" href=\"".(route('preventive.export.tula', $preventive->id))."\"") : ("title=\"Formato de Dos Bocas\" href=\"".(route('preventive.export.dos-bocas', $preventive->id))."\"")),
                        "variant"       => "dark-red",
                        "label"         => "PDF",
                    ];
				}
                $modelBody[$index][]["content"] = $buttons;
            }            
        @endphp
        @component("components.tables.table",[
            "modelHead" => $modelHead,
            "modelBody" => $modelBody,
            "themeBody" => "striped"
        ])
        @endcomponent	
        {{ $preventives->appends($_GET)->links() }}    
    @else
        @component("components.labels.not-found") @endcomponent
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
            generalSelect({'selector': '.js-projects', 'model': 41, 'option_id': {{$option_id}} });
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1 });
			generalSelect({'selector': '.js-contractor', 'model': 50 });
            $(document).on('change', '[name="project_id"]',function()
            {
                idproject = $('[name="project_id"] option:selected').val();
				$('[name="code_wbs"]').val(null).trigger('change').html('');
				$('[name="code_wbs"]').removeClass('error').parent().find('.form-error').remove();
				if (idproject != null && idproject != undefined && idproject != "")
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(idproject == v.id)
						{
							if(v.flagWBS != null)
							{
								$('[name="code_wbs"]').prop('disabled', false);
							}
							else
							{
								$('[name="code_wbs"]').prop('disabled', true);
							}					
						}
					});
				} 
				else
				{
					$('[name="code_wbs"]').prop('disabled', true);
				}
            })
        });
    </script>
@endsection