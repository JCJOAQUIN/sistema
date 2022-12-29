@extends('layouts.child_module')

@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
			A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser	=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$requestAccount	=	App\Account::find($request->adjustment->first()->idAccAccDestiny);
		$modelTable	=
		[
			["Folio",							$request->folio],
			["Título y fecha",					htmlentities($request->adjustment->first()->title)." - ".($request->adjustment->first()->datetitle != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->datetitle)->format('d-m-Y') : "")],
			["Comentarios",						$request->adjustment->first()->commentaries!="" ? htmlentities($request->adjustment->first()->commentaries) : '---'],
			["Solicitante",						$requestUser],
			["Elaborado por",					$elaborateUser],
			["Empresa Origen",					$request->adjustment->first()->idEnterpriseOrigin !="" ? App\Enterprise::find($request->adjustment->first()->idEnterpriseOrigin)->name : ""],
			["Empresa Destino",					App\Enterprise::find($request->adjustment->first()->idEnterpriseDestiny)->name],
			["Dirección Destino",				App\Area::find($request->adjustment->first()->idAreaDestiny)->name],
			["Departamento Destino",			App\Department::find($request->adjustment->first()->idDepartamentDestiny)->name],
			["Clasificación del Gasto Destino",	$requestAccount->account." ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto Destino",				App\Project::find($request->adjustment->first()->idProjectDestiny)->proyectName]
		];
	@endphp
	@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
		@slot('classEx')
			mt-4
		@endslot
		@slot('title')
			Detalles de la Solicitud de {{ $request->requestkind->kind }}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE ORIGEN
	@endcomponent
	@component('components.labels.not-found', ["variant" => "alert"])
		@slot('classEx')
			@if(count($request->adjustment->first()->adjustmentFolios)>0) hidden @endif
		@endslot
		@slot('attributeEx')
			id="error_request"
		@endslot
		Debe seleccionar una solicitud
	@endcomponent
	<div class="folios justify-center grid md:grid-cols-2 grid-cols-1">
		@foreach ($request->adjustment->first()->adjustmentFolios as $af)
			<div class="col-span-1 mx-2">
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" class="folios_adjustment" value="{{ $af->idFolio }}"
					@endslot
				@endcomponent
				@php
					$modelTable	=
					[
						["Empresa:",					$af->requestModel->reviewedEnterprise->name],
						["Dirección:",					$af->requestModel->reviewedDirection->name],
						["Departamento:",				$af->requestModel->reviewedDepartment->name],
						["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' '. $af->requestModel->accountsReview->description.' ('. $af->requestModel->accountsReview->content.")" : 'Varias'],
						["Proyecto:",					$af->requestModel->reviewedProject->proyectName]
					];
				@endphp
				@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
					@slot('classEx')
						mt-4
					@endslot
					@slot('attributeEx')
						style="border: 1px solid #c6c6c6; max-width: 500px; width: 100%;"
					@endslot
					@slot('title')
						FOLIO #{{$af->idFolio}}
					@endslot
				@endcomponent
			</div>
		@endforeach
	</div>
	<div id="detail" class="hidden">
	</div>
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PEDIDO
	@endcomponent
	<div class="form-container">
		@php
			$modelHead			=	[];
			$body				=	[];
			$modelBody			=	[];
			$retentionConcept	=	0;
			$countConcept		=	1;
			$modelHead			=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Solicitud de"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Unidad"],
					["value"	=>	"Descripci&oacute;n"],
					["value"	=>	"Precio Unitario"],
					["value"	=>	"IVA"],
					["value"	=>	"Impuesto Adicional"],
					["value"	=>	"Retenciones"],
					["value"	=>	"Importe"],
				]
			];
			foreach($request->adjustment->first()->adjustmentFolios as $detail)
			{
				switch ($detail->requestModel->kind)
				{
					case '1':
						foreach ($detail->requestModel->purchases->first()->detailPurchase as $detpurchase)
						{
							$taxesConcept=0;
							foreach ($detpurchase->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$retentionConcept=0;
							foreach ($detpurchase->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}
							$body	=
							[
								[
									"content"	=>	["label"	=>	$countConcept]
								],
								[
									"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
								],
								[
									"content"	=>	["label"	=>	$detpurchase->quantity]
								],
								[
									"content"	=>	["label"	=>	$detpurchase->unit]
								],
								[
									"content"	=>	["label"	=>	htmlentities($detpurchase->description)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detpurchase->unitPrice,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detpurchase->tax,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detpurchase->amount,2)]
								],
							];
							$countConcept++;
							$modelBody[]	=	$body;
						}
						break;
					case '3':
						foreach ($detail->requestModel->expenses->first()->expensesDetail as $detexpenses)
						{
							$taxesConcept		=	0;
							$retentionConcept	=	0;
							foreach ($detexpenses->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							if (isset($detexpenses->retentions))
							{
								foreach ($detexpenses->retentions as $ret)
								{
									$retentionConcept+=$ret->amount;
								}
							}
							$body	=
							[
								[
									"content"	=>	["label"	=>	$countConcept]
								],
								[
									"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
								],
								[
									"content"	=>	["label"	=>	"---"]
								],
								[								
									"content"	=>	["label"	=>	"---"]
								],
								[
									"content"	=>	["label"	=> htmlentities($detexpenses->description)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detexpenses->unitPrice,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detexpenses->tax,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detexpenses->amount,2)]
								],
							];
							$countConcept++;
							$modelBody[]	=	$body;
						}
						break;
					case '9':
						foreach ($detail->requestModel->refunds->first()->refundDetail as $detrefund)
						{
							$taxesConcept=0;
							$retentionConcept=0;
							foreach ($detrefund->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$body	=
							[
								[
									"content"	=>	["label"	=>	$countConcept]
								],
								[
									"content"	=>	["label"	=>	$detail->requestModel->requestkind->kind.' #'.$detail->requestModel->folio]
								],
								[
									"content"	=>	["label"	=>	"---"]
								],
								[
									"content"	=>	["label"	=>	"---"]
								],
								[
									"content"	=>	["label"	=> htmlentities($detrefund->concept)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detrefund->unitPrice,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detrefund->tax,2)]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
								],
								[
									"content"	=>	["label"	=>	"$ 0.00"]
								],
								[
									"content"	=>	["label"	=>	"$ ".number_format($detrefund->amount,2)]
								],
							];
							$countConcept++;
							$modelBody[]	=	$body;
						}
						break;
				}
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body"
			@endslot
		@endcomponent
	</div>
	@php
		foreach ($request->adjustment->first()->detailAdjustment as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "label"	=>	"$ ".number_format($request->adjustment->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "label"	=>	"$ ".number_format($request->adjustment->first()->additionalTax,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "label"	=>	"$ ".number_format($request->adjustment->first()->retention,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "label"	=>	"$ ".number_format($request->adjustment->first()->tax,2)]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label", "classEx" => "h-10 py-2", "label"	=>	"$ ".number_format($request->adjustment->first()->amount,2)]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->adjustment->first()->currency,
			"Fecha de pago"		=>	$request->adjustment->first()->paymentDate != null ? Carbon\Carbon::createFromFormat('Y-m-d',$request->adjustment->first()->paymentDate)->format('d-m-Y') : "",
			"Forma de pago"		=>	$request->adjustment->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->adjustment->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
		@slot('classEx')
			employee-details
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
		if (count($request->adjustment->first()->documentsAdjustment)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->adjustment->first()->documentsAdjustment as $doc)
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
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						],
					],
					[
						"content"	=>	["label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y  H:i:s')],
					],
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('movements-accounts.adjustment.updateReview', $request->folio)."\"", "methodEx"=>"PUT"])
		<div class="form-container mt-12">
			<div class="flex justify-center">
				@component('components.labels.label')
					¿Desea aprobar ó rechazar la solicitud?
				@endcomponent
			</div>
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status"
					id="aprobar"
					value="4"
				@endslot
				@slot('attributeExButtonTwo')
					id="rechazar"
					name="status"
					value="6"
				@endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			@component('components.labels.title-divisor')
				@slot('classEx')
					mt-12
				@endslot
				CUENTA DE DESTINO
			@endcomponent
			<div class="form-container">
				@component('components.containers.container-form')
					<div class="col-span-2">
						@component('components.labels.label') Empresa: @endcomponent
						@php
							$optionsEnterprises	=	[];
							foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
							{
								if ($request->adjustment()->exists() && $request->adjustment->first()->idEnterpriseDestiny == $enterprise->id)
								{
									$optionsEnterprises[]	=
									[
										"value"			=>	$enterprise->id,
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
										"selected"		=>	"selected"
									];
								}
								else
								{
									$optionsEnterprises[]	=
									[
										"value"			=>	$enterprise->id,
										"description"	=>	strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name,
									];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionsEnterprises])
							@slot('classEx')
								js-enterprises-destination removeselect
							@endslot
							@slot('attributeEx')
								name="enterpriseid_destination" multiple="multiple" data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Dirección: @endcomponent
						@php
							$optionsArea	=	[];
							foreach (App\Area::orderName()->where('status','ACTIVE')->get() as $area)
							{
								if ($request->adjustment()->exists() && $request->adjustment->first()->idAreaDestiny == $area->id)
								{
									$optionsArea[]	=
									[
										"value"			=>	$area->id,
										"description"	=>	$area->name,
										"selected"		=>	"selected"
									];
								}
								else
								{
									$optionsArea[]	=
									[
										"value"			=>	$area->id,
										"description"	=>	$area->name
									];
								}
							}
						@endphp
						@component('components.inputs.select', ["options" => $optionsArea])
							@slot('classEx')
								js-areas-destination removeselect
							@endslot
							@slot('attributeEx')
								multiple="multiple" name="areaid_destination" data-validation="required"
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Departamento: @endcomponent
						@php
							$optionsDepartment	=	[];
							foreach (App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep($option_id)->pluck('departament_id'))->get() as $department)
							{
								if ($request->adjustment()->exists() && $request->adjustment->first()->idDepartamentDestiny == $department->id)
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
								multiple="multiple" name="departmentid_destination" id="multiple-departments" data-validation="required"
							@endslot
							@slot('classEx')
								js-departments-destination removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Cuenta: @endcomponent
						@php
							$options	=	collect();
							if (isset($request) && $request->adjustment()->exists() && $request->adjustment->first()->idAccAccDestiny !="")
							{
								$options	= $options->concat([[
									"value"			=>	$request->adjustment->first()->accountDestiny->idAccAcc,
									"description"	=>	$request->adjustment->first()->accountDestiny->account." - ".$request->adjustment->first()->accountDestiny->description." (".$request->adjustment->first()->accountDestiny->content.")",
									"selected"		=>	"selected"
								]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								multiple="multiple" name="accountid_destination" data-validation="required"
							@endslot
							@slot('classEx')
								js-accounts-destination removeselect
							@endslot
						@endcomponent
					</div>
					<div class="col-span-2">
						@component('components.labels.label') Proyecto:	@endcomponent
						@php
							$options	=	collect();
							if ($request->adjustment()->exists() && $request->adjustment->first()->idProjectDestiny != "")
							{
								$options	=	$options->concat([["value"	=>	$request->adjustment->first()->projectDestiny->idproyect,	"description"	=>	$request->adjustment->first()->projectDestiny->proyectName,	"selected"	=>	"selected"]]);
							}
						@endphp
						@component('components.inputs.select', ["options" => $options])
							@slot('attributeEx')
								name="projectid_destination" multiple="multiple" data-validation="required"
							@endslot
							@slot('classEx')
								js-projects-destination removeselect
							@endslot
						@endcomponent
					</div>
				@endcomponent
			</div>
			<div class="flex-wrap w-full grid md:grid-cols-1 gap-x-10 mt-4">
				@component('components.labels.label') Comentarios: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						name="checkCommentA"
					@endslot
				@endcomponent
			</div>
		</div>
		<div id="rechaza" class="hidden">
			<div class="flex-wrap w-full grid md:grid-cols-1 gap-x-10 mt-4">
				@component('components.labels.label') Comentarios: @endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						name="checkCommentR"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component("components.buttons.button",["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
			@php
				$href	=	isset($option_id) ? url(App\Module::find($option_id)->url) : url(App\Module::find($child_id)->url);
			@endphp
			@component("components.buttons.button", ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
<script type="text/javascript">
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
							swal('', 'Todos los campos son requeridos', 'error');
							return false;
						}
						else if ($('#tbody-concepts tr').length > 0 || $('.idDetailAdjustmentNew').val()) 
						{
							swal('', 'Tiene conceptos sin asignar', 'error');
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
		validate();
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 23});
		generalSelect({'selector': '.js-projects-destination', 'model': 21});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-areas-destination",
					"placeholder"				=> "Seleccione la dirección",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-departments-destination",
					"placeholder"				=> "Seleccione el departamento",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		count = 0;
		$(document).on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		})
		.on('change','input[name="status"]',function()
		{
			if ($('input[name="status"]:checked').val() == "4") 
			{
				$("#rechaza").slideUp("slow").addClass('hidden');
				$("#aceptar").slideToggle("slow").removeClass('hidden');
				@component('components.scripts.selects',["selects" => $selects]) @endcomponent
			}
			else if ($('input[name="status"]:checked').val() == "6") 
			{
				$("#aceptar").slideUp("slow").addClass('hidden');
				$("#rechaza").slideToggle("slow").removeClass('hidden');
			}
		})

		/*$('.subtotal').text("$"+sumatotal);
		sumatotal = 0;
		$('.importe').each(function(i, v)
			{
				valor		= parseFloat($(this).val());
				sumatotal	= sumatotal + valor;
			});*/
	});
</script>
@endsection