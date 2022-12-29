@extends("layouts.child_module")
@section("data")
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
	@component("components.forms.form", ["attributeEx" => "action=\"".route("computer.follow.update",$request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component("components.labels.title-divisor")    FOLIO: {{ $request->folio }} @endcomponent
		@component('components.labels.subtitle')
			Elaborado por: {{$request->elaborateUser->fullName()}}
		@endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
					@endslot
					@slot("attributeEx")
						name="title"
						placeholder="Ingrese el título"
						data-validation="required"
						@if(isset($request) && $request->status!=2) disabled @endif
						@if(isset($request)) value="{{ $request->computer->first()->title }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Fecha:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
						datepicker2
					@endslot
					@slot("attributeEx")
						name="datetitle"
						placeholder="Ingrese la fecha"
						data-validation="required"
						readonly="readonly"
						@if(isset($request) && $request->status!=2) disabled @endif
						@if(isset($request->computer->first()->datetitle)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->datetitle)->format('d-m-Y') }}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre del solicitante: @endcomponent
				@php
					$options = collect();
					if($request->idRequest != "")
					{
						$options = $options->concat([["value" => $request->idRequest, "selected" => "selected", "description" => $request->requestUser->fullName()]]);
					}
					$attributeEx = "name=\"user_id\" multiple=\"multiple\" id=\"multiple-users\" data-validation=\"required\"".($request->status != 2 ? " disabled" : "");
					$classEx = "js-users removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach(App\Enterprise::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeEnt($option_id)->pluck("enterprise_id"))->get() as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
						if($request->idEnterprise == $enterprise->id)
						{
							$options = $options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => $description]]);
						}
						else
						{
							$options = $options->concat([["value" => $enterprise->id, "description" => $description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises\" data-validation=\"required\"".($request->status != 2 ? " disabled" : "");
					$classEx = "js-enterprises removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Dirección: @endcomponent
				@php
					$options = collect();
					foreach(App\Area::orderName()->where("status","ACTIVE")->get() as $area)
					{
						if($request->idArea == $area->id)
						{
							$options = $options->concat([["value" => $area->id, "selected" => "selected", "description" => $area->name]]);
						}
						else
						{
							$options = $options->concat([["value" => $area->id, "description" => $area->name]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"area_id\" id=\"multiple-areas\" data-validation=\"required\"".($request->status != 2 ? " disabled" : "");
					$classEx = "js-areas removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Departamento: @endcomponent
				@php
					$options = collect();
					foreach(App\Department::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeDep($option_id)->pluck("departament_id"))->get() as $department)
					{
						if($request->idDepartment == $department->id)
						{
							$options = $options->concat([["value" => $department->id, "selected" => "selected", "description" => $department->name]]);
						}
						else
						{
							$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
						}
					}
					$attributeEx = "multiple=\"multiple\" name=\"department_id\" id=\"multiple-departments\" data-validation=\"required\"".($request->status != 2 ? " disabled" : "");
					$classEx = "js-departments removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Clasificación de gastos: @endcomponent
				@php
					$options = collect();
					if ($request->account != "")
					{
						$options = [["value" => $request->account, "selected" => "selected", "description" => $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"]];
					}
					$attributeEx = "multiple=\"multiple\" name=\"account_id\" data-validation=\"required\"".($request->status != 2 ? " disabled" : "" );
					$classEx = "js-accounts removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if($request->idProject != "")
					{
						$options = $options->concat([["value" => $request->idProject, "selected" => "selected", "description" => $request->requestProject->proyectName]]);
					}
					$attributeEx = "data-validation=\"required\" name=\"project_id\" multiple=\"multiple\" id=\"multiple-projects\"".($request->status != 2 ? " disabled" : "");
					$classEx = "js-projects removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ingrese el puesto: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						remove
					@endslot
					@slot("attributeEx")
						placeholder="Ingrese el puesto"
						name="position"
						data-validation="required"
						@if(isset($request) && $request->status != 2) disabled @endif
						@if(isset($request) && $request->computer->first()->position != "") value="{{$request->computer->first()->position}}" @endif
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@component("components.labels.label") Nuevo ingreso @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot("classEx")
							entry
						@endslot
						@slot("attributeEx")
							name="entry"
							value="0"
							id="no_entry"
							@if($request->status != 2) disabled @endif
							@if($request->computer->first()->entry != 1) checked @endif		
						@endslot
						No
						@if($request->status != 2) 
							@slot('classExLabel')
								disabled
							@endslot
						@endif
					@endcomponent
					@component("components.buttons.button-approval")
						@slot("classEx")
							entry
						@endslot
						@slot("attributeEx")
							name="entry"
							value="1"
							id="entry"
							@if($request->status != 2) disabled @endif
							@if($request->computer->first()->entry == 1) checked @endif		
						@endslot
						Si
						@if($request->status != 2) 
							@slot('classExLabel')
								disabled
							@endslot
						@endif
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 @if($request->computer->first()->entry != 1) hidden @endif " id="date_entry">
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
						@if($request->status != 2) disabled @endif
						@if(isset($request->computer->first()->entry_date)) value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $request->computer->first()->entry_date)->format('d-m-Y')}}" @endif
					@endslot
				@endcomponent
			</div>
		@endcomponent

		@component("components.labels.title-divisor")    ASIGNACIÓN DE EQUIPO <span class="help-btn" id="help-btn-assign"></span>@endcomponent
		<div class="content-start items-start flex flex-wrap justify-center pt-5 pb-5 w-full my-6">
			@component("components.buttons.button-device",["label" => "Smartphone", "icon" => "<span class='icon-phone text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
				@endslot
				@slot("attributeEx")
					name=device
					request-validate
					value=1
					@if(isset($request) && $request->status!=2) disabled @endif
					@if(isset($request) && $request->computer->first()->device==1) checked @endif
					hidden
					id=smartphone
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label" => "Tablet", "icon" => "<span class='icon-tablet text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
				@endslot
				@slot("attributeEx")
					name=device
					request-validate
					value=2
					@if(isset($request) && $request->status!=2) disabled @endif
					@if(isset($request) && $request->computer->first()->device==2) checked @endif 
					hidden
					id=tablet
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label" => "Laptop", "icon" => "<span class='icon-laptop text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
				@endslot
				@slot("attributeEx")
					name=device
					request-validate
					value=3
					@if(isset($request) && $request->status!=2) disabled @endif
					@if(isset($request) && $request->computer->first()->device==3) checked @endif 
					hidden
					id=laptop
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
			@component("components.buttons.button-device",["label" => "Computadora", "icon" => "<span class='icon-pc text-6xl text-white'></span>"])
				@slot("classEx")
					device 
					request-validate
				@endslot
				@slot("attributeEx")
					name=device
					request-validate
					value=4
					@if(isset($request) && $request->status!=2) disabled @endif
					@if(isset($request) && $request->computer->first()->device==4) checked @endif 
					hidden
					id=computer
				@endslot
				@slot("classExContainer")
					mx-5
				@endslot
			@endcomponent
		</div>
		@component("components.labels.title-divisor")    ALTA / CONFIGURACIÓN DE CUENTA <span class="help-btn" id="help-btn-accounts"></span> @endcomponent
		@if ($request->status==2)
			@component("components.containers.container-form", ["classEx" => "md:grid-cols-1"])
				<div class="col-span-2 container_email">
					@component("components.labels.label")
						Cuenta de correo:
					@endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							remove
							email_account
						@endslot
						@slot("attributeEx")
							placeholder="Ingrese la cuenta de correo"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2 container_alias">
					@component("components.labels.label")
						Alias:
					@endcomponent
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
						Agregar
					@endcomponent
				</div>
			@endcomponent
			<div id="deleteAccount"></div>
		@endif
		<div class="block overflow-auto w-full text-center">
			@php
				$modelBody = [];
				$modelHead = [];
				
				if ($request->status == 2)
				{
					$modelHead = ["Cuenta","Alias"," "];
				}
				else 
				{
					$modelHead = ["Cuenta","Alias"];
				}
				

				foreach($request->computer->first()->computerAccounts as $account)
				{
					$body =
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind"    	=> "components.labels.label",
									"label" 	=> htmlentities($account->email_account),
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "idcomputerEmailsAccounts",
									"attributeEx" => "type=hidden name=idcomputerEmailsAccounts[] value=\"".htmlentities($account->idcomputerEmailsAccounts)."\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"attributeEx" => "type=hidden name=email_account[] value=\"".htmlentities($account->email_account)."\"",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    	=> "components.labels.label",
									"label" 	=> htmlentities($account->alias_account),
								],
								[
									"kind" => "components.inputs.input-text",
									"attributeEx" => "type=hidden name=alias_account[] value=\"".htmlentities($account->alias_account)."\"",
								],
							],
						],
					];
					
					$request->status != 2 ? $disable = "disabled=\"disabled\"" : $disable = "";

					if ($request->status == 2)
					{
						$body [] =
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
						];
					}
					
					$modelBody [] = $body;
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"variant" => "default"
			])
			@slot("classsEx")
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
			$id_software = $request->computer->first()->software->map(function($software)
			{
				return $software->idsoftware;
			});
		@endphp
		@component("components.labels.title-divisor")    LICENCIA Y APLICACIONES <span class="help-btn" id="help-btn-licences"></span> @endcomponent
		<div class="content-center items-center flex flex-wrap justify-center w-full pt-5 pb-5">
			<div class="self-auto border-2 border-gray-400 box-border m-4 max-w-none pr-5 pl-2.5 w-full modules @if(isset($request) && isset($request->computer->first()->device)) block @else hidden @endif">
				<ul class="pl-4">
					<li class="pl-16 pt-6 software-list">
						@if($request->computer->first()->device!="")
							@php
								$kind=1;
							@endphp
							@if($request->computer->first()->device==1 || $request->computer->first()->device==2)
								@php
									$kind=0;
								@endphp
							@endif
							<ul class="software_checks">
							@foreach(App\Software::where("kind",$kind)->get() as $software)
								<li>
									@php
										$flag = false;
										foreach ($request->computer->first()->software as $softwareU)
										{
											if($softwareU->idsoftware==$software->idsoftware)
											{
												$flag = true;
											}
										}
									@endphp
									@component("components.inputs.switch")
										@slot("classEx")
											software_id
										@endslot
										@slot("attributeEx")
											name = "software_check[]"
											id="software_{{$software->idsoftware}}"
											@if($flag) checked @elseif(!isset($request->computer->first()->software) && $software->required) checked @endif
											@if($request->status != 2) disabled @endif
											value="{{$software->idsoftware}}"
											forvalue="software_{{$software->idsoftware}}"
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
							input-all
							other_software
							@endslot
							@slot("attributeEx")
							id="other_software"
							name="other_software"
							placeholder="Ingrese las licencias y/o aplicaciones adicionales que no se encuentren en la lista"
							@if($request->status != 2) disabled @endif
							value="{{$request->computer->first()->other_software}}"
							@endslot
						@endcomponent
					</li>
				</ul>
			</div>
		</div>
		@if($request->idCheck != "")
			@component("components.labels.title-divisor")    DATOS DE REVISIÓN @endcomponent
			<div class="mb-6">
				@component("components.tables.table-request-detail.container",["variant" => "simple"])
					@php
						$modelTable = [ "Revisó" => $request->reviewedUser->fullname() ];
						if($request->checkComment != "")
						{
							$modelTable["Comentarios"] =  htmlentities($request->checkComment);
						}
						else 
						{
							$modelTable["Comentarios"] =  "Sin Comentarios";
						}
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
					@endcomponent
				@endcomponent
			</div>
		@endif
		@if($request->idAuthorize != "")
			@component("components.labels.title-divisor")    DATOS DE AUTORIZACIÓN @endcomponent
			@component("components.tables.table-request-detail.container",["variant" => "simple"])
				@php
					$modelTable = [ "Autorizó" => $request->authorizedUser->fullname() ];
					if($request->authorizeComment != "")
					{
						$modelTable["Comentarios"] =  htmlentities($request->authorizeComment);
					}
					else 
					{
						$modelTable["Comentarios"] =  "Sin Comentarios";
					}
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
				@endcomponent
			@endcomponent
			@if($request->code != null)
				<div class="text-center mt-10 mb-6">
					<div class="font-bold mb-4">Código:</div>
					<div class="flex flex-col items-center">
						<div class="w-2/3 md:w-1/4 font-bold text-3xl border-2 border-warm-gray-400 py-2.5">{{ $request->code  }}</div>
					</div>
					<div class="mt-6">Este código es necesario para que le entreguen su equipo.</div>
				</div>
			@endif
		@endif
		
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@if($request->status == "2")
				@component("components.buttons.button", ["variant"=>"primary"])
					@slot("classEx")
						text-center
						w-48
						md:w-auto
					@endslot
					@slot("attributeEx")
						type="submit"
						name="enviar"
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
						formaction="{{ route("computer.follow.updateunsent", $request->folio) }}"
					@endslot
					GUARDAR SIN ENVIAR
				@endcomponent
			@endif
			@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
				@slot("classEx")
					text-center
					w-48
					md:w-auto
					load-actioner
				@endslot
				@slot("attributeEx")
					type="button"
					@if(isset($option_id))
						href="{{ url(App\Module::find($option_id)->url) }}"
					@else
						href="{{ url(App\Module::find($child_id)->url) }}"
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
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
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 9});
			generalSelect({'selector': '.js-users', 'model': 13});
			generalSelect({'selector': '.js-projects', 'model': 17, 'option_id': {{$option_id}} });
			id_software = @json($id_software);
			other_software = @json($request->computer->first()->other_software);
			request_device = @json($request->computer->first()->device);
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
					$(".container_email, .container_alias").parent().find(".form-error").remove();
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
						form[0].reset();
						$("#body").html("");
						$(".removeselect").val(null).trigger("change");
					}
					else
					{
						swal.close();
					}
				});
			})
			.on("change",".entry",function()
			{
				if ($("input[name='entry']:checked").val() == "1")
				{
					$("#date_entry").removeClass('hidden').addClass('block');
				}
				else
				{
					$("#date_entry").removeClass('block').addClass('hidden');
					$("#datepicker").val("");
					$("#date_entry").find('.form-error').remove();
					$("#date_entry").find('.remove').removeClass('error').removeClass('valid').removeAttr('style');
				}
			})
			.on("click",".add-account",function()
			{
				$(".container_email, .container_alias").parent().find(".form-error").remove();
				email = $(".email_account").val().trim();
				alias = $(".alias_account").val().trim();
				if (email!="" && alias!="")
				{
					if ($(".email_account").val().indexOf("@", 0) == -1 || $(".email_account").val().indexOf(".", 0) == -1)
					{
						swal("","El email ingresado es incorrecto.","info");
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
								$(".container_email").append("<span class='form-error'>Ingrese otra cuenta de correo</span>");
								$(".container_alias").append("<span class='form-error'>Ingrese otro alias</span>");
								swal("","Los datos ya se encuentran agregados.","error");
								flag = false;
							}
							else if(email == temails || alias == talias)
							{
								if(email == temails)
								{
									$(".container_email").append("<span class='form-error'>Ingrese otra cuenta de correo</span>");
								}
								else if(alias == talias)
								{
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
						$(".container_email").append("<span class='form-error'>Ingrese una cuenta de correo</span>");
						$(".container_alias").append("<span class='form-error'>Ingrese un alias</span>");
					}
					else if(email == "" || alias == "")
					{
						if(email == "")
						{
							$(".container_email").append("<span class='form-error'>Ingrese una cuenta de correo</span>");
						}
						else if(alias == "")
						{
							$(".container_alias").append("<span class='form-error'>Ingrese un alias</span>");
						}
					}
					swal("","Debe agregar un email y un alias","error");
				}
			})
			.on("click",".delete-item",function()
			{
				idcomputerEmailsAccounts = $(this).parents('.tr').find(".idcomputerEmailsAccounts").val();
				if (idcomputerEmailsAccounts != undefined)
				{
					del = $("<input type=\"hidden\" name=\"delete[]\" value="+idcomputerEmailsAccounts+">");
					$("#deleteAccount").append(del);
				}
				$(this).parents('.tr').remove();
			})
			.on("click","#help-btn-assign",function()
			{
				swal("Ayuda","En este apartado debe seleccionar el equipo que esta solicitando.","info");
			})
			.on("click","#help-btn-accounts",function()
			{
				swal("Ayuda","Aquí deberá ingresar las cuentas de correo que serán configuradas en el equipo solicitado, posteriormente deberá dar clic en el botón \"Agregar\".","info");
			})
			.on("click","#help-btn-licences",function()
			{
				swal("Ayuda","Aquí deberá seleccionar las aplicaciones que necesita tener instaladas en el equipo solicitado.","info");
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
			})
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
					$('.js-accounts').val(null).trigger('change');
				}
			});
		}
	</script>
@endsection
