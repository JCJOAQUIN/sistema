<center>
    <strong>DATOS DEL PROVEEDOR</strong>
</center>
<div class='divisor'>
    <div class='gray-divisor'></div>
    <div class='orange-divisor'></div>
    <div class='gray-divisor'></div>
</div>
<div>
    <table class='employee-details'>
        <tbody>
            <tr>
                <td><b>Razón Social:</b></td>
                <td><label>{{ $request->purchases->first()->provider->businessName }}</label></td>
            </tr>
            <tr>
                <td><b>RFC:</b></td>
                <td><label>{{ $request->purchases->first()->provider->rfc }}</label></td>
            </tr>
            <tr>
                <td><b>Teléfono:</b></td>
                <td><label>{{ $request->purchases->first()->provider->phone }}</label></td>
            </tr>
            <tr>
                <td><b>Calle:</b></td>
                <td><label>{{ $request->purchases->first()->provider->address }}</label></td>
            </tr>
            <tr>
                <td><b>Número:</b></td>
                <td><label>{{ $request->purchases->first()->provider->number }}</label></td>
            </tr>
            <tr>
                <td><b>Colonia:</b></td>
                <td><label>{{ $request->purchases->first()->provider->colony }}</label></td>
            </tr>
            <tr>
                <td><b>CP:</b></td>
                <td><label>{{ $request->purchases->first()->provider->postalCode }}</label></td>
            </tr>
            <tr>
                <td><b>Ciudad:</b></td>
                <td><label>{{ $request->purchases->first()->provider->city }}</label></td>
            </tr>
            <tr>
                <td><b>Estado:</b></td>
                <td><label>{{ App\State::find($request->purchases->first()->provider->state_idstate)->description }}</label></td>
            </tr>
            <tr>
                <td><b>Contacto:</b></td>
                <td><label>{{ $request->purchases->first()->provider->contact }}</label></td>
            </tr>
            <tr>
                <td><b>Beneficiario:</b></td>
                <td><label>{{ $request->purchases->first()->provider->beneficiary }}</label></td>
            </tr>
            <tr>
                <td><b>Otro</b></td>
                <td><label>{{ $request->purchases->first()->provider->commentaries }}</label></td>
            </tr>
        </tbody>
    </table>
    <div class='form-container'>
        <div class='table-responsive'>
            <table id='table2' class='table-no-bordered'>
                <thead>
                    <tr>
                        <th>Banco</th>
                        <th>Cuenta</th>
                        <th>Sucursal</th>
                        <th>Referencia</th>
                        <th>CLABE</th>
                        <th>Moneda</th>
                        <th>Convenio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>                            
                    @foreach($request->purchases->first()->provider->providerData->providerBank as $bank)
                    
                        @if($request->purchases->first()->provider_has_banks_id == $bank->id) 
                            @php
                                $class = "marktr";
                            @endphp
                        @else
                            @php
                                $class = "";
                            @endphp
                        @endif
                        <tr class="{{ $class }}">
                            <td>
                                {{ $bank->bank->description }}
                                <input type='hidden' class='providerBank' name='providerBank[]' value="{{ $bank->idProvider }}">
                                <input type='hidden' name='bank[]' value="{{ $bank->banks_idBank }}">
                            </td>
                            <td>
                                {{ $bank->account }}
                                <input type='hidden' name='account[]' value="{{ $bank->account }}">
                            </td>
                            <td>
                                {{ $bank->branch }}
                                <input type='hidden' name='branch_office[]' value="{{ $bank->branch }}">
                            </td>
                            <td>
                                {{ $bank->reference }}
                                <input type='hidden' name='reference[]' value="{{ $bank->reference }}">
                            </td>
                            <td>
                                {{ $bank->clabe }}
                                <input type='hidden' name='clabe[]' value="{{ $bank->clabe }}">
                            </td>
                            <td>
                                {{ $bank->currency }}
                                <input type='hidden' name='currency[]' value="{{ $bank->currency }}">
                            </td>
                            <td>
                                @if($bank->agreement=='')
                                    -------------------------
                                @else
                                    @php
                                        $bank->agreement;
                                    @endphp
                                @endif
                                <input type='hidden' name='agreement[]' value="{{ $bank->agreement }}">
                            </td>
                            <td>
                                <button class='delete-item' type='button'><span class='icon-x delete-span' style='display: none;'></span></button>
                            </td>                                                       
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<center>
    <strong>DATOS DEL PEDIDO</strong>
