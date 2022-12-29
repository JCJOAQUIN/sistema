@php
	$modelHead = 
	[
		["value" => "Acciones", "show" => "true"],
		["value" => "Alías", "show" => "true"],
		["value" => "Nombre en Tarjeta"],
		["value" => "Número en Tarjeta"],
		["value" => "Estado"],
		["value" => "Principal/Adicional"],
	];

	$modelBody = [];
@endphp
@foreach($tdc as $t)
	@php
		$user = App\User::find($t->assignment);
		$status = $principal = '';
		switch ($t->status) 
		{
			case 1:
				$status = 'Vigente';
				break;
			case 2:
				$status = 'Bloqueada';
				break;
			case 3:
				$status = 'Cancelada';
				break;
			default:
				break;
		}

		switch ($t->principal_aditional) 
		{
			case 1:
				$principal = 'Principal';
				break;
			case 2:
				$principal = 'Adicional';
				break;
			default:
				break;
		}

		$body = 
		[
			[
				"show" => "true",
				"content" => 
				[
					[
						"kind" 			=> "components.inputs.checkbox",
						"attributeEx" 	=> "id=\"id_".$t->idcreditCard."\" type=\"radio\" name=\"idcreditCard\" value=\"".$t->idcreditCard."\"",
						"radio"			=> "radio",
						"label"			=> "<span class=\"icon-check\"></span>",
						"classEx" 		=>  "checkbox"
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"classEx"		=> "idEnterprise",
						"attributeEx" 	=> "type=\"hidden\" value=\"".$t->idEnterprise."\""
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"classEx"		=> "idAccAcc",
						"attributeEx" 	=> "type=\"hidden\" value=\"".$t->idAccAcc."\""
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"classEx"		=> "nameEnterprise",
						"attributeEx" 	=> "type=\"hidden\" value=\"".App\Enterprise::find($t->idEnterprise)->name."\""
					],
					[
						"kind" 			=> "components.inputs.input-text",
						"classEx"		=> "nameAccount",
						"attributeEx" 	=> "type=\"hidden\" value=\"".App\Account::find($t->idAccAcc)->account.' - '.App\Account::find($t->idAccAcc)->description."\""
					]
				]
			],
			[
				"show" => "true",
				"content" =>
				[
					"label" => $t->alias
				]
			],
			[
				"content" =>
				[
					"label" => $t->name_credit_card
				]
			],
			[
				"content" =>
				[
					"label" => $t->credit_card
				]
			],
			[
				"content" =>
				[
					"label" => $status
				]
			],
			[
				"content" =>
				[
					"label" => $principal
				]
			]
		];
		$modelBody[] = $body;
	@endphp
@endforeach
@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead" => "true"])@endcomponent