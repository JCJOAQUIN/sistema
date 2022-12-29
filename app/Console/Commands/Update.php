<?php

namespace App\Console\Commands;

use App;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Excel;
use PHPExcel_Cell;
use Illuminate\Support\Facades\DB;

class Update extends Command
{
	protected $signature = 'update:test';

	protected $description = 'Pruebas';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$typeReport		= 2;
		$requests = App\RequestModel::where('kind',16)
					->whereIn('status',[4,5,10,11,12,15])
					->orderBy('fDate','DESC')
					->orderBy('folio','DESC')
					->get();

		$countNF = $countSalary	= $countBonus = $countSettlement = $countLiquidation = $countVP = $countPS = 0;
		$nominasNF = $nominaSalary = $nominaBonus = $nominaSettlement = $nominaLiquidation = $nominaVP = $nominaPS = [];
		foreach ($requests as $request) 
		{
			foreach (App\NominaEmployee::join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')->where('nomina_employees.idnomina',$request->nominasReal->first()->idnomina)->where('nomina_employees.visible',1)->orderBy('real_employees.last_name','ASC')->orderBy('real_employees.scnd_last_name','ASC')->orderBy('real_employees.name','ASC')->select('nomina_employees.*')->get() as $nomina)
			{
				if($request->taxPayment == 1)
				{
					switch ($request->nominasReal->first()->idCatTypePayroll) 
					{
						case '001':
							$nominaSalary[$countSalary]['folio']			= $request->folio;
							$nominaSalary[$countSalary]['title']			= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaSalary[$countSalary]['type']				= $request->nominasReal->first()->typePayroll()->exists() ? $request->nominasReal->first()->typePayroll->description : '';
							$nominaSalary[$countSalary]['id']				= $nomina->idrealEmployee;
							$nominaSalary[$countSalary]['name']				= $nomina->employee->first()->name;
							$nominaSalary[$countSalary]['last_name']		= $nomina->employee->first()->last_name;
							$nominaSalary[$countSalary]['scnd_last_name']	= $nomina->employee->first()->scnd_last_name;
							$nominaSalary[$countSalary]['work_project']		= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaSalary[$countSalary]['work_enterprise']	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaSalary[$countSalary]['work_account']		= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaSalary[$countSalary]['salary_rangeDate']	= $nomina->from_date.' '.$nomina->to_date;
							$nominaSalary[$countSalary]['salary_date']		= $request->authorizeDate;
							$nominaSalary[$countSalary]['salary_netIncome']	= $nomina->salary->first()->netIncome;
							
							$countSalary++;
							break;

						case '002':
							$nominaBonus[$countBonus]['folio']				= $request->folio;
							$nominaBonus[$countBonus]['title']				= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaBonus[$countBonus]['type']				= $request->nominasReal->first()->typePayroll->exists() ? $request->nominasReal->first()->typePayroll->description : '';
							$nominaBonus[$countBonus]['id']					= $nomina->idrealEmployee;
							$nominaBonus[$countBonus]['name']				= $nomina->employee->first()->name;
							$nominaBonus[$countBonus]['last_name']			= $nomina->employee->first()->last_name;
							$nominaBonus[$countBonus]['scnd_last_name']		= $nomina->employee->first()->scnd_last_name;
							$nominaBonus[$countBonus]['work_project']		= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaBonus[$countBonus]['work_enterprise']	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaBonus[$countBonus]['work_account']		= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaBonus[$countBonus]['bonus_date']			= $request->authorizeDate;
							$nominaBonus[$countBonus]['bonus_netIncome']	= $nomina->bonus->first()->netIncome;
							$countBonus++;
							break;

						case '003':
							$nominaSettlement[$countSettlement]['folio']				= $request->folio;
							$nominaSettlement[$countSettlement]['title']				= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaSettlement[$countSettlement]['type']					= $request->nominasReal->first()->typePayroll()->exists() ? $request->nominasReal->first()->typePayroll->description : '';
							$nominaSettlement[$countSettlement]['id']					= $nomina->idrealEmployee;
							$nominaSettlement[$countSettlement]['name']					= $nomina->employee->first()->name;
							$nominaSettlement[$countSettlement]['last_name']			= $nomina->employee->first()->last_name;
							$nominaSettlement[$countSettlement]['scnd_last_name']		= $nomina->employee->first()->scnd_last_name;
							$nominaSettlement[$countSettlement]['work_project']			= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaSettlement[$countSettlement]['work_enterprise']		= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaSettlement[$countSettlement]['work_account']			= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaSettlement[$countSettlement]['settlement_downDate'] 	= $nomina->down_date;
							$nominaSettlement[$countSettlement]['settlement_date'] 		= $request->authorizeDate;
							$nominaSettlement[$countSettlement]['settlement_netIncome']	= $nomina->liquidation->first()->netIncome;
							$countSettlement++;
							break;
						case '004':
							$nominaLiquidation[$countLiquidation]['folio']					= $request->folio;
							$nominaLiquidation[$countLiquidation]['title']					= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaLiquidation[$countLiquidation]['type']					= $request->nominasReal->first()->typePayroll()->exists() ? $request->nominasReal->first()->typePayroll->description : '';
							$nominaLiquidation[$countLiquidation]['id']						= $nomina->idrealEmployee;
							$nominaLiquidation[$countLiquidation]['name']					= $nomina->employee->first()->name;
							$nominaLiquidation[$countLiquidation]['last_name']				= $nomina->employee->first()->last_name;
							$nominaLiquidation[$countLiquidation]['scnd_last_name']			= $nomina->employee->first()->scnd_last_name;
							$nominaLiquidation[$countLiquidation]['work_project']			= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaLiquidation[$countLiquidation]['work_enterprise']		= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaLiquidation[$countLiquidation]['work_account']			= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaLiquidation[$countLiquidation]['liquidation_downDate']	= $nomina->down_date;
							$nominaLiquidation[$countLiquidation]['liquidation_date']		= $request->authorizeDate;
							$nominaLiquidation[$countLiquidation]['liquidation_netIncome']	= $nomina->liquidation->first()->netIncome;
							$countLiquidation++;
							break;
						
						case '005':
							$nominaVP[$countVP]['folio']			= $request->folio;
							$nominaVP[$countVP]['title']			= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaVP[$countVP]['type']				= $request->nominasReal->first()->typePayroll->description;
							$nominaVP[$countVP]['id']				= $nomina->idrealEmployee;
							$nominaVP[$countVP]['name']				= $nomina->employee->first()->name;
							$nominaVP[$countVP]['last_name']		= $nomina->employee->first()->last_name;
							$nominaVP[$countVP]['scnd_last_name']	= $nomina->employee->first()->scnd_last_name;
							$nominaVP[$countVP]['work_project']		= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaVP[$countVP]['work_enterprise']	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaVP[$countVP]['work_account']		= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaVP[$countVP]['vp_date']			= $request->authorizeDate;
							
							$nominaVP[$countVP]['vp_netIncome']		= $nomina->vacationPremium->first()->netIncome;
							
							$countVP++;
							break;

						case '006':
							$nominaPS[$countPS]['folio']			= $request->folio;
							$nominaPS[$countPS]['title']			= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
							$nominaPS[$countPS]['type']				= $request->nominasReal->first()->typePayroll->exists() ? $request->nominasReal->first()->typePayroll->description : '';
							$nominaPS[$countPS]['id']				= $nomina->idrealEmployee;
							$nominaPS[$countPS]['name']				= $nomina->employee->first()->name;
							$nominaPS[$countPS]['last_name']		= $nomina->employee->first()->last_name;
							$nominaPS[$countPS]['scnd_last_name']	= $nomina->employee->first()->scnd_last_name;
							
							$nominaPS[$countPS]['work_project']		= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
							$nominaPS[$countPS]['work_enterprise']	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
							$nominaPS[$countPS]['work_account']		= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
							$nominaVP[$countPS]['ps_date']			= $request->authorizeDate;
							$nominaPS[$countPS]['ps_netIncome']		= $nomina->profitSharing->first()->netIncome;
							$countPS++;
							break;

						default:
							# code...
							break;
					}
				}
				else
				{
					$req		= App\RequestModel::find($request->folio);
					$rf			= App\RequestModel::where('kind',16)
									->where('idprenomina',$req->idprenomina)
									->where('idDepartment',$req->idDepartment)
									->first();
					$nominaemp 	= App\NominaEmployee::where('idrealEmployee',$nomina->idrealEmployee)
									->where('idnomina',$rf->nominasReal->first()->idnomina)
									->first();

					$nominasNF[$countNF]['folio']			= $request->folio;
					$nominasNF[$countNF]['title']			= $request->nominasReal->first()->title.' '.$request->nominasReal->first()->datetitle;
					$nominasNF[$countNF]['type']			= $request->nominasReal->first()->typePayroll->exists() ? $request->nominasReal->first()->typePayroll->description : '';
					$nominasNF[$countNF]['name']			= $nomina->employee->first()->name;
					$nominasNF[$countNF]['last_name']		= $nomina->employee->first()->last_name;
					$nominasNF[$countNF]['scnd_last_name']	= $nomina->employee->first()->scnd_last_name;
					$nominasNF[$countNF]['dataNF_rangeDate']=$request->nominasReal->first()->from_date.' - '.$request->nominasReal->first()->to_date;
					$nominasNF[$countNF]['dataNF_downDate']	= $request->nominasReal->first()->down_date;
					$nominasNF[$countNF]['dataNF_date']		= $request->authorizeDate;
					$nominasNF[$countNF]['work_project']	= $nomina->workerData->first()->projects()->exists() ? $nomina->workerData->first()->projects->proyectName :'';
					$nominasNF[$countNF]['work_enterprise']	= $nomina->workerData->first()->enterprises()->exists() ? $nomina->workerData->first()->enterprises->name : '';
					$nominasNF[$countNF]['work_account']	= $nomina->workerData->first()->accounts()->exists() ? $nomina->workerData->first()->accounts->account.' '.$nomina->workerData->first()->accounts->description : '';
					
					$nominasNF[$countNF]['dataNF_amount']	= $nomina->nominasEmployeeNF->first()->amount;
					
					$countNF++;
				}
			}
		}
		
		Excel::create('REPORTE DE NÓMINAS', function($excel) use ($nominasNF, $nominaSalary, $nominaBonus, $nominaSettlement, $nominaLiquidation, $nominaVP, $nominaPS,$typeReport)
		{
			if ($typeReport == 2) 
			{
				if ($nominasNF != null) 
				{
					$excel->sheet('Nóminas No Fiscales',function($sheet) use ($nominasNF)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'L' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:L1');							
						$sheet->cell('A1:L1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:L2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:L2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Tipo',
								'Rango de Fecha',
								'Fecha de Baja',
								'Fecha de Autorización',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominasNF as $nomina) 
						{
							$row	= [];
							$row[]	= $nomina['folio'];
							$row[]	= $nomina['last_name'];
							$row[]	= $nomina['scnd_last_name'];
							$row[]	= $nomina['name'];
							$row[] 	= $nomina['type'];
							$row[] 	= $nomina['dataNF_rangeDate'];
							$row[]	= $nomina['dataNF_downDate'];
							$row[]	= $nomina['dataNF_date'];
							$row[]	= $nomina['work_enterprise'];
							$row[] 	= $nomina['work_account'];
							$row[] 	= $nomina['work_project'];
							$row[]	= $nomina['dataNF_amount'];
							$sheet->appendRow($row);
						}	
					});
				}
				if ($nominaSalary != null) 
				{
					$excel->sheet('Sueldo',function($sheet) use ($nominaSalary)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'J' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:J1');							
						$sheet->cell('A1:J1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:J2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:J2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Rango de Fechas',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaSalary as $nomina) 
						{
							$row	= [];
							$row[]	= $nomina['folio'];
							$row[]	= $nomina['last_name'];
							$row[]	= $nomina['scnd_last_name'];
							$row[]	= $nomina['name'];
							$row[] 	= $nomina['salary_date'];
							$row[]  = $nomina['salary_rangeDate'];
							$row[]	= $nomina['work_enterprise'];
							$row[] 	= $nomina['work_account'];
							$row[] 	= $nomina['work_project'];
							$row[]  = $nomina['salary_netIncome'];
							$sheet->appendRow($row);
						}	
					});
				}
				if ($nominaBonus != null) 
				{
					$excel->sheet('Aguinaldo',function($sheet) use ($nominaBonus)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'I' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:I1');							
						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaBonus as $nomina) 
						{
							$row	= [];
							$row[]	= $nomina['folio'];
							$row[]	= $nomina['last_name'];
							$row[]	= $nomina['scnd_last_name'];
							$row[]	= $nomina['name'];
							$row[]  = $nomina['bonus_date'];
							$row[]	= $nomina['work_enterprise'];
							$row[] 	= $nomina['work_account'];
							$row[] 	= $nomina['work_project'];
							$row[]  = $nomina['bonus_netIncome'];
							$sheet->appendRow($row);
						}	
					});
				}

