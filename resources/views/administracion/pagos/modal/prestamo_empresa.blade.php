<div class="sm:text-center text-left my-5">
	A continuación podrá verificar la información de la solicitud antes de continuar con el proceso:
</div>
@php
	$taxes 		= 0;
	$retentions = 0;
	$requestUser           = App\User::find($request->idRequest);
	$elaborateUser         = App\User::find($request->idElaborate);
	$requestAccountOrigin  = App\Account::find($request->loanEnterprise->first()->idAccAccOrigin);
	$requestAccountDestiny = App\Account::find($request->loanEnterprise->first()->idAccAccDestiny);
	$modelTable = 
	[
		["Folio:", $request->folio],
		["Título y fecha:", htmlentities($request->loanEnterprise->first()->title)." - ".Carbon\Carbon::createFromFormat('Y-m-d', $request->loanEnterprise->first()->datetitle)->format('d-m-Y')],
		["Fiscal:", $request->taxPayment == 1 ? "Si" : "No" ],
		["Solicitante:", $requestUser->fullname()],
		["Elaborado por:", $elaborateUser->fullname()],
		["Empresa Origen:", App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseOrigin)->name],
		["Clasificación del Gasto Origen:", $requestAccountOrigin->account." - ".$requestAccountOrigin->description." (".$requestAccountOrigin->content.")"],
		["Empresa Destino:", App\Enterprise::find($request->loanEnterprise->first()->idEnterpriseDestiny)->name],
		["Clasificación del Gasto Destino:", $requestAccountDestiny->account." - ".$requestAccountDestiny->description." (".$requestAccountDestiny->description.")"],
	];	
@endphp
@component("components.templates.outputs.table-detail", ["modelTable" => $modelTable, "classEx" => "mb-6"]) 
	@slot('title')
		Detalles de la Solicitud de {{ $request->requestkind->kind }}
	@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) CONDICIONES DE PAGO @endcomponent
@php
	$modelTable	=
	[
		"Tipo de moneda" 	=> $request->loanEnterprise->first()->currency,
		"Fecha de pago" 	=> Carbon\Carbon::createFromFormat('Y-m-d', $request->loanEnterprise->first()->paymentDate)->format('d-m-Y'),
		"Forma de pago"		=> $request->loanEnterprise->first()->paymentMethod->method,
		"Importe a pagar" 	=> "$ ".number_format($request->loanEnterprise->first()->amount,2),
	];
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
@slot('classEx')
	employee-details
@endslot
@endcomponent

@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) DOCUMENTOS @endcomponent
@if(count($request->loanEnterprise->first()->documentsLoan)>0)
	@php
		$modelHead =
        [
            [
                "label" => "Documento"
            ],
            [
                "label" => "Fecha"
            ],
        ];
        $modelBody = [];	
		foreach($request->loanEnterprise->first()->documentsLoan as $doc)
		{
			$modelBody[] = 
            [
                "classEx" => "tr",
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"        	=> "components.buttons.button",
							"buttonElement" => "a",
                            "attributeEx"	=> "target=\"_blank\" href=\"".url('docs/movements/'.$doc->path)."\"",
                            "variant" => "secondary",
							"label" => "Archivo",
                        ],
                    ],
                ],
                [
                    "classEx" => "td",
                    "content" =>
                    [
                        [
                            "kind"  => "components.labels.label",
                            "label" => Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $doc->date)->format('d-m-Y'),
                        ],
                    ],
                ],						
            ];
		}
	@endphp
	@component("components.tables.alwaysVisibleTable",[
		"modelHead" => $modelHead,
		"modelBody" => $modelBody,
		"variant" => "default"
		])
		@slot('classEx')
			table
		@endslot   
	@endcomponent
@else
	@component("components.labels.not-found",[
		"classEx"   => "my-6"
	])
	@endcomponent
@endif

<div class="mb-6">
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