@extends("layouts.child_module")
@section("data")
	@component("components.forms.form", ["attributeEx" => "action=\"".route('computer.store')."\" method=\"POST\" id=\"container-alta\""])
		@component("components.labels.title-divisor") NUEVA SOLICITUD @endcomponent
		@component("components.containers.container-form")	
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						required
					@endslot
					@slot("attributeEx") 
						name="title"
						placeholder="Ingrese el título"
						data-validation="required"
						@if(isset($requests)) value="{{ $requests->computer->first()->title }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") 
						datepicker2 
						removeselect 
					@endslot
					@slot("attributeEx") 
						name="datetitle"
						placeholder="Ingrese la fecha"
						data-validation="required"
						readonly="readonly"
						@if(isset($requests)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $requests->computer->first()->datetitle)->format('d-m-Y') }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Solicitante: @endcomponent
				@php
					$options = collect();
					if(isset($requests))
					{
						$options = $options->concat([["value" => $requests->idRequest, "selected" => "selected", "description" => $requests->requestUser->fullName()]]);
					}
					$attributeEx = "name=\"user_id\" id=\"multiple-users\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-users removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeEnt($option_id)->pluck("enterprise_id"))->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if(isset($requests) && $requests->idEnterprise==$enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id\" id=\"multiple-enterprises\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-enterprises removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Dirección: @endcomponent
				@php
					$options = collect();
					foreach(App\Area::orderName()->where("status","ACTIVE")->get() as $area)
					{
						if(isset($requests) && $requests->idArea == $area->id)
						{
							$options = $options->concat([["value" => $area->id, "selected" => "selected", "description" => $area->name]]);
						}
						else
						{
							$options = $options->concat([["value" => $area->id, "description" => $area->name]]);
						}
					}
					$attributeEx = "name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-areas removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach(App\Department::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeDep($option_id)->pluck("departament_id"))->get() as $department)
					{
						if(isset($requests) && $requests->idDepartment == $department->id)
						{
							$options = $options->concat([["value" => $department->id, "selected" => "selected", "description" => $department->name]]);
						}
						else
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
						}
					}
					$attributeEx = "name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-departments removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Clasificación de gastos: @endcomponent
				@php
					$options = collect();
					if (isset($requests))
					{
						$options = [["value" => $requests->account, "selected" => "selected", "description" => $requests->accounts->account." - ".$requests->accounts->description." (".$requests->accounts->content.")"]];
					}
					$attributeEx = "name=\"account_id\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-accounts removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if(isset($requests))
					{
						$options = $options->concat([["value" => $requests->idProject, "selected" => "selected", "description" => $requests->requestProject->proyectName]]);
					}
					$attributeEx = "name=\"project_id\" id=\"multiple-projects\" data-validation=\"required\" multiple=\"multiple\"";
					$classEx = "js-projects removeselect";
				@endphp
				@component ("components.inputs.select", ["options"=>$options, "attributeEx"=>$attributeEx, "classEx"=>$classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ingrese el puesto: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
					@endslot
					@slot("attributeEx")						
						id="position"
						placeholder="Ingrese el puesto"
						name="position"
						data-validation="required"
						@if(isset($requests)) value="{{ $requests->computer->first()->position }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@component("components.labels.label") Nuevo ingreso: @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("classEx")
							entry
						@endslot
						@slot("attributeEx")
							name="entry"
							value="0"
							@if(!isset($requests)) checked @elseif (isset($requests) && $requests->computer->first()->entry != 1) checked @endif
							id="no_entry"
						@endslot
						No
					@endcomponent

					@component("components.buttons.button-approval")
						@slot("classEx")
							entry
						@endslot
						@slot("attributeEx")
							name="entry"
							value="1"
							@if(isset($requests) && $requests->computer->first()->entry == 1) checked @endif
							id="entry"
						@endslot
						Si
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 @if(isset($requests) && $requests->computer->first()->entry == 1) block @else hidden @endif " id="date_entry">
				@component("components.labels.label") Fecha de ingreso: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
					@endslot
					@slot("attributeEx")						
						name="entry_date"
						step="1"
						placeholder="Ingrese la fecha"
						id="datepicker" 
						readonly="readonly" 
						data-validation="required"
						@if(isset($requests) && $requests->computer->first()->entry_date != "") value= {{ Carbon\Carbon::createFromFormat("Y-m-d", $requests->computer->first()->entry_date)->format("d-m-Y") }} @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		
		@component("components.labels.title-divisor") ASIGNACIÓN DE EQUIPO <span class="help-btn" id="help-btn-assign"></span> @endcomponent
		<div class="content-start items-start flex flex-wrap justify-center py-5 w-full container-devices my-6">
			@component("components.buttons.button-device",["label"=>"Smartphone", "icon"=>"<span class='icon-phone text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
					resetCheck
				@endslot
				@slot("attributeEx")
					name="device"
					request-validate
					value="1"
					@if(isset($requests) && $requests->computer->first()->device==1) checked @elseif(!isset($requests)) checked @endif 
					hidden
					id="smartphone"
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label"=>"Tablet", "icon"=>"<span class='icon-tablet text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
					resetCheck
				@endslot
				@slot("attributeEx")
					name="device"
					request-validate
					value="2"
					@if(isset($requests) && $requests->computer->first()->device==2) checked @endif 
					hidden
					id="tablet"
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label"=>"Laptop", "icon"=>"<span class='icon-laptop text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
					resetCheck
				@endslot
				@slot("attributeEx")
					name="device"
					request-validate
					value="3"
					@if(isset($requests) && $requests->computer->first()->device==3) checked @endif 
					hidden
					id="laptop"
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label"=>"Computadora", "icon"=>"<span class='icon-pc text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
					resetCheck
				@endslot
				@slot("attributeEx")
					name="device"
					request-validate
					value="4"
					@if(isset($requests) && $requests->computer->first()->device==4) checked @endif 
					hidden
					id="computer"
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
		</div>
		@component("components.labels.title-divisor") ALTA / CONFIGURACIÓN DE CUENTA <span class="help-btn" id="help-btn-accounts"></span> @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2 container_email">
				@component("components.labels.label") Cuenta de correo: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")						
						remove
						email_account
					@endslot
					@slot("attributeEx")
						placeholder="Ingrese una cuenta de correo"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 container_alias">
				@component("components.labels.label") Alias: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						alias_account
					@endslot
					@slot("attributeEx")
						placeholder="Ingrese un alias"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant"=>"warning"])
					@slot("classEx")
						add-account
					@endslot
					@slot("attributeEx")
						type="button"
					@endslot	
					<span class="icon-plus"></span>							
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
		<div class="block overflow-auto w-full text-center">
			@php
				$heads = ["Cuenta","Alias","Acción"];
				$modelBody = [];
				if(isset($requests))
				{
					foreach($requests->computer->first()->computerAccounts as $account)
					{
						$body =
						[
							"classEx"=>"tr",
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"label" 		=> htmlentities($account->email_account),
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"$account->idcomputerEmailsAccounts\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"email_account[]\" value=\"".htmlentities($account->email_account)."\"",
									],
								],
							],
							[
								"content" =>
								[
									[
										"kind" 			=> "components.labels.label",
										"label"			=> htmlentities($account->alias_account),
									],
									[
										"kind"			=>"components.inputs.input-text",
										"attributeEx"	=>"type=\"hidden\" name=\"alias_account[]\" value=\"".htmlentities($account->alias_account)."\"",
									],
								],
							],
							[
								"content" =>
								[
									[
										"label"   => "<span class='icon-x'></span>",
										"kind"    => "components.buttons.button",
										"variant" => "red",
										"classEx" => "delete-item",
									],
								],
							],
						];
						$modelBody [] = $body; 
					}
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead"=>$heads,
				"modelBody"=>$modelBody,
				"variant"=>"default"
			])
				@slot("classEx")
					bg-transparent
					mb-4
					max-w-full
					w-full
				@endslot
				@slot("attributeExBody")
					id="body-accounts"
				@endslot
			@endcomponent
		</div>
		@php
			if(isset($requests))
			{
				$id_software = $requests->computer->first()->software->map(function($software)
				{
					return $software->idsoftware;
				});
				$other_software = $requests->computer->first()->other_software;
				$request_device = $requests->computer->first()->device;
			}
			else 
			{
				$id_software = null;
				$other_software = null;
				$request_device = null;
			}
		@endphp
		@component("components.labels.title-divisor")    LICENCIA Y APLICACIONES <span class="help-btn" id="help-btn-licences"></span> @endcomponent
		<div class="content-center items-center flex flex-wrap justify-center w-full pt-5 pb-5">
			<div class="self-auto border-2 border-gray-400 box-border m-4 max-w-none pr-5 pl-2.5 w-full modules">
				<ul class="pl-4">
					<li class="pl-16 pt-6 software-list">
						@if(isset($requests) && isset($requests->computer->first()->device))
							@php
								$kind=1;
							@endphp
							@if($requests->computer->first()->device==1 || $requests->computer->first()->device==2)
								@php
									$kind=0;
								@endphp
							@endif
							<ul>
								@foreach(App\Software::where("kind",$kind)->get() as $software)
									<li>
										@php
											$flag = false;
											foreach ($requests->computer->first()->software as $softwareU)
											{
												if($softwareU->idsoftware==$software->idsoftware)
												{
													$flag = true;
												}
											}
										@endphp
										@component("components.inputs.switch")
											@slot("attributeEx")
												type="checkbox"
												name="software_check[]"
												id="software_{{$software->idsoftware}}"
												@if($flag) checked @elseif(!isset($requests->computer->first()->software) && $software->required) checked @endif
												value="{{$software->idsoftware}}"
												forvalue="software_{{$software->idsoftware}}"
											@endslot
											@slot('classEx')
												software_id
											@endslot
											@slot('slot')
												{{$software->name}}
											@endslot
										@endcomponent
									</li>
								@endforeach
							</ul>
						@endif
					</li>
				</ul>
				<ul>
					<li class="block text-center">
						@component("components.labels.label") Otro(s): @endcomponent
						@component("components.inputs.text-area")
							@slot("classEx")
								m-auto
								other_software
							@endslot
							@slot("attributeEx")
								id="other_software"
								name="other_software"
								placeholder="Ingrese las licencias y/o aplicaciones adicionales que no se encuentren en la lista"
								value = "@if(isset($requests)) {{$requests->computer->first()->other_software}} @endif"
							@endslot
						@endcomponent
					</li>
				</ul>
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48 
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
					id="enviar"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button", ["variant"=>"secondary"])
				@slot("classEx")
					save
					text-center
					w-48
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="save"
					id="save"
					formaction="{{ route("computer.unsent") }}"
				@endslot
				GUARDAR SIN ENVIAR
			@endcomponent
			@component("components.buttons.button", ["variant"=>"reset"])
				@slot("classEx")
					btn-delete-form
					text-center
					w-48
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="reset"
					name="borra"
				@endslot
				BORRAR CAMPOS
			@endcomponent
		</div>
	@endcomponent	
