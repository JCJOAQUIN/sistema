@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	0;
		$taxes3	=	0;
		$docs	=	0;
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
			["Título y fecha:",	htmlentities($request->expenses->first()->title)." - ".Carbon\Carbon::parse($request->expenses->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",		App\Enterprise::find($request->idEnterprise)->name],
			["Dirección:",		App\Area::find($request->idArea)->name],
			["Departamento:",	App\Department::find($request->idDepartment)->name],
			["Proyecto:",		isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
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
		foreach ($request->expenses as $expense)
		{
			if (isset($request))
			{
				foreach ($request->expenses->first()->expensesDetail as $detail)
				{
					foreach ($detail->taxes as $tax)
					{
						$taxes3 += $tax->amount;
					}
				}
			}
			$expenseMethod		=	$expense->paymentMethod->method;
			$expenseReference	=	($expense->reference != "" ? htmlentities($expense->reference) : "---");
			$expenseCurrency	=	$expense->currency;
			$expenseTotal		=	$expense->total;
		}
		foreach ($request->expenses as $expense)
		{
			$expenseBank	=	"";
			$bankAlias		=	"";
			$cardNumber		=	"";
			$clabeInter		=	"";
			$accountNumber	=	"";
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() as $bank)
			{
				if ($expense->idEmployee == $bank->idEmployee)
				{
					$expenseBank	=	$bank->description;
					$bankAlias		=	$bank->alias!=null ? $bank->alias : '---';
					$cardNumber		=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
					$clabeInter		=	$bank->clabe!=null ? $bank->clabe : '---';
					$accountNumber	=	$bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable	=
		[
			"Forma de pago"		=>	$expenseMethod !="" ? $expenseMethod : "---",
			"Referencia"		=>	$expenseReference !="" ? $expenseReference : "---",
			"Tipo de moneda"	=>	$expenseCurrency !="" ? $expenseCurrency : "---",
			"Importe"			=>	$expenseTotal !="" ? "$ ".number_format($expenseTotal,2) : "---",
			"Banco"				=>	$expenseBank !="" ? $expenseBank : "---",
			"Alias"				=>	$bankAlias !="" ? $bankAlias : "---",
			"Número de tarjeta"	=>	$cardNumber !="" ? $cardNumber : "---",
			"CLABE"				=>	$clabeInter !="" ? $clabeInter : "---",
			"Número de cuenta"	=>	$accountNumber !="" ? $accountNumber : "---",
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
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$countConcept	=	1;
		$subtotalFinal	=	$ivaFinal	=	$totalFinal	=	0;
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
		
		foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
		{
			$subtotalFinal	+=	$expensesDetail->amount;
			$ivaFinal		+=	$expensesDetail->tax;
			$totalFinal		+=	$expensesDetail->sAmount;
			$taxes2 = 0;
			foreach ($expensesDetail->taxes as $tax)
			{
				$taxes2	+=	$tax->amount;
			}
			if (App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get()->count()>0)
			{
				$componentsExt	=	[];
				foreach (App\ExpensesDocuments::where('idExpensesDetail',$expensesDetail->idExpensesDetail)->get() as $doc)
				{
					$componentsExt[]	=
					[
						"kind"	=>	"components.labels.label",
						"label"	=>	Carbon\Carbon::parse($doc->date)->format('d-m-Y')
					];
					$componentsExt[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".asset('docs/expenses/'.$doc->path)."\""
					];
				}
			}
			else 
			{
				$componentsExt	=	["kind"	=>	"components.labels.label",	"label"	=>	"Sin documento"];
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept !="" ? $countConcept : "---"],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->concept !="" ? htmlentities($expensesDetail->concept) : "---"],
				],
				[
					"content"	=>	["label"	=>	isset($expensesDetail->account) ? $expensesDetail->account->account.' - '.$expensesDetail->account->description.' ('.$expensesDetail->account->content.")" : "---"],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->document !="" ? $expensesDetail->document : "---"],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->taxPayment==1 ? "Si" : "No"],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->amount !="" ? "$ ".number_format($expensesDetail->amount,2) : "---"],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->tax !="" ? "$ ".number_format($expensesDetail->tax,2) : "---"],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxes2,2)],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->sAmount !="" ? "$ ".number_format($expensesDetail->sAmount,2) : "---"],
				],
				[
					"content"	=>	$componentsExt
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
		@slot('attributeExBody')
			id="table"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
		@slot('attributeExBody')
			id="body"
		@endslot
	@endcomponent
	@php
		$subtotal	=	$totalFinal!=0 ? "$ ".number_format($subtotalFinal,2) : $subtotal	=	"";
		$iva		=	$totalFinal!=0 ? "$ ".number_format($ivaFinal,2): $iva	=	"";
		if (isset($request))
		{
			foreach ($request->expenses->first()->expensesDetail as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes	+=	$tax->amount;
				}
			}
		}
		if (isset($request->expenses))
		{
			foreach ($request->expenses as $expense)
			{
				$reintegro	=	"$ ".number_format($expense->reintegro,2);
				$reembolso	=	"$ ".number_format($expense->reembolso,2);
			}
		}
		else
		{
			$reintegro	=	"$ 0.00";
		}
		$total	=	$totalFinal!=0 ? "$ ".number_format($totalFinal,2) : $total	=	"";
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	$subtotal]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	$iva]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ". number_format($taxes,2)]]],
			["label"	=>	"Reintegro:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	$reintegro]]],
			["label"	=>	"Reembolso:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	$reembolso]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	$total]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12"]) DATOS DE REVISIÓN @endcomponent
	@php
		$reviewAccount	=	App\Account::find($request->accountR);
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")" : "Varias",
			"Nombre del Proyecto"		=>	$request->reviewedProject->proyectName,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-4"]) ETIQUETAS ASIGNADAS @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Concepto", "Clasificación de gasto", "Etiquetas"];
		foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
		{
			$labels	=	"";
			if ($expensesDetail->labels()->exists())
			{
				$counter	=	0;
				foreach ($expensesDetail->labels as $label)
				{
					$counter++;
					$labels	.=	$label->label->description.($counter<count($expensesDetail->labels) ? ", " : "");
				}
			}
			else
			{
				$labels	=	"Sin etiqueta";
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	htmlentities($expensesDetail->concept)],
				],
				[
					"content"	=>	["label"	=>	$expensesDetail->accountR->account." - ".$expensesDetail->accountR->description." (".$expensesDetail->accountR->content.")"],
				],
				[
					"content"	=>	["label"	=>	$labels],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
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
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
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
			$body		=	[];
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
					["value"	=>	""]
				]
			];
			foreach($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$wbsData	=	$r->wbs()->exists() ? $r->wbs->code_wbs : 'Sin datos';
				$edtData	=	$r->edt()->exists() ? $r->edt->code : 'Sin datos';
				$body	=
				[
					[
						"content"	=>
						[
							["label"	=>	$r->enterprise->name],
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
								"attributeEx"	=>	"type=\"hidden\" value=\"".htmlentities($r->commentaries)."\"",
								"classEx"		=>	"commentaries"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->direction->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->direction->name."\"",
								"classEx"		=>	"direction"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->department->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->department->name."\"",
								"classEx"		=>	"department"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->project->proyectName],
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
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->expense->concept],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->expense->concept."\"",
								"classEx"		=>	"concept"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.")"],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accounts->account.' '.$r->accounts->description.' ('.$r->accounts->content.")"."\"",
								"classEx"		=>	"account"
							]
						],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"attributeEx"	=>	"title=\"Ver datos\" data-target=\"#modalUpdate\" data-toggle=\"modal\"",
								"classEx"		=>	"view-data"
							]
						],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped", "classEx" => "mt-4"]) @endcomponent
	@endif
	@component('components.forms.form', ["attributeEx" => "id=\"formsearch\" method=\"POST\" action=\"".route('reclassification.update-expense',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor', ["classEx" => "mt-12"]) CLASIFICACIÓN ACTUAL @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	collect();
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->idEnterpriseR == $enterprise->id)
						{
							$optionsEnterprise	=	$optionsEnterprise->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$optionsEnterprise	=	$optionsEnterprise->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterprise])
					@slot('attributeEx')
						name="idEnterpriseR"
						multiple="multiple"
						data-validation="required"
						id="multiple-enterprisesR"
					@endslot
					@slot('classEx')
						js-enterprisesR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent	
				@php
					$optionsDirection	=	collect();
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->idAreaR == $area->id)
						{
							$optionsDirection	=	$optionsDirection->concat([["value" => $area->id, "description" => strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name, "selected" => "selected"]]);
						}
						else
						{
							$optionsDirection	=	$optionsDirection->concat([["value" => $area->id, "description" => strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name]]);
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
					$optionsDepartment	=	collect();
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->idDepartamentR == $department->id)
						{
							$optionsDepartment	=	$optionsDepartment->concat([["value" => $department->id, "description" => $department->name, "selected" => "selected"]]);
						}
						else
						{
							$optionsDepartment	=	$optionsDepartment->concat([["value" => $department->id, "description" => $department->name]]);
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
			<div class="col-span-2 select_father_wbs @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists()) block @else hidden @endif @else block @endif">
				@component('components.labels.label', ["label" => "WBS:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists())
					{
						$wbsData	=	App\CatCodeWBS::find($request->code_wbs);
						$options	=	$options->concat([["value" => $wbsData->id, "description" => $wbsData->code_wbs, "selected" => "selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_wbs removeselect"]) @endcomponent
			</div>
			<div class="col-span-2 select_father_edt @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists() && $request->wbs()->exists() && $request->wbs->codeEDT()->exists()) block @else hidden  @endif @else block @endif">
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
					["value"	=>	"Concepto"],
					["value"	=>	"Clasificación de gasto"],
					["value"	=>	"Importe"]
				]
			];
			foreach($request->expenses->first()->expensesDetail as $expensesDetail)
			{
				$optionsPoject	=	collect();
				$accountData	=	App\Account::find($expensesDetail->idAccountR);
				$optionsPoject	=	$optionsPoject->concat([["value" => $accountData->idAccAcc, "description" => strlen($accountData->account.' - '.$accountData->description) >= 35 ? substr(strip_tags($accountData->account.' - '.$accountData->description), 0, 35).'...' : $accountData->account.' - '.$accountData->description, "selected"	=>	"selected"]]);
				$body	=
				[
					[
						"content"	=>
						[
							["label"	=>	$countConcept],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"idExpensesDetail[]\" value=\"".$expensesDetail->idExpensesDetail."\"",
								"classEx"		=>	"idExpensesDetail"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	htmlentities($expensesDetail->concept)]
						],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.inputs.select",
								"options" 		=>	$optionsPoject,
								"attributeEx"	=>	"name=\"accountR[]\"",
								"classEx"		=>	"js-accountsR"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	"$ ".number_format($expensesDetail->sAmount,2)]
						],
					],
				];
				$countConcept++;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped", "classEx" => "mt-4", "attributeEx" => "id=\"table\"", "attributeExBody" => "id=\"body-classify\""]) @endcomponent
		@component('components.labels.label', ["classEx" => "mt-8"]) Comentarios (opcional): @endcomponent
		@component("components.inputs.text-area", ["attributeEx" => "name=\"commentaries\""]) @endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"send\" value=\"RECLASIFICAR\""]) RECLASIFICAR @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["classEx" => "load-actioner", "buttonElement" => "a", "variant" => "reset", "attributeEx" => "href=\"".$href."\"", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
	@component("components.modals.modal",["variant" => "large"])
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
			@component('components.tables.alwaysVisibleTable', ["variant" => "default", "modelHead" => $modelHead, "modelBody", $modelBody, "themeBody" => "striped"])@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Modificó: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-name\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-date\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-enterprise\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-direction\""])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-department\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-project\""]) @endcomponent
				</div>
				@if($request->code_wbs != "")
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
					@component('components.labels.label') Concepto: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-concept\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación de gasto: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-account\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Comentarios: @endcomponent
					@component('components.labels.label', ["attributeEx" => "name=\"view-commentaries\""]) @endcomponent
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
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	function validate()
	{
		$.validate(
		{
			form: '#formsearch',
			onError : function($form)
			{
				swal('','{{ Lang::get("messages.form_error") }}','error');
				return false;
			}
		});
	}
	$(document).ready(function()
	{
		validate();
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
