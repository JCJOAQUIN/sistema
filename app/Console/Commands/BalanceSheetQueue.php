<?php

namespace App\Console\Commands;

use App;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Excel;
use PHPExcel_Cell;
use Illuminate\Support\Facades\DB;

class BalanceSheetQueue extends Command
{
	protected $signature = 'queue:balancesheet';

	protected $description = 'Generar reportes de balance general y estado de resultados';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$reports = App\BalanceSheet::where('status',0)->get();

		foreach ($reports as $report) 
		{
			$enterprise 	= $report->dataEnterprise->idEnterprise;
			$rfcEnterprise 	= App\Enterprise::find($enterprise)->rfc;
			$project 		= $report->dataProject()->exists() ? $report->dataProject : '' ;
			$year    		= $report->dataYears->year;
			$monthsTemp 	= $report->dataMonths;
			$type  			= $report->type;

			$months 		= array();
			foreach ($monthsTemp as $temp) 
			{
				array_push($months, $temp->month);
			}

			$accountsBalance	= App\Account::where('idEnterprise',$enterprise)
								->where(function($query)
								{
									$query->where('account','like','1%')->orWhere('account','like','2%')->orWhere('account','like','3%');
								})
								->orderBy('account','ASC')
								->get();

			$accountsStatement	= App\Account::where('idEnterprise',$enterprise)
								->where(function($query)
								{
									$query->where('account','like','4%')->orWhere('account','like','5%');
								})
								->orderBy('account','ASC')
								->get();

			$arrayChart					= [];
			$accountRegister			= array();
			$accountRegisterStatement	= array();
			$accountsGraphs				= array();
			$total						= array();
			$count 						= 0;
			
			if ($report->type == 1) 
			{
				$requestsStationery = App\RequestModel::select('request_models.accountR as accountRequest','lots.account as accountWarehouse','lots.subtotal as subtotalWarehouse','stationeries.subtotal as subtotalRequest')
									->leftJoin('stationeries','request_models.folio','=','stationeries.idFolio')
									->leftJoin('detail_stationeries','stationeries.idStationery','=','detail_stationeries.idStat')
									->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
									->leftJoin('warehouses','detail_stationeries.idwarehouse','=','warehouses.idwarehouse')
									->leftJoin('lots','warehouses.idLot','=','lots.idlot')
									->where('request_models.kind',7)
									->whereIn('request_models.status',[5,9,10,11,12])
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
									->whereYear('request_models.fDate',$year)
									->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $months).')')
									->orderBy('kind','ASC')
									->orderBy('request_models.folio','ASC')
									->get();

