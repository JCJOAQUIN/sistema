<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>

@php
	$requestUser = App\User::find($req->idRequest);
	$elaborateUser = App\User::find($req->idElaborate);
	$requestAccount = App\Account::find($req->account);
	$modelTable =
	[
		["Folio:", $req->folio],
		["Título y fecha:", htmlentities($req->finance->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $req->finance->datetitle)->format('d-m-Y')],
		["Fiscal:", $req->taxPayment == 1 ? "Si" : "No" ],
		["Solicitante:", $req->requestUser->fullname()],
		["Elaborado por:", $req->elaborateUser->fullname()],
		["Empresa:", $req->requestEnterprise->name],
		["Dirección:", $req->requestDirection->name],
		["Departamento:", $req->requestDepartment->name],
		["Clasificación del gasto:", $req->accounts->account." - ".$req->accounts->description." (".$req->accounts->content.")"],
		["Proyecto:", isset(App\Project::find($req->idProject)->proyectName) ? App\Project::find($req->idProject)->proyectName : 'No se selccionó proyecto'],
	];
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"])
	@slot('title')
		Detalles de la Solicitud de {{ $req->requestkind->kind }}
	@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DEL GASTO FINANCIERO @endcomponent
@php
	$modelTable	=
	[
		"Tipo"           => $req->finance->kind,
		"Fecha de Pago"  => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $req->PaymentDate)->format('d-m-Y'),
		"Método de Pago" => $req->finance->paymentMethod,
		"Banco"	         => $req->finance->banks->description,
		"Cuenta"         => $req->finance->bankAccount()->exists() ? $req->finance->bankAccount->alias.' - '.$req->finance->bankAccount->account  : '---',
		"Tarjeta"        => $req->finance->creditCard()->exists() ? $req->finance->creditCard->alias.' - '.$req->finance->creditCard->credit_card : '---',
		"Moneda"         => $req->finance->currency,
		"Notas"          => $req->finance->note != "" ? $req->finance->note : "---",
		"Semana"         => $req->finance->week,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent
@php
    $modelTable = [];
    $modelTable =
    [
		["label" => "Subtotal: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($req->finance->subtotal,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($req->finance->tax,2), "classEx" => "my-2"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($req->finance->amount,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
	"textNotes" => ""
])
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
					"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payment->paymentDate)->format('d-m-Y H:i:s'),
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