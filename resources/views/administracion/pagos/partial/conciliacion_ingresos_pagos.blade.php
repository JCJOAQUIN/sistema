@if(isset($selectedBill) &&  $selectedBill != "")
	@php
		$modelBody = [];
		$modelHead = 
		[
			[
				["value" => "Empresa"],
				["value" => "Cliente"],
				["value" => "Monto"],
				["value" => "Fecha"],
				["value" => "Solicitud"],
				["value" => "Folio"],
				["value" => "Serie"],
			]
		];
		foreach($selectedBill as $selected)
		{
			$selected->total 	!= '' ? $total = "$ ".number_format($selected->total,2) : $total = '---';
			$selected->kindBill === 'BILLF' ? $type = 1 : $type = 2;
			$body = [];
			$body = 
			[
				"attributeEx" => "title=\"Ver datos\"",
				"classEx" => "tr selected",
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label", 
							"label" => $selected->businessName, 
							"classEx" => "businessName"
						],
						[
							"kind" => "components.inputs.input-text", 
							"attributeEx" => "value=\"".$selected->idBill."\"", 
							"classEx" => "idBill hidden"
						],
						[
							"kind" => "components.inputs.input-text", 
							"attributeEx" => "value=\"".$selected->folioRequest."\"", 
							"classEx" => "idFolio hidden"
						],
						[
							"kind" => "components.inputs.input-text", 
							"attributeEx" => "value=\"".$selected->businessName."\"", 
							"classEx" => "enterprisePay hidden"
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "value=\"".$type."\"",
							"classEx" => "type hidden"
						]
					], 
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label", 
							"label" => $selected->clientBusinessName, 
							"classEx" => "clientBusinessName"
						],
					] 
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label", 
							"label" => $total, 
							"classEx" => "total"
						], 
						[
							"kind" => "components.inputs.input-text", 
							"attributeEx" => "value=\"".$selected->total."\"", 
							"classEx" => "amount hidden"
						]
					] 
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->expeditionDate != "" ? $selected->expeditionDate : "---",
							"classEx" => "expeditionDate"
						]
					] 
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => "Solicitud de ".$selected->kind." #".$selected->folioRequest,
							"classEx" => "solicitud"
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label", 
							"label" => $selected->folio != '' ? $selected->folio : '---', 
							"classEx" => "folio"
						]
					] 
				],
				[
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->serie != '' ? $selected->serie : '---',
							"classEx" => "serie"
						]
					] 
				],
			];
			$modelBody[] = $body;	
		}
	@endphp
	@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"]) @endcomponent	
@endif
@php
	$modelHead = 
	[
		[
			["value" => "Empresa"],
			["value" => "Cliente"],
			["value" => "Monto"],
			["value" => "Fecha"],
			["value" => "Solicitud"],
			["value" => "Folio"],
			["value" => "Serie"],
		]
	];
	$modelBody = [];
	foreach ($billUnion as $bill) 
	{
		$bill->total 	!= '' ? $total = "$ ".number_format($bill->total,2) : $total = '---';
		$bill->kindBill === 'BILLF' ? $type = 1 : $type = 2;
		$body = [];
		$body = 
		[
			"attributeEx" => "title=\"Ver datos\"",
			"classEx" => "tr",
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $bill->businessName,
						"classEx" => "businessName"
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "value=\"".$bill->idBill."\"",
						"classEx" => "idBill hidden"
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "value=\"".$bill->folioRequest."\"", 
						"classEx" => "idFolio hidden"
					],
					[
						"kind" => "components.inputs.input-text", 
						"attributeEx" => "value=\"".$bill->businessName."\"", 
						"classEx" => "enterprisePay hidden"
					],
					[
						"kind" => "components.inputs.input-text", 
						"attributeEx" => "value=\"".$type."\"", 
						"classEx" => "type hidden"
					]
				], 
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $bill->clientBusinessName, 
						"classEx" => "clientBusinessName"
					],
				] 
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $total,
						"classEx" => "total"
					], 
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "value=\"".$bill->total."\"", 
						"classEx" => "amount hidden"
					]
				] 
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $bill->expeditionDate != "" ? $bill->expeditionDate : "---",
						"classEx" => "expeditionDate"
					]
				] 
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "Solicitud de ".$bill->kind." #".$bill->folioRequest,
						"classEx" => "solicitud"
					]
				]
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $bill->folio != '' ? $bill->folio : '---',
						"classEx" => "folio"
					]
				] 
			],
			[
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $bill->serie != '' ? $bill->serie : '---',
						"classEx" => "serie"
					]
				] 
			],
		];

		$modelBody[] = $body;	
	}
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead"	=> "true"]) 
	@slot("classExBody")
		tbody
	@endslot
@endcomponent
{{ $billUnion->appends($_GET)->links() }}