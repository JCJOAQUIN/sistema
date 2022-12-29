@extends("layouts.child_module")
@section("data")
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@if(count($requests) > 0)
			@slot("export")
			<div class="text-right">
				<label>
					@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type=submit formaction={{ route("computer.export.delivery") }} @endslot
					@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endcomponent
				</label>
			</div>
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$heads = 
			[
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Empresa"],
					["value" => "Estado"],
					["value" => "Fecha de revisión"],
					["value" => "Acción"],
				]
			];
			foreach($requests as $request)
			{
				$table = [
						[
							"content" =>
							[
								"label" => $request->folio,
							]
						],
						[
							"content" =>
							[				
								"label" => $request->computer->first()->title != null ? htmlentities($request->computer->first()->title) : "No hay",
							]
						],
						[
							"content" =>
							[
								"label" => $request->requestUser->fullName(),
							],
						],
						[
							"content" =>
							[
								"label" => $request->elaborateUser->fullName(),
							],
						],
						[
							"content" =>
							[
								"label" => $request->requestEnterprise->name,
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
								"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->reviewDate)->format('d-m-Y H:i'),
							]
						],
						[
							"content" =>
							[
								[
									"kind" => "components.buttons.button", 
									"classEx" => "follow-btn load-actioner",
									"buttonElement" => "a",
									"attributeEx" => "alt=\"Editar solicitud\" title=\"Editar solicitud\" href=\"".route("computer.delivery.edit",$request->folio)."\"",
									"variant" => "success",
									"label" => "<span class='icon-pencil'></span>",
								]
							]
						],
					];
				$modelTable[] = $table;
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $heads,
			"modelBody" => $modelTable,
			"themeBody" => "striped"
		])
		@endcomponent
		{{ $requests->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection
@section("scripts")
	<link rel="stylesheet" href="{{ asset("css/jquery-ui.css") }}">
	<script src="{{ asset("js/jquery-ui.js") }}"></script>
	<script src="{{ asset("js/jquery.numeric.js") }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect([
					[
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
		});
	</script> 
@endsection
