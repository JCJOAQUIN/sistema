@extends('layouts.child_module')

@section('data')
	@component('components.forms.form',["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('requisition.authorization.update',$request->folio)."\" id=\"container-alta\"", "files" => true])
		<div class="sm:text-center text-left my-5">
			A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
		</div>
		@php
			$requestUser   = App\User::find($request->idRequest);
			$elaborateUser = App\User::find($request->idElaborate);
			$modelTable    = 
			[
				[
					"Tipo de Requisición:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->typeRequisition->name
						]
					]
				],
				[
					"Proyecto:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requestProject()->exists() ? $request->requestProject->proyectName : 'No hay'
						],
						[
							"kind"        => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_proyectName\" value=\"".$request->requestProject->idproyect."\""
						]
					]
					
				],
				[
					"Prioridad:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->urgent == 1 ? 'Alta': 'Baja'
						]
					]
				],
				[
					"Folio:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->folio
						]
					]
				],
				[
					"Solicitante:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition()->exists() && $request->requisition->request_requisition != "" ? $request->requisition()->exists() ? $request->requisition->request_requisition: 'Sin solicitante': $request->requestUser()->exists() ? $request->requestUser->fullName(): 'Sin solicitante'
						]
					]
				],
				[
					"Título:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => htmlentities($request->requisition->title)
						]
					]
				],
				[
					"Número:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->number
						]
					]
				],
			];
			if($request->requisition->code_wbs != "")
			{
				array_push($modelTable, [
					"Subproyecto/Código WBS:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->wbs()->exists() ? $request->requisition->wbs->code_wbs: 'No hay'
						]
					]
				]);
				array_push($modelTable, [
					"Código EDT:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->edt()->exists() ? $request->requisition->edt->fullName(): 'No hay'
						]
					]
				]);
			}

			if($request->requisition->generated_number != '')
			{
				array_push($modelTable, [
					"Número de requisición:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->generated_number
						]
					]
				]);
			}
			if($request->requisition->requisition_type == 5)
			{
				array_push($modelTable, [
					"Compra/Renta:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->buy_rent
						]
					]
				]);
				if($request->requisition->buy_rent == "Renta")
				{
					array_push($modelTable, [
						"Vigencia:", 
						[
							[
								"kind"  => "components.labels.label",
								"label" => $request->requisition->validity
							]
						]
					]);
				}
			}
			array_push($modelTable, [
				"Fecha en que se solicitó:", 
				[
					[
						"kind"  => "components.labels.label",
						"label" => $request->requisition->date_request
					]
				]
			]);
			if($request->requisition->date_obra != '')
			{
				array_push($modelTable, [
					"Fecha en que debe estar en obra:", 
					[
						[
							"kind"  => "components.labels.label",
							"label" => $request->requisition->date_obra
						]
					]
				]);
			}
		@endphp
		@component("components.templates.outputs.table-detail", 
		[
			"modelTable" => $modelTable, 
			"title"      => "Detalles de la Solicitud"
		]) 
		@endcomponent
		@if($request->requisition->requisition_type != 3)
			@component('components.labels.title-divisor') 
				CONCEPTOS
				@slot('classEx')
					pb-4
				@endslot
			@endcomponent
			<div class="flex flex-row justify-end">
				@component('components.buttons.button',["variant" => "warning"])
					@slot('attributeEx')
						type        = "button"
						data-toggle = "modal" 
						data-target = "#newProvider"
						title       = "Agregar nuevo proveedor"
					@endslot
					@slot('classEx')
						add-provider
					@endslot
					@slot('label')
					<span class="icon-plus"></span>
					<span>Agregar nuevo proveedor</span>
					@endslot
				@endcomponent
				@component('components.buttons.button',["variant" => "success", "buttonElement" => "a"])
					@slot('attributeEx')
						type       = "submit "
						href = "{{ route('requisition.export',$request->folio) }}"
						title = "Exportar a Excel"
					@endslot
					@slot('label')
						<span>Exportar a Excel</span><span class="icon-file-excel"></span>
					@endslot
				@endcomponent
			</div>
			@php
				$body_id = "";
				switch($request->requisition->requisition_type)
				{
					case '1':
						$modelHead =
						[
							["value" => "Nombre", "show" => true],
							["value" => "Descripción", "show" => true],
							["value" => "Existencia en Almacén", "show" => true],
							["value" => "Categoría"],
							["value" => "Tipo"],
							["value" => "Cant."],
							["value" => "Medida"],
							["value" => "Unidad"],
						];
					break;
					case '2':
						$modelHead =
						[
							["value" => "Nombre", "show" => true],
							["value" => "Descripción", "show" => true],
							["value" => "Categoría"],
							["value" => "Cant."],
							["value" => "Unidad"],
							["value" => "Periodo"],
						];
					break;
					case '4':
						$modelHead =
						[
							["value" => "Nombre", "show" => true],
							["value" => "Descripción", "show" => true],
							["value" => "Cant."],
							["value" => "Unidad"],
						];
					break;
					case '5':
						$modelHead =
						[
							["value" => "Nombre", "show" => true],
							["value" => "Descripción", "show" => true],
							["value" => "Existencia de Almacén", "show" => true],
							["value" => "Categoría"],
							["value" => "Cant."],
							["value" => "Medida"],
							["value" => "Unidad"],
							["value" => "Marca"],
							["value" => "Modelo"],
							["value" => "Tiempo de Utilización"],
						];
					break;
					case '6':
						$modelHead =
						[
							["value" => "Nombre", "show" => true],
							["value" => "Descripción", "show" => true],
							["value" => "Cant."],
							["value" => "Unidad"],
						];
					break;
				}
				if(in_array($request->status,[3,4,5,17]))
				{
					array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
				}
				$modelGroup =
				[
					[
						"name"			=> "Conceptos",
						"id"			=> 'concepts',
						"colNumber"		=> count(array_column($modelHead,'show'))
					],
					[
						"name"			=> "Detalles",
						"id"			=> 'details',
						"colNumber"		=> (count($modelHead)-count(array_column($modelHead,'show')))
					]
				];
				if($request->requisition->requisitionHasProvider()->exists())
				{
					foreach($request->requisition->requisitionHasProvider as $provider)
					{
						$modelHead[]	=	["value" => "Precio Unitario"];
						$modelHead[]	=	["value" => "Subtotal"];
						$modelHead[]	=	["value" => "IVA"];
						$modelHead[]	=	["value" => "Impuesto Adicional"];
						$modelHead[]	=	["value" => "Retenciones"];
						$modelHead[]	=	["value" => "Total"];
						$footer =
						[
							[
								"kind"  => "components.labels.label",
								"label" => "Tipo de Moneda: "
							],
							[
								"kind"        => "components.inputs.select",
								"classEx"     => "custom-select remove",
								"attributeEx" => "name = \"type_currency_provider_".$provider->id."\" data-validation = \"required\"",
								"options"     => 
								[
									[
										"value"       => "MXN",
										"description" => "MXN"
									],
									[
										"value"       => "USD",
										"description" => "USD"
									],
									[
										"value"       => "EUR",
										"description" => "EUR"
									],
									[
										"value"       => "Otro",
										"description" => "Otro"
									]
								]
							],
							[
								"kind"  => "components.labels.label",
								"label" => "Tiempo de entrega (opcional): "
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "name=\"delivery_time_".$provider->id."\" placeholder=\"Ingrese el tiempo de entrega\" value=\"".$provider->delivery_time."\""
							],
							[
								"kind"  => "components.labels.label",
								"label" => "Crédito Días (Opcional): "
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "name=\"credit_time_".$provider->id."\" placeholder=\"Ingrese el crédito\" value=\"".$provider->credit_time."\""
							],
							[
								"kind"  => "components.labels.label",
								"label" => "Garantía (Opcional): "
							],
							[
								"kind"        => "components.inputs.input-text",
								"attributeEx" => "name=\"guarantee_".$provider->id."\" placeholder=\"Ingrese la garantía\" value=\"".$provider->guarantee."\""
							],
							[
								"kind"  => "components.labels.label",
								"label" => "Comentarios (Opcional): "
							],
							[
								"kind"        => "components.inputs.text-area",
								"attributeEx" => "name=\"commentaries_provider_".$provider->id."\" placeholder=\"Ingrese un comentario\"",
								"label"       => $provider->commentaries
							]
						];
						if($request->requisition->requisition_type == 1 || $request->requisition->requisition_type == 5 )
						{
							array_splice($footer, 8, 0, 
							[
								[
									"kind"  => "components.labels.label",
									"label" => "Partes de Repuesto (Opcional): "
								]
							]
							);
							array_splice($footer, 9, 0,
							[ 
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "name=\"spare_".$provider->id."\" placeholder=\"Ingrese las partes de repuesto\" value=\"".$provider->spare."\""
								]
							]
							);
						}
						$headersProvider = 
						"<div class=\"flex justify-center items-center\">
							<input type=\"hidden\" class=\"provider_count\" value=\"".$provider->providerData->businessName."\">
							<input type=\"hidden\" class=\"provider_exists_requisition\" value=\"".$provider->providerData->id."\">
							<input type=\"hidden\" name=\"idRequisitionHasProvider[]\" class=\"id_provider_secondary\" value=\"".$provider->id."\">";
							if($provider->documents()->exists())
							{
								$headersProvider .= 
								view("components.buttons.button",[
									"classEx" => "viewDocumentProvider bg-white rounded rounded-full text-light-blue-500",
									"attributeEx" => "data-id=\"".$provider->id."\" data-toggle=\"modal\" data-target=\"#viewDocumentProvider\" type=\"button\"",
									"label" => "<span class=\"icon-search\"></span> Ver Documentos",
									"buttonElement" => "noVariant"
								])->render();
							}
							$headersProvider .=
							view("components.buttons.button",[
								"classEx" => "addDocumentProvider bg-white rounded rounded-full text-green-600 hover:bg-white",
								"attributeEx" => "title=\"Agregar Documentos\" type=\"button\" data-toggle=\"modal\" data-target=\"#addDocumentProvider\"",
								"label" => "<span class=\"icon-plus\"></span> Agregar Documentos",
								"buttonElement" => "noVariant"
							])->render().
							view("components.buttons.button",[
								"classEx" => "bg-white rounded rounded-full text-red-400",
								"attributeEx" => "title=\"Eliminar Proveedor\" name=\"btnDeleteProvider\" type=\"submit\" formaction=\"".route('requisition.delete-provider',$provider->id)."\"",
								"label" => "Eliminar proveedor",
								"buttonElement" => "noVariant"
							])->render().
						"</div>";
						
						$modelGroup[]	=	
						[
							"name"			=> $provider->providerData->businessName,
							"id"			=> 'providers',
							"colNumber"		=> 6,
							"footer"		=> $footer,
							"content"		=> $headersProvider
						];
					}
				}
				$modelBody = [];
				if($request->requisition->details()->exists())
                {
                    foreach($request->requisition->details as $key=>$detail)
                    {
						$body = [];
                        switch($request->requisition->requisition_type)
                        {
                            case(1):
                                $body = 
                                [
									[
										"content" => 
										[
											[
												"label" => $detail->name
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->description),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->exists_warehouse
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												"classEx"     => "t_id"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
												"classEx"     => "t_category"
											],
											[
												"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : ''
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->cat_procurement_material_id."\"",
												"classEx"     => "t_type"
											],
											[
												"label" => $detail->procurementMaterialType()->exists() ? $detail->procurementMaterialType->name : ''
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\"",
												"classEx"     => "t_quantity"
											],
											[
												"label" => $detail->quantity
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->measurement),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->unit
											]
										]
									]
                                ];
                                break;
                            case(2):
                                $body = 
                                [
									[
										"content" => 
										[
											[
												"label" => $detail->name
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->description),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->period
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												"classEx"     => "t_id"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
												"classEx"     => "t_category"
											],
											[
												"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : ''
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\"",
												"classEx"     => "t_quantity"
											],
											[
												"label" => $detail->quantity
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->unit
											]
										]
									]
                                ];
                                break;
                            case(4):
                                $body = 
                                [
									[
										"content" => 
										[
											[
												"label" => $detail->name
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->description),
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												"classEx"     => "t_id"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\"",
												"classEx"     => "t_quantity"
											],
											[
												"label" => $detail->quantity
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->unit
											]
										]
									]
                                ];
                                break;
                            case(5):
                                $body = 
                                [
									[
										"content" => 
										[
											[
												"label" => $detail->name
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->description),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->brand),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->model),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->usage_time),
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->exists_warehouse
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												"classEx"     => "t_id"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
												"classEx"     => "t_category"
											],
											[
												"label" => $detail->categoryData()->exists() ? $detail->categoryData->description : ''
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\"",
												"classEx"     => "t_quantity"
											],
											[
												"label" => $detail->quantity
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->measurement)
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->unit
											]
										]
									]
                                ];
                                break;

                            case(6):
                                $body = 
                                [
									[
										"content" => 
										[
											[
												"label" => $detail->name
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => htmlentities($detail->description)
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												"classEx"     => "t_id"
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity."\"",
												"classEx"     => "t_quantity"
											],
											[
												"label" => $detail->quantity
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $detail->unit
											]
										]
									]
                                ];
                                break;
                        }
						if(in_array($request->status,[3,4,5,17,27]))
						{
							array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["label" => $detail->part]]]]);
						}
						if($request->requisition->requisitionHasProvider()->exists())
                        {
							foreach($request->requisition->requisitionHasProvider as $provider)
                            {
								$price = App\ProviderSecondaryPrice::where('idRequisitionDetail',$detail->id)->where('idRequisitionHasProvider',$provider->id)->first();
								$taxesData = [];
                                if($price != "" && $price->taxesData()->exists())
                                {
                                    foreach ($price->taxesData as $tax)
                                    {
                                        array_push($taxesData, 
                                            [
                                                "id"         => $tax->id,
                                                "name"       => $tax->name,
                                                "amount"     => $tax->amount
                                            ]
                                        );
                                    }
                                }
                                $retentionsData = [];
                                if($price != "" && $price->retentionsData()->exists())
                                {
                                    foreach ($price->retentionsData as $retention)
                                    {
                                        array_push($retentionsData, 
                                            [
                                                "id"         => $retention->id,
                                                "name"       => $retention->name,
                                                "amount"     => $retention->amount
                                            ]
                                        );
                                    }
                                }
								$priceId   = $price != "" ? $price->id : "x";
                                $unitPrice = $price != "" ? $price->unitPrice : "0.00";
                                $subtotal  = $price != "" ? $price->subtotal : "0.00";
                                $iva	   = $price != "" ? $price->iva : "0.00";
                                $total	   = $price != "" ? $price->total : "0.00";

								$body[] = 
								[
									"content" =>
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" name=\"idProviderSecondaryPrice_".$detail->id."_".$provider->id."\" value=\"".$priceId."\"",
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "name=\"unitPrice_".$detail->id."_".$provider->id."\" placeholder=\"Ingrese el precio\" data-provider=\"".$provider->id."\" data-item=\"".$detail->id."\" value=\"".$unitPrice."\" data-validation=\"required\"",
											"classEx"     => "remove-validation-concept t_unitPrice"
										]
									]
								];
								$body[] = 
								[
									"content" =>
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "name=\"subtotal_".$detail->id."_".$provider->id."\" placeholder=\"Ingrese el subtotal\" data-provider=\"".$provider->id."\" data-item=\"".$detail->id."\" value=\"".$subtotal."\" data-validation=\"required\" readonly=\"readonly\"",
											"classEx"     => "remove-validation-concept t_subtotal"
										]
									]
								];
								$body[]	=
								[
									"content" =>
									[
										[
											"kind"        => "components.inputs.select",
											"attributeEx" => "name=\"typeTax_".$detail->id."_".$provider->id."\" data-provider=\"".$provider->id."\" data-item=\"".$detail->id."\" data-validation=\"required\"",
											"classEx"     => "custom-select remove-validation-concept t_typeTax",
											"options"     => 
											[
												[
													"value"       => "no",
													"description" => "NO",
													"selected"    => ($price != "" && $price->typeTax == "no" || $price == "") ? "selected" : ""
												],
												[
													"value"       => "a",
													"description" => App\Parameter::where('parameter_name','IVA')->first()->parameter_value."%",
													"selected"    => $price != "" && $price->typeTax == "a" ? "selected" : ""
												],
												[
													"value"       => "b",
													"description" => App\Parameter::where('parameter_name','IVA2')->first()->parameter_value."%",
													"selected"    => $price != "" && $price->typeTax == "b" ? "selected" : ""
												]
											]
										],
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" placeholder=\"0.00\"  data-provider=\"".$provider->id."\" data-item=\"".$detail->id."\" name=\"iva_".$detail->id."_".$provider->id."\" value=\"".$iva."\" data-validation=\"required\" readonly=\"readonly\"",
											"classEx"     => "remove-validation-concept t_iva"
										]
									]
								];
								$body[]	=
								[
									"content" =>
									[
										[
											"kind"        => "components.templates.inputs.taxesRequisitions",
											"name"        => "tax",
											"detailId"    => $detail->id,
											"providerId"  => $provider->id,
											"addedData"   => $taxesData,
											"attributeEx" => "data-provider=\"".$provider->id."\"",
											"classEx"     => "add_taxes",
											"classExButton"	=> "p-2.5"
										]
									]
								];
								$body[] = 
								[
									"content" =>
									[
										[
											"kind" => "components.templates.inputs.taxesRequisitions",
											"name"        => "ret",
											"detailId"    => $detail->id,
											"providerId"  => $provider->id,
											"addedData"   => $retentionsData,
											"attributeEx" => "data-provider=\"".$provider->id."\"",
											"classEx"     => "add_retentions",
											"classExButton"	=> "p-2.5"
										]
									]
								];
								$body[]	=
								[
									"content" =>
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "placeholder=\"Ingrese el total\" readonly=\"readonly\" data-item=\"".$detail->id."\" name=\"total_".$detail->id."_".$provider->id."\" value=\"".$total."\" data-provider=\"".$provider->id."\" data-validation=\"required\"",
											"classEx"     => "remove-validation-concept t_total"
										]
									]
								];
							}
						}
						$modelBody[] = $body;
					}
				}
			@endphp
			@component('components.tables.table-provider',[
				"attributeExBody"	=> "id=\"body_art\"",
				"modelHead"      	=> $modelHead,
				"modelBody"      	=> $modelBody,
				"modelGroup"		=> $modelGroup
			])
			@endcomponent
		@else
			@if($request->requisition->staff()->exists())
				@component('components.labels.title-divisor') 
					DATOS DE LA VACANTE
					@slot('classEx')
						pb-4
					@endslot
				@endcomponent
				<div class="employee-details">
					<div class="flex justify-center px-6">
						<div class="justify-center">
							@component('components.tables.table-request-detail.container',['variant'=>'simple'])
								@php
									$modelTable = [];
									$modelTable["Jefe inmediato"] = $request->requisition->staff->boss->fullName();
									$modelTable["Horario"] = $request->requisition->staff->staff_schedule_start."  -  ".$request->requisition->staff->staff_schedule_end;
									$modelTable["Rango de sueldo"] = "$".number_format($request->requisition->staff->staff_min_salary,2)." - $ ".number_format($request->requisition->staff->staff_max_salary,2);
									$modelTable["Motivo"] = $request->requisition->staff->staff_reason;
									$modelTable["Puesto"] = $request->requisition->staff->staff_position;
									$modelTable["Periodicidad"] = $request->requisition->staff->staff_periodicity;
									$modelTable["Descripción general de la vacante"] = $request->requisition->staff->staff_s_description;
									$modelTable["Habilidades requeridas"] = $request->requisition->staff->staff_habilities;
									$modelTable["Experiencia deseada"] = $request->requisition->staff->staff_experience;

									$responsabilities = "";
									foreach($request->requisition->staffResponsabilities as $responsibilityStaff)
									{
										$responsabilities = $responsabilities.$responsibilityStaff->dataResponsibilities->responsibility.", ";
									}
									$modelTable["Responsabilidades"] = $responsabilities;
								@endphp
								@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
								@endcomponent
							@endcomponent
						</div>
					</div>
					<div class="w-full mx-3">
						@php
							$body 			= [];
							$modelBody		= [];
							$modelHead = ["Función", "Descripción"];
							foreach($request->requisition->staffFunctions as $function)
							{
								$body = 
								[
									"classEx" => "tr",
									[
										"content" => 
										[
											[
												"label" => $function->function
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $function->description
											]
										]
									]
								];
								array_push($modelBody, $body);
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"title" => "Funciones"
						])
							@slot('classEx')
								text-center employee-details
							@endslot
						@endcomponent
					</div>
					<div class="w-full mx-3">
						@php
							$body 			= [];
							$modelBody		= [];
							$modelHead = ["Deseables", "Descripción"];
							foreach($request->requisition->staffDesirables as $desirable)
							{
								$body = 
								[
									"classEx" => "tr",
									[
										"content" => 
										[
											[
												"label" => $desirable->desirable
											]
										]
									],
									[
										"content" => 
										[
											[
												"label" => $desirable->description
											]
										]
									]
								];
								array_push($modelBody, $body);
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"title" => "Deseables"
						])
							@slot('classEx')
								text-center employee-details
							@endslot
						@endcomponent
					</div>
				</div>
			@else
				<div id="staff-table" class="@if(isset($request) && in_array($request->requisition->requisition_type,[1,2,4,5,6])) hidden  @elseif(!isset($request)) hidden @endif">
					<div class="w-full mx-3">
						@php
							$body 			= [];
							$modelBody		= [];
							$modelHead = ["Nombre", "Puesto", "Acción"];
							
							if(isset($request) && $request->requisition->employees()->exists())
							{
								foreach($request->requisition->employees as $key => $emp)
								{
									$body = 
									[
										[
											"content" => 
											[
												[
													"label" => htmlentities($emp->fullName())
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" =>  htmlentities($emp->position)
												]
											]
										],
										[
											"content" => 
											[
												[
													"kind"        => "components.buttons.button", 
													"classEx" => "view-employee",
													"variant" => "secondary",
													"label" => "<span class=\"icon-search\"></span>",
													"attributeEx" => "data-toggle=\"modal\"data-target=\"#detailEmployee\""
												],
												[
													"kind"        => "components.inputs.input-text", 
													"attributeEx" => "name=\"rq_employee_id[]\" type=\"hidden\" value=\"".$emp->id."\""
												]
											]
										]
									];
									array_push($modelBody, $body);
								}
							}
						@endphp
						@component('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody
						])
							@slot('classEx')
								text-center
							@endslot
							@slot('attributeExBody')
								id="list_employees"
							@endslot
						@endcomponent
						
					</div>
				</div>
			@endif
		@endif
		@if($request->requisition->documents()->exists())
			@component('components.labels.title-divisor')    DOCUMENTOS DE LA REQUISICIÓN @endcomponent
			<div class="mx-3">	
				@php
					$body 			= [];
					$modelBody		= [];
					$modelHead = ['Tipo de documento', 'Folio fiscal', 'Archivo', 'Modificado Por', 'Fecha'];
					foreach($request->requisition->documents->sortByDesc('created') as $doc)
					{
						$body = 
						[
							"classEx" => "tr",
							[
								"content" =>
								[
									"label" => $doc->name,
									[
										"kind"  	  => "components.inputs.input-text", 
										"attributeEx" => "type=\"hidden\" name=\"document-id[]\" value=\"".$doc->id."\""
									]
								]
							],
							[
								"content" =>
								[
									["label" => htmlentities($doc->fiscal_folio)]
								]
							],
							[
								"content" =>
								[
									[
										"label" => view("components.buttons.button",[
										"buttonElement" => "a",
										"classEx"       => "w-32",
										"variant"       => "secondary",
										"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
										"label"         => "Archivo"
										])->render()
									]
								]
							],
							[
								"content" =>
								[
									"label" => $doc->user->fullName()
								]
							],
							[
								"content" =>
								[
									"label" => Carbon\Carbon::parse($doc->created)->format('d-m-Y')
								]
							]
						];
						array_push($modelBody, $body);
					}
				@endphp
				@component('components.tables.alwaysVisibleTable',[
					"modelHead" => $modelHead,
					"modelBody" => $modelBody
				])
				@endcomponent
			</div>
		@endif
		@component('components.containers.container-form')
			<div id="documents-requisition" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6">			
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx') type="button" name="addDocRequisition" id="addDocRequisition" @endslot
					<span class="icon-plus"></span>
					<span>Nuevo documento</span>
				@endcomponent
			</div>
		@endcomponent
		<span id="spanDelete"></span>
		@component('components.containers.container-form', ["attributeEx" => "id=\"comment flex\""])
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.labels.label') 
					Comentarios (opcional)
				@endcomponent
				@component('components.inputs.text-area')
					@isset($classExComment) 
						@slot('classEx')
							text-area w-full
						@endslot
					@endisset
					@slot('attributeEx')
						cols="90" rows="10" name="revisionComment" @isset($attributeExComment) {!!$attributeExComment!!} @endisset
					@endslot
				@endcomponent
			</div>
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('attributeEx') 
					type="submit" name="send"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto
				@endslot
				APROBAR REQUISICIÓN
			@endcomponent
			@component("components.buttons.button",["variant" => "secondary"])
				@slot('attributeEx') 
					type="submit" name="btnSave" id="save" formaction="{{ route('requisition.save-authorization',$request->folio) }}"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto save
				@endslot
				GUARDAR CAMBIOS
			@endcomponent
			@component("components.buttons.button",["variant" => "red"])
				@slot('attributeEx') 
					type="submit" id="reject" name="btnReject" formaction="{{ route('requisition.reject-authorization',$request->folio) }}"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto text-center reject
				@endslot
				RECHAZAR REQUISICIÓN
			@endcomponent	
		</div>
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				viewDocumentProvider
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('classExBody')
				modal-view-document
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="submit" 
						name="btnAddProviderDocuments" 
						formaction="{{route('requisition.provider-documents.store',$request->folio)}}"
					@endslot
					<span class="icon-check"></span> Agregar Documentos
				@endcomponent
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
						formaction="{{route('requisition.provider-documents.store',$request->folio)}}"
					@endslot
					@slot('classEx')
						closeViewDocument
					@endslot
					<span class="icon-cross"></span> Cerrar
				@endcomponent
			@endslot
		@endcomponent
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				newComment
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
				@component("components.labels.label") Comentario de votación @endcomponent
				@component("components.inputs.text-area", ["attributeEx" => "name=\"comment\""])
				@endcomponent
				<input type="hidden" name="id_detail">
				<input type="hidden" name="id_provider">
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="button" 
						name="btnAddCommentaries" 
					@endslot
					<span class="icon-check"></span> Agregar comentario
				@endcomponent
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-cross"></span> Cerrar
				@endcomponent
			@endslot
		@endcomponent
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				viewComment
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
				@component("components.labels.label") Comentario de votación @endcomponent
				@component("components.inputs.text-area", ["attributeEx" => "name=\"commentView\""])
				@endcomponent
				<input type="hidden" name="id_detail">
				<input type="hidden" name="id_provider">
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-cross"></span> Cerrar
				@endcomponent
			@endslot
		@endcomponent
		<input type="hidden" name="data_validate" value="1">
	@endcomponent
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"documentProvider\" method=\"post\"", 
		"methodEx"    => "PUT",
		"token"       => "true"
	])		
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				addDocumentProvider
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
				<input type="hidden" name="idRequisitionHasProviderDoc">
				@component("components.containers.container-form")	
					<div class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6" id="documents">
						@component('components.documents.upload-files',
						[
							"componentsExUp"       =>
							[
								[
									"kind" 	  => "components.labels.label", 
									"label"	  => "Tipo de documento:"
								],
								[
									"kind" 		  => "components.inputs.select", 
									"classEx"     => "custom-select nameDocument",
									"attributeEx" => "name=\"nameDocument[]\"",
									"options"	  => collect(
										[
											[
												"value"       => "Cotización",
												"description" => "Cotización"
											],
											[
												"value"       => "Ficha Técnica",
												"description" => "Ficha Técnica"
											],
											[
												"value"       => "Control de Calidad",
												"description" => "Control de Calidad"
											],
											[
												"value"       => "Contrato",
												"description" => "Contrato"
											],
											[
												"value"       => "Factura",
												"description" => "Factura"
											],
											[
												"value"       => "REQ. OC. FAC.",
												"description" => "REQ. OC. FAC."
											],
											[
												"value"       => "Otro",
												"description" => "Otro"
											]
										])
								],
							],
							"classExInput"         => "pathActioner",
							"attributeExInput"     => "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExDelete"        => "delete-doc",
							"attributeExRealPath"  => "name=\"realPath[]\"",
							"classExRealPath"	   => "path"
						])
						@endcomponent
					</div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx')
								type="button" 
								name="addDocProvider" 
								id="addDocProvider"
							@endslot
							<span class="icon-plus"></span>
							<span>Nuevo Documento</span>
						@endcomponent
					</div>
				@endcomponent
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="submit" 
						name="btnAddProviderDocuments" 
						formaction="{{route('requisition.provider-documents.store',$request->folio)}}"
					@endslot
					<span class="icon-check"></span> Agregar Documentos
				@endcomponent
				@component('components.buttons.button', ["variant" => "red"])
					@slot('attributeEx')
						type="button" 
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			@endslot
		@endcomponent
	@endcomponent
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"newProvideer\" method=\"post\" action=\"".route('requisition.store-provider',$request->folio)."\"", 
		"classEx" 	  => "request-validate", 
		"methodEx"    => "PUT", 
		"token"       => "true"
	])
		@csrf
		@method('PUT')
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				newProvider
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			@endslot
			@slot('modalBody')
				<div class="block p-6" id="form">
					@component('components.labels.title-divisor')
						SELECCIONE UNA OPCIÓN
					@endcomponent
					<div class="flex flex-wrap justify-center w-full space-x-2 pt-4">
						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								type="radio" name="prov" id="new-prov" value="nuevo" 
							@endslot
							@slot('classExLabel')
								rounded
							@endslot
							@slot('classExContainer')
								pb-4
							@endslot
							Registrar Nuevo
						@endcomponent

						@component('components.buttons.button-approval')
							@slot('attributeEx') 
								type="radio" name="prov" id="buscar-prov" value="buscar" @if(isset($requests)) checked @endif 
							@endslot
							@slot('classExLabel')
								rounded
							@endslot
							@slot('classExContainer')
								pb-4
							@endslot
							Buscar Existente
						@endcomponent
					</div>
				</div>
				<div class="w-full pb-2">
					<div class="hidden form-search-provider">
						<div class="px-2 md:px-56">
							@component('components.inputs.input-text') 
								@slot('attributeEx')
									type="hidden" id="pagePagination" value="1"
								@endslot
							@endcomponent
							@component('components.inputs.input-search') 
								Buscar Proveedor
								@slot('attributeExInput')
									placeholder="Ingrese un nombre" 
								@endslot
								@slot('classExInput')
									input-search
								@endslot
								@slot('attributeExButton')
									type="button"
									id="search_provider"
								@endslot
							@endcomponent
						</div>
						<div class="table-queue-provider hidden">
							@component('components.labels.title-divisor')
								PROVEEDORES SELECCIONADOS
							@endcomponent
							@php	
								$modelHead = ["ID", "Nombre", "RFC", "Eliminar"];
								$modelBody = [];
							@endphp
							@component("components.tables.alwaysVisibleTable", ["modelHead" => $modelHead, "modelBody" => $modelBody]) 
								@slot('attributeExBody')
									id="result_queue_provider"
								@endslot
							@endcomponent
							@component("components.buttons.button", ["variant" => "success"]) 
								@slot("classEx")
									massive-action
								@endslot
								@slot("attributeEx")
									type="submit" name="addMultiProvider" formaction="{{route('requisition.store-provider',$request->folio)}}""
								@endslot
								<span class='icon-plus'></span> Agregar lista de proveedores
							@endcomponent
						</div>
						<div id="result_provider_container">
							@component('components.labels.title-divisor')
								LISTA DE PROVEEDORES
							@endcomponent
							<div id="result_provider">
							</div>
						</div>
						<div id="form_edit_provider"></div>
					</div>
				</div>
				<div class="form-add-provider hidden">
					<div>
						@component('components.labels.title-divisor')
							DATOS DEL PROVEEDOR
						@endcomponent
					</div>
					<div class="form-container container-blocks" id="container-data">
						@component('components.containers.container-form')
							<div class="col-span-2">
								@component('components.labels.label') Razón Social: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="businessName" data-old-reason="" placeholder="Ingrese la razón social" data-validation="required length" data-validation-length="max150"
									@endslot
									@slot('classEx')
										remove businessName
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Calle: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="address" placeholder="Ingrese una calle" data-validation="required length" data-validation-length="max100"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Número: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="number" placeholder="Ingrese un número" data-validation="required length" data-validation-length="max45"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Colonia: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="colony" placeholder="Ingrese una colonia" data-validation="required length" data-validation-length="max70"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Código Postal: @endcomponent
								@component("components.inputs.select", ["options" => []])
									@slot("classEx") remove cp @endslot
									@slot("attributeEx") id="cp" name="postalCode" data-validation="required" multiple="multiple" @endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Ciudad: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="city" placeholder="Ingrese una ciudad" data-validation="required length" data-validation-length="max70"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Estado: @endcomponent
								@php
									$options = collect();
									foreach(App\State::orderName()->get() as $state)
									{
										$options = $options->concat([['value'=>$state->idstate, 'description'=>$state->description]]);
									}
									$attributeEx = "name=\"state_idstate\" multiple=\"multiple\" data-validation=\"required\"";
									$classEx = "js-state removeselect";
								@endphp
								@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') RFC: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="rfc" data-old-rfc="" placeholder="Ingrese el RFC" data-validation="server" data-validation-url="{{route('requisition.provider-validation')}}"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Teléfono: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										id="input-small" name="phone" placeholder="Ingrese el teléfono" data-validation="phone required"
									@endslot
									@slot('classEx')
										phone remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Contacto: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="contact" placeholder="Ingrese el contacto" data-validation="required"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label') Beneficiario: @endcomponent
								@component('components.inputs.input-text')
									@slot('attributeEx')
										name="beneficiary" placeholder="Ingrese el nombre del beneficiario" data-validation="required"
									@endslot
									@slot('classEx')
										remove
									@endslot
								@endcomponent
							</div>
							<div class="col-span-2">
								@component('components.labels.label')Comentarios (opcional): @endcomponent
								@component('components.inputs.text-area')
									@slot('attributeEx')
										name="commentaries" placeholder="Ingrese un comentario"
									@endslot
								@endcomponent
							</div>
						@endcomponent
						
						<div class="block form-container">
							@component('components.labels.title-divisor')
								CUENTAS BANCARIAS
							@endcomponent
							
							<div id="banks" class="form-container pt-4 @if(isset($requests)) hidden @endif">
								<div id="form-container-inline">
									@component('components.labels.label') Para agregar una cuenta nueva es necesario colocar los siguientes campos: @endcomponent
									@component('components.containers.container-form')
										<div class="col-span-2">
											@component('components.labels.label') Banco: @endcomponent
											@php
												$options = collect();
												$attributeEx = "multiple=\"multiple\"";
												$classEx = "js-bank";
											@endphp
											@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Alias: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese un alias"
												@endslot
												@slot('classEx')
													alias
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Cuenta bancaria: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese una cuenta bancaria" data-validation="cuenta"
												@endslot
												@slot('classEx')
													account
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Sucursal: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese una sucursal"
												@endslot
												@slot('classEx')
													branch_office
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Referencia: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese una referencia"
												@endslot
												@slot('classEx')
													reference
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') CLABE: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese una CLABE" data-validation="clabe"
												@endslot
												@slot('classEx')
													clabe
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Moneda @endcomponent
											@php
												$options = collect(
													[
														['value'=>'MXN', 'description'=>'MXN'], 
														['value'=>'USD', 'description'=>'USD'], 
														['value'=>'EUR', 'description'=>'EUR'], 
														['value'=>'Otro', 'description'=>'Otro']
													]
												);
												$classEx = "custom-select currency";
											@endphp
											@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') IBAN: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese un IBAN" data-validation="iban"
												@endslot
												@slot('classEx')
													iban
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') BIC/SWIFT: @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese el BIC/SWIFT" data-validation="bic_swift"
												@endslot
												@slot('classEx')
													bic_swift
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2">
											@component('components.labels.label') Convenio (opcional): @endcomponent
											@component('components.inputs.input-text')
												@slot('attributeEx')
													placeholder="Ingrese el convenio"
												@endslot
												@slot('classEx')
													agreement
												@endslot
											@endcomponent
										</div>
										<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
											@component('components.buttons.button', ["variant" => "warning"])
												@slot('attributeEx') id="addAccount" type="button" @endslot
												<span class="icon-plus"></span>
												<span>Agregar</span>
											@endcomponent
										</div>
									@endcomponent
								</div>
							</div>
							@php
								$body 	   = [];
								$modelBody = [];
								$modelHead = ["Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acciones"];
								
							@endphp
							@component('components.tables.alwaysVisibleTable',[
								"modelHead" 			=> $modelHead,
								"modelBody" 			=> $modelBody,
								"themeBody" 			=> "striped"
							])
								@slot('attributeExBody')
									id="banks-body"
								@endslot
							@endcomponent
						</div>
					</div>
					@component('components.containers.container-form')
						<div id="documents-new-provider" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"></div>
						<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
							@component('components.buttons.button', ["variant" => "warning"])
								@slot('attributeEx') name="addDocNewProvider" id="addDocNewProvider" @endslot
								<span class="icon-plus"></span>
								<span>Nuevo documento</span>
							@endcomponent
						</div>
					@endcomponent
					<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
						<input type="hidden" name="idRequisition" value="{{ $request->requisition->id }}">
						@component('components.buttons.button',[
							"variant" => "success"
							])
							@slot('classeEx')
								closeViewDocument
							@endslot
							@slot('attributeEx')
								type="submit"
								id="addProvider"
								name="btnAddProvider"
							@endslot
							<span class="icon-check"></span> Agregar
						@endcomponent
						@component('components.buttons.button',[
							"variant" => "red"
							])
							@slot('classeEx')
								close-modal
							@endslot
							@slot('attributeEx')
								type="button"
								data-dismiss="modal"
							@endslot
							<span class="icon-x"></span> Cerrar
						@endcomponent
					</div>
				</div>
			@endslot
		@endcomponent
	@endcomponent
	@component('components.modals.modal', ["variant" => "large"])
		@slot('id')
			detailEmployee
		@endslot
		@slot('attributeEx')
			tabindex="-1"
		@endslot
		@slot('classExBody') modal-employee @endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "red"])
				@slot('attributeEx')
					type="button" 
					data-dismiss="modal"
				@endslot
				<span class="icon-x"></span> Cerrar
			@endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/papaparse.min.js') }}"></script>
