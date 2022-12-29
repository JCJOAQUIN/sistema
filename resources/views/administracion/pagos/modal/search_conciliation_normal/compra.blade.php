@component('components.labels.title-divisor') DATOS DEL PEDIDO @endcomponent
@php
    $modelHead =
    [
        [
            ["value" => "Cantidad"],
            ["value" => "Unidad"],
            ["value" => "Descripción"],
            ["value" => "Precio Unitario"],
            ["value" => "IVA"],
            ["value" => "Retenciones"],
            ["value" => "Impuesto Adicional"],
            ["value" => "Importe"]
        ]
    ];

    $taxes = $retentions = 0;
    $modelBody = [];
    foreach($req->purchases->first()->detailPurchase as $detail)
    {
        $taxesConcept=0;
        foreach($detail->taxes as $tax)
        {
            $taxesConcept+=$tax->amount;
            $taxes += $tax->amount;
        
        }
        $retentionConcept=0;
        foreach($detail->retentions as $ret)
        {
            $retentionConcept+=$ret->amount;
            $retentions += $ret->amount;
        }

        $unit = $detail->unit != "" ? $detail->unit : "---";
        $body = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $detail->quantity,
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $unit,
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($detail->description),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->unitPrice,2),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->tax,2),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($retentionConcept,2),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($taxesConcept,2),
                    ]
                ]
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($detail->amount,2),
                    ]
                ]
            ]
        ];
        
        $modelBody [] = $body;
    }
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody])
    @slot('attributeEx')
        id="table"
    @endslot
    @slot('classEx')
        table
    @endslot
    @slot('attributeExBody')
        id="body"
    @endslot
@endcomponent

@php
    $modelTable = [];
    $modelTable = 
	[
		["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => "$ ".number_format($req->purchases->first()->subtotales,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($req->purchases->first()->tax,2), "classEx" => "my-2"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($retentions,2), "classEx" => "my-2"]]],
        ["label" => "Imp. Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($taxes,2), "classEx" => "my-2"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($req->purchases->first()->amount,2), "classEx" => "my-2"]]],
	];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
])
    @if($req->purchases->first()->notes != "")
        @slot('textNotes')
            {{ htmlentities($req->purchases->first()->notes) }}
        @endslot
    @endif
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mt-6"]) DOCUMENTOS @endcomponent
@if(count($req->purchases->first()->documents)>0)
   @php
        $modelHeadsDocs =
        [
            [
                "label" => "Documento"
            ],
            [
                "label" => "Fecha"
            ],
        ];
        $modelTableDocs = [];
        foreach($req->purchases->first()->documents as $doc)
        {
            $modelTableDocs[] = 
            [
                "classEx" => "tr",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"          => "components.buttons.button",
                            "buttonElement" => "a",
                            "attributeEx"   => "target=\"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\"",
                            "variant"       => "secondary",
							"label"         => "Archivo",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"  => "components.labels.label",
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y H:i:s'),
                        ],
                    ],
                ],						
            ];
        }
   @endphp
   @component("components.tables.alwaysVisibleTable",[
    "modelHead" => $modelHeadsDocs,
    "modelBody" => $modelTableDocs,
    "variant" => "default"
    ])   
   @endcomponent
@else
    @component("components.labels.not-found",["classEx"   => "my-6"]) @endcomponent
@endif

@component('components.labels.title-divisor') PAGO @endcomponent
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
                    "label" => $movement->accounts->account." - ".$movement->accounts->description." (".$movement->accounts->description.")",
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

<div class="my-6">
    <div class="text-center">
        @component("components.buttons.button",[
            "variant"		=> "success",
            "attributeEx" 	=> "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"",
            "label"			=> "« Ocultar",
            "classEx"		=> "exit",
        ])  
        @endcomponent
    </div>
</div>