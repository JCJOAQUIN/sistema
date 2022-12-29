@extends('layouts.child_module')

@section('data')
 @php
	$taxesCount = $taxesCountBilling = 0;
	$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
 @endphp
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('income.store')."\"", "files" => true])
		@component('components.labels.title-divisor') Nueva solicitud @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent	
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese el título" data-validation="required" @if(isset($requests)) value="{{ $requests->income->first()->title }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="datetitle" @if(isset($requests)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$requests->income->first()->datetitle)->format('d-m-Y') }}" @endif data-validation="required" placeholder="Ingrese la fecha" readonly="readonly"
					@endslot
					@slot('classEx')
						datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal: @endcomponent
				<div class="flex flex-row">
					@component("components.buttons.button-approval")
						@slot('classExContainer') mr-2 @endslot
						@slot('attributeEx')
							id="nofiscal" name="fiscal" value="0" checked @if(isset($requests) && $requests->taxPayment==0) checked @endif
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot('classExContainer') mr-2 @endslot
						@slot('attributeEx')
							id="fiscal" name="fiscal" value="1" @if(isset($requests) && $requests->taxPayment==1) checked @endif
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$options	=	collect();
					if (isset($requests->requestUser) && $requests->requestUser!="")
					{
						$options	=	$options->concat([["value"	=>	$requests->requestUser->id,	"description"	=>	$requests->requestUser->fullName(), "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						name="userid" multiple="multiple" id="multiple-users" data-validation="required"
					@endslot
					@slot('classEx')
						js-users removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if (isset($requests) && $requests->idEnterprise == $enterprise->id)
						{
							$optionsEnterpise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsEnterpise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterpise])
					@slot('attributeEx')
						name="enterpriseid" multiple="multiple" id="multiple-enterprises select2-selection--multiple" data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Proyecto/Contrato: @endcomponent
				@php
					$options	=	collect();
					if (isset($requests))
					{
						$options = $options->concat([["value" => $requests->requestProject->idproyect, "description" => $requests->requestProject->proyectName, "selected" => "selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						name="projectid" multiple="multiple" id="multiple-projects" data-validation="required"
					@endslot
					@slot('classEx')
						js-projects removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@if(isset($requests))
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="employee_number" id="efolio" placeholder="Ingrese el número de empleado" value="@foreach($requests->refunds as $refund) {{ $refund->idUsers }} @endforeach"
				@endslot
				@slot('classEx')
					employee_number
				@endslot
			@endcomponent
			<div class="resultbank">
				@component('components.labels.title-divisor') SELECCIONE UNA CUENTA @endcomponent
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
							["value"	=>	"Sucursal"],
							["value"	=>	"Referencia"],
							["value"	=>	"CLABE"],
							["value"	=>	"Moneda"],
							["value"	=>	"Convenio"],
						],
					];
					if (isset($requests))
					{
						foreach(App\BanksAccounts::where('idEnterprise',$requests->idEnterprise)->get() as $bank)
						{
							if ($requests->income->first()->idbanksAccounts == $bank->idbanksAccounts)
							{
								$mark	=	"marktr";
								$check	=	"checked";
							}
							else
							{
								$mark	=	"";
								$check	=	"false";
							}
							$body	=
							[
								"classEx"	=>	"tr_banks_body $mark",
								[
									"content"	=>
									[
										[
											"kind"			=>	"components.inputs.checkbox",
											"classEx"		=>	"my-2 checkbox",
											"attributeEx"	=>	"type=\"radio\" id=\"idBA $bank->idbanksAccounts\" name=\"idbanksAccounts\" $check value=\"".$bank->idbanksAccounts."\"",
											"classExLabel"	=>	"request-validate",
											"label"			=>	"<span class='icon-check'></span>",
											"radio"			=>	"true"
										]
									]
								],
								[
									"content"	=>	["label"	=>	$bank->bank->description],
								],
								[
									"content"	=>	["label"	=>	$bank->alias!=null ? $bank->alias : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->account!=null ? $bank->account : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->branch!=null ? $bank->branch : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->reference!=null ? $bank->reference : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->clabe!=null ? $bank->clabe : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->currency!=null ? $bank->currency : '---'],
								],
								[
									"content"	=>	["label"	=>	$bank->agreement!=null ? $bank->agreement : '---'],
								],
							];
							$modelBody[]	=	$body;
						}
					}
				@endphp
				@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table2\"", "attributeExBody" => "id=\"banks-body\"", "classExBody" => "request-validate"]) @endcomponent
			</div>
		@else
			<div class="resultbank table-responsive hidden"></div>
		@endif
		@component('components.labels.title-divisor', ["classEx" => "mt-12"]) SELECCIONAR CLIENTE <span class="help-btn" id="help-btn-select-client"> @endcomponent
		<div class="flex flew-row justify-center mt-5">
			@component("components.buttons.button-approval")
				@slot('classExContainer') mr-2 @endslot
				@slot('attributeEx')
					name="prov" value="nuevo" id="new-prov"
				@endslot
				Nuevo
			@endcomponent
			@component("components.buttons.button-approval")
				@slot('classExContainer') mr-2 @endslot
				@slot('attributeEx')
					name="prov" value="buscar" id="buscar-prov"
					@if(isset($requests)) checked @endif
				@endslot
				Buscar
			@endcomponent
		</div>
		<div class="text-center w-full pb-2 @if(!isset($requests)) hidden @endif " id="buscar">
			<div id="container-cambio" class="px-2 md:px-56">
				@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" id=\"pagePagination\" value=\"1\""]) @endcomponent
				@component('components.inputs.input-search') 
					Buscar Cliente
					@slot('attributeExInput')
						name="search" 
						id="input-search"
						placeholder="Ingrese el nombre del cliente" 
					@endslot
					@slot('attributeExButton')
						type="button"
					@endslot
					@slot('classExButton')
						button-search
					@endslot
				@endcomponent
			</div>
			<div class="table-responsive client mt-4 text-center"> </div>
			<div id="not-found"> </div>
		</div>
		<div id="form-prov" class="request-validate @if(!isset($requests)) hidden @endif">
			<div class="flex flex-col items-center mt-5">
				@component('components.labels.label') DATOS DEL CLIENTE @endcomponent
				<div class="flex-row mt-4 whitespace-nowrap" id="editCkeck">
					@component("components.inputs.switch")
						@slot('attributeEx')
							name="edit" value="1" id="edit"
						@endslot
						@slot('forvalue')
							edit
						@endslot
							<span class="slider round"></span>Habilitar edición</label>
					@endcomponent
					<span class="help-btn"></span>
				</div>
			</div>
			<input type="hidden" name="idClient" @if(isset($requests)) value="{{ $requests->income->first()->client->idClient }}" @endif>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')Razón Social: @endcomponent	
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="reason" placeholder="Ingrese la razón social" data-validation="length required server" data-validation-length="max150" data-validation-url="{{ route('income.client.validation') }}" @if(isset($requests)) data-validation-req-params="{{ json_encode(array('oldReason'=>$requests->income->first()->client->businessName)) }}" value="{{ $requests->income->first()->client->businessName }}" disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Calle: @endcomponent	
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100" @if(isset($requests)) value="{{ $requests->income->first()->client->address }}" disabled @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Número: @endcomponent	
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="number" placeholder="Ingrese un número" data-validation="required length" data-validation-length="max45" @if(isset($requests)) value="{{ $requests->income->first()->client->number }}" disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Colonia: @endcomponent	
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70" @if(isset($requests)) value="{{ $requests->income->first()->client->colony }}" disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Código Postal: @endcomponent
					@php
						$options	=	collect();
						if (isset($requests) && $requests->income->first()->client->postalCode != "")
						{
							$options	=	$options->concat([["value"	=>	$requests->income->first()->client->postalCode, "description"	=>	$requests->income->first()->client->postalCode,	"selected"	=>	"selected"]]);
						}
					@endphp
					@component('components.inputs.select', ["options" => $options, "classEx" => "input-text remove cp"])
						@slot('attributeEx')
							name="cp" id="cp" placeholder="Ingrese un código postal" multiple="multiple" data-validation="required" @if(isset($requests)) disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Ciudad: @endcomponent	
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="city" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70" @if(isset($requests)) value="{{ $requests->income->first()->client->city }}" disabled @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Estado: @endcomponent
					@php
						$optionState = [];
						foreach(App\State::orderName()->get() as $state)
						{
							if(isset($requests) && $requests->income->first()->client->state_idstate==$state->idstate)
							{
								$optionState[] = ["value" => $state->idstate, "description" => $state->description, "selected" => "selected"];
							}
							else
							{
								$optionState[] = ["value" => $state->idstate, "description" => $state->description];
							}
						}
						$disabled	=	isset($requests) ? "disabled" :	"false";
					@endphp
					@component('components.inputs.select', ["options" => $optionState,"classEx" => "js-state remove", "attributeEx" => "name=\"state\" multiple=\"multiple\" data-validation=\"required\" disabled=\"".$disabled."\""])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') RFC: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="server" data-validation-url="{{ route('income.client.validation') }}"  @if(isset($requests)) value="{{ $requests->income->first()->client->rfc }}" data-validation-req-params="{{ json_encode(array('oldRfc'=>$requests->income->first()->client->rfc)) }}" disabled @endif 
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Teléfono: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							phone remove
						@endslot
						@slot('attributeEx')
							type="text" name="phone" placeholder="Ingrese el teléfono" phone remove" data-validation="phone required" @if(isset($requests)) value="{{ $requests->income->first()->client->phone }}" disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Contacto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="contact" placeholder="Ingrese un contacto"  data-validation="required" @if(isset($requests)) value="{{ $requests->income->first()->client->contact }}" disabled @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Correo Electrónico: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="email" placeholder="Ingrese el correo electrónico"  data-validation="email" @if(isset($requests)) value="{{ $requests->income->first()->client->email }}" disabled @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Otro (opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="other" placeholder="Ingrese otro" @if(isset($requests)) value="{{ $requests->income->first()->client->commentaries }}" disabled @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button",["variant" => "red"])
						@slot('attributeEx')
							id="closeFormProv" type="button"
						@endslot
						Cerrar
					@endcomponent
				</div>
			@endcomponent
		</div>
		<div class="form-container" id="form">
			@component('components.labels.title-divisor')
				@slot('classEx') mt-12 @endslot
				DATOS DE VENTA <span class="help-btn" id="help-btn-dates">
			@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label')Cantidad: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx') quanty @endslot
						@slot('attributeEx')
							type="text" name="quantity" placeholder="Ingrese la cantidad"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Unidad: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx') unit @endslot
						@slot('attributeEx')
							type="text" name="unit" placeholder="Ingrese la unidad"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Descripción: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="description" placeholder="Ingrese la descripción"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Precio Unitario: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx') price @endslot
						@slot('attributeEx')
							type="text" name="price" placeholder="Ingrese el precio"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 @if(isset($requests) && $requests->taxPayment == 0) hidden @endif">
					@component('components.labels.label')Tipo de IVA: @endcomponent
					<div class="flex flew-row">
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								name="iva_kind" id="iva_no" value="no" title="No IVA" checked=""
								@if(isset($requests) && $requests->taxPayment == 0) disabled @endif
							@endslot
							No
						@endcomponent
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								name="iva_kind" id="iva_a" value="a" title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
								@if(isset($requests) && $requests->taxPayment == 0) disabled @endif
							@endslot
							A
						@endcomponent
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								name="iva_kind" id="iva_b" value="b" title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
								@if(isset($requests) && $requests->taxPayment == 0) disabled @endif
							@endslot
							B
						@endcomponent
					</div>
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional'])  @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention'])  @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							amount
						@endslot
						@slot('attributeEx')
							type="text" readonly="readonly" name="amount" placeholder="Ingrese el importe"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot("attributeEx")
							id="add" name="add" type="button"
						@endslot
						@slot('classEx')
							add2
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar concepto</span>
					@endcomponent
				</div>
			@endcomponent
		</div>
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
					["value"	=>	"Acciones"]
				]
			];
			if (isset($requests) && $child == 0)
			{
				foreach($requests->income->first()->incomeDetail as $key=>$detail)
				{
					$componentEx			=	[];
					$componentsRetentions	=	[];
					$taxesConcept=0;
					$retentionConcept=0;
					foreach ($detail->taxes as $tax)
					{
						$componentEx[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"",
							"classEx"		=>	"num_amountAdditional"
						];
						$componentEx[]		=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"".htmlentities($tax->name)."\"",
							"classEx"		=>	"num_nameAmount"
						];
						$taxesConcept+=$tax->amount;
					}
					$componentEx[]	=	["label"	=>	"$ ".number_format($taxesConcept,2)];
					foreach ($detail->retentions as $ret)
					{
						$componentsRetentions[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"",
							"classEx"		=>	"num_amountRetention"
						];
						$componentsRetentions[]		=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"".htmlentities($ret->name)."\"",
							"classEx"		=>	"num_nameRetention"
						];
						$retentionConcept+=$ret->amount;
					}
					$componentsRetentions[]	=	["label"	=>	"$ ".number_format($retentionConcept,2)];
					$taxesCount++;
					$body	=
					[
						[
							"content"	=>
							[
								[
									"kind"		=>	"components.labels.label",
									"label"		=>	$key+1,
									"classEx"	=>	"countConcept"
								],
							],
						],
						[
							"content"	=>
							[
								["label"			=>	$detail->quantity],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
									"classEx"		=>	"tquanty"
								]
							]
						],
						[
							"content"	=>
							[
								["label"			=>	htmlentities($detail->unit)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".htmlentities($detail->unit)."\"",
									"classEx"		=>	"tunit"
								]
							]
						],
						[
							"content"	=>
							[
								["label"			=>	htmlentities($detail->description)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\"",
									"classEx"		=>	"tdescr"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".($detail->typeTax !="" ? $detail->typeTax : "no")."\"",
									"classEx"		=>	"tivakind"
								]
							]
						],
						[
							"content"	=>
							[
								["label"			=>	"$ ".$detail->unitPrice],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
									"classEx"		=>	"tprice"
								]
							]
						],
						[
							"content"	=>
							[
								["label"			=>	"$ ".number_format($detail->tax,2)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
									"classEx"		=>	"tiva"
								]
							]
						],
						[
							"content"	=>	$componentEx
						],
						[
							"content"	=>	$componentsRetentions
						],
						[
							"content"	=>
							[
								["label"			=>	"$ ".number_format($detail->amount,2)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->amount."\"",
									"classEx"		=>	"tamount"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"success",
									"label"			=>	"<span class='icon-pencil'></span>",
									"attributeEx"	=>	"id=\"edit\" type=\"button\"",
									"classEx"		=>	"edit-item"
								],
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"label"			=>	"<span class='icon-x delete-span'></span>",
									"classEx"		=>	"delete-item",
									"attributeEx"	=>	"type=\"button\""
								],
							]
						],
					];
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
		@php
			$subtotalLabel			=	"$ 0.00";
			$taxPayLabel			=	"$ 0.00";
			$totalPayLabel			=	"$ 0.00";
			$amountRetentionLabel	=	"$ 0.00";
			$amountAditionalLabel	=	"$ 0.00";
			$subtotal				=	"";
			$taxPay					=	"";
			$totalPay				=	"";
			$amountRetention		=	"";
			$amountAditional		=	"";
			if (isset($requests) && $child == 0)
			{
				$subtotal		= number_format($requests->income->first()->subtotales,2,".",",");
				$taxPay			= number_format($requests->income->first()->tax,2,".",",");
				$totalPay		= number_format($requests->income->first()->amount,2,".",",");
				$subtotalLabel	= '$ '.number_format($requests->income->first()->subtotales,2,".",",");
				$taxPayLabel	= '$ '.number_format($requests->income->first()->tax,2,".",",");
				$totalPayLabel	= '$ '.number_format($requests->income->first()->amount,2,".",",");
			}
			if (isset($requests) && $child == 0)
			{
				foreach ($requests->income->first()->incomeDetail as $detail)
				{
					foreach ($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}
					foreach ($detail->retentions as $ret)
					{
						$retentions += $ret->amount;
					}
				}
				$amountRetention 		= number_format($retentions,2);
				$amountAditional		= number_format($taxes,2);
				$amountRetentionLabel	= '$ '.number_format($retentions,2);
				$amountAditionalLabel	= '$ '.number_format($taxes,2);
			}
			$modelTable	=
			[
				["label"	=>	"Subtotal:",			"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2 generalClass subtotalLabel",	"label"	=>	$subtotalLabel ],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"subtotal\" value=\"".$subtotal."\"", "classEx" => "removeselect"]
					]
				],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2 generalClass amountAALabel",	"label"	=>	$amountAditionalLabel ],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountAA\" value=\"".$amountAditional."\"", "classEx" => "removeselect"]
					]
				],
				["label"	=>	"Retenciones:",			"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2 generalClass amountRLabel",	"label"	=>	$amountRetentionLabel ],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountR\" value=\"".$amountRetention."\"", "classEx" => "removeselect"]
					]
				],
				["label"	=>	"IVA:",					"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2 generalClass totalivaLabel",	"label"	=>	$taxPayLabel ],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"totaliva\" value=\"".$taxPay."\"","classEx" => "removeselect"]
					]
				],
				["label"	=>	"TOTAL:",				"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2 generalClass totalLabel",	"label"	=>	$totalPayLabel ],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"id=\"input-extrasmall\" placeholder=\"$0.00\" readonly type=\"hidden\" name=\"total\" value=\"".$totalPay."\"", "classEx" => "removeselect"]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
		@component('components.labels.title-divisor')
			CARGAR DOCUMENTOS
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				<div class="md:block grid">
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
					@if(isset($requests) && $child == 1)
						<input name="createChild" value="{{ $requests->folio }}" hidden/>
					@endif
				</div>
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx') mr-2 enviar @endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
					id="send"
					value="ENVIAR SOLICITUD"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button",["variant" => "secondary"])
				@slot('classEx') mr-2 save @endslot
				@slot("attributeEx")
					type="submit" 
					id="save" 
					name="save" 
					value="GUARDAR SIN ENVIAR" 
					formaction="{{ route('income.unsent') }}"
				@endslot
				GUARDAR SIN ENVIAR
			@endcomponent
			@component("components.buttons.button",["variant" => "reset"])
				@slot('classEx') btn-delete-form @endslot
				@slot("attributeEx")
					type="reset"
					name="borra"
					value="Borrar campos"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent
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
				path = $('.path').length;
				if(path > 0)
				{
					pas = true;
					$('.path').each(function()
					{
						if($(this).val() == '')
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
				cant	= $('input[name="quantity"]').removeClass('error').val();
				unit	= $('input[name="unit"]').removeClass('error').val();
				descr	= $('input[name="description"]').removeClass('error').val();
				precio	= $('input[name="price"]').removeClass('error').val();
				if (cant != "" || descr != "" || precio != "" || unit != "") 
				{
					swal('', 'Tiene un concepto sin agregar', 'error');
					return false;
				}
				subtotal	= 0;
				iva			= 0;
				descuento	= Number($('input[name="descuento"]').val());
				$(".tr").each(function(i, v)
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
					prov		= $('#form-prov').is(':visible');
					conceptos	= $('#body .tr').length;
					if($('#form-prov').is(':visible') && conceptos > 0)
					{
						if($('#banks-body .tr_banks_body').length>0)
						{
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
								swal('', 'Debe seleccionar una cuenta', 'error');
								return false;
							}
						}
						else
						{
							swal('', 'Debe ingresar al menos una cuenta', 'error');
							return false;
						}
					}
					else
					{
						swal('', 'Debe ingresar al menos un concepto de pedido y todos los datos del cliente', 'error');
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
	$(document).ready(function()
	{
		validate()
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional','function'=>'taxesRetention'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention','name' => 'retention','function'=>'taxesRetention'])  @endcomponent
		$('[name="price"],[name="additionalAmount"],[name="retentionAmount"]').on("contextmenu",function(e)
		{
			return false;
		});
		count			= 0;
		countB			= {{ $taxesCount }};
		$('.phone,.clabe,.account').numeric(false);
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false, decimal : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.additionalAmount,.retentionAmount,[name="price"]',).numeric({ altDecimal: ".", decimalPlaces: 2,negative : false });
		$(function() 
		{
			$("#datepicker, .datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.cp', 'model': 2});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-projects', 'model': 21});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-state",
					"placeholder"				=> "Seleccione un estado",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('change','input[name="prov"]',function()
		{
			if ($('input[name="prov"]:checked').val() == "nuevo") 
			{
				$("#form-prov").slideDown("slow");
				$('input[name="idClient"]').val('');
				$('input[name="reason"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
				$('input[name="address"]').val('').prop('disabled',false);
				$('input[name="number"]').val('').prop('disabled',false);
				$('input[name="colony"]').val('').prop('disabled',false);
				$('#cp').val('').trigger('change').prop('disabled',false);
				$('input[name="city"]').val('').prop('disabled',false);
				$('.js-state').val('').trigger('change').prop('disabled',false);
				$('input[name="rfc"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
				$('input[name="phone"]').val('').prop('disabled',false);
				$('input[name="contact"]').val('').prop('disabled',false);
				$('input[name="beneficiary"]').val('').prop('disabled',false);
				$('input[name="email"]').val('').prop('disabled',false);
				$('input[name="other"]').val('').prop('disabled',false);
				$("#buscar").slideUp('fast');
				$(".checks").hide();
				$('#banks').show();
				$('#editCkeck').hide();
			}
			else if ($('input[name="prov"]:checked').val() == "buscar") 
			{
				$("#buscar").slideDown("slow");
				$("#form-prov").slideUp('fast');
				$(".input-bank").css({display:'block'});
				$(".select-bank").css({display:'none'});
				$('#editCkeck').show();
			}
		})
		.on('click','.help-btn',function()
		{
			swal('Ayuda','Al habilitar la edición, usted podrá modificar los campos del cliente; si la edición permanece deshabilitada no se guardará ningún cambio en el mismo.','info');
		})
		.on('click','#save',function(e)
		{
			e.preventDefault();
			if ($('input[name="prov"]').is(':checked') && $('#form-prov').is(':visible')) 
			{
				rfc		= $('[name="rfc"]').val();
				reason	= $('[name="reason"]').val();
				if (rfc == "" || reason == "") 
				{
					swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
					if (rfc == "")
					{
						$('[name="rfc"]').addClass('error');
					}
					if (reason == "")
					{
						$('[name="reason"]').addClass('error');
					}
				}
				else
				{
					$('.remove').removeAttr('data-validation');
					$('.removeselect').removeAttr('required');
					$('.removeselect').removeAttr('data-validation');
					$('.request-validate').removeClass('request-validate');
					action = $(this).attr('formaction');
					form = $('form#container-alta').attr('action',action);
					form.submit();
				}
			}
			else
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');

				action = $(this).attr('formaction');
				form = $('form#container-alta').attr('action',action);
				form.submit();
			}
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
			}
			else if ($('input[name="fiscal"]:checked').val() == "0") 
			{
				$('.iva_kind').prop('disabled',true);
				$('#iva_no').prop('checked',true);
				$('.iva_kind').parent('p').stop(true,true).fadeOut();
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
					$('#table-client').stop().hide();
					$('.removeselect').val(null).trigger('change');
					$('.generalClass').text("$ 0.00");
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('change','.quanty,.price,.iva_kind,.additionalAmount,.retentionAmount,.addiotional,.retention',function()
		{
			taxesRetention();
		})
		.on('change','input[name="edit"]',function()
		{
			if($(this).is(':checked'))
			{
				swal({
					title		: "Habilitar edición de cliente",
					text		: "¿Desea habilitar la edición del cliente?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((enable) =>
				{
					if(enable)
					{
						$('input[name="reason"]').prop('disabled',false);
						$('input[name="address"]').prop('disabled',false);
						$('input[name="number"]').prop('disabled',false);
						$('input[name="colony"]').prop('disabled',false);
						$('#cp').prop('disabled',false);
						$('input[name="city"]').prop('disabled',false);
						$('.js-state').prop('disabled',false);
						$('input[name="rfc"]').prop('disabled',false);
						$('input[name="phone"]').prop('disabled',false);
						$('input[name="contact"]').prop('disabled',false);
						$('input[name="beneficiary"]').prop('disabled',false);
						$('input[name="email"]').prop('disabled',false);
						$('input[name="other"]').prop('disabled',false);
						$('#banks').show();
						$('#banks-body .delete-item').show();
					}
					else
					{
						$('input[name="reason"]').prop('disabled',true);
						$('input[name="address"]').prop('disabled',true);
						$('input[name="number"]').prop('disabled',true);
						$('input[name="colony"]').prop('disabled',true);
						$('#cp').prop('disabled',true);
						$('input[name="city"]').prop('disabled',true);
						$('.js-state').prop('disabled',true);
						$('input[name="rfc"]').prop('disabled',true);
						$('input[name="phone"]').prop('disabled',true);
						$('input[name="contact"]').prop('disabled',true);
						$('input[name="beneficiary"]').prop('disabled',true);
						$('input[name="email"]').prop('disabled',true);
						$('input[name="other"]').prop('disabled',true);
						$('#banks').hide();
						$('#banks-body .delete-item').hide();
						$(this).prop('checked',false);
					}
				});
			}
			else
			{
				swal({
					title		: "Deshabilitar edición de cliente",
					text		: "Si deshabilita la edición las modificaciones realizadas al cliente no serán guardadas",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((disabled) =>
				{
					if(disabled)
					{
						$('input[name="reason"]').prop('disabled',true);
						$('input[name="address"]').prop('disabled',true);
						$('input[name="number"]').prop('disabled',true);
						$('input[name="colony"]').prop('disabled',true);
						$('#cp').prop('disabled',true);
						$('input[name="city"]').prop('disabled',true);
						$('.js-state').prop('disabled',true);
						$('input[name="rfc"]').prop('disabled',true);
						$('input[name="phone"]').prop('disabled',true);
						$('input[name="contact"]').prop('disabled',true);
						$('input[name="beneficiary"]').prop('disabled',true);
						$('input[name="email"]').prop('disabled',true);
						$('input[name="other"]').prop('disabled',true);
						$('#banks').hide();
						$('#banks-body .delete-item').hide();
					}
					else
					{
						$('input[name="reason"]').prop('disabled',false);
						$('input[name="address"]').prop('disabled',false);
						$('input[name="number"]').prop('disabled',false);
						$('input[name="colony"]').prop('disabled',false);
						$('#cp').prop('disabled',false);
						$('input[name="city"]').prop('disabled',false);
						$('.js-state').prop('disabled',false);
						$('input[name="rfc"]').prop('disabled',false);
						$('input[name="phone"]').prop('disabled',false);
						$('input[name="contact"]').prop('disabled',false);
						$('input[name="beneficiary"]').prop('disabled',false);
						$('input[name="email"]').prop('disabled',false);
						$('input[name="other"]').prop('disabled',false);
						$('#banks').show();
						$('#banks-body .delete-item').show();
						$(this).prop('checked',true);
					}
				});
			}
		})
		.on('click','#add',function()
		{
			countConcept		= $('.countConcept').length;
			cant				= $('input[name="quantity"]').removeClass('error').val();
			unit				= $('input[name="unit"]').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			iva					= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2				= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivakind 			= $('input[name="iva_kind"]:checked').val();
			ivaCalc				= 0;
			taxesConcept 		= 0;
			retentionConcept 	= 0;
			if (cant == "" || descr == "" || precio == "" || unit == "")
			{
				if(cant=="")
				{
					$('input[name="quantity"]').addClass('error');
				}
				if(unit=="")
				{
					$('input[name="unit"]').addClass('error');
				}
				if(descr=="")
				{
					$('input[name="description"]').addClass('error');
				}
				if(precio=="")
				{
					$('input[name="price"]').addClass('error');
				}
				swal('', 'Por favor llene todos los campos.', 'error');
			}
			else if (cant == 0 || precio == 0)
			{
				swal('','La cantidad y el precio unitario no pueden ser cero', 'error');
				$('input[name="quantity"]').addClass('error');
				$('input[name="price"]').addClass('error');
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

				nameAmounts = $('<td></td>');
				$('.additionalName').each(function(i,v)
				{
					nameAmount = $(this).val();
					nameAmounts.append($('<input type="hidden" class="num_nameAmount" name="tnameamount'+countB+'[]">').val(nameAmount));
				});

				amountsAA = $('<td></td>');
				$('.additionalAmount').each(function(i,v)
				{
					amountAA = $(this).val();
					amountsAA.append($('<input type="hidden" class="num_amountAdditional" name="tamountadditional'+countB+'[]">').val(amountAA));
					taxesConcept = Number(taxesConcept) + Number(amountAA);
				});

				nameRetentions = $('<td></td>');
				$('.retentionName').each(function(i,v)
				{
					name = $(this).val();
					nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="tnameretention'+countB+'[]">').val(name));
				});

				amountsRetentions = $('<td></td>');
				$('.retentionAmount').each(function(i,v)
				{
					amountR = $(this).val();
					amountsRetentions.append($('<input type="hidden" class="num_amountRetention" name="tamountretention'+countB+'[]">').val(amountR));
					retentionConcept = Number(retentionConcept)+Number(amountR);
				});
				checkTotal = ((cant*precio)+ivaCalc+taxesConcept)-retentionConcept;
				if (checkTotal < 0) 
				{
					swal('','El total de retenciones no pueden ser mayor al importe total','error');
				}
				else
				{
					@php
						$buttonEdit = view("components.buttons.button",[
							"variant"	=>	"success",
							"label"		=>	"<span class=\"icon-pencil\"></span>",
							"attributeEx"	=>	"type=\"button\"",
							"classEx"	=>	"edit-item"
						])->render();
						$buttonDelete = view("components.buttons.button",[
							"variant"	=>	"red",
							"label"		=>	"<span class=\"icon-x\"></span>",
							"classEx"	=>	"delete-item"
						])->render();
					@endphp
					newButtonEdit = '{!!preg_replace("/(\r)*(\n)*/", "", $buttonEdit)!!}';
					newButtonDelete = '{!!preg_replace("/(\r)*(\n)*/", "", $buttonDelete)!!}';
					buttonEdit = $(newButtonEdit);
					buttonDelete = $(newButtonDelete);
					countConcept = countConcept+1;
					@php
						$body 		=	[];
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
								["value"	=>	"Acción"]
							]
						];
						$modelBody = 
						[
							[
								[
									"content"	=>
									[
										"kind"		=>	"components.labels.label",
										"label"		=>	"",
										"classEx"	=>	"countConcept"
									]
								],
								[
									"content"	=>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelQuantity"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\"",
											"classEx"		=>	"tquanty"
										]
									]
								],
								[
									"content"	=>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelUnit"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\"",
											"classEx"		=>	"tunit"
										]
									]
								],
								[
									"content"	=>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelDescr"
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
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelPrice"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\"",
											"classEx"		=>	"tprice"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelIva"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
											"classEx"		=>	"tiva"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelTaxesConcept"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelRetention"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.labels.label",
											"label"		=>	"",
											"classEx"	=>	"labelImport"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\"",
											"classEx"		=>	"ttotal"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\"",
											"classEx"		=>	"tamount"
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=>	"components.buttons.button",
											"variant"	=>	"success",
											"label"		=>	"<span class=\"icon-pencil\"></span>",
											"classEx"	=>	"edit-item",
											"attributeEx"	=>	"type=\"button\" id=\"edit\""
										],
										[
											"kind"		=>	"components.buttons.button",
											"variant"	=>	"red",
											"label"		=>	"<span class=\"icon-x\"></span>",
											"classEx"	=>	"delete-item"
										]
									]
								]
							]
						];
						$table = view('components.tables.table',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"	=> "true"
						])->render();
					@endphp
					table_row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					table=$(table_row);
					table.find('.countConcept').text(countConcept);
					table.find('.labelQuantity').text(Number(cant));
					table.find('.tquanty').val(Number(cant));
					table.find('.labelUnit').text(unit);
					table.find('.tunit').val(unit);
					table.find('.labelDescr').text(descr);
					table.find('.tdescr').val(descr);
					table.find('.tivakind').val(ivakind);
					table.find('.labelPrice').text('$ '+Number(precio).toFixed(2));
					table.find('.tprice').val(precio);
					table.find('.labelIva').text('$ '+Number(ivaCalc).toFixed(2));
					table.find('.tiva').val(ivaCalc);
					table.find('.labelTaxesConcept').text('$ '+Number(taxesConcept).toFixed(2));
					table.find('.labelRetention').text('$ '+Number(retentionConcept).toFixed(2));
					table.find('.labelImport').text('$ '+Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept).toFixed(2));
					table.find('.ttotal').val(((cant*precio)+ivaCalc));
					table.find('.tamount').val(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
					table.append(nameAmounts);
					table.append(amountsAA);
					table.append(nameRetentions);
					table.append(amountsRetentions);
					$('#body').append(table);
					$('input[name="quantity"]').removeClass('error').val("");
					$('input[name="description"]').removeClass('error').val("");
					$('input[name="price"]').removeClass('error').val("");
					$('input[name="iva_kind"]').prop('checked',false);
					$('input[name="additional_exist"]').prop('checked',false);
					$('input[name="retention_new"]').prop('checked',false);
					$('#iva_no').prop('checked',true);
					$('input[name="amount"]').val("");
					$('input[name="unit"]').val("");
					$('#newsImpuestos').empty();
					$('#newsRetention').empty();
					$('.additionalName').val('');
					$('.additionalAmount').val('');
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
			$(this).parents('.tr').remove();
			total_cal();
			countB = $('.tr').length;
			$('.tr').each(function(i,v)
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
			unit				= $('input[name="unit"]').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			if (cant == "" || descr == "" || precio == "" || unit == "") 
			{
				tquanty		= $(this).parents('.tr').find('.tquanty').val();
				tunit		= $(this).parents('.tr').find('.tunit').val();
				tdescr		= $(this).parents('.tr').find('.tdescr').val();
				tivakind	= $(this).parents('.tr').find('.tivakind').val();
				tprice		= $(this).parents('.tr').find('.tprice').val();
				t_amount	= $(this).parents('.tr').find('.tamount').val();
				
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
						$('input[name="unit"]').val(tunit);
						$('input[name="description"]').val(tdescr);
						$('input[name="price"]').val(tprice);
						$('input[name="amount"]').val(t_amount);
						
						$(this).parents('.tr').remove();
						total_cal();
						countB = $('.tr').length;
						$('.tr').each(function(i,v)
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
		
		.on('click','.button-search', function()
		{
			client_search();
		})
		.on('click','.pagination a', function(e)
		{
			e.preventDefault();
			href	=	$(this).attr('href');
			url		=	new URL(href);
			params	=	new URLSearchParams(url.search);
			page	=	params.get('page');
			client_search(page);
		})
		.on('change','.js-enterprises',function()
		{
			idEnterprise	= $(this).val();
			folio			= $('#id'+idEnterprise).text();
			$('#efolio').val(folio);
			if (idEnterprise != '') 
			{
				$('.resultbank').stop().fadeIn();
			}
			else
			{
				$('.resultbank').stop().fadeOut();
			}
			$text = $('#efolio').val();

			if(idEnterprise != "" && idEnterprise != undefined)
			{
				$.ajax({
					type : 'post',
					url  : '{{ route("income.search.bank") }}',
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
			}
		})
		.on('click', '.edit', function()
		{
			$('#cp').val(null).trigger('change');
			encodedString	= $('#client_'+$(this).val()).val();
			decodedString	= Base64.decode(encodedString);
			json			= JSON.parse(decodedString);
			reasonTemp		= {'oldReason':json.client.businessName};
			rfcTemp			= {'oldRfc':json.client.rfc};
			$('input[name="idClient"]').val(json.client.idClient);
			$('input[name="reason"]').val(json.client.businessName).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(reasonTemp));
			$('input[name="address"]').val(json.client.address).prop('disabled',true);
			$('input[name="number"]').val(json.client.number).prop('disabled',true);
			$('input[name="colony"]').val(json.client.colony).prop('disabled',true);
			$('#cp').append(new Option(json.client.postalCode, json.client.postalCode, true, true)).trigger('change').prop('disabled',true);
			$('input[name="city"]').val(json.client.city).prop('disabled',true);
			$('.js-state').val(json.client.state_idstate).prop('disabled',true);
			$('input[name="rfc"]').val(json.client.rfc).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(rfcTemp));
			$('input[name="phone"]').val(json.client.phone).prop('disabled',true);
			$('input[name="contact"]').val(json.client.contact).prop('disabled',true);
			$('input[name="email"]').val(json.client.email).prop('disabled',true);
			$('input[name="other"]').val(json.client.commentaries).prop('disabled',true);
			$('input[name="edit"]').prop('checked',false);
			$(".checks").show();
			$('#banks').hide();
			$('#form-prov').stop().fadeIn();
			$('.client').stop().fadeOut();
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-state",
						"placeholder"				=> "Seleccione un estado",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		})
		.on('click','#closeFormProv',function()
		{
			$('#form-prov').stop().fadeOut();
			$('.client').stop().fadeIn();
		})
		.on('click','.checkbox',function()
		{
			$('.idchecked').val('0');
			$('.marktr').removeClass('marktr');
			$(this).parents('tr').addClass('marktr');
			$(this).parents('tr').find('.idchecked').val('1');
		})
		.on('click','[name="addDoc"]',function()
		{
			@php
				$options = collect(
					[
						["value"	=>	"Cotización",			"description"	=>	"Cotización"],
						["value"	=>	"Ficha Técnica",		"description"	=>	"Ficha Técnica"],
						["value"	=>	"Control de Calidad",	"description"	=>	"Control de Calidad"],
						["value"	=>	"Contrato",				"description"	=>	"Contrato"],
						["value"	=>	"Factura",				"description"	=>	"Factura"],
						["value"	=>	"Ticket",				"description"	=>	"Ticket"],
						["value"	=>	"Otro",					"description"	=>	"Otro"]
					]
				);
				$newDoc = html_entity_decode((String)view('components.documents.upload-files',[
					"attributeExInput"	=>	"name=\"path\" accept=\".pdf,.jpg,.png\"",
					"componentsExUp"	=>
						[
							["kind"	=>	"components.labels.label",	"classEx"		=>	"font-bold",				"label"		=>	"Tipo de documento"],
							["kind"	=>	"components.inputs.select",	"attributeEx"	=>	"name=\"nameDocument[]\" data-validation=\"required\"",	"classEx"	=>	"nameDocument",	"options"	=>	$options], 
						],
					"classExRealPath"		=>	"path",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExInput"			=>	"inputDocs pathActioner",
					"classExDelete"			=>	"delete-doc",
				]));
			@endphp

			newDoc			=	'{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			containerNewDoc	=	$(newDoc);
			$('#documents').append(containerNewDoc);
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$('#documents').removeClass('hidden');
			@php
				$selects = collect([
					[
						"identificator"				=> ".nameDocument",
						"placeholder"				=> "Seleccione el tipo de documento",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('click','#help-btn-select-client',function()
		{
			swal('Ayuda','En este apartado debe seleccionar un cliente. De clic en "Buscar" si va a tomar un cliente que ya existe. De click en "Nuevo" si desea agregar un cliente en caso de no encontrarlo en el buscador.','info');
		})
		.on('click','#help-btn-account-bank',function()
		{
			swal('Ayuda','En este apartado debe seleccionar una cuenta bancaria del cliente. Dé click en el icono que se encuentra al final de cada cuenta para seleccionarla.','info');
		})
		.on('click','#help-btn-dates',function()
		{
			swal('Ayuda','En este apartado debe agregar cada uno de los conceptos pertenecientes al pedido.','info');
		})
		.on('click','#help-btn-condition-pay',function()
		{
			swal('Ayuda','En este apartado debe agregar las condiciones de pago.','info');
		})
		.on('change','.inputDocs.pathActioner',function(e)
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
					url			: '{{ route("income.upload") }}',
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
			uploadedName	= $(this).parents('docs-p').find('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("income.upload") }}',
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
		});
	});
	
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
		$('input[name="subtotal"]').val(Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val(Number(iva).toFixed(2));
		$('input[name="total"]').val(Number(total).toFixed(2));
		$(".amount_total").val(Number(total).toFixed(2));
		$('input[name="amountAA"]').val(Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val(Number(amountR).toFixed(2));
		$('.subtotalLabel').text('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalivaLabel').text('$ '+Number(iva).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalLabel').text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.amountAALabel').text('$ '+Number(amountAA).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.amountRLabel').text('$ '+Number(amountR).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
	}
	
	function client_search(page)
	{
		$text = $('#input-search').val().trim();
		if ($text == "")
		{
			($('.flag-not-found').length > 0) ? $('.flag-not-found').remove() : "";
			@php
				$notfound = view('components.labels.not-found', ["classEx" => "flag-not-found", "text" => "No se encontraron clientes registrados"])->render();
			@endphp
			tableRelation = '{!!preg_replace("/(\r)*(\n)*/", "", $notfound)!!}';
			$('#not-found').stop().show();
			$('#not-found').append(tableRelation);
			$('#table-client').stop().hide();
		}
		else
		{
			$('#not-found').stop().hide();
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("income.create.client") }}',
				data	: {'search':$text},
				success	: function(data)
				{
					$('.client').html(data).show();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.client').html('').hide();
				}
			}); 
		}
	}
	function taxesRetention()
	{
		cant			= $('input[name="quantity"]').val();
		precio			= $('input[name="price"]').val();
		iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
		iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
		ivaCalc			= 0;
		taxAditional	= 0;
		retention 		= 0;
		taxes 			= 0;
		retentions		= 0;
		if($('input[name="additional"]:checked').val() == 'si')
		{
			$('.additionalAmount').each(function()
			{ 
				if($(this).val())
				{
					taxAditional+=parseFloat($(this).val()); 
				} 
			});
		}
		if($('input[name="retention"]:checked').val() == 'si')
		{
			$('.retentionAmount').each(function(){
				if($(this).val())
				{
					retention	+=parseFloat($(this).val()); 
				} 
			});
		}
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
			retentions=0;
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
			taxes=0; retentions=0;
		}
		else
		{
			taxes = parseFloat(taxes); retentions= parseFloat(retentions);
		}	
		totalImporte    = ((cant * precio)+ivaCalc)+taxAditional-retention;
		$('input[name="amount"]').val(totalImporte.toFixed(2));
	}
</script>
@endsection
