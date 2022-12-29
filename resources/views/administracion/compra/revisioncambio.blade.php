@extends('layouts.child_module')

@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	@if(!empty($request->purchases->first()))
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
								font-bold
							@endslot
							FOLIO: 
						@endcomponent 
						@component('components.labels.label')
							@slot('classEx')
								px-2
							@endslot 
							{{ $request->new_folio }} 
						@endcomponent
					</div>
					@if($request->purchases->first()->requisitionRequest->idProject == 75)
						<div class="flex flex-row">
							@component('components.labels.label') 
								@slot('classEx')
									font-bold
								@endslot
								SUBPROYECTO/CÓDIGO WBS:
							@endcomponent 
							@component('components.labels.label')
								@slot('classEx')
									px-2
								@endslot 
								{{ $request->purchases->first()->requisitionRequest->requisition->wbs->code_wbs }}.
							@endcomponent
						</div>
						@if($request->purchases->first()->requisitionRequest->requisition->edt()->exists())
							<div class="flex flex-row">
								@component('components.labels.label') 
									@slot('classEx')
										font-bold
									@endslot
									CÓDIGO EDT:
								@endcomponent 
								@component('components.labels.label')
									@slot('classEx')
										px-2
									@endslot 
									{{ $request->purchases->first()->requisitionRequest->requisition->edt()->exists() ? $request->purchases->first()->requisitionRequest->requisition->edt->fullName() : '' }}.
								@endcomponent
							</div>
						@endif
					@endif
				@endslot
			@endcomponent
		@endif
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestAccount = App\Account::find($request->account);
		$modelTable    = 
		[
			[
				"Folio:", $request->new_folio != null ? $request->new_folio : $request->folio
			],
			[
				"Título y fecha:", ($request->purchases->count() > 0 && $request->purchases->first()->title != '' ? htmlentities($request->purchases->first()->title).($request->purchases->first()->datetitle != "" ? " - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->purchases->first()->datetitle)->format('d-m-Y') : '') : ''),
			],
			[
				"Número de Orden:", empty($request->purchases->first()) ? "---" : htmlentities($request->purchases->first()->numberOrder),
			],
			[
				"Fiscal:", $request->taxPayment == 1 ? "Si" : "No"
			],
			[
				"Solicitante:", $request->requestUser->fullName(),
			],
			[
				"Elaborado por:", $request->elaborateUser->fullName(),
			],
			[
				"Empresa:", App\Enterprise::find($request->idEnterprise)->name
			],
			[
				"Dirección:", App\Area::find($request->idArea)->name
			],
			[
				"Departamento:", App\Department::find($request->idDepartment)->name
			],
			[
				"Clasificación del gasto:", $requestAccount->account." - ".$requestAccount->description
			],
			[
				"Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'
			]
		];
	@endphp
	@component("components.templates.outputs.table-detail", 
	[
		"modelTable" => $modelTable, 
		"title"      => "Detalles de la Solicitud"
	]) 
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL PROVEEDOR @endcomponent
	@php
		if($request->purchases->count() > 0 && $request->purchases->first()->provider()->exists())
		{
			$modelTable =
			[
				"Razón Social " => $request->purchases->first()->provider->businessName,
				"RFC "          => $request->purchases->first()->provider->rfc,
				"Teléfono "     => $request->purchases->first()->provider->phone,
				"Calle "        => $request->purchases->first()->provider->address,
				"Número "       => $request->purchases->first()->provider->number,
				"Colonia "      => $request->purchases->first()->provider->colony,
				"Ciudad "       => $request->purchases->first()->provider->city,
				"Estado "       => App\State::find($request->purchases->first()->provider->state_idstate)->description,
				"Contacto "     => $request->purchases->first()->provider->contact,
				"Beneficiario " => $request->purchases->first()->provider->beneficiary,
				"Otro "         => $request->purchases->first()->provider->commentaries
			];
		}
		else
		{
			$modelTable =
			[
				"Razón Social " => "",
				"RFC " => "",
				"Teléfono " => "",
				"Calle " => "",
				"Número " => "",
				"Colonia " => "",
				"Ciudad " => "",
				"Estado " => "",
				"Contacto " => "",
				"Beneficiario " => "",
				"Otro " => ""
			];
		}
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
	@php
		$body 	   = [];
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
				["value" => "IBAN"],
				["value" => "BIC/SWIFT"],
				["value" => "Convenio"]
			]
		];
		if($request->purchases->count() > 0 &&  $request->purchases->first()->provider()->exists())
		{
			$classEx = "";
			foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
			{
				if($request->purchases->first()->provider_has_banks_id == $bank->id) 
				{
					$classEx = "marktr";
				}
				
				if($bank->iban == "")
				{
					$iban = "---";
				}
				else
				{
					$iban = $bank->iban;
				}
				
				if($bank->bic_swift=='')
				{
					$swift = "---";
				}
				else
				{
					$swift = $bank->bic_swift;
				}
						
				if($bank->agreement=='')
				{
					$agreement = "---";
				}
				else
				{
					$agreement = $bank->agreement;
				}

				if($request->purchases->first()->provider_has_banks_id == $bank->id)
				{
					$value_idchecked = 1;
				}
				else 
				{
					$value_idchecked = 0; 
				}
				$body = 
				[
					"classEx" => $classEx,
					[
						"content" => 
						[
							"label" => $bank->bank->description
						]
					],
					[
						"content" => 
						[
							"label" => $bank->alias != "" ? $bank->alias : '---'
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->account != "" ? $bank->account : '---'
						]
					],
					[
						"content" => 
						[ 
							"label" => $bank->branch != "" ? $bank->branch : '---'
						]
					],
					[
						"content" => 
						[
							"label" => $bank->reference != "" ? $bank->reference : '---'
						]
					],
					[
						"content" => 
						[
							"label" => $bank->clabe != "" ? $bank->clabe : '---'
						]
					],
					[
						"content" => 
						[
							"label" => $bank->currency
						]
					],
					[
						"content" => 
						[ 
							"label" => $iban
						]
					],
					[
						"content" => 
						[ 
							"label" => $swift
						]
					],
					[
						"content" => 
						[
							"label" => $agreement
						]
					]
				];
				array_push($modelBody, $body);
			}
		}
	@endphp
	@component('components.tables.table',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
		"themeBody" 			=> "striped"
	])
		@slot('attributeExBody')
			id="table2"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor') 
		DATOS DEL PEDIDO 
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$countConcept = 1;
		$body 	      = [];
		$modelBody    = [];
		$modelHead    = 
		[
			[
				["value" => "#"],
				["value" => "Cantidad"],
				["value" => "Unidad"],
				["value" => "Descripción"],
				["value" => "Precio Unitario"],
				["value" => "IVA"],
				["value" => "Impuesto Adicional"],
				["value" => "Retenciones"],
				["value" => "Importe"]
			]
		];
		if(isset($request->purchases))
		{
			foreach($request->purchases->first()->detailPurchase as $detail)
			{
				$taxesConcept=0;
				foreach($detail->taxes as $tax)
				{
					$taxesConcept+=$tax->amount;
				}

				$retentionConcept=0;
				foreach($detail->retentions as $ret)
				{
					$retentionConcept+=$ret->amount;
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
							"label" => "$ ".number_format($detail->unitPrice,2)
						]
					],
					[
						"content" => 
						[
							"label" => "$ ".number_format($detail->tax,2)
						]
					],
					[
						"content" => 
						[
							"label" => "$ ".number_format($taxesConcept,2)
						]
					],
					[
						"content" => 
						[ 
							"label" => "$ ".number_format($retentionConcept,2)
						]
					],
					[
						"content" => 
						[ 
							"label" => "$ ".number_format($detail->amount,2)
						]
					]
				];
				array_push($modelBody, $body);
				$countConcept++;
			}
		}
	@endphp
	@component('components.tables.table',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
		"themeBody" 			=> "striped"
	])
		@slot('attributeExBody')
			id="table"
		@endslot
	@endcomponent
	
	@php
		$subtotal  		= "$ ".number_format(0,2,".",",");
		$iva       		= "$ ".number_format(0,2,".",",");
		$total     		= "$ ".number_format(0,2,".",",");
		$taxes_val 		= "$ ".number_format(0,2,".",",");
		$retentions_val = "$ ".number_format(0,2,".",",");
		$notas     		= "name=\"note\" placeholder=\"Ingrese una nota\" cols=\"80\" readonly=\"readonly\"";
		$textNotes 		= "";
		if(isset($request))
		{
			$subtotal = "$ ".number_format($request->purchases->first()->subtotales,2,".",",");
			$iva = "$ ".number_format($request->purchases->first()->tax,2,".",",");
			$taxes_val = "$ ".number_format($taxes,2);
			$retentions_val = "$ ".number_format($retentions,2); 
			$total = "$ ".number_format($request->purchases->first()->amount,2,".",",");
			$textNotes = $request->purchases->first()->notes;
			foreach($request->purchases->first()->detailPurchase as $detail)
			{				
				foreach($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
			}
			foreach($request->purchases->first()->detailPurchase as $detail)
			{					
				foreach($detail->retentions as $ret)
				{
					$retentions += $ret->amount;
				}
			}
		}
		$modelTable = 
		[
			[
				"label" => "Subtotal: ", 
				"inputsEx" => 
					[
						[
							"kind" 		  => "components.labels.label",
							"label"		  => $subtotal,
							"classEx"     => "h-10 py-2"
						],
						[
							"kind" 		  => "components.inputs.input-text",
							"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->subtotales,2,".",",") : "0.00")."\" readonly name=\"subtotal\"",
							"classEx"     => "removeInput"
						]
					]
			],
			[
				"label"            => "Impuesto Adicional: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $taxes_val,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($taxes,2) : "0.00")."\" readonly name=\"amountAA\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "Retenciones: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $retentions_val,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($retentions,2) : "0.00")."\" readonly name=\"amountR\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "IVA: ",	
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $iva,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->tax,2,".",",") : "0.00")."\" readonly name=\"totaliva\"",
											"classEx"     => "removeInput"
										]
									]
			],
			[
				"label"            => "TOTAL: ", 
				"inputsEx"		   => 
									[
										[
											"kind" 		  => "components.labels.label",
											"label"		  => $total,
											"classEx"     => "h-10 py-2"
										],
										[
											"kind" 		  => "components.inputs.input-text",
											"attributeEx" => "type=\"hidden\" value=\"".(isset($request) ? number_format($request->purchases->first()->amount,2,".",",") : "0.00")."\" readonly name=\"total\"",
											"classEx"     => "removeInput"
										]
									]
			],
		];
	@endphp
	@component('components.templates.outputs.form-details',[
		"modelTable" 		 => $modelTable,
		"attributeExComment" => $notas,
		"textNotes"          => $textNotes
	])
	@endcomponent
	@component('components.labels.title-divisor') 
		CONDICIONES DE PAGO
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		
		$modelTable =
		[
			"Referencia/Número de factura " => htmlentities($request->purchases->first()->reference),
			"Tipo de moneda " 				=> $request->purchases->first()->typeCurrency,
			"Fecha de pago " 				=> $request->PaymentDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->PaymentDate)->format('d-m-Y') : "",
			"Forma de pago " 				=> $request->purchases->first()->paymentMode,
			"Estatus de factura " 			=> $request->purchases->first()->billStatus,
			"Importe a pagar " 				=> "$ ".number_format($request->purchases->first()->amount,2)
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
	@component('components.labels.title-divisor') 
		DOCUMENTOS
		@slot('classExContainer')
			pb-4
		@endslot
	@endcomponent
	@php
		$body 	   = [];
		$no_result = true;
		$modelBody = [];
		$modelHead = ["Tipo de Documento" ,"Archivo", "Fecha"];
		if(count($request->purchases->first()->documents)>0)
		{
			foreach($request->purchases->first()->documents as $doc)
			{
				$body = 
				[
					[
						"content" => 
						[
							[
								"label" => $doc->name
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "secondary",
								"label"         => "Archivo",
								"buttonElement" => "a",
								"attributeEx"   => "target = \"_blank\" href = \"".url('docs/purchase/'.$doc->path)."\""
							]
						]
					],
					[ 
						"content" => 
						[
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y')
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
			$no_result = false;
		}
	@endphp
	@if($no_result)
		@component("components.labels.not-found", ["text" => "No se han encontrado documentos registrados"]) @endcomponent
	@else
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot('attributeExBody')
				id="banks-body"
			@endslot
		@endcomponent
	@endif
	
	@php
		$i					= 1;
		$idPurchase			= $request->purchases->first()->idPurchase;
		$partialPayments	= $request->purchases->first()->partialPayment;
		$editable			= false;
	@endphp
	<input value="0" class="partial_total" type="hidden">
	@include('administracion.compra.form.partial')
	@component("components.forms.form",
	[
		"attributeEx" => "id=\"container-alta\" method=\"post\" action=\"".route('purchase.review.update', $request->folio)."\"",
		"methodEx"	  => "PUT",
		"token"       => "true"
	])
		<div class="my-4">
			@component('components.containers.container-approval')
				@slot('attributeExLabel') id="label-inline" @endslot

				@slot('attributeExButton') name="status" value="4" id="aprobar" @endslot
				@slot('attributeExButtonTwo') name="status" value="6" id="rechazar" @endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			@component('components.labels.title-divisor') 
				ASIGNACIÓN DE ETIQUETAS
				@slot('classExContainer')
					pb-4
				@endslot
			@endcomponent
			<div class="flex flex-wrap justify-center w-full">
				<div class="flex-wrap w-full">
					@php						
						$attributeEx = "name=\"idLabelsReview[]\" multiple=\"multiple\"";
						$classEx = "js-labelsR labelsNew";
						
						$body 	    = [];
						$no_result = true;
						$modelBody = [];
						$modelHead = ["", "Cantidad" ,"Descripción"];
						$i         = 0;
						foreach($request->purchases->first()->detailPurchase as $detail)
						{
							$body = 
							[
								[
									"classEx" => "td",
									"content" => 
									[
										[
											"kind"             => "components.inputs.checkbox",
											"label"            => "<span class=icon-check></span>", 
											"attributeEx"      => "id=\"id_article_".$i."\" name=\"add-article_".$i."\" value=\"1\"",
											"classExContainer" => "inline-flex",
											"classEx"		   => "add-article",
											"classExLabel"	   => "request-validate"
										]
									]
								],
								[
									"classEx" => "td",
									"content" => 
									[
										"label" => $detail->quantity.' '.$detail->unit
									]
								],
								[
									"classEx" => "td",
									"content" => 
									[
										[
											"label" => htmlentities($detail->description),
										],
										[
											"kind"        => "components.inputs.input-text",
											"classEx"     => "idDetailPurchaseOld",
											"attributeEx" => "type = \"hidden\" value = \"". $detail->idDetailPurchase."\""
										],
										[
											"kind"        => "components.inputs.input-text",
											"classEx"     => "quantityOld",
											"attributeEx" => "type = \"hidden\" value = \"".$detail->quantity.' '.$detail->unit."\""
										],
										[
											"kind"        => "components.inputs.input-text",
											"classEx"     => "conceptOld",
											"attributeEx" => "type = \"hidden\" value = \"".htmlentities($detail->description)."\""
										]
									]
								]
							];
							array_push($modelBody, $body);
							$i++; 
						}
						$body = 
						[
							[
								"content" => 
								[
									[
										"kind"             => "components.labels.label",
										"label"            => ""
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"             => "components.labels.label",
										"label"            => "Etiquetas:"
									],
								]
							],
							[
								"content" => 
								[
									[
										"kind"             => "components.inputs.select",										
										"attributeEx" 	   => $attributeEx, 
										"classEx" 		   => $classEx
									]
								]	
							]
						];
						array_push($modelBody, $body);
					@endphp
					@component('components.tables.alwaysVisibleTable',[
						"modelHead" 			=> $modelHead,
						"modelBody" 			=> $modelBody,
						"themeBody" 			=> "striped"
					])
						@slot('attributeExBody')
							id="tbody-concepts"
						@endslot
					@endcomponent

					<div class="flex justify-center mt-2 mb-6">
						@component("components.buttons.button", ["variant" => "warning"])
							@slot("classEx")
								add-label
							@endslot
							@slot("attributeEx")
								type="button" 
							@endslot
							<span class="icon-plus"></span>
							<span>Agregar</span>
						@endcomponent
					</div>
				</div>
			</div>
			@component('components.labels.title-divisor') 
				ETIQUETAS ASIGNADAS
				@slot('classExContainer')
					pb-4
				@endslot
			@endcomponent
			@php
				$body 	    = [];
				$modelBody = [];
				$modelHead = ["Concepto" ,"Etiquetas", ""];
			@endphp
			@component('components.tables.alwaysVisibleTable',[
				"modelHead" 			=> $modelHead,
				"modelBody" 			=> $modelBody
			])
				@slot('attributeExBody')
					id="tbody-conceptsNew"
				@endslot
			@endcomponent
			<span id="labelsAssign">
				
			</span>
			@component("components.containers.container-form")	
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([['value'=>$enterprise->id, 'description'=>$description, 'selected' => 'selected']]);
							}
							else
							{
								$options = $options->concat([['value'=>$enterprise->id, 'description'=>$description]]);
							}
						}
						$attributeEx = "name=\"idEnterpriseR\" id=\"multiple-enterprisesR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-enterprisesR";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Area: @endcomponent
					@php
						$options = collect();
						foreach(App\Area::orderName()->where('status','ACTIVE')->get() as $area)
						{
							if($request->idArea == $area->id)
							{
								$options = $options->concat([['value'=>$area->id, 'description'=>$area->name, 'selected' => 'selected']]);
							}
							else
							{
								$options = $options->concat([['value'=>$area->id, 'description'=>$area->name]]);
							}
						}
						$attributeEx = "name=\"idAreaR\" id=\"multiple-areasR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-areasR";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$options = collect();
						foreach(App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							$options = $options->concat([['value' => $department->id, 'description' => $department->name, 'selected' => ($request->idDepartment == $department->id ? "selected" : "")]]);
						}
						$attributeEx = "id=\"multiple-departmentsR\" multiple=\"multiple\" name=\"idDepartmentR\" data-validation=\"required\"";
						$classEx = "js-departmentsR";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent

				</div>
				<div class="col-span-2">
					@component('components.labels.label') Cuenta: @endcomponent
					@php
						$options = collect();
						foreach(App\Account::where('idEnterprise',$request->idEnterprise)->where('selectable',1)->get() as $account)
						{
							$description = $account->account.' - '.$account->description." (".$account->content.")";
							if($request->account == $account->idAccAcc)
							{
								$options = $options->concat([['value'=>$account->idAccAcc, 'description'=>$description, 'selected' => 'selected']]);
							}
							else
							{
								$options = $options->concat([['value'=>$account->idAccAcc, 'description'=>$description]]);
							}
						}
						$attributeEx = "name=\"accountR\" id=\"multiple-accountsR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-accountsR removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')  Proyecto: @endcomponent
					@php
						$options = collect();
						foreach(App\Project::orderName()->whereIn('status',[1,2])->get() as $project)
						{
							if($request->idProject == $project->idproyect)
							{
								$options = $options->concat([['value'=>$project->idproyect, 'description'=>$project->proyectName, 'selected' => 'selected']]);
							}
							else
							{
								$options = $options->concat([['value'=>$project->idproyect, 'description'=>$project->proyectName]]);
							}
						}
						$attributeEx = "name=\"project_id\" id=\"multiple-projectsR\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-projectsR removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Comentarios (Opcional) @endcomponent
					@component("components.inputs.text-area")
						@slot('attributeEx')
							cols = "90" 
							rows = "10" 
							name = "checkCommentA"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			@component('components.labels.label') Comentarios (Opcional) @endcomponent
			@component("components.inputs.text-area")
				@slot('attributeEx')
					cols = "90" 
					rows = "10" 
					name = "checkCommentR"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('attributeEx') 
					type="submit"  name="enviar"
				@endslot
				@slot('classEx') 
					w-48 md:w-auto
				@endslot
				ENVIAR SOLICITUD
			@endcomponent	
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
		</div>
	@endcomponent
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		function validate()
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
							enterprise	= $('#multiple-enterprisesR').val();
							area		= $('#multiple-areasR').val();
							department	= $('#multiple-departmentsR').val();
							account		= $('#multiple-accountsR').val();
							if(enterprise == '' || area == '' || department == '' || account == '')
							{
								swal('', 'Por favor agregue los campos faltantes.', 'error');
								return false;
							}
							else if (($('#tbody-conceptsNew .tr').length) != $('#tbody-concepts .tr').length-1)  // || $('.idDetailPurchaseNew').val()
							{
								swal('', 'Por favor agregue los conceptos faltantes.', 'error');
								return false;
							}
							else
							{
								swal("Cargando",{
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
							swal("Cargando",{
									icon: '{{ asset(getenv('LOADING_IMG')) }}',
									button: false,
									closeOnClickOutside: false,
									closeOnEsc: false
								});
							return true;
						}
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
		}
		$(document).ready(function()
		{
			generalSelect({'selector': '.js-users', 'model': 36});
			generalSelect({'selector': '.js-accounts', 'depends':'.js-enterprises', 'model':10});
			count = 0;
			validate();
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#aceptar").removeClass("hidden");
					$("#rechaza").addClass("hidden");
					generalSelect({'selector': '.js-accountsR', 'depends':'.js-enterprisesR', 'model':4});
					generalSelect({'selector': '.js-projectsR', 'model': 17, 'option_id': {{$option_id}} });
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : -1});
					generalSelect({'selector': '.js-projectsR', 'model': 21});
					@php
						$selects = collect([
							[
								"identificator"          => ".js-enterprisesR", 
								"placeholder"            => "Seleccione la empresa", 
								"maximumSelectionLength" => "1"
							],
							[
								"identificator"          => ".js-areasR", 
								"placeholder"            => "Seleccione la dirección", 
								"maximumSelectionLength" => "1"
							],
							[
								"identificator"          => ".js-departmentsR", 
								"placeholder"            => "Seleccione el departamento", 
								"maximumSelectionLength" => "1"
							],						
						]);
					@endphp
					@component("components.scripts.selects",["selects" => $selects])
					@endcomponent
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#rechaza").removeClass("hidden");
					$("#aceptar").addClass("hidden");
				}
			})
			.on('click','.add-label',function()
			{
				errorSwalElements=true;
				$('.add-article').each(function()
				{
					if($(this).is(':checked')) 
					{
						errorSwalElements=false;
						$(this).prop( "checked",false); 
						$(this).parents('.tr').hide();
						tr					= $(this).parents('.tr');
						concept 			= tr.find('.conceptOld').val();
						quantity  			= tr.find('.quantityOld').val();
						idDetailPurchase 	= tr.find('.idDetailPurchaseOld').val().trim();
						@php
							$modelBody = 
							[
								[
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind"	  => "components.labels.label",
												"classEx" => "concept-data"
											],
											[
												"label" => "<input type=\"hidden\" class=\"conceptLabel\">"
											],
											[
												"label" => "<input type=\"hidden\" class=\"quantityLabel\">"
											]
										]
									],
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"label" => "<input type=\"hidden\" name=\"t_idDetailPurchase[]\" class=\"idDetailPurchaseLabel\">"
											],
											[
												"label" => "<span class=\"labelsAssign\"></span>"
											]
										]
									],
									[
										"classEx" => "td",
										"content" => 
										[
											[
												"kind"		=> "components.buttons.button",
												"variant"	=> "red",
												"classEx"	=> "delete-item",
												"label" 	=> "<span class=\"icon-x delete-span\"></span>"
											]
										]
									]
								]
							];
							$modelHead = ["", "Cantidad" ,"Descripción"];
							$tableRow  = view('components.tables.alwaysVisibleTable',[
											"modelHead" => $modelHead,
											"modelBody" => $modelBody,
											"noHead"	=> true
										])->render();
						@endphp
						trNew = $('{!!preg_replace("/(\r)*(\n)*/", "", $tableRow)!!}');
						concept = String(concept).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
						trNew.find('.concept-data').html(concept);
						trNew.find('.conceptLabel').val(concept);
						trNew.find('.quantityLabel').val(quantity);

						trNew.find('[name="t_idDetailPurchase[]"]').val(idDetailPurchase);
						trNew.find('.labelsAssign').attr("id", 'labelsAssign'+count);

						$('#tbody-conceptsNew').append(trNew);

						$('select[name="idLabelsReview[]"] option:selected').each(function()
						{
							label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
							labelText = $('<label></labell').text($(this).text()+', ');
							$('#labelsAssign'+count).append(label);
							$('#labelsAssign'+count).append(labelText);
						});

						count++;

					}
				})
				$('.js-labelsR').val(null).trigger('change');

				if(errorSwalElements)
				{
					swal('', 'Seleccione los elementos que les quiera agregar esta(s) etiqueta(s)', 'error');
				}
			})
			.on('click','.delete-item',function()
			{
				
				idDetailPurchaseOld	= $(this).parents('#tbody-conceptsNew .tr').find('.idDetailPurchaseLabel').val();
				$('.idDetailPurchaseOld').each(function(){
					if($(this).val().trim()==idDetailPurchaseOld)
					{
						$(this).parents('.tr').show();
					}
				});
				$(this).parents('.tr').remove();

				$('#tbody-conceptsNew .tr').each(function(i,v)
				{
					$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
					$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
				});
				count = $('#tbody-conceptsNew .tr').length;
			})
			.on('change','.js-enterprisesR',function()
			{
				$('.js-accountsR').empty();
			})
			;
		});
	</script>
@endsection