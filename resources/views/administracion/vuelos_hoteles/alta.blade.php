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
		$taxes = $retentions = 0;
	@endphp

 	@component("components.forms.form" , ["attributeEx" => "action=\"".route('flights-lodging.store')."\" method=\"POST\" id=\"container-alta\"", "files" => "true"]) 
		{{-- General data --}}
		@component("components.labels.title-divisor")
			@if ($option_id == 285) 
				Nueva solicitud 
			@elseif (($option_id == 286) && (isset($request)) && $request->status == 2) 
				Editar solicitud 
			@elseif (($option_id == 286) && (isset($request)) && $request->status == 3) 
				Datos de la solicitud 
			@endif
		@endcomponent

		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Título:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="title"
						placeholder="Ingrese el título" 
						@if(isset($request)) 
							value="{{$request->flightsLodging->title}}" 
						@endif 
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot

					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Fecha de ingreso de solicitud:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text"  
						name="datetitle" 
						@if(isset($request->flightsLodging->date)) 
							value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$request->flightsLodging->date)->format('d-m-Y') }}" 
						@endif 
						placeholder="Ingrese la fecha" 
						readonly="readonly" 
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot
					@slot('classEx')
						removeselect 
						datepicker
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label")
					Nombre del solicitante:
				@endcomponent

				@php
					$optionsSol =  collect();
					if(isset($request) && isset($request->idRequest))
					{
						$user 	 = App\User::find($request->idRequest);
						$optionsSol = $optionsSol->concat([["value" => $user->id, "description" => $user->name." ".$user->last_name." ".$user->scnd_last_name, "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsSol])
					@slot('attributeEx')
						name="userid" 
						multiple="multiple"  
						id="multiple-users"
						data-validation="required" 
						@if(isset($request) && $request->status != 2)
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						js-users removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre de la empresa:
				@endcomponent

				@php
					$optionsEmp =  collect();
					foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
					{
						if(isset($request) && $request->idEnterprise == $enterprise->id)
						{
							$optionsEmp = $optionsEmp->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"]]);
						}
						else 
						{
							$optionsEmp = $optionsEmp->concat([["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name ]]);
						}
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsEmp])
					@slot('attributeEx')
						name="enterpriseid" 
						multiple="multiple"  
						id="multiple-enterprises"
						data-validation="required" 
						@if(isset($request) && $request->status != 2)
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						js-enterprises removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre de la dirección: 
				@endcomponent

				@php
					$optionsDir =  collect();
					foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
					{
						if(isset($request) && $request->idArea == $area->id)
						{
							$optionsDir = $optionsDir->concat([["value" => $area->id, "description" => $area->name, "selected" => "selected"]]);
						}
						else 
						{
							$optionsDir = $optionsDir->concat([["value" => $area->id, "description" => $area->name ]]);
						}
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsDir])
					@slot('attributeEx')
						multiple="multiple" 
						name="areaid"  
						id="multiple-areas" 
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot
					@slot('classEx')
						js-areas removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre del departamento:
				@endcomponent

				@php
					$optionsDep =  collect();
					foreach(App\Department::where('status', 'ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->orderBy('name','asc')->get() as $department)
					{
						if(isset($request) && $request->idDepartment == $department->id)
						{
							$optionsDep = $optionsDep->concat([["value" => $department->id, "description" => $department->name, "selected" => "selected"]]);
						}
						else 
						{
							$optionsDep = $optionsDep->concat([["value" => $department->id, "description" => $department->name ]]);
						}
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsDep])
					@slot('attributeEx')
						multiple="multiple" 
						name="departmentid"  
						id="multiple-departments" 
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot
					@slot('classEx')
						js-departments removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Clasificación del gasto:
				@endcomponent

				@php
					$optionsClas =  collect();
					if(isset($request) && isset($request->account))
					{
						$account = App\Account::find($request->account);
						$optionsClas = $optionsClas->concat([["value" => $account->idAccAcc, "description" => $account->account . "-". $account->description. " (".$account->content.")", "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsClas])
					@slot('attributeEx')
						multiple="multiple" 
						name="accountid"  
						id="multiple-accounts"
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot
					@slot('classEx')
						js-accounts removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Proyecto:
				@endcomponent

				@php
					$optionsProy =  collect();
					
					if(isset($request) && isset($request->idProject))
					{
						$project = App\Project::find($request->idProject);
						$optionsProy = $optionsProy->concat([["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsProy])
					@slot('attributeEx')
						multiple="multiple" 
						name="project_id"
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif 
						data-validation="required"
					@endslot
					@slot('classEx')
						form-control removeselect
					@endslot
				@endcomponent
			</div>
			<div class = "col-span-2 select_father_wbs @if(isset($request)) @if($request->idProject != '' && $request->requestProject->codeWBS()->exists()) @else hidden @endif @else hidden" @endif>
				@component("components.labels.label")
					Nombre del WBS:
				@endcomponent

				@php
					$optionsWBS =  collect();

					if(isset($request) && $request->code_wbs)
					{
						$wbs = App\CatCodeWBS::find($request->code_wbs);
						$optionsWBS = $optionsWBS->concat([["value" => $wbs->id, "description" => $wbs->code_wbs, "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsWBS])
					@slot('attributeEx')
						name="code_wbs"
						multiple="multiple" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif 
						data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div id="codeEDTContainer" class = "col-span-2 select_father_edt js-edt @if(isset($request)) @if($request->idProject != '' && $request->requestProject->codeWBS()->exists() && $request->wbs()->exists() && $request->wbs->codeEDT()->exists())  @else hidden @endif @else hidden @endif">
				@component("components.labels.label")
					Nombre del EDT:
				@endcomponent	

				@php
					$optionsEdt =  collect();

					if(isset($request) && isset($request->code_edt))
					{
						$edt = App\CatCodeEDT::find($request->code_edt);
						$optionsEdt = $optionsEdt->concat([["value" => $edt->id, "description" => $edt->code.' ('.$edt->description.')', "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsEdt])
					@slot('attributeEx')
						name="code_edt" 
						multiple="multiple" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif 
						data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Viaje solicitado por Pemex ó PTI:
				@endcomponent

				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							id="no_solicited" 
							name="solicited_by" 
							value="0" 
							checked = "true"
							@if (isset($request) && $request->flightsLodging->pemex_pti == 0) 
								checked 
							@endif 
							@if(isset($request) && $request->status != 2) 
								disabled='disabled' 
							@endif
						@endslot
						@slot('classExContainer') 
							solicited 
						@endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							id="si_solicited" 
							name="solicited_by" 
							value="1" 
							@if (isset($request) && $request->flightsLodging->pemex_pti == 1) 
								checked 
							@endif 
							@if(isset($request) && $request->status != 2) 
								disabled='disabled' 
							@endif
						@endslot
						@slot('classExContainer') 
							solicited 
						@endslot
						Sí
					@endcomponent
				</div>
			</div>
		@endcomponent

		@if(isset($request) && $request->status != 2) 
		@else
			@component("components.labels.title-divisor")
				Datos específicos del vuelo
			@endcomponent
			
			@component("components.containers.container-form")
				{{-- Flights data --}}
				<div class="col-span-4 grid grid-cols-4 gap-2 md:gap-4">		
					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Tipo de vuelo:
						@endcomponent

						<div class="flex justify-left p-0 space-x-2">
							@component('components.buttons.button-approval')
								@slot('attributeEx') 
									id="single_flight" 
									name="kind_flight" 
									value="1" 
									checked = "true"
								@endslot
								@slot('classExContainer') 
									kindflight
								@endslot
								Sencillo
							@endcomponent
							@component('components.buttons.button-approval')
								@slot('attributeEx') 
									id="round_flight" 
									name="kind_flight" 
									value="2"
								@endslot
								@slot('classExContainer') 
									kindflight 
								@endslot
								Redondo
							@endcomponent
						</div>
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Nombre del pasajero:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="hidden"
							@endslot
							@slot('classEx')
								flight_details_id
							@endslot
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text"
								name="passenger"
								placeholder="Ingrese el nombre del pasajero"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Cargo: 
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="position"
								placeholder="Ingrese el cargo"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Fecha de nacimiento:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="dateburn" 
								placeholder="Ingrese la fecha" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								removeselect 
								datepicker2 
								dateburn
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Aerolínea (ida) 
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="aeroline" 
								placeholder="Ingrese la aerolínea"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Ruta (ida):
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="route" 
								placeholder="Ingrese la ruta"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Fecha de partida:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="date_departure" 
								placeholder="Ingrese la fecha" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								removeselect 
								datepicker
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Hora de partida:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="time_departure" 
								step="60" 
								value="00:00" 
								placeholder="Seleccione la hora" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								timepath
							@endslot
						@endcomponent
					</div>
				</div>

				{{-- Return data depends of kind --}}
				<div id="roundf" class="hidden col-span-4 grid grid-cols-4 gap-2 md:gap-4">
					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Aerolínea (regreso):
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="aeroline_back"
								placeholder="Ingrese la aerolínea"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Ruta (regreso):
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="route_back" 
								placeholder="Ingrese la ruta"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Fecha de regreso:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text"
								name="date_back" 
								placeholder="Ingrese la fecha" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								removeselect 
								datepicker
							@endslot
						@endcomponent
					</div>

					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Hora de regreso:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="time_back" 
								step="60" 
								value="00:00" 
								placeholder="Seleccione la hora" 
								readonly="readonly"
							@endslot

							@slot('classEx')
								timepath
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Nombre del jefe directo:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text" 
							name="boss" 
							placeholder="Ingrese el nombre del jefe directo"
						@endslot

						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Equipaje documentado:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text" 
							name="checked_baggage" 
							placeholder="Ingrese el equipaje documentado"
						@endslot

						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Fecha de último viaje familiar:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text"
							name="date-lftravel" 
							placeholder="Ingrese la fecha" 
							readonly="readonly"
						@endslot

						@slot('classEx')
							removeselect 
							datepicker2 
							date-lftravel
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						¿Hospedaje? 
					@endcomponent

					<div class="flex justify-left p-0 space-x-2">
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								id="host_no" 
								name="host_check" 
								value="no" 
								checked="true"
								@if(isset($request) && $request->status != 2) 
									disabled='disabled' 
								@endif
							@endslot
							@slot('classExContainer') 
								host_check
							@endslot
							No
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								id="host_si" 
								name="host_check" 
								value="si" 
								@if(isset($request) && $request->status != 2) 
									disabled='disabled' 
								@endif
							@endslot
							@slot('classExContainer') 
								host_check
							@endslot
							Sí
						@endcomponent
					</div>			
				</div>
				{{-- Hosting data--}}
				<div id="hosting_data" class="hidden col-span-4 grid grid-cols-4 gap-2 md:gap-4">
					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Hospedaje:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="host_place" 
								placeholder="Ingrese el hospedaje"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Fecha de ingreso:
						@endcomponent

						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="date-in" 
								placeholder="Ingrese la fecha" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								removeselect datepicker
							@endslot
						@endcomponent
					</div>
					<div class="col-span-4 md:col-span-2">
						@component("components.labels.label")
							Fecha de salida:
						@endcomponent
						
						@component("components.inputs.input-text")
							@slot('attributeEx')
								type="text" 
								name="date-out" 
								placeholder="Ingrese la fecha" 
								readonly="readonly"
							@endslot
							@slot('classEx')
								removeselect 
								datepicker
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Descripción/Motivo del viaje:
					@endcomponent

					@component("components.inputs.text-area")
						@slot('attributeEx')
							type="text" 
							name="description" 
							placeholder="Ingrese la descripción o motivo"
						@endslot
						@slot('classEx')
							description
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Subtotal:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text" 
							name="subtotal" 
							placeholder="Ingrese el subtotal"
						@endslot
						@slot('classEx')
							subtotal-flight removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						IVA:
					@endcomponent

					<div class="flex justify-left p-0 space-x-2">
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								id="type_no" 
								name="iva" 
								value="no" 
								checked = "true"
							@endslot
							@slot('classExContainer') 
								iva
							@endslot
							No
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								id="type_a" 
								name="iva" 
								value="a"
								title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
							@endslot
							@slot('classExContainer') 
								iva
							@endslot
							Tipo A
						@endcomponent
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								id="type_b" 
								name="iva" 
								value="b"
								title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
							@endslot
							@slot('classExContainer') 
								iva
							@endslot
							Tipo B
						@endcomponent
					</div>
				</div>
				<div class="col-span-4 md:col-span-2">
					@component("components.labels.label")
						Total:
					@endcomponent

					@component("components.inputs.input-text")
						@slot('attributeEx')
							type="text" 
							readonly="readonly" 
							name="total" 
							placeholder="Ingrese el total"
						@endslot
						@slot('classEx')
							removeselect total-flight
						@endslot
					@endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'taxes','name' => 'taxes'])  @endcomponent
				</div>
				<div class="md:col-span-4 col-span-2">
					@component('components.templates.inputs.taxes',['type'=>'retention','name' => 'check_retention'])  @endcomponent
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"]) 
						@slot("attributeEx")
							type = "button" 
							name = "add" 
							id	 = "add"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar vuelo</span>
					@endcomponent
				</div>
			@endcomponent
		@endif
		{{-- Flights data table --}}
		<div id="body">
			@if(isset($request))
				@component("components.labels.title-divisor") Datos de pasajeros @endcomponent
				@php
					$passanger_number	= 1;
					$countFlight		= 0;
				@endphp
				@foreach($request->flightsLodging->details as $detail)
					<div class="countFlight">
						@php
							$tablePassenger = 
							[
								["Nombre:", htmlentities($detail->passenger_name)],
								["Descripción/Motivo del viaje:", htmlentities($detail->journey_description)],
								["Cargo:", !empty($detail->job_position) ? htmlentities($detail->job_position)  : 'No hay cargo'],
								["Fecha de nacimiento:", Carbon\Carbon::createFromFormat('Y-m-d', $detail->born_date)->format('d-m-Y')],
								["Jefe directo:", htmlentities($detail->direct_superior)],
								["Último viaje familiar:", Carbon\Carbon::createFromFormat('Y-m-d',$detail->last_family_journey_date)->format('d-m-Y')],
								["Tipo de vuelo:", $detail->typeFlightData()],
								["Equipaje documentado:", !empty($detail->checked_baggage) ? htmlentities($detail->checked_baggage)  : 'No'],
								["Hospedaje:", !empty($detail->hosting) ? htmlentities($detail->hosting)  : 'No'],
								["Fecha de ingreso:", !empty($detail->singin_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$detail->singin_date)->format('d-m-Y')  : '---'],
								["Fecha de salida:", !empty($detail->output_date) ? Carbon\Carbon::createFromFormat('Y-m-d',$detail->output_date)->format('d-m-Y')  : '---']
							];

							$tableFlightI =
							[
								"Aereolínea"      => htmlentities($detail->airline),
								"Ruta"			  => htmlentities($detail->route),
								"Fecha de salida" => Carbon\Carbon::createFromFormat('Y-m-d', $detail->departure_date)->format('d-m-Y'),
								"Hora de salida"  => Carbon\Carbon::createFromFormat('H:i:s', $detail->departure_hour)->format('H:i')
							];
							
							$tableTotal =
							[
								[
									"label" 	=> "Subtotal:", 
									"inputsEx"  => 
									[
										
										[
											"kind" 		=> "components.labels.label",
											"label" 	=> "$".(number_format($detail->subtotal,2)),
											"classEx" 	=> "h-10 py-2"
										]

									]
								],
								[
									"label" 	=> "IVA:", 
									"inputsEx" 	=> 
									[
										
										[
											"kind" 		=> "components.labels.label",
											"label" 	=> "$".(number_format($detail->iva,2)),
											"classEx" 	=> "h-10 py-2"
										]

									]
								],
								[
									"label" 	=> "Impuesto adicional:", 
									"inputsEx" 	=> 
									[
										
										[
											"kind" 		=> "components.labels.label",
											"label" 	=> "$".(number_format($detail->taxes,2)),
											"classEx" 	=> "h-10 py-2"
										]

									]
								],
								[
									"label" 	=> "Retenciones:", 
									"inputsEx" 	=> 
									[
										
										[
											"kind" 		=> "components.labels.label",
											"label" 	=> "$".(number_format($detail->retentions,2)),
											"classEx" 	=> "h-10 py-2"
										]

									]
								],
								[
									"label" 	=> "Total:", 
									"inputsEx" 	=> 
									[
										
										[
											"kind" 		=> "components.labels.label",
											"label" 	=> "$".(number_format($detail->total,2)),
											"classEx" 	=> "h-10 py-2"
										]

									]
								],
							];

							if(isset($detail->airline_back, $detail->route_back, $detail->departure_date_back, $detail->departure_hour_back))
							{
								$tableFlightR =
								[
									"Aereolínea" 	  => $detail->airline_back,
									"Ruta" 			  => $detail->route_back,
									"Fecha de salida" => Carbon\Carbon::createFromFormat('Y-m-d', $detail->departure_date_back)->format('d-m-Y'),
									"Hora de salida"  => Carbon\Carbon::createFromFormat('H:i:s',$detail->departure_hour_back)->format('H:i')
								];
							}
						@endphp
						
						@if(isset($tablePassenger))
							@component("components.templates.outputs.table-detail", ["modelTable" => $tablePassenger, "title" => "Pasajero ".$passanger_number, "classEx" => "mt-6 mb-6"]) @endcomponent
						@endif

						<div class="md:ml-20 col-span-4 grid grid-cols-4 gap-2 md:gap-4 content-center">
							<div class="col-span-4 md:col-span-2">
								@if(isset($tableFlightI))
									@component("components.labels.subtitle")
										Datos de vuelo (IDA)
									@endcomponent
									@component("components.templates.outputs.table-detail-single", ["modelTable" => $tableFlightI]) @endcomponent
								@endif
							</div>
							<div class="col-span-4 md:col-span-2">
								@if(isset($detail->airline_back, $detail->route_back, $detail->departure_date_back, $detail->departure_hour_back))
									@component("components.labels.subtitle")
										Datos de vuelo (Regreso)
									@endcomponent
									@component("components.templates.outputs.table-detail-single", ["modelTable" => $tableFlightR]) @endcomponent
								@endif
							</div>
						</div>
						
						@if(isset($tableTotal))
							<div class="md:ml-20">
								@component("components.labels.subtitle")
									Costo individual
								@endcomponent
							</div>
							@component("components.templates.outputs.form-details", ["modelTable" => $tableTotal, "classEx" => "justify-center"]) @endcomponent
						@endif	
						@if(isset($request) && $request->status == 2)
							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="ttipo[]" 
									value="{{ $detail->type_flight }}"
								@endslot
								@slot('classEx')
									ttipo
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									type="hidden"  
									name="flight_details_id[]" 
									value="{{$detail->id}}"
								@endslot
								@slot('classEx')
									t_flight_details_id
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="tpassenger[]" 
									value="{{ $detail->passenger_name }}"
								@endslot
								@slot('classEx')
									tpassenger
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  type="hidden" 
									name="tpassengerPosition[]" 
									value="{{ $detail->job_position }}"
								@endslot
								@slot('classEx')
									tpassengerPosition
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="tburn[]" 
									value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->born_date)->format('d-m-Y') }}"
								@endslot
								@slot('classEx')
									tburn
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="tairline[]" 
									value="{{ $detail->airline }}"
								@endslot
								@slot('classEx')
									tairline
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="troute[]" 
									value="{{ $detail->route }}"
								@endslot
								@slot('classEx')
									troute
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="tdateFlight[]" 
									value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->departure_date)->format('d-m-Y') }}"
								@endslot
								@slot('classEx')
									tdateFlight
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="thourFlight[]" 
									value="{{ Carbon\Carbon::createFromFormat('H:i:s',$detail->departure_hour)->format('H:i') }}"
								@endslot
								@slot('classEx')
									thourFlight
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"  
									type="hidden" 
									name="tairlineBack[]" 
									value="{{ $detail->airline_back }}"
								@endslot
								@slot('classEx')
									tairlineBack
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="trouteBack[]" 
									value="{{ $detail->route_back }}"
								@endslot
								@slot('classEx')
									trouteBack
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tdateFlightBack[]" 
									@if (isset($detail->departure_date_back))
										value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->departure_date_back)->format('d-m-Y') }}"
									@endif
								@endslot
								@slot('classEx')
									tdateFlightBack
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="thourFlightBack[]" 
									@isset($detail->departure_hour_back)
										value="{{ Carbon\Carbon::createFromFormat('H:i:s',$detail->departure_hour_back)->format('H:i') }}"
									@endisset
								@endslot
								@slot('classEx')
									thourFlightBack
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tbossName[]" 
									value="{{ $detail->direct_superior }}"
								@endslot
								@slot('classEx')
									tbossName
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tbaggage[]" 
									value="{{ $detail->checked_baggage }}"
								@endslot
								@slot('classEx')
									tbaggage
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tlastTravel[]" 
									value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->last_family_journey_date)->format('d-m-Y') }}"
								@endslot
								@slot('classEx')
									tlastTravel
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="thostPlace[]" 
									value="{{ $detail->hosting }}"
								@endslot
								@slot('classEx')
									thostPlace
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tdateIn[]" 
									@isset($detail->singin_date)
										value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->singin_date)->format('d-m-Y') }}"
									@endisset
								@endslot
								@slot('classEx')
									tdateIn
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tdateOut[]" 
									@isset($detail->output_date)
										value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$detail->output_date)->format('d-m-Y') }}"
									@endisset
								@endslot
								@slot('classEx')
									tdateOut
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="tdescription[]" 
									value="{{ $detail->journey_description }}"
								@endslot
								@slot('classEx')
									tdescription
								@endslot
							@endcomponent

							@if($detail->taxesData()->exists())
								@foreach($detail->taxesData as $tax)
									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $tax->id }}" 
											name="t_tax_id{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_tax_id
										@endslot
									@endcomponent

									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $tax->name }}" 
											name="t_name_tax{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_name_tax
										@endslot
									@endcomponent

									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $tax->amount }}" 
											name="t_amount_tax{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_amount_tax
										@endslot
									@endcomponent
								@endforeach
							@endif

							@if($detail->retentionsData()->exists())
								@foreach($detail->retentionsData as $retention)
									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $retention->id }}" 
											name="t_retention_id{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_retention_id
										@endslot
									@endcomponent

									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $retention->name }}" 
											name="t_name_retention{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_name_retention
										@endslot
									@endcomponent

									@component("components.inputs.input-text")
										@slot('attributeEx')
											type="hidden" 
											value="{{ $retention->amount }}" 
											name="t_amount_retention{{ $countFlight }}[]"
										@endslot

										@slot('classEx')
											num_amount_retention
										@endslot
									@endcomponent
								@endforeach
							@endif

							@php
								$countFlight++;
							@endphp

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"
									type="hidden" 
									name="tsubtotal[]" 
									value="{{ $detail->subtotal }}"
								@endslot

								@slot('classEx')
									tsubtotal
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"
									type="hidden" 
									name="tiva[]" 
									value="{{ $detail->iva }}"
								@endslot

								@slot('classEx')
									tiva
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true" 
									type="hidden" 
									name="ttaxes[]" 
									value="{{ $detail->taxes }}"
								@endslot

								@slot('classEx')
									ttaxes
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"
									type="hidden" 
									name="tretentions[]" 
									value="{{ $detail->retentions }}"
								@endslot

								@slot('classEx')
									tretentions
								@endslot
							@endcomponent

							@component("components.inputs.input-text")
								@slot('attributeEx')
									readonly="true"
									type="hidden" 
									name="ttotal[]" 
									value="{{ $detail->total }}"
								@endslot

								@slot('classEx')
									ttotal
								@endslot
							@endcomponent

							<div class="text-center my-4">
								@component("components.buttons.button", ["variant" => "success"])
									@slot('attributeEx')
										type="button"
									@endslot

									@slot('classEx')
										edit-item
									@endslot
									<span class="icon-pencil"></span>
									Editar
								@endcomponent

								@component("components.buttons.button", ["variant" => "red"])
									@slot('attributeEx')
										type="button"
									@endslot

									@slot('classEx')
										delete-item
									@endslot
									<span class="icon-x"></span>
									Eliminar
								@endcomponent
							</div>
						@endif

						@php
							$passanger_number ++;
						@endphp
					</div>
				@endforeach
			@endif
		</div>

		@component("components.labels.title-divisor") Costos totales @endcomponent
		<div class="totales">
			@php
				$tableTotals =
				[
					[
						"label"    => "Subtotal:", 
						"inputsEx" => 
						[
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"subtotal_flight\" placeholder=\"$0.00\" value=\"".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->subtotal,2) : $request->flightsLodging->subtotal : "")."\""
							],
							[
								"kind" => "components.labels.label",
								"label" => "$".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->subtotal,2) : $request->flightsLodging->subtotal : ''),
								"classEx" => "h-10 py-2 subtotal_flight"
							]

						]
					],
					[
						"label"    => "IVA:", 
						"inputsEx" => 
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"iva_flight\" placeholder=\"$0.00\" value=\"".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->iva,2) : $request->flightsLodging->iva : '')."\""
							],
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->iva,2) : $request->flightsLodging->iva : ''),
								"classEx" 	=> "h-10 py-2 iva_flight"
							]

						]
					],
					[
						"label"    => "Impuesto adicional:", 
						"inputsEx" => 
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"taxes_flight\" placeholder=\"$0.00\" value=\"".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->taxes,2) : $request->flightsLodging->taxes : '')."\""
							],
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->taxes,2) : $request->flightsLodging->taxes : ''),
								"classEx" 	=> "h-10 py-2 taxes_flight"
							]

						]
					],
					[
						"label"    => "Retenciones:", 
						"inputsEx" => 
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"retentions_flight\" placeholder=\"$0.00\" value=\"".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->retentions,2) : $request->flightsLodging->retentions : '')."\""
							],
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->retentions,2) : $request->flightsLodging->retentions : ''),
								"classEx" 	=> "h-10 py-2 retentions_flight"
							]

						]
					],
					[
						"label"    => "Total:", 
						"inputsEx" => 
						[
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" name=\"total_flight\" placeholder=\"$0.00\" value=\"".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->total,2) : $request->flightsLodging->total : '')."\""
							],
							[
								"kind"		=> "components.labels.label",
								"label" 	=> "$".(isset($request) ? $request->status != 2 ? number_format($request->flightsLodging->total,2) : $request->flightsLodging->total : ''),
								"classEx" 	=> "h-10 py-2 total_flight"
							]

						]
					],
				];
			@endphp

			@component("components.templates.outputs.form-details", ["modelTable" => $tableTotals, "title" => "", "classEx" => "justify-center"]) @endcomponent
		</div>
		{{-- Payment method --}}
		@component("components.labels.title-divisor")
			Condiciones de pago
		@endcomponent

		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Referencia:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="reference" 
						placeholder="Ingrese la referencia"
						@if (isset($request)) 
							value="{{ $request->flightsLodging->reference }}"
						@endif 
						data-validation="required" 
						@if(isset($request) && $request->status != 2) 
							disabled='disabled' 
						@endif
					@endslot

					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Forma de pago:
				@endcomponent

				@php
					$optionsPay =  collect();

					foreach(App\PaymentMethod::all() as $payment_method)
					{
						if(isset($request)) 
						{
							if($request->flightsLodging->payment_method == $payment_method->idpaymentMethod)
							{
								$optionsPay = $optionsPay->concat([["value" => $payment_method->idpaymentMethod, "description" => $payment_method->method, "selected" => "selected"]]);
							}
							else 
							{
								$optionsPay = $optionsPay->concat([["value" => $payment_method->idpaymentMethod, "description" => $payment_method->method]]);
							}
						}
						else 
						{
							$optionsPay = $optionsPay->concat([["value" => $payment_method->idpaymentMethod, "description" => $payment_method->method]]);
						}
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsPay])
					@slot('attributeEx')
						name="pay_mode" 
						multiple="multiple"
						data-validation="required" 
						@if(isset($request) && $request->status != 2)
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						js-form-pay removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Moneda:
				@endcomponent

				@php
					$optionsCurrency =  collect();

					foreach(["MXN", "USD" ,"EUR", "Otro"] as $item)
					{
						$optionsCurrency = $optionsCurrency->concat(
						[
							[
								"value" => $item,
								"description" => $item,
								"selected" => (isset($request) && $request->flightsLodging->currency == $item ? "selected" : "")
							]
						]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsCurrency])
					@slot('attributeEx')
						name="type_currency" 
						multiple="multiple" 
						data-validation="required"
						@if(isset($request) && $request->status != 2)
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Estado de factura:
				@endcomponent

				@php
					$optionsFact =  collect();

					foreach(["Pendiente", "Entregado" ,"No aplica"] as $item)
					{
						$optionsFact = $optionsFact->concat(
						[
							[
								"value" => $item,
								"description" => $item,
								"selected" => (isset($request) && $request->flightsLodging->bill_status == $item ? "selected" : "")
							]
					]);
					}
				@endphp

				@component("components.inputs.select",["options" =>	$optionsFact])
					@slot('attributeEx')
						name="status_bill" 
						multiple="multiple"
						data-validation="required"
						@if(isset($request) && $request->status != 2)
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						js-ef removeselect
					@endslot
				@endcomponent
			</div>
		@endcomponent
		{{-- Documents relations --}}
		<div class="col-span-2 md:col-span-4 table-striped">
			@if(isset($request) && $request->flightsLodging->documents()->exists())
				@component("components.labels.title-divisor")
					Documentos de la solicitud
				@endcomponent

				@php
					$documentsBody = [];
					$modelHead = ["Tipo de documento", "Archivo", "Modificado por", "Fecha"];
					foreach ($request->flightsLodging->documents as $document)
					{
						$dates	= strtotime($document->date);
						$date	= date('d-m-Y H:i', $dates);

						$row =
						[
							"classEx" => "tr",
							[
								"content" => 
								[
									["kind" => "components.labels.label", "label" => $document->name ]
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "secondary",
										"buttonElement"	=> "a",
										"attributeEx"	=> "target=\"_blank\" title=\"".$document->path."\"".' '."href=\"".asset('docs/flights_lodging/'.$document->path)."\"",
										"label"			=> "Archivo"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"flight_document_id[]\" value=\"".$document->id."\""
									]
								]
							],
							[
								"content" => 
								[
									["kind" => "components.labels.label", "label" => $document->userData->fullName() ]
								]
							],
							[
								"content" => 
								[
									["kind" => "components.labels.label", "label" => $date]
								]
							]
						];
						$documentsBody[] = $row;
					}
				@endphp

				<div class="table-responsive">
					@AlwaysVisibleTable(["modelHead" => $modelHead, "modelBody" => $documentsBody,"variant" => "default", "attributeExBody" => "id=\"bodyT\"", "attributeEx" => "id=\"table-documents\""]) @endAlwaysVisibleTable
				</div>
			@endif
		</div>
		{{-- documents upload --}}
		@component("components.labels.title-divisor")
			Cargar documentos
		@endcomponent

		@component("components.containers.container-form")
			<div id=documents class="hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>
			<div class="col-span-4 md:col-span-2 grid justify-items-center md:justify-items-start">
				<div class="md:block grid">
					@component("components.buttons.button", ["variant" => "warning", "classEx" => "add2"]) 
						@slot("attributeEx")
							type="button"
							name="addDoc" 
							id="addDoc" 
							@if(isset($request) && $request->status == 1) 
								disabled 
							@endif
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar documento</span>
					@endcomponent

					@if (isset($request) && $request->status != 2)
						@component("components.buttons.button", ['variant' => 'success'])
							@slot('attributeEx')
								type="submit" 
								id="loadNewDoc" 
								name="loadNewDoc" 
								formaction="{{ route('flights-lodging.follow.loadNewDocument',['id'=>$request->folio])}}" 
								@if(isset($request) && $request->status == 1) 
									disabled 
								@endif
							@endslot

							@slot('classEx')
								loadNewDoc
							@endslot

							CARGAR
						@endcomponent				
					@endif
				</div>
			</div>
		@endcomponent

		@component("components.inputs.input-text")
			@slot('attributeEx')
				type="hidden"
				name="deleted_rows"
			@endslot

			@slot('classEx')
				deleted_rows
			@endslot
		@endcomponent

		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@if(isset($request))
				@if($request->status == 2 && !isset($new_request))
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx') 
							type="submit" 
							name="enviarSR"
							formaction="{{ route('flights-lodging.sendToReview' , $request->folio)}}"
						@endslot
						@slot('classEx') 
							enviarSR w-48 md:w-auto
						@endslot
						ENVIAR SOLICITUD
					@endcomponent
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx') 
							type="submit" 
							name="saveU" 
							id="saveU" 
							formaction="{{ route('flights-lodging.update.unsend' , $request->folio)}}"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto saveU
						@endslot
						GUARDAR SIN ENVIAR
					@endcomponent
				@elseif (isset($new_request) && $new_request)
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx') 
							type="submit" 
							name="enviar"
						@endslot
						@slot('classEx') 
							enviar w-48 md:w-auto
						@endslot
						ENVIAR SOLICITUD
					@endcomponent
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx') 
							type="submit" 
							name="saveLU" 
							id="saveLU" 
							formaction="{{ route('flights-lodging.unsend') }}"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto saveLU
						@endslot
						GUARDAR SIN ENVIAR
					@endcomponent
				@endif
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
					@slot('attributeEx')
						@if(isset($option_id)) 
							href="{{ url(getUrlRedirect($option_id)) }}" 
						@else 
							href="{{ url(getUrlRedirect($child_id)) }}"
						@endif 
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			@else
				@component("components.buttons.button",["variant" => "primary"])
					@slot('attributeEx') 
						type="submit" 
						name="enviar"
					@endslot
					@slot('classEx') 
						enviar w-48 md:w-auto
					@endslot
					ENVIAR SOLICITUD
				@endcomponent
				@component("components.buttons.button",["variant" => "secondary"])
					@slot('attributeEx') 
						type="submit" 
						name="saveLU" 
						id="saveLU" 
						formaction="{{ route('flights-lodging.unsend') }}"
					@endslot
					@slot('classEx') 
						w-48 md:w-auto saveLU
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
				@component('components.buttons.button',["variant" => "reset"])
					@slot('attributeEx')
						type = "reset"
						name = "borrar"
					@endslot
					@slot('classEx')
						borrar btn-delete-form
					@endslot
						BORRAR CAMPOS
				@endcomponent
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">	
		$(document).ready(function()
		{
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '[name="project_id"]', 'model': 14});
			generalSelect({'selector': '.js-accounts', 'depends':'.js-enterprises', 'model': 10});
			generalSelect({'selector': '[name="code_edt"]', 'depends':'[name="code_wbs"]', 'model': 15});
			@isset($request)
				generalSelect({'selector': '[name="code_wbs"]','depends':'[name="project_id"]', 'model':54, 'status': {{ $request->status}} });				
			@else
				generalSelect({'selector': '[name="code_wbs"]','depends':'[name="project_id"]', 'model':54, 'status': null });				
			@endisset

			validation();
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => 'Seleccione la empresa', 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-form-pay", 
						"placeholder"            => 'Seleccione la forma de pago', 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name="type_currency"]', 
						"placeholder"            => "Seleccione el tipo de moneda", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '.js-ef', 
						"placeholder"            => "Seleccione el estado", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-areas", 
						"placeholder"            => 'Seleccione la dirección', 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-departments", 
						"placeholder"            => 'Seleccione el departamento', 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			@component('components.scripts.taxes',['type'=>'taxes','name' => 'taxes','function'=>'sum_total'])  @endcomponent
			@component('components.scripts.taxes',['type'=>'retention','name' => 'check_retention','function'=>'sum_total'])  @endcomponent
			$('.taxes, .total-flight, .subtotal-flight,.taxesAmount,.check_retentionAmount').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2});
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
				$(".datepicker").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
				$(".datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
			});
			$(document).on('change','[name="project_id"]',function()
			{
				$('[name="code_edt"]').empty();
				$('[name="code_wbs"]').empty();
				idproject = $('[name="project_id"] option:selected').val();
					
				$.ajax(
				{
					type	: 'get',
					url		: '{{ route("requisition.get-wbs") }}',
					data	: {'idproject':idproject},
					success : function(data)
					{
						if(data.length > 0)
						{
							$('.select_father_wbs').show();
							@isset($request)
								generalSelect({'selector': '[name="code_wbs"]','depends':'[name="project_id"]', 'model':54, 'status': {{ $request->status}} });				
							@else
								generalSelect({'selector': '[name="code_wbs"]','depends':'[name="project_id"]', 'model':54, 'status': null });				
							@endisset
						}
						else
						{
							$('.select_father_edt').hide();
							$('.select_father_wbs').hide();
						}
					},
					error : function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.select_father_wbs').hide();
						$('.select_father_edt').hide();
					}
				});

				$('[name="work_project"]').val(idproject).trigger('change');

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
						sum_total();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','[name="code_wbs"]',function()
			{
				$('[name="code_edt"]').empty();
				code_wbs = $(this).val();
				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ route("requisition.get-edt") }}',
					data 	: {
						'code_wbs':code_wbs,
					},
					success : function(data)
					{
						if (data.length > 0) 
						{
							$('.select_father_edt').show();
							generalSelect({'selector': '[name="code_edt"]', 'depends':'[name="code_wbs"]', 'model': 15});	
						}
						else
						{
							$('[name="code_edt"] option').empty();
							$('.select_father_edt').hide();
						}
					},
					error : function(data)
					{
						$('[name="code_edt"] option').empty();
						$('.select_father_edt').hide();
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
			})
			.on('click','input[name="kind_flight"]',function()
			{
				if($(this).val() == '2')
				{
					$('#roundf').removeClass('hidden');
				}
				else
				{
					
					$('#roundf').addClass('hidden');
				}
			})
			.on('click','input[name="host_check"]',function()
			{
				if($(this).val() == 'si')
				{
					$('#hosting_data').removeClass('hidden').addClass('grid');
				}
				else
				{
					$('input[name="host_place"]').val('');
					$('input[name="date-in"]').val('');
					$('input[name="date-out"]').val('');
					$('#hosting_data').removeClass('grid').addClass('hidden');
				}
			})
			.on('change','.timepath',function()
			{
				$(this).daterangepicker({
					timePicker 			: true,
					singleDatePicker	: true,
					timePicker24Hour 	: true,
					locale : {
						format : 'HH:mm',
						"applyLabel": "Seleccionar",
						"cancelLabel": "Cancelar",
					}
				}).on('show.daterangepicker', function (ev, picker){
					picker.container.find(".calendar-table").remove();
				});
			})
			.on('click','#add',function()
			{
				countFlight			= $('.countFlight').length;
				kindf				= $('input[name="kind_flight"]:checked').removeClass('error').val().trim();
				namePassenger		= $('input[name="passenger"]').removeClass('error').val().trim();
				passengerPosition	= $('input[name="position"]').removeClass('error').val().trim();
				burn				= $('input[name="dateburn"]').removeClass('error').val().trim();
				airline				= $('input[name="aeroline"]').removeClass('error').val().trim();
				route				= $('input[name="route"]').removeClass('error').val().trim();
				departureDate		= $('input[name="date_departure"]').removeClass('error').val().trim();
				departureHour		= $('input[name="time_departure"]').removeClass('error').val().trim();
				flight_details_id 	= $('.flight_details_id').val();

				if(flight_details_id == "")
				{
					flight_details_id = "x";
				}

				switch($('input[name="kind_flight"]:checked').val())
				{
					case '1':
						airlineBack		= "";
						routeReturn		= "";
						returnDate		= "";
						returnHour		= "";
						break;
					case '2':
						airlineBack		= $('input[name="aeroline_back"]').removeClass('error').val().trim();
						routeReturn		= $('input[name="route_back"]').removeClass('error').val().trim();
						returnDate		= $('input[name="date_back"]').removeClass('error').val().trim();
						returnHour		= $('input[name="time_back"]').removeClass('error').val().trim();

						startDateFormat = moment(departureDate+' '+departureHour,'DD-MM-YYYY HH:mm:ss');
						endDateFormat = moment(returnDate+' '+returnHour,'DD-MM-YYYY HH:mm:ss');

						startDate = moment(startDateFormat).format('YYYY-MM-DD HH:mm:ss');
						endDate = moment(endDateFormat).format('YYYY-MM-DD HH:mm:ss');

						diff = moment(endDate).diff(startDate, 'minutes');
						if(diff <= 0)
						{
							swal('','La fecha de regreso tiene que ser posterior a la fecha de ida','error');
							$('input[name="date_back"]').addClass('error').val('');
							return false;
						}

						break;
				}

				bossName		= $('input[name="boss"]').removeClass('error').val().trim();
				baggage			= $('input[name="checked_baggage"]').removeClass('error').val().trim();
				lastTravel		= $('input[name="date-lftravel"]').removeClass('error').val().trim();
				hostPlace		= $('input[name="host_place"]').removeClass('error').val().trim();
				dateIn			= $('input[name="date-in"]').removeClass('error').val().trim();
				dateOut			= $('input[name="date-out"]').removeClass('error').val().trim();
				subtotal		= Number($('input[name="subtotal"]').removeClass('error').val().trim());
				total			= Number($('input[name="total"]').removeClass('error').val().trim());
				description		= $('textarea[name="description"]').removeClass('error').val().trim();
				flag			= false;

				dateInFormat	= moment(dateIn,'DD-MM-YYYY');
				dateOutFormat	= moment(dateOut,'DD-MM-YYYY');

				hostStartDate	= moment(dateInFormat).format('YYYY-MM-DD');
				hostEndDate		= moment(dateOutFormat).format('YYYY-MM-DD');
				hostDiff		= moment(hostEndDate).diff(hostStartDate, 'days');
				if(hostDiff <= 0)
				{
					swal('','La fecha de salida tiene que ser posterior a la fecha de ingreso','error');
					$('[name="date-out"]').addClass('error').val('');
					return false;
				}

				if(kindf == "1")
				{
					if(namePassenger == "" || passengerPosition == "" || burn =="" || airline == ""  || route == "" || departureDate == "" || departureHour == "" || baggage == "" || lastTravel == "" || total == "" || subtotal == "" || description == "")
					{
						if(namePassenger=="")
						{
							$('input[name="passenger"]').addClass('error');
						}
						if(passengerPosition=="")
						{
							$('input[name="position"]').addClass('error');
						}
						if(burn=="")
						{
							$('input[name="dateburn"]').addClass('error');
						}
						if(airline=="")
						{
							$('input[name="aeroline"]').addClass('error');
						}
						if(route=="")
						{
							$('input[name="route"]').addClass('error');
						}
						if(departureDate=="")
						{
							$('input[name="date_departure"]').addClass('error');
						}
						if(departureHour=="")
						{
							$('input[name="time_departure"]').addClass('error');
						}
						if(baggage=="")
						{
							$('input[name="checked_baggage"]').addClass('error');
						}
						if(lastTravel=="")
						{
							$('input[name="date-lftravel"]').addClass('error');
						}
						if(total=="")
						{
							$('input[name="total"]').addClass('error');
						}
						if(subtotal=="")
						{
							$('input[name="subtotal"]').addClass('error');
						}
						if(description=="")
						{
							$('textarea[name="description"]').addClass('error');
						}
						swal('', 'Por favor llene todos los campos.', 'error');
					}
					else
					{
						flag = true;
					}
				}
				else if(kindf == "2")
				{
					if(namePassenger == "" || passengerPosition == "" || burn =="" || airline == ""  || route == "" || departureDate == "" || departureHour == "" || baggage == "" || lastTravel == "" || total == "" || subtotal == "" || description == "" || airlineBack == "" || routeReturn == "" || returnDate == "" || returnHour == "")
					{
						if(namePassenger=="")
						{
							$('input[name="passenger"]').addClass('error');
						}
						if(passengerPosition=="")
						{
							$('input[name="position"]').addClass('error');
						}
						if(burn=="")
						{
							$('input[name="dateburn"]').addClass('error');
						}
						if(airline=="")
						{
							$('input[name="aeroline"]').addClass('error');
						}
						if(route=="")
						{
							$('input[name="route"]').addClass('error');
						}
						if(departureDate=="")
						{
							$('input[name="date_departure"]').addClass('error');
						}
						if(departureHour=="")
						{
							$('input[name="time_departure"]').addClass('error');
						}
						if(baggage=="")
						{
							$('input[name="checked_baggage"]').addClass('error');
						}
						if(lastTravel=="")
						{
							$('input[name="date-lftravel"]').addClass('error');
						}
						if(total=="")
						{
							$('input[name="total"]').addClass('error');
						}
						if(subtotal=="")
						{
							$('input[name="subtotal"]').addClass('error');
						}
						if(description=="")
						{
							$('textarea[name="description"]').addClass('error');
						}
						if(airlineBack == "")
						{
							$('input[name="aeroline_back"]').addClass('error');
						}
						if(routeReturn == "")
						{
							$('input[name="route_back"]').addClass('error');
						}
						if(returnDate == "")
						{
							$('input[name="date_back"]').addClass('error');
						}
						if(returnHour == "")
						{
							$('input[name="time_back"]').addClass('error');
						}
						swal('', 'Por favor llene todos los campos.', 'error');
						
					}
					else
					{
						flag = true;
					}
				}
				if(flag)
				{

					typeTax			= $('input[name="iva"]:checked').val();
					iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
					iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
					ivaCalc			= 0;
					sum_taxes		= 0;
					sum_retentions	= 0;

					switch(typeTax)
					{
						case 'no':
							ivaCalc = 0;
							break;
						case 'a':
							ivaCalc = subtotal*iva;
							break;
						case 'b':
							ivaCalc = subtotal*iva2;
							break;
					}

					name_taxes = $('<div></div>');
					if ($('[name="taxes"]:checked').val() == 'si') 
					{
						$('.taxesName').each(function(i,v)
						{
							name_tax = $(this).val().trim();
							if (name_tax != "") 
							{
								@php
									$inputTaxId = view('components.inputs.input-text',[
										"classEx" => "num_tax_id",
										"attributeEx" => "type=hidden"
									])->render();
								
									$inputTaxName = view('components.inputs.input-text',[
										"classEx" => "num_name_tax",
										"attributeEx" => "type=hidden"
									])->render();
								@endphp

								inputTaxId = '{!!preg_replace("/(\r)*(\n)*/", "", $inputTaxId)!!}';
								inputTaxName = '{!!preg_replace("/(\r)*(\n)*/", "", $inputTaxName)!!}';

								row_inputTaxId = $(inputTaxId);
								row_inputTaxId.attr('name', 't_tax_id'+countFlight+'[]');

								row_inputTaxName = $(inputTaxName);
								row_inputTaxName.attr('name', 't_name_tax'+countFlight+'[]');
								row_inputTaxName.val(name_tax);

								name_taxes.append(row_inputTaxId, row_inputTaxName);
							}
						});
					}

					amount_taxes = $('<div></div>');
					if ($('[name="taxes"]:checked').val() == 'si') 
					{
						$('.taxesAmount').each(function(i,v)
						{
							amount_tax = $(this).val().trim();
							if (amount_tax != "") 
							{
								@php
									$inputTaxAmount = view('components.inputs.input-text',[
										"classEx" => "num_amount_tax",
										"attributeEx" => "type=hidden"
									])->render();
								@endphp

								inputTaxAmount = '{!!preg_replace("/(\r)*(\n)*/", "", $inputTaxAmount)!!}';
								row_inputTaxAmount = $(inputTaxAmount);
								row_inputTaxAmount.attr('name', 't_amount_tax'+countFlight+'[]');
								row_inputTaxAmount.val(amount_tax);

								amount_taxes.append(row_inputTaxAmount);
							}
						});
					}


					name_retentions =  $('<div></div>');
					if ($('[name="check_retention"]:checked').val() == 'si') 
					{
						$('.check_retentionName').each(function(i,v)
						{
							name_retention = $(this).val().trim();
							if (name_retention != "") 
							{
								@php

									$inputRetentionsId = view('components.inputs.input-text',[
										"classEx" => "num_retention_id",
										"attributeEx" => "type=hidden"
									])->render(); 

									$inputNameRetentions = view('components.inputs.input-text',[
										"classEx" => "num_nameRetention",
										"attributeEx" => "type=hidden"
									])->render();
									
								@endphp
								
								$inputRetentionsId = '{!!preg_replace("/(\r)*(\n)*/", "", $inputRetentionsId)!!}';
								row_RetentionId = $($inputRetentionsId);
								row_RetentionId.attr('name', 't_retention_id'+countFlight+'[]');
								row_RetentionId.val('x');
								name_taxes.append(row_RetentionId);

								inputNameRetentions = '{!!preg_replace("/(\r)*(\n)*/", "", $inputNameRetentions)!!}';
								row_nameRetentions = $(inputNameRetentions);
								row_nameRetentions.attr('name', 't_name_retention'+countFlight+'[]');
								row_nameRetentions.val(name_retention);
								name_retentions.append(row_nameRetentions);
							}
						});
					}

					amount_retentions = $('<div></div>');
					if ($('[name="check_retention"]:checked').val() == 'si') 
					{
						$('.check_retentionAmount').each(function(i,v)
						{
							amount_retention = $(this).val().trim();
							if (amount_retention != "") 
							{
								@php
									$inputAmountRetention = view('components.inputs.input-text',[
										"classEx" => "num_amount_retention",
										"attributeEx" => "type=hidden"
									])->render();
								@endphp
								inputAmountRetention = '{!!preg_replace("/(\r)*(\n)*/", "", $inputAmountRetention)!!}';
								row_amountsRetention = $(inputAmountRetention);
								row_amountsRetention.attr('name', 't_amount_retention'+countFlight+'[]')
								row_amountsRetention.val(amount_retention)
								amount_retentions.append(row_amountsRetention);
							}
						});
					}


					if ($('[name="taxes"]:checked').val() == 'si') 
					{
						$('.taxesAmount').each(function(i,v)
						{
							if ($(this).val().trim() != "") 
							{
								sum_taxes += Number($(this).val());
							}
						});
					}

					if ($('[name="check_retention"]:checked').val() == 'si') 
					{
						$('.check_retentionAmount').each(function(i,v)
						{
							if ($(this).val().trim() != "") 
							{
								sum_retentions += Number($(this).val());
							}
						});
					}

					total = ((subtotal + ivaCalc + sum_taxes) - sum_retentions);

					if (kindf == 1) 
					{
						kind = 'sencillo';
					} 
					else 
					{
						kind = 'redondo';
					}

					@php
						$countP = 
						$component  =   "";

						$input = view('components.templates.outputs.table-detail',[
							"variant"    => true,
							"classEx" 	 => "mt-6 mb-6",
							"modelTable" =>
							[
								["Nombre",            		[["kind"    =>  "components.labels.label",  "classEx"   =>  "class_namePassenger"]]],
								["Cargo",             		[["kind"    =>  "components.labels.label",  "classEx"   =>  "class_passengerPosition"]]],
								["Fecha de nacimiento",     [["kind"    =>  "components.labels.label",  "classEx"   =>  "class_burn"]]],
								["Jefe directo",  			[["kind"    =>  "components.labels.label",  "classEx"   =>  "class_NA"]]],
								["Último viaje familiar",   [["kind"    =>  "components.labels.label",  "classEx"   =>  "class_lastTravel"]]],
								["Tipo de vuelo",           [["kind"    =>  "components.labels.label",  "classEx"   =>  "class_kind"]]],
								["Hospedaje",            	[["kind"    =>  "components.labels.label",  "classEx"   =>  "class_hostPlace"]]],
								["Fecha de ingreso",        [["kind"    =>  "components.labels.label",  "classEx"   =>  "class_dateIn"]]],
								["Fecha de salida",         [["kind"    =>  "components.labels.label",  "classEx"   =>  "class_dateOut"]]],
							],
							"title"     =>
							[
								["kind" => "components.labels.label",  "classEx" => "class_pasajero text-white titleClass text-center"]
							]
						]);
						$component .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", "<div class=\"content-centermy-4 mx-6 col-span-1\">".
						$input->render()."</div>"));
					@endphp

					component = '{!!preg_replace("/(\r)*(\n)*/", "", $component)!!}';
					table_detail = $(component);

					table_detail.find(".class_namePassenger").text(namePassenger);
					table_detail.find(".class_passengerPosition").text(passengerPosition);
					table_detail.find(".class_burn").text(burn);
					table_detail.find(".class_NA").text("N/A");
					table_detail.find(".class_lastTravel").text(lastTravel);
					table_detail.find(".class_kind").text(kind);
					table_detail.find(".class_hostPlace").text(hostPlace != "" ? hostPlace : "No");
					table_detail.find(".class_dateIn").text(dateIn != "" ? dateIn : "---");
					table_detail.find(".class_dateOut").text(dateOut != "" ? dateOut : "---");
					table_detail.find(".class_pasajero").text("Pasajero "+(countFlight + 1)).removeClass("ml-14");

					@php
						$componentTVIR = "";
						$modelTableVI = [];
						$modelTableVR = [];

						$modelTableVI['Aereolínea'] 		= view('components.labels.label', ['classEx' => "class_airline"])->render();
						$modelTableVI['Ruta'] 				= view('components.labels.label', ['classEx' => "class_route"])->render();
						$modelTableVI['Fecha de salida'] 	= view('components.labels.label', ['classEx' => "class_departure_date"])->render();
						$modelTableVI['Hora de salida'] 	= view('components.labels.label', ['classEx' => "class_departure_hou"])->render();
						$componentVI  = view('components.templates.outputs.table-detail-single', ['modelTable' => $modelTableVI])->render();
						$subtitleVI   = view('components.labels.subtitle', ["label" => 'Datos de vuelo (IDA)'])->render();

						$modelTableVR['Aereolínea'] 		= view('components.labels.label', ['classEx' => "class_airlineBack"])->render();
						$modelTableVR['Ruta'] 				= view('components.labels.label', ['classEx' => "class_routeReturn"])->render();
						$modelTableVR['Fecha de salida'] 	= view('components.labels.label', ['classEx' => "class_returnDate"])->render();
						$modelTableVR['Hora de salida'] 	= view('components.labels.label', ['classEx' => "class_returnHour"])->render();
						$componentVR  = view('components.templates.outputs.table-detail-single', ['modelTable' => $modelTableVR])->render();
						$subtitleVR   = view('components.labels.subtitle', ["label" => 'Datos de vuelo (VUELTA)'])->render();

						$componentTVIR .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", "<div class=\"md:ml-20 col-span-4 grid grid-cols-4 gap-2 md:gap-4 content-center\">".
							"<div class=\"col-span-4 md:col-span-2\">".$subtitleVI.$componentVI."</div>".
							"<div class=\"col-span-4 md:col-span-2\">".$subtitleVR.$componentVR."</div>".
						"</div>"));
					@endphp

					componentTVIR = '{!!preg_replace("/(\r)*(\n)*/", "", $componentTVIR)!!}';
					table_detailFI = $(componentTVIR);

					table_detailFI.find(".class_airline").text(airline);
					table_detailFI.find(".class_route").text(route);
					table_detailFI.find(".class_departure_date").text(departureDate);
					table_detailFI.find(".class_departure_hou").text(departureHour);
					table_detailFI.find(".class_airlineBack").text(airlineBack != "" ? airlineBack : "---");
					table_detailFI.find(".class_routeReturn").text(routeReturn != "" ? routeReturn : "---");
					table_detailFI.find(".class_returnDate").text(returnDate != "" ? returnDate : "---");
					table_detailFI.find(".class_returnHour").text(returnHour != "" ? returnHour : "---");

					@php
						$componentTCI = "";
						$modelTableCI = view('components.templates.outputs.form-details',[
							"classEx" => "justify-center",
							"modelTable" =>
							[
								[
									"label" => "Subtotal:", 
									"inputsEx" => [["kind" => "components.labels.label", "classEx" => "h-10 py-2 class_subtotal"]]
								],
								[
									"label" => "IVA:", 
									"inputsEx" => [["kind" => "components.labels.label", "classEx" => "h-10 py-2 class_ivaCalc"]]
								],
								[
									"label" => "Impuesto adicional:", 
									"inputsEx" => [["kind" => "components.labels.label", "classEx" => "h-10 py-2 class_sum_taxes"]]
								],
								[
									"label" => "Retenciones:", 
									"inputsEx" => [["kind" => "components.labels.label", "classEx" => "h-10 py-2 class_sum_retentions"]]
								],
								[
									"label" => "Total:", 
									"inputsEx" => [["kind" => "components.labels.label", "classEx" => "h-10 py-2 class_total"]]
								]
							]
						])->render();
						$subtitleCI   = view('components.labels.subtitle', ["label" => 'Costo individual'])->render();
						$componentTCI .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", "<div class=\"md:ml-20\">".$subtitleCI."</div>".$modelTableCI));
					@endphp

					componentTCI = '{!!preg_replace("/(\r)*(\n)*/", "", $componentTCI)!!}';
					table_detailCI = $(componentTCI);

					table_detailCI.find(".class_subtotal").text("$"+Number(subtotal).toFixed(2));
					table_detailCI.find(".class_ivaCalc").text("$"+Number(ivaCalc).toFixed(2));
					table_detailCI.find(".class_sum_taxes").text("$"+Number(sum_taxes).toFixed(2));
					table_detailCI.find(".class_sum_retentions").text("$"+Number(sum_retentions).toFixed(2));
					table_detailCI.find(".class_total").text("$"+Number(total).toFixed(2));

					@php
						$componentButtons = "";
						$buttonEdit   = view('components.buttons.button', ["variant" => "success", "attributeEx" => "type=button", "classEx" => "edit-item", "label" => '<span class="icon-pencil"></span> Editar'])->render();
						$buttonDelete = view('components.buttons.button', ["variant" => "red", "attributeEx" => "type=button", "classEx" => "delete-item", "label" => '<span class="icon-x"></span> Eliminar'])->render();
						$componentButtons .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "","<div class=\"text-center my-4\">".$buttonEdit.$buttonDelete."</div>"));
					@endphp

					$componentButtons = '{!!preg_replace("/(\r)*(\n)*/", "", $componentButtons)!!}';
					buttons = $($componentButtons);

					@php
						$inputType = view('components.inputs.input-text',[
							"classEx"	  => "ttipo",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"ttipo[]\"",
						])->render();
					
						$inputDetails = view('components.inputs.input-text',[
							"classEx"	  => "t_flight_details_id",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"flight_details_id[]\"",
						])->render();
					
						$inputPassenger = view('components.inputs.input-text',[
							"classEx"	  => "tpassenger",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tpassenger[]\"",
						])->render();
					
						$inputPositionPas = view('components.inputs.input-text',[
							"classEx"	  => "tpassengerPosition",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tpassengerPosition[]\"",
						])->render();
					
						$inputBurn = view('components.inputs.input-text',[
							"classEx"	  => "tburn",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tburn[]\"",
						])->render();
					
						$inputAirline = view('components.inputs.input-text',[
							"classEx"	  => "tairline",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tairline[]\"",
						])->render();
					
						$inputRoute = view('components.inputs.input-text',[
							"classEx"	  => "troute",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"troute[]\"",
						])->render();
					
						$inputDateFlight = view('components.inputs.input-text',[
							"classEx"	  => "tdateFlight",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdateFlight[]\"",
						])->render();
					
						$inputHourFlight = view('components.inputs.input-text',[
							"classEx"	  => "thourFlight",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"thourFlight[]\"",
						])->render();
					
						$inputAirlineBack = view('components.inputs.input-text',[
							"classEx"	  => "tairlineBack",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tairlineBack[]\"",
						])->render();
					
						$inputRouteBack = view('components.inputs.input-text',[
							"classEx"	  => "trouteBack",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"trouteBack[]\"",
						])->render();
					
						$inputDateFlightBack = view('components.inputs.input-text',[
							"classEx"	  => "tdateFlightBack",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdateFlightBack[]\"",
						])->render();
					
						$inputHourFlightBack = view('components.inputs.input-text',[
							"classEx"	  => "thourFlightBack",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"thourFlightBack[]\"",
						])->render();
					
						$inputDescription = view('components.inputs.input-text',[
							"classEx"	  => "tdescription",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdescription[]\"",
						])->render();
					
						$inputBossName = view('components.inputs.input-text',[
							"classEx"	  => "tbossName",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tbossName[]\"",
						])->render();
					
						$inputBaggage = view('components.inputs.input-text',[
							"classEx"	  => "tbaggage",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tbaggage[]\"",
						])->render();
					
						$inputLastTravel = view('components.inputs.input-text',[
							"classEx"	  => "tlastTravel",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tlastTravel[]\"",
						])->render();
					
						$inputHostPlace = view('components.inputs.input-text',[
							"classEx"	  => "thostPlace",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"thostPlace[]\"",
						])->render();
					
						$inputDateIn = view('components.inputs.input-text',[
							"classEx"	  => "tdateIn",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdateIn[]\"",
						])->render();
					
						$inputDateOut = view('components.inputs.input-text',[
							"classEx"	  => "tdateOut",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tdateOut[]\"",
						])->render();
					
						$inputSubtotal = view('components.inputs.input-text',[
							"classEx"	  => "tsubtotal",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tsubtotal[]\"",
						])->render();
					
						$inputIva = view('components.inputs.input-text',[
							"classEx"	  => "tiva",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tiva[]\"",
						])->render();
					
						$inputTaxes = view('components.inputs.input-text',[
							"classEx"	  => "ttaxes",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"ttaxes[]\"",
						])->render();
					
						$inputRetentions = view('components.inputs.input-text',[
							"classEx"	  => "tretentions",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"tretentions[]\"",
						])->render();
					
						$inputTotal = view('components.inputs.input-text',[
							"classEx"	  => "ttotal",
							"attributeEx" => "readonly=\"true\" type=\"hidden\" name=\"ttotal[]\""
						])->render();
					@endphp


					inputType 			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputType)!!}';
					inputDetails        = '{!!preg_replace("/(\r)*(\n)*/", "", $inputDetails)!!}';
					inputPassenger		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputPassenger)!!}';
					inputPositionPas	= '{!!preg_replace("/(\r)*(\n)*/", "", $inputPositionPas)!!}';
					inputBurn			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputBurn)!!}';
					inputAirline		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputAirline)!!}';
					inputRoute			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputRoute)!!}';
					inputDateFlight		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputDateFlight)!!}';
					inputHourFlight		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputHourFlight)!!}';
					inputAirlineBack	= '{!!preg_replace("/(\r)*(\n)*/", "", $inputAirlineBack)!!}';
					inputRouteBack		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputRouteBack)!!}';
					inputDateFlightBack = '{!!preg_replace("/(\r)*(\n)*/", "", $inputDateFlightBack)!!}';
					inputHourFlightBack = '{!!preg_replace("/(\r)*(\n)*/", "", $inputHourFlightBack)!!}';
					inputDescription	= '{!!preg_replace("/(\r)*(\n)*/", "", $inputDescription)!!}';
					inputBossName		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputBossName)!!}';
					inputBaggage		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputBaggage)!!}';
					inputLastTravel		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputLastTravel)!!}';
					inputHostPlace		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputHostPlace)!!}';
					inputDateIn			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputDateIn)!!}';
					inputDateOut		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputDateOut)!!}';
					inputSubtotal		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputSubtotal)!!}';
					inputIva			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputIva)!!}';
					inputTaxes			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputTaxes)!!}';
					inputRetentions		= '{!!preg_replace("/(\r)*(\n)*/", "", $inputRetentions)!!}';
					inputTotal			= '{!!preg_replace("/(\r)*(\n)*/", "", $inputTotal)!!}';

					row_inputType         	= $(inputType);
					row_inputDetails      	= $(inputDetails);
					row_inputPassenger    	= $(inputPassenger);
					row_inputPositionPas  	= $(inputPositionPas);
					row_inputBurn         	= $(inputBurn);
					row_inputAirline      	= $(inputAirline);
					row_inputRoute        	= $(inputRoute);
					row_inputDateFlight   	= $(inputDateFlight);
					row_inputHourFlight   	= $(inputHourFlight);
					row_inputAirlineBack  	= $(inputAirlineBack);
					row_inputRouteBack      = $(inputRouteBack);
					row_inputDateFlightBack = $(inputDateFlightBack);
					row_inputHourFlightBack = $(inputHourFlightBack);
					row_inputDescription    = $(inputDescription);
					row_inputBossName       = $(inputBossName);
					row_inputBaggage        = $(inputBaggage);
					row_inputLastTravel     = $(inputLastTravel);
					row_inputHostPlace      = $(inputHostPlace);
					row_inputDateIn         = $(inputDateIn);
					row_inputDateOut        = $(inputDateOut);
					row_inputSubtotal       = $(inputSubtotal);
					row_inputIva         	= $(inputIva);
					row_inputTaxes          = $(inputTaxes);
					row_inputRetentions     = $(inputRetentions);
					row_inputTotal          = $(inputTotal);
					
					row_inputType.val(kindf);
					row_inputDetails.val(flight_details_id);
					row_inputPassenger.val(namePassenger);
					row_inputPositionPas.val(passengerPosition);
					row_inputBurn.val(burn);
					row_inputAirline.val(airline);
					row_inputRoute.val(route);
					row_inputDateFlight.val(departureDate);
					row_inputHourFlight.val(departureHour);
					row_inputAirlineBack.val(airlineBack);
					row_inputRouteBack.val(routeReturn);
					row_inputDateFlightBack.val(returnDate);
					row_inputHourFlightBack.val(returnHour);
					row_inputDescription.val(description);
					row_inputBossName.val(bossName);
					row_inputBaggage.val(baggage);
					row_inputLastTravel.val(lastTravel);
					row_inputHostPlace.val(hostPlace);
					row_inputDateIn.val(dateIn);
					row_inputDateOut.val(dateOut);
					row_inputSubtotal.val(Number(subtotal).toFixed(2));
					row_inputIva.val(Number(ivaCalc).toFixed(2));
					row_inputTaxes.val(Number(sum_taxes).toFixed(2));
					row_inputRetentions.val(Number(sum_retentions).toFixed(2));
					row_inputTotal.val(Number(total).toFixed(2));


					$('#body').append($('<div class="countFlight">').
						append(table_detail,table_detailFI,table_detailCI,buttons,
						row_inputType, row_inputDetails,
						row_inputPassenger, row_inputPositionPas,
						row_inputBurn, row_inputAirline,
						row_inputRoute, row_inputDateFlight,
						row_inputHourFlight, row_inputAirlineBack,
						row_inputRouteBack, row_inputDateFlightBack,
						row_inputHourFlightBack, row_inputDescription,
						row_inputBossName, row_inputBaggage,
						row_inputLastTravel, row_inputHostPlace,
						row_inputDateIn, row_inputDateOut,
						row_inputSubtotal, row_inputIva,
						row_inputTaxes,row_inputRetentions,
						row_inputTotal,name_taxes,
						amount_taxes,name_retentions,amount_retentions
					));

					$('input[name="passenger"]').removeClass('error').val("");
					$('input[name="position"]').removeClass('error').val("");
					$('input[name="dateburn"]').removeClass('error').val("");
					$('input[name="aeroline"]').removeClass('error').val("");
					$('input[name="route"]').removeClass('error').val("");
					$('input[name="date_departure"]').removeClass('error').val("");
					$('input[name="time_departure"]').removeClass('error').val("");
					$('input[name="aeroline_back"]').removeClass('error').val("");
					$('input[name="route_back"]').removeClass('error').val("");
					$('input[name="date_back"]').removeClass('error').val("");
					$('input[name="time_back"]').removeClass('error').val("");
					$('input[name="boss"]').removeClass('error').val("");
					$('input[name="checked_baggage"]').removeClass('error').val("");
					$('input[name="date-lftravel"]').removeClass('error').val("");
					$('input[name="host_place"]').removeClass('error').val("");
					$('input[name="date-in"]').removeClass('error').val("");
					$('input[name="date-out"]').removeClass('error').val("");
					$('input[name="subtotal"]').removeClass('error').val("");
					$('textarea[name="description"]').removeClass('error').val("");
					$('input[name="total"]').removeClass('error').val("");
					$('#roundf').removeClass('grid').addClass('hidden');
					$('#hosting_data').removeClass('grid').addClass('hidden');
					$('#single_flight').prop("checked",true);
					$('#host_no').prop('checked',true);
					$('#type_no,#taxes_no,#retention_no').prop('checked',true);
					$('#more_taxes,#more_retentions').empty();
					$('.taxesName,.check_retentionName,.taxesAmount,.check_retentionAmount').val('');

					sum_total();
					$(".edit-item").removeAttr('disabled');
					$(".delete-item").removeAttr('disabled');
				}
			})
			.on('click','.delete-item',function()
			{
				idDeleted = $(this).parents('.countFlight').find('.t_flight_details_id').val();
				$('.deleted_rows').val(idDeleted+","+$('.deleted_rows').val());
				$(this).parents('.countFlight').remove();

				$('.countFlight').each(function(i,v)
				{
					$(this).find('.p_number').text(i+1);
					$(this).find('.num_name_tax').attr('name','t_name_tax'+i+'[]');
					$(this).find('.num_amount_tax').attr('name','t_amount_tax'+i+'[]');
					$(this).find('.num_name_retention').attr('name','t_name_retention'+i+'[]');
					$(this).find('.num_amount_retention').attr('name','t_amount_retention'+i+'[]');
					$(this).find('.num_tax_id').attr('name','t_tax_id'+i+'[]');
					$(this).find('.num_retention_id').attr('name','t_retention_id'+i+'[]');
				});
				sum_total();
			})
			.on('click','.edit-item',function()
			{
				kindf				= $('.countFlight').find('.ttipo').val();
				namePassenger		= $('.countFlight').find('.tpassenger').val();
				passengerPosition	= $('.countFlight').find('.tpassengerPosition').val();
				burn				= $('.countFlight').find('.tburn').val();
				airline				= $('.countFlight').find('.tairline').val();
				route				= $('.countFlight').find('.troute').val();
				departureDate		= $('.countFlight').find('.tdateFlight').val();
				departureHour		= $('.countFlight').find('.thourFlight').val();
				airlineBack			= $('.countFlight').find('.tairlineBack').val();
				routeReturn			= $('.countFlight').find('.trouteBack').val();
				returnDate			= $('.countFlight').find('.tdateFlightBack').val();
				returnHour			= $('.countFlight').find('.thourFlightBack').val();
				bossName			= $('.countFlight').find('.tbossName').val();
				baggage				= $('.countFlight').find('.tbaggage').val();
				lastTravel			= $('.countFlight').find('.tlastTravel').val();
				hostPlace			= $('.countFlight').find('.thostPlace').val();
				dateIn				= $('.countFlight').find('.tdateIn').val();
				dateOut				= $('.countFlight').find('.tdateOut').val();
				subtotal			= $('.countFlight').find('.tsubtotal').val();
				total				= $('.countFlight').find('.ttotal').val();
				description			= $('.countFlight').find('.tdescription').val();
				flight_details_id	= $('.countFlight').find('.t_flight_details_id').val();

				if (flight_details_id == "x") 
				{
					$('.flight_details_id').val('');
				}
				else
				{
					$('.flight_details_id').val(flight_details_id);
				}

				swal({
					title		: "Editar vuelo",
					text		: "Se perderán los impuestos adicionales y retenciones, ¿desea continuar?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((continuar) =>
				{
					if(hostPlace !="" || dateIn != "" || dateOut != "")
					{
						$('#hosting_data').removeClass('hidden').addClass('grid');
						$('#host_si').prop('checked',true);
					}
					else
					{
						$('#hosting_data').removeClass('grid').addClass('hidden');
					}
					if(continuar)
					{
						if(kindf == '1')
						{
							$('#single_flight').prop("checked",true);
							$('#roundf').addClass('hidden').removeClass('grid');
						}
						else if(kindf == '2')
						{
							$('#round_flight').prop("checked",true);
							$('#roundf').removeClass('hidden').addClass('grid');
						}
						$('input[name="passenger"]').val(namePassenger);
						$('input[name="position"]').val(passengerPosition);
						$('input[name="dateburn"]').val(burn);
						$('input[name="aeroline"]').val(airline);
						$('input[name="route"]').val(route);
						$('input[name="date_departure"]').val(departureDate);
						$('input[name="time_departure"]').val(departureHour);
						$('input[name="aeroline_back"]').val(airlineBack);
						$('input[name="route_back"]').val(routeReturn);
						$('input[name="date_back"]').val(returnDate);
						$('input[name="time_back"]').val(returnHour);
						$('input[name="boss"]').val(bossName);
						$('input[name="checked_baggage"]').val(baggage);
						$('input[name="date-lftravel"]').val(lastTravel);
						$('input[name="host_place"]').val(hostPlace);
						$('input[name="date-in"]').val(dateIn);
						$('input[name="date-out"]').val(dateOut);
						$('input[name="subtotal"]').val(subtotal);
						$('input[name="total"]').val(total);
						$('textarea[name="description"]').val(description);
						$(this).parents('.countFlight').remove();

						$('.countFlight').each(function(i,v)
						{
							$(this).find('.p_number').text(i+1);
							$(this).find('.num_name_tax').attr('name','t_name_tax'+i+'[]');
							$(this).find('.num_amount_tax').attr('name','t_amount_tax'+i+'[]');
							$(this).find('.num_name_retention').attr('name','t_name_retention'+i+'[]');
							$(this).find('.num_amount_retention').attr('name','t_amount_retention'+i+'[]');
							$(this).find('.num_tax_id').attr('name','t_tax_id'+i+'[]');
							$(this).find('.num_retention_id').attr('name','t_retention_id'+i+'[]');
						});

						sum_total();
						$(".edit-item").attr('disabled','disabled');
						$(".delete-item").attr('disabled','disabled');
					}
					else
					{
						swal.close();
					}
				});
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

					$selects = collect([
						[
							"identificator"          => ".nameDocument", 
							"placeholder"            => "Seleccione el tipo de documento", 
							"maximumSelectionLength" => "1"
						]
					]);
				
					$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));

					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"componentsExUp"       => $select,
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\"",
						"classExRealPath"	   => "path",
						"attributeExRealPath"  => "name=\"realPath[]\"",
					])->render();
				@endphp
					
			
				newDocF = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				$('#documents').removeClass('hidden').append(newDocF);
				
				$('[name="monto[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
				
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
					$(".datepicker").datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
					$(".datepicker2").datepicker({ dateFormat: "dd-mm-yy" });
				});
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('.path');
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
						url			: '{{ route("flights-lodging.uploader") }}',
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
				uploadedName	= $(this).parent('.docs-p-r').siblings('.docs-p-l').children('.path');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("flights-lodging.uploader") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parent('.docs-p-r').parent('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parent('.docs-p-r').parent('.docs-p').remove();
					}
				});
				$(this).parents('div.docs-p').remove();
			})
			.on('click','#saveLU',function(e)
			{
				e.preventDefault();
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				action = $(this).attr('formaction');
				form = $('form#container-alta').attr('action',action);
				form.submit();
			})
			.on('click','.enviarSR',function(e)
			{
				e.preventDefault();
				action = $(this).attr('formaction');
				form = $('form#container-alta').attr('action',action);
				form.submit();
			})
			.on('click','.saveU',function(e)
			{
				e.preventDefault();
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				action = $(this).attr('formaction');
				form = $('form#container-alta').attr('action',action);
				form.submit();
			})
			.on('change','[name="subtotal"],[name="iva"],.taxesAmount,.check_retentionAmount',function()
			{
				subtotal		= Number($('[name="subtotal"]').val());
				typeTax			= $('input[name="iva"]:checked').val();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc			= 0;
				sum_taxes		= 0;
				sum_retentions	= 0;

				if ($('[name="taxes"]:checked').val() == 'si') 
				{
					$('.taxesAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_taxes += Number($(this).val());
						}
					});
				}

				if ($('[name="check_retention"]:checked').val() == 'si') 
				{
					$('.check_retentionAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_retentions += Number($(this).val());
						}
					});
				}

				switch(typeTax)
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = subtotal*iva;
						break;
					case 'b':
						ivaCalc = subtotal*iva2;
						break;
				}
				total = ((subtotal + ivaCalc + sum_taxes) - sum_retentions);
				if (total < 0) 
				{
					$(this).val('');
					swal('','El total no puede ser negativo','info');
				}
				else
				{
					$('[name="total"]').val(Number(total).toFixed(2));
				}
			})
			.on('click','.delete-tax',function()
			{
				$(this).parents('.span-tax').remove();
				subtotal		= Number($('[name="subtotal"]').val());
				typeTax			= $('input[name="iva"]:checked').val();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc			= 0;
				sum_taxes		= 0;
				sum_retentions	= 0;

				if ($('[name="taxes"]:checked').val() == 'si') 
				{
					$('.taxesAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_taxes += Number($(this).val());
						}
					});
				}

				if ($('[name="check_retention"]:checked').val() == 'si') 
				{
					$('.check_retentionAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_retentions += Number($(this).val());
						}
					});
				}

				switch(typeTax)
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = subtotal*iva;
						break;
					case 'b':
						ivaCalc = subtotal*iva2;
						break;
				}
				total = ((subtotal + ivaCalc + sum_taxes) - sum_retentions);
				$('[name="total"]').val(Number(total).toFixed(2));
			})
			.on('click','.delete-retention',function()
			{
				$(this).parents('.span-retention').remove();
				subtotal		= Number($('[name="subtotal"]').val());
				typeTax			= $('input[name="iva"]:checked').val();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc			= 0;
				sum_taxes		= 0;
				sum_retentions	= 0;

				if ($('[name="taxes"]:checked').val() == 'si') 
				{
					$('.taxesAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_taxes += Number($(this).val());
						}
					});
				}

				if ($('[name="check_retention"]:checked').val() == 'si') 
				{
					$('.check_retentionAmount').each(function(i,v)
					{
						if ($(this).val() != "") 
						{
							sum_retentions += Number($(this).val());
						}
					});
				}

				switch(typeTax)
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = subtotal*iva;
						break;
					case 'b':
						ivaCalc = subtotal*iva2;
						break;
				}
				total = ((subtotal + ivaCalc + sum_taxes) - sum_retentions);
				$('[name="total"]').val(Number(total).toFixed(2));
			})
		});
		
		function sum_total()
		{
			subtotal	= 0;
			iva			= 0;
			taxes		= 0;
			retentions	= 0;
			$(".countFlight").each(function(i, v)
			{
				iva			+= Number($(this).find('.tiva').val());
				subtotal	+= Number($(this).find('.tsubtotal').val());
				taxes		+= Number($(this).find('.ttaxes').val());
				retentions	+= Number($(this).find('.tretentions').val());
			});
			total = ((subtotal+iva+taxes)-retentions);
			

			$('[name="subtotal_flight"]').val(Number(subtotal).toFixed(2));
			$('.subtotal_flight').text("$"+Number(subtotal).toFixed(2));

			$('[name="iva_flight"]').val(Number(iva).toFixed(2));
			$('.iva_flight').text("$"+Number(iva).toFixed(2));

			$('[name="taxes_flight"]').val(Number(taxes).toFixed(2));
			$('.taxes_flight').text("$"+Number(taxes).toFixed(2));

			$('[name="retentions_flight"]').val(Number(retentions).toFixed(2));
			$('.retentions_flight').text("$"+Number(retentions).toFixed(2));

			$('[name="total_flight"]').val(Number(total).toFixed(2));
			$('.total_flight').text("$"+Number(total).toFixed(2));
		}

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
				onSuccess	:function($form)
				{
					@if(isset($request))
						@if($request->status == 2 && !isset($new_request))
							if($('form#container-alta').attr('action') == $('.enviarSR').attr('formaction'))
							{
								if(($('.countFlight').length)==0)
								{
									swal('', 'Debe agregar al menos un vuelo.', 'warning');
									return false;
								}
								else
								{
									namePassenger		= $('input[name="passenger"]').removeClass('error').val();
									passengerPosition	= $('input[name="position"]').removeClass('error').val();
									burn				= $('input[name="dateburn"]').removeClass('error').val();
									airline				= $('input[name="aeroline"]').removeClass('error').val();
									route				= $('input[name="route"]').removeClass('error').val();
									departureDate		= $('input[name="date_departure"]').removeClass('error').val();
									airlineBack			= $('input[name="aeroline_back"]').removeClass('error').val();
									routeReturn			= $('input[name="route_back"]').removeClass('error').val();
									returnDate			= $('input[name="date_back"]').removeClass('error').val();
									bossName			= $('input[name="boss"]').removeClass('error').val();
									baggage				= $('input[name="checked_baggage"]').removeClass('error').val();
									lastTravel			= $('input[name="date-lftravel"]').removeClass('error').val();
									hostPlace			= $('input[name="host_place"]').removeClass('error').val();
									dateIn				= $('input[name="date-in"]').removeClass('error').val();
									dateOut				= $('input[name="date-out"]').removeClass('error').val();
									subtotal			= $('input[name="subtotal"]').removeClass('error').val();
									total				= $('input[name="total"]').removeClass('error').val();
									description			= $('textarea[name="description"]').removeClass('error').val();
									path				= $('.path').length;
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
									if(namePassenger != "" || passengerPosition != "" || burn !="" || airline != ""  || route != "" || departureDate != ""
									|| bossName != "" || baggage != "" || lastTravel != "" || hostPlace != "" || dateIn != "" || dateOut != "" || subtotal != "" || total != "" 
									|| description != "" || airlineBack != "" || routeReturn != "" || returnDate != "")
									{
										swal('', 'Tiene un concepto sin agregar', 'error');
										return false;
									}
								}
								
							}
						@endif
						@if(isset($new_request) && $new_request)
							if($('form#container-alta').attr('action') != $('#saveLU').attr('formaction'))
							{
								if(($('.countFlight').length)==0)
								{
									swal('', 'Debe agregar al menos un vuelo.', 'warning');
									return false;
								}
								else
								{
									namePassenger		= $('input[name="passenger"]').removeClass('error').val();
									passengerPosition	= $('input[name="position"]').removeClass('error').val();
									burn				= $('input[name="dateburn"]').removeClass('error').val();
									airline				= $('input[name="aeroline"]').removeClass('error').val();
									route				= $('input[name="route"]').removeClass('error').val();
									departureDate		= $('input[name="date_departure"]').removeClass('error').val();
									airlineBack			= $('input[name="aeroline_back"]').removeClass('error').val();
									routeReturn			= $('input[name="route_back"]').removeClass('error').val();
									returnDate			= $('input[name="date_back"]').removeClass('error').val();
									bossName			= $('input[name="boss"]').removeClass('error').val();
									baggage				= $('input[name="checked_baggage"]').removeClass('error').val();
									lastTravel			= $('input[name="date-lftravel"]').removeClass('error').val();
									hostPlace			= $('input[name="host_place"]').removeClass('error').val();
									dateIn				= $('input[name="date-in"]').removeClass('error').val();
									dateOut				= $('input[name="date-out"]').removeClass('error').val();
									subtotal			= $('input[name="subtotal"]').removeClass('error').val();
									total				= $('input[name="total"]').removeClass('error').val();
									description			= $('textarea[name="description"]').removeClass('error').val();
									path				= $('.path').length;
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
									if(namePassenger != "" || passengerPosition != "" || burn !="" || airline != ""  || route != "" || departureDate != ""
									|| bossName != "" || baggage != "" || lastTravel != "" || hostPlace != "" || dateIn != "" || dateOut != "" || subtotal != "" || total != "" 
									|| description != "" || airlineBack != "" || routeReturn != "" || returnDate != "")
									{
										swal('', 'Tiene un concepto sin agregar', 'error');
										return false;
									}
								}
							}
						@endif
					@else
						if($('form#container-alta').attr('action') != $('#saveLU').attr('formaction'))
						{
							if(($('.countFlight').length)==0)
								{
									swal('', 'Debe agregar al menos un vuelo.', 'warning');
									return false;
								}
							else
							{
								namePassenger		= $('input[name="passenger"]').removeClass('error').val();
								passengerPosition	= $('input[name="position"]').removeClass('error').val();
								burn				= $('input[name="dateburn"]').removeClass('error').val();
								airline				= $('input[name="aeroline"]').removeClass('error').val();
								route				= $('input[name="route"]').removeClass('error').val();
								departureDate		= $('input[name="date_departure"]').removeClass('error').val();
								airlineBack			= $('input[name="aeroline_back"]').removeClass('error').val();
								routeReturn			= $('input[name="route_back"]').removeClass('error').val();
								returnDate			= $('input[name="date_back"]').removeClass('error').val();
								bossName			= $('input[name="boss"]').removeClass('error').val();
								baggage				= $('input[name="checked_baggage"]').removeClass('error').val();
								lastTravel			= $('input[name="date-lftravel"]').removeClass('error').val();
								hostPlace			= $('input[name="host_place"]').removeClass('error').val();
								dateIn				= $('input[name="date-in"]').removeClass('error').val();
								dateOut				= $('input[name="date-out"]').removeClass('error').val();
								subtotal			= $('input[name="subtotal"]').removeClass('error').val();
								total				= $('input[name="total"]').removeClass('error').val();
								description			= $('textarea[name="description"]').removeClass('error').val();
								path				= $('.path').length;
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
								if(namePassenger != "" || passengerPosition != "" || burn !="" || airline != ""  || route != "" || departureDate != ""
								|| bossName != "" || baggage != "" || lastTravel != "" || hostPlace != "" || dateIn != "" || dateOut != "" || subtotal != "" || total != "" 
								|| description != "" || airlineBack != "" || routeReturn != "" || returnDate != "")
								{
									swal('', 'Tiene un concepto sin agregar', 'error');
									return false;
								}
							}
						}
					@endif
				}
			});
		}
	</script>
@endsection