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
		$taxesCount = 0;
		$taxes 		= 0;
		$retentions = 0;
	@endphp
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('purchase.follow.update', $request->folio)."\"",
		"files"		  => "true",
		"methodEx"	  => "PUT",
		"token"       => "true"
	])
		@if($request->purchases->first()->idRequisition != "")
			@component("components.labels.not-found", ["variant" => "note"])
				@slot("slot")
					Esta solicitud viene de la requisición #{{ $request->purchases->first()->idRequisition }}. <br>
					Si hay algún dato incorrecto por favor modifíquelo.
				@endslot
			@endcomponent
			@component("components.labels.not-found", ["variant" => "note"])
				@slot("slot")
					<div class="flex flex-row">
						@component('components.labels.label') 
							@slot('classEx')
								font-bold
							@endslot
							FOLIO: 
						@endcomponent @component('components.labels.label')
							@slot('classEx')
								px-2
							@endslot 
							{{ $request->new_folio }} 
						@endcomponent
					</div>
					@if($request->purchases->first()->requisitionRequest->requisition->wbs()->exists())
						<div class="flex flex-row">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
								@endslot
								CÓDIGO WBS:
							@endcomponent 
							@component('components.labels.label')
								@slot('classEx')
									px-2
								@endslot 
								{{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}.
							@endcomponent
						</div>
					@endif
					@if($request->purchases->first()->requisitionRequest->requisition->edt()->exists())
						<div class="flex flex-row">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
								@endslot
								CÓDIGO EDT:
							@endcomponent 
							@component('components.labels.label')
								@slot('classEx')
									px-2
								@endslot 
								{{ $request->purchases->first()->requisitionRequest->requisition->edt()->exists() ? $request->purchases->first()->requisitionRequest->requisition->edt->fullName() : '' }}.
							@endcomponent
						</div>
					@endif
				@endslot
			@endcomponent
		@endif
		@if($request->idNomina != "")
			@component("components.labels.not-found", ["variant" => "note"])
				@slot("slot")
					Esta solicitud viene de la Solicitud de Nómina #{{ $request->idNomina }} "{{ $request->nominaData->title }}"
					Si hay algún dato incorrecto por favor modifíquelo.
				@endslot
			@endcomponent
		@endif
		<input type="hidden" class="main_folio" name="main_folio" value="{{$request->folio}}"/>
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			Folio: {{ $request->folio }}
		@endcomponent
		
		<input type="hidden" name="status" value="{{ $request->status }}">
		@php
			$elaborate = App\User::find($request->idElaborate);
		@endphp
		@component('components.labels.subtitle')
			Elaborado por: {{ $elaborate->name }} {{ $elaborate->last_name }} {{ $elaborate->scnd_last_name }}
		@endcomponent
		@component("components.containers.container-form")	
			<div class="col-span-2">
				@component('components.labels.label') 
					Título: 
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="title" 
						placeholder="Ingrese el título" 
						data-validation="required" 
						@if($request->status!=2) disabled="disabled" @endif
						@if(isset($request)) value="{{ $request->purchases->first()->title }}" @endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Fecha:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						removeselect datepicker2
					@endslot
					@slot('attributeEx')
						readonly="readonly" 
						name="datetitle" 
						placeholder="Ingrese la fecha" 
						data-validation="required"
						@if($request->status!=2) disabled="disabled" @endif
						@if(isset($request))
							@if (isset($request->purchases->first()->datetitle))
								value="{{Carbon\Carbon::createFromFormat('Y-m-d',$request->purchases->first()->datetitle)->format('d-m-Y')}}"
							@endif
						@endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Fiscal:
				@endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('attributeEx') id="nofiscal" name="fiscal" value="0" @if(isset($request) && $request->taxPayment==0) checked @endif @if($request->status != 2) disabled @endif @endslot
						@slot('classExContainer') inline-flex @endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx') id="fiscal" name="fiscal" value="1" @if(isset($request) && $request->taxPayment==1) checked @endif @if($request->status != 2) disabled @endif @endslot
						@slot('classExContainer') inline-flex @endslot
						Sí
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Número de Orden (Opcional):
				@endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						removeselect
					@endslot
					@slot('attributeEx')
						name="numberOrder" 
						placeholder="Ingrese el número de orden" 
						@if(isset($request)) value="{{ $request->purchases->first()->numberOrder }}" @endif
						@if($request->status!=2) disabled @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Solicitante:
				@endcomponent
				@php
					$options = collect();
					if(isset($request) &&  isset($request->idRequest))
					{
						$user 	 = App\User::find($request->idRequest);
						$options = $options->concat([['value'=>$user->id, 'selected'=>'selected', 'description'=>$user->name." ".$user->last_name." ".$user->scnd_last_name]]);
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"userid\" id=\"multiple-users\" multiple=\"multiple\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "name=\"userid\" id=\"multiple-users\" multiple=\"multiple\" data-validation=\"required\"";
					}
					$classEx = "js-users removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Empresa:
				@endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
						$options = $options->concat([['value'=>$enterprise->id, 'description'=>$description]]);
					}
					if(isset($request->idEnterprise) && $request->idEnterprise != "")
					{
						$optionSelected = collect($options->where('value', $request->idEnterprise)->first())->put('selected', 'selected');
						$options = $options->concat($options->where('value', $request->idEnterprise)->push($optionSelected));
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"enterpriseid\" multiple=\"multiple\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "name=\"enterpriseid\" multiple=\"multiple\" data-validation=\"required\"";
					}
					
					$classEx = "js-enterprises removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Dirección:
				@endcomponent
				@php
					$options = collect();
					foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
					{
						$options = $options->concat([['value'=>$area->id, 'description'=>$area->name]]);
					}
					if(isset($request->idArea) && $request->idArea != "")
					{
						$optionSelected = collect($options->where('value', $request->idArea)->first())->put('selected', 'selected');
						$options = $options->concat($options->where('value', $request->idArea)->push($optionSelected));
					}
					if($request->status != 2)
					{
						$attributeEx = "multiple=\"multiple\" name=\"areaid\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "multiple=\"multiple\" name=\"areaid\" data-validation=\"required\"";
					}
					$classEx = "js-areas removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Departamento:
				@endcomponent
				@php
					$options = collect();
					foreach(App\Department::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if(isset($request) && $request->idDepartment == $department->id)
						{
							$options = $options->concat([['value'=>$department->id, 'selected'=>'selected', 'description'=>$department->name]]);
						}
						else{
							$options = $options->concat([['value'=>$department->id, 'description'=>$department->name]]);
						}
					}
					if($request->status != 2)
					{
						$attributeEx = "multiple=\"multiple\" name=\"departmentid\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "multiple=\"multiple\" name=\"departmentid\" data-validation=\"required\"";
					}
					$classEx = "js-departments removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Clasificación del gasto:
				@endcomponent
				@php
					$options = collect();
					if(isset($request->account))
					{
						$account = App\Account::find($request->account);
						$options = $options->concat([['value'=>$account->idAccAcc, 'selected'=>'selected', 'description'=>$account->account." - ".$account->description." (".$account->content.")"]]);
					}
					if($request->status != 2)
					{
						$attributeEx = "multiple=\"multiple\" name=\"accountid\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "multiple=\"multiple\" name=\"accountid\" data-validation=\"required\"";
					}
					$classEx = "js-accounts removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Proyecto:
				@endcomponent
				@php
					$options = collect();
					if(isset($request->idProject))
					{
						$project = App\Project::find($request->idProject);
						$options = $options->concat([['value'=>$project->idproyect, 'selected'=>'selected', 'description'=>$project->proyectName]]);
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"projectid\" multiple=\"multiple\" data-validation=\"required\" disabled";
					}
					else
					{
						$attributeEx = "name=\"projectid\" multiple=\"multiple\" data-validation=\"required\"";
					}
					$classEx = "js-projects removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			@if($request->idRequisition != "" || $request->idNomina != "")
				@if($request->idRequisition != "")
					<p>
						@if($request->purchases->first()->requisitionRequest->requisition->wbs()->exists())
							<b>CÓDIGO WBS:</b> {{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}. <br>
						@endif
						@if($request->purchases->first()->requisitionRequest->requisition->edt()->exists())
							<b>CÓDIGO EDT:</b> {{ $request->purchases->first()->requisitionRequest->requisition->edt->fullName() }}.
						@endif
					</p>
				@endif
			@else
				@php
					$arrayProject = Auth::user()->inChargeProject($option_id)->pluck('project_id')->toArray();
				@endphp
				<div class="col-span-2 select_father_wbs @if(isset($request)) @if($request->idProject != '' && in_array($request->idProject, $arrayProject) && $request->requestProject->codeWBS()->exists()) block @else hidden @endif @else block @endif">
					@component('components.labels.label') 
						Código WBS:
					@endcomponent
					@php
						$options = collect();
						if(isset($request->code_wbs))
						{
							$code = App\CatCodeWBS::find($request->code_wbs);
							$options = $options->concat([['value'=>$code->id, 'selected'=>'selected', 'description'=>$code->code_wbs]]);
						}
						if($request->status != 2)
						{
							$attributeEx = "multiple=\"multiple\" name=\"code_wbs\" data-validation=\"required\" disabled";
						}
						else
						{
							$attributeEx = "multiple=\"multiple\" name=\"code_wbs\" data-validation=\"required\"";
						}
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="select_father_edt @if(isset($request)) @if($request->code_wbs != '' && in_array($request->idProject,$arrayProject) && $request->wbs()->exists() && $request->wbs->codeEDT()->exists()) block @else hidden  @endif @else block @endif">
					@component('components.labels.label') 
						Código EDT:
					@endcomponent
					@php
						$options = collect();
						if(isset($request->code_edt))
						{
							$code 	 = App\CatCodeEDT::find($request->code_edt);
							$options = $options->concat([['value'=>$request->code_edt, 'selected'=>'selected', 'description'=>$code->code.' ('.$code->description.')']]);
						}
						if($request->status != 2)
						{
							$attributeEx = "multiple=\"multiple\" name=\"code_edt\" data-validation=\"required\" disabled";
						}
						else
						{
							$attributeEx = "multiple=\"multiple\" name=\"code_edt\" data-validation=\"required\"";
						}
						$classEx = "js-code_edt removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
			@endif
		@endcomponent
		<div class="block p-2" id="form">
			@if($request->status == 2)
				@component('components.labels.title-divisor')
					SELECCIONAR PROVEEDOR <span class="help-btn" id="help-btn-select-provider"> </span>
				@endcomponent
				<div class="flex flex-wrap justify-center w-full space-x-2 pt-4">
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							type="radio" name="prov" id="new-prov" value="nuevo" 
						@endslot
						@slot('classExLabel')
							rounded
						@endslot
						@slot('classExContainer')
							pb-4
						@endslot
						Registrar Nuevo
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							type="radio" name="prov" id="buscar-prov" value="buscar" @if(isset($request)) checked @endif 
						@endslot
						@slot('classExLabel')
							rounded
						@endslot
						@slot('classExContainer')
							pb-4
						@endslot
						Buscar Existente
					@endcomponent
				</div>
				<div class="text-center w-full pb-2">
					<div class=" @if($request->purchases->first()->idRequisition != '') hidden @else block @endif" id="buscar">
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
									placeholder="Ingrese el nombre" 
								@endslot
								@slot('attributeExButton')
									type="button"
								@endslot
								@slot('classExButton')
									button-search
								@endslot
							@endcomponent
						</div>
						<div class="provider pt-2"></div>
					</div>
				</div>
			@endif
			<div id="form-prov" class="request-validate @if(isset($request->purchases->first()->idProvider) && (in_array($request->purchases->first()->provider->status,[0,1,2]) || $request->status != 2)) block @else hidden @endif">
				<div class="container-blocks" id="container-data">
					<div>
						@component('components.labels.subtitle')
							DATOS DEL PROVEEDOR
						@endcomponent
					</div>
					@if($request->status == 2)
						<div class="text-center p-2 checks 
						@if(!isset($request->purchases->first()->idProvider) || $request->purchases->first()->idProvider == '') 
							hidden 
						@else 
							@isset($request->purchases->first()->provider->status) 
								@if($request->purchases->first()->provider->status==1) 
									hidden
								@else
									block
								@endif
							@else
								block
							@endisset  
						@endif">
							@component('components.inputs.switch')
								@slot('attributeEx')
									name="edit" type="checkbox" value="1" id="edit" @if($request->purchases->first()->idRequisition != "") @elseif(isset($request->purchases->first()->provider->status) && $request->purchases->first()->provider->status==0) disabled checked @endif
								@endslot
								Habilitar edición 
							@endcomponent
							<span class="help-btn"></span>
						</div>
					@endif
					<div class="justify-center">
						<input type="hidden" name="provider_data_id" @if(isset($request->purchases->first()->provider)) value="{{ $request->purchases->first()->provider->provider_data_id }}" @endif>
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="idProvider" type="hidden" @if(isset($request->purchases->first()->provider)) value="{{ $request->purchases->first()->provider->idProvider }}" @endif
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="provider_data_id" type="hidden" @if(isset($request->purchases->first()->provider)) value="{{ $request->purchases->first()->provider->provider_data_id }}" @endif
							@endslot
						@endcomponent
						@component("components.containers.container-form")	
							<div class="col-span-2">
								@component('components.labels.label') Razón Social: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->businessName }}" 
											data-validation-req-params="{{ json_encode(array('oldReason'=>$request->purchases->first()->provider->businessName)) }}" 
										@endif
										name="reason" placeholder="Ingrese la razón social" data-validation="length required server" data-validation-length="max150" data-validation-url="{{ route('provider.validation') }}" 
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Calle: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
										name="address" placeholder="Ingrese la calle" data-validation="required length" data-validation-length="max100" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->address }}"
										@endif
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
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif 
										name="number" placeholder="Ingrese el número" data-validation="required length" data-validation-length="max45" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->number }}"
										@endif
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
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
										name="colony" placeholder="Ingrese la colonia" data-validation="required length" data-validation-length="max70" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->colony }}"
										@endif
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Código Postal: @endcomponent
								@php 
									$options = collect();
									if(isset($request->purchases->first()->provider->postalCode))
									{
										$options = $options->concat([['value'=>$request->purchases->first()->provider->postalCode, 'selected'=>'selected', 'description'=>$request->purchases->first()->provider->postalCode]]);
									}
								@endphp
								@component('components.inputs.select', ["options" => $options])
									@slot('attributeEx')
										name="cp" id="cp" placeholder="Ingrese el código postal" multiple="multiple" data-validation="required" data-validation-length="max10"
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
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
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
										name="city" placeholder="Ingrese la ciudad" data-validation="required length" data-validation-length="max70" @if(isset($request)) value="{{ isset($request->purchases->first()->provider->city) ? $request->purchases->first()->provider->city : "" }}" @endif
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Estado: @endcomponent
								@php
									$options = collect();
									foreach (App\State::orderBy('description','asc')->get() as $state)
									{
										if (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->state_idstate == $state->idstate)
										{
											$options = $options->concat([['value'=>$state->idstate, 'selected'=>'selected', 'description'=>$state->description]]);
										}
										else
										{
											$options = $options->concat([['value'=>$state->idstate, 'description'=>$state->description]]);
										}
									}
									$attributeEx = "name=\"state\" multiple=\"multiple\" data-validation=\"required\"";
									if(isset($request->purchases->first()->idRequisition))
									{
										$attributeEx = $attributeEx." disabled";
									} elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2))
									{
										$attributeEx = $attributeEx." disabled";
									}
									
									$classEx = "js-state remove";
								@endphp
								@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') RFC: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="rfc" placeholder="Ingrese el RFC" data-validation="server" data-validation-url="{{ route('provider.validation') }}" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->rfc }}" data-validation-req-params="{{ json_encode(array('oldRfc'=>$request->purchases->first()->provider->idProvider)) }}"
										@endif
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif
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
										name="phone" placeholder="Ingrese el teléfono" data-validation="number" 
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->phone }}"
										@endif
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
										name="contact" placeholder="Ingrese el contacto" data-validation="required" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->contact }}"
										@endif
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif 
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
										name="beneficiary" placeholder="Ingrese el nombre del beneficiario" data-validation="required" 
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->beneficiary }}"
										@endif
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
										name="other" placeholder="Ingrese otro" 
										@if(isset($request->purchases->first()->provider)) 
											value="{{ $request->purchases->first()->provider->commentaries }}" 
										@endif
										@if($request->purchases->first()->idRequisition != "") 
											disabled="disabled" 
										@elseif($request->status != 2 || (isset($request->purchases->first()->provider) && $request->purchases->first()->provider->status == 2)) 
											disabled="disabled" 
										@endif 
									@endslot
								@endcomponent
							</div>
						@endcomponent
					</div>
					<div class="block">
						@component('components.labels.subtitle')
							CUENTAS BANCARIAS <span class="help-btn" id="help-btn-account-bank">
						@endcomponent
						@if($request->status == "2")
							<div id="banks" @if($request->purchases->first()->idRequisition != "") style="display: none" @elseif(isset($request->purchases->first()->provider->status) && $request->purchases->first()->provider->status!=0)  style="display: none" @endif>
								Para agregar una cuenta nueva es necesario colocar los siguientes campos:
								@component("components.containers.container-form")
									@slot('attributeEx')
										id="contentBank"
									@endslot
									<div class="col-span-2">
										@component('components.labels.label') Banco: @endcomponent
										@php
											$options = collect();
											$attributeEx = "multiple=\"multiple\"";
											$classEx = "js-bank";
										@endphp
										@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
										@endcomponent
									</div>
									<div class="col-span-2">
										@component('components.labels.label') Alias: @endcomponent
										@component('components.inputs.input-text')
											@slot('attributeEx')
												placeholder="Ingrese el alias"
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
												placeholder="Ingrese la cuenta bancaria" data-validation="cuenta"
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
												placeholder="Ingrese la sucursal"
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
												placeholder="Ingrese la referencia"
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
												placeholder="Ingrese la CLABE" data-validation="clabe"
											@endslot
											@slot('classEx')
												clabe
											@endslot
										@endcomponent
									</div>
									<div class="col-span-2">
										@component('components.labels.label') Moneda: @endcomponent
										@php
											$options = collect(
												[ 
													['value'=>'MXN', 'description'=>'MXN'], 
													['value'=>'USD', 'description'=>'USD'], 
													['value'=>'EUR', 'description'=>'EUR'], 
													['value'=>'Otro','description'=>'Otro']
												]
											);
											$classEx = "currency";
											$attributeEx = "id=\"currency\"";
										@endphp
										@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
									</div>
									<div class="col-span-2">
										@component('components.labels.label') IBAN: @endcomponent
										@component('components.inputs.input-text')
											@slot('attributeEx')
												placeholder="Ingrese el IBAN" data-validation="iban"
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
												placeholder="Ingrese el BIC/SWIFT" data-validation="bic_swift"
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
												placeholder="Ingrese el convenio"
											@endslot
											@slot('classEx')
												agreement
											@endslot
										@endcomponent
									</div>
									<div class="col-span-2">
										@component('components.buttons.button', ["variant" => "warning"])
											@slot('attributeEx') id='add2' name='add2' type='button' @endslot
											@slot('classEx') add2 @endslot
											<span class="icon-plus"></span>
											<span>Agregar</span>
										@endcomponent
									</div>
								@endcomponent
							</div>
						@endif
						@php
							$notFound  = 0;
							$body 			= [];
							$modelBody		= [];
							$buttonDelete	= [];
							$modelHead 		= ["Seleccionar" ,"Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio"];
							if ($request->status == "2")
							{
								array_push($modelHead, "Acción");
							}
							if(isset($request->purchases->first()->provider->providerData->providerBank))
							{
								$classEx   = "";
								$iban 	   = "";
								$bic_swift = "";
								$agreement = "";
								$checked   = "";

								if($request->status == 2)
								{
									$providerBanks = $request->purchases->first()->provider->providerData->providerBank;
								}
								else
								{
									$providerBanks = $request->purchases->first()->provider->providerData->providerBankToShow($request->purchases->first()->provider_has_banks_id)->get();
								}
								foreach($providerBanks as $bank)
								{
									if($request->purchases->first()->provider_has_banks_id == $bank->id) 
									{
										$classEx="marktr";
										$checked = "checked"; 
									}

									if($bank->iban=='')
									{
										$iban = "---";
									}
									else
									{
										$iban = $bank->iban;
									}

									if($bank->bic_swift=='')
									{
										$bic_swift = "---";
									}
									else
									{
										$bic_swift = $bank->bic_swift;
									}
									
									if($bank->agreement=='')
									{
										$agreement = "---";
									}
									else
									{
										$agreement = $bank->agreement;
									}

									$body = 
									[
										"classEx" => "tr-bankAccount ".$classEx,
										[
											"content" => 
											[
												[
													"kind"             => "components.inputs.checkbox",
													"label"            => "<span class=icon-check></span>", 
													"radio"			   => "true",
													"attributeEx"      => "id=".$bank->id." name=provider_has_banks_id value=".$bank->id." ".$checked,
													"classExContainer" => "inline-flex",
													"classEx"		   => "checkbox",
													"classExLabel"	   => "request-validate".(isset($globalRequests) ? " disabled" : ""),
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"checked[]\" value=\"".$checked."\"",
													"classEx" => "idchecked"
												]
											]
										],
										[
											"classEx" => "countConcept",
											"content" => 
											[
												[
													"label" => isset($bank->bank->description) ? $bank->bank->description : ""
												],
												[
													"kind" => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"providerBank[]\" value=\"".$bank->id."\"",
													"classEx" => "providerBank"
												],
												[
													"kind" => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"bank[]\" value=\"".$bank->banks_idBanks."\""
												]
											]
										],
										[ 
											"show"    => "true",				
											"content" => 
											[
												"label" => isset($bank->alias) ? $bank->alias : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
												]
											]
										],
										[
											"content" => 
											[ 
												"label" => isset($bank->account) ? $bank->account : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => isset($bank->branch) ? $bank->branch : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"branch_office[]\" value=\"".$bank->branch."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => isset($bank->reference) ? $bank->reference : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"reference[]\" value=\"".$bank->reference."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => isset($bank->clabe) ? $bank->clabe : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => isset($bank->currency) ? $bank->currency : "---",
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"currency[]\" value=\"".$bank->currency."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => $iban,
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"iban[]\" value=\"".$bank->iban."\""
												]
											]
										],
										[
											"content" => 
											[
												"label" => $bic_swift,
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"bic_swift[]\" value=\"".$bank->bic_swift."\""
												]
											]
										],
										[
											"content" => 
											[
												["kind"        => "components.labels.label", "classEx"	=>	"text-black", "label" => $agreement],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"greement[]\" value=\"".$bank->agreement."\""
												]
											]
										],
									];
									if ($request->status == "2")
									{
										$buttonDelete	=
										[
											"content" => 
											[
												[
													"kind"  	  => "components.buttons.button",
													"variant" 	  => "red",
													"label" 	  => "<span class='icon-x delete-span'></span>",
													"classEx"	  => "delete-item delete-account",
													"attributeEx" => "type=\"button\"".(isset($globalRequests) ? " disabled" : ""),
												]
											]
										];
									$body[] = $buttonDelete;
									}
									array_push($modelBody, $body);
									$notFound++;
								}
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" 			=> $modelHead,
							"modelBody" 			=> $modelBody,
							"themeBody" 			=> "striped"
						])
							@slot('attributeExBody') 
								id="banks-body" 
							@endslot	
							@slot('classExBody') 
								request-validate
							@endslot	
						@endcomponent
						@if($notFound == 0)
							@component('components.labels.not-found', ["text" => "No se han encontrado cuentas bancarias registradas", "attributeEx" => "id=\"not-found-accounts\""])  @endcomponent
						@endif
					</div>
				</div>
			</div>
			@component('components.labels.title-divisor')
				DATOS DEL PEDIDO <span class="help-btn" id="help-btn-dates"></span>
			@endcomponent
			@if($request->status == "2" && $request->purchases->first()->idRequisition == "")
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component('components.labels.label') Cantidad: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="quantity" placeholder="Ingrese la cantidad"
							@endslot
							@slot('classEx')
								quanty
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Unidad: @endcomponent
						@php
							$options = collect();
							foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
							{
								foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
								{
									$options = $options->concat([['value'=>$child->description, 'description'=>$child->description]]);
								}
							}
							$attributeEx = "name=\"unit\" multiple=\"multiple\"";
							$classEx = "unit";
						@endphp
						@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Descripción: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="description" placeholder="Ingrese la descripción"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Precio Unitario: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="price" placeholder="Ingrese un precio"
							@endslot
							@slot('classEx')
								price
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 ivaKind md:col-span-4 @if(isset($request) && $request->taxPayment == 0) hidden @endif">
						@component('components.labels.label') 
							Tipo de IVA:
							@slot('attributeEx')
								id="label-inline"
							@endslot
						@endcomponent
						<div class="flex space-x-2">
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx') type="radio" name="iva_kind" class="iva_kind" id="iva_no" value="no" checked="" @if(isset($request) && $request->taxPayment == 0) disabled @endif @endslot
									No
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx') type="radio" name="iva_kind" class="iva_kind" id="iva_a" value="a" @if(isset($request) && $request->taxPayment == 0) disabled @endif @endslot
									A
								@endcomponent
							</div>
							<div>
								@component('components.buttons.button-approval')
									@slot('attributeEx') type="radio" name="iva_kind" class="iva_kind" id="iva_b" value="b" @if(isset($request) && $request->taxPayment == 0) disabled @endif @endslot
									B
								@endcomponent
							</div>
						</div>
					</div>
					<div class="col-span-2 md:col-span-4">
						@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'additional'])  @endcomponent
					</div>
					<div class="col-span-2 md:col-span-4">
						@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'retention'])  @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Importe: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								readonly placeholder="Ingrese el importe" name="amount"
							@endslot
							@slot('classEx')
								amount
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx') id='add' name='add' type='button' @endslot
							@slot('classEx') add2 @endslot
							<span class="icon-plus"></span>
							<span>Agregar concepto</span>
						@endcomponent
					</div>
				@endcomponent
			@endif
			@php
				$classTh = "";
				if($request->status == "2" && $request->purchases->first()->idRequisition == "")
				{
					$classTh = "block";
				}
				else
				{
					$classTh = "hidden";
				}
				$body 			= [];
				$modelBody		= [];
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
						["value" => "", "classEx" => $classTh]
					]
				];
				if(isset($request))
				{
					foreach($request->purchases->first()->detailPurchase as $key=>$detail)
					{
						$body = 
						[
							"classEx" => "tr_body",
							[
								"classEx" => "countConcept",
								"content" => 
								[
									["label" => $key+1]
								]
							],		
							[
								"content" => 
								[
									["label" => $detail->quantity],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcategory[]\" value=\"".$detail->category."\"",
										"classEx"     => "tcategory"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tmeasurement[]\" value=\"".$detail->measurement."\"",
										"classEx"     => "tmeasurement"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcode[]\" value=\"".$detail->code."\"",
										"classEx"     => "tcode"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcommentaries[]\" value=\"".$detail->commentaries."\"",
										"classEx"     => "tcommentaries"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\"".$detail->quantity."\"",
										"classEx"     => "tquanty"
									]
								]
							],
							[
								"content" => 
								[ 
									["label" => $detail->unit],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tunit[]\" value=\"".$detail->unit."\"",
										"classEx"     => "tunit"
									]
								]
							],
							[
								"content" => 
								[
									["label" => htmlentities($detail->description)],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\" value=\"".htmlentities($detail->description)."\"",
										"classEx"     => "tdescr"
									],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\" value=\"".$detail->typeTax."\"",
										"classEx"     => "tivakind"
									]
								]
							],
							[
								"content" => 
								[
									["label" => $detail->unitPrice],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tprice[]\" value=\"".$detail->unitPrice."\"",
										"classEx"     => "tprice"
									]
								]
							],
							[
								"content" => 
								[
									["label" => $detail->tax],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tiva[]\" value=\"".$detail->tax."\"",
										"classEx"     => "tiva"
									]
								]
							]
						];
						$taxesConcept=0;
						$taxes_array =
						[
							"content" => 
							[
							]
						];
						foreach($detail->taxes as $tax)
						{
							array_push($taxes_array["content"], 
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"tamountadditional".$taxesCount."[]\" value=\"".$tax->amount."\"",
									"classEx"     => "num_amountAdditional"
								]
							);
							array_push($taxes_array["content"],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"tnameamount".$taxesCount."[]\" value=\"".$tax->name."\"",
									"classEx"     => "num_nameAmount"
								]
							);
							$taxesConcept+=$tax->amount;
						}
						array_push($taxes_array["content"],["label" => "$ ".number_format($taxesConcept,2)]);
						array_push($body, $taxes_array);

						$retentionConcept=0;
						$retentions_array =
						[
							"content" => 
							[
							]
						];
						foreach($detail->retentions as $ret)
						{
							array_push($retentions_array["content"], 
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"tamountretention".$taxesCount."[]\" value=\"".$ret->amount."\"",
									"classEx"     => "num_amountRetention"
								]
							);
							array_push($retentions_array["content"], 
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "num_nameRetention",
									"attributeEx" => "type=\"hidden\" name=\"tnameretention".$taxesCount."[]\" value=\"".$ret->name."\""
								]
							);
							$retentionConcept+=$ret->amount;
						}
						
						$taxesCount++;
						array_push($retentions_array["content"],["label" => "$ ".number_format($retentionConcept,2)]);
						array_push($body, $retentions_array);

						array_push($body, 
							[
								"content" => 
								[
									["label" => "$ ".$detail->amount],
									[
										"kind"        => "components.inputs.input-text",
										"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tamount[]\" value=\"".$detail->amount."\"",
										"classEx"     => "tamount"
									]
								]
							]
						);
						if($request->status == "2" && $request->purchases->first()->idRequisition == "")
						{
							array_push($body, 
							[
								"content" => 
								[
									[
										"kind"        => "components.buttons.button",
										"variant"     => "success",
										"label"       => "<span class=\"icon-pencil\"></span>",
										"classEx"     => "edit-item",
										"attributeEx" => "id=edit",
										"attributeEx" => "type=\"button\"",
									],
									[
										"kind"    	  => "components.buttons.button",
										"variant" 	  => "red",
										"label"   	  => "<span class=\"icon-x delete-span\"></span>",
										"classEx" 	  => "delete-item",
										"attributeEx" => "type=\"button\"",
									]
								]
							]);
						}						
						array_push($modelBody, $body);
					}
				}
			@endphp
			@component('components.tables.table',[
				"modelHead" 			=> $modelHead,
				"modelBody" 			=> $modelBody,
				"themeBody" 			=> "striped"
			])
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('attributeExBody') 
					id="body" 
				@endslot	
			@endcomponent
			@php
				$subtotal  		= "$ ".number_format(0,2,".",",");
				$iva       		= "$ ".number_format(0,2,".",",");
				$total     		= "$ ".number_format(0,2,".",",");
				$taxes_val 		= "$ ".number_format(0,2,".",",");
				$retentions_val = "$ ".number_format(0,2,".",",");
				$notas     		= "name=\"note\" placeholder=\"Ingrese la nota\" cols=\"80\"";
				$textNotes 		= "";

				if(isset($request))
				{
					$subtotal  		= "$ ".number_format($request->purchases->first()->subtotales,2,".",",");
					$iva 	   		= "$ ".number_format($request->purchases->first()->tax,2,".",",");
					$taxes_val 		= "$ ".number_format($taxes,2);
					$retentions_val = "$ ".number_format($retentions,2); 
					$total 			= "$ ".number_format($request->purchases->first()->amount,2,".",",");
					$textNotes 		= isset($request->purchases->first()->notes) ? $request->purchases->first()->notes : "";
					foreach($request->purchases->first()->detailPurchase as $detail)
					{
						foreach($detail->taxes as $tax)
						{
							$taxes += $tax->amount;
						}
					}
					foreach($request->purchases->first()->detailPurchase as $detail)
					{
						foreach($detail->retentions as $ret)
						{
							$retentions += $ret->amount;
						}
					}
				}
				$modelTable = 
				[
					[
						"label"            => "Subtotal: ", 
						"inputsEx"		   => 
											[
												[
													"kind" 		  => "components.labels.label",
													"label"		  => $subtotal,
													"classEx"     => "h-10 py-2 subtotal-label-details"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->subtotales,2,".",",") : "0.00")."\" readonly name=\"subtotal\"",
													"classEx"     => "removeInput"
												]
											]
					],
					[
						"label"            => "Impuesto Adicional: ",	
						"inputsEx"		   => 
											[
												[
													"kind" 		  => "components.labels.label",
													"label"		  => $taxes_val,
													"classEx"     => "h-10 py-2 taxes-label-details"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($taxes,2) : "0.00")."\" readonly name=\"amountAA\"",
													"classEx"     => "removeInput"
												]
											]
					],
					[
						"label"            => "Retenciones: ",	
						"inputsEx"		   => 
											[
												[
													"kind" 		  => "components.labels.label",
													"label"		  => $retentions_val,
													"classEx"     => "h-10 py-2 retentions-label-details"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($retentions,2) : "0.00")."\" readonly name=\"amountR\"",
													"classEx"     => "removeInput"
												]
											]
					],
					[
						"label"            => "IVA: ",	
						"inputsEx"		   => 
						[
							[
								"kind" 		  => "components.labels.label",
								"label"		  => $iva,
								"classEx"     => "h-10 py-2 iva-label-details"
							],
							[
								"kind" 		  => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->tax,2,".",",") : "0.00")."\" readonly name=\"totaliva\"",
								"classEx"     => "removeInput"
							]
						]
					],
					[
						"label"            => "TOTAL: ", 
						"inputsEx"		   => 
						[
							[
								"kind" 		  => "components.labels.label",
								"label"		  => $total,
								"classEx"     => "h-10 py-2 total-label-details"
							],
							[
								"kind" 		  => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->amount,2,".",",") : "0.00")."\" readonly name=\"total\"",
								"classEx"     => "removeInput"
							]
						]
					],
				];
			@endphp
			@component('components.templates.outputs.form-details',[
				"modelTable" 		 => $modelTable,
				"attributeExComment" => $notas,
				"textNotes"          => $textNotes,
				"attributeExComment" => (isset($globalRequests) ? "readonly" : ""),
			])
			@endcomponent
			@component('components.labels.title-divisor')
				CONDICIONES DE PAGO <span class="help-btn" id="help-btn-condition-pay"></span>
			@endcomponent
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component('components.labels.label') Referencia/Número de factura (Opcional): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="referencePuchase" placeholder="Ingrese una referencia" @if(isset($request)) value="{{ $request->purchases->first()->reference }}" @endif  @if($request->status != 2) disabled @endif
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Tipo de moneda: @endcomponent
					@php
						$options = collect(
							[
								['value'=>'MXN', 'description'=>'MXN', "selected" => ((isset($request) && isset($request->purchases->first()->typeCurrency) && $request->purchases->first()->typeCurrency == "MXN") ? "selected" : "")], 
								['value'=>'USD', 'description'=>'USD', "selected" => ((isset($request) && isset($request->purchases->first()->typeCurrency) && $request->purchases->first()->typeCurrency == "USD") ? "selected" : "")], 
								['value'=>'EUR', 'description'=>'EUR', "selected" => ((isset($request) && isset($request->purchases->first()->typeCurrency) && $request->purchases->first()->typeCurrency == "EUR") ? "selected" : "")], 
								['value'=>'Otro', 'description'=>'Otro', "selected" => ((isset($request) && isset($request->purchases->first()->typeCurrency) && $request->purchases->first()->typeCurrency == "Otro") ? "selected" : "")]
							]
						);
						if($request->status != 2)
						{
							$attributeEx = "name=\"type_currency\" data-validation=\"required\" disabled=\"disabled\"";
						} 
						else
						{
							$attributeEx = "name=\"type_currency\" data-validation=\"required\"";
						}
						$classEx = "remove";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent 
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha de pago: @endcomponent
				
					@php
						$date = '';
						if (isset($request->PaymentDate)) 
						{
							$date	= $request->PaymentDate->format('d-m-Y');
						}
					@endphp
					
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="date" placeholder="Seleccione la fecha" step="1" @if($request->status == 2) id="datepicker" @endif value="{{ $date }}" @if($request->status != 2) disabled @endif data-validation="required"
						@endslot
						@slot('classEx')
							remove
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Forma de pago: @endcomponent
					@php
						$options = collect(
							[
								['value'=>'Cheque', 'description'=>'Cheque', "selected" => ((isset($request) && isset($request->purchases->first()->paymentMode) && $request->purchases->first()->paymentMode == "Cheque") ? "selected" : "")], 
								['value'=>'Efectivo', 'description'=>'Efectivo', "selected" => ((isset($request) && isset($request->purchases->first()->paymentMode) && $request->purchases->first()->paymentMode == "Efectivo") ? "selected" : "")], 
								['value'=>'Transferencia', 'description'=>'Transferencia', "selected" => ((isset($request) && isset($request->purchases->first()->paymentMode) && $request->purchases->first()->paymentMode == "Transferencia") ? "selected" : "")]
							]
						);
						if($request->status != 2)
						{
							$attributeEx = "name=\"pay_mode\" data-validation=\"required\" disabled=\"disabled\"";
						}
						else
						{
							$attributeEx = "name=\"pay_mode\" data-validation=\"required\"";
						}
						$classEx = "js-form-pay removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Estado de la factura: @endcomponent
					@php
						$selected = "No Aplica";
						$custom = false;
						if(isset($request))
						{
							if($request->purchases->first()->billStatus && 
							(
								$request->purchases->first()->billStatus != "Pendiente"
								&&
								$request->purchases->first()->billStatus != "Entregado"
								&&
								$request->purchases->first()->billStatus !="No Aplica"))
							{
								$selected = $request->purchases->first()->billStatus;
								$custom = true;
							}
							
								$selected = "Pendiente";
							if($request->purchases->first()->billStatus == "Pendiente" || $request->purchases->first()->billStatus == "")
								$selected = "Pendiente";
							if($request->purchases->first()->billStatus == "Entregado")
								$selected = "Entregado";
							if($request->purchases->first()->billStatus == "No Aplica")
								$selected = "No Aplica";
						}

						$options = collect();
						if($custom)
						{
							$options = $options->concat([['value'=>$request->purchases->first()->billStatus, 'selected'=>'selected', 'description'=>$request->purchases->first()->billStatus]]);
						}
						else
						{
							if($selected != "")
							{
								foreach (['Pendiente', 'Entregado', 'No Aplica'] as $item) {
									$options = $options->concat(
									[
										[
											"value" 		=> $item,
											"description" 	=> $item,
											"selected" 		=> ($item == $selected ? "selected" : "")
										]
									]);
								}
							}
						}
						$attributeEx = "name=\"status_bill\" data-validation=\"required\"".(isset($globalRequests) ? " disabled" : "");
						$classEx = "js-ef removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Importe a pagar: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="amount_total" placeholder="Ingrese el importe" value="@if($request->status != 2)$ @endif {{ number_format($request->purchases->first()->amount,2) }}" @if($request->status != 2) disabled @endif data-validation="required"
						@endslot
						@slot('classEx')
							amount_total remove
						@endslot
					@endcomponent
				</div>
			@endcomponent

			@component('components.labels.title-divisor')
				DOCUMENTOS
			@endcomponent
			@php
				$noDocuments  = true;
				$countConcept = 1;
				$body         = [];
				$modelBody    = [];
				$modelHead    = ["Tipo de documento", "Archivo", "Fecha"];
			
				if(count($request->purchases->first()->documents)>0)
				{
					$noDocuments = false;
					foreach($request->purchases->first()->documents as $doc)
					{
						$body = 
						[
							[
								"content" =>
								[
									"label" => $doc->name
								]
							],
							[ 
								"content" =>
								[
									[
										"kind"          => "components.buttons.button", 
										"variant"       => "secondary",
										"label"         => "Archivo",
										"buttonElement" => "a",
										"attributeEx"   => "target = \"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\""
									]
								]
							],
							[
								"content" =>
								[
									"label" => $doc->date->format('d-m-Y')
								]
							]
						];

						array_push($modelBody, $body);
						$countConcept++;
					}
				}
			@endphp
			@if($noDocuments)
				@component("components.labels.not-found", ["text" => "No se han encontrado documentos registrados"]) @endcomponent
			@else
				@component('components.tables.alwaysVisibleTable',[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
				@endcomponent 
			@endif
			@if (!isset($globalRequests))
				@component('components.labels.title-divisor') CARGAR DOCUMENTOS @endcomponent
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
			@endif

			@if($request->idCheck != "")
				@component('components.labels.title-divisor') 
					DATOS DE REVISIÓN 
					@slot('classExContainer')
						pb-4
					@endslot
				@endcomponent
				@php
					$date = $request->PaymentDate != '' ? $request->PaymentDate->format('d-m-Y') : "";
					$reviewAccount = App\Account::find($request->accountR);
					$labels = "";
					foreach($request->labels as $label)
					{
						$labels = $labels." ".$label->description;
					}
					if($request->status == 4)
					{
						$modelTable =
						[
							"Revisó " => $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
							"Nombre de la Empresa " => $request->reviewedEnterprise->name,
							"Nombre de la Dirección " => $request->reviewedDirection->name,
							"Nombre del Departamento " => $request->reviewedDepartment->name,
							"Clasificación del gasto " => isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description: "No hay",
							"Nombre del Proyecto " => $request->reviewedProject->name,
							"Etiquetas " => $labels != "" ? $labels: "No hay etiquetas",
							"Comentarios " => $request->checkComment== "" ? "Sin comentarios": $request->checkComment 
						];
					}
					else
					{
						$modelTable =
							[
								"Revisó " => $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
								"Comentarios " => $request->checkComment== "" ? "Sin comentarios": htmlentities($request->checkComment),
							];
					}
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
				@if($request->idEnterpriseR != "")
					@component('components.labels.title-divisor') 
						ETIQUETAS ASIGNADAS
						@slot('classExContainer')
							pb-4
						@endslot
					@endcomponent
					@php
						$body 	    = [];
						$modelBody = [];
						$modelHead = ["Cantidad", "Descripción" ,"Etiquetas"];

						foreach($request->purchases->first()->detailPurchase as $detail)
						{
							$labels = "";
							foreach($detail->labels as $label)
							{
								$labels = $labels.", ".$label->label->description; 
							}
							$body = 
							[
								[
									"content" =>
									[
										"label" => $detail->quantity." ".$detail->unit
									]
								],
								[ 
									"content" =>
									[
										[
											"label" => htmlentities($detail->description),
										]
									]
								],
								[
									"content" =>
									[
										"label" => $labels
									]
								]
							];
							array_push($modelBody, $body);
						}
					@endphp
					@component('components.tables.alwaysVisibleTable',[
						"modelHead" 			=> $modelHead,
						"modelBody" 			=> $modelBody,
						"themeBody" 			=> "striped"
					])
						@slot('attributeExBody')
							id="tbody-conceptsNew"
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
						@slot('attributeEx')
							id="table"
						@endslot
					@endcomponent
				@endif
			@endif
			@if($request->idAuthorize != "")
				@component('components.labels.title-divisor') 
					DATOS DE AUTORIZACIÓN
					@slot('classExContainer')
						pb-4
					@endslot
				@endcomponent
				@php
					$date = $request->PaymentDate != '' ? $request->PaymentDate->format('d-m-Y') : "";
					$reviewAccount = App\Account::find($request->accountR);
					$labels = "";
					foreach($request->labels as $label)
					{
						$labels = $labels." ".$label->description;
					}
					$modelTable =
					[
						"Autorizó"     => $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
						"Comentarios"  => $request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
					];
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
				@endcomponent
			@endif

			@if($request->status == 13)
				@component('components.labels.title-divisor') 
					DATOS DE PAGOS
					@slot('classExContainer')
						pb-4
					@endslot
				@endcomponent
				@php
					$modelTable =
					[
						"Comentarios"  => $request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment),
					];
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
				@endcomponent
			@endif

			@component('components.labels.title-divisor') 
				HISTORIAL DE PAGOS
				@slot('classExContainer')
					pb-4
				@endslot
			@endcomponent
			@php
				$payments 		= App\Payment::where('idFolio',$request->folio)->get();
				$total 			= $request->purchases->first()->amount;
				$totalPagado 	= 0;
				if(count($payments)>0)
				{
					$body 		= [];
					$modelBody 	= [];
					$modelHead	= 
					[
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
									"variant"		=> "dark-red",
									"buttonElement" => "a",
									"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
									"label"			=> 'PDF'
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
				}
			@endphp
			@if(count($payments) > 0)
				@component('components.tables.table',[
					"modelHead" 			=> $modelHead,
					"modelBody" 			=> $modelBody,
					"themeBody" 			=> "striped"
				])
				@endcomponent
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
				@endcomponent
			@else
				@component("components.labels.not-found", ["text" => "No se han encontrado pagos registrados"]) @endcomponent
			@endif
			@php
				$i = 1; 
				if(isset($request))
				{
					$partialPayments	= $request->purchases->first()->partialPayment;
					$editable			= true;
				}
				else
				{
					$editable 	= true;
				}
			@endphp
			<input value="{{ $request->purchases->first()->amount }}" class="request_total" type="hidden">
			<input class="partials_total" type="hidden">
			@include('administracion.compra.form.partial')

			<span id="spanDelete"></span>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
				@if($request->status == "2")
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx') 
							type="submit"  
							name="enviar"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto
						@endslot
						ENVIAR SOLICITUD
					@endcomponent	
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx') 
							type="button"  
							name="save" 
							id="save" 
							formaction="{{ route('purchase.follow.updateunsent', $request->folio) }}"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto
						@endslot
						GUARDAR SIN ENVIAR
					@endcomponent
					@component("components.buttons.button",["variant" => "reset"])
						@slot('buttonElement')
							a
						@endslot 
						@slot('attributeEx') 
							@if(isset($option_id)) 
								href="{{ url(App\Module::find($option_id)->url) }}"
							@else 
								href="{{ url(App\Module::find($child_id)->url) }}"
							@endif 
						@endslot
						@slot('classEx') 
							load-actioner w-48 md:w-auto text-center
						@endslot
						REGRESAR
					@endcomponent	
				@else
					@if (!isset($globalRequests))
						@component("components.buttons.button",["variant" => "secondary"])
							@slot('attributeEx') 
								type="button"  
								name="save" 
								id="save" 
								formaction="{{ route('purchase.updatebill', $request->folio) }}"
							@endslot
							@slot('classEx') 
								w-48 md:w-auto
							@endslot
							GUARDAR SIN ENVIAR
						@endcomponent
					@endif
					@component("components.buttons.button",["variant" => "reset"])
						@slot('buttonElement')
							a
						@endslot 
						@slot('attributeEx')
							@if(isset($option_id)) 
								href="{{ url(App\Module::find($option_id)->url) }}"
							@else 
								href="{{ url(App\Module::find($child_id)->url) }}"
							@endif 
						@endslot
						@slot('classEx')
							load-actioner w-48 md:w-auto text-center
						@endslot
						REGRESAR
					@endcomponent
				@endif
			</div>
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
		generalSelect({'selector': '.js-users', 'model': 36});
		generalSelect({'selector': '.js-projects', 'model': 17, 'option_id': {{$option_id}} });
		generalSelect({'selector': '.js-code_wbs', 'depends':'.js-projects', 'model':1});
		generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
		generalSelect({'selector': '.js-accounts', 'depends':'.js-enterprises', 'model':10});
		generalSelect({'selector': '.js-bank', 'model': 27});
		generalSelect({'selector': '#cp', 'model': 2});
		@component('components.scripts.taxes',['type'=>'taxes','name' => 'additional','function'=>'calcule_amount'])  @endcomponent
		@component('components.scripts.taxes',['type'=>'retention','name' => 'retention','function'=>'calcule_amount'])  @endcomponent
		@php
			$selects = collect([
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-state",
					"placeholder"            => "Seleccione un Estado",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-form-pay", 
					"placeholder"            => "Seleccione la forma de pago", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-ef",
					"placeholder"            => "Seleccione el estado", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-areas", 
					"placeholder"            => "Seleccione la dirección", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-departments", 
					"placeholder"            => "Seleccione el departamento", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".currency, [name=type_currency]",
					"placeholder"            => "Seleccione el tipo de moneda", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=unit]", 
					"placeholder"            => "Seleccione la unidad", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-partial", 
					"placeholder"            => "Seleccione el porcentaje/neto", 
					"maximumSelectionLength" => "1"
				],
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])
		@endcomponent
		$('[name="quantity"],[name="price"],[name="additionalAmount"],[name="retentionAmount"],[name="amountTotal"]').on("contextmenu",function(e)
		{
			return false;
		});
		count	= 0;
		countB	= {{ $taxesCount }};
		$('.phone,.clabe,.account,.cp').numeric(false);    // números
		$('.price, .dis').numeric({ negative : false });
		$('.quanty').numeric({ negative : false });
		$('.amount,.tquanty,.tprice,.tamount,.descuento,.totaliva,.subtotal,.total,.amountAdditional,.retentionAmount',).numeric({ altDecimal: ".", decimalPlaces: 2 });
		total_cal();
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
			$("#datepicker").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			$(".datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
		});
		$('.request_total').trigger('change');
		$(document).on('click','#add',function()
		{
			countConcept		= $('.countConcept').length;
			cant				= $('input[name="quantity"]').removeClass('error').val();
			unit				= $('[name="unit"] option:selected').removeClass('error').val();
			descr				= $('input[name="description"]').removeClass('error').val();
			precio				= $('input[name="price"]').removeClass('error').val();
			ivakind 			= $('input[name="iva_kind"]:checked').val();
			iva					= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2				= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc				= 0;
			taxesConcept 		= 0;
			retentionConcept 	= 0;
			$('[name="unit"]').parent('div').find('.form-error').remove();
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
			if (cant == "" || descr == "" || precio == "" || unit == "" || unit == undefined)
			{
				if(cant=="")
				{
					$('input[name="quantity"]').addClass('error');
				}
				if(unit=="" || unit == undefined)
				{
					$('[name="unit"]').parent('div').append($('<span class="help-block form-error">Este campo es obligatorio</span>'));
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
				nameAmounts = $('<div class="hidden"></div>');
				$('.additionalName').each(function(i,v)
				{
					nameAmount = $(this).val();
					@php
						$input = view('components.inputs.input-text',[
							"classEx" => "num_nameAmount",
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
				if( ((cant*precio)+ivaCalc+taxesConcept-retentionConcept) < 0)
				{
					swal('', 'El importe no puede ser negativo.', 'error');
				}
				else if(((cant*precio)+ivaCalc+taxesConcept-retentionConcept) == 0)
				{
					swal('', 'El importe total no puede ser igual a cero', 'error');
					return false;
				}
				else 
				{
					countConcept = countConcept+1;
					@php
						$body 		= [];
						$modelBody	= [];
						$modelHead	= [
							[
							["value" => "#", "show" => "true"],
							["value" => "Cantidad", "show" => "true"],
							["value" => "Unidad", "show" => "true"],
							["value" => "Descripci&oacute;n", "show" => "true"],
							["value" => "Precio Unitario"],
							["value" => "IVA"],
							["value" => "Impuesto adicional"],
							["value" => "Retenciones"],
							["value" => "Importe"],
							["value" => ""]
							]
						];
					
						$modelBody = 
						[
							[
								"classEx" => "tr_body",
								[
									"show"    => "true",
									"classEx" => "countConcept",
									"content" => 
									[
										"label" => ""
									]
								],
								[ 
									"show"    => "true",				
									"content" => 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcategory[]\"",
											"classEx"     => "tcategory"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcode[]\"",
											"classEx"     => "tcode"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tcommentaries[]\"",
											"classEx"     => "tcommentaries"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tquanty[]\" value=\" \"",
											"classEx"     => "tquanty"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tmeasurement[]\" value=\"\"",
											"classEx"     => "tmeasurement"
										]
									]
								],
								[
									"show" => "true",
									"content" => 
									[ 
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tunit[]\"",
											"classEx"     => "tunit"
										]
									]
								],
								[
									"show" => "true",
									"content" => 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdescr[]\"",
											"classEx"     => "tdescr"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tivakind[]\"",
											"classEx"     => "tivakind"
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tprice[]\"",
											"classEx"     => "tprice"
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
											"classEx"     => "tiva"
										]
									]
								],
								[
									"classEx" => "taxes_td",
									"content" => 
									[
										"label" => ""
									]
								],
								[
									"classEx" => "retentions_td",
									"content" => 
									[
										"label" => ""
									]
								],
								[
									"content" => 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tamount[]\"",
											"classEx"     => "tamount"
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"        => "components.buttons.button",
											"label"       => "<span class=\"icon-pencil\"></span>",
											"variant"     => "success",
											"classEx"     => "edit-item",
											"attributeEx" => "id=\"edit\" type=\"button\""
										],
										[
											"kind"    	  => "components.buttons.button",
											"variant" 	  => "red",
											"label"   	  => "<span class=\"icon-x delete-span\"></span>",
											"classEx" 	  => "delete-item",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
							
						$table = view('components.tables.table',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"	=> "true",
							"themeBody" => "striped"
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(table);
					rowColor('#body',row);
					row.find('.countConcept').text(countB+1);
					row.find('[name="tquanty[]"]').parent().prepend(cant);
					row.find('[name="tquanty[]"]').val(cant);
					row.find('[name="tunit[]"]').val(unit);
					row.find('[name="tunit[]"]').parent().prepend(unit);
					row.find('[name="tdescr[]"]').val(descr);
					row.find('[name="tdescr[]"]').parent().prepend(descr);
					row.find('[name="tivakind[]"]').val(ivakind);
					row.find('[name="tprice[]"]').val(precio);
					row.find('[name="tprice[]"]').parent().prepend('$ '+Number(precio).toFixed(2));
					row.find('[name="tiva[]"]').val(ivaCalc);
					row.find('[name="tiva[]"]').parent().prepend('$ '+Number(ivaCalc).toFixed(2));
					row.find('.taxes_td :first-child').prepend('$ '+Number(taxesConcept).toFixed(2));
					row.find('.taxes_td').append(nameAmounts).append(amountsAA);
					row.find('.retentions_td :first-child').prepend('$ '+Number(retentionConcept).toFixed(2));
					row.find('.retentions_td').append(nameRetentions).append(amountsRetentions);
					@php
						$input = view('components.inputs.input-text',[
							"classEx" => "ttotal",
							"attributeEx" => "readonly=true type=hidden"
						])->render();
					@endphp
					input = '{!!preg_replace("/(\r)*(\n)*/", "", $input)!!}';
					input_row = $(input);
					input_row.val(((cant*precio)+ivaCalc))
					row.find('[name="tamount[]"]').val(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept);
					row.find('[name="tamount[]"]').parent().prepend('$ '+Number(((cant*precio)+ivaCalc+taxesConcept)-retentionConcept).toFixed(2));
					row.find('[name="tamount[]"]').parent().append(input_row);	
					$('#body').append(row);
					$('input[name="quantity"]').removeClass('error').val("");
					$('input[name="description"]').removeClass('error').val("");
					$('input[name="price"]').removeClass('error').val("");
					$('input[name="amount"]').removeClass('error').val("");
					$('input[name="iva"]').prop('checked',false);
					$('input[name="iva_kind"]').prop('checked',false);
					additionalCleanComponent();
					retentionCleanComponent();
					$('#iva_no').prop('checked',true);
					$('[name="unit"]').val('').trigger('change');
					total_cal();
					//totalPartialPayments();
					countB++;
				}
			}
		})
		.on('change','.quanty,.price,.iva_kind,.additionalAmount,.additionalCheckComponent,.retentionAmount,.addiotional,.retention',function()
		{
			calcule_amount();
		})
		.on('change','input[name="prov"]',function()
		{
			if ($('input[name="prov"]:checked').val() == "nuevo") 
			{
				$("#form-prov").slideDown("slow");
				$('input[name="idProvider"]').val('');
				$('input[name="provider_data_id"]').val('');
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
				$("#buscar").slideUp('fast');
				$(".checks").hide();
				$('#banks-body').html('');
				$('#banks').show();
				$('#banks-body .delete-item').show();
				generalSelect({'selector': '#cp', 'model': 2});
				generalSelect({'selector': '.js-bank', 'model': 27});
				@php
					$selects = collect([
						[
							"identificator"          => ".currency", 
							"placeholder"            => "Seleccione el tipo de moneda", 
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".js-state",
							"placeholder"            => "Seleccione un Estado",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])
				@endcomponent
			}
			else if ($('input[name="prov"]:checked').val() == "buscar") 
			{
				$("#buscar").slideDown("slow");
				$("#form-prov").slideUp('fast');
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
			request_total		= Number($('.request_total').val()).toFixed(2);
			partials_total		= Number($('.partials_total').val()).toFixed(2);
			partialTypePayment	= $('.partialTypePayment option:selected').val();
			partialPayment		= $('.partialPayment').val();
			partialDate			= $('.partialDate').val();
			docsNew 			= $('.docsNew').length;
			docsExists 			= $('.docsExists').length;

			if ($('#partialForms').is(':visible') && (partialTypePayment == "0" || partialTypePayment == "1" || partialPayment != "" || partialDate != "" || docsNew > 0 || docsExists > 0))  
			{
				swal('', 'Tiene un parcialidad de pago sin agregar', 'error');
				return false;				
			}
			else if ($('#body .tr').length > 0 && $('#bodyPartial .tr').length > 0 && Number(partials_total) > Number(request_total))
			{
				swal('', 'El total de la solicitud no coincide con el total de las parcialidades.', 'error');
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
			$("#form").slideDown("slow");
			if ($('input[name="fiscal"]:checked').val() == "1") 
			{
				$('.ivaKind').removeClass('hidden');
				$('.iva_kind').prop('disabled',false);
				$('#iva_no').prop('checked',true);
				$('.iva_kind').parent('p').stop(true,true).fadeIn();
				$('input[name=rfc]').attr('data-validation','rfc required server');
			}
			else if ($('input[name="fiscal"]:checked').val() == "0") 
			{
				$('.ivaKind').addClass('hidden');
				$('.iva_kind').prop('disabled',true);
				$('#iva_no').prop('checked',true);
				$('.iva_kind').parent('p').stop(true,true).fadeOut();
				$('input[name=rfc]').removeAttr('data-validation','required');
			}
		})
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts').val(null).trigger('change');
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
		.on('change','.quanty,.price,.iva_kind,.additionalAmount,.retentionAmount,.addiotional,.retention',function()
		{
			calcule_amount();
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
						generalSelect({'selector': '.js-bank', 'model': 27});
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
						$('input[name="other"]').prop('disabled',true);
						$('#banks').hide();
						$('#banks-body .delete-item').hide();
						$(this).prop('checked',false);
						generalSelect({'selector': '.js-bank', 'model': 27});
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
						$('input[name="other"]').prop('disabled',true);
						$('#banks').hide();
						$('#banks-body .delete-item').hide();
						generalSelect({'selector': '.js-bank', 'model': 27});
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
						$('input[name="other"]').prop('disabled',false);
						$('#banks').show();
						$('#banks-body .delete-item').show();
						$(this).prop('checked',true);
						generalSelect({'selector': '.js-bank', 'model': 27});
					}
				});
			}
		})
		.on('click','.delete-item',function()
		{
			swal({
				title		: "Eliminar concepto",
				text		: "Al eliminar, se eliminarán los pagos programados ¿Desea continuar?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((continuar) =>
			{
				if(continuar)
				{
					$(this).parents('.tr').remove();
					countB = $('#body .tr').length;
					$('#body .tr').each(function(i,v)
					{
						$(this).find('.num_nameAmount').attr('name','tnameamount'+i+'[]');
						$(this).find('.num_amountAdditional').attr('name','tamountadditional'+i+'[]');
						$(this).find('.num_nameRetention').attr('name','tnameretention'+i+'[]');
						$(this).find('.num_amountRetention').attr('name','tamountretention'+i+'[]');
					});
					total_cal();
					if($('.countConcept').length>0)
					{
						$('.countConcept').each(function(i,v)
						{
							$(this).html(i+1);
						});
					}
					$('#bodyPartial').children('.trPartial').each(function()
					{
						$(this).remove();
						totalPartialPayments();
						$('#partialForms').hide();
						$('#cancelPaymentProgram').hide();
						$('#activePaymentProgram').show();
					});
				}
				else
				{
					swal.close();
				}
			});
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
					text		: "Al editar, se eliminarán los pagos programados, los impuestos adicionales y retenciones agregados ¿Desea continuar?",
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
						$('#bodyPartial').children('.trPartial').each(function()
						{
							$(this).remove();
							totalPartialPayments();
							$('#partialForms').hide();
							$('#cancelPaymentProgram').hide();
							$('#activePaymentProgram').show();
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
			bank			= $('.js-bank :selected').val();
			bankName		= $('.js-bank :selected').text();
			account			= $('.account').val();
			branch_office	= $('.branch_office').val();
			reference		= $('.reference').val();
			clabe			= $('.clabe').val();
			currency		= $('.currency').val();
			agreement		= $('.agreement').val();
			alias 			= $('.alias').val();
			iban			= $('.iban').val();
			bic_swift 		= $('.bic_swift').val();
			validBicSwiftIban	= $('.bic_swift').hasClass('error') || $('.iban').hasClass('error');
			if (validBicSwiftIban == true)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				return false;
			}
			else
			{
				iban			= $('.iban').val();
				bic_swift 		= $('.bic_swift').val();
			}
			if(bank!=undefined)
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
				else if($(this).parents('#contentBank').find('.clabe').parent('div').hasClass('has-error') || $(this).parents('#contentBank').find('.account').parent('div').hasClass('has-error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					flag = false;
					$('#banks-body .tr-bankAccount').each(function()
					{
						tr_account	= $(this).find('[name="account[]"]').val();
						tr_clabe	= $(this).find('[name="clabe[]"]').val();
						tr_bank		= $(this).find('[name="bank[]"]').val();
						if(clabe != "" && tr_clabe != "" && clabe == tr_clabe && bank == tr_bank)
						{
							swal('','La CLABE ya se encuentra registrada, por favor verifique sus datos.','error');
							$('.clabe').removeClass('valid').addClass('error');
							flag = true;
						}
						if(account != "" && tr_account != "" && account == tr_account && bank == tr_bank)
						{
							swal('','La cuenta ya se encuentra registrada, por favor verifique sus datos.','error');
							$('.account').removeClass('valid').addClass('error');
							flag = true;
						}
					});
					if(!flag)
					{
						@php
							$modelHead = ["Seleccionar" ,"Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acción"];
								
							$modelBody = 
							[
								[
									"classEx" => "tr-bankAccount",
									[
										"content" => 
										[
											[
												"kind"             => "components.inputs.checkbox",
												"label"            => "<span class=\"icon-check\"></span>", 
												"attributeEx"      => "name=\"provider_has_banks_id\"",
												"radio"			   => "true",
												"classExContainer" => "inline-flex",
												"classEx"		   => "checkbox",
												"classExLabel"	   => "labelCheck request-validate"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"checked[]\"",
												"classEx"     => "idchecked"
											],
										]
									],
									[
										"show"    => "true",
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"classEx"     => "providerBank",
												"attributeEx" => "type=\"hidden\" name=\"providerBank[]\""
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"bank[]\""
											]
										]
									],
									[ 
										"show"    => "true",
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"alias[]\"",
												"classEs"     => "alias_row"
											]
										]
									],
									[
										"show"    => "true",
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"account[]\"",
												"classEx"     => "account_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"branch_office[]\"",
												"classEx"     => "branch_office_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"reference[]\"",
												"classEx"     => "reference_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"clabe[]\"",
												"classEx"     => "clabe_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"currency[]\"",
												"classEx"     => "currency_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"iban[]\"",
												"classEx"     => "iban_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"bic_swift[]\"",
												"classEx"     => "switf_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"agreement[]\"",
												"classEs"     => "areement_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"    	  => "components.buttons.button",
												"variant" 	  => "red",
												"label"   	  => "<span class=\"icon-x delete-span\"></span>",
												"classEx" 	  => "delete-item delete-account",
												"attributeEx" => "type=\"button\""
											]
										]
									]
								]
							];
							$table = view('components.tables.alwaysVisibleTable',[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"themeBody" => "striped",
								"noHead"    => true
							])->render();
							$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
						@endphp

						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						row = $(table);
						row.find('[name="providerBank[]"]').val(bank);
						row.find('[name="providerBank[]"]').parent().prepend(bankName);
						row.find('[name="bank[]"]').val(bank);
						row.find('[name="alias[]"]').val(alias);
						row.find('[name="alias[]"]').parent().prepend(alias !="" ? alias : "---");
						row.find('[name="account[]"]').val(account);
						row.find('[name="account[]"]').parent().prepend(account !="" ? account : "---");
						row.find('[name="branch_office[]"]').val(branch_office);
						row.find('[name="branch_office[]"]').parent().prepend(branch_office !="" ? branch_office : "---");
						row.find('[name="reference[]"]').val(reference);
						row.find('[name="reference[]"]').parent().prepend(reference !="" ? reference : "---");
						row.find('[name="clabe[]"]').val(clabe);
						row.find('[name="clabe[]"]').parent().prepend(clabe !="" ? clabe : "---");
						row.find('[name="currency[]"]').val(currency);
						row.find('[name="currency[]"]').parent().prepend(currency);
						row.find('[name="iban[]"]').val(iban);
						row.find('[name="iban[]"]').parent().prepend(iban !="" ? iban : "---");
						row.find('[name="bic_swift[]"]').val(bic_swift);
						row.find('[name="bic_swift[]"]').parent().prepend(bic_swift !="" ? bic_swift : "---");
						row.find('[name="agreement[]"]').val(agreement);
						row.find('[name="agreement[]"]').parent().prepend(agreement !="" ? agreement : "---");
						row.find('.checkbox').attr('id', bank);
						row.find('.labelCheck').attr('for', bank);
						row.find('.checkbox').val(bank);
						$('#banks-body').append(row);
						$('.clabe, .account,.iban,.bic_swift').removeClass('valid').val('');
						$('.branch_office,.reference,.currency,.agreement,.alias,.iban,.bic_swift').val('');
						$('.currency').val(0).trigger('change');
						$('#banks-body').find('.error').removeClass('error');
						$('.js-bank').val(0).trigger("change");
						count++;
						total_cal();
						$('#not-found-accounts').hide();
					}
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
			provider_search(1)
		})
		.on('click','.paginate a', function(e)
		{
			e.preventDefault();
			provider_data_id = $('[name="provider_data_id"]').val();
			href	=	$(this).attr('href');
			url		=	new URL(href);
			params	=	new URLSearchParams(url.search);
			page	=	params.get('page');
			provider_search(page,provider_data_id);
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
			$('input[name="provider_data_id"]').val(json.provider.provider_data_id);
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
			bankName = $(this).siblings("[name=\"name-bank-provider\"]").val();
			$.each(json.banks,function(i,v)
			{
				bankName = $(this).siblings(".name-bank-provider").val();
				@php
					$modelHead = ["", "Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", ""];
					$modelBody = 
					[
						[
							"classEx" => "tr-bankAccount",
							[
								"content" => 
								[
									[
										"kind"             => "components.inputs.checkbox",
										"label"            => "<span class=icon-check></span>", 
										"attributeEx"      => "name=provider_has_banks_id",
										"radio"			   => "true",
										"classExContainer" => "inline-flex",
										"classEx"		   => "checkbox",
										"classExLabel"	   => "labelCheck request-validate"
									],
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=checked[] value=0",
										"classEx" 	  => "idchecked"
									]
								]
							],
							[
								"show" => "true",
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"classEx" 	  => "providerBank",
										"attributeEx" => "type=hidden name=providerBank[]"
									],
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=bank[]"
									]
								]
							],
							[ 
								"show" 	  => "true",
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=alias[]",
										"classEs" 	  => "alias_row"
									]
								]
							],
							[
								"show"	  => "true",
								"content" => 
								[ 
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=account[]",
										"classEx" 	  => "account_row"
									]
								]
							],
							[
								"content" => 
								[ 
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=branch_office[]",
										"classEx" 	  => "branch_office_row"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=reference[]",
										"classEx" 	  => "reference_row"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"clabe[]\"",
										"classEx" 	  => "clabe_row"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=currency[]",
										"classEx" 	  => "currency_row"
									]
								]
							],
							[
								"content" => 
								[ 
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=iban[]",
										"classEx" 	  => "iban_row"
									]
								]
							],
							[
								"content" => 
								[ 
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=bic_swift[]",
										"classEx" 	  => "switf_row"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"  	  => "components.inputs.input-text",
										"attributeEx" => "type=hidden name=agreement[]",
										"classEs" 	  => "areement_row"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"    	  => "components.buttons.button",
										"variant" 	  => "red",
										"label"   	  => '<span class="icon-x delete-span"></span>',
										"classEx" 	  => "delete-item delete-account",
										"attributeEx" => "type=\"button\""
									]
								]
							]
						]
					];
					$table = view('components.tables.alwaysVisibleTable',[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"themeBody" => "striped",
						"noHead"    => true
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row = $(table);
				row.find('[name="providerBank[]"]').val(v.id);
				row.find('[name="bank[]"]').val(v.banks_idBanks);
				row.find('[name="bank[]"]').parent().prepend(bankName == null ? '---' : bankName);
				row.find('[name="alias[]"]').val(v.alias);
				row.find('[name="alias[]"]').parent().prepend(v.alias == null ? '---' : v.alias);
				row.find('[name="account[]"]').val(v.account)
				row.find('[name="account[]"]').parent().prepend(v.account == null ? '---' : v.account)
				row.find('[name="branch_office[]"]').val(v.branch)
				row.find('[name="branch_office[]"]').parent().prepend(v.branch == null ? '---' : v.branch)
				row.find('[name="reference[]"]').val(v.reference)
				row.find('[name="reference[]"]').parent().prepend(v.reference == null ? '---' : v.reference)
				row.find('[name="clabe[]"]').val(v.clabe)
				row.find('[name="clabe[]"]').parent().prepend(v.clabe == null ? '---' : v.clabe)
				row.find('[name="currency[]"]').val(v.currency)
				row.find('[name="currency[]"]').parent().prepend(v.currency == null ? '---' : v.currency)
				row.find('[name="iban[]"]').val(v.iban)
				row.find('[name="iban[]"]').parent().prepend(v.iban ==null ? '---' :v.iban)
				row.find('[name="bic_swift[]"]').val(v.bic_swift)
				row.find('[name="bic_swift[]"]').parent().prepend(v.bic_swift ==null ? '---' :v.bic_swift)
				row.find('[name="agreement[]"]').val(v.agreement)
				row.find('[name="agreement[]"]').parent().prepend(v.agreement ==null ? '---' :v.agreement)
				row.find('.checkbox').attr('id', v.id);
				row.find('.labelCheck').attr('for', v.id);
				row.find('.checkbox').val(v.id);
				$('#banks-body').append(row);
			});
			$('input[name="edit"]').prop('checked',false);
			$('#form-prov').slideDown();
			$(".checks").show();
			$('#banks').hide();
			$('.provider').stop().fadeOut();
			$('#banks-body .delete-item').hide();
			count++;
			generalSelect({'selector': '.js-bank', 'model': 27});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-state",
						"placeholder"            => "Seleccione un Estado",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		})
		.on('click','.checkbox',function()
		{
			$('.idchecked').val('0');
			$('.marktr').removeClass('marktr');
			$(this).parents('.tr').addClass('marktr');
			$(this).parents('.tr').find('.idchecked').val('1');
		})
		.on('click','.span-delete',function()
		{
			$(this).parents('span').remove();
		})
		.on('click','#help-btn-select-provider',function()
		{
			swal('Ayuda','En este apartado debe seleccionar un proveedor. De clic en "Buscar" si va a tomar un proveedor ya existe. De clic en "Nuevo" si desea agregar un proveedor en caso de no encontrarlo en el buscador.','info');
		})
		.on('click','#help-btn-account-bank',function()
		{
			swal('Ayuda','En este apartado debe seleccionar una cuenta bancaria del proveedor. De clic en el ícono que se encuentra al final de cada cuenta para seleccionarla.','info');
		})
		.on('click','#help-btn-dates',function()
		{
			swal('Ayuda','En este apartado debe agregar cada uno de los conceptos pertenecientes al pedido.','info');
		})
		.on('click','#help-btn-condition-pay',function()
		{
			swal('Ayuda','En este apartado debe agregar las condiciones de pago. Le recordamos que puede enviar su orden de compra sin factura en caso de no contar con ella y posteriormente cargarla.','info');
		})
		.on('click','#addDoc',function()
		{
			@php
				$options = collect(
					[
						["value"=>"Cotización", "description"=>"Cotización"], 
						["value"=>"Ficha Técnica", "description"=>"Ficha Técnica"], 
						["value"=>"Control de Calidad", "description"=>"Control de Calidad"], 
						["value"=>"Contrato", "description"=>"Contrato"], 
						["value"=>"Factura", "description"=>"Factura"], 
						["value"=>"Ticket", "description"=>"Ticket"], 
						["value"=>"Otro", "description"=>"Otro"]
					]
				);
				$select = view('components.inputs.select',[
					"options" => $options,
					"classEx" => "nameDocument",
					"attributeEx" => "name=nameDocument[] data-validation=required multiple",
				])->render();
				$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
				$newDoc = view('components.documents.upload-files',[					
					"attributeExInput"     => "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"componentsExUp"       => $select,
					"attributeExRealPath"  => "name=\"realPath[]\"",
					"componentsExDown"	   => 
											[
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Seleccione la fecha: ",
													"classEx"    => "data_datepath hidden"
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"   => "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\"",
													"classEx"		=> "data_datepath hidden datepicker datepath",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Seleccione la hora: ",
													"classEx"    => "data_timepath hidden"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\"",
													"classEx"	  => "data_timepath hidden timepath",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Folio fiscal: ",
													"classEx"    => "hidden data_folio"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"folio_fiscal[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\"",
													"classEx"	  => "hidden data_folio folio_fiscal",
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Número de ticket: ",
													"classEx"    => "hidden data_ticket"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"num_ticket[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\"",
													"classEx"	  => "hidden data_ticket num_ticket"
												],
												[
													"kind" 		 => "components.labels.label", 
													"label"		 => "Monto total: ",
													"classEx"    => "hidden data_amount"
												],
												[
													"kind" 		  => "components.inputs.input-text",
													"attributeEx" => "name=\"monto[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\"",
													"classEx"	  => "hidden data_amount monto",
												]
											],
					"classExInput"         => "pathActioner",
					"classExDelete"        => "delete-doc"
				])->render();
			@endphp
			newdoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			$('#documents').append(newdoc);
			$('[name="monto[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			validation();
			
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('#documents').removeClass('hidden');
			$('.timepath').daterangepicker({
				timePicker			: true,
				singleDatePicker	: true,
				timePicker24Hour	: true,
				timePickerIncrement	: 1,
				autoApply			: false,
				locale : 
				{
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
						"identificator"          => ".nameDocument", 
						"placeholder"            => "Seleccione el tipo de documento", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
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
			})
			.on('show.daterangepicker', function (ev, picker)
			{
				picker.container.find(".calendar-table").remove();
			});
		})
		.on('change','.folio_fiscal,.num_ticket,.timepath,.monto,.datepath',function()
		{
			const array_folios	= $('.folio_fiscal').serializeArray();
			const array_ticket	= $('.num_ticket').serializeArray();
			const array_path	= $('.path').serializeArray();

			folio		= $(this).parents('.docs-p').find('.folio_fiscal').val().toUpperCase();
			num_ticket	= $(this).parents('.docs-p').find('.num_ticket').val().toUpperCase();
			timepath	= $(this).parents('.docs-p').find('.timepath').val();
			monto		= $(this).parents('.docs-p').find('.monto').val();
			datepath	= $(this).parents('.docs-p').find('.datepath').val();

			object= $(this);

			if((folio.length!==0&&timepath.length!==0&&datepath.length!==0)||(num_ticket.length!==0&&timepath.length!==0&&monto.length!==0&&datepath.length!==0))
			{	
				$.ajax(
					{
						type		: 'post',
						url			: '{{ route("purchase.validationDocs") }}',
						data 		: {
							'fiscal_value'	: folio,
							'num_ticket'	: num_ticket,
							'timepath'		: timepath,
							'monto'			: monto,
							'datepath'		: datepath,
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
								scnd_fiscal.val('').addClass('error');
								scnd_timepath.val('').addClass('error');
								scnd_datepath.val('').addClass('error');
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
								return;
							}
						}
					}

				});
			});	
		})
		.on('change','.nameDocument',function()
		{
			var type_document = $('option:selected',this).val();
			switch(type_document)
			{
				case 'Factura': 
					$(this).parents('.docs-p').find('.data_folio').show();
					$(this).parents('.docs-p').find('.data_datepath').show();
					$(this).parents('.docs-p').find('.data_timepath').show();
					$(this).parents('.docs-p').find('.data_ticket').hide();
					$(this).parents('.docs-p').find('.data_amount').hide();

					$(this).parents('.docs-p').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.monto').removeClass('error valid').val('');

					break;
				case 'Ticket': 
					$(this).parents('.docs-p').find('.data_folio').hide();
					$(this).parents('.docs-p').find('.data_datepath').show();
					$(this).parents('.docs-p').find('.data_timepath').show();
					$(this).parents('.docs-p').find('.data_ticket').show();
					$(this).parents('.docs-p').find('.data_amount').show();
					
					$(this).parents('.docs-p').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.monto').removeClass('error valid').val('');
					break;
				default : 
					$(this).parents('.docs-p').find('.data_folio').hide();
					$(this).parents('.docs-p').find('.data_datepath').show();
					$(this).parents('.docs-p').find('.data_timepath').hide();
					$(this).parents('.docs-p').find('.data_ticket').hide();
					$(this).parents('.docs-p').find('.data_amount').hide();	
					
					$(this).parents('.docs-p').find('.datepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.timepath').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.folio_fiscal').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.num_ticket').removeClass('error valid').val('');
					$(this).parents('.docs-p').find('.monto').removeClass('error valid').val('');
					break;
			}
					
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
				$(this).addClass('hidden').parents('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("purchase.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('[name="realPath[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('[name="realPath[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parents('.docs-p').find('[name="realPath[]"]').val('');
					}
				}).done(function() {
					$('.disable-button').prop('disabled', false);
					$(this).removeClass('hidden');
				});
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
				url			: '{{ route("purchase.upload") }}',
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
			if($("#documents").html()=="")
			{
				$('.send').removeAttr('disabled');
			}
			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		})
		.on('click','.delete-account',function()
		{
			id = $(this).parents('.tr').find('.providerBank').val();
			if (id != "x") 
			{
				deleteID = $('<input type="hidden" name="deleteAccount[]" value="'+id+'">');
				$('#spanDelete').append(deleteID);
			}
			$(this).parents('.tr').remove();
			if($('#banks-body .tr').length == 0)
			{
				$('#not-found-accounts').show();
			}
			swal('','Cuenta eliminada exitosamente','success');
		})
	});

	function validation()
	{
		$.validate(
		{
			form: '#container-alta',
			modules		: 'security',
			onError   : function($form){
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
				
				@if($request->status == 2 && $request->idRequisition == "")
				if (cant != "" || descr != "" || precio != "" || unit != undefined) 
				{
					swal('', 'Tiene un concepto sin agregar', 'error');
					return false;
				}
				@endif
				subtotal	= 0;
				iva			= 0;
				descuento	= Number($('input[name="descuento"]').val());
				$("#body .tr").each(function(i, v)
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
				else if(total == "0")
				{
					swal('', 'El importe total no puede ser igual a cero', 'error');
					return false;
				}
				if($('.request-validate').length>0)
				{
					partialTypePayment	= $('.partialTypePayment option:selected').val();
					partialPayment		= $('.partialPayment').val();
					partialDate			= $('.partialDate').val();
					docsNew 			= $('.docsNew').length;
					docsExists 			= $('.docsExists').length;

					if ($('#partialForms').is(':visible') && (partialTypePayment == "0" || partialTypePayment == "1" || partialPayment != "" || partialDate != "" || docsNew > 0 || docsExists > 0)) 
					{
						swal('', 'Tiene un parcialidad de pago sin agregar', 'error');
						return false;				
					}

					prov			= $('#form-prov').is(':visible');
					conceptos		= $('#body .tr').length;
					request_total	= Number($('.request_total').val()).toFixed(2);
					partials_total	= Number($('.partials_total').val()).toFixed(2);
					if(prov && conceptos>0)
					{
						if ($('#bodyPartial .tr').length > 0 && Number(partials_total) > Number(request_total))
						{
							swal('', 'El total de la solicitud no coincide con el total de las parcialidades.', 'error');
							return false;
						}
						else if ($('select[name="pay_mode"] option:selected').val() == "Transferencia") 
						{
							if($('#banks-body .tr').length>0)
							{
								// aqui va la validación de la forma de pago if para saber si se guarda o no
								if ($('.checkbox').is(':checked')) 
								{
									swal("Cargando",{
										icon: '{{ asset(getenv('LOADING_IMG')) }}',
										button: false,
									});
									return true;
								}
								else
								{
									swal('', 'Debe seleccionar una cuenta de proveedor', 'error');
									return false;
								}
							}
							else
							{
								swal('', 'Debe ingresar al menos una cuenta de proveedor', 'error');
								return false;
							}
						}
						else
						{
							swal("Cargando",{
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button: false,
							});
							return true;
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
							});
					return true;
				}
			}
		});
	}
	function provider_search(page,provider_data_id)
	{
		text = $("#input-search").val().trim();
		if (text == "")
		{
			$('#not-found').stop().show();
			$('#table-provider').stop().hide();
		}
		else
		{
			$('#not-found').stop().hide();
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("purchase.create.provider") }}',
				data	: {'search':text, "page":page, "provider_data_id":provider_data_id},
				success	: function(data)
				{
					$('.provider').html(data).show();
				},
				error	: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.provider').html('').hide();
				}
			}); 
		}
	}
	function calcule_amount() 
	{
		cant			= $('input[name="quantity"]').val();
		precio			= $('input[name="price"]').val();
		iva				= ("{{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }}")/100;
		iva2			= ("{{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }}")/100;
		ivaCalc			= 0;
		taxAditional 	= 0;
		retention 		= 0;

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
		totalImporte    = ((cant * precio)+ivaCalc)+taxAditional-retention;
		$('input[name="amount"]').val(totalImporte.toFixed(2));
	}
	function total_cal()
	{
		subtotal	= 0;
		iva			= 0;
		amountsAA 	= 0;
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

			subtotal	+= (Number(tempQ)*Number(tempP));
			iva			+= Number($(this).find('.tiva').val());
			amountsAA 	= Number(tempAA);
			amountR 	= Number(tempR);

		});
		total = (subtotal+iva+amountsAA)-amountR;
		$('.request_total').val(total);
		$('.request_total').trigger("change");
		$('input[name="subtotal"]').val(Number(subtotal).toFixed(2));
		$('.subtotal-label-details').html('$ '+Number(subtotal).toFixed(2));
		$('input[name="totaliva"]').val(Number(iva).toFixed(2));
		$('.iva-label-details').html('$ '+Number(iva).toFixed(2));
		$('input[name="total"]').val(Number(total).toFixed(2));
		$('.total-label-details').html('$ '+Number(total).toFixed(2));
		$('.amount_total').val(Number(total).toFixed(2));
		$('input[name="amountAA"]').val(Number(amountsAA).toFixed(2));
		$('.taxes-label-details').html('$ '+Number(amountsAA).toFixed(2));
		$('input[name="amountR"]').val(Number(amountR).toFixed(2));
		$('.retentions-label-details').html('$ '+Number(amountR).toFixed(2));
		totalPartialPayments()
	}
</script>
@include('administracion.compra.form.partialJs')
@endsection 
