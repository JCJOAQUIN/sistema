@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"edit_employee\" action=\"".route('nomina.nomina-create.employeeupdatenomina')."\""])
	<div>
		@component('components.labels.title-divisor') DETALLES DE EMPLEADO @endcomponent
		<form id="form-employee">
			@component("components.containers.container-form")
				<div class="col-span-2">
					<div class="mb-4">
						@component('components.labels.label') Nombre(s): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el nombre" 
								type="text" 
								name="name"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->name }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Apellido Paterno: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el apellido" 
								type="text" 
								name="last_name"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->last_name }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Apellido Materno (Opcional): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el apellido"
								type="text"
								name="scnd_last_name"
								value="{{ $nominaemployee->employee->first()->scnd_last_name }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') CURP: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese un CURP" 
								type="text"
								name="curp"
								data-validation="server"
								data-validation-url="{{url('configuration/employee/curp')}}"
								@isset($nominaemployee->employee->first()->curp) data-validation-req-params="{{json_encode(array('oldCurp'=>$nominaemployee->employee->first()->curp))}}" @endisset
								value="{{ $nominaemployee->employee->first()->curp }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') RFC: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el RFC con homoclave"
								type="text"
								name="rfc"
								data-validation="server"  
								data-validation-url="{{url('configuration/employee/rfc')}}" 
								@isset($nominaemployee->employee->first()->rfc) data-validation-req-params="{{ json_encode(array('oldRfc'=>$nominaemployee->employee->first()->rfc)) }}" @endisset
								value="{{ $nominaemployee->employee->first()->rfc }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') #IMSS: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el número de imss"
								type="text" 
								name="imss" 
								data-validation="custom"
								data-validation-regexp="^(\d{10}-\d{1})$"
								data-validation-error-msg="Por favor, ingrese un # IMSS válido"
								data-validation-optional="true"
								value="{{ $nominaemployee->employee->first()->imss }}" 
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Correo electrónico: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el correo electrónico" type="text" name="email" data-validation="email" value="{{ $nominaemployee->employee->first()->email }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Número teléfonico: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el número teléfonico" type="text" name="phone" data-validation="phone required" value="{{ $nominaemployee->employee->first()->phone }}"
							@endslot
						@endcomponent
					</div>	
				</div>
				<div class="col-span-2">
					<div class="mb-4">
						@component('components.labels.label') Calle: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la calle"
								type="text"
								name="street"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->street }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	 
						@component('components.labels.label') Número: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text"
								name="number"
								placeholder="Ingrese en número"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->number }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	 
						@component('components.labels.label') Colonia: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text"
								name="colony"
								placeholder="Ingrese la colonia"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->colony }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	
						@component('components.labels.label') Código Postal: @endcomponent
						@php
							$optionCP = [];
							if(isset($nominaemployee->employee->first()->cp) && $nominaemployee->employee->first()->cp != "")
							{
								$optionCP[] = ["value" => $nominaemployee->employee->first()->cp, "description" => $nominaemployee->employee->first()->cp, "selected" => "selected"];
							}
							else
							{
								$optionCP[] = ["value" => $nominaemployee->employee->first()->cp, "description" => $nominaemployee->employee->first()->cp];
							}
						@endphp
						@component('components.inputs.select', ['options' => $optionCP])
							@slot('attributeEx')
								name="cp" id="cp" placeholder="Ingrese el código postal" multiple="multiple" data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	 
						@component('components.labels.label') Ciudad: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text"
								name="city"
								placeholder="Ingrese la ciudad"
								data-validation="required"
								value="{{ $nominaemployee->employee->first()->city }}"
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	 
						@component('components.labels.label') Estado: @endcomponent
						@php
							$optionState 	= [];
							$e 				= App\State::where('idstate', $nominaemployee->employee->first()->state_id)->first();

							if(isset($e) && $e != '')
							{
								$optionState[] = ["value" => $e->idstate, "description" => $e->description, "selected" => "selected"];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionState])
							@slot('attributeEx')
								name="state"
								multiple
								data-validation="required"
							@endslot
							@slot('classEx')
								js-state
							@endslot
						@endcomponent
					</div>
				</div>
				@slot("attributeEx")
					id="container-data"
				@endslot
			@endcomponent
			@component('components.labels.title-divisor') INFORMACIÓN LABORAL @endcomponent
			<div class="flex justify-center my-4">
				@component('components.inputs.switch')
					@slot('attributeEx')
						type="checkbox"
						id="editworker"
						name="editworker"
						value="x"
					@endslot
						Habilitar edición 
				@endcomponent
				@component('components.labels.label')
					<span class="help-btn" id="help-btn-edit-employee"></span>
				@endcomponent
			</div>
			@component('components.containers.container-form')
				<div class="col-span-2">
					<div class="mb-4">
						@component('components.labels.label') Estado: @endcomponent
						@php
							$optionWork = [];
							$e			= App\State::where('idstate',$nominaemployee->workerData->first()->state)->first();
							if(isset($e) && $e != '') 
							{
								$optionWork[] = ["value" => $e->idstate, "description" => $e->description, "selected" => "selected"];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionWork ])
							@slot('attributeEx')
								disabled="disabled" 
								name="work_state"
								multiple
								data-validation="required"
							@endslot
							@slot('classEx')
								js-state disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Proyecto: @endcomponent
						@php
							$project = App\Project::orderName()->where('status',1)
								->where('idproyect',$nominaemployee->workerData->first()->project)
								->first();
						 
						 	$optionWorkProject = [];
							if ($nominaemployee->workerData->first()->project == $project->idproyect)
							{
								$optionWorkProject[] = ["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionWorkProject ])
							@slot('attributeEx')
								disabled="disabled" 
								name="work_project"
								multiple
							@endslot
							@slot('classEx')
								disabled
								js-projects
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">	 
						<div class="select_father @if($nominaemployee->workerData->first()->employeeHasWbs()->exists() || $nominaemployee->workerData->first()->projects->codeWBS()->exists()) block @else hidden @endif">
							@component('components.labels.label') WBS: @endcomponent
							@php
								$optionWorkWbs = [];
								foreach(App\CatCodeWBS::where('project_id',$nominaemployee->workerData->first()->project)->get() as $wbs)
								{
									if(isset($nominaemployee) && count($nominaemployee->workerData)>0 && in_array($wbs->id, json_decode(json_encode($nominaemployee->workerData->first()->employeeHasWbs->pluck('id')),true)))
									{
										$optionWorkWbs[] = ["value" => $wbs->id, "description" => $wbs->code_wbs, "selected" => "selected"];
									}
									else
									{
										$optionWorkWbs[] = ["value" => $wbs->id, "description" => $wbs->code_wbs];
									}
								}
							@endphp
							@component('components.inputs.select', ["options" => $optionWorkWbs ])
								@slot('attributeEx')
									disabled="disabled"
									name="work_wbs[]"
									multiple="multiple"
								@endslot
								@slot('classEx')
									disabled
									js-wbs
								@endslot
							@endcomponent
						</div>
					</div>
					<div class="mb-4">
						@component('components.labels.label') Empresa: @endcomponent
						@php
							$optionEnterprise = [];
							foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
							{
								if($nominaemployee->workerData->first()->enterprise == $enterprise->id)
								{
									$optionEnterprise[] = ["value" => $enterprise->id, "description" =>  $enterprise->name, "selected" => "selected"];
								}
								else
								{
									$optionEnterprise[] = ["value" => $enterprise->id, "description" =>  $enterprise->name];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionEnterprise ])
							@slot('attributeEx')
								disabled="disabled" 
								name="work_enterprise"
								multiple 
								data-validation="required"
							@endslot
							@slot('classEx')
								js-enterprises disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Clasificación de Gasto: @endcomponent
						@php
							$a = App\Account::where('idEnterprise',$nominaemployee->workerData->first()->enterprise)
								->where('idAccAcc',$nominaemployee->workerData->first()->account)
								->where(function($q)
								{
									$q->where('account','LIKE','5102%')
									->orWhere('account','LIKE','5303%')
									->orWhere('account','LIKE','5403%');
								})
								->where('selectable',1)
								->first();

							$optionWorkAccount = [];
							if ($nominaemployee->workerData->first()->account == $a->idAccAcc)
							{
								$optionWorkAccount[] = ["value" => $a->idAccAcc, "description" => $a->account.' - '.$a->description.' ('.$a->content.')', "selected" => "selected" ];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionWorkAccount])
							@slot('attributeEx')
								disabled="disabled"
								multiple
								name="work_account"
								data-validation="required"
							@endslot
							@slot('classEx')
								js-accounts disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Lugar de trabajo (opcional): @endcomponent
						@php
							$optionPlace = [];
							foreach(App\Place::orderName()->where('status',1)->get() as $place)
							{	
								$flag = false;
								foreach ($nominaemployee->workerData->first()->places as $p)
								{
									if($place->id == $p->id) 
									{
										$flag = true;
									}
								}
								if($flag) 
								{
									$optionPlace[] = ["value" => $place->id, "description" => $place->place, "selected" => "selected"];
								}
								else 
								{
									$optionPlace[] = ["value" => $place->id, "description" => $place->place];
								}
							}
						@endphp
						@component('components.inputs.select', [ "options" => $optionPlace])
							@slot('attributeEx')
								disabled="disabled"
								name="work_place[]"
								multiple
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Dirección: @endcomponent
						@php
							$optionDirection = [];
							foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
							{
								if($nominaemployee->workerData->first()->direction == $area->id)
								{
									$optionDirection[] = ["value" => $area->id, "description" => $area->name, "selected" => "selected"];
								}
								else
								{
									$optionDirection[] = ["value" => $area->id, "description" => $area->name];
								}
							}
						@endphp
						@component('components.inputs.select', [ "options" => $optionDirection])
							@slot('attributeEx')
								disabled="disabled"
								name="work_direction"
								multiple
								data-validation="required"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Departamento: @endcomponent
						@php
							$optionDepartament = [];
							foreach(App\Department::orderName()->where('status','ACTIVE')->get() as $area)
							{
								if($nominaemployee->workerData->first()->department == $area->id)
								{
									$optionDepartament[] = ["value" => $area->id, "description" => $area->name,"selected" => "selected"];
								}
								else
								{
									$optionDepartament[] = ["value" => $area->id, "description" => $area->name];
								}
							}
						@endphp
						@component('components.inputs.select', [ "options" => $optionDepartament])
							@slot('attributeEx')
								disabled="disabled"
								multiple
								name="work_department"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') SubDepartamento: @endcomponent
						@php
							$optionSubdepartment = [];
							foreach(App\Subdepartment::orderName()->get() as $subdepartment)
							{
								if(isset($nominaemployee) && count($nominaemployee->workerData)>0 && in_array($subdepartment->id, json_decode(json_encode($nominaemployee->workerData->first()->employeeHasSubdepartment->pluck('id')),true)))
								{
									$optionSubdepartment[] = ["value" => $subdepartment->id, "description" => $subdepartment->name, "selected" => "selected"];
								}
								else
								{
									$optionSubdepartment[] = ["value" => $subdepartment->id, "description" => $subdepartment->name];
								}
							}
						@endphp
						@component('components.inputs.select', [ "options" => $optionSubdepartment])
							@slot('attributeEx')
								disabled="disabled"
								multiple="multiple" 
								name="work_subdepartment[]"
							@endslot
							@slot('classEx')
								disabled 
								js-work-subdepartment
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Puesto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el puesto" 
								type="text" 
								name="work_position"
								disabled="disabled" 
								data-validation="length required" 
								data-validation-length="max100" 
								value="{{ $nominaemployee->workerData->first()->position }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Jefe inmediato: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el jefe inmediato" 
								type="text" 
								name="work_immediate_boss" 
								disabled="disabled" 
								data-validation="length" 
								data-validation-length="max100" 
								value="{{ $nominaemployee->workerData->first()->immediate_boss }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@php
							$newDateAdmission = $nominaemployee->workerData->first()->admissionDate != '' ? $nominaemployee->workerData->first()->admissionDate->format('d-m-Y') : '';
						@endphp
						@component('components.labels.label') Fecha de ingreso: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la fecha" 
								type="text" 
								name="work_income_date"
								disabled="disabled" 
								data-validation="required"
								value="{{ $newDateAdmission }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Estado de IMSS: @endcomponent
						@php
							$optionImss	= [];
							$valueIMSS	= ["0" => "Inactivo", "1" => "Activo"];
							foreach ($valueIMSS as $k => $v)
							{
								$optionImss[] = [
									"value"			=> $k,
									"description"	=> $v,
									"selected"		=> ($nominaemployee->workerData->first()->status_imss == $k ? "selected" : "")
								];
							}
						@endphp
						@component('components.inputs.select',[ "options" => $optionImss])
							@slot('attributeEx')
								multiple
								name="work_status_imss"
								disabled="disabled"
							@endslot
							@slot('classEx')
								disabled
								js-imss
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@php
							$newDateImss = $nominaemployee->workerData->first()->imssDate != '' ? $nominaemployee->workerData->first()->imssDate->format('d-m-Y') : '';
						@endphp
						@component('components.labels.label') Fecha de alta IMSS (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la fecha"
								type="text"
								name="work_imss_date"
								disabled="disabled" 
								value="{{ $newDateImss }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@php
							$newDateDown = $nominaemployee->workerData->first()->downDate != '' ? $nominaemployee->workerData->first()->downDate->format('d-m-Y') : '';
						@endphp
						@component('components.labels.label') Fecha de baja (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la fecha"
								type="text"
								name="work_down_date"
								disabled="disabled"
								value="{{ $newDateDown	}}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@php
							$newDateEnding	= $nominaemployee->workerData->first()->endingDate != '' ? $nominaemployee->workerData->first()->endingDate->format('d-m-Y') : '';
						@endphp
						@component('components.labels.label') Fecha de término de relación laboral (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese la fecha"
								type="text"
								name="work_ending_date"
								disabled="disabled"
								value="{{ $newDateEnding }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
				</div>
				<div class="col-span-2">
					<div class="mb-4">
						@php
							$newDateReentry		= $nominaemployee->workerData->first()->reentryDate != '' ? $nominaemployee->workerData->first()->reentryDate->format('d-m-Y') : '';
						@endphp 	 
						@component('components.labels.label') Reingreso (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el reingreso"
								type="text"
								name="work_reentry_date"
								disabled="disabled"
								value="{{ $newDateReentry }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Tipo de trabajador: @endcomponent
						@php
							$optionWorkEmployee = [];
							foreach(App\CatContractType::orderName()->get() as $contract)
							{
								if($nominaemployee->workerData->first()->workerType==$contract->id)
								{
									$optionWorkEmployee[] = ["value" => $contract->id, "description" => $contract->description, "selected" => "selected"];
								}
								else
								{
									$optionWorkEmployee[] = ["value" => $contract->id, "description" => $contract->description];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionWorkEmployee ])
							@slot('attributeEx')
								disabled="disabled" 
								multiple="multiple"
								name="work_type_employee" 
								data-validation="required"
							@endslot
							@slot('classEx')
								disabled
								js-work-employee
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Régimen: @endcomponent
						@php
							$optionRegime = [];
							foreach(App\CatRegimeType::orderName()->get() as $regime)
							{
								if($nominaemployee->workerData->first()->regime_id==$regime->id)
								{
									$optionRegime[] = ["value" => $regime->id, "description" => $regime->description, "selected" => "selected"];
								}
								else
								{
									$optionRegime[] = ["value" => $regime->id, "description" => $regime->description];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionRegime ])
							@slot('attributeEx')
								disabled="disabled"
								multiple="multiple"
								name="work_regime_employee"
								data-validation="required"
							@endslot
							@slot('classEx')
								disabled
								js-regime
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Estatus: @endcomponent
						@php
							$optionStatus = [];
							$valueStatus = [
								"1" => "Activo",
								"2" => "Baja pacial",
								"3" => "Baja definitiva",
								"4" => "Suspensión",
								"5" => "Boletinado"
							];
							foreach ($valueStatus as $key => $value)
							{
								$optionStatus[] = [
									"value"			=> $key,
									"description"	=> $value,
									"selected"		=> ($nominaemployee->workerData->first()->workerStatus == $key ? "selected" : "")
								];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionStatus])
							@slot('attributeEx')
								multiple="multiple"
								disabled="disabled"
								name="work_status_employee"
								data-validation="required"
							@endslot
							@slot('classEx')
								disabled
								js-status
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') SDI (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el SDI"
								type="text"
								name="work_sdi"
								disabled="disabled" 
								data-validation="number"
								data-validation-allowing="float"
								data-validation-optional="true" 
								value="{{ $nominaemployee->workerData->first()->sdi }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Periodicidad: @endcomponent
						@php
							$optionPeriodicity = [];
							foreach(App\CatPeriodicity::orderName()->get() as $per)
							{
								if ($nominaemployee->workerData->first()->periodicity == $per->c_periodicity)
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
								}
								else
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionPeriodicity])
							@slot('attributeEx')
								disabled="disabled"
								name="work_periodicity"
								data-validation="required"
								multiple="multiple"
							@endslot
							@slot('classEx')
								disabled
								js-periodicity
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Registro patronal: @endcomponent
						@php
							$optionEmployerRegister = [];
							$ers = App\EmployerRegister::where('enterprise_id',$nominaemployee->workerData->first()->enterprise)->get();
							foreach ($ers as $er)
							{
								if($nominaemployee->workerData->first()->employer_register == $er->employer_register)
								{
									$optionEmployerRegister[] = ["value" => $er->employer_register, "description" => $er->employer_register, "selected" => "selected"];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionEmployerRegister])
							@slot('attributeEx')
								name="work_employer_register"
								multiple
								disabled="disabled"
								data-validation="required"
							@endslot
							@slot('classEx')
								disabled
								js-Employer-Register
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Forma de pago: @endcomponent
						@php
							$optionPaymentMethod = [];
							foreach(App\PaymentMethod::orderName()->get() as $pay)
							{
								if($nominaemployee->workerData->first()->paymentWay==$pay->idpaymentMethod)
								{
									$optionPaymentMethod[] = ["value" => $pay->idpaymentMethod, "description" => $pay->method, "selected" => "selected"];
								}
								else
								{
									$optionPaymentMethod[] = ["value" => $pay->idpaymentMethod, "description" => $pay->method];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionPaymentMethod ])
							@slot('attributeEx')
								name="work_payment_way"
								data-validation="required"
								multiple
								disabled="disabled"
							@endslot
							@slot('classEx')
								disabled
								js-work-payment
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Sueldo neto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el sueldo neto"
								type="text"
								name="work_net_income"
								disabled="disabled"
								value="{{ $nominaemployee->workerData->first()->netIncome }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Complemento (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el complemento"
								type="text"
								name="work_complement"
								disabled="disabled"
								data-validation="number"
								data-validation-allowing="float"
								data-validation-optional="true"
								value="{{ $nominaemployee->workerData->first()->complement }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
					<div class="mb-4">
						@component('components.labels.label') Monto Fonacot (si aplica): @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								placeholder="Ingrese el monto fonacot"
								type="text"
								name="work_fonacot"
								disabled="disabled"
								data-validation="number"
								data-validation-allowing="float"
								data-validation-optional="true"
								value="{{ $nominaemployee->workerData->first()->fonacot }}"
							@endslot
							@slot('classEx')
								disabled
							@endslot
						@endcomponent
					</div>
				</div>
			@endcomponent
			<div>
				@php
					$body 		= [];
					$modelBody	= [];
					$modelHead	= ["Empresa Anterior", "Fecha de Ingreso"];

					$optionEnterprise = [];
					foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
					{
						if ($nominaemployee->workerData->first()->enterpriseOld == $enterprise->id) 
						{
							$optionEnterprise[] = ["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"];
						}
						else
						{
							$optionEnterprise[] = ["value" => $enterprise->id, "description" => $enterprise->name];
						}
					}

					$newDateOld	= $nominaemployee->workerData->first()->admissionDateOld != '' ? $nominaemployee->workerData->first()->admissionDateOld->format('d-m-Y') : '';
					$body = [
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "name=\"work_enterprise_old\" disabled=\"disabled\" multiple",
									"classEx"		=> "disabled",
									"options"		=> $optionEnterprise
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "placeholder=\"Ingrese la fecha\" type=\"text\" name=\"work_income_date_old\" disabled=\"disabled\" value=\"".$newDateOld."\"",
									"classEx"		=> "disabled"
								]
							]
						]
					];
					$modelBody[] = $body;
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelHead" => $modelHead,
					"modelBody"	=> $modelBody,
					"title"		=> "NEGOCIACIONES DE CAMBIO DE EMPRESA"
				])
				@endcomponent
			</div>
			<div class="mb-4">
				@php
					$body 		= [];
					$modelBody	= [];
					$modelHead	= ["Porcentaje de nómina", "Porcentaje de bonos"];
					
					$body = [
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "placeholder=\"Ingrese el porcentaje\" type=\"text\" name=\"work_nomina\" disabled=\"disabled\" data-validation=\"number required\" value=\"".$nominaemployee->workerData->first()->nomina."\"",
									"classEx"		=> "disabled"
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "placeholder=\"Ingrese el porcentaje\" type=\"text\" name=\"work_bonus\" disabled=\"disabled\" data-validation=\"number required\" value=\"".$nominaemployee->workerData->first()->bono."\"",
									"classEx"		=> "disabled"
								]
							]
						]
					];
					$modelBody[] = $body;
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelHead" => $modelHead,
					"modelBody"	=> $modelBody,
					"title"		=> "Esquema de pagos"
				])
				@endcomponent
			</div>
			<div class="flex justify-center mb-4">
				@if ($nominaemployee->workerData->first()->infonavitCredit != '')
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox"
							checked="checked"
							disabled="disabled"
							id="infonavit"
							name="infonavit"
						@endslot
						@slot('classEx')
							disabled
						@endslot
						Infonavit
					@endcomponent
				@else
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox"
							disabled="disabled"
							id="infonavit"
							name="infonavit"
						@endslot
						@slot('classEx')
							disabled
						@endslot
						Infonavit
					@endcomponent
				@endif
			</div>
			<div class="tbody mb-4 @if($nominaemployee->workerData->first()->infonavitCredit == '') hidden @endif">
				@php
					$body 		= [];
					$modelBody 	= [];
					$modelHead	= [
						[
							["value" => "Número de crédito"],
							["value" => "Descuento"],
							["value" => "Tipo de descuento"],
						]
					];

					$optionInfonavit = [];
					if($nominaemployee->workerData->first()->infonavitDiscountType == 1) 
					{
						$optionInfonavit[] = ["value" => "1", "description" => "VSM (Veces Salario Mínimo)", "selected" => "selected"];
					}
					else
					{
						$optionInfonavit[] = ["value" => "1", "description" => "VSM (Veces Salario Mínimo)"];
					}
					if ($nominaemployee->workerData->first()->infonavitDiscountType == 2)
					{
						$optionInfonavit[] = ["value" => "2", "description" => "Cuota fija", "selected" => "selected"];
					}
					else
					{
						$optionInfonavit[] = ["value" => "2", "description" => "Cuota fija"];
					}
					if ($nominaemployee->workerData->first()->infonavitDiscountType == 3)
					{
						$optionInfonavit[] = ["value" => "3", "description" => "Porcentaje", "selected" => "selected"];
					}
					else
					{
						$optionInfonavit[] = ["value" => "3", "description" => "Porcentaje"];
					}

					$body = [
						[
							"content" => 
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el credito\" disabled=\"disabled\" name=\"work_infonavit_credit\" data-validation=\"required\" value=\"".$nominaemployee->workerData->first()->infonavitCredit."\"",
								"classEx"		=> "disabled"
							]
						],
						[
							"content" => 
							[
								"kind" 			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el descuento\" disabled=\"disabled\" name=\"work_infonavit_discount\" data-validation=\"number required\" data-validation-allowing=\"float\" value=\"".$nominaemployee->workerData->first()->infonavitDiscount."\"",
								"classEx"		=> "disabled"
							]
						],
						[
							"content" => 
							[
								"kind"			=> "components.inputs.select",
								"attributeEx"	=> "name=\"work_infonavit_discount_type\" disabled=\"disabled\" multiple=\"multiple\" data-validation=\"required\"",
								"options"		=> $optionInfonavit,
								"classEx"		=> "disabled js-infonavit-discount-type"
							]
						]
					];
					$modelBody[] = $body;
				@endphp
				@component('components.tables.table', [
						"modelBody"	=> $modelBody,
						"modelHead" => $modelHead
					])
				@endcomponent
			</div>
			<div class="my-4">
				@component('components.labels.title-divisor') CUENTAS BANCARIAS @endcomponent
				@component('components.containers.container-form')
					@slot("classEx")
						tr_bank
					@endslot
					@slot("attributeEx")
						id="bank-data-register"
					@endslot
					<div class="col-span-2">
						@component('components.labels.label') Alias: @endcomponent
						@component('components.inputs.input-text',
							[
								"attributeEx" 	=> "type=\"text\" placeholder=\"Ingrese un alias\"",
								"classEx"		=> "alias"
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Banco: @endcomponent
						@component('components.inputs.select', 
							[
								"attributeEx"	=> "multiple=\"multiple\"",
								"classEx"		=> "bank",
								"options"		=> []
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') CLABE: @endcomponent
						@component('components.inputs.input-text',
							[
								"attributeEx" 	=> "type=\"text\" placeholder=\"Ingrese una CLABE\" data-validation=\"clabe\"",
								"classEx"		=> "clabe"
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Cuenta: @endcomponent
						@component('components.inputs.input-text',
							[
								"attributeEx" 	=> "type=\"text\" placeholder=\"Ingrese una cuenta bancaria\" data-validation=\"cuenta\"",
								"classEx"		=> "account"
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Tarjeta: @endcomponent
						@component('components.inputs.input-text',
							[
								"attributeEx" 	=> "type=\"text\" placeholder=\"Ingrese una tarjeta\" data-validation=\"tarjeta\"",
								"classEx"		=> "card"
							])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Sucursal: @endcomponent
						@component('components.inputs.input-text',
							[
								"attributeEx" 	=> "type=\"text\" placeholder=\"Ingrese una sucursal\"",
								"classEx"		=> "branch_office"
							])
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', 
							[
								"variant"		=> "warning",
								"attributeEx" 	=> "type=\"button\" id=\"add-bank\"",
								"label"			=> "<span class=\"icon-plus\"></span> Agregar"
							])
						@endcomponent
					</div>
				@endcomponent
				@php
					$body 		= [];
					$modelBody	= [];
					$modelHead 	= [ "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción" ];

					foreach($nominaemployee->employee()->first()->bankData->where('visible',1)->where('type',1) as $b) 
					{
						$body = [ "classEx" => "tr_body",
							[
								"content" => 
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx" 	=> "type=\"hidden\" value=\"".$b->id."\"",
										"classEx"		=> "idbank",
									],
									[
										"kind"		=> "components.labels.label",
										"label" 	=> $b->alias
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"label" 	=> $b->bank->description,
										"classEx"	=> "validate_bank"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"label" 	=> isset($b->clabe) ? $b->clabe : '---',
										"classEx"	=> "validate_clabe"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$b->clabe."\"",
										"classEx"		=> "modal_clabe"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"label" 	=> isset($b->account) ? $b->account : '---' ,
										"classEx"	=> "validate_account"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$b->account."\"",
										"classEx"		=> "modal_account"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"label" 	=> isset($b->cardNumber) ? $b->cardNumber : '---',
										"classEx"	=> "validate_card"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$b->cardNumber."\"",
										"classEx"		=> "modal_cardNumber"
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"	=> "components.labels.label",
										"label" => isset($b->branch) ? $b->branch : '---'
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx" 	=> "type=\"button\"",
										"classEx"		=> "delete-bank",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						];
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 		=> $modelBody,
					"modelHead" 		=> $modelHead,
					"classExBody"		=> "body_content",
					"attributeExBody" 	=> "id=\"banks-body\""
				])
				@endcomponent 
			</div>
			<div class="flex justify-center">
				@if ($nominaemployee->workerData->first()->alimonyDiscount != '')
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox"
							checked="checked"
							disabled="disabled"
							id="alimony"
							name="alimony"
						@endslot
						@slot('classEx')
							disabled
						@endslot
						Pensión Alimenticia
					@endcomponent
				@else
					@component('components.inputs.switch')
						@slot('attributeEx')
							type="checkbox"
							disabled="disabled"
							id="alimony"
							name="alimony"
						@endslot
						@slot('classEx')
							disabled
						@endslot
						Pensión Alimenticia
					@endcomponent
				@endif
			</div>
			<div class="tbody_alimony mb-4 @if($nominaemployee->workerData->first()->alimonyDiscount == '') hidden @endif">
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= ["Tipo de descuento", "Descuento"];

					$optionAlimony = [];
					if($nominaemployee->workerData->first()->alimonyDiscountType == 1) 
					{
						$optionAlimony[] = ["value" => "1", "description" => "Monto", "selected" => "selected"];
					}
					else
					{
						$optionAlimony[] = ["value" => "1", "description" => "Monto"];
					}
					if($nominaemployee->workerData->first()->alimonyDiscountType == 2) 
					{
						$optionAlimony[] = ["value" => "2", "description" => "Porcentaje", "selected" => "selected"];
					}
					else
					{
						$optionAlimony[] = ["value" => "2", "description" => "Porcentaje"];
					}
					$body = [
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.select",
									"attributeEx"	=> "disabled=\"disabled\" multiple=\"multiple\" name=\"work_alimony_discount_type\" data-validation=\"required\"",
									"classEx"		=> "disabled js-alimony-discount-type",
									"options"		=> $optionAlimony
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el descuento\" disabled name=\"work_alimony_discount\" data-validation=\"number required\" data-validation-allowing=\"float\" value=\"".$nominaemployee->workerData->first()->alimonyDiscount."\"",
									"classEx"		=> "disabled"
								]
							]
						]
					];
					$modelBody[] = $body;
				@endphp	
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" => $modelBody,
					"modelHead"	=> $modelHead
				])
				@endcomponent
				<div id="accounts-alimony" class="@if($nominaemployee->workerData->first()->alimonyDiscount != '') block  @else hidden @endif">
					@component('components.labels.title-divisor') CUENTAS BANCARIAS DEL BENEFICIARIO @endcomponent
					@component('components.containers.container-form')
						@slot("classEx")
							tr_bank_alimony
						@endslot
						@slot("attributeEx")
							id="bank-data-register-alimony"
						@endslot
						<div class="col-span-2">
							@component('components.labels.label') Beneficiario: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese un beneficiario\"",
									"classEx"		=> "beneficiary disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Alias: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese un alias\"",
									"classEx"		=> "alias_alimony disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Banco: @endcomponent
							@component('components.inputs.select', 
								[
									"attributeEx"	=> "multiple=\"multiple\" disabled=\"disabled\"",
									"classEx"		=> "bank disabled",
									"options"		=> []
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') CLABE: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese una CLABE\" data-validation=\"clabe\"",
									"classEx"		=> "clabe_alimony disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Cuenta: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese una cuenta bancaria\" data-validation=\"cuenta\"",
									"classEx"		=> "account_alimony disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Tarjeta: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese una tarjeta\" data-validation=\"tarjeta\"",
									"classEx"		=> "card_alimony disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Sucursal: @endcomponent
							@component('components.inputs.input-text',
								[
									"attributeEx" 	=> "type=\"text\" disabled=\"disabled\" placeholder=\"Ingrese una sucursal\"",
									"classEx"		=> "branch_office disabled"
								])
							@endcomponent
						</div>
						<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
							@component('components.buttons.button', 
								[
									"variant"		=> "warning",
									"classEx"		=> "disabled",
									"attributeEx" 	=> "type=\"button\" disabled=\"disabled\" id=\"add-bank-alimony\"",
									"label"			=> "<span class=\"icon-plus\"></span> Agregar"
								])
							@endcomponent
						</div>
					@endcomponent
					@php
						$body 		= [];
						$modelBody 	= [];
						$modelHead 	= ["Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción" ];
						
						foreach($nominaemployee->employee()->first()->bankData->where('visible',1)->where('type',2) as $b) 
						{
							$body = [ "classEx" => "tr_body",
								[
									"content" =>
									[
										[
											"kind"	=> "components.labels.label",
											"label" => $b->beneficiary
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$b->id."\"",
											"classEx"		=> "idbank"
										],
										[
											"kind"	=> "components.labels.label",
											"label" => $b->alias
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"	=> "components.labels.label",
											"label" => $b->bank->description
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"	=> "components.labels.label",
											"label" => isset($b->clabe) ? $b->clabe : '---' 
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"	=> "components.labels.label",
											"label" => isset($b->account) ? $b->account : '---'
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"	=> "components.labels.label",
											"label" => isset($b->cardNumber) ? $b->cardNumber : '---'
										]
									] 
								],
								[
									"content" => 
									[
										[
											"kind"	=> "components.labels.label",
											"label" => isset($b->branch) ? $b->branch : '---'
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "red",
											"attributeEx" 	=> "disabled=\"disabled\" type=\"button\"",
											"classEx"		=> "delete-bank disabled",
											"label"			=> "<span class=\"icon-x\"></span>"
										]
									]
								]
							]; 
							$modelBody[] = $body;				
						}
					@endphp
					@component('components.tables.alwaysVisibleTable', [
						"modelBody" 	=> $modelBody,
						"modelHead" 	=> $modelHead,
						"classExBody"	=> "body_content_alimony"
					])
					@endcomponent 
				</div>
			</div>
			<div id="div-delete"></div>
		</form>
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="idemployee" value="{{ $nominaemployee->idrealEmployee }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="idworkingData" value="{{ $nominaemployee->idworkingData }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="idnominaEmployee" value="{{ $nominaemployee->idnominaEmployee }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="folio" value="{{ $folio }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="action" value="{{ $nominaemployee->nominasEmployeeNF()->exists() ? 'update' : 'new' }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type="hidden" name="idnominaemployeenf" value="{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf : null }}"
			@endslot
		@endcomponent
		<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', ["variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="senddata"
				@endslot
				@slot('label')
					<span class="icon-check"></span> <span>Actualizar</span>
				@endslot
			@endcomponent
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
					type="button" title="Cerrar" data-dismiss="modal"
				@endslot
				@slot('classEx')
					exit
				@endslot
				@slot('label')
					<span class="icon-x"></span> <span>Cerrar</span>
				@endslot
			@endcomponent
		</div>
	</div>
@endcomponent 

<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		$('input[name="cp"]').numeric({ negative:false});
		$('input[name="work_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_net_income"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_nomina"]').numeric({negative:false});
		$('input[name="work_bonus"]').numeric({negative:false});
		$('input[name="work_infonavit_credit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_infonavit_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('.clabe,.account,.card, .clabe_alimony, .account_alimony, .card_alimony').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_alimony_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});

		@php
			$selects = collect([
				[
					"identificator"				=> ".js-work-subdepartment",
					"placeholder"				=> "Seleccione el subdepartamento"
				],
				[
					"identificator"				=> ".js-imss",
					"placeholder"				=> "Seleccione el status imss",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-work-employee",
					"placeholder"				=> "Seleccione el tipo de trabajo",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-regime",
					"placeholder"				=> "Seleccione el régimen",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-status",
					"placeholder"				=> "Seleccione un estatus",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-periodicity",
					"placeholder"				=> "Seleccione la periocidad",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-work-payment",
					"placeholder"				=> "Seleccione la forma de pago",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-alimony-discount-type",
					"placeholder"				=> "Seleccione el tipo de descuento",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-infonavit-discount-type",
					"placeholder"				=> "Seleccione el tipo de descuento",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects', [ "selects" => $selects ]) @endcomponent
		generalSelect({'selector' : '[name="work_account"]', 'depends' : '[name="work_enterprise"]', 'model' : 4});
		generalSelect({'selector' : '[name="work_employer_register"]', 'depends' : '[name="work_enterprise"]', 'model' : 47});
		generalSelect({'selector' : '.js-state', 'model' : 31});
		$('#alimony').on('change',function()
		{
			if($(this).is(':checked'))
			{
				$(this).parents('div').find('.tbody_alimony').stop(true,true).fadeIn();
				$('#accounts-alimony').stop(true,true).fadeIn();
				@php
					$selects = collect([
						[
							"identificator"				=> '[name="work_alimony_discount_type"]',
							"placeholder"				=> "Seleccione tipo de descuento",
							"maximumSelectionLength"	=> "1"
						]
					]);
				@endphp
				@component('components.scripts.selects',['selects' => $selects]) @endcomponent
				generalSelect({'selector': '.bank', 'model': 28});
			}
			else
			{
				$(this).parents('div').find('.tbody_alimony').stop(true,true).fadeOut();
				$('#accounts-alimony').stop(true,true).fadeOut();
			}
		});
	});
</script>