				$requestsComputer 	= App\RequestModel::select('request_models.accountR as accountRequest','computer_equipments.account as accountComputer','computers.subtotal as subtotalRequest','computer_equipments.subtotal as subtotalComputer')
						->leftJoin('computers','computers.idFolio','=','request_models.folio')
						->leftJoin('computer_equipments','computer_equipments.id','=','computers.idComputerEquipment')
						->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
						->where('request_models.kind',6)
						->whereIn('request_models.status',[5,9,10,11,12])
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
						->whereYear('request_models.fDate',$year)
						->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $months).')')
						->orderBy('kind','ASC')
						->orderBy('request_models.folio','ASC')
						->get();
				$requests 	= App\RequestModel::selectRaw('
									request_models.folio AS folio,
									IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"),IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,

									IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,

									IF(request_models.kind = 1  OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
									
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
							->whereYear('request_models.fDate',$year)
							->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $months).')')
							->orderBy('request_kinds.kind','ASC')
							->orderBy('request_models.folio','ASC')
							->get();

				$payments	= App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
							->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
							->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
							->select('payments.account as account','payments.subtotal as subtotal')
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
							->whereYear('request_models.fDate',$year)
							->whereRaw('MONTH(request_models.fDate) IN('.implode(',', $months).')')
							->get();

				$sales 		= App\Bill::where('type','I')
							->whereNotNull('folioRequest')
							->whereYear('expeditionDate',$year)
							->whereRaw('MONTH(expeditionDate) IN('.implode(',', $months).')')
							->where('rfc',$rfcEnterprise)
							->whereIn('statusConciliation',[0,1])
							->get();

				$income 	= App\ConciliationMovementBill::leftJoin('bills','bills.idBill','=','conciliation_movement_bills.idbill')
							->leftJoin('movements','movements.idmovement','=','conciliation_movement_bills.idmovement')
							->select('bills.subtotal as subtotalBill','movements.amount as subtotalMovement','movements.idAccount as accountMovement')
							->whereYear('bills.expeditionDate',$year)
							->whereRaw('MONTH(bills.expeditionDate) IN('.implode(',', $months).')')
							->where('bills.rfc',$rfcEnterprise)
							->get();

				$warehouse = App\Lot::select('request_models.accountR as accountPurchase','purchases.subtotales as subtotalPurchase','lots.account as accountLot','lots.subtotal as subtotalLot')
							->leftJoin('request_models','lots.idFolio','request_models.folio')
							->leftJoin('purchases','request_models.folio','purchases.idFolio')
							->where('lots.idEnterprise',$enterprise)
							->whereYear('lots.date',$year)
							->whereRaw('MONTH(lots.date) IN('.implode(',', $months).')')
							->get();

				$warehouseComputer = App\ComputerEquipment::where('idEnterprise',$enterprise)
									->whereYear('date',$year)
									->whereRaw('MONTH(date) IN('.implode(',', $months).')')
									->get();


						 // SUMAR A LA 4100000

				$resultCollectIncome 				= collect($income)->groupBy('accountMovement');
				$resultCollectAccounts				= collect($requests)->groupBy('idAccAccReport');
				$resultCollectPayments				= collect($payments)->groupBy('account');
				$resultCollectWarehouse				= collect($warehouse)->groupBy('accountLot');
				$resultCollectWarehousePurchase		= collect($warehouse)->groupBy('accountPurchase');
				$resultCollectWarehouseComputer		= collect($warehouseComputer)->groupBy('account');
				$resultCollectComputerDelivery		= collect($requestsComputer)->groupBy('accountComputer');
				$resultCollectRequestComputer		= collect($requestsComputer)->groupBy('accountRequest');
				$resultCollectStationeryDelivery	= collect($requestsStationery)->groupBy('accountWarehouse');
				$resultCollectRequestStationery		= collect($requestsStationery)->groupBy('accountRequest');


				if(count($accountsBalance)>0)
				{
					$init  	= 3;
					$keyR 	= 0;
					
					foreach ($accountsBalance as $acc) 
					{
						$accountRegister[$keyR]['description']	= $acc->account.' '.strtoupper($acc->description);
						$accountRegister[$keyR]['account']		= $acc->account;
						$accountRegister[$keyR]['selectable']	= $acc->selectable;
						$accountRegister[$keyR]['identifier']	= $acc->level;
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
								$balance    -= $resultCollectPayments[$ta]->sum('subtotal');
							}
							if(isset($resultCollectWarehouse[$ta]))
							{
								$balance    += $resultCollectWarehouse[$ta]->sum('subtotalLot');
							}
							if(isset($resultCollectWarehouseComputer[$ta]))
							{
								$balance    += $resultCollectWarehouseComputer[$ta]->sum('subtotal');
							}

							if(isset($resultCollectComputerDelivery[$ta]))
							{
								$balance    -= $resultCollectComputerDelivery[$ta]->sum('subtotalRequest');
							}
							if(isset($resultCollectStationeryDelivery[$ta]))
							{
								$balance    -= $resultCollectStationeryDelivery[$ta]->sum('subtotalRequest');
							}
							if(isset($resultCollectIncome[$ta]))
							{
								$balance    += $resultCollectIncome[$ta]->sum('subtotalBill');
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
				if(count($accountsStatement)>0)
				{
					$init  	= 3;
					$keyR 	= 0;
					
					foreach ($accountsStatement as $acc) 
					{
						$accountRegisterStatement[$keyR]['description']			= $acc->account.' '.strtoupper($acc->description);
						$accountRegisterStatement[$keyR]['descriptionGraph']	= strtoupper($acc->description);
						$accountRegisterStatement[$keyR]['account']		= $acc->account;
						$accountRegisterStatement[$keyR]['selectable']	= $acc->selectable;
						$accountRegisterStatement[$keyR]['identifier']	= $acc->level;
						$accountRegisterStatement[$keyR]['father']		= $acc->father;
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
								$balance    -= $resultCollectPayments[$ta]->sum('subtotal');
							}
							if(isset($resultCollectRequestComputer[$ta]))
							{
								$balance    += $resultCollectRequestComputer[$ta]->sum('subtotalRequest');
							}

							if(isset($resultCollectRequestStationery[$ta]))
							{
								$balance    += $resultCollectRequestStationery[$ta]->sum('subtotalRequest');
							}

							if(isset($resultCollectWarehousePurchase[$ta]))
							{
								$balance    -= $resultCollectWarehousePurchase[$ta]->sum('subtotalPurchase');
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
				$total['4000000'] = $income->sum('subtotal');
				$total['4100000'] = $income->sum('subtotal');

				$total['4000000']				= $sales->sum('subtotal');
				$total['4100000']				= $sales->sum('subtotal');
				
				$total['ventas']				= $total['4000000'];
				$total['costo_ventas']			= $total['5100000'];
				$total['utilidad_bruta']		= $total['ventas'] - $total['costo_ventas'];
				
				$total['gastos_ventas']			= $total['5200000'];
				$total['gastos_operacion']		= $total['5300000'];
				$total['gastos_administracion']	= $total['5400000'];
				$total['utilidad_operacion']	= $total['utilidad_bruta'] - $total['gastos_ventas'] - $total['gastos_operacion'] - $total['gastos_administracion'];
				
				$total['gastos_financieros']	= $total['5500000'] + $total['5600000'];
				$total['utilidad_isr_ptu']		= $total['utilidad_operacion'] - $total['gastos_financieros'];
				$total['utilidad_neta']			= $total['utilidad_isr_ptu'];
				
				$total['margen_uti_bruta']		= $total['ventas'] != 0 ? $total['utilidad_bruta']/$total['ventas'] : 0;
				$total['margen_uti_op']			= $total['ventas'] != 0 ? $total['utilidad_operacion']/$total['ventas'] : 0;
				$total['margen_uti_neta']		= $total['ventas'] != 0 ? $total['utilidad_neta']/$total['ventas'] : 0;

				$total['resumen_ventas']	= $total['ventas'];
				$total['resumen_ingresos']	= $total['1101000']+$total['1102000']+$total['1103000']+$total['1104000']+$total['1105000'];
				$total['resumen_gastos']	= $total['gastos_financieros']+$total['gastos_administracion']+$total['gastos_operacion']+$total['gastos_ventas']+$total['costo_ventas'];

				//return $total;

				$entName	= App\Enterprise::find($enterprise)->name.' '.$year;
				$fileName	= 'AdG'.round(microtime(true) * 1000).'_report';
				
				Excel::create($fileName, function($excel) use ($enterprise,$accountRegister,$total,$year,$accountRegisterStatement)
				{
					$excel->sheet('BalanceGeneral'.$year,function($sheet) use ($enterprise,$accountRegister,$total,$year)
					{
						$sheet->setWidth(array(
							'A'     => 15,
							'B'     => 15,
							'C'     => 15,
							'D'     => 25,
							'E'     => 25,
							'F'     => 15,
							'G'		=> 30,
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
						$sheet->cell('A1:G1', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});
						$sheet->row(1,['BALANCE GENERAL']);
						$sheet->row(2,['']);
						$init = 3;
						foreach ($accountRegister as $acc)
						{
							$row	= [];
							$row[]	= strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]	= '';
							}
							$row[]	= $total[$acc['account']];

							$sheet->appendRow($row);
							$sheet->mergeCells('A'.$init.':'.'F'.$init);

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
							$init++;
						}
					});

					$excel->sheet('EstadoResultados'.$year,function($sheet) use ($enterprise,$accountRegisterStatement,$total,$year)
					{
						$sheet->setWidth(array(
							'A'     => 15,
							'B'     => 15,
							'C'     => 15,
							'D'     => 25,
							'E'     => 25,
							'F'     => 15,
							'G'		=> 30,
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
						$sheet->cell('A1:G1', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});
						$sheet->row(1,['ESTADO DE RESULTADOS']);
						$sheet->row(2,['']);
						$init = 3;
						foreach ($accountRegisterStatement as $acc)
						{
							$row	= [];
							$row[]	= strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]	= '';
							}
							$row[]	= $total[$acc['account']];

							$sheet->appendRow($row);
							$sheet->mergeCells('A'.$init.':'.'F'.$init);

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
							$init++;
						}

						
						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2 || $acc['identifier'] == 3 || $acc['identifier'] == 4) 
							{
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								$row[]	= $total[$acc['account']];

								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

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
								$init++;
							}
						}
						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2 || $acc['identifier'] == 3) 
							{
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								$row[]	= $total[$acc['account']];

								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

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
								$init++;
							}
						}

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2) 
							{
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								$row[]	= $total[$acc['account']];

								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

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
								$init++;
							}
						}

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;

						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						// ------ utilidad bruta ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['ventas'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'COSTO DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['costo_ventas'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['utilidad_bruta'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;

						// ------ utilidad de operation ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['gastos_ventas'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['gastos_operacion'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE ADMINISTRACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['gastos_administracion'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['utilidad_operacion'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;


						// ------ utilidad antes de isr y ptu ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS FINANCIEROS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['gastos_financieros'];	
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD ANTES ISR Y PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['utilidad_isr_ptu'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;


						// ------ utilidad neta ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'ISR';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= 0;
							
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= 0;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['utilidad_neta'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;


						$row		= [];
						for ($i=0; $i < 7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['margen_uti_bruta'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['margen_uti_op'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						$row[]	= $total['margen_uti_neta'];
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;
					});
					
					$excel->sheet('Resumen'.$year,function($sheet) use ($enterprise,$accountRegister,$total,$year)
					{
						$sheet->setWidth(array(
							'A'     => 30,
							'B'     => 25,
						));
						$sheet->setColumnFormat(array(
								'A' => '@',
								'B' => '"$"#,##0.00_-',
							));
						$sheet->setStyle(array(
												'font' => array(
														'name'      =>  'Calibri',
														'size'      =>  12,
														'color' => ['argb' => 'EB2B02'],
													)
												));
						$sheet->mergeCells('A1:B1');
						$sheet->cell('A1:B1', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});
						$sheet->row(1,['RESUMEN']);
						$sheet->row(2,['','2020']);
						$init = 3;
						$row	= [];
						$row[]	= 'VENTAS';
						$row[] 	= $total['resumen_ventas'];

						$sheet->appendRow($row);

						$row	= [];
						$row[]	= 'INGRESOS';
						$row[] 	= $total['resumen_ingresos'];

						$sheet->appendRow($row);

						$row	= [];
						$row[]	= 'GASTOS TOTALES';
						$row[] 	= $total['resumen_gastos'];

						$sheet->appendRow($row);
						$init++;

						$sheet->cell('A2:B5', function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '16'));
						});
						$sheet->setColumnFormat(array(
							'A2:B2' => '@'
						));
					});
				})->store('xlsx', storage_path('report'));
				
			}
			else
			{
				foreach ($months as $month) 
				{
					$requestsStationery = App\RequestModel::select('request_models.accountR as accountRequest','lots.account as accountWarehouse','lots.subtotal as subtotalWarehouse','stationeries.subtotal as subtotalRequest')
										->leftJoin('stationeries','request_models.folio','=','stationeries.idFolio')
										->leftJoin('detail_stationeries','stationeries.idStationery','=','detail_stationeries.idStat')
										->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
										->leftJoin('warehouses','detail_stationeries.idwarehouse','=','warehouses.idwarehouse')
										->leftJoin('lots','warehouses.idLot','=','lots.idlot')
										->where('request_models.kind',7)
										->whereIn('request_models.status',[5,9,10,11,12])
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
										->whereYear('request_models.fDate',$year)
										->whereMonth('request_models.fDate',$month)
										->orderBy('kind','ASC')
										->orderBy('request_models.folio','ASC')
										->get();

					$requestsComputer 	= App\RequestModel::select('request_models.accountR as accountRequest','computer_equipments.account as accountComputer','computers.subtotal as subtotalRequest','computer_equipments.subtotal as subtotalComputer')
							->leftJoin('computers','computers.idFolio','=','request_models.folio')
							->leftJoin('computer_equipments','computer_equipments.id','=','computers.idComputerEquipment')
							->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
							->where('request_models.kind',6)
							->whereIn('request_models.status',[5,9,10,11,12])
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
							->whereYear('request_models.fDate',$year)
							->whereMonth('request_models.fDate',$month)
							->orderBy('kind','ASC')
							->orderBy('request_models.folio','ASC')
							->get();
					$requests 	= App\RequestModel::selectRaw('
							request_models.folio AS folio,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"),IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
							IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, accounts.description, IF(request_models.kind = 8, resAcc.description, IF(request_models.kind = 9, refAcc.description, IF(request_models.kind = 9,accounts.description, IF(request_models.kind = 16, nomAccount.description,""))))) AS description,
							IF(request_models.kind = 1  OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, IF(request_models.kind = 8, resAcc.idAccAcc, IF(request_models.kind = 9, refAcc.idAccAcc, IF(request_models.kind = 9,accounts.idAccAcc, IF(request_models.kind = 16, nomAccount.idAccAcc,""))))) AS idAccAccReport,
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
						->whereYear('request_models.fDate',$year)
						->whereMonth('request_models.fDate',$month)
						->orderBy('request_kinds.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->get();

					$payments	= App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
								->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
								->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
								->select('payments.account as account','payments.subtotal as subtotal')
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
								->whereYear('request_models.fDate',$year)
								->whereMonth('request_models.fDate',$month)
								->get();

					$sales 		= App\Bill::where('type','I')
								->whereNotNull('folioRequest')
								->whereYear('expeditionDate',$year)
								->whereMonth('expeditionDate',$month)
								->where('rfc',$rfcEnterprise)
								->whereIn('statusConciliation',[0,1])
								->get();

					$income 	= App\ConciliationMovementBill::leftJoin('bills','bills.idBill','=','conciliation_movement_bills.idbill')
								->leftJoin('movements','movements.idmovement','=','conciliation_movement_bills.idmovement')
								->select('bills.subtotal as subtotalBill','movements.amount as subtotalMovement','movements.idAccount as accountMovement')
								->whereYear('bills.expeditionDate',$year)
								->whereMonth('bills.expeditionDate',$month)
								->where('bills.rfc',$rfcEnterprise)
								->get();

					$warehouse = App\Lot::select('request_models.accountR as accountPurchase','purchases.subtotales as subtotalPurchase','lots.account as accountLot','lots.subtotal as subtotalLot')
								->leftJoin('request_models','lots.idFolio','request_models.folio')
								->leftJoin('purchases','request_models.folio','purchases.idFolio')
								->where('lots.idEnterprise',$enterprise)
								->whereYear('lots.date',$year)
								->whereMonth('lots.date',$month)
								->get();

					$warehouseComputer = App\ComputerEquipment::where('idEnterprise',$enterprise)
										->whereYear('date',$year)
										->whereMonth('date',$month)
										->get();


							 // SUMAR A LA 4100000

					$resultCollectIncome 				= collect($income)->groupBy('accountMovement');
					$resultCollectAccounts				= collect($requests)->groupBy('idAccAccReport');
					$resultCollectPayments				= collect($payments)->groupBy('account');
					$resultCollectWarehouse				= collect($warehouse)->groupBy('accountLot');
					$resultCollectWarehousePurchase		= collect($warehouse)->groupBy('accountPurchase');
					$resultCollectWarehouseComputer		= collect($warehouseComputer)->groupBy('account');
					$resultCollectComputerDelivery		= collect($requestsComputer)->groupBy('accountComputer');
					$resultCollectRequestComputer		= collect($requestsComputer)->groupBy('accountRequest');
					$resultCollectStationeryDelivery	= collect($requestsStationery)->groupBy('accountWarehouse');
					$resultCollectRequestStationery		= collect($requestsStationery)->groupBy('accountRequest');
					if(count($accountsBalance)>0)
					{
						$init 				= 3;
						$keyR				= 0;
						
						foreach ($accountsBalance as $acc) 
						{
							$accountRegister[$keyR]['description']	= $acc->account.' '.strtoupper($acc->description);
							$accountRegister[$keyR]['account']		= $acc->account;
							$accountRegister[$keyR]['selectable']	= $acc->selectable;
							$accountRegister[$keyR]['identifier']	= $acc->level;
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
									$balance    -= $resultCollectPayments[$ta]->sum('subtotal');
								}
								if(isset($resultCollectWarehouse[$ta]))
								{
									$balance    += $resultCollectWarehouse[$ta]->sum('subtotalLot');
								}
								if(isset($resultCollectWarehouseComputer[$ta]))
								{
									$balance    += $resultCollectWarehouseComputer[$ta]->sum('subtotal');
								}

								if(isset($resultCollectComputerDelivery[$ta]))
								{
									$balance    -= $resultCollectComputerDelivery[$ta]->sum('subtotalRequest');
								}
								if(isset($resultCollectStationeryDelivery[$ta]))
								{
									$balance    -= $resultCollectStationeryDelivery[$ta]->sum('subtotalRequest');
								}
								if(isset($resultCollectIncome[$ta]))
								{
									$balance    += $resultCollectIncome[$ta]->sum('subtotalBill');
								}
							}
							
							if(isset($total[$acc->account.'_'.$month]))
							{
								$total[$acc->account.'_'.$month] += $balance;
							}
							else
							{
								$total[$acc->account.'_'.$month] = $balance;
							}
							if(isset($total[$acc->father.'_'.$month]))
							{
								$total[$acc->father.'_'.$month] += $balance;
							}
							else
							{
								$total[$acc->father.'_'.$month] = $balance;
							}



							if($acc->father!='')
							{
								$new = App\Account::where('account',$acc->father)->first();
								
								if ($new->father != '')
								{
									if (isset($total[$new->father.'_'.$month])) 
									{
										$total[$new->father.'_'.$month] += $balance;
									}
									else
									{
										$total[$new->father.'_'.$month] = $balance;
									}
									$new_a = App\Account::where('account',$new->father)->first();
									if ($new_a->father != '') 
									{
										if (isset($total[$new_a->father.'_'.$month])) 
										{
											$total[$new_a->father.'_'.$month] += $balance;
										}
										else
										{
											$total[$new_a->father.'_'.$month] = $balance;
										}
										$new_b = App\Account::where('account',$new_a->father)->first();
										if ($new_b->father != '') 
										{
											if (isset($total[$new_b->father.'_'.$month])) 
											{
												$total[$new_b->father.'_'.$month] += $balance;
											}
											else
											{
												$total[$new_b->father.'_'.$month] = $balance;
											}
										}
									}
								}
							}
							$keyR++;
						}
					}

					if(count($accountsStatement)>0)
					{
						$init 				= 3;
						$keyR				= 0;
						
						foreach ($accountsStatement as $acc) 
						{
							$accountRegisterStatement[$keyR]['description']			= $acc->account.' '.strtoupper($acc->description);
							$accountRegisterStatement[$keyR]['descriptionGraph']	= strtoupper($acc->description);
							$accountRegisterStatement[$keyR]['account']				= $acc->account;
							$accountRegisterStatement[$keyR]['selectable']			= $acc->selectable;
							$accountRegisterStatement[$keyR]['identifier']			= $acc->level;
							$accountRegisterStatement[$keyR]['father']				= $acc->father;

							$balance = 0;
							$tempAcc = App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
							foreach ($tempAcc as $ta)
							{
								if(isset($resultCollectAccounts[$ta]))
								{
									$balance    += $resultCollectAccounts[$ta]->sum('amount');
								}
								if(isset($resultCollectPayments[$ta]))
								{
									$balance    -= $resultCollectPayments[$ta]->sum('subtotal');
								}
								if(isset($resultCollectRequestComputer[$ta]))
								{
									$balance    += $resultCollectRequestComputer[$ta]->sum('subtotalRequest');
								}

								if(isset($resultCollectRequestStationery[$ta]))
								{
									$balance    += $resultCollectRequestStationery[$ta]->sum('subtotalRequest');
								}

								if(isset($resultCollectWarehousePurchase[$ta]))
								{
									$balance    -= $resultCollectWarehousePurchase[$ta]->sum('subtotalPurchase');
								}
							}
							
							if(isset($total[$acc->account.'_'.$month]))
							{
								$total[$acc->account.'_'.$month] += $balance;
							}
							else
							{
								$total[$acc->account.'_'.$month] = $balance;
							}
							if(isset($total[$acc->father.'_'.$month]))
							{
								$total[$acc->father.'_'.$month] += $balance;
							}
							else
							{
								$total[$acc->father.'_'.$month] = $balance;
							}



							if($acc->father!='')
							{
								$new = App\Account::where('account',$acc->father)->first();
								
								if ($new->father != '')
								{
									if (isset($total[$new->father.'_'.$month])) 
									{
										$total[$new->father.'_'.$month] += $balance;
									}
									else
									{
										$total[$new->father.'_'.$month] = $balance;
									}
									$new_a = App\Account::where('account',$new->father)->first();
									if ($new_a->father != '') 
									{
										if (isset($total[$new_a->father.'_'.$month])) 
										{
											$total[$new_a->father.'_'.$month] += $balance;
										}
										else
										{
											$total[$new_a->father.'_'.$month] = $balance;
										}
										$new_b = App\Account::where('account',$new_a->father)->first();
										if ($new_b->father != '') 
										{
											if (isset($total[$new_b->father.'_'.$month])) 
											{
												$total[$new_b->father.'_'.$month] += $balance;
											}
											else
											{
												$total[$new_b->father.'_'.$month] = $balance;
											}
										}
									}
								}
							}
							$keyR++;
						}
					}
					$total['4000000_'.$month]				= $sales->sum('subtotal');
					$total['4100000_'.$month]				= $sales->sum('subtotal');
					
					$total['ventas_'.$month]				= $total['4000000_'.$month];
					$total['costo_ventas_'.$month]			= $total['5100000_'.$month];
					$total['utilidad_bruta_'.$month]		= $total['ventas_'.$month] - $total['costo_ventas_'.$month];
					
					$total['gastos_ventas_'.$month]			= $total['5200000_'.$month];
					$total['gastos_operacion_'.$month]		= $total['5300000_'.$month];
					$total['gastos_administracion_'.$month]	= $total['5400000_'.$month];
					$total['utilidad_operacion_'.$month]	= $total['utilidad_bruta_'.$month] - $total['gastos_ventas_'.$month] - $total['gastos_operacion_'.$month] - $total['gastos_administracion_'.$month];
					
					$total['gastos_financieros_'.$month]	= $total['5500000_'.$month] + $total['5600000_'.$month];
					$total['utilidad_isr_ptu_'.$month]		= $total['utilidad_operacion_'.$month] - $total['gastos_financieros_'.$month];
					$total['utilidad_neta_'.$month]			= $total['utilidad_isr_ptu_'.$month];
					
					$total['margen_uti_bruta_'.$month]		= $total['ventas_'.$month] != 0 ? $total['utilidad_bruta_'.$month]/$total['ventas_'.$month] : 0;
					$total['margen_uti_op_'.$month]			= $total['ventas_'.$month] != 0 ? $total['utilidad_operacion_'.$month]/$total['ventas_'.$month] : 0;
					$total['margen_uti_neta_'.$month]		= $total['ventas_'.$month] != 0 ? $total['utilidad_neta_'.$month]/$total['ventas_'.$month] : 0;

					$total['resumen_ventas_'.$month]	= $total['ventas_'.$month];
					$total['resumen_ingresos_'.$month]	= $total['1101000_'.$month]+$total['1102000_'.$month]+$total['1103000_'.$month]+$total['1104000_'.$month]+$total['1105000_'.$month];
					$total['resumen_gastos_'.$month]	= $total['gastos_financieros_'.$month]+$total['gastos_administracion_'.$month]+$total['gastos_operacion_'.$month]+$total['gastos_ventas_'.$month]+$total['costo_ventas_'.$month];

				}

				$monthsArray = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
				$entName	= App\Enterprise::find($enterprise)->name.' '.$monthsArray[reset($months)].'-'.$monthsArray[end($months)].''.$year;
				$fileName	= 'AdG'.round(microtime(true) * 1000).'_report';
				
				Excel::create($fileName, function($excel) use ($enterprise,$accountRegister,$accountRegisterStatement,$total,$months,$year,$monthsArray)
				{
					
					$excel->sheet('BalanceGeneralMensual',function($sheet) use ($enterprise,$accountRegister,$total,$months,$year,$monthsArray)
					{
						$countMonths 	= count($months);
						$range			= 'B:'.chr(70+($countMonths+1)).'';
						$endRange		= chr(70+($countMonths+1));
						$numberColumn	= 1+($countMonths+1);

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1' 	=> '@',
								'B:'.$endRange.'' 	=> '"$"#,##0.00_-',
							));

						$sheet->mergeCells('A1:'.$endRange.'1');
						$sheet->mergeCells('A2:F2');
						
						$sheet->cell('A1:'.$endRange.'2', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});

						$titles = [ 
							'Cuenta','','','','','',
						];
							
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							array_push($titles,$monthsArray[$months[$i]]);
						}
						array_push($titles,'Total');

						$sheet->row(1,['BALANCE GENERAL']);
						$sheet->row(2,$titles);

						$init	= 3;
						$end	= 3;

						foreach ($accountRegister as $acc)
						{
							$totalYear = 0;
							$row	= [];
							$row[]	= strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]	= '';
							}
							for ($i=0; $i < $countMonths; $i++) 
							{ 
								$row[]	= $total[$acc['account'].'_'.$months[$i]];
								$totalYear += $total[$acc['account'].'_'.$months[$i]];
							}
							$row[] = $totalYear;
							$sheet->appendRow($row);
							$sheet->mergeCells('A'.$init.':'.'F'.$init);

							switch ($acc['identifier']) 
							{
								case 1:
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
									{
										$cells->setBackground('#2F75B5');
										$cells->setFontColor('#ffffff');
										$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									});
									break;

								case 2:
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
							$init++;
							$end++;
						}
					});

					$excel->sheet('EstadoResultadosMensual',function($sheet) use ($enterprise,$accountRegisterStatement,$total,$months,$year,$monthsArray)
					{
						$countMonths 	= count($months);
						$range			= 'B:'.chr(70+($countMonths+1)).'';
						$endRange		= chr(70+($countMonths+1));
						$numberColumn	= 1+($countMonths+1);

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1' 	=> '@',
								'B:'.$endRange.'' 	=> '"$"#,##0.00_-',
							));

						$sheet->mergeCells('A1:'.$endRange.'1');
						$sheet->mergeCells('A2:F2');
						
						$sheet->cell('A1:'.$endRange.'2', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});

						$titles = [ 
							'Cuenta','','','','','',
						];
							
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							array_push($titles,$monthsArray[$months[$i]]);
						}
						array_push($titles,'Total');

						$sheet->row(1,['ESTADO DE RESULTADOS']);
						$sheet->row(2,$titles);

						$init	= 3;
						$end	= 3;

						foreach ($accountRegisterStatement as $acc)
						{
							$totalYear = 0;
							$row	= [];
							$row[]	= strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]	= '';
							}
							for ($i=0; $i < $countMonths; $i++) 
							{ 
								$row[]	= $total[$acc['account'].'_'.$months[$i]];
								$totalYear += $total[$acc['account'].'_'.$months[$i]];
							}
							$row[] = $totalYear;
							$sheet->appendRow($row);
							$sheet->mergeCells('A'.$init.':'.'F'.$init);

							switch ($acc['identifier']) 
							{
								case 1:
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
									{
										$cells->setBackground('#2F75B5');
										$cells->setFontColor('#ffffff');
										$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									});
									break;

								case 2:
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
									$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
							$init++;
							$end++;
						}

						
						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2 || $acc['identifier'] == 3 || $acc['identifier'] == 4) 
							{
								$totalYear = 0;
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]	= $total[$acc['account'].'_'.$months[$i]];
									$totalYear += $total[$acc['account'].'_'.$months[$i]];
								}
								$row[] = $totalYear;
								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

									case 2:
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
								$init++;
								$end++;
							}
						}
						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2 || $acc['identifier'] == 3) 
							{
								$totalYear = 0;
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]	= $total[$acc['account'].'_'.$months[$i]];
									$totalYear += $total[$acc['account'].'_'.$months[$i]];
								}
								$row[] = $totalYear;
								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

									case 2:
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
								$init++;
								$end++;
							}
						}

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						foreach ($accountRegisterStatement as $acc)
						{
							if ($acc['identifier'] == 2) 
							{
								$totalYear = 0;
								$row	= [];
								$row[]	= strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]	= '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]	= $total[$acc['account'].'_'.$months[$i]];
									$totalYear += $total[$acc['account'].'_'.$months[$i]];
								}
								$row[] = $totalYear;
								$sheet->appendRow($row);
								$sheet->mergeCells('A'.$init.':'.'F'.$init);

								switch ($acc['identifier']) 
								{

									case 2:
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
										$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
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
								$init++;
								$end++;
							}
						}

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						// ------ utilidad bruta ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['ventas_'.$months[$i]];
							$totalYear += $total['ventas_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'COSTO DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['costo_ventas_'.$months[$i]];
							$totalYear += $total['costo_ventas_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['utilidad_bruta_'.$months[$i]];
							$totalYear += $total['utilidad_bruta_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;
						$end++;

						// ------ utilidad de operation ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['gastos_ventas_'.$months[$i]];
							$totalYear += $total['gastos_ventas_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['gastos_operacion_'.$months[$i]];
							$totalYear += $total['gastos_operacion_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS DE ADMINISTRACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['gastos_administracion_'.$months[$i]];
							$totalYear += $total['gastos_administracion_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['utilidad_operacion_'.$months[$i]];
							$totalYear += $total['utilidad_operacion_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;
						$end++;


						// ------ utilidad antes de isr y ptu ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS FINANCIEROS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['gastos_financieros_'.$months[$i]];
							$totalYear += $total['gastos_financieros_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD ANTES ISR Y PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['utilidad_isr_ptu_'.$months[$i]];
							$totalYear += $total['utilidad_isr_ptu_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;
						$end++;


						// ------ utilidad neta ----
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'ISR';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= 0;
							$totalYear += 0;
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= 0;
							$totalYear += 0;
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['utilidad_neta_'.$months[$i]];
							$totalYear += $total['utilidad_neta_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
						{
							$cells->setTextIndent(4);
						});
						$init++;
						$end++;


						$row		= [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]	= '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['margen_uti_bruta_'.$months[$i]];
							$totalYear += $total['margen_uti_bruta_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['margen_uti_op_'.$months[$i]];
							$totalYear += $total['margen_uti_op_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;
						$end++;

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'MARGEN DE UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]	= '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['margen_uti_neta_'.$months[$i]];
							$totalYear += $total['margen_uti_neta_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
						});
						$sheet->setColumnFormat(array(
							'G'.$init.':S'.$init.'' => '0.00%'
						));
						$init++;
						$end++;
					});

					$excel->sheet('Resumen',function($sheet) use ($enterprise,$accountRegisterStatement,$total,$months,$year,$monthsArray)
					{
						$countMonths 	= count($months);
						$range			= 'B:'.chr(66+($countMonths)).'';
						$endRange		= chr(66+($countMonths));

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1' 	=> '@',
								'B:'.$endRange.'' 	=> '"$"#,##0.00_-',
							));

						$sheet->mergeCells('A1:'.$endRange.'1');
						
						$sheet->cell('A1:'.$endRange.'2', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '22','bold' => true));
						});

						$titles = [ 
							'',
						];
							
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							array_push($titles,$monthsArray[$months[$i]]);
						}
						array_push($titles,'Total');

						$sheet->row(1,['RESUMEN']);
						$sheet->row(2,$titles);

						$init	= 3;
						$end	= 3;
						
						$totalYear	= 0;
						$row		= [];
						$row[]		= 'VENTAS';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['resumen_ventas_'.$months[$i]];
							$totalYear += $total['resumen_ventas_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'INGRESOS';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['resumen_ingresos_'.$months[$i]];
							$totalYear += $total['resumen_ingresos_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);

						$totalYear	= 0;
						$row		= [];
						$row[]		= 'GASTOS TOTALES';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]	= $total['resumen_gastos_'.$months[$i]];
							$totalYear += $total['resumen_gastos_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);
						$init++;

						$sheet->cell('A3:M13', function($cells)  
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '16'));
						});
						$sheet->setColumnFormat(array(
							'A2:B2' => '@'
						));
					});

				})->store('xlsx', storage_path('report'));
				
			}

			$accountsER 	= [];
			$key 			= 0;
			$getAccountER 	= App\Account::where('idEnterprise',$report->dataEnterprise->idEnterprise)
							->where(function($query)
							{
								$query->where('account','like','4%')->orWhere('account','like','5%');
							})
							->orderBy('account','ASC')
							->get();

			if (count($getAccountER)>0) 
			{
				foreach ($getAccountER as $acc) 
				{
					if($acc->level == 2)
					{
						$accountsER[$key]['idAccAcc']		= $acc->idAccAcc;
						$accountsER[$key]['account']		= $acc->account;
						$accountsER[$key]['description']	= $acc->description;
						$key++;
					}
				}
			}

			$rep = 	App\BalanceSheet::find($report->id);
			$rep->status = 1;
			$rep->file 	 = $fileName;
			$rep->save();
		}
	}
}
