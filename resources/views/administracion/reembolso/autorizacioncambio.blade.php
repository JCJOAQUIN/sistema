@extends('layouts.child_module')
@section('data')
	@php
		$taxes	= 0;
	@endphp
	@if($request->refunds->first()->idRequisition != "")
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div><span class="icon-bullhorn"></span> Esta solicitud viene de la requisición #{{ $request->refunds->first()->idRequisition }}.</div>
			@endslot
		@endcomponent
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				<div class="flex flex-row">
					@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
						FOLIO:
					@endcomponent
					{{ $request->new_folio }}.
				</div>
				@if($request->refunds->first()->requisitionRequest->idProject == 75)
					<div class="flex flex-row">
						@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
							SUBPROYECTO/CÓDIGO WBS:
						@endcomponent
						{{ $request->refunds->first()->requisitionRequest->requisition->wbs->code_wbs }}.
					</div>
					@if($request->refunds->first()->requisitionRequest->requisition->edt()->exists())
						<div class="flex flex-row">
							@component("components.labels.label", ["classEx" => "font-bold text-blue-900"])
								CÓDIGO EDT:
							@endcomponent
							{{  $request->refunds->first()->requisitionRequest->requisition->edt->fullName() }}.
						</div>
					@endif
				@endif
			@endslot
		@endcomponent
	@endif
	<div class="sm:text-center text-left my-5">
		A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
	</div>
	@php
		$requestUser = App\User::find($request->idRequest);
		$elaborateUser = App\User::find($request->idElaborate);
		$modelTable = 
		[
			["Folio:", $request->folio],
			["Título y fecha:", htmlentities($request->refunds->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->refunds->first()->datetitle)->format('d-m-Y')],
			["Solicitante:", $request->requestUser()->exists() ? $request->requestUser->fullname() : ""],
			["Elaborado por:", $request->requestUser()->exists() ? $request->elaborateUser->fullname() : ""],
			["Empresa:", $request->requestUser()->exists() ? $request->requestEnterprise->name : ""],
			["Dirección:", $request->requestUser()->exists() ? $request->requestDirection->name : ""],
			["Departamento:", $request->requestUser()->exists() ? $request->requestDepartment->name : ""],
			["Proyecto:", $request->requestUser()->exists() ? $request->requestProject->proyectName : ""]
		];	
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de la Solicitud", "classEx" => "mb-6"]) @endcomponent
	@component("components.labels.title-divisor") DATOS DEL SOLICITANTE @endcomponent
	@component("components.tables.table-request-detail.container",["variant" => "simple"])
		@php
			$modelTable						= [];
			$modelTable["Forma de pago"]	= $request->refunds->first()->paymentMethod->method;
			$modelTable["Referencia"]		= $request->refunds->first()->reference != null ? htmlentities($request->refunds->first()->reference) : '---';
			$modelTable["Tipo de moneda"]	= $request->refunds->first()->currency;
			$modelTable["Importe"]			= "$ ".number_format($request->refunds->first()->total,2);
		
			foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$request->refunds->first()->idUsers)->get() as $bank)
			{
				if($request->refunds->first()->idEmployee == $bank->idEmployee)
				{
					$modelTable["Banco"]				= $bank->description;
					$modelTable["Alias"]				= $bank->alias!=null ? $bank->alias : '---';
					$modelTable["Número de tarjeta"]	= $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$modelTable["CLABE"]				= $bank->clabe!=null ? $bank->clabe : '---';
					$modelTable["Número de cuenta"]		= $bank->account!=null ? $bank->account : '---';
				}
			}
			
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
	@endcomponent
	@component("components.labels.title-divisor") RELACIÓN DE DOCUMENTOS @endcomponent
	@php
		$modelHead = 
		[
			[
				["value" => "#"],
				["value" => "Concepto"],
				["value" => "Clasificación del gasto"],
				["value" => "Fiscal"],
				["value" => "Subtotal"],
				["value" => "IVA"],
				["value" => "Impuesto Adicional"],
				["value" => "Retenciones"],
				["value" => "Importe"],
				["value" => "Documento(s)"]
			]
		];
		$subtotalFinal = $ivaFinal = $totalFinal = 0;
		$countConcept = 1;
		$modelBody = [];
		foreach($request->refunds->first()->refundDetail as $refundDetail)
		{		
			$subtotalFinal	+= $refundDetail->amount;
			$ivaFinal		+= $refundDetail->tax;
			$totalFinal		+= $refundDetail->sAmount;
			$body = 
			[
				"classEx" => "tr",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    => "components.labels.label",
							"label" => $countConcept,
						]
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind"    	=> "components.labels.label",
							"label" 	=> htmlentities($refundDetail->concept),
						],
					],
				],
			];
			
			if(isset($refundDetail->account))
			{
				$accountDescription = $refundDetail->account->account.' - '.$refundDetail->account->description.' ('.$refundDetail->account->content.')';
			}
			else
			{
				$accountDescription = "";
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $accountDescription,
					],
				],
			];
			if($refundDetail->taxPayment==1) 
			{
				$taxPayment = "si";
			}
			else
			{
				$taxPayment = "no";
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"  => "components.labels.label",
						"label" => $taxPayment,
					],
				],
			];
			$body[] = 
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->amount,2),
					],
				],
			];
			$body[] = 
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind"    => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->tax,2),
					],
				],
			];
			$taxes2 = 0;
			foreach($refundDetail->taxes as $tax)
			{
				$taxes2 += $tax->amount;
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($taxes2,2),
					],
				],
			];

			$retentions2 = 0;
			foreach($refundDetail->retentions as $ret)
			{
				$retentions2 +=$ret->amount;
			}
			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($retentions2,2),
					],
				],
			];

			$body[] =
			[
				"classEx" => "td",
				"content" =>
				[
					[
						"kind" => "components.labels.label",
						"label" => "$ ".number_format($refundDetail->sAmount,2),
					],
				],
			];
			if(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get()->count()>0)
			{
				$contentBodyDocs = [];
				foreach(App\RefundDocuments::where('idRefundDetail',$refundDetail->idRefundDetail)->get() as $doc)
				{
					if ($doc->datepath != "") 
					{
						$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->datepath)->format('d-m-Y');
					}
					else
					{
						$date = Carbon\Carbon::createFromFormat('Y-m-d',$doc->date)->format('d-m-Y');
					}

					if($doc->name != '')
					{
						$docName = $doc->name;
					}
					else
					{
						$docName = "Otro";
					}

					$contentBodyDocs[] = 
					[
						"kind"  => "components.labels.label",
						"label" => $date,
					];
					$contentBodyDocs[] = 
					[
						"kind"  => "components.labels.label",
						"label" => $docName,
					];
					$contentBodyDocs[] = 
					[
						"kind" => "components.buttons.button", 									
						"buttonElement" => "a",
						"attributeEx" => "target=\"_blank\" title=\"".$doc->path."\" href=\"".asset("docs/refounds/".$doc->path)."\"",
						"variant" => "dark-red",
						"label" => "PDF",
					];
				}
			}
			else
			{
				$contentBodyDocs[] =
				[
					"kind"  => "components.labels.label",
					"label" => "---",
				];
			}
			$body[] = [
				"classEx" => "td",
				"content" => $contentBodyDocs
			];
			$modelBody [] = $body;
			$countConcept++;
		}
	@endphp
	@component("components.tables.table",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"classEx" 	=> "my-6",
		"themeBody" => "striped"
	])
		@slot("classExBody")
			request-validate
		@endslot
		@slot("attributeExBody")
			id="body"
		@endslot
	@endcomponent
	@php
		$modelTable = [];
		$taxes 	= 0;
		$retentionConcept2=0;

		if($totalFinal!=0)
		{
			$valueSubtotal = "value = \"".number_format($subtotalFinal,2)."\"";
			$labelSubtotal = "$ ".number_format($subtotalFinal,2);
 			$valueIVA 	   = "value = \"".number_format($ivaFinal,2)."\"";
			$labelIVA 	   = "$ ".number_format($ivaFinal,2);
			$valueTotal    = "value = \"".number_format($totalFinal,2)."\"";
			$labelTotal    = "$ ".number_format($totalFinal,2);
		}
		else 
		{
			$valueSubtotal = "";
			$labelSubtotal = "$ 0.00";
 			$valueIVA 	   = "";
			$labelIVA 	   = "$ 0.00";
			$valueTotal    = "";
			$labelTotal    = "$ 0.00";
		}
		if(isset($request))
		{
			foreach($request->refunds->first()->refundDetail as $detail)
			{
				foreach($detail->taxes as $tax)
				{
					$taxes += $tax->amount;
				}
				foreach($detail->retentions as $ret)
				{
					$retentionConcept2 = $retentionConcept2 + $ret->amount;
				}
			}
		}
		
		$modelTable = 
		[	
			["label" => "Subtotal: ", "inputsEx" => [["kind" =>	"components.labels.label",	"label" => $labelSubtotal, "classEx" => "my-2 label-subtotal"],["kind" =>	"components.inputs.input-text",	"classEx" => "subtotal", "attributeEx" => "type=\"hidden\" id=\"subtotal\" readonly name=\"subtotal\" ".$valueSubtotal]]],
			["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelIVA, "classEx" => "my-2 label-IVA"],["kind" =>	"components.inputs.input-text",	"classEx" => "ivaTotal", "attributeEx" => "type=\"hidden\" id=\"iva\" name=\"iva\" ".$valueIVA]]],
			["label" => "Impuesto Adicional: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($taxes,2), "classEx" => "my-2 label-taxes"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountAA\" value=\"".number_format($taxes,2)."\""]]],
			["label" => "Retenciones: ", "inputsEx" => [["kind"	=> "components.labels.label",	"label"	=> "$ ".number_format($retentionConcept2,2), "classEx" => "my-2 label-retentions"],["kind" => "components.inputs.input-text", "attributeEx" => "type=\"hidden\" name=\"amountRetentions\" value=\"".number_format($retentionConcept2,2)."\""]]],
			["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label"	=>	$labelTotal, "classEx" => "my-2 label-total"],["kind" =>	"components.inputs.input-text",	"classEx" => "total", "attributeEx" => "type=\"hidden\" id=\"total\" name=\"total\" ".$valueTotal]]],
		];
	@endphp
	@component("components.templates.outputs.form-details", ["modelTable" => $modelTable, "classEx" => "mb-6"]) @endcomponent
	@component("components.labels.title-divisor") DATOS DE REVISIÓN @endcomponent
	@component("components.tables.table-request-detail.container",["variant" => "simple"])
		@php
			$reviewAccount = App\Account::find($request->accountR);
			if(isset($reviewAccount->account))
			{
				$accountDescription = $reviewAccount->account." - ".$reviewAccount->description." (".$reviewAccount->content.")";
			}
			else 
			{
				$accountDescription = "Varias";
			}
			if($request->checkComment == "")
			{
				$checkComment = "Sin comentarios";
			}
			else
			{
				$checkComment = htmlentities($request->checkComment);
			}
			$modelTable = 
			[
				"Revisó" 					=> $request->reviewedUser->fullname(),
				"Nombre de la Empresa" 		=> App\Enterprise::find($request->idEnterpriseR)->name,
				"Nombre de la Dirección" 	=> $request->reviewedDirection->name,
				"Nombre del Departamento" 	=> App\Department::find($request->idDepartamentR)->name,
				"Clasificación del gasto"	=> $accountDescription,
				"Nombre del Proyecto" 		=> $request->reviewedProject->proyectName,
				"Comentarios" 				=> $checkComment,
			];
		@endphp
		@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) @endcomponent
	@endcomponent
	@component("components.labels.title-divisor") ETIQUETAS Y RECLASIFICACIÓN ASIGNADA @endcomponent
	@php
		$modelHead = 
		[
			[
				["value" => "Concepto"],
				["value" => "Clasificación de gasto"],
				["value" => "Etiquetas"]
			]
		];
		$modelBody = [];
		foreach(App\RefundDetail::where('idRefund',$request->refunds->first()->idRefund)->get() as $refundDetail)
		{
			$labels = [];
			foreach($refundDetail->labels as $label)
			{
				$labels [] = $label->label->description;
			}
			
			$body = [
				"classEx" => "tr",
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => htmlentities($refundDetail->concept),
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						[
							"kind" => "components.labels.label",
							"label" => $refundDetail->accountR->account." - ".$refundDetail->accountR->description." (".$refundDetail->accountR->content.")",
						],
					],
				],
				[
					"classEx" => "td",
					"content" =>
					[
						"kind" => "components.labels.label",
						"label" => count($labels) > 0 ? implode(", ",$labels) : 'Sin etiquetas',
					],
				],
			];
			$modelBody [] = $body;
		}	
	@endphp
	@component("components.tables.table",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"classEx" => "my-6",
		"themeBody" => "striped"
	])
		@slot('attributeEx')
			id="table"
		@endslot
		@slot('classExBody')
			request-validate
		@endslot
		@slot('attributeExBody')
			id="tbody-conceptsNew"
		@endslot
	@endcomponent
	<div>
	</div>
	@component("components.forms.form", ["attributeEx" => "action=\"".route('refund.authorization.update',$request->folio)."\" method=\"POST\" id=\"container-alta\"", "methodEx" => "PUT"])
		@component('components.containers.container-approval')
			@slot('attributeExLabel')
				id="label-inline"
			@endslot
			@slot('attributeExButton')
				name="status"
				value="5"
				id="aprobar"
			@endslot
			@slot('attributeExButtonTwo')
				name="status"
				value="7"
				id="rechazar"
			@endslot
		@endcomponent	
		<div id="aceptar" class="hidden">
			@component("components.labels.label")
				@slot('classEx')
					text-center
				@endslot
				Comentarios (Opcional)
			@endcomponent
			@component("components.inputs.text-area")
				@slot("classEx")
					text-area
				@endslot
				@slot("attributeEx")
					cols="90"
					rows="10"
					name="authorizeCommentA"
				@endslot
			@endcomponent
		</div>
		<div class="w-full grid grid-cols-1 md:flex gap-2 justify-items-center md:justify-center items-center my-6">
			@component("components.buttons.button", ["variant"=>"primary"])
				@slot("classEx")
					w-48
					md:w-auto
				@endslot
				@slot("attributeEx")
					type="submit"
					name="enviar"
					value="ENVIAR SOLICITUD"
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
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
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
						swal('Cargando',{
							icon:'{{ asset(getenv('LOADING_IMG')) }}',
							button: false,
							closeOnClickOutside: false,
							closeOnEsc         : false
						});
						return true;
					}
					else
					{
						swal('', 'Por favor seleccione al menos un estado.', 'error');
						return false;
					}
				}
			});
			$(document).on('change','input[name="status"]',function()
			{
				$("#aceptar").slideDown("slow");
			});
		});
	</script>
@endsection
