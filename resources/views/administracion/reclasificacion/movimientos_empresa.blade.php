@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	App\User::find($request->idRequest);
		$elaborateUser	=	App\User::find($request->idElaborate);
		$accountOrigin	=	App\Account::find($request->movementsEnterprise->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->movementsEnterprise->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->movementsEnterprise->first()->title)." - ".$request->movementsEnterprise->first()->datetitle !="" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->datetitle)->format('d-m-Y') :  null],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser->name." ".$requestUser->last_name." ".$requestUser->scnd_last_name],
			["Elaborado por:",						$elaborateUser->name." ".$elaborateUser->last_name." ".$elaborateUser->scnd_last_name],
			["Empresa Origen:",						App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOrigin)->name],
			["Clasificación del Gasto Origen:",		$accountOrigin->account." - ".$accountOrigin->description." (".$requestAccount->content.")"],
			["Empresa Destino:",					App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseDestiny)->name],
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
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->movementsEnterprise->first()->typeCurrency,
			"Fecha de pago"		=>	$request->movementsEnterprise->first()->paymentDate !="" ? Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->paymentDate)->format('d-m-Y') : null,
			"Forma de pago"		=>	$request->movementsEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->movementsEnterprise->first()->amount,2),
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
		if (count($request->movementsEnterprise->first()->documentsMovements)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->movementsEnterprise->first()->documentsMovements as $doc)
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
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y')],
					],
				];
				$modelBody[]	=	$body;
			}
		}
		else
		{
			$modelHead	=	["Documento"];
			$body		=
				[
					[
						"content"	=>	["label"	=>	"NO HAY DOCUMENTOS"],
					],
				];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$accountOrigin	=	App\Account::find($request->movementsEnterprise->first()->idAccAccOriginR);
		$requestAccount	=	App\Account::find($request->movementsEnterprise->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	isset($request->reviewedUser->name) ? $request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name : "---",
			"Nombre de la Empresa de Origen"		=>	isset($request->movementsEnterprise->first()->idEnterpriseOriginR) ? App\Enterprise::find($request->movementsEnterprise->first()->idEnterpriseOriginR)->name : "---",
			"Clasificación del Gasto de Origen"		=>	isset($accountOrigin->account) ? $accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")" : "---",
			"Clasificación del Gasto de Destino"	=>	isset($requestAccount->account) ? $requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")" : "---",
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
				"Comentarios"	=>	$request->paymentComment == "" ? "Sin comentarios" : $request->paymentComment
			];
		@endphp
		@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@endif
	@php
		$payments		=	App\Payment::where('idFolio',$request->folio)->get();
		$total			=	$request->movementsEnterprise->first()->amount;
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
					$componentExButton[]	=
					[
						"kind"			=>	"components.buttons.button",
						"label"			=>	"PDF",
						"buttonElement"	=>	"a",
						"variant"		=>	"dark-red",
						"attributeEx"	=>	"type=\"button\"target=\"_blank\" href=\"".asset('docs/payments/'.$doc->path)."\" title=\"".$doc->path."\""
					];
				}
				$body	=
				[
					[
						"content"	=>	["label"	=>	$pay->accounts->account.' - '.$pay->accounts->description],
					],
					[
						"content"	=>	["label"	=>	"$.".number_format($pay->amount,2)],
					],
					[
						"content"	=>	$componentExButton,
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$pay->paymentDate)->format('d-m-Y')],
					],
				];
				$totalPagado	+=	$pay->amount;
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody, "themeBody" => "striped"])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
		@php
			$pendingPay	=	$total-$totalPagado;
			$modelTable	=
			[
				["label"	=>	"Total pagado:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$totalPagado]]],
				["label"	=>	"Resta por pagar:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".$pendingPay]]],
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
			$modelHead	=	["Empresa Origen","Clasificación del Gasto Origen","Clasificación del Gasto Destino"];
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
								"attributeEx"	=>	"type=\"hidden\" value=\"".$date->format('d-m-Y')."\"",
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
							["label"	=>	$r->accountsOrigin->account.' '.$r->accountsOrigin->description.' ('.$r->accountsOrigin->content.")"],
							[
								"kind"			=>	"components.inputs.input-text",
								"attributeEx"	=>	"type=\"hidden\" value=\"".$r->accountsOrigin->account.' '.$r->accountsOrigin->description.' ('.$r->accountsOrigin->content.")\"",
								"classEx"		=>	"account"
							],
						],
					],
					[
						"content"	=>	["label"	=>	$r->accountsDestiny->account.' '.$r->accountsDestiny->description.' ('.$r->accountsDestiny->content.")"],
					],
				];
				$modelBody[]	=	$body;
			}
		@endphp
		@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])
			@slot('classEx')
				mt-4
			@endslot
		@endcomponent
	@endif
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CLASIFICACIÓN ACTUAL
	@endcomponent
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('reclassification.update-movements-enterprise',$request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.subtitle', ["label" => "CUENTA DE ORIGEN"]) @endcomponent
		@component('components.containers.container-form')
			<div class="col-span-2">
				@component('components.labels.label') Empresa: @endcomponent
				@php
					$optionsEnterprise	=	[];
					foreach (App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get() as $enterprise)
					{
						if ($request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idEnterpriseOriginR == $enterprise->id)
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
						js-enterprises
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Clasificación de gasto: @endcomponent
				@php
					$options	=	collect();
					if (isset($request) && $request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idAccAccOriginR !="")
					{
						$options	=	$options->concat([["value"	=>	$request->movementsEnterprise->first()->accountOrigin->idAccAcc,	"description"	=>	$request->movementsEnterprise->first()->accountOrigin->account." - ".$request->movementsEnterprise->first()->accountOrigin->description." (".$request->movementsEnterprise->first()->accountOrigin->content.")",	"selected"	=>	"selected"]]);
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
		<div class="col-span-2 md:col-start-2 md:col-end-4">
			@component('components.labels.label') Clasificación de gasto: @endcomponent
			@php
				$options	=	collect();
				if (isset($request) && $request->movementsEnterprise()->exists() && $request->movementsEnterprise->first()->idAccAccDestinyR !="")
				{
					$options	=	$options->concat([["value"	=>	$request->movementsEnterprise->first()->accountDestiny->idAccAcc,	"description"	=>	$request->movementsEnterprise->first()->accountDestiny->account." - ".$request->movementsEnterprise->first()->accountDestiny->description." (".$request->movementsEnterprise->first()->accountDestiny->content.")",	"selected"	=>	"selected"]]);
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
		@component('components.labels.label', ["classEx" => "mt-8"]) Comentarios (opcional) @endcomponent
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
		generalSelect({'selector': '.js-accounts-origin', 'depends': '.js-enterprises', 'model': 18});
		generalSelect({'selector': '.js-accounts-destination', 'depends': '.js-enterprises', 'model': 32});
		@php
			$selects = collect([
				[
					"identificator"				=> ".js-enterprises",
					"placeholder"				=> "Seleccione la empresa",
					"language"					=> "es",
					"maximumSelectionLength"	=> "1"
				]
			]);
		@endphp
		@component('components.scripts.selects',["selects" => $selects]) @endcomponent
		$(document).on('change','.js-enterprises',function()
		{
			$('.js-accounts-origin').empty();
			$('.js-accounts-destination').empty();
		});
	});
</script>
@endsection
