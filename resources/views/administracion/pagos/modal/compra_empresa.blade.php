<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>

@php 
	$taxes 		= 0;
	$retentions = 0;

	$requestUser           = App\User::find($request->idRequest);
	$elaborateUser         = App\User::find($request->idElaborate);
	$requestAccountOrigin  = App\Account::find($request->purchaseEnterprise->first()->idAccAccOrigin);
	$requestAccountDestiny = App\Account::find($request->purchaseEnterprise->first()->idAccAccDestiny);
	$modelTable = 
	[
		["Folio:", $request->folio],
		["Título y fecha:", htmlentities($request->purchaseEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->purchaseEnterprise->first()->datetitle)->format('d-m-Y')],
		["Número de Orden:", $request->purchaseEnterprise->first()->numberOrder != "" ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No"],
		["Solicitante:", $requestUser->fullname()],
		["Elaborado por:", $elaborateUser->fullname()],
		["Empresa Origen:", App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOrigin)->name],
		["Dirección Origen:", App\Area::find($request->purchaseEnterprise->first()->idAreaOrigin)->name],
		["Departamento Origen:", App\Department::find($request->purchaseEnterprise->first()->idDepartamentOrigin)->name], 
		["Clasificación del Gasto Origen:", $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
		["Proyecto Origen:", App\Project::find($request->purchaseEnterprise->first()->idProjectOrigin)->proyectName],
		["Empresa Destino:", App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestiny)->name],
		["Clasificación del Gasto Destino:", $requestAccountDestiny->account." - ".$requestAccountDestiny->description." (".$requestAccountDestiny->description.")"],
		["Proyecto Destino:", App\Project::find($request->purchaseEnterprise->first()->idProjectDestiny)->proyectName],	
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

	foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
	{
		$taxesConcept=0;
		foreach($detail->taxes as $tax)
		{
			$taxesConcept+=$tax->amount;
		}

		$retentionConcept=0;
		foreach($detail->retentions as $ret)
		{
			$retentionConcept+=$ret->amount;
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
						"label" => $countConcept,
					]
				]
			],
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
						"label" => $detail->unit,
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
						"label" => "$ ".number_format($detail->amount,2),
					]
				]
			],
		];
		$countConcept++;
		$modelBody [] = $body;
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
@endcomponent

@php
	foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
	{
		foreach($detail->taxes as $tax)
		{
			$taxes += $tax->amount;
		}
	}
	
	foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
	{
		foreach($detail->retentions as $ret)
		{
			$retentions += $ret->amount;
		}
	}						

    $modelTableDetailPurchase = [];
    $modelTableDetailPurchase = 
    [
        ["label" => "Subtotal: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,'.',','), "classEx" => "my-2"]]],
        ["label" => "Impuesto Adicional: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($taxes,2), "classEx" => "my-2"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($retentions,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,'.',','), "classEx" => "my-2"]]],
        ["label" => "TOTAL: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,'.',','), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTableDetailPurchase,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
	"classEx" => "mt-6",
])
	@if($request->purchaseEnterprise->first()->notes != "")
		@slot('textNotes')
			{{ $request->purchaseEnterprise->first()->notes }}
		@endslot
	@endif
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) CONDICIONES DE PAGO @endcomponent
@php
	$modelTable	=
	[
		"Tipo de Moneda" =>	$request->purchaseEnterprise->first()->typeCurrency,
		"Fecha de pago"	 => $request->purchaseEnterprise->first()->paymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d', $request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y') : "---",
		"Forma de pago"  => $request->purchaseEnterprise->first()->paymentMethod->method,
	];
	if($request->purchaseEnterprise->first()->idbanksAccounts != "")
	{
		$modelTable["Banco"] = $request->purchaseEnterprise->first()->banks->bank->description;
		$modelTable["Alias"] = $request->purchaseEnterprise->first()->banks->alias;
		$modelTable["Cuenta"] = $request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
		
		$modelTable["Clabe"] = $request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
		$modelTable["Sucursal"] = $request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
		$modelTable["Referencia"] = $request->purchaseEnterprise->first()->banks->reference != "" ? htmlentities($request->purchaseEnterprise->first()->banks->reference) : "---";
	}
	$modelTable["Importe a pagar"] = "$ ".number_format($request->purchaseEnterprise->first()->amount,2);
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DOCUMENTOS @endcomponent
@if(count($request->purchaseEnterprise->first()->documentsPurchase)>0)
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
		foreach($request->purchaseEnterprise->first()->documentsPurchase as $doc)
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
							"attributeEx" => "target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
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