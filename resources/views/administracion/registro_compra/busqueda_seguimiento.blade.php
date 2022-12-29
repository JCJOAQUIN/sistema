@extends('layouts.child_module')

@section('data')
	@Title(["classEx" => "font-semibold"]) BUSCAR SOLICITUDES @endTitle
	@SearchForm(
	[
		"form" => ["action	=\"".route('purchase-record.search')."\" method	=\"GET\" id		=\"formsearch\""], 
		"values" => 
		[
			"folio"                => $folio, 
			"name"                 => $name, 
			"minDate"              => $mindate, 
			"maxDate"              => $maxdate, 
			"enterprise_option_id" => $option_id, 
			"enterprise_id"        => $enterpriseid
		]
	])
		@slot("contentEx")
			<div class="col-span-2">
				@Label(["label" => "Proveedor:"])@endLabel
				@InputText(["attributeEx" => "type=\"text\" name=\"provider\" id=\"input-search\" placeholder=\"Ingrese un proveedor\" value=\"".(isset($provider) ? $provider : '')."\""]) @endInputText
			</div>
			<div class="col-span-2">
				@Label(["label" => "Clasificación del Gasto:"])@endLabel
				@php
					$options = collect();
					foreach(App\Account::orderNumber()->where('idEnterprise',$enterpriseid)->where('selectable',1)->get() as $a)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $a->idAccAcc, 
								"description"	=> $a->account." - ".$a->description." (".$a->content.")", 
								"selected"		=> ((isset($account) && $account == $a->idAccAcc) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["classEx" => "js-account removeselect", "options" => $options, "attributeEx" => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\""])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Estado:"])@endLabel
				@php
					$options = collect();
					foreach(App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12,18])->orderBy('description','asc')->get() as $s)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $s->idrequestStatus, 
								"description"	=> $s->description, 
								"selected"		=> ((isset($status) && in_array($s->idrequestStatus, $status)) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["classEx" => "js-status removeselect", "options" => $options, "attributeEx" => "title=\"Estado de Solicitud\" multiple=\"multiple\" name=\"status[]\""])@endSelect
			</div>
			<div class="col-span-2">
				@Label(["label" => "Estado de Factura:"])@endLabel
				@php
					$options = collect();
					foreach(["Pendiente", "Entregado", "No Aplica", "Otro"] as $item)
					{
						$options = $options->concat(
						[
							[
								"value"			=> $item, 
								"description"	=> $item, 
								"selected"		=> ((isset($documents) && $documents == $item) ? "selected" : "")
							]
						]);
					}
				@endphp
				@Select(["classEx" => "js-status-bill removeselect", "options" => $options, "attributeEx" => "title=\"Estado de Factura\" multiple=\"multiple\" name=\"documents\""])@endSelect
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot("export")	
				<div class="float-right">
					@Button(["classEx" => "export", "attributeEx" => "type=\"submit\"  formaction=\"".route('purchase-record.export.follow')."\"", "variant" => "success", "label" => "Exportar a excel <span class=\"icon-file-excel\"></span>"])@endButton		
				</div>
			@endslot
		@endif
	@endSearchForm
	@if(count($requests) > 0)
		@php
			foreach($requests as $request)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $request->folio
						]
					],
					[
						"content" =>
						[
							"label" => $request->purchaseRecord()->exists() && $request->purchaseRecord->title != null ? htmlentities($request->purchaseRecord->title) : 'No hay'
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : 'No hay'
						]
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description
						]
					],
					[
						"content" =>
						[
							"label" => 
							(
								isset($request->reviewedEnterprise->name) ? 
									$request->reviewedEnterprise->name 
								:
									(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name)) ? 
										$request->requestEnterprise->name 
									: 
										"No hay"
							)
						]
					],
					[
						"content" =>
						[
							"label" => 
							(
								isset($request->accountsReview->account) ? 
									$request->accountsReview->account.' '.$request->accountsReview->description
								:  
									(isset($request->accountsReview->account) == false && isset($request->accounts->account)) ? 
										$request->accounts->account.' '.$request->accounts->description 
									: 
										"No hay"
							)
						]
					],
					[
						"content" => 
						[
							"label" => (new DateTime($request->fDate))->format('d-m-Y')
						]
					],
					[
						"content" =>
						in_array($request->status, [5,6,7,10,11,13])
						?
							[
								["kind" => "components.buttons.button", "attributeEx" => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('purchase-record.create.new',$request->folio)."\"", "label" => "<span class=\"icon-plus\"></span>", "variant" => "warning", "buttonElement" => "a", "classEx" => "load-actioner"],
								["kind" => "components.buttons.button", "attributeEx" => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('purchase-record.follow.edit',$request->folio)."\"", "label" => "<span class=\"icon-search\"></span>", "variant" => "secondary", "buttonElement" => "a", "classEx" => "load-actioner"]
							]
						:
							(
								in_array($request->status, [3,4,5,12])
								?
									[
										["kind" => "components.buttons.button", "attributeEx" => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('purchase-record.follow.edit',$request->folio)."\"", "label" => "<span class=\"icon-search\"></span>", "variant" => "secondary", "buttonElement" => "a", "classEx" => "load-actioner"]
									]
								:
									[
										["kind" => "components.buttons.button", "attributeEx" => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('purchase-record.follow.edit',$request->folio)."\"", "label" => "<span class=\"icon-pencil\"></span>", "variant" => "success", "buttonElement" => "a", "classEx" => "load-actioner"]
									]
							)
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@Table(
		[
			"modelHead" => 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Empresa"],
					["value" => "Clasificación del Gasto"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			],
			"modelBody" => $modelBody
		])
		@endTable
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found")@endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "yy-mm-dd" });
			});
			@ScriptSelect(
			[
				"selects" => 
				[
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione una empresa",
						"language" 				 => 'es',
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione un estado",
						"language" 				 => 'es',
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status-bill", 
						"placeholder"            => "Seleccione un estado de factura",
						"language" 				 => 'es',
						"maximumSelectionLength" => "1"
					],	
				]
			])
			@endScriptSelect
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model':10});
		});
	</script>
@endsection
