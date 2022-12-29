@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedataf',$nominaemployee->salary->first()->idSalary)."\"", "methodEx" => "PUT"])
	@component('components.labels.title-divisor') DATOS @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"salary_idpaymentMethod\" value=\"1\" id=\"accountBank\"".($nominaemployee->salary->first()->idpaymentMethod == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"salary_idpaymentMethod\" value=\"2\" id=\"cash\"".($nominaemployee->salary->first()->idpaymentMethod == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"salary_idpaymentMethod\" value=\"3\" id=\"checks\"".($nominaemployee->salary->first()->idpaymentMethod == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"salary_idpaymentMethod\" value=\"4\" id=\"checks_refund\"".($nominaemployee->salary->first()->idpaymentMethod == 4 ? " checked" : ""),
			]						
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->salary->first()->idpaymentMethod == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
			{
				$varChecked = '';
				if(in_array($b->id, $nominaemployee->salary->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray()))
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
					],
				];
				if($nominaemployee->salary->first()->nominaEmployeeAccounts()->exists())
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"salary_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChecked,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"salary_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChec,
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
	<div class="table-responsive @if($nominaemployee->salary->first()->alimony > 0) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
			{
				$varCheckedA = '';
				if($b->id == $nominaemployee->salary->first()->idAccountBeneficiary)
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
					],
				];
				if($nominaemployee->salary->first()->idAccountBeneficiary != '')
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"salary_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varCheckedA,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"salary_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varChecA,
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
					type="text" name="salary_sd" placeholder="Ingrese el S.D." value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->sd : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') S.D.I. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_sdi" placeholder="Ingrese el S.D.I" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->sdi : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días trabajados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_workedDays" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->workedDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label')Días para IMSS: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_daysForImss" placeholder="Ingrese los días para IMSS" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->daysForImss : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') PERCEPCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Sueldo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_salary" placeholder="Ingrese el sueldo" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->salary : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Préstamo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_loan_perception" placeholder="Ingrese el préstamo" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->loan_perception : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Puntualidad: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_puntuality" placeholder="Ingrese la puntualidad" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->puntuality : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Asistencia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_assistance" placeholder="Ingrese la asistencia" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->assistance : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tiempo extra gravado: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_extra_hours_taxed" placeholder="Ingrese el tiempo extra gravado" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->extra_time_taxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Tiempo extra exento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_extra_hours" placeholder="Ingrese el tiempo extra exento" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->extra_time - $nominaemployee->salary->first()->extra_time_taxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Día festivo gravado: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_holiday_taxed" placeholder="Ingrese el día festivo gravado" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->holiday_taxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Día festivo exento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_holiday" placeholder="Ingrese el día festivo exento" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->holiday - $nominaemployee->salary->first()->holiday_taxed : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima dominical exenta: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_except_sundays" placeholder="Ingrese la prima dominical exenta" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->exempt_sunday : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Prima dominical gravada: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_taxed_sundays" placeholder="Ingrese la prima dominical gravada" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->taxed_sunday : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Subsidio Causado: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_subsidy" placeholder="Ingrese el subsidio causado" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->subsidyCaused : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Subsidio: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_subsidy" placeholder="Ingrese el subsidio" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->subsidy : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total percepciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_totalPerceptions" placeholder="Ingrese el total de percepciones" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->totalPerceptions : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') RETENCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') IMSS: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_imss" placeholder="Ingrese el número de IMSS" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->imss : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Infonavit: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_infonavit" placeholder="Ingrese el Infonavit" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->infonavit : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Fonacot: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_fonacot" placeholder="Ingrese el Fonacot" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->fonacot : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Préstamo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_loan_retention" placeholder="Ingrese el préstamo" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->loan_retention : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Pensión Alimenticia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_alimony" placeholder="Ingrese la pensión alimenticia" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->alimony : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Retención de ISR: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_isrRetentions" placeholder="Ingrese la retención de ISR" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->isrRetentions : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Otra retención: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_other_retention_concept" placeholder="Ingrese otra retención" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->other_retention_concept : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Importe de otra retención: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_other_retention_amount" placeholder="Ingrese el importe de otra retención" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->other_retention_amount : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total retenciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_totalRetentions" placeholder="Ingrese el total de retenciones" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->totalRetentions : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo neto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="salary_netIncome" readonly="readonly" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->salary()->exists() ? $nominaemployee->salary->first()->netIncome : null }}"
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
				type="submit" name="senddata"  value="{{ $nominaemployee->salary()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->salary()->exists() ? 'Actualizar' : 'Agregar' }}</span>
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
				<span class="icon-x"></span> <span>Cerrar<span>
			@endslot
		@endcomponent
	</div>
@endcomponent

<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$('input[name="salary_sd"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_workedDays"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_daysForImss"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_salary"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_loan_perception"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_puntuality"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_assistance"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_subsidy"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_subsidy"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_totalPerceptions"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_imss"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_infonavit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_loan_retention"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_alimony"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_isrRetentions"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_totalRetentions"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_netIncome"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_extra_hours_taxed"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_extra_hours"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_holiday_taxed"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_holiday"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_except_sundays"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_taxed_sundays"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
	$('input[name="salary_other_retention_amount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false});
</script>
