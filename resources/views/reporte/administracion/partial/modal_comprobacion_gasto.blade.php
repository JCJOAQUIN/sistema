<div class="pb-6">
	@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
	@php
		$taxes 	= 0;
		$taxes3 = 0;
		$docs 	= 0;
		foreach($request->expenses as $expense)
		{
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes3 += $tax->amount;
					}
				}
			}
			$var_paymentMethod	= $expense->paymentMethod->method;
			$var_reference		= $expense->reference != '' ? $expense->reference : '---';
			$var_currency		= $expense->currency;
			$var_total			= '$ '.number_format($expense->total,2);
		}
		
		$var_description	= '';
		$var_alias			= '';
		$var_cardNumber	 	= '';
		$var_clabe			= '';
		$var_account	 	= '';
		foreach($request->expenses as $expense)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() as $bank)
			{	
				if($expense->idEmployee == $bank->idEmployee)
				{
					$var_description	= $bank->description;
					$var_alias			= $bank->alias!=null ? $bank->alias : '---';
					$var_cardNumber	 	= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$var_clabe			= $bank->clabe!=null ? $bank->clabe : '---';
					$var_account	 	= $bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable = 
		[
			"Forma de pago"		=> $var_paymentMethod,
			"Referencia"		=> $var_reference,
			"Tipo de moneda"	=> $var_currency,
			"Importe"			=> $var_total,
			"Banco"				=> $var_description,
			"Alias"				=> $var_alias,
			"Número de tarjeta"	=> $var_cardNumber,
			"CLABE"				=> $var_clabe,
			"Número de cuenta"	=> $var_account,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
</div>
<div class="pb-6">
	@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS @endcomponent
	<div class="mt-4"> 
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "#"],
					["value" => "Concepto"],
					["value" => "Clasificación del gasto"],
					["value" => "Fiscal"],
					["value" => "Subtotal"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Importe"],
					["value" => "Documento(s)"]
				]
			];
			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			$countConcept = 1;
			foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
			{
				$subtotalFinal	+= $expensesDetail->amount;
				$ivaFinal		+= $expensesDetail->tax;
				$totalFinal		+= $expensesDetail->sAmount;

				$varAccount = '';
				if(isset($expensesDetail->account))
				{
					$varAccount = $expensesDetail->account->account.' '.$expensesDetail->account->description;
				}
				$varTax = '';
				if($expensesDetail->taxPayment==1)
				{
					$varTax = 'Si';
				}
				else
				{
					$varTax = 'No';
				}
				$taxes2 = 0;
				foreach($expensesDetail->taxes as $tax)
				{
					$taxes2 += $tax->amount;
				}
				$varTaxAmount = '$ '.number_format($taxes2,2);

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
							"label" => $expensesDetail->concept
						]
					],
					[
						"content" =>
						[
							"label" => $varAccount
						]
					],
					[
						"content" =>
						[
							"label" => $varTax
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->amount,2)
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->tax,2)
						]
					],
					[
						"content" =>
						[
							"label" => $varTaxAmount
						]
					],
					[
						"content" =>
						[
							"label" => '$ '.number_format($expensesDetail->sAmount,2)
						]
					]
				];
				$testDoc = '';
				if($expensesDetail->documents()->exists())
				{
					foreach($expensesDetail->documents as $doc)
					{
						if($doc->name != '')
						{ 
							$varName = $doc->name; 
						}
						else
						{
							$varName = 'Otro';
						}
						$testDoc .= '<div class="nowrap">';
						$testDoc .= view('components.labels.label',[
							"label"		=> $doc->date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y'): ''
						])->render();
						$testDoc .= view('components.labels.label',[
							"label"		=> $varName
						])->render();
						$testDoc .= view('components.buttons.button',[
							"variant"		=> "dark-red",
							"buttonElement"	=> "a",
							"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/expenses/'.$doc->path)."\"",
							"label"			=> 'PDF'
						])->render();
						$testDoc .= "</div>";
					}
				}
				else
				{
					$testDoc	= "Sin documento";
				}
				$body[] = 
				[
					"content" => 
					[ 
						"label"	=> $testDoc 
					]
				];
				$modelBody[]	= $body;
				$countConcept++;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead
		])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
			@slot('classExBody')
				request-valid
			@endslot
		@endcomponent
	</div>
	<div class="totales">
		@php
			$varSubTotal		= '';
			$varIva				= '';
			$varTotal			= '';
			$varSubTotalLabel	= "$ 0.00";
			$varIvaLabel		= "$ 0.00";
			$varTotalLabel		= "$ 0.00";
			if($totalFinal!=0)
			{
				$varSubTotal		= number_format($subtotalFinal,2);
				$varIva				= number_format($ivaFinal,2);
				$varTotal			= number_format($totalFinal,2);
				$varSubTotalLabel	= '$ '.number_format($subtotalFinal,2);
				$varIvaLabel		= '$ '.number_format($ivaFinal,2);
				$varTotalLabel		= '$ '.number_format($totalFinal,2);
			}  
			$varReintegro 		= '';
			$varReembolso 		= '';
			$varReintegroLabel 	= '$ 0.00';
			$varReembolsoLabel 	= '$ 0.00';
			if(isset($request->expenses))
			{
				foreach($request->expenses as $expense)
				{
					$varReintegro		= number_format($expense->reintegro,2);
					$varReembolso		= number_format($expense->reembolso,2);
					$varReintegroLabel	= '$ '.number_format($expense->reintegro,2);
					$varReembolsoLabel	= '$ '.number_format($expense->reembolso,2);
				}
			}
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}	 
				}
			}
			$modelTable = [
				[
					"label" => "Subtotal:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> $varSubTotalLabel,
							"classEx"	=> "my-2 subtotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx"		=> "subtotal",	
							"attributeEx"	=> "type=\"hidden\" readonly id=\"subtotal\" name=\"subtotal\" value=\"".$varSubTotal."\""
						]
					]
				], 
				[
					"label" => "IVA:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> $varIvaLabel,
							"classEx"	=> "my-2 ivaTotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx"		=> "ivaTotal",	
							"attributeEx"	=> "type=\"hidden\" readonly id=\"iva\" name=\"iva\" value=\"".$varIva."\""
						]
					]
				], 
				[
					"label" => "Impuesto Adicional:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> '$ '.number_format($taxes,2),
							"classEx"	=> "my-2 labelAmount"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" readonly name=\"amountAA\" value=\"".number_format($taxes,2)."\""
						]
					]
				], 
				[
					"label" => "Reintegro:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> $varReintegroLabel,
							"classEx"	=> "my-2 reintegro"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" readonly id=\"reintegro\" name=\"reintegro\" value=\"".$varReintegro."\"",
							"classEx"		=> "reintegro"
						]
					]
				],
				[
					"label" => "Reembolso:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> $varReembolsoLabel,
							"classEx"	=> "my-2 reembolso"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx"	=> "type=\"hidden\" readonly id=\"reembolso\" name=\"reembolso\" value=\"".$varReembolso."\"",
							"classEx"		=> "reembolso"
						]
					]
				],
				[
					"label" => "TOTAL:", "inputsEx" =>
					[
						[
							"kind"		=> "components.labels.label",
							"label"		=> $varTotalLabel,
							"classEx"	=> "my-2 total"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx"		=> "total",	
							"attributeEx"	=> "type=\"hidden\" id=\"total\" readonly name=\"total\" value=\"".$varTotal."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', [ "modelTable" => $modelTable]) @endcomponent
	</div>
</div>
<div class="pb-6">
	@if($request->status == 13)
		@component('components.labels.title-divisor') DATOS DE PAGOS @endcomponent
		@php
			$varComment = '';
			if($request->paymentComment == "")
			{
				$varComments = 'Sin comentarios';
			}
			else
			{
				$varComment = $request->paymentComment;
			}	
			$modelTable = [ "Comentarios"	=> $varComment ];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@endif
	@php
		$payments		= App\Payment::where('idFolio',$request->folio)->get();
		$total 			= $request->expenses->first()->total;
		$totalPagado	= 0;
	@endphp
	@if(count($payments))
		@component('components.labels.title-divisor') HISTORIAL DE PAGOS @endcomponent
		<div class="my-4"> 
			@php
				$body 		= [];
				$modelBody 	= [];
				$modelHead	= 
				[
					[
						["value"	=> "Cuenta"],
						["value"	=> "Cantidad"],
						["value"	=> "Documento"],
						["value"	=> "Fecha"]
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
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead"	=> $modelHead,
			])
			@endcomponent
			@php
				foreach($payments as $pay)
				{
					$totalPagado += $pay->amount;
				}
				$varRes			= '';
				$varResLabel	= '$ 0.00';
				foreach($request->expenses as $expense)
				{
					if($expense->reembolso > 0)
					{
						$varRes 		= number_format($expense->reembolso-$totalPagado,2);
						$varResLabel	= '$ '.number_format($expense->reembolso-$totalPagado,2);
					}
					else if($expense->reintegro > 0)
					{
						$varRes 		= number_format($expense->reintegro-$totalPagado ,2);
						$varResLabel 	= '$ '.number_format($expense->reintegro-$totalPagado ,2);
					}
				}
				$modelTable =
				[
					[
						"label" => "Total pagado:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> "$ ".number_format($totalPagado,2),
								"classEx" 	=> "my-2"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" value=\"".number_format($totalPagado,2)."\""
							]
						]
					],
					[
						"label" => "Resta por pagar:", "inputsEx" =>
						[
							[
								"kind" 		=> "components.labels.label",
								"label" 	=> $varResLabel,
								"classEx" 	=> "my-2"
							],
							[
								"kind"			=> "components.inputs.input-text",
								"attributeEx" 	=> "type=\"hidden\" value=\"".$varRes."\""
							]
						]
					]
				];
			@endphp
			@component('components.templates.outputs.form-details', [ "modelTable" => $modelTable]) @endcomponent
		</div>
	@endif
</div>