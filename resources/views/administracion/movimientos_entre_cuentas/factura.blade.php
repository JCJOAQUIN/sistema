@extends('layouts.child_module')

@php
	$totalBill = $request->bill->sum('total');
	switch ($request->kind) 
	{
		case 11:
			$titleRequest		=	$request->adjustment->first()->title;
			$datetitle			=	$request->adjustment->first()->datetitle != null ? Carbon\Carbon::parse($request->adjustment->first()->datetitle)->format('d-m-Y') : "";
			$fiscal				=	$request->taxPayment == 1 ? 'Sí' : 'No';
			$requestUser		=	$request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
			$amount				=	$request->adjustment->first()->amount;
			$taxRegime			=	$request->adjustment->first()->enterpriseOrigin->taxRegime;
			$originEnterprise	=	$request->adjustment->first()->enterpriseOrigin->id;
			$rfcOrigin			=	$request->adjustment->first()->enterpriseOrigin->rfc; 
			$nameOrigin			=	$request->adjustment->first()->enterpriseOrigin->name;
			$rfcDestiny			=	$request->adjustment->first()->enterpriseDestiny->rfc;
			$nameDestiny		=	$request->adjustment->first()->enterpriseDestiny->name;
			break;
		case 13:
			$titleRequest		=	$request->purchaseEnterprise->first()->title;
			$datetitle			=	$request->purchaseEnterprise->first()->datetitle != null ? Carbon\Carbon::parse($request->purchaseEnterprise->first()->datetitle)->format('d-m-Y') : "";
			$fiscal		=	$request->taxPayment == 1 ? 'Sí' : 'No';
			$requestUser		=	$request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
			$amount		=	$request->purchaseEnterprise->first()->amount;
			$taxRegime		=	$request->purchaseEnterprise->first()->enterpriseOrigin->taxRegime;
			$originEnterprise		=	$request->purchaseEnterprise->first()->enterpriseDestiny->id;
			$rfcOrigin		=	$request->purchaseEnterprise->first()->enterpriseDestiny->rfc;
			$nameOrigin		=	$request->purchaseEnterprise->first()->enterpriseDestiny->name;
			$rfcDestiny		=	$request->purchaseEnterprise->first()->enterpriseOrigin->rfc;
			$nameDestiny		=	$request->purchaseEnterprise->first()->enterpriseOrigin->name;
			break;
		case 14:
			if($request->groups->first()->operationType == 'Entrada')
			{
				$titleRequest     = $request->groups->first()->title;
				$datetitle        = $request->groups->first()->datetitle != null ? Carbon\Carbon::parse($request->groups->first()->datetitle)->format('d-m-Y') : "";
				$fiscal           = $request->taxPayment == 1 ? 'Sí' : 'No';
				$requestUser      = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
				$amount           = $request->groups->first()->amount;
				$taxRegime        = $request->groups->first()->enterpriseDestiny->taxRegime;
				$originEnterprise = $request->groups->first()->enterpriseDestiny->id;
				$rfcOrigin        = $request->groups->first()->enterpriseDestiny->rfc;
				$nameOrigin       = $request->groups->first()->enterpriseDestiny->name;
				$rfcDestiny       = $request->groups->first()->provider->rfc;
				$nameDestiny      = $request->groups->first()->provider->businessName;
			}
			else
			{
				return redirect('/error');
			}
			break;
	}
	$incomeBill   = true;
@endphp

