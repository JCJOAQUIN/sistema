@extends('layouts.child_module')
  
@section('data')
	@component("components.forms.form", ["attributeEx" => "id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR ETIQUETAS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label", ["label" => "Etiqueta:"]) @endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						placeholder = "Ingrese la etiqueta" 
						type  = "text" 
						value = "{{ $search }}"
						name  = "search"
						id    = "input-search"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent
		@if($label->count() >0)
			<div class="float-right">
				@component("components.buttons.button", ["variant" => "success", "classEx" => "export"])
					@slot("attributeEx")
						type		= "submit"
						formaction	= "{{ route('labels.export') }}" 
					@endslot
					Exportar a Excel
					<span class='icon-file-excel'></span>
				@endcomponent
			</div>
		@endif
	@endcomponent
	@if($label->count() >0)
		@php
			$modelHead = 
			[
				["value" => "ID"], 
				["value" => "Descripción"], 
				["value" => "Acción"]
			];
			foreach($label as $item)
			{
				$body = 
				[
					[
						"content"	=> 
						[
							[
								"label" => $item->idlabels
							]	
						]	
					],
					[
						"content" => 
						[
							[
								"label" => htmlentities($item->description),
							]	
						]		
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "a",
								"attributeEx"	=> "title=\"Editar etiqueta\" href=\"".route('labels.edit',$item->idlabels)."\"",
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
		{{$label->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection
