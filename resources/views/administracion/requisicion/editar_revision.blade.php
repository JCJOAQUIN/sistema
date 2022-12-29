@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('requisition.review.update',$request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT", "files" => true])
		<div class="sm:text-center text-left my-5">
			A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
		</div>
		@php
			$requestUser   = App\User::find($request->idRequest);
			$elaborateUser = App\User::find($request->idElaborate);
			$modelTable	   = 
			[
				[
					"Tipo de Requisición:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requisition->typeRequisition->name
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_requisitionType\" value=\"".$request->requisition->typeRequisition->id."\""
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
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_proyectName\" value=\"".$request->requestProject->idproyect."\""
						]
					]
					
				],
			];
			if($request->requisition->code_wbs != "")
			{
				$modelTable [] = 
				[
					"Subproyecto/Código WBS:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requisition->wbs()->exists() ? $request->requisition->wbs->code_wbs : 'No hay'
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_wbs\" value=\"".$request->requisition->code_wbs."\""
						]
					],
				];
				$modelTable [] = 
				[
					"Código EDT:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requisition->edt()->exists() ? $request->requisition->edt->fullName() : 'No hay'
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_edt\" value=\"".$request->requisition->code_edt."\""
						]
					],
				];
			}

			$modelTable [] = 
			[
				"Prioridad:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => $request->requisition->urgent == 1 ? 'Alta' : 'Baja'
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_urgent\" value=\"".$request->requisition->urgent."\""
					]
				]
			];

			$modelTable [] = 
			[
				"Folio:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => $request->folio
					],
					[ 
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_folio\" value=\"".$request->folio."\""
					]
				]
			];

			$modelTable [] = 
			[
				"Solicitante:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => $request->requisition()->exists() && $request->requisition->request_requisition != "" ? $request->requisition()->exists() ? $request->requisition->request_requisition : 'Sin solicitante' : $request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin solicitante'
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_solicitant\" value=\"".$request->requestUser->id."\""
					],
				]
			];

			$modelTable [] = 
			[
				"Título:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => htmlentities($request->requisition->title)
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_title\" value=\"".htmlentities($request->requisition->title)."\""
					]
				]
			];
			$modelTable [] = 
			[
				"Número:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => $request->requisition->number
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_number\" value=\"".$request->requisition->number."\""
					]
				]
			];
			if($request->requisition->generated_number != '')
			{
				$modelTable [] = 
				[
					"Número de requisición:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requisition->generated_number
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_generated_number\" value=\"".$request->requisition->generated_number."\""
						]
					]
				];
			}
			if($request->requisition->requisition_type == 5)
			{
				$modelTable [] = 
				[
					"Compra/Renta:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => $request->requisition->buy_rent
						],
						[
							"kind" => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" name=\"t_buy_rent\" value=\"".$request->requisition->buy_rent."\""
						]
					]
				];
				if($request->requisition->buy_rent == "Renta")
				{
					$modelTable [] = 
					[
						"Vigencia:", 
						[
							[
								"kind" => "components.labels.label",
								"label" => $request->requisition->validity
							],
							[
								"kind" => "components.inputs.input-text",
								"attributeEx" => "type=\"hidden\" name=\"t_validity\" value=\"".$request->requisition->validity."\""
							]
						]
					];
				}
			}
			if($request->requisition->subcontract_number != '')
			{
				$modelTable [] =
				[
					"Número de subcontrato:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => Carbon\Carbon::createFromFormat('Y-m-d', $request->requisition->date_obra)->format('d-m-Y')
						]
					]
				];
			}
			if($request->requisition->date_obra != '')
			{
				$modelTable [] =
				[
					"Fecha en que debe estar en obra:", 
					[
						[
							"kind" => "components.labels.label",
							"label" => Carbon\Carbon::createFromFormat('Y-m-d', $request->requisition->date_obra)->format('d-m-Y')
						]
					]
				];
			}
			$modelTable [] = 
			[
				"Fecha en que se solicitó:", 
				[
					[
						"kind" => "components.labels.label",
						"label" => Carbon\Carbon::createFromFormat('Y-m-d', $request->requisition->date_request)->format('d-m-Y')
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_subcontract\" value=\"".$request->requisition->subcontract_number."\""
					],
					[
						"kind" => "components.inputs.input-text",
						"attributeEx" => "type=\"hidden\" name=\"t_validity\" value=\"".$request->requisition->date_request."\""
					]
				]
			];
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
				@component('components.labels.label')
					@component('components.buttons.button',["variant" => "success", "buttonElement" => "a"])
						@slot('attributeEx')
							type 	= "submit "
							href 	= "{{ route('requisition.export',$request->folio) }}"
							title 	= "Exportar a Excel"
						@endslot
						@slot('label')
							<span>Exportar a Excel</span><span class="icon-file-excel"></span>
						@endslot
					@endcomponent
				@endcomponent
			</div>
			@php
				$body_id = "";
				$modelBody = [];
				if(isset($request))
				{
					switch($request->requisition->requisition_type)
					{
						case(1):
							$modelHead = 
							[
								["value" => "Nombre", "show" => true],
								["value" => "Descripción", "show" => true],
								["value" => "Categoría"],
								["value" => "Tipo"],
								["value" => "Cant."],
								["value" => "Medida"],
								["value" => "Unidad"],
								["value" => "Existencia en Almacén"]
							];
							if(in_array($request->status,[3,4,5,17,27]))
							{
								array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
							}
							break;
						case(2):
							$modelHead = 
							[
								["value" => "Nombre", "show" => true],
								["value" => "Descripción", "show" => true],
								["value" => "Categoría"],
								["value" => "Cant."],
								["value" => "Unidad"],
								["value" => "Periodo"]
							];
							if(in_array($request->status,[3,4,5,17]))
							{
								array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
							}
							break;
						case(4):
							$modelHead = 
							[
								["value" => "Nombre", "show" => true],
								["value" => "Descripción", "show" => true],
								["value" => "Cant."],
								["value" => "Unidad"]
							];
							if(in_array($request->status,[3,4,5,17,27]))
							{
								array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
							}
							break;
						case(5):
							$modelHead = 
							[
								["value" => "Nombre", "show" => true],
								["value" => "Descripción", "show" => true],
								["value" => "Categoría"],
								["value" => "Cant."],
								["value" => "Medida"],
								["value" => "Unidad"],
								["value" => "Marca"],
								["value" => "Modelo"],
								["value" => "Tiempo de Utilización"],
								["value" => "Existencia en Almacén"],
							];
							if(in_array($request->status,[3,4,5,17,27]))
							{
								array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
							}
							break;
						case(6):
							$modelHead = 
							[
								["value" => "Nombre", "show" => true],
								["value" => "Descripción", "show" => true],
								["value" =>"Cant."],
								["value" =>"Unidad"]
							];
							if(in_array($request->status,[3,4,5,17,27]))
							{
								array_splice( $modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
							}
							break;
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
							$footer =
							[
								[
									"kind"  => "components.labels.label",
									"label" => "Tipo de Moneda: "
								],
								[
									"kind"        => "components.inputs.select",
									"classEx"     => "custom-select remove typeCurrency",
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
									"label" => "Tiempo de entrega (Opcional): "
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
								array_splice($footer, 7, 0, 
									[
										[
											"kind"  => "components.labels.label",
											"label" => "Partes de Repuesto (Opcional): "
										]
									]
								);
								array_splice($footer, 7, 0, 
									[
										[
											"kind"        => "components.inputs.input-text",
											"attributeEx" => "name=\"spare_".$provider->id."\" placeholder=\"Ingrese las partes de repuesto\" value=\"".$provider->spare."\""
										]
									]
								);
							}
							if(isset($provider) && $provider->type_currency != "")
							{
								$index = 0;
								foreach($footer[1]["options"] as $option)
								{
									if($option["value"] == $provider->type_currency)
									{
										array_push(
											$footer[1]["options"][$index],
											[
												"selected" => "selected"
											] 
										);
									}
									$index ++;
								}
							}
							$modelGroup[]	=	
							[
								"name"			=> $provider->providerData->businessName,
								"id"			=> 'providers',
								"colNumber"		=> 6,
								"footer"		=> $footer
							];
						}
					}
					if($request->requisition->details()->exists())
					{
						foreach($request->requisition->details as $key=>$detail)
						{
							$body = [
								"classEx" => "tr row_concepts_".$detail->id,
								"attributeEx" => "data-item=\"".$detail->id."\""
							];
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
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_name[]\" value=\"".$detail->name."\"",
													"classEx"     => "t_name"
												],
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_description[]\" value=\"".$detail->description."\"",
													"classEx"     => "t_description"
												],
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
													"attributeEx" => "type=\"hidden\" name=\"t_category[]\" value=\"".$detail->category."\"",
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
													"attributeEx" => "type=\"hidden\" name=\"t_type[]\" value=\"".$detail->cat_procurement_material_id."\"",
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
													"attributeEx" => "type=\"hidden\" name=\"t_quantity[]\" value=\"".$detail->quantity."\"",
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
													"label" => $detail->measurement
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_measurement[]\" value=\"".$detail->measurement."\"",
													"classEx"     => "t_measurement"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->unit
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_unit[]\" value=\"".$detail->unit."\"",
													"classEx"     => "t_unit"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->exists_warehouse
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_exists_warehouse[]\" value=\"".$detail->exists_warehouse."\"",
													"classEx"     => "t_exists_warehouse"
												]
											]
										]
									];
									if(in_array($request->status,[3,4,5,17,27]))
									{
										array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["kind" => "components.labels.label", "label" => $detail->part]]]]);
									}
									break;
								case(2):		
									$body = 
									[
										[
											"content" => 
											[
												[
													"label" => $detail->name
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_name[]\" value=\"".$detail->name."\"",
													"classEx"     => "t_name"
												],
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_description[]\" value=\"".$detail->description."\"",
													"classEx"     => "t_description"
												],
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
													"attributeEx" => "type=\"hidden\" name=\"t_category[]\" value=\"".$detail->category."\"",
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
													"attributeEx" => "type=\"hidden\" name=\"t_quantity[]\" value=\"".$detail->quantity."\"",
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
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_unit[]\" value=\"".$detail->unit."\"",
													"classEx"     => "t_unit"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->period
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_period[]\" value=\"".$detail->period."\"",
													"classEx"     => "t_period"
												]
											]
										]
									];
									if(in_array($request->status,[3,4,5,17,27]))
									{
										array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["kind" => "components.labels.label", "label" => $detail->part]]]]);
									}
									break;
								case(4):
									$body = 
									[
										
										[
											"content" => 
											[
												[
													"label" => $detail->name
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_name[]\" value=\"".$detail->name."\"",
													"classEx"     => "t_name"
												],
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_description[]\" value=\"".$detail->description."\"",
													"classEx"     => "t_description"
												],
											]
										],
										[
											"content" => 
											[
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_quantity[]\" value=\"".$detail->quantity."\"",
													"classEx"     => "t_quantity"
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
													"classEx"     => "t_id"
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
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_unit[]\" value=\"".$detail->unit."\"",
													"classEx"     => "t_unit"
												],
												[
													"label" => $detail->unit
												]
											]
										]
									];
									if(in_array($request->status,[3,4,5,17,27]))
									{
										array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["kind" => "components.labels.label", "label" => $detail->part]]]]);
									}
									break;
								case(5):
									$bodyConcepts = 
									[
										[
											"content" => 
											[
												[
													"label" => $detail->name
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_name[]\" value=\"".$detail->name."\"",
													"classEx"     => "t_name"
												],
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_description[]\" value=\"".$detail->description."\"",
													"classEx"     => "t_description"
												],
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
													"attributeEx" => "type=\"hidden\" name=\"t_category[]\" value=\"".$detail->category."\"",
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
													"attributeEx" => "type=\"hidden\" name=\"t_quantity[]\" value=\"".$detail->quantity."\"",
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
													"label" => $detail->measurement
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_measurement[]\" value=\"".$detail->measurement."\"",
													"classEx"     => "t_measurement"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->unit
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_unit[]\" value=\"".$detail->unit."\"",
													"classEx"     => "t_unit"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->brand
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_brand[]\" value=\"".$detail->brand."\"",
													"classEx"     => "t_brand"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->model
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_model[]\" value=\"".$detail->model."\"",
													"classEx"     => "t_model"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->usage_time
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_usage_time[]\" value=\"".$detail->usage_time."\"",
													"classEx"     => "t_usage_time"
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->exists_warehouse
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_exists_warehouse[]\" value=\"".$detail->exists_warehouse."\"",
													"classEx"     => "t_exists_warehouse"
												]
											]
										]
									];
									if(in_array($request->status,[3,4,5,17,27]))
									{
										array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["kind" => "components.labels.label", "label" => $detail->part]]]]);
									}
									break;
								case(6):
									$body = 
									[
										[
											"content" => 
											[
												[
													"label" => $detail->name
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_name[]\" value=\"".$detail->name."\"",
													"classEx"     => "t_name"
												],
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_description[]\" value=\"".$detail->description."\"",
													"classEx"     => "t_description"
												],
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
													"attributeEx" => "type=\"hidden\" name=\"t_quantity[]\" value=\"".$detail->quantity."\"",
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
												],
												[
													"kind"        => "components.inputs.input-text",
													"attributeEx" => "type=\"hidden\" name=\"t_unit[]\" value=\"".$detail->unit."\"",
													"classEx"     => "t_unit"
												]
											]
										]
									];
									if(in_array($request->status,[3,4,5,17,27]))
									{
										array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["kind" => "components.labels.label", "label" => $detail->part]]]]);
									}
									break;
							}			
							$body[]	=
							[
								"content" => 
								[
									[
										"kind"        => "components.buttons.button",
										"attributeEx" => "type=\"button\" data-item=\"".$detail->id."\" title=\"Eliminar\"",
										"variant"	  => "red",
										"label"		  => "<span class=\"icon-x\"></span>",
										"classEx"     => "delete-art"
									],
									[
										"kind"        => "components.buttons.button",
										"attributeEx" => "type=\"button\" data-item=\"".$detail->id."\" title=\"Dividir\"",
										"variant"	  => "success",
										"label"		  => "<span class=\"icon-divide\"></span>",
										"classEx"     => "add-art"
									],
								]
							];
							$modelBody[] = $body;
						}
					}
				}
			@endphp
			@component('components.tables.table-provider',[
				"modelHead"			=> $modelHead,
				"modelBody"			=> $modelBody,
				"modelGroup"		=> $modelGroup,
				"attributeExBody"	=> "id=\"body_art\"",
				"classExBody"	 	=> "request-validate"
			])
			@endcomponent
				@php
					$modelBody = [];
					if(isset($request))
					{
						switch($request->requisition->requisition_type)
						{
							case(1):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Categoría"],
									["value" => "Tipo"],
									["value" => "Cant."],
									["value" => "Medida"],
									["value" => "Unidad"],
									["value" => "Existencia en Almacén"]
								];
								break;
							case(2):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Categoría"],
									["value" => "Cant."],
									["value" => "Unidad"],
									["value" => "Periodo"]
								];
								break;
							case(4):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Cant."],
									["value" => "Unidad"]
								];
								break;
							case(5):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Categoría"],
									["value" => "Cant."],
									["value" => "Medida"],
									["value" => "Unidad"],
									["value" => "Marca"],
									["value" => "Modelo"],
									["value" => "Tiempo de Utilización"],
									["value" => "Existencia en Almacén"]
								];
								break;
							case(6):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],								
									["value" =>"Cant."],
									["value" =>"Unidad"]
								];
								break;
						}
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
				@endphp
				@component('components.tables.table-provider',[
					"modelHead"			=> $modelHead,
					"modelBody"			=> $modelBody,
					"modelGroup"		=> $modelGroup,
					"attributeEx"     	=> " id=\"table-selected-items\"",
					"classExContainer"	=> "hidden",
					"attributeExBody" 	=> "id=\"others_req\"",
					"attributeExFoot" 	=> "id=\"foot_req\""
				])
				@endcomponent
		@else
			@component('components.labels.title-divisor') 
				DATOS DE LA VACANTE
				@slot('classEx')
					pb-4
				@endslot
			@endcomponent
			@if($request->requisition->staff()->exists())
				<div class="employee-details">
					<div class="flex justify-center">
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
					<div class="w-full">
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
					<div class="w-full">
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
					<div class="w-full">
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
										"classEx" => "tr",
										[
											"content" => 
											[
												[
													"label" => htmlentities($emp->fullName()),
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" =>  htmlentities($emp->position),
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
													"attributeEx" => "data-toggle=\"modal\"data-target=\"#detailEmployee\" type=\"button\"",
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
			@php
				$body 			= [];
				$modelBody		= [];
				$modelHead = ['Tipo de documento', 'Archivo', 'Modificado Por', 'Fecha'];
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
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"variant"       => "secondary",
									"attributeEx"   => "target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\"",
									"label"         => "Archivo"
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
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->created)->format('d-m-Y'),
							]
						]
					];
					array_push($modelBody, $body);
				}
			@endphp
			@component('components.tables.alwaysVisibleTable',[
				"variant" => "hidden",  
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
				@slot('classEx')
					text-center
				@endslot
				@slot('attributeEx')
					id="table"
				@endslot
				@slot('attributeExBody')
					id="body"
				@endslot
			@endcomponent
		@endif
		@component('components.containers.container-form')
			<div id="documents-requisition" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6">
			</div>
			<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx') type="button" name="addDocRequisition" id="addDocRequisition"@endslot
					<span class="icon-plus"></span>
					<span>Nuevo documento</span>
				@endcomponent
			</div>
		@endcomponent
		<span id="spanDelete"></span>
		<div id="comment">
			@component('components.labels.label') 
				Comentarios (Opcional):
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
				{{ $request->checkComment }}
			@endcomponent				
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
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
					type="submit" name="btnSave" id="save" formaction="{{ route('requisition.save-review',$request->folio) }}"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto save
				@endslot
				GUARDAR CAMBIOS
			@endcomponent
			@component("components.buttons.button",["variant" => "red"])
				@slot('attributeEx') 
					type="submit" id="reject" name="btnReject" value="" formaction="{{ route('requisition.reject-review',$request->folio) }}"
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
			@slot('modalHeader')
				@component('components.buttons.button')
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					@slot('classEx')
						close
					@endslot
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot('classExBody')
				modal-view-document
			@endslot
			@slot('modalFooter')
				<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button',[
						"variant" => "red"
						])
						@slot('classeEx')
							closeViewDocument
						@endslot
						@slot('attributeEx')
							type="button"
							data-dismiss="modal"
						@endslot
						<span class="icon-x"></span> Cerrar
					@endcomponent
				</div>
			@endslot
		@endcomponent
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				addDocumentProvider
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
					<span aria-hidden="true">&times;</span>
				@endcomponent
			@endslot
			@slot('modalBody')
				<input type="hidden" name="idRequisitionHasProviderDoc">
				@php
					$options = collect(
						[
							["value"=>"Cotización", "description"=>"Cotización"], 
							["value"=>"Ficha Técnica", "description"=>"Ficha Técnica"], 
							["value"=>"Control de Calidad", "description"=>"Control de Calidad"], 
							["value"=>"Contrato", "description"=>"Contrato"], 
							["value"=>"Factura", "description"=>"Factura"], 
							["value"=>"REQ. OC. FAC.", "description"=>"REQ. OC. FAC."], 
							["value"=>"Otro", "description"=>"Otro"]
						]
					);
					$labelSelect = view('components.labels.label',[
						"label" => "Selecciona el tipo de archivo",
						"classEx" => "font-bold"
					])->render();
					$select = view('components.inputs.select',[
						"options" => $options,
						"classEx" => "custom-select nameDocument", 
						"attributeEx" => "name=\"nameDocument[]\" data-validation=\"required\"",
					])->render();
					$select = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $select));
					$labelSelect = html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $labelSelect));
				@endphp
				<div id="documents">
					@component('components.documents.upload-files',[
						"attributeExRealPath" => "name=\"realPath[]\"",
						"classExRealPath"     => "path",				
						"attributeExInput"    => "name=\"path\" accept=\".pdf,.jpg,.png\"",
						"classExInput"        => "pathActioner",
						"componentsExUp"      => $labelSelect.$select,
						"classExDelete"       => "delete-doc"
					])
					@endcomponent
				</div>
				@component('components.buttons.button', ["variant" => "warning"])
					@slot('attributeEx')
						name = "addDocProvider"
						id   = "addDocProvider"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar Documentos</span>
				@endcomponent
			@endslot
			@slot('modalFooter')
				<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
					@component('components.buttons.button',[
						"variant" => "success"
						])
						@slot('classeEx')
							closeViewDocument
						@endslot
						@slot('attributeEx')
							type="submit"
							name="btnAddProviderDocuments"
							data-dismiss="modal"
							formaction="{{ route('requisition.provider-documents.store',$request->folio) }}"
						@endslot
						<span class="icon-check"></span> Agregar Documentos
					@endcomponent
					@component('components.buttons.button',[
						"variant" => "red"
						])
						@slot('classeEx')
							closeViewDocument
						@endslot
						@slot('attributeEx')
							type="button"
							data-dismiss="modal"
						@endslot
						<span class="icon-x"></span> Cerrar
					@endcomponent
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
		@slot('modalHeader')
			@component('components.buttons.button')
				@slot('attributeEx')
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
		@endslot
		@slot('classExBody')
			modal-employee
		@endslot
		@slot('modalFooter')
			<div class="mt-4 w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
				@component('components.buttons.button')
					@slot('classeEx')
						closeViewDocument
					@endslot
					@slot('attributeEx')
						type="button"
						data-dismiss="modal"
					@endslot
					<span class="icon-x"></span> Cerrar
				@endcomponent
			</div>
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

				$('input[name="realPathRequisition[]').each(function(i,v)
				{
					nameDocument = $(this).parents('.docs-p').find('[name="nameDocumentRequisition[]"] option:selected').val();
					if( $(this).val() == "" || nameDocument == "0" )
					{
			 			flag = true;
					}
				});

				if(flag)
				{
					swal('', 'Tiene archivos sin agregar, por favor verifique sus datos.', 'error');
					return false;
				}

				if($('.request-validate').length>0)
				{
					conceptos	= $('#body_art .tr').length;
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
		});
	}
	
	$(document).ready(function()
	{
		validation();
		zipCode();
		$(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });
		$('.t_unitPrice,.t_subtotal,.t_total,.clabe,.account').numeric({ altDecimal: ".", decimalPlaces: 2,negative:false });
		$('.phone').numeric({negative:false});
		@php
			$selects = collect([
				[
					"identificator"         => '[name="state_idstate"],[name="code[]"]', 
					"placeholder"           => "Seleccione uno",
					"maximumSelectionLength"=> "1" 
				],
				[
					"identificator"         => '.typeCurrency', 
					"placeholder"           => "Seleccione el tipo de moneda",
					"maximumSelectionLength"=> "1" 
				],
			]);
		@endphp
		@component("components.scripts.selects",["selects"=>$selects]) @endcomponent

		// $('[name="code[]"]').select2(
		// {
		// 	language				: "es",
		// 	maximumSelectionLength	: 1,
		// 	placeholder 			: "Código",
		// 	tags 					: true,
		// 	width 					: '100%'
		// })
		// .on("change",function(e)
		// {
		// 	if($(this).val().length>1)
		// 	{
		// 		$(this).val($(this).val().slice(0,1)).trigger('change');
		// 	}
		// });

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

		$(document).on('click','#upload_file,#export,[name="btnAddProviderDocuments"],[name="btnReject"]',function()
		{
			$('.remove').removeAttr('data-validation');
			$('.removeselect').removeAttr('required');
			$('.removeselect').removeAttr('data-validation');
			$('.request-validate').removeClass('request-validate');
			$('.remove-validation-concept').removeAttr('data-validation');
		})
		// .on('click','[name="btnAddProviderDocuments"]',function(e)
		// {
		// 	e.preventDefault()
			
		// 	action = $(this).attr('formaction');
		// 	form = $('form').attr('action',action);
		// 	needFileName = false;

		// 	$('[name="realPath[]"]').each(function()
		// 	{
		// 		if($(this).val() != "" )
		// 		{
		// 			select = $(this).parents('div').find('.nameDocument');
		// 			name = select.find('option:selected').val()

		// 			if(name == 0)
		// 			{
		// 				needFileName = true;
		// 				select.addClass('error')
		// 			}
		// 		}
		// 	});

		// 	if(!needFileName)
		// 	{
		// 		form.submit();
		// 	}
		// 	else
		// 	{
		// 		swal('', 'Debe seleccionar el tipo de documento', 'error');
		// 	}

		// })
		// .on('click','.drop_art',function()
		// {
		// 	comment_value = $('.commentDropArt').val();
		// 	if(comment_value === "")
		// 	{
		// 		swal('','Ingrese comentario para continuar.','error');
		// 	}
		// })
		.on('click','.delete-art',function()
		{
				if(($("#body_art .tr").length-1) == 0)
				{
					swal('','La requisición debe contar con al menos un concepto','warning');
				}
				else
				{
					// comment_value = $('.commentDropArt').val();
			
					// if(comment_value !== "")
					// {
						id = $(this).parents('.tr').find('.t_id').val();
		
						if (id != "x") 
						{
							deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
							$('#spanDelete').append(deleteID);
						}
		
						$(this).parents('.tr').remove();
						swal('','Concepto eliminado','success');
					// }
				}
			
		})
		.on('click','.add-art',function()
		{
			
			idMain	= $(this).attr('data-item');

			if(($("#body_art .tr").length-1)==0)
			{
				swal('','La requisición debe contar con al menos un concepto','warning');
			}
			else
			{
				others_req =  $('.row_concepts_'+idMain).appendTo('#body_art');

				@php
					$btn = view('components.buttons.button',[
						"attributeEx" => "type=\"button\" title=\"Eliminar\"",
						"variant"	  => "red",
						"label"		  => "<span class=\"icon-x\"></span>",
						"classEx"     => "return-art"
					])->render();
				@endphp
				btn = '{!!preg_replace("/(\r)*(\n)*/", "", $btn)!!}';
				btnElement = $(btn);
				btnElement.attr('data-item', idMain);
				trElement = $('<div class="col-span-1 w-48 text-center py-2 btnDiv"></div>');
				trElement.append(btnElement);
				others_req = others_req.append(trElement);

				$(this).parents('.tr').find('.contentEx').remove();
				$('#table-selected-items').parents('.container-root').removeClass('hidden');
				$('#table-selected-items').show();
				$(this).closest('.tr').remove();
				$('#others_req').append(others_req);
				name = $('.row_concepts_'+idMain).find('.t_id').attr('name');
				$('.row_concepts_'+idMain).find('.t_id').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_category').attr('name');
				$('.row_concepts_'+idMain).find('.t_category').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_type').attr('name');
				$('.row_concepts_'+idMain).find('.t_type').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_quantity').attr('name');
				$('.row_concepts_'+idMain).find('.t_quantity').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_measurement').attr('name');
				$('.row_concepts_'+idMain).find('.t_measurement').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_unit').attr('name');
				$('.row_concepts_'+idMain).find('.t_unit').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_name').attr('name');
				$('.row_concepts_'+idMain).find('.t_name').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_description').attr('name');
				$('.row_concepts_'+idMain).find('.t_description').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_exists_warehouse').attr('name');
				$('.row_concepts_'+idMain).find('.t_exists_warehouse').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_period').attr('name');
				$('.row_concepts_'+idMain).find('.t_period').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_brand').attr('name');
				$('.row_concepts_'+idMain).find('.t_brand').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_model').attr('name');
				$('.row_concepts_'+idMain).find('.t_model').removeAttr('name').attr('name', "selected_"+name);
				name = $('.row_concepts_'+idMain).find('.t_usage_time').attr('name');
				$('.row_concepts_'+idMain).find('.t_usage_time').removeAttr('name').attr('name', "selected_"+name);
			}
		})
		.on('click','.return-art',function()
		{
			idMain		= $(this).attr('data-item');
			others_req	= $('.row_concepts_'+idMain).appendTo('#body_art');
			@php
				$btnDel = view('components.buttons.button',[
					"attributeEx" => "type=\"button\" title=\"Eliminar\"",
					"variant"	  => "red",
					"label"		  => "<span class=\"icon-x\"></span>",
					"classEx"     => "delete-art"
				])->render();
				$btnAdd = view('components.buttons.button',[
					"attributeEx" => "type=\"button\" title=\"Dividir\"",
					"variant"	  => "success",
					"label"		  => "<span class=\"icon-divide\"></span>",
					"classEx"     => "add-art"
				])->render();
			@endphp
			btn = '{!!preg_replace("/(\r)*(\n)*/", "", $btnDel)!!}'+'{!!preg_replace("/(\r)*(\n)*/", "", $btnAdd)!!}';
			btnElement = $(btn);
			btnElement.find('.delete-art').attr('data-item', idMain);
			btnElement.find('.add-art').attr('data-item', idMain);
			trElement = $('<div class="col-span-1 w-48 text-center py-2"></div>');
			trElement.append(btnElement);
			others_req = others_req.append(trElement);
			/*others_req = others_req.append($("<td class='buttons'></td>")
				.append('<button class="btn btn-red delete-art" data-item="'+idMain+'" type="button"><span class="icon-x"></span></button>')
				.append('<button class="btn btn-green add-art" data-item="'+idMain+'" title="Dividir" type="button" style="padding: 7px 9px 6px 6px;"><svg viewBox="0 0 1000 700" xmlns="http://www.w3.org/2000/svg"  xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><path d="M448 256L192 0 0 192l310.72 300.41c6.42-20.87 13.42-39.62 19.99-55.5 19.94-48.141 55.68-117.5 112.7-174.52L448 256zM576 0l133.49 133.49L512 330.98c-45.74 45.739-75.1 103.039-91.67 143.05C403.76 514.04 384 575.32 384 640v384h256V640c0-36.44 27.25-102.23 53.02-128l197.49-197.49L1024 448V0H576z" style="fill-rule:nonzero;"/></svg></button>')
			);*/
			$(this).parents('.tr').find('.btnDiv').remove();
			$('#table-selected-items').show();
			$(this).closest('.tr').remove();
			$('#body_art').append(others_req);
			$('.row_concepts_'+idMain).find('.t_id').removeAttr('name').attr('name', 'idRequisitionDetail[]');
			$('.row_concepts_'+idMain).find('.t_category').removeAttr('name').attr('name', "t_category[]");
			$('.row_concepts_'+idMain).find('.t_type').removeAttr('name').attr('name', "t_type[]");
			$('.row_concepts_'+idMain).find('.t_quantity').removeAttr('name').attr('name', "t_quantity[]");
			$('.row_concepts_'+idMain).find('.t_measurement').removeAttr('name').attr('name', "t_measurement[]");
			$('.row_concepts_'+idMain).find('.t_unit').removeAttr('name').attr('name', "t_unit[]");
			$('.row_concepts_'+idMain).find('.t_name').removeAttr('name').attr('name', "t_name[]");
			$('.row_concepts_'+idMain).find('.t_description').removeAttr('name').attr('name', "t_description[]");
			$('.row_concepts_'+idMain).find('.t_exists_warehouse').removeAttr('name').attr('name', "t_exists_warehouse[]");
			$('.row_concepts_'+idMain).find('.t_period').removeAttr('name').attr('name', "t_period[]");
			$('.row_concepts_'+idMain).find('.t_brand').removeAttr('name').attr('name', "t_brand[]");
			$('.row_concepts_'+idMain).find('.t_model').removeAttr('name').attr('name', "t_model[]");
			$('.row_concepts_'+idMain).find('.t_usage_time').removeAttr('name').attr('name', "t_usage_time[]");
			@if($request->requisition->requisitionHasProvider()->exists())
				@foreach($request->requisition->requisitionHasProvider as $provider) 
					$('.row_concepts_'+idMain).children('td').find('[name="selected_idProviderSecondaryPrice_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "idProviderSecondaryPrice_"+idMain+"_{{ $provider->id  }}");
					$('.row_concepts_'+idMain).children('td').find('[name="selected_unitPrice_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "unitPrice_"+idMain+"_{{ $provider->id  }}");
					$('.row_concepts_'+idMain).children('td').find('[name="selected_subtotal_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "subtotal_"+idMain+"_{{ $provider->id  }}");
					$('.row_concepts_'+idMain).children('td').find('[name="selected_typeTax_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "typeTax_"+idMain+"_{{ $provider->id  }}");
					$('.row_concepts_'+idMain).children('td').find('[name="selected_iva_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "iva_"+idMain+"_{{ $provider->id  }}");
					$('.row_concepts_'+idMain).children('td').find('[name="selected_total_'+idMain+'_{{ $provider->id  }}"]').removeAttr('name').attr('name', "total_"+idMain+"_{{ $provider->id  }}");
				@endforeach
			@endif
			if(($("#foot_req .tr").length)==0)
			{
				@if($request->requisition->requisitionHasProvider()->exists())
					@if(isset($request))
						@switch($request->requisition->requisition_type)
							@case(1)
								@php
									$colspan = 9;
								@endphp
							@break

							@case(2)
								@php
									$colspan = 7;
								@endphp
							@break

							@case(4)
								@php
									$colspan = 5;
								@endphp
							@break

							@case(5)
								@php
									$colspan = 11;
								@endphp
							@break

							@case(6)
								@php
									$colspan = 5;
								@endphp
							@break
						@endswitch
					@endif
					foot_tr = $('<th colspan="{{$colspan}}"></th>');
					@foreach($request->requisition->requisitionHasProvider as $provider)
						$("#main_foot").append(foot_tr);
						foot_tr = $('.th_{{$provider->id}}').appendTo(foot_tr);
						$('.th_{{$provider->id}}').children('p').find('[name="selected_type_currency_provider_{{ $provider->id }}"]').removeAttr('name').attr('name', "type_currency_provider_{{ $provider->id }}");
						$('.th_{{$provider->id}}').children('p').find('[name="selected_delivery_time_{{ $provider->id }}"]').removeAttr('name').attr('name', "delivery_time_{{ $provider->id }}");
						$('.th_{{$provider->id}}').children('p').find('[name="selected_credit_time_{{ $provider->id }}"]').removeAttr('name').attr('name', "credit_time_{{ $provider->id }}");
						$('.th_{{$provider->id}}').children('p').find('[name="selected_guarantee_{{ $provider->id }}"]').removeAttr('name').attr('name', "guarantee_{{ $provider->id }}");
						$('.th_{{$provider->id}}').children('p').find('[name="selected_spare_{{ $provider->id }}"]').removeAttr('name').attr('name', "spare_{{ $provider->id }}");
						$('.th_{{$provider->id}}').children('p').find('[name="selected_commentaries_provider_{{ $provider->id }}"]').removeAttr('name').attr('name', "commentaries_provider_{{ $provider->id }}");
					@endforeach
					$('#main_foot').append(foot_tr);
				@endif
			}
			if(($('#others_req .tr').length) == 0)
			{
				$('#table-selected-items').hide();
				$('#table-selected-items').parents('.container-root').addClass('hidden');
				$('#foot_req').empty();
			}
		})
		.on('change','#files',function(e)
		{
			label		= $(this).next('label');
			fileName	= e.target.value.split( '\\' ).pop();
			if(fileName)
			{
				label.find('span').html(fileName);
			}
			else
			{
				label.html(labelVal);
			}
		})
		.on('click','[data-toggle="modal"]',function()
		{
			generalSelect({'selector': '.js-bank', 'model': 28});
			//id			= $(this).parents('tr').find('.t_id').val();
			//quantity	= $(this).parents('tr').find('.t_quantity').val();
			//unit		= $(this).parents('tr').find('.t_unit').val();
			//description = $(this).parents('tr').find('.t_description').val();
		})
		.on('click','#addProvider',function(e)
		{
			idRequisition 	= {{ $request->requisition->id }};
			businessName	= $('[name="businessName"]').val();
			address			= $('[name="address"]').val();
			number			= $('[name="number"]').val();
			colony			= $('[name="colony"]').val();
			postalCode		= $('[name="postalCode"]').val();
			city			= $('[name="city"]').val();
			state_idstate	= $('[name="state_idstate"] option:selected').val();
			rfc				= $('[name="rfc"]').val();
			phone			= $('[name="phone"]').val();
			contact			= $('[name="contact"]').val();
			beneficiary		= $('[name="beneficiary"]').val();
			commentaries	= $('[name="commentaries"]').val();

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

			form = $(this).attr('formaction');

			needFileName = false
			$('input[name="realPathNewProvider[]"]').each(function(){
				if($(this).val() != "" )
				{
					
					select = $(this).parents('div').find('.nameDocumentNewProvider')
					name = select.find('option:selected').val()

					if(name == 0)
					{
						needFileName = true;
						select.addClass('error')
					}
				}
			});

			if (rfc == "" || businessName == "" || address == "" || number == "" || colony == "" || postalCode == "" ||city == "" || state_idstate == "" || state_idstate == undefined || rfc == "" || phone == "" || contact == "" || beneficiary == "" || needFileName) 
			{
				e.preventDefault();
				$('[name="businessName"],[name="address"],[name="number"],[name="colony"],[name="postalCode"],[name="city"],[name="state_idstate"],[name="rfc"],[name="phone"],[name="contact"],[name="beneficiary"]').removeClass('error');

				if (businessName == "")
					$('[name="businessName"]').addClass('error');

				if (address == "")
					$('[name="address"]').addClass('error');

				if (number == "")
					$('[name="number"]').addClass('error');

				if (colony == "")
					$('[name="colony"]').addClass('error');

				if (postalCode == "")
					$('[name="postalCode"]').addClass('error');

				if (city == "")
					$('[name="city"]').addClass('error');

				if (state_idstate == "" || state_idstate == undefined)
					$('[name="state_idstate"]').addClass('error');

				if (rfc == "")
					$('[name="rfc"]').addClass('error');

				if (phone == "")
					$('[name="phone"]').addClass('error');

				if (contact == "")
					$('[name="contact"]').addClass('error');

				if (beneficiary == "")
					$('[name="beneficiary"]').addClass('error');

				swal('Error','Falta llenar campos obligatorios.','error');
			}
			else
			{
				$('.remove-validation-concept').removeAttr('data-validation');
				form.submit();
			}


		})
		.on('click','#addAccount',function()
		{
			bank			= $(this).parents('tbody').find('.js-bank').val();
			bankName		= $(this).parents('tbody').find('.js-bank :selected').text();
			account			= $(this).parents('tbody').find('.account').val();
			branch_office	= $(this).parents('tbody').find('.branch_office').val();
			reference		= $(this).parents('tbody').find('.reference').val();
			clabe			= $(this).parents('tbody').find('.clabe').val();
			currency		= $(this).parents('tbody').find('.currency').val();
			agreement		= $(this).parents('tbody').find('.agreement').val();
			alias 			= $(this).parents('tbody').find('.alias').val();
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
				else if($(this).parents('tr').find('.clabe').hasClass('error') || $(this).parents('tr').find('.account').hasClass('error'))
				{
					swal('', 'Por favor ingrese datos correctos', 'error');
				}
				else
				{
					bank = $('<tr></tr>')
							.append($('<td></td>')
								.append(bankName)
								.append($('<input type="hidden" class="providerBank" name="providerBank[]" value="x">'))
								.append($('<input type="hidden" name="idBanks[]" value="'+bank+'">'))
								)
							.append($('<td></td>')
								.append(alias)
								.append($('<input type="hidden" name="alias[]" value="'+alias+'">'))
								)
							.append($('<td></td>')
								.append(account)
								.append($('<input type="hidden" name="account[]" value="'+account+'">'))
								)
							.append($('<td></td>')
								.append(branch_office)
								.append($('<input type="hidden" name="branch[]" value="'+branch_office+'">'))
								)
							.append($('<td></td>')
								.append(reference)
								.append($('<input type="hidden" name="reference[]" value="'+reference+'">'))
								)
							.append($('<td></td>')
								.append(clabe)
								.append($('<input type="hidden" name="clabe[]" value="'+clabe+'">'))
								)
							.append($('<td></td>')
								.append(currency)
								.append($('<input type="hidden" name="currency[]" value="'+currency+'">'))
								)
							.append($('<td></td>')
								.append(agreement)
								.append($('<input type="hidden" name="agreement[]" value="'+agreement+'">'))
								)
							.append($('<td></td>')
								.append($('<button class="btn btn-red delete-account" type="button"><span class="icon-x"></span></button>'))
								);
					$('#banks-body').append(bank);
					$('.clabe, .account').removeClass('valid').val('');
					$('.branch_office,.reference,.currency,.agreement,.alias').val('');
					$(this).parents('tbody').find('.error').removeClass('error');
					$('.js-bank').val(0).trigger("change");
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
			$(this).parents('tr').remove();
		})
		.on('change','.t_unitPrice,.t_subtotal,.t_typeTax',function()
		{
			idProvider	= $(this).attr('data-provider');
			idDetail	= $(this).attr('data-item');
			quantity	= $(this).parents('.tr').find('.t_quantity').val();
			unitPrice	= $(this).parents('.tr').find('.t_unitPrice[data-provider="'+idProvider+'"]').val();
			subtotal 	= $(this).parents('.tr').find('.t_subtotal[data-provider="'+idProvider+'"]').val();
			typeTax 	= $(this).parents('.tr').find('.t_typeTax[data-provider="'+idProvider+'"] option:selected').val();

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
			total    = ((quantity * unitPrice)+ivaCalc);

			$(this).parents('.tr').find('.t_subtotal[data-provider="'+idProvider+'"]').val(subtotal.toFixed(2));
			$(this).parents('.tr').find('.t_iva[data-provider="'+idProvider+'"]').val(ivaCalc.toFixed(2));
			$(this).parents('.tr').find('.t_total[data-provider="'+idProvider+'"]').val(total.toFixed(2));
		})
		.on('click','[name="btnSave"]',function(e)
		{
			e.preventDefault();
			swal("Cargando",{
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false
			});
			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];
			object = $(this);
			if ($('[name="datepath[]"]').length > 0) 
			{
				$('[name="datepath[]"]').each(function(i,v)
				{	
					datepath.push($(this).val());
					fiscal_folio.push($(this).siblings('[name="fiscal_folio[]"]').val());
					ticket_number.push($(this).siblings('[name="ticket_number[]"]').val());
					timepath.push($(this).siblings('[name="timepath[]"]').val());
					amount.push($(this).siblings('[name="amount[]"]').val());
					
					$(this).siblings('[name="fiscal_folio[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="ticket_number[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="timepath[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="amount[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
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
						$('[name="datepath[]"]').each(function(j,v)
						{							
							ticket_number	= $(this).siblings('[name="ticket_number[]"]');
							fiscal_folio	= $(this).siblings('[name="fiscal_folio[]"]');
							timepath		= $(this).siblings('[name="timepath[]"]');
							amount			= $(this).siblings('[name="amount[]"]');
							datepath		= $(this);
							$(data).each(function(i,d)
							{
								if (d == fiscal_folio.val() || d == ticket_number.val()) 
								{
									ticket_number.addClass('error');
									fiscal_folio.addClass('error');
									timepath.addClass('error');
									amount.addClass('error');
									datepath.addClass('error');
									flag = true;
								}
							});
						});
						if (flag) 
						{
							swal('','Los documentos marcados ya se encuentran registrados, por favor verifique los datos.','error');
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
					nameDocument = $(this).parents('.docs-p').find('[name="nameDocumentRequisition[]"] option:selected').val();
					if( $(this).val() == "" || nameDocument == undefined )
					{
			 			flag = true;
					}
				});

				if(flag)
				{
					swal('', 'Tiene un archivo sin agregar, por favor verifique sus datos.', 'error');
				}
				else
				{
					$('.remove-validation-concept').removeAttr('data-validation');
					action	= object.attr('formaction');
					form   = $('#container-alta').attr('action',action);
					form.submit();
				}
			}
		})
		.on('click','[name="btnAddProvider"],[name="btnDeleteProvider"],[name="idProviderBtn"],[name="addMultiProvider"],[name="btnAddProviderDocuments"]',function()
		{
			$('.remove-validation-concept').removeAttr('data-validation');
		})
		.on('change','input[name="prov"]',function()
		{
			if ($('input[name="prov"]:checked').val() == "nuevo") 
			{
				$(".form-add-provider").fadeIn();
				$(".form-search-provider").fadeOut();
				$('.phone').numeric({negative:false});
				generalSelect({'selector': '.js-bank', 'model': 28});
				$('[name="state_idstate"]').select2(
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
				});
				zipCode();
			}
			else if ($('input[name="prov"]:checked').val() == "buscar") 
			{
				$(".form-search-provider").fadeIn();
				$(".form-add-provider").fadeOut();
			}
		})
		.on('click','.add-provider',function()
		{
			$('.cp,.phone').numeric({negative:false});
		})
		.on('click','#search_provider', function()
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
				type	: 'get',
				url		: '{{ url("administration/requisition/search-provider") }}',
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
		.on('click','.editResultProvider',function()
		{
			folio			= {{ $request->folio }};
			requisition_id	= {{ $request->requisition->id }};
			provider_id		= $(this).parents('tr').find('.t_provider').val();
			$.ajax(
			{
				type 	: 'get',
				url 	: '{{ url("/administration/requisition/edit-provider") }}',
				data 	: {'folio':folio,'requisition_id':requisition_id,'provider_id':provider_id},
				success : function(data)
				{
					$('#result_provider').fadeOut();
					$('#form_edit_provider').html(data);
					generalSelect({'selector': '.js-bank', 'model': 28});
					$('[name="state_idstate_edit"]').select2(
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
					});
					validation();
				},
				error : function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
					$('#result_provider').fadeOut();
					$('#form_edit_provider').html('');
				}
			})
		})
		.on('click','#updateProvider',function()
		{
			idRequisition 	= {{ $request->requisition->id }};
			businessName	= $('[name="businessName_edit"]').val();
			address			= $('[name="address_edit"]').val();
			number			= $('[name="number_edit"]').val();
			colony			= $('[name="colony_edit"]').val();
			postalCode		= $('[name="postalCode_edit"]').val();
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

			if (rfc == "" || businessName == "" || address == "" || number == "" || colony == "" || postalCode == "" ||city == "" || state_idstate == "" || state_idstate == undefined || rfc == "" || phone == "" || contact == "" || beneficiary == "") 
			{
				$('[name="businessName_edit"],[name="address_edit"],[name="number_edit"],[name="colony_edit"],[name="postalCode_edit"],[name="city_edit"],[name="state_idstate_edit"],.rfc_edit,[name="phone_edit"],[name="contact_edit"],[name="beneficiary_edit"]').removeClass('error');

				if (businessName == "")
					$('[name="businessName"]').addClass('error');

				if (address == "")
					$('[name="address"]').addClass('error');

				if (number == "")
					$('[name="number"]').addClass('error');

				if (colony == "")
					$('[name="colony"]').addClass('error');

				if (postalCode == "")
					$('[name="postalCode"]').addClass('error');

				if (city == "")
					$('[name="city"]').addClass('error');

				if (state_idstate == "" || state_idstate == undefined)
					$('[name="state_idstate"]').addClass('error');

				if (rfc == "")
					$('.rfc_edit').addClass('error');

				if (phone == "")
					$('[name="phone"]').addClass('error');

				if (contact == "")
					$('[name="contact"]').addClass('error');

				if (beneficiary == "")
					$('[name="beneficiary"]').addClass('error');

				swal('Error','Falta llenar campos obligatorios.','error');
			}
			else
			{
				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ url("/administration/requisition/update-provider") }}',
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
						'agreement'		:agreement,
						'idProvider' 	:idProvider,

					},
					success : function(data)
					{
						swal('Actualizado','Proveedor Actualizado','success');
						$('#form_edit_provider').empty();
						$('#result_provider').fadeIn();
						
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				})
			}


		})
		.on('click','[data-dismiss="modal"]',function()
		{
			$('#result_provider').fadeIn();
			$('#form_edit_provider').empty();
		})
		.on('click','.addDocumentProvider',function()
		{
			idRequisitionHasProvider = $(this).parent('div').find('.id_provider_secondary').val();
			$('[name="idRequisitionHasProviderDoc"]').val(idRequisitionHasProvider);

		})
		.on('click','#addDocProvider',function()
		{
			newdoc	= $('<div class="docs-p"></div>')
						.append($('<div class="docs-p-l"></div>')
							.append($('<select class="custom-select nameDocument" name="nameDocument[]"></select><br><br>')
								.append($('<option value="0" disabled selected>Seleccione uno</option>'))
								.append($('<option value="Cotización">Cotización</option>'))
								.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
								.append($('<option value="Control de Calidad">Control de Calidad</option>'))
								.append($('<option value="Contrato">Contrato</option>'))
								.append($('<option value="Factura">Factura</option>'))
								.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
								.append($('<option value="Otro">Otro</option>')))
							.append($('<div class="uploader-content"></div>')
								.append($('<input type="file" name="path" class="input-text pathActioner" accept=".pdf,.jpg,.png">'))	
							)
							.append($('<input type="hidden" name="realPath[]" class="path">')
								)
						)
						.append($('<div class="docs-p-r"></div>')
							.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
							)
						);
			$('#documents').append(newdoc);
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
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
							"label" => "Tipo de documento:",
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
									"label" => "Fecha:",
									"classEx" => "datepicker datepath pt-2",
								],
								[
									"kind" 	=> "components.inputs.input-text", 
									"classEx" => "datepicker datepath pb-2",
									"attributeEx"	=> "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Hora:",
									"classEx" => "timepath hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "timepath hidden pb-2",
									"attributeEx"	=> "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Folio fiscal:",
									"classEx" => "fiscal_folio hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "fiscal_folio hidden pb-2",
									"attributeEx"	=> "name=\"fiscal_folio[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Número de ticket:",
									"classEx" => "ticket_number hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "ticket_number hidden pb-2",
									"attributeEx"	=> "name=\"ticket_number[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Monto total:",
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
					/*newdoc	= $('<div class="docs-p"></div>')
						.append($('<div class="docs-p-l"></div>')
							.append($('<select class="nameDocumentRequisition" name="nameDocumentRequisition[]" multiple="multiple" data-validation="required"></select><br><br>')
								.append($('<option value="Cotización">Cotización</option>'))
								.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
								.append($('<option value="Control de Calidad">Control de Calidad</option>'))
								.append($('<option value="Contrato">Contrato</option>'))
								.append($('<option value="Factura">Factura</option>'))
								.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
								.append($('<option value="Otro">Otro</option>')))
							.append($('<div class="uploader-content" style="width: 100%; margin: 0px;"></div>')
								.append($('<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">'))	
							)
							.append($('<input type="hidden" name="realPathRequisition[]" class="path">')
								)
							.append($('<br><br><input type="text" name="datepath[]" step="1" class="new-input-text datepicker datepath" placeholder="Seleccione fecha" readonly="readonly" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="timepath[]" step="60" value="00:00" class="new-input-text timepath" placeholder="Seleccione hora" readonly="readonly" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="fiscal_folio[]" class="new-input-text fiscal_folio" placeholder="Folio Fiscal" style="display:none;" data-validation="required">" '))
							.append($('<br><input type="text" name="ticket_number[]" class="new-input-text ticket_number" placeholder="Número de Ticket" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="amount[]" class="new-input-text amount" placeholder="Monto total" style="display:none;" data-validation="required">'))
						)
						.append($('<div class="docs-p-r"></div>')
							.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
							)
						);*/
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
							"label" => "Tipo de documento:",
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
							"attributeExInput" => "name=path accept=.pdf,.jpg,.png",
							"classExInput" => "pathActionerRequisition",
							"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
							"classExDelete" => "delete-doc"
						])->render();
					@endphp
					newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
					/*newdoc	= $('<div class="docs-p"></div>')
						.append($('<div class="docs-p-l"></div>')
							.append($('<select class="nameDocumentRequisition" name="nameDocumentRequisition[]" multiple="multiple" data-validation="required"></select><br><br>')
								.append($('<option value="Acta Constitutiva">Acta Constitutiva</option>'))
								.append($('<option value="Poder del representante legal">Poder del representante legal</option>'))
								.append($('<option value="Identificación oficial">Identificación oficial</option>'))
								.append($('<option value="RFC">RFC</option>'))
								.append($('<option value="Cedula Fiscal">Cedula Fiscal</option>'))
								.append($('<option value="Domicilio">Domicilio</option>'))
								.append($('<option value="CV">CV</option>'))
								.append($('<option value="Revisión técnica">Revisión técnica</option>'))
								.append($('<option value="Anexos">Anexos</option>'))
								.append($('<option value="Pólizas de Fianza">Pólizas de Fianza</option>')))
							.append($('<div class="uploader-content" style="width: 100%; margin: 0px;"></div>')
								.append($('<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">'))	
							)
							.append($('<input type="hidden" name="realPathRequisition[]" class="path">')
								)
						)
						.append($('<div class="docs-p-r"></div>')
							.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
							)
						);*/
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
							"label" => "Tipo de documento:",
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
							"attributeExInput" => "name=path accept=.pdf,.jpg,.png",
							"classExInput" => "pathActionerRequisition",
							"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
							"classExDelete" => "delete-doc",
							"componentsExDown"		=>  [
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Fecha:",
									"classEx" => "datepicker datepath pt-2",
								],
								[
									"kind" 	=> "components.inputs.input-text", 
									"classEx" => "datepicker datepath pb-2",
									"attributeEx"	=> "name=\"datepath[]\" step=\"1\" placeholder=\"Ingrese la fecha\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Hora:",
									"classEx" => "timepath hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "timepath hidden pb-2",
									"attributeEx"	=> "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione la hora\" readonly=\"readonly\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Folio fiscal:",
									"classEx" => "fiscal_folio hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "fiscal_folio hidden pb-2",
									"attributeEx"	=> "name=\"fiscal_folio[]\" placeholder=\"Ingrese el folio fiscal\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Número de ticket:",
									"classEx" => "ticket_number hidden pt-2",
								],
								[
									"kind" 			=> "components.inputs.input-text", 
									"classEx" 		=> "ticket_number hidden pb-2",
									"attributeEx"	=> "name=\"ticket_number[]\" placeholder=\"Ingrese el número de ticket\" data-validation=\"required\""
								],
								[
									"kind" 	=> "components.labels.label", 
									"label" => "Monto total:",
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
					/*newdoc	= $('<div class="docs-p"></div>')
						.append($('<div class="docs-p-l"></div>')
							.append($('<select class="nameDocumentRequisition" name="nameDocumentRequisition[]" multiple="multiple" data-validation="required"></select><br><br>')
								.append($('<option value="Cotización">Cotización</option>'))
								.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
								.append($('<option value="Control de Calidad">Control de Calidad</option>'))
								.append($('<option value="Contrato">Contrato</option>'))
								.append($('<option value="Factura">Factura</option>'))
								.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
								.append($('<option value="Otro">Otro</option>')))
							.append($('<div class="uploader-content" style="width: 100%; margin: 0px;"></div>')
								.append($('<input type="file" name="path" class="input-text pathActionerRequisition" accept=".pdf,.jpg,.png">'))	
							)
							.append($('<input type="hidden" name="realPathRequisition[]" class="path">')
								)
							.append($('<br><br><input type="text" name="datepath[]" step="1" class="new-input-text datepicker datepath" placeholder="Seleccione fecha" readonly="readonly" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="timepath[]" step="60" value="00:00" class="new-input-text timepath" placeholder="Seleccione hora" readonly="readonly" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="fiscal_folio[]" class="new-input-text fiscal_folio" placeholder="Folio Fiscal" style="display:none;" data-validation="required">" '))
							.append($('<br><input type="text" name="ticket_number[]" class="new-input-text ticket_number" placeholder="Número de Ticket" style="display:none;" data-validation="required">'))
							.append($('<br><input type="text" name="amount[]" class="new-input-text amount" placeholder="Monto total" style="display:none;" data-validation="required">'))
						)
						.append($('<div class="docs-p-r"></div>')
							.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
							)
						);*/
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

			validation();

			@php
				$selects = collect([
					[
						"identificator"         => '[name="nameDocumentRequisition[]"]', 
						"placeholder"           => "Seleccione el tipo de documento",
						"maximumSelectionLength"=> "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
			// $('[name="nameDocumentRequisition[]"]').select2(
			// {
			// 	language				: "es",
			// 	maximumSelectionLength	: 1,
			// 	placeholder 			: "Seleccione el tipo de documento",
			// 	width 					: "100%",
			// })
			// .on("change",function(e)
			// {
			// 	if($(this).val().length>1)
			// 	{
			// 		$(this).val($(this).val().slice(0,1)).trigger('change');
			// 	}
			// });
		})
		.on('change','.pathActionerRequisition',function(e)
		{
			filename		= $(this);
			uploadedName 	= $(this).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]');
			extention		= /\.jpg|\.png|\.jpeg|\.pdf/i;
			
			if (filename.val().search(extention) == -1)
			{
				swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf.', 'warning');
				$(this).val('');
			}
			else if (this.files[0].size>315621376)
			{
				swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb.', 'warning');
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
					url			: '{{ url("/administration/requisition/upload") }}',
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
		.on('click','.delete-doc-requisition',function()
		{
			swal(
			{
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false
			});
			actioner		= $(this);
			uploadedValue	= $(this).parents('.docs-p').find('.path').val();
			formData		= new FormData();
			formData.append("realPath[]",uploadedValue);
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
				},
				error		: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
				}
			});
			$(this).parents('div.docs-p').remove();
		})
		.on('click','.delete-doc',function()
		{
			swal(
			{
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false
			});
			actioner		= $(this);
			uploadedValue	= $(this).parents('.docs-p').find('.path').val();
			formData		= new FormData();
			formData.append("realPath[]",uploadedValue);
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
				},
				error		: function()
				{
					swal('','Sucedió un error, por favor intente de nuevo.','error');
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
				type 	: 'get',
				url 	: '{{ url("administration/requisition/provider-documents/view") }}',
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
				}
			})
		})
		.on('click','.add-queue',function()
		{
			id				= $(this).attr('data-provider-id');
			businessName	= $(this).attr('data-provider-business-name');
			rfc				= $(this).attr('data-provider-rfc');

			queue = $('<tr></tr>')
					.append($('<td></td>')
						.append(id)
						.append($('<input type="hidden" name="multiprovider[]" value="'+id+'">')))
					.append($('<td></td>')
						.append(businessName))
					.append($('<td></td>')
						.append(rfc));

			$('#result_queue_provider').append(queue);
			$(this).parents('tr').remove();

			if($('#result_queue_provider tr').length > 0)
			{
				$('.table-queue-provider').show();
			}
			else
			{
				$('.table-queue-provider').hide();
			}
		})
		.on('change','[name="code[]"]',function()
		{
			code		= $(this).parents('.tr').find('[name="code[]"] option:selected').val();
			conceptId	= $(this).attr('concept-id');
			target		= $(this);
			input 		= $(this).parents('.tr').find('[name="codeReal[]"]');
			input.val(null);

			if (code != undefined) 
			{
				$.ajax(
				{
					type 	: 'get',
					url 	: '{{ route("requisition.validation-code") }}',
					data 	: {
						'code':code,'conceptId':conceptId
					},
					success : function(data)
					{
						if (data.validate == "false") 
						{
							target.val(0).trigger("change");
							input.val(null);

							swal("","Este código le pertenece al concepto "+data.concepts+". Por favor intente con otro.","error");
						}
						else
						{
							input.val(code);
						}
					},
					error : function(data)
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
					}
				});
			}
		})
		.on('click','#addDocNewProvider',function()
		{
			newdoc	= $('<div class="docs-p"></div>')
						.append($('<div class="docs-p-l"></div>')
							.append($('<select class="custom-select nameDocumentNewProvider" name="nameDocumentNewProvider[]"></select><br><br>')
								.append($('<option value="0" disabled selected>Seleccione uno</option>'))		
								.append($('<option value="Cotización">Cotización</option>'))
								.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
								.append($('<option value="Control de Calidad">Control de Calidad</option>'))
								.append($('<option value="Contrato">Contrato</option>'))
								.append($('<option value="Factura">Factura</option>'))
								.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
								.append($('<option value="Otro">Otro</option>')))
							.append($('<div class="uploader-content"></div>')
								.append($('<input type="file" name="path" class="input-text pathActionerNewProvider" accept=".pdf,.jpg,.png">'))	
							)
							.append($('<input type="hidden" name="realPathNewProvider[]" class="path">')
								)
						)
						.append($('<div class="docs-p-r"></div>')
							.append($('<button class="delete-doc" type="button"><span class="icon-x delete-span"></span></button>')
							)
						);
			$('#documents-new-provider').append(newdoc);
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
		.on('change','.nameDocumentRequisition',function()
		{
			type_document = $('option:selected',this).val();
			switch(type_document)
			{
				case 'Factura': 
					$(this).parents('.docs-p').find('.fiscal_folio').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.ticket_number').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.amount').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
				case 'Ticket': 
					$(this).parents('.docs-p').find('.fiscal_folio').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.ticket_number').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.amount').show().removeClass('error').val('');
					$(this).parents('.docs-p').find('.timepath').show().removeClass('error').val('');	
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
				default :  
					$(this).parents('.docs-p').find('.fiscal_folio').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.ticket_number').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.amount').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.timepath').hide().attr("style", "display:none").val('');
					$(this).parents('.docs-p').find('.datepath').show().removeClass('error').val('');	
					break;
			}
		})
		.on('click','[name="send"]',function(e)
		{
			e.preventDefault();
			swal("Cargando",{
				icon				: '{{ asset(getenv('LOADING_IMG')) }}',
				button				: false,
				closeOnClickOutside	: false,
				closeOnEsc			: false
			});
			fiscal_folio	= [];
			ticket_number	= [];
			timepath		= [];
			amount			= [];
			datepath		= [];
			object = $(this);
			if ($('[name="datepath[]"]').length > 0) 
			{
				$('[name="datepath[]"]').each(function(i,v)
				{	
					datepath.push($(this).val());
					fiscal_folio.push($(this).siblings('[name="fiscal_folio[]"]').val());
					ticket_number.push($(this).siblings('[name="ticket_number[]"]').val());
					timepath.push($(this).siblings('[name="timepath[]"]').val());
					amount.push($(this).siblings('[name="amount[]"]').val());
					
					$(this).siblings('[name="fiscal_folio[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="ticket_number[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="timepath[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).siblings('[name="amount[]"]').removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
					$(this).removeClass('error').removeClass('valid').css({ 'background-color' : 'border-color'});
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
						$('[name="datepath[]"]').each(function(j,v)
						{							
							ticket_number	= $(this).siblings('[name="ticket_number[]"]');
							fiscal_folio	= $(this).siblings('[name="fiscal_folio[]"]');
							timepath		= $(this).siblings('[name="timepath[]"]');
							amount			= $(this).siblings('[name="amount[]"]');
							datepath		= $(this);
							$(data).each(function(i,d)
							{
								if (d == fiscal_folio.val() || d == ticket_number.val()) 
								{
									ticket_number.addClass('error');
									fiscal_folio.addClass('error');
									timepath.addClass('error');
									amount.addClass('error');
									datepath.addClass('error');
									flag = true;
								}
							});
						});
						if (flag) 
						{
							swal('','Los documentos marcados ya se encuentran registrados, por favor verifique los datos.','error');
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
					nameDocument = $(this).parents('.docs-p').find('[name="nameDocumentRequisition[]"] option:selected').val();
					if( $(this).val() == "" || nameDocument == undefined )
					{
			 			flag = true;
					}
				});
				if(flag)
				{
					swal('', 'Aún tiene un archivo sin agregar, por favor verifique sus campos.', 'error');
				}
				else
				{
					$('.remove-validation-concept').removeAttr('data-validation');
					action = object.attr('formaction');
					form   = $('#container-alta').attr('action',action);
					form.submit();
				}
			}
		})
		.on('change','.fiscal_folio,.ticket_number,.timepath,.amount,.datepath',function()
		{
			$(this).removeClass('error').css({ 'background-color' : 'border-color'});
			object 		= $(this);
			flag 		= false;
			$('.docs-p').each(function(i,v)
			{
				firstFiscalFolio	= $(this).find('[name="fiscal_folio[]"]').val();
				firstTimepath		= $(this).find('[name="timepath[]"]').val();
				firstDatepath		= $(this).find('[name="datepath[]"]').val();
				$('.docs-p').each(function(j,v)
				{
					if(i!==j)
					{
						scndFiscalFolio		= $(this).find('[name="fiscal_folio[]"]').val();
						scndTimepath		= $(this).find('[name="timepath[]"]').val();
						scndDatepath		= $(this).find('[name="datepath[]"]').val();
						if (firstFiscalFolio != "" && firstTimepath != "" && firstDatepath != "" && scndFiscalFolio != "" && scndTimepath != "" && scndDatepath != "" && firstDatepath == scndDatepath && firstTimepath == scndTimepath && firstFiscalFolio.toUpperCase() == scndFiscalFolio.toUpperCase())
						{
							flag = true;
						}
					}
				});
			});
			if(flag)
			{
				swal('', 'La factura ya ha sido registrada en esta solicitud, por favor intenta nuevamente.', 'error');
				object.parent().find('.timepath').addClass('error').val('');
				object.parent().find('.datepath').addClass('error').val('');
				object.parent().find('.fiscal_folio').addClass('error').val('');
				return false;
			}
		})
		.on('click','.view-employee',function()
		{
			employee_id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
			if(employee_id != "")
			{
				$.ajax(
				{
					type	: 'post',
					url		: '{{ route("requisition.view-detail-employee") }}',
					data	: {'employee_id':employee_id},
					success : function(data)
					{
						$('.modal-employee').html(data);
						$('#detailEmployee').modal('show');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#detailEmployee').hide();
					}
				});
			}
		})
	});
	function zipCode()
	{
		$('#cp').select2({
			maximumSelectionLength: 1,
			width		: "80%",
			placeholder : "Ingrese un código postal",
			ajax                  :
			{
				delay   : 400,
				url     : '{{route('requisition.catalogue.zip')}}',
				dataType: 'json',
				method  : 'post',
				data    : function (params)
				{
					s =
					{
						search: params.term,
					}
					return s;
				}
			},
			minimumInputLength: 3,
			language          : 
			{
				noResults: function()
				{
					return "No hay resultados";
				},
				searching: function()
				{
					return "Buscando...";
				},
				inputTooShort: function(args)
				{
					return 'Por favor ingrese más de 3 caracteres';
				}
			}
		})
		.on("change",function(e)
		{
			if($(this).val().length>1)
			{
				$(this).val($(this).val().slice(0,1)).trigger('change');
			}
		});
	}
</script>
@endsection
