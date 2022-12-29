@section('css')
	<style type="text/css">

		.inputfile
		{
			height		: 0.1px;
			opacity		: 0;
			overflow	: hidden;
			position	: absolute;
			width		: 0.1px;
			z-index		: -1;
		}
		.inputfile + label
		{
			background-color	: #eb3621;
			color				: #fff;
			cursor				: pointer;
			display				: inline-block;
			font-size			: 1.25rem;
			font-weight			: 700;
			width: 97%;
			overflow			: hidden;
			padding				: 0.625rem 1.25rem;
			text-overflow		: ellipsis;
			white-space			: nowrap;
		}
		.inputfile + label svg
		{
			fill			: currentColor;
			height			: 1em;
			margin-right	: 0.25em;
			margin-top		: -0.25em;
			vertical-align	: middle;
			width			: 1em;
		}
		.inputfile:focus + label,
		.inputfile + label:hover
		{
			background-color	: #db3831;
		}
		ul
		{
			list-style		: disc;
			padding-left	: .5em;
		}
	</style>
@endsection

{!! Form::open(['route' => $route, 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
	<div class="container-blocks">
		<div class="search-table-center">
			
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Título</label>
					</div>
					<div class="right">
						<input type="text" name="name" class="new-input-text remove" placeholder="Título" required>
					</div>
				</div>
			</div>
			
			<div class="search-table-center-row">
				<p style="padding-left: 15px; width: 97%;">
					<select class="js-project removeselect form-control" name="project_id" multiple="multiple" data-validation="required">
						@foreach(App\Project::orderName()->get() as $project)
							<option value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>			
						@endforeach
					</select>
				</p>
			</div>
			<div class="search-table-center-row">
				<p style="padding-left: 15px; width: 100%;">
					<input type="file" name="file" id="csv" class="inputfile inputfile-1" accept=".xlsx,.xls"/>
					<label for="csv"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccione un archivo&hellip;</span></label>
				</p>
			</div>
		</div>
	</div>
	<br>
	<center>
		<button type="submit" class="btn btn-green">SUBIR</button>
	</center>
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
				
				if (path == undefined || path == "") 
				{
					swal({
						title: "Error",
						text: "Debe agregar un documento.",
						icon: "error",
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
		});
		$('.js-project').select2(
		{
			placeholder: 'Seleccione un proyecto',
			language: "es",
			maximumSelectionLength: 1,
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
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
