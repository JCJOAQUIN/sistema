@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
@php
    $taxes 	  = 0;
    $request  = App\RequestModel::find($req->folio);
    foreach($req->loan as $loan)
    {
        $modelTable	=
        [
            "Forma de pago"	=>	isset($loan->paymentMethod->method) ? $loan->paymentMethod->method : '---',
            "Referencia"	=>	$loan->reference == "" ? "---": htmlentities($loan->reference),
            "Importe"		=>	"$ ".number_format($loan->amount,2),
        ];
    }
    foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
    {	
        if($loan->idEmployee == $bank->idEmployee)
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