@section('data')
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Título"],
				["value"	=>	"Fecha"],
				["value"	=>	"Fiscal"],
				["value"	=>	"Solicitante"],
				["value"	=>	"Total"],
				["value"	=>	"Acción"],
			]
		];
		$body	=
		[
			[
				"content"	=>	["label"	=>	htmlentities($titleRequest)]
			],
			[
				"content"	=>	["label"	=>	$datetitle]
			],
			[
				"content"	=>	["label"	=>	$fiscal]
			],
			[
				"content"	=>	["label"	=>	$requestUser]
			],
			[
				"content"	=>	["label"	=>	"$ ".number_format($amount,2)]
			],
			[
				"content"	=>
				[
					"kind"			=>	"components.buttons.button",
					"variant"		=>	"warning",
					"attributeEx"	=>	"type=\"button\" id=\"view-detail\"",
					"label"			=>	"<span class=\"icon-plus\"></span>"
				]
			]
		];
		$modelBody[]	=	$body;
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Datos de solicitud
		@endslot
	@endcomponent
	<diV id="detail-request" style="display: none;">
		@php
			$taxes = $retentions = 0;
		@endphp
		@switch($request->kind)
			@case(11)
				<div class="form-container">
					@php
						$modelHead	=	[];
						$body		=	[];
						$modelBody	=	[];
						$modelHead	=
						[
							[
								["value"	=>	"#"],
								["value"	=>	"Solicitud de"],
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
						foreach($request->adjustment->first()->adjustmentFolios as $detail)
						{
							switch ($detail->requestModel->kind)
							{
								case '1':
									foreach ($detail->requestModel->purchases->first()->detailPurchase as $detpurchase)
									{
										$taxesConcept=0;
										foreach ($detpurchase->taxes as $tax)
										{
											$taxesConcept+=$tax->amount;
										}
										$retentionConcept=0;
										foreach ($detpurchase->retentions as $ret)
										{
											$retentionConcept+=$ret->amount;
										}
										$body	=
										[
											[
												"content"	=>	["label"	=>	$countConcept]
											],
											[
												"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
											],
											[
												"content"	=>	["label"	=>	$detpurchase->quantity]
											],
											[
												"content"	=>	["label"	=>	$detpurchase->unit]
											],
											[
												"content"	=>	["label"	=>	$detpurchase->description]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detpurchase->unitPrice,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detpurchase->tax,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detpurchase->amount,2)]
											],
										];
										$countConcept++;
										$modelBody[]	=	$body;
									}
									break;
								case '3':
									foreach ($detail->requestModel->expenses->first()->expensesDetail as $detexpenses)
									{
										$taxesConcept=0;
										foreach ($detexpenses->taxes as $tax)
										{
											$taxesConcept+=$tax->amount;
										}
										$retentionConcept=0;
										foreach ($detexpenses->retentions as $ret)
										{
											$retentionConcept+=$ret->amount;
										}
										$body	=
										[
											[
												"content"	=>	["label"	=>	$countConcept]
											],
											[
												"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
											],
											[
												"content"	=>	["label"	=>	"-"]
											],
											[
												"content"	=>	["label"	=>	"-"]
											],
											[
												"content"	=>	["label"	=>	$detexpenses->description]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detexpenses->unitPrice,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detexpenses->tax,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detexpenses->amount,2)]
											],
										];
										$countConcept++;
										$modelBody[]	=	$body;
									}
									break;
								case '9':
									foreach ($detail->requestModel->refunds->first()->refundDetail as $detrefund)
									{
										$taxesConcept=0;
										foreach ($detrefund->taxes as $tax)
										{
											$taxesConcept+=$tax->amount;
										}
										$body	=
										[
											[											
												"content"	=>	["label"	=>	$countConcept]
											],
											[
												"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
											],
											[
												"content"	=>	["label"	=>	"-"]
											],
											[
												"content"	=>	["label"	=>	"-"]
											],
											[
												"content"	=>	["label"	=>	$detrefund->concept]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detrefund->unitPrice,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detrefund->tax,2)]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
											],
											[
												"content"	=>	["label"	=>	"$ 0.00"]
											],
											[
												"content"	=>	["label"	=>	"$ ".number_format($detrefund->amount,2)]
											],
										];
										$countConcept++;
										$modelBody[]	=	$body;
									}
									break;
							}
						}
					@endphp
					@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
						@slot('classEx')
							mt-4
						@endslot
						@slot('attributeExBody')
							id="body"
						@endslot
					@endcomponent
				</div>
				<div class="totales2">
					@php
						foreach ($request->adjustment->first()->detailAdjustment as $detail)
						{
							foreach ($detail->taxes as $tax)
							{
								$taxes += $tax->amount;
							}
						}
						$modelTable	=
						[
							["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->adjustment->first()->subtotales,2,".",",")]]],
							["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->adjustment->first()->additionalTax,2)]]],
							["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->adjustment->first()->retention,2)]]],
							["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->adjustment->first()->tax,2)]]],
							["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->adjustment->first()->amount,2)]]],
						];
					@endphp
					@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @slot('noNotes') @endslot @endcomponent
				</div>
			@break
			@case(13)
				<div class="form-container">
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
						foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $det)
						{
							$taxesConcept=0;
							foreach ($det->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$retentionConcept=0;
							foreach ($det->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}
							$body	=
							[
								[
									"content"	=>	["label"	=>	$countConcept]
								],
								[
									"content"	=>	["label"	=>	$det->quantity]
								],
								[
									"content"	=>	["label"	=>	$det->unit]
								],
								[
									"content"	=>	["label"	=>	$det->description]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->unitPrice,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->tax,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->amount,2)]
								],
							];
							$countConcept++;
							$modelBody[]	=	$body;
						}
					@endphp
					@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
						@slot('attributeEX')
							id="table"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
						@slot('attributeExBody')
							id="body"
						@endslot
					@endcomponent
				</div>
				<div class="totales2">
					@php
						foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $det)
						{
							foreach ($det->taxes as $tax)
							{
								$taxes += $tax->amount;
							}
							foreach ($det->retentions as $ret)
							{
								$retentions += $ret->amount;
							}
						}
						$modelTable	=
						[
							["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",")]]],
							["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
							["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
							["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",")]]],
							["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",")]]],
						];
					@endphp
					@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
						@slot('textNotes')
							{{ $request->purchaseEnterprise->first()->notes }}
						@endslot
					@endcomponent
				</div>
			@break
			@case(14)
				<div class="form-container">
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
						foreach($request->groups->first()->detailGroups as $det)
						{
							$taxesConcept=0;
							foreach ($det->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$retentionConcept=0;
							foreach ($det->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}
							$body	=
							[
								[
									"content"	=>	["label"	=>	$countConcept]
								],
								[
									"content"	=>	["label"	=>	$det->quantity]
								],
								[
									"content"	=>	["label"	=>	$det->unit]
								],
								[
									"content"	=>	["label"	=>	$det->description]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->unitPrice,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->tax,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($det->amount,2)]
								],
							];
							$countConcept++;
							$modelBody[]	=	$body;
						}
					@endphp
					@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('classEx')
							mt-4
						@endslot
						@slot('attributeExBody')
							id="body"
						@endslot
					@endcomponent
				</div>
				<div class="totales2">
					@php
						foreach ($request->groups->first()->detailGroups as $detail)
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
						$modelTable	=
						[
							["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,".",",")]]],
							["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
							["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
							["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,".",",")]]],
							["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($request->groups->first()->amount,2,".",",")]]]
						];
					@endphp
					@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
						@slot('textNotes')
							{{ $request->groups->first()->notes }}
						@endslot
					@endcomponent
				</div>
			@break 
		@endswitch
	</diV>
	@if($request->taxPayment == 1)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Serie"],
					["value"	=>	"Monto"],
					["value"	=>	"Estado"],
					["value"	=>	"Estado  de factura"],
					["value"	=>	"Acción"],
				]
			];
			foreach($request->bill as $savedBill)
			{
				if ($savedBill->status==0)
				{
					$statusBill	=	"Pendiente de timbrado";
				}
				else if ($savedBill->status==1)
				{
					$statusBill	=	"Pendiente de conciliación";
				}
				else if ($savedBill->status==2)
				{
					$statusBill	=	"Coinciliado";
				}
				else if ($savedBill->status==3)
				{
					$statusBill	=	"En proceso de cancelación";
				}
				else if ($savedBill->status==5)
				{
					$statusBill	=	"En proceso de cancelación";
				}
				else
				{
					$statusBill	=	"Cancelado";
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$savedBill->folio]
					],
					[
						"content"	=>	["label"	=>	$savedBill->serie]
					],
					[
						"content"	=>	["label"	=>	$savedBill->total]
					],
					[
						"content"	=>	["label"	=>	$statusBill]
					],
					[
						"content"	=>	["label"	=>	$savedBill->statusCFDI]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#billDetailModal\" data-bill=\"".$savedBill->idBill."\" ",
								"label"			=>	"<span class=\"icon-search\"></span>"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
			@slot('title')
				Facturas registradas
			@endslot
		@endcomponent
		@component('components.forms.form', ["attributeEx" => "id=\"container-factura\" method=\"POST\" action=\"".route('income.projection.income.save', 152)."\""])
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="taxRegime" name="taxRegime" value="{{$taxRegime}}"
				@endslot
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="bill_version" @if(isset($bill)) value="{{ $bill->version }}" @else value="{{ str_replace('_','.',env('CFDI_VERSION','3_3')) }}" @endif
				@endslot
			@endcomponent
			@if($taxRegime == '')
				@component('components.labels.not-found', ["variant" => "alert"])
					@slot('title')
						Error:
					@endslot
					La empresa no cuenta con régimen fiscal registrado por lo que no se podrá proceder con la alta de la factura, capture o indique al personal autorizado que registre este campo en el módulo «Empresa»
				@endcomponent
			@endif
			@php
				if(!isset($bill))
				{
					$bill                     = new App\Bill;
					$bill->rfc                = $rfcOrigin;
					$bill->taxRegime          = $taxRegime;
					$bill->version            = str_replace('_','.',env('CFDI_VERSION','3_3'));
					$bill->clientRfc          = $rfcDestiny;
					$bill->clientBusinessName = $nameDestiny;
				}
			@endphp
			@include('administracion.facturacion.cfdi_form')
		@endcomponent
		@include('administracion.facturacion.cfdi_modals')
	@else
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["ID", "Folio", "Monto", "Estado", ""];
			foreach($request->billNF as $bill)
			{
				if ($bill->statusCFDI==0)
				{
					$statusBill	=	"Pendiente de ingreso";
				}
				else if ($bill->statusCFDI==1)
				{
					$statusBill	=	"Conciliado";
				}
				else if ($bill->statusCFDI==2)
				{
					$statusBill	=	"Cancelado";
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bill->idBill],
					],
					[
						"content"	=>	["label"	=>	$bill->folio],
					],
					[
						"content"	=>	["label"	=>	$bill->total],
					],
					[
						"content"	=>	["label"	=>	$statusBill],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#billDetailModal\" data-bill=\"".$bill->idBill."\"",
								"label"			=>	"<span class=\"icon-search\"></span>"
							]
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('title')
				Ingresos registrados
			@endslot
		@endcomponent
		@component('components.forms.form', ["attributeEx" => "method=\"POST\"	id=\"container-factura\" action=\"".route('income.projection.income.nf.save')."\""])
			@component('components.labels.subtitle', ["label" => "Empresa"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')
						*RFC:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" readonly name="rfc_emitter" value="{{ $rfcOrigin }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">	
					@component('components.labels.label')
						*Razón social:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" readonly name="business_name_emitter" value="{{ $nameOrigin }}"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle', ["label" => "Cliente"]) @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')
						*RFC:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
						type="text" readonly name="rfc_receiver" value="{{ $rfcDestiny }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						*Razón social:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
						type="text" readonly name="business_name_receiver" value="{{ $nameDestiny }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						*Forma de pago:
					@endcomponent
					@php
						$optionsPaymentWay	=	[];
						foreach (App\CatPaymentWay::orderName()->get() as $p)
						{
							$optionsPaymentWay[]	=
							[
								"value"			=>	$p->paymentWay,
								"description"	=>	$p->description,
							];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsPaymentWay])
						@slot('attributeEx')
							name="cfdi_payment_way" multiple="multiple"
						@endslot
						@slot('classEx')
							js-pamentWay
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						*Método de pago:
					@endcomponent
					@php
						$optionsPaymentMethod	=	[];
						foreach (App\CatPaymentMethod::orderName()->get() as $p)
						{
							if ($p->paymentMethod=='PUE')
							{
								$optionsPaymentMethod[]	=
								[
									"value"			=>	$p->paymentMethod,
									"description"	=>	$p->description,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsPaymentMethod[]	=
								[
									"value"			=>	$p->paymentMethod,
									"description"	=>	$p->description,
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsPaymentMethod])
						@slot('attributeEx')
							name="cfdi_payment_method"
						@endslot
						@slot('classEx')
							js-paymentMethod
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Folio:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="folio" type="text" readonly value="{{ $request->folio }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')
						Condiciones de pago:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="conditions"
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"*Cantidad"],
						["value"	=>	"*Descripción"],
						["value"	=>	"*Valor unitario"],
						["value"	=>	"*Importe"],
						["value"	=>	"*Descuento"],
						["value"	=>	"Acción"],
					]
				];
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" id=\"cfdi-quantity\"",
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" id=\"cfdi-description\"",
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" id=\"cfdi-value\"",
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" id=\"cfdi-total\" readonly value=\"0\"",
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"text\" id=\"cfdi-discount\" value=\"0\"",
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"warning",
								"attributeEx"	=>	"type=\"button\"",
								"classEx"		=>	"add-cfdi-concept",
								"label"			=>	"<span class=\"icon-plus\"></span>"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
				@slot('classEx')
					mt-4
				@endslot
			@endcomponent
			@php
				$modelTable	=
				[
					["label"	=>	"*Subtotal:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"subtotalLabel h-10 py-2",	"label"	=>	""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" readonly name=\"subtotal\""]
						]
					],
					["label"	=>	"Descuento:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"discount_cfdiLabel h-10 py-2",	"label"	=>	""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" readonly name=\"discount_cfdi\""]
						]
					],
					["label"	=>	"*Total:",		"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"cfdi_totalLabel h-10 py-2",	"label"	=>	""],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" readonly name=\"cfdi_total\""]
						]
					],
				];
			@endphp
			@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
			<div class="flex justify-end">
				@component('components.buttons.button',["variant" => "primary"])
					@slot('attributeEx')
						type="submit"
					@endslot
					@slot('label')
						Registrar Ingreso
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	@component('components.modals.modal')
		@slot('id')
			billDetailModal
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalHeader')
			@component('components.buttons.button')
				@slot('classEx')
					close
				@endslot
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('label')
					<span aria-hidden="true">&times;</span>
				@endslot
			@endcomponent
		@endslot
		@slot('modalBody')
			<img src="{{asset(getenv('LOADING_IMG'))}}" width="100">
		@endslot
	@endcomponent
@endsection
@section('scripts')
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	@php
		$selects = collect([
			[
				"identificator"          => ".js-regime",
				"placeholder"            => "Seleccione el régimen fiscal",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi",
				"placeholder"            => "Seleccione el uso de CFDI",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi-type",
				"placeholder"            => "Seleccione el tipo de CFDI",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-cfdi-export",
				"placeholder"            => "Seleccione el tipo de exportación",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-currency",
				"placeholder"            => "Seleccione el tipo de moneda",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-payment-way",
				"placeholder"            => "Seleccione el tipo de pago",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-payment-method",
				"placeholder"            => "Seleccione el método de pago",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-pamentWay",
				"placeholder"            => "Seleccione la forma de pago",
				"maximumSelectionLength" => "1"
			],
			[
				"identificator"          => ".js-paymentMethod",
				"placeholder"            => "Seleccione el método de pago",
				"maximumSelectionLength" => "1"
			]
		]);
	@endphp
	@if($request->taxPayment == 1)
		<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
		<script src="{{ asset('js/daterangepicker.js') }}"></script>
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		@include('administracion.facturacion.cfdi_script')
		<script type="text/javascript">
			$(document).ready(function()
			{
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
				validate();
				$('#cfdi-quantity,#cfdi-value,#cfdi-discount').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });

				$(document).on('click','[data-toggle="modal"]',function()
				{
					id	= $(this).attr('data-bill');
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('income.projection.detail') }}',
						data	: {'id':id,'requestModel':{{ $request->folio }}},
						success	: function(data)
						{
							$('.modal-body').html(data);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#billDetailModal').hide();
						}
					});
				})
				.on('click','[data-dismiss="modal"]',function()
				{
					$('.modal-body').html('<center><img src="{{asset(getenv('LOADING_IMG'))}}" width="100"></center>');
				})
				.on('click','#view-detail',function()
				{
					$('#view-detail span.icon-plus').stop(true,true).toggleClass('active');
					$('#detail-request').stop(true,true).slideToggle();
				});
			});
			function validate()
			{
				$.validate(
				{
					form	: '#container-factura',
					modules	: 'security',
					onError	: function($form)
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					}
				});
			}
		</script>
	@else
		<script type="text/javascript">
			$(document).ready(function()
			{
				validate();
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
				$('#cfdi-quantity,#cfdi-value,#cfdi-discount').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
				
				$(document).on('click','[data-toggle="modal"]',function()
				{
					validate();
					id	= $(this).attr('data-bill');
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('income.projection.detailnf') }}',
						data	: {'id':id},
						success	: function(data)
						{
							$('.modal-body').html(data);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#billDetailModal').hide();
						}
					});
				})
				.on('click','[data-dismiss="modal"]',function()
				{
					$('.modal-body').html('<center><img src="{{asset(getenv('LOADING_IMG'))}}" width="100"></center>');
				})
				.on('input','#cfdi-quantity',function()
				{
					q		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
					v		= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
					total	= q * v;
					$('#cfdi-total').val(total);
				})
				.on('input','#cfdi-value',function()
				{
					v		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
					q		= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
					total	= q * v;
					$('#cfdi-total').val(total);
				})
				.on('change','[name="cfdi_payment_way"]',function()
				{
					if($(this).val()=='99')
					{
						$('[name="cfdi_payment_method"]').val('PPD');
					}
					else
					{
						$('[name="cfdi_payment_method"]').val('PUE');
					}
				})
				.on('change','[name="cfdi_payment_method"]',function()
				{
					if($(this).val()=='PUE' && $('[name="cfdi_payment_way"]').val()=='99')
					{
						$('[name="cfdi_payment_way"]').val('01');
					}
					else if($(this).val()=='PPD' && $('[name="cfdi_payment_way"]').val()!='99')
					{
						$('[name="cfdi_payment_way"]').val('99');
					}
				})
				.on('click','.add-cfdi-concept',function()
				{
					quantity	= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
					description	= $('#cfdi-description').val();
					valueCFDI	= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
					total		= $('#cfdi-total').val();
					discount	= isNaN(Number($('#cfdi-discount').val())) ? 0 : Number($('#cfdi-discount').val());
					if(quantity==0 || valueCFDI==0)
					{
						swal('','La cantidad y el valor unitario no puede ser cero','warning');
					}
					else if(quantity=='' || description =='' || valueCFDI=='')
					{
						swal('','Por favor complete los datos del producto','warning');
					}
					else if((Number(discount) > total) && Number(discount)!='')
					{
						swal('','El descuento debe ser menor o igual al importe','warning');
					}
					else
					{
						tr = '				<tr>';
						tr += '					<td class="align-middle"><input name="quantity[]" type="text" readonly class="form-control-plaintext" value="'+quantity+'"></td>';
						tr += '					<td class="align-middle"><input name="description[]" type="text" readonly class="form-control-plaintext" value="'+description+'"></td>';
						tr += '					<td class="align-middle"><input name="valueCFDI[]" type="text" readonly class="form-control-plaintext" value="'+valueCFDI+'"></td>';
						tr += '					<td class="align-middle"><input name="amount[]" type="text" readonly class="form-control-plaintext" value="'+total+'"></td>';
						tr += '					<td class="align-middle"><input name="discount[]" type="text" readonly class="form-control-plaintext" value="'+discount+'"></td>';
						tr += '					<td class="align-middle" style="width: 3%;">';
						tr += '						<button type="button" class="btn btn-red cfdi-concept-delete"><span class="icon-x"></span></button>';
						tr += '					</td>';
						tr += '				</tr>';
						$('.cfdi-concepts>tbody').append(tr);
						$('#cfdi-quantity').val('');
						$('#cfdi-description').val('');
						$('#cfdi-value').val('');
						$('#cfdi-total').val(0);
						$('#cfdi-discount').val(0);
						subtotalGlobal	= 0;
						discountGlobal	= 0;
						$('[name="amount[]"]').each(function(i,v)
						{
							subtotalGlobal += Number($(this).val());
						});
						$('[name="discount[]"]').each(function(i,v)
						{
							discountGlobal += Number($(this).val());
						});
						totalGlobal = Number(subtotalGlobal - discountGlobal);
						$('[name="subtotal"]').val(subtotalGlobal);
						$('[name="discount_cfdi"]').val(discountGlobal);
						$('[name="cfdi_total"]').val(totalGlobal);
					}
				})
				.on('click','#view-detail',function()
				{
					$('#view-detail span.icon-plus').stop(true,true).toggleClass('active');
					$('#detail-request').stop(true,true).slideToggle();
				});
				
				function validate()
				{
					$.validate(
					{
						form		: '#container-factura',
						onSuccess	: function($form)
						{
							if($('.table.cfdi-concepts tbody tr').length<1)
							{
								swal('','Por favor ingrese al menos un concepto','error');
								return false;
							}
							else if(Number($('[name="cfdi_total"]').val()) <= 0)
							{
								swal('','No pueden registrarse pagos en cero o total negativo','error');
								return false;
							}
						},
					});
				}
			});
		</script>
	@endif
@endsection

@php
	function serie($num)
	{
		$result	= '';
		$prev	= $num/26;
		if($prev>1)
		{
			$prev	= floor($prev);
			$res	= $num%26;
			if($res == 0)
			{
				$prev	= $prev - 1;
			}
			$result	= chr(substr("000".($prev+64),-3));
			$num	= $num - ($prev*26);
		}
		$result	.= chr(substr("000".($num+64),-3));
		return $result;
	}
@endphp
