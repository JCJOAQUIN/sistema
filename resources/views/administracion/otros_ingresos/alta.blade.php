@extends('layouts.child_module')
@section('data')
	@if(isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif
	@php
		$taxesCount = 0;
		$taxes = $retentions = 0;
	@endphp
	@if(isset($request) && !isset($new_request))
		{!! Form::open(['route'=>['other-income.update',$request->folio],'method'=>'put','id'=>'form-income','files'=>true]) !!}
	@else
		{!! Form::open(['route'=>'other-income.store','method'=>'post','id'=>'form-income','files'=>true]) !!}
	@endif
		<center>
			<strong>Nueva solicitud</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div><br>
		<div class="group">
			<p>
				<b>Título:</b><input type="text" name="title" class="new-input-text removeselect" placeholder="Ej. Préstamo de Carlos..." data-validation="required" @if(isset($request)) @if($request->status != 2) disabled="disabled" @endif value="{{ $request->otherIncome->title }}" @endif>
			</p>
			<p>
				<b>Fecha:</b><input type="text" class="new-input-text removeselect datepicker" name="datetitle" @if(isset($request))  @if($request->status != 2) disabled="disabled" @endif value="{{ $request->otherIncome->datetitle }}" @endif data-validation="required" placeholder="Seleccione una fecha" readonly="readonly">
			</p>
			<p>
				<label class="label-form">Fiscal</label><br><br>
				<input type="radio" name="taxPayment" id="nofiscal" value="0" @if(isset($request) && $request->taxPayment==0) checked="checked" @endif @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				<label for="nofiscal">No</label> 
				<input type="radio" name="taxPayment" id="fiscal" value="1" @if(isset($request) && $request->taxPayment==1) checked ="checked" @endif @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
				<label for="fiscal">Sí</label>
				<br><br>
			</p>
			<p>
				<select class="form-control removeselect" name="type_income" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
					<option @if(isset($request) && $request->otherIncome->type_income == 1) selected="selected" @endif value="1">Préstamo de terceros (socios, personales, grupos)</option>
					<option @if(isset($request) && $request->otherIncome->type_income == 2) selected="selected" @endif value="2">Reembolso/reintegro</option>
					<option @if(isset($request) && $request->otherIncome->type_income == 3) selected="selected" @endif value="3">Devoluciones</option>
					<option @if(isset($request) && $request->otherIncome->type_income == 4) selected="selected" @endif value="4">Ganancias por inversión</option>
				</select>
			</p>
			<p>
				<select class="form-control removeselect" name="request_id" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
					@foreach(App\User::whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc')->get() as $user)
						<option value="{{ $user->id }}"@if(isset($request) && $request->idRequest == $user->id)  selected @endif>{{ $user->fullName() }}</option>
					@endforeach
				</select><br>
			</p>
			<p>
				<select class="form-control removeselect" name="enterprise_id" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
					@foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderName()->get() as $enterprise)
						<option value="{{ $enterprise->id }}" @if(isset($request) && $request->idEnterprise == $enterprise->id) selected @endif>{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
					@endforeach
				</select><br>
			</p>
			<p>
				<select class="form-control removeselect" name="project_id" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
					@foreach(App\Project::whereIn('status',[1,2])->orderBy('proyectName','asc')->get() as $project)
						<option value="{{ $project->idproyect }}" @if(isset($request) && $request->idProject == $project->idproyect) selected @endif>{{ $project->proyectName }}</option>
					@endforeach
				</select><br>
			</p>
		</div>

		@if(isset($request))
			<br>
			<div class="resultbank table-responsive">
				<br>
				<center>
					<strong>Seleccione una cuenta</strong>
				</center>
				<div class='divisor'>
					<div class='gray-divisor'></div>
					<div class='orange-divisor'></div>
					<div class='gray-divisor'></div>
				</div><br> 
				<table id='table2' class='table-no-bordered'>
					<thead class='table-no-background'>
						<tr>
							<th>Banco</th>
							<th>Alias</th>
							<th>Cuenta</th>
							<th>Sucursal</th>
							<th>Referencia</th>
							<th>CLABE</th>
							<th>Moneda</th>
							<th>Convenio</th>
							<th></th>
						</tr>
					</thead>
					<tbody class='request-validate' id='banks-body'>
						@foreach(App\BanksAccounts::where('idEnterprise',$request->idEnterprise)->get() as $bank)
							<tr  @if($request->otherIncome->idbanksAccounts == $bank->idbanksAccounts) class="marktr" @endif>
								<td>{{ $bank->bank->description }}</td>
								<td>{{ $bank->alias!=null ? $bank->alias : '-----' }}</td>
								<td>{{ $bank->account!=null ? $bank->account : '-----' }}</td>
								<td>{{ $bank->branch!=null ? $bank->branch : '-----' }}</td>
								<td>{{ $bank->reference!=null ? $bank->reference : '-----' }}</td>
								<td>{{ $bank->clabe!=null ? $bank->clabe : '-----' }}</td>
								<td>{{ $bank->currency!=null ? $bank->currency : '-----' }}</td>
								<td>{{ $bank->agreement!=null ? $bank->agreement : '-----' }}</td>
								<td><input id='idBA{{ $bank->idbanksAccounts }}' @if($request->otherIncome->idbanksAccounts == $bank->idbanksAccounts) checked="checked" @endif type='radio' name='idbanksAccounts' class='checkbox' value="{{ $bank->idbanksAccounts }}" @if($request->status != 2) disabled="disabled" @endif>
								<label class='check-small request-validate' for='idBA{{ $bank->idbanksAccounts }}' @if(isset($globalRequests)) style="pointer-events: none; opacity: .50;" @endif><span class="icon-checkmark"></span></label>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<br>
		@else
			<br><br>
			<div class="resultbank table-responsive" style="display: none;"></div>
			<br>
		@endif

		<div id="type_income">
			<div class="group">
				<p>
					<b>Prestatario</b>
					<input type="text" class="new-input-text remove" name="borrower" placeholder="Nombre de socio, grupo o tercero" data-validation="required" @if(isset($request)) @if($request->status != 2) disabled="disabled" @endif value="{{ $request->otherIncome->borrower }}" @endif>
				</p>
			</div>
			<center>
				<strong>Datos del Concepto</strong>
			</center>
			<div class="divisor">
				<div class="gray-divisor"></div>
				<div class="orange-divisor"></div>
				<div class="gray-divisor"></div>
			</div>
			<div class="container-blocks" id="container-data" @if(isset($request) && $request->status != 2) style="display: none;" @endif>
				<div class="search-table-center">
					<div class="search-table-center-row">
						<p>
							<label class="label-form">Cantidad</label><br>
							<input type="text" id="quantity" class="new-input-text" placeholder="0">
								{{--@foreach($request->otherIncome->details as $detail)--}}
									<input type="hidden" class="idDetailIncome" name="idDetail[]" value="x">
								{{--@endforeach--}}
							
						</p>
						<p>
							<select id="unit" class="form-control removeselect" multiple="multiple">
								@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
									@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
										<option value="{{ $child->description }}">{{ $child->description }}</option>
									@endforeach
								@endforeach
							</select>
						</p>
						<p>
							<label class="label-form">Descripción</label><br>
							<input type="text" id="description" class="new-input-text" placeholder="Descripción">
						</p>
						<p>
							<label class="label-form">Precio Unitario</label><br>
							<input type="text" id="unitPrice" class="new-input-text" placeholder="0.00">
						</p>
						<p>
							<label class="label-form">Tipo de IVA</label><br><br>
							<input type="radio" name="iva_kind" class="iva_kind" id="iva_no" value="no" checked=""><label for="iva_no" title="No IVA">No</label>
							<input type="radio" name="iva_kind" class="iva_kind" id="iva_a" value="a"><label for="iva_a" title="{{App\Parameter::where('parameter_name','IVA')->first()->parameter_value}}%" >A</label>
							<input type="radio" name="iva_kind" class="iva_kind" id="iva_b" value="b"><label for="iva_b" title="{{App\Parameter::where('parameter_name','IVA2')->first()->parameter_value}}%">B</label>
						</p>
						<p>
							<label class="label-form">Impuestos adicionales</label><br><br>
							<input type="radio" name="tax_new" class="tax_new" id="no_tax" value="no" checked="true"><label for="no_tax">No</label> 
							<input type="radio" name="tax_new" class="tax_new" id="si_tax" value="si" ><label for="si_tax">Sí</label>
						</p>
						<div id="taxes_exist" style="display: none;">
							<div class="left">
								<label class="label-form">Nombre del Impuesto Adicional</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text nameTax" placeholder="Impuesto Adicional">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Impuesto Adicional</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text amountTax" placeholder="$0.00">
							</div>
							<p id="newsImpuestos">
								
							</p>
							<button type="button" class="btn btn-red newImpuesto">Nuevo Impuesto</button>
							<br>
						</div>
						<p>
							<label class="label-form">Retenciones</label><br><br>
							<input type="radio" name="retention_new" class="retention" id="no_retention" value="no" checked="true"><label for="no_retention">No</label> 
							<input type="radio" name="retention_new" class="retention" id="si_retention" value="si" ><label for="si_retention">Sí</label>
						</p>
						<div id="retention_new" style="display: none;">
							<div class="left">
								<label class="label-form">Nombre</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text nameRetention" placeholder="Retención">
							</div>
							<br>
							<div class="left">
								<label class="label-form">Importe de retención</label>
							</div>
							<div class="right">
								<input type="text" class="new-input-text amountRetention" placeholder="$0.00">
							</div>
							<p id="newsRetention">
								
							</p>
							<button type="button" class="btn btn-red newRetention">Nueva Retención</button>
							<br>
						</div>
						<p>
							<label class="label-form">Subtotal</label><br>
							<input type="text" class="new-input-text" id="subtotal" placeholder="0.00" readonly="readonly">
						</p>
						<p>
							<label class="label-form">Total</label><br>
							<input type="text" class="new-input-text" id="total" placeholder="0.00" readonly="readonly">
						</p>
						<p>
							<button class="btn btn-orange" id="addArt" type="button"><span class="icon-plus"></span><span>Agregar Concepto</span></button>
						</p>
					</div>
				</div>
			</div>
			<div class="form-container">
				<div class="table-responsive table-striped">
					<table class="table">
						<thead class="thead-dark">
							<th>#</th>
							<th>Cantidad</th>
							<th>Unidad</th>
							<th>Descripci&oacute;n</th>
							<th>Precio Unitario</th>
							<th>IVA</th>
							<th>Impuesto adicional</th>
							<th>Retenciones</th>
							<th>Importe</th>
							<th></th>
						</thead>
						<tbody id="body-concepts" class="request-validate">
							@if(isset($request))
								@foreach($request->otherIncome->details as $key=>$detail)
									<tr>
										<td class="countConcept">{{$key+1}}</td>
										<td>
											{{ $detail->quantity }}
											<input type="hidden" class="idDetail"  value="{{ $detail->id }}">
											<input readonly="true" class="input-table quantity_data" type="hidden" @if(isset($new_request)) name="quantity_data[]" @endif value="{{ $detail->quantity }}">
										</td>
										<td>
											{{ $detail->unit }}
											<input readonly="true" class="input-table unit_data" type="hidden" @if(isset($new_request)) name="unit_data[]" @endif value="{{ $detail->unit }}">
										</td>
										<td>
											{{ $detail->description }}
											<input readonly="true" class="input-table description_data" type="hidden" @if(isset($new_request)) name="description_data[]" @endif value="{{ $detail->description }}">
											<input readonly="true" class="input-table type_tax_data" type="hidden" @if(isset($new_request)) name="type_tax_data[]" @endif value="{{ $detail->type_tax }}">
										</td>
										<td>
											$ {{ $detail->unit_price }}
											<input readonly="true" class="input-table unit_price_data" type="hidden" @if(isset($new_request)) name="unit_price_data[]" @endif value="{{ $detail->unit_price }}">
										</td>
										<td>
											$ {{ $detail->tax }}
											<input readonly="true" class="input-table tax_data" type="hidden" @if(isset($new_request)) name="tax_data[]" @endif  value="{{ $detail->tax }}">
										</td>
										<td>
											$ {{ number_format($detail->total_taxes,2) }}
											<input readonly="true" class="input-table total_taxes_data" type="hidden" @if(isset($new_request)) name="total_taxes_data[]" @endif value="{{ $detail->total_taxes }}">
											@foreach($detail->taxes as $tax)
												<input type="hidden" class="num_amountTax" value="{{ $tax->amount }}" @if(isset($new_request)) name="t_amountTax{{ $key }}[]"@endif >
												<input type="hidden" class="num_nameTax" value="{{ $tax->name }}" @if(isset($new_request)) name="t_nameTax{{ $key }}[]"@endif >
											@endforeach
										</td>
										<td>
											$ {{ number_format($detail->total_retentions,2) }}
											<input readonly="true" class="input-table total_retentions_data" type="hidden" @if(isset($new_request)) name="total_retentions_data[]" @endif value="{{ $detail->total_retentions }}">
											@foreach($detail->retentions as $ret)
												<input type="hidden" class="num_amountRetention" value="{{ $ret->amount }}" @if(isset($new_request)) name="t_amountRetention{{ $key }}[]"@endif >
												<input type="hidden" class="num_nameRetention" value="{{ $ret->name }}" @if(isset($new_request)) name="t_nameRetention{{ $key }}[]"@endif >
											@endforeach
											@php
												$taxesCount++;
											@endphp
										</td>
										<td>
											$ {{ $detail->total }}
											<input readonly="true" class="input-table subtotal_data" type="hidden" @if(isset($new_request)) name="subtotal_data[]" @endif value="{{ $detail->subtotal }}">
											<input readonly="true" class="input-table total_data" type="hidden" @if(isset($new_request)) name="total_data[]" @endif value="{{ $detail->total }}">
										</td>
										<td style="display: inline-table;">
											<button id="edit" class="btn btn-blue edit-item" type="button" @if($request->status != 2) disabled="disabled" @endif><span class="icon-pencil"></span></button>
											<button class="btn btn-red delete-item" @if($request->status != 2) disabled="disabled" @endif><span class="icon-x"></span></button>
										</td>
									</tr>
								@endforeach
							@endif
						</tbody>
					</table>
				</div>
				<br>
			</div>
			
			<div class="totales2">
				<!--div class="totales">
					<textarea name="note" class="input-text" placeholder="Nota" cols="80"></textarea>
				</div-->
				<div class="totales" style="margin-left: 10px;"> 
					<table>
						<tr>
							<td><label class="label-form">Subtotal:</label></td>
							<td>
								<input placeholder="$0.00" readonly class="input-table" type="text" name="subtotal" @if(isset($request)) @if($request->status == 2) value="{{ $request->otherIncome->subtotal }}" @else value="{{ number_format($request->otherIncome->subtotal,2) }}" @endif @endif>
							</td>
						</tr>
						<tr>
							<td><label class="label-form">Impuesto Adicional:</label></td>
							<td>
								<input placeholder="$0.00" readonly class="input-table" type="text" name="total_taxes" @if(isset($request)) @if($request->status == 2) value="{{ $request->otherIncome->total_taxes }}" @else value="{{ number_format($request->otherIncome->total_taxes,2) }}" @endif @endif>

							</td>
						</tr>
						<tr>
							<td><label class="label-form">Retenciones:</label></td>
							<td>
								<input placeholder="$0.00" readonly class="input-table" type="text" name="total_retentions" @if(isset($request)) @if($request->status == 2) value="{{ $request->otherIncome->total_retentions }}" @else value="{{ number_format($request->otherIncome->total_retentions,2) }}" @endif @endif>

							</td>
						</tr>
						<tr>
							<td><label class="label-form">IVA: </label></td>
							<td>
								<input placeholder="$0.00" readonly class="input-table" type="text" name="total_iva" @if(isset($request)) @if($request->status == 2) value="{{ $request->otherIncome->total_iva }}" @else value="{{ number_format($request->otherIncome->total_iva,2) }}" @endif @endif>
							</td>
						</tr>
						<tr>
							<td><label class="label-form">Total:</label></td>
							<td>
								<input id="input-extrasmall" placeholder="$0.00" readonly class="input-table" type="text" name="total" @if(isset($request)) @if($request->status == 2) value="{{ $request->otherIncome->total }}" @else value="{{ number_format($request->otherIncome->total,2) }}" @endif @endif>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<center>
			<strong>Condiciones de Pago</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div>
		<div class="form-container">
			<div class="group">
				<p>
					<label  class="label-form">Tipo de moneda</label>
					<select class="removeselect" name="type_currency" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
						<option value="MXN" @if(isset($request) && $request->otherIncome->type_currency == 'MXN') selected @endif >MXN</option>
						<option value="USD" @if(isset($request) && $request->otherIncome->type_currency == 'USD') selected @endif >USD</option>
						<option value="EUR" @if(isset($request) && $request->otherIncome->type_currency == 'EUR') selected @endif >EUR</option>
						<option value="Otro" @if(isset($request) && $request->otherIncome->type_currency == 'Otro') selected @endif >Otro</option>
					</select>
				</p>
				<p>
					<label class="label-form">Forma de pago</label>
					<select class="removeselect" multiple="multiple" name="pay_mode" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>
						<option value="Cheque" @if(isset($request)) @if($request->otherIncome->pay_mode == "Cheque") selected="selected" @endif @endif>Cheque</option>
						<option value="Efectivo" @if(isset($request)) @if($request->otherIncome->pay_mode == "Efectivo") selected="selected" @endif @endif>Efectivo</option>
						<option value="Transferencia" @if(isset($request)) @if($request->otherIncome->pay_mode == "Transferencia") selected="selected" @endif @endif>Transferencia</option>
					</select>
				</p>
				<p>
					<label class="label-form">Estado  de factura</label>
					<select class="removeselect" multiple="multiple" name="status_bill" data-validation="required" @if(isset($request) && $request->status != 2) disabled="disabled" @endif>

						@php
							$selected	= "No Aplica";
							$custom		= false;
							if(isset($request))
							{
								if($request->otherIncome->status_bill && ($request->otherIncome->status_bill != "Pendiente" && $request->otherIncome->status_bill != "Entregado" && $request->otherIncome->status_bill !="No Aplica"))
								{
									$selected	= $request->otherIncome->status_bill;
									$custom		= true;
								}
								
								$selected	= "Pendiente";
								if($request->otherIncome->status_bill == "Pendiente" || $request->otherIncome->status_bill == "")
									$selected	= "Pendiente";
								if($request->otherIncome->billStatus == "Entregado")
									$selected	= "Entregado";
								if($request->otherIncome->billStatus == "No Aplica")
									$selected	= "No Aplica";
							}
						@endphp
						@if ($custom)
							<option value="{{ $request->otherIncome->status_bill }}" selected="selected">{{ $request->otherIncome->status_bill }}</option>
						@endif
						<option value="Pendiente" @if($selected == "Pendiente") selected="selected" @endif >Pendiente</option>
						<option value="Entregado" @if($selected == "Entregado") selected="selected" @endif >Entregado</option>
						<option value="No Aplica" @if($selected == "No Aplica") selected="selected" @endif >No Aplica</option>
					</select>
				</p>
			</div>
			<div class="group">
				<p>
					<label  class="label-form">Referencia/Número de factura</label>
					<input type="text" name="reference" class="new-input-text remove" placeholder="Ingrese una referencia" @if(isset($request)) @if($request->status != 2) disabled="disabled" @endif value="{{ $request->otherIncome->reference }}" @endif>
				</p>
				<p>
					<label class="label-form">Fecha de Pago</label>
					<input type="text" name="payment_date"class="new-input-text remove datepicker" placeholder="Seleccione fecha" readonly="readonly" data-validation="required" @if(isset($request)) @if($request->status != 2) disabled="disabled" @endif  value="{{ $request->PaymentDate }}" @endif>
				</p>
				<p>
					<label class="label-form">Importe a pagar</label>
					<input type="text" id="total_pay" class="new-input-text remove" readonly placeholder="$0.00" data-validation="required" @if(isset($request)) @if($request->status != 2) disabled="disabled" @endif  value="{{ $request->otherIncome->total }}" @endif>
				</p>
			</div>
		</div>
		<br><br><br>
		@if(isset($request))
			<center>
				<strong>DOCUMENTOS</strong>
			</center>
			<div class="divisor">
				<div class="gray-divisor"></div>
				<div class="orange-divisor"></div>
				<div class="gray-divisor"></div>
			</div> 	
			<div class="table-responsive table-striped">
				<table class="table">
					<thead class="thead-dark">
						<th>Nombre</th>
						<th>Archivo</th>
						<th>Fecha</th>
					</thead>
					<tbody>
						@if(count($request->otherIncome->documents)>0)
							@foreach($request->otherIncome->documents as $doc)
								<tr>
									<td>
										{{ $doc->name }}
									</td>
									<td>
										<a target="_blank" href="{{ url('docs/other-income/'.$doc->path) }}" style="text-decoration: none; color: black;">{{ $doc->path }}</a>
									</td>
									<td>
										{{ Carbon\Carbon::parse($doc->created_at)->format('d-m-Y') }}
									</td>
								</tr>
							@endforeach
						@else
							<tr>
								<td colspan="3" width="10%">
									NO HAY DOCUMENTOS
								</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>
			<br><br><br>
		@endif
		@if(isset($request) && $request->idCheck != "")
			<p><br></p>
			<center>
				<strong>DATOS DE REVISIÓN</strong>
			</center>
			<div class="divisor">
				<div class="gray-divisor"></div>
				<div class="orange-divisor"></div>
				<div class="gray-divisor"></div>
			</div>
			<div>
				<table class="employee-details">
					<tbody>
						<tr>
							<td>
								<b>Revisó:</b>
							</td>
							<td>
								<label>{{ $request->reviewedUser->name }} {{ $request->reviewedUser->last_name }} {{ $request->reviewedUser->scnd_last_name }}</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Comentarios:</b>
							</td>
							<td>
								@if($request->checkComment == "")
									<label>Sin comentarios</label>
								@else
									<label>{{ $request->checkComment }}</label>
								@endif
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		@endif
		@if(isset($request) && $request->idAuthorize != "")
			<p><br></p>
			<center>
				<strong>DATOS DE AUTORIZACIÓN</strong>
			</center>
			<div class="divisor">
				<div class="gray-divisor"></div>
				<div class="orange-divisor"></div>
				<div class="gray-divisor"></div>
			</div>
			<div>
				<table class="employee-details">
					<tbody>
						<tr>
							<td>
								<b>Autorizó:</b>
							</td>
							<td>
								<label>{{ $request->authorizedUser->name }} {{ $request->authorizedUser->last_name }} {{ $request->authorizedUser->scnd_last_name }}</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Comentarios:</b>
							</td>
							<td>
								@if($request->authorizeComment == "")
									<label>Sin comentarios</label>
								@else
									<label>{{ $request->authorizeComment }}</label>
								@endif
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		@endif
		<p><br></p>
		<center>
			<strong>CARGAR DOCUMENTOS</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div>
		<center>
			<div id="documents"></div>
			<p>
				<button type="button" name="addDoc" class="btn btn-orange" @if(isset($request) && $request->status == 1) disabled @endif><span class="icon-plus"></span><span>Agregar documento</span></button>
			</p>
			@if(isset($request) && $request->status != 2)
				<input class="btn btn-red" type="submit" name="send" value="CARGAR" formaction="{{ route('other-income.upload-documents', $request->folio) }}" @if($request->status == 1) disabled @endif>
			@endif
		</center>
		<br>
		<span id="deleteConcepts"></span>
		@if(isset($request) && !isset($new_request))
			@if($request->status == 2)
				<center>
					<input class="btn btn-red" type="submit" name="store" value="ENVIAR">

					<input class="btn btn-blue" type="submit" name="save" value="GUARDAR" formaction="{{ route('other-income.save-update',$request->folio) }}" >
				</center>
			@endif
			@if($request->status == 1)
				<div class="mt-5 text-center">
					<a
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}" 
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}" 
						@endif 
					><button class="btn" type="button">REGRESAR</button></a>
				</div>
			@endif
		@else
			<center>
				<input class="btn btn-red" type="submit" name="store" value="ENVIAR">
				<input class="btn btn-blue" type="submit" name="save" value="GUARDAR" formaction="{{ route('other-income.save') }}">
				<input class="btn btn-delete-form" type="reset" name="borra" value="Borrar campos">
			</center>
		@endif
	{!! Form::close() !!}	
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript">
		$.validate(
		{
			form	: '#form-income',
			modules	: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				quantity	= $('#quantity').removeClass('error').val();
				unit		= $('#unit option:selected').removeClass('error').val();
				description	= $('#description').removeClass('error').val();
				unitPrice	= $('#unitPrice').removeClass('error').val();

				if (quantity != "" || description != "" || unitPrice != "" || unit != undefined) 
				{
					swal('', 'Tiene un concepto sin agregar', 'error');
					return false;
				}


				total = $('[name="total"]').val();
				if(total<0)
				{
					swal('', 'El importe total no puede ser negativo', 'error');
					return false;
				}
				if(total == 0)
				{
					swal('', 'El importe total no puede ser igual a cero', 'error');
					return false;
				}

				path	= $('.path').length;

				if(path>0)
				{
					pas=true;
					$('.path').each(function()
					{
						if($(this).val()=='')
						{
							swal('', 'Por favor cargue los documentos faltantes.', 'error');
							pas = false;
						}
					});
					if(!pas) return false;
				}

				if($('.request-validate').length>0)
				{
					
					conceptos	= $('#body-concepts tr').length;
					if(conceptos>0)
					{
						if($('#banks-body tr').length>0)
						{
							if ($('.checkbox').is(':checked')) 
							{
								swal("Cargando",{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
								return true;
							}
							else
							{
								swal('', 'Debe seleccionar una cuenta de la empresa', 'error');
								return false;
							}
							
						}
						else
						{
							swal('', 'Debe seleccionar un cuenta de la empresa.', 'error');
							return false;
						}
					}
					else
					{
						swal('', 'Debe ingresar al menos un concepto.', 'error');
						return false;
					}
				}
				else
				{	
					swal("Cargando",{
						icon: '{{ asset(getenv('LOADING_IMG')) }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
					
				}
			}
		});
		$(document).ready(function()
		{
			count 	= 0;
			countB	= {{ $taxesCount }};
			$('#quantity,#unitPrice,#subtotal,#total,.amountTax,.amountRetention',).numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
			$(".datepicker").datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
			$('[name="type_income"]').select2(
			{
				placeholder				: 'Tipo de Ingreso',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="enterprise_id"]').select2(
			{
				placeholder				: 'Seleccione una empresa',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="project_id"]').select2(
			{
				placeholder				: 'Seleccione un proyecto',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="request_id"]').select2(
			{
				placeholder				: 'Seleccione al solicitante',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('#unit').select2(
			{
				placeholder				: 'Unidad',
				language				: "es",
				maximumSelectionLength	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="type_currency"],[name="pay_mode"],[name="status_bill"]').select2(
			{
				placeholder 			: 'Seleccione uno',
				language 				: "es",
				maximumSelectionLength 	: 1,
				width 					: '100%'
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$(document).on('change','[name="enterprise_id"]',function()
			{
				idEnterprise = $(this).val();
				$.ajax({
					type : 'get',
					url  : '{{ url("administration/income/search/bank") }}',
					data : {'idEnterprise':idEnterprise},
					success:function(data)
					{
						$('.resultbank').html(data);
						$('.resultbank').stop().fadeIn();
					},
					error:function(data)
					{
						$('.resultbank').stop().fadeOut();
					}
				});
			})
			.on('click','#addArt',function()
			{
				countConcept	= $('.countConcept').length;
				cant			= $('#quantity').removeClass('error').val();
				unit			= $('#unit option:selected').removeClass('error').val();
				descr			= $('#description').removeClass('error').val();
				precio			= $('#unitPrice').removeClass('error').val();
				idDetail		= $('.idDetailIncome').val();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivakind			= $('[name="iva_kind"]:checked').val();
				ivaCalc			= 0;
				totalTaxes		= 0;
				totalRetentions	= 0;
				subtotal 		= $('#subtotal').val();

				if (cant == "" || descr == "" || precio == "" || unit == "")
				{
					if(cant=="")
					{
						$('#quantity').addClass('error');
					}
					if(unit=="")
					{
						$('#unit').addClass('error');
					}
					if(descr=="")
					{
						$('#description').addClass('error');
					}
					if(precio=="")
					{
						$('#unitPrice').addClass('error');
					}
					swal('', 'Por favor llene todos los campos.', 'error');
				}
				else
				{
					switch($('[name="iva_kind"]:checked').val())
					{
						case 'no':
							ivaCalc = 0;
							break;
						case 'a':
							ivaCalc = cant*precio*iva;
							break;
						case 'b':
							ivaCalc = cant*precio*iva2;
							break;
					}

					nameTaxes = $('<td></td>');
					$('.nameTax').each(function(i,v)
					{
						nameTax = $(this).val();
						nameTaxes.append($('<input type="hidden" class="num_nameTax" name="t_nameTax'+countB+'[]">').val(nameTax));
					});

					amountsTaxes = $('<td></td>');
					$('.amountTax').each(function(i,v)
					{
						amountTax = $(this).val();
						amountsTaxes.append($('<input type="hidden" class="num_amountTax" name="t_amountTax'+countB+'[]">').val(amountTax));
						totalTaxes = Number(totalTaxes) + Number(amountTax);
					});

					nameRetentions = $('<td></td>');
					$('.nameRetention').each(function(i,v)
					{
						nameRetention = $(this).val();
						nameRetentions.append($('<input type="hidden" class="num_nameRetention" name="t_nameRetention'+countB+'[]">').val(nameRetention));
					});

					amountsRetentions = $('<td></td>');
					$('.amountRetention').each(function(i,v)
					{
						amountRetention = $(this).val();
						amountsRetentions.append($('<input type="hidden" class="num_amountRetention" name="t_amountRetention'+countB+'[]">').val(amountRetention));
						totalRetentions = Number(totalRetentions)+Number(amountRetention);
					});

					total = ((cant*precio)+ivaCalc+totalTaxes)-totalRetentions;

					if(total < 0)
					{
						swal('','El costo total no puede ser negativo','warning');
					}
					
					if(idDetail != 'x')
					{

						
							total = ((cant*precio)+ivaCalc+totalTaxes)-totalRetentions;
							countConcept = countConcept+1;
							tr_table	= $('<tr></tr>')
										.append($('<td class="countConcept"></td>')
											.append(countConcept))
										.append($('<td></td>')
											.append(cant)
											.append($('<input readonly="true" class="input-table quantity_data" type="hidden" name="quantity_data[]"/>').val(cant))
											.append($('<input type="hidden" class="idDetail" name="idDetail[]">').val()))
										.append($('<td></td>')
											.append(unit)
											.append($('<input class="input-table unit_data" type="hidden" name="unit_data[]"/>').val(unit)))
										.append($('<td></td>')
											.append(descr)
											.append($('<input readonly="true" class="input-table description_data" type="hidden" name="description_data[]"/>').val(descr)))
										.append($('<td hidden></td>')
											.append($('<input readonly="true" class="input-table type_tax_data" type="hidden" name="type_tax_data[]"/>').val(ivakind)))
										.append($('<td></td>')
											.append('$ '+Number(precio).toFixed(2))
											.append($('<input readonly="true" class="input-table unit_price_data"  type="hidden" name="unit_price_data[]"/>').val(precio)))
										.append($('<td></td>')
											.append('$ '+Number(ivaCalc).toFixed(2))
											.append($('<input readonly="true" class="input-table tax_data" type="hidden" name="tax_data[]"/>').val(ivaCalc)))
										.append($('<td></td>')
											.append('$ '+Number(totalTaxes).toFixed(2))
											.append($('<input readonly="true" class="input-table total_taxes_data" type="hidden" name="total_taxes_data[]"/>').val(totalTaxes)))
										.append($('<td></td>')
											.append('$ '+Number(totalRetentions).toFixed(2))
											.append($('<input readonly="true" class="input-table total_retentions_data" type="hidden" name="total_retentions_data[]"/>').val(totalRetentions)))
										.append($('<td></td>')
											.append('$ '+Number(total).toFixed(2))
											.append($('<input readonly="true" class="input-table subtotal_data" type="hidden" name="subtotal_data[]"/>').val(subtotal))
											.append($('<input readonly="true" class="input-table total_data" type="hidden" name="total_data[]"/>').val(total)))
										.append($('<td hidden></td>')
											.append(nameTaxes)
											.append(amountsTaxes)
											.append(nameRetentions)
											.append(amountsRetentions))
										.append($('<td style="display: inline-table;"></td>')
											.append($('<button class="btn btn-blue edit-item" type="button"></button>')
												.append($('<span class="icon-pencil"></span>')))
											.append($('<button class="btn btn-red delete-item"></button>')
												.append($('<span class="icon-x"></span>'))));

							$('#body-concepts').append(tr_table);
							$('#quantity,#description,#unitPrice').removeClass('error').val('');
							$('[name="iva_kind"],[name="tax_new"],[name="retention_new"]').prop('checked',false);
							$('#iva_no,#no_retention,#no_tax').prop('checked',true);
							$('#unit').val(null).trigger('change');
							$('#newsImpuestos,#newsRetention').empty();
							$('.nameTax,.amountTax,.nameRetention,.amountRetention,#subtotal,#total').val('');
							$('#taxes_exist,#retention_new').stop(true,true).slideUp().hide();
							total_cal();
							countB++;
						
						
					}
					else
					{
						
						total = ((cant*precio)+ivaCalc+totalTaxes)-totalRetentions;
						countConcept = countConcept+1;
						tr_table	= $('<tr></tr>')
									.append($('<td class="countConcept"></td>')
										.append(countConcept))
									.append($('<td></td>')
										.append(cant)
										.append($('<input readonly="true" class="input-table quantity_data" type="hidden" name="quantity_data[]"/>').val(cant))
										.append($('<input type="hidden" class="idDetail" name="idDetail[]">').val(idDetail)))
									.append($('<td></td>')
										.append(unit)
										.append($('<input class="input-table unit_data" type="hidden" name="unit_data[]"/>').val(unit)))
									.append($('<td></td>')
										.append(descr)
										.append($('<input readonly="true" class="input-table description_data" type="hidden" name="description_data[]"/>').val(descr)))
									.append($('<td hidden></td>')
										.append($('<input readonly="true" class="input-table type_tax_data" type="hidden" name="type_tax_data[]"/>').val(ivakind)))
									.append($('<td></td>')
										.append('$ '+Number(precio).toFixed(2))
										.append($('<input readonly="true" class="input-table unit_price_data"  type="hidden" name="unit_price_data[]"/>').val(precio)))
									.append($('<td></td>')
										.append('$ '+Number(ivaCalc).toFixed(2))
										.append($('<input readonly="true" class="input-table tax_data" type="hidden" name="tax_data[]"/>').val(ivaCalc)))
									.append($('<td></td>')
										.append('$ '+Number(totalTaxes).toFixed(2))
										.append($('<input readonly="true" class="input-table total_taxes_data" type="hidden" name="total_taxes_data[]"/>').val(totalTaxes)))
									.append($('<td></td>')
										.append('$ '+Number(totalRetentions).toFixed(2))
										.append($('<input readonly="true" class="input-table total_retentions_data" type="hidden" name="total_retentions_data[]"/>').val(totalRetentions)))
									.append($('<td></td>')
										.append('$ '+Number(total).toFixed(2))
										.append($('<input readonly="true" class="input-table subtotal_data" type="hidden" name="subtotal_data[]"/>').val(subtotal))
										.append($('<input readonly="true" class="input-table total_data" type="hidden" name="total_data[]"/>').val(total)))
									.append($('<td hidden></td>')
										.append(nameTaxes)
										.append(amountsTaxes)
										.append(nameRetentions)
										.append(amountsRetentions))
									.append($('<td style="display: inline-table;"></td>')
										.append($('<button class="btn btn-blue edit-item" type="button"></button>')
											.append($('<span class="icon-pencil"></span>')))
										.append($('<button class="btn btn-red delete-item"></button>')
											.append($('<span class="icon-x"></span>'))));

						$('#body-concepts').append(tr_table);
						$('#quantity,#description,#unitPrice').removeClass('error').val('');
						$('[name="iva_kind"],[name="tax_new"],[name="retention_new"]').prop('checked',false);
						$('#iva_no,#no_retention,#no_tax').prop('checked',true);
						$('#unit').val(null).trigger('change');
						$('#newsImpuestos,#newsRetention').empty();
						$('.nameTax,.amountTax,.nameRetention,.amountRetention,#subtotal,#total').val('');
						$('#taxes_exist,#retention_new').stop(true,true).slideUp().hide();
						total_cal();
						countB++;
					}
					
				}
			})
			.on('click','.checkbox',function()
			{
				$('.marktr').removeClass('marktr');
				$(this).parents('tr').addClass('marktr');
			})
			.on('click','[name="retention_new"]',function()
			{
				if($(this).val() == 'si')
				{
					$('#retention_new').stop(true,true).slideDown().show();
				}
				else
				{
					$('#retention_new').stop(true,true).slideUp().hide();
				}
			})
			.on('click','.newRetention',function()
			{
				newR = $('<span class="span-taxes"></span>')
						.append($('<div class="left"></div>')
							.append($('<label class="label-form">Nombre de la Retención</label>')))
						.append($('<div class="right"></div>')
							.append($('<input type="text" class="input-text nameRetention" placeholder="Impuesto Adicional">')))
						.append($('<br>'))
						.append($('<div class="left"></div>')
							.append($('<label class="label-form">Importe</label>')))
						.append($('<div class="right"></div>')
							.append($('<input type="text" class="input-text amountRetention" placeholder="$0.00">'))
							.append($('<button class="span-delete btn btn-red" type="button">Quitar</button>')));

				$('#newsRetention').append(newR);
				$('.amountRetention',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amountRetention').on("contextmenu",function(e)
				{
					return false;
				});
			})
			.on('click','[name="tax_new"]',function()
			{
				if($(this).val() == 'si')
				{
					$('#taxes_exist').stop(true,true).slideDown().show();
				}
				else
				{
					$('#taxes_exist').stop(true,true).slideUp().hide();
				}
			})
			.on('click','.newImpuesto',function()
			{
				newI = $('<span class="span-taxes"></span>')
						.append($('<div class="left"></div>')
							.append($('<label class="label-form">Nombre del Impuesto Adicional</label>')))
						.append($('<div class="right"></div>')
							.append($('<input type="text" class="input-text nameTax" placeholder="Impuesto Adicional">')))
						.append($('<br>'))
						.append($('<div class="left"></div>')
							.append($('<label class="label-form">Importe</label>')))
						.append($('<div class="right"></div>')
							.append($('<input type="text" class="input-text amountTax" placeholder="$0.00">'))
							.append($('<button class="span-delete btn btn-red" type="button">Quitar</button>')));

				$('#newsImpuestos').append(newI);
				$('.amountTax',).numeric({ altDecimal: ".", decimalPlaces: 2 });
				$('.amountTax').on("contextmenu",function(e)
				{
					return false;
				});
			})
			.on('click','.span-delete',function()
			{
				$(this).parents('span').remove();
			})
			.on('click','.delete-item',function()
			{
				id = $(this).parents('tr').find('.idDetail').val();
				if (id != "x") 
				{
					$('#deleteConcepts').append($('<input type="hidden" name="deleteConcepts[]" value="'+id+'">'));
				}

				$(this).parents('tr').remove();
				total_cal();
				countB = $('#body-concepts tr').length;
				$('#body-concepts tr').each(function(i,v)
				{
					$(this).find('.num_nameTax').attr('name','t_nameTax'+i+'[]');
					$(this).find('.num_amountTax').attr('name','t_amountTax'+i+'[]');
					$(this).find('.num_nameRetention').attr('name','t_nameRetention'+i+'[]');
					$(this).find('.num_amountRetention').attr('name','t_amountRetention'+i+'[]');
				});
				if($('.countConcept').length>0)
				{
					$('.countConcept').each(function(i,v)
					{
						$(this).html(i+1);
					});
				}
			})

			.on('click','.edit-item',function()
			{
				cant				= $('#quantity').removeClass('error').val();
				unit				= $('#unit option:selected').removeClass('error').val();
				descr				= $('#description').removeClass('error').val();
				precio				= $('#unitPrice').removeClass('error').val();
				if (cant == "" || descr == "" || precio == "" || unit == "") 
				{
					tquanty		= $(this).parents('tr').find('.quantity_data').val();
					tidDetail	= $(this).parents('tr').find('.idDetail').val();
					tunit		= $(this).parents('tr').find('.unit_data').val();
					tdescr		= $(this).parents('tr').find('.description_data').val();
					tivakind	= $(this).parents('tr').find('.type_tax_data').val();
					tprice		= $(this).parents('tr').find('.unit_price_data').val();
					tsubtotal	= $(this).parents('tr').find('.subtotal_data').val();
					ttotal 		= $(this).parents('tr').find('.total_data').val();

					swal({
						title		: "Editar concepto",
						text		: "Al editar, se eliminarán los impuestos adicionales y retenciones agregados ¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((continuar) =>
					{
						if(continuar)
						{
							if(tivakind == 'a')
							{
								$('#iva_a').prop("checked",true);
							}
							else if(tivakind == 'b')
							{
								$('#iva_b').prop("checked",true);
							}
							else
							{
								$('#iva_no').prop("checked",true);
							}

							$('#quantity').val(tquanty);
							$('#unit').val(tunit).trigger('change');
							$('#description').val(tdescr);
							$('#unitPrice').val(tprice);
							$('#subtotal').val(tsubtotal);
							$('#total').val(ttotal);
							$('.idDetailIncome').val(tidDetail);
							
							$(this).parents('tr').remove();
							total_cal();
							countB = $('#body-concepts tr').length;
							$('#body-concepts tr').each(function(i,v)
							{
								$(this).find('.num_nameTax').attr('name','t_nameTax'+i+'[]');
								$(this).find('.num_amountTax').attr('name','t_amountTax'+i+'[]');
								$(this).find('.num_nameRetention').attr('name','t_nameRetention'+i+'[]');
								$(this).find('.num_amountRetention').attr('name','t_amountRetention'+i+'[]');
							});
							if($('.countConcept').length>0)
							{
								$('.countConcept').each(function(i,v)
								{
									$(this).html(i+1);
								});
							}
						}
						else
						{
							swal.close();
						}
					});
					//$(".idDetailIncome").val($(".idDetail").val());
				}
				else
				{
					swal('', 'Tiene un concepto sin agregar a la lista', 'error');
				}
				
			})

			.on('change','#quantity,#unitPrice,.iva_kind,.amountTax,.amountRetention',function()
			{
				cant			= $('#quantity').val();
				precio			= $('#unitPrice').val();
				iva				= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
				iva2			= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
				ivaCalc			= 0;
				totalTaxes		= 0;
				totalRetentions	= 0;


				switch($('[name="iva_kind"]:checked').val())
				{
					case 'no':
						ivaCalc = 0;
						break;
					case 'a':
						ivaCalc = cant*precio*iva;
						break;
					case 'b':
						ivaCalc = cant*precio*iva2;
						break;
				}

				amountsTaxes = 0;
				$('.amountTax').each(function(i,v)
				{
					amountTax = $(this).val();
					totalTaxes = Number(totalTaxes) + Number(amountTax);
				});

				amountsRetentions = 0;
				$('.amountRetention').each(function(i,v)
				{
					amountRetention = $(this).val();
					totalRetentions = Number(totalRetentions)+Number(amountRetention);
				});

				subtotal	= cant * precio;
				total		= ((cant * precio)+ivaCalc+totalTaxes)-totalRetentions;

				$('#subtotal').val(subtotal.toFixed(2));
				$('#total').val(total.toFixed(2));
			})
			.on('click','[name="save"]',function()
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('change','[name="taxPayment"]',function()
			{
				if ($('[name="taxPayment"]:checked').val() == "1") 
				{
					$('.iva_kind').prop('disabled',false);
					$('#iva_no').prop('checked',true);

				}
				else if ($('[name="taxPayment"]:checked').val() == "0") 
				{
					$('.iva_kind').prop('disabled',true);
					$('#iva_no').prop('checked',true);

				}
			})
			.on('click','[name="addDoc"]',function()
			{
				newdoc	= $('<div class="docs-p"></div>')
							.append($('<div class="docs-p-l"></div>')
								.append($('<select class="custom-select nameDocument" name="nameDocument[]"></select><br><br>')
									.append($('<option value="Cotización">Cotización</option>'))
									.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
									.append($('<option value="Control de Calidad">Control de Calidad</option>'))
									.append($('<option value="Contrato">Contrato</option>'))
									.append($('<option value="Factura">Factura</option>'))
									.append($('<option value="Ticket">Ticket</option>'))
									.append($('<option value="Otro">Otro</option>')))
								.append($('<div class="uploader-content"></div>')
									.append($('<input type="file" name="path" class="input-text pathActioner" accept=".pdf,.jpg,.png">'))	
								)
								.append($('<input type="hidden" name="realPath[]" class="path">')
									)
							)
							.append($('<div class="docs-p-r"></div>')
								.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
								)
							);
				$('#documents').append(newdoc);
				$(function() 
				{
					$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
				});
			})
			.on('change','.input-text.pathActioner',function(e)
			{
				filename		= $(this);
				uploadedName	= $(this).parent('.uploader-content').siblings('[name="realPath[]"]');
				extention 		= /\.jpg|\.png|\.jpeg|\.pdf/i;

				if (filename.val().search(extention) == -1) 
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if (this.files[0].size>315621376)
				{
					swal('','El tamaño máximo de su archivo no debe ser mayor a 300Mb','warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match(/\bimage_\S+/g) || []).join(' ');
					});
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop('files')[0]);
					formData.append(uploadedName.attr('name'), uploadedName.val());

					$.ajax(
					{
						type : 'POST',
						url : '{{ route('other-income.upload-file') }}',
						data : formData,
						contentType : false,
						processData :false,
						success : function(r)
						{
							if (r.error == 'DONE') 
							{
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(e.currentTarget).parent('.uploader-content').siblings('[name="realPath[]"]').val(r.path);
								$(e.currentTarget).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(e.currentTarget).val('');
								$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
							}
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					});
				}
			})
			.on('click','.delete-doc',function()
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false
				});
				actioner		= $(this);
				uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
				formData		= new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ url("/administration/purchase/upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('.docs-p').remove();
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						form[0].reset();
						$('#body').html('');
						$('#documents').empty();
						$('#body-concepts').empty();
						$('.removeselect').val(null).trigger('change');
					}
					else
					{
						swal.close();
					}
				});
			})
		});
		function total_cal()
		{
			subtotal		= 0;
			iva				= 0;
			amountTax		= 0;
			amountRetention	= 0;
			total 			= 0;
			$("#body-concepts tr").each(function(i, v)
			{
				subtotal		+= Number($(this).find('.subtotal_data').val());
				iva				+= Number($(this).find('.tax_data').val());
				amountTax		+= Number($(this).find('.total_taxes_data').val());
				amountRetention	+= Number($(this).find('.total_retentions_data').val());
				total 			+= Number($(this).find('.total_data').val());
			});
			$('[name="subtotal"]').val(Number(subtotal).toFixed(2));
			$('[name="total_iva"]').val(Number(iva).toFixed(2));
			$('[name="total"],#total_pay').val(Number(total).toFixed(2));
			$('[name="total_taxes"]').val(Number(amountTax).toFixed(2));
			$('[name="total_retentions"]').val(Number(amountRetention).toFixed(2));
		}
		@if(isset($alert))
			{!! $alert !!}
		@endif
	</script>
@endsection
