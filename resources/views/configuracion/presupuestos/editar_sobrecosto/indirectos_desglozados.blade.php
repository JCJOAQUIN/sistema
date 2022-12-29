
@php

	function generateFactores($budget_id)
	{
		$COIndirectItemizedGeneral	=	App\COIndirectItemizedGeneral::where('idUpload',$budget_id)->first();
		$COIndirectItemizedConcept	=	App\COIndirectItemizedConcept::where('idUpload',$budget_id)->get();

		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["MONTO DE LA OBRA A COSTO DIRECTO $ ", "TOTALES", "%  INDIRECTO"];
			$body	=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COIndirectItemizedGeneral->montoobra,//input value
							'',//input title
							"montoobra"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COIndirectItemizedGeneral->totales,//input value
							'',//input title
							"totales"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COIndirectItemizedGeneral->indirecto,//input value
							'',//input title
							"indirecto"
						)
					],
				],
			];
			$modelBody[]	=	$body;

		$table_body	=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.alwaysVisibleTable',
		[
			"modelHead"	=>	$modelHead,
			"modelBody"	=>	$modelBody
		])));
		/* $table_body =
		"
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>MONTO DE LA OBRA A COSTO DIRECTO $ </th>
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
							$COIndirectItemizedGeneral->montoobra,//input value
							'',//input title
							"montoobra")

							."
							</td>
						<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COIndirectItemizedGeneral->totales,//input value
							'',//input title
							"totales")

							."
							</td>
						<td>
							".
							generateInputForm(
							'decimal6',//input type
							$COIndirectItemizedGeneral->indirecto,//input value
							'',//input title
							"indirecto")
							."
							</td>
					</tr>
				</tbody>
			</table>"; */

		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"CONCEPTO"],
				["value"	=>	"MONTO"],
				["value"	=>	"PORCENTAJE"],
				["value"	=>	"MONTO"],
				["value"	=>	"PORCENTAJE"]
			]
		];
		foreach($COIndirectItemizedConcept as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'text',//input type
							$bg->concepto,//input value
							'',//input title
							"concepto[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->monto1,//input value
							'',//input title
							"monto1[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->porcentaje1,//input value
							'',//input title
							"porcentaje1[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->monto2,//input value
							'',//input title
							"monto2[$bg->id]"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$bg->porcentaje2,//input value
							'',//input title
							"porcentaje2[$bg->id]"
						)
					],
				],
			];
			$modelBody[]	=	$body;
		}
		$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.table',
		[
			"modelHead"	=>	$modelHead,
			"modelBody"	=>	$modelBody
		])));

		/* 	$table_body .= "
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>CONCEPTO</th>
						<th>MONTO</th>
						<th>PORCENTAJE</th>
						<th>MONTO</th>
						<th>PORCENTAJE</th>
					</tr> 
				</thead>
				<tbody>
			";
			foreach ($COIndirectItemizedConcept as $bg)
			{
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
					$bg->porcentaje2,//input value
					'',//input title
					"porcentaje2[$bg->id]")

					."
					</td>
				</tr>
				";
			}

			$table_body .= "
				</tbody>
			</table>
			";
	*/

		return $table_body;
	}




	$namesFactores	=
	[
		'custom'	=>	true,
		'data'		=>	generateFactores($budget_id),
	];

	$campos = [

		'SobreCostoFactoes' =>
		[
			'title'	=>	'DESGLOSE DE COSTOS INDIRECTOS',
			'names'	=>	$namesFactores,
		],

	];

@endphp


{!! Form::open(['route' => ['Sobrecosto.save.indirectosDesglosados',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
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
{{-- <center>
<button type="submit" class="btn btn-red">Siguiente</button>
</center> --}}
{!! Form::close() !!}
