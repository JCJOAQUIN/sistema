@extends('layouts.child_module')
@section('data')
	@if(isset($work_force))
		@component('components.forms.form',["attributeEx" => "id=\"form-container\" method=\"POST\" action=\"".route('work-force.update',$work_force->id)."\"", "methodEx" => "PUT"])
	@else
		@component('components.forms.form',["attributeEx" => "id=\"form-container\" method=\"POST\" action=\"".route('work-force.store')."\""])
	@endif
		@component('components.labels.title-divisor') Datos @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionProject = [];
					if(isset($work_force->project_id))
					{
						$optionProject[] = ['value' => $work_force->project_id, 'description' => $work_force->projectData->proyectName, 'selected' => 'selected'];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionProject])
					@slot('attributeEx')
						id="project_id" multiple="multiple" @isset($work_force) name="project_id" @endisset
					@endslot
					@slot('classEx')
						removeselect js-projects
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') WBS: @endcomponent
				@php
					$optionWBS = [];
					if(isset($work_force->wbs_id))
					{	 
						$optionWBS[] = ['value' => $work_force->wbs_id, 'description' => $work_force->wbsData->code_wbs, 'selected' => 'selected'];
					}
				@endphp
				@component('components.inputs.select',['options' => $optionWBS])
					@slot('attributeEx')
						id="wbs_id"
						multiple="multiple"
						@isset($work_force)
							name="wbs_id"
						@endisset
						@if(isset($work_force) && $work_force->wbs_id == "")
							disabled="disabled"
						@endif
					@endslot
					@slot('classEx')
						removeselect js-code_wbs
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Localización del frente de trabajo: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						id="location_wbs"
						placeholder="Ingrese la localización"
						@isset($work_force)
							name="location_wbs"
							value="{{ $work_force->location }}"
						@endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Contratista/Subcontratista: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						id="provider"
						placeholder="Ingrese el contratista/subcontratista"
						@isset($work_force)
							name="provider"
							value="{{ $work_force->provider }}"
						@endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fuerza de Trabajo: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						id="work_force"
						rows="6"
						placeholder="Ingrese la fuerza de trabajo"
						@isset($work_force)
							name="work_force"
						@endisset
					@endslot
					@isset($work_force)
						{{ $work_force->work_force }}
					@endisset
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Descripción de la actividad: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						id="description"
						rows="6"
						placeholder="Ingrese una descripción"
						@isset($work_force)
							name="description"
						@endisset
					@endslot
					@isset($work_force)
						{{ $work_force->description }}
					@endisset
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						id="date"
						placeholder="Ingrese la fecha"
						readonly="readonly"
						@isset($work_force)
							name="date"
							value="{{Carbon\Carbon::createFromFormat('Y-m-d',$work_force->date)->format('d-m-Y')}}"
						@endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Horas Hombre por Día: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						id="man_hours_per_day"
						readonly="readonly"
						placeholder="Ingrese el total de horas"
						@isset($work_force)
							name="man_hours_per_day"
							value="{{ $work_force->man_hours_per_day }}"
						@endisset
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Total de Trabajadores: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text"
						id="total_workers"
						readonly="readonly"
						placeholder="Ingrese el total"
						@isset($work_force)
							name="total_workers"
							value="{{ $work_force->total_workers }}"
						@endisset
					@endslot
				@endcomponent
			</div>
			@if(!isset($work_force))
				<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" id="work_force_id" value="x"
						@endslot
					@endcomponent
					@component('components.buttons.button',['variant' => 'warning'])
						@slot('attributeEx')
							id="add_register" type="button"
						@endslot
						<span class="icon-plus"></span>
						<span>Agregar</span>
					@endcomponent
				</div>				 
			@endif
		@endcomponent
		@if(!isset($work_force))
			@php
				$modelBody	= [];
				$modelHead	= 
				[
					[
						["value" => "Proyecto"],
						["value" => "WBS"],
						["value" => "Localización"],
						["value" => "Fecha"],
						["value" => "Descripción de Actividad"],
						["value" => "Contratista/Subcontratista"],
						["value" => "Fuerza de Trabajo"],
						["value" => "Total de Trabajadores"],
						["value" => "Horas Hombre por Día"],
						["value" => "Acciones"]
					]
				];
			@endphp
			@component('components.tables.table',[
				"modelBody"			=> $modelBody,
				"modelHead"			=> $modelHead,
				"attributeExBody"	=> "id=\"body_work_force\""
			])
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button',['variant' => 'secondary'])
					@slot('attributeEx')
						type="submit" id="send"
					@endslot
					GUARDAR DATOS
				@endcomponent
				@component('components.buttons.button',['variant' => 'reset'])
					@slot('attributeEx')
						type="button"
					@endslot
					@slot('classEx')
						btn-delete-form
					@endslot
					BORRAR CAMPOS
				@endcomponent
			</div>
		@else
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
				@component('components.buttons.button',['variant' => 'primary'])
					@slot('attributeEx')
						type="submit" id="send"
					@endslot
					ACTUALIZAR DATOS
				@endcomponent
				@component('components.buttons.button',["variant" => "reset", "buttonElement" => "a"])
					@slot('attributeEx')
						@if(isset($option_id)) 
							href="{{ url(getUrlRedirect($option_id)) }}" 
						@else 
							href="{{ url(getUrlRedirect($child_id)) }}" 
						@endif 
					@endslot
					@slot('classEx')
						load-actioner
					@endslot
					REGRESAR
				@endcomponent
			</div>
		@endif
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@if(!isset($work_force))
				validation();
			@endif
			$('#date').datepicker({ minDate: 0, dateFormat: "dd-mm-yy" });
			generalSelect({'selector': '.js-projects', 'model': 41, 'option_id':{{$option_id}} });
			generalSelect({'selector': '#wbs_id', 'depends': '.js-projects', 'model': 22});
			$(document).on('click','.btn-delete-form',function(e)
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
						$('#provider,#work_force,#total_workers,#man_hours_per_day,#description,#location_wbs').removeClass('error').val('');
						$('#wbs_id').val(null).trigger('change');
						$('#project_id').val(null).trigger('change');
						$('#wbs_id').empty('');
						if($('#project_id').parent('div').find(".help-block").length > 0)
						{
							$('#project_id').parent('div').find(".help-block").remove();
						}
						if($('#wbs_id').parent('div').find(".help-block").length > 0)
						{
							$('#wbs_id').parent('div').find(".help-block").remove();
						}
						$('#work_force_id').val('x');
						$('#date').val('');
						$('#body_work_force').html('');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('change','#project_id',function()
			{
				$('#wbs_id').html('');
				project_id = $('#project_id option:selected').val();
				if (project_id != undefined) 
				{
					$.each(generalSelectProject, function(i,v)
					{
						if(project_id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.js-code_wbs').removeAttr('disabled');
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
					$('.js-code_wbs').removeAttr('disabled');
				}
			})
			.on('change','#work_force',function()
			{
				regex			= /(\d+)/g;
				string			= $('#work_force').val();
				numbers			= string.match(regex);
				total_workers	= 0;
				
				if (numbers != null) 
				{
					for (i = numbers.length - 1; i >= 0; i--)
					{
						total_workers += Number(numbers[i]);
					}
				}
				man_hours_per_day = 8 * total_workers;
				$('#total_workers').val(total_workers);
				$('#man_hours_per_day').val(man_hours_per_day);
			})
			.on('click','#add_register',function()
			{
				$('#provider,#work_force,#total_workers,#man_hours_per_day,#description,#date').removeClass('error');
				if($('#project_id').parent('div').find(".help-block").length > 0)
				{
					$('#project_id').parent('div').find(".help-block").remove();
				}
				if($('#wbs_id').parent('div').find(".help-block").length > 0)
				{
					$('#wbs_id').parent('div').find(".help-block").remove();
				}
				project_id			= $('#project_id option:selected').val();
				project_id_text		= $('#project_id option:selected').text();
				wbs_id				= $('#wbs_id option:selected').val();
				wbs_id_text			= $('#wbs_id option:selected').text();
				location_wbs		= $('#location_wbs');
				provider			= $('#provider');
				work_force			= $('#work_force');
				total_workers		= $('#total_workers');
				man_hours_per_day	= $('#man_hours_per_day');
				description 		= $('#description');
				date 				= $('#date');
				work_force_id 		= $('#work_force_id').val();

				if (provider.val().trim() == "" || location_wbs.val().trim() =="" || date.val().trim() == "" || work_force.val().trim() == "" || total_workers.val().trim() == "" || man_hours_per_day.val().trim() == "" || project_id == undefined || description.val().trim() == "" || ($('#wbs_id option').length > 0 && wbs_id == undefined))
				{
					if (provider.val().trim() == "") 
					{
						provider.addClass('error');
					}
					if (location_wbs.val().trim() == "")
					{
						location_wbs.addClass('error');
					}
					if (work_force.val().trim() == "") 
					{
						work_force.addClass('error');
					}
					if (total_workers.val().trim() == "" ) 
					{
						total_workers.addClass('error');
					}
					if (man_hours_per_day.val().trim() == "") 
					{
						man_hours_per_day.addClass('error');
					}
					if (description.val().trim() == "") 
					{
						description.addClass('error');
					}
					if (date.val().trim() == "") 
					{
						date.addClass('error');
					}
					if (project_id == undefined) 
					{
						if($('#project_id').parent('div').find(".help-block").length == 0)
						{
							$('#project_id').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
						}
					}
					if ($('#wbs_id option').length > 0 && wbs_id == undefined) 
					{
						if($('#wbs_id').parent('div').find(".help-block").length == 0)
						{
							$('#wbs_id').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
						}
					}
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
				else if(total_workers.val().trim() == 0 )
				{
					swal('','El total de trabajadores no puede ser cero.','error');
					total_workers.addClass('error');
					man_hours_per_day.addClass('error');
				}
				else
				{
					@php
						$body		= [];
						$modelBody	= [];
						$modelHead	= 
						[
							[
								["value" => "Proyecto"],
								["value" => "WBS"],
								["value" => "Localización"],
								["value" => "Fecha"],
								["value" => "Descripción de Actividad"],
								["value" => "Contratista/Subcontratista"],
								["value" => "Fuerza de Trabajo"],
								["value" => "Total de Trabajadores"],
								["value" => "Horas Hombre por Día"],
								["value" => "Acciones"]
							]
						];
						$body = [ "classEx" => "tr-work",
							[
								"content"	=>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"work_force_id[]\""
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"project_id[]\""
									]
								]
							],
							[
								"content"	=>
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "class-wbs-id" 
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"wbs_id[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"location_wbs[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"date[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"description[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"provider[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"work_force[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"total_workers[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"man_hours_per_day[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "success",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "edit-item",
										"label"			=> "<span class=\"icon-pencil\"></span>"
									],
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "delete-item",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						];
						$modelBody[]	= $body;
						$workTable		= view('components.tables.table',[
							"modelBody" => $modelBody,
							"modelHead"	=> $modelHead, 
							"noHead"	=> "true"
						])->render();
					@endphp
					row	= '{!!preg_replace("/(\r)*(\n)*/", "", $workTable)!!}';
					tr	= $(row);
					tr.find('[name="work_force_id[]"]').parent().prepend(project_id_text);
					tr.find('[name="work_force_id[]"]').val(work_force_id);
					tr.find('[name="project_id[]"]').val(project_id);
					tr.find('.class-wbs-id').text(wbs_id_text);
					tr.find('[name="wbs_id[]"]').parent().prepend(wbs_id_text != "" ? wbs_id_text : '---');
					tr.find('[name="wbs_id[]"]').val(wbs_id);
					location_wbs = String(location_wbs.val()).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="location_wbs[]"]').parent().prepend(location_wbs);
					tr.find('[name="location_wbs[]"]').val(location_wbs);
					tr.find('[name="date[]"]').parent().prepend(date.val());
					tr.find('[name="date[]"]').val(date.val());
					description = String(description.val()).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="description[]"]').parent().prepend(description);
					tr.find('[name="description[]"]').val(description);
					provider = String(provider.val()).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="provider[]"]').parent().prepend(provider);
					tr.find('[name="provider[]"]').val(provider);
					work_force = String(work_force.val()).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="work_force[]"]').parent().prepend(work_force);
					tr.find('[name="work_force[]"]').val(work_force);
					tr.find('[name="total_workers[]"]').parent().prepend(total_workers.val());
					tr.find('[name="total_workers[]"]').val(total_workers.val());
					tr.find('[name="man_hours_per_day[]"]').parent().prepend(man_hours_per_day.val());
					tr.find('[name="man_hours_per_day[]"]').val(man_hours_per_day.val());
					$('#body_work_force').append(tr);
					$('#provider,#location_wbs,#work_force,#total_workers,#man_hours_per_day,#description,#date').removeClass('error').val('');
					$('#wbs_id').val(null).trigger('change');
					$('#project_id').val(null).trigger('change');
					$('#wbs_id').empty('');
					if($('#project_id').parent('div').find(".help-block").length > 0)
					{
						$('#project_id').parent('div').find(".help-block").remove();
					}
					if($('#wbs_id').parent('div').find(".help-block").length > 0)
					{
						$('#wbs_id').parent('div').find(".help-block").remove();
					}
					$('#work_force_id').val('x');
					$('.edit-item').removeAttr('disabled');
					swal('', '{{ Lang::get("messages.record_created") }}', 'success');
				}
			})
			.on('click','.edit-item',function()
			{
				work_force_id		= $(this).parents('.tr-work').find('[name="work_force_id[]"]').val();
				project_id			= $(this).parents('.tr-work').find('[name="project_id[]"]').val();
				wbs_id				= $(this).parents('.tr-work').find('[name="wbs_id[]"]').val();
				wbs_id_text			= $(this).parents('.tr-work').find('.class-wbs-id').text();
				location_wbs		= $(this).parents('.tr-work').find('[name="location_wbs[]"]').val();
				date				= $(this).parents('.tr-work').find('[name="date[]"]').val();
				description			= $(this).parents('.tr-work').find('[name="description[]"]').val();
				provider			= $(this).parents('.tr-work').find('[name="provider[]"]').val();
				work_force			= $(this).parents('.tr-work').find('[name="work_force[]"]').val();
				total_workers		= $(this).parents('.tr-work').find('[name="total_workers[]"]').val();
				man_hours_per_day	= $(this).parents('.tr-work').find('[name="man_hours_per_day[]"]').val();
				$('#work_force_id').val(work_force_id);
				$('#project_id').val(project_id).trigger('change');
				$('#location_wbs').val(location_wbs);
				$('#description').val(description);
				$('#provider').val(provider);
				$('#date').val(date);
				$('#work_force').val(work_force);
				$('#total_workers').val(total_workers);
				$('#man_hours_per_day').val(man_hours_per_day);
				$('#wbs_id').append(new Option(wbs_id_text, wbs_id, true, true)).trigger('change');
				$(this).parents('.tr-work').remove();
				$('.edit-item').attr('disabled',true);
			})
			.on('click','.delete-item',function()
			{
				$(this).parents('.tr-work').remove();
				swal('', '{{ Lang::get("messages.record_deleted") }}', 'error');
			});
		});
		function validation()
		{
			$.validate(
			{
				form	: '#form-container',
				modules	: 'security',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess: function($form)
				{
					concepts = $('#body_work_force .tr-work').length;
					if (concepts > 0) 
					{
						return true;
					}
					else
					{
						swal('','Debe agregar al menos un registro.','error');
						return false;
					}
				}
			});
		}
	</script>
@endsection