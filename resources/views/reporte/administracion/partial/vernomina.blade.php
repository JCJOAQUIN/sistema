@php
	$modelTable	=
	[
		["Folio:",$request->folio],
		["Título y fecha:",htmlentities($request->nominasReal->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->nominasReal->first()->datetitle)->format('d-m-Y')],
		["Categoría:",($request->idDepartment == 11 ? 'Obra' : 'Administrativa')." - ".($request->nominasReal->first()->typeNomina())],
		["Tipo:",$request->nominasReal->first()->typePayroll->description],
	];
	switch($request->nominasReal->first()->idCatTypePayroll)
	{
		case('001'):
			$modelTable[]	= ["Periodicidad:", App\CatPeriodicity::find($request->nominasReal->first()->idCatPeriodicity)->description];
			$modelTable[]	= ["Rango de fecha:", Carbon\Carbon::createFromFormat('Y-m-d', $request->nominasReal->first()->from_date)->format('d-m-Y')." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->nominasReal->first()->to_date)->format('d-m-Y')];
		break;
	}
	$modelTable[] = ["Solicitante:", $request->requestUser->fullName()];
	$modelTable[] = ["Elaborado por:", $request->elaborateUser->fullName()];
@endphp
@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
	@slot('classEx')
		mt-4
	@endslot
	@slot('title')
		@component('components.labels.label')
			@slot('classEx')
				w-11/12
				text-center
				text-white
				ml-14
			@endslot
			Detalles de la Solicitud de Nómina de {{ App\CatTypePayroll::find($request->nominasReal->first()->idCatTypePayroll)->description }}
		@endcomponent
	@endslot
@endcomponent

@component('components.labels.title-divisor') LISTA DE EMPLEADOS @endcomponent
@php
	$flagNormalTable = false;
	$modelHead = [];
	$modelBody = [];
	if($request->taxPayment == 1)
	{
		switch($request->nominasReal->first()->idCatTypePayroll)
		{
			case('001'):
				$flagNormalTable = true;
				$modelHead = 
				[
					[
						["value"	=> "Nombre del Empleado"],
						["value"	=> "Desde",],
						["value"	=> "Hasta"],
						["value"	=> "Periodicidad"],
						["value"	=> "Faltas"],
						["value"	=> "Préstamo (Percepción)"],
						["value"	=> "Préstamo (Retención)"],
					]
				];
				foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> $n->employee->first()->fullName(),
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> Carbon\Carbon::createFromFormat('Y-m-d', $n->from_date)->format('d-m-Y'),
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> Carbon\Carbon::createFromFormat('Y-m-d', $n->to_date)->format('d-m-Y'),
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> App\CatPeriodicity::where('c_periodicity',$n->idCatPeriodicity)->first()->description,
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> $n->absence!= '' ? $n->absence : '---',
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> $n->loan_perception!= '' ? $n->loan_perception : '---',
							]
						],
						[
							"content" =>
							[
								"kind"	=> "components.labels.label",
								"label"	=> $n->loan_retention!= '' ? $n->loan_retention : '---',
							]
						],
					];
				}
				break;
			
			case('002'):
				$modelHead = [["Nombre del Empleado", "Días para aguinaldo"]];
				foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->employee->first()->fullName(),
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->day_bonus,
								],
							],
						],
					];
				}
				break;

			case('003'):
			case('004'):
				$modelHead = [["Nombre del Empleado", "Fecha de baja", "Días trabajados", "Otras percepciones"]];
				foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->employee->first()->fullName(),
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> Carbon\Carbon::createFromFormat('Y-m-d', $n->down_date)->format('d-m-Y'),
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->worked_days,
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->other_perception,
								],
							],
						],
					];
				}
				break;
			case('005'):
			case('006'):
				$modelHead = [["Nombre del Empleado", "Días trabajados"]];
				foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
				{
					$modelBody[] = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->employee->first()->fullName(),
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"	=> "components.labels.label",
									"label"	=> $n->worked_days,
								],
							],
						],
					];
				}
				break;
		}	
	}
	else
	{
		$modelHead = [["Nombre del Empleado", "Tipo", "Fiscal/No Fiscal"]];

		foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
		{
			$modelBody[] = 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						[
							"kind"	=> "components.labels.label",
							"label"	=> $n->employee->first()->fullName(),
						],
					],
				],
				[
					"content" =>
					[
						[
							"kind"	=> "components.labels.label",
							"label"	=> $n->type == 1 ? 'Obra' : 'Administrativa',
						],
					],
				],								
				[
					"content" =>
					[
						[
							"kind"	=> "components.labels.label",
							"label"	=> $n->typeNomina(),
						],
					],
				],
			];
		}
	}		
@endphp
@if ($flagNormalTable)
	@component("components.tables.table",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"themeBody" => "striped"
	])
	@endcomponent
@else
	@component("components.tables.alwaysVisibleTable",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
	])
	@endcomponent
@endif

@php
	$payments 		= App\Payment::where('idFolio',$request->folio)->get();
	$total 			= $request->nominasReal->first()->amount;
	$totalPagado 	= 0;
@endphp
@if(count($payments) > 0)
	@component('components.labels.title-divisor') HISTORIAL DE PAGOS @endcomponent
	@php
		$modelHead = 
		[
			[
				["value" => "Empleado"],
				["value" => "Empresa",],
				["value" => "Cuenta"],
				["value" => "Cantidad"],
				["value" => "Documento"],
				["value" => "Fecha"],
			]
		];
		$modelBody = [];
		foreach($payments as $pay)
		{
			$contentDocs = [];
			foreach($pay->documentsPayments as $doc)
			{
				$containerButton = "";
				$containerButton .= '<div class="w-full">';
				$containerButton .= view('components.buttons.button',[																
					"buttonElement" => "a",
					"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/payments/'.$doc->path)."\"",
					"variant" => "secondary",
					"label" => "Archivo",
				])->render();
				$containerButton .= '</div>';
				$contentDocs [] =
				[
					"label" => $containerButton,
				];
			}
			if (count($contentDocs) == 0)
			{	
				$contentDocs [] =
				[
					"label" => "---",
				];
			}

			$modelBody[] = 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						"kind"	=> "components.labels.label",
						"label" => $pay->nominaEmployee->employee->first()->fullName(),
					]
				],
				[
					"content" =>
					[
						"kind"	=> "components.labels.label",
						"label" => $pay->enterprise->name,
					]
				],
				[
					"content" =>
					[
						"kind"	=> "components.labels.label",
						"label" => $pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.')',
					]
				],
				[
					"content" =>
					[
						"kind"	=> "components.labels.label",
						"label" => "$ ".number_format($pay->amount,2),
					]
				],
				[
					"content" => $contentDocs,
				],
				[
					"content" =>
					[
						"kind"	=> "components.labels.label",
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $pay->paymentDate)->format('d-m-Y H:i:s'),
					],
				],
			];
			$totalPagado += $pay->amount;
		}
	@endphp

	@component("components.tables.table",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"themeBody" => "striped"
	])
	@endcomponent	

	@php
		$modelTable	=
		[
			["label" => "Total pagado: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => "$ ".number_format($totalPagado,2)]]],
			["label" => "Resta: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => "$ ".number_format($total-$totalPagado,2)]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
@endif

<div class="my-6">
	<div class="text-center">
		@component("components.buttons.button",[
			"variant"		=> "success",
			"attributeEx" 	=> "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"",
			"label"			=> "« Ocultar",
			"classEx"		=> "exit",
		])  
		@endcomponent
	</div>
</div>