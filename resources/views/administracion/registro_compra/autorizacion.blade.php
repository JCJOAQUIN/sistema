@extends('layouts.child_module')
@section('data')
@php
	$taxes = $retentions = 0;
@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	<div class="pb-6">
		@php
			$elaborateUser = App\User::find($request->idElaborate);
			$modelTable =
			[
				["Folio", $request->folio],
				["Título y fecha", htmlentities($request->purchaseRecord->title). " - " .Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseRecord->datetitle)->format('d-m-Y')],
				["Número de Orden", $request->purchaseRecord->numberOrder!="" ? htmlentities($request->purchaseRecord->numberOrder) : '---'],
				["Fiscal", $request->taxPayment == 1 ? "Sí" : "No"],
				["Solicitante", $request->requestUser->fullName()],
				["Elaborado por", $request->elaborateUser->fullName()],
				["Empresa", $request->requestEnterprise->name],
				["Dirección", $request->requestDirection->name],
				["Departamento", $request->requestDepartment->name],
				["Clasificación de gasto", $request->accounts->fullClasificacionName()],
				["Proyecto", $request->requestProject->proyectName]
			];

			if($request->wbs()->exists())
			{
				$modelTable[] = ["WBS:", $request->wbs->code_wbs];
			}
			if($request->edt()->exists())
			{
				$modelTable[] = ["EDT:", $request->edt->description];
			}
			$modelTable[] = ["Proveedor", htmlentities($request->purchaseRecord->provider)];

		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent	
	</div>
	@component('components.labels.title-divisor')    DATOS DEL PEDIDO @endcomponent
	@php
		$body		= [];
		$modelBody	= [];
		$modelHead	= 
		[
			[
				["value" => "Cantidad"],
				["value" => "DescripciÓn"],
				["value" => "#"],
				["value" => "Unidad"],
				["value" => "Precio Unitario"],
				["value" => "IVA"],
				["value" => "Impuesto Adicional"],
				["value" => "Retenciones"],
				["value" => "Importe"]
			]
		];
		$countConcept = 1;
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$taxesConcept		= $detail->taxes()->sum('amount');
			$retentionConcept	= $detail->retentions()->sum('amount');
			$body = 
			[
				[
					"content"	=>
					[
					"label" => $detail->quantity
					]
				],
				[
					"content"	=>
					[
					"label" => htmlentities($detail->description)
					]
				],
				[
					"content" =>
					[
						"label" => $countConcept
					]
				],
				[
					"content" =>
					[
						"label" => $detail->unit
					]
				],
				[
					"content" => 
					[
						"label" => "$".number_format($detail->unitPrice,2)
					]
				],
				[
					"content" =>
					[
						"label" => "$".number_format($detail->tax,2)
					]
				],
				[
					"content" =>
					[
						"label" => "$".number_format($taxesConcept,2)
					]
				],
				[
					"content" =>
					[
						"label" => "$".number_format($retentionConcept,2)
					]
				],
				[
					"content" =>
					[
						"label" => "$".number_format($detail->total,2)
					]
				]
			];
			$modelBody[] = $body;
			$countConcept++;
		}
	@endphp
	@component("components.tables.table",
		[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
	@endcomponent
	<div class="mt-6">
		@component("components.labels.title-divisor") Totales @endcomponent
		@php
			$tableTotals =
			[
				[
					"label" => "Subtotal:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($request->purchaseRecord->subtotal,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Impuestos adicionales:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($request->purchaseRecord->amount_taxes,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Retenciones:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($request->purchaseRecord->amount_retention,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "IVA:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($request->purchaseRecord->tax,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Total:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($request->purchaseRecord->total,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
			];
		@endphp
		@component("components.templates.outputs.form-details", ["modelTable" => $tableTotals, "title" => "", "classEx" => "mt-6"]) @endcomponent
	</div>
	<div class="mt-6">
		@component("components.labels.title-divisor")
			CONDICIONES DE PAGO
		@endcomponent
		@php
			$modelTable = 
			[
				"Empresa"           => $request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '---',
				"Cuenta"            => $request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '---',
				"Referencia"        => ($request->purchaseRecord->reference != "" ? htmlentities($request->purchaseRecord->reference) : "---"),
				"Tipo de moneda"    => $request->purchaseRecord->typeCurrency,
				"Fecha de pago"     => $request->PaymentDate != "" ? Carbon\Carbon::parse($request->PaymentDate)->format('d-m-Y') : '',
				"Forma de pago"     => $request->purchaseRecord->paymentMethod,
				"Estado de factura" => $request->purchaseRecord->billStatus,
				"Importe a pagar"   => "$".number_format($request->purchaseRecord->total,2)
			];
		@endphp
		@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable])@endcomponent
	</div>

	@if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		@php
			$t		= App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user	= App\User::find($t->assignment);
			$status	= $principal = '';
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
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Responsable"],
					["value" => "Número de Tarjeta"],
					["value" => "Nombre en Tarjeta"],
					["value" => "Status"],
					["value" => "Principal/Adicional"]
				]
			];
			$body = 
			[
				[ 
					"content" =>
					[
						"label" => $user->fullName()
					]
				],
				[ 
					"content" =>
					[
						"label" => $t->credit_card
					]
				],
				[
					"content"	=>
					[
						"label" => $t->name_credit_card
					]
				],
				[
					"content"	=>
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
		@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
		@endcomponent
	@endif
	<div class="col-span-2 md:col-span-4 table-striped">
		@if(count($request->purchaseRecord->documents)>0)
			@component("components.labels.title-divisor")
				Documentos de la solicitud
			@endcomponent
			@php
				$documentsBody = [];
				$modelHead = ["Tipo de documento", "Archivo", "Fecha"];
				foreach($request->purchaseRecord->documents as $doc)
				{
					$date = Carbon\Carbon::parse($doc->date)->format('d-m-Y H:i');
					$row  =
					[
						"classEx" => "tr",
						[
							"content" => 
							[
								["kind" => "components.labels.label", "label" => $doc->name ]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "secondary",
									"buttonElement"	=> "a",
									"attributeEx"	=> "target=\"_blank\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/purchase-record/'.$doc->path)."\"",
									"label"			=> "Archivo"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"flight_document_id[]\" value=\"".$doc->id."\""
								]
							]
						],
						[
							"content" => 
							[
								["kind" => "components.labels.label", "label" => $date]
							]
						]
					];
					$documentsBody[] = $row;
				}
			@endphp
			<div class="table-responsive">
				@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $documentsBody,"variant" => "default", "attributeExBody" => "id=\"bodyT\"", "attributeEx" => "id=\"table-documents\""]) @endcomponent
			</div>
			
		@endif
	</div>
	<div class="mt-10">
		@component("components.labels.title-divisor")
			DATOS DE REVISIÓN
		@endcomponent
		<div class="my-6">
			@component("components.tables.table-request-detail.container",["variant"=>"simple"])
				@php
					$reviewDate = Carbon\Carbon::parse($request->reviewDate)->format('d-m-Y');
					$modelTable = 
					[
						"Revisó"                  => $request->reviewedUser->fullName(),
						"Nombre de la Empresa"    => $request->reviewedEnterprise->name,
						"Nombre de la Dirección"  => $request->reviewedDirection->name,
						"Nombre del Departamento" => $request->reviewedDepartment->name,
						"Clasificación del gasto" => $request->accountsReview->fullClasificacionName(),
						"Nombre del Proyecto"     => $request->reviewedProject->proyectName,
						"Comentarios"             => $request->checkComment != "" ? htmlentities($request->checkComment) : 'Sin comentarios',
					];
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
			@endcomponent
		</div>
		<div class="mt-6">
			@component('components.labels.title-divisor') ETIQUETAS ASIGNADAS @endcomponent
			@php
				$labelBody = [];
				$modelHead = ["Cantidad", "Descripción", "Etiquetas"];
				foreach($request->purchaseRecord->detailPurchase as $detail)
				{
					$textLabel = "";
					if($detail->labels()->exists())
					{
						foreach($detail->labels as $label)
						{
							$textLabel .= $label->label->description.",";
						}
					}
					else
					{
						$textLabel = "---";
					}
					$row =
					[
						[
							"content" => 
							[
								"label" => $detail->quantity." ".$detail->unit
							]
						],
						[
							"content" => 
							[
								"label" => htmlentities($detail->description),
							]
						],
						[
							"content" => 
							[
								"label" => $textLabel
							]
						]
					];
					$labelBody[] = $row;
				}
			@endphp
			<div class="table-responsive">
				@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $labelBody,"variant" => "default", "attributeExBody" => "id=\"bodyT\"", "attributeEx" => "id=\"tbody-conceptsNew\""]) @endcomponent
			</div>
		</div>
	</div>
	@component("components.forms.form",["methodEx"=> "PUT","attributeEx" => "method=\"POST\" action=\"".route('purchase-record.authorization.update', $request->folio)."\" id=\"container-alta\""])
		<div class="my-4">
			@component("components.containers.container-approval")
				@slot('textLabel')
					¿Desea autorizar ó rechazar la solicitud? 
				@endslot
				@slot("attributeExButton")
					name="status"
					id="aprobar"
					value="10"
				@endslot
				@slot("classExButton")
					approve
				@endslot
				@slot("attributeExButtonTwo")
					name="status"
					id="rechazar"
					value="7"
				@endslot
				@slot("classExButtonTwo")
					refuse
				@endslot
			@endcomponent
		</div>
		<div class="mt-4" id="aceptar">
			@component("components.labels.label")
				Comentarios (opcional) 
			@endcomponent
			@component("components.inputs.text-area")
				@slot("attributeEx")
					name="authorizeCommentA"
					cols="90"
					rows="10"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4 mb-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot("attributeEx") 
					type="submit"
					name="enviar"
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', [ "buttonElement" => "a", "variant" => "reset"])
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}" 
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				@slot('classEx')
					load-actioner
				@endslot
				REGRESAR 
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script>
	$(document).ready(function()
	{
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					swal("Cargando",{
						icon: '{{ url('images/loading.svg') }}',
						button: false,
						closeOnClickOutside: false,
						closeOnEsc: false
					});
					return true;
				}
				else
				{
					swal('', 'Debe seleccionar al menos un estado', 'error');
					return false;
				}
			}
		});
	});
</script>
@endsection
