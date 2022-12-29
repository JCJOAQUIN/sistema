@extends('layouts.child_module')
@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
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
	{!! Form::open(['route' => ['payroll.follow.update', $request->folio], 'method' => 'put', 'id' => 'container-alta']) !!}
		<center>
			@component('components.labels.title-divisor')    Folio: {{ $request->folio }} @endcomponent<br>
			<p>
				<b>Título:</b><input type="text" name="title" class="input-text removeselect" placeholder="Ej. Compra de equipo..." data-validation="required" @if(isset($request)) value="{{ $request->nominas->first()->title }}" @endif @if($request->status!=2) disabled="disabled" @endif>
			</p>
			<p>
				<b>Fecha:</b><input type="text" class="input-text removeselect datepicker2" name="datetitle" @if(isset($request)) value="{{ $request->nominas->first()->datetitle }}" @endif data-validation="required" placeholder="Seleccione una fecha" readonly="readonly" @if($request->status!=2) disabled="disabled" @endif>
			</p>
		</center>
		<div class="div-form-group" style="max-width: 400px;">
			<p>
				<b>Elaborado por:</b>
				@foreach(App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{{ $elaborate->name }} {{ $elaborate->last_name }} {{ $elaborate->scnd_last_name }}
				@endforeach
			</p>
			<p>
				<select class="js-users removeselect" name="user_id" multiple="multiple" style="width: 98%;" data-validation="required" @if($request->status == 2) > @else disabled="disbled"> @endif
					@foreach(App\User::orderName()->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->where('sys_user',1)->get() as $user) 
						<option value="@if($request->idRequest == $user->id)
						 {{ $user->id }}" selected="selected">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option> 
						 @else
						 {{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option> 
						 @endif
						@endforeach 
				</select><br>
			</p>
		</div><br><br>
		<br><br>
		<center>
			@component('components.labels.title-divisor')    Datos de empleado @endcomponent<br>
			
		</center>

		@if($request->status == 2)
			<div class="div-form-group" style="max-width: 400px;">
				<p>
					<select class="js-employees removeselect" name="employeeid" multiple="multiple" style="width: 98%;">
						@foreach(App\User::orderName()->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])->get() as $user) 
							<option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</option>	
						@endforeach 
					</select><br>
				</p>
				<p>
					<select class="js-enterprises removeselect" name="enterprise_id" multiple="multiple"  style="width: 98%; border: 0px;">
						@foreach($enterprises as $enterprise)
							<option value="{{ $enterprise->id }}">{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
						@endforeach
					</select><br>
				</p> 
				<p>
					<select class="js-areas input-text removeselect" multiple="multiple" name="area_id" style="width: 98%;">
						@foreach($areas as $area)
							<option value="{{ $area->id }}">{{ $area->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-departments input-text removeselect" multiple="multiple" name="department_id" style="width: 98%;">
						@foreach($departments as $department)
							<option value="{{ $department->id }}">{{ $department->name }}</option>
						@endforeach
					</select><br>
				</p>
				<p>
					<select class="js-accounts removeselect" class="input-text" multiple="multiple" name="accountid" style="width: 98%;">
						
					</select><br>
				</p>
				<p>
					<select class="js-projects removeselect" name="projectid" multiple="multiple" style="width: 98%; border: 0px;"> 
						@foreach(App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
							<option value="{{ $project->idproyect }}">{{ $project->proyectName }}</option>
						@endforeach 
					</select> <br>
				</p>
			</div>
		@endif
		@foreach($request->nominas as $nomina)
		<input type="hidden" name="idNominaApplication" value="{{ $nomina->idNominaApplication }}">
		@endforeach
		
			
		<br>
		<div class="table-responsive result">
			
		</div>
		<input type="hidden" name="employee_number" class="input-text employee_number" id="efolio" placeholder="Número de empleado">
		<input type="hidden" class="input-text name">
		<input type="hidden" class="input-text last_name">
		<input type="hidden" class="input-text scnd_last_name">
		<br><br>
		@if($request->status == 2)
			<div class="methodForm" style="display: none;">
				<br><br>
				@component('components.labels.title-divisor')    FORMA DE PAGO @endcomponent<br>
				<center>
					<div class="div-form-group" style="display: flex; overflow-x: auto;">
						<input type="radio" name="method" id="accountBank" value="1">
						<label for="accountBank">Cuenta Bancaria</label> 
						<input type="radio" name="method" id="cash" value="2">
						<label for="cash">Efectivo</label>
						<input type="radio" name="method" id="checks" value="3">
						<label for="checks">Cheque</label>
						<br>
					</div>
				</center>
			</div>
			<br><br>
		@endif
		<div class="resultbank table-responsive" style="display: none;">
			
		</div>
		<div class="datos" style="display: none;">
			@component('components.labels.title-divisor')    INGRESE LOS SIGUIENTES DATOS @endcomponent
			<div class='form-container'>
				<p>
					<label class='label-form'>Referencia</label><input type='text' placeholder='Ingrese la referencia' class='input-text reference-new'>
				</p>
				<p>
					<label class='label-form'>Importe</label><input type='text' placeholder='$0.00' class='input-text amount'>
				</p>
				<p>
					<label class='label-form'>Razón de pago</label><input type='text' placeholder='Ingrese la razón de pago' class='input-text reason_payment'>
				</p>
			</div>
			<div class='form-container'><p><button type='button' name='add' id='add'><div class='btn_plus'>+</div> Agregar concepto</button><button type='button' name='canc' id='exit' class='btn'>Cancelar</button></p></div>
		</div>
		<br><br>
		<div class="form-container">
				<div class="table-responsive">
					<table id="table2" class="table-no-bordered">
						<thead>
							<th># Empleado</th>
							<th>Nombre del Empleado</th>
							<th>Empresa</th>
							<th>Proyecto</th>
							<th hidden>Departamento</th>
							<th hidden>Dirección</th>
							<th hidden>Clasificación de gasto</th>
							@if($request->status != 2)
								<th>Forma de pago</th>
							@endif
							<th style="display: none;">Banco</th>
							<th style="display: none;"># Tarjeta</th>
							<th style="display: none;">Cuenta</th>
							<th style="display: none;">CLABE</th>
							<th>Referencia</th>
							<th>Importe</th>
							<th>Razon</th>
							@if($request->status == 2)
							<th width="8%"></th>
							@endif
							@if($request->status != 2)
							<th>Acción</th>
							@endif
						</thead>
						<tbody id="body-payroll" class="request-validate">
							@foreach($request->nominas as $nomina)
								@foreach(App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$nomina->idNominaApplication)->get() as $noEmp)
								<tr>
									<td hidden>
										<input type="text" name="t_idpaymentmethod[]" value="{{ $noEmp->idpaymentMethod }}">
									</td>
									<td>
										{{ $noEmp->idUsers }}
										<input readonly class="input-table iduser" type="hidden" name="t_employee_number[]" value="{{ $noEmp->idUsers }}">
									</td>
									<td>
										{{ $noEmp->name }} {{ $noEmp->last_name }} {{ $noEmp->scnd_last_name }}
										<input readonly class="input-table name" type="hidden" value="{{ $noEmp->name }}">
										<input readonly class="input-table last_name" type="hidden" value="{{ $noEmp->last_name }}">
										<input readonly class="input-table scnd_last_name" type="hidden" value="{{ $noEmp->scnd_last_name }}">
									</td>
									<td>
										{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_enterprise[]" value="{{ $noEmp->idEnterprise }}">
									</td>
									<td>
										{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_project[]" value="{{ $noEmp->idProject }}">
									</td>
									<td hidden>
										{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_department[]" value="{{ $noEmp->idDepartment }}">

										<input readonly class="input-table enterprise" type="hidden" value="{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}">
										<input readonly class="input-table project" type="hidden" value="{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}">
										<input readonly class="input-table department" type="hidden" value="{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}">
										<input readonly class="input-table area" type="hidden" value="{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}">
										<input readonly class="input-table accounttext" type="hidden" value="{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}">
									</td>
									<td hidden>
										{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_direction[]" value="{{ $noEmp->idArea }}">
									</td>
									<td hidden>
										{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}
										<input readonly class="input-table" type="hidden" name="t_accountid[]" value="{{ $noEmp->idAccount }}">
									</td>
									@if($request->status != 2)
										<td>
											@switch($noEmp->idpaymentMethod)
												@case(1)
													Cuenta Bancaria
													@break
												@case(2)
													Efectivo
													@break
												@case(3)
													Cheque
													@break
											@endswitch
										</td>
									@endif
									<td hidden>
										{{ $noEmp->bank }}
										<input readonly value="{{ $noEmp->bank }}" class="input-table bank" type="hidden" name="t_bank[]">
									</td>

									<td hidden>
										{{ $noEmp->cardNumber }}<input value="{{ $noEmp->cardNumber }}" readonly class="input-table cardNumber" type="hidden" name="t_card_number[]">
									</td>

									<td hidden>
										{{ $noEmp->account }}
										<input value="{{ $noEmp->account }}" readonly class="input-table account" type="hidden" name="t_account[]">
									</td>

									<td hidden>
										{{ $noEmp->clabe }}
										<input value="{{ $noEmp->clabe }}" readonly value="" class="input-table clabe" type="hidden" name="t_clabe[]">
									</td>

									<td>{{ $noEmp->reference }}
										<input value="{{ $noEmp->reference }}" readonly class="input-table reference" type="hidden" name="t_reference[]">
									</td>

									<td>{{ $noEmp->amount }}
										<input value="{{ $noEmp->amount }}" readonly class="input-table importe" type="hidden" name="t_amount[]">
									</td>

									<td>
										{{ $noEmp->description }}
										<input readonly class="input-table description" type="hidden" name="t_reason_payment[]" value="{{ $noEmp->description }}">
									</td>

									@if($request->status != 2)
										<td>
											<button class="btn btn-green" type="button" id="ver">Ver datos</button>
										</td>
									@endif
									@if($request->status == 2)
										<td>
											<button class="delete-item"><span class="icon-x delete-span"></span></button>
										</td>
									@endif
								</tr>
								@endforeach
							@endforeach
						</tbody>
						<tfoot>
							<th></th>
						</tfoot>
					</table>
				</div>
				<br>
				
			</div>
			@if($request->status != 2)
				<div class="formulario" style="display: none; border: 1px solid #bbb6b6; padding: 10px; width: 600px; margin: 0px auto; border-radius: 10px;">
					<table class="employee-details">
						<tbody>
							<tr>
								<td><b>Nombre:</b></td>
								<td><label id="nameEmp"></label></td>
							</tr>
							<tr>
								<td><b>Empresa:</b></td>
								<td><label id="enterprise"></label></td>
							</tr>
							<tr>
								<td><b>Departamento:</b></td>
								<td><label id="department"></label></td>
							</tr>
							<tr>
								<td><b>Dirección:</b></td>
								<td><label id="area"></label></td>
							</tr>
							<tr>
								<td><b>Proyecto:</b></td>
								<td><label id="project"></label></td>
							</tr>
							<tr>
								<td><b>Clasificación del gasto:</b></td>
								<td><label id="accounttext"></label></td>
							</tr>
							<tr>
								<td><b>Banco:</b></td>
								<td><label id="idBanksEmp"></label></td>
							</tr>
							<tr>
								<td><b>Número de Tarjeta:</b></td>
								<td><label id="card_numberEmp"></label></td>
							</tr>
							<tr>
								<td><b>Cuenta Bancaria:</b></td>
								<td><label id="accountEmp"></label></td>
							</tr>
							<tr>
								<td><b>CLABE:</b></td>
								<td><label id="clabeEmp"></label></td>
							</tr>
							<tr>
								<td><b>Referencia:</b></td>
								<td><label id="referenceEmp"></label></td>
							</tr>
							<tr>
								<td><b>Importe:</b></td>
								<td><label id="amountEmp"></label></td>
							</tr>
							<tr>
								<td><b>Razón de pago:</b></td>
								<td><label id="reason_paymentEmp"></label></td>
							</tr>
						</tbody>
					</table>
					<div class="form-container">
						<p>
							<button type="button" name="canc" id="exit" class="btn btn-green">« Ocultar</button>
						</p>
					</div>
				</div>
			@endif
			<div class="form-container">
				<div class="table-responsive">
					<table class="table-no-bordered">
						<thead>
							<th width="20%"></th>
							<th width="20%"></th>
							<th width="20%"></th>
							<th width="20%">TOTAL:</th>
							<th width="20%">@foreach($request->nominas as $nomina)<input value="{{ $nomina->amount }}" id="input-extrasmall" placeholder="0" readonly class="input-table total" type="text" name="total">@endforeach</th>
						</thead>
					</table>
				</div>
				<br>
			</div>
		</div>
		@if($request->idCheck != "")
			<br>
			@component('components.labels.title-divisor')    DATOS DE REVISIÓN @endcomponent
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
					@if($request->idEnterpriseR!="")
						<tr>
							<td><b>Nombre de la Empresa:</b></td>
							<td><label>{{ App\Enterprise::find($request->idEnterpriseR)->name }}</label></td>
						</tr>
						<tr>
							<td>
								<b>Nombre de la Dirección:</b>
							</td>
							<td>
								<label>{{ $request->reviewedDirection->name }}</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Nombre del Departamento:</b>
							</td>
							<td>
								<label>{{ App\Department::find($request->idDepartamentR)->name }}</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Clasificación del gasto:</b>
							</td>
							@php
								$reviewAccount = App\Account::find($request->accountR);
							@endphp
							<td>
								<label>@if(isset($reviewAccount->account)) {{ $reviewAccount->account }} - {{ $reviewAccount->description }} @else No hay @endif</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Nombre del Proyecto:</b>
							</td>
							<td>
								<label>{{ $request->reviewedProject->proyectName }}</label>
							</td>
						</tr>
						<tr>
							<td>
								<b>Etiquetas:</b>
							</td>
							<td>
								@foreach($request->labels as $label)
									<label>{{ $label->description }},</label>
								@endforeach
							</td>
						</tr>
					@endif
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
		@if($request->idAuthorize != "")
			<br><br>
			@component('components.labels.title-divisor')    DATOS DE AUTORIZACIÓN @endcomponent
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
		@if($request->status == 13)
			<br><br>
			@component('components.labels.title-divisor')    DATOS DE PAGOS @endcomponent
			<div>
				<table class="employee-details">
					<tbody>
						<tr>
							<td><b>Comentarios:</b></td>
							<td>
								@if($request->paymentComment == "")
									<label>Sin comentarios</label>
								@else
									<label>{{ $request->paymentComment }}</label>
								@endif
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		@endif
		<br><br>
		@php
			$payments = App\Payment::where('idFolio',$request->folio)->get();
			$total = $request->nominas->first()->amount;
			$totalPagado = 0;
		@endphp
		@if(count($payments))
		<br><br>
			@component('components.labels.title-divisor')    HISTORIAL DE PAGOS @endcomponent

			<table class="table-no-bordered">
				<thead>
					<th width="25%">Cuenta</th>
					<th width="25%">Cantidad</th>
					<th width="25%">Documento</th>
					<th width="25%">Fecha</th>
				</thead>
				<tbody>
					@foreach($payments as $pay)
					<tr>
						<td>
							{{ $pay->accounts->account.' - '.$pay->accounts->description }}
						</td>
						<td>
							{{ '$'.number_format($pay->amount,2) }}
						</td>
						<td>
							@foreach($pay->documentsPayments as $doc)
								<a href="{{ asset('docs/payments/'.$doc->path) }}" target="_blank" class="btn btn-red" title="{{ $doc->path }}">
									<span class="icon-pdf"></span>
								</a>
							@endforeach
						</td>
						<td>
							{{ $pay->paymentDate }}
						</td>
					</tr>
					@php
						$totalPagado += $pay->amount;
					@endphp
					@endforeach
					<br>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><b>Total pagado:</b> ${{ number_format($totalPagado,2) }}</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><b>Resta por pagar:</b> ${{ number_format($total-$totalPagado,2) }} </td>
					</tr>
				</tbody>
			</table>
		@endif

		<center> 
		<p> 
		@if($request->status == "2")
		  <input class="btn btn-red" type="submit" name="enviar" value="ENVIAR SOLICITUD"> 
		  <input class="btn btn-blue save" type="submit" id="save" name="save" value="GUARDAR SIN ENVIAR" formaction="{{ route('payroll.follow.updateunsent', $request->folio) }}">  
		  <a 
				@if(isset($option_id)) 
					href="{{ url(App\Module::find($option_id)->url) }}" 
				@else 
					href="{{ url(App\Module::find($child_id)->url) }}" 
				@endif 
			><button class="btn" type="button">REGRESAR</button></a>
		@else
		  <a 
				@if(isset($option_id)) 
					href="{{ url(App\Module::find($option_id)->url) }}" 
				@else 
					href="{{ url(App\Module::find($child_id)->url) }}" 
				@endif 
			><button class="btn" type="button">REGRESAR</button></a>
		@endif
		</p> 
	</center>
	<br> 
	{!! Form::close() !!}

@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$.validate(
	{
		form: '#container-alta',
		onError   : function($form)
		{
			swal('', '{{ Lang::get("messages.form_error") }}', 'error');
		},
		onSuccess : function($form)
		{
			if($('.request-validate').length>0)
			{
				conceptos	= $('#body-payroll tr').length;
				if(conceptos>0)
				{
					swal("Cargando",{
						icon: '{{ url('images/loading.svg') }}',
						button: false,
					});
					return true;
				}
				else
				{
					swal('', 'Todos los campos son requeridos', 'error');
					return false;
				}
			}
			else
			{
				swal("Cargando",{
					icon: '{{ url('images/loading.svg') }}',
					button: false,
				});
				return true;
			}
		}
	});

	$(document).ready(function()
	{
		$('.js-users').select2({
			placeholder: 'Nombre del Solicitante',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-employees').select2({
			placeholder: 'Seleccione un Empleado',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-enterprises').select2(
		{
			placeholder: 'Seleccione la Empresa',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-areas').select2(
		{
			placeholder: 'Seleccione la Dirección',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-departments').select2(
		{
			placeholder: 'Seleccione el Departamento',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-projects').select2(
		{
			placeholder: 'Seleccione el Proyecto/Contrato',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$('.js-accounts').select2(
		{
			placeholder: 'Clasificación de Gasto',
			language: "es",
			maximumSelectionLength: 1
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
		$(function() 
		{
			$(".datepicker2").datepicker({ minDate: 0, dateFormat: "yy-mm-dd" });
		});

	   	$('.card_number,.destination_account,.destination_key,.employee_number,.amount').numeric(false);    // números
	   	$('.amount,.importe',).numeric({ altDecimal: ".", decimalPlaces: 2 });
	   	var sumatotal = 0;

		$(".importe").each(function(i, v)
		{
			valor = parseFloat($(this).val());
			sumatotal = sumatotal + valor ;
		});

		$(".total").val(sumatotal);
	
		$(document).on('change', '.js-employees', function()
		{
			id = $(this).val();
			$('.datos').stop().show();
			$('.methodForm').stop().show();
			text = $('#efolio').val();
			$.ajax(
			{
				type : 'get',
				url  : '{{ url("administration/payroll/search/bank") }}',
				data : {'employee_number':id},
				success:function(data)
				{
					$('.resultbank').html(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.resultbank').html('');
				}
			});
		})
		.on('click','#exit', function(){

			$(".formulario").slideToggle();
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
					$('#body-payroll').html('');
					$('.removeselect').val(null).trigger('change');
					$('.resultbank').hide();
					$('.result').hide();
					form[0].reset();
				}
				else
				{
					swal.close();
				}
			});
		})
		.on('keyup','#input-search', function(){
		
			$text = $(this).val().trim();
			if ($text == "") 
			{
				$('#not-found').stop().show();
				$('#not-found').html("RESULTADO NO ENCONTRADO");
				$('#table').stop().hide();
				$('.resultbank').stop().hide();
				$('.result').stop().hide();
				$('.datos').stop().hide();
				$('.methodForm').stop().hide();
			}
			else
			{
				$('#not-found').stop().hide();
				$('.result').stop().show();
				$.ajax({
					type : 'get',
					url  : '{{ url("administration/payroll/search/user") }}',
					data : {'search':$text},
					success:function(data)
					{
						$('.result').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.result').html('');
					}
				}); 
			}
		})
		.on('click','#save',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('#body-payroll').append($('<tr></tr>'));
		})
		.on('click','.checkbox',function()
		{
			$('.marktr').removeClass('marktr');
			$(this).parents('tr').addClass('marktr');
		})
		.on('click','.delete-item',function()
		{
			$(this).parents('tr').remove();
			
			var sumatotal = 0;
			
			$(".importe").each(function(i, v)
			{
				valor = parseFloat($(this).val());
				sumatotal = sumatotal + valor ;
			});
			
			$(".total").val(sumatotal);
			
		})
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts').empty();
			$enterprise = $(this).val();
			$.ajax(
			{
				type 	: 'get',
				url 	: '{{ url("/administration/purchase/create/account") }}',
				data 	: {'enterpriseid':$enterprise},
				success : function(data)
				{
					$.each(data,function(i, d)
					{
						$('.js-accounts').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
					});
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('.js-accounts').val(null).trigger('change');
				}
			})
		})
		.on('click','#ver',function()
		{
			nameEmp           = $(this).parent('td').parent('tr').find('.name').val();
			lastnameEmp       = $(this).parent('td').parent('tr').find('.last_name').val();
			scnd_last_nameEmp = $(this).parent('td').parent('tr').find('.scnd_last_name').val();
			bankEmp           = $(this).parent('td').parent('tr').find('.bank').val();
			cardEmp           = $(this).parent('td').parent('tr').find('.cardNumber').val();
			accountEmp        = $(this).parent('td').parent('tr').find('.account').val();
			clabeEmp          = $(this).parent('td').parent('tr').find('.clabe').val();
			referenceEmp      = $(this).parent('td').parent('tr').find('.reference').val();
			amountEmp         = $(this).parent('td').parent('tr').find('.importe').val();
			reason_paymentEmp = $(this).parent('td').parent('tr').find('.description').val();
			accounttext       = $(this).parent('td').parent('tr').find('.accounttext').val();
			enterprise    	  = $(this).parent('td').parent('tr').find('.enterprise').val();
			project           = $(this).parent('td').parent('tr').find('.project').val();
			area              = $(this).parent('td').parent('tr').find('.area').val();
			department        = $(this).parent('td').parent('tr').find('.department').val();
			if(accountEmp == '')
			{
				accountEmp = '-----';
			}

			if(cardEmp == '')
			{
				cardEmp = '-----';
			}

			if(clabeEmp == '')
			{
				clabeEmp = '-----';
			}			

			$('#nameEmp').html(nameEmp+' '+lastnameEmp+' '+scnd_last_nameEmp);
			$('#idBanksEmp').html(bankEmp);
			$('#card_numberEmp').html(cardEmp);
			$('#accountEmp').html(accountEmp);
			$('#clabeEmp').html(clabeEmp);
			$('#referenceEmp').html(referenceEmp);
			$('#amountEmp').html(amountEmp);
			$('#reason_paymentEmp').html(reason_paymentEmp);
			$('#accounttext').html(accounttext);
			$('#enterprise').html(enterprise);
			$('#project').html(project);
			$('#area').html(area);
			$('#department').html(department);
			$(".formulario").slideDown();
		})
		.on('click','#exit', function()
		{
			$(".formulario").slideUp();
		})
		.on('click','#add',function()
		{
			employee_number = $('.js-employees option:checked').val();
			name            = $('.js-employees option:checked').text();
			
			enterprise      = $('.js-enterprises option:checked').text();
			enterpriseid    = $('.js-enterprises option:checked').val();
			
			department      = $('.js-departments option:checked').text();
			departmentid    = $('.js-departments option:checked').val();
			
			direction       = $('.js-areas option:checked').text();
			directionid     = $('.js-areas option:checked').val();
			
			accounttext     = $('.js-accounts option:checked').text();
			accountid       = $('.js-accounts option:checked').val();
			
			project         = $('.js-projects option:checked').text();
			projectid       = $('.js-projects option:checked').val();
			
			reference       = $('.reference-new').val();
			amount          = $('.amount').val();
			reason          = $('.reason_payment').val();
			check           = $('input[name="idemp"]').is(':checked');
			bank            = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.bank').val();
			clabe           = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.clabe').val();
			account         = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.account').val();
			card_number     = $('input[name="idemp"]:checked').parent('td').parent('tr').find('.card_number').val();
			idpaymentmethod = $('input[name="method"]:checked').val();


			if (idpaymentmethod == "" || idpaymentmethod == undefined) 
			{
				swal('', 'Por favor seleccione una forma de pago', 'error');
			}
			else
			{
				if (idpaymentmethod == 1) 
				{
					if (employee_number == "" || name == "" || amount == "" || reason == "" || enterpriseid=="" || directionid=="" || accountid=="" || projectid=="" ||  enterpriseid == undefined ||directionid==undefined || accountid==undefined || projectid==undefined ) 
					{
						if(amount=="" || amount == 0)
						{
							$('.amount').addClass('error');
						}
						if(reason=="")
						{
							$('.reason_payment').addClass('error');
						}
						swal('', 'Por favor llene los campos necesarios', 'error');
					}
					else if(check == false)
					{
						swal('', 'Por favor seleccione una cuenta', 'error');
					}
					else
					{
						tr_table    = $('<tr></tr>')
										.append($('<td hidden></td>')
											.append(idpaymentmethod)
											.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
										)
										.append($('<td></td>')
											.append(employee_number)
											.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
										)
										.append($('<td></td>')
											.append(name)
											.append($('<input readonly class="input-table" type="hidden"/>').val(name))
										)
										.append($('<td></td>')
											.append(enterprise)
											.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
										)
										.append($('<td></td>')
											.append(project)
											.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
										)
										.append($('<td hidden></td>')
											.append(department)
											.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
										)
										.append($('<td hidden></td>')
											.append(direction)
											.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
										)
										.append($('<td hidden></td>')
											.append(accounttext)
											.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
										)
										.append($('<td hidden></td>')
											.append(bank)
											.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val(bank))
										)
										.append($('<td hidden></td>')
											.append(card_number)
											.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val(card_number))
										)
										.append($('<td hidden></td>')
											.append(account)
											.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val(account))
										)
										.append($('<td hidden></td>')
											.append(clabe)
											.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val(clabe))
										)
										.append($('<td></td>')
											.append(reference)
											.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
										)
										.append($('<td></td>')
											.append(amount)
											.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
										)
										.append($('<td></td>')
											.append(reason)
											.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
										)
										.append($('<td></td>')
											.append($('<button class="delete-item"></button>')
												.append($('<span class="icon-x delete-span"></span>'))
											)
										);

						$('#body-payroll').append(tr_table);

						var sumatotal = 0;
						$(".importe").each(function(i, v)
						{
							valor = parseFloat($(this).val());
							sumatotal = sumatotal + valor ;
						});

						$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
						$(".total").val(sumatotal);
						$('#input-search').val('');
						$(".formulario").slideToggle();
						$('#table').slideToggle();
						$('.datos').stop().hide();
						$('.methodForm').stop().hide();
						$('.result').stop().hide();
						$('.reference-new').val('');
						$('.amount').val('');
						$('.reason_payment').val('');
						$('.resultbank').stop().hide();
					}   
				}
				else
				{
					if (employee_number == "" || name == "" || amount == "" || reason == "" ) 
					{
						if(amount=="" || amount == 0)
						{
							$('.amount').addClass('error');
						}
						if(reason=="")
						{
							$('.reason_payment').addClass('error');
						}
						swal('', 'Por favor llene los campos necesarios', 'error');
					}
					else
					{
						tr_table    = $('<tr></tr>')
										.append($('<td hidden></td>')
											.append(idpaymentmethod)
											.append($('<input readonly class="input-table" type="hidden" name="t_idpaymentmethod[]">').val(idpaymentmethod))
										)
										.append($('<td></td>')
											.append(employee_number)
											.append($('<input readonly class="input-table" type="hidden" name="t_employee_number[]">').val(employee_number))
										)
										.append($('<td></td>')
											.append(name)
											.append($('<input readonly class="input-table" type="hidden"/>').val(name))
										)
										.append($('<td></td>')
											.append(enterprise)
											.append($('<input readonly class="input-table" type="hidden" name="t_enterprise[]">').val(enterpriseid))
										)
										.append($('<td></td>')
											.append(project)
											.append($('<input readonly class="input-table" type="hidden" name="t_project[]">').val(projectid))
										)
										.append($('<td hidden></td>')
											.append(department)
											.append($('<input readonly class="input-table" type="hidden" name="t_department[]">').val(departmentid))
										)
										.append($('<td hidden></td>')
											.append(direction)
											.append($('<input readonly class="input-table" type="hidden" name="t_direction[]">').val(directionid))
										)
										.append($('<td hidden></td>')
											.append(accounttext)
											.append($('<input readonly class="input-table" type="hidden" name="t_accountid[]">').val(accountid))
										)
										.append($('<td hidden></td>')
											.append('x')
											.append($('<input readonly class="input-table" type="hidden" name="t_bank[]">').val('x'))
										)
										.append($('<td hidden></td>')
											.append('x')
											.append($('<input readonly class="input-table" type="hidden" name="t_card_number[]">').val('x'))
										)
										.append($('<td hidden></td>')
											.append('x')
											.append($('<input readonly class="input-table" type="hidden" name="t_account[]">').val('x'))
										)
										.append($('<td hidden></td>')
											.append('x')
											.append($('<input readonly class="input-table" type="hidden" name="t_clabe[]">').val('x'))
										)
										.append($('<td></td>')
											.append(reference)
											.append($('<input readonly class="input-table" type="hidden" name="t_reference[]">').val(reference))
										)
										.append($('<td></td>')
											.append(amount)
											.append($('<input readonly class="input-table importe" type="hidden" name="t_amount[]">').val(amount))
										)
										.append($('<td></td>')
											.append(reason)
											.append($('<input readonly class="input-table" type="hidden" name="t_reason_payment[]">').val(reason))
										)
										.append($('<td></td>')
											.append($('<button class="delete-item"></button>')
												.append($('<span class="icon-x delete-span"></span>'))
											)
										);

						$('#body-payroll').append(tr_table);

						var sumatotal = 0;
						$(".importe").each(function(i, v)
						{
							valor = parseFloat($(this).val());
							sumatotal = sumatotal + valor ;
						});

						$('.js-accounts,.js-departments,.js-enterprises,.js-employees,.js-projects,.js-areas').val(null).trigger('change');
						$(".total").val(sumatotal);
						$('#input-search').val('');
						$(".formulario").slideToggle();
						$('#table').slideToggle();
						$('.datos').stop().hide();
						$('.methodForm').stop().hide();
						$('.result').stop().hide();
						$('.reference-new').val('');
						$('.amount').val('');
						$('.reason_payment').val('');
						$('.resultbank').stop().hide();
					}
				}
			}
			
		})
		.on('click','input[name="method"]',function()
		{
			if($(this).val() == 1)
			{
				$('.resultbank').stop(true,true).slideDown().show();
			}
			else
			{
				$('.resultbank').stop(true,true).slideUp().hide();
			}
		});
	});
</script>
@endsection
