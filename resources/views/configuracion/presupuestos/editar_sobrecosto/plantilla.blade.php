
@php
	function generateFactores($budget_id)
	{
		$COGeneralTemplate = App\COGeneralTemplate::where('idUpload',$budget_id)->first();
		
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["FACTORES", "FACTORES", "PORCENTAJE"];
		$body		=
		[
			[
				"content"	=>
				[
					generateInputForm
					(
						'decimal6',//input type
						$COGeneralTemplate->factor1,//input value
						'',//input title
						"factor1G"
					)
				],
			],
			[
				"content"	=>
				[
					generateInputForm
					(
						'decimal6',//input type
						$COGeneralTemplate->factor2,//input value
						'',//input title
						"factor2G"
					)
				],
			],
			[
				"content"	=>
				[
					generateInputForm
					(
						'decimal6',//input type
						$COGeneralTemplate->porcentaje,//input value
						'',//input title
						"porcentaje"
					)
				],
			]
		];
		$modelBody[]	=	$body;
		$table_body .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
		[
			"classEx"	=>	"table",
			"modelHead"	=>	$modelHead,
			"modelBody"	=>	$modelBody,
			"variant"	=>	"default"
		])));

/* 
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
			</table>"; */
		return $table_body;
	}

	function generatePersonalOC($budget_id)
	{
		$table_body .= "<div class=\"grid grid-cols-12\">";
			$COCentralStaffGeneralTemplate = App\COFieldStaffGeneralTemplate::where('idUpload',$budget_id)->first();
			$table_body	=	"";
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["MONTO TOTAL", "PORCENTAJE"];
			$body		=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->montototal,//input value
							'',//input title
							"GeneralCMontoTotal"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->porcentaje,//input value
							'',//input title
							"GeneralCPorcentaje"
						)
					],
				]
			];
			$modelBody[]	=	$body;
			$table_body 	.= "<div class=\"col-span-4\">";
				$table_body .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
				[
					"classEx"		=>	"table",
					"modelHead"		=>	$modelHead,
					"modelBody"		=>	$modelBody,
					"variant"		=>	"default"
				])));
			$table_body	.=	"</div>";

			$table_body	=	"";
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				["value"	=>	"GASTOS TECNICOS Y ADMINISTRATIVOS",	"show"	=>	"true"],
				["value"	=>	"CATEGORIAS",							"show"	=>	"true"],
				["value"	=>	"CANTIDAD",	"							show"	=>	"true"],
				["value"	=>	"SALARIO"],
				["value"	=>	"IMPORTE"],
				["value"	=>	"FACTORES"],
				["value"	=>	"FACTORES"],
			];
			foreach(App\COFieldStaffTemplate::where('idUpload',$budget_id)->get() as $bg)
			{
				$body	=
				[
					[
						"show"		=>	"true",
						"content"	=>
						[
							$bg->group ?
							generateInputForm
							(
								'text',//input type
								$bg->group,//input value
								'',//input title
								"group[$bg->id]"
							)
							: ''
						],
					],
					[
						"show"		=>	"true",
						"content"	=>
						[
							generateInputForm
							(
								'text',//input type
								$bg->category,//input value
								'',//input title
								"category[$bg->id]"
							)
						],
					],
					[
						"show"		=>	"true",
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->amount,//input value
								'',//input title
								"amount[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->salary,//input value
								'',//input title
								"salary[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->import,//input value
								'',//input title
								"import[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->factor1,//input value
								'',//input title
								"factor1[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->factor2,//input value
								'',//input title
								"factor2[$bg->id]"
							)
						],
					],
				];
				$modelBody[]	=	$body;
			}
			$table_body	.=	"<div class=\"col-span-8\">";
				$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
				[
					"classEx"		=>	"table",
					"modelHead"		=>	$modelHead,
					"modelBody"		=>	$modelBody,
					"variant"		=>	"default"
				])));
			$table_body	.=	"</div>";
		$table_body	.=	"</div>";
			/*
		$table_body .= "</div>";
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
			</table>"; */


		/* 
		$table_body .=
		'	
			<table class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th>GASTOS TECNICOS Y ADMINISTRATIVOS</th>
						<th>CATEGORIAS</th>
						<th>CANTIDAD</th>
						<th>SALARIO MENSUAL</th>
						<th>IMPORTE</th>
						<th>FACTORES</th>
						<th>FACTORES</th>
					</tr> 
				</thead>
			<tbody>';
			foreach (App\COFieldStaffTemplate::where('idUpload',$budget_id)->get() as $bg) {
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
			</table>'; */
		return $table_body;
	}

	function generatePersonalOCentral($budget_id)
	{

		$table_body .= "<div class=\"grid grid-cols-12\">";
			$COCentralStaffGeneralTemplate = App\COCentralStaffGeneralTemplate::where('idUpload',$budget_id)->first();
			$table_body	=	"";
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["MONTO TOTAL", "PORCENTAJE"];
			$body		=
			[
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->montototal,//input value
							'',//input title
							"GeneralCentralMontoTotal"
						)
					],
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'decimal6',//input type
							$COCentralStaffGeneralTemplate->porcentaje,//input value
							'',//input title
							"GeneralCentralPorcentaje"
						)
					],
				]
			];
			$modelBody[]	=	$body;
			$table_body	.=	"<div class=\"col-span-4\">";
				$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
				[
					"classEx"	=>	"table",
					"modelHead"	=>	$modelHead,
					"modelBody"	=>	$modelBody,
					"variant"	=>	"default"
				])));
			$table_body	.=	"</div>";

			$table_body	=	"";
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				["value"	=>	"GASTOS TECNICOS Y ADMINISTRATIVOS",	"show"	=>	"true"],
				["value"	=>	"CATEGORIAS",							"show"	=>	"true"],
				["value"	=>	"CANTIDAD",								"show"	=>	"true"],
				["value"	=>	"SALARIO MENSUAL"],
				["value"	=>	"IMPORTE"],
				["value"	=>	"FACTORES"],
				["value"	=>	"FACTORES"],
			];
			foreach(App\COCentralStaffTemplate::where('idUpload',$budget_id)->get() as $bg)
			{
				$body	=
				[
					[
						"show"		=>	"true",
						"content"	=>
						[
							$bg->group ?
							generateInputForm
							(
								'text',//input type
								$bg->group,//input value
								'',//input title
								"CentraLgroup[$bg->id]"
							)
							: ''
						],
					],
					[
						"show"		=>	"true",
						"content"	=>
						[
							generateInputForm
							(
								'text',//input type
								$bg->category,//input value
								'',//input title
								"CentraLcategory[$bg->id]"
							)
						],
					],
					[
						"show"		=>	"true",
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->amount,//input value
								'',//input title
								"CentraLamount[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->salary,//input value
								'',//input title
								"CentraLsalary[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->import,//input value
								'',//input title
								"CentraLimport[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->factor1,//input value
								'',//input title
								"CentraLfactor1[$bg->id]"
							)
						],
					],
					[
						"content"	=>
						[
							generateInputForm
							(
								'decimal6',//input type
								$bg->factor2,//input value
								'',//input title
								"CentraLfactor2[$bg->id]"
							)
						],
					],
				];
				$modelBody[]	=	$body;
			}
			$table_body	.=	"<div class=\"col-span-8\">";
				$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
				[
					"attributeEx"	=>	"id=\"table\"",
					"classEx"		=>	"table",
					"modelHead"		=>	$modelHead,
					"modelBody"		=>	$modelBody,
					"variant"		=>	"default"
				])));
			$table_body	.=	"</div>";
		$table_body	.=	"</div>";





		/* $COCentralStaffGeneralTemplate = App\COCentralStaffGeneralTemplate::where('idUpload',$budget_id)->first();
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
						<th>CATEGORIAS</th>
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
			</table>'; */
			return $table_body;
	}

	function generateListadoCentral($budget_id)
	{
		$COCentralStaffListTemplate = App\COCentralStaffListTemplate::where('idUpload',$budget_id)->get();
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["", ""];
		foreach($COFieldStaffListTemplate as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						$bg->group ?
						generateInputForm
						(
							'text',//input type
							$bg->group,//input value
							'',//input title
							"groupCentral[$bg->id]"
						)
						: ''
					]
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'text',//input type
							$bg->category,//input value
							'',//input title
							"categoryCentral[$bg->id]"
						)
					]
				]
			];
			$modelBody[]	=	$body;
		}
		$table_body	.=	html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
		[
			"classEx"	=>	"table",
			"modelHead"	=>	$modelHead,
			"modelBody"	=>	$modelBody,
			"variant"	=>	"default"
		])));

		/* $COCentralStaffListTemplate = App\COCentralStaffListTemplate::where('idUpload',$budget_id)->get();
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
			</table>"; */
			return $table_body;
	}
	function generateListadoCampo($budget_id)
	{
		$COFieldStaffListTemplate = App\COFieldStaffListTemplate::where('idUpload',$budget_id)->get();
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["", ""];
		foreach($COFieldStaffListTemplate as $bg)
		{
			$body	=
			[
				[
					"content"	=>
					[
						$bg->group ?
						generateInputForm
						(
							'text',//input type
							$bg->group,//input value
							'',//input title
							"groupC[$bg->id]"
						)
						: ''
					]
				],
				[
					"content"	=>
					[
						generateInputForm
						(
							'text',//input type
							$bg->category,//input value
							'',//input title
							"categoryC[$bg->id]"
						)
					]
				]
			];
			$modelBody[]	=	$body;
		}
		$table_body .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable",
		[
			"classEx"	=>	"table",
			"modelHead"	=>	$modelHead,
			"modelBody"	=>	$modelBody,
			"variant"	=>	"default"
		])));




		/* $COFieldStaffListTemplate = App\COFieldStaffListTemplate::where('idUpload',$budget_id)->get();
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
			</table>"; */
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
