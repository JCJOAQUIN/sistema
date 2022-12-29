@extends('layouts.child_module')

@section('data')
	@component('components.forms.form', ["attributeEx" => "id=\"ncstatus_massive\" action=\"".route('status-nc.massive.upload')."\" method=\"POST\" accept-charset=\"UTF-8\" enctype=\"multipart/form-data\""])
		@component('components.labels.title-divisor', ["label" => "Alta Masiva"]) @endcomponent
		@component('components.labels.not-found', ["variant" => "note"])
			@component('components.labels.label')
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva:
				<span class="inline-block"> 
					@component('components.buttons.button', ["variant" => "success", "attributeEx" => "href=\"".route('status-nc.massive.template')."\"", "buttonElement" => "a", "label" => "Plantilla de estados de no conformidades"]) @endcomponent
				</span>
			@endcomponent
			@component('components.labels.label')
				Dé clic en el siguiente enlace para descargar la lista de catálogos para el llenado de la plantilla:
				<span class="inline-block"> 
					@component('components.buttons.button', ["variant" => "success", "attributeEx" => "href=\"".route('status-nc.export.catalogs')."\"", "buttonElement" => "a", "label" => "Catálogos para plantilla"]) @endcomponent
				</span>
			@endcomponent
			@component('components.labels.label', ["label" => "El Proyecto y WBS/Frente de Trabajo, por favor ingresar por el ID."]) @endcomponent
			@component('components.labels.label', ["label" => "El formato de la fecha debe ser : YYYY-MM-DD."]) @endcomponent
			@component('components.labels.label', ["label" => "El archivo debe tener una extensión .CSV"]) @endcomponent
		@endcomponent
		<div class="mt-12">
			@php
				$buttonExload =
				[
					"separator"	=>
					[
						["kind" => "components.buttons.button-approval", "label" => "coma (,)", "attributeEx" => "value=\",\" name=\"separator\" id=\"separatorComa\" checked"],
						["kind" => "components.buttons.button-approval", "label" => "Punto y coma (;)", "attributeEx" => "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""]
					],
					"buttonEx"	=>
					[
						["kind"	=>	"components.buttons.button",	"label"	=>	"SUBIR ARCHIVO",	"variant"	=>	"primary",	"attributeEx"	=>	"type=\"submit\""],
					]
				];
			@endphp
			@component("components.documents.select_file_csv", ["attributeExInput" => "name=\"csv_file\" id=\"csv\" accept=\".csv\"", "buttons" => $buttonExload])
			@endcomponent
		</div>
	@endcomponent
@endsection