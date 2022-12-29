@php
	$taxes	=	$retentions = 0;
@endphp
@php
	$modelTable		=
	[
		["Folio:",								$request->folio],
		["Título y fecha:",						htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y')],
		["Número de Orden:",					$request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---'],
		["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
		["Tipo de Operación:",					$request->groups->first()->operationType],
		["Solicitante:",						$request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
		["Elaborado por:",						$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""],
		["Empresa Origen:",						$request->groups->first()->enterpriseOrigin()->exists() ? $request->groups->first()->enterpriseOrigin->name : ""],
		["Dirección Origen:",					$request->groups->first()->areaOrigin()->exists() ? $request->groups->first()->areaOrigin->name : ""],
		["Departamento Origen:",				$request->groups->first()->departmentOrigin()->exists() ? $request->groups->first()->departmentOrigin->name : ""],
		["Clasificación del Gasto Origen:",		$request->groups->first()->accountOrigin()->exists() ? $request->groups->first()->accountOrigin->fullClasificacionName() : ""],
		["Proyecto Origen:",					$request->groups->first()->projectOrigin()->exists() ? $request->groups->first()->projectOrigin->proyectName : ""],
		["Empresa Destino:",					$request->groups->first()->enterpriseDestiny()->exists() ? $request->groups->first()->enterpriseDestiny->name : ""],
		["Clasificación del Gasto Destino:",	$request->groups->first()->accountDestiny()->exists() ? $request->groups->first()->accountDestiny->fullClasificacionName() : ""],
	];
@endphp
@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
	@slot('classEx')
		mt-4
	@endslot
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent
@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PROVEEDOR"]) @endcomponent
@php
	$modelTable	=
	[
		"Razón Social"	=>	$request->groups->first()->provider->businessName,
		"RFC"			=>	$request->groups->first()->provider->rfc,
		"Teléfono"		=>	$request->groups->first()->provider->phone,
		"Calle"			=>	$request->groups->first()->provider->address,
		"Número"		=>	$request->groups->first()->provider->number,
		"Colonia"		=>	$request->groups->first()->provider->colony,
		"CP"			=>	$request->groups->first()->provider->postalCode,
		"Ciudad"		=>	$request->groups->first()->provider->city,
		"Estado"		=>	App\State::find($request->groups->first()->provider->state_idstate)->description,
		"Contacto"		=>	$request->groups->first()->provider->contact,
		"Beneficiario"	=>	$request->groups->first()->provider->beneficiary,
		"Otro"			=>	$request->groups->first()->provider->commentaries,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
@php
	$modelHead	=	[];
	$body		=	[];
	$modelBody	=	[];
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
			["value"	=>	"IBAN"],
			["value"	=>	"BIC/SWIFT"],
			["value"	=>	"Convenio"],
		]
	];
	foreach($request->groups->first()->provider->providerData->providerBank as $bank)
	{
		$marktr	=	$request->groups->first()->provider_has_banks_id == $bank->id ? "marktr" : "";
		if ($request->groups->first()->provider_has_banks_id == $bank->id)
		{
			$marktr	=	"marktr";
		}
		$bankIban		=	$bank->iban	=='' ? "---" : $bank->iban;
		$bankBic_swift	=	$bank->bic_swift=='' ? "---" : $bank->bic_swift;
		$bankAgreement	=	$bank->agreement=='' ? "---" : $bank->agreement;
		$body	=
		[
			"classEx"	=>	$marktr,
			[
				"content"	=>	["label"	=>	$bank->bank->description]
			],
			[
				"content"	=>	["label"	=>	$bank->alias]
			],
			[
				"content"	=>	["label"	=>	$bank->account]
			],
			[
				"show"		=>	"true",
				"content"	=>	["label"	=>	$bank->branch]
			],
			[
				"content"	=>	["label"	=>	$bank->reference]
			],
			[
				"content"	=>	["label"	=>	$bank->clabe]
			],
			[
				"content"	=>	["label"	=>	$bank->currency]
			],
			[
				"content"	=>	["label"	=>	$bankIban]
			],
			[
				"content"	=>	["label"	=>	$bankBic_swift]
			],
			[
				"content"	=>	["label"	=>	$bankAgreement]
			]
		];
		$modelBody[]	=	$body;
	}
@endphp
@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
	@slot('classEx')
		mt-4
	@endslot
	@slot('attributeEx')
		id="table2"
	@endslot
@endcomponent
@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PEDIDO"]) @endcomponent
@php
	$modelHead	=	[];
	$body		=	[];
	$modelBody	=	[];
	$modelHead	=
	[
		[
			["value"	=>	"#"],
			["value"	=>	"Cantidad"],
			["value"	=>	"Unidad"],
			["value"	=>	"Descripción"],
			["value"	=>	"Precio Unitario"],
			["value"	=>	"IVA"],
			["value"	=>	"Impuesto Adicional"],
			["value"	=>	"Retenciones"],
			["value"	=>	"Importe"],
		]
	];
	$countConcept = 1;
	foreach($request->groups->first()->detailGroups as $detail)
	{
		$taxesConcept		=	0;
		$retentionConcept	=	0;
		foreach ($detail->taxes as $tax)
		{
			$taxesConcept+=$tax->amount;
		}
		foreach ($detail->retentions as $ret)
		{
			$retentionConcept+=$ret->amount;
		}
		$body	=
		[
			[
				"content"	=>	["label"	=>	$countConcept]
			],
			[
				"content"	=>	["label"	=>	$detail->quantity]
			],
			[
				"content"	=>	["label"	=>	$detail->unit]
			],
			[
				"content"	=>	["label"	=>	htmlentities($detail->description)]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($detail->amount,2)]
			],
		];
		$countConcept++;
		$modelBody[]	=	$body;
	}
@endphp
@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
	@slot('classEx')
		mt-4
	@endslot
	@slot('attributeEx')
		id="body"
	@endslot
@endcomponent
@php
	foreach ($request->groups->first()->detailGroups as $detail)
	{
		foreach ($detail->taxes as $tax)
		{
			$taxes += $tax->amount;
		}
	}
	foreach ($request->groups->first()->detailGroups as $detail)
	{
		foreach ($detail->retentions as $ret)
		{
			$retentions += $ret->amount;
		}
	}
	$modelTable	=
	[
		["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,".",","),	"attributeEx"	=>	"name=\"subtotal\""]]],
		["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2,".",","),	"attributeEx"	=>	"name=\"amountAA\""]]],
		["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2,".",","),	"attributeEx"	=>	"name=\"amountR\""]]],
		["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,".",","),	"attributeEx"	=>	"name=\"totaliva\""]]],
		["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->amount,2,".",","),	"attributeEx"	=>	"name=\"total\""]]],
	];
@endphp
@component('components.templates.outputs.form-details', ["modelTable" => $modelTable, "textNotes" => $request->groups->first()->notes, "classExComment" => "disabled", "attributeExComment" => "name=\"note\""])@endcomponent
@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL MOVIMIENTO"]) @endcomponent
@php
	$modelTable	=
	[
		"Importe Total"		=>	"$ ".$request->groups->first()->amount,
		"Comisión"			=>	"$ ".$request->groups->first()->commission,
		"Importe a retomar"	=>	"$ ".$request->groups->first()->amountRetake,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
@php
	$modelTable	=
	[
		"Referencia/Número de factura"	=>	$request->groups->first()->reference,
		"Tipo de moneda"				=>	$request->groups->first()->typeCurrency,
		"Fecha de pago"					=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y'),
		"Forma de pago"					=>	$request->groups->first()->paymentMethod->method,
		"Estado  de factura"			=>	$request->groups->first()->statusBill,
		"Importe a paga"				=>	"$ ".number_format($request->groups->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent