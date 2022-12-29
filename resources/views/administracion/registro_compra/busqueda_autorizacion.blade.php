@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate, "enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid];
		$hidden = [];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values, "hidden" => $hidden])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label')Cuenta:@endcomponent
				@php
					$optionsAccount	= collect();
					if(isset($account))
					{
						$acc			= App\Account::find($account);
						$optionsAccount	= $optionsAccount->concat([["value"=>$acc->idAccAcc, "selected"=>"selected", "description"=>$acc->fullClasificacionName() ]]);
					}
				@endphp
				@component('components.inputs.select', 
					[
						'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account\"", 
						'classEx'     => "js-account removeselect", 
						"options"     => $optionsAccount
					]
				)
				@endcomponent
			</div>
			
		@endslot
		@if(count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('purchase-record.export.authorization') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$body		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Fecha de revisión"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
					["value" => "Acción"]
				]
			];
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
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->reviewDate)->format('d-m-Y H:i')
						]
					],
					[
						"content" =>
						[
							"label" => isset($request->reviewedEnterprise->name) ? $request->reviewedEnterprise->name : "No hay" 
						]
					],
					[
						"content" =>
						[
							"label" => isset($request->accountsReview->account) ? $request->accountsReview->account.' '.$request->accountsReview->description : "No hay"
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"buttonElement"	=> "a",
							"classEx"		=> "follow-btn load-actioner",
							"attributeEx"	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('purchase-record.authorization.edit',$request->folio)."\"",
							"variant"		=> "success",	
							"label"			=> "<span class=\"icon-pencil\"></span>"
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",
			[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
			])
		@endcomponent
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
			@php
				$selects = collect(
				[
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione una empresa",
						"languaje"				=> "es",
						"maximumSelectionLength"=> "1",
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise', 'model':10});
		});
	</script>
@endsection