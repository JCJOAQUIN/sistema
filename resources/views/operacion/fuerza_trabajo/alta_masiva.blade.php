@extends('layouts.child_module')

@section('data')
	@component('components.forms.form', [ 'attributeEx' => "method=\"POST\" id=\"employee_massive\" action=\"".route('work-force.upload')."\"", "files" => true])
		@component('components.labels.title-divisor') Alta Masiva @endcomponent
		@component('components.labels.not-found', ['variant' => 'note'])
			<li>
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva:
				<span class="inline-block">
					@component('components.buttons.button',
						[
							"variant" 		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "href=\"".route('work-force.massive.template')."\"",
							"label"			=> "Plantilla para Fuerza de Trabajo"
						])
					@endcomponent
				</span>
			</li>
			<li>
				Dé clic en el siguiente enlace para descargar la lista de catálogos para el llenado de la plantilla:
				<span class="inline-block">
					@component('components.buttons.button',
						[
							"variant" 		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "href=\"".route('work-force.export.catalogs')."\"",
							"label"			=> "Catálogos para plantilla"
						])
					@endcomponent
				</span>
			</li>
			<li>
				El Proyecto y WBS/Frente de Trabajo, por favor ingresar por el ID.
			</li>
			<li>
				El formato de la fecha debe ser : YYYY-MM-DD.
			</li>
			<li>
				El archivo debe tener una extensión .CSV
			</li>
		@endcomponent
		@php
			$buttons = 
			[
				"separator" => 
				[
					[
						"kind"			=> "components.buttons.button-approval",
						"label"			=> "coma (,)",
						"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComa\""
					],
					[
						"kind"			=> "components.buttons.button-approval",
						"label" 		=> "punto y coma (;)",
						"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComa\""
					]
				]
			];
		@endphp
		@component('components.documents.select_file_csv',
			[
				"attributeExInput"	=> "type=\"file\" name=\"csv_file\" id=\"csv\"",
				"buttons"			=> $buttons
			])
		@endcomponent
		<div class="flex justify-center">
			@component('components.buttons.button', [ "variant" => "primary"])
				@slot('attributeEx')
					type="submit"
				@endslot
				SUBIR ARCHIVO
			@endcomponent
		</div>	 
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$(document).on('change','#csv',function(e)
			{
				label		= $(this).next('label');
				fileName	= e.target.value.split( '\\' ).pop();
				if(fileName)
				{
					label.find('span').html(fileName);
				}
				else
				{
					label.html(labelVal);
				}
			});
			$('#separatorComa').prop('checked',true);
		});
	</script>
@endsection