
@php

function generateTable($budget_id)
{

	$COEnterpriseDocument = App\COEnterpriseDocument::where('idUpload',$budget_id)->get();
	$table_body =
	"
		<table class='table table-striped'>
			<thead class='thead-dark'>
				<tr>
					<th>PARAMETRO GENERAL</th>
					<th>PARAMETRO ESPECIFICO</th>
					";
				foreach ($COEnterpriseDocument as $bg) {
					$table_body .="
					<th>".
							generateInputForm(
							'text',//input type
							$bg->name,//input value
							'',//input title
							"EmpresaName[$bg->id]")
				."</th>";
				}
	$table_body .="
			</tr> 
			</thead>
			<tbody>";
	$table_body .="
				<tr>
					<td rowspan='3'>TIPO DE ANTICIPO</td>
					<td >Un ejercicio con un Anticipo</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->unanticipo,//input value
											'',//input title
											"unanticipo[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>Un ejercicio con 2 anticipos</td>";
					foreach ($COEnterpriseDocument as $bg)
					{
						$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
										generateInputForm(
										'checkbox',//input type
										$c->dosanticipo,//input value
										'',//input title
										"dosanticipo[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";
	$table_body .=
				"<tr>
					<td>Rebasa un Ejercicio presupuestal</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->rebasa,//input value
											'',//input title
											"rebasa[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

$table_body .="
				<tr>
					<td rowspan='2'>  MODELO DE CALCULO DEL FINANCIAMIENTO</td>
					<td >Importe Total de Obra</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COFinancingCalcDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->importetotal,//input value
											'',//input title
											"importetotal[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>Costo Directo+Indirecto</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COFinancingCalcDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->costodirectoindirecto,//input value
											'',//input title
											"costodirectoindirecto[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";


$table_body .="
				<tr>
					<td rowspan='4'>INTERESES A CONSIDERAR EN EL FINANCIAMIENTO</td>
					<td >Solo intereses negativos</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->negativos,//input value
											'',//input title
											"negativos[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>Ambos Interes (+ y -)</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->ambos,//input value
											'',//input title
											"ambos[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";
	$table_body .=
				"<tr>
					<td>Tasa Activa</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->tasaactiva,//input value
											'',//input title
											"tasaactiva[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";
	$table_body .=
				"<tr>
					<td>Tasa Pasiva</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->tasapasiva,//input value
											'',//input title
											"tasapasiva[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";



$table_body .="
				<tr>
					<td rowspan='2'>CALCULO DEL CARGO ADICIONAL</td>
					<td >Sobre el Importe de Estimaciones</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COAdditionalChargeCalcDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->sobreelimporte,//input value
											'',//input title
											"sobreelimporte[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>Sobre el Costo directo de la Obra</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COAdditionalChargeCalcDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->costodirecto,//input value
											'',//input title
											"costodirecto[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";




$table_body .="
				<tr>
					<td rowspan='2'>DIAS A CONSIDERAR EN EL AÑO</td>
					<td >Año Fiscal (1 Ene al 31 Dic)</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\CODaysToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->anofiscal,//input value
											'',//input title
											"anofiscal[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>Año Comercial (360 Dias)</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\CODaysToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->anocomercial,//input value
											'',//input title
											"anocomercial[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";



$table_body .="
				<tr>
					<td rowspan='2'>DIAS A CONSIDERAR EN EL AÑO</td>
					<td >CA= Sub / (1-0.005) - Sub</td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COThousandDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->casub,//input value
											'',//input title
											"casub[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

	$table_body .=
				"<tr>
					<td>CA= CA1* Sub </td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\COThousandDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'checkbox',//input type
											$c->caca,//input value
											'',//input title
											"caca[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";


$table_body .="
				<tr>
					<td>DIAS DE PAGO P/ESTIMACIONES</td>
					<td></td>";
					foreach ($COEnterpriseDocument as $bg) {
						$c = App\CODaysToPayDocument::where('idDocEmpresa',$bg->id)->first();
						$table_body.="<td>".
											generateInputForm(
											'number',//input type
											$c->dias,//input value
											'',//input title
											"dias[$c->id]")
								."</td>";
					}
					$table_body .="
				</tr>";

			$table_body .= "</tbody>
		</table>";


		return $table_body;
}




$names = [
	'custom' => true,
	'data' => generateTable($budget_id),
];

$campos = [

	'COSummaryConcept' => [
		'title' => 'RESUMEN ',
		'names' =>	$names,
	],

];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.documentacion',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
<input name="save" value="true" hidden>
@foreach ($campos as $campo)
<div class="margin_top">
	@component('components.labels.title-divisor') {{ $campo['title'] }} @endcomponent

	@if (array_key_exists('custom',$campo['names']))
		{!! $campo['names']['data'] !!}
	@else
		@foreach ($campo['names'] as $key => $value)
			<div class='container-blocks'>
				<div class='search-table-center'>
					{!! generateInputForm(
						$value['type'],//input type
						$campo['db'][$key],//input value
						$value['name'],//input title
						$key,//input name and db name
						array_key_exists('values',$value) ? $value['values'] : []//select values
						)
					!!}
				</div>
			</div>
		@endforeach
	@endif
</div>
@endforeach
<center>
<button type="submit" class="btn btn-red">Siguiente</button>
</center>
{!! Form::close() !!}
