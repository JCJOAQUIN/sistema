@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('blueprints.follow')."\" method=\"GET\" id=\"formsearch\""])
		@component('components.labels.title-divisor') BUSCAR PLANOS @endcomponent

		@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""])
            <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				
				@component("components.labels.label") Nombre: @endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "blueprint_name" 
						value = "{{ isset($blueprint_name) ? $blueprint_name : '' }}"
						placeholder = "Ingrese el nombre"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent
	@endcomponent

	@if(count($blueprints) > 0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Nombre del plano"], 
					["value" => "Acciones"]
				]
			];
			foreach ($blueprints as $blueprint)
			{
				$body =
				[
					[ 
						"content"	=> 
						[
							["label" => $blueprint->id ]
						]		
					],
					[ 
						"content" => 
						[
							["label" => htmlentities($blueprint->name) ]
						]	
					]		
				];

				$buttons = 
				[
					["kind" => "components.buttons.button","variant" => "success", "buttonElement" => "a", "attributeEx" => "href=\"".route('blueprints.edit',[$blueprint->id])."\" alt=\"Editar Plano\" title=\"Editar Planor\"", "label" => "<span class=\"icon-pencil\"></span>"]
				];
				array_push($body, ["content" => $buttons]);
				$modelBody[] = $body;
			}
		@endphp

		@component("components.tables.table",
		[
			"modelHead" => $modelHead,
			"modelBody" => $modelBody,
		])
		@endcomponent

		{{$blueprints->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "id=\"not-found\""]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection