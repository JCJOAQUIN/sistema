<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<style type="text/css">
		@font-face
		{
			font-family: 'Narrow';
			src: url({{ storage_path('fonts\narrow.ttf') }}) format("truetype");
			font-weight: 400;
			font-style: normal;
		}
		.main
		{
			border-collapse : collapse;
			border-spacing  : 0;
			margin          : 0;
			padding         : 0;
			width           : 100%;
			text-align      : center;
			page-break-after: always;
		}
		.enterprise-logo
		{
			width: 150px;
		}
		body
		{
			width: 700px;
			font-size: 10px;
			font-family: Narrow, sans-serif;
		}
		.red-col
		{
			background: #B74C57;
			color     : #fff;
			border    : 1px solid #333740;
		}
		.white-col
		{
			border: 1px solid #333740;
		}
		.blue-col
		{
			background: #333740;
			color     : #fff;
			border    : 1px solid #333740;
		}
		.text-right
		{
			text-align: right;
		}
		.text-left
		{
			text-align: left;
		}
		.grey-col
		{
			background: #e0e2e6;
			padding   : 0 0 0 20px;
			border    : 1px solid #333740;
			text-align: left;
		}
		.with-info
		{
			background: #eccfd2;
			color     : #B74C57;
		}
	</style>
	@php
		$month = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
	@endphp
