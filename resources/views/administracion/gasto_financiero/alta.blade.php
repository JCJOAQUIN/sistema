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
	@if(isset($requests) && $requests->status == 2)
		@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('finance.update',$requests->folio)."\"", "methodEx" => "PUT"])
	@elseif(isset($requests) && isset($action) && $action == 'new')
		@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('finance.store')."\""])
	@else
		@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('finance.store')."\""])
	@endif
		@if(isset($requests))
			@component('components.labels.title-divisor') solicitud @endcomponent
		@else
			@component('components.labels.title-divisor') Nueva solicitud @endcomponent
		@endif
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component("components.inputs.input-text")
					@slot('classEx') remove @endslot
					@slot('attributeEx')
						type="text" name="title" placeholder="Ingrese el título" data-validation="required"
						@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
							data-validation="required"
						@elseif(isset($requests)) 
							disabled 
						@endif
						@if(isset($requests)) value="{{$requests->finance->title}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component("components.inputs.input-text")
					@slot('classEx') datepicker2 remove @endslot
					@slot('attributeEx')
						type ="text" name ="datetitle"
						@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
							data-validation="required" placeholder="Ingrese una fecha" readonly="readonly"
						@elseif(isset($requests))
							disabled
						@endif
						@if(isset($requests)) value="{{ $requests->finance->datetitle!="" ? Carbon\Carbon::createFromFormat('Y-m-d',$requests->finance->datetitle)->format('d-m-Y') : null}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal: @endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
					@slot('classExLabel')
						@if((isset($requests) && $requests->status!=2) && !isset($action)) 
							disabled
						@endif
					@endslot
					@slot('attributeEx')
							type="radio" id="nofiscal" name="fiscal" value="0"
							@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
								data-validation="required"
							@else
								disabled
							@endif
							@if(isset($requests) && $requests->taxPayment=='0') checked="checked" @elseif(!isset($requests)) checked="checked" @endif
						@endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('classExLabel')
							@if((isset($requests) && $requests->status!=2) && !isset($action)) 
								disabled
							@endif
						@endslot
						@slot('attributeEx')
							type="radio" id="fiscal" name="fiscal" value="1"
							@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
								data-validation="required"
							@else
								disabled
							@endif
							@if(isset($requests) && $requests->taxPayment=='1') checked="checked" @endif
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo: @endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('classExLabel')
							@if((isset($requests) && $requests->status!=2) && !isset($action)) 
								disabled
							@endif
						@endslot
						@slot('attributeEx')
							type="radio" name="kind" value="Interés" id="interest"
							@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
								data-validation="required"
							@else 
								disabled 
							@endif
							@if(isset($requests) && $requests->finance->kind=='Interés') 
								checked
							@elseif(!isset($requests)) 
								checked 
							@endif
						@endslot
						Interés
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('classExLabel')
							@if((isset($requests) && $requests->status!=2) && !isset($action)) 
								disabled
							@endif
						@endslot
						@slot('attributeEx')
							type="radio" name="kind" id="commission" value="Comisión"
							@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
								data-validation="required"
							@else 
								disabled 
							@endif 
							@if(isset($requests) && $requests->finance->kind=='Comisión') 
								checked 
							@endif
						@endslot
						Comisión
					@endcomponent
				</div>
			</div>
			@php
				 $options	=	collect();
				 if (isset($requests) && $requests->idRequest != "")
				 {
					$names		=	App\User::find($requests->idRequest);
					$options	=	$options->concat([['value'=>$requests->idRequest, 'selected'=>'selected', 'description'=>$names->fullName()]]);
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	'disabled';
				} 
				$attributeEx	=	"name=\"userid\" id=\"multiple-users\" ".$attr;
				$classEx		=	'js-users removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@component('components.inputs.select', ['options' => $options,'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options = collect();
				foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
				{
					if(isset($requests) && $requests->idArea == $area->id)
					{
						$options	=	$options->concat([['value'=>$area->id, 'selected'=>'selected', 'description'=>$area->name]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$area->id, 'description'=>$area->name]]);
					}
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"areaid\" id=\"multiple-areas\" ".$attr;
				$classEx		=	'js-areas removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options	=	collect();
				foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
				{			
					$description	=	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
					if(isset($requests) && $requests->idEnterprise == $enterprise->id)
					{
						$options	=	$options->concat([['value'=>$enterprise->id, 'selected'=>'selected', 'description'=>$description]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$enterprise->id, 'description'=>$description]]);
					}
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"enterpriseid\" id=\"multiple-enterprises\" ".$attr;
				$classEx		=	'js-enterprises removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					$options = collect();
					if (isset($requests) && $requests->account!="")
					{
						$options	=	$options->concat([["value"	=>	$requests->accounts->idAccAcc,	"description"	=>	$requests->accounts->account." - ".$requests->accounts->description." (".$requests->accounts->content.")",	"selected"	=>	"selected"]]);
					}

					if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
					{ 
						$attr	=	"data-validation=\"required\"";
					}
					else
					{ 
						$attr	=	"disabled";
					}
					$attributeEx	=	"name=\"accountid\" id=\"multiple-accounts\" ".$attr;
					$classEx		=	'js-accounts removeselect';
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options = collect();
				foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
				{			
					if(isset($requests) && $requests->idDepartment == $department->id)
					{
						$options	=	$options->concat([['value'=>$department->id, 'selected'=>'selected', 'description'=>$department->name]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$department->id, 'description'=>$department->name]]);
					}
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"departmentid\" id=\"multiple-departments\" ".$attr;
				$classEx		=	'js-departments removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options	=	collect();
				if (isset($requests) && $requests->idProject !="")
				{
					$options	=	$options->concat([['value'	=>	$requests->requestProject->idproyect, 'selected'=>'selected', 'description'	=>	$requests->requestProject->proyectName]]);
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"projectid\" id=\"multiple-projects\" ".$attr;
				$classEx		=	'js-projects removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx') remove @endslot
					@slot('attributeEx')
						type="text"
						name="date"
						step="1"
						placeholder="Ingrese una fecha"
						id="datepicker"
						@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
							data-validation="required"
							readonly 
						@else 
							disabled 
						@endif
						@if(isset($requests)) value="{{ $requests->PaymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$requests->PaymentDate)->format('d-m-Y') : '' }}" @endif
					@endslot
				@endcomponent
			</div>
			@php
				$options	=	collect();
				$values		=	["Cargo Automático", "Transferencia"];
				foreach($values as $item)
				{
					if(isset($requests) && $requests->finance->paymentMethod == $item)
					{
						$options	=	$options->concat([['value'=>$item, 'selected'=>'selected', 'description'=>$item]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$item, 'description'=>$item]]);
					}
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{ 
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"payment_method\" id=\"multiple-payment-method\" ".$attr;
				$classEx		=	'js-payment-method removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Método de pago: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options	=	collect();
				if (isset($requests) && $requests->finance->bank !="")
				{
					$options	=	$options->concat([['value'=>$requests->finance->banks->idBanks, 'description'	=>	$requests->finance->banks->description, "selected" => "selected"]]);
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{
					$attr	=	"disabled";
				}
				$attributeEx	=	"name=\"bank\" ".$attr;
				$classEx		=	'js-bank removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Banco: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options	=	collect();
				if (isset($requests) && $requests->finance->account !="")
				{
					$options	=	$options->concat([['value'=>$requests->finance->bankAccount->id, "selected" => "selected", 'description'=>$requests->finance->bankAccount->alias." - ".$requests->finance->bankAccount->account]]);
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"";
				}
				else
				{
					$attr	=	"disabled";
				}
				$attributeEx	=	"id=\"bank_account\" name=\"bank_account\" ".$attr;
				$classEx		=	'removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') **Cuenta: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			<div class="col-span-2 mb-4 @if(!isset($requests) || (isset($requests) && $requests->taxPayment == '0')) hidden @endif ">
				@component('components.labels.label')
					@slot("attributeEx") id="label-inline" @endslot
					Tipo de IVA:
				@endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('classEx') iva_kind @endslot
						@slot('attributeEx')
							type="radio" name="iva_kind" value="no" title="No IVA" id="iva_no"
							@if((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif 
							@if(!isset($requests)) checked @elseif(isset($requests) && $requests->finance->taxType == "no") checked @endif
						@endslot
						@slot('classExLabel')
							@if ((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif
							inline-block mb-0 
						@endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('classEx') iva_kind @endslot
						@slot('attributeEx')
						type="radio" name="iva_kind" value="a" title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%" id="iva_a"
							@if((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif 
							@if(isset($requests) && $requests->finance->taxType == "a") checked @endif
						@endslot
						@slot('classExLabel')
							@if ((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif
							inline-block
						@endslot
						A
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('classEx') iva_kind @endslot
						@slot('attributeEx')
							type="radio" name="iva_kind" value="b" title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%" id="iva_b"
							@if((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif 
							@if(isset($requests) && $requests->finance->taxType == "b") checked @endif
						@endslot
						@slot('classExLabel')
							@if ((isset($requests) && $requests->status!=2) && !isset($action)) disabled @endif
							inline-block
						@endslot
						B
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx') type="hidden" name="iva" @endslot
						@slot('value')
							@if(isset($requests) && $requests->status == 2) {{$requests->finance->tax}} @endif
						@endslot
					@endcomponent
				</div>
			</div>
			@php
				$options	=	collect();
				foreach(App\CreditCards::where('principal_aditional',1)->get() as $credit)
				{
					$description	=	$credit->alias.' - '.$credit->credit_card;
					if(isset($requests) && $requests->finance->card == $credit->idcreditCard)
					{
						$options	=	$options->concat([['value'=>$credit->idcreditCard, "selected" => "selected", 'description'=>$description]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$credit->idcreditCard, 'description'=>$description]]);
					}
				}
				$attr = ((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) ? "" : "disabled";
				$attributeEx	=	"id=\"bank_card\" name=\"bank_card\" ".$attr;
				$classEx		=	'removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') **Tarjeta: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			@php
				$options	=	collect();
				$conditions	=	["MXN", "USD", "EUR", "Otro"];
				foreach($conditions as $item)
				{
					if(isset($requests) && $requests->finance->currency==$item)
					{
						$options	=	$options->concat([['value'=>$item, "selected" => "selected", 'description'=>$item]]);
					}
					else
					{
						$options	=	$options->concat([['value'=>$item, 'description'=>$item]]);
					}
				}
				if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
				{ 
					$attr	=	"data-validation=\"required\"";
				}
				else
				{
					$attr	=	"disabled";
				}
				$attributeEx	=	"id=\"currency\" name=\"currency\" ".$attr;
				$classEx		=	'removeselect';
			@endphp
			<div class="col-span-2">
				@component('components.labels.label') Moneda: @endcomponent
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Subtotal: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx') remove @endslot
					@slot('attributeEx')
						type="text" name="subtotal" step=".01" placeholder='Ingrese el subtotal'
						@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
							data-validation="required number"
							data-validation-allowing="float"
						@else 
							disabled 
						@endif
						@if(isset($requests)) value="{{ $requests->finance->subtotal }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe: @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx') amount remove @endslot
					@slot('attributeEx')
						@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new')) 
							readonly
						@else 
							disabled
						@endif 
						@if(isset($requests)) value="{{$requests->finance->amount}}" @endif
						type="text" name="amount" placeholder="Ingrese el importe"
					@endslot
				@endcomponent
			</div>
			@if((isset($requests) && !isset($action) && $requests->finance->week != ''))
				<div class="col-span-2">
					@component('components.labels.label') Semana: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							remove
						@endslot
						@slot('attributeEx')
							@if (isset($requests) && $requests->status!=2)
								disabled
							@endif
							type="text" readonly rows="4" value="{{ $requests->finance->week }}"
						@endslot
					@endcomponent
				</div>
			@endif
			<div class="col-span-2">
				@component('components.labels.label') Notas (Opcional): @endcomponent
				@component('components.inputs.text-area')
					@slot('classEx')
						remove
					@endslot
					@slot('attributeEx')
						rows="6" name="notes"
						@if(isset($requests) && $requests->status!=2 && !isset($action)) disabled @endif
					@endslot
					@if(isset($requests)){{$requests->finance->note}}@endif
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') **Debe ingresar al menos un campo @endcomponent
			</div>
		@endcomponent
		@if(isset($requests) && $requests->idCheck != "" && !isset($action))
			<div class="block overflow-auto w-full text-left mt-12">
				@component('components.labels.title-divisor') DATOS DE REVISIÓN @endcomponent
				@php
					$accountDescription	=	"";
					$labelDescription	=	"";
					if ($requests->idEnterpriseR!="")
					{
						$reviewAccount = App\Account::find($requests->accountR);
						if (isset($reviewAccount->account))
						{
							$accountDescription	=	$reviewAccount->account." - ".$reviewAccount->description;
						}
						else
						{
							$accountDescription	=	"No hay";
						}
					}
					foreach ($requests->labels as $label)
					{
						$labelDescription	=	$label->description;
					}
					$modelTable	=
					[
						"Revisó"					=>	$requests->reviewedUser->name." ".$requests->reviewedUser->last_name." ".$requests->reviewedUser->scnd_last_name,
						"Nombre de la Empresa"		=>	$requests->idEnterpriseR!="" ? App\Enterprise::find($requests->idEnterpriseR)->name : "",
						"Nombre del Departamento"	=>	$requests->idEnterpriseR!="" ? App\Department::find($requests->idDepartamentR)->name : "",
						"Nombre de la Dirección"	=>	$requests->idEnterpriseR!="" ? $requests->reviewedDirection->name : "",
						"Clasificación del gasto"	=>	$accountDescription,
						"Nombre del Proyecto"		=>	$requests->idEnterpriseR!="" ? $requests->reviewedProject->proyectName : "",
						"Etiquetas"					=>	$labelDescription,
						"Comentarios"				=>	$requests->checkComment == "" ? "Sin comentarios" : htmlentities($requests->checkComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
				@endcomponent
			</div>
		@endif
		@if(isset($requests) && $requests->idAuthorize != "" && !isset($action))
			<div class="block overflow-auto w-full text-left mt-24">
				@component('components.labels.title-divisor') DATOS DE AUTORIZACIÓN @endcomponent
				@php
					$modelTable	=
					[
						"Autorizó"		=>	$requests->authorizedUser->name." ".$requests->authorizedUser->last_name." ".$requests->authorizedUser->scnd_last_name,
						"Comentarios"	=>	$requests->authorizeComment == "" ? "Sin comentarios" : htmlentities($requests->authorizeComment),
					];
				@endphp
				@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
				@endcomponent
			</div>
		@endif
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center">
			@if((isset($requests) && $requests->status==2) || !isset($requests))
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "classEx" => "enviar", "label" => "ENVIAR SOLICITUD"]) @endcomponent
				@if(isset($requests))
					@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "type=\"submit\" name=\"save\" id=\"save\" data-validation-skipped=\"1\"  formaction=\"".route('finance.update.only',$requests->folio)."\"", "classEx" => "save", "label" => "GUARDAR SIN ENVIAR"]) @endcomponent
				@else
					@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "type=\"submit\" name=\"save\" id=\"save\" data-validation-skipped=\"1\" formaction=\"".route('finance.unsent')."\"", "classEx" => "save", "label" => "GUARDAR SIN ENVIAR"]) @endcomponent
				@endif
				
				@php
					$link	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$link."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
			@elseif(isset($requests) && isset($action) && $action == 'new')
				@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "classEx" => "enviar", "label" => "ENVIAR SOLICITUD"]) @endcomponent
				@component('components.buttons.button', ["variant" => "secondary", "attributeEx" => "type=\"submit\" name=\"save\" id=\"save\" data-validation-skipped=\"1\" formaction=\"".route('finance.unsent')."\"", "classEx" => "save", "label" => "GUARDAR SIN ENVIAR"]) @endcomponent
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\"", "classEx" => "btn-delete-form", "label" => "Borrar campos"]) @endcomponent
			@else
				@php
					$link	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$link."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
			@endif
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
		function validate()
		{
			$.validate(
			{
				form	: '#container-alta',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.removeselect[name="bank_account"]').val() == '' && $('.removeselect[name="bank_card"]').val() == '')
					{
						swal('','Al menos debe seleccionar un campo: Cuenta/Tarjeta','error');
						return false;
					}
					else if(Number($('.remove[name="subtotal"]').val()) <= 0)
					{
						swal('','El subtotal no puede ser menor o igual a cero','error');
						$('[name="subtotal"]').addClass('error');
						return false;
					}
					else
					{
						return true;
					}
				}
			});
		}
	@endif
	$(document).ready(function()
	{
		@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
			validate();
		@endif
		generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 3});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-bank', 'model': 27});
		generalSelect({'selector': '.js-projects', 'model': 21});
		generalSelect({'selector': '#bank_account', 'model': 40});
		@php
			$selects = collect([
				[
					"identificator"				=> "#currency",
					"placeholder"				=> "Seleccione el tipo de moneda",
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
					"identificator"				=> ".js-areas",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departments",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-payment-method",
					"placeholder"				=> "Seleccione el método de pago",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-money",
					"placeholder"				=> "Seleccione wl tipo de moneda",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-currency_p",
					"placeholder"				=> "Seleccione el tipo de moneda",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> "#bank_card",
					"placeholder"				=> "Seleccione una tarjeta",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$('input[name="subtotal"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="bank_account"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="bank_card"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
			$(function() 
			{
				$("#datepicker").datepicker({dateFormat: "dd-mm-yy" });
				$(".datepicker2").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			});
		@endif
		$('.js-payment-method').change( function() 
		{
			valuePayment = $('select[name="payment_method"] option:selected').val();
			$('.js-bank').prop('disabled', false);
		});
		@if((isset($requests) && $requests->status==2) || !isset($requests) || (isset($requests) && isset($action) && $action == 'new'))
			$(document).on('click','#save',function()
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.removeselect').removeClass('removeselect');
				$('.request-validate').removeClass('request-validate');
			})
			.on('change','input[name="fiscal"]',function()
			{
				
				if ($('input[name="fiscal"]:checked').val() == "1") 
				{
					$('.iva_kind').prop('disabled',false);
					$('#iva_no').prop('checked',true);
					$('.iva_kind').parent('div').parent('div').parent('div').stop(true,true).fadeIn();
				}
				else if ($('input[name="fiscal"]:checked').val() == "0") 
				{
					$('.iva_kind').prop('disabled',true);
					$('#iva_no').prop('checked',true);
					$('.iva_kind').parent('div').parent('div').parent('div').stop(true,true).fadeOut();
				}
				precio	= Number($('input[name="subtotal"]').val());
				iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc	= 0;

				switch($('input[name="iva_kind"]:checked').val())
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = precio*iva;
						break;
					case 'b':
						ivaCalc = precio*iva2;
						break;
				}
				totalImporte	= (precio+ivaCalc);
				$('input[name="amount"]').val(totalImporte.toFixed(2));
				$('input[name="iva"]').val(ivaCalc.toFixed(2));
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
						$('.removeselect').val(null).trigger('change');
						$('.remove').val("");
						$('#nofiscal').attr('checked','checked');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','input[name="subtotal"],.iva_kind',function()
			{
				precio	= Number($('input[name="subtotal"]').val());
				iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc	= 0;

				switch($('input[name="iva_kind"]:checked').val())
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = precio*iva;
						break;
					case 'b':
						ivaCalc = precio*iva2;
						break;
				}
				totalImporte	= (precio+ivaCalc);
				$('input[name="amount"]').val(totalImporte.toFixed(2));
				$('input[name="iva"]').val(ivaCalc.toFixed(2));
			})
			.on('change','.js-enterprises',function()
			{
				$('.js-accounts').empty();
			});
		@endif
	});
	@if(isset($alert))
		{!! $alert !!}
	@endif
</script>
@endsection
