@php
	$body		= [];
	$modelBody	= [];
	$modelHead	= [
		[
			["value" => "Nombre del Empleado"],
			["value" => "Faltas"],
			["value" => "Horas extra"],
			["value" => "Días festivos"],
			["value" => "Domingos trabajados"],
			["value" => "Acción"]
		]
	];
	$periodicityWorker = $employee->workerDataVisible()->exists() ? $employee->workerDataVisible->first()->periodicity : '02';
	$body = [ "classEx" => "tr_bodypayroll",
		[
			"content" =>
			[
				[
					"label" => $employee->fullName()
				],
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" name=\"employee_id[]\" value=\"".$employee->id."\"",
					"classEx"		=> "idemployee-table-prenomina"
				],
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"hidden\" value=\"".$periodicityWorker."\"",
					"classEx"		=> "periodicity"
				]
			]
		],
		[
			"content" =>
			[
				[
					"kind"			=> "components.inputs.input-text",
					"attributeEx"	=> "type=\"text\" name=\"absence[]\" placeholder=\"Ingrese las faltas\" value=\"0\""
				]
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"extra_hours[]\" placeholder=\"Ingrese las horas extras\" value=\"0\""
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"holidays[]\" placeholder=\"Ingrese los dias festivos\" value=\"0\""
			]
		],
		[
			"content" =>
			[
				"kind"			=> "components.inputs.input-text",
				"attributeEx"	=> "type=\"text\" name=\"sundays[]\" placeholder=\"Ingrese los domingos trabajados\" value=\"0\""
			]
		],
		[
			"content" =>
			[
				"kind"		=> "components.buttons.button",
				"variant"	=> "red",
				"classEx"	=> "btn-delete-tr",
				"label"		=> "<span class=\"icon-x\"></span>"
			]
		]
	];
	$modelBody[] = $body;
@endphp
@component('components.tables.table',
[
	"modelBody" => $modelBody,
	"modelHead" => $modelHead,
	"noHead"	=> true
])
@endcomponent
