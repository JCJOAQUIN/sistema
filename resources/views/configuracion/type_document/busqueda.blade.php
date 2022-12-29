htmlentities(@extends('layouts.child_module'))

@section('data')
    @component("components.forms.form", ["attributeEx" => "action=\"".route('type.document.follow')."\" method=\"GET\" id=\"formsearch\""])
        @component("components.labels.title-divisor")
            BUSCAR TIPO DE DOCUMENTOS
        @endcomponent
        @component("components.containers.container-form")
            <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
                @component("components.labels.label") Siglas: @endcomponent

                @component("components.inputs.input-text")
                    @slot("attributeEx")
                        type="text"
                        name="nameDocument"
                        value="{{isset($documentName) ? $documentName : '' }}"
                        id="nameDocument"
                        placeholder="Ingrese las siglas"
                    @endslot
                @endcomponent
            </div>
            <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "classEx" => "btn-search", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
        @endcomponent
    @endcomponent

    @if(count($documents) > 0)
        @php
            $modelHead = 
            [
                [
                    ["value" => "ID"], 
                    ["value" => "Siglas"], 
                    ["value" => "Descripción"], 
                    ["value" => "Acción"]
                ]
            ];

            foreach($documents as $doc)
            {
                $body = 
                [
                    [
                        "content"   =>
                        [
                            [
                                "label" => $doc->id
                            ]
                        ]
                    ],
                    [
                        "content"   => 
                        [
                            [
                                "label" => htmlentities($doc->name)
                            ]
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "label" => htmlentities($doc->description)
                            ]
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "kind"          => "components.buttons.button",
                                "variant"       => "success", "buttonElement" => "a", 
                                "attributeEx"   => "href=\"".route('type.document.edit',$doc->id)."\" alt=\"Editar documento\" title=\"Editar documento\"",
                                "label"         => "<span class=\"icon-pencil\"></span>"
                            ]       
                        ]
                    ]    
                ];
                $modelBody[] = $body;
            }
        @endphp
        <div class="table-responsive">
            @component("components.tables.table",
            [
                "modelHead" => $modelHead,
                "modelBody" => $modelBody,
            ])
            @endcomponent
        </div>

        {{$documents->appends($_GET)->links()}}
    @else
        @component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
    @endif
@endsection