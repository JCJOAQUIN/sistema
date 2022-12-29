@php
    $modelHead = 
    [
        ["label" => "Categoría <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"categoria\""],
        ["label" => "Cantidad <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"cantidad\""],
        ["label" => "Concepto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
    ];
    $modelBody = [];
    foreach($articleAll as $article)
    {
        $body = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [   
                        "kind" => "components.labels.label",
                        "label" => $article->categoryData()->exists() ? $article->categoryData->description : '',
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$article->category."\"",
                        "classEx" => "category"
                    ]
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $article->quantity,
                        "classEx" => "td_quantity"
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$article->idStatDetail."\"",
                        "classEx" => "id"
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$article->quantity."\"",
                        "classEx" => "quantity"
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($article->product),
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".htmlentities($article->product)."\"",
                        "classEx" => "material"
                    ]
                ]
            ],
        ];
        $modelBody[] = $body;
    }
@endphp
@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead,"modelBody" => $modelBody, "noHead" => true, "variant" => "default"]) @endcomponent