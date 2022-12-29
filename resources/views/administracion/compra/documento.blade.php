@php
	$date = Carbon\Carbon::now();
	$taxes = 0;
@endphp
<!DOCTYPE html>
<html>
	<head>
		@if($request->purchases->first()->idRequisition != "")
			<style>
			.header
				{
					border-collapse	: separate;
					border-spacing	: 25px;
					width: 90%;
					margin			: 0 auto;
				}
			</style>
		@else
			<style>
				.header
				{
					border-collapse	: separate;
					border-spacing	: 25px;
					margin			: auto;
					padding			: 0;
				}
			</style>
		@endif
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
		</style>
	</head>
	<body>
		<header>
			<table class="header">
				<tbody>
					<tr>
						@if ($request->purchases->first()->idRequisition != "")
							<td class="logo">
								<label class="pdf-label">Folio: {{ $request->folio }} </label>
								<br>
								<label class="pdf-label">Fecha: {{ $date->format('d-m-Y')}}</label>
								<label class="pdf-label">Hora: {{ $date->toTimeString() }}</label>
							</td>
						@else
							<td class="logo"></td>
							<td class="date">
								<label class="pdf-label">Folio: {{ $request->folio }} </label>
								<br>
								<label class="pdf-label">Fecha: {{ $date->format('d-m-Y')}}</label>
							</td>
						@endif
					</tr>
				</tbody>
			</table>
		</header>
		<main>
			<div class="pdf-full">
				<div class="pdf-body">
					<div class="block-info">
						<center>A continuación podrá verificar la información de la Solicitud de Compra:</center>
						<br>
						<table class="request-info">
							<thead>
								<tr>
									<th colspan="2" class="pdf-table-center-header">Detalles de la Solicitud</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<th align="left">Título y fecha:</th>
									<th align="right">{{$request->purchases->first()->title }} - {{ $request->purchases->first()->datetitle }}</th>
								</tr>
								<tr>
									<th align="left">Fiscal:</th>
									<th align="right">@if($request->taxPayment == 1) Si @else No @endif</th>
								</tr>
								<tr>
									<th align="left">Solicitante:</th>
									<th align="right">
										@php
											$requestUser = App\User::find($request->idRequest);
										@endphp
										{{ $requestUser->name }} {{ $requestUser->last_name }} {{ $requestUser->scnd_last_name }}
									</th>
								</tr>
								<tr>
									<th align="left">Elaborado por:</th>
									<th align="right">
										@php
											$elaborateUser = App\User::find($request->idElaborate);
										@endphp
										{{ $elaborateUser->name }} {{ $elaborateUser->last_name }} {{ $elaborateUser->scnd_last_name }}
									</th>
								</tr>
								<tr>
									<th align="left">Empresa:</th>
									<th align="right">{{ App\Enterprise::find($request->idEnterprise)->name }}</th>
								</tr>
								<tr>
									<th align="left">Dirección:</th>
									<th align="right">{{ App\Area::find($request->idArea)->name }}</th>
								</tr>
								<tr>
									<th align="left">Departamento:</th>
									<th align="right">{{ App\Department::find($request->idDepartment)->name }}</th>
								</tr>
								<tr>
									<th align="left">Clasificación del gasto:</th>
									<th align="right">
										@php
											$requestAccount = App\Account::find($request->account);
										@endphp
										{{ $requestAccount->account }} - {{ $requestAccount->description }}
									</th>
								</tr>
								<tr class="no-border">
									<th align="left">Proyecto:</th>
									<th align="right">{{ isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto' }}</th>
								</tr>
							</tbody>
						</table>
					</div>
					<p><br></p>
					<div class="block-info">
						<center>
							<strong>DATOS DEL PROVEEDOR</strong>
						</center>
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
						<table class="employee-details centered-table">
							<tbody>
								<tr>
									<td><b>Razón Social:</b></td>
									<td><label>{{ $request->purchases->first()->provider->businessName }}</label></td>
								</tr>
								<tr>
									<td><b>RFC:</b></td>
									<td><label>{{ $request->purchases->first()->provider->rfc }}</label></td>
								</tr>
								<tr>
									<td><b>Teléfono:</b></td>
									<td><label>{{ $request->purchases->first()->provider->phone }}</label></td>
								</tr>
								<tr>
									<td><b>Calle:</b></td>
									<td><label>{{ $request->purchases->first()->provider->address }}</label></td>
								</tr>
								<tr>
									<td><b>Número:</b></td>
									<td><label>{{ $request->purchases->first()->provider->number }}</label></td>
								</tr>
								<tr>
									<td><b>Colonia:</b></td>
									<td><label>{{ $request->purchases->first()->provider->colony }}</label></td>
								</tr>
								<tr>
									<td><b>CP:</b></td>
									<td><label>{{ $request->purchases->first()->provider->postalCode }}</label></td>
								</tr>
								<tr>
									<td><b>Ciudad:</b></td>
									<td><label>{{ $request->purchases->first()->provider->city }}</label></td>
								</tr>
								<tr>
									<td><b>Estado:</b></td>
									<td><label>{{ App\State::find($request->purchases->first()->provider->state_idstate)->description }}</label></td>
								</tr>
								<tr>
									<td><b>Contacto:</b></td>
									<td><label>{{ $request->purchases->first()->provider->contact }}</label></td>
								</tr>
								<tr>
									<td><b>Beneficiario:</b></td>
									<td><label>{{ $request->purchases->first()->provider->beneficiary }}</label></td>
								</tr>
								<tr>
									<td><b>Otro:</b></td>
									<td><label>{{ $request->purchases->first()->provider->commentaries }}</label></td>
								</tr>
							</tbody>
						</table>
						<table class="centered-table bank-info">
							<thead>
								<tr>
									<th>Banco</th>
									<th>Cuenta</th>
									<th>Sucursal</th>
									<th>Referencia</th>
									<th>CLABE</th>
									<th>Moneda</th>
									<th>Convenio</th>
								</tr>
							</thead>
							<tbody>
								@foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
									@if($request->purchases->first()->provider_has_banks_id == $bank->id)
										<tr>
											<td>
												{{$bank->bank->description}}
											</td>
											<td>
												{{$bank->account}}
											</td>
											<td>
												{{$bank->branch}}
											</td>
											<td>
												{{$bank->reference}}
											</td>
											<td>
												{{$bank->clabe}}
											</td>
											<td>
												{{$bank->currency}}
											</td>
											<td>
												@if($bank->agreement=='')
													---
												@else
													{{$bank->agreement}}
												@endif
											</td>
										</tr>
									@endif
								@endforeach
							</tbody>
						</table>
					</div>
					<p><br></p>
					<div class="block-info">
						<center>
							<strong>DATOS DEL PEDIDO</strong>
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
									<th>Cantidad</th>
									<th>Descripci&oacute;n</th>
									<th>Precio Unitario</th>
									<th>IVA</th>
									<th>Descuento</th>
									<th>Importe</th>
								</tr>
							</thead>
							<tbody>
								@foreach($request->purchases->first()->detailPurchase as $detail)
									<tr>
										<td>{{ $detail->quantity }}</td>
										<td>{{ $detail->description }}</td>
										<td>$ {{ number_format($detail->unitPrice,2) }}</td>
										<td>$ {{ number_format($detail->tax,2) }}</td>
										<td>$ {{ number_format($detail->discount,2) }}</td>
										<td>$ {{ number_format($detail->amount,2) }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						<p>&nbsp;</p>
						<table class="centered-table bank-info total-details">
							<tbody>
								<tr>
									<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
									<td>Subtotal:</td>
									<td>$ {{ number_format($request->purchases->first()->subtotales,2,".",",") }}</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>Impuesto Adicional:</td>
									<td>
										@foreach($request->purchases->first()->detailPurchase as $detail)
											@foreach($detail->taxes as $tax)
												@php 
													$taxes += $tax->amount
												@endphp
											@endforeach
										@endforeach
										$ {{ number_format($taxes,2,".",",") }}
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>IVA:</td>
									<td>$ {{ number_format($request->purchases->first()->tax,2,".",",") }}</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>TOTAL:</td>
									<td>$ {{ number_format($request->purchases->first()->amount+$taxes,2,".",",") }}</td>
								</tr>
							</tbody>
						</table>
						<div class="pdf-notes"><label>Notas:</label> {{ $request->purchases->first()->notes }}</div>
					</div>
					<p><br></p>
					<div class="block-info">
						<center>
							<strong>CONDICIONES DE PAGO</strong>
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
						<table class="employee-details centered-table">
							<tbody>
								<tr>
									<td><b>Referencia/Número de factura:</b></td>
									<td><label>{{ $request->purchases->first()->reference }}</label></td>
								</tr>
								<tr>
									<td><b>Tipo de moneda:</b></td>
									<td><label>{{ $request->purchases->first()->typeCurrency }}</label></td>
								</tr>
								<tr>
									<td><b>Fecha de pago:</b></td>
									@php	
										$date = '';
										if ($request->PaymentDate!='') 
										{
											$date	= $request->PaymentDate->format('d-m-Y');
										}	
									@endphp
									<td><label>{{ $date }}</label></td>
								</tr>
								<tr>
									<td>
										<b>Forma de pago:</b>
									</td>
									<td>
										<label>{{ $request->purchases->first()->paymentMode }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Estado  de factura:</b>
									</td>
									<td>
										<label>{{ $request->purchases->first()->billStatus }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Importe a pagar:</b>
									</td>
									<td>
										<label>${{ number_format($request->purchases->first()->amount+$taxes,2) }}</label>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<p><br></p>
					<div class="block-info">
						<center>
							<strong>DATOS DE REVISIÓN</strong>
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
						<table class="employee-details centered-table">
							<tbody>
								<tr>
									<td>
										<b>Revisó:</b>
									</td>
									<td>
										<label>{{ $request->reviewedUser->name }} {{ $request->reviewedUser->last_name }} {{ $request->reviewedUser->scnd_last_name }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Nombre de la Empresa:</b>
									</td>
									<td>
										<label>{{ App\Enterprise::find($request->idEnterpriseR)->name }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Nombre de la Dirección:</b>
									</td>
									<td>
										<label>{{ $request->reviewedDirection->name }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Nombre del Departamento:</b>
									</td>
									<td>
										<label>{{ App\Department::find($request->idDepartamentR)->name }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Clasificación del gasto:</b>
									</td>
									@php
										$reviewAccount = App\Account::find($request->accountR);
									@endphp
									<td>
										<label>@if(isset($reviewAccount->account)) {{ $reviewAccount->account }} - {{ $reviewAccount->description }} @else No hay @endif</label>
									</td>
								</tr>
								
								<tr>
									<td>
										<b>Nombre del Proyecto:</b>
									</td>
									<td>
										<label>{{ $request->reviewedProject->proyectName }}</label>
									</td>
								</tr>
								<tr>
									<td>
										<b>Etiquetas:</b>
									</td>
									<td>
										@foreach($request->labels as $label)
											<label>{{ $label->description }},</label>
										@endforeach
									</td>
								</tr>
								<tr>
									<td>
										<b>Comentarios:</b>
									</td>
									<td>
										@if($request->checkComment == "")
											<label>Sin comentarios</label>
										@else
											<label>{{ $request->checkComment }}</label>
										@endif
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<p><br></p>
					<div class="block-info">
						<center>
							<strong>ETIQUETAS ASIGNADAS</strong>
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
									<th>Cantidad</th>
									<th>Descripci&oacute;n</th>
									<th>Importe</th>
									<th>Impuesto Adicional</th>
									<th>Etiquetas</th>
								</tr>
							</thead>
							<tbody>
								@foreach($request->purchases->first()->detailPurchase as $detail)
									<tr>
										<td>{{ $detail->quantity }}</td>
										<td>{{ $detail->description }}</td>
										<td>$ {{ number_format($detail->amount,2) }}</td>
										@php
										$taxes2 = 0;
										@endphp
											@foreach($detail->taxes as $tax)
												@php 
													$taxes2 += $tax->amount
												@endphp
											@endforeach
										<td>
											{{ number_format($taxes2,2) }}
										</td>
										<td>
											@foreach($detail->labels as $label)
												{{ $label->label->description }},
											@endforeach
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<p><br></p>
					@if($request->idAuthorize != "")
						<div class="block-info">
							<center>
								<strong>DATOS DE AUTORIZACIÓN</strong>
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
							<table class="employee-details centered-table">
								<tbody>
									<tr>
										<td>
											Autorizó:
										</td>
										<td>
											{{ $request->authorizedUser->name }} {{ $request->authorizedUser->last_name }} {{ $request->authorizedUser->scnd_last_name }}
										</td>
									</tr>
									<tr>
										<td>
											Comentarios:
										</td>
										<td>
											@if($request->authorizeComment == "")
												<label>Sin comentarios</label>
											@else
												<label>{{ $request->authorizeComment }}</label>
											@endif
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