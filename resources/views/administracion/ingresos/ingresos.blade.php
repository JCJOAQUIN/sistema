@extends('layouts.child_module')
@php
	$incomeBill   = true;
@endphp
@section('data')
	@php
		$taxesCount = $taxesCountBilling = 0;
		$taxes = $retentions = $taxesBilling = $retentionsBilling = 0;
	@endphp
	@if ($requestModel->parent)
		@component('components.labels.not-found', ["variant"	=>	"note"])
			<span class="icon-bullhorn"></span> Esta solicitud es complementaria a la solicitud #{{ $requestModel->parent->parentRequestModel->folio }}.
		@endcomponent
	@endif
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Título"],
				["value"	=>	"Fecha"],
				["value"	=>	"Fiscal"],
				["value"	=>	"Solicitante"],
				["value"	=>	"Total"],
				["value"	=>	"Acción"]
			]
		];
		$body	=
		[
			[
				"content"	=>	["label"	=>	htmlentities($requestModel->income->first()->title)]
			],
			[
				"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d',$requestModel->income->first()->datetitle)->format('d-m-Y')]
			],
			[
				"content"	=>	["label"	=>	$requestModel->taxPayment == 1 ? "Si" : "No"]
			],
			[
				"content"	=>	["label"	=>	$requestModel->requestUser->name." ".$requestModel->requestUser->last_name]
			],
			[
				"content"	=>	["label"	=>	$requestModel->income->first()->amount]
			],
			[
				"content"	=>
				[
					"kind"			=>	"components.buttons.button",
					"variant"		=>	"warning",
					"attributeEx"	=>	"type=\"button\" id=\"view-detail\"",
					"label"			=>	"<span class='icon-plus'></span>"
				]
			]
		];
		$modelBody[]	=	$body;
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('classExHead')
			table-info
		@endslot
		@slot('title')
			Datos de solicitud
		@endslot
	@endcomponent
	<div id="detail-request" class="hidden">
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
					["value"	=>	"Descripción"],
					["value"	=>	"Precio Unitario"],
					["value"	=>	"IVA"],
					["value"	=>	"Impuesto adicional"],
					["value"	=>	"Retenciones"],
					["value"	=>	"Importe"]
				]
			];
			if (isset($requestModel))
			{
				foreach($requestModel->income->first()->incomeDetail as $key=>$detail)
				{
					$taxesConcept=0;
					foreach ($detail->taxes as $tax)
					{
						$taxesConcept+=$tax->amount;
					}
					$retentionConcept=0;
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
								"kind"		=>	"components.labels.label",
								"label"		=>	$key+1,
								"classEx"	=>	"countConcept"
							]
						],
						[
							"content"	=>	["label"	=>	$detail->quantity]
						],
						[
							"content"	=>	["label"	=>	htmlentities($detail->unit)]
						],
						[
							"content"	=>	["label"	=>	htmlentities($detail->description)]
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
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		<div class="totales2">
			<div class="totales">
				@php
					if (isset($requestModel))
					{
						$subtotal		=	"$ ".number_format($requestModel->income->first()->subtotales,2,".",",");
						$taxesTotal		=	"$ ".number_format($requestModel->income->first()->tax,2,".",",");
						$totalAmount	=	"$ ".number_format($requestModel->income->first()->amount,2,".",",");
						foreach ($requestModel->income->first()->incomeDetail as $detail)
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
					}
					$modelTable	=
					[
						["label"	=>	"Subtotal:",			"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2",	"label"	=>	$subtotal],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"subtotal\"	value=\"".$subtotal."\""]
						]
						],
						["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2",	"label"	=>	"$ ".number_format($taxes,2)],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"amountAA\"	value=\"$ ".number_format($taxes,2)."\""]
						]
						],
						["label"	=>	"Retenciones:",			"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2",	"label"	=>	"$ ".number_format($retentions,2)],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"amountR\"	value=\"$ ".number_format($retentions,2)."\""]
						]
						],
						["label"	=>	"IVA:",					"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2",	"label"	=>	$taxesTotal],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"totaliva\"	value=\"".$taxesTotal."\""]
						]
						],
						["label"	=>	"TOTAL:",				"inputsEx"	=>
						[
							["kind"	=>	"components.labels.label",		"classEx"		=>	"h-10 py-2",	"label"	=>	$totalAmount],
							["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"total\"	value=\"".$totalAmount."\" id=\"input-extrasmall\""]
						]
						],
					]
				@endphp
				@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])@endcomponent
			</div>
		</div>
	</div>
	@if($requestModel->taxPayment == 1)
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Folio"],
					["value"	=>	"Serie"],
					["value"	=>	"Monto"],
					["value"	=>	"Estado"],
					["value"	=>	"Estado  de factura"],
					["value"	=>	"Acción"]
				]
			];
			foreach($requestModel->bill as $savedBill)
			{
				$buttonPrefact	=	[];
				if ($savedBill->status==0)
				{
					$status	=	"Pendiente de timbrado";
				}
				else if ($savedBill->status==1)
				{
					$status	=	"Pendiente de conciliación";
				}
				else if ($savedBill->status==2)
				{
					$status	=	"Coinciliado";
				}
				else if ($savedBill->status==3)
				{
					$status	=	"En proceso de cancelación";
				}
				else if ($savedBill->status==5)
				{
					$status	=	"En proceso de cancelación";
				}
				else
				{
					$status	=	"Cancelado";
				}
				if ($savedBill->status==0)
				{
					$buttonPrefact	=
					[
						"kind"			=> "components.buttons.button",
						"variant"		=>	"dark-red",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"alt=\"Descargar pre-factura\" title=\"Descargar pre-factura\" href=\"".route('income.prefactura',$savedBill->idBill)."\"",
						"label"			=>	"<span class='icon-pdf'></span>"
					];
				}
				else
				{
					$buttonPrefact	=	["label"	=>	""];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$savedBill->folio]
					],
					[
						"content"	=>	["label"	=>	$savedBill->serie]
					],
					[
						"content"	=>	["label"	=>	$savedBill->total]
					],
					[
						"content"	=>	["label"	=>	$status]
					],
					[
						"content"	=>	["label"	=>	$savedBill->statusCFDI]
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"warning",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" title=\"Replicar CFDI\" href=\"".route('income.projection.income.bill',[$requestModel,$savedBill->idBill])."\"",
								"label"			=>	"<span class='icon-plus'></span>"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"attributeEx"	=>	"type=\"button\" title=\"Ver detalles\" data-toggle=\"modal\" data-target=\"#billDetailModal\" data-bill=\"".$savedBill->idBill."\"",
								"label"			=>	"<span class='icon-search'></span>"
							],
							$buttonPrefact
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				my-4
			@endslot
			@slot('title')
				Facturas registradas
			@endslot
		@endcomponent
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-factura\" action=\"".route('income.projection.income.save', 143)."\""])
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" id="taxRegime" name="taxRegime" value="{{$requestModel->enterprise()->first()->taxRegime}}"
				@endslot
			@endcomponent	
			@component('components.inputs.input-text')
				@slot('attributeEx')
					type="hidden" name="bill_version" @if(isset($bill)) value="{{ $bill->version }}" @else value="{{ str_replace('_','.',env('CFDI_VERSION','3_3')) }}" @endif
				@endslot
			@endcomponent	
			@if($requestModel->enterprise()->first()->taxRegime == '')
				@component('components.labels.not-found', ["variant" => "alert"])
					@slot('title') Error: @endslot
					La empresa no cuenta con régimen fiscal registrado por lo que no se podrá proceder con la alta de la factura, capture o indique al personal autorizado que registre este campo en el módulo «Empresa»
				@endcomponent
			@endif
			@php
				if(!isset($bill))
				{
					$bill						=	new App\Bill;
					$bill->rfc					=	$requestModel->enterprise()->first()->rfc;
					$bill->taxRegime			=	$requestModel->enterprise()->first()->taxRegime;
					$bill->version				=	str_replace('_','.',env('CFDI_VERSION','3_3'));
					$bill->clientRfc			=	$requestModel->income->first()->client->rfc;
					$bill->clientBusinessName	=	$requestModel->income->first()->client->businessName;
				}
			@endphp
			@include('administracion.facturacion.cfdi_form')
		@endcomponent
		@include('administracion.facturacion.cfdi_modals')
	@else
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"ID"],
					["value"	=>	"Folio"],
					["value"	=>	"Monto"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"]
				]
			];
			foreach($requestModel->billNF as $bill)
			{
				if ($bill->statusCFDI==0)
				{
					$status	=	"Pendiente de ingreso";
				}
				else if ($bill->statusCFDI==1)
				{
					$status	=	"Conciliado";
				}
				else if ($bill->statusCFDI==2)
				{
					$status	=	"Cancelado";
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bill->idBill]
					],
					[
						"content"	=>	["label"	=>	$bill->folio]
					],
					[
						"content"	=>	["label"	=>	$bill->total]
					],
					[
						"content"	=>	["label"	=>	$status]
					],
					[
						"content"	=>
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"attributeEx"	=>	"type=\"button\" data-toggle=\"modal\" data-target=\"#billDetailModal\" data-bill=\"".$bill->idBill."\"",
							"label"			=>	"<span class='icon-search'></span>"
						]
					]
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				my-4
			@endslot
			@slot('title')
				Ingresos registrados
			@endslot
		@endcomponent
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-factura\" action=\"".route('income.projection.income.nf.save')."\""])
			<div class="card">
				@component('components.labels.title-divisor')
					Agregar Ingreso
				@endcomponent
				@component('components.labels.subtitle', ["label" => "Empresa:"]) @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') *RFC: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" readonly name="rfc_emitter" value="{{$requestModel->enterprise()->first()->rfc}}" placeholder="Ingrese el RFC"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') *Razón social: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" readonly name="business_name_emitter" value="{{$requestModel->enterprise()->first()->name}}" placeholder="Ingrese la razón social"
							@endslot
						@endcomponent
					</div>
				@endcomponent
				@component('components.labels.subtitle', ["label" => "Cliente:"]) @endcomponent
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') *RFC: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" readonly name="rfc_receiver" value="{{ $requestModel->income->first()->client->rfc }}" placeholder="Ingrese el RFC"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') *Razón social: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" readonly name="business_name_receiver" value="{{ $requestModel->income->first()->client->businessName }}" placeholder="Ingrese la razón social"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') *Forma de pago: @endcomponent
						@php
							foreach (App\CatPaymentWay::orderName()->get() as $p)
							{
								$optionsPaymentWay[]	=	["value"	=>	$p->paymentWay,	"description"	=>	$p->description,];
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionsPaymentWay])
							@slot('attributeEx')
								name="cfdi_payment_way" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-payment
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') *Método de pago: @endcomponent
						@php
							$optionsPaymentMethod	=	[];
							foreach (App\CatPaymentMethod::orderName()->get() as $p)
							{
								if ($p->paymentMethod=='PUE')
								{
									$optionsPaymentMethod[]	=	["value"	=>	$p->paymentMethod,	"description"	=>	$p->description,	"selected"	=>	"selected"];
								}
								else
								{
									$optionsPaymentMethod[]	=	["value"	=>	$p->paymentMethod,	"description"	=>	$p->description,];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionsPaymentMethod])
							@slot('attributeEx')
								name="cfdi_payment_method" data-validation="required"
							@endslot
							@slot('classEx')
								js-payment-method
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label')
							Folio:
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
							name="folio" type="text" readonly value="{{$requestModel->folio}}" placeholder="Ingrese el folio"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label')
							Condiciones de pago:
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="text" name="conditions" placeholder="Ingrese las condiciones"
							@endslot
						@endcomponent
					</div>
				@endcomponent
				@php
					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=
					[
						[
							["value"	=>	"*Cantidad"],
							["value"	=>	"*Descripción"],
							["value"	=>	"*Valor unitario"],
							["value"	=>	"*Importe"],
							["value"	=>	"*Descuento"],
							["value"	=>	"Acción"]
						]
					];
					$body	=
					[
						[
							"content"	=>
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"id=\"cfdi-quantity\" placeholder=\"Ingrese la cantidad\""
							]
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"id=\"cfdi-description\" placeholder=\"Ingrese la descripcion\""
							]
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"id=\"cfdi-value\" placeholder=\"Ingrese el valor\""
							]
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"readonly id=\"cfdi-total\" value=\"0\""
							]
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"id=\"cfdi-discount\" value=\"0\""
							]
						],
						[
							"content"	=>
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"warning",
								"attributeEx"	=>	"type=\"button\"",
								"classEx"		=>	"add-cfdi-concept",
								"label"			=>	"<span class='icon-plus'></span>"
							]
						]
					];
					$modelBody[]	=	$body;
				@endphp
				@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
					@slot('classEx')
						mt-4 cfdi-concepts
					@endslot
					@slot('attributeExBody')
						id="body-cfdi-concepts"
					@endslot
				@endcomponent
				@php
					$modelTable	=
					[
						["label"	=>	"*Subtotal:",	"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"	=>	"subtotalLabel h-10 py-2",	"label"	=>	"$ 0.00"],
								["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"subtotal\""]
							]
						],
						["label"	=>	"Descuento:",	"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"	=>	"discount_cfdiLabel h-10 py-2",	"label"	=>	"$ 0.00"],
								["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"discount_cfdi\""]
							]
						],
						["label"	=>	"*Total:",		"inputsEx"	=>
							[
								["kind"	=>	"components.labels.label",		"classEx"	=>	"cfdi_totalLabel h-10 py-2",	"label"	=>	"$ 0.00"],
								["kind"	=>	"components.inputs.input-text",	"attributeEx"	=>	"type=\"hidden\" name=\"cfdi_total\""]
							]
						],
					];
				@endphp
				@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
				<div class="flex flex-row justify-center flex-wrap mt-8">
					@component('components.buttons.button', ["variant"	=>	"primary"])
						@slot('attributeEx')
							type="submit"
						@endslot
						@slot('label')
							Registrar Ingreso
						@endslot
					@endcomponent
				</div>
			</div>
		@endcomponent
	@endif
	@component('components.modals.modal')
		@slot('id')
			billDetailModal
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('modalHeader')
			@component('components.buttons.button')
				@slot('classEx')
					close
				@endslot
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('label')
					<span aria-hidden="true">&times;</span>
				@endslot
			@endcomponent
		@endslot
		@slot('modalBody')
		@endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant"	=>	"red"])
				@slot('attributeEx')
					type="button" data-dismiss="modal"
				@endslot
				@slot('label')
					Cerrar
				@endslot
			@endcomponent
		@endslot
	@endcomponent
