
@php

	function generateDObraUnAnticipo($budget_id)
	{
		$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th >NUMERO</th>
						<th >ANTICIPOS</th>
						<th >PORCENTAJE</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COCAnAdvance::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->numero,//input value
							'',//input title
							"unAnticipoNumero[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->anticipos,//input value
							'',//input title
							"unAnticipoAnticipo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->porcentaje,//input value
							'',//input title
							"unAnticipoPorcentaje[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}
	function generateDObraDosAnticipo($budget_id)
	{
			$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>NUMERO</th>
						<th>ANTICIPOS</th>
						<th>PORCENTAJE</th>
						<th>PERIODO DE ENTREGA</th>
					</tr> 
				</thead>			
			<tbody>';
			foreach (App\COConstructionTwoAdvance::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->numero,//input value
							'',//input title
							"dosAnticipoNumero[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->anticipos,//input value
							'',//input title
							"dosAnticipoAnticipo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->porcentaje,//input value
							'',//input title
							"dosAnticipoPorcentaje[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->periododeentrega,//input value
							'',//input title
							"dosAnticipoPeriodoEntrega[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}
	function generateDObraRebasenEP($budget_id)
	{
		$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>NUMERO</th>
						<th>ANTICIPOS</th>
						<th>PORCENTAJE</th>
						<th>IMPORTE A EJERCER</th>
						<th>IMPORTE DE ANTICIPO</th>
						<th>PERIODO DE ENTREGA</th>
					</tr> 
				</thead>			
			<tbody>';
			foreach (App\COConstructionBudgetExceed::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->numero,//input value
							'',//input title
							"masAnticipoNumero[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->anticipos,//input value
							'',//input title
							"masAnticipoAnticipo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->porcentaje,//input value
							'',//input title
							"masAnticipoPorcentaje[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->importeaejercer,//input value
							'',//input title
							"masAnticipoImporteEjercer[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->importedeanticipo,//input value
							'',//input title
							"masAnticipoAnticipo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->periododeentrega,//input value
							'',//input title
							"masAnticipoPeriodoEntrega[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}
	function generateDObraAMIP($budget_id)
	{
			$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>ANTICIPOS</th>
						<th></th>
						<th>MONTO A EJERCER</th>
						<th></th>
						<th>IMPORTE DE ANTICIPOS</th>
						<th></th>
						<th>PERIODO DE ENTREGA</th>
					</tr> 
				</thead>			
			<tbody>';
			foreach (App\COConstructionAMIP::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->anticipo1,//input value
							'',//input title
							"DObraAMIPAnticipo1[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->anticipo2,//input value
							'',//input title
							"DObraAMIPAnticipo2[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->monto1,//input value
							'',//input title
							"DObraAMIPMonto1[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->monto2,//input value
							'',//input title
							"DObraAMIPMonto2[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->importe1,//input value
							'',//input title
							"DObraAMIPImporte1[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->importe2,//input value
							'',//input title
							"DObraAMIPImporte2[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->periodo,//input value
							'',//input title
							"DObraAMIPPeriodo[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>';
			return $table_body;
	}



	$COCValues = App\COCValues::where('idUpload',$budget_id)->first();

	$COCValuesThatApply = App\COCValuesThatApply::where('idUpload',$budget_id)->first();
	$COCRequiredValues = App\COCRequiredValues::where('idUpload',$budget_id)->first();
	$COCAdvanceTypeTable = App\COCAdvanceTypeTable::where('idUpload',$budget_id)->first();
	$COCSecondAdvanceTypeTable = App\COCSecondAdvanceTypeTable::where('idUpload',$budget_id)->first();


	$namesDObraValores = [
			'costodirectodelaobra' => [
				'name' => 'COSTO DIRECTO DE LA OBRA :',
				'type' => 'decimal6'
			],
			'importetotaldelamanodeobragravable' => [
				'name' => 'IMPORTE TOTAL DE LA MANO DE OBRA GRAVABLE :',
				'type' => 'decimal6'
			],
			'importetotaldelaobra' => [
				'name' => 'IMPORTE TOTAL DE LA OBRA:',
				'type' => 'decimal6'
			],
			'factorparalaobtenciondelasfp' => [
				'name' => 'FACTOR PARA LA OBTENCI??N DE LA SFP:',
				'type' => 'decimal6'
			],
			'porcentajedeutilidadbrutapropuesta' => [
				'name' => 'PORCENTAJE DE UTILIDAD BRUTA PROPUESTA:',
				'type' => 'decimal6'
			],
			'tasadeinteresusada' => [
				'name' => 'TASA DE INTER??S USADA:',
				'type' => 'decimal6'
			],
			'puntosdelbanco' => [
				'name' => 'PUNTOS DEL BANCO:',
				'type' => 'decimal6'
			],
			'indicadoreconomicodereferencia' => [
				'name' => 'INDICADOR ECON??MICO DE REFERENCIA:',
				'type' => 'text'
			],
			'isr' => [
				'name' => 'ISR (Impuesto Sobre la Renta):',
				'type' => 'decimal6'
			],
			'ptu' => [
				'name' => 'PTU (Participacion de trabajadores en la utilidad):',
				'type' => 'decimal6'
			],
	];
	$namesDObraValoresAplican = [
			'tipodeanticipo' => [
				'name' => 'TIPO DE ANTICIPO',
				'type' => 'select',
				'values' => [
					'Un ejercicio con un Anticipo',
					'Un ejercicio con 2 anticipos',
					'Rebasa un Ejercicio presupuestal'
				]
			],
			'modelodecalculodelfinanciamiento' => [
				'name' => 'MODELO DE CALCULO DEL FINANCIAMIENTO',
				'type' => 'select',
				'values' => [
					'Importe Total de Obra',
					'Costo Directo+Indirecto'
					]
			],
			'interesesaconsiderarenelfinanciamiento' => [
				'name' => 'INTERESES A CONSIDERAR EN EL FINANCIAMIENTO',
				'type' => 'select',
				'values' => [
					'Solo intereses negativos',
					'Ambos Interes (+ y -)',
				]
			],
			'tasaactiva' => [
				'name' => 'TIPO DE TASA',
				'type' => 'select',
				'values' => [
					'Tasa Activa = 6.6438 %',
					'Tasa Pasiva = 6.6438 %',
					' '
				]
			],
			'calculodelcargoadicional' => [
				'name' => 'CALCULO DEL CARGO ADICIONAL',
				'type' => 'select',
				'values' => [
					'Sobre el Importe de Estimaciones',
					'Sobre el Costo directo de la Obra',
				]
			],
			'diasaconsiderarenela??o' => [
				'name' => 'DIAS A CONSIDERAR EN EL A??O',
				'type' => 'select',
				'values' => [
					'A??o Fiscal (1 Ene al 31 Dic)',
					'A??o Comercial (360 Dias)',
				]
			],
	];
	$namesDObraValoresRequeridos = [
			'anticipoaproveedoresaliniciodeobra' => [
				'name' => 'ANTICIPO A PROVEEDORES AL INICIO DE OBRA:',
				'type' => 'decimal6'
			],
			'porcentajedeimpuestosobrenomina' => [
				'name' => 'PORCENTAJE DE IMPUESTO SOBRE N??MINA:',
				'type' => 'decimal6'
			],
			'presentaciondespuesdelcorte' => [
				'name' => 'Presentaci??n despues del corte:',
				'type' => 'number'
			],
			'revisionyautorizacion' => [
				'name' => 'Revisi??n y Autorizaci??n:',
				'type' => 'number'
			],
			'diasparaelpago' => [
				'name' => 'Dias para el pago:',
				'type' => 'number'
			],
			'totaldedias' => [
				'name' => 'Total de Dias:',
				'type' => 'number'
			],
			'periododecobroprimeraestimacion' => [
				'name' => 'PERIODO DE COBRO PRIMERA ESTIMACION:',
				'type' => 'number'
			],
			'periododeentregasegundoanticipo' => [
				'name' => 'PERIODO DE ENTREGA SEGUNDO ANTICIPO:',
				'type' => 'number'
			],
			'redondeoparaprogramadepersonaltecnico' => [
				'name' => 'Redondeo para Programa de Personal Tecnico:',
				'type' => 'number'
			],
			'presentaciondelprogramadepersonaltecnico' => [
				'name' => 'Presentacion del Programa de Personal T??cnico:',
				'type' => 'select',
				'values' => [
					'No. de Personas',
					'No. de Jornales',
					'Horas Hombre',
				]
			],
			'horasjornada' => [
				'name' => 'horas Jornada',
				'type' => 'number'
			],
	];

	$namesDObraUnAnticipo = [
		'custom' => true,
		'data' => generateDObraUnAnticipo($budget_id),
	];
	$namesDObraDosAnticipo = [
		'custom' => true,
		'data' => generateDObraDosAnticipo($budget_id),
	];
	$namesDObraRebasenEP = [
		'custom' => true,
		'data' => generateDObraRebasenEP($budget_id),
	];

	$namesDObraTDEAnticipo = [
			'costodirectodelaobra' => [
				'name' => 'COSTO DIRECTO DE LA OBRA:',
				'type' => 'decimal6'
			],
			'indirectodeobra' => [
				'name' => 'INDIRECTO DE OBRA:',
				'type' => 'decimal6'
			],
			'costodirectoindirecto' => [
				'name' => 'COSTO DIRECTO +INDIRECTO:',
				'type' => 'decimal6'
			],
			'montototaldelaobra' => [
				'name' => 'MONTO TOTAL DE LA OBRA:',
				'type' => 'decimal6'
			],
			'importeparafinanciamiento' => [
				'name' => 'IMPORTE PARA FINANCIAMIENTO:',
				'type' => 'decimal6'
			],
			'importeejercer1' => [
				'name' => 'IMPORTE EJERCER1:',
				'type' => 'decimal6'
			],
			'importeejercer2' => [
				'name' => 'IMPORTE EJERCER2:',
				'type' => 'decimal6'
			],
			
	];
	$namesDObraTDEAnticipoSegundo = [
			'periodosprogramados' => [
				'name' => 'PERIODOS PROGRAMADOS:',
				'type' => 'number'
			],
			'periodofinaldecobro' => [
				'name' => 'PERIODO FINAL DE COBRO:',
				'type' => 'number'
			],
			'periododeamortizacion2doanticipo' => [
				'name' => 'PERIODO DE AMORTIZACION 2do ANTICIPO:',
				'type' => 'number'
			],

			
	];

	$namesDObraAMIP = [
		'custom' => true,
		'data' => generateDObraAMIP($budget_id),
	];

	$campos = [
		'COCValues' => [
			'title' => 'VALORES DE LA OBRA',
			'db' => $COCValues,
			'names' =>	$namesDObraValores,
		],
		'COCValuesThatApply' => [
			'title' => 'ELIJA LOS VALORES QUE APLICAN',
			'db' => $COCValuesThatApply,
			'names' =>	$namesDObraValoresAplican,
		],
		'COCRequiredValues' => [
			'title' => 'ESCRIBA LOS VALORES REQUERIDOS',
			'db' => $COCRequiredValues,
			'names' =>	$namesDObraValoresRequeridos,
		],
		'COCAnAdvance' => [
			'title' => 'PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO',
			'names' =>	$namesDObraUnAnticipo,
		],
		'COConstructionTwoAdvance' => [
			'title' => 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL',
			'names' =>	$namesDObraDosAnticipo,
		],
		'COConstructionBudgetExceed' => [
			'title' => 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL',
			'names' =>	$namesDObraRebasenEP,
		],
		'COCAdvanceTypeTable' => [
			'title' => 'TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO',
			'db' => $COCAdvanceTypeTable,
			'names' =>	$namesDObraTDEAnticipo,
		],
		'COCSecondAdvanceTypeTable' => [
			'title' => 'TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO',
			'db' => $COCSecondAdvanceTypeTable,
			'names' =>	$namesDObraTDEAnticipoSegundo,
		],
		'COConstructionAMIP' => [
			'title' => '',
			'names' =>	$namesDObraAMIP,
		],
	];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.datosObra',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
<input name="save" value="true" hidden>
@foreach ($campos as $campo)
	<div class="margin_top">
		@component('components.labels.title-divisor', ["classEx" => "font-semibold"]) {{ $campo['title'] }} @endcomponent
		<center>
			<strong>{{ $campo['title'] }}</strong>
		</center>
		<div class='divisor'>
			<div class='gray-divisor'></div>
			<div class='orange-divisor'></div>
			<div class='gray-divisor'></div>
		</div>
	
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
