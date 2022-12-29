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
use Ilovepdf\CompressTask;
use PDF;
use Excel;
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

class AdministracionRegistroCompraController extends Controller
{
	private $module_id = 203;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
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

	public function create()
	{
		if (Auth::user()->module->where('id',280)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.registro_compra.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 280,
				]);
		}
	}

	public function validationDocs(Request $request)
	{
		if($request->ajax())
		{
			$fiscal_val = $request->fiscal_value;
			$num_ticket = $request->num_ticket;
			$monto      = $request->monto;
			$datepath   = $request->datepath;
			$date       = Carbon::createFromFormat('d-m-Y', $datepath)->format('Y-m-d');
			$time       = new \DateTime($request->timepath);
			$timepath   = $time->format('H:i:s');
			if($fiscal_val!=''||$num_ticket!='')
			{
				$check_docs = App\PurchaseRecordDocuments::leftJoin('purchase_records','purchase_record_documents.idPurchaseRecord','purchase_records.id')
						->leftJoin('request_models','purchase_records.idFolio','request_models.folio')
						->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
						->where(function($check) use ($fiscal_val, $date, $timepath,$num_ticket, $monto)
						{
							$check->where(function($query2) use($date, $fiscal_val, $timepath)
							{
								$query2->where('purchase_record_documents.fiscal_folio',$fiscal_val);
								$query2->where('purchase_record_documents.datepath','like', ''.$date.'%');
								$query2->where('purchase_record_documents.timepath','like', ''.$timepath.'%');
								$query2->whereNotNull('purchase_record_documents.fiscal_folio');
							})
							->orWhere(function($query) use($num_ticket, $monto, $date,  $timepath){
								$query->where('purchase_record_documents.ticket_number',$num_ticket);
								$query->where('purchase_record_documents.datepath','like', ''.$date.'%');
								$query->where('purchase_record_documents.timepath','like', ''.$timepath.'%');
								$query->where('purchase_record_documents.amount', $monto);
								$query->whereNotNull('purchase_record_documents.ticket_number');
							});
						})
						->count();
				if($check_docs>0)
				{
					return Response('false');
				}
			}
			return Response('true');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',203)->count() > 0) 
		{
			$t_request               = new App\RequestModel();
			$t_request->kind         = 17;
			$t_request->taxPayment   = $request->fiscal;
			$t_request->fDate        = Carbon::now();
			$t_request->PaymentDate  = ($request->date!= null ? Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d') : null);
			$t_request->status       = 3;
			$t_request->account      = $request->accountid;
			$t_request->idEnterprise = $request->enterpriseid;
			$t_request->idArea       = $request->areaid;
			$t_request->idDepartment = $request->departmentid;
			$t_request->idProject    = $request->projectid;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->code_edt     = $request->code_edt;
			$t_request->idRequest    = $request->userid;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();
			$folio                               = $t_request->folio;
			$kind                                = $t_request->kind;
			$t_purchase_record                   = new App\PurchaseRecord();
			$t_purchase_record->idFolio          = $folio;
			$t_purchase_record->idKind           = $kind;
			$t_purchase_record->title            = $request->title;
			$t_purchase_record->datetitle        = ($request->datetitle!= null ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null);
			$t_purchase_record->numberOrder      = $request->numberOrder;
			$t_purchase_record->reference        = $request->referencePuchase;
			$t_purchase_record->provider         = $request->provider;
			$t_purchase_record->notes            = $request->note;
			$t_purchase_record->paymentMethod    = $request->pay_mode;
			$t_purchase_record->typeCurrency     = $request->type_currency;
			$t_purchase_record->billStatus       = $request->status_bill;
			$t_purchase_record->subtotal         = $request->subtotal;
			$t_purchase_record->tax              = $request->totaliva;
			$t_purchase_record->amount_taxes     = $request->amountAA;
			$t_purchase_record->amount_retention = $request->amountR;
			$t_purchase_record->total            = $request->amount_total;
			if ($request->pay_mode == "TDC Empresarial") 
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_input;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_input;	
			}
			else
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_select;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_select;
			}
			$t_purchase_record->idcreditCard = $request->idcreditCard;
			$t_purchase_record->save();
			$purchase = $t_purchase_record->id;
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name               = Files::rename($request->realPath[$i],$folio);
						$documents                   = new App\PurchaseRecordDocuments();
						$documents->fiscal_folio     = $request->folio_fiscal[$i];
						$documents->ticket_number    = $request->num_ticket[$i];
						$documents->amount           = $request->monto[$i];
						$documents->datepath         = Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
						$documents->timepath         = $request->timepath[$i];
						$documents->name             = $request->nameDocument[$i];
						$documents->path             = $new_file_name;
						$documents->idPurchaseRecord = $purchase;
						$documents->save();
					}
				}
			}
			for ($i=0; $i < count($request->tamount); $i++)
			{
				$t_detailPurchaseR                   = new App\PurchaseRecordDetail();
				$t_detailPurchaseR->idPurchaseRecord = $purchase;
				$t_detailPurchaseR->quantity         = $request->tquanty[$i];
				$t_detailPurchaseR->unit             = $request->tunit[$i];
				$t_detailPurchaseR->description      = $request->tdescr[$i];
				$t_detailPurchaseR->unitPrice        = $request->tprice[$i];
				$t_detailPurchaseR->tax              = $request->tiva[$i];
				$t_detailPurchaseR->discount         = $request->tdiscount[$i];
				$t_detailPurchaseR->total            = $request->tamount[$i];
				$t_detailPurchaseR->typeTax          = $request->tivakind[$i];
				$t_detailPurchaseR->subtotal         = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchaseR->save();
				$idPurchaseRecordDetail = $t_detailPurchaseR->id;
				$tamountadditional      = 'tamountadditional'.$i;
				$tnameamount            = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                         = new App\PurchaseRecordTaxes();
							$t_taxes->name                   = $request->$tnameamount[$d];
							$t_taxes->amount                 = $request->$tamountadditional[$d];
							$t_taxes->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                         = new App\PurchaseRecordRetention();
							$t_retention->name                   = $request->$tnameretention[$d];
							$t_retention->amount                 = $request->$tamountretention[$d];
							$t_retention->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_retention->save();
						}
					}
				}
			}
			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 282);
				})
				->whereHas('inChargeDepGet',function($q) use ($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',282);
				})
				->whereHas('inChargeEntGet',function($q) use ($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',282);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Registro de Compra";
						$status      = "Revisar";
						$date        = Carbon::now();
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url         = route('purchase-record.review.edit',['id'=>$folio]);
						$subject     = "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/purchase-record')->with('alert',$alert);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',281)->count() > 0)
		{
			if(Auth::user()->globalCheck->where('module_id',281)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',281)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$documents    = $request->documents;
			$provider     = $request->provider;
			$requests     = App\RequestModel::where(function($q)
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(281)->pluck('enterprise_id'))
						->orWhereNull('idEnterprise');
				})
				->where('kind',17)
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro') 
						{
							$q->whereHas('purchaseRecord',function($q)
							{
								$q->whereNotIn('billStatus',['Pendiente','Entregado','No Aplica']);
							});
						}
						else
						{
							$q->whereHas('purchaseRecord',function($q) use($documents)
							{
								$q->where('billStatus',$documents);
							});
						}
					}
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status)
				{
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('idEnterprise',$enterpriseid)->orWhere('idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query2) use ($account)
						{	
							$query2->where('account',$account)->orWhere('accountR',$account);
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$query->where('folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->where(function($query) use($provider)
				{
					if($provider != "") 
					{
						$query->whereHas('purchaseRecord', function($q) use($provider)
						{
							$q->where('provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return view('administracion.registro_compra.busqueda_seguimiento',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 281,
					'requests'     => $requests,
					'account'      => $account, 
					'name'         => $name, 
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'folio'        => $folio,
					'status'       => $status,
					'enterpriseid' => $enterpriseid,
					'documents'    => $documents,
					'provider'     => $provider,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function unsent(Request $request)
	{
		if (Auth::user()->module->where('id',203)->count()>0) 
		{
			$t_request               = new App\RequestModel();
			$t_request->kind         = 17;
			$t_request->taxPayment   = $request->fiscal;
			$t_request->fDate        = Carbon::now();
			$t_request->PaymentDate  = ($request->date!= null ? Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d') : null);
			$t_request->status       = 2;
			$t_request->account      = $request->accountid;
			$t_request->idEnterprise = $request->enterpriseid;
			$t_request->idArea       = $request->areaid;
			$t_request->idDepartment = $request->departmentid;
			$t_request->idProject    = $request->projectid;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->code_edt     = $request->code_edt;
			$t_request->idRequest    = $request->userid;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();
			$folio                               = $t_request->folio;
			$kind                                = $t_request->kind;
			$t_purchase_record                   = new App\PurchaseRecord();
			$t_purchase_record->idFolio          = $folio;
			$t_purchase_record->idKind           = $kind;
			$t_purchase_record->title            = $request->title;
			$t_purchase_record->datetitle        = ($request->datetitle!= null ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null);
			$t_purchase_record->numberOrder      = $request->numberOrder;
			$t_purchase_record->reference        = $request->referencePuchase;
			$t_purchase_record->provider         = $request->provider;
			$t_purchase_record->notes            = $request->note;
			$t_purchase_record->paymentMethod    = $request->pay_mode;
			$t_purchase_record->typeCurrency     = $request->type_currency;
			$t_purchase_record->billStatus       = $request->status_bill;
			$t_purchase_record->subtotal         = $request->subtotal;
			$t_purchase_record->tax              = $request->totaliva;
			$t_purchase_record->amount_taxes     = $request->amountAA;
			$t_purchase_record->amount_retention = $request->amountR;
			$t_purchase_record->total            =  $request->amount_total;
			if ($request->pay_mode == "TDC Empresarial") 
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_input;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_input;	
			}
			else
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_select;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_select;
			}
			$t_purchase_record->idcreditCard 		= $request->idcreditCard;
			$t_purchase_record->save();
			$purchase								= $t_purchase_record->id;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "")
					{
						$documents                   = new App\PurchaseRecordDocuments();
						$new_file_name               = Files::rename($request->realPath[$i],$folio);
						$documents->path             = $new_file_name;
						$documents->fiscal_folio     = $request->folio_fiscal[$i];
						$documents->ticket_number    = $request->num_ticket[$i];
						$documents->amount           = $request->monto[$i];
						$documents->datepath         = Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
						$documents->timepath         = $request->timepath[$i];
						$documents->name             = $request->nameDocument[$i];
						$documents->idPurchaseRecord = $purchase;
						$documents->save();
					}
				}
			}
			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchaseR                   = new App\PurchaseRecordDetail();
				$t_detailPurchaseR->idPurchaseRecord = $purchase;
				$t_detailPurchaseR->quantity         = $request->tquanty[$i];
				$t_detailPurchaseR->unit             = $request->tunit[$i];
				$t_detailPurchaseR->description      = $request->tdescr[$i];
				$t_detailPurchaseR->unitPrice        = $request->tprice[$i];
				$t_detailPurchaseR->tax              = $request->tiva[$i];
				$t_detailPurchaseR->discount         = $request->tdiscount[$i];
				$t_detailPurchaseR->total            = $request->tamount[$i];
				$t_detailPurchaseR->typeTax          = $request->tivakind[$i];
				$t_detailPurchaseR->subtotal         = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchaseR->save();
				$idPurchaseRecordDetail = $t_detailPurchaseR->id;
				$tamountadditional      = 'tamountadditional'.$i;
				$tnameamount            = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                         = new App\PurchaseRecordTaxes();
							$t_taxes->name                   = $request->$tnameamount[$d];
							$t_taxes->amount                 = $request->$tamountadditional[$d];
							$t_taxes->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                         = new App\PurchaseRecordRetention();
							$t_retention->name                   = $request->$tnameretention[$d];
							$t_retention->amount                 = $request->$tamountretention[$d];
							$t_retention->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_retention->save();
						}
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('purchase-record.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
	}

	public function updateUnsentFollow(Request $request,$id)
	{
		if (Auth::user()->module->where('id',203)->count()>0) 
		{
			$t_request               = App\RequestModel::find($id);
			$t_request->kind         = 17;
			$t_request->taxPayment   = $request->fiscal;
			$t_request->fDate        = Carbon::now();
			$t_request->PaymentDate  = ($request->date!= null ? Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d') : null);
			$t_request->status       = 2;
			$t_request->account      = $request->accountid;
			$t_request->idEnterprise = $request->enterpriseid;
			$t_request->idArea       = $request->areaid;
			$t_request->idDepartment = $request->departmentid;
			$t_request->idProject    = $request->projectid;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->code_edt     = $request->code_edt;
			$t_request->idRequest    = $request->userid;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();
			$folio                               = $t_request->folio;
			$kind                                = $t_request->kind;
			$purchaseID                          = App\PurchaseRecord::where('idFolio',$folio)->first()->id;
			isset(App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->first()->id) ? $detailID = App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->first()->id : $detailID = null;
			$t_purchase_record                   = App\PurchaseRecord::find($purchaseID);
			$t_purchase_record->idFolio          = $folio;
			$t_purchase_record->idKind           = $kind;
			$t_purchase_record->title            = $request->title;
			$t_purchase_record->datetitle        = ($request->datetitle!= null ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null);
			$t_purchase_record->numberOrder      = $request->numberOrder;
			$t_purchase_record->reference        = $request->referencePuchase;
			$t_purchase_record->provider         = $request->provider;
			$t_purchase_record->notes            = $request->note;
			$t_purchase_record->paymentMethod    = $request->pay_mode;
			$t_purchase_record->typeCurrency     = $request->type_currency;
			$t_purchase_record->billStatus       = $request->status_bill;
			$t_purchase_record->subtotal         = $request->subtotal;
			$t_purchase_record->tax              = $request->totaliva;
			$t_purchase_record->amount_taxes     = $request->amountAA;
			$t_purchase_record->amount_retention = $request->amountR;
			$t_purchase_record->total            = $request->amount_total;
			if ($request->pay_mode == "TDC Empresarial") 
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_input;
				$t_purchase_record->idAccAccPayment     = $request->accountid_payment_input;	
			}
			else
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_select;
				$t_purchase_record->idAccAccPayment     = $request->accountid_payment_select;
			}
			$t_purchase_record->idcreditCard = $request->idcreditCard;
			$t_purchase_record->save();
			$purchase                        = $t_purchase_record->id;
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$documents                   = new App\PurchaseRecordDocuments();
						$new_file_name               = Files::rename($request->realPath[$i],$folio);
						$documents->path             = $new_file_name;
						$documents->fiscal_folio     = $request->folio_fiscal[$i];
						$documents->ticket_number    = $request->num_ticket[$i];
						$documents->amount           = $request->monto[$i];
						$documents->datepath         = Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
						$documents->timepath         = $request->timepath[$i];
						$documents->name             = $request->nameDocument[$i];
						$documents->idPurchaseRecord = $purchase;
						$documents->save();
					}
				}
			}
			$deleteTaxes      = App\PurchaseRecordTaxes::where('idPurchaseRecordDetail',$detailID)->delete();
			$deleteRetentions = App\PurchaseRecordRetention::where('idPurchaseRecordDetail',$detailID)->delete();
			$delete           = App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->delete();
			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchaseR                   = new App\PurchaseRecordDetail();
				$t_detailPurchaseR->idPurchaseRecord = $purchase;
				$t_detailPurchaseR->quantity         = $request->tquanty[$i];
				$t_detailPurchaseR->unit             = $request->tunit[$i];
				$t_detailPurchaseR->description      = $request->tdescr[$i];
				$t_detailPurchaseR->unitPrice        = $request->tprice[$i];
				$t_detailPurchaseR->tax              = $request->tiva[$i];
				$t_detailPurchaseR->discount         = $request->tdiscount[$i];
				$t_detailPurchaseR->total            = $request->tamount[$i];
				$t_detailPurchaseR->typeTax          = $request->tivakind[$i];
				$t_detailPurchaseR->subtotal         = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchaseR->save();
				$idPurchaseRecordDetail	= $t_detailPurchaseR->id;
				$tamountadditional		= 'tamountadditional'.$i;
				$tnameamount			= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                         = new App\PurchaseRecordTaxes();
							$t_taxes->name                   = $request->$tnameamount[$d];
							$t_taxes->amount                 = $request->$tamountadditional[$d];
							$t_taxes->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                         = new App\PurchaseRecordRetention();
							$t_retention->name                   = $request->$tnameretention[$d];
							$t_retention->amount                 = $request->$tamountretention[$d];
							$t_retention->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_retention->save();
						}
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('purchase-record.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
	}

	public function updateFollow(Request $request,$id)
	{
		if (Auth::user()->module->where('id',203)->count()>0) 
		{
			$t_request               = App\RequestModel::find($id);
			$t_request->kind         = 17;
			$t_request->taxPayment   = $request->fiscal;
			$t_request->fDate        = Carbon::now();
			$t_request->PaymentDate  = ($request->date!= null ? Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d') : null);
			$t_request->status       = 3;
			$t_request->account      = $request->accountid;
			$t_request->idEnterprise = $request->enterpriseid;
			$t_request->idArea       = $request->areaid;
			$t_request->idDepartment = $request->departmentid;
			$t_request->idProject    = $request->projectid;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->code_edt     = $request->code_edt;
			$t_request->idRequest    = $request->userid;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();
			$folio      = $t_request->folio;
			$kind       = $t_request->kind;
			$purchaseID = App\PurchaseRecord::where('idFolio',$folio)->first()->id;
			isset(App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->first()->id) ? $detailID = App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->first()->id : $detailID = null;
			$t_purchase_record                   = App\PurchaseRecord::find($purchaseID);
			$t_purchase_record->idFolio          = $folio;
			$t_purchase_record->idKind           = $kind;
			$t_purchase_record->title            = $request->title;
			$t_purchase_record->datetitle        = ($request->datetitle!= null ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null);
			$t_purchase_record->numberOrder      = $request->numberOrder;
			$t_purchase_record->reference        = $request->referencePuchase;
			$t_purchase_record->provider         = $request->provider;
			$t_purchase_record->notes            = $request->note;
			$t_purchase_record->paymentMethod    = $request->pay_mode;
			$t_purchase_record->typeCurrency     = $request->type_currency;
			$t_purchase_record->billStatus       = $request->status_bill;
			$t_purchase_record->subtotal         = $request->subtotal;
			$t_purchase_record->tax              = $request->totaliva;
			$t_purchase_record->amount_taxes     = $request->amountAA;
			$t_purchase_record->amount_retention = $request->amountR;
			$t_purchase_record->total            = $request->amount_total;
			if ($request->pay_mode == "TDC Empresarial") 
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_input;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_input;	
			}
			else
			{
				$t_purchase_record->idEnterprisePayment = $request->enterpriseid_payment_select;
				$t_purchase_record->idAccAccPayment 	= $request->accountid_payment_select;
			}
			$t_purchase_record->idcreditCard 		= $request->idcreditCard;
			$t_purchase_record->save();
			$purchase								= $t_purchase_record->id;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$date= Carbon::parse($request->datepath[$i])->format('Y-m-d');
						$documents						= new App\PurchaseRecordDocuments();
						$new_file_name = Files::rename($request->realPath[$i],$folio);
						$documents->path				= $new_file_name;
						$documents->fiscal_folio 	= $request->folio_fiscal[$i];
						$documents->ticket_number 	= $request->num_ticket[$i];
						$documents->amount 			= $request->monto[$i];
						$documents->datepath 		= $date;
						$documents->timepath 		= $request->timepath[$i];
						$documents->name			= $request->nameDocument[$i];
						$documents->idPurchaseRecord	= $purchase;
						$documents->save();
					}
				}
			}
			$deleteTaxes      = App\PurchaseRecordTaxes::where('idPurchaseRecordDetail',$detailID)->delete();
			$deleteRetentions = App\PurchaseRecordRetention::where('idPurchaseRecordDetail',$detailID)->delete();
			$delete           = App\PurchaseRecordDetail::where('idPurchaseRecord',$purchaseID)->delete();
			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchaseR                   = new App\PurchaseRecordDetail();
				$t_detailPurchaseR->idPurchaseRecord = $purchase;
				$t_detailPurchaseR->quantity         = $request->tquanty[$i];
				$t_detailPurchaseR->unit             = $request->tunit[$i];
				$t_detailPurchaseR->description      = $request->tdescr[$i];
				$t_detailPurchaseR->unitPrice        = $request->tprice[$i];
				$t_detailPurchaseR->tax              = $request->tiva[$i];
				$t_detailPurchaseR->discount         = $request->tdiscount[$i];
				$t_detailPurchaseR->total            = $request->tamount[$i];
				$t_detailPurchaseR->typeTax          = $request->tivakind[$i];
				$t_detailPurchaseR->subtotal         = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchaseR->save();
				$idPurchaseRecordDetail = $t_detailPurchaseR->id;
				$tamountadditional      = 'tamountadditional'.$i;
				$tnameamount            = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                         = new App\PurchaseRecordTaxes();
							$t_taxes->name                   = $request->$tnameamount[$d];
							$t_taxes->amount                 = $request->$tamountadditional[$d];
							$t_taxes->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                         = new App\PurchaseRecordRetention();
							$t_retention->name                   = $request->$tnameretention[$d];
							$t_retention->amount                 = $request->$tamountretention[$d];
							$t_retention->idPurchaseRecordDetail = $idPurchaseRecordDetail;
							$t_retention->save();
						}
					}
				}
			}
			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 282);
				})
				->whereHas('inChargeDepGet',function($q) use ($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',282);
				})
				->whereHas('inChargeEntGet',function($q) use ($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',282);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Registro de Compra";
						$status      = "Revisar";
						$date        = Carbon::now();
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url         = route('purchase-record.review.edit',['id'=>$folio]);
						$subject     = "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/purchase-record')->with('alert',$alert);
		}
	}

	public function follow($id)
	{
		if(Auth::user()->module->where('id',281)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',281)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',281)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::where('kind',17)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			if($request != "")
			{
				return view('administracion.registro_compra.seguimiento',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 281,
						'request'   => $request
					]);
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

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',282)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$requests     = App\RequestModel::where('kind',17)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(282)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(282)->pluck('departament_id'))
				->where('status',3)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid)
				{
					if ($enterpriseid != "") 
					{
						$q->where('idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('account',$account);
					}
					if($name != "")
					{
						$q->whereHas('requestUser',function($q) use ($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						}); 
					}
					if($folio != "")
					{
						$q->where('folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->where(function ($q) use($provider)
				{
					if ($provider != "") 
					{
						$q->whereHas('purchaseRecord',function($q) use($provider)
						{
							$q->where('provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.registro_compra.busqueda_revision',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 282,
						'requests'     => $requests,
						'account'      => $account,
						'name'         => $name,
						'mindate'      => $request->mindate,
						'maxdate'      => $request->maxdate,
						'folio'        => $folio,
						'enterpriseid' => $enterpriseid,
						'type'         => $type,
						'provider'     => $provider,
					]
				)
			)->cookie(
				'urlSearch', storeUrlCookie(282), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',282)->count()>0)
		{
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::where('kind',17)
				->where('status',3)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(282)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(282)->pluck('departament_id'))
				->find($id);
			if($request != "")
			{
				return view('administracion.registro_compra.revision',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 282,
						'request'   => $request
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect()->route('purchase-record.review')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',282)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == 4)
				{
					for ($i=0; $i < count($request->t_idPurchaseRecordDetail); $i++) 
					{
						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelPurchase                         = new App\PurchaseRecordLabel();
								$labelPurchase->idLabel                = $request->$idLabelsAssign[$d];
								$labelPurchase->idPurchaseRecordDetail = $request->t_idPurchaseRecordDetail[$i];
								$labelPurchase->save();
							}
						}
					}
					$review->status         = $request->status;
					$review->accountR       = $request->accountR;
					$review->idEnterpriseR  = $request->idEnterpriseR;
					$review->idDepartamentR = $request->idDepartmentR;
					$review->idAreaR        = $request->idAreaR;
					$review->idProjectR     = $request->project_id;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentA;
					$review->reviewDate     = Carbon::now();
					$review->save();
					if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>17));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 283);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)->where('module_id',283);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)->where('module_id',283);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					$user = App\User::find($review->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Registro de Compra";
								$status      = "Autorizar";
								$date        = Carbon::now();
								$url         = route('purchase-record.authorization.edit',['id' => $id]);
								$subject     = "Solicitud por Autorizar";
								$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert 	= "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
				elseif ($request->status == 6)
				{
					$review->status       = $request->status;
					$review->idCheck      = Auth::user()->id;
					$review->checkComment = $request->checkCommentR;
					$review->reviewDate   = Carbon::now();
					$review->save();
					$emailRequest         = "";
					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest = App\User::where('id',$review->idElaborate)
							->where('notification',1)
							->get();
					}
					else
					{
						$emailRequest = App\User::where('id',$review->idElaborate)
							->orWhere('id',$review->idRequest)
							->where('notification',1)
							->get();
					}
					if ($emailRequest != "")
					{
						try
						{
							foreach ($emailRequest as $email)
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Registro de Compra";
								$status      = "RECHAZADA";
								$date        = Carbon::now();
								$url         = route('purchase-record.follow.edit',['id' => $id]);
								$subject     = "Estado de Solicitud";
								$requestUser = null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_ruled_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(282, $alert, 'administration/purchase-record/review');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',283)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? $request->mindate: null;
			$maxdate      = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$requests     = App\RequestModel::where('kind',17)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(283)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(283)->pluck('departament_id'))
				->where('status',4)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid)
				{
					if ($enterpriseid != "") 
					{
						$q->where('idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('account',$account);
					}
					if($name != "")
					{
						$q->whereHas('requestUser',function($q) use ($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$q->where('folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('fDate',[''.Carbon::parse($mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::parse($maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
					}
				})
				->where(function ($q) use($provider)
				{
					if ($provider != "") 
					{
						$q->whereHas('purchaseRecord',function($q) use($provider)
						{
							$q->where('provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.registro_compra.busqueda_autorizacion',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 283,
						'requests'     => $requests,
						'account'      => $account,
						'name'         => $name,
						'mindate'      => $mindate,
						'maxdate'      => $maxdate,
						'folio'        => $folio,
						'enterpriseid' => $enterpriseid,
						'type'         => $type,
						'provider'     => $provider,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(283), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',283)->count()>0)
		{
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::where('kind',17)
				->where('status',4)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(283)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(283)->pluck('departament_id'))
				->find($id);
			if ($request != "")
			{
				return view('administracion.registro_compra.autorizacion',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 283,
						'request'   => $request
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect()->route("purchase-record.authorization")->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',283)->count()>0)
		{
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 10 || $checkStatus->status == 7) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$data                        = App\Module::find($this->module_id);
				$authorize                   = App\RequestModel::find($id);
				$authorize->status           = $request->status;
				$authorize->idAuthorize      = Auth::user()->id;
				$authorize->authorizeComment = $request->authorizeCommentA;
				$authorize->authorizeDate    = Carbon::now();
				$authorize->save();
				if ($request->status == 10)
				{
					$t_payment                            = new App\Payment();
					$t_payment->fiscal                    = $authorize->taxPayment;
					$t_payment->subtotal                  = $authorize->purchaseRecord->subtotal;
					$t_payment->iva                       = $authorize->purchaseRecord->tax;
					$t_payment->amount                    = $authorize->purchaseRecord->total;
					$t_payment->subtotal_real             = $authorize->purchaseRecord->subtotal;
					$t_payment->iva_real                  = $authorize->purchaseRecord->tax;
					$t_payment->amount_real               = $authorize->purchaseRecord->total;
					$t_payment->account                   = $authorize->purchaseRecord->idAccAccPayment;
					$t_payment->paymentDate               = $authorize->PaymentDate;
					$t_payment->elaborateDate             = Carbon::now();
					$t_payment->idFolio                   = $authorize->folio;
					$t_payment->idKind                    = $authorize->kind;
					$t_payment->idRequest                 = Auth::user()->id;
					$t_payment->idEnterprise              = $authorize->purchaseRecord->idEnterprisePayment;
					$t_payment->commentaries              = '';
					$t_payment->exchange_rate             = 1;
					$t_payment->exchange_rate_description = '';
					$t_payment->save();
				}
				$alert        = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				$emailRequest = "";
				if ($authorize->idElaborate == $authorize->idRequest) 
				{
					$emailRequest = App\User::where('id',$authorize->idElaborate)
						->where('notification',1)
						->get();
				}
				else
				{
					$emailRequest = App\User::where('id',$authorize->idElaborate)
						->orWhere('id',$authorize->idRequest)
						->where('notification',1)
						->get();
				}
				$user = App\User::find($authorize->idRequest);
				if ($emailRequest != "")
				{
					try
					{
						foreach ($emailRequest as $email)
						{
							$name = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to   = $email->email;
							$kind = "Registro de Compra";
							if ($request->status == 10)
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date			= Carbon::now();
							$url			= route('purchase-record.follow.edit',['id'=>$id]);
							$subject		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert = "swal('', '".Lang::get("messages.request_ruled_no_mail")."', 'success');";
					}
				}
			}
			return searchRedirect(283, $alert, 'administration/purchase-record/review');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',203)->count()>0)
		{
			/*
			$test = DB::table('request')
				->selectRaw('refund_documents.path, request_models.status')
				->join('refunds', 'refunds.idFolio', 'request_models.folio')
				->join('refund_details', 'refunddetail.idRefund', 'refunds.idRefund')
				->join('refund_documents', 'refunddocuments.idRefundDetail', 'refunddetail.idRefundDetail')
				->where('refunds.idFolio', $id)
				->whereIn('request_models.status', [5])
				->get();
				
			if($test!='[]')
			{
				$flag=1;
			}
			else
			{
				$flag=2;
			}

			*/
			if(Auth::user()->globalCheck->where('module_id',281)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',281)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data     = App\Module::find($this->module_id);
			$requests = App\RequestModel::whereIn('status',[5, 6, 7,10,11,12,13])
				->where('kind',17)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			if($requests != "")
			{
				return view('administracion.registro_compra.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 280,
						'requests'  => $requests
					]);
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

	public function getCreditCard(Request $request)
	{
		if($request->ajax())
		{
			$output   = "";
			$accounts = App\Account::leftJoin('credit_cards','credit_cards.idAccAcc','accounts.idAccAcc')
				->select('accounts.idAccAcc','accounts.account','accounts.description','accounts.content')
				->where('credit_cards.idEnterprise',$request->enterpriseid)
				->where('credit_cards.assignment',$request->request_id)
				->groupBy('accounts.idAccAcc')
				->get();
			if (count($accounts) > 0) 
			{
				return Response($accounts);
			}
		}
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'   => 'ERROR',
			'message' => 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/purchase-record/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention            = strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention = 'AdG'.round(microtime(true) * 1000).'_purchaseDoc.';
				$name                 = $nameWithoutExtention.$extention;
				$destinity            = '/docs/purchase-record/'.$name;
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
						$response['extention']	= strtolower($extention);
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

	public function updateBill(Request $request, $id)
	{
		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			$purchase               = App\PurchaseRecord::where('idFolio',$id)->get();
			$updateBill             = App\PurchaseRecord::find($purchase->first()->id);
			$updateBill->billStatus = $request->status_bill;
			$updateBill->save();
			for ($i=0; $i < count($request->realPath); $i++) 
			{ 
				if ($request->realPath[$i] != "") 
				{
					$documents                   = new App\PurchaseRecordDocuments();
					$new_file_name               = Files ::rename($request->realPath[$i],$id);
					$documents->path             = $new_file_name;
					$date                        = Carbon::parse($request->datepath[$i])->format('Y-m-d');
					$documents->fiscal_folio     = $request->folio_fiscal[$i];
					$documents->ticket_number    = $request->num_ticket[$i];
					$documents->amount           = $request->monto[$i];
					$documents->datepath         = $date;
					$documents->timepath         = $request->timepath[$i];
					$documents->name             = $request->nameDocument[$i];
					$documents->idPurchaseRecord = $purchase->first()->id;
					$documents->save();
				}
			}
			$alert 	= "swal('', 'Documentos Enviados Exitosamente', 'success');";
			return redirect()->route('purchase-record.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			$purchase 				= App\PurchaseRecord::where('idFolio',$id)->get();
			$updateBill 			= App\PurchaseRecord::find($purchase->first()->id);
			$updateBill->billStatus = $request->status_bill;
			$updateBill->save();
			$alert 	= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('purchase-record.follow.edit',['id'=>$id])->with('alert',$alert);
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',281)->count() > 0)
		{
			if(Auth::user()->globalCheck->where('module_id',281)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',281)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$documents    = $request->documents;
			$provider     = $request->provider;
			$requests = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchase_records.title," - ",purchase_records.datetitle) as title,
					purchase_records.numberOrder as number_order,
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
					IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					purchase_records.provider as provider,
					purchase_records.reference as reference,
					purchase_records.paymentMethod as paymentMethod,
					credit_cards.credit_card as creditCard,
					CONCAT_WS(" ",responsibleUser.name,responsibleUser.last_name,responsibleUser.scnd_last_name) as responsible,
					purchase_record_details.quantity as detail_quantity,
					purchase_record_details.unit as detail_unit,
					purchase_record_details.description as detail_description,
					purchase_record_details.unitPrice as detail_unit_price,
					purchase_record_details.subtotal as detail_subtotal,
					purchase_record_details.tax as detail_tax,
					IFNULL(purchase_record_taxes.taxes_amount,0) as detail_taxes,
					IFNULL(purchase_record_retentions.retention_amount,0) as detail_retentions,
					purchase_record_details.total as detail_amount,
					purchase_record_labels.labels as detail_labels,
					IFNULL(payment.payment_amount,0) as payment_amount,
					purchase_records.typeCurrency as currency,
					purchase_records.total as amount_total
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchase_records','purchase_records.idFolio','request_models.folio')
				->leftJoin('purchase_record_details','purchase_records.id','purchase_record_details.idPurchaseRecord')
				->leftJoin('credit_cards','credit_cards.idcreditCard','purchase_records.idcreditCard')
				->leftJoin('users as responsibleUser','responsibleUser.id','credit_cards.assignment')
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
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS purchase_record_labels'),'purchase_record_details.id','purchase_record_labels.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS purchase_record_taxes'),'purchase_record_details.id','purchase_record_taxes.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS purchase_record_retentions'),'purchase_record_details.id','purchase_record_retentions.idPurchaseRecordDetail')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS payment'),function($q)
					{
						$q->on('request_models.folio','=','payment.idFolio')
						->on('request_models.kind','=','payment.idKind');
					})
				->where(function($q)
				{
					$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(281)->pluck('enterprise_id'))
						->orWhereNull('request_models.idEnterprise');
				})
				->where(function($q)
				{
					$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(281)->pluck('departament_id'))
						->orWhereNull('request_models.idDepartment');
				})
				->where('request_models.kind',17)
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro') 
						{
							$q->whereNotIn('purchase_records.billStatus',['Pendiente','Entregado','No Aplica']);
						}
						else
						{
							$q->where('purchase_records.billStatus',$documents);
						}
					}
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status,$provider)
				{
					if ($enterpriseid != "") 
					{
						$query->where(function($query) use ($enterpriseid)
						{
							$query->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query) use ($account)
						{
							$query->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
						$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($provider != "") 
					{
						$query->where('purchase_records.provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Registro de Compras.xlsx');
			$headers		= ['Reporte de Registro de Compras','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders	= [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaders		= ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','','','','',''];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$subHeaders		= ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Número de Tarjeta','Responsable de Tarjeta','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total Pagado','Moneda','Importe Total'];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio	= '';
			$kindRow	= true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
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
					$request->taxPayment         = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->paymentMethod      = '';
					$request->creditCard         = '';
					$request->responsible        = '';
					$request->payment_amount     = '';
					$request->currency           = '';
					$request->amount_total       = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['detail_unit_price','detail_subtotal','detail_tax','detail_taxes','detail_retentions','detail_amount','payment_amount','amount_total']))
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

	public function exportReview(Request $request)
	{
		if(Auth::user()->module->where('id',282)->count() > 0)
		{
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchase_records.title," - ",purchase_records.datetitle) as title,
					purchase_records.numberOrder as number_order,
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
					IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					purchase_records.provider as provider,
					purchase_records.reference as reference,
					purchase_records.paymentMethod as paymentMethod,
					credit_cards.credit_card as creditCard,
					CONCAT_WS(" ",responsibleUser.name,responsibleUser.last_name,responsibleUser.scnd_last_name) as responsible,
					purchase_record_details.quantity as detail_quantity,
					purchase_record_details.unit as detail_unit,
					purchase_record_details.description as detail_description,
					purchase_record_details.unitPrice as detail_unit_price,
					purchase_record_details.subtotal as detail_subtotal,
					purchase_record_details.tax as detail_tax,
					IFNULL(purchase_record_taxes.taxes_amount,0) as detail_taxes,
					IFNULL(purchase_record_retentions.retention_amount,0) as detail_retentions,
					purchase_record_details.total as detail_amount,
					purchase_record_labels.labels as detail_labels,
					IFNULL(payment.payment_amount,0) as payment_amount,
					purchase_records.typeCurrency as currency,
					purchase_records.total as amount_total
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchase_records','purchase_records.idFolio','request_models.folio')
				->leftJoin('purchase_record_details','purchase_records.id','purchase_record_details.idPurchaseRecord')
				->leftJoin('credit_cards','credit_cards.idcreditCard','purchase_records.idcreditCard')
				->leftJoin('users as responsibleUser','responsibleUser.id','credit_cards.assignment')
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
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS purchase_record_labels'),'purchase_record_details.id','purchase_record_labels.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS purchase_record_taxes'),'purchase_record_details.id','purchase_record_taxes.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS purchase_record_retentions'),'purchase_record_details.id','purchase_record_retentions.idPurchaseRecordDetail')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS payment'),function($q)
					{
						$q->on('request_models.folio','=','payment.idFolio')
						->on('request_models.kind','=','payment.idKind');
					})
				->where('request_models.kind',17)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(282)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(282)->pluck('departament_id'))
				->where('request_models.status',3)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid,$provider)
				{
					if ($enterpriseid != "") 
					{
						$q->where('request_models.idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('request_models.account',$account);
					}
					if($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
					}
					if($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($provider != "") 
					{
						$q->where('purchase_records.provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Registro de Compras.xlsx');
			$headers		= ['Reporte de Registro de Compras','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders	= [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders		= ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','','','','',''];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders		= ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Número de Tarjeta','Responsable de Tarjeta','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total Pagado','Moneda','Importe Total'];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio	= '';
			$kindRow	= true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
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
					$request->taxPayment         = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->paymentMethod      = '';
					$request->creditCard         = '';
					$request->responsible        = '';
					$request->payment_amount     = '';
					$request->currency           = '';
					$request->amount_total = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['detail_unit_price','detail_subtotal','detail_tax','detail_taxes','detail_retentions','detail_amount','payment_amount','amount_total']))
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

	public function exportAuthorize(Request $request)
	{
		if(Auth::user()->module->where('id',283)->count() > 0)
		{
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? $request->mindate: null;
			$maxdate      = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? $request->mindate: null;
			$maxdate      = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchase_records.title," - ",purchase_records.datetitle) as title,
					purchase_records.numberOrder as number_order,
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
					IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					purchase_records.provider as provider,
					purchase_records.reference as reference,
					purchase_records.paymentMethod as paymentMethod,
					credit_cards.credit_card as creditCard,
					CONCAT_WS(" ",responsibleUser.name,responsibleUser.last_name,responsibleUser.scnd_last_name) as responsible,
					purchase_record_details.quantity as detail_quantity,
					purchase_record_details.unit as detail_unit,
					purchase_record_details.description as detail_description,
					purchase_record_details.unitPrice as detail_unit_price,
					purchase_record_details.subtotal as detail_subtotal,
					purchase_record_details.tax as detail_tax,
					IFNULL(purchase_record_taxes.taxes_amount,0) as detail_taxes,
					IFNULL(purchase_record_retentions.retention_amount,0) as detail_retentions,
					purchase_record_details.total as detail_amount,
					purchase_record_labels.labels as detail_labels,
					IFNULL(payment.payment_amount,0) as payment_amount,
					purchase_records.typeCurrency as currency,
					purchase_records.total as amount_total
				')
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchase_records','purchase_records.idFolio','request_models.folio')
				->leftJoin('purchase_record_details','purchase_records.id','purchase_record_details.idPurchaseRecord')
				->leftJoin('credit_cards','credit_cards.idcreditCard','purchase_records.idcreditCard')
				->leftJoin('users as responsibleUser','responsibleUser.id','credit_cards.assignment')
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
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS purchase_record_labels'),'purchase_record_details.id','purchase_record_labels.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS purchase_record_taxes'),'purchase_record_details.id','purchase_record_taxes.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS purchase_record_retentions'),'purchase_record_details.id','purchase_record_retentions.idPurchaseRecordDetail')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS payment'),function($q)
					{
						$q->on('request_models.folio','=','payment.idFolio')
						->on('request_models.kind','=','payment.idKind');
					})
				->where('request_models.kind',17)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(283)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(283)->pluck('departament_id'))
				->where('request_models.status',4)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid,$provider)
				{
					if ($enterpriseid != "") 
					{
						$q->where('request_models.idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('request_models.account',$account);
					}
					if($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
					}
					if($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('request_models.fDate',[''.Carbon::parse($mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::parse($maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
					}
					if($provider != "") 
					{
						$q->where('purchase_records.provider','LIKE','%'.preg_replace("/\s+/", "%", $provider).'%');
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Registro de Compras.xlsx');
			$headers		= ['Reporte de Registro de Compras','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders	= [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaders		= ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','','','','',''];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$subHeaders		= ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Número de Tarjeta','Responsable de Tarjeta','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total Pagado','Moneda','Importe Total'];
			$tempSubHeader	= [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio	= '';
			$kindRow	= true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
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
					$request->taxPayment         = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->paymentMethod      = '';
					$request->creditCard         = '';
					$request->responsible        = '';
					$request->payment_amount     = '';
					$request->currency           = '';
					$request->amount_total = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['detail_unit_price','detail_subtotal','detail_tax','detail_taxes','detail_retentions','detail_amount','payment_amount','amount_total']))
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

	public function getCreditCardsData(Request $request)
	{
		if ($request->ajax()) 
		{
			$tdc = App\CreditCards::where('assignment',$request->request_id)->get();
			return view('administracion.registro_compra.partial.tarjetas_credito',['tdc'=>$tdc]);
		}
	}
}
