@extends('layouts.child_module')
  
@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('job-positions.edit')."\" method=\"GET\""])
		@component("components.labels.title-divisor") BUSCAR PUESTOS @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label")
					Nombre:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type = "text" 
						@isset($name) 
							value = "{{ $name }}" 
						@endisset 
						name = "name"
						placeholder = "Ingrese el nombre"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if($job_positions->count() >0)
		@php
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Nombre"], 
					["value" => "Jefe Inmediato"], 
					["value" => "Acción"]
				]
			];
			foreach($job_positions as $item)
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
								"label" => htmlentities($item->name),
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $item->immediateBoss()->exists() ? $item->immediateBoss->name : '---'
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
								"attributeEx"   => "href=\"".route('job-positions.show',$item->id)."\" alt=\"Editar puesto\" title=\"Editar puesto\"",
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
				"modelBody" => $modelBody,
			])
			@endcomponent
		</div>
		{{$job_positions->appends($_GET)->links()}}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection
