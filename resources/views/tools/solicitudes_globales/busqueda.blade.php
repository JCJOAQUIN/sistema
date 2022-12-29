@extends('layouts.child_module')  
@section('data')	
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate, "enterprise_id" => $enterpriseid, "enterprise_option_id" => $option_id];
	@endphp
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "values" => $values]) 
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Tipo de solicitud: @endcomponent
				@php
					$options = collect();
					foreach (App\RequestKind::whereNotIn('idrequestkind',[21])->orderName()->get() as $k)
					{
						$options = $options->concat([["value" => $k->idrequestkind, "selected" => ((isset($kind) && $kind == $k->idrequestkind) ? "selected" : ""), "description" => $k->kind]]);
					}
					$attributeEx = "title=\"Tipo de Solicitud\" name=\"kind\" multiple=\"multiple\"";
					$classEx = "js-kind";
				@endphp
				@component("components.inputs.select",["attributeEx" => $attributeEx, "classEx" => $classEx, "options" => $options])@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Estado de solicitud: @endcomponent
				@php
					$options = collect();
					foreach (App\StatusRequest::where('idrequestStatus',"!=",1)->orderBy('description','asc')->get() as $s)
					{
						$options = $options->concat([["value" => $s->idrequestStatus, "selected" => ((isset($status) && $s->idrequestStatus == $status) ? "selected" : ""), "description" => $s->description]]);
					}
					$attributeEx = "title=\"Estado de solicitud\" name=\"status\" multiple=\"multiple\"";
					$classEx = "js-status";
				@endphp
				@component("components.inputs.select",["attributeEx" => $attributeEx, "classEx" => $classEx, "options" => $options])@endcomponent
			</div>
		@endslot
	@endcomponent
	@if(count($requests) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Tipo"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Empresa"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"],
				]
			];			
			foreach($requests as $request)
			{
				switch ($request->kind) 
				{
					case 1:
							$tittleRequest = $request->purchases()->exists() && $request->purchases->first()->title != null ? $request->purchases->first()->title : 'No hay';
						break;

					case 2:
							$tittleRequest = $request->nominas->first()->title != null ? $request->nominas->first()->title : 'No hay';
						break;

					case 3:
							$tittleRequest = $request->expenses->first()->title != null ? $request->expenses->first()->title : 'No hay';
						break;
					
					case 4:
							$tittleRequest = $request->staff->first()->title != null ? $request->staff->first()->title : 'No hay';
						break;

					case 5:
							$tittleRequest = $request->loan->first()->title != null ? $request->loan->first()->title : 'No hay';
						break;
					
					case 6:
							$tittleRequest = $request->computer()->exists() && $request->computer->first()->title != null ? $request->computer->first()->title : "No hay";
						break;
					
					case 7:
							$tittleRequest = $request->stationery->first()->title != null ? $request->stationery->first()->title : 'No hay';
						break;
					
					case 8:
							$tittleRequest = $request->resource->first()->title != null ? $request->resource->first()->title : 'No hay';
						break;
					
					case 9:
							$tittleRequest = $request->refunds->first()->title != null ? $request->refunds->first()->title : 'No hay';
						break;
						
					case 10:
							$tittleRequest = $request->income()->exists() ? isset($request->income->first()->title) ? $request->income->first()->title : 'No hay' : 'No hay';
						break;
					
					case 11:
							$tittleRequest = $request->adjustment->first()->title != null ? $request->adjustment->first()->title : 'No hay';
						break;

					case 12:
							$tittleRequest = $request->loanEnterprise->first()->title != null ? $request->loanEnterprise->first()->title : 'No hay';
						break;

					case 13:
							$tittleRequest = $request->purchaseEnterprise->first()->title != null ? $request->purchaseEnterprise->first()->title : 'No hay';
						break;

					case 14:
							$tittleRequest = $request->groups->first()->title != null ? $request->groups->first()->title : 'No hay';
						break;

					case 15:
							$tittleRequest = $request->movementsEnterprise->first()->title != null ? $request->movementsEnterprise->first()->title : 'No hay';
						break;

					case 16:
							$tittleRequest = $request->nominasReal->first()->title != null ? $request->nominasReal->first()->title : 'No hay';
						break;

					case 17:
							$tittleRequest = $request->purchaseRecord()->exists() && $request->purchaseRecord->title != null ? $request->purchaseRecord->title : 'No hay';
						break;

					case 18:
							$tittleRequest = $request->finance()->exists() && $request->finance->title != null ? $request->finance->title : 'No hay';
						break;

					case 19:
							$tittleRequest = $request->requisition()->exists() && $request->requisition->title != null ? $request->requisition->title : 'No hay';
						break;

					case 20:
							$tittleRequest = $request->otherIncome()->exists() && $request->otherIncome->title != null ? $request->otherIncome->title : 'No hay';
						break;

					case 21:
							$tittleRequest = "No hay";
						break;

					case 22:
							$tittleRequest = $request->workOrder()->exists() && $request->workOrder->title != null ? $request->workOrder->title : 'No hay';
						break;
					
					case 23:
							$tittleRequest = $request->flightsLodging()->exists() && $request->flightsLodging->title != null ? $request->flightsLodging->title : 'No hay';
						break;

					default:
							$tittleRequest = "No hay";
						break;
				}
				$enterpriseName = "";
				if (isset($request->requestEnterprise->name))
				{
					$enterpriseName = $request->requestEnterprise->name;
				}
				elseif (isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$enterpriseName = $request->requestEnterprise->name;
				}					
				elseif ($request->kind == 11 || $request->kind == 12 || $request->kind == 13 || $request->kind == 14 || $request->kind == 15)
				{
					$enterpriseName = "Varias";
				}
				else 
				{
					$enterpriseName = "No hay";
				}
				$modelTable[] = 
				[
					[
						"content" =>
						[
							"label" => $request->folio,
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($tittleRequest),
						]
					],
					[
						"content" =>
						[
							"label" => $request->requestkind->kind,
						],
					],
					[
						"content" =>
						[
							"label" => ($request->requestUser()->exists() ? $request->requestUser->fullName() : 'No hay'),
						],
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description,
						],
					],
					[
						"content" =>
						[
							"label" => $enterpriseName,
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
							[
								"kind" 			=> "components.buttons.button",								
								"buttonElement" => "a",
								"attributeEx" 	=> "alt=\"Ver solicitud\" title=\"Ver solicitud\" href=\"".route("global-requests.follow.show",$request->folio)."\"",
								"variant" 		=> "secondary",
								"label" 		=> "<span class='icon-search'></span>",
							],
						],
					]
				];				
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelTable,
			"themeBody" => "striped"
		])
		@endcomponent	
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection
@section('scripts')
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-kind", 
						"placeholder"            => "Seleccione el tipo",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione el estado",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		});
	</script>
@endsection
