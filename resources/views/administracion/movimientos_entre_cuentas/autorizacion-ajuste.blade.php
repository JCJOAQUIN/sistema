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
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->adjustment->first()->title)." - ".($request->adjustment->first()->datetitle != null ? Carbon\Carbon::createFromFormat('Y-m-d', $request->adjustment->first()->datetitle)->format('d-m-Y') : "")],
			["Comentarios:",						$request->adjustment->first()->commentaries!="" ? htmlentities($request->adjustment->first()->commentaries) : '---'],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->adjustment->first()->idEnterpriseOrigin)->name],
			["Empresa Destino:",					App\Enterprise::find($request->adjustment->first()->idEnterpriseDestiny)->name],
			["Dirección Destino:",					App\Area::find($request->adjustment->first()->idAreaDestiny)->name],
			["Departamento Destino:",				App\Department::find($request->adjustment->first()->idDepartamentDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto Destino:",					App\Project::find($request->adjustment->first()->idProjectDestiny)->proyectName],
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
	<div class="folios justify-between grid md:grid-cols-2 grid-cols-1">
		@foreach($request->adjustment->first()->adjustmentFolios as $af)
			<div class="col-span-1 mx-2">
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="hidden" value="{{ $af->idFolio }}"
					@endslot
					@slot('classEx')
						folios_adjustment
					@endslot
				@endcomponent
				@php
					$modelTable	=
					[
						["Empresa:",					$af->requestModel->reviewedEnterprise->name],
						["Dirección:",					$af->requestModel->reviewedDirection->name],
						["Departamento:",				$af->requestModel->reviewedDepartment->name],
						["Clasificación del gasto:",	$af->requestModel->accountsReview()->exists() ? $af->requestModel->accountsReview->account.' - '.$af->requestModel->accountsReview->description.' ('.$af->requestModel->accountsReview->content.")" : 'Varias'],
						["Proyecto:",					$af->requestModel->reviewedProject->proyectName]
					];
				@endphp
				@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
					@slot('classEx')
						mt-4
					@endslot
					@slot('title')
						FOLIO #{{ $af->idFolio }}
					@endslot
				@endcomponent
			</div>
		@endforeach
	</div>
	<div id="detail" style="display: none;"></div>
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DEL PEDIDO
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Solicitud de"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Unidad"],
				["value"	=>	"Descripción"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"],
			]
		];
		foreach($request->adjustment->first()->adjustmentFolios as $detail)
		{
			$countConcept	=	1;
			switch ($detail->requestModel->kind)
			{
				case '1':
					foreach ($detail->requestModel->purchases->first()->detailPurchase as $detpurchase)
					{
						$taxesConcept		=	0;
						$retentionConcept	=	0;
						foreach ($detpurchase->taxes as $tax)
						{
							$taxesConcept+=$tax->amount;
						}
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
							]
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
						foreach ($detexpenses->retentions as $ret)
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
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	"---"]
							],
							[
								"content"	=>	["label"	=>	$detexpenses->description]
							],
							[
								"content"	=>	["label"	=>	" $".number_format($detexpenses->unitPrice,2)]
							],
							[
								"content"	=>	["label"	=>	" $".number_format($detexpenses->tax,2)]
							],
							[
								"content"	=>	["label"	=>	" $".number_format($taxesConcept,2)]
							],
							[
								"content"	=>	["label"	=>	" $".number_format($retentionConcept,2)]
							],
							[
								"content"	=>	["label"	=>	" $".number_format($detexpenses->amount,2)]
							]
						];
						$countConcept++;
						$modelBody[]	=	$body;
					}
					break;
				case '9':
					foreach ($detail->requestModel->refunds->first()->refundDetail as $detrefund)
					{
						$taxesConcept	=	0;
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
								"content"	=>	["label"	=>	$detrefund->concept]
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
							]
						];
						$countConcept++;
						$modelBody[]	=	$body;
					}
					break;
			}
			$countConcept = 1;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeExBody')
			id="body"
		@endslot
	@endcomponent
	<div class="totales2">
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
				["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"subtotal\"",	"label"	=>	"$ ".number_format($request->adjustment->first()->subtotales,2,".",",")]]],
				["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountAA\"",	"label"	=>	"$ ".number_format($request->adjustment->first()->additionalTax,2)]]],
				["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"amountR\"",		"label"	=>	"$ ".number_format($request->adjustment->first()->retention,2)]]],
				["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"totaliva\"",	"label"	=>	"$ ".number_format($request->adjustment->first()->tax,2)]]],
				["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"h-10 py-2",	"attributeEx"	=>	"name=\"total\" id=\"input-extrasmall\"",	"label"	=>	"$ ".number_format($request->adjustment->first()->amount,2)]]],
			];
		@endphp
		@component('components.templates.outputs.form-details', ["modelTable" => $modelTable]) @endcomponent
	</div>
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
			$modelHead	=	["Documentos", "Fecha"];
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
							],
						]
					],
					[
						"content"	=>	["label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H:i:s')],
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
					"content"	=>	["label"	=>	 "NO HAY DOCUMENTOS"],
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
		$requestAccount = App\Account::find($request->adjustment->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->adjustment->first()->idEnterpriseDestinyR)->name,
			"Nombre de la Dirección de Destino"		=>	App\Area::find($request->adjustment->first()->idAreaDestinyR)->name,
			"Nombre del Departamento de Destino"	=>	App\Department::find($request->adjustment->first()->idDepartamentDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Destino"		=>	App\Project::find($request->adjustment->first()->idProjectDestinyR)->proyectName,
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment)
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('movements-accounts.authorization.update', $request->folio)."\"", "methodEx" => "PUT"])
		<div class="form-container mt-12">
			<div class="flex justify-center">
				@component('components.labels.label')
					@slot('attributeEx')
						id="label-inline"
					@endslot
					¿Desea aprobar ó rechazar la solicitud?
				@endcomponent
			</div>
			@component('components.containers.container-approval')
				@slot('attributeExButton')
					name="status"
					id="aprobar"
					value="5"
				@endslot
				@slot('attributeExButtonTwo')
					id="rechazar"
					name="status"
					value="7"
				@endslot
			@endcomponent
		</div>
		<div id="aceptar" class="hidden">
			<div class="flex-wrap w-full grid md:grid-cols-1 gap-x-10 mt-4">
				@component('components.labels.label')
					Comentarios (opcional)
				@endcomponent
				@component('components.inputs.text-area')
					@slot('attributeEx')
						name="authorizeCommentA"
					@endslot
				@endcomponent
			</div>
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button',["variant" => "primary", "attributeEx" => "type=\"submit\" name=\"enviar\" value=\"ENVIAR SOLICITUD\"", "label" => "ENVIAR SOLICITUD"]) @endcomponent
			@php
				$href	=	isset($option_id) ? url(getUrlRedirect($option_id)) : url(getUrlRedirect($child_id));
			@endphp
			@component('components.buttons.button', ["variant" => "reset", "attributeEx" => "href=\"".$href."\"", "buttonElement" => "a", "classEx" => "load-actioner", "label" => "REGRESAR"]) @endcomponent
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
