@if($fiscal == 1)
	@switch($typeNomina)
		@case('001') {{-- SUELDO --}}
			@component('components.labels.title-divisor') DATOS @endcomponent
			<div class="resultbank table-responsive" @if($nomina_employees->salary->first()->idpaymentMethod == 1) block @else hidden @endif>
				@php
					$body		= [];
					$modelBody	= []; 
					$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

					foreach (App\EmployeeAccount::whereIn('id',$nomina_employees->salary->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())->get() as $b)
					{
						$body = 
						[
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
						
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent 
			</div>
			@component('components.labels.subtitle') INFORMACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary->first()->paymentMethod->method }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D.:@endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->sd : null }}  @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D.I.: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->sdi : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días trabajados: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->workedDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold'])Días para IMSS:@endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->daysForImss : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') PERCEPCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->salary : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Préstamo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->loan_perception : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Puntualidad: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->puntuality : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Asistencia: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->assistance : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Tiempo extra gravado: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->extra_time_taxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Tiempo extra exento: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->extra_time - $nomina_employees->salary->first()->extra_time_taxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Día festivo gravado: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->holiday_taxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Día festivo exento: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->holiday - $nomina_employees->salary->first()->holiday_taxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima dominical exenta: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->exempt_sunday : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima dominical gravada: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->taxed_sunday : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Subsidio Causado: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->subsidyCaused : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Subsidio: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->subsidy : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total percepciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->totalPerceptions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') RETENCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) IMSS: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->imss : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Infonavit: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->infonavit : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Fonacot: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->fonacot : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Préstamo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->loan_retention : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Pensión Alimenticia: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->alimony : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Retención de ISR: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->isrRetentions : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Otra retención: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->other_retention_concept : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Importe de otra retención: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->other_retention_amount : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total retenciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->totalRetentions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') SUELDO NETO @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo neto: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->salary()->exists() ? $nomina_employees->salary->first()->netIncome : null }} @endcomponent
				</div>
			@endcomponent
		@break

		@case('002') {{-- AGUINALDO --}}
			@component('components.labels.title-divisor') DATOS @endcomponent
			<div class="resultbank table-responsive" @if($nomina_employees->bonus->first()->idpaymentMethod == 1) block @else hidden @endif>
				@php
					$body		= [];
					$modelBody	= []; 
					$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

					foreach (App\EmployeeAccount::whereIn('id',$nomina_employees->bonus->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())->get() as $b)
					{
						$body = 
						[
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
						
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent 
			</div>
			@component('components.labels.subtitle') INFORMACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus->first()->paymentMethod->method }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') S.D.: @endcomponent
					@component('components.labels.label'){{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->sd : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') S.D.I. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->sdi : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@php
						$dateAdmission = '';
						if($nomina_employees->bonus()->exists())
						{
							$dateAdmission = $nomina_employees->bonus->first()->dateOfAdmission != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nomina_employees->bonus->first()->dateOfAdmission)->format('d-m-Y') : null;
						}
					@endphp
					@component('components.labels.label',['classEx' => 'font-bold']) Fecha de ingreso: @endcomponent
					@component('components.labels.label') {{ $dateAdmission }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días para aguinaldos: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->daysForBonuses : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Parte proporcional para aguinaldo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->proportionalPartForChristmasBonus : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') PERCEPCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Aguinaldo exento: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->exemptBonus : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Aguinaldo gravable: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->taxableBonus : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{  $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->totalPerceptions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') RETENCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Pensión Alimenticia: @endcomponent
					@component('components.labels.label') {{  $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->alimony : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) ISR: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->isr : null  }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{  $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->totalTaxes : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') SUELDO NETO @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo neto: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->bonus()->exists() ? $nomina_employees->bonus->first()->netIncome : null }} @endcomponent
				</div>
			@endcomponent
		@break
		@case('003') {{-- FINIQUITO --}}
		@case('004') {{-- LIQUIDACIÓN --}}
			@component('components.labels.title-divisor') DATOS @endcomponent
			<div class="resultbank table-responsive" @if($nomina_employees->liquidation->first()->idpaymentMethod == 1) block @else hidden @endif>
				@php
					$body		= [];
					$modelBody	= []; 
					$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

					foreach (App\EmployeeAccount::whereIn('id',$nomina_employees->liquidation->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())->get() as $b)
					{
						$body = 
						[
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
						
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent 
			</div>
			@component('components.labels.subtitle') INFORMACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation->first()->paymentMethod->method }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->sd : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D.I. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->sdi : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@php
						$newDateAdmission	= '';
						if($nomina_employees->liquidation()->exists())
						{
							$newDateAdmission = $nomina_employees->liquidation->first()->admissionDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nomina_employees->liquidation->first()->admissionDate)->format('d-m-Y') : '';
						}
					@endphp
					@component('components.labels.label',['classEx' => 'font-bold']) Fecha de ingreso: @endcomponent
					@component('components.labels.label') {{ $newDateAdmission }}@endcomponent
				</div>
				<div class="col-span-2">
					@php
						$dateDown = '';
						if($nomina_employees->liquidation()->exists())
						{
							$dateDown = $nomina_employees->liquidation->first()->downDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$nomina_employees->liquidation->first()->downDate)->format('d-m-Y') : '';
						}
					@endphp
					@component('components.labels.label',['classEx' => 'font-bold']) Fecha de baja: @endcomponent
					@component('components.labels.label') {{ $dateDown }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Años completos: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->fullYears : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días trabajados: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->workedDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días para vacaciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->holidayDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días de aguinaldo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->bonusDays : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') PERCEPCIONES @endcomponent
			@component('components.containers.container-form')
				@if($typeNomina == '004')
					<div class="col-span-2">
						@component('components.labels.label',['classEx' => 'font-bold']) Sueldo por liquidación: @endcomponent
						@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->liquidationSalary : null }} @endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label',['classEx' => 'font-bold']) 20 días x año de servicios: @endcomponent
						@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->twentyDaysPerYearOfServices : null }} @endcomponent
					</div>
				@endif
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima de antigüedad: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->seniorityPremium : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Vacaciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->holidays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Indemnización exenta: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->exemptCompensation : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Indemnización gravada: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->taxedCompensation : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Aguinaldo exento: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->exemptBonus : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Aguinaldo gravable: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->taxableBonus : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima vacacional exenta: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->holidayPremiumExempt : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima vacacional gravada: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->holidayPremiumTaxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Otras percepciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->otherPerception : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->totalPerceptions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') RETENCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) ISR: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->isr : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Pensión Alimenticia: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->alimony : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Otras retenciones: @endcomponent
					@component('components.labels.label'){{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->other_retention : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->totalRetentions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') SUELDO NETO @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo neto: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->liquidation()->exists() ? $nomina_employees->liquidation->first()->netIncome : null }} @endcomponent
				</div>
			@endcomponent
		@break
		@case('005') {{-- PRIMA VACACIONAL --}}
			@component('components.labels.title-divisor') DATOS @endcomponent
			<div class="resultbank table-responsive" @if($nomina_employees->vacationPremium->first()->idpaymentMethod == 1) block @else hidden @endif>
				@php
					$body		= [];
					$modelBody	= []; 
					$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

					foreach (App\EmployeeAccount::whereIn('id',$nomina_employees->vacationPremium->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())->get() as $b)
					{
						$body = 
						[
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
						
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent 
			</div>
			@component('components.labels.subtitle') INFORMACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium->first()->paymentMethod->method }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->sd : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D.I. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->sdi : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días trabajados: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->workedDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días para vacaciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->holidaysDays : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') PERCEPCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Vacaciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->holidays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima vacacional exenta: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->exemptHolidayPremium : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Prima vacacional gravada: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->holidayPremiumTaxed : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->totalPerceptions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') RETENCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) ISR: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->isr : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Pensión Alimenticia: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->alimony : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->totalTaxes : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') SUELDO NETO @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo neto: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->vacationPremium()->exists() ? $nomina_employees->vacationPremium->first()->netIncome : null }} @endcomponent
				</div>
			@endcomponent
		@break
		@case('006') {{-- REPARTO DE UTILIDADES --}}
			@component('components.labels.title-divisor') DATOS @endcomponent
			<div class="resultbank table-responsive" @if($nomina_employees->profitSharing->first()->idpaymentMethod == 1) block @else hidden @endif>
				@php
					$body		= [];
					$modelBody	= []; 
					$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

					foreach (App\EmployeeAccount::whereIn('id',$nomina_employees->profitSharing->first()->nominaEmployeeAccounts->pluck('idEmployeeAccounts')->toArray())->get() as $b)
					{
						$body = 
						[
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
						
						$modelBody[] = $body;
					}
				@endphp
				@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent 
			</div>
			@component('components.labels.subtitle') INFORMACIÓN @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing->first()->paymentMethod->method }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->sd : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) S.D.I. @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->sdi : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Días trabajados: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->workedDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->totalSalary : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) PTU por días: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->ptuForDays : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) PTU por sueldo: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->ptuForSalary : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) PTU total: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->totalPtu : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') PERCEPCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) PTU exenta: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->exemptPtu : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) PTU gravada: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->taxedPtu : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total percepciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->totalPerceptions : null }} @endcomponent
				</div>
			@endcomponent
			@component('components.labels.subtitle') RETENCIONES @endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Retenciones de ISR: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->isrRetentions : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Pensión Alimenticia: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->alimony : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Total retenciones: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->totalRetentions : null }} @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label',['classEx' => 'font-bold']) Sueldo neto: @endcomponent
					@component('components.labels.label') {{ $nomina_employees->profitSharing()->exists() ? $nomina_employees->profitSharing->first()->netIncome : null }} @endcomponent
				</div>
			@endcomponent
		@break
	@endswitch
