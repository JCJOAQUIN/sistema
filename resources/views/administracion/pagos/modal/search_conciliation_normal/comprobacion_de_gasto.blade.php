@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS @endcomponent
@php
    $modelHead = 
    [
        [
            ["value" => "Concepto"],
            ["value" => "Clasificación de gasto"],
            ["value" => "Tipo de Documento/No. Factura"],
            ["value" => "Fiscal"],
            ["value" => "Subtotal"],
            ["value" => "IVA"],
            ["value" => "Impuesto Adicional"],
            ["value" => "Importe"],
            ["value" => "Documento(s)"]
        ]
    ];

    $taxes         = 0;
    $subtotalFinal = $ivaFinal = $totalFinal = 0;
    $modelBody     = [];
    foreach(App\ExpensesDetail::where('idExpenses',$req->expenses->first()->idExpenses)->get() as $expensesDetail)
    {
        $subtotalFinal	+= $expensesDetail->amount;
        $ivaFinal		+= $expensesDetail->tax;
        $totalFinal		+= $expensesDetail->sAmount;

        if($expensesDetail->taxPayment==1)
        {
            $fiscal = "si";
        }
        else
        {
            $fiscal = "no";
        }

        $taxes2 = 0;
        foreach($expensesDetail->taxes as $tax)
        {
            $taxes2 += $tax->amount;
        }

        $contentBodyDocs = [];
        if(App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get()->count() > 0)
        {
            foreach(App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get() as $doc)
            {
                $containerButton = "";
                $containerButton .= '<div class="w-full">';
                $containerButton .= view('components.buttons.button',[																
                    "buttonElement" => "a",
                    "attributeEx"   => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/expenses/'.$doc->path)."\"",
                    "variant"       => "dark-red",
                    "label"         => "PDF",
                ])->render();
                $containerButton .= '</div>';
                $contentBodyDocs [] =
                [
                    "label" => $containerButton,
                ];
            }
        }
        else
        {
            $contentBodyDocs[] = 
            [
                "kind"  => "components.labels.label",
                "label" => "---",
            ];
        }

        $body = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($expensesDetail->concept),
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $expensesDetail->account->account." - ".$expensesDetail->account->description." (".$expensesDetail->account->content.")",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $expensesDetail->document != '' ? $expensesDetail->document : '---',
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $fiscal,
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($expensesDetail->amount,2),
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_amount[]\" value=\"".$expensesDetail->amount."\"",
                        "classEx"     => "t-amount",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($expensesDetail->tax,2),
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($taxes2,2),
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($expensesDetail->sAmount,2),
                    ],
                    [
                        "kind"        => "components.inputs.input-text",
                        "attributeEx" => "type=\"hidden\" name=\"t_total[]\" value=\"4.64\"",
                        "classEx"     => "t-iva",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" => $contentBodyDocs,
            ],
        ];

        $modelBody[] = $body;
    }

    $modelTableDetailPurchase = [];
    if($totalFinal!=0)
    {
        $valueSubtotal   = "$ ".number_format($subtotalFinal,2);
        $valueIVA        = "$ ".number_format($ivaFinal,2);
        $valueTotalFinal = "$ ".number_format($totalFinal,2);
    }
    else 
    {
        $valueSubtotal   = "$ 0.00";
        $valueIVA        = "$ 0.00";
        $valueTotalFinal = "$ 0.00";
    }

    if(isset($req))
    {
        foreach($req->expenses->first()->expensesDetail as $detail)
        {
            foreach($detail->taxes as $tax)
            {
                $taxes += $tax->amount;
            }
        }
    }

    if(isset($req->expenses)) 
    {
        foreach($req->expenses as $expense) 
        {
            $valueExpenses = "$ ".number_format($expense->reintegro,2);
            $valueRefund   = "$ ".number_format($expense->reembolso,2);
        }
    }
    else 
    {
        $valueExpenses = "$ 0.00";
        $valueRefund   = "$ 0.00";
    }

    $modelTableDetailPurchase = 
    [
        ["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label", "label" => $valueSubtotal, "classEx" => "my-2"]]],
        ["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label", "label" => $valueIVA, "classEx" => "my-2"]]],
        ["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($taxes,2), "classEx" => "my-2"]]],
        ["label" => "Reintegro: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => $valueExpenses, "classEx" => "my-2"]]],
        ["label" => "Reembolso: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => $valueRefund, "classEx" => "my-2"]]],
        ["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label", "label" => $valueTotalFinal, "classEx" => "my-2"]]],
    ];

@endphp
@component("components.tables.table",[
    "modelHead" => $modelHead,
    "modelBody" => $modelBody,
    "themeBody" => "striped"
]);
    @slot('attributeEx')
        id="table"
    @endslot
    @slot('classEx')
        table-no-bordered
    @endslot
    @slot('attributeExBody')
        id="body"
    @endslot
    @slot('classExBody')
        request-validate
    @endslot
@endcomponent

@component("components.templates.outputs.form-details",[
    "modelTable"            => $modelTableDetailPurchase
])
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mt-8 mb-4"]) PAGO @endcomponent
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
            "attributeEx" => "target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"$doc->path\"",
            "variant"       => "dark-red",
            "label"         => "PDF",
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

@component('components.labels.title-divisor',["classExContainer" => "mt-6 mb-4"]) MOVIMIENTO @endcomponent
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