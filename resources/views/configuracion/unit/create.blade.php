@extends('layouts.child_module')

@section('data')
	@isset($unit)
		@component("components.forms.form", ["attributeEx" => "action=\"".route('unit.update',$unit->id)."\" id=\"unitForm\" method=\"POST\""])
		@slot("methodEx")
			PUT
		@endslot
	@else
		@component("components.forms.form", ["attributeEx" => "action=\"".route('unit.store')."\" id=\"unitForm\" method=\"POST\""])
	@endisset
		@component('components.labels.title-divisor') DATOS DE UNIDADES DE REQUISICIONES @endcomponent
		@component("components.labels.subtitle") Para {{ (isset($unit)) ? "editar la unidad" : "agregar una unidad nueva" }} es necesario colocar los siguientes campos: @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") Unidad: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text"
						name = "unit_name"
						data-validation = "server length"
						data-validation-length = "max150"
						data-validation-url = "{{ route('unit.validate') }}"
						placeholder = "Ingrese la unidad"
						@if(isset($unit))
							value = "{{ $unit->name }}"
							data-validation-req-params = "{{ json_encode(array('oldUnit'=>$unit->name)) }}"
						@endif
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor') DETALLES DE LA UNIDAD @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo de requisición:
				@endcomponent
				@php
					$options =  collect();
					foreach (App\RequisitionType::where('status',1)->get() as $rqType)
					{
						$catValue = ($rqType->warehouse->count() > 0 ? 1 : 0);
						$options = $options->concat([["value" => $rqType->id, "description" => $rqType->name, "attributeExOption" => "data-category=\"".$catValue."\""]]);
					}
				@endphp
				@component("components.inputs.select",["options" => $options, "attributeEx" => "id=\"requisitionType\"", "classEx" => "removeselect"]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Categoría:
				@endcomponent
				@component("components.inputs.select",["attributeEx" => "disabled multiple=\"multiple\" id=\"categorySelection\"", "classEx" => "removeselect"]) @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant" => "warning"]) 
					@slot("attributeEx")
						type = "button" 
						id = "addTypeCategory"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent
		@php
			$modelHead = ["Tipo de requisición", "Categoría", "Acción"];
			$modelBody = [];
			$style = 'style="display:none !important;"';
		@endphp
		@isset($unit)
			@php
				$modelHead = ["Tipo de requisición", "Categoría", "Acción"];
				foreach($unit->category_rq as $crq)
				{
					$body =
					[
						"classEx" => "tr",
						[
							"content" => 
							[
								[
									"kind"  => "components.labels.label",
									"label" => $crq->rq_type->name
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" value=\"$crq->rq_id\" name=\"rqType[]\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"  => "components.labels.label",
									"label" => !empty($crq->category_id) ? $crq->category->description : ''
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" value=\"$crq->category_id\" name=\"category[]\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx"	=> "type=\"button\"",							
									"label"			=> "<span class=\"icon-x delete-span\"></span>",
									"variant"		=> "red",
									"classEx"		=> "delete-type-category"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
				$style= "";
			@endphp
		@endisset
		@AlwaysVisibleTable(["modelHead" => $modelHead, "modelBody" => $modelBody, "variant" => "default", "attributeEx" => "id=\"rqTypeCategoryTable\" $style ", "attributeExBody" => "id=\"rqTypeCategoryBody\""]) @endAlwaysVisibleTable
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@isset($unit)
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
					@endslot
					Actualizar
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
			@else
				@component('components.buttons.button', ["variant"=>"primary"])
					@slot('attributeEx')
						type = "submit"
						name = "send"
					@endslot
					Registrar
				@endcomponent
				@component("components.buttons.button", ["classEx" => "btn-delete-form", "variant" => "reset"]) 
					@slot("attributeEx")
						type = "reset" 
						name = "borrar"
					@endslot
					Borrar campos
				@endcomponent
			@endisset
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		function validate()
		{
			$.validate(
			{
				form		: '#unitForm',
				modules		: 'security',
				onSuccess	: function($form)
				{		
					if($('[name="rqType[]"]').length == 0)
					{
						swal('', 'Debe ingresar al menos una combinación de tipo de requisición y categoría.', 'error');
						return false;
					}					
				},
				onError: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				}
			});
		}
		$(document).ready(function()
		{
			validate();
			generalSelect({'selector':'#categorySelection', 'depends':'#requisitionType', 'model': 8});
			@php
				$selects = collect([
					[
						"identificator"          => "#requisitionType", 
						"placeholder"            => "Seleccione el tipo", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			$(document).on('change','#requisitionType',function()
			{
				$('#categorySelection').html('');
				if($(this).val() == '')
				{
					$('#categorySelection').prop('disabled',true);
				}
				else
				{
					if($(this).find('option:selected').attr('data-category') == 1)
					{
						$('#categorySelection').prop('disabled',false);
					}
					else
					{
						$('#categorySelection').prop('disabled',true);
					}
				}
			})
			.on('click','#addTypeCategory',function()
			{
				rqType           = $('#requisitionType').val();
				requiredCategory = false;
				category         = $('#categorySelection').val();
				if(!$('#categorySelection').is(':disabled') && category == '')
				{
					swal('','Por favor seleccione una categoría','error');
				}
				else if(rqType == '')
				{
					swal('','Por favor seleccione un tipo de requisición','error');
				}
				else
				{
					exists = false;
					$('[name="rqType[]"]').each(function(i,v)
					{
						if($(this).val() == rqType && $($('[name="category[]"]')[i]).val() == category)
						{
							swal('','La combinación de tipo de requisición y categoría ya se encuentra agregada.','error');
							exists = true;
							return false;
						}
					});
					if(!exists)
					{
						@php
							$modelHead = ["Tipo de requisición", "Categoría", "Acción"];
							$modelBody = [];
							$modelBody[] = 
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind"	=> "components.labels.label",
											"classEx" 	=> "class_name"
										],
										[
											"kind"	=> "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"rqType[]\""
										]
									]
									
								],
								[
									"content" => 
									[
										[
											"kind"		=> "components.labels.label",
											"classEx" 	=> "class_category"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"category[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"classEx"		=> "delete-type-category",
											"attributeEx"	=> "type=\"button\"",
											"label"			=> "<span class=\"icon-x\"></span>",
											"variant" 		=> "red"
										]
									]
								]
							];
							$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead" 	=> true,
									"variant" 	=> "default"
								])->render();
						@endphp
						reqName = $('#requisitionType option:selected').text();
						reqCategory = $('#categorySelection option:selected').text();
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						$("#rqTypeCategoryTable").removeAttr('style');
						req = $(table);
						req = rowColor('#rqTypeCategoryBody', req);
						req.find(".class_name").text(reqName);
						req.find("[name='rqType[]']").val(rqType);
						req.find(".class_category").text(reqCategory);
						req.find("[name='category[]']").val(category);
						$("#rqTypeCategoryTable").removeClass("hidden");
						$('#rqTypeCategoryBody').append(req);
						$('#requisitionType').val('').trigger('change');
						$('#categorySelection').html('');
						$('#categorySelection').prop('disabled',true);
					}
				}
			})
			.on('click','.delete-type-category',function()
			{
				del = $(this).parents('.tr');
				swal({
					title     : "Confirmación",
					text      : "¿Confirma que desea eliminar el tipo de requisición/categoría de la unidad?",
					icon      : "warning",
					buttons   : ["Cancelar","OK"],
					dangerMode: true,
				})
				.then((willDelete) =>
				{
					if (willDelete)
					{
						del.remove();
					}
				});
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
						$('#rqTypeCategoryBody').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
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