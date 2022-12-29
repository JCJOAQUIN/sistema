<div id="machine-table" @if(isset($request) && in_array($request->requisition->requisition_type,[1,2,3,4,6])) style="display: none;"  @elseif(!isset($request)) style="display: none;" @endif class="justify-center">
	@component('components.labels.title-divisor') 
		CARGA MASIVA (OPCIONAL)
	@endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		@slot("slot")
			@component("components.labels.label")
				Si desea cargar conceptos de forma masiva para esta requisición, utilice la siguiente plantilla. 
				@component("components.buttons.button", ["variant" => "success", "buttonElement" => "a"])
					@slot("attributeEx")
						type="button"
						href="{{route('requisition.download-layout-machine')}}"
					@endslot
					DESCARGAR PLANTILLA
				@endcomponent	
				@component("components.labels.label")
					En el archivo se indica como debe llenarse los campos "categoría" y "tipo".
				@endcomponent
			@endcomponent
		@endslot
	@endcomponent
	@php
		$buttons = [
			"separator" => 
			[
				[
					"kind" 			=> "components.buttons.button-approval",
					"label"			=> "coma (,)",
					"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComaMaquinaria\""
				],
				[
					"kind"			=> "components.buttons.button-approval",
					"label" 		=> "punto y coma (;)",
					"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComaMaquinaria\""
				]
			], 
			"buttonEx" =>
			[
			]
		];
		if(isset($request) && !isset($new_requisition))
		{
			if(isset($request) && $request->status == 2) 
			{
				array_push($buttons["buttonEx"], [
					"kind" => "components.buttons.button",
					"label" => "CARGAR ARCHIVO",
					"variant" => "primary",
					"attributeEx" => "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.save-follow',$request->folio)."\""
				]);
			}
		}
		else
		{
			array_push($buttons["buttonEx"], [
				"kind" => "components.buttons.button",
				"label" => "CARGAR ARCHIVO",
				"variant" => "primary",
				"attributeEx" => "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.store-detail')."\""
			]);
		}
	@endphp
	@component('components.documents.select_file_csv', 
	[
		"attributeExInput"	=> "type=\"file\" name=\"csv_file_machine\" id=\"files_machine\" accept=\".csv\"",
		"buttons"			=> $buttons
	])
	@endcomponent
	@component("components.labels.title-divisor", ["classEx" => "mt-8"])
		DATOS DEL CONCEPTO
	@endcomponent
	@component("components.containers.container-form", ["classEx" => "form-requisition"])
		<div class="col-span-2">
			@component('components.labels.label') Categoría: @endcomponent
			@php
				$options = collect();
				foreach(App\CatWarehouseType::where('status',1)->where('requisition_types_id',5)->orderBy('description','asc')->get() as $category)
				{
					$options = $options->concat([['value'=>$category->id, 'description'=>$category->description]]);
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'category removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cantidad: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					quantity remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese una cantidad"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Medida: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					measurement remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese una medida"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Unidad: @endcomponent
			@php
				$options = collect([
					["value" => "Pendiente", "description" => "Pendiente"],
					["value" => "Entregado", "description" => "Entregado"],
					["value" => "No aplica", "description" => "No aplica"],
					["value" => "Otro", "description" => "Otro"]
				]);
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'js-measurement_compras unit removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Nombre: @endcomponent
			@php
				$options = collect();
				foreach(App\CatRequisitionName::orderBy('name','asc')->get() as $n)
				{
					$options = $options->concat([['value'=>$n->name, 'description'=>$n->name]]);
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'js-name removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Marca: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					brand remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese una marca"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Modelo: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					model remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese un modelo"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tiempo de Utilización: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					usage-time remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese el tiempo"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Existencia en Almacén: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					exists_warehouse remove
				@endslot
				@slot('attributeEx')
					readonly="readonly"
					value="0"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Descripción: @endcomponent
			@component("components.inputs.text-area")
				@slot('attributeEx')
					placeholder = "Ingrese una descripción" 
				@endslot
				@slot('classEx')
					description remove
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar concepto",])
				@slot('attributeEx')
					id="add_art_machine" type="button"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@php
		$body 	   = [];
		$modelBody = [];
		$modelHead = ["Categoría", "Cant.", "Medida", "Unidad", "Nombre", "Descripción", "Marca", "Modelo", "Tiempo de Utilización", "Existencia en Almacén", "Acción"];
		
		if(isset($request) && $request->requisition->requisition_type == 5)
		{
			if($request->requisition->details()->exists())
			{
				foreach($request->requisition->details as $detail)
				{
					if (isset($new_requisition))
					{
						$contentCategory =
						[
							[
								"label" => $detail->categoryData->description,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_category",
								"attributeEx" => "type=\"hidden\" name=\"category[]\" value=\"".$detail->category."\""
							]
						];
						$contentQuantity =
						[
							[
								"label" => $detail->quantity,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" name=\"quantity[]\" value=\"".$detail->quantity."\""
							]
						];
						$contentMeasurement =
						[
							[
								"label" => htmlentities($detail->measurement),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_measurement",
								"attributeEx" => "type=\"hidden\" name=\"measurement[]\" value=\"".htmlentities($detail->measurement)."\""
							]
						];
						$contentUnit =
						[
							[
								"label" => $detail->unit,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_unit",
								"attributeEx" => "type=\"hidden\" name=\"unit[]\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							[
								"label" => $detail->catNames->name,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_name",
								"attributeEx" => "type=\"hidden\" name=\"name[]\" value=\"".$detail->name."\""
							]
						];
						$contentDescription =
						[
							[
								"label" => htmlentities($detail->description),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_description",
								"attributeEx" => "type=\"hidden\" name=\"description[]\" value=\"".htmlentities($detail->description)."\""
							]
						];
						$contentBrand =
						[
							[
								"label" => htmlentities($detail->brand),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_brand",
								"attributeEx" => "type=\"hidden\" name=\"brand[]\" value=\"".$detail->id."\""
							]
						];
						$contentModel =
						[
							[
								"label" => htmlentities($detail->model),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_model",
								"attributeEx" => "type=\"hidden\" name=\"model[]\" value=\"".htmlentities($detail->model)."\""
							]
						];
						$contentUsageTime =
						[
							[
								"label" => htmlentities($detail->usage_time),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_usage_time",
								"attributeEx" => "type=\"hidden\" name=\"usage_time[]\" value=\"".htmlentities($detail->usage_time)."\""
							]
						];
						$contentWarehouse =
						[
							[
								"label" => $detail->exists_warehouse,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_exists_warehouse",
								"attributeEx" => "type=\"hidden\" name=\"exists_warehouse[]\" value=\"".$detail->exists_warehouse."\" readonly"
							]
						];
					}
					else
					{
						$contentCategory =
						[
							[
								"label" => $detail->categoryData->description,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_category",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\""
							]
						];
						$contentQuantity =
						[
							[
								"label" => $detail->quantity,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "id",
								"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail\" value=\"".$detail->id."\""
							]
						];
						$contentMeasurement =
						[
							[
								"label" => htmlentities($detail->measurement),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_measurement",
								"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->measurement)."\""
							]
						];
						$contentUnit =
						[
							[
								"label" => $detail->unit,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_unit",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							[
								"label" => $detail->catNames->name,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_name",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->name."\""
							]
						];
						$contentDescription =
						[
							"label" => htmlentities($detail->description),
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_description",
								"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->description)."\""
							]
						];
						$contentBrand =
						[
							[
								"label" => htmlentities($detail->brand),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_brand",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->id."\""
							]
						];
						$contentModel =
						[
							[
								"label" => htmlentities($detail->model),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_model",
								"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->model)."\""
							]
						];
						$contentUsageTime =
						[
							[
								"label" => htmlentities($detail->usage_time),
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_usage_time",
								"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->usage_time)."\""
							]
						];
						$contentWarehouse =
						[
							[
								"label" => $detail->exists_warehouse,
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_exists_warehouse",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->exists_warehouse."\" readonly"
							]
						];
					}
					if (isset($request) && ($request->status == 2 || isset($new_requisition)))
					{
						$componentButtons	=
						[
							[
								"kind"  => "components.buttons.button",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>",
								"classEx" => "edit-art-machine"
							],
							[
								"kind"  => "components.buttons.button",
								"variant" => "red",
								"label" => "<span class=\"icon-x delete-span\"></span>",
								"classEx" => "delete-art"
							]
						];
					}
					$body = 
					[
						[
							"content" => $contentCategory
						],
						[
							"content" => $contentQuantity
						],
						[
							"content" => $contentMeasurement
						],
						[ 
							"content" => $contentUnit
						],
						[
							"content" => $contentName
						],
						[
							"content" => $contentDescription
						],
						[
							"content" => $contentBrand
						],
						[
							"content" => $contentModel
						],
						[
							"content" => $contentUsageTime
						],
						[
							"content" => $contentWarehouse
						],
						[
							"content" => $componentButtons
						]
					];
					$modelBody[] = $body;
				}
			}
		}
	@endphp
	@component('components.tables.alwaysVisibleTable',[
		"variant" => "nohidden",
		"modelHead" => $modelHead,
		"modelBody" => $modelBody, 
		"themeBody" => "striped",
		"title"     => "Conceptos"
	])
		@slot('attributeExBody')
			id="body_art_machine"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
</div>