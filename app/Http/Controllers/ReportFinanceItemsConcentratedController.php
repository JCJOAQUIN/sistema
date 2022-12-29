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

class ReportFinanceItemsConcentratedController extends Controller
{
	private $module_id = 130;
	public function accountConcentratedReport(Request $request)
	{
		if (Auth::user()->module->where('id',212)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);

			return view('reporte.finanzas.concentrado_partidas',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 212
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function accountConcentratedExcel(Request $request)
	{
		$enterprise     = $request->idEnterprise;
		$project 		= $request->idProject;
		$month 			= $request->month;
		$year 			= $request->year;

		$accounts       = App\Account::select('account','description','selectable','idAccAcc')
							->where('idEnterprise',$enterprise)
							->where('account','like','5%')
							->orderBy('account','ASC')
							->groupBy('account','description','selectable')
							->get();


		$array = [];
		$groupingDesg = [];
		$count = 0;

		$totalGroups = [];
		$totalGastos = [];
		for ($i=0; $i < count($request->year); $i++) 
		{ 
			$requests 	= App\RequestModel::selectRaw('
							request_models.folio AS folio,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,

							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,

							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,

							ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
								'
							
							/*ROUND(IF(request_models.kind = 1, detail_purchases.amount, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.sAmount, IF(request_models.kind = 18, finances.amount, IF(request_models.kind = 17, purchase_record_details.total, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
							'*/
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
					->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
					->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
					->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[5,10,11,12])
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
					->whereYear('request_models.fDate',$request->year[$i])
					->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
					->orderBy('request_kinds.kind','ASC')
					->orderBy('request_models.folio','ASC')
					->get();

			$payments	= App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
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
					->whereYear('request_models.fDate',$request->year[$i])
					->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
					->get();

			$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
			$resultCollectPayments =  collect($payments)->groupBy('account');

			if(count($accounts)>0)
			{
				$init 				= 3;
				$keyR				= 0;
				$total				= array();
				
				foreach ($accounts as $acc) 
				{
					$balance								= 0;
					$tempAcc								= App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
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
					
					if(isset($total[$acc->account]))
					{
						$total[$acc->account] += $balance;
					}
					else
					{
						$total[$acc->account] = $balance;
					}
					if(isset($total[$acc->father]))
					{
						$total[$acc->father] += $balance;
					}
					else
					{
						$total[$acc->father] = $balance;
					}



					if($acc->father!='')
					{
						$new = App\Account::where('account',$acc->father)->first();
						
						if ($new->father != '')
						{
							if (isset($total[$new->father])) 
							{
								$total[$new->father] += $balance;
							}
							else
							{
								$total[$new->father] = $balance;
							}
							$new_a = App\Account::where('account',$new->father)->first();
							if ($new_a->father != '') 
							{
								if (isset($total[$new_a->father])) 
								{
									$total[$new_a->father] += $balance;
								}
								else
								{
									$total[$new_a->father] = $balance;
								}
								$new_b = App\Account::where('account',$new_a->father)->first();
								if ($new_b->father != '') 
								{
									if (isset($total[$new_b->father])) 
									{
										$total[$new_b->father] += $balance;
									}
									else
									{
										$total[$new_b->father] = $balance;
									}
								}
							}
						}
					}
					$keyR++;
				}

				for ($g=0; $g < count($request->account); $g++) 
				{
					$groupingAccount		= App\GroupingAccount::find($request->account[$g]); 
					$groupingDesg[$g]['name']	= $groupingAccount->name;
					$groupingDesg[$g]['total_'.$request->year[$i]]	= 0;
					foreach ($groupingAccount->hasAccount as $group)
					{
						$accName				= App\Account::find($group->idAccAcc)->account;
						$groupingDesg[$g]['total_'.$request->year[$i]]	+= $total[$accName];
						if(isset($totalGroups['total_'.$request->year[$i]]))
						{
							$totalGroups['total_'.$request->year[$i]] += $total[$accName];
						}
						else
						{
							$totalGroups['total_'.$request->year[$i]] = $total[$accName];
						}
					}
				}
			}
			$totalGastos['totalGastos_'.$request->year[$i]] = $total['5000000'];
			$count++;
		}				

		$requests 	= App\RequestModel::selectRaw('
						request_models.folio AS folio,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,

						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,

						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
						
						ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
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
				->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
				->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
				->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
				->whereIn('request_models.kind',[1,8,9,16,17,18])
				->whereIn('request_models.status',[5,10,11,12])
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
				->whereRaw('YEAR(request_models.fDate) IN('.implode(',', $year).')')
				->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
				->orderBy('request_kinds.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->get();

		$payments	= App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
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
				->whereRaw('YEAR(request_models.fDate) IN('.implode(',', $year).')')
				->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
				->get();

		

		$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
		$resultCollectPayments =  collect($payments)->groupBy('account');

		$resultCollectDescription =  collect($requests)->groupBy('description');
		//return $resultCollectDescription;

		if(count($accounts)>0)
		{
			$init 				= 3;
			$keyR				= 0;
			$total				= array();
			
			foreach ($accounts as $acc) 
			{
				$balance								= 0;
				$tempAcc								= App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
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
				
				if(isset($total[$acc->account]))
				{
					$total[$acc->account] += $balance;
				}
				else
				{
					$total[$acc->account] = $balance;
				}
				if(isset($total[$acc->father]))
				{
					$total[$acc->father] += $balance;
				}
				else
				{
					$total[$acc->father] = $balance;
				}



				if($acc->father!='')
				{
					$new = App\Account::where('account',$acc->father)->first();
					
					if ($new->father != '')
					{
						if (isset($total[$new->father])) 
						{
							$total[$new->father] += $balance;
						}
						else
						{
							$total[$new->father] = $balance;
						}
						$new_a = App\Account::where('account',$new->father)->first();
						if ($new_a->father != '') 
						{
							if (isset($total[$new_a->father])) 
							{
								$total[$new_a->father] += $balance;
							}
							else
							{
								$total[$new_a->father] = $balance;
							}
							$new_b = App\Account::where('account',$new_a->father)->first();
							if ($new_b->father != '') 
							{
								if (isset($total[$new_b->father])) 
								{
									$total[$new_b->father] += $balance;
								}
								else
								{
									$total[$new_b->father] = $balance;
								}
							}
						}
					}
				}
				$keyR++;
			}

			$grouping = [];
			for ($i=0; $i < count($request->account); $i++) 
			{
				$groupingAccount		= App\GroupingAccount::find($request->account[$i]); 
				$grouping[$i]['name']	= $groupingAccount->name;
				$grouping[$i]['total']	= 0;
				foreach ($groupingAccount->hasAccount as $g)
				{
					$accName				= App\Account::find($g->idAccAcc)->account;
					$grouping[$i]['total']	+= $total[$accName];
				}
			}

			//return $grouping;

			Excel::create('Concentrado de Partidas', function($excel) use ($enterprise,$grouping,$groupingDesg,$total,$request,$totalGroups,$totalGastos)
			{
				$excel->sheet('Concentrado',function($sheet) use ($enterprise,$grouping,$total,$request)
				{
					$sheet->setWidth(array(
						'A'     => 40,
						'B'     => 30,
					));

					$sheet->setColumnFormat(array(
							'A' => '@',
							'B' => '"$"#,##0.00_-',
						));
					$sheet->mergeCells('A1:B1');
					
					$sheet->cell('A1:B2', function($cells) {
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '18','bold' => true));
									$cells->setBackground('#7fc544');
									$cells->setFontColor('#ffffff');
								});

					$sheet->row(1,['TOTALES']);
					$sheet->row(2,['Grupo','Total']);

					$init			= 3;
					$end			= 3;
					$totalAccounts	= 0;

					foreach ($grouping as $g)
					{
						$row	= [];
						$row[]	= strtoupper($g['name']);
						$row[]	= $g['total'];
						$sheet->appendRow($row);

						$totalAccounts += $g['total'];
						$end++;
					}
					$row = [];
					$row[] = '';
					$row[] = '';
					$end++;
					$sheet->appendRow($row);

					$row = [];
					$row[] = "Total";
					$row[] = $totalAccounts;
					$end++;
					$sheet->appendRow($row);

					$row = [];
					$row[] = "Total de Gastos";
					$row[] = $total['5000000'];
					$end++;
					$sheet->appendRow($row);

					$row = [];
					$row[] = "% Sobre Total de Gastos";
					$row[] = $totalAccounts>0 ? ($totalAccounts*100/$total['5000000'])/100 : 0;
					$sheet->appendRow($row);

					
					$sheet->cell('A'.$init.':B'.$end, function($cells) 
					{
						$cells->setFont(array('family' => 'Calibri','size' => '16'));
					});
					$sheet->setColumnFormat(array(
						'A'.$end.':B'.$end => '0.00%'
					));
				});

				$excel->sheet('Desglosado',function($sheet) use ($enterprise,$groupingDesg,$total,$request,$totalGroups,$totalGastos)
				{
					$countYears 	= count($request->year);
					$range			= 'B:'.chr(65+($countYears*2)).'';
					$endRange		= chr(65+($countYears*2));
					$numberColumn	= 1+($countYears*2);

					

					$sheet->setColumnFormat(array(
							'A1:'.$endRange.'2' 	=> '@',
							'B:'.$endRange.'' 	=> '"$"#,##0.00_-',
						));
					$sheet->mergeCells('A1:'.$endRange.'1');
					
					$sheet->cell('A1:'.$endRange.'2', function($cells) {
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '18','bold' => true));
									$cells->setBackground('#7fc544');
									$cells->setFontColor('#ffffff');
								});

					$titles = [ 
						'Grupo',
					];
						
					for ($i=0; $i < $countYears; $i++) 
					{ 
						array_push($titles,$request->year[$i]);
						array_push($titles,'% de'.$request->year[$i]);
					}

					$sheet->row(1,['TOTALES']);
					$sheet->row(2,$titles);

					$init	= 3;
					$end	= 3;

					foreach ($groupingDesg as $g)
					{
						$row	= [];
						$row[]	= strtoupper($g['name']);
						for ($i=0; $i < $countYears; $i++) 
						{ 
							$row[]	= $g['total_'.$request->year[$i]];
							

							if($totalGroups['total_'.$request->year[$i]] <= 0)
							{
								$row[] = 0;
							}
							else{
								$row[] 	= (($g['total_'.$request->year[$i]]*100)/$totalGroups['total_'.$request->year[$i]])/100;
							}
						}
						$sheet->appendRow($row);
						$end++;
					}
					
					$row	= [];
					$row[]	= '';
					$row[]	= '';
					$end++;
					$sheet->appendRow($row);

					$row	= [];
					$row[]	= "Total";
					for ($i=0; $i < $countYears; $i++) 
					{
						$row[] = $totalGroups['total_'.$request->year[$i]];
						$row[] = 1;
					}
					$end++;
					$sheet->appendRow($row);

					$row	= [];
					$row[]	= "Total Gastos";
					for ($i=0; $i < $countYears; $i++) 
					{
						$row[] = $totalGastos['totalGastos_'.$request->year[$i]];
						$row[] = '';
					}
					$end++;
					$sheet->appendRow($row);

					$row	= [];
					$row[]	= "% Sobre Total de Gastos";
					for ($i=0; $i < $countYears; $i++) 
					{
						$row[] = $totalGroups['total_'.$request->year[$i]]>0 ? ($totalGroups['total_'.$request->year[$i]]*100/$totalGastos['totalGastos_'.$request->year[$i]])/100 : 0;
						$row[] = '';
					}
					
					$sheet->appendRow($row);

					$sheet->cell('A'.$init.':'.$endRange.''.$end.'', function($cells) 
					{
						$cells->setFont(array('family' => 'Calibri','size' => '16'));
					});

					$sheet->setColumnFormat(array(
						'A'.$end.':'.$endRange.''.$end.'' => '0.00%'
					));

					$countLetter = 0;
					for ($i=0; $i < $countYears; $i++) 
					{
						$rangePercentage = chr(67+$countLetter).''.$init.':'.chr(67+$countLetter).''.$end.'';
						$sheet->setColumnFormat(array(
							''.$rangePercentage.'' => '0.00%'
						));
						$countLetter = $countLetter+2;
					}

					
				});
			})->export('xlsx');
		}
		else
		{
			$alert  = "swal('', 'Sin resultados', 'error');";
			return redirect('/report/finance/account-concentrated')->with('alert',$alert);
		}
	}

