@component("components.labels.title-divisor") RELACIÓN DE DOCUMENTOS @endcomponent
@php
	$modelHead = 
	[
		[
			["value" => "#"],
			["value" => "Concepto"],
			["value" => "Clasificación del gasto"],
			["value" => "Fiscal"],
			["value" => "Subtotal"],
			["value" => "IVA"],
			["value" => "Impuesto Adicional"],
			["value" => "Retenciones"],
			["value" => "Importe"],
			["value" => "Documento(s)"]
		]
	];
	$subtotalFinal = $ivaFinal = $totalFinal = 0;
	$countConcept = 1;
	$modelBody = [];
	foreach($request->refunds->first()->refundDetail as $refundDetail)
	{       
		$subtotalFinal	+= $refundDetail->amount;
		$ivaFinal		+= $refundDetail->tax;
		$totalFinal		+= $refundDetail->sAmount;
		$taxes2			= $refundDetail->taxes->sum('amount');
		$retentions2	= $refundDetail->retentions->sum('amount');

		if($refundDetail->taxPayment==1) 
		{
			$taxPayment = "si";
		}
		else
		{
			$taxPayment = "no";
		}

		if($refundDetail->account()->exists())
		{
			$accountDescription = $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.')';
		}
		else
		{
			$accountDescription = "";
		}

		$body = 
		[
			"classEx" => "tr",
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => $countConcept,
					]
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => htmlentities($refundDetail->concept),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $accountDescription,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $taxPayment,
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->amount,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->tax,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($taxes2,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($retentions2,2),
					],
				],
			],
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->sAmount,2),
					],
				],
			]
		];
		
		
		if($refundDetail->refundDocuments()->exists())
		{
			$contentBodyDocs = [];
			foreach($refundDetail->refundDocuments as $doc)
			{
				if ($doc->datepath != "") 
				{
					$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->datepath)->format('d-m-Y');
				}
				else
				{
					$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y');
				}

				if($doc->name != '')
				{
					$docName = $doc->name;
				}
				else
				{
					$docName = "Otro";
				}

				$contentBodyDocs[] = 
				[
					"kind"	=> "components.labels.label",
					"label"	=> $date,
				];
				$contentBodyDocs[] = 
				[
					"kind"	=> "components.labels.label",
					"label"	=> $docName,
				];
				$contentBodyDocs[] = 
				[
					"kind"			=> "components.buttons.button",                                  
					"buttonElement"	=> "a",
					"attributeEx"	=> "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset("docs/refounds/".$doc->path)."\"",
					"variant"		=> "dark-red",
					"label"			=> "PDF",
				];
			}
		}
		else
		{
			$contentBodyDocs[] =
			[
				"kind"  => "components.labels.label",
				"label" => "---",
			];
		}
		$body[] = [
			"classEx" => "td",
			"content" => $contentBodyDocs
		];
		$modelBody [] = $body;
		$countConcept++;
	}
@endphp
@component("components.tables.table",[
	"modelHead" => $modelHead,
	"modelBody" => $modelBody,
	"classEx"   => "my-6",
	"themeBody" => "striped"
])
	@slot("classExBody")
		request-validate
	@endslot
	@slot("attributeExBody")
		id="body"
	@endslot
@endcomponent
@php
	$modelTable	= [];
	$taxes		= 0;
	$retentions	= 0;

	if($totalFinal!=0)
	{
		$labelSubtotal	= "$ ".number_format($subtotalFinal,2);
		$labelIVA		= "$ ".number_format($ivaFinal,2);
		$labelTotal		= "$ ".number_format($totalFinal,2);
	}
	else 
	{
		$valueSubtotal	= "";
		$labelSubtotal	= "$ 0.00";
		$valueIVA		= "";
		$labelIVA		= "$ 0.00";
		$valueTotal		= "";
		$labelTotal		= "$ 0.00";
	}

	foreach($request->refunds->first()->refundDetail as $detail)
	{
		$taxes		+= $detail->taxes->sum('amount');
		$retentions	+= $detail->retentions->sum('amount');
	}
	
	$modelTable = 
	[   
		[
			"label"		=> "Subtotal: ", 
			"inputsEx"	=> 
			[
				[
					"kind"		=> "components.labels.label",  
					"label"		=> $labelSubtotal, 
					"classEx"	=> "my-2 label-subtotal"
				]
			]
		],
		[
			"label"		=> "IVA: ", 
			"inputsEx"	=> 
			[
				[
					"kind"		=> "components.labels.label",   
					"label"		=>  $labelIVA, 
					"classEx"	=> "my-2 label-IVA"
				]
			]
		],
		[
			"label"		=> "Impuesto Adicional: ", 
			"inputsEx"	=> 
			[
				[
					"kind"		=> "components.labels.label",    
					"label"		=> "$ ".number_format($taxes,2), 
					"classEx"	=> "my-2 label-taxes"
				]
			]
		],
		[
			"label"		=> "Retenciones: ", 
			"inputsEx"	=> 
			[
				[
					"kind"		=> "components.labels.label",   
					"label"		=> "$ ".number_format($retentions,2), 
					"classEx"	=> "my-2 label-retentions"
				]
			]
		],
		[
			"label"		=> "TOTAL: ", 
			"inputsEx"	=> 
			[
				[
					"kind"		=> "components.labels.label", 
					"label"		=>  $labelTotal, 
					"classEx"	=> "my-2 label-total"
				]
			]
		],
	];
@endphp
@component("components.templates.outputs.form-details", ["modelTable" => $modelTable, "classEx" => "mb-6"]) @endcomponent