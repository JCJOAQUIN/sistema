{!! Form::open(['route' => ['nomina.nomina-create.updatedataf',$nominaemployee->settlement->first()->idSettlement], 'method' => 'PUT', 'id' => 'container-alta']) !!}
<div class="modal-content">
	<div class="modal-header" style="border:none;display:block">
		<span class="close exit">&times;</span>
	</div>
	<div class="modal-body">
		@component('components.labels.title-divisor')    DATOS @endcomponent<br><br>
		<div class="table-responsive">
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="2">
							SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO
						</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<center>
			<div class="div-form-group" style="display: flex; overflow-x: auto; max-width: 500px;">
				<input type="radio" name="settlement_idpaymentMethod" id="accountBank" value="1" @if($nominaemployee->settlement()->exists() && $nominaemployee->settlement->first()->idpaymentMethod == 1) checked="checked" @endif>
				<label for="accountBank">Cuenta Bancaria</label> 
				<input type="radio" name="settlement_idpaymentMethod" id="cash" value="2" @if($nominaemployee->settlement()->exists() && $nominaemployee->settlement->first()->idpaymentMethod == 2) checked="checked" @endif>
				<label for="cash">Efectivo</label>
				<input type="radio" name="settlement_idpaymentMethod" id="checks" value="3" @if($nominaemployee->settlement()->exists() && $nominaemployee->settlement->first()->idpaymentMethod == 3) checked="checked" @endif>
				<label for="checks">Cheque</label>
				<input type="radio" name="settlement_idpaymentMethod" id="checks_refund" value="4" @if($nominaemployee->settlement()->exists() && $nominaemployee->settlement->first()->idpaymentMethod == 4) checked="checked" @endif>
				<label for="checks_refund">Cheque para Reintegro</label>
				<br>
			</div>
		</center>
		<div class="resultbank table-responsive" @if($nominaemployee->settlement()->exists() && $nominaemployee->settlement->first()->idpaymentMethod == 1) style="display: block;" @else style="display: none;" @endif>
			<br><br>
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="7">
							SELECCIONE UNA CUENTA DEL EMPLEADO
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
					@foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
						<tr>
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
								<label class="container">
									@if($nominaemployee->settlement->first()->nominaEmployeeAccounts()->exists())
										<input type="checkbox" @if(in_array($b->id, $nominaemployee->settlement->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())) checked="checked" @endif name="settlement_idemployeeAccounts[]" multiple="multiple" value="{{ $b->id }}">
									@else
										<input type="checkbox" @if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->last()->id) checked="checked" @endif name="settlement_idemployeeAccounts[]" multiple="multiple" value="{{ $b->id }}">
									@endif
									<span class="checkmark"></span>
								</label>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<p><br></p>
		<div class="table-responsive" @if($nominaemployee->settlement->first()->alimony > 0)  style="display: block;" @else style="display: none;" @endif>
			<br><br>
			<table class="table" style="min-width: 100%;">
				<thead class="thead-dark">
					<tr>
						<th colspan="8">
							SELECCIONE LA CUENTA DEL BENEFICIARIO DE PENSIÓN ALIMENTICIA
						</th>
					</tr>
					<tr>
						<th>Beneficiario</th>
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
					@foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
						<tr>
							<td>
								{{ $b->beneficiary }}
							</td>
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
								@if($nominaemployee->settlement->first()->idAccountBeneficiary != '')
									<input id='idEmp{{ $b->id}}' type='radio' name='settlement_idAccountBeneficiary' class='checkbox' value='{{ $b->id }}' class='btn btn-green' @if($b->id == $nominaemployee->settlement->first()->idAccountBeneficiary) checked="checked" @endif>
									<label class='check-small request-validate' for='idEmp{{ $b->id}}'><span class='icon-checkmark'></span></label>
								@else
									<input id='idEmp{{ $b->id}}' type='radio' name='settlement_idAccountBeneficiary' class='checkbox' value='{{ $b->id }}' class='btn btn-green' @if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id) checked="checked" @endif>
									<label class='check-small request-validate' for='idEmp{{ $b->id}}'><span class='icon-checkmark'></span></label>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<tr>
						<th colspan="4">
							INFORMACIÓN
						</th>
					</tr>
				</thead>
				<tbody>
		 			<tr>
		 				<td>S.D.</td>
		 				<td>
		 					<p>
		 						<input type="text" name="settlement_sd" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->sd : null }}">
		 					</p>
		 				</td>
						<td>S.D.I.</td>
						<td>
							<p>
								<input type="text" name="settlement_sdi" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->sdi : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Fecha de ingreso</td>
						<td>
							<p>
								<input type="text" name="settlement_admissionDate" class="datepicker new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->admissionDate : $nominaemployee->workerData->first()->admissionDate }}">
							</p>
						</td>
						<td>Fecha de baja</td>
						<td>
							<p>
								<input type="text" name="settlement_downDate" class="datepicker new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->downDate : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Años completos</td>
						<td>
							<p>
								<input type="text" name="settlement_fullYears" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->fullYears : null }}">
							</p>
						</td>
						<td>Días trabajados</td>
						<td>
							<p>
								<input type="text" name="settlement_workedDays" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->workedDays : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Días para vacaciones</td>
						<td>
							<p>
								<input type="text" name="settlement_holidayDays" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->holidayDays : null }}">
							</p>
						</td>
						<td>Días de aguinaldo</td>
						<td>
							<p>
								<input type="text" name="settlement_bonusDays" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->bonusDays : null }}">
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<tr>
						<th colspan="4">
							PERCEPCIONES
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Prima de antigüedad</td>
						<td>
							<p>
								<input type="text" name="settlement_seniorityPremium" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->seniorityPremium : null }}">
							</p>
						</td>
						<td>Vacaciones</td>
						<td>
							<p>
								<input type="text" name="settlement_holidays" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->holidays : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Indemnización exenta</td>
						<td>
							<p>
								<input type="text" name="settlement_exemptCompensation" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->exemptCompensation : null }}">
							</p>
						</td>
						<td>Indemnización gravada</td>
						<td>
							<p>
								<input type="text" name="settlement_taxedCompensation" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->taxedCompensation : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Aguinaldo exento</td>
						<td>
							<p>
								<input type="text" name="settlement_exemptBonus" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->exemptBonus : null }}">
							</p>
						</td>
						<td>Aguinaldo gravable</td>
						<td>
							<p>
								<input type="text" name="settlement_taxableBonus" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->taxableBonus : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Prima vacacional exenta</td>
						<td>
							<p>
								<input type="text" name="settlement_holidayPremiumExempt" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->holidayPremiumExempt : null }}">
							</p>
						</td>
						<td>Prima vacacional gravada</td>
						<td>
							<p>
								<input type="text" name="settlement_holidayPremiumTaxed" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->holidayPremiumTaxed : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Otras percepciones</td>
						<td>
							<p>
								<input type="text" name="settlement_otherPerception" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->otherPerception : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td><b>Total</b></td>
						<td>
							<p>
								<input type="text" name="settlement_totalPerceptions" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->totalPerceptions : null }}">
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark">
					<tr>
						<th colspan="4">
							RETENCIONES
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>ISR</td>
						<td>
							<p>
								<input type="text" name="settlement_isr" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->isr : null }}">
							</p>
						</td>
						<td>Pensión Alimenticia</td>
						<td>
							<p>
								<input type="text" name="settlement_alimony" class="new-input-text" placeholder="Escriba aquí..." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->alimony : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td>Otras retenciones</td>
						<td>
							<p>
								<input type="text" name="settlement_otherRetention" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->other_retention : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td><b>Total</b></td>
						<td>
							<p>
								<input type="text" name="settlement_totalRetentions" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->totalRetentions : null }}">
							</p>
						</td>
					</tr>
					<tr>
						<td><b>Sueldo neto</b></td>
						<td>
							<p>
								<input type="text" name="settlement_netIncome" readonly="readonly" class="new-input-text" placeholder="Escriba aquí.." value="{{ $nominaemployee->settlement()->exists() ? $nominaemployee->settlement->first()->netIncome : null }}">
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="folio" value="{{ $folio }}">
		<input type="hidden" name="idtypepayroll" value="{{ $idtypepayroll }}">
		<center>
			<input type="submit" name="senddata" value="{{ $nominaemployee->settlement()->exists() ? 'Actualizar' : 'Agregar' }}" class="btn btn-green">
				
			<button type="button" class="btn btn-red exit" title="Cerrar">
				<span class="icon-x"></span> Cerrar
			</button>
		</center><br>
	</div>
</div>
{!! Form::close() !!}
