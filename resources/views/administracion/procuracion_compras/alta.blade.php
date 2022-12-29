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
	@if(isset($request))
		{!! Form::open(['route' => ['procurement-purchases.purchase-update',$request->id], 'method' => 'PUT', 'id' => 'container-alta','files' => true]) !!}
	@else
		{!! Form::open(['route' => 'procurement-purchases.purchase-save', 'method' => 'POST', 'id' => 'container-alta','files' => true]) !!}
	@endif
		<div class="group">
			<div class="card">
				<div class="card-header card-header-dark">
					SELECCIONE UNA CUENTA
				</div>
				<div class="card-body">
					<label><b>Cuenta:</b></label>
					<select class="form-control removeselect" name="account" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
						@foreach(App\CatAccounts::all() as $acc)
							<option value="{{ $acc->id }}" @if(isset($request) && $request->account == $acc->id) selected="selected" @endif>{{ $acc->fullName() }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>
		<p><br></p>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-dark" style="min-width: 100%;">
					<th colspan="4">ORDEN DE COMPRA</th>
				</thead>
			</table>
		</div>
		<div class="form-row px-3">
			<div class="form-group col-md-6 mb-4">
				<label><b>Proyecto:</b></label>
				<select name="project_id" class="form-control removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					@foreach(App\Project::where('status',1)->whereIn('idproyect',[74,75])->orderBy('proyectName','asc')->get() as $project)
						<option value="{{ $project->idproyect }}" @if(isset($request) && $request->project_id == $project->idproyect) selected="selected" @endif>{{ $project->proyectName }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6 mb-4 select_father" @if(isset($request)) @if($request->project_id == 75) style="display: table-row;" @endif @else style="display: table-row;" @endif>
				<label><b>Código WBS:</b></label>
				<select name="code_wbs" class="removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					@foreach(App\CatCodeWBS::orderBy('code_wbs','asc')->get() as $code)
						<option value="{{ $code->id }}" @if(isset($request) && $request->code_wbs == $code->id) selected="selected" @endif>{{ $code->code_wbs }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Moneda:</b></label>
				<select class="removeselect" name="type_currency" multiple="multiple" data-validation="required"  @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					<option value="MXN" @if(isset($request) && $request->type_currency == 'MXN') selected="selected" @endif>MXN</option>
					<option value="USD" @if(isset($request) && $request->type_currency == 'USD') selected="selected" @endif>USD</option>
					<option value="EUR" @if(isset($request) && $request->type_currency == 'EUR') selected="selected" @endif>EUR</option>
					<option value="Otro" @if(isset($request) && $request->type_currency == 'Otro') selected="selected" @endif>Otro</option>
				</select>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Estatus:</b></label>
				<select name="status" class="form-control" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					@foreach(App\StatusRequest::whereIn('idrequestStatus',[24,25,26])->get() as $status)
						<option value="{{ $status->idrequestStatus }}" @if(isset($request) && $request->status == $status->idrequestStatus) selected="selected" @endif>{{ $status->description }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Número OC:</b></label>
				<input type="text" name="numberOrder" class="new-input-text removeselect" placeholder="Ej. 0001" data-validation="required" @isset($request) value="{{ $request->numberOrder }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			
			<div class="form-group col-md-6 mb-4">
				<label><b>CO#:</b></label>
				<input type="text" name="numberCO" class="new-input-text removeselect" placeholder="Ej. 1000" data-validation="required" @isset($request) value="{{ $request->numberCO }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Contrato:</b></label>
				<input type="text" name="contract" class="new-input-text removeselect" placeholder="Ej. 1000" data-validation="required" @isset($request) value="{{ $request->contract }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Descripción:</b></label>
				<input type="text" name="descriptionShort" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->descriptionShort }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			
			<div class="form-group col-md-6 mb-4 required-account" @if(isset($request) && $request->account == 5) style="display:none;" @endif>
				<label><b>Fecha req. en sitio:</b></label>
				<input type="text" class="new-input-text removeselect datepicker" name="date_obra" data-validation="required" placeholder="Seleccione una fecha" readonly="readonly" value="{{ isset($request) ? $request->date_obra!="" ? $request->date_obra->format('Y-m-d') : '' : '' }}" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
			</div>
			<div class="form-group col-md-6 mb-4 required-account" @if(isset($request) && $request->account == 5) style="display:none;" @endif>
				<label><b>Fecha promesa entrega:</b></label>
				<input type="text" class="new-input-text removeselect datepicker" name="date_promise" data-validation="required" placeholder="Seleccione una fecha" readonly="readonly" value="{{ isset($request) ? $request->date_promise!="" ? $request->date_promise->format('Y-m-d') : '' : '' }}" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Fecha Elaboración:</b></label>
				<input type="text" name="date_request" class="new-input-text datepicker2 removeselect" placeholder="Seleccione una fecha" readonly="readonly" data-validation="required" @isset($request) value="{{ $request->date_request!="" ? $request->date_request->format('Y-m-d') : '' }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4 required-account" @if(isset($request) && $request->account == 5) style="display:none;" @endif>
				<label><b>Fecha Cierre:</b></label>
				<input type="text" name="date_close" class="new-input-text datepicker2 removeselect" placeholder="Seleccione una fecha"  readonly="readonly" data-validation="required" @isset($request) value="{{ $request->date_close!="" ? $request->date_close->format('Y-m-d') : '' }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Destino:</b></label>
				<input type="text" name="destination" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->destination }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Sitio:</b></label>
				<input type="text" name="site" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->site }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Ingeniero:</b></label>
				<input type="text" name="engineer" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->engineer }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Comprador:</b></label>
				<select name="buyer" class="form-control removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					@foreach(App\CatBuyer::orderBy('name','ASC')->get() as $buyer)
						<option value="{{ $buyer->name }}" @if(isset($request) && $request->buyer != "" && $request->buyer == $buyer->name) selected="selected" @endif>{{ $buyer->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Expedidor:</b></label>
				<select name="expeditor" class="form-control removeselect" multiple="multiple" data-validation="required" @if(isset($request) && $request->status != 24) disabled="disabled" @endif>
					@foreach(App\CatExpeditor::orderBy('name','ASC')->get() as $expeditor)
						<option value="{{ $expeditor->name }}" @if(isset($request) && $request->expeditor != "" && $request->expeditor == $expeditor->name) selected="selected" @endif>{{ $expeditor->name }}</option>
					@endforeach
				</select>
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
				<textarea type="text" name="descriptionLong" class="new-input-text removeselect" placeholder="Orden de compra (especificación técnica)." data-validation="required" rows="10" @if(isset($request) && $request->status != 24) disabled="disabled" @endif >@isset($request) {{ $request->descriptionLong }} @endisset</textarea>
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
				<input type="text" name="provider" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->provider }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Ubicación:</b></label>
				<input type="text" name="ubicationProvider" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->ubicationProvider }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Contacto:</b></label>
				<input type="text" name="contactProvider" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->contactProvider }}" @if($request->status != 24) disabled="disabled" @endif  @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Teléfono:</b></label>
				<input type="text" name="phoneProvider" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->phoneProvider }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
			</div>
			<div class="form-group col-md-6 mb-4">
				<label><b>Email:</b></label>
				<input type="text" name="emailProvider" class="new-input-text removeselect" placeholder="Escriba aquí..." data-validation="required" @isset($request) value="{{ $request->emailProvider }}" @if($request->status != 24) disabled="disabled" @endif @endisset>
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
						<th></th>
					</tr>
				</thead>
				<tbody id="body_art" class="request-validate">
					@isset($request)
						@foreach($request->details as $detail)
							<tr>
								<td>
									{{ $detail->part }}
									<input type="hidden" name="part[]" class="input-text t_part" placeholder="0" value="{{ $detail->part }}">
									<input type="hidden" class="idDetail" value="x">
									<input type="hidden" name="code[]" class="input-text t_code" placeholder="0" value="{{ $detail->code }}">
									<input type="hidden" name="unit[]" class="input-text t_unit" placeholder="0" value="{{ $detail->unit }}">
									<input type="hidden" name="description[]" class="input-text t_description" placeholder="0" value="{{ $detail->description }}">
									<input type="hidden" name="quantity[]" class="input-text t_quantity" placeholder="0" value="{{ $detail->quantity }}">
									<input type="hidden" name="price[]" class="input-text t_price" placeholder="0" value="{{ $detail->price }}">
									<input type="hidden" name="total_concept[]" class="input-text t_total_concept" placeholder="0" value="{{ $detail->total_concept }}">
									<input type="hidden" name="type_currency_concept[]" class="input-text t_type_currency" placeholder="0" value="{{ $detail->type_currency }}">
									<input type="hidden" name="date_one[]" class="input-text t_date_one" placeholder="0" value="{{ $detail->date_one->format('Y-m-d') }}">
									<input type="hidden" name="date_two[]" class="input-text t_date_two" placeholder="0" value="{{ $detail->date_two->format('Y-m-d') }}">
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
								<td>
									@if(isset($request) && $request->status == 24)
										<button class="btn btn-red delete-art" type="button"><span class="icon-x"></span></button>
									@endif
								</td>
							</tr>
						@endforeach
					@endisset
				</tbody>
				<tfoot>
					@if(!isset($request) || (isset($request) && $request->status == 24))
						<tr>
							<td>
								<input type="text" class="part new-input-text" placeholder="0">
							</td>
							<td>
								<input type="text" class="code new-input-text" placeholder="0">
							</td>
							<td>
								<select class="js-measurement_compras unit form-control" multiple="multiple">
								 	@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
										@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
									  		<option value="{{ $child->description }}">{{ $child->description }}</option>
										@endforeach
								  	@endforeach
								</select>
							</td>
							<td>
								<input type="text" class="description new-input-text" placeholder="0">
							</td>
							<td>
								<input type="text" class="quantity new-input-text" placeholder="0">
							</td>
							<td>
								<input type="text" class="price new-input-text" placeholder="0">
							</td>
							<td>
								<input type="text" class="total_concept new-input-text" placeholder="0" readonly="readonly">
							</td>
							<td>
								<select class="form-control type_currency" multiple="multiple">
									<option value="MXN">MXN</option>
									<option value="USD">USD</option>
									<option value="EUR">EUR</option>
									<option value="Otro">Otro</option>
								</select>
							</td>
							<td>
								<input type="text" class="date_one new-input-text" placeholder="De clic.." readonly="readonly">
							</td>
							<td>
								<input type="text" class="date_two new-input-text" placeholder="De clic.." readonly="readonly">
							</td>
							<td>
								<button class="btn btn-green" id="addArt" type="button"><span class="icon-plus"></span></button>
							</td>
						</tr>
					@endif
				</tfoot>
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
				<tbody id="body_milestone">
					@if(isset($request))
						@foreach ($request->milestones as $m)
							<tr>
								<td>
									{{ $m->seq_num }} <input type="hidden" name="seq_num_t[]" value="{{ $m->seq_num }}">
								</td>
								<td>
									{{ $m->milestone }} <input type="hidden" name="milestone_t[]" value="{{ $m->milestone }}">
								</td>
								<td>
									<input type="text" name="schedule_t[]" class="new-input-text" value="{{ $m->schedule!="" ? $m->schedule->format('Y-m-d') : '' }}" readonly>
								</td>
								<td>
									<input type="text" name="status_milestone_t[]" class="new-input-text" value="{{ $m->status }}">
								</td>
								<td>
									<input type="text" name="complete_status_t[]" class="new-input-text" value="{{ $m->complete_status!="" ? $m->complete_status->format('Y-m-d') : '' }}" readonly>
								</td>
							</tr>
						@endforeach
					@else
						<tr>
							<td>1 <input type="hidden" name="seq_num_t[]" value="1"></td>
							<td> 
								<select name="milestone_t[]" class="form-control milestone_t" multiple="multiple">
									@foreach(App\CatMilestones::all() as $m)
										<option value="{{ $m->name }}">{{ $m->name }}</option>
									@endforeach
								</select>
							</td>
							<td><input type="text" name="schedule_t[]" class="new-input-text" readonly></td>
							<td><input type="text" name="status_milestone_t[]" class="new-input-text"></td>
							<td><input type="text" name="complete_status_t[]" class="new-input-text" readonly></td>
							<!--td><button class="btn btn-red delete-milestone" type="button"><span class="icon-x"></span></button></td-->
						</tr>
					@endif
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5"><button class="btn btn-green add-milestone" type="button"><span class="icon-plus"></span><span>Agregar Nuevo</span></button></td>
					</tr>
				</tfoot>
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
							<b>Fecha: </b> {{ $remark->date->format('dMy') }} <input type="hidden" class="datepicker2" name="date_remark[]" value="{{ $remark->date->format('Y-m-d') }}">
						</p>
						<p>
							<b>Observación: </b> {{ $remark->remark }} <input type="hidden" name="remark[]" value="{{ $remark->remark }}">
						</p>
						<div style="width:100%; height: 1px; background: gray;"></div>
					</div>
				@endforeach
			@endisset
		</div>
		<div class="form-row px-3 remarks">
			<div class="form-group col-md-12 mb-4">
				<label><b>Fecha:</b></label>
				<input type="text" name="date_remark[]" class="input-text date_remark datepicker2" placeholder="Seleccione una fecha" readonly="readonly"><br>
				<label><b>Descripción:</b></label>
				<textarea type="text" name="remark[]" class="new-input-text removeselect" placeholder="Escriba aquí..."  rows="10"></textarea>
			</div>
		</div>
		<button class="btn btn-green add-remark" type="button"><span class="icon-plus"></span><span>Agregar Otra Observación</span></button>
		<p><br></p>
		<span id="delete"></span>
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
										{{ $history->procurementPurchase->date_request != "" ? $history->procurementPurchase->date_request->format('Y-m-d') : 'Sin Fecha' }}
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
			@if(isset($request))
				@if($request->status == 24)
					<button class="btn btn-blue" name="save" type="submit" formaction="{{ route('procurement-purchases.purchase-update',$request->id) }}"><i class="fas fa-save"></i> GUARDAR ORDEN</button>
				@else
					<button class="btn btn-red" name="save" type="submit" formaction="{{ route('procurement-purchases.purchase-save-remarks',$request->id) }}"><i class="fas fa-save"></i> GUARDAR OBSERVACIONES</button>
				@endif
			@else
				<button class="btn btn-blue" name="save" type="submit"><i class="fas fa-save"></i> GUARDAR ORDEN</button>
			@endif

		</center>
		<div id="myModal" class="modal"></div>

	{!! Form::close() !!}
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script type="text/javascript">

		$.validate(
		{
			form: '#container-alta',
			modules		: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				if ($('.request-validate').length > 0) 
				{	
					part			= $('.part').val();
					code			= $('.code').val();
					unit			= $('.unit option:selected').val();
					description		= $('.description').val();
					quantity		= $('.quantity').val();
					price			= $('.price').val();
					total_concept	= $('.total_concept').val();
					type_currency	= $('.type_currency option:selected').val();
					date_one		= $('.date_one').val();
					date_two		= $('.date_two').val();

					$('.part,.code,.unit,.description,.quantity,.price,.total_concept,.type_currency,.date_one,.date_two').removeClass('error');

					if (part != "" || code != "" || unit != undefined || description != "" || quantity != "" || price != "" ||total_concept != "" || type_currency != undefined || date_one != "" || date_two != "") 
					{

						swal('', 'Tiene un concepto sin agregar', 'error');
						return false;
					}
					else if ($('#body_art tr').length == 0) 
					{
						swal('', 'Debe agregar al menos un concepto', 'error');
						return false;
					}
				}
			}
		});
		$(document).ready(function()
		{
			$('.quantity,.price,.total_concept',).numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			$('[name="date_obra"],[name="date_promise"],.date_one,.date_two,[name="date_remark[]"],[name="date_close"],[name="date_request"],[name="schedule_t[]"],[name="complete_status_t[]"]').datepicker({  dateFormat: "yy-mm-dd" });
			$('[name="urgent"],[name="project_id"],[name="account"],[name="code_wbs"],[name="code_edt"],[name="type_currency"],[name="status"]').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Seleccione uno",
				width 					: "100%"
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="request_purchase"]').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Seleccione uno",
				width 					: "100%",
				tags 					: true,
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.unit').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Medida",
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-name').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Nombre",
				tags: true
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.category').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Categoría"
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-measurement-unit').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder 			: "Medida",
				tags: true
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.type_currency').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder				: "Moneda",
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('[name="buyer"],[name="expeditor"]').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder				: "Seleccione uno o escriba aquí...",
				tags 					: true
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});	
			$('.milestone_t').select2(
			{
				language				: "es",
				maximumSelectionLength	: 1,
				placeholder				: "Seleccione uno o escriba aquí.",
				tags 					: true,
				width 					: '80%',
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			function calc_total()
			{
				total = 0;
				$("#body_art tr").each(function(i, v)
				{
					total += Number($(this).find('.t_total_concept').val());
				});
				total = total;
				$('[name="total_request"]').val(total);
			}

			function removeValidation() 
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			}

			$(document).on('click','#addArt',function()
			{
				part			= $(this).parents('tr').find('.part').val();
				code			= $(this).parents('tr').find('.code').val();
				unit			= $(this).parents('tr').find('.unit option:selected').val();
				description		= $(this).parents('tr').find('.description').val();
				quantity		= $(this).parents('tr').find('.quantity').val();
				price			= $(this).parents('tr').find('.price').val();
				total_concept	= $(this).parents('tr').find('.total_concept').val();
				type_currency	= $(this).parents('tr').find('.type_currency option:selected').val();
				date_one		= $(this).parents('tr').find('.date_one').val();
				date_two		= $(this).parents('tr').find('.date_two').val();

				$('.part,.code,.unit,.description,.quantity,.price,.total_concept,.type_currency,.date_one,.date_two').removeClass('error');

				if (part == "" ||code == "" ||unit == undefined ||description == "" ||quantity == "" ||price == "" ||total_concept == "" ||type_currency == undefined ||date_one == "" ||date_two == "") 
				{
					if (part == "")
						$('.part').addClass('error');
					if (code == "")
						$('.code').addClass('error');
					if (unit == undefined)
						$('.unit').addClass('error');
					if (description == "")
						$('.description').addClass('error');
					if (quantity == "")
						$('.quantity').addClass('error');
					if (price == "")
						$('.price').addClass('error');
					if (total_concept == "")
						$('.total_concept').addClass('error');
					if (type_currency == undefined)
						$('.type_currency').addClass('error');
					if (date_one == "")
						$('.date_one').addClass('error');
					if (date_two == "")
						$('.date_two').addClass('error');

					swal('','Faltan campos por agregar','error');
				}
				else
				{
					if (unit == "" || unit == undefined || unit == "undefined") 
					{
						unit = "";
					}
					tr 	= $('<tr></tr>')
							.append($('<td></td>')
								.append(part)
								.append($('<input type="hidden" name="part[]" class="input-text t_part" placeholder="0" value="'+part+'">'))
								.append($('<input type="hidden" class="idDetail" value="x">')))
							.append($('<td></td>')
								.append(code)
								.append($('<input type="hidden" name="code[]" class="input-text t_code" placeholder="0" value="'+code+'">')))
							.append($('<td></td>')
								.append(unit)
								.append($('<input type="hidden" name="unit[]" class="input-text t_unit" placeholder="0" value="'+unit+'">')))
							.append($('<td></td>')
								.append(description)
								.append($('<input type="hidden" name="description[]" class="input-text t_description" placeholder="0" value="'+description+'">')))
							.append($('<td></td>')
								.append(quantity)
								.append($('<input type="hidden" name="quantity[]" class="input-text t_quantity" placeholder="0" value="'+quantity+'">')))
							.append($('<td></td>')
								.append(price)
								.append($('<input type="hidden" name="price[]" class="input-text t_price" placeholder="0" value="'+price+'">')))
							.append($('<td></td>')
								.append(total_concept)
								.append($('<input type="hidden" name="total_concept[]" class="input-text t_total_concept" placeholder="0" value="'+total_concept+'">')))
							.append($('<td></td>')
								.append(type_currency)
								.append($('<input type="hidden" name="type_currency_concept[]" class="input-text t_type_currency" placeholder="0" value="'+type_currency+'">')))
							.append($('<td></td>')
								.append(date_one)
								.append($('<input type="hidden" name="date_one[]" class="input-text t_date_one" placeholder="0" value="'+date_one+'">')))
							.append($('<td></td>')
								.append(date_two)
								.append($('<input type="hidden" name="date_two[]" class="input-text t_date_two" placeholder="0" value="'+date_two+'">')))
							.append($('<td></td>')
								.append($('<button class="btn btn-red delete-art" type="button"><span class="icon-x"></span></button>')));

					$('#body_art').append(tr);
					$('.part,.code,.description,.quantity,.price,.total_concept,.date_one,.date_two').val('');
					$('.unit,.type_currency').val(0).trigger('change');
					calc_total();
					swal('','Artículo agregado','success');
				}
			})
			.on('click','[name="save"]',function()
			{
				if ($('[name="status"] option:selected').val() != 26) 
				{
					removeValidation();
				}
			})
			.on('click','.delete-art',function()
			{
				id = $(this).parents('tr').find('.idDetail').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#delete').append(deleteID);
				}
				$(this).parents('tr').remove();
				calc_total();
				swal('','Concepto eliminado','success');
			})
			.on('change','[name="project_id"]',function()
			{
				idproject = $('[name="project_id"] option:selected').val();

				if (idproject == 75) 
				{
					$('.select_father').show();
					$('[name="code_wbs"]').select2(
					{
						language				: "es",
						maximumSelectionLength	: 1,
						placeholder 			: "Seleccione uno",
						width 					: "100%"
					})
					.on("change",function(e)
					{
						if($(this).val().length>1)
						{
							$(this).val($(this).val().slice(0,1)).trigger('change');
						}
					});
				}
				else
				{
					$('.select_father').hide();
					$('[name="code_wbs"]').val(0).trigger('change');
				}

			})
			.on('change','.quantity,.price',function()
			{
				quantity	= $('.quantity').val();
				price		= $('.price').val();
				total		= quantity*price;
				$('.total_concept').val(total);
			})
			.on('click','.add-remark',function()
			{
				remark = $('<div class="form-group col-md-12 mb-4 remark"></div')
						.append($('<label><b>Fecha:</b></label>'))
						.append($('<input type="text" name="date_remark[]" class="input-text date_remark datepicker2" placeholder="Seleccione una fecha" readonly="readonly"><br>'))
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
			})
			.on('click','.add-milestone',function()
			{
				seq_num = $('[name="seq_num_t[]"]').length + 1;
				tr = $('<tr></tr>')
						.append($('<td></td>')
							.append($('<input type="hidden" name="seq_num_t[]" value="'+seq_num+'">'))
							.append(seq_num))
						.append($('<td></td>')
							.append($('<select name="milestone_t[]" class="form-control milestone_t" multiple="multiple"></select>')
									@foreach(App\CatMilestones::all() as $m)
										.append($('<option value="{{ $m->name }}">{{ $m->name }}</option>'))
									@endforeach
									))
						.append($('<td></td>')
							.append($('<input type="text" name="schedule_t[]" class="new-input-text" readonly>')))
						.append($('<td></td>')
							.append($('<input type="text" name="status_milestone_t[]" class="new-input-text">')))
						.append($('<td></td>')
							.append($('<input type="text" name="complete_status_t[]" class="new-input-text" readonly>')));
				$('#body_milestone').append(tr);
				$('.milestone_t').select2(
				{
					language				: "es",
					maximumSelectionLength	: 1,
					placeholder				: "Seleccione uno o escriba aquí.",
					tags 					: true,
					width 					: '80%',
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				});
				$('[name="schedule_t[]"],[name="complete_status_t[]"]').datepicker({  dateFormat: "yy-mm-dd" });
			})
			.on('change','[name="account"]',function()
			{
				id = $('[name="account"] option:selected').val();

				if (id == 5) 
				{
					$('.required-account').hide();
				}
				else
				{
					$('.required-account').show();
				}
			})
		});
	</script>
@endsection