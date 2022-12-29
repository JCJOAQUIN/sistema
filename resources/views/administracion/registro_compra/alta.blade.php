@extends('layouts.child_module')

@section('data')
@php
$taxesCount = 0;
	$taxes = $retentions = 0;
@endphp
@component("components.forms.form", ["attributeEx" => "action=\"".route('purchase-record.store')."\" method=\"POST\" id=\"container-alta\"", "files" => true])
	@component("components.labels.title-divisor") REGISTRO DE COMPRA @endcomponent
	@component("components.containers.container-form")
		<div class="col-span-2">
			@component("components.labels.label") Título: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($requests)) value="{{ $requests->purchaseRecord->title }}" @endif
				@endslot
				@slot("classEx")
					new-input-text removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="datetitle" @if(isset($requests)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $requests->purchaseRecord->datetitle)->format('d-m-Y') }}" @endif data-validation="required" placeholder="Ingrese la fecha" readonly="readonly"
				@endslot
				@slot("classEx")
					new-input-text removeselect datepicker2
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fiscal: @endcomponent
			<div class="flex space-x-2">
				@component("components.buttons.button-approval")
					@slot("attributeEx")
						type="radio" checked name="fiscal" id="nofiscal" value="0" @if(isset($requests) && $requests->taxPayment==0) checked @endif
					@endslot
					No
				@endcomponent
				@component("components.buttons.button-approval")
					@slot("attributeEx")
						type="radio" name="fiscal" id="fiscal" value="1" @if(isset($requests) && $requests->taxPayment==1) checked @endif
					@endslot
					Sí
				@endcomponent
			</div>
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Número de Orden (Opcional): @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="numberOrder" placeholder="Ingrese el número de orden" @if(isset($requests)) value="{{ $requests->purchaseRecord->numberOrder }}" @endif
				@endslot
				@slot("classEx")
					new-input-text removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Solicitante: @endcomponent
			@php
				$optionUser = [];
				if(isset($requests->idRequest) && $requests->idRequest !="")
				{
					$optionUser[] 	= ["value" => $requests->idRequest, "description" => $requests->requestUser->fullName(), "selected" => "selected"];
				}
			@endphp
			@component("components.inputs.select", ["options" => $optionUser ])
				@slot("attributeEx")
					name="userid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
				{
					$options = $options->concat(
					[
						[
							"value" 		=> $enterprise->id, 
							"description"	=> $enterprise->name, 
							"selected"		=> ((isset($requests) && $requests->idEnterprise == $enterprise->id) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("attributeEx")
					name="enterpriseid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Dirección: @endcomponent
			@php
				$options = collect();
				foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
				{
					$options = $options->concat(
					[
						[
							"value"			=> $area->id, 
							"description"	=> $area->name, 
							"selected"		=> ((isset($requests) && $requests->idArea == $area->id) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("attributeEx")
					name="areaid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Departamento: @endcomponent
			@php
				$options = collect();
				foreach(App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->orderBy('name','asc')->get() as $department)
				{
					$options = $options->concat(
					[
						[
							"value"			=> $department->id, 
							"description"	=> $department->name, 
							"selected"		=> ((isset($requests) && $requests->idDepartment == $department->id) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("attributeEx")
					name="departmentid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Clasificación de Gasto: @endcomponent
			@component("components.inputs.select", 
			[
				"options" => 
					isset($requests) ? 
					[
						[
							"value" => $requests->accounts->idAccAcc, 
							"description" =>  $requests->accounts->account." - ".$requests->accounts->description." (".$requests->accounts->content.")", 
							"selected" => "selected"
						]
					] 
					: 
					[]
			])
				@slot("attributeEx")
					name="accountid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Proyecto: @endcomponent
			@component("components.inputs.select", 
			[
				"options" => 
					isset($requests) ? 
					[
						[
							"value" => $requests->requestProject->idproyect, 
							"description" =>  $requests->requestProject->proyectName, 
							"selected" => "selected"
						]
					] 
					: 
					[]
			])
				@slot("attributeEx")
					name="projectid" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect js-projects
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 select_wbs @if(!isset($requests)) hidden @elseif(isset($requests) && $requests->idProject != '' && $requests->requestProject->codeWBS()->exists()) @else hidden @endif">
			@component("components.labels.label") WBS: @endcomponent
			@component("components.inputs.select", 
			[
				"options" => 
					isset($requests->wbs->id) ? 
					[
						[
							"value" => $requests->wbs->id, 
							"description" =>  $requests->wbs->code_wbs, 
							"selected" => "selected"
						]
					] 
					: 
					[]
			])
				@slot("attributeEx")
					name="code_wbs" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect js-code_wbs
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 select_edt @if(!isset($requests)) hidden @elseif(isset($requests) && $requests->code_wbs != '' && $requests->wbs->codeEDT()->exists()) @else hidden @endif">
			@component("components.labels.label") EDT: @endcomponent
			@component("components.inputs.select", 
			[
				"options" => 
					isset($requests->edt->id) ? 
					[
						[
							"value" => $requests->edt->id, 
							"description" =>  $requests->edt->code.' ('.$requests->edt->description.')', 
							"selected" => "selected"
						]
					] 
					: 
					[]
			])
				@slot("attributeEx")
					name="code_edt" data-validation="required" multiple="multiple"
				@endslot
				@slot("classEx")
					removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Proveedor: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="provider" placeholder="Ingrese un proveedor" data-validation="required" @if(isset($requests)) value="{{ $requests->purchaseRecord->provider }}" @endif
				@endslot
				@slot("classEx")
					new-input-text removeselect
				@endslot
			@endcomponent
		</div>
	@endcomponent

	@component("components.labels.title-divisor") DATOS DEL PEDIDO @endcomponent
	@component("components.containers.container-form")
		<div class="col-span-2">
			@component("components.labels.label") Cantidad: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="quantity" placeholder="Ingrese la cantidad"
				@endslot
				@slot("classEx")
					input-text quanty
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Unidad: @endcomponent
			@php
				$options = collect();
				foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
				{
					foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
					{
						$options = $options->concat([["value" => $child->description, "description" =>  $child->description]]);
					}
				}
			@endphp
			@component("components.inputs.select", ["options" => $options])
				@slot("attributeEx")
					name="unit" multiple="multiple"
				@endslot
				@slot("classEx")
					unit form-control
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Descripción: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="description" placeholder="Ingrese la descripción"
				@endslot
				@slot("classEx")
					input-text
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Precio Unitario: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="text" name="price" placeholder="Ingrese el precio unitario"
				@endslot
				@slot("classEx")
					input-text price
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 @if(isset($requests) && $requests->taxPayment == 0) hidden @endif">
			@component("components.labels.label") Tipo de IVA: @endcomponent
			<div class="flex space-x-2">
				@component("components.buttons.button-approval")
					@slot("attributeEx")
						type="radio" name="iva_kind" id="iva_no" value="no" checked="" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
					@endslot
					@slot("attributeExLabel")
						title="No IVA"
					@endslot
					@slot("classEx")
						iva_kind
					@endslot
					No
				@endcomponent
				@component("components.buttons.button-approval")
					@slot("attributeEx")
						type="radio" name="iva_kind" id="iva_a" value="a" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
					@endslot
					@slot("attributeExLabel")
						title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
					@endslot
					@slot("classEx")
						iva_kind
					@endslot
					A
				@endcomponent
				@component("components.buttons.button-approval")
					@slot("attributeEx")
						type="radio" name="iva_kind" id="iva_b" value="b" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
					@endslot
					@slot("attributeExLabel")
						title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
					@endslot
					@slot("classEx")
						iva_kind
					@endslot
					B
				@endcomponent
			</div>
		</div>
		<div class="col-span-2 md:col-span-4">
			@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional_exist'])  @endcomponent
		</div>
		<div class="col-span-2 md:col-span-4">
			@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention_new'])  @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Importe: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					readonly type="text" name="amount" placeholder="Ingrese el importe"
				@endslot
				@slot("classEx")
					input-text amount
				@endslot
			@endcomponent
			<div class="mt-4">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("attributeEx")
						type="button" name="add" id="add"
					@endslot
					@slot("classEx")
						add2
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar concepto</span>
				@endcomponent
			</div>
		</div>
	@endcomponent
	
	@php
		$modelHead = 
		[
			[
				["value" => "#"],
				["value" => "Cantidad"],
				["value" => "Unidad"],
				["value" => "Descripción"],
				["value" => "Precio Unitario"],
				["value" => "IVA"],
				["value" => "Impuesto adicional"],
				["value" => "Retenciones"],
				["value" => "Importe"],
				["value" => "Acciones"],
			]
		];
		if(isset($requests))
		{
			$modelBody	=	[];
			foreach($requests->purchaseRecord->detailPurchase as $key=>$detail)
			{
				$taxesConcept	=	0;
				
				$taxesTd = [];
				foreach($detail->taxes as $tax)
				{
					$taxesInputs = 
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"",
							"classEx"		=> "num_amountAdditional"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"tnameamount".$taxesCount."[]"."\" value=\"".htmlentities($tax->name)."\"",
							"classEx"		=> "num_nameAmount"
						]
					];

					$taxesTd["content"] = $taxesInputs;
					$taxesConcept+=$tax->amount;
				}
				$taxesTd["content"][] = 
				[
					"kind" => "components.labels.label",
					"label"=> number_format($taxesConcept,2)
				];

				$retentionConcept	=	0;
				$retTd = [];
				foreach($detail->retentions as $ret)
				{
					$retInputs = 
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"",
							"classEx"		=> "num_amountRetention"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" name=\"tnameretention".$taxesCount."[]"."\" value=\"".htmlentities($ret->name)."\"",
							"classEx"		=> "num_nameRetention"
						]
					];

					$retTd["content"] = $retInputs;
					$retentionConcept+=$ret->amount;
				}
				$retTd["content"][] = 
				[
					"kind" => "components.labels.label",
					"label"=> number_format($retentionConcept,2)
				];
				
				$taxesCount++;

				$body = 
				[
					"classEx" => "tr",
					[					
						"classEx" => "countConcept",
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label"=> $key+1
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label"=> $detail->quantity
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
								"classEx"		=> "input-table tquanty"
							]
						]
					],
					[
						"content" =>
						[
							[
								
								"kind" => "components.labels.label",
								"label"=> $detail->unit
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\"",
								"classEx"		=> "input-table tunit"
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label"=> htmlentities($detail->description)
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\"",
								"classEx"		=> "input-table tdescr"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
								"classEx"		=> "input-table tivakind"
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label"=> "$ ".$detail->unitPrice
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
								"classEx"		=> "input-table tprice"
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.labels.label",
								"label"=> "$ ".$detail->tax
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
								"classEx"		=> "input-table tiva"
							]
						]
					],
					$taxesTd,
					$retTd,
					[
						"content" =>
						[
							[
								"kind" 	=> "components.labels.label",
								"label" => "$".$detail->total
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->total."\"",
								"classEx"		=> "input-table tamount"
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "id=\"edit\" type=\"button\"",
								"classEx"		=>  "edit-item",
								"variant"		=> "success",
								"label"			=> "<span class=\"icon-pencil\"></span>"
							],
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "type=\"button\"",
								"classEx"		=> "delete-item",
								"variant"		=> "red",
								"label"			=> "<span class=\"icon-x\"></span>"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		}
		else
		{
			$modelBody = [];
		}
		
	@endphp
	@Table(["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\"", "attributeExBody" => "id=\"body\""])@endTable
	@php
		$subtotal			= isset($requests) ? $requests->purchaseRecord->subtotal : '0.00';
		$amount_taxes		= isset($requests) ? $requests->purchaseRecord->amount_taxes : '0.00';
		$amount_retention	= isset($requests) ? $requests->purchaseRecord->amount_retention : '0.00';
		$tax				= isset($requests) ? $requests->purchaseRecord->tax : '0.00';
		$total				= isset($requests) ? $requests->purchaseRecord->total : '0.00';
		
		$modelTable	=
		[
			[
				"label"	=>	"Subtotal:",			
				"inputsEx" => 
				[
					["kind" => "components.labels.label", "classEx" => "subtotal_class py-2", "label" => "$ ".$subtotal],
					[
						"kind" => "components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\"	readonly type=\"hidden\"	name=\"subtotal\"	value=\"".$subtotal."\"",
					]
				]
			],
			[
				"label"	=>	"Impuesto Adicional:",	
				"inputsEx" => 
				[
					["kind" => "components.labels.label", "classEx" => "amount_tax_class py-2", "label" => "$ ".$amount_taxes],
					["kind" => "components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\"	readonly type=\"hidden\"	name=\"amountAA\"	value=\"$ ".$amount_taxes."\""]
				]
			],
			[
				"label"	=>	"Retenciones:",			
				"inputsEx" => 
				[
					["kind" => "components.labels.label", "classEx" => "amount_ret_class py-2", "label" => "$ ".$amount_retention],
					["kind" => "components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\"	readonly type=\"hidden\"	name=\"amountR\"	value=\"$ ".$amount_retention."\""]
				]
			],
			[
				"label"	=>	"IVA:",					
				"inputsEx" => 
				[
					["kind" => "components.labels.label", "classEx" => "iva_class py-2", "label" => "$ ".$tax],
					["kind" => "components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\"	readonly type=\"hidden\"	name=\"totaliva\"	value=\"".$tax."\""]
				]
			],
			[
				"label"	=>	"TOTAL:",				
				"inputsEx" => 
				[
					["kind" => "components.labels.label", "classEx" => "total_class py-2", "label" => "$ ".$total],
					["kind" => "components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\"	readonly type=\"hidden\"	name=\"total\"		value=\"".$total."\",	id=\"input-extrasmall\""]
				]
			]
		];
	@endphp
	@component("components.templates.outputs.form-details", ["modelTable" => $modelTable, "attributeExComment" => "name=\"note\" placeholder=\"Ingrese una nota\" cols=\"80\""]) @endcomponent
	@component("components.labels.title-divisor")CONDICIONES DE PAGO @endcomponent
	@component("components.containers.container-form")		
		<div class="col-span-2">
			@component("components.labels.label") Referencia/Número de factura (Opcional): @endcomponent
			@php
				isset($requests) ? $value = htmlentities($requests->purchaseRecord->reference) : $value = "";
			@endphp
			@component("components.inputs.input-text", ["classEx" => "new-input-text remove", "attributeEx" => "type=\"text\" name=\"referencePuchase\" placeholder=\"Ingrese una referencia\" value=\"".$value."\""]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Tipo de moneda: @endcomponent
			@php
				$options = collect();
				foreach(["MXN", "USD", "EUR", "Otro"] as $item)
				{
					$options = $options->concat(
					[
						[
							"value" => $item, 
							"description" => $item, 
							"selected" => ((isset($requests) && $item == $requests->purchaseRecord->typeCurrency) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options, "classEx" => "remove"])
				@slot("attributeEx")
					name="type_currency" data-validation="required"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Fecha de pago: @endcomponent
			@component("components.inputs.input-text", ["classEx" => "remove"])
				@slot("attributeEx")
					type="text" name="date" step="1" placeholder="Ingrese la fecha" readonly="readonly" id="datepicker" data-validation="required" @if(isset($requests)) value="{{ $requests->PaymentDate != "" ? date('d-m-Y',strtotime($requests->PaymentDate)) : '' }}" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Forma de pago: @endcomponent
			@php
				$options = collect();
				foreach(["Efectivo", "Cheque", "Transferencia", "TDC Empresarial"] as $item)
				{
					$options = $options->concat(
					[
						[
							"value" => $item, 
							"description" => $item, 
							"selected" => ((isset($requests) && $item == $requests->purchaseRecord->paymentMethod) ? "selected" : "")
						]
					]);
				}
			@endphp
			@component("components.inputs.select", ["options" => $options, "classEx" => "removeselect"])
				@slot("attributeEx")
					name="pay_mode" data-validation="required"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Estado de Factura: @endcomponent
			@php
				$options = collect();
				$selected = "No Aplica";
				$custom = false;
				if(isset($requests))
				{
					$r = $requests->purchaseRecord->billStatus;
					if($r && 
					(
						$r != "Pendiente"
						&&
						$r != "Entregado"
						&&
						$r !="No Aplica"))
					{
						$selected = $r;
						$custom = true;
					}
					if($r == "Pendiente" || $r == "")
						$selected = "Pendiente";
					if($r == "Entregado")
						$selected = "Entregado";
					if($r == "No Aplica")
						$selected = "No Aplica";
				}
				if($custom)
				{
					$options = $options->concat([["value" => $r, "description" => $r, "selected" => "selected"]]);
				}
				else
				{
					$options = $options->concat([[
						"value"			=> "No Aplica",
						"description"	=> "No Aplica",
						"selected"		=> ($selected == "No Aplica" ? "selected" : "")
					]]);
				}
				$options = $options->concat([[
					"value"			=> "Pendiente",
					"description"	=> "Pendiente",
					"selected"		=> ($selected == "Pendiente" ? "selected" : "")
				]]);
				$options = $options->concat([[
					"value"			=> "Entregado",
					"description"	=> "Entregado",
					"selected"		=> ($selected == "Entregado" ? "selected" : "")
				]]);
			@endphp
			@component("components.inputs.select", ["options" => $options, "classEx" => "js-ef remove"])
				@slot("attributeEx")
					name="status_bill" data-validation="required"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Importe pagado: @endcomponent
			@component("components.inputs.input-text", ["classEx" => "amount_total remove"])
				@slot("attributeEx")
					type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if(isset($requests)) value="{{ $requests->purchaseRecord->total }}" @endif
				@endslot
			@endcomponent
		</div>
	@endcomponent
	<div id="view-credit-cards" style="display: none;">
		@NotFound(["variant" => "note", "attributeEx" => "id=\"error_request\" role=\"alert\""]) Seleccione una tarjeta @endNotFound
		@php
			$modelHead = 
			[
				[
					["value" => "Acciones"],
					["value" => "Alías"],
					["value" => "Nombre en Tarjeta"],
					["value" => "Número en Tarjeta"],
					["value" => "Estado"],
					["value" => "Principal/Adicional"],
				]
			];
			$modelBody = [];
			if(isset($requests) && $requests->purchaseRecord->paymentMethod == "TDC Empresarial")
			{
				foreach(App\CreditCards::where('idAccAcc',$requests->purchaseRecord->idAccAccPayment)->where('assignment',$requests->idRequest)->where('idEnterprise',$requests->purchaseRecord->idEnterprisePayment)->get() as $t)
				{
					$status = $principal = '';
					switch ($t->status) 
					{
						case 1:
							$status = 'Vigente';
							break;
						case 2:
							$status = 'Bloqueada';
							break;
						case 3:
							$status = 'Cancelada';
							break;
						default:
							break;
					}

					switch ($t->principal_aditional) 
					{
						case 1:
							$principal = 'Principal';
							break;
						case 2:
							$principal = 'Adicional';
							break;
						default:
							break;
					}
					if($t->idcreditCard == $requests->purchaseRecord->idcreditCard)
					{
						$checked	= "checked";
						$class		= "marktr";
					}
					else
					{
						$class		= "";
						$checked	= "";
					}

					$body = 
					[
						"classEx" => $class,
						[
							"content" =>
							[
								[
									"kind"			=> "components.inputs.checkbox",
									"attributeEx"	=> "type=\"radio\" id=\"id_".$t->idcreditCard."\" name=\"idcreditCard\" value=\"".$t->idcreditCard."\" ".$checked,
									"classEx"		=> "checkbox"
								]
							]
						],
						[
							"content" =>
							[
								"label" => $t->alias
							]
						],
						[
							"content" =>
							[
								"label" => $t->name_credit_card
							]
						],
						[
							"content" =>
							[
								"label" => $t->credit_card
							]
						],
						[
							"content" =>
							[
								"label" => $status
							]
						],
						[
							"content" =>
							[
								"label" => $principal
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@Table(["attributeExBody" => "id=\"body-credit-cards\"", "modelHead" => $modelHead, "modelBody" => $modelBody]) @endTable
	</div>
	@ContainerForm(["classEx" => 'tr-input-condition'.((isset($requests) && $requests->purchaseRecord->paymentMethod == "TDC Empresarial") ? "" : " hidden")])
		<div class="col-span-2">
			@component('components.labels.label', ["label" => "Empresa:"])@endcomponent
			@component('components.inputs.input-text',["attributeEx" => "type=\"hidden\" readonly=\"readonly\" name=\"enterpriseid_payment_input\" value=\"".(isset($requests) ? $requests->purchaseRecord->idEnterprisePayment : '')."\""])@endcomponent
			@component('components.inputs.input-text',["classEx" => "new-input-text", "attributeEx" => "type=\"text\" placeholder=\"Ingrese la empresa\" readonly=\"readonly\" name=\"enterpriseName_payment_input\" value=\"".(isset($requests->purchaseRecord->idEnterprisePayment) ? App\Enterprise::find($requests->purchaseRecord->idEnterprisePayment)->name : '')."\""])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label', ["label" => "Clasificación de Gasto:"])@endcomponent
			@component('components.inputs.input-text',["attributeEx" => "type=\"hidden\" readonly=\"readonly\" name=\"accountid_payment_input\" value=\"".(isset($requests) ? $requests->purchaseRecord->idAccAccPayment : '')."\""])@endcomponent
			@component('components.inputs.input-text',["classEx" => "new-input-text", "attributeEx" => "type=\"text\" placeholder=\"Ingrese la clasificación del gasto\" readonly=\"readonly\" name=\"accountName_payment_input\" value=\"".(isset($requests->purchaseRecord->idAccAccPayment) ? App\Account::find($requests->purchaseRecord->idAccAccPayment)->account.'-'.App\Account::find($requests->purchaseRecord->idAccAccPayment)->description : '')."\""])@endcomponent
		</div>
	@endContainerForm
	@ContainerForm(["classEx" => 'tr-select-condition'.((isset($requests) && $requests->purchaseRecord->paymentMethod != "TDC Empresarial") ? "" : " hidden")])
		<div class="col-span-2">
			@component('components.labels.label', ["label" => "Empresa:"])@endcomponent
			@php
				$options = collect();
				foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
				{
					$options = $options->concat(
					[
						[
							'value' => $enterprise->id, 
							'description' => $enterprise->name, 
							"selected" => ((isset($requests) && $requests->purchaseRecord->idEnterprisePayment == $enterprise->id) ? 'selected' : '')
						]
					]);
				}
			@endphp
			@Select(["classEx" => "removeselect", "attributeEx" => "name=\"enterpriseid_payment_select\" data-validation=\"required\"", "options" => $options])@endSelect
		</div>
		<div class="col-span-2">
			@component('components.labels.label', ["label" => "Clasificación de Gasto:"])@endcomponent
			@php
				$options = collect();
				if(isset($requests))
				{
					foreach(App\Account::orderNumber()->where('selectable',1)->where('idEnterprise', $requests->purchaseRecord->idEnterprisePayment)->get() as $account)
					{
						$options = $options->concat(
						[
							[
								'value' => $account->idAccAcc, 
								'description' => $account->account." - ".$account->description." (".$account->content.")", 
								"selected" => ((isset($requests) && $account->idAccAcc==$requests->purchaseRecord->idAccAccPayment) ? 'selected' : '')
							]
						]);
					}
				}
			@endphp
			@Select(["classEx" => "removeselect", "attributeEx" => "name=\"accountid_payment_select\" data-validation=\"required\"", "options" => $options])@endSelect
		</div>
	@endContainerForm
	@component("components.labels.title-divisor") CARGAR DOCUMENTOS @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
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
	<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-10 mb-6">
		@component("components.buttons.button", ["variant" => "primary"])
			@slot("classEx")
				enviar
			@endslot
			@slot("attributeEx")
				type="submit"  name="enviar"
			@endslot
			ENVIAR SOLICITUD
		@endcomponent
		@component("components.buttons.button", ["variant" => "secondary"])
			@slot("classEx")
				save
			@endslot
			@slot("attributeEx")
				type="submit" id="save" name="save" formaction="{{ route('purchase-record.unsent') }}"
			@endslot
			GUARDAR SIN ENVIAR
		@endcomponent
		@component("components.buttons.button", ["variant" => "reset"])
			@slot("classEx")
				btn-delete-form
			@endslot
			@slot("attributeEx")
				type="reset" name="borra"
			@endslot
			Borrar campos
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
	$(document).ready(function()
	{
		validation();
		@ScriptSelect([ 'selects' => 
			[	
				[
					"identificator"          => "[name=\"areaid\"]", 
					"placeholder"            => "Selecciona la dirección", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"enterpriseid\"],[name=\"enterpriseid_payment_select\"]", 
					"placeholder"            => "Selecciona la empresa", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"departmentid\"]", 
					"placeholder"            => "Selecciona el departamento", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"pay_mode\"]", 
					"placeholder"            => "Selecciona el método de pago", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"type_currency\"]", 
					"placeholder"            => "Selecciona el tipo de moneda", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"unit\"]", 
					"placeholder"            => "Selecciona la unidad", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"status_bill\"]", 
					"placeholder"            => "Selecciona la unidad", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				]
			]
		])
		@endScriptSelect
		generalSelect({'selector':'[name="userid"]','model': 13});
		generalSelect({'selector':'[name="accountid"]','depends': '[name="enterpriseid"]','model': 10});
		generalSelect({'selector':'.js-projects','model': 21});
		generalSelect({'selector':'.js-code_wbs', 'depends':'[name="projectid"]', 'model':22});
		generalSelect({'selector':'[name="code_edt"]', 'depends':'[name="code_wbs"]', 'model':15});
		generalSelect({'selector':'[name="accountid_payment_select"]','depends':'[name="enterpriseid_payment_select"]','model':10});
		$('[name="price"],[name="amountAdditional"],[name="retentionAmount"]').on("contextmenu",function(e)
		{
			return false;
		});
		count = 0;
		countB = {{ $taxesCount }};
		$('.phone,.clabe,.account,.cp').numeric(false);    // números
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.retentionAmount',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional_exist','function'=>'amountConcept'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention','name' => 'retention_new','function'=>'amountConcept'])  @endcomponent
		$(function() 
		{
			$("#datepicker").datepicker({ dateFormat: "dd-mm-yy" });
			$(".datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
		});
		$(document).on('click','.help-btn',function()
		{
			swal('Ayuda','Al habilitar la edición, usted podrá modificar los campos del proveedor; si la edición permanece deshabilitada no se guardará ningún cambio en el mismo.','info');
		})
		.on('click','#save',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
		})
		.on('change','input[name="act_gas"]',function()
		{
			$("#condition").slideDown("slow").css({display:'flex'});
		})
		.on('change','input[name="fiscal"]',function()
		{
			//$("#form").slideDown("slow");
			if ($('input[name="fiscal"]:checked').val() == "1") 
			{
				$('.iva_kind').prop('disabled',false);
				$('#iva_no').prop('checked',true);
				$('.iva_kind').parent('p').stop(true,true).fadeIn();
				$('input[name=rfc]').attr('data-validation','rfc required server');
			}
			else if ($('input[name="fiscal"]:checked').val() == "0") 
			{
				$('.iva_kind').prop('disabled',true);
				$('#iva_no').prop('checked',true);
				$('.iva_kind').parent('p').stop(true,true).fadeOut();
				$('input[name=rfc]').removeAttr('data-validation','required');
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
					$('#form-prov').hide();
					$('#banks').hide();
					$('#buscar').hide();
					$('#not-found').stop().hide();
					$('#table-provider').stop().hide();
					$('.removeselect').val(null).trigger('change');
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','.quanty,.price,.iva_kind,.additional_existAmount,.retention_newAmount,.addiotional,.retention',function()
		{
			amountConcept();
		})
		.on('click','#add',function()
		{
			countConcept     = $('.countConcept').length;
			cant             = $('input[name="quantity"]').removeClass('error').val();
			unit             = $('[name="unit"] option:selected').removeClass('error').val();
			descr            = $('input[name="description"]').removeClass('error').val();
			precio           = $('input[name="price"]').removeClass('error').val();
			iva              = ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2             = ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivakind          = $('input[name="iva_kind"]:checked').val();
			ivaCalc          = 0;
			taxesConcept     = 0;
			retentionConcept = 0;
			if (cant == "" || cant == "" || descr == "" || precio == "" || precio == "" || unit == undefined)
			{
				if(cant=="")
				{
					$('input[name="quantity"]').addClass('error');
				}
				if(cant == "")
				{
					$('input[name="quantity"]').addClass('error');
					 
				}
				if(unit==undefined)
				{
					$('[name="unit"]').addClass('error');
				}
				if(descr=="")
				{
					$('input[name="description"]').addClass('error');
				}
				if(precio=="")
				{
					$('input[name="price"]').addClass('error');
				}
				if(precio == "")
				{
					$('input[name="price"]').addClass('error');
					 
				}
				swal('', 'Por favor llene todos los campos.', 'error');
			}
			else if (cant == 0 && precio == 0)
			{
				swal('','La cantidad y el precio unitario no pueden ser negativos, ni ceros', 'error');
				$('input[name="quantity"]').addClass('error');
				$('input[name="price"]').addClass('error');
				return false;
			}
			else if (cant == 0 || precio == 0)
			{
				if (cant == 0)
				{
					swal('','La cantidad no puede ser cero', 'error');
					$('input[name="quantity"]').addClass('error');
					return false;
				}
				else if (precio == 0)
				{
					swal('','El precio unitario no puede ser cero', 'error');
					$('input[name="price"]').addClass('error');
					return false;
				}
				return false;
			}
			else
			{
				switch($('input[name="iva_kind"]:checked').val())
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = cant*precio*iva;
						break;
					case 'b':
						ivaCalc = cant*precio*iva2;
						break;
				}
				nameAmounts = $('<div hidden></div>');
				$('.additional_existName').each(function(i,v)
				{
					nameAmount = $(this).val();
					nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+countB+'[]">').val(nameAmount));
				});
				amountsAA = $('<div hidden></div>');
				if($('input[name="additional_exist"]:checked').val() == 'si')
				{
					$('.additional_existAmount').each(function(i,v)
					{
						amountAA = $(this).val();
						amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+countB+'[]">').val(amountAA));
						taxesConcept = Number(taxesConcept) + Number(amountAA);
					});
				}
				nameRetentions = $('<div hidden></div>');
				$('.retention_newName').each(function(i,v)
				{
					name = $(this).val();
					nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="tnameretention'+countB+'[]">').val(name));
				});
				amountsRetentions = $('<div hidden></div>');
				if($('input[name="retention_new"]:checked').val() == 'si')
				{
					$('.retention_newAmount').each(function(i,v)
					{
						amountR = $(this).val();
						amountsRetentions.append($('<input type="hidden" class="num_amountRetention" name="tamountretention'+countB+'[]">').val(amountR));
						retentionConcept = Number(retentionConcept)+Number(amountR);
					});
				}
				if( ((cant*precio)+ivaCalc+taxesConcept-retentionConcept) < 0)
				{
					swal('', 'El importe no puede ser negativo.', 'error');
				}
				else 
				{
					countConcept = countConcept+1;
					@php
						$modelHead = 
						[
							[
								["value" => "#"],
								["value" => "Cantidad"],
								["value" => "Unidad"],
								["value" => "Descripción"],
								["value" => "Precio Unitario"],
								["value" => "IVA"],
								["value" => "Impuesto adicional"],
								["value" => "Retenciones"],
								["value" => "Importe"],
								["value" => "Acciones"],
							]
						];
						$modelBody = 
						[
							[
								"classEx" => "tr",
								[
									"classEx" => "countConcept",
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classCount"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classCant"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tquanty",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classUnit"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tunit",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tunit[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classTdescr"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tdescr",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classTprice"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tprice",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tprice[]\""
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tivakind",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classTiva"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tiva",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tiva[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classTaxesConcept"
										]
									]
								],
								[
									"classEx" => "taxes_class",
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classRetentionConcept"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "classTotal"
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table ttotal",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\""
										],
										[
											"kind"		 => "components.inputs.input-text",
											"classEx"	 => "input-table tamount",
											"attributeEx"=> "readonly=\"true\" type=\"hidden\" name=\"tamount[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"			=> "components.buttons.button",
											"classEx"		=> "edit-item",
											"attributeEx"	=> "type=\"button\" id=\edit\"",
											"variant"		=> "success",
											"label"			=> "<span class=\"icon-pencil\"></span>"
										],
										[
											"kind"			=> "components.buttons.button",
											"classEx"		=> "delete-item",
											"attributeEx"	=> "type=\"button\"",
											"variant"		=> "red",
											"label"			=> "<span class=\"icon-x\"></span>"
										]
									]
								]
							]
						];
						$table = view("components.tables.table",[
							"modelHead" 	  => $modelHead,
							"modelBody" 	  => $modelBody,
							"themeBody" 	  => "striped",
							"attributeExBody" => "id=\"body\"", 
							"noHead"		  => "true"
						])->render();
						$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr_table = $(table);
					tr_table.find('.taxes_class')
						.append(nameAmounts)
						.append(amountsAA)
						.append(nameRetentions)
						.append(amountsRetentions);
					tr_table.find('.classCount').text(countConcept);
					tr_table.find('.classCant').text(cant);
					tr_table.find('.tquanty').val(cant);
					tr_table.find('.classUnit').text(unit);
					tr_table.find('.tunit').val(unit);
					tr_table.find('.classTdescr').text(descr);
					tr_table.find('.tdescr').val(descr);
					tr_table.find('.ivakind').val(ivakind);
					tr_table.find('.classTprice').text("$"+Number(precio).toFixed(2));
					tr_table.find('.tprice').val(precio);
					tr_table.find('.classTiva').text("$"+Number(ivaCalc).toFixed(2));
					tr_table.find('.tiva').val(ivaCalc);
					tr_table.find('.classTaxesConcept').text("$"+Number(taxesConcept).toFixed(2));
					tr_table.find('.classRetentionConcept').text("$"+Number(retentionConcept).toFixed(2));
					tr_table.find('.classTotal').text("$"+Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept).toFixed(2));
					tr_table.find('.ttotal').val((cant*precio)+ivaCalc);
					tr_table.find('.tamount').val(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
					$('#body').append(tr_table);
					$('input[name="quantity"]').removeClass('error').val("");
					$('input[name="description"]').removeClass('error').val("");
					$('input[name="price"]').removeClass('error').val("");
					$('input[name="iva_kind"]').prop('checked',false);
					$('input[name="additional_exist"]').prop('checked',false);
					$('input[name="retention_new"]').prop('checked',false);
					$('#iva_no').prop('checked',true);
					$('#no_additional').prop('checked',true);
					$('#no_retention').prop('checked',true);
					$('input[name="amount"]').val("");
					$('[name="unit"]').val('').trigger('change');
					$('#newsImpuestos').empty();
					$('#newsRetention').empty();
					$('.nameAmount').val('');
					$('.amountAdditional').val('');
					$('.retentionName').val('');
					$('.retentionAmount').val('');
					$('#taxes_exist').stop(true,true).slideUp().hide();
					$('#retention_new').stop(true,true).slideUp().hide();
					additional_existCleanComponent();
					retention_newCleanComponent();
					total_cal();
					countB++;
				}
			}
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('.tr').remove();
			total_cal();
			countB = $('#body .tr').length;
			$('#body .tr').each(function(i,v)
			{
				$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
				$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
				$(this).find('.num_nameRetention').attr('name','tnameretention'+i+'[]');
				$(this).find('.num_amountRetention').attr('name','tamountretention'+i+'[]');
			});
			if($('.countConcept').length>0)
			{
				$('.countConcept').each(function(i,v)
				{
					$(this).html(i+1);
				});
			}
		})
		.on('click','.edit-item',function()
		{
			cant   = $('input[name="quantity"]').removeClass('error').val();
			unit   = $('[name="unit"] option:selected').removeClass('error').val();
			descr  = $('input[name="description"]').removeClass('error').val();
			precio = $('input[name="price"]').removeClass('error').val();
			if (cant == "" || descr == "" || precio == "" || unit == "") 
			{
				tquanty  = $(this).parents('.tr').find('.tquanty').val();
				tunit    = $(this).parents('.tr').find('.tunit').val();
				tdescr   = $(this).parents('.tr').find('.tdescr').val();
				tivakind = $(this).parents('.tr').find('.tivakind').val();
				tprice   = $(this).parents('.tr').find('.tprice').val();
				swal({
					title		: "Editar concepto",
					text		: "Al editar, se eliminarán los impuestos adicionales y retenciones agregados ¿Desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((continuar) =>
				{
					if(continuar)
					{
						if(tivakind == 'a')
						{
							$('#iva_a').prop("checked",true);
						}
						else if(tivakind == 'b')
						{
							$('#iva_b').prop("checked",true);
						}
						else
						{
							$('#iva_no').prop("checked",true);
						}
						$('input[name="quantity"]').val(tquanty);
						$('[name="unit"]').val(tunit).trigger('change');
						$('input[name="description"]').val(tdescr);
						$('input[name="price"]').val(tprice);
						
						$(this).parents('.tr').remove();
						total_cal();
						countB = $('#body .tr').length;
						$('#body .tr').each(function(i,v)
						{
							$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
							$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
							$(this).find('.num_nameRetention').attr('name','tnameretention'+i+'[]');
							$(this).find('.num_amountRetention').attr('name','tamountretention'+i+'[]');
						});
						if($('.countConcept').length>0)
						{
							$('.countConcept').each(function(i,v)
							{
								$(this).html(i+1);
							});
						}
					}
					else
					{
						swal.close();
					}
				});
			}
			else
			{
				swal('', 'Tiene un concepto sin agregar a la lista', 'error');
			}
		})
		.on('click','.checkbox',function()
		{
			$('.idchecked').val('0');
			$('.marktr').removeClass('marktr');
			$(this).parents('.tr').addClass('marktr');
			$(this).parents('.tr').find('.idchecked').val('1');
		})
		.on('click','#addDoc',function()
		{
			@php
				$options = collect();
				foreach (["Ticket","Factura","Otro"] as $kind)
				{
					$options = $options->concat([["value" => $kind, "description" => $kind]]);	
				}
				$newDoc = view('components.documents.upload-files',[					
						"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput" => " input-text pathActioner",
						"attributeExRealPath" => "name=\"realPath[]\"",
						"classExRealPath" => "path",
						"componentsExUp" => 
						[
							["kind" => "components.labels.label", "label" => "Tipo de documento:"],
							["kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"nameDocument[]\"  data-validation=\"required\"", "classEx" => "nameDocument mb-6"]
						],
						"componentsExDown" =>
						[
							["kind" => "components.labels.label", "label" => "Fecha:", "classEx" => "mt-4 hidden data_datepath"],
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"datepath[]\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\"", "classEx" => "hidden datepicker datepath my-2 data_datepath"],
							["kind" => "components.labels.label", "label" => "Hora:", "classEx" => "timepath hidden data_timepath"],
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Selecciona la Hora\" readonly=\"readonly\" data-validation=\"required\"", "classEx" => "timepath hidden my-2 data_timepath"],
							["kind" => "components.labels.label", "label" => "Folio Fiscal:", "classEx" => "folio_fiscal hidden data_folio"],
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\"", "classEx" => "folio_fiscal hidden my-2 data_folio"],
							["kind" => "components.labels.label", "label" => "Número de Ticket:", "classEx" => "ticket_number hidden data_ticket"],
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"num_ticket[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\"", "classEx" => "num_ticket hidden my-2 data_ticket"],
							["kind" => "components.labels.label", "label" => "Monto:", "classEx" => "amount_ticket hidden data_amount"],
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"monto[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\"", "classEx" => "monto hidden my-2 data_amount"],
						],
						"classExDelete" => "delete-doc",
					])->render();
			@endphp
			newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('#documents').append(containerNewDoc);
			$('[name="monto[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			validation();
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('#documents').removeClass('hidden');
			$('.timepath').daterangepicker({
				timePicker			: true,
				singleDatePicker	:true,
				timePicker24Hour	: true,
				timePickerIncrement	: 1,
				autoApply			: false,
				locale : 
				{
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
					"cancelLabel": "Cancelar",
				}
			}).on('show.daterangepicker', function (ev, picker) 
			{
				picker.container.find(".calendar-table").remove();
			});
			@ScriptSelect(
			[
				'selects' => 
				[
					[
						"identificator"          => ".nameDocument", 
						"placeholder"            => "Selecciona el tipo de documento", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			])
			@endScriptSelect
		})
		.on('change','.timepath',function()
		{
			$(this).daterangepicker({	
				timePicker : true,		 
				singleDatePicker:true,   
				timePicker24Hour : true, 
				locale : {
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
					"cancelLabel": "Cancelar",
				}
			}).on('show.daterangepicker', function (ev, picker){
				picker.container.find(".calendar-table").remove();
			});
		})
		.on('change','.folio_fiscal,.num_ticket,.timepath,.monto,.datepath',function()
		{
			main_folio = $('.main_folio').val();
			const array_folios = $('.folio_fiscal').serializeArray();
			const array_ticket = $('.num_ticket').serializeArray();

			folio 		= $(this).parents('.components-ex-down').find('.folio_fiscal').val().toUpperCase();
			num_ticket 	= $(this).parents('.components-ex-down').find('.num_ticket').val().toUpperCase();
			timepath 	= $(this).parents('.components-ex-down').find('.timepath').val();
			monto		= $(this).parents('.components-ex-down').find('.monto').val();
			datepath 	= $(this).parents('.components-ex-down').find('.datepath').val();

			object= $(this);

			if((datepath.length!==0&&folio.length!==0&&timepath.length!==0)||(num_ticket.length!==0&&timepath.length!==0&&monto.length!==0&&datepath.length!==0&&datepath.length!==0))
			{	
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("purchase-record.validationDocs") }}',
					data 		: {
							'fiscal_value'	:	folio,
							'num_ticket'	:	num_ticket,
							'timepath'		:	timepath,
							'monto'			:	monto,
							'datepath'		:	datepath,
						},
					success : function(data)
					{
						if(data==='false')
						{
							swal('','Este documento ya ha sido utilizado en otra solicitud.','error');				
							object.parents('.components-ex-down').find('.folio_fiscal').addClass('error').removeClass('valid').val('');
							object.parents('.components-ex-down').find('.num_ticket').addClass('error').removeClass('valid').val('');
							object.parents('.components-ex-down').find('.timepath').addClass('error').removeClass('valid').val('');
							object.parents('.components-ex-down').find('.monto').addClass('error').removeClass('valid').val('');
							object.parents('.components-ex-down').find('.datepath').addClass('error').removeClass('valid').val('');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})	
			}

			$('.datepath').each(function(i,v)
			{
				row          = 0;
				first_fiscal		= $(this).parents('.components-ex-down').find('.folio_fiscal');
				first_num_ticket	= $(this).parents('.components-ex-down').find('.num_ticket');
				first_monto			= $(this).parents('.components-ex-down').find('.monto');
				first_timepath		= $(this).parents('.components-ex-down').find('.timepath');
				first_datepath		= $(this).parents('.components-ex-down').find('.datepath');
				first_name_doc		= $(this).parents('.components-ex-down').find('.nameDocument option:selected').val();

				$('.datepath').each(function(j,v)
				{
					scnd_fiscal		= $(this).parents('.components-ex-down').find('.folio_fiscal');
					scnd_num_ticket	= $(this).parents('.components-ex-down').find('.num_ticket');
					scnd_monto		= $(this).parents('.components-ex-down').find('.monto');
					scnd_timepath	= $(this).parents('.components-ex-down').find('.timepath');
					scnd_datepath	= $(this).parents('.components-ex-down').find('.datepath');
					scnd_name_doc	= $(this).parents('.components-ex-down').find('.nameDocument option:selected').val();

					if (i!==j) 
					{
						if (first_name_doc == "Factura") 
						{
							if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_fiscal.val().toUpperCase() == scnd_fiscal.val().toUpperCase()) 
							{
								swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
								scnd_fiscal.val('').removeClass('valid').addClass('error');
								scnd_timepath.val('').removeClass('valid').addClass('error');
								scnd_datepath.val('').removeClass('valid').addClass('error');
								$(this).parents('.components-ex-down').find('span.form-error').remove();
								return;
							}
						}

						if (first_name_doc == "Ticket") 
						{
							if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_num_ticket.val().toUpperCase() == scnd_num_ticket.val().toUpperCase()) 
							{
								swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
								scnd_num_ticket.val('').addClass('error');
								scnd_timepath.val('').addClass('error');
								scnd_datepath.val('').addClass('error');
								$(this).parents('.components-ex-down').find('span.form-error').remove();
								return;
							}
						}
					}

				});
			});
		})
		.on('change','.nameDocument',function()
		{			
			$(this).parents('.components-ex-up').siblings('.components-ex-down').find('span.form-error').removeClass('help-block form-error').hide();
			var type_document = $('option:selected',this).val();
			switch(type_document)
			{
				case 'Factura': 
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_folio').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_datepath').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_timepath').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_ticket').hide();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_amount').hide();

					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.monto').removeClass('error valid').val('');

					break;
				case 'Ticket': 
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_folio').hide();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_datepath').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_timepath').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_ticket').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_amount').show();
					
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.monto').removeClass('error valid').val('');
					break;
				default : 
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_folio').hide();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_datepath').show();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_timepath').hide();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_ticket').hide();
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.data_amount').hide();	
					
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.monto').removeClass('error valid').val('');
					break;
			}
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('click','#help-btn-select-provider',function()
		{
			swal('Ayuda','En este apartado debe seleccionar un proveedor. De click en "Buscar" si va a tomar un proveedor ya existe. De click en "Nuevo" si desea agregar un proveedor en caso de no encontrarlo en el buscador.','info');
		})
		.on('click','#help-btn-account-bank',function()
		{
			swal('Ayuda','En este apartado debe seleccionar una cuenta bancaria del proveedor. De click en el icono que se encuentra al final de cada cuenta para seleccionarla.','info');
		})
		.on('click','#help-btn-dates',function()
		{
			swal('Ayuda','En este apartado debe agregar cada uno de los conceptos pertenecientes al pedido.','info');
		})
		.on('click','#help-btn-condition-pay',function()
		{
			swal('Ayuda','En este apartado debe agregar las condiciones de pago. Le recordamos que puede enviar su orden de compra sin factura en caso de no contar con ella y posteriormente cargarla.','info');
		})
		.on('change','.pathActioner',function(e)
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
					url			: '{{ url("/administration/purchase-record/upload") }}',
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
				url			: '{{ url("/administration/purchase-record/upload") }}',
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
				$('#documents').addClass('hidden');
			}
		})
		.on('change','select[name="enterpriseid_payment_select"]',function()
		{	
			$('select[name="accountid_payment_select"]').empty();
			$('#body-credit-cards').empty();
			$('#view-credit-cards').hide();
			generalSelect({'selector': '[name="accountid_payment_select"]','depends':'[name="enterpriseid_payment_select"]','model':10});
		})
		.on('change','select[name="pay_mode"]',function()
		{
			$('select[name="accountid_payment_select"]').empty();
			if ($(this).val() == '')
			{
				$('[name="enterpriseid_payment_input"]').val('');
				$('[name="enterpriseName_payment_input"]').val('');
				$('[name="accountid_payment_input"]').val('');
				$('[name="accountName_payment_input"]').val('');
				$('select[name="enterpriseid_payment_select"]').val(null).trigger('change');
				$('select[name="accountid_payment_select"]').val(null).trigger('change');
				$('.tr-input-condition').addClass('hidden');
				$('.tr-select-condition').addClass('hidden');
			}
			else if ($(this).val() == 'TDC Empresarial')
			{
				$('#body-credit-cards').empty();
				$('#view-credit-cards').show();
				$('.tr-input-condition').removeClass('hidden').addClass('block');
				$('.tr-select-condition').addClass('hidden').removeClass('block');
				request_id = $('[name="userid"] option:selected').val();
				$.ajax(
				{
					type : 'post',
					url  : '{{ route("purchase-record.credit-cards-data") }}',
					data : {'request_id':request_id},
					success : function(data)
					{
						$('#body-credit-cards').append(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#body-credit-cards').html('');
					}	
				});
			}
			else
			{
				enterprise 	= $('select[name="enterpriseid"] option:selected').val();
				account 	= $('select[name="accountid"] option:selected').val();
				$('select[name="enterpriseid_payment_select"]').val(enterprise).trigger('change');
				$('select[name="accountid_payment_select"]').val(account).trigger('change');
				$('.tr-input-condition').addClass('hidden').removeClass('block');
				$('.tr-select-condition').removeClass('hidden').addClass('block');
				@ScriptSelect(
				[
					'selects' =>
					[
						[
							"identificator"          => "[name=\"enterpriseid_payment_select\"]", 
							"placeholder"            => "Selecciona la empresa", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				])
				@endScriptSelect
				generalSelect({'selector':'[name="accountid_payment_select"]','depends':'[name="enterpriseid_payment_select"]','model':10});
			}
		})
		.on('click','[name="idcreditCard"]',function()
		{
			idEnterprise	= $(this).parents('.tr').find('.idEnterprise').val();
			idAccAcc		= $(this).parents('.tr').find('.idAccAcc').val();
			nameEnterprise	= $(this).parents('.tr').find('.nameEnterprise').val();
			nameAccount		= $(this).parents('.tr').find('.nameAccount').val();
			$('[name="enterpriseid_payment_input"]').val(idEnterprise);
			$('[name="enterpriseName_payment_input"]').val(nameEnterprise);
			$('[name="accountid_payment_input"]').val(idAccAcc);
			$('[name="accountName_payment_input"]').val(nameAccount);
		})
		.on('change','select[name="userid"]',function()
		{
			$('select[name="pay_mode"]').val(null).trigger('change');
		})
		.on('change','[name="projectid"]',function()
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
							$('.select_wbs').removeClass('hidden').addClass('block');
							generalSelect({'selector':'.js-code_wbs', 'depends':'[name="projectid"]', 'model':22});
						}
						else
						{
							$('[name="code_wbs"], [name="code_edt"]').html('');
							$('.select_wbs, [name="code_edt"]').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('[name="code_wbs"], [name="code_edt"]').html('');
				$('.select_wbs, [name="code_edt"]').removeClass('block').addClass('hidden');				
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
							$('.select_edt').removeClass('hidden').addClass('block');
							generalSelect({'selector':'[name="code_edt"]', 'depends':'[name="code_wbs"]', 'model':15});
						}
						else
						{
							$('[name="code_edt"]').html('');
							$('.select_edt, [name="code_edt"]').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('[name="code_edt"]').html('');
				$('.select_edt, [name="code_edt"]').removeClass('block').addClass('hidden');				
			}
		})
	});
	function validation()
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
				cant	= $('input[name="quantity"]').removeClass('error').val();
				unit	= $('[name="unit"] option:selected').removeClass('error').val();
				descr	= $('input[name="description"]').removeClass('error').val();
				precio	= $('input[name="price"]').removeClass('error').val();
				path	= $('.path').length;

				if(path>0)
				{
					pas=true;
					$('.path').each(function()
					{
						if($(this).val()=='')
						{
							swal('', 'Por favor cargue los documentos faltantes.', 'error');
							pas = false;
						}
					});
					if(!pas) return false;
				}

				if (cant != "" || descr != "" || precio != "" || unit != undefined) 
				{
					swal('', 'Tiene un concepto sin agregar', 'error');
					return false;
				}
				subtotal	= 0;
				iva			= 0;
				$("#body .tr").each(function(i, v)
				{
					tempQ		= $(this).find('.tquanty').val();
					tempP		= $(this).find('.tprice').val();
					subtotal	+= Number(tempQ)*Number(tempP);
					iva			+= Number($(this).find('.tiva').val());
				});
				total = (subtotal+iva);
				if(total<0)
				{
					swal('', 'El importe total no puede ser negativo', 'error');
					return false;
				}	
				if($('.request-validate').length>0)
				{
					prov		= $('#form-prov').is(':visible');
					conceptos	= $('#body .tr').length;
					if(conceptos>0)
					{
						if ($('select[name="pay_mode"] option:selected').val() == "TDC Empresarial") 
						{
							if($('#body-credit-cards .tr').length>0)
							{
								// aqui va la validación de la forma de pago if para saber si se guarda o no
								if ($('.checkbox').is(':checked')) 
								{
									swal("Cargando",{
										icon: '{{ asset(getenv('LOADING_IMG')) }}',
										button: false,
										closeOnClickOutside: false,
										closeOnEsc: false
									});
									return true;
								}
								else
								{
									swal('', 'Debe seleccionar una TDC Empresarial', 'error');
									return false;
								}
								
							}
							else
							{
								swal('', 'No hay Tarjetas de Crédito', 'error');
								return false;
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
						swal('', 'Debe ingresar al menos un concepto de pedido', 'error');
						return false;
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
		});
	}
	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountAA 	= 0;
		amountR 	= 0;
		//descuento	= Number($('input[name="descuento"]').val());
		$("#body .tr").each(function(i, v)
		{
			tempQ		= $(this).find('.tquanty').val();
			tempP		= $(this).find('.tprice').val();
			tempAA 		= null;
			tempR 		= null;
			$(".num_amountAdditional").each(function(i, v)
			{
				tempAA 		+= Number($(this).val());
			});
			$(".num_amountRetention").each(function(i, v)
			{
				tempR 		+= Number($(this).val());
			});
			
			//tempD		= $(this).find('.tdiscount').val();
			subtotal	+= (Number(tempQ)*Number(tempP));
			iva			+= Number($(this).find('.tiva').val());
			amountAA 	= Number(tempAA);
			amountR 	= Number(tempR);
		});
		total = (subtotal+iva + amountAA)-amountR;
		$('.subtotal_class').text('$ '+Number(subtotal).toFixed(2));
		$('.amount_tax_class').text('$ '+Number(amountAA).toFixed(2));
		$('.amount_ret_class').text('$ '+Number(amountR).toFixed(2));
		$('.iva_class').text('$ '+Number(iva).toFixed(2));
		$('.total_class').text('$ '+Number(total).toFixed(2));

		$('input[name="subtotal"]').val(Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val(Number(iva).toFixed(2));
		$('input[name="total"]').val(Number(total).toFixed(2));
		$(".amount_total").val(Number(total).toFixed(2));
		$('input[name="amountAA"]').val(Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val(Number(amountR).toFixed(2));
	}
	function amountConcept()
	{
		if(isNaN(Number($('input[name="quantity"]').val())))
		{
			cant = 0;
			$('input[name="quantity"]').val(0);
		}
		else
		{
			cant = Number($('input[name="quantity"]').val());
		}
		if(isNaN($('input[name="price"]').val()))
		{
			precio = 0;
			$('input[name="price"]').val(0);
		}
		else
		{
			precio = Number($('input[name="price"]').val());
		}
		iva          = ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
		iva2         = ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
		ivaCalc      = 0;
		taxAditional = 0;
		retention    = 0;
		$('.additional_existAmount').each(function()
		{ 
			if($(this).val())
			{
				tmpTax = Number($(this).val());
				if(isNaN(tmpTax))
				{
					tmpTax = 0;
					$(this).val(0);
				}
				taxAditional += tmpTax;
			} 
		});
		$('.retention_newAmount').each(function(){
			if($(this).val())
			{
				tmpRet = Number($(this).val());
				if(isNaN(tmpRet))
				{
					tmpRet = 0;
					$(this).val(0);
				}
				retention += tmpRet;
			} 
		});
		switch($('input[name="iva_kind"]:checked').val())
		{
			case 'no':
				ivaCalc = 0;
				break;
			case 'a':
				ivaCalc = cant*precio*iva;
				break;
			case 'b':
				ivaCalc = cant*precio*iva2;
				break;
		}
		totalImporte = ((cant * precio)+ivaCalc) + taxAditional - retention;
		$('input[name="amount"]').val(totalImporte.toFixed(2));
	}
</script>
@endsection
