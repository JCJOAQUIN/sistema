@extends('layouts.child_module')
@section('data')
	@if(isset($activity))
		@component("components.forms.form",["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('activitiesprogramation.update', $activity->id)."\" id=\"container-alta\""])
		@component('components.inputs.input-text', ["classEx" => "activityExist", "attributeEx" => "type=\"hidden\" value=\"".$activity->id."\""]) @endcomponent
	@else
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route('activitiesprogramation.store')."\" id=\"container-alta\""])
	@endif
		@component("components.labels.title-divisor") PROGRAMACIÓN DE ACTIVIDADES @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Proyecto:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($activity) && $activity->project_id !="")
					{
						$options = $options->concat([["value"	=>	$activity->project->idproyect,	"description"	=>	$activity->project->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-project removeselect"])
					@slot('attributeEx')
						name="project_id" multiple="multiple"
						@isset($activity) data-validation="required" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Código WBS:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($activity) && $activity->wbs_id != "")
					{
						$options	=	$options->concat([["value"	=>	$activity->codeWBS->id,	"description"	=>	$activity->codeWBS->code_wbs,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-code_wbs removeselect"])
					@slot('attributeEx')
						name="code_wbs" multiple="multiple" style="width: 98%;"
						@if(isset($activity) && $activity->wbs_id == "") disabled="disabled" @endif
						@isset($activity) data-validation="required" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Folio permiso de trabajo:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="folio" placeholder="Ingrese el número de folio" value="{{ isset($activity) ? $activity->folio : '' }}"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@slot('classEx')
						folio
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Contratista:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="contractor" placeholder="Ingrese el contratista" value="{{ isset($activity) ? $activity->contractor : '' }}"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@slot('classEx')
						contractor
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Especialidad:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="specialty" placeholder="Ingrese la especialidad" value="{{ isset($activity) ? $activity->specialty : '' }}"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@slot('classEx')
						specialty
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha de inicio:"]) @endcomponent
				@php
					$startDate	=	isset($activity) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$activity->start_date." ".$activity->start_hour)->format('d-m-Y H:i') : "";
					$endDate	=	isset($activity) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$activity->end_date." ".$activity->end_hour)->format('d-m-Y H:i') : '';
					$inputs= [
						[
							"input_classEx" => "input-text-date datepicker with-time",
							"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$startDate."\"",
						],
						[
							"input_classEx" => "input-text-date datepicker with-time",
							"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$endDate."\"",
						]
					];
				$required	=	isset($activity) ? "required" : "";
				@endphp
				@component("components.inputs.range-input",["inputs" => $inputs, "attributeEx" => "data-validation=\"".$required."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Área/Ubicación:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="area" placeholder="Ingrese el área/ubicación" value="{{ isset($activity) ? $activity->area : '' }}"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@slot('classEx')
						area
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "No. de personal:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="number" placeholder="Ingrese el número de personal" value="{{ isset($activity) ? $activity->personal_number : '' }}"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@slot('classEx')
						number
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Recursos:"]) @endcomponent
				@php
					$options	=	collect();
					$resourcesData	=
					[
						"A"		=>	"A.-Andamio",
						"GT"	=>	"GT.-Grua o Titan",
						"CV"	=>	"CV.-Camión de Volteo",
						"MS"	=>	"MS.-Maquina de Soldar",
						"BA"	=>	"BA.-Bomba de Achique",
						"GN"	=>	"GN.- Generador",
						"CR"	=>	"CR.-Camioneta de Redilas",
						"RT"	=>	"RT.-Retroexcavadora",
						"COA"	=>	"COA.- Cilindtros de Oxiacetileno",
						"CA"	=>	"CA.- Cilindros de Argon",
						"O"		=>	"O.-Otro especificar",
						"NA"	=>	"NA.- No Aplica",
					];
					foreach ($resourcesData as $key => $resources)
					{
						if (isset($activity) && $activity->hasResource($key)->count()>0)
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$resources,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$resources]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-resource removeselect", "attributeEx" => "name=\"resource[]\" multiple=\"multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Estatus:"]) @endcomponent
				@php
					$options	=	collect();
					$statusData	=
					[
						"I"		=>	"I.-Inicio",
						"C"		=>	"C.-Continua",
						"NI"	=>	"NI.-No Inició",
						"T"		=>	"T.-Terminó",
					];
					foreach ($statusData as $key => $status)
					{
						if (isset($activity) && $activity->status_code == $key)
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-status removeselect"])
					@slot('attributeEx')
						name="status" multiple="multiple"
						@isset($activity) data-validation="required" @endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Causas de Incumplimiento: "]) @endcomponent
				@php
					$options	=	collect();
					$causesData	=
					[
						"CAEX"	=>	"CAEX.- Causas Externas",
						"FADS"	=>	"FADS.- Falta Análisis de Seguridad",
						"MAF"	=>	"MAF.- Falta de Mecanismos Auxiliares de Fabricación",
						"FDMA"	=>	"FDMA.- Falta de Material",
						"FDPE"	=>	"FDPE.- Falta de Personal",
						"TPSC"	=>	"TPSC.- Trabajos Previos Sin Concluir",
						"ENPL"	=>	"ENPL.- Error En Planeación",
						"TPUR"	=>	"TPUR.- Trabajos por Urgencias",
						"PE"	=>	"PE.- Por Emergencia",
					];
					foreach ($causesData as $key => $causes)
					{
						if (isset($activity) && $activity->hasCause($key)->count()>0)
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$causes,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$causes]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-causes removeselect"])
					@slot('attributeEx')
						name="causes_non_compliance[]" multiple="multiple"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Descripción de las actividades en el proyecto (Área):"]) @endcomponent
				@component('components.inputs.text-area', ["classEx" => "descr"])
					@slot('attributeEx')
						type="text" name="description" placeholder="Ingrese una descripción"
						@isset($activity) data-validation="required" @endisset
					@endslot
					@if(isset($activity)){{$activity->description}}@endif
				@endcomponent
			</div>
			@if (!isset($activity))
				<div class="md:col-span-4 col-span-2 md:text-left text-center">
					@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"add\" id=\"add\"", "classEx" => "add2", "label" => "<span class=\"icon-plus\"></span> Agregar"]) @endcomponent
				</div>
			@endif
		@endcomponent
		@if(!isset($activity))
			@php
				$modelBody	= [];
				$modelHead	= [
					[
						["value" => "#", "classEx" => "sticky inset-x-0"],
						["value" => "Proyecto:", "classEx" => "sticky inset-x-0"],
						["value" => "Descripción de las actividades en el proyecto (Área):"],
						["value" => "Código WBS:"],
						["value" => "Folio permiso de trabajo:"],
						["value" => "Contratista:"],
						["value" => "Especialidad:"],
						["value" => "Fecha de Inicio:"],
						["value" => "Hora de Inicio:"],
						["value" => "Fecha de Finalización:"],
						["value" => "Hora de Finalización:"],
						["value" => "Área/Ubicación:"],
						["value" => "No. de personal:"],
						["value" => "Recursos:"],
						["value" => "Estatus:"],
						["value" => "Causas de incumplimiento:"],
						["value" => "Acciones"]
					]
				];
			@endphp
			@component('components.tables.table', [
				"modelBody"			=> $modelBody,
				"modelHead"			=> $modelHead,
				"attributeExBody"	=> "id=\"body\"",
				"classExBody"		=> "request-validate"
			])@endcomponent
		@endif
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@php
				if (isset($activity))
				{
					$variant	=	"secondary";
					$href		=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				}
				else
				{
					$variant	=	"primary";
				}
			@endphp
			@component('components.buttons.button', ["variant" => $variant, "attributeEx" => "type=\"submit\" name=\"enviar\"", "classEx" => "enviar", "label" => isset($activity) ? "ACTUALIZAR ACTIVIDAD" : "GUARDAR ACTIVIDAD"]) @endcomponent
			@if (!isset($activity))
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\"", "label" => "BORRAR CAMPOS"]) @endcomponent
			@else
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script type="text/javascript">
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione el estatus",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name=\"causes_non_compliance[]\"],[name=\"resource[]\"]', 
						"placeholder"            => "Seleccione uno o varios",
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
			generalSelect({'selector': '.js-project', 'model': 49, 'option_id': {{$option_id}} });
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-project','model': 22});
			$.validate(
			{
				form    :	'#container-alta',
				modules	:	'security',
				onError :	function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}','error');
				},
				onSuccess : function($form)
				{
					activityExist   = $('.activityExist').val();
					project_id      = $('.js-project').val();
					code_wbs_id     = $('.js-code_wbs').val();
					folio           = $('input[name="folio"]').val().trim();
					contractor      = $('input[name="contractor"]').val().trim();
					specialty       = $('input[name="specialty"]').val().trim();
					start_date      = $('input[name="mindate"]').val();
					end_date        = $('input[name="maxdate"]').val();
					description     = $('textarea[name="description"]').val().trim();
					area            = $('input[name="area"]').val().trim();
					number          = $('input[name="number"]').val().trim();
					resource_id     = $('.js-resource option:selected').length;
					status_id       = $('.js-status').val();
					causes_id       = $('.js-causes option:selected').length;

					if(activityExist != undefined || (project_id == "" && code_wbs_id =="" && folio == "" && contractor == "" && specialty == "" && start_date == "" && end_date == "" && area == "" && number == "" && resource_id == 0 && status_id == "" && causes_id == 0 && description == ""))
					{
						if (activityExist != undefined)
						{
							swal('Cargando',{
								icon: '{{ url(getenv('LOADING_IMG')) }}',
								button: false,
							});
							return true;
						}
						else if($('.request-validate').length>0)
						{
							actividades =  $('#body .tr').length;
							if(actividades>0)
							{
								swal('Cargando',{
									icon: '{{ url(getenv('LOADING_IMG')) }}',
									button: false,
								});
								return true;
							}
							else
							{
								$('#body .tr').addClass('error');
								swal('','Debe programar al menos una actividad','error');
								return false;
							}
						}
						else
						{
							swal('Cargando',{
									icon: '{{ url(getenv('LOADING_IMG')) }}',
									button: false,
							});
							return true;
						}
					}
					else
					{
						swal('','Complete todos los campos y agrege la actividad','error');
						return false;
					}
				}
			});       
			$(document).on('change', '[name="project_id"]',function()
			{
				idproject = $('[name="project_id"] option:selected').val();

				if (idproject != undefined)
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(idproject == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.select_father').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-project','model': 22});
							}
							else
							{
								$('.js-code_wbs').html('');
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs').html('');
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
						form[0].reset();
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						$('.descr, .js-project, .js-code_wbs, .js-resource, .js-status, .js-causes').parent().find('.form-error').remove();
						$('.folio, .contractor, .specialty, .start_date, .end_date, .time, .area, .number').removeClass('error');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','#add',function()
			{
				$('.descr, .js-project, .js-code_wbs, .js-resource, .js-status, .js-causes').parent().find('.form-error').remove();
				$('.folio, .contractor, .specialty, .start_date, .end_date, .time, .area, .number').removeClass('error');
				startRange     = $('#mindate').val();
				endRange      = $('#maxdate').val();
				startDate          = moment(startRange,'DD-MM-YYYY HH:mm');
				sartHour          = moment(startRange,'DD-MM-YYYY HH:mm');
				endDate          = moment(endRange,'DD-MM-YYYY HH:mm');
				endHour          = moment(endRange,'DD-MM-YYYY HH:mm');
				count           =   $('.count').length;
				description     =   $('textarea[name="description"]').val().trim();
				project_id      =   $('.js-project').val();
				project_name    =   $('.js-project option:selected').text();
				code_wbs_id     =   $('.js-code_wbs').val();
				code_wbs_name   =   $('.js-code_wbs option:selected').text();
				folio           =   $('input[name="folio"]').val().trim();
				contractor      =   $('input[name="contractor"]').val().trim();
				specialty       =   $('input[name="specialty"]').val().trim();
				start_date      =   moment(startRange,'DD-MM-YYYY HH:mm').format('DD-MM-YYYY');
				schedule_start  =   moment(startRange,'DD-MM-YYYY HH:mm').format('HH:mm');
				end_date        =   moment(endRange,'DD-MM-YYYY HH:mm').format('DD-MM-YYYY');
				schedule_end    =   moment(endRange,'DD-MM-YYYY HH:mm').format('HH:mm');
				area            =   $('input[name="area"]').val().trim();
				number          =   $('input[name="number"]').val().trim();
				//resource_id     =   $('.js-resource').val();
				//resource_name   =   $('.js-resource option:selected').text();
				status_id       =   $('.js-status').val();
				status_name     =   $('.js-status option:selected').text();
				//causes_id       =   $('.js-causes').val();
				//causes_name     =   $('.js-causes option:selected').text();
				if(start_date > end_date)
				{
					swal('','La fecha inicial no puede ser mayor a la fecha final.','error');
					return false;
				}

				if(schedule_start > schedule_end)
				{
					swal('','La hora inicial no puede ser mayor a la hora de finalización.','error');
					return false;
				}

				if(description == "" || project_id == "" || folio == "" || contractor =="" || specialty =="" || start_date == "" || schedule_start == "" || end_date == "" || schedule_end == "" || area=="" || number == "" || status_id == "" || ($('.js-code_wbs option').length > 0 && code_wbs_id==""))
				{
					if(description == "")
					{
						$('.descr').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(project_id == "")
					{
						$('.js-project').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if($('.js-code_wbs option').length > 0 && code_wbs_id=="")
					{
						$('.js-code_wbs').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if(folio == "")
					{
						$('.folio').addClass('error');
					}
					if(contractor == "")
					{
						$('.contractor').addClass('error');
					}
					if(specialty == "")
					{
						$('.specialty').addClass('error');
					}
					if(start_date == "")
					{
						$('.start_date').addClass('error');
					}
					if(schedule_start == "")
					{
						$('.start').addClass('error');
					}
					if(end_date == "")
					{
						$('.end_date').addClass('error');
					}
					if(schedule_end == "")
					{
						$('.end').addClass('error');
					}
					if(area == "")
					{
						$('.area').addClass('error');
					}
					if(number == "")
					{
						$('.number').addClass('error');
					}
					if(status_id == "")
					{
						$('.js-status').parent().append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}

					swal('', 'Por favor llene todos los campos', 'error');
				}
				else
				{
					count   =   count+1;

					td_cause	= $('<div></div>');
					td_resource	= $('<div></div>');

					if ($('.js-causes option:selected').length > 0) 
					{
						$('.js-causes option:selected').each(function(i,v)
						{
							cause = $(v).val();
							cause_text = $(v).text();
							td_cause.append(cause_text+', ');
							td_cause.append('<input readonly="true" class="input-table tcauses" value="'+cause+'" type="hidden" name="tcauses_'+count+'[]">');
						});
					}

					if ($('.js-resource option:selected').length > 0) 
					{
						$('.js-resource option:selected').each(function(i,v)
						{
							resource = $(v).val();
							resource_text = $(v).text();
							td_resource.append(resource_text+', ');
							td_resource.append('<input readonly="true" class="input-table tresource" value="'+resource+'" type="hidden" name="tresource_'+count+'[]">');
						});
					}

					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "#", "classEx" => "sticky inset-x-0"],
								["value" => "Proyecto:", "classEx" => "sticky inset-x-0"],
								["value" => "Descripción de las actividades en el proyecto (Área):"],
								["value" => "Código WBS:"],
								["value" => "Folio permiso de trabajo:"],
								["value" => "Contratista:"],
								["value" => "Especialidad:"],
								["value" => "Fecha de Inicio:"],
								["value" => "Hora de Inicio:"],
								["value" => "Fecha de Finalización:"],
								["value" => "Hora de Finalización:"],
								["value" => "Área/Ubicación:"],
								["value" => "No. de personal:"],
								["value" => "Recursos:"],
								["value" => "Estatus:"],
								["value" => "Causas de incumplimiento:"],
								["value" => "Acciones:"]
							]
						];
						
						$body = [
							"classEx"	=>	"tr",
							[
								"classEx"	=>	"count sticky inset-x-0",
								"content"	=>
								[
									["label" => ""]
								]
							],
							[
								"classEx"	=>	"tprojectContent sticky inset-x-0",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tproject[]\"",	"classEx"	=>	"tproject"]
								]
							],
							[
								"classEx"	=>	"tdescriptionContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tdescription[]\"",	"classEx"	=>	"tdescrp"]
								]
							],
							[
								"classEx"	=>	"tcode_wbsContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tcode_wbs[]\"",	"classEx"	=>	"tcode_wbs"]
								]
							],
							[
								"classEx"	=>	"tfolioContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tfolio[]\"",	"classEx"	=>	"tfolio"]
								]
							],
							[
								"classEx"	=>	"tcontractorContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tcontractor[]\"",	"classEx"	=>	"tcontractor"]
								]
							],
							[
								"classEx"	=>	"tspecialtyContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tspecialty[]\"",	"classEx"	=>	"tspecialty"]
								]
							],
							[
								"classEx"	=>	"tstart_dateContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tstart_date[]\"",	"classEx"	=>	"tstart_date"]
								]
							],
							[
								"classEx"	=>	"tschedule_startContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tschedule_start[]\"",	"classEx"	=>	"tschedule_start"]
								]
							],
							[
								"classEx"	=>	"tend_dateContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tend_date[]\"",	"classEx"	=>	"tend_date"]
								]
							],
							[
								"classEx"	=>	"tschedule_endContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tschedule_end[]\"",	"classEx"	=>	"tschedule_end"]
								]
							],
							[
								"classEx"	=>	"tareaContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tarea[]\"",	"classEx"	=>	"tarea"]
								]
							],
							[
								"classEx"	=>	"tnumberContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tnumber[]\"",	"classEx"	=>	"tnumber"]
								]
							],
							[
								"classEx"	=>	"resourcesContent",
								"content" =>
								[
									["label"	=>	""]
								]
							],
							[
								"classEx"	=>	"tstatusContent",
								"content" =>
								[
									["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tstatus[]\"",	"classEx"	=>	"tstatus"]
								]
							],
							[
								"classEx"	=>	"causesContent",
								"content" =>
								[
									["label"	=>	""]
								]
							],
							[
								"content" =>
								[
									["kind"		=>	"components.buttons.button",	"variant"	=>	"success",	"attributeEx"	=>	"type=\"button\"",	"classEx"	=>	"edit-item", "label"	=>	"<span class=\"icon-pencil\"></span>"],
									["kind"		=>	"components.buttons.button",	"variant"	=>	"red",	"attributeEx"	=>	"type=\"button\"",	"classEx"	=>	"delete-item", "label"	=>	"<span class=\"icon-x\"></span>"]
								]
							]
						];
						$modelBody[] = $body;
						$table = view('components.tables.table', [
							"modelBody"			=> $modelBody,
							"modelHead"			=> $modelHead,
							"noHead"			=> true,
							"attributeExBody"	=> "id=\"body\"",
							"classExBody" 		=> "request-validate"
						])->render();
					@endphp
					table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr_table		=	$(table);

					tr_table.find('.count').html(count);
					description = String(description).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tdescrp').val(description);
					tr_table.find('.tdescriptionContent').prepend(description);
					tr_table.find('.tproject').val(project_id);
					tr_table.find('.tprojectContent').prepend(project_name);
					tr_table.find('.tcode_wbs').val(code_wbs_id);
					tr_table.find('.tcode_wbsContent').prepend(code_wbs_name);
					folio = String(folio).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tfolio').val(folio);
					tr_table.find('.tfolioContent').prepend(folio);
					contractor = String(contractor).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tcontractor').val(contractor);
					tr_table.find('.tcontractorContent').prepend(contractor);
					specialty = String(specialty).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tspecialty').val(specialty);
					tr_table.find('.tspecialtyContent').prepend(specialty);
					tr_table.find('.tstart_date').val(start_date);
					tr_table.find('.tstart_dateContent').prepend(start_date);
					tr_table.find('.tschedule_start').val(schedule_start);
					tr_table.find('.tschedule_startContent').prepend(schedule_start);
					tr_table.find('.tend_date').val(end_date);
					tr_table.find('.tend_dateContent').prepend(end_date);
					tr_table.find('.tschedule_end').val(schedule_end);
					tr_table.find('.tschedule_endContent').prepend(schedule_end);
					area = String(area).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tarea').val(area);
					tr_table.find('.tareaContent').prepend(area);
					number = String(number).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr_table.find('.tnumber').val(number);
					tr_table.find('.tnumberContent').prepend(number);
					tr_table.find('.resourcesContent').html(td_resource);
					tr_table.find('.tstatus').val(status_id);
					tr_table.find('.tstatusContent').prepend(status_id);
					tr_table.find('.causesContent').html(td_cause);
				
					$('#body').append(tr_table);
					$('textarea[name="description"]').val('');
					$('.js-project').val("").trigger('change');
					$('.js-code_wbs').val("").trigger('change');
					$('input[name="folio"]').val('');
					$('input[name="contractor"]').val('');
					$('input[name="specialty"]').val(''); 
					$('input[name="mindate"]').val('');
					$('input[name="maxdate"]').val('');
					$('input[name="area"]').val('');
					$('input[name="number"]').val('');
					$('.js-resource').val("").trigger('change');
					$('.js-status').val("").trigger('change');
					$('.js-causes').val("").trigger('change');
					swal('', 'Actividad agregada exitosamente.', 'success');
					stickyAdjustment();
				}
			})
			.on('click','.delete-item',function()
			{
				$(this).parents('.tr').remove();
				actividad = $('#body .tr').length;
				if($('.count').length>0)
				{
					$('.count').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})
			.on('click','.edit-item',function()
			{
				data_count_id 	= $(this).attr('data-id-count');
				project_id      = $('.js-project').val();
				code_wbs_id     = $('.js-code_wbs').val();
				folio           = $('input[name="folio"]').val().trim();
				contractor      = $('input[name="contractor"]').val().trim();
				specialty       = $('input[name="specialty"]').val().trim();
				start_date      = $('input[name="mindate"]').val();     
				end_date        = $('input[name="maxdate"]').val();
				description     = $('textarea[name="description"]').val().trim();
				area            = $('input[name="area"]').val().trim();
				number          = $('input[name="number"]').val().trim();
				resource_id     = $('.js-resource option:selected').length;
				status_id       = $('.js-status').val();
				causes_id       = $('.js-causes option:selected').length;

				if(project_id == "" && code_wbs_id =="" && folio == "" 
				&& contractor == "" && specialty == "" && start_date == "" 
				&& end_date == "" && area == "" && number == "" && resource_id == 0 && status_id == ""
				&& causes_id == 0 && description == "")
				{

					tproject        = $(this).parents('.tr').find('.tproject').val();
					tcode_wbs       = $(this).parents('.tr').find('.tcode_wbs').val();
					tfolio          = $(this).parents('.tr').find('.tfolio').val();
					tcontractor     = $(this).parents('.tr').find('.tcontractor').val();
					tspecialty      = $(this).parents('.tr').find('.tspecialty').val();
					tstart_date     = $(this).parents('.tr').find('.tstart_date').val();
					tschedule_start = $(this).parents('.tr').find('.tschedule_start').val();
					tend_date       = $(this).parents('.tr').find('.tend_date').val();
					tschedule_end   = $(this).parents('.tr').find('.tschedule_end').val();
					tarea           = $(this).parents('.tr').find('.tarea').val();
					tnumber         = $(this).parents('.tr').find('.tnumber').val();
					tresource       = $(this).parents('.tr').find('.tresource');
					tstatus         = $(this).parents('.tr').find('.tstatus').val();
					tcauses         = $(this).parents('.tr').find('.tcauses');
					tdescrp         = $(this).parents('.tr').find('.tdescrp').val();

					swal({
						title       :   "Editar Actividad",
						text        :   "¿Desea editar la actividad?",
						icon        :   "warning",
						buttons     :   ["Cancelar","OK"],
						dangerMode  :   true,
					})
					.then((continuar) =>
					{
						if(continuar)
						{
							tresource_values = [];
							$(tresource).each(function(i,v)
							{
								tresource_values.push($(this).val());
							});

							tcauses_values = [];
							$(tcauses).each(function(i,v)
							{
								tcauses_values.push($(this).val());
							});

							$('.js-project').val(tproject).trigger('change');
						
							$('input[name="folio"]').val(tfolio);
							$('input[name="contractor"]').val(tcontractor);
							$('input[name="specialty"]').val(tspecialty);
							$('input[name="mindate"]').val(tstart_date+' '+tschedule_start);
							$('input[name="maxdate"]').val(tend_date+' '+tschedule_end);
							$('input[name="area"]').val(tarea);
							$('input[name="number"]').val(tnumber);
							$('.js-resource').val(tresource_values).trigger('change');
							$('.js-status').val(tstatus).trigger('change');
							$('.js-causes').val(tcauses_values).trigger('change');
							$('textarea[name="description"]').val(tdescrp);

							$.ajax(
							{
								type	: 'get',
								url		: '{{ route('activitiesprogramation.get-wbs')}}',
								data	: {'idproject':tproject},
								success : function(data)
								{
									if (data != "") 
									{
										$('[name="code_wbs"]').empty();
										$.each(data,function(i, d) 
										{
											$('[name="code_wbs"]').append('<option value='+d.id+'>'+d.code_wbs+'</option>').prop('disabled',false);
										});
										$('.js-code_wbs').val(tcode_wbs).trigger('change');
									}
									else
									{
										$('[name="code_wbs"]').prop('disabled', true);
									}
								},
								error : function()
								{
									swal('','Sucedió un error, por favor intente de nuevo.','error');
									$('[name="code_wbs"]').val(null).trigger('change');
								}
							});

						$(this).parents('.tr').remove();
				
							actividad = $('#body div').length;
							
							if($('.count').length>0)
							{
								$('.count').each(function(i,v)
								{
									$(this).html(i+1);
								});
							}
						}
						else
						{
							swal.close();
						}
					});
				}
				else
				{
				swal('','Tiene una actividad sin agregar','error');
				}
			})
		});
	</script>
@endsection