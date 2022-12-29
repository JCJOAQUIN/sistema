@extends('layouts.child_module')
@section('data')
	@component("components.forms.form", ["methodEx" => "PUT", "attributeEx" => "method=\"POST\" action=\"".route('requisition.generate-request',$request->folio)."\" id=\"container-alta\"","files" => true])
		<div class="sm:text-center text-left my-5">
			A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
		</div>
		@php
			
			$modelTable = 
			[
				["Tipo de Requisición",$request->requisition->typeRequisition->name],
				["Proyecto", $request->requestProject()->exists() ? $request->requestProject->proyectName : 'No hay'],
			];
			if($request->requisition->code_wbs != "")
			{
				$modelTable[] = ["Subproyecto/Código WBS",$request->requisition->wbs()->exists() ? $request->requisition->wbs->code_wbs : 'No hay'];
			}
			if($request->requisition->code_wbs != "")
			{
				$modelTable[] = ["Código EDT",$request->requisition->edt()->exists() ? $request->requisition->edt->fullName() : 'No hay'];
			}
			$modelTable[] = ["Prioridad",$request->requisition->urgent == 1 ? 'Alta' : 'Baja'];
			$modelTable[] = ["Folio", $request->folio,];
			if($request->requisition()->exists() && $request->requisition->request_requisition != "")
			{
				$modelTable[] = ["Solicitante",$request->requisition()->exists() ? $request->requisition->request_requisition : 'Sin solicitante'];
			}
			else
			{
				$modelTable[] = ["Solicitante",$request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin solicitante'];
			}
			$modelTable[] = ["Título", htmlentities($request->requisition->title)];
			$modelTable[] = ["Número",$request->requisition->number];
			if($request->requisition->requisition_type == 5)
			{
				$modelTable[] = ["Compra/Renta",$request->requisition->buy_rent];
				if($request->requisition->buy_rent == "Renta")
				{
					$modelTable[] = ["Vigencia",$request->requisition->validity];
				}
			}
			$modelTable[] = ["Fecha en que se solicitó",$request->requisition->date_request];
			$modelTable[] = ["Fecha de comparación",$request->requisition->date_comparation];
			if($request->requisition->date_obra != '')
			{
				$modelTable[] = ["Fecha en que debe estar en obra",$request->requisition->date_obra];
			}				
		@endphp
		@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Requisición"])@endcomponent	
		@if($request->requisition->requisition_type != 3)
			@php
				$count = 0;
			@endphp
			@foreach ($request->requisition->details as $detail) 
				@foreach ($detail->getWinnerProvider as $voteGlobal) 
					@php
						$winnerProvider[$count]['businessName']				= $voteGlobal->businessName;
						$winnerProvider[$count]['idRequisitionHasProvider']	= $voteGlobal->idRequisitionHasProvider;
						$winnerProvider[$count]['idProviderSecondary']		= $voteGlobal->idProviderSecondary;
						$count++;
					@endphp
				@endforeach
			@endforeach
			@component("components.labels.not-found",["variant" => "note"])
				@slot("slot") Seleccione que tipo de solicitud desea generar y llene los campos necesarios. @endslot
			@endcomponent
			@component("components.labels.not-found",["variant" => "note"])
				@slot("slot") Las solicitudes que sean generadas se guardarán en estado "Guardado" y podrá encontrarlas en los módulos correspondientes a Compras y Reembolsos. @endslot
			@endcomponent
			@foreach ($request->requisition->getWinnerProvider as $providerWin)
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden"
						name="providers[]"
						value="{{ $providerWin->idProviderSecondary }}"
					@endslot
				@endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="hidden" 
						name="idRequisitionHasProvider[]" 
						value="{{ $providerWin->idRequisitionHasProvider }}"
					@endslot
				@endcomponent
				@component("components.buttons.button",["variant" => "warning"])
					@slot("classEx")
						accordion
					@endslot
					@slot("attributeEx")
						type="button"
					@endslot
					SOLICITUD A GENERAR PARA {{ mb_strtoupper($providerWin->businessName) }} - {{ mb_strtoupper($providerWin->rfc) }}
				@endcomponent
				<div class="panel">
					@component("components.labels.title-divisor")
						SOLICITUD A GENERAR
					@endcomponent
					@component("components.containers.container-form")
						<div class="col-span-2">
							@component("components.labels.label")
								Tipo de Solicitud
							@endcomponent
							@php
								$options = collect(
									[
										["value" => "1", "description" => "Compra (Compra Central)"],
										["value" => "9", "description" => "Reembolso (Compra Local)"],
									]
								);
								$attributeEx = "multiple=\"multiple\" data-validation=\"required\" name=\"typeRequest_".$providerWin->idProviderSecondary."\"";
								$classEx = "js-type-request form-control";
							@endphp
							@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
						</div>
					@endcomponent
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
					@php
						$modelBody = [];
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
									["value" => "Precio Unitario"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Retenciones"],
									["value" => "Total"],
								];
							break;
							case '2':
								$modelHead = 
								[
									["value" => "Nombre"],
									["value" => "Descripción"],
									["value" => "Periodo"],
									["value" => "Categoría"],
									["value" => "Cant."],
									["value" => "Unidad"],
									["value" => "Precio Unitario"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Retenciones"],
									["value" => "Total"],
								];
							break;
							case '4':
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Cant."],
									["value" => "Unidad"],
									["value" => "Precio Unitario"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Retenciones"],
									["value" => "Total"],	
								];
							break;
							case '5':
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Marca"],
									["value" => "Modelo"],
									["value" => "Tiempo de Utilización"],
									["value" => "Existencia en Almacén"],
									["value" => "Categoría"],
									["value" => "Cant."],
									["value" => "Medida"],
									["value" => "Unidad"],
									["value" => "Precio Unitario"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Retenciones"],
									["value" => "Total"],						
								];
							break;
							case '6':
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Cant."],
									["value" => "Unidad"],
									["value" => "Precio Unitario"],
									["value" => "Subtotal"],
									["value" => "IVA"],
									["value" => "Impuesto Adicional"],
									["value" => "Retenciones"],
									["value" => "Total"],
								];
							break;
						}
						if(in_array($request->status,[3,4,5,17]))
						{
							array_splice($modelHead, count(array_column($modelHead,'show')), 0, [["value" => "Part."]]);
						}
						$modelGroup = 
						[
							[
								"name" 		=> "Conceptos",
								"id"		=> "concepts",
								"colNumber"	=> (count(array_column($modelHead,'show')))
							],
							[
								"name"		=> "Detalles",
								"id" 		=> "details",
								"colNumber"	=> (count($modelHead)-count(array_column($modelHead,'show')))
							]
						];
						foreach($request->requisition->details->where('idRequisitionHasProvider',$providerWin->idRequisitionHasProvider) as $detail)
						{
							
							$price 	= $detail->priceWin($providerWin->idRequisitionHasProvider)->first();
							$iva	= (App\Parameter::where('parameter_name','IVA')->first()->parameter_value)/100;
							$iva2	= (App\Parameter::where('parameter_name','IVA2')->first()->parameter_value)/100;
							$ivaCalc	= 0;
							if(isset($price->typeTax))
							{
								$typeTax = $price->typeTax;
							}
							else
							{
								$typeTax = "no";
							}
							switch($typeTax)
							{
								case 'no':
									$ivaCalc = 0;
									break;
								case 'a':
									$ivaCalc = $detail->quantity*$price->unitPrice*$iva;
									break;
								case 'b':
									$ivaCalc = $detail->quantity*$price->unitPrice*$iva2;
									break;
							}
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
													"label" => $detail->name, 								
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->exists_warehouse, 							
												]
											]
										],
										[
											"content"	=>
											[
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_id",
													"attributeEx" 	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_category",
													"attributeEx" 	=> "type=\"hidden\" value=\"".$detail->category."\"",
												],
												[
													"label"			=> $detail->categoryData()->exists() ? $detail->categoryData->description : '',
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_type",
													"attributeEx"	=> " type=\"hidden\" value=\"".$detail->cat_procurement_material_id."\"",
												],
												[
													"label"			=> $detail->procurementMaterialType()->exists() ? $detail->procurementMaterialType->name : '',
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_quantity",
													"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
												],
												[
													"label"			=> $detail->quantity,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->measurement,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->unit,
												]
											]					
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->unitPrice,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->subtotal,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($ivaCalc,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->taxes,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->retentions,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->total,2)
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
													"label" => $detail->name, 								
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->period, 							
												]
											]
										],
										[
											"content"	=>
											[
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_id",
													"attributeEx" 	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_category",
													"attributeEx" 	=> "type=\"hidden\" value=\"".$detail->category."\"",
												],
												[
													"label"			=> $detail->categoryData()->exists() ? $detail->categoryData->description : '',
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_quantity",
													"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
												],
												[
													"label"			=> $detail->quantity,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->unit,
												]
											]					
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->unitPrice,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->subtotal,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($ivaCalc,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->taxes,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->retentions,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->total,2)
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
													"label" => $detail->name, 								
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description, 															
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_id",
													"attributeEx" 	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_quantity",
													"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
												],
												[
													"label"			=> $detail->quantity,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->unit,
												]
											]					
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->unitPrice,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->subtotal,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($ivaCalc,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->taxes,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->retentions,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->total,2)
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
													"label" => $detail->name, 								
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->brand, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->model, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->usage_time, 															
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->exists_warehouse, 															
												]
											]
										],
										[
											"content"	=>
											[
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_id",
													"attributeEx" 	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												],
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_category",
													"attributeEx" 	=> "type=\"hidden\" value=\"".$detail->category."\"",
												],
												[
													"label"			=> $detail->categoryData()->exists() ? $detail->categoryData->description : '',
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_quantity",
													"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
												],
												[
													"label"			=> $detail->quantity,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->measurement,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->unit,
												]
											]					
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->unitPrice,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->subtotal,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($ivaCalc,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->taxes,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->retentions,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->total,2)
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
													"label" => $detail->name, 								
												]
											]
										],
										[
											"content" => 
											[
												[
													"label" => $detail->description, 															
												]
											]
										],
										[
											"content" =>
											[
												[
													"kind" 			=> "components.inputs.input-text",
													"classEx" 		=> "t_id",
													"attributeEx" 	=> "type=\"hidden\" name=\"idRequisitionDetail[]\" value=\"".$detail->id."\"",
												],
												[
													"kind"			=> "components.inputs.input-text",
													"classEx"		=> "t_quantity",
													"attributeEx"	=> "type=\"hidden\" value=\"".$detail->quantity."\"",
												],
												[
													"label"			=> $detail->quantity,
												]
											]
										],
										[
											"content" =>
											[
												[
													"label"			=> $detail->unit,
												]
											]					
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->unitPrice,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->subtotal,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($ivaCalc,2),
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->taxes,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => $price != "" ? "$".number_format($price->retentions,2) : "0.00",
												]
											]
										],
										[
											"content" =>
											[
												[
													"label" => "$".number_format($price->total,2)
												]
											]
										]
									];
								break;
							}
							if(in_array($request->status,[3,4,5,17]))
							{
								array_splice($body, count(array_column($modelHead,'show')), 0, [["content" => [["label" => $detail->part]]]]);
							}
							$modelBody[] = $body;
						}
					@endphp
					@component('components.tables.table-provider',[
						"noButtons"			=> true,
						"modelHead"   		=> $modelHead,
						"modelBody"   		=> $modelBody,
						"modelGroup"		=> $modelGroup
					])
					@endcomponent
				</div>
			@endforeach
					
		@else
			@if ($request->requisition->staff()->exists())
				<div class="mt-4">
					@component("components.labels.title-divisor")
						DATOS DE LA VACANTE
					@endcomponent
				</div>
				<div class="my-6">
					@component("components.tables.table-request-detail.container",["variant" => "simple"])
						@php
							
							$modelTable = 
								[
									"Jefe inmediato" 					=> $request->requisition->staff->boss->fullName(),
									"Horario"							=> $request->requisition->staff->staff_schedule_start." - ".$request->requisition->staff->staff_schedule_end,
									"Rango de sueldo"					=> "$".number_format($request->requisition->staff->staff_min_salary,2)." - "."$".number_format($request->requisition->staff->staff_max_salary,2),
									"Motivo"							=> $request->requisition->staff->staff_reason,
									"Puesto"							=> $request->requisition->staff->staff_position,
									"Periodicidad"						=> $request->requisition->staff->staff_periodicity,
									"Descripción general de la vacante" => $request->requisition->staff->staff_s_description,
									"Habilidades requeridas"			=> $request->requisition->staff->staff_habilities,
									"Experiencia deseada"				=> $request->requisition->staff->staff_experience,
								];
								foreach($request->requisition->staffFunctions as $function)
								{
									$modelTable['Función'] = $function->function;
									$modelTable['Descripción de la función'] = $function->description;
								}
								foreach($request->requisition->staffResponsabilities as $responsibilityStaff)
								{
									$modelTable['Responsabilidades'] = "-".$responsibilityStaff->dataResponsibilities->responsibility;
								}
								foreach($request->requisition->staffDesirables as $desirable)
								{
									$modelTable['Deseables'] = $desirable->desirable;
									$modelTable['Descripción'] = $desirable->description;
								}
						@endphp
						@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable])
							@slot("classEx")
								employee-details
							@endslot
						@endcomponent
					@endcomponent	
				</div>
			@else
				<div id="staff-table" @if(isset($request) && in_array($request->requisition->requisition_type,[1,2,4,5,6])) class="hidden" @elseif(!isset($request)) class="hidden" @endif>
					@php
						$heads = ["Nombre","CURP","Puesto","Acción"];
						$modelBody = [];

						if(isset($request) && $request->requisition->employees()->exists())
						{
							foreach($request->requisition->employees as $emp)
							{
								$body = 
								[
									"classEx" => "tr",
									[
										"content" =>
										[
											[
												"label" => $emp->fullName(),
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => $emp->curp,
											]
										]
									],
									[
										"content" =>
										[
											[
												"label" => $emp->position
											]
										]
									],
									[
										"content" =>
										[
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx" 	=> "type=\"hidden\" name=\"rq_employee_id[]\" value=\"".$emp->id."\"",
											],
											[
												"kind" 			=> "components.buttons.button",
												"classEx" 		=> "follow-btn view-employee",
												"attributeEx" 	=> "data-toggle=\"modal\" data-target=\"#detailEmployee\" type=\"button\"",
												"label"			=> "<span class=\"icon-search\"></span>",
												"variant"		=> "secondary",
											]
										]
									],
								];
								$modelBody [] = $body;
							}
						}
					@endphp
					@component("components.tables.alwaysVisibleTable",[
						"modelHead" => $heads,
						"modelBody" => $modelBody,
					])
						@slot("attributeExBody")
							id="list_employees"
						@endslot
					@endcomponent
				</div>
			@endif
		@endif		
		@component('components.labels.title-divisor') DOCUMENTOS DE LA REQUISICIÓN @endcomponent
		@if($request->requisition->documents()->exists())
			@php
				$heads = ["Tipo de Documento","Archivo","Modificado Por","Fecha"];
				$modelBody = [];

				foreach($request->requisition->documents->sortByDesc('created') as $doc)
				{
					$body = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"label" => $doc->name,
								]
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.buttons.button",
									"attributeEx" 	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/requisition/'.$doc->path)."\"",
									"buttonElement" => "a",
									"label"			=> "PDF",
									"variant"		=> "dark-red",
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $doc->user->fullName(),
								]
							]
						],
						[
							"content" =>
							[
								[
									"label" => $doc->created->format('d-m-Y'),
								]
							]
						]
					];
					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead"	=> $heads,
				"modelBody"	=> $modelBody,
			])
				@slot("classEx")
					text-center
				@endslot
			@endcomponent
		@endif
		<div class="text-center my-6">
			@if ($request->requisition->requisition_type != 3)
				@component("components.buttons.button", ["variant" => "primary"])
					@slot("attributeEx")
						type="submit" name="send"
					@endslot
					GENERAR SOLICITUDES
				@endcomponent
			@endif
			@component("components.buttons.button", ["variant" => "reset", "buttonElement" => "a"])
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
	@component("components.modals.modal",["variant" => "large"])
		@slot("id")
			detailEmployee
		@endslot
		@slot("attributeEx")
			tabindex="-1"
		@endslot
		@slot("classExBody")
			modal-employee
		@endslot
		@slot("modalFooter")
			@component("components.buttons.button", ["variant" => "red"])
				@slot('attributeEx')
					type="button"
					data-dismiss="modal"
				@endslot
				<span class="icon-x"></span>
				Cerrar
			@endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script type="text/javascript">
		function validation()
		{
			$.validate(
			{
				form	: '#container-alta',
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
					return true;
				}
			});
		}		
		$(document).ready(function()
		{
			validation();
			$(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
			$('.t_unitPrice,.t_subtotal,.t_total').numeric({ altDecimal: ".", decimalPlaces: 2 });
			@php
				$selects = collect([
					[
						"identificator"          => "[name=\"state_idstate\"],.js-bank,.js-type-request", 
						"placeholder"            => "Seleccione uno", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"user_request\"],[name=\"urgent\"],[name=\"project_id\"],[name=\"account_id\"],[name=\"area_id\"],[name=\"enterprise_id\"],[name=\"department_id\"]", 
						"placeholder"            => "Seleccione uno", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
			$(document).on('click','#upload_file,#save',function()
			{
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
			})
			.on('click','[data-toggle="modal"]',function()
			{
				@php
					$selects = collect([
						[
							"identificator"          => "[name=\"state_idstate\"],.js-bank,.js-type-request", 
							"placeholder"            => "Seleccione uno", 
							"language"               => "es",
							"maximumSelectionLength" => "1"
						],
					]);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
				//id			= $(this).parents('tr').find('.t_id').val();
				//quantity	= $(this).parents('tr').find('.t_quantity').val();
				//unit		= $(this).parents('tr').find('.t_unit').val();
				//description = $(this).parents('tr').find('.t_description').val();
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

				$(this).parents('.tr').find('.t_total[data-provider="'+idProvider+'"]').val(total.toFixed(2));
			})
			.on('click','[name="btnSave"],[name="btnAddProvider"],[name="btnDeleteProvider"]',function()
			{
				$('.remove-validation-concept').removeAttr('data-validation');
			})
			.on('change','select[name="enterprise_id"]',function()
			{
				$('select[name="account_id"]').empty();
				generalSelect({'selector': '.js-accounts', 'depends': '.js-enterprises', 'model': 10});
			})
			.on('click','.view-employee',function()
			{
				employee_id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
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
			});

			acc = $(".accordion");
			for (i = 0; i < acc.length; i++) 
			{
				acc[i].addEventListener("click", function() 
				{
					this.classList.toggle("active");
					var panel = this.nextElementSibling;
					if (panel.style.maxHeight) 
					{
						panel.style.maxHeight = null;
					} 
					else 
					{
						panel.style.maxHeight = panel.scrollHeight + "px";
					} 
					@php
						$selects = collect([
							[
								"identificator"          => ".js-type-request", 
								"placeholder"            => "Seleccione uno", 
								"language"               => "es",
								"maximumSelectionLength" => "1"
							],
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects])@endcomponent
				});
			}
		});
	</script>
@endsection