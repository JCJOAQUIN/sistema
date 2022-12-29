@php
	$taxes	=	$retentions	=	0;
	$modelTable				=
	[
		["Folio:",$request->folio],
		["Título y fecha:", htmlentities($request->movementsEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->datetitle)->format('d-m-Y')],
		["Fiscal:",$request->taxPayment == 1 ? "Si" : "No"],
		["Solicitante:",$request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
		["Elaborado por:",$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""],
		["Empresa Origen:",$request->movementsEnterprise->first()->enterpriseOrigin()->exists() ? $request->movementsEnterprise->first()->enterpriseOrigin->name : ""],
		["Clasificación del Gasto Origen:",$request->movementsEnterprise->first()->accountOrigin()->exists() ? $request->movementsEnterprise->first()->accountOrigin->fullClasificacionName() : ""],
		["Empresa Destino:",$request->movementsEnterprise->first()->enterpriseDestiny()->exists() ? $request->movementsEnterprise->first()->enterpriseDestiny->name : ""],
		["Clasificación del Gasto Destino:",$request->movementsEnterprise->first()->accountDestiny()->exists() ? $request->movementsEnterprise->first()->accountDestiny->fullClasificacionName() : ""],
	];
@endphp
@component('components.templates.outputs.table-detail', ["modelTable" => $modelTable])
	@slot('classEx')
		mt-4
	@endslot
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent
@component('components.labels.title-divisor', ["classEx" => "mt-12", "label" => "CONDICIONES DE PAGO"]) @endcomponent
@php
	$modelTable	=
	[
		"Tipo de moneda"	=>	$request->movementsEnterprise->first()->typeCurrency,
		"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->movementsEnterprise->first()->paymentDate)->format('d-m-Y'),
		"Forma de pago"		=>	$request->movementsEnterprise->first()->paymentMethod->method,
		"Importe a pagar"	=>	"$ ".number_format($request->movementsEnterprise->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent