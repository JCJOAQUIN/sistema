@extends('layouts.child_module')
@section('data')
	@ContainerForm()
		<div class="col-span-2">
			<div class="py-2">
				@component("components.labels.label") Nombre(s): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text name @endslot
					@slot("attributeEx") type="text" name="name" value="{{ $user->name }}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Apellido paterno: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text last_name @endslot
					@slot("attributeEx") type="text" name="last_name" value="{{ $user->last_name }}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Apellido materno (Opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text scnd_last_name @endslot
					@slot("attributeEx") type="text" name="scnd_last_name" placeholder="Apellido materno" value="{{ $user->scnd_last_name }}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label", ["label" => "Seleccione una opción:"]) @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval",["classExLabel" => "disabled", "attributeEx" => "type=\"radio\" ".($user->gender == "hombre" ? "checked=\"true\"" : "")." name=\"gender\" id=\"hombre\" value=\"hombre\" disabled", "label" => "Hombre"]) @endcomponent
					@component("components.buttons.button-approval",["classExLabel" => "disabled", "attributeEx" => "type=\"radio\" ".($user->gender == "mujer" ? "checked=\"true\"" : "")." name=\"gender\" id=\"mujer\" value=\"mujer\" disabled", "label" => "Mujer"]) @endcomponent
				
				</div>
			</div>
			<div class="py-2">
				@component("components.labels.label") Teléfono (opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text phone @endslot
					@slot("attributeEx") type="text" name="phone" placeholder="10 dígitos" value="{{ $user->phone }}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Extensión (opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text extension @endslot
					@slot("attributeEx") type="text" name="extension" value="{{ $user->extension }}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Correo electrónico: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text email @endslot
					@slot("attributeEx") type="text" name="email" value="{{ $user->email }}" disabled @endslot
				@endcomponent
			</div>
		</div>
		<div class="col-span-2">
			<div class="py-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options 	= collect();
					foreach($enterprises as $enterprise)
					{
						$temp 		= $enterprise->id;
						$flag 		= false;
						foreach($user_has_enterprises as $user_has_enterprise)
						{
							if($temp == $user_has_enterprise->enterprise_id)
							{
								$flag = true;
							}
						}
						$options = $options->concat(
						[
							[
								"value"			=> $enterprise->id, 
								"description"	=> $enterprise->name, 
								"selected"		=> (($flag == true) ? "selected" : "")
							]
						]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") js-enterprises @endslot
					@slot("attributeEx") name="enterprises[]" multiple="multiple" id="multiple-enterprises" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Dirección: @endcomponent
				@php
					$options = collect();
					if(isset($areas))
					{
						foreach($areas as $area)
						{
							$options = $options->concat(
							[
								[
									"value"			=> $area->id, 
									"description"	=> $area->name, 
									"selected"		=> (($user->area_id == $area->id) ? "selected" : "")
								]
							]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") js-areas input-text @endslot
					@slot("attributeEx") multiple="multiple" name="area_id" style="width: 98%;" id="multiple-areas" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach($departments as $department)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $department->id, 
								"description"	=> $department->name, 
								"selected"		=> (($user->departament_id == $department->id) ? "selected" : "")
							]
						]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") js-departments @endslot
					@slot("attributeEx") multiple="multiple" name="department_id" style="width: 98%;" id="multiple-departments" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Puesto: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") input-text position @endslot
					@slot("attributeEx") type="text" name="position" value="{{$user->position}}" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Sección de ticket que puede revisar: @endcomponent
				@php
					$options = collect();
					foreach($sections as $section)
					{
						$flag = false;
						if($user->inReview != null)
						{
							foreach($user->inReview as $inReview)
							{
								if($inReview->idsectionTickets==$section->idsectionTickets)
								{
									$flag = true;
								}
							}
						}
						if($flag)
						{
							$options = $options->concat([["value" => $section->idsectionTickets, "description" => $section->section, "selected" => "selected"]]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") js-sections removeselect @endslot
					@slot("attributeEx")  multiple="multiple" name="section_id[]" style="width: 98%;" id="multiple-section"  @if($user->sys_user == 0) disabled="disabled" @endif disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.labels.label") Empleado relacionado al usuario: @endcomponent
				@php
					$options = collect();
					if(isset($user->real_employee_id))
					{
						foreach(App\RealEmployee::all() as $employee)
						{
							$options = $options->concat(
							[
								[
									"value"			=> $employee->id, 
									"description"	=> $employee->fullName(),
									"selected"		=> (($user->real_employee_id == $employee->id) ? "selected" : "")
								]
							]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") js-departments input-text @endslot
					@slot("attributeEx") multiple="multiple" name="real_employee_id" style="width: 98%;" disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.inputs.switch")
					@slot("attributeEx") name="cash" type="checkbox" value="1" id="cash" @if($user->cash) checked @endif disabled @endslot
					Caja chica
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.inputs.input-text")
					@slot("classEx") input-text cash_amount @endslot
					@slot("attributeEx") type="text" name="cash_amount" @if($user->cash==0) style="display: none;" @endif @if($user->cash) value="{{$user->cash_amount}}" @endif disabled @endslot
				@endcomponent
			</div>
			<div class="py-2">
				@component("components.inputs.switch")
					@slot("classEx") adglobal @endslot
					@slot("attributeEx") name="adglobal" type="checkbox" value="1" id="adglobal" @if($user->adglobal) checked @endif disabled @endslot
					Personal AdGlobal
				@endcomponent
			</div>
		</div>
	@endContainerForm
	@component("components.labels.title-divisor") CUENTAS BANCARIAS @endcomponent
	@php
		$modelHead = 
		[
			"Banco", 
			"Alias", 
			"Número de tarjeta", 
			"CLABE interbancaria", 
			"Número de cuenta"
		];
		$body = [];
		$modelBody = [];
		foreach(App\Employee::where('idUsers',$user->id)->where('visible',1)->get() as $emp)
		{
			foreach(App\Banks::where('idBanks',$emp->idBanks)->get() as $bank)
			{
				$body = 
				[
					[
						"content" =>
						[
							[
								"label"	=> $emp->bank->description
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> (($emp->alias=='') ? "---" : $emp->alias)
							]
						]
					],
					[
						"content" =>
						[
							[								
								"label"	=> (($emp->cardNumber=='') ? "---" : $emp->cardNumber)
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> (($emp->clabe=='') ? "---" : $emp->clabe)
							]
						]
					],
					[
						"content" =>
						[
							[
								"label"	=> (($emp->account=="") ? "---" : $emp->account)
							]
						]
					],
				];
				$modelBody[] = $body;
			}
		}
	@endphp
	@component("components.tables.alwaysVisibleTable", ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot("attributeExBody")
			id="banks-body"
		@endslot
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
										"classEx"	=> "module_title"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"$submodule->global_permission\"",
										"classEx"		=> "global_permission"
									],
									[
										"kind" 			=> "components.inputs.switch",
										"attributeEx" 	=> "name=\"module[]\" type=\"checkbox\" hidden id=\"module_".$submodule->id."\" value=\"$submodule->id\" data-father=\"admin_".$admin->id."\" data-permission-type=\"".$submodule->permission_type."\" ".$checked,
										"classEx" 		=> "newmodules"
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
				@TableUsers(["rowClass" => "module_buttons", "modelHead" => $modelHead, "modelBody" => $modelBody, "title" => $titleSticky, "variant" => "users"]) @endTableUsers
			@endforeach
			<input type="hidden" id="idmodule">
			<input type="hidden" id="permission_type">
		</div>
	</div>
	<div class="flex justify-center mt-4">
		@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset"])		
			@slot("attributeEx")
				@if(isset($option_id)) 
					href="{{ url(App\Module::find($option_id)->url) }}" 
				@else 
					href="{{ url(App\Module::find($child_id)->url) }}" 
				@endif 
			@endslot
			@slot('classEx')
				load-actioner
			@endslot
			REGRESAR
		@endcomponent
	</div>
@endsection

@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript">
	status = @json($user->status);
	$(document).ready(function()
	{
		validation();
		if(status == 'DELETED')
		{
            $("#view-permission :input").prop("disabled", true);
		}
		$('input[name="phone"]').numeric(false);
		$('.cash_amount').numeric({ altDecimal: ".", decimalPlaces: 2 });
		$('.card-number,.clabe,.account,.phone,.extension').numeric(false);
		@ScriptSelect(
		[
			"selects" =>
			[
				[
					"identificator"	=> ".js-banks",
					"placeholder"	=> "Seleccione un banco"
				],
				[
					"identificator"	=> ".bank",
					"placeholder"	=> "Seleccione un banco"
				],
				[
					"identificator"	=> ".js-enterprises",
					"placeholder"	=> "Seleccione una empresa"
				],
				[
					"identificator"	=> ".js-areas",
					"placeholder"	=> "Seleccione una dirección"
				],
				[
					"identificator"	=> ".js-departments",
					"placeholder"	=> "Seleccione un departamento"
				],
				[
					"identificator"	=> ".js-banks",
					"placeholder"	=> "Seleccione un banco"
				],
				[
					"identificator"	=> ".js-departmentsRA",
					"placeholder"	=> "Seleccione un departamento"
				],
				[
					"identificator"	=> ".js-sections",
					"placeholder"	=> "Seleccione una sección"
				],
				[
					"identificator"	=> ".enterprises-permission-global",
					"placeholder"	=> "Seleccione una empresa"
				],
				[
					"identificator"	=> ".departments-permission-global",
					"placeholder"	=> "Seleccione un departamento"
				],
			]
		])
		@endScriptSelect
	
		$(document).on('change','input[type="checkbox"].newmodules',function()
		{
			checkBox	= $(this);
			id			= $(this).val();
			if (id != 127 && id != 101 && id != 229 && id != 230 && id != 231 && id != 232 && id !=260 && id !=261 && id !=276)
			{
				if(checkBox.is(':checked'))
				{
					$('#idmodule').val(id);
					checkBox.prop('checked',false);
					$('#myModal').show();
					$('.js-enterprises-permission').select2(
					{
						placeholder				: 'Seleccione una o varias empresas',
						language				: "es"
					});
					$('.js-departments-permission').select2(
					{
						placeholder				: 'Seleccione uno o varios departamentos',
						language				: "es"
					});
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
										checkBox.siblings('.follow-btn.editModule').hide();
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
			else if(id == 229 || id == 230 || id == 231 || id == 232 || id == 260 || id == 261 || id == 276)
			{
				if(checkBox.is(':checked'))
				{
					$('#idmodule').val(id);
					checkBox.prop('checked',false);
					$('#editionPermisionModal').show();
					$('#project_permission,#requisition_permission').select2(
					{
						placeholder				: 'Seleccione un o varios',
						language				: "es",
						width 					: '100%'
					});
	
					if(id == 229 || id == 230 || id == 260 || id == 261)
					{
						$('.view-permission-requisition').hide();
					}
					if (id == 231 || id == 232 || id == 276 )
					{
						$('.view-permission-requisition').show();
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
										checkBox.siblings('.follow-btn.editModule').hide();
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
		})
		.on('click','.editModule',function()
		{
			id = $(this).attr('data-id');
			if($(this).siblings('[name="module[]"]').is(':checked'))
			{
				swal({
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false,
				});
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('user.module.permission') }}',
					data	: {'module' : id, 'user': {{$user->id}} },
					success	:function(data)
					{
						$('#idmodule').val(id);
						if( id == 229 || id == 230 || id == 231 || id == 232 || id == 260 || id == 261 || id == 276)
						{
							$('#project_permission').val(data.project);
							$('#requisition_permission').val(data.requisition);
							$('#editionPermisionModal').show();
							$('#project_permission,#requisition_permission').select2(
							{
								placeholder				: 'Seleccione un o varios',
								language				: "es",
								width 					: '100%'
							});
	
							if(id == 229 || id == 230 || id == 260 || id == 261)
							{
								$('.view-permission-requisition').hide();
							}
							if (id == 231 || id == 232 || id == 276)
							{
								$('.view-permission-requisition').show();
							}
						}
						else
						{
							$('.js-enterprises-permission').val(data.enterprise);
							$('.js-departments-permission').val(data.department);
							$('#myModal').show();
							$('.js-enterprises-permission').select2(
							{
								placeholder				: 'Seleccione una o varias empresas',
								language				: "es"
							});
							$('.js-departments-permission').select2(
							{
								placeholder				: 'Seleccione uno o varios departamentos',
								language				: "es"
							});
						}
						swal.close();
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
			}
		})
		.on('click','[data-dismiss="modal"]',function()
		{
			$('.js-enterprises-permission').val(null).trigger('change');
			$('.js-departments-permission').val(null).trigger('change');
			$('#project_permission').val(null).trigger('change');
			$('#requisition_permission').val(null).trigger('change');
			$('.all-select').addClass('select');
			$('#idmodule').val('');
		})
		.on('click','#add_permission',function()
		{
			id = $('#idmodule').val();
			if(( id == 229 || id == 230 || id == 231 || id == 232 || id == 260 || id == 261 || id == 276) || ($('.js-enterprises-permission').val() != '' && $('.js-departments-permission').val() != ''))
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
	
				if(id == 231 || id == 232 || id == 276)
				{
					req_type = $('#requisition_permission').val();
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
					data	: {'module' : id, 'user': {{$user->id}},'action':'on','enterprise': $('.js-enterprises-permission').val(),'department':$('.js-departments-permission').val(),'additional':additional,'req_type':req_type,'proj':$('#project_permission').val()},
					success	:function(data)
					{
						if(data == 'DONE')
						{
							$('#module_'+id).prop('checked',true);
							$('#module_'+id).siblings('.follow-btn.editModule').show();
							$('.js-enterprises-permission').val(null).trigger('change');
							$('.js-departments-permission').val(null).trigger('change');
							$('#requisition_permission').val(null).trigger('change');
							$('.all-select').addClass('select');
							$('#idmodule').val('');
							$('#myModal').hide();
							$('#editionPermisionModal').hide();
							$('#'+father).prop('checked',true);
							$('#'+father2).prop('checked',true);
							$('#project_permission').val(null).trigger('change');
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
				swal('','Debe seleccionar al menos una empresa y un departamento','error');
			}
		})
		.on('change','input[type="checkbox"]',function()
		{
			if(this.checked)
			{
				$(this).parents('li').children('input[type="checkbox"]').prop('checked',true);
			}
			var checked = $(this).prop("checked"),
				father = $(this).parent();
	
				father.find('input[type="checkbox"]').prop({
					checked: checked
				});
	
				function checkSiblings(check)
				{
					var parent = check.parent().parent(),
						all = true;
	
					check.siblings().each(function() 
					{
						return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
					});
	
					if (all && checked) 
					{
						$(this).parents('li').children('input[type="checkbox"]').prop('checked',true);
						parent.children('input[type="checkbox"]').prop({
							checked: checked
						});
						checkSiblings(parent);
					}
					else if(all && !checked)
					{
						parent.children('input[type="checkbox"]').prop("checked",checked);
						parent.children('input[type="checkbox"]').prop((parent.find('input[type="checkbox"]').length < 0));
						checkSiblings(parent);
					}
					else
					{
						check.parent("li").children('input[type="checkbox"]').prop('checked',false);
					}
				} 
				checkSiblings(father);
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
			value = $(this).parent('td').parent('tr').find('.idEmployee').val();
			del = $('<input type="hidden" name="delete[]">').val(value);
			$('#delete').append(del);
			$(this).parents('tr').remove();
		})
		.on('change','#cash',function()
		{
			if($(this).is(':checked'))
			{
				$('.cash_amount').stop(true,true).slideDown();
			}
			else
			{
				$('.cash_amount').stop(true,true).slideUp();
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
			alias		= $(this).parents('tr').find('.alias').val();
			card		= $(this).parents('tr').find('.card-number').val();
			clabe		= $(this).parents('tr').find('.clabe').val();
			account		= $(this).parents('tr').find('.account').val();
			bankid		= $(this).parents('tr').find('.bank').val();
			bankName	= $(this).parents('tr').find('.bank :selected').text();
			if(bankid.length>0)
			{
				if (card == "" && clabe == "" && account == "")
				{
					$('.card-number, .clabe, .account').addClass('error');
					swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
				}
				else if($(this).parents('tr').find('.card-number').hasClass('error') || $(this).parents('tr').find('.clabe').hasClass('error') || $(this).parents('tr').find('.account').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					bank = $('<tr></tr>')
							.append($('<td></td>')
								.append(bankName)
								.append($('<input type="hidden" class="idEmployee" name="idEmployee[]" value="x">'))
								.append($('<input type="hidden" name="bank[]" value="'+bankid+'">'))
								)
							.append($('<td></td>')
								.append(alias =='' ? '---' :alias)
								.append($('<input type="hidden" name="alias[]" value="'+alias+'">'))
								)
							.append($('<td></td>')
								.append(card =='' ? '---' :card)
								.append($('<input type="hidden" name="card[]" value="'+card+'">'))
								)
							.append($('<td></td>')
								.append(clabe =='' ? '---' :clabe)
								.append($('<input type="hidden" name="clabe[]" value="'+clabe+'">'))
								)
							.append($('<td></td>')
								.append(account =='' ? '---' :account)
								.append($('<input type="hidden" name="account[]" value="'+account+'">'))
								)
							.append($('<td></td>')
								.append($('<button class="delete-item" type="button"><span class="icon-x delete-span"></span></button>'))
								);
					$('#banks-body').append(bank);
					$('.card-number, .clabe, .account, .alias').removeClass('valid').val('');
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
				$(target+' option').each(function(i,v)
				{
					$(this).prop('selected',true);
					$(target).trigger('change');
				});
			}
			else
			{
				$(this).addClass('select');
				$(target+' option').each(function(i,v)
				{
					$(this).prop('selected',false);
					$(target).trigger('change');
				});
			}
		})
		.on('click','#apply_permission_global',function()
		{
			modules = [];
			$('[name="module[]"]:checked').each(function(i,v)
			{
				modules.push($(this).val());
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
						swal({
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false,
						});
						$.ajax(
						{
							type	: 'post',
							url		: '{{ route('user.module.permission.update.global') }}',
							data	: {'modules' : modules, 'user': {{$user->id}}, 'enterprise':enterprise, 'department':department },
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
</script>
@endsection