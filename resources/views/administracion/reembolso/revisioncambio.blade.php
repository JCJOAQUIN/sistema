@extends('layouts.child_module')
@section('data')
	@php
		$taxes	= 0;
	@endphp
	@if($request->refunds->first()->idRequisition != "")
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div><span class="icon-bullhorn"></span> Esta solicitud viene de la requisición #{{ $request->refunds->first()->idRequisition }}.</div>
			@endslot
		@endcomponent
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div class="flex flex-row">
					@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
						FOLIO:
					@endcomponent
					{{ $request->new_folio }}.
				</div>
				@if($request->refunds->first()->requisitionRequest->idProject == 75)
					<div class="flex flex-row">
						@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
							SUBPROYECTO/CÓDIGO WBS:
						@endcomponent
						{{ $request->refunds->first()->requisitionRequest->requisition->wbs->code_wbs }}.
					</div>
					@if($request->refunds->first()->requisitionRequest->requisition->edt()->exists())
						<div class="flex flex-row">
							@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
								CÓDIGO EDT:
							@endcomponent
							{{  $request->refunds->first()->requisitionRequest->requisition->edt->fullName() }}.
						</div>
					@endif
				@endif
			@endslot
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->refunds->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->refunds->first()->datetitle)->format('d-m-Y')],
			["Solicitante:", $request->requestUser()->exists() ? $request->requestUser->fullname() : ""],
			["Elaborado por:", $request->requestUser()->exists() ? $request->elaborateUser->fullname() : ""],
			["Empresa:", $request->requestUser()->exists() ? $request->requestEnterprise->name : ""],
			["Dirección:", $request->requestUser()->exists() ? $request->requestDirection->name : ""],
			["Departamento:", $request->requestUser()->exists() ? $request->requestDepartment->name : ""],
			["Proyecto:", $request->requestUser()->exists() ? $request->requestProject->proyectName : ""]
		];	
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud", "classEx" => "mb-6"]) @endcomponent
	@component("components.labels.title-divisor") DATOS DEL SOLICITANTE @endcomponent
	@component("components.tables.table-request-detail.container",["variant" => "simple"])
		@php
			$modelTable						= [];
			$modelTable["Forma de pago"]	= $request->refunds->first()->paymentMethod->method;
			$modelTable["Referencia"]		= $request->refunds->first()->reference != null ? htmlentities($request->refunds->first()->reference) : '---';
			$modelTable["Tipo de moneda"]	= $request->refunds->first()->currency;
			$modelTable["Importe"]			= "$ ".number_format($request->refunds->first()->total,2);

			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->refunds->first()->idUsers)->get() as $bank)
			{
				if($request->refunds->first()->idEmployee == $bank->idEmployee)
				{
					$modelTable["Banco"]				= $bank->description;
					$modelTable["Alias"]				= $bank->alias!=null ? $bank->alias : '---';
					$modelTable["Número de tarjeta"]	= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$modelTable["CLABE"]				= $bank->clabe!=null ? $bank->clabe : '---';
					$modelTable["Número de cuenta"]		= $bank->account!=null ? $bank->account : '---';
				}
			}
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
	@endcomponent
	@component("components.labels.title-divisor") RELACIÓN DE DOCUMENTOS @endcomponent
	@php
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
		$countConcept = 1;
		$modelBody = [];
		foreach($request->refunds->first()->refundDetail as $refundDetail)
		{		
			$subtotalFinal	+= $refundDetail->amount;
			$ivaFinal		+= $refundDetail->tax;
			$totalFinal		+= $refundDetail->sAmount;
			$body = 
			[
				"classEx" => "tr",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    => "components.labels.label",
							"label" => $countConcept,
						]
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    	=> "components.labels.label",
							"label" 	=> htmlentities($refundDetail->concept),
						],
					],
				],
			];
			
			if(isset($refundDetail->account))
			{
				$accountDescription = $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.')';
			}
			else
			{
				$accountDescription = "---";
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $accountDescription,
					],
				],
			];
			if($refundDetail->taxPayment==1) 
			{
				$taxPayment = "si";
			}
			else
			{
				$taxPayment = "no";
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $taxPayment,
					],
				],
			];

			$body[] = 
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->amount,2),
					],
				],
			];
			$body[] = 
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->tax,2),
					],
				],
			];

			$taxes2 = 0;
			foreach($refundDetail->taxes as $tax)
			{
				$taxes2 += $tax->amount;
			}
			
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($taxes2,2),
					],
				],
			];

			$retentions2 = 0;
			foreach($refundDetail->retentions as $ret)
			{
				$retentions2 +=$ret->amount;
			}

			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($retentions2,2),
					],
				],
			];

			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->sAmount,2),
					],
				],
			];

			if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
			{
				$contentBodyDocs = [];
				foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
				{
					if ($doc->datepath != "") 
					{
						$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->datepath)->format('d-m-Y');
					}
					else
					{
						$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y');
					}
					
					if($doc->name != '')
					{
						$docName = $doc->name;
					}
					else
					{
						$docName = "Otro";
					}
					$contentBodyDocs[] = 
					[
						"kind"  => "components.labels.label",
						"label" => $date,
					];
					$contentBodyDocs[] = 
					[
						"kind"  => "components.labels.label",
						"label" => $docName,
					];
					$contentBodyDocs[] = 
					[
						"kind" => "components.buttons.button", 									
						"buttonElement" => "a",
						"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset("docs/refounds/".$doc->path)."\"",
						"variant" => "dark-red",
						"label" => "PDF",
					];
				}
			}
			else
			{
				$contentBodyDocs[] =
				[
					"kind"  => "components.labels.label",
					"label" => "---",
				];
			}
			$body[] = [
				"classEx" => "td",
				"content" => $contentBodyDocs
			];
			$modelBody [] = $body;
			$countConcept++;
		}
	@endphp
	@component("components.tables.table",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"classEx" 	=> "my-6",
		"themeBody" => "striped"
	])
		@slot("classExBody")
			request-validate
		@endslot
		@slot("attributeExBody")
			id="body"
		@endslot
	@endcomponent
	@php
		$modelTable = [];
		$taxes 	= 0;
		$retentionConcept2 = 0;

		if($totalFinal!=0)
		{
			$valueSubtotal = "value = \"".number_format($subtotalFinal,2)."\"";
			$labelSubtotal = "$ ".number_format($subtotalFinal,2);
 			$valueIVA 	   = "value = \"".number_format($ivaFinal,2)."\"";
			$labelIVA 	   = "$ ".number_format($ivaFinal,2);
			$valueTotal    = "value = \"".number_format($totalFinal,2)."\"";
			$labelTotal    = "$ ".number_format($totalFinal,2);
		}
		else 
		{
			$valueSubtotal = "";
			$labelSubtotal = "$ 0.00";
 			$valueIVA 	   = "";
			$labelIVA 	   = "$ 0.00";
			$valueTotal    = "";
			$labelTotal    = "$ 0.00";
		}
		if(isset($request))
		{
			foreach($request->refunds->first()->refundDetail as $detail)
			{
				foreach($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
				foreach($detail->retentions as $ret)
				{
					$retentionConcept2 = $retentionConcept2 + $ret->amount;
				}
			}
		}
		$modelTable = 
		[	
			["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => $labelSubtotal, "classEx" => "my-2 label-subtotal"],["kind" => "components.inputs.input-text",	"classEx" => "subtotal", "attributeEx" => "type=\"hidden\" id=\"subtotal\" readonly name=\"subtotal\" ".$valueSubtotal]]],
			["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelIVA, "classEx" => "my-2 label-IVA"],["kind" => "components.inputs.input-text",	"classEx" => "ivaTotal", "attributeEx" => "type=\"hidden\" id=\"iva\" name=\"iva\" ".$valueIVA]]],
			["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($taxes,2), "classEx" => "my-2 label-taxes"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountAA\" value=\"".number_format($taxes,2)."\""]]],
			["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($retentionConcept2,2), "classEx" => "my-2 label-retentions"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountRetentions\" value=\"".number_format($retentionConcept2,2)."\""]]],
			["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelTotal, "classEx" => "my-2 label-total"],["kind" => "components.inputs.input-text",	"classEx" => "total", "attributeEx" => "type=\"hidden\" id=\"total\" name=\"total\" ".$valueTotal]]],
		];
		
	@endphp
	@component("components.templates.outputs.form-details", ["modelTable" => $modelTable, "classEx" => "mb-6"]) @endcomponent
	@component("components.forms.form", ["attributeEx" => "action=\"".route('refund.review.update',$request->folio)."\" method=\"POST\" id=\"container-alta\"", "files" => true, "methodEx" => "PUT"])
		@component('components.containers.container-approval')
			@slot('attributeExLabel')
				id="label-inline"
			@endslot
			@slot('attributeExButton')
				name="status"
				value="4"
				id="aprobar"
			@endslot
			@slot('attributeExButtonTwo')
				name="status"
				value="6"
				id="rechazar"
			@endslot
		@endcomponent	
		<div id="aceptar" class="hidden">
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-enterprisesR\" name=\"idEnterpriseR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-enterprisesR";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
					@component("components.inputs.input-text")
						@slot("classEx")
							enterprisesR_old_id
						@endslot
						@slot("attributeEx") 
							type="hidden"
							value="{{$request->idEnterprise}}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Dirección: @endcomponent
					@php
						$options = collect();
						foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							$description = $area->name;
							if($request->idArea == $area->id)
							{
								$options = $options->concat([["value"=>$area->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$area->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-areasR\" multiple=\"multiple\" name=\"idAreaR\" data-validation=\"required\"";
						$classEx = "js-areasR";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Departamento: @endcomponent
					@php
						$options = collect();
						foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							$description = $department->name;
							if($request->idDepartment == $department->id)
							{
								$options = $options->concat([["value"=>$department->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$department->id, "description"=>$description]]);
							}
						}
						$attributeEx = "id=\"multiple-departmentsR\" multiple=\"multiple\" name=\"idDepartmentR\" data-validation=\"required\"";
						$classEx = "js-departmentsR";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Proyecto: @endcomponent
					@php
						$options = collect();
						if($request->idProject != "")
						{
							$options = $options->concat([["value" => $request->idProject, "selected" => "selected", "description" => $request->requestProject->proyectName]]);
						}
						$attributeEx = "id=\"multiple-projectsR\" name=\"project_id\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-projects removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2 code-WBS @if(!isset($request->code_wbs)) hidden @endif ">
					@component("components.labels.label") Código WBS: @endcomponent
					@php
						$options = collect();
						if($request->code_wbs != "")
						{
							$options = $options->concat([["value"=>$request->code_wbs, "selected"=>"selected", "description"=>$request->wbs->code_wbs]]);
						}
						$attributeEx = "name=\"code_wbs\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-code_wbs removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2 code-EDT @if(!isset($request->code_edt)) hidden @endif ">
					@component("components.labels.label") Código EDT: @endcomponent
					@php
						$options = collect();
						if($request->code_edt != "")
						{
							$options = $options->concat([["value" => $request->code_edt, "selected" => "selected", "description" => $request->edt->code." (".$request->edt->description.")"]]);
						}
						$attributeEx = "name=\"code_edt\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-code_edt removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
			@endcomponent
			<!-- AQUI MUESTRA LOS DATOS A RECLASIFICAR -->
			@component("components.labels.title-divisor") RECLASIFICACIÓN <span class="help-btn" id="help-btn-classify"></span> @endcomponent
			@php
				$subtotalFinal = $ivaFinal = $totalFinal = 0;
				$modelHead = ["Reclasificar","Concepto","Clasificación de gasto"];
				$modelBody =[];
				foreach(App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
				{
					$options = collect();
					$options = $options->concat([["value" => $refundDetail->idAccount, "selected" => "selected", "description" => $refundDetail->account->account." - ".$refundDetail->account->description." (".$refundDetail->account->content.")"]]);
					$body =
					[
						"classEx" => "tr",
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"classExContainer" 	=> "inline-flex",
									"kind"				=> "components.inputs.checkbox",
									"classEx"			=> "add-article d-none",
									"attributeEx"		=> "type=\"checkbox\" id=\"id_article_".$refundDetail->idRefundDetail."\" value=\"1\" name=\"add-article_".$refundDetail->idRefundDetail."\"",
									"id"				=> "id_article_".$refundDetail->idRefundDetail."",
									"classExLabel"		=> "check-small request-validate",
									"label"				=> "<span class=\"icon-check\"></span>"
								]
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" => "components.labels.label",
									"label" => htmlentities($refundDetail->concept),
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".$refundDetail->idRefundDetail."\"",
									"classEx"     => "idRefundDetailOld",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".htmlentities($refundDetail->concept)."\"",
									"classEx"     => "conceptOld",
								],
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".$refundDetail->idAccount."\"",
									"classEx"     => "accountOld_id",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".($refundDetail->account->account." - ".$refundDetail->account->description." (".$refundDetail->account->content.")")."\"",
									"classEx"     => "accountOld_name",
								],
								[
									"kind"        => "components.inputs.select",
									"attributeEx" => "multiple=\"multiple\" name=\"account_idR\"",
									"classEx"     => "js-accountsR account",
									"options"     => $options,
								],
								
							]
						],
					];
					$modelBody[] = $body;
				}
				$options = collect();
				$modelBody [] = 
				[
					"classEx"=> "tr add-label",
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"label" => "",
							],
						],
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"	=> "components.labels.label",
								"label"	=> "Etiquetas de reclasificación:"
							],
						],
					],
					[
						"classEx" => "td",
						"content" =>
						[
							[
								"kind"        => "components.inputs.select",
								"attributeEx" => "multiple=\"multiple\" name=\"idLabelsReview[]\"",
								"classEx"     => "js-labelsR labelsNew",
								"options"     => $options,
							]
						],
					],
				];
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
				@slot("attributeEx")
					id="table"
				@endslot
				@slot("classEx")
					table
					mt-6
				@endslot
				@slot("classExBody")
					request-validate
				@endslot
				@slot('attributeExBody')
					id="body-concepts-classify"
				@endslot
			@endcomponent

			<div class="flex justify-center mt-2 mb-6">
				@component("components.buttons.button", ["variant" => "warning"])
					@slot("classEx")
						reclassify
					@endslot
					@slot("attributeEx")
						type="button" 
						title="Agregar"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
							
			<!-- AQUI SE VEN LOS RECLASIFICADOS CON BOTON PARA AÑADIR ETIQUETAS -->
			@component("components.labels.title-divisor") RELACIÓN DE DOCUMENTOS APROBADOS <span class="help-btn" id="help-btn-add-label"></span> @endcomponent
			@php
				$modelHead = 
				[
					[
						["value" => "Concepto", "show" => "true"],
						["value" => "Clasificación de gasto", "show" => "true"],
						["value" => "Etiquetas"],
						["value" => "Acción"]
					]
				];
				$modelBody = [];
			@endphp
			@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead"	=> $modelHead,
				"themeBody" => "striped"
			])
				@slot('attributeEx')
					id="table" 
				@endslot
				@slot('classEx')
					table-refunds
					my-6
				@endslot
				@slot('attributeExBody')
					id="body-concepts-reclassify" 
				@endslot
				@slot('classExBody')
					request-validate
				@endslot
			@endcomponent
			<!-- ignoramos -->
			@component('components.labels.label') 
					@slot('classEx')
						text-center
					@endslot
						Comentarios (Opcional)
				@endcomponent
				@component("components.inputs.text-area")
					@slot("attributeEx")
						cols="90" 
						rows="10" 
						name="checkCommentA"
					@endslot
					@slot("classEx")
						text-area
					@endslot
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			@component("components.labels.label")
				@slot('classEx')
					text-center
				@endslot
				Comentarios (Opcional)
			@endcomponent
			@component("components.inputs.text-area")
				@slot("classEx")
					text-area
				@endslot
				@slot("attributeEx")
					cols="90"
					rows="10"
					name="checkCommentR"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center my-6">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					w-48
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
					value="ENVIAR SOLICITUD"
				@endslot
				ENVIAR SOLICITUD
			@endcomponent
			@component("components.buttons.button", ["variant"=>"reset", "buttonElement"=>"a"])
				@slot("classEx")
					load-actioner
					w-48
					md:w-auto
					text-center
				@endslot
				@slot("attributeEx")
					@if(isset($option_id)) 
						href="{{ url(getUrlRedirect($option_id)) }}"
					@else 
						href="{{ url(getUrlRedirect($child_id)) }}" 
					@endif
				@endslot
				REGRESAR
			@endcomponent
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
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
						if($('input#aprobar').is(':checked'))
						{
							if (($('#body-concepts-classify .tr').length-1) != $('#body-concepts-reclassify .tr').length) 
							{
								swal('', 'Por favor agregue los conceptos faltantes.', 'error');
								return false;
							}
							else
							{
								swal('Cargando',{
									icon: '{{ asset(getenv("LOADING_IMG")) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
								
								return true;
							}
						}
						else
						{
							swal('Cargando',{
								icon: '{{ asset(getenv("LOADING_IMG")) }}',
								button: false,
								closeOnClickOutside: false,
								closeOnEsc: false
							});
							
							return true;
						}
					}
					else
					{
						swal('', 'Por favor seleccione al menos un estado.', 'error');
						return false;
					}
				},
				onError  : function($form)
				{
					swal("", '{{ Lang::get("messages.form_error") }}', "error");
				}
			});
			$('.js-enterprisesR').on('select2:unselecting', function (e)
			{
				e.preventDefault();
				swal({
					title		: "Eliminar Empresa",
					text		: "Si elimina la empresa, deberá reclasificar todos los conceptos.",
					icon		: "warning",
					buttons		: true,
					dangerMode	: true,
				})
				.then((willClean) =>
				{
					if(willClean)
					{
						$(this).val(null).trigger('change');
						$('#body-concepts-classify .tr').removeClass('hidden').addClass('block');
						$('#body-concepts-reclassify').empty();
						$('.js-accountsR').empty();
						generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
						count = 0;
					}
					else{
						swal.close();
					}
				});
			});
			count = 0;
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").removeClass("block").addClass("hidden");
					$("#aceptar").removeClass("hidden").addClass("block");
					@php
						$selects = collect([
							[
								"identificator"         => ".js-enterprisesR", 
								"placeholder"           => "Seleccione la empresa", 
								"maximumSelectionLength"=> "1"
							],
							[
								"identificator"         => ".js-areasR",
								"placeholder"           => "Seleccione la dirección", 
								"maximumSelectionLength"=> "1"
							],
							[
								"identificator"         => ".js-departmentsR",
								"placeholder"           => "Seleccione el departamento", 
								"maximumSelectionLength"=> "1"
							]
						]);
					@endphp
					@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
					generalSelect({'selector': '.js-projects', 'model': 41, 'option_id':{{$option_id}} });
					generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
					generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : -1});
					generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").removeClass("block").addClass("hidden");
					$("#rechaza").removeClass("hidden").addClass("block");
				}
			})
			.on('change','.js-enterprisesR',function()
			{
				$('.js-accountsR').empty();
				$('.approve-classify').hide();
				identerprise = $(this).find('option:selected').val();
				if(identerprise != null)
				{
					enterprisesR_old_id = $('.enterprisesR_old_id').val();
					if(enterprisesR_old_id == identerprise)
					{
						$('.account').each(function()
						{
							oldId 			= $(this).parents('.tr').find('.accountOld_id').val();
							oldDescription 	= $(this).parents('.tr').find('.accountOld_name').val();
							$(this).append('<option value='+oldId+' selected="selected">'+oldDescription+'</option>');
						});	
					}
				}
				generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
			})
			.on('change','.js-projects',function()
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
								$('.code-WBS').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_wbs', 'depends': '.js-projects', 'model': 1});
							}
							else
							{
								$('.js-code_wbs, .js-code_edt').html('');
								$('.code-WBS, .code-EDT').removeClass('block').addClass('hidden');
							}					
						}
					});
				} 
				else
				{
					$('.js-code_wbs, .js-code_edt').html('');
					$('.code-WBS, .code-EDT').removeClass('block').addClass('hidden');	
				}
			})
			.on('change','.js-code_wbs',function()
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
								$('.code-EDT').removeClass('hidden').addClass('block');
								generalSelect({'selector': '.js-code_edt', 'depends': '.js-code_wbs', 'model': 15});
							}
							else
							{
								$('.js-code_edt').html('');
								$('.code-EDT').removeClass('block').addClass('hidden');
							}					
						}
					});
				}
				else
				{
					$('.js-code_edt').html('');
					$('.code-EDT').removeClass('block').addClass('hidden');
				}
			})
			.on('click','.reclassify',function()
			{
				errorSwalElements = true;
				$('.add-article').each(function(){
					if($(this).is(':checked')) {
						errorSwalElements	= false;
						tr					= $(this).parents('.tr');
						idRefundDetailNew 	= tr.find('.idRefundDetailOld').val();
						conceptNew  		= tr.find('.conceptOld').val();
						accountIdNew 		= tr.find('.account option:selected').val();
						accountNameNew 		= tr.find('.account option:selected').text();
						accountIdOld		= tr.find('.accountOld_id').val();
						accountNameOld		= tr.find('.accountOld_name').val();
						if (accountIdNew == null)
						{
							swal('','Por favor seleccione una cuenta.','error');
						}
						else
						{
							$(this).prop( "checked",false); 
							$(this).parents('.tr').removeClass('block').addClass('hidden');
							@php
								$modelHead	= 
								[
									[
										["value" => "Concepto"],
										["value" => "Clasificación del gasto"],
										["value" => "Etiquetas"],
										["value" => "Acción"],
									]
								];
								$modelBody	= [];
								$modelBody [] = [
									"classEx" => "tr",
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "idRefundDetailNew",
												"attributeEx" => "type=\"hidden\" value=\"\"",
											],
											[
												"kind"  		=> "components.labels.label",
												"classEx" 		=> "conceptNewLabel"
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "conceptNew",
												"attributeEx" => "type=\"hidden\" value=\"\"",
											],
										],
									],
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind"  		=> "components.labels.label",
												"classEx" 		=> "accountNameNew"
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "accountIdNew",
												"attributeEx" => "type=\"hidden\" value=\"\"",
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "accountNameOld",
												"attributeEx" => "type=\"hidden\" value=\"\"",
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "accountIdOld",
												"attributeEx" => "type=\"hidden\" value=\"\"",
											],
										],
									],
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind"  		=> "components.labels.label",
												"classEx" 		=> "labelsAssignSpan"
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "t_idRefundDetail",
												"attributeEx" => "type=\"hidden\" name=\"t_idRefundDetail[]\" value=\"\"",
											],
											[
												"kind" => "components.inputs.input-text",
												"classEx" => "t_idAccountR",
												"attributeEx" => "type=\"hidden\" name=\"t_idAccountR[]\" value=\"\"",
											],
										],
									],
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind"  		=> "components.buttons.button",
												"variant"	 	=> "red",
												"label" 		=> '<span class="icon-x delete-span"></span>',
												"attributeEx" 	=>	"type=\"button\"",
												"classEx" 		=> "delete-item"
											],
										],
									],
								];
								$table2 = view('components.tables.table', [
										"modelHead" => $modelHead,
										"modelBody" => $modelBody,
										"themeBody" => "striped", 
										"noHead"	=> "true"
									])->render();
							@endphp
							table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
							row = $(table);
							row.find('.idRefundDetailNew').val(idRefundDetailNew);
							row.find('.conceptNewLabel').text(conceptNew);
							row.find('.conceptNew').val(conceptNew);
							row.find('.accountNameNew').text(accountNameNew);
							row.find('.accountIdNew').val(accountIdNew);
							row.find('.accountNameOld').val(accountNameOld);
							row.find('.accountIdOld').val(accountIdOld);
							row.find('.labelsAssignSpan').append($('<span class="labelsAssign" id="labelsAssign'+count+'"></span>'));
							row.find('.t_idRefundDetail').val(idRefundDetailNew);
							row.find('.t_idAccountR').val(accountIdNew);
							$('#body-concepts-reclassify').append(row);
							selecteds = $('select[name="idLabelsReview[]"] option:selected').length;
							if(selecteds > 0)
							{							
								$('select[name="idLabelsReview[]"] option:selected').each(function(i,v){
									if (i === (selecteds - 1))
									{
										separator = '';
									}
									else
									{
										separator = ', ';
									}
									label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
									labelText = $('<label></label>').text($(this).text()+separator);
									$('#labelsAssign'+count).append(label);
									$('#labelsAssign'+count).append(labelText);
								});
							}
							else
							{
								labelText = $('<label></label>').text('Sin etiquetas');
								$('#labelsAssign'+count).append(labelText);
							}
							count++;
						}
					}
				})
				count_label = 0;
				$('#body-concepts-classify .tr').each(function(i)
				{
					if($(this).is(':visible'))
					{
						count_label++;
					}
				});
				if(count_label == 1)
				{
					$('.add-label').addClass('hidden');
				}
				if(count_label == 0)
				{
					swal('', 'Ya no cuenta con elementos para agregar etiquetas.', 'info');
					return false;
				}
				if(errorSwalElements){
					swal('', 'Por favor seleccione los elementos que quiera agregar y la(s) etiqueta(s) si es necesario.', 'error');
				}
				else
				{
					$('.js-labelsR').val(null).trigger('change');
				}
			})
			.on('click','.delete-item',function()
			{
				idRefundDetailNew = $(this).parents('#body-concepts-reclassify .tr').find('.idRefundDetailNew').val();
				idaccount = $(this).parents('.tr').find('.accountIdOld').val();
				if($('.add-label').is(':visible') == false)
				{
					$('.add-label').removeClass('hidden');
				}
				$('.idRefundDetailOld').each(function(){
					if($(this).val() == idRefundDetailNew)
					{
						$(this).parents('.tr').removeClass('hidden');
						$(this).parents('.tr').find('.js-accountsR').val(idaccount).trigger('change');
					}
				});
				$(this).parents('.tr').remove();

				$('#body-concepts-reclassify .tr').each(function(i,v)
				{
					$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
					$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
				});
				count = $('#body-concepts-reclassify .tr').length;
			})
			.on('click','#help-btn-classify',function()
			{
				swal('Ayuda','Debe aprobar o editar la clasificación del gasto en caso de no ser la correcta que se muestra para cada concepto.','info');
			})
			.on('click','#help-btn-add-label',function()
			{
				swal('Ayuda','En este apartado debe agregar una o varias etiquetas por concepto.','info');
			})
		});
	</script>
@endsection
