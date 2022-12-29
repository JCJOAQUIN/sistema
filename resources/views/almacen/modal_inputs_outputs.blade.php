@php
	$total = $warehouse->quantityReal - $warehouse->damaged ;
	$total = $total - $warehouse->quantity;
@endphp
@Table(
[
	"classEx" => "table",
	"modelHead" => 
	[
		[
			["value" => "Producto/Material"],
			["value" => "Categoría"],
			["value" => "Clave"],
			["value" => "Recibidos"],
			["value" => "Dañados"],
			["value" => "Existencia"],
			["value" => "Entregados"],
		]
	],
	"modelBody" => 
	[
		[
			[
				"content"	=>
				[
					["label" => htmlentities($warehouse->cat_c->description)],
				]
			],
			[
				"content"	=>
				[
					["label" => $warehouse->wareHouse ? $warehouse->wareHouse->description : 'Sin categoría'],
				]
			],
			[
				"content" =>
				[
					["label" => $warehouse->short_code],
				]
			],
			[
				"content" =>
				[
					["label" => $warehouse->quantityReal],
				]
			],
			[
				"content" =>
				[
					["label" => $warehouse->damaged ? $warehouse->damaged : 0],
				]
			],
			[
				"content" =>
				[
					["label" => $warehouse->quantity],
				]
			],
			[
				"content" =>
				[
					["label" => $total],
				]
			]
		]
	]
])
@endTable