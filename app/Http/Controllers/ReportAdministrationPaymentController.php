<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class ReportAdministrationPaymentController extends Controller
{
	private $module_id = 96;
	public function paymentsReport(Request $request)
	{
		if(Auth::user()->module->where('id',188)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account		= $request->account;
			$enterpriseid	= $request->enterpriseid;
			$projectid		= $request->projectid;
			$folio			= $request->folio;
			$kind			= $request->kind;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$conciliation	= $request->conciliation;

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = $mindate;
				$endRange   = $maxdate;

				if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate!="" && $maxdate!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$payments     = App\Payment::where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(188)->pluck('enterprise_id'))
								->orWhere('idEnterprise',null);
				})
				->whereHas('request',function($query)
				{
					$query->whereIn('idDepartment',Auth::user()->inChargeDep(188)->pluck('departament_id'))->orWhere('idDepartment',null);
				})
				->where(function ($query) use ($account, $mindate, $maxdate, $folio,$kind,$enterpriseid,$conciliation,$projectid)
				{
					if($enterpriseid != "")
					{
						$query->where('idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$query->where('account',$account);
					}
					if($projectid != '')
					{
						$query->where(function($q) use($projectid)
						{
							$q->whereHas('request',function($q) use($projectid)
								{
									$q->where('idProject',$projectid)
										->orWhere('idProjectR',$projectid);
								})
								->orWhereHas('nominaEmployee', function($q) use($projectid)
								{
									$q->whereHas('workerData',function($q) use($projectid)
									{
										$q->where('project',$projectid);
									});
								});
						});
					}
					if($folio != "")
					{
						$query->where('idFolio',$folio);
					}   
					if($kind != "")
					{
						$query->where('idKind',$kind);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('paymentDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if($conciliation != '' && $conciliation != 'all')
					{
						$query->where('statusConciliation',$conciliation);
					}
				})
				->orderBy('paymentDate','DESC')
				->orderBy('idFolio','DESC')
				->paginate(10);
			return view('reporte.administracion.pagos',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 188,
					'payments'     => $payments,
					'account'      => $account,
					'folio'        => $folio,
					'kind'         => $kind,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'enterpriseid' => $enterpriseid,
					'conciliation' => $conciliation,
					'projectid'    => $projectid
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function paymentsExport(Request $request)
	{
		if(Auth::user()->module->where('id',188)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account		= $request->account;
			$enterpriseid	= $request->enterpriseid;
			$projectid		= $request->projectid;
			$folio			= $request->folio;
			$kind			= $request->kind;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$conciliation	= $request->conciliation;

			$payments   = DB::table('payments')
						->selectRaw('
							payments.idFolio as idFolio,
							request_kinds.kind as kind,
							enterprises.name as enterpriseName,
							CONCAT_WS(" ",accounts.account,accounts.description) as accountName,
							DATE_FORMAT(payments.paymentDate, "%d-%m-%Y") as paymentDate,
							IF(payments.idKind = 1, purchases.title,
								IF(payments.idKind = 3, expenses.title, 
									IF(payments.idKind = 5, loans.title,
										IF(payments.idKind = 8, resources.title,
											IF(payments.idKind = 9, refunds.title,
												IF(payments.idKind = 11, adjustments.title,
													IF(payments.idKind = 12, loan_enterprises.title,
														IF(payments.idKind = 13, purchase_enterprises.title,
															IF(payments.idKind = 14, groups.title,
																IF(payments.idKind = 15, movements_enterprises.title,
																	IF(payments.idKind = 16, nominas.title,
																		IF(payments.idKind = 17, purchase_records.title, " ")
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
							) as concept,
							IF(payments.idKind = 1, providers.businessName,
								IF(payments.idKind = 3, CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name), 
									IF(payments.idKind = 5, CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name),
										IF(payments.idKind = 8, CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name),
											IF(payments.idKind = 9, CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name),
												IF(payments.idKind = 11, adjustments.title,
													IF(payments.idKind = 12, loan_enterprises.title,
														IF(payments.idKind = 13, purchase_enterprises.title,
															IF(payments.idKind = 14, groups.title,
																IF(payments.idKind = 15, movements_enterprises.title,
																	IF(payments.idKind = 16, CONCAT_WS(" ",real_employees.name,real_employees.last_name,real_employees.scnd_last_name),
																		IF(payments.idKind = 17, purchase_records.provider, " ")
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
							) as paid_to,
							payments.commentaries as commentaries,
							payments.amount as amount
						')
						->leftJoin('request_models','request_models.folio','payments.idFolio')
						->leftJoin('request_kinds','payments.idKind','request_kinds.idrequestkind')
						->leftJoin('enterprises','enterprises.id','payments.idEnterprise')
						->leftJoin('accounts','accounts.idAccAcc','payments.account')
						->leftJoin('purchases','request_models.folio','purchases.idFolio')
						->leftJoin('providers','providers.idProvider','purchases.idProvider')
						->leftJoin('expenses','request_models.folio','expenses.idFolio')
						->leftJoin('loans','request_models.folio','loans.idFolio')
						->leftJoin('resources','request_models.folio','resources.idFolio')
						->leftJoin('refunds','request_models.folio','refunds.idFolio')
						->leftJoin('adjustments','request_models.folio','adjustments.idFolio')
						->leftJoin('loan_enterprises','request_models.folio','loan_enterprises.idFolio')
						->leftJoin('purchase_enterprises','request_models.folio','purchase_enterprises.idFolio')
						->leftJoin('groups','request_models.folio','groups.idFolio')
						->leftJoin('movements_enterprises','request_models.folio','movements_enterprises.idFolio')
						->leftJoin('purchase_records','request_models.folio','purchase_records.idFolio')
						->leftJoin('nominas','request_models.folio','nominas.idFolio')
						->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
						->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
						->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
						->leftJoin('users','request_models.idRequest','users.id')
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('payments.idEnterprise',Auth::user()->inChargeEnt(188)->pluck('enterprise_id'))
										->orWhere('payments.idEnterprise',null);
						})
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(188)->pluck('departament_id'))
										->orWhere('request_models.idDepartment',null);
						})
						->where(function ($query) use ($account, $mindate, $maxdate, $folio,$kind,$enterpriseid,$conciliation,$projectid)
						{
							if($enterpriseid != "")
							{
								$query->where('payments.idEnterprise',$enterpriseid);
							}
							if($account != "")
							{
								$query->where('payments.account',$account);
							}
							if($projectid != '')
							{
								$query->where(function($q) use($projectid)
								{
									$q->where('request_models.idProject',$projectid)->orWhere('request_models.idProjectR',$projectid)->orWhere('worker_datas.project',$projectid);
								});
							}
							if($folio != "")
							{
								$query->where('payments.idFolio',$folio);
							}   
							if($kind != "")
							{
								$query->where('payments.idKind',$kind);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('payments.paymentDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if($conciliation != '' && $conciliation != 'all')
							{
								$query->where('payments.statusConciliation',$conciliation);
							}
						})
						->orderBy('payments.paymentDate','DESC')
						->orderBy('payments.idFolio','DESC')
						->get();


			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Pagos.xlsx');

			$headers        = ['Reporte de Pagos','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Tipo de Solicitud','Empresa','Clasificación del gasto','Fecha de pago','Concepto','Pagado a','Comentarios','Importe'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio  = '';
			$kindRow    = true;

			foreach($payments as $payment)
			{
				if($tempFolio != $payment->idFolio)
				{
					$tempFolio = $payment->idFolio;
					$kindRow = !$kindRow;
				}

				$tmpArr = [];
				foreach($payment as $k => $r)
				{
					if(in_array($k,['amount']))
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

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function paymentsDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$payment = App\Payment::find($request->idpayment);
			return view('reporte.administracion.partial.modal_pago')->with('payment',$payment);
		}
	}
}
