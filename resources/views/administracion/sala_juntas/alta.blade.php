@extends('layouts.child_module')
@section('data')
	@php
		$attributeEx = "action=\"".(isset($boardroom) ? route('boardroom.save') : route('boardroom.store'))."\" method=\"POST\" id=\"container-alta\"";
	@endphp
	@component("components.forms.form", ["attributeEx"=>$attributeEx, "files" => true])
		@isset($boardroom)
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden"
					name="br" 
					value="{{ $boardroom->id }}"
				@endslot		
			@endcomponent
		@endisset
		@component("components.labels.title-divisor") INFORMACIÓN GENERAL @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Nombre: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="name" 
						placeholder="Ingrese el nombre"
						@isset($boardroom) value="{{ $boardroom->name }}" @endisset
						data-validation="required"
					@endslot
					@slot("classEx")
						remove
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Ubicación/Inmueble: @endcomponent
				@php
					$options = collect();
					foreach(App\Property::all() as $p)
					{
						if(isset($boardroom) && $boardroom->property_id == $p->id)
						{
							$options = $options->concat([["value"=>$p->id, "selected"=>"selected", "description"=>$p->property]]);
						}
						else
						{
							$options = $options->concat([["value"=>$p->id, "description"=>$p->property]]);
						}
					}
					$attributeEx = "name=\"property_id\" data-valitation=\"required\" multiple=\"multiple\"";
					$classEx = "removeselect";
				@endphp				
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Empresa: @endcomponent
				@php
					$options = collect();
					foreach($enterprises as $enterprise)
					{
						$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
						if(isset($boardroom) && $boardroom->enterprise_id == $enterprise->id)
						{
							$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
						}
					}
					$attributeEx = "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises select2-selection--multiple\" data-validation=\"required\"";
					$classEx = "js-enterprises removeselect";
				@endphp				
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Capacidad máxima de personas: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="max_capacity"
						placeholder="Ingrese la capacidad"
						data-validation="required number" 
						@isset($boardroom) value="{{ $boardroom->max_capacity }}" @endisset
					@endslot
					@slot('classEx')	
						remove
						max_capacity
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="description" 
						rows="5"
						placeholder="Ingrese una descripción"
					@endslot
					@slot('classEx')
						remove
					@endslot
					@isset($boardroom){{ $boardroom->description }}@endisset
				@endcomponent
			</div>
		@endcomponent

		@component("components.labels.title-divisor") ELEMENTO DE SALA @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label") Cantidad: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						placeholder="Ingrese la cantidad"
						id="quantity"
					@endslot
					@slot("classEx")
						quanty
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Elemento: @endcomponent
				@php
					$options = collect();
					foreach(App\CatElements::all() as $element)
					{
						$options = $options->concat([["value"=>$element->id, "description"=>$element->name]]);
					}
					$attributeEx = "id=\"element\" multiple=\"multiple\"";
					$classEx = "form-control js-element removeselect";
				@endphp				
				@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
				@endcomponent						
			</div>
			<div class="col-span-2 hidden" id="element_description_container">
				@component("components.labels.label") Descripción: @endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						id="element_description"
						placeholder="Ingrese la descripción"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button", ["variant"=>"warning"])
					@slot("attributeEx")
						type="button"
						name="add"
						id="add_element"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
		@endcomponent

		@php
			$modelHead = 
			[
				[
					["value" => "#"],
					["value" => "Cantidad"],
					["value" => "Elemento"],
					["value" => "Acción"]
				]
			];

			$modelBody = [];
			if(isset($boardroom))
			{
				foreach($boardroom->elements as $index => $el)
				{	
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "countElement",
									"label" => $index+1,
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label" => $el->quantity,
									"classEx" => "t-quantity",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "element_id",
									"attributeEx" => "type=\"hidden\" value=\"".$el->id."\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "quantity",
									"attributeEx" => "type=\"hidden\" value=\"".$el->quantity."\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "element",
									"attributeEx" => "type=\"hidden\" value=\"".$el->element_id."\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "element_description",
									"attributeEx" => "type=\"hidden\" value=\"".htmlentities($el->element_description)."\"",
								],						
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"  => "components.labels.label",
									"classEx" => "name-element",
									"label" => $el->element_id == 6 ? htmlentities($el->description) : $el->element,
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.buttons.button", 
									"classEx" => "edit-item",
									"attributeEx" => "id=\"edit\" type=\"button\"",
									"variant" => "success",
									"label" => "<span class=\"icon-pencil\"></span>",
								],
								[
									"kind" => "components.buttons.button", 
									"classEx" => "delete-art",
									"attributeEx" => "id=\"cancel\" type=\"button\"",
									"variant" => "red",
									"label" => "<span class=\"icon-x\"></span>",
								],
							],
						],
					];
				}
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot("attributeEx")
				id="table"
			@endslot
			@slot("attributeExBody")
				id="body"
			@endslot
		@endcomponent
		<div id="deleteElements"></div>

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
				GUARDAR
			@endcomponent
			@if(isset($boardroom))
				@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
					@slot("classEx")
						load-actioner
						text-center
						w-48 
						md:w-auto
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
			@else
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
			@endif
		</div>
	@endcomponent
@endsection 
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">		
		$(document).ready(function()
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
					$('.form-error').remove();
					if($('.countElement').length == 0)
					{
						swal('', 'Por favor ingrese al menos un elemento en la sala.', 'error');
						return false
					}
					else if($('.max_capacity').val() == 0)
					{
						$('.max_capacity').removeClass('valid').addClass('error').css("border-color","rgb(204, 4, 4)");
						$('.max_capacity').parent().append('<span class="help-block form-error">No puede ser 0</span>');
						swal('', 'Por favor verifique la capacidad máxima de personas.', 'error');
						return false
					}
					else
					{
						swal('Cargando',{
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc         	: false
						});
						return true;
					}
				}
			});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"        	 => "[name=\"property_id\"]", 
						"placeholder"            => "Seleccione la ubicación",
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"          => ".js-element", 
						"placeholder"            => "Seleccione un elemento",
						"maximumSelectionLength" => "1",
					],
				]);
			@endphp
			@component('components.scripts.selects',['selects'=>$selects, 'noDocumentReady'=>true]) @endcomponent
			$('#quantity,.max_capacity').numeric({negative:false, decimal:false});

			$(document).on('click','#add_element',function()
			{
				$('#remove_error_elements').find('.form-error').remove();
				quantity            = $("#quantity").val();
				element             = $("#element option:selected").val();
				nameElement 		= $('#element option:selected').text().trim();
				element_description = $("#element_description").val();
				countElement 		= $('.countElement').length + 1;

				if(quantity == '')
				{
					swal("","Por favor ingrese la cantidad.","error");
					$("#quantity").parent().append('<span class="help-block form-error">No puede quedar vacío</span>');
					return false;
				}
				if(parseInt(quantity) == 0)
				{
					swal("","La cantidad debe ser mayor a 0.","error");
					$("#quantity").parent().append('<span class="help-block form-error">No puede ser 0</span>');
					return false;
				}
				if(element == null)
				{
					swal("","Debe seleccionar un elemento.","error");
					$("#element").parent().append('<span class="help-block form-error">Seleccione un elemento</span>');
					return false;
				}				
				if(element == 6)
				{
					if(element_description.trim() == '')
					{
						swal("","Por favor ingrese la descripción.","error");
						$("#element_description").parent().append('<span class="help-block form-error">No puede quedar vacío</span>');
						return false;
					}
					else
					{
						nameElement = element_description;
					}
				}

				@php
					$modelHead = 
					[
						[
							["value" => "#"],
							["value" => "Cantidad"],
							["value" => "Elemento"],
							["value" => "Acción"],
						]
					];
					$modelBody = [];
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "countElement",
									"label"   => "",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "t-quantity",
									"label"   => "",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "quantity",
									"attributeEx" => "type=\"hidden\" name=\"quantity[]\" value=\"\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "element",
									"attributeEx" => "type=\"hidden\" name=\"element[]\" value=\"\"",
								],
								[
									"kind" => "components.inputs.input-text",
									"classEx" => "element_description",
									"attributeEx" => "type=\"hidden\" name=\"element_description[]\" value=\"\"",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind"  => "components.labels.label",
									"classEx" => "name-element",
									"label" => "",
								],
							],
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.buttons.button", 
									"classEx" => "edit-item",
									"attributeEx" => "id=\"edit\" type=\"button\"",
									"variant" => "success",
									"label" => "<span class=\"icon-pencil\"></span>",
								],
								[
									"kind" => "components.buttons.button", 
									"classEx" => "delete-art",
									"attributeEx" => "id=\"cancel\" type=\"button\"",
									"variant" => "red",
									"label" => "<span class=\"icon-x\"></span>",
								],
							],
						],
					];
					$table = view("components.tables.table",[
						"modelHead" 	  => $modelHead,
						"modelBody" 	  => $modelBody,
						"themeBody" 	  => "striped",
						"attributeExBody" => "id=\"body\"", 
						"noHead"		  => "true"
					])->render();
					$table2 = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				tr_table = $(table);
				tr_table.find(".countElement").text(countElement);
				tr_table.find(".t-quantity").text(Number(quantity));
				tr_table.find(".quantity").val(Number(quantity));
				tr_table.find(".element").val(element);
				tr_table.find(".element_description").val(element_description);
				tr_table.find(".name-element").text(nameElement);
				$('#body').append(tr_table);
				$("#quantity").val(null);
				$("#element_description").val(null);
				$("#element").val(null).trigger('change');
				$('.edit-item').removeAttr('disabled',true);
			})
			.on('click','.edit-item',function()
			{
				tr = $(this).parents('.tr');
				$('.edit-item').attr('disabled',true);
				quantity            = tr.find('.quantity').val();
				element             = tr.find('.element').val();
				nameElement 		= tr.find('.name-element').text().trim();
				
				element_id   = tr.find('.element_id').val();

				if(element_id != null)
				{
					$('#deleteElements').append("<input type='hidden' name='deleteElements[]' value='"+element_id+"'/>");
				}
				if(element == 6)
				{
					$('#element_description_container').show();
					$('#element_description').val(nameElement);
				}
				else
				{
					$('#element_description').val("");
					$('#element_description_container').hide();
				}
				
				$("#quantity").val(quantity);
				$("#element").val(element).trigger('change');

				tr.remove();
				$('.countElement').each(function(i,v)
				{
					$(this).html(i+1);
				});
			})
			.on('click','.delete-art',function(){
				
				tr = $(this).parents('.tr');

				elemen_id = tr.find('.element_id').val();

				if(elemen_id != null)
				{
					$('#deleteElements').append("<input type='hidden' name='deleteElements[]' value='"+elemen_id+"'/>")
				}

				tr.remove();
				$('.countElement').each(function(i,v)
				{
					$(this).html(i+1);
				});
			})
			.on('change','.js-element',function () 
			{
				if($(this).find('option:selected').val() == 6)
				{
					$('#element_description_container').slideDown().show();
				}
				else
				{
					$('#element_description_container').slideUp();
				}
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Desea limpiar su formulario?",
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
						$('.remove').val('');
						$('.removeselect').val(null).trigger('change');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on("focusout",".max_capacity, #quantity",function()
			{
				if($.isNumeric($(this).val()) == false)
				{
					$(this).val(null);
				}
			});
		});	
	</script>
@endsection
