@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedataf',$nominaemployee->liquidation->first()->idLiquidation)."\"", "methodEx" => "PUT"])
	@component('components.labels.title-divisor') DATOS @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"liquidation_idpaymentMethod\" value=\"1\" id=\"accountBank\"".($nominaemployee->liquidation()->exists() && $nominaemployee->liquidation->first()->idpaymentMethod == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"liquidation_idpaymentMethod\" value=\"2\" id=\"cash\"".($nominaemployee->liquidation()->exists() && $nominaemployee->liquidation->first()->idpaymentMethod == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"liquidation_idpaymentMethod\" value=\"3\" id=\"checks\"".($nominaemployee->liquidation()->exists() && $nominaemployee->liquidation->first()->idpaymentMethod == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"liquidation_idpaymentMethod\" value=\"4\" id=\"checks_refund\"".($nominaemployee->liquidation()->exists() && $nominaemployee->liquidation->first()->idpaymentMethod == 4 ? " checked" : ""),
			]							
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->liquidation()->exists() && $nominaemployee->liquidation->first()->idpaymentMethod == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
			{
				$varChecked = '';
				if(in_array($b->id, $nominaemployee->liquidation->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray()))
				{
					$varChecked = "checked";
				} 
				$varChec = '';
				if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->last()->id)
				{
					$varChec = "checked";
				}
				
				$body = [
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
				if($nominaemployee->liquidation->first()->nominaEmployeeAccounts()->exists())
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"liquidation_idEmployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChecked,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"liquidation_idEmployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChec,
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
	<div class="table-responsive @if($nominaemployee->liquidation->first()->alimony > 0) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
			{
				$varCheckedA = '';
				if($b->id == $nominaemployee->liquidation->first()->idAccountBeneficiary)
				{
					$varCheckedA = 'checked';
				} 
				$varChecA = '';
				if($b->id == $nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2)->last()->id)
				{
					$varChecA = 'checked';
				}
				$body = [
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
					]
				];
				if($nominaemployee->liquidation->first()->idAccountBeneficiary != '')
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"liquidation_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varCheckedA,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"liquidation_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varChecA,
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
					type="text" name="liquidation_sd" placeholder="Ingrese el S.D." value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->sd : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') S.D.I. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_sdi" placeholder="Ingrese el S.D.I" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->sdi : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$newDateAdmission	= '';
				if($nominaemployee->liquidation()->exists())
				{
					$newDateAdmission = $nominaemployee->liquidation->first()->admissionDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->liquidation->first()->admissionDate)->format('d-m-Y') : '';
				}
			@endphp
			@component('components.labels.label') Fecha de ingreso: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_admissionDate" placeholder="Ingrese la fecha" value="{{ $newDateAdmission }}"
				@endslot
				@slot('classEx')
					datepicker
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$dateDown = '';
				if($nominaemployee->liquidation()->exists())
				{
					$dateDown = $nominaemployee->liquidation->first()->downDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->liquidation->first()->downDate)->format('d-m-Y') : '';
				}
			@endphp
			@component('components.labels.label') Fecha de baja: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_downDate" placeholder="Ingrese la fecha" value="{{ $dateDown }}"
				@endslot
				@slot('classEx')
					datepicker
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Años completos: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_fullYears" placeholder="Ingrese los años" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->fullYears : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días trabajados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_workedDays" placeholder="Ingrese los dias trabajados" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->workedDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días para vacaciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_holidayDays" placeholder="Ingrese los dias para vacaciones" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->holidayDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días de aguinaldo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_bonusDays" placeholder="Ingrese los dias de aguinaldo" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->bonusDays : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') PERCEPCIONES @endcomponent
	@component('components.containers.container-form')
		@if($idtypepayroll == '004')
			<div class="col-span-2">
				@component('components.labels.label') Sueldo por liquidación: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="liquidation_liquidationSalary" placeholder="Ingrese el sueldo por liquidación" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->liquidationSalary : null }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') 20 días x año de servicios: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" name="liquidation_twentyDaysPerYearOfServices" placeholder="Ingrese los dias por año de servicios" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->twentyDaysPerYearOfServices : null }}"
					@endslot
				@endcomponent
			</div>
		@endif
		<div class="col-span-2">
			@component('components.labels.label') Prima de antigüedad: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_seniorityPremium" placeholder="Ingrese la prima de antigüedad" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->seniorityPremium : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Vacaciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_holidays" placeholder="Ingrese las vacaciones" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->holidays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Indemnización exenta: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_exemptCompensation" placeholder="Ingrese la indemnización exenta" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->exemptCompensation : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Indemnización gravada: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_taxedCompensation" placeholder="Ingrese la indemnización gravada" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->taxedCompensation : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Aguinaldo exento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_exemptBonus" placeholder="Ingrese el aguinaldo exento" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->exemptBonus : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Aguinaldo gravable: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_taxableBonus" placeholder="Ingrese el aguinaldo gravable" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->taxableBonus : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima vacacional exenta: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_holidayPremiumExempt" placeholder="Ingrese la prima vacacional exenta" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->holidayPremiumExempt : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima vacacional gravada: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_holidayPremiumTaxed" placeholder="Ingrese la prima vacacional gravada" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->holidayPremiumTaxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Otras percepciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_otherPerception" placeholder="Ingrese otras percepciones" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->otherPerception : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_totalPerceptions" placeholder="Ingrese el total" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->totalPerceptions : null }}"
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
					type="text" name="liquidation_isr" placeholder="Ingrese el ISR" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->isr : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Pensión Alimenticia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_alimony" placeholder="Ingrese la pensión alimenticia" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->alimony : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Otras retenciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_otherRetention" placeholder="Ingrese la retención" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->other_retention : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_totalRetentions" placeholder="Ingrese el total" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->totalRetentions : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo neto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="liquidation_netIncome" readonly="readonly" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->liquidation()->exists() ? $nominaemployee->liquidation->first()->netIncome : null }}"
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
				type="submit" name="senddata"  value="{{ $nominaemployee->liquidation()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->liquidation()->exists() ? 'Actualizar' : 'Agregar' }}</span>
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
	$('input[name="liquidation_sd"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_fullYears"]').numeric({ negative:false});
	$('input[name="liquidation_workedDays"]').numeric({ negative:false});
	$('input[name="liquidation_holidayDays"]').numeric({ negative:false});
	$('input[name="liquidation_bonusDays"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_seniorityPremium"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_holidays"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_exemptCompensation"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_taxedCompensation"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_exemptBonus"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_taxableBonus"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_holidayPremiumExempt"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_holidayPremiumTaxed"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_otherPerception"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_totalPerceptions"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_isr"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_totalRetentions"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_netIncome"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_alimony"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_liquidationSalary"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_twentyDaysPerYearOfServices"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
	$('input[name="liquidation_otherRetention"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false});
</script>