</head>
<body>
	<main>
		@if(isset($employee) && $employee != "")
			<table class="main">
				<tbody>
					<tr>
						<td colspan="12" class="white-col" style="width: 70%; font-size: 16px; border-right:none; color: #333740;">PROYECTA INDUSTRIAL DE MÉXICO</td>
						<td colspan="4" class="white-col" style="border-left: none;"><img class="enterprise-logo" src="{{asset('images/enterprise').'/'.App\Enterprise::find(5)->path}}"></td>
					</tr>
					<tr>
						<td colspan="16" class="blue-col" style="font-size: 12px">REQUISICIÓN DE PERSONAL</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4" class="red-col">PUESTO A CUBRIR:</td>
						<td colspan="6" class="red-col">DEPARTAMENTO:</td>
						<td colspan="6" class="red-col">FECHA DE ELABORACIÓN:</td>
					</tr>
					<tr>
						<td colspan="4" class="white-col" style="padding: 5px 0">{{ $employee->position }}</td>
						<td colspan="6" class="white-col" style="padding: 5px 0">{{ $employee->subdepartment()->exists() ? $employee->subdepartment->name : '' }}</td>
						<td colspan="6" class="white-col" style="padding: 5px 0">{{ $employee->requisition->request_model->fDate->format('d') }}-{{ $month[$employee->requisition->request_model->fDate->format('n')] }}-{{ $employee->requisition->request_model->fDate->format('Y') }}</td>
					</tr>
					<tr>
						<td colspan="4" class="red-col">EN REEMPLAZO DE:</td>
						<td colspan="7" class="red-col">PUESTO DEL JEFE INMEDIATO:</td>
						<td colspan="5" class="red-col">PLAZAS SOLICITADAS:</td>
					</tr>
					<tr>
						<td colspan="4" class="white-col" style="padding: 5px 0">{{ $employee->replace }}</td>
						<td colspan="7" class="white-col" style="padding: 5px 0">{{ $employee->position_immediate_boss }}</td>
						<td colspan="5" class="white-col" style="padding: 5px 0">1</td>
					</tr>
					<tr>
						<td colspan="7" class="red-col">INDICAR CON "X" EN DONDE SE CUBRIRÁ LA VACANTE</td>
						<td colspan="2" class="white-col"></td>
						<td class="white-col"></td>
						<td colspan="2" class="white-col">DOS BOCAS</td>
						<td class="white-col @if($employee->project == 126) with-info @endif ">@if($employee->project == 126) X @endif</td>
						<td colspan="2" class="white-col">TULA</td>
						<td class="white-col @if($employee->project == 124) with-info @endif ">@if($employee->project == 124) X @endif</td>
					</tr>
					<tr>
						<td colspan="16" style="height: 3px;"></td>
					</tr>
					<tr>
						<td colspan="16" class="blue-col">PARA USO EXCLUSIVO DEL ÁREA DE RECURSOS HUMANOS</td>
					</tr>
					<tr>
						<td colspan="2" class="blue-col text-right">SALARIO MENSUAL:</td>
						<td colspan="2" class="white-col {{ ($employee->netIncome > 0 ? 'with-info' : '') }}">{{ ($employee->netIncome > 0 ? '$ '.number_format($employee->netIncome,2) : '') }}</td>
						<td colspan="2" class="blue-col">VIÁTICOS:</td>
						<td colspan="2" class="white-col with-info">{{ ($employee->viatics > 0 ? '$ '.number_format($employee->viatics,2) : 'N/A') }}</td>
						<td colspan="2" class="blue-col">CAMPAMENTO:</td>
						<td colspan="2" class="white-col with-info">{{ ($employee->camping > 0 ? '$ '.number_format($employee->camping,2) : 'N/A') }}</td>
						<td colspan="2" class="blue-col">ADICIONAL AUTORIZADO *</td>
						<td colspan="2" class="white-col with-info">N/A</td>
					</tr>
					<tr>
						<td colspan="2" class="blue-col">IMPORTE NETO:</td>
						<td class="white-col with-info">X</td>
						<td colspan="2" class="blue-col text-right">IMPORTE BRUTO:</td>
						<td class="white-col"></td>
						<td colspan="2" class="blue-col text-right">PAGO QUINCENAL:</td>
						<td class="white-col @if($employee->periodicity == '04') with-info @endif ">@if($employee->periodicity == '04') X @endif</td>
						<td colspan="2" class="blue-col text-right">PAGO SEMANAL:</td>
						<td class="white-col @if($employee->periodicity == '02') with-info @endif ">@if($employee->periodicity == '02') X @endif</td>
						<td colspan="4" class="blue-col">* Especificar motivos del monto adicional autorizado:</td>
					</tr>
					<tr>
						<td colspan="12" class="blue-col">TIPO DE CONTRATO:</td>
						<td colspan="4" rowspan="2" class="white-col"></td>
					</tr>
					<tr>
						<td colspan="2" class="blue-col text-right">ASIMILADOS A SALARIO:</td>
						<td class="white-col @if($employee->regime_id == '09') with-info @endif ">@if($employee->regime_id == '09') X @endif</td>
						<td class="blue-col">OBRA:</td>
						<td class="white-col with-info">X</td>
						<td class="blue-col">OTRO:</td>
						<td colspan="6" class="white-col"></td>
					</tr>
					<tr>
						<td colspan="5" class="blue-col text-right">NOMBRE COMPLETO DE QUIEN OCUPARÁ EL PUESTO:</td>
						<td colspan="5" class="white-col with-info">{{ $employee->name }} {{ $employee->last_name }} {{ $employee->scnd_last_name }}</td>
						<td colspan="2" class="blue-col text-right">FECHA DE CONTRATACIÓN:</td>
						<td colspan="4" class="white-col with-info">{{ $employee->admissionDate->format('d') }}-{{ $month[$employee->admissionDate->format('n')] }}-{{ $employee->admissionDate->format('Y') }}</td>
					</tr>
					<tr>
						<td colspan="3" class="blue-col"></td>
						<td colspan="2" class="blue-col">WBS</td>
						<td colspan="2" class="white-col @if($employee->wbs_id != '') with-info @endif " style="padding: 5px 0">@if($employee->wbs_id != ''){{ $employee->wbsData->code }}@endif</td>
						<td colspan="2" class="blue-col">DIRECTO</td>
						<td colspan="2" class="white-col @if($employee->wbs_id != '' && $employee->wbsData->code != 400) with-info @endif " style="padding: 5px 0">@if($employee->wbs_id != '' && $employee->wbsData->code != 400) X @endif</td>
						<td colspan="2" class="blue-col">INDIRECTO</td>
						<td colspan="3" class="white-col @if($employee->wbs_id != '' && $employee->wbsData->code == 400) with-info @endif " style="padding: 5px 0">@if($employee->wbs_id != '' && $employee->wbsData->code == 400) X @endif</td>
					</tr>
					<tr>
						<td colspan="16" class="red-col text-left">PROPÓSITO BÁSICO DEL PUESTO:</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 50px">{{ $employee->purpose }}</td>
					</tr>
					<tr>
						<td colspan="16" class="red-col text-left">REQUERIMIENTOS DEL PUESTO:</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 30px">{{ $employee->requeriments }}</td>
					</tr>
					<tr>
						<td colspan="16" class="red-col text-left">OBSERVACIONES:</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 30px">{{ $employee->observations }}</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 70px; border-bottom:none;"></td>
					</tr>
					<tr>
						<td colspan="4" style="border-bottom: 1px solid #333740; border-left: 1px solid #333740;">{{ $employee->immediate_boss }}</td>
						<td>&nbsp;</td>
						<td colspan="5" style="border-bottom: 1px solid #333740;">Ing. Jorge Alberto Tovar Nicolli</td>
						<td>&nbsp;</td>
						<td colspan="5" style="border-bottom: 1px solid #333740; border-right: 1px solid #333740;">Lic. Ana Margarita Olán León</td>
					</tr>
					<tr>
						<td colspan="4" style="border-left: 1px solid #333740;">{{ $employee->position_immediate_boss }}</td>
						<td>&nbsp;</td>
						<td colspan="5">Director de Proyecto</td>
						<td>&nbsp;</td>
						<td colspan="5" style="border-right: 1px solid #333740;">Coordinador de RR.HH.</td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 50px; border-bottom:none; border-top:none;"></td>
					</tr>
					<tr>
						<td colspan="5" style="border-left: 1px solid #333740;"></td>
						<td colspan="5" style="border-bottom: 1px solid #333740;">Lic. César Ulises Padilla Herrera</td>
						<td colspan="6" style="border-right: 1px solid #333740;"></td>
					</tr>
					<tr>
						<td colspan="5" style="border-left: 1px solid #333740;"></td>
						<td colspan="5">SUBDIRECTOR DE ADMÓN.</td>
						<td colspan="6" style="border-right: 1px solid #333740;"></td>
					</tr>
					<tr>
						<td colspan="16" class="white-col" style="height: 50px; border-top:none;"></td>
					</tr>
					<tr>
						<td colspan="16" style="height: 3px;"></td>
					</tr>
					<tr>
						<td colspan="16" class="blue-col" style="border-bottom:1px solid #fff;">PARA USO EXCLUSIVO DEL ÁREA DE RECURSOS HUMANOS</td>
					</tr>
					<tr>
						<td colspan="5" class="blue-col" style="border-right: 1px solid #fff;">ENTREVISTAS:</td>
						<td colspan="6" class="blue-col" style="border-right: 1px solid #fff;">PRUEBAS PSICOMÉTRICAS:</td>
						<td colspan="5" class="blue-col">EXÁMENES MÉDICOS:</td>
					</tr>
					<tr>
						<td colspan="5" class="white-col" style="height: 30px;"></td>
						<td colspan="6" class="white-col"></td>
						<td colspan="5" class="white-col"></td>
					</tr>
					<tr>
						<td colspan="16" class="grey-col" style="font-size: 8px;">
							PIM-MEX-PRC-RH-G-001-FOR2-REV0
						</td>
					</tr>
				</tbody>
			</table>
		@else
			@foreach ($request->requisition->employees as $emp)
				<table class="main">
					<tbody>
						<tr>
							<td colspan="12" class="white-col" style="width: 70%; font-size: 16px; border-right:none; color: #333740;">PROYECTA INDUSTRIAL DE MÉXICO</td>
							<td colspan="4" class="white-col" style="border-left: none;"><img class="enterprise-logo" src="{{asset('images/enterprise').'/'.App\Enterprise::find(5)->path}}"></td>
						</tr>
						<tr>
							<td colspan="16" class="blue-col" style="font-size: 12px">REQUISICIÓN DE PERSONAL</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4" class="red-col">PUESTO A CUBRIR:</td>
							<td colspan="6" class="red-col">DEPARTAMENTO:</td>
							<td colspan="6" class="red-col">FECHA DE ELABORACIÓN:</td>
						</tr>
						<tr>
							<td colspan="4" class="white-col" style="padding: 5px 0">{{ $emp->position }}</td>
							<td colspan="6" class="white-col" style="padding: 5px 0">{{ $emp->subdepartment()->exists() ? $emp->subdepartment->name : '' }}</td>
							<td colspan="6" class="white-col" style="padding: 5px 0">{{ $request->fDate->format('d') }}-{{ $month[$request->fDate->format('n')] }}-{{ $request->fDate->format('Y') }}</td>
						</tr>
						<tr>
							<td colspan="4" class="red-col">EN REEMPLAZO DE:</td>
							<td colspan="7" class="red-col">PUESTO DEL JEFE INMEDIATO:</td>
							<td colspan="5" class="red-col">PLAZAS SOLICITADAS:</td>
						</tr>
						<tr>
							<td colspan="4" class="white-col" style="padding: 5px 0">{{ $emp->replace }}</td>
							<td colspan="7" class="white-col" style="padding: 5px 0">{{ $emp->position_immediate_boss }}</td>
							<td colspan="5" class="white-col" style="padding: 5px 0">1</td>
						</tr>
						<tr>
							<td colspan="7" class="red-col">INDICAR CON "X" EN DONDE SE CUBRIRÁ LA VACANTE</td>
							<td colspan="2" class="white-col"></td>
							<td class="white-col"></td>
							<td colspan="2" class="white-col">DOS BOCAS</td>
							<td class="white-col @if($request->idProject == 126) with-info @endif ">@if($request->idProject == 126) X @endif</td>
							<td colspan="2" class="white-col">TULA</td>
							<td class="white-col @if($request->idProject == 124) with-info @endif ">@if($request->idProject == 124) X @endif</td>
						</tr>
						<tr>
							<td colspan="16" style="height: 3px;"></td>
						</tr>
						<tr>
							<td colspan="16" class="blue-col">PARA USO EXCLUSIVO DEL ÁREA DE RECURSOS HUMANOS</td>
						</tr>
						<tr>
							<td colspan="2" class="blue-col text-right">SALARIO MENSUAL:</td>
							<td colspan="2" class="white-col {{ ($emp->netIncome > 0 ? 'with-info' : '') }}">{{ ($emp->netIncome > 0 ? '$ '.number_format($emp->netIncome,2) : '') }}</td>
							<td colspan="2" class="blue-col">VIÁTICOS:</td>
							<td colspan="2" class="white-col with-info">{{ ($emp->viatics > 0 ? '$ '.number_format($emp->viatics,2) : 'N/A') }}</td>
							<td colspan="2" class="blue-col">CAMPAMENTO:</td>
							<td colspan="2" class="white-col with-info">{{ ($emp->camping > 0 ? '$ '.number_format($emp->camping,2) : 'N/A') }}</td>
							<td colspan="2" class="blue-col">ADICIONAL AUTORIZADO *</td>
							<td colspan="2" class="white-col with-info">N/A</td>
						</tr>
						<tr>
							<td colspan="2" class="blue-col">IMPORTE NETO:</td>
							<td class="white-col with-info">X</td>
							<td colspan="2" class="blue-col text-right">IMPORTE BRUTO:</td>
							<td class="white-col"></td>
							<td colspan="2" class="blue-col text-right">PAGO QUINCENAL:</td>
							<td class="white-col @if($emp->periodicity == '04') with-info @endif ">@if($emp->periodicity == '04') X @endif</td>
							<td colspan="2" class="blue-col text-right">PAGO SEMANAL:</td>
							<td class="white-col @if($emp->periodicity == '02') with-info @endif ">@if($emp->periodicity == '02') X @endif</td>
							<td colspan="4" class="blue-col">* Especificar motivos del monto adicional autorizado:</td>
						</tr>
						<tr>
							<td colspan="12" class="blue-col">TIPO DE CONTRATO:</td>
							<td colspan="4" rowspan="2" class="white-col"></td>
						</tr>
						<tr>
							<td colspan="2" class="blue-col text-right">ASIMILADOS A SALARIO:</td>
							<td class="white-col @if($emp->regime_id == '09') with-info @endif ">@if($emp->regime_id == '09') X @endif</td>
							<td class="blue-col">OBRA:</td>
							<td class="white-col with-info">X</td>
							<td class="blue-col">OTRO:</td>
							<td colspan="6" class="white-col"></td>
						</tr>
						<tr>
							<td colspan="5" class="blue-col text-right">NOMBRE COMPLETO DE QUIEN OCUPARÁ EL PUESTO:</td>
							<td colspan="5" class="white-col with-info">{{ $emp->name }} {{ $emp->last_name }} {{ $emp->scnd_last_name }}</td>
							<td colspan="2" class="blue-col text-right">FECHA DE CONTRATACIÓN:</td>
							<td colspan="4" class="white-col with-info">{{ $emp->admissionDate->format('d') }}-{{ $month[$emp->admissionDate->format('n')] }}-{{ $emp->admissionDate->format('Y') }}</td>
						</tr>
						<tr>
							<td colspan="3" class="blue-col"></td>
							<td colspan="2" class="blue-col">WBS</td>
							<td colspan="2" class="white-col @if($request->requisition->code_wbs != '') with-info @endif " style="padding: 5px 0">@if($request->requisition->code_wbs != ''){{ $request->requisition->wbs->code }}@endif</td>
							<td colspan="2" class="blue-col">DIRECTO</td>
							<td colspan="2" class="white-col @if($request->requisition->code_wbs != '' && $request->requisition->wbs->code != 400) with-info @endif " style="padding: 5px 0">@if($request->requisition->code_wbs != '' && $request->requisition->wbs->code != 400) X @endif</td>
							<td colspan="2" class="blue-col">INDIRECTO</td>
							<td colspan="3" class="white-col @if($request->requisition->code_wbs != '' && $request->requisition->wbs->code == 400) with-info @endif " style="padding: 5px 0">@if($request->requisition->code_wbs != '' && $request->requisition->wbs->code == 400) X @endif</td>
						</tr>
						<tr>
							<td colspan="16" class="red-col text-left">PROPÓSITO BÁSICO DEL PUESTO:</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 50px">{{ $emp->purpose }}</td>
						</tr>
						<tr>
							<td colspan="16" class="red-col text-left">REQUERIMIENTOS DEL PUESTO:</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 30px">{{ $emp->requeriments }}</td>
						</tr>
						<tr>
							<td colspan="16" class="red-col text-left">OBSERVACIONES:</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 30px">{{ $emp->observations }}</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 70px; border-bottom:none;"></td>
						</tr>
						<tr>
							<td colspan="4" style="border-bottom: 1px solid #333740; border-left: 1px solid #333740;">{{ $emp->immediate_boss }}</td>
							<td>&nbsp;</td>
							<td colspan="5" style="border-bottom: 1px solid #333740;">Ing. Jorge Alberto Tovar Nicolli</td>
							<td>&nbsp;</td>
							<td colspan="5" style="border-bottom: 1px solid #333740; border-right: 1px solid #333740;">Lic. Ana Margarita Olán León</td>
						</tr>
						<tr>
							<td colspan="4" style="border-left: 1px solid #333740;">{{ $emp->position_immediate_boss }}</td>
							<td>&nbsp;</td>
							<td colspan="5">Director de Proyecto</td>
							<td>&nbsp;</td>
							<td colspan="5" style="border-right: 1px solid #333740;">Coordinador de RR.HH.</td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 50px; border-bottom:none; border-top:none;"></td>
						</tr>
						<tr>
							<td colspan="5" style="border-left: 1px solid #333740;"></td>
							<td colspan="5" style="border-bottom: 1px solid #333740;">Lic. César Ulises Padilla Herrera</td>
							<td colspan="6" style="border-right: 1px solid #333740;"></td>
						</tr>
						<tr>
							<td colspan="5" style="border-left: 1px solid #333740;"></td>
							<td colspan="5">SUBDIRECTOR DE ADMÓN.</td>
							<td colspan="6" style="border-right: 1px solid #333740;"></td>
						</tr>
						<tr>
							<td colspan="16" class="white-col" style="height: 50px; border-top:none;"></td>
						</tr>
						<tr>
							<td colspan="16" style="height: 3px;"></td>
						</tr>
						<tr>
							<td colspan="16" class="blue-col" style="border-bottom:1px solid #fff;">PARA USO EXCLUSIVO DEL ÁREA DE RECURSOS HUMANOS</td>
						</tr>
						<tr>
							<td colspan="5" class="blue-col" style="border-right: 1px solid #fff;">ENTREVISTAS:</td>
							<td colspan="6" class="blue-col" style="border-right: 1px solid #fff;">PRUEBAS PSICOMÉTRICAS:</td>
							<td colspan="5" class="blue-col">EXÁMENES MÉDICOS:</td>
						</tr>
						<tr>
							<td colspan="5" class="white-col" style="height: 30px;"></td>
							<td colspan="6" class="white-col"></td>
							<td colspan="5" class="white-col"></td>
						</tr>
						<tr>
							<td colspan="16" class="grey-col" style="font-size: 8px;">
								PIM-MEX-PRC-RH-G-001-FOR2-REV0
							</td>
						</tr>
					</tbody>
				</table>
			@endforeach
		@endif
	</main>
</body>
</html>