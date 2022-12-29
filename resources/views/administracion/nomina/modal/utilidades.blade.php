@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.nomina-create.updatedataf',$nominaemployee->profitSharing->first()->idprofitSharing)."\"", "methodEx" => "PUT"])
	@component('components.labels.title-divisor') DATOS @endcomponent
	@component('components.labels.subtitle') SELECCIONE UNA FORMA DE PAGO PARA EL EMPLEADO @endcomponent
	@php
		$buttons = 
		[
			[
				"textButton" 		=> "Cuenta Bancaria",
				"attributeButton" 	=> "type=\"radio\" name=\"profitsharing_idpaymentMethod\" value=\"1\" id=\"accountBank\"".($nominaemployee->profitSharing()->exists() && $nominaemployee->profitSharing->first()->idpaymentMethod == 1 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Efectivo",
				"attributeButton" 	=> "type=\"radio\" name=\"profitsharing_idpaymentMethod\" value=\"2\" id=\"cash\"".($nominaemployee->profitSharing()->exists() && $nominaemployee->profitSharing->first()->idpaymentMethod == 2 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque",
				"attributeButton" 	=> "type=\"radio\" name=\"profitsharing_idpaymentMethod\" value=\"3\" id=\"checks\"".($nominaemployee->profitSharing()->exists() && $nominaemployee->profitSharing->first()->idpaymentMethod == 3 ? " checked" : ""),
			],
			[
				"textButton" 		=> "Cheque para Reintegro",
				"attributeButton" 	=> "type=\"radio\" name=\"profitsharing_idpaymentMethod\" value=\"4\" id=\"checks_refund\"".($nominaemployee->profitSharing()->exists() && $nominaemployee->profitSharing->first()->idpaymentMethod == 4 ? " checked" : ""),
			]							
		];
	@endphp
	@component("components.buttons.buttons-pay-method", ["buttons" => $buttons]) @endcomponent
	<div class="resultbank table-responsive @if($nominaemployee->profitSharing()->exists() && $nominaemployee->profitSharing->first()->idpaymentMethod == 1) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',1) as $b)
			{
				$varChecked = '';
				if(in_array($b->id, $nominaemployee->profitSharing->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray()))
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
				if($nominaemployee->profitSharing->first()->nominaEmployeeAccounts()->exists())
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"profitsharing_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChecked,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"profitsharing_idemployeeAccounts[]\" value=\"".$b->id."\"".' '.$varChec,
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
	<div class="table-responsive @if($nominaemployee->profitSharing->first()->alimony > 0) block @else hidden @endif">
		@php
			$body		= [];
			$modelBody	= []; 
			$modelHead 	= ["Acción", "Beneficiario", "Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

			foreach ($nominaemployee->employee->first()->bankData->where('visible',1)->where('type',2) as $b)
			{
				$varCheckedA = '';
				if($b->id == $nominaemployee->profitSharing->first()->idAccountBeneficiary)
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
				if($nominaemployee->profitSharing->first()->idAccountBeneficiary != '')
				{
					array_push($body[0]['content'],
					[
						"kind"			=> "components.inputs.checkbox",
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"profitsharing_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varCheckedA,
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
						"attributeEx"	=> "id=\"idEmp$b->id\" name=\"profitsharing_idAccountBeneficiary[]\" value=\"".$b->id."\"".' '.$varChecA,
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
					type="text" name="profitsharing_sd" placeholder="Ingrese el S.D." value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->sd : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') S.D.I. @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_sdi" placeholder="Ingrese el S.D.I" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->sdi : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Días trabajados: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_workedDays" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->workedDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_totalSalary" placeholder="Ingrese el total" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->totalSalary : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') PTU por días: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_ptuForDays" placeholder="Ingrese el PTU por días" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->ptuForDays : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') PTU por sueldo: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_ptuForSalary" placeholder="Ingrese el PTU por sueldo" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->ptuForSalary : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') PTU total: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_totalPtu" placeholder="Ingrese el PTU total" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->totalPtu : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') PERCEPCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') PTU exenta: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_exemptPtu" placeholder="Ingrese el PTU exenta" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->exemptPtu : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') PTU gravada: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_taxedPtu" placeholder="Ingrese el PTU gravada" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->taxedPtu : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total percepciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_totalPerceptions" placeholder="Ingrese el total percepciones" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->totalPerceptions : null }}"
				@endslot
			@endcomponent
		</div>
	@endcomponent
	@component('components.labels.subtitle') RETENCIONES @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label') Retenciones de ISR: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_isrRetentions" placeholder="Ingrese las retenciones de ISR" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->isrRetentions : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Pensión Alimenticia: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_alimony" placeholder="Ingrese la pensión alimenticia" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->alimony : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Total retenciones: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_totalRetentions" placeholder="Ingrese el total retenciones" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->totalRetentions : null }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label') Sueldo neto: @endcomponent
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="text" name="profitsharing_netIncome" readonly="readonly" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->profitSharing()->exists() ? $nominaemployee->profitSharing->first()->netIncome : null }}"
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
				type="submit" name="senddata"  value="{{ $nominaemployee->profitSharing()->exists() ? 'Actualizar' : 'Agregar' }}"
			@endslot
			@slot('label')
				<span class="icon-check"></span> <span>{{ $nominaemployee->profitSharing()->exists() ? 'Actualizar' : 'Agregar' }}</span>
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
	$('input[name="profitsharing_sd"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_workedDays"]').numeric({ negative:false});
	$('input[name="profitsharing_totalSalary"]').numeric({ negative:false});
	$('input[name="profitsharing_ptuForDays"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_ptuForSalary"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_totalPtu"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_exemptPtu"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_taxedPtu"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_totalPerceptions"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_isrRetentions"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_alimony"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_totalRetentions"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
	$('input[name="profitsharing_netIncome"]').numeric({ altDecimal: ".", decimalPlaces: 2,});
</script>