
@php

function generateTable($budget_id)
{

	$COTechnicalStaffConcept = App\COTechnicalStaffConcept::where('idUpload',$budget_id)->whereNull('parent')->get();
	$c = App\COTechnicalStaffConcept::where('idUpload',$budget_id)->whereNotNull('total')->first();



		$table_body = "
		<div class='table-responsive'>
		<table id='table' class='table table-striped'>
			<thead class='thead-dark'>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					";

		foreach (App\COTechnicalStaffYear::where('idConcept',$c->id)->get() as $key => $value) {
			$table_body .="
					<th>$value->ano</th>
			";
		}
		
		$table_body .="
				</tr>
				<tr>
					<th width='20%'>AREA DE TRABAJO</th>
					<th width='20%'>CATEGORÍA</th>
					<th width='20%'>UNIDAD</th>
					<th>TOTAL</th>
					";
		$anos = App\COTechnicalStaffYear::where('idConcept',$c->id)->count();
		foreach (App\COTechnicalStaffYear::where('idConcept',$c->id)->get() as $key => $value) {
			$table_body .="
					<th>$value->mes</th>
			";
		}
		
		$table_body .="
				</tr> 
			</thead>
			<tbody>
		";

		foreach ($COTechnicalStaffConcept as $bg)
		{
			if($bg->childrens()->count() > 0)
			{
				$table_body.= "
				<tr>
					<td colspan='4'>
						".
						generateInputForm(
						'text',//input type
						$bg->category,//input value
						'',//input title
						"category[$bg->id]")
						."
					</td>
				</tr>
				";
				foreach ($bg->childrens as $bg2) {
					if($bg->childrens()->count() > 0)
					{
						$table_body.= "
						<tr>
							<td colspan='4'>
								".
								generateInputForm(
								'text',//input type
								$bg2->category,//input value
								'',//input title
								"category[$bg2->id]")
								."
							</td>
						</tr>
						";
						foreach ($bg2->childrens as $bg3) {
							$table_body .= "
							<tr>
								<td></td>
								<td>".
										generateInputForm(
										'text',//input type
										$bg3->category,//input value
										'',//input title
										"category[$bg3->id]")
									."</td>
								<td>
								"
								.
								generateInputForm(
								'decimal6',//input type
								$bg3->measurement,//input value
								'',//input title
								"measurement[$bg3->id]")
								."
								</td>
								<td>
								"
								.
								generateInputForm(
								'decimal6',//input type
								$bg3->total,//input value
								'',//input title
								"total[$bg3->id]")
								."
								</td>
								";
							foreach (App\COTechnicalStaffYear::where('idConcept',$bg3->id)->get() as $key => $ch) {
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
						}
					}
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

{!! Form::open(['route' => ['Sobrecosto.save.persTecnico',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
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
