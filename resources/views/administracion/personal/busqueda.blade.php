@extends('layouts.child_module')
  
@section('data')
	@component('components.labels.title-divisor') Buscar Solicitudes @endcomponent
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
				@component('components.labels.label') Estado: @endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->get() as $s)
					{
						$description = $s->description;
						if(isset($status) && $status == $s->idrequestStatus)
						{
							$options = $options->concat([['value'=>$s->idrequestStatus, 'selected'=>'selected', 'description'=>$s->description]]);
						}
						else
						{
							$options = $options->concat([['value'=>$s->idrequestStatus, 'description'=>$s->description]]);
						}
					}
					$attributeEx = "title=\"Solicitud de Estado\" name=\"status\" multiple=\"multiple\"";
					$classEx = "js-status";
				@endphp
				@component('components.inputs.select', 
					[
						'options'     => $options, 
						'attributeEx' => $attributeEx, 
						'classEx'     => $classEx
					])
				@endcomponent
			</div>  
		@endslot
		@if(count($requests) > 0)
			@slot('export')
				<div class="text-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type="submit" 
								formaction="{{route('staff.export.follow')}}" 
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
					$body = 
					[
						[							
							"content" => 
							[
								"label" => $request != null ? $request->folio : '---'
							]
						],
						[
							"content" => 
							[ 
								"label" => $request->staff->first() != null ? htmlentities($request->staff->first()->title) : '---'
							]
						],
						[
							"content" => 
							[ 
								"label" => $userRequest->id != null ? $userRequest->name." ".$userRequest->last_name." ".$userRequest->scnd_last_name : "---"
							]
						],
						[
							"content" => 
							[
								"label" => $userElaborate->id != null ? $userElaborate->name." ".$userElaborate->last_name." ".$userElaborate->scnd_last_name : "---"
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
								"label" => $request->statusrequest != null ? $request->statusrequest->description : '---'
							]
						],
						[
							"content" => 
							[ 
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y')
							]
						]
					];
					
					if($request->status == 5 || $request->status == 6 || $request->status == 7  || $request->status == 10 || $request->status == 11) 
					{
						$btnElements = [
							"content" => 
							[ 
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"label"         => "<span class=\"icon-plus\"></span>",
									"variant"       => "warning",
									"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=".route('staff.create.new',$request->folio)
								],
								[
									"kind"          => "components.buttons.button",
									"buttonElement" => "a",
									"variant"       => "secondary",
									"label"         => "<span class=\"icon-search\"></span>",
									"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('staff.follow.edit',$request->folio)
								]
							]
						];
						$body[] = $btnElements;
					}
					else if($request->status == 3 || $request->status == 4 || $request->status == 5  || $request->status == 10 || $request->status == 11)
					{ 
						$btnElements = 
						[
							"content" => 
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "secondary",
								"label"         => "<span class=\"icon-search\"></span>",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=".route('staff.follow.edit',$request->folio)
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
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"variant"       => "success",
								"label"         => "<span class=\"icon-pencil\"></span>",
								"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=".route('staff.follow.edit',$request->folio)
							]
						];
						$body[] = $btnElements;
					}
					array_push($modelBody, $body);
				}
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
			@slot('classEx')
				text-center
			@endslot
			@slot('attributeEx')
				id="table"
			@endslot
			@slot('attributeExBody')
				id="body2"
			@endslot
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione un estatus", 
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione una empresa", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$(function() 
			{
				$( ".datepicker" ).datepicker({ maxDate: 0, dateFormat: "dd-mm-yy" });
			});
		});
    </script> 
@endsection

