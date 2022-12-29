@extends('layouts.child_module')

@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') BUSCAR PRESUPUESTOS @endcomponent
		@php
			$values = 
			[
				'enterprise_option_id' => $option_id, 
				'enterprise_id'        => $enterpriseid, 
				'folio'                => $folio, 
				'name'                 => $name, 
				'minDate'              => $mindate, 
				'maxDate'              => $maxdate
			];
		@endphp
		@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')Cuenta:@endcomponent
					@php
						$options = collect();
						if(isset($account) && isset($enterpriseid) && $account != null)
						{
							foreach(App\Account::where('idEnterprise',$enterpriseid)->where('selectable',1)->get() as $acc)
							{
								$description = $acc->account.' - '.$acc->description." (".$acc->content.")";
								$value = $acc->idAccAcc;
								if($account == $value)
								{
									$options = $options->concat([['value'=>$value, 'selected'=>'selected', 'description'=>$description]]);
								}
								else
								{
									$options = $options->concat([['value'=>$value, 'description'=>$description]]);
								}
							}
						}
						else
						{
							foreach(App\Account::where('idEnterprise',$enterpriseid)->where('selectable',1)->get() as $acc)
							{
								$options = $options->concat([['value'=>$acc->idAccAcc, 'description'=>$acc->account.' - '.$acc->description." (".$acc->content.")"]]);
							}
						}
						$attributeEx = "title=\"Cuenta\" name=\"account\"";
						$classEx     = "js-account removeselect";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Solicitud:@endcomponent
					@php
						$options = collect();
						foreach(App\RequestKind::whereIn('idrequestkind',[1,9])->orderBy('kind','ASC')->get() as $k)
						{
							$description = $k->kind;
							$value       = $k->idrequestkind;
							if(isset($kind) && $kind == $value)
							{
								$options = $options->concat([['value'=>$value, 'selected'=>'selected', 'description'=>$description]]);
							}
							else
							{
								$options = $options->concat([['value'=>$value, 'description'=>$description]]);
							}
						}
						$attributeEx = "name=\"kind\"";
						$classEx     = "js-kind";
					@endphp
					@component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx]) @endcomponent
				</div>
				<div class="col-span-2">
					@component("components.labels.label")
						Estatus del presupuesto
					@endcomponent
					@php
						$options = collect();
						$value = ["0" => "Rechazado", "1" => "Autorizado"];
						foreach($value as $item => $description)
						{
							$options = $options->concat(
								[
									[
										"value"       => $item,
										"description" => $description,
										"selected"    => ((isset($status) && $item == $status) ? "selected" : '')
									]
								]
							);
						}
						$attributeEx = "multiple=\"multiple\" name=\"status\"";
						$classEx = "js-status";
 					@endphp
					 @component('components.inputs.select', ['options' => $options, 'attributeEx' => $attributeEx, 'classEx' => $classEx])@endcomponent
				</div>
			@endslot
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$body      = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "Folio"],
					["value" => "Folio de requisición"],
					["value" => "Tipo de solicitud"],
					["value" => "Título"],
					["value" => "Empresa"],
					["value" => "Solicitante"],
					["value" => "Estado de la solicitud"],
					["value" => "Estado del presupuesto"],
					["value" => "Clasificación del gasto"],
					["value" => "Importe"],
					["value" => "Acción"]
				]
			];
			foreach($requests as $request)
			{
				switch($request->kind)
				{
					case 1:
						$total        = $request->purchases->first()->amount;
						$titleRequest = htmlentities($request->purchases->first()->title);
						break;
					case 9:
						$total        = $request->refunds->first()->total;
						$titleRequest = htmlentities($request->refunds->first()->title);
						break;
				}
				if ($request->reviewedEnterprise()->exists())
				{
					$enterprise = $request->reviewedEnterprise->name;
				}
				else if($request->requestEnterprise()->exists())
				{
					$enterprise = $request->requestEnterprise->name;
				}
				else
				{
					$enterprise = "Sin empresa";
				}
				$userRequest = $request->requestUser()->exists() ? $request->requestUser->fullName() : 'Sin nombre';
				$budgetStatus = $request->budget->status == 1 ? "Autorizado" : "Rechazado";
				if(isset($request->accountsReview->account) && $request->kind == 1)
				{
					$clasificacion = $request->accountsReview->account.' '.$request->accountsReview->description;
				}
				else if(!isset($request->accountsReview->account) && $request->kind == 1)
				{
					$clasificacion = "--";
				}
				else
				{
					$clasificacion = "Varias";
				}
				$body = [
					[
						"content" => 
						[
							"label" => $request->folio
						]
					],
					[
						"content" => 
						[
							"label" => $request->idRequisition != null ? $request->idRequisition : ''
						]
					],
					[
						"content" => 
						[
							"label" => $request->requestkind->kind != null ? $request->requestkind->kind : 'No hay'
						]
					],
					[
						"content" => 
						[
							"label" => $titleRequest
						]
					],
					[
						"content" =>
						[
							"label" => $enterprise
						]
					],
					[
						"content" =>
						[
							"label" => $userRequest
						]
					],
					[
						"content" =>
						[ 
							"label" => $request->statusrequest != null ? $request->statusrequest->description : 'Sin estado'
						]
					],
					[
						"content" =>
						[
							"label" => $budgetStatus
						]
					],
					[
						"content" =>
						[
							"label" => $clasificacion
						]
					],
					[
						"content" =>
						[
							"label" => "$".number_format($total,2)
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('budget.view',$request->folio)."\""
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"label"         => "<span class=\"icon-pencil\"></span>", 
								"variant"       => "success",
								"classEx"       => "load-actioner",
								"attributeEx"   => "alt=\"Editar Presupuesto\" title=\"Editar Presupuesto\" href=\"".route('budget.show',$request->folio)."\""
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
			@slot('classEx')
				text-center
			@endslot
		@endcomponent
		<div class="flex flex-row justify-center">
			{{ $requests->appends([
				'account'      => $account,
				'name'         => $name,
				'folio'        => $folio,
				'kind'         => $kind,
				'mindate'      => $mindate,
				'maxdate'      => $maxdate,
				'enterpriseid' => $enterpriseid,
				'status'       => $status
			])
			->render() }}
		</div>
	@else
		@component("components.labels.not-found")
			@slot("slot")
				Resultado no encontrado
			@endslot
		@endcomponent
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
						"identificator"          => ".js-kind", 
						"placeholder"            => "Seleccione el tipo de solicitud", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-account", 
						"placeholder"            => "Seleccione una cuenta", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione el estatus del presupuesto", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])@endcomponent
			generalSelect({'selector': '.js-account', 'depends': '.js-enterprise','model': 10});
			$('input[name="folio"]').numeric({ negative:false});
			$(function()
			{
				$('.datepicker').datepicker(
				{
					dateFormat : 'yy-mm-dd',
				});
			});
		});
	</script>
@endsection