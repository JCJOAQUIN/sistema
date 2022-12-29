@extends('layouts.child_module')
@section('data')
	@if(isset($property))
		<form id="form-properties" method="post" action="{{route('property.update',$property->id)}}">
		@csrf
		@method('PUT')
	@else
		<form id="form-properties" method="post" action="{{route('property.store')}}">
		@csrf
	@endif
		@component("components.labels.title-divisor") DATOS DE INMUEBLE @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Inmueble:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('classEx') new-input-text @endslot
					@slot("attributeEx") type="text" name="property" placeholder="Ingrese el nombre" data-validation="required" @if(isset($property)) value="{{ $property->property }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Ubicación:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="location" placeholder="Ingrese la ubicación" data-validation="required" @if(isset($property)) value="{{ $property->location }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo de Inmueble:
				@endcomponent
				@php
					$options = collect();
					$options = $options->concat(
					[
						[
							"value" => "Propio", "description" => "Propio", 'selected' => ((isset($property) && $property->type_property == "Propio") ? "selected" : "")
						],
						[
							"value" => "Renta", "description" => "Renta", 'selected' => ((isset($property) && $property->type_property == "Renta") ? "selected" : "")
						]
					]);
				@endphp
				
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") type_property removeselect form-control @endslot
					@slot("attributeEx") name="type_property" multiple="multiple" data-validation="required" @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Uso de Inmueble:
				@endcomponent
				@php
					$options = collect();
					$options = $options->concat(
					[
						[
							"value" => "Comercial", "description" => "Comercial", 'selected' => ((isset($property) && $property->use_property == "Comercial") ? "selected" : "")
						],
						[
							"value" => "Habitacional", "description" => "Habitacional", 'selected' => ((isset($property) && $property->use_property == "Habitacional") ? "selected" : "")
						]
					]);
				@endphp
				
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") removeselect form-control @endslot
					@slot("attributeEx") name="use_property" class="removeselect form-control" multiple="multiple" data-validation="required" @endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") Características Físicas @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Número de habitaciones:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="number_of_rooms" placeholder="Ingrese el número de habitaciones" data-validation="required" @if(isset($property)) value="{{ $property->number_of_rooms }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Número de baños:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="number_of_bathrooms" placeholder="Ingrese el número de baños" data-validation="required" @if(isset($property)) value="{{ $property->number_of_bathrooms }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Estacionamiento:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="parking_lot" placeholder="Ingrese si cuenta con estacionamiento" data-validation="required" @if(isset($property)) value="{{ $property->parking_lot }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Cocina:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="kitchen_room" placeholder="Ingrese si cuenta con cocina" data-validation="required" @if(isset($property)) value="{{ $property->kitchen_room }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Jardín:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="garden" placeholder="Ingrese si cuenta con jardín" data-validation="required" @if(isset($property)) value="{{ $property->garden }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Sala de Juntas:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="boardroom" placeholder="Ingrese si cuenta con sala de juntas" data-validation="required" @if(isset($property)) value="{{ $property->boardroom }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Amueblado:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="furnished" placeholder="Ingrese si es amueblado" data-validation="required" @if(isset($property)) value="{{ $property->furnished }}" @endif @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Medidas en m2:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="measurements" placeholder="Ingrese las medidas" data-validation="required" @if(isset($property)) value="{{ $property->measurements }}" @endif @endslot
				@endcomponent
			</div>
		@endcomponent
		@component("components.labels.title-divisor") DOCUMENTACIÓN LEGAL @endcomponent
		
		@component("components.containers.container-form")
			@php
				$options = collect();
				foreach(["Contrato de arrendamiento", "INE de arrendatario o de dueño", "Inmueble en garantía", "Pagaré", "Otro"] as $item)
				{
					$options = $options->concat([["value" => $item, "description" => $item]]);
				}
			@endphp
			<div class="col-span-2">
				@component("components.labels.label")
					Tipo de Documento:
				@endcomponent
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") removeselect form-control @endslot
					@slot("attributeEx") name="type_document" multiple="multiple" @endslot
					@slot("classExContainer") type_document @endslot 
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Comentarios: (Opcional)
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="description" placeholder="Ingrese un comentario" @endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 
					INE
				@endcomponent
				@component('components.documents.upload-files', 
				[
					"attributeExInput"    => "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExInput" 		  => "pathActioner path_legal_document",
					"classExContainer"	  => "uploader-content legal-documents",
					"classExRealPath" 	  => "path real_name_legal_document",
					"classEx"			  => "docs-legal",
					"attributeExRealPath" => "name=\"realPath\"",
					"classExDelete"		  => "delete-lega-doc"
				])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@component("components.buttons.button", ["variant" => "warning"])
				@slot("attributeEx") id="add_legal_document" type="button" @endslot
				<span class="icon-plus"></span>
				<span>Agregar documento</span>
				@endcomponent	
			</div>		
		@endcomponent
		
		@php
			$modelHead = ["Tipo de Documento", "Comentarios", "Documento", "Acción"];		
			$body = [];
			$modelBody = [];
			if(isset($property))
			{
				foreach($property->legalDocuments as $document)
				{
					$body = 
					[
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> $document->legal_document,
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "legal_document_id",
									"attributeEx"	=> "type=\"hidden\" name=\"legal_document_id[]\" value=\"".$document->id."\"",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_legal_document[]\" value=\"".$document->legal_document."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> ($document->description != "" ?  htmlentities($document->description) : 'Sin comentarios'),
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_description[]\" value=\"".htmlentities($document->description)."\"",
								]
							]
						],
						[
							"classEx" => "nowrap",
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"buttonElement"	=> "a",
									"label"			=> "Archivo",
									"variant"		=> "secondary",
									"attributeEx"	=> "href=\"".url('docs/properties/'.$document->path)."\" target=\"_blank\" title=\"".$document->path."\"",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "t_path_legal_document",
									"attributeEx"	=> "type=\"hidden\" name=\"t_path_legal_document[]\" value=\"".$document->path."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"classEx"		=> "delete-legal-document",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		
		@component("components.tables.alwaysVisibleTable", ["variant" => "default", "modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot("classEx")
			@endslot
			@slot("attributeEx")
			@endslot
			@slot("attributeExBody")
				id="legal-documents"
			@endslot
		@endcomponent

		@component("components.labels.title-divisor") INFORMACIÓN DE PAGOS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2 amount">
				@component("components.labels.label")
					Tipo de pago:
				@endcomponent
				@php
					$options = collect();
					$optionsArray = ["Renta","Luz","Internet","Agua","Predial","Mantenimiento","Servicio","Servicio","Vigilancia"];
					foreach($optionsArray as $item)
					{
						$options = $options->concat([["value" => $item, "description" => $item]]);
					}
				@endphp
				
				@component("components.inputs.select", ["options" => $options, "classExContainer" => "type_payment"])
					@slot("classEx") removeselect form-control @endslot
					@slot("attributeEx")  name="payment_type" class="removeselect form-control" multiple="multiple" @endslot
				@endcomponent
			</div>
			<div class="col-span-2 amount">
				@component("components.labels.label")
					Monto:
				@endcomponent
				@component("components.inputs.input-text")
					@slot("classEx") new-input-text @endslot
					@slot("attributeEx") type="text" name="amount" placeholder="Ingrese el monto" @endslot
				@endcomponent
			</div>
			@php
				$options = collect();
				$optionsArray = ["Semanal", "Mensual", "Bimestral", "Anual", "Otro"];
				foreach($optionsArray as $item)
				{
					$options = $options->concat([["value" => $item, "description" => $item]]);
				}
			@endphp
			<div class="col-span-2 periodicity">
				@component("components.labels.label")
					Periodicidad:
				@endcomponent
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx") removeselect form-control @endslot
					@slot("attributeEx") name="periodicity" multiple="multiple" @endslot
				@endcomponent
			</div>
			<div class="col-span-2 periodicity-amount">
				@component("components.labels.label")
					Periodo de Pago:
				@endcomponent
				@php
					$inputs = 
					[
						[
							"input_classEx"		=> "new-input-text",
							"input_attributeEx" => "placeholder=\"Desde:\" readonly type=\"text\" name=\"mindate\""
						],
						[
							"input_classEx"		=> "new-input-text",
							"input_attributeEx" => "placeholder=\"Hasta:\" readonly type=\"text\" name=\"maxdate\""
						]
					];
				@endphp
				@component("components.inputs.range-input", ["inputs" => $inputs])@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.documents.upload-files', 
				[
					"attributeExInput"    => "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExInput" 		  => "pathActioner path_payment_document",
					"classExContainer"	  => "uploader-content documents-payments",
					"classExRealPath" 	  => "path real_name_payment_document",
					"classEx"			  => "documents-payments",
					"attributeEx"		  => "id=\"documents-payments\"",
					"attributeExRealPath" => "name=\"realPath\"",
					"classExDelete"		  => "delete-payment-doc"
				])@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("attributeEx") id="add_payment" type="button" @endslot
					<span class="icon-plus"></span>
					<span>Agregar pago</span>
				@endcomponent
			</div>
		@endcomponent
		@php
			$modelHead = 
			[
				[
					["value" => "Tipo de Pago"], 
					["value" => "Periodicidad"], 
					["value" => "Periodo Pagado"], 
					["value" => "Monto"], 
					["value" => "Comprobantes"], 
					["value" => "Acción"]
				]
			];
			$body = [];
			$modelBody = [];
			if(isset($property))
			{
				foreach($property->payments as $payment)
				{
					$body = 
					[
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> $payment->payment_type,
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "payment_id",
									"attributeEx"	=> "type=\"hidden\" name=\"payment_id[]\" value=\"".$payment->id."\"",
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_payment_type[]\" value=\"".$payment->payment_type."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> $payment->periodicity,
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_periodicity[]\" value=\"".$payment->periodicity."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> $payment->date_range,
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_date_range[]\" value=\"".$payment->date_range."\"",
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"			=> "components.labels.label",
									"label"			=> "$ ".number_format($payment->amount,2),
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\" value=\"".$payment->amount."\"",
								]
							]
						],
						[
							"classEx" => "nowrap",
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"buttonElement" => "a",
									"attributeEx"	=> "href=\"".url('docs/properties/'.$payment->path)."\" target=\"_blank\" title=\"".$payment->path."\"",
									"label"			=> "Archivo",
									"variant"		=> "secondary"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"classEx"		=> "t_path",
									"attributeEx"	=> "type=\"hidden\" name=\"t_path[]\" value=\"".$payment->path."\"",
								]
							]
						],
						[
							"classEx" => "nowrap",
							"content" =>
							[
								[
									"kind"			=> "components.buttons.button",
									"classEx"		=> "delete-payment",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"label"			=> "<span class=\"icon-x\"><span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component("components.tables.table", ["attributeExBody" => "id=\"payments\"", "modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot("classEx")
			@endslot
			@slot("attributeEx")
			@endslot
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-10 mb-6">
			@component("components.buttons.button")
				REGISTRAR
				@slot("attributeEx") type="submit" name="send" @endslot
			@endcomponent
			@if(!isset($property))
				@component("components.buttons.button", ["variant" => "reset", "attributeEx" => "", "classEx" => ""])
					@slot("classEx") reset @endslot
					@slot("attributeEx")  type="reset" @endslot
					BORRAR CAMPOS
				@endcomponent
			@endif
		</div>
		<span id="invisible"></span>
	</form>


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
			validation();
			@ScriptSelect(
			[
				"selects" =>
				[
					[
						"identificator" => "[name=\"type_property\"]",
						"placeholder"	=> "Seleccione el tipo de inmueble",
					],
					[
						"identificator" => "[name=\"use_property\"]",
						"placeholder"	=> "Seleccione el uso de inmueble",
					],
					[
						"identificator" => "[name=\"payment_type\"]",
						"placeholder"	=> "Seleccione el tipo de pago",
					],
					[
						"identificator" => "[name=\"periodicity\"]",
						"placeholder"	=> "Seleccione la periodicidad",
					],
					[
						"identificator" => "[name=\"type_document\"]",
						"placeholder"	=> "Seleccione el tipo de documento",
					]
				]
			])
			@endScriptSelect
			$('[name="amount"],[name="number_of_rooms"],[name="number_of_bathrooms"],[name="measurements"]').numeric({altDecimal: ".", decimalPlaces: 2, negative: false});
			$(document).on('change','[name="maxdate"]',function(e)
			{
				mindate		= moment($('[name="mindate"]').val(), 'DD-MM-YYYY', true);
				maxdate		= moment($('[name="maxdate"]').val(), 'DD-MM-YYYY', true);
				startDate 	= mindate['_i'];
				endDate 	= maxdate['_i'];
				
				if($('option:selected', '[name="periodicity"]').val() != '')
				{
					diff = maxdate.diff(mindate, 'days');
					
					periodicity = $('[name="periodicity"] option:selected').val();
	
					days = [];
					if(periodicity == "Semanal")
					{
						days = [6,7];
					}
					if(periodicity == "Mensual")
					{
						days = [29,30,31];
					}
					if(periodicity == "Bimestral")
					{
						days = [58,59,60,61];
					}
					if(periodicity == "Anual")
					{
						days = [365,366];
					}
					if(periodicity == "Otro")
					{
						days = [];
					}
	
	
					if(!days.includes(diff) && periodicity != "Otro")
					{
						swal('','El rango de fechas seleccionado no concuerda con la periodicidad.','error');
						$('[name=\"mindate\"]').val('');
						$('[name=\"maxdate\"]').val('');
					}
					else
					{
						$('[name=\"mindate\"]').val(startDate);
						$('[name=\"maxdate\"]').val(endDate);
					}
				}
				else
				{
					swal('','Seleccione periodicidad.','warning');
					$('[name=\"mindate\"]').val('');
					$('[name=\"maxdate\"]').val('');
				}


			})
			.on('change','.pathActioner',function(e)
			{
				target = $(e.currentTarget);
				filename		= $(this);
				uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath"]');
				extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;

				if (filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png, un archivo pdf.', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb.', 'warning');
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
						url			: '{{ route("property.upload") }}',
						data		: formData,
						contentType	: false,
						processData	: false,
						success		: function(r)
						{
							if(r.error=='DONE')
							{
								target.removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								target.parent('.uploader-content').siblings('input[name="realPath"]').val(r.path);
								target.val('');
							}
							else
							{
								swal('',r.message, 'error');
								target.removeAttr('style').parent('.uploader-content').removeClass('loading');
								target.val('');
								target.parent('.uploader-content').siblings('input[name="realPath"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor.', 'error');
							target.removeAttr('style').parent('.uploader-content').removeClass('loading');
							target.val('');
							target.parent('.uploader-content').siblings('input[name="realPath"]').val('');
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
				uploadedName	= $(this).parents('.docs-p').find('input[name="realPath"]');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("property.upload") }}',
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
				$(this).parents('div.docs-p').remove();
			})
			.on('change','[name="periodicity"]',function()
			{
				$('[name="mindate"]').val('');
				$('[name="maxdate"]').val('');
			})
			.on('click','#add_legal_document',function()
			{
				$('.type_document, .docs-legal').parent().find('.form-error').remove();
				type_document		= $('[name="type_document"] option:selected').val();
				type_document_text	= $('[name="type_document"] option:selected').text();
				description			= $('[name="description"]').val();
				cont				= true;

				$('.real_name_legal_document').each(function(i,v)
				{
					if($(this).val()=='')
					{
						cont = false;
						$('.docs-legal').append('<span class="help-block form-error">Este campo es obligatorio</span>');
						swal('','Por favor cargue los documentos faltantes.','error');
						if(payment_type == undefined || payment_type == "" || periodicity == undefined || periodicity == "" || amount == undefined || amount == "" || mindate == undefined || mindate == "" || maxdate == undefined || maxdate == "")
						{
							if (payment_type == undefined || payment_type == "")
							{
								$('.type_payment').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if (periodicity == undefined || periodicity == "")
							{
								$('.periodicity').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if (amount == undefined || amount == "")
							{
								$('.amount').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
							if (mindate == undefined || mindate == "" || maxdate == undefined || maxdate == "")
							{
								$('.periodicity-amount').append('<span class="help-block form-error">Estos campos son obligatorios.</span>');
							}
						}
						die();
					}
				});
				
				if(type_document == undefined || type_document == "")
				{
					$('.type_document').append('<span class="help-block form-error">Este campo es obligatorio</span>');
					swal('','Por favor llene todos los datos solicitados.','error');
					return false;
				}

				paths = $('<div></div>');
				$('.real_name_legal_document').each(function(i, v)
				{
					pathName = $(this).val();
					url = '{{ url('docs/properties/') }}/'+pathName;
					@php
						$button = view("components.buttons.button",[
							"classEx" 		=> "file_button",
							"attributeEx"	=> "target=\"_blank\"",
							"variant"		=> "secondary",
							"label"			=> "Archivo",
							"buttonElement" => "a"
						])->render();
						$button = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $button));
					@endphp

					button = '{!!preg_replace("/(\r)*(\n)*/", "", $button)!!}';
					button = $(button);
					button.attr("href", url);
					button.attr("title", pathName);
					paths.append(button);
					paths.append($('<input type="hidden" name="t_path_legal_document[]" class="t_path_legal_document">').val(pathName));
				});
				@php
					$modelHead = ["Tipo de documento", "Comentarios", "Documento", "Acción"];
					$modelBody = 
					[
						[
							[
								"content" => 
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx"	 	=> "type_document_text"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "legal_document_id",
										"attributeEx"	=> "type=\"hidden\" name=\"legal_document_id[]\" value=\"x\""
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_legal_document[]\""
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "t_description"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_description[]\""
									]
								]
							],
							[
								"classEx" => "path_class",
								"content" => 
								[
									"label"		=> ""
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"classEx"		=> "delete-legal-document",
										"attributeEx"	=> "type=\"button\"",
										"variant"		=> "red",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						]
					];
					$table = view("components.tables.alwaysVisibleTable",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead" => true,
						"variant" => "default",
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				tr = $(table);
				tr = rowColor('#legal-documents', tr);
				tr.find('.type_document_text').text(type_document_text);
				tr.find('[name="legal_document_id[]"]').text('x');
				tr.find('[name="t_legal_document[]"]').val(type_document);
				tr.find('.t_description').text(description);
				tr.find('[name="t_description[]"]').val(description);
				tr.find('.path_class').append(paths);

				$('#legal-documents').append(tr);
				$('[name="type_document"]').val('').trigger('change');
				$('[name="description"]').val('');
				$('.path_legal_document').val('');
				$('.real_name_legal_document').val('');
				$('.legal-documents').removeClass('image_success');

				swal('','Documento agregado exitosamente.','success');
			})
			.on('click','#add_payment',function()
			{
				$('.type_payment, .amount, .periodicity, .periodicity-amount, .documents-payments').parent().find('.form-error').remove();
				payment_type		= $('[name="payment_type"] option:selected').val();
				periodicity			= $('[name="periodicity"] option:selected').val();
				payment_type_text	= $('[name="payment_type"] option:selected').text();
				periodicity_text	= $('[name="periodicity"] option:selected').text();
				amount				= $('[name="amount"]').val();
				mindate				= $('[name="mindate"]').val();
				maxdate				= $('[name="maxdate"]').val();
				cont				= true;

				$('.real_name_payment_document').each(function(i,v)
				{
					if($(this).val()=='')
					{
						$('.documents-payments').append('<span class="help-block form-error">Este campo es obligatorio</span>');
						swal('','Por favor cargue los documentos faltantes.','error');
						return false;
					}
				});

				if(payment_type == undefined || payment_type == "" || periodicity == undefined || periodicity == "" || amount == undefined || amount == "" || mindate == undefined || mindate == "" || maxdate == undefined || maxdate == "")
				{
					if (payment_type == undefined || payment_type == "")
					{
						$('.type_payment').append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if (periodicity == undefined || periodicity == "")
					{
						$('.periodicity').append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if (amount == undefined || amount == "")
					{
						$('.amount').append('<span class="help-block form-error">Este campo es obligatorio</span>');
					}
					if (mindate == undefined || mindate == "" || maxdate == undefined || maxdate == "")
					{
						$('.periodicity-amount').append('<span class="help-block form-error">Estos campos son obligatorios.</span>');
					}
					swal('','Por favor llene todos los datos solicitados.','error');
					return false;
				}

				paths = $('<div></div>');
				$('.real_name_payment_document').each(function(i, v)
				{
					pathName = $(this).val();
					url = '{{ url('docs/properties/') }}/'+pathName;
					@php
						$button = view("components.buttons.button",[
							"classEx" 		=> "file_button",
							"attributeEx"	=> "target=\"_blank\"",
							"variant"		=> "secondary",
							"label"			=> "Archivo",
							"buttonElement" => "a"
						])->render();
						$button = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $button));
					@endphp

					button = '{!!preg_replace("/(\r)*(\n)*/", "", $button)!!}';
					button = $(button);
					button.attr("href", url);
					button.attr("title", pathName);
					paths.append(button)
					paths.append($('<input type="hidden" name="t_path[]" class="t_path">').val(pathName));
				});

				@php
					$modelHead = 
					[
						[
							["value" => "Tipo de pago"], 
							["value" => "Periodo de pago"], 
							["value" => "Periodicidad"], 
							["value" => "Monto"],
							["value" => "Comprobantes"], 
							["value" => "Acción"]
						]
					];
					
					$modelBody = 
					[
						[
							[
								"content" => 
								[
									[
										"kind" 			=> "components.labels.label",
										"classEx"	 	=> "payment_type_text"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx" 		=> "payment_id",
										"attributeEx"	=> "type=\"hidden\" name=\"payment_id[]\" value=\"x\""
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_payment_type[]\""
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind" => "components.labels.label",
										"classEx" => "range_dates"
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_date_range[]\"",
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"		=> "components.labels.label",
										"classEx"	=> "periodicity_text"
									],
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_periodicity[]\""
									]
								]
							],
							[
								"content" =>
								[
									[
										"kind"			=> "components.labels.label",
										"classEx"		=> "amount_class"
									],
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"hidden\" name=\"t_amount[]\""
									]
								]
							],
							[
								"classEx" => "path_class",
								"content" => 
								[
									"label" => ""
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.buttons.button",
										"classEx"		=> "delete-payment",
										"attributeEx"	=> "type=\"button\"",
										"variant"		=> "red",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							]
						]
					];
					$table = view("components.tables.table",[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> "true"
					])->render();
					$table = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
				@endphp
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
				tr = $(table);
				tr.find('[name="t_payment_type[]"]').val(payment_type);
				tr.find('.payment_type_text').text(payment_type_text);
				tr.find('.payment_type_text').text(payment_type_text);
				tr.find('[name="t_date_range[]"]').val(mindate+" - "+maxdate);
				tr.find('.range_dates').text(periodicity_text);
				tr.find('[name="t_periodicity[]"]').val(periodicity);
				tr.find('.periodicity_text').text(mindate+" - "+maxdate);
				tr.find('.amount_class').text("$ "+Number(amount));
				tr.find('[name="t_amount[]"]').val(Number(amount));
				tr.find('.path_class').append(paths);
				$('#payments').append(tr);
				$('[name="payment_type"]').val('').trigger('change');
				$('[name="periodicity"]').val('').trigger('change');
				$('[name="amount"]').val('');
				$('[name="mindate"]').val('');
				$('[name="maxdate"]').val('');
				$('.path_payment_document').val('');
				$('.real_name_payment_document').val('');
				$('.documents-payments').removeClass('image_success');

				swal('','Pago agregado exitosamente.','success');
			})
			.on('click','.delete-payment',function()
			{
				id = $(this).parents('tr').find('.payment_id').val();
				if(id != "x")
				{
					$('#invisible').append($('<input type="hidden" name="delete[]"/>').val(id));
				}
				$(this).parents('tr').remove();
				swal('','Pago eliminado exitosamente.','success');
			})
			.on('click','.delete-legal-document',function()
			{
				id = $(this).parents('tr').find('.legal_document_id').val();
				if(id != "x")
				{
					$('#invisible').append($('<input type="hidden" name="deleteLegalDocument[]"/>').val(id));
				}
				$(this).parents('tr').remove();
				swal('','Documento eliminado exitosamente.','success');
			})
			.on('click','.reset',function(e)
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
						$('#payments').html('');
						$('#legal-documents').html('');
						$('.new-input-text').val('');
						$('.removeselect').val(null).trigger('change');
						$('.path_payment_document, .path_legal_document').removeAttr('style');
						$('.real_name_payment_document, .real_name_legal_document').removeAttr('value');
						$('.docs-legal, .type_document, .documents-payments, .type_payment, .periodicity, .amount, .periodicity-amount').parent().find('.form-error').remove();
						$('.documents-payments, .legal-documents').removeClass('image_success');
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','.delete-lega-doc',function()
			{
				$('.path_legal_document').val('');
				$('.real_name_legal_document').val('');
				$('.legal-documents').removeClass('image_success');
			})
			.on('click','.delete-payment-doc',function()
			{
				$('.path_payment_document').val('');
				$('.real_name_payment_document').val('');
				$('.documents-payments').removeClass('image_success');
			})
		});

		function validation()
		{
			$.validate(
			{
				form	: '#form-properties',
				modules	: 'security',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if(Number($('[name="measurements"]').val()) == 0)
					{
						swal('','Las medidas no pueden ser cero','error');
						$('[name="measurements"]').removeClass('valid').addClass('error').val('');
						return false;
					}
					type_document		= $('[name="type_document"] option:selected').val();
					type_document_text	= $('[name="type_document"] option:selected').text();
					description			= $('[name="description"]').val();
					payment_type		= $('[name="payment_type"] option:selected').val();
					periodicity			= $('[name="periodicity"] option:selected').val();
					payment_type_text	= $('[name="payment_type"] option:selected').text();
					periodicity_text	= $('[name="periodicity"] option:selected').text();
					amount				= $('[name="amount"]').val();
					// date_range			= $('[name="date_range"]').val();
					mindate				= $('[name="mindate"]').val();
					maxdate				= $('[name="maxdate"]').val();
					flag				= false;

					$('.real_name_legal_document').each(function(i,v)
					{
						if($(this).val()!='')
						{
							flag = true;
							return false;
						}
					});

					$('.real_name_payment_document').each(function(i,v)
					{
						if($(this).val()!='')
						{
							flag = true;
							return false;
						}
					});

					if (type_document != undefined || payment_type != undefined || periodicity != undefined || amount != "" || mindate != "" || maxdate != "")  
					{
						swal('','Tiene datos sin agregar a la tabla correspondiente','error');
						return false;
					}

					if (flag) 
					{
						swal('','Tiene documentos sin agregar a la tabla correspondiente','error');	
						return false;
					}
					else
					{
						swal("Cargando",
						{
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						return true;
					}
				}
			});
		}
	</script>

@endsection