@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
	@component('components.forms.form', [ 'attributeEx' => "method=\"POST\" id=\"employee_massive\" action=\"".route('administration.employee.massive-upload')."\"", "files" => true])
		@component('components.labels.not-found', ['variant' => 'note'])
			<div>
				<li>El archivo debe tener una extensión .CSV</li>
				<li>Dé clic en el siguiente enlace para descargar la lista de catálogos para el llenado de la plantilla:
					<span class="inline-block">
						@component('components.buttons.button',
							[
								"variant" 		=> "secondary",
								"buttonElement" => "a",
								"attributeEx"	=> "href=\"".route('employee.export.catalogs')."\"",
								"classEx"		=> "employees-catalog",
								"label"			=> "Catálogos para plantilla"
							])
						@endcomponent
					</span>
				</li>
			</div>
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
					id="upload_file"
				@endslot
				SUBIR EMPLEADOS
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('#separatorComa').prop('checked',true);
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
			})
			.on('click','.employees-catalog',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				form = $('<form></form>').attr('method','POST').attr('action',url);
				form.append('@csrf');
				$('body').append(form);
				form.submit();
			});
		});
	</script>
@endsection
