@php
	$requestUser	=	App\User::find($request->idRequest);
	$elaborateUser	=	App\User::find($request->idElaborate);
	$accountOrigin	=	App\Account::find($request->loanEnterprise->first()->idAccAccOrigin);
	$requestAccount	=	App\Account::find($request->loanEnterprise->first()->idAccAccDestiny);
	$modelTable		=
	[
		["Folio:",$request->folio],
		["Título y fecha:", htmlentities($request->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->datetitle)->format('d-m-Y')],
		["Fiscal:",$request->taxPayment == 1 ? "Si" : "No"],
		["Solicitante:",$request->requestUser()->exists() ? $request->requestUser->fullName() : ""],
		["Elaborado por:",$request->elaborateUser()->exists() ? $request->elaborateUser->fullName() : ""],
		["Empresa Origen:",$request->loanEnterprise->first()->enterpriseOrigin()->exists() ? $request->loanEnterprise->first()->enterpriseOrigin->name : ""],
		["Clasificación del Gasto Origen:",$request->loanEnterprise->first()->accountOrigin()->exists() ? $request->loanEnterprise->first()->accountOrigin->fullClasificacionName() : ""],
		["Empresa Destino:",$request->loanEnterprise->first()->enterpriseDestiny()->exists() ? $request->loanEnterprise->first()->enterpriseDestiny->name : ""],
		["Clasificación del Gasto Destino:",$request->loanEnterprise->first()->accountDestiny()->exists() ? $request->loanEnterprise->first()->accountDestiny->fullClasificacionName() : ""],
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
		"Tipo de moneda"	=>	$request->loanEnterprise->first()->currency,
		"Fecha de pago"		=>	Carbon\Carbon::createFromFormat('Y-m-d',$request->loanEnterprise->first()->paymentDate)->format('d-m-Y'),
		"Forma de pago"		=>	$request->loanEnterprise->first()->paymentMethod->method,
		"Importe a pagar"	=>	"$ ".number_format($request->loanEnterprise->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])@endcomponent