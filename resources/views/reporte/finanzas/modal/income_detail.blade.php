@php
	$taxesCount = $taxesCountBilling = 0;
	$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
@endphp
@if ($request->parent)
	@component('components.labels.not-found',["variant"	=>	"note"])
		@slot('attributeEx')
			id="error_request"
		@endslot
		<span class="icon-bullhorn"></span> Esta solicitud es complementaria a la solicitud #{{ $request->parent->parentRequestModel->folio }}.
	@endcomponent
@endif
<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>
<div class="pb-6">
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$modelTable	=
		[
			["Folio:",			$request->folio],
			["Título y fecha:",	$request->income->first()->title." - ".($request->income->first()->datetitle!="" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->income->first()->datetitle)->format('d-m-Y') : null)],
			["Fiscal:",			$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",	$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",		App\Enterprise::find($request->idEnterprise)->name],
			["Proyecto:",		isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles de la solicitud
		@endslot
	@endcomponent
</div>
<div class="mt-10">
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS BANCARIOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Banco"],
				["value"	=>	"Alias"],
				["value"	=>	"Cuenta"],
				["value"	=>	"Sucursal"],
				["value"	=>	"Referencia"],
				["value"	=>	"CLABE"],
				["value"	=>	"Moneda"],
				["value"	=>	"Convenio"],
			]
		];
		foreach(App\BanksAccounts::where('idEnterprise',$request->idEnterprise)->get() as $bank)
		{
			$alias		=	$bank->alias!=null ? $bank->alias : '---';
			$clabe		=	$bank->clabe!=null ? $bank->clabe : '---';
			$account	=	$bank->account!=null ? $bank->account : '---';
			$branch		=	$bank->branch!=null ? $bank->branch : '---';
			$reference	=	$bank->reference!=null ? $bank->reference : '---';
			$currency	=	$bank->currency!=null ? $bank->currency : '---';
			$agreement	=	$bank->agreement!=null ? $bank->agreement : '---';
			if ($request->income->first()->idbanksAccounts == $bank->idbanksAccounts)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bank->bank->description]
					],
					[
						"content"	=>	["label"	=>	$alias]
					],
					[
						"content"	=>	["label"	=>	$account]
					],
					[
						"content"	=>	["label"	=>	$branch]
					],
					[
						"content"	=>	["label"	=>	$reference]
					],
					[
						"content"	=>	["label"	=>	$clabe]
					],
					[
						"content"	=>	["label"	=>	$currency]
					],
					[
						"content"	=>	["label"	=>	$agreement]
					]
				];
				$modelBody[]	=	$body;
			}
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
</div>
<div class="mt-10">
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-6
		@endslot
		DATOS DEL CLIENTE
	@endcomponent
	@php
		$modelTable	=
		[
			"Razón Social"			=>	$request->income->first()->client->businessName,
			"RFC"					=>	$request->income->first()->client->rfc,
			"Teléfono"				=>	$request->income->first()->client->phone,
			"Calle"					=>	$request->income->first()->client->address,
			"Número"				=>	$request->income->first()->client->number,
			"Colonia"				=>	$request->income->first()->client->colony,
			"CP"					=>	$request->income->first()->client->postalCode,
			"Ciudad"				=>	$request->income->first()->client->city,
			"Estado"				=>	App\State::find($request->income->first()->client->state_idstate)->description,
			"Contacto"				=>	$request->income->first()->client->contact,
			"Correo Electrónico"	=>	$request->income->first()->client->email,
			"Otro"					=>	$request->income->first()->client->commentaries,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE LA VENTA
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Unidad"],
				["value"	=>	"Descripci&oacute;n"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"],
			]
		];
		if (isset($request))
		{
			foreach($request->income->first()->incomeDetail as $key=>$detail)
			{
				$taxesConcept		=	0;
				$retentionConcept	=	0;
				foreach ($detail->taxes as $tax)
				{
					$taxesConcept+=$tax->amount;
				}
				foreach ($detail->retentions as $ret)
				{
					$retentionConcept+=$ret->amount;
				}
				$taxesCount++;
				$body	=
				[
					[
						"content"	=>
						[
							"kind"	=>	"components.labels.label",
							"label"	=>	$key+1,
							"classEx"	=>	"countConcept"
						],
					],
					[
						"content"	=>	["label"	=>	$detail->quantity]
					],
					[
						"content"	=>	["label"	=>	$detail->unit]
					],
					[
						"content"	=>	["label"	=>	$detail->description]
					],
					[
						"content"	=>	["label"	=>	"$ ".$detail->unitPrice]
					],
					[
						"content"	=>	["label"	=>	"$ ".$detail->tax]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
					],
					[
						"content"	=>	["label"	=>	"$ ".$detail->amount]
					],
				];
				$modelBody[]	=	$body;
			}
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeExBody')
			id="body" class="request-validate text-center"
		@endslot
	@endcomponent
</div>
<div class="pb-6">
	@php
		if (isset($request))
		{
			foreach ($request->income->first()->incomeDetail as $detail)
			{
				foreach ($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
				foreach ($detail->retentions as $ret)
				{
					$retentions += $ret->amount;
				}
			}
			$subtotal		=	"$ ".number_format($request->income->first()->subtotales,2,".",",");
			$ivaTotal		=	"$ ".number_format($request->income->first()->tax,2,".",",");
			$totalAmount	=	"$ ".number_format($request->income->first()->amount,2,".",",");
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	$subtotal],]
			],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($taxes,2)],]
			],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	"$ ".number_format($taxes,2)],]
			],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	$ivaTotal],]
			],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2", "label"	=>	$totalAmount],]
			],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
</div>
<div class="pb-6">
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DOCUMENTOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->income->first()->documents)>0)
		{
			$modelHead	=	["Nombre", "Archivo", "Fecha"];
			foreach($request->income->first()->documents as $doc)
			{
				$body	=
				[
					[
						"content"	=>	["label"	=>	$doc->name],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/income/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						],
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->created_at)->format('d-m-Y')],
					],
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Archivos"];
			$body	=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
</div>