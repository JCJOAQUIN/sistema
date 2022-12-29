@php
	use Carbon\Carbon;
	$date = Carbon::now();
	$taxes=0;
@endphp
<!DOCTYPE html>
<html>
<head>
	
	<style type="text/css">
		@page {
			margin	: 9em 0 0 0 !important;
		}
		body
		{
			background	: white;
			font-size	: 12px;
			position	: relative !important;
		}
		header
		{
			left		: 0px;
			position	: fixed;
			right		: 0px;
			text-align	: center;
			top			: -9.3em;
		}
		.header
		{
			border-collapse	: separate;
			border-spacing	: 25px;
			width: 90%;
			margin			: 0 auto;
		}
		.header .logo
		{
			margin			: 0 auto;
			margin-bottom	: 5px;
			padding			: 5px;
			text-align		: left;
			vertical-align	: middle;
			width			: 100px;
		}
		.header .logo img
		{
			width: 100%;
		}
		.header .date
		{
			margin			: 0 auto;
			margin-bottom	: 5px;
			padding			: 5px;
			text-align		: right;
			width			: 450px;
		}
		.request-info
		{
			border			: 1px solid #c6c6c6;
			border-collapse	: separate;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info tbody th
		{
			border-bottom	: 1px dotted #c6c6c6;
			font-weight		: 600;
			padding			: 0.5em 0.3em;
		}
		.request-info tbody tr.no-border th
		{
			border: none;
		}
		.pdf-table-center-header
		{
			background		: #ff9f00;
			background		: -moz-linear-gradient(left, #ff9f00 0%, #ffb700 40%, #ffb700 60%, #ff9f00 100%);
			background		: -webkit-linear-gradient(left, #ff9f00 0%,#ffb700 40%,#ffb700 60%,#ff9f00 100%);
			background		: linear-gradient(to right, #ff9f00 0%,#ffb700 40%,#ffb700 60%,#ff9f00 100%);
			color			: #fff;
			filter			: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff9f00', endColorstr='#ff9f00',GradientType=1 );
			font-size		: 1em;
			font-weight		: 700;
			padding			: 0.5em 0;
			text-align		: center;
			text-transform	: uppercase;
		}
		.pdf-divisor
		{
			margin	: 0 auto;
			width	: 95%;
		}
		.pdf-divisor tr td
		{
			padding			: 0;
		}
		.pdf-divisor tr td:nth-child(2)
		{
			width	: 20%;
		}
		.pdf-divisor tr td::after
		{
			background-color	: #c6c6c6;
			content				: '';
			display				: inline-block;
			height				: 1px;
			margin				: 1px 0;
			vertical-align		: middle;
			width				: 100%;
		}
		.pdf-divisor tr td:nth-child(2)::after
		{
			background-color	: #fca700;
			content				: '';
			display				: inline-block;
			height				: 3px;
			width				: 100%;
		}
		.centered-table
		{
			margin	: auto;
			width	: 90%;
		}
		.bank-info
		{
			margin-top: .5em;
		}
		.bank-info th
		{
			font-weight: 600;
		}
		.bank-info th,
		.bank-info td
		{
			text-align: center;
		}
		.block-info
		{
			page-break-inside	: avoid;
		}
		.total-details tr td
		{
			text-align	: right;
		}
		.total-details tr td:nth-child(2)
		{
			text-align	: left;
		}
		.total-details tr td:first-child
		{
			width	: 40%;
		}
		.pdf-notes
		{
			border		: 1px dotted #c6c6c6;
			margin		: 15px 5px 5px;
			padding		: 3px 5px;
			text-align	: left;
		}
	</style>
</head>
<body>
	<header>
		<table class="header">
			<tbody>
				<tr>
					<td class="logo">
						<label class="pdf-label">Fecha: {{ date('d-m-Y',strtotime($date)) }}</label>
						<br>
			<label class="pdf-label">Hora: {{ $date->toTimeString() }}</label>
					</td>
				</tr>
			</tbody>
		</table>
	</header>

		<div class="pdf-full">
			<div class="pdf-body">
		<div class="">
		  
		  			<center><h1>Reporte de almacén de requisiciones</h1></center>
					@component('components.labels.title-divisor')    ARTÍCULOS</strong>
					</center>
					<table class="pdf-divisor">
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
					<table class="centered-table bank-info">
						<thead>
							<tr>
								<th>Código</th>
								<th>Medida</th>
								<th>Unidad</th>
								<th>Nombre</th>
								<th>Cantidad</th>
								<th>Categoría</th>
								<th>Almacén</th>
							</tr>
						</thead>
						<tbody>


			  @foreach ($requests as $request)
				@if ($request->status == 17)
				  @foreach ($request->requisition->purchases as $purchase)
					@foreach ($purchase->detailPurchase as $detail)
					<tr>
						<td>{{ $detail->code }}</td>
						<td>{{ $detail->measurement }}</td>
						<td>{{ $detail->unit }}</td>
						<td>{{$detail->description}}</td>
						<td>{{$detail->quantity}}</td>
						<td>{{$detail->categoria}}</td>
						<td>{{ $request->requisition->requisition_type == 1 ? $detail->estatusAlmacen : 'No Aplica' }}</td>
					</tr>
					@endforeach
					  
				  @endforeach
				  @foreach ($request->requisition->refunds as $refund)
					  @foreach ($refund->refundDetail as $detail)
					  <tr>
					  	<td>{{ $detail->code }}</td>
						<td>{{ $detail->measurement }}</td>
						<td>{{ $detail->unit }}</td>
						<td>{{$detail->concept}}</td>
						<td>{{$detail->quantity}}</td>
						<td>{{$detail->categoria}}</td>
						<td>N/A</td>
					  </tr>
					  @endforeach
				  @endforeach
				  
				  @else
				  @foreach ($request->requisition->details as $detail)
				  <tr>
				  	<td>{{ $detail->code }}</td>
					<td>{{ $detail->measurement }}</td>
					<td>{{ $detail->unit }}</td>
					<td>{{$detail->description}}</td>
					<td>{{$detail->quantity}}</td>
					<td>{{$detail->categoria}}</td>
					<td>Pendiente</td>
				  </tr>
				  @endforeach
				@endif
	
	
			  @endforeach
						</tbody>
					</table>
					<p>&nbsp;</p>

				</div>

			</div>
		</div>

</body>
</html>