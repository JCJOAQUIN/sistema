@extends('layouts.child_module')
@section('data')
    @component("components.forms.form", ["attributeEx" => "action=\"".route('project-stage.search')."\" method=\"GET\" id=\"container-alta\""])
        @component("components.labels.title-divisor") BUSCAR FASES DE PROYECTOS @endcomponent
        @component("components.containers.container-form")
            <div class="col-span-2">
                @component("components.labels.label") Fase: @endcomponent

                @component("components.inputs.input-text") 
                    @slot("attributeEx")
                        type = "text"
                        name = "name"
                        placeholder = "Ingrese la fase"
                        value = "{{isset($name) ? $name : ''}}"
                    @endslot
                @endcomponent
            </div>

            <div class="col-span-2">
                @component("components.labels.label") Descripción: @endcomponent

                @component("components.inputs.input-text")
                    @slot("attributeEx")
                        type = "text"
                        name = "description" 
                        placeholder = "Ingrese la descripción"
                        value = "{{isset($description) ? $description : ''}}"
                    @endslot
                @endcomponent
            </div>

            <div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
                @component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
                @component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
            </div>
        @endcomponent
    @endcomponent

    @if(isset($requests) && count($requests) > 0)
        @php
            $modelHead = 
            [
                [
                    ["value" => "ID"],
                    ["value" => "Fase"],
                    ["value" => "Descripción"],
                    ["value" => "Acción"]
                ]
            ];
            foreach($requests as $request)
            {
                $body = 
                [
                    [
                        "content"   =>
                        [
                            [
                                "label" => $request->id
                            ]
                        ]
                    ],
                    [
                        "content"   =>
                        [
                            [
                                "label" => htmlentities($request->name)
                            ]
                        ]
                    ],
                    [
                        "content"   =>
                        [
                            [
                                "label" => htmlentities($request->description)
                            ]
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "kind"          => "components.buttons.button",
                                "variant"       => "success", 
                                "buttonElement" => "a", 
                                "attributeEx"   => "href=\"".route('project-stages.edit',$request->id)."\" title=\"Editar fase de proyecto\"",
                                "label"         => "<span class=\"icon-pencil\"></span>"
                            ]       
                        ]
                    ]
                ];
                $modelBody[] = $body;
            }
        @endphp

        @component("components.tables.table",
        [
            "modelHead" => $modelHead,
            "modelBody" => $modelBody,
        ])
        @endcomponent

        {{$requests->appends($_GET)->links()}}
    @else
        @component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
    @endif
@endsection
