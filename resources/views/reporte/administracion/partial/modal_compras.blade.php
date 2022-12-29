<div class="pb-6">
	@component('components.labels.title-divisor') DATOS DEL PROVEEDOR @endcomponent
	@php
		$modelTable =
		[
			"Razón Social "	=> $request->purchases->first()->provider->businessName,
			"RFC "			=> $request->purchases->first()->provider->rfc,
			"Teléfono "		=> $request->purchases->first()->provider->phone,
			"Calle "		=> $request->purchases->first()->provider->address,
			"Número "		=> $request->purchases->first()->provider->number,
			"Colonia "		=> $request->purchases->first()->provider->colony,
			"CP"			=> $request->purchases->first()->provider->postalCode,
			"Ciudad "		=> $request->purchases->first()->provider->city,
			"Estado "		=> App\State::find($request->purchases->first()->provider->state_idstate)->description,
			"Contacto "		=> $request->purchases->first()->provider->contact,
			"Beneficiario "	=> $request->purchases->first()->provider->beneficiary,
			"Otro "			=> $request->purchases->first()->provider->commentaries
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent

	@php
		$body 	   = [];
		$modelBody = [];
		$modelHead = 
		[
			[
				["value"	=> "Banco"],
				["value"	=> "Alias"],
				["value"	=> "Cuenta"],
				["value"	=> "Sucursal"],
				["value"	=> "Referencia"],
				["value"	=> "CLABE"],
				["value"	=> "Moneda"],
				["value"	=> "IBAN"],
				["value"	=> "BIC/SWIFT"],
				["value"	=> "Convenio"]
			]
		];
		if(isset($request->purchases))
		{
			foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
			{
				$classEx = "";
				if($request->purchases->first()->provider_has_banks_id == $bank->id) 
				{
					$classEx = "marktr";
				}

				if($request->purchases->first()->provider_has_banks_id == $bank->id)
				{
					$value_idchecked = 1;
				}
				else 
				{
					$value_idchecked = 0; 
				}
				$body = 
				[
					"classEx" => $classEx,
					[
						"content"	=> 
						[
							"label" => $bank->bank->description
						]
					],
					[ 
						"content"	=> 
						[
							"label" => $bank->alias
						]
					],
					[
						"content"	=> 
						[ 
							"label" => $bank->account
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->branch
						]
					],
					[
						"content" => 
						[
							"label" => $bank->reference != "" ? $bank->reference : "---"
						]
					],
					[
						"content" => 
						[
							"label" => $bank->clabe
						]
					],
					[
						"content" => 
						[
							"label" => $bank->currency
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->iban != "" ? $bank->iban : "---"
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->bic_swift != "" ? $bank->bic_swift : "---"
						]
					],
					[
						"content" => 
						[
							"label" => $bank->agreement != "" ? $bank->agreement : "---"
						]
					]
				];
				array_push($modelBody, $body);
				
			}
		}
	@endphp
	@component('components.tables.table',
		[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
		])
	@endcomponent
</div>
<div class="pb-6">
	@component('components.labels.title-divisor') 
		DATOS DEL PEDIDO 
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$countConcept = 1;
		$body 	      = [];
		$modelBody    = [];
		$modelHead    = 
		[
			[
				["value"	=> "#"],
				["value"	=> "Cantidad"],
				["value"	=> "Unidad"],
				["value"	=> "Descripción"],
				["value"	=> "Precio Unitario"],
				["value"	=> "IVA"],
				["value"	=> "Impuesto Adicional"],
				["value"	=> "Retenciones"],
				["value"	=> "Importe"]
			]
		];
		
		foreach($request->purchases->first()->detailPurchase as $detail)
		{
			$taxesConcept=0;
			foreach($detail->taxes as $tax)
			{
				$taxesConcept+=$tax->amount;
			}

			$retentionConcept=0;
			foreach($detail->retentions as $ret)
			{
				$retentionConcept+=$ret->amount;
			}
			$body = 
			[
				[
					"content" => 
					[
						"label" => $countConcept
					]
				],
				[ 
					"content" => 
					[
						"label" => $detail->quantity
					]
				],
				[
					"content" => 
					[ 
						"label" => $detail->unit
					]
				],
				[
					"content" => 
					[ 
						"label" => $detail->description
					]
				],
				[
					"content" => 
					[
						"label" => "$ ".number_format($detail->unitPrice,2)
					]
				],
				[
					"content" => 
					[
						"label" => "$ ".number_format($detail->tax,2)
					]
				],
				[
					"content" => 
					[
						"label" => "$ ".number_format($taxesConcept,2)
					]
				],
				[
					"content" => 
					[ 
						"label" => "$ ".number_format($retentionConcept,2)
					]
				],
				[
					"content" => 
					[ 
						"label" => "$ ".number_format($detail->amount,2)
					]
				]
			];
			array_push($modelBody, $body);
			$countConcept++;
		}
	@endphp
	@component('components.tables.table',
		[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
			"themeBody"	=> "striped"
		])
	@endcomponent
</div>
<div class="pb-6">
	@php
		$notas		= "name=\"note\" placeholder=\"Ingrese la nota\" cols=\"80\" readonly=\"readonly\"";
		$taxes		= 0;
		$retentions	= 0;
		$textNotes	= "";

		foreach($request->purchases->first()->detailPurchase as $detail)
		{				
			$taxes		+= $detail->taxes->sum('amount');				
			$retentions	+= $detail->retentions->sum('amount');
		}
		$subtotal	= "$ ".number_format($request->purchases->first()->subtotales,2);
		$iva		= "$ ".number_format($request->purchases->first()->tax,2);
		$total		= "$ ".number_format($request->purchases->first()->amount,2);
		$textNotes	= $request->purchases->first()->notes != "" ? $request->purchases->first()->notes : "";
		$modelTable = 
		[
			[
				"label"		=> "Subtotal: ", 
				"inputsEx"	=> 
				[
					[
						"kind"	=> "components.labels.label",
						"label"	=> $subtotal,
					]
				]
			],
			[
				"label"		=> "Impuesto Adicional: ",	
				"inputsEx"	=> 
				[
					[
						"kind"	=> "components.labels.label",
						"label"	=> "$ ".number_format($taxes,2)
					]
				]
			],
			[
				"label"		=> "Retenciones: ",	
				"inputsEx"	=> 
				[
					[
						"kind"	=> "components.labels.label",
						"label"	=> "$ ".number_format($retentions,2)
					]
				]
			],
			[
				"label"		=> "IVA: ",	
				"inputsEx"	=> 
				[
					[
						"kind"	=> "components.labels.label",
						"label"	=> $iva,
					]
				]
			],
			[
				"label"		=> "TOTAL: ", 
				"inputsEx"	=> 
				[
					[
						"kind"	=> "components.labels.label",
						"label"	=> $total,
					]
				]
			],
		];
	@endphp
	@component('components.templates.outputs.form-details',[
		"modelTable" 		 => $modelTable,
		"attributeExComment" => $notas,
		"textNotes"          => $textNotes
	])
	@endcomponent
	
	@component('components.labels.title-divisor') 
		CONDICIONES DE PAGO
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$date = $request->PaymentDate != '' ? new \DateTime($request->PaymentDate) : "";
		$modelTable =
		[
			"Referencia/Número de factura "	=> $request->purchases->first()->reference,
			"Tipo de moneda "				=> $request->purchases->first()->typeCurrency,
			"Fecha de pago "				=> $date->format('d-m-Y'),
			"Forma de pago "				=> $request->purchases->first()->paymentMode,
			"Estatus de factura "			=> $request->purchases->first()->billStatus,
			"Importe a pagar "				=> number_format($request->purchases->first()->amount,2)
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent

	@component('components.labels.title-divisor') 
		DOCUMENTOS
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$body 	   = [];
		$no_result = true;
		$modelBody = [];
		$modelHead = ["Tipo de Documento" ,"Archivo", "Fecha"];
		if(count($request->purchases->first()->documents)>0)
		{
			foreach($request->purchases->first()->documents as $doc)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"label" => $doc->name
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "dark-red",
								"label"         => "PDF",
								"buttonElement" => "a",
								"attributeEx"   => "target = \"_blank\" href = \"".url('docs/purchase/'.$doc->path)."\""
							]
						]
					],
					[ 
						"content" => 
						[
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y'),
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
			$no_result = false;
		}
	@endphp
	@if($no_result)
		@component("components.labels.not-found")
			@slot("slot")
				No hay documentos
			@endslot
		@endcomponent
	@else
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot('attributeExBody')
				id="banks-body"
			@endslot
		@endcomponent
	@endif
</div>
<div class="pb-6">
	@component('components.labels.title-divisor') 
		HISTORIAL DE PAGOS
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$payments 		= $request->paymentsRequest;
		$total 			= $request->purchases->first()->amount;
		$totalPagado 	= 0;
		if(count($payments)>0)
		{
			$body 		= [];
			$modelBody 	= [];
			$modelHead	= 
			[
				[
					["value" => "Cuenta"],
					["value" => "Cantidad"],
					["value" => "Documento"],
					["value" => "Fecha"]
				]
			];
			foreach($payments as $pay)
			{ 
				$body = 
				[
					[
						"content" => 
						[
							"label" => $pay->accounts->account.' - '.$pay->accounts->description
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($pay->amount,2)
						]
					],
				];
				if($pay->documentsPayments()->exists())
				{
					$docsContent = [];
					foreach($pay->documentsPayments as $doc)
					{
						$docsContent['content'][] = 
						[
							"kind" 			=> "components.buttons.button",
							"variant"		=> "dark-red",
							"buttonElement" => "a",
							"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/payments/'.$doc->path)."\"",
							"label"			=> 'PDF'
						];
					}
				}
				else 
				{
					$docsContent['content'] = 
					[
						"label" => "Sin documento"
					];
				}
				$body[] = $docsContent;
				$body[] =  
				[ 
					"content" => 
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')
					]
				];
				$modelBody[] = $body;
			}
		}
	@endphp
	@if(count($payments) > 0)
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
		@endcomponent
	@else
		@component("components.labels.not-found")
		@endcomponent
	@endif
</div>