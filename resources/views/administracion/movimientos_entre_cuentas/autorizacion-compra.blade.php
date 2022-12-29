@extends('layouts.child_module')

@section('data')
	@php
		$taxes = $retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	</div>
	@php
		$requestUser			=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser			=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$requestAccountOrigin	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOrigin);
		$requestAccount			=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestiny);
		$modelTable	=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->purchaseEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",					$request->purchaseEnterprise->first()->numberOrder!="" ? htmlentities($request->purchaseEnterprise->first()->numberOrder) : '---'],
			["Fiscal:",								$request->taxPayment == 1 ? "Sí" : "No"],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOrigin)->name],
			["Dirección Origen:",					App\Area::find($request->purchaseEnterprise->first()->idAreaOrigin)->name],
			["Departamento Origen:",				App\Department::find($request->purchaseEnterprise->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen:",		$requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
			["Proyecto Origen:",					App\Project::find($request->purchaseEnterprise->first()->idProjectOrigin)->proyectName],
			["Empresa Destino:",					App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestiny)->name],
			["Clasificación del Gasto Destino:",	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")"],
			["Proyecto Destino:",					App\Project::find($request->purchaseEnterprise->first()->idProjectDestiny)->proyectName],
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
		$countConcept = 1;
		foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$taxesConcept=0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept+=$tax->amount;
			}
			$retentionConcept=0;
			foreach ($detail->retentions as $ret)
			{
				$retentionConcept+=$ret->amount;
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$countConcept]
				],
				[
					"content"	=>	["label"	=>	$detail->quantity]
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->unit)]
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->unitPrice,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->tax,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($taxesConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($retentionConcept,2)]
				],
				[
					"content"	=>	["label"	=>	"$ ".number_format($detail->amount,2)]
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('attributeExBody')
			id="body"
		@endslot
	@endcomponent
	@php
		foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
			foreach ($detail->retentions as $ret)
			{
				$retentions += $ret->amount;
			}
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->subtotales,2,".",",")]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2)]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2)]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->tax,2,".",",")]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2,".",",")]]]
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
		@slot('attributeExComment')
			readonly
		@endslot
		@slot('textNotes')
			{{$request->purchaseEnterprise->first()->notes}}
		@endslot
	@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		CONDICIONES DE PAGO
	@endcomponent
	@php
		if ($request->purchaseEnterprise->first()->idbanksAccounts != "")
		{
			$valueBank			=	$request->purchaseEnterprise->first()->banks->bank->description;
			$valueAlias			=	$request->purchaseEnterprise->first()->banks->alias;
			$valueAccount		=	$request->purchaseEnterprise->first()->banks->account != "" ? $request->purchaseEnterprise->first()->banks->account : "---";
			$valueClabe			=	$request->purchaseEnterprise->first()->banks->clabe != "" ? $request->purchaseEnterprise->first()->banks->clabe : "---";
			$valueBranck		=	$request->purchaseEnterprise->first()->banks->branch != "" ? $request->purchaseEnterprise->first()->banks->branch : "---";
			$valueReference		=	$request->purchaseEnterprise->first()->banks->reference != "" ? $request->purchaseEnterprise->first()->banks->reference : "---";
		}
		$modelTable	=
		[
			"Tipo de moneda"	=>	$request->purchaseEnterprise->first()->typeCurrency,
			"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->purchaseEnterprise->first()->paymentDate)->format('d-m-Y'),
			"Forma de pago"		=>	$request->purchaseEnterprise->first()->paymentMethod->method,
			"Banco"				=>	$valueBank,
			"Alias"				=>	$valueAlias,
			"Cuenta"			=>	$valueAccount,
			"Clabe"				=>	$valueClabe,
			"Sucursal"			=>	$valueBranck,
			"Referencia"		=>	$valueReference,
			"Importe a pagar"	=>	"$ ".number_format($request->purchaseEnterprise->first()->amount,2),
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
		if (count($request->purchaseEnterprise->first()->documentsPurchase)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->purchaseEnterprise->first()->documentsPurchase as $doc)
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
								"attributeEx"	=>	"type=\"button\"target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
								"label"			=>	"Archivo"
							]
						],
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
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		DATOS DE REVISIÓN
	@endcomponent
	@php
		$requestAccountOrigin	=	App\Account::find($request->purchaseEnterprise->first()->idAccAccOriginR);
		$requestAccount			=	App\Account::find($request->purchaseEnterprise->first()->idAccAccDestinyR);
		$modelTable				=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->purchaseEnterprise->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->purchaseEnterprise->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->purchaseEnterprise->first()->idProjectOriginR)->proyectName,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->purchaseEnterprise->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Nombre del Proyecto de Destino"		=>	App\Project::find($request->purchaseEnterprise->first()->idProjectDestinyR)->proyectName,
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor')
		@slot('classEx')
			mt-12
		@endslot
		ETIQUETAS ASIGNADAS
	@endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail)
		{
			$valueLabel = "";
			foreach ($detail->labels as $label)
			{
				$valueLabel	.=	$label->label->description.", ";
			}
			$body	=
			[
				[
					"content"	=>	["label"	=>	$detail->quantity." ".htmlentities($detail->unit)],
				],
				[
					"content"	=>	["label"	=>	htmlentities($detail->description)],
				],
				[
					"content"	=>	["label"	=>	$valueLabel],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
	
	@component('components.forms.form',["attributeEx" => "method=\"POST\" id=\"container-alta\" action=\"".route('movements-accounts.authorization.update', $request->folio)."\"" ,"methodEx" => "PUT"])
		<div class="text-center mt-6">
			@component('components.labels.label')
				¿Desea autorizar ó rechazar la solicitud?
			@endcomponent
		</div>
		@component('components.containers.container-approval')
			@slot('attributeExButton')
				name="status" id="aprobar" value="5"
			@endslot
			@slot('attributeExButtonTwo')
				id="rechazar" name="status" value="7"
			@endslot
		@endcomponent
		<div id="aceptar" class="mt-8 hidden">
			@component('components.labels.label')
				Comentarios (opcional)
			@endcomponent
			@component('components.inputs.text-area')
				@slot('attributeEx')
					name="authorizeCommentA"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center mt-4">
			@component('components.buttons.button', ["variant" => "primary"])
				@slot('attributeEx')
					type="submit" name="enviar" value="ENVIAR SOLICITUD"
				@endslot
				@slot('label')
					ENVIAR SOLICITUD
				@endslot
			@endcomponent
			@component('components.buttons.button', ["variant" => "reset"])
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
				@slot('label')
					REGRESAR
				@endslot
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('scripts')
<script>
	swal({
		icon: '{{ asset(getenv('LOADING_IMG')) }}',
		button: false,
		timer: 1000,
	});
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