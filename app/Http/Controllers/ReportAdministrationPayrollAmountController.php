<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Excel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Carbon\Carbon;

class ReportAdministrationPayrollAmountController extends Controller
{
	private $module_id = 96;
	public function payrollAmount(Request $request)
	{
		if (Auth::user()->module->where('id',270)->count()>0) 
		{
			$data       = App\Module::find($this->module_id);
			$weekOfYear = $request->weekOfYear;
			$year       = date('Y');
			$initRange  = App\Http\Controllers\ReportAdministrationPayrollAmountController::initDate($year,$weekOfYear);
			$endRange   = App\Http\Controllers\ReportAdministrationPayrollAmountController::endDate($year,$weekOfYear);
			$enterprise = $request->enterprise;
			$type       = $request->type;
			$fiscal     = $request->fiscal;
			$employee   = $request->employee;
			$project    = $request->project;
			$min_date   = "";
			$max_date   = "";

			if(($request->mindate=="" && $request->maxdate!="") || ($request->mindate!="" && $request->maxdate=="") || ($request->mindate!="" && $request->maxdate!=""))
			{
				$min_date	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
				$max_date	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

				if(($min_date=="" && $max_date!="") || ($min_date!="" && $max_date==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($min_date!="" && $max_date!="" && $max_date < $min_date) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}


			$nominas    = App\NominaEmployee::where(function($query) use($weekOfYear,$initRange,$endRange)
			{
				if($weekOfYear != "")
				{
					$query->whereBetween('from_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
					$query->whereBetween('to_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
				}
				if($weekOfYear != "")
				{
					$query->orWhereHas('nomina',function($q) use($initRange,$endRange)
					{
						$q->whereBetween('nominas.from_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
						$q->whereBetween('nominas.to_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
					});
				}   
			})
			->where(function($query) use ($enterprise,$project,$type,$fiscal,$employee,$min_date,$max_date)
			{
				if ($enterprise != "") 
				{
					$query->whereHas('workerData',function($q) use($enterprise)
					{
						$q->whereIn('enterprise',$enterprise);
					});
				}
				if ($project != "") 
				{
					$query->whereHas('workerData',function($q) use($project)
					{
						$q->whereIn('project',$project);
					});
				}
				if ($type != "") 
				{
					$query->whereHas('nomina',function($q) use($type)
					{
						$q->whereIn('nominas.idCatTypePayroll',$type);
					});
				}
				if ($fiscal != "") 
				{
					$query->whereIn('fiscal',$fiscal);
				}
				if ($employee != "") 
				{
					$query->whereIn('nomina_employees.idrealEmployee',$employee);
				}

				if($min_date != "" && $max_date != "")
				{
					$query->whereBetween('from_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
					$query->whereBetween('to_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);

					$query->orWhereHas('nomina',function($q) use($min_date,$max_date)
					{
						$q->whereBetween('nominas.from_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
						$q->whereBetween('nominas.to_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
					});
				}
			})
			->whereHas('nomina',function($q)
			{
				$q->whereHas('requestModel',function($req)
				{
					$req->whereIn('status',[5,10,11,12,18]);
				});
			})
			->paginate(10);
			return view('reporte.administracion.montos_nomina',
			[
				'nominas'    => $nominas,
				'weekOfYear' => $weekOfYear,
				'id'         => $data['father'],
				'title'      => $data['name'],
				'details'    => $data['details'],
				'child_id'   => $this->module_id,
				'option_id'  => 270,
				'enterprise' => $enterprise,
				'type'       => $type,
				'fiscal'     => $fiscal,
				'employee'   => $employee,
				'project'    => $project,   
				'mindate'    => $request->mindate,
				'maxdate'    => $request->maxdate,
			]);
		}
	}
	
	public function exportPayrollAmount(Request $request)
	{
		if (Auth::user()->module->where('id',270)->count()>0) 
		{
			$weekOfYear = $request->weekOfYear;
			$year       = date('Y');
			$initRange  = App\Http\Controllers\ReportAdministrationPayrollAmountController::initDate($year,$weekOfYear);
			$endRange   = App\Http\Controllers\ReportAdministrationPayrollAmountController::endDate($year,$weekOfYear);
			$enterprise = $request->enterprise;
			$type       = $request->type;
			$fiscal     = $request->fiscal;
			$employee   = $request->employee;
			$project    = $request->project;
			$min_date   = "";
			$max_date   = "";

			if(($request->mindate=="" && $request->maxdate!="") || ($request->mindate!="" && $request->maxdate=="") || ($request->mindate!="" && $request->maxdate!=""))
			{
				$min_date	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
				$max_date	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

				if(($min_date=="" && $max_date!="") || ($min_date!="" && $max_date==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($min_date!="" && $max_date!="" && $max_date < $min_date) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$nominas    = DB::table('nomina_employees')->leftJoin('nominas','nominas.idnomina','nomina_employees.idnomina')
				->leftJoin('request_models','nominas.idFolio','request_models.folio')
				->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
				->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
				->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
				->leftJoin('projects','worker_datas.project','projects.idproyect')
				->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','cat_type_payrolls.id')
				->leftJoin(DB::raw('(select idnominaEmployee, netIncome from salaries group by idnominaEmployee,netIncome) as salaries'),'nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
				->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
				->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
				->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
				->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
				->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(select idnominaEmployee, SUM(amount) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idnominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->selectRaw('
						request_models.folio as folio,
						real_employees.name as name,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						IF(nomina_employees.from_date IS NOT NULL, nomina_employees.from_date, nominas.from_date) as from_date,
						IF(nomina_employees.to_date IS NOT NULL, nomina_employees.to_date, nominas.to_date) as to_date,
						projects.proyectName as work_project,
						enterprises.name as work_enterprise,
						cat_type_payrolls.description as type,
						IF(nomina_employees.fiscal = 1, "Fiscal",
							IF(nomina_employees.fiscal = 2, "No Fiscal", "NOM 035"
							)
						) as fiscal,
						ROUND(
							IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, 
								IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, 
									IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, 
										IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, 
											IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount
											)
										)
									)
								)
							),2) AS total_fiscal,
						IF(payment.amount != "", payment.amount, 0) as total_paid,
						IF(payment.amount != "",0,ROUND(
							IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, 
								IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, 
									IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, 
										IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, 
											IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, 0
											)
										)
									)
								)
							),2)) AS remaining_pay,
						IF(nomina_employees.fiscal = 1,IF(bills.status = 1, bills.total, 0),"No Aplica") as total_billed,
						IF(nomina_employees.fiscal = 1,IF(bills.status = 0, bills.total, 0),"No Aplica") as pending_billing
					')
				->where(function($query) use($weekOfYear,$initRange,$endRange)
				{
					if($weekOfYear != "")
					{
						$query->whereBetween('nomina_employees.from_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
						$query->whereBetween('nomina_employees.to_date',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
					}
				})
				->where(function($query) use ($enterprise,$project,$type,$fiscal,$employee,$min_date,$max_date)
				{
					if ($enterprise != "") 
					{
						$query->whereIn('enterprises.id',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('projects.idproyect',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('nomina_employees.fiscal',$fiscal);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if($min_date != "" && $max_date != "")
					{
						$query->whereBetween('nomina_employees.from_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
						$query->whereBetween('nomina_employees.to_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);

						$query->orWhere(function($q) use($min_date,$max_date)
						{
							$q->whereBetween('nominas.from_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
							$q->whereBetween('nominas.to_date',[''.$min_date.' '.date('00:00:00').'',''.$max_date.' '.date('23:59:59').'']);
						});
					}
				})
				->whereIn('request_models.status',[5,10,11,12,18])
				->get();

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('f8cd5c')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('EE881F')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Pagos de Nómina.xlsx');

			$headers        = ['Solicitud','Información de Pago','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				if ($k == 0) 
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Nombre', 'Apellido Paterno','Apellido Materno','Desde','Hasta','Empresa', 'Proyecto', 'Categoría','Tipo','Total A Pagar','Total Pagado','Total Por Pagar','Total Timbrado','Total Por Timbrar'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				if ($k == 0) 
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol1);
				}
				else
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($nominas as $employee)
			{
				$tmpArr = [];
				foreach($employee as $k => $r)
				{
					if(in_array($k,['total_fiscal','total_paid','remaining_pay','total_billed','pending_billing']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}

				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
	}

	public function initDate($year,$week)
	{
		switch ($week) 
		{
			case 1:
				$week = '01';
				break;
			case 2:
				$week = '02';
				break;
			case 3:
				$week = '03';
				break;
			case 4:
				$week = '04';
				break;
			case 5:
				$week = '05';
				break;
			case 6:
				$week = '06';
				break;
			case 7:
				$week = '07';
				break;
			case 8:
				$week = '08';
				break;
			case 9:
				$week = '09';
				break;
			default:
				$week = $week;
				break;
		}
		return date('Y-m-d',strtotime($year.'W'.$week.'-1')); 
	}

	public function endDate($year,$week)
	{
		switch ($week) 
		{
			case 1:
				$week = '01';
				break;
			case 2:
				$week = '02';
				break;
			case 3:
				$week = '03';
				break;
			case 4:
				$week = '04';
				break;
			case 5:
				$week = '05';
				break;
			case 6:
				$week = '06';
				break;
			case 7:
				$week = '07';
				break;
			case 8:
				$week = '08';
				break;
			case 9:
				$week = '09';
				break;
			default:
				$week = $week;
				break;
		}
		return date('Y-m-d',strtotime($year.'W'.$week.'-7')); 
	}
}
