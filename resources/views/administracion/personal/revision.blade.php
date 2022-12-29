@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') Buscar Solicitudes @endcomponent
	@php
		$values = 
		[
			'folio'                => $folio,
			'name'                 => $name, 
			'enterprise_option_id' => $option_id, 
			'enterprise_id'        => $enterpriseid,
			'minDate'              => $mindate, 
			'maxDate'              => $maxdate
		];
	@endphp  
	@component('components.forms.searchForm', ["attributeEx" => "id=\"formsearch\"", "values" => $values])  
		@if(count($requests) > 0)
			@slot('export')
				<div class="text-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type = "submit"
								formaction = "{{route('staff.export.review')}}"
							@endslot
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
			$body 			= [];
			$modelBody		= [];
			$modelHead = 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Empresa"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];
			if(isset($requests))
			{
				foreach($requests as $request)
				{
					if($request->idRequest != "")
					{
						$userRequest = App\User::find($request->idRequest);
					}
					else
					{
						$userRequest = new App\User;
					}

					if($request->idElaborate != "")
					{
						$userElaborate = App\User::find($request->idElaborate);
					}
					else
					{
						$userElaborate = new App\User;
					}

					if (isset($request->reviewedEnterprise->name))
					{
						$enterprise = $request->reviewedEnterprise->name;
					}
					else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
					{
						$enterprise = $request->requestEnterprise->name;
					}
					else
					{
						$enterprise = "No hay";
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
								"label" => $request->staff->first()->title != null ? htmlentities($request->staff->first()->title) : 'No hay'
							]
						],
						[
							"content" => 
							[ 
								"label" => $userRequest->id != null ? $userRequest->name." ".$userRequest->last_name." ".$userRequest->scnd_last_name : "No hay solicitante"
							]
						],
						[
							"content" => 
							[ 
								"label" => $userElaborate->id != null ? $userElaborate->name." ".$userElaborate->last_name." ".$userElaborate->scnd_last_name : "No hay"
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
								"label" => $request->statusrequest != null ? $request->statusrequest->description : 'Sin estado'
							]
						],
						[
							"content" => 
							[
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')
							]
						],
						[
							"content" => 
							[
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"variant"		=> "success",
									"label"         => "<span class=\"icon-pencil delete-span\"></span>",
									"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('staff.review.edit',$request->folio)."\""
								]
							]
						]
					];
					array_push($modelBody, $body);
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
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
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa", 
						"maximumSelectionLength" => "1"
					],
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects])
			@endcomponent
		});
	</script> 
@endsection
