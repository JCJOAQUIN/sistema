@extends('layouts.child_module')
@section('data')
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" action=\"".route('employee.massive.upload')."\" id=\"employee_massive\" accept-charset=\"UTF-8\" enctype=\"multipart/form-data\""])
		@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
		@component("components.labels.not-found", ["variant" => "note", "title" => ""])
			@component("components.labels.label")
				Dé clic en el siguiente enlace para descargar la plantilla para la carga masiva: 
				<span class="inline-block">
					@component('components.buttons.button', ["variant" =>	"success", "buttonElement" => "a"])
						@slot('attributeEx')
							type="button"
							href="{{route('employee.massive.template')}}"
						@endslot
						@slot('label')
							PLANTILLA PARA EMPLEADOS
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
							href="{{ route('employee.export-layout') }}"
						@endslot
						@slot('label')
							LISTA DE EMPLEADOS
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
							"label"	=> "SUBIR EMPLEADOS",
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

@section('scripts')
	<script type="text/javascript">
		labelVal = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span>';
		$(document).ready(function()
		{
			$('#separatorComa').prop('checked',true);
			$.validate(
			{
				form: '#employee_massive',
				onError   : function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					swal({
						title              : 'Cargando',
						icon               : '{{ asset(getenv("LOADING_IMG")) }}',
						button             : false,
						closeOnClickOutside: false,
						text               : 'Este proceso puede demorar',
						closeOnEsc         : false
					});
					return true;
				}
			});
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
			.on('click','.employees-list,.employees-catalog',function(e)
			{
				e.preventDefault();
				url = $(this).attr('href');
				form = $('<form></form>').attr('method','POST').attr('action',url);
				form.append('@csrf');
				$('body').append(form);
				form.submit();
				$('#submitButton').removeAttr('disabled');
			});
		});
	</script>
@endsection
