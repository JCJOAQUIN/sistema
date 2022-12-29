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
            .acts
            {
                left		: 0px;
                position	: relative;
                right		: 0px;
                text-align	: center !important;
                top			: -15.8em; 
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
                top			: -5em; 
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
                margin-top:87px;
            }
            .subcategories td, th
            {
                border: 1px solid #000000;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="header" style="border-collapse: collapse;border-spacing: 25px;width: 90%;margin: 0 auto;">
                <label style="font-size:14px;">FORMATO DE AUDITORIAS EFECTIVAS</label>
                <table class="header" width="100%">
                    <tbody>
                        <tr>
                            <td class="logo" style="width: 22%;">
                                <img src="{{ url('images/pti.jpg') }}" style="width:200px;">
                            </td>
                            <td style="width: 22%;">
                                Fecha: {{$audit->date}}
                            </td>
                            <td style="width: 22%;">
                                Hora: {{$audit->created_at->format('H:i:s')}}
                            </td>
                            <td style="width: 22%;">
                                Folio: {{$audit->id}}
                            </td>
                            <td style="width: 12%;">
                                <img src="{{ url('images/sspa-pemex.png') }}" style="width:60px;">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="pdf-full">
                <div class="pdf-body">
                    <div class="block-info request-info sign-form">
                        <table width="100%" style="margin-left:10px;">
                            <body>
                                <tr>
                                    <td width="30%">Tipo de auditoria:</td>
                                    <td width="70%" colspan="2" rowspan="3" style="border: 1px solid #3a4041 !important;"></td>
                                </tr>
                                <tr>
                                    <td height="5%">{{$audit->projectData->proyectName}}</td>
                                </tr>
                                <tr>
                                    <td>Parte de la instalación:</td>
                                </tr>
                                <tr>
                                    <td>Ubicación de la instalación:</td>
                                    <td colspan="2" style="border: 1px solid #3a4041 !important; width:100%;">Contrato: {{$audit->contract }}</td>
                                </tr>
                            </body>
                        </table>
                        <br>
                        <div class="block-info request-info">
                            Compañía(s): {{$audit->contractorData->name}}
                        </div>
                    </div>
                </div>
            </div>
            
            <table width="100%">
                <tbody>
                    <tr>
                        <td>
                            <table width="90%" class="subcategories">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #3a4041 !important;" colspan="12">Registro de actos inseguros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #3a4041 !important;" rowspan="2" colspan="6"></td>
                                        <td colspan="3" style="border: 1px solid #3a4041 !important;text-align:center;">PEMEX</td>
                                        <td colspan="3" style="border: 1px solid #3a4041 !important;text-align:center;">COMPAÑÍA</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #3a4041 !important;">0.33</td>
                                        <td style="border: 1px solid #3a4041 !important;">1</td>
                                        <td style="border: 1px solid #3a4041 !important;">3</td>
                                        <td style="border: 1px solid #3a4041 !important;">0.33</td>
                                        <td style="border: 1px solid #3a4041 !important;">1</td>
                                        <td style="border: 1px solid #3a4041 !important;">3</td>
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
                                            <td colspan="12" style="border: 1px solid #3a4041 !important;">{{$categoryName->name}}</td>
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
                                                <td style="border: 1px solid #3a4041 !important;" colspan="6">{{$subcategory['name']}}</td>
                                                @if($audit->contractorData->id == 65)
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneThirdSubcategory}}</td>
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneSubcategory}}</td>
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessThreeSubcategory}}</td>
                                                @else
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                @endif
                                                @if($audit->contractorData->id == 65)
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                    <td style="border: 1px solid #3a4041 !important;">0</td>
                                                @else
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneThirdSubcategory}}</td>
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessOneSubcategory}}</td>
                                                    <td style="border: 1px solid #3a4041 !important;">{{$countDangerousnessThreeSubcategory}}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    <tr>
                                        <td colspan="6" style="border: 1px solid #3a4041 !important;">CASOS TOTALES</td>
                                        @if($audit->contractorData->id == 65)
                                            <td style="border: 1px solid #3a4041 !important;">{{$n1_3}}</td>
                                            <td style="border: 1px solid #3a4041 !important;">{{$n1}}</td>
                                            <td style="border: 1px solid #3a4041 !important;">{{$n3}}</td>
                                        @else
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                        @endif
                                        @if($audit->contractorData->id == 65)
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                            <td style="border: 1px solid #3a4041 !important;">0</td>
                                        @else
                                            <td style="border: 1px solid #3a4041 !important;">{{$n1_3}}</td>
                                            <td style="border: 1px solid #3a4041 !important;">{{$n1}}</td>
                                            <td style="border: 1px solid #3a4041 !important;">{{$n3}}</td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="vertical-align: top !important;margin-top:95px !important;">
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <thead>
                                    <tr>
                                        <th style="border-color: #000;" colspan="4">EVALUACIÓN DEL DESEMPEÑO EN SEGURIDAD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td width="25%">CASO</td>
                                        <td width="25%">ACTOS INSEGUROS</td>
                                        <td width="25%">FACTOR DE SEVERIDAD</td>
                                        <td width="25%">AI*FS</td>
                                    </tr>
                                    <tr>
                                        <td>POTENCIAL A LESION BAJO</td>
                                        <td>{{$audit->countDangerousnessOneThird()}}</td>
                                        <td>0.33</td>
                                        <td>{{$audit->countDangerousnessOneThird()*0.33}}</td>
                                    </tr>
                                    <tr>
                                        <td>POTENCIAL A LESION MEDIO</td>
                                        <td>{{$audit->countDangerousnessOne()}}</td>
                                        <td>1</td>
                                        <td>{{$audit->countDangerousnessOne()*1}}</td>
                                    </tr>
                                    <tr>
                                        <td>POTENCIAL A LESION ALTO</td>
                                        <td>{{$audit->countDangerousnessThree()}}</td>
                                        <td>3</td>
                                        <td>{{$audit->countDangerousnessThree()*3}}</td>
                                    </tr>
                                    <tr>
                                        <td>TOTALES</td>
                                        <td>{{$audit->countDangerousnessOneThird()+$audit->countDangerousnessOne()+$audit->countDangerousnessThree()}}</td>
                                        <td></td>
                                        <td>{{$audit->countDangerousnessOneThird()*0.33+$audit->countDangerousnessOne()*1+$audit->countDangerousnessThree()*3}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <br>
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <tbody>
                                    <tr>
                                        <th width="70%">NO. DE PERSONAS OBSERVADAS: </th>
                                        <th width="20%">{{$audit->people_involved}}</th>
                                    </tr>
                                </tbody>
                            </table>
                            <br>
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>SEGURA</th>
                                        <th>INSEGURA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>PEMEX</td>
                                        @if($audit->contractorData->id == 65)
                                            <td>{{$audit->people_involved - $audit->severity_factor}}</td>
                                            <td>{{$audit->severity_factor}}</td>
                                        @else
                                            <td>0</td>
                                            <td>0</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td>COMPANÍA</td>
                                        @if($audit->contractorData->id == 65)
                                            <td>0</td>
                                            <td>0</td>
                                        @else
                                            <td>{{$audit->people_involved - $audit->severity_factor}}</td>
                                            <td>{{$audit->severity_factor}}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td>TOTAL</td>
                                        <td>{{$audit->people_involved - $audit->severity_factor}}</td>
                                        <td>{{$audit->severity_factor}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <br>
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <tbody>
                                    <tr>
                                        <th width="70%" style="border: 1px solid #3a4041 !important;">NO. DE PERSONAS CONTACTADAS: </th>
                                        <th width="20%" style="border: 1px solid #3a4041 !important;">{{$audit->severity_factor}}</th>
                                    </tr>
                                </tbody>
                            </table>
                            <br>
                            <div class="block-info request-info">
                                PEMEX
                            </div>
                            <div class="block-info request-info">
                                COMPAÑÍA
                            </div>
                            <div class="block-info request-info">
                                TOTAL
                            </div>
                            <br>
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <tbody>
                                    <tr>
                                        <th width="70%" style="border: 1px solid #3a4041 !important;">Índice de Actos Inseguros (IAI):</th>
                                        <th width="20%" style="border: 1px solid #3a4041 !important;">{{$audit->iai}}</th>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="block-info request-info">
                                =(Suma de ( [AI1*FS1]+[Ain*FSn]+…+/ No. De personas observadas ) *100
                            </div>
                            <br>
                            <table width="90%" class="subcategories" style="margin-left:3px !important;margin-top:0px !important;margin-right:45px;">
                                <tbody>
                                    <tr>
                                        <th width="70%" style="border: 1px solid #3a4041 !important;">Índice de: Actos Seguros (IAS):</th>
                                        <th width="20%" style="border: 1px solid #3a4041 !important;">{{$audit->ias}}</th>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="block-info request-info">
                                =100-IA!
                            </div>
                            <br>
                            <div class="block-info request-info">
                                Desempeño en seguridad
                            </div>
                            <div class="block-info request-info">
                                Seguro mayor que 98%
                            </div>
                            <div class="block-info request-info">
                                Preventivo desde 95% hasta 98%
                            </div>
                            <div class="block-info request-info">
                                Inaceptable menor que 95%
                            </div>
                            <br>
                            <div class="block-info request-info">
                                Factor de severidad
                            </div>
                            <div class="block-info request-info">
                                0.33= Actos inseguros bajo potencial a lesion
                            </div>
                            <div class="block-info request-info">
                                1.0= Actos inseguros medio potencial a lesion
                            </div>
                            <div class="block-info request-info">
                                3.0= Actos inseguros alto potencial a lesion
                            </div>
                            <br>
                            <div style="width:90% !important;border: 1px solid #3a4041 !important;">
                                Observaciones: {{$audit->observations}}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>            
            <div class="pdf-full">
                <div class="pdf-body">
                    <div class="block-info request-info acts">
                        <table width="100%" class="subcategories" style="margin-left:0px; !important">
                            <tbody>
                                <tr>
                                    <td height="2%" width="100%" style="border: 1px solid #3a4041 !important;">
                                        <label style="font-size:14px;text-align	: center;">ACTOS INSEGUROS</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="5%" width="100%" style="border: 1px solid #3a4041 !important;">{{$audit->observations}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <br>
            <div class="pdf-full scnd" style="text-align: left !important;">
                <div class="pdf-body">
                    <div class="block-info request-info">
                        <table width="100%">
                            <tbody>
                                <tr>
                                    <td style="vertical-align: top !important;">
                                        <table width="100%" class="subcategories" style="margin-left:0px; !important">
                                            <tbody>
                                                <tr>
                                                    <td width="80%">TOTAL DE ACTOS INSEGUROS</td>
                                                    <td width="20%">{{count($audit->unsafeAct)+count($audit->unsafeConditions)+count($audit->unsafePractices)}}</td>
                                                </tr>
                                                <tr>
                                                    <td width="80%">TOTAL DE RECOMENDACIONES</td>
                                                    <td width="20%">{{count($audit->unsafeAct)+count($audit->unsafeConditions)+count($audit->unsafePractices)}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>
                                        <table width="100%" class="subcategories">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align: center !important;">ACTOS INSEGUROS CORREGIDOS</td>
                                                    <td style="text-align: center !important;">NUM</td>
                                                </tr>
                                                @foreach(App\AuditCategory::all() as $category)
                                                    @php
                                                        $countDangerousnessOneThirdSubcategory = $audit->leftJoin('unsafe_acts', 'audits.id', 'unsafe_acts.audit_id')->where('audits.id', $audit->id)->where('project_id', $audit->project_id)->where('unsafe_acts.category_id', $category->id)->count();
                                                        $countDangerousnessOneSubcategory = $audit->leftJoin('unsafe_practices', 'audits.id', 'unsafe_practices.audit_id')->where('audits.id', $audit->id)->where('project_id', $audit->project_id)->where('unsafe_practices.category_id', $category->id)->count();
                                                        $countDangerousnessThreeSubcategory = $audit->leftJoin('unsafe_conditions', 'audits.id', 'unsafe_conditions.audit_id')->where('audits.id', $audit->id)->where('project_id', $audit->project_id)->where('unsafe_conditions.category_id', $category->id)->count();
                                                    @endphp
                                                    <tr>
                                                        <td>{{$category->name}}</td>
                                                        <td>{{$countDangerousnessOneThirdSubcategory+$countDangerousnessOneSubcategory+$countDangerousnessThreeSubcategory}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
               
                    <div class="block-info request-info">
                        <table width="100%" class="subcategories" style="margin-top:25px !important;text-align: center !important;margin-left:0px !important;">
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
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <div class="block-info request-info">
                        <label style="font-size:11px; font-weight: bold;">Participantes del Gupo Auditor:</label>
                    </div>
                    <br><br><br><br>
                    <div class="block-info request-info">
                        <table width="100%" style="text-align: center;">
                            <tbody>
                                <tr>
                                    <td style="border-top: 1px solid #000000;">Nombre y Firma</td>
                                    <td></td>
                                    <td style="border-top: 1px solid #000000;">Nombre y Firma</td>
                                    <td></td>
                                    <td style="border-top: 1px solid #000000;">Nombre y Firma</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <div class="block-info request-info">
                        <label style="font-size:11px; font-weight: bold;">Enterado:</label>
                    </div>
                    <br><br><br><br>
                    <div class="block-info request-info">
                        <table width="100%" style="text-align: center;">
                            <tbody>
                                <tr>
                                    <td width="30%" style="border-top: 1px solid #000000;">Nombre y Firma</td>
                                    <td width="16.125%"></td>
                                    <td width="16.125%"></td>
                                    <td width="16.125%"></td>
                                    <td width="16.125%"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
