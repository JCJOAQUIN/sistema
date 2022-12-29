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
		@component("components.forms.searchForm",["variant" => "default", "classExButtonSearch" => "btn-search", "attributeExButtonSearch" => "type=\"button\""])
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Tipo:
				@endcomponent
				@php
					$optionsType[]	=	["value"	=>	"desgloseSalarios",	"description"	=>	"Desglose de Salarios"];
					$optionsType[]	=	["value"	=>	"insumo",			"description"	=>	"Listado de Insumos"];
					$optionsType[]	=	["value"	=>	"preciosUnitarios",	"description"	=>	"Precios Unitarios"];
					$optionsType[]	=	["value"	=>	"presupuesto",		"description"	=>	"Presupuestos"];
					$optionsType[]	=	["value"	=>	"programaObra",		"description"	=>	"Programa de Obra"];
					$optionsType[]	=	["value"	=>	"sobrecosto",		"description"	=>	"Sobrecosto"];
				@endphp
				@component('components.inputs.select', ["options" => $optionsType])
					@slot('attributeEx')
						name="type" multiple="multiple"
					@endslot
					@slot('classEx')
						js-type removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Nombre:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="name" placeholder="Ingrese un nombre"
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
				@component('components.inputs.select', ["options" => []])
					@slot('attributeEx')
						name="project_id" multiple="multiple"
					@endslot
					@slot('classEx')
						js-project removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Inicio de obra:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						autocomplete="off" id="startObra" type="text" name="startObra"  placeholder="Ingrese el inicio de obra"
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
					Fin de obra:
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						autocomplete="off" id="endObra" type="text" name="endObra" placeholder="Ingrese el fin de obra"
					@endslot
					@slot('classEx')
						remove
					@endslot
				@endcomponent
			</div>
		@endcomponent
	{!! Form::close() !!}
	<div id="table-return"></div>
	<div id="pagination"></div>
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-project",
						"placeholder"				=> "Seleccione un proyecto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					],
					[
						"identificator"				=> ".js-type",
						"placeholder"				=> "Seleccione un tipo",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-project', 'model': 24});
			$('input[name="startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
		})
		.on('click','.btn-search',function ()
		{
			type = $('[name="type"]').val();
			if (type == "")
			{
				swal('','Debe de seleccionar el tipo de presupuesto para realizar la búsqueda.','info');
			}
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
				url  : '{{ route("supplies.search.paginate") }}',
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
					data = response
					$('#table-return').html(response.table);
					$('#pagination').html(response['pagination']);
					$('.page-link').on('click', function(e){
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
			})
		}
		function paginate_arts_ObraProgram(page = undefined, first = false)
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
					icon	:'{{ asset(getenv('LOADING_IMG')) }}',
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
