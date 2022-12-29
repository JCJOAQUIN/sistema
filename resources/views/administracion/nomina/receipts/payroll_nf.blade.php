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
			font-family            : ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
			background             : white;
			font-size              : 12px;
			position               : relative !important;
			-webkit-font-smoothing : antialiased;
			-moz-osx-font-smoothing: grayscale;
		}
		.content
		{
			width: 90%;
			margin: 0.5rem auto 0;
		}
		.text-center
		{
			text-align: center;
		}
		.text-right
		{
			text-align: right;
		}
		.text-justify
		{
			text-align: justify;
		}
		.text-left
		{
			text-align: left;
		}
		.text-xs
		{
			font-size: 0.5rem;
			line-height: 0.6rem;
		}
		.text-lg
		{
			font-size  : 1.125rem;
			line-height: 1.75rem;
		}
		.w-full
		{
			width: 100%;
		}
		.w-50
		{
			width: 50%;
		}
		.vertical-bottom
		{
			vertical-align: bottom;
		}
		.pb-2
		{
			padding-bottom: 0.5rem;
		}
		.border
		{
			border-width: 1px;
			border-color: #000;
			border-style: solid;
		}
		.border-bottom
		{
			border-bottom-width: 1px;
			border-bottom-color: #000;
			border-bottom-style: solid;
		}
		table
		{
			border-spacing: 0;
			border-collapse: collapse;
		}
		.px-2
		{
			padding-left: 0.375rem;
			padding-right: 0.375rem;
		}
		.px-5
		{
			padding-left: 1.25rem;
			padding-right: 1.25rem;
		}
		.px-7
		{
			padding-left: 1.75rem;
			padding-right: 1.75rem;
		}
		.px-20
		{
			padding-left : 5rem;
			padding-right: 5rem;
		}
		.pb-2
		{
			padding-bottom: 0.5rem;
		}
		.pb-3
		{
			padding-bottom: 1rem;
		}
		.pb-12
		{
			padding-bottom: 3rem;
		}
		.w-7
		{
			width: 58.333333%;
		}
		.w-5
		{
			width: 41.666667%;
		}
		hr
		{
			height          : 1px;
			width           : 80%;
			margin          : 0 auto;
			border          : none;
			background-color: #000;
		}
	</style>
</head>
@php
	$employee  = $payment->nominaEmployee->employee->first();
	$nomina    = $payment->nominaEmployee->nomina;
	$nominaNF  = $payment->nominaEmployee->nominasEmployeeNF->first();
	$to_date   = new Carbon\Carbon($nomina->to_date);
	$from_date = new Carbon\Carbon($nomina->from_date);
	$from_date->subDays(1);
	$paymentDays = $to_date->diffInDays($from_date);
	$paymentDay  = new Carbon\Carbon($payment->paymentDate);
	$amount      = $nominaNF->amount;
	$discounts   = $nominaNF->discounts->sum('amount');
	$extras      = $nominaNF->extras->sum('amount');
	$months      = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
	$from_date->addDays(1);
	$formatterES = new NumberFormatter("es-ES", NumberFormatter::SPELLOUT);
	$leftAmount   = intval(floor($amount));
	$rightAmount     = intval(round($amount - floor($amount),2) * 100);
