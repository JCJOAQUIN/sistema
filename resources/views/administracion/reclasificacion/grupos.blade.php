@extends('layouts.child_module')
@section('data')
	@php
		$taxes	=	$retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$accountOrigin	=	App\Account::find($request->groups->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->groups->first()->idAccAccDestiny);
	
		$modelTable	=
		[
			["Folio",							$request->folio],
			["Título y fecha",					htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y')],
			["Número de Orden",					$request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---'],
			["Fiscal",							$request->taxPayment == 1 ? "Si": "No"],
			["Tipo de Operación",				$request->groups->first()->operationType],
			["Solicitante",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por",					$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen",					App\Enterprise::find($request->groups->first()->idEnterpriseOrigin)->name],
			["Dirección Origen",				App\Area::find($request->groups->first()->idAreaOrigin)->name],
			["Departamento Origen",				App\Department::find($request->groups->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen",	$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")"],
			["Proyecto Origen",					App\Project::find($request->groups->first()->idProjectOrigin)->proyectName],
			["Empresa Destino",					App\Enterprise::find($request->groups->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"]
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
		DATOS DEL PROVEEDOR
	@endcomponent
	@php
		$modelTable	=
		[
			"Razón Social"	=>	$request->groups->first()->provider->businessName !="" ? $request->groups->first()->provider->businessName : "---",
			"RFC"			=>	$request->groups->first()->provider->rfc !="" ? $request->groups->first()->provider->rfc : "---",
			"Teléfono"		=>	$request->groups->first()->provider->phone !="" ? $request->groups->first()->provider->phone : "---",
			"Calle"			=>	$request->groups->first()->provider->address !="" ? $request->groups->first()->provider->address : "---",
			"Número"		=>	$request->groups->first()->provider->number !="" ? $request->groups->first()->provider->number : "---",
			"Colonia"		=>	$request->groups->first()->provider->colony !="" ? $request->groups->first()->provider->colony : "---",
			"CP"			=>	$request->groups->first()->provider->postalCode !="" ? $request->groups->first()->provider->postalCode : "---",
			"Ciudad"		=>	$request->groups->first()->provider->city !="" ? $request->groups->first()->provider->city : "---",
			"Estado"		=>	$request->groups->first()->provider->state_idstate !="" ? App\State::find($request->groups->first()->provider->state_idstate)->description : "---",
			"Contacto"		=>	$request->groups->first()->provider->contact !="" ? $request->groups->first()->provider->contact : "---",
			"Beneficiario"	=>	$request->groups->first()->provider->beneficiary !="" ? $request->groups->first()->provider->beneficiary : "---",
			"Otro"			=>	$request->groups->first()->provider->commentaries !="" ? $request->groups->first()->provider->commentaries : "---",
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
				["value"	=>	"Convenio"],
				["value"	=>	"Acción"]
			]
		];
		foreach($request->groups->first()->provider->providerBank as $bank)
		{
			$body	=
			[
				"classEx"	=>	$request->groups->first()->provider_has_banks_id == $bank->id ? "marktr" : "	",
				[
					"content"	=>	["label"	=>	$bank->bank->description !="" ? $bank->bank->description : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->alias !="" ? $bank->alias : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->account !="" ? $bank->account : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->branch !="" ? $bank->branch : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->reference !="" ? $bank->reference : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->clabe !="" ? $bank->clabe : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->currency !="" ? $bank->currency : "---"],
				],
				[
					"content"	=>	["label"	=>	$bank->agreement=='' ? "---" : $bank->agreement],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"label"			=>	"<span class='icon-x delete-span'></span>",
							"attributeEx"	=>	"type=\"button\" style=\"display: none;\"",
							"classEx"		=>	"delete-item"
						]
					]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="table2"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PEDIDO
	@endcomponent
	@php
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$countConcept	=	1;
		$modelHead		=
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
		foreach($request->groups->first()->detailGroups as $detail)
		{
			$taxesConcept		=	0;
			$retentionConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept	+=	$tax->amount;
			}
			foreach ($detail->retentions as $ret)
			{
				$retentionConcept	+=	$ret->amount;
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept],
				],
				[
					"content"	=>	["label"	=>	$detail->quantity],
				],
				[
					"content"	=>	["label"	=>	$detail->unit],
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->amount,2)],
				],
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="body" class="text-center"
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
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2,".",",")]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2,".",",")]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,".",",")]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->amount,2,".",",")]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			name="note"
			placeholder="Ingrese la nota"
			readonly="readonly"
		@endslot
		@slot('textNotes') {{htmlentities($request->groups->first()->notes)}} @endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL MOVIMIENTO
	@endcomponent
	@php
		$modelTable	=
		[
			"Importe Total"		=>	"$ ".number_format($request->groups->first()->amount,2),
			"Comisión"			=>	"$ ".number_format($request->groups->first()->commission,2),
			"Importe a retomar"	=>	"$ ".number_format($request->groups->first()->amountRetake,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	$request->groups->first()->reference,
			"Tipo de moneda"				=>	$request->groups->first()->typeCurrency,
			"Fecha de pago"					=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y'),
			"Forma de pago"					=>	$request->groups->first()->paymentMethod->method,
			"Estado de factura"				=>	$request->groups->first()->statusBill,
			"Importe a pagar"				=>	"$ ".number_format($request->groups->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
		@endslot
		DOCUMENTOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->groups->first()->documentsGroups)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->groups->first()->documentsGroups as $doc)
			{
				$body	=
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
			$modelHead	=	["Documento"];
			$body	=
			[
				[
					"content"	=>
					[
						["kind"	=>	"components.labels.label","label"	=>	"NO HAY DOCUMENTOS"]
					],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$accountOrigin	=	App\Account::find($request->groups->first()->idAccAccOriginR);
		$requestAccount	=	App\Account::find($request->groups->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->groups->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->groups->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->groups->first()->idProjectOriginR)->proyectName,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
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
		foreach($request->groups->first()->detailGroups as $detail)
		{
			$labelsDescriprion	=	"";
			$counter	=	0;
			foreach ($detail->labels as $label)
			{
				$counter++;
				$labelsDescriprion	.=	$label->label->description.($counter<count($detail->labels) ? ", " : "");
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$detail->quantity." ".$detail->unit],
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)],
				],
				[
					"content"	=>	["label"	=>	$labelsDescriprion],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew"
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
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios": $request->paymentComment,
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->groups->first()->amount;
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
			foreach($payments as $pay)
			{
				foreach ($pay->documentsPayments as $doc)
				{
					$componentExButton[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"".$doc->path."\"",
						"buttonElement"	=>	"a"
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description],
					],
					[
						"content"	=>	["label"	=>	'$'.number_format($pay->amount,2)],
					],
					[
						"content"	=>	$componentExButton,
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')],
					]
				];
				$totalPagado	+=	$pay->amount;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$totalRest	=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($totalPagado,2,".",",")]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($totalRest,2,".",",")]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@endcomponent
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
					["value"	=>	"Clasificación del Gasto Destino"]
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
								"attributeEx"	=>	"type=\"hidden\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$r->date)->format('d-m-Y H:i:s')."\"",
								"classEx"		=>	"date"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->commentaries."\"",
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
							["label"	=>	$r->accountsOrigin->account.' - '.$r->accountsOrigin->description.' ('.$r->accountsOrigin->content.")"],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accountsOrigin->account.' '.$r->accountsOrigin->description.' ('.$r->accountsOrigin->content.")\"",
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
							["label"	=>	$r->enterpriseDestiny->name]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accountsDestiny->account.' - '.$r->accountsDestiny->description.' ('.$r->accountsDestiny->content.")"]
						]
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-groups',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.subtitle', ["label" => "CUENTA DE ORIGEN"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->groups()->exists() && $request->groups->first()->idEnterpriseOriginR == $enterprise->id)
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
						if ($request->groups()->exists() && $request->groups->first()->idAreaOriginR == $area->id)
						{
							$optionsDirection[]	=	["value"	=>	$area->id,	"description"	=>	$area->name,"selected"	=>	"selected"];
						}
						else
						{
							$optionsDirection[]	=	["value"	=>	$area->id,	"description"	=>	$area->name,];
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
						if ($request->groups()->exists() && $request->groups->first()->idDepartamentOriginR == $department->id)
						{
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,];
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
						$optionsAccount		=	collect();
						$accountOriginData	=	App\Account::find($request->groups->first()->idAccAccOriginR);
						$optionsAccount		=	$optionsAccount->concat([["value" => $accountOriginData->idAccAcc, "description" => $accountOriginData->account.' - '.$accountOriginData->description." (".$accountOriginData->content.")", "selected" => "selected"]]);
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
					$options	=	collect();
					if ($request->groups()->exists() && $request->groups->first()->idProjectOriginR != "")
					{
						$options	=	$options->concat([["value"	=>	$request->groups->first()->projectOrigin->idproyect,	"description"	=>	$request->groups->first()->projectOrigin->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
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
		@endcomponent
		@component('components.labels.subtitle', ["label" => "CUENTA DE DESTINO"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->groups()->exists() && $request->groups->first()->idEnterpriseDestinyR == $enterprise->id)
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
						$optionsAccount		=	collect();
						$accountDestinyData	=	App\Account::find($request->groups->first()->idAccAccDestinyR);
						$optionsAccount		=	$optionsAccount->concat([["value" => $accountDestinyData->idAccAcc, "description" => $accountDestinyData->account.' - '.$accountDestinyData->description." (".$accountDestinyData->content.")", "selected" => "selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsAccount])
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
		@endcomponent
		@component('components.labels.label', ["classEx" => "mt-8"]) Comentarios (opcional): @endcomponent
		@component("components.inputs.text-area", ["attributeEx" => "name=\"commentaries\""]) @endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary", "classEx" => "mr-2", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"RECLASIFICAR\"", "label" => "RECLASIFICAR"]) @endcomponent
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
		generalSelect({'selector': '.js-projects-origin', 'model': 21});
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 12});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 6});
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
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
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
