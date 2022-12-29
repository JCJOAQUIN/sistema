@extends('layouts.child_module')
@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	@if(isset($request->purchases->first()->idRequisition) && $request->purchases->first()->idRequisition != "")
		@component('components.labels.not-found', ["variant" => "note", "attributeEx" => "id=\"error_request\""])
			Esta solicitud viene de la requisición #{{ $request->purchases->first()->idRequisition }}. Si hay algún dato incorrecto por favor modifíquelo.
			@if($request->purchases->first()->requisitionRequest->requisition->wbs()->exists())
				<div class="flex inline mt-2">
					@component('components.labels.label', ["classEx" => "font-bold mr-2"])
						CÓDIGO WBS: 
					@endcomponent
					{{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}.
				</div>
				@if($request->purchases->first()->requisitionRequest->requisition->edt()->exists())
					<div class="flex inline mt-2">
						@component('components.labels.label', ["classEx" => "font-bold mr-2"])
							CÓDIGO EDT:
						@endcomponent
						{{ $request->purchases->first()->requisitionRequest->requisition->edt->fullName() }}.
					</div>
				@endif
			@endif
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$requestAccount	=	App\Account::find($request->account);
	@endphp
	@php
		$modelTable	=
		[
			["Folio",					$request->folio],
			["Título y fecha",			isset($request->purchases->first()->title) && $request->purchases->first()->title !="" ? htmlentities($request->purchases->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchases->first()->datetitle)->format('d-m-Y') : "---"],
			["Número de Orden",			isset($request->purchases->first()->numberOrder) && $request->purchases->first()->numberOrder!="" ? $request->purchases->first()->numberOrder : '---'],
			["Fiscal",					isset($request->taxPayment) && $request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante",				isset($requestUser->name) && $requestUser->name !="" ? $requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name : "---"],
			["Elaborado por",			isset($elaborateUser->name) && $elaborateUser->name!="" ?$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name : "---"],
			["Empresa",					isset(($request->idEnterprise)->name) && ($request->idEnterprise)->name!="" ? App\Enterprise::find($request->idEnterprise)->name : "---"],
			["Dirección ",				isset(($request->idArea)->name) && ($request->idArea)->name !="" ? App\Area::find($request->idArea)->name : "---"],
			["Departamento",			isset(($request->idDepartment)->name) && ($request->idDepartment)->name!="" ? App\Department::find($request->idDepartment)->name : "---"],
			["Clasificación del gasto",	isset($requestAccount) && $requestAccount!="" ? $requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")" : "---"],
			["Proyecto",				isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se seleccionó proyecto'],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
	 	@slot('title')
		 	Detalles de la Solicitud de {{ $request->requestkind->kind }}
		 @endslot
		 @slot('classEx')
			 mt-4
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
			"Razón Social"	=>	$request->purchases->first()->provider->businessName !="" ? $request->purchases->first()->provider->businessName : "---",
			"RFC"			=>	$request->purchases->first()->provider->rfc !="" ? $request->purchases->first()->provider->rfc : "---",
			"Teléfono"		=>	$request->purchases->first()->provider->phone !="" ? $request->purchases->first()->provider->phone : "---",
			"Calle"			=>	$request->purchases->first()->provider->address !="" ? $request->purchases->first()->provider->address : "---",
			"Número"		=>	$request->purchases->first()->provider->number !="" ? $request->purchases->first()->provider->number : "---",
			"Colonia"		=>	$request->purchases->first()->provider->colony !="" ? $request->purchases->first()->provider->colony : "---",
			"CP"			=>	$request->purchases->first()->provider->postalCode !="" ? $request->purchases->first()->provider->postalCode : "---",
			"Ciudad"		=>	$request->purchases->first()->provider->city !="" ? $request->purchases->first()->provider->city : "---",
			"Estado"		=>	$request->purchases->first()->provider->state_idstate !="" ? App\State::find($request->purchases->first()->provider->state_idstate)->description : "---",
			"Contacto"		=>	$request->purchases->first()->provider->contact !="" ? $request->purchases->first()->provider->contact : "---",
			"Beneficiario"	=>	$request->purchases->first()->provider->beneficiary !="" ? $request->purchases->first()->provider->beneficiary : "---",
			"Otro"			=>	$request->purchases->first()->provider->commentaries !="" ? $request->purchases->first()->provider->commentaries : "---",
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
		foreach ($request->purchases->first()->provider->providerBank as $bank)
		{
			$classRow	=	"";
			if ($request->purchases->first()->provider_has_banks_id == $bank->id)
			{
				$classRow	=	"marktr";
			}
			$body	=
			[
				"classEx"		=>	$classRow,
				[
					"content"	=>	["label"	=>	$bank->bank->description!="" ? $bank->bank->description : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->alias!="" ? $bank->alias : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->account!="" ? $bank->account : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->branch!="" ? $bank->branch : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->reference!="" ? $bank->reference : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->clabe!="" ? $bank->clabe : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->currency!="" ? $bank->currency : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->agreement=='' ? "---" : $bank->agreement]
				],
				[
					"content"	=>
					[
						"kind"				=>	"components.buttons.button",
						"variant"			=>	"red",
						"attributeEx"		=>	"type=\"button\"",
						"label"				=>	"<span class='icon-x delete-span'></span>",
						"attributeExLabel"	=>	"style=\"display: none; \""
					]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('classEx')
			mt-4
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
				["value"	=>	"#"	],
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
		foreach ($request->purchases->first()->detailPurchase as $detail)
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
				]
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
			id="body"
		@endslot
	@endcomponent
	@php
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes	+=	$tax->amount;
			}
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"subtotal\"",						"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountAA\"",						"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"amountR\"",							"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"totaliva\"",						"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->tax,2,".",",")]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"total\" id=\"input-extrasmall\"",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchases->first()->amount,2,".",",")]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			name="note"
			placeholder="Ingrese la nota"
			readonly="readonly"
		@endslot
		@slot('textNotes')
			{{ htmlentities($request->purchases->first()->notes) }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	$request->purchases->first()->reference,
			"Tipo de moneda"				=>	$request->purchases->first()->typeCurrency,
			"Fecha de pago"					=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y'),
			"Forma de pago"					=>	$request->purchases->first()->paymentMode,
			"Estado de factura"				=>	$request->purchases->first()->billStatus,
			"Importe a pagar"				=>	"$ ".number_format($request->purchases->first()->amount,2)
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
		if (count($request->purchases->first()->documents)>0)
		{
			$modelHead	=	["Documentos", "Fecha"];
			foreach ($request->purchases->first()->documents as $doc)
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
								"label"			=>	"Archivo",
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
			$body	=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				]
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
		$reviewAccount	=	App\Account::find($request->accountR);
		$labelTicket	=	"";
		if (count($request->labels))
		{
			foreach ($request->labels as $label)
			{
				$labelTicket	.=	$label->description.", ";
			}
		}
		else
		{
			$labelTicket	=	"";
		}
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name."".$request->reviewedUser->last_name."".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "No hay",
			"Nombre del Proyecto"		=>	$request->reviewedProject->proyectName,
			"Etiquetas"					=>	$labelTicket,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment)
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
		$modelHead	=	["Cantidad",	"Descripción",	"Etiquetas"];
		foreach ($request->purchases->first()->detailPurchase as $detail)
		{
			$labelDescription	=	"";
			$counter	=	0;
			if (count($detail->labels))
			{
				foreach ($detail->labels as $label)
				{
					$counter++;
					$labelDescription	.=	$label->label->description.($counter<count($detail->labels) ? ", " : "");
				}
			}
			else
			{
				$labelDescription	=	"Sin etiqueta";
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
					"content"	=>	["label"	=>	$labelDescription]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew"
		@endslot+
		@slot('classExBody')
			request-validate
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
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : $request->authorizeComment
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
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->purchases->first()->amount;
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
						$componentsExt[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"label"			=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""."title=\"".$doc->path."\""
						];
					}
				}
				else 
				{
					$componentsExt	=
					[
						["kind"	=>	"components.labels.label",	"label"	=>	"Sin documento"]
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($pay->amount,2)]
					],
					[
						"content"	=>	$componentsExt
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					],
				];
				$totalPagado += round($pay->amount,2);
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$restaTotal	=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".round($totalPagado,2) ]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".round($restaTotal,2) ]]],
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
					["value"	=>	"Empresa"],
					["value"	=>	"Dirección"],
					["value"	=>	"Departamento"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$wbsData	=	$r->wbs()->exists() ? $r->wbs->code_wbs : 'Sin datos';
				$edtData	=	$r->edt()->exists() ? $r->edt->code : 'Sin datos';
				$body	=
				[
					[
						"content"	=>
						[
							["label"			=>	$r->enterprise->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->enterprise->name."\"",
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
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->commentaries."\"",
								"classEx"		=>	"commentaries"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->direction->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->direction->name."\"",
								"classEx"		=>	"direction"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->department->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->department->name."\"",
								"classEx"		=>	"department"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.")"],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accounts->account.' '.$r->accounts->description."\"",
								"classEx"		=>	"account"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->project->proyectName],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->project->proyectName."\"",
								"classEx"		=>	"project"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$wbsData."\"",
								"classEx"		=>	"wbs hidden"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$edtData."\"",
								"classEx"		=>	"edt hidden"
							],
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"attributeEx"	=>	"type=\"btton\" data-target=\"#modalUpdate\" data-toggle=\"modal\" title=\"Ver datos\"",
								"classEx"		=>	"view-data"
							]
						]
					]
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\", id=\"formsearch\" action=\"".route('reclassification.update-purchase',$request->folio)."\"", "methodEx" => "PUT"])
		<input type="hidden" name="requisition_folio" value="{{ $request->idRequisition }}">
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			CLASIFICACIÓN ACTUAL
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->idEnterpriseR == $enterprise->id)
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
						id="multiple-enterprisesR"
						name="idEnterpriseR"
						multiple="multiple"
						data-validation="required"
						disabled
					@endslot
					@slot('classEx')
						js-enterprisesR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent	
				@php
					$optionsDirection	=	[];
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->idAreaR == $area->id)
						{
							$optionsDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsDirection])
					@slot('attributeEx')
						id="multiple-areasR"
						multiple="multiple"
						name="idAreaR"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-areasR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@php
					$optionsDepartment	=	[];
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->idDepartamentR == $department->id)
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
						id="multiple-departmentsR"
						multiple="multiple"
						name="idDepartmentR"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-departmentsR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					$optionsAccount	=	collect();
					$accountData	=	App\Account::find($request->accountR);
					$optionsAccount	=	$optionsAccount->concat([["value" => $accountData->idAccAcc, "description" => $accountData->account.' - '.$accountData->description." ".$accountData->content, "selected" => "selected"]]);
				@endphp
				@component('components.inputs.select', ["options" => $optionsAccount])
					@slot('attributeEx')
						id="multiple-accountsR"
						multiple="multiple"
						name="accountR"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-accountsR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionsPoject	=	collect();
					$projectData	=	App\Project::find($request->idProjectR);
					$optionsPoject	=	$optionsPoject->concat([["value" => $projectData->idproyect, "description" => $projectData->proyectName, "selected" => "selected"]]);
				@endphp
				@component('components.inputs.select', ["options" => $optionsPoject])
					@slot('attributeEx')
						id="multiple-projectsR"
						name="project_id"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-projects
					@endslot
				@endcomponent
			</div>
			@if(isset($requisition) && $requisition != "" && $request->reviewedProject->codeWBS()->exists())
				<div class="col-span-2 select_father_wbs @if(isset($requisition)) @if($requisition->idProject != '' && $requisition->requestProject->codeWBS()->exists()) block @else hidden @endif @else block @endif">
					@component('components.labels.label', ["label" => "WBS:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($requisition) && $requisition->requisition->code_wbs != "")
						{
							$wbsSelected	=	App\CatCodeWBS::find($requisition->requisition->code_wbs);
							$options		=	$options->concat([["value" => $wbsSelected->id, "description" => $wbsSelected->code_wbs, "selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_wbs removeselect"]) @endcomponent
				</div>
			@elseif(isset($request))
				<div class="col-span-2 select_father_wbs @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists()  && $request->code_wbs != "") block @else hidden @endif @else block @endif">
					@component('components.labels.label', ["label" => "WBS:"])  @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->code_wbs != "")
						{
							$wbsSelected	=	App\CatCodeWBS::find($request->code_wbs);
							$options		=	$options->concat([["value" => $wbsSelected->id, "description" => $wbsSelected->code_wbs, "selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_wbs removeselect"]) @endcomponent
				</div>
			@endif
			@if(isset($requisition) && $requisition != "" && $request->reviewedProject->codeWBS()->exists())
				<div class="col-span-2 select_father_edt @if(isset($requisition)) @if($requisition->idProject != '' && $requisition->requestProject->codeWBS()->exists() && $requisition->requisition->wbs()->exists() && $requisition->requisition->wbs->codeEDT()->exists()) block @endif @else block @endif">
					@component('components.labels.label', ["label" => "EDT:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($requisition) && $requisition->requisition->code_edt != "")
						{
							$edtSelected	=	App\CatCodeEDT::find($requisition->requisition->code_edt);
							$options		=	$options->concat([["value" => $edtSelected->id, "description" => $edtSelected->code." (".$edtSelected->description.")", "selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_edt removeselect"]) @endcomponent
				</div>
			@elseif(isset($request))
				<div class="col-span-2 select_father_edt @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists() && $request->wbs()->exists() && $request->wbs->codeEDT()->exists()) block @else hidden @endif @else block @endif">
					@component('components.labels.label', ["label" => "EDT:"]) @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->code_edt != "")
						{
							$edtSelected	=	App\CatCodeEDT::find($request->code_edt);
							$options		=	$options->concat([["value" => $edtSelected->id, "description" =>	$edtSelected->code." (".$edtSelected->description.")", "selected" => "selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_edt removeselect"]) @endcomponent
				</div>
			@endif
		@endcomponent
		@component('components.labels.label', ["classEx" => "mt-8"]) Comentarios (opcional) @endcomponent
		@component('components.inputs.text-area', ["attributeEx" => "name=\"commentaries\""]) @endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx')
					mr-2
				@endslot
				@slot('attributeEx')
					type="submit"
					name="send"
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
	@component("components.modals.modal",["variant" => "large"])
		@slot('id')modalUpdate @endslot
		@slot('classEx')
			modal fade
		@endslot
		@slot('modalBody')
			@php
				$modelHead	=	[];
				$modelHead	=	["INFORMACIÓN"];
				$modelBody	=	[];
			@endphp
			@component('components.tables.alwaysVisibleTable', ["variant" => "default", "modelHead" => $modelHead, "modelBody", $modelBody])@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Modificó: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-name"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-date"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-enterprise"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-direction"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-department"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-project"
						@endslot
					@endcomponent
				</div>
				@if($request->idRequisition != "")
					<div class="col-span-2">
						@component('components.labels.label') WBS @endcomponent
						@component('components.labels.label', ["attributeEx" => "name=\"view-wbs\""]) @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') EDT @endcomponent
						@component('components.labels.label', ["attributeEx" => "name=\"view-edt\""]) @endcomponent
					</div>
				@endif
				<div class="col-span-2">
					@component('components.labels.label') Clasificación de gasto: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-account"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Comentarios: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-commentaries"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endslot
		@slot('modalFooter')
			@component("components.buttons.button",["variant" => "red"])
				@slot('classEx')
					modal-close
				@endslot
				@slot('attributeEx')
					type=button
					data-dismiss="modal"
				@endslot
				Cerrar
			@endcomponent
		@endslot
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
		onSuccess : function($form) { }
	});
	
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-projects', 'model': 14});
		generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
		generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
		generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprisesR",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areasR",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departmentsR",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(function()
		{
			$( "#datepicker" ).datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
		});
		
		$(document).on('click','.view-data',function()
		{
			$('[name="view-name"]').text($(this).parent('div').parent('div').parent('div').find('.name').val());
			$('[name="view-date"]').text($(this).parent('div').parent('div').parent('div').find('.date').val());
			$('[name="view-enterprise"]').text($(this).parent('div').parent('div').parent('div').find('.enterprise').val());
			$('[name="view-direction"]').text($(this).parent('div').parent('div').parent('div').find('.direction').val());
			$('[name="view-department"]').text($(this).parent('div').parent('div').parent('div').find('.department').val());
			$('[name="view-project"]').text($(this).parent('div').parent('div').parent('div').find('.project').val());
			$('[name="view-account"]').text($(this).parent('div').parent('div').parent('div').find('.account').val());
			$('[name="view-commentaries"]').text($(this).parent('div').parent('div').parent('div').find('.commentaries').val());
			$('[name="view-wbs"]').text($(this).parent('div').parent('div').parent('div').find('.wbs').val());
			$('[name="view-edt"]').text($(this).parent('div').parent('div').parent('div').find('.edt').val());
			$("#modalUpdate").show();
		})
		.on('click','.exit',function()
		{
			$('#modalUpdate').fadeOut();
		})
		.on('change','.js-enterprisesR',function()
		{
			$('.js-accountsR').empty();
		})
		.on('change','[name="project_id"]',function()
		{
			id = $(this).find('option:selected').val();
			if (id != null)
			{
				$.each(generalSelectProject,function(i,v)
				{
					if(id == v.id)
					{
						if(v.flagWBS != null)
						{
							$('.select_father_wbs').removeClass('hidden').addClass('block');
							generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
						}
						else
						{
							$('.js-code_wbs, .js-code_edt').html('');
							$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
						}
					}
				});
			} 
			else
			{
				$('.js-code_wbs, .js-code_edt').html('');
				$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
			}
		})
		.on('change','.js-code_wbs',function()
		{
			id = $(this).find('option:selected').val();
			if (id != null)
			{
				$.each(generalSelectWBS,function(i,v)
				{
					if(id == v.id)
					{
						if(v.flagEDT != null)
						{
							$('.select_father_edt').removeClass('hidden').addClass('block');
							generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
						}
						else
						{
							$('.js-code_edt').html('');
							$('.select_father_edt').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('.js-code_edt').html('');
				$('.select_father_edt').removeClass('block').addClass('hidden');
			}
		});
	});
</script>
@endsection
