@component('components.labels.title-divisor') DATOS DEL EMPLEADO @endcomponent
@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO @endcomponent
@php
	$buttons = 
	[
		[
			"textButton" 		=> "Cuenta Bancaria",
			"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"1\" id=\"accountBank\"".($paymentWay[0] == 1 ? " checked" : ""),
		],
		[
			"textButton" 		=> "Efectivo",
			"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"2\" id=\"cash\"".($paymentWay[0] == 2 ? " checked" : ""),
		],
		[
			"textButton" 		=> "Cheque",
			"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"3\" id=\"checks\"".($paymentWay[0] == 3 ? " checked" : ""),
		],
		[
			"textButton" 		=> "Cheque para Reintegro",
			"attributeButton" 	=> "type=\"radio\" name=\"method_request\" value=\"4\" id=\"checks_refund\"".($paymentWay[0] == 4 ? " checked" : ""),
		],								
	];
@endphp
@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
<div class="resultbank table-responsive @if($paymentWay[0] == 1) block @else hidden @endif">
	@php
		$body		= [];
		$modelBody	= []; 
		$modelHead 	= ["Acción","Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

		foreach ($employee->bankData->where('visible',1)->where('type',1) as $b)
		{
			$varChecked = '';
			if($idemployeeAccount == $b->id)
			{
				$varChecked = "checked";
			}	
			$body = [
				[
					"content" => 
					[
						[
							"kind"			=> "components.inputs.checkbox",
							"attributeEx"	=> "id=\"idEmp$b->id\" name=\"idEmployeeAccounts_request\" value=\"".$b->id."\"".' '.$varChecked,
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
@if($nominaemployee->workerData->first()->alimonyDiscount != '')
	@component('components.labels.title-divisor') DATOS DEL BENEFICIARIO @endcomponent
	<div class="resultbank table-responsive @if($paymentWay[0] == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach($employee->bankData->where('visible',1)->where('type',2) as $b) 
			{
				$varCheckedA = '';
				if($idAccountBeneficiary == $b->id)
				{
					$varCheckedA = 'checked';
				} 
				$body = [
					[
						"content" => 
						[
							[
								"kind"			=> "components.inputs.checkbox",
								"attributeEx"	=> "id=\"idEmp$b->id\" type=\"radio\" name=\"idAccountBeneficiary_request\" value=\"".$b->id."\"".' '.$varCheckedA,
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
								"label" => $b->beneficiary
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
								"label" =>  $b->cardNumber != '' ? $b->cardNumber : '---'
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
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead,
			"title"		=> "SELECCIONE UNA CUENTA"
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
	</div>
@endif
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
<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
	@component('components.buttons.button', ["variant" => "primary"])
		@slot('attributeEx')
			type="button" title="Actualizar" data-dismiss="modal"
		@endslot
		@slot('classEx')			
			update-paymentway-account
		@endslot
		@slot('label')
			<span class="icon-check"></span> <span>Actualizar</span>
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