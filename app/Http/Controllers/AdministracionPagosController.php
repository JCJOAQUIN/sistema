<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Lang;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Excel;
use Ilovepdf\CompressTask;
use Illuminate\Support\Str as Str;
use App\Functions\Files;
use PDF;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionPagosController extends Controller
{
	private $module_id = 89;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{	
			/*
			$sumtotal = App\Payment::sum('amount');
			$sumsubtotal = App\Payment::sum('subtotal');
			$sumiva = App\Payment::sum('iva');
			return $sumtotal;
			//479833129.35
			$payments = App\Payment::all();
			$folios=[];
			$count = 0;
			foreach ($payments as $payment) 
			{
				if (round($payment->iva + $payment->subtotal,2) > round($payment->amount,2)) 
				{
					$folios[$count]['id'] = $payment->idpayment;
					$folios[$count]['iva+subtotal'] = round($payment->iva + $payment->subtotal,2);
					$folios[$count]['amount'] = round($payment->amount,2);
					$count++;
				}
			}
			return $folios;
			*/
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
			return abort(404);
		}
	}
	
	public function getAccount(Request $request)
	{
		if($request->ajax())
		{
			$output = "";
			$accounts = App\Account::orderNumber()->where('idEnterprise',$request->enterpriseid)
				->where('selectable',1)
				->get();
			if (count($accounts) > 0) 
			{
				return Response($accounts);
			}
		}
	}

	private function pendingQuery(Request $request)
	{
		$account      = $request->account;
		$name         = $request->name;
		$enterpriseid = $request->enterpriseid;
		$folio        = $request->folio;
		$kind         = $request->kind;
		$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
		$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		$type_nomina  = $request->type_nomina;
		$requests     = App\RequestModel::whereIn('kind',[1,2,3,5,8,9,11,12,13,14,15,16])
			->whereIn('status',[5,12,18])
			->where('payment',0)
			->whereDoesntHave('nominasReal',function($q)
			{
				$q->where('type_nomina',3);
			})
			->whereDoesntHave('groups', function($q)
			{
				$q->where('operationType','=','Entrada');
			})
			->whereDoesntHave('expenses', function($q)
			{
				$q->where(function($q)
				{
					$q->where('reintegro','=',0)->orWhereNull('reintegro');
				})
				->where(function($q)
				{
					$q->where('reembolso','=',0)->orWhereNull('reembolso');
				});
			})
			->where(function($q)
			{
				$q->where('remittance',0)
					->orWhere(function($q)
					{
						$q->where('remittance',1)
						->whereHas('budget',function($q)
						{
							$q->where('status',1);
						});
					});
			})
			->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartamentR',Auth::user()->inChargeDep(90)->pluck('departament_id'))
								->orWhere('idDepartamentR',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(90)->pluck('enterprise_id'))
								->orWhere('idEnterpriseR',null);
				})
			->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid,$type_nomina)
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
					$query->where('request_models.folio',$folio);
				}
				if($kind != "")
				{
					$query->where('request_models.kind',$kind);
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
				}
				if(isset($type_nomina) && count($type_nomina)>0)
				{
					$query->whereHas('nominasReal',function($q) use($type_nomina)
					{
						$q->whereIn('nominas.type_nomina',$type_nomina)
							->whereIn('request_models.status',[5,12,18]);
					});
				}
			})
			->orderBy('authorizeDate','DESC')
			->orderBy('folio','DESC');
		return $requests;
	}

	public function pendingExport(Request $request)
	{
		if(Auth::user()->module->where('id',90)->count()>0)
		{
			$account		= $request->account;
			$name			= $request->name;
			$enterpriseid	= $request->enterpriseid;
			$folio			= $request->folio;
			$kind			= $request->kind;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$type_nomina	= $request->type_nomina;
			$requests 	= App\RequestModel::selectRaw('
						request_models.folio as folio,
						request_models.idRequisition as idRequisition,
						IFNULL(reviewedEnterprise.name,requestEnterprise.name) as enterpriseName,
						request_kinds.kind as kind,
						status_requests.description as status,
						CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
						DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as date,
						IFNULL(IF(reviewedAccount.account IS NULL, CONCAT_WS(" - ",requestAccount.account,requestAccount.description),CONCAT_WS(" - ",reviewedAccount.account,reviewedAccount.description)),"Varias") as accountName,
						IF(request_models.kind = 1, (purchases.amount - IFNULL(payments.amount_paid,0)),
							IF(request_models.kind = 3, IF(expenses.reembolso > 0, (expenses.reembolso - IFNULL(payments.amount_paid,0)), IF(expenses.reintegro > 0, (expenses.reintegro - IFNULL(payments.amount_paid,0)), 0)),
								IF(request_models.kind = 5, (loans.amount - IFNULL(payments.amount_paid,0)),
									IF(request_models.kind = 8, (resources.total - IFNULL(payments.amount_paid,0)),
										IF(request_models.kind = 9, (refunds.total - IFNULL(payments.amount_paid,0)),
											IF(request_models.kind = 12, (loan_enterprises.amount - IFNULL(payments.amount_paid,0)),
												IF(request_models.kind = 13, (purchase_enterprises.amount - IFNULL(payments.amount_paid,0)),
													IF(request_models.kind = 14, (groups.amount - IFNULL(payments.amount_paid,0)), 
														IF(request_models.kind = 15, (movements_enterprises.amount - IFNULL(payments.amount_paid,0)),
															IF(request_models.kind = 16, (nominas.amount - IFNULL(payments.amount_paid,0)), 
															0)
														)
													)
												)
											)
										)
									)
								)
							)
						) as pendingAmount,
						IF(request_models.taxPayment IS NOT NULL,IF(request_models.taxPayment = 1, "Fiscal", "No Fiscal"),"No especificado") as taxPayment,
						IF(request_models.kind = 1, providers.businessName, "No Aplica") as businessName,
						IF(provider_classifications.classification IS NOT NULL,IF(provider_classifications.classification = 1, "Validado","No validado"),"No Aplica") as providerClassification,
						IF(request_models.kind = 1,purchases.reference,"No Aplica") as reference,
						IF(request_models.kind = 1, purchases.paymentMode,
							IF(request_models.kind = 3, expensesPaymentMethod.method,
								IF(request_models.kind = 5, loansPaymentMethod.method,
									IF(request_models.kind = 8, resourcesPaymentMethod.method,
										IF(request_models.kind = 9, refundsPaymentMethod.method, "No hay")
									)
								)
							)
						) as paymentMethod,
						IF(request_models.kind = 1, purchasesBank.description,
							IF(request_models.kind = 3, expensesBank.description,
								IF(request_models.kind = 5, loansBank.description,
									IF(request_models.kind = 8, resourcesBank.description,
										IF(request_models.kind = 9, refundsBank.description, "No hay")
									)
								)
							)
						) as bank,
						IF(request_models.kind = 1, purchasesAccount.account,
							IF(request_models.kind = 3, expensesAccount.account,
								IF(request_models.kind = 5, loansAccount.account,
									IF(request_models.kind = 8, resourcesAccount.account,
										IF(request_models.kind = 9, refundsAccount.account, "No hay")
									)
								)
							)
						) as account,
						IF(request_models.kind = 1, purchasesAccount.clabe,
							IF(request_models.kind = 3, expensesAccount.clabe,
								IF(request_models.kind = 5, loansAccount.clabe,
									IF(request_models.kind = 8, resourcesAccount.clabe,
										IF(request_models.kind = 9, refundsAccount.clabe, "No hay")
									)
								)
							)
						) as clabe,
						IF(request_models.kind = 1, "No hay",
							IF(request_models.kind = 3, expensesAccount.cardNumber,
								IF(request_models.kind = 5, loansAccount.cardNumber,
									IF(request_models.kind = 8, resourcesAccount.cardNumber,
										IF(request_models.kind = 9, refundsAccount.cardNumber, "No hay")
									)
								)
							)
						) as cardNumber,
						"" as num_partial,
						IF(partial_payments.tipe IS NOT NULL, IF(partial_payments.tipe = 1, partial_payments.payment,((partial_payments.payment * purchases.amount)/ 100)), "No Aplica") as typePartial,
						IF(partial_payments.date_requested IS NOT NULL, DATE_FORMAT(partial_payments.date_requested, "%d-%m-%Y"), "No Aplica") as datePartial,
						IF(partial_payments.date_requested IS NOT NULL, IF(partial_payments.date_delivery IS NOT NULL, "Pagada","Por pagar"), "No Aplica") as statusPartial
					')
					->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
					->leftJoin('request_kinds','request_kinds.idrequestkind','request_models.kind')
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
					->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
					->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
					->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
					->leftJoin('purchases','purchases.idFolio','request_models.folio')
					->leftJoin('providers','providers.idProvider','purchases.idProvider')
					->leftJoin('provider_datas','provider_datas.id','providers.provider_data_id')
					->leftJoin('provider_classifications','provider_classifications.provider_data_id','provider_datas.id')
					->leftJoin('partial_payments','partial_payments.purchase_id','purchases.idPurchase')
					->leftJoin('provider_banks as purchasesAccount','purchasesAccount.id','purchases.provider_has_banks_id')
					->leftJoin('banks as purchasesBank','purchasesBank.idBanks','purchasesAccount.banks_idBanks')
					->leftJoin('nominas','nominas.idFolio','request_models.folio')
					->leftJoin('expenses','expenses.idFolio','request_models.folio')
					->leftJoin('employees as expensesAccount','expensesAccount.idEmployee','expenses.idEmployee')
					->leftJoin('banks as expensesBank','expensesBank.idBanks','expensesAccount.idBanks')
					->leftJoin('loans','loans.idFolio','request_models.folio')
					->leftJoin('employees as loansAccount','loansAccount.idEmployee','loans.idEmployee')
					->leftJoin('banks as loansBank','loansBank.idBanks','loansAccount.idBanks')
					->leftJoin('resources','resources.idFolio','request_models.folio')
					->leftJoin('employees as resourcesAccount','resourcesAccount.idEmployee','resources.idEmployee')
					->leftJoin('banks as resourcesBank','resourcesBank.idBanks','resourcesAccount.idBanks')
					->leftJoin('refunds','refunds.idFolio','request_models.folio')
					->leftJoin('employees as refundsAccount','refundsAccount.idEmployee','refunds.idEmployee')
					->leftJoin('banks as refundsBank','refundsBank.idBanks','refundsAccount.idBanks')
					->leftJoin('groups','groups.idFolio','request_models.folio')
					->leftJoin('movements_enterprises','movements_enterprises.idFolio','request_models.folio')
					->leftJoin('loan_enterprises','loan_enterprises.idFolio','request_models.folio')
					->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
					->leftJoin('budgets','budgets.request_id','request_models.folio')
					->leftJoin(DB::raw('(SELECT idFolio, SUM(amount_real) as amount_paid FROM payments GROUP BY idFolio) as payments'),'payments.idFolio','request_models.folio')
					->leftJoin('payment_methods as expensesPaymentMethod','expensesPaymentMethod.idpaymentMethod','expenses.idpaymentMethod')
					->leftJoin('payment_methods as loansPaymentMethod','loansPaymentMethod.idpaymentMethod','loans.idpaymentMethod')
					->leftJoin('payment_methods as resourcesPaymentMethod','resourcesPaymentMethod.idpaymentMethod','resources.idpaymentMethod')
					->leftJoin('payment_methods as refundsPaymentMethod','refundsPaymentMethod.idpaymentMethod','refunds.idpaymentMethod')
					->whereIn('request_models.kind',[1,2,3,5,8,9,11,12,13,14,15,16])
					->whereIn('request_models.status',[5,12,18])
					->where('request_models.payment',0)
					->where(function($q)
					{
						$q->where('groups.operationType','=','Salida')->orWhereNull('groups.operationType');
					})
					->where(function($q)
					{
						$q->where('nominas.type_nomina','!=',3)->orWhereNull('nominas.type_nomina');
					})
					->where(function($q)
					{
						$q->where(function($q)
						{
							$q->where('expenses.reintegro','>',0)->orWhere('expenses.reembolso','>',0)->orWhereNull('expenses.title');
						});
					})
					->where(function($q)
					{
						$q->where('request_models.remittance',0)
						->orWhere(function($q)
						{
							$q->where('request_models.remittance',1)->where('budgets.status',1);
						})
						->orWhereNull('request_models.remittance');
					})
					->where(function($permissionDep)
					{
						$permissionDep->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(90)->pluck('departament_id'))
									->orWhere('request_models.idDepartamentR',null);
					})
					->where(function($permissionEnt)
					{
						$permissionEnt->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(90)->pluck('enterprise_id'))
									->orWhere('request_models.idEnterpriseR',null);
					})

					->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $kind, $enterpriseid,$type_nomina)
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
							$query->whereRaw('CONCAT_WS(" ",requestUser.name,requestUser.last_name,requestUser.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
						}
						if($folio != "")
						{
							$query->where('request_models.folio',$folio);
						}
						if($kind != "")
						{
							$query->where('request_models.kind',$kind);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('request_models.authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59').'']);
						}
						if(isset($type_nomina) && count($type_nomina)>0)
						{
							$query->whereIn('nominas.type_nomina',$type_nomina)
								->whereIn('request_models.status',[5,12,18]);
						}
					})
					->orderBy('request_models.authorizeDate','DESC')
					->orderBy('request_models.folio','DESC')
					->get();

			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Pagos-Pendientes.xlsx');
			$headers = ['Reporte de Pagos Pendientes','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Folio de Requisicion','Empresa','Tipo de Solicitud','Estado','Solicitante','Fecha de Autorización','Clasificación del gasto','Importe','Fiscal/No Fiscal','Razón Social','Validación de Proveedor','Referencia','Método de pago','Banco','Cuenta','CLABE','No. Tarjeta','Parcialidad','Monto','Fecha estimada para pago','Estado de parcialidad'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			$num_partial   = 1;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$num_partial = 1;
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;

					
					if ($request->typePartial == "No Aplica") 
					{
						$request->num_partial = '';
						$num_partial = 1;
					}
					else
					{
						$request->num_partial = $num_partial;
						$num_partial++;
					}
				}
				else
				{
					$request->folio						= null;
					$request->idRequisition				= '';
					$request->enterpriseName			= '';
					$request->accountName 				= '';
					$request->kind						= '';
					$request->status					= '';
					$request->requestUser				= '';
					$request->date						= '';
					$request->pendingAmount				= '';
					$request->taxPayment				= '';
					$request->businessName				= '';
					$request->providerClassification	= '';
					$request->reference					= '';
					$request->paymentMethod				= '';
					$request->bank						= '';
					$request->account					= '';
					$request->clabe						= '';
					$request->cardNumber				= '';

					if ($request->typePartial != "No Aplica") 
					{
						$request->num_partial = $num_partial;
						$num_partial++;
					}
					else
					{
						$request->num_partial = '';
						$num_partial = 1;
					}
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k,['pendingAmount','typePartial']))
					{
						if($r != '' && $r != 'No Aplica')
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
		if(Auth::user()->module->where('id',90)->count()>0)
		{
			$data     = App\Module::find($this->module_id);
			$requests = self::pendingQuery($request)->paginate(10);
			return response(
				view('administracion.pagos.pendientes',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 90,
						'requests'     => $requests,
						'account'      => $request->account,
						'name'         => $request->name,
						'folio'        => $request->folio,
						'kind'         => $request->kind,
						'mindate'      => $request->mindate!='' ? $request->mindate : null,
						'maxdate'      => $request->maxdate!='' ? $request->maxdate : null,
						'enterpriseid' => $request->enterpriseid,
						'type_nomina'  => $request->type_nomina
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(90), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',90)->count()>0)
		{
			$data 		= App\Module::find($this->module_id);
			$request 	= App\RequestModel::whereIn('kind',[1,2,3,5,8,9,11,12,13,14,15,16])
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartamentR',Auth::user()->inChargeDep(90)->pluck('departament_id'))
						->orWhere('idDepartamentR',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(90)->pluck('enterprise_id'))
						->orWhere('idEnterpriseR',null);
				})
				->whereIn('status',[5,12,18])
				->where('payment',0)
				->where(function($q)
				{
					$q->where('remittance',0)
						->orWhere(function($q)
						{
							$q->where('remittance',1)
							->whereHas('budget',function($q)
							{
								$q->where('status',1);
							});
						});
				})
				->find($id);
			if($request != "") 
			{
				$payment	=App\Payment::where('idFolio',$request->folio)->get();
				$tax		=0;
				$retention	=0;
				$amount		=0;
				$iva		=0;
				foreach($payment as $item)
				{
					$tax		+= $item->tax_real;
					$retention	+= $item->retention_real;
					$amount		+= $item->amount_real;
					$iva		+= $item->iva_real;
				}
				if ($request->kind == 16) 
				{
					return view('administracion.pagos.nomina',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 90,
							'request'	=> $request,
							'tax'		=> $tax,
							'retention'	=> $retention,
							'amount'	=> $amount,
							'iva'		=> $iva,
						]
					);
					/*

					if ($request->nominasReal->first()->type_nomina == 3) 
					{
						return view('administracion.pagos.nomina_nom35',
						[
							'id'		=>$data['father'],
							'title'		=>$data['name'],
							'details'	=>$data['details'],
							'child_id'	=>$this->module_id,
							'option_id'	=>90,
							'request'	=>$request
						]);
					}
					else
					{
						return view('administracion.pagos.nomina',
						[
							'id'		=>$data['father'],
							'title'		=>$data['name'],
							'details'	=>$data['details'],
							'child_id'	=>$this->module_id,
							'option_id'	=>90,
							'request'	=>$request
						]);
					}
					*/
				}
				else
				{
					return view('administracion.pagos.revision',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 90,
							'request'	=> $request,
							'tax'		=> $tax,
							'retention'	=> $retention,
							'amount'	=> $amount,
							'iva'		=> $iva,
						]
					);
				}
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

	public function viewReview($id)
	{
		if(Auth::user()->module->where('id',91)->count()>0)
		{
			$data 		= App\Module::find($this->module_id);
			$payment 	= App\Payment::find($id);
			$request 	= App\RequestModel::whereIn('request_models.kind',[1,2,3,5,8,9,11,12,13,14,15,16,17])
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartamentR',Auth::user()->inChargeDep(91)->pluck('departament_id'))
								->orWhere('idDepartamentR',null);
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(91)->pluck('enterprise_id'))
								->orWhere('idEnterpriseR',null);
				})
				->whereIn('request_models.status',[5,10,11,12,18])
				->where(function($q)
				{
					$q->where('remittance',0)
						->orWhere(function($q)
						{
							$q->where('remittance',1)
							->whereHas('budget',function($q)
							{
								$q->where('status',1);
							});
						});
				})
				->find($payment->idFolio);
			if ($request != "") 
			{
				return view('administracion.pagos.verpago',
					[
						'id'		=>$data['father'],
						'title'		=>$data['name'],
						'details'	=>$data['details'],
						'child_id'	=>$this->module_id,
						'option_id'	=>91,
						'request'	=>$request,
						'payment'	=>$payment
					]);
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

	public function storePaymentNomina(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$idnomina			= App\NominaEmployee::find($request->idnominaEmployee)->idnomina;
			$typePayroll		= App\Nomina::find($idnomina)->idCatTypePayroll;
			$totalPaidEmployee	= App\Payment::where('idFolio',$request->idfolio)->where('idnominaEmployee',$request->idnominaEmployee)->where('type',1)->sum('amount');
			$nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee);
			if (App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 1) 
			{
				switch ($typePayroll) 
				{
					case '001':
						$totalPayment	= round($nominaemployee->salary->first()->netIncome+$nominaemployee->salary->first()->alimony,2);
						break;
					case '002':
						$totalPayment	= round($nominaemployee->bonus->first()->netIncome+$nominaemployee->bonus->first()->alimony,2);
						break;
					case '003':
					case '004':
						$totalPayment	= round($nominaemployee->liquidation->first()->netIncome+$nominaemployee->liquidation->first()->alimony,2);
						break;
					case '005':
						$totalPayment	= round($nominaemployee->vacationPremium->first()->netIncome+$nominaemployee->vacationPremium->first()->alimony,2);
						break;
					case '006':
						$totalPayment	= round($nominaemployee->profitSharing->first()->netIncome+$nominaemployee->profitSharing->first()->alimony,2);
						break;
				}
			}
			else
			{
				$totalPayment	= round($nominaemployee->nominasEmployeeNF->first()->amount,2);
			}
			if (round($request->amount,2) > round($request->amountRes,2))
			{
				$alert = "swal('', 'El pago no se pudo realizar, debido a que el importe es mayor a lo que se adeuda.', 'error');";
				return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
			}
			elseif (round($request->amount+$totalPaidEmployee,2)>round($totalPayment,2))
			{
				$alert = "swal('', 'El pago no se pudo realizar, debido a que el importe es mayor a lo que se le adeuda al empleado.', 'error');";
				return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
			}
			else
			{
				$t_request       = App\RequestModel::find($request->idfolio);
				$t_request->code = $request->code;
				$t_request->save();
				if ($request->exchange_rate != "") 
				{
					$exchange_rate = $request->exchange_rate;
				}
				else
				{
					$exchange_rate = 1;
				}
				$t_payment                   = new App\Payment();
				$t_payment->amount           = $request->amount * $exchange_rate;
				$t_payment->subtotal         = $request->subtotalRes * $exchange_rate;
				$t_payment->tax              = $request->taxRes * $exchange_rate;
				$t_payment->retention        = $request->retentionRes * $exchange_rate;
				$t_payment->iva              = $request->ivaRes * $exchange_rate;
				$t_payment->amount_real      = $request->amount;
				$t_payment->tax_real         = $request->taxRes;
				$t_payment->retention_real   = $request->retentionRes;
				$t_payment->subtotal_real    = $request->subtotalRes;
				$t_payment->iva_real         = $request->ivaRes;
				$t_payment->account          = $request->account;
				$date                        = \DateTime::createFromFormat('d-m-Y', $request->paymentDate);
				$newdate                     = $date->format('Y-m-d');
				$t_payment->paymentDate      = $newdate;
				$t_payment->elaborateDate    = Carbon::now();
				$t_payment->idFolio          = $request->idfolio;
				$t_payment->idKind           = $request->idkind;
				$t_payment->idRequest        = Auth::user()->id;
				$t_payment->idEnterprise     = $request->enterprise_id;
				$t_payment->commentaries     = $request->commentaries;
				$t_payment->idnominaEmployee = $request->idnominaEmployee;
				$t_payment->exchange_rate    = $exchange_rate;
				$t_payment->exchange_rate_description = $request->exchange_rate_description;
				if ($request->beneficiary_pay != '' && $request->type_payment == 2) 
				{
					$t_payment->beneficiary	= $request->beneficiary_pay;
					$t_payment->type = 2;
				}
				else
				{
					$t_payment->type = 1;
				}
				$t_payment->save();
				$idpayment = $t_payment->idpayment;

				if($request->realPath != "")
				{
					for ($i = 0; $i < count($request->realPath); $i++)
					{
						$documents            = new App\DocumentsPayments();
						$new_file_name        = Files::rename($request->realPath[$i],$request->idfolio);
						$documents->path      = $new_file_name;
						$documents->idpayment = $idpayment;
						$documents->save();
					}
				}

				if(App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 0 && round($request->amount+$totalPaidEmployee,2) == round($totalPayment,2))
				{
					$receiptName = App\Nomina::find($idnomina)->idFolio.'_nf_'.Str::uuid();
					$pdf         = PDF::loadView('administracion.nomina.receipts.payroll_nf',['payment' => $t_payment]);
					\Storage::disk('reserved')->put('/receipts/'.$receiptName.'.pdf',$pdf->stream());
					$pdfFile = '/receipts/'.$receiptName.'.pdf';
					$receipt = new App\PayrollReceipt;
					$receipt->idnominaemployeenf = $nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf;
					$receipt->path = $pdfFile;
					$receipt->save();
					$nominaemployee->payment = 1;
					$nominaemployee->save();
				}
				if(App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 1 && round($request->amount+$totalPaidEmployee,2) == round($totalPayment,2))
				{
					$nominaemployee->payment = 1;
					$nominaemployee->save();
					if($nominaemployee->nominaCFDI->count() > 0)
					{
						$bill			= $nominaemployee->nominaCFDI->first();
						$bill->status	= 0;
						$bill->save();
					}
					else
					{
						$bill							= new App\Bill();
						$bill->idProject 				= $nominaemployee->workerData->first()->project;
						$bill->rfc						= $nominaemployee->workerData->first()->enterprises->rfc;
						$bill->businessName				= $nominaemployee->workerData->first()->enterprises->name;
						$bill->taxRegime				= $nominaemployee->workerData->first()->enterprises->taxRegime;
						$bill->clientRfc				= $nominaemployee->employee->first()->rfc;
						$bill->clientBusinessName		= $nominaemployee->employee->first()->name.' '.$nominaemployee->employee->first()->last_name.' '.$nominaemployee->employee->first()->scnd_last_name;
						if(env('CFDI_VERSION','3_3') == '4_0')
						{
							$bill->receiver_tax_regime = $nominaemployee->employee->first()->tax_regime;
							$bill->version             = '4.0';
							$bill->receiver_zip_code   = $nominaemployee->employee->first()->cp;
							$bill->export              = '01';
							$bill->useBill             = 'CN01';
							$bill->paymentWay          = null;
						}
						else
						{
							$bill->useBill    = 'P01';
							$bill->paymentWay = '99';
						}
						$bill->expeditionDate			= Carbon::Now()->subMinute(6);
						$bill->postalCode				= $nominaemployee->workerData->first()->enterprises->postalCode;
						$bill->status					= 8;
						$bill->type						= 'N';
						$bill->paymentMethod			= 'PUE';
						$bill->currency					= 'MXN';
						$bill->save();
						$billReceiver					= new App\BillNominaReceiver();
						$billReceiver->curp				= $nominaemployee->employee->first()->curp;
						$billReceiver->contractType_id	= $nominaemployee->workerData->first()->workerType;
						$billReceiver->regime_id		= $nominaemployee->workerData->first()->regime_id;
						$billReceiver->employee_id		= $nominaemployee->idrealEmployee;
						$billReceiver->periodicity		= $nominaemployee->idCatPeriodicity;
						$billReceiver->c_state			= $nominaemployee->workerData->first()->states->c_state;
						if(env('CFDI_VERSION','3_3') == '3_3' && $billReceiver->c_state == 'CMX')
						{
							$billReceiver->c_state = 'DIF';
						}
						$billReceiver->nss				= str_replace('-','',$nominaemployee->employee->first()->imss);
						if($nominaemployee->workerData->first()->reentryDate != '')
						{
							$billReceiver->laboralDateStart	= $nominaemployee->workerData->first()->reentryDate;
						}
						else
						{
							$billReceiver->laboralDateStart	= $nominaemployee->workerData->first()->imssDate;
						}
						$start = new \Carbon\Carbon($billReceiver->laboralDateStart);
						if($nominaemployee->to_date != '')
						{
							$ending = new \Carbon\Carbon($nominaemployee->to_date);
						}
						else
						{
							$ending = new \Carbon\Carbon($t_payment->paymentDate);
						}
						$ending       = $ending->addDay();
						$years        = $start->diff($ending);
						$months_start = new \Carbon\Carbon($start->addYearsNoOverflow($years->format('%y')));
						$months       = $months_start->diff($ending);
						$days_start   = new \Carbon\Carbon($months_start->addMonthsNoOverflow($months->format('%m')));
						$days         = $days_start->diff($ending);
						$antiq        = 'P';
						if($years->format('%y') > 0)
						{
							$antiq .= $years->format('%y').'Y';
							if($months->format('%m') > 0)
							{
								$antiq .= $months->format('%m').'M';
							}
							$antiq .= $days->format('%d').'D';
						}
						else
						{
							if($months->format('%m') > 0)
							{
								$antiq .= $months->format('%m').'M';
								$antiq .= $days->format('%d').'D';
							}
							else
							{
								$week = floor($days->format('%d') / 7);
								$antiq = 'P'.$week.'W';
							}
						}
						$billReceiver->antiquity		= $antiq;
						$billReceiver->job_risk			= App\EmployerRegister::where('employer_register',$nominaemployee->workerData->first()->employer_register)->first()->position_risk_id;
						$billReceiver->sdi				= $nominaemployee->workerData->first()->sdi;
						$billReceiver->bill_id			= $bill->idBill;
						$billReceiver->save();
						$perceptions					= 0;
						$deductions						= 0;
						$other_payments					= 0;
						$billNomina						= new App\BillNomina();
						$billNomina->employer_register	= $nominaemployee->workerData->first()->employer_register;
						$billNomina->bill_id			= $bill->idBill;
						switch ($typePayroll)
						{
							case '001':
								$billNomina->type				= 'O';
								$billNomina->paymentDate		= $t_payment->paymentDate;
								$billNomina->paymentStartDate	= $nominaemployee->from_date;
								$billNomina->paymentEndDate		= $nominaemployee->to_date;
								$billNomina->paymentDays		= $nominaemployee->salary->first()->workedDays;
								$billNomina->save();
								if($billReceiver->regime_id == '09')
								{
									$per							= new App\BillNominaPerception();
									$per->type						= '046';
									$per->perceptionKey				= '046';
									$per->concept					= 'Asimilados a salarios';
									$per->taxedAmount				= round($nominaemployee->salary->first()->salary,2);
									$per->exemptAmount				= 0;
									$per->bill_nomina_id			= $billNomina->id;
									$per->save();
								}
								else
								{
									$per							= new App\BillNominaPerception();
									$per->type						= '001';
									$per->perceptionKey				= '001';
									$per->concept					= 'Sueldo';
									$per->taxedAmount				= round($nominaemployee->salary->first()->salary,2);
									$per->exemptAmount				= 0;
									$per->bill_nomina_id			= $billNomina->id;
									$per->save();
								}
								$perceptions					+= $per->taxedAmount;
								if($nominaemployee->salary->first()->loan_perception != '' && $nominaemployee->salary->first()->loan_perception > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '038';
									$per->perceptionKey  = '038';
									$per->concept        = 'Préstamo';
									$per->taxedAmount    = round($nominaemployee->salary->first()->loan_perception,2);
									$per->exemptAmount   = 0;
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
								}
								if($nominaemployee->salary->first()->puntuality > 0)
								{
									$per					= new App\BillNominaPerception();
									$per->type				= '010';
									$per->perceptionKey		= '010';
									$per->concept			= 'Puntualidad';
									$per->taxedAmount		= round($nominaemployee->salary->first()->puntuality,2);
									$per->exemptAmount		= 0;
									$per->bill_nomina_id	= $billNomina->id;
									$per->save();
									$perceptions			+= $per->taxedAmount;
								}
								if($nominaemployee->salary->first()->assistance > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '049';
									$per->perceptionKey  = '049';
									$per->concept        = 'Asistencia';
									$per->taxedAmount    = round($nominaemployee->salary->first()->assistance,2);
									$per->exemptAmount   = 0;
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
								}
								if($nominaemployee->salary->first()->extra_hours > 0 && $nominaemployee->salary->first()->extra_time > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '019';
									$per->perceptionKey  = '019';
									$per->concept        = 'Horas extra';
									$per->taxedAmount    = round($nominaemployee->salary->first()->extra_time_taxed,2);
									$per->exemptAmount   = round($nominaemployee->salary->first()->extra_time - $nominaemployee->salary->first()->extra_time_taxed,2);
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
									$perceptions         += $per->exemptAmount;
									if($nominaemployee->salary->first()->extra_hours < 9)
									{
										$extra                   = new App\BillNominaExtraHours();
										$extra->days             = 1;
										$extra->hours            = $nominaemployee->salary->first()->extra_hours;
										$extra->amount           = round($nominaemployee->salary->first()->extra_time,2);
										$extra->cat_type_hour_id = '01';
										$extra->bill_nomina_id   = $billNomina->id;
										$extra->save();
									}
									else
									{
										$extra                   = new App\BillNominaExtraHours();
										$extra->days             = 2;
										$extra->hours            = 9;
										$extra->amount           = $nominaemployee->salary->first()->sd / 8 * 2 * 9;
										$extra->cat_type_hour_id = '01';
										$extra->bill_nomina_id   = $billNomina->id;
										$extra->save();
										if($nominaemployee->salary->first()->extra_hours - 9 > 0)
										{
											$hours = $nominaemployee->salary->first()->extra_hours - 9;
											$extra                   = new App\BillNominaExtraHours();
											$extra->days             = ceil($hours / 8);
											$extra->hours            = $hours;
											$extra->amount           = $nominaemployee->salary->first()->sd / 8 * 3 * $hours;
											$extra->cat_type_hour_id = '02';
											$extra->bill_nomina_id   = $billNomina->id;
											$extra->save();
										}
									}
								}
								if($nominaemployee->salary->first()->holiday != '' && $nominaemployee->salary->first()->holiday > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '001';
									$per->perceptionKey  = '001';
									$per->concept        = 'Día festivo';
									$per->taxedAmount    = round($nominaemployee->salary->first()->holiday_taxed,2);
									$per->exemptAmount   = round($nominaemployee->salary->first()->holiday - $nominaemployee->salary->first()->holiday_taxed,2);
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
									$perceptions         += $per->exemptAmount;
								}
								if($nominaemployee->salary->first()->sundays > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '020';
									$per->perceptionKey  = '020';
									$per->concept        = 'Prima dominical';
									$per->taxedAmount    = round($nominaemployee->salary->first()->taxed_sunday,2);
									$per->exemptAmount   = round($nominaemployee->salary->first()->exempt_sunday,2);
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
									$perceptions         += $per->exemptAmount;
								}
								if($billReceiver->regime_id == '02')
								{
									$otr					= new App\BillNominaOtherPayment();
									$otr->type				= '002';
									$otr->otherPaymentKey	= '002';
									$otr->concept			= 'Subsidio';
									if($nominaemployee->salary->first()->subsidy == '')
									{
										$otr->amount			= 0;
										$otr->subsidy_caused	= 0;
									}
									else
									{
										$otr->amount			= round($nominaemployee->salary->first()->subsidy,2);
										$otr->subsidy_caused	= round($nominaemployee->salary->first()->subsidyCaused,2);
									}
									$otr->bill_nomina_id	= $billNomina->id;
									$otr->save();
									$other_payments			+= $otr->amount;
								}
								if($nominaemployee->salary->first()->imss != '' && $nominaemployee->salary->first()->imss > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '001';
									$ded->deductionKey		= '001';
									$ded->concept			= 'IMSS';
									$ded->amount			= round($nominaemployee->salary->first()->imss,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->infonavit > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '009';
									$ded->deductionKey		= '009';
									$ded->concept			= 'INFONAVIT';
									$ded->amount			= round($nominaemployee->salary->first()->infonavit,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->fonacot > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '011';
									$ded->deductionKey		= '011';
									$ded->concept			= 'FONACOT';
									$ded->amount			= round($nominaemployee->salary->first()->fonacot,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->loan_retention != '' && $nominaemployee->salary->first()->loan_retention > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '004';
									$ded->deductionKey		= '004';
									$ded->concept			= 'Préstamo';
									$ded->amount			= round($nominaemployee->salary->first()->loan_retention,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->other_retention_amount > 0 && $nominaemployee->salary->first()->other_retention_concept != '')
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '004';
									$ded->deductionKey		= '004';
									$ded->concept			= $nominaemployee->salary->first()->other_retention_concept;
									$ded->amount			= round($nominaemployee->salary->first()->other_retention_amount,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->isrRetentions > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '002';
									$ded->deductionKey		= '002';
									$ded->concept			= 'ISR';
									$ded->amount			= round($nominaemployee->salary->first()->isrRetentions,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->salary->first()->alimony > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '007';
									$ded->deductionKey		= '007';
									$ded->concept			= 'Pensión alimenticia';
									$ded->amount			= round($nominaemployee->salary->first()->alimony,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								break;

							case '002':
								$billReceiver->periodicity		= '99';
								$billReceiver->save();
								$billNomina->type				= 'E';
								$billNomina->paymentDate		= $t_payment->paymentDate;
								$billNomina->paymentStartDate	= $t_payment->paymentDate;
								$billNomina->paymentEndDate		= $t_payment->paymentDate;
								$billNomina->paymentDays		= 1;
								$billNomina->save();
								$per							= new App\BillNominaPerception();
								$per->type						= '002';
								$per->perceptionKey				= '002';
								$per->concept					= 'Aguinaldo';
								$per->taxedAmount				= round($nominaemployee->bonus->first()->taxableBonus,2);
								$per->exemptAmount				= round($nominaemployee->bonus->first()->exemptBonus,2);
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
								$perceptions					+= $per->taxedAmount;
								$perceptions					+= $per->exemptAmount;
								if($nominaemployee->bonus->first()->isr > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '002';
									$ded->deductionKey		= '002';
									$ded->concept			= 'ISR';
									$ded->amount			= round($nominaemployee->bonus->first()->isr,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->bonus->first()->alimony > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '007';
									$ded->deductionKey		= '007';
									$ded->concept			= 'Pensión alimenticia';
									$ded->amount			= round($nominaemployee->bonus->first()->alimony,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($billReceiver->regime_id == '02')
								{
									$otr					= new App\BillNominaOtherPayment();
									$otr->type				= '002';
									$otr->otherPaymentKey	= '002';
									$otr->concept			= 'Subsidio';
									$otr->amount			= 0;
									$otr->subsidy_caused	= 0;
									$otr->bill_nomina_id	= $billNomina->id;
									$otr->save();
									$other_payments			+= $otr->amount;
								}
								break;

							case '003':
							case '004':
								$billReceiver->periodicity			= '99';
								$billReceiver->save();
								$billReceiver->regime_id			= '13';
								$billReceiver->contractType_id		= '99';
								$billReceiver->save();
								$billNomina->type					= 'E';
								$billNomina->paymentDate			= $t_payment->paymentDate;
								$billNomina->paymentStartDate		= $t_payment->paymentDate;
								$billNomina->paymentEndDate			= $t_payment->paymentDate;
								$billNomina->paymentDays			= 1;
								$billNomina->save();
								if($nominaemployee->liquidation->first()->liquidationSalary > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '025';
									$per->perceptionKey  = '025';
									$per->concept        = 'Sueldo por liquidación';
									$per->taxedAmount    = round($nominaemployee->liquidation->first()->liquidationSalary,2);
									$per->exemptAmount   = 0;
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
								}
								if($nominaemployee->liquidation->first()->seniorityPremium > 0)
								{
									$per                 = new App\BillNominaPerception();
									$per->type           = '022';
									$per->perceptionKey  = '022';
									$per->concept        = 'Prima de antigüedad';
									$per->taxedAmount    = round($nominaemployee->liquidation->first()->seniorityPremium,2);
									$per->exemptAmount   = 0;
									$per->bill_nomina_id = $billNomina->id;
									$per->save();
									$perceptions         += $per->taxedAmount;
								}
								if(($nominaemployee->liquidation->first()->taxedCompensation + $nominaemployee->liquidation->first()->exemptCompensation) > 0)
								{
									$per								= new App\BillNominaPerception();
									$per->type							= '025';
									$per->perceptionKey					= '025';
									$per->concept						= 'Indemnización';
									$per->taxedAmount					= round($nominaemployee->liquidation->first()->taxedCompensation,2);
									$per->exemptAmount					= round($nominaemployee->liquidation->first()->exemptCompensation,2);
									$per->bill_nomina_id				= $billNomina->id;
									$per->save();
									$perceptions						+= $per->taxedAmount;
									$perceptions						+= $per->exemptAmount;
								}
								if($nominaemployee->liquidation->first()->holidays > 0)
								{
									$per								= new App\BillNominaPerception();
									$per->type							= '001';
									$per->perceptionKey					= '001';
									$per->concept						= 'Vacaciones';
									$per->taxedAmount					= round($nominaemployee->liquidation->first()->holidays,2);
									$per->exemptAmount					= 0;
									$per->bill_nomina_id				= $billNomina->id;
									$per->save();
									$perceptions						+= $per->taxedAmount;
								}
								if(($nominaemployee->liquidation->first()->taxableBonus + $nominaemployee->liquidation->first()->exemptBonus) > 0)
								{
									$per								= new App\BillNominaPerception();
									$per->type							= '002';
									$per->perceptionKey					= '002';
									$per->concept						= 'Aguinaldo';
									$per->taxedAmount					= round($nominaemployee->liquidation->first()->taxableBonus,2);
									$per->exemptAmount					= round($nominaemployee->liquidation->first()->exemptBonus,2);
									$per->bill_nomina_id				= $billNomina->id;
									$per->save();
									$perceptions						+= $per->taxedAmount;
									$perceptions						+= $per->exemptAmount;
								}
								if(($nominaemployee->liquidation->first()->holidayPremiumTaxed + $nominaemployee->liquidation->first()->holidayPremiumExempt) > 0)
								{
									$per								= new App\BillNominaPerception();
									$per->type							= '021';
									$per->perceptionKey					= '021';
									$per->concept						= 'Prima vacacional';
									$per->taxedAmount					= round($nominaemployee->liquidation->first()->holidayPremiumTaxed,2);
									$per->exemptAmount					= round($nominaemployee->liquidation->first()->holidayPremiumExempt,2);
									$per->bill_nomina_id				= $billNomina->id;
									$per->save();
									$perceptions						+= $per->taxedAmount;
									$perceptions						+= $per->exemptAmount;
								}
								if($nominaemployee->liquidation->first()->otherPerception > 0)
								{
									$per							= new App\BillNominaPerception();
									$per->type						= '038';
									$per->perceptionKey				= '038';
									$per->concept					= 'Otras percepciones';
									$per->taxedAmount				= round($nominaemployee->liquidation->first()->otherPerception,2);
									$per->exemptAmount				= 0;
									$per->bill_nomina_id			= $billNomina->id;
									$per->save();
									$perceptions					+= $per->taxedAmount;
								}
								if($nominaemployee->liquidation->first()->isr > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '002';
									$ded->deductionKey		= '002';
									$ded->concept			= 'ISR';
									$ded->amount			= round($nominaemployee->liquidation->first()->isr,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->liquidation->first()->alimony > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '007';
									$ded->deductionKey		= '007';
									$ded->concept			= 'Pensión alimenticia';
									$ded->amount			= round($nominaemployee->liquidation->first()->alimony,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->liquidation->first()->other_retention > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '004';
									$ded->deductionKey		= '004';
									$ded->concept			= 'Otros';
									$ded->amount			= round($nominaemployee->liquidation->first()->other_retention,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								$bill            = App\Bill::find($bill->idBill);
								$indemnification = round($bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('taxedAmount') + $bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('exemptAmount'),2);
								if($indemnification > 0)
								{
									$ind                               = new App\BillNominaIndemnification();
									$ind->total_paid                   = round($indemnification,2);
									$ind->service_year                 = $nominaemployee->liquidation->first()->fullYears;
									$ind->last_ordinary_monthly_salary = round($nominaemployee->liquidation->first()->sd * 30,2);
									$ind->cumulative_income            = $bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('taxedAmount');
									$nonCumulative                     = round($ind->cumulative_income - $ind->last_ordinary_monthly_salary,2);
									$nonCumulative                     = ($nonCumulative < 0 ? 0 : $nonCumulative);
									$ind->non_cumulative_income        = $nonCumulative;
									$ind->bill_nomina_id               = $billNomina->id;
									$ind->save();
								}
								break;
							case '005':
								$billReceiver->periodicity		= '99';
								$billReceiver->save();
								$billNomina->type				= 'E';
								$billNomina->paymentDate		= $t_payment->paymentDate;
								$billNomina->paymentStartDate	= $t_payment->paymentDate;
								$billNomina->paymentEndDate		= $t_payment->paymentDate;
								$billNomina->paymentDays		= 1;
								$billNomina->save();
								$per							= new App\BillNominaPerception();
								$per->type						= '021';
								$per->perceptionKey				= '021';
								$per->concept					= 'Prima vacacional';
								$per->taxedAmount				= round($nominaemployee->vacationPremium->first()->holidayPremiumTaxed,2);
								$per->exemptAmount				= round($nominaemployee->vacationPremium->first()->exemptHolidayPremium,2);
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
								$perceptions					+= $per->taxedAmount;
								$perceptions					+= $per->exemptAmount;
								if($nominaemployee->vacationPremium->first()->isr > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '002';
									$ded->deductionKey		= '002';
									$ded->concept			= 'ISR';
									$ded->amount			= round($nominaemployee->vacationPremium->first()->isr,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->vacationPremium->first()->alimony > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '007';
									$ded->deductionKey		= '007';
									$ded->concept			= 'Pensión alimenticia';
									$ded->amount			= round($nominaemployee->vacationPremium->first()->alimony,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($billReceiver->regime_id == '02')
								{
									$otr					= new App\BillNominaOtherPayment();
									$otr->type				= '002';
									$otr->otherPaymentKey	= '002';
									$otr->concept			= 'Subsidio';
									$otr->amount			= 0;
									$otr->subsidy_caused	= 0;
									$otr->bill_nomina_id	= $billNomina->id;
									$otr->save();
									$other_payments			+= $otr->amount;
								}
								break;

							case '006':
								$billReceiver->periodicity		= '99';
								$billReceiver->save();
								$billNomina->type				= 'E';
								$billNomina->paymentDate		= $t_payment->paymentDate;
								$billNomina->paymentStartDate	= $t_payment->paymentDate;
								$billNomina->paymentEndDate		= $t_payment->paymentDate;
								$billNomina->paymentDays		= 1;
								$billNomina->save();
								$per							= new App\BillNominaPerception();
								$per->type						= '003';
								$per->perceptionKey				= '003';
								$per->concept					= 'Reparto de utilidades';
								$per->taxedAmount				= round($nominaemployee->profitSharing->first()->taxedPtu,2);
								$per->exemptAmount				= round($nominaemployee->profitSharing->first()->exemptPtu,2);
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
								$perceptions					+= $per->taxedAmount;
								$perceptions					+= $per->exemptAmount;
								
								if($nominaemployee->profitSharing->first()->isrRetentions > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '002';
									$ded->deductionKey		= '002';
									$ded->concept			= 'ISR';
									$ded->amount			= round($nominaemployee->profitSharing->first()->isrRetentions,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}
								if($nominaemployee->profitSharing->first()->alimony > 0)
								{
									$ded					= new App\BillNominaDeduction();
									$ded->type				= '007';
									$ded->deductionKey		= '007';
									$ded->concept			= 'Pensión alimenticia';
									$ded->amount			= round($nominaemployee->profitSharing->first()->alimony,2);
									$ded->bill_nomina_id	= $billNomina->id;
									$ded->save();
									$deductions				+= $ded->amount;
								}

								if($billReceiver->regime_id == '02')
								{
									$otr					= new App\BillNominaOtherPayment();
									$otr->type				= '002';
									$otr->otherPaymentKey	= '002';
									$otr->concept			= 'Subsidio';
									$otr->amount			= 0;
									$otr->subsidy_caused	= 0;
									$otr->bill_nomina_id	= $billNomina->id;
									$otr->save();
									$other_payments			+= $otr->amount;
								}
								break;
							default:
								# code...
								break;
						}
						$billNomina->perceptions	= $perceptions;
						$billNomina->deductions		= $deductions;
						$billNomina->other_payments	= $other_payments;
						$billNomina->save();
						$billDetail					= new App\BillDetail();
						$billDetail->keyProdServ	= '84111505';
						$billDetail->keyUnit		= 'ACT';
						$billDetail->quantity		= 1;
						$billDetail->description	= 'Pago de nómina';
						$billDetail->value			= round(($perceptions + $other_payments),2);
						$billDetail->amount			= $billDetail->value;
						$billDetail->discount		= round($deductions,2);
						$billDetail->idBill			= $bill->idBill;
						if($bill->version == '4.0')
						{
							$billDetail->cat_tax_object_id = '01';
						}
						$billDetail->save();
						$bill->status				= 0;
						$bill->subtotal				= $billDetail->amount;
						$bill->discount				= $billDetail->discount;
						$bill->total				= $bill->subtotal - $bill->discount;
						$bill->save();
						$nominaemployee->nominaCFDI()->attach($bill->idBill);
					}
				}

				$payment		= App\Payment::where('idFolio',$request->idfolio)->get();
				$req			= App\RequestModel::find($request->idfolio);
				$resta			= 0;
				$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount') : 0;
				$total 			= $req->nominasReal->first()->amount;

				$resta = round($total,2)-round($totalPagado,2);
				if($resta == 0)
				{
					$payUpdate			= App\RequestModel::find($request->idfolio);
					$payUpdate->status	= 10;
					$payUpdate->payment	= 1;
					$payUpdate->save();   
				}
				else
				{
					$payUpdate			= App\RequestModel::find($request->idfolio);
					$payUpdate->status	= 12;
					$payUpdate->payment	= 0;
					$payUpdate->save();   
				}

				/*$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 180);
						})
						->where('active',1)
						->where('notification',1)
						->get();
				if ($emails != "")
				{
					try
					{
						foreach ($emails as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "";
							$status 		= "Timbrar";
							$date 			= Carbon::now();
							$requestUser	= '';
							$url 			= route('bill.index');
							$subject 		= "CDFI Pendiente";
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', 'Pago registrado exitosamente', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', 'El pago se realizó exitosamente, pero ocurrio un error al enviar el correo de notificación', 'success');";
					}
				}*/

				$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";

				if ($resta == 0) 
				{
					return searchRedirect(90, $alert, 'administration/payments/pending');
				}
				else
				{
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}	
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if (!$request->status)
			{
				$alert = "swal('', 'Acepte o Rechace la solicitud', 'error');";
				return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
			}
			if ($request->status == 'x') 
			{
				if ($request->tax > $request->taxRes)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que el Impuesto adicional es mayor a lo que se adeuda.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				if ($request->retention > $request->retentionRes)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que la Retención es mayor a lo que se adeuda.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				if ($request->subtotal > $request->subtotalRes)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que el Subtotal es mayor a lo que se adeuda.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				if ($request->iva > $request->ivaRes)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que el Iva es mayor a lo que se adeuda.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				if ($request->amount > $request->amountRes)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que el Importe es mayor a lo que se adeuda.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				if ($request->amount < 0)
				{
					$alert = "swal('', 'El pago no se pudo realizar, debido a que el Importe no puede ser menor a 0.', 'error');";
					return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
				}
				else
				{

					$date				= \DateTime::createFromFormat('d-m-Y', $request->paymentDate);
					$newdate			= $date->format('Y-m-d');
					
					$t_request			= App\RequestModel::find($request->idfolio);

					$paid 				= $t_request->paymentsRequest->sum('amount_real');

					switch ($request->idkind) 
					{
						case 1:
							$total	= $t_request->purchases->first()->amount;
							break;

						case 2:
							$total = $t_request->nominas->first()->amount;
							break;

						case 3:
							if($t_request->expenses->first()->reembolso>0)
							{
								$total = $t_request->expenses->first()->reembolso;
							}
							elseif($t_request->expenses->first()->reintegro>0)
							{
								$total = $t_request->expenses->first()->reintegro;
							}
							else
							{
								$total = 0;
							}
							break;

						case 5:
							$total = $t_request->loan->first()->amount;
							break;

						case 8:
							$total = $t_request->resource->first()->total;
							break;

						case 9:
							$total = $t_request->refunds->first()->total;
							break;

						case 11:
							$total = $t_request->adjustment->first()->amount;
							break;
							
						case 12:
							$total = $t_request->loanEnterprise->first()->amount;
							break;

						case 13:
							$total = $t_request->purchaseEnterprise->first()->amount;
							break;

						case 14:
							$total = $t_request->groups->first()->amount;
							break;

						case 15:
							$total = $t_request->movementsEnterprise->first()->amount;
							break;

						default:
							break;
					} 
					
					if (round($paid + $request->amount,2) > round($total,2)) 
					{
						$alert = 'swal("","El total a pagar excede el total de la solicitud, por favor verifique los montos.","error")';
						return redirect()->back()->with('alert',$alert);
					}

					$t_request->code	= $request->code;
					$t_request->save();
					
					if ($request->exchange_rate != "") 
					{
						$exchange_rate = $request->exchange_rate;
					}
					else
					{
						$exchange_rate = 1;
					}

					//paymentDate
					$data									= App\Module::find($this->module_id);
					$t_payment								= new App\Payment();
					$t_payment->amount						= $request->amount * $exchange_rate;
					$t_payment->subtotal					= $request->subtotal * $exchange_rate;
					$t_payment->iva							= $request->iva * $exchange_rate;
					$t_payment->tax							= $request->tax * $exchange_rate;
					$t_payment->retention					= $request->retention * $exchange_rate;
					$t_payment->tax_real					= $request->tax;
					$t_payment->retention_real				= $request->retention;
					$t_payment->amount_real					= $request->amount;
					$t_payment->subtotal_real				= $request->subtotal;
					$t_payment->iva_real					= $request->iva;
					$t_payment->account						= $request->account;
					$t_payment->paymentDate					= Carbon::createFromFormat('d-m-Y',$request->paymentDate)->format('Y-m-d H:i:s');
					$t_payment->elaborateDate				= Carbon::now();
					$t_payment->idFolio						= $request->idfolio;
					$t_payment->idKind						= $request->idkind;
					$t_payment->idRequest					= Auth::user()->id;
					$t_payment->idEnterprise				= $request->enterprise_id;
					$t_payment->commentaries				= $request->commentaries;
					$t_payment->exchange_rate				= $exchange_rate;
					$t_payment->exchange_rate_description	= $request->exchange_rate_description;
					$t_payment->save();

					$idpayment 					= $t_payment->idpayment;
					if(isset($request->checkPartial) && count($request->checkPartial) > 0)
					{
						for($p = 0; $p < count($request->checkPartial); $p++)
						{
							$partialP	= App\PartialPayment::where('id', $request->checkPartial[$p])
								->where('date_delivery',null)
								->where('payment_id', null)
								->first();
							$partialP->date_delivery	= Carbon::createFromFormat('d-m-Y',$request->paymentDate)->format('Y-m-d H:i:s');;
							$partialP->payment_id		= $idpayment;
							$partialP->save();
						}
					}

					if ($request->realPath != "") 
					{
						for ($i=0; $i < count($request->realPath); $i++) 
						{ 
							$documents 				= new App\DocumentsPayments();
							$new_file_name 			= Files::rename($request->realPath[$i],$request->idfolio);
							$documents->path 		= $new_file_name;
							$documents->idpayment 	= $idpayment;
							$documents->save();
						}
					}

					$payment		= App\Payment::where('idFolio',$request->idfolio)->get();
					$req			= App\RequestModel::find($request->idfolio);
					$resta			= 0;
					$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;

					switch ($request->idkind) 
					{
						// purchase request
						case 1:
							$total					= $req->purchases->first()->amount;
							$resta					= round($total,2)-round($totalPagado,2);

							/*
							if ($req->taxPayment == 1) 
							{
								$t_payment->subtotal	= $t_payment->amount / 1.16;
								$t_payment->iva			= $t_payment->amount - $t_payment->subtotal;
								$t_payment->fiscal 		= $req->taxPayment;
								$t_payment->save();
							}
							else
							{
								$t_payment->iva			= 0;
								$t_payment->subtotal	= $t_payment->amount;
								$t_payment->fiscal 		= $req->taxPayment;
								$t_payment->save();
							}
							*/
							$payUpdate				= App\RequestModel::find($request->idfolio);
							if($resta == 0)
							{
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;
						// nomina request
						case 2:
							$total = $req->nominas->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						// expenses request
						case 3:
							$resta = 0;
							if($req->expenses->first()->reembolso>0)
							{
								$total = $req->expenses->first()->reembolso;
							}
							elseif($req->expenses->first()->reintegro>0)
							{
								$total = $req->expenses->first()->reintegro;
							}
							else
							{
								$total = 0;
							}

							$resta = round($total,2)-round($totalPagado,2);

							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						// loan request
						case 5:
							$total = $req->loan->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						// resource request
						case 8:
							$total = $req->resource->first()->total;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						case 9:
							$total = $req->refunds->first()->total;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						case 11:
							$total = $req->adjustment->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;
							
						case 12:
							$total = $req->loanEnterprise->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						case 13:
							$total = $req->purchaseEnterprise->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						case 14:
							$total = $req->groups->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						case 15:
							$total = $req->movementsEnterprise->first()->amount;
							$resta = round($total,2)-round($totalPagado,2);
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;

						/*
						case 16:
							$total = $req->nominasReal->first()->amount;
							$resta = ($total)-$totalPagado;
							if($resta == 0)
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 10;
								$payUpdate->payment	= 1;
								$payUpdate->save();   
							}
							else
							{
								$payUpdate			= App\RequestModel::find($request->idfolio);
								$payUpdate->status	= 12;
								$payUpdate->payment	= 0;
								$payUpdate->save();   
							}
							break;
						*/
						default:
							break;
					} 

					$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
					if ($resta == 0) 
					{
						return searchRedirect(90, $alert, 'administration/payments/pending');
					}
					else
					{
						return redirect()->route('payments.review.edit',['id'=>$request->idfolio])->with('alert',$alert);
					}					
				}
			}
			else
			{
				$t_request					= App\RequestModel::find($request->idfolio);
				$t_request->status			= 13;
				$t_request->paymentComment	= $request->paymentComment;
				$t_request->save();

				
/*
				$emailRequest 			= "";
					
				if ($t_request->idElaborate == $t_request->idRequest) 
				{
					$emailRequest 	= App\User::where('id',$t_request->idElaborate)
									->where('notification',1)
									->get();
				}
				else
				{
					$emailRequest 	= App\User::where('id',$t_request->idElaborate)
									->orWhere('id',$t_request->idRequest)
									->where('notification',1)
									->get();
				}

				if ($emailRequest != "")
				{
					try
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= $t_request->requestkind->kind;
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							switch ($t_request->kind) 
							{
								case 1:
									$url = route('purchase.follow.edit',['id'=>$request->idfolio]);
									break;

								case 2:
									$url = route('payroll.follow.edit',['id'=>$request->idfolio]);
									break;

								case 3:
									$url = route('expenses.follow.edit',['id'=>$request->idfolio]);
									break;
								
								case 5:
									$url = route('loan.follow.edit',['id'=>$request->idfolio]);
									break;

								case 8:
									$url = route('resources.follow.edit',['id'=>$request->idfolio]);
									break;

								case 9:
									$url = route('refunds.follow.edit',['id'=>$request->idfolio]);
									break;

								case 11:
									$url = route('movements-accounts.follow.edit',['id'=>$request->idfolio]);
									break;

								case 12:
									$url = route('movements-accounts.follow.edit',['id'=>$request->idfolio]);
									break;

								case 13:
									$url = route('movements-accounts.follow.edit',['id'=>$request->idfolio]);
									break;

								case 14:
									$url = route('movements-accounts.follow.edit',['id'=>$request->idfolio]);
									break;

								case 15:
									$url = route('movements-accounts.follow.edit',['id'=>$request->idfolio]);
									break;

								default:
									# code...
									break;
							}

							$subject 		= "Estado de Solicitud";
							$requestUser 	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', 'La solicitud fue enviada exitosamente, pero ocurrio un error al enviar el correo de notificación', 'success');";
					}
				}*/

				$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
				return searchRedirect(90, $alert, 'administration/payments/pending');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function editPayment(Request $request)
	{
		if(Auth::user()->module->where('id',91)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$account			= $request->account;
			$name				= $request->name;
			$enterpriseid		= $request->enterpriseid;
			$folio				= $request->folio;
			$kind				= $request->kind;
			$mindate			= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate			= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$idnominaEmployee	= $request->idnominaEmployee;

			$employees 		= App\NominaEmployee::select('idnominaEmployee')->where('idrealEmployee',$idnominaEmployee)->get();

			$requests 	= App\RequestModel::join('payments','request_models.folio','payments.idFolio')
							->where(function($permissionDep)
							{
								$permissionDep->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(91)->pluck('departament_id'))
											->orWhere('request_models.idDepartamentR',null);
							})
							->where(function($permissionEnt)
							{
								$permissionEnt->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(91)->pluck('enterprise_id'))
											->orWhere('request_models.idEnterpriseR',null);
							})
							->whereIn('request_models.kind',[1,2,3,5,8,9,11,12,13,14,15,16,17])
							->whereIn('request_models.status',[5,10,11,12,18])
							->where(function($q)
							{
								$q->where('request_models.remittance',0)
									->orWhere(function($q)
									{
										$q->where('request_models.remittance',1)
										->whereHas('budget',function($q)
										{
											$q->where('status',1);
										});
									});
							})
							->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio,$kind,$enterpriseid,$idnominaEmployee,$employees)
							{
								if($enterpriseid != "")
								{
									$query->where('request_models.idEnterpriseR',$enterpriseid);
								}
								if($account != "")
								{
									$query->where('payments.account',$account);
								}
								if($name != "")
								{
									$query->whereHas('requestUser', function($queryU) use($name)
									{
										$queryU->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($kind != "")
								{
									$query->where('request_models.kind',$kind);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('payments.paymentDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
								}
								if ($idnominaEmployee != "") 
								{
									$query->whereIn('payments.idnominaEmployee',$employees);
								}
							})
							->orderBy('payments.paymentDate','DESC')
							->orderBy('folio','DESC')
							->paginate(10);
			return response(
				view('administracion.pagos.editarver',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 91,
						'requests'			=> $requests,
						'account'			=> $account,
						'name'				=> $name,
						'folio'				=> $folio,
						'kind'				=> $kind,
						'mindate'			=> $request->mindate,
						'maxdate'			=> $request->maxdate,
						'enterpriseid'		=> $enterpriseid,
						'idnominaEmployee'	=> $idnominaEmployee
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(91), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function showPayment($id)
	{
		if(Auth::user()->module->where('id',91)->count()>0)
		{
			$data 		= App\Module::find($this->module_id);
			$payment 	= App\Payment::find($id);
			$request 	= App\RequestModel::whereIn('request_models.kind',[1,2,3,5,8,9,11,12,13,14,15,16,17])
							->where(function($permissionDep)
							{
								$permissionDep->whereIn('idDepartamentR',Auth::user()->inChargeDep(91)->pluck('departament_id'))
											->orWhere('idDepartamentR',null);
							})
							->where(function($permissionEnt)
							{
								$permissionEnt->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(91)->pluck('enterprise_id'))
											->orWhere('idEnterpriseR',null);
							})
							->whereIn('status',[5,10,11,12,18])
							->where(function($q)
							{
								$q->where('remittance',0)
									->orWhere(function($q)
									{
										$q->where('remittance',1)
										->whereHas('budget',function($q)
										{
											$q->where('status',1);
										});
									});
							})
							->find($payment->idFolio);
			if ($request != "") 
			{
				if ($request->kind == 16) 
				{
					return view('administracion.pagos.editarpago-nomina',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id' 	=> $this->module_id,
							'option_id'	=> 91,
							'request' 	=> $request,
							'payment' 	=> $payment
						]
					);
				}
				else
				{
					return view('administracion.pagos.editarpago',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id' 	=> $this->module_id,
							'option_id'	=> 91,
							'request' 	=> $request,
							'payment' 	=> $payment
						]
					);
				}
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

	public function updatePaymentNomina(Request $request,$id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$idnomina			= App\NominaEmployee::find($request->idnominaEmployee)->idnomina;
			$typePayroll		= App\Nomina::find($idnomina)->idCatTypePayroll;
			
			$totalPaidEmployee	= App\Payment::where('idFolio',$request->idfolio)->where('idnominaEmployee',$request->idnominaEmployee)->where('type',1)->sum('amount');
			$nominaemployee		= App\NominaEmployee::find($request->idnominaEmployee);
			if (App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 1) 
			{

				switch ($typePayroll) 
				{
					case '001':
						$totalPayment	= round($nominaemployee->salary->first()->netIncome+$nominaemployee->salary->first()->alimony,2);
						break;

					case '002':
						$totalPayment	= round($nominaemployee->bonus->first()->netIncome+$nominaemployee->bonus->first()->alimony,2);
						break;

					case '003':
					case '004':
						$totalPayment	= round($nominaemployee->liquidation->first()->netIncome+$nominaemployee->liquidation->first()->alimony,2);
						break;

					case '005':
						$totalPayment	= round($nominaemployee->vacationPremium->first()->netIncome+$nominaemployee->vacationPremium->first()->alimony,2);
						break;

					case '006':
						$totalPayment	= round($nominaemployee->profitSharing->first()->netIncome+$nominaemployee->profitSharing->first()->alimony,2);
						break;
					
					default:
						# code...
						break;
				}
			}
			else
			{
				$totalPayment		= round($nominaemployee->nominasEmployeeNF->first()->amount,2);
			}

			if ($request->exchange_rate != "") 
			{
				$exchange_rate = $request->exchange_rate;
			}
			else
			{
				$exchange_rate = 1;
			}

			$t_payment								= App\Payment::find($id);
			$t_payment->account						= $request->account;
			$date									= \DateTime::createFromFormat('d-m-Y', $request->paymentDate);
			$newdate								= $date->format('Y-m-d');
			$t_payment->paymentDate					= $newdate;
			$t_payment->elaborateDate				= Carbon::now();
			$t_payment->idFolio						= $request->idfolio;
			$t_payment->idKind						= $request->idkind;
			$t_payment->idRequest					= Auth::user()->id;
			$t_payment->idEnterprise				= $request->enterprise_id;
			$t_payment->commentaries				= $request->commentaries;
			$t_payment->exchange_rate				= $exchange_rate;
			$t_payment->exchange_rate_description	= $request->exchange_rate_description;
			if ($request->beneficiary_pay != '' && $request->type_payment == 2) 
			{
				$t_payment->beneficiary	= $request->beneficiary_pay;
				$t_payment->type		= 2;
			}
			else
			{
				$t_payment->type 	= 1;
			}


			if (($request->amount+($totalPaidEmployee-$t_payment->amount))>$totalPayment)
			{
				$alert = "swal('', 'El pago no se pudo realizar, debido a que el importe es mayor a lo que se le adeuda al empleado.', 'error');";
				return redirect()->route('payments.showpayment',['id'=>$id])->with('alert',$alert);
			}
			$oldAmount                = $t_payment->amount;
			$t_payment->amount        = $request->amount * $exchange_rate;
			$t_payment->subtotal      = $request->subtotalRes * $exchange_rate;
			$t_payment->iva           = $request->ivaRes * $exchange_rate;
			$t_payment->amount_real   = $request->amount;
			$t_payment->subtotal_real = $request->subtotalRes;
			$t_payment->iva_real      = $request->ivaRes;
			$t_payment->save();

			if(round($request->amount+($totalPaidEmployee-$oldAmount),2) < round($totalPayment,2))
			{
				$nominaemployee->payment = 0;
				$nominaemployee->save();
			}

			if(App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 1 && round($request->amount+($totalPaidEmployee-$oldAmount),2) < round($totalPayment,2))
			{
				if($nominaemployee->nominaCFDI->count() > 0 && $nominaemployee->nominaCFDI->first()->status == 0)
				{
					$bill			= $nominaemployee->nominaCFDI->first();
					$bill->status	= 8;
					$bill->save();
				}
			}
			elseif(App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 1 && round($request->amount+($totalPaidEmployee-$oldAmount),2) == round($totalPayment,2))
			{
				if($nominaemployee->nominaCFDI->count() > 0)
				{
					if($nominaemployee->nominaCFDI->first()->status == 8)
					{
						$bill			= $nominaemployee->nominaCFDI->first();
						$bill->status	= 0;
						$bill->save();
					}
				}
				else
				{
					$bill							= new App\Bill();
					$bill->idProject 				= $nominaemployee->workerData->first()->project;
					$bill->rfc						= $nominaemployee->workerData->first()->enterprises->rfc;
					$bill->businessName				= $nominaemployee->workerData->first()->enterprises->name;
					$bill->taxRegime				= $nominaemployee->workerData->first()->enterprises->taxRegime;
					$bill->clientRfc				= $nominaemployee->employee->first()->rfc;
					$bill->clientBusinessName		= $nominaemployee->employee->first()->name.' '.$nominaemployee->employee->first()->last_name.' '.$nominaemployee->employee->first()->scnd_last_name;
					if(env('CFDI_VERSION','3_3') == '4_0')
					{
						$bill->receiver_tax_regime = $nominaemployee->employee->first()->tax_regime;
						$bill->version             = '4.0';
						$bill->receiver_zip_code   = $nominaemployee->employee->first()->cp;
						$bill->export              = '01';
						$bill->useBill             = 'CN01';
						$bill->paymentWay          = null;
					}
					else
					{
						$bill->useBill    = 'P01';
						$bill->paymentWay = '99';
					}
					$bill->expeditionDate			= Carbon::Now()->subMinute(6);
					$bill->postalCode				= $nominaemployee->workerData->first()->enterprises->postalCode;
					$bill->status					= 8;
					$bill->type						= 'N';
					$bill->paymentMethod			= 'PUE';
					$bill->currency					= 'MXN';
					$bill->save();
					$billReceiver					= new App\BillNominaReceiver();
					$billReceiver->curp				= $nominaemployee->employee->first()->curp;
					$billReceiver->contractType_id	= $nominaemployee->workerData->first()->workerType;
					$billReceiver->regime_id		= $nominaemployee->workerData->first()->regime_id;
					$billReceiver->employee_id		= $nominaemployee->idrealEmployee;
					$billReceiver->periodicity		= $nominaemployee->idCatPeriodicity;
					$billReceiver->c_state			= $nominaemployee->workerData->first()->states->c_state;
					if(env('CFDI_VERSION','3_3') == '3_3' && $billReceiver->c_state == 'CMX')
					{
						$billReceiver->c_state = 'DIF';
					}
					$billReceiver->nss				= str_replace('-','',$nominaemployee->employee->first()->imss);
					if($nominaemployee->workerData->first()->reentryDate != '')
					{
						$billReceiver->laboralDateStart	= $nominaemployee->workerData->first()->reentryDate;
					}
					else
					{
						$billReceiver->laboralDateStart	= $nominaemployee->workerData->first()->imssDate;
					}
					$start = new \Carbon\Carbon($billReceiver->laboralDateStart);
					if($nominaemployee->to_date != '')
					{
						$ending = new \Carbon\Carbon($nominaemployee->to_date);
					}
					else
					{
						$ending = new \Carbon\Carbon($t_payment->paymentDate);
					}
					$ending       = $ending->addDay();
					$years        = $start->diff($ending);
					$months_start = new \Carbon\Carbon($start->addYearsNoOverflow($years->format('%y')));
					$months       = $months_start->diff($ending);
					$days_start   = new \Carbon\Carbon($months_start->addMonthsNoOverflow($months->format('%m')));
					$days         = $days_start->diff($ending);
					$antiq        = 'P';
					if($years->format('%y') > 0)
					{
						$antiq .= $years->format('%y').'Y';
						if($months->format('%m') > 0)
						{
							$antiq .= $months->format('%m').'M';
						}
						$antiq .= $days->format('%d').'D';
					}
					else
					{
						if($months->format('%m') > 0)
						{
							$antiq .= $months->format('%m').'M';
							$antiq .= $days->format('%d').'D';
						}
						else
						{
							$week = floor($days->format('%d') / 7);
							$antiq = 'P'.$week.'W';
						}
					}
					$billReceiver->antiquity		= $antiq;
					$billReceiver->job_risk			= App\EmployerRegister::where('employer_register',$nominaemployee->workerData->first()->employer_register)->first()->position_risk_id;
					$billReceiver->sdi				= $nominaemployee->workerData->first()->sdi;
					$billReceiver->bill_id			= $bill->idBill;
					$billReceiver->save();
					$perceptions					= 0;
					$deductions						= 0;
					$other_payments					= 0;
					$billNomina						= new App\BillNomina();
					$billNomina->employer_register	= $nominaemployee->workerData->first()->employer_register;
					$billNomina->bill_id			= $bill->idBill;
					switch ($typePayroll)
					{
						case '001':
							$billNomina->type				= 'O';
							$billNomina->paymentDate		= $t_payment->paymentDate;
							$billNomina->paymentStartDate	= $nominaemployee->from_date;
							$billNomina->paymentEndDate		= $nominaemployee->to_date;
							$billNomina->paymentDays		= $nominaemployee->salary->first()->workedDays;
							$billNomina->save();
							if($billReceiver->regime_id == '09')
							{
								$per							= new App\BillNominaPerception();
								$per->type						= '046';
								$per->perceptionKey				= '046';
								$per->concept					= 'Asimilados a salarios';
								$per->taxedAmount				= round($nominaemployee->salary->first()->salary,2);
								$per->exemptAmount				= 0;
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
							}
							else
							{
								$per							= new App\BillNominaPerception();
								$per->type						= '001';
								$per->perceptionKey				= '001';
								$per->concept					= 'Sueldo';
								$per->taxedAmount				= round($nominaemployee->salary->first()->salary,2);
								$per->exemptAmount				= 0;
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
							}
							$perceptions					+= $per->taxedAmount;
							if($nominaemployee->salary->first()->loan_perception != '' && $nominaemployee->salary->first()->loan_perception > 0)
							{
								$per					= new App\BillNominaPerception();
								$per->type				= '038';
								$per->perceptionKey		= '038';
								$per->concept			= 'Préstamo';
								$per->taxedAmount		= round($nominaemployee->salary->first()->loan_perception,2);
								$per->exemptAmount		= 0;
								$per->bill_nomina_id	= $billNomina->id;
								$per->save();
								$perceptions			+= $per->taxedAmount;
							}
							if($nominaemployee->salary->first()->puntuality > 0)
							{
								$per					= new App\BillNominaPerception();
								$per->type				= '010';
								$per->perceptionKey		= '010';
								$per->concept			= 'Puntualidad';
								$per->taxedAmount		= round($nominaemployee->salary->first()->puntuality,2);
								$per->exemptAmount		= 0;
								$per->bill_nomina_id	= $billNomina->id;
								$per->save();
								$perceptions			+= $per->taxedAmount;
							}
							if($nominaemployee->salary->first()->assistance > 0)
							{
								$per					= new App\BillNominaPerception();
								$per->type				= '049';
								$per->perceptionKey		= '049';
								$per->concept			= 'Asistencia';
								$per->taxedAmount		= round($nominaemployee->salary->first()->assistance,2);
								$per->exemptAmount		= 0;
								$per->bill_nomina_id	= $billNomina->id;
								$per->save();
								$perceptions			+= $per->taxedAmount;
							}
							if($nominaemployee->salary->first()->extra_hours > 0 && $nominaemployee->salary->first()->extra_time > 0)
							{
								$per                 = new App\BillNominaPerception();
								$per->type           = '019';
								$per->perceptionKey  = '019';
								$per->concept        = 'Horas extra';
								$per->taxedAmount    = round($nominaemployee->salary->first()->extra_time_taxed,2);
								$per->exemptAmount   = round($nominaemployee->salary->first()->extra_time - $nominaemployee->salary->first()->extra_time_taxed,2);
								$per->bill_nomina_id = $billNomina->id;
								$per->save();
								$perceptions         += $per->taxedAmount;
								$perceptions         += $per->exemptAmount;
								if($nominaemployee->salary->first()->extra_hours < 9)
								{
									$extra                   = new App\BillNominaExtraHours();
									$extra->days             = 1;
									$extra->hours            = $nominaemployee->salary->first()->extra_hours;
									$extra->amount           = round($nominaemployee->salary->first()->extra_time,2);
									$extra->cat_type_hour_id = '01';
									$extra->bill_nomina_id   = $billNomina->id;
									$extra->save();
								}
								else
								{
									$extra                   = new App\BillNominaExtraHours();
									$extra->days             = 2;
									$extra->hours            = 9;
									$extra->amount           = $nominaemployee->salary->first()->sd / 8 * 2 * 9;
									$extra->cat_type_hour_id = '01';
									$extra->bill_nomina_id   = $billNomina->id;
									$extra->save();
									if($nominaemployee->salary->first()->extra_hours - 9 > 0)
									{
										$hours = $nominaemployee->salary->first()->extra_hours - 9;
										$extra                   = new App\BillNominaExtraHours();
										$extra->days             = ceil($hours / 8);
										$extra->hours            = $hours;
										$extra->amount           = $nominaemployee->salary->first()->sd / 8 * 3 * $hours;
										$extra->cat_type_hour_id = '02';
										$extra->bill_nomina_id   = $billNomina->id;
										$extra->save();
									}
								}
							}
							if($nominaemployee->salary->first()->holiday != '' && $nominaemployee->salary->first()->holiday > 0)
							{
								$per                 = new App\BillNominaPerception();
								$per->type           = '001';
								$per->perceptionKey  = '001';
								$per->concept        = 'Día festivo';
								$per->taxedAmount    = round($nominaemployee->salary->first()->holiday_taxed,2);
								$per->exemptAmount   = round($nominaemployee->salary->first()->holiday - $nominaemployee->salary->first()->holiday_taxed,2);
								$per->bill_nomina_id = $billNomina->id;
								$per->save();
								$perceptions         += $per->taxedAmount;
								$perceptions         += $per->exemptAmount;
							}
							if($nominaemployee->salary->first()->sundays > 0)
							{
								$per                 = new App\BillNominaPerception();
								$per->type           = '020';
								$per->perceptionKey  = '020';
								$per->concept        = 'Prima dominical';
								$per->taxedAmount    = round($nominaemployee->salary->first()->taxed_sunday,2);
								$per->exemptAmount   = round($nominaemployee->salary->first()->exempt_sunday,2);
								$per->bill_nomina_id = $billNomina->id;
								$per->save();
								$perceptions         += $per->taxedAmount;
								$perceptions         += $per->exemptAmount;
							}
							if($billReceiver->regime_id == '02')
							{
								$otr					= new App\BillNominaOtherPayment();
								$otr->type				= '002';
								$otr->otherPaymentKey	= '002';
								$otr->concept			= 'Subsidio';
								if($nominaemployee->salary->first()->subsidy == '')
								{
									$otr->amount			= 0;
									$otr->subsidy_caused	= 0;
								}
								else
								{
									$otr->amount			= round($nominaemployee->salary->first()->subsidy,2);
									$otr->subsidy_caused	= round($nominaemployee->salary->first()->subsidyCaused,2);
								}
								$otr->bill_nomina_id	= $billNomina->id;
								$otr->save();
								$other_payments			+= $otr->amount;
							}
							if($nominaemployee->salary->first()->imss != '' && $nominaemployee->salary->first()->imss > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '001';
								$ded->deductionKey		= '001';
								$ded->concept			= 'IMSS';
								$ded->amount			= round($nominaemployee->salary->first()->imss,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->infonavit > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '009';
								$ded->deductionKey		= '009';
								$ded->concept			= 'INFONAVIT';
								$ded->amount			= round($nominaemployee->salary->first()->infonavit,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->fonacot > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '011';
								$ded->deductionKey		= '011';
								$ded->concept			= 'FONACOT';
								$ded->amount			= round($nominaemployee->salary->first()->fonacot,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->loan_retention != '' && $nominaemployee->salary->first()->loan_retention > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '004';
								$ded->deductionKey		= '004';
								$ded->concept			= 'Préstamo';
								$ded->amount			= round($nominaemployee->salary->first()->loan_retention,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->other_retention_amount != '' && $nominaemployee->salary->first()->other_retention_concept != '')
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '004';
								$ded->deductionKey		= '004';
								$ded->concept			= $nominaemployee->salary->first()->other_retention_concept;
								$ded->amount			= round($nominaemployee->salary->first()->other_retention_amount,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->isrRetentions > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '002';
								$ded->deductionKey		= '002';
								$ded->concept			= 'ISR';
								$ded->amount			= round($nominaemployee->salary->first()->isrRetentions,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->salary->first()->alimony > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '007';
								$ded->deductionKey		= '007';
								$ded->concept			= 'Pensión alimenticia';
								$ded->amount			= round($nominaemployee->salary->first()->alimony,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							break;

						case '002':
							$billReceiver->periodicity		= '99';
							$billReceiver->save();
							$billNomina->type				= 'E';
							$billNomina->paymentDate		= $t_payment->paymentDate;
							$billNomina->paymentStartDate	= $t_payment->paymentDate;
							$billNomina->paymentEndDate		= $t_payment->paymentDate;
							$billNomina->paymentDays		= 1;
							$billNomina->save();
							$per							= new App\BillNominaPerception();
							$per->type						= '002';
							$per->perceptionKey				= '002';
							$per->concept					= 'Aguinaldo';
							$per->taxedAmount				= round($nominaemployee->bonus->first()->taxableBonus,2);
							$per->exemptAmount				= round($nominaemployee->bonus->first()->exemptBonus,2);
							$per->bill_nomina_id			= $billNomina->id;
							$per->save();
							$perceptions					+= $per->taxedAmount;
							$perceptions					+= $per->exemptAmount;
							if($nominaemployee->bonus->first()->isr > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '002';
								$ded->deductionKey		= '002';
								$ded->concept			= 'ISR';
								$ded->amount			= round($nominaemployee->bonus->first()->isr,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->bonus->first()->alimony > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '007';
								$ded->deductionKey		= '007';
								$ded->concept			= 'Pensión alimenticia';
								$ded->amount			= round($nominaemployee->bonus->first()->alimony,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($billReceiver->regime_id == '02')
							{
								$otr					= new App\BillNominaOtherPayment();
								$otr->type				= '002';
								$otr->otherPaymentKey	= '002';
								$otr->concept			= 'Subsidio';
								$otr->amount			= 0;
								$otr->subsidy_caused	= 0;
								$otr->bill_nomina_id	= $billNomina->id;
								$otr->save();
								$other_payments			+= $otr->amount;
							}
							break;

						case '003':
						case '004':
							$billReceiver->periodicity			= '99';
							$billReceiver->save();
							$billReceiver->regime_id			= '13';
							$billReceiver->contractType_id		= '99';
							$billReceiver->save();
							$billNomina->type					= 'E';
							$billNomina->paymentDate			= $t_payment->paymentDate;
							$billNomina->paymentStartDate		= $t_payment->paymentDate;
							$billNomina->paymentEndDate			= $t_payment->paymentDate;
							$billNomina->paymentDays			= 1;
							$billNomina->save();
							if($nominaemployee->liquidation->first()->liquidationSalary > 0)
							{
								$per                 = new App\BillNominaPerception();
								$per->type           = '025';
								$per->perceptionKey  = '025';
								$per->concept        = 'Sueldo por liquidación';
								$per->taxedAmount    = round($nominaemployee->liquidation->first()->liquidationSalary,2);
								$per->exemptAmount   = 0;
								$per->bill_nomina_id = $billNomina->id;
								$per->save();
								$perceptions         += $per->taxedAmount;
							}
							if($nominaemployee->liquidation->first()->seniorityPremium > 0)
							{
								$per                 = new App\BillNominaPerception();
								$per->type           = '022';
								$per->perceptionKey  = '022';
								$per->concept        = 'Prima de antigüedad';
								$per->taxedAmount    = round($nominaemployee->liquidation->first()->seniorityPremium,2);
								$per->exemptAmount   = 0;
								$per->bill_nomina_id = $billNomina->id;
								$per->save();
								$perceptions         += $per->taxedAmount;
							}
							if(($nominaemployee->liquidation->first()->taxedCompensation + $nominaemployee->liquidation->first()->exemptCompensation) > 0)
							{
								$per								= new App\BillNominaPerception();
								$per->type							= '025';
								$per->perceptionKey					= '025';
								$per->concept						= 'Indemnización';
								$per->taxedAmount					= round($nominaemployee->liquidation->first()->taxedCompensation,2);
								$per->exemptAmount					= round($nominaemployee->liquidation->first()->exemptCompensation,2);
								$per->bill_nomina_id				= $billNomina->id;
								$per->save();
								$perceptions						+= $per->taxedAmount;
								$perceptions						+= $per->exemptAmount;
							}
							if($nominaemployee->liquidation->first()->holidays > 0)
							{
								$per								= new App\BillNominaPerception();
								$per->type							= '001';
								$per->perceptionKey					= '001';
								$per->concept						= 'Vacaciones';
								$per->taxedAmount					= round($nominaemployee->liquidation->first()->holidays,2);
								$per->exemptAmount					= 0;
								$per->bill_nomina_id				= $billNomina->id;
								$per->save();
								$perceptions						+= $per->taxedAmount;
							}
							if(($nominaemployee->liquidation->first()->taxableBonus + $nominaemployee->liquidation->first()->exemptBonus) > 0)
							{
								$per								= new App\BillNominaPerception();
								$per->type							= '002';
								$per->perceptionKey					= '002';
								$per->concept						= 'Aguinaldo';
								$per->taxedAmount					= round($nominaemployee->liquidation->first()->taxableBonus,2);
								$per->exemptAmount					= round($nominaemployee->liquidation->first()->exemptBonus,2);
								$per->bill_nomina_id				= $billNomina->id;
								$per->save();
								$perceptions						+= $per->taxedAmount;
								$perceptions						+= $per->exemptAmount;
							}
							if(($nominaemployee->liquidation->first()->holidayPremiumTaxed + $nominaemployee->liquidation->first()->holidayPremiumExempt) > 0)
							{
								$per								= new App\BillNominaPerception();
								$per->type							= '021';
								$per->perceptionKey					= '021';
								$per->concept						= 'Prima vacacional';
								$per->taxedAmount					= round($nominaemployee->liquidation->first()->holidayPremiumTaxed,2);
								$per->exemptAmount					= round($nominaemployee->liquidation->first()->holidayPremiumExempt,2);
								$per->bill_nomina_id				= $billNomina->id;
								$per->save();
								$perceptions						+= $per->taxedAmount;
								$perceptions						+= $per->exemptAmount;
							}
							if($nominaemployee->liquidation->first()->otherPerception > 0)
							{
								$per							= new App\BillNominaPerception();
								$per->type						= '038';
								$per->perceptionKey				= '038';
								$per->concept					= 'Otras percepciones';
								$per->taxedAmount				= round($nominaemployee->liquidation->first()->otherPerception,2);
								$per->exemptAmount				= 0;
								$per->bill_nomina_id			= $billNomina->id;
								$per->save();
								$perceptions					+= $per->taxedAmount;
							}
							if($nominaemployee->liquidation->first()->isr > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '002';
								$ded->deductionKey		= '002';
								$ded->concept			= 'ISR';
								$ded->amount			= round($nominaemployee->liquidation->first()->isr,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->liquidation->first()->alimony > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '007';
								$ded->deductionKey		= '007';
								$ded->concept			= 'Pensión alimenticia';
								$ded->amount			= round($nominaemployee->liquidation->first()->alimony,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->liquidation->first()->other_retention > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '004';
								$ded->deductionKey		= '004';
								$ded->concept			= 'Otros';
								$ded->amount			= round($nominaemployee->liquidation->first()->other_retention,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							$bill            = App\Bill::find($bill->idBill);
							$indemnification = round($bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('taxedAmount') + $bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('exemptAmount'),2);
							if($indemnification > 0)
							{
								$ind                               = new App\BillNominaIndemnification();
								$ind->total_paid                   = round($indemnification,2);
								$ind->service_year                 = $nominaemployee->liquidation->first()->fullYears;
								$ind->last_ordinary_monthly_salary = round($nominaemployee->liquidation->first()->sd * 30,2);
								$ind->cumulative_income            = $bill->nomina->nominaPerception->whereIn('type',['022','023','025'])->sum('taxedAmount');
								$nonCumulative                     = round($ind->cumulative_income - $ind->last_ordinary_monthly_salary,2);
								$nonCumulative                     = ($nonCumulative < 0 ? 0 : $nonCumulative);
								$ind->non_cumulative_income        = $nonCumulative;
								$ind->bill_nomina_id               = $billNomina->id;
								$ind->save();
							}
							break;
						case '005':
							$billReceiver->periodicity		= '99';
							$billReceiver->save();
							$billNomina->type				= 'E';
							$billNomina->paymentDate		= $t_payment->paymentDate;
							$billNomina->paymentStartDate	= $t_payment->paymentDate;
							$billNomina->paymentEndDate		= $t_payment->paymentDate;
							$billNomina->paymentDays		= 1;
							$billNomina->save();
							$per							= new App\BillNominaPerception();
							$per->type						= '021';
							$per->perceptionKey				= '021';
							$per->concept					= 'Prima vacacional';
							$per->taxedAmount				= round($nominaemployee->vacationPremium->first()->holidayPremiumTaxed,2);
							$per->exemptAmount				= round($nominaemployee->vacationPremium->first()->exemptHolidayPremium,2);
							$per->bill_nomina_id			= $billNomina->id;
							$per->save();
							$perceptions					+= $per->taxedAmount;
							$perceptions					+= $per->exemptAmount;
							if($nominaemployee->vacationPremium->first()->isr > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '002';
								$ded->deductionKey		= '002';
								$ded->concept			= 'ISR';
								$ded->amount			= round($nominaemployee->vacationPremium->first()->isr,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->vacationPremium->first()->alimony > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '007';
								$ded->deductionKey		= '007';
								$ded->concept			= 'Pensión alimenticia';
								$ded->amount			= round($nominaemployee->vacationPremium->first()->alimony,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($billReceiver->regime_id == '02')
							{
								$otr					= new App\BillNominaOtherPayment();
								$otr->type				= '002';
								$otr->otherPaymentKey	= '002';
								$otr->concept			= 'Subsidio';
								$otr->amount			= 0;
								$otr->subsidy_caused	= 0;
								$otr->bill_nomina_id	= $billNomina->id;
								$otr->save();
								$other_payments			+= $otr->amount;
							}
							break;

						case '006':
							$billReceiver->periodicity		= '99';
							$billReceiver->save();
							$billNomina->type				= 'E';
							$billNomina->paymentDate		= $t_payment->paymentDate;
							$billNomina->paymentStartDate	= $t_payment->paymentDate;
							$billNomina->paymentEndDate		= $t_payment->paymentDate;
							$billNomina->paymentDays		= 1;
							$billNomina->save();
							$per							= new App\BillNominaPerception();
							$per->type						= '003';
							$per->perceptionKey				= '003';
							$per->concept					= 'Reparto de utilidades';
							$per->taxedAmount				= round($nominaemployee->profitSharing->first()->taxedPtu,2);
							$per->exemptAmount				= round($nominaemployee->profitSharing->first()->exemptPtu,2);
							$per->bill_nomina_id			= $billNomina->id;
							$per->save();
							$perceptions					+= $per->taxedAmount;
							$perceptions					+= $per->exemptAmount;
							if($nominaemployee->profitSharing->first()->isrRetentions > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '002';
								$ded->deductionKey		= '002';
								$ded->concept			= 'ISR';
								$ded->amount			= round($nominaemployee->profitSharing->first()->isrRetentions,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}
							if($nominaemployee->profitSharing->first()->alimony > 0)
							{
								$ded					= new App\BillNominaDeduction();
								$ded->type				= '007';
								$ded->deductionKey		= '007';
								$ded->concept			= 'Pensión alimenticia';
								$ded->amount			= round($nominaemployee->profitSharing->first()->alimony,2);
								$ded->bill_nomina_id	= $billNomina->id;
								$ded->save();
								$deductions				+= $ded->amount;
							}

							if($billReceiver->regime_id == '02')
							{
								$otr					= new App\BillNominaOtherPayment();
								$otr->type				= '002';
								$otr->otherPaymentKey	= '002';
								$otr->concept			= 'Subsidio';
								$otr->amount			= 0;
								$otr->subsidy_caused	= 0;
								$otr->bill_nomina_id	= $billNomina->id;
								$otr->save();
								$other_payments			+= $otr->amount;
							}
							break;
					}
					$billNomina->perceptions	= $perceptions;
					$billNomina->deductions		= $deductions;
					$billNomina->other_payments	= $other_payments;
					$billNomina->save();
					$billDetail					= new App\BillDetail();
					$billDetail->keyProdServ	= '84111505';
					$billDetail->keyUnit		= 'ACT';
					$billDetail->quantity		= 1;
					$billDetail->description	= 'Pago de nómina';
					$billDetail->value			= round(($perceptions + $other_payments),2);
					$billDetail->amount			= $billDetail->value;
					$billDetail->discount		= round($deductions,2);
					$billDetail->idBill			= $bill->idBill;
					if($bill->version == '4.0')
					{
						$billDetail->cat_tax_object_id = '01';
					}
					$billDetail->save();
					$bill->status				= 0;
					$bill->subtotal				= $billDetail->amount;
					$bill->discount				= $billDetail->discount;
					$bill->total				= $bill->subtotal - $bill->discount;
					$bill->save();
					$nominaemployee->nominaCFDI()->attach($bill->idBill);
				}
			}
			elseif(App\RequestModel::find(App\Nomina::find($idnomina)->idFolio)->taxPayment == 0 && round($request->amount+($totalPaidEmployee-$oldAmount),2) == round($totalPayment,2))
			{
				if(!$nominaemployee->nominasEmployeeNF->first()->payroll_receipt()->exists())
				{
					$receiptName = App\Nomina::find($idnomina)->idFolio.'_nf_'.Str::uuid();
					$pdf         = PDF::loadView('administracion.nomina.receipts.payroll_nf',['payment' => $t_payment]);
					\Storage::disk('reserved')->put('/receipts/'.$receiptName.'.pdf',$pdf->stream());
					$pdfFile = '/receipts/'.$receiptName.'.pdf';
					$receipt = new App\PayrollReceipt;
					$receipt->idnominaemployeenf = $nominaemployee->nominasEmployeeNF->first()->idnominaemployeenf;
					$receipt->path = $pdfFile;
					$receipt->save();
				}
			}

			$idpayment                     = $t_payment->idpayment;

			if ($request->realPath != "") 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$documents 				= new App\DocumentsPayments();
					$new_file_name = Files::rename($request->realPath[$i],$request->idfolio);
					$documents->path 		= $new_file_name;
					$documents->idpayment 	= $idpayment;
					$documents->save();
				}
			}

			if (isset($request->deleteDoc) && $request->deleteDoc != null) 
			{
				for ($i=0; $i < count($request->deleteDoc); $i++) 
				{ 
					$deleteDoc 			= App\DocumentsPayments::find($request->deleteDoc[$i]);
					\Storage::disk('public')->delete('/docs/payments/'.$deleteDoc->path);
					$deleteDoc->delete();
				}
			}

			$req			= App\RequestModel::find($request->idfolio);
			$resta			= 0;
			$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount') : 0;
			$total			= $req->nominasReal->first()->amount;

			$resta = round($total,2)-round($totalPagado,2);
			if($resta == 0)
			{
				$payUpdate			= App\RequestModel::find($request->idfolio);
				$payUpdate->status	= 10;
				$payUpdate->payment	= 1;
				$payUpdate->save();   
			}
			else
			{
				$payUpdate			= App\RequestModel::find($request->idfolio);
				$payUpdate->status	= 12;
				$payUpdate->payment	= 0;
				$payUpdate->save();   
			}   

			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return searchRedirect(91, $alert, 'administration/payments/edit');//AA
		}
	}

	public function updatePayment(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$date		= \DateTime::createFromFormat('d-m-Y', $request->paymentDate);
			$newformat	= $date->format('Y-m-d');
			$data		= App\Module::find($this->module_id);

			if ($request->exchange_rate != "") 
			{
				$exchange_rate = $request->exchange_rate;
			}
			else
			{
				$exchange_rate = 1;
			}

			$t_payment								= App\Payment::find($id);
			$t_payment->account						= $request->account;
			$t_payment->paymentDate					= $newformat;
			$t_payment->elaborateDate				= Carbon::now();
			$t_payment->idFolio						= $request->idfolio;
			$t_payment->idKind						= $request->idkind;
			$t_payment->idRequest					= Auth::user()->id;
			$t_payment->idEnterprise				= $request->enterprise_id;
			$t_payment->commentaries				= $request->commentaries;
			$t_payment->exchange_rate				= $exchange_rate;
			$t_payment->exchange_rate_description	= $request->exchange_rate_description;

			if ($request->amount>$request->amountRes+$t_payment->amount) 
			{
				$alert = "swal('', 'El pago no se pudo realizar, debido a que el importe es mayor a lo que se adeuda.', 'error');";
				return redirect()->route('payments.showpayment',['id'=>$id])->with('alert',$alert);
			}
			$t_payment->amount						= $request->amount * $exchange_rate;
			$t_payment->subtotal					= $request->subtotalRes * $exchange_rate;
			$t_payment->iva							= $request->ivaRes * $exchange_rate;
			$t_payment->amount_real					= $request->amount;
			$t_payment->subtotal_real				= $request->subtotalRes;
			$t_payment->iva_real					= $request->ivaRes;

			$t_payment->save();


			$idpayment 					= $t_payment->idpayment;

			if ($request->realPath != "") 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$documents 				= new App\DocumentsPayments();
					$new_file_name = Files::rename($request->realPath[$i],$request->idfolio);
					$documents->path 		= $new_file_name;
					$documents->idpayment 	= $idpayment;
					$documents->save();
				}
			}

			if (isset($request->deleteDoc) && $request->deleteDoc != null) 
			{
				for ($i=0; $i < count($request->deleteDoc); $i++) 
				{ 
					$deleteDoc 			= App\DocumentsPayments::find($request->deleteDoc[$i]);
					\Storage::disk('public')->delete('/docs/payments/'.$deleteDoc->path);
					$deleteDoc->delete();
				}
			}

			$req			= App\RequestModel::find($request->idfolio);
			$resta			= 0;
			$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;

			switch ($request->idkind) 
			{
				// purchase request
				case 1:
					$total = $req->purchases->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;
				// nomina request
				case 2:
					$total = $req->nominas->first()->amount;
					$resta =round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				// expenses request
				case 3:
					$resta = 0;
					if($req->expenses->first()->reembolso>0)
					{
						$total = $req->expenses->first()->reembolso;
					}
					elseif($req->expenses->first()->reintegro>0)
					{
						$total = $req->expenses->first()->reintegro;
					}
					else
					{
						$total = 0;
					}

					$resta = round($total,2)-round($totalPagado,2);

					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				// loan request
				case 5:
					$total = $req->loan->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				// resource request
				case 8:
					$total = $req->resource->first()->total;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				case 9:
					$total = $req->refunds->first()->total;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				case 11:
					$total = $req->adjustment->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;
					
				case 12:
					$total = $req->loanEnterprise->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				case 13:
					$total = $req->purchaseEnterprise->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				case 14:
					$total = $req->groups->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				case 15:
					$total = $req->movementsEnterprise->first()->amount;
					$resta = round($total,2)-round($totalPagado,2);
					if($resta == 0)
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 10;
						$payUpdate->payment	= 1;
						$payUpdate->save();   
					}
					else
					{
						$payUpdate			= App\RequestModel::find($request->idfolio);
						$payUpdate->status	= 12;
						$payUpdate->payment	= 0;
						$payUpdate->save();   
					}
					break;

				default:
					break;
			} 

			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return searchRedirect(91, $alert, 'administration/payments/edit'); //AA
		}
		else
		{
			return abort(404);
		}
	}

	public function movement()
	{
		if(Auth::user()->module->where('id',144)->count()>0)
		{
			$data = App\Module::find(144);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 144
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementMassive(Request $request)
	{
		if(Auth::user()->module->where('id',186)->count()>0)
		{
			$data 	= App\Module::find(144);
			return view('administracion.pagos.movimientos_alta_masiva',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' 	=> 144,
					'option_id'	=> 186
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementMassiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id',186)->count()>0)
		{
			if($request->file('csv_file')->isValid())
			{
				$name		= '/massive_movements/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
				if ($request->file('csv_file')->getClientOriginalExtension() == 'csv') 
				{
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first	= true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first		= false;
							}
							$csvArr[]	= $data;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use ($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);
					$data			= App\Module::find(144);
					return view('administracion.pagos.modal.verificar_masivo_movimientos',
						[
							'id'			=> $data['father'],
							'title'			=> $data['name'],
							'details'		=> $data['details'],
							'child_id'		=> 144,
							'option_id'		=> 186,
							'csv'			=> $csvArr,
							'fileName'		=> $name,
							'separator'		=> $request->separator,
							'enterprise'	=> $request->enterprise,
							'type'			=> $request->type,
							'account'		=> $request->account
						]);
				}
				else
				{
						$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
						// "swal('', 'El archivo que está cargando no tiene la extensión CSV, intente de nuevo por favor.', 'error');";
						return back()->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.fail_charge_file")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return abort(404);
		}
	}
	
	public function movementMassiveContinue(Request $request)
	{
		if(Auth::user()->module->where('id',186)->count()>0)
		{
			$path		= \Storage::disk('reserved')->path($request->fileName);
			$csvArr		= array();
			if (($handle = fopen($path, "r")) !== FALSE)
			{
				$first	= true;
				while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
				{
					if($first)
					{
						for ($i=0; $i < count($data); $i++) 
						{ 
							$data[$i]	= Str::slug($data[$i],'_');
						}
						$first		= false;
					}
					$csvArr[]	= $data;
				}
				fclose($handle);
			}
			array_walk($csvArr, function(&$a) use ($csvArr)
			{
				$a = array_combine($csvArr[0], $a);
			});
			array_shift($csvArr);
			$updatedEmployee	= array();
			$savedEmployee		= array();
			$errorEmployee		= array();
			$arrayIdMovement 	= array();
			$arrayReplace 		= [',','$'];
			$errors = 0;
			foreach ($csvArr as $key => $e)
			{
				$t_movement					= new App\Movement();
				try 
				{
					if (empty(trim($e[$request->date])))
					{
						$newdate = null;
					}
					else
					{
						$date		= \DateTime::createFromFormat($request->date_format, $e[$request->date]);
						if ($date) 
						{
							$newdate = $date->format('Y-m-d');
						}
						else
						{
							$newdate = null;
						}
					}
					if ($newdate != null) 
					{
						$t_movement->movementDate	= $newdate;
						$t_movement->amount			= empty(trim($e[$request->amount])) ? null : str_replace($arrayReplace,'',$e[$request->amount]);
						$t_movement->description	= empty(trim($e[$request->description])) ? null : $e[$request->description];
						$t_movement->idEnterprise	= $request->enterprise;
						$t_movement->idAccount		= $request->account;
						$t_movement->movementType	= $request->type;
						$t_movement->creator		= Auth::user()->id;
						$t_movement->save();
					}
					$arrayIdMovement[] = $t_movement->idmovement;
				} 
				catch (Exception $e) 
				{
					$errors++;
				}
				catch (\Throwable $e)
				{
					$alert = "swal('No se registró ningún movimiento', 'Por favor verifique la información del archivo cargado y que la relación de los datos del sistema con los del archivo sea la correcta.', 'error');";
					return redirect()->route('payments.movement-massive')->with('alert',$alert);
				}
			}
			$movements	= App\Movement::whereIn('idmovement',$arrayIdMovement)->get();
			if ($errors > 0) 
			{
				$alert = "swal('', 'Algunos movimientos no fueron guardados debido a que no presentaban el formato correcto.', 'info');";
			}
			else
			{
				$alert = "swal('', 'Movimientos registrados', 'success');";
			}

			if (count($movements) == 0) 
			{
				$alert = "swal('', 'No se registró ningún movimiento, por favor verifique la información del archivo cargado.', 'error');";
			}

			$data		= App\Module::find(144);
			return view('administracion.pagos.partial.check_movements',
			[
				'alert'		=> $alert,
				'movements'	=> $movements,
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> 144,
				'option_id'	=> 186,
			]);
		}
		else
		{
			return abort(404);
		}
	}
	
	public function movementMassiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',186)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('payments.movement-massive');
		}
		else
		{
			return abort(404);
		}
	}

	public function movementCreate()
	{
		if(Auth::user()->module->where('id',92)->count()>0)
		{
			$data 	= App\Module::find(144);
			return view('administracion.pagos.movimientos_alta',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' 	=> 144,
					'option_id'	=> 92
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementStore(Request $request)
	{
		
		if(Auth::user()->module->where('id',144)->count()>0)
		{
			for ($i=0; $i < count($request->date); $i++) 
			{
				$date						= $request->date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->date[$i])->format('Y-m-d') : null;
				$t_movement					= new App\Movement();
				$t_movement->movementDate	= $date;
				$t_movement->amount			= $request->amount[$i];
				$t_movement->description	= $request->description[$i];
				$t_movement->commentaries	= $request->commentaries[$i];
				$t_movement->idEnterprise	= $request->enterpriseid[$i];
				$t_movement->idAccount		= $request->accountid[$i];
				$t_movement->movementType	= $request->type[$i];
				$t_movement->creator		= Auth::user()->id;
				$t_movement->save();
			}  
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return redirect('administration/payments/movements')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementEdit(Request $request)
	{
		if (Auth::user()->module->where('id',102)->count()>0) 
		{
			$data			= App\Module::find(144);
			$mov			= $request->mov;
			$account		= $request->account;
			$enterpriseid	= $request->enterpriseid;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			
			$movements 	= App\Movement::whereNotNull('idmovement')
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(102)->pluck('enterprise_id'))
							->where('statusConciliation',0)
							->where(function($query) use ($mindate,$maxdate,$mov,$enterpriseid,$account)
							{
								if ($mov != "") 
								{
									$query->where('description','LIKE','%'.$mov.'%');
								}
								if ($enterpriseid != "") 
								{
									$query->where('idEnterprise',$enterpriseid);
								}
								if ($account != "") 
								{
									$query->where('idAccount',$account);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('movementDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('idmovement','DESC')
							->paginate(10);
			return view('administracion.pagos.movimientos_buscar',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> 144,
						'option_id'		=> 102,
						'movements'		=> $movements,
						'enterpriseid' 	=> $enterpriseid,
						'account'		=> $account,
						'mov'			=> $mov,
						'mindate'		=> $mindate,
						'maxdate'		=> $maxdate,
					]
				);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementShow(Request $request,$id)
	{
		if(Auth::user()->module->where('id',102)->count()>0)
		{
			$data 		= App\Module::find(144);
			$movement  	= App\Movement::find($id);

			return view('administracion.pagos.movimientos_editar',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> 144,
						'option_id'	=> 102,
						'movement'	=> $movement
					]
				);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementView(Request $request,$id)
	{
		if(Auth::user()->module->where('id',102)->count()>0)
		{
			$data 		= App\Module::find(144);
			$movement  	= App\Movement::find($id);

			return view('administracion.pagos.movimientos_ver',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 144,
					'option_id'	=> 102,
					'movement'	=> $movement
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function movementUpdate(Request $request,$id)
	{
		if (Auth::user()->module->where('id',144)->count()>0) 
		{
			$date						= \DateTime::createFromFormat('d-m-Y', $request->movementDate);
			$newdate					= $date->format('Y-m-d');
			$movement 					= App\Movement::find($id);
			$movement->idEnterprise 	= $request->enterpriseid;
			$movement->idAccount 		= $request->accountid;
			$movement->description 		= $request->description;
			$movement->amount 			= $request->amount;
			$movement->commentaries 	= $request->commentaries;
			$movement->movementDate 	= $newdate;
			$movement->movementType 	= $request->type;
			$movement->creator 			= Auth::user()->id;
			$movement->save();

			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return redirect('administration/payments/movements')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliation()
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$data = App\Module::find(145);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 145
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationCreate()
	{ 
		if(Auth::user()->module->where('id',93)->count()>0) 
		{ 
			$data  = App\Module::find(145); 
			return view('administracion.pagos.conciliacion_tipo',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 145,
					'option_id'	=> 93
				]); 
		} 
		else 
		{ 
			return abort(404); 
		} 
	}

	public function conciliationNominaCreate()
	{ 
		if(Auth::user()->module->where('id',93)->count()>0) 
		{ 
			$data  = App\Module::find(145); 
			return view('administracion.pagos.conciliacion_nomina',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 145,
					'option_id'	=> 93
				]); 
		} 
		else 
		{ 
			return abort(404); 
		} 
	}

	public function conciliationNormalCreate()
	{ 
		if(Auth::user()->module->where('id',93)->count()>0) 
		{ 
			$data  = App\Module::find(145); 
			return view('administracion.pagos.conciliacion_normal',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 145,
					'option_id'	=> 93
				]); 
		} 
		else 
		{ 
			return abort(404); 
		} 
	}

	public function conciliationSearch(Request $request)
	{
		if($request->ajax())
		{
			$payment           = "";
			$movement          = "";
			$kindSortPayments  = $request->kindSort == "" ? "amount": $request->kindSort;
			$kindSortMovements = $request->kindSort == "" ? "amount": $request->kindSort;
			$ascDescPayments   = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			$ascDescMovements  = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			switch($kindSortPayments)
			{
				case "kind":
					$kindSortPayments = "idKind";
					break;
				
				case "enterprise":
					$kindSortPayments = "enterprises.name";
					break;

				case "account":
					$kindSortPayments = DB::raw('CONCAT_WS(" ",accounts.account,accounts.description)');
					break;
				
				case "amount":
					$kindSortPayments = "amount";
					break;
				
				case "date":
					$kindSortPayments = "paymentDate";
					break;
				
				case "description":
					$kindSortPayments = DB::raw('CONCAT_WS(" ",request_kinds.kind,payments.idFolio)');
					break;
			}

			switch($kindSortMovements)
			{
				case "kind":
					$kindSortMovements = "movements.movementType";
					break;
				
				case "enterprise":
					$kindSortMovements = "enterprises.name";
					break;

				case "account":
					$kindSortMovements = DB::raw('CONCAT_WS(" ",accounts.account,accounts.description)');
					break;
				
				case "amount":
					$kindSortMovements = "movements.amount";
					break;
				
				case "date":
					$kindSortMovements = "movements.movementDate";
					break;
				
				case "description":
					$kindSortMovements = 'movements.description';
					break;
			}

			$paymentsAll	= App\Payment::select('payments.*')
				->join('enterprises','payments.idEnterprise','enterprises.id')
				->join('accounts','payments.account','accounts.idAccAcc')
				->join('request_kinds','payments.idKind','request_kinds.idrequestkind')
				->where('statusConciliation',0)
				->where('idKind','!=',16)
				->whereHas('request',function($query)
				{
					$query->whereIn('idDepartment',Auth::user()->inChargeDep(93)->pluck('departament_id'))->orWhere('idDepartment',null);
				})
				->whereIn('payments.idEnterprise',Auth::user()->inChargeEnt(93)->pluck('enterprise_id'))
				->where(function($query) use($request)
				{
					if($request->selected != '')
					{
						$selected = explode(',', $request->selected);
						$query->whereNotIn('payments.idpayment',$selected);
					}
					if($request->search != '')
					{
						$query->whereRaw('CONCAT("Solicitud de ",request_kinds.kind," #",payments.idFolio) LIKE "%'.$request->search.'%"');
					}
					if($request->year == 'all' && $request->month != 'all')
					{
						$query->whereMonth('paymentDate',$request->month);
					}

					if($request->year != 'all' && $request->month != 'all') 
					{
						$query->whereMonth('paymentDate',$request->month)->whereYear('paymentDate',$request->year);
					}

					if ($request->year != 'all' && $request->month == 'all') 
					{
						$query->whereYear('paymentDate',$request->year);
					}
				})
				->orderBy($kindSortPayments,$ascDescPayments)
				->paginate(5);

			if($request->selected != "")
			{
				$selected = explode(',', $request->selected);
				$selectedPayment = App\Payment::select('payments.*')
					->join('enterprises','payments.idEnterprise','enterprises.id')
					->join('accounts','payments.account','accounts.idAccAcc')
					->join('request_kinds','payments.idKind','request_kinds.idrequestkind')
					->whereIn('idpayment',$selected)
					->orderBy($kindSortPayments,$ascDescPayments)
					->get();
			}
			else
			{
				$selectedPayment = "";	
			}

			$movementsAll	= App\Movement::select('movements.*')
				->join('enterprises','movements.idEnterprise','enterprises.id')
				->join('accounts','movements.idAccount','accounts.idAccAcc')
				->where('statusConciliation',0)
				->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(93)->pluck('enterprise_id'))
				->where(function($query) use($request)
				{
					if($request->selected != '')
					{
						$selected = explode(',', $request->selected);
						$query->whereNotIn('movements.idmovement', $selected);
					}
					if($request->search != '')
					{
						$query->where('movements.description','LIKE','%'.$request->search.'%');
					}
					if($request->year == 'all' && $request->month != 'all') 
					{
						$query->whereMonth('movementDate',$request->month);
					}
					if($request->year != 'all' && $request->month != 'all') 
					{
						$query->whereMonth('movementDate',$request->month)->whereYear('movementDate',$request->year);
					}
					if ($request->year != 'all' & $request->month == 'all') 
					{
						$query->whereYear('movementDate',$request->year);
					}
				})
				->orderBy($kindSortMovements,$ascDescMovements)
				->paginate(5);

			if($request->selected != "")
			{
				$selected = explode(',', $request->selected);
				$selectedMovement = App\Movement::select('movements.*')
					->join('enterprises','movements.idEnterprise','enterprises.id')
					->join('accounts','movements.idAccount','accounts.idAccAcc')
					->whereIn('idmovement',$selected)
					->orderBy($kindSortMovements,$ascDescMovements)
					->get();
			}
			else
			{
				$selectedMovement = "";	
			}
			
			$payment = view('administracion.pagos.partial.conciliacion_normal_pagos',['paymentsAll'=>$paymentsAll, 'selectedPayment'=>$selectedPayment]);
			$movement = view('administracion.pagos.partial.conciliacion_normal_movimientos',['movementsAll'=>$movementsAll, 'selectedMovement'=>$selectedMovement]);
			return json_encode([urlencode($payment),urlencode($movement)]);
		}
	}

	public function conciliationStore(Request $request)
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$t_payment						= App\Payment::find($request->idpayment);
			$t_payment->statusConciliation	= 1;
			$t_payment->save();

			for ($i=0; $i < count($request->idmovement); $i++) 
			{ 
				$t_movement						= App\Movement::find($request->idmovement[$i]);
				$t_movement->idpayment			= $request->idpayment;
				$t_movement->conciliationDate	= Carbon::now();
				$t_movement->statusConciliation	= 1;
				$t_movement->save();
			}

			$req			= App\RequestModel::find($request->idFolio);
			if ($req->status == 12) 
			{
				$req->status = 18;
			}
			if ($req->status == 10)
			{
				$req->status = 11;
			}
			$req->save();

			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return redirect('administration/payments/conciliation')->with('alert',$alert);

		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationNominaSearch(Request $request)
	{
		if($request->ajax())
		{
			$payment		   = "";
			$movement		   = "";
			$kindSortPayments  = $request->kindSort == "" ? "amount": $request->kindSort;
			$kindSortMovements = $request->kindSort == "" ? "amount": $request->kindSort;
			$ascDescPayments   = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			$ascDescMovements  = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;

			switch($kindSortPayments)
			{
				case "folio":
					$kindSortPayments = "idFolio";
					break;
				
				case "enterprise":
					$kindSortPayments = "enterprises.name";
					break;

				case "employee":
					$kindSortPayments = DB::raw('CONCAT_WS(" ",real_employees.name,real_employees.last_name,real_employees.scnd_last_name)');
					break;
				
				case "kind":
					$kindSortPayments = "request_models.taxPayment";
					break;
				
				case "amount":
					$kindSortPayments = "amount";
					break;
				
				case "date":
					$kindSortPayments = "paymentDate";
					break;
				
				default:
					$kindSortPayments = "amount";
					break;
			}

			switch($kindSortMovements)
			{
				case "enterprise":
					$kindSortMovements = "enterprises.name";
					break;
				
				case "account":
					$kindSortMovements = DB::raw('CONCAT_WS(" ",accounts.account,accounts.description)');
					break;

				case "amount":
					$kindSortMovements = "amount";
					break;
				
				case "date":
					$kindSortMovements = "movementDate";
					break;
				
				case "description":
					$kindSortMovements = "description";
					break;
				
				case "kind":
					$kindSortMovements = "movementType";
					break;
				
				default:
					$kindSortMovements = "amount";
					break;
			}

			$paymentsAll	= App\Payment::select('payments.*')
							->join('enterprises','payments.idEnterprise','enterprises.id')
							->join('accounts','payments.account','accounts.idAccAcc')
							->join('request_models','payments.idFolio','request_models.folio')
							->join('nomina_employees','payments.idnominaEmployee','nomina_employees.idnominaEmployee')
							->join('real_employees','nomina_employees.idrealEmployee','real_employees.id')
							->where('statusConciliation',0)
							->whereHas('request',function($query)
							{
								$query->whereIn('idDepartamentR',Auth::user()->inChargeDep(93)->pluck('departament_id'))->orWhere('idDepartamentR',null);
							})
							->where('idKind',16)
							->whereIn('payments.idEnterprise',Auth::user()->inChargeEnt(93)->pluck('enterprise_id'))
							->where(function($query) use($request)
							{
								if($request->selected != '')
								{
									$selected = explode(',', $request->selected);
									$query->whereNotIn('payments.idpayment',$selected);
								}
								if($request->search != '')
								{
									$query->whereRaw('CONCAT("",real_employees.name," ",real_employees.last_name," ",real_employees.scnd_last_name) LIKE "%'.$request->search.'%"');
								}
								if($request->year == 'all' && $request->month != 'all')
								{
									$query->whereMonth('payments.paymentDate',$request->month);
								}

								if($request->year != 'all' && $request->month != 'all') 
								{
									$query->whereMonth('payments.paymentDate',$request->month)->whereYear('payments.paymentDate',$request->year);
								}

								if ($request->year != 'all' & $request->month == 'all') 
								{
									$query->whereYear('payments.paymentDate',$request->year);
								}
							})
							->orderBy($kindSortPayments,$ascDescPayments)
							->paginate(5);
			
			if($request->selected != "")
			{
				$selected = explode(',', $request->selected);
				$selectedPayment = App\Payment::select('payments.*')
					->join('enterprises','payments.idEnterprise','enterprises.id')
					->join('accounts','payments.account','accounts.idAccAcc')
					->join('request_models','payments.idFolio','request_models.folio')
					->join('nomina_employees','payments.idnominaEmployee','nomina_employees.idnominaEmployee')
					->join('real_employees','nomina_employees.idrealEmployee','real_employees.id')
					->whereIn('idpayment',$selected)
					->orderBy($kindSortPayments,$ascDescPayments)
					->get();
			}
			else
			{
				$selectedPayment = "";	
			}							

			$movementsAll	= App\Movement::select('movements.*')
							->join('enterprises','movements.idEnterprise','enterprises.id')
							->join('accounts','movements.idAccount','accounts.idAccAcc')
							->where('statusConciliation',0)
							->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(93)->pluck('enterprise_id'))
							->where(function($query) use($request)
							{
								if($request->selected != '')
								{
									$selected = explode(',', $request->selected);
									$query->whereNotIn('movements.idmovement',$selected);
								}
								if($request->search != '')
								{
									$query->where('movements.description','LIKE','%'.$request->search.'%');
								}
								if($request->year == 'all' && $request->month != 'all') 
								{
									$query->whereMonth('movementDate',$request->month);
								}

								if($request->year != 'all' && $request->month != 'all') 
								{
									$query->whereMonth('movementDate',$request->month)->whereYear('movementDate',$request->year);
								}

								if ($request->year != 'all' & $request->month == 'all') 
								{
									$query->whereYear('movementDate',$request->year);
								}
							})
							->orderBy($kindSortMovements,$ascDescMovements)
							->paginate(5);
			
			if($request->selected != "")
			{
				$selected = explode(',', $request->selected);
				$selectedMovement = App\Movement::select('movements.*')
					->join('enterprises','movements.idEnterprise','enterprises.id')
					->join('accounts','movements.idAccount','accounts.idAccAcc')
					->whereIn('idmovement',$selected)
					->orderBy($kindSortMovements,$ascDescMovements)
					->get();
			}
			else
			{
				$selectedMovement = "";
			}
			$payment = view('administracion.pagos.partial.conciliacion_nomina_pagos',['paymentsAll'=>$paymentsAll, 'selectedPayment'=>$selectedPayment]);
			$movement = view('administracion.pagos.partial.conciliacion_nomina_movimientos',['movementsAll'=>$movementsAll, 'selectedMovement'=>$selectedMovement]);
			return json_encode([urlencode($payment),urlencode($movement)]);
		}
	}

	public function conciliationStoreNomina(Request $request)
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$t_payment						= App\Movement::find($request->idmovement_nomina);
			$t_payment->statusConciliation	= 1;
			$t_payment->save();

			for ($i=0; $i < count($request->idpayment_nomina); $i++) 
			{ 
				$t_payment						= App\Payment::find($request->idpayment_nomina[$i]);
				$t_payment->idmovement			= $request->idmovement_nomina;
				$t_payment->conciliationDate	= Carbon::now();
				$t_payment->statusConciliation	= 1;
				$t_payment->save();

				$folio = $t_payment->idFolio;
			}

			$requestNomina = App\RequestModel::find($request->idFolio_nomina);
			if ($requestNomina->nominasReal->first()->nominaEmployee->where('visible',1)->where('payment',0)->count()==0) 
			{
				$req			= App\RequestModel::find($request->idFolio_nomina);
				$req->status	= 11;
				$req->save();
			}

			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return redirect('administration/payments/conciliation')->with('alert',$alert);

		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationView(Request $request)
	{
		if (Auth::user()->module->where('id',103)->count()>0) 
		{
			$data			= App\Module::find(145);
			
			return view('administracion.pagos.conciliacion_busqueda_tipo',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> 145,
					'option_id'		=> 103,
				]);
		}
	}

	public function conciliationEdit(Request $request)
	{
		if (Auth::user()->module->where('id',103)->count()>0) 
		{
			$data			= App\Module::find(145);
			$enterpriseid	= $request->enterpriseid;
			$mov			= $request->mov;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$account		= $request->account;

			$movements = App\Movement::join('payments','payments.idpayment','=','movements.idpayment')
						->join('request_models','payments.idFolio','=','request_models.folio')
						->select('movements.idmovement', 'movements.amount', 'movements.idEnterprise', 'movements.idAccount', 'movements.description','movements.conciliationDate','payments.idFolio','payments.amount as amount_pay','payments.remittance as remittance')
						->where('movements.statusConciliation',1)
						->where(function($query)
						{
							$query->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(103)->pluck('departament_id'))
								->orWhere('request_models.idDepartamentR',null);
						})
						->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(103)->pluck('enterprise_id'))
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
						->paginate(10);
			return view('administracion.pagos.busqueda_conciliacion_normal',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> 145,
					'option_id'		=> 103,
					'movements'		=> $movements,
					'account'		=> $account,
					'mov'			=> $mov,
					'mindate'		=> $mindate,
					'maxdate'		=> $maxdate,
					'enterpriseid'	=> $enterpriseid,
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationUpdate($id)
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$movement 						= App\Movement::find($id);
			$idpayment 						= $movement->idpayment;
			
			$payment						= App\Payment::find($idpayment);
			$payment->statusConciliation	= 0;
			$idFolio						= $payment->idFolio;
			
			$request						= App\RequestModel::find($idFolio);
			$request->status				= 10;
			$request->save();

			$payment->save();
			
			$movement->statusConciliation     = 0;
			$movement->idpayment 			= null;
			$movement->conciliationDate 	= null;
			$movement->save();

			$alert = "swal('','".Lang::get("messages.record_deleted")."', 'success')";
			return redirect('administration/payments/conciliation/edit')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$movement	= App\Movement::find($request->idmovement);
			$idpayment	= $movement->idpayment;
			
			$payment	= App\Payment::find($idpayment);
			$folio		= $payment->idFolio;
			
			$req		= App\RequestModel::find($folio);

			switch ($req->kind) 
			{
				case 1:
					return view('administracion.pagos.modal.search_conciliation_normal.compra')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;

				case 2:
					return view('administracion.pagos.modal.search_conciliation_normal.complemento_nomina')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;

				case 3:
					return view('administracion.pagos.modal.search_conciliation_normal.comprobacion_de_gasto')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;

				case 5:
					return view('administracion.pagos.modal.search_conciliation_normal.prestamo_personal')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;

				case 8:
					return view('administracion.pagos.modal.search_conciliation_normal.asignacion_de_recurso')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;
				
				case 9:
					return view('administracion.pagos.modal.search_conciliation_normal.reembolso')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
					break;

			 	case 12:
					return view('administracion.pagos.modal.search_conciliation_normal.prestamo_inter_empresa')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;

			 	case 13:
					return view('administracion.pagos.modal.search_conciliation_normal.compra_inter_empresa')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;

			 	case 14:
					return view('administracion.pagos.modal.search_conciliation_normal.grupos')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;

			 	case 15:
					return view('administracion.pagos.modal.search_conciliation_normal.movimiento_empresa')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;

				case 17:
					return view('administracion.pagos.modal.search_conciliation_normal.registro_compra')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;

			 	case 18:
					return view('administracion.pagos.modal.search_conciliation_normal.gasto_financiero')->with(['payment'=>$payment, 'movement'=>$movement, 'req'=>$req]);
			 		break;		
			}
		}
	}

	public function conciliationNominaEdit(Request $request)
	{
		if (Auth::user()->module->where('id',103)->count()>0) 
		{
			$data			= App\Module::find(145);
			$enterpriseid	= $request->enterpriseid;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$account		= $request->account;
			$folio 			= $request->folio;
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
		    
			return view('administracion.pagos.busqueda_conciliacion_nomina',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> 145,
					'option_id'		=> 103,
					'account'		=> $account,
					'mindate'		=> $mindate,
					'maxdate'		=> $maxdate,
					'enterpriseid'	=> $enterpriseid,
					'payments'		=> $payments,
					'folio'			=> $folio
				]);
		}
	}

	public function conciliationNominaUpdate($id)
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$payment						= App\Payment::find($id);
			$idmovement						= $payment->idmovement;
			$payment->statusConciliation	= 0;
			$payment->idmovement			= null;
			$payment->conciliationDate		= null;
			$payment->save();
			
			
			$movement						= App\Movement::find($idmovement);
			$movement->statusConciliation	= 0;
			$movement->save();
			
			$idFolio						= $payment->idFolio;
			$request						= App\RequestModel::find($idFolio);
			$request->status				= 10;
			$request->save();

			$alert = "swal('','".Lang::get("messages.record_deleted")."', 'success')";
			return redirect('administration/payments/conciliation/edit')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationNominaDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$payment	= App\Payment::find($request->idpayment);
			$folio		= $payment->idFolio;
			
			$req		= App\RequestModel::find($folio);
			$movement	= App\Movement::find($payment->idmovement);
			
			return view('administracion.pagos.modal.modal-nomina')->with(['req'=>$req,'payment'=>$payment,'movement'=>$movement]);

		}
	}

	public function validation(Request $request)
	{
		if($request->oldCode == $request->code)
		{
			$response = array('valid' => true);
		}
		else if($request->code == '')
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo es obligatorio.'
			);
		}
		else
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El código es incorrecto.'
			);
		}
		return Response($response);
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/payments/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_paymentDocument.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/payments/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData	= file_get_contents($request->path);
						$resultData	= \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= $extention;
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']	= $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']	= 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']	= 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					try
					{
						$myTask = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::disk('public')->put('/docs/uncompressed_pdf/'.$name,\File::get($request->path));
						$file = $myTask->addFile(public_path().'/docs/uncompressed_pdf/'.$name);
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($nameWithoutExtention);
						$myTask->download(public_path().'/docs/compressed_pdf');
						\Storage::disk('public')->move('/docs/compressed_pdf/'.$name,$destinity);
						\Storage::disk('public')->delete(['/docs/uncompressed_pdf/'.$name,'/docs/compressed_pdf/'.$name]);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console']	= $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function requestDetail(Request $request)
	{
		if (Auth::user()->module->where('id',93)->count() > 0) 
		{
			if ($request->ajax())
			{
				$request  = App\RequestModel::find($request->folio);

				switch ($request->kind) 
				{
					case 1:
						return view('administracion.pagos.modal.compra',['request'=>$request]);
						break;

					case 2:
						return view('administracion.pagos.modal.complemento_nomina',['request'=>$request]);
						break;

					case 3:
						return view('administracion.pagos.modal.comprobacion_de_gasto',['request'=>$request]);
						break;

					case 5:
						return view('administracion.pagos.modal.prestamo_personal',['request'=>$request]);
						break;

					case 8:
						return view('administracion.pagos.modal.asignacion_de_recurso',['request'=>$request]);
						break;
					
					case 9:
						return view('administracion.pagos.modal.reembolso',['request'=>$request]);
						break;

					case 11:
						return view('administracion.pagos.modal.ajuste_movimientos',['request'=>$request]);
						break;
						
					case 12:
						return view('administracion.pagos.modal.prestamo_empresa',['request'=>$request]);
						break;
					
					case 13:
						return view('administracion.pagos.modal.compra_empresa',['request'=>$request]);
						break;
					
					case 14:
						return view('administracion.pagos.modal.grupos',['request'=>$request]);
						break;
					
					case 15:
						return view('administracion.pagos.modal.movimiento_empresa',['request'=>$request]);
						break;
			
					case 16:
						return view('reporte.administracion.partial.vernomina',['request'=>$request]);
						break;	

					case 17:
						return view('administracion.pagos.modal.registro_compra',['request'=>$request]);
						break;
					
					case 18:
						return view('administracion.pagos.modal.gasto_financiero',['request'=>$request]);
						break;

					default:
						# code...
						break;
				} 
			}
		}
	}

	public function movementDelete($id)
	{
		if(Auth::user()->module->where('id',144)->count()>0)
		{
			App\Movement::find($id)->delete();

			$alert = "swal('','".Lang::get("messages.record_deleted")."', 'success')";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function updateMassive(Request $request)
	{
		if (Auth::user()->module->where('id',144)->count()>0) 
		{
			for ($i=0; $i < count($request->idmovement); $i++) 
			{ 
				$movement 				= App\Movement::find($request->idmovement[$i]);
				$movement->movementDate = $request->movementDate[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->movementDate[$i])->format('Y-m-d') : null;
				$movement->description 	= $request->description[$i];
				$movement->amount 		= $request->amount[$i];
				$movement->movementType = $request->movementType[$i];
				$movement->save();
			}
			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return redirect('administration/payments/movements/edit')->with('alert',$alert);
		}
	}

	public function movementDeleteMassive(Request $request)
	{
		if($request->ajax())
		{
			$movement = App\Movement::find($request->id)->delete();

			return Response('Movimiento Eliminado');
		}
	}

	public function paymentDelete(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$payment 	= App\Payment::find($id);
			if($payment != "")
			{
				$folio 		= $payment->idFolio;
				$kind 		= $payment->idKind;
	
				if ($payment->statusConciliation == 1) 
				{
					$alert = "swal('','El pago no se puede eliminar debido a que ya fue conciliado.','error');";
				}
				else
				{
					if (App\PartialPayment::where('payment_id', $id)->count() > 0)
					{
						App\PartialPayment::where('payment_id', $id)->update(
						[
							'date_delivery'	=> null,
							'payment_id'	=> null
						]);
					}

					$docs = App\DocumentsPayments::where('idpayment',$id)->count() > 0 ? App\DocumentsPayments::where('idpayment',$id)->delete() : '';
					$payment->delete();
	
					$req            = App\RequestModel::find($folio);
					$resta          = 0;
					$totalPagado    = 0;
					$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
					switch ($kind) 
					{
						// purchase request
						case 1:
							$total = $req->purchases->first()->amount;
	
							$resta = round($total,2)-round($totalPagado,2);
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
						// nomina request
						case 2:
							$total = $req->nominas->first()->amount;
							
							$resta = round($total,2)-round($totalPagado,2);
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
	
						// expenses request
						case 3:
							$resta = 0;
							$totalPagado = 0;
							if($req->expenses->first()->reembolso>0)
							{
								$total = $req->expenses->first()->reembolso;
							}
							elseif($req->expenses->first()->reintegro>0)
							{
								$total = $req->expenses->first()->reintegro;
							}
							else
							{
								$total = 0;
							}
	
							
	
							$resta = round($total,2)-round($totalPagado,2);
	
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
	
						// loan request
						case 5:
							$total = $req->loan->first()->amount;
							
							$resta = round($total,2)-round($totalPagado,2);
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
	
						// resource request
						case 8:
							$total = $req->resource->first()->total;
							
							$resta = round($total,2)-round($totalPagado,2);
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
							}						break;
	
						case 9:
							$total = $req->refunds->first()->total;
							
							$resta = round($total,2)-round($totalPagado,2);
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
	
						case 11:
							$total			= $req->adjustment->first()->amount;
							$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta			= round($total,2)-round($totalPagado,2);
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
								
						case 12:
							$total			= $req->loanEnterprise->first()->amount;
							$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta			= round($total,2)-round($totalPagado,2);
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
	
						case 13:
							$total			= $req->purchaseEnterprise->first()->amount;
							$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta			= round($total,2)-round($totalPagado,2);
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
							
						case 14:
							$total			= $req->groups->first()->amount;
							$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta			= round($total,2)-round($totalPagado,2);
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
	
						case 15:
							$total			= $req->movementsEnterprise->first()->amount;
							$totalPagado	= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta			= round($total,2)-round($totalPagado,2);
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
	
						case 16:
							$total						= $req->nominasReal->first()->amount;
							$totalPagado				= $req->paymentsRequest()->exists() ? $req->paymentsRequest->sum('amount_real') : 0;
							$resta						= round($total,2)-round($totalPagado,2);
	
							$nominaemployee				= App\NominaEmployee::find($request->idnominaEmployee);
							$nominaemployee->payment	= 0;
							$nominaemployee->save();
							if($req->taxPayment == 1)
							{
								if($nominaemployee->nominaCFDI->count() > 0 && $nominaemployee->nominaCFDI->first()->status == 0)
								{
									$bill			= $nominaemployee->nominaCFDI->first();
									$bill->status	= 8;
									$bill->save();
								}
							}
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
	
	
					$alert = "swal('','".Lang::get("messages.record_deleted")."', 'success')";
				}
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.record_previously_deleted")."', 'error')";
			}
			return searchRedirect(91, $alert, 'administration/payments/edit');
		}
		else
		{
			return abort(404);
		}
	}

	public function show($id)
	{
		return abort(404);
	}

	public function edit($id)
	{
		return abort(404);
	}

	public function update(Request $request, $id)
	{
		return abort(404);
	}

	public function conciliationIncome()
	{
		if(Auth::user()->module->where('id',191)->count()>0)
		{
			$data = App\Module::find(191);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 191
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationIncomeCreate()
	{ 
		if(Auth::user()->module->where('id',191)->count()>0) 
		{ 
			$data  = App\Module::find(191); 
			return view('administracion.pagos.conciliacion_ingresos',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> 191,
				'option_id'	=> 192
			]); 
		} 
		else 
		{ 
			return abort(404); 
		} 
	}

	public function conciliationIncomeSearch(Request $request)
	{
		if($request->ajax())
		{
			$payment           = "";
			$movement          = "";
			$kindSortPayments  = $request->kindSort == "" ? "idBill": $request->kindSort;
			$kindSortMovements = $request->kindSort == "" ? "amount": $request->kindSort;
			$ascDescPayments   = $request->ascDesc  == "" ? "DESC"   : $request->ascDesc;
			$ascDescMovements  = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			$selectedBF		   = [];
			$selectedBNF	   = [];
			
			if($request->table != 'all')
			{
				if($request->table == 'payments')
				{
					$kindSortMovements = "amount";
				}
				if($request->table == 'movements')
				{
					$kindSortPayments  = "idBill";
				}
			}

			switch($kindSortPayments)
			{
				case "enterprise":
					$kindSortPayments = "a.businessName";
					break;
				
				case "client":
					$kindSortPayments = "a.clientBusinessName";
					break;

				case "amount":
					$kindSortPayments = "a.total";
					break;
				
				case "date":
					$kindSortPayments = "a.expeditionDate";
					break;
				
				case "request":
					$kindSortPayments = DB::raw('CONCAT_WS("Solicitud de ",kind," #",folioRequest)');
					break;

				case "folio":
					$kindSortPayments = "a.folio";
					break;

				case "serie":
					$kindSortPayments = "a.serie";
					break;
			}

			switch($kindSortMovements)
			{
				case "kind":
					$kindSortMovements = "movements.movementType";
					break;
				
				case "enterprise":
					$kindSortMovements = "enterprises.name";
					break;

				case "account":
					$kindSortMovements = DB::raw('CONCAT_WS(" ",accounts.account,accounts.description)');
					break;
				
				case "amount":
					$kindSortMovements = "movements.amount";
					break;
				
				case "date":
					$kindSortMovements = "movements.movementDate";
					break;
				
				case "description":
					$kindSortMovements = 'movements.description';
					break;
			}

			if($request->type != "")
			{
				if($request->selected != "")
				{
					$selected = explode(',', $request->selected);
					$type     = explode(',', $request->type);
					for ($i=0; $i < count($type); $i++)
					{
						//billF es type 1 & billNF es type 2
						if($type[$i] == 1)
						{
							$selectedBF[] = $selected[$i];
						}
						if($type[$i] == 2)
						{
							$selectedBNF[] = $selected[$i];
						}
					}
					
					$selectedbillF = App\Bill::selectRaw(
										'bills.idBill AS idBill,
										bills.rfc AS rfc,
										bills.businessName AS businessName,
										bills.taxRegime AS taxRegime,
										bills.clientRfc AS clientRfc,
										bills.clientBusinessName AS clientBusinessName,
										bills.receiver_tax_regime AS receiver_tax_regime,
										bills.receiver_zip_code AS receiver_zip_code,
										bills.uuid AS uuid,
										bills.noCertificate AS noCertificate,
										bills.satCertificateNo AS satCertificateNo,
										DATE_FORMAT(bills.expeditionDate, "%d-%m-%Y") AS expeditionDate,
										bills.expeditionDateCFDI AS expeditionDateCFDI,
										bills.stampDate AS stampDate,
										bills.cancelRequestDate AS cancelRequestDate,
										bills.CancelledDate AS CancelledDate,
										bills.cancellation_reason AS cancellation_reason,
										bills.substitute_folio AS substitute_folio,
										bills.postalCode AS postalCode,
										bills.export AS export,
										bills.serie AS serie,
										bills.folio AS folio,
										bills.conditions AS conditions,
										bills.status AS status,
										bills.statusCFDI AS statusCFDI,
										bills.statusCancelCFDI AS statusCancelCFDI,
										bills.subtotal AS subtotal, 
										bills.discount AS discount,
										bills.tras AS tras,
										bills.ret AS ret,
										bills.total AS total,
										bills.related AS related,
										bills.originalChain AS originalChain,
										bills.digitalStampCFDI AS digitalStampCFDI,
										bills.digitalStampSAT AS digitalStampSAT,
										bills.signatureValueCancel AS signatureValueCancel,
										bills.type AS type,
										bills.paymentMethod AS paymentMethod,
										bills.paymentWay AS paymentWay,
										bills.currency AS currency,
										bills.exchange AS exchange,
										bills.useBill AS useBill,
										bills.error AS error,
										bills.folio AS folioRequest,
										bills.statusConciliation AS statusConciliation,
										bills.idProject AS idProject,
										bills.issuer_address AS issuer_address,
										bills.receiver_address AS receiver_address,
										bills.version AS version,
										"BILLF" AS kindBill,
										request_kinds.kind as kind')
										->join('request_models','bills.folioRequest','request_models.folio')
										->join('request_kinds','request_models.kind','request_kinds.idrequestkind')
										->whereIn('bills.idBill',$selectedBF);

					$selectedbillNF = App\NonFiscalBill::selectRaw(
										'non_fiscal_bills.idBill AS idBill,
										non_fiscal_bills.rfc AS rfc,
										non_fiscal_bills.businessName AS businessName,
										"" AS taxRegime,
										non_fiscal_bills.clientRfc AS clientRfc,
										non_fiscal_bills.clientBusinessName AS clientBusinessName,
										"" AS receiver_tax_regime,
										"" AS receiver_zip_code,
										"" AS uuid,
										"" AS noCertificate,
										"" AS satCertificateNo,
										DATE_FORMAT(non_fiscal_bills.expeditionDate, "%d-%m-%Y") AS expeditionDate,
										"" AS expeditionDateCFDI,
										"" AS stampDate,
										"" AS cancelRequestDate,
										"" AS CancelledDate,
										"" AS cancellation_reason,
										"" AS substitute_folio,
										"" AS postalCode,
										"" AS export,
										"" AS serie,
										"" AS folio,
										non_fiscal_bills.conditions AS conditions,
										non_fiscal_bills.status AS status,
										"" AS statusCFDI,
										"" AS statusCancelCFDI,
										non_fiscal_bills.subtotal AS subtotal, 
										non_fiscal_bills.discount AS discount,
										"" AS tras,
										"" AS ret,
										non_fiscal_bills.total AS total,
										"" AS related,
										"" AS originalChain,
										"" AS digitalStampCFDI,
										"" AS digitalStampSAT,
										"" AS signatureValueCancel,
										"" AS type,
										non_fiscal_bills.paymentMethod AS paymentMethod,
										non_fiscal_bills.paymentWay AS paymentWay,
										non_fiscal_bills.currency AS currency,
										"" AS exchange,
										"" AS useBill,
										"" AS error,
										non_fiscal_bills.folio AS folioRequest,
										non_fiscal_bills.statusConciliation AS statusConciliation,
										"" AS idProject,
										"" AS issuer_address,
										"" AS receiver_address,
										"" AS version,
										"BILLNF" AS kindBill,
										request_kinds.kind as kind')
										->join('request_models','non_fiscal_bills.folio','request_models.folio')
										->join('request_kinds','request_models.kind','request_kinds.idrequestkind')
										->whereIn('non_fiscal_bills.idBill', $selectedBNF);

					$billSelectedQueries	= $selectedbillF->union($selectedbillNF)->getQuery();
					$billSelectedSql 		= $billSelectedQueries->toSql();	
					$selectedBill 			= DB::table(DB::raw("($billSelectedSql) as a order by $kindSortPayments $ascDescPayments"))->mergeBindings($billSelectedQueries)->get();
				}
				else
				{
					$selectedBill = "";	
				}				
			}
			else
			{
				$selectedBill = "";	
			}
			
			$billF = App\Bill::selectRaw(
						'bills.idBill AS idBill,
						bills.rfc AS rfc,
						bills.businessName AS businessName,
						bills.taxRegime AS taxRegime,
						bills.clientRfc AS clientRfc,
						bills.clientBusinessName AS clientBusinessName,
						bills.receiver_tax_regime AS receiver_tax_regime,
						bills.receiver_zip_code AS receiver_zip_code,
						bills.uuid AS uuid,
						bills.noCertificate AS noCertificate,
						bills.satCertificateNo AS satCertificateNo,
						DATE_FORMAT(bills.expeditionDate, "%d-%m-%Y") AS expeditionDate,
						bills.expeditionDateCFDI AS expeditionDateCFDI,
						bills.stampDate AS stampDate,
						bills.cancelRequestDate AS cancelRequestDate,
						bills.CancelledDate AS CancelledDate,
						bills.cancellation_reason AS cancellation_reason,
						bills.substitute_folio AS substitute_folio,
						bills.postalCode AS postalCode,
						bills.export AS export,
						bills.serie AS serie,
						bills.folio AS folio,
						bills.conditions AS conditions,
						bills.status AS status,
						bills.statusCFDI AS statusCFDI,
						bills.statusCancelCFDI AS statusCancelCFDI,
						bills.subtotal AS subtotal, 
						bills.discount AS discount,
						bills.tras AS tras,
						bills.ret AS ret,
						bills.total AS total,
						bills.related AS related,
						bills.originalChain AS originalChain,
						bills.digitalStampCFDI AS digitalStampCFDI,
						bills.digitalStampSAT AS digitalStampSAT,
						bills.signatureValueCancel AS signatureValueCancel,
						bills.type AS type,
						bills.paymentMethod AS paymentMethod,
						bills.paymentWay AS paymentWay,
						bills.currency AS currency,
						bills.exchange AS exchange,
						bills.useBill AS useBill,
						bills.error AS error,
						bills.folio AS folioRequest,
						bills.statusConciliation AS statusConciliation,
						bills.idProject AS idProject,
						bills.issuer_address AS issuer_address,
						bills.receiver_address AS receiver_address,
						bills.version AS version,
						"BILLF" AS kindBill,
						request_kinds.kind as kind')
						->join('request_models','bills.folioRequest','request_models.folio')
						->join('request_kinds','request_models.kind','request_kinds.idrequestkind')
						->where('bills.type','I')
						->where('bills.status',1)
						->whereNotNull('bills.folioRequest')
						->where('bills.statusConciliation',0)
						->where(function($query) use($request, $selectedBF)
						{
							if(count($selectedBF) > 0)
							{
								$query->whereNotIn('bills.idBill',$selectedBF);
							}
							if($request->search != '')
							{
								$query->where('bills.clientBusinessName','LIKE','%'.$request->search.'%');
							}
							if($request->year == 'all' && $request->month != 'all') 
							{
								$query->whereMonth('bills.expeditionDate',$request->month);
							}

							if($request->year != 'all' && $request->month != 'all') 
							{
								$query->whereMonth('bills.expeditionDate',$request->month)->whereYear('bills.expeditionDate',$request->year);
							}

							if ($request->year != 'all' & $request->month == 'all') 
							{
								$query->whereYear('bills.expeditionDate',$request->year);
							}
						});
						
			$billNF = App\NonFiscalBill::selectRaw(
						'non_fiscal_bills.idBill AS idBill,
						non_fiscal_bills.rfc AS rfc,
						non_fiscal_bills.businessName AS businessName,
						"" AS taxRegime,
						non_fiscal_bills.clientRfc AS clientRfc,
						non_fiscal_bills.clientBusinessName AS clientBusinessName,
						"" AS receiver_tax_regime,
						"" AS receiver_zip_code,
						"" AS uuid,
						"" AS noCertificate,
						"" AS satCertificateNo,
						DATE_FORMAT(non_fiscal_bills.expeditionDate, "%d-%m-%Y") AS expeditionDate,
						"" AS expeditionDateCFDI,
						"" AS stampDate,
						"" AS cancelRequestDate,
						"" AS CancelledDate,
						"" AS cancellation_reason,
						"" AS substitute_folio,
						"" AS postalCode,
						"" AS export,
						"" AS serie,
						"" AS folio,
						non_fiscal_bills.conditions AS conditions,
						non_fiscal_bills.status AS status,
						"" AS statusCFDI,
						"" AS statusCancelCFDI,
						non_fiscal_bills.subtotal AS subtotal, 
						non_fiscal_bills.discount AS discount,
						"" AS tras,
						"" AS ret,
						non_fiscal_bills.total AS total,
						"" AS related,
						"" AS originalChain,
						"" AS digitalStampCFDI,
						"" AS digitalStampSAT,
						"" AS signatureValueCancel,
						"" AS type,
						non_fiscal_bills.paymentMethod AS paymentMethod,
						non_fiscal_bills.paymentWay AS paymentWay,
						non_fiscal_bills.currency AS currency,
						"" AS exchange,
						"" AS useBill,
						"" AS error,
						non_fiscal_bills.folio AS folioRequest,
						non_fiscal_bills.statusConciliation AS statusConciliation,
						"" AS idProject,
						"" AS issuer_address,
						"" AS receiver_address,
						"" AS version,
						"BILLNF" AS kindBill,
						request_kinds.kind as kind')
						->join('request_models','non_fiscal_bills.folio','request_models.folio')
						->join('request_kinds','request_models.kind','request_kinds.idrequestkind')
						->where('non_fiscal_bills.status',0)
						->whereNotNull('non_fiscal_bills.folio')
						->where('non_fiscal_bills.statusConciliation',0)
						->where(function($query) use($request, $selectedBNF)
						{
							if(count($selectedBNF) > 0)
							{
								$query->whereNotIn('non_fiscal_bills.idBill',$selectedBNF);
							}
							if($request->search != '')
							{
								$query->where('non_fiscal_bills.clientBusinessName','LIKE','%'.$request->search.'%');
							}
							if($request->year == 'all' && $request->month != 'all') 
							{
								$query->whereMonth('non_fiscal_bills.expeditionDate',$request->month);
							}

							if($request->year != 'all' && $request->month != 'all') 
							{
								$query->whereMonth('non_fiscal_bills.expeditionDate',$request->month)->whereYear('non_fiscal_bills.expeditionDate',$request->year);
							}

							if ($request->year != 'all' & $request->month == 'all') 
							{
								$query->whereYear('non_fiscal_bills.expeditionDate',$request->year);
							}
						});
		
			$billQueries	= $billF->union($billNF)->getQuery();
			$billSql 		= $billQueries->toSql();	
			$billUnion 		= DB::table(DB::raw("($billSql) as a order by $kindSortPayments $ascDescPayments"))->mergeBindings($billQueries)->paginate(5);

			$movementsAll	= App\Movement::select('movements.*')
				->join('enterprises','movements.idEnterprise','enterprises.id')
				->join('accounts','movements.idAccount','accounts.idAccAcc')
				->where('statusConciliation',0)
				->where(function($query)
				{
					$query->where('movementType','Ingreso')->orWhere('movementType','Devolución')->orWhereNull('movementType');
				})
				->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(93)->pluck('enterprise_id'))
				->where(function($query) use($request)
				{
					if($request->selected != '')
					{
						$selected = explode(',', $request->selected);
						$query->whereNotIn('movements.idmovement', $selected);
					}
					if($request->search != '')
					{
						$query->where('movements.description','LIKE','%'.$request->search.'%');
					}
					if($request->year == 'all' && $request->month != 'all') 
					{
						$query->whereMonth('movementDate',$request->month);
					}

					if($request->year != 'all' && $request->month != 'all') 
					{
						$query->whereMonth('movementDate',$request->month)->whereYear('movementDate',$request->year);
					}

					if ($request->year != 'all' & $request->month == 'all') 
					{
						$query->whereYear('movementDate',$request->year);
					}
				})
				->orderBy($kindSortMovements,$ascDescMovements)
				->paginate(5);

			if($request->selected != "")
			{
				$selected = explode(',', $request->selected);
				$selectedMovement = App\Movement::select('movements.*')
					->join('enterprises','movements.idEnterprise','enterprises.id')
					->join('accounts','movements.idAccount','accounts.idAccAcc')
					->whereIn('idmovement',$selected)
					->orderBy($kindSortMovements,$ascDescMovements)
					->get();
			}
			else
			{
				$selectedMovement = "";	
			}

			$payment	= view('administracion.pagos.partial.conciliacion_ingresos_pagos',['billUnion'=> $billUnion, 'selectedBill'=>$selectedBill]);
			$movement	= view('administracion.pagos.partial.conciliacion_normal_movimientos',['movementsAll'=>$movementsAll, 'selectedMovement'=>$selectedMovement]);
			// Movimientos tambien es de egresos
			return json_encode([urlencode($payment),urlencode($movement)]);
		}
	}

	public function conciliationIncomeStore(Request $request)
	{
		if(Auth::user()->module->where('id',192)->count()>0)
		{
			if ((isset($request->idmovement_multi) && count($request->idmovement_multi)>1) && (isset($request->idbill_multi) && count($request->idbill_multi)>1)) 
			{
				$alert = "swal('Error','No puede conciliar muchos movimientos con muchos movimientos bancarios.','error');";
				return redirect("administration/payments/conciliation-income/create")->with('alert',$alert);
			}

			if (isset($request->idmovement_multi) && count($request->idmovement_multi)>0 && $request->idbill_only != "") 
			{
				if ($request->type_only == 1) 
				{
					$t_bill						= App\Bill::find($request->idbill_only);
					$t_bill->status 			= 2;
					$t_bill->statusConciliation	= 1;
					$t_bill->save();
				}
				else
				{
					$t_bill						= App\NonFiscalBill::find($request->idbill_only);
					$t_bill->status 			= 2;
					$t_bill->statusConciliation	= 1;
					$t_bill->save();

					if($t_bill->requestHasBill()->exists())
					{
						$t_request = $t_bill->requestHasBill;

						if($t_request->kind == 10)
						{
							$outstandingBalance = $t_request->income->first()->amount;
							
							$outstandingBalance -= $t_request->billNF->sum('total');
							
							if($outstandingBalance <= 0)
							{
								$t_request->status = 20;
								$t_request->save();
							}
						}
					}

				}

				for ($i=0; $i < count($request->idmovement_multi); $i++) 
				{ 
					$t_movement						= App\Movement::find($request->idmovement_multi[$i]);
					$t_movement->statusConciliation	= 1;
					$t_movement->conciliationDate	= Carbon::now();
					$t_movement->save();

					$t_conciliation						= new App\ConciliationMovementBill();
					$t_conciliation->idmovement			= $request->idmovement_multi[$i];
					if ($request->type_only == 1)
					{
						$t_conciliation->idbill				= $request->idbill_only;
					}
					else
					{
						$t_conciliation->idNoFiscalBill		= $request->idbill_only;
					}
					
					$t_conciliation->type 				= $request->type_only;
					$t_conciliation->conciliationDate	= Carbon::now();
					$t_conciliation->save();
				}
			}

			if (isset($request->idbill_multi) && count($request->idbill_multi)>0 && $request->idmovement_only != "") 
			{
				$t_movement						= App\Movement::find($request->idmovement_only);
				$t_movement->statusConciliation	= 1;
				$t_movement->conciliationDate	= Carbon::now();
				$t_movement->save();

				for ($i=0; $i < count($request->idbill_multi); $i++) 
				{ 
					if ($request->type_multi[$i] == 1) 
					{
						$t_bill						= App\Bill::find($request->idbill_multi[$i]);
						$t_bill->status 			= 2;
						$t_bill->statusConciliation	= 1;
						$t_bill->save();
					}
					else
					{
						$t_bill						= App\NonFiscalBill::find($request->idbill_multi[$i]);
						$t_bill->status 			= 2;
						$t_bill->statusConciliation	= 1;
						$t_bill->save();

						if($t_bill->requestHasBill()->exists())
						{
							$t_request = $t_bill->requestHasBill;
							if($t_request->kind == 10)
							{
								$outstandingBalance = $t_request->income->first()->amount;
								
								$outstandingBalance -= $t_request->billNF->sum('total');

								if($outstandingBalance <= 0)
								{
									$t_request->status = 20;
									$t_request->save();
								}
							}
						}

					}

					$t_conciliation						= new App\ConciliationMovementBill();
					$t_conciliation->idmovement			= $request->idmovement_only;
					if ($request->type_multi[$i] == 1)
					{
						$t_conciliation->idbill			= $request->idbill_multi[$i];
					}
					else
					{
						$t_conciliation->idNoFiscalBill = $request->idbill_multi[$i];
					}
					$t_conciliation->type 				= $request->type_multi[$i];
					$t_conciliation->conciliationDate	= Carbon::now();
					$t_conciliation->save();
				}
			}
			// -------------------
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return redirect()->route('payments.conciliation-income.create')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationIncomeEdit(Request $request)
	{
		if (Auth::user()->module->where('id',193)->count()>0) 
		{
			$data			= App\Module::find(191);
			$idEnterprise	= $request->idEnterprise;
			$mov			= $request->mov;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$idAccount		= $request->idAccount;
			
			$conciliations 	= App\ConciliationMovementBill::whereHas('movements',function($query) use ($mov,$idEnterprise,$idAccount)
							{
								if ($mov != "") 
								{
									$query->where('description','LIKE','%'.$mov.'%');
								}
								if ($idEnterprise != "") 
								{
									$query->where('idEnterprise',$idEnterprise);
								}
								if ($idAccount != "") 
								{
									$query->where('idAccount',$idAccount);
								}
							})
							->where(function($query) use ($mindate,$maxdate)
							{
								if ($mindate != "" && $maxdate != "") 
								{
									$query->whereBetween('conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('conciliationDate','DESC')
							->paginate(10);

			return view('administracion.pagos.busqueda_conciliacion_ingresos',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> 191,
				'option_id'		=> 193,
				'conciliations'	=> $conciliations,
				'idAccount'		=> $idAccount,
				'mov'			=> $mov,
				'mindate'		=> $mindate,
				'maxdate'		=> $maxdate,
				'idEnterprise'	=> $idEnterprise,
			]);
		}
		else
		{
			return abort(404);
		}
	}

	public function conciliationIncomeUpdate(Request $request,$id)
	{
		if(Auth::user()->module->where('id',145)->count()>0)
		{
			$movementsDeleteFiscal 	= collect();
			$movementsDeleteNF 		= collect();

			if($request->type == 1)
			{
				$movementsDeleteFiscal = App\ConciliationMovementBill::select('idmovement')->where('idbill',$request->idbill)->get();
			}
			else
			{
				$movementsDeleteNF = App\ConciliationMovementBill::select('idmovement')->where('idNoFiscalBill',$request->idbill)->get();
			}

			$movementsDelete = $movementsDeleteFiscal->concat($movementsDeleteNF);			
			$billsFiscalDelete	= App\ConciliationMovementBill::select('idbill')->where('idmovement',$request->idmovement)->where('type',1)->get();
			$billsNFDelete		= App\ConciliationMovementBill::select('idNoFiscalBill')->where('idmovement',$request->idmovement)->where('type',2)->get();
			
			$movements			= App\Movement::whereIn('idmovement',$movementsDelete)->get();
			$billsFiscal		= App\Bill::whereIn('idBill',$billsFiscalDelete)->get();	
			$billsNF 			= App\NonFiscalBill::whereIn('idBill',$billsNFDelete)->get();	

			foreach ($movements as $mov) 
			{
				$movement 						= App\Movement::find($mov->idmovement);
				$movement->statusConciliation	= 0;
				$movement->conciliationDate		= null;
				$movement->save();
			}

			if ($billsFiscal != "") 
			{
				foreach ($billsFiscal as $b) 
				{
					$bill						= App\Bill::find($b->idBill);
					$bill->statusConciliation	= 0;
					$bill->status				= 1;
					$bill->save();
				}
			}

			if ($billsNF != "") 
			{
				foreach ($billsNF as $b) 
				{
					$bill						= App\NonFiscalBill::find($b->idBill);
					$bill->statusConciliation	= 0;
					$bill->status				= 0;
					$bill->save();
				}
			}
			
			App\ConciliationMovementBill::where('idmovement',$request->idmovement)->delete();

			if($request->type == 1)
			{
				App\ConciliationMovementBill::where('idbill',$request->idbill)->delete();
			}
			else
			{
				App\ConciliationMovementBill::where('idNoFiscalBill',$request->idbill)->delete();
			}

			$alert = "swal('','".Lang::get("messages.record_deleted")."', 'success')";
			return redirect('administration/payments/conciliation-income/edit')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function viewConciliation(Request $request)
	{
		if ($request->ajax()) 
		{
			if ($request->type == 1) 
			{
				$bill		= App\Bill::find($request->idbill);
			}
			else
			{
				$bill		= App\NonFiscalBill::find($request->idbill);
			}
			$movement	= App\Movement::find($request->idmovement);
			// partials.bill_details
			// return view('administracion.pagos.modal.factura_movimiento',['bill'=>$bill,'movement'=>$movement,'type'=>$request->type]);
			return view('partials.bill_details',['bill'=>$bill,'movement'=>$movement,'type'=>$request->type, 'conciliacion' => true]);
		}
	}

	public function viewPaymentDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$payment = App\Payment::find($request->idpayment);

			return view('administracion.pagos.modal.ver_pago',['payment'=>$payment]);
		}
	}

	public function viewBill(Request $request)
	{
		if ($request->ajax()) 
		{
			if ($request->type == 1) 
			{
				$bill = App\Bill::find($request->idBill);
			}
			else
			{
				$bill = App\NonFiscalBill::find($request->idBill);
			}
			return view('partials.bill_details',['bill' => $bill, 'type' => $request->type, 'conciliacion' => true]);
		}
	}

	public function exportMovement(Request $request)
	{
		if (Auth::user()->module->where('id',102)->count()>0) 
		{
			$mov			= $request->mov;
			$account		= $request->account;
			$enterpriseid	= $request->enterpriseid;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			
			$movements 	= DB::table('movements')->selectRaw(
							'
								enterprises.name as enterpriseName,
								CONCAT_WS(" - ",accounts.account, accounts.description) as accountName,
								movements.amount,
								movements.description as movementDescription,
								DATE_FORMAT(movements.movementDate, "%d-%m-%Y") as movementDate,
								movements.movementType
							')
							->leftJoin('enterprises', 'enterprises.id', 'movements.idEnterprise')
							->leftJoin('accounts', 'accounts.idAccAcc', 'movements.idAccount')
							->whereNotNull('movements.idmovement')
							->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(102)->pluck('enterprise_id'))
							->where('movements.statusConciliation',0)
							->where(function($query) use ($mindate,$maxdate,$mov,$enterpriseid,$account)
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
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('movements.movementDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('movements.idmovement','DESC')
							->get();
			if(count($movements)==0 || is_null($movements))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-registro-de-movimientos.xlsx');
			$writer->getCurrentSheet()->setName('Movimientos');

			$headers = ['Reporte de registro de movimientos','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Empresa','Cuenta','Importe','Descripción','Fecha', 'Tipo de movimiento'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($movements as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['amount']))
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
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}		
		else
		{
			return redirect('error');
		}
	}
	public function exportNormalConciliation(Request $request)
	{
		if (Auth::user()->module->where('id',103)->count()>0) 
		{
			$enterpriseid	= $request->enterpriseid;
			$mov			= $request->mov;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$account		= $request->account;
			
			$movements = DB::table('movements')
						->selectRaw(
						'
							IF(payments.remittance=1,"Remesa", "Pago") as type,
							enterprises.name as enterpiseName,
							CONCAT("Solicitud de ", request_kinds.kind, " #", request_models.folio) as movementRequest,
							movements.amount,
							movements.description,
							movements.amount as movementAmount,
							DATE_FORMAT(movements.conciliationDate, "%d-%m-%Y") as movementDate,
							CONCAT(accounts.account, " - ", accounts.description, " (",accounts.content,")") as movementAccount
						')
						->leftJoin('payments','payments.idpayment','movements.idpayment')
						->leftJoin('accounts', 'accounts.idAccAcc', 'movements.idAccount')
						->leftJoin('enterprises', 'enterprises.id', 'movements.idEnterprise')
						->leftJoin('request_models','payments.idFolio','request_models.folio')
						->leftJoin('request_kinds','request_kinds.idrequestkind','request_models.kind')
						->where('movements.statusConciliation',1)
						->where(function($query)
						{
							$query->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(103)->pluck('departament_id'))
								->orWhere('request_models.idDepartamentR',null);
						})
						->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(103)->pluck('enterprise_id'))
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

			if(count($movements)==0 || $movements==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-conciliación-de-egresos.xlsx');
			$writer->getCurrentSheet()->setName('Conciliación de egresos');

			$headers = ['Datos del Pago','','','','Datos del Movimiento','', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				if(in_array($mh,['Datos del Movimiento']))
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$subHeader    = ['Tipo', 'Empresa', 'Solicitud', 'Importe del Pago', 'Movimiento',	'Importe del Movimiento', 'Fecha de Conciliación', 'Clasificación del gasto'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if(in_array($sh,['Movimiento']))
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($movements as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['amount', 'movementAmount']))
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
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportNominaConciliation (Request $request)
	{
		if (Auth::user()->module->where('id',103)->count()>0) 
		{
			$enterpriseid	= $request->enterpriseid;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$account		= $request->account;
			$folio 			= $request->folio;

			$payments = DB::table('payments')
						->selectRaw(
						'
							payments.idFolio as folio,
							enterprises.name as enterpriseName,
							CONCAT_WS(" ",real_employees.name, real_employees.last_name, real_employees.scnd_last_name) as employeeName,
							payments.amount as paymentsAmount,
							CONCAT(accounts.account, " - ", accounts.description, " (",accounts.content,")") as movementAccount,
							movements.description as movementName,
							movements.amount as movementAmount,
							DATE_FORMAT(payments.conciliationDate, "%d-%m-%Y") as conciliationDate
						')
						->leftJoin('enterprises', 'enterprises.id', 'payments.idEnterprise')
						->leftJoin('nomina_employees', 'nomina_employees.idnominaEmployee', 'payments.idnominaEmployee')
						->leftJoin('real_employees', 'real_employees.id', 'nomina_employees.idrealEmployee')
						->leftJoin('accounts', 'accounts.idAccAcc', 'payments.account')
						->leftJoin('movements', 'movements.idmovement', 'payments.idmovement')
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
			if(count($payments)==0 || $payments==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol     = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-conciliación-de-egresos-nómina.xlsx');
			$writer->getCurrentSheet()->setName('Conciliación egresos nomina');

			$headers = ['Datos del Pago','','','','','Datos del Movimiento', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				if(in_array($mh,['Datos del Movimiento']))
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$subHeader    = ['Solicitud', 'Empresa', 'Empleado', 'Importe del pago', 'Clasificación del gasto', 'Movimiento',	'Importe del Movimiento', 'Fecha de Conciliación'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if(in_array($sh,['Movimiento']))
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($payments as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['movementAmount', 'conciliationAmount', 'paymentsAmount']))
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
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function conciliationIncomeExport(Request $request)
	{
		if (Auth::user()->module->where('id',193)->count()>0) 
		{
			$idEnterprise	= $request->idEnterprise;
			$mov			= $request->mov;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$idAccount		= $request->idAccount;

			$conciliations = DB::table('conciliation_movement_bills')->selectRaw(
							'
								conciliation_movement_bills.id as id,
								enterprises.name,
								IF(conciliation_movement_bills.type = 1, CONCAT("Solicitud de ",kindBill.kind), CONCAT("Solicitud de ",kindBillNF.kind)) as kind,
								IF(conciliation_movement_bills.type = 1, bills.total, non_fiscal_bills.total) as total,
								movements.description as description,
								movements.movementType as movementType,
								movements.amount as amount,
								DATE_FORMAT(conciliation_movement_bills.conciliationDate, "%d-%m-%Y") as conciliationDate,
								CONCAT_WS(" - ", accounts.account, accounts.description) as account
							')
							->leftJoin('movements','movements.idmovement','conciliation_movement_bills.idmovement')
							->leftJoin('enterprises','enterprises.id','movements.idEnterprise')
							->leftJoin('accounts','accounts.idAccAcc','movements.idAccount')
							->leftJoin('bills','bills.idBill','conciliation_movement_bills.idbill')
							->leftJoin('request_models as requestBill','requestBill.folio','bills.folioRequest')
							->leftJoin('request_kinds as kindBill','kindBill.idrequestkind','requestBill.kind')
							->leftJoin('non_fiscal_bills','non_fiscal_bills.idBill','conciliation_movement_bills.idNoFiscalBill')
							->leftJoin('request_models as requestBillNF','requestBillNF.folio','non_fiscal_bills.folio')
							->leftJoin('request_kinds as kindBillNF','kindBillNF.idrequestkind','requestBillNF.kind')
							->where(function($query) use ($mov,$idEnterprise,$idAccount,$mindate,$maxdate)
							{
								if ($mov != "") 
								{
									$query->where('movements.description','LIKE','%'.$mov.'%');
								}
								if ($idEnterprise != "") 
								{
									$query->where('movements.idEnterprise',$idEnterprise);
								}
								if ($idAccount != "") 
								{
									$query->where('movements.idAccount',$idAccount);
								}
								if ($mindate != "" && $maxdate != "") 
								{
									$query->whereBetween('conciliation_movement_bills.conciliationDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('conciliation_movement_bills.conciliationDate','DESC')
							->get();

						
			if(count($conciliations)==0 || $conciliations==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Conciliación-Ingresos.xlsx');
			$headers = ['Datos del Pago','','','','Datos del Movimiento','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				if($k <= 3)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Empresa','Solicitud','Importe del Pago','Movimiento','Tipo','Importe del Movimiento','Fecha de Conciliación','Clasificación del gasto'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if($k <= 3)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol1);
				}
				else
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			$num_partial   = 1;
			foreach($conciliations as $conciliation)
			{
				if($tempFolio != $conciliation->id)
				{
					$tempFolio = $conciliation->id;
					$kindRow = !$kindRow;
				}
				
				$tmpArr = [];
				foreach($conciliation as $k => $r)
				{
					if(in_array($k,['total','amount']))
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

	public function exportEditPayment(Request $request)
	{
		if(Auth::user()->module->where('id',91)->count()>0)
		{
			$account			= $request->account;
			$name				= $request->name;
			$enterpriseid		= $request->enterpriseid;
			$folio				= $request->folio;
			$kind				= $request->kind;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$idnominaEmployee	= $request->idnominaEmployee;
			$employees 			= App\NominaEmployee::select('idnominaEmployee')->where('idrealEmployee',$idnominaEmployee)->get();
			
			$payments	= DB::table('payments')
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
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(91)->pluck('departament_id'))
										->orWhere('request_models.idDepartamentR',null);
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(91)->pluck('enterprise_id'))
										->orWhere('request_models.idEnterpriseR',null);
						})
						->whereIn('request_models.kind',[1,2,3,5,8,9,11,12,13,14,15,16,17])
						->whereIn('request_models.status',[5,10,11,12,18])
						->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio,$kind,$enterpriseid,$idnominaEmployee)
						{
							if($enterpriseid != "")
							{
								$query->where('request_models.idEnterpriseR',$enterpriseid);
							}
							if($account != "")
							{
								$query->where('payments.account',$account);
							}
							if($name != "")
							{
								$query->whereRaw('CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($kind != "")
							{
								$query->where('request_models.kind',$kind);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('payments.paymentDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($idnominaEmployee != "") 
							{
								$query->where('real_employees.id',$idnominaEmployee);
							}
						})
						->orderBy('payments.paymentDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();


			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Pagos.xlsx');

			$headers		= ['Reporte de Pagos','','','','','','','',''];
			$tempHeaders	= [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders		= ['Folio','Tipo de Solicitud','Empresa','Clasificación del gasto','Fecha de pago','Concepto','Pagado a','Comentarios','Importe'];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio	= '';
			$kindRow	= true;

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
			return redirect('error');
		}	
	}

	public function partialPaymentUpdate(App\PartialPayment $partial,Request $request)
	{
		if (Auth::user()->module->where('id',90)->count()>0) 
		{
			$totalPurchase = $partial->purchase->amount;

			if ($request->form_partial_type == "0") 
			{
				$totalPartial = round(($request->form_partial_amount*$totalPurchase)/100,2);
			}
			else
			{
				$totalPartial = round($request->form_partial_amount,2);
			}

			$totalPaid = $totalPartials = 0;
			if ($partial->purchase->requestModel->paymentsRequest()->exists()) 
			{
				foreach ($partial->purchase->requestModel->paymentsRequest as $key => $payment) 
				{
					if (!$payment->partialPayments()->exists() && $payment->partial_id == "")
					{
						$totalPaid += $payment->amount;
					}
				}
			}

			if ($partial->purchase->partialPayment()->exists())
			{
				foreach ($partial->purchase->partialPayment->where('id','!=',$partial->id) as $key => $value) 
				{
					if ($value->tipe == "0") 
					{
						$totalPartials += round(($value->payment*$totalPurchase)/100,2);
					}
					else
					{
						$totalPartials += round($value->payment,2);
					}
				}
			}
			$remainingPayment = $totalPurchase - $totalPaid - $totalPartials;

			if($totalPartial > $remainingPayment)
			{
				$alert = 'swal("","El total de la parcialidad es mayor al total que se adeuda.","error");';
				return redirect()->back()->with('alert',$alert);
			}

			$data = Carbon::parse($request->form_partial_date)->format('Y-m-d');

			$partial->payment			= $request->form_partial_amount;
			$partial->tipe				= $request->form_partial_type;
			$partial->date_requested	= $data;
			$partial->save();
			
			$alert = 'swal("","Pago programado actualizado existosamente","success");';
			return redirect()->back()->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}
}
