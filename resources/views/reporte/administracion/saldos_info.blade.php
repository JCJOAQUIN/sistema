<div class="pb-6">
	@component('components.labels.title-divisor') {{ $user->fullName() }} @endcomponent
	@php
		$modelTable =
		[
			"Reembolsos autorizados"				=> "$".number_format($result->refund_total,2),
			"Reembolsos pagados"					=> "$".number_format($result->refund_paid,2),
			"Reembolsos por pagar"					=> "$".number_format($result->refund_unpaid,2),
			"Folios con pagos mayores (Reembolso)"	=> $result->refund_paid_more,
			"Recursos autorizados"					=> "$".number_format($result->resource_total,2),
			"Recursos pagados"						=> "$".number_format($result->resource_paid,2),
			"Recursos por pagar"					=> "$".number_format($result->resource_unpaid,2),
			"Recursos por comprobar"				=> "$".number_format($result->to_check,2),
			"Folios por comprobar"					=> $result->folios,
			"Comprobaciones autorizadas"			=> "$".number_format($result->expenses_total,2),
			"Comprobaciones por reembolsar"			=> "$".number_format($result->expenses_refund_to_pay,2),
			"Comprobaciones reembolsadas"			=> "$".number_format($result->expenses_refund_paid,2),
			"Comprobaciones por reintegrar"			=> "$".number_format($result->expenses_reinstate_to_pay,2),
			"Comprobaciones reintegradas"			=> "$".number_format($result->expenses_reinstate_paid,2),
			"Caja chica"							=> ($user->cash == 1 ? number_format($user->cash_amount,2) : "--")
		];
	@endphp
	@component("components.templates.outputs.table-detail-single", ["modelTable" => $modelTable]) 
	@endcomponent
</div>