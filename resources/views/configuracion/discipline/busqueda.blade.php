@extends('layouts.child_module')

@section('data')
	@component("components.forms.form", ["attributeEx" => "action=\"".route('discipline.follow')."\" method=\"GET\" id=\"formsearch\""])
	@component("components.labels.title-divisor") BUSCAR DISCIPLINA @endcomponent
		@component("components.containers.container-form")
			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4">
				@component("components.labels.label")
					Indicador:
				@endcomponent

				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="indicator" 
						value="{{ isset($indicator) ? $indicator : '' }}" 
						id="indicator" 
						placeholder="Ingrese el indicador"
					@endslot
				@endcomponent
			</div>

			<div class="col-span-4 md:col-start-2 md:col-span-2 md:col-end-4 grid justify-items-center md:justify-items-start">
				@component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"","label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
			</div>
		@endcomponent
	@endcomponent

	@if(count($discipline)>0)
		@php 
			$modelHead = 
			[
				[
					["value" => "ID"], 
					["value" => "Indicador"], 
					["value" => "Descripción"], 
					["value" => "Acción"]
				]
			];
			foreach ($discipline as $d)
			{
				$body = 
				[
					[
						"content"	=> 
						[
							[
								"label"	=> $d->id
							]    
						]
					],
					[
						"content"	=>
						[
							[
								"label"	=> htmlentities($d->indicator)
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => htmlentities($d->name)
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
								"attributeEx"       => "href=\"".route('discipline.edit',$d->id)."\" alt=\"Editar Disciplina\" title=\"Editar Disciplina\"", 
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
		{{ $discipline->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found", ["attributeEx" => "not-found"]) RESULTADO NO ENCONTRADO @endcomponent
	@endif
@endsection