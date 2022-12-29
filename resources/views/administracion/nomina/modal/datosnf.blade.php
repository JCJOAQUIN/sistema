@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedatanf')."\""])
	@component('components.labels.title-divisor') DATOS DEL PAGO DEL EMPLEADO @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"1\" id=\"accountBank\"".($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 1 ? " checked" : $paymentWay[0] == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"2\" id=\"cash\"".($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 2 ? " checked" : $paymentWay[0] == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"3\" id=\"checks\"".($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 3 ? " checked" : $paymentWay[0] == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"4\" id=\"checks_refund\"".($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 4 ? " checked" : $paymentWay[0] == 4 ? " checked" : ""),
			]							
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idpaymentMethod == 1) block @elseif($paymentWay[0] == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead 	= ["Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];
			foreach ($employee->bankData->where('visible',1) as $b) 
			{
				$varChecked = '';
				if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->idemployeeAccounts == $b->id)
				{
					$varChecked = "checked";
				}
				elseif($idemployeeAccount == $b->id)
				{
					$varChecked = "checked";
				}
				$body = [
					[
						"content" => 
						[
							[
								"kind"			=> "components.inputs.checkbox",
								"attributeEx"	=> "id=\"idEmp$b->id\" name=\"idEmployeeAccounts\" value=\"".$b->id."\"".' '.$varChecked,
								"classEx"		=> "checkbox",
								"label"			=> "<span class=\"icon-check\"></span>",
								"radio"			=> true
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->alias
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->bank->description
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->clabe != '' ? $b->clabe : '---'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->account != '' ? $b->account : '---'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->cardNumber != '' ? $b->cardNumber : '---'
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $b->branch != '' ? $b->branch : '---'
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp	
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"title"			=> "SELECCIONE UNA CUENTA"
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent 
	</div>
	<div>
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "Referencia"],
					["value" => "Razón de pago"]
				]
			];
			$requestModel = App\RequestModel::find($folio);
			if($requestModel->nominasReal->first()->idCatTypePayroll == '001')
			{
				$modelHead[0][] = ["value" => "Horas Extra No Fiscal"];
				$modelHead[0][] = ["value" => "Días Festivos No Fiscal"];
				$modelHead[0][] = ["value" => "Domingos Trabajados No Fiscal"];
				$modelHead[0][] = ["value" => "Neto No Fiscal"];
			}
			$modelHead[0][] = ["value" => "Total No Fiscal a Pagar"];

			$varReference 	= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->reference : null;
			$varReason		= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->reasonAmount : null;
			$extraTime		= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->extra_time : null;
			$holidayE		= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->holiday : null;
			$sundaysE		= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->sundays : null;
			$complementE	= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->complementPartial : $nominaemployee->workerData->first()->complement;
			$amount			= $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->amount : $nominaemployee->workerData->first()->complement;
			$body = 
			[	"classEx" => "tr_nominasNF",
				[
					"content" =>
					[
						"kind"			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la referencia\" name=\"employee_reference\" value=\"".$varReference."\""
					]
				],
				[
					"content" =>
					[
						"kind" 			=> "components.inputs.input-text",
						"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la razón de pago\" name=\"employee_reason_payment\" value=\"".$varReason."\""
					]
				]
			];
			if($requestModel->nominasReal->first()->idCatTypePayroll == '001')
			{
				array_push($body, [ "content" => [
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"employee_extra_time\" placeholder=\"Ingrese las horas extra\" value=\"".$extraTime."\""
				]]);
				array_push($body, [ "content" => [
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"employee_holiday\" placeholder=\"Ingrese los días festivos\" value=\"".$holidayE."\""
				]]);
				array_push($body, [ "content" => [
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"employee_sundays\" placeholder=\"Ingrese los domingos trabajados\" value=\"".$sundaysE."\""
				]]);
				array_push($body, [ "content" => [
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"employee_complement\" placeholder=\"Ingrese neto no fiscal\" data-validation=\"number\" data-validation-allowing=\"float\" value=\"".$complementE."\"",
					"classEx"		=> "employee_complement"
				]]);
			}
			array_push($body, [ "content" => [
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" readonly=\"readonly\" name=\"employee_amount\" placeholder=\"Ingrese el total\" data-validation=\"number\" data-validation-allowing=\"float\" value=\"".$amount."\"",
				"classEx"		=> "employee_amount"
			]]);
			$modelBody[] = $body;
		@endphp
		@component('components.tables.table', [
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"title"			=> "INGRESE LOS SIGUIENTES DATOS"
		])
		@endcomponent 
	</div>
	<div>
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Monto", "Motivo", "Acción"];
			$body = [ "classEx"	=> "tr_ex",
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "placeholder=\"Ingrese el monto\" type=\"text\"",
							"classEx"		=> "employee_extra"
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "placeholder=\"Ingrese el motivo\" type=\"text\"",
							"classEx"		=> "employee_reason_extra"
						]
					]
				],
				[
					"content" => 
					[
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "warning",
							"attributeEx"	=> "type=\"button\" id=\"add_extra\"",
							"label"			=> "<span class=\"icon-plus\"></span>"
						]
					]
				]
			];
			$modelBody[] = $body;
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody"	=> $modelBody,
			"modelHead" => $modelHead,
			"title"		=> "EXTRAS (OPCIONAL)"
		])
		@endcomponent 
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Monto", "Motivo", "Acción"];
			
			if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->extras()->exists())
			{
				foreach($nominaemployee->nominasEmployeeNF->first()->extras as $extra)
				{
					$body = [ "classEx"	=> "tr_ex",
						[
							"content" =>
							[
								[
									"label" =>  $extra->amount
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_id_extra[]\" value=\"".$extra->id."\"",
									"classEx"		=> "t_id_extra"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_extra[]\" value=\"".$extra->amount."\"",
									"classEx"		=> "t_employee_extra"
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" =>  $extra->reason
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_reason_extra[]\" value=\"".$extra->reason."\"",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "delete-extra",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"classExBody"	=> "body_content_ex"
		])
			@slot('attributeExBody')
				id="extras"
			@endslot
		@endcomponent
	</div>
	<div>
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Monto", "Motivo", "Acción"];
			$body = [ "classEx"	=> "tr_dis",
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "placeholder=\"Ingrese el monto\" type=\"text\"",
							"classEx"		=> "employee_discount"
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx"	=> "placeholder=\"Ingrese el motivo\" type=\"text\"",
							"classEx"		=> "employee_reason_discount"
						]
					]
				],
				[
					"content" => 
					[
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "warning",
							"attributeEx"	=> "type=\"button\" id=\"add_discount\"",
							"label"			=> "<span class=\"icon-plus\"></span>"
						]
					]
				]
			];
			$modelBody[] = $body;
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" => $modelBody,
			"modelHead" => $modelHead,
			"title"		=> "DESCUENTOS (OPCIONAL)"
		])
		@endcomponent 
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= ["Monto", "Motivo", "Acción"];
			
			if($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->nominasEmployeeNF->first()->discounts()->exists())
			{
				foreach($nominaemployee->nominasEmployeeNF->first()->discounts as $discount)
				{
					$body = [	"classEx" => "tr_dis",
						[
							"content" =>
							[
								[
									"label" => $discount->amount
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_id_discount[]\" value=\"".$discount->id."\"",
									"classEx"		=> "t_id_discount"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_discount[]\" value=\"".$discount->amount."\"",
									"classEx"		=> "t_employee_discount"
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" =>  $discount->reason
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_reason_discount[]\" value=\"".$discount->reason."\"",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "delete-discount",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}	
				foreach($nominaemployee->nominasEmployeeNF->first()->discountInfonavit as $discount)
				{
					$body = [	"classEx" => "tr_dis",
						[
							"content" =>
							[
								[
									"label" => $discount->amount
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_id_discount[]\" value=\"".$discount->id."\"",
									"classEx"		=> "t_id_discount"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_discount[]\" value=\"".$discount->amount."\"",
									"classEx"		=> "t_employee_discount"
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" =>  $discount->reason
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_employee_reason_discount[]\" value=\"".$discount->reason."\"",
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "red",
									"attributeEx"	=> "type=\"button\"",
									"classEx"		=> "delete-discount",
									"label"			=> "<span class=\"icon-x\"></span>"
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"classExBody"	=> "body_content_dis"
		])
			@slot('attributeExBody')
				id="discounts"
			@endslot
		@endcomponent
	</div>
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idnominaEmployee" value="{{ $nominaemployee->idnominaEmployee }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="folio" value="{{ $folio }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="action" value="{{  $nominaemployee->nominasEmployeeNF()->exists() ? 'update' : 'new' }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idnominaemployeenf" value="{{  $nominaemployee->nominasEmployeeNF()->exists() ? $nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf : null }}"
		@endslot
	@endcomponent
	<div id="delete"></div>
	<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
		@component('components.buttons.button', ["variant" => "primary"])
			@slot('attributeEx')
				type="submit"
				name="senddata"
				value="{{ $nominaemployee->nominasEmployeeNF()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->nominasEmployeeNF()->exists() ? 'Actualizar' : 'Agregar' }}</span>
			@endslot
		@endcomponent
		@component('components.buttons.button', ["variant" => "red"])
			@slot('attributeEx')
				type="button" title="Cerrar" data-dismiss="modal"
			@endslot
			@slot('classEx')
				exit
			@endslot
			@slot('label')
				<span class="icon-x"></span> <span>Cerrar</span>
			@endslot
		@endcomponent
	</div>
@endcomponent
