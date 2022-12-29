<div id="material-table" @if(isset($request) && in_array($request->requisition->requisition_type,[2,3,4,5,6])) class="hidden" @elseif(!isset($request)) class="hidden" @endif class="justify-center">
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
						href="{{route('requisition.download-layout-material')}}"
					@endslot
					DESCARGAR PLANTILLA
				@endcomponent
				@component('components.labels.label')
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
					"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComaMaterial\""
				],
				[
					"kind"			=> "components.buttons.button-approval",
					"label" 		=> "punto y coma (;)",
					"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComaMaterial\""
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
		"attributeExInput"	=> "type=\"file\" name=\"csv_file_material\" id=\"files_material\"",
		"buttons"			=> $buttons
	])
	@endcomponent
	@component('components.labels.title-divisor') 
		DATOS DEL CONCEPTO
		@slot('classEx')
			mt-8 
		@endslot
	@endcomponent
	@component("components.containers.container-form", ["classEx" => "form-material"])
		<div class="col-span-2">
			@component('components.labels.label') Categoría: @endcomponent
			@php
				$options = collect();
				foreach(App\CatWarehouseType::where('status',1)->where('requisition_types_id',1)->orderBy('description','asc')->get() as $category)
				{
					$options = $options->concat([['value'=>$category->id, 'description'=>$category->description]]);
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'category removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2 cat-pm-container">
			@component('components.labels.label') Tipo: @endcomponent
			@php
				$options = collect();
				foreach(App\CatProcurementMaterial::orderBy('name','asc')->get() as $cat_pm)
				{
					$options = $options->concat([['value'=>$cat_pm->id, 'description'=>$cat_pm->name]]);
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'cat-pm removeselect'])
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
				if(isset($request))
				{
					$unity = App\Unit::whereHas('category_rq',function($q) use($request)
					{
						$q->where('rq_id',$request->requisition->requisition_type);
					})->orderBy('name','asc')->get();
					foreach($unity as $child)
					{
						$options = $options->concat([['value'=>$child->name, 'description'=>$child->name]]);
					}
				}
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
		<div class="col-span-4">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar concepto",])
				@slot('attributeEx')
					id="add_art_material" type="button"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@php
		$body 	   = [];
		$modelBody = [];
		$modelHead = ["Categoría", "Tipo", "Cant.", "Medida", "Unidad", "Nombre", "Descripción", "Existencia en Almacén", "Acción"];
		if(isset($request) && $request->requisition->requisition_type == 1)
		{
			if($request->requisition->details()->exists())
			{
				foreach($request->requisition->details as $detail)
				{
					if (isset($new_requisition))
					{
						$contentCategory =
						[
							"label" => $detail->categoryData->description,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_category",
								"attributeEx" => "type=\"hidden\" name=\"category[]\" value=\"".$detail->category."\""
							]
						];
						$contentType =
						[
							"label" => $detail->procurementMaterialType()->exists() ? $detail->procurementMaterialType->name : '',
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_type",
								"attributeEx" => "type=\"hidden\" name=\"type[]\" value=\"".$detail->cat_procurement_material_id."\""
							]
						];
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" name=\"quantity[]\" value=\"".$detail->quantity."\""
							]
						];
						$contentMeasurement =
						[
							"label" => htmlentities($detail->measurement),
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_measurement",
								"attributeEx" => "type=\"hidden\" name=\"measurement[]\" value=\"".htmlentities($detail->measurement)."\""
							]
						];
						$contentUnit =
						[
							"label" => $detail->unit,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_unit",
								"attributeEx" => "type=\"hidden\" name=\"unit[]\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							"label" => $detail->catNames->name,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_name",
								"attributeEx" => "type=\"hidden\" name=\"name[]\" value=\"".$detail->name."\""
							]
						];
						$contentDescription =
						[
							"label" => htmlentities($detail->description),
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_description",
								"attributeEx" => "type=\"hidden\" name=\"description[]\" value=\"".htmlentities($detail->description)."\""
							]
						];
						$contentWarehouse =
						[
							"label" => $detail->exists_warehouse,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_exists_warehouse",
								"attributeEx" => "type=\"hidden\" name=\"exists_warehouse[]\" value=\"".$detail->exists_warehouse."\""
							]
						];
					}
					else
					{
						$contentCategory =
						[
							"label" => $detail->categoryData->description,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_category",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\""
							]
						];
						$contentType =
						[
							"label" => $detail->procurementMaterialType()->exists() ? $detail->procurementMaterialType->name : '',
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "id",
								"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_type",
								"attributeEx" => "type=\"hidden\" name=\"t_type\" value=\"".$detail->cat_procurement_material_id."\""
							]
						];
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\""
							]
						];
						$contentMeasurement =
						[
							"label" => htmlentities($detail->measurement),
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_measurement",
								"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->measurement)."\""
							]
						];
						$contentUnit =
						[
							"label" => $detail->unit,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_unit",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							"label" => $detail->name,
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
						$contentWarehouse =
						[
							"label" => $detail->exists_warehouse,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_exists_warehouse",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->exists_warehouse."\" readonly"
							]
						];
					}
					$componentButtons	=	[];
					if (isset($request) && ($request->status == 2 || isset($new_requisition)))
					{
						$componentButtons	=
						[
							[
								"kind"  => "components.buttons.button",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>",
								"classEx" => "edit-art-material"
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
							"content" => $contentType
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
			id="body_art_material"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
</div>