@php
	use Carbon\Carbon;
	$date = Carbon::now();
@endphp
<!DOCTYPE html>
<html>
	<head>
		
		<style type="text/css">
			@page {
				margin-top	: 1em !important;
				margin-bottom	: 1em !important;
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
				/* left		: 0px; */
				position	: fixed;
				top			: -12.3em;
			}
			.header
			{
				/* border-collapse	: separate; */
				border-spacing	: 25px;
				width: 100%;
				margin			: 0 10px;
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
				width			: 94%;
			}

			.request-info tbody th
			{
				border-bottom	: 1px dotted #c6c6c6;
				font-weight		: 600;
				padding			: 0.5em 0.3em;
			}
			/* .request-info tbody tr.no-border th
			{
				border: none;
			} */

			.pdf-table-center-header
			{
				background		: #ff644e;
				color			: #fff;
				filter			: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff9f00', endColorstr='#ff9f00',GradientType=1 );
				font-size		: 1em;
				font-weight		: 700;
				padding			: 0.5em 0;
				text-align		: center;
				/* text-transform	: uppercase; */
			}
			.pdf-table-center-header-white
			{
				background		: #fff;
				color			: #000;
				filter			: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff9f00', endColorstr='#ff9f00',GradientType=1 );
				font-size		: 1em;
				font-weight		: 700;
				padding			: 0.5em 0;
				text-align		: center;
				/* text-transform	: uppercase; */
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
				background-color	: #ff644e;
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
			.page-break {
				page-break-after: always;
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
			.border-line
			{
				border			: 1px solid #c6c6c6;
			}
			.border-bottom-firma
			{
				border-bottom: 1px solid #c6c6c6;
				width:300px;
				margin:auto
			}
			.text-center
			{
				text-align: center;
			}

			.request-info-firma
			{
				margin			: 0 auto;
				width			: 90%;
			}

			.request-info-firma tbody th
			{
				font-weight		: 600;
				padding			: 0.5em 0.3em;
			}
			.request-info-firma tbody tr.no-border th
			{
				border: none;
			}
			.float-right
			{
				float: right;
			}
			.float-left
			{
				float: left;
			}
			.borders
			{
				border: 1px solid #cfcfcf;
				border-collapse: collapse;
			}
			.h-20
			{
				height: 70px;
			}
			.text-center
			{
				text-align: center;
			}
			.text-left
			{
				text-align: left;
			}
			.text-right
			{
				text-align: right;
			}
			.w-10
			{
				width: 27%;
			}
			.w-15
			{
				width: 35%;
			}
		</style>
	</head>
	<body>

		@foreach ($result as $key)
			@php
				$title       = $key->title;
				$projectName = $key->proyectName;
				$urgent      = $key->urgent;
				$code_wbs    = $key->code_wbs;
				$code_edt    = $key->edtDescription;
				$code_edt_f  = $key->edtCode;
			@endphp
		@endforeach
			<table class="header">
				<tbody>
					<tr>
						<td>
							<div class="text-center">
								<label>
									<b>{{$title}}</b>
								</label>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="col-md-6 mb-4 float-left text-left">
								<label>Proyecto: {{ $projectName }} </label>
								<br><br>
								@if($t_request->requisition()->exists() && $t_request->requisition->request_requisition != "")
									<label>Solicitante: {{$t_request->requisition()->exists() ? $t_request->requisition->request_requisition : 'Sin solicitante'}} </label>
								@else
									<label>Solicitante: {{$t_request->requestUser()->exists() ? $t_request->requestUser->fullName() : 'Sin solicitante'}} </label>
								@endif
								<br><br>
								<div class="@if($code_wbs != "") w-10 @else w-15 @endif">
									<div class="mb-4 float-left">
										<label>Prioridad: @if($urgent == 0) Baja @endif @if($urgent == 1) Media @endif @if($urgent == 2) Alta @endif</label>
									</div>
									<div class="mb-4 float-right">
										<label>Tipo de Requisición: {{$t_request->requisition->typeRequisition->name}}</label>
									</div>
								</div>
							</div>
							<div class="col-md-6 mb-4 float-right text-right">
								<label>Fecha: {{ date('d-m-Y',strtotime($date)) }}</label><br>
								<label>Hora: {{ $date->toTimeString() }}</label>
								<br><br>
								<label>Código WBS: @if($code_wbs != "") {{ $code_wbs }} @else N/A @endif </label>
								<br>
								<label>Código EDT: @if($code_edt_f != "") {{$code_edt_f}} ({{ $code_edt}}) @else N/A @endif</label>
								<br>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			
			<br><br><br><br>
			<div class="text-center">
				<label><b>TABLA COMPARATIVA</b></label>
			</div>
			
			@foreach ($result as $data) 
				@php
					$type_material = App\CatProcurementMaterial::where('id',$data->cat_procurement_material_id )->get();
					foreach ($type_material as $item) 
					{
						$type_material = $item->name;
					}
				@endphp	
				<table class="request-info borders">
					<thead class="pdf-table-center-header borders text-center">
						@switch($t_request->requisition->requisition_type)	
							@case(1)
								<tr>
									<th class="borders">Part</th>
									<th class="borders" style="height: 20px;">Concepto</th>
									<th class="borders">Descripción</th>
									<th class="borders">Categoría</th>
									<th class="borders">Tipo</th>
									<th class="borders">Cantidad</th>
									<th class="borders">Medida</th>
									<th class="borders">Unidad</th>
									<th class="borders">Almacén</th>
								</tr>
								@break
							@case(2)
								<tr>
									<th class="borders">Part</th>
									<th class="borders" style="height: 20px;">Concepto</th>
									<th class="borders">Descripción</th>
									<th class="borders">Categoría</th>
									<th class="borders">Cantidad</th>
									<th class="borders">Unidad</th>
									<th class="borders">Periodo</th>
								</tr>
								@break
							@case(4)
								<tr>
									<th class="borders">Part</th>
									<th class="borders" style="height: 20px;">Concepto</th>
									<th class="borders">Descripción</th>
									<th class="borders">Cantidad</th>
									<th class="borders">Unidad</th>
								</tr>
								@break
							@case(5)
								<tr>
									<th class="borders">Part</th>
									<th class="borders" style="height: 20px;">Concepto</th>
									<th class="borders">Descripción</th>
									<th class="borders">Categoría</th>
									<th class="borders">Cantidad</th>
									<th class="borders">Medida</th>
									<th class="borders">Unidad</th>
									<th class="borders">Almacén</th>
									<th class="borders">Marca</th>
									<th class="borders">Modelo</th>
									<th class="borders">Tiempo de utilización</th>
								</tr>
								@break
							@case(6)
								<tr>
									<th class="borders">Part</th>
									<th class="borders" style="height: 20px;">Concepto</th>
									<th class="borders">Descripción</th>
									<th class="borders">Cantidad</th>
									<th class="borders">Unidad</th>
								</tr>
								@break
						@endswitch
					</thead>
					<tbody class="text-center">
						@switch($t_request->requisition->requisition_type)	
							@case(1)
								<tr>
									<td class="borders">{{$data['part']}}</td>
									<td class="borders">{{$data['name']}}</td>
									<td class="borders">{{$data['description']}}</td>
									<td class="borders">{{$data['category']}}</td>
									<td class="borders">@if($type_material != "[]") {{$type_material}} @endif</td>
									<td class="borders">{{$data['quantity']}}</td>
									<td class="borders">{{$data['measurement']}}</td>
									<td class="borders">{{$data['unit']}}</td>
									<td class="borders">{{$data['exists_warehouse']}}</td>
								</tr>
								@break
							@case(2)
								<tr>
									<td class="borders">{{$data['part']}}</td>
									<td class="borders">{{$data['name']}}</td>
									<td class="borders">{{$data['description']}}</td>
									<td class="borders">{{$data['category']}}</td>
									<td class="borders">{{$data['quantity']}}</td>
									<td class="borders">{{$data['unit']}}</td>
									<td class="borders">{{$data['period']}}</td>
								</tr>
								@break
							@case(4)
							
								<tr>
									<td class="borders">{{$data['part']}}</td>
									<td class="borders">{{$data['name']}}</td>
									<td class="borders">{{$data['description']}}</td>
									<td class="borders">{{$data['quantity']}}</td>
									<td class="borders">{{$data['unit']}}</td>
								</tr>
								@break
							@case(5)
								<tr>
									<td class="borders">{{$data['part']}}</td>
									<td class="borders">{{$data['name']}}</td>
									<td class="borders">{{$data['description']}}</td>
									<td class="borders">{{$data['category']}}</td>
									<td class="borders">{{$data['quantity']}}</td>
									<td class="borders">{{$data['measurement']}}</td>
									<td class="borders">{{$data['unit']}}</td>
									<td class="borders">{{$data['exists_warehouse']}}</td>

									<td class="borders">{{$data['brand']}}</td>
									<td class="borders">{{$data['model']}}</td>
									<td class="borders">{{$data['usage_time']}}</td>
								</tr>
							@break
							@case(6)
								<tr>
									<td class="borders">{{$data['part']}}</td>
									<td class="borders">{{$data['name']}}</td>
									<td class="borders">{{$data['description']}}</td>
									<td class="borders">{{$data['quantity']}}</td>
									<td class="borders">{{$data['unit']}}</td>
								</tr>
								@break
						@endswitch
					</tbody>
				</table>
				@php
					$providerArray = [];
					$providerArrayData = [];
					foreach ($t_request->requisition->requisitionHasProvider as $key)
					{
						$providerArrayData[] = $key->type_currency;
						$providerArrayData[] = $key->delivery_time;
						$providerArrayData[] = $key->credit_time;
						$providerArrayData[] = $key->guarantee;
						$providerArrayData[] = $key->spare;
						$providerArrayData[] = $key->commentaries;
						array_push($providerArray, $providerArrayData);
						$providerArrayData = [];	
					}
					$n = 0;
				@endphp									
				<table class="request-info borders">
					<thead class="pdf-table-center-header borders text-center">
						<tr>
							<th class="borders" width="20%" style="height: 20px;">Proveedor</th>
							<th class="borders" width="8%">Precio Unitario</th>
							<th class="borders" width="8%">Subtotal</th>
							<th class="borders" width="8%">IVA</th>
							<th class="borders" width="8%">Impuestos</th>
							<th class="borders" width="8%">Retenciones</th>
							<th class="borders" width="8%">Total</th>
							<th class="borders" width="30%">Generales</th>
						</tr>
					</thead>
					<tbody class="text-center">	
						@for ($i = 0; $i<$t_request->requisition->requisitionHasProvider()->count(); $i++)
							{{-- <div style="page-break-after: always;"></div> --}}
							@php
								$prov	= null;
								$prov = $t_request->requisition->requisitionHasProvider[$i];

								$t = App\requisitionHasProvider::where('idProviderSecondary', $prov->idProviderSecondary)->where('id', $data->idRequisitionHasProvider)->count();
								if($t>0)
								{
									$color 		= '#fff8f1';
									$textColor  = '#ff6553';
								}
								else
								{
									$color 		= 'white';
									$textColor  = 'black';
								}
							@endphp
							<tr>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>{{ $prov->providerData->businessName }}</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>${{number_format($data['unitPrice'.$prov->id],2)}}</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>${{number_format($data['subtotal'.$prov->id],2)}}</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>${{number_format($data['iva'.$prov->id],2)}}</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>@if($data['taxes'.$prov->id] != "")${{number_format($data['taxes'.$prov->id],2)}}@else $0.00 @endif</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>@if($data['retentions'.$prov->id] != "")${{number_format($data['retentions'.$prov->id],2)}}@else $0.00 @endif</td>
								<td class="borders" style='background-color: {{ $color }}; color:{{ $textColor }}'>${{number_format($data['total'.$prov->id],2)}}</td>
								<td class="borders text-left" style='background-color: {{ $color }}; color:{{ $textColor }}'>
									<label>MONEDA: {{$providerArray[$i][0]}}</label><br>
									<label>TIEMPO DE ENTREGA: @if($providerArray[$i][1]!=""){{$providerArray[$i][1]}} @else N/A @endif</label><br>
									<label>CRÉDITO DÍAS: @if($providerArray[$i][2]!=""){{$providerArray[$i][2]}} @else N/A @endif</label><br>
									<label>GARANTÍA: @if($providerArray[$i][3]!=""){{$providerArray[$i][3]}} @else N/A @endif</label><br>
									<label>PARTES DEL REPUESTO: @if($providerArray[$i][4]!=""){{$providerArray[$i][4]}} @else N/A @endif</label><br>
									<label>COMENTARIOS: {{$providerArray[$i][5]}}</label>
								</td>
							</tr>						
						@endfor
					</tbody>
				</table>
				<br>
			@endforeach
			{{-- <div class="block-info">
				<table class="request-info-firma">
					<tbody>
						<tr>
							<td style="width:400px;" class="text-center">
								<label>Eli Taboada Rojas</label>
								<div class="border-bottom-firma"></div>
							</td>
							<td style="width:400px;" class="text-center">
								<label>Eli Taboada Rojas</label>
								<div class="border-bottom-firma"></div>
							</td>
						</tr>

						<tr>
							<td style="width:400px;" class="text-center">Firma</td>
							<td style="width:400px;" class="text-center">Firma</td>
						</tr>
					</tbody>
				</table>
			</div> --}}
			
		
		<script type="text/php">
			if (isset($pdf))
			{
				$text = "{PAGE_NUM}";
				$size = 8;
				$font = $fontMetrics->getFont("Verdana");
				$x = 425;
				$y = $pdf->get_height() - 35;
				$pdf->page_text($x, $y, $text, $font, $size);
			}
		</script>
	</body>
	
</html>
