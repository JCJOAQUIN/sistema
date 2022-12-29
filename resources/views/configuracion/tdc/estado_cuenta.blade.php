@php
	use Carbon\Carbon;
	$date		= DateTime::createFromFormat('m-Y', $month.'-'.$year);
	$total		= 0;
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
			margin			: auto;
			padding			: 0;
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
			width: 180%;
		}
		.header .date
		{
			margin			: 0 auto;
			margin-bottom	: 5px;
			text-align		: left;
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
		<table class="header">
			<tbody>
				<tr>
					<td class="date">
						<br>
						<label class="pdf-label">{{ $tdc->user->name }} {{ $tdc->user->last_name }} {{ $tdc->user->scnd_last_name }}</label><br>
						<label class="pdf-label">Fecha: {{ $date->format('M-Y') }}</label><br>
						<label class="pdf-label">Número de Tarjeta: {{ $tdc->credit_card }}</label>
					</td>
					<td class="logo">
					</td>
				</tr>
			</tbody>
		</table>
	<main>
		<div class="pdf-full">
			<div class="pdf-body">
				<div class="block-info">
					<center>A continuación podrá verificar la información de la cuenta</center>
				</div>
				<p><br></p>
				<div class="block-info">
					<center>
						<strong>  DATOS </strong>
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
								<th width="20%">Fecha</th>
								<th width="60%">Concepto</th>
								<th width="20%">Importe</th>
							</tr>
						</thead>
						<tbody>
							@foreach($payments as $payment)
									@php
										$date	= new \DateTime($payment->paymentDate);
										$total	+= $payment->amount;
									@endphp
									<tr>
										<td>
											{{ $date->format('d-m-Y') }}
										</td>
										<td>
											Solicitud de {{ $payment->request->requestkind->kind }} #{{ $payment->request->folio }}
										</td>
										
										<td>
											$ {{ number_format($payment->amount,2) }}
										</td>
									</tr>
							@endforeach
						</tbody>
					</table>
					<p>&nbsp;</p>
					<table class="centered-table bank-info total-details">
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td><b>Total:</b></td>
								<td><b>$ {{ number_format($total,2) }}</b></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><b>Límite de crédito:</b></td>
								<td><b>$ {{ number_format($tdc->limit_credit,2) }}</b></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><b>Saldo actual:</b></td>
								<td><b>$ {{ number_format($tdc->limit_credit - $total,2) }}</b></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><b>Gasto Promedio:</b></td>
								<td><b>$ {{ number_format($monthlyAverageExpense,2) }}</b></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</main>
</body>
</html>