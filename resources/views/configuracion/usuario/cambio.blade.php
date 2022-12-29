@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") Datos de usuario @endcomponent
	@component("components.labels.subtitle") Para editar el usuario es necesario colocar los siguientes campos: @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('user.update',$user->id)."\" method=\"post\" id=\"container-alta\"", "methodEx" => "put"])
		@component("components.containers.container-form")
			<div class="col-span-2">
				<div class="py-2">
					@component("components.labels.label", ["label" => "Nombre(s)"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese el nombre(s)\" type=\"text\" name=\"name\" value=\"".htmlentities($user->name)."\" data-validation=\"required\" data-validation-length=\"min2\"", "classEx" => "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Apellido paterno"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese el apellido paterno\" type=\"text\" name=\"last_name\" value=\"".htmlentities($user->last_name)."\" data-validation=\"required\" data-validation-length=\"min2\"", "classEx" => "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Apellido materno (opcional)"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "placeholder=\"Ingrese el apellido materno\" type=\"text\" name=\"scnd_last_name\" value=\"".htmlentities($user->scnd_last_name)."\"", "classEx" => "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Seleccione una opción"]) @endcomponent
					<div class="flex space-x-2">
						@if($user->gender == "hombre")
							@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" checked=\"true\" name=\"gender\" id=\"hombre\" value=\"hombre\"", "label" => "Hombre"]) @endcomponent
							@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" name=\"gender\" id=\"mujer\" value=\"mujer\"", "label" => "Mujer"]) @endcomponent
						@else
							@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" name=\"gender\" id=\"hombre\" value=\"hombre\"", "label" => "Hombre"]) @endcomponent
							@component("components.buttons.button-approval",["attributeEx" => "type=\"radio\" checked=\"true\" name=\"gender\" id=\"mujer\" value=\"mujer\"", "label" => "Mujer"]) @endcomponent
						@endif
					</div>
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Teléfono (Opcional)"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"phone\" placeholder=\"Ingrese el teléfono\" value=\"$user->phone\" data-validation=\"phone\"", "classEx" => "input-text"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Extensión (Opcional)"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"extension\" placeholder=\"Ingrese la extensión\" value=\"$user->extension\"", "classEx" => "input-text extension"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Correo Electrónico"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" name=\"email\" placeholder=\"Ingrese el correo electrónico\" value=\"".$user->email."\" data-validation=\"required email server\" data-validation-url=\"".route('user.validation')."\" data-validation-req-params=\"".htmlentities(json_encode(array('oldUser'=>$user->email)))."\"", "classEx" => "input-text"]) @endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@php
					$options = collect();
					foreach($enterprises as $enterprise)
					{
						
						$flag = false;
						foreach($user_has_enterprises as $user_has_enterprise)
						{
							$temp = $enterprise->id;

							if($temp == $user_has_enterprise->enterprise_id)
							{
								$flag=true;
							}
						}
						if($flag)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);	
						}
					}
				@endphp
				<div class="py-2">
					@component("components.labels.label", ["label" => "Agregue la empresa"]) @endcomponent
					@component("components.inputs.select", ["attributeEx" => "name=\"enterprises[]\" id=\"multiple-enterprises\" data-validation=\"required\"", "options" => $options, "classEx" => "js-enterprises"]) @endcomponent
				</div>
				
				@php
					$options = collect();
					foreach($areas as $area)
					{
						if($user->area_id == $area->id)
						{
							$options = $options->concat([["value" => $area->id, "description" => $area->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $area->id, "description" => $area->name]]);	
						}
					}
				@endphp
				<div class="py-2">
					@component("components.labels.label", ["label" => "Agregue una dirección"]) @endcomponent
					@component("components.inputs.select", ["attributeEx" => "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"", "options" => $options, "classEx" => "input-text js-areas"]) @endcomponent
				</div>
				@php
					$options = collect();
					foreach($departments as $department)
					{
						if($user->departament_id == $department->id)
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name, "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name]]);	
						}
					}
				@endphp
				<div class="py-2">
					@component("components.labels.label", ["label" => "Agregue un departamento (opcional)"]) @endcomponent
					@component("components.inputs.select", ["attributeEx" => "name=\"department_id\" id=\"multiple-departments\"", "options" => $options, "classEx" => "input-text js-departments"]) @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Puesto:"]) @endcomponent
					@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese el puesto\" name=\"position\" value=\"".htmlentities($user->position)."\" data-validation-depends-on=\"usertype\" data-validation-depends-on-value=\"1\"", "classEx" => "input-text position"]) @endcomponent
				</div>

				@php 
					$options = collect();
					foreach($sections as $section)
					{
						if($user->inReview != null)
						{
							foreach($user->inReview as $inReview)
							{
								if($inReview->idsectionTickets==$section->idsectionTickets)
								{
									$options =  $options->concat([["value" => $section->idsectionTickets, "description" => $section->section, "selected" => "selected"]]);
								}
								else
								{
									$options =  $options->concat([["value" => $section->idsectionTickets, "description" => $section->section]]);
								}
							}
						}
						else
						{
							$options =  $options->concat([["value" => $section->idsectionTickets, "description" => $section->section]]);
						}
					}
					($user->sys_user == 0) ? $attributeEx = "disabled=\"disabled\"" : $attributeEx = "";
				@endphp
				<div class="py-2">
					@component("components.labels.label", ["label" => "Sección de Ticket que puede Revisar (Opcional)"]) @endcomponent
					@component("components.inputs.select", ["options" => $options, "attributeEx" => "name=\"section_id[]\" id=\"multiple-section\" ".$attributeEx, "classEx" => "js-sections removeselect"]) @endcomponent
				</div>
				@php
					$options = collect();
					foreach(App\RealEmployee::all() as $employee)
					{
						if($user->real_employee_id == $employee->id)
						{
							$options = $options->concat([["value" => $employee->id, "description" => $employee->fullName(), "selected" => "selected"]]);
						}
						else
						{
							$options = $options->concat([["value" => $employee->id, "description" => $employee->fullName()]]);
						}
					}
				@endphp
				<div class="py-2">
					@component("components.labels.label", ["label" => "Empleado relacionado al usuario:"])@endcomponent
					@component("components.inputs.select", ["options" => $options, "attributeEx" => "name=\"real_employee_id\""]) @endcomponent
				</div>
				<div>
					@component("components.labels.label", ["label" => "Caja chica"])@endcomponent
					@component("components.inputs.switch") @slot("attributeEx") name="cash" type="checkbox" value="1" id="cash" @if($user->cash) checked @endif @endslot @endcomponent
				</div>
				<div class="py-2 {{$user->cash==0 ? 'hidden' : ''}}">
					@component("components.labels.label") Ingrese la cantidad: @endcomponent
					@component("components.inputs.input-text") @slot("classEx") input-text cash_amount @endslot @slot("attributeEx") type="text" name="cash_amount" placeholder="Ingrese la cantidad" data-validation="required" data-validation-depends-on="cash" @if($user->cash) value="{{$user->cash_amount}}" @endif @endslot @endcomponent
				</div>
				<div class="py-2">
					@component("components.labels.label", ["label" => "Personal AdGlobal"]) @endcomponent
					@component("components.inputs.switch") @slot("attributeEx") name="adglobal" type="checkbox" value="1" id="adglobal" @if($user->adglobal) checked @endif @endslot @endcomponent
				</div>
			</div>
		@endcomponent
		@component('components.labels.title-divisor')    CUENTAS BANCARIAS @endcomponent
		@php
			$options = collect();
		@endphp
		@component("components.containers.container-form", ["attributeEx" => "id=\"banks\""])
			<div class="col-span-2">
				@component("components.labels.label") Banco: @endcomponent
				@component("components.inputs.select", ["options" => $options, "classEx" => "input-select-2 bank"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" placeholder=\"Ingrese el alias\"", "classEx" => "alias"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * Número de tarjeta: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" data-validation=\"tarjeta\" placeholder=\"Ingrese el número de tarjeta\"", "classEx" => "input-text card-number"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * CLABE interbancaria: @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" data-validation=\"clabe\" placeholder=\"Ingrese la CLABE\"", "classEx" => "input-text clabe"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") * Número de cuenta @endcomponent
				@component("components.inputs.input-text", ["attributeEx" => "type=\"text\" data-validation=\"cuenta\" placeholder=\"Ingrese la cuenta bancaria\"", "classEx" => "input-text account"]) @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component("components.labels.label", ["classEx" => "col-span-2 w-full"]) *Para agregar una cuenta nueva es necesario colocar al menos uno de los campos. @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"add\" id=\"add\"", "classEx" => "add2", "label" => "<span class=\"icon-plus\"></span> Agregar"]) @endcomponent
			</div>
		@endcomponent

		@php
			$modelBody = [];
			foreach(App\Employee::where('idUsers',$user->id)->where('visible',1)->get() as $emp)
			{
				foreach(App\Banks::where('idBanks',$emp->idBanks)->get() as $bank)
				{
					$body = [
						"classEx" => "tr",
						[
							"show" => "true",
							"content" => 
							[
								["label" => $emp->bank->description],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"idEmployee[]\" value=\"".$emp->idEmployee."\"", "classEx" => "idEmployee"],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"bank[]\" value=\"".$emp->idBanks."\"", "classEx" => "idEmployee"]
							]
						]
					];
					if($emp->alias=='')
					{
						$body[] =
						[
							"show" => "true",
							"content" =>
							[
								["label" => "---"],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"alias[]\" value=\"".$emp->alias."\""]
							]
						];
					}
					else
					{
						$body[] =
						[
							"content" =>
							[
								["label" => $emp->alias],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"alias[]\" value=\"".$emp->alias."\""]
							]
						];
					}

					if($emp->cardNumber=='')
					{
						$body[] =
						[
							"content" =>
							[
								["label" => "---"],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"card[]\" value=\"".$emp->cardNumber."\""]
							]
						];
					}
					else
					{
						$body[] =
						[
							"content" =>
							[
								["label" => $emp->cardNumber],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"card[]\" value=\"".$emp->cardNumber."\""]
							]
						];
					}

					if($emp->clabe=='')
					{
						$body[] =
						[
							"content" =>
							[
								["label" => "---"],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"clabe[]\" value=\"".$emp->clabe."\""]
							]
						];
					}
					else
					{
						$body[] =
						[
							"content" =>
							[
								["label" => $emp->clabe],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"clabe[]\" value=\"".$emp->clabe."\""]
							]
						];
					}

					if($emp->account=='')
					{
						$body[] =
						[
							"content" =>
							[
								["label" => "---"],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"account[]\" value=\"".$emp->account."\""]
							]
						];
					}
					else
					{
						$body[] =
						[
							"content" =>
							[
								["label" => $emp->account],
								["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"account[]\" value=\"".$emp->account."\""]
							]
						];
					}
					$body[] =
					[
						"content" =>
						[
							["kind" => "components.buttons.button", "variant" => "red", "attributeEx" => "type=\"button\"", "classEx" => "delete-item", "label" => "<span class=\"icon-x\"></span>"]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@AlwaysVisibleTable(["modelHead" => ["Banco", "Alias", "Número de tarjeta", "CLABE interbancaria", "Número de cuenta", "Acciones"], "modelBody" => $modelBody, "attributeExBody" => "id=\"banks-body\""]) @endAlwaysVisibleTable
		
		<div id="delete"></div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\"", "label" => "GUARDAR CAMBIOS"]) @endcomponent
			@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset"]) 
				@slot("attributeEx")
					@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
					@else 
						href="{{ url(App\Module::find($child_id)->url) }}" 
					@endif 
				@endslot 
				Regresar
			@endcomponent
		</div>
	@endcomponent
	<div id="view-permission">
		@component("components.labels.title-divisor") ACCESO A MÓDULOS @endcomponent
		<div class="form-container">
			<div class="div-form-group modules">
				@php
					$accessMod = $user->module->pluck('id')->toArray();
				@endphp
				@component("components.containers.container-cards")
					@slot("fields")
						{!! App\Http\Controllers\ConfiguracionUsuarioController::build_modules(NULL,$accessMod, true) !!}
					@endslot
				@endcomponent
			</div>
		</div>
		@component("components.containers.container-cards", 
		[
			"title" 	=> "APLICAR PERMISOS DE FORMA GLOBAL", 
			"subtitle"	=> "Seleccione las empresas y departamentos a los que tendrán acceso en los módulos activados."
		])
			@slot("fields")
			<div>
					<div class="float-right">
						@component("components.buttons.button", ["variant" => "success", "classEx" => "all-select select", "attributeEx" => "type=\"button\" data-target=\"enterprises-permission-global\"", "label" => " todas"]) @endcomponent
					</div>
					@php
						$options = collect();
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
						}
					@endphp
					@component("components.labels.label", ["classEx" => "pt-3", "label" => "Empresa:"]) @endcomponent
					@component("components.inputs.select", ["options" => $options, "classEx" => "enterprises-permission-global", "attributeEx" => "name=\"enterpriseid_global[]\""]) @endcomponent
				</div>
				<div>
					<div class="float-right">
						@component("components.buttons.button", ["variant" => "success", "classEx" => "all-select  select", "attributeEx" => "type=\"button\" data-target=\"departments-permission-global\"", "label" => " todos"]) @endcomponent
					</div>
					@php
						$options = collect();
						foreach(App\Department::orderName()->where('status','ACTIVE')->get() as $department)
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
						}
					@endphp
					@component("components.labels.label", ["classEx" => "pt-3", "label" => "Departamento:"]) @endcomponent
					@component("components.inputs.select", ["options" => $options, "classEx" => "departments-permission-global", "attributeEx" => "name=\"departmentid_global[]\""]) @endcomponent
				</div>
				<div class="text-center">
					@component("components.buttons.button",["classEx" => "mt-2", "variant" => "primary", "attributeEx" => "type=\"button\" id=\"apply_permission_global\"", "label" => "Aplicar"]) @endcomponent
				</div>
			@endslot
		@endcomponent
		<div class="form-container">
			@component("components.labels.title-divisor") ACCESO A MÓDULOS ESPECÍFICOS @endcomponent
			@php
				$modelBody = [];
				$colsArray = [];
				foreach(App\Module::where('father',null)->where('permissionRequire',1)->orderBy('name')->get() as $moduleFather)
				{
					$max = 0;
					foreach(App\Module::where('father',$moduleFather->id)->orderBy('itemOrder')->orderBy('name')->get() as $admin)
					{
						if(App\Module::where('father',$admin->id)->orderBy('itemOrder')->orderBy('name')->count() > $max)
						{
							$max = App\Module::where('father',$admin->id)->orderBy('itemOrder')->orderBy('name')->count();
						}
					}
					$colsArray[] =  $max;
				}
				$i = 0;
			@endphp
			
			@foreach(App\Module::where('father',null)->where('permissionRequire',1)->orderBy('name')->get() as $moduleFather)
				@php
					$modelBody = [];
					foreach(App\Module::where('father',$moduleFather->id)->orderBy('itemOrder')->orderBy('name')->get() as $admin)
					{
						$body = [];									
						if(in_array($admin->id, $accessMod))
						{
							$checked = "checked";
						}
						else
						{
							$checked = "";
						}
						$body[] = 
						[
							"content" =>
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $admin->name
								],
								[
									"kind" 			=> "components.inputs.switch",
									"attributeEx" 	=> "hidden id=\"admin_".$admin->id."\" value=\"".$admin->id."\" data-father=\"father_".$moduleFather->id."\" ".$checked,
									"classExLabel" 	=> "hidden"
								]
							]
						];
									
						foreach(App\Module::where('father',$admin->id)->orderBy('itemOrder')->orderBy('name')->get() as $submodule)
						{
							if(in_array($submodule->id, $accessMod))
							{
								$checked = "checked";
							}
							else
							{
								$checked  = "";
							}
							if(!in_array($submodule->id, $accessMod))
							{
								$hidden = " hidden";
							}
							else
							{
								$hidden = "";
							}
							if(!in_array($submodule->id,[101,127,253,254,255,256,257,361,362]))
							{
								$button = 
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "type=\"button\" data-id=\"".$submodule->id."\" data-permission-type=\"".$submodule->permission_type."\"",
									"variant" 		=> "success",
									"classEx" 		=> "follow-btn editModule".$hidden,
									"label" 		=> "<span class=\"icon-pencil\"></span>"
								];
							}
							else
							{
								$button = 
								[
									"label" => ""
								];
							}

							$body[] = 
							[
								"content" =>
								[
									[
										"kind" 		=> "components.labels.label",
										"label"		=> ucwords($submodule->name),
										"classEx"	=> "module_title w-24"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"$submodule->global_permission\"",
										"classEx"		=> "global_permission"
									],
									[
										"kind" 				=> "components.inputs.switch",
										"attributeEx" 		=> "name=\"module[]\" type=\"checkbox\" hidden id=\"module_".$submodule->id."\" value=\"$submodule->id\" data-father=\"admin_".$admin->id."\" data-permission-type=\"".$submodule->permission_type."\" ".$checked,
										"classEx" 			=> "newmodules"
									],		
									$button
								]
							];
							
							$body[0]["show"] = "true";
						}
						for($j = 0; $j < ($colsArray[$i] - App\Module::where('father',$admin->id)->orderBy('itemOrder')->orderBy('name')->count()); $j++)
						{
							$body[]["content"] = 
							[
								"kind" => "components.labels.label",
								"label"=> "",
							];
						}			
						$modelBody[]= $body;		
					}
					$i++;

					$modelHead = [];
					for($j = 0; $j < count($modelBody[0]); $j++)
					{
						array_push($modelHead, ["value" => ""]);
					}
					$modelHead[0]["show"] = "true";
					
					if(in_array($moduleFather->id, $accessMod))
					{
						$checked = "checked";
					}
					else
					{
						$checked = "";
					}
	
					$titleSticky = 
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $moduleFather->name,
							"classEx" 	=> "text-white text-xl font-semibold"
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"checkbox\" hidden id=\"father_".$moduleFather->id."\" value=\"$moduleFather->id\" ".$checked,
							"classEx" 		=> "hidden"
						]
					];
				@endphp
				@TableUsers(["rowClass" => "module_buttons", "modelHead" => $modelHead, "modelBody" => $modelBody, "title" => $titleSticky, "noHeads" => true]) @endTableUsers
			@endforeach
			<input type="hidden" id="idmodule">
			<input type="hidden" id="permission_type">
		</div>
	</div>
	@component("components.modals.modal", ["attributeEx" => "id=\"myModal\"", "modalTitle" => "Permisos"]) 
		@slot('modalBody')
			<div>
				<div class="float-right">
					@component("components.buttons.button", ["variant" => "success", "classEx" => "all-select select", "attributeEx" => "type=\"button\" data-target=\"js-enterprises-permission\"", "label" => " todas"]) @endcomponent
				</div>
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->where('status','ACTIVE')->get() as $enterprise)
					{
						$options =  $options->concat([["value" => $enterprise->id, "description" => $enterprise->name]]);
					}
				@endphp
				@component("components.labels.label", ["classEx" => "pt-3"]) Seleccione una o varias empresas: @endcomponent
				@component("components.inputs.select", ["classEx" => "js-enterprises-permission", "options" => $options, "attributeEx" => "name=\"enterpriseid\""]) @endcomponent
			</div>
			<div>
				<div class="float-right">
					@component("components.buttons.button", ["variant" => "success", "classEx" => "all-select select", "attributeEx" => "type=\"button\" data-target=\"js-departments-permission\"", "label" => " todos"]) @endcomponent						
				</div>
				@php
					$options = collect();
					foreach(App\Department::orderName()->where('status','ACTIVE')->get() as $department)
					{
						$options =  $options->concat([["value" => $department->id, "description" => $department->name]]);
					}
				@endphp
				@component("components.labels.label", ["classEx" => "pt-3"]) Seleccione uno o varios departamentos: @endcomponent
				@component("components.inputs.select", ["classEx" => "js-departments-permission", "options" => $options, "attributeEx" => "name=\"departmentid\""]) @endcomponent
			</div>
			<div class="view-permission-project">
				<div class="float-right">
					@component("components.buttons.button", ["variant" => "success", "label" => " todos", "attributeEx" => "type=\"button\" data-target=\"project-request\"", "classEx" => "all-select select"])@endcomponent
				</div>
				@component("components.labels.label", ["classEx" => "pt-3"]) Proyecto: @endcomponent
				@component("components.inputs.select", ["options" => [], "attributeEx" => "id=\"project_request\"", "classEx" => "project-request"]) @endcomponent
			</div>
			<div class="view-global-permission py-4">
				@component("components.labels.label") Seleccione si el usuario tendrá permiso para ver todas las solicitudes: @endcomponent
				@component("components.inputs.select", ["options" => [["value" => "0", "description" => "No"], ["value" => "1", "description" => "Sí"]], "attributeEx" => "name=\"global_permission\"", "classEx" => "js-global-permission"]) @endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
				@component("components.buttons.button", ["variant" => "primary", "attributeEx" => "type=\"button\" id=\"add_permission\"", "label" => "Aceptar"]) @endcomponent
				@component("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" data-dismiss=\"modal\"", "label" => "Cerrar"]) @endcomponent
			</div>
		@endslot
	@endcomponent
	@component("components.modals.modal", ["attributeEx" => "id=\"editionPermisionModal\"", "modalTitle" => "Selección de permisos"])
		@slot("modalBody")
			@php
				$options = collect();
				foreach(App\Project::whereIn('status',[1,2])->orderBy('proyectName','asc')->get() as $project)
				{
					$options  = $options->concat([["value" => $project->idproyect, "description" => $project->proyectName]]);
				}
			@endphp
			<div class="py-4">
				<div class="float-right">
					@component("components.buttons.button", ["variant" => "success", "label" => " todos", "attributeEx" => "type=\"button\" data-target=\"project-request\"", "classEx" => "all-select select"])@endcomponent
				</div>
				@component("components.labels.label") Proyecto: @endcomponent
				@component("components.inputs.select", ["classEx" => "project-request", "attributeEx" => "id=\"project_permission\"", "options" => $options]) @endcomponent
			</div>
			@php
				$options = collect();
				foreach (App\RequisitionType::where('status',1)->get() as $rq)
				{
					$options  = $options->concat([["value" => $rq->id, "description" => $rq->name]]);
				}
			@endphp
			<div class="view-permission-requisition py-4">
				@component("components.labels.label") Permiso por tipo de requisición: @endcomponent
				@component("components.inputs.select", ["attributeEx" => "id=\"requisition_permission\"", "options" => $options]) @endcomponent
			</div>
			@php
				$options = collect();
				$options  = $options->concat([["value" => "0", "description" => "No"]]);
				$options  = $options->concat([["value" => "1", "description" => "Sí"]]);
			@endphp
			<div class="view-global-permission py-4">
				@component("components.labels.label") Seleccione si el usuario tendrá permiso para ver todas las solicitudes: @endcomponent
				@component("components.inputs.select", ["classEx" => "js-global-permission-rq", "attributeEx" => "name=\"global_permission_rq\"", "options" => $options]) @endcomponent
			</div>
			<div class="view-quality-permission py-4">
				@component("components.labels.label") Seleccione si el usuario podrá subir archivos de calidad: @endcomponent
				@component("components.inputs.select", ["classEx" => "js-quality-permission", "attributeEx" => "name=\"quality_permission\"", "options" => [["value" => "0", "description" => "No"],["value" => "1", "description" => "Sí"]]]) @endcomponent
			</div>
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
				@component("components.buttons.button", ["attributeEx" => "type=\"button\" id=\"add_permission\"", "label" => "Aceptar"]) @endcomponent
				@component("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" data-dismiss=\"modal\"", "label" => "Cerrar"]) @endcomponent
			</div>
		@endslot
	@endcomponent
	@component("components.modals.modal", ["attributeEx" => "id=\"uploadFilePermission\"", "modalTitle" => "Permisos de Carga de Archivos"])
		@slot("modalBody")
			@php
				$options = collect();
				$options  = $options->concat([["value" => "0", "description" => "No"]]);
				$options  = $options->concat([["value" => "1", "description" => "Sí"]]);
			@endphp
			@component("components.labels.label") Permiso para cargar: @endcomponent
			@component("components.inputs.select", ["attributeEx" => "id=\"upload_file_permission\"", "options" => $options]) @endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6 mt-4">
				@component("components.buttons.button", ["attributeEx" => "type=\"button\" id=\"add_permission\"", "label" => "Aceptar"]) @endcomponent
				@component("components.buttons.button", ["variant" => "red", "attributeEx" => "type=\"button\" data-dismiss=\"modal\"", "label" => "Cerrar"]) @endcomponent
			</div>
		@endslot
	@endcomponent
