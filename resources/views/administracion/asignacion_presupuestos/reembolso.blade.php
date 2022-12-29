@section('data')
	@php 
		$user       = App\User::find($request->idRequest); 
		$account    = App\Account::find($request->account); 
		$project    = App\Project::find($request->idProject); 
		$docs       = 0;
		$taxes      = 0;
		$retentions = 0;
	@endphp
	@if($request->refunds->first()->idRequisition != "")
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<span class="icon-bullhorn"></span> Esta solicitud viene de la requisición #{{ $request->refunds->first()->idRequisition }}. 
			@endslot
		@endcomponent
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div class="flex flex-row">
					@component('components.labels.label') 
						@slot('classEx')
							font-bold text-blue-900
						@endslot
						FOLIO: 
					@endcomponent @component('components.labels.label')
						@slot('classEx')
							px-2
						@endslot 
						{{ $request->folio }} 
					@endcomponent
				</div>
				@if($request->refunds->first()->requisitionRequest->idProject == 75)
					<div class="flex flex-row">
						@component('components.labels.label') 
							@slot('classEx')
								font-bold text-blue-900
							@endslot
							SUBPROYECTO/CÓDIGO WBS:
						@endcomponent @component('components.labels.label')
							@slot('classEx')
								px-2
							@endslot
							{{ $request->refunds->first()->requisitionRequest->requisition->wbs->code_wbs }}.
						@endcomponent
					</div>
					<div class="flex flex-row">
						@component('components.labels.label') 
							@slot('classEx')
								font-bold text-blue-900
							@endslot
							CÓDIGO EDT:
						@endcomponent @component('components.labels.label')
							@slot('classEx')
								px-2
							@endslot
							{{ $request->refunds->first()->requisitionRequest->requisition->edt()->exists() ? $request->refunds->first()->requisitionRequest->requisition->edt->fullName() : '' }}.
						@endcomponent
					</div>
				@endif
			@endslot
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$dateTitle  = $request->refunds->first()->datetitle != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->refunds->first()->datetitle)->format('d-m-Y') : "";
		$modelTable = 
		[
			["Folio: ", $request->folio],
			["Título y fecha: ", htmlentities($request->refunds->first()->title)." - ".$dateTitle],
			["Solicitante: ", $request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
			["Elaborado por: ", $request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""]
		];
	@endphp
	@component("components.templates.outputs.table-detail", 
	[
		"modelTable" => $modelTable, 
		"title"      => "Detalles de la Solicitud"
	]) 
	@endcomponent
	<div class="px-4 md:px-8">
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			Datos del solicitante
		@endcomponent
		@component("components.tables.table-request-detail.container",["variant" => "simple"])
			@slot('classEx')mt-4 @endslot
			@foreach($request->refunds as $refund)
				@php
					$modelTable =
					[
						"Forma de pago "  => $refund->paymentMethod != '' ? $refund->paymentMethod->method : 'No asignado',
						"Referencia "     => $refund->reference != "" ? htmlentities($refund->reference) : "Sin referencia",
						"Tipo de moneda " => $refund->currency != "" ? $refund->currency : "No asignado",
						"Importe "        => $refund->total != "" ? "$".number_format($refund->total,2) : "No asignado"
					];
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])  @endcomponent
			@endforeach
			@foreach($request->refunds as $refund)
				@foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$refund->idUsers)->get() as $bank)
					@if($refund->idEmployee == $bank->idEmployee)
						@php
							$modelTable =
							[
								"Banco "             => $bank->description,
								"Alias "             => $bank->alias!=null ? htmlentities($bank->alias) : '---',
								"Número de tarjeta " => $bank->cardNumber!=null ? $bank->cardNumber : '---',
								"CLABE "             => $bank->clabe!=null ? $bank->clabe : '---',
								"Número de cuenta "  => $bank->account!=null ? $bank->account : '---'
							];
						@endphp
						@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
						@endcomponent
					@endif
				@endforeach
			@endforeach
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			RELACIÓN DE DOCUMENTOS
		@endcomponent
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "#"],
					["value" => "Concepto"],
					["value" => "Clasificación del gasto"],
					["value" => "Fiscal"],
					["value" => "Subtotal"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Retenciones"],	
					["value" => "Importe"],
					["value" => "Documento(s)"]
				]
			];
			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			if(isset($request))
			{
				$countConcept = 1;
				foreach(App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
				{
					$subtotalFinal += $refundDetail->amount;
					$ivaFinal      += $refundDetail->tax;
					$totalFinal    += $refundDetail->sAmount;
					$taxes2        =  $refundDetail->taxes->sum('amount');
					$retentions2   =  $refundDetail->retentions->sum('amount');
					$documents     =  [];
					if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
					{
						foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
						{
							$documents['content'] =
							[
								[
									"kind"  => "components.labels.label", 
									"label" => Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y')
								], 
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a", 
									"variant"       => "dark-red",
									"label"         => "PDF", 
									"attributeEx"   => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/refounds/'.$doc->path)."\""
								]
							];
						}
					}
					else
					{
						$documents['content'] =
							[
								"label" => "---"
							];
					}
					$body = [
						[
							"content" => 
							[
								"label" => $countConcept
							]
						],
						[
							"content" => 
							[
								"label" => htmlentities($refundDetail->concept),
							]
						],
						[
							"content" => 
							[
								"label" => $refundDetail->account != null ? $refundDetail->account->account.' '.$refundDetail->account->description : 'No hay'
							]
						],
						[
							"content" =>
							[
								"label" => $refundDetail->taxPayment == 1 ? 'si' : 'no'
							]
						],
						[
							"content" =>
							[
								"label" => '$'.number_format($refundDetail->amount,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$'.number_format($refundDetail->tax,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$'.number_format($taxes2,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$'.number_format($retentions2,2)
							]
						],
						[
							"content" =>
							[
								"label" => '$'.number_format($refundDetail->sAmount,2)
							]
						]
					];
					array_push($body, $documents);
					array_push($modelBody, $body);
					$countConcept++;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeExBody')
				id="body" 
			@endslot
			@slot('classExBody')
				text-center
			@endslot
		@endcomponent
		@php
			if($totalFinal!=0)
			{
				$subtotal = number_format($subtotalFinal,2);
			}
			if($totalFinal!=0)
			{
				$iva = number_format($ivaFinal,2);
			}
			if(isset($request))
			{
				foreach($request->refunds->first()->refundDetail as $detail)
				{
					$taxes += $detail->taxes->sum('amount');
				}
			}
			if(isset($request))
			{
				foreach($request->refunds->first()->refundDetail as $detail)
				{
					$retentions += $detail->retentions->sum('amount');
				}
			}
			if($totalFinal!=0)
			{
				$total = number_format($totalFinal,2);
			}
			$modelTable = 
			[
				[
					"label"    => "SUBTOTAL",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => "$".$subtotal,
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "subtotal",
							"attributeEx" => "id=\"subtotal\" placeholder=\"Ingrese el subtotal\" type=\"hidden\" value=\"".$subtotal."\" name=\"subtotal\"",
						]
					]
				],
				[
					"label"    => "IVA",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => "$".$iva,
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "subtotal",
							"attributeEx" => "id=\"iva\" placeholder=\"Ingrese el iva\" type=\"hidden\" value=\"".$iva."\" name=\"iva\"",
						]
					]
				],
				[
					"label"    => "IMPUESTO ADICIONAL",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => "$".number_format($taxes,2),
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "amountAA",
							"attributeEx" => "id=\"amountAA\" placeholder=\"Ingrese el impuesto\" type=\"hidden\" value=\"".number_format($taxes,2)."\" name=\"amountAA\"",
						]
					]
				],
				[
					"label"    => "RETENCIONES",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => "$".number_format($retentions,2),
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "amountRetentions",
							"attributeEx" => "id=\"amountRetentions\" placeholder=\"Ingrese una retencion\" type=\"hidden\" value=\"".number_format($retentions,2)."\" name=\"amountRetentions\"",
						]
					]
				],
				[
					"label"    => "TOTAL",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => "$".$total,
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "total",
							"attributeEx" => "id=\"total\" placeholder=\"Ingrese el subtotal\" type=\"hidden\" value=\"".$subtotal."\" name=\"total\"",
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details',[
			"modelTable" => $modelTable,			
		]) @endcomponent
		@if($request->idCheck != "")
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE REVISIÓN
			@endcomponent
			@component("components.tables.table-request-detail.container",["variant" => "simple"])
				@slot('classEx')mt-4 @endslot
				@php
					$modelTable = [];
					$modelTable ['Revisó'] = $request->reviewedUser->fullName();
				@endphp
				@if($request->idEnterpriseR!="")
					@php
						$reviewAccount = App\Account::find($request->accountR);
						$modelTable ['Nombre de la Empresa']    = $request->idEnterpriseR != null ? App\Enterprise::find($request->idEnterpriseR)->name : "No hay";
						$modelTable ['Nombre de la Dirección']  = $request->reviewedDirection->name != null ? $request->reviewedDirection->name : "No hay";
						$modelTable ['Nombre del Departamento'] = $request->idDepartamentR != null ? App\Department::find($request->idDepartamentR)->name : "No hay";
						$modelTable ['Clasificación del gasto'] = isset($reviewAccount->account) ? $reviewAccount->account."-".$reviewAccount->description : "Varias";
						$modelTable ['Nombre del Proyecto']     = $request->reviewedProject->proyectName != null ? $request->reviewedProject->proyectName : "No hay";
						$labels = "";
						foreach($request->labels as $label)
						{
							$labels = $labels." ".$label->description."," ;
						}
						$modelTable ['Etiquetas']   = $labels;
						$modelTable ['Comentarios'] = $request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment);
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
				@endif
			@endcomponent
			@if($request->idEnterpriseR!="")
				@component('components.labels.title-divisor')
					@slot('classEx')
						mt-12
					@endslot
					ETIQUETAS Y RECLASIFICACIÓN ASIGNADA
				@endcomponent
				@php
					$body      = [];
					$modelBody = [];
					$modelHead = 
					[
						[
							["value" => "#"],
							["value" => "Concepto"],
							["value" => "Clasificación de gasto"],
							["value" => "Etiquetas"]
						]
					];
					$subtotalFinal = $ivaFinal = $totalFinal = 0;
					if(isset($request))
					{
						$countConcept = 1;
						foreach(App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
						{
							$labelsRefund = "";
							foreach($refundDetail->labels as $label)
							{
								$labelsRefund = $labelsRefund.$label->label->description.", ";
							}
							$body =
							[
								[
									"content" => 
									[
										"label" => $countConcept
									]
								],
								[
									"content" => 
									[
										"label" => htmlentities($refundDetail->concept),
									]
								],
								[
									"content" =>
									[
										"label" => $refundDetail->accountR->account."-".$refundDetail->accountR->description
									]
								],
								[
									"content" =>
									[
										"label" => $labelsRefund
									]
								]
							];
							array_push($modelBody, $body);
							$countConcept++;
						}
					}
				@endphp
				@component('components.tables.table',[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody,
					"themeBody" => "striped"
				])
					@slot('attributeEx')
						id="table"
					@endslot
					@slot('attributeExBody')
						id="body" 
					@endslot
					@slot('classExBody')
						text-center
					@endslot
				@endcomponent
			@endif
		@endif
		@if($request->idAuthorize != "")
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				DATOS DE AUTORIZACIÓN
			@endcomponent
			@component("components.tables.table-request-detail.container",["variant" => "simple"])
				@slot('classEx')mt-4 @endslot
				@php
					$modelTable ['Autorizó'] 		= $request->authorizedUser->fullName();
					$modelTable ['Comentarios']     = $request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment);
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
				@endcomponent
			@endcomponent
		@endif
		@php
			$payments       = App\Payment::where('idFolio',$request->folio)->get();
			$total          = $request->refunds->first()->total;
			$iva            = $request->refunds->first()->refundDetail()->sum('tax');
			$subtotal       = $request->refunds->first()->refundDetail()->sum('amount');
			$totalPagado    = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount') : 0;
			$subtotalPagado = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal') : 0;
			$ivaPagado      = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva') : 0;
		@endphp
		@if(count($payments) > 0)
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				Historial de pagos
			@endcomponent
			@php
				$body      = [];
				$modelBody = [];
				$modelHead = 
				[
					[
						["value" => "Cuenta"],
						["value" => "Cantidad"],
						["value" => "Fecha"],
						["value" => "Documento"],
					]
				];
				if(isset($payments))
				{
					foreach($payments as $pay)
					{
						$documentsPayTd = [];
						foreach($pay->documentsPayments as $doc)
						{
							$documentsPayTd["content"] = 
								[
									"kind"          => "components.buttons.button", 
									"label"         => "PDF", 
									"buttonElement" => "a",
									"variant"       => "dark-red",
									"attributeEx"   => "target=\"_blank\"href=\"".url('docs/payments/'.$doc->path)."\""
								];
						}
						$body = 
						[
							[
								"content" => 
								[
									"label" => $pay->accounts->account.' - '.$pay->accounts->description
								]
							],
							[
								"content" => 
								[
									"label" => '$'.number_format($pay->amount,2)
								]
							],
							[
								"content" =>
								[
									"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y'),
								]
							]
						];
						if(count($documentsPayTd) > 0)
						{
							array_push($body, $documentsPayTd);
						}
						else
						{
							array_push($body, 
								[
									"content" =>
									[
										"label" => "No hay documento"
									]
								]
							);
						}
						array_push($modelBody, $body);
						$countConcept++;
					}
				}
			@endphp
			@component('components.tables.table',
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
				@slot('classEx')
					mt-5
				@endslot
			@endcomponent
			@php
				$modelTable =
				[
					"Total pagado " => "$".number_format($totalPagado,2),
					"Resta "        => "$".number_format(($total)-$totalPagado,2)
				];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])
				@slot('classEx')
					mt-5
				@endslot
			@endcomponent
		@endif
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type  = "hidden" 
				id    = "restaTotal" 
				value = "{{ round(($total)-$totalPagado,2) }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type  = "hidden" 
				id    = "restaSubtotal" 
				value = "{{ round(($subtotal)-$subtotalPagado,2) }}"
			@endslot
		@endcomponent
		@component('components.inputs.input-text')
			@slot('attributeEx')
				type  = "hidden" 
				id    = "restaIva" 
				value = "{{ round(($iva)-$ivaPagado,2) }}"
			@endslot
		@endcomponent
	</div>
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'dd-mm-yy',
				});
			});
			doc = {{ $docs }};
			$(document).on('click','#save',function()
			{
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('click','.btn-delete-form',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title		: "Limpiar formulario",
					text		: "¿Confirma que desea limpiar el formulario?",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body').html('');
						$('.removeselect').val(null).trigger('change');
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
		});
		function total_cal()
		{
			subtotal	= 0;
			ivaTotal	= 0;
			$("#body tr").each(function(i, v)
			{
				ivaTotal	+= Number($(this).find('.t-iva').val());
				subtotal	+= Number($(this).find('.t-amount').val());
			});
			total	= subtotal+ivaTotal;
			$(".subtotal").val('$ '+Number(subtotal).toFixed(2));
			$(".ivaTotal").val('$ '+Number(ivaTotal).toFixed(2));
			$(".total").val('$ '+Number(total).toFixed(2));
		}
	</script>
@endsection
