<div class="mt-10">
	@component("components.labels.title-divisor")
		DATOS DEL EMPLEADO
	@endcomponent
	@php
		$body		= [];
		$modelBody	= [];

		
		$modelHead	= 
		[
			[
				["value" => "# Empleado"],
				["value" => "Nombre"],
				["value" => "Empresa"],
				["value" => "Proyecto"],
				["value" => "Forma de pago"],
				["value" => "Referencia"],
				["value" => "Importe"],
				["value" => "Razón"]
			]
		];

		
		$totalFinal = 0;
		foreach($request->nominas->first()->noAppEmp as $noEmp)
		{
			$totalFinal		+= $noEmp->amount;
			$enterpriseName	= $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
			$projectName	= $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
			$name			=  $noEmp->employee->fullName();
			$body			= 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						[
							"label" => $noEmp->idUsers,
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $name,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $enterpriseName,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $projectName,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $noEmp->paymentMethod->method,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $noEmp->reference,
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => "$".number_format($noEmp->amount,2),
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $noEmp->description,
						]
					]
				]
			];
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