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

class ReportFinanceAccountsConcentratedController extends Controller
{
	private $module_id = 130;
	public function concentratedReport(Request $request)
	{
		if (Auth::user()->module->where('id',132)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$type			= $request->type!='' ? $request->type : null;
			$enterprise		= $request->idEnterprise;
			$father			= $request->father;
			$arrayResult[]	= null;
			$projectname	= $request->projectname!='' ? $request->projectname : null;
			$accountdesc	= $request->accountdesc!='' ? $request->accountdesc : null;

			return view('reporte.finanzas.concentrado',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 132,
					'type'			=> $type,
					'enterprise'	=> $enterprise,
					'father'		=> $father,
					'arrayResult'	=> $arrayResult,
					'projectname'	=> $projectname,
					'accountdesc'	=> $accountdesc
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function concentratedReportResult(Request $request)
	{
		if (Auth::user()->module->where('id',132)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprise		= $request->idEnterprise;
			$project		= $request->idProject;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$father			= $request->father;
			$type			= $request->type;
			$projectname	= $request->projectname!='' ? $request->projectname : null;
			$accountdesc	= $request->accountdesc!='' ? $request->accountdesc : null;

			$accounts   	= App\Account::select('account','description','selectable')
								->whereIn('idEnterprise',$enterprise)
								->orderBy('account','ASC')
								->groupBy('account','description','selectable')
								->get();
			
			$requests   = App\RequestModel::selectRaw('
							request_models.folio AS folio,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
							
							ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
									'
					)
					->join('status_requests','request_models.status','=','status_requests.idrequestStatus')
					->join('request_kinds','request_models.kind','=','request_kinds.idrequestkind')
					->join('users','request_models.idRequest','=','users.id')
					->join('users as elab','request_models.idElaborate','=','elab.id')
					->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
					//->leftJoin('providers','purchases.idProvider','=','providers.idProvider')
					->leftJoin('detail_purchases','purchases.idPurchase','=','detail_purchases.idPurchase')
					->leftJoin('resources','request_models.folio','=','resources.idFolio')
					//->leftJoin('payment_methods AS resourcePayment','resources.idpaymentMethod','=','resourcePayment.idpaymentMethod')
					->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
					->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
					->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
					//->leftJoin('payment_methods AS refundPayment','refunds.idpaymentMethod','=','refundPayment.idpaymentMethod')
					->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
					->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
					->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
					->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
					//->leftJoin('payment_methods AS regComPayment','purchase_records.paymentMethod','=','regComPayment.idpaymentMethod')
					->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
					//->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					//->leftJoin('payment_methods AS nomNFPayment','nomina_employee_n_fs.idpaymentMethod','=','nomNFPayment.idpaymentMethod')
					->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					//->leftJoin('payment_methods AS liqPayment','liquidations.idpaymentMethod','=','liqPayment.idpaymentMethod')
					->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					//->leftJoin('payment_methods AS bonPayment','bonuses.idpaymentMethod','=','bonPayment.idpaymentMethod')
					->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					//->leftJoin('payment_methods AS vacPayment','vacation_premia.idpaymentMethod','=','vacPayment.idpaymentMethod')
					->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					//->leftJoin('payment_methods AS salPayment','salaries.idpaymentMethod','=','salPayment.idpaymentMethod')
					->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					//->leftJoin('payment_methods AS profPayment','profit_sharings.idpaymentMethod','=','profPayment.idpaymentMethod')
					->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
					//->leftJoin('areas AS nominaDir','worker_datas.direction','=','nominaDir.id')
					//->leftJoin('departments AS nominaDep','worker_datas.department','=','nominaDep.id')
					->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
					->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
					->leftJoin('finances','request_models.folio','=','finances.idFolio')
					->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
					//->leftJoin('areas','request_models.idAreaR','=','areas.id')
					//->leftJoin('departments','request_models.idDepartamentR','=','departments.id')
					->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
					->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[5,10,11,12])
					->where( function($query) use ($enterprise) 
					{
						if($enterprise != null)
						{
							$query->whereIn('request_models.idEnterpriseR',$enterprise)->orWhereIn('worker_datas.enterprise',$enterprise);
						}
					})
					->where( function($query) use ($project) 
					{
						if($project != null)
						{
							$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
						}
					})
					->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
					->orderBy('request_kinds.kind','ASC')
					->orderBy('request_models.folio','ASC')
					->get();

			$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
					->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
					->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->select('payments.account as account','payments.amount as amount')
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[5,10,11,12])
					->whereIn('payments.idEnterprise',$enterprise)
					->where( function($query) use ($project) 
					{
						if($project != null)
						{
							$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
						}
					})
					->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
					->get();

			$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
			$resultCollectPayments =  collect($payments)->groupBy('account');

			$accountRegister = array();
			$keyR            = 0;
			$total           = array();
			if(count($accounts)>0)
			{
				$accountRegister = array();
				$keyR = 0;
				$total = array();
				foreach ($accounts as $acc) 
				{
					$accountRegister[$keyR]['description']  = $acc->account.' '.ucwords($acc->description);
					$accountRegister[$keyR]['account']      = $acc->account;
					$accountRegister[$keyR]['selectable']   = $acc->selectable;
					$accountRegister[$keyR]['identifier']   = $acc->level;
					$accountRegister[$keyR]['father']       = $acc->father;
					$balance        = 0;
					$tempAcc                                = App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
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
			}
			$count = 0;
			foreach ($accountRegister as $acc)
			{
				if ($acc['selectable'] == 0 && $father == $acc['father'] && ($acc['identifier'] == 2 || $acc['identifier'] == 3))
				{
					if($total[$acc['account']] != 0)
					{
						//$arrayCircle[$count]['description']   = mb_convert_case($acc['description'], MB_CASE_TITLE, "UTF-8").' -'.PHP_EOL.' $'.number_format($total[$acc['account']],2);
						$arrayCircle[$count]['description'] = mb_strtoupper($acc['description'], "UTF-8");
						$arrayCircle[$count]['total']       = round($total[$acc['account']],2);

						$arrayBar[$count]['description']    = mb_strtoupper($acc['description'], "UTF-8");
						$arrayBar[$count]['total']          = round($total[$acc['account']],2);
						$count++;
					}
				}   
			}

			if ($type == "1") 
			{
				if(isset($arrayCircle))
				{
					$alert = '';
				}
				else
				{
					$arrayCircle = [];
					$alert  = "swal('', 'Sin resultados', 'error');";
					
				}

				return view('reporte.finanzas.concentrado',
				[
					'arrayResult'	=> $arrayCircle,
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'type'			=> $type,
					'option_id'		=> 132,
					'enterprise'	=> $enterprise,
					'project'		=> $request->idProject,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'father'		=> $father,
					'alert'			=> $alert,
					'projectname'	=> $projectname,
					'accountdesc'	=> $accountdesc
				]);
			}
			elseif ($type == "2")
			{
				if(isset($arrayBar))
				{
					$alert = '';
				}
				else
				{
					$arrayBar = [];
					$alert  = "swal('', 'Sin resultados', 'error');";
				}
				return view('reporte.finanzas.concentrado',
				[
					'arrayResult'	=> $arrayBar,
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'type'			=> $type,
					'option_id'		=> 132,
					'enterprise'	=> $enterprise,
					'project'		=> $request->idProject,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'father'		=> $father,
					'alert'			=> $alert,
					'projectname'	=> $projectname,
					'accountdesc'	=> $accountdesc
				]);
			}
		}
	}

	public function concentratedExcel(Request $request)
	{
		$enterprise	= $request->idEnterprise;
		$project	= $request->idProject;
		$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
		$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

		$accounts	= App\Account::select('account','description','selectable')
						->whereIn('idEnterprise',$enterprise)
						->orderBy('account','ASC')
						->groupBy('account','description','selectable')
						->get();
		

		$requests   = App\RequestModel::selectRaw('
						request_models.folio AS folio,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
						
						ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
								'
				)
				->join('status_requests','request_models.status','=','status_requests.idrequestStatus')
				->join('request_kinds','request_models.kind','=','request_kinds.idrequestkind')
				->join('users','request_models.idRequest','=','users.id')
				->join('users as elab','request_models.idElaborate','=','elab.id')
				->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
				//->leftJoin('providers','purchases.idProvider','=','providers.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','=','detail_purchases.idPurchase')
				->leftJoin('resources','request_models.folio','=','resources.idFolio')
				//->leftJoin('payment_methods AS resourcePayment','resources.idpaymentMethod','=','resourcePayment.idpaymentMethod')
				->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
				->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
				->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
				//->leftJoin('payment_methods AS refundPayment','refunds.idpaymentMethod','=','refundPayment.idpaymentMethod')
				->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
				->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
				->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
				->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
				//->leftJoin('payment_methods AS regComPayment','purchase_records.paymentMethod','=','regComPayment.idpaymentMethod')
				->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
				//->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				//->leftJoin('payment_methods AS nomNFPayment','nomina_employee_n_fs.idpaymentMethod','=','nomNFPayment.idpaymentMethod')
				->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				//->leftJoin('payment_methods AS liqPayment','liquidations.idpaymentMethod','=','liqPayment.idpaymentMethod')
				->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				//->leftJoin('payment_methods AS bonPayment','bonuses.idpaymentMethod','=','bonPayment.idpaymentMethod')
				->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				//->leftJoin('payment_methods AS vacPayment','vacation_premia.idpaymentMethod','=','vacPayment.idpaymentMethod')
				->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				//->leftJoin('payment_methods AS salPayment','salaries.idpaymentMethod','=','salPayment.idpaymentMethod')
				->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				//->leftJoin('payment_methods AS profPayment','profit_sharings.idpaymentMethod','=','profPayment.idpaymentMethod')
				->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
				//->leftJoin('areas AS nominaDir','worker_datas.direction','=','nominaDir.id')
				//->leftJoin('departments AS nominaDep','worker_datas.department','=','nominaDep.id')
				->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
				->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
				->leftJoin('finances','request_models.folio','=','finances.idFolio')
				->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
				//->leftJoin('areas','request_models.idAreaR','=','areas.id')
				//->leftJoin('departments','request_models.idDepartamentR','=','departments.id')
				->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
				->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
				->whereIn('request_models.kind',[1,8,9,16,17,18])
				->whereIn('request_models.status',[5,10,11,12])
				->where( function($query) use ($enterprise) 
				{
					if($enterprise != null)
					{
						$query->whereIn('request_models.idEnterpriseR',$enterprise)->orWhereIn('worker_datas.enterprise',$enterprise);
					}
				})
				->where( function($query) use ($project) 
				{
					if($project != null)
					{
						$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
					}
				})
				->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
				->orderBy('request_kinds.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->get();

		$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
				->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
				->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->select('payments.account as account','payments.amount as amount')
				->whereIn('request_models.kind',[1,8,9,16,17,18])
				->whereIn('request_models.status',[5,10,11,12])
				->whereIn('payments.idEnterprise',$enterprise)
				->where( function($query) use ($project) 
				{
					if($project != null)
					{
						$query->whereIn('request_models.idProjectR',$project)->orWhereIn('worker_datas.project',$project);
					}
				})
				->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
				->get();

		

		$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
		$resultCollectPayments =  collect($payments)->groupBy('account');

		if(count($accounts)>0)
		{
			$init               = 3;
			$accountRegister    = array();
			$keyR               = 0;
			$total              = array();
			
			foreach ($accounts as $acc) 
			{
				$accountRegister[$keyR]['description']  = $acc->account.' '.strtoupper($acc->description);
				$accountRegister[$keyR]['account']      = $acc->account;
				$accountRegister[$keyR]['selectable']   = $acc->selectable;
				$accountRegister[$keyR]['identifier']   = $acc->level;
				$balance                                = 0;
				$tempAcc                                = App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
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

			Excel::create('Concentrado-Cuentas', function($excel) use ($enterprise,$accountRegister,$total)
			{
				$excel->sheet('Datos',function($sheet) use ($enterprise,$accountRegister,$total)
				{
					$sheet->setWidth(array(
						'A'     => 15,
						'B'     => 15,
						'C'     => 15,
						'D'     => 15,
						'E'     => 15,
						'F'     => 15,
						'G'     => 15
					));

					$sheet->setColumnFormat(array(
							'A' => '@',
							'G' => '"$"#,##0.00_-',
						));
					$sheet->setStyle(array(
											'font' => array(
													'name'      =>  'Calibri',
													'size'      =>  12,
													'color' => ['argb' => 'EB2B02'],
												)
											));
					$sheet->mergeCells('A1:G1');
					$sheet->mergeCells('A2:F2');
					$sheet->cell('A1:G1', function($cells) {
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									$cells->setFontColor('#000000');
									$cells->setBackground('ffffff');
								});
					$sheet->cell('A2:G2', function($cells) {
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
									$cells->setFontColor('#ffffff');
									$cells->setBackground('1F3764');
								});
					$sheet->row(1,['Reporte de Cuentas']);
					$sheet->row(2,['Nombre de Cuenta', '', '', '', '', '', 'Monto']);
					$init = 3;
					foreach ($accountRegister as $acc)
					{
						if ($acc['selectable'] == 0)
						{
							$row        = [];
							$row[0]     = strtoupper($acc['description']);
							$row[1]     = '';
							$row[2]     = '';
							$row[3]     = '';
							$row[4]     = '';
							$row[5]     = '';

							$row[6]     = $total[$acc['account']];

							$sheet->appendRow($row);
							switch ($acc['identifier']) 
							{
								case 1:
									$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
									{
										$cells->setBackground('#2F75B5');
										$cells->setFontColor('#ffffff');
										$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									});
									break;
								case 2:
									$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
									{
										$cells->setBackground('#D9E1F2');
										$cells->setFontColor('#000000');
										$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
									});
									$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
									{
										$cells->setTextIndent(2);
									});
									break;
								case 3:
									$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
									{
										$cells->setFontColor('#000000');
										$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
									});
									$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
									{
										$cells->setTextIndent(4);
									});
									break;
								default:
									$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
									{
										$cells->setFontColor('#000000');
										$cells->setFont(array('family' => 'Calibri','size' => '12'));
									});
									$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
									{
										$cells->setTextIndent(6);
									});
									break;
							}
							$sheet->mergeCells('A'.$init.':'.'F'.$init);
							/*if ($acc['selectable'] == 0 && $acc['identifier'] == 1) 
							{
								$sheet->cell('A'.$init.':'.'G'.$init, function($cells) {
									  $cells->setAlignment('center');
									   $cells->setFontColor('#f00000');
									  $cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									});
							}
							elseif ($acc['selectable'] == 0 && $acc['identifier'] != 1) 
							{
								$sheet->cell('A'.$init.':'.'G'.$init, function($cells) {
									  $cells->setAlignment('center');
									  $cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
									});
							}
							*/
							$init++;
						}
					}
				});
			})->export('xlsx');
		}
		else
		{
			$alert  = "swal('', 'Sin resultados', 'error');";
			return redirect('/report/finance/concentrated')->with('alert',$alert);
		}
	}

	public function concentratedChartsBar(Request $request)
	{
		$father = $request->father;
		$accountRegister = $request->accountRegister;

		//return Response($accountRegister);
		foreach ($accountRegister as $acc)
		{
			$array[]     = [mb_strtoupper($acc[0]),$acc[1],'red'];
			
		}
		return Response($array);
	}
}
