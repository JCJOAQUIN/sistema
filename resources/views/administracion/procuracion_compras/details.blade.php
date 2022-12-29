<div class='modal-content'>
	<div class='modal-header'>
		<span class='close exit'>&times;</span>
	</div>
	<div class='modal-body'>
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
		<center>
			<b>DESCARGAR ORDEN</b> <br>
			<a href="{{ route('procurement-purchases.purchase-download',$request->id) }}" class="btn btn-red" style="font-size: 50px;"><span class="icon-pdf"></span></a>
		</center>
	</div>
	<center>
		<button class="btn btn-red exit" type="button">Cerrar</button>
	</center>
</div>