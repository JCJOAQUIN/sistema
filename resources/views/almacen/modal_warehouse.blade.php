
	<div class="grid justify-items-center">
		@foreach ($warehouses as $w)
			@php
				$modelTable	=
				[
					"Número de lote"			=>	$w->lot->idlot,
					"Empresa"					=>	$w->lot->enterprise->name,
					"Cuenta"					=>	($w->lot->account ? $w->lot->accounts->account.' '.$w->lot->accounts->description.' ('.$w->lot->accounts->content.')' : ''),
					"Inversión Total"			=>	$w->lot->total,
					"Inversión en Artículos"	=>	$w->amount,
					"Fecha"						=>	$w->lot->date!="" ? Carbon\Carbon::createFromFormat('Y-m-d',$w->lot->date)->format('d-m-Y') : "---",
					"Concepto"					=>	htmlentities($w->cat_c->description),
					"Cantidad"					=>	$w->quantity,
					"Dañados"					=>	$w->damaged ? $w->damaged : 0,
					"Estado"					=>	[["kind" => "components.labels.label", "label" => $w->status == 1 ? "ACTIVO" : "AGOTADO", "classEx" => $w->status == 1 ? "text-green-600" : "text-red-600"]],
				];
			@endphp
			@component('components.templates.outputs.table-detail-single', ["classEx" => "", "modelTable" => $modelTable])@endcomponent
		@endforeach
		@php
			$visibility	=	($w->status == 0 || !$edit ) ? "hidden" : "";
		@endphp
		@component('components.buttons.button', ["variant" => "success", "buttonElement" => "a", "attributeEx" => "title=\"Editar\" href=\"".route("warehouse.edit",['id'=>$w->idwarehouse])."\"", "classEx" => "$visibility follow-btn", "label" => "EDITAR"]) @endcomponent
	</div>
	<div class="text-center">
		@component('components.buttons.button', ["variant" => "red", "attributeEx" => "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"", "classEx" => "exit-stationery text-center", "label" => "« Ocultar"]) @endcomponent
	</div>