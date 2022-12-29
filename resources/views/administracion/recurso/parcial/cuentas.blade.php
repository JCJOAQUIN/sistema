@if(count($banks)>0)
	@component('components.labels.title-divisor') SELECCIONE UNA CUENTA @endcomponent	
	@php
		$body = [];
		$modelBody = [];
		$modelHead = 
		[
			[
				["value"=>"Acción"],
				["value"=>"Banco"],
				["value"=>"Alias"],
				["value"=>"Número de tarjeta"],
				["value"=>"CLABE"],
				["value"=>"Número de cuenta"]
			]
		];

		foreach($banks as $bank)
		{
			$alias 		= $bank->alias!=null ? $bank->alias : '---';
			$cardNumber = $bank->cardNumber!=null ? $bank->cardNumber : '---';
			$clabe 		= $bank->clabe!=null ? $bank->clabe : '---';
			$account 	= $bank->account!=null ? $bank->account : '---';
		
			$body = 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						[
							"classExContainer" 	=> "inline-flex",
							"kind"          	=> "components.inputs.checkbox",
							"label"				=> "<span class=\"icon-check\"></span>",
							"classEx"			=> "checkbox request-validate",
							"attributeEx"		=> "id=\"idEmp".$bank->idEmployee ."\" name=\"idEmployee\" value=\"".$bank->idEmployee."\"",
						],
					]
				],	
				[
					"content" =>
					[
						[
							"label" => $bank->description,
						],
						[
							"kind" 			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" name=\"bank[]\" placeholder=\"Ingrese el banco\" value=\"".$bank->description."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $alias,
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"alias[]\" placeholder=\"Ingrese el alias\" value=\"".$bank->alias."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $cardNumber,
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"card[]\" placeholder=\"Ingrese el número de tarjeta\" value=\"".$cardNumber."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $clabe,
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"clabe[]\" placeholder=\"Ingrese la CLABE\" value=\"".$clabe."\"",
						]
					]
				],
				[
					"content" =>
					[
						[
							"label" => $account,
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"account[]\" placeholder=\"Ingrese la cuenta bancaria\" value=\"".$account."\"",
						]
					]
				],
			];
			$modelBody[]=$body;
		}
	@endphp
	@component("components.tables.table",[
		"modelHead"	=> $modelHead,
		"modelBody"	=> $modelBody,
	])
		@slot("classEx")	
			text-center
		@endslot
		@slot("attributeEx")
			id="table2"
		@endslot
		@slot("classExBody")
			request-validate
		@endslot
	@endcomponent
@else
	@component("components.labels.not-found")		
		@slot('text')
			No se han encontrado cuentas registradas
		@endslot
	@endcomponent
@endif
