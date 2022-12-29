@extends('layouts.child_module')
@section('data')
 @php
	$taxesCount = $taxesCountBilling = 0;
	$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
 @endphp
 	<center>
		<center>
		<strong>TIPO DE SOLICITUD:</strong>
		</center>
		<div class="divisor">
			<div class="gray-divisor"></div>
			<div class="orange-divisor"></div>
			<div class="gray-divisor"></div>
		</div><br>
	</center>
	<!--input type="radio" name="typeRequest" id="ajuste" value="1">
	<label for="ajuste">AJUSTE DE MOVIMIENTOS</label> <br><br><br>
	<input type="radio" name="typeRequest" id="prestamos" value="2">
	<label for="prestamos">PRESTAMOS INTER-EMPRESAS</label><br><br><br>
	<input type="radio" name="typeRequest" id="compras" value="3">
	<label for="compras">COMPRA INTER-EMPRESAS</label><br><br><br>
	<input type="radio" name="typeRequest" id="grupos" value="4">
	<label for="grupos">GRUPOS</label><br><br><br>
	<input type="radio" name="typeRequest" id="movimientos" value="5">
	<label for="movimientos">MOVIMIENTOS MISMA EMPRESA</label><br><br><br>
	<br><br><br-->
	<div class="container-sub-blocks">
		<a href="{{ route('movements-accounts.adjustment') }}" class="sub-block" id="btn-adjustment">AJUSTE DE MOVIMIENTOS</a>
		<a href="{{ route('movements-accounts.loan') }}" class="sub-block" id="btn-loan">PRESTAMOS INTER-EMPRESAS</a>
		<a href="{{ route('movements-accounts.purchase') }}" class="sub-block" id="btn-purchase">COMPRA INTER-EMPRESAS</a>
		<a href="{{ route('movements-accounts.groups') }}" class="sub-block" id="btn-group">GRUPOS</a>
		<a href="{{ route('movements-accounts.movements') }}" class="sub-block" id="btn-movements">MOVIMIENTOS MISMA EMPRESA</a>
	</div>
	<br><br>
@endsection