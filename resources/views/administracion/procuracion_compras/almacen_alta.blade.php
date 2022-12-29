@extends('layouts.child_module')
  
@section('data')
	{!! Form::open(['route' => 'procurement-purchases.warehouse-save', 'method' => 'POST', 'id' => 'container-alta','files' => true]) !!}
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark" style="min-width: 100%;">
					<th colspan="4">RESUMEN DE ORDEN DE COMPRA</th>
				</thead>
			</table>
		</div>
		<div class="form-row px-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Cuenta:</b></label>
				<label>{{ $purchase->account != "" ? $purchase->accountData->name : 'Sin Cuenta' }}</label>
				<input type="hidden" name="folio" value="{{ $purchase->id }}">
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Proyecto:</b></label>
				<label>{{ $purchase->project()->exists() ? $purchase->project->proyectName : 'Sin Proyecto' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4 select_father" @if(isset($purchase)) @if($purchase->idProject == 75) style="display: table-row;" @endif @else style="display: table-row;" @endif>
				<label><b>Código WBS:</b></label>
				<label>{{ $purchase->code_wbs!="" ? $purchase->wbs->code_wbs : 'Sin WBS' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Moneda:</b></label>
				<label>{{ $purchase->type_currency != "" ? $purchase->type_currency : 'Sin Moneda' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Estatus:</b></label>
				<label>{{ $purchase->statusRequest->description }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Número OC:</b></label>
				<label>{{ $purchase->numberOrder != "" ? $purchase->numberOrder : 'Sin Número de Orden' }}</label>
			</div>
			
			<div class="form-group col-md-6 mb-4">
				<label><b>CO#:</b></label>
				<label>{{ $purchase->numberCO != "" ? $purchase->numberCO : 'Sin CO#' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Descripción:</b></label>
				<label>{{ $purchase->descriptionShort != "" ? $purchase->descriptionShort : 'Sin Descripción' }}</label>
			</div>
			
			<div class="form-group col-md-6 mb-4">
				<label><b>Fecha req. en sitio:</b></label>
				<label>{{ $purchase->date_obra != "" ? $purchase->date_obra->format('Y-m-d') : 'Sin Fecha' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Fecha promesa entrega:</b></label>
				<label>{{ $purchase->date_promise != "" ? $purchase->date_promise->format('Y-m-d') : 'Sin Fecha' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Fecha Elaboración:</b></label>
				<label>{{ $purchase->date_request != "" ? $purchase->date_request->format('Y-m-d') : 'Sin Fecha' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Fecha Cierre:</b></label>
				<label>{{ $purchase->date_close != "" ? $purchase->date_close->format('Y-m-d') : 'Sin Fecha' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Destino:</b></label>
				<label>{{ $purchase->destination != "" ? $purchase->destination : 'Sin Destino' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Sitio:</b></label>
				<label>{{ $purchase->site != "" ? $purchase->site : 'Sin Sitio' }}</label>
			</div>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark" style="min-width: 100%;">
					<th colspan="4">DESCRIPCIÓN DE COMPRA</th>
				</thead>
			</table>
		</div>
		<div class="form-row px-3">
			<div class="form-group col-md-12 mb-4">
				<label><b>Descripción:</b></label>
				<label>{{ $purchase->descriptionLong!="" ? $purchase->descriptionLong : 'Sin Descripción' }}</label>
			</div>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark" style="min-width: 100%;">
					<th colspan="4">DATOS DE PROVEEDOR</th>
				</thead>
			</table>
		</div>
		<div class="form-row px-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Proveedor:</b></label>
				<label>{{ $purchase->provider != "" ? $purchase->provider : 'Sin Datos' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Ubicación:</b></label>
				<label>{{ $purchase->ubicationProvider != "" ? $purchase->ubicationProvider : 'Sin Datos' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Contacto:</b></label>
				<label>{{ $purchase->contactProvider != "" ? $purchase->contactProvider : 'Sin Datos' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Teléfono:</b></label>
				<label>{{ $purchase->phoneProvider != "" ? $purchase->phoneProvider : 'Sin Datos' }}</label>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Email:</b></label>
				<label>{{ $purchase->emailProvider != "" ? $purchase->emailProvider : 'Sin Datos' }}</label>
			</div>
		</div>
		<p><br></p>
		<div class="alert alert-info" id="error_request" role="alert">
			<b>Fecha(1):</b> Fecha req. en sitio. <br>
			<b>Fecha(2):</b> Fecha promesa entrega.
		</div>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<tr>
						<th colspan="12">CONCEPTOS DE ÓRDEN DE COMPRA</th>
					</tr>
					<tr>
						<th>Partida</th>
						<th>Código Mat.</th>
						<th>Medida</th>
						<th>Descripción</th>
						<th>Cant.</th>
						<th>Precio</th>
						<th>Total</th>
						<th>Moneda</th>
						<th>Fecha (1)</th>
						<th>Fecha (2)</th>
						<th>Estatus</th>
						<th>Acción</th>
					</tr>
				</thead>
				<tbody class="request-validate" id="pending_items">
					@foreach($purchase->details as $detail)
						<tr>
							<td>
								{{ $detail->part }} 
								@if($detail->warehouseStatus == 0)
									<input type="hidden" class="id_detail_add" name="pending_items[]" value="{{ $detail->id }}">
									<input type="hidden" class="code_add" value="{{ $detail->code }}">
									<input type="hidden" class="unit_add" value="{{ $detail->unit }}">
									<input type="hidden" class="description_add" value="{{ $detail->description }}">
									<input type="hidden" class="quantity_add" value="{{ $detail->quantity }}">
									<input type="hidden" class="price_add" value="{{ $detail->price }}">
									<input type="hidden" class="total_concept_add" value="{{ $detail->total_concept }}">
								@endif
							</td>
							<td>{{ $detail->code }} </td>
							<td>{{ $detail->unit }} </td>
							<td>{{ $detail->description }} </td>
							<td>{{ $detail->quantity }} </td>
							<td>{{ $detail->price }}</td>
							<td>{{ $detail->total_concept }}</td>
							<td>{{ $detail->type_currency }}</td>
							<td>{{ $detail->date_one->format('Y-m-d') }}</td>
							<td>{{ $detail->date_two->format('Y-m-d') }}</td>
							<td>{{ $detail->warehouseStatus() }}</td>
							<td>
								@if($detail->warehouseStatus == 0)
									<button class="btn btn-green add-art" type="button">
										<span class="icon-plus"></span>
									</button>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<p><br></p>
		<div class="form-warehouse" style="display:none">
			<div class="table-responsive">
				<table class="table">
					<thead class="thead-dark" style="min-width: 100%;">
						<th colspan="4">DATOS PARA CARGAR A ALMACÉN</th>
					</thead>
				</table>
			</div>
			<div class="container-blocks" id="container-data">
				<div class="search-table-center">
					<div class="search-table-center">
						<div class="search-table-center-row">
							<div class="left">
								<label class="label-form">Descripción</label>
							</div>
							<div class="right">
								<input readonly type="text" class="new-input-text description" placeholder="Descripción">
								<input type="hidden" class="id_detail" class="disabled">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Medida</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text remove measure" placeholder="Medida">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Código Mat</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text remove code_mat disabled" placeholder="Ingrese el código">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Cantidad de artículos sin dañar</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text remove quantity_not_damaged disabled" value="0">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Cantidad de artículos dañados</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text remove damaged disabled" value="0">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Total de artículos recibidos</label>
							</div>
							<div class="right">
								<input readonly type="text" class="new-input-text remove quantity disabled" placeholder="0">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Precio unitario</label>
							</div>
							<div class="right">
								<input readonly type="text" class="new-input-text remove unit_price disabled" placeholder="$0.00">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Importe</label>
							</div>
							<div class="right">
								<input readonly type="text" class="new-input-text remove total_art disabled" placeholder="$0.00">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Fecha de entrega</label>
							</div>
							<div class="right">
								<input readonly type="text" class="new-input-text remove date_entry disabled" placeholder="Seleccione una fecha">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Comentario (Opcional)</label>
							</div>
							<div class="right">
								<textarea id="commentaries" name="commentaries" cols="20" rows="4" placeholder="Ingrese el comentario" class="new-input-text"></textarea>
							</div>
						</div>
						<center>
							<button type="button" class="btn btn-green add-warehouse">
								<span class="icon-plus"></span>
								<span>AGREGAR</span>
							</button>
						</center>
					</div>
				</div>
			</div>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<tr>
						<th colspan="10">CONCEPTOS A REGISTRAR</th>
					</tr>
					<tr>
						<th>Código Mat.</th>
						<th>Medida</th>
						<th>Descripción</th>
						<th>Artículos no dañados</th>
						<th>Artículos dañados</th>
						<th>Total de Artículos</th>
						<th>Precio Unitario</th>
						<th>Importe</th>
						<th>Fecha de entrega</th>
						<th>Comentarios</th>
					</tr>
				</thead>
				<tbody id="body_art" class="request-validate">
					
				</tbody>
			</table>
		</div>
		<p><br></p>
		<center>
			<button type="submit" class="btn btn-red" name="send">
				<i class="fas fa-save"></i> CARGAR ARTÍCULOS
			</button>
		</center>
	{!! Form::close() !!}
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('.date_entry').datepicker({  dateFormat: "yy-mm-dd" });
			$(document).on('click','.add-art',function()
			{
				id_detail_add		= $(this).parents('tr').find('.id_detail_add').val();
				code_add			= $(this).parents('tr').find('.code_add').val();
				unit_add			= $(this).parents('tr').find('.unit_add').val();
				description_add		= $(this).parents('tr').find('.description_add').val();
				quantity_add		= $(this).parents('tr').find('.quantity_add').val();
				price_add			= $(this).parents('tr').find('.price_add').val();
				total_concept_add	= $(this).parents('tr').find('.total_concept_add').val();

				$('.id_detail').val(id_detail_add);
				$('.description').val(description_add);
				$('.measure').val(unit_add);
				$('.code_mat').val(code_add);
				$('.quantity').val(quantity_add);
				$('.unit_price').val(price_add);
				$('.total_art').val(total_concept_add);
				$('.form-warehouse').fadeIn(300);
				$('.quantity_not_damaged').val(quantity_add);
				$(this).parents('tr').remove();
			})
			.on('click','.add-warehouse',function()
			{
				id_detail				= $('.id_detail').val();
				description				= $('.description').val();
				measure					= $('.measure').val();
				code_mat				= $('.code_mat').val();
				quantity				= $('.quantity').val();
				unit_price				= $('.unit_price').val();
				total_art				= $('.total_art').val();
				quantity_not_damaged	= $('.quantity_not_damaged').val();
				damaged					= $('.damaged').val();
				date_entry				= $('.date_entry').val();
				commentaries			= $('[name="commentaries"]').val();

				sum_art = Number(quantity_not_damaged)+Number(damaged);

				if (description == "" || measure == "" || code_mat == "" || quantity == "" || unit_price == "" || total_art == "" || quantity_not_damaged == "" || damaged == "" || date_entry == "")
				{
					swal('','Debe ingresar los campos obligatorios','error');
				}
				else if(Number(sum_art)>Number(quantity) || Number(sum_art)<Number(quantity))
				{
					swal('','La suma de los artículos dañados y los artículos sin dañar debe ser igual a la catnidad de artículos recibidos','error');
				}
				else
				{
					tr = $('<tr></tr>')
						.append($('<td></td>')
							.append($('<input type="hidden" name="code_mat[]" value="'+code_mat+'">'))
							.append($('<input type="hidden" name="id_detail[]" value="'+id_detail+'">'))
							.append(code_mat))
						.append($('<td></td>')
							.append($('<input type="hidden" name="measure[]" value="'+measure+'">'))
							.append(measure))
						.append($('<td></td>')
							.append($('<input type="hidden" name="description[]" value="'+description+'">'))
							.append(description))
						.append($('<td></td>')
							.append($('<input type="hidden" name="quantity_not_damaged[]" value="'+quantity_not_damaged+'">'))
							.append(quantity_not_damaged))
						.append($('<td></td>')
							.append($('<input type="hidden" name="damaged[]" value="'+damaged+'">'))
							.append(damaged))
						.append($('<td></td>')
							.append($('<input type="hidden" name="quantity[]" value="'+quantity+'">'))
							.append(quantity))
						.append($('<td></td>')
							.append($('<input type="hidden" name="unit_price[]" value="'+unit_price+'">'))
							.append(unit_price))
						.append($('<td></td>')
							.append($('<input type="hidden" name="total_art[]" value="'+total_art+'">'))
							.append(total_art))
						.append($('<td></td>')
							.append($('<input type="hidden" name="date_entry[]" value="'+date_entry+'">'))
							.append(date_entry))
						.append($('<td></td>')
							.append($('<input type="hidden" name="commentaries[]" value="'+commentaries+'">'))
							.append(commentaries));

					$('#body_art').append(tr);
					$('.form-warehouse').fadeOut(300);

					$('.description').val('');
					$('.id_detail').val('');
					$('.measure').val('');
					$('.code_mat').val('');
					$('.quantity').val('');
					$('.unit_price').val('');
					$('.total_art').val('');
					$('.quantity_not_damaged').val('');
					$('.damaged').val('');
					$('.date_entry').val('');
					$('.commentaries').val('');
					swal('','Artículo agregado a la lista','success');
				}
			})
			.on('click','[name="send"]',function(e)
			{
				pending_items = $('[name="pending_items[]"]').length;
				added_items = $('#body_art tr').length;
				e.preventDefault();
				form = $(this).parents('form');
				if (added_items>0) 
				{
					if (pending_items>0)
					{
						swal({
							title: "Hay artículos sin agregar, ¿Desea continuar?",
							text: "Podrá agregarlos después",
							icon: "warning",
							buttons: ["Cancelar","OK"],
						})
						.then((isConfirm) =>
						{
							if(isConfirm)
							{
								form.submit();
							}
						});
					}
					else
					{
						form.submit();
					}
				}
				else
				{
					swal('','No ha agregado ningún artículo','error');
				}
			})
		});
	</script>
@endsection