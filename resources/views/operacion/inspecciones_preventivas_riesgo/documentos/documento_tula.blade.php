<!DOCTYPE html>
<html>
	<head>
		<style>
			@page
			{
				margin-top	    : 2em !important;
				margin-bottom	: 3em !important;
			}
			body
			{
				background	: white;
				font-size	: 12px;
				position	: relative !important;
				font-family	: 'Helvetica',sans-serif;
			}
			header
			{
				position	: fixed;
				top			: -8.8em;
			}
			.header
			{
				border-spacing	: 10px;
				width           : 75%;
				margin-bottom	: 1em !important;
			}
			.observation
			{
				font-size	    : 12px;
				border-spacing	: 10px;
				width           : 100%;
				margin-bottom	: 1em !important;
			}
			.footer
			{
				border-spacing	: 10px;
				width           : 145%;
				font-size	    : 12px;
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
			.header td
			{
				font-size   : 1.2em;
				font-weight : 400;
				text-align  : right;
				width       : 10px;
			}
			th.rotate 
			{
				text-align      : left;
				border			: 1px solid #000000;
			}
			
			th.rotate > div, th.rotate > div > div
			{
				position    : relative;
				overflow    : visible;
				width       : 20px;
				height      : 350px;
				font-size   : 0.9em;
			}

			th.rotate > div > div:after
			{
				height      : 350px;
				overflow    : visible;
				position    : absolute;
				transform   : rotate(270deg);
				width       : 350px;
			}
			td.vertical 
			{
				text-align      : left;
				padding-bottom  : 0.6em;
				border			: 1px solid #000000;
			}
			.catcolor-1
			{
				background      : yellow;
				padding-bottom  : 0.6em;
			}
			.catcolor-2
			{
				background      : orange;
				padding-bottom  : 0.6em;
			}
			.catcolor-3
			{
				background      : cyan;
				padding-bottom  : 0.6em;
			}
			.catcolor-4
			{
				background      : greenyellow;
				padding-bottom  : 0.6em;
			}
			.catcolor-5
			{
				background      : pink;
				padding-bottom  : 0.6em;
			}
			td.vertical > div, td.vertical > div > div
			{
				position    : relative;
				overflow    : visible;
				width       : 20px;
				height      : 30px;
				font-size   : 1em;
			}

			td.vertical > div > div:after
			{
				height      : 30px;
				overflow    : visible;
				position    : absolute;
				transform   : rotate(270deg);
				width       : 30px;
			}
			.request-info
			{
				border			: 1px solid #3a4041;
				border-collapse	: collapse;
				margin			: 0 auto;
				width			: 100%;
			}
			.request-info tbody th
			{
				border-bottom	: 1px solid #3a4041;
			}
			.request-info tbody td
			{
				border-bottom	: 1px solid #3a4041; 
			}
			.request-info-cell
			{
				border			: 1px solid #3a4041;
				border-collapse	: collapse;
				margin			: 0 auto;
				width			: 100%;
			}
			.request-info-cell th
			{
				border			: 1px solid #000000;
				border-collapse : separate;
				vertical-align  : top;
				font-size: 1.2em;
			}
			.request-info-cell tbody td
			{
				border			: 1px solid #000000;
				padding			: 0.1em 0.1em;
				font-size       : 1em;
				font-weight     : 400;
			}
			.request-info-cell-cat
			{
				border			: 1px solid #3a4041;
				border-collapse	: collapse;
				margin			: 0 auto;
				width			: 100%;
			}
			.request-info-cell-cat th
			{
				border			: 1px solid #000000;
				border-collapse : separate;
				vertical-align  : middle;
				text-align      : left;
				font-size       : 1.1em;
			}
			.request-info-cell-cat tbody td
			{
				border			: 1px solid #000000;
				padding			: 0.1em 0.1em;
				font-size       : 0.9em;
				font-weight     : 400;
			}
			.pdf-table-center-header
			{
				background  : #c6c6c6;
				color       : #000000;
				font-size   : 1.2em;
				font-weight : 400;
				text-align  : center;
			}
			.page-break
			{
				page-break-after: always;
			}
		</style>
	</head>
	<body>
		<table class="header">
			<tbody>
				<tr>
					<td class="logo" style="text-align: left;width: 37%;">
						<img src="{{ url('images/proyecta2.png') }}" style="width:130px;">
					</td>
				</tr>
				<tr>
					<td style="text-align: left">
						@php $time = new DateTime($preventive->date); $date = $time->format('d/m/Y'); @endphp
						Fecha: {{ $date }}
					</td>
					<td>
						Seguridad <td style="border: 1px solid black; text-align: center; @if ($preventive->heading == 1)background: black;@endif"> </td>
					</td>
					<td>
						Ambiental <td style="border: 1px solid black; text-align: center; @if ($preventive->heading == 2)background: black;@endif"> </td>
					</td>
					<td>
						Salud Ocupacional <td style="border: 1px solid black; text-align: center; @if ($preventive->heading == 3)background: black;@endif"> </td>
					</td>
				</tr>
			</tbody>
		</table>
		<main>
			<div  class="pdf-full">
				<div class="pdf-body">
					<div class="block-info" style="margin-bottom: 2%;">
						<table class="request-info-cell">
							<thead>
								<tr>
									<th class="pdf-table-center-header">Hora</th>
									<th class="pdf-table-center-header">Desviación/Hallazgo</th>
									<th class="pdf-table-center-header">Lugar y/o Área</th>
									<th class="pdf-table-center-header">Acto/Condición</th>
									<th class="pdf-table-center-header">Supervisor
										Responsable
										Firma de enterado
									</th>
									<th class="pdf-table-center-header">Acción Correctiva</th>
									<th class="pdf-table-center-header">Disciplina</th>
									<th class="pdf-table-center-header">Estatus:
										Cerrado/Abierto
									</th>
								</tr>
							</thead>
							<tbody>
								@if (isset($preventive))
									@foreach ($preventive->detailInspection as $preven)
										<tr style="text-align:center;">
											<td>
												@php 
													if ($preven->hour != "") 
													{ 
														$time = new DateTime($preven->hour); 
														$date = $time->format('H:i'); 
													}
													else
													{
														$date = "";
													}
												@endphp
												{{ $date }}
											</td>
											<td>
												{{ $preven->condition }}
											</td>
											<td>
												{{ $preventive->area }}
											</td>
											<td>
												{{ $preven->actData() }}
											</td>
											<td>
												{{ $preven->responsible }}
											</td>
											<td>
												{{ $preven->action }}
											</td>
											<td>
												{{ $preven->discipline }}
											</td>
											<td>{{ $preven->statusData() }}</td>
										</tr>
									@endforeach
								@else
									
								@endif
							</tbody>
						</table>
					</div>
					<div class="block-info" style="margin-bottom: 3%;">
						<table class="observation">
							<tbody>
								<tr style="font-size: 12px">
									<td>
										<strong>Observaciones:</strong> <u>{{ $preventive->observation }}</u>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="block-info" style="margin-bottom: 3%;">
						<table class="footer">
							<tbody>
								<tr style="font-weight: bold;">
									<td align="justify">
										Nombre y firma del Supervisor SSPA
									</td>
									<td align="justify">
										Nombre y firma del Responsable SSPA
									</td>
								</tr>
								<tr style="font-size: 12px">
									<td align="justify" style="padding-left: 1em">
										{{ $preventive->supervisor_name }}
									</td>
									<td align="justify" style="padding-left: 1em">
										{{ $preventive->responsible_name }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="page-break"></div>
			<div class="pdf-full">
				<div class="pdf-body">
					<div class="block-info">
						<table class="request-info-cell-cat">
							<thead>
								<tr>
									<th class="rotate">
										<div>
											<div class="insecure">
											</div>
											<style>
												.insecure:after
												{
													content: 'Acto o condición insegura';
												}
											</style>
										</div>
									</th>
									<th class="rotate">
										<div>
											<div class="secure"></div>
											<style>
												.secure:after
												{
													content: 'Acciones inseguras';
												}
											</style>
										</div>
									</th>
									@foreach (App\AuditCategory::all() as $category)
										<th class="rotate">
											<div class="catcolor-{{$category->id}}">
												<div class="audit-{{$category->id}}"></div>
												<style>
													.audit-{{$category->id}}:after
													{
														content: '{{ substr($category->name,2) }}';
													}
												</style>
											</div>
										</th>
										@foreach (App\AuditSubcategory::where('audit_category_id',$category->id)->get() as $sub)
											<th class="rotate">
												<div>
													<div class="subaudit-{{$sub->id}}"></div>
													<style>
														.subaudit-{{$sub->id}}:after
														{
															content: '{{ substr($sub->name,4) }}';
														}
													</style>
												</div>
											</th>
										@endforeach
									@endforeach
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Hora</td>
									<td>F.S</td>
									@foreach (App\AuditCategory::all() as $category)
										<td class="vertical">
											<div class="catcolor-{{$category->id}}">
												<div class="cat-{{$category->id}}"></div>
												<style>
													.cat-{{$category->id}}:after
													{
														content: '{{ substr($category->name,0,1) }}';
													}
												</style>
											</div>
										</td>                                
										@foreach ($category->subcategories as $sub)
											<td class="vertical">
												<div>
													<div class="sub-{{$sub->id}}"></div>
													<style>
														.sub-{{$sub->id}}:after
														{
															content: '{{ substr($sub->name,0,4) }}';
														}
													</style>
												</div>
											</td>
										@endforeach
									@endforeach
								</tr>
								@foreach ($preventive->detailInspection as $detail)
									<tr>
										<td rowspan="3">
											@if (isset($preventive)) 
												@php 
													$time = new DateTime($detail->hour); 
													$hour = $time->format('H:i'); 
												@endphp 
											@endif
											{{ $hour }}
										</td>
										<td>
											0.33    
										</td>
										 @foreach (App\AuditCategory::all() as $category)
											<td> </td>
											@foreach ($category->subcategories as $subcategory)
												<td>
													{{ $detail->severity == "1/3" && $detail->subcategory_id == $subcategory->id ? 1 : 0 }}
												</td>
											@endforeach
										@endforeach
									</tr>
									<tr>
										<td>
											1    
										</td>
										 @foreach (App\AuditCategory::all() as $category)
											<td> </td>
											@foreach ($category->subcategories as $subcategory)
												<td>
													{{ $detail->severity == "1" && $detail->subcategory_id == $subcategory->id ? 1 : 0 }}
												</td>
											@endforeach
										@endforeach
									</tr>
									<tr>
										<td>
											3    
										</td>
										 @foreach (App\AuditCategory::all() as $category)
											<td> </td>
											@foreach ($category->subcategories as $subcategory)
												<td>
													{{ $detail->severity == "3" && $detail->subcategory_id == $subcategory->id ? 1 : 0 }}
												</td>
											@endforeach
										@endforeach
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</main>
	</body>
</html>