@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "method=\"POST\" action=\"".route('nomina.nomina-calculator-excel')."\" id=\"container-alta\""])
		@if(isset($prenomina))
			@component("components.inputs.input-text")
				@slot("attributeEx")
					type="hidden" name="prenom_id" value="{{$prenomina->idprenomina}}"
				@endslot
			@endcomponent
		@endif
		@component("components.labels.title-divisor") DATOS DE LA SOLICITUD @endcomponent
		@ContainerForm()
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("classEx")
						removeselect
					@endslot
					@slot("attributeEx")
						type="text" name="title" placeholder="Ingrese un título" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Tipo de nómina: @endcomponent
				@php
					$options = collect();
					foreach(App\CatTypePayroll::orderName()->get() as $t)
					{
						$options = $options->concat([["value" => $t->id, "description" => $t->description]]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx")
						js-typepayroll removeselect
					@endslot
					@slot("attributeEx")
						title="Tipo de nómina" name="type_payroll" data-validation="required" multiple="multiple"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 hidden" id="data_salary">
				@component("components.labels.label") Periodicidad: @endcomponent
				@php
					$options = collect();
					foreach(App\CatPeriodicity::whereIn('c_periodicity',['02','04','05'])->get() as $per)
					{
						$options = $options->concat([["value" => $per->c_periodicity, "description" => $per->description]]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $options])
					@slot("classEx")
						js-periodicity removeselect
					@endslot
					@slot("attributeEx")
						name="periodicity" data-validation="required"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 hidden data_salary">
				@component("components.labels.label") Fecha: @endcomponent
				@php
					$inputs = 
					[
						[
							"input_classEx"		=> "datepicker remove",
							"input_attributeEx" => "type=\"text\" name=\"from_date\" data-validation=\"required\" placeholder=\"Desde\" readonly=\"readonly\""
						],
						[
							"input_classEx"		=> "datepicker remove",
							"input_attributeEx" => "type=\"text\" name=\"to_date\" data-validation=\"required\" placeholder=\"Hasta\" readonly=\"readonly\""
						]
					];
				@endphp
				@component("components.inputs.range-input", ["inputs" => $inputs]) @endcomponent
			</div>
			<span class="hidden" id="data_bonus"></span>
			<span class="hidden" id="data_liquidation"></span>
			<span class="hidden" id="data_vacation_premium"></span>
			<div class="col-span-2 hidden" id="data_profit_sharing">				
				@component("components.labels.label") PTU: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="ptu_to_pay" data-validation="required" placeholder="Ingrese el PTU"
					@endslot
				@endcomponent
			</div>
		@endContainerForm
		@component("components.labels.title-divisor") SELECCIONE UNA OPCIÓN @endcomponent
		<div class="flex row justify-center space-x-2 my-4">  
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						name="type_employee" 
						id="exist" 
						value="1"
					@endslot
						Existente
				@endcomponent
			</div>
			<div>
				@component('components.buttons.button-approval')
					@slot('attributeEx')
						name="type_employee" 
						id="new" 
						value="2"
					@endslot
						Nuevo
				@endcomponent
			</div>
		</div>
		<div id="employee_exists" class="hidden">
			@component('components.inputs.input-search') 
				Empleado:
				@slot('attributeExInput')
					type="text" title="Escriba aquí" name="searchEmployee" id="input-search" placeholder="Ingrese un nombre"
				@endslot
				@slot('attributeExButton')
					type="button" id="search-btn"
				@endslot
			@endcomponent
			<div id="result"></div>
		</div>
		<div id="employee_new" class="hidden">
			<div id="form_new_salary" class="hidden">
				@component("components.labels.title-divisor") DATOS DEL SUELDO @endcomponent
				@ContainerForm(["classEx" => "table"])
					<div class="col-span-2">
						@component("components.labels.label") Nombre: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								idemployee-table-prenomina
							@endslot
							@slot("attributeEx")
								type="hidden" value="X"
							@endslot
						@endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-fullname
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese un nombre"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Admisión: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-salary-admission-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Baja (Opcional): @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-salary-down-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") S.D.I: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-sdi
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el S.D.I"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Faltas: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-absence
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese las faltas" value="0"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Préstamo (Percepción): @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-loan-perception
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el préstamo de percepción" value="0"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Préstamo (Retención): @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-loan-retention
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el préstamo de retención" value="0"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Bono Asistencia (%): @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-bono-assistance
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el bono de asistencia" value="0"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Bono de Puntualidad (%): @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-bono-puntuality
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el bono de puntualidad" value="0"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Registro Patronal: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-employer-register
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese un registro patronal" data-validation="server" name="validation_employer_register" data-validation-url="{{ route('nomina.nomina-calculator.employee_register') }}"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento Infonavit: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "VSM (Veces Salario Mínimo)", "2" => "Cuota fija", "3" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-salary-infonavit-discount-type js-infonavit-discount removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Descuento Infonavit: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-infonavit-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el descuento"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento Pensión Alimenticia: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "Monto", "2" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-salary-alimony-discount-type removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Pensión Alimenticia: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-alimony-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la pensión alimenticia"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fonacot: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-fonacot
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el fonacot"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Sueldo Neto: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-salary-net-income
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el sueldo"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-salary
							@endslot
							@slot("attributeEx")
								type="button"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				@endContainerForm
			</div>
			<div id="form_new_bonus" class="hidden">
				@component("components.labels.title-divisor") DATOS DE AGUINALDO @endcomponent
				@ContainerForm(["classEx" => "table"])
					<div class="col-span-2">
						@component("components.labels.label") Nombre: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								idemployee-table-prenomina
							@endslot
							@slot("attributeEx")
								type="hidden" value="X"
							@endslot
						@endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-bonus-fullname
							@endslot
							@slot("attributeEx")
								type="text" data-validation="required" placeholder="Ingrese un nombre"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Admisión: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-bonus-admission-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha" data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Días para aguinaldo: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-bonus-day-bonus
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese los días para aguinaldo"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") S.D.I: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-bonus-sdi
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el S.D.I." data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento de Pensión Alimenticia: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "Monto", "2" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-bonus-alimony-discount-type removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Pensión Alimenticia: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-bonus-alimony-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la pensión alimenticia"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-bonus
							@endslot
							@slot("attributeEx")
								type="button"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				@endContainerForm
			</div>
			<div id="form_new_liquidation" class="hidden">
				@component("components.labels.title-divisor") DATOS @endcomponent
				@ContainerForm(["classEx" => "table"])
					<div class="col-span-2">
						@component("components.labels.label") Nombre: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								idemployee-table-prenomina
							@endslot
							@slot("attributeEx")
								type="hidden" value="X"
							@endslot
						@endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-fullname
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese un nombre"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Admisión: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-liquidation-admission-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Baja: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-liquidation-down-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Días Trabajados: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-worked-days
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese los días trabajados"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") S.D.I: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-sdi
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el S.D.I."
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Otra percepción: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-other-perception
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la percepción"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Otra retención: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-other-retention
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la retención"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento Pensión Alimenticia: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "Monto", "2" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-liquidation-alimony-discount-type removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Pensión Alimenticia: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-liquidation-alimony-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la pensión"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-liquidation
							@endslot
							@slot("attributeEx")
								type="button"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				@endContainerForm
			</div>
			<div id="form_new_vacation_premium" class="hidden">
				@component("components.labels.title-divisor") DATOS PRIMA VACACIONAL @endcomponent
				@ContainerForm(["classEx" => "table"])
					<div class="col-span-2">
						@component("components.labels.label") Nombre: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								idemployee-table-prenomina
							@endslot
							@slot("attributeEx")
								type="hidden" value="X"
							@endslot
						@endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-vacation-premium-fullname
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese un nombre"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Admisión: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-vacation-premium-admission-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Días Trabajados: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-vacation-premium-worked-days
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese los dias trabajados"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") S.D.I: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-vacation-premium-sdi
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el S.D.I."
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento Pensión Alimenticia: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "Monto", "2" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-vacation-premium-alimony-discount-type removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Pensión Alimenticia: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-vacation-premium-alimony-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la pensión"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-vacation-premium
							@endslot
							@slot("attributeEx")
								type="button"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				@endContainerForm
			</div>
			<div id="form_new_profit_sharing" class="hidden">
				@component("components.labels.title-divisor") DATOS REPARTO DE UTILIDADES @endcomponent
				@ContainerForm(["classEx" => "table"])
					<div class="col-span-2">
						@component("components.labels.label") Nombre: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								idemployee-table-prenomina
							@endslot
							@slot("attributeEx")
								type="hidden" value="X"
							@endslot
						@endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-profit-sharing-fullname
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese un nombre"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Fecha de Admisión: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								datepicker new-profit-sharing-admission-date
							@endslot
							@slot("attributeEx")
								type="text" readonly="readonly" placeholder="Ingrese la fecha"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Días Trabajados: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-profit-sharing-worked-days
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese los dias trabajados"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") S.D.I: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-profit-sharing-sdi
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese el S.D.I."
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Tipo de Descuento Pensión Alimenticia: @endcomponent
						@php
							$options = collect();
							foreach(["1" => "Monto", "2" => "Porcentaje"] as $key => $value)
							{
								$options = $options->concat([["value" => $key, "description" => $value]]);
							}
						@endphp
						@component("components.inputs.select", ["options" => $options])
							@slot("classEx")
								new-profit-sharing-alimony-discount-type removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component("components.labels.label") Pensión Alimenticia: @endcomponent
						@component("components.inputs.input-text")
							@slot("classEx")
								new-profit-sharing-alimony-discount
							@endslot
							@slot("attributeEx")
								type="text" placeholder="Ingrese la pensión"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-profit-sharing
							@endslot
							@slot("attributeEx")
								type="button"
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				@endContainerForm
			</div>
		</div>
		@component('components.labels.title-divisor') Datos de Empleado @endcomponent
		<div id="thead-type" class="content-table"></div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [ "variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="enviar"
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ "variant" => "reset"])
				@slot('attributeEx')
					type="reset" name="borra"
				@endslot
				@slot('classEx')
					btn-delete-form
				@endslot
					Borrar campos
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{asset('js/jquery.mask.js')}}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				modules : 'file security',
				form	: '#container-alta',
				onError	: function($form)
				{
					swal('', '{{ Lang::get("messages.form_error") }}', 'error');
				},
				onSuccess : function($form)
				{
					if($('.request-validate').length>0)
					{
						employees = $('#body-payroll .tr-payroll').length;
						if(employees>0)
						{
							c = 0;
							$("[name='alimonyDiscount[]']").each(function()
							{
								sdi				= $(this).parents('.tr-payroll').find('[name="sdi[]"]');
								alimonyDiscount = parseFloat($(this).val());
								net_income		= parseFloat($(this).parents('.tr-payroll').find('[name="net_income[]"]').val());
								if(alimonyDiscount > net_income)
								{
									$(this).removeClass("valid").addClass("error");
									swal("", "Las pensiones no pueden superar el sueldo neto", "warning");
									c++;
								}
								else
								{
									$(this).removeClass("error").addClass("valid");
								}
								if(sdi.val() <= 0)
								{
									sdi.removeClass("valid").addClass("error");
									swal("", "S.D.I debe ser mayor a cero.", "warning");
									c++;
								}
								else
								{
									sdi.removeClass("error").addClass("valid");
								}
							});
							$('.admission_date').each(function()
							{
								moment.defaultFormat= "DD.MM.YYYY";
								selector 			= $(this).parents('.tr-payroll');
								admission_dateV		= moment(selector.find('.down_date').val(),moment.defaultFormat);
								down_dateV			= moment(selector.find('.admission_date').val(),moment.defaultFormat);
								down_date 			= selector.find('.down_date').val();
								admission_date		= $(this).val();
								var_down_date		= down_date,moment.defaultFormat;
								var_admission_date	= admission_date,moment.defaultFormat;

								if(var_down_date < var_admission_date)
								{
									swal('', 'La fecha de baja debe ser mayor a la fecha de admisión.','error');
									selector.find(".down_date").addClass("error").removeClass('valid');
									$(this).addClass("error").removeClass('valid');
									c++;
								}
								else
								{
									selector.find(".down_date").removeClass("error").addClass("valid");
									$(this).removeClass("error").addClass("valid");
								}
								if((selector.find('.absence').val() > admission_dateV.diff(down_dateV, 'days')))
								{
									swal('', 'Las faltas deben coincidir con las fechas ingresadas.','error');
									selector.find('.absence').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find(".absence").removeClass("error").addClass('valid');
								}
								if((selector.find('.worked_days').val() > admission_dateV.diff(down_dateV, 'days')) || selector.find('.worked_days').val() == 0)
								{
									if(selector.find('.worked_days').val() == 0)
									{
										message = 'Los días trabajados deben ser mayor a cero.';
									}
									else
									{
										message = 'Los días trabajados deben coincidir con las fechas ingresadas.';
									}
									swal('', message,'error');
									selector.find('.worked_days').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find(".worked_days").removeClass("error").addClass('valid');
								}
								if(selector.find('[name="employer_register[]"]').hasClass('error') && selector.find('[name="employer_register[]"]').hasClass('valid') == false)
								{
									swal('', 'Registro patronal incorrecto.','error');
									selector.find('[name="employer_register[]"]').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find('[name="employer_register[]"]').removeClass("error").addClass('valid');
								}
								if($('[name="day_bonus[]"]').val() > 365 || $('[name="day_bonus[]"]').val() == 0)
								{
									swal('', 'Los días para aguinaldo no pueden ser cero, ni mayor a 365 días.','error');
									selector.find('[name="day_bonus[]"]').addClass('error').removeClass('valid');
									c++;
								}
								if(selector.find('.net_income').val() == 0)
								{
									swal('', 'El sueldo neto debe ser mayor a cero.','error');
									selector.find('.net_income').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find(".net_income").removeClass("error").addClass('valid');
								}
								if(selector.find('[name="bono_assistance[]"]').val() > 100)
								{
									selector.find('[name="bono_assistance[]"]').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find('[name="bono_assistance[]"]').removeClass('error').addClass('valid');
								}
								if(selector.find('[name="bono_puntuality[]"]').val() > 100)
								{
									selector.find('[name="bono_puntuality[]"]').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find('[name="bono_puntuality[]"]').removeClass('error').addClass('valid');
								}
								if((selector.find('[name="infonavitDiscount[]"]').val() > 100) && $('[name="infonavitDiscountType[]"]').val() == 3)
								{
									swal('', 'El descuento Infonavit no debe exceder el 100%','error');
									selector.find('[name="infonavitDiscount[]"]').val('').addClass('error').removeClass('valid');
									c++;
								}
								else
								{
									selector.find('[name="infonavitDiscount[]"]').removeClass("error").addClass('valid');
								}
								if($('[name="type_payroll"]').val() == '006' && $('[name="ptu_to_pay"]').val() <= 0)
								{
									swal("", "PTU debe ser mayor a cero.", "warning");
									$('[name="ptu_to_pay"]').addClass('error').removeClass('valid');
									c++;
								}
							});
							if(c > 0)
							{
								return false;
							}
							else
							{
								swal("Cargando",{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									timer: 1000,
								});
								return true;
							}
						}
						else
						{
							swal('', 'Debe agregar al menos un empleado', 'error');
							return false;
						}
					}
					else
					{
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							timer: 1000,
							button: false,
						});
						return true;
					}
				}
			});
			$('input[name="ptu_to_pay"]').numeric({ negative: false });
			$('.new-bonus-day-bonus').numeric({ negative: false });
			$('.new-liquidation-worked-days').numeric({ negative: false });
			$('.new-vacation-premium-worked-days').numeric({negative: false });
			$('.new-profit-sharing-worked-days').numeric({negative: false });
			$('.new-salary-absence').numeric({negative: false });
			$('.new-bonus-sdi').numeric({ altDecimal: ".", negative: false });
			$('.new-bonus-alimony-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-liquidation-sdi').numeric({ altDecimal: ".", negative: false });
			$('.new-liquidation-other-perception').numeric({ altDecimal: ".", negative: false });
			$('.new-liquidation-alimony-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-vacation-premium-sdi').numeric({ altDecimal: ".", negative: false });
			$('.new-vacation-premium-alimony-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-profit-sharing-sdi').numeric({ altDecimal: ".", negative: false });
			$('.new-profit-sharing-alimony-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-sdi').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-loan-perception').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-bono-assistance').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-loan-retention').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-fonacot').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-bono-assistance').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-infonavit-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-alimony-discount').numeric({ altDecimal: ".", negative: false });
			$('.new-salary-net-income').numeric({ altDecimal: ".", negative: false });
			$('input[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
			$('input[name="alimonyDiscount"]').numeric({ altDecimal: ".", negative: false });
			$('input[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
			@php
				$selects = collect([
					[
						"identificator"				=> '.js-typepayroll',
						"placeholder"				=> "Seleccione el tipo de nómina",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			$(function() 
			{
				$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
			});
			$(document).on('click','input[name="type_employee"]',function()
			{
				if($(this).val() == 1)
				{
					$('#employee_exists').stop(true,true).fadeIn().show();
					$('#employee_new').stop(true,true).fadeOut().hide();
				}
				else
				{
					type_payroll = $('select[name="type_payroll"] option:selected').val();
					if (type_payroll != '' && type_payroll != undefined) 
					{
						if (type_payroll == '001') 
						{
							$('#employee_new').stop(true,true).fadeIn().show();
							$('#form_new_salary').stop(true,true).fadeIn().show();
							$('#form_new_bonus,#form_new_liquidation,#form_new_vacation_premium,#form_new_profit_sharing').stop(true,true).fadeOut().hide();
							@php
								$selects = collect([
									[
										"identificator"				=> '.new-salary-infonavit-discount-type, .new-salary-alimony-discount-type',
										"placeholder"				=> "Seleccione el tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						}
						if (type_payroll == '002') 
						{
							$('#employee_new').stop(true,true).fadeIn().show();
							$('#form_new_bonus').stop(true,true).fadeIn().show();
							$('#form_new_salary,#form_new_liquidation,#form_new_vacation_premium,#form_new_profit_sharing').stop(true,true).fadeOut().hide();
							@php
								$selects = collect([
									[
										"identificator"				=> '.new-bonus-alimony-discount-type',
										"placeholder"				=> "Seleccione un tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						}
						if (type_payroll == '003' || type_payroll == '004') 
						{
							$('#employee_new').stop(true,true).fadeIn().show();
							$('#form_new_liquidation').stop(true,true).fadeIn().show();
							$('#form_new_salary,#form_new_bonus,#form_new_vacation_premium,#form_new_profit_sharing').stop(true,true).fadeOut().hide();	
							@php
								$selects = collect([
									[
										"identificator"				=> '.new-liquidation-alimony-discount-type',
										"placeholder"				=> "Seleccione un tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent
						}
						if (type_payroll == '005') 
						{
							$('#employee_new').stop(true,true).fadeIn().show();
							$('#form_new_vacation_premium').stop(true,true).fadeIn().show();
							$('#form_new_salary,#form_new_bonus,#form_new_liquidation,#form_new_profit_sharing').stop(true,true).fadeOut().hide();
							@php
								$selects = collect([
									[
										"identificator"				=> '.new-vacation-premium-alimony-discount-type',
										"placeholder"				=> "Seleccione un tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent	
						}
						if (type_payroll == '006') 
						{
							$('#employee_new').stop(true,true).fadeIn().show();
							$('#form_new_profit_sharing').stop(true,true).fadeIn().show();
							$('#form_new_salary,#form_new_bonus,#form_new_liquidation,#form_new_vacation_premium').stop(true,true).fadeOut().hide();
							@php
								$selects = collect([
									[
										"identificator"				=> '.new-profit-sharing-alimony-discount-type',
										"placeholder"				=> "Seleccione un tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent	
						}
					}
					else
					{
						$(this).prop('checked',false);
						swal('','Seleccione un tipo de nómina','error');
					}
					$('#employee_exists').stop(true,true).fadeOut().hide();
				}
			})
			.on('submit','form',function()
			{
				setTimeout(() =>
				{
					$('[name="enviar"]').removeAttr('disabled');
				}, 5000);
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title       : "Limpiar formulario",
					text        : "¿Confirma que desea limpiar el formulario?",
					icon        : "warning",
					buttons     : ["Cancelar","OK"],
					dangerMode  : true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body-payroll').html('');
						$('.removeselect').val(null).trigger('change');
						$('.result').hide();
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','#search-btn', function()
			{
				swal(
				{
					icon	: '{{ asset(getenv('LOADING_IMG')) }}',
					button	: false,
				});
				text = $('input[name="searchEmployee"]').val();
				idrealEmployee = [];
				$('.idemployee-table-prenomina').each(function()
				{
					idrealEmployee.push(Number($(this).val()));
				});
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("nomina.prenomina-create.getemployee") }}',
					data	: {'search':text,'idrealEmployee':idrealEmployee},
					success	: function(data)
					{
						$('#result').html(data);
						$('#result').stop(true,true).fadeIn();
						swal.close();
					},
					error: function()
					{
						swal.close();
					}
				}); 
			})
			.on('focusout','[name="employer_register[]"]',function()
			{
				selector = $(this);
				$.ajax(
				{
					type 	: 'post',
					url 	: '{{ route("nomina.nomina-calculator.employee_register") }}',
					data 	: {'validation_employer_register':selector.val()},
					success : function(data)
					{
						if(data.valid == true)
						{
							selector.addClass('valid').removeClass('error');
						}
						else
						{
							selector.addClass('error').removeClass('valid');
						}
					}
				});
			})
			.on('click','#btn-search', function()
			{
				search(1)
			})
			.on('click','.paginate a', function(e)
			{
				e.preventDefault();
				href   = $(this).attr('href');
				url    = new URL(href);
				params = new URLSearchParams(url.search);
				page   = params.get('page');
				search(page)
			})
			.on('click','.add-user',function()
			{
				actioner		= $(this);
				employee		= $(this).parents('.tr_employee').find('.id-employee-table').val();
				type_payroll	= $('select[name="type_payroll"] option:selected').val();
				if (employee != undefined && employee != '' && type_payroll != '' && type_payroll != undefined) 
				{
					actioner.attr('disabled',true);
					$.ajax(
					{
						type 	: 'post',
						url 	: '{{ route("nomina.nomina-calculator-partial") }}',
						data 	:{'employee':employee,'type_payroll':type_payroll}, 
						success : function(data)
						{
							$('#body-payroll').append(data);
							$('#result').stop(true,true).slideUp();
							$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
							$('.day_bonus').numeric({ altDecimal: ".", negative: false });
							$('.sdi').numeric({ altDecimal: ".", negative: false });
							$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
							$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
							$('.datepicker').on("contextmenu",function(e)
							{
								return false;
							});
							actioner.attr('disabled',false);
							@php
								$selects = collect([
									[
										"identificator"				=> '.js-discount-type',
										"placeholder"				=> "Seleccione un tipo de descuento",
										"maximumSelectionLength"	=> "1"
									]
								]);
							@endphp
							@component('components.scripts.selects',["selects" => $selects]) @endcomponent	
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#body-payroll').html('');
							$('#result').hide();
						}
					}).then(()=>{
					});
				}	
				else
				{
					actioner.attr('disabled',false);
					swal('','Seleccione un tipo de nómina','error');
				}
			})
			.on('click','.btn-delete-tr',function()
			{
				$(this).parents('.tr-payroll').remove();
			})
			.on('change','select[name="type_payroll"]',function()
			{
				type_payroll	= $('select[name="type_payroll"] option:selected').val();
				thead			= '';
				if (type_payroll != undefined && type_payroll != '') 
				{
					if (type_payroll == '001') 
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Baja (Opcional)"],
									["value" => "S.D.I."],
									["value" => "Faltas"],
									["value" => "Préstamo (Percepción)"],
									["value" => "Préstamo (Retención)"],
									["value" => "Bono Puntualidad (%)"],
									["value" => "Bono Asistencia (%)"],
									["value" => "Registro patronal"],
									["value" => "Tipo descuento de Infonavit"],
									["value" => "Descuento Infonavit"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Fonacot"],
									["value" => "Sueldo Neto"], 
									["value" => "Acción"]
								]
							];
							$table = view('components.tables.table', [
								"modelHead"			=> $modelHead,
								"modelBody"			=> $modelBody,
								"attributeExBody"	=> "id=\"body-payroll\"",
								"classExBody"		=> "request-validate"
							])->render();
						@endphp
						bodySalary	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						thead		= $(bodySalary);
						$('#data_salary,.data_salary').show();
						$('#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
						@php
							$selects = collect([
								[
									"identificator"				=> '.js-periodicity',
									"placeholder"				=> "Seleccione la periocidad",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
					if (type_payroll == '002') 
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días para aguinaldo"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$table = view('components.tables.table', [
								"modelHead"			=> $modelHead,
								"modelBody"			=> $modelBody,
								"attributeExBody"	=> "id=\"body-payroll\"",
								"classExBody"		=> "request-validate"
							])->render();
						@endphp
						bodyBonus	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						thead		= $(bodyBonus);
						$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
					}
					if (type_payroll == '003' || type_payroll == '004')
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de baja"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Otra percepción"],
									["value" => "Otra retención"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$table = view('components.tables.table', [
								"modelHead"			=> $modelHead,
								"modelBody"			=> $modelBody,
								"attributeExBody"	=> "id=\"body-payroll\"",
								"classExBody"		=> "request-validate"
							])->render();
						@endphp
						bodyLiquidation	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						thead			= $(bodyLiquidation);
						$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
					}
					if (type_payroll == '005') 
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$table = view('components.tables.table', [
								"modelHead"			=> $modelHead,
								"modelBody"			=> $modelBody,
								"attributeExBody"	=> "id=\"body-payroll\"",
								"classExBody"		=> "request-validate"
							])->render();
						@endphp
						bodyPrima	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						thead		= $(bodyPrima);
						$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
					}
					if (type_payroll == '006') 
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$table = view('components.tables.table', [
								"modelHead"			=> $modelHead,
								"modelBody"			=> $modelBody,
								"attributeExBody"	=> "id=\"body-payroll\"",
								"classExBody"		=> "request-validate"
							])->render();
						@endphp
						bodyProfit	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						thead		= $(bodyProfit);
						$('#data_profit_sharing').show();
						$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium').hide();
					}
					$('#thead-type').html(thead);
				}
				else
				{
					$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
					$('input[name="type_employee"]').prop('checked',false);
					$('#employee_new,#employee_exists').stop(true,true).fadeOut().hide();
					$('#thead-type,#body-payroll').html(thead);
				}	
			})
			.on('change','.new-salary-infonavit-discount',function()
			{
				if($('.new-salary-infonavit-discount-type option:selected').val() == 3 && $(this).val() > 100)
				{
					swal("", "El descuento Infonavit no debe exceder el 100%", "warning");
					$(this).val('').addClass('error');
				}
			})
			.on('click','.add-salary',function()
			{
				full_name			= $('.new-salary-fullname').val();
				admission_date		= $('.new-salary-admission-date').val();
				down_date			= $('.new-salary-down-date').val();
				sdi					= $('.new-salary-sdi').val();
				absence				= $('.new-salary-absence').val();
				loan_perception		= $('.new-salary-loan-perception').val();
				loan_retention		= $('.new-salary-loan-retention').val();
				bono_puntuality		= $('.new-salary-bono-puntuality').val();
				bono_assistance		= $('.new-salary-bono-assistance').val();
				employer_register	= $('.new-salary-employer-register').val();
				infonavit_type_text	= $('.new-salary-infonavit-discount-type option:selected').text();
				infonavit_type_val	= $('.new-salary-infonavit-discount-type option:selected').val();
				infonavit_discount	= $('.new-salary-infonavit-discount').val();
				alimony_type_text	= $('.new-salary-alimony-discount-type option:selected').text();
				alimony_type_val	= $('.new-salary-alimony-discount-type option:selected').val();
				alimony_discount	= $('.new-salary-alimony-discount').val();
				fonacot				= $('.new-salary-fonacot').val();
				net_income			= $('.new-salary-net-income').val();
				moment.defaultFormat= "DD.MM.YYYY";
				var_down_date 		= down_date,moment.defaultFormat;
				var_admission_date	= admission_date,moment.defaultFormat;

				if(down_date != "")
				{
					if(var_down_date < var_admission_date)
					{
						$(this).parents("#form_new_salary").find(".new-salary-down-date").addClass("error");
						$(this).parents("#form_new_salary").find(".new-salary-admission-date").addClass("error");
						swal("", "La fecha de baja debe ser mayor a la fecha de admisión", "error");
						return false;
					}
					else
					{
						$(this).parents("#form_new_salary").find(".new-salary-down-date").removeClass("error");
						$(this).parents("#form_new_salary").find(".new-salary-admission-date").removeClass("error");
					}
				}
				admission_dateV	= moment($('.new-salary-admission-date').val(),moment.defaultFormat);
				down_dateV		= moment($('.new-salary-down-date').val(),moment.defaultFormat);			
				if((absence > down_dateV.diff(admission_dateV, 'days')))
				{
					swal('', 'Las faltas deben coincidir con las fechas ingresadas.','error');
					$('.new-salary-absence').addClass('error');
					return false;
				}
				else
				{
					$('.new-salary-absence').removeClass('error');
				}
				if($('.new-salary-bono-puntuality').val() > 100)
				{
					$('.new-salary-bono-puntuality').val('').addClass('error');
					swal('', 'El porcentaje Bono de Puntualidad no debe exceder el 100%.','error');
					return false;
				}
				if($('.new-salary-bono-assistance').val() > 100)
				{
					$('.new-salary-bono-assistance').val('').addClass('error');
					swal('', 'El porcentaje Bono de Asistencia no debe exceder el 100%.','error');
					return false;
				}
				if (full_name != '' && admission_date != '' && sdi != '' && employer_register !='' && net_income != '') 
				{
					if(sdi == 0)
					{
						$('.new-salary-sdi').removeClass("valid");
						$('.new-salary-sdi').addClass("error");
						swal("", "S.D.I debe ser mayor a cero", "warning");
					}
					else if(net_income <= 0)
					{
						$('.ew-salary-net-income').removeClass("valid");
						$('.ew-salary-net-income').addClass("error");
						swal("", "El sueldo Neto debe ser mayor a cero", "warning");
					}
					else if ($('.new-salary-employer-register').hasClass('error')) 
					{
						swal('Error','Verifique el Registro Patronal','error');
					}
					else
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Baja (Opcional)"],
									["value" => "S.D.I."],
									["value" => "Faltas"],
									["value" => "Préstamo (Percepción)"],
									["value" => "Préstamo (Retención)"],
									["value" => "Bono Puntualidad (%)"],
									["value" => "Bono Asistencia (%)"],
									["value" => "Registro patronal"],
									["value" => "Tipo descuento de Infonavit"],
									["value" => "Descuento Infonavit"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Fonacot"],
									["value" => "Sueldo Neto"], 
									["value" => "Acción"]
								]
							];
							$body = [ "classEx"	=> "tr-payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content" 	=>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"x\"",
											"classEx"		=> "idemployee-table-prenomina w-40"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"fullname[]\" data-validation=\"required\" placeholder=\"Ingrese un nombre\"",
											"classEx"		=> "fullname-table-prenomina w-40"
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\" name=\"admission_date[]\"",
										"classEx"		=> "datepicker admission_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" name=\"downDate[]\"",
										"classEx"		=> "datepicker down_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"sdi[]\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\"",
										"classEx"		=> "sdi w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\"",
										"classEx"		=> "absence w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"loan_perception[]\" placeholder=\"Ingrese la percepción\"",
										"classEx"		=> "loan_perception w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"loan_retention[]\" placeholder=\"Ingrese la retención\"",
										"classEx"		=> "loan_retention w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"bono_puntuality[]\" placeholder=\"Ingrese el bono\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"bono_assistance[]\" placeholder=\"Ingrese el bono\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"employer_register[]\" data-validation=\"required\" placeholder=\"Ingrese el registro patronal\""
									]
								],		
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" readonly=\"readonly\"",
											"classEx"		=> "class-infonavit-text w-40"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"infonavitDiscountType[]\""
										]
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"infonavitDiscount[]\" placeholder=\"Ingrese el descuento\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.select",
										"classEx"		=> "w-40",
										"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"fonacot[]\" placeholder=\"Ingrese fonacot\""
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"net_income[]\" data-validation=\"required\" placeholder=\"Ingrese el sueldo neto\"",
										"classEx"		=> "net_income"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[] = $body;
							$tableSalary = view('components.tables.table', [
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHeads"	=> true
							])->render();
						@endphp
						bodySalary	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableSalary)!!}';
						tr			= $(bodySalary);
						tr.find('.fullname-table-prenomina').val(full_name);
						tr.find('.admission_date').val(admission_date);
						tr.find('.down_date').val(down_date);
						tr.find('.sdi').val(sdi);
						tr.find('.absence').val(absence);
						tr.find('.loan_perception').val(loan_perception);
						tr.find('.loan_retention').val(loan_perception);
						tr.find('[name="bono_puntuality[]"]').val(bono_puntuality);
						tr.find('[name="bono_assistance[]"]').val(bono_assistance);
						tr.find('[name="employer_register[]"]').val(employer_register);
						tr.find('.class-infonavit-text').val(infonavit_type_text);
						tr.find('[name="infonavitDiscountType[]"]').val(infonavit_type_val);
						tr.find('[name="infonavitDiscount[]"]').val(infonavit_discount);
						tr.find("[name='alimonyDiscountType[]']").append($('<option value="1">Monto</option><option value="2">Porcentaje</option>'));
						tr.find("[name='alimonyDiscountType[]']").val(alimony_type_val);
						tr.find('[name="alimonyDiscount[]"]').val(alimony_discount);
						tr.find('[name="fonacot[]"]').val(fonacot);
						tr.find('.net_income').val(net_income);
						$('#body-payroll').append(tr);
						$('.new-salary-fullname,.new-salary-admission-date,.new-salary-down-date,.new-salary-sdi,.new-salary-absence,.new-salary-loan-perception,.new-salary-loan-retention,.new-salary-bono,.new-salary-employer-register,.new-salary-infonavit-discount,.new-salary-fonacot,.new-salary-net-income,.new-salary-alimony-discount').val(null);
						$('.new-salary-infonavit-discount-type,.new-salary-alimony-discount-type').trigger('change').val(null);
						$('#form_new_salary').stop(true,true).fadeOut().hide();
						$('input[name="type_employee"]').prop('checked',false);
						$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
						$('.day_bonus').numeric({ altDecimal: ".", negative: false });
						$('.sdi').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
						$('.datepicker').on("contextmenu",function(e)
						{
							return false;
						});
						@php
							$selects = collect([
								[
									"identificator"				=> '[name="alimonyDiscountType[]"]',
									"placeholder"				=> "Seleccione un tipo de descuento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				}
				else
				{
					$('.new-salary-fullname').addClass("error");
					$('.new-salary-admission-date').addClass("error");
					$('.new-salary-sdi').addClass("error");
					$('.new-salary-employer-register').addClass("error");
					$('.new-salary-net-income').addClass("error");
					swal('Error','Nombre, Fecha de Admisión, SDI, Registro Patronal y Sueldo Neto son obligatorios','error');
				}
			})
			.on('change','[name="alimonyDiscountType[]"]',function()
			{
				$(this).parents('.tr-payroll').find('[name="alimonyDiscount[]"]').val("0");;			
			})
			.on('click','.add-bonus',function()
			{
				full_name			= $('.new-bonus-fullname').val();
				admission_date		= $('.new-bonus-admission-date').val();
				day_bonus			= $('.new-bonus-day-bonus').val();
				sdi					= $('.new-bonus-sdi').val();
				alimony_type_text	= $('.new-bonus-alimony-discount-type option:selected').text();
				alimony_type_val	= $('.new-bonus-alimony-discount-type option:selected').val();
				alimony_discount	= $('.new-bonus-alimony-discount').val();
				
				if (full_name != '' && admission_date != '' && sdi != '' && day_bonus !='')
				{
					if(sdi == 0)
					{
						$('.new-bonus-sdi').removeClass("valid");
						$('.new-bonus-sdi').addClass("error");
						swal("", "S.D.I debe ser mayor a cero.", "error");
					}
					else
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días para aguinaldo"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$body = [ "classEx" => "tr-payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"x\"",
											"classEx"		=> "idemployee-table-prenomina"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"fullname[]\" placeholder=\"Ingrese un nombre\" data-validation=\"required\"",
											"classEx"		=> "fullname-table-prenomina w-40"
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" data-validation=\"required\" placeholder=\"Ingrese la fecha\" name=\"admission_date[]\" data-validation=\"required\"",
										"classEx"		=> "datepicker admission_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" data-validation=\"required\" name=\"day_bonus[]\" placeholder=\"Ingrese los días de trabajo\" data-validation=\"required\"",
										"classEx"		=> "day_bonus w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" data-validation=\"required\"placeholder=\"Ingrese el S.D.I\" name=\"sdi[]\" data-validation=\"required\"",
										"classEx"		=> "sdi w-40"
									]
								],
								[
									"content"	=>
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\"",
										"classEx"		=> "js-discount-type w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\"",
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[] = $body;
							$tableBonus = view('components.tables.table', [
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHeads"	=> true	
							])->render();
						@endphp
						bodyBonus	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableBonus)!!}';
						tr			= $(bodyBonus);
						tr.find('.fullname-table-prenomina').val(full_name);
						tr.find('.admission_date').val(admission_date);
						tr.find('.day_bonus').val(day_bonus);
						tr.find('.sdi').val(sdi);
						tr.find("[name='alimonyDiscountType[]']").append($('<option value="1">Monto</option><option value="2">Porcentaje</option>'));
						tr.find("[name='alimonyDiscountType[]']").val(alimony_type_val);
						tr.find("[name='alimonyDiscount[]']").val(alimony_discount);		
						$('#body-payroll').append(tr);
						$('.new-bonus-fullname,.new-bonus-admission-date,.new-bonus-day-bonus,.new-bonus-sdi,.new-bonus-alimony-discount').val(null);
						$('.new-bonus-alimony-discount-type').trigger('change').val(null);
						$('#form_new_bonus').stop(true,true).fadeOut().hide();
						$('input[name="type_employee"]').prop('checked',false);
						$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
						$('.day_bonus').numeric({ altDecimal: ".", negative: false });
						$('.sdi').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
						$('.datepicker').on("contextmenu",function(e)
						{
							return false;
						});
						@php
							$selects = collect([
								[
									"identificator"				=> '.js-discount-type',
									"placeholder"				=> "Seleccione un tipo de descuento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				}
				else
				{
					$('.new-bonus-fullname').addClass("error");
					$('.new-bonus-admission-date').addClass("error");
					$('.new-bonus-day-bonus').addClass("error");
					$('.new-bonus-sdi').addClass("error");
					swal('Error','Nombre, Fecha de Admisión, Días para aguinaldo y SDI son obligatorios','error');
				}
			})
			.on('click','.add-liquidation',function()
			{
				full_name			= $('.new-liquidation-fullname').val();
				admission_date		= $('.new-liquidation-admission-date').val();
				down_date			= $('.new-liquidation-down-date').val();
				sdi					= $('.new-liquidation-sdi').val();
				worked_days 		= $('.new-liquidation-worked-days').val();
				other_retention 	= $('.new-liquidation-other-retention').val();
				other_perception 	= $('.new-liquidation-other-perception').val();
				alimony_type_text	= $('.new-liquidation-alimony-discount-type option:selected').text();
				alimony_type_val	= $('.new-liquidation-alimony-discount-type option:selected').val();
				alimony_discount	= $('.new-liquidation-alimony-discount').val();
				moment.defaultFormat= "DD.MM.YYYY";
				var_down_date 		= down_date,moment.defaultFormat;
				var_admission_date	= admission_date,moment.defaultFormat;

				if(var_down_date < var_admission_date)
				{
					$(this).parents("#form_new_liquidation").find(".new-liquidation-down-date").addClass("error");
					$(this).parents("#form_new_liquidation").find(".new-liquidation-admission-date").addClass("error");
					swal("", "La fecha de baja debe ser mayor a la fecha de admisión", "warning");
					return false;
				}
				else
				{
					$(this).parents("#form_new_liquidation").find(".new-liquidation-down-date").removeClass("error");
					$(this).parents("#form_new_liquidation").find(".new-liquidation-admission-date").removeClass("error");
				}
				if (full_name != '' && admission_date != '' && sdi != '' && worked_days !='' && down_date != '') 
				{
					admission_dateV	= moment($('.new-liquidation-admission-date').val(),moment.defaultFormat);
					down_dateV		= moment($('.new-liquidation-down-date').val(),moment.defaultFormat);			
					if((worked_days > down_dateV.diff(admission_dateV, 'days')) || worked_days == 0)
					{
						swal('', 'Los días trabajados deben coincidir con las fechas ingresadas.','warning');
						$('.new-liquidation-worked-days').addClass('error');
					}
					else if(sdi == 0)
					{
						$('.new-liquidation-sdi').removeClass("valid");
						$('.new-liquidation-sdi').addClass("error");
						swal("", "S.D.I debe ser mayor a cero", "warning");
					}
					else
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de baja"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Otra percepción"],
									["value" => "Otra retención"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$body = [ "classEx" => "tr-payroll",
								[
									"classEx"	=> "sticky inset-x-0",
									"content"	=>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"x\"",
											"classEx"		=> "idemployee-table-prenomina"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"fullname[]\" data-validation=\"required\" placeholder=\"Ingrese un nombre\"",
											"classEx"		=> "fullname-table-prenomina w-40"
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"admission_date[]\"",
										"classEx"		=> "datepicker admission_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"down_date[]\"",
										"classEx"		=> "datepicker down_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese los días trabajados\" name=\"worked_days[]\"",
										"classEx"		=> "worked_days w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"sdi[]\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\"",
										"classEx"		=> "sdi w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"other_perception[]\" placeholder=\"Ingrese la percepción\"",
										"classEx"		=> "other_perception w-40" 
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"other_retention[]\" placeholder=\"Ingrese la retención\"",
										"classEx"		=> "other_retention w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.select",
										"classEx"		=> "w-40",
										"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\""
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\""
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[] = $body;
							$tableLiquidation = view('components.tables.table', [
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHeads"	=> true
							])->render();
						@endphp
						bodyLiquidation	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableLiquidation)!!}';
						tr				= $(bodyLiquidation);
						tr.find('.fullname-table-prenomina').val(full_name);
						tr.find('.admission_date').val(admission_date);
						tr.find('.down_date').val(down_date);
						tr.find('.worked_days').val(worked_days);
						tr.find('.sdi').val(sdi);
						tr.find('.other_perception').val(other_perception);
						tr.find('.other_retention').val(other_retention);
						tr.find('[name="alimonyDiscountType[]"]').append($('<option value="1">Monto</option><option value="2">Porcentaje</option>'));
						tr.find('[name="alimonyDiscountType[]"]').val(alimony_type_val);
						tr.find('[name="alimonyDiscount[]"]').val(alimony_discount);
						$('#body-payroll').append(tr);
						$('.new-liquidation-sdi,.new-liquidation-fullname,.new-liquidation-other-perception,.new-liquidation-worked-days,.new-liquidation-down-date,.new-liquidation-admission-date,.new-liquidation-alimony-discount').val(null);
						$('.new-liquidation-alimony-discount-type').trigger('change').val(null);
						$('#form_new_liquidation').stop(true,true).fadeOut().hide();
						$('input[name="type_employee"]').prop('checked',false);
						$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
						$('.day_bonus').numeric({ altDecimal: ".", negative: false });
						$('.sdi').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
						$('.datepicker').on("contextmenu",function(e)
						{
							return false;
						});
						@php
							$selects = collect([
								[
									"identificator"				=> '[name="alimonyDiscountType[]"]',
									"placeholder"				=> "Seleccione un tipo de descuento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				}
				else
				{
					$('.new-liquidation-admission-date').addClass("error");
					$('.new-liquidation-fullname').addClass("error");
					$('.new-liquidation-down-date').addClass("error");
					$('.new-liquidation-worked-days').addClass("error");
					$('.new-liquidation-sdi').addClass("error");
					swal('Error','Nombre, Fecha de Admisión, Fecha de Baja, Días Trabajados y SDI son obligatorios','error');
				}
			})
			.on('click','.add-vacation-premium',function()
			{
				full_name			= $('.new-vacation-premium-fullname').val();
				admission_date		= $('.new-vacation-premium-admission-date').val();
				sdi					= $('.new-vacation-premium-sdi').val();
				worked_days 		= $('.new-vacation-premium-worked-days').val();
				alimony_type_text	= $('.new-vacation-premium-alimony-discount-type option:selected').text();
				alimony_type_val	= $('.new-vacation-premium-alimony-discount-type option:selected').val();
				alimony_discount	= $('.new-vacation-premium-alimony-discount').val();

				if (full_name != '' && admission_date != '' && sdi != '' && worked_days !='') 
				{
					if(sdi == 0)
					{
						$('.new-vacation-premium-sdi').removeClass("valid");
						$('.new-vacation-premium-sdi').addClass("error");
						swal("", "S.D.I debe ser mayor a cero", "warning");
					}
					else if(worked_days == 0)
					{
						$('.new-vacation-premium-worked-days').removeClass("valid");
						$('.new-vacation-premium-worked-days').addClass("error");
						swal("", "Los días trabajados deben ser mayor a cero", "warning");
					}
					else
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$body = [ "classEx" => "tr-payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content"	=>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"x\"",
											"classEx"		=> "idemployee-table-prenomina",
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"text\" name=\"fullname[]\" data-validation=\"required\" placeholder=\"Ingrese un nombre\"",
											"classEx"		=> "fullname-table-prenomina w-40", 
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"admission_date[]\"",
										"classEx"		=> "datepicker admission_date w-40" 
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese los días trabajados\" name=\"worked_days[]\"",
										"classEx"		=> "worked_days w-40" 
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"sdi[]\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\"",
										"classEx"		=> "sdi w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.select",
										"classEx"		=> "w-40",
										"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\"" 
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\""
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[] 	= $body;
							$tableVacation	= view('components.tables.table', [
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHeads"	=> true
							])->render();
						@endphp
						bodyVacation	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableVacation)!!}';
						tr				= $(bodyVacation);
						tr.find('.fullname-table-prenomina').val(full_name);
						tr.find('.admission_date').val(admission_date);
						tr.find('.worked_days').val(worked_days);
						tr.find('.sdi').val(sdi);
						tr.find("[name='alimonyDiscountType[]']").append($('<option value="1">Monto</option><option value="2">Porcentaje</option>'));
						tr.find("[name='alimonyDiscountType[]']").val(alimony_type_val);
						tr.find("[name='alimonyDiscount[]']").val(alimony_discount);
						$('#body-payroll').append(tr);
						$('.new-vacation-premium-sdi,.new-vacation-premium-fullname,.new-vacation-premium-worked-days,.new-vacation-premium-admission-date,.new-vacation-premium-alimony-discount').val(null);
						$('.new-vacation-premium-alimony-discount-type').trigger('change').val(null);
						$('#form_new_vacation_premium').stop(true,true).fadeOut().hide();
						$('input[name="type_employee"]').prop('checked',false);
						$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
						$('.day_bonus').numeric({ altDecimal: ".", negative: false });
						$('.sdi').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
						$('.datepicker').on("contextmenu",function(e)
						{
							return false;
						});
						@php
							$selects = collect([
								[
									"identificator"				=> '[name="alimonyDiscountType[]"]',
									"placeholder"				=> "Seleccione un tipo de descuento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				}
				else
				{
					$('.new-vacation-premium-fullname').addClass("error");
					$('.new-vacation-premium-admission-date').addClass("error");
					$('.new-vacation-premium-worked-days').addClass("error");
					$('.new-vacation-premium-sdi').addClass("error");
					swal('Error','Nombre, Fecha de Admisión, Días Trabajados y SDI son obligatorios','error');
				}
			})
			.on('click','.add-profit-sharing',function()
			{
				full_name			= $('.new-profit-sharing-fullname').val();
				admission_date		= $('.new-profit-sharing-admission-date').val();
				sdi					= $('.new-profit-sharing-sdi').val();
				worked_days 		= $('.new-profit-sharing-worked-days').val();
				alimony_type_text	= $('.new-profit-sharing-alimony-discount-type option:selected').text();
				alimony_type_val	= $('.new-profit-sharing-alimony-discount-type option:selected').val();
				alimony_discount	= $('.new-profit-sharing-alimony-discount').val();

				if (full_name != '' && admission_date != '' && sdi != '' && worked_days !='') 
				{
					if(worked_days == 0)
					{	
						$('.new-profit-sharing-worked-days').removeClass("valid");
						$('.new-profit-sharing-worked-days').addClass("error");
						swal("", "Los días trabajados deben ser mayor a cero", "warning");
					}
					else if(sdi == 0)
					{	
						$('.new-profit-sharing-sdi').removeClass("valid");
						$('.new-profit-sharing-sdi').addClass("error");
						swal("", "S.D.I debe ser mayor a cero", "warning");
					}
					else
					{
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead 	= [
								[
									["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
									["value" => "Fecha de admisión", "classEx" => "sticky inset-x-0"],
									["value" => "Días trabajados"],
									["value" => "SDI"],
									["value" => "Tipo descuento de Pensión Alimenticia"],
									["value" => "Descuento Pensión Alimenticia"],
									["value" => "Acción"]
								]
							];
							$body = [ "classEx" => "tr-payroll",
								[
									"classEx" => "sticky inset-x-0",
									"content"	=>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"hidden\" value=\"x\"",
											"classEx"		=> "idemployee-table-prenomina"				
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx" 	=> "type=\"text\" name=\"fullname[]\" data-validation=\"required\" placeholder=\"Ingrese un nombre\"",
											"classEx"		=> "fullname-table-prenomina w-40"
										]
									]
								],
								[
									"classEx" => "sticky inset-x-0",
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"admission_date[]\"",
										"classEx"		=> "datepicker admission_date w-40"
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese los días trabajados\" name=\"worked_days[]\"",
										"classEx"		=> "worked_days w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.input-text",
										"attributeEx"	=> "type=\"text\" name=\"sdi[]\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\"",
										"classEx"		=> "sdi w-40"
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.inputs.select",
										"classEx"		=> "w-40",
										"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\"" 
									]
								],
								[
									"content" =>
									[
										"kind" 			=> "components.inputs.input-text",
										"classEx"		=> "w-40",
										"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\""
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "red",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "btn-delete-tr",
										"label"			=> "<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[] 	= $body;
							$tableProfit	= view('components.tables.table', [
								"modelHead"	=> $modelHead,
								"modelBody"	=> $modelBody,
								"noHeads"	=> true
							])->render();
						@endphp
						bodyProfit	= '{!!preg_replace("/(\r)*(\n)*/", "", $tableProfit)!!}';
						tr			= $(bodyProfit);
						tr.find('.fullname-table-prenomina').val(full_name);
						tr.find('.admission_date').val(admission_date);
						tr.find('.worked_days').val(worked_days);
						tr.find('.sdi').val(sdi);
						tr.find("[name='alimonyDiscountType[]']").append($('<option value="1">Monto</option><option value="2">Porcentaje</option>'));
						tr.find("[name='alimonyDiscountType[]']").val(alimony_type_val);
						tr.find("[name='alimonyDiscount[]']").val(alimony_discount);
						$('#body-payroll').append(tr);
						$('.new-profit-sharing-sdi,.new-profit-sharing-fullname,.new-profit-sharing-worked-days,.new-profit-sharing-admission-date,.new-profit-sharing-alimony-discount').val(null);
						$('.new-profit-sharing-alimony-discount-type').trigger('change').val(null);
						$('#form_new_profit_sharing').stop(true,true).fadeOut().hide();
						$('input[name="type_employee"]').prop('checked',false);
						$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
						$('.day_bonus').numeric({ altDecimal: ".", negative: false });
						$('.sdi').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="other_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="worked_days[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="absence[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_perception[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="loan_retention[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_puntuality[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="bono_assistance[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="infonavitDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="alimonyDiscount[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="fonacot[]"]').numeric({ altDecimal: ".", negative: false });
						$('[name="net_income[]"]').numeric({ altDecimal: ".", negative: false });
						$('.datepicker').on("contextmenu",function(e)
						{
							return false;
						});
						@php
							$selects = collect([
								[
									"identificator"				=> '[name="alimonyDiscountType[]"]',
									"placeholder"				=> "Seleccione un tipo de descuento",
									"maximumSelectionLength"	=> "1"
								]
							]);
						@endphp
						@component('components.scripts.selects',["selects" => $selects]) @endcomponent
					}
				}
				else
				{
					$('.new-profit-sharing-fullname').addClass("error");
					$('.new-profit-sharing-admission-date').addClass("error");
					$('.new-profit-sharing-worked-days').addClass("error");
					$('.new-profit-sharing-sdi').addClass("error");
					swal('Error','Nombre, Fecha de Admisión, Días Trabajados y SDI son obligatorios','error');
				}
			})
			.on('select2:unselecting','select[name="type_payroll"]', function (e)
			{
				if ($('#body-payroll .tr-payroll').length > 0) 
				{
					e.preventDefault();
					swal({
						title		: "Eliminar",
						text		: "Se eliminaran los empleados agregados a la lista ¿Desea continuar?",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((willClean) =>
					{
						if(willClean)
						{
							$(this).val(null).trigger('change');
							$('#data_salary,.data_salary,#data_bonus,#data_liquidation,#data_vacation_premium,#data_profit_sharing').hide();
							$('input[name="type_employee"]').prop('checked',false);
							$('#employee_new,#employee_exists').stop(true,true).fadeOut().hide();
							$('#thead-type,#body-payroll').html('');
						}
						else
						{
							swal.close();
						}
					});
				}
			})
			.on('change click','select[name="type_payroll"], input[name="type_employee"]', function (e)
			{
				$('.new-salary-fullname,.new-salary-admission-date,.new-salary-sdi,.new-salary-employer-register,.new-salary-net-income,.new-bonus-fullname,.new-bonus-admission-date,.new-bonus-day-bonus,.new-bonus-sdi,.new-liquidation-admission-date,.new-liquidation-fullname,.new-liquidation-down-date,.new-liquidation-worked-days,.new-liquidation-sdi,.new-vacation-premium-fullname,.new-vacation-premium-admission-date,.new-vacation-premium-worked-days,.new-vacation-premium-sdi,.new-profit-sharing-fullname,.new-profit-sharing-admission-date,.new-profit-sharing-worked-days,.new-profit-sharing-sdi').removeClass("error");
				$('.new-salary-fullname,.new-salary-admission-date,.new-salary-sdi,.new-salary-employer-register,.new-salary-net-income,.new-bonus-fullname,.new-bonus-admission-date,.new-bonus-day-bonus,.new-bonus-sdi,.new-liquidation-admission-date,.new-liquidation-fullname,.new-liquidation-down-date,.new-liquidation-worked-days,.new-liquidation-sdi,.new-vacation-premium-fullname,.new-vacation-premium-admission-date,.new-vacation-premium-worked-days,.new-vacation-premium-sdi,.new-profit-sharing-fullname,.new-profit-sharing-admission-date,.new-profit-sharing-worked-days,.new-profit-sharing-sdi').removeClass("valid");
			})
			.on('change','[name="alimonyDiscount[]"]',function()
			{
				type = $(this).parents('.tr-payroll').find('[name="alimonyDiscountType[]"]').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-bonus-alimony-discount',function()
			{
				type = $('.new-bonus-alimony-discount-type').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-liquidation-alimony-discount',function()
			{
				type = $('.new-liquidation-alimony-discount-type').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-vacation-premium-alimony-discount',function()
			{
				type = $('.new-vacation-premium-alimony-discount-type').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-profit-sharing-alimony-discount',function()
			{
				type = $('.new-profit-sharing-alimony-discount-type').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-salary-alimony-discount',function()
			{
				type = $('.new-salary-alimony-discount-type').val();
				if (type == 1 || type == 2) 
				{
					if (type == 2) 
					{
						if ($(this).val() > 100) 
						{
							swal('','El valor no puede ser mayor a 100','error');
							$(this).val('0');
						}
					}
				}
				else
				{
					swal('','Seleccione el tipo de descuento de pensión alimenticia.','info');
					$(this).val('0');
				}
			})
			.on('change','.new-salary-alimony-discount-type',function()
			{
				type = $(this).val();
				if (type == 1 || type == 2) 
				{
					$('.new-salary-alimony-discount').attr('disabled',false);
				}
				else
				{
					$('.new-salary-alimony-discount').attr('disabled',true);
					$('.new-salary-alimony-discount').val('0');
				}
			})
			.on('change','.new-bonus-alimony-discount-type',function()
			{
				type = $(this).val();
				if (type == 1 || type == 2) 
				{
					$('.new-bonus-alimony-discount').attr('disabled',false);
				}
				else
				{
					$('.new-bonus-alimony-discount').attr('disabled',true);
					$('.new-bonus-alimony-discount').val('0');
				}
			})
			.on('change','.new-liquidation-alimony-discount-type',function()
			{
				type = $(this).val();
				if (type == 1 || type == 2) 
				{
					$('.new-liquidation-alimony-discount').attr('disabled',false);
				}
				else
				{
					$('.new-liquidation-alimony-discount').attr('disabled',true);
					$('.new-liquidation-alimony-discount').val('0');
				}
			})
			.on('change','.new-vacation-premium-alimony-discount-type',function()
			{
				type = $(this).val();
				if (type == 1 || type == 2) 
				{
					$('.new-vacation-premium-alimony-discount').attr('disabled',false);
				}
				else
				{
					$('.new-vacation-premium-alimony-discount').attr('disabled',true);
					$('.new-vacation-premium-alimony-discount').val('0');
				}
			})
			.on('change','.new-profit-sharing-alimony-discount-type',function()
			{
				type = $(this).val();
				if (type == 1 || type == 2) 
				{
					$('.new-profit-sharing-alimony-discount').attr('disabled',false);
				}
				else
				{
					$('.new-profit-sharing-alimony-discount').attr('disabled',true);
					$('.new-profit-sharing-alimony-discount').val('0');
				}
			})
			.on('change','[name="to_date"],[name="from_date"]',function()
			{
				moment.defaultFormat	= "DD.MM.YYYY";
				check_from_date			= $('[name="from_date"]');
				check_to_date			= $('[name="to_date"]');
				periodicity				= $('[name="periodicity"]');

				if (check_from_date.val() != "" && check_to_date.val() != "") 
				{
					from_date	= moment(check_from_date.val(),moment.defaultFormat);
					to_date		= moment(check_to_date.val(),moment.defaultFormat);
					diff		= to_date.diff(from_date, 'days');
					days		= [];
					if(periodicity.val() == "02")
					{
						days = [6,7];
					}
					if(periodicity.val() == "04")
					{
						days = [14,15,16];
					}
					if(periodicity.val() == "05")
					{
						days = [28,29,30,31];
					}
					if(!days.includes(diff) && periodicity.val() != undefined)
					{
						swal('','El rango de fechas seleccionado no concuerda con la periodicidad.','error');
						check_from_date.removeClass('valid').removeClass('error').val('');
						check_to_date.removeClass('valid').removeClass('error').val('');
					}
					else if(periodicity.val() == undefined)
					{
						swal('','Seleccione primero una periodicidad','error');
						check_from_date.removeClass('valid').removeClass('error').val('');
						check_to_date.removeClass('valid').removeClass('error').val('');
					}
				}
			})
			.on('change','.periodicity',function()
			{
				check_from_date	= $('[name="from_date"]');
				check_to_date	= $('[name="to_date"]');
				if (check_from_date.val() != "" && check_to_date.val() != "") 
				{
					check_from_date.removeClass('valid').removeClass('error').val('');
					check_to_date.removeClass('valid').removeClass('error').val('');
				}
			});
		});

		function search(page)
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false,
			});
			text = $('input[name="searchEmployee"]').val();
			idrealEmployee = [];

			$('.idemployee-table-prenomina').each(function(){
				idrealEmployee.push(Number($(this).val()))
			})
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("nomina.prenomina-create.getemployee") }}',
				data	: {'search':text,'idrealEmployee':idrealEmployee, 'page':page},
				success	: function(data)
				{
					$('#result').html(data);
					$('#result').stop(true,true).fadeIn();
					swal.close();
				},
				error: function()
				{
					swal.close();
				}
			}); 
		}
	</script>
@endsection