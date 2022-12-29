@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS @endcomponent
@php
	$modelHead = 
	[
		[
			["value" => "Concepto"],
			["value" => "Clasificación del gasto"],
			["value" => "Tipo de Documento/No. Factura"],
			["value" => "Fiscal"],
			["value" => "Subtotal"],
			["value" => "IVA"],
			["value" => "Impuesto Adicional"],
			["value" => "Retenciones"],
			["value" => "Importe"],
			["value" => "Documento(s)"]
		]
	];
	
	$subtotalFinal = $ivaFinal = $totalFinal = $docs = 0;

	$modelBody = [];

	foreach($req->refunds->first()->refundDetail as $key=>$refundDetail)
	{
		$subtotalFinal	+= $refundDetail->amount;
		$ivaFinal		+= $refundDetail->tax;
		$totalFinal		+= $refundDetail->sAmount;

		if($refundDetail->account)
		{
			$accountDesc 	= $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.')';
			$valueAccount 	= "value=\"".$refundDetail->idAccount."\"";
		}
		else 
		{
			$accountDesc  = '---';
			$valueAccount = '';
		}

		$refundDetail->document == '' ? $rDocument='---' : $rDocument=$refundDetail->document;

		$refundDetail->taxPayment == 1 ? $taxPayment ="si" : $taxPayment ="no";

		$refundDetail->tax > 0 ? $taxDetail = "si" : $taxDetail = "no";

		$taxes2 	= 0;
		$taxesTd 	= [];
		foreach($refundDetail->taxes as $tax)
		{
			$taxes2 += $tax->amount;
		}
		
		$taxesTd[] = 
		[
			"kind" => "components.labels.label",
			"label" => "$ ".number_format($taxes2,2),
		];

		$retentionConcept = 0;
		$retentionsTd     = [];
		foreach($refundDetail->retentions as $ret)
		{				
			$retentionConcept+=$ret->amount;
		}

		$retentionsTd[] =
		[
			"kind" => "components.labels.label",
			"label" => "$ ".number_format($retentionConcept,2),
		];

		$documentsPayments = [];
		if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->count() == 0)
		{
			$documentsPayments [] =
			[
				"label" => "---",
			];
		}
		else 
		{
			$nowrap = '';
			$documentsPayments = [];
			foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
			{
				$containerButton = "";
				$containerButton .= '<div class="w-full">';
				$containerButton .= view('components.buttons.button',[																
					"buttonElement" => "a",
					"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/refounds/'.$doc->path)."\"",
					"variant"       => "dark-red",
					"label"         => "PDF",
				])->render();
				$containerButton .= '</div>';
				$documentsPayments [] =
				[
					"label" => $containerButton,
				];
			}
		}

		$body = [
			"classEx" => "tr",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"classEx" => "concept",
						"label" => htmlentities($refundDetail->concept),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"classEx" => "accountDesc",
						"label"	=> $accountDesc
					],
				],
			],
			[
				"classEx" => "td",
				"content" => 
				[
					[
						"kinds" => "components.labels.label",
						"label" => $rDocument,
					]
				]
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"classEx" => "taxPayment",
						"label"	=> $taxPayment,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"classEx" => "taxPayment",
						"label"	=> "$ ".number_format($refundDetail->amount,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"classEx" => "tax",
						"label"	=> "$ ".number_format($refundDetail->tax,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" => $taxesTd,
			],
			[
				"classEx" => "td",
				"content" => $retentionsTd,
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->sAmount,2),
					],
				]
			],
			[
				"classEx" => "td",
				"content" => $documentsPayments,
			],
		];

		$modelBody[] = $body;
		$docs++;
	}
@endphp
@component("components.tables.table",[
	"modelHead" => $modelHead,
	"modelBody" => $modelBody,
	"themeBody" => "striped"
])
	@slot("attributeEx")
		id="table"
	@endslot
	@slot("classExBody")
		request-validate
	@endslot
	@slot("attributeExBody")
		id="body"
	@endslot
@endcomponent

@php
	$retentionConcept2=0;
	$taxes=0;
	$modelTable = [];
	
	if($totalFinal!=0)
	{
		$valueSubtotal = "value=\"".number_format($subtotalFinal,2)."\"";
		$labelSubtotal = "$ ".number_format($subtotalFinal,2);
		$valueIVA 	   = "value=\"".number_format($ivaFinal,2)."\"";
		$labelIVA 	   = "$ ".number_format($ivaFinal,2);
		$valueTotal    = "value=\"".number_format($totalFinal,2)."\"";
		$labelTotal    = "$ ".number_format($totalFinal,2);
	}
	else 
	{
		$valueSubtotal = "";
		$labelSubtotal = "$ 0.00";
		$valueIVA 	   = "";
		$labelIVA 	   = "$ 0.00";
		$valueTotal    = "";
		$labelTotal    = "$ 0.00";
	}
	if(isset($req))
	{
		foreach($req->refunds->first()->refundDetail as $detail)
		{
			foreach($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
			foreach($detail->retentions as $ret)
			{
				$retentionConcept2 = $retentionConcept2 + $ret->amount;
			}
		}
	}
	
	$modelTable = 
	[
		["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => $labelSubtotal, "classEx" => "my-2 label-subtotal"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelIVA, "classEx" => "my-2 label-IVA"]]],
		["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($taxes,2), "classEx" => "my-2 label-taxes"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($retentionConcept2,2), "classEx" => "my-2 label-retentions"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelTotal, "classEx" => "my-2 label-total"]]],
	];
@endphp
@component("components.templates.outputs.form-details", ["modelTable" => $modelTable]) @endcomponent

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
                    "kind" 	=> "components.labels.label",
                    "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->paymentDate)->format('d-m-Y'),
                ]
            ]
        ],
    ];
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent

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
                    "kind" 	=> "components.labels.label",
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