</center>
<div class='divisor'>
    <div class='gray-divisor'></div>
    <div class='orange-divisor'></div>
    <div class='gray-divisor'></div>
</div>
<div class='form-container'>
    <div class='table-responsive'>
        <table id='table' class='table-no-bordered'>
            <thead>
                <th>#</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Descripci&oacute;n</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Impuesto Adicional</th>
                <th>Retenciones</th>
                <th>Importe</th>
            </thead>
            <tbody id='body'>
                @php
                    $countConcept = 1;
                @endphp
                @foreach($request->purchases->first()->detailPurchase as $detail)
                    <tr>
                        <td>{{ $countConcept }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $detail->unit }}</td>
                        <td>{{ $detail->description }}</td>
                        <td>$ {{ number_format($detail->unitPrice,2) }}</td>
                        <td>$ {{ number_format($detail->subtotal,2) }}</td>
                        <td>$ {{ number_format($detail->tax,2) }}</td>
                        <td>
                            @php
                                $taxesConcept=0;
                                foreach($detail->taxes as $tax)
                                {
                                    $taxesConcept+=$tax->amount;
                                }
                            @endphp                                
                            ${{ number_format($taxesConcept,2) }}
                        </td>
                        <td>
                            @php
                                $retentionConcept=0;
                                foreach($detail->retentions as $ret)
                                {
                                    $retentionConcept+=$ret->amount;
                                }
                            @endphp                                  
                            $ {{ number_format($retentionConcept,2) }}
                        </td>
                        <td>$ {{ number_format($detail->amount,2) }}</td>
                        </tr>
                    @php
                        $countConcept++;
                    @endphp
                @endforeach
            </tbody>
        </table>
    </div>
    <br>
</div>
<div class='totales2'>
    <div class='totales'>
        <textarea name='note' class='input-text' placeholder='Nota' cols='80' readonly='readonly'>"{{ $request->purchases->first()->notes }}"</textarea>
    </div>
    <div class='totales' style='margin-left: 10px;'>
        <table>
            <tr>
                <td><label class='label-form'>Subtotal:</label></td>
                <td><input placeholder='0' readonly class='input-table' type='text' name='subtotal' value="$ {{ number_format($request->purchases->first()->subtotales,2,'.',',') }}"></td>
            </tr>
            <tr>
                <td><label class='label-form'>Impuesto Adicional:</label></td>
                <td>
                    @php
                        $taxes = 0;
                        foreach($request->purchases->first()->detailPurchase as $detail)
                        {
                            foreach($detail->taxes as $tax)
                            {
                                $taxes += $tax->amount;
                            }
                        }
                    @endphp
                    <input placeholder='0' readonly class='input-table' type='text' name='amountAA' value="$ {{ number_format($taxes,2) }}">
                </td>
            </tr>
            <tr>
                <td><label class='label-form'>Retenciones:</label></td>
                <td>
                    @php
                        $retentions = 0;
                        foreach($request->purchases->first()->detailPurchase as $detail)
                        {
                            foreach($detail->retentions as $ret)
                            {
                                $retentions += $ret->amount;
                            }
                        }
                    @endphp
                    <input placeholder='$0.00' readonly class='input-table' type='text' name='amountR' value="$ {{ number_format($retentions,2) }}">
                </td>
            </tr>
            <tr>
                <td><label class='label-form'>IVA: </label></td>
                <td><input placeholder='0' readonly class='input-table' type='text' name='totaliva' value="$ {{ number_format($request->purchases->first()->tax,2,'.',',') }}"></td>
            </tr>
            <tr>
                <td><label class='label-form'>TOTAL:</label></td>
                <td><input id='input-extrasmall' placeholder='0' readonly class='input-table' type='text' name='total' value="$ {{ number_format($request->purchases->first()->amount,2,'.',',') }}"></td>
            </tr>
        </table>
    </div> 
