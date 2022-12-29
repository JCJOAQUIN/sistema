{!! Form::open(['route' => 'nomina.nomina-create.changetypeupdate', 'method' => 'POST', 'id' => 'container-alta']) !!}
<div class="modal-content">
	<div class="modal-header" style="border:none;display:block">
		<span class="close exit">&times;</span>
	</div>
	<div class="modal-body">
		@component('components.labels.title-divisor')    DATOS DEL PAGO DEL EMPLEADO @endcomponent<br><br>
		<div class="table-responsive">
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="2">
							DATOS ACTUALES
						</th>
					</tr>
				</thead>
				<tbody>
		 			<tr>
						<td>Nombre</td>
						<td>
							<p>
								<input type="text" readonly="readonly" class="input-text" name="employee_reference" value="{{ $nominaemployee->employee->first()->name.' '.$nominaemployee->employee->first()->last_name.' '.$nominaemployee->employee->first()->scnd_last_name }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Tipo</td>
						<td>
							<p>
								<select title="Tipo de nómina" name="type_change" class="custom-select">
									<option value="1" @if($nominaemployee->type == 1) selected="selected" @endif>Obra</option>
									<option value="2" @if($nominaemployee->type == 2) selected="selected" @endif>Administrativa</option>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<td>Fiscal/No fiscal</td>
						<td>
							<p>
								<select title="Fiscal/No Fiscal" name="fiscal_change" class="custom-select">
									<option value="1" @if($nominaemployee->fiscal == 1) selected="selected" @endif>Fiscal</option>
									<option value="2" @if($nominaemployee->fiscal == 2) selected="selected" @endif>No Fiscal</option>
									<option value="3" @if($nominaemployee->fiscal == 3) selected="selected" @endif>Ambas</option>
								</select>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="idnominaEmployee_change" value="{{ $nominaemployee->idnominaEmployee }}">
		<input type="hidden" name="idrealEmployee_change" value="{{ $nominaemployee->idrealEmployee }}">
		<input type="hidden" name="idworkingData_change" value="{{ $nominaemployee->idworkingData }}">
		
		<center>
			<input type="submit" name="senddata" value="Cambiar" class="btn btn-green">
			<button type="button" class="btn btn-red exit" title="Cerrar">
				<span class="icon-x"></span> Cerrar
			</button>
		</center><br>
	</div>
</div>
{!! Form::close() !!}