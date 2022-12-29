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

class ReportFinanceIvaController extends Controller
{
	private $module_id = 130;
	public function ivaReport(Request $request)
	{
		if (Auth::user()->module->where('id',225)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$reports = App\BalanceSheet::orderBy('date','DESC')->orderBy('id','DESC')->paginate(10);

			return view('reporte.finanzas.iva',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 225,
					'reports'	=> $reports
				]);
		}
	}

	public function ivaResultFail(Request $request)
	{
		if (Auth::user()->module->where('id',225)->count()>0) 
		{
			$enterprise     = $request->enterprise;
			$rfcEnterprise  = App\Enterprise::find($enterprise)->rfc;
			$project        = $request->project;
			$year           = $request->year;
			$months         = $request->months;
			$type           = $request->type;

			$data           = App\Module::find($this->module_id);

			$accountsBalance    = App\Account::where('idEnterprise',$enterprise)
								->where(function($query)
								{
									$query->where('account','like','1%')->orWhere('account','like','2%')->orWhere('account','like','3%');
								})
								->orderBy('account','ASC')
								->get();

			$accountsStatement  = App\Account::where('idEnterprise',$enterprise)
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
			$count						= 0;
			
			if ($request->type == 1) 
			{
				$requestsStationery = App\RequestModel::selectRaw('request_models.accountR as accountRequest, lots.account as accountWarehouse, lots.iva as ivaWarehouse, stationeries.iva as ivaRequest, CONCAT("Folio #",request_models.folio," ",stationeries.title) as concept')
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

				$requestsComputer   = App\RequestModel::selectRaw('request_models.accountR as accountRequest, computer_equipments.account as accountComputer, computers.iva as ivaRequest, computer_equipments.iva as ivaComputer, CONCAT("Folio #",request_models.folio," ",computers.title) as concept ')
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

				$requests   = App\RequestModel::selectRaw('
									request_models.folio AS folio,

									IF(request_models.kind = 1  OR request_models.kind = 17 OR request_models.kind = 18, accounts.idAccAcc, 
										IF(request_models.kind = 8, resAcc.idAccAcc, 
											IF(request_models.kind = 9, refAcc.idAccAcc, 
												IF(request_models.kind = 9,accounts.idAccAcc, ""
												)
											)
										)
									) AS idAccAccReport,

									IF(request_models.kind = 1, CONCAT("Folio #", request_models.folio," ", detail_purchases.description), 
										IF(request_models.kind = 8, CONCAT("Folio #", request_models.folio," ", resource_details.concept), 
											IF(request_models.kind = 9, CONCAT("Folio #", request_models.folio," ", refund_details.concept), 
												IF(request_models.kind = 18, CONCAT("Folio #", request_models.folio," ", finances.kind), 
													IF(request_models.kind = 17, CONCAT("Folio #", request_models.folio," ", purchase_record_details.description), ""
													)
												)
											)
										)
									) AS concept,
									
									ROUND(
										IF(request_models.kind = 1, detail_purchases.tax, 
											IF(request_models.kind = 8, "0", 
												IF(request_models.kind = 9, refund_details.amount, 
													IF(request_models.kind = 18, finances.tax, 
														IF(request_models.kind = 17, purchase_record_details.tax, ""
															
														)
													)
												)
											)
										),2) AS iva
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
							->leftJoin('finances','request_models.folio','=','finances.idFolio')
							->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
							->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
							->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
							->whereIn('request_models.kind',[1,8,9,17,18])
							->whereIn('request_models.status',[5,10,11,12])
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
							->orderBy('request_kinds.kind','ASC')
							->orderBy('request_models.folio','ASC')
							->get();

				$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
							->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','=','payments.idnominaEmployee')
							->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
							->select('payments.account as account','payments.iva as iva')
							->selectRaw('payments.iva as iva, CONCAT("Pago del Folio #",payments.idFolio) as concept, payments.account as account')
							->whereIn('request_models.kind',[1,8,9,16,17,18])
							->whereIn('request_models.status',[5,10,11,12])
							->where('payments.idEnterprise',$enterprise)
							->where('payments.iva','>',0)
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

				$sales      = App\Bill::leftJoin('bill_details','bills.idBill','=','bill_details.idBill')
							->leftJoin('bill_taxes','bill_details.idBillDetail','=','bill_taxes.idBillDetail')
							->selectRaw('bill_taxes.amount as iva, CONCAT("Factura #",bills.folio," - ",bill_details.description) as concept')
							->where('bills.type','I')
							->whereNotNull('bills.folioRequest')
							->whereYear('bills.expeditionDate',$year)
							->whereRaw('MONTH(bills.expeditionDate) IN('.implode(',', $months).')')
							->where('bills.rfc',$rfcEnterprise)
							->whereIn('bills.statusConciliation',[0,1])
							->whereIn('bills.status',[1,2])
							->where('bill_taxes.tax','002')
							->where( function($query) use ($project) 
							{
								if($project != null)
								{
									$query->whereIn('bills.idProject',$project);
								}
							})
							->get();

				$income     = App\ConciliationMovementBill::leftJoin('bills','bills.idBill','=','conciliation_movement_bills.idbill')
							->leftJoin('bill_details','bills.idBill','=','bill_details.idBill')
							->leftJoin('bill_taxes','bill_details.idBillDetail','=','bill_taxes.idBillDetail')
							->leftJoin('movements','movements.idmovement','=','conciliation_movement_bills.idmovement')
							->selectRaw('bill_taxes.amount as iva, CONCAT("Factura #",bills.folio," - ",bill_details.description) as concept')
							->whereYear('bills.expeditionDate',$year)
							->whereRaw('MONTH(bills.expeditionDate) IN('.implode(',', $months).')')
							->where('bills.rfc',$rfcEnterprise)
							->where( function($query) use ($project) 
							{
								if($project != null)
								{
									$query->whereIn('bills.idProject',$project);
								}
							})
							->get();

				$warehouse = App\Lot::selectRaw('request_models.accountR as accountPurchase, purchases.tax as ivaPurchase, lots.account as accountLot, lots.iva as ivaLot, request_models.folio as folio, CONCAT("Lote De Inventario #",lots.idlot) as concept')
							->leftJoin('request_models','lots.idFolio','request_models.folio')
							->leftJoin('purchases','request_models.folio','purchases.idFolio')
							->where('lots.idEnterprise',$enterprise)
							->whereYear('lots.date',$year)
							->whereRaw('MONTH(lots.date) IN('.implode(',', $months).')')
							->get();

				$warehouseComputer = App\ComputerEquipment::selectRaw('
										IF(type = 1,CONCAT(quantity," Smartphone ",brand),IF(type = 2, CONCAT(quantity," Tablet ",brand), IF(type = 3, CONCAT(quantity," Laptop ",brand), IF(type = 4, CONCAT(quantity," Desktop ",brand),"")))) as concept, account, iva
									')
									->where('idEnterprise',$enterprise)
									->whereYear('date',$year)
									->whereRaw('MONTH(date) IN('.implode(',', $months).')')
									->get();

				$resultCollectIncome				= collect($income)->groupBy('accountMovement');
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
					$init   = 3;
					$keyR   = 0;
					
					foreach ($accountsBalance as $acc) 
					{
						$accountRegister[$keyR]['description']  = $acc->account.' '.strtoupper($acc->description);
						$accountRegister[$keyR]['account']      = $acc->account;
						$accountRegister[$keyR]['selectable']   = $acc->selectable;
						$accountRegister[$keyR]['identifier']   = $acc->level;
						$balance                                = 0;
						$tempAcc                                = App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
						$keyConcept = 0;
						foreach ($tempAcc as $ta)
						{
							if(isset($resultCollectAccounts[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectAccounts[$acc->idAccAcc];
								$balance    += $resultCollectAccounts[$ta]->sum('iva');
								$keyConcept++;
							}
							if(isset($resultCollectPayments[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectPayments[$acc->idAccAcc];
								$balance    -= $resultCollectPayments[$ta]->sum('iva');
								$keyConcept++;
							}
							if(isset($resultCollectWarehouse[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectWarehouse[$acc->idAccAcc];
								$balance    += $resultCollectWarehouse[$ta]->sum('ivaLot');
								$keyConcept++;
							}
							if(isset($resultCollectWarehouseComputer[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectWarehouseComputer[$acc->idAccAcc];
								$balance    += $resultCollectWarehouseComputer[$ta]->sum('iva');
								$keyConcept++;
							}

							if(isset($resultCollectComputerDelivery[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectComputerDelivery[$acc->idAccAcc];
								$balance    -= $resultCollectComputerDelivery[$ta]->sum('ivaRequest');
								$keyConcept++;
							}
							if(isset($resultCollectStationeryDelivery[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectStationeryDelivery[$acc->idAccAcc];
								$balance    -= $resultCollectStationeryDelivery[$ta]->sum('ivaRequest');
								$keyConcept++;
							}
							if(isset($resultCollectIncome[$ta]))
							{
								$accountRegister[$keyR]['concepts'][$keyConcept]    = $resultCollectIncome[$acc->idAccAcc];
								$balance    += $resultCollectIncome[$ta]->sum('iva');
								$keyConcept++;
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
					$init   = 3;
					$keyR   = 0;
					
					foreach ($accountsStatement as $acc) 
					{
						$accountRegisterStatement[$keyR]['description']         = $acc->account.' '.strtoupper($acc->description);
						$accountRegisterStatement[$keyR]['descriptionGraph']    = strtoupper($acc->description);
						$accountRegisterStatement[$keyR]['account']     = $acc->account;
						$accountRegisterStatement[$keyR]['selectable']  = $acc->selectable;
						$accountRegisterStatement[$keyR]['identifier']  = $acc->level;
						$accountRegisterStatement[$keyR]['father']      = $acc->father;
						$balance                                = 0;
						$tempAcc                                = App\Account::select('idAccAcc')->where('account',$acc->account)->where('description',$acc->description)->pluck('idAccAcc');
						$keyConcept = 0;
						foreach ($tempAcc as $ta)
						{
							if(isset($resultCollectAccounts[$ta]))
							{
								$accountRegisterStatement[$keyR]['concepts'][$keyConcept]   = $resultCollectAccounts[$acc->idAccAcc];
								$balance    += $resultCollectAccounts[$ta]->sum('iva');
								$keyConcept++;
							}
							if(isset($resultCollectPayments[$ta]))
							{
								$accountRegisterStatement[$keyR]['concepts'][$keyConcept]   = $resultCollectPayments[$acc->idAccAcc];
								$balance    -= $resultCollectPayments[$ta]->sum('iva');
								$keyConcept++;
							}
							if(isset($resultCollectRequestComputer[$ta]))
							{
								$accountRegisterStatement[$keyR]['concepts'][$keyConcept]   = $resultCollectRequestComputer[$acc->idAccAcc];
								$balance    += $resultCollectRequestComputer[$ta]->sum('ivaRequest');
								$keyConcept++;
							}

							if(isset($resultCollectRequestStationery[$ta]))
							{
								$accountRegisterStatement[$keyR]['concepts'][$keyConcept]   = $resultCollectRequestStationery[$acc->idAccAcc];
								$balance    += $resultCollectRequestStationery[$ta]->sum('ivaRequest');
								$keyConcept++;
							}

							if(isset($resultCollectWarehousePurchase[$ta]))
							{
								$accountRegisterStatement[$keyR]['concepts'][$keyConcept]   = $resultCollectWarehousePurchase[$acc->idAccAcc];
								$balance    -= $resultCollectWarehousePurchase[$ta]->sum('ivaPurchase');
								$keyConcept++;
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
				$total['4000000']   = $income->sum('iva');
				$total['4100000']   = $income->sum('iva');
				
				$total['4000000']   = $sales->sum('iva');
				$total['4100000']   = $sales->sum('iva');
				
				/*
				$total['ventas']                = $total['4000000'];
				$total['costo_ventas']          = $total['5100000'];
				$total['utilidad_bruta']        = $total['ventas'] - $total['costo_ventas'];
				
				$total['gastos_ventas']         = $total['5200000'];
				$total['gastos_operacion']      = $total['5300000'];
				$total['gastos_administracion'] = $total['5400000'];
				$total['utilidad_operacion']    = $total['utilidad_bruta'] - $total['gastos_ventas'] - $total['gastos_operacion'] - $total['gastos_administracion'];
				
				$total['gastos_financieros']    = $total['5500000'] + $total['5600000'];
				$total['utilidad_isr_ptu']      = $total['utilidad_operacion'] - $total['gastos_financieros'];
				$total['utilidad_neta']         = $total['utilidad_isr_ptu'];
				
				$total['margen_uti_bruta']      = $total['ventas'] != 0 ? $total['utilidad_bruta']/$total['ventas'] : 0;
				$total['margen_uti_op']         = $total['ventas'] != 0 ? $total['utilidad_operacion']/$total['ventas'] : 0;
				$total['margen_uti_neta']       = $total['ventas'] != 0 ? $total['utilidad_neta']/$total['ventas'] : 0;

				$total['resumen_ventas']    = $total['ventas'];
				$total['resumen_ingresos']  = $total['1101000']+$total['1102000']+$total['1103000']+$total['1104000']+$total['1105000'];
				$total['resumen_gastos']    = $total['gastos_financieros']+$total['gastos_administracion']+$total['gastos_operacion']+$total['gastos_ventas']+$total['costo_ventas'];
				*/

				//return $accountRegisterStatement;

				$entName    = App\Enterprise::find($enterprise)->name.' '.$year;
				$fileName   = 'AdG'.round(microtime(true) * 1000).'_report';
				
				Excel::create($fileName, function($excel) use ($enterprise,$accountRegister,$total,$year,$accountRegisterStatement)
				{
					$excel->sheet('IVA por pagar'.$year,function($sheet) use ($enterprise,$accountRegister,$total,$year)
					{
						$sheet->setWidth(array(
							'A'     => 15,
							'B'     => 15,
							'C'     => 15,
							'D'     => 25,
							'E'     => 25,
							'F'     => 15,
							'G'     => 30,
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
						$sheet->row(1,['IVA POR PAGAR']);
						$sheet->row(2,['']);
						$init = 3;
						foreach ($accountRegister as $acc)
						{
							$row    = [];
							$row[]  = strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]  = '';
							}
							$row[]  = $total[$acc['account']];

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
							if (isset($acc['concepts']) && count($acc['concepts'])>0) 
							{
								foreach ($acc['concepts'] as $concept) 
								{
									foreach ($concept as $c) {
										$row    = [];
										$row[]  = $c['concept'];
										for ($i=0; $i < 5; $i++) 
										{ 
											$row[]  = '';
										}
										$row[]  = $c['iva'];
										$sheet->appendRow($row);
										$sheet->mergeCells('A'.$init.':'.'F'.$init);
										$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
										{
											$cells->setFontColor('#000000');
											$cells->setFont(array('family' => 'Calibri','size' => '12'));
										});
										$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
										{
											$cells->setTextIndent(8);
										});
										$init++;
									}
									
								}
							}
						}
					});

					$excel->sheet('IVA acreditable'.$year,function($sheet) use ($enterprise,$accountRegisterStatement,$total,$year)
					{
						$sheet->setWidth(array(
							'A'     => 15,
							'B'     => 15,
							'C'     => 15,
							'D'     => 25,
							'E'     => 25,
							'F'     => 15,
							'G'     => 30,
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
						$sheet->row(1,['IVA ACREDITABLE']);
						$sheet->row(2,['']);
						$init = 3;
						foreach ($accountRegisterStatement as $acc)
						{
							$row    = [];
							$row[]  = strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]  = '';
							}
							$row[]  = $total[$acc['account']];

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
							if (isset($acc['concepts']) && count($acc['concepts'])>0) 
							{
								foreach ($acc['concepts'] as $concept) 
								{
									foreach ($concept as $c) {
										$row    = [];
										$row[]  = $c['concept'];
										for ($i=0; $i < 5; $i++) 
										{ 
											$row[]  = '';
										}
										$row[]  = $c['iva'];
										$sheet->appendRow($row);
										$sheet->mergeCells('A'.$init.':'.'F'.$init);
										$sheet->cell('A'.$init.':'.'G'.$init, function($cells) 
										{
											$cells->setFontColor('#000000');
											$cells->setFont(array('family' => 'Calibri','size' => '12'));
										});
										$sheet->cell('A'.$init.':'.'F'.$init, function($cells) 
										{
											$cells->setTextIndent(8);
										});
										$init++;
									}
									
								}
							}
						}

					});
				
				})->export('xlsx'); 
				//})->store('xlsx', storage_path('report'));
				
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

					$requestsComputer   = App\RequestModel::select('request_models.accountR as accountRequest','computer_equipments.account as accountComputer','computers.subtotal as subtotalRequest','computer_equipments.subtotal as subtotalComputer')
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
					$requests   = App\RequestModel::selectRaw('
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

					$payments   = App\RequestModel::join('payments','request_models.folio','=','payments.idFolio')
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

					$sales      = App\Bill::where('type','I')
								->whereNotNull('folioRequest')
								->whereYear('expeditionDate',$year)
								->whereMonth('expeditionDate',$month)
								->where('rfc',$rfcEnterprise)
								->whereIn('statusConciliation',[0,1])
								->get();

					$income     = App\ConciliationMovementBill::leftJoin('bills','bills.idBill','=','conciliation_movement_bills.idbill')
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


							 

					$resultCollectIncome                = collect($income)->groupBy('accountMovement');
					$resultCollectAccounts              = collect($requests)->groupBy('idAccAccReport');
					$resultCollectPayments              = collect($payments)->groupBy('account');
					$resultCollectWarehouse             = collect($warehouse)->groupBy('accountLot');
					$resultCollectWarehousePurchase     = collect($warehouse)->groupBy('accountPurchase');
					$resultCollectWarehouseComputer     = collect($warehouseComputer)->groupBy('account');
					$resultCollectComputerDelivery      = collect($requestsComputer)->groupBy('accountComputer');
					$resultCollectRequestComputer       = collect($requestsComputer)->groupBy('accountRequest');
					$resultCollectStationeryDelivery    = collect($requestsStationery)->groupBy('accountWarehouse');
					$resultCollectRequestStationery     = collect($requestsStationery)->groupBy('accountRequest');
					if(count($accountsBalance)>0)
					{
						$init               = 3;
						$keyR               = 0;
						
						foreach ($accountsBalance as $acc) 
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
						$init               = 3;
						$keyR               = 0;
						
						foreach ($accountsStatement as $acc) 
						{
							$accountRegisterStatement[$keyR]['description']         = $acc->account.' '.strtoupper($acc->description);
							$accountRegisterStatement[$keyR]['descriptionGraph']    = strtoupper($acc->description);
							$accountRegisterStatement[$keyR]['account']             = $acc->account;
							$accountRegisterStatement[$keyR]['selectable']          = $acc->selectable;
							$accountRegisterStatement[$keyR]['identifier']          = $acc->level;
							$accountRegisterStatement[$keyR]['father']              = $acc->father;

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
					$total['4000000_'.$month]               = $sales->sum('subtotal');
					$total['4100000_'.$month]               = $sales->sum('subtotal');
					
					$total['ventas_'.$month]                = $total['4000000_'.$month];
					$total['costo_ventas_'.$month]          = $total['5100000_'.$month];
					$total['utilidad_bruta_'.$month]        = $total['ventas_'.$month] - $total['costo_ventas_'.$month];
					
					$total['gastos_ventas_'.$month]         = $total['5200000_'.$month];
					$total['gastos_operacion_'.$month]      = $total['5300000_'.$month];
					$total['gastos_administracion_'.$month] = $total['5400000_'.$month];
					$total['utilidad_operacion_'.$month]    = $total['utilidad_bruta_'.$month] - $total['gastos_ventas_'.$month] - $total['gastos_operacion_'.$month] - $total['gastos_administracion_'.$month];
					
					$total['gastos_financieros_'.$month]    = $total['5500000_'.$month] + $total['5600000_'.$month];
					$total['utilidad_isr_ptu_'.$month]      = $total['utilidad_operacion_'.$month] - $total['gastos_financieros_'.$month];
					$total['utilidad_neta_'.$month]         = $total['utilidad_isr_ptu_'.$month];
					
					$total['margen_uti_bruta_'.$month]      = $total['ventas_'.$month] != 0 ? $total['utilidad_bruta_'.$month]/$total['ventas_'.$month] : 0;
					$total['margen_uti_op_'.$month]         = $total['ventas_'.$month] != 0 ? $total['utilidad_operacion_'.$month]/$total['ventas_'.$month] : 0;
					$total['margen_uti_neta_'.$month]       = $total['ventas_'.$month] != 0 ? $total['utilidad_neta_'.$month]/$total['ventas_'.$month] : 0;

					$total['resumen_ventas_'.$month]    = $total['ventas_'.$month];
					$total['resumen_ingresos_'.$month]  = $total['1101000_'.$month]+$total['1102000_'.$month]+$total['1103000_'.$month]+$total['1104000_'.$month]+$total['1105000_'.$month];
					$total['resumen_gastos_'.$month]    = $total['gastos_financieros_'.$month]+$total['gastos_administracion_'.$month]+$total['gastos_operacion_'.$month]+$total['gastos_ventas_'.$month]+$total['costo_ventas_'.$month];

				}

				$monthsArray = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
				$entName    = App\Enterprise::find($enterprise)->name.' '.$monthsArray[reset($months)].'-'.$monthsArray[end($months)].''.$year;
				$fileName   = 'AdG'.round(microtime(true) * 1000).'_report';
				
				Excel::create($fileName, function($excel) use ($enterprise,$accountRegister,$accountRegisterStatement,$total,$months,$year,$monthsArray)
				{
					
					$excel->sheet('BalanceGeneralMensual',function($sheet) use ($enterprise,$accountRegister,$total,$months,$year,$monthsArray)
					{
						$countMonths    = count($months);
						$range          = 'B:'.chr(70+($countMonths+1)).'';
						$endRange       = chr(70+($countMonths+1));
						$numberColumn   = 1+($countMonths+1);

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1'     => '@',
								'B:'.$endRange.''   => '"$"#,##0.00_-',
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

						$init   = 3;
						$end    = 3;

						foreach ($accountRegister as $acc)
						{
							$totalYear = 0;
							$row    = [];
							$row[]  = strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]  = '';
							}
							for ($i=0; $i < $countMonths; $i++) 
							{ 
								$row[]  = $total[$acc['account'].'_'.$months[$i]];
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
						$countMonths    = count($months);
						$range          = 'B:'.chr(70+($countMonths+1)).'';
						$endRange       = chr(70+($countMonths+1));
						$numberColumn   = 1+($countMonths+1);

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1'     => '@',
								'B:'.$endRange.''   => '"$"#,##0.00_-',
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

						$init   = 3;
						$end    = 3;

						foreach ($accountRegisterStatement as $acc)
						{
							$totalYear = 0;
							$row    = [];
							$row[]  = strtoupper($acc['description']);
							for ($i=0; $i < 5; $i++) 
							{ 
								$row[]  = '';
							}
							for ($i=0; $i < $countMonths; $i++) 
							{ 
								$row[]  = $total[$acc['account'].'_'.$months[$i]];
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

						
						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
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
								$row    = [];
								$row[]  = strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]  = '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]  = $total[$acc['account'].'_'.$months[$i]];
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
						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
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
								$row    = [];
								$row[]  = strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]  = '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]  = $total[$acc['account'].'_'.$months[$i]];
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

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
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
								$row    = [];
								$row[]  = strtoupper($acc['description']);
								for ($i=0; $i < 5; $i++) 
								{ 
									$row[]  = '';
								}
								for ($i=0; $i < $countMonths; $i++) 
								{ 
									$row[]  = $total[$acc['account'].'_'.$months[$i]];
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

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$sheet->cell('A'.$init.':'.''.$endRange.''.$init, function($cells) 
						{
							$cells->setBackground('##e01313');
						});
						$init++;
						$end++;

						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						// ------ utilidad bruta ----
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['ventas_'.$months[$i]];
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'COSTO DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['costo_ventas_'.$months[$i]];
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['utilidad_bruta_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'GASTOS DE VENTAS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['gastos_ventas_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'GASTOS DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['gastos_operacion_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'GASTOS DE ADMINISTRACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['gastos_administracion_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['utilidad_operacion_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'GASTOS FINANCIEROS';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['gastos_financieros_'.$months[$i]];
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'UTILIDAD ANTES ISR Y PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['utilidad_isr_ptu_'.$months[$i]];
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'ISR';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = 0;
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
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'PTU';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = 0;
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['utilidad_neta_'.$months[$i]];
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


						$row        = [];
						for ($i=0; $i < $countMonths+7; $i++) 
						{ 
							$row[]  = '';
						}
						$sheet->appendRow($row);
						$sheet->mergeCells('A'.$init.':'.'F'.$init);
						$init++;
						$end++;

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'MARGEN DE UTILIDAD BRUTA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['margen_uti_bruta_'.$months[$i]];
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'MARGEN DE UTILIDAD DE OPERACIÓN';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['margen_uti_op_'.$months[$i]];
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

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'MARGEN DE UTILIDAD NETA';
						for ($i=0; $i < 5; $i++) 
						{ 
							$row[]  = '';
						}
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['margen_uti_neta_'.$months[$i]];
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
						$countMonths    = count($months);
						$range          = 'B:'.chr(66+($countMonths)).'';
						$endRange       = chr(66+($countMonths));

						$sheet->setColumnFormat(array(
								'A1:'.$endRange.'1'     => '@',
								'B:'.$endRange.''   => '"$"#,##0.00_-',
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

						$init   = 3;
						$end    = 3;
						
						$totalYear  = 0;
						$row        = [];
						$row[]      = 'VENTAS';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['resumen_ventas_'.$months[$i]];
							$totalYear += $total['resumen_ventas_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'INGRESOS';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['resumen_ingresos_'.$months[$i]];
							$totalYear += $total['resumen_ingresos_'.$months[$i]];
						}
						$row[] = $totalYear;
						$sheet->appendRow($row);

						$totalYear  = 0;
						$row        = [];
						$row[]      = 'GASTOS TOTALES';
						for ($i=0; $i < $countMonths; $i++) 
						{ 
							$row[]  = $total['resumen_gastos_'.$months[$i]];
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

			$accountsER     = [];
			$key            = 0;
			$getAccountER   = App\Account::where('idEnterprise',$request->enterprise)
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
						$accountsER[$key]['idAccAcc']       = $acc->idAccAcc;
						$accountsER[$key]['account']        = $acc->account;
						$accountsER[$key]['description']    = $acc->description;
						$key++;
					}
				}
			}

			//return $total;
			$data = App\Module::find($this->module_id);

			return view('reporte.finanzas.balance_estado_resultados',
			[
				'id'                        => $data['father'],
				'title'                     => $data['name'],
				'details'                   => $data['details'],
				'child_id'                  => $this->module_id,
				'option_id'                 => 218,
				'accountRegister'           => $accountRegister,
				'accountRegisterStatement'  => $accountRegisterStatement,
				'total'                     => $total,
				'fileName'                  => $fileName,
				'enterprise'                => $request->enterprise,
				'project'                   => $request->project,
				'year'                      => $request->year,
				'months'                    => $request->months,
				'type'                      => $request->type,
				'accountsER'                => $accountsER,
				'accountsStatement'         => $accountsStatement
			]);
			// CREAR UN ARREGLO POR CADA CUENTA... NO HAY DE OTRA
		}
	}

	public function ivaResult(Request $request)
	{
		if (Auth::user()->module->where('id',225)->count()>0)
		{
			$enterprise     = $request->enterprise;
			$rfcEnterprise  = App\Enterprise::find($enterprise)->rfc;
			$project        = $request->project;
			$year           = $request->year;
			$months         = $request->months;
			$labelArray     = $request->labels;
			$totalIvaRequest = 0;


			$results    = array();
			$print      = array();
			$key        = 0;
			$requests = App\RequestModel::whereIn('kind',[1,3,8,9,11,12,13,14,15,17])
						->whereIn('status',[5,9,10,11,12])
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(225)->pluck('departament_id'))
										->orWhere('idDepartment',null);
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(225)->pluck('enterprise_id'))
										->orWhere('idEnterprise',null);
						})
						->whereYear('request_models.authorizeDate',$year)
						->whereRaw('MONTH(request_models.authorizeDate) IN('.implode(',', $months).')')
						->where(function($query) use ($project,$enterprise)
						{
							if($enterprise != null)
							{
								$query->where('request_models.idEnterpriseR',$enterprise);
							}
							if($project != null)
							{
								$query->whereIn('request_models.idProjectR',$project);
							}
						})
						->get();

			$requestsIncome = App\RequestModel::where('kind',10)
							->whereIn('status',[5,10,11,12])
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(225)->pluck('enterprise_id'))
							->whereYear('request_models.authorizeDate',$year)
							->whereRaw('MONTH(request_models.authorizeDate) IN('.implode(',', $months).')')
							->where(function ($query) use ($enterprise,$project)
							{
								if($enterprise != null)
								{
									$query->where('request_models.idEnterprise',$enterprise);
								}
								if ($project != "")
								{                               
									$query->whereIn('request_models.idProject',$project);
								}
							})
							->orderBy('fDate','DESC')
							->orderBy('folio','DESC')
							->get();

			foreach ($requests as $request) 
			{
				switch ($request->kind) 
				{
					case 1:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->purchases->first()->title.' - '.$request->purchases->first()->datetitle;
						$results[$key]['numberOrder']           = $request->purchases->first()->numberOrder;
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->requestEnterprise->name;
						$results[$key]['requestDirection']      = $request->requestDirection->name;
						$results[$key]['requestDepartment']     = $request->requestDepartment->name;
						$results[$key]['requestProject']        = $request->requestProject->proyectName;
						$results[$key]['requestAccount']        = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->purchases->first()->amount;
						$results[$key]['providerName']          = $request->purchases->first()->provider()->exists() ? $request->purchases->first()->provider->businessName : '';
						$results[$key]['reference']             = $request->purchases->first()->reference;
						$results[$key]['paymentMode']           = $request->purchases->first()->paymentMode;
						if($request->purchases->first()->provider_has_banks_id!='')
						{
							$results[$key]['bankName']      = $request->purchases->first()->bankData->bank->description;
							$results[$key]['bankAccount']   = $request->purchases->first()->bankData->account.' ';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = $request->purchases->first()->bankData->branch;
							$results[$key]['bankReference'] = $request->purchases->first()->bankData->reference;
							$results[$key]['bankClabe']     = $request->purchases->first()->bankData->clabe.' ';
							$results[$key]['bankCurrency']  = $request->purchases->first()->bankData->currency;
							$results[$key]['bankAgreement'] = $request->purchases->first()->bankData->agreement;
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = '';
							$results[$key]['bankAgreement'] = '';
						}
						
						foreach ($request->purchases->first()->detailPurchase as $detail) 
						{
							$print[$request->folio] = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = $detail->quantity;
							$tempArray['detailUnit']        = $detail->unit;
							$tempArray['detailDescription'] = $detail->description;
							$tempArray['detailAccount']     = '';
							$tempArray['detailUnitPrice']   = $detail->unitPrice;
							$tempArray['detailSubtotal']    = $detail->subtotal;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}

							$tempArray['detailTaxesConcept']    = $taxesConcept;
							$retentionConcept                   = 0;

							foreach($detail->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}

							$tempArray['detailRetentionConcept']    = $retentionConcept;
							$tempArray['detailAmount']              = $detail->amount;
							$tempArray['detailAmountResource']      = '';
							$tempArray['diferenceRequest']          = '';
							$tempArray['reembolso']                 = '';
							$tempArray['reintegro']                 = '';
							$results[$key]['concepts'][]            = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
							
						}
						$key++;
						

						break;


					case 3:
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = $request->expenses->first()->resourceId;
						$results[$key]['title']                 = $request->expenses->first()->title.' - '.$request->expenses->first()->datetitle;
						$results[$key]['numberOrder']           = '';
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->requestEnterprise->name;
						$results[$key]['requestDirection']      = $request->requestDirection->name;
						$results[$key]['requestDepartment']     = $request->requestDepartment->name;
						$results[$key]['requestProject']        = $request->requestProject->proyectName;
						$results[$key]['requestAccount']        = 'Varias';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->reviewedEnterprise()->exists() ? 'Varias' : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->expenses->first()->total;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = $request->expenses->first()->reference;
						$results[$key]['paymentMode']           = $request->expenses->first()->paymentMethod->method;
						if($request->expenses->first()->idEmployee!='')
						{
							$results[$key]['bankName']      = $request->expenses->first()->bankData->bank->description;
							$results[$key]['bankAccount']   = $request->expenses->first()->bankData->account.' ';
							$results[$key]['bankCard']      = $request->expenses->first()->bankData->cardNumber.' ';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = $request->expenses->first()->bankData->clabe.' ';
							$results[$key]['bankCurrency']  = $request->expenses->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = $request->expenses->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						foreach ($request->expenses->first()->expensesDetail as $detail) 
						{
							$print[$request->folio]         = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $detail->taxPayment==1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = '';
							$tempArray['detailUnit']        = '';
							$tempArray['detailDescription'] = $detail->concept;
							$tempArray['detailAccount']     = $detail->accountR()->exists() ? $detail->accountR->account.' '.$detail->accountR->description.' ('.$detail->accountR->content.')' : $detail->account->account.' '.$detail->account->description.' ('.$detail->account->content.')';
							$tempArray['detailUnitPrice']   = '';
							$tempArray['detailSubtotal']    = $detail->amount;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$tempArray['detailTaxesConcept']        = $taxesConcept;
							$tempArray['detailRetentionConcept']    = '';
							$tempArray['detailAmount']              = $detail->sAmount;

							$totalResource  = App\RequestModel::find($request->expenses->first()->resourceId)->resource->first()->total;
							$totalExpense   = $request->expenses->first()->total;

							$tempArray['detailAmountResource']  = $totalResource;
							$tempArray['diferenceRequest']      = $totalExpense-$totalResource;
							if ($request->payment == 1 && $request->expenses->first()->reembolso>0) 
							{
								$tempArray['reembolso'] = 'Pagado';
							}
							elseif ($request->payment == 0 && $request->expenses->first()->reembolso>0) 
							{
								$tempArray['reembolso'] = 'No Pagado';
							}
							elseif ($request->expenses->first()->reembolso==0) 
							{
								$tempArray['reembolso'] = 'No Aplica';
							}
							else
							{
								$tempArray['reembolso'] = 'No Aplica';
							}

							if ($request->payment == 1 && $request->expenses->first()->reintegro>0 && $request->free == 1) 
							{
								$tempArray['reintegro'] = 'Comprobado';
							}
							elseif ($request->payment == 0 && $request->expenses->first()->reintegro>0 && $request->free == 0) 
							{
								$tempArray['reintegro'] = 'No Comprobado';
							}
							elseif ($request->payment == 1 && $request->expenses->first()->reintegro>0 && $request->free == 0) 
							{
								$tempArray['reintegro'] = 'No Comprobado';
							}
							elseif ($request->expenses->first()->reintegro==0) 
							{
								$tempArray['reintegro'] = 'No Aplica';
							}
							else
							{
								$tempArray['reintegro'] = 'No Aplica';
							}
							$results[$key]['concepts'][]    = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
						}
						$key++;             
						break;
					case 8:
						$results[$key]['folio']     = $request->folio;
						$results[$key]['status']    = $request->statusrequest->description;
						$results[$key]['kind']      = $request->requestkind->kind;
						$expense                    = App\RequestModel::join('expenses','request_models.folio','expenses.idFolio')->whereIn('status',[4,5,10,11,12])->where('resourceId',$request->folio)->first();
						$check                      = '';
						if ($expense != null) 
						{
							$check = "SÍ";
						}
						else
						{
							$check = "NO";
						}
						
						$results[$key]['check']                 =  $check;
						$results[$key]['folioResource']         =  '';
						$results[$key]['title']                 =  $request->resource->first()->title.' '.$request->resource->first()->datetitle;
						$results[$key]['numberOrder']           =  '';
						$results[$key]['requestUser']           =  $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         =  $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         =  date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     =  $request->requestEnterprise->name;
						$results[$key]['requestDirection']      =  $request->requestDirection->name;
						$results[$key]['requestDepartment']     =  $request->requestDepartment->name;
						$results[$key]['requestProject']        =  $request->requestProject->proyectName;
						$results[$key]['requestAccount']        =  'Varias';
						$results[$key]['reviewedUser']          =  $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            =  $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    =  $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     =  $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    =  $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       =  $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       =  $request->reviewedEnterprise()->exists() ? 'Varias' : '';
						$results[$key]['authorizedUser']        =  $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         =  $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                =  $request->resource->first()->total;
						$results[$key]['providerName']          =  '';
						$results[$key]['reference']             =  '';

						if($request->resource->first()->idpaymentMethod!='')
						{
							$results[$key]['paymentMode']   =  $request->resource->first()->paymentMethod->method;
							if($request->resource->first()->idpaymentMethod==1)
							{
								$results[$key]['bankName']      = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->bank->description : '';
								$results[$key]['bankAccount']   = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->account.' ' : '';
								$results[$key]['bankCard']      = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->cardNumber.' ' : '';
								$results[$key]['bankBranch']    = '';
								$results[$key]['bankReference'] = '';
								$results[$key]['bankClabe']     = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->clabe.' ' : '';
								$results[$key]['bankCurrency']  = $request->resource->first()->currency;
								$results[$key]['bankAgreement'] = '';
							}
							else
							{
								$results[$key]['bankName']      = '';
								$results[$key]['bankAccount']   = '';
								$results[$key]['bankCard']      = '';
								$results[$key]['bankBranch']    = '';
								$results[$key]['bankReference'] = '';
								$results[$key]['bankClabe']     = '';
								$results[$key]['bankCurrency']  = $request->resource->first()->currency;
								$results[$key]['bankAgreement'] = '';
							}
						}
						else
						{
							$results[$key]['bankName']      = 'Sin método de pago';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = $request->resource->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						$print[$request->folio] = $request->folio;
						foreach($request->resource->first()->resourceDetail as $detail)
						{
							$tempArray                              = array();
							$tempArray['taxPayment']                = '';
							$tempArray['detailQuantity']            = '';
							$tempArray['detailUnit']                = '';
							$tempArray['detailDescription']         = $detail->concept;
							$tempArray['detailAccount']             = $detail->accountsReview()->exists() ? $detail->accountsReview->account.' '.$detail->accountsReview->description.' ('.$detail->accountsReview->content.')' : $detail->accounts->account.' '.$detail->accounts->description.' ('.$detail->accounts->content.')';
							$tempArray['detailUnitPrice']           = '';
							$tempArray['detailSubtotal']            = '';
							$tempArray['detailTax']                 = 0;
							$tempArray['detailTaxesConcept']        = '';
							$tempArray['detailRetentionConcept']    = '';
							$tempArray['detailAmount']              = $detail->amount;
							if ($check == "SÍ") 
							{
								$tempArray['detailAmountResource']  = $expense['total'];
								$tempArray['diferenceRequest']      = $expense['total']-$request->resource->first()->total;
							}
							else
							{
								$tempArray['detailAmountResource']  = '';
								$tempArray['diferenceRequest']      = '';
							}
							$tempArray['reembolso'] =  '';
							$tempArray['reintegro'] =  '';
							$results[$key]['concepts'][] = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
							
						}
						$key++;
						break;

					case 9:
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->refunds->first()->title.' - '.$request->refunds->first()->datetitle;
						$results[$key]['numberOrder']           = '';
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->requestEnterprise->name;
						$results[$key]['requestDirection']      = $request->requestDirection->name;
						$results[$key]['requestDepartment']     = $request->requestDepartment->name;
						$results[$key]['requestProject']        = $request->requestProject->proyectName;
						$results[$key]['requestAccount']        = 'Varias';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->reviewedProject()->exists() ? 'Varias' : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->refunds->first()->total;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = $request->refunds->first()->reference;
						$results[$key]['paymentMode']           = $request->refunds->first()->paymentMethod->method;

						if($request->refunds->first()->idEmployee!='')
						{
							$results[$key]['bankName']      = $request->refunds->first()->bankData->bank->description;
							$results[$key]['bankAccount']   = $request->refunds->first()->bankData->account.' ';
							$results[$key]['bankCard']      = $request->refunds->first()->bankData->cardNumber.' ';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = $request->refunds->first()->bankData->clabe.' ';
							$results[$key]['bankCurrency']  = $request->refunds->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = $request->refunds->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						foreach ($request->refunds->first()->refundDetail as $detail) 
						{
							$print[$request->folio]         = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $detail->taxPayment==1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = '';
							$tempArray['detailUnit']        = '';
							$tempArray['detailDescription'] = $detail->concept;
							$tempArray['detailAccount']     = $detail->accountR()->exists() ? $detail->accountR->account.' '.$detail->accountR->description.' ('.$detail->accountR->content.')' : $detail->account->account.' '.$detail->account->description.' ('.$detail->account->content.')';
							$tempArray['detailUnitPrice']   = '';
							$tempArray['detailSubtotal']    = $detail->amount;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$tempArray['detailTaxesConcept']        = $taxesConcept;
							$tempArray['detailRetentionConcept']    = '';
							$tempArray['detailAmount']              = $detail->sAmount;
							
							$tempArray['detailAmountResource']      = '';
							$tempArray['diferenceRequest']          = '';
							$tempArray['reembolso']                 = '';
							$tempArray['reintegro']                 = '';

							$results[$key]['concepts'][] = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
						}
						$key++;
						break;

					case 11:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->adjustment->first()->title.' - '.$request->adjustment->first()->datetitle;
						$results[$key]['numberOrder']           = $request->adjustment->first()->numberOrder;
						$results[$key]['requestUser']           = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->requestEnterprise->name;
						$results[$key]['requestDirection']      = $request->requestDirection->name;
						$results[$key]['requestDepartment']     = $request->requestDepartment->name;
						$results[$key]['requestProject']        = $request->requestProject->proyectName;
						$results[$key]['requestAccount']        = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->adjustment->first()->amount;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = '';
						$results[$key]['paymentMode']           = '';
						$results[$key]['bankName']              = '';
						$results[$key]['bankAccount']           = '';
						$results[$key]['bankCard']              = '';
						$results[$key]['bankBranch']            = '';
						$results[$key]['bankReference']         = '';
						$results[$key]['bankClabe']             = '';
						$results[$key]['bankCurrency']          = '';
						$results[$key]['bankAgreement']         = '';
						
						$print[$request->folio] = $request->folio;
						$tempArray                              = array();
						$tempArray['taxPayment']                = '';
						$tempArray['detailQuantity']            = '';
						$tempArray['detailUnit']                = '';
						$tempArray['detailDescription']         = '';
						$tempArray['detailAccount']             = '';
						$tempArray['detailUnitPrice']           = '';
						$tempArray['detailSubtotal']            = '';
						$tempArray['detailTax']                 = '';
						$tempArray['detailTaxesConcept']        = '';
						$tempArray['detailRetentionConcept']    = '';
						$tempArray['detailAmount']              = '';
						$tempArray['detailAmountResource']      = '';
						$tempArray['diferenceRequest']          = '';
						$tempArray['reembolso']                 = '';
						$tempArray['reintegro']                 = '';
						$results[$key]['concepts'][]            = $tempArray;
						$totalIvaRequest += $tempArray['detailTax'];
						
						$key++;
						break;

					case 12:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->loanEnterprise->first()->title.' - '.$request->loanEnterprise->first()->datetitle;
						$results[$key]['numberOrder']           = '';
						$results[$key]['requestUser']           = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->loanEnterprise->first()->enterpriseOrigin()->exists() ? $request->loanEnterprise->first()->enterpriseOrigin->name : '';
						$results[$key]['requestDirection']      = '';
						$results[$key]['requestDepartment']     = '';
						$results[$key]['requestProject']        = '';
						$results[$key]['requestAccount']        = $request->loanEnterprise->first()->accountOrigin()->exists() ? $request->loanEnterprise->first()->accountOrigin->account.' - '.$request->loanEnterprise->first()->accountOrigin->description : '';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->loanEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->loanEnterprise->first()->enterpriseOriginReviewed->name : '';
						$results[$key]['reviewedDirection']     = '';
						$results[$key]['reviewedDepartment']    = '';
						$results[$key]['reviewedProject']       = '';
						$results[$key]['reviewedAccount']       = $request->loanEnterprise->first()->accountOriginReviewed()->exists() ? $request->loanEnterprise->first()->accountOriginReviewed->account.' - '.$request->loanEnterprise->first()->accountOriginReviewed->description : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->loanEnterprise->first()->amount;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = '';
						$results[$key]['paymentMode']           = $request->loanEnterprise->first()->paymentMethod()->exists() ? $request->loanEnterprise->first()->paymentMethod->method : '';
						
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';

						$print[$request->folio] = $request->folio;
						$tempArray                              = array();
						$tempArray['taxPayment']                = '';
						$tempArray['detailQuantity']            = '';
						$tempArray['detailUnit']                = '';
						$tempArray['detailDescription']         = '';
						$tempArray['detailAccount']             = '';
						$tempArray['detailUnitPrice']           = '';
						$tempArray['detailSubtotal']            = '';
						$tempArray['detailTax']                 = '';
						$tempArray['detailTaxesConcept']        = '';
						$tempArray['detailRetentionConcept']    = '';
						$tempArray['detailAmount']              = '';
						$tempArray['detailAmountResource']      = '';
						$tempArray['diferenceRequest']          = '';
						$tempArray['reembolso']                 = '';
						$tempArray['reintegro']                 = '';
						$results[$key]['concepts'][]            = $tempArray;
						$totalIvaRequest += $tempArray['detailTax'];
						$key++;
						break;

					case 13:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->purchaseEnterprise->first()->title.' - '.$request->purchaseEnterprise->first()->datetitle;
						$results[$key]['numberOrder']           = $request->purchaseEnterprise->first()->numberOrder;
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->purchaseEnterprise->first()->enterpriseOrigin()->exists() ? $request->purchaseEnterprise->first()->enterpriseOrigin->name : '';
						$results[$key]['requestDirection']      = $request->purchaseEnterprise->first()->areaOrigin()->exists() ? $request->purchaseEnterprise->first()->areaOrigin->name : '';
						$results[$key]['requestDepartment']     = $request->purchaseEnterprise->first()->departmentOrigin()->exists() ? $request->purchaseEnterprise->first()->departmentOrigin->name : '';
						$results[$key]['requestProject']        = $request->purchaseEnterprise->first()->projectOrigin()->exists() ? $request->purchaseEnterprise->first()->projectOrigin->proyectName : '';
						$results[$key]['requestAccount']        = $request->purchaseEnterprise->first()->accountOrigin()->exists() ? $request->purchaseEnterprise->first()->accountOrigin->account.' - '.$request->purchaseEnterprise->first()->accountOrigin->description : '';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->purchaseEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->enterpriseOriginReviewed->name : '';
						$results[$key]['reviewedDirection']     = $request->purchaseEnterprise->first()->areaOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->areaOriginReviewed->name : '';
						$results[$key]['reviewedDepartment']    = $request->purchaseEnterprise->first()->departmentOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->departmentOriginReviewed->name : '';
						$results[$key]['reviewedProject']       = $request->purchaseEnterprise->first()->projectOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->projectOriginReviewed->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->purchaseEnterprise->first()->accountOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->accountOriginReviewed->account.' - '.$request->purchaseEnterprise->first()->accountOriginReviewed->description : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->purchaseEnterprise->first()->amount;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = $request->purchaseEnterprise->first()->reference;
						$results[$key]['paymentMode']           = $request->purchaseEnterprise->first()->paymentMethod->method;
						if($request->purchaseEnterprise->first()->idbanksAccounts!='')
						{
							$results[$key]['bankName']      = $request->purchaseEnterprise->first()->banks->bank->description;
							$results[$key]['bankAccount']   = $request->purchaseEnterprise->first()->banks->account.' ';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = $request->purchaseEnterprise->first()->banks->branch;
							$results[$key]['bankReference'] = $request->purchaseEnterprise->first()->banks->reference;
							$results[$key]['bankClabe']     = $request->purchaseEnterprise->first()->banks->clabe.' ';
							$results[$key]['bankCurrency']  = $request->purchaseEnterprise->first()->banks->currency;
							$results[$key]['bankAgreement'] = $request->purchaseEnterprise->first()->banks->agreement;
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = '';
							$results[$key]['bankAgreement'] = '';
						}
						
						foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail) 
						{
							$print[$request->folio]                 = $request->folio;
							$tempArray                              = array();
							$tempArray['taxPayment']                = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']            = $detail->quantity;
							$tempArray['detailUnit']                = $detail->unit;
							$tempArray['detailDescription']         = $detail->description;
							$tempArray['detailAccount']             = '';
							$tempArray['detailUnitPrice']           = $detail->unitPrice;
							$tempArray['detailSubtotal']            = $detail->subtotal;
							$tempArray['detailTax']                 = $detail->tax;
							$tempArray['detailTaxesConcept']        = $detail->taxes()->sum('amount');
							$tempArray['detailRetentionConcept']    = $detail->retentions()->sum('amount');
							$tempArray['detailAmount']              = $detail->amount;
							$tempArray['detailAmountResource']      = '';
							$tempArray['diferenceRequest']          = '';
							$tempArray['reembolso']                 = '';
							$tempArray['reintegro']                 = '';
							$results[$key]['concepts'][]            = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
						}
						$key++;
						break;

					case 14:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->groups->first()->title.' - '.$request->groups->first()->datetitle;
						$results[$key]['numberOrder']           = $request->groups->first()->numberOrder;
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->groups->first()->enterpriseOrigin()->exists() ? $request->groups->first()->enterpriseOrigin->name : '';
						$results[$key]['requestDirection']      = $request->groups->first()->areaOrigin()->exists() ? $request->groups->first()->areaOrigin->name : '';
						$results[$key]['requestDepartment']     = $request->groups->first()->departmentOrigin()->exists() ? $request->groups->first()->departmentOrigin->name : '';
						$results[$key]['requestProject']        = $request->groups->first()->projectOrigin()->exists() ? $request->groups->first()->projectOrigin->proyectName : '';
						$results[$key]['requestAccount']        = $request->groups->first()->accountOrigin()->exists() ? $request->groups->first()->accountOrigin->account.' - '.$request->groups->first()->accountOrigin->description : '';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->groups->first()->enterpriseOriginReviewed()->exists() ? $request->groups->first()->enterpriseOriginReviewed->name : '';
						$results[$key]['reviewedDirection']     = $request->groups->first()->areaOriginReviewed()->exists() ? $request->groups->first()->areaOriginReviewed->name : '';
						$results[$key]['reviewedDepartment']    = $request->groups->first()->departmentOriginReviewed()->exists() ? $request->groups->first()->departmentOriginReviewed->name : '';
						$results[$key]['reviewedProject']       = $request->groups->first()->projectOriginReviewed()->exists() ? $request->groups->first()->projectOriginReviewed->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->groups->first()->accountOriginReviewed()->exists() ? $request->groups->first()->accountOriginReviewed->account.' - '.$request->groups->first()->accountOriginReviewed->description : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->groups->first()->amount;
						$results[$key]['providerName']          = $request->groups->first()->provider()->exists() ? $request->groups->first()->provider->businessName : '';
						$results[$key]['reference']             = $request->groups->first()->reference;
						$results[$key]['paymentMode']           = $request->groups->first()->paymentMethod->method;
						if($request->groups->first()->provider_has_banks_id!='')
						{
							$results[$key]['bankName']      = $request->groups->first()->bankData->bank->description;
							$results[$key]['bankAccount']   = $request->groups->first()->bankData->account.' ';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = $request->groups->first()->bankData->branch;
							$results[$key]['bankReference'] = $request->groups->first()->bankData->reference;
							$results[$key]['bankClabe']     = $request->groups->first()->bankData->clabe.' ';
							$results[$key]['bankCurrency']  = $request->groups->first()->bankData->currency;
							$results[$key]['bankAgreement'] = $request->groups->first()->bankData->agreement;
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = '';
							$results[$key]['bankAgreement'] = '';
						}
						
						foreach ($request->groups->first()->detailGroups as $detail) 
						{
							$print[$request->folio]                 = $request->folio;
							$tempArray                              = array();
							$tempArray['taxPayment']                = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']            = $detail->quantity;
							$tempArray['detailUnit']                = $detail->unit;
							$tempArray['detailDescription']         = $detail->description;
							$tempArray['detailAccount']             = '';
							$tempArray['detailUnitPrice']           = $detail->unitPrice;
							$tempArray['detailSubtotal']            = $detail->subtotal;
							$tempArray['detailTax']                 = $detail->tax;
							$tempArray['detailTaxesConcept']        = $detail->taxes()->sum('amount');
							$tempArray['detailRetentionConcept']    = $detail->retentions()->sum('amount');
							$tempArray['detailAmount']              = $detail->amount;
							$tempArray['detailAmountResource']      = '';
							$tempArray['diferenceRequest']          = '';
							$tempArray['reembolso']                 = '';
							$tempArray['reintegro']                 = '';
							$results[$key]['concepts'][]            = $tempArray;
							$totalIvaRequest += $tempArray['detailTax'];
						}
						$key++;
						break;

					case 15:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->movementsEnterprise->first()->title.' - '.$request->movementsEnterprise->first()->datetitle;
						$results[$key]['numberOrder']           = '';
						$results[$key]['requestUser']           = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->movementsEnterprise->first()->enterpriseOrigin()->exists() ? $request->movementsEnterprise->first()->enterpriseOrigin->name : '';
						$results[$key]['requestDirection']      = '';
						$results[$key]['requestDepartment']     = '';
						$results[$key]['requestProject']        = '';
						$results[$key]['requestAccount']        = $request->movementsEnterprise->first()->accountOrigin()->exists() ? $request->movementsEnterprise->first()->accountOrigin->account.' - '.$request->movementsEnterprise->first()->accountOrigin->description : '';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->movementsEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->movementsEnterprise->first()->enterpriseOriginReviewed->name : '';
						$results[$key]['reviewedDirection']     = '';
						$results[$key]['reviewedDepartment']    = '';
						$results[$key]['reviewedProject']       = '';
						$results[$key]['reviewedAccount']       = $request->movementsEnterprise->first()->accountOriginReviewed()->exists() ? $request->movementsEnterprise->first()->accountOriginReviewed->account.' - '.$request->movementsEnterprise->first()->accountOriginReviewed->description : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->movementsEnterprise->first()->amount;
						$results[$key]['providerName']          = '';
						$results[$key]['reference']             = '';
						$results[$key]['paymentMode']           = $request->movementsEnterprise->first()->paymentMethod()->exists() ? $request->movementsEnterprise->first()->paymentMethod->method : '';
						
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';

						$print[$request->folio] = $request->folio;
						$tempArray                              = array();
						$tempArray['taxPayment']                = '';
						$tempArray['detailQuantity']            = '';
						$tempArray['detailUnit']                = '';
						$tempArray['detailDescription']         = '';
						$tempArray['detailAccount']             = '';
						$tempArray['detailUnitPrice']           = '';
						$tempArray['detailSubtotal']            = '';
						$tempArray['detailTax']                 = '';
						$tempArray['detailTaxesConcept']        = '';
						$tempArray['detailRetentionConcept']    = '';
						$tempArray['detailAmount']              = '';
						$tempArray['detailAmountResource']      = '';
						$tempArray['diferenceRequest']          = '';
						$tempArray['reembolso']                 = '';
						$tempArray['reintegro']                 = '';
						$results[$key]['concepts'][]            = $tempArray;
						$totalIvaRequest += $tempArray['detailTax'];
						$key++;
						break;

					case 17:
						
						$results[$key]['folio']                 = $request->folio;
						$results[$key]['status']                = $request->statusrequest->description;
						$results[$key]['kind']                  = $request->requestkind->kind;
						$results[$key]['check']                 = '';
						$results[$key]['folioResource']         = '';
						$results[$key]['title']                 = $request->purchaseRecord->title.' - '.$request->purchaseRecord->datetitle;
						$results[$key]['numberOrder']           = $request->purchaseRecord->numberOrder;
						$results[$key]['requestUser']           = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$results[$key]['elaborateUser']         = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$results[$key]['elaborateDate']         = date('d-m-Y H:s',strtotime($request->fDate));
						$results[$key]['requestEnterprise']     = $request->requestEnterprise->name;
						$results[$key]['requestDirection']      = $request->requestDirection->name;
						$results[$key]['requestDepartment']     = $request->requestDepartment->name;
						$results[$key]['requestProject']        = $request->requestProject->proyectName;
						$results[$key]['requestAccount']        = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
						$results[$key]['reviewedUser']          = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
						$results[$key]['reviewDate']            = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
						$results[$key]['reviewedEnterprise']    = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
						$results[$key]['reviewedDirection']     = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
						$results[$key]['reviewedDepartment']    = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
						$results[$key]['reviewedProject']       = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
						$results[$key]['reviewedAccount']       = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
						$results[$key]['authorizedUser']        = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$results[$key]['authorizeDate']         = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
						$results[$key]['amount']                = $request->purchaseRecord->total;
						$results[$key]['providerName']          = $request->purchaseRecord->provider;
						$results[$key]['reference']             = $request->purchaseRecord->reference;
						$results[$key]['paymentMode']           = $request->purchaseRecord->paymentMethod;

						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';
						
						
						foreach ($request->purchaseRecord->detailPurchase as $detail) 
						{
							$idDetailPurchase = $detail->id;
							$print[$request->folio]                 = $request->folio;
							$tempArray                              = array();
							$tempArray['taxPayment']                = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']            = $detail->quantity;
							$tempArray['detailUnit']                = $detail->unit;
							$tempArray['detailDescription']         = $detail->description;
							$tempArray['detailAccount']             = '';
							$tempArray['detailUnitPrice']           = $detail->unitPrice;
							$tempArray['detailSubtotal']            = $detail->subtotal;
							$tempArray['detailTax']                 = $detail->tax;
							$tempArray['detailTaxesConcept']        = $detail->taxes()->sum('amount');;
							$tempArray['detailRetentionConcept']    = $detail->retentions()->sum('amount');;
							$tempArray['detailAmount']              = $detail->total;
							$tempArray['detailAmountResource']      = '';
							$tempArray['diferenceRequest']          = '';
							$tempArray['reembolso']                 = '';
							$tempArray['reintegro']                 = '';
							$results[$key]['concepts'][]            = $tempArray;

							$totalIvaRequest += $tempArray['detailTax'];
						}
						$key++;

						break;

					default:
						
						break;
				}
			}

			Excel::create('IVA', function($excel) use ($results,$print,$requestsIncome,$totalIvaRequest)
			{
				$excel->sheet('Iva acreditable',function($sheet) use ($results,$print)
				{
					$sheet->setStyle([
							'font' => [
								'name'  => 'Calibri',
								'size'  => 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'Y'     => '"$"#,##0.00_-',
						'Z'     => '@',
						'AA'    => '@',
						'AB'    => '@',
						'AC'    => '@',
						'AD'    => '0',
						'AE'    => '0',
						'AG'    => '@',
						'AH'    => '0',
						'AI'    => '@',
						'AJ'    => '@',
						'AO'    => '@',
						'AN'    => '"$"#,##0.00_-',
					));
					$sheet->mergeCells('A1:AN1');

					$sheet->mergeCells('A2:G2');
					$sheet->mergeCells('H2:O2');
					$sheet->mergeCells('P2:V2');
					$sheet->mergeCells('W2:X2');
					$sheet->mergeCells('Y2:AJ2');
					$sheet->mergeCells('AK2:AN2');

					$sheet->cell('A1:AN1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:AN2', function($cells)
					{
						$cells->setBackground('#1d353d');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A3:AN3', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:AN3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Reporte Maestro']);
					$sheet->row(2,['Datos de la solicitud','','','','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos la solicitud','','','','','','','','','','','','Conceptos','','','']);
					$sheet->row(3,['Folio','Estado de solicitud','Tipo','Comprobación','Folio de la solicitud de recurso','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Monto Total de Solicitud','Razón Social','Referencia','Método de pago','Banco','Cuenta','Tarjeta','Sucursal','Referencia','CLABE','Moneda','Convenio',/*'Fiscal/No Fiscal',*/'Cantidad',/*'Unidad',*/'Concepto','Clasificación de gasto',/*'Precio Unitario','Subtotal',*/'IVA',/*'Impuesto Adicional','Retenciones','Importe Total','Etiquetas','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro'*/]);

					$beginMerge = 3;
					foreach ($results as $result)
					{
						if (isset($result['concepts'])) 
						{
							$row    = [];
							$row[]  = $result['folio'];                 
							$row[]  = $result['status'];                
							$row[]  = $result['kind'];                  
							$row[]  = $result['check'];                 
							$row[]  = $result['folioResource'];         
							$row[]  = $result['title'];                 
							$row[]  = $result['numberOrder'];           
							$row[]  = $result['requestUser'];           
							$row[]  = $result['elaborateUser'];         
							$row[]  = $result['elaborateDate'];         
							$row[]  = $result['requestEnterprise'];     
							$row[]  = $result['requestDirection'];      
							$row[]  = $result['requestDepartment'];     
							$row[]  = $result['requestProject'];        
							$row[]  = $result['requestAccount'];        
							$row[]  = $result['reviewedUser'];          
							$row[]  = $result['reviewDate'];            
							$row[]  = $result['reviewedEnterprise'];    
							$row[]  = $result['reviewedDirection'];     
							$row[]  = $result['reviewedDepartment'];    
							$row[]  = $result['reviewedProject'];       
							$row[]  = $result['reviewedAccount'];       
							$row[]  = $result['authorizedUser'];        
							$row[]  = $result['authorizeDate'];         
							$row[]  = $result['amount'];                
							$row[]  = $result['providerName'];          
							$row[]  = $result['reference'];             
							$row[]  = $result['paymentMode'];
							$row[]  = $result['bankName'];      
							$row[]  = $result['bankAccount'];   
							$row[]  = $result['bankCard'];      
							$row[]  = $result['bankBranch'];    
							$row[]  = $result['bankReference']; 
							$row[]  = $result['bankClabe'];     
							$row[]  = $result['bankCurrency'];  
							$row[]  = $result['bankAgreement']; 
							$first  = true;
							foreach($result['concepts'] as $concept)
							{
								if (!$first)
								{
									$row    = array();
									$row[]  = $result['folio'];                 
									$row[]  = $result['status'];                
									$row[]  = $result['kind'];                  
									$row[]  = $result['check'];                 
									$row[]  = $result['folioResource'];         
									$row[]  = $result['title'];                 
									$row[]  = $result['numberOrder'];           
									$row[]  = $result['requestUser'];           
									$row[]  = $result['elaborateUser'];         
									$row[]  = $result['elaborateDate'];         
									$row[]  = $result['requestEnterprise'];     
									$row[]  = $result['requestDirection'];      
									$row[]  = $result['requestDepartment'];     
									$row[]  = $result['requestProject'];        
									$row[]  = $result['requestAccount'];        
									$row[]  = $result['reviewedUser'];          
									$row[]  = $result['reviewDate'];            
									$row[]  = $result['reviewedEnterprise'];    
									$row[]  = $result['reviewedDirection'];     
									$row[]  = $result['reviewedDepartment'];    
									$row[]  = $result['reviewedProject'];       
									$row[]  = $result['reviewedAccount'];       
									$row[]  = $result['authorizedUser'];        
									$row[]  = $result['authorizeDate'];         
									$row[]  = $result['amount'];                
									$row[]  = $result['providerName'];          
									$row[]  = $result['reference'];             
									$row[]  = $result['paymentMode'];
									$row[]  = $result['bankName'];      
									$row[]  = $result['bankAccount'];   
									$row[]  = $result['bankCard'];      
									$row[]  = $result['bankBranch'];    
									$row[]  = $result['bankReference']; 
									$row[]  = $result['bankClabe'];     
									$row[]  = $result['bankCurrency'];  
									$row[]  = $result['bankAgreement']; 
								}
								else
								{
									$first = false;
									$beginMerge++;
								}
								//$row[]    = $concept['taxPayment'];
								$row[]  = $concept['detailQuantity'];   
								//$row[]    = $concept['detailUnit'];       
								$row[]  = $concept['detailDescription'];    
								$row[]  = $concept['detailAccount'] != '' ? $concept['detailAccount'] : $result['reviewedAccount'];     
								//$row[]    = $concept['detailUnitPrice'];  
								//$row[]    = $concept['detailSubtotal'];
								$row[]  = $concept['detailTax'] != '' ? $concept['detailTax'] : 0;      
								//$row[]    = $concept['detailTaxesConcept'];       
								//$row[]    = $concept['detailRetentionConcept'];   
								//$row[]    = $concept['detailAmount'];             
								//$row[]    = $concept['detailLabel'];
								//$row[]    = $concept['detailAmountResource'];
								//$row[]    = $concept['diferenceRequest'];
								//$row[]    = $concept['reembolso'];
								//$row[]    = $concept['reintegro'];
								$sheet->appendRow($row);
							}
						}
						
					}
				});
				
				$excel->sheet('Iva por pagar',function($sheet) use ($requestsIncome,$totalIvaRequest)
				{
					$sheet->setStyle([
							'font' => [
								'name'  => 'Calibri',
								'size'  => 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'Q'     => '@',
						'T'     => '@',
						'Z'     => '"$"#,##0.00_-',
						'AA'    => '"$"#,##0.00_-',
						'AB'    => '"$"#,##0.00_-',
						'AC'    => '"$"#,##0.00_-',
						'AD'    => '"$"#,##0.00_-',
						'AE'    => '"$"#,##0.00_-',
						'AF'    => '"$"#,##0.00_-',
						'AG'    => '"$"#,##0.00_-',
						'AH'    => '"$"#,##0.00_-',
						'AI'    => '"$"#,##0.00_-',
						'AJ'    => '"$"#,##0.00_-',
						'AK'    => '"$"#,##0.00_-',
						'AL'    => '"$"#,##0.00_-',
						'AM'    => '"$"#,##0.00_-',
						'AN'    => '"$"#,##0.00_-',
						'AO'    => '"$"#,##0.00_-',
					));
					$sheet->mergeCells('A1:AO1');

					$sheet->mergeCells('A2:D2');
					$sheet->mergeCells('E2:I2');
					$sheet->mergeCells('J2:K2');
					$sheet->mergeCells('L2:M2');
					$sheet->mergeCells('N2:V2');
					$sheet->mergeCells('W2:AF2');
					$sheet->mergeCells('AG2:AG2');
					$sheet->mergeCells('AH2:AK2');
					$sheet->mergeCells('AL2:AO2');

					$sheet->cell('A1:A01', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:AO2', function($cells)
					{
						$cells->setBackground('#1d353d');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A3:AO3', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:AO3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['IVA POR PAGAR']);
					$sheet->row(2,['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','IVA facturado','','','','IVA Cobrado','','','']);
					$sheet->row(3,
						[
							'Folio',
							'Estado de Solicitud',
							'Título',
							'Fiscal/No fiscal',
							'Solicitante',
							'Elaborado por',
							'Fecha de elaboración',
							'Empresa',
							'Proyecto',
							'Revisada por',
							'Fecha de revisión',
							'Autorizada por',
							'Fecha de autorización',
							'Razón Social',
							'Banco',
							'Alias',
							'Cuenta',
							'Sucursal',
							'Referencia',
							'CLABE',
							'Moneda',
							'Convenio',
							'Cantidad',
							'Unidad',
							'Descripción',
							'Precio Unitario',
							'Subtotal',
							'IVA',
							'Impuesto Adicional',
							'Retenciones',
							'Importe',
							'Importe Total',
							'Monto proyectado',
							'Subtotal',
							'Traslados',
							'Retenciones',
							'Monto facturado',
							'Subtotal',
							'Traslados',
							'Retenciones',
							'Monto cobrado',
							//'Monto por pagar',
							//'Monto por facturar',
						]);

					$beginMerge = 3;
					$totalIvaPay = 0;
					$totalIvaBill = 0;
					$countRow = 0;
					foreach ($requestsIncome as $request)
					{
						$tempCount  = 0;
						$row        = [];
						$row[]      = $request->folio;
						$row[]      = $request->statusrequest->description;
						$row[]      = $request->income->first()->title.' - '.$request->income->first()->datetitle;
						$row[]      = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
						$row[]      = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$row[]      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$row[]      = date('d-m-Y H:s',strtotime($request->fDate));
						$row[]      = $request->requestEnterprise->name;
						$row[]      = $request->requestProject->proyectName;
						$row[]      = $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name;
						$row[]      = date('d-m-Y H:s',strtotime($request->reviewDate));
						$row[]      = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
						$row[]      = date('d-m-Y H:s',strtotime($request->authorizeDate));
						
						if($request->income->first()->idbanksAccounts!='')
						{
							$row[]  = $request->requestEnterprise->name;
							$row[]  = $request->income->first()->bankData->bank->description;
							$row[]  = $request->income->first()->bankData->alias;
							$row[]  = $request->income->first()->bankData->account;
							$row[]  = $request->income->first()->bankData->branch;
							$row[]  = $request->income->first()->bankData->reference;
							$row[]  = $request->income->first()->bankData->clabe;
							$row[]  = $request->income->first()->bankData->currency;
							$row[]  = $request->income->first()->bankData->agreement;
						}
						else
						{
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
							$row[]  = '';
						}
						$first = true;
						foreach($request->income->first()->incomeDetail as $detail)
						{
							if (!$first)
							{
								$row    = array();
								$row    = [];
								$row[]  = $request->folio;
								$row[]  = $request->statusrequest->description;
								$row[]  = $request->income->first()->title.' - '.$request->income->first()->datetitle;
								$row[]  = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
								$row[]  = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
								$row[]  = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
								$row[]  = date('d-m-Y H:s',strtotime($request->fDate));
								$row[]  = $request->requestEnterprise->name;
								$row[]  = $request->requestProject->proyectName;
								$row[]  = $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name;
								$row[]  = date('d-m-Y H:s',strtotime($request->reviewDate));
								$row[]  = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
								$row[]  = date('d-m-Y H:s',strtotime($request->authorizeDate));
								
								if($request->income->first()->idbanksAccounts!='')
								{
									$row[]  = $request->requestEnterprise->name;
									$row[]  = $request->income->first()->bankData->bank->description;
									$row[]  = $request->income->first()->bankData->alias;
									$row[]  = $request->income->first()->bankData->account;
									$row[]  = $request->income->first()->bankData->branch;
									$row[]  = $request->income->first()->bankData->reference;
									$row[]  = $request->income->first()->bankData->clabe;
									$row[]  = $request->income->first()->bankData->currency;
									$row[]  = $request->income->first()->bankData->agreement;
								}
								else
								{
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
								}
							}
							else
							{
								$first = false;
								$beginMerge++;
							}
							$row[]  = $detail->quantity;
							$row[]  = $detail->unit;
							$row[]  = $detail->description;
							$row[]  = $detail->unitPrice;
							$row[]  = $detail->subtotal;
							$row[]  = $detail->tax;

							$ivaTotal = $detail->tax;

							$taxesConcept   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$row[]              = $taxesConcept;

							$retentionConcept   = 0;
							foreach($detail->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}

							$row[]  = $retentionConcept;
							$row[]  = $detail->amount;
							$row[]  = $request->income->first()->amount;

							$totalProjected = $request->income->first()->amount;

							$row[]  = $totalProjected;

							if ($request->taxPayment == 1) 
							{
								$subtotalBill   = 0;
								$trasBill       = 0;
								$retBill        = 0;
								$totalBill      = 0;
								
								$subtotalPay    = 0;
								$trasPay        = 0;
								$retPay         = 0;
								$totalPay       = 0;

								foreach ($request->bill->where('status',1) as $bill) 
								{
									$subtotalBill   += $bill->subtotal;
									$retBill        += $bill->ret;
									$totalBill      += $bill->total;
									foreach ($bill->billDetail as $detail)
									{
										$trasBill   += $detail->taxesTrasIva->sum('amount');
									}
								}

								foreach ($request->bill->where('status',2) as $bill) 
								{
									$subtotalPay    += $bill->subtotal;
									$retPay         += $bill->ret;
									$totalPay       += $bill->total;

									foreach ($bill->billDetail as $detail) 
									{
										$trasPay    += $detail->taxesTrasIva->sum('amount');
									}
								}

								$row[]  = $subtotalBill;
								$row[]  = $trasBill;
								$row[]  = $retBill;
								$row[]  = $totalBill;
								$row[]  = $subtotalPay;
								$row[]  = $trasPay;
								$row[]  = $retPay;
								$row[]  = $totalPay;
								$totalIvaPay += $trasPay;
								$totalIvaBill += $trasBill;

							}
							else
							{
								$subtotalBill   = 0;
								$trasBill       = 0;
								$retBill        = 0;
								$totalBill      = 0;

								foreach ($request->billNF->where('status',1) as $b) 
								{
									$subtotalPay    += $b->subtotal;
									$totalPay       += $b->total;
								}

								$row[]  = '0';
								$row[]  = '0';
								$row[]  = '0';
								$row[]  = '0';
								$row[]  = $subtotalPay;
								$row[]  = '0';
								$row[]  = '0';
								$row[]  = $totalPay;
							}
							$tempCount++;
							$countRow++;
							$sheet->appendRow($row);
						}
					}
					$row = [];
					for ($i=0; $i < 41; $i++) 
					{ 
						$row[]  = '';
					}
					$countRow++;
					$sheet->appendRow($row);

					$row = [];
					for ($i=0; $i < 41; $i++) 
					{ 
						$row[]  = '';
					}
					$countRow++;
					$sheet->appendRow($row);

					$row = [];
					$row[] = 'IVA Acreditable';
					$row[] = -$totalIvaRequest;
					$countRow++;
					$sheet->appendRow($row);
					$sheet->cell('A'.($countRow+3).':A'.($countRow+3).'', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->setColumnFormat(array(
						'B'.($countRow+3).'' => '"$"#,##0.00_-',
					));

					$row = [];
					$row[] = 'IVA Facturado';
					$row[] = $totalIvaBill;
					$countRow++;
					$sheet->appendRow($row);
					$sheet->cell('A'.($countRow+3).':A'.($countRow+3).'', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->setColumnFormat(array(
						'B'.($countRow+3).'' => '"$"#,##0.00_-',
					));

					$row = [];
					$row[] = 'IVA Cobrado';
					$row[] = $totalIvaPay;
					$countRow++;
					$sheet->appendRow($row);
					$sheet->cell('A'.($countRow+3).':A'.($countRow+3).'', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->setColumnFormat(array(
						'B'.($countRow+3).'' => '"$"#,##0.00_-',
					));

					
				});
			})->export('xlsx');
		}
	}
}
