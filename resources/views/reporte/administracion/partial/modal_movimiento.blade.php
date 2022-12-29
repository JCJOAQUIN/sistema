<div class="pb-6">
	@php
		$modelTable =
		[
			["Empresa",$movement->enterprise->name],
			["Clasificación del gasto",$movement->accounts->account.' - '.$movement->accounts->description.'('.$movement->accounts->content.')' ],
			["Tipo de movimiento",$movement->movementType ],
			["Descripción",htmlentities($movement->description) ],
			["Fecha",$movement->movementDate != "" ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$movement->movementDate)->format('d-m-Y') : '' ],
			["Comentarios",$movement->commentaries != "" ? htmlentities($movement->commentaries) : 'Sin comentarios' ],
			["Importe",$movement->amount != "" ? "$".number_format($movement->amount,2) : '']
		];
	@endphp
	@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "title" => "Detalles de movimiento"]) 
	@endcomponent  
</div>

