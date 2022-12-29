@component('components.labels.title-divisor',["classExContainer" => "mb-6"]) DATOS DEL SOLICITANTE @endcomponent
@php
    $taxes 	  = 0;
    $request  = App\RequestModel::find($request->folio);

    foreach($request->loan as $loan)
    {
        $modelTable	=
        [
            "Forma de pago"	=>	isset($loan->paymentMethod->method) ? $loan->paymentMethod->method : '---',
            "Referencia"	=>	$loan->reference == "" ? "---": htmlentities($loan->reference),
            "Importe"		=>	"$ ".number_format($loan->amount,2),
        ];
        
        foreach(App\Employee::join('banks','employees.idBanks','banks.idBanks')->where('employees.idUsers',$loan->idUsers)->get() as $bank)
        {	
            if($loan->idEmployee == $bank->idEmployee)
            {
                $modelTable['Banco']             = $bank->description;
                $modelTable['Número de tarjeta'] = $bank->cardNumber;
                $modelTable['CLABE']             = $bank->clabe   == "" ? "---": $bank->clabe;
                $modelTable['Número de cuenta']  = $bank->account == "" ? "---": $bank->account;
            }
        }
    }
@endphp
@component('components.templates.outputs.table-detail-single', ["modelTable" => $modelTable])
    @slot('classEx')
        employee-details
    @endslot
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