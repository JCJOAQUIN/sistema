@component('components.labels.title-divisor') 
	INFORMACIÓN GENERAL
@endcomponent
@if(isset($employee_config) && !$employee_config)
	@component("components.labels.subtitle") Para {{ (isset($employee)) ? "editar el empleado" : "agregar un empleado nuevo" }} es necesario colocar los siguientes campos: @endcomponent
@endif
@if(isset($new_rq) || isset($employee_new))
	@component("components.containers.container-form")
	<input type="hidden" name="employee_id" value="x">
@else
	@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""])
@endif
		<div class="col-span-2">
			@component('components.labels.label') 
				Nombre(s):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el nombre" 
					type="text" 
					name="name" 
					data-validation="length required" 
					data-validation-length="min2" 
					@if(isset($employee)) value="{{$employee->name}}" @endif  
					@if(isset($employee_edit)) value="{{$employee_edit->name}}" @endif 
					@if(isset($request) && $request->status != 2)  disabled="disabled" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Apellido Paterno:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el apellido paterno" 
					type="text" 
					name="last_name" 
					data-validation="length required" 
					data-validation-length="min2" 
					@if(isset($employee)) value="{{$employee->last_name}}" @endif 
					@if(isset($employee_edit)) value="{{$employee_edit->last_name}}" @endif 
					@if(isset($request) && $request->status != 2)  disabled="disabled" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Apellido Materno (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el apellido materno" 
					type="text" 
					name="scnd_last_name" 
					@if(isset($employee)) value="{{$employee->scnd_last_name}}" @endif 
					@if(isset($employee_edit)) value="{{$employee_edit->scnd_last_name}}" @endif 
					@if(isset($request) && $request->status != 2)  disabled="disabled" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				CURP:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el CURP" 
					type="text" 
					name="curp" 
					@if(isset($employee_new))
						data-validation="server" data-validation-url="{{route('requisition.employee.curp-validate')}}" 
						@if(isset($request))
							data-validation-req-params="{{json_encode(array('folio'=>$request->folio))}}" 
						@endif 
					@endif 
					@if(isset($employee)) 
						value="{{$employee->curp}}" data-validation="server" data-validation-req-params="{{json_encode(array('oldCurp'=>$employee->curp))}}" data-validation-url="{{route('employee.curp')}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->curp}}" data-validation="server" data-validation-url="{{route('administration.employee.validate-curp')}}" 
					@endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
					@if(!isset($employee) && !isset($employee_edit) && !isset($request) && !isset($employee_new))
						data-validation="server" data-validation-url="{{route('employee.curp')}}"
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				RFC @if(!isset($new_rq)) (Opcional) @endisset
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el RFC" 
					type="text" name="rfc"
					data-validation="server"
					@if(isset($new_rq)) 
						data-validation-url="{{route('requisition.employee.rfc-validate')}}" 
					@else
						data-validation-url="{{route('employee.rfc')}}"
					@endif
					@if(isset($request))  
						data-validation-req-params="{{json_encode(array('folio'=>$request->folio))}}" 
					@endif
					@if(isset($employee)) 
						value="{{$employee->rfc}}"  data-validation="server" data-validation-req-params="{{json_encode(array('oldRfc'=>$employee->rfc))}}"
					@endif
					@if(isset($employee_edit)) 
						value="{{$employee_edit->rfc}}" data-validation="server" data-validation-url="{{route('administration.employee.validate-rfc')}}" 
					@endif 
					@if(isset($request) && $request->status != 2)  disabled="disabled" @endif
					@if(!isset($employee) && !isset($employee_edit) && !isset($request) && !isset($employee_new))
						data-validation="server"  data-validation-url="{{route('employee.rfc')}}" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Régimen fiscal:
			@endcomponent
			@php
				$options = collect();
				foreach(App\CatTaxRegime::where('physical','Sí')->get() as $regime)
				{
					if(isset($employee) && $employee->tax_regime == $regime->taxRegime || isset($employee_edit) && $employee_edit->tax_regime == $regime->taxRegime)
					{
						$options = $options->concat([['value'=>$regime->taxRegime, 'selected'=>'selected', 'description'=>$regime->taxRegime." - ".$regime->description]]);
					}
					else
					{
						$options = $options->concat([['value'=>$regime->taxRegime, 'description'=>$regime->taxRegime." - ".$regime->description]]);
					}
				}
				$attributeEx = "id=\"tax_regime\" name=\"tax_regime\"";
				if(isset($new_rq) || isset($employee_new)) 
				{
					$attributeEx = $attributeEx." data-validation=\"required\"";
				}
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				# IMSS (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el número de seguro social" 
					name="imss" 
					data-validation="custom" 
					data-validation-regexp="^(\d{10}-\d{1})$" 
					data-validation-error-msg="Por favor, ingrese un # IMSS válido" 
					data-validation-optional="true" 
					@if(isset($employee)) 
						value="{{$employee->imss}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->imss}}" 
					@endif
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Correo electrónico:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el correo electrónico" 
					name="email" 
					@if(isset($employee)) 
						value="{{$employee->email}}" data-validation="server" data-validation-url="{{route('employee.email')}}" data-validation-req-params="{{json_encode(array('oldEmail'=>$employee->email))}}"
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->email}}"
					@endif
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
					@if(!isset($employee) && !isset($employee_edit) && !isset($request) && !isset($employee_new))
						data-validation="server" data-validation-url="{{route('employee.email')}}"
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Número telefónico: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					placeholder="Ingrese el número telefónico" 
					type="text" name="phone" 
					data-validation="phone required" 
					@if(isset($employee)) value="{{$employee->phone}}" @endif 
					@if(isset($employee_edit)) value="{{$employee_edit->phone}}" @endif
					@if(isset($request) && $request->status != 2)  disabled="disabled" @endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Calle:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la calle" 
					name="street" 
					data-validation="length required" 
					data-validation-length="max100" 
					@if(isset($employee)) 
						value="{{$employee->street}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->street}}" 
					@endif
					@if(isset($request) && $request->status != 2) 
						disabled="disabled" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Número:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					@if(isset($new_rq) || isset($employee_new)) 
						name="number_employee" 
					@else 
						name="number" 
					@endif 
					placeholder="Ingrese el número" 
					data-validation-length="max45" 
					data-validation="required length" 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->number}}" 
					@endif
					@if(isset($employee)) 
						value="{{$employee->number}}" 
					@endif
					@if(isset($request) && $request->status != 2) 
						disabled="disabled" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Colonia:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					@if(isset($employee_edit)) 
						value="{{$employee_edit->colony}}" 
					@endif
					placeholder="Ingrese la colonia" 
					name="colony" 
					data-validation="length required" 
					data-validation-length="max70" 
					@if(isset($employee)) 
						value="{{$employee->colony}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->colony}}"
					@endif
					@if(isset($request) && $request->status != 2) 
						disabled="disabled" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')
				CP:
			@endcomponent
			@php
				$options = collect();
				if(isset($employee) && isset($employee->cp))
				{
					$options = $options->concat([['value'=>$employee->cp, 'selected'=>'selected', 'description'=>$employee->cp]]);
				}
				elseif(isset($employee_edit) && isset($employee_edit->cp))
				{
					$options = $options->concat([['value'=>$employee_edit->cp, 'selected'=>'selected', 'description'=>$employee_edit->cp]]);
				}
				$attributeEx = "id=\"cp\" name=\"cp\" multiple=\"multiple\" data-validation=\"required\"";
				if(isset($request) && $request->status != 2)
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Ciudad:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="city" 
					placeholder="Ingrese la ciudad" 
					data-validation="required length" 
					data-validation-length="max70" 
					@if(isset($employee)) 
						value="{{$employee->city}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->city}}" 
					@endif
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Entidad:
			@endcomponent
			@php
				$options = collect();
				if(isset($employee) && isset($employee->state_id))
				{
					$e = App\State::find($employee->state_id);
					$options = $options->concat([['value'=>$e->idstate, 'selected'=>'selected', 'description'=>$e->description]]);
				}
				elseif(isset($employee_edit) && isset($employee_edit->state_id))
				{
					$e = App\State::find($employee_edit->state_id);
					$options = $options->concat([['value'=>$e->idstate, 'selected'=>'selected', 'description'=>$e->description]]);
				}
				$attributeEx = "name=\"state\" multiple data-validation=\"required\"";
				if(isset($request) && $request->status != 2)
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'removeselect'])
			@endcomponent				
		</div>
		@if(isset($new_rq) || isset($employee_edit) || isset($employee)  || isset($employee_new))
			<div class="col-span-2">
				@component('components.labels.label') 
					En Reemplazo De: (Opcional)
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="replace" 
						placeholder="Ingrese el reemplazo" 
						data-validation-length="max70" 
						@if(isset($employee_edit)) 
							value="{{$employee_edit->replace}}" 
						@elseif(isset($employee)) 
							value="{{$employee->replace}}"
						@endif
						@if(isset($request) && $request->status != 2)  
							disabled="disabled" 
						@endif
					@endslot
				@endcomponent
			</div>
			@if(isset($new_rq) || isset($employee_edit) || isset($employee_new))
				<div class="col-span-2">
					@component('components.labels.label') 
						¿Requiere Equipo de Cómputo?:
					@endcomponent
					@php
						$options = collect();
						$values = ["0" => "No", "1" => "Sí"];

						foreach($values as $key => $value)
						{
							if(isset($employee_edit) && $employee_edit->computer_required == $key)
							{
								$options = $options->concat([["value" => $key, "description" => $value, "selected" => "selected"]]);
							}
							else
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						}
						$attributeEx = "name=\"computer_required\" multiple data-validation=\"required\"";
						if(isset($request) && $request->status != 2)
						{
							$attributeEx = $attributeEx." disabled=\"disabled\"";
						} 
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'removeselect'])
					@endcomponent
				</div>
			@endif
			<div class="col-span-2">
				@component('components.labels.label') 
					¿Personal calificado?
				@endcomponent
				<div class="flex p-0 space-x-2">
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							name="qualified_employee" id="no_qualified" value="0" 
							@if((isset($employee_edit) && $employee_edit->qualified_employee == 0) || (isset($employee) && $employee->qualified_employee == 0)) checked="checked" @endif
						@endslot
						@slot('classExContainer') inline-flex @endslot
						No
					@endcomponent
					@component('components.buttons.button-approval')
						@slot('attributeEx') 
							name="qualified_employee" id="yes_qualified" value="1" 
							@if((isset($employee_edit) && $employee_edit->qualified_employee == 1) || (isset($employee) && $employee->qualified_employee == 1)) checked="checked" @endif 
						@endslot
						@slot('classExContainer') inline-flex @endslot
						Sí
					@endcomponent
				</div>
			</div>
		@else
			<div class="col-span-2">
				@component('components.labels.label') 
					Usuario del Sistema Adglobal
				@endcomponent
				@php
					$optionSelected = "";
					if(isset($employee) && $employee != "")
					{
						$optionSelected = $employee->sys_user;
					}
					$options = collect([
						['value'=> '0', 'description'=>'No', 'selected' => (($optionSelected == 0 ? "selected" : ""))],
						['value'=> '1', 'description'=>'Sí', 'selected' => (($optionSelected == 1 ? "selected" : ""))]
					]);
					$attributeEx = "name=\"sys_user\" multiple data-validation=\"required\"";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'removeselect'])
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if(isset($new_rq) || isset($employee_edit) || isset($employee) || isset($employee_new))
		@component('components.labels.title-divisor') 
			INFORMACIÓN ADICIONAL
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') 
					Propósito básico del puesto:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="purpose" 
						data-validation="required"
						placeholder="Ingrese el próposito básico del puesto" 
						@if(isset($request) && $request->status != 2)  
							disabled="disabled"
						@endif
						@if(isset($employee_edit)) 
							value="{{$employee_edit->purpose}}" 
						@elseif(isset($employee)) 
							value="{{$employee->purpose}}" 
						@endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Requerimientos del puesto:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="requeriments" 
						placeholder="Ingrese los requerimientos del puesto" 
						@if(isset($request) && $request->status != 2)  
							disabled="disabled" 
						@endif
						@if(isset($employee_edit)) 
							value="{{$employee_edit->requeriments}}" 
						@elseif(isset($employee)) 
							value="{{$employee->requeriments}}" 
						@endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Observaciones:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="observations" 
						placeholder="Ingrese las observaciones" 
						@if(isset($request) && $request->status != 2)  
							disabled="disabled"
						@endif
						@if(isset($employee_edit)) 
							value="{{$employee_edit->observations}}" 
						@elseif(isset($employee)) 
							value="{{$employee->observations}}" 
						@endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	@component('components.labels.title-divisor') INFORMACIÓN LABORAL @endcomponent
	@if(isset($employee))
		<div class="text-center p-2 checks">
			@component('components.inputs.switch')
				@slot('attributeEx')
					id="edit_data" 
					name="edit_data" 
					value="@if($employee->workerDataVisible->count()>0){{$employee->workerDataVisible->first()->id}}@else x @endif"
				@endslot
				Habilitar edición 
			@endcomponent
		</div>
	@endif
	@component("components.containers.container-form")
		<div class="col-span-2">
			@component('components.labels.label') 
				Entidad Laboral:
			@endcomponent
			@php
				$options = collect();
				foreach(App\State::orderName()->get() as $e)
				{
					if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->state == $e->idstate)
					{
						$options = $options->concat([['value'=>$e->idstate, 'selected'=>'selected', 'description'=>$e->c_state." - ".$e->description]]);
					}
					else if(isset($employee_edit) && $employee_edit->state == $e->idstate)
					{
						$options = $options->concat([['value'=>$e->idstate, 'selected'=>'selected', 'description'=>$e->c_state." - ".$e->description]]);
					}
					else
					{
						$options = $options->concat([['value'=>$e->idstate, 'description'=>$e->c_state." - ".$e->description]]);
					}
				}
				$attributeEx = "name=\"work_state\" data-validation=\"required\"";
				if(isset($employee) || (isset($request) && $request->status != 2)) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'laboral-data removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2 @if(isset($employee_new)) hidden @endif">
			@component('components.labels.label') 
				Proyecto:
			@endcomponent
			@php
				$options = collect();
				/* general select cases
				if(isset($new_rq))
				{
					$projects = App\Project::where('status',1)->whereNotIn('idproyect',[75])->whereIn('idproyect',Auth::user()->inChargeProject(229)->pluck('project_id'))->orderBy('proyectName','asc')->get();
				}
				else
				{
					$projects = App\Project::orderName()->whereIn('status',[1,2])->get();
				}*/
				if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->project))
				{
					$project = App\Project::find($employee->workerDataVisible->first()->project);
					$options = $options->concat([['value' => $project->idproyect, 'selected' => 'selected', 'description' => $project->proyectName]]);
				} 
				else if(isset($employee_edit) && isset($employee_edit->project)) 
				{
					$project = App\Project::find($employee_edit->project);
					$options = $options->concat([['value' => $project->idproyect, 'selected' => 'selected', 'description' => $project->proyectName]]);
				}
				$attributeEx = "name=\"work_project\" multiple";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => 'laboral-data js-projects removeselect'])
			@endcomponent
		</div>
		@if(!isset($new_rq) || isset($employee_edit) || isset($employee_new))
			<div class="col-span-2 select_father select_wbs
				@if(isset($employee) && isset($employee->workerDataVisible->first()->project)) 
					@if($employee->workerDataVisible->first()->project == '' || !$employee->workerDataVisible->first()->projects->codeWBS()->exists())
						hidden
					@endif
				@else
					hidden
				@endif 
				@if(isset($employee_edit)) 
					@if(!isset($employee_edit->wbs) || $employee_edit->project == '' || !$employee_edit->projects->codeWBS()->exists())
						hidden
					@endif 
				@endif">
				@component('components.labels.label') 
					WBS:
				@endcomponent
				@php
					$options = collect();
					if(isset($employee) || isset($employee_edit))
					{
						if(isset($employee))
						{
							if(isset($employee) && count($employee->workerDataVisible) > 0 && isset($employee->workerDataVisible->first()->employeeHasWbs))
							{
								foreach($employee->workerDataVisible->first()->employeeHasWbs as $wbs)
								{
									$options = $options->concat([['value'=>$wbs->id, 'selected'=>'selected', 'description'=>$wbs->code_wbs]]);
								}
							}
							else if(isset($employee_edit) && in_array($wbs->id, $employee->wbs_id))
							{
								foreach($employee->wbs_id as $wbs)
								{
									$options = $options->concat([['value'=>$wbs->id, 'selected'=>'selected', 'description'=>$wbs->code_wbs]]);
								}
							}
						}
						else if(isset($employee_edit) && isset($employee_edit->wbs_id))
						{
							$wbs = App\CatCodeWBS::find($employee_edit->wbs_id);
							$options = $options->concat([['value'=>$wbs->id, 'selected' => 'selected', 'description'=>$wbs->code_wbs]]);
						}
					}
				
					$attributeEx = "name=\"work_wbs[]\"";
					if(isset($employee)) 
					{
						$attributeEx = $attributeEx." disabled=\"disabled\"";
					}  
					$classEx="wbs js-code_wbs multichoice laboral-data w-full removeselect"
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') 
				Empresa:
			@endcomponent
			@php
				$optionEnterprise = [];
				foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->enterprise==$enterprise->id)
					{
						$optionEnterprise[] =
						[
							"value"			=> $enterprise->id,
							"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							"selected"		=> "selected"
						];
					}
					else if(isset($employee_edit) && $employee_edit->enterprise==$enterprise->id)
					{
						$optionEnterprise[] =
						[
							"value"			=> $enterprise->id,
							"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							"selected"		=> "selected"
						];
					}
					else
					{
						$optionEnterprise[] =
						[
							"value"			=> $enterprise->id,
							"description"	=> strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name
						];
					}
				}
				$attributeEx = "name=\"work_enterprise\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect"
			@endphp
			@component('components.inputs.select', ['options' => $optionEnterprise, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Clasificación de Gasto:
			@endcomponent
			@php
				$options = collect();
				if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->account))
				{
					$account = App\Account::find($employee->workerDataVisible->first()->account);
					$options = $options->concat([['value'=>$account->idAccAcc, 'selected'=>'selected', 'description'=>$account->account." - ".$account->description." (".$account->content.")"]]);
				}
				else if(isset($employee_edit) && isset($employee_edit->account))
				{
					$account = App\Account::find($employee_edit->account);
					$options = $options->concat([['value'=>$account->idAccAcc, 'selected'=>'selected', 'description'=>$account->account." - ".$account->description." (".$account->content.")"]]);
				}
				$attributeEx = "name=\"work_account\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect"
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		@if(!isset($new_rq) && !isset($employee_new))
			<div class="col-span-2">
				@component('components.labels.label') 
					Lugar de Trabajo:
				@endcomponent
				@php
					$options = collect();
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->places)) 
					{
						foreach($employee->workerDataVisible->first()->places as $place)
						{
							$options   = $options->concat([['value'=>$place->id, 'selected'=>'selected', 'description'=>$place->place]]);
						}
					}
					$attributeEx = "name=\"work_place[]\" multiple";
					if(isset($employee)) 
					{
						$attributeEx = $attributeEx." disabled";
					}  
					$classEx="multichoice laboral-data removeselect"
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') 
				Dirección:
			@endcomponent
			@php
				$options = collect();
				foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->direction))
					{
						if($employee->workerDataVisible->first()->direction == $area->id)
						{
							$options = $options->concat([['value'=>$area->id, 'selected'=>'selected', 'description'=>$area->name]]);
						}
						else
						{
							$options = $options->concat([['value'=>$area->id, 'description'=>$area->name]]);
						}
					}
					else if(isset($employee_edit) && $employee_edit->direction==$area->id)
					{
						$options = $options->concat([['value'=>$area->id, 'selected'=>'selected', 'description'=>$area->name]]);
					}
					else
					{
						$options = $options->concat([['value'=>$area->id, 'description'=>$area->name]]);
					}
				}
				$attributeEx = "name=\"work_direction\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect"
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Departamento (Opcional):
			@endcomponent
			@php
				$options = collect();
				foreach(App\Department::orderName()->where('status','ACTIVE')->get() as $department)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->department))
					{
						if($employee->workerDataVisible->first()->department == $department->id)
						{
							$options   = $options->concat([['value'=>$department->id, 'selected'=>'selected', 'description'=>$department->name]]);
						}
						else
						{
							$options   = $options->concat([['value'=>$department->id, 'description'=>$department->name]]);
						}
					}
					else if(isset($employee_edit) && isset($employee_edit->department)) 
					{
						if($employee_edit->department == $department->id)
						{
							$options   = $options->concat([['value'=>$department->id, 'selected'=>'selected', 'description'=>$department->name]]);
						}
						else
						{
							$options   = $options->concat([['value'=>$department->id, 'description'=>$department->name]]);
						}
					}
					else
					{
						$options   = $options->concat([['value'=>$department->id, 'description'=>$department->name]]);
					}
				}
				$attributeEx = "name=\"work_department\" multiple";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect"
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Subdepartamento (Opcional):
			@endcomponent
			@php
				if(isset($new_rq) || isset($employee_new))
				{
					$attributeEx = "name=\"work_subdepartment\"";
					$classEx     = "laboral-data removeselect";
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = $attributeEx." disabled=\"disabled\"";
					} 
				}
				else
				{
					$attributeEx = "name=\"work_subdepartment[]\"";
					$classEx     = "multichoice laboral-data removeselect";
					if(isset($employee)) 
					{
						$attributeEx = $attributeEx." disabled";
					}  
				}
				$options = collect();
				if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->employeeHasSubdepartment))
				{
					foreach($employee->workerDataVisible->first()->employeeHasSubdepartment as $subdepartment)
					{
						$options = $options->concat([['value'=>$subdepartment->id, 'selected'=>'selected', 'description'=>$subdepartment->name]]);
					}
				}
				elseif(isset($employee_edit))
				{
					$subdepartment = App\Subdepartment::where('id',$employee_edit->subdepartment_id)->first();
					if(isset($subdepartment) && $subdepartment != '')
					{
						$options = $options->concat([[ "value" => $subdepartment->id, "description"	=> $subdepartment->name, "selected"	=> "selected" ]]);
					}
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Puesto:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el puesto" 
					name="work_position" 
					data-validation="length required" 
					data-validation-length="max100" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->position}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->position}}" 
					@endif 
					@if(isset($employee)) disabled @endif @if(isset($request) && $request->status != 2) 
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Jefe Inmediato (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el jefe Inmediato" 
					type="text" 
					name="work_immediate_boss" 
					data-validation="length" 
					data-validation-length="max100" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->immediate_boss}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->immediate_boss}}" 
					@endif 
					@if(isset($employee)) disabled @endif @if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx') laboral-data @endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Puesto del Jefe Inmediato (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el puesto del jefe inmediato" 
					type="text" 
					name="work_position_immediate_boss" 
					data-validation="length" 
					data-validation-length="max100"  
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->position_immediate_boss }}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->position_immediate_boss}}" 
					@endif  
					@if(isset($employee)) disabled @endif @if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Fecha de ingreso:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Seleccione la fecha de ingreso" 
					name="work_income_date" 
					data-validation="date required" 
					data-validation-format="dd-mm-yyyy"
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->admissionDate != "" ? $employee->workerDataVisible->first()->admissionDate->format('d-m-Y') : ""}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->admissionDate != "" ? $employee_edit->admissionDate->format('d-m-Y') : '' }}" 
					@endif  
					@if(isset($employee)) 
						disabled 
					@endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled"
					@endif
				@endslot
				@slot('classEx')
					laboral-data
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Estado de IMSS: @if(!isset($new_rq)) (Opcional) @endif
			@endcomponent
			@php
				$attributeEx = "name=\"work_status_imss\" multiple";
				if(isset($new_rq)) 
				{
					$attributeEx = $attributeEx." data-validation=\"required\""; 
				}
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
				
				$options = [];
				$valIMSS = [ "0" => "Inactivo", "1" => "Activo"];
				 
				foreach ($valIMSS as $key => $v)
				{
					$options[] =
					[
						"value"			=> $key,
						"description"	=> $v,
						"selected"		=>
						(
								isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->status_imss == $key
							?
								"selected"
							: 
								isset($employee_edit) && $employee_edit->status_imss == $key
							?
								"selected"
							:
								""
						)
					];
				}	
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Fecha de alta (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Seleccione la fecha de alta" 
					name="work_imss_date" 
					data-validation="date" 
					data-validation-format="dd-mm-yyyy"
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->imssDate!="" ? $employee->workerDataVisible->first()->imssDate->format('d-m-Y') : ""}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->imssDate != "" ? $employee_edit->imssDate->format('d-m-Y') : '' }}" 
					@endif
					@if(isset($employee)) 
						disabled 
					@endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Fecha de baja (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Seleccione la fecha de baja" 
					name="work_down_date" 
					data-validation="date" 
					data-validation-optional="true" 
					data-validation-format="dd-mm-yyyy"
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->downDate != "" ? $employee->workerDataVisible->first()->downDate->format('d-m-Y') : ""}}"
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->downDate != "" ? $employee_edit->downDate->format('d-m-Y') : '' }}" 
					@endif
					@if(isset($employee)) disabled @endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Fecha de término de relación laboral (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
			<input  @if(isset($employee)) disabled @endif @if(isset($request) && $request->status != 2)  disabled="disabled" @endif>
				@slot('attributeEx')
					placeholder="Seleccione la fecha de término de relación laboral" 
					type="text" 
					name="work_ending_date" 
					data-validation="date" 
					data-validation-format="dd-mm-yyyy"
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->endingDate != "" ? $employee->workerDataVisible->first()->endingDate->format('d-m-Y') : ""}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->endingDate != "" ? $employee_edit->endingDate->format('d-m-Y') : '' }}" 
					@endif
					@if(isset($employee)) 
						disabled 
					@endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Reingreso (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Seleccione la fecha de reingreso" 
					name="work_reentry_date" 
					data-validation="date" 
					data-validation-format="dd-mm-yyyy"
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->reentryDate != "" ? $employee->workerDataVisible->first()->reentryDate->format('d-m-Y') : ""}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->reentryDate != "" ? $employee_edit->reentryDate->format('d-m-Y') : '' }}" 
					@endif
					@if(isset($employee)) disabled @endif @if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Tipo de Trabajador:
			@endcomponent
			@php
				$options = collect();
				foreach(App\CatContractType::orderName()->get() as $contract)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->workerType))
					{
						if($employee->workerDataVisible->first()->workerType == $contract->id)
						{
							$options = $options->concat([['value'=>$contract->id, 'selected'=>'selected', 'description'=>$contract->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$contract->id, 'description'=>$contract->description]]);
						}
					}
					else if(isset($employee_edit) && isset($employee_edit->workerType)) 
					{
						if($employee_edit->workerType == $contract->id)
						{
							$options = $options->concat([['value'=>$contract->id, 'selected'=>'selected', 'description'=>$contract->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$contract->id, 'description'=>$contract->description]]);
						}
					}
					else
					{
						$options = $options->concat([['value'=>$contract->id, 'description'=>$contract->description]]);
					}
				}
				$attributeEx = "name=\"work_type_employee\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Régimen:
			@endcomponent
			@php
				$options = collect();
				foreach(App\CatRegimeType::orderName()->get() as $regime)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->regime_id))
					{
						if($employee->workerDataVisible->first()->regime_id==$regime->id)
						{
							$options = $options->concat([['value'=>$regime->id, 'selected'=>'selected', 'description'=>$regime->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$regime->id, 'description'=>$regime->description]]);
						}
					} 
					else if(isset($employee_edit) && isset($employee_edit->regime_id)) 
					{
						if($employee_edit->regime_id == $regime->id)
						{
							$options = $options->concat([['value'=>$regime->id, 'selected'=>'selected', 'description'=>$regime->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$regime->id, 'description'=>$regime->description]]);
						}
					}
					else
					{
						$options = $options->concat([['value'=>$regime->id, 'description'=>$regime->description]]);
					}
				}
				$attributeEx = "name=\"regime_employee\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Estado de Empleado:
			@endcomponent
			@php
				$attributeEx = "name=\"work_status_employee\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$options	= [];
				$valStatus	= [ "1" => "Activo", "2" => "Baja parcial", "3" => "Baja definitiva", "4" => "Suspensión", "5" => "Boletinado" ];
				 
				foreach ($valStatus as $key => $v)
				{
					$options[] =
					[
						"value"			=> $key,
						"description"	=> $v,
						"selected"		=>
						(
								isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->workerStatus == $key
							?
								"selected"
							:
								isset($employee_edit) && $employee_edit->workerStatus == $key
							?
								"selected"
							:
								""
						)
					]; 
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Motivo de estado (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="work_status_reason" 
					@if(isset($employee)) 
						disabled="disabled" 
					@endif 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->status_reason}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->status_reason}}" 
					@endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
					placeholder="Ingrese el motivo de estado"
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				SDI (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el SDI" 
					name="work_sdi" 
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->sdi}}" 
					@endif 
					@if(isset($employee_edit)) 
						value="{{$employee_edit->sdi}}" 
					@endif
					@if(isset($employee)) disabled @endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Periodicidad:
			@endcomponent
			@php
				$options = collect();
				foreach(App\CatPeriodicity::orderName()->get() as $per)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->periodicity))
					{
						if($employee->workerDataVisible->first()->periodicity == $per->c_periodicity)
						{
							$options = $options->concat([['value'=>$per->c_periodicity, 'selected'=>'selected', 'description'=>$per->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$per->c_periodicity, 'description'=>$per->description]]);
						}
					}
					else if(isset($employee_edit) && $employee_edit->periodicity==$per->c_periodicity)
					{
						$options = $options->concat([['value'=>$per->c_periodicity, 'selected'=>'selected', 'description'=>$per->description]]);
					}
					else
					{
						$options = $options->concat([['value'=>$per->c_periodicity, 'description'=>$per->description]]);
					}
				}
				$attributeEx = "name=\"work_periodicity\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Registro Patronal:
			@endcomponent
			@php
				$options = collect();
				if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->employer_register))
				{
					$options = $options->concat([['value'=>$employee->workerDataVisible->first()->employer_register, 'selected'=>'selected', 'description'=>$employee->workerDataVisible->first()->employer_register]]);
				}
				elseif(isset($employee_edit) && $employee_edit->employer_register != "")
				{
					$options = $options->concat([['value'=>$employee_edit->employer_register, 'selected'=>'selected', 'description'=>$employee_edit->employer_register]]);
				}
				$attributeEx = "name=\"work_employer_register\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Forma de Pago:
			@endcomponent
			@php
				$optionsPay = [];
				foreach(App\PaymentMethod::orderName()->get() as $pay)
				{
					if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->paymentWay==$pay->idpaymentMethod)
					{ 
						$optionsPay[] = ['value' =>$pay->idpaymentMethod, 'selected'=>'selected', 'description'=>$pay->method];
					}
					else if(isset($employee_edit) && $employee_edit->paymentWay==$pay->idpaymentMethod) 
					{
						$optionsPay[] = ['value' => $pay->idpaymentMethod, 'selected'=>'selected', 'description'=>$pay->method];
					}
					else
					{
						$optionsPay[] = ['value'=>$pay->idpaymentMethod, 'description'=>$pay->method];
					}
				}
				$attributeEx = "name=\"work_payment_way\" multiple data-validation=\"required\"";
				if(isset($employee)) 
				{
					$attributeEx = $attributeEx." disabled";
				}  
				if(isset($request) && $request->status != 2) 
				{
					$attributeEx = $attributeEx." disabled=\"disabled\"";
				} 
				$classEx="laboral-data removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $optionsPay, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				if(isset($employee_config))
				{
					if(isset($employee) && count($employee->workerDataVisible)>0)
					{
						$netIncomeValue = $employee->workerDataVisible->first()->netIncome;
					}
					if(isset($employee_edit))
					{
						$netIncomeValue = $employee_edit->netIncome;
					}
				}
				else
				{
					if(isset($employee) && count($employee->workerDataVisible)>0)
					{
						if($employee->workerDataVisible->first()->periodicity == "02")
						{
							$netIncomeValue = number_format(($employee->workerDataVisible->first()->netIncome / 4),2,'.','');
						}
						else if($employee->workerDataVisible->first()->periodicity == "04")
						{
							$netIncomeValue = number_format(($employee->workerDataVisible->first()->netIncome / 2),2,'.','');
						}
					}
					if(isset($employee_edit))
					{
						if($employee_edit->periodicity == "02")
						{
							$netIncomeValue = number_format(($employee_edit->netIncome / 4),2,'.','');
						}
						else if($employee_edit->periodicity == "04")
						{
							$netIncomeValue = number_format(($employee_edit->netIncome / 2),2,'.','');
						}
					}
				}
			@endphp
			@component('components.labels.label') 
				@if(isset($employee_config)) Sueldo neto (Opcional) @else Sueldo neto mensual (Opcional): @endif
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el sueldo neto" 
					name="work_net_income"
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($netIncomeValue)) value="{{$netIncomeValue}}" @endif
					@if(isset($employee)) disabled @endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Viáticos (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="0.00"
					name="work_viatics" 
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif 
					@if(isset($employee)) 
						disabled="disabled" 
					@endif 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->viatics}}" 
					@endif
					@if(isset($employee_edit)) value="{{$employee_edit->viatics}}" @endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Campamento (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="0.00" 
					name="work_camping"
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif  
					@if(isset($employee)) 
						disabled="disabled" 
					@endif 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->camping}}" 
					@endif
					@if(isset($employee_edit)) value="{{$employee_edit->camping}}" @endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Complemento (si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingresa el complemento" 
					name="work_complement"  
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->complement}}" 
					@endif 
					@if(isset($employee)) disabled @endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
					@if(isset($employee_edit)) value="{{$employee_edit->complement}}" @endif
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Monto Fonacot (Si aplica):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese el monto fonacot"
					name="work_fonacot" 
					data-validation="number" 
					data-validation-allowing="float" 
					data-validation-optional="true" 
					@if(isset($employee) && count($employee->workerDataVisible)>0) 
						value="{{$employee->workerDataVisible->first()->fonacot}}" 
					@endif 
					@if(isset($employee)) disabled @endif 
					@if(isset($request) && $request->status != 2)  
						disabled="disabled" 
					@endif
					@if(isset($employee_edit)) value="{{$employee_edit->fonacot}}" @endif 
				@endslot
				@slot('classEx')
					laboral-data 
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@if(!isset($new_rq) && !isset($employee_edit) && !isset($employee_new))
		@php
			$dateOld	 = "";
			if(isset($employee) && count($employee->workerDataVisible)>0)
			{
				$dateOld = $employee->workerDataVisible->first()->admissionDateOld != "" ? $employee->workerDataVisible->first()->admissionDateOld->format('d-m-Y') : "";
			}
			if(isset($employee))
			{
				$disabled = " disabled";
			}
			else
			{
				$disabled = "";
			}
			$options = collect();
			foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
            {
                $description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
                if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->enterpriseOld==$enterprise->id)
                {
                    $options = $options->concat([['value'=>$enterprise->id, 'selected'=>'selected', 'description'=>$description]]);
                }
                else
                {
                    $options = $options->concat([['value'=>$enterprise->id, 'description'=>$description]]);
                }
            }
		@endphp
		@component('components.labels.title-divisor') 
			NEGOCIACIONES DE CAMBIO DE EMPRESA
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') 
					Empresa Anterior
				@endcomponent
				@component('components.inputs.select', [
					"classEx"     => "laboral-data removeselect",
					"attributeEx" => "name=\"work_enterprise_old\" multiple ".$disabled,
					"options"     => $options,
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Fecha de Ingreso
				@endcomponent
				@component('components.inputs.input-text')
					@slot("classEx")     
						laboral-data
					@endslot
                    @slot("attributeEx")
						placeholder="Seleccione la fecha de ingreso" value="{{$dateOld}}" type="text" name="work_income_date_old" data-validation-format="dd-mm-yyyy" data-validation-allowing="range[0;100]" {{$disabled}}
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	@if(!isset($new_rq) && !isset($employee_new))
		@component('components.labels.title-divisor') 
			Esquema de pagos
		@endcomponent
		@php
			if(isset($employee) && count($employee->workerDataVisible)>0)
			{
				$nominaValue = $employee->workerDataVisible->first()->nomina;
				$bonoValue	 = $employee->workerDataVisible->first()->bono;
			}
			else
			{
				$nominaValue = "";
				$bonoValue	 = "";
			}
			if(isset($employee))
			{
				$disabled = " disabled";
			}
			else
			{
				$disabled = " ";
			}		
		@endphp
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') 
					Porcentaje de nómina
				@endcomponent
				@component('components.inputs.input-text')
					@slot("classEx")     
						laboral-data
					@endslot
                    @slot("attributeEx")
						placeholder="Ingrese el porcentaje de nómina" type="text" name="work_nomina" value="{{$nominaValue}}" data-validation="number required" data-validation-allowing="range[0;100]" {{$disabled}}
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Porcentaje de bonos
				@endcomponent
				@component('components.inputs.input-text')
					@slot("classEx")     
						laboral-data
					@endslot
                    @slot("attributeEx")
						placeholder="Ingrese el porcentaje de bonos" type="text" name="work_bonus" value="{{$bonoValue}}" data-validation="number required" data-validation-allowing="range[0;100]" {{$disabled}}
					@endslot
				@endcomponent
			</div>
		@endcomponent
	@endif
	@component('components.labels.title-divisor')
		<div class="text-center p-2 checks">
			@component('components.inputs.switch')
				@slot('attributeEx')
					id="infonavit" 
					name="infonavit"
					@if(isset($employee_edit) && $employee_edit->infonavitCredit!='') 
						checked
					@endif
					@if(isset($employee)) disabled @endif
				@endslot
				@slot('classEx')
					custom-control-input laboral-data
				@endslot
				INFONAVIT 
			@endcomponent
		</div>
	@endcomponent
	<div class="infonavit-container" @if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->infonavitCredit!='') block @elseif(isset($employee_edit) && $employee_edit->infonavitCredit!='') block @else hidden @endif>
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') 
					Número de crédito
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="work_infonavit_credit"  
						data-validation="required" 
						placeholder="Ingrese el número de crédito"
						@if(isset($employee) && count($employee->workerDataVisible)>0) 
							value="{{$employee->workerDataVisible->first()->infonavitCredit}}" 
						@endif 
						@if(isset($employee_edit)) 
							value="{{$employee_edit->infonavitCredit}}" 
						@endif
						@if(isset($employee)) disabled @endif
					@endslot
					@slot('classEx')
						laboral-data 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Descuento:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="work_infonavit_discount" 
						data-validation="number required" 
						data-validation-allowing="float" 
						placeholder="Ingrese el descuento"
						@if(isset($employee) && count($employee->workerDataVisible)>0) 
							value="{{$employee->workerDataVisible->first()->infonavitDiscount}}" 
						@endif 
						@if(isset($employee_edit)) 
							value="{{$employee_edit->infonavitDiscount}}" 
						@endif
						@if(isset($employee)) disabled @endif
					@endslot
					@slot('classEx')
						laboral-data 
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Tipo de descuento:
				@endcomponent
				@php
					$optionSelected = '';
					if(isset($employee) && count($employee->workerDataVisible)>0 && isset($employee->workerDataVisible->first()->infonavitDiscountType))
					{
						$optionSelected = $employee->workerDataVisible->first()->infonavitDiscountType;
					}
					if(isset($employee_edit) && isset($employee_edit->infonavitDiscountType))
					{
						$optionSelected = $employee_edit->infonavitDiscountType;
					}
					$options = collect([
						['value'=> '1', 'description' => 'VSM (Veces Salario Mínimo)', 'selected' => (($optionSelected == 1) ? "selected" : "")],
						['value'=> '2', 'description' => 'Cuota fija', 'selected' => (($optionSelected == 2) ? "selected" : "")],
						['value'=> '3', 'description' => 'Porcentaje', 'selected' => (($optionSelected == 3) ? "selected" : "")]
					]);
					$attributeEx = "name=\"work_infonavit_discount_type\" multiple data-validation=\"required\" ";
					if(isset($employee)) 
					{
						$attributeEx = $attributeEx." disabled";
					}  
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = $attributeEx." disabled=\"disabled\"";
					} 
					$classEx="laboral-data removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
			</div>
		@endcomponent
	</div>
	@component('components.labels.title-divisor') CUENTAS BANCARIAS DEL EMPLEADO @endcomponent
	@component("components.containers.container-form")
		@slot('classEx')
			content-bank class-banks
		@endslot
		<div class="col-span-2">
			@component('components.labels.label') 
				Alias:
			@endcomponent
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
			@component('components.labels.label') 
				Banco:
			@endcomponent
			@php
				$options = collect();
				$classEx="bank removeselect";
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => $classEx])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				CLABE:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la CLABE" 
					data-validation="clabe"
				@endslot
				@slot('classEx')
					clabe 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 account-td">
			@component('components.labels.label') 
				Cuenta bancaria:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					name="account" 
					placeholder="Ingrese la cuenta bancaria" 
					data-validation="cuenta"
				@endslot
				@slot('classEx')
					account 
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Tarjeta:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la tarjeta" 
					data-validation="tarjeta"
				@endslot
				@slot('classEx')
					card
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Sucursal (Opcional):
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					placeholder="Ingrese la sucursal"
				@endslot
				@slot('classEx')
					branch_office
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar cuenta",])
				@slot('attributeEx')
					id="add-bank"
					type="button"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@if(isset($employee))
		@php
			$body 			= [];
			$modelBody		= [];
			$classEx		= "";
			$modelHead		= [ "Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
			foreach($employee->bankData()->where('visible',1)->where('type',1)->get() as $bank)
			{
				$valueBank = isset($bank->idCatBank) && $bank->idCatBank != "" ? $bank->idCatBank : $bank->id_catbank;
				$body = 
				[ "classEx" => "tr-employee",
					[
						"classEx" =>"td",
						"content" => 
						[
							"label" => $bank->alias,
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\" value=\"".$bank->beneficiary."\""
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"type_account[]\" value=\"1\""
							]
						]
					],
					[ 
						"classEx" =>"td",
						"content" => 
						[ 
							"label" => $bank->bank->description,
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"".$bank->id."\"",
								"classEx"     => "idEmployee"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"bank[]\" value=\"".$valueBank."\""
							],
						]
					],
					[
						"classEx" =>"td",
						"content" => 
						[ 
							"label" => $bank->clabe !='' ? $bank->clabe : " --- ",
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
							]
						]
					],
					[
						"classEx" =>"td",
						"content" => 
						[ 
							"label" => $bank->account !='' ? $bank->account : " --- ",
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
							]
						]
					],
					[
						"classEx" =>"td",
						"content" => 
						[ 
							"label" => $bank->cardNumber !='' ? $bank->cardNumber : " --- ",
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"card[]\" value=\"".$bank->cardNumber."\""
							]
						]
					],
					[
						"classEx" =>"td",
						"content" => 
						[ 
							"label" => $bank->branch !='' ? $bank->branch : " --- ",
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"branch[]\" value=\"".$bank->branch."\""
							]
						]
					],
					[
						"classEx" =>"td",
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "red",
								"label"         => "<span class=\"icon-x\"></span>",
								"attributeEx"   => "type=\"button\"",
								"classEx"		=> "delete-bank"
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
	@elseif(isset($employee_edit))
		@php
			$body 			= [];
			$modelBody		= [];
			$classEx		= "";
			$modelHead		= [ "Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
			foreach($employee_edit->bankData()->where('type',1)->get() as $bank)
			{
				$valBank = isset($bank->idCatBank) && $bank->idCatBank != "" ? $bank->idCatBank : $bank->id_catbank;
				$body = 
				[ "classEx"	=> "tr-employee-edit",
					[
						"content" => 
						[
							[
								"label" => $bank->alias
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\" value=\"".$bank->beneficiary."\""
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"type_account[]\" value=\"1\""
							]
						]
					],
					[ 
						"content" => 
						[ 
							[
								"label" => $bank->bank->description
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
								"classEx"     => "idEmployee"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"bank[]\" value=\"".$valBank."\""
							],
						]
					],
					[
						"content" => 
						[ 
							[
								"label" => $bank->clabe !='' ? $bank->clabe : "---"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => $bank->account !='' ? $bank->account : "---"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
							]
						]
					],
					[
						"content" => 
						[ 
							[
								"label" => $bank->cardNumber !='' ? $bank->cardNumber : "---"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"card[]\" value=\"".$bank->cardNumber."\""
							]
						]
					],
					[
						"content" => 
						[ 
							[
								"label" => $bank->branch !='' ? $bank->branch : "---"
							],
							[
								"kind"          => "components.inputs.input-text",
								"attributeEx"   => "type=\"hidden\" name=\"branch[]\" value=\"".$bank->branch."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "red",
								"label"         => "<span class=\"icon-x\"></span>",
								"attributeEx"   => "type=\"button\"",
								"classEx"		=> "delete-bank"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
	@else
		@php
			$classEx	= "hidden";
			$body 		= [];
			$modelBody	= [];
			$modelHead	= [ "Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
		@endphp
		@component('components.labels.not-found', ["text" => "No se han encontrado cuentas bancarias registradas", "classEx" => "class-accounts", "attributeEx" => "id=\"not-found-accounts\""])  @endcomponent
	@endif
	@component('components.tables.alwaysVisibleTable',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody
	])
		@slot('attributeEx')
			id="bank-data-register"
		@endslot
		@slot('classExContainer')
			{{$classEx}}
		@endslot
		@slot('attributeExBody')
			id="bodyEmployee"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		<div class="text-center p-2 checks">
			@component('components.inputs.switch')
				@slot('attributeEx')
					id="alimony" 
					name="alimony"					
					@if(isset($employee_edit) && $employee_edit->alimonyDiscount!='') 
						checked="checked" 
					@endif
					@if(isset($employee)) disabled @endif
				@endslot
				@slot('classEx')
					laboral-data
				@endslot
				Pensión Alimenticia
			@endcomponent
		</div>
	@endcomponent
	<div class="alimony-container @if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->alimonyDiscount!='') block @elseif(isset($employee_edit) && $employee_edit->alimonyDiscount!='') block @else hidden @endif" id="infonavit-form">
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label') 
					Tipo de descuento:
				@endcomponent
				@php
					$optionSelected = "";
					if(isset($employee) && count($employee->workerDataVisible)>0 && ($employee->workerDataVisible->first()->alimonyDiscountType==1 || $employee->workerDataVisible->first()->alimonyDiscountType==2))
					{
						$optionSelected = $employee->workerDataVisible->first()->alimonyDiscountType;
					}
					$options = collect([
						['value'=> '1', 'description'=>'Monto', 'selected' => (($optionSelected == 1) ? "selected" : "")],
						['value'=> '2', 'description'=>'Porcentaje', 'selected' => (($optionSelected == 2) ? "selected" : "")]
					]);
					$attributeEx = "name=\"work_alimony_discount_type\" multiple data-validation=\"required\"".(isset($employee) ? " disabled" : "");
					$classEx="laboral-data removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Descuento:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name                     = "work_alimony_discount" 
						data-validation          = "number required" 
						data-validation-allowing = "float" 
						placeholder="Ingrese el descuento"
						@if(isset($employee) && count($employee->workerDataVisible)>0) 
							value="{{$employee->workerDataVisible->first()->alimonyDiscount}}" 
						@endif 
						@if(isset($employee)) disabled @endif
					@endslot
					@slot('classEx')
						laboral-data
					@endslot
				@endcomponent
			</div>
		@endcomponent
	</div>
	<div id="accounts-alimony" class="@if(isset($employee) && count($employee->workerDataVisible)>0 && $employee->workerDataVisible->first()->alimonyDiscount!='')  @elseif(isset($employee_edit) && $employee_edit->alimonyDiscount!='') @else hidden @endif">
		@component('components.labels.subtitle')
			CUENTAS BANCARIAS DEL BENEFICIARIO
		@endcomponent
		@component("components.containers.container-form")
			@slot('classEx')
				content-bank-alimony class-banks
			@endslot
			<div class="col-span-2">
				@component('components.labels.label') 
					Beneficiario:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el beneficiario"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						beneficiary disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Alias:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el alias"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						alias disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Banco:
				@endcomponent
				@component('components.inputs.select', ['options' => $options = collect(), 'classEx' => "bank disabled-alimony removeselect"])
				@slot('attributeEx')
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					CLABE:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese la CLABE" 
						data-validation="clabe"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						clabe disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Cuenta:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese la cuenta"
						data-validation="cuenta"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						account disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Tarjeta:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese la tarjeta" 
						data-validation="tarjeta"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						card disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Sucursal (Opcional):
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese la sucursal"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
					@slot('classEx')
						branch_office disabled-alimony
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar cuenta", "classEx" => "disabled-alimony"])
					@slot('attributeEx')
						type="button"
						id="add-bank-alimony"
						@if(isset($employee) && count($employee->workerDataVisible) > 0 && $employee->workerDataVisible->first()->alimonyDiscount != '') disabled="disabled" @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Beneficiario","Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
		@endphp
		@if(isset($employee))
			@php
				$classEx		= "";
				foreach($employee->bankData()->where('visible',1)->where('type',2)->get() as $bank)
				{
					$body = 
					[
						[
							"content" => 
							[
								[
									"label" => $bank->beneficiary !='' ? $bank->alias : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\" value=\"".$bank->beneficiary."\""
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"type_account[]\" value=\"2\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->alias !='' ? $bank->alias : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
								]
							]
						],
						[ 
							"content" => 
							[ 
								[
									"label" => $bank->bank->description !='' ? $bank->bank->description : " --- ",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"".$bank->id."\"",
									"classEx"     => "idEmployee"
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"bank[]\" value=\"".$bank->idCatBank."\""
								],
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->clabe !='' ? $bank->clabe : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->account !='' ? $bank->account : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
								]
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->cardNumber !='' ? $bank->cardNumber : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"card[]\" value=\"".$bank->cardNumber."\""
								]
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->branch !='' ? $bank->branch : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"branch[]\" value=\"".$bank->branch."\""
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"          => "components.buttons.button",
									"attributeEx"   => "type=\"button\"",
									"variant"       => "red",
									"label"         => "<span class=\"icon-x\"></span>",
									"classEx"       => "delete-bank"
								]
							]
						]
					];
					array_push($modelBody, $body);
				}
			@endphp
		@elseif(isset($employee_edit))
			@php
				$classEx		= "";
				foreach($employee_edit->bankData()->where('type',2)->get() as $bank)
				{
					$valBank = isset($bank->idCatBank) && $bank->idCatBank != "" ? $bank->idCatBank : $bank->id_catbank;
					$body = 
					[ "classEx"	=> "tr-employee-edit",
						[
							"content" => 
							[
								[
									"label" => $bank->beneficiary !='' ? $bank->alias : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\" value=\"".$bank->beneficiary."\""
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"type_account[]\" value=\"2\""
								]
							]
						],
						[
							"show"    => "true",
							"content" => 
							[
								[
									"label" => $bank->alias !='' ? $bank->alias : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"alias[]\" value=\"".$bank->alias."\""
								]
							]
						],
						[ 
							"show"    => "true",
							"content" => 
							[ 
								[
									"label" => $bank->bank->description !='' ? $bank->bank->description : "---",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
									"classEx"     => "idEmployee"
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"bank[]\" value=\"".$valBank."\""
								]
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->clabe !='' ? $bank->clabe : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"clabe[]\" value=\"".$bank->clabe."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->account !='' ? $bank->account : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"account[]\" value=\"".$bank->account."\""
								]
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->cardNumber !='' ? $bank->cardNumber : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"card[]\" value=\"".$bank->cardNumber."\""
								]
							]
						],
						[
							"content" => 
							[ 
								[
									"label" => $bank->branch !='' ? $bank->branch : "---",
								],
								[
									"kind"          => "components.inputs.input-text",
									"attributeEx"   => "type=\"hidden\" name=\"branch[]\" value=\"".$bank->branch."\""
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"          => "components.buttons.button",
									"attributeEx"   => "type=\"button\"",
									"variant"       => "red",
									"label"         => "<span class=\"icon-x\"></span>",
									"classEx"       => "delete-bank"
								]
							]
						]
					];
					array_push($modelBody, $body);
				}
			@endphp
		@else
			@php $classEx		= "hidden"; @endphp
			@component('components.labels.not-found', ["text" => "No se han encontrado cuentas bancarias registradas", "attributeEx" => "id=\"not-found-accounts-alimony\""]) @endcomponent
		@endif
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody
		])
			@slot('attributeEx')
				id="bank-data-register-alimony"
			@endslot
			@slot('classExContainer')
				{{$classEx}}
			@endslot
			@slot('attributeExBody')
				id="bodyAlimony"
			@endslot
		@endcomponent
	</div>
	@component('components.labels.title-divisor')
		LISTA DE DOCUMENTOS
	@endcomponent
	@php
		/* ACTA DE NACIMIENTO */
		$actaContent         = [];
		$docBirthCertificate = isset($employee) && $employee->doc_birth_certificate != "" ? $employee->doc_birth_certificate : (isset($employee_edit) && $employee_edit->doc_birth_certificate != "" ? $employee_edit->doc_birth_certificate : "Sin documento");
		$urlFile 			 = "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docBirthCertificate))
		{
			$urlFile = url('docs/requisition/'.$docBirthCertificate);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docBirthCertificate))
		{
			$urlFile = url('docs/staff/'.$docBirthCertificate);
		}
		if($docBirthCertificate != "Sin documento" && $urlFile != ""){
			$actaContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$actaContent = 
			[
				"label" => "Sin documento"
			];
		}

		/* COMPROBANTE DE DOMICILIO */
		$comprobanteContent = [];
		$docProofOfAddress  = isset($employee) && $employee->doc_proof_of_address != "" ? $employee->doc_proof_of_address : (isset($employee_edit) && $employee_edit->doc_proof_of_address != "" ? $employee_edit->doc_proof_of_address : "Sin documento");
		$urlFile 			= "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docProofOfAddress))
		{
			$urlFile = url('docs/requisition/'.$docProofOfAddress);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docProofOfAddress))
		{
			$urlFile = url('docs/staff/'.$docProofOfAddress);
		}
		if($docProofOfAddress != "Sin documento" && $urlFile != "")
		{
			$comprobanteContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$comprobanteContent = 
			[
				"label" => "Sin documento"
			];
		}

		/* NSS */
		$nssContent = [];
		$docNss 	= isset($employee) && $employee->doc_nss != "" ? $employee->doc_nss : (isset($employee_edit) && $employee_edit->doc_nss != "" ? $employee_edit->doc_nss : "Sin documento");
		$urlFile 	= "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docNss))
		{
			$urlFile = url('docs/requisition/'.$docNss);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docNss))
		{
			$urlFile = url('docs/staff/'.$docNss);
		}
		if($docNss != "Sin documento" && $urlFile != "")
		{
			$nssContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"	
			];
		}
		else
		{
			$nssContent = 
			[
				"label" => "Sin documento"
			];
		}
		//INE 
		$ineContent = [];
		$docIne 	= isset($employee) && $employee->doc_ine != "" ? $employee->doc_ine : (isset($employee_edit) && $employee_edit->doc_ine != "" ? $employee_edit->doc_ine : "Sin documento");
		$urlFile 	= "";
		if(\Storage::disk("public")->exists("/docs/requisition/".$docIne))
		{
			$urlFile = url("docs/requisition/".$docIne);
		}
		elseif(\Storage::disk("public")->exists("/docs/staff/".$docIne))
		{
			$urlFile = url("docs/staff/".$docIne);
		}
		if(($docIne != "Sin documento") && ($urlFile != ""))
		{
			$ineContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"	
			];
		}
		else
		{
			$ineContent = 
			[
				"label" => "Sin documento"
			];
		}

		/* CURP */
		$curpContent = [];
		$docCurp 	 = isset($employee) && $employee->doc_curp != "" ? $employee->doc_curp : (isset($employee_edit) && $employee_edit->doc_curp != "" ? $employee_edit->doc_curp : "Sin documento");
		$urlFile 	 = "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docCurp))
		{
			$urlFile = url('docs/requisition/'.$docCurp);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docCurp))
		{
			$urlFile = url('docs/staff/'.$docCurp);
		}
		if($docCurp != "Sin documento" && $urlFile != "")
		{
			$curpContent =
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"	
			]; 
		}
		else
		{
			$curpContent = 
			[
				"label" => "Sin documento"
			];
		}

		/* RFC */
		$rfcContent = [];
		$docRfc  	= isset($employee) && $employee->doc_rfc != "" ? $employee->doc_rfc : (isset($employee_edit) && $employee_edit->doc_rfc != "" ? $employee_edit->doc_rfc : "Sin documento");
		$urlFile 	= "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docRfc))
		{
			$urlFile = url('docs/requisition/'.$docRfc);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docRfc))
		{
			$urlFile = url('docs/staff/'.$docRfc);
		}
		if($docRfc != "Sin documento" && $urlFile != "")
		{
			$rfcContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$docRfc."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$rfcContent = 
			[
				"label" => "Sin documento"
			];
		}

		$cvContent  = [];
		$docCv  	= isset($employee) && $employee->doc_cv != "" ? $employee->doc_cv : (isset($employee_edit) && $employee_edit->doc_cv != "" ? $employee_edit->doc_cv : "Sin documento");
		$urlFile 	= "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docCv))
		{
			$urlFile = url('docs/requisition/'.$docCv);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docCv))
		{
			$urlFile = url('docs/staff/'.$docCv);
		}
		if($docCv != "Sin documento" && $urlFile != "")
		{
			$cvContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$docRfc."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$cvContent = 
			[
				"label" => "Sin documento"
			];
		}

		$ceContent			= [];
		$docProofOfStudies  = isset($employee) && $employee->doc_proof_of_studies != "" ? $employee->doc_proof_of_studies : (isset($employee_edit) && $employee_edit->doc_proof_of_studies != "" ? $employee_edit->doc_proof_of_studies : "Sin documento");
		$urlFile 			= "";
		if(\Storage::disk('public')->exists('/docs/requisition/'.$docProofOfStudies))
		{
			$urlFile = url('docs/requisition/'.$docProofOfStudies);
		}
		elseif(\Storage::disk('public')->exists('/docs/staff/'.$docProofOfStudies))
		{
			$urlFile = url('docs/staff/'.$docProofOfStudies);
		}
		if($docProofOfStudies != "Sin documento" && $urlFile != "")
		{
			$ceContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$ceContent = 
			[
				"label" => "Sin documento"
			];
		}

		$cpContent 					= [];
		$docProfessionalLicense 	= isset($employee) && $employee->doc_professional_license != "" ? $employee->doc_professional_license : (isset($employee_edit) && $employee_edit->doc_professional_license != "" ? $employee_edit->doc_professional_license : "Sin documento");
		$urlFile 					= "";
		if(\Storage::disk("public")->exists("/docs/requisition/".$docProfessionalLicense))
		{
			$urlFile = url("docs/requisition/".$docProfessionalLicense);
		}
		elseif(\Storage::disk("public")->exists("/docs/staff/".$docProfessionalLicense))
		{
			$urlFile = url("docs/staff/".$docProfessionalLicense);
		}
		if($docProfessionalLicense != "Sin documento" && $urlFile != "")
		{		
			$cpContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$cpContent = 
			[
				"label" => "Sin documento"
			];
		}
			
		$requisitionContent = [];
		$docRequisition 	= isset($employee) && $employee->doc_requisition != "" ? $employee->doc_requisition : (isset($employee_edit) && $employee_edit->doc_requisition != "" ? $employee_edit->doc_requisition : "Sin documento");
		$urlFile 			= "";
		if(\Storage::disk("public")->exists("/docs/requisition/".$docRequisition))
		{
			$urlFile = url("docs/requisition/".$docRequisition);
		}
		elseif(\Storage::disk("public")->exists("/docs/staff/".$docRequisition))
		{
			$urlFile = url("docs/staff/".$docRequisition);
		}
		if($docRequisition != "Sin documento" && $urlFile != "")
		{
			$requisitionContent = 
			[
				"kind"          => "components.buttons.button",
				"buttonElement" => "a",
				"variant"       => "secondary",
				"attributeEx"   => "target=\"_blank\" href=\"".$urlFile."\"",
				"label"         => "Archivo"
			];
		}
		else
		{
			$requisitionContent = 
			[
				"label" => "Sin documento"
			];
		}
					
		$modelBody = [
			[
				[
					"content" =>
					[
						[
							"label" => "Acta de nacimiento"
						]
					]
				],
				[ 
					"classEx" => "doc_birth_certificate",
					"content" =>
					[
						$actaContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Comprobante de Domicilio"
						]
					]
				],
				[ 
					"classEx" => "doc_proof_of_address",
					"content" =>
					[
						$comprobanteContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Número  de Seguridad Social"
						]
					]
				],
				[ 
					"classEx" => "doc_nss",
					"content" =>
					[
						$nssContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "INE"
						]
					]
				],
				[ 
					"classEx" => "doc_ine",
					"content" =>
					[
						$ineContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "CURP"
						]
					]
				],
				[ 
					"classEx" => "doc_curp",
					"content" =>
					[
						$curpContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "RFC"
						]
					]
				],
				[ 
					"classEx" => "doc_rfc",
					"content" =>
					[
						$rfcContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Curriculum Vitae/Solicitud de Empleo"
						]
					]
				],
				[ 
					"classEx" => "doc_cv",
					"content" =>
					[
						$cvContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Comprobante de Estudios"
						]
					]
				],
				[ 
					"classEx" => "doc_proof_of_studies",
					"content" =>
					[
						$ceContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Cédula Profesional"
						]
					]
				],
				[ 
					"classEx" => "doc_professional_license",
					"content" =>
					[
						$cpContent
					]
				]
			],
			[
				[
					"content" =>
					[
						[
							"label" => "Requisición Firmada"
						]
					]
				],
				[ 
					"classEx" => "doc_requisition",
					"content" =>
					[
						$requisitionContent
					]
				]
			]
		];
		$docsContent	= [];
		$docsEmployee	= [];
		$docsName		= [];
		if(isset($employee))
		{
			foreach($employee->documents as $doc)
			{
				$docsName = [
					[
						"kind"		=> "components.labels.label",
						"classEx"	=> "name-doc",
						"label"		=> $doc->name
					]
				];
				if($doc->path != "")
				{
					$docsContent = [
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
							"label"			=> "Archivo"
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"",
							"classEx"		=> "class-path"
						]
					];
				}
				else
				{
					$docsContent = [
						[
							"label" => "Sin documento"
						]
					];
				}
				$docsEmployee = [ "classEx"	=> "tr-remove",
					[
						"content" => $docsName
					],
					[
						"content" => $docsContent
					]
				];
				$modelBody[] = $docsEmployee;
			}
		}
		elseif(isset($employee_edit) && $employee_edit->documents != null)
		{
			foreach($employee_edit->documents as $doc)
			{
				$docsName = [
					[
						"kind"		=> "components.labels.label",
						"classEx"	=> "name-doc",
						"label"		=> $doc->name
					]
				];
				if($doc->path != "")
				{
					$docsContent = [
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
							"label"			=> "Archivo"
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"",
							"classEx"		=> "class-path"
						]
					];
				}
				else
				{
					$docsContent = [
						[
							"label" => "Sin documento"
						]
					];
				}
				$docsEmployee = [ "classEx"	=> "tr-remove",
					[
						"content" => $docsName
					],
					[
						"content" => $docsContent
					]
				];
				$modelBody[] = $docsEmployee;
			}
		}
		elseif(isset($employee_edit) && $employee_edit->staffDocuments != null)
		{
			foreach($employee_edit->staffDocuments as $doc)
			{
				$docsName = [
					[
						"kind"		=> "components.labels.label",
						"classEx"	=> "name-doc",
						"label"		=> $doc->name
					]
				];
				if($doc->path != "")
				{
					$docsContent = [
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\" href=\"".url('docs/staff/'.$doc->path)."\"",
							"label"			=> "Archivo"
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"",
							"classEx"		=> "class-path"
						]
					];
				}
				else
				{
					$docsContent = [
						[
							"label" => "Sin documento"
						]
					];
				}
				$docsEmployee = [ "classEx"	=> "tr-remove",
					[
						"content" => $docsName
					],
					[
						"content" => $docsContent
					]
				];
				$modelBody[] = $docsEmployee;
			}
		}
		$modelHead = ["Nombre del documento", "Archivo"];
	@endphp
	@component('components.tables.alwaysVisibleTable',[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody
	])
		@slot('classExBody')
			text-center
		@endslot
		@slot('attributeExBody')
			id="documents_employee"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		DOCUMENTOS
	@endcomponent
	@component("components.containers.container-form")
		<div class="col-span-2">
			@component('components.labels.label') 
				Acta de nacimiento
			@endcomponent
			@php
				$docBirthCertificate 	= isset($employee) && $employee->doc_birth_certificate != "" ? $employee->doc_birth_certificate : (isset($employee_edit) && $employee_edit->doc_birth_certificate != "" ? $employee_edit->doc_birth_certificate : "x");
				$flagBirthCertificate 	= false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docBirthCertificate))
				{
					$flagBirthCertificate = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docBirthCertificate))
				{
					$flagBirthCertificate = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"				=> true,
				"attributeExInput"		=> "name=\"path\" accept=\".pdf\"",
				"classExInput"			=> "pathActioner",
				"classExContainer"		=> isset($employee) && $employee->doc_birth_certificate != "" && $flagBirthCertificate ? "image_success" : (isset($employee_edit) && $employee_edit->doc_birth_certificate != "" && $flagBirthCertificate ? "image_success" : ""),
				"classExRealPath"		=> "path",
				"attributeExRealPath"	=> isset($employee) && $employee->doc_birth_certificate != "" && $flagBirthCertificate ? "name=\"doc_birth_certificate\" value=\"".$employee->doc_birth_certificate."\"" : (isset($employee_edit) && $employee_edit->doc_birth_certificate != "" && $flagBirthCertificate ? "name=\"doc_birth_certificate\" value=\"".$employee_edit->doc_birth_certificate."\"" : "name=\"doc_birth_certificate\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Comprobante de Domicilio
			@endcomponent
			@php
				$docProofOfAddress  = isset($employee) && $employee->doc_proof_of_address != "" ? $employee->doc_proof_of_address : (isset($employee_edit) && $employee_edit->doc_proof_of_address != "" ? $employee_edit->doc_proof_of_address : "x");
				$flagProofOfAddress = false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docProofOfAddress))
				{
					$flagProofOfAddress = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docProofOfAddress))
				{
					$flagProofOfAddress = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"         	  => "true",
				"attributeExInput" 	  => "name=\"path\" accept=\".pdf\"",
				"classExInput"     	  => "pathActioner",
				"classExContainer" 	  => isset($employee) && $employee->doc_proof_of_address != "" && $flagProofOfAddress ? "image_success" : (isset($employee_edit) && $employee_edit->doc_proof_of_address != "" && $flagProofOfAddress ? "image_success" : ""),
				"classExRealPath"  	  => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_proof_of_address != "" && $flagProofOfAddress ? "name=\"doc_proof_of_address\" value=\"".$employee->doc_proof_of_address."\"" : (isset($employee_edit) && $employee_edit->doc_proof_of_address != "" && $flagProofOfAddress ? "name=\"doc_proof_of_address\" value=\"".$employee_edit->doc_proof_of_address."\"" : "name=\"doc_proof_of_address\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Número  de Seguridad Social (NSS)
			@endcomponent
			@php
				$docNss 	= isset($employee) && $employee->doc_nss != "" ? $employee->doc_nss : (isset($employee_edit) && $employee_edit->doc_nss != "" ? $employee_edit->doc_nss : "x");
				$flagNss 	= false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docNss))
				{
					$flagNss = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docNss))
				{
					$flagNss = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"         	  => "true",
				"attributeExInput" 	  => "name=\"path\" accept=\".pdf\"",
				"classExInput"     	  => "pathActioner",
				"classExContainer" 	  => isset($employee) && $employee->doc_nss != "" && $flagNss ? "image_success" : (isset($employee_edit) && $employee_edit->doc_nss != "" && $flagNss ? "image_success" : ""),
				"classExRealPath"  	  => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_nss != "" && $flagNss ? "name=\"doc_nss\" value=\"".$employee->doc_nss."\"" : (isset($employee_edit) && $employee_edit->doc_nss != "" && $flagNss ? "name=\"doc_nss\" value=\"".$employee_edit->doc_nss."\"" : "name=\"doc_nss\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				INE
			@endcomponent
			@php
				$docIne 	= isset($employee) && $employee->doc_ine != "" ? $employee->doc_ine : (isset($employee_edit) && $employee_edit->doc_ine != "" ? $employee_edit->doc_ine : "x");
				$flagIne 	= false;
				if(\Storage::disk("public")->exists("/docs/requisition/".$docIne))
				{
					$flagIne = true;
				}
				elseif(\Storage::disk("public")->exists("/docs/staff/".$docIne))
				{
					$flagIne = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"            => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput" 		  => "pathActioner",
				"classExContainer"	  => isset($employee) && $employee->doc_ine != "" && $flagIne ? "image_success" : (isset($employee_edit) && $employee_edit->doc_ine != "" && $flagIne ? "image_success" : "uploader-content"),
				"classExRealPath" 	  => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_ine != "" && $flagIne ? "name=\"doc_ine\" value=\"".$employee->doc_ine."\"" : (isset($employee_edit) && $employee_edit->doc_ine != "" && $flagIne ? "name=\"doc_ine\" value=\"".$employee_edit->doc_ine."\"" : "name=\"doc_ine\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				CURP
			@endcomponent
			@php
				$docCurp  = isset($employee) && $employee->doc_curp != "" ? $employee->doc_curp : (isset($employee_edit) && $employee_edit->doc_curp != "" ? $employee_edit->doc_curp : "x");
				$flagCurp = false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docCurp))
				{
					$flagCurp = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docCurp))
				{
					$flagCurp = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete" 			  => "true",
				"attributeExInput"	  => "name=\"path\" accept=\".pdf\"",
				"classExInput" 		  => "pathActioner",
				"classExContainer" 	  => isset($employee) && $employee->doc_curp != "" && $flagCurp ? "image_success" : (isset($employee_edit) && $employee_edit->doc_curp != "" && $flagCurp ? "image_success" : ""),
				"classExRealPath" 	  => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_curp != "" && $flagCurp ? "name=\"doc_curp\" value=\"".$employee->doc_curp."\"" : (isset($employee_edit) && $employee_edit->doc_curp != "" && $flagCurp ? "name=\"doc_curp\" value=\"".$employee_edit->doc_curp."\"" : "name=\"doc_curp\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				RFC
			@endcomponent
			@php
				$docRfc  = isset($employee) && $employee->doc_rfc != "" ? $employee->doc_rfc : (isset($employee_edit) && $employee_edit->doc_rfc != "" ? $employee_edit->doc_rfc : "x");
				$flagRfc = false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docRfc))
				{
					$flagRfc = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docRfc))
				{
					$flagRfc = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"            => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput"        => "pathActioner",
				"classExContainer"    => isset($employee) && $employee->doc_rfc != "" && $flagRfc ? "image_success" : (isset($employee_edit) && $employee_edit->doc_rfc != "" && $flagRfc ? "image_success" : ""),
				"classExRealPath"     => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_rfc != "" && $flagRfc ? "name=\"doc_rfc\" value=\"".$employee->doc_rfc."\"" : (isset($employee_edit) && $employee_edit->doc_rfc != "" && $flagRfc ? "name=\"doc_rfc\" value=\"".$employee_edit->doc_rfc."\"" : "name=\"doc_rfc\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Curriculum Vitae/Solicitud de Empleo
			@endcomponent
			@php
				$docCv   = isset($employee) && $employee->doc_cv != "" ? $employee->doc_cv : (isset($employee_edit) && $employee_edit->doc_cv != "" ? $employee_edit->doc_cv : "x");
				$flagCv  = false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docCv))
				{
					$flagCv = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docCv))
				{
					$flagCv = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete" 			  => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput"        => "pathActioner",
				"classExContainer"    => isset($employee) && $employee->doc_cv != "" && $flagCv ? "image_success" : (isset($employee_edit) && $employee_edit->doc_cv != "" && $flagCv ? "image_success" : ""),
				"classExRealPath"     => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_cv != "" && $flagCv ? "name=\"doc_cv\" value=\"".$employee->doc_cv."\"" : (isset($employee_edit) && $employee_edit->doc_cv != "" && $flagCv ? "name=\"doc_cv\" value=\"".$employee_edit->doc_cv."\"" : "name=\"doc_cv\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Comprobante de Estudios
			@endcomponent
			@php
				$docProofOfStudies  = isset($employee) && $employee->doc_proof_of_studies != "" ? $employee->doc_proof_of_studies : (isset($employee_edit) && $employee_edit->doc_proof_of_studies != "" ? $employee_edit->doc_proof_of_studies : "x");
				$flagProofOfStudies = false;
				if(\Storage::disk('public')->exists('/docs/requisition/'.$docProofOfStudies))
				{
					$flagProofOfStudies = true;
				}
				elseif(\Storage::disk('public')->exists('/docs/staff/'.$docProofOfStudies))
				{
					$flagProofOfStudies = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete"			  => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput"        => "pathActioner",
				"classExContainer"    => isset($employee) && $employee->doc_proof_of_studies != "" && $flagProofOfStudies ? "image_success" : (isset($employee_edit) && $employee_edit->doc_proof_of_studies != "" && $flagProofOfStudies ? "image_success" : ""),
				"classExRealPath"     => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_proof_of_studies != "" && $flagProofOfStudies ? "name=\"doc_proof_of_studies\" value=\"".$employee->doc_proof_of_studies."\"" : (isset($employee_edit) && $employee_edit->doc_proof_of_studies != "" && $flagProofOfStudies ? "name=\"doc_proof_of_studies\" value=\"".$employee_edit->doc_proof_of_studies."\"" : "name=\"doc_proof_of_studies\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Cédula Profesional
			@endcomponent
			@php
				$docProfessionalLicense	 = isset($employee) && $employee->doc_professional_license != "" ? $employee->doc_professional_license : (isset($employee_edit) && $employee_edit->doc_professional_license != "" ? $employee_edit->doc_professional_license : "x");
				$flagProfessionalLicense = false;
				if(\Storage::disk("public")->exists("/docs/requisition/".$docProfessionalLicense))
				{
					$flagProfessionalLicense = true;
				}
				elseif(\Storage::disk("public")->exists("/docs/staff/".$docProfessionalLicense))
				{
					$flagProfessionalLicense = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete" 			  => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput"        => "pathActioner",
				"classExContainer"    => isset($employee) && $employee->doc_professional_license != "" && $flagProfessionalLicense ? "image_success" : (isset($employee_edit) && $employee_edit->doc_professional_license != "" && $flagProfessionalLicense ? "image_success" : ""),
				"classExRealPath"     => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_professional_license != "" && $flagProfessionalLicense ? "name=\"doc_professional_license\" value=\"".$employee->doc_professional_license."\"" : (isset($employee_edit) && $employee_edit->doc_professional_license != "" && $flagProfessionalLicense ? "name=\"doc_professional_license\" value=\"".$employee_edit->doc_professional_license."\"" : "name=\"doc_professional_license\""),
			])@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') 
				Requisición Firmada
			@endcomponent
			@php
				$docRequisition  = isset($employee) && $employee->doc_requisition != "" ? $employee->doc_requisition : (isset($employee_edit) && $employee_edit->doc_requisition != "" ? $employee_edit->doc_requisition : "x");
				$flagRequisition = false;
				if(\Storage::disk("public")->exists("/docs/requisition/".$docRequisition))
				{
					$flagRequisition = true;
				}
				elseif(\Storage::disk("public")->exists("/docs/staff/".$docRequisition))
				{
					$flagRequisition = true;
				}
			@endphp
			@component('components.documents.upload-files', 
			[
				"noDelete" 			  => "true",
				"attributeExInput"    => "name=\"path\" accept=\".pdf\"",
				"classExInput"        => "pathActioner",
				"classExContainer"    => isset($employee) && $employee->doc_requisition != "" && $flagRequisition ? "image_success" : (isset($employee_edit) && $employee_edit->doc_requisition != "" && $flagRequisition ? "image_success" : ""),
				"classExRealPath"     => "path",
				"attributeExRealPath" => isset($employee) && $employee->doc_requisition != "" && $flagRequisition ? "name=\"doc_requisition\" value=\"".$employee->doc_requisition."\"" : (isset($employee_edit) && $employee_edit->doc_requisition != "" && $flagRequisition ? "name=\"doc_requisition\" value=\"".$employee_edit->doc_requisition."\"" : "name=\"doc_requisition\""),
			])@endcomponent
		</div>
	@endcomponent
	@component('components.labels.title-divisor')
		Otros documentos
	@endcomponent
	@component('components.containers.container-form')
		<div id="other_documents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6">			
		</div>
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class=\"icon-plus\"></span> Agregar documento",])
				@slot('attributeEx')
					id="add_document"
					type="button"
				@endslot
			@endcomponent
		</div>
	@endcomponent