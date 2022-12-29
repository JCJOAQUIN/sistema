@extends('layouts.child_module')
@section('data')
	<br>
	<center>A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:</center>
	<br>
	<div class="profile-table-center">
		<div class="profile-table-center-header">
			Detalles de la Solicitud
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Folio:
			</div>
			<div class="right">
				<p>{{$request->folio }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Título y fecha:
			</div>
			<div class="right">
				<p>{{$request->nominas->first()->title }} - {{ $request->nominas->first()->datetitle }}</p>
			</div>
		</div>
		<div class="profile-table-center-row">
			<div class="left">
				Solicitante:
			</div>
			<div class="right">
				@php
					$requestUser = App\User::find($request->idRequest);
				@endphp
				<p>{{ $requestUser->name }} {{ $requestUser->last_name }} {{ $requestUser->scnd_last_name }}</p>
			</div>
		</div>
		<div class="profile-table-center-row no-border">
			<div class="left">
				Elaborado por:
			</div>
			<div class="right">
				@php
					$elaborateUser = App\User::find($request->idElaborate);
				@endphp
				<p>{{ $elaborateUser->name }} {{ $elaborateUser->last_name }} {{ $elaborateUser->scnd_last_name }}</p>
			</div>
		</div>
	</div>
	@component('components.labels.title-divisor')    DATOS DEL EMPLEADO @endcomponent
	<div class="form-container">
		<div class="table-responsive">
			<table id="table2" class="table-no-bordered">
				<thead>
					<th width="23%"># Empleado</th>
					<th width="23%">Nombre del Empleado</th>
					<th>Empresa</th>
					<th>Proyecto</th>
					<th hidden>Departamento</th>
					<th hidden>Dirección</th>
					<th hidden>Clasificación de gasto</th>
					<th>Forma de pago</th>
					<th style="display: none;" width="23%">Banco</th>
					<th style="display: none;"># Tarjeta</th>
					<th style="display: none;">Cuenta</th>
					<th style="display: none;">CLABE</th>
					<th>Referencia</th>
					<th width="23%">Importe</th>
					<th>Razon</th>
					
					<th width="8%">Acción</th>
					
				</thead>
				<tbody id="body-payroll" class="request-validate">
					@foreach(App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$request->nominas->first()->idNominaApplication)->get() as $noEmp)
						<tr>
							<td>{{ $noEmp->idUsers }}<input readonly class="input-table iduser" type="hidden" name="t_employee_number[]" value="{{ $noEmp->idUsers }}"></td>

							<td>{{ $noEmp->name }} {{ $noEmp->last_name }} {{ $noEmp->scnd_last_name }}<input readonly class="input-table name" type="hidden" value="{{ $noEmp->name }}">
							<input readonly class="input-table last_name" type="hidden" value="{{ $noEmp->last_name }}">
							<input readonly class="input-table scnd_last_name" type="hidden" value="{{ $noEmp->scnd_last_name }}"></td>
							<td>
								{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}
								<input readonly class="input-table enterprise" type="hidden" name="t_enterprise[]" value="{{ $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay' }}">
							</td>
							<td>
								{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}
								<input readonly class="input-table project" type="hidden" name="t_project[]" value="{{  $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay' }}">
							</td>
							<td hidden>
								{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}
								<input readonly class="input-table department" type="hidden" name="t_department[]" value="{{ $noEmp->department()->exists() ? $noEmp->department->name : 'No hay' }}">
							</td>
							<td hidden>
								{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}
								<input readonly class="input-table area" type="hidden" name="t_direction[]" value="{{ $noEmp->area()->exists() ? $noEmp->area->name : 'No hay' }}">
							</td>
							<td hidden>
								{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}
								<input readonly class="input-table accounttext" type="hidden" name="t_accountid[]" value="{{ $noEmp->accounts()->exists() ? $noEmp->accounts->account.' - '.$noEmp->accounts->description : 'No hay' }}">
							</td>
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
							<td hidden>{{ $noEmp->bank }}<input readonly value="{{ $noEmp->bank }}" class="input-table bank" type="hidden" name="t_bank[]"></td>

							<td hidden>{{ $noEmp->cardNumber }}<input value="{{ $noEmp->cardNumber }}" readonly class="input-table cardNumber" type="hidden" name="t_card_number[]"></td>

							<td hidden>{{ $noEmp->account }}<input value="{{ $noEmp->account }}" readonly class="input-table account" type="hidden" name="t_account[]"></td>

							<td hidden>{{ $noEmp->clabe }}<input value="{{ $noEmp->clabe }}" readonly value="" class="input-table clabe" type="hidden" name="t_clabe[]"></td>

							<td>{{ $noEmp->reference }}<input value="{{ $noEmp->reference }}" readonly class="input-table reference" type="hidden" name="t_reference[]"></td>

							<td>{{ $noEmp->amount }}<input value="{{ $noEmp->amount }}" readonly class="input-table importe" type="hidden" name="t_amount[]"></td>

							<td>{{ $noEmp->description }}<input readonly class="input-table description" type="hidden" name="t_reason_payment[]" value="{{ $noEmp->description }}"></td>

							@if($request->status != 2)
							<td>
								<button class="btn btn-green" type="button" id="ver">Ver datos</button>
							</td>
							@endif
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<br>
	</div>
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
	<div class="form-container">
		<div class="total-diplayed">
			<b>TOTAL: {{ $request->nominas->first()->amount }}</b>
		</div>
	</div>
	<br><br>
	@component('components.labels.title-divisor')    DATOS DE REVISIÓN @endcomponent
	<div>
		<table class="employee-details">
			<tbody>
				<tr>
					<td><b>Revisó:</b></td>
					<td><label>{{ $request->reviewedUser->name }} {{ $request->reviewedUser->last_name }} {{ $request->reviewedUser->scnd_last_name }}</label></td>
				</tr>
				<tr>
					<td><b>Comentarios:</b></td>
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
	<br><br><br>
	{!! Form::open(['route' => ['payroll.authorization.update', $request->folio], 'method' => 'put', 'id' => 'container-alta']) !!}
		<div class="form-container">
			<center>
				<p>
					<label class="label-form" id="label-inline" >¿Desea autorizar ó rechazar la solicitud?</label><br><br>	
					<input type="radio" name="status" id="aprobar" value="5">
					<label for="aprobar" class="approve"><span class="icon-checkmark"></span> Aprobar</label>
					<input type="radio" name="status" id="rechazar" value="7">
					<label for="rechazar" class="refuse"><span class="icon-cross"></span> Rechazar</label>
				</p>
			</center>
		</div>
		<div id="aceptar">
			<div class="form-container">
				<label class="label-form">Comentarios (opcional)</label>
				<textarea class="text-area" cols="90" rows="10" name="authorizeCommentA"></textarea>
			</div>
		</div>
		<center>
			<p>
				<input class="btn btn-red" type="submit" name="enviar" value="ENVIAR SOLICITUD">
				<a
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif 
				>
					<button class="btn" type="button">REGRESAR</button>
				</a>
			</p>
		</center>
		<br>
    {!! Form::close() !!}
