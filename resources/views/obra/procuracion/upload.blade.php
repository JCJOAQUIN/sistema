@extends('layouts.child_module')

@section('data')
	@component('components.forms.form', 
	[
		"attributeEx" => "method=\"POST\" id=\"procurement_massive\" action=\"".route('construction.procurement.upload.file')."\" accept-charset=\"UTF-8\" ", 
		"files"		  => true,
		"token"       => "true"
	])
		@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<ul>
					<li>
						Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva:
						<span class="inline-block">
							@component("components.buttons.button", ["variant" => "success", "buttonElement" => "a"])
								@slot("attributeEx")
									href="{{route('construction.procurement.download')}}"
								@endslot
								Plantilla de carga
							@endcomponent
						</span>
					</li>
					<li>
						El archivo debe tener una extensión .CSV
					</li>
					<li>
						El formato de la fecha debe ser : YYYY-MM-DD.
					</li>
				</ul>
			@endslot
		@endcomponent
		@php
			$buttons = [
				"separator" => 
				[
					[
						"kind" 			=> "components.buttons.button-approval",
						"label"			=> "coma (,)",
						"attributeEx"	=> "value=\",\" name=\"separator\" id=\"coma-delimitator\""
					],
					[
						"kind"			=> "components.buttons.button-approval",
						"label" 		=> "punto y coma (;)",
						"attributeEx"	=> "value=\";\" name=\"separator\" id=\"punto-coma-delimitator\""
					]
				], 
				"buttonEx" =>
				[
					[
						"kind" => "components.buttons.button",
						"label" => "CARGAR ARCHIVO",
						"variant" => "primary",
						"attributeEx" => "type=\"submit\""
					]
				]
			];
		@endphp
		@component('components.documents.select_file_csv', 
		[
			"attributeExInput"	=> "type=\"file\" name=\"csv_file\" id=\"csv\" accept=\".csv\"",
			"buttons"			=> $buttons
		])
		@endcomponent
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		labelVal	= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span>';
		$(document).ready(function()
		{
			$('#coma-delimitator').prop('checked',true);
		})
		.on('change','#csv',function(e)
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
		
	</script>
@endsection