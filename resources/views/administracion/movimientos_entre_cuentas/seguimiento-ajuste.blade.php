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
		$taxesCount = $taxesCountBilling = 0;
		$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	<div id="form-adjustment">
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('movements-accounts.adjustment.follow.update', $request->folio)."\"", "methodEx" => "PUT", "files" => true])
			@component('components.labels.title-divisor')
				@slot('classEx')
					mb-4
				@endslot
				FORMULARIO AJUSTE DE MOVIMIENTOS
			@endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Título: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							removeselect
						@endslot
						@slot("attributeEx")
							type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($request)) value="{{ $request->adjustment->first()->title }}" @endif @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Fecha: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							removeselect datepicker2
						@endslot
						@slot("attributeEx")
							type="text" class="removeselect datepicker2" name="datetitle" @if(isset($request)) value="{{$request->adjustment->first()->datetitle != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->datetitle)->format('d-m-Y') : null}}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Nombre del solicitante: @endcomponent
					@php
						$options	=	collect();
						if (isset($request) && $request->idRequest != "")
						{
							$options	=	$options->concat([["value"	=>	$request->requestUser->id,	"description"	=>	$request->requestUser->fullname(),	"selected"	=> "selected"]]);
						}
					@endphp
					@component("components.inputs.select",["options"	=>	$options])
						@slot('attributeEx')
							name="userid" multiple="multiple"  data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							js-users removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Comentarios del ajuste: @endcomponent
					@component("components.inputs.text-area")
						@slot('attributeEx')
							name="commentaries"
							@if($request->status!=2) disabled="disabled" @endif
						@endslot
						{{ $request->adjustment->first()->commentaries }}
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				SELECCIÓN DE SOLICITUDES
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionsEnterprises	=	[];
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->adjustment()->exists() && $request->adjustment->first()->idEnterpriseOrigin == $enterprise->id)
							{
								$optionsEnterprises[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsEnterprises[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsEnterprises])
						@slot('attributeEx')
							removeselect" name="enterpriseid" multiple="multiple" data-validation="required" @if($request->adjustment->first()->adjustmentFolios()->exists()) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							js-enterprises removeselect
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="enterpriseid_origin" value="{{ $request->adjustment->first()->idEnterpriseOrigin }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Folio: @endcomponent
					@component('components.inputs.select')
						@slot('classEx')
							js-folios removeselect
						@endslot
						@slot('attributeEx')
							multiple="multiple" name="folios" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE ORIGEN
			@endcomponent
			@component('components.labels.not-found', ["variant" => "alert"])
				@slot('classEx')
					@if(count($request->adjustment->first()->adjustmentFolios)>0) hidden @endif
					mt-4
				@endslot
				@slot('attributeEx')
					id="error_request"
				@endslot
					Debe seleccionar una solicitud
			@endcomponent
			<div class="folios justify-between" style="display: flex;flex-wrap: wrap;">
				@foreach($request->adjustment->first()->adjustmentFolios as $af)
					@switch($af->requestModel->kind)
						@case(1)
							@php
								$subtotal_request	= $af->requestModel->purchases->first()->subtotales;
								$iva_request		= $af->requestModel->purchases->first()->tax;
								$tax_request		= 0;
								$retention_request	= 0;
								$total_request		= $af->requestModel->purchases->first()->amount;
							@endphp
							@foreach($af->requestModel->purchases->first()->detailPurchase as $detail)
								@foreach($detail->taxes as $tax)
									@php
										$tax_request += $tax->amount
									@endphp
								@endforeach
							@endforeach
							@foreach($af->requestModel->purchases->first()->detailPurchase as $detail)
								@foreach($detail->retentions as $ret)
									@php
										$retention_request += $ret->amount
									@endphp
								@endforeach
							@endforeach
						@break
						@case(3)
							@php
								$subtotal_request	= 0;
								$iva_request		= 0;
								$tax_request		= 0;
								$retention_request	= 0;
								$total_request		= 0;
							@endphp
							@foreach($af->requestModel->expenses->first()->expensesDetail as $detail)
								@php
									$subtotal_request	+= $detail->amount;
									$iva_request		+= $detail->tax;
									$total_request		+= $detail->sAmount;
								@endphp
								@foreach($detail->taxes as $tax)
									@php
										$tax_request += $tax->amount
									@endphp
								@endforeach
							@endforeach
						@break
						@case(9)
							@php
								$subtotal_request	= 0;
								$iva_request		= 0;
								$tax_request		= 0;
								$retention_request	= 0;
								$total_request		= 0;
							@endphp
							@foreach($af->requestModel->refunds->first()->refundDetail as $detail)
								@php
									$subtotal_request	+= $detail->amount;
									$iva_request		+= $detail->tax;
									$total_request		+= $detail->sAmount;
								@endphp
								@foreach($detail->taxes as $tax)
									@php
										$tax_request += $tax->amount
									@endphp
								@endforeach
							@endforeach
						@break
					@endswitch
					@component('components.inputs.input-text')
						@slot('classEx')
							folios_adjustment
						@endslot
						@slot('attributeEx')
							type="hidden" name="folios_adjustment[]" value="{{ $af->idFolio }}"
						@endslot
					@endcomponent
					<div class="content-center container-folio w-full max-w-md mx-6">
						@php
							$modelTable	=
							[
								["Empresa:",					$af->requestModel->reviewedEnterprise->name],
								["Dirección:",					$af->requestModel->reviewedDirection->name],
								["Departamento:",				$af->requestModel->reviewedDepartment->name],
								["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' '. $af->requestModel->accountsReview->description : 'Varias'],
								["Proyecto:",					$af->requestModel->reviewedProject->proyectName],
							];
						@endphp
						@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
							@slot('classEx')
								mt-4
							@endslot
							@slot('title')
								@component('components.labels.label')
									@slot('classEx')
										w-11/12
										text-center
										text-white
										ml-14
									@endslot
									FOLIO  #{{ $af->idFolio }}
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $af->idFolio }}"
									@endslot
									@slot('classEx')
										del-folio
									@endslot
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $subtotal_request }}"
									@endslot
									@slot('classEx')
										subtotal_request
									@endslot
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $tax_request }}"
									@endslot
									@slot('classEx')
										tax_request
									@endslot
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $retention_request }}"
									@endslot
									@slot('classEx')
										retention_request
									@endslot
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $iva_request }}"
									@endslot
									@slot('classEx')
										iva_request
									@endslot
								@endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										type="hidden" value="{{ $total_request }}"
									@endslot
									@slot('classEx')
										total_request
									@endslot
								@endcomponent
								@if ($request->status == 2)
									@component('components.buttons.button', ["variant" => "red"])
										@slot('attributeEx')
											type="button"
										@endslot
										@slot('label')
											<span class="icon-x"></span>
										@endslot
										@slot('classEx')
											mr-4
											h-8
											delete-folio
										@endslot
									@endcomponent
								@endif
							@endslot
						@endcomponent
					</div>
				@endforeach
			</div>
			<div id="detail" style="display: none;"></div>
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE AJUSTE
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionsEnterprises	=	[];
						foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							if ($request->adjustment()->exists() && $request->adjustment->first()->idEnterpriseDestiny == $enterprise->id)
							{
								$optionsEnterprises[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsEnterprises[]	=
								[
									"value"			=>	$enterprise->id,
									"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options"	=>	$optionsEnterprises])
						@slot('classEx')
							js-enterprises-destination removeselect
						@endslot
						@slot('attributeEx')
							name="enterpriseid_destination" multiple="multiple" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
						$optionsArea	=	[];
						foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if ($request->adjustment()->exists() && $request->adjustment->first()->idAreaDestiny == $area->id)
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
									"description"	=>	$area->name
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options"	=>	$optionsArea])
						@slot('classEx')
							js-areas-destination removeselect
						@endslot
						@slot('attributeEx')
							multiple="multiple" name="areaid_destination"  data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$optionsDepartment	=	[];
						foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if ($request->adjustment()->exists() && $request->adjustment->first()->idDepartamentDestiny == $department->id)
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
									"description"	=>	$department->name
								];
							}
						}
					@endphp
					@component('components.inputs.select', ["options"	=>	$optionsDepartment])
						@slot('classEx')
							js-departments-destination removeselect
						@endslot
						@slot('attributeEx')
							multiple="multiple" name="departmentid_destination" id="multiple-departments" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación del gasto: @endcomponent
					@php
						$options	=	collect();
						if ($request->adjustment()->exists() && $request->adjustment->first()->idAccAccDestiny != "")
						{
							$options	=	$options->concat([["value"	=>	$request->adjustment->first()->accountDestiny->idAccAcc,	"description"	=>	$request->adjustment->first()->accountDestiny->account." - ".$request->adjustment->first()->accountDestiny->description." (".$request->adjustment->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options"	=>	$options])
						@slot('classEx')
							js-accounts-destination removeselect
						@endslot
						@slot('attributeEx')
							multiple="multiple" name="accountid_destination"  data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto/Contrato: @endcomponent
					@php
						$options	=	collect();
						if ($request->adjustment()->exists() && $request->adjustment->first()->idProjectDestiny != "")
						{
							$options	=	$options->concat([["value"	=>	$request->adjustment->first()->projectDestiny->idproyect,	"description"	=>	$request->adjustment->first()->projectDestiny->proyectName,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options"	=>	$options])
						@slot('classEx')
							js-projects-destination removeselect
						@endslot
						@slot('attributeEx')
							name="projectid_destination" multiple="multiple"  data-validation="required" @if($request->status!=2) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CONDICIONES DE PAGO
			@endcomponent
			<div class="form-container">
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Tipo de moneda: @endcomponent
						@php
							$options		=	collect();
							$currencyData	=	["MXN", "USD", "EUR", "Otro"];
							foreach ($currencyData as $currency)
							{
								if ($request->adjustment->first()->currency == $currency)
								{
									$options	=	$options->concat([["value"	=>	$currency,	"description"	=>	$currency,	"selected"	=>	"selected"]]);
								}
								else
								{
									$options	=	$options->concat([["value"	=>	$currency,	"description"	=>	$currency]]);
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('classEx')
								remove
							@endslot
							@slot('attributeEx')
								name="type_currency" multiple="multiple" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Fecha de Pago: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								@if($request->status!=2) disabled="disabled" @endif type="text" name="date" step="1" placeholder="Ingrese la fecha" data-validation="required" readonly="readonly" id="datepicker" @if(isset($request)) value="{{ $request->adjustment->first()->paymentDate != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->paymentDate)->format('d-m-Y') : null}}" @endif
							@endslot
							@slot('classEx')
								remove
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Forma de pago: @endcomponent
						@php
							$options		=	collect();
							$payFormData	=	["1"=>"Cuenta Bancaria","2"=>"Efectivo","3"=>"Cheque"];
							foreach ($payFormData as $k => $payForm)
							{
								if (isset($request) && $request->adjustment->first()->idpaymentMethod == $k)
								{
									$options	=	$options->concat([["value"	=>	$k,	"description"	=>	$payForm,	"selected"	=>	"selected"]]);
								}
								else
								{
									$options	=	$options->concat([["value"	=>	$k,	"description"	=>	$payForm]]);
								}
							}
						@endphp
						@component('components.inputs.select', ["options" =>	$options])
							@slot('attributeEx')
								@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="pay_mode" data-validation="required" style="width: 83%;"
							@endslot
							@slot('classEx')
								js-form-pay removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Subtotal: @endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								@if($request->status!=2) disabled="disabled" @endif type="text" name="subtotal_adjustment" placeholder="Ingrese el subtotal" value="{{ $request->adjustment->first()->subtotales }}" readonly="readonly"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") IVA: @endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
								@if($request->status!=2) disabled="disabled" @endif type="text" name="iva_adjustment" placeholder="Ingrese el iva" value="{{ $request->adjustment->first()->tax }}" readonly="readonly"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Impuestos adicionales: @endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
									@if($request->status!=2) disabled="disabled" @endif type="text" name="tax_adjustment" placeholder="Ingrese el impuesto" value="{{ $request->adjustment->first()->additionalTax }}" readonly="readonly"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Retenciones: @endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
									@if($request->status!=2) disabled="disabled" @endif type="text" name="retention_adjustment" placeholder="Ingrese una retención" value="{{ $request->adjustment->first()->retention }}" readonly="readonly"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Total: @endcomponent
						@component("components.inputs.input-text")
							@slot('attributeEx')
									@if($request->status!=2) disabled="disabled" @endif  type="text" name="total_adjustment" placeholder="Ingrese el total" value="{{ $request->adjustment->first()->amount }}" readonly="readonly"
							@endslot
						@endcomponent
					</div>
				@endcomponent
			</div>
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
				if (count($request->adjustment->first()->documentsAdjustment)>0)
				{
					$modelHead	=	["Documento", "Fecha"];
					foreach($request->adjustment->first()->documentsAdjustment as $doc)
					{
						$body	=
						[
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"buttonElement"	=>	"a",
										"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
										"label"			=>	"Archivo"
									]
								],
							],
							[
								"content"	=>	["label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
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
							"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
			@component('components.labels.title-divisor')
				CARGAR DOCUMENTOS
			@endcomponent
			@component('components.containers.container-form', ["classEx" => "documentContent"])
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start text-center">
				<div class="md:block grid">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							type="button" name="addDoc" id="addDoc" @if($request->status == 1) disabled @endif
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar documento</span>
					@endcomponent
					@if($request->status != 2)
						@component('components.buttons.button', ["variant" => "success"])
							@slot('attributeEx')
								type="submit" name="send" id="send" value="CARGAR" formaction="{{ route('movements-accounts.update.documents', $request->folio) }}" @if($request->status == 1) disabled @endif
							@endslot
							@slot('label')
								CARGAR
							@endslot
						@endcomponent
					@endif
				</div>
			</div>
			@endcomponent
			@if($request->idCheck != "")
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE REVISIÓN
				@endcomponent
				@php
					if ($request->adjustment->first()->idEnterpriseDestinyR != '')
					{
						$requestAccount	=	App\Account::find($request->adjustment->first()->idAccAccDestinyR);
						$EnterpriseName	=	App\Enterprise::find($request->adjustment->first()->idEnterpriseDestinyR)->name;
						$areaName		=	App\Area::find($request->adjustment->first()->idAreaDestinyR)->name;
						$departmentName	=	App\Department::find($request->adjustment->first()->idDepartamentDestinyR)->name;
						$AccountDestiny	=	$requestAccount->account." - ".$requestAccount->description;
						$projectName	=	App\Project::find($request->adjustment->first()->idProjectDestinyR)->proyectName;
					}
					else
					{
						$requestAccount	=	"";
						$EnterpriseName	=	"";
						$areaName		=	"";
						$departmentName	=	"";
						$AccountDestiny	=	"";
						$projectName	=	"";
					}
					$modelTable	=
					[
						"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
						"Nombre de la Empresa de Destino"		=>	$EnterpriseName,
						"Nombre de la Dirección de Destino"		=>	$areaName,
						"Nombre del Departamento de Destino"	=>	$departmentName,
						"Clasificación del Gasto de Destino"	=>	$AccountDestiny,
						"Nombre del Proyecto de Destino"		=>	$projectName,
						"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
					@slot('classEx')
						employee-details
					@endslot
				@endcomponent
			@endif
			@if($request->idAuthorize != "")
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE AUTORIZACIÓN
				@endcomponent
				@php
					$modelTable	=
					[
						"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
						"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
					@slot('classEx')
						employee-details
					@endslot
				@endcomponent
			@endif
			@if($request->status == 13)
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					DATOS DE PAGOS
				@endcomponent
				@php
					$modelTable	=	["Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment)];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@endif
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-12 mb-6">
				@if($request->status == "2")
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx')
							type="button" name="enviar" value="ENVIAR SOLICITUD"
						@endslot
						@slot('classEx')
							enviar
						@endslot
						ENVIAR SOLICITUD
					@endcomponent
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx')
							type="submit" id="save" name="save" value="GUARDAR SIN ENVIAR" formaction="{{ route('movements-accounts.adjustment.follow.unsent', $request->folio) }}"
						@endslot
						@slot('classEx')
							save
						@endslot
						GUARDAR SIN ENVIAR
					@endcomponent
				@endif
				@component("components.buttons.button",["variant" => "reset"])
					@slot('attributeEx')
						@if(isset($option_id))
							href="{{ url(App\Module::find($option_id)->url) }}"
						@else
							href="{{ url(App\Module::find($child_id)->url) }}"
						@endif
					@endslot
					@slot('buttonElement')
						a
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
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
				path	= $('.path').length;
				if(path>0)
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
				swal("Cargando",
				{
					icon: '{{ asset(getenv('LOADING_IMG')) }}',
					button: false,
					closeOnClickOutside: false,
					closeOnEsc: false
				});
				return true;
			}
		});
	}
	$(document).ready(function()
	{
		validate();
		folios			= [];
		$('.folios_adjustment').each(function()
		{
			folios.push(Number($(this).val()));
		});
		count			= 0;
		countB			= {{ $taxesCount }};
		countBilling	= {{ $taxesCountBilling }};
		$('.phone,.clabe,.account,.cp').numeric(false);    // números
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false, decimal : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.amountAdditional_billing,retentionAmount,retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		$(function()
		{
			$("#datepicker, .datepicker2").datepicker({  dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.js-projects-destination', 'model': 21});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 23});
		generalSelect({'selector':'.js-folios', 'depends':'.js-enterprises','model': 29});

		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areas-destination",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departments-destination",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises",
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
			folios = 1;
		})
		.on('change','.js-enterprises',function()
		{
			enterprisejs	=	$('.js-enterprises').val();
			if (enterprisejs!="")
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				$('.js-folios').empty();
				
			}
		})
		.on('change','.js-folios',function()
		{
			$('.js-folios').parent().find('.form-error').remove();
			folio = $(this).val();
			if (($('.folios_adjustment').val() == '' || $('.folios_adjustment').val() == undefined) && (folio == undefined || folio == ''))
			{
				$('.js-enterprises').removeAttr('disabled');
			}
			else
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				$('.js-enterprises').removeAttr('disabled');
				$('#detail').empty();
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("movements-accounts.adjustment.create.detailrequest") }}',
					data 	: {'folio':folio[0]},
					success : function(data)
					{
						$('#detail').html(data).stop(true,true).slideDown().show();
						$('.js-enterprises').attr('disabled',true);
						$('#error_request').hide();
						$('.folios').hide();
						swal.close();
					},
					error	: function()
					{
						swal.close();
					}
				});
			}
		})
		.on('click','#close_request',function()
		{
			$('.js-folios').val(null).trigger('change');
			$('#detail').stop(true,true).slideUp().hide();
			$('#detail').empty();
			if ($('.folios_adjustment').val() == '' || $('.folios_adjustment').val() == undefined)
			{
				$('#error_request').show();
				$('.folios').hide();
			}
			else
			{
				$('#error_request').hide();
				$('.folios').show();
			}
		})
		.on('click','#add_request',function()
		{
			enterprise_request 	= $('input[name="enterprise_request"]').val();
			department_request 	= $('input[name="department_request"]').val();
			direction_request 	= $('input[name="direction_request"]').val();
			account_request 	= $('input[name="account_request"]').val();
			project_request 	= $('input[name="project_request"]').val();
			
			subtotal_request	= $('input[name="subtotal_request"]').val();
			iva_request			= $('input[name="iva_request"]').val();
			tax_request			= $('input[name="tax_request"]').val();
			retention_request	= $('input[name="retention_request"]').val();
			total_request		= $('input[name="total_request"]').val();
			sumTotales();
 			@php
				$component	=	"";
				$input = view('components.templates.outputs.table-detail',[
					"modelTable"	=>
					[
						["Empresa", [["kind" => "components.labels.label", "classEx" => "enterpriceClass"]]],	// enterprise_request
						["Dirección", [["kind" => "components.labels.label", "classEx" => "directionClass"]]],	// direction_request
						["Departamento", [["kind" => "components.labels.label", "classEx" => "departmentClass"]]],	// department_request
						["Clasificación de gasto", [["kind" => "components.labels.label", "classEx" => "accountClass"]]],	// account_request
						["Proyecto", [["kind" => "components.labels.label", "classEx" => "projectClass"]]],	// project_request
					],
					"title"	=>
					[
						["kind" => "components.labels.label", "classEx" => "w-11/12 text-white ml-14 titleClass text-center"], // $('select[name="folios"] option:selected').val()
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "del-folio"], // $('select[name="folios"] option:selected').val()
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "subtotal_request"],	// subtotal_request
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "tax_request"],	// tax_request
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "retention_request"],	// retention_request
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "iva_request"],	// iva_request
						["kind" => "components.inputs.input-text",	"attributeEx" => "type=\"hidden\"", "classEx" => "total_request"],	// total_request
						["kind" => "components.inputs.input-text",	"classEx" 	 => "folios_adjustment","attributeEx" 	=> "type=\"hidden\" name=\"folios_adjustment[]\""],
						["kind" => "components.buttons.button",		"attributeEx" => "type=\"button\"", "label" => "<span class=\"icon-x\"></span>", "classEx" => "mr-4 h-8 delete-folio",	"variant"	=>	"red"]
					],
					"classEx"	=>	"mt-4"
				]);
				$component .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", "<div class=\"content-center container-folio w-full max-w-md my-4 mx-6\">".
					$input->render()."</div>"));
			@endphp
			component = '{!!preg_replace("/(\r)*(\n)*/", "", $component)!!}';
			table_detail = $(component);
			table_detail.find(".enterpriceClass").text(enterprise_request);
			table_detail.find(".directionClass").text(direction_request);
			table_detail.find(".departmentClass").text(department_request);
			table_detail.find(".accountClass").text(account_request);
			table_detail.find(".projectClass").text(project_request);
			table_detail.find(".titleClass").text("FOLIO #"+$('select[name="folios"] option:selected').val());
			table_detail.find(".folios_adjustment").val($('select[name="folios"] option:selected').val());
			table_detail.find(".del-folio").val($('select[name="folios"] option:selected').val());
			table_detail.find(".subtotal_request").val(subtotal_request);
			table_detail.find(".tax_request").val(tax_request);
			table_detail.find(".retention_request").val(retention_request);
			table_detail.find(".iva_request").val(iva_request);
			table_detail.find(".total_request").val(total_request);
			$('.folios').append(table_detail);
			$('#error_request').hide();
			$('#detail').stop(true,true).slideUp().hide();
			$('#detail').empty();
			$('.folios').show();
			$('.js-folios').empty();
			enterprise	= $('select[name="enterpriseid"] option:selected').val();
			folios		= [];
			$('.folios_adjustment').each(function()
			{
				folios.push(Number($(this).val()));
			});
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
				timer: 500,
			});
		})
		.on('click','.delete-folio',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			folio = $(this).parents('div.container-folio').find('.del-folio').val();
			subtotal_request	= $(this).parents('div.container-folio').find('.subtotal_request').val();
			iva_request			= $(this).parents('div.container-folio').find('.iva_request').val();
			tax_request			= $(this).parents('div.container-folio').find('.tax_request').val();
			retention_request	= $(this).parents('div.container-folio').find('.retention_request').val();
			total_request		= $(this).parents('div.container-folio').find('.total_request').val();
			resTotales(subtotal_request,iva_request,tax_request,retention_request,total_request);
			$('.folios_adjustment').each(function()
			{
				if (folio == $(this).val())
				{
					$(this).remove();
				}
			});
			$('.js-folios').empty();
			enterprise	= $('select[name="enterpriseid"] option:selected').val();
			folios		= [];
			$('.folios_adjustment').each(function()
			{
				folios.push(Number($(this).val()));
			});
			
			$(this).parents('div.container-folio').remove();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
		.on('click','#addDoc',function()
		{
			@php
				$uploadDoc	=	html_entity_decode((String)view("components.documents.upload-files",
				[
					"classExInput"			=>	"docInput pathActioner",
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExDelete"			=>	"delete-doc"
				]));
			@endphp
			uploadDoc 	=	'{!!preg_replace("/(\r)*(\n)*/", "", $uploadDoc)!!}';
			$('#documents').append(uploadDoc);
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
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
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
				error		: function()
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
		.on('click','.enviar',function()
		{
			if($('.js-folios').val() == "")
			{
				if(folios != 0)
				{
					swal(
					{
						title: '¿Desea enviar la solicitud?',
						icon: 'info',
						buttons: {
							cancel: "Cancelar",
							send: {
								text: "Enviar",
								value: "send",
							},
						},
					}).then((value)=>
					{
						switch (value)
						{
							case "send":
								$('#container-alta').submit();
							break;
						}
					});
				}
				else
				{
					$('.js-folios').parent().find('.form-error').remove();
					$('.js-folios').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					swal('', 'Por favor seleccione un folio y agregue por lo menos una solicitud.', 'error');
					return false;
				}
			}
			else
			{
				if(folios != 0)
				{
					if($('.js-folios').val() != "")
					{
						swal('', 'Tiene un folio seleccionado para agregar o para cerrar.', 'info');
						return false;
					}
					else
					{
						$('#container-alta').submit();
					}
				}
				else
				{
					$('.js-folios').parent().find('.form-error').remove();
					swal('', 'Por favor agregue por lo menos una solicitud.', 'error');
					return false;
				}
			}
			if($('.total_adjustment').val() == 0 || $('.total_adjustment').val() == NULL)
			{
				swal('', 'El total no puede quedar en 0.', 'error');
				return false;
			}
		})
	});
	function resTotales(subtotal_request,iva_request,tax_request,retention_request,total_request)
	{
		subtotal_adjustment		= $('input[name="subtotal_adjustment"]').val();
		iva_adjustment			= $('input[name="iva_adjustment"]').val();
		tax_adjustment			= $('input[name="tax_adjustment"]').val();
		retention_adjustment	= $('input[name="retention_adjustment"]').val();
		total_adjustment		= $('input[name="total_adjustment"]').val();
		subtotal_new	= Number(subtotal_adjustment) - Number(subtotal_request);
		iva_new			= Number(iva_adjustment) - Number(iva_request);
		tax_new			= Number(tax_adjustment) - Number(tax_request);
		retention_new	= Number(retention_adjustment) - Number(retention_request);
		total_new		= Number(total_adjustment) - Number(total_request);
		$('input[name="subtotal_adjustment"]').val(Number(subtotal_new).toFixed(2));
		$('input[name="iva_adjustment"]').val(Number(iva_new).toFixed(2));
		$('input[name="tax_adjustment"]').val(tax_new);
		$('input[name="retention_adjustment"]').val(Number(retention_new).toFixed(2));
		$('input[name="total_adjustment"]').val(Number(total_new).toFixed(2));
	}
	function sumTotales()
	{
		subtotal_adjustment		= $('input[name="subtotal_adjustment"]').val();
		iva_adjustment			= $('input[name="iva_adjustment"]').val();
		tax_adjustment			= $('input[name="tax_adjustment"]').val();
		retention_adjustment	= $('input[name="retention_adjustment"]').val();
		total_adjustment		= $('input[name="total_adjustment"]').val();
		subtotal_request		= $('input[name="subtotal_request"]').val();
		iva_request				= $('input[name="iva_request"]').val();
		tax_request				= $('input[name="tax_request"]').val();
		retention_request		= $('input[name="retention_request"]').val();
		total_request			= $('input[name="total_request"]').val();
		subtotal_new	= Number(subtotal_adjustment) + Number(subtotal_request);
		iva_new			= Number(iva_adjustment) + Number(iva_request);
		tax_new			= Number(tax_adjustment) + Number(tax_request);
		retention_new	= Number(retention_adjustment) + Number(retention_request);
		total_new		= Number(total_adjustment) + Number(total_request);
		$('input[name="subtotal_adjustment"]').val(Number(subtotal_new).toFixed(2));
		$('input[name="iva_adjustment"]').val(Number(iva_new).toFixed(2));
		$('input[name="tax_adjustment"]').val(tax_new);
		$('input[name="retention_adjustment"]').val(Number(retention_new).toFixed(2));
		$('input[name="total_adjustment"]').val(Number(total_new).toFixed(2));
	}
	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountAA 	= 0;
		amountR 	= 0;
		//descuento	= Number($('input[name="descuento"]').val());
		$("#body tr").each(function(i, v)
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
			amountAA	= Number(tempAA);
			amountR		= Number(tempR);
		});
		total = (subtotal+iva + amountAA)-amountR;
		$('input[name="subtotal"]').val('$ '+Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val('$ '+Number(iva).toFixed(2));
		$('input[name="total"]').val('$ '+Number(total).toFixed(2));
		$(".amount_total").val('$ '+Number(total).toFixed(2));
		$('input[name="amountAA"]').val('$ '+Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val('$ '+Number(amountR).toFixed(2));
	}
</script>
@endsection
