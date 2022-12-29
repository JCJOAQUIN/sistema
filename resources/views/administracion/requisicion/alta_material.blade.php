@extends('layouts.child_module')

@section('data')
	@if (isset($globalRequests) && $globalRequests == true)
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					@slot("classEx")
						font-bold
						inline-block
						text-blue-900
					@endslot
						TIPO DE SOLICITUD: 
				@endcomponent
				{{ mb_strtoupper($request->requestkind->kind) }}
			@endslot
		@endcomponent
	@endif
	@if(isset($request))
		@if (isset($new_requisition) && $new_requisition)
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requisition.store')."\"", "files" => true])
			@component('components.inputs.input-text', ["attributeEx" => "type=\"hidden\" name=\"folio_requisition_rejected\" value=\"".$request->folio."\""]) @endcomponent
		@else
			@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requisition.update',$request->folio)."\"", "methodEx" => "PUT", "files" => true])
		@endif
	@else
		@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('requisition.store')."\"", "files" => true])
	@endif
		@if(isset($request) && !isset($new_requisition))
			@component("components.labels.not-found", ["variant" => "note"])
				@slot("slot")
					Folio de la requisición: {{ $request->folio }}
				@endslot
			@endcomponent
		@endif
		@component('components.labels.title-divisor') Nueva solicitud @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2 class-project">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$options = collect();
					if (isset($request->idProject) && $request->idProject != "")
					{
						$projectData	=	App\Project::find($request->idProject);
						$options = $options->concat([['value'=>$projectData->idproyect, 'description'=>$projectData->proyectName, 'selected'=>'selected']]);
					}
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = "name=\"project_id\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"project_id\" data-validation=\"required\"";
					}
					$classEx = "removeselect js-projects";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2 select_father_wbs hidden class-wbs">
				@component('components.labels.label') Código WBS: @endcomponent
				@php
					$options = collect();
					if(isset($request) && $request->idProject != '' && $request->requestProject->codeWBS()->exists())
					{
						foreach(App\CatCodeWBS::where('project_id',$request->idProject)->where(function($q) use($request){ if($request->status == 2) { $q->where('status',1); } })->orderBy('code_wbs','asc')->get() as $code)
						{
							if(isset($request) && $request->requisition->code_wbs == $code->id)
							{
								$options = $options->concat([['value'=>$code->id, 'selected'=>'selected', 'description'=>$code->code_wbs]]);
							}
							else
							{
								$options = $options->concat([['value'=>$code->id, 'description'=>$code->code_wbs]]);
							}
						}
					}
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = "name=\"code_wbs\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"code_wbs\" data-validation=\"required\"";
					}
					$classEx = "removeselect js-code_wbs";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2 select_father_edt hidden" id="codeEDTContainer">
				@component('components.labels.label') Código EDT: @endcomponent
				@php
					$options = collect();
					if(isset($request))
					{
						foreach(App\CatCodeEDT::where('codewbs_id',$request->requisition->code_wbs)->get() as $edt)
						{
							$description = $edt->code.' ('.$edt->description.')';
							if(isset($request) && $request->requisition->code_edt == $edt->id)
							{
								$options = $options->concat([['value'=>$edt->id, 'selected'=>'selected', 'description'=>$description]]);
							}
							else
							{
								$options = $options->concat([['value'=>$edt->id, 'description'=>$description]]);
							}
						}
					}
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = "name=\"code_edt\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"code_edt\" data-validation=\"required\"";
					}
					$classEx = "removeselect js-code_edt";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Fecha en Obra: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="date_obra" data-validation="required" placeholder="Ingrese una fecha" readonly="readonly" value="{{ isset($request->requisition->date_obra) ? Carbon\Carbon::createFromFormat('Y-m-d',$request->requisition->date_obra)->format('d-m-Y'): '' }}"
						@if(isset($request) && $request->status != 2) disabled="disabled" @endif
					@endslot
					@slot('classEx')
						removeselect datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Título: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="title" 
						placeholder="Ingrese el título"
						data-validation="required" 
						value="{{ isset($request) ? $request->requisition->title : '' }}" 
						@if(isset($request) && $request->status != 2) 
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			@if(isset($request) && $request->status != 2)
				<div class="col-span-2">
					@component('components.labels.label')  No. @endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							name="number" 
							data-validation="required" 
							value="{{ $request->requisition->number }}" 
							disabled="disabled"
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				</div>
				@if($request->requisition->generated_number != '')
					<div class="col-span-2">
						@component('components.labels.label')  No. de requisición: @endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								name="number"  
								data-validation="required" 
								value="{{ $request->requisition->generated_number }}" 
								disabled="disabled"
							@endslot
							@slot('classEx')
								removeselect
							@endslot
						@endcomponent
					</div>
				@endif
			@endif
			<div class="col-span-2">
				@component('components.labels.label') Solicitante: @endcomponent
				@if(isset($request) && !isset($new_requisition) && $request->requisition->request_requisition != "" && $request->status != 2)
					@component('components.inputs.input-text')
						@slot('attributeEx')
							value="{{ $request->requisition->request_requisition }}" 
							readonly="readonly"
						@endslot
						@slot('classEx')
							removeselect
						@endslot
					@endcomponent
				@else
					@php
						$options = collect();
						if (isset($request->idRequest) && $request->idRequest != "")
						{
							$solicitantData	=	App\User::find($request->idRequest);
							$options	=	$options->concat([["value"	=>	$solicitantData->id,	"description"	=>	$solicitantData->fullname(),	"selected"	=>	"selected"]]);
						}
						if(isset($request) && $request->status != 2) 
						{
							$attributeEx = "name=\"request_requisition\" data-validation=\"required\" disabled=\"disabled\"";
						}
						else
						{
							$attributeEx = "name=\"request_requisition\" data-validation=\"required\"";
						}
						$classEx = "removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				@endif
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Tipo de Requisición: @endcomponent
				@php
					$options = collect();
					foreach(App\RequisitionType::where('status',1)->orderBy('name','asc')->get() as $rt)
					{
						if(isset($request) && $request->requisition->requisition_type == $rt->id)
						{
							$options = $options->concat([['value'=>$rt->id, 'selected'=>'selected', 'description'=>$rt->name]]);
						}
						else
						{
							$options = $options->concat([['value'=>$rt->id, 'description'=>$rt->name]]);
						}
					}
					if(isset($request) && $request->status != 2) 
					{
						$attributeEx = "name=\"requisition_type\" data-validation=\"required\" disabled=\"disabled\"";
					}
					else
					{
						$attributeEx = "name=\"requisition_type\" data-validation=\"required\"";
					}
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, "classEx" => "removeselect"])
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Prioridad: @endcomponent
				@php
					$options = collect([
						['value'=>'0', 'description'=>'Baja', 'selected' => ''.((isset($request) && isset($request->requisition->urgent) && $request->requisition->urgent == "0") ? "selected" : "")],
						['value'=>'1', 'description'=>'Media', 'selected' => ''.((isset($request) && isset($request->requisition->urgent) && $request->requisition->urgent == "1") ? "selected" : "")],
						['value'=>'2', 'description'=>'Alta', 'selected' => ''.((isset($request) && isset($request->requisition->urgent) && $request->requisition->urgent == "2") ? "selected" : "")]
					]);
					if(isset($request) && $request->status != 2)
					{
						$attributeEx = "name=\"urgent\" data-validation=\"required\" disabled=\"disabled\"";
					} 
					else
					{
						$attributeEx = "name=\"urgent\" data-validation=\"required\"";
					}
					$classEx = "removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
				@endcomponent
			</div>
			<div class="col-span-2 subcontract-number @if(!isset($request)) hidden @else @if($request->requisition->requisition_type == 4) block @else hidden @endif @endif">
				@component('components.labels.label') Número de subcontrato: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="subcontract_number" 
						placeholder="Ingrese el número de subcontrato" 
						data-validation="required" 
						value="{{ isset($request) ? $request->requisition->subcontract_number : '' }}"
						@if(isset($request) && $request->status != 2) 
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 select-buy-rent @if(!isset($request)) hidden @else @if($request->requisition->requisition_type == 5) block @else hidden @endif @endif">
				@component('components.labels.label') Compra/Renta: @endcomponent
				@php
					$options = collect();
					$value = ["Compra" => "Compra","Renta" => "Renta"];

					foreach($value as $item => $description)
					{
						$options = $options->concat(
							[
								[
									"value"			=> $item,
									"description" 	=> $description,
									"selected"		=> (isset($request) && $request->requisition->buy_rent == $item ? "selected" : '')
								]
							]
						);
					}
					
					if(isset($request) && $request->status != 2)
					{
						$attributeEx = "name=\"buy_rent\" data-validation=\"required\" disabled=\"disabled\"";
					} 
					else
					{
						$attributeEx = "name=\"buy_rent\" data-validation=\"required\"";
					}
					$classEx = "removeselect";
				@endphp
				@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
			</div>
			<div class="col-span-2 select-validity @if(!isset($request)) hidden @else @if($request->requisition->requisition_type == 5 && $request->requisition->buy_rent == 'Renta') block @else hidden @endif @endif">
				@component('components.labels.label') Vigencia: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						name="validity" 
						placeholder="Ingrese la vigencia" 
						data-validation="required" 
						value="{{ isset($request) ? $request->requisition->validity : '' }}" 
						@if(isset($request) && $request->status != 2) 
							disabled="disabled" 
						@endif
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			@if(isset($request) && $request->status == 17)
				<div class="w-full col-span-2 mb-4 flex justify-center">
					@component('components.labels.label')
						@component('components.buttons.button',[
							"buttonElement" => "a",
							"variant"       => "dark-red",
						])
							@slot('attributeEx') 
								title="Exportar a Excel" 
								href="{{ route('requisition.export',$request->folio) }}"
							@endslot
							@slot('label') 
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
					@component('components.buttons.button',[
						"label"         => "Exportar a PDF",
						"buttonElement" => "a",
						"variant"       => "dark-red",
						"attributeEx"   => "titlet=\"Exportar a PDF\" href=\"".route('requisition.export.pdf',$request->folio)."\""
					])
					@endcomponent
				</div>
			@endif
		@endcomponent
		@if(isset($request) && $request->requestRequisition()->exists() && !isset($new_requisition))
			@component('components.labels.title-divisor') 
				RESUMEN DE SOLICITUDES GENERADAS 
				@slot('classEx')
					pb-4
				@endslot
			@endcomponent
			@foreach($request->requestRequisition as $requestGenerated)
				@if($requestGenerated->purchases()->exists())
					@php
						$modelTable    = 
						[
							[
								"Tipo de solicitud: ", $requestGenerated->requestkind->kind
							],
							[
								"Folio: ", $requestGenerated->folio
							],
							[
								"Proveedor: ", isset($requestGenerated->purchases->first()->provider->businessName) ? $requestGenerated->purchases->first()->provider->businessName : ""
							]
						];
					@endphp
					@component("components.templates.outputs.table-detail", 
					[
						"modelTable" => $modelTable, 
						"title"      => "Detalles de la Solicitud"
					])
					@endcomponent
					@php
						$modelHead =
						[
							[
								["value" => "Cantidad"],
								["value" => "Unidad"],
								["value" => "Descripci&oacute;n"],
								["value" => "Precio Unitario"],
								["value" => "Subtotal"],
								["value" => "IVA"],
								["value" => "Impuesto Adicional"],
								["value" => "Retenciones"],
								["value" => "Importe"]
							]
						];
						$modelBody = [];
						foreach($requestGenerated->purchases->first()->detailPurchase as $detail)
						{
							$body= 
							[
								[
									"content" =>["label" => $detail->quantity]
								],
								[
									"content" =>["label" => $detail->unit]
								],
								[
									"content" =>["label" => htmlentities($detail->description)]
								],
								[
									"content" =>["label" => "$".number_format($detail->unitPrice,2)]
								],
								[
									"content" =>["label" => "$".number_format($detail->subtotal,2)]
								],
								[
									"content" =>["label" => "$".number_format($detail->tax,2)]
								]
								,
								[
									"content" =>["label" => "$".number_format($detail->taxes->sum('amount'),2)]
								],
								[
									"content" =>["label" => "$".number_format($detail->retentions->sum('amount'),2)]
								],
								[
									"content" =>["label" => "$".number_format($detail->amount,2)]
								]
							];
						}
					@endphp
					<div class="py-4">
						@component('components.tables.table',[
							"modelHead" 			=> $modelHead,
							"modelBody" 			=> $modelBody
						])
							@slot('classEx')
								text-center
							@endslot
						@endcomponent
					</div>
					@if(Auth::user()->module->where('id',29)->count()>0)
						<div class="w-ful flex justify-center">
							@component('components.buttons.button', [
								"buttonElement" => "a",
								"variant"       => "success",
								"attributeEx"   => "href=\"".route('purchase.follow.edit',$requestGenerated->folio)."\"",
								"label"         => "Ver Solicitud #".$requestGenerated->folio
							])
							@endcomponent
						</div>
					@endif
				@endif
				@if($requestGenerated->refunds()->exists())
					@php
						$modelTable    = 
						[
							[
								"Tipo de solicitud: ", $requestGenerated->requestkind->kind
							],
							[
								"Folio: ", $requestGenerated->folio
							]
						];
					@endphp
					@component("components.templates.outputs.table-detail", 
					[
						"modelTable" => $modelTable, 
						"title"      => "Detalles de la Solicitud"
					]) 
					@endcomponent
					@php
						$modelHead =
						[
							[
								["value" => "Concepto"],
								["value" => "Clasificación del gasto"],
								["value" => "Tipo de Documento/No. Facturan"],
								["value" => "Fiscal"],
								["value" => "Subtotal"],
								["value" => "IVA"],
								["value" => "Impuesto Adicional"],
								["value" => "Importe"],
								["value" => "Documento(s)"]
							]
						];
						$modelBody = [];
						foreach($requestGenerated->refunds->first()->refundDetail as $refundDetail)
						{
							$body= 
							[
								[
									"content" =>["label" => $refundDetail->concept]
								],
								[
									"content" =>["label" => $refundDetail->account != "" ? $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.")" : ""]
								],
								[
									"content" =>["label" => $refundDetail->document]
								],
								[
									"content" =>["label" => $refundDetail->taxPayment==1 ? "Sí" : "No"]
								],
								[
									"content" =>["label" => "$".number_format($refundDetail->amount,2)]
								],
								[
									"content" =>["label" => "$".number_format($refundDetail->tax,2)]
								]
								,
								[
									"content" =>["label" => "$".number_format($refundDetail->taxes->sum('amount'),2)]
								],
								[
									"content" =>["label" => "$".number_format($refundDetail->sAmount,2)]
								]
							];
							$actions =
							[
								"content" => 
								[
								]
							];
							if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
							{
								foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
								{
									$date = new \DateTime($doc->date);
									$newdate = Carbon\Carbon::parse($date)->format('d-m-Y');
									array_push($actions["content"], [
										"kind"  => "components.buttons.button",
										"buttonElement" => "a",
										"variant" => "dark-red",
										"label" => $newdate,
										"attributeEx" => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('requisition.authorization.show',$request->folio)."\""
									]);
								}
							}
							else
							{
								array_push($actions["content"], ["label" => "---"]);
							}
						}
					@endphp
					@component('components.tables.table',[
						"modelHead" 			=> $modelHead,
						"modelBody" 			=> $modelBody
					])
						@slot('classEx')
							text-center
						@endslot
					@endcomponent
					@if(Auth::user()->module->where('id',29)->count()>0)
						<div class="w-ful flex justify-center">
							@component('components.buttons.button', [
								"buttonElement" => "a",
								"variant"       => "success",
								"attributeEx"   => "href=\"".route('refund.follow.edit',$requestGenerated->folio)."\"",
								"label"         => "Ver Solicitud #".$requestGenerated->folio
							])
							@endcomponent
						</div>
					@endif
				@endif
			@endforeach
		@endif
		@if(isset($request) && ($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 27 || $request->status == 17 || $request->status == 28))
			@if($request->requisition->requisition_type != 3)
				@component('components.labels.title-divisor') 
					CONCEPTOS
					@slot('classEx')
						pb-4
					@endslot
				@endcomponent
				<div class="flex flex-row justify-end">
					@component('components.labels.label')
						@component('components.buttons.button',["variant" => "success"])
							@slot('attributeEx')
								type       = "submit "
								formaction = "{{ route('requisition.export',$request->folio) }}"
								formmethod = "get"
								title = "Exportar a Excel"
							@endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span>
							@endslot
						@endcomponent
					@endcomponent
				</div>
				@php
					$usersVoting = App\User::leftJoin('user_has_modules','users.id','user_has_modules.user_id')
									->leftJoin('permission_projects','user_has_modules.iduser_has_module','permission_projects.user_has_module_iduser_has_module')
									->leftJoin('permission_reqs','user_has_modules.iduser_has_module','permission_reqs.user_has_module_id')
									->where('user_has_modules.module_id',276)
									->where('permission_projects.project_id',$request->idProject)
									->where('permission_reqs.requisition_type_id',$request->requisition->requisition_type)
									->get();
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
								$body_id	=	"body_art_material";
								break;
							case(2):
								$modelHead = 
								[
									["value" => "Descripción", "show" => true],
									["value" => "Nombre", "show" => true],
									["value" => "Categoría"],
									["value" => "Cant."],
									["value" => "Unidad"],
									["value" => "Periodo"]
								];
								$body_id="body_art_general_services";
								break;
							case(4):								
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" => "Cant."],
									["value" => "Unidad"]
									
								];
								$body_id="body_art_subcontract";
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
								$body_id="body_art_machine";
								break;
							case(6):
								$modelHead = 
								[
									["value" => "Nombre", "show" => true],
									["value" => "Descripción", "show" => true],
									["value" =>"Cant."],
									["value" =>"Unidad"],
								];
								$body_id = "body_art_comercial";
								break;
						}
						if(in_array($request->status,[3,4,5,17,27]))
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
						if($request->requisition->requisitionHasProvider()->exists())
						{
							foreach($request->requisition->requisitionHasProvider as $provider)
							{
								$modelHead[] = ["value" => "Precio Unitario"];
								$modelHead[] = ["value" => "Subtotal"];
								$modelHead[] = ["value" => "IVA"];
								$modelHead[] = ["value" => "Impuesto Adicional"];
								$modelHead[] = ["value" => "Retenciones"];
								$modelHead[] = ["value" => "Total"];
								$headersProvider = "<div>
									<input type=\"hidden\" class=\"provider_count\" value=\"".$provider->providerData->businessName."\">
									<input type=\"hidden\" name=\"idRequisitionHasProvider[]\" class=\"id_provider_secondary\" value=\"".$provider->id."\">";
									if($provider->documents()->exists())
									{
										$headersProvider .= "<button type=\"button\" class=\"btn btn-blue viewDocumentProvider\" data-id=\"".$provider->id."\" data-toggle=\"modal\" data-target=\"#viewDocumentProvider\"><span class=\"icon-search\"></span> Ver Documentos</button>";
									}
								$headersProvider .=	"</div>";
								$modelTable	=
								[
									"Tipo de Moneda"	=> [["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"Ingrese el tipo de moneda\" value=\"".$provider->type_currency."\""]],
									"Tiempo de Entrega (Opcional)"	=> [["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"Ingrese el tiempo de entrega\" value=\"".htmlentities($provider->delivery_time)."\""]],
									"Crédito Días (Opcional)"	=>	[["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"Ingrese el crédito\" value=\"".htmlentities($provider->credit_time)."\""]],
									"Garantía (Opcional)"	=> [["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"Ingrese la garantía\" value=\"".htmlentities($provider->guarantee)."\""]]
								];
								if(in_array($request->requisition->requisition_type, [1,5])) 
								{
									$modelTable["Partes de Repuesto (Opcional)"]	=	[["kind" => "components.inputs.input-text", "attributeEx" => "placeholder=\"Ingrese las partes de repuesto\" value=\"".htmlentities($provider->spare)."\""]];
								}
								$modelGroup[] =
								[
									"name"		=> "Proveedores",
									"id"		=> "providers",
									"colNumber"	=> 6,
									"content"	=> $headersProvider,
									"footer"	=> [["kind" => "components.templates.outputs.table-detail-single", "modelTable" => $modelTable]]
								];
								if(count($usersVoting)>0)
								{
									foreach($usersVoting as $user)
									{
										$modelHead[] = ['value' => $user->fullName()];
									}
									$modelGroup[] = 
									[
										"name"		=> "Votaciones",
										"id"		=> "voting",
										"colNumber"	=> count($usersVoting)
									];
								}
							}
						}
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
														"kind"	=> "components.labels.label",
														"label" => $detail->name
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
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
														"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
														"classEx"     => "t_category"
													],
													[
														"kind"	=> "components.labels.label",
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
														"kind"	=> "components.labels.label",
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
														"kind"	=> "components.labels.label",
														"label" => $detail->quantity
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => htmlentities($detail->measurement)
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->unit
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->exists_warehouse
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
														"kind"	=> "components.labels.label",
														"label" => $detail->name
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
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
														"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
														"classEx"     => "t_category"
													],
													[
														"kind"	=> "components.labels.label",
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
														"kind"	=> "components.labels.label",
														"label" => $detail->quantity
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->unit
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->period
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
														"kind"	=> "components.labels.label",
														"label" => $detail->name
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => htmlentities($detail->description)
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
														"kind"	=> "components.labels.label",
														"label" => $detail->quantity
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
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
														"kind"	=> "components.labels.label",
														"label" => $detail->name
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
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
														"attributeEx" => "type=\"hidden\" value=\"".$detail->category."\"",
														"classEx"     => "t_category"
													],
													[
														"kind"	=> "components.labels.label",
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
														"kind"	=> "components.labels.label",
														"label" => $detail->quantity
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => htmlentities($detail->measurement)
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->unit
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->brand
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->model
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->usage_time
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => $detail->exists_warehouse
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
														"kind"	=> "components.labels.label",
														"label" => $detail->name
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
														"label" => htmlentities($detail->description)
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
														"kind"	=> "components.labels.label",
														"label" => $detail->quantity
													]
												]
											],
											[
												"content" => 
												[
													[
														"kind"	=> "components.labels.label",
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
										$body[] = ["content" =>[["label" => $price != "" ? number_format($price->unitPrice,2) : 0]]];
										$body[] = ["content" =>[["label" => $price != "" ? number_format($price->subtotal,2) : 0]]];
										$body[] = ["content" =>[["label" => $price != "" && $price->typeTax == "no" ? "No" : $price != "" && $price->typeTax == "a" ? App\Parameter::where('parameter_name','IVA')->first()->parameter_value."%" : $price != "" && $price->typeTax == "b" ? App\Parameter::where('parameter_name','IVA2')->first()->parameter_value."%" : ""]]];
										$body[] = ["content" =>[["label" => $price != "" ? number_format($price->taxes,2) : 0]]];
										$body[] = ["content" =>[["label" => $price != "" ? number_format($price->retentions,2) : 0]]];
										$body[] = ["content" =>[["label" => $price != "" ? number_format($price->total,2) : 0]]];
										if(count($usersVoting)>0)
										{
											foreach($usersVoting as $user)
											{
												if($detail->votingProvider()->exists())
												{
													if($user->user_id != Auth::user()->id)
													{
														if($detail->votingProvider()->where('user_id',$user->user_id)->exists())
														{
															foreach($detail->votingProvider->where('user_id',$user->user_id) as $votingUser)
															{
																if(isset($provider->id) && $provider->id == $votingUser->idRequisitionHasProvider)
																{
																	$body[] =
																	[
																		"content" => 
																		[
																			[
																				"kind"        => "components.inputs.input-text",
																				"attributeEx" => "type=\"hidden\" value=\"".$votingUser->commentaries."\"",
																				"classEx"     => "view-comment"
																			],
																			[
																				"kind"        => "components.buttons.button",
																				"attributeEx" => "data-toggle=\"modal\" data-target=\"#viewComment\"",
																				"classEx"     => "btnCommentView",
																				"label"		  => "<span class=\"icon-search\"></span>"
																			],
																			[
																				"kind"        => "components.labels.label",
																				"attributeEx" => "style=\"color: rgb(17, 179, 81);font-size: 23px;\"",
																				"classEx"     => "request-validate",
																				"label"		  => "<span class=\"icon-checkmark\"></span>"
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
																				"kind"	=> "components.labels.label",
																				"label" => "---"
																			]
																		]
																	];
																}
															}
														}
														else
														{
															$body[] =
															[
																"content" => 
																[
																	[
																		"kind"	=> "components.labels.label",
																		"label" => "---"
																	]
																]
															];
														}
													}
													else
													{
														if($detail->votingProvider()->where('user_id',Auth::user()->id)->exists())
														{
															foreach($detail->votingProvider->where('user_id',Auth::user()->id) as $votingUser)
															{
																if(isset($provider->id) && $provider->id == $votingUser->idRequisitionHasProvider)
																{
																	$body[] =
																	[
																		"content" => 
																		[
																			[
																				"kind"        => "components.inputs.input-text",
																				"attributeEx" => "type=\"hidden\" value=\"".$votingUser->commentaries."\"",
																				"classEx"     => "view-comment"
																			],
																			[
																				"kind"        => "components.buttons.button",
																				"attributeEx" => "data-toggle=\"modal\" data-target=\"#viewComment\"",
																				"classEx"     => "btnCommentView",
																				"label"		  => "<span class=\"icon-search\"></span>"
																			],
																			[
																				"kind"        => "components.labels.label",
																				"attributeEx" => "style=\"color: rgb(17, 179, 81);font-size: 23px;\"",
																				"classEx"     => "request-validate",
																				"label"		  => "<span class=\"icon-checkmark\"></span>"
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
																				"kind"	=> "components.labels.label",
																				"label" => "---"
																			]
																		]
																	];
																}
															}
														}
														else
														{
															$body[] =
															[
																"content" => 
																[
																	[
																		"kind"	=> "components.labels.label",
																		"label" => "---"
																	]
																]
															];
														}
													}
												}
												else
												{
													if($user->user_id != Auth::user()->id)
													{
														$body[] =
														[
															"content" => 
															[
																[
																	"kind"	=> "components.labels.label",
																	"label" => "---"
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
																	"kind"	=> "components.labels.label",
																	"label" => "---"
																]
															]
														];
													}
												}
											}
										}
									}
								}
								$modelBody[] = $body;
							}
						}
					}
				@endphp
				@component('components.tables.table-provider',[
					"modelHead" 	=> $modelHead,
					"modelBody" 	=> $modelBody,
					"modelGroup"	=> $modelGroup
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
					<div class="flex justify-center px-6">
						<div>
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
								@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable, "classEx" => "employee-details"]) 
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
							"themeBody" => "striped"
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
							"themeBody" => "striped"
						])
							@slot('classEx')
								text-center employee-details
							@endslot
						@endcomponent
					</div>
				@else
					<div id="staff-table" @if(isset($request) && in_array($request->requisition->requisition_type,[1,2,4,5,6])) style="display: none;"  @elseif(!isset($request)) style="display: none;" @endif>
						<div class="w-full mx-3">
							@php
								$body 			= [];
								$modelBody		= [];
								$modelHead = ["Nombre", "CURP", "Puesto", "Acción"];
								
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
														"label" => $emp->curp
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
														"attributeEx" => "data-toggle=\"modal\" type=\"button\" data-target=\"#detailEmployee\""
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
		@endif
		@if((isset($request) && $request->status == 2) || !isset($request))
			<div id="alert_select_requisition" @if(isset($request) && $request->status == 2) style="display: none;" @endif>
				@component("components.labels.not-found", ["variant" => "note"])
					@slot("slot")
						Seleccione un tipo de requisición.
					@endslot
				@endcomponent
			</div>
			@include('administracion.requisicion.tipos.alta_material')
			@include('administracion.requisicion.tipos.alta_maquinaria')
			@include('administracion.requisicion.tipos.alta_subcontrato')
			@include('administracion.requisicion.tipos.alta_servicios_generales')
			@include('administracion.requisicion.tipos.alta_comercial')
			@include('administracion.requisicion.tipos.alta_personal')
		@endif
		@if( isset($request) && $request->status == 2 && $request->requisition->requisition_type == '03' && in_array($request->idProject,[124,126]) && $request->requisition->employees->count() > 0 && !isset($new_requisition))
			@component('components.labels.title-divisor') 
				FORMATO DE REQUISICIÓN DE PERSONAL
				@slot('classEx')
					pb-4
				@endslot
			@endcomponent
			<div class="flex flex-row justify-end">
				@component('components.labels.label')
					@component('components.buttons.button',["variant" => "success"])
						@slot('attributeEx')
							href="{{ route('requisition.personal',['id'=>$request->folio]) }}"
						@endslot
						@slot('label')
							Descargar formato
						@endslot
					@endcomponent
				@endcomponent
			</div>
		@endif
		@component('components.labels.title-divisor') DOCUMENTOS DE LA REQUISICIÓN @endcomponent
		@if(isset($request) && $request->requisition->documents()->exists() && !isset($new_requisition))
			<div class="mx-3">
				@php
					$body 		=	[];
					$modelBody	=	[];
					if(isset($request) && $request->status == 2)
					{
						$modelHead = ['Nombre', 'Archivo', 'Modificado Por', 'Fecha', 'Acciones'];
					}
					else
					{
						$modelHead = ['Nombre', 'Archivo', 'Modificado Por', 'Fecha'];
					}
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
									"label" => Carbon\Carbon::parse($doc->created)->format('d-m-Y')
								]
							]
						];
						if(isset($request) && $request->status == 2)
						{
							array_push($body, 
								[
									"content" =>
									[
										[
											"kind"          => "components.buttons.button",
											"variant"       => "red",
											"label"         => "<span class=\"icon-x\"></span>",
											"classEx"       => "delete-document",
											"attributeEx"	=>	"type=\"button\""
										]
									]
								]
							);
						}
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
					@slot('classExBody')
						request-validate
					@endslot
				@endcomponent
			</div>
		@else
			@component("components.labels.not-found", ["text" => "Sin documentos agregados"]) @endcomponent
		@endif
		@if(isset($request) && $request->idCheck != "" && !isset($new_requisition))
			@component('components.labels.title-divisor')
				DATOS DE REVISIÓN
			@endcomponent
			@component('components.tables.table-request-detail.container',['variant'=>'simple'])
				@php
					$modelTable = [];
					$modelTable["Revisó"] = $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name;
					$modelTable["Comentarios"] = $request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment);
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable, "classEx" => "employee-details"]) @endcomponent
			@endcomponent
		@endif
		@if(isset($request) && $request->idAuthorize != "" && !isset($new_requisition))
			@component('components.labels.title-divisor')
				DATOS DE AUTORIZACIÓN
			@endcomponent
			@component('components.tables.table-request-detail.container',['variant'=>'simple'])
				@php
					$modelTable = [];
					$modelTable["Autorizó"] = $request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name;
					$modelTable["Comentarios"] = $request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment);
				@endphp
				@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable, "classEx" => "employee-details"]) @endcomponent
			@endcomponent
		@endif
		@component('components.labels.title-divisor') CARGAR DOCUMENTOS @endcomponent
		<div id="documents">
			@component('components.containers.container-form')
				@if(isset($request) && !isset($new_requisition))
					@if(!in_array($option_id, [276, 231, 232]))
						@if($request->status != 17 && $request->status != 6 && $request->status != 7 && $request->status != 28)
							<div id="documents-requisition" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"> </div>
							<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
								@component('components.buttons.button', ["variant" => "warning"])
									@slot('attributeEx') type="button" name="addDocRequisition" id="addDocRequisition" @if($request->status == 1) disabled @endif @endslot
									<span class="icon-plus"></span>
									<span>Nuevo documento</span>
								@endcomponent
							</div>
							@if($request->status != 2)
								<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
									@component('components.buttons.button', ["variant" => "success"])
										@slot('attributeEx') type="submit" name="save" id="save" formaction="{{ route('requisition.upload-documents',$request->folio) }}" @if($request->status == 1) disabled @endif @endslot
										@slot('classEx') save @endslot
										<span class="icon-plus"></span>
										<span>CARGAR DOCUMENTOS</span>
									@endcomponent
								</div>
							@endif
						@else
							@component("components.labels.not-found") 
								@slot("text")
									No se encontraron documentos
								@endslot
							@endcomponent
						@endif
					@else
						@component("components.labels.not-found") 
							@slot("text")
								No se encontraron documentos
							@endslot
						@endcomponent
					@endif
				@else
					<div id="documents-requisition" class="col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6"> </div>
					<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
						@component('components.buttons.button', ["variant" => "warning"])
							@slot('attributeEx') type="button" name="addDocRequisition" id="addDocRequisition" @if(isset($globalRequests)) disabled @endif @endslot
							<span class="icon-plus"></span>
							<span>Nuevo documento</span>
						@endcomponent
					</div>
				@endif
			@endcomponent
		</div>
		<span id="spanDelete"></span>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@if(isset($request))
				@if($request->status == 2 && !isset($new_requisition))
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx') 
							type="submit" name="send"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto
						@endslot
						ENVIAR REQUISICIÓN
					@endcomponent
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx') 
							type="submit" name="save" id="save" formaction="{{ route('requisition.save-follow',$request->folio) }}"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto save
						@endslot
						GUARDAR CAMBIOS
					@endcomponent
				@endif
				@if (isset($new_requisition) && $new_requisition)
					@component("components.buttons.button",["variant" => "primary"])
						@slot('attributeEx') 
							type="submit" name="send"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto
						@endslot
						ENVIAR REQUISICIÓN
					@endcomponent
					@component("components.buttons.button",["variant" => "secondary"])
						@slot('attributeEx') 
							type="submit" name="save" id="save" formaction="{{ route('requisition.save') }}"
						@endslot
						@slot('classEx') 
							w-48 md:w-auto save
						@endslot
						GUARDAR CAMBIOS
					@endcomponent
				@endif
				@component("components.buttons.button",["variant" => "reset"])
					@slot('buttonElement')
						a
					@endslot 
					@slot('attributeEx') 
						@if(isset($option_id)) 
							href="{{ url(App\Module::find($option_id)->url) }}"
						@else 
							href="{{ url(App\Module::find($child_id)->url) }}"
						@endif
					@endslot
					@slot('classEx') 
						load-actioner w-48 md:w-auto text-center
					@endslot
					REGRESAR
				@endcomponent	
			@else
				@component("components.buttons.button",["variant" => "primary"])
					@slot('attributeEx') 
						type="submit" name="send"
					@endslot
					@slot('classEx') 
						w-48 md:w-auto
					@endslot
					ENVIAR REQUISICIÓN
				@endcomponent
				@component("components.buttons.button",["variant" => "secondary"])
					@slot('attributeEx') 
						type="submit" name="save" id="save" formaction="{{ route('requisition.save') }}"
					@endslot
					@slot('classEx') 
						w-48 md:w-auto save
					@endslot
					GUARDAR CAMBIOS
				@endcomponent
				@component('components.buttons.button', ["variant" => "reset"])
					@slot('attributeEx')
						type="reset" name="borra" value="BORRAR CAMPOS"
					@endslot
					@slot('classEx')
						btn-delete-form-requisition
					@endslot
					BORRAR CAMPOS
				@endcomponent
			@endif
		</div>
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				viewDocumentProvider
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
			@endslot
			@slot('classExBody')
				modal-view-document
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "classEx" => "closeViewDocument", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
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
				@component('components.labels.label', ["label" => "Comentario de votación"]) @endcomponent
				@component('components.inputs.text-area', ["attributeEx" => "readonly=\"readonly\" name=\"commentView\""]) @endcomponent
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
			@endslot
		@endcomponent
		@component('components.modals.modal', ["variant" => "large"])
			@slot('id')
				viewDetailPurchase
			@endslot
			@slot('attributeEx')
				tabindex="-1"
			@endslot
			@slot('modalBody')
			@endslot
			@slot('attributeExBody')
				id="detail_purchase"
			@endslot
			@slot('modalFooter')
				@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
			@endslot
		@endcomponent
		<div id="delete_employee"></div>
	@endcomponent
	@component("components.forms.form", ["attributeEx" => "id=\"form_employee\" method=\"post\""])
		@component("components.modals.modal",["variant" => "large"])
			@slot('id') addEmployee @endslot
			@slot('modalTitle')
				Agregar empleado
			@endslot
			@slot('modalBody')
				@include('administracion.requisicion.tipos.alta_empleado')
			@endslot
			@slot('modalFooter')
				@component("components.buttons.button",["variant" => "secondary"])
					@slot('attributeEx')
						type="submit" id="save_employee"
					@endslot
					Guardar
				@endcomponent
				@component("components.buttons.button",["variant" => "red"])
					@slot('attributeEx')
						type         = "button" 
						data-dismiss = "modal"
					@endslot
					Cerrar
				@endcomponent
			@endslot
		@endcomponent
	@endcomponent
	@component("components.modals.modal",["variant" => "large"])
		@slot('id') dataUrlModal @endslot
		@slot('modalBody')
			<center>
				<iframe id="frame" width="560" height="315" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</center>
		@endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
		@endslot
	@endcomponent
	@component("components.modals.modal",["variant" => "large"])
		@slot('id') detailEmployee @endslot
		@slot('modalTitle') Agregar empleado @endslot
		@slot('modalBody') @endslot
		@slot('classExBody') modal-employee @endslot
		@slot('modalFooter')
			@component('components.buttons.button', ["variant" => "red", "attributeEx" => "data-dismiss=\"modal\" type=\"button\"", "label" => "<span class='icon-x'></span> Cerrar"]) @endcomponent
		@endslot
	@endcomponent
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}" />
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script type="text/javascript" src="{{asset('js/jquery.mask.js')}}"></script>
	<script type="text/javascript">
		$('.cat-pm').attr('disabled',true);
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
					needFileName 	= 0;
					$('[name="realPathRequisition[]"]').each(function()
					{
						name = $(this).parents('.docs-p').find('.nameDocumentRequisition').val();
						path = $(this).val();
						
						if(name == "0" || path == "")
						{
							$(this).parents('.docs-p').find('.nameDocumentRequisition').addClass('error');
							needFileName = needFileName + 1;
						}
					});
					if(needFileName>0)
					{
						if($('#documents-requisition').find('.docs-p').length>0)
						{
							swal('', 'Debe seleccionar el tipo de documento y cargar un documento.', 'error');
							return false;
						}
					}
					else if($('.request-validate').length>0)
					{
						type_requisition = $('[name="requisition_type"] option:selected').val();

						if (type_requisition == 1)
						{
							conceptos = $('#body_art_material .tr').length;
						}
						else if(type_requisition == 2)
						{
							conceptos = $('#body_art_general_services .tr').length;
						}
						else if(type_requisition == 5)
						{
							conceptos = $('#body_art_machine .tr').length;
						}
						else if(type_requisition == 3)
						{
							conceptos = $('#list_employees .tr').length;
							if (conceptos > 0) 
							{
								flagEmployee = true;
								$('#list_employees .tr').each(function(i,v)
								{
									rq_qualified_employee 		= $(this).find('[name="rq_qualified_employee[]"]').val();
									rq_doc_birth_certificate	= $(this).find('[name="rq_doc_birth_certificate[]"]').val();
									rq_doc_proof_of_address		= $(this).find('[name="rq_doc_proof_of_address[]"]').val();
									rq_doc_nss					= $(this).find('[name="rq_doc_nss[]"]').val();
									rq_doc_ine					= $(this).find('[name="rq_doc_ine[]"]').val();
									rq_doc_curp					= $(this).find('[name="rq_doc_curp[]"]').val();
									rq_doc_rfc					= $(this).find('[name="rq_doc_rfc[]"]').val();
									rq_doc_cv					= $(this).find('[name="rq_doc_cv[]"]').val();
									rq_doc_proof_of_studies		= $(this).find('[name="rq_doc_proof_of_studies[]"]').val();
									rq_doc_professional_license	= $(this).find('[name="rq_doc_professional_license[]"]').val();
									rq_doc_requisition			= $(this).find('[name="rq_doc_requisition[]"]').val();
									if (rq_qualified_employee == "1" && (rq_doc_birth_certificate == "" || rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_curp == "" || rq_doc_rfc == "" || rq_doc_cv == "" || rq_doc_proof_of_studies == ""|| rq_doc_professional_license == "" || rq_doc_requisition == "")) 
									{
										flagEmployee = false;
										$(this).addClass('tr-red');
									}
									else if(rq_qualified_employee != "1" && (rq_doc_proof_of_address == "" || rq_doc_nss == "" || rq_doc_ine == "" || rq_doc_rfc == "" || rq_doc_requisition == ""))
									{
										flagEmployee = false;
										$(this).addClass('tr-red');
									}
									else
									{
										$(this).removeClass('tr-red');
									}
								});
								if (flagEmployee) 
								{
									swal("Cargando",
									{
										icon				: '{{ asset(getenv('LOADING_IMG')) }}',
										button				: false,
										closeOnClickOutside	: false,
										closeOnEsc			: false
									});
									return true;
								}
								else
								{
									swal('', 'Por favor anexe todos los documentos a los empleados marcados en rojo.', 'error');
									return false;
								}
							}
							else
							{
								swal('', 'Debe ingresar al menos un empleado', 'error');
								return false;
							}
						}
						else if(type_requisition == 4)
						{
							conceptos = $('#body_art_subcontract .tr').length;
						}
						else if(type_requisition == 6)
						{
							conceptos = $('#body_art_comercial .tr').length;
						}
						else
						{
							conceptos = 0;
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
			});
		}
		function dataEmployee()
		{
			$(document).on("change",function(e)
			{
				if($(this).val().length>1)
				{
					$(this).val($(this).val().slice(0,1)).trigger('change');
				}
			});
			generalSelect({ 'selector': '#cp', 'model': 2 });
			generalSelect({ 'selector': '[name=\"state\"]', 'model': 31 });
			generalSelect({ 'selector': '[name=\"work_state\"]', 'model': 31 });
			generalSelect({ 'selector': '.js-projects', 'model': 17, 'option_id': {{$option_id}} });
			generalSelect({ 'selector': '[name=\"work_account\"]', 'depends': '[name=\"work_enterprise\"]', 'model': 4 });
			generalSelect({	'selector': '[name=\"work_subdepartment\"]', 'model': 39});
			generalSelect({'selector': '.bank', 'model': 28});
			generalSelect({'selector': '[name=\"work_employer_register\"]', 'depends': '[name=\"work_enterprise\"]', 'model': 47});
			@php
				$selects = collect([
					[
						"identificator"          => "#tax_regime",
						"placeholder"            => "Seleccione el régimen fiscal",
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
					"identificator"          => "[name=\"work_payment_way\"]",
						"placeholder"            => "Seleccione la forma de pago",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_periodicity\"]",
						"placeholder"            => "Seleccione la periodicidad",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_status_employee\"]",
						"placeholder"            => "Seleccione el estatus",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"regime_employee\"]",
						"placeholder"            => "Seleccione el régimen",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_type_employee\"]",
						"placeholder"            => "Seleccione el tipo de trabajador",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_project\"]",
						"placeholder"            => "Seleccione un proyecto/contrato",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator" => "[name=\"work_place[]\"]",
						"placeholder"   => "Seleccione el lugar de trabajo",
						"language"		=> "es"
					],
					[
						"identificator"          => "[name=\"work_enterprise\"]",
						"placeholder"            => "Seleccione la empresa",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_enterprise_old\"]",
						"placeholder"            => "Seleccione la empresa",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_department\"]",
						"placeholder"            => "Seleccione un departamento",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_direction\"]",
						"placeholder"            => "Seleccione la dirección",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"work_status_imss\"]",
						"placeholder"            => "Seleccione el status de IMSS",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"computer_required\"]",
						"placeholder"            => "Seleccione uno",
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]);
				$script = view("components.scripts.selects",["selects" => $selects])->render();
			@endphp
			{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
			$('input[name="cp"]').numeric({ negative:false});
			$('input[name="work_sdi"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_net_income"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_complement"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_fonacot"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_infonavit_credit"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('input[name="work_infonavit_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('.clabe,.account,.card').numeric({ decimal: false, negative:false});
			$('input[name="clabe"]').numeric({ decimal: false, negative:false});
			$('input[name="account"]').numeric({ decimal: false, negative:false});
			$('input[name="card"]').numeric({ decimal: false, negative:false});
			$('input[name="work_alimony_discount"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative:false});
			$('[name="imss"]').mask('0000000000-0',{placeholder: "__________-_"});
			$('[name="work_income_date"],[name="work_imss_date"],[name="work_down_date"],[name="work_ending_date"],[name="work_reentry_date"],[name="work_income_date_old"]').datepicker({ dateFormat: "dd-mm-yy" });
		}
		$(document).ready(function()
		{
			dataEmployee();
			validation();
			$('.quantity,.exists_warehouse,.subtotal-art,.iva-art,.total-art,.quantity-art,[name="staff_min_salary"],[name="staff_max_salary"],[name="amount[]"]').numeric({ altDecimal: ".", decimalPlaces: 2,negative: false });
			$('[name="date_obra"]').datepicker({  dateFormat: "dd-mm-yy" });
			$('#separatorComaComercial').prop('checked',true);
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
			generalSelect({'selector': '[name=\"request_requisition\"]', 'model': 13});
			generalSelect({'selector': '.js-name', 'model': 53});
			@php
				$selects = collect([
					[
						"identificator"          => '[name=\"urgent\"],[name=\"account_id\"]', 
						"placeholder"            => "Seleccione uno", 
						"language"               => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".unit",
						"placeholder"            => "Seleccione la unidad", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".category", 
						"placeholder"            => "Seleccione una categoría", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"requisition_type\"]", 
						"placeholder"            => "Seleccione el tipo de requisición", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => "[name=\"category_id\"]", 
						"placeholder"            => "Seleccione la categoría", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '[name=\"phase\"]', 
						"placeholder"            => "Seleccione uno", 
						"language"				 => "es",
						"maximumSelectionLength" => "1",
						"tags"                   => "true"
					],
					[
						"identificator"          => "[name=\"buy_rent\"]", 
						"placeholder"            => "Seleccione uno", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '.cat-pm',
						"placeholder"            => "Seleccione un tipo", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => '.nameDocumentRequisition',
						"placeholder"            => "Seleccione el tipo de documento", 
						"language"				 => "es",
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			@if(isset($request) && $request->requisition->requisition_type == 3)						
				$( "#slider" ).slider(
				{
					animate	: "fast",
					max		: {{App\Parameter::where('parameter_name','MAX_SALARY')->first()->parameter_value}},
					min		: {{App\Parameter::where('parameter_name','MIN_SALARY')->first()->parameter_value}},
					range	: true,
					step	: 500,
					values	: [ $('input[name="minSalary"]').val(), $('input[name="maxSalary"]').val() ],
				});
				$('#timePair .time.start').timepicker(
				{
					'timeFormat'	: 'H:i',
					'step'			: 30,
					'maxTime'		: '22:00:00',
					'minTime'		: '05:00:00',
				});
				$('#timePair .time.end').timepicker(
				{
					'showDuration'	: true,
					'timeFormat'	: 'H:i',
					'step'			: 30,
					'maxTime'		: '22:00:00',
					'minTime'		: '05:00:00',
				});
				$('#timePair').datepair();
				@php
					$selects = collect([
						[
							"identificator"          => ".js-responsibilities",
							"placeholder"            => "Seleccione las responsabilidades",
							"language"               => "es",
							"maximumSelectionLength" => "1"
						],
						[
							"identificator"          => ".js-boss",
							"placeholder"            => "Seleccione el jefe inmediato",
							"language"				 => "es",
							"maximumSelectionLength" => "1"
						]
					]);
					$script = view("components.scripts.selects",["selects" => $selects])->render();
				@endphp
				{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
			@endif
			$(document).on('click','#add_art_material',function()
			{
				$('.edit-art-material').attr('disabled', false);
				div 				= $(this).parents('.form-material');
				quantity			= Number(div.find('.quantity').val());
				unit				= div.find('.unit option:selected').val();
				description			= div.find('.description').val();
				id_name				= div.find('.js-name option:selected').val();
				name				= div.find('.js-name option:selected').text();
				measurement			= div.find('.measurement').val();
				exists_warehouse	= div.find('.exists_warehouse').val();
				category 			= div.find('.category option:selected').text();
				id_category 		= div.find('.category option:selected').val();
				id_type_name 		= div.find('.cat-pm option:selected').val();
				type_name 			= div.find('.cat-pm option:selected').text();

				
				if (div.find('.cat-pm option:selected').val() == undefined) 
				{
					type = '';
				}
				else
				{
					type = div.find('.cat-pm option:selected').val();
				}
				
				
				$('.unit').parent('div').find(".help-block").remove();
				$('.cat-pm').parent('div').find(".help-block").remove();
				$('.category').parent('div').find(".help-block").remove();
				$('.js-name').parent('div').find(".help-block").remove();
				$('.part,.quantity,.unit,.js-name,.exists_warehouse,.description,.measurement').removeClass('error');
				if (description == "" || quantity == 0 || quantity == "" || unit == undefined || unit == "" || name == "undefined" || name == "" || exists_warehouse == "" || category == "" || category == undefined) 
				{
					if (description == "" || description == undefined)
					{
						$('.description').addClass('error');
					}
					if (quantity == 0 || quantity == "")
					{
						$('.quantity').addClass('error');
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass('error');
					}
					if (name == "" || name == "undefined")
					{
						$('.js-name').addClass('error');

						if($('.js-name').hasClass("error"))
						{
							if($('.js-name').parent('div').find(".help-block").length == 0)
							{
								$('.js-name').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					if (measurement == "" || measurement == undefined)
					{
						$('.measurement').addClass('error');
					}
					if (exists_warehouse == "")
					{
						$('.exists_warehouse').addClass('error');
					}
					if (category == "" || category == undefined)
					{
						$('.category').addClass('error');

						if($('.category').hasClass("error"))
						{
							if($('.category').parent('div').find(".help-block").length == 0)
							{
								$('.category').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					if (type_name == "" || type_name == undefined)
					{
						$('.cat-pm').addClass('error');

						if($('.cat-pm').hasClass("error"))
						{
							if($('.cat-pm').parent('div').find(".help-block").length == 0)
							{
								$('.cat-pm').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass('error');

						if($('.unit').hasClass("error"))
						{
							if($('.unit').parent('div').find(".help-block").length == 0)
							{
								$('.unit').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					swal('','Por favor ingrese todos los datos requeridos','error');
				}
				else
				{
					if (measurement == "" || measurement == undefined || measurement == "undefined") 
					{
						measurement = "";
					}
					@php
						$modelHead = ["Categoría", "Tipo", "Cant.", "Medida", "Unidad", "Nombre", "Descripción", "Existencia en Almacén", ""];
						$modelBody = [
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_category",
											"attributeEx" => "type=\"hidden\" name=\"category[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_type",
											"attributeEx" => "type=\"hidden\" name=\"type[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_quantity",
											"attributeEx" => "type=\"hidden\" name=\"quantity[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_measurement",
											"attributeEx" => "type=\"hidden\" name=\"measurement[]\" placeholder=\"0\""
										]
									]
								],
								[ 
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_unit",
											"attributeEx" => "type=\"hidden\" name=\"unit[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_name",
											"attributeEx" => "type=\"hidden\" name=\"name[]\" placeholder=\"Nombre\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_description",
											"attributeEx" => "type=\"hidden\" name=\"description[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_exists_warehouse",
											"attributeEx" => "type=\"hidden\" name=\"exists_warehouse[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.buttons.button",
											"variant" => "success",
											"label" => "<span class=\"icon-pencil\"></span>",
											"classEx" => "edit-art-material",
											"attributeEx" => "type=\"button\""
										],
										[
											"kind"  => "components.buttons.button",
											"variant" => "red",
											"label" => "<span class=\"icon-x delete-span\"></span>",
											"classEx" => "delete-art",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);
					tr.find('[name="category[]"]').val(id_category).parent().prepend(category);
					tr.find('[name="type[]"]').val(id_type_name).parent().prepend(type_name);
					tr.find('[name="quantity[]"]').val(quantity).parent().prepend(quantity);
					measurement = String(measurement).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="measurement[]"]').val(measurement).parent().prepend(measurement);
					tr.find('[name="unit[]"]').val(unit).parent().prepend(unit);
					tr.find('[name="name[]"]').val(id_name).parent().prepend(name);
					description = String(description).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
					tr.find('[name="description[]"]').val(description).parent().prepend(description);
					tr.find('[name="exists_warehouse[]"]').val(exists_warehouse).parent().prepend(exists_warehouse);
					$('#body_art_material').append(tr);
					$('.quantity,.unit,.description,.measurement').val('');
					$('.unit,.category,.js-name,.cat-pm').val(0).trigger('change');
					swal('','Concepto agregado','success');
				}
			})
			.on('click','#add_art_machine',function()
			{
				$('.edit-art-machine').attr('disabled', false);
				div 				= $(this).parents('.form-requisition');
				brand				= div.find('.brand').val();
				model				= div.find('.model').val();
				usage_time			= div.find('.usage-time').val();
				quantity			= Number(div.find('.quantity').val());
				id_unit				= div.find('.unit option:selected').val();
				unit				= div.find('.unit option:selected').text();
				description			= div.find('.description').val();
				id_name				= div.find('.js-name option:selected').val();
				name				= div.find('.js-name option:selected').text();
				measurement			= div.find('.measurement').val();
				exists_warehouse	= div.find('.exists_warehouse').val();
				category			= div.find('.category option:selected').text();
				id_category			= div.find('.category option:selected').val();
				
				$('.unit').parent('div').find(".help-block").remove();
				$('.category').parent('div').find(".help-block").remove();
				$('.js-name').parent('div').find(".help-block").remove();
				$('.part,.quantity,.unit,.model,.usage-time,.js-name,.exists_warehouse,.unit,.category,.js-name,.brand,.measurement,.description').removeClass('error');
				if (quantity == 0 || quantity == "" || unit == undefined || unit == "" || name == "undefined" || exists_warehouse == "" || category == "" || category == undefined || brand == "" || brand == undefined || model == "" || model == undefined || usage_time == "" || usage_time == undefined ) 
				{
					if (brand == "")
					{
						$('.brand').addClass('error');
					}
					if (model == "")
					{
						$('.model').addClass('error');
					}
					if (usage_time == "")
					{
						$('.usage-time').addClass('error');
					}
					if (quantity == 0 || quantity == "")
					{
						$('.quantity').addClass('error');
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass('error');
					}
					if (name == "" || name == "undefined")
					{
						$('.js-name').addClass('error');
					}
					if (measurement == "" || measurement == undefined)
					{
						$('.measurement').addClass('error');
					}
					if (exists_warehouse == "")
					{
						$('.exists_warehouse').addClass('error');
					}
					if (description == "")
					{
						$('.description').addClass('error');
					}
					if (category == "" || category == undefined)
					{
						$('.category').addClass('error');
					}
					swal('','Por favor ingrese todos los datos requeridos','error');
				}
				else
				{
					if (measurement == "" || measurement == undefined || measurement == "undefined") 
					{
						measurement = "";
					}
					@php 
						$modelHead = ["Categoría", "Cant.", "Medida", "Unidad", "Nombre", "Descripción", "Marca", "Modelo", "Tiempo de Utilización", "Existencia en Almacén", ""];
						$modelBody = [
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "category",
											"label"	=> "",	
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_category",
											"attributeEx" => "type=\"hidden\" name=\"category[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "quantity",
											"label" => "", 
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_quantity",
											"attributeEx" => "type=\"hidden\" name=\"quantity[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "measurement",
											"label"	=> "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_measurement",
											"attributeEx" => "type=\"hidden\" name=\"measurement[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "l_unit",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_unit",
											"attributeEx" => "type=\"hidden\" name=\"unit[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "name",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_name",
											"attributeEx" => "type=\"hidden\" name=\"name[]\" placeholder=\"Nombre\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "description",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_description",
											"attributeEx" => "type=\"hidden\" name=\"description[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "brand",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_brand",
											"attributeEx" => "type=\"hidden\" name=\"brand[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "model",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_model",
											"attributeEx" => "type=\"hidden\" name=\"model[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "usage_time",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_usage_time",
											"attributeEx" => "type=\"hidden\" name=\"usage_time[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind" => "components.labels.label",
											"classEx" => "exists_warehouse",
											"label" => "",
										],
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_exists_warehouse",
											"attributeEx" => "type=\"hidden\" name=\"exists_warehouse[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.buttons.button",
											"variant" => "success",
											"label" => "<span class=\"icon-pencil\"></span>",
											"classEx" => "edit-art-machine",
											"attributeEx" => "type=\"button\""
										],
										[
											"kind"  => "components.buttons.button",
											"variant" => "red",
											"label" => "<span class=\"icon-x delete-span\"></span>",
											"classEx" => "delete-art",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true
						])->render();
						$table 	= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $table));
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					maq = $(table);
					maq.find(".t_category").val(id_category);
					maq.find(".category").text(category);
					maq.find(".t_quantity").val(quantity);
					maq.find(".quantity").text(quantity);
					maq.find(".t_measurement").val(measurement);
					maq.find(".measurement").text(measurement);
					maq.find(".t_unit").val(id_unit);
					maq.find(".l_unit").text(unit);
					maq.find(".t_name").val(id_name);
					maq.find(".name").text(name);
					maq.find(".t_description").val(description);
					maq.find(".description").text(description);
					maq.find(".t_brand").val(brand);
					maq.find(".brand").text(brand);
					maq.find(".t_model").val(model);
					maq.find(".model").text(model);
					maq.find(".t_usage_time").val(usage_time);
					maq.find(".usage_time").text(usage_time);
					maq.find(".t_exists_warehouse").val(exists_warehouse);
					maq.find(".exists_warehouse").text(exists_warehouse);
					

					$('#body_art_machine').append(maq);
					$('.unit').parent('div').find(".help-block").remove();
					$('.category').parent('div').find(".help-block").remove();
					$('.quantity,.unit,.description,.model,.brand,.usage-time,.measurement').val('');
					$('.unit,.category,.js-name,.cat-pm').val(0).trigger('change');
					swal('','Concepto agregado','success');
				}
			})
			.on('click','#add_art_subcontract',function()
			{
				$('.edit-art-subcontract').attr('disabled', false);
				div 		= $(this).parents('.form-requisition');
				quantity	= Number($('.quantity-art').val());
				unit	    = $('.unit option:selected').val();
				name	    = $('.name-art').val();
				description	= $('.description-art').val();
				$('.unit').parent('div').find(".help-block").remove();
				$('.quantity-art,.unit,.name-art,.description-art').removeClass('error');
				if (quantity == 0 || quantity == "" || unit == "" || name == "" || description == "")
				{
					if (quantity == 0 || quantity == "")
					{
						$('.quantity-art').addClass('error');
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass("error");
					}
					if (name == "")
					{
						$('.name-art').addClass('error');
					}
					if (description == "")
					{
						$('.description-art').addClass('error');
					}
					swal('','Por favor ingrese todos los datos requeridos','error');
				}
				else
				{
					@php 
						$modelHead = ["Cant.", "Unidad", "Nombre", "Descripción", ""];
						$modelBody = [
							[
								"classEx" => "tr",
								[
									"classEx" => "quantity",
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_quantity",
											"attributeEx" => "type=\"hidden\" name=\"quantity[]\""
										]
									]
								],
								[ 
									"classEx" => "unit",
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_unit",
											"attributeEx" => "type=\"hidden\" name=\"unit[]\" placeholder=\"0\""
										]
									]
								],
								[
									"classEx" => "name",
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_name",
											"attributeEx" => "type=\"hidden\" name=\"name[]\" placeholder=\"Nombre\""
										]
									]
								],
								[
									"classEx" => "description",
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_description",
											"attributeEx" => "type=\"hidden\" name=\"description[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.buttons.button",
											"variant" => "success",
											"label" => "<span class=\"icon-pencil\"></span>",
											"classEx" => "edit-art-subcontract",
											"attributeEx" => "type=\"button\""
										],
										[
											"kind"  => "components.buttons.button",
											"variant" => "red",
											"label" => "<span class=\"icon-x delete-span\"></span>",
											"classEx" => "delete-art",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					row = $(table);
					row.find('[name="quantity[]"]').val(quantity);
					row.find('[name="quantity[]"]').parent().prepend(quantity);
					row.find('[name="unit[]"]').val(unit);
					row.find('[name="unit[]"]').parent().prepend(unit);
					row.find('[name="name[]"]').val(name);
					row.find('[name="name[]"]').parent().prepend(name);
					row.find('[name="description[]"]').val(description);
					row.find('[name="description[]"]').parent().prepend(description);
					$('#body_art_subcontract').append(row);
					$('.quantity-art').val('');
					$('.name-art').val('');
					$('.description-art').val('');
					$('.unit').val(0).trigger('change');
					swal('','Concepto agregado','success');
				}
			})
			.on('click','#add_art_general_services',function()
			{
				$('.edit-art-services').attr('disabled', false);
				div 		= $(this).parents('.form-requisition');
				quantity	= Number(div.find('.quantity-art').val());
				unit		= div.find('.unit').val();
				name		= div.find('.name-art').val();
				description	= div.find('.description-art').val();
				period		= div.find('.period-art').val();
				category	= div.find('.category option:selected').text();
				id_category	= div.find('.category option:selected').val();
				$('.unit').parent('div').find(".help-block").remove();
				$('.category').parent('div').find(".help-block").remove();
				$('.quantity-art,.unit,.name-art,.description-art,.period-art,.category').removeClass('error');
				if (quantity == 0 || quantity == "" || unit == "" || name == "" || description == "" || period == "" || category == "" || category == undefined)
				{
					if (quantity == 0 || quantity == "")
					{
						$('.quantity-art').addClass('error');
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass('error');
						if($('.unit').hasClass("error"))
						{
							if($('.unit').parent('div').find(".help-block").length == 0)
							{
								$('.unit').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					if (name == "")
					{
						$('.name-art').addClass('error');
					}
					if (description == "")
					{
						$('.description-art').addClass('error');
					}
					if (period == "")
					{
						$('.period-art').addClass('error');
					}
					if (category == "" || category == undefined)
					{
						$('.category').addClass('error');

						if($('.category').hasClass("error"))
						{
							if($('.category').parent('div').find(".help-block").length == 0)
							{
								$('.category').parent('div').append('<span class="help-block form-error">Este campo es obligatorio</span>');
							}
						}
					}
					swal('','Por favor ingrese todos los datos requeridos','error');
				}
				else
				{
					@php 
						$modelHead = ["Categoría", "Cant.", "Unidad", "Nombre", "Descripción", "Periodo", ""];
						$modelBody = [
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_category",
											"attributeEx" => "type=\"hidden\" name=\"category[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_quantity",
											"attributeEx" => "type=\"hidden\" name=\"quantity[]\""
										]
									]
								],
								[ 
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_unit",
											"attributeEx" => "type=\"hidden\" name=\"unit[]\" placeholder=\"0\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_name",
											"attributeEx" => "type=\"hidden\" name=\"name[]\" placeholder=\"Nombre\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_description",
											"attributeEx" => "type=\"hidden\" name=\"description[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.inputs.input-text",
											"classEx" => "t_period",
											"attributeEx" => "type=\"hidden\" name=\"period[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"  => "components.buttons.button",
											"variant" => "success",
											"label" => "<span class=\"icon-pencil\"></span>",
											"classEx" => "edit-art-services",
											"attributeEx" => "type=\"button\""
										],
										[
											"kind"  => "components.buttons.button",
											"variant" => "red",
											"label" => "<span class=\"icon-x delete-span\"></span>",
											"classEx" => "delete-art",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true
						])->render();
					@endphp
					$('.unit').parent('div').find(".help-block").remove();
					$('.category').parent('div').find(".help-block").remove();
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);
					tr.find('[name="category[]"]').val(id_category).parent().prepend(category);
					tr.find('[name="quantity[]"]').val(quantity).parent().prepend(quantity);
					tr.find('[name="unit[]"]').val(unit).parent().prepend(unit);
					tr.find('[name="name[]"]').val(name).parent().prepend(name);
					tr.find('[name="description[]"]').val(description).parent().prepend(description);
					tr.find('[name="period[]"]').val(period).parent().prepend(period);
					$('#body_art_general_services').append(tr);
					$('.quantity-art').val('');
					$('.name-art').val('');
					$('.description-art').val('');
					$('.period-art').val('');
					$('.unit,.category').val(0).trigger('change');
					swal('','Concepto agregado','success');
				}
			})
			.on('click','#add_art_comercial',function()
			{
				$('.edit-art-comercial').attr('disabled', false);
				div         = $(this).parents('.form-requisition');
				quantity 	= Number(div.find('.quantity-art').val());
				unit	    = div.find('.unit').val();
				name	    = div.find('.name-art').val();
				description = div.find('.description-art').val();
				$('.unit').parent('div').find(".help-block").remove();
				$('.quantity-art,.unit,.name-art,.description-art').removeClass('error');
				if (quantity == 0 || quantity == "" || unit == "" || name == "" || description == "")
				{
					if (quantity == 0 || quantity == "")
					{
						$('.quantity-art').addClass('error');
					}
					if (unit == "" || unit == undefined)
					{
						$('.unit').addClass('error');
					}
					if (name == "")
					{
						$('.name-art').addClass('error');
					}
					if (description == "")
					{
						$('.description-art').addClass('error');
					}
					swal('','Por favor ingrese todos los datos requeridos','error');
				}
				else
				{
					@php 
						$modelHead = ["Cant.", "Unidad", "Nombre", "Descripción", "Acciones"];
						$modelBody = [
							[ "classEx" => "table-row",
								[
									"classEx" => "quantity",
									"content" => 
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx"		=> "t_quantity",
											"attributeEx"	=> "type=\"hidden\" name=\"quantity[]\""
										]
									]
								],
								[ 
									"classEx" => "unit",
									"content" => 
									[
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "t_unit",
											"attributeEx"	=> "type=\"hidden\" name=\"unit[]\" placeholder=\"0\""
										]
									]
								],
								[
									"classEx" => "name",
									"content" => 
									[
										[
											"kind"  		=> "components.inputs.input-text",
											"classEx" 		=> "t_name",
											"attributeEx"	=> "type=\"hidden\" name=\"name[]\" placeholder=\"Nombre\""
										]
									]
								],
								[
									"classEx" => "description",
									"content" => 
									[
										[
											"kind"  		=> "components.inputs.input-text",
											"classEx" 		=> "t_description",
											"attributeEx"	=> "type=\"hidden\" name=\"description[]\""
										]
									]
								],
								[
									"content" => 
									[
										[
											"kind"		=> "components.buttons.button",
											"variant"	=> "success",
											"label"		=> "<span class=\"icon-pencil\"></span>",
											"classEx"	=> "edit-art-comercial",
											"attributeEx" => "type=\"button\""
										],
										[
											"kind"		=> "components.buttons.button",
											"variant"	=> "red",
											"label"		=> "<span class=\"icon-x delete-span\"></span>",
											"classEx"	=> "delete-art",
											"attributeEx" => "type=\"button\""
										]
									]
								]
							]
						];
						$table = view('components.tables.alwaysVisibleTable',[
							"modelHead" => $modelHead,
							"modelBody" => $modelBody,
							"noHead"    => true
						])->render();
					@endphp
					table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
					tr = $(table);
					tr.find('[name="quantity[]"]').val(quantity);
					tr.find('[name="quantity[]"]').parent().prepend(quantity);
					tr.find('[name="unit[]"]').val(unit);
					tr.find('[name="unit[]"]').parent().prepend(unit);
					tr.find('[name="name[]"]').val(name);
					tr.find('[name="name[]"]').parent().prepend(name);
					tr.find('[name="description[]"]').val(description);
					tr.find('[name="description[]"]').parent().prepend(description);
					$('#body_art_comercial').append(tr);
					$('.quantity-art').val('');
					$('.name-art').val('');
					$('.description-art').val('');
					$('.unit').val(0).trigger('change');
					swal('','Concepto agregado','success');
				}
			})
			.on('click','.edit-art-comercial',function()
			{
				$('.edit-art-comercial').attr('disabled', true);
				id = $(this).parents('.tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				tr				= $(this).parents('.tr')
				t_part			= tr.find('.t_part').val();
				t_unit			= tr.find('.t_unit').val();
				t_name			= tr.find('.t_name').val();
				t_quantity		= tr.find('.t_quantity').val();
				t_description	= tr.find('.t_description').val();	
				$('.quantity-art').val(t_quantity);
				$('.unit').val(t_unit).trigger('change');
				$('.name-art').val(t_name);
				$('.description-art').val(t_description);
				$(this).parents('.tr').remove();
			})
			.on('change','.fiscal_folio,.ticket_number,.timepath,.amount,.datepath',function()
			{
				$('.datepath').each(function(i,v)
				{
					row          = 0;
					first_fiscal		= $(this).parents('.docs-p').find('.fiscal_folio');
					first_ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
					first_monto			= $(this).parents('.docs-p').find('.amount');
					first_timepath		= $(this).parents('.docs-p').find('.timepath');
					first_datepath		= $(this).parents('.docs-p').find('.datepath');
					first_name_doc		= $(this).parents('.docs-p').find('.nameDocumentRequisition option:selected').val();
					$('.datepath').each(function(j,v)
					{
						scnd_fiscal		= $(this).parents('.docs-p').find('.fiscal_folio');
						scnd_ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
						scnd_monto		= $(this).parents('.docs-p').find('.amount');
						scnd_timepath	= $(this).parents('.docs-p').find('.timepath');
						scnd_datepath	= $(this).parents('.docs-p').find('.datepath');
						scnd_name_doc	= $(this).parents('.docs-p').find('.nameDocumentRequisition option:selected').val();
						scnd_doc = $(this).parents('.docs-p').find('.datepath').val();
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
			.on('click','#export_catalogs',function(e)
			{
				e.preventDefault();
				object = $(this);
				action = object.attr('formaction');
				form   = $('#container-alta').attr('action',action);
				$('.remove').removeAttr('data-validation');
				$('.removeselect').removeAttr('required');
				$('.removeselect').removeAttr('data-validation');
				$('.request-validate').removeClass('request-validate');
				form.submit();
			})
			.on('click','#upload_file,#save',function(e)
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
								ticket_number	= $(this).parents('.docs-p').find('.ticket_number');
								fiscal_folio	= $(this).parents('.docs-p').find('.fiscal_folio');
								timepath		= $(this).parents('.docs-p').find('.timepath');
								amount			= $(this).parents('.docs-p').find('.amount');
								datepath		= $(this).parents('.docs-p').find('.datepath');
								ticket_number.removeClass('error').removeClass('valid');
								fiscal_folio.removeClass('error').removeClass('valid');
								timepath.removeClass('error').removeClass('valid');
								amount.removeClass('error').removeClass('valid');
								datepath.removeClass('error').removeClass('valid');
								$(data).each(function(i,d)
								{
									if (d == fiscal_folio.val() || d == ticket_number.val()) 
									{
										ticket_number.removeClass('valid').addClass('error')
										fiscal_folio.removeClass('valid').addClass('error');
										timepath.removeClass('valid').addClass('error');
										amount.removeClass('valid').addClass('error');
										datepath.removeClass('valid').addClass('error');
										flag = true;
									}
									else
									{
										ticket_number.removeClass('error').addClass('valid')
										fiscal_folio.removeClass('error').addClass('valid');
										timepath.removeClass('error').addClass('valid');
										amount.removeClass('error').addClass('valid');
										datepath.removeClass('error').addClass('valid');
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
					@if(!isset($request) || (isset($request) && $request->status == 2))
						inputValues  = [];
						selectValues = [];
						checkFile    = 0;
						requisition_type = $('[name="requisition_type"] option:selected').val();
						if (requisition_type == 1) 
						{
							tableName		= "#material-table";
							checkFile		= $('[name="csv_file_material"]').val();
							inputValues		= [".quantity", ".measurement", ".description", ".exists_warehouse"];
							selectValues	= [".category", ".cat-pm", ".unit", ".js-name"];
						}
						else if(requisition_type == 2)
						{
							tableName		= "#general-services-table";
							checkFile		= $('[name="csv_file_service"]').val();
							inputValues		= [".quantity-art", ".name-art", ".period-art", ".description-art"];
							selectValues	= [".category", ".unit"];
						}
						else if(requisition_type == 4)
						{
							tableName		= "#subcontract-table";
							checkFile		= $('[name="csv_file_subcontract"]').val();
							inputValues		= [".quantity-art", ".name-art", ".description-art"];
							selectValues	= [".unit"];
						}
						else if(requisition_type == 5)
						{
							tableName		= "#machine-table";
							checkFile		= $('[name="csv_file_machine"]').val();
							inputValues		= [".quantity", ".measurement", ".model", ".exists_warehouse", ".brand", ".usage-time", ".description"];
							selectValues	= [".category", ".unit", ".js-name"];
						}
						else if(requisition_type == 6)
						{
							tableName		= "#comercial-table";
							checkFile		= $('[name="csv_file_comercial"]').val();
							inputValues		= [".quantity-art", ".name-art", ".description-art"];
							selectValues	= [".unit"];
						}
						else if(requisition_type == 3)
						{
							$('[name="project_id"], [name="code_wbs"]').removeClass('error').parent().find('.form-error').remove();
							project_id = $('[name="project_id"] option:selected').val();
							flagWBS = false;
							if (project_id != undefined) 
							{
								$.each(generalSelectProject,function(i,v)
								{
									if(project_id == v.id)
									{
										if(v.flagWBS != null)
										{
											flagWBS = true;
										}
									}
								});
								wbs_id = $('[name="code_wbs"] option:selected').val();
								if (flagWBS && wbs_id == undefined)
								{
									$('[name="code_wbs"]').addClass('error').parent().append('<span class="form-error">Este campo es obligatorio</span>');
									swal('','Seleccione los campos que son obligatorios,','warning');
									return false;
								}
							}
							else
							{
								$('[name="project_id"]').addClass('error').parent().append('<span class="form-error">Este campo es obligatorio</span>');
								swal('','Seleccione los campos que son obligatorios,','warning');
								return false;
							}
						}
						else if(requisition_type == undefined)
						{
							swal('','Seleccione un tipo de requisición','warning');
							return false;
						}
						bool = true;
						for(i = 0; i<inputValues.length; i++)
						{
							if($(tableName).find(inputValues[i]).val()!='' && i!=3)
							{
								bool = false;
							}
						}
						for(i = 0; i<selectValues.length; i++)
						{
							if($(tableName).find(selectValues[i]).val().length>0)
							{
								bool = false;
							}
						}
						if(bool == false)
						{
							swal('','Tiene un concepto sin agregar.','warning');
							return false;
						}	
						idElement = object.attr('id');
						if (checkFile.length == 0 && idElement=="upload_file")
						{
							object.attr('type', 'button');
							swal('','Cargue un archivo para continuar','warning');
						}
						else
						{
							action = object.attr('formaction');
							form   = $('#container-alta').attr('action',action);
							$('.remove').removeAttr('data-validation');
							$('.removeselect').removeAttr('required');
							$('.removeselect').removeAttr('data-validation');
							$('.request-validate').removeClass('request-validate');
							form.submit();
						}
					@else
						action = object.attr('formaction');
						form   = $('#container-alta').attr('action',action);
						$('.remove').removeAttr('data-validation');
						$('.removeselect').removeAttr('required');
						$('.removeselect').removeAttr('data-validation');
						$('.request-validate').removeClass('request-validate');
						form.submit();
					@endif
				}
			})
			.on('click','.delete-art',function()
			{
				id = $(this).parents('.tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				$(this).parents('.tr').remove();
				swal('','Concepto eliminado','success');
			})
			.on('click','.edit-art-material',function()
			{
				$('.edit-art-material').attr('disabled', true);

				id = $(this).parents('.tr').find('.id').val();

				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				tr = $(this).parents('.tr')
				t_category         = tr.find('.t_category').val();
				t_part             = tr.find('.t_part').val();
				t_quantity         = tr.find('.t_quantity').val();
				t_measurement      = tr.find('.t_measurement').val();
				t_unit             = tr.find('.t_unit').val();
				t_name             = tr.find('.t_name').val();
				t_description      = tr.find('.t_description').val();
				t_exists_warehouse = tr.find('.t_exists_warehouse').val();
				t_type 			   = tr.find('.t_type').val();
				
				$('.category').val(t_category).trigger('change');
				
				$('.measurement').val(t_measurement);
				id = t_category;
				var typeTable = null;
				type = $('[name="requisition_type"] option:selected').val();
				if(type == 1)
				{
					typeTable = 'material-table';
				}
				else if(type == 2)
				{
					typeTable = 'general-services-table';
				}
				else if(type == 5)
				{
					typeTable = 'machine-table';
				}
				else if(type == 6)
				{
					typeTable = 'comercial-table';
				}
				$.ajax(
				{
					type   : 'post',
					url    : '{{ route("requisition.unit") }}',
					data   : {'rq': type, 'category' : id, 't_unit': t_unit},
					success: function(data)
					{
						$('#'+typeTable+' .unit').html(data);
						$('.unit').val(t_unit).trigger('change');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.unit').val('').trigger('change');
					}
				});
				$('.js-name').val(t_name).trigger('change');
				$('.cat-pm').val(t_type).trigger('change');
				$('.part').val(t_part);
				$('.quantity').val(t_quantity);
				$('.description').val(t_description);
				$('.exists_warehouse').val(t_exists_warehouse);
				if (t_type != "") 
				{
					$('.cat-pm-container').show();
					$('.cat-pm').attr('disabled',false);
				}
				else
				{
					$('.cat-pm').trigger('change').val(null);
					$('.cat-pm-container').hide();
				}
				$(this).parents('.tr').remove();
			})
			.on('click','.edit-art-machine',function()
			{
				$('.edit-art-machine').attr('disabled', true);
				id = $(this).parents('.tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				tr = $(this).parents('.tr')
				t_category			= tr.find('.t_category').val();
				t_part				= tr.find('.t_part').val();
				t_quantity			= tr.find('.t_quantity').val();
				t_measurement		= tr.find('.t_measurement').val();
				t_unit				= tr.find('.t_unit').val();
				t_name				= tr.find('.t_name').val();
				t_description		= tr.find('.t_description').val();
				t_exists_warehouse	= tr.find('.t_exists_warehouse').val();
				t_brand				= tr.find('.t_brand').val();
				t_model				= tr.find('.t_model').val();
				t_usage_time		= tr.find('.t_usage_time').val();
				$('.category').val(t_category).trigger('change');
				$('.measurement').val(t_measurement).trigger('change');
				id = t_category;
				var typeTable = null;
				type = $('[name="requisition_type"] option:selected').val();
				if(type == 1)
				{
					typeTable = 'material-table';
				}
				else if(type == 2)
				{
					typeTable = 'general-services-table';
				}
				else if(type == 5)
				{
					typeTable = 'machine-table';
				}
				else if(type == 6)
				{
					typeTable = 'comercial-table';
				}
				$.ajax(
				{
					type   : 'post',
					url    : '{{ route("requisition.unit") }}',
					data   : {'rq': type, 'category' : id},
					success: function(data)
					{
						$('#'+typeTable+' .unit').html(data);
						$('.unit').val(t_unit).trigger('change');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.unit').val('').trigger('change');
					}
				});
				$('.js-name').val(t_name).trigger('change');
				$('.part').val(t_part);
				$('.quantity').val(t_quantity);
				$('.description').val(t_description);
				$('.exists_warehouse').val(t_exists_warehouse);
				$('.brand').val(t_brand);
				$('.model').val(t_model);
				$('.usage-time').val(t_usage_time);
				$(this).parents('.tr').remove();
			})
			.on('click','.edit-art-subcontract',function()
			{
				$('.edit-art-subcontract').attr('disabled', true);
				id = $(this).parents('.tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				tr = $(this).parents('.tr')
				t_part			= tr.find('.t_part').val();
				t_unit			= tr.find('.t_unit').val();
				t_name			= tr.find('.t_name').val();
				t_quantity		= tr.find('.t_quantity').val();
				t_description	= tr.find('.t_description').val();	
				$('.quantity-art').val(t_quantity);
				$('.unit').val(t_unit).trigger('change');
				$('.name-art').val(t_name);
				$('.description-art').val(t_description);
				$(this).parents('.tr').remove();
			})
			.on('click','.edit-art-services',function()
			{
				$('.edit-art-services').attr('disabled', true);
				id = $(this).parents('.tr').find('.id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				tr = $(this).parents('.tr');
				t_part			= tr.find('.t_part').val();
				t_unit			= tr.find('.t_unit').val();
				t_name			= tr.find('.t_name').val();
				t_quantity		= tr.find('.t_quantity').val();
				t_description	= tr.find('.t_description').val();
				t_period		= tr.find('.t_period').val();	
				t_category 		= tr.find('.t_category').val();
				$('.category').val(t_category).trigger('change');
				$('.quantity-art').val(t_quantity);
				id = t_category;
				var typeTable = null;
				type = $('[name="requisition_type"] option:selected').val();
				if(type == 1)
				{
					typeTable = 'material-table';
				}
				else if(type == 2)
				{
					typeTable = 'general-services-table';
				}
				else if(type == 5)
				{
					typeTable = 'machine-table';
				}
				else if(type == 6)
				{
					typeTable = 'comercial-table';
				}
				$.ajax(
				{
					type   : 'post',
					url    : '{{ route("requisition.unit") }}',
					data   : {'rq': type, 'category' : id},
					success: function(data)
					{
						$('#'+typeTable+' .unit').html(data);
						$('.unit').val(t_unit).trigger('change');
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('.unit').val('').trigger('change');
					}
				});
				$('.name-art').val(t_name);
				$('.description-art').val(t_description);
				$('.period-art').val(t_period);
				$(this).parents('.tr').remove();
			})
			.on('change','.files',function(e)
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
			.on('click','[name="send"]',function (e)
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
								ticket_number	= $(this).parents('.docs-p-l').find('.ticket_number');
								fiscal_folio	= $(this).parents('.docs-p-l').find('.fiscal_folio');
								timepath		= $(this).parents('.docs-p-l').find('.timepath');
								amount			= $(this).parents('.docs-p-l').find('.amount');
								datepath		= $(this).parents('.docs-p-l').find('.datepath');
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
					checkFile = "";
					form = object.parents('form');
					requisition_type = $('[name="requisition_type"] option:selected').val();

					if (requisition_type == 1) 
					{
						checkFile = $('[name="csv_file_material"]').val();
					}
					else if(requisition_type == 2)
					{
						checkFile = $('[name="csv_file_service"]').val();
					}
					else if(requisition_type == 3)
					{
						form.submit();
					}
					else if(requisition_type == 4)
					{
						checkFile = $('[name="csv_file_subcontract"]').val();
					}
					else if(requisition_type == 5)
					{
						checkFile = $('[name="csv_file_machine"]').val();
					}
					else if(requisition_type == 6)
					{
						checkFile = $('[name="csv_file_comercial"]').val();
					}
					if (checkFile != "") 
					{
						swal({
							title: "Tiene un archivo sin cargar ¿Desea enviar la solicitud?",
							text : "Los registros que se encuentren en el archivo no serán cargados, primero deberá guardar los cambios en el sistema y comprobar que se hayan subido los registros de su archivo.",
							icon: "warning",
							buttons: ["Cancelar","OK"],
						})
						.then((isConfirm) =>
						{
							if(isConfirm)
							{
								form.submit();
							}
						});
					}
					else
					{
						form.submit();
					}
				}
			})
			.on('change','select[name="enterprise_id"]',function()
			{
				$('select[name="account_id"]').empty();
			})
			.on('click','#addDocRequisition',function()
			{ 
				type = $('[name="requisition_type"] option:selected').val();
				if (type != undefined) 
				{
					$('#documents-requisition').removeClass('hidden');
					if (type == 1 || type == 5) 
					{
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
								"classExInput" => "inputDoc pathActionerRequisition",
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
															"attributeEx"	=> "name=\"timepath[]\" step=\"60\" value=\"00:00\" placeholder=\"Seleccione hora\" readonly=\"readonly\" data-validation=\"required\""
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
					}
					else if(type == 2 || type == 3 || type == 4 || type == 6)
					{
						if(type == 4)
						{
							@php
								$options = collect(
									[
										["value"=>"Acta Constitutiva", "description"=>"Acta Constitutiva"], 
										["value"=>"Poder del representante legal", "description"=>"Poder del representante legal"], 
										["value"=>"Identificación oficial", "description"=>"Identificación oficial"], 
										["value"=>"RFC", "description"=>"RFC"], 
										["value"=>"Cedula Fiscal", "description"=>"Cedula Fiscal"], 
										["value"=>"Domicilio", "description"=>"Domicilio"], 
										["value"=>"CV", "description"=>"CV"], 
										["value"=>"Revisión técnica", "description"=>"Revisión técnica"], 
										["value"=>"Anexos", "description"=>"Anexos"], 
										["value"=>"Pólizas de Fianza", "description"=>"Pólizas de Fianza"]
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
									"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
									"classExInput" => "inputDoc pathActionerRequisition",
									"classExDelete" => "delete-doc"
								])->render();
							@endphp
							newDoc = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
						}
						else if(type == 3)
						{
							@php
								$options = collect(
									[
										["value"=>"Cotización", "description"=>"Cotización"], 
										["value"=>"Ficha Técnica", "description"=>"Ficha Técnica"], 
										["value"=>"Control de Calidad", "description"=>"Control de Calidad"], 
										["value"=>"Contrato", "description"=>"Contrato"], 
										["value"=>"Factura", "description"=>"Factura"], 
										["value"=>"REQ. OC. FAC.", "description"=>"REQ. OC. FAC."], 
										["value"=>"Requisición de Personal", "description"=>"Requisición de Personal"], 
										["value"=>"Otro", "description"=>"Otro"]
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
									"attributeExInput" => "name=path accept=.pdf,.jpg,.png",
									"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
									"classExInput" => "inputDoc pathActionerRequisition",
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
																"attributeEx"	=> "name=\"datepath[]\" step=\"1\" placeholder=\"Seleccione la fecha\" readonly=\"readonly\" data-validation=\"required\""
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
						}
						else
						{
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
									"componentsExUp" => $labelSelect.$select." <div class=\"componentsEx\"></div>",
									"classExInput" => "inputDoc pathActionerRequisition",
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
																"attributeEx"	=> "name=\"fiscal_folio[]\" placeholder=\"Ingrese folio fiscal\" data-validation=\"required\""
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
						}
					}
					$('#documents-requisition').append(newDoc);
					$('[name="amount[]"]').numeric({ altDecimal: ".", decimalPlaces: 2, negative: false });
					$('.datepicker').datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
					$('.timepath').daterangepicker(
					{
						timePicker      : true,
						singleDatePicker: true,
						timePicker24Hour: true,
						autoApply       : true,
						locale          :
						{
							format       : 'HH:mm',
							"applyLabel" : "Seleccionar",
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
								"language"               => "es",
								"maximumSelectionLength" => "1"
							]
						]);
						$script = view("components.scripts.selects",["selects" => $selects])->render();
					@endphp
					{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
				}
				else
				{
					swal('','Seleccione primero un tipo de requisición','info');
				}
			})
			.on('change','.inputDoc.pathActionerRequisition',function(e)
			{
				target = e.currentTarget;
				filename     = $(this);
				uploadedName = $(this).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]');
				extention    = /\.jpg|\.png|\.jpeg|\.pdf/i;
				if(filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if(this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$('.btn_disable').attr('disabled', true);
					$('.disable-button').prop('disabled', true);
					$.ajax(
					{
						type       : 'post',
						url        : '{{ route("requisition.upload") }}',
						data       : formData,
						contentType: false,
						processData: false,
						success    : function(r)
						{
							if(r.error == 'DONE')
							{
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(target).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val(r.path);
								$(target).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(target).val('');
								$(target).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
							}
							$('.btn_disable').attr('disabled', false);	
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(target).val('');
							$(target).parent('.uploader-content').siblings('input[name="realPathRequisition[]"]').val('');
						}
					}).done(function() {
						$('.disable-button').prop('disabled', false);
					});
				}
			})
			.on('click','.delete-doc-requisition',function()
			{
				swal(
				{
					icon  : '{{ asset(getenv('LOADING_IMG')) }}',
					button: false
				});
				actioner     = $(this);
				uploadedName = $(this).parents('.docs-p').find('input[name="realPathRequisition[]"]');
				formData     = new FormData();
				formData.append(uploadedName.attr('name'),uploadedName.val());
				$.ajax(
				{
					type       : 'post',
					url        : '{{ route("requisition.upload") }}',
					data       : formData,
					contentType: false,
					processData: false,
					success    : function(r)
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					},
					error : function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('.docs-p').remove();
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
					type: 'popst',
					url : '{{ route("requisition.provider-documents.view") }}',
					data:
					{
						'id' : id,
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
			.on('click','.btnCommentView',function()
			{
				comment = $(this).parent('td').find('.view-comment').val();
				$('[name="commentView"]').val(comment);
			})
			.on('change','[name="project_id"]',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null)
				{
					$.each(generalSelectProject,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagWBS != null)
							{
								$('.select_father_wbs').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
							}
							else
							{
								$('.js-code_wbs, .js-code_edt').html('');
								$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_wbs, .js-code_edt').html('');
					$('.select_father_wbs, .select_father_edt').removeClass('block').addClass('hidden');
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
				uploadedName	= $(this).parents('.docs-p').find('input[name="realPathRequisition[]"]');
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
					},
					error		: function()
					{
						swal.close();
						actioner.parents('.docs-p').remove();
					}
				});
				$(this).parents('.docs-p').remove();
			})
			.on('change','[name="code_wbs"]',function()
			{
				id = $(this).find('option:selected').val();
				if (id != null)
				{
					$.each(generalSelectWBS,function(i,v)
					{
						if(id == v.id)
						{
							if(v.flagEDT != null)
							{
								$('.select_father_edt').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
							}
							else
							{
								$('.js-code_edt').html('');
								$('.select_father_edt').removeClass('block').addClass('hidden');
							}
						}
					});
				}
				else
				{
					$('.js-code_edt').html('');
					$('.select_father_edt').removeClass('block').addClass('hidden');
				}
			})
			.on('click','[data-toggle="modal"]',function()
			{
				url	= $(this).attr('data-url');

				$('#frame').attr('src',url);
			})
			.on('click','[data-dismiss="modal"]',function()
			{
				if ($('#list_employees tr').length > 0) 
				{
					$('#list_employees tr').each(function()
					{
						$(this).removeClass('active')
					});
				}
				datas = $('#form_employee').serializeArray();
				$.each(datas,function(i,input)
				{
					if (input.name != "qualified_employee") 
					{
						$('#form_employee').find('[name="'+input.name+'"]').val('');
						$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
						$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
						$('#form_employee').find('[name="'+input.name+'"]').parent('p').find('.form-error').remove();
						$('#form_employee').find('[name="'+input.name+'"]').parent('p').find('.help-block').remove();
						$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
					}
				});
				$('#form_employee').find('[name="employee_id"]').val('x');
				$('#form_employee').find('.uploader-content').removeClass('image_pdf');
				$('.doc_birth_certificate').empty().text('Sin documento');
				$('.doc_proof_of_address').empty().text('Sin documento');
				$('.doc_nss').empty().text('Sin documento');
				$('.doc_ine').empty().text('Sin documento');
				$('.doc_curp').empty().text('Sin documento');
				$('.doc_rfc').empty().text('Sin documento');
				$('.doc_cv').empty().text('Sin documento');
				$('.doc_proof_of_studies').empty().text('Sin documento');
				$('.doc_professional_license').empty().text('Sin documento');
				$('.doc_requisition').empty().text('Sin documento');
				$('#documents_employee tr.tr-remove').remove();
				$('#other_documents').empty();
				$('#frame').removeAttr('src');
			})
			.on('change','[name="requisition_type"]',function()
			{
				type = $('option:selected',this).val();
				if (type != undefined)
				{
					var cat       = null;
					var typeTable = null;
					if (type == 1)  // material
					{
						$('#separatorComaMaterial').prop('checked',true);
						$('#subcontract-table,#machine-table,#general-services-table,#comercial-table,#staff-table').hide();
						$('#material-table').stop(true,true).fadeIn();
						$('#alert_select_requisition').hide();
						$('.subcontract-number').addClass('hidden').removeClass('block');
						$('[name="nameDocumentRequisition[]"]').html('');
						$('[name="nameDocumentRequisition[]"]')
							.append($('<option value="Cotización">Cotización</option>'))
							.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
							.append($('<option value="Control de Calidad">Control de Calidad</option>'))
							.append($('<option value="Contrato">Contrato</option>'))
							.append($('<option value="Factura">Factura</option>'))
							.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
							.append($('<option value="Otro">Otro</option>'));
						$('[name="buy_rent"]').trigger('change').val(null);
						$('[name="validity"]').val('');
						$('.select-buy-rent,.select-validity').addClass('hidden').removeClass('block');
						cat = $('#material-table .category').val();
						typeTable = 'material-table';
						generalSelect({'selector': '.js-name', 'model': 53});
						@php
							$selects = collect([
								[
									"identificator"          => ".unit",
									"placeholder"            => "Seleccione una unidad",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".category",
									"placeholder"            => "Seleccione una categoría",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".cat-pm",
									"placeholder"            => "Seleccione un tipo",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
					}
					else if (type == 5) //maquinaria
					{
						$('#separatorComaMaquinaria').prop('checked',true);
						$('.subcontract-number').addClass('hidden').removeClass('block');
						$('#subcontract-table,#material-table,#general-services-table,#comercial-table,#staff-table').hide();
						$('#machine-table').stop(true,true).fadeIn();
						$('#alert_select_requisition').hide();
						
						$('[name="nameDocumentRequisition[]"]').html('');
						$('[name="nameDocumentRequisition[]"]')
							.append($('<option value="Cotización">Cotización</option>'))
							.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
							.append($('<option value="Control de Calidad">Control de Calidad</option>'))
							.append($('<option value="Contrato">Contrato</option>'))
							.append($('<option value="Factura">Factura</option>'))
							.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
							.append($('<option value="Otro">Otro</option>'));
						$('.select-buy-rent').removeClass('hidden').addClass('block');
						$('[name="buy_rent"]').trigger('change').val(null);
						$('[name="validity"]').val('');
						cat = $('#machine-table .category').val();
						typeTable = 'machine-table';
						@php
							$selects = collect([
								[
									"identificator"          => ".unit",
									"placeholder"            => "Seleccione una unidad",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".category",
									"placeholder"            => "Seleccione una categoría",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".cat-pm",
									"placeholder"            => "Seleecione un tipo",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => "[name=\"buy_rent\"]",
									"placeholder"            => "Seleccione uno",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
						generalSelect({'selector': '.js-name', 'model': 53});
					}
					else if(type == 2) // servicios generales
					{
						$('#separatorComaServicios').prop('checked',true);
						$('[name="buy_rent"]').trigger('change').val(null);
						$('[name="validity"]').val('');
						$('.select-buy-rent,.select-validity').addClass('hidden').removeClass('block');
						$('.subcontract-number').addClass('hidden').removeClass('block');
						$('#subcontract-table,#machine-table,#material-table,#comercial-table,#staff-table').hide();
						$('#general-services-table').stop(true,true).fadeIn();
						$('#alert_select_requisition').hide();
						$('[name="nameDocumentRequisition[]"]').html('');
						$('[name="nameDocumentRequisition[]"]')
							.append($('<option value="Cotización">Cotización</option>'))
							.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
							.append($('<option value="Control de Calidad">Control de Calidad</option>'))
							.append($('<option value="Contrato">Contrato</option>'))
							.append($('<option value="Factura">Factura</option>'))
							.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
							.append($('<option value="Otro">Otro</option>'));
						@php
							$selects = collect([
								[
									"identificator"          => ".unit", 
									"placeholder"            => "Seleccione uno", 
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".category",
									"placeholder"            => "Seleccione una categoría",
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								],
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
						cat = $('#general-services-table .category').val();
						typeTable = 'general-services-table';
					}
					else if(type == 6) // comercial
					{
						$('#separatorComaComercial').prop('checked',true);
						$('#general-services-table,#subcontract-table,#material-table,#machine-table,#staff-table').hide();
						$('#alert_select_requisition').hide();
						$('#comercial-table').stop(true,true).fadeIn();
						$('.subcontract-number').addClass('hidden').removeClass('block');
						$('[name="nameDocumentRequisition[]"]').html('');
						$('[name="nameDocumentRequisition[]"]')
							.append($('<option value="Cotización">Cotización</option>'))
							.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
							.append($('<option value="Control de Calidad">Control de Calidad</option>'))
							.append($('<option value="Contrato">Contrato</option>'))
							.append($('<option value="Factura">Factura</option>'))
							.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
							.append($('<option value="Otro">Otro</option>'));
						@ScriptSelect([
							"selects" => 
							[	
								[
									"identificator"          => ".unit", 
									"placeholder"			 => "Seleccione la unidad",
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								],
							]
						])
						@endScriptSelect
						@php
							$selects = collect([
								[
									"identificator"          => ".category",
									"placeholder"            => "Seleccione una categoría",
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								],
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
						cat = $('#comercial-table .category').val();
						typeTable = 'comercial-table';
					}
					else if(type == 3) // personal
					{ 
						$('#separatorComaPersonal').prop('checked',true);
						$('#comercial-table,#general-services-table,#subcontract-table,#material-table,#machine-table').hide();
						$('#alert_select_requisition').hide();
						$('#staff-table').stop(true,true).fadeIn();
						$('.subcontract-number').addClass('hidden').removeClass('block');
						$('[name="nameDocumentRequisition[]"]').html('');
							$('[name="nameDocumentRequisition[]"]')
								.append($('<option value="Cotización">Cotización</option>'))
								.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
								.append($('<option value="Control de Calidad">Control de Calidad</option>'))
								.append($('<option value="Contrato">Contrato</option>'))
								.append($('<option value="Factura">Factura</option>'))
								.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
								.append($('<option value="Requisición de Personal">Requisición de Personal</option>'))
								.append($('<option value="Otro">Otro</option>'));
						$( "#slider" ).slider(
						{
							animate	: "fast",
							max		: {{App\Parameter::where('parameter_name','MAX_SALARY')->first()->parameter_value}},
							min		: {{App\Parameter::where('parameter_name','MIN_SALARY')->first()->parameter_value}},
							range	: true,
							step	: 500,
							values	: [ $('input[name="minSalary"]').val(), $('input[name="maxSalary"]').val() ],
						});
						$('#timePair .time.start').timepicker(
						{
							'timeFormat'	: 'H:i',
							'step'			: 30,
							'maxTime'		: '22:00:00',
							'minTime'		: '05:00:00',
						});
						$('#timePair .time.end').timepicker(
						{
							'showDuration'	: true,
							'timeFormat'	: 'H:i',
							'step'			: 30,
							'maxTime'		: '22:00:00',
							'minTime'		: '05:00:00',
						});
						$('#timePair').datepair();
						@php
							$selects = collect([
								[
									"identificator"          => ".js-responsibilities",
									"placeholder"            => "Seleccione las responsabilidades",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								],
								[
									"identificator"          => ".js-boss",
									"placeholder"            => "Seleccione el jefe inmediato",
									"language"				 => "es",
									"maximumSelectionLength" => "1"
								]
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
					}
					else if(type == 4) // subcontrato
					{
						$('#separatorComaSubcontract').prop('checked',true);
						$('[name="buy_rent"]').trigger('change').val(null);
						$('[name="validity"]').val('');
						$('.select-buy-rent,.select-validity').addClass('hidden').removeClass('block');
						$('#material-table,#machine-table,#general-services-table,#comercial-table,#staff-table').hide();
						$('#subcontract-table').stop(true,true).fadeIn();
						$('#alert_select_requisition').hide();
						$('.subcontract-number').removeClass('hidden').addClass('block');
						@php
							$selects = collect([
								[
									"identificator"          => ".unit",
									"placeholder"            => "Seleccione una unidad",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								]
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
						typeTable = 'subcontract-table';
						$('[name="nameDocumentRequisition[]"]').html('');
						$('[name="nameDocumentRequisition[]"]')
							.append($('<option value="Cotización">Cotización</option>'))
							.append($('<option value="Ficha Técnica">Ficha Técnica</option>'))
							.append($('<option value="Control de Calidad">Control de Calidad</option>'))
							.append($('<option value="Contrato">Contrato</option>'))
							.append($('<option value="Factura">Factura</option>'))
							.append($('<option value="REQ. OC. FAC.">REQ. OC. FAC.</option>'))
							.append($('<option value="Otro">Otro</option>'));
					}
					else
					{
						$('#comercial-table,#general-services-table,#subcontract-table,#material-table,#machine-table,#staff-table').hide();
						$('[name="buy_rent"]').trigger('change').val(null);
						$('[name="validity"]').val('');
						$('.select-buy-rent,.select-validity,.subcontract-number').addClass('hidden').removeClass('block');
						$('#alert_select_requisition').show();
					}
					$('.unit').html('');
					if(type != 3)
					{
						$.ajax(
						{
							type   : 'post',
							url    : '{{ route("requisition.unit") }}',
							data   : {'rq': type, 'category' : cat},
							success: function(data)
							{
								$('#'+typeTable+' .unit').html(data);
							},
							error : function()
							{
								swal('','Sucedió un error, por favor intente de nuevo.','error');
							}
						});
					}
				}
				else
				{
					$('[name="buy_rent"]').trigger('change').val(null);
					$('[name="validity"]').val('');
					$('.select-buy-rent,.select-validity,.subcontract-number').addClass('hidden').removeClass('block');
					$('#comercial-table,#general-services-table,#subcontract-table,#material-table,#machine-table,#staff-table').hide();
					$('#alert_select_requisition').show();
				}
			})
			.on('select2:unselecting','[name="requisition_type"]', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Cambiar de Tipo de Requisición",
					text		: "Si cambia el tipo de requisición, todos los conceptos que ya se encontraban agregados serán eliminados",
					icon		: "warning",
					buttons		: ["Cancelar","OK"],
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$(this).val(null).trigger('change');
						$('#body_art_material tr').each(function()
						{
							id = $(this).find('.id').val();
							if (id != "x") 
							{
								deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
								$('#spanDelete').append(deleteID);
							}
						});
						$('#body_art_subcontract tr').each(function()
						{
							id = $(this).find('.id').val();
							if (id != "x") 
							{
								deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
								$('#spanDelete').append(deleteID);
							}
						});
						$('#body_art_general_services tr').each(function()
						{
							id = $(this).find('.id').val();
							if (id != "x") 
							{
								deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
								$('#spanDelete').append(deleteID);
							}
						});
						$('#body_art_machine tr').each(function()
						{
							id = $(this).find('.id').val();
							if (id != "x") 
							{
								deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
								$('#spanDelete').append(deleteID);
							}
						});
						$('#body_art_comercial tr').each(function()
						{
							id = $(this).find('.id').val();
							if (id != "x") 
							{
								deleteID = $('<input type="hidden" name="delete[]" value="'+id+'">');
								$('#spanDelete').append(deleteID);
							}
						});
						$('#body_art_material,#body_art_subcontract,#body_art_general_services,#body_art_comercial,#body_art_machine').empty();
					}
					else
					{
						swal.close();
					}
				});
			})
			.on('click','.delete-document',function()
			{
				swal({
					text : "¿Deseas eliminar el documento?",
					icon: "warning",
					buttons: ['Cancelar', 'Eliminar']
				})
				.then((isConfirm) =>
				{
					if(isConfirm)
					{
						$(this).parents('.tr').remove();
						toDelete = $('[name="to_delete"]').val();
						documentId = $(this).parents('.tr').find('[name="document-id[]"]').val();
						documentId = toDelete + documentId;
						$("[name='to_delete']").val(documentId+","); //concat values into to-delete input
					}
				});
			})
			.on('change','.category',function()
			{
				id = $('option:selected',this).val();
				if (id == 13)
				{
					$('.cat-pm-container').show();
					$('.cat-pm').attr('disabled',false);
					@php
						$selects = collect([
							[
								"identificator"          => ".cat-pm",
								"placeholder"            => "Seleccione un tipo",
								"language"               => "es",
								"maximumSelectionLength" => "1"
							]
						]);
						$script = view("components.scripts.selects",["selects" => $selects])->render();
					@endphp
					{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
				}
				else
				{
					$('.cat-pm').trigger('change').val(null);
					$('.cat-pm-container').hide();
				}
				var typeTable = null;
				type = $('[name="requisition_type"] option:selected').val();
				if(type == 1)
				{
					typeTable = 'material-table';
				}
				else if(type == 2)
				{
					typeTable = 'general-services-table';
				}
				else if(type == 5)
				{
					typeTable = 'machine-table';
				}
				else if(type == 6)
				{
					typeTable = 'comercial-table';
				}
				$.ajax(
				{
					type   : 'post',
					url    : '{{ route("requisition.unit") }}',
					data   : {'rq': type, 'category' : id},
					success: function(data)
					{
						$('#'+typeTable+' .unit').html(data);
					}
				});
			})
			.on('change','[name="buy_rent"]',function()
			{
				val = $('[name="buy_rent"] option:selected').val();
				if (val == undefined) 
				{
					$('.select-validity').addClass('hidden').removeClass('block');
				}
				else
				{
					if (val == "Renta") 
					{
						$('.select-validity').removeClass('hidden').addClass('block').show();
					}
					else
					{
						$('.select-validity').addClass('hidden').removeClass('block');
					}
				}
			})
			.on('slide slidestop','#slider',function()
			{
				values = $(this).slider('values');
				$('#minSalary').html('$ '+values[0].formatMoney(2));
				$('#maxSalary').html('$ '+values[1].formatMoney(2));
				$('input[name="minSalary"]').val(values[0]);
				$('input[name="maxSalary"]').val(values[1]);
			})
			.on('keyup','.time',function(e)
			{
				$(this).val('');
			})
			.on('click','[name="add_function"]',function()
			{
				functions	= $('[name="function"]').removeClass('error').val().trim();
				description	= $('[name="description_staff"]').removeClass('error').val().trim();
				if (functions == "" || description == "")
				{
					if(functions == "")
					{
						$('[name="function"]').addClass('error');
					}
					if(description == "")
					{
						$('[name="description_staff"]').addClass('error');
					}
					swal('', 'Por favor ingrese todos los datos requeridos', 'error');
				}
				else
				{
					tr_function	= $('<tr></tr>')
								.append($('<td></td>')
									.append(functions)
									.append($('<input readonly="true" class="function_id" type="hidden" value="x">'))
									.append($('<input readonly="true" class="input-table" type="hidden" name="tfunction[]"/>').val(functions))
								)
								.append($('<td></td>')
									.append(description)
									.append($('<input readonly="true" class="input-table" type="hidden" name="tdescr[]"/>').val(description))
								)
								.append($('<td></td>')
									.append($('<button class="delete-function btn btn-red" type="button"></button>')
										.append($('<span class="icon-x"></span>'))
									)
								);
					$('#body_functions').append(tr_function);
					$('[name="function"]').val("");
					$('[name="description_staff"]').val("");
				}
			})
			.on('click','[name="add_desirable"]',function()
			{
				desirable	= $('[name="desirable"]').removeClass('error').val().trim();
				description	= $('[name="d_description"]').removeClass('error').val().trim();
				if (desirable == "" || description == "")
				{
					if(desirable == "")
					{
						$('[name="desirable"]').addClass('error');
					}
					if(description == "")
					{
						$('[name="d_description"]').addClass('error');
					}
					swal('', 'Por favor ingrese todos los datos requeridos', 'error');
				}
				else
				{
					tr_desirable	= $('<tr></tr>')
								.append($('<td></td>')
									.append(desirable)
									.append($('<input readonly="true" class="desirable_id" type="hidden" value="x">'))
									.append($('<input readonly="true" class="input-table" type="hidden" name="tdesirable[]"/>').val(desirable))
								)
								.append($('<td></td>')
									.append(description)
									.append($('<input readonly="true" class="input-table" type="hidden" name="td_descr[]"/>').val(description))
								)
								.append($('<td></td>')
									.append($('<button class="delete-desirable btn btn-red" type="button"></button>')
										.append($('<span class="icon-x"></span>'))
									)
								);
					$('#body_desirables').append(tr_desirable);
					$('[name="desirable"]').val("");
					$('[name="d_description"]').val("");
				}
			})
			.on('click','.delete-function',function()
			{
				id = $(this).parents('tr').find('.function_id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete_functions[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				$(this).parents('tr').remove();
			})
			.on('click','.delete-desirable',function()
			{
				id = $(this).parents('tr').find('.desirable_id').val();
				if (id != "x") 
				{
					deleteID = $('<input type="hidden" name="delete_desirables[]" value="'+id+'">');
					$('#spanDelete').append(deleteID);
				}
				$(this).parents('tr').remove();
			})
			.on('change','[name="staff_max_salary"]',function()
			{
				min_salary = $('[name="staff_min_salary"]').val();
				max_salary = $(this).val();

				if (max_salary != "") 
				{
					if (Number(max_salary) == 0) 
					{
						swal('','El monto no puede ser 0','info');
						$(this).val('');
					}
					if (Number(min_salary) >0 && Number(max_salary)>0) 
					{
						if (Number(min_salary) > Number(max_salary)) 
						{
							swal('','El sueldo máximo no puede ser menor al sueldo mínimo','info');
							$(this).val('');
						}
					}
				}
			})
			.on('change','[name="staff_min_salary"]',function()
			{
				min_salary = $(this).val();
				max_salary = $('[name="staff_max_salary"]').val();

				if (min_salary != "") 
				{
					if (Number(min_salary) == 0) 
					{
						swal('','El monto no puede ser 0','info');
						$(this).val('');
					}

					if (Number(min_salary) >0 && Number(max_salary)>0) 
					{
						if (Number(min_salary) > Number(max_salary)) 
						{
							swal('','El sueldo máximo no puede ser menor al sueldo mínimo','info');
							$(this).val('');
						}
					}
				}
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
			.on('click','#btnAddEmployee',function()
			{
				generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
				$("#bodyEmployee .tr").each(function()
				{
					$(this).remove();
				});
				$("#bodyAlimony .tr").each(function()
				{
					$(this).remove();
				});
				$('#form_employee').find('#alimony').prop('checked',false);
				$(this).parents('table').find('tbody').stop(true,true).fadeOut();
				$('#accounts-alimony').stop(true,true).fadeOut();
				$('#infonavit-form').fadeOut();
				$('#form_employee').find('#yes_qualified').prop('checked',true);
				$('.docs-p').find('.uploader-content').removeClass('image_success');
				dataEmployee();
			})
			.on('change','[name="work_enterprise"]',function()
			{
				$('[name="work_account"]').html('');
				$('[name="work_employer_register"]').html('');
			})
			.on('input','.alias',function()
			{
				if($(this).val() != "")
				{
					$('.alias').addClass('valid').removeClass('error');
				}
				else
				{
					$('.alias').addClass('error').removeClass('valid');
				}
			})
			.on('click','#add-bank',function()
			{				
				alias       = $('.content-bank').find('.alias').val();
				bankid      = $('.content-bank').find('.bank').val();
				bankName    = $('.content-bank').find('.bank :selected').text();
				clabe       = $('.content-bank').find('.clabe').val();
				account     = $('.content-bank').find('.account').val();
				card        = $('.content-bank').find('.card').val();
				branch      = $('.content-bank').find('.branch_office').val();
				if(alias == "")
				{
					swal('', 'Por favor ingrese un alias', 'error');
					$('.alias').addClass('error');
				}
				else if(bankid.length>0)
				{
					if (card == "" && clabe == "" && account == "")
					{
						$('.card, .clabe, .account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if (alias == "")
					{
						$(".alias").addClass("error");
						swal("", "Debe ingresar todos los campos requeridos", "error");
					}
					else 
					{
						flag = false;
						$('#bodyEmployee .tr').each(function()
						{
							name_account 	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();
							if(clabe!= "" && name_clabe !="" &&clabe == name_clabe)
							{
								swal('','La CLABE ya se encuentra registrada para este empleado.','error');
								$('.clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada para este empleado.','error');
								$('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
						});
						if(!flag)
						{
							@php
								$modelBody	= [];
								$body		= [];
								$modelHead = 
								[
									"Alias",
									"Banco",
									"Clabe",
									"Cuenta",
									"Tarjeta",
									"Sucursal",
									"Acción"
								];
								$body = [
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "aliasC",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "aliasI",
												"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "beneficiaryI",
												"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\" value=\"\"",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"1\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "bankName",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "idEmployee",
												"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "bankI",
												"attributeEx"	=> "type=\"hidden\" name=\"bank[]\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "clabeC",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "clabeI",
												"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "accountC",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "accountI",
												"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "cardC",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "cardI",
												"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind" 		=> "components.labels.label",
												"classEx" 	=> "branchC",
												"label"		=> "",
											],
											[
												"kind" 			=> "components.inputs.input-text",
												"classEx" 		=> "branchI",
												"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
											],
										]
									],
									[
										"content" =>
										[
											"classEx" 	=>	"td",
											"content" =>
											[
												"kind"			=> "components.buttons.button",
												"classEx"		=> "delete-bank",
												"attributeEx"	=> "type=\"button\"",
												"label"			=> "<span class=\"icon-x\"></span>",
												"variant"		=> "dark-red",
											]
										]
									]
								];
								$modelBody[] = $body;
								$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead"	=> true,
								])->render();
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank  = $(table);
							bank  = rowColor('#bodyEmployee', bank);
							bank.find('div').each(function()
							{
								$(this).find(".aliasC").text(alias);
								$(this).find(".aliasI").val(alias);
								$(this).find(".bankName").text(bankName);
								$(this).find(".bankI").val(bankid);
								$(this).find(".clabeC").text(clabe != "" ? clabe : "---");
								$(this).find(".clabeI").val(clabe);
								$(this).find(".accountC").text(account != "" ? account : "---");
								$(this).find(".accountI").val(account);
								$(this).find(".cardC").text(card != "" ? card : "---");
								$(this).find(".cardI").val(card);
								$(this).find(".branchC").text(branch != "" ? branch : "---");
								$(this).find(".branchI").val(branch);
							})
							$('#bodyEmployee').append(bank);
							$('.card, .clabe, .account, .alias,.branch_office').removeClass('error').removeClass('valid').val('');
							$('.bank').val(0).trigger("change");
							$('#bank-data-register').parent().removeClass('hidden');
							$('#not-found-accounts').addClass('hidden');
						}
					}
				}
				else
				{
					swal('', 'Seleccione un banco, por favor', 'error');
					$('.bank').addClass('error');
				}
			})
			.on('click','#add-bank-alimony',function()
			{
				beneficiary	= $('.content-bank-alimony').find('.beneficiary').val();
				alias		= $('.content-bank-alimony').find('.alias').val();
				bankid		= $('.content-bank-alimony').find('.bank').val();
				bankName	= $('.content-bank-alimony').find('.bank :selected').text();
				clabe		= $('.content-bank-alimony').find('.clabe').val();
				account		= $('.content-bank-alimony').find('.account').val();
				card		= $('.content-bank-alimony').find('.card').val();
				branch		= $('.content-bank-alimony').find('.branch_office').val();

				if(alias == "" || beneficiary == "")
				{
					if(alias == "")
					{
						$(this).parents('.tr').find('.alias').addClass('error');
					}
					if(beneficiary == "")
					{
						$(this).parents('.tr').find('.beneficiary').addClass('error');
					}
					swal('', 'Por favor ingrese un beneficiario y un alias', 'error');	
				}
				else if(bankid.length>0)
				{
					if (card == "" && clabe == "" && account == "")
					{
						$('.card, .clabe, .account').removeClass('valid').addClass('error');
						swal('', 'Debe ingresar al menos un número de tarjeta, clabe o cuenta bancaria', 'error');
					}
					else if (alias == "")
					{
						$(".alias").addClass("error");
						swal("", "Debe ingresar todos los campos requeridos", "error");
					}
					else
					{
						flag = false;
						$('#bodyAlimony .tr').each(function()
						{
							name_account	= $(this).find('[name="account[]"]').val();
							name_clabe		= $(this).find('[name="clabe[]"]').val();
							name_bank		= $(this).find('[name="bank[]"]').val();

							if(clabe!= "" && name_clabe!= "" &&clabe == name_clabe)
							{
								swal('','La CLABE ya se encuentra registrada para este beneficiario.','error');
								$('clabe').removeClass('valid').addClass('error');
								flag = true;
							}
							if(account != "" && name_account != "" && account == name_account && bankid == name_bank)
							{
								swal('','El número de Cuenta ya se encuentra registrada para este beneficiario.','error');
								$('.acount').removeClass('valid').addClass('error');
								flag = true;
							}
						})
						if(!flag)
						{
							@php
								$body		= [];
								$modelBody	= [];
								$modelHead	= ["Beneficiario","Alias","Banco","Clabe","Cuenta","Tarjeta","Sucursal","Acción"];
								$body		= [
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "beneficiaryC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iBeneficiary",
												"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\"",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"2\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "aliasC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iAlias",
												"attributeEx"	=> "type=\"hidden\" name=\"alias[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "bankName",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "idEmployee",
												"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\" value=\"x\"",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iBank",
												"attributeEx"	=> "type=\"hidden\" name=\"bank[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "clabeC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iClabe",
												"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "accountC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iAccount",
												"attributeEx"	=> "type=\"hidden\" name=\"account[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "cardC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iCard",
												"attributeEx"	=> "type=\"hidden\" name=\"card[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"		=> "components.labels.label",
												"classEx"	=> "branchC",
												"label"		=> "",
											],
											[
												"kind"			=> "components.inputs.input-text",
												"classEx"		=> "iBranch",
												"attributeEx"	=> "type=\"hidden\" name=\"branch[]\"",
											],
										]
									],
									[
										"content" =>
										[
											[
												"kind"			=> "components.buttons.button",
												"classEx"		=> "delete-bank",
												"variant"		=> "dark-red",
												"attributeEx"	=> "type=\"button\"",
												"label"			=> "<span class=\"icon-x\"></span>"
											],
										]
									],
								];
								$modelBody[] = $body;
								$table = view("components.tables.alwaysVisibleTable",[
									"modelHead" => $modelHead,
									"modelBody" => $modelBody,
									"noHead" 	=> true
								])->render();
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							bank = $(table);
							bank = rowColor('#bank-data-register-alimony #bodyAlimony', bank);
							bank.find('div').each(function()
							{
								$(this).find(".beneficiaryC").text(beneficiary);
								$(this).find(".iBeneficiary").val(beneficiary);
								$(this).find(".aliasC").text(alias);
								$(this).find(".iAlias").val(alias);
								$(this).find(".bankName").text(bankName);
								$(this).find(".iBank").val(bankid);
								$(this).find(".clabeC").text(clabe != "" ? clabe : "---");
								$(this).find(".iClabe").val(clabe);
								$(this).find(".accountC").text(account != "" ? account : "---");
								$(this).find(".iAccount").val(account);
								$(this).find(".cardC").text(card != "" ? card : "---");
								$(this).find(".iCard").val(card);
								$(this).find(".branchC").text(branch != "" ? branch : "---");
								$(this).find(".iBranch").val(branch);
							})
							$('#bank-data-register-alimony #bodyAlimony').append(bank);
							$('.bank, .card, .clabe, .account, .alias, .beneficiary, .branch_office').removeClass('error').removeClass('valid').val('');
							$('.bank').val(0).trigger("change"); 
							$('#bank-data-register-alimony').parent().removeClass('hidden');
							$('#not-found-accounts-alimony').addClass('hidden');
						}
					}	
				}
				else
				{
					swal('', 'Seleccione un banco, por favor', 'error');
					$('.bank').addClass('error');
				}
			})
			.on('click','.delete-bank', function()
			{
				$(this).parents('.tr').remove();
			})
			.on('change','#infonavit',function()
			{
				if($(this).is(':checked'))
				{
					$('.infonavit-container').stop(true,true).fadeIn();
				}
				else
				{
					$('.infonavit-container').stop(true,true).fadeOut();
				}
				@php
					$selects = collect(
						[
							[
								"identificator"				=> "[name=\"work_infonavit_discount_type\"]",
								"placeholder"				=> "Seleccione el tipo de descuento",
								"language"					=> "es",
								"maximumSelectionLength"	=> "1",
							]
						]
					);
				@endphp	
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('change','#alimony',function()
			{
				if($(this).is(':checked'))
				{
					$('.alimony-container').addClass('block').removeClass('hidden');
					$(this).parents('table').find('tbody').stop(true,true).fadeIn();
					$('#accounts-alimony').stop(true,true).fadeIn();
					@php
						$selects = collect([
							[
								"identificator"          => "[name=\"work_alimony_discount_type\"]",
								"placeholder"            => "Seleccione el tipo de descuento",
								"language"               => "es",
								"maximumSelectionLength" => "1"
							]
						]);
						$script = view("components.scripts.selects",["selects" => $selects])->render();
					@endphp
					{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
					generalSelect({'selector': '.bank', 'model': 28});
				}
				else
				{
					$('.alimony-container').addClass('hidden').removeClass('block');
					$('#accounts-alimony').stop(true,true).fadeOut();
				}
			})
			.on('click','#save_employee',function(e)
			{
				object = $(this);
				$.validate(
				{
					form	: '#form_employee',
					modules	: 'security',
					onError	: function($form)
					{
						swal('', '{{ Lang::get("messages.form_error") }}', 'error');
					},
					onSuccess : function($form)
					{	
						curpObject	= $('[name="curp"]');
						curp		= curpObject.val();
						flag		= false;

						curpObject.parent('div').find('.help-block').remove();
						if ($('#list_employees .tr').length > 0) 
						{
							$('#list_employees .tr').each(function(i,v)
							{
								if (!$(this).hasClass('active') && curp == $(this).find('[name="rq_curp[]"]').val()) 
								{
									flag = true;
								}
							});
						}
						if (flag) 
						{
							curpObject.removeClass('valid');
							curpObject.addClass('error');
							curpObject.parent('div').removeClass('has-success').append('<span class="help-block form-error">El CURP ya se encuentra registrado en esta requisición</span>');
							swal('', 'Por favor, verifique los datos registrados.', 'error');
							return false;
						}
						else
						{
							curpObject.removeClass('error');
							curpObject.addClass('valid');
							$('.span-error').remove();
							other_doc = true;
							span_doc = "<span class='help-block form-error span-error'>Este campo es obligatorio</span>";
							$('.path_other_document').each(function(i,v)
							{
								if($(this).val() == "")
								{
									other_doc = false;
									$(this).parents('.docs-p').find('.uploader-content').append(span_doc);
									swal('', 'Por favor llene todos los campos que son obligatorios.', 'error');
								}
								else
								{
									$(this).parents('.docs-p').find('.span-error').remove();
								}
							})
							if(other_doc == false)
							{
								return false;
							}
							if ($('#list_employees .tr').length > 0) 
							{
								$('#list_employees .tr').each(function()
								{
									if ($(this).hasClass('active')) 
									{
										$(this).remove();
									}
								});
								$('#list_employees .tr').each(function(i,v)
								{
									$(this).find('.t_alias').attr('name','alias_'+i+'[]');
									$(this).find('.t_beneficiary').attr('name','beneficiary_'+i+'[]');
									$(this).find('.t_type').attr('name','type_'+i+'[]');
									$(this).find('.t_idEmployee').attr('name','idEmployee_'+i+'[]');
									$(this).find('.t_idCatBank').attr('name','idCatBank_'+i+'[]');
									$(this).find('.t_clabe').attr('name','clabe_'+i+'[]');
									$(this).find('.t_bankName').attr('name','bankName_'+i+'[]');
									$(this).find('.t_account').attr('name','account_'+i+'[]');
									$(this).find('.t_cardNumber').attr('name','cardNumber_'+i+'[]');
									$(this).find('.t_branch').attr('name','branch_'+i+'[]');
									$(this).find('.t_name_other_document').attr('name','name_other_document_'+i+'[]');
									$(this).find('.t_path_other_document').attr('name','path_other_document_'+i+'[]');
								});
							}
							count_employee				= $('#list_employees .tr').length;
							span						= $('<span></span>');
							flag_input_subdepartment	= false;
							flag_input_department		= false;
							datas						= $('#form_employee').serializeArray();
							$.each(datas,function(i,input)
							{
								if (input.name != 'work_subdepartment')
								{
									flag_input_subdepartment = true;
								}
								else
								{
									flag_input_subdepartment = false;
								}

								if (input.name != 'work_department')
								{
									flag_input_department = true;
								}
								else
								{
									flag_input_department = false;
								}

								if (input.name != 'alias[]' && input.name != 'beneficiary[]' && input.name != 'type_account[]' && input.name != 'idEmployeeBank[]' && input.name != 'bank[]' && input.name != 'clabe[]' && input.name != 'account[]' && input.name != 'card[]' && input.name != 'branch[]' && input.name != 'name_other_document[]' && input.name != 'path_other_document[]') 
								{
									value = String(input.value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
									span.append($('<input type="hidden" name="rq_'+input.name+'[]" value="'+value+'">'));
								}
							});	
							if (flag_input_subdepartment) 
							{
								span.append($('<input type="hidden" name="rq_work_subdepartment[]" value="">'));
							}
							if (flag_input_department) 
							{
								span.append($('<input type="hidden" name="rq_work_department[]" value="">'));
							}
							$('#bodyEmployee').find('.tr').each(function()
							{
								alias			= $(this).find('[name="alias[]"]').val();
								beneficiary		= $(this).find('[name="beneficiary[]"]').val();
								type_account	= $(this).find('[name="type_account[]"]').val();
								idEmployeeBank	= $(this).find('[name="idEmployeeBank[]"]').val();
								bank			= $(this).find('[name="bank[]"]').val();
								clabe			= $(this).find('[name="clabe[]"]').val();
								account			= $(this).find('[name="account[]"]').val();
								card			= $(this).find('[name="card[]"]').val();
								branch			= $(this).find('[name="branch[]"]').val();
								bankName		= $(this).find('[name="bank[]"]').parent().text();
								div 			= $('<div class="container-accounts"></div>')
													.append($('<input type="hidden" class="t_alias" name="alias_'+count_employee+'[]" value="'+alias+'">'))								
													.append($('<input type="hidden" class="t_beneficiary" name="beneficiary_'+count_employee+'[]" value="'+beneficiary+'">'))								
													.append($('<input type="hidden" class="t_type" name="type_'+count_employee+'[]" value="'+type_account+'">'))								
													.append($('<input type="hidden" class="t_idEmployee" name="idEmployee_'+count_employee+'[]" value="'+idEmployeeBank+'">'))								
													.append($('<input type="hidden" class="t_idCatBank" name="idCatBank_'+count_employee+'[]" value="'+bank+'">'))								
													.append($('<input type="hidden" class="t_clabe" name="clabe_'+count_employee+'[]" value="'+clabe+'">'))
													.append($('<input type="hidden" class="t_bankName" name="bankName_'+count_employee+'[]" value="'+bankName+'">'))			
													.append($('<input type="hidden" class="t_account" name="account_'+count_employee+'[]" value="'+account+'">'))								
													.append($('<input type="hidden" class="t_cardNumber" name="cardNumber_'+count_employee+'[]" value="'+card+'">'))
													.append($('<input type="hidden" class="t_branch" name="branch_'+count_employee+'[]" value="'+branch+'">'));

								span.append(div);
							});
							$('#bodyAlimony').find('.tr').each(function()
							{
								alias			= $(this).find('[name="alias[]"]').val();
								beneficiary		= $(this).find('[name="beneficiary[]"]').val();
								type_account	= $(this).find('[name="type_account[]"]').val();
								idEmployeeBank	= $(this).find('[name="idEmployeeBank[]"]').val();
								bank			= $(this).find('[name="bank[]"]').val();
								clabe			= $(this).find('[name="clabe[]"]').val();
								account			= $(this).find('[name="account[]"]').val();
								card			= $(this).find('[name="card[]"]').val();
								branch			= $(this).find('[name="branch[]"]').val();
								bankName		= $(this).find('[name="bank[]"]').parent().text();
								div 			= $('<div class="container-accounts-alimony"></div>')
													.append($('<input type="hidden" class="t_alias" name="alias_'+count_employee+'[]" value="'+alias+'">'))
													.append($('<input type="hidden" class="t_beneficiary" name="beneficiary_'+count_employee+'[]" value="'+beneficiary+'">'))
													.append($('<input type="hidden" class="t_type" name="type_'+count_employee+'[]" value="'+type_account+'">'))
													.append($('<input type="hidden" class="t_idEmployee" name="idEmployee_'+count_employee+'[]" value="'+idEmployeeBank+'">'))
													.append($('<input type="hidden" class="t_idCatBank" name="idCatBank_'+count_employee+'[]" value="'+bank+'">'))
													.append($('<input type="hidden" class="t_clabe" name="clabe_'+count_employee+'[]" value="'+clabe+'">'))
													.append($('<input type="hidden" class="t_bankName" name="bankName_'+count_employee+'[]" value="'+bankName+'">'))			
													.append($('<input type="hidden" class="t_account" name="account_'+count_employee+'[]" value="'+account+'">'))
													.append($('<input type="hidden" class="t_cardNumber" name="cardNumber_'+count_employee+'[]" value="'+card+'">'))
													.append($('<input type="hidden" class="t_branch" name="branch_'+count_employee+'[]" value="'+branch+'">'));
								span.append(div);
							});
							$('.form_other_doc').each(function()
							{
								name_other_document = $(this).find('.name_other_document option:selected').val();
								path_other_document = $(this).find('.path_other_document').val();
								if (name_other_document != undefined && name_other_document != "" && path_other_document != "") 
								{
									div	= $('<div class="container-other-documents"></div>')
										.append($('<input type="hidden" class="t_name_other_document" name="name_other_document_'+count_employee+'[]" value="'+name_other_document+'">'))
										.append($('<input type="hidden" class="t_path_other_document" name="path_other_document_'+count_employee+'[]" value="'+path_other_document+'">'));
									span.append(div);
								}
							});
							@php
								$modelHead	= [];
								$body		= [];
								$modelBody	= [];
								$modelHead	= ["NOMBRE", "CURP", "PUESTO", "ACCIÓN"];
								$body	=
								[
									[
										"content"	=>
										[
											[
												"kind"		=>	"components.labels.label",
												"classEx"	=>	"nameEmployee",
											]
										]
									],
									[
										"content"	=>
										[
											[
												"kind"			=>	"components.labels.label",
												"classEx"		=>	"curpEmployee"
											]
										]
									],
									[
										"classEx" => "td_final",
										"content"	=>
										[
											[
												"kind"			=>	"components.labels.label",
												"classEx"		=>	"positionEmployee"
											]
										]
									],
									[
										"content"	=>
										[
											[
												"kind"			=>	"components.buttons.button",
												"variant"		=>	"success",
												"label"			=>	"<span class=\"icon-pencil\"></span>",
												"attributeEx"	=>	"data-toggle=\"modal\" data-target=\"#addEmployee\" type=\"button\"",
												"classEx"		=>	"edit-employee"
											],
											[
												"kind"			=>	"components.buttons.button",
												"variant"		=>	"red",
												"label"			=>	"<span class=\"icon-x delete-span\"></span>",
												"attributeEx"	=>	"type=\"button\"",
												"classEx"		=>	"delete-employee"
											],
										]
									],
								];
								$modelBody[]	=	$body;
								$table = view('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $modelBody,"noHead"    => true])->render();
							@endphp
							table	=	'{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
							row		=	$(table);
							nameEmployee = $('#form_employee').find('[name="name"]').val()+' '+$('#form_employee').find('[name="last_name"]').val()+' '+$('#form_employee').find('[name="scnd_last_name"]').val();
							nameEmployee = String(nameEmployee).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
							row.find('.nameEmployee').append(nameEmployee);
							row.find('.curpEmployee').append($('#form_employee').find('[name="curp"]').val());
							positionEmployee = $('#form_employee').find('[name="work_position"]').val();
							positionEmployee = String(positionEmployee).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
							row.find('.positionEmployee').append(positionEmployee)
							row.find('.td_final').append(span);
							$('#list_employees').append(row);
							$('#form_employee').trigger("reset");

							$.each(datas,function(i,input)
							{
								if (input.name != "qualified_employee") 
								{
									$('#form_employee').find('[name="'+input.name+'"]').val('');
									$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
									$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
									$('#form_employee').find('[name="'+input.name+'"]').parent().find('.form-error').remove();
									$('#form_employee').find('[name="'+input.name+'"]').parent().find('.help-block').remove();
									$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
								}
							});
							$('#form_employee').find('[name="employee_id"]').val('x');
							$('#form_employee').find('.uploader-content').removeClass('image_success');
							$('.doc_birth_certificate').empty().text('Sin documento');
							$('.doc_proof_of_address').empty().text('Sin documento');
							$('.doc_nss').empty().text('Sin documento');
							$('.doc_ine').empty().text('Sin documento');
							$('.doc_curp').empty().text('Sin documento');
							$('.doc_rfc').empty().text('Sin documento');
							$('.doc_cv').empty().text('Sin documento');
							$('.doc_proof_of_studies').empty().text('Sin documento');
							$('.doc_professional_license').empty().text('Sin documento');
							$('.doc_requisition').empty().text('Sin documento');
							$('#documents_employee .tr .tr-remove').remove();
							$('#other_documents').empty();
							swal('','Empleado agregado exitosamente. \n Por favor, de click en "Guardar Cambios" para poder generar el nuevo formato de requisición.','success');
							object.parents('.modal').modal('hide');
							return false;
						}
					}
				});
			})
			.on('click','.edit-employee',function()
			{
				$('#form_employee').trigger("reset");
				datas = $('#form_employee').serializeArray();
				$.each(datas,function(i,input)
				{
					if (input.name != "qualified_employee") 
					{
						$('#form_employee').find('[name="'+input.name+'"]').val('');
						$('#form_employee').find('[name="'+input.name+'"]').removeClass('valid').removeClass('error');
						$('#form_employee').find('[name="'+input.name+'"]').val(null).trigger('change');
						$('#form_employee').find('[name="'+input.name+'"]').parent().find('.form-error').remove();
						$('#form_employee').find('[name="'+input.name+'"]').parent().find('.help-block').remove();
						$('#form_employee').find('[name="'+input.name+'"]').removeAttr('style');
					}
				});
				$('#yes_qualified').prop('checked',true);
				$('#documents_employee .tr-remove').remove();
				var parent = $(this).parents('.tr');
				dataEmployee();
				$('#form_employee').find('[name="cp"]').html('<option value="'+parent.find('[name="rq_cp[]"]').val()+'" selected>'+parent.find('[name="rq_cp[]"]').val()+'</option>');
				$('#form_employee').find('[name="employee_id"]').val(parent.find('[name="rq_employee_id[]"]').val());
				$('#form_employee').find('[name="name"]').val(parent.find('[name="rq_name[]"]').val());
				$('#form_employee').find('[name="last_name"]').val(parent.find('[name="rq_last_name[]"]').val());
				$('#form_employee').find('[name="scnd_last_name"]').val(parent.find('[name="rq_scnd_last_name[]"]').val());
				$('#form_employee').find('[name="curp"]').val(parent.find('[name="rq_curp[]"]').val());
				$('#form_employee').find('[name="rfc"]').val(parent.find('[name="rq_rfc[]"]').val());
				$('#form_employee').find('[name="tax_regime"]').val(parent.find('[name="rq_tax_regime[]"]').val()).trigger('change');
				$('#form_employee').find('[name="imss"]').val(parent.find('[name="rq_imss[]"]').val());
				$('#form_employee').find('[name="email"]').val(parent.find('[name="rq_email[]"]').val());
				$('#form_employee').find('[name="phone"]').val(parent.find('[name="rq_phone[]"]').val());
				$('#form_employee').find('[name="street"]').val(parent.find('[name="rq_street[]"]').val());
				$('#form_employee').find('[name="number_employee"]').val(parent.find('[name="rq_number_employee[]"]').val());
				$('#form_employee').find('[name="colony"]').val(parent.find('[name="rq_colony[]"]').val());
				$('#form_employee').find('[name="city"]').val(parent.find('[name="rq_city[]"]').val());
				$('#form_employee').find('[name="state"]').html('<option value="'+parent.find('[name="rq_state[]"]').val()+'" selected>'+parent.find('[name="state_description"]').val()+'</option>');
				$('#form_employee').find('[name="work_state"]').val(parent.find('[name="rq_work_state[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_project"]').val(parent.find('[name="rq_work_project[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_enterprise"]').val(parent.find('[name="rq_work_enterprise[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_account"]').html('<option value="'+parent.find('[name="rq_work_account[]"]').val()+'" selected>'+parent.find('[name="account_description"]').val()+'</option>');
				$('#form_employee').find('[name="work_direction"]').val(parent.find('[name="rq_work_direction[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_department"]').val(parent.find('[name="rq_work_department[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_position"]').val(parent.find('[name="rq_work_position[]"]').val());
				$('#form_employee').find('[name="work_immediate_boss"]').val(parent.find('[name="rq_work_immediate_boss[]"]').val());
				$('#form_employee').find('[name="work_income_date"]').val(parent.find('[name="rq_work_income_date[]"]').val());
				$('#form_employee').find('[name="work_status_imss"]').val(parent.find('[name="rq_work_status_imss[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_imss_date"]').val(parent.find('[name="rq_work_imss_date[]"]').val());
				$('#form_employee').find('[name="work_down_date"]').val(parent.find('[name="rq_work_down_date[]"]').val());
				$('#form_employee').find('[name="work_ending_date"]').val(parent.find('[name="rq_work_ending_date[]"]').val());
				$('#form_employee').find('[name="work_reentry_date"]').val(parent.find('[name="rq_work_reentry_date[]"]').val());
				$('#form_employee').find('[name="work_type_employee"]').val(parent.find('[name="rq_work_type_employee[]"]').val()).trigger('change');
				$('#form_employee').find('[name="regime_employee"]').val(parent.find('[name="rq_regime_employee[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_status_employee"]').val(parent.find('[name="rq_work_status_employee[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_status_reason"]').val(parent.find('[name="rq_work_status_reason[]"]').val());
				$('#form_employee').find('[name="work_sdi"]').val(parent.find('[name="rq_work_sdi[]"]').val());
				$('#form_employee').find('[name="work_periodicity"]').val(parent.find('[name="rq_work_periodicity[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_employer_register"]').html('<option value="'+parent.find('[name="rq_work_employer_register[]"]').val()+'" selected>'+parent.find('[name="rq_work_employer_register[]"]').val()+'</option>');
				$('#form_employee').find('[name="work_payment_way"]').val(parent.find('[name="rq_work_payment_way[]"]').val()).trigger('change');
				$('#form_employee').find('[name="work_net_income"]').val(parent.find('[name="rq_work_net_income[]"]').val());
				$('#form_employee').find('[name="work_complement"]').val(parent.find('[name="rq_work_complement[]"]').val());
				$('#form_employee').find('[name="work_fonacot"]').val(parent.find('[name="rq_work_fonacot[]"]').val());
				$('#form_employee').find('[name="work_infonavit_credit"]').val(parent.find('[name="rq_work_infonavit_credit[]"]').val());
				$('#form_employee').find('[name="work_infonavit_discount"]').val(parent.find('[name="rq_work_infonavit_discount[]"]').val());
				$('#form_employee').find('[name="work_infonavit_discount_type"]').val(parent.find('[name="rq_work_infonavit_discount_type[]"]').val());
				$('#form_employee').find('[name="work_alimony_discount_type"]').val(parent.find('[name="rq_work_alimony_discount_type[]"]').val());
				$('#form_employee').find('[name="work_alimony_discount"]').val(parent.find('[name="rq_work_alimony_discount[]"]').val());
				$('#form_employee').find('[name="replace"]').val(parent.find('[name="rq_replace[]"]').val());
				$('#form_employee').find('[name="purpose"]').val(parent.find('[name="rq_purpose[]"]').val());
				$('#form_employee').find('[name="requeriments"]').val(parent.find('[name="rq_requeriments[]"]').val());
				$('#form_employee').find('[name="observations"]').val(parent.find('[name="rq_observations[]"]').val());
				$('#form_employee').find('[name="work_viatics"]').val(parent.find('[name="rq_work_viatics[]"]').val());
				$('#form_employee').find('[name="work_camping"]').val(parent.find('[name="rq_work_camping[]"]').val());
				$('#form_employee').find('[name="work_position_immediate_boss"]').val(parent.find('[name="rq_work_position_immediate_boss[]"]').val());
				$('#form_employee').find('[name="work_subdepartment"]').val(parent.find('[name="rq_work_subdepartment[]"]').val()).trigger('change');
				$('#form_employee').find('[name="computer_required"]').val(parent.find('[name="rq_computer_required[]"]').val()).trigger('change');
				if (parent.find('[name="rq_qualified_employee[]"]').val() == "1") 
				{
					$('#form_employee').find('#yes_qualified').prop('checked',true);
				}
				else
				{
					$('#form_employee').find('#no_qualified').prop('checked',true);
				}
				if (parent.find('[name="rq_doc_birth_certificate[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_birth_certificate[]"]').val();
					$('#form_employee').find('[name="doc_birth_certificate"]').val(parent.find('[name="rq_doc_birth_certificate[]"]').val());
					$('[name="doc_birth_certificate"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_birth_certificate').text('');
					@php
						$btnBirth = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnBirth = '{!!preg_replace("/(\r)*(\n)*/", "", $btnBirth)!!}';
					$('.doc_birth_certificate').append($(btnBirth).attr('href',url).attr('title',parent.find('[name="rq_doc_birth_certificate[]"]').val()));
				}
				if (parent.find('[name="rq_doc_proof_of_address[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_proof_of_address[]"]').val();
					$('#form_employee').find('[name="doc_proof_of_address"]').val(parent.find('[name="rq_doc_proof_of_address[]"]').val());
					$('[name="doc_proof_of_address"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_proof_of_address').text('');
					@php
						$btnAddress = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnAddress = '{!!preg_replace("/(\r)*(\n)*/", "", $btnAddress)!!}';
					$('.doc_proof_of_address').append($(btnAddress).attr('href',url).attr('title',parent.find('[name="rq_doc_proof_of_address[]"]').val()));
				}
				if (parent.find('[name="rq_doc_nss[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_nss[]"]').val();
					$('#form_employee').find('[name="doc_nss"]').val(parent.find('[name="rq_doc_nss[]"]').val());
					$('[name="doc_nss"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_nss').text('');
					@php
						$btnNSS = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnNSS = '{!!preg_replace("/(\r)*(\n)*/", "", $btnNSS)!!}';
					$('.doc_nss').append($(btnNSS).attr('href',url).attr('title',parent.find('[name="rq_doc_nss[]"]').val()));
				}
				if (parent.find('[name="rq_doc_ine[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_ine[]"]').val();
					$('#form_employee').find('[name="doc_ine"]').val(parent.find('[name="rq_doc_ine[]"]').val());
					$('[name="doc_ine"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_ine').text('');
					@php
						$btnIne = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnIne = '{!!preg_replace("/(\r)*(\n)*/", "", $btnIne)!!}';
					$('.doc_ine').append($(btnIne).attr('href',url).attr('title',parent.find('[name="rq_doc_ine[]"]').val()));
				}
				if (parent.find('[name="rq_doc_curp[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_curp[]"]').val();
					$('#form_employee').find('[name="doc_curp"]').val(parent.find('[name="rq_doc_curp[]"]').val());
					$('[name="doc_curp"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_curp').text('');
					@php
						$btnCurp = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnCurp = '{!!preg_replace("/(\r)*(\n)*/", "", $btnCurp)!!}';
					$('.doc_curp').append($(btnCurp).attr('href',url).attr('title',parent.find('[name="rq_doc_curp[]"]').val()));
				}
				if (parent.find('[name="rq_doc_rfc[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_rfc[]"]').val();
					$('#form_employee').find('[name="doc_rfc"]').val(parent.find('[name="rq_doc_rfc[]"]').val());
					$('[name="doc_rfc"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_rfc').text('');
					@php
						$btnRFC = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnRFC = '{!!preg_replace("/(\r)*(\n)*/", "", $btnRFC)!!}';
					$('.doc_rfc').append($(btnRFC).attr('href',url).attr('title',parent.find('[name="rq_doc_rfc[]"]').val()));
				}
				if (parent.find('[name="rq_doc_cv[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_cv[]"]').val();
					$('#form_employee').find('[name="doc_cv"]').val(parent.find('[name="rq_doc_cv[]"]').val());
					$('[name="doc_cv"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_cv').text('');
					@php
						$btnCV = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnCV = '{!!preg_replace("/(\r)*(\n)*/", "", $btnCV)!!}';
					$('.doc_cv').append($(btnCV).attr('href',url).attr('title',parent.find('[name="rq_doc_cv[]"]').val()));
				}
				if (parent.find('[name="rq_doc_proof_of_studies[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_proof_of_studies[]"]').val();
					$('#form_employee').find('[name="doc_proof_of_studies"]').val(parent.find('[name="rq_doc_proof_of_studies[]"]').val());
					$('[name="doc_proof_of_studies"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_proof_of_studies').text('');
					@php
						$btnStudies = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnStudies = '{!!preg_replace("/(\r)*(\n)*/", "", $btnStudies)!!}';
					$('.doc_proof_of_studies').append($(btnStudies).attr('href',url).attr('title',parent.find('[name="rq_doc_proof_of_studies[]"]').val()));
				}
				if (parent.find('[name="rq_doc_professional_license[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_professional_license[]"]').val();
					$('#form_employee').find('[name="doc_professional_license"]').val(parent.find('[name="rq_doc_professional_license[]"]').val());
					$('[name="doc_professional_license"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_professional_license').text('');
					@php
						$btnLicense = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnLicense = '{!!preg_replace("/(\r)*(\n)*/", "", $btnLicense)!!}';
					$('.doc_professional_license').append($(btnLicense).attr('href',url).attr('title',parent.find('[name="rq_doc_professional_license[]"]').val()));
				}
				if (parent.find('[name="rq_doc_requisition[]"]').val() != "") 
				{
					url = '{{ url('docs/requisition') }}/'+parent.find('[name="rq_doc_requisition[]"]').val();
					$('#form_employee').find('[name="doc_requisition"]').val(parent.find('[name="rq_doc_requisition[]"]').val());
					$('[name="doc_requisition"]').siblings('.uploader-content').addClass('image_success');
					$('.doc_requisition').text('');
					@php
						$btnRequisition = view('components.buttons.button',[
							"variant"		=> "secondary",
							"buttonElement" => "a",
							"attributeEx"	=> "type=\"button\" target=\"_blank\"",
							"label"			=> "Archivo"
						])->render();
					@endphp
					btnRequisition = '{!!preg_replace("/(\r)*(\n)*/", "", $btnRequisition)!!}';
					$('.doc_requisition').append($(btnRequisition).attr('href',url).attr('title',parent.find('[name="rq_doc_requisition[]"]').val()));
				}
				accounts			= parent.find('.container-accounts');
				accounts_alimony	= parent.find('.container-accounts-alimony');
				$('#bank-data-register .tr').empty();
				$('#bank-data-register-alimony .tr').empty();
				if (accounts.length > 0) 
				{
					$(accounts).each(function(i,v)
					{
						alias		= $(this).find('.t_alias').val();
						bankid		= $(this).find('.t_idCatBank').val();
						clabe		= $(this).find('.t_clabe').val();
						account		= $(this).find('.t_account').val();
						card		= $(this).find('.t_cardNumber').val();
						branch		= $(this).find('.t_branch').val();
						idEmployee	= $(this).find('.t_idEmployee').val();
						bankName 	= $(this).find('.t_bankName').val();
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [ "Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];

							$body = [
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-alias"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\" value=\"\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"1\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-bankName"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\"",
											"classEx"		=> "idEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-clabe"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-account"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-card"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-branch"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"branch[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"          => "components.buttons.button",
											"variant"       => "red",
											"label"         => "<span class=\"icon-x\"></span>",
											"attributeEx"   => "type=\"button\"",
											"classEx"		=> "delete-bank"
										]
									]
								]
							];
							$modelBody[] = $body;
							$table = view("components.tables.alwaysVisibleTable",[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"    => true
							])->render();
						@endphp
						table	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						bank	= $(table);
						bank 	= rowColor('#bodyEmployee', bank);
						bank.find('.class-alias').text(alias == '' ? '---' : alias);
						bank.find('[name="alias[]"]').val(alias);
						bank.find('.class-bankName').text(bankName);
						bank.find('[name="idEmployeeBank[]"]').val(idEmployee);
						bank.find('[name="bank[]"]').val(bankid);
						bank.find('.class-clabe').text(clabe == '' ? '---' : clabe);
						bank.find('[name="clabe[]"]').val(clabe);
						bank.find('.class-account').text(account == '' ? '---' : account);
						bank.find('[name="account[]"]').val(account);
						bank.find('.class-card').text(card == '' ? '---' : card);
						bank.find('[name="card[]"]').val(card);
						bank.find('.class-branch').text(branch == '' ? '---' : branch);
						bank.find('[name="branch[]"]').val(branch);
						$('.class-accounts').addClass('hidden')
						$('#bank-data-register').parent().removeClass('hidden');
						$('#bodyEmployee').append(bank);
					});
				}
				if (accounts_alimony.length > 0) 
				{
					$(accounts_alimony).each(function(i,v)
					{
						beneficiary	= $(this).find('.t_beneficiary').val();
						alias		= $(this).find('.t_alias').val();
						bankid		= $(this).find('.t_idCatBank').val();
						clabe		= $(this).find('.t_clabe').val();
						account		= $(this).find('.t_account').val();
						card		= $(this).find('.t_cardNumber').val();
						branch		= $(this).find('.t_branch').val();
						idEmployee	= $(this).find('.t_idEmployee').val();
						bankName 	= $(this).find('.t_bankName').val();

						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= ["Beneficiario","Alias","Banco","CLABE","Cuenta","Tarjeta","Sucursal","Acción"];
							
							$body = [ "classEx"	=> "tr-employee-edit",
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-beneficiary"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"beneficiary[]\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"type_account[]\" value=\"2\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-alias"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"alias[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-bankName"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"idEmployeeBank[]\"",
											"classEx"		=> "idEmployee"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"bank[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-clabe"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-account"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"account[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-card"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"card[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-branch"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"branch[]\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind"          => "components.buttons.button",
											"variant"       => "red",
											"label"         => "<span class=\"icon-x\"></span>",
											"attributeEx"   => "type=\"button\"",
											"classEx"		=> "delete-bank"
										]
									]
								]
							];
							$modelBody[]	= $body;
							$table			= view("components.tables.alwaysVisibleTable",[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"    => true
							])->render();
						@endphp
						table	= '{!!preg_replace("/(\r)*(\n)*/", "", $table)!!}';
						bank	= $(table);
						bank 	= rowColor('#bodyAlimony', bank);
						bank.find('.class-beneficiary').text(beneficiary == '' ? '---' : beneficiary);
						bank.find('[name="beneficiary[]"]').val(beneficiary);
						bank.find('.class-alias').text(alias == '' ? '---' : alias);
						bank.find('[name="alias[]"]').val(alias);
						bank.find('.class-bankName').text(bankName);
						bank.find('[name="idEmployeeBank[]"]').val(idEmployee);
						bank.find('[name="bank[]"]').val(bankid);
						bank.find('.class-clabe').text(clabe == '' ? '---' : clabe);
						bank.find('[name="clabe[]"]').val(clabe);
						bank.find('.class-account').text(account == '' ? '---' : account);
						bank.find('[name="account[]"]').val(account);
						bank.find('.class-card').text(card == '' ? '---' : card);
						bank.find('[name="card[]"]').val(card);
						bank.find('.class-branch').text(branch == '' ? '---' : branch);
						bank.find('[name="branch[]"]').val(branch);
						$('#bodyAlimony').append(bank);
					});
					alimony = $('#alimony');
					alimony.prop('checked',true);
					$('#accounts-alimony').stop(true,true).fadeIn();
					$('.alimony-container').stop(true,true).fadeIn();
					$('#bank-data-register-alimony').parent().removeClass('hidden');
					$('#not-found-accounts-alimony').addClass('hidden');
					generalSelect({'selector': '.bank', 'model': 28});
					@php
						$selects = collect([
							[
								"identificator"          => "[name=\"work_alimony_discount_type\"]",
								"placeholder"            => "Seleccione el tipo de descuento",
								"language"               => "es",
								"maximumSelectionLength" => "1"
							]
						]);
						$script = view("components.scripts.selects",["selects" => $selects])->render();
					@endphp
					{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
				}
				other_documents	= $(this).parents('.tr').find('.container-other-documents');
				if (other_documents.length > 0) 
				{
					$(other_documents).each(function(i,v)
					{
						name	= $(this).find('.t_name_other_document').val();
						path	= $(this).find('.t_path_other_document').val();
						url		= '{{ url('docs/requisition') }}/'+path;
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= ["Nombre del documento", "Archivo"];
							$body = [ "classEx" => "tr-remove",
								[
									"content"	=>
									[
										[
											"kind"		=> "components.labels.label",
											"classEx"	=> "class-name"
										]
									]
								],
								[
									"classEx"	=> "doc_birth_certificate",
									"content"	=>
									[
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "secondary",
											"buttonElement" => "a",
											"attributeEx"	=> "type=\"button\" target=\"_blank\"",
											"classEx"		=> "btn-doc",
											"label"			=> "Archivo"
										]
									]
								],
							];
							$modelBody[] = $body;
							$tableDoc = view('components.tables.alwaysVisibleTable',[
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"    => true
							])->render();
						@endphp
						tableDoc= '{!!preg_replace("/(\r)*(\n)*/", "", $tableDoc)!!}';
						tr		= $(tableDoc);
						tr.find('.class-name').text(name);
						tr.find('.btn-doc').attr('href',url);
						tr.find('.btn-doc').attr('title',path);
						$('#documents_employee').append(tr);
						@php
							$docs = view('components.documents.upload-files',[
								"classEx"				=> "form_other_doc",
								"classExContainer"		=> "image_success",
								"classExInput"			=> "pathActioner",
								"attributeExInput"		=> "type=\"file\" name=\"path\" accept=\".pdf\"",
								"classExDelete"			=> "delete_other_doc",
								"attributeExRealPath"	=> "type=\"hidden\" name=\"path_other_document[]\"",
								"classExRealPath"		=> "path path_other_document",
								"componentsExUp"		=>
								[
									[
										"kind" => "components.labels.label", 
										"label" => "Seleccione el tipo de documento:"
									],
									[
										"kind" 			=> "components.inputs.select",
										"classEx" 		=> "name_other_document",
										"attributeEx"	=> "name=\"name_other_document[]\" multiple data-validation=\"required\"" 
									]
								]
							])->render();
						@endphp
						docEmployee = '{!!preg_replace("/(\r)*(\n)*/", "", $docs)!!}';
						doc			= $(docEmployee);
						doc.find('[name="path_other_document[]"]').val(path);
						if(name == "Aviso de retención por crédito Infonavit")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Aviso de retención por crédito Infonavit">Aviso de retención por crédito Infonavit</value>'))
						}
						if(name == "Estado de cuenta")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Estado de cuenta">Estado de cuenta</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Estado de cuenta">Estado de cuenta</value>'))
						}
						if(name == "Cursos de capacitación")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Cursos de capacitación">Cursos de capacitación</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Cursos de capacitación">Cursos de capacitación</value>'))
						}
						if(name == "Carta de recomendación")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Carta de recomendación">Carta de recomendación</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Carta de recomendación">Carta de recomendación</value>'))
						}
						if(name == "Identificación")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Identificación">Identificación</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Identificación">Identificación</value>'))
						}
						if(name == "Hoja de expediente")
						{
							doc.find('[name="name_other_document[]"]').append($('<option selected="selected" value="Hoja de expediente">Hoja de expediente</value>'));
						}
						else
						{
							doc.find('[name="name_other_document[]"]').append($('<option value="Hoja de expediente">Hoja de expediente</value>'))
						}
						$('#other_documents').append(doc);
						@php
							$selects = collect([
								[
									"identificator"          => "[name=\"name_other_document[]\"]",
									"placeholder"            => "Seleccione el tipo de documento",
									"language"               => "es",
									"maximumSelectionLength" => "1"
								]
							]);
							$script = view("components.scripts.selects",["selects" => $selects])->render();
						@endphp
						{!!preg_replace("/(\r)*(\n)*/", "", $script)!!}
					});
				}
				object = $(this);
				@if(!isset($request) || isset($request) && $request->status == 2) 
					object.parents('.tr').addClass('active');
				@endif
			})
			.on('change','.pathActioner',function(e)
			{	
				target = e.currentTarget;
				filename     = $(this);
				uploadedName = $(this).parent('.uploader-content').siblings('.path');
				extention    = /\.pdf/i;
				if(filename.val().search(extention) == -1)
				{
					swal('', 'El tipo de archivo no es soportado, por favor seleccione una imagen jpg, png o un archivo pdf', 'warning');
					$(this).val('');
				}
				else if(this.files[0].size>315621376)
				{
					swal('', 'El tamaño máximo de su archivo no debe ser mayor a 300Mb', 'warning');
				}
				else
				{
					$(this).css('visibility','hidden').parent('.uploader-content').addClass('loading').removeClass(function (index, css)
					{
						return (css.match (/\bimage_\S+/g) || []).join(' '); // removes anything that starts with "image_"
					});
					formData = new FormData();
					formData.append(filename.attr('name'), filename.prop("files")[0]);
					formData.append(uploadedName.attr('name'),uploadedName.val());
					$('.btn_disable').attr('disabled', true);
					$('.disable-button').prop('disabled', true);
					$.ajax(
					{
						type       : 'post',
						url        : '{{ route("requisition.upload") }}',
						data       : formData,
						contentType: false,
						processData: false,
						success    : function(r)
						{
							if(r.error == 'DONE')
							{
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading').addClass('image_success');
								$(target).parent('.uploader-content').siblings('.path').val(r.path);
								$(target).val('');
							}
							else
							{
								swal('',r.message, 'error');
								$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
								$(target).val('');
								$(target).parent('.uploader-content').siblings('.path').val('');
							}
							$('.btn_disable').attr('disabled', false);	
						},
						error: function()
						{
							swal('', 'Ocurrió un error durante la carga del archivo, intente de nuevo, por favor', 'error');
							$(target).removeAttr('style').parent('.uploader-content').removeClass('loading');
							$(target).val('');
							$(target).parent('.uploader-content').siblings('.path').val('');
						}
					}).done(function() {
						$('.disable-button').prop('disabled', false);
					});
				}
			})
			.on('click','.delete-employee',function()
			{
				id = $(this).parents('.tr').find('[name="rq_employee_id[]"]').val();
				if (id != "x")
				{
					$('#delete_employee').append($('<input type="hidden" name="delete_employee[]" value="'+id+'">'));
				}
				$(this).parents('.tr').remove();
				$('#list_employees .tr').each(function(i,v)
				{
					$(this).find('.t_alias').attr('name','alias_'+i+'[]');
					$(this).find('.t_beneficiary').attr('name','beneficiary_'+i+'[]');
					$(this).find('.t_type').attr('name','type_'+i+'[]');
					$(this).find('.t_idEmployee').attr('name','idEmployee_'+i+'[]');
					$(this).find('.t_idCatBank').attr('name','idCatBank_'+i+'[]');
					$(this).find('.t_clabe').attr('name','clabe_'+i+'[]');
					$(this).find('.t_account').attr('name','account_'+i+'[]');
					$(this).find('.t_cardNumber').attr('name','cardNumber_'+i+'[]');
					$(this).find('.t_branch').attr('name','branch_'+i+'[]');
					$(this).find('.t_name_other_document').attr('name','name_other_document_'+i+'[]');
					$(this).find('.t_path_other_document').attr('name','path_other_document_'+i+'[]');
				});
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
						$('.modal-employee').html(data);
					}
				});
			})
			.on('change','[name="work_infonavit_discount"]',function()
			{
				work_infonavit_discount_type = $('[name="work_infonavit_discount_type"] option:selected').val();
				if (work_infonavit_discount_type == 3) 
				{
					if ($(this).val() > 100) 
					{
						$(this).val('');
						swal('','El porcentaje no puede ser mayor de 100','error');
					}
				}
			})
			.on('change','[name="work_infonavit_discount_type"]',function()
			{
				work_infonavit_discount_type = $('[name="work_infonavit_discount_type"] option:selected').val();
				if (work_infonavit_discount_type == 3) 
				{
					if ($('[name="work_infonavit_discount"]').val() > 100) 
					{
						$('[name="work_infonavit_discount"]').val('');
						swal('','El porcentaje no puede ser mayor de 100','error');
					}
				}
			})
			.on('change','[name="work_alimony_discount_type"]',function()
			{
				work_alimony_discount_type = $('[name="work_alimony_discount_type"] option:selected').val();
				if (work_alimony_discount_type == 2) 
				{
					if ($('[name="work_alimony_discount"]').val() > 100) 
					{
						$('[name="work_alimony_discount"]').val('');
						swal('','El porcentaje no puede ser mayor de 100','error');
					}
				}
			})
			.on('change','[name="work_alimony_discount"]',function()
			{
				work_alimony_discount_type = $('[name="work_alimony_discount_type"] option:selected').val();
				if (work_alimony_discount_type == 2) 
				{
					if ($(this).val() > 100) 
					{
						$(this).val('');
						swal('','El porcentaje no puede ser mayor de 100','error');
					}
				}
			})
			.on('click','#add_document',function()
			{
				@php
					$options = collect();
					$docskind = ["Aviso de retención por crédito Infonavit","Estado de cuenta","Cursos de capacitación","Carta de recomendación","Certificado médico","Identificación","Hoja de expediente"];
					foreach($docskind as $kind)
					{
						$options = $options->concat([["value" => $kind, "description" => $kind]]);
					}
					$newDoc = view('components.documents.upload-files',[
						"classEx"				=> "form_other_doc",
						"attributeExInput" 		=> "name=\"path\" accept=\".pdf\"",
						"classExInput" 			=> "pathActioner",
						"attributeExRealPath" 	=> "name=\"path_other_document[]\"",
						"classExRealPath" 		=> "path path_other_document",
						"componentsExUp" => 
						[
							[
								"kind" => "components.labels.label", "label" => "Tipo de documento:"
							],
							[
								"kind" => "components.inputs.select", "options" => $options, "attributeEx" => "name=\"name_other_document[]\" multiple=\"multiple\" data-validation=\"required\"", "classEx" => "name_other_document mb-6"
							]
						],
						"classExDelete"			=> "delete_other_doc",	
					])->render();
				@endphp
				newDoc          = '{!!preg_replace("/(\r)*(\n)*/", "", $newDoc)!!}';
				containerNewDoc = $(newDoc);
				$('#other_documents').append(containerNewDoc);
				@php
					$selects = collect(
						[
							[
								"identificator"				=> "[name=\"name_other_document[]\"]",
								"placeholder"				=> "Seleccione el tipo de documento",
								"maximumSelectionLength"	=> "1",
							]
						]
					);
				@endphp
				@component("components.scripts.selects",["selects" => $selects])@endcomponent
			})
			.on('click','.delete_other_doc',function()
			{
				$(this).parents('.docs-p').remove();
			})
			.on('click','.view-detail-request',function()
			{
				folio = $(this).attr('data-folio');
				$.ajax(
				{
					type		: 'post',
					url			: '{{ route('requisition.view-detail-purchase') }}',
					data		: { 'folio':folio },
					success		: function(data)
					{
						$('#detail_purchase').html(data);
					},
					error : function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#viewDetailPurchase').hide();
					}
				});
			})
			.on('select2:unselecting','[name="project_id"]', function (e)
			{
				requisition_type	= $('[name="requisition_type"] option:selected').val();
				list_employees		= $('#list_employees .tr').length;
				if (requisition_type == 3 && list_employees > 0)
				{
					e.preventDefault();
					swal({
						title		: "Cambiar de Proyecto",
						text		: "Si cambia el proyecto, todos los empleados que ya se encontraban agregados serán eliminados",
						icon		: "warning",
						buttons		: ["Cancelar","OK"],
						dangerMode	: true,
					})
					.then((willClean) =>
					{
						if(willClean)
						{
							$(this).val(null).trigger('change');
							$('#list_employees .tr').each(function()
							{
								id = $(this).find('[name="rq_employee_id[]"]').val();
								if (id != "x") 
								{
									$('#delete_employee').append($('<input type="hidden" name="delete_employee[]" value="'+id+'">'));
								}
							});
							$('#list_employees').empty();
						}
						else
						{
							swal.close();
						}
					});
				}
			})
			.on('click','.btn-delete-form-requisition',function(e)
			{
				e.preventDefault();
				form = $(this).parents('form');
				swal({
					title       : "Limpiar formulario",
					text        : "¿Confirma que desea limpiar el formulario?",
					icon        : "warning",
					buttons     : ["Cancelar","OK"],
					dangerMode  : true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$('#body').html('');
						$('.tr').html("");
						$('.remove').val("");
						$('.removeselect').val(null).trigger('change');
						$('.uploader-content').removeClass('image_success');
						$('#documents-requisition').addClass('hidden');
						$('#documents-requisition').empty();
						form[0].reset();
					}
					else
					{
						swal.close();
					}
				});
			})
			Number.prototype.formatMoney = function(c, d, t)
			{
				var n = this,
				d = d == undefined ? "." : d, 
					d = d == undefined ? "." : d, 
				d = d == undefined ? "." : d, 
					d = d == undefined ? "." : d, 
				d = d == undefined ? "." : d, 
				t = t == undefined ? "," : t, 
					t = t == undefined ? "," : t, 
				t = t == undefined ? "," : t, 
					t = t == undefined ? "," : t, 
				t = t == undefined ? "," : t, 
				s = n < 0 ? "-" : "", 
					s = n < 0 ? "-" : "", 
				s = n < 0 ? "-" : "", 
					s = n < 0 ? "-" : "", 
				s = n < 0 ? "-" : "", 
				i = String(parseInt(n = Math.abs(Number(n) || 0))), 
					i = String(parseInt(n = Math.abs(Number(n) || 0))), 
				i = String(parseInt(n = Math.abs(Number(n) || 0))), 
					i = String(parseInt(n = Math.abs(Number(n) || 0))), 
				i = String(parseInt(n = Math.abs(Number(n) || 0))), 
				j = (j = i.length) > 3 ? j % 3 : 0;
				return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t);
			};
		});
	</script>
@endsection
