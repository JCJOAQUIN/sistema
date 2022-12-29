@if(isset($partialPayments) || $editable)
	@component('components.labels.title-divisor') Programa de pagos @endcomponent
	@if(!$editable && isset($partialPayments) && count($partialPayments) == 0)
		@php
			$body 			= [];
			$modelBody		= [];
			
			$modelHead = ["Monto", "Porcentaje/Neto", "Fecha de Pago"];
		@endphp
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
		@endcomponent
	@endif
	@component('components.labels.not-found', ["text" => "No se han encontrado programas de pagos registrados"])
			@slot("classEx")
				not-found-payments
				@if(isset($partialPayments) && count($partialPayments) > 0) hidden @endif
			@endslot
	@endcomponent
	<div class="form-container @if(!isset($partialPayments) || (isset($partialPayments) && count($partialPayments) == 0)) hidden @endif py-6" id="partialForms">
		@php
			$body 			= [];
			$modelBody		= [];
			if($editable)
			{
				$modelHead = 
				[
					[
						["value" => "Parcialidad"],
						["value" => "Monto"],
						["value" => "Fecha pago"],
						["value" => "Estado"],
						["value" => "Documento(s)"],
						["value" => "Acciones"]
					]
				];
			}
			else
			{
				$modelHead = 
				[
					[
						["value" => "Parcialidad"],
						["value" => "Monto"],
						["value" => "Fecha pago"],
						["value" => "Estado"],
						["value" => "Documento(s)"]
					]
				];
			}
			if(isset($partialPayments))
			{
				foreach ($partialPayments as $item)
				{
					$state = 0;
					if($item->date_delivery != null)
					{
						$state = 1;
					}

					$partialPaymentValue = "";
					if($item['tipe'])
					{
						$partialPaymentValue = "$ ".$item['payment'];
					}
					else 
					{
						$partialPaymentValue = $item['payment']."%";
					}
					$contentDocs = [];
					if(count($partialPayments) > 0)
					{
						$docs_counter = $i;
						foreach ($item->documentsPartials as $doc)
						{
							$containerButton = "";
							$containerButton .= '<div class="grid grid-cols-3 nowrap">';
							$containerButton .= '<div class="col-span-1">'.view('components.buttons.button',[																
								"buttonElement" => "a",
								"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset('docs/purchase/'.$doc->path)."\"",
								"variant" => "secondary",
								"label" => "<span class='icon-file'></span>",
							])->render().'</div>';
							$containerButton .= '<div class="col-span-2 grid-rows-2"> 
													<div>'.view('components.labels.label',[																
																"label" => $doc->name,
															])->render().
													'</div> 
													<div>'.view('components.labels.label',[																
															"label" => Carbon\Carbon::createFromFormat('Y-m-d', $doc->datepath)->format('d-m-Y'),
														])->render().
													'</div>
												</div>';
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"path_p".$docs_counter."[]\" value=\"".$doc->path."\"",
								"classEx"		=> "path_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"name_p".$docs_counter."[]\" value=\"".$doc->name."\"",
								"classEx"		=> "name_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"folio_p".$docs_counter."[]\" value=\"".$doc->fiscal_folio."\"",
								"classEx"		=> "folio_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"ticket_p".$docs_counter."[]\" value=\"".$doc->ticket_number."\"",
								"classEx"		=> "ticket_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"monto_p".$docs_counter."[]\" value=\"".$doc->amount."\"",
								"classEx"		=> "monto_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"timepath_p".$docs_counter."[]\" value=\"".Carbon\Carbon::createFromFormat('H:i:s', $doc->timepath)->format('H:i')."\"",
								"classEx"		=> "timepath_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"datepath_p".$docs_counter."[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d', $doc->datepath)->format('d-m-Y')."\"",
								"classEx"		=> "datepath_p"
							])->render();
							$containerButton .= view('components.inputs.input-text',[																
								"attributeEx" 	=> "type=\"hidden\" name=\"num_p".$docs_counter."[]\" value=\"0\"",
								"classEx"		=> "num_p"
							])->render();
							$containerButton .= '</div>';
							$contentDocs [] =
							[
								"label" => $containerButton,
							];
						}
						if(count($contentDocs) == 0)
						{
							$contentDocs [] =
							[
								"label" => "---",
							];	
						}
					}
					else 
					{
						$contentDocs [] =
						[
							"label" => "---",
						];	
					}
					// En duda por si se ocupa
					// $value = "";
					// if(isset($item['state']))
					// {
					// 	$value='Pagado';
					// }
					// else
					// {
					// 	$value='Sin pagar';
					// }
					$body = 
					[
						"classEx" => "trPartial",
						[
							"content" 	=> 
							[
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_id",
									"attributeEx" => "value=\"".$item['id']."\" name=\"partial_id[]\" type=\"hidden\""
								],
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial numPartial",
									"attributeEx" => "type=\"hidden\" value=\"".$i."\"",
								],
								[
									"kind"	=> "components.labels.label",
									"label"	=> $i,
								],
							]
						],
						[ 
							"content" => 
							[
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_payment",
									"attributeEx" => "name=\"partial_payment[]\" type=\"hidden\" value=\"".$item['payment']."\""
								],
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_type",
									"attributeEx" => "name=\"partial_type[]\" type=\"hidden\" value=\"".$item['tipe']."\""
								],
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_paymentText",
									"attributeEx" => "type=\"hidden\" value=\"".$partialPaymentValue."\"",
								],
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "partial_paymentText_label",
									"label"		=> $partialPaymentValue,
								],
							]
						],
						[
							"content" => 
							[
								[
									"kind"		=> "components.labels.label",
									"classEx"	=> "partial_date_label",
									"label"		=> Carbon\Carbon::createFromFormat('Y-m-d', $item->date_requested)->format('d-m-Y'),
								],
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_date",
									"attributeEx" => "type=\"hidden\" name=\"partial_date[]\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d', $item->date_requested)->format('d-m-Y')."\"".(isset($globalRequests) ? " disabled" : ""),
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind"		=> "components.labels.label",
									"classEx" 	=> "partial_stateText partial",
									"label"		=> ($state == 1 ? "Pagado" : "Sin pagar"),
								],
								[
									"kind"        => "components.inputs.input-text",
									"classEx"     => "partial_state",
									"attributeEx" => "type=\"hidden\" value=\"".$state."\"",
								]
							]
						],
						[
							"classEx" => "contentDocs",
							"content" => $contentDocs
						]
					];
					if($editable)
					{
						if(!$item['state'] && $state == 0)
						{
							$body[] =
							[
								"content" => 
								[
									[
										"kind"        => "components.buttons.button",
										"classEx"     => "partial-edit",
										"variant"	  => "success",
										"label"		  => "<span class=\"icon-pencil\"></span>",
										"attributeEx" => "alt=\"Editar Solicitud\" type=\"button\" title=\"Editar Solicitud\"".(isset($globalRequests) ? " disabled" : ""),
									],
									[
										"kind"        => "components.buttons.button",
										"classEx"     => "partial-delete",
										"variant"	  => "red",
										"label"		  => "<span class=\"icon-x\">",
										"attributeEx" => "type=\"button\" title=\"Suspender\"".(isset($globalRequests) ? " disabled" : ""),
									]
								]
							];
						}
						else 
						{
							$body[] =
							[
								"content" => 
								[
									[
										"label" => "---",
									],
								]
							];
						}
					}
					array_push($modelBody, $body);
					$i++;
				}
			}
		@endphp
		@component('components.tables.table',[
			"classEx"			=> "table",
			"modelHead" 		=> $modelHead,
			"modelBody" 		=> $modelBody,
			"themeBody" 		=> "striped",
			"attributeExBody"	=> "id=\"bodyPartial\"",
		])
		@endcomponent
		@if($editable)
			@component('components.containers.container-form', ["attributeEx" => "id=\"programPaymentForm\""])
				<div class="col-span-2">
					@component('components.labels.label') 
						Porcentaje/Neto:
					@endcomponent
					@php
						$options = collect(
							[
								['value'=>'0', 'description'=>'Porcentaje'], 
								['value'=>'1', 'description'=>'Neto']
							]
						);
						$classEx = "partialTypePayment js-partial";
						$attributeEx = "name=\"tipe[]\"".(isset($globalRequests) ? " disabled" : "");
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') 
						Monto:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							partialPayment
						@endslot
						@slot('attributeEx')
							placeholder="Ingrese el monto o porcentaje"
							@isset($globalRequests) disabled @endisset
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') 
						Fecha de Pago:
					@endcomponent
					@component('components.inputs.input-text')
						@slot('classEx')
							partialDate
						@endslot
						@slot('attributeEx')
							id          = "datepickerPartial"
							readonly
							placeholder = "Ingrese la fecha de pago"
							@isset($globalRequests) disabled @endisset
						@endslot
					@endcomponent
				</div>
				<div class="form-group col-md-6 mb-4">
					<label  class="label-form">Resta por programar:</label>
					<label class="remainingPayment"></label>
				</div>
				<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 documents_partial"></div>
				<div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
					@component('components.buttons.button', ["variant" => "warning"])
						@slot('attributeEx') id="addDocPartial" type="button" @isset($globalRequests) disabled @endisset @endslot
						<span class="icon-plus"></span> <span>Anexar documento</span>
					@endcomponent
					@component('components.buttons.button', ["variant" => "success"])
						@slot('attributeEx') id="addNewPartialPayment" type="button" @isset($globalRequests) disabled @endisset @endslot
						<span class="icon-plus"></span> <span>Agregar</span>
					@endcomponent
				</div>
			@endcomponent
		@endif
	</div>
	@if($editable)
		@if(!isset($partialPayments) || (isset($partialPayments) && count($partialPayments) == 0))
			@component('components.buttons.button', ["variant" => "warning"])
				@slot('classEx') add2 @endslot
				@slot('attributeEx') name="activePaymentProgram" id="activePaymentProgram" type="button" @if( isset($request) && $request->status == 1) disabled="disabled" @endif @if(isset($globalRequests)) disabled @endif @endslot
				<span class="icon-plus"></span>
				<span>Agregar programa de pagos</span>
			@endcomponent
			<div class="text-center">
				@component('components.buttons.button', ['variant' => 'red'])
					@slot('attributeEx') type="button" name="cancelPaymentProgram" id="cancelPaymentProgram" @if(isset($globalRequests)) disabled @endif @endslot
					@slot('classEx') hidden @endslot
					Cancelar
				@endcomponent
			</div>
		@endif
	@endif
@endif
