@extends('layouts.child_module')
@section('data')
	@component("components.forms.form",["attributeEx" => "method=\"GET\" action=\"".route('contract.follow')."\" id=\"formsearch\""])
		@component("components.labels.title-divisor") BUSCAR CONTRATOS @endcomponent
		@component("components.containers.container-form", ["attributeEx" => "id=\"container-data\""])
			<div class="col-span-2">
				@component("components.labels.label") Número de contrato: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="contract_number" value="{{ isset($contract_number) ? $contract_number : '' }}" id="input-search" placeholder="Ingrese el número del contrato"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label") Nombre del contrato: @endcomponent
				@component("components.inputs.input-text")
					@slot("attributeEx")
						type="text" name="contract_name" value="{{ isset($contract_name) ? $contract_name : '' }}" id="input-search" placeholder="Ingrese el nombre del contrato"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
				@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
				@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
			</div>
		@endcomponent
	@endcomponent
	@if (count($contracts)>0)
		@php
			$modelHead =
			[
				[
					["value" => "ID"], 
					["value" => "Número de contrato"], 
					["value" => "Nombre"], 
					["value" => "Acción"]
				]
			];
			foreach($contracts as $contract)
			{
				$body = 
				[
					[
						"content" =>
						[
							"label" => $contract->id
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($contract->number),
						]
					],
					[
						"content" =>
						[
							"label" => htmlentities($contract->name),
						]
					],
					[
						"content" =>
						[
							[
								"kind" => "components.buttons.button",
								"buttonElement" => "a",
								"variant" => "success",
								"attributeEx" => "title=\"Editar Contrato\" href=\"".route('contract.edit',$contract->id)."\"",
								"label" => "<span class=\"icon-pencil\"></span>"
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
		{{ $contracts->appends($_GET)->links() }}
	@else
		@component("components.labels.not-found") @endcomponent
	@endif
@endsection