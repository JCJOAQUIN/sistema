@extends('layouts.child_module')

@section('data')
	@if(isset($audit))
		@component("components.forms.form", ["attributeEx" => "action=\"".route('audits.update',$audit->id)."\" method=\"POST\" id=\"form-audits\"", "files" => true])
		@slot("methodEx") PUT @endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('audits.store')."\" method=\"POST\" id=\"form-audits\"", "files" => true])
	@endif
		@if(isset($audit))
			@component("components.labels.title-divisor")
				Editar Auditoría
			@endcomponent
		@else
			@component("components.labels.title-divisor")
				Nueva Auditoría
			@endcomponent
		@endif

		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')
					Proyecto:
				@endcomponent

				@php
					$optionsProy =  collect();
					
					if(isset($audit) && isset($audit->project_id))
					{
						$project = App\Project::find($audit->project_id);
						$optionsProy = $optionsProy->concat([["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"]]);
					}
				@endphp

				@component('components.inputs.select', ["options" => $optionsProy])
					@slot('attributeEx')
						name="project_id" 
						multiple="multiple" 
						data-validation="required"
					@endslot
					@slot('classEx')
						project_id removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Código WBS:
				@endcomponent

				@php
					$optionsWBS =  collect();

					if(isset($audit) && isset($audit->wbs_id))
					{
						$wbs = App\CatCodeWBS::find($audit->wbs_id);
						$optionsWBS = $optionsWBS->concat([["value" => $wbs->id, "description" => $wbs->code_wbs, "selected" => "selected"]]);
					}
				@endphp

				@component('components.inputs.select', ["options" => $optionsWBS])
					@slot('attributeEx')
						name="code_wbs" 
						multiple="multiple" 
						@if(isset($audit) && $audit->wbs_id == "") 
							disabled="disabled" 
						@endif 
						data-validation="required"
					@endslot
					@slot('classEx')
						code_wbs removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Contratista:
				@endcomponent

				@php
					$optionsContracts =  collect();

					if(isset($audit) && isset($audit->contractor_id))
					{
						$contract = App\Contractor::find($audit->contractor_id);
						$optionsContracts = $optionsContracts->concat([["value" => $contract->id, "description" => $contract->name, "selected" => "selected"]]);
					}
				@endphp

				@component('components.inputs.select', ["options" => $optionsContracts])
					@slot('attributeEx')
						name="contractor" 
						multiple="multiple" 
						data-validation="required"
					@endslot
					@slot('classEx')
						contractor removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Contrato:
				@endcomponent

				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" 
						name="contract"
						placeholder="Ingrese el nombre del contrato" 
						data-validation="required" 
						@isset($audit) 
							value="{{ $audit->contract }}" 
						@endisset
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Auditor Líder:
				@endcomponent

				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" 
						name="auditor" 
						placeholder="Ingrese el auditor líder" 
						data-validation="required" 
						@isset($audit) 
							value="{{ $audit->auditor }}" 
						@endisset
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Tipo de Auditoría:
				@endcomponent

				@php
					$optionsTypeA = collect();
					$value = 1;
					foreach(["Gerencial", "Línea de Mando" ,"Referencia"] as $item)
					{
						$optionsTypeA = $optionsTypeA->concat(
						[
							[
								"value" => $value,
								"description" => $item,
								"selected" => (isset($audit) && $audit->type_audit == $value ? "selected" : "")
							]
						]);
						$value++;
					}
				@endphp

				@component('components.inputs.select',["options" =>	$optionsTypeA])
					@slot('attributeEx')
						name="type_audit" 
						multiple="multiple" 
						data-validation="required"
					@endslot
					@slot('classEx')
						type_audit removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Fecha:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="date" 
						placeholder="Ingrese la fecha" 
						readonly="readonly"  
						@isset($audit) 
							value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$audit->date)->format('d-m-Y') }}" 
						@endisset 
						data-validation="required"
					@endslot

					@slot('classEx')
						removeselect datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Personas involucradas:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="people_involved" 
						placeholder="Ingrese un número"  
						@isset($audit) 
							value="{{ $audit->people_involved }}" 
						@endisset 
						data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Observaciones:
				@endcomponent

				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="observations" 
						rows="5" 
						placeholder="Ingrese las observaciones"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
					{{ isset($audit) ? $audit->observations : '' }}
				@endcomponent
			</div>
		@endcomponent

		@component("components.labels.title-divisor")
			Datos adicionales
		@endcomponent

		@component('components.containers.container-form')
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre del Responsable PTI ID:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="pti_responsible" 
						placeholder="Ingrese un nombre" 
						data-validation="required" 
						@isset($audit) 
							value="{{ $audit->pti_responsible }}"
						@endisset
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Nombre de Auditor Responsable:
				@endcomponent

				@php
					$optionsAuditor =  collect();

					if(isset($audit) && isset($audit->cat_auditor_id))
					{
						$auditor = App\CatAuditor::find($audit->cat_auditor_id);
						$optionsAuditor = $optionsAuditor->concat([["value" => $auditor->id, "description" => $auditor->name, "selected" => "selected"]]);
					}
				@endphp

				@component("components.inputs.select", ["options" => $optionsAuditor])
					@slot('attributeEx')
						name="cat_auditor_id" 
						multiple="multiple" 
						data-validation="required"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Otro Responsable:
				@endcomponent

				<div class="pt-3" id="new_other_responsible">
					@if(isset($audit))
						@foreach($audit->othersResponsibles as $responsible)
							<div>
								@component("components.inputs.input-text")
									@slot('attributeEx')
										type="hidden" 
										name="other_responsible_id[]"
										placeholder="Ingrese un nombre" 
										data-validation="required" value="{{ $responsible->id }}"
									@endslot
									@slot('classEx')
										removeselect
									@endslot
								@endcomponent

								@component("components.inputs.input-text")
									@slot('attributeEx')
										type="text" 
										name="other_responsible[]"
										placeholder="Ingrese un nombre" 
										data-validation="required" 
										value="{{ $responsible->name }}"
									@endslot
									@slot('classEx')
										removeselect
									@endslot
								@endcomponent

								@component("components.buttons.button", ["variant"=>"red"])
									@slot('attributeEx')
										type="button"
									@endslot
									@slot('classEx')
										delete_other_responsible
									@endslot
									@slot('label')
										<span class="icon-x"></span>
									@endslot
								@endcomponent
							</div>
						@endforeach
					@endif
				</div>

				@component("components.buttons.button", ["variant"=>"warning"])
					@slot('attributeEx')
						type="button"
						id="add_other_responsible"
					@endslot
					@slot('label')
						<span class="icon-plus"></span>
						<span>Agregar Otro Responsable</span>
					@endslot
				@endcomponent
				<div id="delete_other_responsible"></div>
			</div>

			<div class="col-span-2">
				@component("components.labels.label")
					Otros Auditores:
				@endcomponent

				@php
					$optionsAuditors =  collect();

					if(isset($audit) && $audit->othersAuditors->count() > 0)
					{
						foreach($audit->othersAuditors->where('type',1) as $auditor)
						{
							$optionsAuditors = $optionsAuditors->concat(
							[
								[
									"value" 		=> $auditor->name,
									"description" 	=> $auditor->name,
									"selected" 		=> "selected"
								]
							]);
						}
					}
				@endphp

				@component("components.inputs.select", ["options" => $optionsAuditors])
					@slot('attributeEx')
						name="other_auditors_exists[]"  
						multiple="multiple"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent

				<div class="pt-3" id="new_other_auditor">
					@if(isset($audit))
						@foreach($audit->othersAuditorsNew as $other_auditor)
							<div>
								@component("components.inputs.input-text")
									@slot('attributeEx')
										type="hidden" 
										name="other_auditors_new_id[]"
										placeholder="Ingrese un nombre" 
										data-validation="required" 
										value="{{ $other_auditor->id }}"
									@endslot
									@slot('classEx')
										removeselect
									@endslot
								@endcomponent

								@component("components.inputs.input-text")
									@slot('attributeEx')
										type="text" 
										name="other_auditors_new[]"
										placeholder="Ingrese un nombre" 
										data-validation="required" 
										value="{{ $other_auditor->name }}"
									@endslot
									@slot('classEx')
										removeselect
									@endslot
								@endcomponent

								@component("components.buttons.button", ["variant"=>"red"])
									@slot('attributeEx')
										type="button"
									@endslot
									@slot('classEx')
										delete_other_auditor
									@endslot
									@slot('label')
										<span class="icon-x"></span>
									@endslot
								@endcomponent
							</div>
						@endforeach
					@endif
				</div>

				@component("components.buttons.button", ["variant" => "warning"])
					@slot('attributeEx')
						type="button" 
						id="add_other_auditor"
					@endslot
					@slot('label')
						<span class="icon-plus"></span> 
						<span>Agregar Otro Auditor</span>
					@endslot
				@endcomponent
				<div id="delete_other_auditor"></div>
			</div>
		@endcomponent

		@component("components.labels.title-divisor")
			Datos de Auditoría
		@endcomponent

		<div class="flex justify-center p-0 space-x-2">
			@component('components.buttons.button-approval')
				@slot('attributeEx') 
					type="radio" 
					name="audit" 
					id="acts" 
					value="acts" 
					@isset($audit) 
						checked="checked" 
					@endif
				@endslot
				@slot('classExContainer') 
					solicited 
				@endslot
				Actos Inseguros
			@endcomponent
			@component('components.buttons.button-approval')
				@slot('attributeEx') 
					type="radio" 
					name="audit" 
					id="practice" 
					value="practice"
				@endslot
				Práctica Insegura
			@endcomponent
			@component('components.buttons.button-approval')
				@slot('attributeEx') 
					type="radio" 
					name="audit" 
					id="condition" 
					value="condition"
				@endslot
				Condición Insegura
			@endcomponent
		</div>

		<div id="form-unsafe-acts">
			@if (isset($audit))
				@if ($audit->unsafeAct()->exists())
					@foreach ($audit->unsafeAct as $key=>$ua)
					@php
						$count_acts = 1 + $key; 
					@endphp
					@include('operacion.auditorias.parcial.actos_inseguros')
					@endforeach
					<div id="new_form_acts"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_act" 
								id="add_act"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Acto Inseguro</span>
						@endcomponent
					</div>
				@else
					@php
						$count_acts = 1; 
					@endphp
					@include('operacion.auditorias.parcial.actos_inseguros')
					<div id="new_form_acts"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_act" 
								id="add_act"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Acto Inseguro</span>
						@endcomponent
					</div>
				@endif
			@else
				@php
					$count_acts = 1; 
				@endphp
				@include('operacion.auditorias.parcial.actos_inseguros')
				<div id="new_form_acts"></div>
				<div class="text-center pt-2">
					@component('components.buttons.button', ["variant"=>"warning"])
						@slot('attributeEx')
							type="button" 
							name="add_act" 
							id="add_act"
						@endslot
						@slot('classEx')
							add2
						@endslot
						<span class="icon-plus"></span> 
						<span>Agregar Acto Inseguro</span>
					@endcomponent
				</div>
			@endif
		</div>
		<div id="delete_ua"></div>
		
		<div id="form-unsafe-practice">
			{{-- FORMULARIO PRACTICA INSEGURA --}}
			@if(isset($audit))
				@if($audit->unsafePractices()->exists())
					@foreach($audit->unsafePractices as $key=>$up)
						@php
							$count_unsafe_practice = 1 + $key;
						@endphp
						@include('operacion.auditorias.parcial.practica_insegura')
					@endforeach
					<div id="new_form_practice"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_practice" 
								id="add_practice"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Práctica Insegura</span>
						@endcomponent
					</div>
				@else
					@php
						$count_unsafe_practice = 1;
					@endphp
					@include('operacion.auditorias.parcial.practica_insegura')
					<div id="new_form_practice"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_practice" 
								id="add_practice"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Práctica Insegura</span>
						@endcomponent
					</div>
				@endif
			@else
				@php
					$count_unsafe_practice = 1;
				@endphp
				@include('operacion.auditorias.parcial.practica_insegura')
				<div id="new_form_practice"></div>
				<div class="text-center pt-2">
					@component('components.buttons.button', ["variant"=>"warning"])
						@slot('attributeEx')
							type="button" 
							name="add_practice" 
							id="add_practice"
						@endslot
						@slot('classEx')
							add2
						@endslot
						<span class="icon-plus"></span> 
						<span>Agregar Práctica Insegura</span>
					@endcomponent
				</div>
			@endif
		</div>
		<div id="delete_up"></div>

		<div id="form-unsafe-condition">
			{{-- FORMULARIO CONDICION INSEGURA --}}
			@if(isset($audit))
				@if($audit->unsafeConditions()->exists())
					@foreach($audit->unsafeConditions as $key=>$uc)
						@php
							$count_unsafe_condition = 1 + $key;
						@endphp
						@include('operacion.auditorias.parcial.condicion_insegura')
					@endforeach
					<div id="new_form_condition"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_condition" 
								id="add_condition"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Condición Insegura</span>
						@endcomponent
					</div>
				@else
					@php
						$count_unsafe_condition = 1;
					@endphp
					@include('operacion.auditorias.parcial.condicion_insegura')
					<div id="new_form_condition"></div>
					<div class="text-center pt-2">
						@component('components.buttons.button', ["variant"=>"warning"])
							@slot('attributeEx')
								type="button" 
								name="add_condition" 
								id="add_condition"
							@endslot
							@slot('classEx')
								add2
							@endslot
							<span class="icon-plus"></span> 
							<span>Agregar Condición Insegura</span>
						@endcomponent
					</div>
				@endif
			@else
				@php
					$count_unsafe_condition = 1;
				@endphp
				@include('operacion.auditorias.parcial.condicion_insegura')
				<div id="new_form_condition"></div>
				<div class="text-center pt-2">
					@component('components.buttons.button', ["variant"=>"warning"])
						@slot('attributeEx')
							type="button" 
							name="add_condition" 
							id="add_condition"
						@endslot
						@slot('classEx')
							add2
						@endslot
						<span class="icon-plus"></span> 
						<span>Agregar Condición Insegura</span>
					@endcomponent
				</div>
			@endif
		</div>
		<div id="delete_uc"></div>
		<div id="inputs"></div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@if(!isset($audit))
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
						name="send"
					@endslot
					REGISTRAR
				@endcomponent
				@component("components.buttons.button",["variant" => "reset"])
					@slot('classEx') btn-delete-form @endslot
					@slot("attributeEx")
						type="reset"
						name="borrar"
						value="Borrar campos"
					@endslot
					BORRAR CAMPOS
				@endcomponent
			@else
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
						name="send"
					@endslot
					ACTUALIZAR
				@endcomponent
				@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
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
			@endif
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@isset($audit)
				checkedAudit();
			@else
				$('#form-unsafe-acts').hide();
				$('#form-unsafe-practice').hide();
				$('#form-unsafe-condition').hide();
			@endisset
			$('.accordion-content').stop(true,true).slideUp('slow').removeClass('show').addClass('hide');
			validation();
			addSelects();
			
			
			$('[name="date"],[name="ua_fv"]').datepicker({  dateFormat: "dd-mm-yy" });
			$('[name="people_involved"]').numeric({ negative : false, decimal: false });
			$(document).on('change','input[name="audit"]',function()
			{			
				checkedAudit();
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				swal({
					title		: "Limpiar formulario",
					text		: "Esta acción eliminará todos los datos ingresados en el(los) formulario(s) de auditorías.",
					icon		: "warning",
					buttons		: true,
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						$('.form-act').remove();
						$('.form_practice').remove();
						$('.form_condition').remove();
						if($('.count_acts').length == 0)
						{
							newUnsafeAct();
						}
						if ($('.count_unsafe_practice').length == 0) 
						{
							newUnsafePractice();
						}
						if ($('.count_unsafe_condition').length == 0) 
						{
							newUnsafeCondition();
						}
						$('#form-audits')[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			{{-- SCRIPT ACTO INSEGURO --}}
			.on('change', '[name="temp_ua_category_id[]"]',function()
			{
				id_category	= $('option:selected',this).val();  
				object		= $(this);
				object.parents('.accordion-content').find('[name="temp_ua_subcategory_id[]"]').html('');
				$.ajax(
				{
					type    : 'get',
					url     : '{{ route("audits.get-subcat") }}',
					data    : {'id_category':id_category},
					success : function(data)
					{
						if(data != "")
						{
							$.each(data,function(i, d)
							{
								object.parents('.accordion-content').find('[name="temp_ua_subcategory_id[]"]').append('<option value='+d.id+'>'+d.name+'</option>');
							});
						}                            
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('[name="temp_ua_subcategory_id[]"]').val(null).trigger('change');
					}
				}); 
			})
			.on('click','.addDocBefore',function()
			{
				act_before = $(this).siblings('.numberAct_before').val();
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="ua_before_real_path_'+act_before+'[]" class="path">');
				
				$('.documents-before').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('click','.addDocAfter',function()
			{
				act_after = $(this).siblings('.numberAct_after').val();
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="ua_after_real_path_'+act_after+'[]" class="path">');

				$('.documents-after').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('change','.pathActioner',function(e)
			{
				filename        = $(this);
				uploadedName    = $(this).parent('.uploader-content').siblings('.path');
				extention       = /\.jpg|\.png|\.jpeg/i;
				
				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg o png.', 'warning');
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
					formData    = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$.ajax(
					{
						type        : 'post',
						url         : '{{ route("audits.upload") }}',
						data        : formData,
						contentType : false,
						processData : false,
						success     : function(r)
						{
							if(r.error=='DONE')
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('.path').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
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
					icon    : '{{ asset(getenv('LOADING_IMG')) }}',
					button  : false
				});
				actioner        = $(this);
				uploadedName    = $(this).parents('.docs-p').find('.path');
				formData        = new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type        : 'post',
					url         : '{{ route("audits.upload") }}',
					data        : formData,
					contentType : false,
					processData : false,
					success     : function(r)
					{
						swal.close();
						actioner.parent().parent('.docs-p').remove();
					},
					error       : function()
					{
						swal.close();
						actioner.parent().parent('.docs-p').remove();
					}
				});
				$(this).parents('div.docs-p').remove();
			})
			.on('click','.delete_ua',function()
			{
				swal({
					title		: "Eliminar formulario",
					text		: "¿Confirma que desea eliminar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willDelete) =>
				{
					if(willDelete)
					{
						id_act = $(this).parents('.accordion').find('[name="ua_id[]"]').val();
						if (id_act != "x")
						{
							input = $('<input type="text" name="delete_ua[]" value="'+id_act+'">');
							$('#delete_ua').append(input);
						}
						
						$(this).parents('.form-act').remove();
						if($('.form-act').length>0)
						{
							$('.count_acts').each(function(i,v)
							{
								$(this).html("Acto Inseguro # "+(i+1));
							})
						}

						if($('.count_acts').length == 0)
						{
							newUnsafeAct();
						}
						swal('','Acto inseguro eliminado exitosamente','success');
					}
				});
			})
			.on('click','#add_act',function()
			{
				newUnsafeAct();
			})
			{{-- SCRIPT PRACTICA INSEGURA --}}
			.on('click','#add_practice',function()
			{
				newUnsafePractice();
			})
			.on('change', '[name="temp_up_category_id[]"]',function()
			{
				id_category	= $('option:selected',this).val();  
				object		= $(this);
				object.parents('.accordion-content').find('[name="temp_up_subcategory_id[]"]').html('');

				$.ajax(
				{
					type    : 'get',
					url     : '{{ route("audits.get-subcat") }}',
					data    : {'id_category':id_category},
					success : function(data)
					{
						if(data != "")
						{
							$.each(data,function(i, d)
							{
								object.parents('.accordion-content').find('[name="temp_up_subcategory_id[]"]').append('<option value='+d.id+'>'+d.name+'</option>');
							});
						}                            
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('[name="temp_up_subcategory_id[]"]').val(null).trigger('change');
					}
				}); 
			})
			.on('click','[name="add_doc_up_before"]',function()
			{
				count_unsafe_practice = $(this).attr('data-num-up');
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="up_before_real_path_'+count_unsafe_practice+'[]" class="path">');
				$('.up_before_documents').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('click','[name="add_doc_up_after"]',function()
			{
				count_unsafe_practice = $(this).attr('data-num-up');
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="up_after_real_path_'+count_unsafe_practice+'[]" class="path">');
				$('.up_after_documents').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('click','.delete_up',function()
			{
				swal({
					title		: "",
					text		: "¿Confirma que desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						id = $(this).attr('data-id-up');
						if (id != "x") 
						{
							$('#delete_up').append($('<input type="hidden" name="delete_up[]" value="'+id+'">'));
						}

						$(this).parents('.form_practice').remove();

						if($('.count_unsafe_practice').length>0)
						{
							$('.count_unsafe_practice').each(function(i,v)
							{
								$(this).html("Práctica Insegura # "+(i+1));
							});
						}

						if ($('.count_unsafe_practice').length == 0) 
						{
							newUnsafePractice();
						}
						swal('','Práctica insegura eliminada exitosamente','success');
					}
					else
					{
						swal.close();
					}
				});
			})
			{{-- SCRIPT CONDICION INSEGURA --}}
			.on('click','#add_condition',function()
			{
				newUnsafeCondition();
			})
			.on('change', '[name="temp_uc_category_id[]"]',function()
			{
				id_category	= $('option:selected',this).val();  
				object		= $(this);

				object.parents('.accordion-content').find('[name="temp_uc_subcategory_id[]"]').html('');

				$.ajax(
				{
					type    : 'get',
					url     : '{{ route("audits.get-subcat") }}',
					data    : {'id_category':id_category},
					success : function(data)
					{
						if(data != "")
						{
							$.each(data,function(i, d)
							{
								object.parents('.accordion-content').find('[name="temp_uc_subcategory_id[]"]').append('<option value='+d.id+'>'+d.name+'</option>');
							});
						}                            
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('[name="temp_uc_subcategory_id[]"]').val(null).trigger('change');
					}
				}); 
			})
			.on('click','[name="add_doc_uc_before"]',function()
			{
				count_unsafe_condition = $(this).attr('data-num-uc');
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="uc_before_real_path_'+count_unsafe_condition+'[]" class="path">');
				$('.uc_before_documents').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('click','[name="add_doc_uc_after"]',function()
			{
				count_unsafe_condition = $(this).attr('data-num-uc');
				@php
					$newDoc = view('components.documents.upload-files',[					
						"attributeExInput"     => "type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"         => "pathActioner",
						"classExDelete"        => "delete-doc",
						"attributeExDelete"	   => "type=\"button\""
					])->render();
				@endphp
				newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				inputPath = $('<input type="hidden" name="uc_after_real_path_'+count_unsafe_condition+'[]" class="path">');
				$('.uc_after_documents').removeClass('hidden').append($(newDoc).append(inputPath));
			})
			.on('click','.delete_uc',function()
			{
				swal({
					title		: "",
					text		: "¿Confirma que desea eliminar el registro?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						id = $(this).attr('data-id-uc');
						if (id != "x") 
						{
							$('#delete_uc').append($('<input type="hidden" name="delete_uc[]" value="'+id+'">'));
						}

						$(this).parents('.form_condition').remove();

						if($('.count_unsafe_condition').length>0)
						{
							$('.count_unsafe_condition').each(function(i,v)
							{
								$(this).html("Condición Insegura: # "+(i+1));
							});
						}

						if ($('.count_unsafe_condition').length == 0) 
						{
							newUnsafeCondition();
						}
						swal('','Condición insegura eliminada exitosamente','success');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','#add_other_responsible',function()
			{
				@php
					$otherResponsible = "";
					$inputR = view('components.inputs.input-text',[
						"classEx"	  => "removeselect",
						"attributeEx" => "type=\"hidden\" name=\"other_responsible_id[]\" value=\"x\"",
					])->render();
					$inputRV = view('components.inputs.input-text',[
						"classEx"	  => "removeselect",
						"attributeEx" => "data-validation=\"required\" type=\"text\" name=\"other_responsible[]\" placeholder=\"Ingrese un nombre\"",
					])->render();
					$buttonX = view('components.buttons.button', [
						"variant" => "red", 
						"attributeEx" => "type=\"button\"", 
						"classEx" => "delete_other_responsible", 
						"label" => '<span class="icon-x"></span>'
					])->render();
					$otherResponsible .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "","<div class=\"pt-4\">".$inputR.$inputRV.$buttonX."</div>"));
				@endphp

				$otherResponsible = '{!!preg_replace("/(\r)*(\n)*/", "", $otherResponsible)!!}';
				responsible = $($otherResponsible);		
				
				$('#new_other_responsible').append(responsible);
			})
			.on('click','#add_other_auditor',function()
			{
				@php
					$otherAuditor = "";
					$inputA = view('components.inputs.input-text',[
						"classEx"	  => "removeselect",
						"attributeEx" => "data-validation=\"required\" type=\"hidden\" name=\"other_auditors_new_id[]\" placeholder=\"Ingrese un nombre\" value=\"x\"",
					])->render();
					$inputAV = view('components.inputs.input-text',[
						"classEx"	  => "removeselect",
						"attributeEx" => "data-validation=\"required\" type=\"text\" name=\"other_auditors_new[]\" placeholder=\"Ingrese un nombre\"",
					])->render();
					$buttonX = view('components.buttons.button', [
						"variant" => "red", 
						"attributeEx" => "type=\"button\"", 
						"classEx" => "delete_other_auditor", 
						"label" => '<span class="icon-x"></span>'
					])->render();
					$otherAuditor .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "","<div class=\"pt-4\">".$inputA.$inputAV.$buttonX."</div>"));
				@endphp

				$otherAuditor = '{!!preg_replace("/(\r)*(\n)*/", "", $otherAuditor)!!}';
				auditor = $($otherAuditor);	

				$('#new_other_auditor').append(auditor);
			})
			.on('click','.delete_other_responsible',function()
			{
				other_responsible_id = $(this).parent('div').find('[name="other_responsible_id[]"]').val();
				if (other_responsible_id != "x") 
				{
					$('#delete_other_responsible').append($('<input type="hidden" name="delete_other_responsible[]" value="'+other_responsible_id+'">'));
				}
				$(this).parent('div').remove();
			})
			.on('click','.delete_other_auditor',function()
			{
				other_auditors_new_id = $(this).parent('div').find('[name="other_auditors_new_id[]"]').val();
				if (other_auditors_new_id != "x") 
				{
					$('#delete_other_auditor').append($('<input type="hidden" name="delete_other_auditor[]" value="'+other_auditors_new_id+'">'));
				}
				$(this).parent('div').remove();
			})
			.on('click','.accordion',function()
			{
				content = $(this).siblings('.accordion-content');
				indication = $(this).find('.indication');
				if (content.hasClass('show')) 
				{
					content.stop(true,true).slideUp('slow');
					content.removeClass('show');
					content.addClass('hide');
					indication.addClass('icon-show-down');
					indication.removeClass('icon-show-up');
				}
				else if (content.hasClass('hide')) 
				{
					content.stop(true,true).slideDown('slow');
					content.removeClass('hide');
					content.addClass('show');
					indication.addClass('icon-show-up');
					indication.removeClass('icon-show-down');
				}
				addSelects();
			})
		});

		function addSelects()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".subcategory", 
						"placeholder"            => 'Seleccione una subcategoría', 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".dangerousness", 
						"placeholder"            => 'Seleccione el valor de peligrosidad', 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '.id_category', 
						"placeholder"            => "Seleccione una categoría", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '.status', 
						"placeholder"            => "Seleccione el estado del reporte", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name="type_audit"]', 
						"placeholder"            => "Seleccione el tipo de auditoría", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '[name="project_id"]', 'model': 14});
			generalSelect({'selector': '[name="code_wbs"]','depends':'[name="project_id"]', 'model':1});				
			generalSelect({'selector': '[name="contractor"]', 'model': 50});
			generalSelect({'selector': '[name="cat_auditor_id"]', 'model': 55});
			generalSelect({'selector': '[name="other_auditors_exists[]"]', 'model': 55, 'maxSelection' : -1});
			$('.datepicker').datepicker({  dateFormat: "dd-mm-yy" });
			$('.fv').datepicker({  dateFormat: "dd-mm-yy" });
		}
		function newUnsafeAct()
		{
			count_acts = $('.count_acts').length;
			count_acts = count_acts+1;
			@php
				$formUA = 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Categoría:"
					])));

					$optionsCategory = collect();

					foreach (App\AuditCategory::orderBy('id','asc')->get() as $category)
					{
						$optionsCategory = $optionsCategory->concat(
						[
							[
								"value" 		=> $category->id,
								"description" 	=> $category->name,
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_ua_category_id[]\"",
						'classEx' 		=> "id_category removeselect"
					])));
					$formUA .= '<div class="ua_category"></div>';
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Subcategoría:"
					])));

					$optionsSubCategory = collect();
							
					foreach (App\AuditSubcategory::orderBy('id','asc')->get() as $subCategory)
					{
						$optionsSubCategory = $optionsSubCategory->concat(
						[
							[
								"value" 		=> $subCategory->id,
								"description" 	=> $subCategory->name
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsSubCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_ua_subcategory_id[]\"",
						'classEx' 		=> "subcategory  removeselect"
					])));
					$formUA .= '<div class="ua_subcategory"></div>';
				$formUA .= '</div>';
				
				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Valor de peligrosidad:"
					])));

					$optionsDanger =  collect();

					foreach(["1/3", "1" ,"3"] as $item)
					{
						$optionsDanger = $optionsDanger->concat(
						[
							[
								"value" 		=> $item,
								"description" 	=> $item
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsDanger,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_ua_dangerousness[]\"",
						'classEx' 		=> "dangerousness  removeselect"
					])));
					$formUA .= '<div class="ua_dangerousness"></div>';
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Descripción:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"ua_description[]\" id=\"description\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese una descripción\"",
						"classEx"		=>  "description",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción correctiva inmediata:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"ua_action[]\" id=\"action\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción correctiva inmediata\"",
						"classEx"		=>  "action",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción para prevenir repetición:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"ua_prevent[]\" id=\"prevent\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción para prevenir la repetición\"",
						"classEx"		=>  "prevent",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"RE:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"ua_re[]\" id=\"re\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese el RE\"",
						"classEx"		=>  "prevent",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"FV:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"ua_fv[]\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\"",
						"classEx"		=>  "fv removeselect datepicker2"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Estado del Reporte:"
					])));

					$optionsStatus =  collect();
					$value= 1;

					foreach(["Abierto", "Cerrado"] as $item)
					{
						$optionsStatus = $optionsStatus->concat(
						[
							[
								"value" =>  $value,
								"description" => $item
							]
						]);
						$value++;
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsStatus,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_ua_status[]\"",
						'classEx' 		=> "status  removeselect"
					])));
					$formUA .= '<div class="ua_status"></div>';
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Responsable de dicha situación:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"ua_responsable[]\" placeholder=\"Ingrese el responsable de dicha situación\"",
						"classEx"		=>  "removeselect responsable"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes antes de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="documents-before hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx" => "type=\"hidden\" value=\"count_acts\"",
						"classEx"	  => "numberAct_before"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning", 
						"classEx" 		=> "addDocBefore",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>',
						"attributeEx" 	=> "type=\"button\""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes despues de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="documents-after hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx" => "type=\"hidden\" value=\"+count_acts\"",
						"classEx"	  => "numberAct_after"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning", 
						"classEx" 		=> "addDocAfter",
						"attributeEx" 	=> "name=\"addDocAfter\" type=\"button\"",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>'
					])));
				$formUA .= '</div>';

				$formUA2 = 
				'<div class="form-act pt-6">';
					$formUA2 .=
					'<div class="accordion">';
						$formUA2 .=
						'<div class="bg bg-orange-400 flex">';
							$formUA2 .=
							'<div class="w-full md:pl-20 pl-14">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.labels.label",[
									"attributeEx" => "name=\"titleTable\"",
									"classEx"	  => "text-white text-center text-lg font-bold w-full align-middle py-2 count_acts"
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.input-text",[
									"attributeEx" => "type=\"hidden\" name=\"ua_id[]\" value=\"x\""
								])));
							$formUA2.= '</div>';
							$formUA2.= 
							'<div class="float-right flex">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant"		=> "secondary",
									"label" 		=> '<span class="indication fas icon-show-up"></span>',
									"attributeEx" 	=> "type=\"button\"",
									"slot"			=> ""
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant" 		=> "red",
									"label" 		=> '<span class="icon-x"></span>',
									"attributeEx" 	=> "type=\"button\"",
									"classEx" 		=> "delete_ua",
									"slot"			=> ""
								]))); 
							$formUA2.= '</div>';
						$formUA2.= '</div>';
					$formUA2.= '</div>';

					$formUA2 .=
					'<div class="accordion-content hide">';
						$formUA2 .=
						'<div class="form-row">';
							$formUA2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.containers.container-form", [
								"content" => $formUA,
								"classEx" => "my-0"
							])));
						$formUA2.= '</div>';
					$formUA2.= '</div>';
				$formUA2.= '</div>';
			@endphp

			form = '{!!preg_replace("/(\r)*(\n)*/", "", $formUA2)!!}';
			newForm = $(form);
			newForm.find(".count_acts").text("Acto Inseguro: #"+count_acts);
			$('#new_form_acts').append(newForm);
			addSelects();
		}
		function newUnsafePractice() 
		{
			count_unsafe_practice 	= $('.count_unsafe_practice').length + 1;
			@php
				$formUA = 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Categoría:"
					])));

					$optionsCategory = collect();

					foreach (App\AuditCategory::orderBy('id','asc')->get() as $category)
					{
						$optionsCategory = $optionsCategory->concat(
						[
							[
								"value" 		=> $category->id,
								"description" 	=> $category->name,
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_up_category_id[]\"",
						'classEx' 		=> "id_category removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Subcategoría:"
					])));

					$optionsSubCategory = collect();
							
					foreach (App\AuditSubcategory::orderBy('id','asc')->get() as $subCategory)
					{
						$optionsSubCategory = $optionsSubCategory->concat(
						[
							[
								"value" 		=> $subCategory->id,
								"description" 	=> $subCategory->name
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsSubCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_up_subcategory_id[]\"",
						'classEx' 		=> "subcategory  removeselect"
					])));
				$formUA .= '</div>';
				
				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Valor de peligrosidad:"
					])));

					$optionsDanger =  collect();

					foreach(["1/3", "1" ,"3"] as $item)
					{
						$optionsDanger = $optionsDanger->concat(
						[
							[
								"value" 		=> $item,
								"description" 	=> $item
							]
						]);
					}
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsDanger,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_up_dangerousness[]\"",
						'classEx' 		=> "dangerousness  removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Descripción:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"up_description[]\" id=\"description\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese una descripción\"",
						"classEx"		=>  "description",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción correctiva inmediata:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"up_action[]\" id=\"action\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción correctiva inmediata\"",
						"classEx"		=>  "action",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción para prevenir repetición:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"up_prevent[]\" id=\"prevent\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción para prevenir la repetición\"",
						"classEx"		=>  "prevent",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"RE:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"up_re[]\" id=\"re\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese el RE\"",
						"classEx"		=>  "prevent",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"FV:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"up_fv[]\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\"",
						"classEx"		=>  "fv removeselect datepicker2"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Estado del Reporte:"
					])));

					$optionsStatus =  collect();
					$value= 1;

					foreach(["Abierto", "Cerrado"] as $item)
					{
						$optionsStatus = $optionsStatus->concat(
						[
							[
								"value" =>  $value,
								"description" => $item
							]
						]);
						$value++;
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsStatus,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_up_status[]\"",
						'classEx' 		=> "status  removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Responsable de dicha situación:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"up_responsable[]\" placeholder=\"Ingrese el responsable de dicha situación\"",
						"classEx"		=>  "removeselect responsable"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes antes de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="up_before_documents hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>',
						"attributeEx" 	=> "type=\"button\" name=\"add_doc_up_before\"",
						"classEx"		=> "add_doc_up_before"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes despues de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="up_after_documents hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning",
						"attributeEx" 	=> "name=\"add_doc_up_after\" type=\"button\"",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>',
						"classEx"		=> 'add_doc_up_after'
					])));
				$formUA .= '</div>';

				$formUA2 = 
				'<div class="form_practice pt-6">';
					$formUA2 .=
					'<div class="accordion">';
						$formUA2 .=
						'<div class="bg bg-orange-400 flex">';
							$formUA2 .=
							'<div class="w-full md:pl-20 pl-14">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.labels.label",[
									"attributeEx" => "name=\"titleTable\"",
									"classEx"	  => "text-white text-center text-lg font-bold w-full align-middle py-2 count_unsafe_practice"
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.input-text",[
									"attributeEx" => "type=\"hidden\" name=\"up_id[]\" value=\"x\""
								])));
							$formUA2.= '</div>';
							$formUA2.= 
							'<div class="float-right flex">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant"		=> "secondary",
									"label" 		=> '<span class="indication fas icon-show-up"></span>',
									"attributeEx" 	=> "type=\"button\"",
									"slot"			=> ""
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant" 		=> "red",
									"label" 		=> '<span class="icon-x"></span>',
									"attributeEx" 	=> "type=\"button\" data-id-up=\"x\"",
									"classEx" 		=> "delete_up",
									"slot"			=> ""
								]))); 
							$formUA2.= '</div>';
						$formUA2.= '</div>';
					$formUA2.= '</div>';

					$formUA2 .=
					'<div class="accordion-content hide">';
						$formUA2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.containers.container-form", [
							"content" => $formUA,
							"classEx" => "my-0"
						])));
					$formUA2.= '</div>';
				$formUA2.= '</div>';
			@endphp

			form = '{!!preg_replace("/(\r)*(\n)*/", "", $formUA2)!!}';
			newForm = $(form);
			newForm.find(".count_unsafe_practice").text("Práctica Insegura: # "+count_unsafe_practice);
			newForm.find(".add_doc_up_after").attr('data-num-up', count_unsafe_practice);
			newForm.find(".add_doc_up_before").attr('data-num-up', count_unsafe_practice);
			$('#new_form_practice').append(newForm);
			addSelects();
		}	
		function newUnsafeCondition() 
		{
			count_unsafe_condition 	= $('.count_unsafe_condition').length + 1;

			@php
				$formUA = 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Categoría:"
					])));

					$optionsCategory = collect();

					foreach (App\AuditCategory::orderBy('id','asc')->get() as $category)
					{
						$optionsCategory = $optionsCategory->concat(
						[
							[
								"value" 		=> $category->id,
								"description" 	=> $category->name,
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_uc_category_id[]\"",
						'classEx' 		=> "id_category removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Subcategoría:"
					])));

					$optionsSubCategory = collect();
							
					foreach (App\AuditSubcategory::orderBy('id','asc')->get() as $subCategory)
					{
						$optionsSubCategory = $optionsSubCategory->concat(
						[
							[
								"value" 		=> $subCategory->id,
								"description" 	=> $subCategory->name
							]
						]);
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsSubCategory,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_uc_subcategory_id[]\"",
						'classEx' 		=> "subcategory  removeselect"
					])));
				$formUA .= '</div>';
				
				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Valor de peligrosidad:"
					])));

					$optionsDanger =  collect();

					foreach(["1/3", "1" ,"3"] as $item)
					{
						$optionsDanger = $optionsDanger->concat(
						[
							[
								"value" 		=> $item,
								"description" 	=> $item
							]
						]);
					}
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsDanger,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_uc_dangerousness[]\"",
						'classEx' 		=> "dangerousness  removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Descripción:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"uc_description[]\" id=\"description\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese una descripción\"",
						"classEx"		=>  "description",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción correctiva inmediata:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"uc_action[]\" id=\"action\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción correctiva inmediata\"",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Acción para prevenir repetición:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"uc_prevent[]\" id=\"prevent\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese la acción para prevenir la repetición\"",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"RE:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.text-area", [
						"attributeEx"	=>	"name=\"uc_re[]\" id=\"re\" rows=\"3\" cols=\"20\" placeholder=\"Ingrese el RE\"",
						"slot"			=> ""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"FV:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"uc_fv[]\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\"",
						"classEx"		=>  "fv removeselect datepicker2"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Estado del Reporte:"
					])));

					$optionsStatus =  collect();
					$value= 1;

					foreach(["Abierto", "Cerrado"] as $item)
					{
						$optionsStatus = $optionsStatus->concat(
						[
							[
								"value" =>  $value,
								"description" => $item
							]
						]);
						$value++;
					}

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.select", [
						"options" 		=> $optionsStatus,
						'attributeEx' 	=> "multiple=\"multiple\" name=\"temp_uc_status[]\"",
						'classEx' 		=> "status  removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Responsable de dicha situación:"
					])));

					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
						"attributeEx"	=>	"type=\"text\" name=\"uc_responsable[]\" placeholder=\"Ingrese el responsable de dicha situación\"",
						"classEx"		=>  "removeselect"
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes antes de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="uc_before_documents hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>',
						"attributeEx" 	=> "type=\"button\" name=\"add_doc_uc_before\""
					])));
				$formUA .= '</div>';

				$formUA .= 
				'<div class="col-span-2">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
						"label"			=>	"Imágenes despues de resolver:"
					])));
				$formUA .= '</div>';
				$formUA .= '<div class="uc_after_documents hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>';
				$formUA .=
				'<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">';
					$formUA .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.buttons.button", [
						"variant" 		=> "warning",
						"attributeEx" 	=> "name=\"add_doc_up_after\" type=\"button\"",
						"label"	  		=> '<span class="icon-plus"></span><span>Nueva imagen</span>',
						"classEx"		=> 'add_doc_uc_after'
					])));
				$formUA .= '</div>';

				$formUA2 = 
				'<div class="form_condition pt-6">';
					$formUA2 .=
					'<div class="accordion">';
						$formUA2 .=
						'<div class="bg bg-orange-400 flex">';
							$formUA2 .=
							'<div class="w-full md:pl-20 pl-14">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.labels.label",[
									"attributeEx" => "name=\"titleTable\"",
									"classEx"	  => "text-white text-center text-lg font-bold w-full align-middle py-2 count_unsafe_condition"
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view("components.inputs.input-text",[
									"attributeEx" => "type=\"hidden\" name=\"uc_id[]\" value=\"x\""
								])));
							$formUA2.= '</div>';
							$formUA2.= 
							'<div class="float-right flex">';
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant"		=> "secondary",
									"label" 		=> '<span class="indication fas icon-show-up"></span>',
									"attributeEx" 	=> "type=\"button\"",
									"slot"			=> ""
								])));
								$formUA2.= html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('components.buttons.button', [
									"variant" 		=> "red",
									"label" 		=> '<span class="icon-x"></span>',
									"attributeEx" 	=> "type=\"button\" data-id-up=\"x\"",
									"classEx" 		=> "delete_uc",
									"slot"			=> ""
								]))); 
							$formUA2.= '</div>';
						$formUA2.= '</div>';
					$formUA2.= '</div>';

					$formUA2 .=
					'<div class="accordion-content hide">';
						$formUA2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.containers.container-form", [
							"content" => $formUA,
							"classEx" => "my-0"
						])));
					$formUA2.= '</div>';
				$formUA2.= '</div>';
			@endphp

			form = '{!!preg_replace("/(\r)*(\n)*/", "", $formUA2)!!}';
			newForm = $(form);
			newForm.find(".count_unsafe_condition").text("Condición Insegura: # "+count_unsafe_condition);
			newForm.find(".add_doc_uc_after").attr('data-num-uc', count_unsafe_condition);
			newForm.find(".add_doc_uc_before").attr('data-num-uc', count_unsafe_condition);
			$('#new_form_condition').append(newForm);
			addSelects();
		}
		function validation()
		{
			$.validate(
			{
				form	: '#form-audits',
				modules	: 'security',
				onError   : function($form)
				{
					swal('','{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					flag = true;
					flagForm = "nothing";

					$('.form-act').each(function(i,v)
					{
						ua_category			= $(this).find('[name="temp_ua_category_id[]"]');
						ua_subcategory		= $(this).find('[name="temp_ua_subcategory_id[]"]');
						ua_dangerousness	= $(this).find('[name="temp_ua_dangerousness[]"]');
						ua_description		= $(this).find('[name="ua_description[]"]');
						ua_action			= $(this).find('[name="ua_action[]"]');
						ua_prevent			= $(this).find('[name="ua_prevent[]"]');
						ua_re				= $(this).find('[name="ua_re[]"]');
						ua_fv				= $(this).find('[name="ua_fv[]"]');
						ua_status			= $(this).find('[name="temp_ua_status[]"]');
						ua_responsable		= $(this).find('[name="ua_responsable[]"]');
					
						ua_description.removeClass('error');
						ua_action.removeClass('error');
						ua_prevent.removeClass('error');
						ua_re.removeClass('error');
						ua_fv.removeClass('error');
						ua_responsable.removeClass('error');

						if(ua_category.parent().find('.form-error').length > 0)
						{
							ua_category.parent().find('.form-error').remove();
						}
						if(ua_subcategory.parent().find('.form-error').length > 0)
						{
							ua_subcategory.parent().find('.form-error').remove();
						}
						if(ua_dangerousness.parent().find('.form-error').length > 0)
						{
							ua_dangerousness.parent().find('.form-error').remove();
						}
						if(ua_status.parent().find('.form-error').length > 0)
						{
							ua_status.parent().find('.form-error').remove();
						}
						
						if($('option:selected',ua_category).val() == undefined && $('option:selected',ua_subcategory).val() == undefined && $('option:selected',ua_dangerousness).val() == undefined && ua_description.val().trim() == '' && ua_action.val().trim() == '' && ua_prevent.val().trim() == '' && ua_re.val().trim() == '' && ua_fv.val().trim() == '' && $('option:selected',ua_status).val() == undefined && ua_responsable.val().trim() == '')
						{
							return true;
						}

						if($('option:selected',ua_category).val() == undefined || $('option:selected',ua_subcategory).val() == undefined || $('option:selected',ua_dangerousness).val() == undefined || ua_description.val().trim() == '' || ua_action.val().trim() == '' || ua_prevent.val().trim() == '' || ua_re.val().trim() == '' || ua_fv.val().trim() == '' || $('option:selected',ua_status).val() == undefined || ua_responsable.val().trim() == '')
						{
							if($('option:selected',ua_category).val() == undefined)
							{
								ua_category.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if($('option:selected',ua_subcategory).val() == undefined)
							{
								ua_subcategory.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if($('option:selected',ua_dangerousness).val() == undefined)
							{
								ua_dangerousness.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_description.val().trim() == '')
							{
								ua_description.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_action.val().trim() == '')
							{
								ua_action.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_prevent.val().trim() == '')
							{
								ua_prevent.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_re.val().trim() == '')
							{
								ua_re.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_fv.val().trim() == '')
							{
								ua_fv.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if($('option:selected',ua_status).val() == undefined)
							{
								ua_status.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if(ua_responsable.val().trim() == '')
							{
								ua_responsable.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							flag = false;
							flagForm = "act"
						}
						
					});

					$('.form_practice').each(function(i,v)
					{
						temp_up_category_id		= $(this).find('[name="temp_up_category_id[]"]');
						temp_up_subcategory_id	= $(this).find('[name="temp_up_subcategory_id[]"]');
						temp_up_dangerousness	= $(this).find('[name="temp_up_dangerousness[]"]');
						temp_up_status			= $(this).find('[name="temp_up_status[]"]');
						up_description			= $(this).find('[name="up_description[]"]');
						up_action				= $(this).find('[name="up_action[]"]');
						up_prevent				= $(this).find('[name="up_prevent[]"]');
						up_re					= $(this).find('[name="up_re[]"]');
						up_fv					= $(this).find('[name="up_fv[]"]');
						up_responsable			= $(this).find('[name="up_responsable[]"]');

						up_description.removeClass('error');
						up_action.removeClass('error');
						up_prevent.removeClass('error');
						up_re.removeClass('error');
						up_fv.removeClass('error');
						up_responsable.removeClass('error');

						if (temp_up_category_id.parent('div').find(".help-block").length >0) 
						{
							temp_up_category_id.parent('div').find(".help-block").remove();	
						}
						if (temp_up_subcategory_id.parent('div').find(".help-block").length > 0) 
						{
							temp_up_subcategory_id.parent('div').find(".help-block").remove();	
						}
						if (temp_up_dangerousness.parent('div').find(".help-block").length > 0) 
						{
							temp_up_dangerousness.parent('div').find(".help-block").remove();	
						}
						if (temp_up_status.parent('div').find(".help-block").length > 0) 
						{
							temp_up_status.parent('div').find(".help-block").remove();	
						}

						if($('option:selected',temp_up_category_id).val() == undefined && $('option:selected',temp_up_subcategory_id).val() == undefined && $('option:selected',temp_up_dangerousness).val() == undefined && $('option:selected',temp_up_status).val() == undefined && up_description.val().trim() == "" && up_action.val().trim() == "" && up_prevent.val().trim() == "" && up_re.val().trim() == "" && up_fv.val().trim() == "" && up_responsable.val().trim() == "")
						{
							return true;
						}

						if($('option:selected',temp_up_category_id).val() == undefined || $('option:selected',temp_up_subcategory_id).val() == undefined || $('option:selected',temp_up_dangerousness).val() == undefined || $('option:selected',temp_up_status).val() == undefined || up_description.val().trim() == "" || up_action.val().trim() == "" || up_prevent.val().trim() == "" || up_re.val().trim() == "" || up_fv.val().trim() == "" || up_responsable.val().trim() == "")
						{
							if($('option:selected',temp_up_category_id).val() == undefined)
							{
								if (temp_up_category_id.parent('div').find(".help-block").length == 0) 
								{
									temp_up_category_id.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_up_subcategory_id).val() == undefined)
							{
								if (temp_up_subcategory_id.parent('div').find(".help-block").length == 0) 
								{
									temp_up_subcategory_id.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_up_dangerousness).val() == undefined)
							{
								if (temp_up_dangerousness.parent('div').find(".help-block").length == 0) 
								{
									temp_up_dangerousness.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_up_status).val() == undefined)
							{
								if (temp_up_status.parent('div').find(".help-block").length == 0) 
								{
									temp_up_status.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if(up_description.val().trim() == "")
							{
								up_description.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(up_action.val().trim() == "")
							{
								up_action.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(up_prevent.val().trim() == "")
							{
								up_prevent.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(up_re.val().trim() == "")
							{
								up_re.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(up_fv.val().trim() == "")
							{
								up_fv.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(up_responsable.val().trim() == "")
							{
								up_responsable.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							flag = false;
							flagForm = "pract";
						}
					});

					$('.form_condition').each(function(i,v)
					{
						temp_uc_category_id		= $(this).find('[name="temp_uc_category_id[]"]');
						temp_uc_subcategory_id	= $(this).find('[name="temp_uc_subcategory_id[]"]');
						temp_uc_dangerousness	= $(this).find('[name="temp_uc_dangerousness[]"]');
						temp_uc_status			= $(this).find('[name="temp_uc_status[]"]');
						uc_description			= $(this).find('[name="uc_description[]"]');
						uc_action				= $(this).find('[name="uc_action[]"]');
						uc_prevent				= $(this).find('[name="uc_prevent[]"]');
						uc_re					= $(this).find('[name="uc_re[]"]');
						uc_fv					= $(this).find('[name="uc_fv[]"]');
						uc_responsable			= $(this).find('[name="uc_responsable[]"]');

						uc_description.removeClass('error');
						uc_action.removeClass('error');
						uc_prevent.removeClass('error');
						uc_re.removeClass('error');
						uc_fv.removeClass('error');
						uc_responsable.removeClass('error');

						if (temp_uc_category_id.parent('div').find(".help-block").length >0) 
						{
							temp_uc_category_id.parent('div').find(".help-block").remove();	
						}
						if (temp_uc_subcategory_id.parent('div').find(".help-block").length > 0) 
						{
							temp_uc_subcategory_id.parent('div').find(".help-block").remove();	
						}
						if (temp_uc_dangerousness.parent('div').find(".help-block").length > 0) 
						{
							temp_uc_dangerousness.parent('div').find(".help-block").remove();	
						}
						if (temp_uc_status.parent('div').find(".help-block").length > 0) 
						{
							temp_uc_status.parent('div').find(".help-block").remove();	
						}

						if($('option:selected',temp_uc_category_id).val() == undefined && $('option:selected',temp_uc_subcategory_id).val() == undefined && $('option:selected',temp_uc_dangerousness).val() == undefined && $('option:selected',temp_uc_status).val() == undefined && uc_description.val().trim() == "" && uc_action.val().trim() == "" && uc_prevent.val().trim() == "" && uc_re.val().trim() == "" && uc_fv.val().trim() == "" && uc_responsable.val().trim() == "")
						{
							return true;
						}

						if($('option:selected',temp_uc_category_id).val() == undefined || $('option:selected',temp_uc_subcategory_id).val() == undefined || $('option:selected',temp_uc_dangerousness).val() == undefined || $('option:selected',temp_uc_status).val() == undefined || uc_description.val().trim() == "" || uc_action.val().trim() == "" || uc_prevent.val().trim() == "" || uc_re.val().trim() == "" || uc_fv.val().trim() == "" || uc_responsable.val().trim() == "")
						{
							if($('option:selected',temp_uc_category_id).val() == undefined)
							{
								if (temp_uc_category_id.parent('div').find(".help-block").length == 0) 
								{
									temp_uc_category_id.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_uc_subcategory_id).val() == undefined)
							{
								if (temp_uc_subcategory_id.parent('div').find(".help-block").length == 0) 
								{
									temp_uc_subcategory_id.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_uc_dangerousness).val() == undefined)
							{
								if (temp_uc_dangerousness.parent('div').find(".help-block").length == 0) 
								{
									temp_uc_dangerousness.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if($('option:selected',temp_uc_status).val() == undefined)
							{
								if (temp_uc_status.parent('div').find(".help-block").length == 0) 
								{
									temp_uc_status.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
								}
							}

							if(uc_description.val().trim() == "")
							{
								uc_description.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(uc_action.val().trim() == "")
							{
								uc_action.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(uc_prevent.val().trim() == "")
							{
								uc_prevent.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(uc_re.val().trim() == "")
							{
								uc_re.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(uc_fv.val().trim() == "")
							{
								uc_fv.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							if(uc_responsable.val().trim() == "")
							{
								uc_responsable.addClass('error').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}

							flag = false;
							flagForm = "con"
						}
					});

					if(flag==true && flagForm == "nothing")
					{
						$('#inputs').empty();
						$('.form_condition').each(function(i,v)
						{
							temp_uc_category_id		= $(this).find('[name="temp_uc_category_id[]"] option:selected').val();
							temp_uc_subcategory_id	= $(this).find('[name="temp_uc_subcategory_id[]"] option:selected').val();
							temp_uc_dangerousness	= $(this).find('[name="temp_uc_dangerousness[]"] option:selected').val();
							temp_uc_status			= $(this).find('[name="temp_uc_status[]"] option:selected').val();
							if (temp_uc_category_id == undefined) 
							{
								temp_uc_category_id = '';
							}
							if (temp_uc_subcategory_id == undefined) 
							{
								temp_uc_subcategory_id = '';
							}
							if (temp_uc_dangerousness == undefined) 
							{
								temp_uc_dangerousness = '';
							}
							if (temp_uc_status == undefined) 
							{
								temp_uc_status = '';
							}

							@php
								$inputCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=uc_category_id[]"
								])->render(); 

								$inputSubCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=uc_subcategory_id[]"
								])->render();

								$inputDangerousness = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=uc_dangerousness[]"
								])->render();

								$inputStatus = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=uc_status[]"
								])->render();
							@endphp

							$inputCategory 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputCategory)!!}';
							$inputSubCategory 	= '{!!preg_replace("/(\r)*(\n)*/", "",$inputSubCategory)!!}';
							$inputDangerousness = '{!!preg_replace("/(\r)*(\n)*/", "",$inputDangerousness)!!}';
							$inputStatus 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputStatus)!!}';

							row_category 		= $($inputCategory);
							row_SubCategory 	= $($inputSubCategory);
							row_Dangerousness 	= $($inputDangerousness);
							row_Status 			= $($inputStatus);

							row_category.val(temp_uc_category_id);
							row_SubCategory.val(temp_uc_subcategory_id);
							row_Dangerousness.val(temp_uc_dangerousness);
							row_Status.val(temp_uc_status);

							$('#inputs').append(row_category, row_SubCategory, row_Dangerousness, row_Status);
							return true;
						});	

						$('.form-act').each(function(i,v)
						{
							category          = $(this).find('.id_category option:selected').val();
							div_category      = $(this).find('.ua_category');
							subcategory       = $(this).find('.subcategory option:selected').val();
							div_subcategory   = $(this).find('.ua_subcategory');
							dangerousness     = $(this).find('.dangerousness option:selected').val();
							div_dangerousness = $(this).find('.ua_dangerousness');
							status            = $(this).find('.status option:selected').val();
							div_status        = $(this).find('.ua_status');
							
							if(category == undefined)
							{
								category = '';
							}
							if(subcategory == undefined)
							{
								subcategory = '';
							}
							if(dangerousness == undefined)
							{
								dangerousness = '';
							}
							if(status == undefined)
							{
								status = '';
							}

							@php
								$inputCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=ua_category_id[]"
								])->render(); 

								$inputSubCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=ua_subcategory_id[]"
								])->render();

								$inputDangerousness = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=ua_dangerousness[]"
								])->render();

								$inputStatus = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=ua_status[]"
								])->render();
							@endphp

							$inputCategory 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputCategory)!!}';
							$inputSubCategory 	= '{!!preg_replace("/(\r)*(\n)*/", "",$inputSubCategory)!!}';
							$inputDangerousness = '{!!preg_replace("/(\r)*(\n)*/", "",$inputDangerousness)!!}';
							$inputStatus 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputStatus)!!}';

							row_category 		= $($inputCategory);
							row_SubCategory 	= $($inputSubCategory);
							row_Dangerousness 	= $($inputDangerousness);
							row_Status 			= $($inputStatus);

							row_category.val(category);
							row_SubCategory.val(subcategory);
							row_Dangerousness.val(dangerousness);
							row_Status.val(status);

							div_category.append(row_category);
							div_subcategory.append(row_SubCategory);
							div_dangerousness.append(row_Dangerousness);
							div_status.append(row_Status);
						});

						$('.form_practice').each(function(i,v)
						{
							temp_up_category_id		= $(this).find('[name="temp_up_category_id[]"] option:selected').val();
							temp_up_subcategory_id	= $(this).find('[name="temp_up_subcategory_id[]"] option:selected').val();
							temp_up_dangerousness	= $(this).find('[name="temp_up_dangerousness[]"] option:selected').val();
							temp_up_status			= $(this).find('[name="temp_up_status[]"] option:selected').val();

							if (temp_up_category_id== undefined) 
							{
								temp_up_category_id = '';
							}
							if (temp_up_subcategory_id== undefined) 
							{
								temp_up_subcategory_id = '';
							}
							if (temp_up_dangerousness== undefined) 
							{
								temp_up_dangerousness = '';
							}
							if (temp_up_status == undefined) 
							{
								temp_up_status = '';
							}

							@php
								$inputCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=up_category_id[]"
								])->render(); 

								$inputSubCategory = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=up_subcategory_id[]"
								])->render();

								$inputDangerousness = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=up_dangerousness[]"
								])->render();

								$inputStatus = view('components.inputs.input-text',[
									"attributeEx" => "type=hidden name=up_status[]"
								])->render();
							@endphp

							$inputCategory 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputCategory)!!}';
							$inputSubCategory 	= '{!!preg_replace("/(\r)*(\n)*/", "",$inputSubCategory)!!}';
							$inputDangerousness = '{!!preg_replace("/(\r)*(\n)*/", "",$inputDangerousness)!!}';
							$inputStatus 		= '{!!preg_replace("/(\r)*(\n)*/", "",$inputStatus)!!}';

							row_category 		= $($inputCategory);
							row_SubCategory 	= $($inputSubCategory);
							row_Dangerousness 	= $($inputDangerousness);
							row_Status 			= $($inputStatus);

							row_category.val(temp_up_category_id);
							row_SubCategory.val(temp_up_subcategory_id);
							row_Dangerousness.val(temp_up_dangerousness);
							row_Status.val(temp_up_status);

							$('#inputs').append(row_category, row_SubCategory, row_Dangerousness, row_Status);

						});
					}
					else if(flag ==false && flagForm != "nothing")
					{
						if(flagForm == "con")
						{
							$("#form-unsafe-practice").fadeOut();
							$("#form-unsafe-acts").fadeOut();
							$('#condition').prop("checked", true);

							$("#form-unsafe-condition").fadeIn().children('#new_form_condition').each(function() 
							{ 
								if($(this).children('.form_condition').children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.form_condition').children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.form_condition').children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});

							$("#form-unsafe-condition").fadeIn().children('.form_condition').each(function() 
							{ 
								if($(this).children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});
							addSelects();
							swal('','{{ Lang::get("messages.form_error") }}', 'error');
							return false;
						}
						else if(flagForm == "act") 
						{
							$("#form-unsafe-practice").fadeOut();
							$("#form-unsafe-condition").fadeOut();
							$('#acts').prop("checked", true);

							$("#form-unsafe-acts").fadeIn().children('#new_form_acts').each(function() 
							{ 
								if($(this).children('.form-act').children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.form-act').children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.form-act').children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});
							$("#form-unsafe-acts").fadeIn().children('.form-act').each(function() 
							{ 
								if($(this).children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});
							addSelects();
							swal('','{{ Lang::get("messages.form_error") }}', 'error');
							return false;
						}
						else if (flagForm == "pract") 
						{
							$("#form-unsafe-condition").fadeOut();
							$("#form-unsafe-acts").fadeOut();
							$('#practice').prop("checked", true);
							$("#form-unsafe-practice").fadeIn().children('#new_form_practice').each(function() 
							{ 
								if($(this).children('.form_practice').children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.form_practice').children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.form_practice').children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});
							$("#form-unsafe-practice").fadeIn().children('.form_practice').each(function() 
							{ 
								if($(this).children('.accordion-content').find('.form-error').length > 0)
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('hide').addClass('show').removeAttr("style");
								}
								else
								{
									$(this).children('.accordion-content').stop(true,true).removeClass('show').addClass('hide').attr("style","display: none;");
								}
							});
							addSelects();
							swal('','{{ Lang::get("messages.form_error") }}', 'error');
							return false;
						}
					}
				}
			});
		}
		function checkedAudit()
		{
			count_acts = $('.count_acts').length;
			count_practice = $('.count_unsafe_practice').length;
			count_unsafe = $('.count_unsafe_condition').length;

			if($('input[name="audit"]:checked').val() == "acts")
			{
				$("#form-unsafe-acts").fadeIn();
				$("#form-unsafe-practice").fadeOut();
				$("#form-unsafe-condition").fadeOut();
				count_acts = count_acts + 0;
				addSelects();		
			}
			else if($('input[name="audit"]:checked').val() == "practice")
			{
				$("#form-unsafe-practice").fadeIn();
				$("#form-unsafe-acts").fadeOut();
				$("#form-unsafe-condition").fadeOut();
				count_practice = count_practice + 0;
				addSelects();
				
			}
			else if($('input[name="audit"]:checked').val() == "condition")
			{
				$("#form-unsafe-condition").fadeIn();
				$("#form-unsafe-acts").fadeOut();
				$("#form-unsafe-practice").fadeOut();
				addSelects();
				count_unsafe = count_unsafe + 0;
			}
		}
	</script>
@endsection

