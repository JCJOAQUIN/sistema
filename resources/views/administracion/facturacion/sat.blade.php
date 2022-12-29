@extends('layouts.child_module')
  
@section('data')
<div id="container-cambio" class="div-search">
	<form action="">
		@component('components.labels.title-divisor')    FACTURAS EMITIDAS Y RECIBIDAS @endcomponent
		<center>
			<div class="search-table-center">
				<div class="search-table-center-row">
					<div class="left">
						<label class="label-form">Rango de fechas:</label>
					</div>
					<div class="right-date">
						<p>
							<input type="text" name="mindate" value="{{ isset($mindate) ? date('d-m-Y',strtotime($mindate)) : '' }}" step="1" class="input-text-date datepicker" placeholder="Desde"> - <input type="text" name="maxdate" value="{{ isset($maxdate) ? date('d-m-Y',strtotime($maxdate)) : '' }}" step="1" class="input-text-date datepicker" placeholder="Hasta">
						</p>
					</div>
				</div>
				<div class="search-table-center-row">
					<p>
						<select title="Empresa" name="rfc_enterprise" class="js-enterprise" multiple="multiple" style="width: 98%; max-width: 150px;">
							@foreach(App\Enterprise::orderName()->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
								@if(isset($rfc_enterprise) && $rfc_enterprise == $enterprise->rfc)
									<option value="{{ $enterprise->rfc }}" selected>{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
								@else
									<option value="{{ $enterprise->rfc }}">{{ strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name }}</option>
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
	</form>
<br><br>
</div>
<br>

	<div class="table-responsive table-striped">
		<table class="table">
			<thead class="thead-dark">
				<th>Emisor</th>
				<th>Receptor</th>
				<th></th>
				<th></th>
				<th></th>
			</thead>
			<tbody>
				
			</tbody>
		</table>
	</div>
	

@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
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
		});
		@if(isset($alert))
			{!! $alert !!}
		@endif
	</script>
@endsection
