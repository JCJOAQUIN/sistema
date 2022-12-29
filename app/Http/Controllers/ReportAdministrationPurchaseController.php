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

class ReportAdministrationPurchaseController extends Controller
{
	private $module_id = 96;
	public function purchaseReport(Request $request)
	{
		if (Auth::user()->module->where('id',97)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$account        = $request->account;
			$name           = $request->name;
			$folio          = $request->folio;
			$status         = $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid   = $request->enterpriseid;
			$documents      = $request->documents;
			$provider       = $request->provider;
			$initRange      = "";
			$endRange       = "";

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

			$requests     = App\RequestModel::where(function($q)
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(29)->pluck('enterprise_id'))
						->orWhereNull('idEnterprise');
				})
				->where('kind',1)
				->whereIn('status',[4,5,6,7,10,11,12,13,18])
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro') 
						{
							$q->whereHas('purchases',function($q)
							{
								$q->whereNotIn('billStatus',['Pendiente','Entregado','No Aplica']);
							});
						}
						else
						{
							$q->whereHas('purchases',function($q) use($documents)
							{
								$q->where('billStatus',$documents);
							});
						}
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status, $initRange, $endRange)
				{
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query2) use ($account)
						{   
							$query2->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->where(function($query) use($provider)
				{
					if($provider != "") 
					{
						$query->whereHas('purchases', function($q) use($provider)
						{
							$q->whereHas('provider',function($q) use($provider)
							{
								$q->where('businessName','LIKE','%'.$provider.'%')
									->orWhere('rfc','LIKE','%'.$provider.'%');
							});
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return view('reporte.administracion.compra',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 97,
					'requests'     => $requests,
					'account'      => $account, 
					'name'         => $name, 
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'folio'        => $folio,
					'status'       => $status,
					'enterpriseid' => $enterpriseid,
					'documents'    => $documents,
					'provider'     => $provider
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function purchaseExcel(Request $request)
	{
		if (Auth::user()->module->where('id',97)->count()>0)
		{
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;
			$documents		= $request->documents;
			$provider		= $request->provider;
			$initRange		= "";
			$endRange		= "";

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

			$requestsPurchase = DB::table('request_models')
						->selectRaw(
					'request_models.folio as folio,
					request_models.idRequisition as idRequisition,
					status_requests.description as status,
					CONCAT(purchases.title," - ",purchases.datetitle) as title,
					purchases.numberOrder as number_order,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					request_enterprise.name as request_enterprise,
					request_direction.name as request_direction,
					request_department.name as request_department,
					request_project.proyectName as request_project,
					CONCAT(request_account.account, " ", request_account.description," (",request_account.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
					review_enterprise.name as review_enterprise,
					review_direction.name as review_direction,
					review_department.name as review_department,
					review_project.proyectName as review_project,
					CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
					purchases.amount as amount,
					IF(request_models.taxPayment = 1, "Fiscal", "No Fiscal") as tax,
					purchase_provider.businessName as provider,
					purchases.reference as reference,
					purchases.paymentMode as payment_way,
					purchase_provider_bank_data.description as bank,
					purchase_provider_bank.account as bank_account,
					purchase_provider_bank.branch as branch,
					purchase_provider_bank.reference bank_reference,
					purchase_provider_bank.clabe as clabe,
					purchase_provider_bank.currency as bank_currency,
					purchase_provider_bank.agreement as bank_agreement,
					detail_purchases.quantity as detail_quantity,
					detail_purchases.unit as detail_unit,
					detail_purchases.description as detail_description,
					detail_purchases.unitPrice as detail_unit_price,
					detail_purchases.subtotal as detail_subtotal,
					detail_purchases.tax as detail_tax,
					IFNULL(taxes_purchase.taxes_amount,0) as detail_taxes,
					IFNULL(retention_purchase.retention_amount,0) as detail_retentions,
					detail_purchases.amount as detail_amount,
					dp_labels.labels as detail_labels,
					purchases.amount as total,
					purchases.typeCurrency as currency,
					IFNULL(p.payment_amount,0) as paid_amount
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
						->on('request_models.kind','=','purchases.idKind');
				})
				->leftJoin('users as request_user','idRequest','request_user.id')
				->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
				->leftJoin('enterprises as request_enterprise','request_models.idEnterprise','request_enterprise.id')
				->leftJoin('areas as request_direction','idArea','request_direction.id')
				->leftJoin('departments as request_department','idDepartment','request_department.id')
				->leftJoin('projects as request_project','idProject','request_project.idproyect')
				->leftJoin('accounts as request_account','request_models.account','request_account.idAccAcc')
				->leftJoin('users as review_user','idCheck','review_user.id')
				->leftJoin('enterprises as review_enterprise','request_models.idEnterpriseR','review_enterprise.id')
				->leftJoin('areas as review_direction','idAreaR','review_direction.id')
				->leftJoin('departments as review_department','idDepartamentR','review_department.id')
				->leftJoin('projects as review_project','idProjectR','review_project.idproyect')
				->leftJoin('accounts as review_account','request_models.accountR','review_account.idAccAcc')
				->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
				->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
				->leftJoin('provider_banks as purchase_provider_bank','purchases.provider_has_banks_id','purchase_provider_bank.id')
				->leftJoin('banks as purchase_provider_bank_data','purchase_provider_bank.banks_idBanks','purchase_provider_bank_data.idBanks')
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->where(function($q)
				{
					$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(29)->pluck('enterprise_id'))
						->orWhereNull('request_models.idEnterprise');
				})
				->where('request_models.kind',1)
				->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18])
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro')
						{
							$q->whereNotIn('purchases.billStatus',['Pendiente','Entregado','No Aplica']);
						}
						else
						{
							$q->where('purchases.billStatus',$documents);
						}
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status,$provider, $initRange, $endRange)
				{
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query2) use ($account)
						{   
							$query2->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if($provider != "") 
					{
						$query->where('purchase_provider.businessName','LIKE','%'.$provider.'%')
							->orWhere('purchase_provider.rfc','LIKE','%'.$provider.'%');
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('reporte-de-compras.xlsx');
			$mainHeaderArr = ['Datos de la solicitud','','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','Datos del proveedor','','','','','','','','','','Datos de la solicitud','','','','','','','','','','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 4)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
				elseif($k <= 19)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
				}
				elseif($k <= 21)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
				elseif($k <= 23)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				elseif($k <= 33)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['Folio','Folio de Requisición','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Monto','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Banco','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a Pagar','Moneda','Total pagado'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 4)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
				}
				elseif($k <= 19)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
				}
				elseif($k <= 21)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
				elseif($k <= 23)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				elseif($k <= 33)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requestsPurchase as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
					$request->idRequisition      = '';
					$request->status             = '';
					$request->title              = '';
					$request->number_order       = '';
					$request->request_user       = '';
					$request->elaborate_user     = '';
					$request->date               = '';
					$request->request_enterprise = '';
					$request->request_direction  = '';
					$request->request_department = '';
					$request->request_project    = '';
					$request->request_account    = '';
					$request->review_user        = '';
					$request->review_date        = '';
					$request->review_enterprise  = '';
					$request->review_direction   = '';
					$request->review_department  = '';
					$request->review_project     = '';
					$request->review_account     = '';
					$request->authorize_user     = '';
					$request->authorize_date     = '';
					$request->tax                = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->payment_way        = '';
					$request->bank               = '';
					$request->bank_account       = '';
					$request->branch             = '';
					$request->bank_reference     = '';
					$request->clabe              = '';
					$request->bank_currency      = '';
					$request->bank_agreement     = '';
					$request->amount             = '';
					$request->currency           = '';
					$request->paid_amount        = null;
					$request->total              = null;
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['amount','detail_unit_price','detail_subtotal', 'detail_tax', 'detail_taxes', 'detail_retentions', 'detail_amount', 'paid_amount','total']))
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
					elseif($k == 'detail_quantity')
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

	public function purchaseDetail(Request $request)
	{
		if($request->ajax())
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.modal_compras')->with('request',$request);
		}
	}
	
	public function getAccount(Request $request)
	{
		if ($request->ajax())
		{
			$output   = "";
			$accounts = App\Account::where('selectable',1)
				->where('idEnterprise',$request->enterpriseid)
				->get();
			if (count($accounts)>0)
			{
				return Response($accounts);
			}
		}
	}
}
