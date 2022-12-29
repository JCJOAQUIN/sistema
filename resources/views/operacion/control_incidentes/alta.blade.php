@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") DATOS DEL INCIDENTE @endcomponent
	@if(isset($incident))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('incident-control.update',$incident->id)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
	@else
		@component("components.forms.form", ["attributeEx" => "id=\"add-record\""])
	@endif
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Proyecto: @endcomponent
					@php
						$options = collect();
						if(isset($incident) && $incident->project_id != "")
						{
							$options = $options->concat([["value" => $incident->project_id, "selected" => "selected", "description" => $incident->requestProject->proyectName]]);
						}
						$attributeEx = "name=\"project_id\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-projects removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Código WBS: @endcomponent
					@php
						$options = collect();
						if(isset($incident) && $incident->project_id != '' && $incident->wbs_id != '')
						{
							$options = $options->concat([["value" => $incident->wbs_id, "selected" => "selected", "description" => $incident->wbs->code_wbs]]);
						}
						$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"".(isset($incident) && $incident->project_id != '' && !$incident->requestProject->codeWBS()->exists() ? " disabled=\"disabled\"" : (!isset($incident) ? " disabled=\"disabled\"" : ""));
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Localización de frente de trabajo: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="location_wbs"
							placeholder="Ingrese la localización"
							value="{{ isset($incident) ? $incident->location: '' }}"
							data-validation="required"
						@endslot
						@slot("classEx")
							location_wbs
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Fecha de incidente: @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx") 
							date_incident
							removeselect
							datepicker2
						@endslot
						@slot("attributeEx") 
							name="date_incident"
							placeholder="Ingrese la fecha"
							readonly="readonly"
							value="{{ isset($incident) ? Carbon\Carbon::createFromFormat('Y-m-d', $incident->date_incident)->format('d-m-Y') : '' }}"
							data-validation="required"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Trabajador: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="employee"
							placeholder="Ingrese un nombre"
							value="{{ isset($incident) ? $incident->employee : '' }}" 
							data-validation="required"
						@endslot
						@slot("classEx")
							employee
							removeselect
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Nivel de impacto: @endcomponent
					@php
						$options = collect();
						$options = $options->concat([["value" => "1", "selected" => (isset($incident) && $incident->impact_level == "1" ? "selected" : ""), "description" => "Bajo"]]);
						$options = $options->concat([["value" => "2", "selected" => (isset($incident) && $incident->impact_level == "2" ? "selected" : ""), "description" => "Moderado"]]);
						$options = $options->concat([["value" => "3", "selected" => (isset($incident) && $incident->impact_level == "3" ? "selected" : ""), "description" => "Grave"]]);
						$attributeEx = "name=\"impact_level\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "impact_level removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Estado del incidente: @endcomponent
					@php
						$options = collect();
						$options = $options->concat([["value" => "1", "selected" => (isset($incident) && $incident->status == "1" ? "selected" : ""), "description" => "En proceso"]]);
						$options = $options->concat([["value" => "2", "selected" => (isset($incident) && $incident->status == "2" ? "selected" : ""), "description" => "Finalizado"]]);
						$attributeEx = "name=\"status\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "status removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Descripción del incidente: @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							name="description" 
							id="description" 
							rows="5" 
							cols="20" 
							placeholder="Ingrese una descripción"
							data-validation="required"
						@endslot
						@if(isset($incident)) {{ $incident->description }} @endif
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Causas: @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							name="causes"
							id="causes"
							rows="5"
							cols="20"
							placeholder="Ingrese las causas"
							data-validation="required"
						@endslot
						@if(isset($incident)) {{ $incident->causes }} @endif
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Recomendación: @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							name="recommendation"
							id="recommendation"
							rows="5"
							cols="20"
							placeholder="Ingrese una recomendación"
							data-validation="required"
						@endslot
						@if(isset($incident)) {{ $incident->recommendation }} @endif
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Comunicado: @endcomponent
					@component("components.inputs.text-area")
						@slot("attributeEx")
							name="communique"
							id="communique"
							rows="5"
							cols="20"
							placeholder="Ingrese un comunicado"
							data-validation="required"
						@endslot
						@if(isset($incident)) {{ $incident->communique }} @endif
					@endcomponent
				</div>
			@endcomponent
			@if(isset($incident))
				@component("components.labels.title-divisor") DOCUMENTOS CARGADOS @endcomponent				
				@php
					$modelHead = ['Documento', 'Acción'];
					$modelBody = [];
					foreach($incident->documents as $doc)
					{
						$modelBody [] = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									[
										"kind"          => "components.buttons.button",
										"buttonElement" => "a",
										"variant"       => "secondary",
										"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
										"label"         => "Archivo"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" value=\"".$doc->path."\"",
										"classEx"		=> "docPath",
									],
								],
							],
							[
								"content" =>
								[
									[
										"kind"          => "components.buttons.button",
										"variant"       => "red",
										"attributeEx"   => "type=\"button\"",
										"classEx"		=> "deleteDoc delete-item",
										"label"         => "<span class =\"icon-x\"></span>",
									],
								],
							],
						];
					}
				@endphp
				@component('components.tables.alwaysVisibleTable',[
					"variant" => "hidden",  
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
				@endcomponent
			@endif
			@component('components.containers.container-form')
				<div id="documents_incidents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6">
				</div>
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx') 
							type="button"
							id="add_incident_document"
						@endslot
						<span class="icon-plus"></span>
						<span>Anexar documento</span>
					@endcomponent
				</div>
			@endcomponent
			@if (!isset($incident))
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx') 
							type="submit"
							name="add-incident"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar registro</span>
					@endcomponent
				@endcomponent
			@endif
			@if(!isset($incident))
				@component("components.forms.form", ["attributeEx" => "action=\"".route('incident-control.store')."\" method=\"POST\" id=\"container-alta\""])
				@php
					$modelHead = 
					[
						[
							["value" => "Proyecto"],
							["value" => "WBS"],
							["value" => "Localización"],
							["value" => "Fecha"],
							["value" => "Trabajador"],
							["value" => "Descripción"],
							["value" => "Recomendación"],
							["value" => "Documento(s)"],
							["value" => "Acción"]
						]
					];
					$modelBody = [];
				@endphp
				@component("components.tables.table",[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
					@slot("attributeExBody")
						id="incidents-body"
					@endslot
					@slot("classEx")
						mt-6
					@endslot
				@endcomponent
			@endif
			<div id="deletedDocs" class="hidden"></div>
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
					@endslot
					@if(isset($incident)) ACTUALIZAR @else REGISTRAR INCIDENTES @endif
				@endcomponent
				@if (!isset($incident))
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
					@component("components.buttons.button", ["variant" => "reset", "buttonElement" => "a"])
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
				@endif
			</div>
		@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".impact_level", 
						"placeholder"            => "Seleccione el nivel de impacto",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".status", 
						"placeholder"            => "Seleccione el estatus",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$.validate(
			{
				modules	: 'security',
				form	: '#add-record',
				onError : function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}','error');
				},
				onSuccess : function($form)
				{  
					addRow();
					return false;
				}
			});
			$.validate(
			{
				modules	: 'security',
				form	: '#container-alta',
				onError : function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}','error');
				},
				onSuccess : function($form)
				{  
					swal(
					{
						icon				: '{{ asset(getenv("LOADING_IMG")) }}',
						button             	: false,
						closeOnClickOutside	: false,
						closeOnEsc         	: false
					});
					@if (!isset($incident))
						project_id		= $('[name="project_id"] option:selected').val();
						idwbs			= $('[name="code_wbs"] option:selected').val();
						location_wbs	= $('[name="location_wbs"]').val();
						date			= $('[name="date_incident"]').val();
						employee		= $('.employee').val();
						impact_level	= $('.impact_level option:selected').val();
						status_incident	= $('[name="status"] option:selected').val();
						description		= $('#description').val().trim();
						recommendation	= $('#recommendation').val().trim();
						causes			= $('#causes').val().trim();
						communique		= $('#communique').val().trim();
						if(project_id != undefined || idwbs != undefined || location_wbs != "" || date != "" || employee != "" || impact_level != undefined || status_incident != undefined || description != "" || recommendation != "" || causes != "" || communique != "")
						{
							swal('', 'Tiene información para agregar, por favor verifique sus campos.', 'error');
							return false;
						}
						rows = $('#incidents-body .tr').length;
						if(rows <= 0)
						{
							swal('', 'No cuenta con ningún incidente para registrar, por favor verifique sus datos.', 'error');
							return false;
						}	
					@endif
					flagDocs = false;
					$('.path').each(function(i)
					{
						if ($(this).val() == "") 
						{
							flagDocs = true;
							return false;
						}
					});
					if (flagDocs)
					{
						return swal('','Por favor agregue los documentos faltantes.','info');
						return false;
					}				
				}
			});
			generalSelect({'selector': '.js-projects', 'model': 41, 'option_id':{{$option_id}} });
			generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
			$('[name="date_incident"]').datepicker({  dateFormat: "dd-mm-yy", maxDate: 0 });
			$(document).on('change', '.js-projects',function()
			{
				$('.js-code_wbs').html('');
				idProject = $(this).find('option:selected').val();
				if (idProject != null && idProject != undefined && idProject != "")
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(idProject == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.js-code_wbs').attr("data-validation","required").attr('disabled',false);
							}
							else
							{
								$('.js-code_wbs').attr('disabled',true);
							}			
						}
					});
				}
				else
				{
					$('.js-code_wbs').parent().removeClass('has-error');
					$('.js-code_wbs').parent().find('.form-error').remove();
					$('.js-code_wbs').removeClass('error').removeAttr("data-validation").removeAttr("current-error").removeAttr("style").attr('disabled',true);
				}
			})
			.on('click','.deleteDoc',function()
			{
				documentPath = $(this).parents('.tr').find('.docPath').val();
				$('#deletedDocs').append($('<input type="hidden" name="docPathDeleted[]" value="'+documentPath+'">'))
				$(this).parents('.tr').remove();
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $('#add-record');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario y los datos ingresados en la tabla?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#incidents-body').html('');
						$('#documents_incidents').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','.delete-item', function()
			{
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button             	: false,
					closeOnClickOutside	: false,
					closeOnEsc         	: false
				});
				flagSwal		= false;
				actioner		= $(this);
				realPath		= [];
				actioner.parents('.tr').find('.edit_fine_path').each(function()
				{
					realPath.push($(this).val());
				});
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("incident-control.upload") }}',
					data		: {'realPath':realPath},
					success		: function(r)
					{	
						flagSwal = true;
					},
					error		: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				}).done(function(r)
				{
					if(flagSwal)
					{
						actioner.parents('.tr').remove();
						swal.close();
					}
				});
			})
			.on('click','.edit-item', function()
			{
				project				= $('[name="project_id"] option:selected').text();
				project_id			= $('[name="project_id"] option:selected').val();
				wbs					= $('[name="code_wbs"] option:selected').text();
				idwbs				= $('[name="code_wbs"] option:selected').val();
				location_wbs		= $('[name="location_wbs"]').val();
				date				= $('[name="date_incident"]').val();
				employee			= $('.employee').val();
				impact_level		= $('.impact_level option:selected').val();
				status_incident		= $('[name="status"] option:selected').val();
				description			= $('#description').val().trim();
				recommendation		= $('#recommendation').val().trim();
				causes				= $('#causes').val().trim();
				communique			= $('#communique').val().trim();
				actioner			= $(this);
				if(project_id == undefined && location_wbs == "" && date=="" && employee=="" && impact_level == undefined && status_incident == undefined && description=="" && recommendation=="" && causes=="" && communique=="" && idwbs==undefined)
				{
					countDocs = $(this).parents('.tr').find('.nowrap').length;
					if (countDocs > 0)
					{
						swal({
							title     : "Editar incidente",
							text      : "Al editar el incidente los documentos cargados se perderan.\n¿Desea editarlo?",
							icon      : "warning",
							buttons   : ["Cancelar","OK"],
							dangerMode: true,
						})
						.then((continuar) =>
						{
							if(continuar)
							{
								flagSwal = false;
								realPath = [];
								actioner.parents('.tr').find('.edit_fine_path').each(function()
								{
									realPath.push($(this).val());
								});
								$.ajax(
								{
									type		: 'post',
									url			: '{{ route("incident-control.upload") }}',
									data		: {'realPath':realPath},
									success		: function(r)
									{	
										flagSwal = true;
									},
									error		: function()
									{
										swal('','Sucedió un error, por favor intente de nuevo.','error');
									}
								}).done(function(r)
								{
									if(flagSwal)
									{
										edit(actioner);
										swal.close();
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
						edit(actioner);
					}
				}
				else
				{
					swal('', 'Tiene información para agregar, por favor verifique sus campos.', 'error');
				}
			})
			.on('change','.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName 	= $(this).parent('.docs-p').find('.path');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
				if (filename.val().search(extention) == -1)
				{
					swal("", "@lang('messages.extension_allowed', ['param' => 'jpg, png o pdf' ])", "warning");
					$(this).val('');				
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).parent('.uploader-content').addClass('loading').removeClass('image_success');
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type		: 'post',
						url			: '{{ route("incident-control.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).parent('.uploader-content').removeClass('loading empty').addClass('image_success full');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
						},
						error: function()
						{						
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
						}
					})
				}
			})
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon				: '{{ asset(getenv("LOADING_IMG")) }}',
					button             	: false,
					closeOnClickOutside	: false,
					closeOnEsc         	: false
				});
				flagSwal		= false;
				actioner		= $(this);
				uploadedName	= $(this).parents('.docs-p').find('.path').val();
				realPath		= [];
				realPath.push(uploadedName);
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("incident-control.upload") }}',
					data		: {'realPath':realPath},
					success		: function(r)
					{	
						flagSwal = true;
					},
					error		: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				}).done(function(r)
				{
					if(flagSwal)
					{
						actioner.parents('.docs-p').remove();
						swal.close();
					}
				});
			})
			.on('click','#add_incident_document',function()
			{
				@php
					$newDoc = view('components.documents.upload-files',[					
						"classExContainer" 		=> "empty",
						"attributeExInput" 		=> "name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput" 			=> "pathActioner",
						"attributeExRealPath" 	=> "name=\"incident_path[]\"",
						"classExRealPath" 		=> "path",
						"classExDelete" 		=> "delete-doc",
					])->render();
				@endphp
				newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				containerNewDoc = $(newDoc);
				$("#documents_incidents").append(containerNewDoc);
			});
		});
		function addRow()
		{
			flag = false;
			$('.path').each(function(i)
			{
				path = $(this).val();
				if (path == "") 
				{
					flag = true;
					return false;
				}
			});
			if (flag) 
			{
				return swal('','Por favor agregue los documentos faltantes.','info');
			}
			else
			{
				@php
					$modelHead = 
					[
						[
							["value" => "Proyecto"],
							["value" => "WBS"],
							["value" => "Localización"],
							["value" => "Fecha"],
							["value" => "Trabajador"],
							["value" => "Descripción"],
							["value" => "Recomendación"],
							["value" => "Documento(s)"],
							["value" => "Acción"],
						]
					];
					$modelBody = [];
					$modelBody = 
					[
						[
							"classEx" => "tr",
							[
								"classEx" 	=> "td label-project",
								"content" 	=>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tproyect",
										"attributeEx" 	=> "type=\"hidden\" name=\"project_id[]\"",
									]
								]
							],
							[
								"classEx" 	=> "td label-wbs",
								"content" 	=>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "twbs",
										"attributeEx" 	=> "type=\"hidden\" name=\"code_wbs[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "twbs-text",
										"attributeEx" 	=> "type=\"hidden\"",
									],
								],
							],
							[
								"classEx" => "td label-location",
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tlocationwbs",
										"attributeEx" 	=> "type=\"hidden\" name=\"location_wbs[]\"",
									],
								],
							],
							[
								"classEx" => "td label-date",
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tdate",
										"attributeEx" 	=> "type=\"hidden\" name=\"date_incident[]\"",
									],
								],
							],
							[
								"classEx" => "td label-employee",
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "temployee",
										"attributeEx" 	=> "type=\"hidden\" name=\"employee[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tlevel",
										"attributeEx" 	=> "type=\"hidden\" name=\"impact_level[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tstatus",
										"attributeEx" 	=> "type=\"hidden\" name=\"status[]\"",
									],
								],
							],
							[
								"classEx" => "td label-description",
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tdescr",
										"attributeEx" 	=> "type=\"hidden\" name=\"description[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tcauses",
										"attributeEx" 	=> "type=\"hidden\" name=\"causes[]\"",
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "tcom",
										"attributeEx" 	=> "type=\"hidden\" name=\"communique[]\"",
									],
								],
							],
							[
								"classEx" => "td label-recommendation",
								"content" =>
								[
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "trecom",
										"attributeEx" 	=> "type=\"hidden\" name=\"recommendation[]\"",
									],
								],
							],
							[
								"classEx" => "td allPaths",
								"content" =>
								[
									"label" => "",
								],
							],
							[
								"classEx" => "td",
								"content" =>
								[
									[
										"kind" => "components.buttons.button", 
										"classEx" => "edit-item",
										"attributeEx" => "type=\"button\"",
										"variant" => "success",
										"label" => "<span class=\"icon-pencil\"></span>",
									],
									[
										"kind" => "components.buttons.button", 
										"classEx" => "delete-item",
										"attributeEx" => "type=\"button\"",
										"variant" => "red",
										"label" => "<span class=\"icon-x\"></span>",
									],
								],
							],
						],
					];
					$table = view("components.tables.table",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"themeBody" => "striped", 
						"noHead"	=> "true"
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table 				= '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row 				= $(table);
				project				= $('[name="project_id"] option:selected').text();
				project_id			= $('[name="project_id"] option:selected').val();
				wbs					= $('[name="code_wbs"] option:selected').text();
				idwbs				= $('[name="code_wbs"] option:selected').val();
				location_wbs		= $('[name="location_wbs"]').val().trim();
				date				= $('[name="date_incident"]').val();
				employee			= $('.employee').val().trim();
				impact_level		= $('.impact_level option:selected').val();
				status_incident		= $('[name="status"] option:selected').val();
				description			= $('#description').val().trim();
				recommendation		= $('#recommendation').val().trim();
				causes				= $('#causes').val().trim();
				communique			= $('#communique').val().trim();
				num_incident		= (parseInt($('#incidents-body .tr').length)+1);
				
				$(".docs-p").each(function()
				{
					pathName	= $(this).find('.path').val();
					url 		= '{{ asset("docs/incident-control/") }}/'+pathName;
					@php
						$newButtonDoc = view("components.buttons.button", [
							"buttonElement" => "a",
							"attributeEx"	=> "target=\"_blank\"",
							"variant" 		=> "secondary",
							"label"   		=> "<span class=\"fas fa-file-alt\"></span>",
						])->render();
					@endphp
					newButtonDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newButtonDoc)!!}';

					row.find(".allPaths").append($("<div class='nowrap'></div>")
							.append($(newButtonDoc).attr('href',url).attr('title',pathName))
							.append($('<input type="hidden" name="t_path'+num_incident+'[]" value="'+pathName+'" class="edit_fine_path">'))
							);
				});
				row.find(".label-project").prepend(project);
				row.find(".tproyect").val(project_id);
				row.find(".label-wbs").prepend(wbs);
				row.find(".twbs").val(idwbs);
				row.find(".twbs-text").val(wbs);
				location_wbs = String(location_wbs).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				row.find(".label-location").prepend(location_wbs);
				row.find(".tlocationwbs").val(location_wbs);
				row.find(".label-date").prepend(date);
				row.find(".tdate").val(date);
				employee = String(employee).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				row.find(".label-employee").prepend(employee);
				row.find(".temployee").val(employee);
				row.find(".tlevel").val(impact_level);
				row.find(".tstatus").val(status_incident);
				description = String(description).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				row.find(".label-description").prepend(description);
				row.find(".tdescr").val(description);
				row.find(".tcauses").val(causes);
				row.find(".tcom").val(communique);
				recommendation = String(recommendation).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				row.find(".label-recommendation").prepend(recommendation);
				row.find(".trecom").val(recommendation);
				$('#incidents-body').append(row);
				$('.date_incident,.employee,.location_wbs').removeClass('error').removeClass('valid').val('');
				$('.js-projects').val(null).trigger("change").removeClass('error');
				$('.js-code_wbs').html('');
				$('.impact_level').val(null).trigger("change").removeClass('error');
				$('.status').val(null).trigger("change").removeClass('error');
				$('textarea[id="description"]').removeClass('error').removeClass('valid').val('');
				$('textarea[id="causes"]').removeClass('error').removeClass('valid').val('');
				$('textarea[id="recommendation"]').removeClass('error').removeClass('valid').val('');
				$('textarea[id="communique"]').removeClass('error').removeClass('valid').val('');
				$('#documents_incidents').empty();
				$('.edit-item').attr('disabled', false);
			}		
		}
		function edit(actioner)
		{
			tproyect     = actioner.parents('.tr').find('.tproyect').val();
			twbs      	 = actioner.parents('.tr').find('.twbs').val();
			twbsText	 = actioner.parents('.tr').find('.twbs-text').val();
			tlocationwbs = actioner.parents('.tr').find('.tlocationwbs').val();
			tdate        = actioner.parents('.tr').find('.tdate').val();
			temployee 	 = actioner.parents('.tr').find('.temployee').val();
			tlevel       = actioner.parents('.tr').find('.tlevel').val();
			tstatus      = actioner.parents('.tr').find('.tstatus').val();
			tdescr       = actioner.parents('.tr').find('.tdescr').val();
			tcauses      = actioner.parents('.tr').find('.tcauses').val();
			tcom         = actioner.parents('.tr').find('.tcom').val();
			trecom       = actioner.parents('.tr').find('.trecom').val();
			$('.js-projects').val(tproyect).trigger('change');
			if(twbs != "")
			{
				$('.js-code_wbs').attr('disabled',false);
				$('.js-code_wbs').append('<option value='+twbs+' selected="selected">'+twbsText+'</option>');
			}
			$('.location_wbs').val(tlocationwbs).trigger('change');
			$('.date_incident').val(tdate).trigger('change');
			$('.employee').val(temployee).trigger('change');
			$('.impact_level').val(tlevel).trigger('change');
			$('.status').val(tstatus).trigger('change');
			$('textarea[name="causes"]').val(tcauses);
			$('textarea[name="description"]').val(tdescr);
			$('textarea[name="recommendation"]').val(trecom);
			$('textarea[name="communique"]').val(tcom);
			$('.edit-item').attr('disabled', true);
			actioner.parents('.tr').remove();
		}
	</script>
@endsection