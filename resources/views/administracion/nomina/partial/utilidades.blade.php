@php
	$body		= [];
	$modelBody	= [];
	$modelHead 	= [
		[
			["value" => "Nombre del Empleado", "classEx" => "sticky inset-x-0"],
			["value" => "Fecha de admisión"],
			["value" => "Días trabajados"],
			["value" => "SDI"],
			["value" => "Tipo descuento de Pensión Alimenticia"],
			["value" => "Descuento Pensión Alimenticia"],
			["value" => "Acción"]
		]
	];
	$valueEmployee 		= $employee != '' ? $employee->id : '';
	$valueEmployeeName	= $employee != '' ? $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name : '';
	$valueEmployeeDate	= $employee != '' ? $employee->workerDataVisible->first()->reentryDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$employee->workerDataVisible->first()->reentryDate)->format('d-m-Y') : Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$employee->workerDataVisible->first()->imssDate)->format('d-m-Y') : '';
	$valueEmployeeSDI	= $employee != '' && $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->sdi : 0;
	$optionDiscount		= [];
	$valueDiscount		= [ "1" => "Monto", "2" => "Porcentaje" ];
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
	
	$body = [ "classEx" => "tr-payroll",
		[
			"classEx"		=> "sticky inset-x-0",
			"content"	=>
			[
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" value=\"".$valueEmployee."\"",
					"classEx"		=> "idemployee-table-prenomina"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"fullname[]\" data-validation=\"required\" placeholder=\"Ingrese un nombre\" value=\"".$valueEmployeeName."\"",
					"classEx"		=> "fullname-table-prenomina"
				]
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" readonly=\"readonly\" placeholder=\"Ingrese la fecha\" data-validation=\"required\" name=\"admission_date[]\" value=\"".$valueEmployeeDate."\"",
				"classEx"		=> "datepicker admission_date w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese los días trabajados\" data-validation=\"required\" name=\"worked_days[]\"",
				"classEx"		=> "worked_days w-40"
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" placeholder=\"Ingrese el S.D.I\" data-validation=\"required\" name=\"sdi[]\" value=\"".$valueEmployeeSDI."\"",
				"classEx"		=> "sdi w-40"
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
	"modelHead" => $modelHead,
	"noHeads"	=> true,
])
@endcomponent