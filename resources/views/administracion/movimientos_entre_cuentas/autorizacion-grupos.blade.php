@extends('layouts.child_module')

@section('data')
	@php
		$taxes	=	$retentions = 0;
	@endphp
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser	=	$request->requestUser()->exists() ? $request->requestUser->fullName() : "Sin solicitante";
		$elaborateUser	=	$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : "Sin elaborador";
		$accountOrigin	=	App\Account::find($request->groups->first()->idAccAccOrigin);
		$requestAccount	=	App\Account::find($request->groups->first()->idAccAccDestiny);
		$modelTable		=
		[
			["Folio:",								$request->folio],
			["Título y fecha:",						htmlentities($request->groups->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->groups->first()->datetitle)->format('d-m-Y')],
			["Número de Orden:",					$request->groups->first()->numberOrder!="" ? htmlentities($request->groups->first()->numberOrder) : '---'],
			["Fiscal:",								$request->taxPayment == 1 ? "Si" : "No"],
			["Tipo de Operación:",					$request->groups->first()->operationType],
			["Solicitante:",						$requestUser],
			["Elaborado por:",						$elaborateUser],
			["Empresa Origen:",						App\Enterprise::find($request->groups->first()->idEnterpriseOrigin)->name],
			["Dirección Origen:",					App\Area::find($request->groups->first()->idAreaOrigin)->name],
			["Departamento Origen:",				App\Department::find($request->groups->first()->idDepartamentOrigin)->name],
			["Clasificación del Gasto Origen:",		$accountOrigin->account." - ".$accountOrigin->description." (".$accountOrigin->content.")"],
			["Proyecto Origen:",					App\Project::find($request->groups->first()->idProjectOrigin)->proyectName],
			["Empresa Destino:",					App\Enterprise::find($request->groups->first()->idEnterpriseDestiny)->name],
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
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PROVEEDOR"]) @endcomponent
	@php
		$modelTable	=
		[
			"Razón Social"	=>	$request->groups->first()->provider->businessName,
			"RFC"			=>	$request->groups->first()->provider->rfc,
			"Teléfono"		=>	$request->groups->first()->provider->phone,
			"Calle"			=>	$request->groups->first()->provider->address,
			"Número"		=>	$request->groups->first()->provider->number,
			"Colonia"		=>	$request->groups->first()->provider->colony,
			"CP"			=>	$request->groups->first()->provider->postalCode,
			"Ciudad"		=>	$request->groups->first()->provider->city,
			"Estado"		=>	App\State::find($request->groups->first()->provider->state_idstate)->description,
			"Contacto"		=>	$request->groups->first()->provider->contact,
			"Beneficiario"	=>	$request->groups->first()->provider->beneficiary,
			"Otro"			=>	$request->groups->first()->provider->commentaries,
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
				["value"	=>	"Banco"],
				["value"	=>	"Alias"],
				["value"	=>	"Cuenta"],
				["value"	=>	"Sucursal"],
				["value"	=>	"Referencia"],
				["value"	=>	"CLABE"],
				["value"	=>	"Moneda"],
				["value"	=>	"IBAN"],
				["value"	=>	"BIC/SWIFT"],
				["value"	=>	"Convenio"],
			]
		];
		foreach($request->groups->first()->provider->providerData->providerBank as $bank)
		{
			$marktr	=	$request->groups->first()->provider_has_banks_id == $bank->id ? "marktr" : "";
			if ($request->groups->first()->provider_has_banks_id == $bank->id)
			{
				$marktr	=	"marktr";
			}
			$bankIban		=	$bank->iban	=='' ? "---" : $bank->iban;
			$bankBic_swift	=	$bank->bic_swift=='' ? "---" : $bank->bic_swift;
			$bankAgreement	=	$bank->agreement=='' ? "---" : $bank->agreement;
			$body	=
			[
				"classEx"	=>	$marktr,
				[
					"content"	=>	["label"	=>	$bank->bank->description]
				],
				[
					"content"	=>	["label"	=>	$bank->alias]
				],
				[
					"content"	=>	["label"	=>	$bank->account]
				],
				[
					"content"	=>	["label"	=>	$bank->branch]
				],
				[
					"content"	=>	["label"	=>	$bank->reference]
				],
				[
					"content"	=>	["label"	=>	$bank->clabe]
				],
				[
					"content"	=>	["label"	=>	$bank->currency]
				],
				[
					"content"	=>	["label"	=>	$bankIban]
				],
				[
					"content"	=>	["label"	=>	$bankBic_swift]
				],
				[
					"content"	=>	["label"	=>	$bankAgreement]
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
			id="table2"
		@endslot
	@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL PEDIDO"]) @endcomponent
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
				["value"	=>	"Descripci&oacute;n"],
				["value"	=>	"Precio Unitario"],
				["value"	=>	"IVA"],
				["value"	=>	"Impuesto Adicional"],
				["value"	=>	"Retenciones"],
				["value"	=>	"Importe"],
			]
		];
		$countConcept = 1;
		foreach($request->groups->first()->detailGroups as $detail)
		{
			$taxesConcept		=	0;
			$retentionConcept	=	0;
			foreach ($detail->taxes as $tax)
			{
				$taxesConcept+=$tax->amount;
			}
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
					"content"	=>	["label"	=>	$detail->unit]
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
			$countConcept++;
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.table', ["modelHead" => $modelHead, "modelBody" => $modelBody])
		@slot('classEx')
			mt-4
		@endslot
		@slot('attributeEx')
			id="body"
		@endslot
	@endcomponent
	@php
		foreach ($request->groups->first()->detailGroups as $detail)
		{
			foreach ($detail->taxes as $tax)
			{
				$taxes += $tax->amount;
			}
		}
		foreach ($request->groups->first()->detailGroups as $detail)
		{
			foreach ($detail->retentions as $ret)
			{
				$retentions += $ret->amount;
			}
		}
		$modelTable	=
		[
			["label"	=>	"Subtotal:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->subtotales,2,".",","),	"attributeEx"	=>	"name=\"subtotal\""]]],
			["label"	=>	"Impuesto Adicional:",	"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($taxes,2,".",","),	"attributeEx"	=>	"name=\"amountAA\""]]],
			["label"	=>	"Retenciones:",			"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($retentions,2,".",","),	"attributeEx"	=>	"name=\"amountR\""]]],
			["label"	=>	"IVA:",					"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->tax,2,".",","),	"attributeEx"	=>	"name=\"totaliva\""]]],
			["label"	=>	"TOTAL:",				"inputsEx"	=>	[["kind"	=>	"components.labels.label",	"classEx"	=>	"py-2",	"label"	=>	"$ ".number_format($request->groups->first()->amount,2,".",","),	"attributeEx"	=>	"name=\"total\""]]],
		];
	@endphp
	@component('components.templates.outputs.form-details', ["modelTable" => $modelTable, "textNotes" => $request->groups->first()->notes, "classExComment" => "disabled", "attributeExComment" => "name=\"note\""])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DEL MOVIMIENTO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Importe Total"		=>	"$ ".$request->groups->first()->amount,
			"Comisión"			=>	"$ ".$request->groups->first()->commission,
			"Importe a retomar"	=>	"$ ".$request->groups->first()->amountRetake,
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
	@php
		$modelTable	=
		[
			"Referencia/Número de factura"	=>	($request->groups->first()->reference != "" ? htmlentities($request->groups->first()->reference) : "---"),
			"Tipo de moneda"				=>	$request->groups->first()->typeCurrency,
			"Fecha de pago"					=>	Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->PaymentDate)->format('d-m-Y'),
			"Forma de pago"					=>	$request->groups->first()->paymentMethod->method,
			"Estado  de factura"			=>	$request->groups->first()->statusBill,
			"Importe a paga"				=>	"$ ".number_format($request->groups->first()->amount,2),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DOCUMENTOS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		if (count($request->groups->first()->documentsGroups)>0)
		{
			$modelHead	=	["Documento", "Fecha"];
			foreach($request->groups->first()->documentsGroups as $doc)
			{
				$body	=
				[
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"label"			=>	"Archivo",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"type=\"button\" target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\""
							]
						],
					],
					[
						"content"	=>	["label"	=>	 Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->date)->format('d-m-Y H: i:s')]
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
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "DATOS DE REVISIÓN"]) @endcomponent
	@php
		$requestAccountOrigin = App\Account::find($request->groups->first()->idAccAccOriginR);
		$requestAccount = App\Account::find($request->groups->first()->idAccAccDestinyR);
		$modelTable	=
		[
			"Revisó"								=>	$request->reviewedUser->name." ".$request->reviewedUser->last_name." ".$request->reviewedUser->scnd_last_name,
			"Nombre de la Empresa de Origen"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseOriginR)->name,
			"Nombre de la Dirección de Origen"		=>	App\Area::find($request->groups->first()->idAreaOriginR)->name,
			"Nombre del Departamento de Origen"		=>	App\Department::find($request->groups->first()->idDepartamentOriginR)->name,
			"Clasificación del Gasto de Origen"		=>	$requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")",
			"Nombre del Proyecto de Origen"			=>	App\Project::find($request->groups->first()->idProjectOriginR)->proyectName,
			"Nombre de la Empresa de Destino"		=>	App\Enterprise::find($request->groups->first()->idEnterpriseDestinyR)->name,
			"Clasificación del Gasto de Destino"	=>	$requestAccount->account." - ".$requestAccount->description." (".$requestAccount->content.")",
			"Comentarios"							=>	$request->checkComment == "" ? "Sin comentarios" : htmlentities($request->checkComment),
		];
	@endphp
	@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
	@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "ETIQUETAS ASIGNADAS"]) @endcomponent
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Cantidad", "Descripción", "Etiquetas"];
		foreach($request->groups->first()->detailGroups as $detail)
		{
			$labelDescription = "";
			foreach ($detail->labels as $label)
			{
				$labelDescription	.=	$label->label->description.", ";
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
					"content"	=>	["label"	=>	$labelDescription],
				],
			];
			$modelBody[]	=	$body;
		}
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody, "attributeExBody" => "id=\"tbody-conceptsNew\"", "classExBody" => "request-validate"])@endcomponent
	@component('components.forms.form', ["attributeEx" => "id=\"container-alta\" method=\"POST\" action=\"".route('movements-accounts.authorization.update', $request->folio)."\"", "methodEx" => "PUT"])
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