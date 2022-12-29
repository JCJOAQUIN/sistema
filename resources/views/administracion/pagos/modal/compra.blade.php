@component('components.labels.title-divisor') DATOS DEL PROVEEDOR @endcomponent
@php
    $modelTableDetails = [
        "Razón Social" => $request->purchases->first()->provider->businessName,
        "RFC" => $request->purchases->first()->provider->rfc,
        "Teléfono" => $request->purchases->first()->provider->phone,
        "Calle" => $request->purchases->first()->provider->address,
        "Número" => $request->purchases->first()->provider->number,
        "Colonia" => $request->purchases->first()->provider->colony,
        "CP" => $request->purchases->first()->provider->postalCode,
        "Ciudad" => $request->purchases->first()->provider->city,
        "Estado" => App\State::find($request->purchases->first()->provider->state_idstate)->description,
        "Contacto" => $request->purchases->first()->provider->contact,
        "Beneficiario" => $request->purchases->first()->provider->beneficiary,
        "Otro" => $request->purchases->first()->provider->commentaries,
    ];
@endphp
@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTableDetails]) @endcomponent
@php
    $modelHeadAccounts = 
    [
        [
            ["value" => "Banco"],
            ["value" => "Cuenta"],
            ["value" => "Sucursal"],
            ["value" => "Referencia"],
            ["value" => "CLABE"],
            ["value" => "Moneda"],
            ["value" => "Convenio"]
        ]
    ];
    $modelTableAccounts = [];
    foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
    {
        if($request->purchases->first()->provider_has_banks_id == $bank->id)
        {
            $modelTableAccounts [] = 
            [
                "classEx" => "tr",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->bank->description,
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"providerBank[]\" value=\"".$bank->idProvider."\"",
                            "classEx"     => "providerBank",
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"bank[]\" value=\"".$bank->banks_idBanks."\"",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->account != null ? $bank->account : '---',
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\"",
                            "classEx"     => "providerBank",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->branch != null ? $bank->branch : '---',
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"branch_office[]\" value=\"".$bank->branch."\"",
                            "classEx"     => "providerBank",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->reference != null ? htmlentities($bank->reference) : '---',
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"reference[]\" value=\"".$bank->reference."\"",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->clabe != null ? $bank->clabe : '---',
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\"",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->currency,
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"currency[]\" value=\"".$bank->currency."\"",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind" => "components.labels.label",
                            "label" => $bank->agreement != null ? $bank->agreement : '---',
                        ],
                        [
                            "kind"        => "components.inputs.input-text",
                            "attributeEx" => "type=\"hidden\" name=\"agreement[]\" value=\"".$bank->agreement."\"",
                        ],
                    ],
                ],
            ];
        }
    }
@endphp
@component("components.tables.table",[
        "modelHead" => $modelHeadAccounts,
        "modelBody" => $modelTableAccounts,
        "themeBody" => "striped"
    ]);
@endcomponent
@component('components.labels.title-divisor',["classExContainer" => "my-6",]) DATOS DEL PEDIDO @endcomponent
@php
    $modelHeadPurchase = 
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
    $modelTablePurchase = [];
    $taxes = $retentions = 0;
    foreach($request->purchases->first()->detailPurchase as $detail)
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
        $modelTablePurchase [] = 
        [
            "classEx" => "tr",
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
                        "label" => $detail->unit != null ? $detail->unit : '---',
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
                        "label" => "$ ".number_format($detail->amount,2),
                    ],								
                ],
            ],
        ];
    }
@endphp
@component("components.tables.table",[
    "modelHead" => $modelHeadPurchase,
    "modelBody" => $modelTablePurchase,
    "themeBody" => "striped",
    "attributeEx" => "id=\"table\"",
    "attributeExBody" => "id=\"body\"",
]);
@endcomponent
@php
    $modelTableDetailPurchase = [];
    $modelTableDetailPurchase = 
    [
        ["label" => "Subtotal: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchases->first()->subtotales,2), "classEx" => "my-2"]]],
        ["label" => "IVA: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchases->first()->tax,2), "classEx" => "my-2"]]],
        ["label" => "Retenciones: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($retentions,2), "classEx" => "my-2"]]],
        ["label" => "Imp. Adicional: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($taxes,2), "classEx" => "my-2"]]],
        ["label" => "TOTAL: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchases->first()->amount,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTableDetailPurchase,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
])
    @if($request->purchases->first()->notes != "")
        @slot('textNotes')
            {{ htmlentities($request->purchases->first()->notes) }}
        @endslot
    @endif
@endcomponent
@component("components.labels.title-divisor",["classExContainer" => "mt-6",]) DOCUMENTOS @endcomponent
@if (count($request->purchases->first()->documents)>0)
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
        foreach($request->purchases->first()->documents as $doc)
        {
            $modelTableDocs[] = 
            [
                "classEx" => "tr",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"        => "components.buttons.button",
                            "buttonElement" => "a",
							"attributeEx" => "target=\"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\"",
							"variant" => "dark-red",
							"label" => "PDF",
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