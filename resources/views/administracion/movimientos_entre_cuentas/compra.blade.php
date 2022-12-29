@extends('layouts.child_module')

@section('data')
	@php
		$taxesCount = $taxesCountBilling = 0;
		$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	<div id="form-purchase">
		@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('movements-accounts.purchase.store')."\"", "files" => true])
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				FORMULARIO DE COMPRAS INTER-EMPRESAS
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Título: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="title" placeholder="Ingrese un título" data-validation="required" @if(isset($requests)) value="{{ $requests->purchaseEnterprise->first()->title }}" @endif
						@endslot
						@slot('classEx')
							generalInput removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="datetitle" @if(isset($requests)) value=" {{ Carbon\Carbon::createFromFormat('Y-m-d',$requests->purchaseEnterprise->first()->datetitle)->format('d-m-Y') }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
						@endslot
						@slot('classEx')
							generalInput removeselect datepicker2
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fiscal: @endcomponent
					<div class="flex p-0 space-x-2">
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								id="nofiscal" name="fiscal" checked="checked" value="0" @if(isset($requests) && $requests->taxPayment==0) checked="checked" @endif
							@endslot
							@slot('classExContainer')
								inline-flex
							@endslot
							No
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') id="fiscal" name="fiscal" value="1" @if(isset($requests) && $requests->taxPayment==1) checked @endif @endslot
							@slot('classExContainer') inline-flex @endslot
							Sí
						@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Número de Orden (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="numberOrder" placeholder="Ingrese el número de Orden" @if(isset($requests)) value="{{ $requests->purchaseEnterprise->first()->numberOrder }}" @endif
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Solicitante: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && $requests->idRequest != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->requestUser->id,	"description"	=>	$requests->requestUser->name,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="userid" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-users removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE ORIGEN (COMPRA)
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionsEnterprise	=	[];
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if (isset($requests) && $requests->purchaseEnterprise->first()->idEnterpriseOrigin == $enterprise->id)
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
							name="enterpriseid_origin" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprises-origin removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
						$optionsArea	=	[];
						foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if (isset($requests) && $requests->purchaseEnterprise->first()->idAreaOrigin == $area->id)
							{
								$optionsArea[]	=
								[
									"value"			=>	$area->id,
									"description"	=>	$area->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsArea[]	=
								[
									"value"			=>	$area->id,
									"description"	=>	$area->name,
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsArea])
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
						$optionsDepartment	=	[];
						foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if (isset($requests) && $requests->purchaseEnterprise->first()->idDepartamentOrigin == $department->id)
							{
								$optionsDepartment[]	=
								[
									"value"			=>	$department->id,
									"description"	=>	$department->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsDepartment[]	=
								[
									"value"			=>	$department->id,
									"description"	=>	$department->name,
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsDepartment])
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
						if (isset($requests) && $requests->purchaseEnterprise->first()->idAccAccOrigin != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->purchaseEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$requests->purchaseEnterprise->first()->accountOrigin->account." - ".$requests->purchaseEnterprise->first()->accountOrigin->description." (".$requests->purchaseEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							multiple="multiple" name="accountid_origin"  data-validation="required"
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
						if (isset($requests) && $requests->purchaseEnterprise->first()->idProjectOrigin != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->purchaseEnterprise->first()->projectOrigin->idproyect,	"description"	=>	$requests->purchaseEnterprise->first()->projectOrigin->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="projectid_origin" multiple="multiple"  data-validation="required"
						@endslot
						@slot('classEx')
							js-projects-origin removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DESTINO (INGRESO)
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options	=	[];
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if (isset($requests) && $requests->purchaseEnterprise->first()->idEnterpriseDestiny == $enterprise->id)
							{
								$options[]	=
								[
									"value"			=>	 $enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$options[]	=
								[
									"value"			=>	 $enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="enterpriseid_destination" multiple="multiple" data-validation="required"
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
						if (isset($requests) && $requests->purchaseEnterprise->first()->idAccAccDestiny != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->purchaseEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$requests->purchaseEnterprise->first()->accountDestiny->account." - ".$requests->purchaseEnterprise->first()->accountDestiny->description." (".$requests->purchaseEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
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
						if (isset($requests) && $requests->purchaseEnterprise->first()->idProjectDestiny != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->purchaseEnterprise->first()->projectDestiny->idproyect,	"description"	=>	$requests->purchaseEnterprise->first()->projectDestiny->proyectName,	"selected"	=>	"selected"]]);
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
			@if(isset($requests) && $requests->purchaseEnterprise->first()->idEnterpriseDestiny != "")
				@if(count(App\BanksAccounts::where('idEnterprise',$requests->purchaseEnterprise->first()->idEnterpriseDestiny)->get()) > 0)	
					<div class="resultbank">
						@component('components.labels.title-divisor')
							@slot('classEx')
								mt-12
							@endslot
							SELECCIONE UNA CUENTA
						@endcomponent
						@php
							$modelHead	=	[];
							$body		=	[];
							$modelBody	=	[];
							$modelHead	=
							[
								[
									["value"	=>	""],
									["value"	=>	"Banco"],
									["value"	=>	"Alias"],
									["value"	=>	"Cuenta"],
									["value"	=>	"CLABE"],
									["value"	=>	"Sucursal"],
									["value"	=>	"Referencia"],
									["value"	=>	"Moneda"],
									["value"	=>	"Convenio"],
								]
							];
							foreach(App\BanksAccounts::where('idEnterprise',$requests->purchaseEnterprise->first()->idEnterpriseDestiny)->get() as $bank)
							{
								$checked	=	"";
								if($requests->purchaseEnterprise->first()->idbanksAccounts == $bank->idbanksAccounts) 
								{
									$checked	=	"checked";
								}
								$body	=
								[
									[
										"content"	=>
										[
											[
												"kind"			=> "components.inputs.checkbox",
												"attributeEx" 	=> "id=\"idBA$bank->idbanksAccounts\" type=\"radio\" name=\"idbanksAccounts\" value=\"".$bank->idbanksAccounts."\""." ".$checked,
												"classEx"		=> "checkbox",
												"id"			=> "idEmp$bank->idEmployee",
												"classExLabel"	=> "check-small request-validate",
												"label"			=> '<span class="icon-check"></span>',
												"radio"			=>	true
											]
										]
									],
									[
										"content"	=>	["label"	=>	$bank->bank->description]
									],
									[
										"content"	=>	["label"	=>	$bank->alias != "" ? $bank->alias : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->account != "" ? $bank->account : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->clabe != "" ? $bank->clabe : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->branch != "" ? $bank->branch : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->reference != "" ? $bank->reference : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->currency != "" ? $bank->currency : "---"]
									],
									[
										"content"	=>	["label"	=>	$bank->agreement != "" ? $bank->agreement : "---"]
									]
								];
								$modelBody[]	=	$body;
							}
						@endphp
						@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
							@slot('classEx')
								mt-4 
							@endslot
							@slot('attributeEx')
								id="table2"
							@endslot
							@slot('attributeExBody')
								id="banks-body"
							@endslot
						@endcomponent
					</div>
				@else
					@component('components.labels.not-found', ["variant" => "alert"])
						NO HAY CUENTAS REGISTRADAS PARA ESTA EMPRESA
					@endcomponent
				@endif
			@else
				<div class="resultbank hidden"></div>
			@endif
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE LA COMPRA
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Cantidad: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="quantity" placeholder="Ingrese la cantidad"
						@endslot
						@slot('classEx')
							generalInput quanty
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Unidad: @endcomponent
					@php
						$optionsUnit	=	[];
						foreach (App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
						{
							foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
							{
								$optionsUnit[]	=	["value"	=>	$child->description,	"description"	=>	$child->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsUnit])
						@slot('attributeEx')
							name="unit" multiple="multiple"
						@endslot
						@slot('classEx')
							unit form-control
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Descripción: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="description" placeholder="Ingrese la descripción"
						@endslot
						@slot('classEx')
						generalInput
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Precio Unitario: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="price" placeholder="Ingrese el precio"
						@endslot
						@slot('classEx')
							generalInput price
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Tipo de IVA: @endcomponent
					<div class="flex row mb-4 space-x-2">
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="iva_kind" id="iva_no" value="no" checked="" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
								@endslot
								@slot('classEx')
									iva_kind
								@endslot
								@slot('label')
									No
								@endslot
								@slot('attributeExLabel')
								title="No IVA"
								@endslot
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="iva_kind" id="iva_a" value="a" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
								@endslot
								@slot('classEx')
									iva_kind
								@endslot
								@slot('label')
									A
								@endslot
								@slot('attributeExLabel')
									title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
								@endslot
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="iva_kind" id="iva_b" value="b" @if(isset($requests) && $requests->taxPayment == 0) disabled @endif
								@endslot
								@slot('classEx')
									iva_kind
								@endslot
								@slot('label')
									B
								@endslot
								@slot('attributeExLabel')
									title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
								@endslot
							@endcomponent
						</div>
					</div>
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional'])  @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention'])  @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							readonly type="text" name="amount" placeholder="Ingrese el importe"
						@endslot
						@slot('classEx')
							generalInput amount
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							type="button" name="add" id="add"
						@endslot
						@slot('classEx')
							add2
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar concepto</span>
					@endcomponent
				</div>
				@slot("attributeEx")
					id="container-data"
				@endslot
			@endcomponent
			<div class="form-container">
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
							["value"	=>	"Impuesto adicional"],
							["value"	=>	"Retenciones"],
							["value"	=>	"Importe"],
							["value"	=>	"Acciones"],
						]
					];
					if (isset($requests))
					{
						foreach($requests->purchaseEnterprise->first()->detailPurchaseEnterprise as $key=>$detail)
						{
							$taxesConcept		=	0;
							$retentionConcept	=	0;
							$componentsExt		=	[];
							foreach ($detail->taxes as $tax)
							{
								$componentsAmount[]	=
								[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"",
										"classEx"		=>	"num_amountAdditional"
								];
								$componentsAmount[]	=
								[
									
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"".$tax->name."\"",
										"classEx"		=>	"num_nameAmount"
								];
								$taxesConcept+=$tax->amount;
							}
							$componentsAmount[]	=	["label"	=>	"$ ".number_format($taxesConcept,2)];
							foreach ($detail->retentions as $ret)
							{
								$componentsRetention[]	=
								[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"",
										"classEx"		=>	"num_amountRetention"
									
								];
								$componentsRetention[]	=
								[
									
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"".$ret->name."\"",
										"classEx"		=>	"num_nameRetention"
								];
								$retentionConcept+=$ret->amount;
							}
							$componentsRetention[]	=	["label"	=>	"$ ".number_format($retentionConcept,2)];
							$body	=
							[
								"classEx" => "tr_body",
								[									
									"content"	=>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	$key+1,
											"classEx"	=>	"countConcept"
										]
									],
								],
								[
									"content"	=>
									[
										["label"	=>	$detail->quantity],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
											"classEx"		=>	"tquanty"
										]
									],
								],
								[
									"content"	=>
									[
										["label"	=>	$detail->unit],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\"",
											"classEx"		=>	"tunit"
										]
									],
								],
								[
									"content"	=>
									[
										["label"	=>	$detail->description],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".$detail->description."\"",
											"classEx"		=>	"tdescr"
										],
										[
											"kind"	=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
											"classEx"		=>	"tivakind"
										]
									],
								],
								[
									"content"	=>
									[
										["label"	=>	"$ ".$detail->unitPrice],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
											"classEx"		=>	"tprice"
										]
									],
								],
								[
									"content"	=>
									[
										["label"	=>	"$ ".$detail->tax],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
											"classEx"		=>	"tiva"
										]
									],
								],
								[
									"content"	=>	$componentsAmount
								],
								[
									"content"	=>	$componentsRetention
								],
								[
									"content"	=>
									[
										["label"	=>	"$ ".$detail->amount],
										[
											"kind"	=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"". $detail->amount."\"",
											"classEx"		=>	"tamount"
										]
									],
								],
								[
									"content"	=>
									[
										[
											"kind"			=>	"components.buttons.button",
											"variant"		=>	"success",
											"attributeEx"	=>	" id=\"edit\" type=\"button\"",
											"classEx"		=>	"edit-item",
											"label"			=>	"<span class=\"icon-pencil\"></span>"
										],
										[
											"kind"		=>	"components.buttons.button",
											"variant"	=>	"red",
											"label"		=>	"<span class=\"icon-x delete-span\"></span>",
											"classEx"	=>	"delete-item",
											"attributeEx"	=>	"type=\"button\""
										]
									],
								],
							];
							$taxesCount++;
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
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent
			</div>
			<div class="totales2">
				@php
					$valueSubtotal	=	"";
					$valueIva		=	"";
					$valueTotal		=	"";
					if (isset($requests))
					{
						$valueSubtotal	=	"$ ".number_format($requests->purchaseEnterprise->first()->subtotales,2,".",",");
					}
					if (isset($requests))
					{
						foreach ($requests->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
						{
							foreach ($detail->taxes as $tax)
							{
								$taxes += $tax->amount;
							}
						}
					}
					if (isset($requests))
					{
						foreach ($requests->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
						{
							foreach ($detail->retentions as $ret)
							{
								$retentions += $ret->amount;
							}
						}
						$valueIva	=	"$ ".number_format($requests->purchaseEnterprise->first()->tax,2,".",",");
						$valueTotal	=	"$ ".number_format($requests->purchaseEnterprise->first()->amount,2,".",",");
					}
					$modelTable	=
					[
						["label"	=>	"Subtotal:",			"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"		=>	"py-2 subtotalLabel", "label"	=>	$valueSubtotal!="" ? $valueSubtotal : "$ 0.00"],
								["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"type=\"hidden\" name=\"subtotal\" value=\"".$valueSubtotal."\""],
							]
						],
						["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"		=>	"py-2 amountAALabel", "label"	=>	$taxes!="" ? "$ ".number_format($taxes,2) : "$ 0.00"],
								["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"type=\"hidden\" name=\"amountAA\" value=\"$ ".number_format($taxes,2)."\""],
							]
						],
						["label"	=>	"Retenciones:",			"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"		=>	"py-2 amountRLabel", "label"	=>	$retentions!="" ? "$ ".number_format($retentions,2) : "$ 0.00"],
								["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"type=\"hidden\" name=\"amountR\" value=\"$".number_format($retentions,2)."\""],
							]
						],
						["label"	=>	"IVA:",					"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"		=>	"py-2 totalivaLabel", "label"	=>	$valueIva!="" ? $valueIva : "$ 0.00"],
								["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"type=\"hidden\" name=\"totaliva\" value=\"".$valueIva."\""],
							]
						],
						["label"	=>	"TOTAL:",				"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"		=>	"py-2 totalLabel", "label"	=>	$valueTotal!="" ? $valueTotal : "$ 0.00"],
								["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"type=\"hidden\" name=\"total\" value=\"".$valueTotal."\" id=\"input-extrasmall\""],
							]
						],
					];
				@endphp
				@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
			</div>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12 totales
				@endslot
				CONDICIONES DE PAGO
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
					@php
						$options		=	collect();
						$currencyData	=	['MXN','USD','EUR','Otro'];
						foreach ($currencyData as $currency)
						{
							if (isset($requests) && $requests->purchaseEnterprise->first()->typeCurrency  == $currency)
							{
								$options	=	$options->concat([["value"	=>	$currency, "description"	=>	$currency, "selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$currency, "description"	=>	$currency]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							multiple="multiple" name="type_currency" data-validation="required"
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha de Pago: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="date" step="1" placeholder="Ingrese la fecha" data-validation="required" readonly="readonly" id="datepicker"
						@endslot
						@slot('classEx')
						generalInput remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Forma de Pago: @endcomponent
					@php
						$options		=	collect();
						foreach (App\PaymentMethod::orderName()->get() as $method)
						{
							if (isset($requests) && isset($requests->purchaseEnterprise->first()->idpaymentMethod) && $requests->purchaseEnterprise->first()->idpaymentMethod == $method->idpaymentMethod)
							{
								$options	=	$options->concat([["value"	=>	$method->idpaymentMethod,	"description"	=>	$method->method,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$method->idpaymentMethod,	"description"	=>	$method->method]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							multiple="multiple" name="pay_mode" data-validation="required"
						@endslot
						@slot('classEx')
							js-form-pay removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe a pagar: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if(isset($requests)) value="{{ $requests->purchaseEnterprise->first()->amount }}" @endif
						@endslot
						@slot('classEx')
							amount_total remove
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				CARGAR DOCUMENTOS
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							type="button" name="addDoc" id="addDoc"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar documento</span>
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@component('components.buttons.button', ["variant" => "primary"])
					@slot('attributeEx')
						type="submit"  name="enviar" value="ENVIAR SOLICITUD"
					@endslot
					@slot('classEx')
						enviar
					@endslot
					ENVIAR SOLICITUD
				@endcomponent
				@component('components.buttons.button', ["variant" => "secondary"])
					@slot('attributeEx')
						type="submit" id="save" name="save" value="GUARDAR SIN ENVIAR" formaction="{{ route('movements-accounts.purchase.unsent') }}"
					@endslot
					@slot('classEx')
						save
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
				@component('components.buttons.button', ["variant" => "reset"])
					@slot('attributeEx')
						type="reset" name="borra" value="Borrar campos"
					@endslot
					@slot('classEx')
						btn-delete-form
					@endslot
					Borrar campos
				@endcomponent
			</div>
		@endcomponent
	</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
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
					cant	= $('input[name="quantity"]').removeClass('error').val();
					unit	= $('[name="unit"] option:selected').removeClass('error').val();
					descr	= $('input[name="description"]').removeClass('error').val();
					precio	= $('input[name="price"]').removeClass('error').val();
					if (cant != "" || descr != "" || precio != "" || unit != undefined) 
					{
						swal('', 'Tiene un concepto sin agregar', 'error');
						return false;
					}
					subtotal	= 0;
					iva			= 0;
					descuento	= Number($('input[name="descuento"]').val());
					$("tr_body").each(function(i, v)
					{
						tempQ		= $(this).find('.tquanty').val();
						tempP		= $(this).find('.tprice').val();
						subtotal	+= Number(tempQ)*Number(tempP);
						iva			+= Number($(this).find('.tiva').val());
					});
					total = (subtotal+iva)-descuento;
					if(total<0)
					{
						swal('', 'El importe total no puede ser negativo', 'error');
						return false;
					}	
					if($('.request-validate').length>0)
					{
						conceptos	= $('.tr_body').length;
						path		= $('.path').length;
						if($('[name="pay_mode"] option:selected').val() == "1" && !$('[name="idbanksAccounts"]').is(':checked'))
						{
							swal('', 'Debe seleccionar una cuenta bancaria.', 'error');
							return false;
						}
						else if(path>0)
						{
							pas = true;
							$('.path').each(function()
							{
								if($(this).val()=='')
								{
									pas = false;
								}
							});
							if(!pas)
							{
								swal('', 'Por favor cargue los documentos faltantes.', 'error');
								return false;
							}
						}
						else if(conceptos>0)
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
							swal('', 'Debe ingresar al menos un concepto de pedido.', 'error');
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
		validate();
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional','function'=>'total_cal'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention','name' => 'retention','function'=>'total_cal'])  @endcomponent
		$('[name="price"],[name="additionalAmount"],[name="retentionAmount"],[name="amountTotal"]').on("contextmenu",function(e)
		{
			return false;
		});
		count			=	0;
		countB			=	{{ $taxesCount }};
		countBilling	=	{{ $taxesCountBilling }};
		$('.phone,.clabe,.account,.cp').numeric(false);
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false});
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.additionalAmount,.amountAdditional_billing,.retentionAmount,.sretentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
		$(function() 
		{
			$("#datepicker, .datepicker2").datepicker({  dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-projects-origin', 'model': 21});
		generalSelect({'selector': '.js-projects-destination', 'model': 21});
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 18});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 32});
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
				[
					"identificator"				=> ".js-form-pay",
					"placeholder"				=> "Seleccione una forma de pago",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".unit",
					"placeholder"				=> "Seleccione uno",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> "[name=\"type_currency\"]",
					"placeholder"				=> "Seleccione el tipo de moneda",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('click','#save',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
		})
		.on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
			
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
			
		})
		.on('click','#addDoc',function()
		{
			@php
				
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExInput"			=>	"docInput pathActioner",
					"classExDelete"			=>	"delete-doc",
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc = $(newDoc);
			$('#documents').append(containerNewDoc);
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$('#documents').removeClass('hidden');
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('change','.docInput.pathActioner',function(e)
		{
			filename		=	$(this);
			uploadedName	=	$(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
			extention		=	/\.jpg|\.png|\.jpeg|\.pdf/i;
			
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
					url			: '{{ route("movements-accounts.upload") }}',
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
				url			: '{{ route("movements-accounts.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('div.docs-p').remove();

			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		})
		.on('change','.quanty,.price,.iva_kind,.additionalAmount, .retentionAmount',function()
		{
			cant	= $('input[name="quantity"]').val();
			precio	= $('input[name="price"]').val();
			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc	= 0;
			taxes = 0;$('.additionalAmount').each(function(i,v)//amounts
			{
				taxes = taxes + Number($(this).val());
			});
			retentions = 0;$('.retentionAmount').each(function(i,v)//retentions
			{
				retentions = retentions + Number($(this).val());
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
			if(taxes=="")
			{
				taxes=0;
			}
			else
			{
				taxes = parseFloat(taxes);
			}
			totalImporte    = ((cant * precio)+ivaCalc)+taxes;
			$('input[name="amount"]').val(totalImporte.toFixed(2));
			if(retentions=="")
			{
				retentions = 0;
			}
			else
			{
				retentions = parseFloat(retentions);
			}
			if(retentions>totalImporte)
			{
				swal('','El total no puede ser negativo','error');
				return false;
			}
			else
			{
				totalImporte    = ((cant * precio)+ivaCalc)-retentions;
				$('input[name="amount"]').val(totalImporte.toFixed(2));
			}
			if(taxes=="" && retentions=="")
			{
				taxes=0; 
				retentions=0;
			}
			else
			{
				taxes = parseFloat(taxes); 
				retentions= parseFloat(retentions);
			}	
			totalImporte    = ((cant * precio)+ivaCalc)+taxes-retentions;
			$('input[name="amount"]').val(totalImporte.toFixed(2));
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

			if (cant == "" || descr == "" || precio == "" || unit == undefined)
			{
				if(cant=="")
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
				swal('','Por favor llene todos los campos.','error');
			}
			else if($('[name="amount"]').val() == "NaN")
			{
				$('[name="amount"]').addClass('error');
				swal('','Por favor verifique los montos ingresados.','error');
			}
			else if( cant <= 0)
			{
				swal('','La cantidad no puede ser menor o igual a 0.','error');
			}
			else if( precio <= 0)
			{
				swal('','El precio unitario no puede ser menor o igual a 0.','error');
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
					@php
						$input = view('components.inputs.input-text',[
							"classEx" => "tnameamount",
							"attributeEx" => "type=hidden"
						])->render();
					@endphp
					input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
					row_nameAmount = $(input);
					row_nameAmount.attr('name', 'tnameamount'+countB+'[]')
					row_nameAmount.val(nameAmount)
					nameAmounts.append(row_nameAmount);
				});
				amountsAA = $('<div></div>');
				if($('input[name="additional"]:checked').val() == 'si')
				{
					$('.additionalAmount').each(function(i,v)
					{
						amountAA = $(this).val();
						@php
							$input = view('components.inputs.input-text',[
								"classEx" => "num_amountAdditional",
								"attributeEx" => "type=hidden"
							])->render();
						@endphp
						input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
						row_amountAA = $(input);
						row_amountAA.attr('name', 'tamountadditional'+countB+'[]')
						row_amountAA.val(amountAA)
						amountsAA.append(row_amountAA);
						taxesConcept = Number(taxesConcept) + Number(amountAA);
					});
				}
				nameRetentions = $('<div></div>');
				$('.retentionName').each(function(i,v)
				{
					name = $(this).val();
					@php
						$input = view('components.inputs.input-text',[
							"classEx" => "num_nameRetention",
							"attributeEx" => "type=hidden"
						])->render();
					@endphp
					input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
					row_nameRetentions = $(input);
					row_nameRetentions.attr('name', 'tnameretention'+countB+'[]')
					row_nameRetentions.val(name)
					nameRetentions.append(row_nameRetentions);
				});

				amountsRetentions = $('<div></div>');
				if($('input[name="retention"]:checked').val() == 'si')
				{
					$('.retentionAmount').each(function(i,v)
					{
						amountR = $(this).val();
						@php
							$input = view('components.inputs.input-text',[
								"classEx" => "num_amountRetention",
								"attributeEx" => "type=hidden"
							])->render();
						@endphp
						input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
						row_amountsRetentions = $(input);
						row_amountsRetentions.attr('name', 'tamountretention'+countB+'[]')
						row_amountsRetentions.val(amountR)
						amountsRetentions.append(row_amountsRetentions);
						retentionConcept = Number(retentionConcept)+Number(amountR);
					});
				}

				total = Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
				if(Number(total) <= 0)
				{
					swal("","El total no puede ser menor o igual a cero.","error");
					return false;
				}
				else
				{	
					countConcept = countConcept+1;
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
								["value"	=>	"Impuesto adicional"],
								["value"	=>	"Retenciones"],
								["value"	=>	"Importe"],
								["value"	=>	"Acciones"],
							]
						];
						$body	=
						[
							"classEx"	=>	"tr_body",
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"		=>	"components.labels.label",
										"label"		=>	"",
										"classEx"	=>	"countConcept"
									]
								],
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tQuantyTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\"",
										"classEx"		=>	"tquanty"
									]
								],
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tUnitTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\"",
										"classEx"		=>	"tunit"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tDescrTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescr[]\"",
										"classEx"		=>	"tdescr"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tivakind[]\"",
										"classEx"		=>	"tivakind"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tPriceTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\"",
										"classEx"		=>	"tprice"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tIvaTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
										"classEx"		=>	"tiva"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tAditionalTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional[]\"",
										"classEx"		=>	"num_amountAdditional"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount[]\"",
										"classEx"		=>	"num_nameAmount"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tRetentionTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tamountretention[]\"",
										"classEx"		=>	"num_amountRetention"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"tnameretention[]\"",
										"classEx"		=>	"num_nameRetention"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"tAmountTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"readonly=\true\" type=\"hidden\" name=\"tamount[]\"",
										"classEx"		=>	"tamount"
									]
								],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"success",
										"attributeEx"	=>	" id=\"edit\" type=\"button\"",
										"classEx"		=>	"edit-item",
										"label"			=>	"<span class=\"icon-pencil\"></span>"
									],
									[
										"kind"		=>	"components.buttons.button",
										"variant"	=>	"red",
										"label"		=>	"<span class=\"icon-x delete-span\"></span>",
										"classEx"	=>	"delete-item"
									]
								],
							],
						];
						$modelBody[]	=	$body;
						$table = view('components.tables.table',["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"])->render();
					@endphp
					table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row		=	$(table);

					row.find('.countConcept').text(countB+1);
					row.find('.tQuantyTxt').text(cant);
					row.find('.tquanty').val(cant);
					row.find('.tUnitTxt').text(unit);
					row.find('.tunit').val(unit);
					row.find('.tDescrTxt').text(descr);
					row.find('.tdescr').val(descr);
					row.find('.tivakind').val(ivakind);
					row.find('.tPriceTxt').text('$ '+precio);
					row.find('.tprice').val(precio);
					row.find('.tIvaTxt').text('$ '+ivaCalc);
					row.find('.tiva').val(ivaCalc);
					row.find('.tAditionalTxt').text('$ '+taxesConcept);
					row.find('.tAditionalTxt').parent().append(nameAmounts);
					row.find('.tAditionalTxt').parent().append(amountsAA);
					row.find('.tRetentionTxt').text('$ '+retentionConcept);
					row.find('.tRetentionTxt').parent().append(nameRetentions);
					row.find('.tRetentionTxt').parent().append(amountsRetentions);
					row.find('.tAmountTxt').text('$ '+(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept));
					row.find('.tamount').val((((cant*precio)+ivaCalc+taxesConcept)-retentionConcept));
					row.find('.ttotal').val(((cant*precio)+ivaCalc));
					
					$('#body').append(row);
					$('input[name="quantity"]').removeClass('error').val("");
					$('input[name="description"]').removeClass('error').val("");
					$('input[name="price"]').removeClass('error').val("");
					$('input[name="iva_kind"]').prop('checked',false);
					$('input[name="addiotional"]').prop('checked',false);
					$('input[name="retention"]').prop('checked',false);
					$('#iva_no').prop('checked',true);
					$('#no_additional').prop('checked',true);
					$('#no_retention').prop('checked',true);
					$('input[name="amount"]').val("");
					$('[name="unit"]').val(null).trigger('change');
					$('#newsImpuestos').empty();
					$('#newsRetention').empty();
					$('.additionalName').val('');
					$('.additionalAmount ').val('');
					$('.retentionName').val('');
					$('.retentionAmount').val('');
					$('#taxes_exist').stop(true,true).slideUp().hide();
					$('#retention_new').stop(true,true).slideUp().hide();
					additionalCleanComponent();
					retentionCleanComponent();
					total_cal();
					countB++;
				}
			}
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('.tr_body').remove();
			total_cal();
			countB = $('.tr_body').length;
			$('.tr_body').each(function(i,v)
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
			cant	=	$('input[name="quantity"]').removeClass('error').val();
			unit	=	$('[name="unit"] option:selected').removeClass('error').val();
			descr	=	$('input[name="description"]').removeClass('error').val();
			precio	=	$('input[name="price"]').removeClass('error').val();
			if (cant == "" || descr == "" || precio == "" || unit == undefined) 
			{
				tquanty		=	$(this).parents('.tr_body').find('.tquanty').val();
				tunit		=	$(this).parents('.tr_body').find('.tunit').val();
				tdescr		=	$(this).parents('.tr_body').find('.tdescr').val();
				tivakind	=	$(this).parents('.tr_body').find('.tivakind').val();
				tprice		=	$(this).parents('.tr_body').find('.tprice').val();

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
						$('input[name="amount"]').val(tquanty*tprice);

						$(this).parents('.tr_body').remove();
						total_cal();
						countB = $('.tr_body').length;
						$('.tr_body').each(function(i,v)
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
		.on('click','input[name="retention_new"]',function()
		{
			if($(this).val() == 'si')
			{
				$('#retention_new').stop(true,true).slideDown().show();
			}
			else
			{
				$('#retention_new').stop(true,true).slideUp().hide();
			}
		})
		.on('click','.newRetention',function()
		{
			newI = $('<span class="span-taxes"><div class="left"><label class="label-form">Nombre de la Retención</label></div><div class="right"><input type="text" name="retentionName" class="generalInput retentionName" placeholder="Ingrese un nombre"></div><br><div class="left"><label class="label-form">Importe de Retención</label></div><div class="right"><input type="text" name="retentionAmount" class="generalInput retentionAmount" placeholder="Ingrese un importe"><button class="span-delete delete-item btn btn-red" type="button">Quitar</button></div><br></span>');
			$('#newsRetention').append(newI);
			$('.retentionAmount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
			$('[name="retentionAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
		})
		.on('click','input[name="additional_exist"]',function()
		{
			if($(this).val() == 'si')
			{
				$('#taxes_exist').stop(true,true).slideDown().show();
			}
			else
			{
				$('#taxes_exist').stop(true,true).slideUp().hide();
			}
		})
		.on('click','.newadditional',function()
		{
			newI = $('<span class="span-taxes"><div class="left"><label class="label-form">Nombre del Impuesto Adicional</label></div><div class="right"><input type="text" name="nameAmount" class="generalInput nameAmount" placeholder="Ingrese un nombre"></div><br><div class="left"><label class="label-form">Impuesto Adicional</label></div><div class="right"><input type="text" name="additionalAmount" class="generalInput additionalAmount" placeholder="Ingrese un impuesto"><button class="span-delete delete-item btn btn-red" type="button">Quitar</button></div><br></span>');
			$('#newsImpuestos').append(newI);
			$('.additionalAmount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
			$('[name="additionalAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
		})
		.on('click','.btn-delete-form',function(e)
		{
			e.preventDefault();
			form = $('form');
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
					$('#body').html('');
					$('#form-prov').hide();
					$('#banks').hide();
					$('#buscar').hide();
					$('#not-found').stop().hide();
					$('#table-provider').stop().hide();
					$('.removeselect').val(null).removeClass('valid').removeClass('error').removeAttr('style').trigger('change');
					$('.input-table,.generalInput,.remove').val('').removeClass('valid').removeClass('error').removeAttr('style');
					$('.generalInput,.removeselect,.remove').siblings('.form-error').remove();
					$('#iva_no').prop('checked',true);
					$('#nofiscal').prop('checked',true);
					$('#no_additional').prop('checked',true);
					$('#no_retention').prop('checked',true);
					$('#documents').empty();
					additionalCleanComponent()
					retentionCleanComponent()
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','.js-enterprises-destination',function()
		{
			idEnterprise	= $(this).val();
			if (idEnterprise != '') 
			{
				$('.resultbank').stop().fadeIn();
			}
			else
			{
				$('.resultbank').stop().fadeOut();
			}
			$.ajax({
				type : 'post',
				url  : '{{ route("movements-accounts.search.bank") }}',
				data : {'idEnterprise':idEnterprise},
				success:function(data)
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
	});
	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountAA 	= 0;
		amountR 	= 0;
		//descuento	= Number($('input[name="descuento"]').val());
		$(".tr_body").each(function(i, v)
		{
			tempQ	=	$(this).find('.tquanty').val();
			tempP	=	$(this).find('.tprice').val();
			tempAA 	=	null;
			tempR 	=	null;
			$(".num_amountAdditional").each(function(i, v)
			{
				tempAA	+=	Number($(this).val());
			});
			$(".num_amountRetention").each(function(i, v)
			{
				tempR 		+= Number($(this).val());
			});
			//tempD		= $(this).find('.tdiscount').val();
			subtotal	+= (Number(tempQ)*Number(tempP));
			iva			+= Number($(this).find('.tiva').val());
			amountAA	= Number(tempAA);
			amountR		= Number(tempR);
		});
		total = (subtotal+iva + amountAA)-amountR;
		$('input[name="subtotal"]').val('$ '+Number(subtotal).toFixed(2));
		$('.subtotalLabel').text('$ '+Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val('$ '+Number(iva).toFixed(2));
		$('.totalivaLabel').text('$ '+Number(iva).toFixed(2));
		$('input[name="total"]').val('$ '+Number(total).toFixed(2));
		$('.totalLabel').text('$ '+Number(total).toFixed(2));
		$(".amount_total").val('$ '+Number(total).toFixed(2));
		$('input[name="amountAA"]').val('$ '+Number(amountAA).toFixed(2));
		$('.amountAALabel').text('$ '+Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val('$ '+Number(amountR).toFixed(2));
		$('.amountRLabel').text('$ '+Number(amountR).toFixed(2));
	}
</script>
@endsection
