<!DOCTYPE html>
<html>
<head>
	<style>
		.header
		{
			border-collapse	: collapse;
			border-spacing	: 25px;
			width: 90%;
			margin			: 0 auto;
		}	
	
		@page 
		{
			margin	: 8.8em 0 0 0 !important;
		}
		body
		{
			background	: white;
			font-size	: 10.5px;
			font-family	: Arial, Helvetica, sans-serif
			position	: relative !important;
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
			padding			: 5px;
			text-align		: center;
			vertical-align	: top;
			width			: 100px;
		}
		.header .logo img
		{
			width: 100%;
		}
		.request-info
		{
			border			: 1px solid #3a4041;
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info tbody th
		{
			border-bottom	: 1px solid #3a4041;
		}
		.request-info tbody td
		{
			border-bottom	: 1px solid #3a4041;
			padding			: 0.2em 0.1em;
		}
		.request-info-cell
		{
			border			: 1px solid #3a4041;
			border-collapse	: collapse;
			margin			: 0 auto;
			width			: 90%;
		}
		.request-info-cell tbody td
		{
			border			: 1px solid #3a4041;
			padding			: 0.1em 0.1em;
		}
		.request-info-cell tr th
		{
			border			: 1px solid #3a4041;
			padding			: 0.1em 0.1em;
			vertical-align: top; 
		}
		.pdf-table-center-header
		{
			background: #c6c6c6; color: #000;font-size: 1em; font-weight: 400; padding: 0.2.5em 0; text-align: center;
		}
		.block-info
		{
			page-break-inside	: avoid;
		}
	</style>
</head>
<body>
	<header>
			<table class="header request-info-cell">
				<tbody>
					<tr>
						<td class="logo" style="width: 37%;">
							<img src="{{ url('images/pti.jpg') }}" style="width:230px;">
						</td>
						<td style="width: 63%;" align="center">
							Proyecto:EJECUCIÓN DE LA INGENIERÍA COMPLEMENTARIA, PROCURA Y CONSTRUCCIÓN DE INSTALACIONES DE LOS RACKS DE TUBERÍA DE INTEGRACIÓN, 
							DEL LIBRAMIENTO PROVISIONAL, EL CAMINO DE TRANSITO PESADO, LAS CIMENTACIONES SUPERFICIALES, EL SISTEMA DE CONTRAINCENDIOS DEL PAQUETE 6, 
							DURANTE LA FASE II B DE LA REFINERÍA EN DOS BOCAS, PARAÍSO, TABASCO.
							<label style="font-size: 15px"><br>NUEVA REFINERIA DOS BOCAS TABASCO</label>
							<label style="font-size: 15px"><br>{{ $audit->contract }}</label>
						</td>
					</tr>
				</tbody>
			</table>
	</header>
	<main>
		<div class="pdf-full">
			<div class="pdf-body">
				<div class="block-info">
					<table class="request-info">
						<thead>
							<tr>
								<th colspan="6" class="pdf-table-center-header">REGISTRO DE AUDITORÍA EFECTIVA </th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th align="left" colspan="2" style="padding-left: 0.3em; width: 33%;">Centro de trabajo o instalación: </th>
								<td align="left" colspan="4">Nueva Refineria Dos Bocas, Tabasco</td>
							</tr>
							<tr>
								<th align="left" style="padding-left: 0.3em; width: 5%;">Hora: </th>
								<td style="width: 5%;">{{ date('H:s',strtotime($audit->created_at)) }}</td>
								<th align="left" style="width: 8%;">Fecha: </th>
								<td>{{ date('d-m-Y',strtotime($audit->date)) }}</td>
								<th align="left" style="width: 8%;">Folio: </th>
								<td>{{ $audit->id }}</td>
							</tr>
							<tr>
								<th align="left" colspan="2" style="padding-left: 0.3em; width: 10%;">Proyecto Auditado: </th>
								<td colspan="4"><label>{{ $audit->projectData->proyectName }}</label></td>
							</tr>
							<tr>
								<th align="left" colspan="2" style="padding-left: 0.3em; width: 10%;">Área: </th>
								<td colspan="4"><label>{{ $audit->wbsData->code_wbs }}</label></td>
							</tr>
							<tr>
								<th align="left" colspan="2" style="padding-left: 0.3em; width: 10%;">Auditor Líder: </th>
								<td colspan="4"><label>{{ $audit->auditor }}</label></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="block-info">
					<table class="request-info-cell">
						<thead>
							<tr>
								<th colspan="8" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
									ACTOS INSEGUROS
								</th>
							</tr>
							<tr>
								<th rowspan="2" class="pdf-table-center-header" style="width: 4%;">No.</th>
								<th rowspan="2" class="pdf-table-center-header" style="width: 25%;">Descripción</th>
								<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción Correctiva Inmediata</th>
								<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción para Prevenir Repetición</th>
								<th rowspan="2" class="pdf-table-center-header">RE</th>
								<th rowspan="2" class="pdf-table-center-header">FV</th>
							</tr>
							<tr>
								<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
								<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
								<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
								<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
							</tr>
						</thead>
						<tbody>
							@if($audit->unsafeAct()->exists())
								@foreach ($audit->unsafeAct as $key=>$ua)
									<tr style="text-align: center;">
										<td>{{ $key+1 }}</td>
										<td>{{ $ua->description }}</td>
										<td colspan="2">{{ $ua->action }}</td>
										<td colspan="2">{{ $ua->prevent }}</td>
										<td>{{ $ua->re }}</td>
										<td>{{ date('d/m/Y',strtotime($ua->fv)) }}</td>
									</tr>
								@endforeach
							@else
								<tr style="text-align: center;">
									<td> <br></td>
									<td> </td>
									<td colspan="2"> </td>
									<td colspan="2"> </td>
									<td> </td>
									<td> </td>
								</tr>
							@endif
						</tbody>
					</table>
					<div class="block-info">
						<table class="request-info-cell">
							<thead>
								<tr>
									<th colspan="8" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
										PRÁTICAS INSEGURAS
									</th>
								</tr>
								<tr>
									<th rowspan="2" class="pdf-table-center-header" style="width: 4%;">No.</th>
									<th rowspan="2" class="pdf-table-center-header" style="width: 25%;">Descripción</th>
									<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción Correctiva Inmediata</th>
									<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción para Prevenir Repetición</th>
									<th rowspan="2" class="pdf-table-center-header">RE</th>
									<th rowspan="2" class="pdf-table-center-header">FV</th>
								</tr>
								<tr>
									<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
									<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
									<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
									<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
								</tr>
							</thead>
							<tbody>
								@if($audit->unsafePractices()->exists())
									@foreach($audit->unsafePractices as $key=>$up)
										<tr style="text-align:center;">
											<td>{{ $key+1 }}</td>
											<td>{{ $up->description }}</td>
											<td colspan="2">{{ $up->action }}</td>
											<td colspan="2">{{ $up->prevent }}</td>
											<td>{{ $up->re }}</td>
											<td>{{ date('d/m/Y',strtotime($up->fv)) }}</td>
										</tr>
									@endforeach
								@else
									<tr style="text-align: center;">
										<td> <br></td>
										<td> </td>
										<td colspan="2"> </td>
										<td colspan="2"> </td>
										<td> </td>
										<td> </td>
									</tr>
								@endif
							</tbody>
						</table>	
					</div>
					<div class="block-info">
						<table class="request-info-cell">
							<thead>
								<tr>
									<th colspan="8" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
										CONDICIONES INSEGUROS
									</th>
								</tr>
								<tr class="border">
									<th rowspan="2" class="pdf-table-center-header" style="width: 4%;">No.</th>
									<th rowspan="2" class="pdf-table-center-header" style="width: 25%;">Descripción</th>
									<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción Correctiva Inmediata</th>
									<th colspan="2" class="pdf-table-center-header" style="padding-left: 10%; padding-right: 10%;">Acción para Prevenir Repetición</th>
									<th rowspan="2" class="pdf-table-center-header">RE</th>
									<th rowspan="2" class="pdf-table-center-header">FV</th>
								</tr>
								<tr>
									<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
									<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
									<th class="pdf-table-center-header" style="width: 12%;">PTI-ID</th>
									<th class="pdf-table-center-header" style="width: 12%;">IDINSA PROYECTA</th>
								</tr>
							</thead>
							<tbody>
								@if($audit->unsafeConditions()->exists())
									@foreach($audit->unsafeConditions as $key=>$uc)
										<tr style="text-align:center;">
											<td>{{ $key+1 }}</td>
											<td>{{ $uc->description }}</td>
											<td colspan="2">{{ $uc->action }}</td>
											<td colspan="2">{{ $uc->prevent }}</td>
											<td>{{ $uc->re }}</td>
											<td>{{ date('d/m/Y',strtotime($uc->fv)) }}</td>
										</tr>
									@endforeach
								@else
									<tr style="text-align: center;">
										<td> <br> </td>
										<td> </td>
										<td colspan="2"> </td>
										<td colspan="2"> </td>
										<td> </td>
										<td> </td>
									</tr>
								@endif
								<tr>
									<td colspan="4">RE - Responsable de la ejecución</td>
									<td colspan="4">FV - Fecha de Vencimiento de las Acciones Correctivas</td>
								</tr>
								<tr>
									<td colspan="8">Observaciones: {{ $audit->observations }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="block-info">
					<table class="request-info-cell">
						<thead>
							<tr>
								<th colspan="6" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
									Auditores: PTI-ID
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="3" width="50%">
									Nombre
								</td>
								<td colspan="3" width="50%">
									Firma
								</td>
							</tr>
							<tr>
								<td colspan="3" width="50%">{{ $audit->pti_responsible }}</td>
								<td colspan="3" width="50%"></td>
							</tr>
							@foreach($audit->othersResponsibles as $responsible)
								<tr>
									<td colspan="3" width="50%">{{ $responsible->name }}</td>
									<td colspan="3" width="50%"></td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<table class="request-info-cell">
						<thead>
							<tr>
								<th colspan="6" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
									Auditores: IDINSA - PROYECTA
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="3" width="50%">
									Nombre
								</td>
								<td colspan="3" width="50%">
									Firma
								</td>
							</tr>
							<tr>
								<td colspan="3" width="50%">{{ $audit->auditorData()->exists() ? $audit->auditorData->name : '' }}</td>
								<td colspan="3" width="50%"></td>
							</tr>
							@foreach($audit->othersAuditors as $auditor)
								<tr>
									<td colspan="3" width="50%">{{ $auditor->name }}</td>
									<td colspan="3" width="50%"></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="block-info">
					<table class="request-info-cell">
						<thead>
							<tr>
								<th rowspan="2" class="pdf-table-center-header" style="width: 54.1%; vertical-align:middle; padding-left: 0.3em; text-align:left;">
									Categorías y subcategorías
								</th>
								<th colspan="3" class="pdf-table-center-header">
									Factor de severidad
								</th>
								<th rowspan="2" class="pdf-table-center-header" style="width: 11.4%; vertical-align:middle">
									N
								</th>
							</tr>
							<tr>
								<th class="pdf-table-center-header" style="width: 11.4%">
									(1/3)
								</th>
								<th class="pdf-table-center-header" style="width: 11.4%">
									1
								</th>
								<th class="pdf-table-center-header" style="width: 11.4%">
									3
								</th>
							</tr>
						</thead>
						<tbody>
							@php
								$n1_3= $n1 = $n3 = 0;
								$total_persons = $audit->people_involved;
							@endphp
							@foreach(App\AuditCategory::all() as $category)
								<tr>
									<th colspan="5" class="pdf-table-center-header" style="text-align: left; padding-left: 0.3em;">
										{{ $category->name }}
									</th>
								</tr>
								@foreach($category->subcategories as $subcategory)
									@php
										$countDangerousnessOneThirdSubcategory 	= $audit->countDangerousnessOneThirdSubcategory($subcategory->id);
										$countDangerousnessOneSubcategory 		= $audit->countDangerousnessOneSubcategory($subcategory->id);
										$countDangerousnessThreeSubcategory 	= $audit->countDangerousnessThreeSubcategory($subcategory->id);

										$n1_3	+= $countDangerousnessOneThirdSubcategory;
										$n1		+= $countDangerousnessOneSubcategory;
										$n3		+= $countDangerousnessThreeSubcategory;

										$total = $countDangerousnessOneThirdSubcategory+$countDangerousnessOneSubcategory+$countDangerousnessThreeSubcategory;
									@endphp
									<tr>
										<td style="padding-left: 0.3em;">{{ $subcategory->name }}</td>
										<td>{{ $countDangerousnessOneThirdSubcategory }}</td>
										<td>{{ $countDangerousnessOneSubcategory }}</td>
										<td>{{ $countDangerousnessThreeSubcategory }}</td>
										<td>{{ $total }}</td>
									</tr>
								@endforeach
							@endforeach
							<tr>
								<td align="left" style="padding-left: 0.3em;">Totales:</td>
								<td align="center">N1/3= {{ $n1_3 }}</td>
								<td align="center">N1= {{ $n1 }}</td>
								<td align="center">N3= {{ $n3 }}</td>
								<td align="center">{{ $n1_3 + $n1 + $n3 }}</td>
							</tr>
							<tr>
								<td colspan="5" align="center">
									N = Número de activos inseguros observados
								</td>
							</tr>
							<tr>
								<td colspan="4" align="right">
									T = Total de personas involucradas en el desarrollo de la actividad, observándola o transitando por el área =
								</td>
								<td>
									{{ $total_persons }}
								</td>
							</tr>
							<tr>
								<td colspan="4" align="right">
									IAI = (( SUM(Ni X Fsi) ) / T) X 100 = (( N1/3 ) X (1/3) + ( N1 ) X (1) + ( N3 ) X (3)) / T ) X 100 =
								</td>
								<td>
									@php
										if ($total_persons > 0) 
										{
											$iai = round(((($n1_3 * (1/3)) + ($n1 * 1) + ($n3*3))/$total_persons)*100,2);
											$ias = round(100 - $iai,2);
										}
										else
										{
											$iai = 0;
											$ias = 0;
										}
									@endphp	
									{{ $iai }}
								</td>
							</tr>
							<tr>
								<td colspan="4" align="right" style="padding-right: 0.24em;">
									IAS = 100 - IAI =
								</td>
								<td>
									{{ $ias }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="block-info">
					<table class="request-info-cell">
						<thead>
							<tr>
								<th colspan="2" class="pdf-table-center-header">
									Personas Trabajando en Forma Segura
								</th>
								<th colspan="2" class="pdf-table-center-header">
									Personas Trabajando en Forma Insegura
								</th>
							</tr>
							<tr>
								<th class="pdf-table-center-header">Cantidad</th>
								<th class="pdf-table-center-header">Departamento</th>
								<th class="pdf-table-center-header">Cantidad</th>
								<th class="pdf-table-center-header">Departamento</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{ $total_persons - ($n1_3 + $n1 + $n3) }}</td>
								<td>{{ $audit->contractorData->name }}</td>
								<td>{{ $n1_3 + $n1 + $n3 }}</td>
								<td>{{ $audit->contractorData->name }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</main>
</body>
</html>