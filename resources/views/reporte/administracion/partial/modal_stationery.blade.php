@php
	$date		= $request->stationery->first()->datetitle != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->stationery->first()->datetitle)->format('d-m-Y') : "";
	$modelTable	= 
	[
		["Folio", $request->new_folio != null ? $request->new_folio : $request->folio],
		["Título y fecha", htmlentities($request->stationery->first()->title). " - " .$date ],
		["Solicitante", $request->requestUser->fullName()],
		["Elaborado", $request->elaborateUser->fullName()],
		["Empresa", $request->requestEnterprise->name],
		["Dirección", $request->requestDirection->name],
		["Departamento", $request->requestDepartment->name],
		["Proyecto", $request->requestProject()->exists() ? $request->requestProject->proyectName : ""],
		["Clasificación del Gasto", $request->accounts->fullClasificacionName()],
	];
	if(isset($request) && $request->stationery()->first())
	{
		$value	= $request->stationery()->first()->subcontractorProvider;
		if(strlen($value) > 0)
		{
			$modelTable[]	= ["Subcontratista/Proveedor", $value ];
		}
		else
		{
			$modelTable[]	= ["Subcontratista/Proveedor", "No aplica" ];
		}						
	}
@endphp	
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent

@component("components.labels.title-divisor") Detalles del artículo @endcomponent
<div class="my-4">
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= 
		[
			[
				["value"	=> "#"],
				["value"	=> "Categoría"],
				["value"	=> "Cantidad"],
				["value"	=> "Concepto"],
				["value"	=> "Código corto"],
				["value"	=> "Código largo"],
				["value"	=> "Comentario"],
			]
		];

		$countConcept = 1;

		foreach ($request->stationery->first()->detailStat as $key=>$detail) 
		{
			$body =
			[
				"classEx" => "tr",
				[
					"content"	=>
					[
						[
							"label" => $countConcept,
						]
					]
				],
				[
					"content"	=>
					[
						[
							"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : "",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $detail->quantity,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => htmlentities($detail->product),
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => htmlentities($detail->short_code),
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => htmlentities($detail->long_code),
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => htmlentities($detail->commentaries),
						]
					]
				],
			];
			$countConcept++;
			$modelBody[] = $body; 
		}
	@endphp

	@component("components.tables.table",
		[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
		])
	@endcomponent
</div>