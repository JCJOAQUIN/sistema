@extends('layouts.child_module')
  
@section('data')
	<div id="container-cambio" class="div-search">
		@component('components.labels.title-divisor') Buscar solicitudes @endcomponent
		@php
			$values = 
			[
				'enterprise_option_id' => $option_id, 
				'enterprise_id'        => $enterpriseid, 
				'folio'                => $folio, 
				'name'                 => $name, 
				'minDate'              => $mindate, 
				'maxDate'              => $maxdate,
			];
		@endphp  
		@component('components.forms.searchForm', ["values" => $values])
			@slot('contentEx')
				<div class="col-span-2">
					@component('components.labels.label')Proveedor:@endcomponent
					@component('components.inputs.input-text')
						@slot('attributeEx')
							type        = "text" 
							name        = "provider" 
							id          = "input-search" 
							placeholder = "Ingrese un proveedor" 
							value       = "{{ isset($provider) ? $provider : '' }}"
						@endslot
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Cuenta:@endcomponent
					@php
						$optionsAccount = collect();
						if(isset($account))
						{
							foreach($account as $a)
							{
								$accountSelected = App\Account::find($a);
								$description    = $accountSelected->account."-".$accountSelected->description."(".$accountSelected->content.")";
								$optionsAccount = $optionsAccount->concat([['value'=>$accountSelected->idAccAcc, 'description'=>$description, 'selected' => 'selected']]);
							}
						}
					@endphp
					@component('components.inputs.select', 
						[
							'attributeEx' => "title=\"Cuenta\" multiple=\"multiple\" name=\"account[]\"", 
							'classEx'     => "js-accounts removeselect", 
							"options"     => $optionsAccount
						]
					)
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Estado:@endcomponent
					@php
						$options = collect();
						foreach(App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])
						->orderBy('description','asc')
						->get() as $s)
						{
							if(isset($status) && in_array($s->idrequestStatus, $status))
							{
								$options = $options->concat([['value'=>$s->idrequestStatus, 'selected' => 'selected', 'description'=>$s->description]]);
							}
							else
							{
								$options = $options->concat([['value'=>$s->idrequestStatus, 'description'=>$s->description]]);
							}
						}
					@endphp
					@component('components.inputs.select', 
						[
							'attributeEx' => "title=\"Estado de Solicitud\" multiple=\"multiple\" name=\"status[]\"", 
							'classEx'     => "js-status", 
							"options"     => $options
						]
					)
					@endcomponent
				</div>
				<div class="col-span-2">
					@component('components.labels.label')Estado de la factura:@endcomponent
					@php
						$options = collect([
							["value" => "Pendiente", "description" => "Pendiente", "selected" => ((isset($documents) && $documents == "Pendiente") ? "selected" : "")],
							["value" => "Entregado", "description" => "Entregado", "selected" => ((isset($documents) && $documents == "Entregado") ? "selected" : "")],
							["value" => "No aplica", "description" => "No aplica", "selected" => ((isset($documents) && $documents == "No aplica") ? "selected" : "")],
							["value" => "Otro", "description" => "Otro", "selected" => ((isset($documents) && $documents == "Otro") ? "selected" : "")]
						]);
					@endphp
					@component('components.inputs.select', [
							"attributeEx" => "title=\"Estado de factura\" multiple=\"multiple\" name=\"documents\"", 
							"classEx"     => "js-status-bill", 
							"options"     => $options
						]
					)
					@endcomponent
				</div>
			@endslot
			@if(count($requests) > 0)
				@slot('export')
					<div class="flex flex-row justify-end">
						@component('components.labels.label')
							@component('components.buttons.button',["variant" => "success"])
								@slot('attributeEx')
									type       = "submit "
									formaction = "{{ route('purchase.export.follow') }}"
								@endslot
								@slot('label')
									<span>Exportar a Excel</span> <span class="icon-file-excel"></span>
								@endslot
							@endcomponent
						@endcomponent
					</div>
				@endslot
			@endif
		@endcomponent
	</div>
	@if(count($requests) > 0)
		@php
			$body 			= [];
			$modelBody		= [];
			$modelHead = 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Estado"],
					["value" => "Empresa"],
					["value" => "Clasificación del gasto"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];
			foreach($requests as $request)
			{
				if(isset($request->reviewedEnterprise->name))
				{
					$reviewedEnterprise = $request->reviewedEnterprise->name;
				}
				else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$reviewedEnterprise = $request->requestEnterprise->name;
				}
				else{
					$reviewedEnterprise = "No hay";
				}

				if(isset($request->accountsReview->account))
				{
					$accountsReview = $request->accountsReview->account.' '.$request->accountsReview->description;
				}
				else if(isset($request->accountsReview->account) == false && isset($request->accounts->account))
				{
					$accountsReview = $request->accounts->account.' '.$request->accounts->description;
				}
				else{
					$accountsReview = "No hay";
				}
				$body = 
				[
					[
						"content" => 
						[
							"label" => $request->new_folio != null ? $request->new_folio : $request->folio
						]
					],
					[ 
						"content" => 
						[
							"label" => $request->purchases()->exists() && $request->purchases->first()->title != null ? htmlentities($request->purchases->first()->title) : 'No hay'
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
							"label" => isset($request->statusrequest->description) ? $request->statusrequest->description : "---"
						]
					],
					[
						"content" => 
						[
							"label" => $reviewedEnterprise
						]
					],
					[
						"content" =>
						[
							"label" => $accountsReview
						]
					],
					[
						"content" =>
						[
							"label" => $request->fDate->format('d-m-Y')
						]
					]
				];

				if($request->status == 5 || $request->status == 10 || $request->status == 11)
				{
					$btnElements = [
						"content" => 
						[ 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"label"         => "<span class=\"icon-plus\"></span>",
								"variant"       => "warning",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=".route('purchase.create.new',$request->folio)
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('purchase.follow.edit',$request->folio)
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "dark-red",
								"label"         => "<span class=\"icon-pdf\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Descargar orden\" title=\"Descargar orden\" href=".route('purchase.download.document',$request->folio)
							]
						]
					];
					$body[] = $btnElements;
				}
				else if($request->status == 6 || $request->status == 7  || $request->status == 13) 
				{
					$btnElements = [
						"content" => 
						[ 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"label"         => "<span class=\"icon-plus\"></span>",
								"variant"       => "warning",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=".route('purchase.create.new',$request->folio)
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('purchase.follow.edit',$request->folio)
							]
						]
					];
					$body[] = $btnElements;
				}
				else if($request->status == 4 || $request->status == 10 || $request->status == 11  )
				{ 
					$btnElements = 
					[
						"content" => 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('purchase.follow.edit',$request->folio)
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "dark-red",
								"label"         => "<span class=\"icon-pdf\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Descargar orden\" title=\"Descargar orden\" href=".route('purchase.download.document',$request->folio)
							]
						]
					];
					$body[] = $btnElements;
				}
				else if($request->status == 3 || $request->status == 5  || $request->status == 12)
				{
					$btnElements = 
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('purchase.follow.edit',$request->folio)
							]
						]
					];
					$body[] = $btnElements;
				}
				else
				{
					$btnElements = 
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "success",
								"label"         => "<span class=\"icon-pencil\"></span>",
								"classEx"       => "follow-btn load-actioner",
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=".route('purchase.follow.edit',$request->folio)
							]
						]
					];
					$body[] = $btnElements;
				}

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
		{{$requests->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found")
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
			generalSelect({'selector': '.js-users', 'model': 36});
			generalSelect({'selector': '.js-accounts', 'depends':'.js-enterprise', 'model':10, 'maxSelection': -1});
			generalSelect({'selector': '[name="project_id"]', 'model': 24});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-type", 
						"placeholder"            => "Seleccione un tipo", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status",
						"placeholder"            => "Seleccione un estado"
					],
					[
						"identificator"          => ".js-status-bill", 
						"placeholder"            => "Seleccione el estado de factura", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		});
	</script>
@endsection