<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>
@php
	$requestUser = App\User::find($request->idRequest);
	$elaborateUser = App\User::find($request->idElaborate);
	$requestAccount = App\Account::find($request->account);
	$modelTable =
	[
		["Folio:", $request->folio],
		["Título y fecha:", htmlentities($request->finance->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->finance->datetitle)->format('d-m-Y')],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No" ],
		["Solicitante:", $request->requestUser->fullname()],
		["Elaborado por:", $request->elaborateUser->fullname()],
		["Empresa:", $request->requestEnterprise->name],
		["Dirección:", $request->requestDirection->name],
		["Departamento:", $request->requestDepartment->name],
		["Clasificación del gasto:", $request->accounts->account." - ".$request->accounts->description." (".$request->accounts->content.")"],
		["Proyecto:", isset(App\Project::find($request->idProject)->proyectName) ? App\Project::find($request->idProject)->proyectName : 'No se selccionó proyecto'],
	];
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"])
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "my-6"]) DATOS DEL GASTO FINANCIERO @endcomponent
@php
	$modelTable	=
	[
		"Tipo"           => $request->finance->kind,
		"Fecha de Pago"  => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $request->PaymentDate)->format('d-m-Y'),
		"Método de Pago" => $request->finance->paymentMethod,
		"Banco"	         => $request->finance->banks->description,
		"Cuenta"         => $request->finance->bankAccount()->exists() ? $request->finance->bankAccount->alias.' - '.$request->finance->bankAccount->account  : '---',
		"Tarjeta"        => $request->finance->creditCard()->exists() ? $request->finance->creditCard->alias.' - '.$request->finance->creditCard->credit_card : '---',
		"Moneda"         => $request->finance->currency,
		"Notas"          => $request->finance->note != "" ? htmlentities($request->finance->note) : "---",
		"Semana"         => $request->finance->week,
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
	@slot('classEx')
		employee-details
	@endslot
@endcomponent
@php
    $modelTable = [];
    $modelTable =
    [
		["label" => "Subtotal: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($request->finance->subtotal,2), "classEx" => "my-2"]]],
		["label" => "IVA: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($request->finance->tax,2), "classEx" => "my-2"]]],
		["label" => "TOTAL: ", "inputsEx" => [["kind" => "components.labels.label",	"label" => "$ ".number_format($request->finance->amount,2), "classEx" => "my-2"]]],
    ];
@endphp
@component("components.templates.outputs.form-details",[
    "modelTable" => $modelTable,
    "attributeExComment" => "name=\"note\" placeholder=\"Ingrese la nota\" readonly=\"readonly\"",
	"textNotes" => ""
])
@endcomponent

<div class="my-6">
    <div class="text-center">
        @component("components.buttons.button",[
            "variant"		=> "success",
            "attributeEx" 	=> "type=\"button\" title=\"Ocultar\" data-dismiss=\"modal\"",
            "label"			=> "« Ocultar",
            "classEx"		=> "exit",
        ])  
        @endcomponent
    </div>
</div>