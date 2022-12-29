<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		header
		{
			width          : 80%;
			margin         : 0 auto;
		}
		@page 
		{
			margin	: 2em 0 0 0 !important;
            font-family: Arial, Helvetica, sans-serif;
        }
		body
		{
			font-family: Arial, Helvetica, sans-serif;
			background : white;
			font-size  : 12px;
			position   : relative !important;
		}
		header
		{
			left		: 0px;
			position	: fixed;
			right		: 0px;
			text-align	: center;
			top			: 0em;
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
			background		: #343a40;
			color			: #fff;
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
		.border-line
		{
			border			: 1px solid #c6c6c6;
		}
		.border-bottom
		{
			border-bottom: 1px solid #c6c6c6;
		}
		.text-center
		{
			text-align: center;
		}
		.page-break 
		{
		    page-break-after: always;
		}
		.footer 
		{
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: #2a0927;
            color: white;
            text-align: center;
            line-height: 35px;
        }
	</style>
</head>
<body>
	<main>	
		@foreach($requests as $request)
		<div class="pdf-full">
			<center><img src="{{ url('images/banner_idinsa_proyecta.png') }}" width="30%"></center>
			<div class="pdf-body">
				<div class="block-info">
					<br>
					<table class="request-info">
						<thead>
							<tr>
								<th colspan="2" class="pdf-table-center-header">{{ $request->project->proyectName }}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th align="left">Para:</th>
								<th align="right">{{ $request->provider }}</th>
							</tr>
							<tr>
								<th align="left">Número de OC:</th>
								<th align="right">{{ $request->numberOrder }}</th>
							</tr>
							<tr>
								<th align="left">Fecha OC:</th>
								<th align="right">
									{{ $request->date_request!="" ? $request->date_request->format('Y-m-d') : 'Sin Fecha' }}
								</th>
							</tr>
							<tr>
								<th align="left">Monto:</th>
								<th align="right">
									{{ number_format($request->total_request,2) }}
								</th>
							</tr>
							<tr>
								<th align="left">Moneda:</th>
								<th align="right">
								{{ $request->type_currency }}	
								</th>
							</tr>
							<tr>
								<th align="left">Estatus:</th>
								<th align="right">
									{{ $request->statusRequest->description }}	
								</th>
							</tr>
							<tr>
								<th align="left">Comprador:</th>
								<th align="right">
									{{ $request->buyer }}	
								</th>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<div class="block-info">
					<table class="centered-table bank-info">
						<thead>
							<tr>
								<th colspan="8" class="pdf-table-center-header">DATOS DEL PEDIDO</th>
							</tr>
							<tr>
								<th>Código Mat.</th>
								<th>Descripci&oacute;n</th>
								<th>Partida</th>
								<th>Medida</th>
								<th>Cantidad</th>
								<th>Precio</th>
								<th>Total</th>
								<th>Fecha Entrega</th>
							</tr>
						</thead>
						<tbody>
							@foreach($request->details as $detail)
								<tr>
									<td>{{ $detail->code }}</td>
									<td>{{ $detail->description }}</td>
									<td>{{ $detail->part }} </td>
									<td>{{ $detail->unit }}</td>
									<td>{{ $detail->quantity }}</td>
									<td>{{ number_format($detail->price,2) }}</td>
									<td>{{ number_format($detail->total_concept,2) }} </td>
									<td>{{ $detail->date_two!="" ? $detail->date_two->format('Y-m-d') : 'Sin Fecha' }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<p>&nbsp;</p>
					<table class="centered-table total-details">
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><strong>TOTAL DE ORDEN DE COMPRA:</strong></td>
								<td>$ {{ number_format($request->total_request,2) }}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br><br></p>
			</div>
		</div>
		<div class="page-break"></div>
		@endforeach
	</main>
	<script type="text/php">
		if (isset($pdf))
		{
			$text = "Página {PAGE_NUM} de {PAGE_COUNT}";
			$size = 8;
			$font = $fontMetrics->getFont("Verdana");
			$width = $fontMetrics->get_text_width($text, $font, $size) / 2;
			$x = ($pdf->get_width() - $width);
			$y = $pdf->get_height() - 35;
			$pdf->page_text($x, $y, $text, $font, $size);
		}
	</script>
</body>


</html>