@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions	=	0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser	=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->datetitle)->format('d-m-Y')],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
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
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->loanEnterprise->first()->currency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->loanEnterprise->first()->paymentMethod->method,
			"Importe a pagar"	=>	"$ ".number_format($request->loanEnterprise->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
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
						]
					],
					[
						"content"	=>	["label"	=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
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
				]
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOriginR);
		$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestinyR);
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
	@component('components.forms.form', ["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('movements-accounts.authorization.update', $request->folio)."\"", "methodEx" => "PUT"])
		@component('components.labels.label', ["label" => "¿Desea autorizar ó rechazar la solicitud?", "classEx" => "text-center"]) @endcomponent
		@component('components.containers.container-approval')
			@slot('attributeExButton')
				name="status" id="aprobar" value="5"
			@endslot
			@slot('attributeExButtonTwo')
				id="rechazar" name="status" value="7"
			@endslot
		@endcomponent
		<div class="hidden" id="aceptar">
			@component('components.labels.label', ["label" => "Comentarios (opcional)"]) @endcomponent
			@component('components.inputs.text-area', ["attributeEx" => "name=\"authorizeCommentA\""]) @endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-8">
			@component('components.buttons.button', ["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id)) ;
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "buttonElement" => "a", "attributeEx" => "href=\"".$href."\"", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
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
						swal("Cargando",{
							icon: '{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc: false
						});
						return true;
					}
					else
					{
						swal('', 'Debe seleccionar al menos un estado', 'error');
						return false;
					}
				}
			});
			count = 0;
			$(document).on('change','input[name="status"]',function()
			{
				$("#aceptar").slideDown("slow");
			});
		});
	</script>
@endsection