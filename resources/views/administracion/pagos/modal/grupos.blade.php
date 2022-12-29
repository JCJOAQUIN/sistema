<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>

@php 
	$taxes 		= 0;
	$retentions = 0;

	$requestUser           = App\User::find($request->idRequest);
	$elaborateUser         = App\User::find($request->idElaborate);
	$requestAccountOrigin  = App\Account::find($request->groups->first()->idAccAccOrigin);
	$requestAccountDestiny = App\Account::find($request->groups->first()->idAccAccDestiny);
	$modelTable = 
	[
		["Folio:", $request->folio],
		["Título y fecha:", htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->groups->first()->datetitle)->format('d-m-Y')],
		["Número de Orden:", $request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---'],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No" ],
		["Tipo de Operación:", $request->groups->first()->operationType],
		["Solicitante:", $requestUser->fullName()],
		["Elaborado por:", $elaborateUser->fullName()],
		["Empresa Origen:", App\Enterprise::find($request->groups->first()->idEnterpriseOrigin)->name],
		["Dirección Origen:", App\Area::find($request->groups->first()->idAreaOrigin)->name],
		["Departamento Origen:", App\Department::find($request->groups->first()->idDepartamentOrigin)->name],
		["Clasificación del Gasto Origen:", $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
		["Proyecto Origen:", App\Project::find($request->groups->first()->idProjectOrigin)->proyectName],
		["Empresa Destino:", App\Enterprise::find($request->groups->first()->idEnterpriseDestiny)->name],
		["Clasificación del Gasto Destino:", $requestAccountDestiny->account." - ".$requestAccountDestiny->description." (".$requestAccountDestiny->content.")"],
	];
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"]) 
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent

@component("components.labels.title-divisor",["classExContainer" => "mt-6",]) DATOS DEL PROVEEDOR @endcomponent
@php
	$modelTable	=
	[
		"Razón Social" => $request->groups->first()->provider->businessName,
		"RFC" => $request->groups->first()->provider->rfc,
		"Teléfono" => $request->groups->first()->provider->phone,
		"Calle" => $request->groups->first()->provider->address,
		"Número" => $request->groups->first()->provider->number,
		"Colonia" => $request->groups->first()->provider->colony,
		"CP" => $request->groups->first()->provider->postalCode,
		"Ciudad" => $request->groups->first()->provider->city,
		"Estado" => App\State::find($request->groups->first()->provider->state_idstate)->description,
		"Contacto" => $request->groups->first()->provider->contact,
		"Beneficiario" => $request->groups->first()->provider->beneficiary,
		"Otro" => $request->groups->first()->provider->commentaries == "" ? "---" : $request->groups->first()->provider->commentaries,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent

@php
	$modelHead	=	[];
	$body		=	[];
	$modeBody	=	[];
	$modelHead	=
	[
		[
			["value"	=>	"Banco"],
			["value"	=>	"Alias"],
			["value"	=>	"Cuenta"],
			["value"	=>	"Sucursal"],
			["value"	=>	"Referencia"],
			["value"	=>	"CLABE"],
			["value"	=>	"Moneda"],
			["value"	=>	"Convenio"],
			["value"	=>	""]
		]
	];
	$modelBody = [];

	foreach ($request->groups->first()->provider->providerBank as $bank)
	{
		if ($request->groups->first()->provider_has_banks_id == $bank->id)
		{
			$body	=
			[
				"classEx"	=>	"tr",
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->bank->description,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->alias,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->account,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->branch,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->reference == "" ? "---" : $bank->reference,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->clabe == "" ? "---" : $bank->clabe,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->currency,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind" => "components.labels.label",
							"label"	=>	$bank->agreement == "" ? "---" : $bank->agreement,
						]
					]
				],
				[
					"classEx" => "td",
					"content"	=>	
					[
						[
							"kind"             => "components.inputs.checkbox",
							"label"            => "<span class=\"icon-check\"></span>", 
							"attributeEx"      => "checked",
							"classExContainer" => "inline-flex"
						]
					]
				],
			];
			$modelBody[]	=	$body;
		}
	}
@endphp
@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
	@slot('classEx')
		mt-4
	@endslot
@endcomponent

@component("components.labels.title-divisor",["classExContainer" => "mt-6",]) DATOS DEL PEDIDO @endcomponent
@php
	$modelHead = 
	[
		[
			["value" =>	"#"],
			["value" =>	"Cantidad"],
			["value" =>	"Unidad"],
			["value" =>	"Descripción"],
			["value" =>	"Precio Unitario"],
			["value" =>	"IVA"],
			["value" =>	"Impuesto Adicional"],
			["value" =>	"Retenciones"],
			["value" =>	"Importe"]
		]
	];

	$countConcept = 1;
	$modelBody = [];
	foreach($request->groups->first()->detailGroups as $detail)
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
		$modelBody[] = $body;
	}
@endphp
@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
	@slot('classEx')
		mt-4
	@endslot
	@slot('attributeEx')
		id="table"
	@endslot
@endcomponent
@php
	foreach($request->groups->first()->detailGroups as $detail)
	{
		foreach($detail->taxes as $tax)
		{
			$taxes += $tax->amount;
		}
	}
	
	foreach($request->groups->first()->detailGroups as $detail)
	{
		foreach($detail->retentions as $ret)
		{
			$retentions += $ret->amount;
		}							
	}
	
    $modelTable = [];
    $modelTable = 
    [
        ["label" => "Subtotal: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,'.',','), "classEx" => "my-2"]]],
		["label" => "Impuesto Adicional: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($taxes,2), "classEx" => "my-2"]]],
		["label" => "Retenciones: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($retentions,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,'.',','), "classEx" => "my-2"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind"	=>	"components.labels.label",	"label"	=>	"$ ".number_format($request->groups->first()->amount,2,'.',','), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
])
	@if($request->groups->first()->notes != "")
		@slot('textNotes')
		 {{ $request->groups->first()->notes }}
		@endslot
	@endif
@endcomponent

@component("components.labels.title-divisor",["classExContainer" => "mt-6",]) CONDICIONES DE PAGO @endcomponent
@php
	$modelTable	=
	[
		"Referencia/Número de factura" => $request->groups->first()->reference == "" ? "---" : htmlentities($request->groups->first()->reference),
		"Tipo de moneda" => $request->groups->first()->typeCurrency,
		"Fecha de pago" => $request->PaymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->PaymentDate)->format('d-m-Y') : "---",
		"Forma de pago" => $request->groups->first()->paymentMethod->method,
		"Estado  de factura" => $request->groups->first()->statusBill,
		"Importe a pagar" => "$ ".number_format($request->groups->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent

@component("components.labels.title-divisor",["classExContainer" => "mt-6",]) DOCUMENTOS @endcomponent
@if(count($request->groups->first()->documentsGroups)>0)
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
		foreach($request->groups->first()->documentsGroups as $doc)
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
	@endcomponent
@else
	@component("components.labels.not-found",["classEx" => "my-6"]) @endcomponent
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