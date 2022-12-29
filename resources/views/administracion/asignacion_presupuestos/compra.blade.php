@section('data')
	@php
		$taxes      = 0;
		$retentions = 0;
		$user       = App\User::find($request->idRequest);
		$enterprise = App\Enterprise::find($request->idEnterprise);
		$area       = App\Area::find($request->idArea);
		$department = App\Department::find($request->idDepartment);
		$account    = App\Account::find($request->account);
		$state      = App\State::find($request->purchases->first()->provider->state_idstate);
		$project    = App\Project::find($request->idProject);
	@endphp
	@if($request->purchases->first()->idRequisition != "")
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<span class="icon-bullhorn"></span> Esta solicitud viene de la requisición #{{ $request->purchases->first()->idRequisition }}. 
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
				@if($request->purchases->first()->requisitionRequest->idProject == 75)
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
							{{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}.
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
							{{ $request->purchases->first()->requisitionRequest->requisition->edt()->exists() ? $request->purchases->first()->requisitionRequest->requisition->edt->fullName() : '' }}.
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
		$dateTitle = $request->purchases->first()->datetitle != "" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->purchases->first()->datetitle)->format('d-m-Y') : "";
		$modelTable = 
			[
				["Folio: 			", $request->folio],
				["Título y fecha: 	", htmlentities($request->purchases->first()->title)." - ".$dateTitle],
				["Número de Orden: 	", $request->purchases->first()->numberOrder != "" ? $request->purchases->first()->numberOrder: '---'],
				["Fiscal: 			", $request->taxPayment == 0 ? "No": "Sí"],
				["Solicitante:		", $request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
				["Elaborado por:	", $request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""]
			];
	@endphp
	@component("components.templates.outputs.table-detail", 
	[
		"modelTable" => $modelTable,
		"title"      => "Detalles de la Solicitud"
	]) 
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		Datos del proveedor
	@endcomponent
	<div class="px-4 md:px-8">
		@component("components.tables.table-request-detail.container",["variant" => "simple"])
			@php
				$modelTable =
					[
						"Razón Social " => $request->purchases->first()->provider->businessName,
						"RFC " 			=> $request->purchases->first()->provider->rfc,
						"Teléfono " 	=> $request->purchases->first()->provider->phone,
						"Calle " 		=> $request->purchases->first()->provider->address,
						"Número "		=> $request->purchases->first()->provider->number,
						"Colonia " 		=> $request->purchases->first()->provider->colony,
						"CP " 			=> $request->purchases->first()->provider->postalCode,
						"Ciudad " 		=> $request->purchases->first()->provider->city,
						"Estado "		=> App\State::find($request->purchases->first()->provider->state_idstate)->description,
						"Contacto " 	=> $request->purchases->first()->provider->contact,
						"Beneficiario " => $request->purchases->first()->provider->beneficiary,
						"Otro " 		=> $request->purchases->first()->provider->commentaries
					];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
		@endcomponent
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = 
				[
					[
						["value" => "Banco"],
						["value" => "Alias"],
						["value" => "Cuenta"],
						["value" => "Sucursal"],
						["value" => "Referencia"],
						["value" => "CLABE"],
						["value" => "Moneda"],
						["value" => "Convenio"],
					]
				];
			if(isset($request))
			{
				foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
				{
					$classEx = "";
					if ($request->purchases->first()->provider_has_banks_id == $bank->id) 
					{
						$classEx = "marktr";
					}
					$body = 
					[
						"classEx" => $classEx,
						[
							"content" => 
							[ 
								"label" => $bank->bank->description == '' ? '---' : $bank->bank->description
							]
						],
						[
							"content" =>
							[
								"label" => $bank->alias == '' ? '---' : htmlentities($bank->alias),
							]
						],
						[
							"content" => 
							[
								"label" => $bank->account == '' ? '---' : $bank->account
							]
						],
						[
							"content" => 
							[
								"label" => $bank->branch == '' ? '---' : $bank->branch
							]
						],
						[
							"content" => 
							[
								"label" => $bank->reference == '' ? '---' : $bank->reference
							]
						],
						[
							"content" => 
							[
								"label" => $bank->clabe == '' ? '---' : $bank->clabe
							]
						],
						[
							"content" => 
							[
								"label" => $bank->currency == '' ? '---' : $bank->currency
							]
						],
						[
							"content" => 
							[ 
								"label" => $bank->agreement == '' ? '---' : $bank->agreement 
							]
						],
					];
					$modelBody [] = $body;
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('classEx')
				mt-5
			@endslot
			@slot('classExBody')
				text-center
			@endslot
		@endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			Datos del pedido
		@endcomponent
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "#"],
					["value" => "Cantidad"],
					["value" => "Unidad"],
					["value" => "Descripci&oacute;n"],
					["value" => "Precio Unitario"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Retenciones"],
					["value" => "Importe"]
				]
			];
			if(isset($request))
			{
				$countConcept = 1;
				foreach($request->purchases->first()->detailPurchase as $detail)
				{
					$taxesConcept     = $detail->taxes->sum('amount');
					$retentionConcept = $detail->retentions->sum('amount');
					$body             =
					[
						"classEx"=>"tr",
						[
							"content" => 
							[
								"label" => $countConcept
							]
						],
						[
							"content" => 
							[
								"label" => $detail->quantity
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
								"label" => htmlentities($detail->description),
							]
						],
						[
							"content" =>
							[
								"label" => "$".$detail->unitPrice
							]
						],
						[
							"content" =>
							[
								"label" => "$".$detail->tax
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
								"label" => "$".number_format($detail->amount,2)
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
			@slot('classEx')
				mt-5
			@endslot
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('classExBody')
				text-center
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
		@endcomponent
		@php
			$subtotal   = isset($request) ? "$".number_format($request->purchases->first()->subtotales,2): "";
			$iva        = isset($request) ? "$".number_format($request->purchases->first()->tax,2)       : "";
			$total      = isset($request) ? "$".number_format($request->purchases->first()->amount,2)    : "";
			$textNotes  = isset($request) ? $request->notes : "";
			$notas      = "name=\"note\"";
			$taxes      = 0;
			$retentions = 0;
			if(isset($request))
			{
				foreach($request->purchases->first()->detailPurchase as $detail)
				{
					$taxes += $detail->taxes->sum('amount');
				}
				foreach($request->purchases->first()->detailPurchase as $detail)
				{
					$retentions += $detail->retentions->sum('amount');
				}
			}
			$aditionalTaxes = isset($request) ? "$".number_format($taxes,2) : "";
			$retentions     = isset($request) ? "$".number_format($retentions,2) : "";	
			$modelTable     =
			[
				[
					"label"	=>	"Subtotal: ",
					"inputsEx" =>
					[
						[
							"kind"  => "components.labels.label",
							"label" => $subtotal,
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "value=\"".$subtotal."\" name=\"asubtotal\" type=\"hidden\"  readonly",
							"classEx"     => "hidden",
						]
					]
				],
				[
					"label"    => "Impuesto Adicional: ",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $aditionalTaxes,
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "value=\"".$aditionalTaxes."\" name=\"amountAA\" type=\"hidden\"  readonly",
							"classEx"     => "hidden",
						]
					]
				],
				[
					"label"    => "Retenciones: ",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $retentions,
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "value=\"".$retentions."\" name=\"amountR\" type=\"hidden\"  readonly",
							"classEx"     => "hidden",
						]
					]
				],
				[
					"label"    => "IVA: ",
					"inputsEx" =>
					[
						[
							"kind"  => "components.labels.label",
							"label" => $iva,
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "value=\"".$iva."\" name=\"totaliva\" type=\"hidden\"  readonly",
							"classEx"     => "hidden",
						]
					]
				],
				[
					"label"    => "TOTAL: ",
					"inputsEx" => 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $total,
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "value=\"".$total."\" name=\"asubtotal\" type=\"hidden\"  readonly",
							"classEx"     => "hidden",
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details',[
			"modelTable"         => $modelTable,
			"attributeExComment" => htmlentities($notas),
		])
		@endcomponent
		@component('components.labels.title-divisor') Condiciones de pago @endcomponent
		@php
			$modelTable =
			[
				"Referencia/Número de factura " => ($request->purchases->first()->reference != "" ? htmlentities($request->purchases->first()->reference) : "---"),
				"Tipo de moneda "               => $request->purchases->first()->typeCurrency,
				"Fecha de pago "                => isset($request) && $request->PaymentDate != "" ? $request->PaymentDate->format('d-m-Y') : "---",
				"Forma de pago "                => ($request->purchases->first()->paymentMode != "" ? $request->purchases->first()->paymentMode : "---"),
				"Estatus de factura "           => ($request->purchases->first()->billStatus != "" ? $request->purchases->first()->billStatus : "---"),
				"Importe a pagar "              => "$".number_format($request->purchases->first()->amount,2)
			];
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			Documentos
		@endcomponent
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = ["Tipo de Documento","Folio Fiscal/#Ticket", "Archivo", "Fecha"];
			if(count($request->purchases->first()->documents) > 0)
			{
				foreach($request->purchases->first()->documents as $doc)
				{
					$body = 
					[
						[
							"content" =>
							[
								"label" => $doc->name
							]
						],
						[
							"content" =>
							[
								"label" => $doc->fiscal_folio != "" ? $doc->fiscal_folio : ($doc->ticket_number != "" ? $doc->ticket_number : "")
							]
						],
						[
							"content" =>
							[
								[
									"kind"          => "components.buttons.button", 
									"label"         => "PDF",
									"buttonElement" => "a",
									"variant"       => "dark-red",
									"attributeEx"   => "target=\"_blank\" href=\"".url('docs/purchase/'.$doc->path)."\""
								]
							]
						],
						[
							"content" =>
							[
								"label" => $doc->date->format('d-m-Y')
							]
						]
					];
					array_push($modelBody, $body);
					$countConcept++;
				}
			}
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('classExBody')
				text-center
			@endslot
		@endcomponent
		@if($request->reviewDate != '')
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				Datos de revisión
			@endcomponent
			@php
				$reviewAccount = App\Account::find($request->accountR);
				$time          = strtotime($request->PaymentDate);
				$date          = date('d-m-Y',$time);
				$modelTable    = 
				[
					"Revisó "                  => $request->reviewedUser->fullName(),
					"Nombre de la Empresa "    => App\Enterprise::find($request->idEnterpriseR)->name,
					"Nombre de la Dirección "  => $request->reviewedDirection->name,
					"Nombre del Departamento " => App\Department::find($request->idDepartamentR)->name,
					"Clasificación del gasto " => isset($reviewAccount->account) ? $reviewAccount->account." - ".$reviewAccount->description: "No hay",
					"Nombre del Proyecto "     => $request->reviewedProject->proyectName,
					"Comentarios "             => $request->checkComment == "" ? "Sin comentarios": htmlentities($request->checkComment),
				];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
		@endif
		@if($request->idEnterpriseR!="")
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				Etiquetas asignadas
			@endcomponent
			@php
				$body      = [];
				$modelBody = [];
				$modelHead =
				[
					[
						["value" => "#"],
						["value" => "Cantidad"],
						["value" => "Descripción"],
						["value" => "Etiquetas"]
					]
				];
				if(isset($request))
				{
					$countConcept = 1;
					foreach($request->purchases->first()->detailPurchase as $detail)
					{
						$labelsDetail = "";
						foreach($detail->labels as $label)
						{
							$labelsDetail = $labelsDetail." ".$label->label->description.",";
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
									"label" => $labelsDetail
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
				@slot('classEx')
					mt-5
				@endslot
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('classExBody')
					request-validate text-center
				@endslot
				@slot('attributeExBody')
					id="tbody-conceptsNew"
				@endslot
			@endcomponent
		@endif
		@if($request->idAuthorize != "")
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				Datos de autorización
			@endcomponent
			@php
				$reviewAccount = App\Account::find($request->accountR);
				$modelTable =
				[
					"Autorizó "    => $request->authorizedUser->fullName(),
					"Comentarios " => $request->authorizeComment == "" ? "Sin comentarios": $request->authorizeComment
				];
			@endphp
			@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
		@endif
		@php
			$payments       = App\Payment::where('idFolio',$request->folio)->get();
			$subtotal       = $request->purchases->first()->subtotales;
			$iva            = $request->purchases->first()->tax;
			$total          = $request->purchases->first()->amount;
			$totalPagado    = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('amount_real') : 0;
			$subtotalPagado = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('subtotal_real') : 0;
			$ivaPagado      = $request->paymentsRequest()->exists() ? $request->paymentsRequest->sum('iva_real') : 0;
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
						["value" => "Documento"]
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script> 
	<script src="{{ asset('js/datepicker.js') }}"></script> 
	<script type="text/javascript">
		function total_cal()
		{
			subtotal  = 0;
			iva       = 0;
			descuento = Number($('input[name="descuento"]').val());
			$("#body .tr").each(function(i, v)
			{
				tempQ    =  $(this).find('.tquanty').val();
				tempP    =  $(this).find('.tprice').val();
				subtotal += Number(tempQ)*Number(tempP);
				iva      += Number($(this).find('.tiva').val());
			});
			total = (subtotal+iva) - descuento;
			$('input[name="subtotal"]').val('$ '+Number(subtotal).toFixed(2));
			$('input[name="totaliva"]').val('$ '+Number(iva).toFixed(2));
			$('input[name="total"]').val('$ '+Number(total).toFixed(2));
			$(".amount_total").val('$ '+Number(total).toFixed(2));
		}
	</script>
@endsection