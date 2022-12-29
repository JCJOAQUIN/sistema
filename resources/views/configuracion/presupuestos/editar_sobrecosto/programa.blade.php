
@php
	function generatePeriodo($budget_id)
	{
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"PROGRAMADO"],
				["value"	=>	"TITULO"],
				["value"	=>	"DIAS NATURALES"],
				["value"	=>	"DIAS TOTALES"],
				["value"	=>	"FACTOR"],
				["value"	=>	"AÑO"],
				["value"	=>	"IMPORTE DEL PERIODO"]
			]
		];
		foreach(App\COPeriodProgram::where('idUpload',$budget_id)->get() as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'number',//input type
							$bg->programado,//input value
							'',//input title
							"PeriodOprogramado[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'text',//input type
							$bg->titulo,//input value
							'',//input title
							"PeriodOtitulo[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'number',//input type
							$bg->diasnaturales,//input value
							'',//input title
							"PeriodOdiasnaturales[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'number',//input type
							$bg->diastotales,//input value
							'',//input title
							"PeriodOdiastotales[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->factorano,//input value
							'',//input title
							"PeriodOfactorano[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'number',//input type
							$bg->ano,//input value
							'',//input title
							"PeriodOano[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->importedelperiodo,//input value
							'',//input title
							"PeriodOimportedelperiodo[$bg->id]"
						)
					],
				]
			];
			$modelBody[]	=	$body;
		}
		$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
		[
			"attributeEx"	=>	"id=\"table\"",
			"classEx"		=>	"table",
			"modelHead"		=>	$modelHead,
			"modelBody"		=>	$modelBody
		])));
		/* $table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>PROGRAMADO</th>
						<th>TITULO</th>
						<th>DIAS NATURALES</th>
						<th>DIAS TOTALES</th>
						<th>FACTOR</th>
						<th>AÑO</th>
						<th>IMPORTE DEL PERIODO</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COPeriodProgram::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->programado,//input value
							'',//input title
							"PeriodOprogramado[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'text',//input type
							$bg->titulo,//input value
							'',//input title
							"PeriodOtitulo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->diasnaturales,//input value
							'',//input title
							"PeriodOdiasnaturales[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->diastotales,//input value
							'',//input title
							"PeriodOdiastotales[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->factorano,//input value
							'',//input title
							"PeriodOfactorano[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'number',//input type
							$bg->ano,//input value
							'',//input title
							"PeriodOano[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->importedelperiodo,//input value
							'',//input title
							"PeriodOimportedelperiodo[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>'; */
		return $table_body;
	}
	function generateCostoPeriodo($budget_id)
	{
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"TOTAL COSTO DIRECTO"],
				["value"	=>	"COSTO MATERIALES"],
				["value"	=>	"COSTO MANO DE OBRA"],
				["value"	=>	"COSTO EQUIPO"],
				["value"	=>	"COSTO OTROS INSUMOS"]
			]
		];
		foreach(App\COCostPeriodProgram::where('idUpload',$budget_id)->get() as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->totalcostodirecto,//input value
							'',//input title
							"CostoPeriodOtotalcostodirecto[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->costomateriales,//input value
							'',//input title
							"CostoPeriodOcostomateriales[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->costomanodeobra,//input value
							'',//input title
							"CostoPeriodOcostomanodeobra[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->costoequipo,//input value
							'',//input title
							"CostoPeriodOcostoequipo[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->costootrosinsumos,//input value
							'',//input title
							"CostoPeriodOcostootrosinsumos[$bg->id]"
						)
					],
				]
			];
			$modelBody[]	=	$body;
		}
		$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
		[
			"attributeEx"	=>	"id=\"table\"",
			"classEx"		=>	"table",
			"modelHead"		=>	$modelHead,
			"modelBody"		=>	$modelBody
		])));



