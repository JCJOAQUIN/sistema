@extends('layouts.child_module')
@section('data')
	@php
		$enterpriseSelected = $areaSelected = $departmentSelected = $userSelected = $accountSelected = $projectSelected = '';
		$docs 	= 0;
		$taxes 	= 0;
		$retentionConcept2=0;
	@endphp
	@component("components.forms.form", ["attributeEx" => "action=\"".route('refund.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
		@component('components.inputs.input-text')
			@slot('classEx')
				requestFolio
			@endslot
			@slot('attributeEx')
				type="hidden"
				@if(isset($requests)) value="{{ $requests->folio }}" @endif
			@endslot
		@endcomponent
		@component("components.labels.title-divisor") NUEVA SOLICITUD @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
					@endslot
					@slot("attributeEx") 
						name="title"
						placeholder="Ingrese el título" 
						data-validation="required"
						@if(isset($requests)) value="{{ $requests->refunds->first()->title }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
						datepicker
					@endslot
					@slot("attributeEx") 
						name="datetitle"
						data-validation="required"
						placeholder="Ingrese la fecha" 
						readonly="readonly"
						@if(isset($requests)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $requests->refunds->first()->datetitle)->format('d-m-Y') }}" @endif
					@endslot
				@endcomponent
			</div>
			@component("components.inputs.input-text")
				@slot("attributeEx") 
					type="hidden"
					name="from_new"
					@if(isset($requests)) value="true" @else value="false" @endif
				@endslot
			@endcomponent
			@component("components.inputs.input-text")
				@slot("classEx") 
					main_folio
				@endslot
				@slot("attributeEx") 
					type="hidden"
					name="main_folio"
					@if(isset($requests)) value="{{$requests->folio}}" @endif
				@endslot
			@endcomponent
			<div class="col-span-2">
				@component("components.labels.label") Solicitante: @endcomponent
				@php
					$options = collect();
					if(isset($requests))
					{
						$userSelected = App\User::find($requests->idRequest);
						$options = $options->concat([["value" => $userSelected->id, "selected" => "selected", "description" => $userSelected->fullName()]]);
					}
					$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"";
					$classEx = "js-users removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeEnt($option_id)->pluck("enterprise_id"))->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if(isset($requests) && $requests->idEnterprise == $enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises\" data-validation=\"required\"";
					$classEx = "js-enterprises removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Dirección: @endcomponent
				@php
					$options = collect();
					foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if(isset($requests) && $requests->idArea == $area->id)
						{
							$options = $options->concat([["value" => $area->id, "selected" => "selected", "description" => $area->name]]);
						}
						else 
						{
							$options = $options->concat([["value" => $area->id, "description" => $area->name]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"";
					$classEx = "js-areas removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if(isset($requests) && $requests->idDepartment == $department->id)
						{
							$options = $options->concat([["value" => $department->id, "selected" => "selected", "description" => $department->name]]);
						}
						else 
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\"";
					$classEx = "js-departments removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if(isset($requests))
					{
						$options = $options->concat([["value" => $requests->idProject, "selected" => "selected", "description" => $requests->requestProject->proyectName]]);
					}
					$attributeEx = "name=\"project_id\" data-validation=\"required\" multiple=\"multiple\" id=\"multiple-projects\"";
					$classEx = "js-projects removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
            @php
                $arrayProject = Auth::user()->inChargeProject($option_id)->pluck('project_id')->toArray();
            @endphp
			<div class="col-span-2 code-WBS @if(!isset($requests->code_wbs) || !in_array($requests->idProject,$arrayProject)) hidden @endif ">
				@component("components.labels.label") Código WBS: @endcomponent
				@php
					$options = collect();
					if(isset($requests) && $requests->code_wbs != "")
					{
						$options = $options->concat([["value" => $requests->code_wbs, "selected" => "selected", "description" => $requests->wbs->code_wbs]]);
					}
					$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "js-code_wbs removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2 code-EDT @if(!isset($requests->code_edt) || !in_array($requests->idProject,$arrayProject)) hidden @endif ">
				@component("components.labels.label") Código EDT: @endcomponent
				@php
					$options = collect();
					if(isset($requests) && $requests->code_edt != "")
					{
						$options = $options->concat([["value" => $requests->code_edt, "selected" => "selected", "description" => $requests->edt->code." (".$requests->edt->description.")"]]);
					}
					$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "js-code_edt removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endcomponent
		
		@component("components.labels.title-divisor") FORMA DE PAGO <span class="help-btn" id="help-btn-method-pay"></span> @endcomponent
		@php
			$buttons = 
			[
				[
					"textButton" 		=> "Cuenta Bancaria",
					"attributeButton" 	=> "name=\"method\" value=\"1\" id=\"accountBank\"".(isset($requests) && $requests->refunds->first()->idpaymentMethod == 1 ? " checked" : ""),
				],
				[
					"textButton" 		=> "Efectivo",
					"attributeButton" 	=> "name=\"method\" value=\"2\" id=\"cash\"".(isset($requests) && $requests->refunds->first()->idpaymentMethod == 2 ? " checked" : !isset($requests) ? " checked" : ""),
				],
				[
					"textButton" 		=> "Cheque",
					"attributeButton" 	=> "name=\"method\" value=\"3\" id=\"checks\"".(isset($requests) && $requests->refunds->first()->idpaymentMethod == 3 ? " checked" : ""),
				],								
			];
		@endphp
		@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
		
		@isset($requests)
			@component("components.inputs.input-text")
				@slot("classEx")
					employee_number
				@endslot
				@slot("attributeEx")
					type="hidden"
					name="employee_number"
					id="efolio"
					value="@foreach($requests->refunds as $refund){{ $refund->idUsers }}@endforeach"
				@endslot
			@endcomponent
		@endisset
		
		<div class="resultbank @if(isset($requests) && $requests->refunds->first()->idpaymentMethod == 1) block @else hidden @endif ">
			@if(isset($requests) && $requests->refunds->first()->idpaymentMethod == 1)
				@component("components.labels.title-divisor", ["classExContainer" => "mb-6"]) SELECCIONE UNA CUENTA @endcomponent
				@php
					$modelHead = 
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
					$modelBody = [];
					foreach($requests->refunds as $refund)
					{
						foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('visible',1)->where('employees.idUsers',$refund->idUsers)->get() as $bank)
						{
							if($refund->idEmployee == $bank->idEmployee)
							{
								$checked = "checked=\"checked\"";
								$accountSelect = "accountSelect";
							}
							else
							{
								$checked = "";
								$accountSelect = "";
							}
							
							$body = 
							[
								"classEx" => "tr ".$accountSelect."",
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"classExContainer" 	=> "inline-flex",
											"radio"				=> true,
											"kind"				=> "components.inputs.checkbox",
											"classEx"			=> "checkbox",
											"attributeEx"		=> "id=\"idEmp".$bank->idEmployee."\" ".$checked." name=\"idEmployee\" value=\"".$bank->idEmployee."\"",
											"label"				=> "<span class=\"icon-check\"></span>"
										],
									],
								],
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"kind"    => "components.labels.label",
											"label"   => $bank->description,
										],
									],
								],
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"kind"    => "components.labels.label",
											"label"   => $bank->alias != null ? $bank->alias : '---',
										],
									],
								],
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"kind"  => "components.labels.label",
											"label" => $bank->cardNumber != null ? $bank->cardNumber : '---',
										],
									],
								],
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"kind"  => "components.labels.label",
											"label" => $bank->clabe != null ? $bank->clabe : '---',
										],
									],
								],
								[
									"classEx" => "td",
									"content" =>
									[
										[
											"kind"  => "components.labels.label",
											"label" => $bank->account != null ? $bank->account : '---',
										],
									],
								],						
							];
							$modelBody[] = $body;
						}
					}
				@endphp
				@component("components.tables.table",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
					@slot("attributeEx")
						id="table2"
					@endslot
					@slot("classExBody")
						request-validate
					@endslot
				@endcomponent
			@endif
		</div>
		<div id="banks" class="bankAccount my-6 @if(isset($requests) && $requests->refunds->first()->idpaymentMethod == 1) block @else hidden @endif ">
			@component("components.containers.container-form", ["classEx" => "container-accounts"])
				<div class="col-span-2">
					@component("components.labels.label") Banco: @endcomponent
					@component("components.inputs.select", ["attributeEx" => "name=\"bank\" multiple=\"multiple\"", "classEx" => "bank"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Alias: @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese el alias\"", "classEx" => "alias"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") *Número de tarjeta: @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese el número de tarjeta\" data-validation=\"tarjeta\"", "classEx" => "card-number",]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") *CLABE: @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese la CLABE\" data-validation=\"clabe\"", "classEx" => "clabe"]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") *Cuenta bancaria: @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese la cuenta bancaria\" data-validation=\"cuenta\"", "classEx" => "account"]) @endcomponent
				</div>
				<div class="col-span-2 md:col-span-4">
					*Para agregar una cuenta nueva es necesario colocar al menos uno de los campos.
				</div>
				<div class="col-span-2 md:col-span-4">
					@component("components.buttons.button",
					[
						"attributeEx" => "type=\"button\" name=\"addAccount\" id=\"addAccount\"",
						"variant" => "warning",
						"classEx" => "addAccount"
					])
					<span class="icon-plus"></span>
					<span>Agregar</span>
					@endcomponent
				</div>
			@endcomponent
		</div>
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Referencia (Opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
					@endslot
					@slot("attributeEx")
						name="reference"
						placeholder="Ingrese la referencia"
						@if(isset($requests) && $requests->refunds->first()->reference != "") value="{{ $requests->refunds->first()->reference }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de moneda: @endcomponent
				@php
					$options = collect();
					$currencies = ["MXN","USD","EUR","Otro"];
					foreach ($currencies as $currency) 
					{
						if(isset($requests) && $requests->refunds->first()->currency == $currency)
						{
							$options = $options->concat([["value" => $currency, "selected" => "selected", "description" => $currency]]);
						}
						else 
						{
							$options = $options->concat([["value" => $currency, "description" => $currency]]);
						}
					}
					$attributeEx = "name=\"currency\" multiple=\"multiple\" data-validation=\"required\"";
					$classEx = "removeselect currency";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") RELACIÓN DE DOCUMENTOS <span class="help-btn" id="help-btn-documents"></span> @endcomponent
		@component("components.containers.container-form", ["classEx" => "concept-container"])
			<div class="col-span-2">
				@component("components.labels.label") Concepto: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") 
						input-all
					@endslot
					@slot("attributeEx") 
						type="text"
						name="concept"
						placeholder="Ingrese el concepto"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Clasificación del gasto: @endcomponent
				@component ("components.inputs.select", ["attributeEx" => "multiple=\"multiple\" name=\"account_id\"", "classEx" => "js-accounts removeselect"]) @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="documents"></div>
			<div class="col-span-2 md:col-span-4">
				@component("components.buttons.button", ["variant"=>"warning"])
					@slot("attributeEx")
						type="button"
						name="addDoc"
						id="addDoc"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar documento</span>
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fiscal: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("classEx")
							fiscal
						@endslot
						@slot("attributeEx")
							name="fiscal"
							value="no"
							id="nofiscal"
							checked
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("classEx")
							fiscal
						@endslot
						@slot("attributeEx")
							name="fiscal"
							value="si"
							id="fiscal"
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Subtotal: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") 
						input-extrasmall2
						subamount
					@endslot
					@slot("attributeEx") 
						name="subamount"
						placeholder="Ingrese el subtotal"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") IVA: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("classEx")
							iva
						@endslot
						@slot("attributeEx")
							disabled
							name="iva"
							id="noiva"
							value="no" 
							checked
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("classEx")
							iva
						@endslot
						@slot("attributeEx")
							disabled
							name="iva"
							id="siiva"
							value="si" 
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de IVA: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("classEx")
							iva_kind
						@endslot
						@slot("attributeEx")
							disabled
							name="iva_kind"
							class="iva_kind"
							id="iva_a"
							value="a"
							checked
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						@slot("attributeExLabel")
							title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
						@endslot
						Tipo A
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("classEx")
							iva_kind
						@endslot
						@slot("attributeEx")
							disabled
							name="iva_kind"
							class="iva_kind"
							id="iva_b"
							value="b"
						@endslot
						@slot("classExLabel")
							bg-white
						@endslot
						@slot("attributeExLabel")
							title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
						@endslot
						Tipo B
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 md:col-span-4">
				@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional'])  @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention'])  @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Importe: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") 
						input-extrasmall2
						amount
					@endslot
					@slot("attributeEx") 
						name="amount"
						placeholder="Ingrese el importe"
						readonly
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant"=>"warning"])
					@slot("attributeEx")
						type="button"
						name="add"
						id="add"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar concepto</span>
				@endcomponent
			</div>
		@endcomponent
		@php
			$modelHead = [
				[
					["value" => "#"],
					["value" => "Concepto"],
					["value" => "Clasificación del gasto"],
					["value" => "Fiscal"],
					["value" => "Subtotal"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Retenciones"],
					["value" => "Importe"],
					["value" => "Documento(s)"],
					["value" => "Acción"]
				]
			];

			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			$c=0;
			$modelBody = [];
			if(isset($requests))
			{
				foreach($requests->refunds->first()->refundDetail as $key=>$refundDetail)
				{	
					isset($empty) ? $valueT_1 = "value=\"".$empty."\"" : $valueT_1 = "";	
					$subtotalFinal	+= $refundDetail->amount;
					$ivaFinal		+= $refundDetail->tax;
					$totalFinal		+= $refundDetail->sAmount;
					$body = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "countConcept",
									"label" => $key+1,
								],
								[
									"kind" => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"t_1\" ".$valueT_1,
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "idRefundDetail idRDe",
									"attributeEx" => "type=\"hidden\" name=\"idRDe[]\" value=\"$refundDetail->idRefundDetail\"",
								]
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label" => htmlentities($refundDetail->concept),
									"classEx" => "concept",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "t_concept",
									"attributeEx" => "type=\"hidden\" name=\"t_concept[]\" value=\"".htmlentities($refundDetail->concept)."\"",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    	=> "components.labels.label",
									"classEx"	=> "accountText",
									"label" 	=> $refundDetail->account->account." - ".$refundDetail->account->description." (".$refundDetail->account->content.")",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "t_account",
									"attributeEx" => "type=\"hidden\" name=\"t_account[]\" value=\"$refundDetail->idAccount\"",
								],
							],
						],
					];

					$refundDetail->taxPayment == 1 ? $taxPayment ="si" : $taxPayment ="no";
					$body[] =
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => $taxPayment,
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t_fiscal",
								"attributeEx" => "type=\"hidden\" name=\"t_fiscal[]\"",
							],
						],
					]; 

					$body[] = 
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"    => "components.labels.label",
								"label" => "$ ".number_format($refundDetail->amount,2),
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t-amount t_amount",
								"attributeEx" => "type=\"hidden\" name=\"t_amount[]\" value=\"$refundDetail->amount\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "amounttemp",
								"attributeEx" => "type=\"hidden\" value=\"$refundDetail->amount\"",
							],
						],
					];
					
					$refundDetail->tax > 0 ? $tax = "si" : $tax = "no";

					$body[] = 
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t_iva",
								"attributeEx" => "type=\"hidden\" name=\"t_iva[]\" value=\"".$tax."\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t-iva-kind",
								"attributeEx" => "type=\"hidden\" name=\"t_iva_kind[]\" value=\"done\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t_iva_kind",
								"attributeEx" => "readonly type=\"hidden\" name=\"tivakind[]\" value=\"".$refundDetail->typeTax."\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t_iva_kind",
								"attributeEx" => "readonly type=\"hidden\" name=\"tivakind[]\" value=\"".$refundDetail->typeTax."\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t_iva_kind",
								"attributeEx" => "readonly type=\"hidden\" name=\"tivakind[]\" value=\"".$refundDetail->typeTax."\"",
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t-iva",
								"attributeEx" => "type=\"hidden\" name=\"t_ivatotal[]\" value=\"".$refundDetail->tax."\"",
							],
							[
								"kind"  => "components.labels.label",
								"label" => "$ ".number_format($refundDetail->tax,2),
							]
						],
					]; 

					$taxes2 = 0;
					$taxesTd = [];
					foreach($refundDetail->taxes as $tax)
					{
						$taxes2 += $tax->amount;

						$taxesTd[] = 
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "num_amountAdditional",
							"attributeEx" => "type=\"hidden\" name=\"t_amount_tax".$docs."[]\" value=\"$tax->amount\"",
						];

						$taxesTd[] = 
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "num_nameAmount",
							"attributeEx" => "type=\"hidden\" name=\"t_name_tax".$docs."[]\" value=\"$tax->name\"",
						];
					}

					$taxesTd[] = 
					[
						"kind"  => "components.labels.label",
						"label" => "$ ".number_format($taxes2,2),
					];

					$body[] =
					[
						"classEx" => "td",
						"content" => $taxesTd,
					];

					$retentionConcept=0;
					$retentions = [];
					foreach($refundDetail->retentions as $ret)
					{
						$retentions[] = 
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "num_amountRetention",
							"attributeEx" => "type=\"hidden\" name=\"t_amount_retention".$docs."[]\" value=\"$ret->amount\"",
						];
						$retentions[] = 
						[
							"kind" => "components.inputs.input-text",
							"classEx" => "num_nameRetention",
							"attributeEx" => "type=\"hidden\" name=\"t_name_retention".$docs."[]\" value=\"$ret->name\"",
						];
						$retentionConcept+=$ret->amount;
					}

					$retentions[] = 
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($retentionConcept,2),
					];
					
					$body[] =
					[
						"classEx" => "td",
						"content" => $retentions,
					];
					
					$body[] = 
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"  => "components.labels.label",
								"label" => "$ ".number_format($refundDetail->sAmount,2),
							],
							[
								"kind" => "components.inputs.input-text",
								"classEx" => "t-iva",
								"attributeEx" => "type=\"hidden\" name=\"t_total[]\"",
							],
						],
					];
					
					$contentBodyDocs 	= [];
					$nowrap 			= '';
					foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
					{
						$nowrap 	.= '<div class="nowrap">';

						$date 		 = $doc->datepath != "" ? Carbon\Carbon::createFromFormat('Y-m-d', $doc->datepath)->format('d-m-Y') : Carbon\Carbon::createFromFormat('Y-m-d', $doc->date)->format('d-m-Y');
						$timepath 	 = $doc->timepath != "" ? Carbon\Carbon::createFromFormat('H:i:s', $doc->timepath)->format('H:i') : $doc->timepath;
						$docDatepath = $doc->datepath != "" ? Carbon\Carbon::createFromFormat('Y-m-d', $doc->datepath)->format('d-m-Y') : null;

						$nowrap .= view('components.labels.label',[																
							"label" => $date,
						])->render();

						$nowrap .= view("components.buttons.button",[
							"buttonElement" => "a",
							"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset("docs/refounds/".$doc->path)."\"",
							"variant" => "dark-red",
							"label" => "<span class='fas fa-file-alt'></span>",
						])->render();

						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_path",
							"attributeEx" => "type=\"hidden\" name=\"t_path".$docs."[]\" value=\"".$doc->path."\"",
						])->render();

						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_new",
							"attributeEx" => "type=\"hidden\" name=\"t_new".$docs."[]\" value=\"0\"",
						])->render();
						
						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_datepath",
							"attributeEx" => "type=\"hidden\" name=\"t_datepath".$docs."[]\" value=\"".$docDatepath."\"",
						])->render();
						
						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_fiscal_folio",
							"attributeEx" => "type=\"hidden\" name=\"t_fiscal_folio".$docs."[]\" value=\"".htmlentities($doc->fiscal_folio)."\"",
						])->render();

						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_ticket_number",
							"attributeEx" => "type=\"hidden\" name=\"t_ticket_number".$docs."[]\" value=\"".htmlentities($doc->ticket_number)."\"",
						])->render();

						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_amount",
							"attributeEx" => "type=\"hidden\" name=\"t_amount".$docs."[]\" value=\"".$doc->amount."\"",
						])->render();
						
						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_timepath",
							"attributeEx" => "type=\"hidden\" name=\"t_timepath".$docs."[]\" value=\"".$timepath."\"",
						])->render();

						$nowrap .= view("components.inputs.input-text",[
							"classEx" => "num_name_doc",
							"attributeEx" => "type=\"hidden\" name=\"t_name_doc".$docs."[]\" value=\"".$doc->name."\"",
						])->render();

						$nowrap .= "</div>";
					}
					$body[] =
					[
						"classEx" 	=> "td",
						"content"   =>  ["label" => $nowrap],
					];
					
					$body[] = 
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "edit-item",
								"attributeEx" => "id=\"edit\" type=\"button\"",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>",
							],
							[
								"kind" => "components.buttons.button", 
								"classEx" => "delete-item",
								"attributeEx" => "id=\"cancel\" type=\"button\"",
								"variant" => "red",
								"label" => "<span class=\"icon-x\"></span>",
							],
						],
					];
					$docs++;
					$c=$c+1;
					$modelBody [] = $body;
				}
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot("attributeEx")
				id="table"
			@endslot
			@slot("classExBody")
				request-validate
			@endslot
			@slot("attributeExBody")
				id="body"
			@endslot
		@endcomponent

		@component("components.inputs.input-text")
			@slot("attributeEx") 
				type="hidden"
				name="flag2"
				@isset($b) value="{{$b}}" @endisset
			@endslot
		@endcomponent

		@php
			$modelTable = [];
			
			if($totalFinal!=0)
			{
				$valueSubtotal = "value=\"".number_format($subtotalFinal,2)."\"";
				$labelSubtotal = "$ ".number_format($subtotalFinal,2);
				$valueIVA 	   = "value=\"".number_format($ivaFinal,2)."\"";
				$labelIVA 	   = "$ ".number_format($ivaFinal,2);
				$valueTotal    = "value=\"".number_format($totalFinal,2)."\"";
				$labelTotal    = "$ ".number_format($totalFinal,2);
			}
			else 
			{
				$valueSubtotal = "";
				$labelSubtotal = "$ 0.00";
				$valueIVA 	   = "";
				$labelIVA 	   = "$ 0.00";
				$valueTotal    = "";
				$labelTotal    = "$ 0.00";
			}
			if(isset($requests))
			{
				foreach($requests->refunds->first()->refundDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
					foreach($detail->retentions as $ret)
					{
						$retentionConcept2 = $retentionConcept2 + $ret->amount;
					}
				}
			}
			
			$modelTable = 
			[	
				["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => $labelSubtotal, "classEx" => "my-2 label-subtotal"],["kind" =>	"components.inputs.input-text",	"classEx" => "subtotal", "attributeEx" => "type=\"hidden\" id=\"subtotal\" readonly name=\"subtotal\" ".$valueSubtotal]]],
				["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelIVA, "classEx" => "my-2 label-IVA"],["kind" =>	"components.inputs.input-text",	"classEx" => "ivaTotal", "attributeEx" => "type=\"hidden\" id=\"iva\" name=\"iva\" ".$valueIVA]]],
				["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($taxes,2), "classEx" => "my-2 label-taxes"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountAA\" value=\"".number_format($taxes,2)."\""]]],
				["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($retentionConcept2,2), "classEx" => "my-2 label-retentions"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountRetentions\" value=\"".number_format($retentionConcept2,2)."\""]]],
				["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelTotal, "classEx" => "my-2 label-total"],["kind" =>	"components.inputs.input-text",	"classEx" => "total", "attributeEx" => "type=\"hidden\" id=\"total\" name=\"total\" ".$valueTotal]]],
			];
		@endphp
		@component("components.templates.outputs.form-details", ["modelTable" => $modelTable]) @endcomponent
		<div id="invisible"></div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-10 mb-6">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="send"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button", ["variant"=>"secondary"])
				@slot("classEx")
					save
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="button"
					name="save"
					id="save"
					formaction={{ route("refund.unsent") }}
				@endslot
				GUARDAR SIN ENVIAR
			@endcomponent
			@component("components.buttons.button", ["variant"=>"reset"])
				@slot("classEx")
					btn-delete-form
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="reset"
					name="borra"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"         => ".js-enterprises", 
						"placeholder"           => "Seleccione la empresa",
						"maximumSelectionLength"=> "1",
					],
					[
						"identificator"         => ".js-areas", 
						"placeholder"           => "Seleccione la dirección", 
						"maximumSelectionLength"=> "1",

					],
					[
						"identificator"         => ".js-departments", 
						"placeholder"           => "Seleccione el departamento",
						"maximumSelectionLength"=> "1"
					],
					[
						"identificator"         => "[name=\"currency\"]", 
						"placeholder"           => "Seleccione el tipo de moneda",
						"maximumSelectionLength"=> "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
			
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					return false;
				},
				onSuccess : function($form)
				{
					concept			= $('input[name="concept"]').val().trim();
					date			= $('input[name="datepath"]').length;
					account			= $('.js-accounts').val();
					path			= $('input[name="realPath"]').length;
					amount			= $('input[name="amount"]').val().trim();
					if (concept != "" || date > 0 || account != "" || path > 0) 
					{
						swal('', 'Hay conceptos para agregar, por favor verifique los campos.', 'error');
						return false;
					}
					if($('.request-validate').length > 0)
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
									swal({
										title              : 'Cargando',
										icon               : '{{ asset(getenv("LOADING_IMG")) }}',
										button             : false,
										closeOnClickOutside: false,
										text               : 'Estamos validando los datos',
										closeOnEsc         : false
									});
									return true;									
										
								}
								else
								{
									swal('', 'Por favor seleccione una cuenta.', 'error');
									return false;
								}
							}
							else
							{
								swal({
									title              : 'Cargando',
									icon               : '{{ asset(getenv("LOADING_IMG")) }}',
									button             : false,
									closeOnClickOutside: false,
									text               : 'Estamos validando los datos',
									closeOnEsc         : false
								});
								return true;
							}
						}
						else if (method == undefined) 
						{
							swal('', 'Por favor seleccione un método de pago.', 'error');
							return false;
						}
						else
						{
							swal('', 'Por favor agregue al menos un concepto a la tabla.', 'error');
							return false;
						}
					}
					else
					{
						swal({
							title              : 'Cargando',
							icon               : '{{ asset(getenv("LOADING_IMG")) }}',
							button             : false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						return true;
					}
				}
			});
			array_folios			= $('.folio_fiscal').serializeArray();
			array_ticket			= $('.num_ticket').serializeArray();	
			array_concept_folio		= $('.validation_fiscal_folio').serializeArray();
			array_concept_ticket	= $('.validation_ticket').serializeArray();	
			$('[name="amount"],[name="additionalAmount"],[name="retentionAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
			$('.timepath').daterangepicker({
				timePicker : true,
				singleDatePicker:true,
				timePicker24Hour : true,
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
			$(function() 
			{
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
				$(".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
				$(".datepicker2" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
			});
			@component('components.scripts.taxes',['type'=>'taxes', 'name' => 'additional','function'=>'amountConcept'])  @endcomponent
			@component('components.scripts.taxes',['type'=>'retention', 'name' => 'retention','function'=>'amountConcept'])  @endcomponent
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
			generalSelect({'selector': '.js-projects', 'model': 41, 'option_id':{{$option_id}} });
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
			generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '.bank', 'model': 27});
			$('.amount,.additionalAmount,.subamount,.retentionAmount,.monto,.descuento').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('.js-enterprises').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Eliminar Empresa",
					text		: "Si elimina la empresa, todos los conceptos que ya se encontraban agregados serán eliminados.\n¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$(this).val(null).trigger('change');
						$(".subtotal").val(null);
						$(".ivaTotal").val(null);
						$("[name='amountAA']").val(null);
						$("[name='amountRetentions']").val(null);
						$(".total").val(null);
						$('input[name="amountAA"]').val(null);
						total_cal();
						section = "all";
						deleteDocs(section);
						$('#body').empty();
					}
					else
					{
						swal.close();
					}
				});
			});
			doc = {{ $docs }};
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
			.on('change','.js-projects',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null && id != undefined && id != "")
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.code-WBS').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
							}
							else
							{
								$('.js-code_wbs, .js-code_edt').html('');
								$('.code-WBS, .code-EDT').removeClass('block').addClass('hidden');
							}					
						}
					});
				} 
				else
				{
					$('.js-code_wbs, .js-code_edt').html('');
					$('.code-WBS, .code-EDT').removeClass('block').addClass('hidden');	
				}
			})
			.on('change','.js-code_wbs',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null && id != undefined && id != "")
				{
					$.each(generalSelectWBS,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagEDT != null)
							{
								$('.code-EDT').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
							}
							else
							{
								$('.js-code_edt').html('');
								$('.code-EDT').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_edt').html('');
					$('.code-EDT').removeClass('block').addClass('hidden');
				}
			})
			.on('click','#addDoc',function()
			{
				@php
						$options = collect();
						$docsKind = ["Ticket","Factura","Otro"];

						foreach ($docsKind as $kind)
						{
							$options = $options->concat([["value" => $kind, "description" => $kind]]);	
						}
						$newDoc = view('components.documents.upload-files',[					
							"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExInput" => "pathActioner",
							"attributeExRealPath" => "name=\"realPath\"",
							"classExRealPath" => "path",
							"componentsExUp" => 
							[
								["kind" => "components.labels.label", "label" => "Tipo de documento:"],
								["kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"name_document[]\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "name_document mb-6"]
							],
							"componentsExDown" =>
							[
								["kind" => "components.labels.label", "label" => "Fecha:", "classEx" => "mt-4"],
								["kind" => "components.inputs.input-text", "attributeEx" => "name=\"datepath[]\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\"", "classEx" => "datepicker datepath my-2"],
								["kind" => "components.labels.label", "label" => "Hora:", "classEx" => "timepath hidden"],
								["kind" => "components.inputs.input-text", "attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\"", "classEx" => "timepath hidden my-2"],
								["kind" => "components.labels.label", "label" => "Folio Fiscal:", "classEx" => "folio_fiscal hidden"],
								["kind" => "components.inputs.input-text", "attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\"", "classEx" => "folio_fiscal hidden my-2"],
								["kind" => "components.labels.label", "label" => "Número de Ticket:", "classEx" => "ticket_number hidden"],
								["kind" => "components.inputs.input-text", "attributeEx" => "name=\"num_ticket[]\" placeholder=\"Ingrese el número de ticket\"", "classEx" => "ticket_number hidden my-2"],
								["kind" => "components.labels.label", "label" => "Monto:", "classEx" => "amount_ticket hidden"],
								["kind" => "components.inputs.input-text", "attributeEx" => "name=\"monto[]\" placeholder=\"Ingrese el monto total\"", "classEx" => "amount_ticket hidden my-2"],
							],
				 			"classExDelete" => "delete-doc",
				 		])->render();
				@endphp
				newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				containerNewDoc = $(newDoc);
				$("#documents").append(containerNewDoc);
				$('.amount_ticket').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
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

				$('.name_document').select2(
				{
					language				: "es",
					maximumSelectionLength	: 1,
					placeholder 			: "Seleccione el tipo de documento",
					width 					: "100%",
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
			})
			.on('change','.timepath',function()
			{
				$(this).daterangepicker({	
					timePicker : true,		 
					singleDatePicker:true,   
					timePicker24Hour : true, 
					autoApply: true,
					locale : {
						format : 'HH:mm',
						"applyLabel": "Seleccionar",
						"cancelLabel": "Cancelar",
					}
				}).on('show.daterangepicker', function (ev, picker){
					picker.container.find(".calendar-table").remove();
				});
			})
			.on('change','.name_document',function()
			{
				$(this).parent().parent().find(".form-error").remove();
				kindDocument = $('option:selected',this).val();
				switch(kindDocument)
				{
					case 'Factura': 
						$(this).parents('.docs-p').find('.folio_fiscal').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.ticket_number').hide().val('');
						$(this).parents('.docs-p').find('.amount_ticket').hide().val('');
						$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
					case 'Ticket': 
						$(this).parents('.docs-p').find('.folio_fiscal').hide().val('');
						$(this).parents('.docs-p').find('.ticket_number').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.amount_ticket').show().removeClass('error').val('');
						$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
					default :  
						$(this).parents('.docs-p').find('.folio_fiscal').hide().val('');
						$(this).parents('.docs-p').find('.ticket_number').hide().val('');
						$(this).parents('.docs-p').find('.amount_ticket').hide().val('');
						$(this).parents('.docs-p').find('.timepath').hide().val('');
						$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
						break;
				}
				if( kindDocument == null)
				{
					$(this).parent().parent().append("<span class='form-error'>Este campo es obligatorio</span>");
				}
			})
			.on('change','.folio_fiscal,.ticket_number,.timepath,.amount_ticket,.datepath',function()
			{
				$(this).removeClass('error');
				object 		= $(this);
				flag 		= false;
				duplicate	= '';
				$('.docs-p').each(function(i,v)
				{	
					firstFiscalFolio	= $(this).find('[name="folio_fiscal[]"]').val();
					firstTicketNumber	= $(this).find('[name="num_ticket[]"]').val();
					firstAmount			= $(this).find('[name="monto[]"]').val();
					firstTimepath		= $(this).find('[name="timepath[]"]').val();
					firstDatepath		= $(this).find('[name="datepath[]"]').val();
					firstNameDoc		= $(this).find('[name="name_document[]"] option:selected').val();

			
					
					$('.docs-p').each(function(j,v)
					{
						if(i!==j)
						{
							scndFiscalFolio		= $(this).find('[name="folio_fiscal[]"]').val();
							scndTicketNumber	= $(this).find('[name="num_ticket[]"]').val();
							scndAmount			= $(this).find('[name="monto[]"]').val();
							scndTimepath		= $(this).find('[name="timepath[]"]').val();
							scndDatepath		= $(this).find('[name="datepath[]"]').val();
							scndNameDoc			= $(this).find('[name="name_document[]"] option:selected').val();
							
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
					$('#body').find('.nowrap').each(function()
					{
						name = $(this).find('.num_name_doc').val();
						if (name == "Factura" && firstNameDoc == 'Factura') 
						{
							folio		= $(this).find('.num_fiscal_folio').val();
							datepath	= $(this).find('.num_datepath').val();
							timepath	= $(this).find('.num_timepath').val();
							if (datepath == firstDatepath && timepath == firstTimepath && folio.toUpperCase() == firstFiscalFolio.toUpperCase()) 
							{
								duplicate 	= 'folio fiscal "'+folio+'"';
								flag 		= true;
							}
						}
						if (name == "Ticket" && firstNameDoc == 'Ticket') 
						{							
							ticket		= $(this).find('.num_ticket_number').val();
							datepath	= $(this).find('.num_datepath').val();
							timepath	= $(this).find('.num_timepath').val();
							amount 		= $(this).find('.num_amount').val();
							if (datepath == firstDatepath && timepath == firstTimepath && ticket.toUpperCase() == firstTicketNumber.toUpperCase() && Number(amount).toFixed(2) == Number(firstAmount).toFixed(2))
							{
								duplicate 	= 'número de ticket "'+ticket+'"';
								flag 		= true;
							}
						}
					});
				});
				if(flag)
				{
					swal('', 'El documento con '+duplicate+' ya se encuentra registrado, por favor verifique los datos.', 'error');
					object.parent().find('.ticket_number').addClass('error').val('');
					object.parent().find('.amount_ticket').addClass('error').val('');
					object.parent().find('.timepath').addClass('error').val('');
					object.parent().find('.datepath').addClass('error').val('');
					object.parent().find('.folio_fiscal').addClass('error').val('');
					return false;
				}
			})
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});
				
				section 		= "concept";
				actioner		= $(this);
				deleteDocs(section, actioner);
			})
			.on('click','#addAccount',function(e)
			{
				$(this).attr('disabled', 'disabled');
				setInterval(() =>
				{
					$('#addAccount').removeAttr('disabled', 'disabled');
				}, 2000);
				$('.error-js-users').remove();
				$('.alias').removeClass('error');
				$('.error-bank').remove();
				alias		= $(this).parents('.container-accounts').find('.alias').val();
				card		= $(this).parents('.container-accounts').find('.card-number').val();
				clabe		= $(this).parents('.container-accounts').find('.clabe').val();
				account		= $(this).parents('.container-accounts').find('.account').val();
				bankid		= $(this).parents('.container-accounts').find('.bank :selected').val();
				userid		= $('select[name="user_id"] :selected').val();
				if (userid != null)
				{
					if (bankid == null)
					{
						swal('', 'Por favor seleccione un banco.', 'error');
						$('.bank').parent().append('<span class="help-block form-error error-bank">Seleccione una opción</span>');
					}
					else if (alias == "")
					{
						$('.alias').removeClass('valid').addClass('error');
						swal('', 'Por favor ingrese un alias.', 'error');
					}
					else if (card == "" && clabe == "" && account == "")
					{
						$('.card-number, .clabe, .account').removeClass('valid').addClass('error');
						swal('', 'Por favor ingrese al menos un número de tarjeta, CLABE o cuenta bancaria.', 'error');
					}
					else if($(this).parents('.container-accounts').find('.card-number').hasClass('error') || $(this).parents('.container-accounts').find('.clabe').hasClass('error') || $(this).parents('.container-accounts').find('.account').hasClass('error'))
					{
						if(card == '')
						{
							$(this).parents('.container-accounts').find('.card-number').removeClass('error');
						}
						if (clabe == '')
						{
							$(this).parents('.container-accounts').find('.clabe').removeClass('error');
						}
						if (account == '')
						{
							$(this).parents('.container-accounts').find('.account').removeClass('error');
						}
						swal('', 'Por favor ingrese datos correctos.', 'error');
					}
					else
					{
						$.ajax(
						{
							type 	: 'post',
							url 	: '{{ route("refund.add-bankAccount") }}',
							data 	: 
							{
								'bankid'  : bankid,
								'alias'   : alias,
								'card'	  : card,
								'clabe'	  : clabe,
								'account' : account,
								'userid'  : userid
							},
							success : function(data)
							{
								$.ajax(
								{
									type : 'post',
									url  : '{{ route("refund.search.bank") }}',
									data : 
									{
										'idUsers':userid
									},
									success:function(e)
									{
										$('.resultbank').html(e);
									},
									error: function(e)
									{
										$('.resultbank').html('');
										swal('','Sucedió un error, por favor intente de nuevo.','error');
									}
								});
								if (data > 0)
								{
									swal('','Los datos ya se encuentran registrados, por favor verifique su información.','error');
									$('.alias').val(alias);
									$('.card-number').val(card);
									$('.clabe').val(clabe);
									$('.account').val(account);
									$('.bank').val(bankid).trigger("change");
								}
							},
							error: function(data)
							{
								$('.alias').val(alias);
								$('.card-number').val(card);
								$('.clabe').val(clabe);
								$('.account').val(account);
								$('.bank').val(bankid).trigger("change");
								$('.addAccount').prop("disabled",false);
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						});
						$('.card-number, .clabe, .account, .alias').removeClass('valid').val('');
						$('.bank').val(null).trigger("change");
						$('.addAccount').prop("disabled",false);
					}
				}
				else
				{
					swal('','Por favor seleccione un solicitante.','error');
					$('select[name="user_id"]').parent().append('<span class="help-block form-error error-js-users">Seleccione una opción</span>');
				}
			})
			.on('click','#add',function(e)
			{
				$(this).attr('disabled', 'disabled');
				e.preventDefault();
				countConcept	= $(".countConcept").length;
				amountAAtotal	= 0;
				retentionValue	= 0;
				concept			= $('input[name="concept"]').val().trim();
				account			= $(".js-accounts option:selected").val();
				accountText		= $(".js-accounts option:selected").text();
				fiscal			= $("input[name='fiscal']:checked").val();
				iva				= $("input[name='iva']:checked").val();
				ivaKind			= $("input[name='iva_kind']:checked").val();
				path			= $("input[name='realPath']").length;
				amount			= $("input[name='amount']").val().trim();
				subamount		= $("input[name='subamount']").val().trim();
				ivaParam		= ({{ App\Parameter::where("parameter_name","IVA")->first()->parameter_value }})/100;
				ivaParam2		= ({{ App\Parameter::where("parameter_name","IVA2")->first()->parameter_value }})/100;
				ivaCal			= iva=="si" ? (ivaKind =="a" ? Number(Number(subamount) * Number(ivaParam)).toFixed(2) : Number(Number(subamount) * Number(ivaParam2)).toFixed(2)) : 0;
				additionalCheck = $('[name="additional"]:checked').val();
				retentionCheck	= $('[name="retention"]:checked').val();

				$('.concept-container .error').removeClass("error");
				$(".js-accounts").parent().find(".form-error").remove();
				$("#documents").find(".form-error").remove();
				$(".docs-p").find('.error').removeClass('error');

				flagAdd 		= true;
				message 		= "Por favor ingrese los campos marcados";
				messageExtra 	= "";
				if (concept == "" && account == null && subamount == "" && amount == "")
				{
					$("[name='subamount'], [name='amount'], [name='concept']").addClass("error");
					$(".js-accounts").parent().append("<span class='form-error'>Este campo es obligatorio</span>");
					flagAdd = false;
				}
				else if (concept == "" || account == null || subamount == "" || Number(subamount) <= 0 || amount == "" || Number(amount) <= 0)
				{	
					if(concept == "")
					{
						$("input[name='concept']").addClass("error");
					}
					if(account == null)
					{
						$(".js-accounts").parent().append("<span class='form-error'>Este campo es obligatorio</span>");
					}
					if (subamount == "" || Number(subamount) <= 0)
					{
						$("input[name='subamount']").addClass("error");
						message = "Por favor, verifique que el subtotal e importe sean mayores a cero";
					}
					if (amount == "" || Number(amount) <= 0)
					{
						$("input[name='amount']").addClass("error");
						message = "Por favor, verifique que el subtotal e importe sean mayores a cero";
					}
					flagAdd = false;
				}
				else if (path == 0)
				{
					message = "Por favor agregue al menos un documento";
					messageExtra = "";
					flagAdd = false;
				}
				else if (path != 0)
				{
					$(".docs-p").each(function(i,v)
					{
						t_fiscalC    = $(this).find("[name='folio_fiscal[]']");
						t_numticketC = $(this).find("[name='num_ticket[]']");
						t_montoC     = $(this).find("[name='monto[]']");
						t_timeC      = $(this).find("[name='timepath[]']");
						t_dateC      = $(this).find("[name='datepath[]']");
						t_name_doc 	 = $(this).find("[name='name_document[]'] option:selected").val();
						t_path		 = $(this).find("[name='realPath']").val();
						if(t_path == '')
						{
							message = "Por favor agregue los documentos faltantes";
							flagAdd = false;
						}
						if (t_name_doc == null)
						{
							$(this).find(".name_document").parent().parent().append("<span class='form-error'>Este campo es obligatorio</span>");
							if(t_path == '')
							{
								messageExtra = " y los campos marcados";
							}
							flagAdd = false;
						}
						if(t_dateC.val() == "")
						{
							t_dateC.addClass("error");
							if(t_path == '')
							{
								messageExtra = " y los campos marcados";
							}
							flagAdd = false;
						}
						if (t_name_doc == "Factura") 
						{
							if(t_fiscalC.val() == "" || t_timeC.val() == "" || t_dateC.val() == "")
							{
								if(t_fiscalC.val() == "")
								{
									t_fiscalC.addClass("error");
								}
								if(t_timeC.val() == "")
								{
									t_timeC.addClass("error");
								}
								if(t_dateC.val() == "")
								{
									t_dateC.addClass("error");
								}
								if(t_path == '')
								{
									messageExtra = " y los campos marcados";
								}
								flagAdd = false;
							}
						}
						if (t_name_doc == "Ticket") 
						{	
							if(t_numticketC.val() == "" || t_timeC.val() == "" || t_dateC.val() == "" || t_montoC.val() == "")
							{
								if(t_numticketC.val() == "")
								{
									t_numticketC.addClass("error");
								}
								if(t_timeC.val() == "")
								{
									t_timeC.addClass("error");
								}
								if(t_dateC.val() == "")
								{
									t_dateC.addClass("error");
								}
								if(t_montoC.val() == "")
								{
									t_montoC.addClass("error");
								}
								if(t_path == '')
								{
									messageExtra = " y los campos marcados";
								}
								flagAdd = false;
							}
						}
					});

					if (additionalCheck == "si")
					{
						nameAmounts = $('<div hidden></div>');
						amountsAA 	= $('<div hidden></div>');
						$(".additionalName").each(function()
						{
							if($(this).val() == '')
							{
								$(this).addClass('error');
								flagAdd = false;								
							}
							else
							{
								nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="t_name_tax'+doc+'[]">').val($(this).val()));
							}
						});
						$(".additionalAmount").each(function()
						{
							if($(this).val() == '' || $(this).val() == 0)
							{
								$(this).addClass('error');
								flagAdd = false;
							}
							else
							{
								amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="t_amount_tax'+doc+'[]">').val($(this).val()));
								amountAAtotal = Number(amountAAtotal)+ Number($(this).val());
							}
						});
					}
					else
					{
						nameAmounts = null;
						amountsAA 	= null;
					}

					if (retentionCheck == "si")
					{
						nameRetentions = $('<div hidden></div>');
						retentions = $('<div hidden></div>');
						$(".retentionName").each(function()
						{
							if($(this).val() == '')
							{
								$(this).addClass('error');
								flagAdd = false;
							}
							else
							{
								nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="t_name_retention'+doc+'[]">').val($(this).val()));
							}
						});
						$(".retentionAmount").each(function()
						{
							if($(this).val() == '' || $(this).val() == 0)
							{
								$(this).addClass('error');
								flagAdd = false;
							}
							else
							{
								retentions.append($('<input type="hidden" class="num_amountRetention" name="t_amount_retention'+doc+'[]">').val($(this).val()));
								retentionValue = Number(retentionValue)+ Number($(this).val());
							}
						});
					}
					else
					{
						nameRetentions 	= null;
						retentions 		= null;
					}
				}
				if (flagAdd)
				{
					fiscal_folio	= [];
					ticket_number	= [];
					timepath		= [];
					amount_ticket	= [];
					datepath		= [];

					$(".docs-p").each(function() 
					{
						fiscal_folio.push($(this).find('[name="folio_fiscal[]"]').val());
						ticket_number.push($(this).find('[name="num_ticket[]"]').val());
						timepath.push($(this).find('[name="timepath[]"]').val());
						amount_ticket.push(Number($(this).find('[name="monto[]"]').val()).toFixed(2));
						datepath.push($(this).find('[name="datepath[]"]').val());
					});
					swal({
						icon               : '{{ asset(getenv("LOADING_IMG")) }}',
						button             : false,
						closeOnClickOutside: false,
						closeOnEsc         : false
					});
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("refund.validation-document") }}',
						data 		: 
						{
							'fiscal_folio'	: fiscal_folio,
							'ticket_number'	: ticket_number,
							'timepath'		: timepath,
							'amount'		: amount_ticket,
							'datepath'		: datepath,
						},
						success: function(data)
						{
							flag = true;
							$(".docs-p").each(function(j,v)
							{
								datepath      = $(this).find("[name='datepath[]']");
								timepath      = $(this).find("[name='timepath[]']");
								fiscal_folio  = $(this).find("[name='folio_fiscal[]']");
								ticket_number = $(this).find("[name='num_ticket[]']");
								amount_ticket = $(this).find("[name='monto[]']");

								$(data).each(function(i,d)
								{
									if (j == d) 
									{
										datepath.addClass('error');
										timepath.addClass('error');
										fiscal_folio.addClass('error');
										ticket_number.addClass('error');
										amount_ticket.addClass('error');
										flag = false;
									}
								});
							});
						},
						error: function(data)
						{
							flag = false;
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					}).done(function(data) 
					{
						if(flag)
						{
							@php
								$modelHead = 
								[
									[
										["value" => "#"],
										["value" => "Concepto"],
										["value" => "Clasificación del gasto"],
										["value" => "Fiscal"],
										["value" => "Subtotal"],
										["value" => "IVA"],
										["value" => "Impuesto Adicional"],
										["value" => "Retenciones"],
										["value" => "Importe"],
										["value" => "Documento(s)"],
										["value" => "Acción"]
									]
								];
								$modelBody = [];
								$modelBody = 
								[
									[
										"classEx" => "tr",
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label" => "",
													"classEx" => "countConcept",
												],
											]	
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label" => "",
													"classEx" => "concept"
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t_concept",
													"attributeEx" => "type=\"hidden\" name=\"t_concept[]\" value=\"\"",
												],
												[
													"kind"    => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"idRDe[]\" value=\"x\"",
													"classEx" => "idRDe idRefundDetail",
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label" => "",
													"classEx" => "accountText",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t_account",
													"attributeEx" => "type=\"hidden\" name=\"t_account[]\" value=\"\"",
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"  => "components.labels.label",
													"label" => "",
													"classEx" => "fiscal",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t_fiscal",
													"attributeEx" => "type=\"hidden\" name=\"t_fiscal[]\" value=\"\"",
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label" => "",
													"classEx" => "subamount",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t-amount t_amount",
													"attributeEx" => "type=\"hidden\" name=\"t_amount[]\" value=\"\"",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "amounttemp",
													"attributeEx" => "type=\"hidden\" value=\"\"",
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"  => "components.labels.label",
													"label" => "",
													"classEx" => "ivaCal",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t_iva",
													"attributeEx" => "type=\"hidden\" name=\"t_iva[]\" value=\"\"",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t-iva-kind",
													"attributeEx" => "type=\"hidden\" name=\"t_iva_kind[]\" value=\"\"",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t-iva",
													"attributeEx" => "type=\"hidden\" name=\"t_iva_val[]\" value=\"\"",
												],
												[
													"kind"  => "components.inputs.input-text",
													"classEx" => "t_iva_kind",
													"attributeEx" => "type=\"hidden\" name=\"tivakind[]\" value=\"\" readonly",
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"  => "components.labels.label",
													"label" => "",
													"classEx" => "amountAAtotal"
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"  => "components.labels.label",
													"label" => "",
													"classEx" => "retentionValue"
												],
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind"  => "components.labels.label",
													"label" => "",
													"classEx" => "total-amount",
												],
												[
													"kind" => "components.inputs.input-text",
													"classEx" => "t_total",
													"attributeEx" => "type=\"hidden\" name=\"t_total[]\" value=\"\"",
												],
											],
										],
										[
											"classEx" => "td allPaths",
											"content" =>
											[
												"label" => "",
											],
										],
										[
											"classEx" => "td",
											"content" =>
											[
												[
													"kind" => "components.buttons.button", 
													"classEx" => "edit-item",
													"attributeEx" => "id=\"edit\" type=\"button\"",
													"variant" => "success",
													"label" => "<span class=\"icon-pencil\"></span>",
												],
												[
													"kind" => "components.buttons.button", 
													"classEx" => "delete-item",
													"attributeEx" => "id=\"cancel\" type=\"button\"",
													"variant" => "red",
													"label" => "<span class=\"icon-x\"></span>",
												],
											],
										],
									],
								];
								$table = view("components.tables.table",[
									"modelHead" 	  => $modelHead,
									"modelBody" 	  => $modelBody,
									"themeBody" 	  => "striped",
									"attributeExBody" => "id=\"body\"", 
									"noHead"		  => "true"
								])->render();
								$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
							tr_table = $(table);
							tempFlag = 1;
							$(".docs-p").each(function(i, v)
							{
								pathName		= $(this).find('[name="realPath"]').val();
								folio_fiscal	= $(this).find('[name="folio_fiscal[]"]').val();
								ticket_number	= $(this).find('[name="num_ticket[]"]').val();
								amount_ticket	= $(this).find('[name="monto[]"]').val();
								timepath		= $(this).find('[name="timepath[]"]').val();
								datepath		= $(this).find('[name="datepath[]"]').val();
								nameDoc			= $(this).find('[name="name_document[]"] option:selected').val();

								@php
									$newButtonPDF = view("components.buttons.button", [
										"buttonElement" => "a",
										"attributeEx" => "href=\"#\"",
										"variant" => "dark-red",
										"label"   => "<span class=\"fas fa-file-alt\"></span>",
									])->render();

									$dateLabel = view("components.labels.label")->render();
									
								@endphp
								newButtonPDF = '{!!preg_replace("/(\r)*(\n)*/", "", $newButtonPDF)!!}';
								dateLabel = '{!!preg_replace("/(\r)*(\n)*/", "", $dateLabel)!!}';

								tr_table.find(".allPaths").append($("<div class='nowrap'></div>").append($(dateLabel).text($(".datepath").get(i).value))
										.append($(newButtonPDF).attr('title',pathName))
										.append($('<input type="hidden" name="t_path'+doc+'[]" class="num_path">').val(pathName))
										.append($('<input type="hidden" name="t_fiscal_folio'+doc+'[]" class="num_fiscal_folio">').val(folio_fiscal))
										.append($('<input type="hidden" name="t_ticket_number'+doc+'[]" class="num_ticket_number">').val(ticket_number))
										.append($('<input type="hidden" name="t_amount'+doc+'[]" class="num_amount">').val(amount_ticket))
										.append($('<input type="hidden" name="t_timepath'+doc+'[]" class="num_timepath">').val(timepath))
										.append($('<input type="hidden" name="t_datepath'+doc+'[]" class="num_datepath">').val(datepath))
										.append($('<input type="hidden" name="t_name_doc'+doc+'[]" class="num_name_doc">').val(nameDoc))
										.append($('<input type="hidden" name="t_new'+doc+'[]" class="num_new">').val(1)));
							});
			
							total		 = Number(subamount) + Number(ivaCal);
							countConcept = countConcept+1;

							tr_table.find(".countConcept").text(countConcept);
							tr_table.find(".concept").text(concept);
							tr_table.find(".t_concept").val(concept);
							tr_table.find(".accountText").text(accountText);
							tr_table.find(".t_account").val(account);
							tr_table.find(".fiscal").text(fiscal);
							tr_table.find(".t_fiscal").val(fiscal);
							tr_table.find(".subamount").text("$ "+Number(subamount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tr_table.find(".t_amount").val(subamount);
							tr_table.find(".amounttemp").val(subamount);
							tr_table.find(".ivaCal").text("$ "+Number(ivaCal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tr_table.find(".t_iva").val(iva);
							tr_table.find(".t-iva-kind").val(ivaKind);
							tr_table.find(".t-iva").val(ivaCal);
							tr_table.find(".t_iva_kind").val(ivaKind);
							tr_table.find(".amountAAtotal").text("$ "+Number(amountAAtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tr_table.find(".retentionValue").text("$ "+Number(retentionValue).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tr_table.find(".total-amount").text("$ "+Number(total+amountAAtotal-retentionValue).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
							tr_table.find(".t_total").val(Number(total).toFixed(2));
							tr_table.find(".allPaths")
								.append(nameRetentions)
								.append(retentions)
								.append(nameAmounts)
								.append(amountsAA);
							$('#body').append(tr_table);
							$('.js-accounts').val(null).trigger('change');
							$('.js-accounts').empty();
							$('input[name="concept"]').val('');
							$('input[name="account"]').val('');
							$('input[name="account_id"]').val('');
							$('#nofiscal').prop("checked",true);
							$('#noiva,#iva_a').prop('checked',true);
							$('.iva,.iva_kind').prop('disabled',true);
							$('input[name="amount"]').val('');
							$('input[name="concept"]').removeClass('error');
							$('input[name="path"]').removeClass('error');
							$('input[name="datepath"]').removeClass('error');
							$('input[name="amount"]').removeClass('error');
							$('#documents').empty();
							$('.add-concept').removeAttr('disabled');
							$('.additionalName').val('');
							$('.additionalAmount').val('');
							
							$('#documents').html('');
							$('#js-account-span').removeClass('help-block')
							$('#js-account-span').removeClass('form-error')
							$('#js-account-span').hide();
							$('.subamount').val("");
							
							retVal		= ({{ $retentionConcept2 }});
							$('input[name="amountRetentions"]').val(retVal+retentionValue);
							
							additionalCleanComponent();
							retentionCleanComponent();
							
							total_cal();
							if ($('select[name="request_kind"] option:selected').val() == 0) 
							{
								refund();
							}
							doc++;
                            setInterval(() =>
                            {
                                $('#add').removeAttr('disabled', 'disabled');
                            }, 2000);
							$('.edit-item').removeAttr('disabled');
							swal.close();
						}
						else
						{
							swal('','Los documentos marcados ya se encuentran registrados.','error');
						}
					});
				}
				else
				{
                    $('#add').removeAttr('disabled', 'disabled');
					swal("", message+messageExtra+".", "error");
				}
			})
			.on('change','.fiscal,.iva,.iva_kind,.subamount,.additionalAmount,.retentionAmount',function()
			{
				amountConcept();	
			})
			.on('click','.edit-item',function()
			{
				actioner 		= $(this);
				section  		= "table";
				concept			= $('input[name="concept"]').val().trim();
				date			= $('input[name="datepath"]').length;
				account			= $('.js-accounts option:selected').val();
				path			= $('input[name="realPath"]').length;
				amount			= $('input[name="amount"]').val().trim();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				totalAmount		= 0;
				ivaCal			= 0;
				if (concept != "" || date > 0 || account != null || path > 0) 
				{
					swal('', 'Tiene un concepto sin agregar a la lista', 'error');
				}
				else
				{
					idRDe			= $(this).parents('.tr').find('.idRDe').val();
					t_concept		= $(this).parents('.tr').find('.t_concept').val();
					t_account		= $(this).parents('.tr').find('.t_account').val();
					t_accountText 	= $(this).parents('.tr').find('.accountText').text();
					t_fiscal		= $(this).parents('.tr').find('.t_fiscal').val();
					t_amount		= $(this).parents('.tr').find('.t_amount').val();
					t_iva			= $(this).parents('.tr').find('.t_iva').val();
					t_iva_kind		= $(this).parents('.tr').find('.t_iva_kind').val();
					amounttemp 		= $(this).parents('.tr').find('.amounttemp').val();
				
					swal({
						title		: "Editar concepto",
						text		: "Al editar, los impuestos adicionales, las retenciones adicionales y los documentos agregados en este concepto seran eliminados por lo que deberá cargarlos de nuevo. ¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((continuar) =>
					{
						if(continuar)
						{
							if(t_fiscal == 'si')
							{
								$('.iva,.iva_kind').removeAttr('disabled',false);
								$('#fiscal').prop("checked",true);
								if (t_iva == 'si') 
								{
									$('#siiva').prop("checked",true);
									if (t_iva_kind == 'a') 
									{
										$('#iva_a').prop("checked",true);
										ivaCal = amounttemp*iva; 
									}
									else
									{
										$('#iva_b').prop("checked",true);
										ivaCal = amounttemp*iva2;
									}
								}
								else
								{
									$('#noiva').prop("checked",true);
								}
							}
							else
							{
								$('.iva,.iva_kind').prop('disabled',true);
								$('#nofiscal').prop("checked",true);
							}
							totalAmount = (parseFloat(amounttemp)+parseFloat(ivaCal));
							$('input[name="concept"]').val(t_concept);
							$('.js-accounts').append('<option value='+t_account+' selected="selected">'+t_accountText+'</option>');
							$('.amount').val(totalAmount.toFixed(2));
							$('.subamount').val(parseFloat(amounttemp).toFixed(2));
							deleteDocs(section, actioner);
							$('.edit-item').attr('disabled','disabled');
						}
						else
						{
							swal.close();
						}
					});
				}
			})
			.on('click','.checkbox',function()
			{
				$('.accountSelect').removeClass('accountSelect');
				$(this).parents('.tr').addClass('accountSelect');
			})
			.on('click','[name="save"]', function(e)
			{
				e.preventDefault();
				object 		= $(this);
				$('#body').find('.tr-red').removeClass('tr-red');
				swal({
					title				: 'Cargando',
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button				: false,
					text				: 'Validando los documentos',
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});

				fiscal_folio	= [];
				ticket_number	= [];
				timepath		= [];
				amount			= [];
				datepath		= [];
				new_doc			= [];
				
				requestFolio = $('.requestFolio').val();
				fromOther = '';
				if(requestFolio != null && requestFolio != '')
				{
					fromOther = 1;
				}
				
				if ($('.num_datepath').length > 0) 
				{
					$('.num_datepath').each(function(i,v)
					{
						fiscal_folio.push($(this).siblings('.num_fiscal_folio').val());
						ticket_number.push($(this).siblings('.num_ticket_number').val());
						timepath.push($(this).siblings('.num_timepath').val());
						amount.push(Number($(this).siblings('.num_amount').val()).toFixed(2));
						datepath.push($(this).val());
						new_doc.push($(this).siblings('.num_new').val());
					});
					
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route("refund.validation-document") }}',
						data	: 
						{
							'fiscal_folio'	: fiscal_folio,
							'ticket_number'	: ticket_number,
							'timepath'		: timepath,
							'amount'		: amount,
							'datepath'		: datepath,
							'requestFolio'  : requestFolio,
							'new_doc'		: new_doc,
							'fromOther'		: fromOther,
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
										tr.parents('.tr').addClass('tr-red');
										flag = true;
									}
								});
							});
							if (flag) 
							{
								swal('','Los conceptos marcados contienen documentos que ya han sido utilizados, por favor verifique sus datos.','error');
							}
						},
						error : function(data)
						{
							flag = true;
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
			.on('click','[name="send"]',function(e)
			{
				e.preventDefault();
				object 		= $(this);
				$('#body').find('.tr-red').removeClass('tr-red');
				swal({
					title				: 'Cargando',
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button				: false,
					text				: 'Validando los documentos',
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});

				fiscal_folio	= [];
				ticket_number	= [];
				timepath		= [];
				amount			= [];
				datepath		= [];
				new_doc			= [];
				requestFolio	= $('.requestFolio').val();
				fromOther = '';
				if(requestFolio != null && requestFolio != '')
				{
					fromOther = 1;
				}
				
				if ($('.num_datepath').length > 0) 
				{
					$('.num_datepath').each(function(i,v)
					{
						fiscal_folio.push($(this).siblings('.num_fiscal_folio').val());
						ticket_number.push($(this).siblings('.num_ticket_number').val());
						timepath.push($(this).siblings('.num_timepath').val());
						amount.push(Number($(this).siblings('.num_amount').val()).toFixed(2));
						datepath.push($(this).val());
						new_doc.push($(this).siblings('.num_new').val());
					});

					$.ajax(
					{
						type	: 'post',
						url		: '{{ route("refund.validation-document") }}',
						data	: 
						{
							'fiscal_folio'	: fiscal_folio,
							'ticket_number'	: ticket_number,
							'timepath'		: timepath,
							'amount'		: amount,
							'datepath'		: datepath,
							'requestFolio'  : requestFolio,
							'new_doc'		: new_doc,
							'fromOther'		: fromOther,
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
										tr.parents('.tr').addClass('tr-red');
										flag = true;
									}
								});
							});
							if (flag) 
							{
								swal('','Los conceptos marcados contienen documentos que ya han sido utilizados en otra solicitud.','error');
							}
						},
						error: function(data)
						{
							flag = true;
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
						swal({
							icon				: '{{ asset(getenv("LOADING_IMG")) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						form[0].reset();
						$('.removeselect').val(null).trigger('change');
						additionalCleanComponent();
						retentionCleanComponent();
						$('.removeinput').val('');
						$('#banks, .resultbank').hide();
						section = "all";
						deleteDocs(section);
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','.js-enterprises',function(e)
			{
				$('.js-accounts').empty();
			})
			.on('click','.delete-item',function()
			{
				actioner = $(this);
				swal({
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});
				section = "table";
				deleteDocs(section, actioner);
			})
			.on('change', '.js-users', function()
			{
				getAccounts();
			})
			.on('click','input[name="method"]',function()
			{
				getAccounts();
			})
			.on('click','.span-delete',function()
			{
				$(this).parents('span').remove();
			})
			.on('click','#help-btn-method-pay',function()
			{
				swal('Ayuda','En este apartado debe seleccionar la forma de pago, si usted selecciona "Cuenta Bancaria", deberá seleccionar una de las cuentas que se le muestran del "Solicitante", en caso de que no tenga cuenta, por favor solicite al "Solicitante" que agregue al menos una cuenta bancaria.','info');
			})
			.on('click','#help-btn-documents',function()
			{
				swal('Ayuda','En este apartado se debe agregar cada uno de los conceptos por los cuales solicita un reembolso','info');
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal("", "@lang('messages.extension_allowed', ['param' => 'jpg, png o pdf' ])", "warning");
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb, por favor verifique su tamaño.', 'warning');
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
						url			: '{{ route("refund.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						cache		: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
							}
						},
						error: function(err)
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath"]').val('');
						}
					})
				}
			});
		});
		
		function total_cal()
		{
			
			subtotal			= 0;
			ivaTotal			= 0;
			amount_taxes		= 0;
			amount_retentions	= 0;
			$("#body .tr").each(function(i, v)
			{
				ivaTotal				+= Number($(this).find('.t-iva').val());
				subtotal				+= Number($(this).find('.amounttemp').val());
				temp_amount_taxes		= 0;
				temp_amount_retentions	= 0;

				$(this).find(".num_amountAdditional").each(function(i, v)
				{
					amount_taxes += Number($(this).val());
				});

				$(this).find(".num_amountRetention").each(function(i, v)
				{
					amount_retentions += Number($(this).val());
				});
			});

			total = (subtotal+ivaTotal+amount_taxes)-amount_retentions;
			$(".label-subtotal").text("$ "+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$(".subtotal").val(Number(subtotal).toFixed(2));

			$(".label-IVA").text("$ "+Number(ivaTotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$(".ivaTotal").val(Number(ivaTotal).toFixed(2));

			$(".label-total").text("$ "+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$(".total").val(Number(total).toFixed(2));

			$(".label-taxes").text("$ "+Number(amount_taxes).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('input[name="amountAA"]').val(Number(amount_taxes).toFixed(2));

			$(".label-retentions").text("$ "+Number(amount_retentions).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
			$('input[name="amountRetentions"]').val(Number(amount_retentions).toFixed(2));

		}
		function amountConcept()
		{
			iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc			= 0;
			taxAditional 	= 0;
			retention 		= 0;
			
			subamount = $('.subamount').val();

			$('.additionalAmount').each(function()
			{ 
				if($(this).val())
				{
					taxAditional+=parseFloat($(this).val()); 
				} 
			});

			$('.retentionAmount').each(function(){
				if($(this).val())
				{
					retention+=parseFloat($(this).val()); 
				} 
			});

			switch($('input[name="fiscal"]:checked').val())
			{
				case 'no':
					ivaCalc = 0;
					$("#noiva").prop('checked',true);
					$(".iva").prop('disabled', true);
					$("#iva_a").prop('checked',true);
					$(".iva_kind").prop('disabled', true);
					break;
				case 'si':
					switch($('input[name="iva"]:checked').val())
					{
						case 'no':
							ivaCalc = 0;
							$("#iva_a").prop('checked',true);
							$(".iva_kind").prop('disabled', true);
							break;
						case 'si':
							switch($('input[name="iva_kind"]:checked').val())
							{
								case 'a':
									ivaCalc = subamount*iva;
									break;
								case 'b':
									ivaCalc = subamount*iva2;
									break;
							}
							break;
					}
					break;
			}

			ivaTotal = Number(ivaCalc+taxAditional-retention);
			totalImporte    = Number(subamount)+ivaTotal;
			if (totalImporte != '' && totalImporte != null)
			{
				$('.amount').val(totalImporte.toFixed(2));
			}
			else
			{
				$('.amount').val(null);
			}
		}
		function deleteConcept() 
		{
			$('#body').empty();
			$('.js-accounts').empty();
			doc = 0;
			$enterprise = $(this).val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("refund.create.account") }}',
				data 	: {'enterpriseid':$enterprise},
				success : function(data)
				{
					$.each(data,function(i, d)
					{
						$('.js-accounts').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
					});
				},
				error : function(data)
				{
					$('.js-accounts').html('');
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
		}
		function deleteDocs(section, actioner)
		{
			namesPaths 	= [];
			flagSwal 	= false;
			if(section 	== "concept")
			{
				valuePath = actioner.parents('.docs-p').find(".path").val();
				if(valuePath != "")
				{
					namesPaths.push(valuePath);
				}
			}
			if(section == "table")
			{
				idRD = actioner.parents(".tr").find('.idRefundDetail').val();
				if( idRD == 'x')
				{
					actioner.parents(".tr").find('.num_path').each( function()
					{
						namesPaths.push($(this).val());
					});
				}
			}
			if(section == "all")
			{
				$("#documents").find(".path").each( function()
				{
					valuePath = $(this).val();
					if(valuePath != "")
					{
						namesPaths.push(valuePath);
					}
				});

				$("#body .tr").find('.idRefundDetail').each( function()
				{
					idRD = $(this).val();
					if( idRD == 'x')
					{
						$(this).parents(".tr").find('.num_path').each( function()
						{
							namesPaths.push($(this).val());
						});
					}
				});
			}
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("refund.upload") }}',
				data		: {'namesPaths': namesPaths},
				success		: function(r)
				{
					if(section == "concept")
					{
						actioner.parents('.docs-p').remove();
					}
					if(section == "table")
					{
						actioner.parents('.tr').remove();
						
						$('#body .tr').each(function(i,v)
						{
							$(this).find('.num_path').attr('name','t_path'+i+'[]');
							$(this).find('.num_name_doc').attr('name','t_name_doc'+i+'[]');
							$(this).find('.num_fiscal_folio').attr('name','t_fiscal_folio'+i+'[]');
							$(this).find('.num_ticket_number').attr('name','t_ticket_number'+i+'[]');
							$(this).find('.num_timepath').attr('name','t_timepath'+i+'[]');
							$(this).find('.num_amount').attr('name','t_amount'+i+'[]');
							$(this).find('.num_new').attr('name','t_new'+i+'[]');
							$(this).find('.num_datepath').attr('name','t_datepath'+i+'[]');
							$(this).find('.num_nameAmount').attr('name','t_name_tax'+i+'[]');
							$(this).find('.num_amountAdditional').attr('name','t_amount_tax'+i+'[]');
							$(this).find('.num_amountRetention').attr('name','t_amount_retention'+i+'[]');
							$(this).find('.num_nameRetention').attr('name','t_name_retention'+i+'[]');
						});
						if($('.countConcept').length>0)
						{
							$('.countConcept').each(function(i,v)
							{
								$(this).html(i+1);
							});
						}
						doc = $('#body .tr').length;
						total_cal();
					}
					if(section == "all")
					{
						$('#body, #documents').html('');
						total_cal();
					}
					flagSwal = true;
				},
				error: function(r)
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			}).done(function(r)
			{
				if(flagSwal)
				{
					swal.close();
				}
			});
		}
		function getAccounts()
		{
			flagHidden = false;
			if($('.js-users option:selected').val() != null)
			{
				if($('[name="method"]:checked').val() == 1)
				{
					id = $('.js-users option:selected').val();
					folio 	= $('#id'+id).text();
					$('#efolio').val(folio);
					$text = $('#efolio').val();

					$.ajax({
						type : 'post',
						url  : '{{ route("refund.search.bank") }}',
						data : {'idUsers':id},
						success:function(data)
						{
							$('.resultbank, .bankAccount').removeClass('hidden').addClass('block');
							$('.resultbank').html(data);
							@php
								$selects = collect([
									[
										"identificator"         =>"[name=\"bank\"]", 
										"placeholder"           =>"Seleccione el banco",
										"maximumSelectionLength"=>"1",
									]
								]);
							@endphp
							@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
							generalSelect({'selector': '.bank', 'model': 27});
						},
						error:function(data)
						{
							$('.resultbank').html('');
							$('.resultbank, .bankAccount').removeClass('block').addClass('hidden');
							swal('','Lo sentimos ocurrió un error, por favor intente de nuevo.','error');
						}
					});
				}
				else
				{
					flagHidden = true;
				}
			}
			else
			{
				flagHidden = true;
			}

			if(flagHidden)
			{
				$('.account').removeClass('error').removeClass('valid').removeAttr('style').val('').parent().removeClass('has-error').find('.form-error').remove();
				$('.clabe').removeClass('error').removeClass('valid').removeAttr('style').val('').parent().removeClass('has-error').find('.form-error').remove();
				$('.card-number').removeClass('error').removeClass('valid').removeAttr('style').val('').parent().removeClass('has-error').find('.form-error').remove();
				$('.alias').removeClass('error').val('');
				$('[name="bank"]').val(null).trigger('change');
				$('.resultbank').removeClass('block').addClass('hidden');
				$('#banks').removeClass('block').addClass('hidden');
			}
		}
	</script>
@endsection
