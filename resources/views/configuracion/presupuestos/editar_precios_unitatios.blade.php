@extends('layouts.child_module')
@section('data')

@if ($budgetUpload->status != 'Subiendo')
{!! Form::open(['route' => 'UnitPrices.finish', 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
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

		
	</div>


</div>
<br>
<center>
	<button type="submit" formaction="{{ route('UnitPrices.delete') }}" class="btn btn-red">ELIMINAR</button>
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
			<label class="label-form">Estatus</label>

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
				@component('components.labels.title-divisor')    DETALLES DEL ARTÍCULO @endcomponent
				<br>
				<br>
				<center>
					<div class="search-table-center">
						<input hidden name="id">
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Código</label>
							</div>
							<div class="right">
								<input type="text" name="code" class="new-input-text remove" placeholder="Código" data-validation="required">
							</div>
						</div>
						<br>
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Concepto</label>
							</div>
							<div class="right">
								<input type="text" name="concept" class="new-input-text remove" placeholder="Concepto" data-validation="required">
							</div>
						</div>
						<br>
						<div class="search-table-center-row">
								<select class="js-measurement removeselect form-control" name="measurement" multiple="multiple">
									@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
										@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
											<option value="{{ $child->id }}">{{ $child->abbreviation }}</option>			
										@endforeach
									@endforeach
								</select>
						</div>
						<br>
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Cantidad</label>
							</div>
							<div class="right">
								<input type="text" name="amount" class="new-input-text remove" placeholder="Cantidad" data-validation="required">
							</div>
						</div>
						<br>
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Precio</label>
							</div>
							<div class="right">
								<input type="text" name="price" class="new-input-text remove" placeholder="Precio" data-validation="required">
							</div>
						</div>
						<br>
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Importe</label>
							</div>
							<div class="right">
								<input type="text" name="import" class="new-input-text remove" placeholder="Importe" data-validation="required">
							</div>
						</div>
						<br>
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">% Incidencia</label>
							</div>
							<div class="right">
								<input type="text" name="incidence" class="new-input-text remove" placeholder="% Incidencia" data-validation="required">
							</div>
						</div>
	
					</div>
				</center>
			</div>
			<div class="modal-footer">
					<button type="button" class="btn btn-green send-edit">Actualizar</button>
					<button type="button" class="btn btn-blue" data-dismiss="modal">Cancelar</button>
			</div>
		</div>
	</div>
</div>


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
			$('.js-measurement').select2({
				placeholder: 'Seleccione la unidad (Medición)',
				language: "es",
				maximumSelectionLength: 1,
				disabled:false
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			paginate_arts(undefined,true);
		});
		$(document).on('click','.edit-item',function()
		{
			id          = $(this).parents('tr').find('.id').html();
			code        = $(this).parents('tr').find('.code').html();
			concept     = $(this).parents('tr').find('.concept').html();
			measurement = $(this).parents('tr').find('.measurement').html();
			date        = $(this).parents('tr').find('.date').html();
			amount      = $(this).parents('tr').find('.amount').html();
			price       = $(this).parents('tr').find('.price').html();
			n_import    = $(this).parents('tr').find('.import').html();
			incidence   = $(this).parents('tr').find('.incidence').html();
			cat_names = [
				@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
					@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
						{
							'id':{{ $child->id }},
							'value':'{{ $child->abbreviation }}'
						},
					@endforeach
				@endforeach
			];
			cat = cat_names.find(element => element.value === measurement);
			if(cat)
			{
				cat=cat.id;
			}
			$('input[name="id"]').val(id);
			$('input[name="code"]').val(code);
			$('input[name="concept"]').val(concept);
			$('input[name="date"]').val(date);
			$('input[name="amount"]').val(amount);
			$('input[name="price"]').val(price);
			$('input[name="import"]').val(n_import);
			$('input[name="incidence"]').val(incidence);
			$('#modalEdit').show();
			$('input[name="date"]').datepicker({ dateFormat:'dd-mm-yy' });
			$('.js-measurement').select2(
			{
				placeholder: 'Seleccione la unidad (Medición)',
				language: "es",
				maximumSelectionLength: 1,
				disabled:false
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('input[name="amount"],input[name="price"],input[name="import"],input[name="incidence"]').numeric({ negative : false, altDecimal: ".", decimalPlaces: 25 });
			$('.js-measurement').val(cat).trigger('change');
		})
		.on('click','.send-edit',function()
		{
			swal(
			{
				closeOnClickOutside:false,
				closeOnEsc:false,
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			id          = $('input[name="id"]').val();
			code        = $('input[name="code"]').val();
			concept     = $('input[name="concept"]').val();
			date        = $('input[name="date"]').val();
			amount      = $('input[name="amount"]').val();
			price       = $('input[name="price"]').val();
			n_import    = $('input[name="import"]').val();
			incidence   = $('input[name="incidence"]').val();
			measurement = $('.js-measurement option:selected').text().trim()
			formData    = new FormData();
			formData.append('id', id);
			formData.append('code', code);
			formData.append('concept', concept);
			formData.append('date', date);
			formData.append('amount', amount);
			formData.append('price', price);
			formData.append('import', n_import);
			formData.append('incidence', incidence);
			formData.append('measurement', measurement);
			$.ajax({
				type		: 'post',
				url			: '{{ url("/administration/budgets/article/unit_prices/edit") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(data)
				{
					tr = $('#id-'+id)
					tr.find('.code').html(code);
					tr.find('.concept').html(concept);
					tr.find('.measurement').html(measurement);
					tr.find('.date').html(date);
					tr.find('.amount').html(amount);
					tr.find('.price').html(price);
					tr.find('.import').html(n_import);
					tr.find('.incidence').html(incidence);
					swal('','Artículo actualizado.','success')
					$('#modalEdit').hide()
				},
				error		: function()
				{
					swal('', 'Error al actualizar.', 'error')
				}
			});
		})
		.on('change','#startObra,#endObra',function ()
		{
			d1 = $('#startObra').val()
			d2 = $('#endObra').val()
			date1 = moment(d1,'DD-MM-YYYY');
			date2 = moment(d2,'DD-MM-YYYY');
			diffDays = date2.diff(date1, 'days');
			if(!moment(date1).isBefore(date2))
			{
				swal('', 'Error, la fecha de inicio de obra debe ser mayor que la de fin de obra.', 'error')
				.then(function () {
					set_default_dates();
				})
				return
			}
			$('#days').val(diffDays);
		})
		.on('change keyup paste','#input-search',function ()
		{
			paginate_arts(undefined,true);
		})

		function set_default_dates()
		{
			default_date1 = $('#startObra').data("default");
			default_date2 = $('#endObra').data("default");
			$('#startObra').val(default_date1);
			$('#endObra').val(default_date2);
			d_date1 = moment(default_date1,'DD-MM-YYYY');
			d_date2 = moment(default_date2,'DD-MM-YYYY');
			d_diffDays = d_date2.diff(d_date1, 'days');
			$('#days').val(d_diffDays);
		}

		function paginate_arts(page =undefined,firstSearch = false)
		{
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
			search = $('#input-search').val();
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("/administration/budgets/create/unit_prices/paginate_arts") }}',
				data : {
					'page':page,
					'budgetUpload':{{ $budgetUpload->id }},
					'search':search,
					},
				success : function(response)
				{
					statusDate = moment().format('DD-MM-YYYY HH:mm:ss');
					$('#statusDate').html(statusDate);
					oldStatus = $('#status').html();
					newStatus = response.UnitPricesUploads.status;
					if(oldStatus == 'Subiendo' && newStatus != 'Subiendo')
					{
						location.reload(true);
						return;
					}
					$('#status').html(response.UnitPricesUploads.status);
					data = response;
					if(response.UnitPricesUploads.status != 'Subiendo')
					{
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
							paginate_arts(page,false);
						});
					}
					if(!firstSearch)
					{
						swal.close();
						window.location = '#table';
					}
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
		}
		function getUploadStatus()
		{
			status = $('#status').html();
			if(status == 'Subiendo')
			{
				paginate_arts(undefined,true);
				setTimeout(() => {
					getUploadStatus()
				}, 30000);
			}
		}
	</script>
@endsection
