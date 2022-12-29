@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		switch ($request->nominasReal->first()->idCatTypePayroll) {
			case '001':
				$perioricity		=	isset($request->nominasReal->first()->idCatPeriodicity) ? App\CatPeriodicity::find($request->nominasReal->first()->idCatPeriodicity)->description : "---";
				$dateRange			=	Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->from_date)->format('d-m-Y')." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->to_date)->format('d-m-Y');
				break;
			case '002':
				break;
			case '003':
				break;
			case '004':
				break;
			case '005':
				break;
			case '006':
				break;
		}
		$modelTable	=
		[
			["label:",			$request->folio],
			["Título y fecha:",	$request->nominasReal->first()->title." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->datetitle)->format('d-m-Y')],
			["Categoría:",		$request->idDepartment == 11 ? 'Obra' : 'Administrativa'],
			["Tipo:",			$request->taxPayment == 1 ? 'Fiscal' : 'No fiscal'],
			["Periodicidad:",	$perioricity],
			["Rango de fecha:",	$dateRange !="" ? $dateRange : "---"],
			["Solicitante:",	$request->requestUser->name." - ".$request->requestUser->last_name." - ".$request->requestUser->scnd_last_name],
			["Elaborado por:",	$request->elaborateUser->name." - ".$request->elaborateUser->last_name." - ".$request->elaborateUser->scnd_last_name],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable])
		@slot('title')
			Detalles de la Solicitud de Nómina de {{ App\CatTypePayroll::find($request->nominasReal->first()->idCatTypePayroll)->description }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		Lista de Empleados
	@endcomponent
	@php
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$modelHead		=	["Nombre del Empleado", "Importe", "Acción"];
		$flagAlimony	=	false;
		foreach ($request->nominasReal->first()->nominaEmployee->where('visible',1)->where('payment',0) as $n)
		{
			$componentExt	=	[];
			if ($request->taxPayment == 1)
			{
				switch ($request->nominasReal->first()->idCatTypePayroll)
				{
					case '001':
						$totalPaymentEmployee = $n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$componentExt	=
						[
							["label"			=>	"$".number_format($n->salary->first()->netIncome-$totalPaymentEmployee,2) ],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->salary->first()->netIncome-$totalPaymentEmployee,2)."\"",
								"classEx"		=>	"netIncome"
							]
						];
						if ($n->salary->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '002':
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$componentExt			=
						[
							["label"			=>	"$".number_format($n->bonus->first()->netIncome-$totalPaymentEmployee,2) ],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->bonus->first()->netIncome-$totalPaymentEmployee,2)."\"",
								"classEx"		=>	"netIncome"
							]
						];
						if ($n->bonus->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '003':
					case '004':
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$componentExt			=
						[
							["label"			=>	"$".number_format($n->liquidation->first()->netIncome-$totalPaymentEmployee,2) ],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->liquidation->first()->netIncome-$totalPaymentEmployee,2)."\"",
								"classEx"		=>	"netIncome"
							]
						];
						if ($n->liquidation->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '005':
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$componentExt			=
						[
							["label"			=>	"$".number_format($n->vacationPremium->first()->netIncome-$totalPaymentEmployee,2) ],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->vacationPremium->first()->netIncome-$totalPaymentEmployee,2)."\"",
								"classEx"		=>	"netIncome"
							]
						];
						if ($n->vacationPremium->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '006':
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$componentExt			=
						[
							["label"			=>	"$".number_format($n->profitSharing->first()->netIncome-$totalPaymentEmployee,2)],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->profitSharing->first()->netIncome-$totalPaymentEmployee,2)."\"",
								"classEx"		=>	"netIncome"
							]
						];
						if ($n->profitSharing->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
				}
			}
			else
			{
				$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
				$componentExt			=
				[
					["label"			=>	"$".number_format($n->nominasEmployeeNF->first()->amount-$totalPaymentEmployee,2) ],
					[
						"kind"			=>	"components.inputs.input-text",
						"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->nominasEmployeeNF->first()->amount-$totalPaymentEmployee,2)."\"",
						"classEx"		=>	"netIncome"
					]
				];
			}
			$body	=
			[
				[
					"content"	=>
					[
						["label"			=>	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name],
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"idnominaEmployee_request[]\" type=\"hidden\" value=\"".$n->idnominaEmployee."\"",
							"classEx"		=>	"idnominaEmployee"
						]
					]
				],
				[
					"content"	=>	$componentExt
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"warning",
							"attribueEx"	=>	"title=\"Agregar pago\" type=\"button\"",
							"label"			=>	"<span class='icon-plus'></span>",
							"classEx"		=>	"add-employee"
						]
					]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('attributeEx')
			id=table
		@endslot
		@slot('attributeExBody')
			id="body-payroll"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
	@endcomponent
	@if($flagAlimony == true)
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Lista de Beneficiarios de Pensión Alimenticia"]) @endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Nombre del Empleado"],
					["value"	=>	"Beneficiario"],
					["value"	=>	"Importe"],
					["value"	=>	"Acción"]
				]
			];
			$flagAlimony = false;
			$componentsBeneficiary	=	[];
			$componentsSalary		=	[];
			foreach($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
			{
				if ($request->taxPayment == 1)
				{
					$totalPaymentBeneficiary = $n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
					switch ($request->nominasReal->first()->idCatTypePayroll)
					{
						case '001':
							if ($n->salary->first()->alimony > 0)
							{
								if ($n->salary->first()->idAccountBeneficiary != '')
								{
									$componentsBeneficiary	=
									[
										["label"			=>	App\EmployeeAccount::find($n->salary->first()->idAccountBeneficiary)->beneficiary],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".App\EmployeeAccount::find($n->salary->first()->idAccountBeneficiary)->beneficiary."\"",
											"classEx"		=>	"beneficiary-label"
										]
									];
								}
								$componentsSalary	=
								[
									["label"			=>	"$ ".number_format($n->salary->first()->alimony-$totalPaymentBeneficiary,2)],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->salary->first()->alimony-$totalPaymentBeneficiary,2)."\"",
										"classEx"		=>	"netIncome"
									]
								];
							}
							else
							{
								$componentsBeneficiary	=
								[
									["label"	=>	"---"],
								];
								$componentsSalary	=
								[
									["label"	=>	"$0.00"],
								];
							}
							break;
						case '002':
							if ($n->bonus->first()->alimony > 0)
							{
								if ($n->bonus->first()->idAccountBeneficiary != '')
								{
									$componentsBeneficiary	=
									[
										[
											["label"			=>	App\EmployeeAccount::find($n->bonus->first()->idAccountBeneficiary)->beneficiary],
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx"	=>	"type=\"hidden\" value=\"".App\EmployeeAccount::find($n->bonus->first()->idAccountBeneficiary)->beneficiary."\"",
												"classEx"		=>	"beneficiary-label"
											]
										]
									];
								}
								$componentsSalary	=
								[
									[
										["label"			=>	"$ ".number_format($n->bonus->first()->alimony-$totalPaymentBeneficiary,2)],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->bonus->first()->alimony-$totalPaymentBeneficiary,2)."\"",
											"classEx"		=>	"netIncome"
										]
									]
								];
							}
							else
							{
								$componentsBeneficiary	=
								[
									["label"	=>	"---"],
								];
								$componentsSalary	=
								[
									["label"	=>	"$0.00"],
								];
							}
							break;
						case '003':
						case '004':
							if ($n->liquidation->first()->alimony > 0)
							{
								if ($n->liquidation->first()->idAccountBeneficiary != '')
								{
									$componentsBeneficiary	=
									[
										[
											["label"			=>	App\EmployeeAccount::find($n->liquidation->first()->idAccountBeneficiary)->beneficiary],
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx"	=>	"type=\"hidden\" value=\"".App\EmployeeAccount::find($n->liquidation->first()->idAccountBeneficiary)->beneficiary."\"",
												"classEx"		=>	"beneficiary-label"
											]
										]
									];
								}
								$componentsSalary	=
								[
									[
										["label"			=>	"$ ".number_format($n->liquidation->first()->alimony-$totalPaymentBeneficiary,2)],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->liquidation->first()->alimony-$totalPaymentBeneficiary,2)."\"",
											"classEx"		=>	"netIncome"
										]
									]
								];
							}
							else
							{
								$componentsBeneficiary	=
								[
									["label"	=>	"---"],
								];
								$componentsSalary	=
								[
									["label"	=>	"$0.00"],
								];
							}
							break;
						case '005':
							if ($n->vacationPremium->first()->alimony > 0)
							{
								if ($n->vacationPremium->first()->idAccountBeneficiary != '')
								{
									$componentsBeneficiary	=
									[
										[
											["label"			=>	App\EmployeeAccount::find($n->vacationPremium->first()->idAccountBeneficiary)->beneficiary],
											[
												"kind"			=>	"components.inputs.input-text",
												"attributeEx"	=>	"type=\"hidden\" value=\"".App\EmployeeAccount::find($n->vacationPremium->first()->idAccountBeneficiary)->beneficiary."\"",
												"classEx"		=>	"beneficiary-label"
											]
										]
									];
								}
								else
								{
									$componentsBeneficiary	=
									[
										["label"	=>	"---"],
									];
								}
								$componentsSalary	=
								[
									[
										["label"			=>	"$ ".number_format($n->vacationPremium->first()->alimony-$totalPaymentBeneficiary,2)],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->vacationPremium->first()->alimony-$totalPaymentBeneficiary,2)."\"",
											"classEx"		=>	"netIncome"
										]
									]
								];
							}
							else
							{
								$componentsBeneficiary	=
								[
									["label"	=>	"---"],
								];
								$componentsSalary	=
								[
									["label"	=>	"$0.00"],
								];
							}
							break;
						case '006':
							if ($n->profitSharing->first()->alimony > 0)
							{
								if ($n->profitSharing->first()->idAccountBeneficiary != '')
								{
									$componentsBeneficiary	=
									[
										["label"			=>	App\EmployeeAccount::find($n->profitSharing->first()->idAccountBeneficiary)->beneficiary],
										[
											"kind"			=>	"components.inputs.input-text",
											"attributeEx"	=>	"type=\"hidden\" value=\"".App\EmployeeAccount::find($n->profitSharing->first()->idAccountBeneficiary)->beneficiary."\"",
											"classEx"		=>	"beneficiary-label"
										]
									];
								}
								else
								{
									$componentsBeneficiary	=
									[
										["label"	=>	"---"]
									];
								}
								$componentsSalary	=
								[
									["label"			=>	"$ ".number_format($n->profitSharing->first()->alimony-$totalPaymentBeneficiary,2)],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" value=\"".round($n->profitSharing->first()->alimony-$totalPaymentBeneficiary,2)."\"",
										"classEx"		=>	"netIncome"
									]
								];
							}
							else
							{
								$componentsBeneficiary	=
								[
									["label"	=>	"---"],
								];
								$componentsSalary	=
								[
									["label"	=>	"$0.00"],
								];
							}
							break;
					}
					$body	=
					[
						[
							"content"	=>
							[
								["label"			=>	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" name=\"idnominaEmployee_request[]\" value=\"".$n->idnominaEmployee."\"",
									"classEx"		=>	"idnominaEmployee_beneficiary"
								]
							]
						],
						[
							"content"	=>	$componentsBeneficiary
						],
						[
							"content"	=>	$componentsSalary
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.buttons.button",
								"label"			=>	"<span class='icon-plus'></span>",
								"variant"		=>	"warning",
								"attributeEx"	=>	"title=\"Agregar pago\" type=\"button\"",
								"classEx"		=>	"add-beneficiary"
							],
						],
					];
				}
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->nominasReal->first()->amount;
		$totalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
		$subtotalPagado	=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal_real') : 0;
		$ivaPagado		=	$request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva_real') : 0;
	@endphp
	@if(count($payments) > 0)
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Empleado"],
					["value"	=>	"Empresa"],
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"]
				]
			];
			foreach ($payments as $pay)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->nominaEmployee->employee->first()->name.' '.$pay->nominaEmployee->employee->first()->last_name.' '.$pay->nominaEmployee->employee->first()->scnd_last_name]
					],
					[
						"content"	=>	["label"	=>	$pay->enterprise->name]
					],
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"]
					],
					[
						"content"	=>	["label"	=>	"$".number_format($pay->amount,2)]
					],
				];
				if (count($pay->documentsPayments)>0)
				{
					$componentExButtons = [];
					foreach ($pay->documentsPayments as $doc)
					{
						$componentExButtons[] =	
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"label"			=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\""." title=\"".$doc->path."\""
						];
						
					}
					$body[]	= ["content"	=>$componentExButtons];
				}
				else
				{
					$body[]	=
					[
						"content"	=>	["label"	=>	"Sin documentos"]
					];
				}
				$body[]	=
				[
					"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" =>	$modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=> "$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"label"	=> "$ ".number_format(($total)-$totalPagado,2)]]]
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
	@endif
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PAGO"]) @endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.payment.update',$payment->idpayment)."\"", "methodEx" => "PUT", "files" => true])
		@php
			$modelHead	=	["DATOS INGRESADOS"];
			$modelBody	=	[];
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Tipo de pago: @endcomponent
				@if($payment->type == 1)
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="type_payment" value="1"
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" value="Pago normal a empleado" readonly
						@endslot
					@endcomponent
				@else
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden" name="type_payment" value="2"
						@endslot
					@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" value="Pago de pensión alimenticia" readonly
						@endslot
					@endcomponent
				@endif
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empleado: @endcomponent
				@php
					$options	=	collect();
					foreach ($request->nominasReal->first()->nominaEmployee as $n)
					{
						if ($n->idnominaEmployee == $payment->idnominaEmployee)
						{
							$options	=	$options->concat([["value" => $n->idnominaEmployee, "description" => $n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name, "selected" => "selected"]]);
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('classEx')
						js-employees custom-select
					@endslot
					@slot('attributeEx')
						name="idnominaEmployee" data-validation="required" style="position: relative;" multiple="multiple"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 tr-beneficiary @if ($payment->type == 1) hidden @endif">
				@component('components.labels.label') Beneficiario @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="beneficiary_pay" value="{{ $payment->beneficiary }}"
					@endslot
					@slot('classEx')
						beneficiary-pay
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprises	=	[];
					foreach (App\Enterprise::orderBy('name','asc')->get() as $enterprise)
					{
						if ($payment->idEnterprise == $enterprise->id)
						{
							$optionsEnterprises[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsEnterprises[]	=	["value"	=>	$enterprise->id,	"description"	=>	$enterprise->name];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterprises])
					@slot('classEx')
						custom-select js-enterprises
					@endslot
					@slot('attributeEx')
						name="enterprise_id" style="position: relative;" data-validation="required" multiple="multiple"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación del gasto: @endcomponent
				@php
					$options		=	collect();
					$accountData	=	App\Account::find($payment->account);
					if ($payment->account!="")
					{
						$options	=	$options->concat([["value"	=>	$accountData->idAccAcc,	"description"	=>	$accountData->account." - ".$accountData->description." (".$accountData->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('classEx')
						custom-select js-accounts
					@endslot
					@slot('attributeEx')
						name="account" style="position: relative;" data-validation="required" multiple="multiple"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="idfolio" value="{{ $request->folio }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" name="idkind" value="{{ $request->kind }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Subtotal: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el subtotal" type="text" name="subtotalRes" data-validation="required" value="{{ $payment->subtotal_real }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Importe: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el importe" type="hidden" name="amountRes" data-validation="required" value="{{ ($total)-$totalPagado }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el importe" type="text" name="amount" data-validation="required" value="{{ $payment->amount_real }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') IVA: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el iva" type="text" name="ivaRes" data-validation="required" value="{{ $payment->iva_real }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha de pago: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese la fecha" type="text" name="paymentDate" readonly="readonly" data-validation="required" value="@if(isset($payment->paymentDate)) {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$payment->paymentDate)->format('d-m-Y') }} @endif"
					@endslot
					@slot('classEx')
						datepicker
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tasa de cambio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate" placeholder="Ingrese la tasa de cambio" value="{{ $payment->exchange_rate }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Descripción de tasa de cambio: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate_description" placeholder="Ingrese la descripción" value="{{ $payment->exchange_rate_description }}"
					@endslot
				@endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label') Comentarios: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						type="text" name="commentaries" placeholder="Ingrese el comentario"
					@endslot
					{{ $payment->commentaries }}
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "COMPROBANTES"]) @endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=	["Documento", "Acciones"];
			if (App\DocumentsPayments::where('idpayment',$payment->idpayment)->count()>0)
			{
				foreach (App\DocumentsPayments::where('idpayment',$payment->idpayment)->get() as $doc)
				{
					$body	=
					[
						"classEx"	=>	"removeDoc",
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"secondary",
									"buttonElement"	=>	"a",
									"label"			=>	"Archivo",
									"attributeEx"	=>	"target=\"_blank\" href=\"".asset('/docs/payments/'.$doc->path)."\""." title=\"Ver documento\""
								],
								[
									"kind"			=>	"components.inputs.input-text",
									"attributeEx"	=>	"type=\"hidden\" value=\"".$doc->iddocumentsPayments."\"",
									"classEx"		=>	"iddocumentsPayments"
								]
							]
						],
						[
							"content"	=>
							[
								[
									"kind"			=>	"components.buttons.button",
									"variant"		=>	"red",
									"label"			=>	"<span class='icon-x delete-span'></span>",
									"classEx"		=>	"delete-item exist-doc",
									"attributeEx"	=>	"id=\"delete-doc\""
								]
							]
						],
					];
					$modelBody[]	=	$body;
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hidden" id="documents"></div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						type="button" name="addDoc" id="addDoc"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar documento</span>
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', ["variant"	=>	"primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ACTUALIZAR PAGO\"", "label" => "ACTUALIZAR PAGO", "classEx" => "enviar"]) @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>	
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{asset('js/jquery.mask.js')}}"></script>
	<script>
		$('input[name="amountRes"]').attr('value',$('#restaTotal').val());
		$(document).ready(function()
		{
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
			generalSelect({'selector': '.js-employees', 'model': 48, 'id': {{$request->folio}}});
			@php
			$selects = collect([
				[
					"identificator"			 => ".js-enterprises",	
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				],
			]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'dd-mm-yy',
				});
			});
			$('input[name="subtotalRes"],input[name="ivaRes"]').numeric({ negative: false, altDecimal: ".", decimalPlaces: 2 });
			$('input[name="amount"]').numeric({ altDecimal: ".", decimalPlaces: 2 });
			$.validate(
			{
				form: '#container-alta',
				modules: 'security',
				onError   : function()
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function()
				{
					pathFlag = true;
					$('.path').each(function()
					{
						path = $(this).val();
						
						if(path == "")
						{
							pathFlag = false;
						}
					});
					
					amount     = $('input[name="amount"]').val();

					if(!pathFlag) 
					{
						swal('','Por favor agregue los documentos faltantes.','error');
						return false;
					}
					else if(amount <= 0 || amount == '' || amount == 'NaN' || amount == null)
					{
						$('input[name="amount"]').removeClass('valid').addClass('error');	
						swal('','El importe no puede ser cero ó negativo, por favor verifique los datos.','error');
						return false;
					}
					else
					{
						swal({
							icon               : '{{ asset(getenv('LOADING_IMG')) }}',
							button             : false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						return true;
					}
				}
			});
		});
		$(document).on('click','.enviar',function (e)
		{
			e.preventDefault();
			subtotal = $('input[name="subtotalRes"],input[name="amount"]').removeClass('error');
			form = $('#container-alta');
			amount   = $('input[name="amount"]').val();
			if(amount == '' || amount == 'NaN' || amount == null)
			{
				$('input[name="amount"]').val('0');
			}
			docFlag = true;
			if($('.path').length == 0 && $(".old_path").length<=0 && $(".removeDoc").length <= 0)
			{
				docFlag = false;
			}

			if (!docFlag) 
			{
				
				swal({
					title: "¿Desea actualizar el pago sin comprobante?",
					icon: "warning",
					buttons: ["Cancelar","OK"],
				})
				.then((isConfirm) =>
				{
					if(isConfirm)
					{
						form.submit();
					}
				});
			}
			else
			{
				form.submit();
			}
		})
		.on('change','.js-enterprises',function()
		{
			$('.js-accounts').empty();
		})
		.on('click','#addDoc',function()
		{
			hasHidden	=	$('#documents').hasClass('hidden');
			if (hasHidden)
			{
				$('#documents').removeClass('hidden');
			}
			@php
				$uploadDoc 	=	html_entity_decode((String)view("components.documents.upload-files", [
					"classExInput"			=>	"inputDoc pathActioner",
					"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
					"attributeExRealPath"	=>	"name=\"realPath[]\"",
					"classExRealPath"		=>	"path",
					"classExDelete"			=>	"delete-doc"
				]));
			@endphp
			uploadDoc	=	'{!!preg_replace("/(\r)*(\n)*/", "", $uploadDoc)!!}';
			$('#documents').append(uploadDoc);
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parents('.docs-p').find('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("payments.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('.docs-p').remove();
			if($('.docs-p').length<1)
			{
				$('#documents').addClass('hidden');
			}
		})
		.on('click','.exist-doc',function()
		{
			docR = $(this).parents('.removeDoc').find('.iddocumentsPayments').val();
			inputDelete = $('<input type="hidden" name="deleteDoc[]">').val(docR);
			$('#docs-remove').append(inputDelete);
			$(this).parents('.removeDoc').remove();
		})
		.on('change','.inputDoc.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("payments.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				})
			}
		})
		.on('change','[name="idnominaEmployee"]',function()
		{
			flag = false;
			idnominaEmployee = $(this).val();
			if($('[name="type_payment"]').val() == 2)
			{
				$('.idnominaEmployee_beneficiary').each(function()
				{
					if($(this).val() == idnominaEmployee)
					{
						beneficiary	= $(this).parent('td').parent('tr').find('.beneficiary-label').val();
						return flag = true;
					}
				});
				if (flag) 
				{
					$('input[name="beneficiary_pay"]').val(beneficiary);
				}
				else
				{
					swal('','Este empleado no paga pensión alimenticia','error');
					$(this).val(null).trigger('change');
				}
			}
		})
		.on('change','[name="subtotalRes"], [name="ivaRes"]',function()
		{
			subtotalRes		= parseFloat($('[name="subtotalRes"]').val());
			ivaRes			= parseFloat($('[name="ivaRes"]').val());
			$('[name="amount"]').val((subtotalRes+ivaRes).toFixed(2));
		})
		.on('change','[name="amount"]',function()
		{
			var amount = 0;
			if((parseFloat($('[name="subtotalRes"]').val())) != 0 && (parseFloat($('[name="subtotalRes"]').val())) != 0)
			{
				subtotalRes		= parseFloat($('[name="subtotalRes"]').val());
				ivaRes			= parseFloat($('[name="ivaRes"]').val());
				amount = ivaRes + subtotalRes
			}
			else if((parseFloat($('[name="subtotalRes"]').val())) != 0)
			{
				subtotalRes		= parseFloat($('[name="subtotalRes"]').val());
				amount = amount + subtotalRes
			} 
			else if((parseFloat($('[name="ivaRes"]').val())) != 0)
			{
				ivaRes			= parseFloat($('[name="ivaRes"]').val());
				amount = amount + ivaRes
			}

			if(amount != 0 && $('[name="amount"]').val() != amount){
				$('[name="amount"]').val(amount.toFixed(2))
			}
		})
		.on("focusout","input[name='subtotalRes'], input[name='ivaRes'], .taxRes, .retentionRes, input[name='amount']",function()
		{
			valueThis = $.isNumeric($(this).val());
			if(valueThis == false)
			{
				$(this).val(null);
			}
		});
	</script>
@append
