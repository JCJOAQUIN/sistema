@if(isset($selectedPayment) && $selectedPayment != "")
	@php
		$modelHead = 
		[
			[
				["value" => "Tipo"],
				["value" => "Empresa"],
				["value" => "Cuenta"],
				["value" => "Monto"],
				["value" => "Fecha"],
				["value" => "Descripción"],
			]
		];
		$modelBody = [];
		$billpurchase = "";
		foreach ($selectedPayment as $selected)
		{
			$path = isset($selected->documentsPayments->first()->path) ? $selected->documentsPayments->first()->path : "";
			$requestPurchase = App\Purchase::where('idFolio',$selected->idFolio)->where('idKind',$selected->idKind)->count();
			if($requestPurchase > 0)
			{
				$purchases = App\Purchase::where('idFolio',$selected->idFolio)->where('idKind',$selected->idKind)->get();
				if(count($purchases->first()->documents)>0)
				{
					$billpurchase = "si hay";
				}
			}
			$paymentRemittance = $selected->remittance == 1 ? 'remesa' : 'pago';
			$kindFolio = mb_strtolower(App\RequestKind::find($selected->idKind)->kind."".$selected->idFolio);
			$body = 
			[
				"attributeEx" => "title=\"Ver datos\"",
				"classEx" => "tr selected",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $selected->remittance == 1 ? 'Remesa' : 'Pago',
							"classEx" => "paymentRemittance",
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
							"classEx" => "folio",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->enterprise->name."\"",
							"classEx" => "enterprisePay",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idKind."\"",
							"classEx" => "idkind",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$billpurchase."\"",
							"classEx" => "billpurchase",
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
							"classEx" => "accountDescription",
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
							"classEx" => "paymentAmount",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->idpayment."\"",
							"classEx" => "idpayment",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$selected->amount."\"",
							"classEx" => "amount",
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".$path."\"",
							"classEx" => "document",
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
							"classEx" => "paymentDate"
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => "Solicitud de ".App\RequestKind::find($selected->idKind)->kind." #".$selected->idFolio,
							"classEx" => "paymentKind"
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
			["value" => "Tipo"],
			["value" => "Empresa"],
			["value" => "Cuenta"],
			["value" => "Monto"],
			["value" => "Fecha"],
			["value" => "Descripción"],
		]
	];
	$modelBody = [];
	$billpurchase = "";
	foreach ($paymentsAll as $payments)
	{
		$path = isset($payments->documentsPayments->first()->path) ? $payments->documentsPayments->first()->path : "";
		$requestPurchase = App\Purchase::where('idFolio',$payments->idFolio)->where('idKind',$payments->idKind)->count();
		if($requestPurchase > 0)
		{
			$purchases = App\Purchase::where('idFolio',$payments->idFolio)->where('idKind',$payments->idKind)->get();
			if(count($purchases->first()->documents)>0)
			{
				$billpurchase = "si hay";
			}
		}
		$paymentRemittance = $payments->remittance == 1 ? 'remesa' : 'pago';
		$kindFolio = mb_strtolower(App\RequestKind::find($payments->idKind)->kind."".$payments->idFolio);
		$body = [
			"attributeEx" => "title=\"Ver datos\"",
			"classEx" => "tr",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $payments->remittance == 1 ? 'Remesa' : 'Pago',
						"classEx" => "paymentRemittance",
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
						"classEx" => "folio",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->enterprise->name."\"",
						"classEx" => "enterprisePay",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->idKind."\"",
						"classEx" => "idkind",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$billpurchase."\"",
						"classEx" => "billpurchase",
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => $payments->accounts->account." - ".$payments->accounts->description." (".$payments->accounts->content.")",
						"classEx" => "accountDescription",
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
						"classEx" => "paymentAmount",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->idpayment."\"",
						"classEx" => "idpayment",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$payments->amount."\"",
						"classEx" => "amount",
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" value=\"".$path."\"",
						"classEx" => "document",
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
						"classEx" => "paymentDate"
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "Solicitud de ".App\RequestKind::find($payments->idKind)->kind." #".$payments->idFolio,
						"classEx" => "paymentKind"
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
