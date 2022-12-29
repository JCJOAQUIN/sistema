@extends('layouts.child_module')
@section('data')
	@if(isset($request))
		@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('resource.store')."\" id=\"container-alta\"", "files"=>true])
			@component('components.labels.title-divisor') NUEVA SOLICITUD @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
						@endslot
						@slot('attributeEx')
							name="title"
							placeholder="Ingrese el título"
							data-validation="required"
							value="{{ $request->resource->first()->title }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
							datepicker
						@endslot
						@slot('attributeEx')
							name="datetitle"
							value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y') }}"
							data-validation="required"
							placeholder="Ingrese la fecha"
							readonly="readonly"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->idRequest)
						{
							$user = App\User::find($request->idRequest);
							$options = $options->concat([["value"=>$user->id, "selected"=>"selected","description"=>$user->name. " " .$user->last_name. " " .$user->scnd_last_name]]);
						}						
						$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"";
						$classEx	 = "js-users removeselect select-error";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options = collect();
						foreach($enterprises as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([["value" => $enterprise->id,"selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value" => $enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\"";
						$classEx	 = "js-enterprises removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
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
						$attributeEx = "name=\"area_id\" multiple=\"multiple\" id=\"multiple-areas\" data-validation=\"required\"";
						$classEx	 = "js-areas removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$options = collect();
						foreach($departments as $department)
						{
							$description = $department->name;
							if($request->idDepartment == $department->id)
							{
								$options = $options->concat([["value"=>$department->id, "selected"=>"selected","description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
							}
						}
						$attributeEx = "name=\"department_id\" multiple=\"multiple\" id=\"multiple-departments\" data-validation=\"required\"";
						$classEx	 = "js-departments removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto: @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->idProject)
						{
							$project = App\Project::find($request->idProject);
							$options = $options->concat([["value"=>$project->idproyect, "selected"=>"selected","description"=>$project->proyectName]]);
						}
						$attributeEx = "name=\"project_id\" multiple=\"multiple\" id=\"multiple-projects\" data-validation=\"required\"";
						$classEx	 = "js-projects removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2 select_father @if(!isset($request->code_wbs)) hidden @endif" >
					@component('components.labels.label') Código WBS: @endcomponent
					@php
						$options = collect();
						if(isset($request) && $request->code_wbs)
						{
							$code = App\CatCodeWBS::find($request->code_wbs);
							$options = $options->concat([["value"=>$code->id, "selected"=>"selected","description"=>$code->code_wbs]]);
						}						
						$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="code-edt col-span-2 @if(!isset($request->code_edt)) hidden @endif">
					<div class="col-span-2">
						@component('components.labels.label') Código EDT: @endcomponent
						@php
							$options = collect();
							if(isset($request) && $request->code_edt)
							{
								$edt = App\CatCodeEDT::find($request->code_edt);
								$options = $options->concat([["value"=>$edt->id, "selected"=>"selected", "description"=>$edt->code.' ('.$edt->description.')']]);
							}
							$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"";
							$classEx = "js-code_edt removeselect";
						@endphp
						@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
					</div>
				</div>
				<div class="col-span-2 check_balance"></div>
			@endcomponent
			@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->resource->first()->idpaymentMethod == 1 ? " checked" : ""),
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->resource->first()->idpaymentMethod == 2 ? " checked" : ""),
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->resource->first()->idpaymentMethod == 3 ? " checked" : ""),
					],							
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
			@component('components.inputs.input-text')
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
				@component('components.labels.title-divisor') SELECCIONE UNA CUENTA: @endcomponent
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

					foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->resource->first()->idUsers)->get() as $bank)
					{
						$marktr 	= "";
						$checked	= "";
						if($request->resource->first()->idEmployee == $bank->idEmployee)
						{
							$marktr = "marktr";
						}

						if($request->resource->first()->idEmployee == $bank->idEmployee) 
						{
							$checked = 'checked';
						}
						
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
										"attributeEx"		=> "id=\"id".$bank->idEmployee."\" name=\"idEmployee\" type=\"radio\" value=\"".$bank->idEmployee."\" ".$checked,
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
						$modelBody[] = $body;
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
					@component('components.labels.label') Referencia: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
						@endslot
						@slot('attributeEx')
							name="reference"
							placeholder="Ingrese la referencia"
							value="{{ $request->resource->first()->reference }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
					@php
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
						$attributeEx = "name=\"currency\" data-validation=\"required\"";
						$classEx = "js-currency removeselect";
					@endphp
					@component('components.inputs.select',["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])  @endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Concepto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="concept"
							placeholder="Ingrese el concepto"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@php
						$options = collect();						
						$attributeEx = "name=\"account_id\"";
						$classEx = "js-accounts removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							amount
						@endslot
						@slot('attributeEx')
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
			@php
				$body = [];
				$modelBody = [];
				$modelHead = 
				[
					[
						["value"=>"#"],
						["value"=>"Concepto"],
						["value"=>"Clasificación de gasto"],
						["value"=>"Importe"]
					]
				];

				if($request->status == 2)
				{
					$modelHead[0] = [["value"=>""]];
				}

				$subtotalFinal = $ivaFinal = $totalFinal = 0;

				foreach($request->resource->first()->resourceDetail as $key=>$resourceDetail)
				{
					$totalFinal		+= $resourceDetail->amount;
					$body = 
					[
						"classEx" => "tr",
						[
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
									"label" => "$ ".number_format($resourceDetail->amount,2),
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "input-table t-amount",
									"attributeEx" 	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$resourceDetail->amount."\"",
								]
							]
						],
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
						]
					];
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
								"attributeEx" => "id=\"total\" type=\"hidden\" readonly=\"readonly\" name=\"total\" placeholder=\"$0.00\" value=\""."$".number_format($totalFinal,2)."\""
							]
						]
					]
				];
			@endphp
			@component('components.templates.outputs.form-details',[
					"modelTable" => $modelTable,
				])
			@endcomponent
			@component('components.labels.title-divisor') CARGAR DOCUMENTOS @endcomponent
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
			<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
				@component('components.buttons.button',["variant"=>"primary"])
					@slot('attributeEx')
						type="submit"
						name="enviar"
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
						formaction="{{ route('resource.unsent') }}"
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
				@component('components.buttons.button', ["variant"=>"reset"])
					@slot('classEx')
						btn-delete-form
					@endslot
					@slot('attributeEx')
						type="reset"
						name="borra"
						value="Borrar Campos"
					@endslot
					BORRAR CAMPOS
				@endcomponent
			</div>
		@endcomponent	
	@else
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route('resource.store')."\" id=\"container-alta\"", "files"=>true])
			@component('components.labels.title-divisor') NUEVA SOLICITUD @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
						@endslot
						@slot('attributeEx')
							name="title"
							placeholder="Ingrese el título" data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
							datepicker
						@endslot
						@slot('attributeEx')
							name="datetitle"
							data-validation="required"
							placeholder="Ingrese la fecha"
							readonly="readonly"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options = collect();						
						$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"";
						$classEx = "js-users removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options = collect();
						foreach($enterprises as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
						$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\"";
						$classEx = "js-enterprises removeselect select-error";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
						$options = collect();
						foreach($areas as $area)
						{
							$description = $area->name;
							$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
						}
						$attributeEx = "multiple=\"multiple\" name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"";
						$classEx = "js-areas removeselect input-text";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$options = collect();
						foreach($departments as $department)
						{
							$description = $department->name;
							$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
						}
						$attributeEx = "name=\"department_id\" multiple=\"multiple\" id=\"multiple-departments\" data-validation=\"required\"";
						$classEx	 = "js-departments removeselect";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto: @endcomponent
					@php						
						$attributeEx = "data-validation=\"required\" name=\"project_id\" multiple=\"multiple\" id=\"multiple-projects\"";
						$classEx = "js-projects removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="col-span-2 select_father @if(!isset($request)) hidden @endif ">
					@component('components.labels.label') Código WBS: @endcomponent
					@php						
						$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="code-edt col-span-2 @if(!isset($request->code_edt)) hidden @endif">
					<div class="col-span-2">
						@component('components.labels.label') Código EDT: @endcomponent
						@php							
							$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"";
							$classEx = "js-code_edt removeselect";
						@endphp
						@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
					</div>
				</div>
			@endcomponent
			<div class="check_balance"></div>
			@component('components.labels.title-divisor') FORMA DE PAGO<span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"",
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\" checked",
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"",
					],							
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
			<div class="resultbank hidden"></div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Referencia: (Opcional) @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							removeselect
						@endslot
						@slot('attributeEx')
							name="reference"
							placeholder="Ingrese la referencia"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
						@php
							$options = collect(
								[
									["value"=>"MXN", "description"=>"MXN"],
									["value"=>"USD", "description"=>"USD"],
									["value"=>"EUR", "description"=>"EUR"],
									["value"=>"Otro", "description"=>"Otro"],
								]
							);

							$attributeEx = "name=\"currency\" data-validation=\"required\"";
							$classEx = "js-currency removeselect";
						@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>	
			@endcomponent		
			@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS <span class="help-btn" id="help-btn-documents"></span> @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Concepto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="concept"
							placeholder="Ingrese el concepto"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@php
						$attributeEx = "name=\"account_id\"";
						$classEx = "js-accounts removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
				<div class="col-span-2">	
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							amount
						@endslot
						@slot('attributeEx')
							name="amount"
							placeholder="Ingrese el importe"
						@endslot
					@endcomponent
				</div>	
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button", ["variant" => "warning"])
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
			@php
				$body = [];
				$modelBody = [];
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
								"classEx" => "total"
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "total",
								"attributeEx" => "id=\"total\" type=\"hidden\" readonly=\"readonly\" name=\"total\" placeholder=\"$0.00\""
							]
						]
					]
				];
			@endphp
			@component('components.templates.outputs.form-details',[
				"modelTable" => $modelTable,				
			])
			@endcomponent
			<div id="paths"></div>
			@component('components.labels.title-divisor') CARGAR DOCUMENTOS @endcomponent
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
			<div class="content-start items-start flex flex-row flex-wrap justify-center w-full">
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
						formaction="{{ route('resource.unsent') }}"
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
				@component('components.buttons.button', ["variant"=>"reset"])
					@slot('classEx')
						btn-delete-form
					@endslot
					@slot('attributeEx')
						type="reset"
						name="borra"
						value="Borrar Campos"
					@endslot
					BORRAR CAMPOS
				@endcomponent
			</div>
		@endcomponent
	@endif
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
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length>0)
					{
						conceptos	= $('#body .tr').length;
						check 		=  $('.checkbox:checked').length;
						method 		= $('input[name="method"]:checked').val();
						if(conceptos>0 && method != undefined)
						{
							if (method == 1) 
							{
								if (check>0) 
								{
									swal('Cargando',{
										icon: '{{ asset(getenv('LOADING_IMG')) }}',
										button: false,
										closeOnClickOutside: false,
										closeOnEsc: false
									});
									return true;
								}
								else
								{
									swal('', 'Debe seleccionar una cuenta', 'error');
									return false;
								}
							}
							else
							{
								swal('Cargando',{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
								return true;
							}
							
						}
						else if (method == undefined) 
						{
							swal('', 'Debe seleccionar un método de pago', 'error');
							return false;
						}
						else
						{
							swal('', 'Todos los campos son requeridos', 'error');
							return false;
						}
					}
					else
					{
						swal('Cargando',{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
						});
						return true;
					}
				}
			});
			$('[name="amount"]').on("contextmenu",function(e)
			{
				return false;
			});
			$('.amount').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$(function() 
			{
				$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
			$(document).on('click','#add',function()
			{
				countConcept	= $('.countConcept').length;
				concept			= $('input[name="concept"]').val().trim();
				amount       	= Number($('input[name="amount"]').val().trim());
				account			= $('.js-accounts :selected').text();
				id_account		= $('.js-accounts :selected').val();
				$('.js-accounts').parent().find('.form-error').remove();
				$('input[name="concept"]').removeClass('error');
				$('input[name="amount"]').removeClass('error');
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
				if(amount == 0 && amount != "")
				{
					swal('', 'El importe no puede ser 0.', 'error');
					$('input[name="amount"]').addClass('error');
					return false;
				}
				if(concept == "" && account == "" && amount == "")
				{
					swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
					$('input[name="concept"]').addClass('error');
					$('.js-accounts').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					$('input[name="amount"]').addClass('error');
					return false;
				}
				if (concept == "" || account == "" || amount == "")
				{
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
						swal('', 'La cantidad no puede ser cero', 'error');
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
			.on('change', '.js-users', function()
			{
				id	= $('option:selected',this).val();
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('resource.get-accounts-employee') }}',
					data	: {'idUsers':id},
					success :function(data)
					{
						$('.resultbank').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.resultbank').html('');
					}
				});
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
										ticket_number.removeClass('valid').addClass('error');
										fiscal_folio.removeClass('valid').addClass('error');
										timepath.removeClass('valid').addClass('error');
										amount.removeClass('valid').addClass('error');
										datepath.removeClass('valid').addClass('error');
										flag = true;
									}
									else
									{
										ticket_number.removeClass('error').addClass('valid');
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
					}).done(function(data)
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
						swal('','Tiene documentos sin agregar','error');
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
					text				: 'Validando los documentos',
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
						form	= object.parents('form');
						form.submit();
					}
					else
					{
						swal('','Tiene documentos sin agregar','error');
					}
				}
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
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
			.on('click','.delete-item',function()
			{
				$(this).parents('.tr').remove();
				total_cal();
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
							["kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"nameDocument[]\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "nameDocument mb-6"],
						],
						"componentsExDown" =>
						[
							["kind" => "components.labels.label", "label" => "Seleccione la fecha:", "classEx" => "datepath hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"datepath[]\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\" step=\"1\"", "classEx" => "datepicker datepath hidden my-2"],
							["kind" => "components.labels.label", "label" => "Seleccione la hora:", "classEx" => "timepath hidden"],["kind" => "components.inputs.input-text", "attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\"", "classEx" => "timepath hidden my-2"],
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
				$(this).removeClass('error');
				object 		= $(this);
				flag 		= false;
				duplicate	= '';
				$('.docs-p').each(function(i,v)
				{	
					firstFiscalFolio	= $(this).find('[name="folio_fiscal[]"]').val();
					firstTicketNumber	= $(this).find('[name="ticket_number[]"]').val();
					firstAmount			= $(this).find('[name="amount[]"]').val();
					firstTimepath		= $(this).find('[name="timepath[]"]').val();
					firstDatepath		= $(this).find('[name="datepath[]"]').val();
					firstNameDoc		= $(this).find('[name="nameDocument[]"] option:selected').val();
					$('.docs-p').each(function(j,v)
					{
						if(i!==j)
						{
							scndFiscalFolio		= $(this).find('[name="folio_fiscal[]"]').val();
							scndTicketNumber	= $(this).find('[name="ticket_number[]"]').val();
							scndAmount			= $(this).find('[name="amount[]"]').val();
							scndTimepath		= $(this).find('[name="timepath[]"]').val();
							scndDatepath		= $(this).find('[name="datepath[]"]').val();
							scndNameDoc			= $(this).find('[name="nameDocument[]"] option:selected').val();
							if(firstNameDoc == "Factura" && scndNameDoc == "Factura" )
							{
								if (firstFiscalFolio != "" && firstTimepath != "" && firstDatepath != "" && firstDatepath == scndDatepath && firstTimepath == scndTimepath && firstFiscalFolio.toUpperCase() == scndFiscalFolio.toUpperCase())
								{
									duplicate 	= 'folio fiscal "'+firstFiscalFolio+'"';
									flag = true;
								}
							}
							if(firstNameDoc == "Ticket" && scndNameDoc == "Ticket" )
							{
								if(firstTicketNumber != "" && firstAmount != "" && firstTimepath != "" && firstDatepath != "" && scndNameDoc == firstNameDoc && scndDatepath == firstDatepath && scndTimepath == firstTimepath && scndTicketNumber.toUpperCase() == firstTicketNumber.toUpperCase() && Number(scndAmount).toFixed(2) == Number(firstAmount).toFixed(2))
								{
									duplicate 	= 'número de ticket "'+firstTicketNumber+'"';
									flag = true;
								}
							}
						}
					});
				});
				if(flag)
				{
					swal('', 'El documento con '+duplicate+' ya se encuentra registrado, por favor verifique los datos.', 'error');
					object.parent().find('[name="folio_fiscal[]"]').val('').addClass('error');
					object.parent().find('[name="ticket_number[]"]').val('').addClass('error');
					object.parent().find('[name="amount[]"]').val('').addClass('error');
					object.parent().find('[name="timepath[]"]').val('').addClass('error');
					object.parent().find('[name="datepath[]"]').val('').addClass('error');
					return false;
				}
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
				uploadedName	= $(this).parent('.docs-p').find('input[name="realPath[]"]');
				formData		= new FormData();
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
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('div.docs-p').remove();
				
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