/* 
		$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>TOTAL COSTO DIRECTO</th>
						<th>COSTO MATERIALES</th>
						<th>COSTO MANO DE OBRA</th>
						<th>COSTO EQUIPO</th>
						<th>COSTO OTROS INSUMOS</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COCostPeriodProgram::where('idUpload',$budget_id)->get() as $bg)
			{
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->totalcostodirecto,//input value
							'',//input title
							"CostoPeriodOtotalcostodirecto[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->costomateriales,//input value
							'',//input title
							"CostoPeriodOcostomateriales[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->costomanodeobra,//input value
							'',//input title
							"CostoPeriodOcostomanodeobra[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->costoequipo,//input value
							'',//input title
							"CostoPeriodOcostoequipo[$bg->id]")
						."</td>
					<td>".
						generateInputForm(
							'decimal6',//input type
							$bg->costootrosinsumos,//input value
							'',//input title
							"CostoPeriodOcostootrosinsumos[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>'; */
			return $table_body;
	}
	function generateAvance($budget_id)
	{
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["PARCIAL", "ACUMULADO"];
		foreach(App\COAdvanceProgram::where('idUpload',$budget_id)->get() as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal5',//input type
							$bg->parcial,//input value
							'',//input title
							"AvancEparcial[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal5',//input type
							$bg->acumulado,//input value
							'',//input title
							"AvancEacumulado[$bg->id]"
						)
					],
				]
			];
			$modelBody[]	=	$body;
		}
		$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
		[
			"attributeEx"	=> "id=\"table\"",
			"classEx"		=>	"table",
			"modelHead"		=>	$modelHead,
			"modelBody"		=>	$modelBody,
			"variant"		=>	"default"
		])));


	/* 	
		$table_body =
		'	
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>PARCIAL</th>
						<th>ACUMULADO</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COAdvanceProgram::where('idUpload',$budget_id)->get() as $bg) {
				$table_body .= 
				"<tr>
					<td>".
						generateInputForm(
							'decimal5',//input type
							$bg->parcial,//input value
							'',//input title
							"AvancEparcial[$bg->id]")
					."</td>
					<td>".
						generateInputForm(
							'decimal5',//input type
							$bg->acumulado,//input value
							'',//input title
							"AvancEacumulado[$bg->id]")
						."</td>
					</tr>";
			}
			$table_body .=
			'</tbody>
			</table>'; */
			return $table_body;
	}





	$namesPeriodo = [
		'custom' => true,
		'data' => generatePeriodo($budget_id),
	];
	$namesCostoPeriodo = [
		'custom' => true,
		'data' => generateCostoPeriodo($budget_id),
	];
	$namesAvance = [
		'custom' => true,
		'data' => generateAvance($budget_id),
	];


	
	$campos = [

		'COPeriodProgram' => [
			'title' => 'PERIODO',
			'names' =>	$namesPeriodo,
		],
		'SobreCostoProgramaCosto' => [
			'title' => 'COSTO DIRECTO POR PERIODO',
			'names' =>	$namesCostoPeriodo,
		],
		'COAdvanceProgram' => [
			'title' => '% DE AVANCE	',
			'names' =>	$namesAvance,
		],
	];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.programa',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
	<input name="save" value="true" hidden>
	@foreach ($campos as $campo)
		<div class="margin_top">
			@component('components.labels.title-divisor', ["classEx" => "font-semibold"]) {{ $campo['title'] }} @endcomponent
			@if (array_key_exists('custom',$campo['names']))
				{!! $campo['names']['data'] !!}
			@else
			@component('components.containers.container-form')
				@foreach ($campo['names'] as $key => $value)
					<div class='col-span-4 md:col-start-2 md:col-span-2 md:col-end-4'>
						{!! generateInputForm(
								$value['type'],//input type
								$campo['db'][$key],//input value
								$value['name'],//input title
								$key,//input name and db name
								array_key_exists('values',$value) ? $value['values'] : []//select values
							)
						!!}
					</div>
				@endforeach
			@endcomponent
			@endif
		</div>
	@endforeach
	<div class="text-center mt-6">
		@component('components.buttons.button', ["variant" => "success"])
			@slot('label')
				Siguiente
			@endslot
			@slot('attributeEx')
				type="submit"
			@endslot	
		@endcomponent
	</div>
{{-- 
<center>
	<button type="submit" class="btn btn-red">Siguiente</button>
</center> --}}
{!! Form::close() !!}
