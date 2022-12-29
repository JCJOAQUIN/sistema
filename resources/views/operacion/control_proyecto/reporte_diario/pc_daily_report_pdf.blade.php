<!DOCTYPE html>
<html>
<head>
	<style>
		.header
		{
			border-collapse	: collapse;
			border-spacing	: 25px;
			width			: 90%;
			margin			: 0 auto;
		}	
		@page 
		{
			margin	: 9em 0 4em 0 !important;
		}
		body
		{
			background	: white;
			font-size	: 10.5px;
			font-family	: Arial, Helvetica, sans-serif;
			position	: relative !important;
			counter-reset: page;
		}
		header
		{
			left		: 0px;
			position	: fixed;
			right		: 0px;
			text-align	: center;
			top			: -10.8em;
		}
		.header .logo
		{
			margin			: 0 auto;
			margin-bottom	: 5px;
		}
		.block-info
		{
			page-break-inside	: initial;
		}
		.request-info-text
		{
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info
		{
			border			: 1px solid;
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info tbody th
		{
			border-bottom	: 1px solid;
		}
		.request-info tbody td
		{
			border-bottom	: 1px solid;
		}
		.request-info-cell
		{
			border			: 1px solid;
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info-cell tbody td
		{
			border			: 1px solid #3a4041;
			padding			: 10px 3px 10px 3px;
		}
		.request-info-cell tr th
		{
			font-size		: 12px;
			border			: 1px solid #3a4041;
			padding			: 10px 0 10px 5px;
		}
		.pdf-table-center-header
		{
			color: #000;
			font-size: 2em; 
			font-weight: 400; 
			padding: 0.2.5em 0; 
			text-align: center;
			background: #c6c6c6;
		}
		.pdf-table-left-header
		{
			color: #000;
			font-size: 2em; 
			font-weight: 400; 
			padding: 0.2.5em 0; 
			text-align: left;
			background: #c6c6c6;
		}
		.table-center-header-header
		{
			color: #000;
			font-size: 18px; 
			font-weight: 400; 
			padding: 0.2.5em 0; 
			text-align: center;
		}
		.title-header
		{
			color: #000;
			font-size: 18px; 
			font-weight: 400; 
			padding: 0.2.5em 0; 
			text-align: center;
		}
		.table-text-top td
		{
			color: #000;
			font-weight:bold;
			font-size:12px;
		}
		.td-images
		{
			vertical-align: bottom !important; 
			padding: 0; 
			width:50%;
			text-align: center;
		}
		.td-images img
		{
			max-width : 327px;
			max-height: 250px;
		}
		.td-images p
		{
			text-align: left;
		}
		.td-signature
		{
			width: 50%;
		}
		.td-signature p
		{
			text-align: center; 
			font-size: 16px; 
			margin:0;
		}
		.request-info-cell
		{
			border			: 1px solid;
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info-cell tbody td
		{
			border			: 1px solid;
			padding			: 10px 3px 10px 3px;
		}
		.request-info-cell tr th
		{
			font-size		: 12px;
			border			: 1px solid;
			padding			: 10px 0 10px 5px;
		}
		/* Empiezan clases de tablas con estructuras de subtablas */
		.multiple-tables
		{			
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.multiple-tables tbody td
		{
			padding			: 10px 3px 10px 3px;
		}
		.multiple-tables tr th
		{
			font-size		: 12px;
			padding			: 10px 0 10px 5px;
		}
		.cell-border
		{
			border			: 1px solid;
		}
		/*  */
		.page-break
		{
			page-break-after: always;
		}
	</style>
</head>
<body>
	<header>
		<table class="header">
			<tbody>
				<tr>
					<td class="logo" style="text-align: left; vertical-align: top;">
						<img src="{{ url('images/proyecta2.png') }}" style="width: 157px; height:55px;">
					</td>
					<td class="logo" style="text-align: right; vertical-align: bottom;">
						<img src="{{ url('images/pti.jpg') }}" style=" width: 193px; height:40px;margin-bottom: -5px;">
					</td>
				</tr>
			</tbody>
		</table>
		<div class="title-header" style="margin-top: 1em;">
			<b>Reporte Diario de Actividades</b>
		</div>
	</header>
	<main>
		<div class="pdf-full">
			<div class="pdf-body">
				<div class="block-info">
					<table class="request-info-text">
						<tbody class="table-text-top">
							<tr>
								<td style="width:60%;">
									<p style="text-align: justify;line-height: 20px;">
										Obra: Ejecución De La Ingeniería Complementaria, Procura Y
										Construcción De Instalaciones De Los Racks De Tubería De
										Integración De Libramiento Provisional, El Camino De Tránsito Pesado,
										Las Cimentaciones Superficiales, El Sistema De Contraincendios Del
										Paquete 6, Durante La Fase II B De La Refinería En Dos Bocas, Paraíso,
										Tabasco.
									</p>
									@php
										$code_wbs = explode(" ", $requests->wbs->code_wbs);
										$desc_wbs = '';
										for($i=1; $i < count($code_wbs); $i++)
										{
											$desc_wbs = $desc_wbs.' '.$code_wbs[$i];
										}
									@endphp
									<p> Frente: {{ $requests->wbs->code }} - {{ $desc_wbs }} </p>
								</td>
								<td style="text-align: right; width:40%; vertical-align:top;">
									@php	
										$time	= new DateTime($requests->date);
										$date	= $time->format('Y-m-d');
									@endphp
									<p> Fecha: {{ $date }} </p>
									<p> No. De Reporte: {{ $requests->noReport() }} </p>
								</td>
							</tr>
							<tr>
								<td style="width:60%;">
									<p> Disciplina: {{ mb_strtoupper($requests->discipline->name) }} </p>
									@if (isset($requests->tm_client_hours_from) && $requests->tm_client_hours_from != "" && isset($requests->tm_client_hours_to) && $requests->tm_client_hours_to != "") <p> T.M.C.: De {{ $requests->tm_client_hours_from }} a {{ $requests->tm_client_hours_to }} </p> @endif
									@if (isset($requests->tm_client_id) && $requests->tm_client_id != "") <p> Cat. T.M.C.: {{ mb_strtoupper($requests->catTMC->name) }}</p> @endif
								</td>
								<td style="text-align: right; width:40%;">
									<p> Cond. Climatológica: {{ mb_strtoupper($requests->weather->name) }} </p>
									<p style="font-weight:normal;"> Horario: {{ $requests->work_hours_from }} a {{ $requests->work_hours_to }} </p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<br>

				<div class="block-info">
					<table class="request-info-cell">
						<thead class="request-info">
							<tr>
								<th class="pdf-table-left-header" style="width:auto;"><b>#</b></th>
								<th class="pdf-table-left-header" style="width:8%;"><b>Partida</b></th>
								<th class="pdf-table-left-header" style="width:40%;"><b>Actividades</b></th>
								<th class="pdf-table-left-header"><b>Cant.</b></th>
								<th class="pdf-table-left-header"><b>Ud.</b></th>
								<th class="pdf-table-left-header"><b>Monto</b></th>
								<th class="pdf-table-left-header"><b>Área</b></th>
								<th class="pdf-table-left-header" style="width:28%;"><b>Plano</b></th>
							</tr>
						</thead>
						<tbody>
							@foreach($requests->pcdrDetails as $k => $pcdrDetail)
								<tr>
									<td style="width:auto;">{{ $k+1 }}</td>
									<td style="width:8%;">{{ $pcdrDetail->contract->contract_item }}</td>
									<td style="width:40%;">{{ $pcdrDetail->contract->activity }}</td>
									<td>{{ $pcdrDetail->quantity }}</td>
									<td>{{ $pcdrDetail->contract->unit }}</td>
									<td>{{ $pcdrDetail->amount }}</td>
									<td>{{ $pcdrDetail->area }}</td>
									<td style="width:28%;">{{ $pcdrDetail->blueprint->name }}</td>
								</tr>
							@endforeach
							<tr>
								<td>N:</td>
								<td colspan="7">{{ $requests->comments }}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="page-break"></div>
				
				<div class="block-info">
					<table class="multiple-tables">
						<thead>
							<tr>
								<th colspan="2" class="pdf-table-center-header cell-border" style="width: 49%;"><b>Maquinaria, Equipo y Herramienta</b></th>
								<th></th>
								<th colspan="4" class="pdf-table-center-header cell-border" style="width: 49%;"><b>Personal</b></th>
							</tr>
							<tr>
								<th class="pdf-table-left-header cell-border" style="width:40px;background:none;"><b>Cant.</b></th>
								<th class="pdf-table-left-header cell-border" style="width:290px;background:none;"><b>Descripción</b></th>
								<th></th>
								<th class="pdf-table-left-header cell-border" style="width:40px;background:none;"><b>Cant.</b></th>
								<th class="pdf-table-left-header cell-border" style="width:285px;background:none;" colspan="2"><b>Descripción</b></th>
								<th class="cell-border" style="width:45px;background:none;text-align:center;padding:0;"><b>Horas</b></th>
							</tr>
						</thead>
						<tbody>
							@php
								$totalMEH 		= $requests->pcdrMEH->count();
								$totalStaff 	= $requests->pcdrStaff->count();
								$mayor 			= $totalMEH > $totalStaff ? $totalMEH+1 : $totalStaff+1;
								$flagTotalHours = true;
								$flagNoTotal 	= true;
								$totalHours 	= 0;
							@endphp
							@for ($i=0;$i<$mayor;$i++)
								<tr>
									@if (isset($requests->pcdrMEH[$i]) && $requests->pcdrMEH[$i] != "")
										<td class="cell-border">
											{{ $requests->pcdrMEH[$i]->quantity }}
										</td>
										<td class="cell-border">
											{{ $requests->pcdrMEH[$i]->machineries->name }}
										</td>
									@else
										<td colspan="2">
									</td>
									@endif
									<td></td>
									@if (isset($requests->pcdrStaff[$i]))
										@php
											$totalHours = $totalHours+$requests->pcdrStaff[$i]->hours;
										@endphp
										<td class="cell-border">
											{{ $requests->pcdrStaff[$i]->quantity }}
										</td>
										<td class="cell-border" colspan="2">
											{{ $requests->pcdrStaff[$i]->staffIndustry->name }}
										</td>
										<td class="cell-border" style="text-align: center;">
											{{ $requests->pcdrStaff[$i]->hours }}
										</td>
									@else
										@if ($flagTotalHours)
											@php
												$flagTotalHours = false;
											@endphp
											<td colspan="2"></td>
											<td class="cell-border" style="text-align: center;"> Total de Horas </td>
											<td class="cell-border" style="text-align: center;"> {{ $totalHours }} </td>
										@else
											<td colspan="4"></td>
										@endif
									@endif
								</tr>
							@endfor
						</tbody>
					</table>
				</div>
				<div class="page-break"></div>

				<div class="block-info">
					<table class="request-info-cell">
						<thead class="request-info">
							<tr style="background: #c6c6c6;">
								<th colspan="2" class="pdf-table-center-header"><b>Fotografías</b></th>
							</tr>
						</thead>
						<tbody>
								@php
									$totalTD = [];
									$count	 = 1;
									foreach ($requests->pcdrDetails as $pcdrDetail) 
									{
										foreach ($pcdrDetail->pcdrDocuments->where('kind','ADJ_IMAGEN') as $images) 
										{
											$totalTD [] = $images->path;
										}
									}
								@endphp
								@for ($i = 0; $i < count($totalTD); $i++)
									<tr>
										<td class="td-images">
											@php
												$file_exists = Storage::disk('public')->exists('/docs/daily_report_operations/'.$totalTD[$i]);
											@endphp
											@if ($file_exists > 0)
												<p style="text-align: center;"><img src="{{ url('/docs/daily_report_operations/'.$totalTD[$i]) }}"></p>
											@else
												<p>Recurso no encontrado</p>
											@endif
											<p style="padding-left: 5px;">({{ $count }}) - {{ $pcdrDetail->contract->contract_item }} - {{ $pcdrDetail->contract->activity }}</p>
											@php
												$count++;
											@endphp
										</td>
										@if (isset($totalTD[$i+1]) && $totalTD[$i+1] != '')
											<td class="td-images">
												@php
													$file_exists = Storage::disk('public')->exists('/docs/daily_report_operations/'.$totalTD[$i+1]);													
												@endphp
												@if ($file_exists > 0)
													<p style="text-align: center;"><img src="{{ url('/docs/daily_report_operations/'.$totalTD[$i+1]) }}"></p>
												@else
													<p>Recurso no encontrado</p>
												@endif
												<p style="padding-left: 5px;">({{ $count }}) - {{ $pcdrDetail->contract->contract_item }} - {{ $pcdrDetail->contract->activity }}</p>
												@php												
													$count++;
													$i++;
												@endphp
											</td>
										@else
											<td class="td-images">
											</td>
										@endif
									</tr>
								@endfor
						</tbody>
					</table>
				</div>

				<div class="block-info">
					<table class="request-info-cell" style="border: none;">
						<tbody>
							@php
								$names 		= [];
								$position 	= [];
								foreach($requests->pcdrSignatures as $pcdrSignature)
								{
									$names [] 		= $pcdrSignature->name;
									$position [] 	= $pcdrSignature->position;
								}	
							@endphp
							@for ($i = 0; $i < count($names); $i++)
								<tr>
									<td class="td-signature" style="border: none;">
										<p style="border-bottom: 1px solid #000;margin-top: 150px;">{{ $names[$i] }}</p>
										<p>{{ $position[$i] }}</p>
									</td>
									@if (isset($names[$i+1]) && $position[$i+1] != '')
										<td class="td-signature" style="border: none;">
											<p style="border-bottom: 1px solid #000;margin-top: 150px;">{{ $names[$i+1] }}</p>
											<p>{{ $position[$i+1] }}</p>
										</td>
										@php
											$i++;
										@endphp
									@else
										<td class="td-signature" style="border: none;">
										</td>
									@endif
								</tr>
							@endfor
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</main>
	<script type="text/php">
        if (isset($pdf))
        {
			$text = "Hoja {PAGE_NUM} de {PAGE_COUNT}";
			$size = 8;
			$font = $fontMetrics->getFont("Arial", "normal");
			$width = $fontMetrics->get_text_width($text, $font, $size) / 2;
			$x = 270;
			$y = $pdf->get_height() - 35;
			$pdf->page_text($x, $y, $text, $font, $size);
		}
    </script>
</body>
</html>