@php
	$taxes = $retentions = 0;	
	$modelTable	=
	[
		["Folio:", $request->folio ],
		["Título y fecha:", (isset($request->purchaseEnterprise->first()->title) ? htmlentities($request->purchaseEnterprise->first()->title) : "-")." - ".(isset($request->purchaseEnterprise->first()->datetitle) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->datetitle)->format('d-m-Y') : "-")],
		["Número de Orden:", $request->purchaseEnterprise->first()->numberOrder != "" ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No"],
		["Solicitante:",$request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
		["Elaborado por:",$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""],
		["Empresa Origen:",$request->purchaseEnterprise->first()->enterpriseOrigin()->exists() ? $request->purchaseEnterprise->first()->enterpriseOrigin->name : ""],
		["Dirección Origen:", $request->purchaseEnterprise->first()->areaOrigin()->exists() ? $request->purchaseEnterprise->first()->areaOrigin->name : ""],
		["Departamento Origen:",$request->purchaseEnterprise->first()->departmentOrigin()->exists() ? $request->purchaseEnterprise->first()->departmentOrigin->name : ""],
		["Clasificación del Gasto :",$request->purchaseEnterprise->first()->accountOrigin()->exists() ? $request->purchaseEnterprise->first()->accountOrigin->fullClasificacionName() : ""],
		["Proyecto Origen:",$request->purchaseEnterprise->first()->projectOrigin()->exists() ? $request->purchaseEnterprise->first()->projectOrigin->proyectName : ""],
		["Empresa Destino:",$request->purchaseEnterprise->first()->enterpriseDestiny()->exists() ? $request->purchaseEnterprise->first()->enterpriseDestiny->name : ""],
		["Clasificación del Gasto Destino:",$request->purchaseEnterprise->first()->accountDestiny()->exists() ? $request->purchaseEnterprise->first()->accountDestiny->fullClasificacionName() : ""],
		["Proyecto Destino:", $request->purchaseEnterprise->first()->projectDestiny()->exists() ? $request->purchaseEnterprise->first()->projectDestiny->proyectName : ""],
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
@component('components.labels.title-divisor')
	@slot('classEx')
		mt-12
	@endslot
	DATOS DEL PEDIDO
@endcomponent
@php
	$modelHead	= [];
	$body		= [];
	$modelBody	= [];
	$modelHead	=
	[
		[
			["value"  =>  "#"],
			["value"  =>  "Cantidad"],
			["value"  =>  "Unidad"],
			["value"  =>  "Descripción"],
			["value"  =>  "Precio Unitario"],
			["value"  =>  "IVA"],
			["value"  =>  "Impuesto Adicional"],
			["value"  =>  "Retenciones"],
			["value"  =>  "Importe"]
		]
	];
	$countConcept = 1;
	if (isset($request->purchaseEnterprise->first()->detailPurchaseEnterprise))
	{
		foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$taxesConcept = 0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept+=$tax->amount;
			}
			$retentionConcept=0;
			foreach ($detail->retentions as $ret)
			{
				$retentionConcept+=$ret->amount;
			}
			$body =
			[
				[
					"content" =>  ["label"  =>  $countConcept]
				],
				[
					"content" =>  ["label"  =>  $detail->quantity]
				],
				[
					"content" =>  ["label"  =>  htmlentities($detail->unit)]
				],
				[
					"content" =>  ["label"  =>  htmlentities($detail->description)]
				],
				[
					"content" =>  ["label"  =>  "$ ".number_format($detail->unitPrice,2)]
				],
				[
					"content" =>  ["label"  =>  "$ ".number_format($detail->tax,2)]
				],
				[
					"content" =>  ["label"  =>  "$ ".number_format($taxesConcept,2)]
				],
				[
					"content" =>  ["label"  =>  "$ ".number_format($retentionConcept,2)]
				],
				[
					"content" =>  ["label"  =>  "$ ".number_format($detail->amount,2)]
				],
			];
			$countConcept++;
			$modelBody[]  = $body;
		}
	}
@endphp
@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
	@slot('classEx')
		mt-4
	@endslot
	@slot('attributeEx')
		id="table"
	@endslot
	@slot('attributeExBody')
		id="body"
	@endslot
@endcomponent
<div class="totales2">
	@if (isset($request->purchaseEnterprise->first()->detailPurchaseEnterprise))
		@php
			foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
				foreach ($detail->retentions as $ret)
				{
					$retentions += $ret->amount;
				}
			}
			$modelTable =
			[
				["label"  =>  "Subtotal:",      "inputsEx"  =>  [["kind"  =>  "components.labels.label",  "classEx" =>  "py-2", "label" =>  "$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",")]]],
				["label"  =>  "Impuesto Adicional:",  "inputsEx"  =>  [["kind"  =>  "components.labels.label",  "classEx" =>  "py-2", "label" =>  "$ ".number_format($taxes,2)]]],
				["label"  =>  "Retenciones:",     "inputsEx"  =>  [["kind"  =>  "components.labels.label",  "classEx" =>  "py-2", "label" =>  "$ ".number_format($retentions,2)]]],
				["label"  =>  "IVA:",         "inputsEx"  =>  [["kind"  =>  "components.labels.label",  "classEx" =>  "py-2", "label" =>  "$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",")]]],
				["label"  =>  "TOTAL:",       "inputsEx"  =>  [["kind"  =>  "components.labels.label",  "classEx" =>  "py-2", "label" =>  "$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",")]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
			@slot('classExComment')
				totales
			@endslot
			{{ $request->purchaseEnterprise->first()->notes }}
		@endcomponent
	@endif
</div>
@component('components.labels.title-divisor')
	@slot('classEx')
		mt-12
	@endslot
	CONDICIONES DE PAGO
@endcomponent
@php
	if (isset ($request->purchaseEnterprise->first()->idbanksAccounts))
	{
		$bankDescription  = $request->purchaseEnterprise->first()->banks->bank->description;
		$bankAlias      = $request->purchaseEnterprise->first()->banks->alias;
		$bankAccount    = $request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
		$bankbClave     = $request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
		$bankBranch     = $request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
		$bankReference    = $request->purchaseEnterprise->first()->banks->reference != "" ? $request->purchaseEnterprise->first()->banks->reference : "---";
	}
	else
	{
		$bankDescription  = "---";
		$bankAlias      = "---";
		$bankAccount    = "---";
		$bankbClave     = "---";
		$bankBranch     = "---";
		$bankReference    = "---";
	}
	
	$modelTable =
	[
		"Tipo de moneda"  =>  isset($request->purchaseEnterprise->first()->typeCurrency) ? $request->purchaseEnterprise->first()->typeCurrency : "---",
		"Fecha de pago"   =>  isset($request->purchaseEnterprise->first()->paymentDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y') : "---",
		"Forma de pago"   =>  isset($request->purchaseEnterprise->first()->paymentMethod->method) ? $request->purchaseEnterprise->first()->paymentMethod->method : "---",
		"Banco"       =>  $bankDescription,
		"Alias"       =>  $bankAlias,
		"Cuenta"      =>  $bankAccount,
		"Clabe"       =>  $bankbClave,
		"Sucursal"      =>  $bankBranch,
		"Referencia"    =>  $bankReference,
		"Importe a pagar" =>  isset($request->purchaseEnterprise->first()->amount) ? "$ ".number_format($request->purchaseEnterprise->first()->amount,2) : "---",
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent