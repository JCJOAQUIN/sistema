@extends('layouts.child_module')
@section('data')
	@php
	$taxes = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable	=
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	(isset($request->refunds->first()->title) ? htmlentities($request->refunds->first()->title) : "-")." - ".($request->refunds->first()->datetitle!="" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->refunds->first()->datetitle)->format('d-m-Y') : "-")],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",		App\Enterprise::find($request->idEnterprise)->name],
			["Dirección:",		App\Area::find($request->idArea)->name],
			["Departamento:",	App\Department::find($request->idDepartment)->name],
			["Proyecto:",		isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto']
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
		DATOS DEL SOLICITANTE
	@endcomponent
	@php
		foreach ($request->refunds as $refund)
		{
			$paymentMetods		=	$refund->paymentMethod->method;
			$refundReference	=	$refund->reference!="" ? htmlentities($refund->reference) : "---";
			$refundCurrency		=	$refund->currency;
			$refundTotal		=	number_format($refund->total,2);
		}
		foreach ($request->refunds as $refund)
		{
			$bankDescription	=	"---";
			$bankAlias			=	"---";
			$bankCard			=	"---";
			$bankClabe			=	"---";
			$bankAccount		=	"---";
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$refund->idUsers)->get() as $bank)
			{
				if ($refund->idEmployee == $bank->idEmployee)
				{
					$bankDescription	=	$bank->description;
					$bankAlias			=	$bank->alias!=null ? $bank->alias : '---';
					$bankCard			=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
					$bankClabe			=	$bank->clabe!=null ? $bank->clabe : '---';
					$bankAccount		=	$bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable	=
		[
			"Forma de pago"		=>	$paymentMetods,
			"Referencia"		=>	$refundReference,
			"Tipo de moneda"	=>	$refundCurrency,
			"Importe"			=>	"$ ".$refundTotal,
			"Banco"				=>	$bankDescription,
			"Alias"				=>	$bankAlias,
			"Número de tarjeta"	=>	$bankCard,
			"CLABE"				=>	$bankClabe,
			"Número de cuenta"	=>	$bankAccount,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent

	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		RELACIÓN DE DOCUMENTOS
	@endcomponent
	@php
		$subtotalFinal	=	$ivaFinal = $totalFinal = 0;
		$countConcept	=	1;
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$modelHead		=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Concepto"],
				["value"	=>	"Clasificación del gasto"],
				["value"	=>	"Tipo de Documento/No. Factura"],
				["value"	=>	"Fiscal"],
				["value"	=>	"Subtotal"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Importe"],
				["value"	=>	"Documento(s)"]
			]
		];
		foreach (App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
		{
			$subtotalFinal	+=	$refundDetail->amount;
			$ivaFinal		+=	$refundDetail->tax;
			$totalFinal		+=	$refundDetail->sAmount;
			$taxes2 = 0;
			foreach ($refundDetail->taxes as $tax)
			{
				$taxes2	+=	$tax->amount;
			}
			if (App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
			{
				$docsComponents	=	[];
				foreach (App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
				{
					$docsComponents[]	=
					[
						"kind"			=>	"components.labels.label",
						"label"			=>	Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y')
					];
					$docsComponents[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"buttonElement"	=>	"a",
						"label"			=>	"<span class=\"icon-pdf\"></span>",
						"attributeEx"	=>	"target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/refounds/'.$doc->path)."\""
					];
				}
			}
			else
			{
				$docsComponents[]	=	["kind"	=>	"components.labels.label", "label"	=>	"---"];
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	htmlentities($refundDetail->concept)]
				],
				[
					"content"	=>	["label"	=>	isset($refundDetail->account) ? $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.")" : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->document!="" ? $refundDetail->document : "---"]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->taxPayment==1 ? "Sí" : "No"]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($refundDetail->amount,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($refundDetail->tax,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($taxes2,2)]
				],
				[
					"content"	=>	["label"	=>	"$".number_format($refundDetail->sAmount,2)]
				],
				[
					"content"	=>	$docsComponents
				],
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" =>$modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeExBody')
			id="body"
		@endslot
	@endcomponent
	@php
		if ($totalFinal!=0)
		{
			$subtotal	=	number_format($subtotalFinal,2);
			$ivaFinal	=	number_format($ivaFinal,2);
			$finalTotal	=	number_format($totalFinal,2);
		}
		if (isset($request))
		{
			foreach ($request->refunds->first()->refundDetail as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes	+=	$tax->amount;
				}
			}
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$subtotal]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$ivaFinal]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$finalTotal]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$reviewAccount		=	App\Account::find($request->accountR);
		$labelDescription	=	"";
		foreach ($request->labels as $label)
		{
			$labelDescription	.=	$label->description.", ";
		}
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	isset($request->reviewedDirection->name) ? $request->reviewedDirection->name : "",
			"Nombre del Departamento"	=>	isset(App\Department::find($request->idDepartamentR)->name) ? App\Department::find($request->idDepartamentR)->name : "",
			"Clasificación del gasto"	=>	isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "Varias",
			"Nombre del Proyecto"		=>	isset($request->reviewedProject->proyectName) ? $request->reviewedProject->proyectName : "",
			"Etiquetas"					=>	$labelDescription,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		ETIQUETAS Y RECLASIFICACIÓN ASIGNADA
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		["Concepto","Clasificación de gasto","Etiquetas"];
		foreach (App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
		{
			$labelComponent	=	"---";
			if ($refundDetail->labels()->exists())
			{
				$labelComponent	=	"";
				$counter	=	0;
				foreach ($refundDetail->labels as $label)
				{
					$counter++;
					$labelComponent	.=	$label->label->description.($counter<count($refundDetail->labels) ? ", " : "");
				}
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	htmlentities($refundDetail->concept)]
				],
				[
					"content"	=>	["label"	=>	$refundDetail->accountR->account." - ".$refundDetail->accountR->description." (".$refundDetail->accountR->content.")"]
				],
				[
					"content"	=>	["label"	=>	$labelComponent]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" =>$modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
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
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment)
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@if($request->request_has_reclassification()->exists())
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			CLASIFICACIÓN DE GASTO ANTERIOR
		@endcomponent
		@php
			$modelHead	=	[];
			$bodyy		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Empresa"],
					["value"	=>	"Dirección"],
					["value"	=>	"Departamento"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Concepto"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Acción"]
				]
			];
			foreach ($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$direction	=	$r->direction()->exists() ? $r->direction->name : "";
				$department	=	$r->department()->exists() ? $r->department->name : "";
				$project	=	$r->project()->exists() ? $r->project->proyectName : "";
				$refund		=	$r->refund()->exists() ? $r->refund->concept : "";
				$body		=
				[
					[
						"content"	=>
						[
							["label"	=>	$r->enterprise->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->enterprise->name."\"",
								"classEx"		=>	"hidden enterprise"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->user->name.' '.$r->user->last_name.' '.$r->user->scnd_last_name."\"",
								"classEx"		=>	"hidden name"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$r->date)->format('d-m-Y')."\"",
								"classEx"		=>	"hidden date"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".htmlentities($r->commentaries)."\"",
								"classEx"		=>	"hidden commentaries"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$direction],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$direction."\"",
								"classEx"		=>	"hidden direction"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$department],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$department."\"",
								"classEx"		=>	"hidden department"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$project],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$project."\"",
								"classEx"		=>	"hidden project"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".($r->wbs()->exists() ? $r->wbs->code_wbs : 'Sin datos')."\"",
								"classEx"		=>	"hidden wbs"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".($r->edt()->exists() ? $r->edt->code : 'Sin datos')."\"",
								"classEx"		=>	"hidden edt"
							],
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$refund],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$refund."\"",
								"classEx"		=>	"hidden concept"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.')'],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.')'."\"",
								"classEx"		=>	"hidden account"
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class=\"icon-search\"></span>",
								"attributeEx"	=>	"type=\"button\" data-target=\"#modalUpdate\" data-toggle=\"modal\"",
								"classEx"		=>	"view-data"
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
		@endcomponent
	@endif
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"formsearch\" action=\"".route('reclassification.update-refund',$request->folio)."\"", "methodEx" => "PUT"])
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
					$optionEnterprise	=	[];
					foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if($request->idEnterpriseR == $enterprise->id)
						{
							$optionEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								"selected"		=>	"selected"
							];
						}
						else 
						{
							$optionEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionEnterprise])
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
					$optionDirection	=	[];
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->idAreaR == $area->id)
						{
							$optionDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionDirection])
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
					$optionDepartment	=	[];
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->idDepartamentR == $department->id)
						{
							$optionDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionDepartment])
					@slot('attributeEx')
						id="multiple-departmentsR"
						name="idDepartamentR"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-departmentsR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$options	=	collect();
					if (isset($request->idProjectR) && $request->idProjectR !="")
					{
						$options	=	$options->concat([["value"	=>	$request->requestProject->idproyect,	"description"	=>	$request->requestProject->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
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
			<div class="col-span-2 select_father_wbs @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists()) block @else hidden @endif @else block @endif">
				@component('components.labels.label', ["label" => "WBS"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->code_wbs != "")
					{
						$wbsData	=	App\CatCodeWBS::find($request->code_wbs);
						$options	=	$options->concat([["value"	=>	$wbsData->id, "description"	=>	$wbsData->code_wbs, "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_wbs removeselect"]) @endcomponent
			</div>
			<div class="col-span-2 select_father_edt @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists() && $request->wbs()->exists() && $request->wbs->codeEDT()->exists()) block @else hidden @endif @else block @endif">
				@component('components.labels.label', ["label" => "EDT"]) @endcomponent
				@php
					$options	=	collect();
					$edtData	=	App\CatCodeEDT::find($request->code_edt);
					if (isset($request) && $request->code_edt != "")
					{
						$options	=	$options->concat([["value"	=>	$edtData->id, "description"	=>	$edtData->code.' ('.$edtData->description.')', "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_edt removeselect"]) @endcomponent
			</div>
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Concepto"],
					["value"	=>	"Clasificación de gasto"],
					["value"	=>	"Importe"]
				]
			];
			$countConcept	=	1;
			foreach ($request->refunds->first()->refundDetail as $refundDetail)
			{
				$options	=	collect();
				if ($refundDetail->idAccountR != "")
				{
					$options	=	$options->concat([["value"	=>	$refundDetail->account->idAccAcc,	"description"	=>	$refundDetail->account->account." - ".$refundDetail->account->description." (".$refundDetail->account->content.")",	"selected"	=>	"selected"]]);
				}
				$body	=
				[
					[
						"content"	=>
						[
							["label"			=>	$countConcept],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"idRefundDetail[]\" value=\"".$refundDetail->idRefundDetail."\"",
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	htmlentities($refundDetail->concept)]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.select",
								"options"		=>	$options,
								"attributeEx"	=>	"multiple=\"multiple\" name=\"accountR[]\" data-validation=\"required\"",
								"classEx"		=>	"js-accountsR"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	"$ ".number_format($refundDetail->sAmount,2)]
						]
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
			@slot('attributeExBody')
				id="body-classify"
			@endslot
		@endcomponent
		@component('components.labels.label')
			@slot('classEx')
				mt-8
			@endslot
			Comentarios (opcional)
		@endcomponent
		@component('components.inputs.text-area')
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
	@component("components.modals.modal")
		@slot('id')
			modalUpdate
		@endslot
		@slot('classEx')
			modal fade
		@endslot
		@slot('modalBody')
			@php
				$modelHead	=	[];
				$modelHead	=	["INFORMACIÓN"];
				$modelBody	=	[];
			@endphp
			@component("components.tables.alwaysVisibleTable", ["variant" => "default", "modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
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
				@if($request->code_wbs != "")
					<div class="col-span-2">
						@component('components.labels.label', ["label" => "WBS"]) @endcomponent
						@component('components.labels.label', ["attributeEx" => "name=\"view-wbs\""]) @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label', ["label" => "EDT"]) @endcomponent
						@component('components.labels.label', ["attributeEx" => "name=\"view-edt\""]) @endcomponent
					</div>
				@endif
				<div class="col-span-2">
					@component('components.labels.label') Concepto: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-concept"
						@endslot
					@endcomponent
				</div>
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
<script>
	$.validate(
	{
		form: '#container-alta',
		onSuccess : function($form)
		{
			if($('input[name="status"]').is(':checked'))
			{
				swal('Cargando',{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
				});
				return true;
			}
			else
			{
				swal('', 'Debe seleccionar al menos un estado', 'error');
				return false;
			}
		}
	});
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-projects', 'model': 14});
		generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 22});
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
		$(document).on('click','.view-data',function()
		{
			$('[name="view-name"]').text($(this).parent('div').parent('div').parent('div').find('.name').val());
			$('[name="view-date"]').text($(this).parent('div').parent('div').parent('div').find('.date').val());
			$('[name="view-enterprise"]').text($(this).parent('div').parent('div').parent('div').find('.enterprise').val());
			$('[name="view-direction"]').text($(this).parent('div').parent('div').parent('div').find('.direction').val());
			$('[name="view-department"]').text($(this).parent('div').parent('div').parent('div').find('.department').val());
			$('[name="view-project"]').text($(this).parent('div').parent('div').parent('div').find('.project').val());
			$('[name="view-concept"]').text($(this).parent('div').parent('div').parent('div').find('.concept').val());
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
							generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 22});
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
		.on('change','[name="code_wbs"]',function()
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
		})
	});
</script>

@endsection
