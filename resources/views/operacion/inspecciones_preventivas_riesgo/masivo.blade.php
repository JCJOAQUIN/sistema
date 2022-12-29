@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") ALTA MASIVA @endcomponent
	@component("components.forms.form", ["attributeEx" => "id=\"preventive_massive\" action=\"".route('preventive.massive.upload')."\" method=\"POST\" accept-charset=\"UTF-8\" enctype=\"multipart/form-data\""])
		@component("components.labels.not-found", ["variant" => "note", "title" => ""])
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva: 
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a"])
						@slot('attributeEx')
							type="button"
							href="{{route('preventive.massive.template')}}"
						@endslot
						@slot('label')
							PLANTILLA INSPECCIÓN PREVENTIVA DE RIESGO
						@endslot
					@endcomponent
				</span>
			@endcomponent
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la lista de catálogos para el llenado de la plantilla:
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a", "classEx" => "employees-list"])
						@slot('attributeEx')
							type="button"						
							href="{{ route('preventive.export.catalogs') }}"
						@endslot
						@slot('label')
							CATÁLOGOS PARA PLANTILLA
						@endslot
					@endcomponent
				</span>
			@endcomponent
			@component("components.labels.label")
				El Proyecto y Frente de Trabajo/WBS, por favor ingresar por el ID.
			@endcomponent
			@component("components.labels.label")
				El formato de la fecha debe ser : YYYY-MM-DD.
			@endcomponent
			@component("components.labels.label")
				El archivo debe tener una extensión .CSV
			@endcomponent
		@endcomponent
		@component("components.documents.select_file_csv", 
			[
				"buttons" => 
				[
					"separator" =>
					[
						[
							"kind" 			=> "components.buttons.button-approval",
							"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorPuntoComa\" checked=\"checked\"",
							"label"			=> "coma (,)",
						],
						[
							"kind" 			=> "components.buttons.button-approval",
							"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorComa\"",
							"label" 		=> "punto y coma (;)",
						]
					],
					"buttonEx" => 
					[
						[
							"kind"	=> "components.buttons.button",
							"label"	=> "SUBIR ARCHIVO",
							"variant" => "primary",
							"attributeEx" => "type=\"submit\"",
						],
					],
				],
			])
			@slot("attributeExInput")
				name="csv_file" 
				id="csv"
			@endslot
			@slot("classEx")
				inputfile inputfile-1
			@endslot
		@endcomponent
	@endcomponent
@endsection
@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$.validate(
			{
				form: '#preventive_massive',
				onSuccess : function($form)
				{
					swal({
						title              	: 'Cargando',
						icon				: '{{ asset(getenv("LOADING_IMG")) }}',
						button             	: false,
						closeOnClickOutside	: false,
						text               	: 'Este proceso puede demorar',
						closeOnEsc         	: false
					});
					if ($('#csv').val() == "")
					{
						swal('','Por favor agregue un archivo.','error')
						return false;
					}
					if (!$('[name="separator"]').is(':checked'))
					{
						swal('','Por favor seleccione un separador.','error')
						return false;
					}
				}
			});
		});
	</script>
@endsection
