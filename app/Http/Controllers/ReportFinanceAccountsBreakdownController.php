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

class ReportFinanceAccountsBreakdownController extends Controller
{
	private $module_id = 130;

	public function breakdownReport(Request $request)
	{
		if (Auth::user()->module->where('id',131)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$type			= $request->type!='' ? $request->type : null;
			$enterprise		= $request->idEnterprise;
			$father			= $request->father;
			$arrayResult[]	= null;
			$enterprisename	= $request->enterprisename!='' ? $request->enterprisename : null;
			$projectname	= $request->projectname!='' ? $request->projectname : null;
			$accountdesc	= $request->accountdesc!='' ? $request->accountdesc : null;

			return view('reporte.finanzas.desglose',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 131,
					'type'				=> $type,
					'enterprise'		=> $enterprise,
					'father'			=> $father,
					'arrayResult'		=> $arrayResult,
					'enterprisename'	=> $enterprisename,
					'projectname'		=> $projectname,
					'accountdesc'		=> $accountdesc
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function breakdownReportResult(Request $request)
	{
		if (Auth::user()->module->where('id',131)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$enterprise     = $request->idEnterprise;
			$project        = $request->idProject;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$father         = $request->father;
			$type           = $request->type;
			$enterprisename = App\Enterprise::select('name')
				->where('id', $enterprise)
				->get();
			$projectname = $request->projectname!='' ? $request->projectname : null;
			$accountdesc = $request->accountdesc!='' ? $request->accountdesc : null;
			$accounts    = App\Account::where('idEnterprise',$enterprise)
				->orderBy('account','ASC')
				->get();
			$requests = App\RequestModel::selectRaw('
						request_models.folio AS folio,
						IF(request_models.kind = 16, CONCAT(cat_type_payrolls.description," Folio #",request_models.folio), CONCAT(request_kinds.kind," Folio #",request_models.folio)) AS tipo,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, IF(request_models.taxPayment = 1, "Fiscal", "No fiscal"), IF(request_models.kind = 9, IF(refund_details.taxPayment = 1, "Fiscal", "No fiscal"), IF(request_models.kind = 16, IF(nomina_employees.fiscal = 1, "Fiscal", "No fiscal"), ""))) AS fiscal,
						DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i:%s") AS fecha,
						IF(request_models.kind = 1, detail_purchases.description, IF(request_models.kind = 8, resource_details.concept, IF(request_models.kind = 9, refund_details.concept, IF(request_models.kind = 18, finances.kind, IF(request_models.kind = 16, CONCAT_WS(" ", cat_type_payrolls.description, "-", real_employees.name, real_employees.last_name, real_employees.scnd_last_name), IF(request_models.kind = 17, purchase_record_details.description, "")))))) AS concept,
						ROUND(IF(request_models.kind = 1, detail_purchases.subtotal, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.amount, IF(request_models.kind = 18, finances.subtotal, IF(request_models.kind = 17, purchase_record_details.subtotal, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount
				')
				->join('status_requests','request_models.status','=','status_requests.idrequestStatus')
				->join('request_kinds','request_models.kind','=','request_kinds.idrequestkind')
				->join('users','request_models.idRequest','=','users.id')
				->join('users as elab','request_models.idElaborate','=','elab.id')
				->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
				//->leftJoin('provider','purchases.idProvider','=','provider.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','=','detail_purchases.idPurchase')
				->leftJoin('resources','request_models.folio','=','resources.idFolio')
				//->leftJoin('paymentMethod AS resourcePayment','resources.idpaymentMethod','=','resourcePayment.idpaymentMethod')
				->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
				->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
				->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
				//->leftJoin('paymentMethod AS refundPayment','refunds.idpaymentMethod','=','refundPayment.idpaymentMethod')
				->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
				->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
				->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
				->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
				//->leftJoin('paymentMethod AS regComPayment','purchase_records.paymentMethod','=','regComPayment.idpaymentMethod')
				->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
				->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				//->leftJoin('paymentMethod AS nomNFPayment','nomina_employee_n_fs.idpaymentMethod','=','nomNFPayment.idpaymentMethod')
				->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				//->leftJoin('paymentMethod AS liqPayment','liquidations.idpaymentMethod','=','liqPayment.idpaymentMethod')
				->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				//->leftJoin('paymentMethod AS bonPayment','bonuses.idpaymentMethod','=','bonPayment.idpaymentMethod')
				->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				//->leftJoin('paymentMethod AS vacPayment','vacation_premia.idpaymentMethod','=','vacPayment.idpaymentMethod')
				->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				//->leftJoin('paymentMethod AS salPayment','salaries.idpaymentMethod','=','salPayment.idpaymentMethod')
				->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				//->leftJoin('paymentMethod AS profPayment','profit_sharings.idpaymentMethod','=','profPayment.idpaymentMethod')
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
				->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
				->where(function ($query) use ($mindate,$maxdate,$enterprise,$project)
				{
					if($enterprise != '')
					{
						$query->where(function ($q) use($enterprise)
						{
							$q->where('request_models.idEnterpriseR',$enterprise)
							->orWhere('worker_datas.enterprise',$enterprise);
						});
					}
					if($project != '')
					{
						$query->where(function ($q) use($project)
						{
							$q->whereIn('request_models.idProjectR',$project)
							->orWhereIn('worker_datas.project',$project);
						});
					}
				})
				->orderBy('request_kinds.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->get();
	
			$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
					->leftJoin('accounts','payments.account','=','accounts.idAccAcc')
					->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
					->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->selectRaw(
						'
						payments.idFolio as folio,
						CONCAT("Pago Folio #",payments.idFolio) as tipo,
						CONCAT(accounts.account," ",accounts.description," (",accounts.content,")") as accounts,
						payments.account as idAccAccReport,
						IF(request_models.taxPayment = 1, "Fiscal", "No fiscal") as fiscal,
						payments.paymentDate as fecha,
						CONCAT(payments.idFolio," - ",payments.commentaries) as concept,
						payments.amount as amount
						'
						)
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[5,10,11,12])
					->where( function($query) use ($project,$enterprise) 
					{
						if($enterprise != '')
						{
							$query->where(function ($q) use($enterprise)
							{
								$q->where('request_models.idEnterpriseR',$enterprise)
								->orWhere('worker_datas.enterprise',$enterprise);
							});
						}
						if($project != '')
						{
							$query->where(function ($q) use($project)
							{
								$q->whereIn('request_models.idProjectR',$project)
								->orWhereIn('worker_datas.project',$project);
							});
						}
					})
					->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
					->get();
	
			$resultCollectAccounts =  collect($requests)->groupBy('idAccAccReport');
			$resultCollectPayments =  collect($payments)->groupBy('idAccAccReport');
			$init = 3;
	
			$accountRegister = array();
			$keyR            = 0;
			$total           = array();
			if (count($accounts)>0) 
			{
				foreach ($accounts as $acc) 
				{
					$accountRegister[$keyR]['description']  = $acc->account.' '.ucwords($acc->description);
					$accountRegister[$keyR]['father']       = $acc->father;
					$accountRegister[$keyR]['account']      = $acc->account;
					$accountRegister[$keyR]['selectable']   = $acc->selectable;
					$accountRegister[$keyR]['identifier']   = $acc->level;
					$balance        = 0;

					if(isset($resultCollectAccounts[$acc->idAccAcc]))
					{
						$balance += $resultCollectAccounts[$acc->idAccAcc]->sum('amount');
					}
					if(isset($resultCollectPayments[$acc->idAccAcc]))
					{
						$balance += $resultCollectPayments[$acc->idAccAcc]->sum('amount');
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
						$new = App\Account::where('account',$acc->father)->where('idEnterprise',$enterprise)->first();
						
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
							$new_a = App\Account::where('account',$new->father)->where('idEnterprise',$enterprise)->first();
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
								$new_b = App\Account::where('account',$new_a->father)->where('idEnterprise',$enterprise)->first();
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
						$arrayCircle[$count]['description'] = mb_strtoupper($acc['description'], "UTF-8");
						$arrayCircle[$count]['total']       = round($total[$acc['account']],2);
	
						$arrayBar[$count]['description']    = mb_strtoupper($acc['description'], "UTF-8");
						$arrayBar[$count]['total']          = round($total[$acc['account']],2);
						$count++;
					}
				}
			}
			if ($type == 1) 
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

				return view('reporte.finanzas.desglose',
				[
					'arrayResult'=> $arrayCircle,
					'id'        =>$data['father'],
					'title'     =>$data['name'],
					'details'   =>$data['details'],
					'child_id'  =>$this->module_id,
					'option_id' =>131,
					'enterprise'=> $enterprise,
					'project'   => $project,
					'mindate'   => $request->mindate,
					'maxdate'   => $request->maxdate,
					'father'    => $father,
					'type'      => $type,
					'alert'     => $alert,
					'enterprisename' => $enterprisename,
					'projectname' => $projectname,
					'accountdesc' => $accountdesc
				]);
			}
			elseif ($type == 2)
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
				return view('reporte.finanzas.desglose',
				[
					'arrayResult'		=> $arrayBar,
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 131,
					'enterprise'		=> $enterprise,
					'project'			=> $project,
					'mindate'			=> $request->mindate,
					'maxdate'			=> $request->maxdate,
					'father'			=> $father,
					'type'				=> $type,
					'alert'				=> $alert,
					'enterprisename'	=> $enterprisename,
					'projectname'		=> $projectname,
					'accountdesc'		=> $accountdesc
				]);
			}   
		}
		else
		{
			return abort(404);
		}
	}

	public function breakdownExcel(Request $request)
	{
		$enterprise		= $request->idEnterprise;
		$rfcEnterprise	= App\Enterprise::find($enterprise)->rfc;
		$project		= $request->idProject;
		$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
		$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
		$accounts		= App\Account::where('idEnterprise',$enterprise)
			->orderBy('account','ASC')
			->get();
		$init = 3;
		$requestsStationery = App\RequestModel::select('request_models.accountR as accountRequest','lots.account as accountWarehouse','lots.subtotal as subtotalWarehouse','stationeries.subtotal as subtotalRequest')
			->leftJoin('stationeries','request_models.folio','=','stationeries.idFolio')
			->leftJoin('detail_stationeries','stationeries.idStationery','=','detail_stationeries.idStat')
			->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
			->leftJoin('warehouses','detail_stationeries.idwarehouse','=','warehouses.idwarehouse')
			->leftJoin('lots','warehouses.idLot','=','lots.idlot')
			->where('request_models.kind',7)
			->whereIn('request_models.status',[5,9,10,11,12])
			->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->whereNotNull('lots.account')
			->where( function($query) use ($enterprise) 
			{
				if($enterprise != null)
				{
					$query->where('request_models.idEnterpriseR',$enterprise);
				}
			})
			->where( function($query) use ($project) 
			{
				if($project != null)
				{
					$query->whereIn('request_models.idProjectR',$project);
				}
			})
			->orderBy('kind','ASC')
			->orderBy('request_models.folio','ASC')
			->get();

		$requestsComputer   = App\RequestModel::select('request_models.accountR as accountRequest','computer_equipments.account as accountComputer','computers.subtotal as subtotalRequest','computer_equipments.subtotal as subtotalComputer')
			->leftJoin('computers','computers.idFolio','=','request_models.folio')
			->leftJoin('computer_equipments','computer_equipments.id','=','computers.idComputerEquipment')
			->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
			->where('request_models.kind',6)
			->whereIn('request_models.status',[5,9,10,11,12])
			->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->where( function($query) use ($enterprise) 
			{
				if($enterprise != null)
				{
					$query->where('request_models.idEnterpriseR',$enterprise);
				}
			})
			->where( function($query) use ($project) 
			{
				if($project != null)
				{
					$query->whereIn('request_models.idProjectR',$project);
				}
			})
			->orderBy('kind','ASC')
			->orderBy('request_models.folio','ASC')
			->get();
		$requests   = App\RequestModel::selectRaw('
						request_models.folio AS folio,
						IF(request_models.kind = 16, CONCAT(cat_type_payrolls.description," Folio #",request_models.folio), CONCAT(request_kinds.kind," Folio #",request_models.folio)) AS tipo,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), 
							IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), 
								IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), 
									IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""
									)
								)
							)
						) AS accounts,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, 
							IF(request_models.kind = 8, resAcc.idAccAcc, 
								IF(request_models.kind = 9, refAcc.idAccAcc, 
									IF(request_models.kind = 16, nomAccount.idAccAcc,""
									)
								)
							)
						) AS idAccAccReport,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, IF(request_models.taxPayment = 1, "Fiscal", "No fiscal"), 
							IF(request_models.kind = 9, IF(refund_details.taxPayment = 1, "Fiscal", "No fiscal"), 
								IF(request_models.kind = 16, IF(nomina_employees.fiscal = 1, "Fiscal", "No fiscal"), ""
								)
							)
						) AS fiscal,
						DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i:%s") AS fecha,
						IF(request_models.kind = 1, detail_purchases.description, 
							IF(request_models.kind = 8, resource_details.concept, 
								IF(request_models.kind = 9, refund_details.concept, 
									IF(request_models.kind = 18, finances.kind, 
										IF(request_models.kind = 16, CONCAT_WS(" ", cat_type_payrolls.description, "-", real_employees.name, real_employees.last_name, real_employees.scnd_last_name), 
											IF(request_models.kind = 17, purchase_record_details.description, ""
											)
										)
									)
								)
							)
						) AS concept,
						ROUND(
							IF(request_models.kind = 1, detail_purchases.subtotal, 
								IF(request_models.kind = 8, resource_details.amount, 
									IF(request_models.kind = 9, refund_details.amount, 
										IF(request_models.kind = 18, finances.subtotal, 
											IF(request_models.kind = 17, purchase_record_details.subtotal, 
												IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, 
													IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, 
														IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, 
															IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, 
																IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount
																)
															)
														)
													)
												)
											)
										)
									)
								)
							)
							,2) AS amount
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
			->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
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
			->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
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
			->orderBy('request_kinds.kind','ASC')
			->orderBy('request_models.folio','ASC')
			->get();

		$payments = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
			->leftJoin('accounts','payments.account','=','accounts.idAccAcc')
			->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
			->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
			->selectRaw(
				'
				payments.idFolio as folio,
				CONCAT("Pago Folio #",payments.idFolio) as tipo,
				CONCAT(accounts.account," ",accounts.description," (",accounts.content,")") as accounts,
				payments.account as idAccAccReport,
				IF(request_models.taxPayment = 1, "Fiscal", "No fiscal") as fiscal,
				payments.paymentDate as fecha,
				CONCAT(payments.idFolio," - ",payments.commentaries) as concept,
				payments.subtotal as amount
				'
				)
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
			->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->get();
		$sales = App\Bill::selectRaw(
				'bills.idBill as folio,
				CONCAT("Factura de", "Venta") as tipo,
				CONCAT("Cuenta ","de Ventas") as accounts,
				"-" as idAccAccReport,
				"Fiscal" as fiscal,
				bills.expeditionDate as fecha,
				CONCAT("Factura # ",bills.idBill) as concept,
				bills.subtotal as subtotal
			')
			->where('type','I')
			->whereNotNull('folioRequest')
			->whereBetween('expeditionDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->where('rfc',$rfcEnterprise)
			->whereIn('statusConciliation',[0,1])
			->get();

		$income = App\ConciliationMovementBill::selectRaw(
				'bills.idBill as folio,
				CONCAT("Factura de", "Venta") as tipo,
				CONCAT("Cuenta ","de Ventas") as accounts,
				movements.idAccount as accountMovement,
				"Fiscal" as fiscal,
				bills.expeditionDate as fecha,
				CONCAT("Factura # ",bills.idBill) as concept,
				bills.subtotal as subtotalBill')
			->leftJoin('bills','bills.idBill','=','conciliation_movement_bills.idbill')
			->leftJoin('movements','movements.idmovement','=','conciliation_movement_bills.idmovement')
			->whereBetween('bills.expeditionDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->where('bills.rfc',$rfcEnterprise)
			->get();

		$warehouse = App\Lot::selectRaw(
				'lots.idlot as folio,
				CONCAT("Lote de", "Articulos") as tipo,
				CONCAT("Cuenta ","de Lote") as accounts,
				"" as fiscal,
				lots.date as fecha,
				CONCAT("Lote #",lots.idlot) as concept,
				lots.account as accountLot,
				request_models.accountR as accountPurchase,
				lots.subtotal as subtotalLot,
				purchases.subtotales as subtotalPurchase
				')
			->leftJoin('request_models','lots.idFolio','request_models.folio')
			->leftJoin('purchases','request_models.folio','purchases.idFolio')
			->where('lots.idEnterprise',$enterprise)
			->whereBetween('lots.date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->get();

		$warehouseComputer = App\ComputerEquipment::selectRaw(
				'computer_equipments.id as folio,
				CONCAT("Equipo de", "Computo") as tipo,
				CONCAT("Cuenta ","de Computo") as accounts,
				CONCAT("Equipo de", "Computo") as fiscal,
				computer_equipments.date as fecha,
				CONCAT("Equipo ",computer_equipments.brand) as concept,
				computer_equipments.account as account,
				computer_equipments.subtotal as subtotal
			')
			->where('idEnterprise',$enterprise)
			->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
			->get();

		$resultCollectIncome             = collect($income)->groupBy('accountMovement');
		$resultCollectAccounts           = collect($requests)->groupBy('idAccAccReport');
		$resultCollectPayments           = collect($payments)->groupBy('idAccAccReport');
		$resultCollectWarehouse          = collect($warehouse)->groupBy('accountLot');
		$resultCollectWarehousePurchase  = collect($warehouse)->groupBy('accountPurchase');
		$resultCollectWarehouseComputer  = collect($warehouseComputer)->groupBy('account');
		$resultCollectComputerDelivery   = collect($requestsComputer)->groupBy('accountComputer');
		$resultCollectRequestComputer    = collect($requestsComputer)->groupBy('accountRequest');
		$resultCollectStationeryDelivery = collect($requestsStationery)->groupBy('accountWarehouse');
		$resultCollectRequestStationery  = collect($requestsStationery)->groupBy('accountRequest');

		//return $resultCollectPayments;
		$accountRegister = array();
		$keyR            = 0;
		$total           = array();
		if (count($accounts)>0) 
		{
			foreach ($accounts as $acc) 
			{
				$accountRegister[$keyR]['description']  = $acc->account.' '.strtoupper($acc->description);
				$accountRegister[$keyR]['account']      = $acc->account;
				$accountRegister[$keyR]['selectable']   = $acc->selectable;
				$accountRegister[$keyR]['identifier']   = $acc->level;
				$balance                                = 0;
				$keyConcept = 0;

				


				if(isset($resultCollectAccounts[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectAccounts[$acc->idAccAcc];
					$balance    += $resultCollectAccounts[$acc->idAccAcc]->sum('amount');
					$keyConcept++;
				}
				if(isset($resultCollectPayments[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectPayments[$acc->idAccAcc];
					$balance    -= $resultCollectPayments[$acc->idAccAcc]->sum('amount');
					$keyConcept++;
				}
				if(isset($resultCollectWarehouse[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectWarehouse[$acc->idAccAcc];
					$balance    += $resultCollectWarehouse[$acc->idAccAcc]->sum('subtotalLot');
					$keyConcept++;
				}
				if(isset($resultCollectWarehouseComputer[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectWarehouseComputer[$acc->idAccAcc];
					$balance    += $resultCollectWarehouseComputer[$acc->idAccAcc]->sum('subtotal');
					$keyConcept++;
				}

				if(isset($resultCollectComputerDelivery[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectComputerDelivery[$acc->idAccAcc];
					$balance    -= $resultCollectComputerDelivery[$acc->idAccAcc]->sum('subtotalRequest');
					$keyConcept++;
				}
				if(isset($resultCollectStationeryDelivery[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectStationeryDelivery[$acc->idAccAcc];
					$balance    -= $resultCollectStationeryDelivery[$acc->idAccAcc]->sum('subtotalRequest');
					$keyConcept++;
				}
				if(isset($resultCollectIncome[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectIncome[$acc->idAccAcc];
					$balance    += $resultCollectIncome[$acc->idAccAcc]->sum('subtotalBill');
					$keyConcept++;
				}
				
				if(isset($resultCollectRequestComputer[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectRequestComputer[$acc->idAccAcc];
					$balance    += $resultCollectRequestComputer[$acc->idAccAcc]->sum('subtotalRequest');
					$keyConcept++;
				}

				if(isset($resultCollectRequestStationery[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectRequestStationery[$acc->idAccAcc];
					$balance    += $resultCollectRequestStationery[$acc->idAccAcc]->sum('subtotalRequest');
					$keyConcept++;
				}

				if(isset($resultCollectWarehousePurchase[$acc->idAccAcc]))
				{
					$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectWarehousePurchase[$acc->idAccAcc];
					$balance    -= $resultCollectWarehousePurchase[$acc->idAccAcc]->sum('subtotalPurchase');
					$keyConcept++;
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
					$new = App\Account::where('account',$acc->father)->where('idEnterprise',$enterprise)->first();
					
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
						$new_a = App\Account::where('account',$new->father)->where('idEnterprise',$enterprise)->first();
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
							$new_b = App\Account::where('account',$new_a->father)->where('idEnterprise',$enterprise)->first();
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

			$total['4000000']   = $income->sum('subtotal');
			$total['4100000']   = $income->sum('subtotal');
			
			$total['4000000']   = $sales->sum('subtotal');
			$total['4100000']   = $sales->sum('subtotal');

			Excel::create('Reporte-Cuentas - '.App\Enterprise::find($enterprise)->name, function($excel) use ($enterprise,$accountRegister,$total)
			{
				$excel->sheet('Datos',function($sheet) use ($enterprise,$accountRegister,$total)
				{
					$sheet->setWidth(array(
						'A'     => 3,
						'B'     => 3,
						'C'     => 3,
						'D'     => 25,
						'E'     => 25,
						'F'     => 25,
						'G'     => 25,
						'H'     => 25,
						'I'     => 25,
					));

					$sheet->setColumnFormat(array(
							'A' => '@',
							'I' => '"$"#,##0.00_-',
						));
					$sheet->setStyle(array(
											'font' => array(
													'name'      =>  'Calibri',
													'size'      =>  12,
													'color' => ['argb' => 'EB2B02'],
												)
											));
					$sheet->mergeCells('A1:I1');
					$sheet->mergeCells('A2:H2');
					$sheet->cell('A1:I1', function($cells) {
								  $cells->setFontWeight('bold');
								  $cells->setAlignment('center');
								  $cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
								  $cells->setFontColor('#000000');
								  $cells->setBackground('ffffff');
								});
					$sheet->cell('A2:I2', function($cells) {
								  $cells->setFontWeight('bold');
								  $cells->setAlignment('center');
								  $cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
								  $cells->setFontColor('#ffffff');
								  $cells->setBackground('1F3764');
								});
					$sheet->row(1,['Reporte de Cuentas']);
					$sheet->row(2,['Nombre de Cuenta', '', '', '', '', '', '', '', 'Monto']);
					$init = 3;
					foreach ($accountRegister as $acc)
					{
						$row        = [];
						$row[0]     = strtoupper($acc['description']);
						$row[1]     = '';
						$row[2]     = '';
						$row[3]     = '';
						$row[4]     = '';
						$row[5]     = '';
						$row[6]     = '';
						$row[7]     = '';
						$row[8]     = $total[$acc['account']];

						$sheet->appendRow($row);
						switch ($acc['identifier']) 
						{
							case 1:
								$sheet->cell('A'.$init.':'.'I'.$init, function($cells) 
								{
									$cells->setBackground('#2F75B5');
									$cells->setFontColor('#ffffff');
									$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
								});
								break;
							case 2:
								$sheet->cell('A'.$init.':'.'I'.$init, function($cells) 
								{
									$cells->setBackground('#D9E1F2');
									$cells->setFontColor('#000000');
									$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
								});
								$sheet->cell('A'.$init.':'.'H'.$init, function($cells) 
								{
									$cells->setTextIndent(2);
								});
								break;
							case 3:
								$sheet->cell('A'.$init.':'.'I'.$init, function($cells) 
								{
									$cells->setFontColor('#000000');
									$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
								});
								$sheet->cell('A'.$init.':'.'H'.$init, function($cells) 
								{
									$cells->setTextIndent(4);
								});
								break;
							default:
								$sheet->cell('A'.$init.':'.'I'.$init, function($cells) 
								{
									$cells->setFontColor('#000000');
									$cells->setFont(array('family' => 'Calibri','size' => '12'));
								});
								$sheet->cell('A'.$init.':'.'H'.$init, function($cells) 
								{
									$cells->setTextIndent(6);
								});
								break;
						}
						$sheet->mergeCells('A'.$init.':'.'H'.$init);
						/*if ($acc['selectable'] == 0 && $acc['identifier'] == 1) 
						{
							$sheet->cell('A'.$init.':'.'F'.$init, function($cells) {
								$cells->setFontColor('#f00000');
							});
						}
						elseif ($acc['selectable'] == 0 && $acc['identifier'] != 1) 
						{
							$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
							{
								$cells->setFontColor('#f00000');
							});
						}
						else
						{
						}*/
						$init++;

						if (isset($acc['concepts']) && count($acc['concepts'])>0) 
						{
							$sheet->row($init,['', '', '', 'Tipo', 'Fecha', 'Cuenta', 'Fiscal', 'Concepto', '']);
							$sheet->cell('D'.$init.':'.'H'.$init, function($cells) {
								$cells->setFontWeight('bold');
								$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
								$cells->setFontColor('#000000');
								$cells->setBackground('#D9E1F2');
							});
							$init++;
							foreach ($acc['concepts'] as $concept) 
							{
								foreach ($concept as $c) {
									$row    = [];
									$row[]  = "";
									$row[]  = "";
									$row[]  = "";
									$row[]  = isset($c['tipo']) ? $c['tipo'] : 'Pago';
									$row[]  = $c['fecha'];
									$row[]  = $c['accounts'];
									$row[]  = $c['fiscal'];
									$row[]  = $c['concept'];
									$row[]  = $c['amount'];
									$sheet->appendRow($row);
									$sheet->cell('D'.$init.':'.'H'.$init, function($cells) {
									  $cells->setFont(array('family' => 'Calibri','size' => '12'));
									});
									$init++;
								}
								
							}
						}
					}
				});
			})->export('xlsx');
		}
		else
		{
			$alert  = "swal('', 'Sin resultados', 'error');";
			return redirect('/report/finance/breakdown')->with('alert',$alert);
		}
		
	}
}
