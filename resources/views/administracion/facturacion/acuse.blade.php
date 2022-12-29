<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/reset.css') }}">
	<style type="text/css">
		body *,
		tbody th,
		tbody td,
		{
			font-family: 'Baskerville' !important;
			font-size	: 10px;
			font-color: #000000;
		}
		@page {
			margin	: 4em 0 0 0 !important;
        }
		body
		{
			background	: white;
			font-size	: 12px;
			position	: relative !important;
			padding-top: 10rem;
		}
		header
		{
			left		: 0px;
			position	: fixed;
			right		: 0px;
			top			: 0px;
		}
		img.logo
		{
			max-width: 10rem;
			max-height: 7rem;
		}
		.request-info
		{
			border			: 1px solid #c6c6c6;
			border-collapse	: separate;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info.no-border
		{
			border	: 0;
		}
		.request-info tbody th
		{
			border-bottom	: 1px dotted #c6c6c6;
			font-family		: 'Baskerville' !important;
			font-weight		: 600;
			padding			: 0.5rem;
		}

		.request-info tbody th div.normal
		{
			font-weight	: 300;
		}
		.thead-dark tr th,
		.thead-dark tr td
		{
			font-family	: 'Baskerville' !important;
			background-color: #dddddd !important;
		}
		.request-info tbody th div.text-left
		{
			float		: left;
			font-family	: 'Baskerville' !important;
			font-weight	: bolder;
		}
		.request-info tbody tr.no-border th
		{
			border: none;
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
		.table
		{
			border-collapse	: collapse !important;
			margin			: 0;
		}

		.table td,
		.table th,
		.table-permission td,
		.table-permission th
		{
			padding	: .3rem !important;
		}
		.table thead th
		{
			border-bottom	: 0 !important;
			vertical-align	: middle;
		}
		.pdf-full
		{
			width	: 780px;
		}
		.text-break
		{
			font-size	: 8px;
			max-width	: 650px;
			width		: 650px;
			word-break	: break-all !important;
			word-wrap	: break-word !important;
		}
		.text-break2
		{
			font-size	: 8px;
			max-width	: 540px;
			width		: 540px;
			word-break	: break-all !important;
			word-wrap	: break-word !important;
		}
		.text-center
		{
			text-align: center;
		}
		.font-bold
		{
			font-weight: bold;
		}
		.title
		{
			font-size: 1.3rem;
		}
	</style>
</head>
<body>
	<header>
		<div style="width: 690px;margin: auto;position: relative;text-align: right">
			<img class="logo" src="{{ asset('/images/enterprise/'.\App\Enterprise::where('rfc',$bill->rfc)->first()->path) }}">
		</div>
	</header>
	<main>
		<p><br></p>
		<p><br></p>
		<div class="pdf-full">
			<div class="pdf-body">
				<div class="block-info">
					<center>
						<p class="font-bold title">ACUSE DE CANCELACIÓN</p>
					</center>
					<p><br></p>
					<table class="request-info no-border centered-table">
						<tbody>
							<tr>
								<th width="50%" class="text-left">Fecha y hora de solicitud</th>
								<th width="50%" class="text-right normal">{{$bill->cancelRequestDate}}</th>
							</tr>
							<tr>
								<th width="50%" class="text-left">Fecha y hora de cancelación</th>
								<th width="50%" class="text-right normal">{{$bill->CancelledDate}}</th>
							</tr>
							<tr>
								<th width="50%" class="text-left">RFC emisor</th>
								<th width="50%" class="text-right normal">{{$bill->rfc}}</th>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<div class="block-info">
					<table class="centered-table text-center">
						<thead>
							<tr>
								<th class="font-bold">Folio fiscal</th>
								<th class="font-bold">Estado CFDI</th>
								<th class="font-bold">Estado de cancelación</th>
								<th class="font-bold">Motivo de cancelación</th>
								<th class="font-bold">CFDI reemplaza</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{$bill->uuid}}</td>
								<td>
									{{$bill->statusCFDI}}
								</td>
								<td>
									{{$bill->statusCancelCFDI}}
								</td>
								<td>
									@switch($bill->cancellation_reason)
										@case('01')
											01 - Comprobantes emitidos con errores con relación.
											@break
										@case('02')
											02 - Comprobantes emitidos con errores sin relación.
											@break
										@case('03')
											03 - No se llevó a cabo la operación.
											@break
										@case('04')
											04 - Operación nominativa relacionada en una factura global.
											@break
									@endswitch
								</td>
								<td>
									{{$bill->substitute_folio}}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<p><br></p>
				<div class="block-info">
					<table class="table" style="margin: auto;width: 700px;max-width: 700px; border:1px solid">
						<thead class="thead-dark">
							<tr>
								<th>Sello digital SAT</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td align="center">
									<div class="text-break">{{$bill->signatureValueCancel}}</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</main>
</body>
</html>