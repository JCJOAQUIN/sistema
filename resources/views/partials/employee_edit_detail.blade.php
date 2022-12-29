@component('components.labels.title-divisor') DETALLES DE EMPLEADO @endcomponent
@component('components.inputs.input-text')
	@slot('attributeEx')
		type="hidden" name="idemployee" value="{{ $employee->id }}"
	@endslot
@endcomponent
@component('components.inputs.input-text')
	@slot('attributeEx')
		type="hidden" name="idworkingData" value="{{ $employee->workerData->where('visible',1)->first()->id }}"
	@endslot
@endcomponent
@component("components.containers.container-form")
	<div class="col-span-2">
		<div class="mb-4">
			@component('components.labels.label') Nombre(s): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el nombre" 
					name="name"
					data-validation="required" 
					value="{{ $employee->name }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Apellido Paterno: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el apellido" 
					name="last_name"
					data-validation="required" 
					value="{{ $employee->last_name }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Apellido Materno (opcional): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el apellido"
					type="text"
					name="scnd_last_name"
					value="{{ $employee->scnd_last_name }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') CURP: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el CURP" 
					name="curp"
					data-validation="custom required"
					data-validation-regexp="^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$"
					data-validation-error-msg="Por favor, ingrese un CURP válido"
					value="{{ $employee->curp }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') RFC: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el RFC con homoclave"
					name="rfc"
					data-validation="server"
					data-validation-url="{{url('configuration/employee/rfc')}}" 
					@isset($employee->rfc) data-validation-req-params="{{ json_encode(array('oldRfc'=>$employee->rfc)) }}" @endisset
					value="{{ $employee->rfc }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') #IMSS: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el número de IMSS"
					name="imss" 
					data-validation="custom"
					data-validation-regexp="^(\d{10}-\d{1})$"
					data-validation-error-msg="Por favor, ingrese un # IMSS válido"
					data-validation-optional="true"
					value="{{ $employee->imss }}" 
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Correo electrónico: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el correo electrónico"
					name="email"
					data-validation="email"
					value="{{ $employee->email }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Número teléfonico: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el número teléfonico" type="text" name="phone" data-validation="phone required" value="{{ $employee->phone }}"
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
					name="street"
					data-validation="required"
					value="{{ $employee->street }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">	 
			@component('components.labels.label') Número: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="number"
					placeholder="Ingrese un número"
					data-validation="required" 
					value="{{ $employee->number }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">	 
			@component('components.labels.label') Colonia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="colony"
					placeholder="Ingrese la colonia"
					data-validation="required" 
					value="{{ $employee->colony }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Código Postal: @endcomponent
			@php
				$optionCP = [];
				if(isset($employee->cp) && $employee->cp != "")
				{
					$optionCP[] = ["value" => $employee->cp, "description" => $employee->cp, "selected" => "selected"];
				}
				else
				{
					$optionCP[] = ["value" => $employee->cp, "description" => $employee->cp];
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
					name="city"
					placeholder="Ingrese la ciudad"
					data-validation="required" 
					value="{{ $employee->city }}"
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">	 
			@component('components.labels.label') Estado: @endcomponent
			@php
				$e				= App\State::where('idstate',$employee->state_id)->first();
				$optionState	= [];
				 
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
		<span class="help-btn" id="help-btn-edit-employee"></span>
	@endcomponent
</div>
@component('components.containers.container-form')
	<div class="col-span-2">
		<div class="mb-4">	 
			@component('components.labels.label') Estado: @endcomponent
			@php
				$optionWork = [];
				$e			= App\State::where('idstate',$employee->workerData->where('visible',1)->first()->state)->first();

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
					disabled
					js-state
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Proyecto: @endcomponent
			@php
				$project = App\Project::orderName()->where('status',1)
					->where('idproyect',$employee->workerData->where('visible',1)->first()->project)
					->first();

				$optionWorkProject = [];
				if($employee->workerData->where('visible',1)->first()->project == $project->idproyect) 
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
			<div class="select_father @if($employee->workerData->where('visible',1)->first()->employeeHasWbs()->exists() || $employee->workerData->where('visible',1)->first()->projects->codeWBS()->exists()) block @else hidden @endif">
				@component('components.labels.label') WBS: @endcomponent
				@php
					$optionWorkWbs = [];
					foreach(App\CatCodeWBS::where('project_id',$employee->workerData->where('visible',1)->first()->project)->get() as $wbs)
					{
						if(isset($employee) && count($employee->workerData->where('visible',1))>0 && in_array($wbs->id, json_decode(json_encode($employee->workerData->where('visible',1)->first()->employeeHasWbs->pluck('id')),true)))
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
					if ($employee->workerData->where('visible',1)->first()->enterprise == $enterprise->id) 
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
				$a = App\Account::where('idEnterprise',$employee->workerData->where('visible',1)->first()->enterprise)
					->where('idAccAcc',$employee->workerData->where('visible',1)->first()->account)
					->where(function($q)
					{ 
						$q->where('account','LIKE','5102%')
						->orWhere('account','LIKE','5303%')
						->orWhere('account','LIKE','5403%');
					})
					->where('selectable',1)
					->first();
				
				$optionWorkAccount = [];
				if($employee->workerData->where('visible',1)->first()->account == $a->idAccAcc) 
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
					foreach($employee->workerData->where('visible',1)->first()->places as $p) 
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
					if($employee->workerData->where('visible',1)->first()->direction == $area->id)
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
					if($employee->workerData->where('visible',1)->first()->department == $area->id)
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
					if(isset($employee) && count($employee->workerData->where('visible',1))>0 && in_array($subdepartment->id, json_decode(json_encode($employee->workerData->where('visible',1)->first()->employeeHasSubdepartment->pluck('id')),true)))
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
					value="{{ $employee->workerData->where('visible',1)->first()->position }}"
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
					value="{{ $employee->workerData->where('visible',1)->first()->immediate_boss }}"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@php
				$newDateAdmission = $employee->workerData->where('visible',1)->first()->admissionDate != '' ? $employee->workerData->where('visible',1)->first()->admissionDate->format('d-m-Y') : ''; 
			@endphp
			@component('components.labels.label') Fecha de ingreso: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la fecha" 
					type="text" 
					name="work_income_date"
					disabled="disabled" 
					readonly="readonly"
					data-validation="required"
					value="{{ $newDateAdmission }}"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@component('components.labels.label') Estatus IMSS: @endcomponent
			@php
				$optionImss = [];
				if($employee->workerData->where('visible',1)->first()->status_imss == 0)
				{
					$optionImss[] = ["value" => "0", "description" => "Inactivo", "selected" => "selected"];
				}
				else
				{
					$optionImss[] = ["value" => "0", "description" => "Inactivo"];
				}
				if($employee->workerData->where('visible',1)->first()->status_imss == 1)
				{
					$optionImss[] = ["value" => "1", "description" => "Activo", "selected" => "selected"];
				}
				else
				{
					$optionImss[] = ["value" => "1", "description" => "Activo"];
				}
			@endphp
			@component('components.inputs.select',[ "options" => $optionImss])
				@slot('attributeEx')
					multiple
					name="work_status_imss"
					disabled="disabled"
				@endslot
				@slot('classEx')
					laboral-data
					js-imss
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@php
				$newDateImss = $employee->workerData->where('visible',1)->first()->imssDate != '' ? $employee->workerData->where('visible',1)->first()->imssDate->format('d-m-Y') : '';
			@endphp
			@component('components.labels.label') Fecha de alta IMSS (si aplica): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la fecha"
					type="text"
					name="work_imss_date"
					disabled="disabled" 
					readonly="readonly" 
					value="{{ $newDateImss }}"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@php
				$newDateDown = $employee->workerData->where('visible',1)->first()->downDate != '' ? $employee->workerData->where('visible',1)->first()->downDate->format('d-m-Y') : '';
			@endphp
			@component('components.labels.label') Fecha de baja (si aplica): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la fecha"
					type="text"
					name="work_down_date"
					disabled="disabled"
					readonly="readonly"
					value="{{ $newDateDown }}"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
		<div class="mb-4">
			@php
				$newDateEnding = $employee->workerData->where('visible',1)->first()->endingDate != '' ? $employee->workerData->where('visible',1)->first()->endingDate->format('d-m-Y') : '';
			@endphp
			@component('components.labels.label') Fecha de término de relación laboral (si aplica): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la fecha"
					type="text"
					name="work_ending_date"
					readonly="readonly"
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
				$newDateReentry = $employee->workerData->where('visible',1)->first()->reentryDate != '' ? $employee->workerData->where('visible',1)->first()->reentryDate->format('d-m-Y') : '';
			@endphp 
			@component('components.labels.label') Reingreso (si aplica): @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el reingreso"
					type="text"
					name="work_reentry_date"
					disabled="disabled"
					readonly="readonly"
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
					if($employee->workerData->where('visible',1)->first()->workerType==$contract->id)
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
					if($employee->workerData->where('visible',1)->first()->regime_id==$regime->id)
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
				if($employee->workerData->where('visible',1)->first()->workerStatus == 1)
				{
					$optionStatus[] = ["value" => "1", "description" => "Activo", "selected" => "selected"];
				}
				else
				{
					$optionStatus[] = ["value" => "1", "description" => "Activo"];
				}
				if($employee->workerData->where('visible',1)->first()->workerStatus == 2)
				{
					$optionStatus[] = ["value" => "2", "description" => "Baja pacial", "selected" => "selected"];
				}
				else
				{
					$optionStatus[] = ["value" => "2", "description" => "Baja pacial"];
				}
				if($employee->workerData->where('visible',1)->first()->workerStatus == 3)
				{
					$optionStatus[] = ["value" => "3", "description" => "Baja definitiva", "selected" => "selected"];
				}
				else
				{
					$optionStatus[] = ["value" => "3", "description" => "Baja definitiva"];
				}
				if($employee->workerData->where('visible',1)->first()->workerStatus == 4)
				{
					$optionStatus[] = ["value" => "4", "description" => "Suspensión", "selected" => "selected"];
				}
				else
				{
					$optionStatus[] = ["value" => "4", "description" => "Suspensión"];
				}
				if($employee->workerData->where('visible',1)->first()->workerStatus == 5)
				{
					$optionStatus[] = ["value" => "5", "description" => "Boletinado", "selected" => "selected"];
				}
				else
				{
					$optionStatus[] = ["value" => "5", "description" => "Boletinado"];
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
					value="{{ $employee->workerData->where('visible',1)->first()->sdi }}"
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
					if($employee->workerData->where('visible',1)->first()->periodicity == $per->c_periodicity)
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
				if(isset($employee) && count($employee->workerData)>0)
				{	
					$ers = App\EmployerRegister::where('enterprise_id',$employee->workerData->where('visible',1)->first()->enterprise)->get(); 
					foreach ($ers as $er)
					{
						if($employee->workerData->where('visible',1)->first()->employer_register == $er->employer_register)
						{
							$optionEmployerRegister[] = ["value" => $er->employer_register, "description" => $er->employer_register, "selected" => "selected"];
						}
					}
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionEmployerRegister])
				@slot('attributeEx')
					name="work_employer_register"
					multiple
					data-validation="required"
					@if(isset($employee)) disabled @endif
				@endslot
				@slot('classEx')
					laboral-data 
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
					if(isset($employee) && count($employee->workerData)>0 && $employee->workerData->where('visible',1)->first()->paymentWay==$pay->idpaymentMethod)
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
					@if(isset($employee)) disabled @endif
					multiple
				@endslot
				@slot('classEx')
					laboral-data
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
					data-validation="number"
					data-validation-allowing="float"
					value="{{ $employee->workerData->where('visible',1)->first()->netIncome }}"
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
					value="{{ $employee->workerData->where('visible',1)->first()->complement }}"
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
					value="{{ $employee->workerData->where('visible',1)->first()->fonacot }}"
				@endslot
				@slot('classEx')
					disabled
				@endslot
			@endcomponent
		</div>
	</div>
@endcomponent
<div class="mb-4">
	@php
		$body 		= [];
		$modelBody	= [];
		$modelHead	= [ "Empresa Anterior", "Fecha de Ingreso" ];
	
		$optionEnterprise = [];
		foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
		{
			if(isset($employee) && count($employee->workerData->where('visible',1))>0 && $employee->workerData->where('visible',1)->first()->enterpriseOld==$enterprise->id)
			{
				$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"];
			}
			else
			{
				$optionEnterprise[] = ["value" => $enterprise->id, "description" => strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
			}
		}
		$newDateAd = '';
		if(isset($employee) && count($employee->workerData->where('visible',1))>0)
		{
			$newDateAd	= $employee->workerData->where('visible',1)->first()->admissionDateOld != '' ?  $employee->workerData->where('visible',1)->first()->admissionDateOld->format('d-m-Y') : '';
		} 
		$varClass = '';
		if(isset($employee))
		{
			$varClass = "disabled";
		} 
		
		$body = [
			[
				"content" => 
				[
					[
						"kind"			=> "components.inputs.select",
						"attributeEx"	=> "name=\"work_enterprise_old\" multiple=\"multiple\" data-validation=\"required\"",
						"options"		=> $optionEnterprise
					]
				]
			],
			[
				"content" =>
				[
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "placeholder=\"Ingrese la fecha\" type=\"text\" readonly=\"readonly\" name=\"work_income_date_old\" data-validation=\"required\" data-validation-allowing=\"range[0;100]\" value=\"".$newDateAd."\""." ".$varClass,
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
		$modelHead	= [ "Porcentaje de nómina", "Porcentaje de bonos"];
	
		$body = [
			[
				"content" => 
				[
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "placeholder=\"Ingrese el porcentaje\" type=\"text\" name=\"work_nomina\" disabled=\"disabled\" data-validation=\"number required\" value=\"".$employee->workerData->where('visible',1)->first()->nomina."\"",
						"classEx"		=> "disabled"
					]
				]
			],
			[
				"content" =>
				[
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "placeholder=\"Ingrese el porcentaje\" type=\"text\" name=\"work_bonus\" disabled=\"disabled\" data-validation=\"number required\" value=\"".$employee->workerData->where('visible',1)->first()->bono."\"",
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
<div class="text-center mb-4">
	@if($employee->workerData->where('visible',1)->first()->infonavitCredit != '') 
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
<div class="tbody mb-4 @if($employee->workerData->where('visible',1)->first()->infonavitCredit == '') hidden @endif">
	@php
		$body 		= [];
		$modelBody 	= [];
		$modelHead	= 
		[
			[
				["value" => "Número de crédito"],
				["value" => "Descuento"],
				["value" => "Tipo de descuento"]
			]
		];
		$optionInfonavit = [];
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 1)
		{
			$optionInfonavit[] = ["value" => "1", "description" => "VSM (Veces Salario Mínimo)", "selected" => "selected"];
		}
		else
		{
			$optionInfonavit[] = ["value" => "1", "description" => "VSM (Veces Salario Mínimo)"];
		}
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 2)
		{
			$optionInfonavit[] = ["value" => "2", "description" => "Cuota fija", "selected" => "selected"];
		}
		else
		{
			$optionInfonavit[] = ["value" => "2", "description" => "Cuota fija"];
		}
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 3)
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
					"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el crédito\" disabled=\"disabled\" name=\"work_infonavit_credit\" data-validation=\"required\" value=\"".$employee->workerData->where('visible',1)->first()->infonavitCredit."\"",
					"classEx"		=> "disabled"
				]
			],
			[
				"content" => 
				[
					"kind" 			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el descuento\" disabled=\"disabled\" name=\"work_infonavit_discount\" data-validation=\"number required\" data-validation-allowing=\"float\" value=\"".$employee->workerData->where('visible',1)->first()->infonavitDiscount."\"",
					"classEx"		=> "disabled"
				]
			],
			[
				"content" => 
				[
					"kind"			=> "components.inputs.select",
					"attributeEx"	=> "name=\"work_infonavit_discount_type\" disabled=\"disabled\" multiple=\"multiple\" data-validation=\"required\"",
					"options"		=> $optionInfonavit,
					"classEx"		=> "js-infonavit disabled"
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
		@slot("attributeEx")
			id="bank-data-register"
		@endslot
		@slot("classEx")
			tr_bank
		@endslot
		<div class="col-span-2">
			@component('components.labels.label') Alias: @endcomponent
			@component('components.inputs.input-text',
				[
					"attributeEx" 	=> "placeholder=\"Ingrese un alias\"",
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
					"label"			=> "<span class=\"icon-plus\"></span> <span>Agregar</span>"
				])
			@endcomponent
		</div>
	@endcomponent
	@php
		$body 		= [];
		$modelBody	= [];
		$modelHead 	= [ "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal", "Acción" ];
		
		foreach($employee->bankData->where('visible',1) as $b) 
		{
			$body = 
			[	"classEx" => "tr_body",
				[
					"content" => 
					[
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" value=\"".$b->id."\"",
							"classEx"		=> "idbank",
						],
						[
							"label" 	=> $b->alias
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $b->bank->description
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => isset($b->clabe) ? $b->clabe : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => isset($b->account) ? $b->account : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => isset($b->cardNumber) ?  $b->cardNumber : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
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
							"classEx"		=> "btn delete-bank",
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
		"classExBody"	=> "body_content",
	])
	@endcomponent 
</div>
<div id="div-delete"></div>
<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
	@component('components.buttons.button', ["variant" => "primary"])
		@slot('attributeEx')
			type="button" title="Actualizar"
		@endslot
		@slot('classEx')
			update-employee
		@endslot
		@slot('label')
			<span class="icon-check"> </span> <span>Actualizar</span>
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

<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		generalSelect({'selector' : '[name="work_account"]', 'depends' : '[name="work_enterprise"]', 'model' : 4});
		generalSelect({'selector' : '[name="work_employer_register"]', 'depends' : '[name="work_enterprise"]', 'model' : 47});
		generalSelect({'selector' : '.js-state', 'model' : 31});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-imss",
					"placeholder"				=> "Seleccione el status de IMSS",
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
					"identificator"				=> ".js-work-subdepartment",
					"placeholder"				=> "Seleccione el subdepartamento"
				],
				[
					"identificator"				=> ".js-infonavit",
					"placeholder"				=> "Seleccione el tipo de descuento",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects', [ "selects" => $selects ]) @endcomponent
		$('input[name="cp"]').numeric({ negative:false});
		$('input[name="work_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_net_income"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_nomina"]').numeric({negative:false});
		$('input[name="work_bonus"]').numeric({negative:false});
		$('input[name="work_infonavit_credit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_infonavit_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('.clabe,.account,.card').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="work_alimony_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
	});
</script>