<script src="{{ asset('js/daterangepicker.js') }}"></script>
<script type="text/javascript">
	function search_provider(page)
	{
		idProvider = [];
		$('[name="multiprovider[]"]').each(function(i,v)
		{
			idProvider.push($(this).val());
		});

		$('.provider_exists_requisition').each(function(i,v)
		{
			idProvider.push($(this).val());
		});
		
		text = $('.input-search').val().trim();
		folio = {{ $request->folio }};
		$.ajax(
		{
			type	: 'post',
			url		: '{{ route("requisition.search-provider") }}',
			data	: {'text':text,'folio':folio,'idProvider':idProvider, 'page':page},
			success	: function(data)
			{
				$('#result_provider').html(data);
				$('#result_provider_container').show();
			}
		});
	}
	function validationNewProvideer()
	{
		$.validate(
		{
			form	: '#newProvideer',
			modules	: 'security',
			onError : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				if ($('input[name="prov"]:checked').val() == "buscar") 
				{
					if ($('#result_queue_provider .table-row').length == 0 && $('[name="idProviderBtn"]').val() == "" && $('[name="idProviderBtn"]').val() == undefined) 
					{
						swal('', 'Por favor seleccione al menos un proveedor.', 'error');
						return false;
					}
				}

				pathProvideer = $('[name="realPathNewProvider[]"]').length;
				flag=true;
				if(pathProvideer>0)
				{
					$('[name="realPathNewProvider[]"]').each(function()
					{
						if($(this).val()=='')
						{
							swal('', 'Por favor cargue los documentos faltantes.', 'error');
							flag = false;
						}
					});
				}
				if(flag)
				{
					swal("Cargando",{
						icon				: '{{ asset(getenv('LOADING_IMG')) }}',
						button				: false,
						closeOnClickOutside	: false,
						closeOnEsc			: false
					});
					return true;
				}
				else
				{
					return false;
				}
			}
		});
	}
	function validationEditProvideer()
	{
		$.validate(
		{
			form	: '#editProvideer',
			modules	: 'security',
			onError : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				swal("Cargando",{
					icon				: '{{ asset(getenv('LOADING_IMG')) }}',
					button				: false,
					closeOnClickOutside	: false,
					closeOnEsc			: false
				});
			}
		});
	}
	
	function validation()
	{
		$.validate(
		{
			form	: '#container-alta',
			modules	: 'security',
			onError   : function($form)
			{
				swal('', '{{ Lang::get("messages.form_error") }}', 'error');
			},
			onSuccess : function($form)
			{
				
				flag	= false;
				dataValidate = $('[name="data_validate"]').val();
				if (dataValidate == 1) 
				{
					$('input[name="realPathRequisition[]').each(function(i,v)
					{
						nameDocument = $(this).parent('.docs-p').find('[name="nameDocumentRequisition[]"] option:selected').val();
						if( $(this).val() == "" || nameDocument == "0" )
						{
				 			flag = true;
						}
					});

					if(flag)
					{
						swal('', 'Tiene un archivo sin agregar', 'error');
						return false;
					}

					if($('.request-validate').length>0)
					{
						conceptos	= $('#body_art .tr').length;
						providers 	= $('.provider_exists_requisition').length;

						if (providers == 0) 
						{
							swal('', 'Debe agregar al menos un proveedor', 'error');
							return false;
						}
						
						if(conceptos>0)
						{	
							swal("Cargando",{
								icon				: '{{ asset(getenv('LOADING_IMG')) }}',
								button				: false,
								closeOnClickOutside	: false,
								closeOnEsc			: false
							});
							return true;
						}
						else
						{
							swal('', 'Debe ingresar al menos un concepto de pedido', 'error');
							return false;
						}
					}
					else
					{	
						swal("Cargando",{
							icon				: '{{ asset(getenv('LOADING_IMG')) }}',
							button				: false,
							closeOnClickOutside	: false,
							closeOnEsc			: false
						});
						return true;
					}		
				}
				else
				{	
					swal("Cargando",{
						icon				: '{{ asset(getenv('LOADING_IMG')) }}',
						button				: false,
						closeOnClickOutside	: false,
						closeOnEsc			: false,
						timer 				: 1500,
					});
					return true;
				}	
			}
		});
	}

	$(document).ready(function()
	{
		validation();
		validationNewProvideer();
		validationEditProvideer();
		$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
		$('.t_unitPrice,.t_subtotal,.t_total,.clabe,.account').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false });
		@php
			$selects = collect(
				[
					[
						"identificator"          => "[name=\"state_idstate\"],.custom-select,.currency,.t_typeTax", 
						"placeholder"            => "Seleccione uno", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					]
				]
			);
		@endphp
		@component("components.scripts.selects",["selects" => $selects])@endcomponent
		$(document).on('click','#upload_file,[name="export_excel"],[name="btnReject"]',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			$('.validate-vote').removeClass('validate-vote');
			$('.provider_exists_requisition').removeClass('provider_exists_requisition');
			$('[name="data_validate"]').val('0');

		})
		.on('click','[name="btnAddProviderDocuments"]',function(e)
		{
			e.preventDefault()
			
			action = $(this).attr('formaction');
			form = $('form#documentProvider').attr('action',action);
			needFileName = false;

			$('[name="realPath[]"]').each(function()
			{
				select = $(this).parents('div').find('.nameDocument');
				name = $('option:selected',select).val();
				select.removeClass('error');

				if($(this).val() == "" || name == 0 || name == "")
				{
					select = $(this).parents('div').find('.nameDocument');
					name = select.find('option:selected').val()

					needFileName = true;
					select.addClass('error')
				}
			});

			if(!needFileName)
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
				$('.validate-vote').removeClass('validate-vote');
				$('.provider_exists_requisition').removeClass('provider_exists_requisition');
				$('[name="data_validate"]').val('0');
				form.submit();
			}
			else
			{
				swal('', 'Debe seleccionar el tipo de documento y cargar un documento', 'error');
			}

		})
		.on('click','[data-toggle="modal"]',function()
		{
			@php
				$selects = collect(
					[
						[
							"identificator"          => "[name=\"state_idstate\"],.custom-select,.currency,.t_typeTax", 
							"placeholder"            => "Seleccione uno", 
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]
				);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
		})
		.on('click','.close-modal',function(e)
		{
			remove = $('[name="businessName"],[name="address"],[name="number"],[name="colony"],[name="postalCode"],[name="city"],[name="rfc"],[name="phone"],[name="contact"],[name="beneficiary"]');
			remove.removeAttr("style").removeClass('error').removeClass('valid').parent('p').removeClass('has-error').removeClass('has-success').find('.extra-error, .form-error').remove();
			remove.val("");
			$('.js-state').removeClass('error').removeClass('valid').parent('p').removeClass('has-error').removeClass('has-success').find('.extra-error, .form-error').remove();
			$('.js-state, .js-bank').val(null).trigger('change');
			$('#documents-new-provider').html("");
		})
		.on('change','input[name="status"]',function()
		{
			$("#comment").show();
		})
		.on('click','.paginate a', function(e)
		{
			e.preventDefault();
			href   = $(this).attr('href');
			url    = new URL(href);
			params = new URLSearchParams(url.search);
			page   = params.get('page');
			search_provider(page)
		})
		.on('click','#addAccount',function()
		{
			bank			= $('.js-bank').val();
			bankName		= $('.js-bank :selected').text();
			account			= $('.account').val();
			branch_office	= $('.branch_office').val();
			reference		= $('.reference').val();
			clabe			= $('.clabe').val();
			currency		= $('.currency').val();
			agreement		= $('.agreement').val();
			alias 			= $('.alias').val();
			iban			= $('.iban').val();
			bic_swift 		= $('.bic_swift').val();

			clabe_tr  = bankAccount_tr = true;

			$("#banks-body .tr").each(function(i,v)
			{
				var currentRow=$(this).closest(".tr");

				bank_tr 	= $(this).find("[name='bank[]']").val();
				account_tr	= $(this).find("[name='account[]']").val();

				if((clabe == currentRow.find("td:eq(5)").text()) && (clabe != ""))
				{
					clabe_tr = false;
				}
				else if((bankName+" "+account) == (bank_tr+" "+account_tr) && (account != ""))
				{
					bankAccount_tr = false;
				}
			});

			if(clabe_tr == false)
			{
				swal("", "Esta clabe ya ha sido registrada anteriormente", "error");
				return false;
			}
			else if(bankAccount_tr == false)
			{
				swal("", "Esta cuenta bancaria y banco ya han sido registrados anteriormente", "error");
				return false;
			}

			if(bank.length>0)
			{
				$('.account,.reference,.clabe,.currency').removeClass('error');
				if (account == "" && reference=="" && clabe == "" || currency == "")
				{
					if(account == "" && reference=="" && clabe == "")
					{
						if(account == "")
						{
							$('.account').addClass('error');
						}
						if(reference=="")
						{
							$('.reference').addClass('error');
						}
						if(clabe == "")
						{
							$('.clabe').addClass('error');
						}
					}
					if(currency == "")
					{
						$('.currency').addClass('error');
					}
					swal('', 'Debe ingresar todos los campos requeridos', 'error');
				}
				else if($(this).parent().siblings().children().find('.clabe').hasClass('error') || $(this).parent().siblings().children().find('.account').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					bool = true;
					if(account != "" && (account.length > 15 || account.length < 5))
					{
						$('.account').addClass('error');
						$('.account').parent().append('<span class="help-block form-error span-error">La cuenta debe ser entre 5 y 15 dígitos.</span>');
						bool = false;
					}
					if(clabe != "" && (clabe.length != 18))
					{
						$('.clabe').addClass('error');
						$('.clabe').parent().append('<span class="help-block form-error span-error">La CLABE debe ser de 18 dígitos.</span>');
						bool = false;
					}
					if(bool)
					{
						@php
							$modelHead = ["Banco", "Alias", "Cuenta", "Sucursal", "Referencia", "CLABE", "Moneda", "IBAN", "BIC/SWIFT", "Convenio", "Acciones"];
							$modelBody = 
							[
								[
									"classEx" => "tr-bankAccount",
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"classEx"     => "providerBank",
												"attributeEx" => "type=\"hidden\" value=\"x\" name=\"providerBank[]\""
											],
											[
												"kind"        => "components.inputs.input-text",
												"classEx"     => "bank_account_id",
												"attributeEx" => "type=\"hidden\" value=\"x\""
											],
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"idBanks[]\""
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"alias[]\"",
												"classEs"     => "alias_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"account[]\"",
												"classEx"     => "account_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"branch[]\"",
												"classEx"     => "branch_office_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"reference[]\"",
												"classEx"     => "reference_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"clabe[]\"".(isset($bank->clabe) ? "value=\"{{$bank->clabe}}\"" : "value=\"\""),
												"classEx"     => "clabe_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"currency[]\"",
												"classEx"     => "currency_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"iban[]\"",
												"classEx"     => "iban_row"
											]
										]
									],
									[
										"content" => 
										[ 
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"bic_swift[]\"",
												"classEx"     => "switf_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"        => "components.inputs.input-text",
												"attributeEx" => "type=\"hidden\" name=\"agreement[]\"",
												"classEs"     => "areement_row"
											]
										]
									],
									[
										"content" => 
										[
											[
												"kind"    => "components.buttons.button",
												"variant" => "red",
												"label"   => "<span class=\"icon-x delete-span\"></span>",
												"classEx" => "delete-item delete-account"
											]
										]
									]
								]
							];
							$table = view('components.tables.alwaysVisibleTable',[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"themeBody" => "striped",
								"noHead"    => true
							])->render();
							$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
						@endphp

						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						row = $(table);

						row.find('[name="idBanks[]"]').val(bank);
						row.find('[name="idBanks[]"]').parent().prepend(bankName);

						row.find('[name="alias[]"]').val(alias);
						row.find('[name="alias[]"]').parent().prepend(alias);

						row.find('[name="account[]"]').val(account);
						row.find('[name="account[]"]').parent().prepend(account);
						
						row.find('[name="branch[]"]').val(branch_office);
						row.find('[name="branch[]"]').parent().prepend(branch_office);

						row.find('[name="reference[]"]').val(reference);
						row.find('[name="reference[]"]').parent().prepend(reference);

						row.find('[name="clabe[]"]').val(clabe);
						row.find('[name="clabe[]"]').parent().prepend(clabe);

						row.find('[name="currency[]"]').val(currency);
						row.find('[name="currency[]"]').parent().prepend(currency);

						row.find('[name="iban[]"]').val(iban);
						row.find('[name="iban[]"]').parent().prepend(iban);

						row.find('[name="bic_swift[]"]').val(bic_swift);
						row.find('[name="bic_swift[]"]').parent().prepend(bic_swift);

						row.find('[name="agreement[]"]').val(agreement);
						row.find('[name="agreement[]"]').parent().prepend(agreement);

						row.find('.checkbox').attr('id', bank);
						row.find('.labelCheck').attr('for', bank);
						row.find('.checkbox').val(bank);
						
						$('#banks-body').append(row);
						$('.clabe,.account,.iban,.bic_swift').removeClass('valid').val('');
						$('.branch_office,.reference,.currency,.agreement,.alias,.iban,.bic_swift').val('');
						$(this).parents('#banks-body').find('.error').removeClass('error');
						$('.js-bank').val(0).trigger("change");
					}
				}
			}
			else
			{
				swal('', 'Seleccione un banco, por favor', 'error');
				$('.js-bank').addClass('error');
			}
		})
		.on('click','.delete-account', function()
		{
			bank_account_id = $(this).parents('.tr').find('.bank_account_id').val();
			if (bank_account_id != "x") 
			{
				delete_account = $('<input name="delete_account[]" type="hidden" value="'+bank_account_id+'">');
				$('#delete_account').append(delete_account);
			}
			$(this).parents('.tr').remove();
		})
		.on('change','.t_unitPrice,.t_subtotal,.t_typeTax,.t_amount_add_ret,.t_amount_add_tax',function()
		{
			idProvider	= $(this).attr('data-provider');
			idDetail	= $(this).attr('data-item');
			tr			= $(this).parents('.tr');

			quantity	= tr.find('.t_quantity').val();
			unitPrice	= tr.find('.t_unitPrice[data-provider="'+idProvider+'"]').val();
			subtotal 	= tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val();
			typeTax 	= tr.find('.t_typeTax[data-provider="'+idProvider+'"] option:selected').val();

			sum_taxes		= 0;
			sum_retentions	= 0;

			tr.find('.t_amount_add_tax[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_taxes += Number($(this).val());
			});

			tr.find('.t_amount_add_ret[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_retentions += Number($(this).val());
			});

			subtotal 	= quantity * unitPrice;

			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc	= 0;

			switch(typeTax)
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = quantity*unitPrice*iva;
					break;
				case 'b':
					ivaCalc = quantity*unitPrice*iva2;
					break;
			}
			total    = ((quantity * unitPrice)+ivaCalc + sum_taxes) - sum_retentions;

			if (total < 0) 
			{
				$(this).val('');
				swal('','El total no puede ser negativo','info');
			}
			else
			{
				tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val(subtotal.toFixed(2));
				tr.find('.t_iva[data-provider="'+idProvider+'"]').val(ivaCalc.toFixed(2));
				tr.find('.t_total[data-provider="'+idProvider+'"]').val(total.toFixed(2));
			}

		})
		.on('click','[name="btnSave"]',function(e)
		{
			e.preventDefault();

			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];

			object = $(this);
			
			if ($('.datepath').length > 0) 
			{
				$('.datepath').each(function(i,v)
				{
					fiscal_folio.push($(this).siblings('.fiscal_folio').val());
					ticket_number.push($(this).siblings('.ticket_number').val());
					timepath.push($(this).siblings('.timepath').val());
					amount.push($(this).siblings('.amount').val());
					datepath.push($(this).siblings('.datepath').val());
				});

				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("requisition.validation-document") }}',
					data	: 
					{
						'fiscal_folio'	: fiscal_folio,
						'ticket_number'	: ticket_number,
						'timepath'		: timepath,
						'amount'		: amount,
						'datepath'		: datepath,
					},
					success : function(data)
					{

						flag = false;
						$('.datepath').each(function(j,v)
						{

							ticket_number	= $(this).siblings('.ticket_number');
							fiscal_folio	= $(this).siblings('.fiscal_folio');
							timepath		= $(this).siblings('.timepath');
							amount			= $(this).siblings('.amount');
							datepath		= $(this).siblings('.datepath');

							ticket_number.removeClass('error').removeClass('valid');
							fiscal_folio.removeClass('error').removeClass('valid');
							timepath.removeClass('error').removeClass('valid');
							amount.removeClass('error').removeClass('valid');
							datepath.removeClass('error').removeClass('valid');

							$(data).each(function(i,d)
							{
								if (d == fiscal_folio.val() || d == ticket_number.val()) 
								{
									ticket_number.addClass('error')
									fiscal_folio.addClass('error');
									timepath.addClass('error');
									amount.addClass('error');
									datepath.addClass('error');
									flag = true;
								}
								else
								{
									ticket_number.addClass('valid')
									fiscal_folio.addClass('valid');
									timepath.addClass('valid');
									amount.addClass('valid');
									datepath.addClass('valid');
								}
							});
						});
						if (flag) 
						{
							swal('','Los documentos marcados ya se encuentran registrados.','error');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if (!flag) 
					{
						send(object);
					}
				});
			}
			else
			{
				send(object);
			}

			function send(object) 
			{

				flag = false;

				$('input[name="realPathRequisition[]').each(function(i,v)
				{
					nameDocument = $(this).siblings('.components-ex-up').find('[name="nameDocumentRequisition[]"] option:selected').val();
					if( $(this).val() == "" || nameDocument == undefined )
					{
			 			flag = true;
					}
				});

				if(flag)
				{
					swal('', 'Tiene un archivo sin agregar', 'error');
				}
				else
				{
					$('.remove').removeAttr('data-validation');
					$('.removeselect').removeAttr('required');
					$('.removeselect').removeAttr('data-validation');
					$('.request-validate').removeClass('request-validate');
					$('.validate-vote').removeClass('validate-vote');
					$('.provider_exists_requisition').removeClass('provider_exists_requisition');
					$('[name="data_validate"]').val('0');
					action	= object.attr('formaction');
					form	= object.parents('form').attr('action',action);
					form.submit();
				}
			}
		})
		.on('click','[name="addMultiProvider"]',function()
		{
			if ($('#result_queue_provider .table-row').length > 0) 
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
				$('.validate-vote').removeClass('validate-vote');
				$('.provider_exists_requisition').removeClass('provider_exists_requisition');
				$('[name="data_validate"]').val('0');
			}
		})
		.on('click','[name="btnDeleteProvider"],[name="idProviderBtn"],[name="export_excel"]',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			$('.validate-vote').removeClass('validate-vote');
			$('.provider_exists_requisition').removeClass('provider_exists_requisition');
			$('[name="data_validate"]').val('0');
		})
		.on('change','input[name="prov"]',function()
		{
			if ($('input[name="prov"]:checked').val() == "nuevo") 
			{
				$(".form-add-provider").fadeIn();
				$(".form-search-provider").fadeOut();
				@php
					$selects = collect(
						[
							[
								"identificator"          => "[name=\"state_idstate\"],.currency", 
								"placeholder"            => "Seleccione uno", 
								"language"				 => "es",
								"maximumSelectionLength" => "1"
							]
						]
					);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
				$('.phone').numeric({negative:false});
				generalSelect({'selector':'.cp', 'model':2});
				generalSelect({'selector':'.js-bank', 'model': 27});
			}
			else if ($('input[name="prov"]:checked').val() == "buscar") 
			{
				$(".form-search-provider").fadeIn();
				$(".form-add-provider").fadeOut();
			}
		})
		.on('click','#search_provider', function()
		{
			search_provider(1)
		})
		.on('click','.editResultProvider',function()
		{
			folio			= {{ $request->folio }};
			requisition_id	= {{ $request->requisition->id }};
			provider_id		= $(this).parents('.tr').find('.t_provider').val();
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("requisition.edit-provider") }}',
				data 	: {'folio':folio,'requisition_id':requisition_id,'provider_id':provider_id},
				success : function(data)
				{
					$('#result_provider_container').fadeOut();
					$('#form_edit_provider').html("");
					$('#form_edit_provider').append($('<form id="editProvideer"></form>').html(data));
					@php
						$selects = collect(
							[
								[
									"identificator"          => "[name=\"state_idstate_edit\"],.currency", 
									"placeholder"            => "Seleccione uno", 
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								]
							]
						);
					@endphp
					@component("components.scripts.selects",["selects" => $selects])@endcomponent
					generalSelect({'selector': '.js-bank', 'model': 27});
					generalSelect({'selector': '#cp', 'model': 2});
					validationEditProvideer();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#result_provider').fadeOut();
				}
			})
		})
		.on('click','#updateProvider',function(e)
		{
			e.preventDefault();
			$('[name="businessName_edit"],[name="address_edit"],[name="number_edit"],[name="colony_edit"],[name="postalCode_edit"],[name="city_edit"],[name="state_idstate_edit"],.rfc_edit,[name="phone_edit"],[name="contact_edit"],[name="beneficiary_edit"]').parent().find('.extra-error').remove();
			idRequisition 	= {{ $request->requisition->id }};
			businessName	= $('[name="businessName_edit"]').val();
			address			= $('[name="address_edit"]').val();
			number			= $('[name="number_edit"]').val();
			colony			= $('[name="colony_edit"]').val();
			postalCode		= $('[name="postalCode_edit"] option:selected').val();
			city			= $('[name="city_edit"]').val();
			state_idstate	= $('[name="state_idstate_edit"] option:selected').val();
			rfc				= $('.rfc_edit').val();
			phone			= $('[name="phone_edit"]').val();
			contact			= $('[name="contact_edit"]').val();
			beneficiary		= $('[name="beneficiary_edit"]').val();
			commentaries	= $('[name="commentaries_edit"]').val();
			idProvider 		= $('[name="idProviderSecondaryUpdate"]').val();
			idBanks = [];
			$('[name="idBanks[]"]').each(function(i,v)
			{
				idBanks.push($(this).val());
			});
			alias = [];
			$('[name="alias[]"]').each(function(i,v)
			{
				alias.push($(this).val());
			});
			account = [];
			$('[name="account[]"]').each(function(i,v)
			{
				account.push($(this).val());
			});
			branch = [];
			$('[name="branch[]"]').each(function(i,v)
			{
				branch.push($(this).val());
			});
			reference = [];
			$('[name="reference[]"]').each(function(i,v)
			{
				reference.push($(this).val());
			});
			clabe = [];
			$('[name="clabe[]"]').each(function(i,v)
			{
				clabe.push($(this).val());
			});
			currency = [];
			$('[name="currency[]"]').each(function(i,v)
			{
				currency.push($(this).val());
			});
			agreement = [];
			$('[name="agreement[]"]').each(function(i,v)
			{
				agreement.push($(this).val());
			});
			iban = [];
			$('[name="iban[]"]').each(function(i,v)
			{
				iban.push($(this).val());
			});
			bic_swift = [];
			$('[name="bic_swift[]"]').each(function(i,v)
			{
				bic_swift.push($(this).val());
			});
			delete_account = [];
			$('[name="delete_account[]"]').each(function(i,v)
			{
				delete_account.push($(this).val());
			});
			if (rfc == "" || businessName == "" || address == "" || number == "" || colony == "" || postalCode == "" ||city == "" || state_idstate == "" || rfc == "" || phone == "" || contact == "" || beneficiary == "") 
			{
				if (businessName == "")
				{
					if($('[name="businessName_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="businessName_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (address == "")
				{
					if($('[name="address_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="address_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}	
				}
				if (number == "")
				{
					if($('[name="number_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="number_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}	
				}
				if (colony == "")
				{
					if($('[name="colony_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="colony_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (postalCode == "")
				{
					if($('[name="postalCode_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="postalCode_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (city == "")
				{
					if($('[name="city_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="city_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (state_idstate == "" || state_idstate == undefined)
				{
					if($('[name="state_idstate_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="state_idstate_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (rfc == "")
				{
					if($('.rfc_edit').parent().hasClass('has-error') == false)
					{
						$('.rfc_edit').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (phone == "")
				{
					if($('[name="phone_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="phone_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (contact == "")
				{
					if($('[name="contact_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="contact_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				if (beneficiary == "")
				{
					if($('[name="beneficiary_edit"]').parent().hasClass('has-error') == false)
					{
						$('[name="beneficiary_edit"]').parent().append('<span class="extra-error form-error">Este campo es obligatorio</span>');
					}
				}
				swal('Error','Por favor llene los campos obligatorios.','error');
			}
			else
			{
				if($('[name="businessName_edit"],[name="address_edit"],[name="number_edit"],[name="colony_edit"],[name="postalCode_edit"],[name="city_edit"],[name="state_idstate_edit"],.rfc_edit,[name="phone_edit"],[name="contact_edit"],[name="beneficiary_edit"]').hasClass('error'))
				{
					swal('Error','Por favor llene los campos correctamente.','error');
				}
				else
				{
					$.ajax(
					{
						type 	: 'get',
						url 	: '{{ route("requisition.update-provider") }}',
						data 	: {
							'businessName'	:businessName,
							'address'		:address,
							'number'		:number,
							'colony'		:colony,
							'postalCode'	:postalCode,
							'city'			:city,
							'state_idstate'	:state_idstate,
							'rfc'			:rfc,
							'phone'			:phone,
							'contact'		:contact,
							'beneficiary'	:beneficiary,
							'commentaries'	:commentaries,
							'idBanks'		:idBanks,
							'alias'			:alias,
							'account'		:account,
							'branch'		:branch,
							'reference'		:reference,
							'clabe'			:clabe,
							'currency'		:currency,
							'iban'			:iban,
							'bic_swift'		:bic_swift,
							'agreement'		:agreement,
							'idProvider' 	:idProvider,
							'delete_account':delete_account,
	
						},
						success : function(data)
						{
							swal('Actualizado','Proveedor Actualizado','success');
							$('#form_edit_provider').empty();
							$('#result_provider').fadeIn();
							$('#delete_account').empty();
							
						},
						error : function(data)
						{
							swal('','Sucedió un error, por favor intente de nuevo.','error');
							return false;
						}
					}).done(function(data)
					{
						idProvider = [];
						$('[name="multiprovider[]"]').each(function(i,v)
						{
							idProvider.push($(this).val());
						});

						$('.provider_exists_requisition').each(function(i,v)
						{
							idProvider.push($(this).val());
						});

						text = $('.input-search').val().trim();
						folio = {{ $request->folio }};
						$.ajax(
						{
							type	: 'post',
							url		: '{{ route("requisition.search-provider") }}',
							data	: {'text':text,'folio':folio,'idProvider':idProvider},
							success	: function(data)
							{
								$('#result_provider_container').removeAttr('style');
								$('#result_provider').html(data);
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
								$('#result_provider').html('');
								return false;
							}
						});
					});
					// return false;
				}
			}
		})
		.on('click','[data-dismiss="modal"]',function()
		{
			$('#result_provider').fadeIn();
			$('#form_edit_provider').empty();
		})
		.on('click','.btnCommentView',function()
		{
			comment = $(this).parent('td').find('.view-comment').val();
			$('[name="commentView"]').val(comment);

		})
		.on('click','.modalComment',function()
		{
			$('.span_commentaries').removeAttr('data-temp');
			id_detail = $(this).attr('data-detail');
			$('[name="id_detail"]').val(id_detail);

			id_provider = $(this).attr('data-provider');
			$('[name="id_provider"]').val(id_provider);

			comment = $(this).parent('td').find('.edit-comment').val();
			$('[name="comment"]').val(comment);

			$(this).parent('td').find('.span_commentaries').attr('data-temp','1');
		})
		.on('click','[name="btnAddCommentaries"]',function()
		{
			$('[data-temp="1"]').empty();
			id_detail	= $('[name="id_detail"]').val();
			id_provider	= $('[name="id_provider"]').val();
			comment 	= $('[name="comment"]').val();
			input		= $('<input type="hidden" class="edit-comment" value="'+comment+'" name="commentaries_'+id_detail+'">');

			$('[data-temp="1"]').append(input);

			$('[data-temp="1"]').removeAttr('data-temp');
			$('#newComment').hide();
			swal('Comentario agregado','','success');

			$('[name="id_detail"]').val('');
			$('[name="id_provider"]').val('');
			$('[name="comment"]').val('');
		})
		.on('click','.addDocumentProvider',function()
		{
			idRequisitionHasProvider = $(this).parent('div').find('.id_provider_secondary').val();
			$('[name="idRequisitionHasProviderDoc"]').val(idRequisitionHasProvider);
			@php
				$selects = collect([
					[
						"identificator"          => ".nameDocument", 
						"placeholder"            => "Seleccione el tipo de documento", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
		})
		.on('click','#addDocProvider',function()
		{
			@php
				$options = collect(
					[
						[
							"value"       => "Cotización", 
							"description" => "Cotización"
						], 
						[
							"value"       => "Ficha Técnica", 
							"description" => "Ficha Técnica"
						], 
						[
							"value"       => "Control de Calidad", 
							"description" => "Control de Calidad"
						], 
						[
							"value"       => "Contrato", 
							"description" => "Contrato"
						], 
						[
							"value"       => "Factura", 
							"description" => "Factura"
						], 
						[
							"value"       => "REQ. OC. FAC.", 
							"description" => "REQ. OC. FAC."
						], 
						[
							"value"       => "Otro", 
							"description" => "Otro"
						]
					]
				);
				$labelSelect = view('components.labels.label',[
					"label" => "Selecciona el tipo de archivo",
				])->render();
				$select = view('components.inputs.select',[
					"options" => $options,
					"classEx" => "custom-select nameDocument", 
					"attributeEx" => "name=\"nameDocument[]\"",
				])->render();
				$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
				$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
				$newDoc = view('components.documents.upload-files',[
					"attributeExRealPath" => "name=\"realPath[]\"",
					"classExRealPath" => "path",				
					"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
					"classExInput" => "pathActioner",
					"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
					"classExDelete" => "delete-doc"
				])->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			$('#documents').append(newDoc);
			@php
				$selects = collect([
					[
						"identificator"          => "[name=\"nameDocument[]\"]", 
						"placeholder"            => "Seleccione el tipo de documento", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			$('[name="btnAddProviderDocuments"]').prop('disabled',false);
		})		
		.on('click','#addDocRequisition',function()
		{
			@switch($request->requisition->requisition_type)
				@case(1)
				@case(2)
				@case(3)
				@case(5)
					@php
						$options = collect(
							[
								[
									"value"       => "Cotización", 
									"description" => "Cotización"
								], 
								[
									"value"       => "Ficha Técnica", 
									"description" => "Ficha Técnica"
								], 
								[
									"value"       => "Control de Calidad", 
									"description" => "Control de Calidad"
								], 
								[
									"value"       => "Contrato", 
									"description" => "Contrato"
								], 
								[
									"value"       => "Factura", 
									"description" => "Factura"
								], 
								[
									"value"       => "REQ. OC. FAC.", 
									"description" => "REQ. OC. FAC."
								], 
								[
									"value"       => "Otro", 
									"description" => "Otro"
								]
							]
						);
						$labelSelect = view('components.labels.label',[
							"label" => "Selecciona el tipo de archivo",
						])->render();
						$select = view('components.inputs.select',[
							"options" => $options,
							"classEx" => "nameDocumentRequisition", 
							"attributeEx" => "name=\"nameDocumentRequisition[]\" multiple=\"multiple\" data-validation=\"required\"",
						])->render();
						$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
						$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
						$newDoc = view('components.documents.upload-files',[
							"attributeExRealPath" => "name=\"realPathRequisition[]\"",
							"classExRealPath" => "path",					
							"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExInput" => "pathActionerRequisition",
							"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
							"classExDelete" => "delete-doc",
							"componentsExDown"		=>  [
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Fecha",
									"classEx" => "datepicker datepath hidden pt-2",
								],
								[
									"kind" 	=> "components.inputs.input-text", 
									"classEx" => "datepicker datepath hidden pb-2",
									"attributeEx"	=> "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Hora",
									"classEx" => "timepath hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "timepath hidden pb-2",
									"attributeEx"	=> "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Folio fiscal",
									"classEx" => "fiscal_folio hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "fiscal_folio hidden pb-2",
									"attributeEx"	=> "name=\"fiscal_folio[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Número de ticket",
									"classEx" => "ticket_number hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "ticket_number hidden pb-2",
									"attributeEx"	=> "name=\"ticket_number[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Monto total",
									"classEx" => "amount hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "amount hidden pb-2",
									"attributeEx"	=> "name=\"amount[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\""
								],
							],
						])->render();
					@endphp
					newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				@break
				@case(4)
					@php
						$options = collect(
							[
								[
									"value"       => "Acta Constitutiva", 
									"description" => "Acta Constitutiva"
								], 
								[
									"value"       => "Poder del representante legal", 
									"description" => "Poder del representante legal"
								], 
								[
									"value"       => "Identificación oficial", 
									"description" => "Identificación oficial"
								], 
								[
									"value"       => "RFC", 
									"description" => "RFC"
								], 
								[
									"value"       => "Cedula Fiscal", 
									"description" => "Cedula Fiscal"
								], 
								[
									"value"       => "Domicilio", 
									"description" => "Domicilio"
								], 
								[
									"value"       => "CV", 
									"description" => "CV"
								], 
								[
									"value"       => "Revisión técnica", 
									"description" => "Revisión técnica"
								], 
								[
									"value"       => "Anexos", 
									"description" => "Anexos"
								], 
								[
									"value"       => "Pólizas de Fianza", 
									"description" => "Pólizas de Fianza"
								]
							]
						);
						$labelSelect = view('components.labels.label',[
							"label" => "Selecciona el tipo de archivo",
						])->render();
						$select = view('components.inputs.select',[
							"options" => $options,
							"classEx" => "nameDocumentRequisition", 
							"attributeEx" => "name=\"nameDocumentRequisition[]\" multiple=\"multiple\" data-validation=\"required\"",
						])->render();
						$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
						$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
						$newDoc = view('components.documents.upload-files',[
							"attributeExRealPath" => "name=\"realPathRequisition[]\"",
							"classExRealPath" => "path",					
							"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExInput" => "pathActionerRequisition",
							"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
							"classExDelete" => "delete-doc"
						])->render();
					@endphp
					newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				@break
				@default
					@php
						$options = collect(
							[
								[
									"value"       => "Cotización", 
									"description" => "Cotización"
								], 
								[
									"value"       => "Ficha Técnica", 
									"description" => "Ficha Técnica"
								], 
								[
									"value"       => "Control de Calidad", 
									"description" => "Control de Calidad"
								], 
								[
									"value"       => "Contrato", 
									"description" => "Contrato"
								], 
								[
									"value"       => "Factura", 
									"description" => "Factura"
								], 
								[
									"value"       => "REQ. OC. FAC.", 
									"description" => "REQ. OC. FAC."
								], 
								[
									"value"       => "Otro", 
									"description" => "Otro"
								]
							]
						);
						$labelSelect = view('components.labels.label',[
							"label" => "Selecciona el tipo de archivo",
						])->render();
						$select = view('components.inputs.select',[
							"options" => $options,
							"classEx" => "nameDocumentRequisition", 
							"attributeEx" => "name=\"nameDocumentRequisition[]\" multiple=\"multiple\" data-validation=\"required\"",
						])->render();
						$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
						$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
						$newDoc = view('components.documents.upload-files',[
							"attributeExRealPath" => "name=\"realPathRequisition[]\"",
							"classExRealPath" => "path",					
							"attributeExInput" => "name=\"path\" accept=\".pdf,.jpg,.png\"",
							"classExInput" => "pathActionerRequisition",
							"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
							"classExDelete" => "delete-doc",
							"componentsExDown"		=>  
							[
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Fecha",
									"classEx" => "datepicker datepath hidden pt-2",
								],
								[
									"kind" 	=> "components.inputs.input-text", 
									"classEx" => "datepicker datepath hidden pb-2",
									"attributeEx"	=> "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Hora",
									"classEx" => "timepath hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "timepath hidden pb-2",
									"attributeEx"	=> "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Folio fiscal",
									"classEx" => "fiscal_folio hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "fiscal_folio hidden pb-2",
									"attributeEx"	=> "name=\"fiscal_folio[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Número de ticket",
									"classEx" => "ticket_number hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "ticket_number hidden pb-2",
									"attributeEx"	=> "name=\"ticket_number[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Monto total",
									"classEx" => "amount hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "amount hidden pb-2",
									"attributeEx"	=> "name=\"amount[]\" placeholder=\"Ingrese el monto total\" data-validation=\"required\""
								],
							],
						])->render();
					@endphp
					newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				@break
			@endswitch
			
			$('#documents-requisition').append(newDoc);
			$('[name="amount[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
			$('.datepicker').datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			$('.timepath').daterangepicker({
				timePicker : true,
				singleDatePicker:true,
				timePicker24Hour : true,
				autoApply: true,
				locale : {
					format : 'HH:mm',
					"applyLabel": "Seleccionar",
					"cancelLabel": "Cancelar",
				}
			})
			.on('show.daterangepicker', function (ev, picker) 
			{
				picker.container.find(".calendar-table").remove();
			});

			@php
				$selects = collect([
					[
						"identificator"          => "[name=\"nameDocumentRequisition[]\"]", 
						"placeholder"            => "Seleccione el tipo de documento", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
		})
		.on('change','.pathActionerRequisition',function(e)
		{
			alert(1);
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("requisition.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
					}
				})
			}
		})
		.on('change','.pathActioner',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPath[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("requisition.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPath[]"]').val('');
					}
				})
			}
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon	: '{{ asset(getenv('LOADING_IMG')) }}',
				button	: false
			});
			actioner		= $(this);
			uploadedName	= $(this).parent().siblings('input[name="realPath[]"]');
			formData		= new FormData();
			formData.append(uploadedName.attr('name'),uploadedName.val());
			$.ajax(
			{
				type		: 'post',
				url			: '{{ route("requisition.upload") }}',
				data		: formData,
				contentType	: false,
				processData	: false,
				success		: function(r)
				{
					swal.close();
					actioner.parents('.docs-p').remove();
					if($('#documents .docs-p').length < 1)
					{
						$('[name="btnAddProviderDocuments"]').prop('disabled',true);
					}
				},
				error		: function()
				{
					swal.close();
					actioner.parents('.docs-p').remove();
				}
			});
			$(this).parents('div.docs-p').remove();
		})
		.on('click','.closeViewDocument',function()
		{
			$('.modal-view-document').empty();
		})
		.on('click','.viewDocumentProvider',function()
		{
			id = $(this).attr('data-id');
			$.ajax(
			{
				type 	: 'post',
				url 	: '{{ route("requisition.provider-documents.view") }}',
				data 	: {
					'id':id,
				},
				success : function(data)
				{
					$('.modal-view-document').html(data);
				},
				error : function(data)
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#viewDocumentProvider').hide();
				}
			})
		})
		.on('click','.remove-queue',function()
		{
			$('#result_queue_provider').append(queue);
			$(this).parents('.table-row').remove();
			
			if($('#result_queue_provider .table-row').length > 0)
			{
				$('.table-queue-provider').addClass("block").removeClass("hidden");
			}
			else
			{
				$('.table-queue-provider').addClass("hidden").removeClass("block");
			}

			idProvider = [];
			$('[name="multiprovider[]"]').each(function(i,v)
			{
				idProvider.push($(this).val());
			});

			$('.provider_exists_requisition').each(function(i,v)
			{
				idProvider.push($(this).val());
			});

			text = $('.input-search').val().trim();
			folio = {{ $request->folio }};
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("requisition.search-provider") }}',
				data	: {'text':text,'folio':folio,'idProvider':idProvider},
				success	: function(data)
				{
					$('#result_provider').html(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#result_provider').html('');
				}
			});
		})
		.on('click','.add-queue',function()
		{
			id				= $(this).attr('data-provider-id');
			businessName	= $(this).attr('data-provider-business-name');
			rfc				= $(this).attr('data-provider-rfc');

			@php
				$modelHead =  ["ID", "Nombre", "RFC", "Eliminar"];

				$modelBody = 
				[
					[
						[
							"content" => 
							[
								[
									"kind"        => "components.labels.label",
									"classEx"     => "id-selected-provider" 
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" name=\"multiprovider[]\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "business-name-provider" 
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"    => "components.labels.label",
									"classEx" => "rfc-provider" 
								]
							]
						],
						[
							"content" => 
							[
								[
									"kind"    => "components.buttons.button",
									"classEx" => "remove-queue",
									"label"   => "<span class=\"icon-x\"></span>",
									"variant" => "red"
								]
							]		
						]
					]
				];
				$table = view("components.tables.alwaysVisibleTable", ["modelHead" => $modelHead, "modelBody" => $modelBody, "noHead" => "true"])->render();
			@endphp
			table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
			queue = $(table);
			queue  = rowColor('#result_queue_provider', queue);
			queue.find(".id-selected-provider").append(id);
			queue.find('[name="multiprovider[]"]').val(id);
			queue.find(".business-name-provider").append(businessName);
			queue.find(".rfc-provider").append(rfc);
			$('#result_queue_provider').append(queue);
			$(this).parents('.tr').remove();

			if($('#result_queue_provider .table-row').length > 0)
			{
				$('.table-queue-provider').addClass("block").removeClass("hidden");
			}
			else
			{
				$('.table-queue-provider').addClass("hidden").removeClass("block");
			}
		})		
		.on('click','#addDocNewProvider',function()
		{
			@php
				$options = collect(
					[
						[
							"value"       => "Cotización", 
							"description" => "Cotización"
						], 
						[
							"value"       => "Ficha Técnica", 
							"description" => "Ficha Técnica"
						], 
						[
							"value"       => "Control de Calidad", 
							"description" => "Control de Calidad"
						], 
						[
							"value"       => "Contrato", 
							"description" => "Contrato"
						], 
						[
							"value"       => "Factura", 
							"description" => "Factura"
						], 
						[
							"value"       => "REQ. OC. FAC.", 
							"description" => "REQ. OC. FAC."
						], 
						[
							"value"       => "Otro", 
							"description" => "Otro"
						]
					]
				);
				$labelSelect = view('components.labels.label',[
					"label"   => "Selecciona el tipo de archivo",
				])->render();
				$select = view('components.inputs.select',[
					"options"     => $options,
					"classEx"     => "custom-select nameDocumentNewProvider", 
					"attributeEx" => "name=\"nameDocumentNewProvider[]\" multiple=\"multiple\" data-validation=\"required\"",
				])->render();
				$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
				$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
				$newDoc = view('components.documents.upload-files',
					[
						"attributeExRealPath" => "name=\"realPathNewProvider[]\" data-validation=\"required\"",
						"classExRealPath"     => "path",					
						"attributeExInput"    => "name=\"path\" \"accept=.pdf,.jpg,.png\"",
						"classExInput"        => "pathActionerNewProvider",
						"componentsExUp"      => $labelSelect.$select." <div class=\"componentsEx\"></div>",
						"classExDelete"       => "delete-doc",
					]
				)->render();
			@endphp
			newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
			$('#documents-new-provider').append(newDoc);
			$('.nameDocumentNewProvider').select2(
				{
					language				: "es",
					maximumSelectionLength	: 1,
					placeholder 			: "Seleccione uno"
				})
				.on("change",function(e)
				{
					if($(this).val().length>1)
					{
						$(this).val($(this).val().slice(0,1)).trigger('change');
					}
				}
			);
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		})
		.on('change','.pathActionerNewProvider',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPathNewProvider[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
			}
			else
			{
				$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
				{
					return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
				});
				formData	= new FormData();
				formData.append(filename.attr('name'), filename.prop("files")[0]);
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route("requisition.upload") }}',
					data		: formData,
					contentType	: false,
					processData	: false,
					success		: function(r)
					{
						if(r.error=='DONE')
						{
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathNewProvider[]"]').val(r.path);
							$(e.currentTarget).val('');
						}
						else
						{
							swal('',r.message, 'error');
							$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(e.currentTarget).val('');
							$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathNewProvider[]"]').val('');
						}
					},
					error: function()
					{
						swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
						$(e.currentTarget).removeAttr('style').parent('.uploader-content').removeClass('loading');
						$(e.currentTarget).val('');
						$(e.currentTarget).parent('.uploader-content').siblings('input[name="realPathNewProvider[]"]').val('');
					}
				})
			}
		})
		.on('click','.add-tax',function()
		{
			data_provider	= $(this).attr('data-provider');
			data_item		= $(this).attr('data-item');

			@php
				$componentTaxes = view("components.templates.inputs.taxesRequisitions",
					[
						"name"        => "tax",
						"detailId"    => "",
						"new"		  => "true",
						"providerId"  => "",
						"classExButton"	=> "p-2.5"
					]
				)->render();
			@endphp
			component = '{!!preg_replace("/(\r)*(\n)*/", "", $componentTaxes)!!}';

			element = $(component);
			element.find(".tax_ret_id").attr('name', "tax_id_"+data_item+"_"+data_provider+"[]");
			element.find(".tax_ret_id").parent().addClass("taxes-row-"+data_item+"-"+data_provider);
			element.find(".t_name_add_tax").attr('name', "name_add_tax_"+data_item+"_"+data_provider+"[]").attr('data-provider', data_provider).attr('data-item', data_item);
			element.find(".t_amount_add_tax").attr('name', "amount_add_tax_"+data_item+"_"+data_provider+"[]").attr('data-provider', data_provider).attr('data-item', data_item);
			element.find(".delete-tax").attr('data-provider', data_provider).attr('data-item', data_item);
			$(this).parents('.div-button').siblings('.add_taxes[data-provider="'+data_provider+'"]').append(element);
			$('.t_amount_add_tax').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false });
		})
		.on('click','.add-ret',function()
		{
			data_provider	= $(this).attr('data-provider');
			data_item		= $(this).attr('data-item');
			@php
				$componentRets = view("components.templates.inputs.taxesRequisitions",
					[
						"name"        => "ret",
						"detailId"    => "",
						"new"		  => "true",
						"providerId"  => "",
						"classExButton"	=> "p-2.5"
					]
				)->render();
			@endphp
			component = '{!!preg_replace("/(\r)*(\n)*/", "", $componentRets)!!}';

			element = $(component);
			element.find(".tax_ret_id").attr('name', "ret_id_"+data_item+"_"+data_provider+"[]");
			element.find(".tax_ret_id").parent().addClass("taxes-row-"+data_item+"-"+data_provider);
			element.find(".t_name_add_ret").attr('name', "name_add_ret_"+data_item+"_"+data_provider+"[]").attr('data-provider', data_provider).attr('data-item', data_item);
			element.find(".t_amount_add_ret").attr('name', "amount_add_ret_"+data_item+"_"+data_provider+"[]").attr('data-provider', data_provider).attr('data-item', data_item);
			element.find(".delete-ret").attr('data-provider', data_provider).attr('data-item', data_item);
			$(this).parents('.div-button').siblings('.add_retentions[data-provider="'+data_provider+'"]').append(element);
			$('.t_amount_add_ret').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false });
		})
		.on('click','.delete-tax',function()
		{
			idProvider	= $(this).attr('data-provider');
			idDetail	= $(this).attr('data-item');

			tr	= $(this).parents('.tr');
			id	= $(this).parents(".taxes-row-"+idDetail+"-"+idProvider).find('.tax_ret_id').val();

			quantity	= tr.find('.t_quantity').val();
			unitPrice	= tr.find('.t_unitPrice[data-provider="'+idProvider+'"]').val();
			subtotal 	= tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val();
			typeTax 	= tr.find('.t_typeTax[data-provider="'+idProvider+'"] option:selected').val();
			
			if (id != "x") 
			{
				deleteID = $('<input type="hidden" name="deleteTaxes[]" value="'+id+'">');
				$('#spanDelete').append(deleteID);
			}
			$(this).parents(".taxes-row-"+idDetail+"-"+idProvider).remove();


			sum_taxes		= 0;
			sum_retentions	= 0;

			tr.find('.t_amount_add_tax[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_taxes += Number($(this).val());
			});

			tr.find('.t_amount_add_ret[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_retentions += Number($(this).val());
			});

			subtotal 	= quantity * unitPrice;

			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc	= 0;
			
			switch(typeTax)
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = quantity*unitPrice*iva;
					break;
				case 'b':
					ivaCalc = quantity*unitPrice*iva2;
					break;
			}
			total    = ((quantity * unitPrice)+ivaCalc + sum_taxes) - sum_retentions;
			tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val(subtotal.toFixed(2));
			tr.find('.t_iva[data-provider="'+idProvider+'"]').val(ivaCalc.toFixed(2));
			tr.find('.t_total[data-provider="'+idProvider+'"]').val(total.toFixed(2));
		})
		.on('click','.delete-ret',function()
		{
			idProvider	= $(this).attr('data-provider');
			idDetail	= $(this).attr('data-item');
			tr			= $(this).parents('.tr');
			quantity	= tr.find('.t_quantity').val();
			unitPrice	= tr.find('.t_unitPrice[data-provider="'+idProvider+'"]').val();
			subtotal 	= tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val();
			typeTax 	= tr.find('.t_typeTax[data-provider="'+idProvider+'"] option:selected').val();
			id	= tr.find('.tax_ret_id').val();

			if (id != "x") 
			{
				deleteID = $('<input type="hidden" name="deleteTaxes[]" value="'+id+'">');
				$('#spanDelete').append(deleteID);
			}
			// $(this).parent('div').remove();
			$(this).parents(".taxes-row-"+idDetail+"-"+idProvider).remove();
			sum_taxes		= 0;
			sum_retentions	= 0;

			tr.find('.t_amount_add_tax[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_taxes += Number($(this).val());
			});

			tr.find('.t_amount_add_ret[data-provider="'+idProvider+'"]').each(function(i,v)
			{
				sum_retentions += Number($(this).val());
			});

			subtotal 	= quantity * unitPrice;

			iva		= ({{ App\Parameter::where('parameter_name','IVA')->first()->parameter_value }})/100;
			iva2	= ({{ App\Parameter::where('parameter_name','IVA2')->first()->parameter_value }})/100;
			ivaCalc	= 0;

			switch(typeTax)
			{
				case 'no':
					ivaCalc = 0;
					break;
				case 'a':
					ivaCalc = quantity*unitPrice*iva;
					break;
				case 'b':
					ivaCalc = quantity*unitPrice*iva2;
					break;
			}
			total    = ((quantity * unitPrice)+ivaCalc + sum_taxes) - sum_retentions;

			tr.find('.t_subtotal[data-provider="'+idProvider+'"]').val(subtotal.toFixed(2));
			tr.find('.t_iva[data-provider="'+idProvider+'"]').val(ivaCalc.toFixed(2));
			tr.find('.t_total[data-provider="'+idProvider+'"]').val(total.toFixed(2));
		})
		.on('change','.nameDocumentRequisition',function()
		{
			type_document = $('option:selected',this).val();
			switch(type_document)
			{
				case 'Factura': 
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.fiscal_folio').show().removeClass('error').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.ticket_number').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.amount').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').show().removeClass('error').val('');	
					break;
				case 'Ticket': 
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.fiscal_folio').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.ticket_number').show().removeClass('error').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.amount').show().removeClass('error').val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').show().removeClass('error').val('');	
					break;
				default :  
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.fiscal_folio').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.ticket_number').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.amount').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.timepath').hide().attr("style", "display:none").val('');
					$(this).parents('.components-ex-up').siblings('.components-ex-down').find('.datepath').show().removeClass('error').val('');	
					break;
			}
		})
		.on('click','[name="send"]',function(e)
		{
			e.preventDefault();

			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];

			object = $(this);
			
			if ($('.datepath').length > 0) 
			{
				$('.datepath').each(function(i,v)
				{
					fiscal_folio.push($(this).parents('.docs-p').find('.fiscal_folio').val());
					ticket_number.push($(this).parents('.docs-p').find('.ticket_number').val());
					timepath.push($(this).parents('.docs-p').find('.timepath').val());
					amount.push($(this).parents('.docs-p').find('.amount').val());
					datepath.push($(this).parents('.docs-p').find('.datepath').val());
				});
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("requisition.validation-document") }}',
					data	: 
					{
						'fiscal_folio'	: fiscal_folio,
						'ticket_number'	: ticket_number,
						'timepath'		: timepath,
						'amount'		: amount,
						'datepath'		: datepath,
					},
					success : function(data)
					{

						flag = false;
						$('.datepath').each(function(j,v)
						{

							ticket_number	= $(this).siblings('.ticket_number');
							fiscal_folio	= $(this).siblings('.fiscal_folio');
							timepath		= $(this).siblings('.timepath');
							amount			= $(this).siblings('.amount');
							datepath		= $(this).siblings('.datepath');

							ticket_number.removeClass('error').removeClass('valid');
							fiscal_folio.removeClass('error').removeClass('valid');
							timepath.removeClass('error').removeClass('valid');
							amount.removeClass('error').removeClass('valid');
							datepath.removeClass('error').removeClass('valid');

							$(data).each(function(i,d)
							{
								if (d == fiscal_folio.val() || d == ticket_number.val()) 
								{
									ticket_number.addClass('error')
									fiscal_folio.addClass('error');
									timepath.addClass('error');
									amount.addClass('error');
									datepath.addClass('error');
									flag = true;
								}
								else
								{
									ticket_number.addClass('valid')
									fiscal_folio.addClass('valid');
									timepath.addClass('valid');
									amount.addClass('valid');
									datepath.addClass('valid');
								}
							});
						});
						if (flag) 
						{
							swal('','Los documentos marcados ya se encuentran registrados.','error');
						}
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
				.done(function(data)
				{
					if (!flag) 
					{
						send(object);
					}
				});
			}
			else
			{
				send(object);
			}

			function send(object) 
			{

				flag = false;

				$('input[name="realPathRequisition[]').each(function(i,v)
				{
					nameDocument = $(this).siblings('.components-ex-up').find('[name="nameDocumentRequisition[]"] option:selected').val();
					if( $(this).val() == "" || nameDocument == undefined )
					{
			 			flag = true;
					}
				});

				if(flag)
				{
					swal('', 'Tiene un archivo sin agregar', 'error');
				}
				else
				{
					$('.remove-validation-concept').removeAttr('data-validation');
					form	= object.parents('form');
					form.submit();
				}
			}

			
		})
		.on('change','.fiscal_folio,.ticket_number,.timepath,.amount,.datepath',function()
		{
			$('.datepath').each(function(i,v)
			{
				row          = 0;
				first_fiscal		= $(this).siblings('.fiscal_folio');
				first_ticket_number	= $(this).siblings('.ticket_number');
				first_monto			= $(this).siblings('.amount');
				first_timepath		= $(this).siblings('.timepath');
				first_datepath		= $(this).siblings('.datepath');
				first_name_doc		= $(this).siblings('.nameDocumentRequisition option:selected').val();

				$('.datepath').each(function(j,v)
				{

					scnd_fiscal		= $(this).siblings('.fiscal_folio');
					scnd_ticket_number	= $(this).siblings('.ticket_number');
					scnd_monto		= $(this).siblings('.amount');
					scnd_timepath	= $(this).siblings('.timepath');
					scnd_datepath	= $(this).siblings('.datepath');
					scnd_name_doc	= $(this).siblings('.nameDocumentRequisition option:selected').val();
					scnd_doc = $(this).siblings('.datepath').val();

					if (i!==j) 
					{
						if (first_name_doc == "Factura") 
						{
							if (first_fiscal.val() != "" && first_timepath.val() != "" && first_datepath.val() != ""  && scnd_datepath.val() != "" && scnd_timepath.val() != "" && scnd_fiscal.val() != "" && first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_fiscal.val().toUpperCase() == scnd_fiscal.val().toUpperCase()) 
							{
								swal('', 'Esta factura ya ha sido registrada en esta solicitud, intenta nuevamente.', 'error');
								scnd_fiscal.val('').removeClass('valid').addClass('error');
								scnd_timepath.val('').removeClass('valid').addClass('error');
								scnd_datepath.val('').removeClass('valid').addClass('error');
								$(this).parents('.docs-p-l').find('span.form-error').remove();
								return;
							}
						}

						if (first_name_doc == "Ticket") 
						{
							if (first_name_doc == scnd_name_doc && first_datepath.val() == scnd_datepath.val() && first_timepath.val() == scnd_timepath.val() && first_ticket_number.val().toUpperCase() == scnd_ticket_number.val().toUpperCase() && first_monto.val() == scnd_monto.val()) 
							{
								swal('', 'Este ticket ya ha sido registrado en esta solicitud, intenta nuevamente.', 'error');
								scnd_ticket_number.val('').addClass('error');
								scnd_timepath.val('').addClass('error');
								scnd_datepath.val('').addClass('error');
								scnd_monto.val('').addClass('error');
								$(this).parents('.docs-p-l').find('span.form-error').remove();
								return;
							}
						}
					}

				});
			});
		})
		// .on('focusout','[name="rfc"]',function()
		// {
		// 	rfc		= $(this).val();
		// 	object	= $(this);
		// 	oldRfc  = $(this).attr('data-old-rfc');
		// 	object.parent('p').find(".help-block").remove();

		// 	$.ajax(
		// 	{
		// 		type		: 'post',
		// 		url			: '{{route('requisition.provider-validation')}}',
		// 		data		: { 'rfc':rfc,'oldRfc':oldRfc },
		// 		success		: function(data)
		// 		{
		// 			if (data != "") 
		// 			{
		// 				if (object.parent('p').find(".help-block").length == 0) 
		// 				{
		// 					object.removeClass('valid');
		// 					object.addClass(data['class']);
		// 					if (data['message'] != "") 
		// 					{
		// 						object.parent('p').append('<span class="help-block form-error">'+data['message']+'</span>');
		// 					}
		// 				}
		// 			}
		// 			else
		// 			{
		// 				object.removeClass('error').addClass(data['class']);
		// 			}
		// 		}
		// 	});
		// })
		.on('focusout','.businessName',function()
		{
			reason	= $(this).val();
			object	= $(this);
			oldReason = $(this).attr('data-old-reason')
			object.parent().find(".help-block").remove(); //object with "help-block" class not found

			$.ajax(
			{
				type		: 'post',
				url			: '{{route('requisition.provider-validation')}}',
				data		: { 'reason':reason, 'oldReason':oldReason },
				success		: function(data)
				{
					if (data != "") 
					{
						if (object.parent().find(".help-block").length == 0) 
						{
							object.removeClass('valid');
							object.addClass(data['class']);
							if (data['message'] != "") 
							{
								object.parent().append('<span class="help-block form-error">'+data['message']+'</span>');
							}
						}
					}
					else
					{
						object.removeClass('error').addClass(data['class']);
					}
				}
			});
		})
		.on('click','.view-employee',function()
		{
			employee_id = $(this).parent().find('[name="rq_employee_id[]"]').val();
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route('requisition.view-detail-employee') }}',
				data	: {'employee_id':employee_id},
				success : function(data)
				{
					$('.modal-employee').html(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#detailEmployee').hide();
				}
			});
		})
		.on('click','#return',function()
		{
			$('#form_edit_provider').empty();
			$('#result_provider').fadeIn();

			idProvider = [];
			$('[name="multiprovider[]"]').each(function(i,v)
			{
				idProvider.push($(this).val());
			});

			$('.provider_exists_requisition').each(function(i,v)
			{
				idProvider.push($(this).val());
			});

			text = $('.input-search').val().trim();
			folio = {{ $request->folio }};
			$.ajax(
			{
				type	: 'post',
				url		: '{{ route("requisition.search-provider") }}',
				data	: {'text':text,'folio':folio,'idProvider':idProvider},
				success	: function(data)
				{
					$('#result_provider').html(data);
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#result_provider').html('');
				}
			});
		})
		{{-- 
		.on('keypress','.input-search',function(e)
		{
			if (e.which == 13) 
			{
				return false;
			}
		})
		 --}}
	});
</script>
@endsection