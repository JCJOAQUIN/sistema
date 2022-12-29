@extends('layouts.child_module')
@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$elaborateUser 	= App\User::find($request->idElaborate);
		$modelTable = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->purchaseRecord->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->purchaseRecord->datetitle)->format('d-m-Y')],
			["Número de Orden:", $request->purchaseRecord->numberOrder != "" ? htmlentities($request->purchaseRecord->numberOrder) : '---'],
			["Fiscal:", $request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:", $request->requestUser->fullname()],
			["Elaborado por:", $request->elaborateUser->fullname()],
			["Dirección:", $request->requestDirection->name],
			["Departamento:", $request->requestDepartment->name],
			["Clasificación del gasto:", $request->accounts->account." - ".$request->accounts->description],
			["Proyecto:", $request->requestProject->proyectName],
		];
		if($request->wbs()->exists())
		{
			$modelTable [] = ["WBS:", $request->wbs->code_wbs];
		}
		if($request->edt()->exists())
		{
			$modelTable [] = ["EDT:", $request->edt->description];
		}
		$modelTable [] = ["Proveedor:", htmlentities($request->purchaseRecord->provider)];
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles del Registro de Compra"])@endcomponent
	@component("components.labels.title-divisor") DATOS DEL PEDIDO @endcomponent
	@php
		$countConcept = 1;
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$taxesConcept 		= $detail->taxes()->sum('amount');
			$retentionConcept 	= $detail->retentions()->sum('amount');
			$body = 
			[
				[
					"content" =>
					[
						"label" => $countConcept,
					]
				],
				[
					"content" =>
					[
						"label" => number_format($detail->quantity,2),
					]
				],
				[
					"content" =>
					[
						"label" => $detail->unit,
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
						"label" => "$ ".number_format($detail->unitPrice,2),
					]
				],
				[
					"content" => 
					[
						"label" => "$ ".number_format($detail->tax,2),
					]
				],
				[
					"content" =>
					[
						"label" => "$ ".number_format($taxesConcept,2),
					]
				],
				[
					"content" =>
					[
						"label" => "$ ".number_format($retentionConcept,2),
					],
				],
				[
					"content" =>
					[
						"label" => "$ ".number_format($detail->total,2),
					],
				],
			];
			$modelBody[] = $body;
			$countConcept++;
		}
	@endphp
	@Table(
	[
		"modelHead" => 
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
		],
		"modelBody" => $modelBody
	])
	@endTable
	@php
		$modelTable = [];
		$modelTable = 
		[	
			[
				"label" => "Subtotal: ", "inputsEx" => 
				[
					[
						"kind"		=> "components.labels.label",	
						"label" 	=> "$ ".number_format($request->purchaseRecord->subtotal,2), 
						"classEx" 	=> "my-2"
					],
				]
			],
			[
				"label" => "Impuesto Adicional: ", 
				"inputsEx" => 
				[
					[
						"kind" => "components.labels.label",
						"label"	=>	"$ ".number_format($request->purchaseRecord->amount_taxes,2), 
						"classEx" => "my-2"
					],
				]
			],
			[
				"label" => "Retenciones: ", 
				"inputsEx" => 
				[
					[
						"kind" => "components.labels.label",
						"label"	=>	"$ ".number_format($request->purchaseRecord->amount_retention,2), 
						"classEx" => "my-2"
					],
				]
			],
			[
				"label" => "IVA: ", 
				"inputsEx" => 
				[
					[
						"kind" => "components.labels.label",
						"label"	=>	"$ ".number_format($request->purchaseRecord->tax,2),
						"classEx" => "my-2"
					],
				]
			],
			[
				"label" => "IVA: ", 
				"inputsEx" => 
				[
					[
						"kind" => "components.labels.label",
						"label"	=>	"$ ".number_format($request->purchaseRecord->total,2),
						"classEx" => "my-2"
					],
				]
			],
		];
		
	@endphp
	@component("components.templates.outputs.form-details", ["modelTable" => $modelTable, "textNotes" => $request->purchaseRecord->notes]) @endcomponent
	@component("components.labels.title-divisor") CONDICIONES DE PAGO @endcomponent
	@php
		$modelTable	=
		[
			"Empresa"						=> ($request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '---'),
			"Cuenta"						=> ($request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '---'),
			"Referencia/Número de factura" 	=> ($request->purchaseRecord->reference != "" ? htmlentities($request->purchaseRecord->reference) : "---"),
			"Tipo de moneda"				=> $request->purchaseRecord->typeCurrency,
			"Fecha de pago"					=> ($request->PaymentDate != '' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->PaymentDate)->format("d-m-Y") : "---"),
			"Forma de pago"					=> $request->purchaseRecord->paymentMethod,
			"Estado de factura"				=> $request->purchaseRecord->billStatus,
			"Importe a pagar"				=> "$ ".number_format($request->purchaseRecord->total,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable]) @endcomponent
	@php
		$modelBody = [];
		if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		{
			$t = App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user = App\User::find($t->assignment);
			$status = $principal = '';
			switch ($t->status) 
			{
				case 1:
					$status = 'Vigente';
					break;
				case 2:
					$status = 'Bloqueada';
					break;
				case 3:
					$status = 'Cancelada';
					break;
				default:
					break;
			}

			switch ($t->principal_aditional) 
			{
				case 1:
					$principal = 'Principal';
					break;
				case 2:
					$principal = 'Adicional';
					break;
				default:
					break;
			}
			$body = 
			[
				"classEx" => ($t->idcreditCard == $request->purchaseRecord->idcreditCard ? "marktr" : ""),
				[
					"content" =>
					[
						"label" => $user->fullName(),
					]
				],
				[
					"content" =>
					[
						"label" => $t->name_credit_card,
					]
				],
				[
					"content" =>
					[
						"label" => $t->credit_card,
					]
				],
				[
					"content" =>
					[
						"label" => $status,
					]
				],
				[
					"content" =>
					[
						"label" => $principal,
					]
				],
			];
			$modelBody[] = $body;
		}
	@endphp
	@Table(
	[
		"modelHead" => 
		[
			[
				["value" => "Responsable"],
				["value" => "Nombre en Tarjeta"],
				["value" => "Número de Tarjeta"],
				["value" => "Estatus"],
				["value" => "Principal/Adicional"],
			]
		],
		"modelBody" => $modelBody,
		"classEx"	=> (!isset($request) && $request->purchaseRecord->paymentMethod != "TDC Empresarial" ? "hidden" : ""),
	])
	@endTable
	@component('components.labels.title-divisor') DOCUMENTOS @endcomponent
	@if(count($request->purchaseRecord->documents) > 0)
		@php
			$modelHead = ["Tipo de documento", "Archivo", "Fecha"];
			$modelBody = [];
			foreach($request->purchaseRecord->documents as $doc)
			{
				$modelBody[] = 
				[
					[
						"content" =>
						[
							"label" => $doc->name,
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 									
								"buttonElement" => "a",
								"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset("docs/purchase-record/".$doc->path)."\"",
								"variant" => "dark-red",
								"label" => "PDF",
							]
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y H:i:s'),
						]
					],
				];
			}
		@endphp
		@component("components.tables.alwaysVisibleTable", 
		[
			"modelBody" => $modelBody,
			"modelHead" => $modelHead,
			"variant"	=> "default"
		]);
		@endcomponent
	@else
		@component("components.labels.not-found", ["text" => "No hay documentos"])@endcomponent
	@endif
	@component("components.forms.form",["attributeEx" => "action=\"".route('purchase-record.review.update',$request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
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
			@component('components.labels.title-divisor')    ASIGNACIÓN DE ETIQUETAS @endcomponent
			@php
				$modelHead = ["Asignar","Cantidad","Descripción"];
				$modelBody =[];
				foreach($request->purchaseRecord->detailPurchase as $detail)
				{
					$modelBody[] =
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
									"attributeEx"		=> "type=\"checkbox\" id=\"id_article_".$detail->id."\" value=\"1\" name=\"add-article_".$detail->id."\"",
									"id"				=> "id_article_".$detail->id."",
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
									"kind" 	=> "components.labels.label",
									"label" => number_format($detail->quantity, 2)." ".$detail->unit,
								],
							]
						],
						[
							"classEx" => "td",
							"content" =>
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => htmlentities($detail->description),
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".$detail->id."\"",
									"classEx"     => "idPurchaseRecordDetailOld",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".$detail->quantity." ".$detail->unit."\"",
									"classEx"     => "quantityOld",
								],
								[
									"kind"        => "components.inputs.input-text",
									"attributeEx" => "type=\"hidden\" value=\"".htmlentities($detail->description)."\"",
									"classEx"     => "conceptOld",
								],
							]
						],
					];
				}
				$options = collect();
				$modelBody [] = 
				[
					"classEx"=> "tr row-labels",
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
								"label"	=> "Etiquetas:"
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
				@endslot
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
						title="Agregar"
					@endslot
					<span class="icon-plus"></span>
					<span>Agregar</span>
				@endcomponent
			</div>
			@component('components.labels.title-divisor') ETIQUETAS ASIGNADAS @endcomponent
			@php
				$modelHead = ["Concepto","Etiquetas","Acción"];
				$modelBody =[];
			@endphp
			@component("components.tables.alwaysVisibleTable",[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"themeBody" => "striped"
			])
				@slot("attributeEx")
					id="table"
				@endslot
				@slot('attributeExBody')
					id="tbody-conceptsNew"
				@endslot
			@endcomponent
			<div id="labelsAssign"></div>
			@component("components.containers.container-form")
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->orderBy('name','asc')->get() as $enterprise)
						{
							if($request->idEnterprise == $enterprise->id)
							{
								$options = $options->concat([["value" => $enterprise->id, "selected" => "selected", "description" => (strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name)]]);
							}
							else 
							{
								$options = $options->concat([["value" => $enterprise->id, "description" => (strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name)]]);
							}
						}
						$attributeEx = "id=\"multiple-enterprisesR\" name=\"idEnterpriseR\" multiple=\"multiple\" data-validation=\"required\" title=\"Empresa\"";
						$classEx = "js-enterprisesR";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Área: @endcomponent
					@php
						$options = collect();
						foreach(App\Area::where('status','ACTIVE')->orderby('name','asc')->get() as $area)
						{
							if($request->idArea == $area->id)
							{
								$options = $options->concat([["value" => $area->id, "selected" => "selected", "description" => $area->name]]);
							}
							else 
							{
								$options = $options->concat([["value" => $area->id, "description" => $area->name]]);
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
						foreach(App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->orderBy('name','asc')->get() as $department)
						{
							if($request->idDepartment == $department->id)
							{
								$options = $options->concat([["value" => $department->id, "selected" => "selected", "description" => $department->name]]);
							}
							else 
							{
								$options = $options->concat([["value" => $department->id, "description" => $department->name]]);
							}
						}
						$attributeEx = "id=\"multiple-departmentsR\" multiple=\"multiple\" name=\"idDepartmentR\" data-validation=\"required\"";
						$classEx = "js-departmentsR";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Clasificación del Gasto: @endcomponent
					@php
						$options = collect();
						$options = $options->concat([["value" => $request->account, "selected" => "selected", "description" => $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"]]);
						$attributeEx = "id=\"multiple-accountsR\" multiple=\"multiple\" name=\"accountR\" data-validation=\"required\"";
						$classEx = "js-accountsR removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label") Proyecto: @endcomponent
					@php
						$options = collect();
						$options = $options->concat([["value" => $request->idProject, "selected" => "selected", "description" => $request->requestProject->proyectName]]);
						$attributeEx = "id=\"multiple-projectsR\" name=\"project_id\" multiple=\"multiple\" data-validation=\"required\"";
						$classEx = "js-projectsR removeselect";
					@endphp
					@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
				</div>
			@endcomponent
			@component("components.labels.label")
				Comentarios (Opcional)
			@endcomponent
			@component("components.inputs.text-area")
				@slot("classEx")
					text-area
				@endslot
				@slot("attributeEx")
					cols="90"
					rows="10"
					name="checkCommentA"
				@endslot
			@endcomponent
		</div>
		<div id="rechaza" class="hidden">
			@component("components.labels.label")
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
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					w-48
					md:w-auto
					text-center
				@endslot
				@slot("attributeEx")
					name="enviar"
					type="submit"
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script>
		$(document).ready(function()
		{
			$.validate(
			{
				form: '#container-alta',
				onError   : function($form)
				{
					swal("", '{{ Lang::get("messages.form_error") }}', "error");
				},
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
								swal('', 'Por favor ingrese los campos son requeridos.', 'error');
								return false;
							}
							else if ( ($('#tbody-concepts .tr').length-1) != $('#tbody-conceptsNew .tr').length) 
							{
								swal('', 'Aún tiene conceptos sin asignar, por favor verifique sus datos.', 'error');
								return false;
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
							swal("Cargando",{
								icon: '{{ url('images/loading.svg') }}',
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
				}
			});
			count = 0;
			$(document).on('change','input[name="status"]',function()
			{
				if ($('input[name="status"]:checked').val() == "4") 
				{
					$("#rechaza").slideUp("slow");
					$("#aceptar").slideToggle("slow").removeClass('hidden');
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
							],
						]);
					@endphp
					@component("components.scripts.selects",["selects"=>$selects]) @endcomponent
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : 10});
					generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
					generalSelect({'selector': '.js-projectsR', 'model': 21});
				}
				else if ($('input[name="status"]:checked').val() == "6") 
				{
					$("#aceptar").slideUp("slow");
					$("#rechaza").slideToggle("slow").removeClass('hidden');
				}
			})
			.on('click','.add-label',function()
			{ 
				errorSwalElements = true;
				$('.add-article').each(function()
				{
					if($(this).is(':checked')) {
						errorSwalElements		= false;
						tr						= $(this).parents('.tr');
						quantity 				= tr.find('.quantityOld').val();
						concept 				= tr.find('.conceptOld').val();
						idPurchaseRecordDetail	= tr.find('.idPurchaseRecordDetailOld').val();
						
						$(this).prop( "checked",false); 
						tr.addClass('hidden');
						@php
							$modelHead = ["Concepto","Etiquetas","Acción"];
							$modelBody	= [];
							$modelBody [] = [
								"classEx" => "tr",
								[
									"classEx" => "td",
									"content" => 
									[
										[
											"kind"  		=> "components.labels.label",
											"classEx" 		=> "concept"
										],
										[
											"kind" => "components.inputs.input-text",
											"classEx" => "conceptLabel",
											"attributeEx" => "type=\"hidden\" value=\"\"",
										],
										[
											"kind" => "components.inputs.input-text",
											"classEx" => "quantityLabel",
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
											"classEx" => "idPurchaseRecordDetailLabel",
											"attributeEx" => "type=\"hidden\" name=\"t_idPurchaseRecordDetail[]\" value=\"\"",
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
							$table2 = view('components.tables.alwaysVisibleTable', [
								"modelHead" => $modelHead,
								"modelBody" => $modelBody,
								"themeBody" => "striped",
								"noHead" 	=> true,
							])->render();
						@endphp
						table = '{!!preg_replace("/(\r)*(\n)*/", "", $table2)!!}';
						row = $(table);
						row.find('.concept').text(concept);
						row.find('.conceptLabel').val(concept);
						row.find('.quantityLabel').val(quantity);
						row.find('.idPurchaseRecordDetailLabel').val(idPurchaseRecordDetail);
						row.find('.labelsAssignSpan').append($('<span class="labelsAssign" id="labelsAssign'+count+'"></span>'));
						$('#tbody-conceptsNew').append(row);
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
				})
				count_label = 0;
				$('#tbody-concepts .tr').each(function(i)
				{
					if($(this).is(':visible'))
					{
						count_label++;
					}
				});
				if(count_label == 1)
				{
					$('.row-labels').addClass('hidden');
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
				if($('.row-labels').is(':visible') == false)
				{
					$('.row-labels').removeClass('hidden');
					generalSelect({'selector': '.js-labelsR', 'model': 19, 'maxSelection' : 10});
				}
				idExpensesDetailNew	= $(this).parents('.tr').find('.idPurchaseRecordDetailLabel').val();
				idaccount = $(this).parent('.tr').find('.accountIdOld').val();
				$('.idPurchaseRecordDetailOld').each(function(){
					if($(this).val() == idExpensesDetailNew){
						$(this).parents('.tr').removeClass('hidden');
					}
				});
				$(this).parents('.tr').remove();

				$('#tbody-conceptsNew .tr').each(function(i,v)
				{
					$(this).find('.idLabelsAssign').attr('name','idLabelsAssign'+i+'[]');
					$(this).find('.labelsAssign').attr('id','labelsAssign'+i+'[]');
				});
				count = $('#tbody-conceptsNew .tr').length;
			});
		});
	</script>
@endsection
