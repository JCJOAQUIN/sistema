<div class="pb-6">
	@component('components.labels.title-divisor') Detalles de pago @endcomponent
	@php
		$modelTable =
		[
			["Folio",$payment->idFolio],
			["Tipo de solicitud",$payment->request->requestkind->kind],
			["Empresa",$payment->enterprise->name],
			["Clasificación del gasto",$payment->accounts->fullClasificacionName()],
			["Fecha",$payment->paymentDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$payment->paymentDate)->format('d-m-Y') : ''],
			["Comentarios",$payment->commentaries != "" ? $payment->commentaries : 'Sin comentarios'],
			["Importe",$payment->amount != "" ? "$".number_format($payment->amount,2) : '']
		];
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de movimiento"]) 
	@endcomponent  
</div>
@component('components.labels.title-divisor') 
	DOCUMENTOS
	@slot('classExContainer')
		pb-4
	@endslot
@endcomponent
@php
	$body 	   = [];
	$no_result = true;
	$modelBody = [];
	$modelHead = ["Archivo", "Fecha"];
	if($payment->documentsPayments()->exists())
	{
		foreach($payment->documentsPayments as $doc)
		{
			$body = 
			[
				[
					"content" => 
					[
						[
							"kind"          => "components.buttons.button",
							"variant"       => "dark-red",
							"label"         => "PDF",
							"buttonElement" => "a",
							"attributeEx"   => "target = \"_blank\" href = \"".url('docs/payments/'.$doc->path)."\""
						]
					]
				],
				[ 
					"content" => 
					[
						[
							"label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$doc->created)->format('d-m-Y'),
						]
					]
				]
			];
			array_push($modelBody, $body);
		}
		$no_result = false;
	}
@endphp
<div class="pb-6">
	@if($no_result)
		@component("components.labels.not-found")
			@slot("slot")
				No hay documentos
			@endslot
		@endcomponent
	@else
		@component('components.tables.alwaysVisibleTable',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody,
			"themeBody" 			=> "striped"
		])
		@endcomponent
	@endif
</div>

