@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ['enterprise_option_id' => $option_id, 'enterprise_id' => $enterpriseid, 'folio' => $folio, 'name' => $name, 'minDate' => $mindate, 'maxDate' => $maxdate];
	@endphp
	@component('components.forms.searchForm', [ "attributeEx" => "id=\"formsearch\"", "values" => $values])
		@if (count($requests) > 0)
			@slot('export')
				@component('components.buttons.button', ["variant" => "success"])
					@slot('attributeEx')
						type="submit"
						formaction="{{ route('expenses.export.review') }}"
					@endslot
					@slot('classEx')
						export
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
					["value" => "Acción"]
				]
			];
			foreach($requests as $request)
			{
				$varRequest = '';
				foreach(App\User::where('id',$request->idRequest)->get() as $user)
				{
					$varRequest = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				}
				$varElaborate = '';
				foreach(App\User::where('id',$request->idElaborate)->get() as $elaborate)
				{
					$varElaborate = $elaborate->name.' '.$elaborate->last_name.' '.$elaborate->scnd_last_name;
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
							"label" => $varRequest
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
							"label" => $varElaborate
						]
					],
					[
						"content" =>
						[
							"label" => isset($request->requestEnterprise->name) ? $request->requestEnterprise->name : "No hay"
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
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$request->fDate)->format('d-m-Y H:i')
						]
					],
					[
						"content" =>
						[
							"kind"          => "components.buttons.button",
							"variant"       => "success",
							"label"         => "<span class=\"icon-pencil\"></span>",
							"buttonElement" => "a",
							"classEx"       => "load-actioner",
							"attributeEx"   => "alt=\"Editar Solicitud\" title=\"Editar Solicitud\" href=\"".route('expenses.review.edit',$request->folio)."\""
						]
					]
				];
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
						"identificator"				=> ".js-enterprise",
						"placeholder"				=> "Seleccione la empresa",
						"maximumSelectionLength"	=> "1"
					]
				]);
			@endphp
			@component("components.scripts.selects", ["selects" => $selects]) @endcomponent
		});
    </script> 
@endsection
