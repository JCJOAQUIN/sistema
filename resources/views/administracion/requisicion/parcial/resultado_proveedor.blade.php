@if (count($providers) > 0)
	@php
		$modelHead =
		[
			[
				["value" => "ID"],
				["value" => "Nombre"],
				["value" => "RFC"],
				["value" => "Agregar a requisición"],
				["value" => "Agregar y seguir buscando"],
				["value" => "Editar"]
			]
		];
		$modelBody = [];
		foreach ($providers as $provider)
		{
			$row = 
			[
				"classEx" => "tr",
				[
					"content" =>
					[
						["label" => $provider->id]
					],
				],
				[
					"content" =>
					[
						["label" => $provider->businessName]
					]
				],
				[
					"content" =>
					[
						["label" => $provider->rfc]
					]
				],
				[
					"content" =>
					[
						[
							"kind"        => "components.buttons.button",
							"classEx"     => "addResultProvider",
							"attributeEx" => "type=\"submit\" name=\"idProviderBtn\" formaction=\"".route('requisition.store-provider',$folio)."\" value=\"".$provider->id."\"",
							"label"       => "<span class=\"icon-plus\"></span>",
							"variant"     => "success"
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind"        => "components.buttons.button",
							"classEx"     => "add-queue",
							"attributeEx" => "type=\"button\" data-provider-id=\"".$provider->id."\" data-provider-business-name=\"".$provider->businessName."\" data-provider-rfc=\"".$provider->rfc."\"",
							"label"       => "<span class=\"icon-add-continous\"></span>"
						]
					]
				],
				[
					"content" =>
					[
						[
							"kind"        => "components.buttons.button",
							"classEx"     => "editResultProvider",
							"attributeEx" => "type=\"button\" name=\"idProviderBtnEdit\" value=\"".$provider->id."\"",
							"label"       => "<span class=\"icon-pencil\"></span>",
							"variant"     => "secondary"
						],
						[
							"kind"        => "components.inputs.input-text",
							"classEx"     => "t_provider",
							"attributeEx" => "type=\"hidden\" value=\"".$provider->id."\" id=\"provider_".$provider->id."\""
						]
					]
				]
			];
			array_push($modelBody, $row);
		}
	@endphp
	@component('components.tables.table',[
		"modelHead" 			=> $modelHead,
		"modelBody" 			=> $modelBody,
	])
		@slot('classEx')
			text-center
		@endslot
	@endcomponent
	<div class="text-center paginate">
        {!! $providers->render() !!}
    </div>
@else
	<div id="not-found">
		@component("components.labels.not-found", ["variant" => "alert"])
			@slot("slot") No se han encontrado proveedores @endslot
		@endcomponent
	</div>
@endif
