@extends("layouts.child_module")
@section("data")
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label")
					Estado de solicitud:
				@endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::orderName()->whereIn("idrequestStatus",[2,3,4,5,9,19])->get() as $s)
					{
						$description = $s->description; 
						if(isset($status) && $status == $s->idrequestStatus)
						{
							$options = $options->concat([["value"=>$s->idrequestStatus, "selected"=>"selected", "description"=>$description]]);
						}
						else
						{
							$options = $options->concat([["value"=>$s->idrequestStatus, "description"=>$description]]);
						}
					}
					$attributeEx = "title=\"Estado de Solicitud\" name=\"status\"";
					$classEx = "js-status";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot("export")
			<div class="text-right">
				@component("components.buttons.button",["variant" => "success"])
				@slot("attributeEx") type="submit" formaction="{{ route("computer.export.follow") }}" @endslot
				@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
				@endcomponent
			</div>
			@endslot
		@endif
	@endcomponent
	@if(count($requests) > 0)
		@php
			$heads = [
				[
					["value" => "Folio"],
					["value" => "Título"],
					["value" => "Solicitante"],
					["value" => "Elaborado por"],
					["value" => "Empresa"],
					["value" => "Estado"],
					["value" => "Fecha de elaboración"],
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
							"label" => $request->computer()->exists() && $request->computer->first()->title != null ? htmlentities($request->computer->first()->title) : "No hay",
						]
					],
					[
						"content" =>
						[
							"label" => ($request->idRequest != "" ? $request->requestUser->fullName() : "No hay solicitante")
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
							"label" => (isset($request->requestEnterprise->name) && $request->requestEnterprise->name != "" ? $request->requestEnterprise->name : "No hay"),
						],
					],
					[
						"content" =>
						[
							"label" => $request->statusrequest->description,
						]
					],
					[
						"content" =>
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->fDate)->format('d-m-Y H:i'),
						]
					],
				];			
				if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 9 || $request->status == 10 || $request->status == 11) 
				{
					$table[] = [
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn load-actioner",
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Nueva solicitud\" title=\"Nueva solicitud\" href=\"".route("computer.create.new",$request->folio)."\"",
								"variant" => "warning",
								"label" => "<span class='icon-plus'></span>",
							],
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn load-actioner",
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Ver solicitud\" title=\"Ver solicitud\" href=\"".route("computer.follow.edit",$request->folio)."\"",
								"variant" => "secondary",
								"label" => "<span class='icon-search'></span>",
							],
						],
					];
				}				
				elseif($request->status == 3 || $request->status == 4 || $request->status == 5  || $request->status == 10 || $request->status == 11)
				{
					$table[] = [	
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn load-actioner",
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Ver solicitud\" title=\"Ver solicitud\" href=\"".route("computer.follow.edit",$request->folio)."\"",
								"variant" => "secondary",
								"label" => "<span class='icon-search'></span>",
							],
						],
					];
				}
				else
				{
					$table[] = [
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn", 
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Editar solicitud\" title=\"Editar solicitud\" href=\"".route("computer.follow.edit",$request->folio)."\"",
								"variant" => "success",
								"label" => "<span class='icon-pencil'></span>",
							],
						],
					];
				} 
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
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione un estado", 
						"maximumSelectionLength" => "1"
					],
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