@else
	@component('components.labels.title-divisor') DATOS @endcomponent
	@if($nomina_employees->nominasEmployeeNF->first()->idpaymentMethod == 1)
		<div class="resultbank table-responsive">
			@php
				$body		= [];
				$modelBody	= []; 
				$modelHead 	= ["Alias", "Banco", "CLABE", "Cuenta", "Tarjeta", "Sucursal"];

				$body = 
				[
					[
						"content" =>
						[
							[ "label" => $nomina_employees->nominasEmployeeNF->first()->employeeAccounts()->exists() ? $nomina_employees->nominasEmployeeNF->first()->employeeAccounts->first()->alias : '---']
						]
					],
					[
						"content" =>
						[
							[ "label" => $nomina_employees->nominasEmployeeNF->first()->employeeAccounts()->exists() ? $nomina_employees->nominasEmployeeNF->first()->employeeAccounts->first()->bank->description : '---']
						]
					],
					[
						"content" =>
						[
							[ "label" => $nomina_employees->nominasEmployeeNF->first()->employeeAccounts()->exists() ? $nomina_employees->nominasEmployeeNF->first()->employeeAccounts->first()->account : '---']
						]
					],
					[
						"content" =>
						[
							[ "label" => $nomina_employees->nominasEmployeeNF->first()->employeeAccounts()->exists() ? $nomina_employees->nominasEmployeeNF->first()->employeeAccounts->first()->clabe : '---']
						]
					],
					[
						"content" =>
						[
							[ "label" => $nomina_employees->nominasEmployeeNF->first()->employeeAccounts()->exists() ? $nomina_employees->nominasEmployeeNF->first()->employeeAccounts->first()->cardNumber : '---']
						]
					]
				];
				
				$modelBody[] = $body;
			@endphp
			@component('components.tables.alwaysVisibleTable', [
					"modelBody" 	=> $modelBody,
					"modelHead" 	=> $modelHead,
				])
				@slot('classExBody')
					request-validate
				@endslot
			@endcomponent 
		</div>
	@endif
	@component('components.labels.subtitle') DATOS DE COMPLEMENTO @endcomponent
	@component('components.containers.container-form')
		<div class="col-span-2">
			@component('components.labels.label',['classEx' => 'font-bold']) Forma de Pago: @endcomponent
			@component('components.labels.label') {{ $nomina_employees->nominasEmployeeNF->first()->paymentMethod->method }} @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label',['classEx' => 'font-bold']) Referencia @endcomponent
			@component('components.labels.label') {{ $nomina_employees->nominasEmployeeNF()->exists() ? $nomina_employees->nominasEmployeeNF->first()->reference : null }} @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label',['classEx' => 'font-bold']) Razón de pago @endcomponent
			@component('components.labels.label') {{ $nomina_employees->nominasEmployeeNF()->exists() ? $nomina_employees->nominasEmployeeNF->first()->reasonAmount : null }} @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label',['classEx' => 'font-bold']) Complemento + Extras - Descuentos @endcomponent
			@component('components.labels.label') {{ $nomina_employees->nominasEmployeeNF()->exists() ? $nomina_employees->nominasEmployeeNF->first()->amount : null }} @endcomponent
		</div>
		<div class="col-span-2">
			@component('components.labels.label',['classEx' => 'font-bold']) Sueldo total: @endcomponent
			@component('components.labels.label') {{ $nomina_employees->nominasEmployeeNF()->exists() && $nomina_employees->nominasEmployeeNF->first()->netIncome > 0 ? $nomina_employees->nominasEmployeeNF->first()->netIncome : $nomina_employees->nominasEmployeeNF->first()->amount }} @endcomponent
		</div>
	@endcomponent

	@if($nomina_employees->nominasEmployeeNF()->exists() && $nomina_employees->nominasEmployeeNF->first()->extras()->exists())
		@component('components.labels.subtitle') EXTRAS @endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value"	=> "Monto"],
					["value"	=> "Descripción"]
				]
			];

			foreach($nomina_employees->nominasEmployeeNF->first()->extras as $extra)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $extra->amount
						]
					],
					[
						"content" =>
						[
							"label" => $extra->reason
						]
					]
				];

				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
	@endif

	@if($nomina_employees->nominasEmployeeNF()->exists() && $nomina_employees->nominasEmployeeNF->first()->discounts()->exists())
		@component('components.labels.subtitle') DESCUENTOS @endcomponent
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				["value"	=> "Monto"],
				["value"	=> "Descripción"]
			];

			foreach($nomina_employees->nominasEmployeeNF->first()->discounts as $discount)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $discount->amount
						]
					],
					[
						"content" =>
						[
							"label" => $discount->reason
						]
					]
				];

				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",
		[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
	@endif
@endif