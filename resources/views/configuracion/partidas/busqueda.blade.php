@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('configuration-items.search')."\" method=\"GET\" id=\"formsearch\""])
		@component('components.labels.title-divisor') BUSCAR PARTIDAS @endcomponent

		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Partida:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="item_name" 
						value="{{ isset($item_name) ? $item_name : '' }}"
						id="input-search" 
						placeholder="Ingrese la partida"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label")
					Actividad:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="item_activity" 
						value="{{ isset($item_activity) ? $item_activity : '' }}" 
						class="input-text-search" 
						id="input-search" 
						placeholder="Ingrese la actividad"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label")
					Nombre del contrato:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="item_contract" 
						value="{{ isset($item_contract) ? $item_contract : '' }}"
						id="input-search" 
						placeholder="Ingrese el nombre del contrato"
					@endslot	
				@endcomponent
			</div>

			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent

	@if (count($items)>0)
		@php 
            $modelHead = 
            [
				[
					["value" => "ID"], 
					["value" => "Partida"], 
					["value" => "Actividad"], 
					["value" => "Contrato"], 
					["value" => "Acción"]
				]
            ];
			
            foreach($items as $item)
            {
                $body = 
                [
                    [
                        "content" => 
                        [
                            [
                                "label" => $item->id 
                            ]    
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "label" => htmlentities($item->contract_item)
                            ]
                        ]
                    ],
					[
                        "content" =>
                        [
                            [
                                "label" => htmlentities($item->activity)
                            ]
                        ]
                    ],
					[
                        "content" =>
                        [
                            [
                                "label" => ((isset($item->contractData->first()->name) && $item->contractData->first()->name != "") ? htmlentities($item->contractData->first()->name) : "---")
                            ]
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "kind"              => "components.buttons.button",
                                "variant"           => "success", 
                                "buttonElement"     => "a", 
                                "attributeEx"       => "href=\"".route('items.edit',$item->id)."\" alt=\"Editar Partida\" title=\"Editar Partida\"", 
                                "label"             => "<span class=\"icon-pencil\"></span>"
                            
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

		{{$items->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection