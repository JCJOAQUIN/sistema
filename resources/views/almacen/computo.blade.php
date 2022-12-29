@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('warehouse.computer.store')."\"", "files" => true])
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS GENERALES"]) @endcomponent
		@component('components.containers.container-form', ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
					{
						$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises removeselect", "attributeEx" => "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Cuenta:"]) @endcomponent
				@component('components.inputs.select', ["classEx" => "js-accounts removeselect", "attributeEx" => "name=\"account_id\" multiple=\"multiple\" id=\"multiple-accounts\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Ubicación/Sede:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\Place::where('status',1)->get() as $place)
					{
						$options	=	$options->concat([["value"	=>	$place->id,	"description"	=>	$place->place]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-places", "attributeEx" => "name=\"place_id\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"date\" id=\"datepicker\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" readonly=\"true\""]) @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DETALLES DE EQUIPO"]) @endcomponent
		@component('components.containers.container-form', ["attributeEx" => "container-data"])
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo:"]) @endcomponent
				<div class="flex space-x-2">
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type" id="smartphone" value="1" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Smartphone
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type" id="tablet" value="2" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Tablet
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type" id="laptop" value="3" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Laptop
						@endcomponent
					</div>
					<div>
						@component('components.buttons.button-approval')
							@slot('attributeEx')
								type="radio" name="type" id="desktop" value="4" data-validation="checkbox_group" data-validation-qty="min1"
							@endslot
							Desktop
						@endcomponent
					</div>
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Marca:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"brand\" placeholder=\"Ingrese la marca\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Capacidad de Almacenamiento:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"storage\" placeholder=\"Ingrese la capacidad\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Procesador:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"processor\" placeholder=\"Ingrese el procesador\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Memoria RAM:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "", "attributeEx" => "type=\"text\" name=\"ram\" placeholder=\"Ingrese la memoria RAM\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "SKU:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"sku\" placeholder=\"Ingrese el sku\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Cantidad:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "quantity", "attributeEx" => "type=\"text\" name=\"quantity\" placeholder=\"Ingrese la cantidad\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Precio unitario:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "amount", "attributeEx" => "type=\"text\" name=\"amount\" placeholder=\"Ingrese el precio\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo de IVA:"]) @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_no" value="no" title="No IVA" checked=""
							@if(isset($requests) && $requests->taxPayment == 0) disabled @endif
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_a" value="a" title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
						@endslot
						A
					@endcomponent
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_b" value="b" title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
						@endslot
						B
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Importe:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "readonly type=\"text\" name=\"fimporte\" placeholder=\"Ingrese el importe\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Comentario:"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "placeholder=\"Ingrese los comentarios\" name=\"commentaries\" id=\"commentaries\""]) @endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"add\" id=\"add\"", "classEx" => "add2", "label" => "<span class=\"icon-plus\"></span> Agregar a lista"]) @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "LISTA DE EQUIPOS A REGISTRAR"]) @endcomponent
		@php
			$modelBody	=	[];
			$modelHead	=
			[
				["value"	=>	"Tipo",		"show"	=>	"true"],
				["value"	=>	"Cantidad",	"show"	=>	"true"],
				["value"	=>	"Marca",	"show"	=>	"true"],
				["value"	=>	"Almacenamiento"],
				["value"	=>	"Procesador"],
				["value"	=>	"RAM"],
				["value"	=>	"SKU"],
				["value"	=>	"P. unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Importe"],
				["value"	=>	"Acciones"],
			];
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeEx" => "id=\"table\"", "attributeExBody" => "id=\"body\"", "classEx" => "mt-4"])@endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Subtotal:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"attributeEx"	=>	"name=\"subtotal_articlesLabel\""]]],
				["label"	=>	"Iva:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"attributeEx"	=>	"name=\"iva_articlesLabel\""]]],
				["label"	=>	"Total:",		"inputsEx"	=>
					[
						["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"attributeEx"	=>	"name=\"total_articlesLabel\""],
						["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"total_articles\" type=\"text\""]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable, "classEx"	=>	"articlesTotals"])@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\"  name=\"enviar\" value=\"ENVIAR\"",		"classEx" => "enviar",			"label" => "ENVIAR"]) @endcomponent
			@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "type=\"reset\" name=\"borra\" value=\"BORRAR CAMPOS\"",	"label" => "BORRAR CAMPOS"]) @endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		function validate()
		{
			$.validate(
			{
				form: '#container-alta',
				modules		: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					total          = parseFloat($('input[name="total"]').val());
					total_articles = parseFloat($('input[name="total_articles"]').val());
					countbody      = $('#body .tr').length;
					if(total_articles == "" || countbody <= 0)
					{
						swal({
							title: "Error",
							text: "Debe agregarse al menos un artículo.",
							icon: "error",
							buttons: 
							{
								confirm: true,
							},
						});
						return false;
					}
					else if (total_articles > total)
					{
						swal({
							title: "Error",
							text: "La inversión de artículos es mayor a la inversión total.",
							icon: "error",
							buttons: 
							{
								confirm: true,
							},
						});
						return false;
					}
					else
					{
						return true;
					}
				}
			});
		}
		$(document).ready(function() 
		{
			validate();
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType': "computo"});
			countbody = $('#body.tr').length;
			if (countbody <= 0) 
			{
				$('#table').hide('');
				$('.articlesTotals').hide('');
			}
			else
			{
				$('#table').show('');
				$('.articlesTotals').show('');
			}
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprises", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-places", 
						"placeholder"            => "Seleccione la ubicación/sede", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-material", 
						"placeholder"            => "Seleccione la ubicación/sede", 
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
		});
		$('.quantity').numeric({ negative : false, decimal : false });
		$('.inversion, .amount').numeric({ negative : false, altDecimal: ".", decimalPlaces: 2 });
		$(function()
		{
			$('#datepicker').datepicker({ dateFormat:'dd-mm-yy' });
		});
		$(document).on('change','.quantity,.amount,.iva_kind',function()
		{
			cant         = $('input[name="quantity"]').val();
			precio       = $('input[name="amount"]').val();
			iva          = ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2         = ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			totalImporte = cant * precio;
			switch($('input[name="iva_kind"]:checked').val())
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = cant*precio*iva;
					break;
				case 'b':
					ivaCalc = cant*precio*iva2;
					break;
			}
			totalImporte    = ((cant * precio)+ivaCalc);
			$('input[name="fimporte"]').val(totalImporte.toFixed(2));
		})
		.on('click','#add',function()
		{
			cant		= $('input[name="quantity"]').val().trim();
			brand 		= $('input[name="brand"]').val().trim();
			storage 	= $('input[name="storage"]').val().trim();
			processor 	= $('input[name="processor"]').val().trim();
			ram  		= $('input[name="ram"]').val().trim();
			sku	 		= $('input[name="sku"]').val().trim();
			amountUnit 	= $('input[name="amount"]').val().trim();
			comm		= $('textarea[id="commentaries"]').val().trim();
			type 		= $('input[type="radio"]:checked').val();
			amount 		= $('input[name="fimporte"]').val();
			iva_kind = $('input[name="iva_kind"]:checked').val()
			ivaCalc = 0
			switch(iva_kind)
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = cant*amountUnit*iva;
						break;
					case 'b':
						ivaCalc = cant*amountUnit*iva2;
						break;
				}
			sub_total = (Number(amountUnit) * Number(cant))
			nameType = "";
			if(!$("input[name='type']").is(':checked'))
			{
				swal({
					title: "Error",
					text: "Debe seleccionar el tipo.",
					icon: "error",
					buttons: 
					{
						confirm: true,
					},
				});
				return;
			}
			if (type == 1) 
			{
				nameType = "Smartphone";
			}
			if (type == 2) 
			{
				nameType = "Tablet";
			}
			if (type == 3) 
			{
				nameType = "Laptop";
			}
			if (type == 4) 
			{
				nameType = "Desktop";
			}
			if (comm == "") 
			{
				comm = "Sin comentarios";
			}
			if (sku == "") 
			{
				sku = "Sin SKU";
			}
			if (cant == "" || brand == "" || storage == "" || processor == "" || ram == "" || amountUnit == "")
			{
				if (cant == "") 
				{
					$('input[name="quantity"]').addClass('error');
				} 
				if(brand == "")
				{
					$('input[name="brand"]').addClass('error');
				}
				if(storage == "")
				{
					$('input[name="storage"]').addClass('error');
				}
				if(processor == "")
				{
					$('input[name="processor"]').addClass('error');
				}
				if(ram == "")
				{
					$('input[name="ram"]').addClass('error');
				}
				if (amountUnit == "") 
				{
					$('input[name="amount"]').addClass('error');
				} 
				swal({
					title: "Error",
					text: "Debe ingresar todos los campos requeridos.",
					icon: "error",
					buttons: 
					{
						confirm: true,
					},
				});
			}
			else if(cant == 0)
			{
				swal('','La cantidad no puede ser cero.','error');
				$('input[name="quantity"]').addClass('error');
			}
			else if(amountUnit == 0)
			{
				swal('','El precio unitario no puede ser cero.','error');
				$('input[name="amount"]').addClass('error');
			}
			else
			{
				@php
					$modelBody	=	[];
					$modelHead	=
					[
						["value"	=>	"Tipo",		"show"	=>	"true"],
						["value"	=>	"Cantidad",	"show"	=>	"true"],
						["value"	=>	"Marca",	"show"	=>	"true"],
						["value"	=>	"Almacenamiento"],
						["value"	=>	"Procesador"],
						["value"	=>	"RAM"],
						["value"	=>	"SKU"],
						["value"	=>	"P. unitario"],
						["value"	=>	"IVA"],
						["value"	=>	"Importe"],
						["value"	=>	"Acciones"],
					];
					$modelBody	=
					[
						[

							[
								"show"		=>	"true",
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"ttypeLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"ttype"		,	"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"ttype[]\""],
								]
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tquantyLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tquanty",		"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tquanty[]\""],
								]
							],
							[
								"show"		=>	"true",
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tbrandLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tbrand",		"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tbrand[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tstorageLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tstorage",			"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tstorage[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tprocessorLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tprocessor",		"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tprocessor[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tramLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tram",			"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tram[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tskuLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tsku",			"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tsku[]\""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tdescr",		"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tcommentaries[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tunitatioLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tunitatio",		"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamountunit[]\""]
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"tivaLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tiva",			"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva[]\""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tiva_kind",	"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tiva_kind[]\""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"tsub_total",	"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tsub_total[]\""],
								]
							],
							[
								"content"	=>
								[
									["kind"	=>	"components.labels.label",		"classEx"	=>	"timporteLabel",	"label"			=>	""],
									["kind"	=>	"components.inputs.input-text",	"classEx"	=>	"timporte",			"attributeEx"	=>	"readonly=\"true\" type=\"hidden\" name=\"tamount[]\""],
								]
							],
							[
								"classEx"	=>	"md:flex md:inline-flex",
								"content"	=>
								[
									["kind"	=>	"components.buttons.button",	"variant"	=>	"success",	"classEx"	=>	"edit-item",	"label"	=>	"<span class=\"icon-pencil\"></span>",	"attributeEx"	=>	"id=\"edit\" type=\"button\""],
									["kind"	=>	"components.buttons.button",	"variant"	=>	"red",		"classEx"	=>	"delete-item",	"label"	=>	"<span class=\"icon-x delete-span\"></span>"],
								]
							],
						]
					];
					$table = view('components.tables.table',[
							"modelHead" 	  => $modelHead,
							"modelBody" 	  => $modelBody,
							"attributeExBody" => "id=\"body\"", 
							"noHead"		  => "true"
						])->render();
				@endphp
				table_row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				table=$(table_row);
				table.find('.ttypeLabel').text(nameType);
				table.find('.ttype').val(type);
				table.find('.tquantyLabel').text(cant);
				table.find('.tquanty').val(cant);
				table.find('.tbrandLabel').text(brand);
				table.find('.tbrand').val(brand);
				table.find('.tstorageLabel').text(storage);
				table.find('.tstorage').val(storage);
				table.find('.tprocessorLabel').text(processor);
				table.find('.tprocessor').val(processor);
				table.find('.tramLabel').text(ram);
				table.find('.tram').val(ram);
				table.find('.tskuLabel').text(sku);
				table.find('.tsku').val(sku);
				table.find('.tdescr').val(comm);
				table.find('.tunitatioLabel').text(Number(amountUnit).toFixed(2));
				table.find('.tunitatio').val(Number(amountUnit).toFixed(2));
				table.find('.tivaLabel').text(Number(ivaCalc).toFixed(2));
				table.find('.tiva').val(Number(ivaCalc).toFixed(2));
				table.find('.tiva_kind').val(iva_kind);
				table.find('.tsub_total').val(sub_total);
				table.find('.timporteLabel').text(Number(amount).toFixed(2));
				table.find('.timporte').val(Number(amount).toFixed(2));
				$('#body').append(table);
				$('input[name="quantity"]').val('');
				$('input[name="brand"]').val('');
				$('input[name="storage"]').val('');
				$('input[name="processor"]').val('');
				$('input[name="ram"]').val('');
				$('input[name="sku"]').val('');
				$('input[name="fimporte"]').val('');
				$('input[name="amount"]').val('');
				$('textarea[id="commentaries"]').val('');
				$('input[type="radio"]').prop('checked', false);
				$('input:radio[name=iva_kind]').filter('[value="no"]').prop('checked', true);
				$('input[name="quantity"]').removeClass('error');
				$('input[name="brand"]').removeClass('error');
				$('input[name="storage"]').removeClass('error');
				$('input[name="processor"]').removeClass('error');
				$('input[name="ram"]').removeClass('error');
				$('input[name="amount"]').removeClass('error');
				countbody = $('#body .tr').length;
				if (countbody <= 0) 
				{
					$('#table').hide();
					$('.articlesTotals').hide('');
				}
				else
				{
					$('#table').show();
					$('.articlesTotals').show('');
				}
				totalArticles();
				$('.edit-item').attr('disabled', false);
			}
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('.tr').remove();
			countbody = $('#body .tr').length;
			if (countbody <= 0) 
			{
				$('#table').hide();
			}
			totalArticles();
			$('.edit-item').attr('disabled', false);
			$('input:radio[name=iva_kind]').filter('[value="no"]').prop('checked', true);
		})
		.on('click','.edit-item',function()
		{
			$('input[name="quantity"]').removeClass('error');
			$('input[name="brand"]').removeClass('error');
			$('input[name="storage"]').removeClass('error');
			$('input[name="processor"]').removeClass('error');
			$('input[name="ram"]').removeClass('error');
			$('input[name="amount"]').removeClass('error');
			nameType   = $(this).parents('.tr').find('.ttype').val();
			cant       = $(this).parents('.tr').find('.tquanty').val();
			brand      = $(this).parents('.tr').find('.tbrand').val();
			storage    = $(this).parents('.tr').find('.tstorage').val();
			processor  = $(this).parents('.tr').find('.tprocessor').val();
			ram        = $(this).parents('.tr').find('.tram').val();
			sku        = $(this).parents('.tr').find('.tsku').val();
			amountUnit = $(this).parents('.tr').find('.tunitatio').val();
			comm       = $(this).parents('.tr').find('.tdescr').val();
			ivaKind    = $(this).parents('.tr').find('.tiva_kind').val();
			total      = $(this).parents('.tr').find('.timporte').val();
			$('input[name="quantity"]').val(cant);
			$('input[name="brand"]').val(brand);
			$('input[name="storage"]').val(storage);
			$('input[name="processor"]').val(processor);
			$('input[name="ram"]').val(ram);
			$('input[name="sku"]').val(sku);
			$('input[name="amount"]').val(amountUnit);
			$('input[name="fimporte"]').val(total);
			$('textarea[id="commentaries"]').val(comm);
			radios = $('input:radio[name=type]');
			radios.filter('[value='+nameType+']').prop('checked', true);
			$('input:radio[name=iva_kind]').filter('[value='+ivaKind+']').prop('checked', true);
			$(this).parents('.tr').remove();
			$('.edit-item').attr('disabled', true);
			totalArticles();
		})
		.on('change','.js-enterprises',function()
		{
			$enterprise = $(this).val();
			$('.js-accounts').empty();
			generalSelect({'selector':'.js-accounts', 'depends':'.js-enterprises', 'model':57, 'warehouseType': "computo"});
		})
		.on('click','[name="borra"]',function(e)
		{
			e.preventDefault();
			swal({
				text		: "Al borrar se eliminarán los equipos ya agregados en la lista. \n¿Desea continuar?",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((continuar) =>
			{
				if(continuar)
				{
					$('#body').html('');
					$('#table').hide();
					$('.removeselect').val(null).trigger('change');
					$('#container-alta')[0].reset();
					totalArticles();
				}
				else
				{
					swal.close();
				}
			});
		});
		function totalArticles()
		{
			var sumatotal = 0;
			var calcSubtotal = 0;
			var calcIva = 0;
			$("#body .tr").each(function(i, v)
			{
				tempQ			= $(this).find('.tquanty').val();
				tempP			= $(this).find('.tunitatio').val();
				calcSubtotal	+= (Number(tempQ)*Number(tempP));
				calcIva			+= Number($(this).find('.tiva').val());
			});
			$(".timporte").each(function(i, v)
			{
				valor = parseFloat($(this).val());
				sumatotal = sumatotal + valor ;
			});
			$('[name="total_articlesLabel"]').text('$ '+Number(sumatotal).toFixed(2));
			$('[name="total_articles"]').val(sumatotal);
			$('[name="subtotal_articlesLabel"]').text('$ '+Number(calcSubtotal).toFixed(2));
			$('[name="iva_articlesLabel"]').text('$ '+Number(calcIva).toFixed(2));
		}
	</script>
@endsection
