<div id="comercial-table"  @if(isset($request) && in_array($request->requisition->requisition_type,[1,2,3,4,5])) class="hidden"  @elseif(!isset($request)) class="hidden" @endif  class="form-requisition">
	@component('components.labels.title-divisor') CARGA MASIVA (OPCIONAL) @endcomponent
	@component("components.labels.not-found", ["variant" => "note"])
		@slot("slot")
			@component("components.labels.label")
				Si desea cargar conceptos de forma masiva para esta requisición, utilice la siguiente plantilla.
				@component('components.buttons.button',['variant' => 'success', 'buttonElement' => 'a'])
					@slot('attributeEx')
						type="button"
						href="{{route('requisition.download-layout-comercial')}}"
					@endslot
					DESCARGAR PLANTILLA
				@endcomponent
			@endcomponent
		@endslot
	@endcomponent
	@php
		$buttons =
		[
			"separator" =>
			[
				[
					"kind" 			=> "components.buttons.button-approval",
					"label"			=> "coma (,)",
					"attributeEx"	=> "value=\",\" name=\"separator\" id=\"separatorComaComercial\""
				],
				[
					"kind"			=> "components.buttons.button-approval",
					"label" 		=> "punto y coma (;)",
					"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComaComercial\""
				]
			], 
			"buttonEx" => []
		];
		if(isset($request) && !isset($new_requisition))
		{
			if(isset($request) && $request->status == 2) 
			{
				array_push($buttons["buttonEx"],
				[
					"kind"			=> "components.buttons.button",
					"label"			=> "CARGAR ARCHIVO",
					"variant"		=> "primary",
					"attributeEx"	=> "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.save-follow',$request->folio)."\""
				]);
			}
		}
		else
		{
			array_push($buttons["buttonEx"],
			[
				"kind" 			=> "components.buttons.button",
				"label" 		=> "CARGAR ARCHIVO",
				"variant"		=> "primary",
				"attributeEx"	=> "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.store-detail')."\""
			]);
		}
	@endphp
	@component('components.documents.select_file_csv', 
	[
		"attributeExInput"	=> "type=\"file\" name=\"csv_file_comercial\" id=\"comercial\" accept=\".csv\"",
		"buttons"			=> $buttons
	])
	@endcomponent
	@component('components.labels.title-divisor')  DATOS DEL CONCEPTO @endcomponent
	@component('components.containers.container-form', ["classEx" => "form-requisition"])
		<div class="col-span-2">
			@component('components.labels.label') Cantidad: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					quantity-art remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese la cantidad"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Unidad: @endcomponent
			@php
				$options = collect();
				if(isset($request))
				{
					$unity = App\Unit::whereHas('category_rq',function($q) use($request)
						{
							$q->where('rq_id',$request->requisition->requisition_type);
						})
						->orderBy('name','asc')
						->get();
					foreach($unity as $child)
					{
						$options = $options->concat([['value'=>$child->name, 'description'=>$child->name]]);
					}
				}
			@endphp
			@component('components.inputs.select', ['options' => $options, 'classEx' => 'unit removeselect']) @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Nombre: @endcomponent
			@component('components.inputs.input-text')
				@slot('classEx')
					name-art remove
				@endslot
				@slot('attributeEx')
					placeholder="Ingrese el nombre"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Descripción: @endcomponent
			@component("components.inputs.text-area")
				@slot('attributeEx')
					placeholder="Ingrese la descripción"
				@endslot
				@slot('classEx')
					description-art remove
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar concepto",])
				@slot('attributeEx')
					type="button" id="add_art_comercial"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@php 
		$body 	   = [];
		$modelBody = [];
		$modelHead = ["Cant.", "Unidad", "Nombre", "Descripción", "Acciones"];
		if(isset($request) && $request->requisition->requisition_type == 6)
		{
			if(isset($request) && $request->requisition->details()->exists())
			{
				foreach($request->requisition->details as $detail)
				{
					if (isset($new_requisition))
					{
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx"		=> "t_part",
								"attributeEx"	=> "type=\"hidden\" name=\"part[]\" value=\"".$detail->part."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_quantity",
								"attributeEx"	=> "type=\"hidden\" name=\"quantity[]\" value=\"".$detail->quantity."\""
							]
						];
						$contentUnit =
						[
							"label" => $detail->unit,
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_unit",
								"attributeEx"	=> "type=\"hidden\" name=\"unit[]\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							"label" => $detail->catNames->name,
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx"		=> "t_name",
								"attributeEx"	=> "type=\"hidden\" name=\"name[]\" value=\"".$detail->name."\""
							]
						];
						$contentDescription =
						[
							"label" => htmlentities($detail->description),
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_description",
								"attributeEx"	=> "type=\"hidden\" name=\"description[]\" value=\"".htmlentities($detail->description)."\""
							]
						];
					}
					else
					{
						$contentQuantity =
						[
							"label" => $detail->quantity,
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_part",
								"attributeEx"	=> "type=\"hidden\" value=\"".$detail->part."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_quantity",
								"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "id",
								"attributeEx"	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\""
							]
						];
						$contentUnit =
						[
							"label" => $detail->unit,
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_unit",
								"attributeEx"	=> "type=\"hidden\" value=\"".$detail->unit."\""
							]
						];
						$contentName =
						[
							"label" => $detail->name,
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_name",
								"attributeEx"	=> "type=\"hidden\" value=\"".$detail->name."\""
							]
						];
						$contentDescription =
						[
							"label" => htmlentities($detail->description),
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "t_description",
								"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($detail->description)."\""
							]
						];
					}
					$body = [ "classEx" => "table-row",
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
					];
					$actions =
					[
						"content" => 
						[
						]
					];
					if(isset($request) && ($request->status == 2 || isset($new_requisition)))
					{
						array_push($actions["content"], 
							[
								"kind"		=> "components.buttons.button",
								"variant"	=> "success",
								"label"		=> "<span class=\"icon-pencil\"></span>",
								"classEx"	=> "edit-art-comercial"
							]
						);
						array_push($actions["content"], 
							[
								"kind" 		=> "components.buttons.button",
								"variant"	=> "red",
								"label"		=> "<span class=\"icon-x delete-span\"></span>",
								"classEx"	=> "delete-art"
							]
						);
					}
					array_push($body, $actions);
					array_push($modelBody, $body);
				}
			}
		}
	@endphp
	@component('components.tables.alwaysVisibleTable',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"title"     => "Conceptos"
		])
		@slot('attributeExBody')
			id="body_art_comercial"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
</div>