@extends('layouts.child_module')
  
@section('data')
	@component('components.templates.outputs.table-detail', 
		[
			"title" => $employee->name." ".$employee->last_name." ".$employee->scnd_last_name,
			"modelTable" => 
			[
				["CURP:"		, [["kind" => "components.labels.label", "label" => $employee->curp]]],
				["Dirección:"	, [["kind" => "components.labels.label", "label" => $employee->street." ".$employee->number." colonia ".$employee->colony]]],
				["RFC:"			, [["kind" => "components.labels.label", "label" => $employee->rfc]]],
				["CP:"			, [["kind" => "components.labels.label", "label" => $employee->cp]]],
				["IMSS:"		, [["kind" => "components.labels.label", "label" => $employee->imss]]],
				["Ciudad:"		, [["kind" => "components.labels.label", "label" => $employee->city.", ".$employee->states->description]]]
			],
			"classEx" => "mb-4"
		])
	@endcomponent
	@php
		$modelHead =
		[
			[
				["value" => "Estado Laboral", "classEx" => "sticky inset-x-0"],
				["value" => "Proyecto/Contrato", "classEx" => "sticky inset-x-0"],
				["value" => "WBS"],
				["value" => "Empresa"],
				["value" => "Clasificación del gasto"],
				["value" => "Lugar de trabajo"],
				["value" => "Dirección"],
				["value" => "Departamento"],
				["value" => "Subdepartamento"],
				["value" => "Puesto"],
				["value" => "Jefe inmediato"],
				["value" => "Puesto del Jefe inmediato"],
				["value" => "Fecha de ingreso"],
				["value" => "Estado de IMSS"],
				["value" => "Fecha de alta"],
				["value" => "Fecha de baja"],
				["value" => "Fecha de término de relación laboral"],
				["value" => "Reingreso"],
				["value" => "Tipo de trabajador"],
				["value" => "Régimen"],
				["value" => "Estado de Empleado"],
				["value" => "Motivo de estado"],
				["value" => "SDI"],
				["value" => "Periodicidad"],
				["value" => "Registro patronal"],
				["value" => "Forma de pago"],
				["value" => "Sueldo neto"],
				["value" => "Viaticos"],
				["value" => "Campamento"],
				["value" => "Complemento"],
				["value" => "Monto Fonacot"],
				["value" => "Porcentaje de nómina"],
				["value" => "Porcentaje de bonos"],
				["value" => "Número de crédito Infonavit"],
				["value" => "Descuento Infonavit"],
				["value" => "Tipo de descuento Infonavit"],
				["value" => "Fecha de histórico"],
				["value" => "Editor"]
			]
		];
		$modelBody = [];
		foreach($workerData as $w)
		{
			$subdepartmentName	=	"---";
			switch($w->infonavitDiscountType)
			{
				case(1):
					$discountType = "VSM (Veces Salario Mínimo)";
					break;
				case(2):
					$discountType = "Cuota fija";
					break;
				case(3): 
					$discountType = "Porcentaje";
					break;
			}

			$wbs = "---";
			if($w->employeeHasWbs()->exists())
			{
				foreach($w->employeeHasWbs as $data) 
				{
					$wbs .= $data->code_wbs.", ";
				}
			}
			if ($w->employeeHasSubdepartment()->exists())
			{
				foreach ($w->employeeHasSubdepartment as $subdepartment)
				{
					$subdepartmentName	.=	$subdepartment->name.", ";
				}
			}
			$body = 
			[
				[
					"classEx" => "sticky inset-x-0",
					"content" => 
					[
						["label" => $w->states()->exists() ? $w->states->description : '---']
					]
				],
				[
					"classEx" => "sticky inset-x-0",
					"content" => 
					[
						["label" => $w->projects()->exists() ? $w->projects->proyectName : '---']
					]
				],
				[
					"content" => 
					[
						[
							"label" => $wbs
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->enterprises()->exists() ? $w->enterprises->name : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->accounts()->exists() ? $w->accounts->account." ".$w->accounts->description." (".$w->accounts->content.")" : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->places->pluck('place')->implode(', ') !=null ? $w->places->pluck('place')->implode(', ') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->directions()->exists() ? $w->directions->name : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->departments()->exists() ? $w->departments->name : '---'
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $subdepartmentName
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->position !="" ? $w->position : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->immediate_boss !="" ? $w->immediate_boss : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->position_immediate_boss !="" ? $w->position_immediate_boss : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->admissionDate != "" ? $w->admissionDate->format('d-m-Y') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->statusImss($w->status_imss)
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->imssDate != "" ? $w->imssDate->format('d-m-Y') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->downDate != "" ? $w->downDate->format('d-m-Y') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->endingDate != "" ? $w->endingDate->format('d-m-Y') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->reentryDate != "" ? $w->reentryDate->format('d-m-Y') : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->workerType != '' ? $w->workerType." ".$w->worker->description : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->regime_id != '' ? $w->regime_id." ".$w->regime->description : '---'
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $w->workerStatus($w->workerStatus) !="" ? $w->workerStatus($w->workerStatus) : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->status_reason !="" ? $w->status_reason : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->sdi !="" ? $w->sdi : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->periodicities()->exists() ? $w->periodicities->description : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->employer_register !="" ? $w->employer_register : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->paymentMethod()->exists() ? $w->paymentMethod->method : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->netIncome !="" ? $w->netIncome : "---"
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $w->viatics !="" ? $w->viatics : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->camping !="" ? $w->camping : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->complement !="" ? $w->complement : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->fonacot !="" ? $w->fonacot : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->nomina !="" ? $w->nomina : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->bono !="" ? $w->bono : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->infonavitCredit !="" ? $w->infonavitCredit : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->infonavitDiscount !="" ? $w->infonavitDiscount : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => isset($discountType) ? $discountType : '---'
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->created_at !="" ? $w->created_at : "---"
						]
					]
				],
				[
					"content" => 
					[
						[
							"label" => $w->editor->name." ".$w->editor->last_name." ".$w->editor->scnd_last_name
						]
					]
				]
			];
			$modelBody[] = $body;
		}
	@endphp
	@component("components.tables.table",["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	{{$workerData->links()}}
@endsection
