@php
	use Carbon\Carbon;
	$date = Carbon::now();
	$taxes=0;
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
			.div-code
			{
				font-size: 24px;
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
			.labels
			{
				margin-left: 60px;
			}
		</style>
	</head>
	<body>
		<header>
			<table >
				<tbody>
					<tr>
						<td align="left">
							<label class="labels">Folio: {{ $request->folio }} </label> 
							<br>
							<label class="labels">Fecha: {{ date('d-m-Y',strtotime($date)) }}</label>
						</td>
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
							<tbody>
								<tr>
									<th colspan="2">Detalles de la Solicitud</th>
								</tr>
								<tr>
									<th align="left">Título y fecha:</th>
									<th align="right">{{$request->stationery->first()->title }} - {{ $request->stationery->first()->datetitle }}</th>
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
					<br>
					<br><br>
					<center>
						<strong>DETALLES DEL ARTÍCULO</strong>
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
				
					<table class="centered-table bank-info text-center">
						<thead>
							<tr>
								<th>#</th>
								<th>Cantidad</th>
								<th>Categoría</th>
								<th width="30%">Concepto</th>
								<th width="10%">Código corto</th>
								<th width="10%">Código largo</th>
								<th width="30%">Comentario</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						@foreach($request->stationery->first()->detailStat as $key=>$detail)
							<tr>
								<td><label>{{$key+1}}</label></td>
								<td>
									<label>{{ $detail->quantity }} </label>
								</td>
								<td>
									<label>{{ $detail->categoryData()->exists() ? $detail->categoryData->description : '' }} </label>
								</td>
								<td width="30%">
									<label>{{ $detail->product }} </label>
								</td>
								<td width="10%">
									<label>{{ $detail->long_code }} </label>
								</td>
								<td width="10%">
									<label>{{ $detail->short_code }} </label>
								</td>
								<td width="30%">
									<label>{{ $detail->commentaries }} </label>
								</td>
							</tr>
							<tr>
								<td colspan="7"><hr></td>
							</tr>
						@endforeach
						</tbody>
					</table>
					<br>
					@if($request->idCheck != "")
						<br><br>
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
						<table class="centered-table">
							<tbody>
								<tr>
									<td><b>Revisó:</b></td>
									<td><label>{{ $request->reviewedUser->name }} {{ $request->reviewedUser->last_name }} {{ $request->reviewedUser->scnd_last_name }}</label></td>
								</tr>
							@if($request->idEnterpriseR!="")
								<tr>
									<td><b>Nombre de la Empresa:</b></td>
									<td><label>{{ App\Enterprise::find($request->idEnterpriseR)->name }}</label></td>
								</tr>
								<tr>
									<td><b>Nombre de la Dirección:</b></td>
									<td><label>{{ $request->reviewedDirection->name }}</label></td>
								</tr>
								<tr>
									<td><b>Nombre del Departamento:</b></td>
									<td><label>{{ App\Department::find($request->idDepartamentR)->name }}</label></td>
								</tr>
								<tr>
									<td><b>Clasificación del gasto:</b></td>
									@php
										$reviewAccount = App\Account::find($request->accountR);
									@endphp
									<td><label>@if(isset($reviewAccount->account)) {{ $reviewAccount->account }} - {{ $reviewAccount->description }} @else No hay @endif</label></td>
								</tr>
								<tr>
									<td><b>Etiquetas:</b></td>
									<td>
										<label>
											@foreach($request->labels as $label)
												{{ $label->description }},
											@endforeach
										</label>
									</td>
								</tr>
							@endif
								<tr>
									<td><b>Comentarios:</b></td>
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
						@if($request->idEnterpriseR!="")
							<br><br>
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
							
							<table class="centered-table text-center">
								<thead>
									<tr>
										<th width="10%">#</th>
										<th width="10%">Cantidad</th>
										<th width="55%">Concepto</th>
										<th width="25%">Etiquetas</th>
									</tr>
								</thead>
								<tbody>
									@php
										$row = 1;
									@endphp
									@foreach($request->stationery->first()->detailStat as $detail)
										<tr>
											<td>{{$row}}</td>
											<td>{{ $detail->quantity }}</td>
											<td>{{ $detail->product }}</td>
											<td>
												</label>
													@foreach($detail->labels as $label)
														{{ $label->label->description }},
													@endforeach
												</label>
											</td>
										</tr>
										<tr>
											<td colspan="7"><hr></td>
										</tr>
										@php
											$row++;
										@endphp
									@endforeach
								</tbody>
							</table>	
						@endif
					@endif
					@if($request->idAuthorize != "")
						<br><br>
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
						<table class="centered-table">
							<tbody>
								<tr>
									<td><b>Autorizó:</b></td>
									<td><label>{{ $request->authorizedUser->name }} {{ $request->authorizedUser->last_name }} {{ $request->authorizedUser->scnd_last_name }}</label></td>
								</tr>
								<tr>
									<td><b>Comentarios:</b></td>
									<td>
										<label>{{ $request->authorizeComment == "" ? 'Sin Comentarios' : $request->authorizeComment }}</label>
									</td>
								</tr>
							</tbody>
						</table>
						@if($request->code != null)
							<table width="100%">
								<tbody>
									<tr>
										<td colspan="5" align="center">
											<b>Código:</b>
										</td>
									</tr>
									<tr>
										<td width="20%"></td>
										<td width="20%"></td>
										<td align="center" style="border-width: 1px;border: solid; border-color: #000000; font-size:24px;" whidth="20%">
											<b>{{ $request->code  }}</b>
										</td>
										<td width="20%"></td>
										<td width="20%"></td>
									</tr>
									<tr>
										<td colspan="5" align="center">
											Este código es necesario para que le entreguen sus artículos.
										</td>
									</tr>
								</tbody>
							</table>	
						@endif
					@endif
				</div>
			</div>
		</main>
	</body>
</html>