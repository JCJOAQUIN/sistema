@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) DATOS DEL SOLICITANTE @endcomponent
@php
    $taxes 	  = 0;
    $request  = App\RequestModel::find($request->folio);
    foreach($request->resource as $resource)
    {
        $modelTable	=
        [
            "Forma de pago"	=>	$resource->paymentMethod->method,
            "Referencia"	=>	$resource->reference == "" ? "---": htmlentities($resource->reference),
            "Tipo de moneda" => $resource->currency,
            "Importe"		=>	"$ ".number_format($resource->total,2),
        ];
    }
    foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->resource->first()->idUsers)->get() as $bank)
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

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) RELACIÓN DE DOCUMENTOS SOLICITADOS @endcomponent

@php
    $modelHead = 
    [
        [
            ["value" => "Concepto"],
            ["value" => "Clasificación de gasto"],
            ["value" => "Importe"]
        ]
    ];

    $subtotalFinal = $ivaFinal = $totalFinal = 0;
    $modelBody = [];           
    foreach($request->resource->first()->resourceDetail as $resourceDetail)
    {
        $totalFinal	+= $resourceDetail->amount;
        $body = 
        [
            "classEx" => "tr",
            [
                "classEx" => "td",
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

<div class="totales">
    @php
        $modelTable = [];
        $modelTable = 
        [
            ["label" => "TOTAL: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($totalFinal,2), "classEx" => "my-2"]]],
        ];
    @endphp
    @component("components.templates.outputs.form-details", ["modelTable" => $modelTable]) @endcomponent
</div>


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