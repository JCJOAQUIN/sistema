
@php

	function generateFactores($budget_id)
	{
		$COGeneralTemplate = App\COGeneralTemplate::where('idUpload',$budget_id)->first();
		$table_body =
		"
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>FACTORES</th>
						<th>FACTORES</th>
						<th>PORCENTAJE</th>
					</tr> 
				</thead>
				<tbody>
					<tr>
						<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COGeneralTemplate->factor1,//input value
							'',//input title
							"factor1G")

							."
							</td>
						<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COGeneralTemplate->factor2,//input value
							'',//input title
							"factor2G")

							."
							</td>
						<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COGeneralTemplate->porcentaje,//input value
							'',//input title
							"porcentaje")

							."
							</td>
					</tr>
				</tbody>
			</table>";
			return $table_body;
	}

	function generatePersonalOC($budget_id)
	{
		$COCentralStaffGeneralTemplate = App\COFieldStaffGeneralTemplate::where('idUpload',$budget_id)->first();
		$table_body = "<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>MONTO TOTAL</th>
						<th>PORCENTAJE</th>
					</tr> 
				</thead>
			<tbody>
				<tr>
					<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->montototal,//input value
							'',//input title
							"GeneralCMontoTotal")

							."
					</td>
					<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->porcentaje,//input value
							'',//input title
							"GeneralCPorcentaje")

							."
					</td>
				</tr>
			</tbody>
			</table>";
		$table_body .=
		'	
			<table class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>GASTOS TECNICOS Y ADMINISTRATIVOS</th>
						<th>CATEGORÍAS</th>
						<th>CANTIDAD</th>
						<th>SALARIO MENSUAL</th>
						<th>IMPORTE</th>
						<th>FACTORES</th>
						<th>FACTORES</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COFieldStaffTemplate::where('idUpload',$budget_id)->get() as $bg)
			{
				$table_body .= 
				"<tr>
					<td>".
						(
							$bg->group ?
							generateInputForm(
							'text',//input type
							$bg->group,//input value
							'',//input title
							"group[$bg->id]")
							: ''
						)
						
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->category,//input value
							'',//input title
							"category[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->amount,//input value
							'',//input title
							"amount[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->salary,//input value
							'',//input title
							"salary[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->import,//input value
							'',//input title
							"import[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->factor1,//input value
							'',//input title
							"factor1[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->factor2,//input value
							'',//input title
							"factor2[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}

	function generatePersonalOCentral($budget_id)
	{
		$COCentralStaffGeneralTemplate = App\COCentralStaffGeneralTemplate::where('idUpload',$budget_id)->first();
		$table_body = "<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>MONTO TOTAL</th>
						<th>PORCENTAJE</th>
					</tr> 
				</thead>
			<tbody>
				<tr>
					<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->montototal,//input value
							'',//input title
							"GeneralCentralMontoTotal")

							."
					</td>
					<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->porcentaje,//input value
							'',//input title
							"GeneralCentralPorcentaje")

							."
					</td>
				</tr>
			</tbody>
			</table>";
		$table_body .=
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>GASTOS TECNICOS Y ADMINISTRATIVOS</th>
						<th>CATEGORÍAS</th>
						<th>CANTIDAD</th>
						<th>SALARIO MENSUAL</th>
						<th>IMPORTE</th>
						<th>FACTORES</th>
						<th>FACTORES</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COCentralStaffTemplate::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						(
							$bg->group ?
							generateInputForm(
							'text',//input type
							$bg->group,//input value
							'',//input title
							"CentraLgroup[$bg->id]")
							: ''
						)
						
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->category,//input value
							'',//input title
							"CentraLcategory[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->amount,//input value
							'',//input title
							"CentraLamount[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->salary,//input value
							'',//input title
							"CentraLsalary[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->import,//input value
							'',//input title
							"CentraLimport[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->factor1,//input value
							'',//input title
							"CentraLfactor1[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->factor2,//input value
							'',//input title
							"CentraLfactor2[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}

	function generateListadoCentral($budget_id)
	{

		$COCentralStaffListTemplate = App\COCentralStaffListTemplate::where('idUpload',$budget_id)->get();
		$table_body =
		"
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th></th>
						<th></th>
					</tr> 
				</thead>
				<tbody>
					";
			foreach ($COCentralStaffListTemplate as $bg) {
				$table_body .=
				"<tr>
					<td>".
						(
							$bg->group 
							? generateInputForm(
								'text',//input type
								$bg->group,//input value
								'',//input title
								"groupCentral[$bg->id]")
							: ''
						)
						."
						</td>
					<td>
						".
						generateInputForm(
						'text',//input type
						$bg->category,//input value
						'',//input title
						"categoryCentral[$bg->id]")

						."
					</td>
				</tr>";
			}
			$table_body .= "
				</tbody>
			</table>";
			return $table_body;
	}
	function generateListadoCampo($budget_id)
	{

		$COFieldStaffListTemplate = App\COFieldStaffListTemplate::where('idUpload',$budget_id)->get();
		$table_body =
		"
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th></th>
						<th></th>
					</tr> 
				</thead>
				<tbody>
					";
			foreach ($COFieldStaffListTemplate as $bg) {
				$table_body .=
				"<tr>
					<td>".
						(
							$bg->group 
							? generateInputForm(
								'text',//input type
								$bg->group,//input value
								'',//input title
								"groupC[$bg->id]")
							: ''
						)
						."
						</td>
					<td>
						".
						generateInputForm(
						'text',//input type
						$bg->category,//input value
						'',//input title
						"categoryC[$bg->id]")

						."
					</td>
				</tr>";
			}
			$table_body .= "
				</tbody>
			</table>";
			return $table_body;
	}




	$namesFactores = [
		'custom' => true,
		'data' => generateFactores($budget_id),
	];
	$namesPersonalOC = [
		'custom' => true,
		'data' => generatePersonalOC($budget_id),
	];
	$namesPersonalOCentral = [
		'custom' => true,
		'data' => generatePersonalOCentral($budget_id),
	];
	$namesPersonalLCentral = [
		'custom' => true,
		'data' => generateListadoCentral($budget_id),
	];
	$namesPersonalLCampo = [
		'custom' => true,
		'data' => generateListadoCampo($budget_id),
	];


	$campos = [

		'SobreCostoFactoes' => [
			'title' => 'Factores',
			'names' =>	$namesFactores,
		],
		'SobreCostoPersonalOC' => [
			'title' => 'PERSONAL DE OFICINA DE CAMPO',
			'names' =>	$namesPersonalOC,
		],
		'SobreCostoPersonalOCentral' => [
			'title' => 'PERSONAL DE OFICINA CENTRAL',
			'names' =>	$namesPersonalOCentral,
		],
		'SobreCostoPersonalListadoC' => [
			'title' => 'LISTADO DE PERSONAL DE CAMPO',
			'names' =>	$namesPersonalLCampo,
		],
		'SobreCostoPersonalListadoCentral' => [
			'title' => 'LISTADO DE PERSONAL DE OFICINA CENTRAL',
			'names' =>	$namesPersonalLCentral,
		],
	];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.plantilla',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
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