</div>
<br><br><br>
<center>
    <strong>CONDICIONES DE PAGO</strong>
</center>
<div class='divisor'>
    <div class='gray-divisor'></div>
    <div class='orange-divisor'></div>
    <div class='gray-divisor'></div>
</div>
<div>
    <table class='employee-details'>
        <tbody>
            <tr>
                <td><b>Referencia/Número de factura:</b></td>
                <td><label>{{ $request->purchases->first()->reference }}</label></td>
            </tr>
            <tr>
                <td><b>Tipo de moneda:</b></td>
                <td><label>{{ $request->purchases->first()->typeCurrency }}</label></td>
            </tr>
            <tr>
                <td><b>Fecha de pago:</b></td>
                <td><label>{{ date('d-m-Y',strtotime($request->PaymentDate)) }}</label></td>
            </tr>
            <tr>
                <td>
                    <b>Forma de pago:</b>
                </td>
                <td>
                    <label>{{ $request->purchases->first()->paymentMode }}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Estado  de factura:</b>
                </td>
                <td>
                    <label>{{ $request->purchases->first()->billStatus }}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Importe a pagar:</b>
                </td>
                <td>
                    <label>$ {{ number_format($request->purchases->first()->amount,2) }}</label>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<br><br><br>
<center>
    <strong>DOCUMENTOS DE LA SOLICITUD</strong>
</center>
<div class='divisor'>
    <div class='gray-divisor'></div>
    <div class='orange-divisor'></div>
    <div class='gray-divisor'></div>
</div>
<br><br><br>
<table class='table'>
    <thead class="thead-dark">
        <th>Tipo de Documento</th>
        <th>Archivo</th>
        <th>Fecha</th>
    </thead>
    <tbody>
        @if(count($request->purchases->first()->documents)>0)
            @foreach($request->purchases->first()->documents as $doc)
                <tr>
                    <td>{{ $doc->name }}</td>
                    <td>
                    <a target='_blank' href="{{ url('docs/purchase/'.$doc->path) }}" style='text-decoration: none; color: black;'>{{ $doc->path }}</a>
                    </td>
                    <td>{{ $doc->date }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3">
                NO HAY DOCUMENTOS
                </td>
            </tr>
        @endif
    </tbody>
</table>
@php
    $payments 		= App\Payment::where('idFolio',$request->folio)->get();
@endphp

@if(count($payments) > 0)
    <br><br>
    <center>
        <strong>HISTORIAL DE PAGOS</strong>
    </center>
    <div class='divisor'>
        <div class='gray-divisor'></div>
        <div class='orange-divisor'></div>
        <div class='gray-divisor'></div>
    </div>
    <table class='table-no-bordered'>
        <thead>
            <th width='25%'>Cuenta</th>
            <th width='25%'>Cantidad</th>
            <th width='25%'>Documento</th>
            <th width='25%'>Fecha</th>
        </thead>
        <tbody>
            @foreach($payments as $pay)
                <tr>    
                    <td>{{ $pay->accounts->account }} - {{ $pay->accounts->description }}</td>
                    <td>$ {{ number_format($pay->amount,2) }}</td>
                    <td>
                        @foreach($pay->documentsPayments as $doc)
                            <a href="{{ asset('docs/payments/'.$doc->path) }}" target='_blank' class='btn btn-red' title="{{ $doc->path }}">
                                <span class='icon-pdf'></span>
                            </a>
                        @endforeach
                    </td>
                    <td>{{ date('d-m-Y',strtotime($pay->paymentDate)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif