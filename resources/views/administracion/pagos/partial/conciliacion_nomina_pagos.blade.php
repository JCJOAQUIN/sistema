@if($selectedPayment != "")
	@php
		$modelHead =
		[
			[
				["value" => "Folio"],
				["value" => "Empresa"],
				["value" => "Empleado"],
				["value" => "Tipo"],
				["value" => "Monto"],
				["value" => "Fecha"],
			]
		];
		$modelBody = [];
		foreach ($selectedPayment as $selected)
		{
			$taxPayment 	   = App\RequestModel::find($selected->idFolio)->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
			$documentsPayments = isset($selected->documentsPayments->first()->path) ? $selected->documentsPayments->first()->path : null;
			$body = [
				"classEx" => "tr selected",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->idFolio,
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->enterprise->name,
							"classEx" => "enterpriseName",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idFolio."\"",
							"classEx" => "folio_nomina",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->enterprise->name."\"",
							"classEx" => "enterprisePay_nomina",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idKind."\"",
							"classEx" => "idkind_nomina",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->nominaEmployee->employee->first()->name.' '.$selected->nominaEmployee->employee->first()->last_name.' '.$selected->nominaEmployee->employee->first()->scnd_last_name,
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $taxPayment,
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
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idpayment."\"",
							"classEx" => "idpayment_nomina",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->amount."\"",
							"classEx" => "amount_nomina",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$documentsPayments."\"",
							"classEx" => "document_nomina",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $selected->paymentDate)->format('d-m-Y'),
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
	$modelHead =
	[
		[
			["value" => "Folio"],
			["value" => "Empresa"],
			["value" => "Empleado"],
			["value" => "Tipo"],
			["value" => "Monto"],
			["value" => "Fecha"],
		]
	];
	$modelBody = [];
	foreach ($paymentsAll as $payments)
	{
		$taxPayment 	   = App\RequestModel::find($payments->idFolio)->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
		$documentsPayments = isset($payments->documentsPayments->first()->path) ? $payments->documentsPayments->first()->path : null;
		$body = [
			"classEx" => "tr",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $payments->idFolio,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $payments->enterprise->name,
						"classEx" => "enterpriseName",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->idFolio."\"",
						"classEx" => "folio_nomina",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->enterprise->name."\"",
						"classEx" => "enterprisePay_nomina",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->idKind."\"",
						"classEx" => "idkind_nomina",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $payments->nominaEmployee->employee->first()->name.' '.$payments->nominaEmployee->employee->first()->last_name.' '.$payments->nominaEmployee->employee->first()->scnd_last_name,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $taxPayment,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($payments->amount,2),
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->idpayment."\"",
						"classEx" => "idpayment_nomina",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->amount."\"",
						"classEx" => "amount_nomina",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$documentsPayments."\"",
						"classEx" => "document_nomina",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $payments->paymentDate)->format('d-m-Y'),
					],
				],
			],
		];
		$modelBody[] = $body;
	}
@endphp
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead" => "true"]) @endcomponent
<div class="result_pagination {{($paymentsAll->lastPage() == 1 ? 'hidden' : '') }}">
	{{ $paymentsAll->links() }}
</div>