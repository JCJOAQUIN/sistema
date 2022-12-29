<!DOCTYPE html>
<html>
    <head>
        <style>
        
            @page 
            {
                margin	: 8.8em 0 0 0 !important;
            }
            body
            {
                background	: white;
                font-size	: 9.8px;
                font-family	: Arial, Helvetica, sans-serif
                position	: relative !important;
            }
            .header
            {
                left		: 15px;
                position	: fixed;
                right		: 0px;
                text-align	: center;
                top			: -10.8em;
            }
            .title-doc
            {
                left		: 0px;
                position	: fixed;
                right		: 0px;
                text-align	: center !important;
                top			: -6.6em; 
            }
            .acts
            {
                left		: 0px;
                position	: fixed;
                right		: 0px;
                text-align	: center !important;
                top			: -50.8em; 
            }
            .scnd
            {
                left		: 0px;
                position	: fixed;
                right		: 0px;
                text-align	: center;
                top			: -5.8em;  
            }
            .sign-form
            {
                left		: 20px;
                position	: absolute;
                right		: 0px;
                text-align	: left;
                top			: -5.3em; 
            }
            .header .logo
            {
                margin			: 0 auto;
                margin-bottom	: 5px;
                padding			: 5px;
                vertical-align	: top;
                width			: 100px;
            }
            .header .logo img
            {
                width: 100%;
            }
            .request-info
            {
                margin			: 0 auto;
                width			: 90%;
            }
            .pdf-table-center-header
            {
                background: #c6c6c6; color: #000;font-size: 1em; font-weight: 400; padding: 0.2.5em 0; text-align: center;
            }
            .block-info
            {
                page-break-inside	: avoid;
            }
            .subcategories
            {
                border-collapse: collapse;
                width: 100%;
                margin-left:25px;
                margin-top:30px;
            }
            .subcategories td, th
            {
                text-align: left !important;
                border: 1px solid #000000;
            }
            .circle-yellow
            {
                border-radius: 100%;
                width: 20px;
                height: 20px;
                background: yellow;
                margin:auto !important;
            }
            .circle-green
            {
                border-radius: 100% !important;
                width: 20px !important;
                height: 20px !important;
                background: green;
                margin:auto !important;
            }
            .circle-red
            {
                border-radius: 100%;
                width: 20px !important;
                height: 20px !important;
                background: red;
                margin:auto !important;
            }
            .mt-10
            {
                margin-top:10px !important;
            }
            .mt-20
            {
                margin-top:20px !important;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="header">
                <label style="font-size:16px;">PROYECTA INDUSTRIAL DE MEXICO</label>
                <img src="{{ url('images/proyecta.png') }}" style="heigth:50px;width:100px; position:absolute;top: -2em;right:20px;">
            </div>
        </main>
        <div class="title-doc" style="text-align:center !important;">
            <label style="font-size:16px;">INFORME DE AUDITORIA DE COMPORTAMIENTO Y ACTITUD</label>
        </div>
        <div class="pdf-full">
            <div class="pdf-body">
                <div class="block-info request-info sign-form" style="margin-left:50px !important;width:635px !important;">
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td width="75%"></td>
                                <td width="25%" style="vertical-align: top !important;">
                                    <div>
                                        No. de Auditoría:  {{$audit->id}}
                                        <hr>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="75%" style="vertical-align: top !important;">Instalación: <hr></td>
                                <td width="25%" style="vertical-align: top !important;">
                                    <div>
                                        Fecha:  {{$audit->date}}
                                        <hr>
                                    </div> 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            <div>
                <div class="block-info request-info" class="acts">
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td>
                                    <table width="100%" class="subcategories">
                                        <tbody>
                                            <tr>
                                                <td style="border: 1px solid #3a4041 !important;">OBSERVACIONES PREVENTIVAS DE SEGURIDAD</td>
                                                <td colspan="3" style="border: 1px solid #3a4041 !important;text-align:center;">FACTOR DE SEVERIDAD</td>
                                            </tr>
                                            @php
                                                $n1_3= $n1 = $n3 = 0;
                                                $total_persons = $audit->people_involved;
                                            @endphp
                                            @foreach($subcategories as $subcategoryData)
                                                @php
                                                    $categoryName = App\AuditCategory::find($subcategoryData[0]->audit_category_id);
                                                @endphp
                                                <tr>
                                                    <td style="border: 2px solid #000000 !important;">{{$categoryName->name}}</td>
                                                    <td style="border: 2px solid #000000 !important;">0.33</td>
                                                    <td style="border: 2px solid #000000 !important;">1</td>
                                                    <td style="border: 2px solid #000000 !important;">3</td>
                                                </tr>
                                                
                                                @foreach($subcategoryData as $subcategory)
                                                    @php
                                                        $countDangerousnessOneThirdSubcategory 	= $audit->countDangerousnessOneThirdSubcategory($subcategory->id);
                                                        $countDangerousnessOneSubcategory 		= $audit->countDangerousnessOneSubcategory($subcategory->id);
                                                        $countDangerousnessThreeSubcategory 	= $audit->countDangerousnessThreeSubcategory($subcategory->id);

                                                        $n1_3	+= $countDangerousnessOneThirdSubcategory;
                                                        $n1		+= $countDangerousnessOneSubcategory;
                                                        $n3		+= $countDangerousnessThreeSubcategory;

                                                        $total = $countDangerousnessOneThirdSubcategory+$countDangerousnessOneSubcategory+$countDangerousnessThreeSubcategory;
                                                    @endphp
                                                    <tr>
                                                        <td style="border: 1px solid #3a4041 !important;">{{$subcategory['name']}}</td>
                                                        <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneThirdSubcategory}}</td>
                                                        <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneSubcategory}}</td>
                                                        <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessThreeSubcategory}}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            <tr>
                                                <td style="border: 1px solid #3a4041 !important;">CASOS TOTALES</td>
                                                <td style="border: 1px solid #3a4041 !important;">{{$n1_3}}</td>
                                                <td style="border: 1px solid #3a4041 !important;">{{$n1}}</td>
                                                <td style="border: 1px solid #3a4041 !important;">{{$n3}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="vertical-align: top !important;margin-top:35px !important;">
                                    <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                        <thead>
                                            <tr>
                                                <th style="border-color: #000;" colspan="4">EVALUACIÓN DEL DESEMPEÑO EN SEGURIDAD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td width="25%">CASO</td>
                                                <td width="25%">No. de Casos (b)</td>
                                                <td width="25%">FACTOR DE SEVERIDAD</td>
                                                <td width="25%">No de Casos X F.S. (a)</td>
                                            </tr>
                                            <tr>
                                                <td>Personal trabajando en forma segura</td>
                                                <td>{{$audit->people_involved - $audit->severity_factor}}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>Personal con actos inseguros con riesgo bajo</td>
                                                <td>{{$audit->countDangerousnessOneThird()}}</td>
                                                <td>0.33</td>
                                                <td>{{$audit->countDangerousnessOneThird()*0.33}}</td>
                                            </tr>
                                            <tr>
                                                <td>Personal con actos inseguros con riesgo medio</td>
                                                <td>{{$audit->countDangerousnessOne()}}</td>
                                                <td>1</td>
                                                <td>{{$audit->countDangerousnessOne()*1}}</td>
                                            </tr>
                                            <tr>
                                                <td>Personal con actos inseguros con riesgo alto</td>
                                                <td>{{$audit->countDangerousnessThree()}}</td>
                                                <td>3</td>
                                                <td>{{$audit->countDangerousnessThree()*3}}</td>
                                            </tr>
                                            <tr>
                                                <td>Totales</td>
                                                <td>{{($audit->people_involved - $audit->severity_factor) + $audit->countDangerousnessOneThird()+$audit->countDangerousnessOne()+$audit->countDangerousnessThree()}}</td>
                                                <td>4.33</td>
                                                <td>{{($audit->countDangerousnessOneThird()*0.33) + ($audit->countDangerousnessOne()*1) + ($audit->countDangerousnessThree()*3)}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                        <tbody>
                                            <tr>
                                                <th width="70%">Indice de actos inseguros(IAI) = (aT/bT) * 100</th>
                                                <th width="20%" style="border: 1px solid #3a4041 !important;">{{$audit->iai}}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                        <tbody>
                                            <tr>
                                                <th width="70%" style="border: 1px solid #3a4041 !important;">Indice de actos seguros (IAS) = 100 - IAI</th>
                                                <th width="20%" style="border: 1px solid #3a4041 !important;">{{$audit->ias}}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <div width="100%" style="text-align:center !important;">
                                        @if($audit->ias >= 98.01)
                                            <div class="circle-green"></div>
                                            Excelente
                                        @elseif($audit->ias >= 95.01 && $audit->ias <= 98)
                                            <div class="circle-yellow"></div>
                                            Bueno o aceptable
                                        @else
                                            <div class="circle-red"></div>
                                            Inaceptable
                                        @endif
                                    </div>
                                    
                                    <div class="block-info request-info mt-20" style="text-align:left !important;">
                                        Excelente mayor o igual a 98.01%
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        Bueno o aceptable desde 95.01 hasta 98%
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        Inaceptable menor o igual a 95
                                    </div>

                                    <div class="block-info request-info mt-20" style="text-align:left !important;">
                                        0.33= Violación a una regla, norma o procedimiento, sin potencial de lesión.
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        1.0= Potencial de lesión menor y baja posibilidad de que ocurra un accidente
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        Preventivo desde 95% hasta 98%
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        Inaceptable menor que 95%
                                    </div>
                                    <br>
                                    <div class="block-info request-info mt-20" style="text-align:left !important;">
                                        Factor de severidad
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        0.33= Actos inseguros bajo potencial a lesion
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        1.0= Actos inseguros medio potencial a lesion
                                    </div>
                                    <div class="block-info request-info mt-10" style="text-align:left !important;">
                                        3.0= Potencial de lesión mayor y alta posibilidad de que ocurra un accidente
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="block-info request-info" style="text-align:center !important;">
                <label style="width:100% !important;font-size:18px !important;">REPORTE DE ACTOS INSEGUROS Y OBSERVACIONES</label>
                <table width="100%" class="subcategories" style="margin-top:0px !important;text-align: center !important;margin-left:0px !important;">
                    <tbody>
                        <tr>
                            <td width="20%">NUM</td>
                            <td width="20%">Recomendaciones</td>
                            <td width="20%">Responsable</td>
                            <td width="20%">Fecha de compromiso</td>
                            <td width="20%">Acción correctiva inmediata</td>
                        </tr>
                        @php
                            $num = 1;
                        @endphp
                        @foreach($audit->unsafeAct as $acts)
                            <tr>
                                <td height="5%">{{$num}}</td>
                                <td height="5%">{{$acts->prevent}}</td>
                                <td height="5%">{{$acts->responsable}}</td>
                                <td height="5%">{{$acts->fv}}</td>
                                <td height="5%">{{$acts->action}}</td>
                            </tr>
                            @php
                                $num++;
                            @endphp
                        @endforeach
                        @foreach($audit->unsafePractices as $acts)
                            <tr>
                                <td height="5%">{{$num}}</td>
                                <td height="5%">{{$acts->prevent}}</td>
                                <td height="5%">{{$acts->responsable}}</td>
                                <td height="5%">{{$acts->fv}}</td>
                                <td height="5%">{{$acts->action}}</td>
                            </tr>
                            @php
                                $num++;
                            @endphp
                        @endforeach
                        @foreach($audit->unsafeConditions as $acts)
                            <tr>
                                <td height="5%">{{$num}}</td>
                                <td height="5%">{{$acts->prevent}}</td>
                                <td height="5%">{{$acts->responsable}}</td>
                                <td height="5%">{{$acts->fv}}</td>
                                <td height="5%">{{$acts->action}}</td>
                            </tr>
                            @php
                                $num++;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="5">Observaciones: {{ $audit->observations }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </body>
    </div>
</html>
