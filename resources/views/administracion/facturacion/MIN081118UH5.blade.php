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
		}
		.table
		{
			border-collapse: collapse !important;
		}
		.table-bordered th,
		.table-bordered td
		{
			border: 1px solid #ddd !important;
		}
		table
		{
			background-color: transparent;
			max-width: 100%;
		}
		.table {
			width			: 100%;
			max-width		: 100%;
			margin-bottom	: 20px;
		}
		.table > thead > tr > th,
		.table > tbody > tr > th,
		.table > tfoot > tr > th,
		.table > thead > tr > td,
		.table > tbody > tr > td,
		.table > tfoot > tr > td {
			padding			: 8px;
			line-height		: 1.42857143;
			border-top		: 1px solid #ddd;
		}
		.table > thead > tr > th {
			border-bottom	: 2px solid #ddd;
		}
		.table > caption + thead > tr:first-child > th,
		.table > colgroup + thead > tr:first-child > th,
		.table > thead:first-child > tr:first-child > th,
		.table > caption + thead > tr:first-child > td,
		.table > colgroup + thead > tr:first-child > td,
		.table > thead:first-child > tr:first-child > td {
			border-top	: 0;
		}
		.table > tbody + tbody {
			border-top	: 2px solid #ddd;
		}
		.table > caption + thead > tr:first-child > th,
		.table > colgroup + thead > tr:first-child > th,
		.table > thead:first-child > tr:first-child > th,
		.table > caption + thead > tr:first-child > td,
		.table > colgroup + thead > tr:first-child > td,
		.table > thead:first-child > tr:first-child > td {
			border-top	: 0;
		}
		.table > tbody + tbody {
			border-top	: 2px solid #ddd;
		}
		.table .table {
			background-color	: #fff;
		}
		.table-condensed > thead > tr > th,
		.table-condensed > tbody > tr > th,
		.table-condensed > tfoot > tr > th,
		.table-condensed > thead > tr > td,
		.table-condensed > tbody > tr > td,
		.table-condensed > tfoot > tr > td {
			padding	: 5px;
		}
		.table-bordered {
			border	: 1px solid #ddd;
		}
		.table-bordered > thead > tr > th,
		.table-bordered > tbody > tr > th,
		.table-bordered > tfoot > tr > th,
		.table-bordered > thead > tr > td,
		.table-bordered > tbody > tr > td,
		.table-bordered > tfoot > tr > td {
			border	: 1px solid #ddd;
		}
		.table-bordered > thead > tr > th,
		.table-bordered > thead > tr > td {
			border-bottom-width	: 2px;
		}
		.table-striped > tbody > tr:nth-of-type(odd) {
			background-color	: #f9f9f9;
		}
		table col[class*="col-"] {
			position	: static;
			display		: table-column;
			float		: none;
		}
		table td[class*="col-"],
		table th[class*="col-"] {
			position	: static;
			display		: table-cell;
			float		: none;
		}
		@page {
			margin	: 4em 0 0 0 !important;
        }
		body
		{
			background	: white;
			font-size	: 12px;
			position	: relative !important;
		}
		.logo
		{
			margin-bottom	: .1rem;
			min-width		: 100%;
			width			: 100%;
		}
		.request-info
		{
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
			font-family		: 'Baskerville' !important;
			font-weight		: 600;
			padding			: 0.1rem;
		}

		.request-info tbody th span.normal
		{
			font-weight	: 300;
		}
		.thead-dark tr th,
		.thead-dark tr td
		{
			font-family	: 'Baskerville' !important;
		}
		.request-info tbody th span.text-left
		{
			font-family	: 'Baskerville' !important;
			font-weight	: bolder;
		}
		.request-info tbody tr.no-border th
		{
			border: none;
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
			padding	: .1rem !important;
		}
		thead tr th,
		.main-header th
		{
			border-bottom	: 0 !important;
			background		: #3274b5 !important;
			color			: white !important;
		}
		.mainware-taxes thead tr th
		{
			background	: none !important;
			color		: black !important;
		}
		.pdf-full
		{
			width	: 780px;
		}
		.text-break
		{
			font-size	: 8px;
			max-width	: 550px;
			width		: 550px;
			word-break	: break-all !important;
			word-wrap	: break-word !important;
		}
		.table
		{
			text-align: center;
			vertical-align: middle;
		}
		.mainware-data tr th,
		.mainware-data tr td,
		.main-footer tbody tr th,
		.main-footer tbody tr td
		{
			border-bottom: 1px dotted #000;
		}
		.table td.mainware-taxes-box
		{
			padding	: 0 !important;
		}
		hr
		{
			background	: #000;
			border		: 0;
			height		: 1px;
			width		: 100%;
		}
		.table-borderless td,
		.table-borderless th
		{
			border: 0!important;
		}
	</style>
</head>
<body>
	<main>
		<div class="pdf-full">
			<div class="pdf-body">
				<div class="block-info">
					<table class="centered-table">
						<tbody>
							<tr>
								<td width="30%">
									@php
										$path	= public_path('/images/enterprise/'.\App\Enterprise::where('rfc',$bill->rfc)->first()->path);
										$type	= pathinfo($path, PATHINFO_EXTENSION);
										$data	= file_get_contents($path);
										$base64	= 'data:image/' . $type . ';base64,' . base64_encode($data);
									@endphp
									<img src="{{ $base64 }}" width="200">
									<p><br></p>
								</td>
								<td width="70%">
									<table class="table table-bordered main-header" style="width: 80%;margin: 0 0 0 auto;">
										<tbody>
											<tr>
												<th>RFC emisor:</th>
												<td>{{$bill->rfc}}</td>
											</tr>
											<tr>
												<th>Nombre emisor:</th>
												<td>{{$bill->businessName}}</td>
											</tr>
											<tr>
												<th>RFC receptor:</th>
												<td>{{$bill->clientRfc}}</td>
											</tr>
											<tr>
												<th>Nombre receptor:</th>
												<td>{{$bill->clientBusinessName}}</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<div class="block-info">
					<table class="request-info no-border centered-table mainware-data">
						<tbody>
							@if($bill->related != null)
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">Tipo de relaci??n: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">{{App\CatRelation::where('typeRelation',$bill->related)->first()->description}}</span>
									</td>
								</tr>
							@endif
							<tr>
								<th @if($bill->type == 'N') colspan="2" @endif>
									<span class="text-left">Uso CFDI: </span>
								</th>
								<td @if($bill->type == 'N') colspan="2" @endif>
									<span class="normal">{{$bill->cfdiUse->description}}</span>
								</td>
							</tr>
							<tr>
								<th @if($bill->type == 'N') colspan="2" @endif>
									<span class="text-left">Efecto de comprobante: </span>
								</th>
								<td @if($bill->type == 'N') colspan="2" @endif>
									<span class="normal">{{$bill->cfdiType->description}}</span>
								</td>
							</tr>
							@if($bill->related != null)
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">Folio fiscal a relacionar: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">
											@foreach($bill->cfdiRelated as $rel)
												{{$rel->cfdi->uuid}}<br>
											@endforeach
										</span>
									</td>
								</tr>
							@endif
							@if($bill->uuid != '')
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">Folio fiscal: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">{{$bill->uuid}}</span>
									</td>
								</tr>
							@endif
							@if($bill->noCertificate != '')
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">No. de serie del CSD: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">{{$bill->noCertificate}}</span>
									</td>
								</tr>
							@endif
							@if($bill->satCertificateNo != '')
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">No. de serie del SAT: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">{{$bill->satCertificateNo}}</span>
									</td>
								</tr>
							@endif
							<tr>
								<th @if($bill->type == 'N') colspan="2" @endif>
									<span class="text-left">C??digo postal, fecha y hora de emisi??n: </span>
								</th>
								<td @if($bill->type == 'N') colspan="2" @endif>
									<span class="normal">{{$bill->postalCode}}, {{$bill->expeditionDateCFDI}}</span>
								</td>
							</tr>
							@if($bill->stampDate != '')
								<tr>
									<th @if($bill->type == 'N') colspan="2" @endif>
										<span class="text-left">Fecha y hora de certificaci??n: </span>
									</th>
									<td @if($bill->type == 'N') colspan="2" @endif>
										<span class="normal">{{$bill->stampDate}}</span>
									</td>
								</tr>
							@endif
							@if($bill->type == 'N')
								<tr>
									<th colspan="4" align="center"><b style="font-weight: 600">Datos complementarios del receptor</b></th>
								</tr>
								<tr>
									<td class="text-left">CURP:</td>
									<td class="normal">{{$bill->nominaReceiver->curp}}</td>
									<td class="text-left">Salario diario integrado</td>
									<td class="normal">{{$bill->nominaReceiver->sdi}}</td>
								</tr>
								<tr>
									<td class="text-left">Tipo contrato:</td>
									<td class="normal" colspan="3">{{$bill->nominaReceiver->nominaContract->description}}</td>
								</tr>
								<tr>
									<td class="text-left">NSS</td>
									<td class="normal">{{$bill->nominaReceiver->nss}}</td>
									<td class="text-left">Fecha de inicio de relaci??n laboral</td>
									<td class="normal">{{$bill->nominaReceiver->laboralDateStart}}</td>
								</tr>
								<tr>
									<td class="text-left">Antig??edad</td>
									<td class="normal">{{$bill->nominaReceiver->antiquity}}</td>
									<td class="text-left">Riesgo de puesto</td>
									<td class="normal">{{$bill->nominaReceiver->nominaPositionRisk->description}}</td>
								</tr>
								<tr>
									<td class="text-left">Tipo r??gimen</td>
									<td class="normal">{{$bill->nominaReceiver->nominaRegime->description}}</td>
									<td class="text-left">N??mero de empleado</td>
									<td class="normal">{{$bill->nominaReceiver->employee_id}}</td>
								</tr>
								<tr>
									<td class="text-left">Periodicidad del pago</td>
									<td class="normal">{{$bill->nominaReceiver->nominaPeriodicity->description}}</td>
									<td class="text-left">Clave entidad federativa</td>
									<td class="normal">{{$bill->nominaReceiver->c_state}}</td>
								</tr>
							@endif
						</tbody>
					</table>
				</div>
				<p><br></p>
				@if($bill->type != 'N')
				<div class="block-info">
					<table class="request-info centered-table no-border">
						<tbody>
							@foreach($bill->billDetail as $d)
								<tr>
									<td>
										<table class="table table-bordered">
											<thead>
												<tr>
													<th>Clave del producto/servicio</th>
													<th>Cantidad</th>
													<th>Clave de unidad</th>
													<th>Valor unitario</th>
													<th>Importe</th>
													<th>Descuento</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>{{$d->keyProdServ}}</td>
													<td>{{$d->quantity}}</td>
													<td>{{$d->keyUnit}}</td>
													<td>{{number_format($d->value,2)}}</td>
													<td>{{number_format($d->amount,2)}}</td>
													<td>{{number_format($d->discount,2)}}</td>
												</tr>
												<tr>
													<td colspan="3" class="align-middle">{{$d->description}}</td>
													<td colspan="3" class="mainware-taxes-box">
														@if($d->taxesTras->count()>0)
															<table class="table table-bordered mainware-taxes">
																<thead>
																	<tr>
																		<th colspan="4">Traslados</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach($d->taxesTras as $t)
																		<tr>
																			<td>{{$t->cfdiTax->description}}</td>
																			<td>{{$t->quota}}</td>
																			<td>{{$t->quotaValue}}</td>
																			<td>{{number_format($t->amount,2)}}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														@endif
														@if($d->taxesRet->count()>0)
															<table class="table table-bordered mainware-taxes">
																<thead>
																	<tr>
																		<th colspan="4">Retenciones</th>
																	</tr>
																</thead>
																<tbody>
																	@foreach($d->taxesRet as $r)
																		<tr>
																			<td>{{$r->cfdiTax->description}}</td>
																			<td>{{$r->quota}}</td>
																			<td>{{$r->quotaValue}}</td>
																			<td>{{number_format($r->amount,2)}}</td>
																		</tr>
																	@endforeach
																</tbody>
															</table>
														@endif
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<p><br></p>
				@endif
				@if($bill->type == 'N')
				<div class="block-info">
					@if($bill->nomina->nominaPerception->count()>0)
						<table class="table centered-table table-bordered">
							<thead>
								<tr>
									<th>Tipo de percepci??n</th>
									<th>Clave</th>
									<th>Concepto</th>
									<th>Importe gravado</th>
									<th>Importe exento</th>
								</tr>
							</thead>
							<tbody>
								@foreach($bill->nomina->nominaPerception as $per)
									<tr>
										<td>{{$per->type}} - {{$per->perception->description}}</td>
										<td>{{$per->perceptionKey}}</td>
										<td>{{$per->concept}}</td>
										<td>{{number_format($per->taxedAmount,2)}}</td>
										<td>{{number_format($per->exemptAmount,2)}}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						<p><br></p>
					@endif
					@if($bill->nomina->nominaDeduction->count()>0)
						<table class="table centered-table table-bordered">
							<thead>
								<tr>
									<th>Tipo de deducci??n</th>
									<th>Clave</th>
									<th>Concepto</th>
									<th>Importe</th>
								</tr>
							</thead>
							<tbody>
								@foreach($bill->nomina->nominaDeduction as $ded)
									<tr>
										<td>{{$ded->type}} - {{$ded->deduction->description}}</td>
										<td>{{$ded->deductionKey}}</td>
										<td>{{$ded->concept}}</td>
										<td>{{number_format($ded->amount,2)}}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						<p><br></p>
					@endif
					@if($bill->nomina->nominaOtherPayment->count()>0)
						<table class="table centered-table table-bordered">
							<thead>
								<tr>
									<th>Tipo otro pago</th>
									<th>Clave</th>
									<th>Concepto</th>
									<th>Importe</th>
								</tr>
							</thead>
							<tbody>
								@foreach($bill->nomina->nominaOtherPayment as $other)
									<tr>
										<td>{{$other->type}} - {{$other->otherPayment->description}}</td>
										<td>{{$other->otherPaymentKey}}</td>
										<td>{{$other->concept}}</td>
										<td>{{number_format($other->amount,2)}}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						<p><br></p>
					@endif
				</div>
				@endif
				<p><br></p>
				<div class="block-info">
					<table class="table centered-table table-bordered">
						<tbody>
							<tr>
								<td>
									@php
										$totalTemp   = explode('.',number_format($bill->total,2,'.',''));
										$formatterES = new NumberFormatter("es", NumberFormatter::SPELLOUT);
										$letter      = $formatterES->format($totalTemp[0]);
									@endphp
									{{ strtoupper(str_replace('??cientos','cientos',$letter)) }} @if($bill->currency == 'MXN') PESOS @elseif($bill->currency == 'USD') D??LARES @elseif($bill->currency != '') {{strtoupper($bill->cfdiCurrency->description)}} @endif {{ $totalTemp[1] }}/100 {{ $bill->currency }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<div class="block-info">
					<table class="request-info no-border centered-table">
						<tbody>
							<tr>
								<td width="50%">
									<table class="request-info main-footer">
										<tbody>
											<tr>
												<th align="left">Moneda</th>
												<td align="left">{{$bill->cfdiCurrency->description}}</td>
											</tr>
											@if($bill->exchange != '')
												<tr>
													<th align="left">Tipo de cambio</th>
													<td align="left">{{$bill->exchange}}</td>
												</tr>
											@endif
											@if($bill->paymentWay != null)
												<tr>
													<th align="left">Forma de pago</th>
													<td align="left">{{$bill->cfdiPaymentWay->description}}</td>
												</tr>
											@endif
											@if($bill->paymentMethod != null)
												<tr>
													<th align="left">M??todo de pago</th>
													<td align="left">{{$bill->cfdiPaymentMethod->description}}</td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
								<td>
									<table class="request-info main-footer">
										<tbody>
											<tr>
												<th align="left">Subtotal</th>
												<td align="right">$ {{number_format($bill->subtotal,2)}}</td>
											</tr>
											@if($bill->type != 'P')
												<tr>
													<th align="left">Descuento</th>
													<td align="right">$ {{number_format($bill->discount,2)}}</td>
												</tr>
											@endif
											@if($bill->type != 'P' && $bill->type != 'N')
												<tr>
													<th align="left">Total de impuestos trasladados</th>
													<td align="right">$ {{number_format($bill->tras,2)}}</td>
												</tr>
												<tr>
													<th align="left">Total de impuestos retenidos</th>
													<td align="right">$ {{number_format($bill->ret,2)}}</td>
												</tr>
											@endif
											<tr>
												<th align="left">Total</th>
												<td align="right">$ {{number_format($bill->total,2)}}</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				@if($bill->type == 'P')
					<div class="block-info">
						<table class="table table-borderless request-info no-border centered-table">
							<tbody>
								<tr>
									<th colspan="2">Informaci??n de pago</th>
								</tr>
								<tr>
									<td>
										<table class="table table-borderless request-info no-border centered-table">
											<tbody>
												<tr>
													<th align="left">Forma de pago</th>
													<td align="right">{{$bill->paymentComplement->first()->complementPaymentWay->description}}</td>
												</tr>
												<tr>
													<th align="left">Fecha de pago</th>
													<td align="right">{{$bill->paymentComplement->first()->paymentDate}}</td>
												</tr>
											</tbody>
										</table>
									</td>
									<td>
										<table class="table table-borderless request-info no-border centered-table">
											<tbody>
												<tr>
													<th align="left">Moneda de pago</th>
													<td align="right">{{$bill->paymentComplement->first()->complementCurrency->description}}</td>
												</tr>
												<tr>
													<th align="left">Monto</th>
													<td align="right">{{$bill->paymentComplement->first()->amount}}</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<p><br></p>
					<div class="block-info">
						<table class="table table-borderless request-info no-border centered-table">
							<tbody>
								<tr>
									<th colspan="2">Documento relacionado</th>
								</tr>
								@foreach($bill->cfdiRelated as $rel)
									<tr>
										<td>
											<table class="table table-borderless request-info no-border centered-table">
												<tbody>
													<tr>
														<th align="left">ID documento</th>
														<td>{{$rel->cfdi->uuid}}</td>
													</tr>
													<tr>
														<th align="left">N??mero parcialidad</th>
														<td align="right">{{$rel->partial}}</td>
													</tr>
												</tbody>
											</table>
										</td>
										<td>
											<table class="table table-borderless request-info no-border centered-table">
												<tbody>
													<tr>
														<th align="left">Moneda del documento relacionado</th>
														<td align="right">{{$rel->cfdi->cfdiCurrency->description}}</td>
													</tr>
													<tr>
														<th align="left">M??todo de pago del documento relacionado</th>
														<td align="right">{{$rel->cfdi->cfdiPaymentMethod->description}}</td>
													</tr>
													<tr>
														<th align="left">Importe de saldo anterior</th>
														<td align="right">{{$rel->prevBalance}}</td>
													</tr>
													<tr>
														<th align="left">Importe pagado</th>
														<td align="right">{{$rel->amount}}</td>
													</tr>
													<tr>
														<th align="left">Importe de saldo insoluto</th>
														<td align="right">{{$rel->unpaidBalance}}</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				@endif
				<p><br></p>
				@if($bill->status == 1)
					<div class="block-info">
						<table style="margin: auto;width: 600px;max-width: 600px;" class="table table-borderless">
							<tbody>
								<tr>
									<td>
										<table>
											<tbody>
												<tr>
													<td>Sello digital del CFDI</td>
												</tr>
												<tr>
													<td align="left">
														<div class="text-break">{{$bill->digitalStampCFDI}}</div>
													</td>
												</tr>
												<tr>
													<td>Sello digital del SAT</td>
												</tr>
												<tr>
													<td align="left">
														<div class="text-break">{{$bill->digitalStampSAT}}</div>
													</td>
												</tr>
												<tr>
													<td>Cadena original</td>
												</tr>
												<tr>
													<td align="left">
														<div class="text-break">{{$bill->originalChain}}</div>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
									<td>
										<img style="width: 100px" src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->errorCorrection('H')->margin(0)->generate('https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id='.$bill->uuid.'&re='.$bill->rfc.'&rr='.$bill->clientRfc.'&tt='.$bill->total.'&fe='.substr($bill->digitalStampCFDI,-8))) !!}">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</main>
</body>
</html>