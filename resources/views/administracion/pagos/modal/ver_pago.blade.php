@php
	if($payment->documentsPayments()->exists())
	{
		$button	=	"";
		foreach($payment->documentsPayments as $doc)
		{
			$button	.=	html_entity_decode((String)view("components.buttons.button",
			[
				"variant" 		=> "dark-red",
				"buttonElement"	=>	"a",
				"label" 		=> "PDF",
				"attributeEx"	=> "type=button target=\"_blank\" title=\"".$doc->path."\""." href=\"".asset('/docs/payments/'.$doc->path)."\"",
			]));
		}
	}
	else
	{
		$button	=	html_entity_decode((String)view("components.labels.label",
			[
				"label" 		=> "Sin documentos"
			]));
	}
	$modelTable	=
	[
		['Folio:',							isset($payment->idFolio) && $payment->idFolio!="" ? $payment->idFolio : "---"],
		['Empresa:',						isset($payment->enterprise->name) && $payment->enterprise->name!="" ? $payment->enterprise->name : "---"],
		['Clasificación de Gasto:',			$payment->accountData()->exists() && $payment->accountData->account!="" ? $payment->accountData->account.' - '.$payment->accountData->description : '--'],
		['Tasa de Cambio:',					isset($payment->exchange_rate) && $payment->exchange_rate!="" ? $payment->exchange_rate : "---"],
		['Descripción de Tasa de Cambio:',	isset($payment->exchange_rate_description) && $payment->exchange_rate_description!="" ? $payment->exchange_rate_description : "---"],
		['Subtotal:',						isset($payment->subtotal) && $payment->subtotal!="" ? '$'.number_format($payment->subtotal,2) : "---"],
		['Impuestos Adicionales:',			isset($payment->tax_real) && $payment->tax_real!="" ? '$'.number_format($payment->tax_real,2) : "---"],
		['Retenciones:',					isset($payment->retention) && $payment->retention!="" ? '$'.number_format($payment->retention,2) : "---"],
		['IVA:',							isset($payment->iva) && $payment->iva!="" ? '$'.number_format($payment->iva,2) : "---"],
		['Total:',							isset($payment->amount) && $payment->amount!="" ? '$'.number_format($payment->amount,2) : "---"],
		['Comentarios:',					$payment->commentaries != "" ? $payment->commentaries : 'Sin Comentarios'],
		['Documentos:',						$button]
	]
@endphp
@component('components.templates.outputs.table-detail', ['modelTable' => $modelTable])
	@slot('title')
		Detalles del pago
	@endslot
@endcomponent