<div id="general-services-table"  @if(isset($request) && in_array($request->requisition->requisition_type,[1,3,4,5,6])) class="hidden"  @elseif(!isset($request)) class="hidden" @endif class="justify-center">
	@component('components.labels.title-divisor') 
		CARGA MASIVA (OPCIONAL)
		@slot('classEx')
			pb-4 
		@endslot
	@endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		@slot("slot")
			@component("components.labels.label")
				Si desea cargar conceptos de forma masiva para esta requisición, utilice la siguiente plantilla.
				@component("components.buttons.button", ["variant" => "success", "buttonElement" => "a"])
					@slot("attributeEx")
						type="button"
						href="{{route('requisition.download-layout-service')}}"
					@endslot
					DESCARGAR PLANTILLA
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
					"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComaServicios\""
				],
				[
					"kind"			=> "components.buttons.button-approval",
					"label" 		=> "punto y coma (;)",
					"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComaServicios\""
				]
			],
			"buttonEx" => [ ]
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
		"attributeExInput"	=> "type=\"file\" name=\"csv_file_service\" id=\"files_service\"",
		"buttons"			=> $buttons
	])
	@endcomponent
	@component('components.labels.title-divisor') 
		DATOS DEL CONCEPTO
		@slot('classEx')
			mt-8 
		@endslot
	@endcomponent
	@component("components.containers.container-form", ["classEx" => "general-services form-requisition"])
		<div class="col-span-2">
			@component('components.labels.label') Categoría: @endcomponent
			@php
				$options = collect();
				foreach(App\CatWarehouseType::where('status',1)->where('requisition_types_id',2)->orderBy('description','asc')->get() as $category)
				{
					$options = $options->concat([['value'=>$category->id, 'description'=>$category->description]]);
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'category removeselect'])
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Unidad: @endcomponent
			@php
				$options = collect();
				foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
				{
					foreach($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
					{
						$options = $options->concat([['value'=>$child->description, 'description'=>$child->description]]);
					}
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'js-measurement_compras unit removeselect']) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Cantidad: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					quantity-art remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese una cantidad"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Nombre: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					name-art remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese un nombre"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Periodo: @endcomponent
			@component("components.inputs.input-text")
				@slot('attributeEx')
					placeholder = "Ingrese un periodo" 
				@endslot
				@slot('classEx')
					period-art remove
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
					description-art remove
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar concepto",])
				@slot('attributeEx')
					id="add_art_general_services" type="button"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@php 
		$body 	   = [];
		$modelBody = [];
		$modelHead = ["Categoría", "Cant.", "Unidad", "Nombre", "Descripción", "Periodo", "Acción"];
		if(isset($request) && $request->requisition->requisition_type == 2)
		{
			if(isset($request) && $request->requisition->details()->exists())
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
								"classEx" => "t_part",
								"attributeEx" => "type=\"hidden\" name=\"part[]\" value=\"".$detail->part."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" name=\"quantity[]\" value=\"".$detail->quantity."\""
							]
						];
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_part",
								"attributeEx" => "type=\"hidden\" name=\"part[]\" value=\"".$detail->part."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" name=\"quantity[]\" value=\"".$detail->quantity."\""
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
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "id",
								"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_part",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->part."\""
							],
							[
								"kind"  => "components.inputs.input-text",
								"classEx" => "t_quantity",
								"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\""
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
					}

					if(isset($request) && $request->requisition->requisition_type == 2)
					{
						if(isset($new_requisition))
						{
							$contentPeriod =
							[
								"label" => $detail->period,
								[
									"kind"  => "components.inputs.input-text",
									"classEx" => "t_period",
									"attributeEx" => "type=\"hidden\" name=\"period[]\" value=\"".$detail->period."\""
								]
							];
						}
						else
						{
							$contentPeriod =
							[
								"label" => $detail->period,
								[
									"kind"  => "components.inputs.input-text",
									"classEx" => "t_period",
									"attributeEx" => "type=\"hidden\" value=\"".$detail->period."\""
								]
							];
						}
					}
					$contentButtons = [];
					if(isset($request) && ($request->status == 2 || isset($new_requisition)))
					{
						$contentButtons = 
						[
							[
								"kind"  => "components.buttons.button",
								"variant" => "success",
								"label" => "<span class=\"icon-pencil\"></span>",
								"classEx" => "edit-art-services",
								"attributeEx" => "type=\"button\""
							],
							[
								"kind"  => "components.buttons.button",
								"variant" => "red",
								"label" => "<span class=\"icon-x delete-span\"></span>",
								"classEx" => "delete-art",
								"attributeEx" => "type=\"button\""
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
							"content" => $contentUnit
						],
						[
							"content" => $contentName
						],
						[
							"content" => $contentDescription
						],
						[
							"content" => $contentPeriod
						],
						[
							"content" => $contentButtons
						]
					];
					
					array_push($modelBody, $body);
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
			id="body_art_general_services"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
</div>