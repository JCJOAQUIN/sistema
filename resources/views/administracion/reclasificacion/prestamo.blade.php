@extends('layouts.child_module')
@section('data')
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$requestAccount	=	App\Account::find($request->account);
		$modelTable		=
		[
			["Folio:",						$request->folio],
			["Título y fecha:",				htmlentities($request->loan->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loan->first()->datetitle)->format('d-m-Y')],
			["Solicitante:",				$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",				$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa:",					App\Enterprise::find($request->idEnterprise)->name],
			["Dirección:",					App\Area::find($request->idArea)->name],
			["Departamento:",				App\Department::find($request->idDepartment)->name],
			["Clasificación del gasto:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
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
		DATOS DEL SOLICITANTE
	@endcomponent
	@php
		foreach ($request->loan as $loan)
		{
			$reference	=	($loan->reference != "" ? htmlentities($loan->reference) : "---");
			$amount		=	"$ ".number_format($loan->amount,2);
			$payment	=	isset($loan->paymentMethod->method) ? $loan->paymentMethod->method : "---";
		}
		foreach ($request->loan as $loan)
		{
			foreach (App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
			{
				if ($loan->idEmployee == $bank->idEmployee)
				{
					$bankName		=	$bank->description;
					$aliasBank		=	$bank->alias!=null ? $bank->alias : '---';
					$cardNum		=	$bank->cardNumber!=null ? $bank->cardNumber : '---';
					$clabeBank		=	$bank->clabe!=null ? $bank->clabe : '---';
					$bankAccount	=	$bank->account!=null ? $bank->account : '---';
				}
			}
		}
		$modelTable	=
		[
			"Nombre"			=>	$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name,
			"Forma de pago"		=>	$payment,
			"Referencia"		=>	$reference,
			"Importe"			=>	$amount,
			"Banco"				=>	$bankName,
			"Alias"				=>	$aliasBank,
			"Número de tarjeta"	=>	$cardNum,
			"CLABE"				=>	$clabeBank,
			"Número de cuenta"	=>	$bankAccount,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.inputs.input-text')
		@slot('attributeEx')
			type="hidden"
			name="employee_number"
			id="efolio"
			placeholder="Número de empleado"
			value="@foreach($request->loan as $loan){{ $loan->idUsers }}@endforeach"
		@endslot
		@slot('classEx')
			employee_number
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$reviewAccount		=	App\Account::find($request->accountR);
		$labelsDescription	=	"";
		foreach ($request->labels as $label)
		{
			$labelsDescription	.=	$label->description.", ";
		}
		$modelTable	=
		[
			"Revisó"					=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa"		=>	App\Enterprise::find($request->idEnterpriseR)->name,
			"Nombre de la Dirección"	=>	$request->reviewedDirection->name,
			"Nombre del Departamento"	=>	App\Department::find($request->idDepartamentR)->name,
			"Clasificación del gasto"	=>	$reviewAccount->account." - ".$reviewAccount->description,
			"Etiquetas"					=>	$labelsDescription,
			"Comentarios"				=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment)
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@if($request->idAuthorize != "")
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DE AUTORIZACIÓN
		@endcomponent
		@php
			$modelTable	=
			[
				"Autorizó"		=>	$request->authorizedUser->name." ".$request->authorizedUser->last_name." ".$request->authorizedUser->scnd_last_name,
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@if($request->status == 13)
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			DATOS DE PAGOS
		@endcomponent
		@php
			$modelTable	=
			[
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment),
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->loan->first()->amount;
		$totalPagado	=	0;
	@endphp
	@if(count($payments) > 0)
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			HISTORIAL DE PAGOS
		@endcomponent
		@php
			$modelHead	=	[];
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"Cuenta"],
					["value"	=>	"Cantidad"],
					["value"	=>	"Documento"],
					["value"	=>	"Fecha"]
				]
			];
			foreach($payments as $pay)
			{
				foreach ($pay->documentsPayments as $doc)
				{
					$componentExtButton[]	=
					[
						"kind"			=>	"components.buttons.button",
						"variant"		=>	"dark-red",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"attributeEx"	=>	"target=\"_blank\" type=\"button\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"".$doc->path."\"",
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description.' ('.$pay->accounts->content.")"],
					],
					[
						"content"	=>	["label"	=>	'$'.number_format($pay->amount,2)],
					],
					[
						"content"	=>	$componentExtButton,
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')],
					]
				];
				$totalPagado += $pay->amount;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$totalPay	=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($totalPagado,2)]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($totalPay,2)]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@endcomponent
	@endif
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
					["value"	=>	"Acción"]
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
							]
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
							]
						],
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accounts->account.' - '.$r->accounts->description.' ('.$r->accounts->content.")"],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accounts->account.' '.$r->accounts->description.' ('.$r->accounts->content.")"."\"",
								"classEx"		=>	"account"
							]
						],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"<span class='icon-search'></span>",
								"attributeEx"	=>	"title=\"Ver datos\" data-target=\"#modalUpdate\" data-toggle=\"modal\"",
								"classEx"		=>	"view-data"
							]
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id\"formsearch\" action=\"".route('reclassification.update-loan',$request->folio)."\"", "methodEx" => "PUT"])
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
						name="idEnterpriseR"
						multiple="multiple"
						data-validation="required"
						id="multiple-enterprisesR"
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
								"description"	=>	strlen($area->name) >= 35 ? substr(strip_tags($area->name),0,35) : $area->name
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
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,	"selected"	=>	"selected"];
						}
						else
						{
							$optionsDepartment[]	=	["value"	=>	$department->id,	"description"	=>	$department->name,];
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
					foreach (App\Account::orderNumber()->where('idEnterprise',$request->idEnterpriseR)->where('selectable',1)->get() as $account)
					{
						if ($request->accountR == $account->idAccAcc)
						{
							$optionsAccount[]	=
							[
								"value"			=>	$account->idAccAcc,
								"description"	=>	$account->account.' - '.$account->description." (".$account->content.")",
								"selected"		=>	"selected"
							];
						}
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionsAccount])
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
		@endcomponent
		@component('components.labels.label',["classEx" => "mt-8"]) Comentarios (opcional): @endcomponent
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
					value="RECLASIFICAR"
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
	$(document).ready(function()
	{
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
	
		$(document).on('click','.view-data',function()
		{
			$('[name="view-name"]').text($(this).parent('div').parent('div').parent('div').find('.name').val());
			$('[name="view-date"]').text($(this).parent('div').parent('div').parent('div').find('.date').val());
			$('[name="view-enterprise"]').text($(this).parent('div').parent('div').parent('div').find('.enterprise').val());
			$('[name="view-direction"]').text($(this).parent('div').parent('div').parent('div').find('.direction').val());
			$('[name="view-department"]').text($(this).parent('div').parent('div').parent('div').find('.department').val());
			$('[name="view-project"]').text($(this).parent('div').parent('div').parent('div').find('.project').val());
			$('[name="view-account"]').text($(this).parent('div').parent('div').parent('div').find('.account').val());
			$('[name="view-commentaries"]').text($(this).parent('div').parent('div').parent('div').find('.commentaries').val());
			$("#modalUpdate").show();
		})
	});


</script>

@endsection
