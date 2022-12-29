@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$period	=	"";
		$range	=	"";
		switch($request->nominasReal->first()->idCatTypePayroll)
		{
			case('001'):
				$period	=	App\CatPeriodicity::find($request->nominasReal->first()->idCatPeriodicity)->description;
				$range	=	Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->from_date)->format('d-m-Y')." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->to_date)->format('d-m-Y');
			break;
			case('002'):
			break;
			case('003'):
			break;
			case('004'):
			break;
			case('005'):
			break;
			case('006'):
			break;
		}
		$modelTable	=
		[
			['Folio:',			$request->folio],
			['Título y fecha:',	htmlentities($request->nominasReal->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->nominasReal->first()->datetitle)->format('d-m-Y')],
			['Categoría:',		$request->idDepartment == 11 ? 'Obra' : 'Administrativa'." - ".$request->nominasReal->first()->typeNomina()],
			['Tipo:',			$request->nominasReal->first()->typePayroll->description],
			["Periodicidad:",	$period!="" ? $period : "---"],
			["Rango de fecha:",	$range!="" ? $range : "---"],
			['Solicitante:',	$request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			['Elaborado por:',	$request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
		]
	@endphp
	@component('components.templates.outputs.table-detail', ['modelTable'	=>	$modelTable])
		@slot('title')
			Detalles de la Solicitud de Nómina de {{ App\CatTypePayroll::find($request->nominasReal->first()->idCatTypePayroll)->description }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Lista de Empleados"]) @endcomponent
	@if($request->nominasReal->first()->type_nomina != 3)
		@if($request->nominasReal->first()->type_nomina == 2)
			<div class="flex justify-end w-full text-right mt-4">
				@component('components.buttons.button',['variant' => 'success'])
					@slot('attributeEx')
						formaction="{{ route('nomina.review-nf.export',$request->folio) }}"
					@endslot
					@slot('slot')
						Exportar datos de pago <span class="icon-file-excel"></span>
					@endslot
				@endcomponent
			</div>
		@endif
		@if($request->nominasReal->first()->type_nomina == 1)
			@switch($request->nominasReal->first()->idCatTypePayroll)
				@case('001')
					@if($request->status != 2)
					<div class="text-right">
						@component('components.labels.label')
							@component('components.buttons.button',['variant' => 'success'])
								@slot('attributeEx')
									formaction="{{ route('nomina.export.salary',$request->folio) }}"
								@endslot
								@slot('slot')
									<span>Exportar a Excel</span><span class="icon-file-excel"></span>
								@endslot
							@endcomponent
						@endcomponent
					</div>
					@endif
				@break
				@case('002')
					@if($request->status != 2)
						<div class="text-right">
							@component('components.labels.label')
								@component('components.buttons.button',['variant' => 'success'])
									@slot('attributeEx')
										formaction="{{ route('nomina.export.bonus',$request->folio) }}"
									@endslot
									@slot('slot')
										Exportar datos de pago <span class="icon-file-excel"></span>
									@endslot
								@endcomponent
							@endcomponent
						</div>
					@endif
				@break
				@case('003')
					@if($request->status != 2)
						<div class="text-right">
							@component('components.labels.label')
								@component('components.buttons.button',['variant' => 'success'])
									@slot('attributeEx')
										formaction="{{ route('nomina.export.settlement',$request->folio) }}"
									@endslot
									@slot('slot')
										Exportar datos de pago <span class="icon-file-excel"></span>
									@endslot
								@endcomponent
							@endcomponent
						</div>
					@endif
				@break
				@case('004')
					@if($request->status != 2)
						<div class="text-right">
							@component('components.labels.label')
								@component('components.buttons.button',['variant' => 'success'])
									@slot('attributeEx')
										formaction="{{ route('nomina.export.liquidation',$request->folio) }}"
									@endslot
									@slot('slot')
										Exportar datos de pago <span class="icon-file-excel"></span>
									@endslot
								@endcomponent
							@endcomponent
						</div>
					@endif
				@break
				@case('005')
					@if($request->status != 2)
						<div class="text-right">
							@component('components.labels.label')
								@component('components.buttons.button',['variant' => 'success'])
									@slot('attributeEx')
										formaction="{{ route('nomina.export.vacationpremium',$request->folio) }}"
									@endslot
									@slot('slot')
										Exportar datos de pago <span class="icon-file-excel"></span>
									@endslot
								@endcomponent
							@endcomponent
						</div>
					@endif
				@break
				@case('006')
					@if($request->status != 2)
						<div class="text-right">
							@component('components.labels.label')
								@component('components.buttons.button',['variant' => 'success'])
									@slot('attributeEx')
										formaction="{{ route('nomina.export.profitsharing',$request->folio) }}"
									@endslot
									@slot('slot')
										Exportar datos de pago <span class="icon-file-excel"></span>
									@endslot
								@endcomponent
							@endcomponent
						</div>
					@endif
				@break
			@endswitch
		@endif
	@else
		@component('components.labels.label', ["label" => "* Verifique que el monto sea correcto para cada empleado", "classEx" => "font-bold mt-2 mb-4"]) @endcomponent
		@if($request->status != 2)
			@if($request->nominasReal->first()->type_nomina == 3)
			<div class="text-right">
				@component('components.labels.label')
					@component('components.buttons.button',['variant' => 'success'])
						@slot('attributeEx')
							formaction="{{ route('nomina.nom35.export',$request->folio) }}"
						@endslot
						@slot('slot')
							Exportar datos de pago <span class="icon-file-excel"></>
						@endslot
					@endcomponent
				@endcomponent
			</div>
			@endif
		@endif
	@endif
	@php
		$body 			=	[];
		$modelBody		=	[];
		$modelHead		=	["Nombre del Empleado", "Importe", "Acción"];
		$flagAlimony	=	false;
		foreach ($request->nominasReal->first()->nominaEmployee->where('visible',1)->where('payment',0) as $n)
		{
			$quantity		=	0;
			$importRounded	=	0;
			if ($request->taxPayment == 1)
			{
				switch ($request->nominasReal->first()->idCatTypePayroll)
				{
					case '001': // salary
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$quantity				=	number_format($n->salary->first()->netIncome-$totalPaymentEmployee,2);
						$importRounded			=	round($n->salary->first()->netIncome-$totalPaymentEmployee,2);
						if ($n->salary->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '002': // bonus
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$quantity				=	number_format($n->bonus->first()->netIncome-$totalPaymentEmployee,2);
						$importRounded 			=	round($n->bonus->first()->netIncome-$totalPaymentEmployee,2);
						if ($n->bonus->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '003': // liquidation
					case '004':
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$quantity				=	number_format($n->liquidation->first()->netIncome-$totalPaymentEmployee,2);
						$importRounded 			=	round($n->liquidation->first()->netIncome-$totalPaymentEmployee,2);
						if($n->liquidation->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '005': //vacation permium
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$quantity				=	number_format($n->vacationPremium->first()->netIncome-$totalPaymentEmployee,2);
						$importRounded			=	round($n->vacationPremium->first()->netIncome-$totalPaymentEmployee,2);
						if($n->vacationPremium->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
					case '006': // profit sharing
						$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
						$quantituy				=	number_format($n->profitSharing->first()->netIncome-$totalPaymentEmployee,2);
						$importRounded			=	round($n->profitSharing->first()->netIncome-$totalPaymentEmployee,2);
						if($n->profitSharing->first()->alimony>0)
						{
							$flagAlimony	=	true;
						}
						break;
				}
			}
			else
			{
				$totalPaymentEmployee	=	$n->payments()->exists() ? $n->payments->where('type',1)->sum('amount') : 0;
				$quantity				=	number_format($n->nominasEmployeeNF->first()->amount-$totalPaymentEmployee,2);
				$importRounded			=	round($n->nominasEmployeeNF->first()->amount-$totalPaymentEmployee,2);
			}
			$body = 
			[
				[
					"content"	=>
					[
						["label" 		=>	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name],
						[
							"kind"			=>	"components.inputs.input-text",
							"classEx"		=>	"idnominaEmployee",
							"attributeEx"	=>	"name=\"idnominaEmployee_request[]\" type=\"hidden\" value=\"".$n->idnominaEmployee."\""
						]
					]
				],
				[
					"content"	=>
					[
						["label"			=>	"$".$quantity],
						[
							"kind"			=>	"components.inputs.input-text",
							"classEx"		=>	"netIncome",
							"attributeEx"	=>	"type=\"hidden\" value=\"".$importRounded."\""
						]
					]
				],
				[
					"content"	=>
					[	
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"warning",
							"label"			=>	"<span class='icon-plus'></span>",
							"classEx"		=>	"add-employee",
							"attributeEx"	=>	"title=\"Agregar pago\" type=\"button\""
						]
					]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ['modelHead' => $modelHead, 'modelBody' => $modelBody])@endcomponent
	@if($flagAlimony == true)
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "Lista de Beneficiarios de Pensión Alimenticia <span class=\"help-btn\" id=\"help-btn-add-employee\"></span>"]) @endcomponent
		@php
			$modelHead				=	[];
			$body					=	[];
			$modelBody				=	[];
			$modelHead = 
			[
				[
					["value"	=>	"Nombre del Empleado"],
					["value"	=>	"Beneficiario"],
					["value"	=>	"Importe"],
					["value"	=>	"Acción"]
				]
			];
			$flagAlimony	=	false;
			foreach ($request->nominasReal->first()->nominaEmployee->where('visible',1) as $n)
			{
				$nameEmployee 			=	"";
				$idemployee				=	"";
				$benicifiaryName 		=	"";
				$paymentBenefiary 		=	"";
				$paymentBenefiaryRound 	=	"";
				if ($request->taxPayment == 1) 
				{
					switch ($request->nominasReal->first()->idCatTypePayroll)
					{
						case '001':
							if ($n->salary->first()->alimony > 0)
							{
								$idemployee		= 	$n->idnominaEmployee;
								$nameEmployee	=	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name;
								if ($n->salary->first()->idAccountBeneficiary != '') 
								{
									$benicifiaryName	=	App\EmployeeAccount::find($n->salary->first()->idAccountBeneficiary)->beneficiary;
								}
								$totalPaymentBeneficiary	=	$n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
								$paymentBenefiary			=	number_format($n->salary->first()->alimony-$totalPaymentBeneficiary,2);
								$paymentBenefiaryRound		=	round($n->salary->first()->alimony-$totalPaymentBeneficiary,2);
							}
							break;
						case '002':
							if ($n->bonus->first()->alimony > 0)
							{
								$idemployee		=	$n->idnominaEmployee;
								$nameEmployee	=	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name;
								if ($n->bonus->first()->idAccountBeneficiary != '') 
								{
									$benicifiaryName	=	App\EmployeeAccount::find($n->bonus->first()->idAccountBeneficiary)->beneficiary;
								}
								$totalPaymentBeneficiary	=	$n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
								$paymentBenefiary			=	number_format($n->bonus->first()->alimony-$totalPaymentBeneficiary,2);
								$paymentBenefiaryRound		=	round($n->bonus->first()->alimony-$totalPaymentBeneficiary,2);
							}
							break;
						case '003':
						case '004':
							if ($n->liquidation->first()->alimony > 0)
							{
								$idemployee 	= 	$n->idnominaEmployee;
								$nameEmployee 	=	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name;

								if ($n->liquidation->first()->idAccountBeneficiary != '') 
								{
									$benicifiaryName = App\EmployeeAccount::find($n->liquidation->first()->idAccountBeneficiary)->beneficiary;
								}
								$totalPaymentBeneficiary	=	$n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
								$paymentBenefiary			=	number_format($n->liquidation->first()->alimony-$totalPaymentBeneficiary,2);
								$paymentBenefiaryRound		=	round($n->liquidation->first()->alimony-$totalPaymentBeneficiary,2);
							}
							break;
						case '005':
							if ($n->vacationPremium->first()->alimony > 0)
							{
								$idemployee		=	$n->idnominaEmployee;
								$nameEmployee	=	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name;
								if ($n->vacationPremium->first()->idAccountBeneficiary != '') 
								{
									$benicifiaryName	=	App\EmployeeAccount::find($n->vacationPremium->first()->idAccountBeneficiary)->beneficiary;
								}
								else 
								{
									$benicifiaryName	=	'---';
								}
								$totalPaymentBeneficiary	=	$n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
								$paymentBenefiary			=	number_format($n->vacationPremium->first()->alimony-$totalPaymentBeneficiary,2);
								$paymentBenefiaryRound		=	round($n->vacationPremium->first()->alimony-$totalPaymentBeneficiary,2);
							}
							break;
						case '006':
							if ($n->profitSharing->first()->alimony > 0) {
								$idemployee		=	$n->idnominaEmployee;
								$nameEmployee	=	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name;
								if ($n->profitSharing->first()->idAccountBeneficiary != '') 
								{
									$benicifiaryName = App\EmployeeAccount::find($n->profitSharing->first()->idAccountBeneficiary)->beneficiary;
								}
								else 
								{
									$benicifiaryName	=	'---';
								}
								$totalPaymentBeneficiary	=	$n->payments()->exists() ? $n->payments->where('type',2)->sum('amount') : 0;
								$paymentBenefiary			=	number_format($n->profitSharing->first()->alimony-$totalPaymentBeneficiary,2);
								$paymentBenefiaryRound		=	round($n->profitSharing->first()->alimony-$totalPaymentBeneficiary,2);
							}
							break;
					}
				}
				$body =
				[
					[
						"content"	=>
						[
							["label" => $nameEmployee!="" ? $nameEmployee : "---"],
							[
								"kind" 			=>	"components.inputs.input-text",
								"classEx" 		=>	"idnominaEmployee_beneficiary",
								"attributeEx" 	=>	"name=\"idnominaEmployee_request[]\" type=\"hidden\" value=\"".$idemployee."\""
							]
						]
					],
					[
						"content"	=>
						[
							["label" => $benicifiaryName!="" ? $benicifiaryName : "---"],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx"		=>	"beneficiary-label",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$benicifiaryName."\""
							]
						]
					],
					[
						"content"	=>
						[
							["label"		=>	$paymentBenefiary!="" ? "$".$paymentBenefiary : "---"],
							[
								"kind"			=>	"components.inputs.input-text",
								"classEx" 		=> 	"netIncome",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$paymentBenefiaryRound."\""
							]
						]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"label"			=>	"<span class='icon-plus'></span>",
								"classEx"		=>	"add-beneficiary",
								"attributeEx"	=>	"title=\"Agregar pago\" type=\"button\""
							]
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ['modelHead'	=>	$modelHead, 'modelBody'	=>	$modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->nominasReal->first()->amount;
		$iva			=	0;
		$subtotal		=	0;
		$totalPagado	=	$request->paymentsRequest()->exists() ? round($request->paymentsRequest->sum('amount_real'),2) : 0;
		$subtotalPagado	=	$request->paymentsRequest()->exists() ? round($request->paymentsRequest->sum('subtotal_real'),2) : 0;
		$ivaPagado		=	$request->paymentsRequest()->exists() ? round($request->paymentsRequest->sum('iva_real'),2) : 0;
	@endphp
	@if(count($payments) > 0)
		@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "HISTORIAL DE PAGOS"]) @endcomponent
		@php
			$modelHead		=	[];
			$body			=	[];
			$modelBody		=	[];
			$employee		=	"";
			$account		=	"";
			$quantity		=	"";
			$modelHead =
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
				$componentDocs	=	[];
				$employee		=	isset($pay->nominaEmployee->employee->first()->name) && $pay->nominaEmployee->employee->first()->name!="" ? $pay->nominaEmployee->employee->first()->name.' '.$pay->nominaEmployee->employee->first()->last_name.' '.$pay->nominaEmployee->employee->first()->scnd_last_name : "---";
				$account		=	isset($pay->accounts->account) && $pay->accounts->account!="" ? $pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")" : "---";
				if (count($pay->documentsPayments)>0) {
					foreach ($pay->documentsPayments as $doc)
					{
						$componentDocs	=
						[
							"kind"			=>	"components.buttons.button",
							"variant" 		=>	"dark-red",
							"label" 		=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx" 	=>	"type=\"button\" target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)." title=".$doc->path."\""
						];
					}
				}
				else
				{
					$componentDocs	=
					[
						"label"	=>	"Sin documento"
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$employee]
					],
					[
						"content"	=>	["label"	=>	isset($pay->enterprise->name) && $pay->enterprise->name!="" ? $pay->enterprise->name : "---"]
					],
					[
						"content"	=>	["label"	=>	$account]
					],
					[
						"content"	=>	["label"	=>	isset($pay->amount) && $pay->amount!="" ? '$'.number_format($pay->amount,2) : "$ 0.00"]
					],
					[
						"content"	=>	$componentDocs
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead"	=>	$modelHead, "modelBody"	=>	$modelBody, "classEx" => "mt-4"]) @endcomponent
		@php
			$model	=
			[
				["label"	=>	"Total pagado",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"totalPagado\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta",		"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"attributeEx"	=>	"name=\"resta\"",	"classEx"	=>	"h-10 py-2",	"label"	=>	"$ ".number_format(($total)-$totalPagado,2)]]]
			]
		@endphp
		@component('components.templates.outputs.form-details',['modelTable'	=>	$model])@endcomponent
	@endif
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PAGO"]) @endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.payment.store')."\"", "files" => true])
		@php
			$modelHead	=	["INGRESAR DATOS"];
			$modelBody	=	[];
		@endphp
		@component('components.tables.alwaysVisibleTable', ['modelHead'	=>	$modelHead, "modelBody"	=>	$modelBody])@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tipo de pago:"]) @endcomponent
				@php
					$optionKind[]	=	["value"	=>	"1",	"description"	=>	"Pago normal a empleado"];
					$optionKind[]	=	["value"	=>	"2",	"description"	=>	"Pago de pensión alimenticia"];
				@endphp
				@component('components.inputs.select', ['options' => $optionKind])
					@slot('classEx')
						js-kindPayment removeselect
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="type_payment"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Empleado:"]) @endcomponent
				@php
					foreach ($request->nominasReal->first()->nominaEmployee as $n)
					{
						$optionEmployees[]	=
						[
							"value"			=>	$n->idnominaEmployee,
							"description"	=>	$n->employee->first()->name.' '.$n->employee->first()->last_name.' '.$n->employee->first()->scnd_last_name
						];
					}
				@endphp
				@component('components.inputs.select')
					@slot('classEx')
						js-employees removeselect
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="idnominaEmployee" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Empresa:"]) @endcomponent
				@php
					foreach(App\Enterprise::orderBy('name','asc')->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						$optionEnterprice[]	=	['value' => $enterprise->id, 'description' => $enterprise->name];
					}
				@endphp
				@component('components.inputs.select', ['options'	=>	$optionEnterprice])
					@slot('classEx')
						js-enterprises removeselect
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="enterprise_id" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Clasificación del gasto:"]) @endcomponent
				@component('components.inputs.select')
					@slot('classEx')
						js-accounts
					@endslot
					@slot('attributeEx')
						multiple="multiple" name="account" data-validation="required"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						inline-flex
					@endslot
					@slot('attributeEx')
						type="hidden" name="idfolio" value="{{ $request->folio }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						inline_flex
					@endslot
					@slot('attributeEx')
						type="hidden" name="idkind" value="{{ $request->kind }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Subtotal:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el subtotal" type="text"  name="subtotalRes" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 hidden tr-beneficiary">
				@component('components.labels.label', ["label" => "Beneficiario:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						beneficiary-pay
					@endslot
					@slot('attributeEx')
						type="text" name="beneficiary_pay"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "IVA:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el iva" type="text" name="ivaRes" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Importe:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el importe" type="hidden" name="amountRes" data-validation="required" value="{{ round(($total)-$totalPagado,2) }}"
					@endslot
				@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder="Ingrese el importe" type="text" name="amount" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Fecha de pago:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('classEx')
						datepicker
					@endslot
					@slot('attributeEx')
						placeholder="Ingrese la fecha" type="text" name="paymentDate" readonly="readonly" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Tasa de cambio (Opcional):"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate" placeholder="Ingrese la tasa de cambio"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label', ["label" => "Descripción de tasa de cambio (Opcional):"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="exchange_rate_description" placeholder="Ingrese la descripción de tasa de cambio"
					@endslot
				@endcomponent
			</div>
			<div class="md:col-span-4 col-span-2">
				@component('components.labels.label', ["label" => "Comentarios (Opcional):"]) @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						type="text" name="commentaries" placeholder="Ingrese los comentarios"
					@endslot
				@endcomponent
			</div>
			@component('components.labels.label', ["label" => "Comprobante:"]) @endcomponent
			<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 hiden" id="documents">
			</div>
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
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ['variant' => 'primary', "classEx" => "mr-2 enviar", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR PAGO\"", "label" => "ENVIAR PAGO"]) @endcomponent
			@php
				$href	=	isset($option_id)? url(getUrlRedirect($option_id)): url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ['variant' => 'reset', "attributeEx" => "href=\"".$href."\"", "label" => "REGRESAR", "classEx" => "load-actioner", "buttonElement" => "a"]) @endcomponent
		</div>
	@endcomponent
	<div id="request"></div>
	<div id="myModal" class="modal"></div>
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{asset('js/jquery.mask.js')}}"></script>
<script>
	$(function()
	{
		$('.datepicker').datepicker(
		{
			dateFormat : 'dd-mm-yy',
		});
	});
	$('input[name="amount"]').attr('value',$('#restaTotal').val());
	$('input[name="amountRes"]').attr('value',$('#restaTotal').val());
	$(document).ready(function()
	{
		generalSelect({'selector': '.js-employees', 'model': 48, 'id': {{$request->folio}}});
		generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
		@php
			$selects = collect([
				[
					"identificator"			 => ".js-kindPayment",	
					"placeholder"            => "Seleccione el tipo de pago", 
					"maximumSelectionLength" => "1"
				],
				[
					"identificator"			 => ".js-enterprises",	
					"placeholder"            => "Seleccione la empresa", 
					"maximumSelectionLength" => "1"
				]
			]);
		@endphp
		@component("components.scripts.selects",["selects" => $selects]) @endcomponent
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

				amount = $('.amount').val();

				if(!pathFlag) 
				{
					swal('','Por favor agregue los documentos faltantes.','error');
					return false;
				}
				else if(amount <= 0 || amount == '' || amount == 'NaN' || amount == null)
				{
					$('.amount').removeClass('valid').addClass('error');
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
		$('[name="amount"],[name="exchange_rate"]').on("contextmenu",function(e)
		{
			return false;
		});
		$('.subtotalRes,.ivaRes').numeric({ negative : false, altDecimal: '.', decimalPlaces: 2 });
		$('input[name="amount"]').numeric({ altDecimal: '.', decimalPlaces: 2 });
		$('input[name="exchange_rate"]').numeric({ altDecimal: ".", decimalPlaces: 2 });
	});
	$(document).on('click','.enviar',function (e)
	{
		e.preventDefault();
		$('.amount').removeClass('error');
		form = $('#container-alta');
		amount = $('.amount').val();
		if(amount == '' || amount == 'NaN' || amount == null)
		{
			$('.amount').val('0');
		}
		docFlag = true;
		if($('.path').length <= 0)
		{
			docFlag = false;
		}
		if(!docFlag) 
		{
			swal({
				title: "¿Desea enviar el pago sin comprobante?",
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
		@php
			$uploadDoc = html_entity_decode((String)view("components.documents.upload-files",[
				"classExInput"			=>	"pathActioner",
				"attributeExInput"		=>	"type=\"file\" name=\"path\" accept=\".pdf,.jpg,.png\"",
				"attributeExRealPath"	=>	"name=realPath[]",
				"classExRealPath"		=>	"path",
				"classExDelete"			=>	"delete-doc"
			]));
		@endphp
		uploadDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $uploadDoc)!!}';
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
	})
	.on('click','.exist-doc',function()
	{
		docR = $(this).parents('p.removeDoc').find('.iddocumentsPayments').val();
		inputDelete = $('<input type="text" name="deleteDoc[]">').val(docR);
		$('#docs-remove').append(inputDelete);
		$(this).parents('p.removeDoc').remove();
	})
	.on('change','.input-text.pathActioner',function(e)
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
	.on('click','.add-employee',function()
	{
		idnominaEmployee 	= $(this).parent('div').parent('div').parent('div').find('.idnominaEmployee').val();
		amount 				= $(this).parent('div').parent('div').parent('div').find('.netIncome').val();
		$('input[name="amount"]').val('');
		$('input[name="subtotalRes"]').val('');
		$('input[name="ivaRes"]').val('');
		$('input[name="amount"]').val(amount);
		$('input[name="subtotalRes"]').val(amount);
		$('select[name="type_payment"]').val('1').trigger('change');
		$('.js-employees').val(idnominaEmployee).trigger('change');
		$('.tr-beneficiary').stop(true,true).fadeOut();
	})
	.on('click','.add-beneficiary',function()
	{
		idnominaEmployee	= $(this).parent('div').parent('div').parent('div').children('div').find('.idnominaEmployee_beneficiary').val();
		amount				= $(this).parent('div').parent('div').children('div').find('.netIncome').val();
		beneficiary			= $(this).parent('div').parent('div').parent('div').children('div').find('.beneficiary-label').val();

		$('input[name="amount"]').val(amount);
		$('input[name="subtotalRes"]').val(amount);
		$('input[name="beneficiary_pay"]').val(beneficiary);
		$('.js-employees').val(idnominaEmployee).trigger('change');	
		$('.tr-beneficiary').stop(true,true).fadeIn();
		$('select[name="type_payment"]').val('2').trigger('change');
		$('input[name="commentaries"]').val('Pago de pensión alimenticia');
	})
	.on('change','[name="type_payment"]',function()
	{
		this_val = $(this,'option:selected').val();
		if (this_val == 2) 
		{
			$('.tr-beneficiary').stop(true,true).fadeIn();
			flag = false;
			idnominaEmployee = $('select[name="idnominaEmployee"] option:selected').val();
			if(idnominaEmployee != "")
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
					$('.tr-beneficiary').stop(true,true).fadeIn();
					$('input[name="beneficiary_pay"]').val(beneficiary);
				}
				else
				{
					$('.tr-beneficiary').stop(true,true).fadeOut();
					$('[name="type_payment"]').val('1').trigger('change');
					$('input[name="beneficiary_pay"]').val('');
					swal('','Este empleado no paga pensión alimenticia','error');
				}
			}
		}
		else
		{
			$('input[name="beneficiary_pay"]').val('');
			$('.tr-beneficiary').stop(true,true).fadeOut();
		}
	})
	.on('change','[name="idnominaEmployee"]',function()
	{
		flag = false;
		idnominaEmployee = $(this,'option:selected').val();
		if($('[name="type_payment"] option:selected').val() == 2)
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
				$('.tr-beneficiary').stop(true,true).fadeOut();
				$('[name="type_payment"]').val('1').trigger('change');
				swal('','Este empleado no paga pensión alimenticia','error');
			}
		}
	})
	.on('change','.subtotalRes, .ivaRes',function()
	{
		subtotalRes		= $('.subtotalRes').val();
		ivaRes			= $('.ivaRes').val();
		if(ivaRes == null || ivaRes == '' || ivaRes == 'NaN')
		{
			ivaRes = 0;
		}
		if(subtotalRes == null || subtotalRes == '' || subtotalRes == 'NaN')
		{
			subtotalRes = 0;
		}
		subtotalRes		= parseFloat(subtotalRes);
		ivaRes			= parseFloat(ivaRes);
		$('.amount').val((subtotalRes+ivaRes).toFixed(2));
	}).on('change','.amount',function()
	{
		var amount = 0;
		if((parseFloat($('.subtotalRes').val())) != 0 && (parseFloat($('.subtotalRes').val())) != 0)
		{
			subtotalRes		= parseFloat($('.subtotalRes').val());
			ivaRes			= parseFloat($('.ivaRes').val());
			amount = ivaRes + subtotalRes
		}
		else if((parseFloat($('.subtotalRes').val())) != 0)
		{
			subtotalRes		= parseFloat($('.subtotalRes').val());
			amount = amount + subtotalRes
		} 
		else if((parseFloat($('.ivaRes').val())) != 0)
		{
			ivaRes			= parseFloat($('.ivaRes').val());
			amount = amount + ivaRes
		}

		if(amount != 0 && $('.amount').val() != amount){
			$('.amount').val(amount.toFixed(2))
		}
	})
	.on("focusout",".subtotalRes,.ivaRes,.amount",function()
	{
		valueThis = $.isNumeric($(this).val());
		if(valueThis == false)
		{
			$(this).val(null);
		}
	});
</script>
@endsection
