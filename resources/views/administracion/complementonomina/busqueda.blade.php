@extends('layouts.child_module')
  
@section('data')

		<div id="container-cambio" class="div-search">
			{!! Form::open(['route' => 'payroll.search', 'method' => 'GET', 'id'=>'formsearch']) !!}
			@component('components.labels.title-divisor')    BUSCAR SOLICITUDES @endcomponent
			<center>
				<div class="search-table-center">
					<div class="search-table-center-row">
						<div class="left">
							<br><label class="label-form">Folio:</label>
						</div>
						<div class="right">
							<p><input type="text" name="folio" class="input-text-search" id="input-search" placeholder="Escribe aquí..." value="{{ isset($folio) ? $folio : '' }}"></p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Nombre:</label>
						</div>
						<div class="right">
							<p><input type="text" name="name" class="input-text-search" id="input-search" placeholder="Escribe aquí..." value="{{ isset($name) ? $name : '' }}"></p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Rango de fechas:</label>
						</div>
						<div class="right-date">
							<p><input type="text" name="mindate" step="1" class="input-text-date datepicker" placeholder="Desde" value="{{ isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '' }}" readonly> - <input type="text" name="maxdate" step="1" class="input-text-date datepicker" placeholder="Hasta" value="{{ isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '' }}" readonly></p>
						</div>
					</div>
					<div class="search-table-center-row">
						<p>
							<select title="Estado de Solicitud" name="status" class="js-status" multiple="multiple" style="width: 98%; max-width: 150px;">
								@foreach (App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->get() as $s)
									@if (isset($status) && $status == $s->idrequestStatus)
										<option value="{{ $s->idrequestStatus }}" selected>{{ $s->description }}</option>
									@else
										<option value="{{ $s->idrequestStatus }}">{{ $s->description }}</option>
									@endif
								@endforeach
							</select>
						</p>
					</div>
				</div>
			</center>
			<center>
				<button class="btn 	btn-search" type="submit"><span class="icon-search"></span> Buscar</button>
			</center>
			<br><br>
		</div>
	<br>
@if(count($requests) > 0)
<div style='float: right'><label class='label-form'>Exportar a Excel<label><button class='btn btn-green export' type='submit'  formaction="{{ route('payroll.export.follow') }}"><span class='icon-file-excel'></span></button></div>
{!! Form::close() !!}
<div class="table-responsive table-striped">
	<table class="table">
	<thead class="thead-dark">
		<th width="14.28%">Folio</th>
		<th>Título</th>
		<th width="14.28%">Solicitante</th>
		<th width="14.28%">Elaborado por</th>
		<th width="14.28%">Estado</th>
		<th width="14.28%">Fecha de elaboración</th>
		<th>Empresa</th>
		<th width="14.28%">Clasificación del gasto</th>
		<th width="14.28%">Acción</th>
		
	</thead>
	@foreach($requests as $request)
	<tr>
		<td>{{ $request->folio }}</td>
		<td>{{ $request->nominas->first()->title != null ? $request->nominas->first()->title : 'No hay' }}</td>
		@if($request->idRequest == "")
		<td>No hay solicitante</td>
		@else
		@foreach(App\User::where('id',$request->idRequest)->get() as $user)
		<td>{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</td>
		@endforeach
		@endif
		@foreach(App\User::where('id',$request->idElaborate)->get() as $user)
		<td>{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</td>
		@endforeach
		<td>
			{{ $request->statusrequest->description }}
		</td>
		@php	
			$time  				= strtotime($request->fDate);
			$date  				= date('d-m-Y H:i',$time);
		@endphp 
		<td>{{ $date  }}</td>
		<td>
			@if (isset($request->reviewedEnterprise->name))
				{{ $request->reviewedEnterprise->name }}
			@elseif(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{{ $request->requestEnterprise->name }}
			@else
				Varias
			@endif
		</td>
		<td>
			@if(isset($request->accountsReview->account))
			{{ $request->accountsReview->account.' '.$request->accountsReview->description }}
			@elseif(isset($request->accountsReview->account) == false && isset($request->accounts->account))
			{{ $request->accounts->account.' '.$request->accounts->description }}
			@else
			Varias
			@endif
		</td>
		<td>
			@if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 10 || $request->status == 11  || $request->status == 13) 
			<a alt="Nueva Solicitud" title="Nueva Solicitud" href="{{ route('payroll.create.new',$request->folio) }}" class='btn follow-btn'><span class='icon-plus'></span></a> 
			<a alt="Ver Solicitud" title="Ver Solicitud" href="{{ route('payroll.follow.edit',$request->folio) }}" class='btn follow-btn'><span class='icon-search'></span></a>
			@elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 12) 
			<a alt="Ver Solicitud" title="Ver Solicitud" href="{{ route('payroll.follow.edit',$request->folio) }}" class='btn follow-btn'><span class='icon-search'></span></a> 
			@else 
			<a alt="Editar Solicitud" title="Editar Solicitud" href="{{ route('payroll.follow.edit',$request->folio) }}" class='btn follow-btn'><span class='icon-pencil'></span></a>
			@endif
		</td>

	</tr>
	@endforeach
	
</table>
</div>

<center>
	{{ $requests->appends([
			'folio'		=> $folio,
			'name'		=> $name,
			'status'	=> $status,
			'mindate'	=> $mindate,
			'maxdate'	=> $maxdate
		])->render() }}
</center><br><br><br>
@else
	<div id="not-found" style="display:block;">No hay solicitudes</div>
@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$('.js-enterprise').select2(
			{
				placeholder : 'Seleccione la empresa',
				language 	: 'es',
				maximumSelectionLength : 1,

			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-account').select2(
			{
				placeholder : 'Seleccione la cuenta',
				language 	: 'es',
				maximumSelectionLength : 1,
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$('.js-status').select2(
			{
				placeholder : 'Seleccione un estado',
				language 	: 'es',
				maximumSelectionLength : 1,
			})
			.on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			$(document).on('change','.js-enterprise',function()
			{
				$('.js-account').empty();
				$enterprise = $(this).val();
				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ url("/administration/payroll/create/account") }}',
					data 	: {'enterpriseid':$enterprise},
					success : function(data)
					{
						$.each(data,function(i, d)
						{
							$('.js-account').append('<option value='+d.idAccAcc+'>'+d.account+' - '+d.description+' ('+d.content+')</option>');
						});
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.js-account').val(null).trigger('change');
					}
				})
			})
		});
		@if(isset($alert)) 
			{!! $alert !!} 
		@endif
	</script> 
@endsection