	public function accountConcentratedCharts(Request $request)
	{
		$enterprise	= $request->idEnterprise;
		$project	= $request->idProject;
		$month		= $request->month;
		$year		= $request->year;
		$account	= $request->account;

		$accounts       = App\Account::select('account','description','selectable','idAccAcc')
							->where('idEnterprise',$enterprise)
							->where('account','like','5%')
							->orderBy('account','ASC')
							->groupBy('account','description','selectable')
							->get();


		$array			= [];
		$groupingDesg	= [];
		$count			= 0;

		$totalGroups = [];
		$totalGastos = [];
		
		for ($i=0; $i < count($request->year); $i++) 
		{ 
			$requests 	= App\RequestModel::selectRaw('
							request_models.folio AS folio,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,

							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,

							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
							
							ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
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
					->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
					->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
					->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[5,10,11,12])
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
					->whereYear('request_models.fDate',$request->year[$i])
					->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
					->orderBy('request_kinds.kind','ASC')
					->orderBy('request_models.folio','ASC')
					->get();

			$payments	= App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
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
					->whereYear('request_models.fDate',$request->year[$i])
					->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $month).')')
					->get();

			$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
			$resultCollectPayments =  collect($payments)->groupBy('account');

			if(count($accounts)>0)
			{
				$init 				= 3;
				$keyR				= 0;
				$total				= array();
				
				foreach ($accounts as $acc) 
				{
					$balance								= 0;
					$tempAcc								= App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
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
					
					if(isset($total[$acc->account]))
					{
						$total[$acc->account] += $balance;
					}
					else
					{
						$total[$acc->account] = $balance;
					}
					if(isset($total[$acc->father]))
					{
						$total[$acc->father] += $balance;
					}
					else
					{
						$total[$acc->father] = $balance;
					}



					if($acc->father!='')
					{
						$new = App\Account::where('account',$acc->father)->first();
						
						if ($new->father != '')
						{
							if (isset($total[$new->father])) 
							{
								$total[$new->father] += $balance;
							}
							else
							{
								$total[$new->father] = $balance;
							}
							$new_a = App\Account::where('account',$new->father)->first();
							if ($new_a->father != '') 
							{
								if (isset($total[$new_a->father])) 
								{
									$total[$new_a->father] += $balance;
								}
								else
								{
									$total[$new_a->father] = $balance;
								}
								$new_b = App\Account::where('account',$new_a->father)->first();
								if ($new_b->father != '') 
								{
									if (isset($total[$new_b->father])) 
									{
										$total[$new_b->father] += $balance;
									}
									else
									{
										$total[$new_b->father] = $balance;
									}
								}
							}
						}
					}
					$keyR++;
				}

				for ($g=0; $g < count($request->account); $g++) 
				{
					$groupingAccount								= App\GroupingAccount::find($request->account[$g]); 
					$groupingDesg[$g]['name']						= $groupingAccount->name;
					$groupingDesg[$g]['total_'.$request->year[$i]]	= 0;
					foreach ($groupingAccount->hasAccount as $group)
					{
						$accName				= App\Account::find($group->idAccAcc)->account;
						$groupingDesg[$g]['total_'.$request->year[$i]]	+= round($total[$accName],2);
						if(isset($totalGroups['total_'.$request->year[$i]]))
						{
							$totalGroups['total_'.$request->year[$i]] += round($total[$accName],2);
						}
						else
						{
							$totalGroups['total_'.$request->year[$i]] = round($total[$accName],2);
						}
					}
				}
			}
			$totalGastos['totalGastos_'.$request->year[$i]] = $total['5000000'];
			$count++;
		}

		$data 	= App\Module::find($this->module_id);

		return view('reporte.finanzas.graficas',
		[
			'id'			=> $data['father'],
			'title'			=> $data['name'],
			'details'		=> $data['details'],
			'child_id'		=> $this->module_id,
			'option_id'		=> 212,
			'groupingDesg'	=> $groupingDesg,
			'enterprise'	=> $enterprise,
			'project'		=> $project,
			'month'			=> $month,
			'year'			=> $year,
			'account'		=> $account,
		]);
	}

	public function getAccountsConcentrated(Request $request)
	{
		if($request->ajax())
		{
			$output 	= "";
			$groupsAccount = App\GroupingAccount::where('idEnterprise',$request->enterpriseid)
							->orderBy('name')
							->get();
			if (count($groupsAccount) > 0) 
			{
				$keyR = 0;
				foreach ($groupsAccount as $group) 
				{	
					$accountRegister[$keyR]['description']	= $group->name;
					$accountRegister[$keyR]['id']			= $group->id;
					$keyR++;
				}
				return Response($accountRegister);
			}
		}
	}
}
