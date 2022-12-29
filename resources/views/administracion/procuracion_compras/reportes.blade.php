@extends('layouts.child_module')
  
@section('data')
<div id="container-cambio" class="div-search">
	{!! Form::open(['route' => 'procurement-purchases.report', 'method' => 'GET', 'id'=>'formsearch']) !!}
		@component('components.labels.title-divisor')    BUSCAR SOLICITUDES @endcomponent
		<center>
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<br><label class="label-form">Número de Orden:</label>
					</div>
					<div class="right">
						<p>
							<input type="text" name="numberOrder" class="input-text-search" id="input-search" placeholder="Escribe aquí..." value="{{ isset($numberOrder) ? $numberOrder : '' }}">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Fecha de Elaboración:</label>
					</div>
					<div class="right-date">
						<p>
							<input type="text" name="mindate" step="1" class="input-text-date datepicker" placeholder="Desde" value="{{ isset($mindate) ? $mindate : '' }}"> - <input type="text" name="maxdate" step="1" class="input-text-date datepicker" placeholder="Hasta" value="{{ isset($maxdate) ? $maxdate : '' }}">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Cuenta" class="js-account removeselect" multiple="multiple" name="account[]" style="width: 98%; max-width: 150px;">
							@foreach(App\CatAccounts::all() as $acc)
								<option value="{{ $acc->id }}" @if(isset($account) && in_array($acc->id,$account)) selected="selected" @endif>{{ $acc->fullName() }}</option>
							@endforeach
						</select>
					</p>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Estado de Solicitud" name="status[]" class="js-status" multiple="multiple" style="width: 98%; max-width: 150px;">
							@foreach(App\StatusRequest::whereIn('idrequestStatus',[24,25,26])->get() as $stat)
								<option value="{{ $stat->idrequestStatus }}" @if(isset($request) && $request->status == $stat->idrequestStatus) selected="selected" @endif>{{ $stat->description }}</option>
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
	<div style='float: right'><a class="btn btn-orange export-dtr" href="{{ route('procurement-purchases.report-dtr') }}"><i class="fas fa-file-pdf"></i> Reporte DTR</a></div>
	<div style='float: right'><a class="btn btn-orange export" href="{{ route('procurement-purchases.report-msr') }}"><i class="fas fa-file-pdf"></i> Reporte MSR</a></div>
	{!! Form::close() !!}
	<div class="table-responsive">
		<table class="table table-striped">
			<thead class="thead-dark">
				<th class="sticky">Número OC</th>
				<th>Proyecto</th>
				<th>CO#</th>
				<th>Estatus</th>
				<th>Descripción</th>
				<th>Proveedor</th>
				<th>Fecha de elaboración</th>
				<th>Ver</th>
				<th>Imprimir</th>
			</thead>
			@foreach($requests as $request)
				<tr>
					<td class="sticky">
						{{  $request->numberOrder }}
					</td>
					<td>
						{{ $request->project()->exists() ? $request->project->proyectName : 'Sin Proyecto' }}
					</td>
					<td>
						{{  $request->numberCO }}
					</td>
					<td>
						{{ $request->statusRequest->description }}
					</td>
					<td>
						{{  $request->descriptionShort }}
					</td>
					<td>
						{{  $request->provider }}
					</td>
					@php	
						$time	= new DateTime($request->created_at);
						$date	= $time->format('d-m-Y H:i');
					@endphp 
					<td>{{ $date  }}</td>
					<td>
						<a alt="Ver Orden" title="Ver Orden" href="{{ route('procurement-purchases.report-view',$request->id) }}" class='btn follow-btn'>
							<i class="fas fa-search"></i>
						</a>
					</td>
					<td>
						<a alt="Descargar Orden" title="Descargar Orden" href="{{ route('procurement-purchases.purchase-download',$request->id) }}" class='btn follow-btn'>
							<i class="fas fa-file-pdf"></i>
						</a>
					</td>
				</tr>
			@endforeach
		</table>
	</div>
	<center>
		{{ $requests->appends([
			'account'			=> $account,
			'numberOrder'		=> $numberOrder,
			'status'			=> $status,
			'mindate'			=> $mindate,
			'maxdate'			=> $maxdate,
		])->render() }}
	</center>
	<br><br><br>
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
		$('[name="folio"]').numeric({ negative:false});
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
		});
		$('[name="account[]"]').select2(
		{
			placeholder : 'Seleccione la cuenta',
			language 	: 'es',
		});
		$('[name="status[]"]').select2(
		{
			placeholder : 'Seleccione un estado',
			language 	: 'es',
		});

		$('[name="request_purchase[]"]').select2(
		{
			language				: "es",
			placeholder 			: "Seleccione un solicitante",
			width 					: "100%",
			tags 					: true,
		});
		$(document).on('click','.export',function(e)
		{
			e.preventDefault();
			attr = $(this).attr('href');
			swal({
				text: "Solo se descargarán las ordenes 'Liberadas'",
				icon: "warning",
				buttons: ["Cancelar","OK"],
			})
			.then((isConfirm) =>
			{
				if(isConfirm)
				{
					window.location.href=attr;
				}
			});
		})
		.on('click','.export-dtr',function(e)
		{
			e.preventDefault();
			attr = $(this).attr('href');
			swal({
				text: "Solo se descargarán las ordenes en estado 'Proceso'",
				icon: "warning",
				buttons: ["Cancelar","OK"],
			})
			.then((isConfirm) =>
			{
				if(isConfirm)
				{
					window.location.href=attr;
				}
			});
		});
		
	});
	@if(isset($alert))
		{!! $alert !!}
	@endif
</script>
@endsection