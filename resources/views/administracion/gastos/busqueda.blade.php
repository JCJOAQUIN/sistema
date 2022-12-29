@extends('layouts.child_module')

@section('data')
	@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ['enterprise_option_id' => $option_id, 'enterprise_id' => $enterpriseid, 'folio' => $folio, 'name' => $name, 'minDate' => $mindate, 'maxDate' => $maxdate];
	@endphp
	@component('components.forms.searchForm', [ "attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label') Folio de Asignación de Recurso: @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type="text" 
						name="resource_id"
						id="input-search" 
						placeholder="Ingrese el folio" 
						value="{{ isset($resource_id) ? $resource_id : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label') Estado: @endcomponent
				@php
					$optionStatus = [];
					foreach (App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->orderBy('description','asc')->get() as $s)
					{
						if (isset($status) && $status == $s->idrequestStatus)
						{
							$optionStatus[] = ["value" => $s->idrequestStatus, "description" => $s->description, "selected" => "selected"];
						}
						else 
						{
							$optionStatus[] = ["value" => $s->idrequestStatus, "description" => $s->description ];
						} 
					}
				@endphp
				@component('components.inputs.select', ["options" => $optionStatus])
					@slot('attributeEx')
						title="Estado de Solicitud" 
						name="status"
						multiple="multiple"
					@endslot		
					@slot('classEx')
						js-status
					@endslot				
				@endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot('export')
				@component('components.buttons.button',['variant' => 'success'])
					@slot('classEx')
						export
					@endslot
					@slot('attributeEx')
						type="submit"
						formaction="{{ route('expenses.export.follow') }}"
					@endslot		
					@slot('slot')
						<span>Exportar a Excel</span> <span class='icon-file-excel'> </span>
					@endslot			
				@endcomponent
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$body 		= [];
			$modelBody 	= [];
			$modelHead 	= [
				[
					["value" => "Folio"],
					["value" => "Solicitante"],
					["value" => "Título"],
					["value" => "Elaborado por"],
					["value" => "Empresa"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
					["value" => "Acciones"]
				]
			];

			foreach($requests as $request)
			{ 
				$varIdRequest	= '';
				if($request->idRequest == "")
				{
					$varIdRequest = "No hay solicitante";
				}
				else 
				{
					foreach(App\User::where('id',$request->idRequest)->get() as $user)
					{
						$varIdRequest = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					}
				}
				$varElaborater = '';
				foreach(App\User::where('id',$request->idElaborate)->get() as $user)
				{
					$varElaborater =  $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				}
				$varReviewd = '';
				if (isset($request->reviewedEnterprise->name))
				{
					$varReviewd = $request->reviewedEnterprise->name;
				}
				else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$varReviewd = $request->requestEnterprise->name;
				}
				else 
				{
					$varReviewd = "No hay";
				}
				$varStatus = '';
				if(isset($request->statusrequest->description))
				{
					$varStatus = $request->statusrequest->description;
				}
				else
				{
					$varStatus = '';
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
							"label" => $varIdRequest,
						]
					],
					[
						"content" =>
						[
							"label" => $request->expenses->first()->title != null ? htmlentities($request->expenses->first()->title) : 'No hay'
						]
					],
					[
						"content" => 
						[
							"label" => $varElaborater
						]
					],
					[
						"content" =>
						[
							"label" => $varReviewd
						]
					],
					[
						"content" => 
						[
							"label" => $varStatus
						]
					],
					[
						"content" => 
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i')
						]
					]
				];
				if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 10 || $request->status == 11 || $request->status == 13) 
				{
					array_push($body,
					[ 
						"content" =>
						[ 
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "warning",
								"label"			=> "<span class=\"icon-plus\"></span>",
								"buttonElement"	=> "a",
								"attributeEx"	=> "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route('expenses.create.new',$request->folio)."\""
							],
							[
								"kind" 			=> "components.buttons.button",
								"variant"		=> "secondary",
								"label"			=> "<span class=\"icon-search\"></span>",
								"buttonElement"	=> "a",
								"attributeEx"	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('expenses.follow.edit',$request->folio)."\""
							]
						]
					]); 
				}
				else if($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 12)
				{	
					array_push($body,
					[
						"content" => 
						[
							"kind"          => "components.buttons.button",
							"variant"       => "secondary",
							"label"         => "<span class=\"icon-search\"></span>",
							"classEx"       => "load-actioner",
							"buttonElement" => "a",
							"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route('expenses.follow.edit',$request->folio)."\""
						]
					]);
				}
				else 
				{
					array_push($body,
					[
						"content" => 
						[
							"kind"          => "components.buttons.button",
							"variant"       => "success",
							"label"         => "<span class=\"icon-pencil\"></span>",
							"classEx"       => "load-actioner",
							"buttonElement" => "a",
							"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('expenses.follow.edit',$request->folio)."\""
						]
					]);
				}
				$modelBody[] = $body;
			}
		@endphp
		@component('components.tables.table', [
				"modelBody" => $modelBody,
				"modelHead" => $modelHead
			])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
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
				$selects = collect ([
					[
						"identificator"          => ".js-enterprise",
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status",
						"placeholder"            => "Seleccione un estado",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects", ["selects" => $selects]) @endcomponent
		});
	</script>
@endsection
