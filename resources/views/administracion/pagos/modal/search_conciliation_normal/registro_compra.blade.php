
@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DEL PEDIDO @endcomponent
@php
    $modelHead = 
    [
        [
            ["value" => "#"],
            ["value" => "Cantidad"],
            ["value" => "Unidad"],
            ["value" => "Descripción"],
            ["value" => "Precio Unitario"],
            ["value" => "IVA"],
            ["value" => "Impuesto Adicional"],
            ["value" => "Retenciones"],
            ["value" => "Importe"]
        ]
    ];

    $modelBody = [];
    $countConcept = 1;
    foreach($req->purchaseRecord->detailPurchase as $detail)
    {
		$taxesConcept = $detail->taxes()->sum('amount');
		$retentionConcept = $detail->retentions()->sum('amount');

        $modelBody [] = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $countConcept,
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $detail->quantity,
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $detail->unit,
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($detail->description),
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->unitPrice,2),
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->tax,2),
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($taxesConcept,2),
                    ],								
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($retentionConcept,2),
                    ],								
                ],
            ],
			[
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->total,2),
                    ],								
                ],
            ],
        ];
		$countConcept++;
    }
@endphp
@component("components.tables.table",[
    "modelHead" => $modelHead,
    "modelBody" => $modelBody,
    "themeBody" => "striped",
    "attributeEx" => "id=\"table\"",
    "attributeExBody" => "id=\"body\"",
]);
@endcomponent
@php
    $modelTable = [];
    $modelTable = 
    [
        ["label" => "Subtotal: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($req->purchaseRecord->subtotal,2), "classEx" => "my-2"]]],
		["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label", "label" =>	"$ ".number_format($req->purchaseRecord->amount_taxes,2), "classEx" => "my-2"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($req->purchaseRecord->amount_retention,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($req->purchaseRecord->tax,2), "classEx" => "my-2"]]],
        ["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label", "label" =>	"$ ".number_format($req->purchaseRecord->total,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
])
	@if($req->purchaseRecord->notes != "")
		@slot('textNotes')
			{{ htmlentities($req->purchaseRecord->notes) }}
		@endslot
	@endif
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) PAGO @endcomponent
    @php
        $modelHead =
        [
            [
                ["value" => "Cuenta"],
                ["value" => "Cantidad"],
                ["value" => "Documento"],
                ["value" => "Fecha"]
            ]
        ];

        $modelBody = [];
        $documentsPayments = [];
        foreach($payment->documentsPayments as $doc)
        {
            $containerButton = "";
            $containerButton .= '<div class="w-full">';
            $containerButton .= view('components.buttons.button',[                                                              
                "buttonElement" => "a",
                "attributeEx"   => "target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"$doc->path\"",
                "variant"       => "secondary",
                "label"         => "Archivo",
            ])->render();
            $containerButton .= '</div>';
            
            $documentsPayments [] =
            [
                "label" => $containerButton,
            ];
        }

        if (count($documentsPayments) == 0)
        {   
            $documentsPayments [] =
            [
                "label" => "---",
            ];
        }

        $modelBody [] = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $payment->accounts->account." - ".$payment->accounts->description." (".$payment->accounts->content.")",
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($payment->amount,2),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" => $documentsPayments
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->paymentDate)->format('d-m-Y'),
                    ]
                ]
            ],
        ];
    @endphp
    @component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent

    @component('components.labels.title-divisor') MOVIMIENTO @endcomponent
    @php
        $modelHead =
        [
            [
                ["value" => "Descripción"],
                ["value" => "Comentarios"],
                ["value" => "Clasificación del Gasto"],
                ["value" => "Fecha de Alta de Movimiento"],
                ["value" => "Fecha de Conciliación"],
                ["value" => "Importe"]
            ]
        ];

        $modelBody = [];
        $modelBody [] = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($movement->description),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($movement->commentaries),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $movement->accounts->account." - ".$movement->accounts->description." (".$movement->accounts->content.")",
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $movement->movementDate)->format('d-m-Y'),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $movement->conciliationDate)->format('d-m-Y H:i:s'),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($movement->amount,2),
                    ]
                ]
            ],
        ];
    @endphp
    @component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent