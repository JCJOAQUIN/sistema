@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
@php
    $taxes 	  = 0;
    $request  = App\RequestModel::find($req->folio);
    foreach($req->resource as $resource)
    {
        $modelTable	=
        [
            "Forma de pago"	=>	$resource->paymentMethod->method,
            "Referencia"	=>	$resource->reference == "" ? "---": htmlentities($resource->reference),
            "Tipo de moneda" => $resource->currency,
            "Importe"		=>	"$ ".number_format($resource->total,2),
        ];
    }

    foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$req->resource->first()->idUsers)->get() as $bank)
    {
        if($resource->idEmployee == $bank->idEmployee)
        {
            $modelTable['Banco']             = $bank->description;
            $modelTable['Número de tarjeta'] = $bank->cardNumber;
            $modelTable['CLABE']             = $bank->clabe   == "" ? "---": $bank->clabe;
            $modelTable['Número de cuenta']  = $bank->account == "" ? "---": $bank->account;
        }
    }
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
    @slot('classEx')
        employee-details
    @endslot
@endcomponent
@component('components.labels.title-divisor',["classExContainer" => "mt-8 mb-4"]) RELACIÓN DE DOCUMENTOS SOLICITADOS @endcomponent
@php
    $modelHead = ["Concepto", "Clasificación de gasto", "Importe"];
    $subtotalFinal = $ivaFinal = $totalFinal = 0;
    $modelBody = [];
    foreach($req->resource->first()->resourceDetail as $resourceDetail)
    {
        $totalFinal	+= $resourceDetail->amount;
        $body = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
                "show" => "true",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => htmlentities($resourceDetail->concept),
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "show" => "true",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => $resourceDetail->accounts->account." - ".$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")",
                    ],
                ],
            ],
            [
                "classEx" => "td",
                "content" =>
                [
                    [
                        "kind" => "components.labels.label",
                        "label" => "$ ".number_format($resourceDetail->amount,2),
                    ],
                ],
            ],
        ];
        $modelBody[] = $body;
    }

    $modelTableDetailPurchase = 
    [
        ["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($totalFinal,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.tables.alwaysVisibleTable",[
        "modelHead" => $modelHead,
        "modelBody" => $modelBody,
    ]);
@endcomponent
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTableDetailPurchase
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
@component("components.tables.table", [
    "modelHead" => $modelHead, 
    "modelBody" => $modelBody]) 
@endcomponent
@component('components.labels.title-divisor',["classExContainer" => "mt-8 mb-4"]) MOVIMIENTO @endcomponent
@php
    $modelHead =
    [
        [
            ["value" => "Descripción"],
            ["value" => "Comentarios"],
            ["value" => "Clasificación del Gasto"],
            ["value" => "Fecha de Alta de Movimiento"],
            ["value" => "Fecha de Conciliación"],
            ["value" => "Importe"],
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