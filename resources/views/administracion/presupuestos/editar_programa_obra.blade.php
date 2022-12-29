@extends('layouts.child_module')
@section('data')
	@if ($budgetUpload->status != 'Subiendo')
		@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('ObraProgram.finish')."\", id=\"container-alta\"", "files" => true])
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
							type="text" name="BudgetID" value="{{ $budgetUpload->id }}"
						@endslot
						@slot('classEx')
							hidden
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="name" placeholder="Ingrese el título" data-validation="required" value="{{ $budgetUpload->name }}"
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
						$optionsProject	=	[];
						foreach (App\Project::orderName()->get() as $project)
						{
							if ($budgetUpload->idproyect == $project->idproyect)
							{
								$optionsProject[] =
								[
									"value"			=>	$project->idproyect,
									"description"	=>	$project->proyectName,
									"selected"		=>	"selected"
								];
							}
							else
							{
								$optionsProject[] =
								[
									"value"			=>	$project->idproyect,
									"description"	=>	$project->proyectName
								];
							}
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
				<div class="col-span-2">
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
							Cliente:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="client" placeholder="Ingrese el cliente" data-validation="required" value="{{ $budgetUpload->client }}"
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
							Concurso No:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="contestNo" placeholder="Ingrese el concurso" data-validation="required" value="{{ $budgetUpload->contestNo }}"
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
							Obra:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="obra" placeholder="Ingrese la obra" data-validation="required" value="{{ $budgetUpload->obra }}"
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
							Lugar:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="place" placeholder="Ingrese el lugar" data-validation="required" value="{{ $budgetUpload->place }}"
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
						Ciudad:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
						value="{{ $budgetUpload->city }}" type="text" name="city" class="new-input-text remove" placeholder="Ingrese la ciudad" data-validation="required"
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
							Inicio de obra:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							data-default="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" id="startObra" value="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" type="text" name="startObra" data-validation="required"
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
							data-default="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" id="endObra" value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" type="text" name="endObra" data-validation="required"
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
							Duración:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							id="days" readonly disabled value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->diffInDays(Carbon\Carbon::parse($budgetUpload->startObra))  }}" type="text"
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
							Tipo de fecha:
					@endcomponent
					@php
						$optionsDateType	=	[];
						if ($budgetUpload->date_type == "Mes")
						{
							$optionsDateType[] =
							[
								"value"			=>	"Mes",
								"description"	=>	"Mes",
								"selected"		=>	"selected"
							];
							$optionsDateType[] =
							[
								"value"			=>	"Semana",
								"description"	=>	"Semana"
							];
						}
						if ($budgetUpload->date_type == "Semana")
						{
							$optionsDateType[] =
							[
								"value"			=>	"Mes",
								"description"	=>	"Mes",
							];
							$optionsDateType[] =
							[
								"value"			=>	"Semana",
								"description"	=>	"Semana",
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsDateType[] =
							[
								"value"			=>	"Mes",
								"description"	=>	"Mes",
							];
							$optionsDateType[] =
							[
								"value"			=>	"Semana",
								"description"	=>	"Semana",
							];
							
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionsDateType])
						@slot('attributeEx')
							name="date_type" multiple="multiple" data-validation="required"
						@endslot
						@slot('classEx')
							js-m-type removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-12">
				@component('components.buttons.button', ["variant"	=>	"red", "buttonElement" => "a"])
					@slot('attributeEx')
						href="{{ route('ObraProgram.delete', $budgetUpload->id) }}"
					@endslot
					@slot('label')
						ELIMINAR
					@endslot
				@endcomponent
				@component('components.buttons.button', ["variant"	=>	"success"])
					@slot('attributeEx')
						type="submit"
					@endslot
					@slot('label')
						GUARDAR
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.inputs.input-search')
			Buscar artículo
			@slot('attributeExInput')
				type="text" name="search" id="input-search" placeholder="Ingrese el artículo"
			@endslot
		@endcomponent
	@endif
	@if ($budgetUpload->status == 'Subiendo')
		@component('components.containers.container-form')
			<div class="col-span-4 text-center">
					@component('components.labels.label')
						Estado:
					@endcomponent
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
						@slot('attributeEx')
							id="status"
						@endslot
						{{ $budgetUpload->status }}
					@endcomponent
			</div>
			<div class="col-span-4 text-center">
					@component('components.labels.label')
						Última actualización:
					@endcomponent
					@component('components.labels.label')
						@slot('classEx')
							font-bold
						@endslot
						@slot('attributeEx')
							id="statusDate"
						@endslot
					@endcomponent
			</div>
		@endcomponent
	@endif
	<div id="table-return"></div>
	<div id="pagination"></div>
	@component('components.modals.modal', ["varian" => "large"])
		@slot('id')
			modalEdit
		@endslot
		@slot('modalBody')
			@component('components.labels.title-divisor')
				DETALLES DEL ARTÍCULO
			@endcomponent
				<div class="justify-center px-4 pt-4 mt-1">
					<div id="modal-content"></div>
				</div>
		@endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "secondary"])
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('classEx')
					send-edit
				@endslot
				@slot('label')
					Actualizar
				@endslot
			@endcomponent
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
					type="button"	data-dismiss="modal"
				@endslot
				@slot('label')
					Cancelar
				@endslot
			@endcomponent
		@endslot
	@endcomponent
{{-- todo------------------------	Inicia edición	------------------------ --}}