@endsection
@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	function validation()
	{
		$.validate(
		{
			form		: '#container-alta',
			modules		: 'security',
			onSuccess	: function($form)
			{
				gender = $('input[name="gender"]').is(':checked');
				if(gender == false)
				{
					swal('', 'Debe seleccionar el género (Hombre/Mujer)', 'error');
					return false;
				}
				else
				{
					swal("Cargando, espere a ser redireccionado",{
						icon: '{{ url('images/loading.svg') }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
			
			},
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			}
		});
	}
	$(document).ready(function()
	{
		validation();
		$('.phone,.extension').numeric(false);
		$('input[name="phone"]').numeric(false);
		$('.cash_amount').numeric({negative: false, altDecimal: ".", decimalPlaces: 2 });
		$('.card-number,.clabe,.account').numeric(false);
		generalSelect({'selector': '.bank', 'model': 28});
		@ScriptSelect(
		[ 
			"selects" =>
			[
				[
					"identificator"          => ".js-sections", 
					"placeholder"            => "Seleccione la sección de ticket", 
					"language"				 => "es"
				],
				[
					"identificator"          => ".js-kindbank", 
					"placeholder"            => "Seleccione el banco", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => ".js-enterprises", 
					"placeholder"            => "Seleccione la empresa", 
					"language"				 => "es",
				],	
				[
					"identificator"          => ".js-areas", 
					"placeholder"            => "Seleccione la dirección", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"          => "[name=\"real_employee_id\"]", 
					"placeholder"            => "Seleccione el empleado", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],		
				[
					"identificator"          => ".js-departments", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],	
				[
					"identificator"          => ".js-departments", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],	
				[
					"identificator"          => ".js-departmentsRA", 
					"placeholder"            => "Seleccione el departamento", 
					"language"				 => "es",
				],
				[
					"identificator"          => ".enterprises-permission-global", 
					"placeholder"            => "Seleccione una o varias empresas", 
					"language"				 => "es",
				],	
				[
					"identificator"          => ".departments-permission-global", 
					"placeholder"            => "Seleccione uno o varios departamentos", 
					"language"				 => "es",
				],	
				[
					"identificator"          => ".js-global-permission", 
					"placeholder"            => "Permiso para ver todas las solicitudes", 
					"language"				 => "es",
					"maximumSelectionLength" => "1"
				],
			]
		]) @endScriptSelect
		generalSelect({'selector':'.bank', 'model':27});
		$(document).on('change','input[type="checkbox"].newmodules',function()
		{
			checkBox		 = $(this);
			id				 = $(this).val();
			permisssion_type = $(this).attr('data-permission-type');
			if (permisssion_type == 2 || permisssion_type == 7)
			{
				if(checkBox.is(':checked'))
				{
					$('.js-global-permission').val('').trigger('change');
					$('#idmodule').val(id);
					$('#permission_type').val(permisssion_type);
					checkBox.prop('checked',false);
					$('#myModal').modal('show');
					if (permisssion_type == 7)
					{					
						$('.view-permission-project').show();
					}
					else
					{
						$('.view-permission-project').hide();
					}
	
					global_permission = $(this).parents('.module_buttons').find('.global_permission').val();
					if (global_permission == "1") 
					{
						$('.view-global-permission').show();
					}
					else
					{
						$('.view-global-permission').hide();
					}
				}
				else
				{
					swal({
						title		: "Confirme que desea continuar",
						text		: "Esta acción eliminará el acceso del usuario al módulo, ¿desea continuar?",
						icon		: "warning",
						buttons		:
						{
							cancel:
							{
								text		: "Cancelar",
								value		: null,
								visible		: true,
								closeModal	: true,
							},
							confirm:
							{
								text		: "Eliminar",
								value		: true,
								closeModal	: false
							}
						},
						dangerMode	: true,
					})
					.then((willDelete) =>
					{
						if (willDelete)
						{
							father	= checkBox.attr('data-father');
							if($('[data-father="'+father+'"]:checked').length>0)
							{
								$('#'+father).prop('checked',true);
							}
							else
							{
								$('#'+father).prop('checked',false);
							}
							father2	= $('#'+father).attr('data-father');
							if($('[data-father="'+father2+'"]:checked').length>0)
							{
								$('#'+father2).prop('checked',true);
							}
							else
							{
								$('#'+father2).prop('checked',false);
							}
							additional	= [];
							if(!$('#'+father).is(':checked'))
							{
								additional.push($('#'+father).val());
							}
							if(!$('#'+father2).is(':checked'))
							{
								additional.push($('#'+father2).val());
							}
							if(id == 223)
							{
								additional.push('222') //desglose
								additional.push('219') //listado de insumos
								additional.push('223') //precios unitatios
								additional.push('221') //presupuestos
								additional.push('226') //programa de obra
								additional.push('227') //sobrecosto
								additional.push('220') //busqueda
							}
							$.ajax(
							{
								type	: 'post',
								url		: '{{ route('user.module.permission.update') }}',
								data	: {'module' : id, 'user': {{$user->id}},'action':'off','additional':additional },
								success	:function(data)
								{
									if(data == 'DONE')
									{
										checkBox.parents('.module_buttons').find('.follow-btn.editModule').hide();
										swal.close();
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
								}
							});
						}
						else
						{
							checkBox.prop('checked',true);
						}
					});
				}
			}
			else if(permisssion_type == 3 || permisssion_type == 4 || permisssion_type == 6)
			{
				if(checkBox.is(':checked'))
				{
					$('.js-global-permission').val('').trigger('change');
					$('#idmodule').val(id);
					$('#permission_type').val(permisssion_type);
					checkBox.prop('checked',false);
					$('#editionPermisionModal').modal('show');
					
					global_permission = $(this).parent('.module_buttons').find('.global_permission').val();
					if (global_permission == "1") 
					{
						$('.view-global-permission').show();
					}
					else
					{
						$('.view-global-permission').hide();
					}
					if(permisssion_type == 3)
					{
						$('.view-permission-requisition').hide();
						$('.view-quality-permission').hide();
					}
					if(permisssion_type == 6)
					{
						$('.view-permission-requisition').hide();
						$('.view-quality-permission').show();
					}
					if (permisssion_type == 4)
					{
						$('.view-permission-requisition').show();
						$('.view-quality-permission').hide();
					}
				}
				else
				{
					swal({
						title		: "Confirme que desea continuar",
						text		: "Esta acción eliminará el acceso del usuario al módulo, ¿desea continuar?",
						icon		: "warning",
						buttons		:
						{
							cancel:
							{
								text		: "Cancelar",
								value		: null,
								visible		: true,
								closeModal	: true,
							},
							confirm:
							{
								text		: "Eliminar",
								value		: true,
								closeModal	: false
							}
						},
						dangerMode	: true,
					})
					.then((willDelete) =>
					{
						if (willDelete)
						{
							father	= checkBox.attr('data-father');
							if($('[data-father="'+father+'"]:checked').length>0)
							{
								$('#'+father).prop('checked',true);
							}
							else
							{
								$('#'+father).prop('checked',false);
							}
							father2	= $('#'+father).attr('data-father');
							if($('[data-father="'+father2+'"]:checked').length>0)
							{
								$('#'+father2).prop('checked',true);
							}
							else
							{
								$('#'+father2).prop('checked',false);
							}
							additional	= [];
							if(!$('#'+father).is(':checked'))
							{
								additional.push($('#'+father).val());
							}
							if(!$('#'+father2).is(':checked'))
							{
								additional.push($('#'+father2).val());
							}
							if(id == 223)
							{
								additional.push('222') //desglose
								additional.push('219') //listado de insumos
								additional.push('223') //precios unitatios
								additional.push('221') //presupuestos
								additional.push('226') //programa de obra
								additional.push('227') //sobrecosto
								additional.push('220') //busqueda
							}
							$.ajax(
							{
								type	: 'post',
								url		: '{{ route('user.module.permission.update') }}',
								data	: {'module' : id, 'user': {{$user->id}},'action':'off','additional':additional },
								success	:function(data)
								{
									if(data == 'DONE')
									{
										checkBox.parents('.module_buttons').find('.follow-btn.editModule').hide();
										swal.close();
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
								}
							});
						}
						else
						{
							checkBox.prop('checked',true);
						}
					});
				}
			}
			else if(permisssion_type == 5)
			{			
				if(checkBox.is(':checked'))
				{
					$('#idmodule').val(id);
					$('#permission_type').val(permisssion_type);
					checkBox.prop('checked',false);
					$('#uploadFilePermission').modal('show');
					@ScriptSelect(
					[
						"selects" =>
						[
							[
								"identificator"          => "#upload_file_permission", 
								"placeholder"            => "Seleccione un permiso", 
								"language"				 => "es",
								"maximumSelectionLength" =>	"1"
							],	
						]
					]) @endScriptSelect
				}
				else
				{
					swal({
						title		: "Confirme que desea continuar",
						text		: "Esta acción eliminará el acceso del usuario al módulo, ¿desea continuar?",
						icon		: "warning",
						buttons		:
						{
							cancel:
							{
								text		: "Cancelar",
								value		: null,
								visible		: true,
								closeModal	: true,
							},
							confirm:
							{
								text		: "Eliminar",
								value		: true,
								closeModal	: false
							}
						},
						dangerMode	: true,
					})
					.then((willDelete) =>
					{
						if (willDelete)
						{
							father	= checkBox.attr('data-father');
							if($('[data-father="'+father+'"]:checked').length>0)
							{
								$('#'+father).prop('checked',true);
							}
							else
							{
								$('#'+father).prop('checked',false);
							}
	
							father2	= $('#'+father).attr('data-father');
							if($('[data-father="'+father2+'"]:checked').length>0)
							{
								$('#'+father2).prop('checked',true);
							}
							else
							{
								$('#'+father2).prop('checked',false);
							}
							additional	= [];
							$.ajax(
							{
								type	: 'post',
								url		: '{{ route('user.module.permission.update') }}',
								data	: {'module' : id, 'user': {{$user->id}},'action':'off','additional':additional },
								success	:function(data)
								{
									if(data == 'DONE')
									{
										checkBox.parents('.module_buttons').find('.follow-btn.editModule').hide();
										swal.close();
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
								}
							});
						}
						else
						{
							checkBox.prop('checked',true);
						}
					});
				}
			}
			else
			{
				swal({
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false,
				});
				if(checkBox.is(':checked'))
				{
					action = 'on';
				}
				else
				{
					action = 'off'
				}
				if(id == 223)
				{
					additional.push('222') //desglose
					additional.push('219') //listado de insumos
					additional.push('223') //precios unitatios
					additional.push('221') //presupuestos
					additional.push('226') //programa de obra
					additional.push('227') //sobrecosto
					additional.push('220') //busqueda
				}
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('user.module.permission.update') }}',
					data	: {'module' : id, 'user': {{$user->id}},'action':action },
					success	:function(data)
					{
						if(data == 'DONE')
						{
							swal.close();
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
			}
			generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
			@ScriptSelect(
			[ 
				"selects" =>
				[
					[
						"identificator"          => ".js-global-permission", 
						"placeholder"            => "Permiso para ver todas las solicitudes", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]
			]) @endScriptSelect
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator"          => "#requisition_permission", 
						"placeholder"            => "Seleccione uno o varios", 
						"language"				 => "es",
					],	
					[
						"identificator"          => ".js-quality-permission", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
					],	
					[
						"identificator"          => "#upload_file_permission", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
						"maximumSelectionLength" =>	"1"
					],
					[
						"identificator"          => ".js-enterprises-permission", 
						"placeholder"            => "Seleccione una o varias empresas", 
						"language"				 => "es",
					],	
					[
						"identificator"          => ".js-departments-permission", 
						"placeholder"            => "Seleccione uno o varios departamentos", 
						"language"				 => "es",
					],
					[
						"identificator"          => ".js-global-permission-rq", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"global_permission\"]", 
						"placeholder"            => "Seleccione uno o varios departamentos", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					]
				]
			])
			@endScriptSelect
			generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
		})
		.on('click','.editModule',function()
		{
			id 					= $(this).attr('data-id');
			permisssion_type	= $(this).attr('data-permission-type');
			global_permission	= $(this).parents('.module_buttons').find('.global_permission').val();
			if (global_permission == "1") 
			{
				$('.view-global-permission').show();
			}
			else
			{
				$('.view-global-permission').hide();
			}
			if($(this).parents('.module_buttons').find('[name="module[]"]').is(':checked'))
			{
				swal('Cargando',{
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false,
				});
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("user.module.permission") }}',
					data	: {'module' : id, 'user': {{$user->id}} },
					success	:function(data)
					{
						flag = true;
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if(flag)
					{
						$('#idmodule').val(id);
						$('#permission_type').val(permisssion_type);
						if( permisssion_type == 3 || permisssion_type == 4 || permisssion_type == 6)
						{
							$('#project_permission').val(data.project).trigger('change');
							$('#requisition_permission').val(data.requisition).trigger('change');
							if (data.global_permission == 1) 
							{
								$('.js-global-permission-rq').val(data.global_permission).trigger('change');
							}
							else
							{
								$('.js-global-permission-rq').val(0);
							}
							$('#editionPermisionModal').modal('show');
							if (data.quality_permission == 1) 
							{
								$('.js-quality-permission').val(data.quality_permission).trigger('change');
							}
							else
							{
								$('.js-quality-permission').val(0).trigger('change');
							}
							if(permisssion_type == 3)
							{
								$('.view-permission-requisition').hide();
								$('.view-quality-permission').hide();
							}
							if (permisssion_type == 4)
							{
								$('.view-permission-requisition').show();
								$('.view-quality-permission').hide();
							}
							if(permisssion_type == 6)
							{
								$('.view-permission-requisition').hide();
								$('.view-quality-permission').show();
							}		
						}
						else if(permisssion_type == 5)
						{
							$('#upload_file_permission').val(data.upload_files).trigger('change');
							$('#uploadFilePermission').modal('show');
							@ScriptSelect(
							[
								"selects" =>
								[
									[
										"identificator"          => "#upload_file_permission", 
										"placeholder"            => "Seleccione un permiso", 
										"language"				 => "es",
										"maximumSelectionLength" =>	"1"
									],	
								]
							]) @endScriptSelect
						}
						else
						{
							$('.js-enterprises-permission').val(data.enterprise).trigger('change');
							$('.js-departments-permission').val(data.department).trigger('change');
							if (data.global_permission == 1) 
							{
								$('.js-global-permission').val(data.global_permission).trigger('change');
							}
							else
							{
								$('.js-global-permission').val(0).trigger('change');
							}
	
							if(permisssion_type == 7)
							{
								$('#project_request').val(data.project).trigger('change');						
								$('.view-permission-project').show();
							}
							else
							{
								$('.view-permission-project').hide();
							}
							$('#myModal').modal('show');
						}
						swal.close();
					}
					@ScriptSelect(
					[
						"selects" =>
						[
							[
								"identificator"          => ".js-enterprises-permission", 
								"placeholder"            => "Seleccione una o varias empresas", 
								"language"				 => "es",
							],
							[
								"identificator"          => ".js-departments-permission", 
								"placeholder"            => "Seleccione uno o varios departamentos", 
								"language"				 => "es",
							],
							[
								"identificator"          => ".js-global-permission-rq", 
								"placeholder"            => "Seleccione un permiso", 
								"language"				 => "es",
								"maximumSelectionLength" => "1"
							]
						]
					])
					@endScriptSelect
					generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
				});
			}
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator"          => "#requisition_permission", 
						"placeholder"            => "Seleccione uno o varios", 
						"language"				 => "es",
					],	
					[
						"identificator"          => ".js-quality-permission", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
					],	
					[
						"identificator"          => "#upload_file_permission", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
						"maximumSelectionLength" =>	"1"
					],
					[
						"identificator"          => ".js-enterprises-permission", 
						"placeholder"            => "Seleccione una o varias empresas", 
						"language"				 => "es",
					],	
					[
						"identificator"          => ".js-departments-permission", 
						"placeholder"            => "Seleccione uno o varios departamentos", 
						"language"				 => "es",
					],
					[
						"identificator"          => ".js-global-permission-rq", 
						"placeholder"            => "Seleccione un permiso", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"global_permission\"]", 
						"placeholder"            => "Seleccione uno o varios departamentos", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					]
				]
			])
			@endScriptSelect
			generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
		})
		$(document).on('click', '[name="enviar"]', function()
		{
			name		=	$('input[name="name"]').val().trim();
			lastName	=	$('input[name="last_name"]').val().trim();
			if (name == "" && lastName == "")
			{
				swal('Error','El nombre y el Apellido Paterno no pueden quedar vacíos.','error');
				$('[name="name"], [name="last_name"]').addClass('error');
				return false;
			}
			else if (name == "" || lastName == "")
			{
				if (name == "")
				{
					swal('Error','El nombre no puede quedar vacío.','error')	;
					$('[name="name"]').addClass('error');
					return false;
				}
				else if (lastName == "")
				{
					swal('Error','El Apellido Paterno no puede quedar vacío.','error')	;
					$('[name="last_name"]').addClass('error');
					return false;
				}
			}
		})
		.on('hidden.bs.modal', function (e) 
		{
			$('.js-enterprises-permission').val(null).trigger('change');
			$('.js-departments-permission').val(null).trigger('change');
			$('#project_request').val(null).trigger('change');
			$('#project_permission').val(null).trigger('change');
			$('#requisition_permission').val(null).trigger('change');
			$('.all-select').addClass('select');
			$('#idmodule').val('');
			$('.js-global-permission').val(null).trigger('change');
			$('.modal').modal('hide');
		})
		.on('click','#add_permission',function()
		{
			if(id == 230)
			{
				global_permission = $('[name="global_permission_rq"] option:selected').val();
			}
			else
			{
				global_permission = $('[name="global_permission"] option:selected').val();
			}
			id = $('#idmodule').val();
			permisssion_type = $('#permission_type').val();
	
			if (permisssion_type == 2 && ($('.js-enterprises-permission').val() == '' || $('.js-departments-permission').val() == '')) 
			{
				swal('','Debe seleccionar al menos una empresa y un departamento','error');
			}
			else if (permisssion_type == 3 && $('#project_permission').val() == '')
			{
				swal('','Debe seleccionar al menos un proyecto','error');
			}
			else if (permisssion_type == 4 && ($('#project_permission').val() == '' || $('#requisition_permission').val() == ''))
			{
				swal('','Debe seleccionar al menos un proyecto y un tipo de requisición','error');
			}
			else if (permisssion_type == 5 && $('#upload_file_permission').val() == '')
			{
				swal('','Debe seleccionar una opción.','error');
			}
			else if (permisssion_type == 6 && ($('#project_permission').val() == '' || $('[name="quality_permission"]').val() == ''))
			{
				swal('','Debe seleccionar al menos un proyecto y la opción del documento de calidad','error');
			}
			else if (permisssion_type == 7 && ($('.js-enterprises-permission').val() == '' || $('.js-departments-permission').val() == '' || $('#project_request').val() == ''))
			{
				swal('','Debe seleccionar al menos una empresa, un departamento y un proyecto','error');
			}
			else
			{
				swal({
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false,
				});
				father		= $('#module_'+id).attr('data-father');
				father2		= $('#'+father).attr('data-father');
				additional	= [];
				additional.push($('#'+father).val());
				additional.push($('#'+father2).val());
				proj = '';
				req_type = '';
				upload_file = '';
				quality_file = '';
				if(permisssion_type == 4)
				{
					req_type = $('#requisition_permission').val();
				}
				else if(permisssion_type == 5)
				{
					upload_file = $('#upload_file_permission').val();
				}
				else if(permisssion_type == 6)
				{
					quality_file = $('[name="quality_permission"] option:selected').val();
				}
				if(id == 243)
				{
					additional.push('244') //alta
					additional.push('245') //busqueda
				}
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('user.module.permission.update') }}',
					data	: {
						'module'           : id, 
						'user'             : {{$user->id}},
						'action'           : 'on',
						'enterprise'       : $('.js-enterprises-permission').val(),
						'department'       : $('.js-departments-permission').val(),
						'additional'       : additional,
						'req_type'         : req_type,
						'quality_file'     : quality_file,
						'proj'             : $('#project_permission').val(),
						'project_request'  : $('#project_request').val(),
						'upload_file'      : upload_file,
						'permisssion_type' : permisssion_type,
						'global_permission': global_permission
					},
					success	:function(data)
					{
						if(data == 'DONE')
						{
							$('#module_'+id).prop('checked',true);
							$('#module_'+id).parents('.module_buttons').find('.follow-btn.editModule').show();
							$('.js-enterprises-permission').val(null).trigger('change');
							$('.js-departments-permission').val(null).trigger('change');
							$('#requisition_permission').val(null).trigger('change');
							$('.all-select').addClass('select');
							$('#idmodule').val('');
							$('#myModal').modal('hide');
							$('#editionPermisionModal').modal('hide');
							$('#uploadFilePermission').modal('hide');
							$('#'+father).prop('checked',true);
							$('#'+father2).prop('checked',true);
							$('#project_permission').val(null).trigger('change');
							$('#project_request').val(null).trigger('change');
							$('#upload_file_permission').val(null).trigger('change');
							$('.js-global-permission').val(null).trigger('change');
							$('.js-quality-permission').val(null).trigger('change');
							swal.close();
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
			}
		})
		.on('change','input[type="checkbox"]',function()
		{
			if($(this).attr('name') == 'cash' || $(this).attr('name') == 'adglobal')
			{
			}
			else
			{
				if(this.checked)
				{
					$(this).parents('.li').children('.select-none').children('input[type="checkbox"]').prop('checked',true);
				}
				var checked = $(this).prop("checked");
				var father = $(this).parent().parent();
				father.find('input[type="checkbox"]').prop({
					checked: checked
				});
				function checkSiblings(check)
				{
					var parent = check.parent().parent(),
					all = true;
					check.siblings().each(function(i, v) 
					{
						return all = ($(this).children('.select-none').children('input[type="checkbox"]').prop("checked") === checked);
					});
					if (all && checked) 
					{
						$(this).parents('.li').children('.select-none').children('input[type="checkbox"]').prop('checked',true);
						parent.children('.select-none').children('input[type="checkbox"]').prop({
							checked: checked
						});
						checkSiblings(parent);
					}
					else if(all && !checked)
					{
						parent.children('.select-none').children('input[type="checkbox"]').prop("checked",checked);
						parent.children('.select-none').children('input[type="checkbox"]').prop((parent.find('input[type="checkbox"]').length < 0));
						checkSiblings(parent);
					}
					/*else
					{
						check.children('.select-none').children('input[type="checkbox"]').prop('checked',false);
					}*/
				} 
				checkSiblings(father);
			}
		})
		.on('change','[name="moduleCheck[]"]',function()
		{
			swal({
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false,
			});
			modules = [];
			$('[name="moduleCheck[]"]:checked').each(function(i,v)
			{
				modules.push($(this).val());
			});
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route('user.module.permission.update.simple') }}',
				data	: {'modules' : modules, 'user': {{$user->id}} },
				success	:function(data)
				{
					if(data == 'DONE')
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
		})
		.on('click','.delete-item', function()
		{
			value = $(this).parents('.tr').find('.idEmployee').val();
			del = $('<input type="hidden" name="delete[]">').val(value);
			$('#delete').append(del);
			$(this).parents('.tr').remove();
		})
		.on('change','#cash',function()
		{
			if($(this).is(':checked'))
			{
				$('.cash_amount').parent('div').stop(true,true).slideDown();
			}
			else
			{
				$('.cash_amount').parent('div').stop(true,true).slideUp();
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
					$('.removeselect').val(null).trigger('change');
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('click','#add',function()
		{
			alias		= $('.alias').val();
			card		= $('.card-number').val();
			clabe		= $('.clabe').val();
			account		= $('.account').val();
			bankid		= $('.bank').val();
			bankName	= $('.bank :selected').text();
	
			$(this).parents('.tr').find('.card-number, .clabe, .account').removeClass("error valid");
	
			clabe_tr  = bankAccount_tr = card_tr = true;
	
			$("#banks-body .tr").each(function(i,v)
			{			
				bank_tr		= $(this).find("[name='bank[]']").val();
				account_tr 	= $(this).find("[name='account[]']").val();
	
				if((clabe == $(this).find("[name='clabe[]']").val()) && (clabe != ""))
				{
					clabe_tr = false;
				}
				else if((bankid+" "+account) == (bank_tr+" "+account_tr) && (account != ""))
				{
					bankAccount_tr = false;
				}
				else if((card == $(this).find("[name='card[]']").val()) && (card != ""))
				{
					card_tr = false;
				}
			});
	
			if(clabe_tr == false)
			{
				swal("", "Esta clabe ya ha sido registrada anteriormente", "error");
				return false;
			}
			else if(bankAccount_tr == false)
			{
				swal("", "Esta cuenta bancaria y banco ya han sido registrados anteriormente", "error");
				return false;
			}
			else if(card_tr == false)
			{
				swal("", "Esta tarjeta ya ha sido registrada anteriormente", "error");
				return false;
			}
	
	
			if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$('.card-number, .clabe, .account').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if (alias == "")
				{
					$(".alias").addClass("error");
					swal("", "Debe ingresar todos los campos requeridos", "error");
				}
				else if(clabe != "" && ($(".clabe").hasClass("error") || clabe.length!=18))
				{
					swal("", "Por favor, debe ingresar 18 dígitos de la CLABE.", "error");
					$(".clabe").addClass("error");
				}
				else if(card != "" && ($(".card-number").hasClass("error") || card.length!=16))
				{
					swal("", "Por favor, debe ingresar 16 dígitos del número de tarjeta.", "error");
					$(".card-number").addClass("error");
				}
				else if(account != "" && ($(".account").hasClass("error") || (account.length>15 || account.length<5)))
				{
					swal("", "Por favor, debe ingresar entre 5 y 15 dígitos del número de cuenta bancaria.", "error");
					$(".account").addClass("error");
				}
				else
				{
					@php
						$modelHead = ["Banco", "Alias", "Número de tarjeta", "CLABE interbancaria", "Número de cuenta", "Acciones"];
						$modelBody =
						[
							[	
								"classEx" => "tr",
								[
									"show" => "true",
									"content" => 
									[
										[
											"label"			=> "<label class=\"bankNameClass\"></label>"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idEmployee[]\" value=\"x\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
										]
									]
								],
								[
									"show" => "true",
									"content" =>
									[
										[
											"label"			=> "<label class=\"aliasClass\"></label>"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"label"			=> "<label class=\"cardClass\"></label>"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"label"			=> "<label class=\"clabeClass\"></label>"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"label"			=> "<label class=\"accountClass\"></label>",
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"attributeEx"	=> "type=\"button\"",
											"classEx"		=> "delete-item",
											"variant"		=> "red",
											"label"			=> "<span class=\"icon-x delete-span\"></span>"
										]
									]
								]
							]
						];
						$banks = view('components.tables.alwaysVisibleTable',[
							"modelHead"		=> $modelHead,
							"modelBody"		=> $modelBody,
							"noHead"		=> true
						])->render();
					@endphp
	
					banks = '{!!preg_replace("/(\r)*(\n)*/", "", $banks)!!}';
					
					bank = $(banks);
					bank.find('.bankNameClass').text(bankName);
					bank.find('[name="bank[]"]').val(bankid);
					bank.find('.aliasClass').text(alias =='' ? '---' :alias);
					bank.find('[name="alias[]"]').val(alias);
					bank.find('.cardClass').text(card =='' ? '---' :card);
					bank.find('[name="card[]"]').val(card);
					bank.find('.clabeClass').text(clabe =='' ? '---' :clabe);
					bank.find('[name="clabe[]"]').val(clabe);
					bank.find('.accountClass').text(account =='' ? '---' :account);
					bank.find('[name="account[]"]').val(account);
	
					$('#banks-body').append(bank);
					$('.card-number, .clabe, .account, .alias').removeClass('valid error').val('');
					$('.bank').val(0).trigger("change");
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.bank').addClass('error');
			}
		})
		.on('click','.all-select',function()
		{
			target	= '.'+$(this).attr('data-target');
			if($(this).hasClass('select'))
			{
				$(this).removeClass('select');
				if($(this).attr('data-target') != 'project-request')
				{
					$(target+' option').each(function(i,v)
					{
						$(this).prop('selected',true);
						$(target).trigger('change');
					});
				}
				else
				{
					swal({
						icon				: '{{ asset(getenv('LOADING_IMG')) }}',
						button				: false,
						closeOnClickOutside	: false
					});
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route("general.select") }}',
						dataType: 'json',
						data    : 
						{
							'model'				: 21,
							'module_id'			: {{isset($child_id) ? $child_id : 'null'}},
							'params_data' 		: {'id': $(this).find('select').val()}
						},
						success	:function(data)
						{
							$('.project-request').select2({data: data['results']});
							$('.project-request').find('option').attr('selected',true);
							$('.project-request').select2();
							generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
						}
					})
					.done(function()
					{
						swal.close();
					});
				}
			}
			else
			{
				$(this).addClass('select');
				if($(this).attr('data-target') != 'project-request')
					{
						$(target+' option').each(function(i,v)
						{
							$(this).prop('selected',false);
							$(target).trigger('change');
						});
					}
					else
					{				
						$(target).val(null).trigger("change"); 
						generalSelect({'selector':'.project-request', 'model':21, 'maxSelection':-1});
					}
				}
			})
			.on('click','#apply_permission_global',function()
			{
				modules = [];
				permisssion_type = [];
				$('[name="module[]"]:checked').each(function(i,v)
				{
					modules.push($(this).val());
					permisssion_type.push($(this).attr('data-permission-type'));
				});
				
		
				enterprise = [];
				$('[name="enterpriseid_global[]"] option:selected').each(function(i,v)
				{
					enterprise.push($(this).val());
				});
		
				department = [];
				$('[name="departmentid_global[]"] option:selected').each(function(i,v)
				{
					department.push($(this).val());
				});
		
				if(enterprise != "" && department != "")
				{
					swal({
						title		: "Aplicar cambios",
						text		: "Las siguientes empresas y departamentos se aplicarán a todos los módulos activos. ¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((confirm) =>
					{
						if(confirm)
						{
							swal('Cargando',{
								icon: '{{ asset(getenv('LOADING_IMG')) }}',
								button				: false,
								closeOnClickOutside	: false,
								closeOnEsc			: false,
							});
							$.ajax(
							{
								type	: 'post',
								url		: '{{ route("user.module.permission.update.global") }}',
								data	: {'modules' : modules, 'user': {{$user->id}}, 'enterprise':enterprise, 'department':department, 'permisssion_type':permisssion_type },
								success	:function(data)
								{
									if(data == 'DONE')
									{
										swal.close();
										swal('','Empresas y departamentos aplicados','success');
										$('[name="enterpriseid_global[]"]').val(null).trigger('change');
										$('[name="departmentid_global[]"]').val(null).trigger('change');
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
								}
							});
						}
						else
						{
							swal.close();
						}
					});
				}
				else
				{
					swal('','Debe seleccionar al menos una empresa y un departamento','info');
				}
		
			});
	});
	function getEntDep($value)
	{	
		$.ajax(
		{
			type	: 'get',
			url		: '{{ url("configuration/user/getentdep") }}',
			data	: {'module_id':$value},
			success	:function(data)
			{
				$('.module_'+$value).append(data);
			},
			error : function()
			{
				swal('','Sucedió un error, por favor intente de nuevo.','error');
			}
		});
	}
</script>
@endsection
