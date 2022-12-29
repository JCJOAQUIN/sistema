@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Proveedor: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name        = "provider"
						id          = "input-search"
						placeholder = "Ingrese un proveedor"
						value       = "{{ isset($provider) ? $provider : "" }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Cuenta: @endcomponent
				@php
					$options = collect();
					if(isset($enterpriseid) && isset($account))
					{
						$accountSelected = App\Account::find($account);
						$options = $options->concat([["value" => $account, "selected" => "selected", "description" => $accountSelected->account." - ".$accountSelected->description." (".$accountSelected->content.")"]]);
					}
					$attributeEx = "title=\"Cuenta\" multiple=\"multiple\" name=\"account\"";
					$classEx = "js-account removeselect";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot("export")
				<div class="text-right">
					<label>
						@component("components.buttons.button",["variant" => "success"])
							@slot("attributeEx") 
								type="submit"
								formaction="{{ route("purchase-record.export.review") }}"
							@endslot
							@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
							@slot("classEx") export @endslot
						@endcomponent
					</label>
				</div>
			@endslot
		@endif
	@endcomponent
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
							"label" => $request->elaborateUser()->exists() ? $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name : 'No hay'
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
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->fDate)->format('d-m-Y H:i'),
						]
					],
					[
						"content" =>
						[
							"label" => (isset($request->requestEnterprise->name) ? $request->requestEnterprise->name : "No hay"),
						]
					],
					[
						"content" =>
						[
							"label" => (isset($request->accounts->account) ? $request->accounts->account.' '.$request->accounts->description : "No hay"),
						],
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button", 
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".(route('purchase-record.review.edit',$request->folio))."\"", 
								"label"         => "<span class=\"icon-pencil\"></span>", 
								"classEx"       => "load-actioner",
								"variant"       => "success",
								"buttonElement" => "a"
							],
						],
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
					["value" => "Elaborado por"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
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
			]
		])
		@endScriptSelect
		generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model':10});
	}); 
</script> 
@endsection

