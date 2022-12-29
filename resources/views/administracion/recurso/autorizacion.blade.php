@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@if (count($requests) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type="submit"
								formaction="{{ route('resource.export.authorization') }}" 
							@endslot
							@slot("classEx")
								export
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
							"label" => $request->resource->first()->title != null ? htmlentities($request->resource->first()->title) : 'No hay',
						]
					],
				];
				foreach(App\User::where("id",$request->idRequest)->get() as $user)
				{
					$body[] = [
						"content" =>
						[
							"label" => $user->name." ".$user->last_name." ".$user->scnd_last_name,
						]
					];
				}
				foreach(App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{
					$body[] = [
						"content" =>
						[
							"label" => $elaborate->name." ".$elaborate->last_name." ".$elaborate->scnd_last_name,
						]
					];
				}
				$body[] = 
				[
					"content" =>
					[
						"label" => isset($request->requestEnterprise->name) ? $request->requestEnterprise->name : "No hay",
					]
				];
				$body[] =
				[
					"content" =>
					[
						"label" => $request->statusrequest->description,
					]
				];
				$time = strtotime($request->fDate);
				$date = date("d-m-Y H:i",$time);
				$body[] = 
				[
					"content" =>
					[
						"label" => $date,
					]
				];
				$body[] = 
				[
					"content" =>
					[
						"kind"          => "components.buttons.button",
						"buttonElement" => "a",
						"classEx"		=> "follow-btn load-actioner",
						"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('resource.authorization.edit',$request->folio)."\"",
						"variant"		=> "success",	
						"label"			=> "<span class=\"icon-pencil\"></span>"
					]
				];
				$modelBody[] = $body;	
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
			])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
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
			@php
				$selects = collect([
					[
						"identificator"			=> ".js-enterprise",
						"placeholder"			=> "Seleccione una empresa",
						"languaje"				=> "es",
						"maximumSelectionLength"=> "1",
					],
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