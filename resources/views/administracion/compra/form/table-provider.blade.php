@if(count($providers)>0)
	@php 
		$modelHead=["ID", "Nombre", "RFC", "Acción"];
		$modelBody = [];
		foreach ($providers as $provider)
		{
			$providerJSON['provider'] = $provider;
			$providerJSON['banks']    = $provider->providerData->providerBank;
			$bankName                 = ($provider->providerData->providerBank->first() != null ? App\Banks::find($provider->providerData->providerBank->first()->banks_idBanks)->description : "");
			$body =
			[
				[
					"content" =>
					[
						"label" => $provider->idProvider
					]
				],
				[
					"content" =>
					[
						"label" => htmlentities($provider->businessName),
					]
				],
				[
					"content" =>
					[
						"label" => $provider->rfc
					]
				],
				[
					"content" =>
					[
						[
							"kind"        => "components.buttons.button",
							"label"       => "Seleccionar",
							'variant'     => "success",
							'attributeEx' => "type=\"button\" value=\"".$provider->idProvider."\"",
							'classEx'     => "edit"
						],
						[
							"kind"        => "components.inputs.input-text",
							'attributeEx' => "type=\"hidden\" id=\"provider_".$provider->idProvider."\" value=\"".base64_encode(json_encode($providerJSON))."\""
						],
						[
							"kind"        => "components.inputs.input-text",
							'attributeEx' => "type=\"hidden\" name=\"name-bank-provider\" value=\"".$bankName."\""
						]
					]
				]
			];
			array_push($modelBody, $body);
		}
	@endphp
	@component('components.tables.alwaysVisibleTable',[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"themeBody" => "striped"
	])
	@endcomponent
	<div class="text-center paginate">
		{!! $providers->render() !!}
	</div>
	
@else
	@component('components.labels.not-found', ["text" => "No se han encontrado proveedores registrados"])@endcomponent  
@endif