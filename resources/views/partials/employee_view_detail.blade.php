@component('components.labels.title-divisor') DATOS PERSONALES @endcomponent
<div> 
	@php
		$modelTable = [
			"Nombre"	=> $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name,
			"CURP"		=> $employee->curp,
			"RFC"		=> $employee->rfc,
			"#IMSS"		=> $employee->imss,
			"Calle"		=> $employee->street,
			"Número"	=> $employee->number,
			"Colonia"	=> $employee->colony,
			"CP"		=> $employee->cp,
			"Ciudad"	=> $employee->city,
			"Estado"	=> $employee->states->description
		];
	@endphp	
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable ]) @endcomponent
</div>
@component('components.labels.title-divisor') DATOS PERSONALES @endcomponent
<div>
	@php
		$varWBS = '';
		if($employee->workerData->where('visible',1)->first()->wbs()->exists())
		{
			$varWBS = $employee->workerData->where('visible',1)->first()->wbs->code_wbs;
		}
		else
		{
			$varWBS = '---';
		}
		$varPlace = '';
		foreach($employee->workerData->where('visible',1)->first()->places as $p) 
		{
			$varPlace = $p->place;
		}
		$varStatus = '';
		if($employee->workerData->where('visible',1)->first()->workerStatus == 1) 
		{
			$varStatus = "Activo";
		}
		if($employee->workerData->where('visible',1)->first()->workerStatus == 2) 
		{
			$varStatus = "Baja pacial";
		}
		if($employee->workerData->where('visible',1)->first()->workerStatus == 3) 
		{
			$varStatus = "Baja definitiva";
		}
		if($employee->workerData->where('visible',1)->first()->workerStatus == 4) 
		{
			$varStatus = "Suspensión";
		}
		if($employee->workerData->where('visible',1)->first()->workerStatus == 5)
		{
			$varStatus = "Boletinado";
		}
		$varDiscount = '';
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 1) 
		{
			$varDiscount = "VSM (Veces Salario Mínimo)";
		}
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 2) 
		{
			$varDiscount = "Cuota fija";
		}
		if($employee->workerData->where('visible',1)->first()->infonavitDiscountType == 3)
		{
			$varDiscount = "Porcentaje";
		}
		$admissionDate	= $employee->workerData->where('visible',1)->first()->admissionDate	!= ''	? $employee->workerData->where('visible',1)->first()->admissionDate->format('d-m-Y')	: '';
		$imssDate		= $employee->workerData->where('visible',1)->first()->imssDate 		!= ''	? $employee->workerData->where('visible',1)->first()->imssDate->format('d-m-Y') 		: '';
		$downDate		= $employee->workerData->where('visible',1)->first()->downDate 		!= ''	? $employee->workerData->where('visible',1)->first()->downDate->format('d-m-Y') 		: '';
		$endingDate		= $employee->workerData->where('visible',1)->first()->endingDate 	!= ''	? $employee->workerData->where('visible',1)->first()->endingDate->format('d-m-Y')		: '';
		$reentryDate	= $employee->workerData->where('visible',1)->first()->reentryDate 	!= ''	? $employee->workerData->where('visible',1)->first()->reentryDate->format('d-m-Y') 		: '';

		$modelTable = [
			"Estado"								=> $employee->workerData->where('visible',1)->first()->states()->exists() ? $employee->workerData->where('visible',1)->first()->states->description : '',
			"Proyecto"								=> $employee->workerData->where('visible',1)->first()->projects()->exists() ? $employee->workerData->where('visible',1)->first()->projects->proyectName : '',
			"WBS"									=> $varWBS,
			"Empresa"								=> $employee->workerData->where('visible',1)->first()->enterprises()->exists() ? $employee->workerData->where('visible',1)->first()->enterprises->name : '',
			"Clasificación de gasto"				=> $employee->workerData->where('visible',1)->first()->accounts->account.' '.$employee->workerData->where('visible',1)->first()->accounts->description,
			"Lugar de Trabajo"						=> $varPlace,
			"Dirección"								=> $employee->workerData->where('visible',1)->first()->directions()->exists() ? $employee->workerData->where('visible',1)->first()->directions->name : '',
			"Departamento"							=> $employee->workerData->where('visible',1)->first()->departments()->exists() ? $employee->workerData->where('visible',1)->first()->departments->name : '',
			"Registro patronal"						=> $employee->workerData->where('visible',1)->first()->employer_register,
			"Puesto"								=> $employee->workerData->where('visible',1)->first()->position,
			"Jefe inmediato"						=> $employee->workerData->where('visible',1)->first()->immediate_boss,
			"Fecha de ingreso"						=> $admissionDate,
			"Status IMSS"							=> $employee->workerData->where('visible',1)->first()->status_imss == 1 ? 'Activo' : 'Inactivo',
			"Fecha de alta IMSS"					=> $imssDate,
			"Fecha de baja"							=> $downDate,
			"Fecha de término de relación laboral"	=> $endingDate,
			"Reingreso"								=> $reentryDate,
			"Tipo de trabajador"					=> $employee->workerData->where('visible',1)->first()->worker()->exists() ?  $employee->workerData->where('visible',1)->first()->worker->description : 'no hay',
			"Estatus"								=> $varStatus,
			"SDI"									=> '$ '.number_format($employee->workerData->where('visible',1)->first()->sdi,2),
			"Periodicidad"							=> App\CatPeriodicity::where('c_periodicity',$employee->workerData->where('visible',1)->first()->periodicity)->first()->description,
			"Sueldo neto"							=> '$ '.number_format($employee->workerData->where('visible',1)->first()->netIncome,2),
			"Complemento"							=> '$ '.number_format($employee->workerData->where('visible',1)->first()->complement,2),
			"Monto Fonacot"							=> '$ '.number_format($employee->workerData->where('visible',1)->first()->fonacot,2),
			"Número de crédito"						=> '$ '.number_format($employee->workerData->where('visible',1)->first()->infonavitCredit,2),
			"Descuento"								=> '$ '.number_format($employee->workerData->where('visible',1)->first()->infonavitDiscount,2),
			"Tipo de descuento"						=> $varDiscount
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@php
		$body 		= [];
		$modelBody	= [];
		$modelHead	= ["Porcentaje de nómina","Porcentaje de bonos"];

		$body = [
			[
				"content" => [ "label" => $employee->workerData->where('visible',1)->first()->nomina ]
			],
			[
				"content" => [ "label" => $employee->workerData->where('visible',1)->first()->bono ]
			]
		];
		$modelBody[] = $body;
	@endphp
	@component('components.tables.alwaysVisibleTable', [
			"modelHead" => $modelHead,
			"modelBody"	=> $modelBody,
			"title"		=> "Esquema de pagos"
		])
	@endcomponent
</div>
@component('components.labels.title-divisor') CUENTAS BANCARIAS @endcomponent
<div class="my-4">
	@php
		$body 		= [];
		$modelBody 	= [];
		$modelHead	= 
		[
			[
				["value" => "Alias"],
				["value" => "Banco"],
				["value" => "CLABE"],
				["value" => "Cuenta"],
				["value" => "Tarjeta"],
				["value" => "Sucursal"]
			]
		];

		foreach($employee->bankData->where('visible',1) as $b) 
		{
			$body = [
				[
					"content" =>
					[
						"label" => $b->alias
					]
				],
				[
					"content" =>
					[
						"label" => $b->bank->description
					]
				],
				[
					"content" =>
					[
						"label" => isset($b->clabe) ? $b->clabe : '---' 
					]
				],
				[
					"content" =>
					[
						"label" => isset($b->account) ? $b->account : '---' 
					]
				],
				[
					"content" =>
					[
						"label" => isset($b->cardNumber) ? $b->cardNumber : '---' 
					]
				],
				[
					"content" =>
					[
						"label" => isset($b->branch) ? $b->branch : '---' 
					]
				]
			];
			$modelBody[] = $body;
		}
	@endphp	
	@component('components.tables.table', [
			"modelBody"	=> $modelBody,
			"modelHead" => $modelHead
		])
		@slot('attributeEx')
			id="bank-data-register"
		@endslot
	@endcomponent
</div>	
<div class="flex justify-center">
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