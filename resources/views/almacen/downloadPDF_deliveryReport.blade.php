@php
	use Carbon\Carbon;
	$date = Carbon::now();
@endphp
<!DOCTYPE html>
<html>
<head>
	
	<style>
		.header
		{
			border-collapse	: separate;
			border-spacing	: 25px;
			margin			: auto;
			padding			: 0;
		}
	</style>

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
                    <td class="logo"></td>
					<td class="date"><label class="pdf-label">Folio: {{$Stationery->requestModel->folio != "" ? $Stationery->requestModel->folio : "---"}} </label> <br><label class="pdf-label">Fecha: {{ date('d-m-Y',strtotime($date)) }}</label></td>
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
								<th align="left">Folio:</th>
								<th align="right">{{$Stationery->requestModel->folio != "" ? $Stationery->requestModel->folio : "---"}}</th>
							</tr>
							<tr>
								<th align="left">Título y fecha:</th>
								<th align="right">{{$Stationery->title != "" ? $Stationery->title : "---" }} - {{ $Stationery->datetitle != "" ? $Stationery->datetitle : ""}}</th>
							</tr>
							<tr>
								<th align="left">Solicitante:</th>
                                @php
                                    $requestUser = App\User::find($Stationery->requestModel->idRequest);
                                @endphp
								<th align="right"{{ $requestUser->name }} {{ $requestUser->last_name }} {{ $requestUser->scnd_last_name }}</th>
							</tr>
							<tr>
								<th align="left">Elaborado por:</th>
								<th align="right">
                                    @php
                                        $elaborateUser = App\User::find($Stationery->requestModel->idElaborate);
                                    @endphp
									{{ $elaborateUser->name }} {{ $elaborateUser->last_name }} {{ $elaborateUser->scnd_last_name }}
								</th>
							</tr>
							<tr>
								<th align="left">Empresa:</th>
								<th align="right">{{ App\Enterprise::find($Stationery->requestModel->idEnterprise)->name }}</th>
							</tr>
							<tr>
								<th align="left">Dirección:</th>
								<th align="right">{{ App\Area::find($Stationery->requestModel->idArea)->name }}</th>
							</tr>
							<tr>
								<th align="left">Departamento:</th>
								<th align="right">{{ App\Department::find($Stationery->requestModel->idDepartment)->name }}</th>
							</tr>
							<tr>
								<th align="left">Subtotal:</th>
								<th align="right">
									${{$Stationery->subtotal != "" ? $Stationery->subtotal : "0.00"}}
								</th>
							</tr>
							<tr class="no-border">
								<th align="left">IVA</th>
								<th align="right">${{$Stationery->iva != "" ? $Stationery->iva : "$0.00"}}</th>
							</tr>
                            <tr class="no-border">
								<th align="left">Total</th>
								<th align="right">${{$Stationery->total != "" ? $Stationery->total : "$0.00"}}</th>
							</tr>
						</tbody>
					</table>
				</div>
				<p><br></p>
				<div class="block-info">
					<center>
						<strong>DATOS DE LA ENTREGA</strong>
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
								<th>Descripción de artículo solicitado</th>
								<th>Cantidad de artículos solicitados</th>
								<th>Descripción de artículo entredado del inventario</th>
								<th>Ubicación del artículo entregado del inventario</th>
                                <th>Subtotal</th>
								<th>IVA</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							@foreach($Stationery->detailStat as $detail)
                                <tr>
                                    <td>
                                        {{$detail->product != "" ? $detail->product : "---"}}
                                    </td>
                                    <td>
                                        {{$detail->quantity != "" ? $detail->quantity : "---"}}
                                    </td>
                                    <td>
                                        {{$detail->productDelivery->cat_c != null ? $detail->productDelivery->cat_c->description : "---"}}
                                    </td>
                                    <td>
                                        {{$detail->productDelivery->location != null ? $detail->productDelivery->location->place : "---"}}
                                    </td>
                                    <td>
                                        ${{$detail->subtotal != "" ? $detail->subtotal : "0.00"}}
                                    </td>
                                    <td>
                                        ${{$detail->iva != "" ? $detail->iva : "0.00"}}
                                    </td>
                                    <td>
                                        ${{$detail->total != "" ? $detail->total : "0.00"}}
                                    </td>
                                </tr>
							@endforeach
						</tbody>
					</table>
				</div>
                <p>&nbsp;</p>
                <div>
					<table class="centered-table bank-info total-details">
						<tbody>
							<tr>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
								<td>Subtotal:</td>
								<td>$ {{$Stationery->subtotal != "" ? number_format($Stationery->subtotal,2,".",",") : "0.00"}}</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>IVA:</td>
								<td>$ {{$Stationery->iva != "" ? number_format($Stationery->iva,2,".",",") : "$0.00"}}</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>TOTAL:</td>
								<td>$ {{$Stationery->total != "" ? number_format($Stationery->total,2,".",",") : "$0.00"}}</td>
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
									<label>{{ $Stationery->requestModel->reviewedUser->name }} {{ $Stationery->requestModel->reviewedUser->last_name }} {{ $Stationery->requestModel->reviewedUser->scnd_last_name }}</label>
								</td>
							</tr>
							<tr>
								<td>
									<b>Nombre de la Empresa:</b>
								</td>
								<td>
									<label>{{ App\Enterprise::find($Stationery->requestModel->idEnterpriseR)->name }}</label>
								</td>
							</tr>
							<tr>
								<td>
									<b>Nombre de la Dirección:</b>
								</td>
								<td>
									<label>{{ $Stationery->requestModel->reviewedDirection->name }}</label>
								</td>
							</tr>
							<tr>
								<td>
									<b>Nombre del Departamento:</b>
								</td>
								<td>
									<label>{{ App\Department::find($Stationery->requestModel->idDepartamentR)->name }}</label>
								</td>
							</tr>
							<tr>
								<td>
									<b>Clasificación del gasto:</b>
								</td>
								@php
									$reviewAccount = App\Account::find($Stationery->requestModel->accountR);
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
									<label>{{ $Stationery->requestModel->reviewedProject != null ? $Stationery->requestModel->reviewedProject->proyectName : "---"}}</label>
								</td>
							</tr>
							<tr>
								<td>
									<b>Etiquetas:</b>
								</td>
								<td>
									@foreach($Stationery->requestModel->labels as $label)
										<label>{{ $label->description }},</label>
									@endforeach
								</td>
							</tr>
							<tr>
								<td>
									<b>Comentarios:</b>
								</td>
								<td>
									@if($Stationery->requestModel->checkComment == "")
										<label>Sin comentarios</label>
									@else
										<label>{{ $Stationery->requestModel->checkComment }}</label>
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
								<th>Producto</th>
								<th>Total</th>
								<th>Etiquetas</th>
							</tr>
						</thead>
						<tbody>
                            @foreach($Stationery->detailStat as $detail)
                                <tr>
                                    <td>{{$detail->quantity != "" ? $detail->quantity : "---"}}</td>
                                    <td>{{$detail->product != "" ? $detail->product : "---"}}</td>
                                    <td> ${{$detail->total != "" ? $detail->total : "0.00"}}</td>
                                    <td>
                                        @foreach(App\LabelDetailStationery::where('idStatDetail', $detail->idStatDetail)->get() as $detailLabel)
                                            {{ $detailLabel->label->description }},
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
						</tbody>
					</table>
				</div>
                <p><br></p>
                @if($Stationery->requestModel->idAuthorize != "")
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
                                        {{ $Stationery->requestModel->authorizedUser->name }} {{ $Stationery->requestModel->authorizedUser->last_name }} {{ $Stationery->requestModel->authorizedUser->scnd_last_name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Comentarios:
                                    </td>
                                    <td>
                                        @if($Stationery->requestModel->authorizeComment == "")
                                            <label>Sin comentarios</label>
                                        @else
                                            <label>{{ $Stationery->requestModel->authorizeComment }}</label>
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