@endsection

@section('scripts')
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>

<script>
	$.validate(
	{
		form: '#container-alta',
		onSuccess : function($form)
		{
			if($('input[name="status"]').is(':checked'))
			{
				swal("Cargando",{
					icon: '{{ url('images/loading.svg') }}',
					button: false,
				});
				return true;
			}
			else
			{
				swal('', 'Debe seleccionar al menos un estado', 'error');
				return false;
			}
		}
	});
	$(document).ready(function(){
		$(document).on('click', '.edit2', function()
		{
			id				= $(this).val();
			folio			= $('#id_'+id).val();
			name			= $('#name_'+id).val();
			lastname		= $('#last_name_'+id).val();
			scndlastname	= $('#scnd_last_name_'+id).val();
			bank			= $('#bank_'+id).val();
			kind			= $('#kindbank_'+id).val();
			card			= $('#card_'+id).val();
			destinyId		= $('#destinyId_'+id).val();
			destinyAcount	= $('#destinyAcount_'+id).val();
			reference		= $('#reference_'+id).val();
			amount			= $('#amount_'+id).val();
			reason			= $('#description_'+id).val();

		
			$(".formulario2").slideToggle();
			$(".formulario2").css('display','flex');
			$('.employee_number2').val(folio);
			$('.name2').val(name);
			$('.last_name2').val(lastname);
			$('.scnd_last_name2').val(scndlastname);
			$('.idBanks2').val(bank);
			$('.idKindOfBank2').val(kind);
			$('.card_number2').val(card);
			$('.destination_key2').val(destinyId);
			$('.destination_account2').val(destinyAcount);
			$('.reference2').val(reference);
			$('.amount2').val(amount);
			$('.reason_payment2').val(reason);

		})
		.on('click','#exit2', function()
		{
			$(".formulario2").slideToggle();
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
			$(".formulario").stop().slideToggle();
		})
		.on('click','#exit', function()
		{
			$(".formulario").slideToggle();
		})
	});
	$('input[name="status"]').change(function()
	{
		$("#aceptar").slideDown("slow");
	});
</script>

@endsection
