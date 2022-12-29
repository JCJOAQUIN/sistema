<!DOCTYPE html>
<html>
	<head>
		<style>
		
			@page 
			{
				margin	: 6em 0 0 0 !important;
			}
			body
			{
				background	: white;
				font-size	: 7px;
				font-family	: Arial, Helvetica, sans-serif
				position	: relative !important;
			}
			.header
			{
				left		: 10px;
				right		: 10px;
				position	: fixed;
				text-align	: center;
				top			: -10.8em;
			}
			.request-info
			{
				margin			: 0 auto;
				width			: 90%;
			}
			.table
			{
				border-collapse: collapse;
				width: 100%;
			}
			.table td, th
			{
				text-align: left;
				border: 1px solid #000000;
			}

			.block-info
			{
				page-break-inside	: initial;
			}
		</style>
	</head>
	<body>
		<header></header>
		<main>
			<div class="block-info">
				<table class="table">
					<thead  style="text-align: center !important;">
						<tr style="height:100px; font-weight: bold; font-size: 9px;">
							<td rowspan="2">Fecha: <br> {{ date('d-m-Y') }}</td>
							<td rowspan="2"> </td>
							<td colspan="7">PROYECTA INDUSTRIAL DE MÉXICO</td>
							<td colspan="3" rowspan="2" style="height: 50px;">
								<img src="{{ url('images/proyecta.png') }}" style="height:50px; width:100px; position:absolute; right:40px;">
							</td>
						</tr>
						<tr style="height:100px; font-weight: bold; font-size: 9px;">
							<td colspan="7">SEGUIMIENTO DEL ESTADO DE NO CONFORMIDADES</td>
						</tr>
						<tr style="font-weight: bold;">
							<td width="5%">N°</td>
							<td width="5%">N° DE REPORTE DE NC</td>
							<td width="17%">DESCRIPCIÓN</td>
							<td width="5%">FECHA</td>
							<td width="5%">LOCALIZACIÓN</td>
							<td width="10%">PROCESO Y/O ÁREA</td>
							<td width="8%">ORIGINADA POR:</td>
							<td width="5%">TIPO DE ACCIÓN</td>
							<td width="10%">EMITIDA POR:</td>
							<td width="5%">STATUS</td>
							<td width="5%">FECHA DE CIERRE</td>
							<td width="20%">OBSERVACIONES</td>
						</tr>
					</thead>
					<tbody>
						@foreach($status_no_conformity as $key => $nc)
							<tr>
								<td style="text-align: center;">{{ $key+1 }}</td>
								<td>{{ $nc->nc_report_number }}</td>
								<td>{{ $nc->description }}</td>
								<td>{{ $nc->date }}</td>
								<td>{{ $nc->location }}</td>
								<td>{{ $nc->process_area }}</td>
								<td>{{ $nc->non_conformity_origin }}</td>
								<td>{{ $nc->typeAction() }}</td>
								<td>{{ $nc->emited_by }}</td>
								<td>{{ $nc->statusData() }}</td>
								<td>{{ $nc->close_date }}</td>
								<td>{{ $nc->observations }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</main>
	</body>
</html>
