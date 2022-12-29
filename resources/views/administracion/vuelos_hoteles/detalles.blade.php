@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	<div class="pb-6">
		@php
			$modelTable =
			[
				["Folio", $requests_flight->folio_request],
				["Título y fecha", htmlentities($requests_flight->title).' - '.Carbon\Carbon::parse($requests_flight->datetitle)->format('d-m-Y')],
				["Solicitante", $requests_flight->request->requestUser->fullName()],
				["Elaborado por", $requests_flight->request->elaborateUser->fullName()],
				["Empresa", $requests_flight->request->enterprise->name],
				["Dirección", isset($requests_flight->request->requestDirection->name) ? $requests_flight->request->requestDirection->name : 'No se selccionó área'],
				["Departamento", $requests_flight->request->requestDepartment->name],
				["Proyecto", isset($requests_flight->request->requestProject) ? $requests_flight->request->requestProject->proyectName : 'No se selccionó proyecto'],
				["WBS", $requests_flight->request->wbs()->exists() ? $requests_flight->request->wbs->code_wbs : 'No se selccionó WBS'],
				["EDT", $requests_flight->request->edt()->exists() ? $requests_flight->request->edt->description : 'No se selccionó EDT']
			];
		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud"])@endcomponent	
	</div>
	
   	<div class="mt-6">
	   	@component("components.labels.title-divisor")
			DETALLES DEL VUELO
		@endcomponent
		@if(isset($requests_flight))
			@php
				$passanger_number	= 0;
				$countFlight		= 0;
			@endphp
			@foreach($requests_flight->details as $detail)
				@php
					$tablePassenger = 
					[
						["Nombre:", htmlentities($detail->passenger_name)],
						["Descripción/Motivo del viaje:", htmlentities($detail->journey_description)],
						["Cargo:", !empty($detail->job_position) ? htmlentities($detail->job_position)  : 'No hay cargo'],
						["Fecha de nacimiento:", Carbon\Carbon::createFromFormat('Y-m-d', $detail->born_date)->format('d-m-Y')],
						["Jefe directo:", htmlentities($detail->direct_superior)],
						["Último viaje familiar:", Carbon\Carbon::createFromFormat('Y-m-d', $detail->last_family_journey_date)->format('d-m-Y')],
						["Tipo de vuelo:", $detail->typeFlightData()],
						["Equipaje documentado:", !empty($detail->checked_baggage) ? htmlentities($detail->checked_baggage)  : 'No'],
						["Hospedaje:", !empty($detail->hosting) ? htmlentities($detail->hosting)  : 'No'],
						["Fecha de ingreso:", !empty($detail->singin_date) ? Carbon\Carbon::createFromFormat('Y-m-d', $detail->singin_date)->format('d-m-Y')  : '---'],
						["Fecha de salida:", !empty($detail->output_date) ? Carbon\Carbon::createFromFormat('Y-m-d', $detail->output_date)->format('d-m-Y')  : '---']
					];

					$tableFlightI =
					[
						["Aereolínea:", htmlentities($detail->airline)],
						["Ruta:", htmlentities($detail->route)],
						["Fecha de salida:", Carbon\Carbon::createFromFormat('Y-m-d', $detail->departure_date)->format('d-m-Y')],
						["Hora de salida:", $detail->departure_hour]
					];

					
					$tableTotal =
					[
						[
							"label" => "Subtotal:", 
							"inputsEx" => 
							[
								
								[
									"kind" => "components.labels.label",
									"label" => "$".(number_format($detail->subtotal,2)),
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
									"label" => "$".(number_format($detail->iva,2)),
									"classEx" => "h-10 py-2"
								]

							]
						],
						[
							"label" => "Impuesto adicional:", 
							"inputsEx" => 
							[
								
								[
									"kind" => "components.labels.label",
									"label" => "$".(number_format($detail->taxes,2)),
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
									"label" => "$".(number_format($detail->retentions,2)),
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
									"label" => "$".(number_format($detail->total,2)),
									"classEx" => "h-10 py-2"
								]

							]
						],
					];

					if(isset($detail->airline_back, $detail->route_back, $detail->departure_date_back, $detail->departure_hour_back))
					{
						$tableFlightR =
						[
							["Aereolínea		:", $detail->airline_back],
							["Ruta				:", $detail->route_back],
							["Fecha de salida	:", Carbon\Carbon::createFromFormat('Y-m-d', $detail->departure_date_back)->format('d-m-Y')],
							["Hora de salida	:", $detail->departure_hour_back]
						];
					}
				@endphp
				@php
					$passanger_number ++;
				@endphp
			@endforeach

			@if(isset($tablePassenger))
				@component("components.templates.outputs.table-detail", ["modelTable" => $tablePassenger, "title" => "Pasajero ".$passanger_number, "classEx" => "mt-6"]) @endcomponent
			@endif

			@if(isset($tableFlightI))
				@component("components.templates.outputs.table-detail", ["modelTable" => $tableFlightI, "title" => "Datos del vuelo (Ida)", "classEx" => "mt-6"]) @endcomponent
			@endif

			@if(isset($tableFlightI))
				@component("components.templates.outputs.form-details", ["modelTable" => $tableTotal, "title" => "", "classEx" => "mt-6"]) @endcomponent
			@endif	
			
			@if(isset($detail->airline_back, $detail->route_back, $detail->departure_date_back, $detail->departure_hour_back))
				@component("components.templates.outputs.table-detail", ["modelTable" => $tableFlightR, "title" => "Datos del vuelo (Vuelta)", "classEx" => "mt-6"]) @endcomponent
			@endif
			
		@endif
	</div>
	<div class="mt-6">
		@component("components.labels.title-divisor") Costos totales @endcomponent
		@php
			$tableTotals =
			[
				[
					"label" => "Subtotal:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"subtotal_flight\" placeholder=\"$0.00\" value=\"".number_format($requests_flight->subtotal,2)."\""
						],
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($requests_flight->subtotal,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "IVA:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"iva_flight\" placeholder=\"$0.00\" value=\"".number_format($requests_flight->iva,2)."\""
						],
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($requests_flight->iva,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Impuesto adicional:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"taxes_flight\" placeholder=\"$0.00\" value=\"".number_format($requests_flight->taxes,2)."\""
						],
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($requests_flight->taxes,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Retenciones:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"retentions_flight\" placeholder=\"$0.00\" value=\"".number_format($requests_flight->retentions,2)."\""
						],
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($requests_flight->retentions,2),
							"classEx" => "h-10 py-2"
						]

					]
				],
				[
					"label" => "Total:", 
					"inputsEx" => 
					[
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"total_flight\" placeholder=\"$0.00\" value=\"".number_format($requests_flight->total,2)."\""
						],
						[
							"kind" => "components.labels.label",
							"label" => "$".number_format($requests_flight->total,2),
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
			$modelTable = ["Forma de pago" => $requests_flight->paymentMethodData->method,
			"Referencia" => htmlentities($requests_flight->reference),
			"Tipo de moneda" => $requests_flight->currency,
			"Estado de factura" => $requests_flight->bill_status,
			"Importe" => "$".number_format($requests_flight->details->sum('total'),2)];
		@endphp
		@component("components.templates.outputs.table-detail-single",["modelTable" => $modelTable])@endcomponent
	</div>

	<div class="col-span-2 md:col-span-4 table-striped">
		@if(isset($requests_flight) && $requests_flight->documents()->exists())
			@component("components.labels.title-divisor")
				Documentos de la solicitud
			@endcomponent

			@php
				$documentsBody = [];
				$modelHead = ["Tipo de documento", "Archivo", "Modificado por", "Fecha"];
				foreach ($requests_flight->documents as $document)
				{
					$dates	= strtotime($document->date);
					$date	= date('d-m-Y H:i', $dates);

					$row =
					[
						"classEx" => "tr",
						[
							"content" => 
							[
								["kind" => "components.labels.label", "label" => $document->name ]
							]
						],
						[
							"content" => 
							[
								[
									"kind"			=> "components.buttons.button",
									"variant"		=> "secondary",
									"buttonElement"	=> "a",
									"attributeEx"	=> "target=\"_blank\" title=\"".$document->path."\"".' '."href=\"".asset('docs/flights_lodging/'.$document->path)."\"",
									"label"			=> "Archivo"
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"flight_document_id[]\" value=\"".$document->id."\""
								]
							]
						],
						[
							"content" => 
							[
								["kind" => "components.labels.label", "label" => $document->userData->fullName() ]
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

	@if(isset($requests_flight) && $requests_flight->request->idCheck != "")
		<div class="mt-10">
			@component("components.labels.title-divisor")
				DATOS DE REVISIÓN
			@endcomponent
			<div class="my-6">
				@component("components.tables.table-request-detail.container",["variant"=>"simple"])
					@php
						$reviewDate = Carbon\Carbon::parse($requests_flight->request->reviewDate)->format('d-m-Y');
						$modelTable = 
						[
							"Revisó"			=> $requests_flight->request->reviewedUser->fullName(),
							"Fecha de revisión"	=> $reviewDate,
							"Comentarios"		=> $requests_flight->request->checkComment == "" ? "Sin comentarios" : htmlentities($requests_flight->request->checkComment)
						];
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
				@endcomponent
			</div>
		</div>
	@endif
	@if(isset($requests_flight) && $requests_flight->request->idAuthorize != "")
		<div class="mt-10">
			@component("components.labels.title-divisor")
				DATOS DE AUTORIZACIÓN
			@endcomponent
			<div class="my-6">
				@component("components.tables.table-request-detail.container",["variant"=>"simple"])
					@php
						$authorizeDate = Carbon\Carbon::parse($requests_flight->request->authorizeDate)->format('d-m-Y');
						$modelTable = 
						[
							"Autorizó"			=> $requests_flight->request->authorizedUser->fullName(),
							"Fecha de autorización"	=> $authorizeDate,
							"Comentarios"		=> $requests_flight->request->authorizeComment == "" ? "Sin comentarios" : $requests_flight->request->authorizeComment
						];
					@endphp
					@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])@endcomponent
				@endcomponent
			</div>
		</div>
	@endif
	
	@if($option_id != 286)
		@component("components.forms.form",["attributeEx" => "method=\"POST\" action=\"".route("flights-lodging.changeStatus", ["submodule" => $option_id])."\" id=\"container-alta\""])
			<div class="my-4">	
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="hidden"  
						name="folio" 
						value="{{ $requests_flight->folio_request }}" 
					@endslot
				@endcomponent
				@component("components.containers.container-approval")
					@slot('textLabel')
						@if($option_id == 287) 
							¿Desea aprobar ó rechazar la solicitud? 
						@elseif($option_id == 288) 
							¿Desea autorizar ó rechazar la solicitud? 
						@endif
					@endslot
					@if($option_id == 287)
						@slot("attributeExButton")
							name="status"
							id="aprobar"
							value="4"
						@endslot
						@slot("classExButton")
							approve
						@endslot
						@slot("attributeExButtonTwo")
							name="status"
							id="rechazar"
							value="6"
						@endslot
						@slot("classExButtonTwo")
							refuse
						@endslot
					@elseif($option_id == 288)
						@slot("attributeExButton")
							name="status"
							id="aprobar"
							value="5"
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
					@endif
				@endcomponent
			</div>		
			<div id="aceptar">
				@component("components.labels.label")
					Comentarios (opcional) 
				@endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						name="comment"
						id="comment"
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
	@endif
@endsection

