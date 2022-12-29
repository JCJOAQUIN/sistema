{!! Form::open(['route' => $route, 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label')
				@slot('classEx')
					font-bold
				@endslot
				Título:
			@endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="name" placeholder="Ingrese el título" data-validation="required"
				@endslot
				@slot('classEx')
					remove
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')
				@slot('classEx')
					font-bold
				@endslot
				Proyecto:
			@endcomponent
			@php
				foreach (App\Project::orderName()->get() as $project)
				{
					$optionsProject[] =
					[
						"value"			=>	$project->idproyect,
						"description"	=>	$project->proyectName
					];
				}
			@endphp
			@component('components.inputs.select', ["options" => $optionsProject])
				@slot('attributeEx')
					name="project_id" multiple="multiple" data-validation="required"
				@endslot
				@slot('classEx')
					js-project removeselect
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@php
				if (isset($request))
				{
					if ($request->status == 2)
					{
						$buttonExload =
						[
							["kind"	=>	"components.buttons.button",	"label"	=>	"CARGAR ARCHIVO",	"variant"	=>	"primary",	"attributeEx"	=>	"type=\"submit\" id=\"upload_file\" formaction=\"".route('work_order.save-follow',$request->folio)."\""],
						];
					}
				}
				else
				{
					$buttonExload =
					[
						["kind"	=>	"components.buttons.button",	"label"	=>	"CARGAR ARCHIVO",	"variant"	=>	"primary",	"attributeEx"	=>	"type=\"submit\" id=\"upload_file\" formaction=\"".route('work_order.store.detail')."\""],
					];
				}
				
			@endphp
			@component("components.documents.select_file_csv", ["attributeExInput" => "name=\"file\" id=\"csv\" accept=\".xlsx,.xls\""])@endcomponent
		</div>
	@endcomponent
	<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
		@component('components.buttons.button')
			@slot('attributeEx')
				type="submit"
			@endslot
			@slot('label')
				SUBIR
			@endslot
		@endcomponent
	</div>
{!! Form::close() !!}

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/select2.min.js') }}"></script>
	<script type="text/javascript">
		$.validate(
		{
			form: '#container-alta',
			modules		: 'security',
			onSuccess : function($form)
			{
				path = $('#csv').val();
				extension = (path.substring(path.lastIndexOf("."))).toLowerCase();
				if(extension != ".xls" && extension != ".xlsx")
				{
					swal('','El tipo de archivo no es válido, favor de verificar.','error');
					event.preventDefault();
				}

				if (path == undefined || path == "") 
				{
					swal({
						title  : "Error",
						text   : "Debe agregar un documento.",
						icon   : "error",
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
		$(document).ready(function ()
		{
			$('input[name="initial_date"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="end_date"]').datepicker({ dateFormat:'dd-mm-yy' });
		})
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-project",
					"placeholder"				=> "Seleccione un proyecto",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1",
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('change','#csv',function(e)
		{
			labelVal	= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span>';
			label		= $(this).next('label');
			fileName	= e.target.value.split( '\\' ).pop();
			if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				this.val('');
				label.html(labelVal);
				return
			}
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
