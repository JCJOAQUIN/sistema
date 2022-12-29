<div id="alert_select_requisition" role="alert">
	@component("components.labels.not-found")		
		@slot('text')
			El solicitante tiene los siguientes Folios de Asignación de Recurso sin comprobación
		@endslot
	@endcomponent
</div>
@php
	$body = [];
	$modelBody = [];
	$heads = ["Folio","Título","Monto"];
	foreach($arrayResource as $resource)
	{
		$body =
		[
			"classEx" => "tr",
			[
				"content" =>
				[
					[
						"label" => $resource['folio'],
					]
				]
			],
			[
				"content" =>
				[
					[
						"label" => $resource['title'],
					]
				]
			],
			[
				"content" =>
				[
					[
						"label" => "$".number_format($resource['amount'],2),
					]
				]
			]
		];
		$modelBody[] = $body;
	}
@endphp
@component("components.tables.alwaysVisibleTable",[
	"modelHead" => $heads,
	"modelBody" => $modelBody,
])
	@slot("classExBody")
		request-validate
	@endslot
	@slot("attributeExBody")
		id="tbody-conceptsNew"
	@endslot
@endcomponent