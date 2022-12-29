@if(count($banks) > 0)
	@component('components.labels.title-divisor') SELECCIONE UNA CUENTA @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	""],
				["value"	=>	"Banco"],
				["value"	=>	"Alias"],
				["value"	=>	"Cuenta"],
				["value"	=>	"CLABE"],
				["value"	=>	"Sucursal"],
				["value"	=>	"Referencia"],
				["value"	=>	"Moneda"],
				["value"	=>	"Convenio"],
			]
		];
		foreach($banks as $bank)
		{
			$body	=
			[
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.inputs.checkbox",
							"attributeEx" 	=>	"id=\"idBA$bank->idbanksAccounts\" type=\"radio\" name=\"idbanksAccounts\" value=\"".$bank->idbanksAccounts."\"",
							"classEx"		=>	"checkbox",
							"id"			=>	"idEmp$bank->idEmployee",
							"classExLabel"	=>	"check-small",
							"label"			=>	'<span class="icon-check"></span>',
							"radio"			=>	true
						]
					]
				],
				[
					"content"	=>	["label"	=>	$bank->bank->description]
				],
				[
					"content"	=>	["label"	=>	$bank->alias != "" ? $bank->alias : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->account != "" ? $bank->account : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->clabe != "" ? $bank->clabe : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->branch != "" ? $bank->branch : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->reference != "" ? $bank->reference : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->currency != "" ? $bank->currency : "---"]
				],
				[
					"content"	=>	["label"	=>	$bank->agreement != "" ? $bank->agreement : "---"]
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4 
		@endslot
		@slot('attributeEx')
			id="table2"
		@endslot
		@slot('attributeExBody')
			id="banks-body"
		@endslot
	@endcomponent
@else
	@component('components.labels.not-found', ["variant" => "alert"])
		@slot('title')	@endslot
		@slot('classEx')
			text-center
		@endslot
		NO HAY CUENTAS REGISTRADAS PARA ESTA EMPRESA
	@endcomponent
@endif