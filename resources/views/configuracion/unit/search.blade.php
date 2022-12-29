@extends('layouts.child_module')
@section('data')
	@component('components.forms.form',["attributeEx" => "id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR UNIDADES @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label") Unidad: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type = "text"
						name = "unit"
						placeholder = "Ingrese la unidad"
						value = "{{ $unit }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if (count($units) > 0)
		@php 
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Unidad"],
					["value" => "Acción"]
				]
			];
			foreach ($units as $u)
			{
				$body =
				[
					[
						"content" 	=> 
						[
							[
								"label" => $u->id
							]
						]
					],
					[
						"content" => 
						[
							[
								"label" => htmlentities($u->name),
							]
						]
					],
					[
						"content" => 
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "alt=\"Editar unidad\" title=\"Editar unidad\" href=\"".route('unit.edit',$u->id)."\"",
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		@component("components.tables.table",
		[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent
		{{$units->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection