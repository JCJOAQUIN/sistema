@extends('layouts.child_module')

@section('data')
	@component('components.forms.form', ["attributeEx"	=>	"method=\"POST\" id=\"container-alta\" action=\"".route('warehouse.edit_send')."\"", "files" => true])
		@component('components.inputs.input-text', ["classEx" => "hidden", "attributeEx" => "name=\"idwarehouse\" value=\"".$warehouse->idwarehouse. "\""]) @endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DETALLES DE LOTE"]) @endcomponent
		@component('components.containers.container-form', ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\Enterprise::orderName()->get() as $enterprise)
					{
						if ($warehouse->lot->idEnterprise == $enterprise->id)
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-enterprises removeselect", "attributeEx" => "name=\"enterprise_id\" multiple=\"multiple\" id=\"multiple-enterprises select2-selection--multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha:"]) @endcomponent
				@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"date\" id=\"datepicker\" placeholder=\"Ingrese la Fecha\" data-validation=\"required\" readonly=\"true\" value=\"".($warehouse->lot->date !="" ? Carbon\Carbon::createFromFormat('Y-m-d',$warehouse->lot->date)->format('d-m-Y') : null)."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Sub Total de Factura/Ticket:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove inversion", "attributeEx" => "type=\"text\" id=\"sub_total\" name=\"sub_total_masiva\" data-validation=\"required\" placeholder=\"Ingrese el subtotal\" value=\"".($warehouse->subtotal !="" ? $warehouse->subtotal : 0)."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Total de Factura/Ticket:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove inversion", "attributeEx" => "type=\"text\" name=\"total\" data-validation=\"required\" placeholder=\"Ingrese el total\" value=\"".($warehouse->lot->total !="" ? $warehouse->lot->total : 0)."\""]) @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CARGAR TICKET/FACTURA"]) @endcomponent
		<div id="delete_documents"></div>
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["Documento", "Acción"];
			foreach($warehouse->lot->documents()->where('status',1)->get() as $doc)
			{
				$body	=
				[
					[
						"content"	=>
						[
							["kind"	=>	"components.buttons.button",	"attributeEx"	=>	"target=\"_blank\" href=\"".url('docs/warehouse/'.$doc->path)."\"",	"variant"	=>	"secondary",	"buttonElement"	=>	"a", "label"	=>	"Archivo"],
							["kind"	=>	"components.inputs.input-text", "attributeEx"	=>	"value=\"".$doc->iddocumentsWarehouse."\"", "classEx"	=>	"hidden id_doc"],
						],
					],
					[
						"content"	=>
						[
							["kind"	=>	"components.buttons.button", "attributeEx"	=>	"type=\"button\"", "classEx"	=>	"old_delete", "variant"	=>	"red", "label"	=>	"<span class=\"icon-x delete-span\"></span>"]
						],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@component('components.containers.container-form')
			<div id="documents" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden"> </div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left">
				@component('components.buttons.button', ["variant" => "warning", "attributeEx" => "type=\"button\" name=\"addDoc\" id=\"addDoc\"", "label" => "<span class=\"icon-plus\"></span> Agregar documento"]) @endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DETALLES DE ARTÍCULO"]) @endcomponent
		<div class="text-center w-full pb-2">
			<div id="container-cambio" class="px-2 md:px-56">
				@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="pagePagination" value="1"
				@endslot
				@endcomponent
				@component('components.inputs.input-search') 
					Buscar artículo
					@slot('attributeExInput')
						name="search" id="input-search" placeholder="Ingrese el nombre del artículo" 
					@endslot
					@slot('attributeExButton')
						type="button"
					@endslot
					@slot('classExButton')
						button-search
					@endslot
				@endcomponent
			</div>
			<div class="provider"> </div>
		</div>
		<div id="table-search-container" class="hidden mt-4">
			<div id="table-return"></div>
			<div id="pagination"></div>
		</div>
		@component('components.containers.container-form', ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Categoría:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\CatWarehouseType::all() as $w)
					{
						if ($warehouse->warehouseType == $w->id)
						{
							$options	=	$options->concat([["value"	=>	$w->id,	"description"	=>	$w->description,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$w->id,	"description"	=>	$w->description]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-category removeselect", "attributeEx" => "name=\"category_id\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Cuenta:"]) @endcomponent
				@php
					$options	=	collect();
					if ($warehouse->account)
					{
						$options	=	$options->concat([["value"	=>	$warehouse->accounts->idAccAcc,	"description"	=>	$warehouse->accounts->account. ' - ' .$warehouse->accounts->description . ' ('.$warehouse->accounts->content.')',	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-accounts removeselect", "attributeEx" => "name=\"account_id\" multiple=\"multiple\" id=\"multiple-accounts select2-selection--multiple\" data-validation=\"required\"", "attributeExOption" => "id=\"current_account_id\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Ubicación/Sede:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\Place::where('status',1)->orWhere('id',$warehouse->place_location)->get() as $place)
					{
						if ($place->id == $warehouse->place_location)
						{
							$options	=	$options->concat([["value"	=>	$place->id,	"description"	=>	$place->place,	"selected"	=>	"selected"]]);
						}
						else
						{
							$options	=	$options->concat([["value"	=>	$place->id,	"description"	=>	$place->place]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-places removeselect", "attributeEx" => "name=\"place_id\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Medida:"]) @endcomponent
				@php
					$options	=	collect();
					foreach (App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
					{
						foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
						{
							if ($warehouse->measurement == $child->id)
							{
								$options	=	$options->concat([["value"	=>	$child->id,	"description"	=>	$child->description,	"selected"	=>	"selected"]]);
							}
							else
							{
								$options	=	$options->concat([["value"	=>	$child->id,	"description"	=>	$child->description]]);
							}
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options,"classEx" => "js-measurement removeselect", "attributeEx" => "name=\"measurement_id\" multiple=\"multiple\" data-validation=\"required\""]) @endcomponent
			</div>
			<div class="md:col-span-4 col-span-2 grid md:grid-cols-4 grid-cols-2 md:gap-4 gap-2 @if ($warehouse->warehouseType != 4) hidden @endif" id="options-computer">
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Tipo:"]) @endcomponent
					<div class="flex space-x-2">
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="type" id="smartphone" value="1" data-validation="checkbox_group" data-validation-qty="min1"
									@if (!$warehouse->type || $warehouse->type == 1) checked="true" @endif
								@endslot
								Smartphone
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="type" id="tablet" value="2" data-validation="checkbox_group" data-validation-qty="min1"
									@if ($warehouse->type == 2) checked="true" @endif
								@endslot
								Tablet
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="type" id="laptop" value="3" data-validation="checkbox_group" data-validation-qty="min1"
									@if ($warehouse->type == 3) checked="true" @endif
								@endslot
								Laptop
							@endcomponent
						</div>
						<div>
							@component('components.buttons.button-approval')
								@slot('attributeEx')
									type="radio" name="type" id="desktop" value="4" data-validation="checkbox_group" data-validation-qty="min1"
									@if ($warehouse->type == 4) checked="true" @endif
								@endslot
								Desktop
							@endcomponent
						</div>
					</div>
				</div>
				@php
					$required	=	$warehouse->warehouseType == 4 ? "required" : "";
				@endphp
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Marca:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"brand\" placeholder=\"Ingrese la marca\" data-validation=\"".$required."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Capacidad de Almacenamiento:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"storage\" placeholder=\"Ingrese la capacidad\" data-validation=\"".$required."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Procesador:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"processor\" placeholder=\"Ingrese el procesador\" data-validation=\"".$required."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "Memoria RAM:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"ram\" placeholder=\"Ingrese la memoria RAM\" data-validation=\"".$required."\""]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label', ["label" => "SKU:"]) @endcomponent
					@component('components.inputs.input-text', ["attributeEx" => "type=\"text\" name=\"sku\" placeholder=\"Ingrese el sku\" data-validation=\"".$required."\""]) @endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Concepto:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove", "attributeEx" => "type=\"text\" name=\"concept_name\" placeholder=\"Ingrese el concepto\" value=\"".$warehouse->cat_c->description."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Código corto (Opcional):"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove short_code disabled", "attributeEx" => "type=\"text\" name=\"short_code\" placeholder=\"Ingrese el código corto\" value=\"".$warehouse->short_code."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Código largo (Opcional):"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove long_code disabled", "attributeEx" => "type=\"text\" name=\"long_code\" placeholder=\"Ingrese el código largo\" value=\"".$warehouse->long_code."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Cantidad:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove quantity disabled", "attributeEx" => "type=\"text\" name=\"quantity\" placeholder=\"Ingrese la cantidad\" value=\"".$warehouse->quantity."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Precio unitario:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove uamount disabled", "attributeEx" => "type=\"text\" name=\"uamount\" placeholder=\"Ingrese el precio\" value=\"".( $warehouse->subtotal ? $warehouse->subtotal : $warehouse->amount) / $warehouse->quantityReal."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo de IVA:"]) @endcomponent
				<div class="flex space-x-2">
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_no" value="no" title="No IVA"
							@if ($warehouse->typeTax == null || $warehouse->typeTax == "no") checked @endif
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						No
					@endcomponent
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_a" value="a" title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%"
							{{ $warehouse->typeTax == "a" ? 'checked' : '' }}
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						A
					@endcomponent
					@component("components.buttons.button-approval")
						@slot('classExContainer') iva_kind @endslot
						@slot('attributeEx')
							name="iva_kind" id="iva_b" value="b" title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%"
							{{ $warehouse->typeTax == "b" ? 'checked' : '' }}
						@endslot
						@slot('classEx')
							iva_kind
						@endslot
						B
					@endcomponent
				</div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Importe:"]) @endcomponent
				@component('components.inputs.input-text', ["classEx" => "remove amount disabled", "attributeEx" => "readonly type=\"text\" name=\"amount\" placeholder=\"Ingrese el importe\" value=\"".$warehouse->amount."\""]) @endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Comentario (Opcional):"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "id=\"commentaries\" name=\"commentaries\" placeholder=\"Ingrese el comentario\""]) {{$warehouse->commentaries}} @endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\"  name=\"enviar\" value=\"ENVIAR\"", "classEx" => "enviar", "label" => "ENVIAR"]) @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>

	<script>
		var removeAccountsCompra = true;
		var editArticle          = true;
	</script>
	@include('almacen.alta.scripts_alta')
	<script>
		updateSelectsAlta();
		$(document).on('click','.old_delete',function(){
			id_doc = $(this).parent('.docs-p-r').siblings('.docs-p-l').children('.id_doc').val()
			$('#delete_documents').append('<input class="pathDelete" name="delete_documents[]" value="'+id_doc+'" hidden/>')
			$(this).parent('.docs-p-r').parent('.docs-p').remove();
		})
		.on('change','.js-category',function () {
			switch ($('.js-category option:selected').val()) {
				case "1":
				case "2":
				case "3":

					$('input[name="brand"]').removeAttr("data-validation");
					$('input[name="storage"]').removeAttr("data-validation");
					$('input[name="processor"]').removeAttr("data-validation");
					$('input[name="ram"]').removeAttr("data-validation");
					$('input[name="sku"]').removeAttr("data-validation");
					$('#options-computer').hide()
					break;
				case "4":
					$('input[name="brand"]').attr("data-validation","required");
					$('input[name="storage"]').attr("data-validation","required");
					$('input[name="processor"]').attr("data-validation","required");
					$('input[name="ram"]').attr("data-validation","required");
					$('input[name="sku"]').attr("data-validation","required");
					$('#options-computer').show()
					break;
			
				default:
					break;
			}
		})
		$.validate(
		{
			form: '#container-alta',
			modules : 'security',
			onSuccess : function($form)
			{
				path        = $('.path').val();
				d_documents = $('.pathDelete').val();
				if((path == undefined || path == "") && d_documents != undefined)
				{
					swal({
						title: "Error",
						text: "Debe agregar al menos un ticket de compra.",
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
	</script>
@endsection