@endsection

@section("scripts")
	<link rel="stylesheet" href="{{ asset("css/jquery-ui.css") }}">
	<script src="{{ asset("js/jquery-ui.js") }}"></script>
	<script src="{{ asset("js/datepicker.js") }}"></script>
	<script>
		$(document).ready(function()
		{	
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
				]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 9});
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '.js-projects', 'model': 17, 'option_id': {{$option_id}} });
			id_software = @json($id_software);
			other_software = @json($other_software);
			request_device = @json($request_device);
	
			if(other_software != "")
			{
				$(".other_software").val(other_software);
			}

			if(request_device == null)
			{
				device = $("[name='device']:checked").val();
				softwareDevice(device);
			}
			
			$.validate(
			{
				form: "#container-alta",
				onError   : function($form)
				{
					$(".container_email, .container_alias").parent().find(".form-error").remove();
					swal("", '{{ Lang::get("messages.form_error") }}', "error");
				},
				onSuccess : function($form)
				{
					if($(".request-validate").length>0)
					{
						device = $("input[name='device']:checked").length;
						accounts = $("#body-accounts").find(".tr").length;
						if(device>0 && accounts>0)
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
							if(device == 0)
							{
								swal("", "Por favor seleccione un dispositivo.", "error");
							}
							else if(accounts == 0)
							{
								swal("", "Por favor agregue por lo menos una cuenta.", "error");
							}
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
			})
			$(function() 
			{
				$( "#datepicker, .datepicker2" ).datepicker({ dateFormat: "dd-mm-yy" });
			});
			$(document).on("click","[name='device']",function()
			{
				device = $(this).val();
				softwareDevice(device);
			})
			.on("click","#save",function()
			{
				$(".removeselect").removeAttr("required");
				$(".removeselect").removeAttr("data-validation");
				$(".remove").removeAttr("data-validation");
				$(".request-validate").removeClass("request-validate");
			})
			.on("click",".btn-delete-form",function(e)
			{
				e.preventDefault();
				form = $(this).parents("form");
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: true,
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						swal({
							icon               : '{{ asset(getenv("LOADING_IMG")) }}',
							button             : false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						form[0].reset();
						$("#body").html("");
						$("#body,#body2").html("");
						$(".removeselect").val(null).trigger("change");
						$(".remove").val("");
						$("#slider").slider("values",0,6000);
						$("#slider").slider("values",1,10000);
						$("#date_entry").removeClass('block').addClass('hidden');
						$("[name='software_check[]']").val("");
						$("#no_entry").prop("checked", true);
						$("#smartphone").prop("checked", true);
						$(".software_id").prop("checked",false);
						request_device = null;
						device = $("[name='device']:checked").val();
						$(".tr").remove();
						$(".container_email, .container_alias").parent().find(".form-error").remove();
						softwareDevice(device);
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','.entry',function()
			{
				if ($('input[name="entry"]:checked').val() == '1') 
				{
					$('#date_entry').removeClass('hidden').addClass('block');
				}
				else
				{
					$('#date_entry').removeClass('block').addClass('hidden');
					$('#datepicker').parent().find('.form-error').remove();
					$('#datepicker').removeClass('valid').removeClass('error').removeAttr('style').val('');
				}
			})
			.on('click','.add-account',function()
			{
				$(".container_email, .container_alias").parent().find(".form-error").remove();
				$(".email_account, .alias_account").removeClass('error');
				email = $(".email_account").val().trim();
				alias = $(".alias_account").val().trim();
				if (email!="" && alias!="")
				{
					if ($(".email_account").val().indexOf("@", 0) == -1 || $(".email_account").val().indexOf(".", 0) == -1)
					{
						swal("","El email ingresado es incorrecto.","info");
						$(".email_account").addClass('error');
						$(".container_email").append("<span class='form-error'>Ingrese una cuenta de correo correcta</span>");
					}
					else
					{
						flag = true;
						$("#body-accounts").find(".tr").each(function()
						{
							temails = $(this).find("[name='email_account[]']").val();
							talias  = $(this).find("[name='alias_account[]']").val();
							if(email == temails && alias == talias)
							{
								$(".email_account, .alias_account").addClass('error');
								$(".container_email").append("<span class='form-error'>Ingrese otra cuenta de correo</span>");
								$(".container_alias").append("<span class='form-error'>Ingrese otro alias</span>");
								swal("","Los datos ya se encuentran agregados.","error");
								flag = false;
							}
							else if(email == temails || alias == talias)
							{
								if(email == temails)
								{
									$(".email_account").addClass('error');
									$(".container_email").append("<span class='form-error'>Ingrese otra cuenta de correo</span>");
								}
								else if(alias == talias)
								{
									$(".alias_account").addClass('error');
									$(".container_alias").append("<span class='form-error'>Ingrese otro alias</span>");
								}
								swal("","Los datos ya se encuentran agregados.","error");
								flag = false;
							}
						})
						if(flag)
						{
							@php
								$heads = ["Cuenta","Alias",""];
								$modelBody = [];
								$modelBody =
								[
									[
										"classEx"=>"tr",
										[
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label"   => "",
													"classEx" => "temail_account",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"email_account[]\"",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"idcomputerEmailsAccounts[]\" value=\"x\"",
												],
											],
										],
										[
											"content" =>
											[
												[
													"kind"    => "components.labels.label",
													"label"   => "",
													"classEx" => "talias_account",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"attributeEx"	=> "type=\"hidden\" name=\"alias_account[]\"",
												],
											],
										],
										[
											"content" =>
											[
												[
													"label"   => "<span class =\"icon-x\"></span>",
													"kind"    => "components.buttons.button",
													"variant" => "red",
													"classEx" => "delete-item",
												],
											],
										],

									],
								];
								$table = view("components.tables.alwaysVisibleTable",[
								"modelHead" => $heads,
								"modelBody" => $modelBody,
								"noHead" => true,
								"variant" => "default"
								])->render();
								$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
							row = $(table);
							row = rowColor('#body-accounts',row);
							row.find(".temail_account").text(email);
							row.find(".talias_account").text(alias);
							row.find("[name='email_account[]']").val(email);
							row.find("[name='alias_account[]']").val(alias);
							$("#body-accounts").append(row);
							$(".email_account").val("");
							$(".alias_account").val("");
							$(".email_account").removeClass("error");
							$(".email_account").removeClass("valid");
							$(".alias_account").removeClass("valid");
							$(".alias_account").removeClass("error");
						}
					}
				}
				else
				{
					if(email == "" && alias == "")
					{
						$(".email_account, .alias_account").addClass('error');
						$(".container_email").append("<span class='form-error'>Ingrese una cuenta de correo</span>");
						$(".container_alias").append("<span class='form-error'>Ingrese un alias</span>");
					}
					else if(email == "" || alias == "")
					{
						if(email == "")
						{
							$(".email_account").addClass('error');
							$(".container_email").append("<span class='form-error'>Ingrese una cuenta de correo</span>");
						}
						else if(alias == "")
						{
							$(".alias_account").addClass('error');
							$(".container_alias").append("<span class='form-error'>Ingrese un alias</span>");
						}
					}
					swal("","Debe agregar un email y un alias","error");
				}
			})
			.on("click",".delete-item",function()
			{
				$(this).parents('.tr').remove();
			})
			.on("click","#help-btn-assign",function()
			{
				swal("Ayuda","En este apartado debe seleccionar el equipo que esta solicitando.","info");
			})
			.on("click","#help-btn-accounts",function()
			{
				swal("Ayuda","Aqui deberá ingresar las cuentas de correo que serán configuradas en el equipo solicitado, posteriormente deberá dar clic en el botón \"Agregar\".","info");
			})
			.on("click","#help-btn-licences",function()
			{
				swal("Ayuda","Aqui deberá seleccionar las aplicaciones que necesita tener instaladas en el equipo solicitado.","info");
			})
			.on("change",".js-enterprises",function()
			{
				$(".js-accounts").empty();
			})
			.on("change","#software_1",function()
			{
				if(!$(this).is(":checked"))
				{
					$(this).prop("checked",true);
				}
			});
		});

		function softwareDevice(device)
		{
			kind = 1;
			if(device==1 || device==2)
			{
				kind = 0;
			}
			$.ajax(
			{
				type	: "post",
				url		: "{{ route("computer.create.software") }}",
				data	: {"kind": kind},
				success	: function(data)
				{
					$(".software-list").html(data).parents(".modules").show();
					if(device == request_device)
					{
						for(i=$(".software_id").val();i<($(".software_id").val() + $(".software_id").length);i++)
						{
							$.each(id_software,function(id)
							{
								if(i==id_software[id])
								{
									$("#software_"+i).prop("checked",true);
								}

							});
						}
						$(".other_software").val(other_software);
					}
					else
					{
						$(".other_software").val("");
					}
				},
				error: function(data)
				{
					$(".software-list").parents(".modules").hide();
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			}).done(function(data)
			{
				if($('.swal-modal').length > 0)
				{
					swal.close();
				}
			});
		}
	</script>
@endsection