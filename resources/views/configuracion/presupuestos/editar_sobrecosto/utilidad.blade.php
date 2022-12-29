
@php
	function generateTable($budget_id)
	{
		$COSummaryGeneralIndirect = App\CODeterminationUtility::where('idUpload',$budget_id)->get();

			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"CLAVE"],
					["value"	=>	"C O N C E P T O"],
					["value"	=>	"F O R M U L A"],
					["value"	=>	"IMPORTE"],
					["value"	=>	"%"]
				]
			];
			foreach($COSummaryGeneralIndirect as $bg)
			{
				$body	=
				[
					[
						"content"	=>
						[
							generateInputForm(
							'text',//input type
							$bg->clave,//input value
							'',//input title
							"clave[$bg->id]")
						],
					],
					[
						"content"	=>
						[
							generateInputForm(
							'text',//input type
							$bg->concepto,//input value
							'',//input title
							"concepto[$bg->id]")
						],
					],
					[
						"content"	=>
						[
							generateInputForm(
							'text',//input type
							$bg->formula,//input value
							'',//input title
							"formula[$bg->id]")
						],
					],
					[
						"content"	=>
						[
							generateInputForm(
							'decimal6',//input type
							$bg->importe,//input value
							'',//input title
							"importe[$bg->id]")
						],
					],
					[
						"content"	=>
						[
							generateInputForm(
							'decimal6',//input type
							$bg->porcentaje,//input value
							'',//input title
							"porcentaje[$bg->id]")
						],
					],
				];
				$modelBody[]	=	$body;
			}
		$table_body	= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])));
		
		/* $table_body =
		"
			<table class='table table-striped'>
				<thead class='thead-dark'>
					<tr>
						<th>CLAVE</th>
						<th>C O N C E P T O</th>
						<th>F O R M U L A</th>
						<th>IMPORTE</th>
						<th>%</th>
					</tr> 
				</thead>
				<tbody>
					<tr>";
				foreach ($COSummaryGeneralIndirect as $bg) {
					$table_body.="
					<td>
						".
						generateInputForm(
						'text',//input type
						$bg->clave,//input value
						'',//input title
						"clave[$bg->id]")

						."
						</td>
					<td>
						".
						generateInputForm(
						'text',//input type
						$bg->concepto,//input value
						'',//input title
						"concepto[$bg->id]")

						."
						</td>
					<td>
						".
						generateInputForm(
						'text',//input type
						$bg->formula,//input value
						'',//input title
						"formula[$bg->id]")

						."
						</td>
					<td>
						".
						generateInputForm(
						'decimal6',//input type
						$bg->importe,//input value
						'',//input title
						"importe[$bg->id]")

						."
						</td>
					<td>
						".
						generateInputForm(
						'decimal6',//input type
						$bg->porcentaje,//input value
						'',//input title
						"porcentaje[$bg->id]")

						."
						</td>
				</tr>";
				}
				$table_body .= "</tbody>
			</table>";
	*/
		return $table_body;
	}




	$names = [
		'custom' => true,
		'data' => generateTable($budget_id),
	];
	$campos = [
		'CODeterminationUtility' => [
			'title' => 'DETERMINACION DEL CARGO POR UTILIDAD ',
			'names' =>	$names,
		],
	];
@endphp

{!! Form::open(['route' => ['Sobrecosto.save.utilidad',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
	<input name="save" value="true" hidden>
	@foreach ($campos as $campo)
	<div class="margin_top">
		@component('components.labels.title-divisor', ["classEx" => "font-semibold"]) {{ $campo['title'] }} @endcomponent

		@if (array_key_exists('custom',$campo['names']))
			{!! $campo['names']['data'] !!}
		@else
			@component('components.containers.container-form')
				@foreach ($campo['names'] as $key => $value)
					<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
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
