@if (count($warehouseRequest) > 0 && $warehouseRequestSelected == "")	
	@php
		$modelHead =
		[
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"quantity\"",
				"label" => "Cantidad <span class='icon-arrow-up'></span>"
			],
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"equipment\"",
				"label" => "Equipo <span class='icon-arrow-up'></span>"
			],
		];
		
		$modelBody = [];
		foreach ($warehouseRequest as $warehouse)
		{
			$modelBody[] = 
			[
				"classEx" => "tr ",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    => "components.labels.label",
							"label"   => $warehouse->quantity,
							"classEx" => "td_quantity cursor-pointer",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$warehouse->id."\"",
							"classEx"		=> "id",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$warehouse->quantity."\"",
							"classEx"		=> "quantity",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"  => "components.labels.label",
							"label" => htmlentities($warehouse->brand),
							"classEx" => "brand cursor-pointer",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($warehouse->brand)."\"",
							"classEx"		=> "material",	
						],
					],
				],						
			];
		}
	@endphp
	@component("components.tables.alwaysVisibleTable",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"noHead" 	=> true,
		"variant" 	=> "default"
	])
		@slot("classEx")
			table-move
			cursor-pointer
		@endslot
		@slot("attributeEx")
			id="warehouse_request"
		@endslot
		@slot("attributeExBody")
			id="body-move"
		@endslot
		@slot("classExHead")
			select-none
			rounded
		@endslot
		@slot("classExBody")
			body
		@endslot
	@endcomponent
	{{ $warehouseRequest->links() }}
@elseif (count($warehouseRequest) > 0 && $warehouseRequestSelected != "")
	@php
		$modelHead =
		[
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"quantity\"",
				"label" => "Cantidad <span class='icon-arrow-up'></span>"
			],
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"equipment\"",
				"label" => "Equipo <span class='icon-arrow-up'></span>"
			],
		];

		$modelBody = [];
		$modelBody[] = 
		[
			"classEx" => "tr selected",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label"   => $warehouseRequestSelected->quantity,
						"classEx" => "td_quantity cursor-pointer",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".$warehouseRequestSelected->id."\"",
						"classEx"		=> "id",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".$warehouseRequestSelected->quantity."\"",
						"classEx"		=> "quantity",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => htmlentities($warehouseRequestSelected->brand),
						"classEx" => "brand cursor-pointer",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($warehouseRequestSelected->brand)."\"",
						"classEx"		=> "material",	
					],
				],
			],						
		];

		foreach ($warehouseRequest as $warehouse)
		{
			$modelBody[] = 
			[
				"classEx" => "tr ",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    => "components.labels.label",
							"label"   => $warehouse->quantity,
							"classEx" => "td_quantity cursor-pointer",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$warehouse->id."\"",
							"classEx"		=> "id",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".$warehouse->quantity."\"",
							"classEx"		=> "quantity",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"  => "components.labels.label",
							"label" => htmlentities($warehouse->brand),
							"classEx" => "brand cursor-pointer",
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($warehouse->brand)."\"",
							"classEx"		=> "material",	
						],
					],
				],						
			];
		}
	@endphp
	@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"noHead" 	=> true,
			"variant" 	=> "default"
		])
		@slot("classEx")
			table-move
			cursor-pointer
		@endslot
		@slot("attributeEx")
			id="warehouse_request"
		@endslot
		@slot("attributeExBody")
			id="body-move"
		@endslot
		@slot("classExHead")
			select-none
			rounded
		@endslot
		@slot("classExBody")
			body
		@endslot
	@endcomponent
	{{ $warehouseRequest->links() }}	
@elseif (count($warehouseRequest) == 0 && $warehouseRequestSelected != "")
	@php
		$modelHead =
		[
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"quantity\"",
				"label" => "Cantidad <span class='icon-arrow-up'></span>"
			],
			[
				"classEx" => "arrow",
				"attributeEx" => "data-sort=\"equipment\"",
				"label" => "Equipo <span class='icon-arrow-up'></span>"
			],
		];

		$modelBody = [];
		$modelBody[] = 
		[
			"classEx" => "tr selected",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label"   => $warehouseRequestSelected->quantity,
						"classEx" => "td_quantity cursor-pointer",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".$warehouseRequestSelected->id."\"",
						"classEx"		=> "id",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".$warehouseRequestSelected->quantity."\"",
						"classEx"		=> "quantity",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => htmlentities($warehouseRequestSelected->brand),
						"classEx" => "brand cursor-pointer",
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($warehouseRequestSelected->brand)."\"",
						"classEx"		=> "material",	
					],
				],
			],						
		];
	@endphp
	@component("components.tables.alwaysVisibleTable",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"noHead" 	=> true,
			"variant" 	=> "default"
		])
		@slot("classEx")
			table-move
			cursor-pointer
		@endslot
		@slot("attributeEx")
			id="warehouse_request"
		@endslot
		@slot("attributeExBody")
			id="body-move"
		@endslot
		@slot("classExHead")
			select-none
			rounded
		@endslot
		@slot("classExBody")
			body
		@endslot
	@endcomponent
	@if($flagSearch)
		@component("components.labels.not-found") @endcomponent
	@else
		{{ $warehouseRequest->links() }}
	@endif
@else
	@component("components.labels.not-found") @endcomponent
@endif