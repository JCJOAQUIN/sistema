@extends('layouts.child_module')

@section('data')
	@if(isset($n_c_status))
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('status-nc.update',$n_c_status->id)."\"", "methodEx" => "PUT"])
	@else
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('status-nc.store')."\""])
	@endif
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Datos"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Proyecto:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($n_c_status->project_id) && $n_c_status->project_id !="")
					{
						$ncConformitiesData	=	App\Project::find($n_c_status->project_id);
						$options			=	$options->concat([["value"	=>	$ncConformitiesData->idproyect,	"description"	=>	$ncConformitiesData->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-projects removeselect", "attributeEx" => "name=\"project_id\" id=\"project_id\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "WBS:"]) @endcomponent
				@php
					$options	=	collect();
					if (isset($n_c_status) && isset($n_c_status->wbs_id))
					{
						$wbsData	=	App\CatCodeWBS::find($n_c_status->wbs_id);
						$options	=	$options->concat([["value"	=>	$wbsData->id,	"description"	=>	$wbsData->code_wbs,	"selected"	=>	"selected"]]);
					}
					$disabled	=	isset($n_c_status) && $n_c_status->wbs_id == "" ? "disabled" : "";
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-code_wbs removeselect", "attributeEx" => "name=\"code_wbs\" id=\"wbs_id\" $disabled"]) @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label', ["label" => "Descripción de No Conformidad / Oportunidad de mejora:"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "id=\"description\" name=\"description\" data-validation=\"required\" placeholder=\"Ingrese una descripción\""])
					@if(isset($n_c_status))
						{{$n_c_status->description}}
					@endif
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"date\" name=\"date\" data-validation=\"required\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" value=\"".(isset($n_c_status) ? Carbon\Carbon::createFromFormat('Y-m-d',$n_c_status->date)->format('d-m-Y') : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Localización:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"location\" name=\"location\" data-validation=\"required\" placeholder=\"Ingrese la localización\" value=\"".(isset($n_c_status) ? $n_c_status->location : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Proceso y/o área:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"process_area\" name=\"process_area\" data-validation=\"required\" placeholder=\"Ingrese el proceso/área\" value=\"".(isset($n_c_status) ? $n_c_status->process_area : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "No conformidad/oportunidad de mejora originada por:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"non_conformity_origin\" name=\"non_conformity_origin\" data-validation=\"required\" placeholder=\"Ingrese el correo\" value=\"".(isset($n_c_status) ? $n_c_status->non_conformity_origin : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo de Acción:"]) @endcomponent
				@php
					$options		=	collect();
					$typeActionData	=	['1'=>'No conformidad','2'=>'Acción correctiva','3'=>'Oportunidad de mejora'];
					foreach ($typeActionData as $key => $status)
					{
						if (isset($n_c_status) && $n_c_status->type_of_action == $key)
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "removeselect form-control", "attributeEx" => "id=\"type_of_action\" name=\"type_of_action\" data-validation=\"required\" multiple=\"multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Acción:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"action\" name=\"action\" placeholder=\"Ingrese la acción\" value=\"".(isset($n_c_status) ? $n_c_status->action : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Emitida por:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"emited_by\" name=\"emited_by\" data-validation=\"required\" placeholder=\"Ingrese el emisor\" value=\"".(isset($n_c_status) ? $n_c_status->emited_by : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Estatus:"]) @endcomponent
				@php
					$options	=	collect();
					$statusData	=	['1'=>'Activo','2'=>'En proceso','3'=>'Finalizado'];
					foreach ($statusData as $key => $status)
					{
						if (isset($n_c_status) && $n_c_status->status == $key)
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$key,	"description"	=>	$status]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "removeselect form-control", "attributeEx" => "id=\"status\" name=\"status\" data-validation=\"required\" multiple=\"multiple\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Número de reporte de NC:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"nc_report_number\" name=\"nc_report_number\" data-validation=\"required\" placeholder=\"Ingrese el número de reporte\" value=\"".(isset($n_c_status) ? $n_c_status->nc_report_number : '')."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha de cierre de reporte de NC:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" id=\"close_date\" name=\"close_date\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" value=\"".(isset($n_c_status) && $n_c_status->close_date!=null ? Carbon\Carbon::createFromFormat('Y-m-d',$n_c_status->close_date)->format('d-m-Y') : null)."\""]) @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label', ["label" => "Observaciones:"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "id=\"observations\" name=\"observations\" data-validation=\"required\" placeholder=\"Ingrese las observaciones\""]) @if(isset($n_c_status)){{$n_c_status->observations}}@endif @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Documentos"]) @endcomponent
		@if (isset($n_c_status))
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=	["Documento","Acción"];
				foreach($n_c_status->documents as $doc)
				{
					$body	=
					[
						[
							"content"	=>
							[
								["kind"	=>	"components.buttons.button",	"variant"	=>	"secondary",	"attributeEx"	=>	"target=\"_blank\" href=\"".(url('/docs/status-nc/'.$doc->path))."\"",	"label"	=>	"Archivo",	"buttonElement"	=>	"a"],
								["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" value=\"".$doc->path."\"",	"classEx"	=>	"docPath"],
							]
						],
						[
							"content"	=>	[["kind"	=>	"components.buttons.button",	"variant"	=>	"red",	"attributeEx"	=>	"type=\"button\"",	"label"	=>	"<span class=\"icon-x delete-span\"></span>",	"classEx"	=>	"delete-item"]],
						],
					];
					$modelBody[]	=	$body;
				}
			@endphp
			@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "variant" => "default"])@endcomponent
			<div id="deletedDocs" class="hidden"> </div>
		@endif
		@component('components.containers.container-form')
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden documents"> </div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "id=\"add_document\" type=\"button\"", "label" => "<span class=\"icon-plus\"></span> Anexar documento"]) @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" id=\"send\"", "label" => isset($n_c_status) ? "ACTUALIZAR DATOS" : "GUARDAR DATOS"]) @endcomponent
			@if (!isset($n_c_status))
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\" value=\"BORRAR CAMPOS\"", "classEx" => "btn-delete-form", "label" => "BORRAR CAMPOS"]) @endcomponent
			@else
				@php
					$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
				@endphp
				@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					close_date	= $('#close_date').val();
					date		= $('#date').val();
					startDate	= moment(date).format('YYYY-MM-DD');
					endDate		= moment(close_date).format('YYYY-MM-DD');
					diff		= moment(endDate).diff(startDate, 'days');
					if(diff < 0)
					{
						swal('','La fecha de cierre no puede ser menor a la fecha del reporte','error');
						return false;
					}
					else
					{
						docs = $('.documents').find('.empty').length;
						if(docs > 0)
						{
							swal('', 'Tiene documentos pendientes por agregar', 'error');
							return false;
						}
						else
						{
							swal("Cargando",{
								icon				: '{{ asset(getenv('LOADING_IMG')) }}',
								button				: false,
								closeOnClickOutside	: false,
								closeOnEsc			: false
							});
							return true;
						}
					}
				}
			});
		}
		$(document).ready(function()
		{
			validate()
			generalSelect({'selector': '.js-projects', 'option_id': {{$option_id}}, 'model': 41});
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 22});

			$('#date,#close_date').datepicker({ dateFormat: "dd-mm-yy",changeMonth: true, changeYear: true, yearRange: '-100:+0' });
			@php
				$selects = collect([
					[
						"identificator"				=> "#type_of_action",
						"placeholder"				=> "Seleccione el tipo de acción",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> "#status",
						"placeholder"				=> "Seleccione el estatus",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(document).on('click','.delete-item',function()
			{
				documentPath	=	$(this).parents('.tr').find('.docPath').val();
				$('#deletedDocs').append($('<input name="docPathDeleted[]" value="'+documentPath+'">'))
				$(this).parents('.tr').remove();
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
				.then((clean) =>
				{
					if(clean)
					{
						form[0].reset();
						$('#project_id,#wbs_id,#type_of_action,#status').val(null).trigger('change');
						$('#description,#date,#location,#process_area,#non_conformity_origin,#action,#emited_by,#nc_report_number,#close_date,#observations').removeClass('error');
						$('.js-projects, .js-code_wbs').parent().find('.form-error').remove();
						$('#wbs_id').empty('');
						if($('#project_id').parent('div').find(".help-block").length > 0)
						{
							$('#project_id').parent('div').find(".help-block").remove();
						}
						if($('#wbs_id').parent('div').find(".help-block").length > 0)
						{
							$('#wbs_id').parent('div').find(".help-block").remove();
						}
						if($('#type_of_action').parent('div').find(".help-block").length > 0)
						{
							$('#type_of_action').parent('div').find(".help-block").remove();
						}
						if($('#status').parent('div').find(".help-block").length > 0)
						{
							$('#status').parent('div').find(".help-block").remove();
						}
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change', '.js-projects',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null)
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.js-code_wbs').removeAttr('disabled');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
							}
							else
							{
								$('.js-code_wbs').attr('disabled',true).html('');
								$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
							}
						}
					});
				} 
				else
				{
					$('.js-code_wbs').attr('disabled',true).html('');
					$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
				}
			})
			.on('change','.pathActioner',function(e)
			{
				target			=	e.currentTarget;
				filename		=	$(this);
				uploadedName 	=	$(this).parent('.uploader-content').siblings('.path');
				extention		=	/\.jpg|\.png|\.jpeg|\.pdf/i;
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData	= new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("status-nc.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(target).parent('.uploader-content').siblings('.path').val(r.path);
								$(target).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(target).val('');
								$(target).parent('.uploader-content').siblings('.path').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(target).val('');
							$(target).parent('.uploader-content').siblings('.path').val('');
						}
					})
				}
			})
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				actioner		= $(this);
				uploadedName	= $(this).parents('.docs-p').children('.path');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("status-nc.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('div.form-group').remove();
			})
			.on('click','#add_document',function()
			{
				if($('.documents').hasClass('hidden'))
				{
					$('.documents').removeClass('hidden');
				}
				$('.uploader-content').addClass('empty');
				@php
					$newDoc = html_entity_decode((String)view('components.documents.upload-files',[
						"attributeExInput"		=>	"name=\"path\" accept=\".pdf,.jpg,.png,.jpeg\"",
						"classExInput"			=>	"pathActioner",
						"classExRealPath"		=>	"path",
						"attributeExRealPath"	=>	"name=\"real_path[]\"",
						"classExDelete"			=>	"delete-doc",
					]))
				@endphp
				newDoc	=	'{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				$('.documents').append(newDoc);
			});
		});
	</script>
@endsection