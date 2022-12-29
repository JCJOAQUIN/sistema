
@php
	$modelTable	=
	[
		["Folio",							[["kind"	=>	"components.labels.label",	"label"	=>	$request->folio]]],
		["Proyecto",						[["kind"	=>	"components.labels.label",	"label"	=>	$request->requestProject()->exists() ? $request->requestProject->proyectName : 'No hay']]],
		["Prioridad",						[["kind"	=>	"components.labels.label",	"label"	=>	$request->requisition->urgent == 1 ? 'Alta' : 'Baja']]],
		["Código WBS",						[["kind"	=>	"components.labels.label",	"label"	=>	$request->requisition->code_wbs]]],
		["Solicitante",						[["kind"	=>	"components.labels.label",	"label"	=>	$request->requestUser->fullName()]]],
		["Título",							[["kind"	=>	"components.labels.label",	"label"	=>	htmlentities($request->requisition->title)]]],
		["Número",							[["kind"	=>	"components.labels.label",	"label"	=>	$request->requisition->number]]],
		["Fecha en que se solicitó",		[["kind"	=>	"components.labels.label",	"label"	=>	$request->requisition->date_request]]],
		["Fecha en que debe estar en obra",	[["kind"	=>	"components.labels.label",	"label"	=>	$request->requisition->date_obra]]],
	];
@endphp
@component("components.templates.outputs.table-detail",["modelTable" => $modelTable, "title" => "DETALLES DE LA REQUISICIÓN"])@endcomponent
@component('components.labels.title-divisor')    DETALLES ARTÍCULOS @endcomponent
@if($request->status == 17)
	@php
		$body = [];
		$modelBody = [];

			foreach ($request->requisition->purchases as $purchase)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label"	=> $detail->code
						]
					],
					[
						"content" =>
						[
							"label"	=> $detail->quantity
						]
					],
					[
						"content" =>
						[
							"label"	=> htmlentities($detail->measurement),
						]
					],
					[
						"content" =>
						[
							"label"	=> htmlentities($detail->unit),
						]
					],
					[
						"content" =>
						[
							"label"	=> htmlentities($detail->description),
						]
					],
					[
						"content" =>
						[
							"label"	=> $detail->categoria
						]
					],
					[
						"content" =>
						[
							"label"	=> $purchase->presupuestoEstatus
						]
					],
					[
						"content" =>
						[
							"label"	=> $request->requisition->requisition_type == 1 ? $detail->estatusAlmacen : 'No Aplica'
						]
					],
					[
						"content" =>
						[
							"label"	=> $request->requisition->requisition_type == 1 ? $detail->statDetail ? ($detail->statDetail->idwarehouse ? 'Entregado' : 'Pendiente') : 'Pendiente' : 'No Aplica'
						]
					],
				];
				$modelBody[] = $body;
			}
			foreach ($request->requisition->refunds as $refund)
			{
				foreach ($refund->refundDetail as $detail)
				{
					$body = 
					[
						[
							"content" =>
							[
								"label"	=> $detail->code
							]
						],
						[
							"content" =>
							[
								"label"	=> $detail->quantity
							]
						],
						[
							"content" =>
							[
								"label"	=> htmlentities($detail->measurement),
							]
						],
						[
							"content" =>
							[
								"label"	=> htmlentities($detail->unit),
							]
						],
						[
							"content" =>
							[
								"label"	=> htmlentities($detail->concept),
							]
						],
						[
							"content" =>
							[
								"label"	=> $detail->categoria
							]
						],
						[
							"content" =>
							[
								"label"	=> $refund->presupuestoEstatus
							]
						],
						[
							"content" =>
							[
								"label"	=> "N/A"
							]
						],
						[
							"content" =>
							[
								"label"	=> (($refund->requestModel->status >= 5) ? (($refund->presupuestoEstatus == 'Aprobada') ? "Si" : "No") : "N/A")
							]
						],
					];
					$modelBody[] = $body;
				}
			}
	@endphp       
@else
	@php
		$modelBody = [];
		foreach ($request->requisition->details as $dt)
		{
			$body = 
			[
				[
					"content" =>
					[
						"label"	=> $dt->code != "" ? $dt->code : "---"
					]
				],
				[
					"content" =>
					[
						"label"	=> $dt->quantity
					]
				],
				[
					"content" =>
					[
						"label"	=> htmlentities($dt->measurement),
					]
				],
				[
					"content" =>
					[
						"label"	=> htmlentities($dt->unit),
					]
				],
				[
					"content" =>
					[
						"label"	=> htmlentities($dt->description),
					]
				],
				[
					"content" =>
					[
						"label"	=> $dt->categoria
					]
				],
				[
					"content" =>
					[
						"label"	=> "Pendiente"
					]
				],
				[
					"content" =>
					[
						"label"	=> "Pendiente"
					]
				],
				[
					"content" =>
					[
						"label"	=> "Pendiente"
					]
				],
			];
			$modelBody[] = $body;
		}
	@endphp
@endif
	@component("components.tables.table", 
	[
		"modelHead" => 
		[
			[
				["value"	=>	"Código"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Medida"],
				["value"	=>	"Unidad"],
				["value"	=>	"Nombre"],
				["value"	=>	"Categoría"],
				["value"	=>	"Presupuestos"],
				["value"	=>	"Almacén"],
				["value"	=>	"Entregado"]
			]
		], 
		"modelBody" => $modelBody
	])@endcomponent

