<div class="modal-content">
	<div class="modal-header" style="border:none;display:block">
		<span class="close exit">&times;</span>
	</div>
	<div class="modal-body">
		<table class="employee-details">
				<tbody>
					<tr>
						<td><b>Nombre:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->name.' '.$nominaemployee->employee->first()->last_name.' '.$nominaemployee->employee->first()->scnd_last_name }}</label></td>
					</tr>
					<tr>
						<td><b>CURP:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->curp }}</label></td>
					</tr>
					<tr>
						<td><b>RFC:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->rfc }}</label></td>
					</tr>
					<tr>
						<td><b>#IMSS:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->imss }}</label></td>
					</tr>
					<tr>
						<td><b>Calle:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->street }}</label></td>
					</tr>
					<tr>
						<td><b>Número:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->number }}</label></td>
					</tr>
					<tr>
						<td><b>Colonia:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->colony }}</label></td>
					</tr>
					<tr>
						<td><b>CP:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->cp }}</label></td>
					</tr>
					<tr>
						<td><b>Ciudad:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->city }}</label></td>
					</tr>
					<tr>
						<td><b>Estado:</b></td>
						<td><label>{{ $nominaemployee->employee->first()->states->description }}</label></td>
					</tr>
				</tbody>
			</table><br><br>
		</div>
		@component('components.labels.title-divisor')    DATOS LABORALES @endcomponent
		<div>
			<table class="employee-details">
				<tbody>
					<tr>
						<td><b>Estado:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->states->description }}</label></td>
					</tr>
					<tr>
						<td><b>Proyecto:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->projects->proyectName }}</label></td>
					</tr>
					@if($nominaemployee->workerData->first()->wbs()->exists())
						<tr>
							<td><b>WBS:</b></td>
							<td><label>{{ $nominaemployee->workerData->first()->wbs->code_wbs }}</label></td>
						</tr>
					@endif
					<tr>
						<td><b>Empresa:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->enterprises->name }}</label></td>
					</tr>
					<tr>
						<td><b>Clasificación de gasto:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->accounts->account.' '.$nominaemployee->workerData->first()->accounts->description }}</label></td>
					</tr>
					<tr>
						<td><b>Lugar de Trabajo:</b></td>
						<td>
							@foreach ($nominaemployee->workerData->first()->places as $p) 
								<label>{{ $p->place }}</label>, 
							@endforeach
			 			</td>
					</tr>
					<tr>
						<td><b>Dirección:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->directions->name }}</label></td>
					</tr>
					<tr>
						<td><b>Departamento:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->departments->name }}</label></td>
					</tr>
					<tr>
						<td><b>Registro patronal:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->employer_register }}</label></td>
					</tr>
					<tr>
						<td><b>Puesto:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->position }}</label></td>
					</tr>
					<tr>
						<td><b>Jefe inmediato:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->immediate_boss }}</label></td>
					</tr>
					<tr>
						<td><b>Fecha de ingreso:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->admissionDate }}</label></td>
					</tr>
					<tr>
						<td><b>Fecha de alta IMSS:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->imssDate }}</label></td>
					</tr>
					<tr>
						<td><b>Fecha de baja:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->downDate }}</label></td>
					</tr>
					<tr>
						<td><b>Fecha de término de relación laboral:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->endingDate }}</label></td>
					</tr>
					<tr>
						<td><b>Reingreso:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->reentryDate }}</label></td>
					</tr>
					<tr>
						<td><b>Tipo de trabajador:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->worker()->exists() ?  $nominaemployee->workerData->first()->worker->description : 'no hay' }} </label></td>
					</tr>
					<tr>
						<td><b>Estado:</b></td>
						<td><label>
						@if ($nominaemployee->workerData->first()->workerStatus == 1) 
							 Activo
						@endif
						@if ($nominaemployee->workerData->first()->workerStatus == 2) 
							 Baja pacial
						@endif
						@if ($nominaemployee->workerData->first()->workerStatus == 3) 
							 Baja definitiva
						@endif
						@if ($nominaemployee->workerData->first()->workerStatus == 4) 
							 Suspensión
						@endif
						@if ($nominaemployee->workerData->first()->workerStatus == 5) 
							 Boletinado
						@endif
			 </label></td>
					</tr>
					<tr>
						<td><b>SDI:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->sdi }}</label></td>
					</tr>
					<tr>
						<td><b>Periodicidad:</b></td>
						<td><label>{{ App\CatPeriodicity::where('c_periodicity',$nominaemployee->workerData->first()->periodicity)->first()->description }}</label></td>
					</tr>
					<tr>
						<td><b>Sueldo neto:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->netIncome }}</label></td>
					</tr>
					<tr>
						<td><b>Complemento:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->complement }}</label></td>
					</tr>
					<tr>
						<td><b>Monto Fonacot:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->fonacot }}</label></td>
					</tr>
					<tr>
						<td><b>Número de crédito:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->infonavitCredit }}</label></td>
					</tr>
					<tr>
						<td><b>Descuento:</b></td>
						<td><label>{{ $nominaemployee->workerData->first()->infonavitDiscount }}</label></td>
					</tr>
					<tr>
						<td><b>Tipo de descuento:</b></td>
						<td><label>
						@if ($nominaemployee->workerData->first()->infonavitDiscountType == 1) 
							 VSM (Veces Salario Mínimo)
						@endif
						@if ($nominaemployee->workerData->first()->infonavitDiscountType == 2) 
							 Cuota fija
						@endif
						@if ($nominaemployee->workerData->first()->infonavitDiscountType == 3) 
							 Porcentaje
						@endif
			 </label></td>
					</tr>
				</tbody>
			</table>
			<p><br></p>
			<div class="table-responsive">
				<table class="table" style="min-width: 100%;">
					<thead class="thead-dark">
						<tr>
							<th colspan="2">Esquema de pagos</th>
						</tr>
						<tr>
							<th>Porcentaje de nómina</th>
							<th>Porcentaje de bonos</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<center>
									<b>{{ $nominaemployee->workerData->first()->nomina }}</b>
								</center>
							</td>
							<td>
								<center>
									<b>{{ $nominaemployee->workerData->first()->bono }}</b>
								</center>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		@component('components.labels.title-divisor')    DATOS DEL PAGO DEL EMPLEADO @endcomponent<br><br>
		<div class="table-responsive">
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="2">
							FORMA DE PAGO
						</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<center>
			<div class="div-form-group" style="display: flex; overflow-x: auto;">
				<input type="radio" disabled="disabled" name="method" id="accountBank" value="1" @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 1) checked="checked" @endif>
				<label for="accountBank">Cuenta Bancaria</label> 
				<input type="radio" disabled="disabled" name="method" id="cash" value="2" @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 2) checked="checked" @endif>
				<label for="cash">Efectivo</label>
				<input type="radio" disabled="disabled" name="method" id="checks" value="3" @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 3) checked="checked" @endif>
				<label for="checks">Cheque</label>
				<br>
			</div>
		</center>

		<div class="resultbank table-responsive" @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 1) style="display: block;" @else style="display: none;" @endif>
			<br><br>
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="7">
							SELECCIONE UNA CUENTA
						</th>
					</tr>
					<tr>
						<th>Alias</th>
						<th>Banco</th>
						<th>CLABE</th>
						<th>Cuenta</th>
						<th>Tarjeta</th>
						<th>Sucursal</th>
						<th></th>
					</tr>
				</thead>
				<tbody class='request-validate'>
					@foreach ($employee->bankData->where('visible',1) as $b) 
						<tr @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idemployeeAccounts == $b->id) class="marktr" @endif>
							<td>
								{{ $b->alias }}
							</td>
							<td>
								{{ $b->bank->description }}
							</td>
							<td>
								{{ $b->clabe != '' ? $b->clabe : '---' }}
							</td>
							<td>
								{{ $b->account != '' ? $b->account : '---' }}
							</td>
							<td>
								{{ $b->cardNumber != '' ? $b->cardNumber : '---' }}
							</td>
							<td>
								{{ $b->branch != '' ? $b->branch : '---' }}
							</td>
							<td>
								<input id='idEmp{{ $b->id}}' type='radio' name='idEmployeeAccounts' class='checkbox' value='{{ $b->id }}' class='btn btn-green'  @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idemployeeAccounts == $b->id) checked="checked" @endif disabled="disabled">
								<label class='check-small request-validate' for='idEmp{{ $b->id}}'><span class='icon-checkmark'></span></label>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="2">
							DATOS
						</th>
					</tr>
				</thead>
				<tbody>
		 			<tr>
						<td>Referencia</td>
						<td>
							<p>
								{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->reference : null }}
							</p>
						</td>
					</tr>
					<tr>
						<td>Descuento</td>
						<td>
							<p>
								{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->discount : null }}
							</p>
						</td>
					</tr>
					<tr>
						<td>Motivo del descuento</td>
						<td>
							<p>
								{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->reasonDiscount : null }}
							</p>
						</td>
					</tr>
					<tr>
						<td>Importe</td>
						<td>
							<p>
								{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->amount : $nominaemployee->workerData->first()->complement }}
							</p>
						</td>
					</tr>
					<tr>
						<td>Razón de pago</td>
						<td>
							<p>
								{{ $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->reasonAmount : null }}
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<center>
			<button type="button" class="btn btn-red exit" title="Cerrar">
				<span class="icon-x"></span> Cerrar
			</button>
		</center><br>
	</div>
</div>