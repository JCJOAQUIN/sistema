@extends('layouts.child_module')
@section('data')
<div id="container-cambio" class="div-search">
			{!! Form::open(['route' => 'payroll.authorization', 'method' => 'GET', 'id'=>'formsearch']) !!}
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
				</div>
			</center>
			<center>
				<button class="btn 	btn-search" type="submit"><span class="icon-search"></span> Buscar</button>
			</center>
			<br><br>
		</div>
	<br>
@if(count($requests) > 0)
<div style='float: right'><label class='label-form'>Exportar a Excel <label><button class='btn btn-green export' type='submit'  formaction="{{ route('payroll.export.authorization') }}"><span class='icon-file-excel'></span></button></div>
{!! Form::close() !!}
<div class="table-responsive table-striped">
	<table class="table">
	<thead class="thead-dark">
		<th width="14.28%">Folio</th>
		<th>Título</th>
		<th width="14.28%">Solicitante</th>
		<th width="14.28%">Elaborado por</th>
		<th width="14.28%">Estado</th>
		<th width="14.28%">Fecha de revisión</th>
		<th>Empresa</th>
		<th width="14.28%">Clasificación del gasto</th>
		<th width="14.28%">Acción</th>
	</thead>
	@foreach($requests as $request)
	<tr>
		<td>{{ $request->folio }}</td>
		<td>{{ $request->nominas->first()->title != null ? $request->nominas->first()->title : 'No hay' }}</td>
		@foreach(App\User::where('id',$request->idRequest)->get() as $user)
		<td>{{ $user->name }} {{ $user->last_name }} {{ $user->scnd_last_name }}</td>
		@endforeach
		@foreach(App\User::where('id',$request->idElaborate)->get() as $elaborate)
		<td>{{ $elaborate->name }} {{ $elaborate->last_name }} {{ $elaborate->scnd_last_name }}</td>
		@endforeach
		<td>
			{{ $request->statusrequest->description }}
		</td>
		@php	
			$time  				= strtotime($request->reviewDate);
			$date  				= date('d-m-Y H:i',$time);
		@endphp 
		<td>{{ $date  }}</td>
		<td>{{ isset($request->reviewedEnterprise->name) ? $request->reviewedEnterprise->name : "Varias" }}</td>
		<td>{{ isset($request->accountsReview->account) ? $request->accountsReview->account.' '.$request->accountsReview->description : "Varias" }}</td>
		<td><a title="Editar Solicitud" href="{{ route('payroll.authorization.edit',$request->folio) }}" class='btn follow-btn'><span class='icon-pencil'></span></a></td>

	</tr>
	@endforeach
	
</table>
</div>

<center>
	{{ $requests->appends([
		'folio' 	=> $folio, 
		'name' 		=> $name,
		'mindate' 	=> $mindate, 
		'maxdate' 	=> $maxdate,
		])->render() }}
</center>
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
		});
	});
		
      @if(isset($alert)) 
      {!! $alert !!} 
      @endif 
    </script> 
@endsection