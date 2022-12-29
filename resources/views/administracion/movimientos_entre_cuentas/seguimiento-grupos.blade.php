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
		$taxesCount	=	$taxesCountBilling = 0;
		$taxes		=	$retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	@component('components.forms.form', ["attributeEx" => "action=\"".route('movements-accounts.groups.follow.update', $request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT", "files" => true])
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			FORMULARIO DE GRUPOS
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="title" placeholder="Ingrese un título" data-validation="required" @if(isset($request) && isset($request->groups->first()->title)) value="{{ $request->groups->first()->title }}" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 gri grid-cols-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="datetitle" @if(isset($request) && isset($request->groups->first()->datetitle)) value="{{ $request->groups->first()->datetitle != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y') : null }}" @endif placeholder="Ingrese la fecha" data-validation="required" readonly="readonly"
					@endslot
					@slot('classEx')
						removeselect datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fiscal: @endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							@if($request->status!=2) disabled="disabled" @endif type="radio" name="fiscal" id="nofiscal" value="0" @if(isset($request) && $request->taxPayment==0) checked @endif
						@endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx')
							@if($request->status !=2) disabled="disabled" @endif type="radio" name="fiscal" id="fiscal" value="1" @if(isset($request) && $request->taxPayment==1) checked @endif
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Número de Orden (Opcional): @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="numberOrder" placeholder="Ingrese el número de orden" @if(isset($request) && isset($request->groups->first()->numberOrder)) value="{{ $request->groups->first()->numberOrder }}" @endif
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
					if (isset($request) && $request->idRequest != "")
					{
						$options	=	$options->concat([["value" => $request->requestUser->id, "description" => $request->requestUser->fullname(),	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif name="userid" multiple="multiple"  data-validation="required"
					@endslot
					@slot('classEx')
						js-users removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de operación: @endcomponent
				@php
					$options	=	collect();
					$values		=	["Entrada", "salida"];
					foreach ($values as $value)
					{
						if (isset($request->groups->first()->operationType) && $request->groups->first()->operationType == $value)
						{
							$options	=	$options->concat([["values"	=>	$value, "description"	=>	$value, "selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["values"	=>	$value, "description"	=>	$value]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif name="typeOperation" multiple="multiple"  data-validation="required"
					@endslot
					@slot('classEx')
						js-type removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			CUENTA DE ORIGEN
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options	=	collect();
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->groups()->exists() && $request->groups->first()->idEnterpriseOrigin == $enterprise->id)
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id, "description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id, "description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif class="" name="enterpriseid_origin" multiple="multiple"  data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises-origin removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent
				@php
					$options	=	collect();
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->groups()->exists() && $request->groups->first()->idAreaOrigin == $area->id)
						{
							$options	=	$options->concat([["value"	=>	$area->id,	"description"	=>	$area->name, "selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$area->id,	"description"	=>	$area->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="areaid_origin"  data-validation="required"
					@endslot
					@slot('classEx')
						js-areas-origin removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@php
					$options	=	collect();
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->groups()->exists() && $request->groups->first()->idDepartamentOrigin == $department->id)
						{
							$options	=	$options->concat([["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$department->id,	"description"	=>	$department->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="departmentid_origin"  data-validation="required"
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
					if (isset($request) && $request->groups()->exists() && $request->groups->first()->idAccAccOrigin != "")
					{
						$options	=	$options->concat([["value"	=>	$request->groups->first()->accountOrigin->idAccAcc,	"description"	=>	$request->groups->first()->accountOrigin->account." - ".$request->groups->first()->accountOrigin->description." (".$request->groups->first()->accountOrigin->content.")", "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_origin"  data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-origin removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$options	=	collect();
					$project	=	isset($request->groups->first()->idProjectOrigin)  ? App\Project::find($request->groups->first()->idProjectOrigin) : "";
					if ($request->groups()->exists() && $request->groups->first()->idProjectOrigin !="")
					{
						$options	=	$options->concat([["value"	=>	$project->idproyect,	"description"	=>	$project->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif name="projectid_origin" multiple="multiple"  data-validation="required"
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
			CUENTA DESTINO
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options	=	collect();
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->groups()->exists() && $request->groups->first()->idEnterpriseDestiny == $enterprise->id)
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif name="enterpriseid_destination" multiple="multiple"  data-validation="required"
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
					if (isset($request) && $request->groups()->exists() && $request->groups->first()->idAccAccDestiny != "")
					{
						$options	=	$options->concat([["value"	=>	$request->groups->first()->accountDestiny->idAccAcc,	"description"	=>	$request->groups->first()->accountDestiny->account." - ".$request->groups->first()->accountDestiny->description." (".$request->groups->first()->accountDestiny->content.")", "selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="accountid_destination"  data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-destination removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			SELECCIONAR PROVEEDOR
		@endcomponent
		<div class="flex flew-row justify-center mt-5">
			@component("components.buttons.button-approval")
				@slot('classExContainer') mr-2 @endslot
				@slot('attributeEx')
					@if($request->status!=2) disabled="disabled" @endif type="radio" name="prov" id="new-prov" value="nuevo"
				@endslot
				Nuevo
			@endcomponent
			@component("components.buttons.button-approval")
				@slot('classExContainer') mr-2 @endslot
				@slot('attributeEx')
					@if($request->status!=2) disabled="disabled" @endif type="radio" name="prov" id="buscar-prov" value="buscar" @if(isset($request)) checked="checked" @endif
				@endslot
				Buscar
			@endcomponent
		</div>
		<div class="text-center w-full pb-2">
			<div class="@if($request->status == 2) block @else hidden @endif" id="buscar">
				<div id="container-cambio" class="px-2 md:px-56">
					@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" id="pagePagination" value="1"
					@endslot
					@endcomponent
					@component('components.inputs.input-search') 
						Buscar Proveedor
						@slot('attributeExInput')
							name="search" 
							id="input-search"
							placeholder="Ingrese un proveedor" 
						@endslot
						@slot('attributeExButton')
							type="button"
						@endslot
						@slot('classExButton')
							button-search
						@endslot
					@endcomponent
				</div>
				<div class="table-responsive provider"> </div>
				<div id="not-found"> </div>
			</div>
		</div>
		<div id="form-prov" class="request-validate @if(isset($request->groups->first()->idProvider) && ($request->groups->first()->provider->status!=1 || $request->status != 2)) block @else hidden @endif">
			<div class="text-center mt-5">
				@component('components.labels.label') - DATOS DEL PROVEEDOR - @endcomponent
				<div class="checks mt-4">
					@component("components.inputs.switch")
						@slot('attributeEx')
							@if($request->status!=2)
								name="edit" type="checkbox" value="1" id="edit" disabled="disabled"
							@elseif(isset($request->groups->first()->provider) && $request->groups->first()->provider->status==0)
								name="edit" type="checkbox" value="1" id="edit" checked
							@else
								name="edit" type="checkbox" value="1" id="edit"
							@endif
						@endslot
						@slot('forvalue')
							edit
						@endslot
							Habilitar edición
					@endcomponent
					<span class="help-btn"></span>
				</div>
			</div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Razón Social: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="reason" placeholder="Ingrese la razón social" data-validation="length required server" data-validation-length="max150" data-validation-url="{{ url('configuration/provider/validate') }}" @if(isset($request->groups->first()->provider)) data-validation-req-params="{{ json_encode(array('oldReason'=>$request->groups->first()->provider->businessName)) }}" value="{{ $request->groups->first()->provider->businessName }}"  @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="idProvider" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->idProvider }}" @endif
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Calle: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->address }}"  @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Número: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="number" placeholder="Ingrese un número" data-validation="required length" data-validation-length="max45" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->number }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Colonia: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->colony }}"  @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Código postal: @endcomponent
						@php
							$options	=	collect();
							if (isset($request) && isset($request->groups->first()->provider) && $request->groups->first()->provider->postalCode != "")
							{
								$options	=	$options->concat([["value" => $request->groups->first()->provider->postalCode, "description" => $request->groups->first()->provider->postalCode, "selected" => "selected"]]);
							}
						@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="cp" id="cp" placeholder="Ingrese el código postal" multiple="multiple" data-validation="required" @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled @endif
						@endslot
						@slot('classEx')
							remove cp
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Ciudad: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="city" class="" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->city }}"  @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Estado: @endcomponent
					@php
						$options	=	collect();
						foreach (App\State::orderName()->get() as $state)
						{
							if (isset($request->groups->first()->provider) && $request->groups->first()->provider->state_idstate==$state->idstate)
							{
								$options	=	$options->concat([["value"	=>	$state->idstate,	"description"	=>	$state->description,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$state->idstate,	"description"	=>	$state->description]]);
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $options])
						@slot('attributeEx')
							name="state" multiple="multiple" data-validation="required" @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							js-state remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') RFC: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="rfc" placeholder="Ingrese el RFC" data-validation="rfc required server" data-validation-url="{{ url('configuration/provider/validate') }}"  @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->rfc }}" data-validation-req-params="{{ json_encode(array('oldRfc'=>$request->groups->first()->provider->rfc)) }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Teléfono: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="phone" placeholder="Ingrese el teléfono" data-validation="number" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->phone }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							phone remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Contacto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="contact" placeholder="Nombre del contacto" data-validation="required" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->contact }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Beneficiario: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="beneficiary" placeholder="Ingrese el beneficiario" data-validation="required" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->beneficiary }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Otro (opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="other" placeholder="Ingrese Otro" @if(isset($request->groups->first()->provider)) value="{{ $request->groups->first()->provider->commentaries }}" @endif @if($request->status != 2 || (isset($request->groups->first()->provider) && $request->groups->first()->provider->status == 2)) disabled="disabled" @endif
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="form-container block" id="container-data">
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					CUENTAS BANCARIAS <span class="help-btn" id="help-btn-account-bank">
				@endcomponent
				<div id="banks" class="@if(isset($request) && $request->status != 2) hidden @endif">
					@component('components.labels.label')
						Para agregar una cuenta nueva es necesario colocar los siguientes campos:
					@endcomponent
					@component('components.containers.container-form')
						<div class="col-span-2">
							@component('components.labels.label') Banco: @endcomponent
							@component('components.inputs.select')
								@slot('attributeEx')
									multiple="multiple"
								@endslot
								@slot('classEx')
									js-bank
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Alias: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese un alias"
								@endslot
								@slot('classEx')
									alias
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Cuenta bancaria: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese una cuenta bancaria" data-validation="cuenta"
								@endslot
								@slot('classEx')
									account
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Sucursal: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese una sucursal"
								@endslot
								@slot('classEx')
									branch_office
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Referencia: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese la referencia"
								@endslot
								@slot('classEx')
									reference
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') CLABE: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese una CLABE" data-validation="clabe"
								@endslot
								@slot('classEx')
									clabe
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Moneda: @endcomponent
							@php
								$options	=	collect();
								$values		=	["MXN","USD","EUR","Otro"];
								foreach ($values as $value)
								{
									$options	=	$options->concat([["value"	=>	$value, "description"	=>	$value]]);
								}
							@endphp
							@component('components.inputs.select', ["options" => $options, "classEx" => "currency"]) @endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') IBAN: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese el IBAN" data-validation="iban"
								@endslot
								@slot('classEx')
									iban
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') BIC/SWIFT: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese el BIC/SWIFT" data-validation="bic_swift"
								@endslot
								@slot('classEx')
									bic_swift
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Convenio (opcional): @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									type="text" placeholder="Ingrese el convenio"
								@endslot
								@slot('classEx')
									agreement
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('attributeEx')
									type="button" name="add2" id="add2"
								@endslot
								@slot('label')
									<span class="icon-plus"></span>
									<span>Agregar</span>
								@endslot
								@slot('classEx')
									add2
								@endslot
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
							["value"	=>	"Seleccionar"],
							["value"	=>	"Banco"],
							["value"	=>	"Alias"],
							["value"	=>	"Cuenta"],
							["value"	=>	"Sucursal"],
							["value"	=>	"Referencia"],
							["value"	=>	"CLABE"],
							["value"	=>	"Moneda"],
							["value"	=>	"IBAN"],
							["value"	=>	"BIC/SWIFT"],
							["value"	=>	"Convenio"],
						]
					];
					if (isset($request->groups->first()->provider->providerData->providerBank))
					{
						foreach($request->groups->first()->provider->providerData->providerBank as $bank)
						{
							$markTr			= $request->groups->first()->provider_has_banks_id == $bank->id ? "marktr"	:	"";
							$bankIban		= $bank->iban=='' ? "---" : $bank->iban;
							$bankBic_swift	= $bank->bic_swift=='' ? "---" : $bank->bic_swift;
							$bankAgreement	= $bank->agreement=='' ? "---" : $bank->agreement;
							$disabled		= $request->status!=2	?	"disabled" : "";
							$checked		= $request->groups->first()->provider_has_banks_id == $bank->id ? "checked": "";
							$valueGroups	= $request->groups->first()->provider_has_banks_id == $bank->id ? 1 : 0;
							$deleteItem		= isset($globalRequests) ? "disabled" : "";
							$body			=
							[
								"classEx"	=>	$markTr,
								[
									"content"	=>
									[
										[
											"kind"				=>	"components.inputs.checkbox",
											"radio"				=>	"true",
											"attributeEx"		=>	"id=\"id".$bank->id."\" name=\"provider_has_banks_id\" value=\"".$bank->id."\" $checked",
											"classExLabel"		=>	$disabled." check-small request-validate".((isset($globalRequests)) ? " disabled" : ""),
											"classExContainer"	=>	"inline-flex",
											"classEx"			=>	"checkbox",
											"label"				=>	"<span class=icon-check></span>",
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"checked[]\" value=\"".$valueGroups."\"",
											"classEx"		=>	"idchecked"
										],
										[
											"kind"			=>	"components.buttons.button",
											"variant"		=>	"red",
											"classEx"		=>	"hidden",
											"attributeEx"	=>	"type=\"button\" $deleteItem",
											"label"			=>	"<span class=\"icon-x delete-span\"></span>"
										],
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->bank->description!="" ? $bank->bank->description : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"providerBank[]\" value=\"".$bank->id."\"",
											"classEx"		=>	"providerBank"
										],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"bank[]\" value=\"".$bank->banks_idBanks."\""
										],
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->alias!="" ? $bank->alias : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->account!="" ? $bank->account : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->branch!="" ? $bank->branch : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"branch_office[]\" value=\"".$bank->branch."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->reference!="" ? $bank->reference : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"reference[]\" value=\"".$bank->reference."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->clabe!="" ? $bank->clabe : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bank->currency!="" ? $bank->currency : "---"],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"currency[]\" value=\"".$bank->currency."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bankIban],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"iban[]\" value=\"".$bank->iban."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bankBic_swift],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"bic_swift[]\" value=\"".$bank->bic_swift."\""
										]
									]
								],
								[
									"content"	=>
									[
										["label"	=>	$bankAgreement],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" name=\"agreement[]\" value=\"".$bank->agreement."\""
										]
									]
								],
							];
							if (isset($globalRequests))
							{
								if($checked != "")
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
					}
				@endphp
				@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
					@slot('attributeExBody')
						id="banks-body"
					@endslot
					@slot('classEx')
						mt-4
					@endslot
				@endcomponent
			</div>
			<div class="w-full flex justify-center mt-4">
				@component('components.buttons.button', ["variant" => "red", "attributeEx" => "id=\"closeFormProv\" type=\"button\"", "label" => "Cerrar"])
					@slot('attributeEx')
						id="closeFormProv" type="button" @if(isset($globalRequests)) disabled @endif
					@endslot
					Cerrar
				@endcomponent
			</div>
		</div>
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DEL PEDIDO <span class="help-btn" id="help-btn-dates">
		@endcomponent
		@if($request->status==2)
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Cantidad: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="quantity" placeholder="Ingrese la cantidad"
						@endslot
						@slot('classEx')
							quanty
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
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Precio Unitario: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="price" placeholder="Ingrese el precio"
						@endslot
						@slot('classEx')
							price
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 ivaKindContainer">
					@component('components.labels.label') Tipo de IVA: @endcomponent
					<div class="flex flew-row mt-5 @if(isset($requests) && $requests->taxPayment == 0) hidden @endif">
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								type="radio" name="iva_kind" id="iva_no" value="no" checked="" @if(isset($request) && $request->taxPayment == 0) disabled @endif title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
							@endslot
							No
						@endcomponent
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								title="No IVA" type="radio" name="iva_kind" id="iva_a" value="a" @if(isset($request) && $request->taxPayment == 0) disabled @endif title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
							@endslot
							A
						@endcomponent
						@component("components.buttons.button-approval")
							@slot('classExContainer') mr-2 iva_kind @endslot
							@slot('attributeEx')
								type="radio" name="iva_kind" id="iva_b" value="b" @if(isset($request) && $request->taxPayment == 0) disabled @endif
							@endslot
							B
						@endcomponent
					</div>
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional']) @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention']) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							readonly type="text" name="amount" class="" placeholder="Ingrese el importe"
						@endslot
						@slot('classEx')
							amount
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
						@slot('label')
							<span class="icon-plus"></span>
							<span>Agregar concepto</span>
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endif
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
				]
			];
			if ($request->status==2)
			{
				$modelHead[0][]	=	["value"	=>	"Acciones"];
			}
			if (isset($request) && isset($request->groups->first()->detailGroups))
			{
				foreach($request->groups->first()->detailGroups as $key=>$detail)
				{
					$taxesConcept			=	0;
					$retentionConcept		=	0;
					$componenAmount			=	[];
					$componentsExRetention	=	[];
					
					foreach ($detail->taxes as $tax)
					{
						$componenAmount[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional".$taxesCount."\"[] value=\"".$tax->amount."\"",
							"classEx"		=>	"num_amountAdditional"
						];
						$componenAmount[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount".$taxesCount."\"[] value=\"".$tax->name."\"",
							"classEx"		=>	"num_nameAmount"
						];
						$taxesConcept+=$tax->amount;
					}
					$componenAmount[]	=	["label"	=>	"$ ".number_format($taxesConcept,2)];
					foreach ($detail->retentions as $ret)
					{
						$componentsExRetention[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tamountadditional".$taxesCount."\"[] value=\"".$tax->amount."\"",
							"classEx"		=>	"num_amountAdditional"
						];
						$componentsExRetention[]	=
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"type=\"hidden\" name=\"tnameamount".$taxesCount."\"[] value=\"".$tax->name."\"",
							"classEx"		=>	"num_nameAmount"
						];
						$retentionConcept+=$ret->amount;
					}
					$componentsExRetention[]	=	["label"	=>	"$ ".number_format($retentionConcept,2)];
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
							]
						],
						[
							"content"	=>
							[
								["label"	=>	$detail->quantity],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
									"classEx"		=>	"input-table tquanty"
								]
							]
						],
						[
							"content"	=>
							[
								["label"	=>	$detail->unit],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\"",
									"classEx"		=>	"input-table tunit"
								]
							]
						],
						[
							"content"	=>
							[
								["label"	=>	htmlentities($detail->description)],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\"",
									"classEx"		=>	"input-table tdescr"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
									"classEx"		=>	"input-table tivakind"
								]
							]
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".$detail->unitPrice],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
									"classEx"		=>	"input-table tprice"
								]
							]
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".$detail->tax],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
									"classEx"		=>	"input-table tiva"
								]
							]
						],
						[
							"content"	=>	$componenAmount
						],
						[
							"content"	=>	$componentsExRetention
						],
						[
							"content"	=>
							[
								["label"	=>	"$ ".$detail->amount],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->amount."\"",
									"classEx"		=>	"input-table tamount"
								]
							]
						],
					];
					if ($request->status==2)
					{
						$body[]	=
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"success",
									"attributeEx"	=>	"id=\"edit\" type=\"button\"",
									"label"			=>	"<span class=\"icon-pencil\"></span>",
									"classEx"		=>	"edit-item"
								],
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"label"			=>	"<span class=\"icon-x delete-span\"></span>",
									"classEx"		=>	"delete-item",
									"attributeEx"	=>	"type=\"button\""
								],
							]
						];
					}
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
		@php
			$subtotal	=	isset($request->groups->first()->subtotales) ? "$ ".number_format($request->groups->first()->subtotales,2,".",",") : "";
			$totalIVA	=	isset($request->groups->first()->tax) ? "$ ".number_format($request->groups->first()->tax,2,".",",") : "";
			$total		=	isset($request->groups->first()->amount) ? "$ ".number_format($request->groups->first()->amount,2,".",",") : "";
			if (isset($request) && isset($request->groups->first()->detailGroups))
			{
				foreach ($request->groups->first()->detailGroups as $detail)
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
			}
			$modelTable	=
			[
				[
					"label"	=>	"Subtotal:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"	=>	"subtotalLabel py-2", "label"	=>	$subtotal],
						["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"subtotal\"	value=\"".$subtotal."\""]
					]
				],
				[
					"label"	=>	"Impuesto Adicional:",	"inputsEx" =>
					[
						["kind"	=>	"components.labels.label",		"classEx"	=>	"amountAALabel py-2", "label"	=>	"$ ".number_format($taxes,2)],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountAA\"	value=\"$ ".number_format($taxes,2)."\""]
					]
				],
				[
					"label"	=>	"Retenciones:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"	=>	"amountRLabel py-2", "label"	=>	"$ ".number_format($retentions,2)],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"amountR\"	value=\"$ ".number_format($retentions,2)."\""]
					]
				],
				[
					"label"	=>	"IVA:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"	=>	"totalivaLabel py-2", "label"	=>	$totalIVA],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"totaliva\"	value=\"".$totalIVA."\""]
					]
				],
				[
					"label"	=>	"TOTAL:",	"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",		"classEx"	=>	"totalLabel py-2", "label"			=>	$total],
						["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"placeholder=\"$0.00\" readonly type=\"hidden\" name=\"total\"	value=\"".$total."\" id=\"input-extrasmall\""]
					]
				],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
			@slot('attributeExComment')
				@if($request->status !=2) disabled @endif
			@endslot
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DEL MOVIMIENTO <span class="help-btn" id="help-btn-condition-pay">
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Importe Total: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="amountTotal" placeholder="Ingrese el importe total" data-validation="required" @if (isset($request->groups->first()->amountMovement)) value="{{ $request->groups->first()->amountMovement }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Comisión: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="commission" placeholder="Ingrese la comisión" data-validation="required" @if (isset($request->groups->first()->commission)) value="{{ $request->groups->first()->commission }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe a retomar: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="amountRetake" placeholder="Ingrese el importe a retomar" readonly="readonly" data-validation="required" @if(isset($request->groups->first()->amountRetake)) value="{{ $request->groups->first()->amountRetake }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Referencia/Número de factura: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="referencePurchase" placeholder="Ingrese una referencia" @if(isset($request) && isset($request->groups->first()->reference)) value="{{ $request->groups->first()->reference }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de moneda: @endcomponent
				@php
					$options	=	collect();
					$values		=	["MXN","USD","EUR","Otro"];
					foreach ($values as $value)
					{
						if ($request->groups()->exists() && $request->groups->first()->typeCurrency == $value)
						{
							$options	=	$options->concat([["value"	=>	$value, "description"	=>	$value, "selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$value, "description"	=>	$value]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						name="type_currency" multiple="multiple" data-validation="required" @if($request->status!=2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de Pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="date" step="1" placeholder="Ingrese la fecha" readonly="readonly" id="datepicker" data-validation="required" @if(isset($request)) value="{{ $request->PaymentDate != null ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y') : null }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Forma de pago: @endcomponent
				@php
					$options	=	collect();
					foreach (App\PaymentMethod::orderName()->get() as $method)
					{
						if (isset($request->groups->first()->idpaymentMethodest) && $request->groups->first()->idpaymentMethod == $method->idpaymentMethod)
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
						@if($request->status!=2) disabled="disabled" @endif multiple="multiple" name="pay_mode" data-validation="required"
					@endslot
					@slot('classEx')
						js-form-pay removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Estado  de factura: @endcomponent
				@if (isset($request))
				@endif
				@php
					$options	=	collect();
					$values		=	["No Aplica","Pendiente","Entregado"];
					$selected	=	"No Aplica";
					$status		=	isset($request->groups->first()->statusBill) ? $request->groups->first()->statusBill : "";
					if (isset($request))
					{
						if ($status == "") {
							$selected	=	"Pendiente";
						}
						if ($status && ($status != "Pendiente" && $status != "Entregado" && $status !="No Aplica"))
						{
							$selected	=	$status;
							$options	=	$options->concat([["value"	=>	$status, "description"	=>	$status, "selected"	=>	"selected"]]);
						}
						if($status == "Pendiente" && $status == "Entregado" && $status =="No Aplica")
						{
							$selected	=	$status;
						}
					}
					foreach ($values as $value)
					{
						if ($value == $selected)
						{
							$options	=	$options->concat([["value"	=>	$value, "description"	=>	$value, "selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$value, "description"	=>	$value]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						multiple="multiple" name="status_bill" data-validation="required" @if($request->status !=2) disabled @endif
					@endslot
					@slot('classEx')
						js-ef removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe a pagar: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						@if($request->status!=2) disabled="disabled" @endif type="text" name="amount_total" readonly placeholder="Ingrese el importe" data-validation="required" @if(isset($request->groups->first()->amount)) value="{{ $request->groups->first()->amount }}" @endif
					@endslot
					@slot('classEx')
						amount_total remove
					@endslot
				@endcomponent
			</div>
		@endcomponent
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
			if (isset($request->groups->first()->documentsGroups) && count($request->groups->first()->documentsGroups)>0)
			{
				foreach($request->groups->first()->documentsGroups as $doc)
				{
					$modelHead	=	["Documento", "Fecha"];
					$body	=
					[
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"secondary",
									"buttonElement"	=>	"a",
									"attributeEx"	=>	"type=\"button\"target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
									"label"			=>	"ARCHIVO"
								]
							],
						],
						[
							"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
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
		@component('components.containers.container-form')
			<div id="documents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				<div class="md:block grid">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx')
							type="button" name="addDoc" id="addDoc" @if($request->status == 1) disabled @endif
						@endslot
						@slot('label')
							<span class="icon-plus"></span>
							<span>Agregar documento</span>
						@endslot
					@endcomponent
					@if ($request->status != 2)
						@component('components.buttons.button', ["variant" => "success"])
							@slot('attributeEx')
								type="submit" id="upload_docs" name="send" formaction="{{ route('movements-accounts.update.documents', $request->folio) }}" @if($request->status == 1) disabled @endif
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
				$requestAccount	=	App\Account::find($request->groups->first()->idAccAccOriginR);
				$requestAccount	=	App\Account::find($request->groups->first()->idAccAccDestinyR);
				$modelTable	=
				[
					"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
					"Nombre de la Empresa de Origen"		=>	$request->groups->first()->idEnterpriseOriginR != "" ? App\Enterprise::find($request->groups->first()->idEnterpriseOriginR)->name : "",
					"Nombre de la Dirección de Origen"		=>	$request->groups->first()->idEnterpriseOriginR != "" ? App\Area::find($request->groups->first()->idAreaOriginR)->name : "",
					"Nombre del Departamento de Origen"		=>	$request->groups->first()->idEnterpriseOriginR != "" ? App\Department::find($request->groups->first()->idDepartamentOriginR)->name : "",
					"Clasificación del Gasto de Origen"		=>	$request->groups->first()->idEnterpriseOriginR != "" ? $requestAccount->account." - ".$requestAccount->description : "",
					"Nombre del Proyecto de Origen"			=>	$request->groups->first()->idEnterpriseOriginR != "" ? App\Project::find($request->groups->first()->idProjectOriginR)->proyectName : "",
					"Nombre de la Empresa de Destino"		=>	$request->groups->first()->idEnterpriseOriginR != "" ? App\Enterprise::find($request->groups->first()->idEnterpriseDestinyR)->name : "",
					"Clasificación del Gasto de Destino"	=>	$request->groups->first()->idEnterpriseOriginR != "" ? $requestAccount->account." - ".$requestAccount->description : "",
					"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : $request->checkComment == "",
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				ETIQUETAS ASIGNADAS
			@endcomponent
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
				foreach($request->groups->first()->detailGroups as $detail)
				{
					$labelValue	=	[];
					foreach ($detail->labels as $label)
					{
						$labelValue[]	=	$label->label->description;
					}
					$body	=
					[
						[
							"content"	=>	["label"	=>	$detail->quantity." ".$detail->unit],
						],
						[
							"content"	=>	["label"	=>	$detail->description],
						],
						[
							"content"	=>	["label"	=>	$labelValue],
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeExBody" => "id=\"tbody-conceptsNew\"", "classExBody" => "request-validate"])@endcomponent
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
					"Autorizó:"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
					"Comentarios:"	=>	$request->authorizeComment == "" ? "Sin comentarios" : $request->authorizeComment,
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
		@endif
		@if($request->status == 13)
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE PAGOS
			@endcomponent
			@php
				$modelTable	=
				[
					"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : $request->paymentComment,
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
		@endif
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@if ($request->status == "2")
				@component('components.buttons.button', ["variant" => "primary", "label" => "ENVIAR SOLICITUD"])
					@slot('attributeEx')
						type="submit" name="enviar" value="ENVIAR SOLICITUD"
					@endslot
				@endcomponent
				@component('components.buttons.button', ["variant" => "secondary", "label" => "GUARDAR SIN ENVIAR"])
					@slot('attributeEx')
						type="submit" id="save" name="save" value="GUARDAR SIN ENVIAR" formaction="{{ route('movements-accounts.groups.follow.unsent', $request->folio) }}"
					@endslot
					@slot('classEx')
						save
					@endslot
				@endcomponent
			@endif
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"])
				@slot('attributeEx')
					type="button" @if(isset($option_id)) href="{{ url(App\Module::find($option_id)->url) }}" @else href="{{ url(App\Module::find($child_id)->url) }}" @endif 
				@endslot
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
				cant	= $('input[name="quantity"]').removeClass('error').val();
				unit	= $('[name="unit"] option:selected').removeClass('error').val();
				descr	= $('input[name="description"]').removeClass('error').val();
				precio	= $('input[name="price"]').removeClass('error').val();
				subtotal	= 0;
				iva			= 0;
				descuento	= Number($('input[name="descuento"]').val());
				$(".tr_body").each(function(i, v)
				{
					tempQ		= $(this).find('.tquanty').val();
					tempP		= $(this).find('.tprice').val();
					subtotal	+= Number(tempQ)*Number(tempP);
					iva			+= Number($(this).find('.tiva').val());
				});
				total = (subtotal+iva)-descuento;
				if(total<0)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
					swal('', 'El importe total no puede ser negativo', 'error');
					return false;
				}	
				if($('.request-validate').length>0)
				{
					prov		= $('#form-prov').is(':visible');
					conceptos	= $('.tr_body').length;
					path		= $('.path').length;
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
					else if(prov && conceptos>0)
					{
						if($('#banks-body tr').length>0)
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
						swal('', 'Debe ingresar al menos un concepto de pedido y todos los datos del proveedor', 'error');
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
		if ($('input[name="fiscal"]:checked').val() == "0") {
			$('.ivaKindContainer').addClass('hidden');
		}
		validate();
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional','function'=>'total_cal'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention','name' => 'retention','function'=>'total_cal'])  @endcomponent
		$('[name="price"],[name="additionalAmount"],[name="retentionAmount"],[name="amountTotal"],[name="commission"],[name="amountRetake"]').on("contextmenu",function(e)
		{
			return false;
		});
		count			= 0;
		countB			= {{ $taxesCount }};
		countBilling	= {{ $taxesCountBilling }};
		$('.phone,.clabe,.account').numeric(false);
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.additionalAmount,.amountAdditional_billing,.retentionAmount,.retentionAmount_billing',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false  });
		$('input[name="amountTotal"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
		$('input[name="commission"]',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
		$(function() 
		{
			$("#datepicker, .datepicker2").datepicker({  dateFormat: "dd-mm-yy" });
		});
		generalSelect({'selector': '.cp', 'model': 2});
		generalSelect({'selector': '.js-users', 'model': 13});
		generalSelect({'selector': '.js-projects-origin', 'model': 21});
		generalSelect({'selector': '.js-bank', 'model': 27});
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 12});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 6});

		@php
			$selects = collect([
				[
					"identificator"				=> ".js-ef",
					"placeholder"				=> "Seleccione el estado de la factura",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
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
					"identificator"				=> ".js-type",
					"placeholder"				=> "Seleccione el tipo de operación",
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
					"identificator"				=> ".js-state",
					"placeholder"				=> "Seleccione un estado",
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
				],
				[
					"identificator"				=> ".currency",
					"placeholder"				=> "Seleccione el tipo de moneda",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		
		$(document).on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
		.on('change','input[name="prov"]',function()
		{
			if ($('input[name="prov"]:checked').val() == "nuevo") 
			{
				$("#form-prov").fadeIn("slow");
				$('input[name="idProvider"]').val('');
				$('input[name="reason"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
				$('input[name="address"]').val('').prop('disabled',false);
				$('input[name="number"]').val('').prop('disabled',false);
				$('input[name="colony"]').val('').prop('disabled',false);
				$('#cp').val(0).prop('disabled',false);
				$('input[name="city"]').val('').prop('disabled',false);
				$('.js-state').val(0).prop('disabled',false);
				$('input[name="rfc"]').val('').prop('disabled',false).removeAttr('data-validation-req-params').removeAttr('data-validation-error-msg');
				$('input[name="phone"]').val('').prop('disabled',false);
				$('input[name="contact"]').val('').prop('disabled',false);
				$('input[name="beneficiary"]').val('').prop('disabled',false);
				$('input[name="other"]').val('').prop('disabled',false);
				$("#buscar").fadeOut('fast');
				$(".checks").addClass('hidden');
				$('#banks-body').html('');
				$('#banks').removeClass('hidden');
				$('#banks-body .delete-item').show();
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
			}
			else if ($('input[name="prov"]:checked').val() == "buscar") 
			{
				$("#buscar").fadeIn("slow");
				$("#form-prov").fadeOut('fast');
				$(".input-bank").css({display:'block'});
				$(".select-bank").css({display:'none'});
			}
		})
		.on('click','.help-btn',function()
		{
			swal('Ayuda','Al habilitar la edición, usted podrá modificar los campos del proveedor; si la edición permanece deshabilitada no se guardará ningún cambio en el mismo.','info');
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
		.on('click','#upload_docs',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
		})
		.on('change','input[name="act_gas"]',function()
		{
			$("#condition").fadeIn("slow").css({display:'flex'});
		})
		
		.on('change','input[name="fiscal"]',function()
		{
			if ($('input[name="fiscal"]:checked').val() == "1")
			{
				$('[name="iva_kind"]').removeAttr('disabled');
				$('#iva_no').attr('checked',true);
				$('.ivaKindContainer').removeClass('hidden');
			}
			else if ($('input[name="fiscal"]:checked').val() == "0")
			{
				$('[name="iva_kind"]').attr('disabled',true);
				$('#iva_no').attr('checked',true);
				$('.ivaKindContainer').addClass('hidden');
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
					$('#body').html('');
					$('#form-prov').addClass('hidden');
					$('#banks').hide();
					$('#buscar').hide();
					$('#not-found').stop().hide();
					$('#table-provider').stop().hide();
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
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
			}if(taxes=="")
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
				$('.retentionAmount').val('');
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
			totalImporte    = ((cant * precio)+ivaCalc)+taxes-retentions;
			$('input[name="amount"]').val(totalImporte.toFixed(2));
		})
		.on('change','input[name="edit"]',function()
		{
			if($(this).is(':checked'))
			{
				swal({
					title		: "Habilitar edición de proveedor",
					text		: "¿Desea habilitar la edición del proveedor?",
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
						$('input[name="other"]').prop('disabled',false);
						$('#banks').show();
						$('#banks-body .delete-item').show();
					}
					else
					{
						$(this).prop('checked',false);
					}
				});
			}
			else
			{
				swal({
					title		: "Deshabilitar edición de proveedor",
					text		: "Si deshabilita la edición las modificaciones realizadas al proveedor no serán guardadas",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((disabled) =>
				{
					if(disabled)
					{
						$('input[name="reason"]').attr('disabled',true);
						$('input[name="address"]').prop('disabled',true);
						$('input[name="number"]').attr('disabled',true);
						$('input[name="colony"]').attr('disabled',true);
						$('#cp').attr('disabled',true);
						$('input[name="city"]').attr('disabled',true);
						$('.js-state').attr('disabled',true);
						$('input[name="rfc"]').attr('disabled',true);
						$('input[name="phone"]').attr('disabled',true);
						$('input[name="contact"]').attr('disabled',true);
						$('input[name="beneficiary"]').attr('disabled',true);
						$('input[name="other"]').attr('disabled',true);
						$('#banks').hide();
						$('#banks-body .delete-item').hide();
					}
					else
					{
						$(this).prop('checked',true);
					}
				});
			}
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
			else if($('[name="amount"]').val() == "NaN")
			{
				$('[name="amount"]').addClass('error');
				swal('','Por favor verifique los montos ingresados.','error');
			}
			else if( cant == 0)
			{
				swal('','La cantidad no puede ser 0.','error');
			}
			else if( precio == 0)
			{
				swal('','El precio unitario no puede ser 0.','error');
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
				total = (((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
				
				if(Number(total) <= 0)
				{
					swal('','El total no puede ser menor o igual a cero','error');
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
							["value"	=>	"#",			"show"	=>	"true"],
							["value"	=>	"Cantidad",		"show"	=>	"true"],
							["value"	=>	"Unidad",		"show"	=>	"true"],
							["value"	=>	"Descripción",	"show"	=>	"true"],
							["value"	=>	"Precio Unitario"],
							["value"	=>	"IVA"],
							["value"	=>	"Impuesto adicional"],
							["value"	=>	"Retenciones"],
							["value"	=>	"Importe"],
							["value"	=>	"Acciones"],
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
								"show"		=>	"true",
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
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"red",
										"label"			=>	"<span class=\"icon-x delete-span\"></span>",
										"classEx"		=>	"delete-item",
										"attributeEx"	=>	"type=\"button\""
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
					$('input[name="additional_exist"]').prop('checked',false);
					$('input[name="retention_new"]').prop('checked',false);
					$('#iva_no').prop('checked',true);
					$('input[name="amount"]').val("");
					$('input[name="unit"]').val(null).trigger('change');
					$('#newsImpuestos').empty();
					$('#newsRetention').empty();
					$('.nameAmount').val('');
					$('.additionalAmount').val('');
					$('.retentionName').val('');
					$('.retentionAmount').val('');
					$('#taxes_exist').stop(true,true).fadeOut().hide();
					$('#retention_new').stop(true,true).fadeOut().hide();
					additionalCleanComponent();
					retentionCleanComponent();
					total_cal();
					amountRetake();
					countB++;
				}
			}
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('tr').remove();
			total_cal();
			amountRetake();
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
			cant				= $('input[name="quantity"]').removeClass('error').val();
			unit				= $('input[name="unit"]').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			if (cant == "" || descr == "" || precio == "" || unit == "") 
			{
				tquanty		= $(this).parents('.tr_body').find('.tquanty').val();
				tunit		= $(this).parents('.tr_body').find('.tunit').val();
				tdescr		= $(this).parents('.tr_body').find('.tdescr').val();
				tivakind	= $(this).parents('.tr_body').find('.tivakind').val();
				tprice		= $(this).parents('.tr_body').find('.tprice').val();
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
						$('.unit').val(tunit).trigger('change');
						$('input[name="description"]').val(tdescr);
						$('input[name="price"]').val(tprice);
						$(this).parents('.tr_body').remove();
						total_cal();
						amountRetake();
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
		.on('click','#add2',function()
		{
			bank			= $(this).parents('#banks').find('.js-bank option:selected').val();
			bankName		= $(this).parents('#banks').find('.js-bank option:selected').text();
			account			= $(this).parents('#banks').find('.account').val();
			branch_office	= $(this).parents('#banks').find('.branch_office').val();
			reference		= $(this).parents('#banks').find('.reference').val();
			clabe			= $(this).parents('#banks').find('.clabe').val();
			currency		= $(this).parents('#banks').find('.currency').val();
			agreement		= $(this).parents('#banks').find('.agreement').val();
			iban			= $(this).parents('#banks').find('.iban').val();
			bic_swift		= $(this).parents('#banks').find('.bic_swift').val();
			alias			= $(this).parents('#banks').find('.alias').val();

			if(bank != undefined)
			{
				$('.account,.reference,.clabe,.currency').removeClass('error');
				if (account == "" && reference=="" && clabe == "" || currency == "")
				{
					if(account == "" && reference=="" && clabe == "")
					{
						if(account == "")
						{
							$('.account').addClass('error');
						}
						if(reference=="")
						{
							$('.reference').addClass('error');
						}
						if(clabe == "")
						{
							$('.clabe').addClass('error');
						}
					}
					if(currency == "")
					{
						$('.currency').addClass('error');
					}
					swal('', 'Debe ingresar todos los campos requeridos', 'error');
				}
				else if($(this).parents('tr').find('.clabe').hasClass('error') || $(this).parents('tr').find('.account').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					@php
						$modelHead	=	[];
						$body		=	[];
						$modelBody	=	[];
						$modelHead	=
						[
							["value"	=>	"Seleccionar",	"show"	=>	"true"],
							["value"	=>	"Banco",		"show"	=>	"true"],
							["value"	=>	"Alias",		"show"	=>	"true"],
							["value"	=>	"Cuenta",		"show"	=>	"true"],
							["value"	=>	"Sucursal"],
							["value"	=>	"Referencia"],
							["value"	=>	"CLABE"],
							["value"	=>	"Moneda"],
							["value"	=>	"IBAN"],
							["value"	=>	"BIC/SWIFT"],
							["value"	=>	"Convenio"],
						];
						$body			=
						[
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"				=>	"components.inputs.checkbox",
										"radio"				=>	"true",
										"attributeEx" 		=>	"name=\"provider_has_banks_id\"",
										"classExLabel"		=>	"check-small request-validate",
										"classExContainer"	=>	"inline-flex",
										"classEx"			=>	"checkbox",
										"label"				=>	"<span class=icon-check></span>",
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"checked[]\"",
										"classEx"		=>	"idchecked"
									],
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"red",
										"classEx"		=>	"hidden delete-item",
										"attributeEx"	=>	"type=\"button\"",
										"label"			=>	"<span class=\"icon-x delete-span\"></span>"
									],
								]
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"providerBankTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"providerBank[]\"",
										"classEx"		=>	"providerBank"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"bank[]\""
									],
								]
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"aliaskTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"alias[]\""
									]
								]
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"accountkTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"account[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"branchkTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"branch_office[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"referenceTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"reference[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"clabeTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"clabe[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"currencyTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"currency[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"ibankTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"iban[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"bicSwiftTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"bic_swift[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.labels.label",
										"classEx"		=>	"agreementTxt"
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" name=\"agreement[]\""
									]
								]
							],
						];
						$modelBody[]	=	$body;
						$table = view('components.tables.table',["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"])->render();
					@endphp
					table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row		=	$(table);
					row.find('.providerBankTxt').text(bankName !=null ? bankName : "---");
					row.find('.providerBank').val('x');
					row.find('[name="bank[]"]').val(bank);
					row.find('.aliaskTxt').text(alias !=null ? alias : "---");
					row.find('[name="alias[]"]').val(alias);
					row.find('.accountkTxt').text(account !=null ? account : "---");
					row.find('[name="account[]"]').val(account);
					row.find('.branchkTxt').text(branch_office !=null ? branch_office : "---");
					row.find('[name="branch_office[]"]').val(branch_office);
					row.find('.referenceTxt').text(reference !=null ? reference : "---");
					row.find('[name="reference[]"]').val(reference);
					row.find('.clabeTxt').text(clabe !=null ? clabe : "---");
					row.find('[name="clabe[]"]').val(clabe);
					row.find('.currencyTxt').text(currency !=null ? currency : "---");
					row.find('[name="currency[]"]').val(currency);
					row.find('.ibankTxt').text(iban =='' ? '---' :iban);
					row.find('[name="iban[]"]').val(iban);
					row.find('.bicSwiftTxt').text(bic_swift =='' ? '---' :bic_swift);
					row.find('[name="bic_swift[]"]').val(bic_swift);
					row.find('.agreementTxt').text(agreement =='' ? '---' :agreement);
					row.find('[name="agreement[]"]').val(agreement);
					row.find('[name="checked[]"]').val('0');
					row.find('.checkbox').attr('id','idNew'+count+'').val(count);
					row.find('.check-small').attr('for','idNew'+count+'');
					$('#banks-body').append(row);
					$('.clabe, .account').removeClass('valid').val('');
					$('.branch_office,.reference,.currency,.agreement,.alias,.iban,.bic_swift').val('');
					$(this).parents('tbody').find('.error').removeClass('error');
					$('.js-bank').val(0).trigger("change");
					count++;
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.js-bank').addClass('error');
			}
		})
		.on('click','.button-search', function()
		{
			provider_search();
		})
		.on('click','.pagination a', function(e)
		{
			e.preventDefault();
			href	=	$(this).attr('href');
			url		=	new URL(href);
			params	=	new URLSearchParams(url.search);
			page	=	params.get('page');
			provider_search(page);
		})
		.on('click', '.edit', function()
		{
			$('#cp').val(null).trigger('change');
			encodedString	= $('#provider_'+$(this).val()).val();
			decodedString	= Base64.decode(encodedString);
			json			= JSON.parse(decodedString);
			reasonTemp		= {'oldReason':json.provider.businessName};
			rfcTemp			= {'oldRfc':json.provider.rfc};
			$('input[name="idProvider"]').val(json.provider.idProvider);
			$('input[name="reason"]').val(json.provider.businessName).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(reasonTemp));
			$('input[name="address"]').val(json.provider.address).prop('disabled',true);
			$('input[name="number"]').val(json.provider.number).prop('disabled',true);
			$('input[name="colony"]').val(json.provider.colony).prop('disabled',true);
			$('#cp').append(new Option(json.provider.postalCode, json.provider.postalCode, true, true)).trigger('change').prop('disabled',true);
			$('input[name="city"]').val(json.provider.city).prop('disabled',true);
			$('.js-state').val(json.provider.state_idstate).prop('disabled',true);
			$('input[name="rfc"]').val(json.provider.rfc).prop('disabled',true).attr('data-validation-req-params',JSON.stringify(rfcTemp));
			$('input[name="phone"]').val(json.provider.phone).prop('disabled',true);
			$('input[name="contact"]').val(json.provider.contact).prop('disabled',true);
			$('input[name="beneficiary"]').val(json.provider.beneficiary).prop('disabled',true);
			$('input[name="other"]').val(json.provider.commentaries).prop('disabled',true);
			$('#banks-body').html('');
			$.each(json.banks,function(i,v)
			{
				bankName = $('.js-bank option[value='+v.banks_idBanks+']').text();
				@php
					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=
					[
						["value"	=>	"Seleccionar",	"show"	=>	"true"],
						["value"	=>	"Banco",		"show"	=>	"true"],
						["value"	=>	"Alias",		"show"	=>	"true"],
						["value"	=>	"Cuenta",		"show"	=>	"true"],
						["value"	=>	"Sucursal"],
						["value"	=>	"Referencia"],
						["value"	=>	"CLABE"],
						["value"	=>	"Moneda"],
						["value"	=>	"IBAN"],
						["value"	=>	"BIC/SWIFT"],
						["value"	=>	"Convenio"],
					];
					$body			=
					[
						[
							"show"		=>	"true",
							"content"	=>
							[
								[
									"kind"				=>	"components.inputs.checkbox",
									"radio"				=>	"true",
									"attributeEx" 		=>	"name=\"provider_has_banks_id\"",
									"classExLabel"		=>	"check-small request-validate",
									"classExContainer"	=>	"inline-flex",
									"classEx"			=>	"checkbox",
									"label"				=>	"<span class=icon-check></span>",
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"checked[]\"",
									"classEx"		=>	"idchecked"
								],
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"classEx"		=>	"hidden delete-item",
									"attributeEx"	=>	"type=\"button\"",
									"label"			=>	"<span class=\"icon-x delete-span\"></span>"
								],
							]
						],
						[
							"show"		=>	"true",
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"providerBankTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"providerBank[]\"",
									"classEx"		=>	"providerBank"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"bank[]\""
								],
							]
						],
						[
							"show"		=>	"true",
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"aliaskTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"alias[]\""
								]
							]
						],
						[
							"show"		=>	"true",
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"accountkTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"account[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"branchkTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"branch_office[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"referenceTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"reference[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"clabeTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"clabe[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"currencyTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"currency[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"ibankTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"iban[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"bicSwiftTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"bic_swift[]\""
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.labels.label",
									"classEx"		=>	"agreementTxt"
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"agreement[]\""
								]
							]
						],
					];
					$modelBody[]	=	$body;
					$table = view('components.tables.table',["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"])->render();
				@endphp
				table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row		=	$(table);
				row.find('.providerBankTxt').text(bankName!=null ? bankName : "---");
				row.find('.providerBank').val('x');
				row.find('[name="bank[]"]').val(v.banks_idBanks);
				row.find('.aliaskTxt').text(v.alias!=null ? v.alias : "---");
				row.find('[name="alias[]"]').val(v.alias);
				row.find('.accountkTxt').text(v.account!=null ? v.account : "---");
				row.find('[name="account[]"]').val(v.account);
				row.find('.branchkTxt').text(v.branch!=null ? v.branch : "---");
				row.find('[name="branch_office[]"]').val(v.branch);
				row.find('.referenceTxt').text(v.reference!=null ? v.reference : "---");
				row.find('[name="reference[]"]').val(v.reference);
				row.find('.clabeTxt').text(v.clabe!=null ? v.clabe : "---");
				row.find('[name="clabe[]"]').val(v.clabe);
				row.find('.currencyTxt').text(v.currency!=null ? v.currency : "---");
				row.find('[name="currency[]"]').val(v.currency);
				row.find('.ibankTxt').text(v.iban ==null ? '---' :v.iban);
				row.find('[name="iban[]"]').val(v.iban ==null ? '' : v.iban);
				row.find('.bicSwiftTxt').text(v.bic_swift ==null ? '---' :v.bic_swift);
				row.find('[name="bic_swift[]"]').val(v.bic_swift ==null ? '' : v.bic_swift);
				row.find('.agreementTxt').text(v.agreement ==null ? '---' :v.agreement);
				row.find('[name="agreement[]"]').val(v.agreement ==null ? '' : v.agreement);
				row.find('[name="checked[]"]').val('0');
				row.find('.check-small').attr('for','id'+v.id).val(v.id);
				row.find('.checkbox').attr('id','id'+v.id).val(v.id);
				$('#banks-body').append(row);
			});
			$('input[name="edit"]').prop('checked',false);
			$('#form-prov').fadeIn();
			$(".checks").show();
			$('#banks').hide();
			$('.provider').hide();
			$('#banks-body .delete-item').hide();
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
		.on('click','.checkbox',function()
		{
			$('.idchecked').val('0');
			$('.marktr').removeClass('marktr');
			$(this).parents('tr').addClass('marktr');
			$(this).parents('tr').find('.idchecked').val('1');
		})
		.on('click','input[name="retention_new"]',function()
		{
			if($(this).val() == 'si')
			{
				$('#retention_new').stop(true,true).fadeIn().show();
			}
			else
			{
				$('#retention_new').stop(true,true).fadeOut().hide();
			}
		})
		.on('click','.newRetention',function()
		{
			newI = $('<span class="span-taxes"><div class="left"><label class="label-form">Nombre de la Retención</label></div><div class="right"><input type="text" name="retentionName" class="retentionName" placeholder="Ingrese un nombre"></div><br><div class="left"><label class="label-form">Importe de Retención</label></div><div class="right"><input type="text" name="retentionAmount" class="retentionAmount" placeholder="Ingrese un importe"><button class="span-delete btn btn-red" type="button">Quitar</button></div><br></span>');
			$('#newsRetention').append(newI);
			$('.retentionAmount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false  });
			$('[name="retentionAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
		})
		.on('click','input[name="additional_exist"]',function()
		{
			if($(this).val() == 'si')
			{
				$('#taxes_exist').stop(true,true).fadeIn().show();
			}
			else
			{
				$('#taxes_exist').stop(true,true).fadeOut().hide();
			}
		})
		.on('click','.newadditional',function()
		{
			newI = $('<span class="span-taxes"><div class="left"><label class="label-form">Nombre del Impuesto Adicional</label></div><div class="right"><input type="text" name="additionalAmount" class="additionalAmount" placeholder="Ingrese un nombre"></div><br><div class="left"><label class="label-form">Impuesto Adicional</label></div><div class="right"><input type="text" name="amountAdditional" class="amountAdditional" placeholder="Ingrese el impuesto"><button class="span-delete btn btn-red" type="button">Quitar</button></div><br></span>');
			$('#newsImpuestos').append(newI);
			$('.additionalAmount',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false  });
			$('[name="additionalAmount"]').on("contextmenu",function(e)
			{
				return false;
			});
		})
		.on('click','#addDoc',function()
		{
			@php
				$newDoc = view('components.documents.upload-files',[
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExInput"			=>	"inputDoc pathActioner",
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
		.on('click','#help-btn-select-provider',function()
		{
			swal('Ayuda','En este apartado debe seleccionar un proveedor. De clic en "Buscar" si va a tomar un proveedor que ya existe. Dé click en "Nuevo" si desea agregar un proveedor en caso de no encontrarlo en el buscador.','info');
		})
		.on('click','#help-btn-account-bank',function()
		{
			swal('Ayuda','En este apartado debe seleccionar una cuenta bancaria del proveedor. Dé click en el icono que se encuentra al final de cada cuenta para seleccionarla.','info');
		})
		.on('click','#help-btn-dates',function()
		{
			swal('Ayuda','En este apartado debe agregar cada uno de los conceptos pertenecientes al pedido.','info');
		})
		.on('click','#help-btn-condition-pay',function()
		{
			swal('Ayuda','En este apartado debe agregar las condiciones de pago. Le recordamos que puede enviar su orden de compra sin factura en caso de no contar con ella y posteriormente cargarla.','info');
		})
		.on('change','.inputDoc.pathActioner',function(e)
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
		.on('change','input[name="amountTotal"], input[name="commission"]',function()
		{
			amountTotal		= 0;
			commission		= 0;
			amountTotal		= $('input[name="amountTotal"]').val();
			commission		= $('input[name="commission"]').val();
			amountRetake	= amountTotal-commission;
			if (amountRetake < 0) 
			{
				swal('','El importe a retomar no puede ser negativo','error');
				$('input[name="commission"]').val('');
				$('input[name="amountRetake"]').val(amountTotal);
			}
			else
			{
				$('input[name="amountRetake"]').val(amountRetake);
			}
		})
		.on('click','#closeFormProv',function()
		{
			$('#form-prov').stop().fadeOut();
			$('.provider').stop().fadeIn();
			$('#not-found').stop().hide();
		})
	});
	function amountRetake() 
	{
		amountTotal		= 0;
		commission		= 0;
		amountTotal		= $('input[name="amountTotal"]').val();
		commission		= $('input[name="commission"]').val();
		amountRetake	= amountTotal-commission;
		$('input[name="amountRetake"]').val(amountRetake);
	}
	function provider_search(page)
	{
		
		$('#not-found').html("");
		text = $("#input-search").val().trim();
		if (text == "")
		{
			@php
				$notfound	=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found", ["text" => "No se encontraron proveedores registrados"])));
			@endphp
			notfound	=	'{!!preg_replace("/(\r)*(\n)*/", "", $notfound)!!}';
			$('#not-found').append(notfound);
			$('#not-found').stop().show();
			$('.provider').stop().hide();
		}
		else
		{
			$('#not-found').stop().hide();
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("movements-accounts.create.provider") }}',
				data	: {'search':text, "page":page},
				success	: function(data)
				{
					$('.provider').html(data).slideDown('slow');
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.provider').html('').hide();
				}
			}); 
		}
	}
	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountAA 	= 0;
		amountR 	= 0;
		$(".tr_body").each(function(i, v)
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
			subtotal	+= (Number(tempQ)*Number(tempP));
			iva			+= Number($(this).find('.tiva').val());
			amountAA 	= Number(tempAA);
			amountR 	= Number(tempR);
		});
		total = (subtotal+iva + amountAA)-amountR;
		$('input[name="subtotal"]').val('$ '+Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val('$ '+Number(iva).toFixed(2));
		$('input[name="total"]').val('$ '+Number(total).toFixed(2));
		$(".amount_total").val('$ '+Number(total).toFixed(2));
		$('input[name="amountTotal"]').val(Number(total).toFixed(2));
		$('input[name="amountAA"]').val('$ '+Number(amountAA).toFixed(2));
		$('input[name="amountR"]').val('$ '+Number(amountR).toFixed(2));
		$('.subtotalLabel').text('$ '+Number(subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalivaLabel').text('$ '+Number(iva).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.totalLabel').text('$ '+Number(total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.amountAALabel').text('$ '+Number(amountAA).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
		$('.amountRLabel').text('$ '+Number(amountR).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
	}
</script>
@endsection
