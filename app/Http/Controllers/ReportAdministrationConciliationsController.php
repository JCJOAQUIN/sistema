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

class ReportAdministrationConciliationsController extends Controller
{
	private $module_id = 96;
	public function conciliationReport(Request $request)
	{
		if (Auth::user()->module->where('id',189)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$enterpriseid	= $request->enterpriseid;
			$mov			= $request->mov;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$account		= $request->account;

			$initRange      = "";
			$endRange       = "";

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = Carbon::parse($mindate)->format('Y-m-d');
				$endRange   = Carbon::parse($maxdate)->format('Y-m-d');

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

			$searchMov    = App\Movement::select('idmovement')->where('description','LIKE','%'.$mov.'%')->get();
			$movements    = App\Movement::join('payments','payments.idpayment','=','movements.idpayment')
				->join('request_models','payments.idFolio','=','request_models.folio')
				->select('movements.idmovement', 'movements.amount', 'movements.idEnterprise', 'movements.idAccount', 'movements.description','movements.conciliationDate','payments.idFolio','payments.amount as amount_pay','payments.remittance as remittance')
				->where('movements.statusConciliation',1)
				->where(function($query)
				{
					$query->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(189)->pluck('departament_id'))
						->orWhere('request_models.idDepartment',null);
				})
				->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(189)->pluck('enterprise_id'))
				->whereNotNull('movements.idpayment')
				->where(function($query) use ($mindate,$maxdate,$enterpriseid,$account,$mov,$initRange,$endRange)
				{
					if ($mov != "") 
					{
						$query->where('movements.description','LIKE','%'.$mov.'%');
					}
					if ($enterpriseid != "") 
					{
						$query->where('movements.idEnterprise',$enterpriseid);
					}
					if ($account != "") 
					{
						$query->where('movements.idAccount',$account);
					}
					if ($mindate != "" && $maxdate != "") 
					{
						$query->whereBetween('movements.conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('movements.idpayment','desc')
				->orderBy('movements.conciliationDate','DESC')
				->paginate(10);
			return view('reporte.administracion.conciliacion_normal',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 189,
					'movements'    => $movements,
					'account'      => $account,
					'mov'          => $mov,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'enterpriseid' => $enterpriseid,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function conciliationExport(Request $request)
	{
		if (Auth::user()->module->where('id',189)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$enterpriseid   = $request->enterpriseid;
			$mov            = $request->mov;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$account        = $request->account;

			$movements      = DB::table('movements')
							->selectRaw('
								IF(payments.remittance = 1, "Remesa", "Pago") as remittance,
								payments.idFolio as idFolio,
								request_kinds.kind as kind,
								enterprises.name as enterpriseName,
								CONCAT_WS(" ",accounts.account,accounts.description) as accountName,
								payments.amount as amountPay,
								movements.description as movementDescription,
								movements.amount as movementAmount, 
								DATE_FORMAT(payments.conciliationDate, "%d-%m-%Y") as conciliationDate
							')
							->leftJoin('payments','payments.idpayment','movements.idpayment')
							->leftJoin('enterprises','enterprises.id','payments.idEnterprise')
							->leftJoin('accounts','accounts.idAccAcc','payments.account')
							->leftJoin('request_models','request_models.folio','payments.idFolio')
							->leftJoin('request_kinds','request_kinds.idrequestkind','payments.idKind')
							->where('movements.statusConciliation',1)
							->where(function($query)
							{
								$query->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(189)->pluck('departament_id'))
									->orWhere('request_models.idDepartment',null);
							})
							->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(189)->pluck('enterprise_id'))
							->whereNotNull('movements.idpayment')
							->where(function($query) use ($mindate,$maxdate,$enterpriseid,$account,$mov)
							{
								if ($mov != "") 
								{
									$query->where('movements.description','LIKE','%'.$mov.'%');
								}
								if ($enterpriseid != "") 
								{
									$query->where('movements.idEnterprise',$enterpriseid);
								}
								if ($account != "") 
								{
									$query->where('movements.idAccount',$account);
								}
								if ($mindate != "" && $maxdate != "") 
								{
									$query->whereBetween('movements.conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('movements.idpayment','desc')
							->orderBy('movements.conciliationDate','DESC')
							->get();

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Conciliación de Nómina.xlsx');

			$headers = ['Datos del pago','','','','','','Datos del movimiento','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders    = ['Tipo','Folio','Solicitud','Empresa','Clasificación del gasto','Importe','Movimiento','Importe','Fecha de conciliación'];
			$tempSubHeader = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio  = '';
			$kindRow    = true;

			foreach($movements as $movement)
			{
				if($tempFolio != $movement->idFolio)
				{
					$tempFolio = $movement->idFolio;
					$kindRow = !$kindRow;
				}

				$tmpArr = [];
				foreach($movement as $k => $r)
				{
					if(in_array($k,['amountPay','movementAmount']))
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

	public function conciliationNominaReport(Request $request)
	{
		if (Auth::user()->module->where('id',189)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$enterpriseid   = $request->enterpriseid;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$account        = $request->account;
			$folio          = $request->folio;

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

			$payments = App\Payment::where('statusConciliation',1)
				->whereNotNull('idmovement')
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(103)->pluck('enterprise_id'))
				->where(function($query) use ($mindate,$maxdate,$enterpriseid,$account,$folio)
				{
					if ($folio != "") 
					{
						$query->where('idFolio',$folio);
					}
					if ($enterpriseid != "") 
					{
						$query->where('idEnterprise',$enterpriseid);
					}
					if ($account != "") 
					{
						$query->where('account',$account);
					}
					if ($mindate != "" && $maxdate != "") 
					{
						$query->whereBetween('conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('idpayment','DESC')
				->orderBy('conciliationDate','DESC')
				->paginate(10);
			return view('reporte.administracion.conciliacion_nomina',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 189,
					'account'       => $account,
					'mindate'       => $request->mindate,
					'maxdate'       => $request->maxdate,
					'enterpriseid'  => $enterpriseid,
					'payments'      => $payments,
					'folio'         => $folio
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function conciliationNominaExport(Request $request)
	{
		if (Auth::user()->module->where('id',189)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$enterpriseid   = $request->enterpriseid;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$account        = $request->account;
			$folio          = $request->folio;

			$payments = DB::table('payments')
						->selectRaw('
							payments.idFolio as idFolio,
							enterprises.name as enterpriseName,
							CONCAT_WS(" ",accounts.account,accounts.description) as accountName,
							CONCAT_WS(" ",real_employees.last_name,real_employees.scnd_last_name,real_employees.name) as employeeName,
							payments.amount as paymentAmount,
							movements.description as movementDescription,
							movements.amount as movementAmount,
							DATE_FORMAT(payments.conciliationDate, "%d-%m-%Y") as conciliationDate
						')
						->leftJoin('enterprises','enterprises.id','payments.idEnterprise')
						->leftJoin('accounts','accounts.idAccAcc','payments.account')
						->leftJoin('movements','movements.idmovement','payments.idmovement')
						->leftJoin('nomina_employees','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
						->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
						->where('payments.statusConciliation',1)
						->whereNotNull('payments.idmovement')
						->whereIn('payments.idEnterprise',Auth::user()->inChargeEnt(103)->pluck('enterprise_id'))
						->where(function($query) use ($mindate,$maxdate,$enterpriseid,$account,$folio)
						{
							if ($folio != "")
							{
								$query->where('payments.idFolio',$folio);
							}
							if ($enterpriseid != "") 
							{
								$query->where('payments.idEnterprise',$enterpriseid);
							}
							if ($account != "") 
							{
								$query->where('payments.account',$account);
							}
							if ($mindate != "" && $maxdate != "") 
							{
								$query->whereBetween('payments.conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('payments.idpayment','DESC')
						->orderBy('payments.conciliationDate','DESC')
						->get();
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Conciliación de Nómina.xlsx');
			$headers = ['Datos del pago','','','','','Datos del movimiento','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders    = ['Folio de Solicitud','Empresa','Clasificación del gasto','Nombre de Empleado','Importe del Pago','Movimiento','Importe del Movimiento','Fecha de Conciliación'];
			$tempSubHeader = [];
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
					if(in_array($k,['paymentAmount','movementAmount']))
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
}
