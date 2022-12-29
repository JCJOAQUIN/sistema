@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('responsibility.search')."\" method=\"GET\" id=\"formsearch\""])
        @component("components.labels.title-divisor")
            BUSCAR RESPONSABILIDADES
        @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-2">
				@component("components.labels.label")
					Responsabilidad: 
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "name" 
						value = "{{ isset($name) ? $name : '' }}"
						id = "input-search" 
						placeholder = "Ingrese la responsabilidad"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-2">
				@component("components.labels.label")
					Descripción:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						name = "description" 
						value = "{{ isset($description) ? $description : '' }}"
						id = "input-search" 
						placeholder = "Ingrese la descripción"
					@endslot
				@endcomponent 
			</div>

			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent

	@if(count($responsibilities) > 0)
		@php 
            $modelHead = 
		    [
				[
					["value"=> "ID"],
					["value"=> "Responsabilidad"],
					["value"=> "Descripción"],
					["value"=> "Acción"]
				]
            ];

            $modelBody = [];
			$body = [];
            foreach($responsibilities as $responsibility)
            {
                $body = 
                [
                    [
                        "content" => 
                        [
                            [
                                "label" => $responsibility->id 
                            ]    
                        ]
                    ],
                    [
                        "content" =>
                        [
                            [
                                "label" => htmlentities($responsibility->responsibility),
                            ]
                        ]
                    ],
					[
                        "content" =>
                        [
                            [
                                "label" => htmlentities($responsibility->description),
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
                                "attributeEx"       => "href=\"".route('responsibility.edit',$responsibility->id)."\" alt=\"Editar\" title=\"Editar\"", 
                                "label"             => "<span class=\"icon-pencil\"></span>"
                            
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
				"modelBody" => $modelBody,
				"modelHead" => $modelHead,
			])			
			@endcomponent
        </div>

		{{$responsibilities->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection