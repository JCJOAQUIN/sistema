@php
	use Carbon\Carbon;
	$date = Carbon::now();
	$taxes = $retentions =0;
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
	</style>
</head>
<body>
	<header>
		<table class="header">
			<tbody>
				<tr>
					<td class="logo"><img src="{{ asset('images/logo-LogIn.jpg') }}"></td>
					<td class="date"><label class="pdf-label">Folio: {{ $request->folio }} </label> <br><label class="pdf-label">Fecha: {{ date('d-m-Y',strtotime($date)) }}</label></td>
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
								<th align="right">{{$request->income->first()->title }} - {{ $request->income->first()->datetitle }}</th>
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
						<strong>DATOS DEL CLIENTE</strong>
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
								<td><label>{{ $request->income->first()->client->businessName }}</label></td>
							</tr>
							<tr>
								<td><b>RFC:</b></td>
								<td><label>{{ $request->income->first()->client->rfc }}</label></td>
							</tr>
							<tr>
								<td><b>Teléfono:</b></td>
								<td><label>{{ $request->income->first()->client->phone }}</label></td>
							</tr>
							<tr>
								<td><b>Calle:</b></td>
								<td><label>{{ $request->income->first()->client->address }}</label></td>
							</tr>
							<tr>
								<td><b>Número:</b></td>
								<td><label>{{ $request->income->first()->client->number }}</label></td>
							</tr>
							<tr>
								<td><b>Colonia:</b></td>
								<td><label>{{ $request->income->first()->client->colony }}</label></td>
							</tr>
							<tr>
								<td><b>CP:</b></td>
								<td><label>{{ $request->income->first()->client->postalCode }}</label></td>
							</tr>
							<tr>
								<td><b>Ciudad:</b></td>
								<td><label>{{ $request->income->first()->client->city }}</label></td>
							</tr>
							<tr>
								<td><b>Estado:</b></td>
								<td><label>{{ App\State::find($request->income->first()->client->state_idstate)->description }}</label></td>
							</tr>
							<tr>
								<td><b>Contacto:</b></td>
								<td><label>{{ $request->income->first()->client->contact }}</label></td>
							</tr>
							<tr>
								<td><b>Correo Electrónico:</b></td>
								<td><label>{{ $request->income->first()->client->email }}</label></td>
							</tr>
							<tr>
								<td><b>Otro</b></td>
								<td><label>{{ $request->income->first()->client->commentaries }}</label></td>
							</tr>
						</tbody>
					</table>
					<table class="centered-table bank-info">
						<thead>
							<tr>
								<th>Banco</th>
								<th>Alias</th>
								<th>Cuenta</th>
								<th>Sucursal</th>
								<th>Referencia</th>
								<th>CLABE</th>
								<th>Moneda</th>
								<th>Convenio</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach(App\BanksAccounts::where('idEnterprise',$request->idEnterprise)->get() as $bank)
								@php
									$alias		= $bank->alias!=null ? $bank->alias : '-----';
									$clabe		= $bank->clabe!=null ? $bank->clabe : '-----';
									$account	= $bank->account!=null ? $bank->account : '-----';
									$branch		= $bank->branch!=null ? $bank->branch : '-----';
									$reference	= $bank->reference!=null ? $bank->reference : '-----';
									$currency	= $bank->currency!=null ? $bank->currency : '-----';
									$agreement	= $bank->agreement!=null ? $bank->agreement : '-----';
								@endphp
								@if($request->income->first()->idbanksAccounts == $bank->idbanksAccounts)
									<tr>
										<td>
											{{ $bank->bank->description }}
										</td>
										<td>
											{{ $alias }}
										</td>
										<td>
											{{ $account }}
										</td>
										<td>
											{{ $branch }}
										</td>
										<td>
											{{ $reference }}
										</td>
										<td>
											{{ $clabe }}
										</td>
										<td>
											{{ $currency }}
										</td>
										<td>
											{{ $agreement }}
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
								<th>#</th>
								<th>Cantidad</th>
								<th>Unidad</th>
								<th>Descripci&oacute;n</th>
								<th>Precio Unitario</th>
								<th>IVA</th>
								<th>Impuesto adicional</th>
								<th>Retenciones</th>
								<th>Importe</th>
							</tr>
						</thead>
						<tbody>
							@foreach($request->income->first()->incomeDetail as $key=>$detail)
								<tr>
									<td class="countConcept">{{$key+1}}</td>
									<td>
										{{ $detail->quantity }}
									</td>
									<td>
										{{ $detail->unit }}
									</td>
									<td>
										{{ $detail->description }}
									</td>
									<td>
										$ {{ $detail->unitPrice }}
									</td>
									<td>
										$ {{ $detail->tax }}
									</td>
									<td>
										@php
											$taxesConcept=0;
										@endphp
										@foreach($detail->taxes as $tax)
											@php
												$taxesConcept+=$tax->amount;
											@endphp
										@endforeach
										$ {{ number_format($taxesConcept,2) }}
									</td>
									<td>
										@php
											$retentionConcept=0;
										@endphp
										@foreach($detail->retentions as $ret)
											@php
												$retentionConcept+=$ret->amount;
											@endphp
										@endforeach
										$ {{ number_format($retentionConcept,2) }}
									</td>
									<td>
										$ {{ $detail->amount }}
									</td>
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
								<td>$ {{ number_format($request->income->first()->subtotales,2,".",",") }}</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>Impuesto Adicional:</td>
								<td>
									@foreach($request->income->first()->incomeDetail as $detail)
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
								<td>Retenciones:</td>
								<td>
									@foreach($request->income->first()->incomeDetail as $detail)
										@foreach($detail->retentions as $ret)
											@php 
												$retentions += $ret->amount
											@endphp
										@endforeach
									@endforeach
									$ {{ number_format($retentions,2,".",",") }}
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>IVA:</td>
								<td>$ {{ number_format($request->income->first()->tax,2,".",",") }}</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>TOTAL:</td>
								<td>$ {{ number_format($request->income->first()->amount,2,".",",") }}</td>
							</tr>
						</tbody>
					</table>
					<div class="pdf-notes"><label>Notas:</label> {{ $request->income->first()->notes }}</div>
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