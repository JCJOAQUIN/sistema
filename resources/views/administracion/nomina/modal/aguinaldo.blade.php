@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedataf',$nominaemployee->bonus->first()->idBonus)."\"", "methodEx" => "PUT"])
	@component('components.labels.title-divisor') DATOS @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"bonus_idpaymentMethod\" value=\"1\" id=\"accountBank\"".($nominaemployee->bonus()->exists() && $nominaemployee->bonus->first()->idpaymentMethod == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"bonus_idpaymentMethod\" value=\"2\" id=\"cash\"".($nominaemployee->bonus()->exists() && $nominaemployee->bonus->first()->idpaymentMethod == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"bonus_idpaymentMethod\" value=\"3\" id=\"checks\"".($nominaemployee->bonus()->exists() && $nominaemployee->bonus->first()->idpaymentMethod == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"bonus_idpaymentMethod\" value=\"4\" id=\"checks_refund\"".($nominaemployee->bonus()->exists() && $nominaemployee->bonus->first()->idpaymentMethod == 4 ? " checked" : ""),
			]					
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->bonus()->exists() && $nominaemployee->bonus->first()->idpaymentMethod == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= [ "Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal" ];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
			{
				$varChecked = '';
				if(in_array($b->id, $nominaemployee->bonus->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray()))
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
				if($nominaemployee->bonus->first()->nominaEmployeeAccounts()->exists())
				{
					array_push($body[0]['content'], 
					[							
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"bonus_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChecked,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"bonus_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChec,
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
	<div class="table-responsive @if($nominaemployee->bonus->first()->alimony > 0) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
			{
				$varCheckedA = '';
				if($b->id == $nominaemployee->bonus->first()->idAccountBeneficiary)
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
				if($nominaemployee->bonus->first()->idAccountBeneficiary != '')
				{
					array_push($body[0]['content'], 
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"bonus_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varCheckedA,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"bonus_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varChecA,
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
					type="text" name="bonus_sd" placeholder="Ingrese el S.D." value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->sd : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') S.D.I. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_sdi" placeholder="Ingrese el S.D.I" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->sdi : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@php
				$dateAdmission = '';
				if($nominaemployee->bonus()->exists())
				{
					$dateAdmission = $nominaemployee->bonus->first()->dateOfAdmission != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->bonus->first()->dateOfAdmission)->format('d-m-Y') : null;
				}
			@endphp
			@component('components.labels.label') Fecha de ingreso: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_dateOfAdmission" placeholder="Ingrese la fecha" value="{{ $dateAdmission }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días para aguinaldos: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_daysForBonuses" placeholder="Ingrese los dias para aguinaldos" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->daysForBonuses : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Parte proporcional para aguinaldo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_proportionalPartForChristmasBonus" placeholder="Ingrese la parte proporcional para aguinaldo" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->proportionalPartForChristmasBonus : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') PERCEPCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Aguinaldo exento: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_exemptBonus" placeholder="Ingrese el aguinaldo exento" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->exemptBonus : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Aguinaldo gravable: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_taxableBonus" placeholder="Ingrese el aguinaldo gravable" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->taxableBonus : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_totalPerceptions" placeholder="Ingrese el total" value="{{  $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->totalPerceptions : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') RETENCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Pensión Alimenticia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_alimony" placeholder="Ingrese la pensión alimenticia" value="{{  $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->alimony : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') ISR: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_isr" placeholder="Ingrese el ISR" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->isr : null  }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_totalTaxes" placeholder="Ingrese el total" value="{{  $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->totalTaxes : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo neto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="bonus_netIncome" readonly="readonly" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->bonus()->exists() ? $nominaemployee->bonus->first()->netIncome : null }}"
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
				type="submit" name="senddata"  value="{{ $nominaemployee->bonus()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->bonus()->exists() ? 'Actualizar' : 'Agregar' }}</span>
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
	$(document).ready(function()
	{
		$('input[name="bonus_sd"],input[name="bonus_sdi"],input[name="bonus_proportionalPartForChristmasBonus"]').numeric({ altDecimal: ".", negative:false});
		$('input[name="bonus_daysForBonuses"]').numeric({ negative:false});
		$('input[name="bonus_exemptBonus"],input[name="bonus_taxableBonus"],input[name="bonus_totalPerceptions"]').numeric({ altDecimal: ".", negative:false});
		$('input[name="bonus_alimony"],input[name="bonus_isr"],input[name="bonus_totalTaxes"]').numeric({ altDecimal: ".", negative:false});	
	});
</script>