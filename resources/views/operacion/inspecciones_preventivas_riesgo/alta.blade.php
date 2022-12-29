@extends('layouts.child_module')
@section('data')
	@if (isset($preventive))
		@component("components.forms.form", ["attributeEx" => "action=\"".(route('preventive.update', $preventive->id))."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".(route('preventive.store'))."\" method=\"POST\" id=\"container-alta\""])
	@endif
		@component("components.labels.title-divisor") DATOS GENERALES DE LA INSPECCIÓN @endcomponent
		@if(isset($preventive))
			<div class="text-center p-2 checks">
				@component('components.inputs.switch')
					@slot('attributeEx')
						id="edit_data" 
						name="edit_data" 
						value="@if ($preventive->count()>0) {{$preventive->id}} @else x @endif"
					@endslot
					Habilitar edición 
				@endcomponent
			</div>
		@endif
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($preventive) && $preventive->project_id != "")
					{
						$options = $options->concat([["value" => $preventive->project_id, "selected" => "selected", "description" => $preventive->project->proyectName]]);
					}
				@endphp
				@component("components.inputs.select", ["attributeEx" => "name=\"project_id\" multiple=\"multiple\" data-validation=\"required\"".(isset($preventive) ? " disabled" : ""), "classEx" => "js-projects inspection-data revomeselect", "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Código WBS: @endcomponent
				@php
					$options = collect();
					if (isset($preventive) && $preventive->project_id != "" && $preventive->wbs_id != "")
					{
						$options = $options->concat([["value" => $preventive->wbs_id, "selected" => "selected", "description" => $preventive->codeWBS->code_wbs]]);
					}
				@endphp
				@component("components.inputs.select", ["attributeEx" => "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\" disabled", "classEx" => "js-code_wbs removeselect".((isset($preventive) && $preventive->wbs_id != "") ? " inspection-data" : ""), "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Contratista: @endcomponent
				@php
					$options = collect();
					if (isset($preventive) && $preventive->contractor_id != "")
					{
						$options = $options->concat([["value" => $preventive->contractor_id, "selected" => "selected", "description" => $preventive->contractorData->name]]);
					}
				@endphp
				@component("components.inputs.select", ["attributeEx" => "name=\"contractor\" multiple=\"multiple\" data-validation=\"required\"".((isset($preventive)) ? " disabled" : ""), "classEx" => "js-contractor inspection-data removeselect", "options" => $options]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Lugar/Área: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="area"
						placeholder="Ingrese el lugar o área de trabajo"
						data-validation="required"
						value="{{ isset($preventive) ? $preventive->area : '' }}"
						@if(isset($preventive)) disabled @endif
					@endslot
					@slot("classEx")
						area
						inspection-data
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Fecha de inspección: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="date"
						placeholder="Ingrese la fecha"
						readonly="readonly"
						data-validation="required"
						value="{{ isset($preventive) ? (Carbon\Carbon::createFromFormat('Y-m-d', $preventive->date)->format('d-m-Y')) : '' }}"
						@if(isset($preventive)) disabled @endif
					@endslot
					@slot("classEx")
						date
						inspection-data
						datepicker2
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Rubro: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value" => 1, "selected" => ((isset($preventive) && $preventive->heading == "1") ? "selected" : ""), "description" => "Seguridad"]]);
					$options = $options->concat([["value" => 2, "selected" => ((isset($preventive) && $preventive->heading == "2") ? "selected" : ""), "description" => "Ambiental"]]);
					$options = $options->concat([["value" => 3, "selected" => ((isset($preventive) && $preventive->heading == "3") ? "selected" : ""), "description" => "Salud Ocupacional"]]);
				@endphp
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"heading\" data-validation=\"required\"".(isset($preventive) ? " disabled" : ""), 
					"classEx" 		=> "js-heading inspection-data removeselect",
					"options"		=> $options,
				])	
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre de supervisor de SSPA: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="supervisor"
						placeholder="Ingrese el nombre"
						data-validation="required"
						value="{{ isset($preventive) ? $preventive->supervisor_name : '' }}"
						@if(isset($preventive)) disabled @endif
					@endslot
					@slot("classEx")
						supervisor
						inspection-data
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre de responsable de SSPA: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="responsible"
						placeholder="Ingrese el nombre"
						data-validation="required"
						value="{{ isset($preventive) ? $preventive->responsible_name : '' }}"						
						@if(isset($preventive)) disabled @endif
					@endslot
					@slot("classEx")
						responsible
						inspection-data
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Observaciones: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="observation"
						placeholder="Ingrese las observaciones"
						@if(isset($preventive)) disabled @endif
					@endslot
					@slot("classEx")
						observation
						inspection-data
						revomeselect
					@endslot
					@if(isset($preventive)){{$preventive->observation}}@endif
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") DATOS ADICIONALES DE LA INSPECCIÓN @endcomponent
		@component("components.inputs.input-text", ["attributeEx" => "type=\"hidden\" name=\"preven_id\" value=\"x\""]) @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Categoría: @endcomponent
				@php
					$options = collect();
					foreach (App\AuditCategory::orderBy('id','asc')->get() as $category)
					{
						$options = $options->concat([["value" => $category->id, "description" => $category->name]]);
					}
				@endphp
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"category_id\"",
					"classEx"		=> "js-category removeselect",
					"options"		=> $options,
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Subcategoría: @endcomponent
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"subcategory_id\"",
					"classEx"		=> "js-subcategory removeselect",
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Acto/Condición: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value" => "1", "description" => "Acto"]]);
					$options = $options->concat([["value" => "2", "description" => "Condición"]]);
				@endphp
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"act\"",
					"classEx"		=> "js-act removeselect",
					"options"		=> $options,
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Factor de severidad: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value" => "1/3", "description" => "1/3"]]);
					$options = $options->concat([["value" => "1", "description" => "1"]]);
					$options = $options->concat([["value" => "3", "description" => "3"]]);
				@endphp
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"severity\"",
					"classEx"		=> "js-severity removeselect",
					"options"		=> $options,
					])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción de Acto/Condición Insegura: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="condition"
						rows="4"
						placeholder="Ingrese la descripción"
					@endslot
					@slot("classEx")
						condition
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Acciones preventivas y/o correctivas (Opcional): @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="action"
						rows="4"
						placeholder="Ingrese las acciones"
					@endslot
					@slot("classEx")
						action
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Hora: @endcomponent
				<div id="timePair">
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="time"
							step="60"
							placeholder="Ingrese la hora"
							readonly="readonly"
						@endslot
						@slot("classEx")
							time
							revomeselect
						@endslot
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Disciplina (Opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="discipline"
						placeholder="Ingrese la disciplina"
					@endslot
					@slot("classEx")
						discipline
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Observador: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="observer"
						placeholder="Ingrese el nombre del observador"
					@endslot
					@slot("classEx")
						observer
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Responsable (Opcional): @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="responsible2"
						placeholder="Ingrese el nombre del responsable"
					@endslot
					@slot("classEx")
						responsible2
						revomeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estatus: @endcomponent
				@php
					$options = collect();
					$options = $options->concat([["value" => "0", "description" => "Abierto"]]);
					$options = $options->concat([["value" => "1", "description" => "Cerrado"]]);
				@endphp
				@component("components.inputs.select", [
					"attributeEx" 	=> "multiple=\"multiple\" name=\"status\"",
					"classEx"		=> "js-status removeselect",
					"options"		=> $options,
				])
				@endcomponent
			</div>
			<div class="col-span-2">
				<div class="hidden">
					@component("components.labels.label") Fecha de cierre: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="dateend"
							placeholder="Ingrese la fecha"
							readonly="readonly"
						@endslot
						@slot("classEx")
							dateend
							datepicker2
							revomeselect
						@endslot
					@endcomponent
				</div>
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant"=>"warning"])
					@slot("attributeEx")
						type="button"
						name="add"
						id="add"
					@endslot
					@slot("classEx")
						add2
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar inspección</span>
				@endcomponent
			</div>
		@endcomponent
		@php
			$modelHead = ["#","Inspección Preventiva","Acción"];
			$modelBody = [];
			if(isset($preventive))
			{
				foreach ($preventive->detailInspection as $key => $preven)
				{
					$tableComponent = "";
					$modelTable = 
					[
						["Categoría", $preven->category->name." <input type=\"hidden\" class=\"preventiveid\" name=\"id_preventive[]\" value=\"".$preven->id."\" /><input class=\"tcategory\" type=\"hidden\" name=\"tcategory[]\" value=\"".$preven->category_id."\" />"],
						["Subcategoría", (($preven->subcategory != "") ? $preven->subcategory->name : "No hay")." <input class=\"tsubcategory\" type=\"hidden\" name=\"tsubcategory[]\"".(($preven->subcategory != "") ? " value=\"".$preven->subcategory_id."\"" : "")." />"],
						["Acto/Condición", (($preven->act == 1) ? "Acto" : "Condición"). " <input readonly class=\"tact\" type=\"hidden\" name=\"tact[]\" value=\"".$preven->act."\" />"],
						["Factor de severidad", $preven->severity. " <input readonly class=\"tseverity\" type=\"hidden\" name=\"tseverity[]\" value=\"".$preven->severity."\" />"],
						["Hora", (($preven->hour != "") ? (Carbon\Carbon::createFromFormat('H:i:s', $preven->hour)->format('H:i')) : "---")." <input readonly class=\"ttime\" type=\"hidden\" name=\"ttime[]\" value=\"".(($preven->hour != "") ? (Carbon\Carbon::createFromFormat('H:i:s', $preven->hour)->format('H:i')) : $preven->hour)."\" />"],
						["Disciplina", (($preven->discipline != "") ? $preven->discipline : "---")." <input readonly class=\"tdiscipline\" type=\"hidden\" name=\"tdiscipline[]\" value=\"".$preven->discipline."\" />"],
						["Descripción Acto/Condición", $preven->condition." <input readonly class=\"tcondition\" type=\"hidden\" name=\"tcondition[]\" value=\"".$preven->condition."\" />"],
						["Acciones preventivas y/o correctivas", (($preven->action != "") ? $preven->action : "---")." <input readonly class=\"taction\" type=\"hidden\" name=\"taction[]\" value=\"".$preven->action."\" />"],
						["Observador", $preven->observer." <input readonly class=\"tobserver\" type=\"hidden\" name=\"tobserver[]\" value=\"".$preven->observer."\" />"],
						["Responsable", (($preven->responsible != "") ? $preven->responsible : "---")." <input readonly class=\"tresponsible2\" type=\"hidden\" name=\"tresponsible2[]\" value=\"".$preven->responsible."\" />"],
						["Estatus", (($preven->status == 0) ? "Abierto" : "Cerrado")." <input readonly class=\"tstatus\" type=\"hidden\" name=\"tstatus[]\" value=\"".$preven->status."\" />"],
						["Fecha de cierre", (($preven->dateend != "") ? (Carbon\Carbon::createFromFormat('Y-m-d', $preven->dateend)->format('d-m-Y')) : "---")." <input readonly class=\"tdateend\" type=\"hidden\" name=\"tdateend[]\" value=\"".(($preven->dateend != "") ? (Carbon\Carbon::createFromFormat('Y-m-d', $preven->dateend)->format('d-m-Y')) : $preven->dateend)."\" />"],
					];
					foreach ($modelTable as $row)
					{
						$tableComponent .= "<div class=\"grid grid-cols-2\">";
						$tableComponent .= view("components.labels.label", ["classEx" => "font-bold text-left", "label" => $row[0].":"])->render();
						$tableComponent .= view("components.labels.label", ["classEx" => "text-left", "label" => $row[1]])->render();
						$tableComponent .= "</div>";
					}
					$modelBody[] =
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "label-count",
									"label" 	=> ($count = 1 + $key),
								],
							],
						],
						[
							"content" =>
							[
								[
									"label"	=> $tableComponent,
								],
							],
						],
						[
							"content" =>
							[
								[
									"label"   		=> "<span class='icon-pencil'></span>",
									"kind"    		=> "components.buttons.button",
									"variant" 		=> "success",
									"attributeEx" 	=> "data-id-count=\"".$count."\" type=\"button\"",
									"classEx" 		=> "edit-item",
								],
								[
									"label"   		=> "<span class='icon-x'></span>",
									"kind"    		=> "components.buttons.button",
									"variant" 		=> "red",
									"attributeEx" 	=> "type=\"button\"",
									"classEx" 		=> "delete-item",
								],
							],
						],
					];
				}
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",[
			"modelHead" 		=> $modelHead,
			"modelBody" 		=> $modelBody,
			"attributeEx"		=> "id=\"table\"",
			"attributeExBody" 	=> "id=\"body\"",
		])
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					text-center
					w-48
					md:w-auto
					enviar
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
				@endslot
				@if(isset($preventive))
					ACTUALIZAR
				@else
					GUARDAR
				@endif
			@endcomponent
			@if(!isset($preventive))
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
			@else
				@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
					@slot("classEx")
						text-center
						w-48
						md:w-auto
					@endslot
					@slot("attributeEx")
						@if(isset($option_id))
							href="{{ url(App\Module::find($option_id)->url) }}"
						@else
							href="{{ url(App\Module::find($child_id)->url) }}"
						@endif
					@endslot
					REGRESAR
				@endcomponent
			@endif
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form    :   '#container-alta',
				onError :   function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}','error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length > 0)
					{
						inspecciones =  $('#body .tr').length;
						if(inspecciones > 0)
						{
							category_id			= $('.js-category').val();
							category_name		= $('.js-category option:selected').text();
							subcategory_id		= $('.js-subcategory').val();
							subcategory_name	= $('.js-subcategory option:selected').text();
							act_id				= $('.js-act').val();
							act_name			= $('.js-act option:selected').text();
							severity_id			= $('.js-severity').val();
							severity_name		= $('.js-severity option:selected').text();
							time				= $('input[name="time"]').val().trim();
							discipline			= $('input[name="discipline"]').val().trim();
							condition			= $('textarea[name="condition"]').val().trim();
							action				= $('textarea[name="action"]').val().trim();
							observer			= $('input[name="observer"]').val().trim();
							responsible2		= $('input[name="responsible2"]').val().trim();
							status_id			= $('.js-status').val();
							status_name			= $('.js-status option:selected').text();
							dateend				= $('input[name="dateend"]').val();

							if(category_name != "" || subcategory_name != "" || act_name != "" || severity_name != "" || time != ""  || discipline != "" || condition != "" || action != "" || observer != "" || responsible2 != "" || dateend != "" || status_name != "")
							{

								swal('', 'Tiene una inspección preventiva sin agregar a la tabla, por favor verifique sus datos.', 'error');
								return false;
							}
							else
							{

								swal('Cargando',{
									icon: '{{ asset(getenv("LOADING_IMG")) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
								return true;
							}
						}
						else
						{
							swal('','No tiene inspecciones para agregar, por favor veifique sus datos.','error');
							return false;
						}
					}
					else
					{
						swal('Cargando',{
								icon: '{{ asset(getenv("LOADING_IMG")) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
						});
						return true;
					}
				}
			});
			$(function()
			{
				$('.datepicker2').datepicker({ dateFormat: "dd-mm-yy" });
				$('.time').numeric(true);
			});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-category",
						"placeholder"            => "Seleccione una categoría",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-subcategory",
						"placeholder"            => "Seleccione una subcategoría",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-severity",
						"placeholder"            => "Seleccione el factor",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status",
						"placeholder"            => "Seleccione un estatus",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-act, .js-heading",
						"placeholder"            => "Seleccione una opción",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-projects', 'model': 41, 'option_id': {{$option_id}} });
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1 });
			generalSelect({'selector': '.js-contractor', 'model': 50 });
			$(function()
			{
				$('.time').daterangepicker({

					timePicker  :   true,
					singleDatePicker:true,
					timePicker24Hour    :   true,
					autoUpdateInput : false,
					locale  :   {
						format       : 'HH:mm',
						"applyLabel" : "Seleccionar",
						"cancelLabel": "Cancelar",
					}
				})
				.on('show.daterangepicker', function (ev, picker)
				{
					picker.container.find(".calendar-table").remove();
				});
				$('.time').on('apply.daterangepicker', function(ev, picker) {
					$(this).val(picker.startDate.format('HH:mm'));
				});
			});
			$(document).on('change', '.js-status', function()
			{
				$('.dateend').removeClass('error');
			   	if($("option:selected", this).val() == 1)
			   	{
					$('.dateend').parent().removeClass('hidden');
			   	}
				else
				{
					$('input[name="dateend"]').val('');
					$('.dateend').parent().addClass('hidden');
				}
			})
			.on('change', '[name="project_id"]',function()
			{
				idproject = $('[name="project_id"] option:selected').val();
				$('[name="code_wbs"]').val(null).trigger('change').html('');
				$('[name="code_wbs"]').removeClass('error').parent().find('.form-error').remove();
				if (idproject != null && idproject != undefined && idproject != "")
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(idproject == v.id)
						{
							if(v.flagWBS != null)
							{
								$('[name="code_wbs"]').prop('disabled', false);
							}
							else
							{
								$('[name="code_wbs"]').prop('disabled', true);
							}					
						}
					});
				} 
				else
				{
					$('[name="code_wbs"]').prop('disabled', true);
				}
			})
			.on('change', '[name="category_id"]',function()
			{
				id_category	= $('[name="category_id"] option:selected').val();
				$('[name="subcategory_id"]').html('');

				$.ajax(
				{
					type    : 'post',
					url     : '{{ route("preventive.get-subcategory") }}',
					data    : {'id_category':id_category},
					success : function(data)
					{
						if(data != "")
						{
							$.each(data,function(i, d)
							{
								$('[name="subcategory_id"]').append('<option value='+d.id+'>'+d.name+'</option>');
							});
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('[name="subcategory_id"]').val(null).trigger('change');
					}
				});
			})
			.on('click','#add',function()
			{
				$('.js-category,.js-subcategory,.js-act,.js-severity,.condition,.js-status').removeClass('error').parent().find('.form-error').remove();
				$('.time,.observer,.responsable2,.dateend').removeClass('error');
				count            =   $('.tr').length;
				category_id      =   $('.js-category').val();
				category_name    =   $('.js-category option:selected').text();
				subcategory_id   =   $('.js-subcategory').val();
				subcategory_name =   $('.js-subcategory option:selected').text();
				act_id           =   $('.js-act').val();
				act_name         =   $('.js-act option:selected').text();
				severity_id      =   $('.js-severity').val();
				severity_name    =   $('.js-severity option:selected').text();
				time             =   $('input[name="time"]').val().trim();
				discipline       =   $('input[name="discipline"]').val().trim();
				condition        =   $('textarea[name="condition"]').val().trim();
				action           =   $('textarea[name="action"]').val().trim();
				observer         =   $('input[name="observer"]').val().trim();
				responsible2     =   $('input[name="responsible2"]').val().trim();
				status_id        =   $('.js-status').val();
				status_name      =   $('.js-status option:selected').text();
				dateend          =   $('input[name="dateend"]').val();
				date             =   $('input[name="date"]').val();
				if(status_id == 1 && dateend != "" && dateend < date)
				{
					swal('','La fecha de cierre no puede ser menor a la fecha de inspección, por favor verifique sus datos.','error');
					$('.dateend').addClass('error');
					return false;
				}
				if(category_id == "" || subcategory_id =="" || act_id == "" || severity_id == "" || time == "" || condition == "" || observer == "" || status_id == "")
				{
					if(category_id == "")
					{
						$('.js-category').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(subcategory_id=="")
					{
						$('.js-subcategory').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(act_id == "")
					{
						$('.js-act').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(severity_id == "")
					{
						$('.js-severity').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(time == "")
					{
						$('.time').addClass('error');
					}
					if(condition == "")
					{
						$('.condition').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(observer == "")
					{
						$('.observer').addClass('error');
					}
					if(status_id == "")
					{
						$('.js-status').addClass('error').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					swal('', 'Por favor llene todos los marcados.', 'error');
				}
				else
				{
					if(status_id == 1 && dateend == "")
					{
						$('.dateend').addClass('error');
						swal('', 'Por favor llene todos los marcados.', 'error');
						return false;
					}
					if($('input[name="preven_id"]').val() != "x")
					{
						id = $('input[name="preven_id"]').val().trim();
					}
					else
					{
						id = "x";
					}
					count = count+1;
					@php
						$modelHead = ["#","Inspección Preventiva","Acción"];
						$modelBody = [];
						$tableComponent = "";
						$modelTable = 
						[
							["Categoría", "<input type=\"hidden\" class=\"preventiveid\" name=\"id_preventive[]\" value=\"\" /><input class=\"tcategory\" type=\"hidden\" name=\"tcategory[]\" value=\"\" />"],
							["Subcategoría", "<input class=\"tsubcategory\" type=\"hidden\" name=\"tsubcategory[]\" value=\"\" />"],
							["Acto/Condición", "<input readonly class=\"tact\" type=\"hidden\" name=\"tact[]\" value=\"\" />"],
							["Factor de severidad", "<input readonly class=\"tseverity\" type=\"hidden\" name=\"tseverity[]\" value=\"\" />"],
							["Hora", "<input readonly class=\"ttime\" type=\"hidden\" name=\"ttime[]\" value=\"\" />"],
							["Disciplina", "<input readonly class=\"tdiscipline\" type=\"hidden\" name=\"tdiscipline[]\" value=\"\" />"],
							["Descripción Acto/Condición", "<input readonly class=\"tcondition\" type=\"hidden\" name=\"tcondition[]\" value=\"\" />"],
							["Acciones preventivas y/o correctivas", "<input readonly class=\"taction\" type=\"hidden\" name=\"taction[]\" value=\"\" />"],
							["Observador", "<input readonly class=\"tobserver\" type=\"hidden\" name=\"tobserver[]\" value=\"\" />"],
							["Responsable", "<input readonly class=\"tresponsible2\" type=\"hidden\" name=\"tresponsible2[]\" value=\"\" />"],
							["Estatus", "<input readonly class=\"tstatus\" type=\"hidden\" name=\"tstatus[]\" value=\"\" />"],
							["Fecha de cierre", "<input readonly class=\"tdateend\" type=\"hidden\" name=\"tdateend[]\" value=\"\" />"],
						];
						foreach ($modelTable as $row)
						{
							$tableComponent .= "<div class=\"grid grid-cols-2\">";
							$tableComponent .= view("components.labels.label", ["classEx" => "font-bold text-left", "label" => $row[0].":"])->render();
							$tableComponent .= view("components.labels.label", ["classEx" => "text-left", "label" => $row[1]])->render();
							$tableComponent .= "</div>";
						}
						$modelBody[] =
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind"    => "components.labels.label",
										"label"   => "",
										"classEx" => "label-count",
									],
								],
							],
							[
								"content" =>
								[
									[
										"label"	=> $tableComponent,
									],
								],
							],
							[
								"content" =>
								[
									[
										"label"   		=> "<span class=\"icon-pencil\"></span>",
										"kind"    		=> "components.buttons.button",
										"variant" 		=> "success",
										"attributeEx" 	=> "data-id-count=\"\" type=\"button\"",
										"classEx" 		=> "edit-item",
									],
									[
										"label"   		=> "<span class=\"icon-x\"></span>",
										"kind"    		=> "components.buttons.button",
										"variant" 		=> "red",
										"attributeEx" 	=> "type=\"button\"",
										"classEx" 		=> "delete-item",
									],
								],
							],
						];
						$table = view("components.tables.alwaysVisibleTable",[
							"modelHead" 		=> $modelHead,
							"modelBody" 		=> $modelBody,
							"noHead"			=> true,
						])->render();
						$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
					row = $(table);
					row = rowColor('#body', row);
					row.find(".label-count").text(count);
					row.find(".preventiveid").val(id);
					row.find(".tcategory").parent().prepend(category_name);
					row.find(".tcategory").val(category_id);
					row.find(".tsubcategory").parent().prepend(subcategory_name);
					row.find(".tsubcategory").val(subcategory_id);
					row.find(".tact").parent().prepend(act_name);
					row.find(".tact").val(act_id);
					row.find(".tseverity").parent().prepend(severity_name);
					row.find(".tseverity").val(severity_id);
					row.find(".ttime").parent().prepend(time);
					row.find(".ttime").val(time);
					(discipline != "") ? (discipline = String(discipline).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) : (discipline = "---");
					row.find(".tdiscipline").parent().prepend(discipline);
					row.find(".tdiscipline").val(discipline);
					condition = String(condition).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					row.find(".tcondition").parent().prepend(condition);
					row.find(".tcondition").val(condition);
					(action != "") ? (action = String(action).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) : (action = "---");
					row.find(".taction").parent().prepend(action);
					row.find(".taction").val(action);
					observer = String(observer).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					row.find(".tobserver").parent().prepend(observer);
					row.find(".tobserver").val(observer);
					(responsible2 != "") ? (responsible2 = String(responsible2).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) : (action = "---");
					row.find(".tresponsible2").parent().prepend(responsible2);
					row.find(".tresponsible2").val(responsible2);
					row.find(".tstatus").parent().prepend(status_name);
					row.find(".tstatus").val(status_id);
					row.find(".tdateend").parent().prepend((dateend != "") ? dateend : "---");
					row.find(".tdateend").val(dateend);
					row.find(".edit-item").attr('data-id-count', count);
					$('#body').append(row);
					$('.js-category').val("").trigger('change');
					$('.js-subcategory').val("").trigger('change');
					$('.js-act').val("").trigger('change');
					$('.js-severity').val("").trigger('change');
					$('input[name="time"]').val('');
					$('input[name="discipline"]').val('');
					$('textarea[name="condition"]').val('');
					$('textarea[name="action"]').val('');
					$('input[name="observer"]').val('');
					$('input[name="responsible2"]').val('');
					$('.js-status').val("").trigger('change');
					$('input[name="dateend"]').val('');
					$('input[name="preven_id"]').val('x');
					$('.hourselect').val("0").trigger('change');
					$('.minuteselect').val("0").trigger('change');
					swal('', 'Datos agregados exitosamente.', 'success');
				}
			})
			.on('click','.delete-item',function()
			{
				swal(
				{
					title		: "Confirmar",
					text		: "¿Desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) =>
				{
					if(willDelete)
					{
						$('#table').append($('<input type="hidden" name="to_delete[]" value="'+$(this).parents('.tr').find('[name="id_preventive[]"]').val()+'">'));
						$(this).parents('.tr').remove();
						if($('.tr').length > 0)
						{
							$('.label-count').each(function(i,v)
							{
								$(this).text(i+1);
							});
						}
					}
				});
			})
			.on('click','.edit-item',function()
			{
				category_id      =   $('.js-category option:selected').val();
				subcategory_id   =   $('.js-subcategory option:selected').val();
				act_id           =   $('.js-act option:selected').val();
				severity_id      =   $('.js-severity option:selected').val();
				time             =   $('input[name="time"]').val().trim();
				discipline       =   $('input[name="discipline"]').val().trim();
				condition        =   $('textarea[name="condition"]').val().trim();
				action           =   $('textarea[name="action"]').val().trim();
				observer         =   $('input[name="observer"]').val().trim();
				responsible2     =   $('input[name="responsible2"]').val().trim();
				status_id        =   $('.js-status option:selected').val();
				dateend          =   $('input[name="dateend"]').val().trim();

				if(category_id == undefined && subcategory_id == undefined && act_id == undefined && severity_id == undefined && time == "" && condition == "" && observer == "" && status_id == undefined && action == "" && discipline == "" && responsible2 == "")
				{
					tid             = $(this).parents('.tr').find('.preventiveid').val();
					tcategory       = $(this).parents('.tr').find('.tcategory').val();
					tsubcategory    = $(this).parents('.tr').find('.tsubcategory').val();
					tact            = $(this).parents('.tr').find('.tact').val();
					tseverity       = $(this).parents('.tr').find('.tseverity').val();
					ttime           = $(this).parents('.tr').find('.ttime').val();
					tdiscipline     = $(this).parents('.tr').find('.tdiscipline').val();
					tcondition      = $(this).parents('.tr').find('.tcondition').val();
					taction         = $(this).parents('.tr').find('.taction').val();
					tobserver       = $(this).parents('.tr').find('.tobserver').val();
					tresponsible2   = $(this).parents('.tr').find('.tresponsible2').val();
					tstatus         = $(this).parents('.tr').find('.tstatus').val();
					tdateend        = $(this).parents('.tr').find('.tdateend').val();

					swal({
						title       :   "Confirmar",
						text        :   "¿Desea editar la inspección preventiva?",
						icon        :   "warning",
						buttons     :   ["Cancelar","OK"],
						dangerMode  :   true,
					})
					.then((continuar) =>
					{
						if(continuar)
						{
							swal('Cargando',{
								icon: '{{ asset(getenv("LOADING_IMG")) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							$('input[name="preven_id"]').val(tid);
							$('.js-category').val(tcategory).trigger('change');
							$('.js-act').val(tact).trigger('change');
							$('.js-severity').val(tseverity).trigger('change');
							$('input[name="time"]').val(ttime);
							$('input[name="discipline"]').val(tdiscipline);
							$('textarea[name="condition"]').val(tcondition);
							$('textarea[name="action"]').val(taction);
							$('input[name="observer"]').val(tobserver);
							$('input[name="responsible2"]').val(tresponsible2);
							$('.js-status').val(tstatus).trigger('change');
							if($('.js-status').val() == 1)
							{
								$('.dateend').parent().removeClass('hidden');
								$('input[name="dateend"]').val(tdateend);
							}
							$.ajax(
							{
								type    : 'post',
								url     : '{{ route("preventive.get-subcategory") }}',
								data    : {'id_category':tcategory},
								success : function(data)
								{
									if(data != "")
									{
										$('[name="subcategory_id"]').empty();
										$.each(data,function(i, d)
										{
											$('[name="subcategory_id"]').append('<option value='+d.id+'>'+d.name+'</option>');
										});
										$('.js-subcategory').val(tsubcategory).trigger('change');
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
									$('[name="subcategory_id"]').val(null).trigger('change');
								}
							});
							$(this).parents('.tr').remove();
							if($('.tr').length > 0)
							{
								$('.label-count').each(function(i,v)
								{
									$(this).text(i+1);
								});
							}
							setTimeout(() => {
								swal.close();
							}, 1000);
						}
						else
						{
							swal.close();
						}
					});
				}
				else
				{
					swal('','Tiene datos para agregar, por favor verifique sus campos.','error');
				}
			})
			.on('change','#edit_data',function()
			{
				if($(this).is(':checked'))
				{
					swal({
						title		: "Habilitar edición.",
						text		: "¿Desea habilitar la edición de la información general de la inspección?",
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
								text		: "Habilitar",
								value		: true,
								closeModal	: true,
							}
						},
						dangerMode	: true,
					})
					.then((a) => {
						if (a)
						{
							$('.inspection-data').prop('disabled',false);
							$('#edit_data').val("x");
						}
						else
						{
							$('#edit_data').prop('checked',false);
							$('.inspection-data').prop('disabled',true);
						}
					});
				}
				else
				{
					swal({
						title		: "Deshabilitar edición.",
						text		: "Si deshabilita la edición las modificaciones realizadas en DATOS GENERALES DE LA INSPECCIÓN no serán guardadas",
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
								text		: "Deshabilitar",
								value		: true,
								closeModal	: true,
							}
						},
						dangerMode	: true,
					})
					.then((a) => {
						if (a)
						{
							$('.inspection-data').prop('disabled',true);
							$('#edit_data').val("x");
						}
						else
						{
							$('#edit_data').prop('checked',true);
							$('.inspection-data').prop('disabled',false);
						}
					});
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Confirmar",
					text		: "¿Desea borrar los datos del formulario?",
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
						$('.js-projects').val(null).trigger('change');
						$('.removeselect').val(null).trigger('change');
						$('.js-category,.js-subcategory,.js-act,.js-severity,.condition,.js-status').parent().find('.form-error').remove();
						$('.js-contractor,.js-heading,.area,.date,.supervisor,.responsable,.time,.discipline,.observer,.responsable2,.dateend').removeClass('error');
					}
					else
					{
						swal.close();
					}
				});
			});
		});
	</script>
@endsection