<!DOCTYPE html>
<html>
    <head>
        <style>
            @page
            {
				margin-top	: 3em !important;
				margin-bottom	: 3em !important;
			}
			body
			{
				background	: white;
				font-size	: 12px;
				position	: relative !important;
				font-family	: 'Helvetica',sans-serif;
			}
            {
				margin-top	: 2em !important;
				margin-bottom	: 3em !important;
			}
			header
			{
				position	: fixed;
				top			: -6.8em;
			}
			.header
			{
				border-spacing	: 10px;
				width: 100%;
			}
            .footer
			{
				margin-top: 1.5em;
				width: 100%;
                font-size	: 11px;
			}
            .header .logo
            {
                margin			: 0 auto;
                text-align		: left;
                vertical-align	: top;
                width			: 100%;
            }
            .header .logo img
            {
                width: 100%;
            }
            .header td
			{
				font-size: 1.1em;
                font-weight: 400;
                text-align: center;
			}
            .request-info-cell
            {
                border			: 3px solid #3a4041;
                border-collapse	: collapse;
                margin			: 0 auto;
                width			: 100%;
            }
            .request-info-cell th
            {
                border			: 2.5px solid #000000;
                border-collapse : separate;
                vertical-align  : middle;
                font-size: 0.8em;
            }
            .request-info-cell tbody td
            {
                border			: 1px solid #000000;
                padding			: 0.1em 0.1em;
                font-size       : 1em;
                font-weight     : 400;
                text-align: center;
            }
            .pdf-table-center-header
            {
                background: #c6c6c6;
                color: #000000;
                font-size: 1.2em;
                font-weight: 400;
                text-align: center;
            }
            .page-break
            {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <table class="header">
            <tbody>
                <tr>
                    <td class="logo" style="width: 10%;">
                        <img src="{{ url('images/pti.jpg') }}" style="width:150px;">
                    </td>
                    <td colspan="2" style="90%">
                        <strong>CONSORCIO IDINSA-PROYECTA</strong>
                        <label style="font-size: 10px"><br><strong>NOMBRE DEL CONTRATO: </strong> "INGENIERIA COMPLEMENTARIA Y CONSTRUCCION DE LOS SERVICIOS DE INTEGRACION DE LOS SISTEMAS ENTERRADOS SAE/RAE, DRENAJE PLUVIAL, DRENAJE ACEITOSO Y RED DEL SISTEMA CONTRAINCENDIO Y DEL HEAVY ROUTE DEL PAQUETE 6".</label>
                        <label style="font-size: 10px"><br><strong>CONTRATO: </strong></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="header">
            <tbody>
                <tr>
                    <td style="font-size: 12px; text-align: left; width: 40%;">
                        ÁREA: <u>{{ $preventive->area }}</u>
                    </td>
                    <td style="font-size: 17px; text-align: center; width: 30%;">
                        <strong>INSPECCIÓN PREVENTIVA DE RIESGOS</strong>
                    </td>
                    <td style="font-size: 12px; text-align: right; width: 30%;">
                        FECHA: <u>{{ $preventive->date }}</u>
                    </td>
                </tr>
            </tbody>
        </table>
        <main>
            <div class="pdf-full">
                <div class="pdf-body">
                    <div class="block-info" style="margin-bottom: 2%;">
                        <table class="request-info-cell">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>CONDICIÓN Y/O ACTO INSEGURO, ORDEN Y LIMPIEZA</th>
                                    <th>ACCIONES PREVENTIVAS Y/O CORRECTIVAS</th>
                                    <th>FECHA DE COMPROMISO</th>
                                    <th>FECHA DE CUMPLIMIENTO</th>
                                    <th>ÁREA</th>
                                    <th>NOMBRE DEL RESPONSABLE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($preventive->detailInspection as $key => $preven)
                                    <tr>
                                        <td>{{ $count = 1 + $key }}</td>
                                        <td>{{ $preven->condition }}</td>
                                        <td>{{ $preven->action }}</td>
                                        <td>{{ $preventive->date }}</td>
                                        <td>{{ $preven->dateend }}</td>
                                        <td>{{ $preventive->area }}</td>
                                        <td>{{ $preven->responsible }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="footer">
                            <tbody>
                                <tr>
                                    <td style="font-size: 12px; padding-left:3em">
                                        SUPERVISOR SSPA: <label style="font-size: 12px"><u>{{ $preventive->supervisor_name }}</u></label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>