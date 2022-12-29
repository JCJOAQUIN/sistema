
@php

function generateTable($budget_id)
{
	$COSummaryGeneralIndirect = App\COSummaryGeneralIndirect::where('idUpload',$budget_id)->first();
	$COSummaryIndirectConcept = App\COSummaryIndirectConcept::where('idUpload',$budget_id)->get();
	$table_body =
	"
		<table class='table table-striped'>
			<thead class='thead-dark'>
				<tr>
					<th>MONTO DE LA OBRA A COSTO DIRECTO :</th>
					<th>TOTALES</th>
					<th>%  INDIRECTO</th>
				</tr> 
			</thead>
			<tbody>
				<tr>
					<td>
						".
						generateInputForm(
						'decimal6',//input type
						$COSummaryGeneralIndirect->montoobra,//input value
						'',//input title
						"montoobra")

						."
						</td>
					<td>
						".
						generateInputForm(
						'decimal6',//input type
						$COSummaryGeneralIndirect->totales,//input value
						'',//input title
						"totales")

						."
						</td>
					<td>
						".
						generateInputForm(
						'decimal6',//input type
						$COSummaryGeneralIndirect->indirecto,//input value
						'',//input title
						"indirecto")

						."
						</td>
				</tr>
			</tbody>
		</table>";


		$table_body .= "
		<table class='table table-striped'>
			<thead class='thead-dark'>
				<tr>
					<th></th>
					<th>ADMINISTRACION OFICINA CENTRAL</th>
					<th></th>
					<th>ADMINISTRACION DE CAMPO</th>
					<th></th>
					<th>TOTALES</th>
					<th></th>
				</tr> 
				<tr>
					<th>CONCEPTO</th>
					<th>MONTO</th>
					<th>PORCENTAJE</th>
					<th>MONTO</th>
					<th>PORCENTAJE</th>
					<th>MONTO</th>
					<th>PORCENTAJE</th>
				</tr> 
			</thead>
			<tbody>
		";

		foreach ($COSummaryIndirectConcept as $bg) {
			$table_body .= "
			<tr>
				<td>
				"
				.
				generateInputForm(
				'text',//input type
				$bg->concepto,//input value
				'',//input title
				"concepto[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->monto1,//input value
				'',//input title
				"monto1[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->porcentaje1,//input value
				'',//input title
				"porcentaje1[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->monto2,//input value
				'',//input title
				"monto2[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->porcentaje2, //input value
				'',//input title
				"porcentaje2[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->montototal,//input value
				'',//input title
				"montototal[$bg->id]")

				."
				</td>
				<td>
				"
				.
				generateInputForm(
				'decimal6',//input type
				$bg->porcentajetotal,//input value
				'',//input title
				"porcentajetotal[$bg->id]")

				."
				</td>
			</tr>
			";
		}

		$table_body .= "
			</tbody>
		</table>
		";


		return $table_body;
}




$names = [
	'custom' => true,
	'data' => generateTable($budget_id),
];

$campos = [

	'SobreCostoResumenIndirectos' => [
		'title' => 'RESUMEN DE COSTOS INDIRECTOS',
		'names' =>	$names,
	],

];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.resumenIndirectos',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
<input name="save" value="true" hidden>
@foreach ($campos as $campo)
<div class="margin_top">
	@component('components.labels.title-divisor', ["classEx" => "font-semibold"]) {{ $campo['title'] }} @endcomponent

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
