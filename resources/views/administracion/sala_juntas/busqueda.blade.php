@extends('layouts.child_module')
@section('data')
	@component('components.labels.title-divisor') BUSCAR @endcomponent	
	@component("components.forms.searchForm", ["attributeEx" => "id=\"formsearch\"", "variant" => "default"])
		<div class="col-span-2">
			@component("components.labels.label") Número: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="id"
					placeholder="Ingrese un número"
					value="{{ isset($id_boardroom) ? $id_boardroom : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Nombre: @endcomponent
			@component("components.inputs.input-text")
				@slot("attributeEx")
					name="name"
					placeholder="Ingrese un nombre"
					value="{{ isset($name) ? $name : '' }}"
				@endslot
			@endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Ubicación: @endcomponent
			@php
				$options = collect();
				foreach(App\Property::all() as $p)
				{
					if(isset($location) && $location == $p->id)
					{
						$options = $options->concat([["value"=>$p->id, "selected"=>"selected", "description"=>$p->property]]);
					}
					else
					{
						$options = $options->concat([["value"=>$p->id, "description"=>$p->property]]);
					}
				}
				$attributeEx = "name=\"location\" multiple=\"multiple\"";
				$classEx = "removeselect location";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
		</div>
		<div class="col-span-2">
			@component("components.labels.label") Empresa: @endcomponent
			@php
				$options = collect();
				foreach($enterprises as $enterprise)
				{
					$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35).'...' : $enterprise->name;
					if(isset($enterprise_id) && $enterprise_id == $enterprise->id)
					{
						$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
					}
					else
					{
						$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
					}
				}
				$attributeEx = "title=\"Empresa\" name=\"enterprise_id\" multiple=\"multiple\"";
				$classEx = "js-enterprise";
			@endphp
			@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx]) @endcomponent
		</div>
		@if(count($boardrooms) > 0)
			@slot("export")
			<div class="text-right">
				<label>
					@component("components.buttons.button",["variant" => "success"])
					@slot("attributeEx") type="submit" formaction="{{ route("boardroom.follow.export") }}" @endslot
					@slot("slot") <span>Exportar a Excel</span><span class="icon-file-excel"></span> @endslot
					@endcomponent
				</label>
			</div>
			@endslot
		@endif
	@endcomponent
	@if(count($boardrooms) > 0)
		@php
			$modelHead = [];
			$modelBody = [];
			$modelHead = 
			[
				[
					["value" => "#"],
					["value" => "Nombre"],
					["value" => "Ubicación"],
					["value" => "Empresa"],
					["value" => "Acción"]
				]
			];

			foreach($boardrooms as $boardroom)
			{
				$modelBody [] = 
				[
					[
						"content" =>
						[
							"label" => $boardroom->id,
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($boardroom->name),
						]
					],
					[
						"content" =>
						[
							"label" => isset($boardroom->locationData->property) ? htmlentities($boardroom->locationData->property) : "",
						],
					],
					[
						"content" =>
						[
							"label" => $boardroom->enterprise->name,
						],
					],
					[
						"content" =>
						[
							[
								"kind" => "components.buttons.button", 
								"classEx" => "follow-btn", 
								"buttonElement" => "a",
								"attributeEx" => "alt=\"Editar\" title=\"Editar\" href=\"".route("boardroom.update",$boardroom->id)."\"",
								"variant" => "success",
								"label" => "<span class='icon-pencil'></span>",
							],
						],
					],
				];
			}
		@endphp
		@component("components.tables.table",[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
			"themeBody" => "striped"
		])
		@endcomponent
		{{ $boardrooms->appends($_GET)->links() }}
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
						"maximumSelectionLength" => "1",
					],
					[
						"identificator"          => ".location", 
						"placeholder"            => "Seleccione la ubicación",
						"maximumSelectionLength" => "1",
					],
				]);
			@endphp
			@component('components.scripts.selects',['selects'=>$selects]) @endcomponent
		});
	</script>
@endsection
