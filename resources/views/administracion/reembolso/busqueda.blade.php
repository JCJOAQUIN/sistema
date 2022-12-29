@extends('layouts.child_module')
@section('data')
	@component("components.labels.title-divisor") BUSCAR SOLICITUDES @endcomponent
	@php
		$values = ["enterprise_option_id" => $option_id, "enterprise_id" => $enterpriseid, "folio" => $folio, "name" => $name, "minDate" => $mindate, "maxDate" => $maxdate];
	@endphp
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "values" => $values])
		@slot("contentEx")
			<div class="col-span-2">
				@component("components.labels.label") Título: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						name="title_shear"
						id="input-search"
						placeholder="Ingrese el título"
						value="{{ isset($title_shear) ? $title_shear : "" }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Proyecto:
				@endcomponent
				@php
					$options = collect();
					if(isset($project) && $project != "")
					{
						$projectSelected = App\Project::find($project);
						$options = $options->concat([["value" => $project, "selected" => "selected", "description" => $projectSelected->proyectName]]);
					}
					$attributeEx = "title=\"Proyecto\" name=\"project\" multiple=\"multiple\"";
					$classEx = "js-project";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Estado:
				@endcomponent
				@php
					$options = collect();
					foreach(App\StatusRequest::orderName()->whereIn('idrequestStatus',[2,3,4,5,6,7,10,11,12])->get() as $s)
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
					$attributeEx = "title=\"Estado de Solicitud\" name=\"status\" multiple=\"multiple\"";
					$classEx = "js-status";
				@endphp
				@component ("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
			</div>
		@endslot
		@if(count($requests) > 0)
			@slot("export")
			<div class="text-right">
				<label>
					@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type="submit" formaction="{{ route("refund.export.follow") }}" @endslot
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
					["value" => "Total"],
					["value" => "Fecha de elaboración"],
					["value" => "Acción"]
				]
			];
			foreach($requests as $request)
			{
				$buttons = [];
				$table = [
					"classEx" => "tr",
						[
							"content" =>
							[
								"label" => $request->new_folio != null ? $request->new_folio : $request->folio,
							]
						],
						[
							"content" =>
							[
								"label" => $request->refunds->first()->title != null ? htmlentities($request->refunds->first()->title) : 'No hay',
							]
						],
						[
							"content" =>
							[
								"label" => ($request->idRequest != "" ? $request->requestUser->fullName() : "No hay solicitante"),
							],	
						],
						[
							"content" =>
							[
								"label" => $request->elaborateUser->fullName(),
							],
						],
					];
				if (isset($request->reviewedEnterprise->name))
				{
					$enterpriseName = $request->reviewedEnterprise->name;
				}
				elseif(isset($request->reviewedEnterprise->name) == false && isset($request->requestEnterprise->name))
				{
					$enterpriseName = $request->requestEnterprise->name;
				}
				else
				{
					$enterpriseName = "No hay";
				}
				$table[] = [
					"content" =>
					[
						"label" => $enterpriseName,
					]
				];
				$table[] = [
					"content" =>
					[
						"label" => $request->statusrequest->description,
					]
				];
				$table[] = [
					"content" =>
					[
						"label" => "$ ".number_format($request->refunds->first()->total,2),
					]
				];
				$table[] = [
					"content" =>
					[
						"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->fDate)->format('d-m-Y H:i'),
					]
				];
				if($request->status == 5 || $request->status == 6 || $request->status == 7 || $request->status == 10 || $request->status == 11 || $request->status == 13)
				{
					if($request->new_folio == null)
					{
						$buttons[] =
						[
							"kind" => "components.buttons.button", 
							"classEx" => "follow-btn load-actioner",
							"buttonElement" => "a",
							"attributeEx" => "alt=\"Nueva solicitud\" title=\"Nueva solicitud\" href=\"".route('refund.create.new',$request->folio)."\"",
							"variant" => "warning",
							"label" => "<span class='icon-plus'></span>",
						];
					}
					$buttons[] =
					[
						"kind" => "components.buttons.button", 
						"classEx" => "follow-btn load-actioner",
						"buttonElement" => "a",
						"attributeEx" => "alt=\"Ver solicitud\" title=\"Ver solicitud\" href=\"".route('refund.follow.edit',$request->folio)."\"",
						"variant" => "secondary",
						"label" => "<span class='icon-search'></span>",
					];
					$table[]["content"] = $buttons;
				}				
				elseif($request->status == 3 || $request->status == 4 || $request->status == 5 || $request->status == 10 || $request->status == 11 || $request->status == 12)
				{
					$table[] = [	
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn load-actioner",
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Ver solicitud\" title=\"Ver solicitud\" href=\"".route('refund.follow.edit',$request->folio)."\"",
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
								"classEx" => "follow-btn load-actioner", 
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Editar solicitud\" title=\"Editar solicitud\" href=\"".route('refund.follow.edit',$request->folio)."\"",
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
						"identificator"          => ".js-enterprise", 
						"placeholder"            => "Seleccione la empresa",
						"maximumSelectionLength" => "1"
					],
					[
						"identificator"          => ".js-status", 
						"placeholder"            => "Seleccione el estatus", 
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			generalSelect({'selector': '.js-project', 'model': 24});
		});
	</script>
@endsection

