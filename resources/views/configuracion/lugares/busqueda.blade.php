@extends('layouts.child_module')
  
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('places.show','edit')."\" method=\"GET\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR LUGARES @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Lugar:"]) @endcomponent

				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder = "Ingrese el lugar" 
						type  = "text" 
						value = "{{$search}}"
						name  = "search"
					@endslot
				@endcomponent
            </div>

            <div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
        @endcomponent 
		
	@if($places->count() > 0)
		<div class="float-right">
			@component("components.buttons.button", ["classEx" => "export", "variant" => "success"])
				@slot("attributeEx")
					type = "submit"
					formaction = "{{ route('places.export') }}" 
					formmethod = "get"
				@endslot
				<span>Exportar a Excel</span>
				<span class="icon-file-excel"></span> 
			@endcomponent
		</div>
		@endcomponent
	
		@php
			$modelHead = 
			[
				[
					["value" => "ID"],
					["value" => "Nombre"], 
					["value" => "Acción"]
				]
			];
			foreach($places as $item)
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
								"label" => htmlentities($item->place),
							]
						]
					],
					[
						"content" =>
						[
							[
								"kind" 			=> "components.buttons.button",
								"attributeEx"	=> "title=\"Editar lugar de trabajo\" href=\"".route('places.edit',$item->id)."\"",							
								"label"			=> "<span class=\"icon-pencil\"></span>",
								"variant"		=> "success",
								"buttonElement"	=> "a"
							]
						]
					],
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

		{{$places->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection
