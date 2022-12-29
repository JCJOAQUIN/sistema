@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$modelTable	=
		[
			["Folio:",						$request->folio],
			["Título y fecha:",				htmlentities($request->purchaseRecord->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseRecord->datetitle)->format('d-m-Y')],
			["Número de Orden:",			$request->purchaseRecord->numberOrder!="" ? htmlentities($request->purchaseRecord->numberOrder) : '---'],
			["Fiscal:",						$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",				$request->requestUser->name." ".$request->requestUser->last_name." ".$request->requestUser->scnd_last_name],
			["Elaborado por:",				$request->elaborateUser->name." ".$request->elaborateUser->last_name." ".$request->elaborateUser->scnd_last_name],
			["Empresa:",					$request->requestEnterprise->name],
			["Dirección:",					$request->requestDirection->name],
			["Departamento:",				$request->requestDepartment->name],
			["Clasificación del gasto:",	$request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"],
			["Proyecto:",					$request->requestProject->proyectName],
			["Proveedor:",					$request->purchaseRecord->provider]
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles del Registro de Compra
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PEDIDO
	@endcomponent
	@php
		$modelHead		=	[];
		$body			=	[];
		$modelBody		=	[];
		$countConcept	=	1;
		$modelHead		=
		[
			[
				["value"	=>	"#"		],
				["value"	=>	"Cantidad",],
				["value"	=>	"Unidad"],
				["value"	=>	"Descripción"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"]
			]
		];
		foreach($request->purchaseRecord->detailPurchase as $detail)
		{
			$taxesConcept		=	$detail->taxes()->sum('amount');
			$retentionConcept	=	$detail->retentions()->sum('amount');
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept],
				],
				[
					"content"	=>	["label"	=>	$detail->quantity],
				],
				[
					"content"	=>	["label"	=>	$detail->unit],
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)],
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->total,2)],
				],
			];
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
	@endcomponent
	@php
		$modelTable	=
		[
			["label"	=>	"Subtotal:"			,	"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseRecord->subtotal,2)]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseRecord->amount_taxes,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseRecord->amount_retention,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseRecord->tax,2)]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseRecord->total,2)]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			name="note
			readonly="readonly"
		@endslot
		@slot('textNotes')
			{{ htmlentities($request->purchaseRecord->notes) }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		$modelTable	=
		[
			"Empresa"						=>	$request->purchaseRecord->enterprisePayment()->exists() ? $request->purchaseRecord->enterprisePayment->name : '',
			"Cuenta"						=>	$request->purchaseRecord->accountPayment()->exists() ? $request->purchaseRecord->accountPayment->account.' - '.$request->purchaseRecord->accountPayment->description : '',
			"Referencia/Número de factura"	=>	($request->purchaseRecord->reference != "" ? htmlentities($request->purchaseRecord->reference) : "---"),
			"Tipo de moneda"				=>	$request->purchaseRecord->typeCurrency,
			"Fecha de pago"					=>	$request->PaymentDate!='' ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y') : null,
			"Forma de pago"					=>	$request->purchaseRecord->paymentMethod,
			"Estatus de factura"			=>	$request->purchaseRecord->billStatus,
			"Importe a pagar"				=>	"$ ".number_format($request->purchaseRecord->total,2)
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Responsable"],
				["value"	=>	"Nombre en Tarjeta"],
				["value"	=>	"Número de Tarjeta"],
				["value"	=>	"Status"],
				["value"	=>	"Principal/Adicional"]
			]
		];
		if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
		{
			$t		=	App\CreditCards::find($request->purchaseRecord->idcreditCard);
			$user	=	App\User::find($t->assignment);
			$status	=	$principal	=	'';
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
			$body	=
			[
				[
					"content"	=>	["label"	=>	$user->name." ".$user->last_name." ".$user->scnd_last_name],
				],
				[
					"content"	=>	["label"	=>	$t->name_credit_card],
				],
				[
					"content"	=>	["label"	=>	$t->credit_card],
				],
				[
					"content"	=>	["label"	=>	$status],
				],
				[
					"content"	=>	["label"	=>	$principal],
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="view-credit-cards"
			@if(isset($request) && $request->purchaseRecord->paymentMethod == "TDC Empresarial")
				style="display: block;"
			@else
				style="display: none;"
			@endif
		@endslot
		@slot('attributeExBody')
			id="body-credit-cards"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DOCUMENTOS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->purchaseRecord->documents)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->purchaseRecord->documents as $doc)
			{
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/purchase-record/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')]
					]
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documento"];
			$body	=
			[
				[
					"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@if($request->request_has_reclassification()->exists())
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			HISTORIAL DE RECLASIFICACIÓN
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Empresa"],
					["value"	=>	"Dirección"],
					["value"	=>	"Departamento"],
					["value"	=>	"Clasificación del gasto"],
					["value"	=>	"Proyecto"],
					["value"	=>	""]
				]
			];
			foreach($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$body	=
				[
					[
						"content"	=>
						[
							["label"	=>	$r->enterprise->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->enterprise->name."\"",
								"classEx"		=>	"enterprise"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->user->name.' '.$r->user->last_name.' '.$r->user->scnd_last_name."\"",
								"classEx"		=>	"name"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$r->date)->format('d-m-Y')."\"",
								"classEx"		=>	"date"
							],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->commentaries."\"",
								"classEx"		=>	"commentaries"
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->direction->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->direction->name."\"",
								"classEx"		=>	"direction"
							],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->department->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->department->name."\"",
								"classEx"		=>	"department"
							],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.')'],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accounts->account.' '.$r->accounts->description.' ('.$r->accounts->content.")\"",
								"classEx"		=>	"account"
							],
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->project->proyectName],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->project->proyectName."\"",
								"classEx"		=>	"project"
							],
						],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" title=\"Ver datos\" data-target=\"#modalUpdate\" data-toggle=\"modal\"",
								"classEx"		=>	"view-data",
								"label"			=>	"<span class='icon-search'></span>"
							],
						],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
	@endif
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-purchaserecord', $request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			CLASIFICACIÓN ACTUAL
		@endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->idEnterpriseR == $enterprise->id)
						{
							$optionsEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsEnterprise[]	=
							[
								"value"			=>	$enterprise->id,
								"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsEnterprise])
					@slot('attributeEx')
						id="multiple-enterprisesR"
						name="idEnterpriseR"
						multiple="multiple"
						data-validation="required"
						disabled
					@endslot
					@slot('classEx')
						js-enterprisesR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Dirección: @endcomponent
				@php
					$optionsDirection	=	[];
					foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
					{
						if ($request->idAreaR == $area->id)
						{
							$optionsDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsDirection[]	=
							[
								"value"			=>	$area->id,
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsDirection])
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
					$optionsDepartment	=	[];
					foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
					{
						if ($request->idDepartamentR == $department->id)
						{
							$optionsDepartment[]	=
							[
								"value"			=>	$department->id,
								"description"	=>	$department->name,
								"selected"		=>	"selected"
							];
						}
						else
						{
							$optionsDepartment[]	=
							[
								"value"			=>	$department->id,
								"description"	=>	$department->name,
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsDepartment])
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
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					$options	=	collect();
					if (isset($request->accountR) && $request->accountR!="")
					{
						$options	=	$options->concat([["value"	=>	$request->accountsReview->idAccAcc,	"description"	=>	$request->accountsReview->account.' - '.$request->accountsReview->description." (".$request->accountsReview->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						multiple="multiple"
						name="accountR"
						data-validation="required"
						id="multiple-accountsR"
					@endslot
					@slot('classEx')
						js-accountsR
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Proyecto: @endcomponent
				@php
					$optionsPoject	=	collect();
					if (isset($request->idProjectR) && $request->idProjectR !="")
					{
						$optionsPoject	=	$optionsPoject->concat([["value"	=>	$request->reviewedProject->idproyect,	"description"	=>	$request->reviewedProject->proyectName,	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsPoject])
					@slot('attributeEx')
						id="multiple-projectsR"
						name="project_id"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-projectsR
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.label')
			@slot('classEx')
				mt-8
			@endslot
			Comentarios (opcional)
		@endcomponent
		@component("components.inputs.text-area")
			@slot('attributeEx')
				name="commentaries"
			@endslot
		@endcomponent
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary"])
				@slot('classEx')
					mr-2
				@endslot
				@slot('attributeEx')
					type="submit"
					name="enviar"
					value="ENVIAR SOLICITUD"
				@endslot
				RECLASIFICAR
			@endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["classEx" => "load-actioner", "buttonElement" => "a", "variant" => "reset", "attributeEx" => "href=\"".$href."\"", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
	@component("components.modals.modal",["variant" => "large"])
		@slot('id')
			modalUpdate
		@endslot
		@slot('classEx')
			modal fade
		@endslot
		@slot('modalBody')
			@php
				$modelHead	=	[];
				$modelHead	=	["INFORMACIÓN"];
				$modelBody	=	[];
			@endphp
			@component('components.tables.alwaysVisibleTable', ["variant" => "default", "modelHead" => $modelHead, "modelBody", $modelBody, "themeBody" => "striped"])@endcomponent
			@component('components.containers.container-form')
				<div class="col-span-2">
					@component('components.labels.label') Modificó: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-name"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Fecha: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-date"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Empresa: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-enterprise"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Dirección: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-direction"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Departamento: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-department"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Proyecto: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-project"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Clasificación de gasto: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-account"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label') Comentarios: @endcomponent
					@component('components.labels.label')
						@slot('attributeEx')
							name="view-commentaries"
						@endslot
					@endcomponent
				</div>
			@endcomponent
		@endslot
		@slot('modalFooter')
			@component("components.buttons.button",["variant" => "red"])
				@slot('classEx')
					modal-close
				@endslot
				@slot('attributeEx')
					type=button
					data-dismiss="modal"
				@endslot
				Cerrar
			@endcomponent
		@endslot
	@endcomponent
@endsection
@section('scripts')
<script>
	swal({
		icon: '{{ asset(getenv('LOADING_IMG')) }}',
		button: false,
		timer: 1000,
	});
	$.validate(
	{
		form: '#container-alta',
		onSuccess : function($form)
		{

		}
	});
	$(document).ready(function()
	{
		$("#rechaza").slideUp("slow");
		$("#aceptar").slideToggle("slow").addClass('form-container').css('display','block');
		generalSelect({'selector': '.js-projectsR', 'model': 14});
		generalSelect({'selector': '.js-accountsR', 'depends': '.js-enterprisesR', 'model': 10});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprisesR",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areasR",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departmentsR",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		count = 0;
		$(document).on('change','.js-enterprisesR',function()
		{
			$('.js-accountsR').empty();
		})
		.on('click','.view-data',function()
		{
			$('[name="view-name"]').text($(this).parent('div').parent('div').parent('div').find('.name').val());
			$('[name="view-date"]').text($(this).parent('div').parent('div').parent('div').find('.date').val());
			$('[name="view-enterprise"]').text($(this).parent('div').parent('div').parent('div').find('.enterprise').val());
			$('[name="view-direction"]').text($(this).parent('div').parent('div').parent('div').find('.direction').val());
			$('[name="view-department"]').text($(this).parent('div').parent('div').parent('div').find('.department').val());
			$('[name="view-project"]').text($(this).parent('div').parent('div').parent('div').find('.project').val());
			$('[name="view-account"]').text($(this).parent('div').parent('div').parent('div').find('.account').val());
			$('[name="view-commentaries"]').text($(this).parent('div').parent('div').parent('div').find('.commentaries').val());
			$('#modalUpdate').fadeIn();
		})
	});
</script>
@endsection
