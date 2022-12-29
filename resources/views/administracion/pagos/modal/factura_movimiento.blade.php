@if($type == 1)
	@component('components.labels.title-divisor')    DATOS DE FACTURA @endcomponent<br>
	<div class="table-responsive">
		<table class="table">
			<thead class="thead-dark">
				<tr>
					<th>Emisor</th>
					<th>Receptor</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><b>{{$bill->rfc}}</b> {{$bill->businessName}}</td>
					<td><b>{{$bill->clientRfc}}</b> {{$bill->clientBusinessName}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<p><br></p>
	<div class="table-responsive">
		<table class="table">
			<tbody>
				<tr>
					<th class="table-dark">Folio:</th>
					<td>{{$bill->folio}}</td>
					<th class="table-dark">Serie:</th>
					<td>{{$bill->serie}}</td>
				</tr>
				<tr>
					<th class="table-dark">Uso de CFDI:</th>
					<td>{{$bill->cfdiUse->useVoucher}} {{$bill->cfdiUse->description}}</td>
					<th class="table-dark">Tipo de CFDI:</th>
					<td>{{$bill->cfdiType->typeVoucher}} {{$bill->cfdiType->description}}</td>
				</tr>
				<tr>
					<th class="table-dark">Forma de pago:</th>
					<td>{{$bill->cfdiPaymentWay->paymentWay}} {{$bill->cfdiPaymentWay->description}}</td>
					<th class="table-dark">Método de pago:</th>
					<td>{{$bill->cfdiPaymentMethod->paymentMethod}} {{$bill->cfdiPaymentMethod->description}}</td>
				</tr>
				<tr>
					<th class="table-dark" colspan="2">CP:</th>
					<td colspan="2">{{$bill->postalCode}}</td>
				</tr>
				<tr>
					<th class="table-dark" colspan="2">Condiciones de pago:</th>
					<td colspan="2">{{$bill->conditions}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<p><br></p>
	<div class="table-responsive">
		@foreach($bill->billDetail as $d)
			<table class="table table-borderless">
				<thead class="thead-dark">
					<tr>
						<th><strong>Clave de producto o servicio</strong></th>
						<th><strong>Clave de unidad</strong></th>
						<th><strong>Cantidad</strong></th>
						<th><strong>Valor unitario</strong></th>
						<th><strong>Importe</strong></th>
						<th><strong>Descuento</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{{$d->keyProdServ}}</td>
						<td>{{$d->keyUnit}}</td>
						<td>{{$d->quantity}}</td>
						<td>{{$d->value}}</td>
						<td>{{$d->amount}}</td>
						<td>{{$d->discount}}</td>
					</tr>
					<tr>
						<td class="align-middle">{{$d->description}}</td>
						<td colspan="5">
							@if($d->taxesTras->count()>0)
								<table class="table">
									<thead class="thead-dark">
										<th colspan="4">Traslados</th>
									</thead>
									<thead class="thead-white">
										<th>Impuesto</th>
										<th>¿Tasa o cuota?</th>
										<th>Valor de la tasa o cuota</th>
										<th>Importe</th>
									</thead>
									<tbody>
										@foreach($d->taxesTras as $t)
											<tr>
												<td>{{$t->cfdiTax->tax}} {{$t->cfdiTax->description}}</td>
												<td>{{$t->quota}}</td>
												<td>{{$t->quotaValue}}</td>
												<td>{{$t->amount}}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							@endif
							@if($d->taxesRet->count()>0)
								<table class="table">
									<thead class="thead-dark">
										<th colspan="4">Retenciones</th>
									</thead>
									<thead class="thead-white">
										<th>Impuesto</th>
										<th>¿Tasa o cuota?</th>
										<th>Valor de la tasa o cuota</th>
										<th>Importe</th>
									</thead>
									<tbody>
										@foreach($d->taxesRet as $t)
											<tr>
												<td>{{$t->cfdiTax->tax}} {{$t->cfdiTax->description}}</td>
												<td>{{$t->quota}}</td>
												<td>{{$t->quotaValue}}</td>
												<td>{{$t->amount}}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							@endif
						</td>
					</tr>
				</tbody>
			</table>
		@endforeach
	</div>
	<p><br></p>
	<div class="table-responsive">
		<table class="table table-borderless">
			<tbody>
				<tr>
					<th class="text-right">Subtotal:</th>
					<td>${{number_format($bill->subtotal,2)}}</td>
				</tr>
				<tr>
					<th class="text-right">Descuento:</th>
					<td>${{number_format($bill->discount,2)}}</td>
				</tr>
				<tr>
					<th class="text-right">Total de impuestos trasladados:</th>
					<td>${{number_format($bill->tras,2)}}</td>
				</tr>
				<tr>
					<th class="text-right">Total de impuestos retenidos:</th>
					<td>${{number_format($bill->ret,2)}}</td>
				</tr>
				<tr>
					<th class="text-right">Total:</th>
					<td>${{number_format($bill->total,2)}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<p><br></p>
	@component('components.labels.title-divisor')    DATOS DE MOVIMIENTO @endcomponent<br>
	<div class="table-responsive">
		<table class="table">
			<tbody>
				<tr>
					<td width="25%" class="table-dark">Empresa:</td>
					<td width="25%">{{$movement->enterprise->name}}</td>
					<td width="25%" class="table-dark">Clasificación de Gasto:</td>
					<td width="25%">{{$movement->accounts->account}} - {{$movement->accounts->description}}</td>
				</tr>
				<tr>
					<td class="table-dark">Tipo de Movimiento:</td>
					<td>{{$movement->movementType}}</td>
					<td class="table-dark">Descripción:</td>
					<td>{{$movement->description}}</td>
				</tr>
				<tr>
					<td colspan="2" class="table-dark">Total:</td>
					<td colspan="2">${{ number_format($movement->amount,2) }}</td>
				</tr>
			</tbody>
		</table>
	</div>
@else
	@component('components.labels.title-divisor')    DATOS DEL INGRESO NO FISCAL @endcomponent<br>
	<div class="table-responsive">
		<table class="table">
			<thead class="thead-dark">
				<tr>
					<th>Empresa</th>
					<th>Cliente</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><b>{{$bill->rfc}}</b> {{$bill->businessName}}</td>
					<td><b>{{$bill->clientRfc}}</b> {{$bill->clientBusinessName}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<p><br></p>
	<div class="table-responsive">
		<table class="table">
			<tbody>
				<tr>
					<th class="table-dark">Forma de pago:</th>
					<td>{{$bill->cfdiPaymentWay->description}}</td>
					<th class="table-dark">Folio:</th>
					<td>{{$bill->folio}}</td>
				</tr>
				<tr>
					<th class="table-dark">Método de pago:</th>
					<td>{{$bill->cfdiPaymentMethod->description}}</td>
					<th class="table-dark">Condiciones de pago:</th>
					<td>{{$bill->conditions}}</td>
				</tr>
			</tbody>
		</table>
	</div>
	<p><br></p>
	<div class="table-responsive">
		@foreach($bill->billDetail as $d)
			<table class="table table-borderless">
				<thead class="thead-dark">
					<tr>
						<th><strong>Cantidad</strong></th>
						<th><strong>Valor unitario</strong></th>
						<th><strong>Importe</strong></th>
						<th><strong>Descuento</strong></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{{$d->quantity}}</td>
						<td>{{$d->value}}</td>
						<td>{{$d->amount}}</td>
						<td>{{$d->discount}}</td>
					</tr>
				</tbody>
			</table>
		@endforeach
	</div>
	<p><br></p>
	<div class="table-responsive">
		<table class="table table-borderless">
			<tbody>
				<tr>
					<th class="text-right">Subtotal:</th>
					<td>{{$bill->subtotal}}</td>
				</tr>
				<tr>
					<th class="text-right">Descuento:</th>
					<td>{{$bill->discount}}</td>
				</tr>
				<tr>
					<th class="text-right">Total:</th>
					<td>{{$bill->total}}</td>
				</tr>
			</tbody>
		</table>
	</div>

	<p><br></p>
	@component('components.labels.title-divisor')    DATOS DE MOVIMIENTO @endcomponent<br>
	<div class="table-responsive">
		<table class="table">
			<tbody>
				<tr>
					<td width="25%" class="table-dark">Empresa:</td>
					<td width="25%">{{$movement->enterprise->name}}</td>
					<td width="25%" class="table-dark">Clasificación de Gasto:</td>
					<td width="25%">{{$movement->accounts->account}} - {{$movement->accounts->description}}</td>
				</tr>
				<tr>
					<td class="table-dark">Tipo de Movimiento:</td>
					<td>{{$movement->movementType}}</td>
					<td class="table-dark">Descripción:</td>
					<td>{{$movement->description}}</td>
				</tr>
				<tr>
					<td colspan="2" class="table-dark">Total:</td>
					<td colspan="2">${{ number_format($movement->amount,2) }}</td>
				</tr>
			</tbody>
		</table>
	</div>
@endif