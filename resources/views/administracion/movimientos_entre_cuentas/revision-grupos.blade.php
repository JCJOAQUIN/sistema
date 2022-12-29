@extends('layouts.child_module')
@section('data')
	@php
		$taxes	=	$retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser	=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$AccountOrigin	=	App\Account::find($request->groups->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->groups->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",					$request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---'],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Tipo de Operación:",					$request->groups->first()->operationType],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->groups->first()->idEnterpriseOrigin)->name],
			["Dirección Origen:",					App\Area::find($request->groups->first()->idAreaOrigin)->name],
			["Departamento Origen:",				App\Department::find($request->groups->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen:",		$AccountOrigin->account." - ".$AccountOrigin->description." (".$AccountOrigin->content.")"],
			["Proyecto Origen:",					App\Project::find($request->groups->first()->idProjectOrigin)->proyectName],
			["Empresa Destino:",					App\Enterprise::find($request->groups->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
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
		
		foreach ($request->groups->first()->provider->providerData->providerBank as $bank)
		{
			$marktr			=	$request->groups->first()->provider_has_banks_id == $bank->id ? "marktr" : "";
			$ibanBank		=	$bank->iban=='' ? "---" : $bank->iban;
			$bicSwift		=	$bank->bic_swift=='' ? "---" : $bank->bic_swift;
			$agreementBank	=	$bank->agreement=='' ? "---" : $bank->agreement;
			$body	=
			[
				"classEx"=>$marktr,
				[
					"content"	=>	["label"	=>	isset($bank->bank->description) && $bank->bank->description!="" ? $bank->bank->description : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->alias) && $bank->alias!="" ? $bank->alias : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->account) && $bank->account!="" ? $bank->account : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->branch) && $bank->branch!="" ? $bank->branch : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->reference) && $bank->reference!="" ? $bank->reference : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->clabe) && $bank->clabe!="" ? $bank->clabe : "---"]
				],
				[
					"content"	=>	["label"	=>	isset($bank->currency) && $bank->currency!="" ? $bank->currency : "---"]
				],
				[
					"content"	=>	["label"	=>	$ibanBank]
				],
				[
					"content"	=>	["label"	=>	$bicSwift]
				],
				[
					"content"	=>	["label"	=>	$agreementBank]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
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
		$countConcept	=	1;
		foreach($request->groups->first()->detailGroups as $detail)
		{
			$taxesConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept	+=	$tax->amount;
			}
			$retentionConcept	=	0;
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
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
	@php
		foreach ($request->groups->first()->detailGroups as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes	+=	$tax->amount;
			}
			foreach ($detail->retentions as $ret)
			{
				$retentions	+=	$ret->amount;
			}
		}
		$subtotal				=	"$	".number_format($request->groups->first()->subtotales,2,".",",");
		$taxesAdditionalLabel	=	"$	".number_format($taxes,2,".",",");
		$retentionsLabel		=	"$	".number_format($retentions,2,".",",");
		$taxesLabel				=	"$	".number_format($request->groups->first()->tax,2,".",",");
		$totaLabel				=	"$	".number_format($request->groups->first()->amount,2,".",",");
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "attributeEx"	=>	"name=\"subtotal\" id=\"subtotalLabel\"",			"label"	=>	$subtotal]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "attributeEx"	=>	"name=\"amountAA\" id=\"addicitonalAmmountLabel\"",	"label"	=>	$taxesAdditionalLabel]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "attributeEx"	=>	"name=\"amountR\"  id=\"retentionLabel\"",			"label"	=>	$retentionsLabel]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "attributeEx"	=>	"name=\"totaliva\" id=\"addicitonaAmmountLabel\"",	"label"	=>	$taxesLabel]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "attributeEx"	=>	"name=\"total\" id=\"addicitonaAmmountLabel\"",		"label"	=>	$totaLabel]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('textNotes')
			{{ $request->groups->first()->notes }}
		@endslot
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
		$time		=	strtotime($request->PaymentDate);
		$date		=	date('d-m-Y',$time);
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	($request->groups->first()->reference != "" ? htmlentities($request->groups->first()->reference) : "---"),
			"Tipo de moneda"				=>	$request->groups->first()->typeCurrency,
			"Fecha de pago"					=>	$date,
			"Forma de pago"					=>	$request->groups->first()->paymentMethod->method,
			"Estado  de factura"			=>	$request->groups->first()->statusBill,
			"Importe a pagar"				=>	"$ ".number_format($request->groups->first()->amount,2),
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
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						],
					],
					[
						"content"	=>
						
						["label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
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
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.forms.form', ["attributeEx" => "action=\"".route('movements-accounts.groups.updateReview', $request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx"=>"PUT"])
		<div class="text-center">
			@component('components.labels.label')
				¿Desea aprobar ó rechazar la solicitud?
			@endcomponent
		</div>
		@component('components.containers.container-approval')
			@slot('attributeExButton')
				name="status" id="aprobar" value="4"
			@endslot
			@slot('attributeExButtonTwo')
				id="rechazar" name="status" value="6"
			@endslot
		@endcomponent
		<div id="aceptar" class="hidden">
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				ASIGNACIÓN DE ETIQUETAS
			@endcomponent
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=	["", "Cantidad", "Descripción"];
				foreach($request->groups->first()->detailGroups as $detail)
				{
					$options	=	collect();
					$body		=
					[
						"classEx"	=>	"tr_body",
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.inputs.checkbox",
									"attributeEx" 	=>	"id=\"id_article_$detail->idgroupsDetail\" name=\"add-article_$detail->idgroupsDetail\" value=\"1\"",
									"classEx"		=>	"add-article d-none",
									"classExLabel"	=>	"check-small request-validate",
									"label"			=>	'<span class="icon-check"></span>',
								]
							]
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
									"attributeEx"	=>	"type=\"hidden\" value=\"".$detail->idgroupsDetail."\"",
									"classEx"		=>	"idgroupsDetailOld"
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
				$body	=
				[
					[
						"content"	=>	[]
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
								"classEx"		=>	"js-labelsR labelsNew"
							]
						
						]
					]
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
			<div class="text-center">@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" title=\"Agregar\"", "classEx" => "add-label", "label" => "<span class=\"icon-plus\"></span> agregar"]) @endcomponent</div>
			@component('components.containers.container-form',	["classEx" => "hidden"])
				<div class="col-span-2">
					@component('components.labels.label',		["label" 		=>	"Concepto"]) @endcomponent
					@component('components.inputs.input-text',	["attributeEx"	=>	"type=\"hidden\"",	"classEx"	=>	"idgroupsDetailNew"]) @endcomponent
					@component('components.inputs.input-text',	["attributeEx"	=>	"type=\"hidden\"",	"classEx"	=>	"quantityNew"]) @endcomponent
					@component('components.inputs.input-text',	["classEx" 		=>	"conceptNew"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Etiquetas"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\Label::orderName()->get() as $label)
						{
							$options	=	$options->concat([["value" =>  $label->idlabels, "description" => $label->description]]);
						}
					@endphp
					@component('components.inputs.select',["options" => $options, "classEx" => "js-labelsR labelsNew", ["attributeEx" => "multiple=\"multiple\" name=\"idLabelsReview[]\""]]) @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning", "label" => "AGREGAR", "attributeEx" => "type=\"button\"", "classEx" => "approve-label"]) @endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				ETIQUETAS ASIGNADAS
			@endcomponent
			@php
				$modelHead		=	[];
				$body			=	[];
				$modelBody		=	[];
				$modelHead		=	["Concepto", "Etiquetas", "Acción"];
				$modelBody[]	=	$body;
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeExBody" => "id=\"tbody-conceptsNew\"", "classExBody" => "request-validate"])@endcomponent
			<span id="labelsAssign"> </span>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE ORIGEN
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->groups()->exists() && $request->groups->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$options	=	$options->concat([["value" =>  $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,"selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" =>  $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"enterpriseid_origin\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-enterprises-origin removeselect"])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
						$options	=	collect();
						foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if ($request->groups()->exists() && $request->groups->first()->idAreaOrigin == $area->id)
							{
								$options	=	$options->concat([["value" => $area->id, "description" => $area->name, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $area->id, "description" => $area->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"areaid_origin\" data-validation=\"required\"", "classEx" => "js-areas-origin removeselect"])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$options	=	collect();
						foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if ($request->groups()->exists() && $request->groups->first()->idDepartamentOrigin == $department->id)
							{
								$options	=	$options->concat([["value" => $department->id, "description" => $department->name, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $department->id, "description" => $department->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"departmentid_origin\" data-validation=\"required\"", "classEx" => "js-departments-origin removeselect"])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@php
						$options	=	collect();
						if ($request->groups()->exists() && $request->groups->first()->idAccAccOrigin !="")
						{
							$options	=	$options->concat([["value"	=>	$request->groups->first()->accountOrigin->idAccAcc,	"description"	=>	$request->groups->first()->accountOrigin->account." - ".$request->groups->first()->accountOrigin->description." (".$request->groups->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"accountid_origin\" data-validation=\"required\"", "classEx" => "js-accounts-origin removeselect"])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto/Contrato: @endcomponent
					@php
						$options	=	collect();
						if ($request->groups()->exists() && $request->groups->first()->idProjectOrigin !="")
						{
							$options	=	$options->concat([["value"	=>	$request->groups->first()->projectOrigin->idproyect,	"description"	=>	$request->groups->first()->projectOrigin->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"projectid_origin\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-projects-origin removeselect"])
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE DESTINO
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
					@php
						$options	=	collect();
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->groups()->exists() && $request->groups->first()->idEnterpriseDestiny == $enterprise->id)
							{	
								$options	=	$options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "name=\"enterpriseid_destination\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "js-enterprises-destination removeselect"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Clasificación del gasto:"]) @endcomponent
					@php
						$options	=	collect();
						if ($request->groups()->exists() && $request->groups->first()->idAccAccDestiny !="")
						{
							$options	=	$options->concat([["value"	=>	$request->groups->first()->accountDestiny->idAccAcc,	"description"	=>	$request->groups->first()->accountDestiny->account." - ".$request->groups->first()->accountDestiny->description." (".$request->groups->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "attributeEx" => "multiple=\"multiple\" name=\"accountid_destination\" data-validation=\"required\"", "classEx" => "js-accounts-destination removeselect"]) @endcomponent
				</div>
			@endcomponent
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label', ["label" => "Comentarios (opcional)"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "checkCommentA"]) @endcomponent
			</div>
		</div>
		<div id="rechaza" class="hidden">
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label', ["label" => "Comentarios (opcional)"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "checkCommentR"]) @endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
			@php
				$optionId	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "label" => "REGRESAR", "buttonElement" => "a", "classEx" => "load-actioner", "attributeEx" => "href=\"".$optionId."\""]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	function validate()
	{
		$.validate(
		{
			form: '#container-alta',
			modules		: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
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
						else if(($('#tbody-concepts .tr_body').length-1) != ($('#tbody-conceptsNew .tr_assign').length - 1)) 
						{
							swal('', 'Tiene conceptos sin asignar', 'error');
							return false;
						}
						else
						{
							swal("Cargando",
							{
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
						swal("Cargando",
						{
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
	}
	$(document).ready(function()
	{
		validate();
		count = 0;
		$(document).on('change','input[name="status"]',function()
		{
			if ($('input[name="status"]:checked').val() == "4") 
			{
				$("#rechaza").slideUp("slow");
				$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
				generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 6});
				generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 6});
				generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : 100});
				generalSelect({'selector': '.js-projects-origin', 'model': 21});
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
						]
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
		.on('click','.add-label',function()
		{ 
			errorSwalElements=true;
			$('.add-article').each(function()
			{
				if($(this).is(':checked')) 
				{
					errorSwalElements	= false;
					tr					= $(this).parents('.tr');
					quantity 		= tr.find('.quantityOld').val();
					concept  		= tr.find('.conceptOld').val();
					idgroupsDetail 	= tr.find('.idgroupsDetailOld').val();
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
										"attributeEx"	=>	"labelsAssign",
										"classEx"		=>	"labelsAssign"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"t_idgroupsDetail[]\"",
										"classEx"		=>	"idgroupsDetailLabel"
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
						$table = view('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $modelBody,"noHead" => true])->render();
					@endphp
					table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row		=	$(table);
					row.find('.conceptTxt').text(concept);
					row.find('.conceptLabel').val(concept);
					row.find('.quantityLabel').val(quantity);
					row.find('.idgroupsDetailLabel').val(idgroupsDetail);
					row.find('.labelsAssign').attr("id","labelsAssign"+count);
					$('#tbody-conceptsNew').append(row);
					countLabels	= ($('select[name="idLabelsReview[]"] option:selected')).length;
					$('select[name="idLabelsReview[]"] option:selected').each(function(i){
						label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
						i < countLabels-1 ? labelText = $('<label></label').text($(this).text()+', ') : labelText = $('<label></label').text($(this).text());
						$('#labelsAssign'+count).append(label);
						$('#labelsAssign'+count).append(labelText);
					});
					count++;
				}
			})
			if(errorSwalElements)
			{
				swal('', 'Seleccione los elementos que les quiera agregar esta(s) etiqueta(s)', 'error');
			}
			else
			{
				$('.js-labelsR').val(null).trigger('change');
			}
		})
		.on('click','.approve-label',function()
		{
			concept 		= $('.conceptNew').text();
			quantity  		= $('.quantityNew').val();
			idgroupsDetail 	= $('.idgroupsDetailNew').val();
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
								"attributeEx"	=>	"labelsAssign",
								"classEx"		=>	"labelsAssign"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" name=\"t_idgroupsDetail[]\"",
								"classEx"		=>	"idgroupsDetailLabel"
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
				$table = view('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $modelBody,"noHead" => true])->render();
			@endphp
			table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
			row		=	$(table);
			row.find('.conceptTxt').text(concept);
			row.find('.conceptLabel').val(concept);
			row.find('.quantityLabel').val(quantity);
			row.find('.idgroupsDetailLabel').val(idgroupsDetail);
			row.find('.labelsAssign').attr("id","labelsAssign"+count);
			$('#tbody-conceptsNew').append(row);
			$('select[name="idLabelsReview[]"] option:selected').each(function()
			{
				label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
				labelText = $('<label></labell').text($(this).text()+', ');
				$('#labelsAssign'+count).append(label);
				$('#labelsAssign'+count).append(labelText);
			});
			$('.js-labelsR').val(null).trigger('change');

			count++;
			$('.view-label').css('display','none');
			$('.conceptNew').text('');
			$('.idgroupsDetailNew').val('');
			$('.add-label').removeAttr('disabled');
		})
		.on('click','.delete-item',function()
		{
			idExpensesDetailNew	= $(this).parents('#tbody-conceptsNew .tr').find('.idgroupsDetailLabel').val();
			idaccount = $(this).parents('.tr').find('.accountIdOld').val();
			$('.idgroupsDetailOld').each(function()
			{
				if($(this).val()==idExpensesDetailNew)
				{
					$(this).parents('.tr').show();
				}
			});
			$(this).parents('.tr').remove();

			$('#tbody-conceptsNew .tr').each(function(i,v)
			{
				$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
				$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
			});
			count = $('#tbody-conceptsNew .tr').length;
		});

		/*$('.subtotal').text("$"+sumatotal);
		sumatotal = 0;
		$('.importe').each(function(i, v)
			{
				valor		= parseFloat($(this).val());
				sumatotal	= sumatotal + valor;
			});*/
	});
</script>
@endsection
