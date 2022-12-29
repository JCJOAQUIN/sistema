
@php

function generateTable($budget_id)
{
	$COFinancialConcept = App\COFinancialConcept::where('idUpload',$budget_id)->whereNull('parent')->get();
	$c = App\COFinancialConcept::where('idUpload',$budget_id)->whereNotNull('parent')->first();
	$table_body = "
		<div class='table-responsive'>
		<table id='table' class='table table-striped'>
			<thead class='thead-dark'>
				<tr>
					<th>CONCEPTO</th>
					";
		$anos = App\COFinancialMonth::where('idConcept',$c->id)->count();
		foreach (App\COFinancialMonth::where('idConcept',$c->id)->get() as $key => $value) {
			$table_body .="
					<th>$value->mes</th>
			";
		}
		$table_body .="
				</tr> 
			</thead>
			<tbody>
		";
		foreach ($COFinancialConcept as $bg)
		{
			if($bg->childrens()->count() > 0)
			{
				$table_body.= "
				<tr>
					<td colspan='4'>
						".
						generateInputForm(
						'text',//input type
						$bg->concept,//input value
						'',//input title
						"concept[$bg->id]")
						."
					</td>
				</tr>
				";
				foreach ($bg->childrens as $bg2) {
					$table_body .= "
					<tr>
					<td>
						".
							generateInputForm(
							'text',//input type
							$bg2->concept,//input value
							'',//input title
							"concept[$bg2->id]")
							."
					</td>
					";
					foreach (App\COFinancialMonth::where('idConcept',$bg2->id)->get() as $key => $ch) {
						$table_body .="
						<td>".
							generateInputForm(
							'decimal6',//input type
							$ch->amount,//input value
							'',//input title
							"amount[$ch->id]")
							."</td>
						";
					}
					$table_body .= "</tr>";
				}
			}
		}
		
		

		$table_body .= "
			</tbody>
		</table>
		</div>
		";


		return $table_body;
}

$COGeneralFinancial = App\COGeneralFinancial::where('idUpload',$budget_id)->first();
$namesGeneral = [
			'indicadoreconomicodereferencia' => [
				'name' => 'INDICADOR ECONOMICO DE REFERENCIA:',
				'type' => 'decimal6'
			],
			'puntosdeintermediaciondelabanca' => [
				'name' => 'PUNTOS DE INTERMEDIACIÓN DE LA BANCA:',
				'type' => 'decimal6'
			],
			'tasadeinteresdiaria' => [
				'name' => 'TASA DE INTERES DIARIA:',
				'type' => 'decimal6'
			],
			'diasparapagodeestimaciones' => [
				'name' => 'DIAS PARA PAGO DE ESTIMACIONES:',
				'type' => 'decimal6'
			],
			'aplicablealperiodo' => [
				'name' => '% APLICABLE AL PERIODO:',
				'type' => 'decimal6'
			],
			'porcentajedefinancieamiento' => [
				'name' => 'PORCENTAJE DE FINANCIEAMIENTO',
				'type' => 'decimal6'
			],
		];

		$names = [
			'custom' => true,
			'data' => generateTable($budget_id),
		];

$campos = [

	'COGeneralFinancial' => [
				'title' => 'ANÁLISIS DE LOS COSTOS DE FINANCIAMIENTO',
				'db' => $COGeneralFinancial,
				'names' =>	$namesGeneral,
			],
	'SobreCostoResumenIndirectos' => [
		'title' => 'RESUMEN DE COSTOS INDIRECTOS',
		'names' =>	$names,
	],

];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.finanCHorizontal',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
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
