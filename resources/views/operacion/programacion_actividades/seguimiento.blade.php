@extends('layouts.child_module')
@section('data')
    @component("components.labels.title-divisor") BUSCAR ACTIVIDADES @endcomponent
    @SearchForm(["variant" => "default"])
        <div class="col-span-2">
            @component("components.labels.label") Proyecto: @endcomponent
            @php
                $options = collect();
                if(isset($project_id))
                {
                    $project = App\Project::find($project_id);
                    $options = $options->concat(
                    [
                        [
                            "value"         => $project->idproyect, 
                            "description"   => $project->proyectName, 
                            "selected"      => "selected"
                        ]
                    ]);
                }
            @endphp
            @component("components.inputs.select", ["options" => $options])
                @slot("classEx") js-project @endslot
                @slot("attributeEx") name="project_id" multiple="multiple" @endslot
            @endcomponent
        </div>
        <div class="col-span-2">
            @component("components.labels.label") Código WBS: @endcomponent
            @php
                $options = collect();
                if(isset($code_wbs))
                {
                    $code_wbs = App\CatCodeWBS::find($code_wbs);
                    $options = $options->concat(
                    [
                        [
                            "value"         => $code_wbs->id, 
                            "description"   => $code_wbs->code_wbs, 
                            "selected"      => "selected"
                        ]
                    ]);
                }
            @endphp
            @component("components.inputs.select", ["options" => $options])
                @slot("classEx") js-code_wbs @endslot
                @slot("attributeEx") name="code_wbs" multiple="multiple" @endslot
            @endcomponent
        </div>
        <div class="col-span-2">
            @component("components.labels.label") Descripción de la acitividad: @endcomponent
            @component("components.inputs.input-text")
                @slot("attributeEx") type="text" name="description" id="input-search" placeholder="Ingrese la descripción" value="{{ isset($description) ? $description : '' }}" @endslot
            @endcomponent
        </div>
        <div class="col-span-2">
            @component("components.labels.label") Folio permiso de trabajo: @endcomponent
            @component("components.inputs.input-text")
                @slot("attributeEx") type="text" name="folio" id="input-search" placeholder="Ingrese el número de folio" value="{{ isset($folio) ? $folio : '' }}" @endslot
            @endcomponent
        </div>
        <div class="col-span-2">
            @component("components.labels.label") Rango de fechas de inicio y finalización de actividades: @endcomponent
            @php
                $inputs = 
                [
                    [
                        "input_classEx" => "datepicker",
                        "input_attributeEx"		=> "type=\"text\" name=\"start_date\" step=\"1\" placeholder=\"Desde\"  readonly value=\"".(isset($start_date) ? date('d-m-Y',strtotime($start_date)) : '')."\""
                    ],
                    [
                        "input_classEx"		=> "datepicker",
                        "input_attributeEx" => "type=\"text\" name=\"end_date\" step=\"1\" placeholder=\"Hasta\" readonly value=\"".(isset($end_date) ? date('d-m-Y',strtotime($end_date)) : '')."\""
                    ]
                ];
            @endphp
            @component("components.inputs.range-input", ["inputs" => $inputs])
                @slot("classEx") @endslot
                @slot("attributeEx") @endslot
            @endcomponent
        </div>
        <div class="col-span-2">
            @component("components.labels.label") Área: @endcomponent
            @component("components.inputs.input-text")
                @slot("attributeEx") type="text" name="area" id="input-search" placeholder="Ingrese un área" value="{{ isset($area) ? $area : '' }}" @endslot
            @endcomponent
        </div>
        @slot('export')
            <div class="float-right">
               @component("components.buttons.button", ["variant" => "success"])
                    @slot("classEx") export @endslot
                    @slot("attributeEx") type='submit'  formaction="{{ route('activitiesprogramation.export') }}" @endslot
                    <span>Exportar a Excel</span><span class='icon-file-excel'></span>
               @endcomponent
            </div>
        @endslot
    @endSearchForm
   @if(count($activities) > 0)
        @php
            $modelHead = 
            [
                [
                    ["value"	=>	"ID"],
                    ["value"	=>	"Descripción de las actividades"],
                    ["value"	=>	"Proyecto"],
                    ["value"	=>	"Código WBS"],
                    ["value"	=>	"Folio permiso de trabajo"],
                    ["value"	=>	"Fecha y Hora Inicio"],
                    ["value"	=>	"Fecha y Hora Finalización"],
                    ["value"	=>	"Área"],
                    ["value"	=>	"Editar"]
                ]
            ];
            $body = [];
            $modelBody = [];
            foreach($activities as $activity)
            {
                $body = 
                [
                    [
                        "content" =>
                        [
                            "label" => $activity->id
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => $activity->description
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => $activity->project->proyectName
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => (($activity->wbs_id == "") ? "Sín código" : $activity->codeWBS->code_wbs)
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => $activity->folio
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->start_date." ".$activity->start_hour)->format('d-m-Y H:i')
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->end_date." ".$activity->end_hour)->format('d-m-Y H:i')
                        ]
                    ],
                    [
                        "content" =>
                        [
                            "label" => $activity->area
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "kind"			=> "components.buttons.button",
                                "buttonElement"	=> "a",
                                "variant"       => "success",
                                "attributeEx"	=> "alt=\"Editar Actividad\" title=\"Editar Actividad\" href=\"".route('activitiesprogramation.follow.edit',$activity->id)."\"",
                                "label"         => "<span class=\"icon-pencil\"></span>"
                            ]
                        ]
                    ]
                ];
                $modelBody[] = $body;
            }
        @endphp
        @component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
            @slot("classEx")
            @endslot
            @slot("attributeEx")
            @endslot
        @endcomponent
        {{ $activities->appends($_GET)->links() }}
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
            $('input[name="folio"],input[name="number"]').numeric(true);
            $(function() 
			{
				$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
            generalSelect({'selector':'.js-project', 'model':41, 'option_id': 148});
            generalSelect({'selector':'.js-code_wbs', 'depends':'.js-project', 'model':1});
            $(document).on('change', '[name="project_id"]',function()
            {
                $('.js-code_wbs').val(null).trigger('change');
            }); 
        });
    </script>
@endsection