				if ($nominaSettlement != null) 
				{
					$excel->sheet('Finiquito',function($sheet) use ($nominaSettlement)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'I' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:I1');							
						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Fecha de Baja',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaSettlement as $nomina) 
						{
								$row	= [];
								$row[]	= $nomina['folio'];
								$row[]	= $nomina['last_name'];
								$row[]	= $nomina['scnd_last_name'];
								$row[]	= $nomina['name'];
								$row[] 	= $nomina['settlement_date'];
								$row[]  = $nomina['settlement_downDate'];
								$row[]	= $nomina['work_enterprise'];
								$row[] 	= $nomina['work_account'];
								$row[] 	= $nomina['work_project'];
								$row[]  = $nomina['settlement_netIncome'];
								$sheet->appendRow($row);
						}	
					});
				}

				if ($nominaLiquidation != null) 
				{
					$excel->sheet('Liquidación',function($sheet) use ($nominaLiquidation)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'I' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:I1');							
						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Fecha de Baja',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaLiquidation as $nomina) 
						{
								$row	= [];
								$row[]	= $nomina['folio'];
								$row[]	= $nomina['last_name'];
								$row[]	= $nomina['scnd_last_name'];
								$row[]	= $nomina['name'];
								$row[]  = $nomina['liquidation_date'];
								$row[]  = $nomina['liquidation_downDate'];
								$row[]	= $nomina['work_enterprise'];
								$row[] 	= $nomina['work_account'];
								$row[] 	= $nomina['work_project'];
								$row[]  = $nomina['liquidation_netIncome'];
								$sheet->appendRow($row);
						}	
					});
				}

				if ($nominaVP != null) 
				{
					$excel->sheet('Prima Vacaional',function($sheet) use ($nominaVP)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'I' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:I1');							
						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaVP as $nomina) 
						{
								$row	= [];
								$row[]	= $nomina['folio'];
								$row[]	= $nomina['last_name'];
								$row[]	= $nomina['scnd_last_name'];
								$row[]	= $nomina['name'];
								$row[]  = $nomina['vp_date'];
								$row[]	= $nomina['work_enterprise'];
								$row[] 	= $nomina['work_account'];
								$row[] 	= $nomina['work_project'];
								$row[] 	= $nomina['vp_netIncome'];
								$sheet->appendRow($row);
						}	
					});
				}
				if ($nominaPS != null) 
				{
					$excel->sheet('Reparto de Utilidades',function($sheet) use ($nominaPS)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);

						$sheet->setColumnFormat(array(
							'I' => '"$"#,##0.00_-',
						));

						$sheet->mergeCells('A1:I1');							
						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','','','']);
						$sheet->row(2,[
								'Folio',// A
								'Apellido Paterno',
								'Apellido Materno',
								'Nombre', // D
								'Fecha de Autorización',
								'Empresa', // K
								'Clasificación de Gasto',
								'Proyecto',
								'Monto', // AQ
							]);

						foreach ($nominaPS as $nomina) 
						{
							$row	= [];
							$row[]	= $nomina['folio'];
							$row[]	= $nomina['last_name'];
							$row[]	= $nomina['scnd_last_name'];
							$row[]	= $nomina['name'];
							$row[]  = $nomina['ps_date'];
							$row[]	= $nomina['work_enterprise'];
							$row[]	= $nomina['work_account'];
							$row[]	= $nomina['work_project'];
							$row[]	= $nomina['ps_netIncome'];
							$sheet->appendRow($row);
						}	
					});
				}
			}
				

		})->store('xlsx', storage_path('report'));
	}
}