@endphp
<body>
	<div class="pb-12 px-20">
		<table class="w-full">
			<thead>
				<tr>
					<th class="w-50 text-left"><u>NÓMINA CORRESPONDIENTE<br>AL COMPLEMENTO, SEMANA {{$from_date->weekOfYear}}<br>{{$from_date->year}}</u></th>
					<th class="w-50 text-right text-lg">RECIBO DE PAGO</th>
				</tr>
			</thead>
		</table>
		<table class="w-full">
			<tbody>
				<tr>
					<td class="w-50">
						<b>{{$employee->last_name}} {{$employee->scnd_last_name}} {{$employee->name}}</b><br>
						DIRECCIÓN: {{$employee->street}} No. {{$employee->number}}, Col. {{$employee->colony}}<br>
						{{$employee->city}}, {{$employee->states()->exists() ? $employee->states->description : '' }}, CP. {{$employee->cp}}<br>
						CURP: {{$employee->curp}}<br>
						NSS: {{$employee->imss}}
					</td>
					<td class="w-50">
						<table class="w-full">
							<tbody>
								<tr>
									<td class="w-50 vertical-bottom">@if($nomina->from_date != '' && $nomina->to_date != '')Días pagados <b>{{$paymentDays}}@endif</b></td>
									<td class="w-50">
										<table class="w-100">
											<tbody>
												<tr>
													<td class="text-center">Fecha de pago</td>
												</tr>
												<tr>
													<td class="text-center pb-2"><b>{{$paymentDay->format('d/')}}{{$months[$paymentDay->format('n')]}}{{$paymentDay->format('/Y')}}</b></td>
												</tr>
												@if($nomina->from_date != '' && $nomina->to_date != '')
													<tr>
														<td class="text-center border">Periodo de pago</td>
													</tr>
													<tr>
														<td class="border px-2">
															Del <b>{{$from_date->format('d')}} de {{$months[$from_date->format('n')]}} {{$from_date->format('Y')}}</b><br>
															Al <b>{{$to_date->format('d')}} de {{$months[$to_date->format('n')]}} {{$to_date->format('Y')}}</b>
														</td>
													</tr>
												@endif
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2">Forma de pago <b>@if($nominaNF->idpaymentMethod == 1)Transferencia @else{{$nominaNF->paymentMethod->method}}@endif</b></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="content">
			<table class="w-full">
				<tbody>
					<tr>
						<td class="border-bottom px-7">Concepto</td>
						<td class="border-bottom px-7 text-right">Percepción</td>
						<td class="border-bottom px-7 text-right">Deducción</td>
						<td class="border-bottom px-7 text-right">Importe</td>
					</tr>
					<tr>
						<td class="border-bottom px-5">{{$nomina->typePayroll()->exists() ? $nomina->typePayroll->description : '' }}</td>
						<td class="border-bottom text-right px-5">$ {{number_format(round($amount + $discounts - $extras,2),2)}}</td>
						<td class="border-bottom px-5"></td>
						<td></td>
					</tr>
					@foreach($nominaNF->extras as $e)
						<tr>
							<td class="border-bottom px-5">{{$e->reason}}</td>
							<td class="border-bottom text-right px-5">$ {{number_format($e->amount,2)}}</td>
							<td class="border-bottom px-5"></td>
							<td></td>
						</tr>
					@endforeach
					@foreach($nominaNF->discounts as $d)
						<tr>
							<td class="border-bottom px-5">{{$d->reason}}</td>
							<td class="border-bottom px-5"></td>
							<td class="border-bottom text-right px-5">$ {{number_format($d->amount,2)}}</td>
							<td></td>
						</tr>
					@endforeach
					<tr>
						<td colspan="3" class="text-right"><b>TOTAL, NETO RECIBIDO</b></td>
						<td class="text-right"><b>$ {{$amount}}</b></td>
					</tr>
				</tbody>
			</table>
		</div>
		<table class="w-full">
			<tbody>
				<tr>
					<td class="w-7 px-2 pb-2">
						***** {{mb_strtoupper($formatterES->format($leftAmount))}} PESOS {{str_pad($rightAmount,2,'0',STR_PAD_LEFT)}}/100 MN *****
					</td>
					<td class="w-5"></td>
				</tr>
				<tr>
					<td class="w-7 pb-3">
						<div class="border px-2 pb-2 text-justify text-xs">
							Recibí el total de percepciones del periodo señalado en este recibo, sin que se adeude cantidad alguna por concepto de salario ordinario, horas extras, séptimo día, días festivos u otras prestaciones a que tengo derecho según la ley. Doy a la vez mi conformidad a los descuentos efectuados tanto de carácter oficial como privado. Manifiesto que durante esta semana laboré dentro de una jornada de trabajo comprendida de  las 10:00 am a las 7:00 pm de lunes a viernes, gozando de una hora diaria para reposar y tomar mis alimentos fuera del centro de trabajo.
						</div>
					</td>
					<td class="w-5 text-center vertical-bottom">
						<hr>
						<span><b>&nbsp;&nbsp;{{$employee->name}} {{$employee->last_name}} {{$employee->scnd_last_name}}&nbsp;&nbsp;</b></span>
						<br><b>FIRMA</b>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="px-20">
		<table class="w-full">
			<thead>
				<tr>
					<th class="w-50 text-left"><u>NÓMINA CORRESPONDIENTE<br>AL COMPLEMENTO, SEMANA {{$from_date->weekOfYear}}<br>{{$from_date->year}}</u></th>
					<th class="w-50 text-right text-lg">RECIBO DE PAGO</th>
				</tr>
			</thead>
		</table>
		<table class="w-full">
			<tbody>
				<tr>
					<td class="w-50">
						<b>{{$employee->last_name}} {{$employee->scnd_last_name}} {{$employee->name}}</b><br>
						DIRECCIÓN: {{$employee->street}} No. {{$employee->number}}, Col. {{$employee->colony}}<br>
						{{$employee->city}}, {{$employee->states->description}}, CP. {{$employee->cp}}<br>
						CURP: {{$employee->curp}}<br>
						NSS: {{$employee->imss}}
					</td>
					<td class="w-50">
						<table class="w-full">
							<tbody>
								<tr>
									<td class="w-50 vertical-bottom">@if($nomina->from_date != '' && $nomina->to_date != '')Días pagados <b>{{$paymentDays}}@endif</b></td>
									<td class="w-50">
										<table class="w-100">
											<tbody>
												<tr>
													<td class="text-center">Fecha de pago</td>
												</tr>
												<tr>
													<td class="text-center pb-2"><b>{{$paymentDay->format('d/')}}{{$months[$paymentDay->format('n')]}}{{$paymentDay->format('/Y')}}</b></td>
												</tr>
												@if($nomina->from_date != '' && $nomina->to_date != '')
													<tr>
														<td class="text-center border">Periodo de pago</td>
													</tr>
													<tr>
														<td class="border px-2">
															Del <b>{{$from_date->format('d')}} de {{$months[$from_date->format('n')]}} {{$from_date->format('Y')}}</b><br>
															Al <b>{{$to_date->format('d')}} de {{$months[$to_date->format('n')]}} {{$to_date->format('Y')}}</b>
														</td>
													</tr>
												@endif
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2">Forma de pago <b>@if($nominaNF->idpaymentMethod == 1)Transferencia @else{{$nominaNF->paymentMethod->method}}@endif</b></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="content">
			<table class="w-full">
				<tbody>
					<tr>
						<td class="border-bottom px-7">Concepto</td>
						<td class="border-bottom px-7 text-right">Percepción</td>
						<td class="border-bottom px-7 text-right">Deducción</td>
						<td class="border-bottom px-7 text-right">Importe</td>
					</tr>
					<tr>
						<td class="border-bottom px-5">{{$nomina->typePayroll->description}}</td>
						<td class="border-bottom text-right px-5">$ {{number_format(round($amount + $discounts - $extras,2),2)}}</td>
						<td class="border-bottom px-5"></td>
						<td></td>
					</tr>
					@foreach($nominaNF->extras as $e)
						<tr>
							<td class="border-bottom px-5">{{$e->reason}}</td>
							<td class="border-bottom text-right px-5">$ {{number_format($e->amount,2)}}</td>
							<td class="border-bottom px-5"></td>
							<td></td>
						</tr>
					@endforeach
					@foreach($nominaNF->discounts as $d)
						<tr>
							<td class="border-bottom px-5">{{$d->reason}}</td>
							<td class="border-bottom px-5"></td>
							<td class="border-bottom text-right px-5">$ {{number_format($d->amount,2)}}</td>
							<td></td>
						</tr>
					@endforeach
					<tr>
						<td colspan="3" class="text-right"><b>TOTAL, NETO RECIBIDO</b></td>
						<td class="text-right"><b>$ {{$amount}}</b></td>
					</tr>
				</tbody>
			</table>
		</div>
		<table class="w-full">
			<tbody>
				<tr>
					<td class="w-7 px-2 pb-2">
						***** {{mb_strtoupper($formatterES->format($leftAmount))}} PESOS {{str_pad($rightAmount,2,'0',STR_PAD_LEFT)}}/100 MN *****
					</td>
					<td class="w-5"></td>
				</tr>
				<tr>
					<td class="w-7 pb-3">
						<div class="border px-2 pb-2 text-justify text-xs">
							Recibí el total de percepciones del periodo señalado en este recibo, sin que se adeude cantidad alguna por concepto de salario ordinario, horas extras, séptimo día, días festivos u otras prestaciones a que tengo derecho según la ley. Doy a la vez mi conformidad a los descuentos efectuados tanto de carácter oficial como privado. Manifiesto que durante esta semana laboré dentro de una jornada de trabajo comprendida de  las 10:00 am a las 7:00 pm de lunes a viernes, gozando de una hora diaria para reposar y tomar mis alimentos fuera del centro de trabajo.
						</div>
					</td>
					<td class="w-5"></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>