@extends('layouts.child_module')

@section('css')
	<style type="text/css">
		.btn svg
		{
			fill: currentColor;
		}
	</style>
@endsection
  
@section('data')
{!! Form::open(['route' => 'budget.search', 'method' => 'GET', 'id' => 'formsearch','files'=>true]) !!}
		<center>
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Tipo:</label>
					</div>
					<div class="right">
						<p>
							<select class="js-type removeselect form-control" style="width: 98%; border: 0px;" name="type" multiple="multiple">
								@if (Auth::user()->module->where('id',222)->count()>0)
									<option value="desgloseSalarios">Desglose de Salarios</option>
								@endif
								@if (Auth::user()->module->where('id',219)->count()>0)
									<option value="insumo">Listado de Insumos</option>
								@endif
								@if (Auth::user()->module->where('id',224)->count()>0)
									<option value="preciosUnitarios">Precios Unitarios</option>
								@endif
								@if (Auth::user()->module->where('id',221)->count()>0)
									<option value="presupuesto">Presupuestos</option>
								@endif
								@if (Auth::user()->module->where('id',226)->count()>0)
									<option value="programaObra">Programa de Obra</option>
								@endif
								@if (Auth::user()->module->where('id',227)->count()>0)
									<option value="sobrecosto">Sobrecosto</option>
								@endif
              </select>
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Nombre:</label>
					</div>
					<div class="right">
						<p><input type="text" name="name" class="input-text-search" placeholder="Ingrese un nombre"></p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Proyecto:</label>
					</div>
					<div class="right">
						<p>
              <select class="js-project removeselect form-control" style="width: 98%; border: 0px;" name="project_id" multiple="multiple">
                @foreach(App\Project::orderName()->get() as $project)
                
                  <option  value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
                @endforeach
              </select>
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Inicio de obra:</label>
					</div>
					<div class="right">
						<input autocomplete="off" id="startObra" type="text" name="startObra" class="new-input-text remove">
					</div>
				</div>
				<br>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Fin de obra:</label>
					</div>
					<div class="right">
						<input autocomplete="off" id="endObra" type="text" name="endObra" class="new-input-text remove">
					</div>
				</div>
				<br>
				
			</div>
		</center>
		<center>
			<button class="btn btn-search" type="button"><span class="icon-search"></span> Buscar</button>
		</center>
	<br>
	{!! Form::close() !!}

		<div id="table-return"></div>
		<div id="pagination"></div>
		
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
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
			$('.js-type').select2(
			{
				placeholder: 'Seleccione un tipo',
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
			$('input[name="startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
		})
		.on('click','.btn-search',function ()
		{
			switch ($('.js-type option:selected').val())
			{
				case 'insumo':
					paginate_arts_budget()
					break;
				case 'presupuesto':
					paginate_arts_supplies()
					break;
				case 'desgloseSalarios':
					paginate_arts_BreakdownWages()
					break;
				case 'preciosUnitarios':
					paginate_arts_UnitPrices()
					break;
				case 'programaObra':
					paginate_arts_ObraProgram()
					break;
				case 'sobrecosto':
					paginate_arts_Sobrecosto()
					break;
			
				default:
					break;
			}
		});
		function paginate_arts_supplies(page = undefined, first = false)
		{
			project_id = $('select[name="project_id"] option:selected').val();
			name       = $('input[name="name"]').val();
			startObra  = $('input[name="startObra"]').val();
			endObra    = $('input[name="endObra"]').val();
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("budget.search.paginate") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_supplies(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
		function paginate_arts_budget(page = undefined, first = false)
		{
			project_id = $('select[name="project_id"] option:selected').val();
			name       = $('input[name="name"]').val();
			startObra  = $('input[name="startObra"]').val();
			endObra    = $('input[name="endObra"]').val();
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("/administration/budgets/search/paginate_search") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response;
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_budget(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
		function paginate_arts_BreakdownWages(page = undefined, first = false)
		{
			project_id = $('select[name="project_id"] option:selected').val();
			name       = $('input[name="name"]').val();
			startObra  = $('input[name="startObra"]').val();
			endObra    = $('input[name="endObra"]').val();
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("breakdownWages.search.paginate") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response;
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_BreakdownWages(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
		function paginate_arts_UnitPrices(page = undefined, first = false)
		{
			project_id	= $('select[name="project_id"] option:selected').val();
			name 				= $('input[name="name"]').val()
			startObra 	= $('input[name="startObra"]').val()
			endObra 		= $('input[name="endObra"]').val()
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("UnitPrices.search.paginate") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response;
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_UnitPrices(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
		function paginate_arts_ObraProgram(page = undefined, first = false)
		{
			project_id	= $('select[name="project_id"] option:selected').val();
			name 				= $('input[name="name"]').val()
			startObra 	= $('input[name="startObra"]').val()
			endObra 		= $('input[name="endObra"]').val()
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("Obra.search.paginate") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_ObraProgram(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
		function paginate_arts_Sobrecosto(page = undefined, first = false)
		{
			project_id	= $('select[name="project_id"] option:selected').val();
			name 				= $('input[name="name"]').val()
			startObra 	= $('input[name="startObra"]').val()
			endObra 		= $('input[name="endObra"]').val()
			if(!first)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("Sobrecosto.search.paginate") }}',
				data : {
					'page':page,
					'project_id':project_id,
					'name':name,
					'startObra':startObra,
					'endObra':endObra,
				},
				success : function(response)
				{
					data = response;
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e)
					{
						e.preventDefault();
						page = $(this).text();
						if($(this).text() === "›")
						{
							if(response.data.current_page + 1 > response.data.last_page)
							{
								return;
							}
							page = response.data.current_page + 1;
						}
						if($(this).text() === "‹")
						{
							if(response.data.current_page - 1 <= 0)
							{
								return;
							}
							page = response.data.current_page - 1;
						}
						paginate_arts_Sobrecosto(page);
					});
					if(!first)
					{
						swal.close();
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#table-return').html('');
					$('#pagination').html('');
				}
			});
		}
	</script>
@endsection
