@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		{!! Form::open(['route' => 'procurement-purchases.warehouse-search', 'method' => 'GET', 'id'=>'formsearch']) !!}
			@component('components.labels.title-divisor')    BUSCAR SOLICITUDES @endcomponent
			<center>
				<div class="search-table-center">
					<div class="search-table-center-row">
						<div class="left">
							<br><label class="label-form">Descripción:</label>
						</div>
						<div class="right">
							<p>
								<input type="text" name="description" class="input-text-search" id="input-search" placeholder="Escribe aquí..." value="{{ isset($description) ? $description : '' }}">
							</p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Código:</label>
						</div>
						<div class="right">
							<p>
								<input type="text" name="code" class="input-text-search" id="input-search" placeholder="Escribe aquí..." value="{{ isset($code) ? $code : '' }}">
							</p>
						</div>
					</div>
					<div class="search-table-center-row">
						<div class="left">
							<label class="label-form">Fecha de Entrada:</label>
						</div>
						<div class="right-date">
							<p>
								<input type="text" name="mindate" step="1" class="input-text-date datepicker" placeholder="Desde" value="{{ isset($mindate) ? $mindate : '' }}"> - <input type="text" name="maxdate" step="1" class="input-text-date datepicker" placeholder="Hasta" value="{{ isset($maxdate) ? $maxdate : '' }}">
							</p>
						</div>
					</div>
					<div class="search-table-center-row">
						<p>
							<select title="Unidad" name="measure[]" class="js-measure" multiple="multiple" style="width: 98%; max-width: 150px;">
								@foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
									@foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
								  		<option value="{{ $child->description }}" @if(isset($measure) && in_array($child->description,$measure)) selected="selected" @endif>{{ $child->description }}</option>
									@endforeach
							  	@endforeach
							</select>
						</p>
					</div>
				</div>
			</center>
			<center>
				<button class="btn btn-search" type="submit"><span class="icon-search"></span> Buscar</button>
			</center>
		<br><br>
	</div>
<br>

	@if(count($inventory) > 0)
			<div style='float: right'><a class="btn btn-orange export" href="{{ route('procurement-purchases.warehouse-export') }}"><i class="fas fa-file-excel"></i> Exportar Resultado</a></div>
		{!! Form::close() !!}
		<div class="table-responsive">
			<table class="table table-striped">
				<thead class="thead-dark">
					<th class="sticky">Descripción</th>
					<th>Unidad</th>
					<th>Código</th>
					<th>Cantidad</th>
					<th>Fecha de Entrada</th>
					<th></th>
				</thead>
				@foreach($inventory as $inv)
					<tr>
						<td class="sticky">
							{{ $inv->description }}
						</td>
						<td>
							{{ $inv->measure }}
						</td>
						<td>
							{{  $inv->code_mat }}
						</td>
						<td>
							{{ $inv->quantity }}
						</td>
						<td>
							{{  $inv->date_entry }}
						</td>
						
						<td>
							<a alt="Ver artículo" title="Ver artículo" href="#" class='btn follow-btn'>
								<i class="fas fa-search"></i>
							</a>
						</td>
					</tr>
				@endforeach
			</table>
		</div>
		<center>
			{{ $inventory->appends([
					'description'	=> $description,
					'code'			=> $code,
					'mindate'		=> $mindate,
					'maxdate'		=> $maxdate,
					'measure'		=> $measure,
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
		$(function() 
		{
			$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
		});
		$('[name="measure[]"]').select2(
		{
			placeholder : 'Seleccione la unidad',
			language 	: 'es',

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
		
		
	});
	@if(isset($alert))
		{!! $alert !!}
	@endif
</script>
@endsection