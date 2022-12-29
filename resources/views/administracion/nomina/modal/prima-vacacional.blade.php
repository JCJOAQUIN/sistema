@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedataf',$nominaemployee->vacationPremium->first()->idvacationPremium)."\"", "methodEx" => "PUT"])
	@component('components.labels.title-divisor') DATOS @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"vacationpremium_idpaymentMethod\" value=\"1\" id=\"accountBank\"".($nominaemployee->vacationPremium()->exists() && $nominaemployee->vacationPremium->first()->idpaymentMethod == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"vacationpremium_idpaymentMethod\" value=\"2\" id=\"cash\"".($nominaemployee->vacationPremium()->exists() && $nominaemployee->vacationPremium->first()->idpaymentMethod == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"vacationpremium_idpaymentMethod\" value=\"3\" id=\"checks\"".($nominaemployee->vacationPremium()->exists() && $nominaemployee->vacationPremium->first()->idpaymentMethod == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"vacationpremium_idpaymentMethod\" value=\"4\" id=\"checks_refund\"".($nominaemployee->vacationPremium()->exists() && $nominaemployee->vacationPremium->first()->idpaymentMethod == 4 ? " checked" : ""),
			]					
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->vacationPremium()->exists() && $nominaemployee->vacationPremium->first()->idpaymentMethod == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
			{
				$varChecked = '';
				if(in_array($b->id, $nominaemployee->vacationPremium->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray()))
				{
					$varChecked = "checked";
				} 
				$varChec = '';
				if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->last()->id)
				{
					$varChec = "checked";
				}
				
				$body =
				[
					[
						"content" => []
					],
					[
						"content" => 
						[
							[ "label" => $b->alias ]
						]
					],
					[
						"content" => 
						[
							[ "label" => $b->bank->description ]
						]
					],
					[
						"content" => 
						[
							[ "label" => $b->clabe != '' ? $b->clabe : '---' ]
						]
					],
					[
						"content" => 
						[
							[ "label" => $b->account != '' ? $b->account : '---' ]
						]
					],
					[
						"content" => 
						[
							[ "label" => $b->cardNumber != '' ? $b->cardNumber : '---' ]
						]
					],
					[
						"content" => 
						[
							[ "label" => $b->branch != '' ? $b->branch : '---' ]
						]
					]
				];
				if($nominaemployee->vacationPremium->first()->nominaEmployeeAccounts()->exists())
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"vacationpremium_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChecked,
						"classEx"		=> "checkbox",
						"label"			=> "<span class=\"icon-check\"></span>",
						"radio"			=> true
					]);
				}
				else
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"vacationpremium_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChec,
						"classEx"		=> "checkbox",
						"label"			=> "<span class=\"icon-check\"></span>",
						"radio"			=> true
					]);
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" 	=> $modelBody,
			"modelHead" 	=> $modelHead,
			"title"			=> "SELECCIONE UNA CUENTA DEL EMPLEADO"
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent 
	</div>
	<div class="table-responsive @if($nominaemployee->vacationPremium->first()->alimony > 0) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
			{
				$varCheckedA = '';
				if($b->id == $nominaemployee->vacationPremium->first()->idAccountBeneficiary)
				{
					$varCheckedA = 'checked';
				} 
				$varChecA = '';
				if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id)
				{
					$varChecA = 'checked';
				}
				$body =
				[
					[
						"content" => []
					],
					[
						"content" =>
						[
							[ "label" => $b->beneficiary ]
						]
					],
					[
						"content" =>
						[
							[ "label" => $b->alias ]
						]
					],
					[
						"content" =>
						[
							[ "label" => $b->bank->description ]
						]
					],
					[
						"content" =>
						[
							[ "label" => $b->clabe != '' ? $b->clabe : '---' ]
						]
					],
					[
						"content" =>
						[
							[ "label" => $b->account != '' ? $b->account : '---' ]
						]
					],
					[
						"content" =>
						[
							[ "label" =>  $b->cardNumber != '' ? $b->cardNumber : '---' ]
						]
					],
					[
						"content" =>
						[
							[ "label" => $b->branch != '' ? $b->branch : '---' ]
						]
					],
				];
				if($nominaemployee->vacationPremium->first()->idAccountBeneficiary != '')
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"vacationpremium_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varCheckedA,
						"classEx"		=> "checkbox",
						"label"			=> "<span class=\"icon-check\"></span>",
						"radio"			=> true
					]);
				}
				else
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"vacationpremium_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varChecA,
						"classEx"		=> "checkbox",
						"label"			=> "<span class=\"icon-check\"></span>",
						"radio"			=> true
					]);
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', [
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead,
			"title"		=> "SELECCIONE LA CUENTA DEL BENEFICIARIO DE PENSIÓN ALIMENTICIA"
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
	</div>
	@component('components.labels.subtitle') INFORMACIÓN @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') S.D. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_sd" placeholder="Ingrese el S.D." value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->sd : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') S.D.I. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_sdi" placeholder="Ingrese el S.D.I" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->sdi : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días trabajados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_workedDays" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->workedDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días para vacaciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_holidaysDays" placeholder="Ingrese los días para vacaciones" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->holidaysDays : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') PERCEPCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Vacaciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_holidays" placeholder="Ingrese las vacaciones" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->holidays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima vacacional exenta: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_exemptHolidayPremium" placeholder="Ingrese la prima vacacional exenta" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->exemptHolidayPremium : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima vacacional gravada: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_holidayPremiumTaxed" placeholder="Ingrese la prima vacacional gravada" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->holidayPremiumTaxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_totalPerceptions" placeholder="Ingrese el total" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->totalPerceptions : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') RETENCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') ISR: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_isr" placeholder="Ingrese el ISR" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->isr : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Pensión Alimenticia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_alimony" placeholder="Ingrese la pensión alimenticia" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->alimony : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_totalTaxes" placeholder="Ingrese el total" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->totalTaxes : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo neto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="vacationpremium_netIncome" readonly="readonly" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->vacationPremium()->exists() ? $nominaemployee->vacationPremium->first()->netIncome : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="folio" value="{{ $folio }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idtypepayroll" value="{{ $idtypepayroll }}"
		@endslot
	@endcomponent
	<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
		@component('components.buttons.button', ["variant" => "primary"])
			@slot('attributeEx')
				type="submit" name="senddata"  value="{{ $nominaemployee->vacationPremium()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->vacationPremium()->exists() ? 'Actualizar' : 'Agregar' }}</span>
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

<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$('input[name="vacationpremium_sd"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_workedDays"]').numeric({ negative:false});
	$('input[name="vacationpremium_holidaysDays"]').numeric({ negative:false});
	$('input[name="vacationpremium_holidays"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_exemptHolidayPremium"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_holidayPremiumTaxed"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_totalPerceptions"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_isr"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_netIncome"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_alimony"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="vacationpremium_totalTaxes"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
</script>
