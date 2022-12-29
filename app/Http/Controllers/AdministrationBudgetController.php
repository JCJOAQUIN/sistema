<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Excel;
use Ilovepdf\CompressTask;
use Illuminate\Support\Str as Str;
use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Lang;

class AdministrationBudgetController extends Controller
{
	private $module_id = 233;
    public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{	
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function pendingExport(Request $request)
	{
		if(Auth::user()->module->where('id',234)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$enterpriseid = $request->enterpriseid;
			$folio        = $request->folio;
			$kind         = $request->kind;
			$mindate      = $request->mindate!='' ? $request->mindate : null;
			$maxdate      = $request->maxdate!='' ? $request->maxdate : null;
			$requests     = App\RequestModel::selectRaw('
					request_models.folio as folio,
					request_models.idRequisition as requestIdRequisition,
					IF(reviewedEnterprise.name IS NOT NULL,reviewedEnterprise.name,requestEnterprise.name) as enterpriseName,
					request_kinds.kind as kind,
					CONCAT_WS(" ",requestUser.name,requestUser.last_name,requestUser.scnd_last_name) as requestUser,
					status_requests.description as status,
					IF(request_models.authorizeDate IS NOT NULL, DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i"), "") as date,
					IF(reviewedAccount.account IS NOT NULL, CONCAT_WS(" - ",reviewedAccount.account,reviewedAccount.description), 
						IF(requestAccount.account IS NOT NULL,CONCAT_WS(" - ",requestAccount.account,requestAccount.description),
							IF(refundRequestAccount.account IS NOT NULL, CONCAT_WS(" - ",refundRequestAccount.account,refundRequestAccount.description),
								IF(refundReviewedAccount.account IS NOT NULL, CONCAT_WS(" - ",refundReviewedAccount.account,refundReviewedAccount.description),"")
							)
						)
					) as requestAccount,
					IF(request_models.kind = 1, purchases.amount,
						IF(request_models.kind = 9, refunds.total,0)
					) as totalRequest,
					IF(request_models.kind = 1, IF(request_models.taxPayment = 1, "Fiscal","No Fiscal"),
						IF(request_models.kind = 9, "", "")
					) as fiscal,
					IF(request_models.kind = 1, IF(providers.businessName IS NOT NULL, providers.businessName,""),"") as reasonSocial,
					IF(request_models.kind = 1, purchases.reference,
						IF(request_models.kind = 9, refunds.reference, "")
					) as reference,
					IF(request_models.kind = 1, purchases.paymentMode,
						IF(request_models.kind = 9, payment_methods.method, "")
					) as paymentMethod,
					IF(request_models.kind = 1, detail_purchases.quantity,
						IF(request_models.kind = 9, refund_details.quantity, "")
					) as quantity,
					IF(request_models.kind = 1, detail_purchases.unit,
						IF(request_models.kind = 9, refund_details.unit, "")
					) as unit,
					IF(request_models.kind = 1, detail_purchases.description,
						IF(request_models.kind = 9, refund_details.concept, "")
					) as concept,
					IF(request_models.kind = 1, detail_purchases.amount,
						IF(request_models.kind = 9, refund_details.sAmount, "")
					) as conceptAmount,
					IF(request_models.kind = 1, purchases.typeCurrency,
						IF(request_models.kind = 9, refunds.currency, "")
					) as currency,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, providerBank.description,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employeeBank.description, "")
					) as bank,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, provider_banks.account,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.account, "")
					) as account,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, provider_banks.clabe,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.clabe, "")
					) as clabe,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, "",
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.cardNumber, "")
					) as cardNumber
				')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('request_kinds','request_kinds.idrequestkind','request_models.kind')
				->leftJoin('users as requestUser','request_models.idRequest','requestUser.id')
				->leftJoin('enterprises as requestEnterprise','request_models.idEnterprise','requestEnterprise.id')
				->leftJoin('enterprises as reviewedEnterprise','request_models.idEnterpriseR','reviewedEnterprise.id')
				->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
				->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
				->leftJoin('purchases','purchases.idFolio','request_models.folio')
				->leftJoin('detail_purchases','detail_purchases.idPurchase','purchases.idPurchase')
				->leftJoin('providers','providers.idProvider','purchases.idProvider')
				->leftJoin('provider_banks','provider_banks.id','purchases.provider_has_banks_id')
				->leftJoin('banks as providerBank','providerBank.idBanks','provider_banks.banks_idBanks')
				->leftJoin('refunds','refunds.idFolio','request_models.folio')
				->leftJoin('refund_details','refund_details.idRefund','refunds.idRefund')
				->leftJoin('accounts as refundRequestAccount','refundRequestAccount.idAccAcc','refund_details.idAccount')
				->leftJoin('accounts as refundReviewedAccount','refundReviewedAccount.idAccAcc','refund_details.idAccountR')
				->leftJoin('payment_methods','payment_methods.idpaymentMethod','refunds.idpaymentMethod')
				->leftJoin('employees','employees.idEmployee','refunds.idEmployee')
				->leftJoin('banks as employeeBank','employeeBank.idBanks','employees.idBanks')
				->whereIn('request_models.kind',[1,9])
				->where('request_models.remittance',1)
				->whereDoesntHave('budget')
				->whereIn('request_models.status',[2,3,4,5])
				->where('request_models.payment',0)
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(234)->pluck('departament_id'))
								->orWhere('request_models.idDepartment',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(234)->pluck('enterprise_id'))
								->orWhere('request_models.idEnterprise',null);
				})
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid)
				{
					if($enterpriseid != "")
					{
						$query->where('request_models.idEnterpriseR',$enterpriseid);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if($name != "")
					{
						$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio)->orWhere('request_models.new_folio',$folio);
					}
					if($kind != "")
					{
						$query->where('request_models.kind',$kind);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				$alert = "swal('', '".Lang::get("messages.result_not_found")."', 'success');";
				return redirect()->back()->with('alert',$alert);
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Presupuestos-Pendientes.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$subHeader		= ['Folio','Folio de la Requisición','Empresa','Tipo de Solicitud','Solicitante','Estado de Solicitud','Fecha de Autorización','Clasificación del gasto','Total','Fiscal/No Fiscal','Razón Social','Referencia','Método de pago','Cantidad','Unidad','Descripción','Total por concepto','Moneda','Banco','Cuenta','CLABE','No. Tarjeta'];
			$headers		= array_fill(0, count($subHeader), '');
			$headers[0]		= 'Reporte de presupuestos pendientes';
			$tempHeaders    = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio                = null;
					$request->requestIdRequisition = null;
					$request->enterpriseName       = '';
					$request->kind                 = '';
					$request->requestUser          = '';
					$request->status               = '';
					$request->date                 = '';
					$request->requestAccount       = '';
					$request->account              = '';
					$request->totalRequest         = '';
					$request->fiscal               = '';
					$request->reasonSocial         = '';
					$request->reference            = '';
					$request->paymentMethod        = '';
					$request->bank                 = '';
					$request->clabe                = '';
					$request->cardNumber           = '';
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k, ['quantity']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif(in_array($k,['totalRequest','conceptAmount']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r,$currencyFormat);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r,$alignment);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function approvedExport(Request $request)
	{
		if(Auth::user()->module->where('id',235)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$enterpriseid = $request->enterpriseid;
			$folio        = $request->folio;
			$kind         = $request->kind;
			$status       = $request->status;
			$mindate      = $request->mindate!='' ? $request->mindate : null;
			$maxdate      = $request->maxdate!='' ? $request->maxdate : null;
			$requests     = App\RequestModel::selectRaw('
					request_models.folio as folio,
					request_models.idRequisition as requestIdRequisition,
					IF(budgets.status = 1, "Autorizado", "Rechazado") as status_budget,
					IF(reviewedEnterprise.name IS NOT NULL,reviewedEnterprise.name,requestEnterprise.name) as enterpriseName,
					request_kinds.kind as kind,
					CONCAT_WS(" ",requestUser.name,requestUser.last_name,requestUser.scnd_last_name) as requestUser,
					status_requests.description as status,
					IF(request_models.authorizeDate IS NOT NULL, DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i"), "") as date,
					IF(reviewedAccount.account IS NOT NULL, CONCAT_WS(" - ",reviewedAccount.account,reviewedAccount.description), 
						IF(requestAccount.account IS NOT NULL,CONCAT_WS(" - ",requestAccount.account,requestAccount.description),
							IF(refundRequestAccount.account IS NOT NULL, CONCAT_WS(" - ",refundRequestAccount.account,refundRequestAccount.description),
								IF(refundReviewedAccount.account IS NOT NULL, CONCAT_WS(" - ",refundReviewedAccount.account,refundReviewedAccount.description),"")
							)
						)
					) as requestAccount,
					IF(request_models.kind = 1, purchases.amount,
						IF(request_models.kind = 9, refunds.total,0)
					) as totalRequest,
					IF(request_models.kind = 1, IF(request_models.taxPayment = 1, "Fiscal","No Fiscal"),
						IF(request_models.kind = 9, "", "")
					) as fiscal,
					IF(request_models.kind = 1, IF(providers.businessName IS NOT NULL, providers.businessName,""),"") as reasonSocial,
					IF(request_models.kind = 1, purchases.reference,
						IF(request_models.kind = 9, refunds.reference, "")
					) as reference,
					IF(request_models.kind = 1, purchases.paymentMode,
						IF(request_models.kind = 9, payment_methods.method, "")
					) as paymentMethod,
					IF(request_models.kind = 1, detail_purchases.quantity,
						IF(request_models.kind = 9, refund_details.quantity, "")
					) as quantity,
					IF(request_models.kind = 1, detail_purchases.unit,
						IF(request_models.kind = 9, refund_details.unit, "")
					) as unit,
					IF(request_models.kind = 1, detail_purchases.description,
						IF(request_models.kind = 9, refund_details.concept, "")
					) as concept,
					IF(request_models.kind = 1, detail_purchases.amount,
						IF(request_models.kind = 9, refund_details.sAmount, "")
					) as conceptAmount,
					IF(request_models.kind = 1, purchases.typeCurrency,
						IF(request_models.kind = 9, refunds.currency, "")
					) as currency,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, providerBank.description,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employeeBank.description, "")
					) as bank,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, provider_banks.account,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.account, "")
					) as account,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, provider_banks.clabe,
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.clabe, "")
					) as clabe,
					IF(request_models.kind = 1 AND purchases.provider_has_banks_id IS NOT NULL, "",
						IF(request_models.kind = 9 AND refunds.idEmployee IS NOT NULL, employees.cardNumber, "")
					) as cardNumber
				')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('request_kinds','request_kinds.idrequestkind','request_models.kind')
				->leftJoin('budgets','budgets.request_id','request_models.folio')
				->leftJoin('users as requestUser','request_models.idRequest','requestUser.id')
				->leftJoin('enterprises as requestEnterprise','request_models.idEnterprise','requestEnterprise.id')
				->leftJoin('enterprises as reviewedEnterprise','request_models.idEnterpriseR','reviewedEnterprise.id')
				->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
				->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
				->leftJoin('purchases','purchases.idFolio','request_models.folio')
				->leftJoin('detail_purchases','detail_purchases.idPurchase','purchases.idPurchase')
				->leftJoin('providers','providers.idProvider','purchases.idProvider')
				->leftJoin('provider_banks','provider_banks.id','purchases.provider_has_banks_id')
				->leftJoin('banks as providerBank','providerBank.idBanks','provider_banks.banks_idBanks')
				->leftJoin('refunds','refunds.idFolio','request_models.folio')
				->leftJoin('refund_details','refund_details.idRefund','refunds.idRefund')
				->leftJoin('accounts as refundRequestAccount','refundRequestAccount.idAccAcc','refund_details.idAccount')
				->leftJoin('accounts as refundReviewedAccount','refundReviewedAccount.idAccAcc','refund_details.idAccountR')
				->leftJoin('payment_methods','payment_methods.idpaymentMethod','refunds.idpaymentMethod')
				->leftJoin('employees','employees.idEmployee','refunds.idEmployee')
				->leftJoin('banks as employeeBank','employeeBank.idBanks','employees.idBanks')
				->whereIn('request_models.kind',[1,9])
				->where('request_models.remittance',1)
				->whereHas('budget')
				->whereIn('request_models.status',[2,3,4,5,10,11,12])
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(235)->pluck('departament_id'))
								->orWhere('request_models.idDepartment',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(235)->pluck('enterprise_id'))
								->orWhere('request_models.idEnterprise',null);
				})
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid, $status)
				{
					if($enterpriseid != "")
					{
						$query->where('request_models.idEnterpriseR',$enterpriseid);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if($name != "")
					{
						$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio)->orWhere('request_models.new_folio',$folio);
					}
					if($kind != "")
					{
						$query->where('request_models.kind',$kind);
					}
					if($status != "")
					{
						$query->whereHas('budget',function($q) use ($status)
						{
							$q->where('status','LIKE','%'.$status.'%');
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('request_models.folio','DESC')
				->get();

			if(count($requests)==0 || $requests==null)
			{
				$alert = "swal('', '".Lang::get("messages.result_not_found")."', 'success');";
				return redirect()->back()->with('alert',$alert);
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Presupuestos-Pendientes.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$subHeader		= ['Folio','Folio de la Requisición','Estado de Presupuesto','Empresa','Tipo de Solicitud','Solicitante','Estado de Solicitud','Fecha de Autorización','Clasificación del gasto','Total','Fiscal/No Fiscal','Razón Social','Referencia','Método de pago','Cantidad','Unidad','Descripción','Total por concepto','Moneda','Banco','Cuenta','CLABE','No. Tarjeta'];
			$headers		= array_fill(0, count($subHeader), '');
			$headers[0]		= 'Reporte de presupuestos pendientes';
			$tempHeaders    = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio						= null;
					$request->requestIdRequisition		= null;
					$request->enterpriseName			= '';
					$request->status_budget 			= '';
					$request->kind						= '';
					$request->requestUser				= '';
					$request->status					= '';
					$request->date						= '';
					$request->requestAccount			= '';
					$request->account					= '';
					$request->totalRequest				= '';
					$request->fiscal					= '';
					$request->reasonSocial				= '';
					$request->reference					= '';
					$request->paymentMethod				= '';
					$request->bank						= '';
					$request->clabe						= '';
					$request->cardNumber				= '';
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k, ['quantity']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif(in_array($k,['totalRequest','conceptAmount']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r,$currencyFormat);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r,$alignment);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}


	public function pending(Request $request)
	{
		if(Auth::user()->module->where('id',234)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$enterpriseid = $request->enterpriseid;
			$folio        = $request->folio;
			$kind         = $request->kind;
			$mindate      = $request->mindate!='' ? $request->mindate : null;
			$maxdate      = $request->maxdate!='' ? $request->maxdate : null;
			$requests     = App\RequestModel::whereIn('kind',[1,9])
				->where('remittance',1)
				->whereDoesntHave('budget')
				->whereIn('status',[2,3,4,5])
				->where('payment',0)
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(234)->pluck('departament_id'))
								->orWhere('idDepartment',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(234)->pluck('enterprise_id'))
								->orWhere('idEnterprise',null);
				})
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid)
				{
					if($enterpriseid != "")
					{
						$query->where('request_models.idEnterpriseR',$enterpriseid);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if($name != "")
					{
						$query->whereHas('requestUser',function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio)->orWhere('request_models.new_folio',$folio);
					}
					if($kind != "")
					{
						$query->where('request_models.kind',$kind);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('authorizeDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('folio','DESC')
				->paginate(10);			
			return response(
				view('administracion.asignacion_presupuestos.pendientes',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 234,
						'requests'     => $requests,
						'account'      => $account,
						'name'         => $name,
						'folio'        => $folio,
						'kind'         => $kind,
						'mindate'      => $mindate,
						'maxdate'      => $maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(234), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',234)->count()>0)
		{
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::whereIn('kind',[1,9])
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(234)->pluck('departament_id'))
								->orWhere('idDepartment',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(234)->pluck('enterprise_id'))
								->orWhere('idEnterprise',null);
				})
				->whereIn('status',[2,3,4,5])
				->where('remittance',1)
				->where('payment',0)
				->whereDoesntHave('budget')
				->find($id);
			if ($request != "") 
			{
				return view('administracion.asignacion_presupuestos.revision',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 234,
						'request'   => $request
					]
				);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function storeBudget(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$statusBudget = App\Budget::where('request_id',$request->idfolio)->get();
			if(count($statusBudget) > 0)
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'success');";
			}
			else
			{
				$budget				= new App\Budget;
				$budget->request_id	= $request->idfolio;
				$budget->user_id	= Auth::user()->id;
				$budget->status		= $request->status;
				$budget->comment	= $request->budgetComment;
				$budget->save();
				$status				= 'aceptado';
				if($budget->status == 0)
				{
					$status	= 'rechazado';
				}
				$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
			}
			return redirect()->route('budget.pending')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveBudget(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			foreach ($request->budget as $budgetRequest)
			{
				$budget				= new App\Budget;
				$budget->request_id	= $budgetRequest;
				$budget->user_id	= Auth::user()->id;
				$budget->status		= $request->status;
				$budget->comment	= $request->comment;
				$budget->save();
			}
			$status				= 'aceptados';
			if($budget->status == 0)
			{
				$status	= 'rechazados';
			}
			$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
			return redirect()->route('budget.pending')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function editBudget(Request $request)
	{
		if(Auth::user()->module->where('id',235)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$enterpriseid	= $request->enterpriseid;
			$folio			= $request->folio;
			$kind			= $request->kind;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;

			$requests 	= App\RequestModel::whereIn('kind',[1,9])
						->where('remittance',1)
						->whereHas('budget')
						->whereIn('status',[2,3,4,5,10,11,12])
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(235)->pluck('departament_id'))
										->orWhere('idDepartment',null);
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(235)->pluck('enterprise_id'))
										->orWhere('idEnterprise',null);
						})
						->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid, $status)
						{
							if($enterpriseid != "")
							{
								$query->where(function($q) use ($enterpriseid)
								{
									$q->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
								});
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use($name)
								{
									$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
								});
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio)->orWhere('request_models.new_folio',$folio);
							}
							if($kind != "")
							{
								$query->where('request_models.kind',$kind);
							}
							if($status != "")
							{
								$query->whereHas('budget',function($q) use ($status)
								{
									$q->where('status','LIKE','%'.$status.'%');
								});
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('authorizeDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('folio','DESC')
						->paginate(10);
			
			return response(
				view('administracion.asignacion_presupuestos.editarver',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 235,
						'requests'		=> $requests,
						'account'		=> $account,
						'name'			=> $name,
						'folio'			=> $folio,
						'kind'			=> $kind,
						'status'		=> $status,
						'mindate'		=> $mindate,
						'maxdate'		=> $maxdate,
						'enterpriseid'	=> $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(235), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showBudget($id)
	{
		if(Auth::user()->module->where('id',235)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$request = App\RequestModel::whereIn('request_models.kind',[1,9])
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(235)->pluck('departament_id'))
						->orWhere('idDepartment',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(235)->pluck('enterprise_id'))
						->orWhere('idEnterprise',null);
				})
				->whereIn('status',[2,3,4,5,10,11,12])
				->where('remittance',1)
				->whereHas('budget')
				->find($id);
			if ($request != "") 
			{
				return view('administracion.asignacion_presupuestos.editar_presupuesto',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id' 	=> $this->module_id,
						'option_id'	=> 235,
						'request' 	=> $request,
						'folio' 	=> $id
					]
				);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateBudget(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$requestMod 	= App\RequestModel::whereIn('request_models.kind',[1,9])
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(235)->pluck('departament_id'))
										->orWhere('idDepartment',null);
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(235)->pluck('enterprise_id'))
										->orWhere('idEnterprise',null);
						})
						->whereIn('request_models.status',[2,3,4,5])
						->where('remittance',1)
						->findOrFail($id);
			$oldBudget			= $requestMod->budget;
			$oldBudget->delete();
			$budget				= new App\Budget;
			$budget->request_id	= $id;
			$budget->user_id	= Auth::user()->id;
			$budget->status		= $request->status;
			$budget->comment	= $request->budgetComment;
			$budget->save();
			$status				= 'aceptado';
			if($budget->status == 0)
			{
				$status	= 'rechazado';
			}
			$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
			return redirect()->route('budget.edit')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function viewReview($id)
	{
		if(Auth::user()->module->where('id',235)->count()>0)
		{
			$data 		= App\Module::find($this->module_id);
			$request 	= App\RequestModel::whereIn('request_models.kind',[1,9])
							->where(function($permissionDep)
							{
								$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(235)->pluck('departament_id'))
											->orWhere('idDepartment',null);
							})
							->where(function($permissionEnt)
							{
								$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(235)->pluck('enterprise_id'))
											->orWhere('idEnterprise',null);
							})
							->whereIn('request_models.status',[2,3,4,5,10,11,12])
							->where('remittance',1)
							->findOrFail($id);
			return view('administracion.asignacion_presupuestos.ver_requisicion',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 235,
					'request'	=> $request,
					'folio'		=> $id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function paymentDelete(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$payment 	= App\Payment::find($id);

			$folio 		= $payment->idFolio;
			$kind 		= $payment->idKind;

			if ($payment->statusConciliation == 1) 
			{
				$alert = "swal('','El pago no se puede eliminar debido a que ya fue conciliado.','error');";
			}
			else
			{	
				$docs = App\DocumentsPayments::where('idpayment',$id)->delete();
				$payment->delete();

				$req            = App\RequestModel::find($folio);
				$resta          = 0;
				$totalPagado    = 0;

				switch ($kind) 
				{
					// purchase request
					case 1:
						$total = $req->purchases->first()->amount;

						$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;

						$resta = $total-$totalPagado;
						if($resta == 0)
						{
							$payUpdate			= App\RequestModel::find($folio);
							$payUpdate->status	= 10;
							$payUpdate->payment	= 1;
							$payUpdate->save();
						}
						else
						{
							$payUpdate			= App\RequestModel::find($folio);
							$payUpdate->status	= 12;
							$payUpdate->payment	= 0;
							$payUpdate->save();
						}
						break;

					case 9:
						$total 			= $req->refunds->first()->total;
						$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
						$resta = $total-$totalPagado;
						if($resta == 0)
						{
							$payUpdate			= App\RequestModel::find($folio);
							$payUpdate->status	= 10;
							$payUpdate->payment	= 1;
							$payUpdate->save();
						}
						else
						{
							$payUpdate			= App\RequestModel::find($folio);
							$payUpdate->status	= 12;
							$payUpdate->payment	= 0;
							$payUpdate->save();
						}
						break;
						
					default:
						break;
				}


				$alert = "swal('','Pago Eliminado Exitosamente','success');";
			}

			return redirect('administration/remittance/edit')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
}
