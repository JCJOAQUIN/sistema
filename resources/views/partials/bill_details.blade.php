@if ($type == 1)
	@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DE FACTURA @endcomponent
	<div class="flex-wrap w-full grid md:grid-cols-2 grid-cols-1 gap-x-10">
		<div class="w-full max-w-full col-span-1 mb-4">
			@php
				$modelTable	= [];
				if(!isset($conciliacion))
				{
					$modelTable["UUID"] = $bill->uuid != "" ? $bill->uuid : "---";
				}
				
				$modelTable["Folio"] = $bill->folio;
				$modelTable["Serie"] = $bill->serie;
				$modelTable["Uso de CFDI"] = $bill->cfdiUse()->exists() ? $bill->cfdiUse->useVoucher." ".$bill->cfdiUse->description : "";
				$modelTable["Tipo de CFDI"] = $bill->cfdiType()->exists() ? $bill->cfdiType->typeVoucher." ".$bill->cfdiType->description : "";
				$modelTable["Forma de pago"] = $bill->cfdiPaymentWay()->exists() ? $bill->cfdiPaymentWay->paymentWay." ".$bill->cfdiPaymentWay->description : "";
				$modelTable["Método de pago"] = $bill->cfdiPaymentMethod()->exists() ? $bill->cfdiPaymentMethod->paymentMethod." ".$bill->cfdiPaymentMethod->description : "";
				$modelTable["Código Postal"] = $bill->postalCode;
				$modelTable["Condiciones de pago"] = $bill->conditions != "" ? $bill->conditions : "---";
				
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent	
		</div>
		<div class="w-full max-w-full col-span-1 mb-4">
			@php
				$modelTable	= [];
				if(isset($req))
				{
					$modelTable["Folio de Solicitud"]	= $req->folio;
					$modelTable["Título"]				=  $req->income()->exists() ? isset($req->income->first()->title) ? $req->income->first()->title : 'Sin Título' : 'Sin Título';
				}
				$modelTable["Emisor"]	= "<b>".$bill->rfc."</b><p>".$bill->businessName."</p>";
				$modelTable["Receptor"]	= "<b>".$bill->clientRfc."</b><p>".$bill->clientBusinessName."</p>";
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent	
		</div>
	</div>
	@foreach ($bill->billDetail as $d)
		<div class="border border-orange-200 mb-8">
			@php
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	"Clave de producto o servicio"],
						["value"	=>	"Clave de unidad"],
						["value"	=>	"Cantidad"],
						["value"	=>	"Valor unitario"],
						["value"	=>	"Importe"],
						["value"	=>	"Descuento"]
					]
				];
				$body	=
				[
					[
						"content"	=>	["label"	=>	$d->keyProdServ]
					],
					[
						"content"	=>	["label"	=>	$d->keyUnit]
					],
					[
						"content"	=>	["label"	=>	$d->quantity]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($d->value,6)]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($d->amount,6)]
					],
					[
						"content"	=>	["label"	=>	"$ ".number_format($d->discount,6)]
					],
				];
				$modelBody[]	=	$body;
			@endphp
			@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody]) @endcomponent
			
			<div class="grid grid-cols-2 mt-4">
				<div class="col-span-1">
					@component('components.labels.label')
						@slot('classEx')
							px-2 font-bold
						@endslot
						Descripción:
					@endcomponent
					@component('components.labels.label')
						@slot('classEx')
							px-2
						@endslot
						{{$d->description}}
					@endcomponent
				</div>
				<div class="col-span-1">
					@if ($d->taxesTras->count()>0)
						@php
							$body 		= [];
							$modelBody	= [];
							$modelHead 	= 
							[	
								[
									["value" => "Impuesto", "classEx" => "hidden"],
									["value" => "¿Tasa o cuota?", "classEx" => "hidden"],
									["value" => "Valor de la tasa o cuota", "classEx" => "hidden"],
									["value" => "Importe", "classEx" => "hidden"],
								]
							];
							foreach($d->taxesTras as $t)
							{
								$body	=
								[
									[
										"content"	=>	["label"	=>	$t->cfdiTax->description],
									],
									[
										"content"	=>	["label"	=>	$t->quota],
									],
									[
										"content"	=>	["label"	=>	$t->quotaValue],
									],
									[
										"content"	=>	["label"	=>	"$ ".number_format($t->amount,6)],
									],
								];
								$modelBody[]	=	$body;
							}
						@endphp
						@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
							@slot('title')
								Traslados
							@endslot
						@endcomponent
					@endif
					@if ($d->taxesRet->count()>0)
						@php
							$body 		= [];
							$modelBody	= [];
							$modelHead	= 
							[	
								[
									["value" => "Impuesto", "classEx" => "hidden"],
									["value" => "¿Tasa o cuota?", "classEx" => "hidden"],
									["value" => "Valor de la tasa o cuota", "classEx" => "hidden"],
									["value" => "Importe", "classEx" => "hidden"],
								]
							];
							foreach($d->taxesRet as $t)
							{
								$body	=
								[
									[
										"content"	=>	["label" =>	$t->cfdiTax->description],
									],
									[
										"content"	=>	["label"	=>	$t->quota],
									],
									[
										"content"	=>	["label"	=>	$t->quotaValue],
									],
									[
										"content"	=>	["label"	=>	"$ ".number_format($t->amount,6)],
									],
								];
								$modelBody[]	=	$body;
							}
						@endphp
						@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
							@slot('title')
								Retenciones
							@endslot
						@endcomponent
					@endif
				</div>
			</div>
		</div>
	@endforeach
	<div class="flex-wrap w-full grid md:grid-cols-2 grid-cols-1 gap-x-10">
		<div class="w-full max-w-full col-span-1 mb-4">
			@component('components.labels.label')
				@slot('classEx')
					mt-4
				@endslot
				<p><b>Moneda:</b> {{ $bill->cfdiCurrency()->exists() ? $bill->cfdiCurrency->description : "" }}</p>
				<p><b>Forma de pago:</b> {{ $bill->cfdiPaymentWay()->exists() ? $bill->cfdiPaymentWay->description : "" }}</p>
				<p><b>Método de pago:</b> {{ $bill->cfdiPaymentMethod()->exists() ? $bill->cfdiPaymentMethod->description : "" }}</p>
			@endcomponent
		</div>
		<div class="w-full max-w-full col-span-1 mb-4">
			@php
				$modelTable = 
				[	
					["label" => "Subtotal: ", "inputsEx" 						=> [["kind" => "components.labels.label",	"label" => "$ ".number_format($bill->subtotal,2), "classEx" => "text-right"]]],
					["label" => "Descuento: ", "inputsEx"					 	=> [["kind" => "components.labels.label",	"label"	=> "$ ".number_format($bill->discount,2), "classEx" => "text-right"]]],
					["label" => "Total de impuestos trasladados: ", "inputsEx" 	=> [["kind" => "components.labels.label",	"label" => "$ ".number_format($bill->tras,2), "classEx" => "text-right"]]],
					["label" => "Total de impuestos retenidos:", "inputsEx" 	=> [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($bill->ret,2), "classEx" => "text-right"]]],
					["label" => "Total: ", "inputsEx" 							=> [["kind" => "components.labels.label",	"label"	=> "$ ".number_format($bill->total,2), "classEx" => "text-right"]]],
				];
			@endphp
			@component('components.templates.outputs.form-details', ["modelTable" => $modelTable, "variantProvider" => ""]) @endcomponent
		</div>
	</div>
