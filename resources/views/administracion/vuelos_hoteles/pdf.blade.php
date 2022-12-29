@php
	use Carbon\Carbon;
	$date = Carbon::now();
@endphp
<!DOCTYPE html>
<html>
	<head>
		
		<style type="text/css">
			@page {
				margin-top	: 1em !important;
				margin-bottom	: 1em !important;
			}
			body
			{
				background	: white;
				font-size	: 12px;
				position	: relative !important;
				font-family	: 'Helvetica',sans-serif;
			}
			header
			{
				/* left		: 0px; */
				position	: fixed;
				top			: -12.3em;
			}
			.header
			{
				/* border-collapse	: separate; */
				border-spacing	: 25px;
				width: 100%;
				margin			: 0 10px;
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
				width			: 100%;
			}

			.request-info tbody th
			{
				border-bottom	: 1px dotted #c6c6c6;
				font-weight		: 600;
				padding			: 0.5em 0.3em;
			}
			/* .request-info tbody tr.no-border th
			{
				border: none;
			} */

			.pdf-table-center-header
			{
				background		: #ff644e;
				color			: #fff;
				filter			: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff9f00', endColorstr='#ff9f00',GradientType=1 );
				font-size		: 1em;
				font-weight		: 700;
				padding			: 0.5em 0;
				text-align		: center;
				/* text-transform	: uppercase; */
			}
			.pdf-table-center-header-white
			{
				background		: #fff;
				color			: #000;
				filter			: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff9f00', endColorstr='#ff9f00',GradientType=1 );
				font-size		: 1em;
				font-weight		: 700;
				padding			: 0.5em 0;
				text-align		: center;
				/* text-transform	: uppercase; */
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
				background-color	: #ff644e;
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
			.page-break {
				page-break-after: always;
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
			.border-line
			{
				border			: 1px solid #c6c6c6;
			}
			.border-bottom-firma
			{
				border-bottom: 1px solid #c6c6c6;
				width:300px;
				margin:auto
			}
			.text-center
			{
				text-align: center;
			}

			.request-info-firma
			{
				width			: 50%;
			}

			.request-info-firma tbody th
			{
				font-weight		: 600;
			}
			.request-info-firma tbody tr.no-border th
			{
				border: none;
			}
			.float-right
			{
				float: right;
			}
			.float-left
			{
				float: left;
			}
			.borders
			{
				border: 1px solid #cfcfcf;
				border-collapse: collapse;
			}
			.h-20
			{
				height: 70px;
			}
			.text-center
			{
				text-align: center;
			}
			.text-left
			{
				text-align: left;
			}
			.text-right
			{
				text-align: right;
			}
			.w-10
			{
				width: 27%;
			}
			.w-15
			{
				width: 35%;
			}

			footer 
			{
	            position: fixed;
	            bottom: 0cm;
	            left: 0cm;
	            right: 0cm;
	            height: 5cm;
	            text-align: center;
	            line-height: 35px;
	        }
		</style>
	</head>
	<body>
			<br><br>
			<table class="header">
				<tbody>
					<center>
						<img width="25%" src="{{ url('images/enterprise/'.$flight_request->requestEnterprise->path) }}">
					</center>
				</tbody>
			</table>
			<br><br><br><br><br><br>
			<table class="headers">
				<tbody>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>SOLICITADO POR:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->requestUser->fullName() }}</td>
					</tr>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>FECHA DE SOLICITUD:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->flightsLodging->date }}</td>
					</tr>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>PROYECTO:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->requestProject->proyectName }}</td>
					</tr>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>WBS:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->wbs()->exists() ? $flight_request->wbs->code_wbs : 'No Aplica' }}</td>
					</tr>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>EDT:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->edt()->exists() ? $flight_request->edt->description : 'No Aplica' }}</td>
					</tr>
					<tr>
						<td width="25%" style="vertical-align: top;"><b>SOLICITADO POR PEMEX/PTI:</b></td>
						<td width="25%" style="vertical-align: top;">{{ $flight_request->flightsLodging->requestedByPemex() }}</td>
					</tr>
				</tbody>
			</table>
			<br><br><br>
			{{ $flight_request->details }}	
			@foreach($flight_request->flightsLodging->details as $flight)
				<table class="request-info">
					<tbody>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>NOMBRE:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->passenger_name }}</td>
							<td width="25%" style="vertical-align: top;"><b>CARGO:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->job_position }}</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>FECHA DE NACIMIENTO:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->born_date }}</td>
							<td width="25%" style="vertical-align: top;"><b>ÚLTIMO VIAJE FAMILIAR:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->last_family_journey_date }}</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>AEROLINEA (IDA):</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->airline }}</td>
							<td width="25%" style="vertical-align: top;"><b>RUTA (IDA):</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->route }}</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>FECHA DE SALIDA:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->departure_date }}</td>
							<td width="25%" style="vertical-align: top;"><b>HORARIO:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->departure_hour }}</td>
						</tr>
						@if($flight->airline_back != "")
							<tr>
								<td width="25%" style="vertical-align: top;"><b>AEROLINEA (REGRESO):</b></td>
								<td width="25%" style="vertical-align: top;">{{ $flight->airline_back }}</td>
								<td width="25%" style="vertical-align: top;"><b>RUTA (REGRESO):</b></td>
								<td width="25%" style="vertical-align: top;">{{ $flight->route_back }}</td>
							</tr>
							<tr>
								<td width="25%" style="vertical-align: top;"><b>FECHA DE REGRESO:</b></td>
								<td width="25%" style="vertical-align: top;">{{ $flight->departure_date_back }}</td>
								<td width="25%" style="vertical-align: top;"><b>HORARIO:</b></td>
								<td width="25%" style="vertical-align: top;">{{ $flight->departure_hour_back }}</td>
							</tr>
						@endif
						<tr>
							<td width="25%" style="vertical-align: top;"><b>HOSPEDAJE:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->hosting != "" ? 'SÍ' : 'NO' }}</td>
							<td width="25%" style="vertical-align: top;"><b>LUGAR:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->hosting != "" ? $flight->hosting : '---' }}</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>FECHA DE INGRESO:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->singin_date != "" ? $flight->singin_date : '---' }}</td>
							<td width="25%" style="vertical-align: top;"><b>FECHA DE SALIDA:</b></td>
							<td width="25%" style="vertical-align: top;">{{ $flight->output_date != "" ? $flight->output_date : '---' }}</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align: top;"><b>DESCRIPCIÓN/MOTIVO DE VIAJE:</b></td>
							<td colspan="3" width="75%" style="vertical-align: top;">{{ $flight->journey_description }}</td>
						</tr>
					</tbody>
				</table>
				<br>
			@endforeach
			<br><br><br>
			<footer>
				<div class="block-info">
					<table class="request-info-firma">
						<tbody>
							<tr>
								<td style="width:340px;" class="text-center">
									<label>Solicita</label> <br><br>
									<div class="border-bottom-firma"></div>
								</td>
								<td style="width:340px;" class="text-center">
									<label>Jefe Directo</label> <br><br>
									<div class="border-bottom-firma"></div>
								</td>
							</tr>
							<tr>
								<td style="width:340px;" class="text-center">{{ $flight_request->requestUser->fullName() }}</td> 
								<td style="width:340px;" class="text-center">Vo. Bo.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</footer>
			
			
		{{-- <script type="text/php">
			if (isset($pdf))
			{
				$text = "{PAGE_NUM}";
				$size = 8;
				$font = $fontMetrics->getFont("Verdana");
				$x = 425;
				$y = $pdf->get_height() - 35;
				$pdf->page_text($x, $y, $text, $font, $size);
			}
		</script> --}}
	</body>
	
</html>
