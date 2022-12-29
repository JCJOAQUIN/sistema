@extends('layouts.child_module')

@section('data')
	@if(isset($globalRequests) && $globalRequests == true)
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
	$taxesCount = 0;
	$taxes 		= 0;
	$retentions = 0;
@endphp
{{-- Form::open(['route' => ['purchase-record.follow.update', $request->folio], 'method' => 'put', 'id' => 'container-alta','files' => true]) --}}
	@Form(["attributeEx" => "action=\"".route('purchase-record.follow.update', $request->folio)."\" method=\"POST\" id=\"container-alta\"", "files" => true, "methodEx" => "PUT"])
		@Title(["label" => "REGISTRO DE COMPRA"])@endTitle
		@php
			$elaborate = App\User::find($request->idElaborate);
		@endphp
		@component('components.labels.subtitle')
			Elaborado por: {{ $elaborate->name }} {{ $elaborate->last_name }} {{ $elaborate->scnd_last_name }}
		@endcomponent
		@ContainerForm([""])
			<div class="col-span-2">
				@Label(["label" => "Título:"])@endLabel
				@InputText(["classEx" => "removeselect", "attributeEx" => "type=\"text\" name=\"title\" placeholder=\"Ingrese el Título\" data-validation=\"required\" value=\"".(isset($request) ? htmlentities($request->purchaseRecord->title) : "")."\" ".(($request->status != 2) ? 'disabled' : '')])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Fecha:"])@endLabel
				@InputText(["classEx" => "removeselect datepicker2", "attributeEx" => "type=\"text\" name=\"datetitle\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" value=\"".(isset($request) && $request->purchaseRecord->datetitle!='' ? Carbon\Carbon::createFromFormat('Y-m-d', $request->purchaseRecord->datetitle)->format('d-m-Y') : "")."\" readonly=\"readonly\" ".(($request->status != 2) ? 'disabled' : '')])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Fiscal:"])@endLabel
				<div class="flex space-x-2">
					@ButtonApproval(["attributeEx" => "type=\"radio\" checked name=\"fiscal\" id=\"nofiscal\" value=\"0\" ".(isset($request) && $request->taxPayment==0 ? 'checked' : '')." ".(($request->status != 2) ? 'disabled' : ''), "label" => "Sí", "classExLabel" => ($request->status != 2) ? 'disabled' : ''])@endButtonApproval
					@ButtonApproval(["attributeEx" => "type=\"radio\" name=\"fiscal\" id=\"fiscal\" value=\"0\" ".(isset($request) && $request->taxPayment==1 ? 'checked' : '')." ".(($request->status != 2) ? 'disabled' : ''), "label" => "No", "classExLabel" => ($request->status != 2) ? 'disabled' : ''])@endButtonApproval
				</div>
			</div>
			<div class="col-span-2">
				@Label(["label" => "Número de Orden (Opcional):"])@endLabel
				@InputText(["classEx" => "removeselect", "attributeEx" => "type=\"text\" name=\"numberOrder\" placeholder=\"Ingrese el número de orden\" data-validation=\"required\" value=\"".(isset($request) ? htmlentities($request->purchaseRecord->numberOrder) : '')."\" ".(($request->status != 2) ? 'disabled' : '')])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Solicitante:"])@endLabel
				@php
					$options = collect();
					if($request->idRequest)
					{
						$options = collect([[
							"value"			=> $request->idRequest,
							"description"	=> $request->requestUser->fullName(),
							"selected"		=> "selected"
						]]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect", "attributeEx" => "name=\"userid\" multiple data-validation=\"required\" ".(($request->status != 2) ? 'disabled' : '')])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Empresa:"])@endLabel
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $enterprise->id,
								"description"	=> $enterprise->name,
								"selected"		=> ((isset($request) && $request->idEnterprise == $enterprise->id) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect", "attributeEx" => "name=\"enterpriseid\" data-validation=\"required\" ".(($request->status != 2) ? 'disabled' : '')])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Dirección:"])@endLabel
				@php
					$options = collect();
					foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $area->id,
								"description"	=> $area->name,
								"selected"		=> ((isset($request) && $request->idArea == $area->id) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect", "attributeEx" => "name=\"areaid\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Departamento:"])@endLabel
				@php
					$options = collect();
					foreach(App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->orderBy('name','asc')->get() as $department)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $department->id,
								"description"	=> $department->name,
								"selected"		=> ((isset($request) && $request->idDepartment == $department->id) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect", "attributeEx" => "name=\"departmentid\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Clasificación del Gasto:"])@endLabel
				@php
					$options = collect();
					if($request->account)
					{
						$options = collect([[
							"value"			=> $request->account,
							"description"	=> $request->accounts->first()->fullClasificacionName(),
							"selected"		=> "selected"
						]]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect", "attributeEx" => "name=\"accountid\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Proyecto:"])@endLabel
				@php
					$options = collect();
					foreach(App\Project::whereIn('status',[1,2])->orderBy('proyectName','asc')->get() as $project)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $project->idproyect,
								"description"	=> $project->proyectName,
								"selected"		=> ((isset($request) && $request->idProject == $project->idproyect) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect js-projects", "attributeEx" => "name=\"projectid\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2 select_wbs @if(!isset($request)) hidden @elseif(isset($request) && $request->idProject != '' && $request->requestProject->codeWBS()->exists()) @else hidden @endif" @if($request->status != 2) disabled="disabled" @endif>
				@Label(["label" => "WBS:"])@endLabel
				@php
					$options = collect();
					if(isset($request))
					{
						foreach(App\CatCodeWBS::where('project_id', $request->idProject)->whereIn('status',[1])->get() as $code)
						{
							$options = $options->concat(
							[
								[
									"value"			=> $code->id,
									"description"	=> $code->code_wbs,
									"selected"		=> (($request->code_wbs == $code->id) ? "selected" : "")
								]
							]);
						}
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect js-code_wbs", "attributeEx" => "name=\"code_wbs\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2 select_edt @if(!isset($request)) hidden @elseif(isset($request) && $request->code_wbs != '' && $request->code_edt != '' && $request->wbs->codeEDT()->exists()) @else hidden @endif" @if($request->status != 2) disabled="disabled" @endif>
				@Label(["label" => "EDT:"])@endLabel
				@php
					$options = collect();
					if(isset($request))
					{
						foreach(App\CatCodeEDT::where('codewbs_id', $request->code_wbs)->get() as $edt)
						{
							$options = $options->concat(
							[
								[
									"value"			=> $edt->id,
									"description"	=> $edt->code.' ('.$edt->description.')',
									"selected"		=> (($request->code_edt == $edt->id) ? "selected" : "")
								]
							]);
						}
					}
				@endphp
				@Select(["options" => $options, "classEx" => "removeselect js-code_edt", "attributeEx" => "name=\"code_edt\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Proveedor:"])@endLabel
				@InputText(["classEx" => "removeselect", "attributeEx" => "type=\"text\" name=\"provider\" placeholder=\"Ingrese el Proveedor\" data-validation=\"required\" value=\"".(isset($request) ? htmlentities($request->purchaseRecord->provider) : '')."\" ".(($request->status != 2) ? 'disabled' : '')])@endInputText
			</div>
		@endContainerForm
		@if($request->status == "2")
			@ContainerForm()
				<div class="col-span-2">
					@Label(["label" => "Cantidad:"])@endLabel
					@InputText(["classEx" => "quanty", "attributeEx" => "type=\"text\" name=\"quantity\" placeholder=\"Ingrese la cantidad\""])@endInputText
				</div>
				<div class="col-span-2">
					@Label(["label" => "Unidad:"])@endLabel
					@php
						$options = collect();
						foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
						{
							foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
							{
								$options = $options->concat(
								[
									[
										"value"			=> $child->description,
										"description"	=> $child->description
									]
								]);
							}
						}
					@endphp
					@Select(["options" => $options, "classEx" => "unit", "attributeEx" => "name=\"unit\" multiple"])@endSelect
				</div>
				<div class="col-span-2">
					@Label(["label" => "Descripción:"])@endLabel
					@InputText(["attributeEx" => "type=\"text\" name=\"description\" placeholder=\"Ingrese la descripción\""])@endInputText
				</div>
				<div class="col-span-2">
					@Label(["label" => "Precio Unitario:"])@endLabel
					@InputText(["classEx" => "price", "attributeEx" => "type=\"text\" name=\"price\" placeholder=\"Ingrese el precio\""])@endInputText
				</div>
				<div class="col-span-2 @if(isset($request) && $request->taxPayment == 0) hidden @endif">
					@Label(["label" => "Tipo de IVA:"])@endLabel
					<div class="flex space-x-2">
						@ButtonApproval(["label" => "No", "classEx" => "iva_kind ".(isset($globalRequests) ? "opacity-50" : ""), "attributeEx" => "title=\"No IVA\" name=\"iva_kind\" id=\"iva_no\" value=\"no\" placeholder=\"$0.00\" ".(isset($request) && $request->taxPayment == 0 ? "disabled" : "")])@endButtonApproval
						@ButtonApproval(["label" => "A", "classEx" => "iva_kind ".(isset($globalRequests) ? "opacity-50" : ""), "attributeEx" => "title=\"".App\Parameter::where('parameter_name','IVA')->first()->parameter_value."%\" name=\"iva_kind\" id=\"iva_a\" value=\"a\" placeholder=\"$0.00\" ".(isset($request) && $request->taxPayment == 0 ? "disabled" : "")])@endButtonApproval
						@ButtonApproval(["label" => "B", "classEx" => "iva_kind ".(isset($globalRequests) ? "opacity-50" : ""), "attributeEx" => "title=\"".App\Parameter::where('parameter_name','IVA2')->first()->parameter_value."%\" name=\"iva_kind\" id=\"iva_b\" value=\"b\" placeholder=\"$0.00\" ".(isset($request) && $request->taxPayment == 0 ? "disabled" : "")])@endButtonApproval
					</div>
				</div>
				<div class="col-span-2 md:col-span-4">
					@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional'])  @endcomponent
				</div>
				<div class="col-span-2 md:col-span-4">
					@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention'])  @endcomponent
				</div>
				<div class="col-span-2">
					@Label(["label" => "Importe"])@endLabel
					@InputText(["classEx" => "amount", "attributeEx" => "readonly type=\"text\" name=\"amount\" placeholder=\"Ingrese el importe\""])@endInputText
				</div>
				<div class="col-span-2 md:col-span-4 text-left">
					@Button(["classEx" => "add2", "attributeEx" => "type=\"button\" name=\"add\" id=\"add\"", "variant" => "warning", "label" => "<span class=\"icon-plus\"></span> Agregar concepto"])@endButton
				</div>
			@endContainerForm
		@endif
		@php
			$modelBody = [];
			foreach($request->purchaseRecord->detailPurchase as $key=>$detail)
			{
				$body =
				[
					[
						"content"	=>
						[
							"label" => $key+1
						]
					],
					[
						"content"	=>
						[
							["label"	=> $detail->unit],
							["kind"		=> "components.inputs.input-text", "classEx" => "tunit", "attributeEx" => "readonly type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\""]							
						]
					],
					[
						"content" =>
						[
							["kind"		=> "components.labels.label", "classEx" => "countConcept", "label" => $detail->quantity],
							["kind"		=> "components.inputs.input-text", "classEx" => "tquanty", "attributeEx" => "type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\""]							
						]
					],
					[
						"content" =>
						[
							["label"	=> htmlentities($detail->description)],
							["kind"		=> "components.inputs.input-text", "classEx" => "tdescr", "attributeEx" => "readonly type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\""],
							["kind"		=> "components.inputs.input-text", "classEx" => "tivakind", "attributeEx" => "readonly type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\""]							
						]
					],
					[
						"content" =>
						[
							["label"	=> "$ ".$detail->unitPrice],
							["kind"		=> "components.inputs.input-text", "classEx" => "tprice", "attributeEx" => "readonly type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\""]							
						]
					],
					[
						"content" =>
						[
							["label"	=> "$ ".$detail->tax],
							["kind"		=> "components.inputs.input-text", "classEx" => "tiva", "attributeEx" => "readonly type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\""]							
						]
					]
				];
				$taxesConcept	= 0;
				$inputsTd = ["label" => "$ 0.00"];
				
				foreach($detail->taxes as $tax)
				{
					$taxesConcept+=$tax->amount;
					$inputsTd	= 
					[
						["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"", "classEx" => "num_amountAdditional"],
						["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"".htmlentities($tax->name)."\"", "classEx" => "num_nameAmount"],
						["kind" => "components.labels.label", "label" => "$ ".number_format($taxesConcept,2)]
					];
				}
				$body[] = ["content" => $inputsTd];
				$retentionConcept	= 0;
				$inputsTd = ["label" => "$ 0.00"];
				foreach($detail->retentions as $ret)
				{
					$retentionConcept+=$ret->amount;
					$inputsTd	= 
					[
						["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"", "classEx" => "num_amountRetention"],
						["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"".htmlentities($ret->name)."\"", "classEx" => "num_nameRetention"],
						["kind" => "components.labels.label", "label" => "$ ".number_format($retentionConcept,2)]
					];
					$taxesCount++;
				}
				$body[] = ["content" => $inputsTd];
				$body[]	=
				[
					"content" =>
					[
						["kind" => "components.labels.label", "label" => "$ ".$detail->total],
						["kind" => "components.inputs.input-text", "classEx" => "tamount", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->total."\""]
					]
				];
				if($request->status == 2)
				{
					$body[]	=
					[
						"content" =>
						[
							["kind" => "components.buttons.button", "attributeEx" => "id=\"edit\" type=\"button\"", "classEx" => "edit-item", "label" => "<span class=\"icon-pencil\"></span>", "variant" => "success"],
							["kind" => "components.buttons.button", "classEx" => "delete-item", "label" => "<span class=\"icon-x\"></span>", "variant" => "red"]
						]
					];
				}
				$modelBody[] = $body;
			}
		@endphp
		@Table(
		[
			"modelHead"		=> 
			[
				[
					["value" => "#"],
					["value" => "Descripción"],
					["value" => "Cantidad"],
					["value" => "Unidad"],
					["value" => "Precio Unitario"],
					["value" => "IVA"],
					["value" => "Impuesto adicional"],
					["value" => "Retenciones"],
					["value" => "Importe"],
					["value" => "Acción"]
				]
			], 
			"modelBody"			=> $modelBody,
			"attributeEx"		=> "id=\"table\"",
			"attributeExBody"	=> "id=\"body\""
		])@endTable
		
		@FormDetails(
		[
			"modelTable" =>
			[
				[
					"label"		=> "Subtotal:", 
					"inputsEx"	=>
					[
						["kind" => "components.labels.label", "classEx" => "subtotal_class py-2", "label" => "$ ".($request->purchaseRecord->subtotal != "" ? $request->purchaseRecord->subtotal : "0.00")],
						["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"$0.00\" readonly type=\"hidden\" name=\"subtotal\" ".(isset($request) ? "value=\"".$request->purchaseRecord->subtotal."\"" : "")]
					]
				],
				[
					"label"		=> "Impuesto Adicional:", 
					"inputsEx"	=>
					[
						["kind" => "components.labels.label", "classEx" => "amount_tax_class py-2", "label" => "$ ".($request->purchaseRecord->amount_taxes != "" ? $request->purchaseRecord->amount_taxes : "0.00")],
						["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountAA\" ".(isset($request) ? "value=\"".$request->purchaseRecord->amount_taxes."\"" : "")]
					]
				],
				[
					"label"		=> "Retenciones:", 
					"inputsEx"	=>
					[
						["kind" => "components.labels.label", "classEx" => "amount_ret_class py-2", "label" => "$ ".($request->purchaseRecord->amount_retention != "" ? $request->purchaseRecord->amount_retention : "0.00")],
						["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountR\" ".(isset($request) ? "value=\"".$request->purchaseRecord->amount_retention."\"" : "")]
					]
				],
				[
					"label"		=> "IVA:", 
					"inputsEx"	=>
					[
						["kind" => "components.labels.label", "classEx" => "iva_class py-2", "label" => "$ ".($request->purchaseRecord->tax != "" ? $request->purchaseRecord->tax : "0.00")],
						["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"$0.00\" readonly type=\"hidden\" name=\"totaliva\" ".(isset($request) ? "value=\"".$request->purchaseRecord->tax."\"" : "")]
					]
				],
				[
					"label"		=> "TOTAL:", 
					"inputsEx"	=>
					[
						["kind" => "components.labels.label", "classEx" => "total_class py-2", "label" => "$ ".($request->purchaseRecord->total != "" ? $request->purchaseRecord->total : "0.00")],
						["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"$0.00\" readonly type=\"hidden\" name=\"total\" ".(isset($request) ? "value=\"".$request->purchaseRecord->total."\"" : "")]
					]
				]
			],
			"attributeExComment"	=> "name=\"note\" placeholder=\"Ingrese la nota\" cols=\"80\" ".($request->status != 2 ? "disabled" : ""),
			"textNotes"				=> (isset($request) ? $request->purchaseRecord->notes : "")
		])@endFormDetails
		@Title(["label" => "CONDICIONES DE PAGO"])@endTitle
		@ContainerForm()
			<div class="col-span-2">
				@Label(["label" => "Referencia\Número de factura (Opcional):"])@endLabel
				@InputText(["classEx" => "remove", "attributeEx" => "type=\"text\" name=\"referencePuchase\" placeholder=\"Ingrese una referencia\" ".(isset($request) ? "value=\"".htmlentities($request->purchaseRecord->reference)."\"" : "")." ".($request->status != 2 ? "disabled" : "")])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Tipo de moneda:"])@endLabel
				@php
					$options = collect();
					foreach(["MXN", "USD", "EUR", "Otro"] as $item)
					{
						$options = $options->concat(
							[
								[
									"value" => $item,
									"description" => $item,
									"selected" => (isset($request) && $request->purchaseRecord->typeCurrency == $item ? "selected" : "" )
								]
							]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "remove", "attributeEx" => "name=\"type_currency\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Fecha de pago:"])@endLabel
				@InputText(["classEx" => "remove", "attributeEx" => "type=\"text\" name=\"date\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" id=\"datepicker\" data-validation=\"required\" ".
				((isset($request) ? "value=\"".($request->PaymentDate != '' ? date('d-m-Y',strtotime($request->PaymentDate)) : "")."\"" : "")." ".($request->status != 2 ? "disabled" : ""))])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Forma de pago:"])@endLabel
				@php
					$options = collect();
					foreach(["Efectivo", "Cheque", "Transferencia", "TDC Empresarial"] as $item)
					{
						$options = $options->concat(
							[
								[
									"value" => $item,
									"description" => $item,
									"selected" => (isset($request) && $request->purchaseRecord->paymentMethod == $item ? "selected" : "" )
								]
							]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "remove", "attributeEx" => "name=\"pay_mode\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Estado de Factura:"])@endLabel
				@php
					$options	= collect();
					$selected	= "No Aplica";
					$custom		= false;
					if(isset($request))
					{
						$r = $request->purchaseRecord->billStatus;
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
						$options = $options->concat(
						[
							[
								"value"			=> $r, 
								"description"	=> $r, 
								"selected"		=> "selected"
							]
						]);
					}
					foreach(["Pendiente", "Entregado", "No Aplica"] as $item)
					{
						$options = $options->concat([["value" => $item, "description" => $item, "selected" => (($selected == $item) ? "selected" : "")]]);
					}
				@endphp
				@Select(["options" => $options, "classEx" => "js-ef removeselect",  "attributeEx" => "name=\"status_bill\" data-validation=\"required\" multiple ".(($request->status != 2) ? "disabled" : "")])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Importe Pagado:"])@endLabel
				@InputText(["classEx" => "amount_total remove", "attributeEx" => "type=\"text\" name=\"amount_total\" readonly placeholder=\"Ingrese el importe\" data-validation=\"required\" ".(isset($request) ? "value=\"".$request->purchaseRecord->total."\"" : "")." ".($request->status != 2 ? "disabled" : "")])@endInputText
			</div>
		@endContainerForm
		@php
			$modelBody = [];
			if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
			{
				foreach(App\CreditCards::where('assignment',$request->idRequest)->get() as $t)
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
					$body = 
					[
						"classEx" => (($t->idcreditCard == $request->purchaseRecord->idcreditCard) ? "marktr" : ""),
						[
							"content" 	=>
							[
								["kind"	=> "components.inputs.input-text", "classEx" => "idEnterprise", "attributeEx" => "type=\"hidden\" value=\"".$t->idEnterprise."\""],
								["kind"	=> "components.inputs.input-text", "classEx" => "idAccAcc", "attributeEx" => "type=\"hidden\" value=\"".$t->idAccAcc."\""],
								["kind"	=> "components.inputs.input-text", "classEx" => "nameEnterprise", "attributeEx" => "type=\"hidden\" value=\"".(App\Enterprise::find($t->idEnterprise)->name)."\""],
								["kind"	=> "components.inputs.input-text", "classEx" => "nameAccount", "attributeEx" => "type=\"hidden\" value=\"".(App\Account::find($t->idAccAcc)->account.'-'.App\Account::find($t->idAccAcc)->description)."\""],
								["kind" => "components.inputs.checkbox", "radio" => true, "classExLabel" => (isset($globalRequests) ? "disabled" : ""), "classEx" => "checkbox", "attributeEx" => "id=\"id_".$t->idcreditCard."\" type=\"radio\" name=\"idcreditCard\" value=\"".$t->idcreditCard."\"". ($t->idcreditCard == $request->purchaseRecord->idcreditCard ? "checked" : "")." ".($request->status != 2 ? "disabled" : "") , "label" => "<span class=\"icon-check\"></span>"]
							]
						],
						[						
							"content" 	=>
							[
								["label" => $t->alias],
							]
						],
						[
							"content" =>
							[
								["label" => $t->name_credit_card],
							]
						],
						[
							"content" =>
							[
								["label" => $t->credit_card],
							]
						],
						[
							"content" =>
							[
								["label" => $status],
							]
						],
						[
							"content" =>
							[
								["label" => $principal],
							]
						]
					];				
					$modelBody[] = $body;
				}
			}
		@endphp
		<div id="view-credit-cards" @if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial") style="display: block;" @else style="display: none;" @endif>
			@if (!isset($globalRequests)) @NotFound(["variant" => "note", "attributeEx" => "id=\"error_request\" role=\"alert\""]) Seleccione una tarjeta @endNotFound @endif
			@Table(
			[
				"modelHead" => 
				[
					[
						["value" => "Acción"],
						["value" => "Alias"],
						["value" => "Nombre en Tarjeta"],
						["value" => "Número de Tarjeta"],
						["value" => "Estado"],
						["value" => "Principal/Adicional"]
					]
				],
				"modelBody"			=> $modelBody,
				"attributeExBody"	=> "id=\"body-credit-cards\""
			])@endTable
		</div>
		@ContainerForm(["classEx" => "tr-input-condition".(isset($request) && $request->purchaseRecord->paymentMethod == 'TDC Empresarial' ? "" : " hidden")])
			<div class="col-span-2">
				@Label(["label" => "Empresa:"])@endLabel
				@InputText(["attributeEx" => "type=\"hidden\" readonly=\"readonly\" name=\"enterpriseid_payment_input\" ".(isset($request) ? "value=\"".$request->purchaseRecord->idEnterprisePayment."\"" : "")])@endInputText
				@InputText(["attributeEx" => "type=\"text\" readonly=\"readonly\" name=\"enterpriseName_payment_input\" ".(isset($request) ? "value=\"".($request->purchaseRecord->idEnterprisePayment != '' ? App\Enterprise::find($request->purchaseRecord->idEnterprisePayment)->name : '')."\"": '')." ".(isset($globalRequests) ? "disabled" : '')])@endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Clasificación de Gasto:"])@endLabel
				@InputText(["attributeEx" => "type=\"hidden\" readonly=\"readonly\" name=\"accountid_payment_input\" ".(isset($request) ? "value=\"".$request->purchaseRecord->idAccAccPayment."\"" : "")])@endInputText
				@InputText(["attributeEx" => "type=\"text\" readonly=\"readonly\" name=\"accountName_payment_input\" ".(isset($request) ? "value=\"".($request->purchaseRecord->idAccAccPayment!='' ? App\Account::find($request->purchaseRecord->idAccAccPayment)->account.'-'.App\Account::find($request->purchaseRecord->idAccAccPayment)->description : '')."\"" : '')." ".(isset($globalRequests) ? "disabled" : "")])@endInputText
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
				@Select(["classEx" => "removeselect", "attributeEx" => "name=\"enterpriseid_payment_select\" data-validation=\"required\"".(isset($globalRequests) ? " disabled" : ""), "options" => $options])@endSelect
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
				@Select(["classEx" => "removeselect", "attributeEx" => "name=\"accountid_payment_select\" data-validation=\"required\"".(isset($globalRequests) ? " disabled" : ""), "options" => $options])@endSelect
			</div>
		@endContainerForm
		@Title(["label" => "DOCUMENTOS CARGADOS"])@endTitle
		@php
			if(count($request->purchaseRecord->documents)>0)
			{
				$modelBody = [];
				$modelHead = ["Documento", "Fecha"];
				foreach($request->purchaseRecord->documents as $doc)
				{
					$body = 
					[
						[
							"content" =>
							[
								["kind" => "components.buttons.button", "buttonElement" => "a", "variant" => "secondary", "attributeEx" => "target=\"_blank\" href=\"".url('docs/purchase-record/'.$doc->path)."\" ", "label" => "Archivo"]
							]
						],
						[
							"content" =>
							[
								["kind" => "components.labels.label", "label" => ($doc->date)->format('d-m-y h:i')]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@if(count($request->purchaseRecord->documents)>0)
			@AlwaysVisibleTable(
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"variant"	=> "default"
			])
			@endAlwaysVisibleTable
		@else
			@NotFound(["attributeEx" => "id=\"error_request\" role=\"alert\"", "text" => "NO HAY DOCUMENTOS"])@endNotFound
		@endif
		@Title(["label" => "CARGAR DOCUMENTO"])@endTitle
		@ContainerForm()
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="documents"></div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@Button(["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\" ".($request->status == 1 ? "disabled" : ""), "label" => "<span class=\"icon-plus\"></span> Agregar documento"])@endButton
				@if($request->status != 2)
					@Button(["attributeEx" => "type=\"submit\" name=\"send\" formaction=\"".route('purchase-record.updatebill', $request->folio)."\" ".($request->status == 1 ? "disabled" : ""), "label" => "ACTUALIZAR INFORMACIÓN"])@endButton
				@endif
			</div>
		@endContainerForm
		@if($request->idCheck != '')
			@Title(["label" => "DATOS DE REVISIÓN"])@endTitle
			@php
				if($request->idEnterpriseR!="")
				{
					$reviewAccount = App\Account::find($request->accountR);
					$modelTable = 
					[
						"Revisó"					=> $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
						"Nombre de la Empresa"		=> App\Enterprise::find($request->idEnterpriseR)->name,
						"Nombre de la Dirección"	=> $request->reviewedDirection->name,
						"Nombre del Departamento"	=> App\Department::find($request->idDepartamentR)->name,
						"Clasificación de Gasto"	=> (isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description : "No hay"),
						"Nombre del Proyecto"		=> $request->reviewedProject->proyectName,
					];
				}
				$modelTable["Comentarios"] = (($request->checkComment == "") ? "Sin comentarios" : htmlentities($request->checkComment));
			@endphp
			@TableDetailSingle(
			[
				"modelTable" => $modelTable
			])@endTableDetailSingle
			@if($request->idEnterpriseR!="")
				@Title(["label" => "ETIQUETAS ASIGNADAS"])@endTitle
				@php
					$modelBody = [];
					foreach($request->purchaseRecord->detailPurchase as $detail)
					{
						$description = "";
						foreach($detail->labels as $label)
						{
							$description .= $label->label->description.", ";
						}
						$body = 
						[
							[
								"content" =>
								[
									["label" => $detail->quantity." ".$detail->unit]
								]
							],
							[
								"content" =>
								[
									["label" => $detail->description]
								]
							],
							[
								"content" =>
								[
									["label" => (($detail->labels()->exists() ? $description : "---"))]
								]
							]
						];
					}
				@endphp
				@AlwaysVisibleTable(
				[
					"modelHead" => ["Cantidad","Descripción","Etiquetas"],
					"modelBody" => $modelBody,
					"variant"	=> "default"
				])@endAlwaysVisibleTable
			@endif
		@endif
		@if($request->idAuthorize != "")
			@Title(["label" => "DATOS DE AUTORIZACIÓN"])@endTitle
			@TableDetailSingle(
			[
				"modelTable" =>
				[
					"Autorizó"		=> $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
					"Comentarios"	=> (($request->authorizeComment == "") ? "Sin comentarios" : htmlentities($request->authorizeComment))
				]
			])
			@endTableDetailSingle
		@endif
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-10 mb-6">
			@if($request->status == "2")
				@Button(["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "ENVIAR SOLICITUD"])@endButton
				@Button(["variant" => "secondary", "attributeEx" => "type=\"submit\" name=\"save\" id=\"save\" formaction=\"".route('purchase-record.follow.updateunsent', $request->folio)."\"", "label" => "GUARDAR SIN ENVIAR"])@endButton
			@endif
			@Button(["buttonElement" => "a", "variant" => "reset", "attributeEx" => "href=\"".(isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url))."\"", "classEx" => "load-actioner", "label" => "REGRESAR"])@endButton
		</div>
	@endForm
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
		@ScriptSelect(
		[
			'selects' => 
			[
				[
					"identificator"          => "[name=\"areaid\"]", 
					"placeholder"            => "Seleccione la dirección", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"enterpriseid\"]", 
					"placeholder"            => "Seleccione la empresa", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"departmentid\"]", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"enterpriseid_payment_select\"]", 
					"placeholder"            => "Seleccione la empresa", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"accountid_payment_select\"]", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"pay_mode\"]", 
					"placeholder"            => "Seleccione la forma de pago", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"type_currency\"]", 
					"placeholder"            => "Seleccione el tipo de moneda", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-ef", 
					"placeholder"            => "Seleccione el estado de factura", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"unit\"]", 
					"placeholder"            => "Seleccione la unidad", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"code_wbs\"]", 
					"placeholder"            => "Seleccione el código wbs", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"code_edt\"]", 
					"placeholder"            => "Seleccione el código edt", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				]
			]
		])@endScriptSelect
		validation();
		generalSelect({'selector': '[name="userid"]', 'model':13});
		generalSelect({'selector': '[name="accountid"]', 'depends': '[name="enterpriseid"]', 'model': 10});
		generalSelect({'selector': '[name="accountid_payment_select"]', 'depends': '[name="enterpriseid_payment_select"]', 'model': 10});
		generalSelect({'selector': '.js-projects', 'model': 21});
		generalSelect({'selector': '.js-code_wbs', 'depends':'.js-projects', 'model':22});
		generalSelect({'selector':'[name="code_edt"]', 'depends':'[name="code_wbs"]', 'model':15});
		@component('components.scripts.taxes',['type'=>'taxes', 'name' => 'additional','function'=>'amountConcept'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention', 'name' => 'retention','function'=>'amountConcept'])  @endcomponent
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
		$(function() 
		{
			$('.timepath').daterangepicker({
					timePicker : true,
					singleDatePicker:true,
					timePicker24Hour : true,
					timePickerIncrement : 1,
					autoApply: false,
					locale : {
						format : 'HH:mm',
						"applyLabel": "Seleccionar",
        				"cancelLabel": "Cancelar",
					}
				}).on('show.daterangepicker', function (ev, picker) 
				{
					picker.container.find(".calendar-table").remove();
				});
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
		.on('change','.quanty,.price,.iva_kind,.additionalAmount,.retentionAmount,.additional,.retention',function()
		{
			amountConcept();
		})
		.on('click','#add',function()
		{
			countConcept		= $('.countConcept').length;
			cant				= $('input[name="quantity"]').removeClass('error').val();
			unit				= $('[name="unit"] option:selected').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			iva					= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2				= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivakind 			= $('input[name="iva_kind"]:checked').val();
			ivaCalc				= 0;
			taxesConcept 		= 0;
			retentionConcept 	= 0;
			if (cant == "" || cant == 0 || descr == "" || precio == "" || precio == 0 || unit == "")
			{
				if(cant=="")
				{
					$('input[name="quantity"]').addClass('error');
				}
				if(cant == 0)
				{
					$('input[name="quantity"]').addClass('error');
					swal("", "La cantidad debe ser mayor a cero.", "error");
					return false;
				}
				if(unit=="")
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
				if(precio == 0)
				{
					$('input[name="price"]').addClass('error');
					swal("", "El precio debe ser mayor a cero.", "error");
					return false;
				}
				swal('', 'Por favor llene todos los campos.', 'error');
			}
			else if (cant == 0 && precio == 0)
			{
				swal('','La cantidad y el precio unitario no pueden ser cero', 'error');
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
				nameAmounts = $('<div></div>');
				$('.additionalName').each(function(i,v)
				{
					nameAmount = $(this).val();
					nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+countB+'[]">').val(nameAmount));
				});
				amountsAA = $('<div></div>');
				if($('input[name="additional"]:checked').val() == 'si')
				{
					$('.additionalAmount').each(function(i,v)
					{
						amountAA = $(this).val();
						amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+countB+'[]">').val(amountAA));
						taxesConcept = Number(taxesConcept) + Number(amountAA);
					});
				}
				nameRetentions = $('<div></div>');
				$('.retentionName').each(function(i,v)
				{
					name = $(this).val();
					nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="tnameretention'+countB+'[]">').val(name));
				});
				amountsRetentions = $('<div></div>');
				if($('input[name="retention"]:checked').val() == 'si')
				{
					$('.retentionAmount').each(function(i,v)
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
						$table = view("components.tables.table",
						[
							"modelHead" => 
							[
								[
									["value" => "#"],
									["value" => "Descripción"],
									["value" => "Cantidad"],
									["value" => "Unidad"],
									["value" => "Precio Unitario"],
									["value" => "IVA"],
									["value" => "Impuesto adicional"],
									["value" => "Retenciones"],
									["value" => "Importe"],
									["value" => "Acción"]
								]
							],
							"modelBody" => 
							[
								[
									"classEx" => "tr",
									[
										"classEx" => "classTaxes",
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "countConcept"],
											["kind" => "components.inputs.input-text", "classEx" => "tivakind", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tdescrClass"],
											["kind" => "components.inputs.input-text", "classEx" => "tdescr", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "cantClass"],
											["kind" => "components.inputs.input-text", "classEx" => "tquanty", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "unitClass"],
											["kind" => "components.inputs.input-text", "classEx" => "tunit", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tunit[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tpriceClass"],
											["kind" => "components.inputs.input-text", "classEx" => "tprice", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tprice[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tivaClass"],
											["kind" => "components.inputs.input-text", "classEx" => "tiva", "attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tiva[]\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tTaxesConcept"],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tRetentionConcept"],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.labels.label", "classEx" => "tTotal"],
											["kind" => "components.inputs.input-text", "classEx" => "ttotal", "attributeEx" => "readonly=\"true\" type=\"hidden\""],
											["kind" => "components.inputs.input-text", "classEx" => "tamount", "attributeEx" => "name=\"tamount\" readonly=\"true\" type=\"hidden\""],
										]
									],
									[
										"content" =>
										[
											["kind" => "components.buttons.button", "attributeEx" => "id=\"edit\" type=\"button\"", "variant" => "success", "classEx" => "edit-item", "label" => "<span class=\"icon-pencil\"></span>"],
											["kind" => "components.buttons.button", "attributeEx" => "type=\"button\"", "variant" => "red", "classEx" => "delete-item", "label" => "<span class=\"icon-x delete-span\"></span>"]
										]
									]
								]
							],
							"themeBody"			=> "striped",
							"attributeExBody"	=> "id=\"body\"", 
							"noHead"		    => "true"
						])->render();
						$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp
					tr_table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					table = $(tr_table);
					table.find('.countConcept').text(countConcept);
					table.find('.classTaxes').append(nameAmounts);
					table.find('.classTaxes').append(amountsAA);
					table.find('.classTaxes').append(nameRetentions);
					table.find('.classTaxes').append(amountsRetentions);
					table.find('.tivakind').val(ivakind);
					table.find('.tunit').val(unit);
					table.find('.tcant').val(cant);
					table.find('.tdescr').val(descr);
					table.find('.tprecio').val(precio);
					table.find(".cantClass").text(cant);
					table.find(".unitClass").text(unit);
					table.find(".tdescrClass").text(descr);
					table.find(".tTaxesConcept").text('$ '+Number(taxesConcept).toFixed(2));
					table.find(".tRetentionConcept").text('$ '+Number(retentionConcept).toFixed(2));
					table.find(".tpriceClass").text('$ '+Number(precio).toFixed(2));
					table.find(".tivaClass").text('$ '+Number(ivaCalc).toFixed(2));
					table.find('.tiva').val(ivaCalc);
					table.find('.tTotal').text('$ '+Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept).toFixed(2));
					table.find('.ttotal').val(((cant*precio)+ivaCalc));
					table.find('.tamount').val(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
					$('#body').append(table);
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
			cant				= $('input[name="quantity"]').removeClass('error').val();
			unit				= $('[name="unit"] option:selected').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			if (cant == "" || descr == "" || precio == "" || unit == "") 
			{
				tquanty		= $(this).parents('.tr').find('.tquanty').val();
				tunit		= $(this).parents('.tr').find('.tunit').val();
				tdescr		= $(this).parents('.tr').find('.tdescr').val();
				tivakind	= $(this).parents('.tr').find('.tivakind').val();
				tprice		= $(this).parents('.tr').find('.tprice').val();

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
							["kind" => "components.inputs.input-text", "attributeEx" => "name=\"datepath[]\" placeholder=\"Ingrese la Fecha\" readonly=\"readonly\" data-validation=\"required\"", "classEx" => "hidden datepicker datepath my-2 data_datepath"],
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
						"placeholder"            => "Seleccione el tipo de documento", 
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

			folio 		= $(this).parents('.docs-p').find('.folio_fiscal').val().toUpperCase();
			num_ticket 	= $(this).parents('.docs-p').find('.num_ticket').val().toUpperCase();
			timepath 	= $(this).parents('.docs-p').find('.timepath').val();
			monto		= $(this).parents('.docs-p').find('.monto').val();
			datepath 	= $(this).parents('.docs-p').find('.datepath').val();
			object= $(this);
			if((datepath.length!==0&&folio.length!==0&&timepath.length!==0)||(num_ticket.length!==0&&timepath.length!==0&&monto.length!==0&&datepath.length!==0&&datepath.length!==0))
			{	
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("purchase-record.validationDocs") }}',
					data 		: 
					{
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
							object.parents('.docs-p').find('.folio_fiscal').addClass('error').removeClass('valid').val('');
							object.parents('.docs-p').find('.num_ticket').addClass('error').removeClass('valid').val('');
							object.parents('.docs-p').find('.timepath').addClass('error').removeClass('valid').val('');
							object.parents('.docs-p').find('.monto').addClass('error').removeClass('valid').val('');
							object.parents('.docs-p').find('.datepath').addClass('error').removeClass('valid').val('');
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
				first_fiscal		= $(this).parents('.docs-p').find('.folio_fiscal');
				first_num_ticket	= $(this).parents('.docs-p').find('.num_ticket');
				first_monto			= $(this).parents('.docs-p').find('.monto');
				first_timepath		= $(this).parents('.docs-p').find('.timepath');
				first_datepath		= $(this).parents('.docs-p').find('.datepath');
				first_name_doc		= $(this).parents('.docs-p').find('.nameDocument option:selected').val();

				$('.datepath').each(function(j,v)
				{

					scnd_fiscal		= $(this).parents('.docs-p').find('.folio_fiscal');
					scnd_num_ticket	= $(this).parents('.docs-p').find('.num_ticket');
					scnd_monto		= $(this).parents('.docs-p').find('.monto');
					scnd_timepath	= $(this).parents('.docs-p').find('.timepath');
					scnd_datepath	= $(this).parents('.docs-p').find('.datepath');
					scnd_name_doc	= $(this).parents('.docs-p').find('.nameDocument option:selected').val();

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
								$(this).parents('.docs-p').find('span.form-error').remove();
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
			uploadedName 	= $(this).parent('.uploader-content').find('.path');
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
					url			: '{{ route("purchase-record.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
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
			uploadedName	= $(this).parents('.docs-p').find('.path');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("purchase-record.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parent('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parent('.docs-p').remove();
				}
			});
			$(this).parents('div.docs-p').remove();
			if($("#documents").html()=="")
			{
				$('.send').removeAttr('disabled');
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
							"placeholder"            => "Seleccione la empresa", 
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
							generalSelect({'selector':'.js-code_wbs', 'depends':'.js-projects', 'model':22});
						}
						else
						{
							$('.js-code_wbs, [name="code_edt"]').html('');
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
							generalSelect({'selector':'.js-code_edt', 'depends':'[name="code_wbs"]', 'model':15});
						}
						else
						{
							$('.js-code_edt').html('');
							$('.select_edt, .js-code_edt').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('.js-code_edt').html('');
				$('.select_edt, .js-code_edt').removeClass('block').addClass('hidden');				
			}
		})
	});
	function validation(){
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

				@if($request->status == 2)
					if (cant != "" || descr != "" || precio != "" || unit != undefined) 
					{
						swal('', 'Tiene un concepto sin agregar', 'error');
						return false;
					}
				@endif
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
