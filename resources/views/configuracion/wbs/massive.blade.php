@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('wbs.massive.upload')."\" enctype=\"multipart/form-data\" method=\"POST\" id=\"wbs_massive\""])
		@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
		@component("components.labels.not-found", ["variant" => "note", "title" => ""])
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva: 
				@component("components.buttons.button", ["variant" => "success", "buttonElement" => "a"])
					@slot("attributeEx")
						href = "{{route('wbs.massive.template')}}"
					@endslot
					PLANTILLA PARA WBS
				@endcomponent
			@endcomponent		
			
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la lista de proyectos para el llenado de la plantilla:
				@component("components.buttons.button", ["variant" => "success", "buttonElement" => "a"])
					@slot("attributeEx") 
						name = "send" 
						href = "{{ route('wbs.export.projects') }}"
					@endslot
					PROYECTOS PARA PLANTILLA
				@endcomponent
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
							"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\"",
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
							"label"	=> "SUBIR WBS",
							"variant" => "primary",
							"attributeEx" => "type=\"submit\" id=\"submitButton\""
						],
					]
				]
			])
			@slot("attributeExInput")
				type	= "file" 
				name	= "csv_file" 
				id		= "csv" 
				accept	= ".csv"
			@endslot
			@slot("classEx")
				inputfile inputfile-1
			@endslot
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<script>
		$(document).ready(function()
		{
			$('#separatorComa').prop('checked',true);
		});
	</script>
@endsection
