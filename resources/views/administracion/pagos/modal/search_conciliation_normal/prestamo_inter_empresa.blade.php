<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>
@php
	$taxes 		= 0;
	$retentions = 0;

	$requestUser           = App\User::find($req->idRequest);
	$elaborateUser         = App\User::find($req->idElaborate);
	$requestAccountOrigin  = App\Account::find($req->loanEnterprise->first()->idAccAccOrigin);
	$requestAccountDestiny = App\Account::find($req->loanEnterprise->first()->idAccAccDestiny);
	$modelTable = 
	[
		["Folio:", $req->folio],
		["Título y fecha:", htmlentities($req->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $req->loanEnterprise->first()->datetitle)->format('d-m-Y')],
		["Fiscal:", $req->taxPayment == 1 ? "Si" : "No" ],
		["Solicitante:", $requestUser->fullname()],
		["Elaborado por:", $elaborateUser->fullname()],
		["Empresa Origen:", App\Enterprise::find($req->loanEnterprise->first()->idEnterpriseOrigin)->name],
		["Clasificación del Gasto Origen:", $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
		["Empresa Destino:", App\Enterprise::find($req->loanEnterprise->first()->idEnterpriseDestiny)->name],
		["Clasificación del Gasto Destino:", $requestAccountDestiny->account." - ".$requestAccountDestiny->description." (".$requestAccountDestiny->description.")"],
	];	
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"]) 
	@slot('title')
		Detalles de la Solicitud de {{ $req->requestkind->kind }}
	@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) CONDICIONES DE PAGO @endcomponent
@php
	$modelTable	=
	[
		"Tipo de moneda" 	=> $req->loanEnterprise->first()->currency,
		"Fecha de pago" 	=> Carbon\Carbon::createFromFormat('Y-m-d', $req->loanEnterprise->first()->paymentDate)->format('d-m-Y'),
		"Forma de pago"		=> $req->loanEnterprise->first()->paymentMethod->method,
		"Importe a pagar" 	=> "$ ".number_format($req->loanEnterprise->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
@slot('classEx')
	employee-details
@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) DOCUMENTOS @endcomponent
@if(count($req->loanEnterprise->first()->documentsLoan)>0)
	@php
		$modelHead =
        [
            [
                "label" => "Documento"
            ],
            [
                "label" => "Fecha"
            ],
        ];
        $modelBody = [];	
		foreach($req->loanEnterprise->first()->documentsLoan as $doc)
		{
			$modelBody[] = 
            [
                "classEx" => "tr",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"        	=> "components.buttons.button",
							"buttonElement" => "a",
                            "attributeEx"	=> "target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
                            "variant" => "secondary",
							"label" => "Archivo",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"  => "components.labels.label",
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y'),
                        ],
                    ],
                ],						
            ];
		}
	@endphp
	@component("components.tables.alwaysVisibleTable",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"variant" => "default"
		])
		@slot('classEx')
			table
		@endslot   
	@endcomponent
@else
	@component("components.labels.not-found",[
		"classEx"   => "my-6"
	])
	@endcomponent
@endif

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

<div class="mb-6">
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