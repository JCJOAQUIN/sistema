@extends('layouts.child_module')

@section('data')
	@component('components.forms.form',["attributeEx" => "id=\"form-content\" method=\"POST\" action=\"".route('warehouse.store-massive')."\""])
		@component('components.labels.title-divisor') ALTA MASIVA @endcomponent
		<div class="alert alert-info" role="alert">
			<ul>
				<li>Por favor verifique que su información se encuentre estructurada como en su archivo CSV.</li>
				<li>Para continuar con el proceso dé clic en el botón «Continuar»</li>
			</ul>
		</div>
		@php
			$modelBody	= [];
			$body		= [];
			$modelHead	= [];
			foreach(array_keys(current($records)) as $headers)
			{
				$heads			= ["value" => $headers];
				$modelHead[]	= $heads; 
			}
			$modelHead[0]["classEx"]= "sticky inset-x-0";

			$arrayReplace			= [' ','-',',','$'];
			$oldFolio				= '';
			$oldTittle				= '';
			foreach ($records as $row)
			{
				$oldFolio			= $row['folio']		!= "" ? $row['folio'] 	: $oldFolio;
				$oldTittle			= $row['titulo']	!= "" ? $row['titulo']	: $oldTittle;
				$nameAccount		= !empty(trim($row['cuenta']))			? App\Account::find($row['cuenta'])			!= "" ? App\Account::find($row['cuenta'])->fullClasificacionName() : '---' : '---';
				$place				= !empty(trim($row['ubicacion_sede']))	? App\Place::find($row['ubicacion_sede'])	!= "" ? App\Place::find($row['ubicacion_sede'])->place : '---' : '---';
				$category			= !empty(trim($row['categoria']))		? App\CatWarehouseType::find($row['categoria'])	? App\CatWarehouseType::find($row['categoria'])->description : '---' : '---';
				$tmeasurement_id	= App\CatMeasurementTypes::where('description',$row['unidad'])->first() != "" ? App\CatMeasurementTypes::where('description',$row['unidad'])->first()->id : '';
				$valueFolio			= !empty(trim($oldFolio))					? $oldFolio													: '';
				$valueOldTittle		= !empty(trim($oldTittle))					? $oldTittle												: '';
				$valueID			= !empty(trim($row['id']))					? $row['id']												: '';
				$valueSede			= !empty(trim($row['ubicacion_sede'])) 		? $row['ubicacion_sede']									: '';
				$valueCategory		= !empty(trim($row['categoria']))			? $row['categoria']											: '';
				$valueAccount		= !empty(trim($row['cuenta']))				? $row['cuenta']											: '';
				$valueCodigo		= !empty(trim($row['codigo']))				? $row['codigo']											: '';
				$valueQuanty		= !empty(trim($row['cantidad']))			? $row['cantidad']											: '';
				$valueDamaged		= !empty(trim($row['danados']))				? $row['danados']											: '';
				$valueConcept		= !empty(trim($row['descripcion']))			? $row['descripcion']										: '';
				$valueUamount		= !empty(trim($row['precio_unitario'])) 	? str_replace($arrayReplace,'',$row['precio_unitario']) 	: '';
				$valueSubTotal		= !empty(trim($row['subtotal']))			? str_replace($arrayReplace,'',$row['subtotal'])			: '';
				$valueIva			= !empty(trim($row['iva']))					? str_replace($arrayReplace,'',$row['iva'])					: '';
				$valueTax			= !empty(trim($row['impuesto_adicional'])) 	? str_replace($arrayReplace,'',$row['impuesto_adicional'])	: '';
				$valueRetention		= !empty(trim($row['retenciones']))			? str_replace($arrayReplace,'',$row['retenciones'])			: '';
				$valueTotal			= !empty(trim($row['total']))				? str_replace($arrayReplace,'',$row['total'])				: '';

				$body = [
					[
						"classEx" => "sticky inset-x-0",
						"content"	=>
						[
							[
								"label" => !empty(trim($oldFolio)) ? $oldFolio : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"t_folio[]\" value=\"".$valueFolio."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($oldTittle))	? $oldTittle : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"t_title[]\" value=\"".$valueOldTittle."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['id']))	? $row['id'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"t_id[]\" value=\"".$valueID."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => $place
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tplace_id[]\" value=\"".$valueSede."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => $category
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tcategory_id[]\" value=\"".$valueCategory."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => $nameAccount
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"taccount_id[]\" value=\"".$valueAccount."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['codigo'])) ? $row['codigo'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tshort_code[]\" value=\"".$valueCodigo."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['cantidad'])) ? $row['cantidad'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tquanty[]\" value=\"".$valueQuanty."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['danados'])) ? $row['danados'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tdamaged[]\" value=\"".$valueDamaged."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['unidad'])) ? $row['unidad'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tmeasurement_id[]\" value=\"".$tmeasurement_id."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['descripcion'])) ? $row['descripcion'] : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tconcept_name[]\" value=\"".$valueConcept."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['precio_unitario'])) ? str_replace($arrayReplace,'',$row['precio_unitario']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tuamount[]\" value=\"".$valueUamount."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['subtotal'])) ? str_replace($arrayReplace,'',$row['subtotal']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tsub_total[]\" value=\"".$valueSubTotal."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['iva'])) ? str_replace($arrayReplace,'',$row['iva']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tiva[]\" value=\"".$valueIva."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['impuesto_adicional'])) ? str_replace($arrayReplace,'',$row['impuesto_adicional']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"\" value=\"".$valueTax."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['retenciones'])) ? str_replace($arrayReplace,'',$row['retenciones']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"\" value=\"".$valueRetention."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"label" => !empty(trim($row['total'])) ? str_replace($arrayReplace,'',$row['total']) : '---'
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"tamount[]\" value=\"".$valueTotal."\""
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",
		[
			"modelHead" => [$modelHead],
			"modelBody" => $modelBody,
		])
		@endcomponent
		<div class="flex justify-center mt-4">
			@component('components.buttons.button',["variant" => "secondary"])
				@slot('attributeEx')
					type="submit" name="send"
				@endslot
				CONTINUAR
			@endcomponent
		</div>
	@endcomponent
@endsection