{{-- todo------------------------	Termina edición	------------------------ --}}


{{--  @@------------------------		Original	------------------------ --}}
{{-- @if ($budgetUpload->status != 'Subiendo')
	{!! Form::open(['route' => 'ObraProgram.finish', 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
	<div class="container-blocks">
		<div class="search-table-center">
			
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Título</label>
					</div>
					<div class="right">
						<input type="text" name="BudgetID" hidden value="{{ $budgetUpload->id }}">
						<input type="text" name="name" class="new-input-text remove" placeholder="Título" data-validation="required" value="{{ $budgetUpload->name }}">
					</div>
				</div>
			</div>
			
			<div class="search-table-center-row">
				<p style="padding-left: 15px; width: 97%;">
					<select class="js-project removeselect form-control" name="project_id" multiple="multiple" data-validation="required">
						@foreach(App\Project::orderName()->get() as $project)
							@if ($budgetUpload->idproyect == $project->idproyect)
							<option selected value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
							@else
								<option value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
							@endif
							
						@endforeach
					</select>
				</p>
			</div>
			
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">cliente</label>
					</div>
					<div class="right">
						<input value="{{ $budgetUpload->client }}" type="text" name="client" class="new-input-text remove" placeholder="cliente" data-validation="required">
					</div>
				</div>
			</div>

			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Concurso No</label>
					</div>
					<div class="right">
						<input value="{{ $budgetUpload->contestNo }}" type="text" name="contestNo" class="new-input-text remove" placeholder="Concurso No" data-validation="required">
					</div>
				</div>
			</div>

			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Obra</label>
					</div>
					<div class="right">
						<input value="{{ $budgetUpload->obra }}" type="text" name="obra" class="new-input-text remove" placeholder="Obra" data-validation="required">
					</div>
				</div>
			</div>

			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Lugar</label>
					</div>
					<div class="right">
						<input value="{{ $budgetUpload->place }}" type="text" name="place" class="new-input-text remove" placeholder="Lugar" data-validation="required">
					</div>
				</div>
			</div>

			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Ciudad</label>
					</div>
					<div class="right">
						<input value="{{ $budgetUpload->city }}" type="text" name="city" class="new-input-text remove" placeholder="Ciudad" data-validation="required">
					</div>
				</div>
			</div>
			
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Inicio de obra:</label>
					</div>
					<div class="right">
						<input data-default="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" id="startObra" value="{{ Carbon\Carbon::parse($budgetUpload->startObra)->format('d-m-Y')  }}" type="text" name="startObra" class="new-input-text remove" data-validation="required">
					</div>
				</div>
			</div>
			
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Fin de obra:</label>
					</div>
					<div class="right">
						<input data-default="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" id="endObra" value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->format('d-m-Y')  }}" type="text" name="endObra" class="new-input-text remove" data-validation="required">
					</div>
				</div>
			</div>
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Duración:</label>
					</div>
					<div class="right">
						<input id="days" readonly disabled value="{{ Carbon\Carbon::parse($budgetUpload->endObra)->diffInDays(Carbon\Carbon::parse($budgetUpload->startObra))  }}" class="new-input-text remove"  type="text">
					</div>
				</div>
			</div>

			<div class="search-table-center-row">
				<p style="padding-left: 15px; width: 97%;">
					<select class="js-m-type removeselect form-control" name="date_type" multiple="multiple" data-validation="required">
						
						<option @if ($budgetUpload->date_type == 'Mes') selected @endif value="Mes">Mes</option>
						<option @if ($budgetUpload->date_type == 'Semana') selected @endif value="Semana">Semana</option>
						
					</select>
				</p>
			</div>

			
		</div>


	</div>
	<br>
	<center>
		<button type="submit" formaction="{{ route('ObraProgram.delete') }}" class="btn btn-red">ELIMINAR</button>
		<button type="submit" class="btn btn-green">GUARDAR</button>
	</center>
	<br>

	{!! Form::close() !!}
	<center>
	
	
		<div class="container-search">
			<br>
			<label class="label-form">Buscar artículo</label>
			<br><br>
			<center>
				<input type="text" name="search" class="input-text" id="input-search" placeholder="Código, grupo, concepto, unidad..."> 
				<span class="icon-search"></span> 
			</center>
			<br><br>
		</div>
	
	</center>
@endif 
<center>


	@if ($budgetUpload->status == 'Subiendo')
		<div class="container-search">
			<br>
			<label class="label-form">Estado</label>

			<center>
				<span>
					<b>
						<label id="status">{{ $budgetUpload->status }}</label>
					</b>
				</span>
			</center>
			<br><br>
		</div>
		<div class="container-search">
			<br>
			<label class="label-form">Ultima actualización</label>

			<center>
				<span>
					<b>
						<label id="statusDate"></label>
					</b>
				</span>
			</center>
			<br><br>
		</div>
	@endif


</center>
<br>
<br>

<div id="table-return"></div>
<div id="pagination"></div>
<div class="modal" id="modalEdit" tabindex="-1" style="display: none;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<center>
					<strong>DETALLES DEL ARTÍCULO</strong>
				</center>
				<div class="divisor">
					<div class="gray-divisor"></div>
					<div class="orange-divisor"></div>
					<div class="gray-divisor"></div>
				</div>
				<br>
				<br>
				<center id="modal-content">
					
				</center>
			</div>
			<div class="modal-footer">
					<button type="button" class="btn btn-green send-edit">Actualizar</button>
					<button type="button" class="btn btn-blue" data-dismiss="modal">Cancelar</button>
			</div>
		</div>
	</div>
</div>
--}}






@endsection

@section('scripts')
  <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
  <script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/moment.js') }}"></script>
	<script type="text/javascript">

$.validate(
{
	form: '#container-alta',
	modules		: 'security',
	onSuccess : function($form)
	{
		status = $('#status').html()
		
		if (status == 'Subiendo') 
		{
			swal({
				title: "Error",
				text: "Debe esperar a que termine de cargar el documento.",
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
			statusDate = moment().format('DD-MM-YYYY HH:mm:ss');
			$('#statusDate').html(statusDate);
			getUploadStatus();
			$('input[name="startObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('input[name="endObra"]').datepicker({ dateFormat:'dd-mm-yy' });
			@php
				$selects = collect([
					[
						"identificator"				=> ".js-m-type",
						"placeholder"				=> "Seleccione el tipo de fecha",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
						"disabled"					=>	"false"
					],
					[
						"identificator"				=> ".js-project",
						"placeholder"				=> "Seleccione un proyecto",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
						"disabled"					=>	"false"
					],
					[
						"identificator"				=> ".js-measurement",
						"placeholder"				=> "Seleccione la unidad",
						"language"					=> "es",
						"maximumSelectionLength"	=> "1",
						"disabled"					=>	"false"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			paginate_arts(undefined,true)
		})
		$(document).on('click','.edit-item',function()
		{
			
			

			id  = $(this).parents('.row').find('.id-row').html().trim();

			swal(
			{
				closeOnClickOutside:false,
				closeOnEsc:false,
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});

			formData	= new FormData();
			formData.append('id', id);

			$.ajax({
			type		: 'post',
			url			: '{{ route("ObraProgram.pagintate.arts.edit") }}',
			data		: formData,
			contentType	: false,
			processData	: false,
			success		: function(data)
			{
				$('#modal-content').html(data.modal);
				swal.close();
				$('#modalEdit').modal('show');
				@php
					$selects = collect([
						[
							"identificator"				=> ".js-measurement",
							"placeholder"				=> "Seleccione la unidad",
							"language"					=> "es",
							"maximumSelectionLength"	=> "1",
							"disabled"					=>	"false"
						]
					]);
				@endphp
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
				$('.amount').numeric({ negative : false, altDecimal: ".", decimalPlaces: 25 });
			},
			error		: function()
			{
				swal('', 'Error al actualizar.', 'error')
			}
			});
		})
		.on('click','.send-edit',function(){
			
			swal(
			{
				closeOnClickOutside:false,
				closeOnEsc:false,
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			
			id          = $('input[name="id"]').val();
			type        = $('input[name="type"]').val();
			code        = $('input[name="code"]').val();
			concept     = $('input[name="concept"]').val();
			measurement = $('.js-measurement option:selected').text().trim();
			

			formData	= new FormData();
			formData.append('id', id);
			formData.append('type', type);
			formData.append('code', code);
			formData.append('concept', concept);
			formData.append('measurement', measurement);
			if(type === 'details')
				$('.amount').each(function(){
						formData.append('details['+$(this).attr('name')+']', $(this).val());
				})



			$.ajax({
			type		: 'post',
			url			: '{{ route("ObraProgram.article.edit") }}',
			data		: formData,
			contentType	: false,
			processData	: false,
			success		: function(data)
			{
				
				paginate_arts(undefined,false)
				swal('','Artículo actualizado.','success')
				$('#modalEdit').hide()
			},
			error		: function()
			{
				swal('', 'Error al actualizar.', 'error')
			}
			});
		})
		.on('change','#startObra,#endObra',function () {
			d1 = $('#startObra').val()
			d2 = $('#endObra').val()
			date1 = moment(d1,'DD-MM-YYYY');
			date2 = moment(d2,'DD-MM-YYYY');
			
			diffDays = date2.diff(date1, 'days')

			if(!moment(date1).isBefore(date2))
			{
				
				swal('', 'Error, la fecha de inicio de obra debe ser mayor que la de fin de obra.', 'error')
				.then(function () {
					set_default_dates()
				})
				return
			}

			$('#days').val(diffDays)
		})
		.on('change keyup paste','#input-search',function () {
			paginate_arts(undefined,true)
		})

		function set_default_dates() {
			default_date1 = $('#startObra').data("default")
			default_date2 = $('#endObra').data("default")
			$('#startObra').val(default_date1)
			$('#endObra').val(default_date2)

			d_date1 = moment(default_date1,'DD-MM-YYYY');
			d_date2 = moment(default_date2,'DD-MM-YYYY');
			
			d_diffDays = d_date2.diff(d_date1, 'days')
			$('#days').val(d_diffDays)
		}

		function paginate_arts(page =undefined,firstSearch = false) {
			if(!firstSearch)
			{
				swal(
				{
					closeOnClickOutside:false,
					closeOnEsc:false,
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
			}
			search = $('#input-search').val()
			$.ajax(
			{
				type : 'get',
				url  : '{{ route("ObraProgram.paginate.arts") }}',
				data : {
					'page':page,
					'budgetUpload':{{ $budgetUpload->id }},
					'search':search,
					},
				success : function(response)
				{

					statusDate = moment().format('DD-MM-YYYY HH:mm:ss')
					$('#statusDate').html(statusDate)
					oldStatus = $('#status').html()
					newStatus = response.ObraProgramUploads.status

					data = response
					if(oldStatus == 'Subiendo' && newStatus != 'Subiendo')
					{
						location.reload(true);
						return
					}
					$('#status').html(response.ObraProgramUploads.status)
					if(response.ObraProgramUploads.status != 'Subiendo')
					{
						$('#table-return').html(response.table);
						$('#pagination').html(response['pagination']);
						
						
						$('.page-link').on('click', function(e){
								e.preventDefault();
								page = $(this).text();
								if($(this).text() === "›"){
									if(response.data.current_page + 1 > response.data.last_page)
										return;
									page = response.data.current_page + 1
								}
								if($(this).text() === "‹"){
									if(response.data.current_page - 1 <= 0)
										return;
									page = response.data.current_page - 1
								}
								paginate_arts(page)
						});
					}
					if(!firstSearch){
						swal.close()
						window.location = '#table'
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			})
		}
		function getUploadStatus() {
			status = $('#status').html()
			if(status == 'Subiendo')
			{
				paginate_arts(undefined,true)
				setTimeout(() => {

					getUploadStatus()
				}, 30000);
			}
		}
	</script>
@endsection