@extends('layouts.child_module')
@section('data')
		@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
		@php
			$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
		@endphp
		@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","values" => $values])
			@slot("contentEx")
				<div class="col-span-2">
					@component("components.labels.label") Estado: @endcomponent
					@php
						$options = collect();
							foreach (App\StatusRequest::whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->orderBy('description','asc')->get() as $s) 
							{
								$description = $s->description;	
								if (isset($status) && $status == $s->idrequestStatus)
								{
									$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
								}
								else
								{
									$options = $options->concat([["value"=>$s->idrequestStatus,"description"=>$description]]);
								}
							}
						$attributeEx = "name=\"status\" multiple=\"multiple\"";
						$classEx = "js-status";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])@endcomponent
				</div>
			@endslot
			@if (count($requests) > 0)
				@slot("export")
					<div class="float-right">
						@component('components.labels.label')
							@component('components.buttons.button',['variant' => 'success'])
								@slot('attributeEx') 
									type=submit 
									formaction={{ route('resource.export.follow') }} @endslot
								@slot('label')
									<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
								@endslot
							@endcomponent
						@endcomponent
					</div>
				@endslot
			@endif
		@endcomponent
		@if (count($requests) > 0)
			@php
				$body = [];
				$modelBody = [];
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

				foreach($requests as $request)
				{
					$body = 
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
								"label" => (isset($request->resource->first()->title) && $request->resource->first()->title != null) ? htmlentities($request->resource->first()->title) : "No hay",
							]
						]
					];

					if($request->idRequest == "")
					{
						$body [] = [
							"content" =>
							[
								"label" => "No hay solicitante",
							]
						];
					}
					else 
					{
						foreach(App\User::where("id",$request->idRequest)->get() as $user)
						{
							$body [] = [
								"content" =>
								[
									"label" => $user->name." ".$user->last_name." ".$user->scnd_last_name,
								]
							];
						}
					}
					foreach(App\User::where("id",$request->idElaborate)->get() as $user)
					{
						$body[] = [
							"content" =>
							[
								"label" => $user->name." ".$user->last_name." ".$user->scnd_last_name,
							]
						];
					}
					if (isset($request->reviewedEnterprise->name))
					{
						$body[] = [
								"content" =>
							[
								"label" => $request->reviewedEnterprise->name,
								]
							];
					}
					else if(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
					{
						$body[] = [
								"content" =>
								[
									"label" => $request->requestEnterprise->name,
								]
							];
					}
						
					else
					{
						$body[] = [
								"content" =>
								[
									"label" => "No hay"
								]
							];
					}
					$body[] = 
					[
						"content" =>	
						[
							"label" => $request->statusrequest != null ? $request->statusrequest->description : "No existe",
						]
					];
					$body[] = 
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i'),
						]
					];
					if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 10 || $request->status == 11 || $request->status == 13) 
					{
						$body[]["content"] = 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a",
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Nueva Solicitud\" title=\"Nueva Solicitud\" href=\"".route("resource.create.new",$request->folio)."\"",
								"variant"		=> "warning",	
								"label"			=> "<span class=\"icon-plus\"></span>"
							],
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"classEx"		=> "follow-btn",
								"attributeEx"   => "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("resource.follow.edit",$request->folio)."\"",
								"variant" 		=> "secondary",
								"label"			=> "<span class=\"icon-search\"></span>"
							],
						];	
					}
					else if($request->status == 3 || $request->status == 4 || $request->status == 5  || $request->status == 10 || $request->status == 11 || $request->status == 12) 
					{
						$body[]["content"] = 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"	   	=> "follow-btn load-actioner",
								"variant" 		=> "secondary",
								"attributeEx"  	=> "alt=\"Ver Solicitud\" title=\"Ver Solicitud\" href=\"".route("resource.follow.edit",$request->folio)."\""
							],
						];	
								
					}
					else 
					{
						$body[]["content"] = 
						[
							[
								"kind"          => "components.buttons.button",
								"buttonElement" => "a", 
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"classEx"	  	=> "follow-btn load-actioner", 
								"variant" 		=> "success",
								"attributeEx" 	=> "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route("resource.follow.edit",$request->folio)."\""
							],
						];				
					}
					$modelBody[] = $body;
				}
			@endphp
			@component("components.tables.table",[
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
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione una empresa",
						"languaje"				=> "es",
						"maximumSelectionLength"=> "1",
					],
					[
						"identificator"			=> ".js-status",
						"placeholder"			=> "Seleccione un estatus",
						"languaje"				=> "es",
						"maximumSelectionLength"=> "1",
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
