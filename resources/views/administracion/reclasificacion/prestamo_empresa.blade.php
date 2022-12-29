@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->datetitle)->format('d-m-Y')],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",						$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen:",						App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseOrigin)->name],
			["Clasificación del Gasto Origen:",		$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")"],
			["Empresa Destino:",					App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
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
		CONDICIONES DE PAGO
	@endcomponent
	@php	
		$time		=	$request->loanEnterprise->first()->paymentDate != '' ? strtotime($request->loanEnterprise->first()->paymentDate) : '';
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->loanEnterprise->first()->currency,
			"Fecha de pago"		=>	$request->loanEnterprise->first()->paymentDate!='' ? Carbon\Carbon::createFromformat('Y-m-d',$request->loanEnterprise->first()->paymentDate)->format('d-m-Y') : '',
			"Forma de pago"		=>	$request->loanEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->loanEnterprise->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
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
		if (count($request->loanEnterprise->first()->documentsLoan)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->loanEnterprise->first()->documentsLoan as $doc)
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
						"content"	=>
						[
							"label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')
						],
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
					"content"	=>
					[
						["label"	=>	"NO HAY DOCUMENTOS"]
					],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOriginR);
		$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestinyR);
	@endphp
	@php
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")",
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
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
				"Comentarios"	=>	$request->authorizeComment == "" ? "Sin comentarios" : htmlentities($request->authorizeComment)
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
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment)
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->loanEnterprise->first()->amount;
		$totalPagado	=	0;
	@endphp
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
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : htmlentities($request->paymentComment)
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->loanEnterprise->first()->amount;
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
				if (count($pay->documentsPayments) > 0)
				{
					$componentExtButton	=	[];
					foreach ($pay->documentsPayments as $doc)
					{
						$componentExtButton[]	=
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"dark-red",
							"label"			=>	"PDF",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"".$doc->path."\""
						];
					}
				}
				else
				{
					$componentExtButton	=	["label"	=>	"Sin documento"];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description],
					],
					[
						"content"	=>	["label"	=>	'$'.number_format($pay->amount,2)],
					],
					[
						"content"	=>	$componentExtButton,
					],
					[
						"content"	=>	["label"	=>		Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')],
					],
				];
				$totalPagado	+=	$pay->amount;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$pendigPay	=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$totalPagado]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$pendigPay]]],
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
					["value"	=>	"Empresa Origen"],
					["value"	=>	"Clasificación del Gasto Origen"],
					["value"	=>	"Empresa Destino"],
					["value"	=>	"Clasificación del Gasto Destino"]
				]
			];
			foreach($request->request_has_reclassification->sortByDesc('date') as $r)
			{
				$date	=	new \DateTime($r->date);
				$body	=
				[
					[
						"content"	=>
						[
							["label"	=>	$r->enterpriseOrigin->name],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->enterpriseOrigin->name."\"",
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
								"attributeEx"	=>	"type=\"hidden\" value=\"".htmlentities($r->commentaries)."\"",
								"classEx"		=>	"commentaries"
							]
						]
					],
					[
						"content"	=>
						[
							["label"	=>	$r->accountsOrigin->account.' '.$r->accountsOrigin->description],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accountsOrigin->account.' '.$r->accountsOrigin->description."\"",
								"classEx"		=>	"account"
							]
						],
					],
					[
						"content"	=>	["label"	=>	$r->enterpriseDestiny->name],
					],
					[
						"content"	=>	["label"	=>	$r->accountsDestiny->account.' '.$r->accountsDestiny->description],
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
	{{-- @component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-purchaserecord', $request->folio)."\"", "methodEx" => "PUT"]) --}}
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-loan-enterprise',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.title-divisor')
			@slot('classEx')
				mt-12
			@endslot
			CLASIFICACIÓN ACTUAL
		@endcomponent
		@component('components.labels.subtitle', ["label" => "CUENTA DE ORIGEN", "classExContainer" => "mt-8"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Empresa:
				@endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idEnterpriseOriginR == $enterprise->id)
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
						name="enterpriseid_origin"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises-origin
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Clasificación de gasto:
				@endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idAccAccOriginR !="")
					{
						$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountOrigin->account." - ".$request->loanEnterprise->first()->accountOrigin->description." (".$request->loanEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						multiple="multiple"
						name="accountid_origin"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-origin
					@endslot
				@endcomponent
			</div>
		@endcomponent
		@component('components.labels.subtitle', ["label" => "CUENTA DE DESTINO"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Empresa:
				@endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idEnterpriseDestinyR == $enterprise->id)
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
						name="enterpriseid_destination"
						multiple="multiple"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-enterprises-destination
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					@slot('classEx')
						font-bold
					@endslot
					Clasificación de gasto:
				@endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->loanEnterprise()->exists() && $request->loanEnterprise->first()->idAccAccDestinyR !="")
					{
						$options	=	$options->concat([["value"	=>	$request->loanEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->loanEnterprise->first()->accountDestiny->account." - ".$request->loanEnterprise->first()->accountDestiny->description." (".$request->loanEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
					}
				@endphp
				@component('components.inputs.select', ["options" => $options])
					@slot('attributeEx')
						multiple="multiple"
						name="accountid_destination"
						data-validation="required"
					@endslot
					@slot('classEx')
						js-accounts-destination
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
@endsection

@section('scripts')
<script>
	$.validate(
	{
		form: '#container-alta',
		onSuccess : function($form)
		{
			return true;
		},
		onError : function($form)
		{
			swal('','{{ Lang::get("messages.form_error") }}','error');
			return false;
		}
	});

	$(document).ready(function()
	{
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises-origin', 'model': 18});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises-destination', 'model': 32});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises-origin",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				],
				[
					"identificator"				=> ".js-enterprises-destination",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		count = 0;
		$(document).on('change','.js-enterprises-origin',function()
		{
			$('.js-accounts-origin').empty();
		})
		.on('change','.js-enterprises-destination',function()
		{
			$('.js-accounts-destination').empty();
		});
	});
</script>
@endsection
