@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$accountOrigin	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio",							$request->folio],
			["Título y fecha",					htmlentities($request->purchaseEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->datetitle)->format('d-m-Y')],
			["Número de Orden",					$request->purchaseEnterprise->first()->numberOrder!="" ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
			["Fiscal",							$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por",					$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen",					App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOrigin)->name],
			["Dirección Origen",				App\Area::find($request->purchaseEnterprise->first()->idAreaOrigin)->name],
			["Departamento Origen",				App\Department::find($request->purchaseEnterprise->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen",	$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")"],
			["Proyecto Origen",					App\Project::find($request->purchaseEnterprise->first()->idProjectOrigin)->proyectName],
			["Empresa Destino",					App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto Destino",				App\Project::find($request->purchaseEnterprise->first()->idProjectDestiny)->proyectName],
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
				["value"	=>	"Importe"]
			]
		];
		$countConcept	=	1;
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$taxesConcept=0;
			$retentionConcept=0;
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
	@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
	@php
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
		}
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			foreach ($detail->retentions as $ret)
			{
				$retentions += $ret->amount;
			}
		}
		$subtotalLabel		=	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",");
		$additionalTaxLabel	=	"$ ".number_format($taxes,2,".",",");
		$retentionLabel		=	"$ ".number_format($retentions,2,".",",");
		$taxLabel			=	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",");
		$totalLabel			=	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",");
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"subtotal\"",	"classEx"	=>	"py-2",	"label"	=>	$subtotalLabel]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountAA\"",	"classEx"	=>	"py-2",	"label"	=>	$additionalTaxLabel]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountR\"",		"classEx"	=>	"py-2",	"label"	=>	$retentionLabel]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"totaliva\"",	"classEx"	=>	"py-2",	"label"	=>	$taxLabel]]],
			["label"	=>	"Total:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"total\"",		"classEx"	=>	"py-2",	"label"	=>	$totalLabel]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			name="note"
			placeholder="Ingrese la nota"
			readonly="readonly"
		@endslot
		@slot('textNotes')
			{{ htmlentities($request->purchaseEnterprise->first()->notes) }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		if ($request->purchaseEnterprise->first()->idbanksAccounts != "")
		{
			$requesBank			=	$request->purchaseEnterprise->first()->banks->bank->description;
			$requesAlias		=	$request->purchaseEnterprise->first()->banks->alias;
			$requesAccount		=	$request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
			$requesClabe		=	$request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
			$requesSucursal		=	$request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
			$requesReference	=	$request->purchaseEnterprise->first()->banks->reference != "" ? htmlentities($request->purchaseEnterprise->first()->banks->reference) : "---";
			$requesAmount		=	number_format($request->purchaseEnterprise->first()->amount,2);
		}
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->purchaseEnterprise->first()->typeCurrency !="" ? $request->purchaseEnterprise->first()->typeCurrency : "---",
			"Fecha de pago"		=>	$request->purchaseEnterprise->first()->paymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y') : "---",
			"Forma de pago"		=>	$request->purchaseEnterprise->first()->paymentMethod->method !="" ? $request->purchaseEnterprise->first()->paymentMethod->method : "---",
			"Banco"				=>	$requesBank,
			"Alias"				=>	$requesAlias,
			"Cuenta"			=>	$requesAccount,
			"Clabe"				=>	$requesClabe,
			"Sucursal"			=>	$requesSucursal,
			"Referencia"		=>	$requesReference,
			"Importe a pagar"	=>	"$ ".$requesAmount,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DOCUMENTOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->purchaseEnterprise->first()->documentsPurchase)>0)
		{
			foreach ($request->purchaseEnterprise->first()->documentsPurchase as $doc)
			{
				$modelHead	=	["Documentos", "Fecha"];
				$body		=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')]
					]
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documentos"];
			$body		=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$accountOrigin	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOriginR);
		$requestAccount	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->purchaseEnterprise->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->purchaseEnterprise->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->purchaseEnterprise->first()->idProjectOriginR)->proyectName,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Destino"		=>	App\Project::find($request->purchaseEnterprise->first()->idProjectDestinyR)->proyectName,
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		ETIQUETAS ASIGNADAS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$totalLabels	=	"";
			$counter	=	0;
			foreach ($detail->labels as $label)
			{
				$counter++;
				$totalLabels	.=	$label->label->description.($counter<count($detail->labels) ? ", " : "");
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$detail->quantity." ".$detail->unit]
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)]
				],
				[
					"content"	=>	["label"	=>	$totalLabels]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component("components.tables.alwaysVisibleTable", ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew" class="request-validate text-center"
		@endslot
	@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
		DATOS DE AUTORIZACIÓN
		@endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@if($request->status == 13)
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DE PAGOS
		@endcomponent
		@php
			$modelTable	=
			[
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : $request->paymentComment
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
		@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->purchaseEnterprise->first()->amount;
		$totalPagado	=	0;
	@endphp
	@if(count($payments) > 0)
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			HISTORIAL DE PAGOS
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"]
				]
			];
			foreach ($payments as $pay)
			{
				if (count($pay->documentsPayments))
				{
					foreach ($pay->documentsPayments as $doc)
					{
						$buttonExt[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"label"			=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""." title=\"".$doc->path."\""
						];
					}
				}
				else 
				{
					$buttonExt	=
					[
						[
							"kind"	=>	"components.labels.label",
							"label"	=>	"Sin documento"
						]
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' '.$pay->accounts->content]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($pay->amount,2)]
					],
					[
						"content"	=>	$buttonExt
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					],
				];
				$totalPagado	+=	$pay->amount;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeExBody')
				class="text-center"
			@endslot
		@endcomponent
		@php
			$resta		=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$totalPagado]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$resta]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@if($request->request_has_reclassification()->exists())
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			HISTORIAL DE RECLASIFICACIÓN
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Empresa Origen"],
					["value"	=>	"Dirección Origen"],
					["value"	=>	"Departamento Origen"],
					["value"	=>	"Clasificación del Gasto Origen"],
					["value"	=>	"Proyecto Origen"],
					["value"	=>	"Empresa Destino"],
					["value"	=>	"Clasificación del Gasto Destino"],
					["value"	=>	"Proyecto Destino"]
				]
			];
			foreach($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$body	=
				[
					[
						"content"	=>
						[
							["label"	=>	$r->enterpriseOrigin->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->enterpriseOrigin->name."\"",
								"classEx"		=>	"enterprise"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->user->name.' '.$r->user->last_name.' '.$r->user->scnd_last_name."\"",
								"classEx"		=>	"name"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$r->date)->format('d-m-Y')."\"",
								"classEx"		=>	"date"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".htmlentities($r->commentaries)."\"",
								"classEx"		=>	"commentaries"
							],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->directionOrigin->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->directionOrigin->name."\"",
								"classEx"		=>	"direction"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->departmentOrigin->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->departmentOrigin->name."\"",
								"classEx"		=>	"department"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accountsOrigin->account.' '.$r->accountsOrigin->description],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accountsOrigin->account.' '.$r->accountsOrigin->description."\"",
								"classEx"		=>	"account"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->projectOrigin->proyectName],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->projectOrigin->proyectName."\"",
								"classEx"		=>	"project"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->enterpriseDestiny->name],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accountsDestiny->account.' '.$r->accountsDestiny->description],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->projectDestiny->proyectName],
						],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
	@endif
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CLASIFICACIÓN ACTUAL
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-purchase-enterprise',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.subtitle', ["label" => "CUENTA DE ORIGEN", "classExContainer" => "mt-8"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idEnterpriseOriginR == $enterprise->id)
						{
							$optionsEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterprise])
					@slot('attributeEx')
						name="enterpriseid_origin"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises-origin
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent	
				@php
					$optionsDirection	=	[];
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAreaOriginR == $area->id)
						{
							$optionsDirection[]	=	["value"	=>	$area->id,	"description"	=>	$area->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsDirection[]	=	["value"	=>	$area->id,	"description"	=>	$area->name];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsDirection])
					@slot('attributeEx')
						multiple="multiple"
						name="areaid_origin"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-areas-origin
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@php
					$optionsDepartment	=	[];
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idDepartamentOriginR == $department->id)
						{
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsDepartment])
					@slot('attributeEx')
						multiple="multiple"
						name="departmentid_origin"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-departments-origin
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					if (isset($request))
					{
						$optionsAccount	=	collect();
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAccAccOriginR !="")
						{
							$optionsAccount	=	$optionsAccount->concat([["value"	=>	$request->purchaseEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->purchaseEnterprise->first()->accountOrigin->account." - ".$request->purchaseEnterprise->first()->accountOrigin->description." (".$request->purchaseEnterprise->first()->accountOrigin->content.")", "selected"	=>	"selected"]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsAccount])
					@slot('attributeEx')
						multiple="multiple"
						name="accountid_origin"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-origin
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionsPoject	=	[];
					foreach (App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idProjectOriginR == $project->idproyect)
						{
							$optionsPoject[]	=	["value"	=>	$project->idproyect,	"description"	=>	$project->proyectName,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsPoject[]	=	["value"	=>	$project->idproyect,	"description"	=>	$project->proyectName];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsPoject])
					@slot('attributeEx')
						name="projectid_origin"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-projects-origin
					@endslot
				@endcomponent
			</div>
		</div>
		@endcomponent
		@component('components.labels.subtitle', ["label" => "CUENTA DE DESTINO"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idEnterpriseDestinyR == $enterprise->id)
						{
							$optionsEnterprise[]	=	["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsEnterprise[]	=	["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterprise])
					@slot('attributeEx')
						name="enterpriseid_destination"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises-destination
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					if (isset($request))
					{
						$options	=	collect();
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAccAccDestinyR !="")
						{
							$options	=	$options->concat([["value"	=>	$request->purchaseEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->purchaseEnterprise->first()->accountDestiny->account." - ".$request->purchaseEnterprise->first()->accountDestiny->description." (".$request->purchaseEnterprise->first()->accountDestiny->content.")", "selected"	=>	"selected"]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						multiple="multiple"
						name="accountid_destination"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-destination
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionsPoject	=	[];
					foreach (App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
					{
						if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idProjectDestinyR == $project->idproyect)
						{
							$optionsPoject[]	=	["value"	=>	$project->idproyect,	"description"	=>	$project->proyectName,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsPoject[]	=	["value"	=>	$project->idproyect,	"description"	=>	$project->proyectName];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsPoject])
					@slot('attributeEx')
						name="projectid_destination"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-projects-destination
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.label', ["classEx" => "mt-8"]) Comentarios (opcional): @endcomponent
		@component("components.inputs.text-area")
			@slot('attributeEx')
				name="commentaries"
			@endslot
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx')
					mr-2
				@endslot
				@slot('attributeEx')
					type="submit"
					name="enviar"
					value="RECLASIFICAR"
				@endslot
				RECLASIFICAR
			@endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["classEx" => "load-actioner", "buttonElement" => "a", "variant" => "reset", "attributeEx" => "href=\"".$href."\"", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$.validate(
	{
		form: '#container-alta',
		onSuccess : function($form)
		{
			return true;
		},
		onError : function($form)
		{
			swal('','{{ Lang::get("messages.form_error") }}','error');
			return false;
		}
	});
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 18});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 18});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-origin",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areas-origin",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departments-origin",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-projects-origin",
					"placeholder"				=> "Seleccione el proyecto/contrato",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-projects-destination",
					"placeholder"				=> "Seleccione el proyecto/contrato",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		count = 0;
		$(document).on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
	});
</script>
@endsection
