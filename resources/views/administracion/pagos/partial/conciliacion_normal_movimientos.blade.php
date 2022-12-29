@if(isset($selectedMovement) &&  $selectedMovement != "")
	@php
		$modelBody = [];
		$modelHead = 
		[
			[
				["value" => "Empresa"],
				["value" => "Cuenta"],
				["value" => "Monto"],
				["value" => "Fecha"],
				["value" => "Descripción"],
				["value" => "Tipo"],
			]
		];
		foreach($selectedMovement as $selected)
		{
			$movementType = $selected->movementType != null ? $selected->movementType : 'No definido';
			$body = [
				"attributeEx" => "title=\"Ver datos\"",
				"classEx" => "tr selected",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->enterprise->name,
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->enterprise->name."\"",
							"classEx" => "enterpriseMov",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->movementType."\"",
							"classEx" => "movementType",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->accounts->account." - ".$selected->accounts->description." (".$selected->accounts->content.")",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => "$ ".number_format($selected->amount,2),
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idmovement."\"",
							"classEx" => "idmovement",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->amount."\"",
							"classEx" => "amount",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $selected->movementDate)->format('d-m-Y'),
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => htmlentities($selected->description),
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $movementType,
						],
					],
				],
			];
			$modelBody[] = $body;
		}
	@endphp
	@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead" => "true"]) @endcomponent	
@endif
@php
	$modelBody = [];
	$modelHead = 
	[
		[
			["value" => "Empresa"],
			["value" => "Cuenta"],
			["value" => "Monto"],
			["value" => "Fecha"],
			["value" => "Descripción"],
			["value" => "Tipo"],
		]
	];
	foreach($movementsAll as $movements)
	{
		$movementType = $movements->movementType!=null ? $movements->movementType : 'No definido';
		$body = [
			"classEx" => "tr",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $movements->enterprise->name,
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$movements->enterprise->name."\"",
						"classEx" => "enterpriseMov",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$movements->movementType."\"",
						"classEx" => "movementType",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $movements->accounts->account." - ".$movements->accounts->description." (".$movements->accounts->content.")",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($movements->amount,2),
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$movements->idmovement."\"",
						"classEx" => "idmovement",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$movements->amount."\"",
						"classEx" => "amount",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $movements->movementDate)->format('d-m-Y'),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => htmlentities($movements->description),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $movementType,
					],
				],
			],
		];
		$modelBody[] = $body;
	}
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead" => "true"]) 
	@slot("classExBody")
		tbody
	@endslot
@endcomponent
<div class="result_pagination {{($movementsAll->lastPage() == 1 ? 'hidden' : '') }}">
	{{ $movementsAll->links() }}
</div>