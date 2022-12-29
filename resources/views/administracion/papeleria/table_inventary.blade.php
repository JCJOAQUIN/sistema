@if($selectedInventary != "")
    @php
        $modelHead = 
        [
            ["label" => "Categoría <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"categoria\""],
            ["label" => "Cantidad <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"cantidad\""],
            ["label" => "Concepto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
            ["label" => "Precio unitario <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
        ];
        $modelBody= [];
        foreach($selectedInventary as $selected)
        {
            $body = 
            [
                "classEx" => "tr selected",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $selected->wareHouse->description,
                        ],
                        [
                            "kind" => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" value=\"".$selected->warehouseType."\"",
                            "classEx" => "category",
                        ]
                    ]
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $selected->quantity,
                            "classEx" => "td_quantity",
                        ],
                        [
                            "kind" => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" value=\"".$selected->idwarehouse."\"",
                            "classEx" => "id",
                        ],
                        [
                            "kind" => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" value=\"".$selected->quantity."\"",
                            "classEx" => "quantity",
                        ]
                    ]
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => htmlentities($selected->cat_c->description),
                        ],
                        [
                            "kind" => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" value\"".htmlentities($selected->cat_c->description)."\"",
                            "classEx" => "material",
                        ]
                    ]
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $selected->amountUnit,
                        ]
                    ]
                ]
            ];
            $modelBody[] = $body;
        }
    @endphp
    @component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead,"modelBody" => $modelBody, "noHead" => true]) @endcomponent
@endif
@php
    $modelHead = 
    [
        ["label" => "Categoría <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"categoria\""],
        ["label" => "Cantidad <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"cantidad\""],
        ["label" => "Concepto <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
        ["label" => "Precio unitario <span class='icon-arrow-up'></span>", "classEx" => "arrow", "attributeEx" => "data-sort=\"concepto\""],
    ];
    $modelBody= [];
    foreach($inventaryAll as $inventary)
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
                        "label" => $inventary->wareHouse->description,
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$inventary->warehouseType."\"",
                        "classEx" => "category",
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $inventary->quantity,
                        "classEx" => "td_quantity",
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$inventary->idwarehouse."\"",
                        "classEx" => "id",
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".$inventary->quantity."\"",
                        "classEx" => "quantity",
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($inventary->cat_c->description),
                    ],
                    [
                        "kind" => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" value=\"".htmlentities($inventary->cat_c->description)."\"",
                        "classEx" => "material",
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $inventary->amountUnit,
                    ]
                ]
            ]
        ];
        $modelBody[] = $body;
    }
@endphp
@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead,"modelBody" => $modelBody, "noHead" => true, "variant" => "default"]) @endcomponent