@component('components.labels.title-divisor')    LISTA DOCUMENTOS DEL PROVEEDOR @endcomponent
@php
	$body 			= [];
	$modelBody		= [];
	$modelHead = ["Nombre", "Archivo", "Modificado por:", "Fecha"];
	
	foreach($requisitionHasProvider->documents->sortByDesc('created') as $doc)
	{
		$body = 
		[
			[
				"content" => 
				[
					[
						"label" => $doc->name
					]
				]
			],
			[
				"content" => 
				[
					[
						"kind"        => "components.buttons.button", 
						"buttonElement" => "a",
						"variant" => "dark-red",
						"label" => "PDF",
						"attributeEx" => "target=\"_blank\" href=\"".url('docs/requisition/'.$doc->path)."\""
					],
				]
			],
			[
				"content" => 
				[
					[
						"label" => $doc->user->fullName()
					]
				]
			],
			[
				"content" => 
				[
					[
						"label" => Carbon\Carbon::parse($doc->created)->format('d-m-Y')
					]
				]
			]
		];
		array_push($modelBody, $body);
	}
@endphp
@component('components.tables.alwaysVisibleTable',[
	"modelHead" => $modelHead,
	"modelBody" => $modelBody
])
	@slot('classEx')
		text-center
	@endslot
	@slot('attributeExBody')
		id="list_employees"
	@endslot
@endcomponent