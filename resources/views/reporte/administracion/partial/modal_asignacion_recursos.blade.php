<div class="pb-6">
	@php
		$dateTitle		= $request->resource->first()->datetitle != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->resource->first()->datetitle)->format('d-m-Y') : '';
		$titleRequest	= $request->resource->first()->title. " - " .$dateTitle;
		$requestUser	= $request->requestUser()->exists() ? $request->requestUser->fullName() : '';
		$elaborateUser	= $request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : '';
		$enterpriseName	= $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : ($request->requestEnterprise()->exists() ? $request->requestEnterprise->name : '');
		$directionName	= $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : ($request->requestDirection()->exists() ? $request->requestDirection->name : '');
		$departmentName	= $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : ($request->requestDepartment()->exists() ? $request->requestDepartment->name : '');
		$projectName	= $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : ($request->requestProject()->exists() ? $request->requestProject->proyectName : '');
		$modelTable =
		[
			["Folio", $request->folio],
			["Título y fecha", $titleRequest],
			["Solicitante", $requestUser],
			["Elaborado por", $elaborateUser],
			["Empresa", $enterpriseName],
			["Dirección", $directionName],
			["Departamento", $departmentName],
			["Proyecto", $projectName],
		];
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"]) 
	@endcomponent  
</div>
<div class="mt-6">
	@component("components.labels.title-divisor")
		DATOS DEL SOLICITANTE
	@endcomponent
	@php
				
		if($request->resource->first()->bankData()->exists())
		{
			$modelTable = 
			[
				"Banco"				=> $request->resource->first()->bankData->bank->description,
				"Alias"				=> $request->resource->first()->bankData->alias!=null ? $request->resource->first()->bankData->alias : '---',
				"Número de tarjeta"	=> $request->resource->first()->bankData->cardNumber!=null ? $request->resource->first()->bankData->cardNumber : '---',
				"CLABE"				=> $request->resource->first()->bankData->clabe!=null ? $request->resource->first()->bankData->clabe : '---',
				"Número de cuenta"	=> $request->resource->first()->bankData->account!=null ? $request->resource->first()->bankData->account : '---',
				"Forma de pago"		=> $request->resource->first()->paymentMethod->method,
				"Referencia"		=> $request->resource->first()->reference,
				"Tipo de moneda"	=> $request->resource->first()->currency,
				"Importe"			=> "$".number_format($request->resource->first()->total,2),
			];
		}
		else
		{
			$modelTable = 
			[
				"Forma de pago"		=> $request->resource->first()->paymentMethod->method,
				"Referencia"		=> $request->resource->first()->reference,
				"Tipo de moneda"	=> $request->resource->first()->currency,
				"Importe"			=> "$".number_format($request->resource->first()->total,2),
			];
		}
		
	@endphp
	@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable]) 
	@endcomponent
</div>
<div class="mt-10">
	@component("components.labels.title-divisor")
		RELACIÓN DE DOCUMENTOS SOLICITADOS
	@endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= 
		[
			[
				["value"	=> "#"],
				["value"	=> "Concepto"],
				["value"	=> "Clasificación de gasto"],
				["value"	=> "Importe"]
			]
		];

		
		$totalFinal = 0;
		$countConcept = 1;

		foreach($request->resource->first()->resourceDetail as $resourceDetail)
		{
			$totalFinal	+= $resourceDetail->amount;

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
							"label" => $resourceDetail->concept,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $resourceDetail->accounts->account." ".$resourceDetail->accounts->description
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => "$".number_format($resourceDetail->amount,2)
						]
					]
				]
			];
			$countConcept++;
			$modelBody[] = $body;
		}            
	@endphp
	@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@slot("classEx")    
			text-center
		@endslot
	@endcomponent
	@php
		
		$modelTable = 
		[
			[
				"label" => "TOTAL:", "inputsEx" => 
				[
					[
						"kind"		=> "components.labels.label",
						"label"		=> "$".number_format($totalFinal,2),
						"classEx"	=> "total"
					]
				]
			]
		];
	@endphp
	@component('components.templates.outputs.form-details',
	[
		"modelTable"	=> $modelTable,
		
	])
	@endcomponent
</div>
@if($request->resource->first()->documents()->exists())
	<div class="mt-10">
		@component("components.labels.title-divisor")
			DOCUMENTOS CARGADOS
		@endcomponent
		@php
			$body = [];
			$modelBody = [];
			$heads = ["Nombre","Archivo","Modificado por"];

			foreach($request->resource->first()->documents->sortByDesc('created_at') as $doc)
			{
				$body =
				[
					"classEx" => "tr",
					[
						"content" =>
						[
							[
								"label" => $doc->name,
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx"	=> "type=\"hidden\" name=\"document-id[]\" value=\"".$doc->id."\"",
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/resource/'.$doc->path)."\"",
								"buttonElement"	=> "a",
								"label"			=> "Archivo",
								"variant"		=> "secondary",
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $doc->user->fullName(),
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.alwaysVisibleTable",
			[
				"modelHead" => $heads,
				"modelBody" => $modelBody,
			])
			@slot("classEx")
				text-center
			@endslot
		@endcomponent
	</div>
@endif