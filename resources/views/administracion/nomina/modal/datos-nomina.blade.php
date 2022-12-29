@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('nomina.data-nomina.update')."\""])
	@component('components.labels.title-divisor') DETALLES DE EMPLEADO @endcomponent
	@component('components.labels.subtitle') Información @endcomponent
	@component('components.containers.container-form')
		@switch($type_payroll)
			@case('001')			
				<div class="col-span-2">
					@component('components.labels.label') Desde: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							datepicker
						@endslot
						@slot('attributeEx')
							type="text" name="from_date_edit" placeholder="Ingrese la fecha" value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->from_date)->format('d-m-Y') }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Hasta: @endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							datepicker
						@endslot
						@slot('attributeEx')
							type="text" name="to_date_edit" placeholder="Ingrese la fecha" value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->to_date)->format('d-m-Y') }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Periodicidad: @endcomponent
					@php
						$optionPeriodicity = [];
						foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
						{
							if($nominaemployee->idCatPeriodicity == $per->c_periodicity)
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
							}
							else
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionPeriodicity])
						@slot('attributeEx')
							name="periodicity_edit" data-validation="required" multiple
						@endslot
						@slot('classEx')
							periodicity js-periodicity
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Faltas: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="absence_edit" placeholder="Ingrese las faltas" value="{{ $nominaemployee->absence }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Préstamo (Percepción): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="loan_perception_edit" placeholder="Ingrese el préstamo" value="{{ $nominaemployee->loan_perception }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Préstamo (Retención): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="loan_retention_edit" placeholder="Ingrese el préstamo" value="{{ $nominaemployee->loan_retention }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Horas extra: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="extra_hours_edit" placeholder="Ingrese las horas extras" value="{{ $nominaemployee->extra_hours }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Días festivos: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="holidays_edit" placeholder="Ingrese los días festivos" value="{{ $nominaemployee->holidays }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Domingos trabajados: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="sundays_edit" placeholder="Ingrese los domingos trabajados" value="{{ $nominaemployee->sundays }}"
						@endslot
					@endcomponent
				</div>
			@break
			@case('002')
				<div class="col-span-2">
					@component('components.labels.label') Días para aguinaldo (Percepción): @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="day_bonus_edit" placeholder="Ingrese los días para aguinaldo" value="{{ $nominaemployee->day_bonus }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Sueldo Neto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="netIncome_edit" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->total }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Periodicidad: @endcomponent
					@php
						$optionPeriodicity = [];
						foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
						{
							if($nominaemployee->idCatPeriodicity == $per->c_periodicity)
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
							}
							else
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionPeriodicity])
						@slot('attributeEx')
							name="periodicity_edit" data-validation="required" multiple
						@endslot
						@slot('classEx')
							periodicity js-periodicity
						@endslot
					@endcomponent
				</div>
			@break
			@case('003')
				@case('004')
					<div class="col-span-2">
						@component('components.labels.label') Sueldo Neto: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="netIncome_edit" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->total }}"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Periodicidad: @endcomponent
						@php
							$optionPeriodicity = [];
							foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
							{
								if($nominaemployee->idCatPeriodicity == $per->c_periodicity)
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
								}
								else
								{
									$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionPeriodicity])
							@slot('attributeEx')
								name="periodicity_edit" data-validation="required" multiple
							@endslot
							@slot('classEx')
								periodicity js-periodicity
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Fecha de baja: @endcomponent
						@component('components.inputs.input-text')
							@slot('classEx')
								datepicker
							@endslot
							@slot('attributeEx')
								type="text" name="down_date_edit" placeholder="Ingrese la fecha" value="{{ Carbon\Carbon::createFromFormat('Y-m-d',$nominaemployee->down_date)->format('d-m-Y') }}"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Días trabajados: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="worked_days_edit" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->worked_days }}"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Otras percepciones: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="otherPerception_edit" placeholder="Ingrese otras percepciones" value="{{ $nominaemployee->other_perception }}"
							@endslot
						@endcomponent
					</div>
			@break
			@case('005')
				<div class="col-span-2">
					@component('components.labels.label') Sueldo Neto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="netIncome_edit" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->total }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Periodicidad: @endcomponent
					@php
						$optionPeriodicity = [];
						foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
						{
							if($nominaemployee->idCatPeriodicity == $per->c_periodicity)
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
							}
							else
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionPeriodicity])
						@slot('attributeEx')
							name="periodicity_edit" data-validation="required" multiple
						@endslot
						@slot('classEx')
							periodicity js-periodicity
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Días trabajados: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="worked_days_edit" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->worked_days }}"
						@endslot
					@endcomponent
				</div>
			@break
			@case('006')
				<div class="col-span-2">
					@component('components.labels.label') Sueldo Neto: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="netIncome_edit" placeholder="Ingrese el sueldo neto" value="{{ $nominaemployee->total }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Periodicidad: @endcomponent
					@php
						$optionPeriodicity = [];
						foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
						{
							if($nominaemployee->idCatPeriodicity == $per->c_periodicity)
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description, "selected" => "selected"];
							}
							else
							{
								$optionPeriodicity[] = ["value" => $per->c_periodicity, "description" => $per->description];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionPeriodicity])
						@slot('attributeEx')
							name="periodicity_edit" data-validation="required" multiple
						@endslot
						@slot('classEx')
							periodicity js-periodicity
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Días trabajados: @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="text" name="worked_days_edit" placeholder="Ingrese los días trabajados" value="{{ $nominaemployee->worked_days }}"
						@endslot
					@endcomponent
				</div>
			@break		
		@endswitch
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idemployee" value="{{ $nominaemployee->idrealEmployee }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idworkingData" value="{{ $nominaemployee->idworkingData }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="idnominaEmployee" value="{{ $nominaemployee->idnominaEmployee }}"
		@endslot
	@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden" name="type_payroll" value="{{ $type_payroll }}"
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
				type="submit" name="senddata"
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
@endcomponent

<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script type="text/javascript"> 
	$(document).ready(function()
	{
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-periodicity",
					"placeholder"				=> "Seleccione la periocidad",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$('input[name="day_bonus_edit"]').numeric({ negative:false});
		$('input[name="liquidation_fullYears"]').numeric({ negative:false});
		$('input[name="netIncome_edit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="worked_days_edit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="otherPerception_edit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="liquidation_sd"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="liquidation_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="liquidation_holidayDays"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="absence_edit"]').numeric({ decimal: false, negative:false});
		$('input[name="loan_perception_edit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="loan_retention_edit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
		$('input[name="extra_hours_edit"]').numeric({ decimal: false, negative:false});
		$('input[name="holidays_edit"]').numeric({ decimal: false, negative:false});
		$('input[name="sundays_edit"]').numeric({ decimal: false, negative:false});
	});
</script>
