<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>

@php
	$modelTable = 
	[
		["Folio:", $request->folio],
		["Título y fecha:", htmlentities($request->purchaseRecord->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->purchaseRecord->datetitle)->format('d-m-Y')],
		["Número de Orden:", $request->purchaseRecord->numberOrder != "" ? htmlentities($request->purchaseRecord->numberOrder) : '---'],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No" ],
		["Solicitante:", $request->requestUser->fullname()],
		["Elaborado por:", $request->elaborateUser->fullname()],
		["Empresa:", $request->requestEnterprise->name],
		["Dirección:", $request->requestDirection->name],
		["Departamento:", $request->requestDepartment->name],
		["Clasificación del gasto:", $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->description.")"],
		["Proyecto:", $request->requestProject->proyectName],
		["Proveedor:", $request->purchaseRecord->provider],
	];
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"]) 
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent

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
    foreach($request->purchaseRecord->detailPurchase as $detail)
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
        ["label" => "Subtotal: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	"$ ".number_format($request->purchaseRecord->subtotal,2), "classEx" => "my-2"]]],
		["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label", "label" =>	"$ ".number_format($request->purchaseRecord->amount_taxes,2), "classEx" => "my-2"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($request->purchaseRecord->amount_retention,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label", "label" => "$ ".number_format($request->purchaseRecord->tax,2), "classEx" => "my-2"]]],
        ["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label", "label" =>	"$ ".number_format($request->purchaseRecord->total,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
])
	@if($request->purchaseRecord->notes != "")
		@slot('textNotes')
			{{ $request->purchaseRecord->notes }}
		@endslot
	@endif
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) CONDICIONES DE PAGO @endcomponent
@php
	$modelTable	=
	[
		"Empresa" => $request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '',
		"Cuenta" => $request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '',
		"Referencia/Número de factura" => $request->purchaseRecord->reference == "" ? "---" : htmlentities($request->purchaseRecord->reference),
		"Tipo de moneda" => $request->purchaseRecord->typeCurrency,
		"Fecha de pago" => $request->PaymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->PaymentDate)->format('d-m-Y') : "---",
		"Forma de pago" => $request->purchaseRecord->paymentMethod,
		"Estado de factura" => $request->purchaseRecord->billStatus != "" ? $request->purchaseRecord->billStatus : "---",
		"Importe a pagar" => "$ ".number_format($request->purchaseRecord->total,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent

<div class="table-responsive @if(isset($request) && $request->purchaseRecord->paymentMethod != 'TDC Empresarial') hidden @endif " id="view-credit-cards">
	@php
		$modelHead = 
		[
			[
				["value" => "Responsable"],
				["value" => "Nombre en Tarjeta"],
				["value" => "Número de Tarjeta"],
				["value" => "Estatus"],
				["value" => "Principal/Adicional"],
			]
    	];

		$modelBody = [];
		if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		{
			$t = App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user = App\User::find($t->assignment);
			$status = $principal = '';
			switch ($t->status) 
			{
				case 1:
					$status = 'Vigente';
					break;
				case 2:
					$status = 'Bloqueada';
					break;
				case 3:
					$status = 'Cancelada';
					break;
				default:
					break;
			}

			switch ($t->principal_aditional) 
			{
				case 1:
					$principal = 'Principal';
					break;
				case 2:
					$principal = 'Adicional';
					break;
				default:
					break;
			}
			$modelBody[] = 
			[
				"classEx" => "tr",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $user->fullname(),
						]
					]
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $t->name_credit_card,
						]
					]
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $t->credit_card,
						]
					]
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $status,
						]
					]
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $principal,
						]
					]
				],
			];			
		}			
	@endphp
</div>
@component("components.tables.table",[
        "modelHead" => $modelHead,
        "modelBody" => $modelBody,
        "themeBody" => "striped"
    ]);
    @slot('attributeExBody')
		id="body-credit-cards"
    @endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DOCUMENTOS @endcomponent
@if(count($request->purchaseRecord->documents)>0)
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
		foreach($request->purchaseRecord->documents as $doc)
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
							"attributeEx" 	=> "target=\"_blank\" href=\"".url('docs/purchase-record/'.$doc->path)."\"",
							"variant" 		=> "secondary",
							"label" 		=> "Archivo",
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
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"variant" => "default"
		])   
	@endcomponent
@else
	@component("components.labels.not-found",["classEx"   => "my-6"]) @endcomponent
@endif

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