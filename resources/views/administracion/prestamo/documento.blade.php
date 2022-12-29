@php 
	use Carbon\Carbon;
	$date = Carbon::now();

	class NumeroALetras
	{
	    private static $UNIDADES = [
	        '',
	        'UN ',
	        'DOS ',
	        'TRES ',
	        'CUATRO ',
	        'CINCO ',
	        'SEIS ',
	        'SIETE ',
	        'OCHO ',
	        'NUEVE ',
	        'DIEZ ',
	        'ONCE ',
	        'DOCE ',
	        'TRECE ',
	        'CATORCE ',
	        'QUINCE ',
	        'DIECISEIS ',
	        'DIECISIETE ',
	        'DIECIOCHO ',
	        'DIECINUEVE ',
	        'VEINTE '
	    ];
	    private static $DECENAS = [
	        'VENTI',
	        'TREINTA ',
	        'CUARENTA ',
	        'CINCUENTA ',
	        'SESENTA ',
	        'SETENTA ',
	        'OCHENTA ',
	        'NOVENTA ',
	        'CIEN '
	    ];
	    private static $CENTENAS = [
	        'CIENTO ',
	        'DOSCIENTOS ',
	        'TRESCIENTOS ',
	        'CUATROCIENTOS ',
	        'QUINIENTOS ',
	        'SEISCIENTOS ',
	        'SETECIENTOS ',
	        'OCHOCIENTOS ',
	        'NOVECIENTOS '
	    ];
	    public static function convertir($number, $moneda = '', $centimos = '', $forzarCentimos = false)
	    {
	        $converted 		= '';
	        $decimales 		= '';
	        if (($number < 0) || ($number > 999999999)) 
	        {
	            return 'No es posible convertir el número a letras';
	        }
	        $div_decimales 	= explode('.',$number);
	        if(count($div_decimales) > 1)
	        {
	            $number 		= $div_decimales[0];
	            $decNumberStr 	= (string) $div_decimales[1];
	            if(strlen($decNumberStr) == 2)
	            {
	                $decNumberStrFill 	= str_pad($decNumberStr, 9, '0', STR_PAD_LEFT);
	                $decCientos 		= substr($decNumberStrFill, 6);
	                $decimales 			= self::convertGroup($decCientos);
	            }
	        }
	        else if (count($div_decimales) == 1 && $forzarCentimos)
	        {
	            $decimales = 'CERO ';
	        }
	        $numberStr 		= (string) $number;
	        $numberStrFill 	= str_pad($numberStr, 9, '0', STR_PAD_LEFT);
	        $millones 		= substr($numberStrFill, 0, 3);
	        $miles 			= substr($numberStrFill, 3, 3);
	        $cientos 		= substr($numberStrFill, 6);
	        if (intval($millones) > 0) 
	        {
	            if ($millones == '001') 
	            {
	                $converted .= 'UN MILLON ';
	            } 
	            else if (intval($millones) > 0) 
	            {
	                $converted .= sprintf('%sMILLONES ', self::convertGroup($millones));
	            }
	        }
	        if (intval($miles) > 0) 
	        {
	            if ($miles == '001') 
	            {
	                $converted .= 'MIL ';
	            } 
	            else if (intval($miles) > 0) 
	            {
	                $converted .= sprintf('%sMIL ', self::convertGroup($miles));
	            }
	        }
	        if (intval($cientos) > 0) 
	        {
	            if ($cientos == '001') 
	            {
	                $converted .= 'UN ';
	            } 
	            else if (intval($cientos) > 0) 
	            {
	                $converted .= sprintf('%s ', self::convertGroup($cientos));
	            }
	        }
	        if(empty($decimales))
	        {
	            $valor_convertido = $converted . strtoupper($moneda);
	        } 
	        else 
	        {
	            $valor_convertido = $converted . strtoupper($moneda) . ' CON ' . $decimales . ' ' . strtoupper($centimos);
	        }
	        return $valor_convertido;
	    }
	    private static function convertGroup($n)
	    {
	        $output = '';
	        if ($n == '100') 
	        {
	            $output = "CIEN ";
	        } 
	        else if ($n[0] !== '0') 
	        {
	            $output = self::$CENTENAS[$n[0] - 1];
	        }
	        $k = intval(substr($n,1));
	        if ($k <= 20) 
	        {
	            $output .= self::$UNIDADES[$k];
	        } 
	        else 
	        {
	            if(($k > 30) && ($n[2] !== '0')) 
	            {
	                $output .= sprintf('%sY %s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
	            } 
	            else 
	            {
	                $output .= sprintf('%s%s', self::$DECENAS[intval($n[1]) - 2], self::$UNIDADES[intval($n[2])]);
	            }
	        }
	        return $output;
	    }
	}
@endphp
<!DOCTYPE html>
<html>
<head>
	<title>Documento de autorización</title>
	<style type="text/css">
		p
		{
			font-size: 12px;
			text-align: justify;
			line-height: 1.5;
		}
	</style>
</head>
<body style="position: relative !important; font-size: 12px; background: white;">
	<div class="pdf-full">
		<table style="border-collapse: separate;border-spacing: 25px;">
			<tbody>
				<tr>
					<td style=" width: 100px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: left;"><img width="100%" src="{{ asset('images/logo-LogIn.jpg') }}"></td>
					<td style=" width: 450px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: right;"><label class="pdf-label">Folio: {{ $requests->folio }} </label> <br><label class="pdf-label">Fecha: {{ date('d-m-Y',strtotime($date)) }}</label></td>
				</tr>
			</tbody>
		</table>
		<div class="pdf-body">
			<center>
				<div class="pdf-body-header-title">
					Solicitud de Préstamo Personal
				</div><br>
				
				<div class="pdf-body-header-subtitle">
					Carta compromiso de conformidad y pago
				</div><br>
				<div class="pdf-body-content">
					<p>
						Yo <u>@foreach(App\User::where('id',$requests->idRequest)->get() as $user) {{ $user->name.' '.$user->last_name.' '.$user->scnd_last_name }} @endforeach</u> hago constar que recibí como préstamo <u>Personal</u> de la empresa 
						<u>@foreach(App\Enterprise::where('id',$requests->idEnterpriseR)->get() as $enterprise) {{ $enterprise->name }} @endforeach</u>, la cantidad de $ <u>@foreach($requests->loan as $loan) {{ $loan->amount }} </u> MXN / <u>{{ $letras = NumeroALetras::convertir($loan->amount, 'pesos mexicanos', 'centavos') }}</u> @endforeach y que me encuentro 
						conforme con la cuota semanal y las fechas en las que debo realizar los pagos correspondientes,
						mismos que entregaré en tiempo y forma al área de Administración de Personal.
					</p>
				</div>
			</center><br><br><br><br><br><br><br><br>
			<table style="border-collapse: separate;border-spacing: 25px;">
				<tbody>
					<tr>
						<td style="font-size: 12px; border: 1px solid; width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: center;"><b>Coordinadora de Recursos Humanos</b><br><br><br>_______________________________ <br>Jacqueline Martínez Briseño</td>
						<td style="font-size: 12px; border: 1px solid; width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: center;"><b>Gerencia de Desarrollo Coorporativo</b><br><br><br>
						_______________________________ <br>
						Gabriela Camacho Salazar</td>
					</tr>
					<tr>
						<td style="font-size: 12px; border: 1px solid; width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: center;">
							<b>Nombre del Trabajador/Beneficiario</b> <br><br><br>
						_______________________________ <br>@foreach(App\User::where('id',$requests->idRequest)->get() as $user) {{ $user->name.' '.$user->last_name.' '.$user->scnd_last_name }} @endforeach
						</td>
						<td style="font-size: 12px; border: 1px solid; width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: center;">
							<b>Autorizador</b><br><br><br>
							_______________________________ <br>
							
						</td>
					</tr>
				</tbody>
			</table><br><br><br><br><br><br>
			<table style="border-collapse: separate;border-spacing: 25px;">
				<tbody>
					<tr>
						<td style=" width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: left; font-size: 8px;">Código: GD-ARH-SO001</td>
						<td style=" width: 250px; margin: 0 auto; margin-bottom: 5px; padding: 5px; text-align: right; font-size: 8px;">Rev. 1.2</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>