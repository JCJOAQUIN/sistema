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
		.text-center
		{
			text-align: center;
		}
		.page-break 
		{
			page-break-after: always;
		}
		.order-table
		{
			border-collapse: collapse;
			border-spacing : 0;
			width: 95%;
			margin: 0 auto;
		}
		.order-table *
		{
			padding: 0;
		}
		.order-table thead
		{
			border: 1px solid #000;
			background-color: #EFEFEF;
		}
		.order-table thead tr th
		{
			padding    : 1em 0;
			font-weight: normal;
		}
		.description-table
		{
			border-collapse: collapse;
			border-spacing : 0;
			width: 95%;
			border-top: 2px solid #000;
			margin: 0;
		}
		.description-table tr:first-child td
		{
			padding-top: 5px;
		}
		.table-details-item.first td
		{
			border-top: 1px solid #000;
		}
		.table-details-item.last td
		{
			border-bottom: 21px solid transparent;
		}
	</style>
</head>
<body>
	@foreach ($projects as $p)
		<div>
			<header>
				<b>CONSORCIO IDINSA-PROYECTA</b>
				<p style="padding: 5px 0">
					{{$p->proyectName}}
					@if($p->contestNo != '')
						<br>{{$p->contestNo}}
					@endif
					@if($p->city != '')
						<br>{{$p->city}}
					@elseif($p->place != '')
						<br>{{$p->place}}
					@elseif($p->placeObra != '')
						<br>{{$p->placeObra}}
					@endif
				</p>
			</header>
			<main>
				<table class="order-table">
					<tbody>
						@foreach($requests->where('project_id',$p->idproyect) as $r)
							<tr>
								<td colspan="13">
									<table class="description-table">
										<tbody>
											<tr>
												<td width="33%">RFQ: {{ $r->numberOrder }}</td>
												<td width="33%">Description: {{ $r->descriptionShort }}</td>
												<td width="33%"></td>
											</tr>
											<tr>
												<td width="33%">Buyer: {{ $r->buyer }}</td>
												<td width="33%">Dur Table: </td>
												<td width="33%">RAS: {{ $r->date_obra != "" ? strtoupper($r->date_obra->format('dMy')) : '' }}</td>
											</tr>
											<tr>
												<td width="33%">Budget Value: {{ number_format($r->total_request,2) }} {{ $r->type_currency }}</td>
												<td width="33%">Status: {{ $r->statusRequest->description }}</td>
												<td width="33%">Engineer: {{ $r->engineer }}</td>
											</tr>
											<tr>
												<td colspan="3">
													<table>
														<tbody>
															<tr>
																<td style="border-bottom: 15px solid transparent;">
																	Remarks:
																</td>
																<td class="">
																	@foreach($r->remarks as $rem)
																		<p style="padding-bottom: 4px;">
																			{{strtoupper($rem->date->format('dMy'))}}:<br>
																			{{$rem->remark}}
																		</p>
																	@endforeach
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							@php
								$itemCount = 1;
							@endphp

							@if($r->milestones()->exists())
								<tr>
									<th style="background: #EFEFEF;" width="20%">Seq Num</th>
									<th style="background: #EFEFEF;" width="20%">Milestone</th>
									<th style="background: #EFEFEF;" width="20%">Schedule</th>
									<th style="background: #EFEFEF;" width="20%">Status</th>
									<th style="background: #EFEFEF;" width="20%">Complete</th>
								</tr>
							@endif
							@foreach ($r->milestones as $milestone)
								<tr class="table-details-item @if($loop->first) first @endif @if($loop->last) last @endif ">
									<td class="text-center">
										{{ $milestone->seq_num }}
									</td>
									<td class="text-center">
										{{ $milestone->milestone }}
									</td>
									<td class="text-center">
										{{ $milestone->schedule != "" ? strtoupper($milestone->schedule->format('dMy')) : '' }}
									</td>
									<td class="text-center">
										{{ $milestone->status }}
									</td>
									<td class="text-center">
										{{ $milestone->complete_status != "" ? strtoupper($milestone->complete_status->format('dMy')) : '' }}
									</td>
								</tr>
							@endforeach
						@endforeach
					</tbody>
				</table>
			</main>
		</div>
		@if(!$loop->last)
			<div class="page-break"></div>
		@endif
	@endforeach
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