@component('components.labels.subtitle') DATOS @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Título: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($request->resource->title)) value="{{ $request->resource->title }}" @endif
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Empresa: @endcomponent
		@php
			$optionEnt = [];
			foreach(App\Enterprise::where('status','ACTIVE')->orderBy('name','asc')->get() as $enterprise)
			{
				$optionEnt[] = [
					"value"			=> $enterprise->id,
					"description"	=> $enterprise->name,
					"selected"		=> (isset($request) && $request->idEnterprise ==$enterprise->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionEnt])
			@slot('attributeEx')
				name="enterpriseid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Departamento: @endcomponent
		@php
			$optionDepartment = [];
			foreach(App\Department::where('status','ACTIVE')->orderBy('name','asc')->get() as $department)
			{
				$optionDepartment[] = [
					"value"			=> $department->id,
					"description"	=> $department->name,
					"selected"		=> (isset($request) && $request->idDepartment == $department->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionDepartment])
			@slot('attributeEx')
				name="departmentid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Solicitante: @endcomponent
		@php
			$optionUser = [];
			if(isset($request) && $request->idRequest !="")
			{
				$optionUser[] = ["value" => $request->idRequest, "description" => $request->requestUser->fullName(), "selected" => "selected"];
			}
		@endphp
		@component('components.inputs.select', ["options" => $optionUser])
			@slot('attributeEx')
				name="userid" multiple="multiple" data-validation="required"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Dirección: @endcomponent
		@php
			$optionDirection = [];
			foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
			{
				$optionDirection[] = [
					"value"			=> $area->id,
					"description"	=> $area->name,
					"selected"		=> (isset($request) && $request->idArea == $area->id ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select', ["options" => $optionDirection])
			@slot('attributeEx')
				name="areaid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Proyecto: @endcomponent
		@php
			$optionProjects = [];
			if(isset($request))
			{
				$project = App\Project::find($request->idProject);
				$optionProjects[] = [
					"value" 		=> $project->idproyect, 
					"description"	=> $project->proyectName,
					"selected"		=> "selected"
				];
			}
		@endphp
		@component('components.inputs.select', ["options" => $optionProjects])
			@slot('attributeEx')
				name="projectid" data-validation="required" multiple="multiple"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
@endcomponent
@component('components.labels.title-divisor') FORMA DE PAGO @endcomponent
@php
	$buttons = 
	[
		[
			"textButton" 		=> "Cuenta Bancaria",
			"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".(isset($request) && $request->resource->idpaymentMethod == 1 ? " checked" : ""),
		],
		[
			"textButton" 		=> "Efectivo",
			"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".(isset($request) && $request->resource->idpaymentMethod == 2 ? " checked" : ""),
		],
		[
			"textButton" 		=> "Cheque",
			"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".(isset($request) && $request->resource->idpaymentMethod == 3 ? " checked" : ""),
		]
	];
@endphp
@component('components.buttons.buttons-pay-method',["buttons" => $buttons]) @endcomponent
<div class="resultbank @if(isset($request)) @if($request->resource->idpaymentMethod == 1) block @else hidden @endif @else hidden @endif">
	@component('components.labels.title-divisor') CUENTA @endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	=
		[
			[
				["value" => "Acción"],
				["value" => "Banco"],
				["value" => "Alias"],
				["value" => "Número de tarjeta"],
				["value" => "CLABE"],
				["value" => "Número de cuenta"]
			]
		];
		if(isset($request))
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->idRequest)->where('visible',1)->get() as $bank)
			{
				$class		= "";
				$checked	= "";
				if($request->resource->idEmployee == $bank->idEmployee)
				{
					$class		= "marktr";
					$checked	= "checked";
				}

				$body = [ "classEx" => $class,
					[
						"content"	=>
						[
							"kind"				=> "components.inputs.checkbox",
							"attributeEx"		=> "id=\"$bank->idEmployee\" type=\"radio\" name=\"idEmployee\" value=\"".$bank->idEmployee."\""." ".$checked,
							"classEx"			=> "checkbox",
							"classExLabel"		=> "request-validate",
							"label"				=> "<span class=\"icon-check\"></span>",
							"classExContainer"	=> "my-2",
							"radio"				=> true
						]
					],
					[
						"content"	=>
						[
							"label" => $bank->description
						]
					],
					[
						"content" =>
						[
							"label" => $bank->alias != null ? $bank->alias : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->cardNumber != null ? $bank->cardNumber : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->clabe != null ? $bank->clabe : '---'
						]
					],
					[
						"content" =>
						[
							"label" => $bank->account != null ? $bank->account : '---'
						]
					]
				];
				$modelBody[] = $body;
			}
		}
	@endphp
	@component('components.tables.table',
		[
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"attributeEx"	=> "id=\"table2\"",
			"classExBody"	=> "request-validate"
		])
	@endcomponent
</div>
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Referencia: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="reference" data-validation="required" placeholder="Ingrese la referencia" @if(isset($request)) value="{{ $request->resource->reference }}" @endif
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Tipo de moneda: @endcomponent
		@php
			$optionCurrency = [];
			$valueCurrency	= ["MXN","USD","EUR","Otro"];
			foreach ($valueCurrency as $v)
			{
				$optionCurrency[] =
				[
					"value"			=> $v,
					"description"	=> $v,
					"selected"		=> (isset($request) && $request->resource->currency == $v ? "selected" : "")
				];
			}
		@endphp
		@component('components.inputs.select',["options" => $optionCurrency])
			@slot('attributeEx')
				name="type_currency" multiple="multiple" data-validation="required"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
@endcomponent
@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS  @endcomponent
@component('components.containers.container-form')
	<div class="col-span-2">
		@component('components.labels.label') Concepto: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="concept" placeholder="Ingrese el concepto"
			@endslot
			@slot('classEx')
				removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Clasificación del gasto: @endcomponent
		@component('components.inputs.select',["options" => [] ])
			@slot('attributeEx')
				multiple="multiple" name="accountid"
			@endslot
			@slot('classEx')
				js-accounts removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2">
		@component('components.labels.label') Importe: @endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="text" name="amount" placeholder="Ingrese el importe"
			@endslot
			@slot('classEx')
				amount removeselect
			@endslot
		@endcomponent
	</div>
	<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
		@component('components.buttons.button',["variant" => "warning"])
			@slot('attributeEx')
				type="button" name="addConceptResource" id="addConceptResource"
			@endslot
			<span class="icon-plus"></span>
			<span>Agregar concepto</span>
		@endcomponent
	</div>
@endcomponent
@php
	$body		= [];
	$modelBody 	= [];
	$modelHead	= 
	[
		[
			["value" => "#"],
			["value" => "Concepto"],
			["value" => "Clasificación de gasto"],
			["value" => "Importe"],
			["value" => "Acción"]
		]
	];
	if(isset($request))
	{
		foreach($request->resource->resourceDetail as $key=>$resourceDetail)
		{
			$body = [ "classEx" => "tr-concept",
				[
					"classEx"	=> "countConcept",
					"content"	=>
					[
						"label" => $key+1
					]
				],
				[
					"content"	=>
					[
						[
							"label" => $resourceDetail->concept
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"idDetail[]\" value=\"".$resourceDetail->id."\"",
							"classEx"		=> "idRefundDetail" 
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"t_concept[]\" value=\"".$resourceDetail->concept."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $resourceDetail->accounts->account.' '.$resourceDetail->accounts->description." (".$resourceDetail->accounts->content.")"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"t_account[]\" value=\"".$resourceDetail->idAccAcc."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => '$ '.number_format($resourceDetail->amount,2)
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$resourceDetail->amount."\"",
							"classEx"		=> "t-amount"
						]
					]
				],
				[
					"content" =>
					[
						"kind"			=> "components.buttons.button",
						"variant"		=> "red",
						"attributeEx"	=> "type=\"button\"",
						"classEx"		=> "delete-item-resource",
						"label"			=> "<span class=\"icon-x\"></span>"
					]
				]
			];
			$modelBody[] = $body;
		}
	}
@endphp
@component('components.tables.table',
	[
		"modelBody" 		=> $modelBody,
		"modelHead" 		=> $modelHead,
		"attributeEx"		=> "id=\"table\"",
		"attributeExBody"	=> "id=\"body\"",
		"classExBody"		=> "request-validate"
	])	
@endcomponent
<div class="totales">
	@php
		$varTotalLabel	= "$ 0.00";
		$varTotal		= "";
		if(isset($request))
		{
			$varTotalLabel	= "$ ".number_format($request->resource->total,2);
			$varTotal		= $request->resource->total;
		} 
		$modelTable =
		[
			[
				"label" => "TOTAL:", "inputsEx" =>
				[
					[
						"kind" 		=> "components.labels.label",
						"label"		=> $varTotalLabel,
						"classEx"	=> "my-2 general-class totalLabel"
					],
					[
						"kind"			=> "components.inputs.input-text",
						"classEx" 		=> "removeselect",	
						"attributeEx" 	=> "type=\"hidden\" readonly id=\"total\" name=\"total_resource\" value=\"".$varTotal."\""
					]
				]
			]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
</div>