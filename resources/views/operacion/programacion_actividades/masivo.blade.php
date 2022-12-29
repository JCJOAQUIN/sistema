@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" id=\"activity_massive\" action=\"".route('activitiesprogramation.massive.upload')."\" accept-charset=\"UTF-8\" enctype=\"multipart/form-data\""])
		@component("components.labels.title-divisor") ALTA MASIVA @endcomponent
		@component("components.labels.not-found", ["variant" => "note", "title" => ""])
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva:
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a"])
						@slot('attributeEx')
							type="button"
							href="{{route('activitiesprogramation.massive.template')}}"
						@endslot
						@slot('label')
							PLANTILLA DE ACTIVIDADES
						@endslot
					@endcomponent
				</span>
			@endcomponent
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la lista de empleados para una actualización:
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a", "classEx" => "employees-list"])
						@slot('attributeEx')
							type="button"						
							href="{{ route('activitiesprogramation.export.catalogs') }}"
						@endslot
						@slot('label')
							CATALOGOS PARA PLANTILLA
						@endslot
					@endcomponent
				</span>
			@endcomponent
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la lista de catálogos para el llenado de la plantilla: 
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a", "classEx" => "employees-catalog"])
						@slot('attributeEx')
							type="button"
							href="{{ route('employee.export.catalogs') }}"
						@endslot
						@slot('label')
							CATÁLOGOS PARA PLANTILLA
						@endslot
					@endcomponent
				</span>
			@endcomponent
			@component("components.labels.label")
				El Proyecto y WBS/Frente de Trabajo, por favor ingresar por el ID.
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
							"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\" checked",
							"classEx"		=> "laboral-data",
							"label"			=> "coma (,)",
						],
						[
							"kind" 			=> "components.buttons.button-approval",
							"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\"",
							"classEx"		=> "laboral-data",
							"label" 		=> "punto y coma (;)",
						]
					],
					"buttonEx" => 
					[
						[
							"kind"	=> "components.buttons.button",
							"label"	=> "SUBIR ARCHIVO",
							"variant" => "primary",
							"attributeEx" => "type=\"submit\" id=\"submitButton\""
						],
					],
				],
			])
			@slot("attributeExInput")
				type="file" name="csv_file" id="csv" accept=".csv"
			@endslot
			@slot("classEx")
				inputfile inputfile-1
			@endslot
		@endcomponent
	@endcomponent
@endsection