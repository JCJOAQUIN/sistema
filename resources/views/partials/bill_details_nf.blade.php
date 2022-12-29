@isset($req)
	@php
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=	["Folio", "Título"];
		$body	=
		[
			[
				"content"	=>	["label"	=>	$req->folio],
			],
			[
				"content"	=>	["label"	=>	$req->income()->exists() ? isset($req->income->first()->title) ? $req->income->first()->title : 'Sin Título' : 'Sin Título'],
			],
		];
		$modelBody[]	=	$body;
	@endphp
	@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
@endisset
@php
	$modelHead	=	[];
	$body		=	[];
	$modelBody	=	[];
	$modelHead	=	["Empresa", "Cliente"];
	$body	=
	[
		[
			"content"	=>
			[
				[
					"kind"	=>	"components.labels.label",
					"label"	=>	$bill->rfc,
					"classEx"	=>	"font-bold"
				],
				["label"	=>	$bill->businessName]
			],
		],
		[
			"content"	=>
			[
				[
					"kind"	=>	"components.labels.label",
					"label"	=>	$bill->clientRfc,
					"classEx"	=>	"font-bold"
				],
				["label"	=>	$bill->clientBusinessName]
			],
		]
	];
	$modelBody[]	=	$body;
@endphp
@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
@php
	$modelTable	=
	[
		"Forma de pago"			=>	$bill->cfdiPaymentWay->description,
		"Folio"					=>	$bill->folio,
		"Método de pago"		=>	$bill->cfdiPaymentMethod->description,
		"Condiciones de pago"	=>	$bill->conditions,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent
@php
	$modelHead	=	[];
	$body		=	[];
	$modelBody	=	[];
	foreach($bill->billDetail as $d)
	{
		$modelHead	=	["Cantidad", "Valor unitario", "Importe", "Descuento"];
		$body	=
		[
			[
				"content"	=>	["label"	=>	$d->quantity],
			],
			[
				"content"	=>	["label"	=>	$d->value],
			],
			[
				"content"	=>	["label"	=>	$d->amount],
			],
			[
				"content"	=>	["label"	=>	$d->discount],
			],
		];
		$modelBody[]	=	$body;
	}
@endphp
@component('components.tables.alwaysVisibleTable', ["modelHead" => $modelHead, "modelBody" => $modelBody])@endcomponent
@php
	$modelTable	=
	[
		["label"	=>	"Subtotal:",	"attributeExInput"	=>	"value=\"".$bill->subtotal."\""],
		["label"	=>	"Descuento:",	"attributeExInput"	=>	"value=\"".$bill->discount."\""],
		["label"	=>	"Total:",		"attributeExInput"	=>	"value=\"".$bill->total."\""],
	];
@endphp
@component('components.templates.outputs.form-details', ["modelTable" => $modelTable])
@endcomponent

		{{-- @@ -----------------	Original	----------------- --}}


{{-- @isset($req)
<div class="table-responsive">
	<table class="table">
		<thead class="thead-dark">
			<tr>
				<th>Folio</th>
				<th>Título</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>{{$req->folio}}</b></td>
				<td><b>{{ $req->income()->exists() ? isset($req->income->first()->title) ? $req->income->first()->title : 'Sin Título' : 'Sin Título' }}</b></td>
			</tr>
		</tbody>
	</table>
</div>
<p><br></p>		
@endisset 
<div class="table-responsive">
	<table class="table">
		<thead class="thead-dark">
			<tr>
				<th>Empresa</th>
				<th>Cliente</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><b>{{$bill->rfc}}</b> {{$bill->businessName}}</td>
				<td><b>{{$bill->clientRfc}}</b> {{$bill->clientBusinessName}}</td>
			</tr>
		</tbody>
	</table>
</div>
<p><br></p>
<div class="table-responsive">
	<table class="table">
		<tbody>
			<tr>
				<th class="table-dark">Forma de pago:</th>
				<td>{{$bill->cfdiPaymentWay->description}}</td>
				<th class="table-dark">Folio:</th>
				<td>{{$bill->folio}}</td>
			</tr>
			<tr>
				<th class="table-dark">Método de pago:</th>
				<td>{{$bill->cfdiPaymentMethod->description}}</td>
				<th class="table-dark">Condiciones de pago:</th>
				<td>{{$bill->conditions}}</td>
			</tr>
		</tbody>
	</table>
</div>
<p><br></p>
<div class="table-responsive">
	@foreach($bill->billDetail as $d)
		<table class="table table-borderless">
			<thead class="thead-dark">
				<tr>
					<th><strong>Cantidad</strong></th>
					<th><strong>Valor unitario</strong></th>
					<th><strong>Importe</strong></th>
					<th><strong>Descuento</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{{$d->quantity}}</td>
					<td>{{$d->value}}</td>
					<td>{{$d->amount}}</td>
					<td>{{$d->discount}}</td>
				</tr>
			</tbody>
		</table>
	@endforeach
</div>
<p><br></p>
<div class="table-responsive">
	<table class="table table-borderless">
		<tbody>
			<tr>
				<th class="text-right">Subtotal:</th>
				<td>{{$bill->subtotal}}</td>
			</tr>
			<tr>
				<th class="text-right">Descuento:</th>
				<td>{{$bill->discount}}</td>
			</tr>
			<tr>
				<th class="text-right">Total:</th>
				<td>{{$bill->total}}</td>
			</tr>
		</tbody>
	</table>
</div>


--}}