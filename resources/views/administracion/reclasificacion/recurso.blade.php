@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable		=
		[
			["Folio",			$request->folio],
			["Título y fecha",	htmlentities($request->resource->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y')],
			["Solicitante",		$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa",			App\Enterprise::find($request->idEnterprise)->name],
			["Dirección",		App\Area::find($request->idArea)->name],
			["Departamento",	App\Department::find($request->idDepartment)->name],
			["Proyecto",		isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto']
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
		$bankName		=	"---";
		$bankAlias		=	"---";
		$bankCard		=	"---";
		$bankClabe		=	"---";
		$bankAccount	=	"---";
		foreach ($request->resource as $resource)
		{
			$resourcePayment	=	$resource->paymentMethod->method;
			$resourceReference	=	($resource->reference != "" ? htmlentities($resource->reference) : "---");
			$resourceCurrency	=	$resource->currency;
			$resourceTotal		=	"$ ".number_format($resource->total,2);
		}
		foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->resource->first()->idUsers)->get() as $bank)
		{
			if ($resource->idEmployee == $bank->idEmployee)
			{
				$bankName		=	$bank->description;
				$bankAlias		=	$bank->alias!=null ? $bank->alias : '---';
				$bankCard		=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
				$bankClabe		=	$bank->clabe!=null ? $bank->clabe : '---';
				$bankAccount	=	$bank->account!=null ? $bank->account : '---';
			}
		}
		$modelTable	=
		[
			"Forma de pago"		=>	$resourcePayment !="" ? $resourcePayment : "---",
			"Referencia"		=>	$resourceReference !="" ? $resourceReference : "---",
			"Tipo de moneda"	=>	$resourceCurrency !="" ? $resourceCurrency : "---",
			"Importe"			=>	$resourceTotal !="" ? $resourceTotal : "---",
			"Banco"				=>	$bankName,
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
		RELACIÓN DE DOCUMENTOS SOLICITADOS
	@endcomponent
	@php
		$subtotalFinal	=	$ivaFinal	=	$totalFinal	=	0;
		$countConcept	=	1;
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$modelHead		=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Concepto"],
				["value"	=>	"Clasificación de gasto"],
				["value"	=>	"Importe"]
			]
		];
		foreach ($request->resource->first()->resourceDetail as $resourceDetail)
		{
			$totalFinal	+=	$resourceDetail->amount;
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	htmlentities($resourceDetail->concept)]
				],
				[
					"content"	=>	["label"	=>	$resourceDetail->accounts->account.' '.$resourceDetail->accounts->description]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($resourceDetail->amount,2)]
				]
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
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
	@php
		if ($totalFinal!=0)
		{
			$finalTotal	=	number_format($totalFinal,2);
		}
		$modelTable	=
		[
			["label"	=>	"TOTAL", "inputsEx"	=>	[["kind"	=>	"components.labels.label",	"label"	=>	$finalTotal, "attributeEx"	=>	"name=\"total\"", "classEx"	=>	" py-2 total"]]]
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
		$labelsDescription	=	"";
		foreach ($request->labels as $label)
		{
			$labelsDescription	.=	$label->description.", ";
		}
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name."  ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description : "Varias",
			"Nombre del Proyecto"		=>	isset($request->reviewedProject->proyectName) ? $request->reviewedProject->proyectName : 'No se seleccionó proyecto',
			"Etiquetas"					=>	$labelsDescription,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment)
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		RELACIÓN DE DOCUMENTOS APROBADOS
	@endcomponent
	@php
		$subtotalFinal	=	$ivaFinal	=	$totalFinal	=	0;
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$modelHead		=	["Concepto", "Clasificación de gasto", "Importe"];
		foreach ($request->resource->first()->resourceDetail as $resourceDetail)
		{
			$totalFinal	+=	$resourceDetail->amount;
			$body	=
			[
				[
					"content"	=>	["label"	=>	htmlentities($resourceDetail->concept)]
				],
				[
					"content"	=>	["label"	=>	$resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($resourceDetail->amount,2)]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])@endcomponent
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
			foreach ($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$wbsCode	=	$r->wbs()->exists() ? $r->wbs->code_wbs : 'Sin datos';
				$edtsCode	=	$r->edt()->exists() ? $r->edt->code : 'Sin datos';
				$body	=
				[
					[
						"content"	=>
						[
							["label"			=>	$r->enterprise->name],
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
							["label"			=>	$r->direction->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->direction->name."\"",
								"classEx"		=>	"hidden direction"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->department->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->department->name."\"",
								"classEx"		=>	"hidden department"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->project->proyectName],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->project->proyectName."\"",
								"classEx"		=>	"hidden project"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$wbsCode."\"",
								"classEx"		=>	"hidden wbs"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$edtsCode."\"",
								"classEx"		=>	"hidden edt"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	htmlentities($r->resource->concept)],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".htmlentities($r->resource->concept)."\"",
								"classEx"		=>	"hidden concept"
							]
						]
					],
					[
						"content"	=>
						[
							["label"			=>	$r->accounts->account.' '.$r->accounts->description],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"value=\"".$r->accounts->account.' '.$r->accounts->description."\"",
								"classEx"		=>	"hidden account"
							]
						]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"label"			=>	"<span class='icon-search'></span>",
							"attributeEx"	=>	"type=\"button\" data-target=\"#modalUpdate\" data-toggle=\"modal\" title=\"ver datos\"",
							"classEx"		=>	"view-data"
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"formsearch\" action=\"".route('reclassification.update-resource',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			RECLASIFICACIÓN
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
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35).'...' : $area->name,
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
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,];
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
					$options	=	collect();
					if (isset($request->idProjectR) && $request->idProjectR !="")
					{
						$options	=	$options->concat([["value"	=>	$request->reviewedProject->idproyect,	"description"	=>	$request->reviewedProject->proyectName,	"selected"	=>	"selected"]]);
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
				@component('components.labels.label', ["label" => "WBS:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->code_wbs !="")
					{
						$options	=	$options->concat([["value"	=>	$request->wbs->id,	"description"	=>	$request->wbs->code_wbs, "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_wbs removeselect"]) @endcomponent
			</div>
			<div class="col-span-2 select_father_edt @if(isset($request)) @if($request->idProjectR != '' && $request->reviewedProject->codeWBS()->exists() && $request->wbs()->exists() && $request->wbs->codeEDT()->exists()) block @else hidden  @endif @else block @endif">
				@component('components.labels.label', ["label" => "EDT:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($request) &&$request->code_edt != "")
					{
						$options	=	$options->concat([["value"	=>	$request->edt->id,	"description"	=>	$request->edt->code.' ('.$request->edt->description.')', "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-code_edt removeselect"]) @endcomponent
			</div>
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
					["value"	=>	"Clasificación de gasto"],
					["value"	=>	"Importe"]
				]
			];
			foreach ($request->resource->first()->resourceDetail as $resourceDetail)
			{
				$totalFinal	+=	$resourceDetail->amount;
				$options	=	collect();
				if ($resourceDetail->idAccAccR != "")
				{
					$options	=	$options->concat([["value"	=>	$resourceDetail->accounts->idAccAcc,	"description"	=>	$resourceDetail->accounts->account." - ".$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")",	"selected"	=>	"selected"]]);
				}
				$body	=
				[
					[
						"content"	=>
						[
							["label"			=>	$countConcept],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"idresourcedetail[]\" value=\"".$resourceDetail->idresourcedetail."\""
							]
						]
					],
					[
						"content"	=>	["label"	=>	$resourceDetail->concept]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.inputs.select",
							"options"		=>	$options,
							"attributeEx"	=>	"multiple=\"multiple\" name=\"accountR[]\" data-validation=\"required\"",
							"classEx"		=>	"js-accountsR"
						]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($resourceDetail->amount,2)]
					],
				];
				$countConcept++;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeExBody')
				id="body-classify" class="text-center"
			@endslot
		@endcomponent
		@component('components.labels.label',["classEx" => "mt-8"]) Comentarios (opcional): @endcomponent
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
	@component('components.modals.modal')
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
			@component('components.tables.alwaysVisibleTable', ["variant" => "default", "modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])@endcomponent
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
						@component('components.labels.label', ["attributeEx"	=>	"name=\"view-wbs\""]) @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label', ["label" => "EDT"]) @endcomponent
						@component('components.labels.label', ["attributeEx"	=>	"name=\"view-edt\""]) @endcomponent
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
