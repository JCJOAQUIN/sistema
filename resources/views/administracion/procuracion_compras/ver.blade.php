@extends('layouts.child_module')
@section('css')
	<style type="text/css">
		#container-data
		{
			display		: block;
			margin		: auto;
			max-width	: 600px;
			padding		: 1em 5%;
		}
		.box
		{
			background-color	: #fff;
			padding				: 2rem 1rem;
		}
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
			max-width			: 80%;
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

		.table .thead-dark th 
		{
			width: 50em;
		}
		.select_father
		{
			display: none;
		}
		.card-header-dark
		{
			background	: #343a40 !important;
			color		: white !important;
			font-weight	: bold !important;
			padding		: 0.3rem;
			text-align	: center;
		}
		.group
		{
			padding: 0px !important;
		}
	</style>
@endsection
@section('data')
	<div class="table-responsive">
		<table class="table">
			<thead class="thead-dark" style="min-width: 100%;">
				<th colspan="4">ORDEN DE COMPRA</th>
			</thead>
		</table>
	</div>
	<div class="form-row px-3">
		<div class="form-group col-md-6 mb-4">
			<label><b>Cuenta:</b></label>
			<label>{{ $request->account != "" ? $request->accountData->name : 'Sin Cuenta' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Proyecto:</b></label>
			<label>{{ $request->project()->exists() ? $request->project->proyectName : 'Sin Proyecto' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4 select_father" @if(isset($request)) @if($request->idProject == 75) style="display: table-row;" @endif @else style="display: table-row;" @endif>
			<label><b>Código WBS:</b></label>
			<label>{{ $request->code_wbs!="" ? $request->wbs->code_wbs : 'Sin WBS' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Moneda:</b></label>
			<label>{{ $request->type_currency != "" ? $request->type_currency : 'Sin Moneda' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Estatus:</b></label>
			<label>{{ $request->statusRequest->description }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Número OC:</b></label>
			<label>{{ $request->numberOrder != "" ? $request->numberOrder : 'Sin Número de Orden' }}</label>
		</div>
		
		<div class="form-group col-md-6 mb-4">
			<label><b>CO#:</b></label>
			<label>{{ $request->numberCO != "" ? $request->numberCO : 'Sin CO#' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Contrato:</b></label>
			<label>{{ $request->contract != "" ? $request->contract : 'Sin Contrato' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Descripción:</b></label>
			<label>{{ $request->descriptionShort != "" ? $request->descriptionShort : 'Sin Descripción' }}</label>
		</div>
		
		<div class="form-group col-md-6 mb-4">
			<label><b>Fecha req. en sitio:</b></label>
			<label>{{ $request->date_obra != "" ? $request->date_obra->format('Y-m-d') : 'Sin Fecha' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Fecha promesa entrega:</b></label>
			<label>{{ $request->date_promise != "" ? $request->date_promise->format('Y-m-d') : 'Sin Fecha' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Fecha Elaboración:</b></label>
			<label>{{ $request->date_request != "" ? $request->date_request->format('Y-m-d') : 'Sin Fecha' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Fecha Cierre:</b></label>
			<label>{{ $request->date_close != "" ? $request->date_close->format('Y-m-d') : 'Sin Fecha' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Destino:</b></label>
			<label>{{ $request->destination != "" ? $request->destination : 'Sin Destino' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Sitio:</b></label>
			<label>{{ $request->site != "" ? $request->site : 'Sin Sitio' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Ingeniero:</b></label>
			<label>{{ $request->engineer != "" ? $request->engineer : 'Sin Sitio' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Comprador:</b></label>
			<label>{{ $request->buyer != "" ? $request->buyer : 'Sin Sitio' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Expedidor:</b></label>
			<label>{{ $request->expeditor != "" ? $request->expeditor : 'Sin Sitio' }}</label>
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
			<label>{{ $request->descriptionLong!="" ? $request->descriptionLong : 'Sin Descripción' }}</label>
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
			<label>{{ $request->provider != "" ? $request->provider : 'Sin Datos' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Ubicación:</b></label>
			<label>{{ $request->ubicationProvider != "" ? $request->ubicationProvider : 'Sin Datos' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Contacto:</b></label>
			<label>{{ $request->contactProvider != "" ? $request->contactProvider : 'Sin Datos' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Teléfono:</b></label>
			<label>{{ $request->phoneProvider != "" ? $request->phoneProvider : 'Sin Datos' }}</label>
		</div>
		<div class="form-group col-md-6 mb-4">
			<label><b>Email:</b></label>
			<label>{{ $request->emailProvider != "" ? $request->emailProvider : 'Sin Datos' }}</label>
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
					<th colspan="12">CONCEPTOS</th>
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
				</tr>
			</thead>
			<tbody id="body_art" class="request-validate">
					@foreach($request->details as $detail)
						<tr>
							<td>
								{{ $detail->part }} 
								<input type="hidden" class="idDetail" value="{{ $detail->id }}">
								<input type="hidden" name="part[]" class="input-text t_part" placeholder="0">
							</td>
							<td>{{ $detail->code }}</td>
							<td>{{ $detail->unit }}</td>
							<td>{{ $detail->description }}</td>
							<td>{{ $detail->quantity }}</td>
							<td>{{ $detail->price }}</td>
							<td>{{ $detail->total_concept }} <input type="hidden" class="t_total_concept" value="{{ $detail->total_concept }}"></td>
							<td>{{ $detail->type_currency }}</td>
							<td>{{ $detail->date_one->format('Y-m-d') }}</td>
							<td>{{ $detail->date_two->format('Y-m-d') }}</td>
						</tr>
					@endforeach
			</tbody>
		</table>
	</div>
	<p><br></p>
	<div class="totales2">
		<div class="totales" style="margin-left: 10px;"> 
			<table>
				<tr>
					<td><label class="label-form">TOTAL:</label></td>
					<td><input id="input-extrasmall" placeholder="$0.00" readonly class="input-table" type="text" name="total_request" @isset($request) value="{{ $request->total_request }}" @endisset></td>
				</tr>
			</table>
			
		</div> 
	</div>
	<p><br></p>
	<p><br></p>
	<div class="form-row px-3">
		<table class="table">
			<thead class="thead-dark">
				<tr>
					<th colspan="5">MILESTONES</th>
				</tr>
				<tr>
					<th>Seq Num</th>
					<th>Milestone</th>
					<th>Schedule</th>
					<th>Status</th>
					<th>Complete</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($request->milestones as $milestone)
					<tr>
						<td>
							{{ $milestone->seq_num }}
						</td>
						<td>
							{{ $milestone->milestone }}
						</td>
						<td>
							{{ $milestone->schedule }}
						</td>
						<td>
							{{ $milestone->status }}
						</td>
						<td>
							{{ $milestone->complete_status }}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<p><br></p>
	<p><br></p>
	<div class="table-responsive">
		<table class="table">
			<thead class="thead-dark" style="min-width: 100%;">
				<th colspan="4">OBSERVACIONES</th>
			</thead>
		</table>
	</div>
	<div class="form-row px-3">
		@isset($request)
			@foreach($request->remarks as $remark)
				<div class="form-group col-md-12 mb-4">
					<p>
						<b>Fecha: </b> {{ $remark->date }}
					</p>
					<p>
						<b>Observación: </b> {{ $remark->remark }}
					</p>
					<div style="width:100%; height: 1px; background: gray;"></div>
				</div>
			@endforeach
		@endisset
	</div>
	{!! Form::open(['route' => ['procurement-purchases.purchase-save-remarks',$request->id], 'method' => 'PUT', 'id' => 'container-alta','files' => true]) !!}
		<div class="form-row px-3 remarks">
			<div class="form-group col-md-12 mb-4">
				<label><b>Fecha:</b></label>
				<input type="text" name="date_remark[]" class="input-text date_remark" placeholder="Seleccione una fecha" readonly="readonly"><br>
				<label><b>Descripción:</b></label>
				<textarea type="text" name="remark[]" class="new-input-text removeselect" placeholder="Escriba aquí..."  rows="10"></textarea>
			</div>
		</div>
		<button class="btn btn-green add-remark" type="button"><span class="icon-plus"></span><span>Agregar Otra Observación</span></button><br>
		<button class="btn btn-red" name="save" type="submit"><i class="fas fa-save"></i> GUARDAR OBSERVACIONES</button>
		<p><br></p>
		<span id="delete"></span>
	{!! Form::close() !!}

	@if(isset($request) && $request->history()->exists())
		<p><br></p>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark" style="min-width: 100%">
					<tr>
						<th colspan="3">HISTORIAL/CAMBIOS DE ORDEN</th>
					</tr>
					<tr>
						<th>Número de Orden</th>
						<th>Fecha de Elaboración</th>
						<th>Acción</th>
					</tr>
				</thead>
				<tbody>
					@foreach(App\ProcurementHistory::where('folio_original',$request->history->first()->folio_original)->orderBy('created_at','DESC')->get() as $history)
						@if($history->folio != $request->id)
							<tr>
								<td>
									{{ $history->procurementPurchase->numberOrder != "" ? $history->procurementPurchase->numberOrder : 'Sin Número de Orden' }}
								</td>
								<td>
									{{ $history->procurementPurchase->date_request != "" ? $history->procurementPurchase->date_request : 'Sin Fecha' }}
								</td>
								<td>
									<button type="button" class="btn btn-green view-detail" data-toggle="modal" data-folio="{{ $history->folio }}"><span class="icon-search"></span></button>
								</td>
							</tr>
						@endif
					@endforeach
				</tbody>
			</table>
		</div>
	@endif
	<center>
		<b>DESCARGAR ORDEN</b> <br>
		<a href="{{ route('procurement-purchases.purchase-download',$request->id) }}" class="btn btn-red" style="font-size: 50px;"><span class="icon-pdf"></span></a>
	</center>

@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('[name="date_remark[]"]').datepicker({  dateFormat: "yy-mm-dd" });
			$(document).on('click','.add-remark',function()
			{
				remark = $('<div class="form-group col-md-12 mb-4 remark"></div')
						.append($('<label><b>Fecha:</b></label>'))
						.append($('<input type="text" name="date_remark[]" class="input-text date_remark" placeholder="Seleccione una fecha" readonly="readonly"><br>'))
						.append($('<label><b>Descripción:</b></label>'))
						.append($('<textarea type="text" name="remark[]" class="new-input-text removeselect" placeholder="Escriba aquí..."  rows="10"></textarea>'))
						.append($('<button class="btn btn-red delete-remark" type="button"><span class="icon-x"></span> Eliminar</button>'));

				$('.remarks').append(remark);
				$('[name="date_remark[]"]').datepicker({  dateFormat: "yy-mm-dd" });
			})
			.on('click','.delete-remark',function()
			{
				$(this).parent('div').remove();
				swal('','Observación eliminada','success');
			})
			.on('click','[data-toggle="modal"]',function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('procurement-purchases.view-detail') }}',
					data	: {'folio':folio},
					success :  function(data)
					{
						$('#myModal').show().html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').hide();
					}
				})
			})
			.on('click','.exit',function()
			{
				$('#myModal').hide();
			});
		})
	</script>
@endsection