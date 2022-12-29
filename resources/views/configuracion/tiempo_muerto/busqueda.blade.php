@extends('layouts.child_module')
@section('data')
	@component("components.forms.form")
		@component('components.labels.title-divisor') BUSCAR CATEGORÍAS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Categoría:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder = "Ingrese la categoría" 
						type  = "text" 
						value = "{{isset($name) ? $name : '' }}"
						name  = "name"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent 
	@endcomponent 
	@if(count($requests) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Categoría"], 
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
							[
								"label" => $request->id
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($request->name),
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind"          => "components.buttons.button",
								"variant"       => "success",
								"buttonElement" => "a",
								"attributeEx"   => "href=\"".route('risk-time-category.edit',$request->id)."\" alt=\"Editar categoría\" title=\"Editar categoría\"",
								"label"         => "<span class=\"icon-pencil\"></span>"
							]
						]
					]
				];
				$modelBody[] = $body;
			}
		@endphp
		<div class="table-responsive">
			@component("components.tables.table",
			[
				"modelHead" => $modelHead, 
				"modelBody" => $modelBody
			])
			@endcomponent
		</div>
		{{$requests->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection
