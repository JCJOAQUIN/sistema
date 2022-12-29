@extends('layouts.child_module')
@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD:
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif

	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('resource.follow.update',$request->folio)."\"", "methodEx" => "PUT", "files"=>true])
		@component("components.labels.title-divisor") Folio: {{ $request->folio }} @endcomponent
		@component('components.labels.subtitle')
			Elaborado por: {{$request->elaborateUser->fullName()}}
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Título:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
					@endslot
					@slot("attributeEx")
						name="title"
						placeholder="Ingrese el título"
						data-validation="required"
						value="{{ $request->resource->first()->title }}"
						@if($request->status!=2) disabled="disabled" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Fecha:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
						datepicker
					@endslot
					@slot("attributeEx")
						name="datetitle"
						value="{{ $request->resource->first()->datetitle!="" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y') : '' }}"
						data-validation="required"
						placeholder="Ingrese la fecha"
						readonly="readonly"
						@if($request->status!=2) disabled="disabled" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Solicitante:
				@endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					if(isset($request) && $request->idRequest)
					{
						$user = App\User::find($request->idRequest);
						$options = $options->concat([["value"=>$user->id, "selected"=>"selected","description"=>$user->name. " " .$user->last_name. " " .$user->scnd_last_name]]);
					}	
					$classEx = "js-users removeselect";
					$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"".' '.$disabled;
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Empresa:
				@endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					foreach($enterprises as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
						if($request->idEnterprise == $enterprise->id)
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$classEx = "js-enterprises removeselect";
					$attributeEx = "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises\" data-validation=\"required\"".' '.$disabled;
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Dirección:
				@endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					foreach($areas as $area)
					{
						$description = $area->name;
						if($request->idArea == $area->id)
						{
							$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
						}
					}
					$classEx = "js-areas removeselect";
					$attributeEx = "name=\"area_id\" multiple=\"multiple\" id=\"multiple-areas\" data-validation=\"required\"".' '.$disabled;
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Departamento:
				@endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					foreach($departments as $department)
					{
						$description = $department->name;
						if($request->idDepartment == $department->id)
						{
							$options = $options->concat([["value"=>$department->id, "selected"=>"selected", "description"=>$description]]);
						} 
						else
						{
							$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
						}
					}
					$classEx = "js-departments removeselect";
					$attributeEx = "name=\"department_id\" multiple=\"multiple\" id=\"multiple-departments\" data-validation=\"required\"".' '.$disabled;
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Proyecto:
				@endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					if(isset($request) && $request->idProject)
					{
						$project = App\Project::find($request->idProject);
						$options = $options->concat([["value"=>$project->idproyect, "selected"=>"selected","description"=>$project->proyectName]]);
					}
					$classEx = "js-projects removeselect";
					$attributeEx = "name=\"project_id\" multiple=\"multiple\" id=\"multiple-projects\" data-validation=\"required\"".' '.$disabled;
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class="col-span-2 select_father @if(!isset($request->code_wbs)) hidden @endif">
				@component('components.labels.label') Código WBS: @endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					} 
					$options = collect();
					if(isset($request) && $request->code_wbs)
					{
						$code = App\CatCodeWBS::find($request->code_wbs);
						$options = $options->concat([["value"=>$code->id, "selected"=>"selected","description"=>$code->code_wbs]]);
					}
					$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"".' '.$disabled;
					$classEx = "js-code_wbs removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
			<div class=" col-span-2 codeEdt @if(!isset($request->code_edt)) hidden @endif">				
				@component('components.labels.label') Código EDT: @endcomponent
				@php
					$disabled = "";
					if($request->status != 2)
					{
							$disabled = "disabled"; 
					}
					$options = collect();
					if(isset($request) && $request->code_edt)
					{
						$edt = App\CatCodeEDT::find($request->code_edt);
						$options = $options->concat([["value"=>$edt->id, "selected"=>"selected", "description"=>$edt->code.' ('.$edt->description.')']]);
					}
					$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"".' '.$disabled;
					$classEx = "js-code_edt removeselect";
				@endphp
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"> @endcomponent
		@php
			$disabled = $request->status != 2 ? " disabled=\"disabled\"" : ""; 
			$buttons = 
			[
				[
					"textButton" 		=> "Cuenta Bancaria",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->resource->first()->idpaymentMethod == 1 ? " checked" : "").$disabled,
				],
				[
					"textButton" 		=> "Efectivo",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->resource->first()->idpaymentMethod == 2 ? " checked" : $request->resource->first()->idpaymentMethod == "" ? " checked" : "").$disabled,
				],
				[
					"textButton" 		=> "Cheque",
					"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->resource->first()->idpaymentMethod == 3 ? " checked" : "").$disabled,
				],							
			];
		@endphp
		@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
		@component("components.inputs.input-text")
			@slot('classEx')
				employee_number
			@endslot
			@slot('attributeEx')
				type="hidden"
				name="employee_number"
				id="efolio"
				placeholder="Ingrese el número de empleado"
				value="{{ $request->resource->first()->idUsers }}"
			@endslot
		@endcomponent
		<div class="resultbank @if($request->resource->first()->idpaymentMethod == 1) block @else hidden @endif">
			@component("components.labels.title-divisor")
				CUENTA
			@endcomponent
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

				foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('visible',1)->where('employees.idUsers',$request->resource->first()->idUsers)->get() as $bank)
				{
					$marktr = "";
					if($request->resource->first()->idEmployee == $bank->idEmployee)
					{
						$marktr = "marktr";
					}
					if($request->status!=2)
					{
						$disabled = ' disabled';
					}
					$body = [];
					$body = 
					[
						"classEx"	=>	"tr ".$marktr,
						[
							"content" 	=>
							[
								[
									"classExContainer" 	=> "inline-flex",
									"kind"          	=> "components.inputs.checkbox",
									"classEx"			=> "checkbox btn-green request-validate",
									"label"				=> "<span class=\"icon-check\"></span>",
									"radio"				=> true,
									"attributeEx"		=> "id=\"id".$bank->idEmployee."\" type=\"radio\" name=\"idEmployee\" value=\"".$bank->idEmployee."\"".($marktr != "" ? " checked" : "").$disabled,
									"classExLabel"		=> $disabled,
								],
							]
						],
						[
							"content" 	=>
							[
								[
									"label" =>	$bank->description
								]
							]
						],
						[
							"content" 	=>
							[
								[
									"label" =>	$bank->alias 
								]
							]
						],
						[
							"content" 	=>
							[
								[
									"label" =>	$bank->cardNumber 
								]
							]
						],
						[
							"content" 	=>
							[
								[
									"label" =>	$bank->clabe
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" =>	$bank->account
								]
							]
						],
					];
					if($request->status != 2)
					{
						if($marktr != "")
						{
							$modelBody[] = $body;
							break;
						}
					}
					else 
					{
						$modelBody[] = $body;
					}
				}
			@endphp
			@component("components.tables.table",[
				"modelHead"	=> $modelHead,
				"modelBody"	=> $modelBody,
			])
				@slot("classEx")	
					text-center
				@endslot
				@slot("classExBody")
					request-validate
				@endslot
			@endcomponent
		</div>
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component("components.labels.label") Referencia: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						removeselect
					@endslot
					@slot('attributeEx')
						name="reference"
						placeholder="Ingrese la referencia"
						value="{{ $request->resource->first()->reference }}" 
						@if($request->status!=2) 
							disabled="disabled"
						@endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de moneda: @endcomponent
					@php
						$disabled = "";
						if($request->status != 2)
						{
								$disabled = "disabled"; 
						}
						$options = collect();
						$value = ["MXN", "EUR", "USD", "Otro"];
						foreach($value as $item)
						{				
							if($item == $request->resource->first()->currency)
							{
								$options = $options->concat([["value" => $item, "description" => $item, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $item, "description" => $item]]);
							}				
						}
						$attributeEx = "name=\"currency\" data-validation=\"required\"".' '.$disabled;
						$classEx = "js-currency removeselect";
					@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
		@endcomponent
		@if($request->status == "2")
			@component("components.labels.title-divisor")
				RELACIÓN DE DOCUMENTOS <span class="help-btn" id="help-btn-documents">
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component("components.labels.label")
						Concepto:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="concept"
							placeholder="Ingrese el concepto"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Clasificación del gasto:
					@endcomponent
					@php
						$options = collect();
						
						$attributeEx = "multiple=\"multiple\" name=\"account_id\"";
						$classEx = "js-accounts removeselect";	
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Importe:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							amount
						@endslot
						@slot("attributeEx")
							name="amount"
							placeholder="Ingrese el importe"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							type="button"
							name="add"
							id="add"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar Concepto</span>
					@endcomponent
				</div>
			@endcomponent
		@endif
		@php
			$body = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value"=>"#"],
					["value"=>"Concepto"],
					["value"=>"Clasificación de gasto"],
					["value"=>"Importe"],
				]
			];

			if($request->status == 2)
			{
				$modelHead[0] = [["value"=>"Acción"]];
			}

			$subtotalFinal = $ivaFinal = $totalFinal = 0;

			foreach($request->resource->first()->resourceDetail as $key=>$resourceDetail)
			{
				$totalFinal		+= $resourceDetail->amount;
				$body = 
				[
					"classEx" => "tr",
					[
						"classEx"	=> "countConcept",
						"content" 	=>
						[
							[
								"label" => $key+1,
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"idRDe[]\" value=\"".$resourceDetail->idresourcedetail."\"",
								"classEx" 		=> "idRefundDetail"
							],
							[
								"label" => htmlentities($resourceDetail->concept),
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx" 		=> "input-table",
								"attributeEx"	=> "type=\"hidden\" name=\"t_concept[]\" value=\"".htmlentities($resourceDetail->concept)."\"",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $resourceDetail->accounts->account.' '.$resourceDetail->accounts->description,
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx" 		=> "input-table",
								"attributeEx" 	=> "type=\"hidden\" name=\"t_account[]\" value=\"".$resourceDetail->idAccAcc."\"",
							]
						]
					],
					[
						"content" =>
						[
							[	
								"label" => "$".number_format($resourceDetail->amount,2),
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx" 		=> "input-table t-amount",
								"attributeEx" 	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$resourceDetail->amount."\"",
							]
						]
					],
					
				];
				if($request->status == 2)
				{
					$body[] = 
					[
						"content" =>
						[
							[
								"kind" 			=> "components.buttons.button",
								"classEx" 		=> "delete-item",
								"attributeEx" 	=> "id=\"cancel\" ",
								"label" 		=> "<span class=\"icon-x delete-span\"></span>",
								"variant" 		=> "dark-red",
							]
						]
					];
				}
				$modelBody [] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
			])
			@slot("classEx")	
				text-center
			@endslot
			@slot("attributeEx")
				id="table"
			@endslot
			@slot("attributeExBody")
				id="body"
			@endslot
			@slot("classExBody")
				request-validate
			@endslot
		@endcomponent
		@php
			
			$modelTable = 
			[
				[
					"label" => "TOTAL:", "inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($totalFinal,2),
							"classEx" => "total"
						],
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "total",
							"attributeEx" => "id=\"total\" type=\"hidden\" readonly=\"readonly\" name=\"total\" placeholder=\"$0.00\" value=\"".$totalFinal."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details',[
			"modelTable" => $modelTable,
			
		])
		@endcomponent
		<div id="invisible"></div>		
		@if($request->idCheck != "")
			<div class="mt-4">
				@component("components.labels.title-divisor") DATOS DE REVISIÓN	@endcomponent
			</div>
			<div class="my-6">
				@component("components.tables.table-request-detail.container",["variant"=>"simple"])
					@php
						$account = '';
						$modelTable = ["Reviso" => $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name];
						if ($request->idEnterpriseR!="")
						{
							$modelTable = ["Nombre de la Empresa" => App\Enterprise::find($request->idEnterpriseR)->name,
											"Nombre de la Dirección" => $request->reviewedDirection->name,
											"Nombre del Departamento" => App\Department::find($request->idDepartamentR)->name,
											"Clasificación del gasto" => $reviewAccount = App\Account::find($request->accountR),
											];
							if(isset($reviewAccount->account))
							{
								$account = $reviewAccount->account. " - ".$reviewAccount->description;
							}
							else
							{
								$account = "Varias";
							}
							$modelTable ['Clasificación del gasto'] = $account;
							if(isset($request->reviewedProject->proyectName))
							{
								$project = $request->reviewedProject->proyectName;
							}
							else
							{
								$modelTable[] = "No se seleccionó proyecto";
							}
							$modelTable ['Nombre del Proyecto'] = $project;
							$labels = "";
							foreach($request->labels as $label)
							{
								$labels = $labels." ".$label->description."," ;
							}
							$modelTable ['Etiquetas'] = $labels ? $labels : "Sin etiquetas";
						}
						if($request->checkComment == "")
						{
							$modelTable["Comentarios"] = "Sin comentarios";
						}
						else 
						{
							$modelTable["Comentarios"] = htmlentities($request->checkComment);
						}
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
				@endcomponent
			</div>
			@if(isset($resourceDetail->accountsReview->account))
				@component("components.labels.title-divisor")
					RELACIÓN DE DOCUMENTOS APROBADOS
				@endcomponent
				@php
					$heads = ["Concepto","Clasificación de gasto","Importe"];
					$modelBody = [];
					$subtotalFinal = $ivaFinal = $totalFinal = 0;
					foreach($request->resource->first()->resourceDetail as $resourceDetail)
					{
						$body = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"label" => htmlentities($resourceDetail->concept),
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => isset($resourceDetail->accountsReview->account) ? $resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description : $resourceDetail->accounts->account.' '.$resourceDetail->accounts->description,
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => "$".number_format($resourceDetail->amount,2),
									]
								]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelBody,
				])
					@slot("classExBody")
						request-validate
					@endslot
					@slot("attributeExBody")
						id="tbody-conceptsNew"
					@endslot
				@endcomponent	
			@endif
		@endif
		@if ($request->idAuthorize != "")
			@component("components.labels.title-divisor")DATOS DE AUTORIZACIÓN @endcomponent
			<div class="my-6">
				@component("components.tables.table-request-detail.container",["variant" => "simple"])
					@php
						$modelTable = ["Autorizó" => $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
										"Comentarios" => $request->authorizeComment == "" ? "Sin Comentarios" : htmlentities($request->authorizeComment)]
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent	
				@endcomponent
			</div>
		@endif
		@if ($request->status == 13)
			@component("components.labels.title-divisor")DATOS DE PAGOS	@endcomponent
			<div class="my-6">
				@component("components.tables.table-request-detail.container",["variant" => "simple"])
					@php
						$modelTable = ["Comentarios" => $request->paymentComment == "" ? "Sin comentarios" : $request->paymentComment]
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent	
				@endcomponent
			</div>
		@endif
		<div class="my-6">
			@php
				$payments 		= App\Payment::where('idFolio',$request->folio)->get();
				$total 			= $request->resource->first()->total;
				$totalPagado 	= 0;
			@endphp
			@if(count($payments) > 0)
				@component("components.labels.title-divisor") HISTORIAL DE PAGOS	@endcomponent
				@php
					$body = [];
					$modelBody = [];
					$heads = ["Cuenta","Cantidad","Documento","Fecha"];

					foreach($payments as $pay)
					{
						$body =
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"label" => $pay->accounts->account." - ".$pay->accounts->description,
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => "$".number_format($pay->amount,2),
									]
								]
							]
						];
						if($pay->documentsPayments()->exists())
						{
							$docsContent = [];
							foreach($pay->documentsPayments as $doc)
							{
								$docsContent['content'][] = 
								
								[
									"kind" 			=> "components.buttons.button",
									"buttonElement" => "a",
									"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
									"label"			=> "PDF",
									"variant"		=> "dark-red",
								];
							}
						}
						else 
						{
							$docsContent['content'] = 
							[
								"label" => "Sin documento"
							];
						}
						$body[] = $docsContent;
						$body[] =  
						[ 
							"content" => 
							[
								"label" => $pay->paymentDate,
							]
						];
						$totalPagado += $pay->amount;
						$modelBody[] = $body;
					}
				@endphp
				@component("components.tables.alwaysVisibleTable",[
					"modelHead"	=> $heads,
					"modelBody"	=> $modelBody,
				])
					@slot("classEx")	
						text-center
					@endslot
				@endcomponent
				@php
					$modelTable = 
					[
						[
							"label" => "Total pagado:",
							"attributeExInput" => "$".number_format($totalFinal,2),
							"class" => "total"		
						],
					];
				@endphp
				@component('components.templates.outputs.form-details',[
					"modelTable" => $modelTable,
					
				])
				@endcomponent
				@php
					$modelTable = 
					[
						[
							"label" => "Resta por pagar:",
							"attributeExInput" => "$".number_format($total-$totalPagado,2),
							"class" => "total"		
						],
					];
				@endphp
				@component('components.templates.outputs.form-details',[
					"modelTable" => $modelTable,
					
				])
				@endcomponent
			@endif
			<div id="delete"></div>
			@if($request->resource->first()->documents()->exists())
				@component("components.labels.title-divisor")
					DOCUMENTOS CARGADOS
				@endcomponent
				@php
					$heads = ["Nombre","Archivo","Modificado Por"];
					$modelBody = [];

					foreach($request->resource->first()->documents->sortByDesc('created_at') as $doc)
					{
						$body = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"label" => $doc->name,
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" name=\"document-id[]\" value=\"".$doc->id."\"",
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.buttons.button",
										"buttonElement" => "a",
										"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/resource/'.$doc->path)."\"",
										"label"			=> "Archivo",
										"variant"		=> "secondary",
									]
								]
							],
							[
								"content" =>
								[
									[
										"label" => $doc->user->fullName(),
									]
								]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				@if (isset($request) && $request->status == 2)
					@component("components.inputs.input-text")
						@slot("attributeEx")
							type="hidden"
							name="to_delete"
						@endslot
					@endcomponent
				@endif
				@component("components.tables.alwaysVisibleTable",[
					"modelHead" => $heads,
					"modelBody" => $modelBody,
				])
				@endcomponent
			@endif
		</div>
		@if($request->status == "2")
			@component("components.labels.title-divisor") CARGAR DOCUMENTOS @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents-resource"></div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							id="addDoc"
							name="addDoc"
							type="button"
						@endslot
						@slot('label')
							<span class="icon-plus"></span>
							<span>Agregar Documento</span>
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endif
		<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
			@if ($request->status == 2)
				@component('components.buttons.button',["variant"=>"primary"])
					@slot('attributeEx')
						type="submit"
						name="enviar"
						id="send"
						value="ENVIAR SOLICITUD"
					@endslot
					ENVIAR SOLICITUD
				@endcomponent
				@component('components.buttons.button',["variant"=>"secondary"])
					@slot('classEx')
						save
					@endslot
					@slot('attributeEx')
						id="save"
						type="submit"
						name="save"
						value="GUARDAR SIN ENVIAR"
						formaction="{{ route('resource.follow.updateunsent', $request->folio) }}"
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
			@endif
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}"
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/bignumber.min.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					concept = $('[name="concept"]').val();
					account = $('.js-accounts option:selected').val();
					amount	= $('[name="amount"]').val();
					if(concept != "" || account != null || amount != "")
					{
						swal('', 'Tiene información en los conceptos para agregar, por favor verifique sus campos.', 'error');
						return false;
					}

					if ($('.request-validate').length > 0)
					{
						conceptos	= $('#body .tr').length;
						check 		= $('.checkbox:checked').length;
						method 		= $('input[name="method"]:checked').val();
						if (method != undefined)
						{
							if (method == 1) 
							{
								if (check <= 0)
								{
									swal('', 'Por favor seleccione una cuenta.', 'error');
									return false;
								}
							}
						}
						else
						{
							swal('', 'Por favor seleccione un método de pago.', 'error');
							return false;
						}
						if (conceptos <= 0)
						{
							swal('', 'Por favor agregue al menos un concepto.', 'error');
							return false;
						}
					}
				}
			});
			@php
				$selects = collect ([
					[
						"identificator"				=> ".js-kind",
						"placeholder"				=> "Seleccione el tipo de gasto",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-enterprises",			
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-areas",
						"placeholder"				=> "Seleccione la dirección",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-departments",
						"placeholder"				=> "Seleccione el departamento",
						"maximumSelectionLength"	=> "1",
					],
					[
						"identificator"				=> ".js-currency",
						"placeholder"				=> "Seleccione el tipo de moneda",
						"maximumSelectionLength"	=> "1",
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '.js-projects', 'model': 21});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
			generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs','model': 15});
			
			$('[name="amount"]').on("contextmenu",function(e)
			{
				return false;
			});
			$(function() 
			{
				$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
			$('.amount,.descuento').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			
			$(document).on('change','input[name="fiscal"]',function()
			{
				if ($('input[name="fiscal"]:checked').val() == "si") 
				{
					$(".iva").prop('disabled', false);
				}
				else if ($('input[name="fiscal"]:checked').val() == "no") 
				{
					$("#noiva").prop('checked',true);
					$(".iva").prop('disabled', true);
					$("#iva_a").prop('checked',true);
					$(".iva_kind").prop('disabled', true);
				}
			})
			.on('change','input[name="iva"]',function()
			{
				if ($('input[name="iva"]:checked').val() == "si") 
				{
					$(".iva_kind").prop('disabled', false);
				}
				else if ($('input[name="iva"]:checked').val() == "no")
				{
					$("#iva_a").prop('checked',true);
					$(".iva_kind").prop('disabled', true);
				}
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
								$('.select_father').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects','model': 22});
							}
							else
							{
								$('.js-code_wbs, .js-code_edt').html('');
								$('.select_father, .code-edt').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs, .js-code_edt').html('');
					$('.select_father, .code-edt').removeClass('block').addClass('hidden');				
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
								$('.code-edt').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
							}
							else
							{
								$('.js-code_edt').html('');
								$('.code-edt').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_edt').html('');
					$('.code-edt').removeClass('block').addClass('hidden');
				}
			})
			.on('click','#add',function()
			{
				$('.js-accounts').parent().find('.form-error').remove();
				countConcept = $('.countConcept').length;
				concept      = $('input[name="concept"]').val().trim();
				amount       = Number($('input[name="amount"]').val().trim());
				account 	= $('.js-accounts :selected').text();
				id_account 	= $('.js-accounts :selected').val();
				regex        = /^(\d{1,18}(\.\d{1,2})?)$/m;
				if(isNaN(amount))
				{
					amount = 0;
					$('input[name="amount"]').val('');
				}
				else if(!regex.test(amount))
				{
					amount = 0;
					$('input[name="amount"]').addClass('error');
					swal('', 'El número ingresado excede el tamaño máximo aceptado', 'error');
					return;
				}
				
				if (concept == "" || account == "" || amount == "")
				{
					swal('', 'Por favor llene los campos necesarios', 'error');
					if(concept == "")
					{
						swal('', 'Por favor llene el Concepto.', 'error');
						$('input[name="concept"]').addClass('error');
					}
					if(account == "")
					{
						swal('', 'Por favor seleccione una clasificación de gasto.', 'error');
						$('.js-accounts').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(amount == "")
					{
						swal('', 'La cantidad no puede ser cero.', 'error');
						$('input[name="amount"]').addClass('error');
					}
				}
				else
				{
					countConcept = countConcept+1;
					@php
						$modelHead = 
						[
							[
								["value" => "#"],
								["value" => "Concepto"],
								["value" => "Clasificación del gasto"],
								["value" => "Importe"],
								["value" => ""]
							]
						];

						$modelBody = 
						[
							[
								"classEx" => "tr",
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx" 	=> "countConcept",
											"label" 	=> "",
										]
									]
								],
								[
									"content" =>
									[
										[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "concept",
												"label" 	=> "",
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx" 		=> "input-table t_concept",
											"attributeEx" 	=> "type=\"hidden\" name=\"t_concept[]\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx" 		=> "input-table t_account",
											"attributeEx" 	=> "type=\"hidden\" name=\"t_account[]\"",
										],
										[
											"kind"			=> "components.labels.label",
											"classEx"		=> "account",
											"label"			=> "",
										],
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "input-table taccount",
											"attributeEx"	=> "type=\"hidden\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.labels.label",
											"classEx"		=> "amount",
											"label"			=> "", 
										],
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "input-table t-amount",
											"attributeEx"	=> " type=\"hidden\" name=\"t_amount[]\"",
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" => "components.buttons.button",
											"attributeEx" => "type=button",
											"classEx" => "delete-item", 
											"label" => "<span class=icon-x></span>",
											"variant" => "red",
										]
									]
								]
							]
						];

						$table = view("components.tables.table", [
							"modelHead" => $modelHead,
							"modelBody" => $modelBody, 
							"noHead"	=> "true"						
						])->render();
						$table_body = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp

					table_body = '{!!preg_replace("/(\r)*(\n)*/", "", $table_body)!!}';
					row = $(table_body);					
					row.find('div').each(function()
					{
						$(this).find('.countConcept').text(countConcept);
						$(this).find('.concept').text(concept);
						$(this).find('.t_concept').val(concept);
						$(this).find('.id_account').text(id_account);
						$(this).find('.t_account').val(id_account);
						$(this).find('.taccount').val(account);
						$(this).find('.account').text(account);
						$(this).find('.t-amount').val(amount);
						$(this).find('.amount').text('$' +Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					})
					
					$('#body').append(row);
					$('input[name="concept"]').val('');
					$('input[name="amount"]').val('');
					$('.js-accounts').val(0).trigger("change");
					$('.js-accounts').parent().find('.form-error').remove();
					$('input[name="concept"]').removeClass('error');
					$('input[name="amount"]').removeClass('error');
					total_cal();
				}
			})
			.on('click','#save',function(e)
			{
				e.preventDefault();
				fiscal_folio	= [];
				ticket_number	= [];
				timepath		= [];
				amount			= [];
				datepath		= [];

				object = $(this);
				
				swal({
					title				: 'Cargando',
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});

				if ($('.datepath').length > 0) 
				{
					$('.datepath').each(function(i,v)
					{
						fiscal_folio.push($(this).parents('.docs-p').find('.fiscal_folio').val());
						ticket_number.push($(this).parents('.docs-p').find('.ticket_number').val());
						timepath.push($(this).parents('.docs-p').find('.timepath').val());
						amount.push($(this).parents('.docs-p').find('.amount').val());
						datepath.push($(this).parents('.docs-p').find('.datepath').val());
					});

					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('resource.validation-document') }}',
						data	: 
						{
							'fiscal_folio'	: fiscal_folio,
							'ticket_number'	: ticket_number,
							'timepath'		: timepath,
							'amount'		: amount,
							'datepath'		: datepath,
						},
						success : function(data)
						{

							flag = false;
							$('.datepath').each(function(j,v)
							{

								ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
								fiscal_folio	= $(this).parents('.docs-p').find('.fiscal_folio');
								timepath		= $(this).parents('.docs-p').find('.timepath');
								amount			= $(this).parents('.docs-p').find('.amount');
								datepath		= $(this).parents('.docs-p').find('.datepath');

								ticket_number.removeClass('error').removeClass('valid');
								fiscal_folio.removeClass('error').removeClass('valid');
								timepath.removeClass('error').removeClass('valid');
								amount.removeClass('error').removeClass('valid');
								datepath.removeClass('error').removeClass('valid');

								$(data).each(function(i,d)
								{
									if (d == fiscal_folio.val() || d == ticket_number.val()) 
									{
										ticket_number.removeClass('valid').addClass('error')
										fiscal_folio.removeClass('valid').addClass('error');
										timepath.removeClass('valid').addClass('error');
										amount.removeClass('valid').addClass('error');
										datepath.removeClass('valid').addClass('error');
										flag = true;
									}
									else
									{
										ticket_number.removeClass('error').addClass('valid')
										fiscal_folio.removeClass('error').addClass('valid');
										timepath.removeClass('error').addClass('valid');
										amount.removeClass('error').addClass('valid');
										datepath.removeClass('error').addClass('valid');
									}
								});
							});
							if (flag) 
							{
								swal('','Los documentos marcados ya se encuentran registrados.','error');
							}
						},
						error : function(data)
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					})
					.done(function(data)
					{
						if (!flag) 
						{
							send(object);
						}
					});
				}
				else
				{
					send(object);
				}

				function send(object) 
				{
					flag = false;
					$('.path').each(function()
					{
						path = $(this).val();
						if(path == "")
						{
							flag = true;
						}

					});


					if (!flag) 
					{
						$('.removeselect').removeAttr('required');
						$('.removeselect').removeAttr('data-validation');
						$('.request-validate').removeClass('request-validate');

						action	= object.attr('formaction');
						form	= $('#container-alta').attr('action',action);
						form.submit();
					}
					else
					{
						swal('','Por favor agregue los documentos faltantes.','error');
					}
				}
			})
			.on('click','[name="enviar"]',function(e)
			{
				e.preventDefault();
				fiscal_folio	= [];
				ticket_number	= [];
				timepath		= [];
				amount			= [];
				datepath		= [];

				object = $(this);
				swal({
					title				: 'Cargando',
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});

				if ($('.datepath').length > 0) 
				{
					$('.datepath').each(function(i,v)
					{
						fiscal_folio.push($(this).parents('.docs-p').find('.fiscal_folio').val());
						ticket_number.push($(this).parents('.docs-p').find('.ticket_number').val());
						timepath.push($(this).parents('.docs-p').find('.timepath').val());
						amount.push($(this).parents('.docs-p').find('.amount').val());
						datepath.push($(this).parents('.docs-p').find('.datepath').val());
					});

					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('resource.validation-document') }}',
						data	: 
						{
							'fiscal_folio'	: fiscal_folio,
							'ticket_number'	: ticket_number,
							'timepath'		: timepath,
							'amount'		: amount,
							'datepath'		: datepath,
						},
						success : function(data)
						{

							flag = false;
							$('.datepath').each(function(j,v)
							{

								ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
								fiscal_folio	= $(this).parents('.docs-p').find('.fiscal_folio');
								timepath		= $(this).parents('.docs-p').find('.timepath');
								amount			= $(this).parents('.docs-p').find('.amount');
								datepath		= $(this).parents('.docs-p').find('.datepath');

								ticket_number.removeClass('error').removeClass('valid');
								fiscal_folio.removeClass('error').removeClass('valid');
								timepath.removeClass('error').removeClass('valid');
								amount.removeClass('error').removeClass('valid');
								datepath.removeClass('error').removeClass('valid');

								$(data).each(function(i,d)
								{
									if (d == fiscal_folio.val() || d == ticket_number.val()) 
									{
										ticket_number.removeClass('valid').addClass('error')
										fiscal_folio.removeClass('valid').addClass('error');
										timepath.removeClass('valid').addClass('error');
										amount.removeClass('valid').addClass('error');
										datepath.removeClass('valid').addClass('error');
										flag = true;
									}
									else
									{
										ticket_number.removeClass('error').addClass('valid')
										fiscal_folio.removeClass('error').addClass('valid');
										timepath.removeClass('error').addClass('valid');
										amount.removeClass('error').addClass('valid');
										datepath.removeClass('error').addClass('valid');
									}
								});
							});
							if (flag) 
							{
								swal('','Los documentos marcados ya se encuentran registrados.','error');
							}
						},
						error : function(data)
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					})
					.done(function(data)
					{
						if (!flag) 
						{
							send(object);
						}
					});
				}
				else
				{
					send(object);
				}

				function send(object) 
				{
					flag = false;
					$('.path').each(function()
					{
						path = $(this).val();
						if(path == "")
						{
							flag = true;
						}

					});


					if (!flag) 
					{
						form = object.parents('form');
						form.submit();
					}
					else
					{					
						swal('','Por favor agregue los documentos faltantes.','error');
					}
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						$('.resultbank').hide();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
			})
			.on('click','.delete-item',function()
			{
				value = $(this).parent('div').parent('.tr').find('.idRefundDetail').val();
				del = $('<input type="hidden" name="delete[]">').val(value);
				$('#delete').append(del);
				$(this).parents('.tr').remove();
				total_cal();
				doc = $('#body .tr').length;
				$('#body .tr').each(function(i,v)
				{
					$(this).find('.num_path').attr('name','t_path'+i+'[]');
				});
				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on('click','input[name="method"]',function()
			{
				if($(this).val() == 1)
				{
					$('.resultbank').stop(true,true).slideDown().show();
				}
				else
				{
					$('.resultbank').stop(true,true).slideUp().hide();
				}
			})
			.on('click','.checkbox',function()
			{
				$('.marktr').removeClass('marktr');
				$(this).parents('.tr').addClass('marktr');
			})
			.on('click','#help-btn-method-pay',function()
			{
				swal('Ayuda','En este apartado debe seleccionar la forma de pago, si usted selecciona "Cuenta Bancaria", deberá elegir una de las cuentas que se le muestran del "Solicitante", en caso de no tener, favor de indicarle que agregue al menos una.','info');
			})
			.on('click','#help-btn-documents',function()
			{
				swal('Ayuda','En este apartado debe agregar cada uno de los conceptos que vaya a solicitar.','info');
			})
			.on('click','[name="addDoc"]',function()
			{
				@php
				
					$options = collect();
					$docsKind = ["Cotización","Ficha Técnica","Control de Calidad","Contrato","Factura","Ticket","Otro"];

					foreach ($docsKind as $kind)
					{
						$options = $options->concat([["value" => $kind, "description" => $kind]]);	
					}

					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput" => "new-input-text pathActioner",
						"attributeExRealPath" => "name=\"realPath[]\"",
						"classExRealPath" => "path",
						"componentsExUp" => 
						[
							["kind" => "components.labels.label", "label" => "Tipo de documento:"],
							["kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"nameDocument[]\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "nameDocument mb-6"]
						],
						"componentsExDown" =>
						[
							["kind" => "components.labels.label", "label" => "Selecciona la fecha:", "classEx" => "datepath hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"datepath[]\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\" step=\"1\"", "classEx" => "datepicker datepath hidden my-2"],
							["kind" => "components.labels.label", "label" => "Selecciona la hora:", "classEx" => "timepath hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\"", "classEx" => "timepath hidden my-2"],
							["kind" => "components.labels.label", "label" => "Folio Fiscal:","classEx" => "fiscal_folio hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\"", "classEx" => "fiscal_folio hidden my-2"],
							["kind" => "components.labels.label", "label" => "Número de Ticket:","classEx" => "ticket_number hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"ticket_number[]\" placeholder=\"Ingrese el número de ticket\"", "classEx" => "ticket_number hidden my-2"],
							["kind" => "components.labels.label", "label" => "Monto total:","classEx" => "amount hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"amount[]\" placeholder=\"Ingrese el total\"", "classEx" => "amount hidden my-2"],
						],
						"classExDelete" => "delete-doc",
					])->render();
				@endphp
				newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				containerNewDoc = $(newDoc);
				$('#documents-resource').append(containerNewDoc);
				$('[name="amount[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
				$('.datepicker').datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
				$('.timepath').daterangepicker({
					timePicker : true,
					singleDatePicker:true,
					timePicker24Hour : true,
					autoApply: true,
					locale : {
						format : 'HH:mm',
						"applyLabel": "Seleccionar",
						"cancelLabel": "Cancelar",
					}
				})
				.on('show.daterangepicker', function (ev, picker) 
				{
					picker.container.find(".calendar-table").remove();
				});
				$('#documents-resource').removeClass('hidden');
				@php
					$selects = collect([
						[
							"identificator"				=> ".nameDocument",
							"placeholder"				=> "Seleccione un tipo de documento",
							"maximumSelectionLength"	=> "1",
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('change','.fiscal_folio,.ticket_number,.timepath,.amount,.datepath',function()
			{
				$('.datepath').each(function(i,v)
				{
					row					= 0;
					first_fiscal		= $(this).parents('.docs-p').find('.fiscal_folio');
					first_ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
					first_amount		= $(this).parents('.docs-p').find('.amount');
					first_timepath		= $(this).parents('.docs-p').find('.timepath');
					first_datepath		= $(this).parents('.docs-p').find('.datepath');
					first_name_doc		= $(this).parents('.docs-p').find('.nameDocument option:selected').val();

					$('.datepath').each(function(j,v)
					{

						scnd_fiscal			= $(this).parents('.docs-p').find('.fiscal_folio');
						scnd_ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
						scnd_amount			= $(this).parents('.docs-p').find('.amount');
						scnd_timepath		= $(this).parents('.docs-p').find('.timepath');
						scnd_datepath		= $(this).parents('.docs-p').find('.datepath');
						scnd_name_doc		= $(this).parents('.docs-p').find('.nameDocument option:selected').val();
						scnd_doc			= $(this).parents('.docs-p').find('.datepath').val();

						if (i!==j) 
						{
							if (first_name_doc == "Factura") 
							{
								if (first_fiscal.val() != "" && first_timepath.val() != "" && first_datepath.val() != ""  && scnd_datepath.val() != "" && scnd_timepath.val() != "" && scnd_fiscal.val() != "" && first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_fiscal.val().toUpperCase() == scnd_fiscal.val().toUpperCase()) 
								{
									swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
									scnd_fiscal.val('').removeClass('valid').addClass('error');
									scnd_timepath.val('').removeClass('valid').addClass('error');
									scnd_datepath.val('').removeClass('valid').addClass('error');
									$(this).parents('.docs-p').find('span.form-error').remove();
									return;
								}
							}

							if (first_name_doc == "Ticket") 
							{
								if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_ticket_number.val().toUpperCase() == scnd_ticket_number.val().toUpperCase() && first_amount.val() == scnd_amount.val()) 
								{
									swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
									scnd_ticket_number.val('').addClass('error');
									scnd_timepath.val('').addClass('error');
									scnd_datepath.val('').addClass('error');
									scnd_amount.val('').addClass('error');
									$(this).parents('.docs-p').find('span.form-error').remove();
									return;
								}
							}
						}

					});
				});
			})
			.on('change','.nameDocument',function()
			{
				type_document = $('option:selected',this).val();
				switch(type_document)
				{
					case 'Factura': 
						$(this).parents('.docs-p').find('.fiscal_folio').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.ticket_number').hide().val('');
						$(this).parents('.docs-p').find('.amount').hide().val('');
						$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
					case 'Ticket': 
						$(this).parents('.docs-p').find('.fiscal_folio').hide().val('');
						$(this).parents('.docs-p').find('.ticket_number').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.amount').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
					default :  
						$(this).parents('.docs-p').find('.fiscal_folio').hide().val('');
						$(this).parents('.docs-p').find('.ticket_number').hide().val('');
						$(this).parents('.docs-p').find('.amount').hide().val('');
						$(this).parents('.docs-p').find('.timepath').hide().val('');
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
				}
			})
			.on('change','.new-input-text.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData	= new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route('resource.upload') }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					})
				}
			})
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				actioner		= $(this);
				uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ url("/administration/resource/upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('.docs-p').remove();

				if($('.docs-p').length<1)
				{
					$('#documents-resource').addClass('hidden');
				}
			})
		});

		function total_cal()
		{
			subtotal	= 0;
			$("#body .tr").each(function(i, v)
			{
				temp     =  Number($(this).find('.t-amount').val());
				subtotal += temp;
			});
			total = Number(subtotal);
			$(".total").val('$' +total.toFixed(2));
			$(".total").text('$' +total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		}
	</script>
@endsection
