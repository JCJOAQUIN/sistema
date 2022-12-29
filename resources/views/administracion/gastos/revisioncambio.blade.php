@extends('layouts.child_module')
@section('data')
	@php
		$taxes 	= 0;
		$taxes3 = 0;
		$docs 	= 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable = 
		[
			["Folio:", 			$request->folio],
			["Título y fecha:", htmlentities($request->expenses->first()->title).' '.Carbon\Carbon::createFromFormat('Y-m-d',$request->expenses->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",	$request->requestUser->fullName()],
			["Elaborado por:",	$request->elaborateUser->fullName()],
			["Empresa:",		isset($request->requestEnterprise->name)		? $request->requestEnterprise->name		: '---'],
			["Dirección:",		isset($request->requestDirection->name)			? $request->requestDirection->name		: '---'],
			["Departamento:",	isset($request->requestDepartment->name)		? $request->requestDepartment->name 	: '---'],
			["Proyecto:",		isset($request->requestProject->proyectName)	? $request->requestProject->proyectName : '---'],
			["Código WBS:",		isset($request->wbs) ? $request->wbs->code_wbs : '---'],
			["Código EDT:",		isset($request->edt) ? $request->edt->fullName() : '---']
		];
	@endphp
	@component('components.templates.outputs.table-detail', [
		"modelTable"	=> $modelTable,
		"title"			=> "Detalles de la Solicitud"
		])
	@endcomponent
	@component('components.labels.title-divisor') DATOS DEL SOLICITANTE @endcomponent
	@php
		foreach($request->expenses as $expense)
		{	
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes3 += $tax->amount;
					}
				}
			}
			$varPaymentMethod 	= $expense->paymentMethod->method;
			$varReference		= ($expense->reference != "" ? htmlentities($expense->reference) : "---");
			$varCurrency	 	= $expense->currency;
			$varTotal			= '$ '.number_format($expense->total,2);
		}
		$varDescription = '';
		$varAlias		= '';
		$varCardNumber	= '';
		$varClabe		= '';
		$varAccount		= '';
		foreach($request->expenses as $expense)
		{
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$expense->idUsers)->get() as $bank)
			{
				if($expense->idEmployee == $bank->idEmployee)
				{
					$varDescription	= $bank->description;
					$varAlias		= $bank->alias!=null ? $bank->alias : '---';
					$varCardNumber	= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$varClabe		= $bank->clabe!=null ? $bank->clabe : '---';
					$varAccount		= $bank->account!=null ? $bank->account : '---';
				}
			}
		}	
		
		$modelTable = [
			"Forma de pago"		=> $varPaymentMethod,
			"Referencia"		=> $varReference,
			"Tipo de moneda"	=> $varCurrency,
			"Importe"			=> $varTotal,
			"Banco"				=> $varDescription,
			"Alias"				=> $varAlias,
			"Número de tarjeta"	=> $varCardNumber,
			"CLABE"				=> $varClabe,
			"Número de cuenta"	=> $varAccount,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single',["modelTable" => $modelTable])
	@endcomponent
	<div class="mt-4"> 
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				[
					["value" => "#"],
					["value" => "Concepto"],
					["value" => "Clasificación del gasto"],
					["value" => "Fiscal"],
					["value" => "Subtotal"],
					["value" => "IVA"],
					["value" => "Impuesto Adicional"],
					["value" => "Importe"],
					["value" => "Documento(s)"],
				]
			];

			$subtotalFinal = $ivaFinal = $totalFinal = 0;
			$countConcept  = 1;
			foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
			{
				$subtotalFinal	+= $expensesDetail->amount;
				$ivaFinal		+= $expensesDetail->tax;
				$totalFinal		+= $expensesDetail->sAmount;
				$varAccount 	= '';
				if(isset($expensesDetail->account))
				{
					$varAccount = $expensesDetail->account->account.' '.$expensesDetail->account->description;
				}
				$varTax = '';
				if($expensesDetail->taxPayment==1)
				{
					$varTax = "Si";
				}
				else
				{ 
					$varTax = "No";
				}  
				$taxes2 = 0;
				foreach($expensesDetail->taxes as $tax)
				{
					$taxes2 += $tax->amount;
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
							"label" => htmlentities($expensesDetail->concept)
						]
					],
					[
						"content" => 
						[
							"label" => $varAccount
						]
					],
					[
						"content" => 
						[
							"label" => $varTax
 						]
					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($expensesDetail->amount,2)
						]
					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($expensesDetail->tax,2)
						]
					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($taxes2,2)
						]
					],
					[
						"content" => 
						[
							"label" => '$ '.number_format($expensesDetail->sAmount,2) 
						]
					],
				];
				$test = '';
				if($expensesDetail->documents()->exists())
				{
					foreach($expensesDetail->documents as $doc)
					{
						if($doc->name != '')
						{
							$var_name = $doc->name;
						}
						else
						{
							$var_name= "Otro";
						}
						$test .= '<div class="nowrap">';
						$test .= '<div><label>'.$doc->date != '' ? Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y') : ''.'</label></div>';
						$test .= '<div><label>'.$var_name.'</label></div>';
						$test .= view('components.buttons.button',[
							"variant"		=> "dark-red",
							"buttonElement"	=> "a",
							"attributeEx"	=> "target=\"_blank\" type=\"button\" title=\"".$doc->path."\"".' '."href=\"".asset('docs/expenses/'.$doc->path)."\"",
							"label"			=> 'PDF'
						])->render();
						$test .= "</div>";
					}
				}
				else 
				{
					$test = "Sin documento";
				}
				$body[] 		= [ "content" => [ "label" => $test ]];
				$modelBody[]	= $body;
				$countConcept++;
			}
		@endphp
		@component('components.tables.table', [
			"modelBody" => $modelBody,
			"modelHead"	=> $modelHead
		])
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
			@slot('classExBody')
				request-valid
			@endslot
		@endcomponent
	</div>
	<div class="totales">
		@php
			$varSubTotal 		= '';
			$varIva				= '';
			$varTotal 			= '';
			$varSubTotalLabel 	= "$ 0.00";
			$varIvaLabel 		= "$ 0.00";
			$varTotalLabel 		= "$ 0.00";
			if($totalFinal!=0)
			{
				$varSubTotal 		= number_format($subtotalFinal,2);
				$varIva		 		= number_format($ivaFinal,2);
				$varTotal			= number_format($totalFinal,2);
				$varSubTotalLabel 	= '$ '.number_format($subtotalFinal,2);
				$varIvaLabel		= '$ '.number_format($ivaFinal,2);
				$varTotalLabel 		= '$ '.number_format($totalFinal,2);
			}  
			if(isset($request))
			{
				foreach($request->expenses->first()->expensesDetail as $detail)
				{
					foreach($detail->taxes as $tax)
					{
						$taxes += $tax->amount;
					}	 
				}
			}
			$varReintegro 		= '';
			$varReembolso 		= '';
			$varReintegroLabel 	= '$ 0.00';
			$varReembolsoLabel 	= '$ 0.00';
			if(isset($request->expenses))
			{
				foreach($request->expenses as $expense)
				{
					$varReintegro 		= number_format($expense->reintegro,2);
					$varReembolso 		= number_format($expense->reembolso,2);
					$varReintegroLabel 	= '$ '.number_format($expense->reintegro,2);
					$varReembolsoLabel	= '$ '.number_format($expense->reembolso,2);
				}
			}
			$modelTable = 
			[
				[
					"label" => "Subtotal:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varSubTotalLabel,
							"classEx" 	=> "my-2 subtotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "subtotal",	
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"subtotal\" name=\"subtotal\" value=\"".$varSubTotal."\""
						]
					]
				], 
				[
					"label" => "IVA:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varIvaLabel,
							"classEx" 	=> "my-2 ivaTotal"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "ivaTotal",	
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"iva\" name=\"iva\" value=\"".$varIva."\""
						]
					]
				], 
				[
					"label" => "Impuesto Adicional:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> '$ '.number_format($taxes,2),
							"classEx"	=> "my-2 labelAmount"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly name=\"amountAA\" value=\"".number_format($taxes,2)."\""
						]
					]
				], 
				[
					"label" => "Reintegro:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varReintegroLabel,
							"classEx" 	=> "my-2 reintegro"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"reintegro\" name=\"reintegro\" value=\"".$varReintegro."\"",
							"classEx" 		=> "reintegro"
						]
					]
				],
				[
					"label" => "Reembolso:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varReembolsoLabel,
							"classEx" 	=> "my-2 reembolso"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"attributeEx" 	=> "type=\"hidden\" readonly id=\"reembolso\" name=\"reembolso\" value=\"".$varReembolso."\"",
							"classEx" 		=> "reembolso"
						]
					]
				],
				[
					"label" => "TOTAL:", "inputsEx" =>
					[
						[
							"kind" 		=> "components.labels.label",
							"label" 	=> $varTotalLabel,
							"classEx" 	=> "my-2 total"
						],
						[
							"kind"			=> "components.inputs.input-text",
							"classEx" 		=> "total",	
							"attributeEx" 	=> "type=\"hidden\" id=\"total\" readonly name=\"total\" value=\"".$varTotal."\""
						]
					]
				]
			];
		@endphp
		@component('components.templates.outputs.form-details', [ "modelTable"	=> $modelTable]) @endcomponent
	</div>
	@component('components.forms.form',[ "attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('expenses.review.update',$request->folio)."\"", "methodEx" => "PUT"])
		<div class="my-4">
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status" id="aprobar" value="4"
				@endslot
				@slot('classExButton')
					approve
				@endslot
				@slot('attributeExButtonTwo')
					name="status" id="rechazar" value="6"
				@endslot
				@slot('classExButtonTwo')
					refuse
				@endslot
			@endcomponent
		</div>
		@foreach($request->expenses->first()->expensesDetail as $ED)
			@if($ED->idresourcedetail != null)
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" 
						name="idresourcedetail[]" 
						value="{{ $ED->idresourcedetail }}"
					@endslot
				@endcomponent
			@endif
		@endforeach
		<div id="aceptar" class="hidden">
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type="hidden"
							value="{{$request->idEnterprise}}"
						@endslot
						@slot('classEx')
							enterprisesR_old_id
						@endslot
					@endcomponent
					@component('components.labels.label') Empresa: @endcomponent
					@php
						$optionEnterprise = [];
						foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
						{
							if($request->idEnterprise == $enterprise->id)
							{
								$optionEnterprise[] = ["value" => $enterprise->id, "description" =>  strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name, "selected" => "selected"];
							}
							else
							{
								$optionEnterprise[] = ["value" => $enterprise->id, "description" =>  strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionEnterprise])
						@slot('attributeEx')
							id="multiple-enterprisesR"
							name="idEnterpriseR" 
							multiple="multiple"
							data-validation="required"
						@endslot
						@slot('classEx')
							js-enterprisesR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@php
						$optionArea = [];
						foreach(App\Area::where('status','ACTIVE')->orderBy('name','asc')->get() as $area)
						{
							if($request->idArea == $area->id)
							{
								$optionArea[] =["value" => $area->id, "description" => $area->name, "selected" => "selected"];
							}
							else
							{
								$optionArea[] =["value" => $area->id, "description" => $area->name];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionArea])
						@slot('attributeEx')
							id="multiple-areasR"
							multiple="multiple" 
							name="idAreaR"
							data-validation="required"
						@endslot
						@slot('classEx')
							js-areasR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@php
						$optionDepartament = [];
						foreach(App\Department::where('status','ACTIVE')->orderby('name','asc')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
						{
							if($request->idDepartment == $department->id)
							{
								$optionDepartament[] =["value" => $department->id, "description" => $department->name, "selected" => "selected"];
							}
							else
							{
								$optionDepartament[] =["value" => $department->id, "description" => $department->name];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionDepartament])
						@slot('attributeEx')
							id="multiple-departmentsR"
							multiple="multiple" 
							name="idDepartmentR"
							data-validation="required"
						@endslot
						@slot('classEx')
							js-departmentsR
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto/Contrato: @endcomponent
					@php
						$optionProject = [];
						foreach(App\Project::whereIn('status',[1,2])->orderBy('proyectName','asc')->get() as $project)
						{
							if($request->idProject == $project->idproyect)
							{
								$optionProject[] =["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"];
							}
							else
							{
								$optionProject[] =["value" => $project->idproyect, "description" => $project->proyectName];
							}
						}
					@endphp
					@component('components.inputs.select', ["options" => $optionProject])
						@slot('attributeEx')
							id="multiple-projectsR" 
							multiple="multiple" 
							name="project_id"
							data-validation="required"
						@endslot
						@slot('classEx')
							js-projectsR removeselect
						@endslot
					@endcomponent
				</div>
			@endcomponent
			<div>
				@component('components.labels.title-divisor') RECLASIFICACIÓN <span class="help-btn" id="help-btn-classify"></span>	@endcomponent
				<div class="mt-4 text-center">
					@php
						$body 		= [];
						$modelBody	= [];
						$modelHead	= [ "", "Concepto", "Clasificación de gasto" ];
						$subtotalFinal = $ivaFinal = $totalFinal = 0;
						foreach(App\ExpensesDetail::where('idExpenses',$request->expenses->first()->idExpenses)->get() as $expensesDetail)
						{
							$body = [ 	"classEx" => "tr_classify",
								[
									"content" =>
									[
										[
											"kind"				=> "components.inputs.checkbox",
											"classEx"			=> "add-article hidden",
											"attributeEx"		=> "type=\"checkbox\" id=\"id_article_$expensesDetail->idExpensesDetail\" value=\"1\" name=\"add-article_$expensesDetail->idExpensesDetail\"",
											"classExLabel"		=> "request-validate",
											"label"				=> "<span class=\"icon-check\"></span>",
											"classExContainer"	=> "my-2",
										]
									]
								],
								[
									"content" => 
									[
										[
											"label" => htmlentities($expensesDetail->concept),
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$expensesDetail->idExpensesDetail."\"",
											"classEx"		=> "idExpensesDetailOld"
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".htmlentities($expensesDetail->concept)."\"",
											"classEx"		=> "conceptOld"
										]
									]
								]
							];
							if(isset($expensesDetail->account))
							{
								$optionAccount = [];
								$account = App\Account::where('selectable',1)
									->where('idEnterprise',$request->idEnterprise)
									->where('idAccAcc',$expensesDetail->idAccount)
									->first();
								if($account != "" && $expensesDetail->idAccount == $account->idAccAcc)
								{
									$optionAccount[]= ["value" => $account->idAccAcc, "description" => $account->account.' '.$account->description.' '.$account->content, "selected" => "selected"];
								}
								array_push($body,
								[			
									"content" =>
									[
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$expensesDetail->idAccount."\"",
											"classEx"		=> "accountOld_id"
										],
										[
											"kind"			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" value=\"".$expensesDetail->account->account.''.$expensesDetail->account->description."\"",
											"classEx"		=> "accountOld_name"
										],
										[
											"kind"			=> "components.inputs.select",
											"attributeEx"	=> "multiple=multiple name=\"account_idR\"",
											"classEx"		=> "js-accountsR account",
											"options"		=> $optionAccount
										]
									]	
								]);
							}
							$modelBody[] = $body; 
						}
						$body = 
						[
							[
								"content" => [[ "label" => "" ]]
							],
							[
								"content" =>
								[
									[
										"label" => "Etiquetas de reclasificación",
									]
								]
							],
							[
								"content" => 
								[
									[
										"kind"			=> "components.inputs.select",
										"attributeEx"	=> "multiple=\"multiple\" name=\"idLabelsReview[]\"",
										"classEx"		=> "js-labelsR labelsNew",
										"options"		=> []
									]
								]
							]
						];
						$modelBody[] = $body; 
					@endphp
					@component('components.tables.alwaysVisibleTable', [
							"modelBody" => $modelBody,
							"modelHead"	=> $modelHead
						])
						@slot('attributeEx')
							id="table" 
						@endslot
						@slot('classEx')
							table
						@endslot
						@slot('attributeExBody')
							id="body-concepts-classify" 
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
					@component('components.buttons.button', ['variant' => 'warning'])
						@slot('classEx')
							add-label
						@endslot
						@slot('attributeEx')
							type="button" title="Agregar concepto"
						@endslot
						Agregar
					@endcomponent
				</div>
			</div>
			<div class="container-blocks view-reclassify hidden" id="container-data">
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Concepto: @endcomponent
						@component('components.labels.label')  
							@slot('classEx')
								concept-reclassify
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden"
							@endslot
							@slot('classEx')
								concept-reclassify
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden"
							@endslot
							@slot('classEx')
								idExpensesDetail-reclassify
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden"
							@endslot
							@slot('classEx')
								idAccountOld-reclassify
							@endslot
						@endcomponent
						@component('components.inputs.input-text')
							@slot('attributeEx')
								type="hidden"
							@endslot
							@slot('classEx')
								nameAccountOld-reclassify
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Clasificación del gasto: @endcomponent
						<select class="js-accountsR" class="input-text" multiple="multiple" name="account_idR" style="width: 98%;">
							@foreach(App\Account::where('selectable',1)->where('idEnterprise',$request->idEnterprise)->get() as $account)
								<option value="{{ $account->idAccAcc }}">{{ $account->account.' '.$account->description }} ({{ $account->content }})</option> 
							@endforeach
						</select>
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Etiquetas: @endcomponent
						<select class="js-labelsR" class="input-text labelsNew" multiple="multiple" name="idLabelsReview[]" style="width: 98%;">
							@foreach($labels as $label)
								<option value="{{ $label->idlabels }}">{{ $label->description }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-span-2">
						@component('components.buttons.button', [ "variant" => "warning"])
							@slot('attributeEx')
								type="button"
							@endslot
							@slot('classEx')
								approve-reclassify
							@endslot
							Agregar
						@endcomponent
					</div>
				@endcomponent
			</div>
			<div class="mt-4">
				@component('components.labels.title-divisor') RELACIÓN DE DOCUMENTOS APROBADOS <span class="help-btn" id="help-btn-add-label"></span> @endcomponent
				<div class="mt-4"> 
					@php
						$body 		= [];
						$modelBody	= [];
						$modelHead	= [
							[
								["value" => "Concepto"],
								["value" => "Clasificación de gasto"],
								["value" => "Etiquetas"],
								["value" => "Acción"],
							]
						];
						$body = [

						];
						$modelBody = $body;
					@endphp
					@component('components.tables.table', [
							"modelBody" => $modelBody,
							"modelHead"	=> $modelHead
						])
						@slot('attributeEx')
							id="table" 
						@endslot
						@slot('classEx')
							table
						@endslot
						@slot('attributeExBody')
							id="body-concepts-reclassify" 
						@endslot
						@slot('classExBody')
							request-validate
						@endslot
					@endcomponent
				</div>
			</div>
			<div class="mt-4">
				@component('components.labels.label') Comentarios (opcional): @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						cols="90" 
						rows="10" 
						name="checkCommentA"
					@endslot
					@slot('classEx')
						text-area
					@endslot
				@endcomponent
			</div>
		</div>
		<div id="rechaza" class="mt-4 hidden">
			@component('components.labels.label') Comentarios (opcional): @endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					cols="90" 
					rows="10" 
					name="checkCommentR"
				@endslot
				@slot('classEx')
					text-area
				@endslot
			@endcomponent
		</div>
		<div class="w-full mt-4 grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mb-6">
			@component('components.buttons.button', [
				"variant" => "primary"
			])
				@slot('attributeEx')
					type="submit"
					name="enviar"
				@endslot
					ENVIAR SOLICITUD
			@endcomponent
			@component('components.buttons.button', ["variant"=>"reset", "buttonElement"=>"a"])
				@slot('attributeEx')
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
@endsection

@section('scripts')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.numeric.js') }}"></script>
<script>
	$(document).ready(function()
	{
		@php
			$selects = collect([ 
				[
					"identificator"				=> ".js-enterprisesR",
					"placeholder"				=> "Seleccione la empresa",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areasR",
					"placeholder"				=> "Seleccione la dirección",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departmentsR",
					"placeholder"				=> "Seleccione el departamento",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-projectsR",
					"placeholder"				=> "Seleccione el proyecto/contrato",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects', ["selects" => $selects]) @endcomponent
		$.validate(
		{
			form: '#container-alta',
			onSuccess : function($form)
			{
				if($('input[name="status"]').is(':checked'))
				{
					if($('input#aprobar').is(':checked'))
					{
						enterprise	= $('#multiple-enterprises').val();
						area		= $('#multiple-areas').val();
						department	= $('#multiple-departments').val();
						account		= $('#multiple-accounts').val();
						if(enterprise == '' || area == '' || department == '' || account == '')
						{
							swal('', 'Todos los campos son requeridos', 'error');
							return false;
						}
						else if ( ($('#body-concepts-reclassify .tr').length) != $('#body-concepts-classify .tr_classify').length || $('.view-reclassify').is(':visible')) 
						{
							swal('', 'Tiene conceptos sin asignar', 'error');
							return false;
						}
						else
						{
							swal('Cargando',{
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
						swal('Cargando',{
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
		$('.js-enterprisesR').on('select2:unselecting', function (e)
		{
			e.preventDefault();
			swal({
				title		: "Eliminar Empresa",
				text		: "Si elimina la empresa, deberá reclasificar todos los conceptos",
				icon		: "warning",
				buttons		: ["Cancelar","OK"],
				dangerMode	: true,
			})
			.then((willClean) =>{
				if(willClean){
					$(this).val(null).trigger('change');
					$('#body-concepts-classify .tr').show();

					$('.js-accountsR').empty();
					$('#body-concepts-reclassify').empty();
					count = 0;
					
				}else{
					swal.close();
				}
			});
		});
		count = 0;
		$(document).on('change','input[name="status"]',function()
		{
			if ($('input[name="status"]:checked').val() == "4") 
			{
				$("#rechaza").slideUp("slow");
				$("#aceptar").slideToggle("slow");
			}
			else if ($('input[name="status"]:checked').val() == "6") 
			{
				$("#aceptar").slideUp("slow");
				$("#rechaza").slideToggle("slow");
			}
			generalSelect({'selector':'.js-accountsR','depends':'.js-enterprisesR','model':10});
			generalSelect({'selector':'.js-labelsR', 'model': 19, 'maxSelection' : 15});
		})
		.on('change','.js-enterprisesR',function()
		{
			generalSelect({'selector':'.js-accountsR','depends':'.js-enterprisesR','model':10});
			$('.js-accountsR').empty();
			$('.approve-classify').hide();
			$enterprise 		= $(this).val();
			enterprisesR_old_id	= $('.enterprisesR_old_id').val();
			jsEnterprisesR		= $('.js-enterprisesR').val();
			
			if(enterprisesR_old_id == jsEnterprisesR)
			{
				$('.account').each(function()
				{  
					oldId = $(this).parents('.tr').find('.accountOld_id').val();
					$(this).val(oldId).trigger('change');
				});
			}
		})
		.on('click','.add-label',function()
		{
			errorSwalElements=true;
			$('.add-article').each(function()
			{
				if($(this).is(':checked'))
				{
					errorSwalElements	= false;
					tr					= $(this).parents('.tr');
					idExpensesDetailNew = tr.find('.idExpensesDetailOld').val();
					conceptNew  		= tr.find('.conceptOld').val();
					accountIdNew 		= tr.find('.account').val();
					accountNameNew 		= tr.find('.account option:selected').text();
					accountIdOld		= tr.find('.accountOld_id').val();
					accountNameOld		= tr.find('.accountOld_name').val();
					if (accountIdNew=='')
					{
						swal('Error','Debe seleccionar una cuenta','error');
					}
					else
					{
						$(this).prop( "checked",false);
						$(this).parents('.tr').hide();
						@php
							$body		= [];
							$modelBody	= [];
							$modelHead	= [
								[
									["value" => "Concepto"],
									["value" => "Clasificación del gasto"],
									["value" => "Etiquetas"],
									["value" => "Acción"],
								]
							];
								
							$body =[ "classEx" => "tr",
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "idExpensesDetailNew"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "conceptNew"	
										]
									]
								],
								[
									"content" =>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "accountIdNew"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "accountNameOld"	
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"",
											"classEx"		=> "accountIdOld"	
										]
									] 
								],
								[
									"classEx"	=> "labelsAssign", 
									"content" 	=>
									[
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\" name=\"t_idExpensesDetail[]\"",
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"attributeEx"	=> "type=\"hidden\"  name=\"t_idAccountR[]\"",
										]
									] 
								],
								[
									"content" => 
									[
										"kind"  		=> "components.buttons.button",
										"variant"	 	=> "red",
										"label" 		=> "<span class=\"icon-x delete-span\"></span>",
										"attributeEx" 	=>	"type=\"button\"",
										"classEx" 		=> "delete-item"
									]
								],
							];
							$modelBody[] = $body;
						
							$table2 = view('components.tables.table', [
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"noHead"	=> "true"
							])->render();
						@endphp	
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
						row = $(table);
						row.find('.idExpensesDetailNew').val(idExpensesDetailNew);
						conceptNew = String(conceptNew).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
						row.find('.conceptNew').parent().prepend(conceptNew);
						row.find('.conceptNew').val(conceptNew);
						row.find('.accountIdNew').parent().prepend(accountNameNew);
						row.find('.accountIdNew').val(accountIdNew);
						row.find('.accountNameOld').val(accountNameOld);
						row.find('.accountIdOld').val(accountIdOld);
						row.find('[name="t_idExpensesDetail[]"]').val(idExpensesDetailNew);
						row.find('[name="t_idAccountR[]"]').val(accountIdNew);
						row.find('.labelsAssign').append($('<span class="labelsAssign" id="labelsAssign'+count+'"></span>'))
						$('#body-concepts-reclassify').append(row);
						$('select[name="idLabelsReview[]"] option:selected').each(function()
						{
							label		= $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
							labelText	= $('<label></label>').text($(this).text()+', ');
							$('#labelsAssign'+count).append(label);
							$('#labelsAssign'+count).append(labelText);
						});
						count++;
					}
				}
			})
			$('.js-labelsR').val(null).trigger('change');
			if(errorSwalElements)
			{
				swal('', 'Seleccione los elementos que les quiera agregar esta(s) etiqueta(s)', 'error');
			}
		})
		.on('click','.approve-reclassify',function()
		{
			conceptNew			= $('.concept-reclassify').val();
			idExpensesDetailNew	= $('.idExpensesDetail-reclassify').val();
			accountIdNew		= $('select[name="account_idR"]').val();
			accountNameNew		= $('select[name="account_idR"] option:selected').text();
			accountIdOld		= $('.idAccountOld-reclassify').val();
			accountNameOld		= $('.nameAccountOld-reclassify').val();

			if (accountIdNew.length<1)
			{
				swal('Error','Debe seleccionar una cuenta','error');
			}
			else
			{
				@php
					$body		= [];
					$modelBody	= [];
					$modelHead	= [
						[
							["value" => "Acción"],
							["value" => "Concepto"],
							["value" => "Clasificación del gasto"],
							["value" => "Etiquetas"],
						]
					];
						
					$body =[ "classEx" => "tr",
						[
							"content" => 
							[
								"kind"  		=> "components.buttons.button",
								"variant"	 	=> "red",
								"label" 		=> "<span class=\"icon-x delete-span\"></span>",
								"attributeEx" 	=> "type=\"button\"",
								"classEx" 		=> "delete-item"
							]
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "idExpensesDetailNew"	
								],
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "conceptNews"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "conceptNew"	
								],
							]
						],
						[
							"content" =>
							[
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "accountNameNews"
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "accountIdNew"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "accountNameOld"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"",
									"classEx"		=> "accountIdOld"	
								]
							] 
						],
						[
							"content" =>
							[
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"t_idExpensesDetail[]\"",
									"classEx"		=> "idExpensesDetailNew"	
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\"  name=\"t_idAccountR[]\"",
									"classEx"		=> "accountIdNew"	
								],
								[
									"kind" 		=> "components.labels.label",
									"classEx"	=> "labelsAssign"
								]
							] 
						]
					];
					$modelBody[] = $body;
				
					$table2 = view('components.tables.table', [
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"noHead"	=> "true"
					])->render();
				@endphp	
				table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
				row = $(table);
				row.find('.idExpensesDetailNew').val(idExpensesDetailNew);
				conceptNew = String(conceptNew).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				row.find('.conceptNews').text(conceptNew);
				row.find('.conceptNew').val(conceptNew);
				row.find('.accountNameNews').text(accountNameNew);
				row.find('.accountIdNew').val(accountIdNew);
				row.find('.accountNameOld').val(accountNameOld);
				row.find('.accountIdOld').val(accountIdOld);
				row.find('.idExpensesDetailNew').val(idExpensesDetailNew);
				row.find('.accountIdNew').val(accountIdNew);
				row.find('.labelsAssign').append($('<span class="labelsAssign" id="labelsAssign'+count+'"></span>'))
				$('#body-concepts-reclassify').append(row);
				$('select[name="idLabelsReview[]"] option:selected').each(function()
				{
					label = $('<input type="hidden" class="idLabelsAssign" name="idLabelsAssign'+count+'[]" value="'+$(this).val()+'">');
					labelText = $('<label></label>').text($(this).text()+', ');
					$('#labelsAssign'+count).append(label);
					$('#labelsAssign'+count).append(labelText);
				});
				$('.js-labelsR').val(null).trigger('change');
				$('.js-accountsR').val(null).trigger('change');
				$('.view-reclassify').css('display','none');
				count++;
				$('.reclassify').prop('disabled',false);
			}
		})
		.on('click','.delete-item',function()
		{
			idExpensesDetailNew	= $(this).parents('#body-concepts-reclassify .tr').find('.idExpensesDetailNew').val();
			idaccount = $(this).parents('.tr').find('.accountIdOld').val();
			$('.idExpensesDetailOld').each(function()
			{
				if($(this).val()==idExpensesDetailNew)
				{
					$(this).parents('.tr').show();
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
			swal('Ayuda','Debe aprobar o editar la clasificación del gasto en caso de no ser la correcta que se muestra para cada concepto','info');
		})
		.on('click','#help-btn-add-label',function()
		{
			swal('Ayuda','En este apartado debe agregar una o varias etiquetas por concepto','info');
		})
	});
</script>
@endsection
