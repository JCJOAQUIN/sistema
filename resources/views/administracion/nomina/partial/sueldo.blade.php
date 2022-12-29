@php
	$body		= [];
	$modelBody	= [];
	$modelHead 	= [
		[
			["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
			["value" => "Admisión"],
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
	$valueEmployee				= $employee != '' ? $employee->id : '';
	$valueEmployeeName			= $employee != '' ? $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name : '';
	$valueEmployeeDate			= $employee != '' ? $employee->workerDataVisible->first()->reentryDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$employee->workerDataVisible->first()->reentryDate)->format('d-m-Y') : Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$employee->workerDataVisible->first()->imssDate)->format('d-m-Y') : '';
	$valueEmployeeDownDate		= $employee != '' && $employee->workerDataVisible->first()->downDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$employee->workerDataVisible->first()->downDate)->format('d-m-Y') : '';
	$valueEmployeeSDI			= $employee != '' && $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->sdi : 0;
	$valueEmployeePuntuality	= $employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->bono != '' ? $employee->workerDataVisible->first()->bono : 0;
	$valueEmployeeAssistance	= $employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->bono != '' ? $employee->workerDataVisible->first()->bono : 0;
	$valueEmployeeRegister		= $employee != '' && $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->employer_register : '';
	$optionDiscountInfo			= [];
	$valueDiscountInfo			= [ "1" => "VSM (Veces Salario Mínimo)", "2" => "Cuota fija", "3" => "Porcentaje" ];
	foreach ($valueDiscountInfo as $key => $value)
	{
		$optionDiscountInfo[] = 
		[
			"value"			=> $key,
			"description"	=> $value,
			"selected"		=> ($employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->infonavitDiscountType == $key ? "selected" : "")
 		];
	}
	$valueEmployeeDiscountInfo	= $employee!='' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->infonavitDiscount != '' ? $employee->workerDataVisible->first()->infonavitDiscount : 0;
	$optionDiscount			= [];
	$valueDiscount			= [ "1" => "Monto", "2" => "Porcentaje" ];
	foreach ($valueDiscount as $key => $value)
	{
		$optionDiscount[] = 
		[
			"value"			=> $key,
			"description"	=> $value,
			"selected"		=> ($employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->alimonyDiscountType == $key ? "selected" : "")
 		];
	}
	$valueEmployeeDiscount	= $employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->alimonyDiscount != '' ? $employee->workerDataVisible->first()->alimonyDiscount : 0;
	$valueEmployeeFonacot	= $employee != '' && $employee->workerDataVisible()->exists() && $employee->workerDataVisible->first()->fonacot!='' ? $employee->workerDataVisible->first()->fonacot : 0;
	$valueEmployeeNet		= $employee != '' && $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->netIncome : '';
	$body = [ "classEx"	=> "tr-payroll",
		[
			"classEx"	=> "sticky inset-x-0",
			"content"	=>
			[
				[
					"kind" 			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" value=\"".$valueEmployee."\"",
					"classEx"		=> "idemployee-table-prenomina"
				],
				[
					"kind" 			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" data-validation=\"required\" placeholder=\"Ingrese un nombre\" name=\"fullname[]\" value=\"".$valueEmployeeName."\"",
					"classEx"		=> "fullname-table-prenomina w-40"
				]
			]
		],
		[
			"content" =>
			[
				"kind" 			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"admission_date[]\" value=\"".$valueEmployeeDate."\"",
				"classEx"		=> "datepicker admission_date w-40"
			]
		],
		[
			"content" =>
			[
				"kind" 			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" name=\"downDate[]\" value=\"".$valueEmployeeDownDate."\"",
				"classEx"		=> "datepicker down_date w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"sdi[]\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\" value=\"".$valueEmployeeSDI."\"",
				"classEx"		=> "sdi w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"0\"",
				"classEx"		=> "absence w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"loan_perception[]\" placeholder=\"Ingrese la percepción\" value=\"0\"",
				"classEx"		=> "loan_perception w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"loan_retention[]\" placeholder=\"Ingrese la retención\" value=\"0\"",
				"classEx"		=> "loan_retention w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"bono_puntuality[]\" placeholder=\"Ingrese el bono\" value=\"".$valueEmployeePuntuality."\"",
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"bono_assistance[]\" placeholder=\"Ingrese el bono\" value=\"".$valueEmployeeAssistance."\"",
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"employer_register[]\" data-validation=\"required\" placeholder=\"Ingrese el registro patronal\" value=\"".$valueEmployeeRegister."\"",
			]
		],
		[
			"content"	=>
			[
				"kind"			=> "components.inputs.select",
				"attributeEx"	=> "name=\"infonavitDiscountType[]\" multiple=\"multiple\"",
				"classEx"		=> "js-discount-type w-40",
				"options"		=> $optionDiscountInfo,
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"infonavitDiscount[]\" placeholder=\"Ingrese el descuento\" value=\"".$valueEmployeeDiscountInfo."\"",
			]
		],
		[
			"content"	=>
			[
				"kind"			=> "components.inputs.select",
				"attributeEx"	=> "name=\"alimonyDiscountType[]\" multiple=\"multiple\"",
				"classEx"		=> "js-discount-type w-40",
				"options"		=> $optionDiscount,
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"alimonyDiscount[]\" placeholder=\"Ingrese el descuento\" value=\"".$valueEmployeeDiscount."\"",
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"classEx"		=> "w-40",
				"attributeEx"	=> "type=\"text\" name=\"fonacot[]\" placeholder=\"Ingrese el fonacot\" value=\"".$valueEmployeeFonacot."\"",
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"net_income[]\" data-validation=\"required\" placeholder=\"Ingrese el suelto neto\" value=\"".$valueEmployeeNet."\"",
				"classEx"		=> "net_income w-40"
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
@endphp
@component('components.tables.table',[
	"modelBody" => $modelBody,
	"modelHead"	=> $modelHead,
	"noHeads"	=> true
])
@endcomponent