@endsection
@section('scripts')
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	@if($requestModel->taxPayment == 1)
		<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
		<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
		<script src="{{ asset('js/daterangepicker.js') }}"></script>
		<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
		<script src="{{ asset('js/jquery-ui.js') }}"></script>
		<script src="{{ asset('js/datepicker.js') }}"></script>
		<script>
			$(document).ready(function()
			{
				@php
					$selects = collect([
						[
							"identificator"          => ".js-payment-method",
							"placeholder"            => "Seleccione el método de pago",
							"maximumSelectionLength" => "1"
						]
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
			}).on('click','[data-toggle="modal"]',function()
			{
				swal({
					icon               : '{{ asset(getenv('LOADING_IMG')) }}',
					button             : false,
					closeOnClickOutside: false,
					closeOnEsc         : false
				});
				$('#billDetailModal .modal-body').html('');
				idbill	= $(this).attr('data-bill');
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route('income.projection.detail') }}',
					data	: {'id':idbill,'requestModel':{{ $requestModel->folio }} },
					success	: function(data)
					{
						$('.modal-body').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#billDetailModal').hide();
					}
				}).done(function(data)
				{
					swal.close();
				});
			})
			.on('click','[data-dismiss="modal"]',function()
			{
				$('.modal-body').html('<center><img src="{{ asset(getenv('LOADING_IMG')) }}" width="100"></center>');
			})
			.on('click','#view-detail',function()
			{
				$('#view-detail span.icon-plus').stop(true,true).toggleClass('active');
				$('#detail-request').stop(true,true).slideToggle();
			});
		</script>
		@include('administracion.facturacion.cfdi_script')
	@else
		<script>
			$(document).ready(function()
			{
				@php
					$selects = collect([
						[
							"identificator"          => ".js-payment-method",
							"placeholder"            => "Seleccione el método de pago",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".js-payment",
							"placeholder"            => "Seleccione la forma de pago",
							"maximumSelectionLength" => "1"
						],
					]);
				@endphp
				@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
				$('#cfdi-quantity,#cfdi-value,#cfdi-discount').numeric({ altDecimal: ".", decimalPlaces: 2, negative : false });
				$.validate(
				{
					form	: '#container-factura',
					onError	: function($form)
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					},
					onSuccess	: function($form)
					{
						if($('.cfdi-concepts #body-cfdi-concepts .concept-row').length<1)
						{
							swal('','Al menos debe ingresar un concepto','error');
							return false;
						}
						else if(Number($('[name="cfdi_total"]').val()) <= 0)
						{
							swal('','No pueden registrarse pagos en cero o total negativo','error');
							return false;
						}
					},
				});
				$(document).on('click','[data-toggle="modal"]',function()
				{
					id	= $(this).attr('data-bill');
					$.ajax(
					{
						type	: 'post',
						url		: '{{ route('income.projection.detailnf') }}',
						data	: {'id':id,'requestModel':{{ $requestModel->folio }} },
						success	: function(data)
						{
							$('.modal-body').html(data);
						},
						error : function()
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							$('#billDetailModal').hide();
						}
					});
				})
				.on('click','[data-dismiss="modal"]',function()
				{
					$('.modal-body').html('<center><img src="{{ asset(getenv('LOADING_IMG')) }}" width="100"></center>');
				})
				.on('input','#cfdi-quantity',function()
				{
					q		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
					v		= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
					total	= q * v;
					$('#cfdi-total').val(total);
				})
				.on('input','#cfdi-value',function()
				{
					v		= isNaN(Number($(this).val())) ? 0 : Number($(this).val());
					q		= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
					total	= q * v;
					$('#cfdi-total').val(total);
				})
				.on('change','[name="cfdi_payment_way"]',function()
				{
					if($(this).val()=='99')
					{
						$('[name="cfdi_payment_method"]').val('PPD').trigger('change');
					}
					else
					{
						$('[name="cfdi_payment_method"]').val('PUE').trigger('change');
					}
				})
				.on('change','[name="cfdi_payment_method"]',function()
				{
					if($(this).val()=='PUE' && $('[name="cfdi_payment_way"]').val()=='99')
					{
						$('[name="cfdi_payment_way"]').val('01').trigger('change');
					}
					else if($(this).val()=='PPD' && $('[name="cfdi_payment_way"]').val()!='99')
					{
						$('[name="cfdi_payment_way"]').val('99').trigger('change');
					}
				})
				.on('click','.add-cfdi-concept',function()
				{
					quantity	= isNaN(Number($('#cfdi-quantity').val())) ? 0 : Number($('#cfdi-quantity').val());
					description	= $('#cfdi-description').val();
					valueCFDI	= isNaN(Number($('#cfdi-value').val())) ? 0 : Number($('#cfdi-value').val());
					total		= $('#cfdi-total').val();
					discount	= isNaN(Number($('#cfdi-discount').val())) ? 0 : Number($('#cfdi-discount').val());
					if(quantity==0 || valueCFDI==0)
					{
						swal('','La cantidad y el valor unitario no puede ser cero','warning');
					}
					else if(quantity=='' || description =='' || valueCFDI=='')
					{
						swal('','Por favor complete los datos del producto','warning');
					}
					else
					{
						@php
							$modelHead	=	[];
							$body		=	[];
							$modelBody	=	[];
							$modelHead	=
							[
								[
									["value"	=>	"*Cantidad"],
									["value"	=>	"*Descripción"],
									["value"	=>	"*Valor unitario"],
									["value"	=>	"*Importe"],
									["value"	=>	"*Descuento"],
									["value"	=>	"Acción"],
								]
							];
							$body	=
							[
								"classEx"	=>	"concept-row",
								[
									"content"	=>
									[
										"kind"	=>	"components.inputs.input-text",
										"attributeEx"	=>	"name=\"quantity[]\" type=\"text\" readonly"
									]
								],
								[
									"content"	=>
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"name=\"description[]\" type=\"text\" readonly"
									]
								],
								[
									"content"	=>
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"name=\"valueCFDI[]\" type=\"text\" readonly"
									]
								],
								[
									"content"	=>
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"name=\"amount[]\" type=\"text\" readonly"
									]
								],
								[
									"content"	=>
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"name=\"discount[]\" type=\"text\" readonly"
									]
								],
								[
									"content"	=>
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"red",
										"attributeEx"	=>	"type=\"button\"",
										"classEx"		=>	"cfdi-concept-delete",
										"label"			=>	"<span class=\"icon-x\"></span>"
									]
								]
							];
							$modelBody[]	=	$body;
							$table = view('components.tables.table',[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"classEx"	=>	"cfdi-concepts",
								"noHead"	=> "true"
							])->render();
						@endphp
						table_row = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						table=$(table_row);
						table.find('[name="quantity[]"]').val(quantity);
						table.find('[name="description[]"]').val(description);
						table.find('[name="valueCFDI[]"]').val(valueCFDI);
						table.find('[name="amount[]"]').val(total);
						table.find('[name="discount[]"]').val(discount);

						$('#body-cfdi-concepts').append(table);
						$('#cfdi-quantity').val('');
						$('#cfdi-description').val('');
						$('#cfdi-value').val('');
						$('#cfdi-total').val(0);
						$('#cfdi-discount').val(0);
						subtotalGlobal	= 0;
						discountGlobal	= 0;
						$('[name="amount[]"]').each(function(i,v)
						{
							subtotalGlobal += Number($(this).val());
						});
						$('[name="discount[]"]').each(function(i,v)
						{
							discountGlobal += Number($(this).val());
						});
						totalGlobal = Number(subtotalGlobal - discountGlobal);
						$('[name="subtotal"]').val(subtotalGlobal);
						$('.subtotalLabel').text('$ '+Number(subtotalGlobal).toFixed(2));
						$('[name="discount_cfdi"]').val(discountGlobal);
						$('.discount_cfdiLabel').text('$ '+Number(discountGlobal).toFixed(2));
						$('[name="cfdi_total"]').val(totalGlobal);
						$('.cfdi_totalLabel').text('$ '+Number(totalGlobal).toFixed(2));
					}
				})
				.on('click','.cfdi-concept-delete',function()
				{
					amount			=	$(this).parents('.concept-row').find('[name="amount[]"]').val();
					discount		=	$(this).parents('.concept-row').find('[name="discount[]"]').val();
					subtotalGlobal	=	$('[name="subtotal"]').val();
					discountGlobal	=	$('[name="discount_cfdi"]').val();
					totalGlobal		=	$('[name="cfdi_total"]').val();
					subtotalGlobal	-=	amount;
					discountGlobal	-=	discount;
					totalGlobal		=	Number(subtotalGlobal - discountGlobal);
					if (totalGlobal != 0)
					{
						$('[name="subtotal"]').val(subtotalGlobal);
						$('[name="discount_cfdi"]').val(discountGlobal);
						$('[name="cfdi_total"]').val(totalGlobal);
						$('[name="subtotalLabel"]').text(subtotalGlobal);
						$('[name="discount_cfdiLabel"]').text(discountGlobal);
						$('[name="cfdi_totalLabel"]').text(totalGlobal);
					}
					else
					{
						$('[name="subtotal"]').val("");
						$('[name="discount_cfdi"]').val("");
						$('[name="cfdi_total"]').val("");
						$('[name="subtotalLabel"]').text("");
						$('[name="discount_cfdiLabel"]').text("");
						$('[name="cfdi_totalLabel"]').text("");
					}
					$(this).parents('.concept-row').remove();
				})
				.on('click','#view-detail',function()
				{
					$('#view-detail span.icon-plus').stop(true,true).toggleClass('active');
					$('#detail-request').stop(true,true).slideToggle();
				})
				.on('click','.cfdi-concept-delete',function()
				{
					$(this).parents('tr').remove();
					swal('','Concepto eliminado exitosamente','success');
				});
			});
		</script>
	@endif
@endsection

@php
	function serie($num)
	{
		$result	= '';
		$prev	= $num/26;
		if($prev>1)
		{
			$prev	= floor($prev);
			$res	= $num%26;
			if($res == 0)
			{
				$prev	= $prev - 1;
			}
			$result	= chr(substr("000".($prev+64),-3));
			$num	= $num - ($prev*26);
		}
		$result	.= chr(substr("000".($num+64),-3));
		return $result;
	}
@endphp
