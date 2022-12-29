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
use PHPExcel_Cell;
use Carbon\Carbon;

class ReportFinanceExpensesConcentratedController extends Controller
{
	private $module_id = 130;
	public function expensesConcentrated(Request $request)
	{
		if (Auth::user()->module->where('id',217)->count()>0) 
		{
			$data = App\Module::find($this->module_id);

			return view('reporte.finanzas.concentrado_gastos',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 217
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function expensesConcentratedResult(Request $request)
	{
		if (Auth::user()->module->where('id',217)->count()>0) 
		{
			$enterprise     = $request->enterprise;
			$project        = $request->project;
			$year           = $request->year;
			$month          = $request->month;
			$account        = $request->account;

			$data           = App\Module::find($this->module_id);

			$accounts       = App\Account::where('idEnterprise',$enterprise)
							->where('account','like','5%')
							->orderBy('account','ASC')
							->get();

			$arrayChart     = [];
			$accountRegister    = array();
			$total              = array();
			$count = 0;
			
			for ($i=0; $i < count($year); $i++) 
			{ 
				$requests   = App\RequestModel::selectRaw('
								request_models.folio AS folio,
								IF(request_models.kind = 1 OR request_models.kind = 7 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
								IF(request_models.kind = 1 OR request_models.kind = 7 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,
								IF(request_models.kind = 1 OR request_models.kind = 7  OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
								ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 7, stationeries.subtotal, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount))))))))))),2) AS amount
								'
						)
						->join('status_requests','request_models.status','=','status_requests.idrequestStatus')
						->join('request_kinds','request_models.kind','=','request_kinds.idrequestkind')
						->join('users','request_models.idRequest','=','users.id')
						->join('users as elab','request_models.idElaborate','=','elab.id')
						->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
						->leftJoin('detail_purchases','purchases.idPurchase','=','detail_purchases.idPurchase')
						->leftJoin('resources','request_models.folio','=','resources.idFolio')
						->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
						->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
						->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
						->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
						->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
						->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
						->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
						->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
						->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
						->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
						->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
						->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
						->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
						->leftJoin('finances','request_models.folio','=','finances.idFolio')
						->leftJoin('stationeries','request_models.folio','=','stationeries.idFolio')
						->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
						->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
						->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
						->whereIn('request_models.kind',[1,7,8,9,16,17,18])
						->whereIn('request_models.status',[5,9,10,11,12])
						->where( function($query) use ($enterprise) 
						{
							if($enterprise != null)
							{
								$query->where('request_models.idEnterpriseR',$enterprise)->orWhere('worker_datas.enterprise',$enterprise);
							}
						})
						->where( function($query) use ($project) 
						{
							if($project != null)
							{
								$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
							}
						})
						->whereYear('request_models.fDate',$year[$i])
						->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
						->orderBy('request_kinds.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->get();

				$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
						->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
						->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
						->select('payments.account as account','payments.amount as amount')
						->whereIn('request_models.kind',[1,8,9,16,17,18])
						->whereIn('request_models.status',[5,10,11,12])
						->where('payments.idEnterprise',$enterprise)
						->where( function($query) use ($project) 
						{
							if($project != null)
							{
								$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
							}
						})
						->whereYear('request_models.fDate',$year[$i])
						->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
						->get();

				$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
				$resultCollectPayments =  collect($payments)->groupBy('account');

				if(count($accounts)>0)
				{
					$init               = 3;
					
					$keyR               = 0;
					
					
					foreach ($accounts as $acc) 
					{
						$accountRegister[$keyR]['description_'.$year[$i]]   = strtoupper($acc->description);
						$accountRegister[$keyR]['account_'.$year[$i]]		= $acc->account;
						$accountRegister[$keyR]['selectable_'.$year[$i]]	= $acc->selectable;
						$accountRegister[$keyR]['level_'.$year[$i]]			= $acc->level;
						$accountRegister[$keyR]['father_'.$year[$i]]		= $acc->father;
						$balance											= 0;
						$tempAcc											= App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
						foreach ($tempAcc as $ta)
						{
							if(isset($resultCollectAccounts[$ta]))
							{
								$balance    += $resultCollectAccounts[$ta]->sum('amount');
							}
							if(isset($resultCollectPayments[$ta]))
							{
								$balance    += $resultCollectPayments[$ta]->sum('amount');
							}
						}
						
						if(isset($total[$acc->account.'_'.$year[$i]]))
						{
							$total[$acc->account.'_'.$year[$i]] += $balance;
						}
						else
						{
							$total[$acc->account.'_'.$year[$i]] = $balance;
						}
						if(isset($total[$acc->father.'_'.$year[$i]]))
						{
							$total[$acc->father.'_'.$year[$i]] += $balance;
						}
						else
						{
							$total[$acc->father.'_'.$year[$i]] = $balance;
						}



						if($acc->father!='')
						{
							$new = App\Account::where('account',$acc->father)->first();
							
							if ($new->father != '')
							{
								if (isset($total[$new->father.'_'.$year[$i]])) 
								{
									$total[$new->father.'_'.$year[$i]] += $balance;
								}
								else
								{
									$total[$new->father.'_'.$year[$i]] = $balance;
								}
								$new_a = App\Account::where('account',$new->father)->first();
								if ($new_a->father != '') 
								{
									if (isset($total[$new_a->father.'_'.$year[$i]])) 
									{
										$total[$new_a->father.'_'.$year[$i]] += $balance;
									}
									else
									{
										$total[$new_a->father.'_'.$year[$i]] = $balance;
									}
									$new_b = App\Account::where('account',$new_a->father)->first();
									if ($new_b->father != '') 
									{
										if (isset($total[$new_b->father.'_'.$year[$i]])) 
										{
											$total[$new_b->father.'_'.$year[$i]] += $balance;
										}
										else
										{
											$total[$new_b->father.'_'.$year[$i]] = $balance;
										}
									}
								}
							}
						}
						$keyR++;
					}
				}
			}

			$fileName   = 'AdG'.round(microtime(true) * 1000).'_report';
			Excel::create($fileName, function($excel) use ($enterprise,$project,$year,$month,$request,$accountRegister,$total)
			{
				foreach($year as $y)
				{
					$nameEnterprise             = App\Enterprise::find($enterprise)->name;
					foreach($request->account as $acc)
					{
						$totalAccount   = 0;
						$nameAccount    = App\Account::where('idEnterprise',$enterprise)->where('account',$acc)->first()->description;
						$sheetName      = $nameAccount.'_'.$y;

						$excel->sheet($sheetName,function($sheet) use ($enterprise,$project,$year,$month,$request,$accountRegister,$total,$totalAccount,$acc,$y,$sheetName,$nameAccount)
						{
							$sheet->setWidth(array(
								'A'     => 35,
								'B'     => 30,
							));

							$sheet->setColumnFormat(array(
									'A' => '@',
									'B' => '"$"#,##0.00_-',
								));
							$sheet->mergeCells('A1:B1');
							
							$sheet->cell('A1:B2', function($cells) 
							{
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family' => 'Calibri','size' => '18','bold' => true));
								$cells->setBackground('#7fc544');
								$cells->setFontColor('#ffffff');
							});

							$sheet->row(1,[mb_strtoupper($nameAccount.' '.$y,'UTF-8')]);
							$sheet->row(2,['CUENTA','TOTAL']);
							$end = 3;
							foreach ($accountRegister as $a)
							{
								if ($a['selectable_'.$y] == 0 && $a['level_'.$y] == 3 && ($a['father_'.$y]==$acc || $a['account_'.$y]==$acc))
								{
									$row    = [];
									$row[]  = mb_strtoupper($a['description_'.$y], 'UTF-8');
									$row[]  = round($total[$a['account_'.$y].'_'.$y],2);
									$sheet->appendRow($row);
									$end++;
									$totalAccount += round($total[$a['account_'.$y].'_'.$y],2);
								}
							}
							$row    = [];
							$row[]  = '';
							$row[]  = '';
							$end++;
							$sheet->appendRow($row);

							$row    = [];
							$row[]  = 'TOTAL:';
							$row[]  = $totalAccount;
							$end++;
							$sheet->appendRow($row);

							$sheet->cell('A3:B'.$end, function($cells) 
							{
								$cells->setFont(array('family' => 'Calibri','size' => '16'));
							});

						});
					}
				}
			})->store('xlsx', storage_path('report'));

			return view('reporte.finanzas.concentrado_gastos',
			[
				'id'				=> $data['father'],
				'title'				=> $data['name'],
				'details'			=> $data['details'],
				'child_id'			=> $this->module_id,
				'option_id'			=> 217,
				'arrayChart'		=> $arrayChart,
				'enterprise'		=> $enterprise,
				'project'			=> $project,
				'year'				=> $year,
				'month'				=> $month,
				'account'			=> $request->account,
				'accountRegister'	=> $accountRegister,
				'total'				=> $total,
				'fileName'			=> $fileName
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function getAccountExpensesConcentrated(Request $request)
	{
		if ($request->ajax()) 
		{
			$accounts	= [];
			$key		= 0;
			$getAccount	= App\Account::where('idEnterprise',$request->enterpriseid)
						->where('account','like','5%')
						->orderBy('account','ASC')
						->get();

			if (count($getAccount)>0) 
			{
				foreach ($getAccount as $acc) 
				{
					if($acc->level == 2)
					{
						$accounts[$key]['idAccAcc']		= $acc->idAccAcc;
						$accounts[$key]['account']		= $acc->account;
						$accounts[$key]['description']	= $acc->account.' '.$acc->description;
						$key++;
					}
				}
			}
			return Response($accounts);
		}

	}

	public function downloadExcel($name)
	{
		if(Auth::user()->module->where('id',217)->count()>0)
		{
			if(\Storage::disk('reserved')->exists('report/'.$name.'.xlsx'))
			{
				return \Storage::disk('reserved')->download('report/'.$name.'.xlsx');
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return abort(404);
		}
	}
}
