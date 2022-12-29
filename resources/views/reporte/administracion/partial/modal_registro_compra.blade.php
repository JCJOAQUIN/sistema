<div class="pb-6">
		@php
			$elaborateUser 	= App\User::find($request->idElaborate);
			$modelTable 	=
			[
				["Folio", $request->folio],
				["Título y fecha", htmlentities($request->purchaseRecord->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseRecord->datetitle)->format('d-m-Y')],
				["Número de Orden", $request->purchaseRecord->numberOrder!="" ? htmlentities($request->purchaseRecord->numberOrder) : '---'],
				["Fiscal", $request->taxPayment == 1 ? "Sí" : "No"],
				["Solicitante", $request->requestUser->fullName()],
				["Elaborado por", $request->elaborateUser->fullName()],
				["Empresa", $request->requestEnterprise->name],
				["Dirección", $request->requestDirection->name],
				["Departamento", $request->requestDepartment->name],
				["Clasificación de gasto", $request->accounts->fullClasificacionName()],
				["Proyecto", $request->requestProject->proyectName]
			];

			if($request->wbs()->exists())
			{
				$modelTable[]	= ["WBS:", $request->wbs->code_wbs];
			}
			if($request->edt()->exists())
			{
				$modelTable[]	= ["EDT:", $request->edt->description];
			}
			$modelTable[] = ["Proveedor", htmlentities($request->purchaseRecord->provider)];

		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent	
	</div>
	@component('components.labels.title-divisor')    DATOS DEL PEDIDO @endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= 
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
		$countConcept = 1;
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$taxesConcept		= $detail->taxes()->sum('amount');
			$retentionConcept	= $detail->retentions()->sum('amount');
			$body = 
			[
				[
					"content"	=>
					[
						"label" => $countConcept
					]
				],
				[ 
					"content"	=>
					[
						"label" => $detail->quantity
					]
				],
				[
					"content"	=>
					[
						"label" => htmlentities($detail->unit),
					]
				],
				[
					"content"	=>
					[
						"label" => htmlentities($detail->description),
					]
				],
				[
					"content"	=> 
					[
						"label" => "$".number_format($detail->unitPrice,2)
					]
				],
				[
					"content"	=>
					[
						"label" => "$".number_format($detail->tax,2)
					]
				],
				[
					"content"	=>
					[
						"label" => "$".number_format($taxesConcept,2)
					]
				],
				[
					"content"	=>
					[
						"label" => "$".number_format($retentionConcept,2)
					]
				],
				[
					"content"	=>
					[
						"label" => "$".number_format($detail->total,2)
					]
				]
			];
			$modelBody[] = $body;
			$countConcept++;
		}
	@endphp
	@component("components.tables.table",
		[
			"modelHead"	=> $modelHead,
			"modelBody"	=> $modelBody,
		])
	@endcomponent
	<div class="mt-6">
		@component("components.labels.title-divisor") Totales @endcomponent
		@php
			$tableTotals =
			[
				[
					"label"		=> "Subtotal:", 
					"inputsEx"	=> 
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> "$".number_format($request->purchaseRecord->subtotal,2),
						]

					]
				],
				[
					"label"		=> "Impuestos adicionales:", 
					"inputsEx"	=> 
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> "$".number_format($request->purchaseRecord->amount_taxes,2),
						]

					]
				],
				[
					"label"		=> "Retenciones:", 
					"inputsEx"	=> 
					[
							[
							"kind"		=> "components.labels.label",
							"label"		=> "$".number_format($request->purchaseRecord->amount_retention,2),
						]

					]
				],
				[
					"label"		=> "IVA:", 
					"inputsEx"	=> 
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> "$".number_format($request->purchaseRecord->tax,2),
						]

					]
				],
				[
					"label"		=> "Total:", 
					"inputsEx"	=> 
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> "$".number_format($request->purchaseRecord->total,2),
						]

					]
				],
			];
		@endphp
		@component("components.templates.outputs.form-details", ["modelTable" => $tableTotals, "title" => "", "classEx" => "mt-6"]) @endcomponent
	</div>
	<div class="mt-6">
		@component("components.labels.title-divisor")
			CONDICIONES DE PAGO
		@endcomponent
		@php
			$modelTable = 
			[
				"Empresa"			=> $request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '',
				"Cuenta"			=> $request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '',
				"Referencia"		=> htmlentities($request->purchaseRecord->reference),
				"Tipo de moneda"	=> $request->purchaseRecord->typeCurrency,
				"Fecha de pago"		=> $request->PaymentDate != "" ? Carbon\Carbon::parse($request->PaymentDate)->format('d-m-Y') : '',
				"Forma de pago"		=> $request->purchaseRecord->paymentMethod,
				"Estado de factura"	=> $request->purchaseRecord->billStatus,
				"Importe a pagar"	=> "$".number_format($request->purchaseRecord->total,2)
			];
		@endphp
		@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable])@endcomponent
	</div>

	@if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		@php
			$t		= App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user	= App\User::find($t->assignment);
			$status	= $principal = '';
			switch ($t->status) 
			{
				case 1:
					$status = 'Vigente';
					break;
				case 2:
					$status = 'Bloqueada';
					break;
				case 3:
					$status = 'Cancelada';
					break;
				default:
					break;
			}
			switch ($t->principal_aditional) 
			{
				case 1:
					$principal = 'Principal';
					break;
				case 2:
					$principal = 'Adicional';
					break;
				default:
					break;
			}
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Responsable"],
					["value"	=> "Nombre en Tarjeta"],
					["value"	=> "Número de Tarjeta"],
					["value"	=> "Status"],
					["value"	=> "Principal/Adicional"]
				]
			];
			$body = 
			[
				[ 
					"content"	=>
					[
						"label" => $user->fullName()
					]
				],
				[
					"content"	=>
					[
						"label" => $t->name_credit_card
					]
				],
				[ 
					"content"	=>
					[
						"label" => $t->credit_card
					]
				],
				[
					"content"	=>
					[
						"label" => $status
					]
				],
				[
					"content"	=> 
					[
						"label" => $principal
					]
				]
			];
			$modelBody[] = $body;
		@endphp
		@component("components.tables.table",
			[
				"modelHead"	=> $modelHead,
				"modelBody"	=> $modelBody,
			])
		@endcomponent
	@endif
	<div class="col-span-2 md:col-span-4 table-striped">
		@if(count($request->purchaseRecord->documents)>0)
			@component("components.labels.title-divisor")
				Documentos de la solicitud
			@endcomponent
			@php
				$documentsBody = [];
				$modelHead = ["Tipo de documento", "Archivo", "Fecha"];
				foreach($request->purchaseRecord->documents as $doc)
				{
					$date	= Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y');
					$row	=
					[
						"classEx"	=> "tr",
						[
							"content"	=> 
							[
								["kind" => "components.labels.label", "label" => $doc->name ]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "secondary",
									"buttonElement"	=> "a",
									"attributeEx"	=> "target=\"_blank\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/purchase-record/'.$doc->path)."\"",
									"label"			=> "Archivo"
								]
							]
						],
						[
							"content"	=> 
							[
								["kind"	=> "components.labels.label", "label" => $date]
							]
						]
					];
					$documentsBody[]	= $row;
				}
			@endphp
			<div class="table-responsive">
				@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $documentsBody,"variant" => "default", "attributeExBody" => "id=\"bodyT\"", "attributeEx" => "id=\"table-documents\""]) @endcomponent
			</div>
			
		@endif
	</div>