@elseif ($type == 2)
	@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DEL INGRESO NO FISCAL @endcomponent
	<div class="flex-wrap w-full grid md:grid-cols-2 grid-cols-1 gap-x-10">
		<div class="w-full max-w-full col-span-1 mb-4">
			@php
				$modelTable	=
				[
					"Folio de Solicitud"	=>	$bill->folio,
					"Forma de pago"			=>	$bill->cfdiPaymentWay()->exists() ? $bill->cfdiPaymentWay->description : "",
					"Método de pago"		=>	$bill->cfdiPaymentMethod()->exists() ? $bill->cfdiPaymentMethod->description : "",
					"Condiciones de pago"	=>	$bill->conditions != "" ? $bill->conditions : "---",
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent	
		</div>
		<div class="w-full max-w-full col-span-1 mb-4">
			@php
				$modelTable	= [];
				$modelTable["Empresa"]	= "<b>".$bill->rfc."</b><p>".$bill->businessName."</p>";
				$modelTable["Cliente"]	= "<b>".$bill->clientRfc."</b><p>".$bill->clientBusinessName."</p>";
			@endphp
			@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent	
		</div>
	</div>
	
	@foreach ($bill->billDetail as $d)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cantidad"],
					["value"	=>	"Valor unitario"],
					["value"	=>	"Importe"],
					["value"	=>	"Descuento"]
				]
			];
			$body	=
			[
				[
					"content"	=>	["label"	=>	$d->quantity]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($d->value,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($d->amount,2)]
				],					
				[
					"content"	=>	["label"	=>	"$ ".number_format($d->discount,2)]
				],
			];
			$modelBody[]	=	$body;
		@endphp
		@component("components.tables.table", ["modelHead" => $modelHead, "modelBody" => $modelBody, "classEx" => "mb-8"]) @endcomponent
	@endforeach

	@php
		$modelTable = 
		[	
			["label" => "Subtotal: ", "inputsEx" 						=> [["kind" => "components.labels.label",	"label" => "$ ".number_format($bill->subtotal,2), "classEx" => "py-2"]]],
			["label" => "Descuento: ", "inputsEx"					 	=> [["kind" => "components.labels.label",	"label"	=> "$ ".number_format($bill->discount,2), "classEx" => "py-2"]]],
			["label" => "Total: ", "inputsEx" 							=> [["kind" => "components.labels.label",	"label"	=> "$ ".number_format($bill->total,2), "classEx" => "py-2"]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
@endif

@isset($movement)
	@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DE MOVIMIENTO @endcomponent
	@php
		$modelTable	= [];
		$modelTable	=
		[
			"Empresa"					=>	$movement->enterprise->name,
			"Clasificación de Gasto"	=>	$movement->accounts->account." - ".$movement->accounts->description." (".$movement->accounts->content.")",
			"Tipo de Movimiento"		=>	$movement->movementType,
			"Descripción"				=>	$movement->description,
			"Total"						=>	"$ ".number_format($movement->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
@endisset

<div class="my-6">
	<div class="text-center">
		@component("components.buttons.button",[
			"variant"		=> "success",
			"attributeEx" 	=> "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"",
			"label"			=> "« Ocultar",
			"classEx"		=> "exit",
		])  
		@endcomponent
	</div>
</div>