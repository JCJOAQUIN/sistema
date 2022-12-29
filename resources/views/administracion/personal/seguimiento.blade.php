@extends('layouts.child_module')
@section('css')
@endsection
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
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('staff.follow.update',$request->folio)."\"",
		"methodEx"    => "PUT",
		"token"       => "true"
	])
		@component('components.labels.title-divisor')    Folio: {{ $request->folio }} @endcomponent
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
						@if(isset($request) && $request->status!=2) disabled="disabled" @endif 
						name="title"
						placeholder="Ingrese un título" 
						data-validation="required"
						@if(isset($request)) value="{{ isset($request->staff->first()->title) ? $request->staff->first()->title : "" }}" @endif
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					Fecha: 
				@endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						datepicker remove
					@endslot
					@slot('attributeEx')
						@isset($request) @if($request->status!=2) disabled="disabled" @endif @endif
						readonly="readonly" 
						name="datetitle" 
						placeholder="Ingrese la fecha" 
						data-validation="required" 
						@isset($request) value="{{ isset($request->staff->first()->datetitle) ? Carbon\Carbon::createFromFormat('Y-m-d', $request->staff->first()->datetitle)->format('d-m-Y') : "" }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@php
					$options = collect();
					if(isset($request) &&  isset($request->idRequest))
					{
						$user 	 = App\User::find($request->idRequest);
						$options = $options->concat([['value'=>$user->id, 'selected'=>'selected', 'description'=>$user->name." ".$user->last_name." ".$user->scnd_last_name]]);
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"user_id\" id=\"multiple-users\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"user_id\" id=\"multiple-users\" data-validation=\"required\"";
					}
					
					$classEx     = "js-users removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$options = collect();
					foreach($enterprises as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
						if(isset($request) && $request->idEnterprise==$enterprise->id)
						{
							$options = $options->concat([['value'=>$enterprise->id, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$enterprise->id, 'description'=>$description]]);
						}
					}
					if(isset($request))
					{
						if($request->status != 2) 
						{
							$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\" disabled=\"disabled\"";
						}
						else
						{
							$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\"";
						}
					}
					else
					{
						$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\"";
					}
					$classEx = "js-enterprises removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent
				@php
					$options = collect();
					foreach($areas as $area)
					{
						$description = $area->name;
						if(isset($request) && $request->idArea == $area->id)
						{
							$options = $options->concat([['value'=>$area->id, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$area->id, 'description'=>$description]]);
						}
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"";
					}
					$classEx = "js-areas removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Departamento: @endcomponent
				@php
					$options = collect();
					foreach($departments as $department)
					{
						$description = $department->name;
						if(isset($request) && $request->idDepartment == $department->id)
						{
							$options = $options->concat([['value'=>$department->id, 'selected'=>'selected', 'description'=>$description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$department->id, 'description'=>$description]]);
						}
					}
					if($request->status != 2)
					{
						$attributeEx = "name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\"";
					}
					$classEx = "js-departments removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto/contrato: @endcomponent
				@php
					$options = collect();
					if(isset($request->idProject))
					{
						$project = App\Project::find($request->idProject);
						$options = $options->concat([['value'=>$project->idproyect, 'selected'=>'selected', 'description'=>$project->proyectName]]);
					}
					$attributeEx = "name=\"project_id\" multiple=\"multiple\" data-validation=\"required\" id=\"multiple-projects\" ".($request->status != 2 ? 'disabled' : '') ;
					$classEx     = "js-projects removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
		@endcomponent
		@foreach($request->staff as $staff)
			@if(isset($staff->reason))
				@component('components.labels.title-divisor')    DATOS DE LA VACANTE @endcomponent
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component('components.labels.label') Jefe directo: @endcomponent
						@php
							$options = collect();
							foreach(App\User::orderName()->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->get() as $user)
							{
								$description = $user->name." ".$user->last_name." ".$user->scnd_last_name;
								if($staff->boss == $user->id)
								{
									$options = $options->concat([['value'=>$user->id, 'selected'=>'selected', 'description'=>$description]]);
								}
								else
								{
									$options = $options->concat([['value'=>$user->id, 'description'=>$description]]);
								}
							}
							if($request->status != 2)
							{
								$attributeEx = "name=\"boss_id\" id=\"multiple-boss\" data-validation=\"required\" disabled=\"disabled\"";
							}
							else
							{
								$attributeEx = "name=\"boss_id\" id=\"multiple-boss\" data-validation=\"required\"";
							}
							$classEx = "js-boss removeselect";
						@endphp
						@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Horario: @endcomponent

						@php
							$value_one	="";					
							$value_two	="";										
							if(isset($request))
							{  
								$value_one = $staff->schedule_start; 
							}
							if(isset($request)){ 
								$value_two = $staff->schedule_end; 
							}
							if($request->status != 2)
							{
								$inputs= [
									[
										'input_classEx'     => "time start inline remove disabled",
										'input_attributeEx' => "placeholder=\"Desde\" name=\"schedule_start\" step=\"1\" data-validation=\"required\" disabled=\"disabled\" value=\"".$value_one."\""
									],
									[
										'input_classEx'     => "time end inline remove disabled",
										'input_attributeEx' => "placeholder=\"Hasta\" name=\"schedule_end\" step=\"1\" data-validation=\"required\" disabled=\"disabled\" value=\"".$value_two."\""
									]
								];
							}
							else
							{
								$inputs= [
									[
										'input_classEx'     => "time start inline remove",
										'input_attributeEx' => "name=\"schedule_start\" step=\"1\" data-validation=\"required\" value=\"".$value_one."\""
									],
									[
										'input_classEx'     => "time end inline remove",
										'input_attributeEx' => "name=\"schedule_end\" step=\"1\" data-validation=\"required\" value=\"".$value_two."\""
									]
								];
							}
						@endphp
						@component('components.inputs.range-input',["inputs" => $inputs, "variant" => "time"])
							@slot('attributeEx')
								id="timePair"
							@endslot
							@if(isset($request) && $request->status != 2) 
								@slot("attributeEx")
									disabled="disabled"
								@endslot
							@endif
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Rango de sueldo: @endcomponent
						@php
							$value_one ="";
							$value_two ="";					
							if($staff->minSalary!=''){ 
								$value_one = $staff->minSalary; 
							}else{
								$value_one = 6000;
							}
							if($staff->maxSalary != ''){ 
								$value_two = $staff->maxSalary; ; 
							}else{
								$value_two = 10000;
							}

							if($request->status != 2)
							{
								$inputs= [
									[
										'input_classEx'     => "number remove",
										'input_attributeEx' => "id=\"minSalary\" name=\"minSalary\" data-validation=\"required\" disabled=\"disabled\"  readonly=\"readonly\" placeholder=\"Ingrese el mínimo\" value=\"".$value_one."\""
									],
									[
										'input_classEx'     => "number remove",
										'input_attributeEx' => "id=\"maxSalary\" name=\"maxSalary\" data-validation=\"required\" disabled=\"disabled\"  readonly=\"readonly\" placeholder=\"Ingrese el máximo\" value=\"".$value_two."\""
									]
								];
							}
							else
							{
								$inputs= [
									[
										'input_classEx'     => "number remove",
										'input_attributeEx' => "id=\"minSalary\" name=\"minSalary\" data-validation=\"required\" placeholder=\"Ingrese el mínimo\" value=\"".$value_one."\""
									],
									[
										'input_classEx'     => "number remove",
										'input_attributeEx' => "id=\"maxSalary\" name=\"maxSalary\" data-validation=\"require\" placeholder=\"Ingrese el máximo\" value=\"".$value_two."\""
									]
								];
							}
						@endphp
						@component('components.inputs.range-input',["inputs" => $inputs, "variant" => "time"])
							@if(isset($request) && $request->status != 2) 
								@slot("attributeEx")
									disabled="disabled"
								@endslot
							@endif																
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Motivo: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								@if(isset($request) && $request->status!=2) disabled="disabled" @endif 
								name="reason"
								placeholder="Ingrese el motivo" 
								data-validation="required"
								value="{{ $staff->reason }}"
							@endslot
							@slot('classEx')
								remove
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Puesto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								@if(isset($request) && $request->status!=2) disabled="disabled" @endif 
								name="position" 
								placeholder="Ingrese el puesto" 
								data-validation="required"
								value="{{$staff->position}}"
							@endslot
							@slot('classEx')
								remove
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Periodicidad: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								@if(isset($request) && $request->status!=2) disabled="disabled" @endif 
								name="periodicity" 
								placeholder="Ingrese la periodicidad" 
								data-validation="required"
								value="{{$staff->periodicity}}"
							@endslot
							@slot('classEx')
								remove
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Descripción general de la vacante: @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								name="s_description" 
								cols="30" 
								rows="5" 
								placeholder="Ingrese la descripción" 
								data-validation="required"
								@if($request->status != 2) 
									readonly
								@endif
							@endslot
							@slot('classEx')
								remove
								@if(isset($request) && $request->status!=2) bg-gray-100 @endif 
							@endslot
							{{$staff->description}}
						@endcomponent
					</div>
				@endcomponent
				@if($request->status == 2)
					@component("components.containers.container-form")
						<div class="w-full col-span-5 md:col-span-2 mb-4">
							@component('components.labels.label') Función: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									name="function"
									placeholder="Ingrese la función"
								@endslot
								@slot('classEx')
									quanty
								@endslot
							@endcomponent
						</div>
						<div class="w-full col-span-5 md:col-span-2 mb-4">
							@component('components.labels.label') Descripci&oacute;n: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									name="description"
									placeholder="Ingrese la descripción"
								@endslot
							@endcomponent
						</div>
						<div class="w-full col-span-5 md:col-span-1 mb-4 pt-4">
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('classEx') add2 @endslot
								@slot('attributeEx') id="add" name="add" type="button" @endslot
								<span class="icon-plus"></span>
								<span>Agregar</span>
							@endcomponent
						</div>
					@endcomponent
				@endif
				<div class="w-full mx-3">	
					@isset($staff)
						@php
							$body 			= [];
							$modelBody		= [];

							if($request->status == 2)
							{
								$modelHead = ["Función", "Descripción", ""];
							}
							else
							{
								$modelHead = ["Función", "Descripción"];
							}

							foreach($staff->functions as $function)
							{

								$body = 
								[
									"classEx" => "tr",
									[
										"content" =>
										[
											[
												"label" => $function->function != null ? htmlentities($function->function) : "No hay"
											],
											[
												"kind"        => "components.inputs.input-text", 
												"attributeEx" => "name=\"tfunction[]\" readonly=\"true\" type=\"hidden\" value=\"".htmlentities($function->function)."\""
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => $function->description != null ? htmlentities($function->description) : "No hay"
											],
											[
												"kind"        => "components.inputs.input-text", 
												"classEx"     => "text-center",
												"attributeEx" => "name=\"tdescr[]\" readonly=\"true\" type=\"hidden\" value=\"".htmlentities($function->description)."\""
											]
										]
									]
								];
								if($request->status == 2)
								{
									array_push($body, 
										[
											"content" =>
											[
												[
													"kind"          => "components.buttons.button",
													"buttonElement" => "a",
													"variant"       => "red",
													"label"         => "<span class=\"icon-x\"></span>",
													"classEx"       => "delete-item",
												]
											]
										]
									);
								}
								array_push($modelBody, $body);
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"themeBody" => "striped"
						])
							@slot('classEx')
								text-center
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
					@else
						@component("components.labels.not-found", ["classEx" => "not-found-functions"]) 
							@slot("text")
								No se encontraron funciones registradas 
							@endslot
						@endcomponent
					@endisset
				</div>	
				@component("components.containers.container-form")
					<div class="col-span-2">
						@component('components.labels.label') Habilidades requeridas: @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								name="habilities" 
								cols="30" 
								rows="5"
								placeholder="Ingrese las habilidades requeridas" 
								data-validation="required" 
								@if($request->status != 2) 
									readonly 
								@endif
							@endslot
							@slot('classEx')
								remove
								@if(isset($request) && $request->status!=2) bg-gray-100 @endif 
							@endslot
							@if(isset($request)) 
								{{$staff->habilities}} 
							@endif
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Experiencia deseada: @endcomponent
						@component('components.inputs.text-area')
							@slot('attributeEx')
								name="experience" 
								cols="30" 
								rows="5"
								placeholder="Ingrese la experiencia deseada" 
								data-validation="required"
								@if($request->status != 2) 
									readonly
								@endif
							@endslot
							@slot('classEx')
								remove
								@if(isset($request) && $request->status!=2) bg-gray-100 @endif 
							@endslot
							@if(isset($request)) 
								{{$staff->experience}} 
							@endif
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Responsabilidades: @endcomponent
						@php
							$options = collect();
							
							foreach($responsibilities as $responsibility)
							{
								$temp = $responsibility->id;
								$flag = false;
								$description = $responsibility->responsibility;
								if(isset($request) && count($staff->responsibility) > 0)
								{
									foreach($staff->responsibility as $responsibilityStaff)
									{
										if($temp == $responsibilityStaff->id)
										{
											$options = $options->concat([['value'=>$responsibility->id, 'selected'=>'selected', 'description'=>$description]]);
										}
										else
										{
											$options = $options->concat([['value'=>$responsibility->id, 'description'=>$description]]);
										}
									}
									
								}
								else
								{
									$options = $options->concat([['value'=>$responsibility->id, 'description'=>$description]]);
								}
							}
							if($request->status != 2)
							{
								$attributeEx = "name=\"responsibilities[]\" id=\"multiple-responsibilities\" disabled=\"disabled\"";
							}
							else
							{
								$attributeEx = "name=\"responsibilities[]\" id=\"multiple-responsibilities\"";
							}
							$classEx = "js-responsibilities removeselect";
						@endphp
						@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
						@endcomponent
					</div>
				@endcomponent
				@if($request->status == 2)
					@component("components.containers.container-form")
						<div class="col-span-2">
							@component('components.labels.label') Deseables: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									name="desirable"
									placeholder="Ingrese un dato"
								@endslot
								@slot('classEx')
									quanty
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2">
							@component('components.labels.label') Descripci&oacute;n: @endcomponent
							@component('components.inputs.input-text')
								@slot('attributeEx')
									name="d_description"
									placeholder="Ingrese la descripción"
								@endslot
							@endcomponent
						</div>
						<div class="col-span-2 pt-4">
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('classEx') 
									add2
								@endslot
								@slot('attributeEx') 
									type="button" 
									id="add2" 
									name="add" 
								@endslot
								<span class="icon-plus"></span>
								<span>Agregar</span>
							@endcomponent
						</div>
					@endcomponent
				@endif
				<div class="w-full mx-3">
					@if(isset($staff))
						@php
							$body 			= [];
							$modelBody		= [];
								
							if($request->status == 2)
							{
								$modelHead = ["Deseables", "Descripción", ""];
							}
							else 
							{
								$modelHead = ["Deseables", "Descripción"];
							}
						
							foreach($staff->desirable as $desirable)
							{
								$body = 
								[
									"classEx" => "tr",
									[
										"content" => 
										[
											[
												"label" => $desirable->desirable != null ? htmlentities($desirable->desirable) : "No hay"
											], 
											[
												"kind"        => "components.inputs.input-text", 
												"attributeEx" => "name=\"tdesirable[]\" readonly=\"true\" type=\"hidden\" value=\"".htmlentities($desirable->desirable)."\""
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $desirable->description != null ? htmlentities($desirable->description) : "No hay"
											],
											[
												"kind"        => "components.inputs.input-text", 
												"attributeEx" => "name=\"td_descr[]\" readonly=\"tru\"e type=\"hidden\" value=\"".htmlentities($desirable->description)."\""
											]
										]
									]
								];
								if($request->status == 2)
								{
									array_push($body, 
										[
											"content" => 
											[
												[
													"kind"          => "components.buttons.button",
													"buttonElement" => "a",
													"variant"       => "red",
													"label"         => "<span class=\"icon-x\"></span>",
													"classEx"       => "delete-item"
												]
											]
										]
									);
								}
								array_push($modelBody, $body);
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"themeBody" => "striped"
						])
							@slot('classEx')
								text-center
							@endslot
							@slot('attributeEx')
								id="table"
							@endslot
							@slot('attributeExBody')
								id="body2"
							@endslot
							@slot('classExBody')
								request-validate
							@endslot
						@endcomponent
					@else
						@component("components.labels.not-found", ["classEx" => "not-found-responsabilities"]) 
							@slot("text")
								No se encontraron responsabilidades registradas 
							@endcomponent
						@endcomponent
					@endisset
				</div>
				@if($request->idCheck != "")
					@component('components.labels.title-divisor')    DATOS DE REVISIÓN @endcomponent
					<div class="flex px-6">
						<div>
							@component('components.tables.table-request-detail.container',['variant'=>'simple'])
								@php
									$modelTable = [];
									$modelTable["Revisó"] = $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name;
									if($request->status == 4)
									{
										$modelTable["Nombre de la Empresa"]    = App\Enterprise ::find($request->idEnterpriseR)->name;
										$modelTable["Nombre del Departamento"] = App\Department::find($request->idDepartamentR)->name;
										$modelTable["Nombre de la Dirección"]  = $request->reviewedDirection->name;
										$modelTable["Nombre del Proyecto"]     = $request->reviewedProject->proyectName;
										$labels = "";
										foreach($request->labels as $label)
										{
											$labels = $labels." ".$label->description; 
										}
										$modelTable["Etiquetas"]  = $labels;
									}
									$modelTable["Comentarios"] = !empty($request->checkComment) ? htmlentities($request->checkComment) : "Sin comentarios";
								@endphp
								@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
								@endcomponent
							@endcomponent
						</div>
					</div>
				@endif
				@if($request->idAuthorize != "")
					@component('components.labels.title-divisor')    DATOS DE AUTORIZACIÓN @endcomponent
					<div class="flex px-6">
						<div >
							@component('components.tables.table-request-detail.container',['variant'=>'simple'])
								@php
									$modelTable                = [];
									$modelTable["Autorizó"]    = $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name;
									$modelTable["Comentarios"] = !empty($request->authorizeComment) ? htmlentities($request->authorizeComment) : "Sin comentarios";
								@endphp
								@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
								@endcomponent
							@endcomponent
						</div>
					</div>
				@endif
			@else
				<div class="w-full mx-3">
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= ["Nombre", "Puesto", "Acciones"];
						if(isset($request) && $request->staff()->exists())
						{
							foreach($staff->staffEmployees as $key => $emp)
							{
								$actionTd = "<span>
											<input type=\"hidden\" name=\"rq_employee_id[]\" value=\"".$emp->id."\">
											<input type=\"hidden\" name=\"rq_name[]\" value=\"".$emp->name."\">
											<input type=\"hidden\" name=\"rq_last_name[]\" value=\"".$emp->last_name."\">
											<input type=\"hidden\" name=\"rq_scnd_last_name[]\" value=\"".$emp->scnd_last_name."\">
											<input type=\"hidden\" name=\"rq_curp[]\" value=\"".$emp->curp."\">
											<input type=\"hidden\" name=\"rq_rfc[]\" value=\"".$emp->rfc."\">
											<input type=\"hidden\" name=\"rq_tax_regime[]\" value=\"".$emp->tax_regime."\">
											<input type=\"hidden\" name=\"rq_imss[]\" value=\"".$emp->imss."\">
											<input type=\"hidden\" name=\"rq_email[]\" value=\"".$emp->email."\">
											<input type=\"hidden\" name=\"rq_phone[]\" value=\"".$emp->phone."\">
											<input type=\"hidden\" name=\"rq_street[]\" value=\"".$emp->street."\">
											<input type=\"hidden\" name=\"rq_number_employee[]\" value=\"".$emp->number."\">
											<input type=\"hidden\" name=\"rq_colony[]\" value=\"".$emp->colony."\">
											<input type=\"hidden\" name=\"rq_cp[]\" value=\"".$emp->cp."\">
											<input type=\"hidden\" name=\"rq_city[]\" value=\"".$emp->city."\">
											<input type=\"hidden\" name=\"rq_state[]\" value=\"".$emp->state."\" data-description=\"".(isset($emp->state) ? App\State::find($emp->state)->description : "")."\">
											<input type=\"hidden\" name=\"rq_work_state[]\" value=\"".$emp->state_id."\" data-description=\"".(isset($emp->state) ? App\State::find($emp->state_id)->description : "")."\">
											<input type=\"hidden\" name=\"rq_work_project[]\" value=\"".$emp->project."\">
											<input type=\"hidden\" name=\"rq_work_wbs[]\" value=\"".$emp->wbs_id."\" data-description=\"".(isset($emp->wbs_id) ? App\CatCodeWBS::find($emp->wbs_id)->code_wbs : "")."\">
											<input type=\"hidden\" name=\"rq_work_enterprise[]\" value=\"".$emp->enterprise."\">
											<input type=\"hidden\" name=\"rq_work_account[]\" value=\"".$emp->account."\" data-description=\"".(isset($emp->account) ? App\Account::find($emp->account)->account." - ".App\Account::find($emp->account)->description." (".App\Account::find($emp->account)->content.")" : "")."\">
											<input type=\"hidden\" name=\"rq_work_direction[]\" value=\"".$emp->direction."\">
											<input type=\"hidden\" name=\"rq_work_department[]\" value=\"".$emp->department."\">
											<input type=\"hidden\" name=\"rq_work_position[]\" value=\"".$emp->position."\">
											<input type=\"hidden\" name=\"rq_work_immediate_boss[]\" value=\"".$emp->immediate_boss."\">
											<input type=\"hidden\" name=\"rq_work_income_date[]\" value=\"".($emp->admissionDate != '' ? $emp->admissionDate->format('d-m-Y') : '')."\">
											<input type=\"hidden\" name=\"rq_work_status_imss[]\" value=\"".$emp->status_imss."\">
											<input type=\"hidden\" name=\"rq_work_imss_date[]\" value=\"".($emp->imssDate != '' ? $emp->imssDate->format('d-m-Y') : '' )."\">
											<input type=\"hidden\" name=\"rq_work_down_date[]\" value=\"".($emp->downDate != '' ? $emp->downDate->format('d-m-Y') : '' )."\">
											<input type=\"hidden\" name=\"rq_work_ending_date[]\" value=\"".($emp->endingDate != '' ? $emp->endingDate->format('d-m-Y') : '' )."\">
											<input type=\"hidden\" name=\"rq_work_reentry_date[]\" value=\"".($emp->reentryDate != '' ? $emp->reentryDate->format('d-m-Y') : '')."\">
											<input type=\"hidden\" name=\"rq_work_type_employee[]\" value=\"".$emp->workerType."\">
											<input type=\"hidden\" name=\"rq_regime_employee[]\" value=\"".$emp->regime_id."\">
											<input type=\"hidden\" name=\"rq_work_status_employee[]\" value=\"".$emp->workerStatus."\">
											<input type=\"hidden\" name=\"rq_work_status_reason[]\" value=\"".$emp->status_reason."\">
											<input type=\"hidden\" name=\"rq_work_sdi[]\" value=\"".$emp->sdi."\">
											<input type=\"hidden\" name=\"rq_work_periodicity[]\" value=\"".$emp->periodicity."\">
											<input type=\"hidden\" name=\"rq_work_employer_register[]\" value=\"".$emp->employer_register."\">
											<input type=\"hidden\" name=\"rq_work_payment_way[]\" value=\"".$emp->paymentWay."\">
											<input type=\"hidden\" name=\"rq_work_net_income[]\" value=\"".$emp->netIncome."\">
											<input type=\"hidden\" name=\"rq_work_complement[]\" value=\"".$emp->complement."\">
											<input type=\"hidden\" name=\"rq_work_fonacot[]\" value=\"".$emp->fonacot."\">
											<input type=\"hidden\" name=\"rq_work_infonavit_credit[]\" value=\"".$emp->infonavitCredit."\">
											<input type=\"hidden\" name=\"rq_work_infonavit_discount[]\" value=\"".$emp->infonavitDiscount."\">
											<input type=\"hidden\" name=\"rq_work_infonavit_discount_type[]\" value=\"".$emp->infonavitDiscountType."\">
											<input type=\"hidden\" name=\"rq_work_alimony_discount_type[]\" value=\"".$emp->alimonyDiscountType."\">
											<input type=\"hidden\" name=\"rq_work_alimony_discount[]\" value=\"".$emp->alimonyDiscount."\">
											<input type=\"hidden\" name=\"rq_replace[]\" value=\"".$emp->replace."\">
											<input type=\"hidden\" name=\"rq_purpose[]\" value=\"".$emp->purpose."\">
											<input type=\"hidden\" name=\"rq_requeriments[]\" value=\"".$emp->requeriments."\">
											<input type=\"hidden\" name=\"rq_observations[]\" value=\"".$emp->observations."\">
											<input type=\"hidden\" name=\"rq_work_viatics[]\" value=\"".$emp->viatics."\">
											<input type=\"hidden\" name=\"rq_work_camping[]\" value=\"".$emp->camping."\">
											<input type=\"hidden\" name=\"rq_work_position_immediate_boss[]\" value=\"".$emp->position_immediate_boss."\">
											<input type=\"hidden\" name=\"rq_work_subdepartment[]\" value=\"".$emp->subdepartment_id."\" data-description=\"".(isset($emp->subdepartment_id) ? App\Subdepartment::find($emp->subdepartment_id)->name : "")."\">
											<input type=\"hidden\" name=\"rq_doc_birth_certificate[]\" value=\"".$emp->doc_birth_certificate."\">
											<input type=\"hidden\" name=\"rq_doc_proof_of_address[]\" value=\"".$emp->doc_proof_of_address."\">
											<input type=\"hidden\" name=\"rq_doc_nss[]\" value=\"".$emp->doc_nss."\">
											<input type=\"hidden\" name=\"rq_doc_ine[]\" value=\"".$emp->doc_ine."\">
											<input type=\"hidden\" name=\"rq_doc_curp[]\" value=\"".$emp->doc_curp."\">
											<input type=\"hidden\" name=\"rq_doc_rfc[]\" value=\"".$emp->doc_rfc."\">
											<input type=\"hidden\" name=\"rq_doc_cv[]\" value=\"".$emp->doc_cv."\">
											<input type=\"hidden\" name=\"rq_doc_proof_of_studies[]\" value=\"".$emp->doc_proof_of_studies."\">
											<input type=\"hidden\" name=\"rq_doc_professional_license[]\" value=\"".$emp->doc_professional_license."\">
											<input type=\"hidden\" name=\"rq_doc_requisition[]\" value=\"".$emp->doc_requisition."\">
											<input type=\"hidden\" name=\"rq_computer_required[]\" value=\"".$emp->computer_required."\">
											<input type=\"hidden\" name=\"rq_qualified_employee[]\" value=\"".$emp->qualified_employee."\">";
								if(isset($emp->staffAccounts))
								{
									foreach($emp->staffAccounts as $acc)
									{
										$actionTd .= $acc->type == '1' ? "<div class=\"container-accounts\">" : "<div class=\"container-accounts-alimony\">";
										$actionTd .= "<input type=\"hidden\" class=\"t_alias\" name=\"alias_".$key."[]\" value=\"".$acc->alias."\">
														<input type=\"hidden\" class=\"t_beneficiary\" name=\"beneficiary_".$key."[]\" value=\"".$acc->beneficiary."\">
														<input type=\"hidden\" class=\"t_type\" name=\"type_".$key."[]\" value=\"".$acc->type."\">
														<input type=\"hidden\" class=\"t_idEmployee\" name=\"idEmployee_".$key."[]\" value=\"".$acc->idEmployee."\">
														<input type=\"hidden\" class=\"t_idCatBank\" name=\"idCatBank_".$key."[]\" value=\"".$acc->id_catbank."\">
														<input type=\"hidden\" class=\"t_clabe\" name=\"clabe_".$key."[]\" value=\"".$acc->clabe."\">
														<input type=\"hidden\" class=\"t_account\" name=\"account_".$key."[]\" value=\"".$acc->account."\">
														<input type=\"hidden\" class=\"t_cardNumber\" name=\"cardNumber_".$key."[]\" value=\"".$acc->cardNumber."\">
														<input type=\"hidden\" class=\"t_branch\" name=\"branch_".$key."[]\" value=\"".$acc->branch."\">
														<input type=\"hidden\" class=\"t_bankName\" name=\"bankName_".$key."[]\" value=\"".$acc->bank->description."\">
													</div>";
									}
								}
								if(isset($emp->staffDocuments))
								{
									foreach($emp->staffDocuments as $doc)
									{
										$actionTd .= "<div class=\"container-other-documents\">
														<input type=\"hidden\" class=\"t_name_other_document\" name=\"name_other_document_".$key."[]\" value=\"".$doc->name."\">
														<input type=\"hidden\" class=\"t_path_other_document\" name=\"path_other_document_".$key."[]\" value=\"".$doc->path."\">
													</div>";
									}
								}
								$actionTd .= "</span>";
								$body = 
								[
									[
										"content" => 
										[
											[
												"label" => $emp->fullName()
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" =>  $emp->position
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $actionTd
											],
											[
												"kind"        => "components.buttons.button", 
												"classEx"     => !in_array($request->status, [2]) ? "edit-employee hidden" : "edit-employee",
												"variant"     => "success",
												"label"       => "<span class=\"icon-pencil\"></span>",
												"attributeEx" => "type=\"button\" data-toggle=\"modal\" data-target=\"#addEmployee\""
											],
											[
												"kind"        => "components.buttons.button", 
												"classEx"     => in_array($request->status, [2]) ? "view-employee hidden" : "view-employee",
												"variant"     => "secondary",
												"label"       => "<span class=\"icon-search\"></span>",
												"attributeEx" => "type=\"button\" data-toggle=\"modal\" data-target=\"#detailEmployee\""
											],
											[
												"kind"        => "components.buttons.button", 
												"attributeEx" => "type=\"button\"",
												"classEx" 	  => !in_array($request->status, [2]) ? "delete-employee hidden" : "delete-employee",
												"variant" 	  => "red",
												"label"   	  => "<span class=\"icon-x\"></span>"
											]
										]
									]
								];
								array_push($modelBody, $body);
							}
						}
					@endphp
					@component('components.tables.alwaysVisibleTable',[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"variant"	=> "default"
					])
						@slot('classEx')text-center @endslot
						@slot('attributeExBody')
							id="list_employees"
						@endslot
					@endcomponent
					@component("components.buttons.button",
					[
						"attributeEx" => "type=\"button\" id=\"btnAddEmployee\"  data-toggle=\"modal\" data-target=\"#addEmployee\"",
						"label"       => "<span class=\"icon-plus\"></span> Agregar Empleado",
						"variant"     => "warning",
						"classEx"     => !in_array($request->status, [2]) ? "hidden" : "h"
					])
					@endcomponent
				</div>
			@endif
		@endforeach
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
						type="submit"  
						name="save" 
						id="save" 
						formaction="{{ route('staff.follow.updateunsent', $request->folio) }}"
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
		</div>
		<div id="delete_employee"></div>
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			detailEmployee
		@endslot
		@slot('attributeEx')
			tabindex="-1" role="document"
		@endslot
		@slot('modalHeader')
			@component('components.buttons.button')
				@slot('attributeEx')
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
		@endslot
		@slot('classExBody') modal-employee @endslot
		@slot('modalFooter')
			<div class="text-center">
				@component('components.buttons.button',[
					"variant" => "red"
					])
					@slot('classeEx')
						disable-button
					@endslot
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			</div>
		@endslot
	@endcomponent
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"form_employee\""
	])
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				addEmployee
			@endslot
			@slot('attributeEx')
				tabindex="-1" role="document"
			@endslot
			@slot('modalHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot('modalBody')
				@php
					$employee_new = 1;
				@endphp
				@include('configuracion.empleado.parcial')
			@endslot
			@slot('modalFooter')
				<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button',[
						"variant" => "secondary"
						])
						@slot('attributeEx')
							type="submit"
							id="save_employee"
						@endslot
						<span class="icon-plus"></span>
						<span>Guardar</span>
					@endcomponent
					@component('components.buttons.button',[
						"variant" => "red"
						])
						@slot('attributeEx')
							type="button"
							data-dismiss="modal"
						@endslot
						<span class="icon-x"></span> Cerrar
					@endcomponent
				</div>
			@endslot
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script>
		function containerAltaValidation()
		{
			$.validate(
			{
				form: '#container-alta',
				modules	: 'security',
				onError   : function($form)
				{
					$('.error-extra').removeClass('error');
					$('.error-extra').addClass('error');
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					@if(isset($globalRequests) && $globalRequests == true)
						if($('.request-validate').length>0)
						{
							$('.error-extra').removeClass('error');
							funciones	= $('#body .tr').length;
							deseables	= $('#body2 .tr').length;
							if(funciones>0 && deseables>0)
							{
								if(flagSalary)
								{
									if(moment('2016-10-08 '+$('#timePair .time.end').val()+':00').diff(moment('2016-10-08 '+$('#timePair .time.start').val()+':00')) >= 1800000)
									{
										swal('Cargando',{
											icon: '{{ asset(getenv("LOADING_IMG")) }}',
											button: false,
										});
										return true;
									}
									else
									{
										swal('', 'El rango de horario laboral debe ser mínimo de 30 minutos', 'error');
										return false;
									}
								}
								else
								{
									swal('', 'El salario máximo debe ser mayor al salario mínimo', 'error');
									return false;
								}
							}
							else
							{
								swal('', 'Debe registrar al menos una función y una habilidad deseable para la vacante', 'error');
								return false;
							}
						}
						else
						{
							swal('Cargando',{
								icon: '{{ asset(getenv("LOADING_IMG")) }}',
								button: false,
							});
							return true;
						}
					@else
						if($('form#container-alta').attr('action') != $('#save').attr('formaction'))
						{
							conceptos = $('#list_employees .tr').length;
							if (conceptos > 0)
							{
								flagEmployee = true;
								$('#list_employees .tr').each(function(i,v)
								{
									rq_qualified_employee 		= $(this).find('[name="rq_qualified_employee[]"]').val();
									rq_doc_birth_certificate	= $(this).find('[name="rq_doc_birth_certificate[]"]').val();
									rq_doc_proof_of_address		= $(this).find('[name="rq_doc_proof_of_address[]"]').val();
									rq_doc_nss					= $(this).find('[name="rq_doc_nss[]"]').val();
									rq_doc_ine					= $(this).find('[name="rq_doc_ine[]"]').val();
									rq_doc_curp					= $(this).find('[name="rq_doc_curp[]"]').val();
									rq_doc_rfc					= $(this).find('[name="rq_doc_rfc[]"]').val();
									rq_doc_cv					= $(this).find('[name="rq_doc_cv[]"]').val();
									rq_doc_proof_of_studies		= $(this).find('[name="rq_doc_proof_of_studies[]"]').val();
									rq_doc_professional_license	= $(this).find('[name="rq_doc_professional_license[]"]').val();
									rq_doc_requisition			= $(this).find('[name="rq_doc_requisition[]"]').val();
									if (rq_qualified_employee == "1" && (rq_doc_birth_certificate == "" || rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_curp == "" || rq_doc_rfc == "" || rq_doc_cv == "" || rq_doc_proof_of_studies == ""|| rq_doc_professional_license == "" || rq_doc_requisition == "")) 
									{
										flagEmployee = false;
										$(this).addClass('tr-red');
									}
									else if(rq_qualified_employee != "1" && (rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_rfc == "" || rq_doc_requisition == ""))
									{
										flagEmployee = false;
										$(this).addClass('tr-red');
									}
									else
									{
										$(this).removeClass('tr-red');
									}
								});

								if (flagEmployee) 
								{
									swal("Cargando",
									{
										icon				: '{{ asset(getenv('LOADING_IMG')) }}',
										button				: false,
										closeOnClickOutside	: false,
										closeOnEsc			: false
									});
									return true;
								}
								else
								{
									swal('', 'Por favor anexe todos los documentos a los empleados marcados en rojo.', 'error');
									return false;
								}
							}
							else
							{
								swal('', 'Debe ingresar al menos un empleado', 'error');
								return false;
							}
						}
					@endif
				}
			});
		}
		$(document).ready(function()
		{
			dataEmployee();
			containerAltaValidation();
			saveEmployeeValidation();
			generalSelect({'selector': '.js-users', 'model': 36});
			generalSelect({'selector': '.js-projects', 'model': 24});
			generalSelect({'selector': '.js-boss', 'model': 36});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa", 
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
						"identificator"          => ".js-responsibilities", 
						"placeholder"            => "Seleccione las responsabilidades"
					],
					[
						"identificator"          => ".js-roles", 
						"placeholder"            => "Seleccione el rol", 
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			var flagSalary = true;
			$('.number',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			$(function() 
			{
				$( ".datepicker" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
			$('#timePair .time.start').timepicker(
			{
				'timeFormat'	: 'H:i',
				'step'			: 30,
				'maxTime'		: '22:00:00',
				'minTime'		: '05:00:00',
			});
			$('#timePair .time.end').timepicker(
			{
				'showDuration'	: true,
				'timeFormat'	: 'H:i',
				'step'			: 30,
				'maxTime'		: '22:00:00',
				'minTime'		: '05:00:00',
			});
			$('#timePair .time.end').on('selectTime', function() 
			{
				// La fecha 2016-10-08 así como el postfijo :00.000 se utilizan sólo para cumplir el formato ISO, favor de dejarlos ahí
				difMomment = moment('2016-10-08 '+$('#timePair .time.end').val()+':00').diff(moment('2016-10-08 '+$('#timePair .time.start').val()+':00'));
			});
			$('#timePair').datepair();
		})
		.on('click', '#btnAddEmployee', function()
		{
			project_id = $('option:selected','[name="project_id"]').val();
			if (project_id != undefined) 
			{
				$('[name="work_wbs[]"]').empty();
				$('.select_father').hide();
				$.each(generalSelectProject, function(i,v)
				{
					if(project_id == v.id)
					{
						if(v.flagWBS != null)
						{
							$('.select_father').show();
							generalSelect({'selector': '[name="work_wbs[]"]', 'depends':'.js-projects', 'model':1});
						}
						else
						{
							$('.select_father').hide();
						}
					}
				});
			}
			else
			{
				$('[name="work_wbs[]"]').empty();
				$('.select_father').hide();
			}
			
			$("#bodyEmployee .tr").each(function()
			{
				$(this).remove();
			});
			$('#bank-data-register').parent().addClass('hidden');
			$('#not-found-accounts').removeClass('hidden');

			$("#bodyAlimony .tr").each(function()
			{
				$(this).remove();
			});
			$('#bank-data-register-alimony').parent().addClass('hidden');
			$('#not-found-accounts-alimony').removeClass('hidden');

			$('#form_employee').find('#alimony').prop('checked',false);
			
			//$(this).parents('table').find('tbody').stop(true,true).fadeOut(); incluso en obra no se utiliza
			$('#accounts-alimony').stop(true,true).fadeOut();
			$('#infonavit-form').fadeOut();
			$('#other_documents').empty();
			$('.name-other-document').parents('.tr').remove();
			$(".pathActioner").removeClass("hidden");
			dataEmployee();
			$('.project-class').hide();
		})
		.on('keyup','.time',function(e)
		{
			$(this).val('');
		})
		.on('click','[data-dismiss="modal"]',function()
		{
			if ($('#list_employeetr').length > 0) 
			{
				$('#list_employeetr').each(function()
				{
					$(this).removeClass('active')
				});
			}

			datas = $('#form_employee').serializeArray();
			
			$.each(datas,function(i,input)
			{
				if (input.name != "qualified_employee") 
				{
					$('#form_employee').find('[name="'+input.name+'"]').val('');
					$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
					$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
					$('#form_employee').find('[name="'+input.name+'"]').parent().find('.form-error').remove();
					$('#form_employee').find('[name="'+input.name+'"]').parent().find('.help-block').remove();
					$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
				}
			});
			
			$('#form_employee').find('[name="employee_id"]').val('x');
			$('#form_employee').find('.uploader-content').removeClass('image_success');
			$('.doc_birth_certificate').empty().text('Sin documento');
			$('.doc_proof_of_address').empty().text('Sin documento');
			$('.doc_nss').empty().text('Sin documento');
			$('.doc_ine').empty().text('Sin documento');
			$('.doc_curp').empty().text('Sin documento');
			$('.doc_rfc').empty().text('Sin documento');
			$('.doc_cv').empty().text('Sin documento');
			$('.doc_proof_of_studies').empty().text('Sin documento');
			$('.doc_professional_license').empty().text('Sin documento');
			$('.doc_requisition').empty().text('Sin documento');
			$('#documents_employee .tr-remove').remove();
			$('#other_documents').empty();
			$(".pathActioner").removeClass("hidden");
			$('#frame').removeAttr('src');
		})
		.on('click','.delete-bank', function()
		{
			$(this).parents('.tr').remove();
			if($('#bank-data-register .tr').length == 0)
			{
				$('#bank-data-register').parent().addClass('hidden');
				$('#not-found-accounts').removeClass('hidden');
			}
		})
		.on('click','.delete-bank-alimony', function()
		{
			$(this).parents('.tr').remove();
			if($('#bodyAlimony .tr').length == 0)
			{
				$('#bank-data-register-alimony').parent().addClass('hidden');
				$('#not-found-accounts-alimony').removeClass('hidden');
			}
		})
		.on('change','.pathActioner',function(e)
		{
			filename     = $(this);
			uploadedName = $(this).parent('.uploader-content').siblings('.path');
			extention    = /\.pdf/i;
			if(filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione un archivo pdf', 'warning');
				$(this).val('');
			}
			else if(this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).addClass('hidden').parents('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData = new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$('.disable-button').prop('disabled', true);				
				$.ajax(
				{
					type       : 'post',
					url        : '{{ route("staff.upload") }}',
					data       : formData,
					contentType: false,
					processData: false,
					success    : function(r)
					{
						if(r.error == 'DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parents('.docs-p').find('.path').val(r.path);
							$(e.currentTarget).val('');
							$(".pathActioner").removeClass("hidden");
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parents('.docs-p').find('.path').val('');
							$(".pathActioner").removeClass("hidden");
						}
						
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parents('.docs-p').find('.path').val('');
						$(".pathActioner").removeClass("hidden");
					}
				}).done(function() {
					$('.disable-button').prop('disabled', false);
				});
			}
		})
		.on('click','.delete-employee',function()
		{
			id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
			if (id != "x") 
			{
				$('#delete_employee').append($('<input type="hidden" class="asd" name="delete_employee[]" value="'+id+'">'));
				$(this).parents('.tr').remove();
			}

			$('#list_employees .tr').each(function(i,v)
			{
				$(this).find('.t_alias').attr('name','alias_'+i+'[]');
				$(this).find('.t_beneficiary').attr('name','beneficiary_'+i+'[]');
				$(this).find('.t_type').attr('name','type_'+i+'[]');
				$(this).find('.t_idEmployee').attr('name','idEmployee_'+i+'[]');
				$(this).find('.t_idCatBank').attr('name','idCatBank_'+i+'[]');
				$(this).find('.t_clabe').attr('name','clabe_'+i+'[]');
				$(this).find('.t_account').attr('name','account_'+i+'[]');
				$(this).find('.t_cardNumber').attr('name','cardNumber_'+i+'[]');
				$(this).find('.t_branch').attr('name','branch_'+i+'[]');
				$(this).find('.t_name_other_document').attr('name','name_other_document_'+i+'[]');
				$(this).find('.t_path_other_document').attr('name','path_other_document_'+i+'[]');
			});
		})
		.on('click','#add',function()
		{
			func	= $('input[name="function"]').removeClass('error').val().trim();
			descr	= $('input[name="description"]').removeClass('error').val().trim();
			if (func == "" || descr == "")
			{
				if(func == "")
				{
					$('input[name="function"]').addClass('error');
				}
				if(descr == "")
				{
					$('input[name="description"]').addClass('error');
				}
				swal('', 'Por favor llene los campos necesarios', 'error');
			}
			else
			{
				@php
					$modelHead = ['Función', 'Descripción', 'Acción'];
					$modelBody = 
					[
						[ 	
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind"    => "components.labels.label",
										"classEx" => "function",
									], 
									[
										"kind"        => "components.inputs.input-text", 
										"attributeEx" => "name=\"tfunction[]\" readonly=\"true\" type=\"hidden\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"    => "components.labels.label", 
										"classEx" => "description"
									],
									[
										"kind"        => "components.inputs.input-text", 
										"attributeEx" => "name=\"tdescr[]\" readonly=\"true\" type=\"hidden\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"          => "components.buttons.button",
										"buttonElement" => "a",
										"variant"       => "red",
										"label"         => "<span class=\"icon-x\"></span>",
										"classEx"       => "delete-item"
									]
								]
							]
						]
					];
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"    => true
					])->render();
					$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row=$(table);
				row = rowColor('#body', row);
				row.find('.description').text(descr);
				row.find('.function').text(func);
				row.find('[name="tfunction[]"]').val(func);
				row.find('[name="tdescr[]"]').val(descr);
				$('#body').append(row);
				$('input[name="function"]').val("");
				$('input[name="description"]').val("");
			}
		})
		.on('change','#infonavit',function()
		{
			if($(this).is(':checked'))
			{
				$('.infonavit-container').stop(true,true).fadeIn();
				@php
					$selects = collect([
						[
							"identificator"          => "[name=\"work_infonavit_discount_type\"]", 
							"placeholder"            => "Seleccione el tipo de descuento", 
							"maximumSelectionLength" => "1",
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])
				@endcomponent
			}
			else
			{
				$('.infonavit-container').stop(true,true).fadeOut();
			}
		})
		.on('change','[name="work_enterprise"]',function()
		{
			$('[name="work_account"]').html('');
			$('[name="work_employer_register"]').html('');
		})
		.on('change','#alimony',function()
		{
			if($(this).is(':checked'))
			{
				$('.alimony-container').stop(true,true).fadeIn();
				$('#accounts-alimony').stop(true,true).fadeIn();
				generalSelect({'selector': '.bank', 'model': 28});
				@php
					$selects = collect([
						[
							"identificator"          => "[name=\"work_alimony_discount_type\"]", 
							"placeholder"            => "Seleccione el tipo de descuento", 
							"maximumSelectionLength" => "1",
						]
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])
				@endcomponent
			}
			else
			{
				$('.alimony-container').stop(true,true).fadeOut();
				$('#accounts-alimony').stop(true,true).fadeOut();
			}
		})
		.on('click','#add-bank',function()
		{
			$(this).parent().siblings().find('.clabe').removeClass('error');
			$(this).parent().siblings().find('.account').removeClass('error');
			$(this).parent().siblings().find('.card').removeClass('error');
			
			alias       = $(this).parent().siblings().find('.alias').val();
			bankid      = $(this).parent().siblings().find('.bank').val();
			bankName    = $(this).parent().siblings().find('.bank :selected').text();
			clabe       = $(this).parent().siblings().find('.clabe').val();
			account     = $(this).parent().siblings().find('.account').val();
			card        = $(this).parent().siblings().find('.card').val();
			branch      = $(this).parent().siblings().find('.branch_office').val();

			if(alias == "")
			{
				swal('', 'Por favor ingrese un alias', 'error');
				$('.alias').addClass('error');
			}
			else if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$(this).parent().parent().find('.card, .clabe, .account').removeClass('valid').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if (alias == "")
				{
					$(this).parent().parent().find(".alias").addClass("error");
					swal("", "Debe ingresar todos los campos requeridos", "error");
				}
				else if(clabe != "" && ($(this).parent().parent().find(".clabe").hasClass("error") || clabe.length!=18))
				{
					swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
					$(".clabe").addClass("error");
				}
				else if(card != "" && ($(this).parent().parent().find(".card-number").hasClass("error") || card.length!=16))
				{
					swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
					$(this).parent().parent().find(".card-number").addClass("error");
				}
				else if(account != "" && ($(this).parent().parent().find(".account").hasClass("error") || (account.length>15 || account.length<5)))
				{
					swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
					$(this).parent().parent().find(".account").addClass("error");
				}
				else 
				{
					flag = false;
					$('#bodyEmployee .tr').each(function()
					{
						name_account 	= $(this).find('[name="account[]"]').val();
						name_clabe		= $(this).find('[name="clabe[]"]').val();
						name_bank		= $(this).find('[name="bank[]"]').val();

						if(clabe!= "" && name_clabe !="" && clabe == name_clabe)
						{
							swal('','La CLABE ya se encuentra registrada para este empleado.','error');
							$('.clabe').removeClass('valid').addClass('error');
							flag = true;
						}
						if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
						{
							swal('','El número de Cuenta ya se encuentra registrada para este empleado.','error');
							$('.acount').removeClass('valid').addClass('error');
							flag = true;
						}
					});
					if(!flag)
					{
						@php
							$body 		= [];
							$modelBody	= [];
							$modelHead  = ["Alias", "Banco", "Clabe", "Cuenta", "Tarjeta", "Sucursal", "Acción"];
							$body = 
							[
								[
									"content" => 
									[
										[
											"kind" 	  		=> "components.labels.label",
											"classEx" 		=> "alias-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"alias[]\""
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\""
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"type_account[]\""
										]

									]
								],
								[ 
									"content" => 
									[ 
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "bank-new",
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\"",
											"classEx"     => "idEmployee"
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"bank[]\""
										],
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 	  	=> "clabe-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 		=> "account-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx"		=> "card-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 		=> "branch-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"branch[]\""
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
							
							array_push($modelBody, $body);
							$table_accounts = view('components.tables.alwaysVisibleTable',[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"    => true
							])->render();
						@endphp
						tr = $('{!!preg_replace("/(\r)*(\n)*/", "", $table_accounts)!!}');
						tr.find('.alias-new').append(alias =='' ? ' --- ' :alias);
						tr.find("[name=\"alias[]\"]").val(alias);
						tr.find("[name=\"type_account[]\"]").val("1");
						tr.find('.bank-new').append(bankName);
						tr.find("[name=\"idEmployeeBank[]\"]").val("x");
						tr.find("[name=\"bank[]\"]").val(bankid);
						tr.find('.clabe-new').append(clabe =='' ? ' --- ' :clabe);
						tr.find("[name=\"clabe[]\"]").val(clabe);
						tr.find('.account-new').append(account =='' ? ' --- ' :account);
						tr.find("[name=\"account[]\"]").val(account);
						tr.find('.card-new').append(card =='' ? ' --- ' :card);
						tr.find("[name=\"card[]\"]").val(card);
						tr.find('.branch-new').append(branch =='' ? ' --- ' :branch);
						tr.find("[name=\"branch[]\"]").val(branch);

						$('#bank-data-register #bodyEmployee').append(tr);
						$('.card, .clabe, .account, .alias,.branch_office').removeClass('error').removeClass('valid').val('');
						$('.bank').val(0).trigger("change");
						$('#bank-data-register').parent().removeClass('hidden');
						$('#not-found-accounts').addClass('hidden');
					}
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.bank').addClass('error');
			}
		})
		.on('click','#add_document',function()
		{
			@php
				$options = collect([
					["value"=>"Aviso de retención por crédito Infonavit", "description"=>"Aviso de retención por crédito Infonavit"], 
					["value"=>"Estado de cuenta", "description"=>"Estado de cuenta"], 
					["value"=>"Cursos de capacitación", "description"=>"Cursos de capacitación"], 
					["value"=>"Carta de recomendación", "description"=>"Carta de recomendación"], 
					["value"=>"Identificación", "description"=>"Identificación"], 
					["value"=>"Hoja de expediente", "description"=>"Hoja de expediente"]
				]);
				$docs = view('components.documents.upload-files',
				[
					"classExInput"			=> "pathActioner",
					"classEx"				=> "form_other_doc",
					"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
					"classExDelete"			=> "delete_other_doc",
					"attributeExRealPath"	=> "type=\"hidden\" name=\"path_other_document[]\"",
					"classExRealPath"		=> "path path_other_document",
					"componentsExUp"		=>	
					[
						[
							"kind" 			=> "components.labels.label", 
							"label" 		=> "Seleccione el tipo de documento:"
						],
						[
							"kind" 			=> "components.inputs.select", 
							"options"		=> $options,
							"classEx"		=> "name_other_document",
							"attributeEx"	=> "name=\"name_other_document[]\" data-validation=\"required\" multiple=\"multiple\"",
						]
					]
				])->render();
			@endphp
			doc = $('{!!preg_replace("/(\r)*(\n)*/", "", $docs)!!}');
			$('#other_documents').append(doc);
			@php
				$selects = collect([
					[
						"identificator"          => "[name=\"name_other_document[]\"]", 
						"placeholder"            => "Seleccione el tipo de documento",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		})
		.on('click','.delete_other_doc',function()
		{
			$(this).parents('.docs-p').remove();
		})
		.on('click','#add-bank-alimony',function()
		{
			$(this).parent().siblings().find('.clabe').removeClass('error');
			$(this).parent().siblings().find('.account').removeClass('error');
			$(this).parent().siblings().find('.card').removeClass('error');
			
			beneficiary	= $(this).parent().siblings().find('.beneficiary').val();
			alias		= $(this).parent().siblings().find('.alias').val();
			bankid		= $(this).parent().siblings().find('.bank').val();
			bankName	= $(this).parent().siblings().find('.bank :selected').text();
			clabe		= $(this).parent().siblings().find('.clabe').val();
			account		= $(this).parent().siblings().find('.account').val();
			card		= $(this).parent().siblings().find('.card').val();
			branch		= $(this).parent().siblings().find('.branch_office').val();

			if(alias == "" || beneficiary == "")
			{
				if(alias == "")
				{
					$(this).parents('.tr').find('.alias').addClass('error');
				}
				if(beneficiary == "")
				{
					$(this).parents('.tr').find('.beneficiary').addClass('error');
				}
				swal('', 'Por favor ingrese un beneficiario y un alias', 'error');	
			}
			else if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$(this).parents('.tr').find('.card, .clabe, .account').removeClass('valid').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if (alias == "")
				{
					$(this).parents('.tr').find(".alias").addClass("error");
					swal("", "Debe ingresar todos los campos requeridos", "error");
				}
				else if(clabe != "" && ($(this).parent().parent().find(".clabe").hasClass("error") || clabe.length!=18))
				{
					swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
					$(this).parents('.tr').find(".clabe").addClass("error");
				}
				else if(card != "" && ($(this).parent().parent().find(".card-number").hasClass("error") || card.length!=16))
				{
					swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
					$(this).parents('.tr').find(".card-number").addClass("error");
				}
				else if(account != "" && ($(this).parent().parent().find(".account").hasClass("error") || (account.length>15 || account.length<5)))
				{
					swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
					$(this).parents('.tr').find(".account").addClass("error");
				}
				else  
				{
					flag = false;
					$('#bodyAlimony .tr').each(function()
					{
						name_account	= $(this).find('[name="account[]"]').val();
						name_clabe		= $(this).find('[name="clabe[]"]').val();
						name_bank		= $(this).find('[name="bank[]"]').val();
						if(clabe!= "" && name_clabe!= "" &&clabe == name_clabe)
						{
							swal('','La CLABE ya se encuentra registrada para este beneficiario.','error');
							$('.clabe').removeClass('valid').addClass('error');
							flag = true;
						}
						if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
						{
							swal('','El número de Cuenta ya se encuentra registrada para este beneficiario.','error');
							$('.acount').removeClass('valid').addClass('error');
							flag = true;
						}
					})
					if(!flag)
					{
						@php
							$modelHead = ["Beneficiario", "Alias", "Banco", "Clabe", "Cuenta", "Tarjeta", "Sucursal", "Acción"];
							$modelBody = 
							[
								[
									[
										"content" => 
										[
											[
												"kind" 	  		=> "components.labels.label",
												"classEx" 		=> "beneficiary-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\""
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"type_account[]\""
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind" 	  		=> "components.labels.label",
												"classEx" 		=> "alias-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"alias[]\""
											]
										]
									],
									[ 
										"content" => 
										[ 
											[
												"kind" 	  	  => "components.labels.label",
												"classEx" 	  => "bank-new",
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\"",
												"classEx"     => "idEmployee"
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"bank[]\""
											],
										]
									],
									[
										"content" => 
										[ 
											[
												"kind" 	  	  	=> "components.labels.label",
												"classEx" 	  	=> "clabe-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"clabe[]\""
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind" 	  	  	=> "components.labels.label",
												"classEx" 		=> "account-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"account[]\""
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind" 	  	  	=> "components.labels.label",
												"classEx"		=> "card-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"card[]\""
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind" 	  	  	=> "components.labels.label",
												"classEx" 		=> "branch-new",
											],
											[
												"kind"          => "components.inputs.input-text",
												"attributeEx"   => "type=\"hidden\" name=\"branch[]\""
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
												"classEx"       => "delete-bank-alimony"
											]
										]
									]
								]
							];
							$row = view('components.tables.alwaysVisibleTable',[
								"modelHead" 			=> $modelHead,
								"modelBody" 			=> $modelBody,
								"noHead"				=> true
							])->render();
						@endphp
						bank = $('{!!preg_replace("/(\r)*(\n)*/", "", $row)!!}');
						bank.find('.beneficiary-new').append(beneficiary =='' ? ' --- ' :beneficiary);
						bank.find("[name=\"beneficiary[]\"]").val(beneficiary);
						bank.find("[name=\"type_account[]\"]").val("2");
						bank.find('.bank-new').append(bankName);
						bank.find("[name=\"idEmployeeBank[]\"]").val("x");
						bank.find("[name=\"bank[]\"]").val(bankid);
						bank.find('.alias-new').append(alias =='' ? ' --- ' :alias);
						bank.find("[name=\"alias[]\"]").val(alias);
						bank.find('.clabe-new').append(clabe =='' ? ' --- ' :clabe);
						bank.find("[name=\"clabe[]\"]").val(clabe);
						bank.find('.account-new').append(account =='' ? ' --- ' :account);
						bank.find("[name=\"account[]\"]").val(account);
						bank.find('.card-new').append(card =='' ? ' --- ' :card);
						bank.find("[name=\"card[]\"]").val(card);
						bank.find('.branch-new').append(branch =='' ? ' --- ' :branch);
						bank.find("[name=\"branch[]\"]").val(branch);
						$('#bank-data-register-alimony #bodyAlimony').append(bank);
						$('.card, .clabe, .account, .alias, .beneficiary, .branch_office').removeClass('error').removeClass('valid').val('');
						$('.bank').val(0).trigger("change"); 
						$('#bank-data-register-alimony').parent().removeClass('hidden');
						$('#not-found-accounts-alimony').addClass('hidden');
					}
				}	
			}			 
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.bank').addClass('error');
			}
		})
		.on('click','#add2',function()
		{
			desi	= $('input[name="desirable"]').removeClass('error').val().trim();
			descr	= $('input[name="d_description"]').removeClass('error').val().trim();
			if (desi == "" || descr == "")
			{
				if(desi == "")
				{
					$('input[name="desirable"]').addClass('error');
				}
				if(descr == "")
				{
					$('input[name="d_description"]').addClass('error');
				}
				swal('', 'Favor de llenar los campos necesarios', 'error');
			}
			else
			{
				@php
					$modelHead = ["Deseables", "Descripción", "Acción"];
					$modelBody = 
					[
						[
							[
								"content" =>
								[
									[
										"kind"    => "components.labels.label", 
										"classEx" => "desirable",
									],  
									[
										"kind"        => "components.inputs.input-text", 
										"attributeEx" => "name=\"tdesirable[]\" readonly=\"true\" type=\"hidden\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"    => "components.labels.label", 
										"classEx" => "description_des"
									],
									[
										"kind"        => "components.inputs.input-text", 
										"attributeEx" => "name=\"td_descr[]\" readonly=\"true\" type=\"hidden\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"          => "components.buttons.button",
										"buttonElement" => "a",
										"variant"       => "red",
										"label"         => "<span class=\"icon-x\"></span>",
										"classEx"       => "delete-item"
									]
								]
							]
						]
					];
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"    => true
					])->render();
					$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				row=$(table);
				row = rowColor('#body2', row);
				row.find('.description_des').text(descr);
				row.find('.desirable').text(desi);
				row.find('[name="tdesirable[]"]').val(desi);
				row.find('[name="td_descr[]"]').val(descr);
				$('#body2').append(row);
				$('input[name="desirable"]').val("");
				$('input[name="d_description"]').val("");
			}
		})
		.on('click','.delete-item',function()
		{
			$(this).parent().parent().parent().parent().remove();
		})
		.on('change paste', '#maxSalary', function () 
		{
			if(Number($('#maxSalary').val()) < Number($('#minSalary').val()))
			{
				flagSalary = false;
				swal('Error', 'El salario máximo debe ser mayor al salario mínimo', 'error');
			}
			else
			{
				flagSalary = true;
			}
		})
		.on('change paste', '#minSalary', function () 
		{
			flagSalary = false;
			if(Number($('#minSalary').val()) > Number($('#maxSalary').val()) && $('#maxSalary').val() != "")
			{
				swal('Error', 'El salario mínimo debe ser menor al salario máximo', 'error');
			}
			else
			{
				flagSalary = true;
			}
		})
		.on('click','#save',function(e)
		{
			e.preventDefault();
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			action = $(this).attr('formaction');
			form = $('form#container-alta').attr('action',action);
			form.submit();
		})
		.on('change','[name="work_infonavit_discount"]',function()
		{
			work_infonavit_discount_type = $('[name="work_infonavit_discount_type"] option:selected').val();
			if (work_infonavit_discount_type == 3) 
			{
				if ($(this).val() > 100) 
				{
					$(this).val('');
					swal('','El porcentaje no puede ser mayor de 100','error');
				}
			}
		})
		.on('change','[name="work_infonavit_discount_type"]',function()
		{
			work_infonavit_discount_type = $('[name="work_infonavit_discount_type"] option:selected').val();
			if (work_infonavit_discount_type == 3) 
			{
				if ($('[name="work_infonavit_discount"]').val() > 100) 
				{
					$('[name="work_infonavit_discount"]').val('');
					swal('','El porcentaje no puede ser mayor de 100','error');
				}
			}
		})
		.on('change','[name="work_alimony_discount_type"]',function()
		{
			work_alimony_discount_type = $('[name="work_alimony_discount_type"] option:selected').val();
			if (work_alimony_discount_type == 2) 
			{
				if ($('[name="work_alimony_discount"]').val() > 100) 
				{
					$('[name="work_alimony_discount"]').val('');
					swal('','El porcentaje no puede ser mayor de 100','error');
				}
			}
		})
		.on('change','[name="work_alimony_discount"]',function()
		{
			work_alimony_discount_type = $('[name="work_alimony_discount_type"] option:selected').val();
			if (work_alimony_discount_type == 2) 
			{
				if ($(this).val() > 100) 
				{
					$(this).val('');
					swal('','El porcentaje no puede ser mayor de 100','error');
				}
			}
		})
		.on('click','.view-employee',function()
		{
			employee_id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("staff.view-detail-employee") }}',
				data	: {'employee_id':employee_id},
				success : function(data)
				{
					$('.modal-employee').html(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#detailEmployee').hide();
				}
			});
		})
		.on('select2:unselecting','[name="project_id"]', function (e)
		{
			list_employees		= $('#list_employees .tr').length;
			if (list_employees > 0)
			{
				e.preventDefault();
				swal({
					title		: "Cambiar de Proyecto",
					text		: "Si cambia el proyecto, todos los empleados que ya se encontraban agregados serán eliminados",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$(this).val(null).trigger('change');

						$('#list_employees .tr').each(function()
						{
							id = $(this).find('[name="rq_employee_id[]"]').val();
							if (id != "x") 
							{
								$('#delete_employee').append($('<input type="hidden" name="delete_employee[]" value="'+id+'">'));
							}
						});
						$('#list_employees').empty();
					}
					else
					{
						swal.close();
					}
				});
			}
		})
		.on('click','.edit-employee',function()
		{
			dataEmployee();
			id = $(this).find('option:selected').val();
			if (id != null)
			{
				$.each(generalSelectProject,function(i,v)
				{
					if(id == v.id)
					{
						if(v.flagWBS != null)
						{
							$('.select_wbs').removeClass('hidden').addClass('block');
							generalSelect({'selector':'.js-code_wbs', 'depends':'[name="projectid"]', 'model':1});
						}
						else
						{
							$('[name="code_wbs"]').html('');
							$('.select_wbs').removeClass('block').addClass('hidden');
						}
					}
				});
			}
			else
			{
				$('[name="code_wbs"], [name="code_edt"]').html('');
				$('.select_wbs, [name="code_edt"]').removeClass('block').addClass('hidden');				
			}	
			generalSelect({'selector': '.bank', 'model': 28});
			
			$('.project-class').hide();
			datas = $('#form_employee').serializeArray();
			$.each(datas,function(i,input)
			{
				if (input.name != "qualified_employee") 
				{
					$('#form_employee').find('[name="'+input.name+'"]').val('');
					$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
					$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
					$('#form_employee').find('[name="'+input.name+'"]').parent().find('.form-error').remove();
					$('#form_employee').find('[name="'+input.name+'"]').parent().find('.help-block').remove();
					$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
				}
			});
			$('#yes_qualified').prop('checked',true);
			$('#documents_employee .tr-remove').remove();
			var parent = $(this).parents('.tr');
			$('#form_employee').find('[name="cp"]').html('<option value="'+parent.find('[name="rq_cp[]"]').val()+'" selected>'+parent.find('[name="rq_cp[]"]').val()+'</option>');
			$('#form_employee').find('[name="employee_id"]').val(parent.find('[name="rq_employee_id[]"]').val());
			$('#form_employee').find('[name="name"]').val(parent.find('[name="rq_name[]"]').val());
			$('#form_employee').find('[name="last_name"]').val(parent.find('[name="rq_last_name[]"]').val());
			$('#form_employee').find('[name="scnd_last_name"]').val(parent.find('[name="rq_scnd_last_name[]"]').val());
			$('#form_employee').find('[name="curp"]').val(parent.find('[name="rq_curp[]"]').val());
			$('#form_employee').find('[name="rfc"]').val(parent.find('[name="rq_rfc[]"]').val());
			$('#form_employee').find('[name="tax_regime"]').val(parent.find('[name="rq_tax_regime[]"]').val()).trigger('change');
			$('#form_employee').find('[name="imss"]').val(parent.find('[name="rq_imss[]"]').val());
			$('#form_employee').find('[name="email"]').val(parent.find('[name="rq_email[]"]').val());
			$('#form_employee').find('[name="phone"]').val(parent.find('[name="rq_phone[]"]').val());
			$('#form_employee').find('[name="street"]').val(parent.find('[name="rq_street[]"]').val());
			$('#form_employee').find('[name="number_employee"]').val(parent.find('[name="rq_number_employee[]"]').val());
			$('#form_employee').find('[name="colony"]').val(parent.find('[name="rq_colony[]"]').val());
			$('#form_employee').find('[name="city"]').val(parent.find('[name="rq_city[]"]').val());
			$('#form_employee').find('[name="state"]').html('<option value="'+parent.find('[name="rq_state[]"]').val()+'" selected>'+parent.find('[name="rq_state[]"]').attr('data-description')+'</option>');
			$('#form_employee').find('[name="work_state"]').html('<option value="'+parent.find('[name="rq_work_state[]"]').val()+'" selected>'+parent.find('[name="rq_work_state[]"]').attr('data-description')+'</option>');
			if(parent.find('[name="rq_work_wbs[]"]').val() != undefined && parent.find('[name="rq_work_wbs[]"]').val() != null && parent.find('[name="rq_work_wbs[]"]').val() != "")
			{
				$('#form_employee').find('[name="work_wbs[]"]').html('<option value="'+parent.find('[name="rq_work_wbs[]"]').val()+'" selected>'+parent.find('[name="rq_work_wbs[]"]').attr('data-description')+'</option>');
			}
			$('#form_employee').find('[name="work_project"]').val(parent.find('[name="rq_work_project[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_enterprise_old"]').val(parent.find('[name="rq_work_enterprise_old[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_enterprise"]').val(parent.find('[name="rq_work_enterprise[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_account"]').html('<option value="'+parent.find('[name="rq_work_account[]"]').val()+'" selected>'+parent.find('[name="rq_work_account[]"]').attr('data-description')+'</option>');
			$('#form_employee').find('[name="work_direction"]').val(parent.find('[name="rq_work_direction[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_department"]').val(parent.find('[name="rq_work_department[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_position"]').val(parent.find('[name="rq_work_position[]"]').val());
			$('#form_employee').find('[name="work_immediate_boss"]').val(parent.find('[name="rq_work_immediate_boss[]"]').val());
			$('#form_employee').find('[name="work_income_date"]').val(parent.find('[name="rq_work_income_date[]"]').val());
			$('#form_employee').find('[name="work_status_imss"]').val(parent.find('[name="rq_work_status_imss[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_imss_date"]').val(parent.find('[name="rq_work_imss_date[]"]').val());
			$('#form_employee').find('[name="work_down_date"]').val(parent.find('[name="rq_work_down_date[]"]').val());
			$('#form_employee').find('[name="work_ending_date"]').val(parent.find('[name="rq_work_ending_date[]"]').val());
			$('#form_employee').find('[name="work_reentry_date"]').val(parent.find('[name="rq_work_reentry_date[]"]').val());
			$('#form_employee').find('[name="work_type_employee"]').val(parent.find('[name="rq_work_type_employee[]"]').val()).trigger('change');
			$('#form_employee').find('[name="regime_employee"]').val(parent.find('[name="rq_regime_employee[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_status_employee"]').val(parent.find('[name="rq_work_status_employee[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_status_reason"]').val(parent.find('[name="rq_work_status_reason[]"]').val());
			$('#form_employee').find('[name="work_sdi"]').val(parent.find('[name="rq_work_sdi[]"]').val());
			$('#form_employee').find('[name="work_periodicity"]').val(parent.find('[name="rq_work_periodicity[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_employer_register"]').val(parent.find('[name="rq_work_employer_register[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_payment_way"]').val(parent.find('[name="rq_work_payment_way[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_net_income"]').val(parent.find('[name="rq_work_net_income[]"]').val());
			$('#form_employee').find('[name="work_complement"]').val(parent.find('[name="rq_work_complement[]"]').val());
			$('#form_employee').find('[name="work_fonacot"]').val(parent.find('[name="rq_work_fonacot[]"]').val());
			$('#form_employee').find('[name="work_infonavit_credit"]').val(parent.find('[name="rq_work_infonavit_credit[]"]').val());
			$('#form_employee').find('[name="work_infonavit_discount"]').val(parent.find('[name="rq_work_infonavit_discount[]"]').val());
			$('#form_employee').find('[name="work_infonavit_discount_type"]').val(parent.find('[name="rq_work_infonavit_discount_type[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_alimony_discount_type"]').val(parent.find('[name="rq_work_alimony_discount_type[]"]').val()).trigger('change');
			$('#form_employee').find('[name="work_alimony_discount"]').val(parent.find('[name="rq_work_alimony_discount[]"]').val());
			$('#form_employee').find('[name="replace"]').val(parent.find('[name="rq_replace[]"]').val());
			$('#form_employee').find('[name="purpose"]').val(parent.find('[name="rq_purpose[]"]').val());
			$('#form_employee').find('[name="requeriments"]').val(parent.find('[name="rq_requeriments[]"]').val());
			$('#form_employee').find('[name="observations"]').val(parent.find('[name="rq_observations[]"]').val());
			$('#form_employee').find('[name="work_viatics"]').val(parent.find('[name="rq_work_viatics[]"]').val());
			$('#form_employee').find('[name="work_camping"]').val(parent.find('[name="rq_work_camping[]"]').val());
			$('#form_employee').find('[name="work_position_immediate_boss"]').val(parent.find('[name="rq_work_position_immediate_boss[]"]').val());
			$('#form_employee').find('[name="work_subdepartment"]').html('<option value="'+parent.find('[name="rq_work_subdepartment[]"]').val()+'" selected>'+parent.find('[name="rq_work_subdepartment[]"]').attr('data-description')+'</option>');
			$('#form_employee').find('[name="computer_required"]').val(parent.find('[name="rq_computer_required[]"]').val()).trigger('change');
			if (parent.find('[name="rq_qualified_employee[]"]').val() == "1") 
			{
				$('#form_employee').find('#yes_qualified').prop('checked',true);
			}
			else
			{
				$('#form_employee').find('#no_qualified').prop('checked',true);
			}
			if (parent.find('[name="rq_doc_birth_certificate[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_birth_certificate[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_birth_certificate"]').val(parent.find('[name="rq_doc_birth_certificate[]"]').val());
				$('[name="doc_birth_certificate"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_birth_certificate').text('');
				$('.doc_birth_certificate').append(btn);
			}

			if (parent.find('[name="rq_doc_proof_of_address[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_proof_of_address[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_proof_of_address"]').val(parent.find('[name="rq_doc_proof_of_address[]"]').val());
				$('[name="doc_proof_of_address"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_proof_of_address').text('');
				$('.doc_proof_of_address').append(btn);
			}

			if (parent.find('[name="rq_doc_nss[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_nss[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_nss"]').val(parent.find('[name="rq_doc_nss[]"]').val());
				$('[name="doc_nss"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_nss').text('');
				$('.doc_nss').append(btn);
			}

			if (parent.find('[name="rq_doc_ine[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_ine[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_ine"]').val(parent.find('[name="rq_doc_ine[]"]').val());
				$('[name="doc_ine"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_ine').text('');
				$('.doc_ine').append(btn);
			}

			if (parent.find('[name="rq_doc_curp[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_curp[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_curp"]').val(parent.find('[name="rq_doc_curp[]"]').val());
				$('[name="doc_curp"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_curp').text('');
				$('.doc_curp').append(btn);
			}

			if (parent.find('[name="rq_doc_rfc[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_rfc[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_rfc"]').val(parent.find('[name="rq_doc_rfc[]"]').val());
				$('[name="doc_rfc"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_rfc').text('');
				$('.doc_rfc').append(btn);
			}

			if (parent.find('[name="rq_doc_cv[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_cv[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_cv"]').val(parent.find('[name="rq_doc_cv[]"]').val());
				$('[name="doc_cv"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_cv').text('');
				$('.doc_cv').append(btn);
			}

			if (parent.find('[name="rq_doc_proof_of_studies[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_proof_of_studies[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_proof_of_studies"]').val(parent.find('[name="rq_doc_proof_of_studies[]"]').val());
				$('[name="doc_proof_of_studies"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_proof_of_studies').text('');
				$('.doc_proof_of_studies').append(btn);
			}

			if (parent.find('[name="rq_doc_professional_license[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_professional_license[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_professional_license"]').val(parent.find('[name="rq_doc_professional_license[]"]').val());
				$('[name="doc_professional_license"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_professional_license').text('');
				$('.doc_professional_license').append(btn)
			}

			if (parent.find('[name="rq_doc_requisition[]"]').val() != "") 
			{
				url = '{{ url('docs/staff') }}/'+parent.find('[name="rq_doc_requisition[]"]').val();
				btnFile(url);
				$('#form_employee').find('[name="doc_requisition"]').val(parent.find('[name="rq_doc_requisition[]"]').val());
				$('[name="doc_requisition"]').siblings('.uploader-content').addClass('image_success');
				$('.doc_requisition').text('');
				$('.doc_requisition').append(btn);
			}

			accounts			= parent.find('.container-accounts');
			accounts_alimony	= parent.find('.container-accounts-alimony');

			$('#bodyEmployee').empty();
			$('#bodyAlimony').empty();

			if (accounts.length > 0) 
			{
				$(accounts).each(function(i,v)
				{
					alias		= $(this).find('.t_alias').val();
					bankid		= $(this).find('.t_idCatBank').val();
					clabe		= $(this).find('.t_clabe').val();
					account		= $(this).find('.t_account').val();
					card		= $(this).find('.t_cardNumber').val();
					branch		= $(this).find('.t_branch').val();
					idEmployee	= $(this).find('.t_idEmployee').val();
					bankName 	= $(this).find('.t_bankName').val();

					@php
						$modelBody		= [
							[
								[
									"content" => 
									[
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "alias-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"alias[]\""
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\""
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"type_account[]\""
										]
									]
								],
								[ 
									"content" => 
									[ 
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "bank-new",
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\"",
											"classEx"     => "idEmployee"
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"bank[]\""
										],
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 	  	=> "clabe-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 		=> "account-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx"		=> "cardNumber-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  	=> "components.labels.label",
											"classEx" 		=> "branch-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"branch[]\""
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
							]
						];
						$modelHead = 
						[
							["value" => "Alias"],
							["value" => "Banco"],
							["value" => "Clabe"],
							["value" => "Cuenta"],
							["value" => "Tarjeta"],
							["value" => "Sucursal"],
							["value" => "Acción"]
						];
						$row = view('components.tables.alwaysVisibleTable',[
							"modelHead" 			=> $modelHead,
							"modelBody" 			=> $modelBody,
							"noHead"				=> "true"
						])->render();
					@endphp
					row = $('{!!preg_replace("/(\r)*(\n)*/", "", $row)!!}');
					row.find(".alias-new").append(alias =='' ? ' --- ' :alias);
					row.find("[name=\"alias[]\"]").val(alias);
					row.find("[name=\"type_account[]\"]").val("1");
					row.find(".bank-new").append(bankName =='' ? ' --- ' :bankName);
					row.find("[name=\"idEmployeeBank[]\"]").val(idEmployee);
					row.find("[name=\"bank[]\"]").val(bankid);
					row.find(".clabe-new").append(clabe =='' ? ' --- ' :clabe);
					row.find("[name=\"clabe[]\"]").val(clabe);
					row.find(".account-new").append(account =='' ? ' --- ' :account);
					row.find("[name=\"account[]\"]").val(account);
					row.find(".cardNumber-new").append(card =='' ? ' --- ' :card);
					row.find("[name=\"card[]\"]").val(card);
					row.find(".branch-new").append(branch =='' ? ' --- ' :branch);
					row.find("[name=\"branch[]\"]").val(branch);
					$('#bank-data-register #bodyEmployee').append(row);
				});
				$('#bank-data-register').parent().removeClass('hidden');
				$('#not-found-accounts').addClass('hidden');
			}
			else
			{
				$('#bank-data-register').parent().addClass('hidden');
				$('#not-found-accounts').removeClass('hidden');
			}

			if (accounts_alimony.length > 0) 
			{
				$(accounts_alimony).each(function(i,v)
				{
					beneficiary	= $(this).find('.t_beneficiary').val();
					alias		= $(this).find('.t_alias').val();
					bankid		= $(this).find('.t_idCatBank').val();
					clabe		= $(this).find('.t_clabe').val();
					account		= $(this).find('.t_account').val();
					card		= $(this).find('.t_cardNumber').val();
					branch		= $(this).find('.t_branch').val();
					idEmployee	= $(this).find('.t_idEmployee').val();
					bankName 	= $(this).find('.t_bankName').val();

					@php
						$modelHead = ["Beneficiario", "Alias", "Banco", "Clabe", "Cuenta", "Tarjeta", "Sucursal", "Acción"];
						$modelBody	= 
						[
							[
								[
									"content" => 
									[
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "beneficiary-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"beneficiary[]\""
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"type_account[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "alias-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"alias[]\""
										]
									]
								],
								[ 
									"content" => 
									[ 
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "bank-new",
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"idEmployeeBank[]\"",
											"classEx"     => "idEmployee"
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"bank[]\""
										],
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "clabe-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "account-new",
										],
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "cardNumber-new",
										], 
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" => 
									[ 
										[
											"kind" 	  	  => "components.labels.label",
											"classEx" 	  => "branch-new",
										], 
										[
											"kind"          => "components.inputs.input-text",
											"attributeEx"   => "type=\"hidden\" name=\"branch[]\""
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
											"classEx"       => "delete-bank-alimony"
										]
									]
								]
							]
						];
						$row = view('components.tables.alwaysVisibleTable',[
							"modelHead" 			=> $modelHead,
							"modelBody" 			=> $modelBody,
							"noHead" 				=> "true"
						])->render();
					@endphp
					bank = $('{!!preg_replace("/(\r)*(\n)*/", "", $row)!!}');
					bank.find(".beneficiary-new").append(beneficiary =='' ? ' --- ' :beneficiary);
					bank.find("[name=\"beneficiary[]\"]").val(beneficiary);
					bank.find("[name=\"type_account[]\"]").val("2");
					bank.find(".alias-new").append(alias =='' ? ' --- ' :alias);
					bank.find("[name=\"alias[]\"]").val(alias);
					bank.find(".bank-new").append(bankName);
					bank.find("[name=\"idEmployeeBank[]\"]").val(idEmployee);
					bank.find("[name=\"bank[]\"]").val(bankid);
					bank.find(".clabe-new").append(clabe =='' ? ' --- ' :clabe);
					bank.find("[name=\"clabe[]\"]").val(clabe);
					bank.find(".account-new").append(account =='' ? ' --- ' :account);
					bank.find("[name=\"account[]\"]").val(account);
					bank.find(".cardNumber-new").append(card =='' ? ' --- ' :card);
					bank.find("[name=\"card[]\"]").val(card);
					bank.find(".branch-new").append(branch =='' ? ' --- ' :branch);
					bank.find("[name=\"branch[]\"]").val(branch);
					$('#bank-data-register-alimony #bodyAlimony').append(bank);
				});
				$('#bank-data-register-alimony').parent().removeClass('hidden');
				$('#not-found-accounts-alimony').addClass('hidden');

				alimony = $('#alimony');
				alimony.prop('checked',true);
				$("#infonavit-form").stop(true,true).fadeIn();
				$('#accounts-alimony').stop(true,true).fadeIn();
			}
			else
			{
				$('#bank-data-register-alimony').parent().addClass('hidden');
				$('#not-found-accounts-alimony').removeClass('hidden');
			}

			$('.name-other-document').parents('.tr').remove();
			other_documents	= $(this).parents('.tr').find('.container-other-documents');
			if (other_documents.length > 0) 
			{
				$('#other_documents').empty();
				$(other_documents).each(function(i,v)
				{
					name	= $(this).find('.t_name_other_document').val();
					path	= $(this).find('.t_path_other_document').val();
					url		= '{{ url("docs/requisition") }}/'+path;

					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [ "Nombre del documento", "Archivo" ];

						$body = [ "classEx" => "tr-remove",
							[
								"content" =>
								[
									[
										"kind" 	  	  => "components.labels.label",
										"classEx" 	  => "name-other-document",
									]
								]
							],
							[ 
								"classEx"	=> "doc_birth_certificate",
								"content"	=>
								[
									[
										"kind"          => "components.buttons.button",
										"buttonElement" => "a",
										"classEx"		=> "file_link",
										"variant"       => "secondary",
										"attributeEx"   => "target=\"_blank\"",
										"label"         => "Archivo"
									]
								]
							]
						];
						$modelBody[] = $body;
						$row = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead" 	=> "true"
						])->render();
					@endphp
					tr = $('{!!preg_replace("/(\r)*(\n)*/", "", $row)!!}');
					tr.find(".name-other-document").append(name);
					tr.find(".file_link").attr("href", url);
					$('#documents_employee').append(tr);
					
					@php
						$options = collect([
							[
								"value"       => "Aviso de retención por crédito Infonavit", 
								"description" => "Aviso de retención por crédito Infonavit"
							], 
							[
								"value"       => "Estado de cuenta", 
								"description" => "Estado de cuenta"
							], 
							[
								"value"       => "Cursos de capacitación", 
								"description" => "Cursos de capacitación"
							], 
							[
								"value"       => "Carta de recomendación", 
								"description" => "Carta de recomendación"
							], 
							[
								"value"       => "Identificación", 
								"description" => "Identificación"
							], 
							[
								"value"       => "Hoja de expediente", 
								"description" => "Hoja de expediente"
							]
						]);
						$labelSelect = view('components.labels.label',[
							"label"		=> "Selecciona el tipo de archivo"
						])->render();
						$select = view('components.inputs.select',[
							"options"		=> $options,
							"classEx"		=> "name_other_document", 
							"attributeEx"	=> "name=\"name_other_document[]\" multiple=\"multiple\" data-validation=\"required\"",
						])->render();
						$newDoc = view('components.documents.upload-files',[
							"classEx"				=> "form_other_doc",
							"classExContainer"		=> "image_success",
							"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExInput"			=> "pathActioner",
							"attributeExRealPath"	=> "type=\"hidden\" name=\"path_other_document[]\"",
							"classExRealPath"		=> "path path_other_document",					
							"classExDelete"			=> "delete_other_doc",
							"componentsExUp"		=> $labelSelect.$select
						])->render();
					@endphp
					newDoc = $('{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}');
					newDoc.find('.name_other_document option[value="'+name+'"]').prop('selected', true);
					newDoc.find('.path_other_document').val(path);
					$('#other_documents').append(newDoc);
					@php
						$selects = collect([
							[
								"identificator"          => "[name=\"name_other_document[]\"]", 
								"placeholder"            => "Seleccione el tipo de documento",
								"maximumSelectionLength" => "1"
							]
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects]) @endcomponent
				});
			}
			
			enterprise_id = $('[name="work_enterprise"] option:selected').val();
			object = $(this);
			$.ajax(
			{
				type	: 'post',
				url		: '{{route("staff.account")}}',
				data	: {'enterprise':enterprise_id},
				success	: function(response)
				{
					rp = '';
					$.each(response.er,function(i,v)
					{
						rp += '<option value="'+v+'">'+v+'</option>';
					});
					$('[name="work_employer_register"]').html(rp);
				},
				error : function()
				{
					swal('','Sucedió un error al cargar los registros patronales, por favor intente de nuevo.','error');
					$('[name="work_employer_register"]').val(null).trigger('change');
				}
			})
			.done(function(data)
			{
				$('#form_employee').find('[name="work_employer_register"]').val(object.parents('.tr').find('[name="rq_work_employer_register[]"]').val()).trigger('change');

				@if(!isset($request) || isset($request) && $request->status == 2) 
					object.parents('.tr').addClass('active');
				@endif
			});
			$('#addEmployee').modal('show');
		});
		
		Number.prototype.formatMoney = function(c, d, t)
		{
			var n = this,
			d = d == undefined ? "." : d, 
			t = t == undefined ? "," : t, 
			s = n < 0 ? "-" : "", 
			i = String(parseInt(n = Math.abs(Number(n) || 0))), 
			j = (j = i.length) > 3 ? j % 3 : 0;
			return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t);
		};
		function saveEmployeeValidation()
		{
			$.validate(
			{
				form	: '#form_employee',
				modules	: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					return false;
				},
				onSuccess : function($form)
				{	
					object = $("#save_employee");
					$('.span-error').remove();
					if($("#infonavit").is(':checked'))
					{
						if($("[name=\"work_infonavit_discount_type\"] option:selected").val() == "3" && $("[name=\"work_infonavit_discount\"]").val() > 100)
						{
							$("[name=\"work_infonavit_discount\"]").addClass("error");
							swal('', 'Por favor ingrese un porcentaje menor a 100.', 'error');
							return false;
						}
					}
					if($("#alimony").is(':checked'))
					{
						if($("[name=\"work_alimony_discount_type\"] option:selected").val() == "2" && $("[name=\"work_alimony_discount\"]").val() > 100)
						{
							$("[name=\"work_alimony_discount\"]").addClass("error");
							swal('', 'Por favor ingrese un porcentaje menor a 100.', 'error');
							return false;
						}
					}
					other_doc = true;
					span_doc = "<span class='help-block form-error span-error'>Este campo es obligatorio</span>";
					$('.path_other_document').each(function(i,v)
					{
						if($(this).val() == "")
						{
							other_doc = false;
							$(this).parents('.docs-p').find('.uploader-content').append(span_doc);
							swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
						}
						else
						{
							$(this).parents('.docs-p').find('.span-error').remove();
						}
					})
					if(other_doc == false)
					{
						return false;
					}

					if ($('#list_employees .tr').length > 0) 
					{
						$('#list_employees .tr').each(function()
						{
							if ($(this).hasClass('active')) 
							{
								$(this).remove();
							}
						});

						$('#list_employees .tr').each(function(i,v)
						{
							$(this).find('.t_alias').attr('name','alias_'+i+'[]');
							$(this).find('.t_beneficiary').attr('name','beneficiary_'+i+'[]');
							$(this).find('.t_type').attr('name','type_'+i+'[]');
							$(this).find('.t_idEmployee').attr('name','idEmployee_'+i+'[]');
							$(this).find('.t_idCatBank').attr('name','idCatBank_'+i+'[]');
							$(this).find('.t_clabe').attr('name','clabe_'+i+'[]');
							$(this).find('.t_bankName').attr('name','bankName_'+i+'[]');
							$(this).find('.t_account').attr('name','account_'+i+'[]');
							$(this).find('.t_cardNumber').attr('name','cardNumber_'+i+'[]');
							$(this).find('.t_branch').attr('name','branch_'+i+'[]');
							$(this).find('.t_name_other_document').attr('name','name_other_document_'+i+'[]');
							$(this).find('.t_path_other_document').attr('name','path_other_document_'+i+'[]');
						});
					}

					count_employee = $('#list_employees .tr').length;
					span = $('<span></span>');
					flag_input_subdepartment	= false;
					flag_input_department		= false;
					datas		= $('#form_employee').serializeArray();

					$.each(datas,function(i,input)
					{
						if (input.name != 'work_subdepartment')
						{
							flag_input_subdepartment = true;
						}
						else
						{
							flag_input_subdepartment = false;
						}

						if (input.name != 'work_department')
						{
							flag_input_department = true;
						}
						else
						{
							flag_input_department = false;
						}

						if (input.name != 'alias[]' && input.name != 'beneficiary[]' && input.name != 'type_account[]' && input.name != 'idEmployeeBank[]' && input.name != 'bank[]' && input.name != 'clabe[]' && input.name != 'account[]' && input.name != 'card[]' && input.name != 'branch[]' && input.name != 'name_other_document[]' && input.name != 'path_other_document[]') 
						{
							if(input.name == 'work_wbs[]')
							{
								span.append($('<input type="hidden" name="rq_'+input.name+'" value="'+input.value+'" data-description="'+$('select[name="'+input.name+'"]').text()+'">'));
							}
							else if(input.name == 'state' || input.name == 'work_state' || input.name == 'work_account' || input.name == 'work_subdepartment')
							{
								span.append($('<input type="hidden" name="rq_'+input.name+'[]" value="'+input.value+'" data-description="'+$('select[name="'+input.name+'"]').text()+'">'));
							}
							else
							{
								span.append($('<input type="hidden" name="rq_'+input.name+'[]" value="'+input.value+'">'));
							}
						}
					});	

					if (flag_input_department) 
					{
						span.append($('<input type="hidden" name="rq_work_department[]" value="">'));
					}
					if (flag_input_subdepartment) 
					{
						span.append($('<input type="hidden" name="rq_work_subdepartment[]" value="">'));
					}

					$('#bodyEmployee .tr').each(function()
					{
						alias			= $(this).find('[name="alias[]"]').val();
						beneficiary		= $(this).find('[name="beneficiary[]"]').val();
						type_account	= $(this).find('[name="type_account[]"]').val();
						idEmployeeBank	= $(this).find('[name="idEmployeeBank[]"]').val();
						bank			= $(this).find('[name="bank[]"]').val();
						clabe			= $(this).find('[name="clabe[]"]').val();
						account			= $(this).find('[name="account[]"]').val();
						card			= $(this).find('[name="card[]"]').val();
						branch			= $(this).find('[name="branch[]"]').val();
						bankName		= $(this).find('[name="bank[]"]').siblings('.bank-new').text();

						div 			= $('<div class="container-accounts"></div>')
											.append($('<input type="hidden" class="t_alias" name="alias_'+count_employee+'[]" value="'+alias+'">'))								
											.append($('<input type="hidden" class="t_beneficiary" name="beneficiary_'+count_employee+'[]" value="'+beneficiary+'">'))								
											.append($('<input type="hidden" class="t_type" name="type_'+count_employee+'[]" value="'+type_account+'">'))								
											.append($('<input type="hidden" class="t_idEmployee" name="idEmployee_'+count_employee+'[]" value="'+idEmployeeBank+'">'))								
											.append($('<input type="hidden" class="t_idCatBank" name="idCatBank_'+count_employee+'[]" value="'+bank+'">'))								
											.append($('<input type="hidden" class="t_clabe" name="clabe_'+count_employee+'[]" value="'+clabe+'">'))								
											.append($('<input type="hidden" class="t_bankName" name="bankName_'+count_employee+'[]" value="'+bankName+'">'))			
											.append($('<input type="hidden" class="t_account" name="account_'+count_employee+'[]" value="'+account+'">'))								
											.append($('<input type="hidden" class="t_cardNumber" name="cardNumber_'+count_employee+'[]" value="'+card+'">'))
											.append($('<input type="hidden" class="t_branch" name="branch_'+count_employee+'[]" value="'+branch+'">'));

						span.append(div);
					});

					$('#bodyAlimony .tr').each(function()
					{
						alias			= $(this).find('[name="alias[]"]').val();
						beneficiary		= $(this).find('[name="beneficiary[]"]').val();
						type_account	= $(this).find('[name="type_account[]"]').val();
						idEmployeeBank	= $(this).find('[name="idEmployeeBank[]"]').val();
						bank			= $(this).find('[name="bank[]"]').val();
						clabe			= $(this).find('[name="clabe[]"]').val();
						account			= $(this).find('[name="account[]"]').val();
						card			= $(this).find('[name="card[]"]').val();
						branch			= $(this).find('[name="branch[]"]').val();
						bankName		= $(this).find('[name="bank[]"]').siblings('.bank-new').text();

						div 			= $('<div class="container-accounts-alimony"></div>')
											.append($('<input type="hidden" class="t_alias" name="alias_'+count_employee+'[]" value="'+alias+'">'))
											.append($('<input type="hidden" class="t_beneficiary" name="beneficiary_'+count_employee+'[]" value="'+beneficiary+'">'))
											.append($('<input type="hidden" class="t_type" name="type_'+count_employee+'[]" value="'+type_account+'">'))
											.append($('<input type="hidden" class="t_idEmployee" name="idEmployee_'+count_employee+'[]" value="'+idEmployeeBank+'">'))
											.append($('<input type="hidden" class="t_idCatBank" name="idCatBank_'+count_employee+'[]" value="'+bank+'">'))
											.append($('<input type="hidden" class="t_clabe" name="clabe_'+count_employee+'[]" value="'+clabe+'">'))
											.append($('<input type="hidden" class="t_bankName" name="bankName_'+count_employee+'[]" value="'+bankName+'">'))
											.append($('<input type="hidden" class="t_account" name="account_'+count_employee+'[]" value="'+account+'">'))
											.append($('<input type="hidden" class="t_cardNumber" name="cardNumber_'+count_employee+'[]" value="'+card+'">'))
											.append($('<input type="hidden" class="t_branch" name="branch_'+count_employee+'[]" value="'+branch+'">'));
						span.append(div);
					});

					$('#other_documents .form_other_doc').each(function()
					{
						name_other_document = $(this).find('.name_other_document option:selected').val();
						path_other_document = $(this).find('.path_other_document').val();

						if (name_other_document != undefined && name_other_document != "" && path_other_document != "") 
						{
							div 	= $('<div class="container-other-documents"></div>')
									.append($('<input type="hidden" class="t_name_other_document" name="name_other_document_'+count_employee+'[]" value="'+name_other_document+'">'))
									.append($('<input type="hidden" class="t_path_other_document" name="path_other_document_'+count_employee+'[]" value="'+path_other_document+'">'));

							span.append(div);
						}
					});
					@php
						$modelHead = ["Nombre", "Puesto", "Acciones"];
							
						$modelBody = 
						[
							[
								[
									"content" => 
									[
										[
											"kind" 	  => "components.labels.label",
											"classEx" => "name-employee-add",
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" 	  => "components.labels.label",
											"classEx" => "work-position-add",
										]
									]
								],
								[ 
									"classEx" => "td_final",
									"content" => 
									[
										[
											"kind"        => "components.buttons.button", 
											"classEx"     => "edit-employee",
											"variant"     => "success",
											"label"       => "<span class=\"icon-pencil\"></span>",
											"attributeEx" => "type=\"button\" data-toggle=\"modal\" data-target=\"#addEmployee\""
										],
										[
											"kind"        => "components.buttons.button", 
											"attributeEx" => "type=\"button\"",
											"classEx"     => "delete-employee",
											"variant"     => "red",
											"label"       => "<span class=\"icon-x\"></span>"
										]
									]
								],
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true,
							"variant"	=> "default"
						])->render();
						$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp

					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(table);

					row.find('.name-employee-add').append($('#form_employee').find('[name="name"]').val()+' '+$('#form_employee').find('[name="last_name"]').val()+' '+$('#form_employee').find('[name="scnd_last_name"]').val());
					row.find('.work-position-add').append($('#form_employee').find('[name="work_position"]').val())
					row.find('.td_final').append(span);
					$('#list_employees').append(row);

					$.each(datas,function(i,input)
					{
						if (input.name != "qualified_employee") 
						{
							$('#form_employee').find('[name="'+input.name+'"]').val('');
							$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
							$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
							$('#form_employee').find('[name="'+input.name+'"]').parent('p').find('.form-error').remove();
							$('#form_employee').find('[name="'+input.name+'"]').parent('p').find('.help-block').remove();
							$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
						}
					});
					
					$('#form_employee').find('[name="employee_id"]').val('x');
					$('#form_employee').find('.uploader-content').removeClass('image_success');


					$('.doc_birth_certificate').empty().text('Sin documento');
					$('.doc_proof_of_address').empty().text('Sin documento');
					$('.doc_nss').empty().text('Sin documento');
					$('.doc_ine').empty().text('Sin documento');
					$('.doc_curp').empty().text('Sin documento');
					$('.doc_rfc').empty().text('Sin documento');
					$('.doc_cv').empty().text('Sin documento');
					$('.doc_proof_of_studies').empty().text('Sin documento');
					$('.doc_professional_license').empty().text('Sin documento');
					$('.doc_requisition').empty().text('Sin documento');
					$('#documents_employee .tr-remove').remove();
					$('#other_documents').empty();
					$(".pathActioner").removeClass("hidden");
					swal('','Empleado agregado exitosamente.','success');
					$('#form_employee')[0].reset();
					$("#form_employee").find("[name=\"state\"], [name=\"work_state\"], [name=\"work_account\"], [name=\"work_subdepartment\"]").empty();
					object.parents('.modal').modal('hide');
					$("#notFoundEmployee").hide();
					return false;
				}
			});
		}
		function dataEmployee()
		{
			generalSelect({'selector': '#cp', 'model': 2});
			generalSelect({'selector': '[name="state"]', 'model': 31});
			generalSelect({'selector': '[name="work_state"]', 'model': 31});
			generalSelect({'selector': '[name="work_account"]', 'depends':'[name="work_enterprise"]', 'model':4});
			generalSelect({'selector': '[name="work_place[]"]', 'model':38});
			generalSelect({'selector': '[name="work_subdepartment"]', 'model': 39});
			generalSelect({'selector': '.bank', 'model': 28});
			generalSelect({'selector': '[name="work_employer_register"]', 'depends': '[name="work_enterprise"]', 'model': 47});
			@php
				$selects = collect([
					[
						"identificator"          => "#tax_regime", 
						"placeholder"            => "Seleccione el régimen fiscal", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"regime_employee\"]", 
						"placeholder"            => "Seleccione el régimen", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"sys_user\"]", 
						"placeholder"            => "Seleccione una opción", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_department\"]", 
						"placeholder"            => "Seleccione el departamento", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_payment_way\"]", 
						"placeholder"            => "Seleccione la forma de pago", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_periodicity\"]", 
						"placeholder"            => "Seleccione la periodicidad", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_status_employee\"]", 
						"placeholder"            => "Seleccione el estatus", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_enterprise\"], [name=\"work_enterprise_old\"]", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_type_employee\"]", 
						"placeholder"            => "Seleccione el tipo de trabajador"
					],
					[
						"identificator"          => "[name=\"work_direction\"]", 
						"placeholder"            => "Seleccione la dirección", 
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"          => "[name=\"work_status_imss\"]", 
						"placeholder"            => "Seleccione el estatus de IMSS", 
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"          => "[name=\"computer_required\"]", 
						"placeholder"            => "Seleccione una opción", 
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"          => "[name=\"work_infonavit_discount_type\"], [name=\"work_alimony_discount_type\"]", 
						"placeholder"            => "Seleccione el tipo de descuento", 
						"maximumSelectionLength" => "1",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$('input[name="cp"]').numeric({ negative:false});
			$('input[name="work_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_net_income"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_infonavit_credit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_infonavit_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('.clabe,.account,.card').numeric({ decimal: false, negative:false});
			$('input[name="clabe"]').numeric({ decimal: false, negative:false});
			$('input[name="account"]').numeric({ decimal: false, negative:false});
			$('input[name="card"]').numeric({ decimal: false, negative:false});
			$('input[name="work_alimony_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('[name="imss"]').mask('0000000000-0',{placeholder: "0000000000-0"});
			$('[name="work_income_date"],[name="work_imss_date"],[name="work_down_date"],[name="work_ending_date"],[name="work_reentry_date"],[name="work_income_date_old"]').datepicker({ dateFormat: "dd-mm-yy" });
		}
		function btnFile(url)
		{
			@php 
				$btn = view('components.buttons.button',[
					"kind"          => "components.buttons.button",
					"buttonElement" => "a",
					"classEx"		=> "file_link",
					"variant"       => "secondary",
					"attributeEx"   => "target=\"_blank\"",
					"label"         => "Archivo"
				])->render();
			@endphp
			btn = $('<div class="text-left md:text-center m-2 w-1/2 md:w-full"> {!!preg_replace("/(\r)*(\n)*/", "", $btn)!!} </div>');
			btn.attr('href', url);
		}
	</script>
@endsection
