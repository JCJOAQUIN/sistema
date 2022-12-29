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
	@php
		$enterpriseSelected = $areaSelected = $departmentSelected = $userSelected = $projectSelected = '';
		$docs 	= 0;
		$taxes 	= 0;
	@endphp
	@component('components.forms.form', [ "attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('expenses.follow.update',$request->folio)."\"", "methodEx" => "PUT", "files" => true])
		@component('components.labels.title-divisor') Folio: {{ $request->folio }} @endcomponent
		@php
			$elaborate = App\User::find($request->idElaborate);
		@endphp
		@component('components.labels.subtitle')
			Elaborado por: {{ $elaborate->name }} {{ $elaborate->last_name }} {{ $elaborate->scnd_last_name }}
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" 
						name="title"
						placeholder="Ingrese el título" 
						data-validation="required" 
						@if(isset($request)) value="{{ $request->expenses->first()->title }}" @endif 
						@if($request->status!=2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						name="datetitle" 
						@if(isset($request->expenses->first()->datetitle)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->expenses->first()->datetitle)->format('d-m-Y') }}" @endif 
						data-validation="required" 
						placeholder="Ingrese la fecha" 
						readonly="readonly" 
						@if($request->status!=2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect 
						datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Nombre del solicitante: @endcomponent
				@php
					$optionUser = [];
					if(isset($request->idRequest) && $request->idRequest !="")
					{
						$optionUser[] 	= ["value" => $request->idRequest, "description" => $request->requestUser->fullName(), "selected" => "selected"];
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionUser])
				@slot('attributeEx')
					name="user_id" 
					multiple="multiple"
					data-validation="required" 
					@if($request->status != 2) disabled="disabled" @endif
				@endslot
				@slot('classEx')
					js-users 
					removeselect
				@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Folio de recurso:
				@endcomponent
				@foreach($request->expenses as $expenses)
					@php
						$optionResource = [];
						if(isset($request) && $request->status==2)
						{
							$resourceList = array();
							$key = 0;
							foreach(App\RequestModel::where('kind',8)->whereIn('status',[5,10,11,12,18])->where('idRequest',$request->idRequest)->get() as $value)
							{
								if($value->resource->first()->expensesRequest->count()==0)
								{
									$resourceList[$key]['folio'] = $value->folio;
									$resourceList[$key]['nombre'] = isset($value->resource->first()->title) ? $value->resource->first()->title : null;
									$key++;	
								}
								else 
								{
									$flag = true;
									foreach($value->resource->first()->expensesRequest as $expenses)
									{
										if($expenses->requestModel->status!=2 && $expenses->requestModel->status!=6 && $expenses->requestModel->status!=7 && $expenses->requestModel->status!=13)
										{
											$flag = false;
										}
									}
									if($flag)
									{
										$resourceList[$key]['folio'] = $value->folio;
										$resourceList[$key]['nombre'] = isset($value->resource->first()->title) ? $value->resource->first()->title : null;
										$key++;
									}
								}
							}
							foreach($resourceList as $list)
							{
								if($request->expenses->first()->resourceId == $list['folio'])
								{
									$optionResource[] = ["value" => $list['folio'], "description" => $list['folio'].' - '.$list['nombre'], "selected" => "selected"];
								}
								else
								{
									$optionResource[] = ["value" => $list['folio'], "description" => $list['folio'].' - '.$list['nombre']];
								}
							}
						}
						else
						{
							$optionResource[] = ["value" =>  $request->expenses->first()->resourceId, "description" => $request->expenses->first()->resourceId.' - '.$request->expenses->first()->title, "selected" => "selected"];
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionResource])
						@slot('attributeEx')
							multiple="multiple" 
							name="resources_id" 
							data-validation="required" 
							@if($request->status != 2) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							js-resources 
							removeselect
						@endslot	
					@endcomponent
				@endforeach
			</div>
		@endcomponent
		@if(isset($request->expenses->first()->resourceId) && $request->expenses->first()->resourceId != "")
			<div id="dates" class="mt-4">
				@php
					$modelTable =
					[
						["Empresa:", 		$request->requestEnterprise->name],
						["Dirección:", 		$request->requestDirection->name ],
						["Departamento:",	$request->requestDepartment->name],
						["Proyecto:",  		isset($request->requestProject->proyectName) ? $request->requestProject->proyectName : "---"],
						["Código WBS:", 	isset($request->wbs) ? $request->wbs->code_wbs : "---"],	 
						["Código EDT:", 	isset($request->edt) ? $request->edt->fullName() : "---"]
					];
				@endphp	
				@component('components.templates.outputs.table-detail',
					[
						"modelTable"	=> $modelTable,
						"title"			=> "Detalles de la Solicitud"
					])
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="enterprise_id" value="{{ $request->requestEnterprise->id }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="area_id" value="{{ $request->requestDirection->id }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="department_id" value="{{ $request->requestDepartment->id }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="project_id" value="{{ $request->requestProject->idproyect }}"
					@endslot
				@endcomponent
				@isset($request->wbs)
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="wbs_id" value="{{$request->wbs->id}}"
						@endslot
					@endcomponent
					@isset($request->edt)
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" name="edt_id" value="{{$request->edt->id}}"
							@endslot
						@endcomponent
					@endisset
				@endisset
			</div>
		@else
			<div id="dates"></div>
		@endif
		@if($request->status != 2)
			@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->expenses->first()->idpaymentMethod == 1 ? " checked" : "")." disabled",
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->expenses->first()->idpaymentMethod == 2 ? " checked" : "")." disabled",
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->expenses->first()->idpaymentMethod == 3 ? " checked" : "")." disabled",
					],								
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" 
					name="employee_number"
					id="efolio" 
					placeholder="Ingrese el número de empleado" 
					value="@foreach($request->expenses as $expense){{ $expense->idUsers }}@endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank @if($request->expenses->first()->idpaymentMethod == 1) block @else hidden @endif">
				@component('components.labels.title-divisor') Cuenta @endcomponent
				@php
					$body 		= [];
					$modelBody 	= [];
					$modelHead	= ["Acción","Banco","Alias","Número de tarjeta","CLABE","Número de cuenta"];
					
					foreach($request->expenses as $expense)
					{
						foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->where('visible',1)->get() as $bank)
						{
							$class 		= '';
							$checked	= '';
							if($expense->idEmployee == $bank->idEmployee)
							{
								$modelBody[] = [ "classEx" => "marktr",
									[
										"content" =>
										[
											[
												"kind"				=> "components.inputs.checkbox",
												"attributeEx" 		=> "id=\"idEmp$bank->idEmployee\" disabled type=\"radio\" name=\"idEmployee\" value=\"".$bank->idEmployee."\" checked",
												"classEx"			=> "checkbox",
												"classExLabel"		=> "request-validate disabled",
												"label"				=> "<span class=\"icon-check\"></span>",
												"classExContainer"	=> "my-2",
												"radio"				=> true
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => $bank->description
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => isset($bank->alias) ? $bank->alias : '---'
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => isset($bank->cardNumber) ? $bank->cardNumber : '---'
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => isset($bank->clabe) ? $bank->clabe : '---'
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => isset($bank->account) ? $bank->account : '---'
											]
										]
									]
								];
							}
						}
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
						"modelBody" => $modelBody,
						"modelHead" => $modelHead
					])
					@slot('attributeEx')
						id="table2"
					@endslot
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent
			</div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Referencia: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" 
							name="reference"
							@foreach($request->expenses as $expense) @if($expense->reference != "") value="{{ $expense->reference }}" @endif @endforeach placeholder="Ingrese la referencia" disabled
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" 
							name="currency"
							@foreach($request->expenses as $expense) @if($expense->currency != "") value="{{ $expense->currency }}" @endif @endforeach placeholder="Seleccione el tipo de moneda" disabled
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@else
			@component('components.labels.title-divisor') FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span>  @endcomponent
			@php
				$buttons = 
				[
					[
						"textButton" 		=> "Cuenta Bancaria",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"1\" id=\"accountBank\"".($request->expenses->first()->idpaymentMethod == 1 ? " checked" : ""),
					],
					[
						"textButton" 		=> "Efectivo",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"2\" id=\"cash\"".($request->expenses->first()->idpaymentMethod == 2 ? " checked" : $request->expenses->first()->idpaymentMethod == "" ? " checked" : ""),
					],
					[
						"textButton" 		=> "Cheque",
						"attributeButton" 	=> "type=\"radio\" name=\"method\" value=\"3\" id=\"checks\"".($request->expenses->first()->idpaymentMethod == 3 ? " checked" : ""),
					],								
				];
			@endphp
			@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" 
					name="employee_number"
					id="efolio" 
					placeholder="Ingrese el número de empleado" 
					value="@foreach($request->expenses as $expense){{ $expense->idUsers }}@endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank table-responsive @if($request->expenses->first()->idpaymentMethod == 1) block @else hidden @endif">
				@if($request->idRequest != "")
					@foreach($request->expenses as $expense)
						@if(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() != "")
							@component('components.labels.title-divisor') Cuenta @endcomponent
							@php
								$body 		= [];
								$modelBody 	= [];
								$modelHead	= ["Acción","Banco","Alias","Número de tarjeta","CLABE","Número de cuenta"];
				
								foreach($request->expenses as $expense)
								{
									foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->where('visible',1)->get() as $bank)
									{
										$class 		= '';
										$checked	= '';
										if($expense->idEmployee == $bank->idEmployee)  
										{
											$class		= "marktr";
											$checked	= "checked";
										}
										$body = [ "classEx" => $class,
											[
												"content" => 
												[
													[
														"kind"				=> "components.inputs.checkbox",
														"attributeEx" 		=> "id=\"idEmp$bank->idEmployee\" \"disabled\" type=\"radio\" name=\"idEmployee\" value=\"".$bank->idEmployee."\"".' '.$checked,
														"classEx"			=> "checkbox",
														"classExLabel"		=> "request-validate",
														"label"				=> "<span class=\"icon-check\"></span>",
														"classExContainer"	=> "my-2",
														"radio"				=> true
													]
												]
											],
											[
												"content" =>
												[
													[
														"label" => $bank->description
													]
												]
											],
											[
												"content" =>
												[
													[
														"label" => isset($bank->alias) ? $bank->alias : '---'
													]
												]
											],
											[
												"content" =>
												[
													[
														"label" => isset($bank->cardNumber) ? $bank->cardNumber : '---'
													]
												]
											],
											[
												"content" =>
												[
													[
														"label" => isset($bank->clabe) ? $bank->clabe : '---'
													]
												]
											],
											[
												"content" =>
												[
													[
														"label" => isset($bank->account) ? $bank->account : '---'
													]
												]
											]
										];
										$modelBody[] = $body;
									}
								}
							@endphp
							@component('components.tables.alwaysVisibleTable', [
									"modelBody" => $modelBody,
									"modelHead" => $modelHead
								])
								@slot('attributeEx')
									id="table2"
								@endslot
								@slot('classExBody')
									request-validate
								@endslot
							@endcomponent
						@endif
					@endforeach
				@endif
			</div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Referencia: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" 
							name="reference"
							placeholder="Ingrese la referencia"
							@foreach($request->expenses as $expense) @if($expense->reference != "") value="{{ $expense->reference }}" @endif @endforeach
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
						$valueCurrency	= ['MXN','USD','EUR','Otro'];
						foreach ($valueCurrency as $v)
						{
							if($v == $request->expenses->first()->currency)
							{
								$optionCurrency[] = ["value" => $v, "description" => $v, "selected" => "selected"];
							}
							else
							{
								$optionCurrency[] = ["value" => $v, "description" => $v];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionCurrency])
						@slot('attributeEx')
							multiple="multiple"
							name="currency"
							data-validation="required"
						@endslot
						@slot('classEx')
							js-currency
							removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endif
		@if($request->status == "2")
			@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS <span class="help-btn" id="help-btn-documents"></span> @endcomponent
			<div class="flex row justify-center space-x-2 my-4">  
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							name="exist_new" 
							id="doc_exist" 
							value="exist" 
							@if($request->expenses->first()->resourceId != "") checked="checked" @endif
						@endslot
							Existente
					@endcomponent
				</div>
				<div>
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							name="exist_new" 
							id="doc_new" 
							value="new"
						@endslot
							Nuevo
					@endcomponent
				</div>
			</div>
			<div id="docs_exist" class="@if($request->expenses->first()->resourceId != "") block @else hidden @endif">
				<div id="documents-resource">
					@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS DE SOLICITUD DE RECURSO @endcomponent
					@php
						$body		=[];
						$modelBody	=[];
						$modelHead	=[
							[
								["value" => "Concepto"],
								["value" => "Clasificación de gasto"],
								["value" => "Importe"],
								["value" => "Acción"],
							]
						];
						
						$subtotalFinal = $ivaFinal = $totalFinal = 0;
						if($request->expenses->first()->resourceId != "")
						{
							foreach(App\Resource::where('idFolio',$request->expenses->first()->resourceId)->get() as $resource)
							{
								foreach ($resource->resourceDetail->where('statusRefund',0) as $resourceDetail)
								{
									$body = [	"classEx" => "tr_detail",
										[
											"content" =>
											[
												[
													"label" => $resourceDetail->concept
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx"		=> "concept-table",
													"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->concept."\""
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx"		=> "idresourcedetail-table",
													"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idresourcedetail."\""
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "account-table",
													"attributeEx" 	=> "type=\"hidden\" value=\"".$resourceDetail->accountsReview->account." ".$resourceDetail->accountsReview->description."\"",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "accountid-table",
													"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idAccAccR."\""
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
													"classEx"		=> "amount-table",
													"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->amount."\""
												]
											]
										],
										[
											"content" =>
											[
												"kind"			=> "components.buttons.button",
												"variant"		=> "warning",
												"attributeEx"	=> "type=\"button\"",
												"classEx"		=> "add-concept",
												"label"			=> "<span class=\"icon-plus\"></span>"
											]
										]
									];
									$modelBody[] = $body;
								}
							}
						}
					@endphp
					@component('components.tables.table', [
						"modelHead" => $modelHead,
						"modelBody"	=> $modelBody,
					])
						@slot('classEx')
							mt-2
						@endslot
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('attributeExBody')
							id="body-classify"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot					
					@endcomponent
				</div>
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" 
								name="idresourcedetail_exist"
							@endslot
							@slot('classEx')
								idResourceDetail
								mb-4
							@endslot
						@endcomponent
						@component('components.labels.label') Concepto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" 
								name="concept_exist"
								placeholder="Ingrese el concepto" 
								readonly
							@endslot
							@slot('classEx')
								input-all
								mb-4
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" 
								name="account_id_exist"
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" 
								name="account_exist"
								placeholder="Ingrese la clasificación del gasto"
								readonly
							@endslot
							@slot('classEx')
								input-all
								mb-4
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Subtotal: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" 
								name="amount_exist"
								placeholder="Ingrese el subtotal"
							@endslot
							@slot('classEx')
								removeselect
								amount
								amount_exist
								mb-4
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">			 
						@component('components.labels.label') Fiscal: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										type="radio" 
										name="fiscal_exist"
										id="nofiscal_exist" 
										value="no" 
										checked
									@endslot
									@slot('classEx')
										fiscal
										fiscal_on
									@endslot
										No
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										type="radio" 
										name="fiscal_exist"
										id="fiscal_exist" 
										value="si"
									@endslot
									@slot('classEx')
										fiscal
									@endslot
										Sí
								@endcomponent
							</div>
						</div>
					</div>
					<div class="col-span-2">
						@component('components.labels.label') IVA: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled"
										type="radio" 
										name="iva_exist" 
										id="noiva_exist" 
										value="no" 
										checked="true"
									@endslot
									@slot('classEx')
										iva
									@endslot
										No
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_exist" 
										id="siiva_exist" 
										value="si"
									@endslot
									@slot('classEx')
										iva
									@endslot
										Sí 
								@endcomponent
							</div>
						</div>
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Tipo de IVA: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_kind_exist" 
										id="iva_a_exist" 
										value="a" 
										checked 
									@endslot
									@slot('classEx')
										iva_kind
									@endslot
									@slot('attributeExLabel')
										title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
									@endslot
										Tipo A
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_kind_exist" 
										id="iva_b_exist" 
										value="b" 
									@endslot
									@slot('classEx')
										iva_kind 
									@endslot
									@slot('attributeExLabel')
										title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
									@endslot
										Tipo B
								@endcomponent
							</div>
						</div>
					</div>
					<div class="md:col-span-4 col-span-2">
						@component('components.templates.inputs.taxes',[
							'type'	=> 'taxes',
							'name' 	=> 'additional_exist',
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								readonly 
								placeholder="Ingrese el importe"
								name="contentMoney"
								type="text"
							@endslot
							@slot('classEx')
								contentMoney
							@endslot
						@endcomponent
					</div>
					<div id="documents_exist" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"></div>
					<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type="button" 
								name="addDoc_exist" 
								id="addDoc_exist"
							@endslot
							@slot('classEx')
								mt-4
							@endslot
								<span class="icon-plus"></span>
								<span>Agregar documento</span>
						@endcomponent
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx')
								type="button" 
								name="add_exist"
							@endslot
							@slot('classEx')
								add
								mt-4
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar concepto</span>
						@endcomponent
					</div>
				@endcomponent
			</div>
			<div id="docs_new" class="hidden">
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden" 
								name="idresourcedetail_new"
							@endslot
							@slot('classEx')
								idResourceDetail
							@endslot
						@endcomponent
						@component('components.labels.label') Concepto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" 
								name="concept_new"
								placeholder="Ingrese el concepto"
							@endslot
							@slot('classEx')
								input-all
								removeselect
								mb-4
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						@component('components.inputs.select', ["options" => []])
							@slot('attributeEx')
								multiple="multiple" 
								name="account_id_new"
							@endslot
							@slot('classEx')
								js-accounts 
								removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Subtotal: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" 
								name="amount_new"
								placeholder="Ingrese el subtotal"
							@endslot
							@slot('classEx')
								amount
								amount_new
								removeselect
								mb-4
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">	 
						@component('components.labels.label') Fiscal: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										type="radio" 
										name="fiscal_new"
										id="nofiscal_new"
										value="no" 
										checked
									@endslot
									@slot('classEx')
										fiscal
										fiscal_on
									@endslot
										No
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										type="radio" 
										name="fiscal_new"
										id="fiscal_new"
										value="si"
									@endslot
									@slot('classEx')
										fiscal
									@endslot
										Sí
								@endcomponent
							</div>
						</div>
					</div>
					<div class="col-span-2">
						@component('components.labels.label') IVA: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled"
										type="radio" 
										name="iva_new"
										id="noiva_new" 
										value="no" 
										checked="true"
									@endslot
									@slot('classEx')
										iva
									@endslot
										No
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_new" 
										id="siiva_new"
										value="si"
									@endslot
									@slot('classEx')
										iva
									@endslot
										Sí 
								@endcomponent
							</div>
						</div>
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Tipo de IVA: @endcomponent
						<div class="flex row mb-4 space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_kind_new"
										id="iva_a_new" 
										value="a" 
										checked
									@endslot
									@slot('classEx')
										iva_kind
									@endslot
									@slot('attributeExLabel')
										title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
									@endslot
										Tipo A 
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx')
										disabled="disabled" 
										type="radio" 
										name="iva_kind_new"
										id="iva_b_new" 
										value="b"
									@endslot
									@slot('classEx')
										iva_kind
									@endslot
									@slot('attributeExLabel')
										title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
									@endslot
										Tipo B
								@endcomponent
							</div>
						</div>
					</div>
					<div class="md:col-span-4 col-span-2">
						@component('components.templates.inputs.taxes', [
								'type' => 'taxes',
								'name' => 'additional_new',
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								readonly 
								placeholder="Ingrese el importe"
								name="contentMoney_new"
								type="text"
							@endslot
							@slot('classEx')
								contentMoney_new
							@endslot
						@endcomponent
					</div>
					<div id="documents_new" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"> </div>
					<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type="button" 
								name="addDoc_new" 
								id="addDoc_new"
							@endslot
							@slot('classEx')
								mt-4
							@endslot
								<span class="icon-plus"></span>
								<span>Agregar documento</span>
						@endcomponent
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx')
								type="button" 
								name="add_new"
							@endslot
							@slot('classEx')
								add
								mt-4
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar concepto</span>
						@endcomponent
					</div>
				@endcomponent
			</div>
		@endif
		<div>
			@php
				$body 		= [];
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "#"],
						["value" => "Concepto"],
						["value" => "Clasificación del gasto"],
						["value" => "Fiscal"],
						["value" => "Subtotal"],
						["value" => "IVA"],
						["value" => "Impuesto Adicional"],
						["value" => "Importe"],
						["value" => "Documento(s)"],
					]
				];
				if($request->status == "2")
				{
					$modelHead[0][] = ["value" => "Acciones"];
				}
				
				$subtotalFinal = $ivaFinal = $totalFinal = 0;
				foreach($request->expenses->first()->expensesDetail as $key=>$expensesDetail)
				{
					$subtotalFinal	+= $expensesDetail->amount;
					$ivaFinal		+= $expensesDetail->tax;
					$totalFinal		+= $expensesDetail->sAmount;
					$varFiscal = '';
					if($expensesDetail->taxPayment==1){
						$varFiscal = "Si";
					}
					else
					{
						$varFiscal = "No";
					} 
					$varTax = '';
					if($expensesDetail->tax>0)
					{
						$varTax = "Si";
					}
					else
					{
						$varTax = 'No';
					}

					$body = 
					[ "classEx" => "tr_datepath",
						[	
							"classEx" => "countConcept",
							"content" =>
							[
								[
									"label" => $key+1
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $expensesDetail->concept
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_concept[]\" value=\"".$expensesDetail->concept."\"",
									"classEx"		=> "t_concept"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_idresourcedetail[]\" value=\"".$expensesDetail->idresourcedetail."\"",
									"classEx"		=> "idresourcedetail t_idresourcedetail"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"idRDe[]\" value=\"".$expensesDetail->idExpensesDetail."\"",
									"classEx"		=> "idExpensesDetail idRDe"
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" 	=> $expensesDetail->account->account.' '.$expensesDetail->account->description
								],
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "accountTexts hidden text-black",
									"label" 	=> $expensesDetail->account->account.' '.$expensesDetail->account->description
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_account[]\" value=\"".$expensesDetail->idAccount."\"",
									"classEx"		=> "t_account"
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $varFiscal 
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_fiscal[]\" value=\"".$varFiscal."\"",
									"classEx"		=> "t_fiscal"
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => '$ '.number_format($expensesDetail->amount,2)
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$expensesDetail->amount."\"",
									"classEx"		=> "t-amount t_amount"
								],
							]
						],
						[
							"content" => 
							[
								[
									"label" => '$ '.number_format($expensesDetail->tax,2)
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_iva[]\" value=\"".$varTax."\"",
									"classEx"		=> "t_iva"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "readonly=\"true\" type=\"hidden\"  name=\"tivakind[]\" value=\"".$expensesDetail->typeTax."\"", 
									"classEx"		=> "t-iva-kind t_iva_kind"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_iva_kind[]\" value=\"".$expensesDetail->typeTax."\"",
									"classEx"		=> "t-iva-kind"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"  name=\"t_iva_val[]\" value=\"".$expensesDetail->tax."\"",
									"classEx"		=> "t-iva"
								],
							]
						]
					];
					$taxes2 = 0;
					if(isset($expensesDetail->taxes))
					{	
						$varExpenses = '';
						foreach($expensesDetail->taxes as $tax)
						{
							$taxes2 += $tax->amount;
							$varExpenses = 
							[  
								"content" => 
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"tamountadditional\"".$docs."[]\" value=\"".$tax->amount."\"",
									"classEx"		=> "num_amountAdditional"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"tnameamount".$docs."[]\" value=\"".$tax->name."\"",
									"classEx"		=> "num_nameAmount"
								]
							];
						}
						$varExpenses =
						[	 
							"content" =>
							[
								"label" => '$ '.number_format($taxes2,2)
							]
						];
					}
					$body[] = $varExpenses;
					$body[] =
					[	 
						"content" =>
						[
							[
								"label" => '$ '.number_format($expensesDetail->sAmount,2)
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"t_total[]\" value=\"".$expensesDetail->sAmount."\"",
								"classEx"		=> "t-iva amountTotalTotal"
							]
						]
					];
					$docsExpenses = '';
					if($expensesDetail->documents()->exists())
					{
						foreach($expensesDetail->documents as $doc)
						{
							$docsExpenses .= '<div class="nowrap">';
							$docsExpenses .= '<div><label>'.isset($doc->date) ? Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y') : ''.'</label></div>';
							$docsExpenses .= view('components.buttons.button',[
								"variant"		=> "secondary",
								"buttonElement"	=> "a",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/expenses/'.$doc->path)."\"",
								"label"			=> 'Archivo'
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_path".$docs."[]\" value=\"".$doc->path."\"",
								"classEx"		=> "num_path"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_new".$docs."[]\" value=\"0\"",
								"classEx"		=> "num_new"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_datepath".$docs."[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y')."\"",
								"classEx"		=> "num_datepath"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_fiscal_folio".$docs."[]\" value=\"".htmlentities($doc->fiscal_folio)."\"",
								"classEx"		=> "num_fiscal_folio"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_ticket_number".$docs."[]\" value=\"".htmlentities($doc->ticket_number)."\"",
								"classEx"		=> "num_ticket_number"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_amount".$docs."[]\" value=\"".$doc->amount."\"",
								"classEx"		=> "num_amount"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_timepath".$docs."[]\" value=\"".$doc->timepath."\"",
								"classEx"		=> "num_timepath"
							])->render();
							$docsExpenses .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"t_name_doc".$docs."[]\" value=\"".$doc->name."\"",
								"classEx"		=> "num_name_doc"
							])->render();
							$docsExpenses .= "</div>";
						}
					}
					else
					{
						$docsExpenses = "Sin documento";
					}
					$body[] = [ "content" => [ "label" => $docsExpenses ]];
					if($request->status == "2")
					{
						array_push($body,
						[	 
							"content" =>
							[ 
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "success",
									"attributeEx"	=> "id=\"edit\" type=\"button\"",
									"classEx"		=> "edit-item",
									"label"			=> "<span class=\"icon-pencil\"></span>"
								],
								[
									"kind" 			=> "components.buttons.button",
									"variant" 		=> "red",
									"attributeEx"	=> "id=\"cancel\" type=\"button\"",
									"classEx"		=> "delete-item",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]	
						]);		
					}
					$docs++;
					$modelBody[] = $body;
				}	 
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('attributeExBody')
					id="body" 
				@endslot
				@slot('classExBody')
					request-validate
				@endslot
			@endcomponent
		</div>
		<div class="totales mb-4">
			@php
				$total 			= 0;
				$total 			= $request->expenses->first()->resourceData()->exists() ? $request->expenses->first()->resourceData->total : 0;
				$varSub 		= '';
				$varIva 		= '';
				$varTotal 		= '';
				$varSubLabel 	= "$ 0.00";
				$varIvaLabel 	= "$ 0.00";
				$varTotalLabel 	= "$ 0.00";
				if($totalFinal!=0)
				{
					$varSub 		= number_format($subtotalFinal,2);
					$varIva 		= number_format($ivaFinal,2);
					$varTotal 		= number_format($totalFinal,2);
					$varSubLabel 	= '$ '.number_format($subtotalFinal,2);
					$varIvaLabel 	= '$ '.number_format($ivaFinal,2);
					$varTotalLabel 	= '$ '.number_format($totalFinal,2);
				}
				if(isset($request))
				{
					foreach($request->expenses->first()->expensesDetail as $detail)
					{
						foreach($detail->taxes as $tax)
						{
							$taxes += $tax->amount;
						}
					}
				}
				$varReintegro 		= '';
				$varReembolso 		= '';
				$varReintegroLabel 	= "$ 0.00";
				$varReembolsoLabel 	= "$ 0.00";
				if(isset($request->expenses))
				{
					foreach($request->expenses as $expense)
					{
						$varReintegro 		= $expense->reintegro;
						$varReembolso 		= $expense->reembolso;
						$varReintegroLabel 	= $expense->reintegro != '' ? '$ '.$expense->reintegro : '$ 0.00';
						$varReembolsoLabel 	= $expense->reembolso != '' ? '$ '.$expense->reembolso : '$ 0.00';
					} 
				}
				$modelTable = 
				[
					[
						"label" => "Total Recurso:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".$total,
								"classEx" 	=> "my-2 totalResourceLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx" 		=> "totalResource",	
								"attributeEx" 	=> "type=\"hidden\" name=\"totalResource\" value=\"".$total."\""
							]
						]
					],
					[
						"label" => "Subtotal:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varSubLabel,
								"classEx" 	=> "my-2 subtotalLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx" 		=> "subtotal",	
								"attributeEx" 	=> "type=\"hidden\" id=\"subtotal\" name=\"subtotal\" value=\"".$varSub."\""
							]
						]
					], 
					[
						"label" => "IVA:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varIvaLabel,
								"classEx" 	=> "my-2 ivaTotalLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx" 		=> "ivaTotal",	
								"attributeEx" 	=> "type=\"hidden\" id=\"iva\" name=\"iva\" value=\"".$varIva."\""
							]
						]
					],
					[
						"label" => "Impuesto Adicional:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".number_format($taxes,2),
								"classEx"	=> "my-2 labelAmount"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"amountAA\" value=\"".number_format($taxes,2)."\""
							]
						]
					], 
					[
						"label" => "Reintegro:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varReintegroLabel,
								"classEx" 	=> "my-2 reintegroLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" id=\"reintegro\" name=\"reintegro\" value=\"".$varReintegro."\"",
								"classEx" 		=> "reintegro"
							]
						]
					],
					[
						"label" => "Reembolso:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varReembolsoLabel,
								"classEx" 	=> "my-2 reembolsoLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" id=\"reembolso\" name=\"reembolso\" value=\"".$varReembolso."\"",
								"classEx" 		=> "reembolso"
							]
						]
					],
					[
						"label" => "TOTAL:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varTotalLabel,
								"classEx" 	=> "my-2 totalLabel"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx" 		=> "total",	
								"attributeEx" 	=> "type=\"hidden\" id=\"total\" name=\"total\" value=\"".$varTotal."\""
							]
						]
					]
				];
			@endphp
			@component('components.templates.outputs.form-details', [ "modelTable" => $modelTable]) @endcomponent
		</div>
		<div id="invisible"></div>
		@if($request->idCheck != "")
			@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
			@php
				$varEnterprise	= '---';
				$varDirection 	= '---';
				$varDepartament = '---';
				$reviewAccount 	= '---';
				$varAccount 	= '---';
				$varProyect		= '---';
				$varDescription = '---';
				if($request->idEnterpriseR!="")
				{
					$varEnterprise	= App\Enterprise::find($request->idEnterpriseR)->name;
					$varDirection 	= $request->reviewedDirection->name;
					$varDepartament = App\Department::find($request->idDepartamentR)->name;
					$reviewAccount 	= App\Account::find($request->accountR);
					if(isset($reviewAccount->account))
					{
						$varAccount = $reviewAccount->account.' - '.$reviewAccount->description;
					}
					else
					{
						$varAccount = 'Varias';
					}
					$varProyect 	=  $request->reviewedProject->proyectName;
					if (count($request->labels))
					{
						foreach($request->labels as $label)
						{
							$varDescription = $label->description;
						}
					}
					else
					{
						$varDescription = 'Sin etiqueta';
					}
				}
				$varLabels = "";
				if(count($request->labels))
				{
					foreach($request->labels as $label)
					{
						$varLabels .= $label->description;
					}
				}
				else
				{
					$varLabels = "Sin etiqueta";
				}
				$varComment = '';
				if($request->checkComment == "")
				{
					$varComment = 'Sin comentarios';
				}		
				else
				{
					$varComment = htmlentities($request->checkComment);
				}			
		
				$modelTable = [
					"Revisó" 					=> $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name,
					"Nombre de la Empresa" 		=> $varEnterprise,
					"Nombre de la Dirección" 	=> $varDirection,
					"Nombre del Departamento" 	=> $varDepartament,
					"Clasificación del gasto" 	=> $varAccount,
					"Nombre del Proyecto" 		=> $varProyect,
					"Comentarios" 				=> $varComment
				];
				if($varLabels != "Sin etiqueta")
				{
					array_splice($modelTable, 5, "Etiquetas", $varLabels);
				}
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
			@if($request->idEnterpriseR!="")
				@component('components.labels.title-divisor') ETIQUETAS ASIGNADAS @endcomponent
				<div class="mt-4 mb-4"> 
					@php
						$body 		= [];
						$modelBody 	= [];
						$modelHead	= [
							[
								["value" => "Concepto"],
								["value" => "Clasificación de gasto"],
								["value" => "Etiquetas"]
							]
						];

						foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
						{
							$varLabel = "";
							if(count($expensesDetail->labels))
							{	
								foreach($expensesDetail->labels as $label)
								{
									$varLabel .= $label->label->description.", ";
								}
							}
							else
							{
								$varLabel = "Sin etiqueta";
							}
										 
							$body = 
							[
								[
									"content" => 
									[
										"label" =>  htmlentities($expensesDetail->concept),
									]
								],
								[
									"content" => 
									[
										"label" => $expensesDetail->accountR->account.' - '.$expensesDetail->accountR->description
									]
								],
								[
									"content" => 
									[
										"label" => $varLabel
									]
								]
							];
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.table', [
						"modelBody" => $modelBody,
						"modelHead"	=> $modelHead,
					])
						@slot('attributeEx')
							id="table"
						@endslot
						@slot('attributeExBody')
							id="tbody-conceptsNew"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot 
					@endcomponent
				</div>
			@endif
		@endif
		@if($request->idAuthorize != "")
			@component('components.labels.title-divisor') DATOS DE AUTORIZACIÓN @endcomponent
			@php
				$varComments = '';
				if($request->authorizeComment == "")
				{
					$varComments = 'Sin comentarios';
				}
				else
				{
					$varComments = $request->authorizeComment;
				}
				$modelTable = [
					"Autorizó" 		=> $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name,
					"Comentarios"	=> htmlentities($varComments),
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
		@endif
		@if($request->status == 13)
			@component('components.labels.title-divisor') DATOS DE PAGOS @endcomponent
			@php
				$varComment = '';
				if($request->paymentComment == "")
				{
					$varComments = 'Sin comentarios';
				}
				else
				{
					$varComment = $request->paymentComment;
				}	
				$modelTable = [ "Comentarios"	=> $varComment ];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
		@endif
		@php
			$payments		= App\Payment::where('idFolio',$request->folio)->get();
			$total 			= $request->expenses->first()->total;
			$totalPagado	= 0;
		@endphp
		@if(count($payments))
			@component('components.labels.title-divisor') HISTORIAL DE PAGOS @endcomponent
			<div class="my-4"> 
				@php
					$body 		= [];
					$modelBody 	= [];
					$modelHead	= [
						[
							["value" => "Cuenta"],
							["value" => "Cantidad"],
							["value" => "Documento"],
							["value" => "Fecha"]
						]
					];
					foreach($payments as $pay)
					{ 
						$body = 
						[
							[
								"content" => 
								[
									"label" => $pay->accounts->account.' - '.$pay->accounts->description
								]
							],
							[
								"content" =>
								[
									"label" => '$ '.number_format($pay->amount,2)
								]
							],
						];
						if($pay->documentsPayments()->exists())
						{
							$docsContent = [];
							foreach($pay->documentsPayments as $doc)
							{
								$docsContent['content'][] = 
								[
									"kind" 			=> "components.buttons.button",
									"variant"		=> "secondary",
									"buttonElement" => "a",
									"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
									"label"			=> 'Archivo'
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
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.table', [
					"modelBody" => $modelBody,
					"modelHead"	=> $modelHead,
				])
				@endcomponent
				@php
					foreach($payments as $pay)
					{
						$totalPagado += $pay->amount;
					}
					$varRes 		= '';
					$varResLabel	= '$ 0.00';
					foreach($request->expenses as $expense)
					{
						if($expense->reembolso > 0)
						{
							$varRes 		= number_format($expense->reembolso-$totalPagado,2);
							$varResLabel	= '$ '.number_format($expense->reembolso-$totalPagado,2);
						}
						else if($expense->reintegro > 0)
						{
							$varRes 		= number_format($expense->reintegro-$totalPagado ,2);
							$varResLabel 	= '$ '.number_format($expense->reintegro-$totalPagado ,2);
						}
					}
					$modelTable =
					[
						[
							"label" => "Total pagado:", "inputsEx" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"label" 	=> "$ ".number_format($totalPagado,2),
									"classEx" 	=> "my-2"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" value=\"".number_format($totalPagado,2)."\""
								]
							]
						],
						[
							"label" => "Resta por pagar:", "inputsEx" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"label" 	=> $varResLabel,
									"classEx" 	=> "my-2"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" value=\"".$varRes."\""
								]
							]
						]
					];
				@endphp
				@component('components.templates.outputs.form-details', [ "modelTable" => $modelTable]) @endcomponent
			</div>
		@endif
		@if(in_array($request->status,[5,10,11,12]) && $request->code != null && $request->expenses->first()->reintegro != null && $request->free == 0)
			@component('components.labels.title-divisor') LIBERACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
					@component('components.labels.label') Código de liberación: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" 
							name="code" 
							id="code"
							placeholder="Ingrese el código de liberación"
							data-validation="server"
							data-validation-url="{{ url('administration/payments/validate') }}"
							data-validation-req-params="{{ json_encode(array('oldCode'=>$request->code)) }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button',[ "variant" => "primary" ])
						@slot('attributeEx')
							type="submit" 
							name="send"
							formaction="{{ route('expenses.code', $request->folio) }}"
						@endslot
							ENVIAR CÓDIGO
					@endcomponent
				</div>	
			@endcomponent
		@endif
		<div id="delete"></div>
		@if($request->status == "2")
			<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6"> 
				@component('components.buttons.button', [
					"variant" => "primary"
					])
					@slot('attributeEx')
						type="submit" 
						name="enviar"
					@endslot
						ENVIAR SOLICITUD
				@endcomponent
				@component('components.buttons.button', [
					"variant" => "secondary"
					])
					@slot('attributeEx')
						type="submit"
						id="save"
						name="save"
						formaction="{{ route('expenses.follow.updateunsent', $request->folio) }}"
					@endslot
					@slot('classEx')
						save
					@endslot
						GUARDAR SIN ENVIAR
				@endcomponent
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
					@slot('attributeEx')
						@if(isset($option_id))
							href="{{ url(App\Module::find($option_id)->url) }}"
						@else
							href="{{ url(App\Module::find($child_id)->url) }}"
						@endif
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			</div>
		@else
			<div class="flex justify-center mt-4">  
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
					@slot('attributeEx')
						@if(isset($option_id))
							href="{{ url(App\Module::find($option_id)->url) }}"
						@else
							href="{{ url(App\Module::find($child_id)->url) }}"
						@endif
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			</div>
		@endif
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/daterangepicker.js') }}"></script>
<script>	
	$(document).ready(function()
	{
		$('.totalResourceLabel').text('$ '+Number($('[name="totalResource"]').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.reintegroLabel').text('$ '+Number($('[name="reintegro"]').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		@php
			$selects = collect([ 
				[
					"identificator"				=> ".js-currency",
					"placeholder"				=> "Seleccione el tipo de moneda",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		generalSelect({'selector':'.js-users', 'model':13});
		generalSelect({'selector':'.js-resources', 'depends':'.js-users', 'model':7, 'user': $('.js-users option:selected').val()});
		$.validate(
		{
			form	: '#container-alta',
			modules	: 'security',
			onError	: function($form)
			{
				swal('', 'Tiene conceptos sin agregar', 'error');
			},
			onSuccess : function($form)
			{
				concept		= $('input[name="concept_exist"]').val().trim();
				date		= $('input[name="datepath_exist"]').length;
				account		= $('.js-accounts').val();
				path		= $('input[name="realPath"]').length;
				amount		= $('input[name="amount_exist"]').val().trim();
				conceptN	= $('input[name="concept_new"]').val().trim();
				dateN		= $('input[name="datepath_new"]').length;
				amountN		= $('input[name="amount_new"]').val().trim();
				if (concept != ""  || date > 0 || amount != "" || account != "" || path > 0 || conceptN != "" || dateN > 0 || amountN != "") 
				{
					swal('', 'Tienes conceptos sin agregar', 'error');
					return false;
				}
				if($('.request-validate').length>0)
				{
					conceptos	= $('#body .tr_datepath').length;
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
								swal('', 'Debe seleccionar un cuenta', 'error');
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
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
			}
		});
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
		});
		if($("#documents_exist .name_document_new").length == 0)
		{
			addNewDocument(true, "exist");
		}
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional_exist','function' => 'amountAdditionalExist'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional_new','function' => 'amountAdditionalNew'])  @endcomponent
		$('[name="amount_new"],[name="additional_newAmount"],[name="additional_existAmount"]').on("contextmenu",function(e)
		{
			return false;
		});
		$('.amount,.descuento,.additional_existAmount,.additional_newAmount').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('.js-enterprises').select2(
		{
			placeholder				: 'Seleccione la empresa',
			allowClear				: false,
			language				: "es",
			maximumSelectionLength	: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-resources').on('select2:unselecting', function (e)
		{
			e.preventDefault();
			swal({
				title		: "Cambiar de Folio de Recurso",
				text		: "Si cambia el folio, todos los conceptos que ya se encontraban agregados serán eliminados",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((willClean) =>
			{
				if(willClean)
				{
					$(this).val(null).trigger('change');
					$('#body .tr_datepaht').each(function()
					{
						id = $(this).find('.idExpensesDetail').val();
						if(id!='x')
						{
							$('#invisible').append($('<input type="hidden" name="delete[]"/>').val(id));
						}
					});
					$('#body .tr_datepath').empty();
				}
				else
				{
					swal.close();
				}
			});
		});
		doc = {{ $docs }};
		$(document).on('change','.js-enterprises',function()
		{
			enterprise = $('select[name="enterprise_id"] option:selected').text();
			$('.enterprise').val(enterprise);
		})
		.on('change','.js-users',function()
		{
			user = $('select[name="user_id"] option:selected').text();
			$('.name_sol').val(user);
		})
		.on('change','input[name="fiscal_exist"]',function()
		{
			if ($('input[name="fiscal_exist"]:checked').val() == "si") 
			{
				$(".iva").prop('disabled', false).trigger("change");
				$("#siiva_exist").siblings('label').removeClass("disabled");
				$("#noiva_exist").siblings('label').removeClass("disabled");
			}
			else if ($('input[name="fiscal_exist"]:checked').val() == "no") 
			{
				$("#noiva_exist").prop('checked',true);
				$(".iva").prop('disabled', true);
				$("#iva_a_exist").prop('checked',true);
				$(".iva_kind").prop('disabled', true);
				$("#siiva_exist").siblings('label').addClass("disabled");
				$("#noiva_exist").siblings('label').addClass("disabled");
			}
		})
		.on('change','input[name="iva_exist"]',function()
		{
			if ($('input[name="iva_exist"]:checked').val() == "si") 
			{
				$(".iva_kind").prop('disabled', false);
				$("#iva_a_exist").siblings('label').removeClass("disabled");
				$("#iva_b_exist").siblings('label').removeClass("disabled");
			}
			else if ($('input[name="iva_exist"]:checked').val() == "no")
			{
				$("#iva_a_exist").prop('checked',true);
				$(".iva_kind").prop('disabled', true);
				$("#iva_a_exist").siblings('label').addClass("disabled");
				$("#iva_b_exist").siblings('label').addClass("disabled");
			}
		})
		.on('change','input[name="fiscal_new"]',function()
		{
			if ($('input[name="fiscal_new"]:checked').val() == "si") 
			{
				$("#siiva_new").prop('disabled', false).trigger("change");
				$("#siiva_new").siblings('label').removeClass("disabled");
				$("#noiva_new").prop('disabled', false).trigger("change");
				$("#noiva_new").siblings('label').removeClass("disabled");
				$(".iva").prop('disabled', false).trigger("change");
			}
			else if ($('input[name="fiscal_new"]:checked').val() == "no") 
			{
				$("#noiva_new").prop('checked',true);
				$(".iva").prop('disabled', true);
				$("#iva_a_new").prop('checked',true);
				$(".iva_kind").prop('disabled', true);
				$("#siiva_new").siblings('label').addClass("disabled");
				$("#noiva_new").siblings('label').addClass("disabled");
			}
		})
		.on('change','input[name="iva_new"]',function()
		{
			if ($('input[name="iva_new"]:checked').val() == "si") 
			{
				$(".iva_kind").prop('disabled', false);
				$("#iva_a_new").siblings('label').removeClass("disabled");
				$("#iva_b_new").siblings('label').removeClass("disabled");
			}
			else if ($('input[name="iva_new"]:checked').val() == "no")
			{
				$("#iva_a_new").prop('checked',true);
				$(".iva_kind").prop('disabled', true);
				$("#iva_a_new").siblings('label').addClass("disabled");
				$("#iva_b_new").siblings('label').addClass("disabled");
			}
		})
		.on('click','input[name="exist_new"]',function()
		{
			$(".fiscal_on").prop('checked', true);
			$("#noiva_new").prop('checked',true);
			$(".iva").prop('disabled', true);
			$("#iva_a_new").prop('checked',true);
			$(".iva_kind").prop('disabled', true);
			$("#noiva_exist").prop('checked',true);
			$("#iva_a_exist").prop('checked',true);
			$(".addiotional_on").prop('checked', true);
		})
		.on('click', '#doc_new', function()
		{	
			valueIdResource		= $('input[name="idresourcedetail_exist"]').val();
			valueConcept		= $('input[name="concept_exist"]').val();
			valueAccount		= $('input[name="account_exist"]').val();
			valueAccountId		= $('input[name="account_id_exist"]').val(); 
			valueAmount 		= $('input[name="amount_exist"]').val(); 

			if(valueConcept != "")
			{
				@php
					$body		=[];
					$modelBody	=[];
					$modelHead	=[
						[
							["value" => "Concepto"],
							["value" => "Clasificación de gasto"],
							["value" => "Importe"],
							["value" => "Acción"],
						]
					];
					$body = 
					[	"classEx" => "tr_detail",
						[						
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx"		=> "idresourcedetail-table",
									"attributeEx"	=> "name=\"idresourcedetail-table\" type=\"hidden\""
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx"		=> "concept-table",
									"attributeEx"	=> "name=\"concept-table\" type=\"hidden\""
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"classEx" 		=> "account-table",
									"attributeEx" 	=> "name=\"account-table\" type=\"hidden\""
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "accountid-table",
									"attributeEx"	=> "name=\"accountid-table\" type=\"hidden\""
								]
							]	 
						],
						[
							"content" =>
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "amount-table",
								"attributeEx"	=> "name=\"amount-table\" type=\"hidden\""
							]
						],
						[
							"content" =>
							[
								"kind"			=> "components.buttons.button",
								"variant"		=> "warning",
								"attributeEx"	=> "type=\"button\"",
								"classEx"		=> "add-concept",
								"label"			=> "<span class=\"icon-plus\"></span>"
							]
						]
					];
					$modelBody[] = $body;
					$table2 = view('components.tables.table', [
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> "true"
					])->render();
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				row.find('.idresourcedetail-table').parent().prepend(valueConcept);
				row.find('.idresourcedetail-table').val(valueIdResource);
				row.find('.concept-table').val(valueConcept);
				row.find('.account-table').parent().prepend(valueAccount);
				row.find('.account-table').val(valueAccount);
				row.find('.accountid-table').val(valueAccountId);
				row.find('.amount-table').parent().prepend('$ '+Number(valueAmount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
				row.find('.amount-table').val(valueAmount);
				$('#body-classify').append(row);
				$('#documents-resource').show();
			}
			$('#docs_exist').find('.input-all').val('');
			$('#docs_exist').find('.contentMoney').val('');
			$('#docs_exist').find('.amount').val('');
			$('#docs_exist').find('.additional_newName').val('');
			$('#docs_exist').find('.additional_newAmount').val('');
			$('#docs_exist').find('.js-accounts').val('').trigger('change');
			$('#docs_exist').find('.fiscal').prop('checked',false);
			$('#docs_exist').find('.iva').prop('checked',false);
			$('#docs_exist').find('.iva_kind').prop('checked',false);
			$('#docs_exist').find('.addiotional').prop('checked',false);
			$('#docs_exist').find('.docs-p').remove();
		})
		.on('click','#addDoc_exist',function()
		{
			@php 
				$options = collect();
				$options= $options->concat([["value"=>"Ticket", "description"=> "Ticket"]]);
				$options= $options->concat([["value"=>"Factura", "description"=> "Factura"]]);
				$options= $options->concat([["value"=>"Otro", "description"=> "Otro"]]);
				$docs_upload = view("components.documents.upload-files",[
					"classExInput"			=> "pathActioner",
					"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExDelete"			=> "delete-doc",
					"attributeExRealPath"	=> "type=\"hidden\" name=\"realPath\"",
					"classExRealPath"		=> "path_exist",
					"componentsExUp"		=>  [
													[
														"kind" 	=> "components.labels.label", 
														"label" => "Seleccione el tipo de documento:"
													],
													[
														"kind" 			=> "components.inputs.select", 
														"options" 		=> $options,
														"classEx" 		=> "name_document name_document_exist",
														"attributeEx"	=> "data-validation=\"required\""
													]
												],
					"componentsExDown"		=> 	[
													[
														"kind" 			=> "components.labels.label", 
														"label"			=> "Seleccione la fecha:",
														"classEx" 		=> "hidden datepath_label"
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "type=\"text\" step=\"1\" name=\"datepath_exist\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
														"classEx"		=> "hidden mb-4 datepicker removeselect datepath datepath_exist",
													],
													[
														"kind" 			=> "components.labels.label", 
														"label"			=> "Seleccione la hora:",
														"classEx"		=> "hidden timepath_label"
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "type=\"text\" step=\"60\" value=\"00:00\"  placeholder=\"Seleccione la hora\" readonly=\"readonly\"",
														"classEx"		=> "hidden mb-4 removeselect timepath timepath_exist",
													],
													[
														"kind" 			=> "components.labels.label", 
														"label"			=> "Folio Fiscal:",
														"classEx"		=> "hidden fiscal_folio_label"
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el folio fiscal\"",
														"classEx"		=> "hidden mb-4 removeselect fiscal_folio fiscal_folio_exist",
													],
													[
														"kind" 		=> "components.labels.label", 
														"label"		=> "Número de ticket:",
														"classEx" 	=> "ticket_number_label mb-4 hidden"
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la el número de ticket\"",
														"classEx"		=> "hidden mb-4 removeselect ticket_number ticket_number_exist"
													],
													[
														"kind" 			=> "components.labels.label", 
														"label"			=> "Monto total:",
														"classEx"		=> "amount_label hidden"
													],
													[
														"kind" 			=> "components.inputs.input-text",
														"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la monto total\"",
														"classEx"		=> "hidden mb-4 removeselect amount amount_exist",
													]
												]
				])->render();
				$docs_upload = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $docs_upload));
			@endphp
			docs_upload = '{!!preg_replace("/(\r)*(\n)*/", "", $docs_upload)!!}';
			$('#documents_exist').append(docs_upload);
			$('.amount').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
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
			@php
				$selects = collect([
					[
						"identificator"				=> ".name_document_exist",
						"placeholder"				=> "Seleccione el tipo de documento",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		})
		.on('click','#addDoc_new',function()
		{
			addNewDocument(false, "new");
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon 	: '{{ url(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url 		: '{{ route("expenses.upload") }}',
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
		})
		.on('click','.add',function()
		{
			$(this).addClass("disabled");
			setInterval(() =>
			{
				$('.add ').removeAttr('disabled', 'disabled');
			}, 1500);
			if ($('input[name="exist_new"]:checked').val() == "exist") 
			{
				countConcept		= $('.countConcept').length;
				amountAAtotal 		= 0;
				idresourcedetail 	= $('input[name="idresourcedetail_exist"]').val().trim();
				concept				= $('input[name="concept_exist"]').val().trim();
				date				= $('.datepath_exist').length;
				iva					= $('input[name="iva_exist"]:checked').val();
				ivaKind				= $('input[name="iva_kind_exist"]:checked').val()
				fiscal				= $('input[name="fiscal_exist"]:checked').val();
				subtotal			= $('input[name="amount_exist"]').val().trim();
				account				= $('input[name="account_id_exist"]').val();
				accountText			= $('input[name="account_exist"]').val();
				path				= $('.path_exist').length;
				ivaParam			= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				ivaParam2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCal				= iva=="si" ? (ivaKind =="a" ? Number(Number(subtotal) * Number(ivaParam)).toFixed(2) : Number(Number(subtotal) * Number(ivaParam2)).toFixed(2)) : 0;
				total				= Number(subtotal) + Number(ivaCal);
				cont 				= true;

				if($('[name="additional_exist"]:checked').val() == "si")
				{
					$('.additional_existAmount').each(function(i,v)
					{
						if($(this).val() == '')
						{
							cont = false;
							swal('','Por favor agrege los impuestos adicionales faltantes','error');
						}
						else if($(this).val() == 0)
						{
							cont = false;
							swal('','El impuesto adicional no puede ser cero','error');
						}
					});	
				}

				if(path != 0 && cont)
				{
					$('.path_exist').each(function(i,v)
					{
						if($(this).val()=='')
						{
							cont = false;
							swal('','Por favor agregue los documentos faltantes','error');
						}
					});
				}

				if(date != 0 && cont)
				{
					$('.datepath_exist').each(function(i,v)
					{
						if($(this).val()=='')
						{
							cont = false;
							$(this).addClass('error');
							swal('','Por favor agregue las fechas faltantes de los documentos','error');
						}
					});
				}
				if (concept == ""  || date == 0 || subtotal == "" || account == "" || path == 0)
				{
					if(account == "" && concept != ""  && date != "" && subtotal != "")
					{
						swal('', 'Por favor seleccione una clasificación de gasto', 'error');
					}
					else if(path == 0)
					{
						swal('','Por favor agregue un documento','error');
					}
					else
					{
						swal('', 'Por favor llene los campos necesarios', 'error');
					}
					if(concept == "")
					{
						$('input[name="concept_exist"]').addClass('error');
					}
					if(date == "")
					{
						$('.datepath_exist').addClass('error');
					}
					if(subtotal == "")
					{
						$('input[name="amount_exist"]').addClass('error');
					}
				}
				else if(cont)
				{
					fiscal_folio	= [];
					ticket_number	= [];
					timepath		= [];
					amount			= [];
					datepath		= [];
					
					if ($('.datepath_exist').length > 0) 
					{
						$('.datepath_exist').each(function(i,v)
						{
							fiscal_folio.push($(this).parents('.components-ex-down').find('.fiscal_folio_exist').val());
							ticket_number.push($(this).parents('.components-ex-down').find('.ticket_number_exist').val());
							timepath.push($(this).parents('.components-ex-down').find('.timepath_exist').val());
							amount.push(Number($(this).parents('.components-ex-down').find('.amount_exist').val()).toFixed(2));
							datepath.push($(this).parents('.components-ex-down').find('.datepath_exist').val());
						});

						$.ajax(
						{
							type	: 'post',
							url		: '{{ route("expenses.validation-document") }}',
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
								$('.datepath_exist').each(function(j,v)
								{

									ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_exist');
									fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_exist');
									timepath		= $(this).parents('.components-ex-down').find('.timepath_exist');
									amount			= $(this).parents('.components-ex-down').find('.amount_exist');
									datepath		= $(this).parents('.components-ex-down').find('.datepath_exist');

									ticket_number.removeClass('error').removeClass('valid');
									fiscal_folio.removeClass('error').removeClass('valid');
									timepath.removeClass('error').removeClass('valid');
									amount.removeClass('error').removeClass('valid');
									datepath.removeClass('error').removeClass('valid');

									$(data).each(function(i,d)
									{
										if (j == d) 
										{
											ticket_number.addClass('error')
											fiscal_folio.addClass('error');
											timepath.addClass('error');
											amount.addClass('error');
											datepath.addClass('error');
											flag = true;
										}
										else
										{
											ticket_number.addClass('valid')
											fiscal_folio.addClass('valid');
											timepath.addClass('valid');
											amount.addClass('valid');
											datepath.addClass('valid');
										}
									});
								});
								if (flag) 
								{
									swal('','Los documentos marcados ya se encuentran registrados.','error');
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						})
						.done(function(data)
						{
							if (!flag) 
							{
								addTr(countConcept,amountAAtotal,idresourcedetail,concept,date,iva,ivaKind,fiscal,subtotal,account,accountText,path,ivaParam,ivaParam2,ivaCal,total,cont);
							}
						});
					}
					else
					{
						swal('','Por favor agregue al menos un documento','error');
					}

					function addTr(countConcept,amountAAtotal,idresourcedetail,concept,date,iva,ivaKind,fiscal,subtotal,account,accountText,path,ivaParam,ivaParam2,ivaCal,total,cont)
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "#"],
									["value" => "Concepto"],
									["value" => "Clasificación del gasto"],
									["value" => "Fiscal"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Importe"],
									["value" => "Documento(s)"],
									["value" => "Acciones"],
								]
							];
							
							$body = [ "classEx" => "tr_datepath",
								[
									"classEx"	=> "countConcept",
									"content" 	=>
									[
										"label"	=> ""
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_concept[]\"",
											"classEx"		=> "t_concept"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idRDe[]\"",
											"classEx"		=> "idRDe idExpensesDetail"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_idresourcedetail[]\"",
											"classEx"		=> "idresourcedetail"	
										]
									] 
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "accountTexts hidden"
 										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_account[]\"",
											"classEx"		=> "t_account"	
										]
									] 
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_fiscal[]\"",
										"classEx"		=> "t_fiscal"
									] 
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\"",
										"classEx"		=> "t-amount t_amount"
									] 
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva[]\"",
											"classEx"		=> "t_iva"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva_kind[]\"",
											"classEx"		=> "t-iva-kind"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva_val[]\"",
											"classEx"		=> "t-iva"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"tivakind[]\"",
											"classEx"		=> "t_iva_kind"	
										]
									] 
								],
								[
									"classEx"	=> "amount_AA_Total",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_total[]\"",
										"classEx"		=> "amountTotalTotal"
									] 
								],
								[
									"classEx" => "docValues",
									"content" =>
									[
										[
											"kind"  => "components.labels.label",
											"label" => "",
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  		=> "components.buttons.button",
											"variant"		=> "success",
											"label" 		=> "<span class=\"icon-pencil\"></span>",
											"classEx" 		=> "edit-item",
											"attributeEx" 	=> "id=\"edit\" type=\"button\""
										],
										[
											"kind"  		=> "components.buttons.button",
											"variant"	 	=> "red",
											"label" 		=> "<span class=\"icon-x\"></span>",
											"attributeEx" 	=> "id=\"cancel\" type=\"button\"",
											"classEx" 		=> "delete-item"
										]
									]
								]
							];
							$modelBody[] = $body;

							$table2 = view('components.tables.table', [
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"themeBody" => "striped",
								"noHead"	=> "true"
							])->render();
						@endphp	
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
						row = $(table);

						tempFlag = 1;
						$(".path_exist").each(function(i, v)
						{
							pathName		= $(this).val();
							fiscal_folio	= $(this).parents('.docs-p').find('.fiscal_folio_exist').val();
							ticket_number	= $(this).parents('.docs-p').find('.ticket_number_exist').val();
							amount			= $(this).parents('.docs-p').find('.amount_exist').val();
							timepath		= $(this).parents('.docs-p').find('.timepath_exist').val();
							datepath		= $(this).parents('.docs-p').find('.datepath_exist').val();
							nameDoc			= $(this).parents('.docs-p').find('.name_document_exist option:selected').val();

							@php
								$newButtonPDF = view("components.buttons.button", [
									"buttonElement" => "a",
									"attributeEx"	=> "href=\"#\" type=\"button\"",
									"variant" 		=> "secondary",
									"label"   		=> "Archivo",
									"classEx" 		=> "button_pdf",
								])->render();
							@endphp
							buttonPDF = '{!!preg_replace("/(\r)*(\n)*/", "", $newButtonPDF)!!}';
							row.find('.docValues').append($('<div class="nowrap"></div>').append($('.datepath_exist').get(i).value)
										.append($(buttonPDF).attr('title',pathName))
										.append($('<input type="hidden" name="t_path'+doc+'[]" class="num_path">').val(pathName))
										.append($('<input type="hidden" name="t_fiscal_folio'+doc+'[]" class="num_fiscal_folio">').val(fiscal_folio))
										.append($('<input type="hidden" name="t_ticket_number'+doc+'[]" class="num_ticket_number">').val(ticket_number))
										.append($('<input type="hidden" name="t_amount'+doc+'[]" class="num_amount">').val(amount))
										.append($('<input type="hidden" name="t_timepath'+doc+'[]" class="num_timepath">').val(timepath))
										.append($('<input type="hidden" name="t_datepath'+doc+'[]" class="num_datepath">').val(datepath))
										.append($('<input type="hidden" name="t_name_doc'+doc+'[]" class="num_name_doc">').val(nameDoc))
										.append($('<input type="hidden" name="t_new'+doc+'[]" class="num_new">').val(1)));
						});
						nameAmounts = $('<div hidden></div>');
						$('.additional_existName').each(function(i,v)
						{
							nameAmount = $(this).val();
							nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+doc+'[]">').val(nameAmount));
						});

						amountsAA = $('<div hidden></div>');
						$('.additional_existAmount').each(function(i,v)
						{
							amountAA = $(this).val();
							amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+doc+'[]">').val(amountAA));
							amountAAtotal = Number(amountAAtotal)+ Number(amountAA);
						});

						countConcept = countConcept+1;
						row.find('.countConcept').prepend(countConcept);
						row.find('.idExpensesDetail').val('x');
						row.find('.idresourcedetail').val(idresourcedetail);
						concept = String(concept).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
						row.find('.t_concept').parent().prepend(concept);
						row.find('.t_concept').val(concept);
						row.find('.accountTexts').parent().prepend(accountText);
						row.find('.accountTexts').text(accountText);
						row.find('.t_account').val(account);
						row.find('.t_fiscal').parent().prepend(fiscal);
						row.find('.t_fiscal').val(fiscal);
						row.find('.t_amount').parent().prepend('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.t_amount').val(subtotal);
						row.find('.t_iva').parent().prepend('$ '+Number(ivaCal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.t-iva').val(ivaCal);
						row.find('.t_iva').val(iva);
						row.find('.t-iva-kind').val(ivaKind);
						row.find('.t_iva_kind').val(ivaKind);
						row.find('.amount_AA_Total').prepend('$ '+Number(amountAAtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.amountTotalTotal').parent().prepend('$ '+Number(total+amountAAtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.amountTotalTotal').val(total);
						row.find('.docValues').append(nameAmounts).append(amountsAA);
						$('#body').append(row);
						$('input[name="idresourcedetail_exist"]').val('');
						$('input[name="concept_exist"]').val('');
						$('input[name="path_exist"]').val('');
						$('.datepath_exist').val('');
						$('input[name="account_exist"]').val('');
						$('input[name="account_id_exist"]').val('');
						$('#nofiscal_exist').prop("checked",true);
						$('#no_additional_exist').prop("checked",true);
						$('#noiva_exist,#iva_a_exist').prop('checked',true);
						$('.iva,.iva_kind').prop('disabled',true);
						$('input[name="amount_exist"]').val('');
						$('input[name="concept_exist"]').removeClass('error');
						$('input[name="path_exist"]').removeClass('error');
						$('.datepath_exist').removeClass('error');
						$('input[name="amount_exist"]').removeClass('error');
						$('#documents_exist').empty();
						$('.add-concept').removeAttr('disabled');
						$('.reintegro').val('');
						$('.reembolso').val('');
						$('.contentMoney').val('');
						$('#newsImpuestos_exist').empty();
						$('.additional_existName').val('');
						$('.additional_existAmount').val('');
						$('#taxes_exist').stop(true,true).slideUp().hide();
						$('#documents_exist').html('');
						additional_existCleanComponent();
						total_cal();
						refund();
						doc++;
						$(this).removeClass("disabled");
						if($("#documents_exist .name_document_new").length == 0)
						{
							addNewDocument(true, "exist");
						}
					}
				}
			}
			else
			{
				countConcept		= $('.countConcept').length;
				amountAAtotal 		= 0;
				idresourcedetail 	= "x";
				concept				= $('input[name="concept_new"]').val().trim();
				iva					= $('input[name="iva_new"]:checked').val();
				ivaKind				= $('input[name="iva_kind_new"]:checked').val();
				fiscal				= $('input[name="fiscal_new"]:checked').val();
				subtotal			= $('input[name="amount_new"]').val().trim();
				account				= $('.js-accounts').val();
				accountText			= $('.js-accounts option:selected').text();
				date				= $('.datepath_new').length;
				path				= $('.path_new').length;
				ivaParam			= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				ivaParam2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCal				= iva=="si" ? (ivaKind =="a" ? Number(Number(subtotal) * Number(ivaParam)).toFixed(2) : Number(Number(subtotal) * Number(ivaParam2)).toFixed(2)) : 0;
				total				= Number(subtotal) + Number(ivaCal);
				cont 				= true;

				if($('[name="additional_new"]:checked').val() == "si")
				{
					$('.additional_newAmount').each(function(i,v)
					{
						if($(this).val() == '')
						{
							cont = false;
							swal('','Por favor agrege los impuestos adicionales faltantes','error');
						}
						else if($(this).val() == 0)
						{
							cont = false;
							swal('','El impuesto adicional no puede ser cero','error');
						}
					});
				}
				
				if(path != 0  && cont)
				{
					$('.path_new').each(function(i,v)
					{
						if($(this).val()=='')
						{
							cont = false;
							swal('','Por favor agregue los documentos faltantes','error');
						}
					});
				}

				if(date != 0 && cont)
				{
					$('.datepath_new').each(function(i,v)
					{
						if($(this).val()=='')
						{
							cont = false;
							$(this).addClass('error');
							swal('','Por favor agregue las fechas faltantes de los documentos','error');
						}
					});
				}

				if (concept == ""  || date == 0 || subtotal == "" || account == "" || path == 0)
				{
					if(account == "" && concept != ""  && date != 0 && subtotal != "")
					{
						swal('', 'Por favor seleccione una clasificación de gasto', 'error');
					}
					else if(path == 0)
					{
						swal('','Por favor agregue un documento','error');
					}
					else
					{
						swal('', 'Por favor llene los campos necesarios', 'error');
					}
					if(concept == "")
					{
						$('input[name="concept_new"]').addClass('error');
					}
					if(date == "")
					{
						$('.datepath_new').addClass('error');
					}
					if(subtotal == "")
					{
						$('input[name="amount_new"]').addClass('error');
					}
				}
				else if(cont)
				{
					fiscal_folio	= [];
					ticket_number	= [];
					timepath		= [];
					amount			= [];
					datepath		= [];
					
					if ($('.datepath_new').length > 0) 
					{
						$('.datepath_new').each(function(i,v)
						{
							fiscal_folio.push($(this).parents('.components-ex-down').find('.fiscal_folio_new').val());
							ticket_number.push($(this).parents('.components-ex-down').find('.ticket_number_new').val());
							timepath.push($(this).parents('.components-ex-down').find('.timepath_new').val());
							amount.push(Number($(this).parents('.components-ex-down').find('.amount_new').val()).toFixed(2));
							datepath.push($(this).parents('.components-ex-down').find('.datepath_new').val());
						});

						$.ajax(
						{
							type	: 'post',
							url		: '{{ route("expenses.validation-document") }}',
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
								$('.datepath_new').each(function(j,v)
								{

									ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_new');
									fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_new');
									timepath		= $(this).parents('.components-ex-down').find('.timepath_new');
									amount			= $(this).parents('.components-ex-down').find('.amount_new');
									datepath		= $(this).parents('.components-ex-down').find('.datepath_new');

									ticket_number.removeClass('error').removeClass('valid');
									fiscal_folio.removeClass('error').removeClass('valid');
									timepath.removeClass('error').removeClass('valid');
									amount.removeClass('error').removeClass('valid');
									datepath.removeClass('error').removeClass('valid');

									$(data).each(function(i,d)
									{
										if (j == d)
										{
											ticket_number.addClass('error')
											fiscal_folio.addClass('error');
											timepath.addClass('error');
											amount.addClass('error');
											datepath.addClass('error');
											flag = true;
										}
										else
										{
											ticket_number.addClass('valid')
											fiscal_folio.addClass('valid');
											timepath.addClass('valid');
											amount.addClass('valid');
											datepath.addClass('valid');
										}
									});
								});
								if (flag) 
								{
									swal('','Los documentos marcados ya se encuentran registrados.','error');
								}
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						})
						.done(function(data)
						{
							if (!flag) 
							{
								addTr(countConcept,amountAAtotal,idresourcedetail,concept,iva,ivaKind,fiscal,subtotal,account,accountText,date,path,ivaParam,ivaParam2,ivaCal,total,cont);
							}
						});
					}
					else
					{
						swal('','Por favor agregue al menos un documento','error');
					}

					function addTr(countConcept,amountAAtotal,idresourcedetail,concept,iva,ivaKind,fiscal,subtotal,account,accountText,date,path,ivaParam,ivaParam2,ivaCal,total,cont)
					{	
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "#"],
									["value" => "Concepto"],
									["value" => "Clasificación del gasto"],
									["value" => "Fiscal"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Importe"],
									["value" => "Documento(s)"],
									["value" => "Acciones"],
								]
							];
							
							$body = [ "classEx" => "tr_datepath",
								[
									"classEx"	=> "countConcept",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content"	=>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_concept[]\"",
											"classEx"		=> "t_concept"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idRDe[]\"",
											"classEx"		=> "idRDe idExpensesDetail"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_idresourcedetail[]\"",
											"classEx"		=> "idresourcedetail"	
										]
									] 
								],
								[
									"content" =>
									[
										[
											"kind" 		=> "components.labels.label",
											"classEx"	=> "accountTexts hidden"
 										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_account[]\"",
											"classEx"		=> "t_account"	
										]
									] 
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_fiscal[]\"",
										"classEx"		=> "t_fiscal"
									] 
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\"",
										"classEx"		=> "t-amount t_amount"
									] 
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva[]\"",
											"classEx"		=> "t_iva"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva_kind[]\"",
											"classEx"		=> "t-iva-kind"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_iva_val[]\"",
											"classEx"		=> "t-iva"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"tivakind[]\"",
											"classEx"		=> "t_iva_kind"	
										]
									] 
								],
								[
									"classEx"	=> "amount_AA_Total",
									"content"	=>
									[
										"label" => ""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_total[]\"",
										"classEx"		=> "amountTotalTotal"
									] 
								],
								[
									"classEx" => "docValues",
									"content" =>
									[
										[
											"kind"  => "components.labels.label",
											"label" => "",
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  		=> "components.buttons.button",
											"variant"		=> "success",
											"label" 		=> "<span class=\"icon-pencil\"></span>",
											"classEx" 		=> "edit-item",
											"attributeEx" 	=> "id=\"edit\" type=\"button\""
										],
										[
											"kind"  		=> "components.buttons.button",
											"variant"	 	=> "red",
											"label" 		=> "<span class=\"icon-x delete-span\"></span>",
											"attributeEx" 	=> "id=\"cancel\" type=\"button\"",
											"classEx" 		=> "delete-item"
										]
									]
								]
							];
							$modelBody[] = $body;

							$table2 = view('components.tables.table', [
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"themeBody" => "striped",
								"noHead"	=> "true"
							])->render();
						@endphp	
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
						row = $(table);
						
						tempFlag = 1;
						$(".path_new").each(function(i, v)
						{
							pathName		= $(this).val();
							fiscal_folio	= $(this).parents('.docs-p').find('.fiscal_folio_new').val();
							ticket_number	= $(this).parents('.docs-p').find('.ticket_number_new').val();
							amount			= $(this).parents('.docs-p').find('.amount_new').val();
							timepath		= $(this).parents('.docs-p').find('.timepath_new').val();
							datepath		= $(this).parents('.docs-p').find('.datepath_new').val();
							nameDoc			= $(this).parents('.docs-p').find('.name_document_new option:selected').val();

							@php
								$newButtonPDF = view("components.buttons.button", [
									"buttonElement" => "a",
									"attributeEx"	=> "href=\"#\" type=\"button\"",
									"variant" 		=> "secondary",
									"label"   		=> "Archivo",
									"classEx" 		=> "button_pdf",
								])->render();
							@endphp
							buttonPDF = '{!!preg_replace("/(\r)*(\n)*/", "", $newButtonPDF)!!}';
							row.find('.docValues').append($('<div class="nowrap"></div>').append($('.datepath_new').get(i).value)
									.append($(buttonPDF).attr('title',pathName))
									.append($('<input type="hidden" name="t_path'+doc+'[]" class="num_path">').val(pathName))
									.append($('<input type="hidden" name="t_fiscal_folio'+doc+'[]" class="num_fiscal_folio">').val(fiscal_folio))
									.append($('<input type="hidden" name="t_ticket_number'+doc+'[]" class="num_ticket_number">').val(ticket_number))
									.append($('<input type="hidden" name="t_amount'+doc+'[]" class="num_amount">').val(amount))
									.append($('<input type="hidden" name="t_timepath'+doc+'[]" class="num_timepath">').val(timepath))
									.append($('<input type="hidden" name="t_datepath'+doc+'[]" class="num_datepath">').val(datepath))
									.append($('<input type="hidden" name="t_name_doc'+doc+'[]" class="num_name_doc">').val(nameDoc))
									.append($('<input type="hidden" name="t_new'+doc+'[]" class="num_new">').val(1)));
						});

						nameAmounts = $('<div hidden></div>');
						$('.additional_newName').each(function(i,v)
						{
							nameAmount = $(this).val();
							nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+doc+'[]">').val(nameAmount));
						});

						amountsAA = $('<div hidden></div>');
						$('.additional_newAmount').each(function(i,v)
						{
							amountAA = $(this).val();
							amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+doc+'[]">').val(amountAA));
							amountAAtotal = Number(amountAAtotal)+ Number(amountAA);
						});

						countConcept = countConcept+1;
						row.find('.countConcept').prepend(countConcept);
						row.find('.idExpensesDetail').val('x');
						row.find('.idresourcedetail').val(idresourcedetail);
						concept = String(concept).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
						row.find('.t_concept').parent().prepend(concept);
						row.find('.t_concept').val(concept);
						row.find('.accountTexts').parent().prepend(accountText);
						row.find('.accountTexts').text(accountText);
						row.find('.t_account').val(account);
						row.find('.t_fiscal').parent().prepend(fiscal);
						row.find('.t_fiscal').val(fiscal);
						row.find('.t_amount').parent().prepend('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.t_amount').val(subtotal);
						row.find('.t_iva').parent().prepend('$ '+Number(ivaCal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.t-iva').val(ivaCal);
						row.find('.t_iva').val(iva);
						row.find('.t-iva-kind').val(ivaKind);
						row.find('.t_iva_kind').val(ivaKind);
						row.find('.amount_AA_Total').prepend('$ '+Number(amountAAtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.amountTotalTotal').parent().prepend('$ '+Number(total+amountAAtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
						row.find('.amountTotalTotal').val(total);
						row.find('.docValues').append(nameAmounts).append(amountsAA);
						$('#body').append(row);
						$('.js-accounts').val(null).trigger('change');
						$('input[name="idresourcedetail_new"]').val('');
						$('input[name="concept_new"]').val('');
						$('input[name="path_new"]').val('');
						$('.datepath_new').val('');
						$('input[name="document_new"]').val('');
						$('input[name="account_new"]').val('');
						$('input[name="account_id_new"]').val('');
						$('#nofiscal_new').prop("checked",true);
						$('#noiva_new,#iva_a_new').prop('checked',true);
						$('#no_additional_new').prop("checked",true);
						$('.iva,.iva_kind').prop('disabled',true);
						$('input[name="amount_new"]').val('');
						$('input[name="concept_new"]').removeClass('error');
						$('input[name="path_new"]').removeClass('error');
						$('.datepath_new').removeClass('error');
						$('input[name="amount_new"]').removeClass('error');
						$('input[name="document_new"]').removeClass('error');
						$('#documents_new').empty();
						$('.add-concept').removeAttr('disabled');
						$('.reintegro').val('');
						$('.reembolso').val('');
						$('.contentMoney_new').val('');
						$('#newsImpuestos_new').empty();
						$('.additional_newName').val('');
						$('.additional_newAmount').val('');
						$('#taxes_new').stop(true,true).slideUp().hide();
						$('#documents_new').html('');
						additional_newCleanComponent();
						total_cal();
						refund();
						doc++;
						$(this).removeClass("disabled");
						if($("#documents_new .name_document_new").length == 0)
						{
							addNewDocument(true, "new");
						}
					}
				}
			}
		})
		.on('click','.edit-item',function()
		{
			concept		= $('input[name="concept_exist"]').val().trim();
			date		= $('.datepath_exist').length;
			account		= $('.js-accounts').val();
			path		= $('input[name="realPath"]').length;
			amount		= $('input[name="amount_exist"]').val().trim();
			conceptN	= $('input[name="concept_new"]').val().trim();
			dateN		= $('.datepath_new').length;
			amountN		= $('input[name="amount_new"]').val().trim();
			if (concept != "" || date > 0 || amount != "" || account != "" || path > 0 || conceptN != "" || dateN > 0 || amountN != "") 
			{
				swal('', 'Tiene conceptos sin agregar', 'error');
				return false;
			}
			else
			{
				generalSelect({'selector':'.js-accounts', 'depends':'[name="enterprise_id"]', 'model':10});
				$('.js-accounts').html('');
				idRDe					= $(this).parents('.tr_datepath').find('.idRDe').val();
				t_concept				= $(this).parents('.tr_datepath').find('.t_concept').val();
				t_account				= $(this).parents('.tr_datepath').find('.t_account').val();
				t_account_description	= $(this).parents('.tr_datepath').find('.accountTexts').text().trim();
				t_document				= $(this).parents('.tr_datepath').find('.t_document').val();
				t_fiscal				= $(this).parents('.tr_datepath').find('.t_fiscal').val();
				t_amount				= $(this).parents('.tr_datepath').find('.t_amount').val();
				t_iva					= $(this).parents('.tr_datepath').find('.t_iva').val();
				t_iva_kind				= $(this).parents('.tr_datepath').find('.t_iva_kind').val();
				t_total_total			= $(this).parents('.tr_datepath').find('.amountTotalTotal').val();

				swal({
					title		: "Editar concepto",
					text		: "Al editar, se eliminarán los impuestos adicionales y documentos agregados y deberá cargarlos de nuevo ¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((continuar) =>
				{
					if(continuar)
					{
						$('#doc_exist').prop('checked',false);
						$('#doc_new').prop('checked',true);
						$('#docs_new').stop(true,true).slideDown();
						$('#docs_exist').stop(true,true).slideUp();
						if(t_fiscal == 'si')
						{
							$('.iva,.iva_kind').removeAttr('disabled',false);
							$('#fiscal_new').prop("checked",true);
							if (t_iva == 'si') 
							{
								$('#siiva_new').prop("checked",true);
								if (t_iva_kind == 'a') 
								{
									$('#iva_a_new').prop("checked",true);
								}
								else
								{
									$('#iva_b_new').prop("checked",true);
								}
							}
							else
							{
								$('#noiva_new').prop("checked",true);
							}
						}
						else
						{
							$('.iva,.iva_kind').prop('disabled',true);
							$('#nofiscal_new').prop("checked",true);
							$('#noiva_new').prop('checked',true);
							$('#iva_a_new').prop('checked',true);
						}
						$('input[name="concept_new"]').val(t_concept);
						$('.js-accounts').append(new Option(t_account_description, t_account, true, true)).trigger('change');
						$('input[name="document_new"]').val(t_document);
						$('input[name="amount_new"]').val(t_amount);
						$('[name="contentMoney_new"]').val(t_total_total);
						id = $(this).parents('.tr_datepath').find('.idExpensesDetail').val();
						if(id!='x')
						{
							$('#invisible').append($('<input type="hidden" name="delete[]"/>').val(id));
						}
						folio 				= $('select[name="resources_id"] option:selected').val();
						idresourcedetail 	= $(this).parents('.tr_datepath').find('.idresourcedetail').val();
						idExpensesDetail 	= $(this).parents('.tr_datepath').find('.idExpensesDetail').val();

						if (folio != "" && idresourcedetail != "") 
						{
							$.ajax(
							{
								type 	: 'post',
								url 	: '{{ route("expenses.resource.detaildelete") }}',
								data 	: {
									'folio':folio,
									'idresourcedetail':idresourcedetail,
									'idExpensesDetail':idExpensesDetail},
								success : function(data)
								{
									$('#body-classify').append(data);
									$('#documents-resource').show();
								},
								error: function(data)
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
									$('#body-classify').html('');
									$('#documents-resource').hide();
								}
							})
						}

						selector = $(this);
						swal(
						{
							icon 	: '{{ url(getenv('LOADING_IMG')) }}',
							button	: false
						});
						newDoc = $(this).parents('.tr_datepath').find('.num_new').val();
						if(newDoc==1)
						{
							uploadedName	= $(this).parents('.tr_datepath').find('.num_path');
							formData		= new FormData();
							formData.append('realPath',uploadedName.val());
							$.ajax(
							{
								type		: 'post',
								url			: '{{ route("expenses.upload") }}',
								data		: formData,
								contentType	: false,
								processData	: false,
								success		: function(r)
								{

								},
								error: function(data)
								{
									swal('','Lo sentimos ocurrió un error en la conexión, por favor intente de nuevo.','error');
								}
							});
						}
						setTimeout(function()
						{
							selector.parents('.tr_datepath').remove();
							$('.reintegro').val('');
							$('.reembolso').val('');
							$('.reintegroLabel').text('$ 0.00');
							$('.reembolsoLabel').text('$ 0.00');
							total_cal();
							refund();
							doc = $('#body .tr_datepath').length;
							$('#body .tr_datepath').each(function(i,v)
							{
								$(this).find('.num_path').attr('name','t_path'+i+'[]');
								$(this).find('.num_name_doc').attr('name','num_name_doc'+i+'[]');
								$(this).find('.num_fiscal_folio').attr('name','num_fiscal_folio'+i+'[]');
								$(this).find('.num_ticket_number').attr('name','num_ticket_number'+i+'[]');
								$(this).find('.num_timepath').attr('name','num_timepath'+i+'[]');
								$(this).find('.num_amount').attr('name','num_amount'+i+'[]');
								$(this).find('.num_new').attr('name','t_new'+i+'[]');
								$(this).find('.num_datepath').attr('name','t_datepath'+i+'[]');
								$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
								$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
							});
							if($('.countConcept').length>0)
							{
								$('.countConcept').each(function(i,v)
								{
									$(this).html(i+1);
								});
							}
							swal.close();
						},500);
					}
					else
					{
						swal.close();
					}
				});
			}
		})
		.on('click','[name="enviar"]',function(e)
		{
			e.preventDefault();
			object 		= $(this);
			counter		= $('.num_datepath').length;
			
			swal({
				title              : 'Cargando',
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
				text               : 'Validando los documentos',
				closeOnClickOutside: false,
				closeOnEsc         : false
			});

			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];
			requestFolio 	= {{ $request->folio }};
			
			if ($('.num_datepath').length > 0) 
			{
				$('.num_datepath').each(function(i,v)
				{
					fiscal_folio.push($(this).parents('.nowrap').find('.num_fiscal_folio').val());
					ticket_number.push($(this).parents('.nowrap').find('.num_ticket_number').val());
					timepath.push($(this).parents('.nowrap').find('.num_timepath').val());
					amount.push(Number($(this).parents('.nowrap').find('.num_amount').val()).toFixed(2));
					datepath.push($(this).parents('.nowrap').find('.num_datepath').val());
				});

				$.ajax(
				{
					type	: 'post', 
					url		: '{{ route("expenses.validation-document") }}',
					data	: 
					{
						'fiscal_folio'	: fiscal_folio,
						'ticket_number'	: ticket_number,
						'timepath'		: timepath,
						'amount'		: amount,
						'datepath'		: datepath,
						'requestFolio' 	: requestFolio
					},
					success : function(data)
					{
						flag = false;
						$('.num_datepath').each(function(j,v)
						{
							tr = $(this);

							$(data).each(function(i,d)
							{
								if (j == d)
								{
									tr.parents('.tr_datepath').addClass('tr-red');
									flag = true;
								}
							});
						});
						if (flag) 
						{
							swal('','Los conceptos marcados contienen documentos que ya han sido utilizados en otra solicitud.','error');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if (!flag) 
					{
						sendForm(object);
					}
				});
			}
			else
			{
				sendForm(object);
			}

			function sendForm(object) 
			{
				if ($('.tr-red').length == 0) 
				{
					form	= object.parents('form');
					form.submit();
				}
				else
				{
					swal('','Los conceptos marcados contienen documentos que ya han sido utilizados en otra solicitud.','error');
				}
			}
		})
		.on('click','#save',function(e)
		{
			e.preventDefault();
			object 		= $(this);
			counter		= $('.num_datepath').length;
			
			swal({
				title              : 'Cargando',
				icon               : '{{ asset(getenv('LOADING_IMG')) }}',
				button             : false,
				text               : 'Validando los documentos',
				closeOnClickOutside: false,
				closeOnEsc         : false
			});

			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];
			requestFolio 	= {{ $request->folio }};
			
			if ($('.num_datepath').length > 0) 
			{
				$('.num_datepath').each(function(i,v)
				{
					fiscal_folio.push($(this).parents('.nowrap').find('.num_fiscal_folio').val());
					ticket_number.push($(this).parents('.nowrap').find('.num_ticket_number').val());
					timepath.push($(this).parents('.nowrap').find('.num_timepath').val());
					amount.push(Number($(this).parents('.nowrap').find('.num_amount').val()).toFixed(2));
					datepath.push($(this).parents('.nowrap').find('.num_datepath').val());
				});

				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("expenses.validation-document") }}',
					data	: 
					{
						'fiscal_folio'	: fiscal_folio,
						'ticket_number'	: ticket_number,
						'timepath'		: timepath,
						'amount'		: amount,
						'datepath'		: datepath,
						'requestFolio' 	: requestFolio
					},
					success : function(data)
					{
						flag = false;
						$('.num_datepath').each(function(j,v)
						{
							tr = $(this);

							$(data).each(function(i,d)
							{
								if (j == d)
								{
									tr.parents('.tr_datepath').addClass('tr-red');
									flag = true;
								}
							});
						});
						if (flag) 
						{
							swal('','Los conceptos marcados contienen documentos que ya han sido utilizados en otra solicitud.','error');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if (!flag) 
					{
						sendForm(object);
					}
				});
			}
			else
			{
				sendForm(object);
			}

			function sendForm(object) 
			{
				if ($('.tr-red').length == 0) 
				{
					$('.removeselect').removeAttr('required');
					$('.removeselect').removeAttr('data-validation');
					$('.request-validate').removeClass('request-validate');
					action	= object.attr('formaction');
					form	= object.parents('form');
					form.attr('action',action);
					form.submit();
				}
				else
				{
					swal('','Los conceptos marcados contienen documentos que ya han sido utilizados en otra solicitud.','error');
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
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','.js-resources',function()
		{
			$('#dates').stop(true,true).slideUp();
			folio 		= $(this).val();
			if (folio != "") 
			{
				$('input[name="concept_exist"]').attr('readonly',true);
				$('input[name="account_exist"]').attr('readonly',true);
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("expenses.resource.detail") }}',
					data 	: {'folio':folio},
					success : function(data)
					{
						$('#body-classify').html(data);	
						$('#documents-resource').show();
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#body-classify').html('');
						$('#documents-resource').hide();
					}
				});
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("expenses.resource.total") }}',
					data 	: {'folio':folio},
					success : function(data)
					{
						$('.totalResource').val(data);
						$('.totalResourceLabel').text('$ '+Number($('[name="totalResource"]').val()).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					},
					error: function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.totalResource').val('');
					}
				});
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("expenses.dates") }}',
					data 	: {'folio':folio},
					success : function(data)
					{
						$('#dates').html(data);
						$('#dates').stop(true,true).slideDown();
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#dates').html('');
					}
				});
			}
			else
			{
				$('input[name="concept_exist"]').attr('readonly',false);
				$('input[name="account_exist"]').attr('readonly',false);
				$('#documents-resource').hide();
				$(".totalResource").val('');
				$(".subtotal").val('');
				$(".ivaTotal").val('');
				$(".total").val('');
				$(".reintegro").val('');
				$(".reembolso").val('');
				$('#dates').html('');
				$(".totalResourceLabel").text('$ 0.00');
				$(".subtotalLabel").text('$ 0.00');
				$(".ivaTotalLabel").text('$ 0.00');
				$(".totalLabel").text('$ 0.00');
				$(".reintegroLabel").text('$ 0.00');
				$(".reembolsoLabel").text('$ 0.00');
				$(".labelAmount").text('$ 0.00');
			}
		})
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts').empty();
			generalSelect({ 'selector' : '.js-accounts', 'depends' : '.js-enterprises', 'model' : 10 });
		})
		.on('click','.delete-item',function()
		{
			id = $(this).parents('.tr_datepath').find('.idExpensesDetail').val();
			if(id!='x')
			{
				$('#invisible').append($('<input type="hidden" name="delete[]"/>').val(id));
			}
			folio 				= $('select[name="resources_id"] option:selected').val();
			idresourcedetail 	= $(this).parents('.tr_datepath').find('.idresourcedetail').val();
			idExpensesDetail 	= $(this).parents('.tr_datepath').find('.idExpensesDetail').val();

			if (folio != "" && idresourcedetail != "") 
			{
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("expenses.resource.detaildelete") }}',
					data 	: {
						'folio':folio,
						'idresourcedetail':idresourcedetail,
						'idExpensesDetail':idExpensesDetail},
					success : function(data)
					{
						$('#body-classify').append(data);
						$('#documents-resource').show();
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#body-classify').html('');
						$('#documents-resource').hide();
					}
				})
			}

			selector = $(this);
			swal(
			{
				icon 	: '{{ url(getenv('LOADING_IMG')) }}',
				button	: false
			});
			newDoc = $(this).parents('.tr_datepath').find('.num_new').val();
			if(newDoc==1)
			{
				uploadedName	= $(this).parents('.tr_datepath').find('.num_path');
				formData		= new FormData();
				formData.append('realPath',uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("expenses.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{

					},
					error: function(data)
					{
						swal('','Lo sentimos ocurrió un error en la conexión, por favor intente de nuevo.','error');
					}
				});
			}
			setTimeout(function()
			{
				selector.parents('.tr_datepath').remove();
				$('.reintegro').val('');
				$('.reembolso').val('');
				$('.reintegroLabel').text('$ 0.00');
				$('.reembolsoLabel').text('$ 0.00');
				total_cal();
				refund();
				doc = $('#body .tr_datepath').length;
				$('#body .tr_datepath').each(function(i,v)
				{
					$(this).find('.num_path').attr('name','t_path'+i+'[]');
					$(this).find('.num_name_doc').attr('name','num_name_doc'+i+'[]');
					$(this).find('.num_fiscal_folio').attr('name','num_fiscal_folio'+i+'[]');
					$(this).find('.num_ticket_number').attr('name','num_ticket_number'+i+'[]');
					$(this).find('.num_timepath').attr('name','num_timepath'+i+'[]');
					$(this).find('.num_amount').attr('name','num_amount'+i+'[]');
					$(this).find('.num_new').attr('name','t_new'+i+'[]');
					$(this).find('.num_datepath').attr('name','t_datepath'+i+'[]');
					$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
					$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
				});
				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
				swal.close();
			},500);
		})
		.on('click','.add-concept',function()
		{
			if ($('input[name="exist_new"]:checked').val() == "exist") 
			{
				$('.add-concept').attr('disabled',true);
				$('input[name="idresourcedetail_exist"]').val($(this).parents('.tr_detail').find('.idresourcedetail-table').val());
				$('input[name="concept_exist"]').val($(this).parents('.tr_detail').find('.concept-table').val());
				$('input[name="account_id_exist"]').val($(this).parents('.tr_detail').find('.accountid-table').val());
				$('input[name="account_exist"]').val($(this).parents('.tr_detail').find('.account-table').val());
				$('input[name="amount_exist"]').val($(this).parents('.tr_detail').find('.amount-table').val());
				$('input[name="contentMoney"]').val($(this).parents('.tr_detail').find('.amount-table').val());
				$(this).parents('.tr_detail').remove();
		
				if ($('#body-classify .tr_detail').length<=0) 
				{
					$('#documents-resource').hide();
				}	
			}
			else
			{
				$('.add-concept').attr('disabled',true);
				$('input[name="idresourcedetail_new"]').val($(this).parents('.tr_detail').find('.idresourcedetail-table').val());
				$('input[name="concept_new"]').val($(this).parents('.tr_detail').find('.concept-table').val());
				$('input[name="account_id_new"]').val($(this).parents('.tr_detail').find('.accountid-table').val());
				$('input[name="account_new"]').val($(this).parents('.tr_detail').find('.account-table').val());
				$('input[name="amount_new"]').val($(this).parents('.tr_detail').find('.amount-table').val());
				$(this).parents('.tr_detail').remove();
			}
		})
		.on('change', '.js-users', function(){
			id 		= $(this).val();
			folio 	= $('#id'+id).text();
			$('#efolio').val(folio);
	
			$text = $('#efolio').val();
			$.ajax({
				type : 'post',
				url  : '{{ route("expenses.search.bank") }}',
				data : {'idUsers':id},
				success:function(data)
				{
					$('.resultbank').html(data);
				},
				error: function(data)
				{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.resultbank').html('');
					}
				}); 
		})
		.on('change','.js-users',function()
		{
			$('#documents-resource').hide();
			$('.js-resources').html('');
			generalSelect({'selector':'.js-resources', 'depends':'.js-users', 'model':7, 'user': $('.js-users option:selected').val()});
		})
		.on('click','.checkbox',function()
		{
			$('.marktr').removeClass('marktr');
			$(this).parents('tr').addClass('marktr');
		})
		.on('change','input[name="exist_new"]',function()
		{
			$('.js-accounts').html('');
			if ($('input[name="exist_new"]:checked').val() == "exist") 
			{
				$('#docs_exist').stop(true,true).slideDown();
				$('#docs_new').stop(true,true).slideUp();
				$('input[name="concept"]').attr('readonly',true);
				$('input[name="account"]').attr('readonly',true);
			}
			else if ($('input[name="exist_new"]:checked').val() == "new") 
			{
				$('#docs_new').stop(true,true).slideDown();
				$('#docs_exist').stop(true,true).slideUp();
				$('input[name="concept"]').attr('readonly',false);
				$('input[name="account"]').attr('readonly',false);
			}
			generalSelect({ 'selector' : '.js-accounts', 'depends' : '[name="enterprise_id"]', 'model' : 10, 'title': 'Folio'});
			if($("#documents_exist .name_document_new").length == 0)
			{
				addNewDocument(true, "exist");
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
		.on('click','#help-btn-method-pay',function()
		{
			swal('Ayuda','En este apartado debe seleccionar la forma de pago, si usted selecciona "Cuenta Bancaria", deberá seleccionar una de las cuentas que se le muestran del "Solicitante", en caso de que no tenga cuenta, por favor solicite al "Solicitante" que agregue al menos una cuenta bancaria.','info');
		})
		.on('click','#help-btn-documents',function()
		{
			swal('Ayuda','En este apartado hay dos opciones, una es seleccionar la opción "Existene" en caso de tomar los conceptos solicitados en la solicitud de asignación de recurso. La opción "Nuevo" es para agregar nuevos conceptos en caso de que se hayan realizado otros gastos que no estaban contemplados en la solicitud de asignación de recurso.','info');
		})
		.on('change','.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
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
					url			: '{{ route("expenses.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
					}
				})
			}
		})
		.on('change','.name_document',function()
		{
			type_document = $('option:selected',this).val();
			switch(type_document)
			{
				case 'Factura': 
					$(this).parents('.docs-p').find('.fiscal_folio_label').show();
					$(this).parents('.docs-p').find('.fiscal_folio').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.ticket_number_label').hide();
					$(this).parents('.docs-p').find('.ticket_number').hide().val('');
					$(this).parents('.docs-p').find('.amount_label').hide();
					$(this).parents('.docs-p').find('.amount').hide().val('');
					$(this).parents('.docs-p').find('.timepath_label').show();
					$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.docs-p').find('.datepath_label').show();					
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
				case 'Ticket': 
					$(this).parents('.docs-p').find('.fiscal_folio_label').hide();
					$(this).parents('.docs-p').find('.fiscal_folio').hide().val('');
					$(this).parents('.docs-p').find('.ticket_number_label').show();
					$(this).parents('.docs-p').find('.ticket_number').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.amount_label').show();
					$(this).parents('.docs-p').find('.amount').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.timepath_label').show();
					$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.docs-p').find('.datepath_label').show();	
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
				default : 
					$(this).parents('.docs-p').find('.fiscal_folio_label').hide();
					$(this).parents('.docs-p').find('.ticket_number_label').hide();
					$(this).parents('.docs-p').find('.amount_label').hide();
					$(this).parents('.docs-p').find('.timepath_label').hide();
					$(this).parents('.docs-p').find('.fiscal_folio').hide().val('');
					$(this).parents('.docs-p').find('.ticket_number').hide().val('');
					$(this).parents('.docs-p').find('.amount').hide().val('');
					$(this).parents('.docs-p').find('.timepath').hide().val('');
					$(this).parents('.docs-p').find('.datepath_label').show();	
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
			}
		})
		.on('change','.fiscal_folio_exist,.ticket_number_exist,.timepath_exist,.amount_exist,.datepath_exist',function()
		{
			$('.datepath_exist').each(function(i,v)
			{
				row					= 0;
				first_fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_exist');
				first_ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_exist');
				first_amount		= $(this).parents('.components-ex-down').find('.amount_exist');
				first_timepath		= $(this).parents('.components-ex-down').find('.timepath_exist');
				first_datepath		= $(this).parents('.components-ex-down').find('.datepath_exist');
				first_name_doc		= $(this).parents('.components-ex-down').siblings('components-ex-up').find('.name_document_exist option:selected').val();
				$('.datepath_exist').each(function(j,v)
				{
					if(i!==j)
					{
						scnd_fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_exist').val();
						scnd_ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_exist').val();
						scnd_amount			= $(this).parents('.components-ex-down').find('.amount_exist').val();
						scnd_timepath		= $(this).parents('.components-ex-down').find('.timepath_exist').val();
						scnd_datepath		= $(this).parents('.components-ex-down').find('.datepath_exist').val();
						scnd_name_doc		= $(this).parents('.components-ex-down').siblings('.components-ex-up').find('.name_document_exist option:selected').val();

						if (scnd_name_doc == "Factura") 
						{
							if (first_fiscal_folio.val() != "" && first_timepath.val() != "" && first_datepath.val() != "" && scnd_name_doc == first_name_doc && scnd_datepath == first_datepath.val() && scnd_timepath == first_timepath.val() && scnd_fiscal_folio.toUpperCase() == first_fiscal_folio.val().toUpperCase()) 
							{
								swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
								first_fiscal_folio.val('').addClass('error');
								first_timepath.val('').addClass('error');
								first_datepath.val('').addClass('error');
								return;
							}
						}

						if (scnd_name_doc == "Ticket") 
						{
							if (first_ticket_number.val() != "" && first_amount.val() != "" && first_timepath.val() != "" && first_datepath.val() != "" && scnd_name_doc == first_name_doc && scnd_datepath == first_datepath.val() && scnd_timepath == first_timepath.val() && scnd_ticket_number.toUpperCase() == first_ticket_number.val().toUpperCase() && Number(scnd_amount).toFixed(2) == Number(first_amount.val()).toFixed(2)) 
							{
								swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
								first_ticket_number.val('').addClass('error');
								first_timepath.val('').addClass('error');
								first_datepath.val('').addClass('error');
								first_amount.val('').addClass('error');
								return;
							}
						}
					}
				});
				$('.num_name_doc').each(function()
				{
					name = $(this).val();
					if (name == "Factura") 
					{
						folio		= $(this).parent('.nowrap').find('.num_fiscal_folio').val();
						datepath	= $(this).parent('.nowrap').find('.num_datepath').val();
						timepath	= $(this).parent('.nowrap').find('.num_timepath').val();
						if (name == first_name_doc && datepath == first_datepath.val() && timepath == first_timepath.val() && folio.toUpperCase() == first_fiscal_folio.val().toUpperCase()) 
						{
							swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
							first_fiscal_folio.val('').addClass('error');
							first_timepath.val('').addClass('error');
							first_datepath.val('').addClass('error');
							return;
						}
					}
					if (name == "Ticket") 
					{
						ticket		= $(this).parent('.nowrap').find('.num_ticket_number').val();
						datepath	= $(this).parent('.nowrap').find('.num_datepath').val();
						timepath	= $(this).parent('.nowrap').find('.num_timepath').val();
						amount 		= $(this).parent('.nowrap').find('.num_amount').val();
						if (name == first_name_doc && datepath == first_datepath.val() && timepath == first_timepath.val() && ticket.toUpperCase() == first_ticket_number.val().toUpperCase() && Number(amount).toFixed(2) == Number(first_amount.val()).toFixed(2))
						{
							swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
							first_ticket_number.val('').addClass('error');
							first_timepath.val('').addClass('error');
							first_datepath.val('').addClass('error');
							first_amount.val('').addClass('error');
							return;
						}
					}
				});
			});
		})
		.on('change','.fiscal_folio_new,.ticket_number_new,.timepath_new,.amount_new,.datepath_new',function()
		{
			$('.datepath_new').each(function(i,v)
			{
				row					= 0;
				first_fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_new');
				first_ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_new');
				first_amount		= $(this).parents('.components-ex-down').find('.amount_new');
				first_timepath		= $(this).parents('.components-ex-down').find('.timepath_new');
				first_datepath		= $(this).parents('.components-ex-down').find('.datepath_new');
				first_name_doc		= $(this).parents('.components-ex-down').siblings('.components-ex-up').find('.name_document_new option:selected').val();
				$('.datepath_new').each(function(j,v)
				{
					if(i!==j)
					{
						scnd_fiscal_folio	= $(this).parents('.components-ex-down').find('.fiscal_folio_new').val();
						scnd_ticket_number	= $(this).parents('.components-ex-down').find('.ticket_number_new').val();
						scnd_amount			= $(this).parents('.components-ex-down').find('.amount_new').val();
						scnd_timepath		= $(this).parents('.components-ex-down').find('.timepath_new').val();
						scnd_datepath		= $(this).parents('.components-ex-down').find('.datepath_new').val();
						scnd_name_doc		= $(this).parents('.components-ex-down').siblings('.components-ex-up').find('.name_document_new option:selected').val();

						if (scnd_name_doc == "Factura") 
						{
							if (first_fiscal_folio.val() != "" && first_timepath.val() != "" && first_datepath.val() != "" && scnd_name_doc == first_name_doc && scnd_datepath == first_datepath.val() && scnd_timepath == first_timepath.val() && scnd_fiscal_folio.toUpperCase() == first_fiscal_folio.val().toUpperCase()) 
							{
								swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
								first_fiscal_folio.val('').addClass('error');
								first_timepath.val('').addClass('error');
								first_datepath.val('').addClass('error');
								return;
							}
						}

						if (scnd_name_doc == "Ticket") 
						{
							if (first_ticket_number.val() != "" && first_amount.val() != "" && first_timepath.val() != "" && first_datepath.val() != "" && scnd_name_doc == first_name_doc && scnd_datepath == first_datepath.val() && scnd_timepath == first_timepath.val() && scnd_ticket_number.toUpperCase() == first_ticket_number.val().toUpperCase() && Number(scnd_amount).toFixed(2) == Number(first_amount.val()).toFixed(2)) 
							{
								swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
								first_ticket_number.val('').addClass('error');
								first_timepath.val('').addClass('error');
								first_datepath.val('').addClass('error');
								first_amount.val('').addClass('error');
								return;
							}
						}
					}
				});
				$('.num_name_doc').each(function()
				{
					name = $(this).val();
					if (name == "Factura") 
					{
						folio		= $(this).parent('.nowrap').find('.num_fiscal_folio').val();
						datepath	= $(this).parent('.nowrap').find('.num_datepath').val();
						timepath	= $(this).parent('.nowrap').find('.num_timepath').val();
						if (name == first_name_doc && datepath == first_datepath.val() && timepath == first_timepath.val() && folio.toUpperCase() == first_fiscal_folio.val().toUpperCase()) 
						{
							swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
							first_fiscal_folio.val('').addClass('error');
							first_timepath.val('').addClass('error');
							first_datepath.val('').addClass('error');
							return;
						}
					}
					if (name == "Ticket") 
					{
						ticket		= $(this).parent('.nowrap').find('.num_ticket_number').val();
						datepath	= $(this).parent('.nowrap').find('.num_datepath').val();
						timepath	= $(this).parent('.nowrap').find('.num_timepath').val();
						amount 		= $(this).parent('.nowrap').find('.num_amount').val();
						if (name == first_name_doc && datepath == first_datepath.val() && timepath == first_timepath.val() && ticket.toUpperCase() == first_ticket_number.val().toUpperCase() && Number(amount).toFixed(2) == Number(first_amount.val()).toFixed(2)) 
						{
							swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
							first_ticket_number.val('').addClass('error');
							first_timepath.val('').addClass('error');
							first_datepath.val('').addClass('error');
							first_amount.val('').addClass('error');
							return;
						}
					}
				});
			});
		})
	})
	.on('change','.additional_existAmount,.addiotional,.amount_exist,.iva,.iva_kind,.fiscal', function()
	{
		amountAdditionalExist();
	})
	.on('change','.additional_newAmount,.addiotional,.amount_new,.iva,.iva_kind,.fiscal', function()
	{
		amountAdditionalNew();
	});

	function amountAdditionalExist()
	{
		cant 			= $('input[name="amount_exist"]').val();
		iva	 			= ("{{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }}")/100;
		iva2 			= ("{{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }}")/100;
		total 			= 0;
		ivaCalc 		= 0;
		totalImporte 	= 0;
		taxAditional 	= 0;

		$('.amount_exist').each(function()
		{ 
			if($(this).val())
			{
				total+=parseFloat($(this).val()); 
			} 
		});
		if($('input[name="iva_exist"]:checked').val() == 'si')
		{
			switch($('input[name="iva_kind_exist"]:checked').val())
			{
				case 'a':
					ivaCalc = cant*iva;
				break;
				case 'b':
					ivaCalc = cant*iva2;
				break;
			}
		}
		if($('input[name="additional_exist"]:checked').val() == 'si')
		{
			$('.additional_existAmount').each(function()
			{ 
				if($(this).val())
				{
					taxAditional+=parseFloat($(this).val()); 
				} 
			});
		}
		if($('input[name="fiscal_exist"]:checked').val() == 'no')
		{
			totalImporte = total+taxAditional;
		}
		else
		{
			totalImporte = total+ivaCalc+taxAditional;
		}
		$('input[name="contentMoney"]').val(totalImporte.toFixed(2));
	}

	function amountAdditionalNew()
	{
		cant 			= $('input[name="amount_new"]').val();
		iva	 			= ("{{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }}")/100;
		iva2 			= ("{{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }}")/100;
		total 			= 0;
		ivaCalc 		= 0;
		totalImporte 	= 0;
		taxAditional 	= 0;

		$('.amount_new').each(function()
		{ 
			if($(this).val())
			{
				total+=parseFloat($(this).val()); 
			} 
		});
		if($('input[name="iva_new"]:checked').val() == 'si')
		{
			switch($('input[name="iva_kind_new"]:checked').val())
			{
				case 'a':
					ivaCalc = cant*iva;
				break;
				case 'b':
					ivaCalc = cant*iva2;
				break;
			}
		}
		if($('input[name="additional_new"]:checked').val() == 'si')
		{
			$('.additional_newAmount').each(function()
			{ 
				if($(this).val())
				{
					taxAditional+=parseFloat($(this).val()); 
				} 
			});
		}
		if($('input[name="fiscal_new"]:checked').val() == 'no')
		{
			totalImporte = total+taxAditional;
		}
		else
		{
			totalImporte = total+ivaCalc+taxAditional;
		}
		$('input[name="contentMoney_new"]').val(totalImporte.toFixed(2));
	}
	
	function total_cal()
	{
		subtotal	= 0;
		ivaTotal	= 0;
		amountAA 	= 0;
		$("#body .tr_datepath").each(function(i, v)
		{
			ivaTotal	+= Number($(this).find('.t-iva').val());
			subtotal	+= Number($(this).find('.t-amount').val());
			tempAA 		= null;
			$(".num_amountAdditional").each(function(i, v)
			{
				tempAA 		+= Number($(this).val());
			});
			amountAA 	= Number(tempAA);
		});
		total	= subtotal+ivaTotal+amountAA;
		$(".subtotal").val(Number(subtotal).toFixed(2));
		$(".ivaTotal").val(Number(ivaTotal).toFixed(2));
		$(".total").val(Number(total).toFixed(2));
		$('input[name="amountAA"]').val(Number(amountAA).toFixed(2));
		$(".subtotalLabel").text('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$(".ivaTotalLabel").text('$ '+Number(ivaTotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$(".totalLabel").text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$(".labelAmount").text('$ '+Number(amountAA).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
	}

	function addNewDocument(noButtons, statusDocument)
	{
		@php
			$options = collect();
			$options= $options->concat([["value"=>"Ticket", "description"=> "Ticket"]]);
			$options= $options->concat([["value"=>"Factura", "description"=> "Factura"]]);
			$options= $options->concat([["value"=>"Otro", "description"=> "Otro"]]);
			$docs_upload = view("components.documents.upload-files",[
				"classExInput"			=> "pathActioner",
				"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
				"classExDelete"			=> "delete-doc",
				"attributeExRealPath"	=> "type=\"hidden\" name=\"realPath\"",
				"classExRealPath"		=> "path_new",
				"componentsExUp"		=> [
												[
													"kind" => "components.labels.label", 
													"label" => "Seleccione el tipo de documento:"
												],
												[
													"kind" 			=> "components.inputs.select", 
													"options" 		=> $options,
													"classEx" 		=> "name_document name_document_new",
													"attributeEx"	=> "data-validation=\"required\"" 
												],
											],
				"componentsExDown"		=>	[
												[
													"kind" 			=> "components.labels.label", 
													"label" 		=> "Seleccione la fecha:",
													"classEx"		=> "datepath_label hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"text\" name=\"datepath_new\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"",
													"classEx"		=> "hidden mb-4 removeselect datepicker datepath datepath_new"
												],
												[
													"kind" 			=> "components.labels.label", 
													"label" 		=> "Seleccione la hora:",
													"classEx"		=> "timepath_label hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"text\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\"",
													"classEx"		=> "hidden mb-4 removeselect timepath timepath_new",
												],
												[
													"kind" 			=> "components.labels.label", 
													"label"			=> "Folio Fiscal:",
													"classEx"		=> "fiscal_folio_label hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el folio fiscal\"",
													"classEx"		=> "hidden mb-4 removeselect fiscal_folio fiscal_folio_new",
												],
												[
													"kind" 			=> "components.labels.label", 
													"label"			=> "Número de ticket:",
													"classEx" 		=> "ticket_number_label mb-4 hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el número de ticket\"",
													"classEx"		=> "hidden mb-4 removeselect ticket_number ticket_number_new"
												],
												[
													"kind" 			=> "components.labels.label", 
													"label"			=> "Monto total:",
													"classEx"		=> "amount_label hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el monto total\"",
													"classEx"		=> "hidden mb-4 removeselect amount amount_new",
												]
											]
			])->render();
			$docs_upload = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $docs_upload));
		@endphp
		docs_upload = $('{!!preg_replace("/(\r)*(\n)*/", "", $docs_upload)!!}');
		if(noButtons)
		{
			docs_upload.find(".delete-uploaded-file").addClass("hidden");
		}
		$('#documents_'+statusDocument).append(docs_upload);
		$('.amount').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
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
		@php
			$selects = collect([
				[
					"identificator"				=> ".name_document_new",
					"placeholder"				=> "Seleccione el tipo de documento",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
	}

	function refund()
	{	
		totalResource 	= $('.totalResource').val();
		total 	 		= $('.total').val();
		reembolso 		= total-totalResource;
		reintegro 		= totalResource-total;	

		if (reintegro > 0) 
		{
			$('.reintegro').val(Number(reintegro).toFixed(2));
			$('.reintegroLabel').text('$ '+Number(reintegro).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

		}
		else
		{
			$('.reembolso').val(Number(reembolso).toFixed(2));
			$('.reembolsoLabel').text('$ '+Number(reembolso).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		}
	}
</script>
@endsection
