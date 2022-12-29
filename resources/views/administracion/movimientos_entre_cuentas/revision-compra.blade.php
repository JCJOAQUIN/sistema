@extends('layouts.child_module')
@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	</div>
	@php
		$requestUser			=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser			=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$requestAccountOrigin	=	isset($request->purchaseEnterprise->first()->idAccAccOrigin) ? App\Account::find($request->purchaseEnterprise->first()->idAccAccOrigin) : "";
		$requestAccount 		=	isset($request->purchaseEnterprise->first()->idAccAccDestiny) ? App\Account::find($request->purchaseEnterprise->first()->idAccAccDestiny) : "";
		$modelTable				=
		[
			["Folio:",								$request->folio ],
			["Título y fecha:",						(isset($request->purchaseEnterprise->first()->title) ? htmlentities($request->purchaseEnterprise->first()->title) : "-")." - ".(isset($request->purchaseEnterprise->first()->datetitle) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->datetitle)->format('d-m-Y') : "-")],
			["Número de Orden:",					isset($request->purchaseEnterprise->first()->numberOrder) ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						isset($request->purchaseEnterprise->first()->idEnterpriseOrigin) ? App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOrigin)->name : "---"],
			["Dirección Origen:",					isset($request->purchaseEnterprise->first()->idAreaOrigin) ? App\Area::find($request->purchaseEnterprise->first()->idAreaOrigin)->name : "---"],
			["Departamento Origen:",				isset($request->purchaseEnterprise->first()->idDepartamentOrigin) ? App\Department::find($request->purchaseEnterprise->first()->idDepartamentOrigin)->name : "---"],
			["Clasificación del Gasto Origen:",		$requestAccountOrigin!="" ? $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")" : "---"],
			["Proyecto Origen:",					isset($request->purchaseEnterprise->first()->idProjectOrigin) ? App\Project::find($request->purchaseEnterprise->first()->idProjectOrigin)->proyectName : "---"],
			["Empresa Destino:",					isset($request->purchaseEnterprise->first()->idEnterpriseDestiny) ? App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestiny)->name : "---"],
			["Clasificación del Gasto Destino:",	$requestAccount!="" ? $requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")" : "---"],
			["Proyecto Destino:",					isset($request->purchaseEnterprise->first()->idProjectDestiny) ? App\Project::find($request->purchaseEnterprise->first()->idProjectDestiny)->proyectName : "---"],
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
				["value"	=>	"Descripci&oacute;n"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"],
			]
		];
		$countConcept = 1;
		if (isset($request->purchaseEnterprise->first()->detailPurchaseEnterprise))
		{
			foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
			{
				$taxesConcept	=	0;
				foreach ($detail->taxes as $tax)
				{
					$taxesConcept+=$tax->amount;
				}
				$retentionConcept=0;
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
						"content"	=>	["label"	=>	htmlentities($detail->unit)]
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
				$modelTable	=
				[
					["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",")]]],
					["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
					["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
					["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",")]]],
					["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",")]]],
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
			$bankDescription	=	$request->purchaseEnterprise->first()->banks->bank->description;
			$bankAlias			=	$request->purchaseEnterprise->first()->banks->alias;
			$bankAccount		=	$request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
			$bankbClave			=	$request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
			$bankBranch			=	$request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
			$bankReference		=	$request->purchaseEnterprise->first()->banks->reference != "" ? $request->purchaseEnterprise->first()->banks->reference : "---";
		}
		else
		{
			$bankDescription	=	"---";
			$bankAlias			=	"---";
			$bankAccount		=	"---";
			$bankbClave			=	"---";
			$bankBranch			=	"---";
			$bankReference		=	"---";
		}
		
		$modelTable	=
		[
			"Tipo de moneda"	=>	isset($request->purchaseEnterprise->first()->typeCurrency) ? $request->purchaseEnterprise->first()->typeCurrency : "---",
			"Fecha de pago"		=>	isset($request->purchaseEnterprise->first()->paymentDate) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y') : "---",
			"Forma de pago"		=>	isset($request->purchaseEnterprise->first()->paymentMethod->method) ? $request->purchaseEnterprise->first()->paymentMethod->method : "---",
			"Banco"				=>	$bankDescription,
			"Alias"				=>	$bankAlias,
			"Cuenta"			=>	$bankAccount,
			"Clabe"				=>	$bankbClave,
			"Sucursal"			=>	$bankBranch,
			"Referencia"		=>	$bankReference,
			"Importe a pagar"	=>	isset($request->purchaseEnterprise->first()->amount) ? "$ ".number_format($request->purchaseEnterprise->first()->amount,2) : "---",
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
		@slot('classEx')
			employee-details
		@endslot
	@endcomponent
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
		if (isset($request->purchaseEnterprise->first()->documentsPurchase) && count($request->purchaseEnterprise->first()->documentsPurchase)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->purchaseEnterprise->first()->documentsPurchase as $doc)
			{
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"buttonElement"	=>	"a",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')]
					],
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
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('movements-accounts.purchase.updateReview', $request->folio)."\"", "methodEx" => "PUT"])
		<div class="form-container mt-12">
			<div class="flex justify-center">
				@component('components.labels.label')
					¿Desea aprobar ó rechazar la solicitud?
				@endcomponent
			</div>
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status"
					id="aprobar"
					value="4"
				@endslot
				@slot('attributeExButtonTwo')
					id="rechazar"
					name="status"
					value="6"
				@endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				ASIGNACIÓN DE ETIQUETAS
			@endcomponent
			<div class="form-container">
				@php
					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=	["", "Cantidad", "Descripción"];
					if (isset($request->purchaseEnterprise->first()->detailPurchaseEnterprise))
					{
						foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
						{
							$options=collect();
							$body	=
							[
								"classEx"	=>	"tr_body",
								[
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.checkbox",
											"attributeEx" 	=> "id=\"id_article_$detail->idPurchaseEnterpriseDetail\" name=\"add-article_$detail->idPurchaseEnterpriseDetail\" value=\"1\"",
											"classEx"		=> "add-article d-none",
											"classExLabel"	=> "check-small request-validate",
											"label"			=> '<span class="icon-check"></span>',
										]
									],
								],
								[
									"content"	=>	["label"	=>	$detail->quantity.' '.htmlentities($detail->unit)],
								],
								[
									"content"	=>
									[
										["label"	=>	htmlentities($detail->description)],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".$detail->idPurchaseEnterpriseDetail."\"",
											"classEx"		=>	"idPurchaseEnterpriseDetailOld"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".$detail->quantity.' '.htmlentities($detail->unit)."\"",
											"classEx"		=>	"quantityOld"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".htmlentities($detail->description)."\"",
											"classEx"		=>	"conceptOld"
										],
									]
								],
							];
							$modelBody[]	=	$body;
						}
					}
					$body	=
					[
						[
							"content"	=>	[],
						],
						[
							"content"	=>
							[
								[
									"kind"		=>	"components.labels.label",
									"label"		=>	"Etiquetas"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.inputs.select",
									"attributeEx"	=>	"multiple=\"multiple\" name=\"idLabelsReview[]\"",
									"classEx"		=>	"js-labelsR",
								]
							]
						],
					];
					$modelBody[]	=	$body;
				@endphp
				@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
					@slot('attributeEx')
						id="table"
					@endslot
					@slot('classEx')
						table
					@endslot
					@slot('attributeExBody')
						id="tbody-concepts"
					@endslot
				@endcomponent
				<div class="text-center">@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" title=\"Agregar\"", "classEx" => "add-label", "label" => "Agregar"]) @endcomponent</div>
			</div>
			<div class="container-blocks view-label hidden" id="container-data">
				<div class="search-table-center">
					@component('components.containers.container-form')
						<div class="col-span-2">
							@component('components.labels.label') Concepto: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="hidden"
								@endslot
								@slot('classEx')
									idPurchaseEnterpriseDetailNew
								@endslot
							@endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="hidden"
								@endslot
								@slot('classEx')
									quantityNew
								@endslot
							@endcomponent
							@component('components.labels.label')
								@slot('classEx')
									conceptNew
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Etiquetas: @endcomponent
							@component('components.inputs.select')
								@slot('attributeEx')
									multiple="multiple" name="idLabelsReview[]"
								@endslot
								@slot('classEx')
									js-labelsR
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('attributeEx')
									type="button"
								@endslot
								@slot('classEx')
									approve-label
								@endslot
								<span class="icon-plus"></span>
								<span>Agregar</span>
							@endcomponent
						</div>
					@endcomponent
				</div>
			</div>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				ETIQUETAS ASIGNADAS
			@endcomponent
			<div class="form-container">
				@php
					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=	["Concepto", "Etiquetas", "Acción"];
					$modelBody[]	=	$body;
				@endphp
				@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
					@slot('attributeExBody')
						id="tbody-conceptsNew"
					@endslot
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent
			</div>
			<span id="labelsAssign"> </span>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE ORIGEN
			@endcomponent
			<div class="form-container">
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Empresa: @endcomponent
						@php
							$enterpriseOrigin	=	[];
							foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
							{
								if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
								{
									$enterpriseOrigin[]	=
									[
										"value"			=>	"$enterprise->id",
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
										"selected"		=>	"selected"
									];
								} else
								{
									$enterpriseOrigin[]	=
									[
										"value"			=>	"$enterprise->id",
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									];
								}
							}
						@endphp
						@component('components.inputs.select', ["options"	=>	$enterpriseOrigin])
							@slot('attributeEx')
								name="enterpriseid_origin" multiple="multiple"" data-validation="required"
							@endslot
							@slot('classEx')
								js-enterprises-origin removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Dirección: @endcomponent
						@php
							$areaOrigin	=	[];
							foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
							{
								if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAreaOrigin == $area->id)
								{
									$areaOrigin[]	=	["value"	=>	$area->id,"description"	=>	$area->name,"selected"	=>	"selected"];
								}
								else
								{
									$areaOrigin[]	=	["value"	=>	$area->id,"description"	=>	$area->name];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $areaOrigin])
							@slot('attributeEx')
								multiple="multiple" name="areaid_origin" data-validation="required"
							@endslot
							@slot('classEx')
								js-areas-origin removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Departamento: @endcomponent
						@php
							$departmentOrigin	= [];
							foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
							{
								if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idDepartamentOrigin == $department->id)
								{
									$departmentOrigin[]	=	["value"	=>	$department->id,"description"	=>	$department->name,"selected"	=>	"selected"];
								}
								else
								{
									$departmentOrigin[]	=	["value"	=>	$department->id,"description"	=>	$department->name];
								}
							}
						@endphp
						@component('components.inputs.select', ["options"	=>	$departmentOrigin])
							@slot('attributeEx')
								multiple="multiple" name="departmentid_origin" data-validation="required"
							@endslot
							@slot('classEx')
								js-departments-origin removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						@php
							$options	=	collect();
							if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAccAccOrigin !="")
							{
								$options	=	$options->concat([["value"	=>	$request->purchaseEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->purchaseEnterprise->first()->accountOrigin->account." - ".$request->purchaseEnterprise->first()->accountOrigin->description." (".$request->purchaseEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								multiple="multiple" name="accountid_origin" data-validation="required"
							@endslot
							@slot('classEx')
								js-accounts-origin removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Proyecto/Contrato: @endcomponent
						@php
							$options	=	collect();
							if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idProjectOrigin != "")
							{
								$options	=	$options->concat([["value"	=>	$request->purchaseEnterprise->first()->projectOrigin->idproyect,	"description"	=>	$request->purchaseEnterprise->first()->projectOrigin->proyectName,	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								name="projectid_origin" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-projects-origin removeselect
							@endslot
						@endcomponent
					</div>
				@endcomponent
			</div>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE DESTINO
			@endcomponent
			<div class="form-container">
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Empresa: @endcomponent
						@php
							$enterpriseDestination	=	[];
							foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
							{
								if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idEnterpriseDestiny == $enterprise->id)
								{
									$enterpriseDestination[]	=
									[
										"value"			=>	$enterprise->id,
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
										"selected"		=>	"selected"
									];
								}
								else
								{
									$enterpriseDestination[]	=
									[
										"value"			=>	$enterprise->id,
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $enterpriseDestination])
							@slot('attributeEx')
								name="enterpriseid_destination" multiple="multiple" border: 0px;" data-validation="required"
							@endslot
							@slot('classEx')
								js-enterprises-destination removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						@php
							$options	=	collect();
							if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idAccAccDestiny !="")
							{
								$options	=	$options->concat([["value"	=>	$request->purchaseEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->purchaseEnterprise->first()->accountDestiny->account." - ".$request->purchaseEnterprise->first()->accountDestiny->description." (".$request->purchaseEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								multiple="multiple" name="accountid_destination" data-validation="required"
							@endslot
							@slot('classEx')
								js-accounts-destination removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Proyecto/Contrato: @endcomponent
						@php
							$options	=	collect();
							if ($request->purchaseEnterprise()->exists() && $request->purchaseEnterprise->first()->idProjectDestiny != "")
							{
								$options	=	$options->concat([["value"	=>	$request->purchaseEnterprise->first()->projectDestiny->idproyect,	"description"	=>	$request->purchaseEnterprise->first()->projectDestiny->proyectName,	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								name="projectid_destination" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-projects-destination removeselect
							@endslot
						@endcomponent
					</div>
				@endcomponent
			</div>
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					name="checkCommentA"
				@endslot
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					name="checkCommentR"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "classEx" => "enviar"]) ENVIAR SOLICITUD @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner enviar btn"]) REGRESAR @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					if($('input#aprobar').is(':checked'))
					{
						enterprise	= $('#multiple-enterprisesR').val();
						area		= $('#multiple-areasR').val();
						department	= $('#multiple-departmentsR').val();
						account		= $('#multiple-accountsR').val();
						if(enterprise == '' || area == '' || department == '' || account == '')
						{
							swal('', 'Todos los campos son requeridos', 'error');
							return false;
						}
						else if (($('#tbody-concepts .tr_body').length-1) != $('#tbody-conceptsNew .tr_assign').length-1) 
						{
							swal('', 'Tiene conceptos sin asignar', 'error');
							return false;
						}
						else
						{
							swal("Cargando",{
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							return true;
						}
					}
					else
					{
						swal("Cargando",{
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
						return true;
					}
				}
				else
				{
					swal('', 'Debe seleccionar al menos un estado', 'error');
					return false;
				}
			}
		});
		generalSelect({'selector': '.js-projects-origin', 'model': 21});
		generalSelect({'selector': '.js-projects-destination', 'model': 21});
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 18});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 32});
		count = 0;
		$(document).on('change','input[name="status"]',function()
		{
			if ($('input[name="status"]:checked').val() == "4") 
			{
				$("#rechaza").slideUp("slow");
				$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
				generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : 100});
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
			}
			else if ($('input[name="status"]:checked').val() == "6") 
			{
				$("#aceptar").slideUp("slow");
				$("#rechaza").slideToggle("slow").addClass('form-container').css('display','block');
			}
		})
		.on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
		.on('click','.add-label',function(){ 
			errorSwalElements=true;
			$('.add-article').each(function(){
				if($(this).is(':checked')) {
					errorSwalElements			= false;
					tr							= $(this).parents('.tr');
					concept  					= tr.find('.conceptOld').val();
					quantity 					= tr.find('.quantityOld').val();
					idPurchaseEnterpriseDetail 	= tr.find('.idPurchaseEnterpriseDetailOld').val();

					$(this).prop( "checked",false); 
					$(this).parents('.tr').hide();

					@php
						$modelHead	=	[];
						$body		=	[];
						$modelBody	=	[];
						$modelHead	=
						[
							["value"	=>	"Concepto",		"show"	=>	"true"],
							["value"	=>	"Etiquetas",	"show"	=>	"true"],
							["value"	=>	""],
						];
						$body	=
						[
							"classEx"	=>	"tr_assign",
							[
								"content"	=>
								[
									[
										"kind"		=>	"components.labels.label",
										"classEx"	=>	"conceptTxt",
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"",
										"classEx"		=>	"conceptLabel"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\"",
										"classEx"		=>	"quantityLabel"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"labelsAssign"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"t_idPurchaseEnterpriseDetail[]\"",
										"classEx"		=>	"idPurchaseEnterpriseDetailLabel"
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"red",
										"label"			=>	"<span class=\"icon-x delete-span\"></span>",
										"attributeEx"	=>	"type=\"button\"",
										"classEx"		=>	"delete-item"
									],
								]
							],
						];
						$modelBody[]	=	$body;
						$table = view('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $modelBody,"noHead"    => true])->render();
					@endphp
					table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row		=	$(table);
					row.find('.conceptTxt').text(concept);
					row.find('.conceptLabel').val(concept);
					row.find('.quantityLabel').val(quantity);
					row.find('.idPurchaseEnterpriseDetailLabel').val(idPurchaseEnterpriseDetail);
					row.find('.labelsAssign').attr("id","labelsAssign"+count);
					$('#tbody-conceptsNew').append(row);
					countLabels	= ($('select[name="idLabelsReview[]"] option:selected')).length;
					$('select[name="idLabelsReview[]"] option:selected').each(function(i){
						label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
						i < countLabels-1 ? labelText = $('<label></label').text($(this).text()+', ') : labelText = $('<label></label').text($(this).text());
						$('#labelsAssign'+count).parent().append(label);
						$('#labelsAssign'+count).parent().append(labelText);
					});
					count++;
				}
			})
			$('.js-labelsR').val(null).trigger('change');
			if(errorSwalElements){
				swal('', 'Seleccione los elementos que les quiera agregar esta(s) etiqueta(s)', 'error');
			}
		})
		.on('click','.approve-label',function()
		{
			concept 					= $('.conceptNew').text();
			quantity  					= $('.quantityNew').val();
			idPurchaseEnterpriseDetail	= $('.idPurchaseEnterpriseDetailNew').val();
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					["value"	=>	"Concepto",		"show"	=>	"true"],
					["value"	=>	"Etiquetas",	"show"	=>	"true"],
					["value"	=>	"Acción"],
				];
				$body	=
				[
					"classEx"	=>	"tr_assign",
					[
						"content"	=>
						[
							[
								"kind"		=>	"components.labels.label",
								"classEx"	=>	"conceptTxt",
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\"",
								"classEx"		=>	"conceptLabel"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\"",
								"classEx"		=>	"quantityLabel"
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.labels.label",
								"classEx"		=>	"labelsAssign"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"t_idPurchaseEnterpriseDetail[]\"",
								"classEx"		=>	"idPurchaseEnterpriseDetailLabel"
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"label"			=>	"<span class=\"icon-x delete-span\"></span>",
								"attributeEx"	=>	"type=\"button\"",
								"classEx"		=>	"delete-item"
							],
						]
					],
				];
				$modelBody[]	=	$body;
				$table = view('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $modelBody,"noHead"    => true])->render();
			@endphp
			table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
			row		=	$(table);
			row.find('.conceptTxt').text(concept);
			row.find('.conceptLabel').val(concept);
			row.find('.quantityLabel').val(quantity);
			row.find('.idPurchaseEnterpriseDetailLabel').val(idPurchaseEnterpriseDetail);
			row.find('.labelsAssign').attr("id","labelsAssign"+count);
			$('#tbody-conceptsNew').append(row);
			$('select[name="idLabelsReview[]"] option:selected').each(function()
			{
				label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
				labelText = $('<label></label').text($(this).text()+', ');
				$('#labelsAssign'+count).parent().append(label);
				$('#labelsAssign'+count).parent().append(labelText);
			});
			$('.js-labelsR').val(null).trigger('change');
			count++;
			$('.view-label').css('display','none');
			$('.conceptNew').text('');
			$('.idPurchaseEnterpriseDetailNew').val('');
			$('.add-label').removeAttr('disabled');
		})
		.on('click','.delete-item',function()
		{
			idPurchaseEnterpriseDetailLabel	= $(this).parents('.tr_assign').find('.idPurchaseEnterpriseDetailLabel').val();
			$('.idPurchaseEnterpriseDetailOld').each(function()
			{
				if($(this).val()==idPurchaseEnterpriseDetailLabel)
				{
					$(this).parents('.tr_body').show();
				}
			});
			$(this).parents('.tr_assign').remove();
			$('#tbody-concetpsNew .tr_assign').each(function(i,v)
			{
				$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
				$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
			});
			count = $('#tbody-conceptsNew .tr_assign').length;
		});
	});
</script>
@endsection
