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
use Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
use Excel;
use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionReembolsoController extends Controller
{
	private $module_id = 117;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count() > 0)
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
			[
				'id'       => $data['father'],
				'title'    => $data['name'],
				'details'  => $data['details'],
				'child_id' => $this->module_id
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',118)->count() > 0)
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.reembolso.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 118
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest(Request $request, $id)
	{
		if(Auth::user()->module->where('id',118)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',119)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',119)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data		= App\Module::find($this->module_id);
			$requests	= App\RequestModel::where('kind',9)
						->whereIn('status',[5,6,7,10,11,12,13]) 
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
				return view('administracion.reembolso.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 118,
						'requests'  => $requests, 
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

	public function validationDocument(Request $request)
	{
		if($request->ajax())
		{
			$folio             = $request->requestFolio;

			if (isset($request->fromOther) && $request->fromOther == 1)
			{
				$check_docs_status = App\RequestModel::where('folio', $folio)->count();
			}
			else
			{
				$check_docs_status = App\RequestModel::where('folio', $folio)->whereIn('status',[2])->count();
			}
			
			if($check_docs_status > 0)
			{
				$folio = $folio;
				$documentExistFolio = App\RefundDocuments::leftJoin('refund_details','refund_details.idRefundDetail','refund_documents.idRefundDetail')
					->leftJoin('refunds','refund_details.idRefund','refunds.idRefund')
					->leftJoin('request_models','refunds.idFolio','request_models.folio')
					->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
					->where('request_models.folio', $folio)
					->get();
					
			}
			else
			{
				$folio = "";
				$documentExistFolio = [];
			}

			$position = [];
		
			for ($i=0; $i < count($request->datepath); $i++)
			{ 
				$tempDatepath = $request->datepath[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d') : $request->datepath[$i];
				$tempTimepath = $request->timepath[$i] != "" ? Carbon::createFromFormat('H:i', $request->timepath[$i])->format('H:i:s') : $request->timepath[$i];

				$options               	= [];
				$options['fiscal_val'] 	= $request->fiscal_folio[$i];
				$options['ticket_val'] 	= $request->ticket_number[$i];
				$options['date']       	= $tempDatepath;
				$options['time']       	= $tempTimepath;
				$options['amount']     	= $request->amount[$i];
				$options['new_doc']    	= $request->new_doc[$i];
			
				if(count($documentExistFolio) > 0)
				{	
					if($request->new_doc[$i] == 1)
					{
						foreach ($documentExistFolio as $doc)
						{	
							if($request->fiscal_folio[$i] != "" &&  $request->fiscal_folio[$i] == $doc->fiscal_folio)
							{
								if($tempDatepath == $doc->datepath && $tempTimepath == $doc->timepath)
								{
									$position[] = $i;
								}
							}
							
							if($request->ticket_number[$i] != "" && $request->ticket_number[$i] == $doc->ticket_number)
							{
								if($tempDatepath == $doc->datepath && $tempTimepath == $doc->timepath && $request->amount[$i] == $doc->amount)
								{
									$position[] = $i;
								}
							}
						}
						$check_docs = App\Functions\DocsValidate::validate($options,$folio);
						if($check_docs>0)
						{	
							if (isset($request->ticket_number[$i]) && $request->ticket_number[$i] != "") 
							{
								if(!in_array($i, $position))
								{
									$position[] = $i;
								}
							}
							if(isset($request->fiscal_folio[$i]) && $request->fiscal_folio[$i] != "")
							{
								if(!in_array($i, $position))
								{
									$position[] = $i;
								}
							}
						}
					}
				}
				else
				{
					$check_docs = App\Functions\DocsValidate::validate($options,$folio);
	
					if($check_docs>0)
					{
						if (isset($request->ticket_number[$i]) && $request->ticket_number[$i] != "") 
						{
							$position[] = $i;
						}
						if(isset($request->fiscal_folio[$i]) && $request->fiscal_folio[$i] != "")
						{
							$position[] = $i;
						}
					}
				}
			}
			return Response($position);
		}


		//
		// if($request->ajax())
		// {
		// 	$folio             = $request->requestFolio;
		// 	$check_docs_status = App\RequestModel::where('folio', $folio)->whereIn('status',[2])->count();
		// 	if($check_docs_status > 0)
		// 	{
		// 		$folio = $folio;
		// 	}
		// 	else
		// 	{
		// 		$folio = "";
		// 	}
		// 	$position = [];
		// 	for ($i=0; $i < count($request->amount); $i++)
		// 	{ 
		// 		$options               = [];
		// 		$options['fiscal_val'] = $request->fiscal_folio[$i];
		// 		$options['ticket_val'] = $request->ticket_number[$i];
		// 		$options['date']       = $request->datepath[$i];
		// 		$options['time']       = $request->timepath[$i];
		// 		$options['amount']     = $request->amount[$i];

		// 		$check_docs            = App\Functions\DocsValidate::validate($options,$folio);

		// 		if($check_docs>0)
		// 		{
		// 			if (isset($request->ticket_number[$i]) && $request->ticket_number[$i] != "") 
		// 			{
		// 				$position[] = $i;
		// 			}
		// 			if(isset($request->fiscal_folio[$i]) && $request->fiscal_folio[$i] != "")
		// 			{
		// 				$position[] = $i;
		// 			}
		// 		}
		// 	}
				
		// 	return Response($position);
		// }
	}

	// public function validationDocument(Request $request)
	// {
	// 	if($request->ajax())
	// 	{
	// 		$fiscal_val = $request->fiscal_value;
	// 		$ticket_val = $request->num_ticket;
	// 		$time_val   = $request->timepath;
	// 		$datepath   = $request->datepath;
	// 		$monto_val  = $request->monto;
	// 		$folio 		= $request->requestFolio;
			
	// 		$date = Carbon::parse($datepath)->format('Y-m-d');

	// 		$check_docs_status = App\RequestModel::where('folio', $folio)->whereIn('status',[2])->count();

	// 		if($check_docs_status>0)
	// 		{
	// 			$folio    = $folio;
	// 		}
	// 		else
	// 		{
	// 			$folio    = "";
	// 		}

	// 		if($fiscal_val!=''||$ticket_val!='')
	// 		{			
	// 			$check_docs = App\RefundDocuments::leftJoin('refundDetail','refundDetail.idRefundDetail','refundDocuments.idRefundDetail')
	// 				->leftJoin('refund','refundDetail.idRefund','refund.idRefund')
	// 				->leftJoin('request','refund.idFolio','request.folio')
	// 				->whereIn('request.status',[2,3,4,5,10,11,12,18])
	// 				->where(function($check) use ($fiscal_val, $date, $time_val,$ticket_val, $monto_val)
	// 				{
	// 					$check->where(function($query) use($fiscal_val, $date, $time_val)
	// 					{
	// 						$query->where('refundDocuments.fiscal_folio', $fiscal_val);
	// 						$query->where('refundDocuments.datepath', 'like', ''.$date.'%');
	// 						$query->where('refundDocuments.timepath','like' ,$time_val.'%');
	// 						$query->whereNotNull('refundDocuments.fiscal_folio');
	// 					})
	// 					->orWhere(function($query2) use($ticket_val, $date, $monto_val, $time_val)
	// 					{
	// 						$query2->where('refundDocuments.ticket_number', $ticket_val);
	// 						$query2->where('refundDocuments.datepath', 'like', ''.$date.'%');
	// 						$query2->where('refundDocuments.timepath','like' ,$time_val.'%');
	// 						$query2->where('refundDocuments.amount', $monto_val);
	// 						$query2->whereNotNull('refundDocuments.ticket_number');
	// 					});
	// 				})
	// 				->whereNotIn('request.folio', [$folio])
	// 				->count();

	// 			if($check_docs>0)
	// 			{
	// 				return Response('false');
	// 			}
	// 		}
	// 		return Response('true');
	// 	}
	// }
	public function validationAccount($request)
	{
		$accountExist = App\Employee::where('idBanks', $request->bankid)
						->where('visible', 1)
						->where(function($query) use ($request)
						{
							if ($request->card != "")
							{
								$query->orWhere('cardNumber', $request->card);
							}
							if ($request->clabe != "")
							{
								$query->orWhere('clabe', $request->clabe);
							}
							if ($request->account != "")
							{
								$query->orWhere('account', $request->account);
							}
						})->count();
		return $accountExist;
	}

	public function addBankAccount(Request $request)
	{
		if((Auth::user()->module->where('id',118)->count() > 0) || (Auth::user()->module->where('id',119)->count() > 0))
		{
			if ($request->ajax()) 
			{
				$validation = $this->validationAccount($request);
				if ($validation == 0)
				{
					$t_employee             = new App\Employee();
					$t_employee->alias      = $request->alias;
					$t_employee->clabe      = $request->clabe;
					$t_employee->account    = $request->account;
					$t_employee->cardNumber = $request->card;
					$t_employee->idBanks    = $request->bankid;
					$t_employee->idUsers    = $request->userid;
					$t_employee->visible    = 1;
					$t_employee->save();
				}
				return $validation;
			}
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',118)->count()>0)
		{
			$t_request               = new App\RequestModel();
			$t_request->kind         = 9;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 3;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->code_edt     = $request->code_edt;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();

			$folio               = $t_request->folio;
			$kind                = $t_request->kind;
			$t_refund            = new App\Refund();
			$t_refund->title     = $request->title;
			$t_refund->datetitle = Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d');
			$t_refund->idFolio   = $folio;
			$t_refund->idKind    = $kind;
			$t_refund->reference = $request->reference;
			$t_refund->currency  = $request->currency;

			if ($request->method == 1) 
			{
				$t_refund->idEmployee = $request->idEmployee;
			}
			else
			{
				$t_refund->idEmployee = null;
			}
			$t_refund->idpaymentMethod = $request->method;
			$t_refund->idUsers         = $request->user_id;
			$t_refund->save();

			$refund			= $t_refund->idRefund;
			$countAmount	= count($request->t_amount);
			$ivaParam		= App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
			$ivaParam2		= App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;

			$total				= 0;
			$taxes 				= 0;

			for($i = 0; $i < $countAmount; $i++)
			{
				$t_amount_tax		= 't_amount_tax'.$i;
				$t_name_tax			= 't_name_tax'.$i;
				$taxesConcept		= 0;
				$retentionsConcept	= 0;
				if(isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
				{
					for($d=0; $d < count($request->$t_amount_tax); $d++) 
					{
						if($request->$t_amount_tax[$d] != "")
						{
							$taxesConcept += $request->$t_amount_tax[$d];
						}
					}
				}

				$t_amount_retention	= 't_amount_retention'.$i;
				$t_name_retention	= 't_name_retention'.$i;
				if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
				{
					for ($d=0; $d < count($request->$t_amount_retention); $d++) 
					{ 
						if ($request->$t_amount_retention[$d] != "") 
						{
							$retentionsConcept += $request->$t_amount_retention[$d];
						}
					}
				}
				if($request->t_iva_kind[$i] == "done") 
				{
					$ivaCalc = $request->t_ivatotal[$i];
				}
				else
				{
					$ivaCalc = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;
				}
				$t_detailRefund             = new App\RefundDetail();
				$t_detailRefund->idRefund   = $refund;
				$t_detailRefund->concept    = $request->t_concept[$i];
				$t_detailRefund->amount     = $request->t_amount[$i];
				$t_detailRefund->idAccount  = $request->t_account[$i];
				$t_detailRefund->taxPayment = $request->t_fiscal[$i]== "si" ? 1 : 0;
				$t_detailRefund->tax        = $ivaCalc;
				$t_detailRefund->sAmount    = ($request->t_amount[$i]+$ivaCalc+$taxesConcept)-$retentionsConcept;
				$t_detailRefund->typeTax    = $request->tivakind[$i];
				$t_detailRefund->save();
				$total            += $t_detailRefund->sAmount;
				$idRD             = $t_detailRefund->idRefundDetail;
				$tempPath         = 't_path'.$i;
				$tempDatePath     = 't_datepath'.$i;
				$tempNew          = 't_new'.$i;
				$tempFiscalFolio  = 't_fiscal_folio'.$i;
				$tempTicketNumber = 't_ticket_number'.$i;
				$tempAmount       = 't_amount'.$i;
				$tempTime         = 't_timepath'.$i;
				$tempNameDoc      = 't_name_doc'.$i;

				if(isset($request->$tempPath) && $request->$tempPath != '')
				{	
					for($d = 0; $d < count($request->$tempPath); $d++) 
					{
						$doc         = $request->$tempPath;
						$new         = $request->$tempNew;
						$t_documents = new App\RefundDocuments();
						if($new[$d] == 1)
						{
							$new_file_name     = Files::rename($doc[$d],$folio);
							$t_documents->path = $new_file_name;
						}
						else
						{
							$extention     = explode('.', $doc[$d]);
							$destinityName = 'AdG'.round(microtime(true) * 1000).'_refundDoc.'.$extention[1];
							$destinity     = '/docs/refounds/'.$destinityName;
							$origin        = '/docs/refounds/'.$doc[$d];
							\Storage::disk('public')->copy($origin,$destinity);
							$new_file_name     = Files::rename($destinityName,$folio);
							$t_documents->path = $new_file_name;
						}
						
						$t_documents->date           	= Carbon::now()->format('Y-m-d');
						$t_documents->idRefundDetail 	= $idRD;
						$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
						$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
						$t_documents->amount			= $request->$tempAmount[$d];
						$t_documents->timepath			= $request->$tempTime[$d];
						$t_documents->datepath			= Carbon::createFromFormat('d-m-Y', $request->$tempDatePath[$d])->format('Y-m-d');
						$t_documents->name				= $request->$tempNameDoc[$d];
						$t_documents->save();
					}
				}
				if(isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
				{ 
					for($d = 0; $d < count($request->$t_amount_tax); $d++)
					{ 
						if($request->$t_amount_tax[$d] != "")
						{ 
							$taxes                   += $request->$t_amount_tax[$d];
							$t_taxes                 = new App\TaxesRefund();
							$t_taxes->name           = $request->$t_name_tax[$d];
							$t_taxes->amount         = $request->$t_amount_tax[$d];
							$t_taxes->idRefundDetail = $idRD;
							$t_taxes->save();
						}
					}
				}
				
				if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
				{
					for ($d=0; $d < count($request->$t_amount_retention); $d++) 
					{ 
						if ($request->$t_amount_retention[$d] != "") 
						{
							$t_retention 					= new App\RefundRetentions();
							$t_retention->name 				= $request->$t_name_retention[$d];
							$t_retention->amount 			= $request->$t_amount_retention[$d];
							$t_retention->idRefundDetail	= $idRD;
							$t_retention->save();
						}
					}
				}
			}

			$subtotal = $t_request->refunds->first()->refundDetail->sum('amount');
			$ivaTotal = $t_request->refunds->first()->refundDetail->sum('tax');

			$taxesTotal = 0;
			$retentionsTotal = 0;
			foreach ($t_request->refunds->first()->refundDetail as $key => $value) 
			{
				$taxesTotal += $value->taxes->sum('amount');
				$retentionsTotal += $value->retentions->sum('amount');
			}

			$t_refund->total = ($subtotal + $ivaTotal + $taxesTotal) - $retentionsTotal;
			$t_refund->save();

			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 120);
				})
				->whereHas('inChargeDepGet',function($q) use($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',120);
				})
				->whereHas('inChargeEntGet',function($q) use($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',120);
				})
				->whereHas('inChargeProjectGet',function($q) use($t_request)
				{
					$q->where('project_id', $t_request->idProject)
						->where('module_id',120);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->user_id);
			if($emails != "")
			{
				try
				{
					foreach($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Reembolso";
						$status      = "Revisar";
						$date        = Carbon::now();
						$url         = route('refund.review.edit',['id'=>$folio]);
						$subject     = "Solicitud por Revisar";
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/refund')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',119)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',119)->count()>0)
			{
				$global_permission = Auth::user()->globalCheck->where('module_id',119)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$title_shear  = $request->title_shear;
			$project      = $request->project;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			
			$requests     = App\RequestModel::where('kind',9)
				->where(function($query)
				{
					$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(119)->pluck('enterprise_id'))
						->orWhereNull('idEnterprise');
				})
				->where(function($query)
				{
					$query->whereIn('idDepartment',Auth::user()->inChargeDep(119)->pluck('departament_id'))
						->orWhereNull('idDepartment');
				})
				->where(function($query)
				{
					$query->whereIn('idProject',Auth::user()->inChargeProject(119)->pluck('project_id'))
						->orWhereNull('idProject');
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($name, $mindate, $maxdate, $folio, $status, $enterpriseid, $title_shear, $project)
				{
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($title_shear != "")
					{
						$query->whereHas('refunds',function ($sql) use ($title_shear){
							$sql->where('title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
						});
					}
					if($project != "")
					{
						$query->whereHas('requestProject',function ($sql) use ($project){
							$sql->where('idproyect',$project);
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->where('request_models.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);

			return view('administracion.reembolso.busqueda',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 119,
					'requests'     => $requests,
					'folio'        => $folio,
					'name'         => $name,
					'title_shear'  => $title_shear,
					'project'      => $project,
					'status'       => $status,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'enterpriseid' => $enterpriseid
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getResource(Request $request)
	{
		if($request->ajax())
		{
			$response = array();
			foreach (App\RequestModel::where('kind',8)->where('status',5)->where('idRequest',$request->user)->get() as $key => $value)
			{
				if($value->resource->first()->refundRequest->count() == 0)
				{
					$response[] = $value->folio;
				}
				else
				{
					$flag = true;
					foreach ($value->resource->first()->refundRequest as $key => $refunds)
					{
						if($refunds->requestModel->status!=2 && $refunds->requestModel->status!=6 && $refunds->requestModel->status!=7)
						{
							$flag = false;
						}
					}
					if($flag)
					{
						$response[] = $value->folio;
					}
				}
			}
			return Response($response);
		}
	}
	
	public function unsent(Request $request)
	{			
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$t_request               = new App\RequestModel();
			$t_request->kind         = 9;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = "2";
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->code_edt     = $request->code_edt;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->save();

			$folio               = $t_request->folio;
			$kind                = $t_request->kind;

			$t_refund            = new App\Refund();
			$t_refund->title     = $request->title;
			$t_refund->datetitle = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_refund->idFolio   = $folio;
			$t_refund->idKind    = $kind;
			$t_refund->reference = $request->reference;
			$t_refund->currency  = $request->currency;
			if($request->method == 1)
			{
				$t_refund->idEmployee = $request->idEmployee;
			}
			else
			{
				$t_refund->idEmployee = null;
			}
			$t_refund->idpaymentMethod = $request->method;
			$t_refund->idUsers         = $request->user_id;
			$t_refund->save();

			$refund				= $t_refund->idRefund;
			$total				= 0;
			$taxes 				= 0;

			if($request->t_amount != "")
			{
				$countAmount = count($request->t_amount);
				$ivaParam    = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
				$ivaParam2   = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
				for($i = 0; $i < $countAmount; $i++)
				{
					$t_amount_tax		= 't_amount_tax'.$i;
					$t_name_tax			= 't_name_tax'.$i;
					$taxesConcept		= 0;
					$retentionsConcept	= 0;
					if(isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
					{
						for($d=0; $d < count($request->$t_amount_tax); $d++)
						{
							if($request->$t_amount_tax[$d] != "")
							{
								$taxesConcept += $request->$t_amount_tax[$d];
							}
						}
					}
					$t_amount_retention	= 't_amount_retention'.$i;
					$t_name_retention	= 't_name_retention'.$i;
					if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
					{
						for ($d=0; $d < count($request->$t_amount_retention); $d++) 
						{ 
							if ($request->$t_amount_retention[$d] != "") 
							{
								$retentionsConcept += $request->$t_amount_retention[$d];
							}
						}
					}
					if($request->t_iva_kind[$i] == "done")
					{
						$ivaCalc = $request->t_ivatotal[$i];
					}
					else
					{
						$ivaCalc = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;
					}
					$t_detailRefund             = new App\RefundDetail();
					$t_detailRefund->idRefund   = $refund;
					$t_detailRefund->concept    = $request->t_concept[$i];
					$t_detailRefund->amount     = $request->t_amount[$i];
					$t_detailRefund->idAccount  = $request->t_account[$i];
					$t_detailRefund->taxPayment = $request->t_fiscal[$i]== "si" ? 1 : 0;
					$t_detailRefund->tax        = $ivaCalc;
					$t_detailRefund->sAmount    = ($request->t_amount[$i]+$ivaCalc+$taxesConcept)-$retentionsConcept;
					$t_detailRefund->typeTax    = $request->tivakind[$i];
					$t_detailRefund->save();

					$total				+= $t_detailRefund->sAmount;
					$idRD				= $t_detailRefund->idRefundDetail;
					$tempPath			= 't_path'.$i;
					$tempNew			= 't_new'.$i;
					$tempFiscalFolio	= 't_fiscal_folio'.$i;
					$tempTicketNumber	= 't_ticket_number'.$i;
					$tempAmount			= 't_amount'.$i;
					$tempTimepath		= 't_timepath'.$i;
					$tempDatepath		= 't_datepath'.$i;
					$tempNameDoc		= 't_name_doc'.$i;
					

					if(isset($request->$tempPath))
					{
						for ($d=0; $d < count($request->$tempPath); $d++)
						{
							$doc		= $request->$tempPath;
							$new		= $request->$tempNew;
							$t_documents = new App\RefundDocuments();
							if($new[$d] == 1)
							{
								$new_file_name     = Files::rename($doc[$d],$folio);
								$t_documents->path = $new_file_name;
							}
							else
							{
								$extention         = explode('.', $doc[$d]);
								$destinityName     = 'AdG'.round(microtime(true) * 1000).'_refundDoc.'.$extention[1];
								$destinity         = '/docs/refounds/'.$destinityName;
								$origin            = '/docs/refounds/'.$doc[$d];
								\Storage::disk('public')->copy($origin,$destinity);
								$new_file_name     = Files::rename($destinityName,$folio);
								$t_documents->path = $new_file_name;
							}

							$t_documents->date				= Carbon::now()->format('Y-m-d');
							$t_documents->idRefundDetail 	= $idRD;
							$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
							$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
							$t_documents->amount			= $request->$tempAmount[$d];
							$t_documents->timepath			= $request->$tempTimepath[$d];
							$t_documents->datepath			= Carbon::createFromFormat('d-m-Y', $request->$tempDatepath[$d])->format('Y-m-d');
							$t_documents->name				= $request->$tempNameDoc[$d];
							$t_documents->save();
						}
					}

					if (isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
					{
						for($d = 0; $d < count($request->$t_amount_tax); $d++) 
						{ 
							if ($request->$t_amount_tax[$d] != "") 
							{
								$taxes                   += $request->$t_amount_tax[$d];
								$t_taxes                 = new App\TaxesRefund();
								$t_taxes->name           = $request->$t_name_tax[$d];
								$t_taxes->amount         = $request->$t_amount_tax[$d];
								$t_taxes->idRefundDetail = $idRD;
								$t_taxes->save();
							}
						}
					}
					
					
					if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
					{
						for ($d=0; $d < count($request->$t_amount_retention); $d++) 
						{ 
							if ($request->$t_amount_retention[$d] != "") 
							{
								$t_retention 					= new App\RefundRetentions();
								$t_retention->name 				= $request->$t_name_retention[$d];
								$t_retention->amount 			= $request->$t_amount_retention[$d];
								$t_retention->idRefundDetail 	= $idRD;
								$t_retention->save();
							}
						}
					}
				}
			}
			
			$subtotal = $t_request->refunds->first()->refundDetail->sum('amount');
			$ivaTotal = $t_request->refunds->first()->refundDetail->sum('tax');

			$taxesTotal = 0;
			$retentionsTotal = 0;
			foreach ($t_request->refunds->first()->refundDetail as $key => $value) 
			{
				$taxesTotal += $value->taxes->sum('amount');
				$retentionsTotal += $value->retentions->sum('amount');
			}

			$t_refund->total = ($subtotal + $ivaTotal + $taxesTotal) - $retentionsTotal;
			$t_refund->save();

			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('refund.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',119)->count() > 0)
		{
			if(Auth::user()->globalCheck->where('module_id',119)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',119)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data        = App\Module::find($this->module_id); 
			$enterprises = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(119)->pluck('enterprise_id'))->get();
			$areas       = App\Area::where('status','ACTIVE')->get(); 
			$departments = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(119)->pluck('departament_id'))->get(); 
			$projects    = App\Project::whereIn('idproyect',Auth::user()->inChargeProject(119)->pluck('project_id'))->get();
			$request 	= App\RequestModel::where('kind',9)
						->where(function ($q) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
							}
						})
						->find($id);
			$iva    = App\Parameter::where('parameter_name','IVA')->get();
			$labels = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			if($request != "") 
			{
				return view('administracion.reembolso.seguimiento',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 119,
						'projects'    => $projects, 
						'enterprises' => $enterprises,
						'areas'       => $areas,
						'departments' => $departments,
						'request'     => $request,
						'iva'         => $iva,
						'labels'      => $labels
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

	public function updateFollow(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$t_request               = App\RequestModel::find($id);
			$t_request->kind         = 9;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 3;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->code_edt     = $request->code_edt;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->save();
			$Refund = App\Refund::where('idFolio',$t_request->folio)
				->where('idKind',$t_request->kind)
				->get();
			$folio = $t_request->folio;
			foreach($Refund as $key => $value)
			{
				$idRefund = $value->idRefund;
			}
			$t_refund            = App\Refund::find($idRefund);
			$t_refund->title     = $request->title;
			$t_refund->datetitle = Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d');
			$t_refund->reference = $request->reference;
			$t_refund->currency  = $request->currency;
			if ($request->method == 1) 
			{
				$t_refund->idEmployee = $request->idEmployee;
			}
			else
			{
				$t_refund->idEmployee = null;
			}
			$t_refund->idpaymentMethod = $request->method;
			$t_refund->idUsers         = $request->user_id;
			$total                     = $t_refund->total;
			$taxes                     = 0;
			if(isset($request->delete))
			{
				if ($request->delete[0] != "")
				{
					for ($i=0; $i < count($request->delete); $i++) 
					{ 
						if (App\RefundDetail::where('idRefundDetail',$request->delete[$i])->count()>0) 
						{
							$filesDeleted = App\RefundDocuments::where('idRefundDetail',$request->delete[$i])->get();
							foreach ($filesDeleted as $k => $v)
							{
								\Storage::disk('public')->delete('/docs/refounds/'.$v->path);
							}
							$del2  = App\RefundDocuments::where('idRefundDetail',$request->delete[$i])->delete();
							App\TaxesRefund::where('idRefundDetail',$request->delete[$i])->delete();
							App\RefundRetentions::where('idRefundDetail',$request->delete[$i])->delete();
							$total -= App\RefundDetail::find($request->delete[$i])->sAmount;
							$del1  = App\RefundDetail::where('idRefundDetail',$request->delete[$i])->delete();	
						}
					}
				}
			}
			$ivaParam  = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
			$ivaParam2 = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
			if (isset($request->t_amount)) 
			{
				$countAmount = count($request->t_amount);
				for ($i=0; $i < $countAmount; $i++)
				{
					if ($request->idRDe[$i] == "x") 
					{
						if ($request->t_iva_kind[$i]=="done") 
						{
							$ivaCalc = $request->t_ivatotal[$i];
						}
						else
						{
							$ivaCalc = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;
						}

						$t_amount_tax		= 't_amount_tax'.$i;
						$t_name_tax			= 't_name_tax'.$i;
						$taxesConcept		= 0;
						$retentionsConcept	= 0;
						if (isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
						{
							for ($d=0; $d < count($request->$t_amount_tax); $d++) 
							{ 
								if ($request->$t_amount_tax[$d] != "") 
								{
									$taxesConcept += $request->$t_amount_tax[$d];
								}
							}
						}

						$t_amount_retention	= 't_amount_retention'.$i;
						$t_name_retention	= 't_name_retention'.$i;
						if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
						{
							for ($d=0; $d < count($request->$t_amount_retention); $d++) 
							{ 
								if ($request->$t_amount_retention[$d] != "") 
								{
									$retentionsConcept += $request->$t_amount_retention[$d];
								}
							}
						}

						$t_detailRefund             = new App\RefundDetail();
						$t_detailRefund->idRefund   = $idRefund;
						$t_detailRefund->concept    = $request->t_concept[$i];
						$t_detailRefund->amount     = $request->t_amount[$i];
						$t_detailRefund->idAccount  = $request->t_account[$i];
						$t_detailRefund->taxPayment = $request->t_fiscal[$i] == "si" ? 1 : 0;
						$t_detailRefund->tax        = $ivaCalc;
						$t_detailRefund->sAmount    = ($request->t_amount[$i]+$ivaCalc+$taxesConcept)-$retentionsConcept;
						$t_detailRefund->typeTax    = $request->tivakind[$i];
						$t_detailRefund->save();
						$total                      += $t_detailRefund->sAmount;
						$idRD                       = $t_detailRefund->idRefundDetail;
						$tempPath                   = 't_path'.$i;
						$tempFiscalFolio            = 't_fiscal_folio'.$i;
						$tempTicketNumber           = 't_ticket_number'.$i;
						$tempAmount                 = 't_amount'.$i;
						$tempTimepath               = 't_timepath'.$i;
						$tempDatepath               = 't_datepath'.$i;
						$tempNameDoc                = 't_name_doc'.$i;
						if (isset($request->$tempPath))
						{
							for ($d=0; $d < count($request->$tempPath); $d++) 
							{
								$doc                         = $request->$tempPath;
								$t_documents                 = new App\RefundDocuments();
								$new_file_name               = Files::rename($doc[$d],$folio);
								$t_documents->path           = $new_file_name;
								$t_documents->idRefundDetail = $idRD;
								$date                        = new \DateTime($request->$tempDatepath[$d]);
								$newdate                     = $date->format('Y-m-d');
								$t_documents->date           = Carbon::now()->format('Y-m-d');
								$t_documents->fiscal_folio   = $request->$tempFiscalFolio[$d];
								$t_documents->ticket_number  = $request->$tempTicketNumber[$d];
								$t_documents->amount         = $request->$tempAmount[$d];
								$t_documents->timepath       = $request->$tempTimepath[$d];
								$t_documents->datepath       = $newdate;
								$t_documents->name           = $request->$tempNameDoc[$d];
								$t_documents->save();
							}
						}
						if (isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
						{
							for ($d=0; $d < count($request->$t_amount_tax); $d++) 
							{ 
								if ($request->$t_amount_tax[$d] != "") 
								{
									$taxes                   += $request->$t_amount_tax[$d];
									$t_taxes                 = new App\TaxesRefund();
									$t_taxes->name           = $request->$t_name_tax[$d];
									$t_taxes->amount         = $request->$t_amount_tax[$d];
									$t_taxes->idRefundDetail = $idRD;
									$t_taxes->save();
								}
							}
						}

						if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
						{
							for ($d=0; $d < count($request->$t_amount_retention); $d++) 
							{ 
								if ($request->$t_amount_retention[$d] != "") 
								{
									$t_retention                 = new App\RefundRetentions();
									$t_retention->name           = $request->$t_name_retention[$d];
									$t_retention->amount         = $request->$t_amount_retention[$d];
									$t_retention->idRefundDetail = $idRD;
									$t_retention->save();
								}
							}
						}
					}
				}
			}

			$subtotal = $t_request->refunds->first()->refundDetail->sum('amount');
			$ivaTotal = $t_request->refunds->first()->refundDetail->sum('tax');

			$taxesTotal = 0;
			$retentionsTotal = 0;
			foreach ($t_request->refunds->first()->refundDetail as $key => $value) 
			{
				$taxesTotal += $value->taxes->sum('amount');
				$retentionsTotal += $value->retentions->sum('amount');
			}

			$t_refund->total = ($subtotal + $ivaTotal + $taxesTotal) - $retentionsTotal;
			$t_refund->save();

			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 120);
				})
				->whereHas('inChargeDepGet',function($q) use($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',120);
				})
				->whereHas('inChargeEntGet',function($q) use($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',120);
				})
				->whereHas('inChargeProjectGet',function($q) use($t_request)
				{
					$q->where('project_id', $t_request->idProject)
						->where('module_id',120);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->user_id);
			if ($emails != "" && $t_request->refunds->first()->idRequisition == "") 
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Reembolso";
						$status      = "Revisar";
						$date        = Carbon::now();
						$url         = route('refund.review.edit',['id'=>$id]);
						$subject     = "Solicitud por Revisar";
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			if($t_request->refunds->first()->idRequisition != "")
			{
				$review                   = App\RequestModel::find($id);
				$review->status           = 5;
				$review->idEnterpriseR    = $request->enterprise_id;;
				$review->idDepartamentR   = $request->department_id;;
				$review->code_edt         = $request->code_edt;
				$review->code_wbs         = $request->code_wbs;
				$review->idAreaR          = $request->area_id;;
				$review->idProjectR       = $request->project_id;;
				$review->idCheck          = Auth::user()->id;
				$review->checkComment     = "";
				$review->reviewDate       = Carbon::now();
				$review->idAuthorize      = Auth::user()->id;
				$review->authorizeComment = "";
				$review->authorizeDate    = Carbon::now();
				$review->save();
				foreach (App\RefundDetail::where('idRefund',$review->refunds->first()->idRefund)->get() as $t_refundDetail)
				{
					$t_refundDetail->idAccountR = $t_refundDetail->idAccount;
					$t_refundDetail->save();
				}
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
				$emailRequest = "";
				$emailRequest = App\User::where('id',$review->idElaborate)
					->where('notification',1)
					->get();
				$emailPay = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',90)
					->where('users.active',1)
					->where('users.notification',1)
					->get();
				$user = App\User::find($review->idRequest);
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to   = $email->email;
							$kind = "Reembolso";
							if ($review->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date        = Carbon::now();
							$url         = route('refund.follow.edit',['id'=>$id]);
							$subject     = "Estado de Solicitud";
							$requestUser = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert = "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}
				if ($review->status == 5)
				{
					if ($emailPay != "") 
					{
						try
						{
							foreach ($emailPay as $email) 
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Reembolso";
								$status      = "Pendiente";
								$date        = Carbon::now();
								$url         = route('payments.review.edit',['id'=>$id]);
								$subject     = "Estado de Solicitud";
								$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert = "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return redirect('administration/refund')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateUnsentFollow(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$t_request               = App\RequestModel::find($id);
			$t_request->kind         = 9;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 2;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->code_edt     = $request->code_edt;
			$t_request->code_wbs     = $request->code_wbs;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->save();
			$Refund = App\Refund::where('idFolio',$t_request->folio)
				->where('idKind',$t_request->kind)
				->get();
			$folio = $t_request->folio;
			foreach ($Refund as $key => $value)
			{
				$idRefund = $value->idRefund;
			}
			$t_refund            = App\Refund::find($idRefund);
			$t_refund->title     = $request->title;
			$t_refund->datetitle = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_refund->reference = $request->reference;
			$t_refund->currency  = $request->currency;
			if ($request->method == 1) 
			{
				$t_refund->idEmployee = $request->idEmployee;
			}
			else
			{
				$t_refund->idEmployee = null;
			}
			$t_refund->idpaymentMethod = $request->method;
			$t_refund->idUsers         = $request->user_id;
			$total                     = $t_refund->total;
			$taxes                     = 0;
			if(isset($request->delete))
			{
				if ($request->delete[0] != "")
				{
					for ($i=0; $i < count($request->delete); $i++)
					{ 
						if (App\RefundDetail::where('idRefundDetail',$request->delete[$i])->count()>0) 
						{
							$filesDeleted = App\RefundDocuments::where('idRefundDetail',$request->delete[$i])->get();
							foreach ($filesDeleted as $k => $v)
							{
								\Storage::disk('public')->delete('/docs/refounds/'.$v->path);
							}
							$del2  = App\RefundDocuments::where('idRefundDetail',$request->delete[$i])->delete();
							App\TaxesRefund::where('idRefundDetail',$request->delete[$i])->delete();
							App\RefundRetentions::where('idRefundDetail',$request->delete[$i])->delete();
							$total -= App\RefundDetail::find($request->delete[$i])->sAmount;
							$del1  = App\RefundDetail::where('idRefundDetail',$request->delete[$i])->delete();
						}
					}
				}
			}
			
			if($request->t_amount != "")
			{
				$countAmount = count($request->t_amount);
				$ivaParam    = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
				$ivaParam2   = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
				for ($i=0; $i < $countAmount; $i++)
				{
					if ($request->idRDe[$i] == "x")
					{
						$ivaCalc			= $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;
						$t_amount_tax		= 't_amount_tax'.$i;
						$t_name_tax			= 't_name_tax'.$i;
						$taxesConcept		= 0;
						$retentionsConcept	= 0;
						if (isset($request->$t_amount_tax) && count($request->$t_amount_tax) > 0) 
						{
							for ($d=0; $d < count($request->$t_amount_tax); $d++) 
							{
								if ($request->$t_amount_tax[$d] != "")
								{
									$taxesConcept += $request->$t_amount_tax[$d];
								}
							}
						}

						$t_amount_retention	= 't_amount_retention'.$i;
						$t_name_retention	= 't_name_retention'.$i;
						if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
						{
							for ($d=0; $d < count($request->$t_amount_retention); $d++) 
							{ 
								if ($request->$t_amount_retention[$d] != "") 
								{
									$retentionsConcept += $request->$t_amount_retention[$d];
								}
							}
						}
						$t_detailRefund             = new App\RefundDetail();
						$t_detailRefund->idRefund   = $idRefund;
						$t_detailRefund->concept    = $request->t_concept[$i];
						$t_detailRefund->amount     = $request->t_amount[$i];
						$t_detailRefund->idAccount  = $request->t_account[$i];
						$t_detailRefund->taxPayment = $request->t_fiscal[$i]== "si" ? 1 : 0;
						$t_detailRefund->tax        = $ivaCalc;
						$t_detailRefund->sAmount    = ($request->t_amount[$i]+$ivaCalc+$taxesConcept)-$retentionsConcept;
						$t_detailRefund->typeTax    = $request->tivakind[$i];
						$t_detailRefund->save();
						$total                      += $t_detailRefund->sAmount;
						$idRD                       = $t_detailRefund->idRefundDetail;
						$tempPath                   = 't_path'.$i;
						$tempFiscalFolio            = 't_fiscal_folio'.$i;
						$tempTicketNumber           = 't_ticket_number'.$i;
						$tempAmount                 = 't_amount'.$i;
						$tempTimepath               = 't_timepath'.$i;
						$tempDatepath               = 't_datepath'.$i;
						$tempNameDoc                = 't_name_doc'.$i;
						if (isset($request->$tempPath))
						{
							for ($d=0; $d < count($request->$tempPath); $d++) 
							{
								$doc                         = $request->$tempPath;
								$t_documents                 = new App\RefundDocuments();
								$new_file_name               = Files::rename($doc[$d],$folio);
								$t_documents->path           = $new_file_name;
								$t_documents->idRefundDetail = $idRD;
								$t_documents->date           = Carbon::now()->format('Y-m-d');
								$t_documents->fiscal_folio   = $request->$tempFiscalFolio[$d];
								$t_documents->ticket_number  = $request->$tempTicketNumber[$d];
								$t_documents->amount         = $request->$tempAmount[$d];
								$t_documents->timepath       = $request->$tempTimepath[$d];
								$t_documents->datepath       = Carbon::createFromFormat('d-m-Y', $request->$tempDatepath[$d])->format('Y-m-d');
								$t_documents->name           = $request->$tempNameDoc[$d];
								$t_documents->save();
							}
						}
						if (isset($request->$t_amount_tax) && $request->$t_amount_tax != "") 
						{
							for ($d=0; $d < count($request->$t_amount_tax); $d++) 
							{ 
								if ($request->$t_amount_tax[$d] != "") 
								{
									$taxes                   += $request->$t_amount_tax[$d];
									$t_taxes                 = new App\TaxesRefund();
									$t_taxes->name           = $request->$t_name_tax[$d];
									$t_taxes->amount         = $request->$t_amount_tax[$d];
									$t_taxes->idRefundDetail = $idRD;
									$t_taxes->save();
								}
							}
						}	
						if (isset($request->$t_amount_retention) && $request->$t_amount_retention != "") 
						{
							for ($d=0; $d < count($request->$t_amount_retention); $d++) 
							{ 
								if ($request->$t_amount_retention[$d] != "") 
								{
									$t_retention                 = new App\RefundRetentions();
									$t_retention->name           = $request->$t_name_retention[$d];
									$t_retention->amount         = $request->$t_amount_retention[$d];
									$t_retention->idRefundDetail = $idRD;
									$t_retention->save();
								}
							}
						}
					}
				}
			}

			$subtotal = $t_request->refunds->first()->refundDetail->sum('amount');
			$ivaTotal = $t_request->refunds->first()->refundDetail->sum('tax');

			$taxesTotal = 0;
			$retentionsTotal = 0;
			foreach ($t_request->refunds->first()->refundDetail as $key => $value) 
			{
				$taxesTotal += $value->taxes->sum('amount');
				$retentionsTotal += $value->retentions->sum('amount');
			}

			$t_refund->total = ($subtotal + $ivaTotal + $taxesTotal) - $retentionsTotal;
			$t_refund->save();

			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('refund.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',120)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$title_shear  = $request->title_shear;
			$project      = $request->project;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;

			$requests = App\RequestModel::where('kind',9)
				->where('status',3)
				->whereIn('idDepartment',Auth::user()->inChargeDep(120)->pluck('departament_id'))
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(120)->pluck('enterprise_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(120)->pluck('project_id'))
				->where(function ($query) use ($name, $mindate, $maxdate, $folio, $enterpriseid, $title_shear, $project)
				{
					if ($enterpriseid != "") 
					{
						$query->where('request_models.idEnterprise',$enterpriseid);
					}
					if($name != "")
					{
						$query->where(function($query) use($name)
						{
							$query->whereHas('requestUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							})
							->orWhereHas('elaborateUser', function($q2) use($name)
							{
								$q2->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							});
						});
					}
					if($title_shear != "")
					{
						$query->whereHas('refunds',function ($sql) use ($title_shear)
						{
							$sql->where('title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
						});
					}
					if($project != "")
					{
						$query->whereHas('requestProject',function ($sql) use ($project)
						{
							$sql->where('idproyect',$project);
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
				
			return response(
				view('administracion.reembolso.revision',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 120,
						'requests'	=> $requests,
						'title_shear'=> $title_shear,
						'project'	=> $project,
						'folio'		=> $folio,
						'name'		=> $name,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(120), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',120)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$enterprises = App\Enterprise::where('status','ACTIVE')->get();
			$areas       = App\Area::where('status','ACTIVE')->get();
			$departments = App\Department::where('status','ACTIVE')->get();
			$labels      = App\Label::orderBy('description','asc')->get();
			$projects    = App\Project::all();
			$request     = App\RequestModel::where('kind',9)
				->where('status',3)
				->whereIn('idDepartment',Auth::user()->inChargeDep(120)->pluck('departament_id'))
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(120)->pluck('enterprise_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(120)->pluck('project_id'))
				->find($id);
			if ($request != "") 
			{
				return view('administracion.reembolso.revisioncambio',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 120,
						'enterprises' => $enterprises,
						'areas'       => $areas,
						'departments' => $departments,
						'request'     => $request,
						'labels'      => $labels,
						'projects'    => $projects
					]
				);
			}
			else
			{
				$alert 	= "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/refund/review')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				if ($request->status == "4")
				{
					$flag = false;
					for ($i=0; $i < count($request->t_idRefundDetail); $i++)
					{
						if ($request->t_idRefundDetail[$i] == null || $request->t_idRefundDetail[$i] == '') 
						{
							$flag = true;
						}
					}
					for ($i=0; $i < count($request->t_idAccountR); $i++)
					{
						if ($request->t_idAccountR[$i] == null || $request->t_idAccountR[$i] == '') 
						{
							$flag = true;
						}
					}
					if ($flag) 
					{
						$alert       = "swal('', 'Hay un error con la reclasificación de conceptos. Intente de nuevo.', 'error');";
						$data        = App\Module::find($this->module_id);
						$enterprises = App\Enterprise::where('status','ACTIVE')->get();
						$areas       = App\Area::where('status','ACTIVE')->get();
						$departments = App\Department::where('status','ACTIVE')->get();
						$labels      = App\Label::orderBy('description','asc')->get();
						$projects    = App\Project::all();
						$request     = App\RequestModel::where('kind',9)
							->where('status',3)
							->whereIn('idDepartment',Auth::user()->inChargeDep(120)->pluck('departament_id'))
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(120)->pluck('enterprise_id'))
							->whereIn('idProject',Auth::user()->inChargeProject(120)->pluck('project_id'))
							->find($id);
						return view('administracion.reembolso.revisioncambio',
								[
									'id'          => $data['father'],
									'title'       => $data['name'],
									'details'     => $data['details'],
									'child_id'    => $this->module_id,
									'option_id'   => 120,
									'enterprises' => $enterprises,
									'areas'       => $areas,
									'departments' => $departments,
									'request'     => $request,
									'labels'      => $labels,
									'projects'    => $projects,
									'alert'       => $alert
								]);
					}
					else
					{
						for ($i=0; $i < count($request->t_idRefundDetail); $i++) 
						{ 
							$t_refundDetail             = App\RefundDetail::find($request->t_idRefundDetail[$i]);
							$t_refundDetail->idAccountR = $request->t_idAccountR[$i];
							$t_refundDetail->save();
							$idLabelsAssign             = 'idLabelsAssign'.$i;
							if ($request->$idLabelsAssign != "") 
							{
								for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
								{ 
									$labelExpense                 = new App\LabelDetailRefund();
									$labelExpense->idlabels       = $request->$idLabelsAssign[$d];
									$labelExpense->idRefundDetail = $request->t_idRefundDetail[$i];
									$labelExpense->save();
								}
							}
						}
						$review                 = App\RequestModel::find($id);
						$review->status         = $request->status;
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
							$review->labels()->attach($request->idLabels,array('request_kind'=>'9'));
						}
						$emails = App\User::whereHas('module',function($q)
							{
								$q->where('id', 121);
							})
							->whereHas('inChargeDepGet',function($q) use($review)
							{
								$q->where('departament_id', $review->idDepartamentR)
									->where('module_id',121);
							})
							->whereHas('inChargeEntGet',function($q) use($review)
							{
								$q->where('enterprise_id', $review->idEnterpriseR)
									->where('module_id',121);
							})
							->whereHas('inChargeProjectGet',function($q) use($review)
							{
								$q->where('project_id', $review->idProjectR)
									->where('module_id',121);
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
									$kind        = "Reembolso";
									$status      = "Autorizar";
									$date        = Carbon::now();
									$url         = route('refund.authorization.edit',['id'=>$id]);
									$subject     = "Solicitud por Autorizar";
									$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
									Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
								}
								$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
							}
							catch(\Exception $e)
							{
								$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
							}
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review               = App\RequestModel::find($id);
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
								$kind        = "Reembolso";
								$status      = "RECHAZADA";
								$date        = Carbon::now();
								$url         = route('refund.follow.edit',['id'=>$id]);
								$subject     = "Estado de Solicitud";
								$requestUser = null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(120, $alert, 'administration/refund');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',121)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$project      = $request->project;
			$title_shear  = $request->title_shear;
			
			$requests = App\RequestModel::where('kind',9)
				->where('status',4)
				->whereIn('idDepartamentR',Auth::user()->inChargeDep(121)->pluck('departament_id'))
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(121)->pluck('enterprise_id'))
				->whereIn('idProjectR',Auth::user()->inChargeProject(121)->pluck('project_id'))
				->where(function ($query) use ($name, $mindate, $maxdate, $folio, $enterpriseid, $project, $title_shear)
				{
					if ($enterpriseid != "") 
					{
						$query->where('request_models.idEnterpriseR',$enterpriseid);
					}
					if($name != "")
					{
						$query->where(function($query) use($name)
						{
							$query->whereHas('requestUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							})
							->orWhereHas('elaborateUser', function($q2) use($name)
							{
								$q2->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							});
						});
					}
					if($title_shear != "")
					{
						$query->whereHas('refunds',function ($sql) use ($title_shear){
							$sql->where('title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
						});
					}
					if($project != "")
					{
						$query->whereHas('requestProject',function ($sql) use ($project){
							$sql->where('idproyect',$project);
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('reviewDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.reembolso.autorizacion',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 121,
						'requests'     => $requests,
						'folio'        => $folio,
						'name'         => $name,
						'mindate'      => $request->mindate,
						'project'      => $project,
						'title_shear'  => $title_shear,
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(121), 2880
			);
		}
		else
		{
			return redirect('/'); 
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',121)->count()>0) 
		{
			$data        = App\Module::find($this->module_id);
			$enterprises = App\Enterprise::where('status','ACTIVE')->get();
			$areas       = App\Area::where('status','ACTIVE')->get();
			$departments = App\Department::where('status','ACTIVE')->get();
			$labels      = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			$projects = App\Project::all();
			$request  = App\RequestModel::where('kind',9)
				->where('status',4)
				->whereIn('idDepartamentR',Auth::user()->inChargeDep(121)->pluck('departament_id'))
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(121)->pluck('enterprise_id'))
				->whereIn('idProjectR',Auth::user()->inChargeProject(121)->pluck('project_id'))
				->find($id);
			if ($request != "") 
			{
				return view('administracion.reembolso.autorizacioncambio',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 121,
						'enterprises' => $enterprises,
						'areas'       => $areas,
						'departments' => $departments,
						'request'     => $request,
						'labels'      => $labels,
						'projects'    => $projects
					]
				);
			}
			else
			{
				$alert 	= "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/refund/authorization')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7)
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$authorize                   = App\RequestModel::find($id);
				$authorize->status           = $request->status;
				$authorize->idAuthorize      = Auth::user()->id;
				$authorize->authorizeComment = $request->authorizeCommentA;
				$authorize->authorizeDate    = Carbon::now();
				$authorize->save();
				$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
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
				$emailPay = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',90)
					->where('users.active',1)
					->where('users.notification',1)
					->get();
				$user = App\User::find($authorize->idRequest);
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to   = $email->email;
							$kind = "Reembolso";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date        = Carbon::now();
							$url         = route('refund.follow.edit',['id'=>$id]);
							$subject     = "Estado de Solicitud";
							$requestUser = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert = "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}
				if ($request->status == 5)
				{
					if ($emailPay != "") 
					{
						try
						{
							foreach ($emailPay as $email) 
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Reembolso";
								$status      = "Pendiente";
								$date        = Carbon::now();
								$url         = route('payments.review.edit',['id'=>$id]);
								$subject     = "Estado de Solicitud";
								$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(121, $alert, 'administration/refund');
		}
	}

	public function getBanks(Request $request)
	{
		if ($request->ajax()) 
		{
			$banks 		= App\Employee::join('banks','employees.idBanks','banks.idBanks')
							->where('visible',1)
							->where('idUsers',$request->idUsers)
							->get();
			$countBanks = count($banks);
			if ($countBanks >= 1) 
			{
				$html = '';
				$body		= [];
				$modelBody	= [];
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.title-divisor',['classExContainer'=> 'mb-6','slot'=> 'SELECCIONE UNA CUENTA'])));
				$modelHead = 
				[
					[
						["value" => "Acción"],
						["value" => "Banco"],
						["value" => "Alias"],
						["value" => "Número de tarjeta"],
						["value" => "CLABE"],
						["value" => "Número de cuenta"],
					]
				];

				foreach ($banks as $bank) 
				{
					$alias      = $bank->alias!=null ? $bank->alias : '---';
					$cardNumber = $bank->cardNumber!=null ? $bank->cardNumber : '---';
					$clabe      = $bank->clabe!=null ? $bank->clabe : '---';
					$account    = $bank->account!=null ? $bank->account : '---';

					$body = 
					[
						"classEx" => "tr",
						[
							"content" =>
							[
								[
									"classExContainer" 	=> "inline-flex",
									"radio"				=> true,
									"kind"				=> "components.inputs.checkbox",
									"classEx"			=> "checkbox",
									"attributeEx"		=> "id=\"idEmp".$bank->idEmployee."\" name=\"idEmployee\" value=\"".$bank->idEmployee."\"",
									"label"				=> "<span class=\"icon-check\"></span>"
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label"   => $bank->description,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"bank[]\" placeholder=\"Ingrese un banco\" value=\"".$bank->description."\"",
									"classEx"		=> "input-extrasmall4"
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label"   => $alias,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"alias[]\" placeholder=\"Ingrese un alias\" value=\"".$bank->alias."\"",
									"classEx"		=> "input-extrasmall4"
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label"   => $cardNumber,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"card[]\" placeholder=\"Ingrese un número de tarjeta\" value=\"".$cardNumber."\"",
									"classEx"		=> "input-extrasmall4"
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label"   => $clabe,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\" placeholder=\"Ingrese una CLABE\" value=\"".$clabe."\"",
									"classEx"		=> "input-extrasmall4",
								],
							],
						],
						[
							"content" =>
							[
								[
									"kind"    => "components.labels.label",
									"label"   => $account,
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"account[]\" placeholder=\"Ingrese una cuenta bancaria\" value=\"".$account."\"",
									"classEx"		=> "input-extrasmall4",
								],
							],
						],
					];
					$modelBody[] = $body;
				}
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
					'attributeEx' 	=> 'id="table2"',
					'classEx' 		=> 'text-center',
					"modelBody"		=> $modelBody,
					"modelHead"		=> $modelHead,
					"themeBody"		=> "striped"
				])));;
				return Response($html);
			}
			else
			{
				//$notfound = '<div id="not-found" style="display:block;">NO HAY CUENTA REGISTRADA</div>';
				$notfound = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found",["text"=> "No se han encontrado cuentas registradas"])));
				return Response($notfound);
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
		
			if(isset($request->realPath) && $request->realPath !='')
			{
				\Storage::disk('public')->delete('/docs/refounds/'.$request->realPath);
			}
			if(isset($request->namesPaths) && $request->namesPaths!='')
			{	
				for ($i=0; $i < count($request->namesPaths); $i++) 
				{
					\Storage::disk('public')->delete('/docs/refounds/'.$request->namesPaths[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention            = strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention = 'AdG'.round(microtime(true) * 1000).'_refundDoc.';
				$name                 = $nameWithoutExtention.$extention;
				$destinity            = '/docs/refounds/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						if (!empty($request->path) && file_exists($request->path)) 
						{
							$sourceData	           = file_get_contents($request->path);
							$resultData	           = \Tinify\fromBuffer($sourceData)->toBuffer();
							\Storage::disk('public')->put($destinity,$resultData);
							$response['error']     = 'DONE';
							$response['path']      = $name;
							$response['message']   = '';
							$response['extention'] = $extention;
						}
						else
						{
							$response['message'] = 'Ocurrió un problema, por favor verifique su archivo.';
						}
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message'] = $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message'] = 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message'] = 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
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
						$response['error']     = 'DONE';
						$response['path']      = $name;
						$response['message']   = '';
						$response['extention'] = $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console'] = $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',119)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',119)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',119)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$project      = $request->project;
			$title_shear  = $request->title_shear;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			
			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio,
								refunds.title,
								IF(refunds.datetitle IS NULL,"No hay", DATE_FORMAT(refunds.datetitle, "%d-%m-%Y")) as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								refunds.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								refund_details.concept as conceptName,
								IF(refund_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								IF(refund_details.taxPayment=1,"Fiscal", "No Fiscal") as conceptFiscal,
								refund_details.amount as conceptAmount,
								refund_details.tax as conceptTax,
								IFNULL(taxes_refunds.amount_taxes,0) as conceptTaxes,
								refund_details.sAmount as conceptTotal,
								IFNULL(paymentsTemp.paymentsAmountReal,0) as amountPaid

							')
							->leftJoin('refunds', 'refunds.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'refunds.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'refunds.idEmployee')
							->leftJoin('refund_details', 'refund_details.idRefund', 'refunds.idRefund')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'refund_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'refund_details.idAccount')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('projects as requestProject', 'requestProject.idproyect', 'request_models.idProject')
							->leftJoin('projects as reviewedProject', 'reviewedProject.idproyect', 'request_models.idProjectR')
							->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as amount_taxes from taxes_refunds group by idRefundDetail) as taxes_refunds'),'taxes_refunds.idRefundDetail','refund_details.idRefundDetail')
							->leftJoin(DB::raw('(SELECT idFolio, SUM(amount_real) as paymentsAmountReal from payments group by idFolio) as paymentsTemp'),'paymentsTemp.idFolio','request_models.folio')
							->where('request_models.kind',9)
							->where(function($query)
							{
								$query->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(119)->pluck('enterprise_id'))	
									->orWhereNull('request_models.idEnterprise');
							})
							->where(function($query)
							{
								$query->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(119)->pluck('departament_id'))
									->orWhereNull('request_models.idDepartment');
							})
							->where(function($query)
							{
								$query->whereIn('request_models.idProject',Auth::user()->inChargeProject(119)->pluck('project_id'))
									->orWhereNull('request_models.idProject');
							})
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $title_shear, $project)
							{
								if ($enterpriseid != "") 
								{
									$query->where(function($queryE) use ($enterpriseid)
									{
										$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
									});
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								}
								if($title_shear != "")
								{
									$query->where('refunds.title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
								}
								if($project != "")
								{
									$query->where('requestProject.idproyect',$project);
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($status != "")
								{
									$query->where('request_models.status',$status);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
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
			$dateFormat   	= (new StyleBuilder())->setFormat('d-m-yy');
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-reembolso.xlsx');
			$writer->getCurrentSheet()->setName('Seguimiento');

			$headers = ['Reporte de seguimiento de reembolso','','','','','','','','','','', '', '', '', '', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación del gasto', 'Tipo fiscal', 'Subtotal por concepto', 'IVA', 'Impuestos adicionales', 'Total por concepto', 'Monto pagado'];
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
					$request->folio				= null;
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->status			= '';
					$request->date				= '';
					$request->total				= null;
					$request->paymentMethod 	= '';
					$request->bankName 			= '';
					$request->cardNumber 		= '';
					$request->clabe				= '';
					$request->account 			= '';
					$request->amountPaid 		= null;
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptAmount', 'conceptTax', 'conceptTotal', 'amountPaid', 'conceptTaxes']))
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
					// else if(in_array($k, ['date']))
					// {
					// 	$r = date("d-m-Y", $r);
					// 	$tmpArr[] = WriterEntityFactory::createCell($r);
					// }
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
		if(Auth::user()->module->where('id',120)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$project      = $request->project;
			$title_shear  = $request->title_shear;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio,
								refunds.title,
								IF(refunds.datetitle IS NULL,"No hay", DATE_FORMAT(refunds.datetitle, "%d-%m-%Y")) as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								refunds.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								refund_details.concept as conceptName,
								IF(refund_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								IF(refund_details.taxPayment=1,"Fiscal", "No Fiscal") as conceptFiscal,
								refund_details.amount as conceptAmount,
								refund_details.tax as conceptTax,
								IFNULL(taxes_refunds.amount_taxes,0) as conceptTaxes,
								refund_details.sAmount as conceptTotal
							')
							->leftJoin('refunds', 'refunds.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'refunds.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'refunds.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('projects as requestProject', 'requestProject.idproyect', 'request_models.idProject')
							->leftJoin('projects as reviewedProject', 'reviewedProject.idproyect', 'request_models.idProjectR')
							->leftJoin('refund_details', 'refund_details.idRefund', 'refunds.idRefund')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'refund_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'refund_details.idAccount')
							->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as amount_taxes from taxes_refunds group by idRefundDetail) as taxes_refunds'),'taxes_refunds.idRefundDetail','refund_details.idRefundDetail')
							->where('request_models.kind',9)
							->where('request_models.status',3)
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(120)->pluck('departament_id'))
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(120)->pluck('enterprise_id'))
							->whereIn('request_models.idProject',Auth::user()->inChargeProject(120)->pluck('project_id'))
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $title_shear, $project)
							{
								if ($enterpriseid != "") 
								{
									$query->where('request_models.idEnterprise',$enterpriseid);
								}
								if($name != "")
								{
									$query->where(function($q) use ($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborateUser.name,elaborateUser.last_name,elaborateUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
									});
								}
								if($title_shear != "")
								{
									$query->where('refunds.title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
								}
								if($project != "")
								{
									$query->where('requestProject.idproyect',$project);
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($status != "")
								{
									$query->where('request_models.status',$status);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);									
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();
			// Review
			// $requests = App\RequestModel::where('kind',9)
			// 	->where('status',3)
			// 	->whereIn('idDepartment',Auth::user()->inChargeDep(120)->pluck('departament_id'))
			// 	->whereIn('idEnterprise',Auth::user()->inChargeEnt(120)->pluck('enterprise_id'))
			// 	->whereIn('idProject',Auth::user()->inChargeProject(120)->pluck('project_id'))
			// 	->where(function ($query) use ($name, $mindate, $maxdate, $folio, $enterpriseid, $title_shear, $project)
			// 	{
			// 		if ($enterpriseid != "") 
			// 		{
			// 			$query->where('request_models.idEnterprise',$enterpriseid);
			// 		}
			// 		if($name != "")
			// 		{
			// 			$query->where(function($query) use($name)
			// 			{
			// 				$query->whereHas('requestUser', function($q) use($name)
			// 				{
			// 					$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
			// 				})
			// 				->orWhereHas('elaborateUser', function($q2) use($name)
			// 				{
			// 					$q2->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
			// 				});
			// 			});
			// 		}
			// 		if($title_shear != "")
			// 		{
			// 			$query->whereHas('refunds',function ($sql) use ($title_shear)
			// 			{
			// 				$sql->where('title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
			// 			});
			// 		}
			// 		if($project != "")
			// 		{
			// 			$query->whereHas('requestProject',function ($sql) use ($project)
			// 			{
			// 				$sql->where('idproyect',$project);
			// 			});
			// 		}
			// 		if($folio != "")
			// 		{
			// 			$query->where('request_models.folio',$folio);
			// 		}
			// 		if($mindate != "" && $maxdate != "")
			// 		{
			// 			$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
			// 		}
			// 	})
			// 	->orderBy('fDate','DESC')
			// 	->orderBy('folio','DESC')
			// 	->paginate(10);
			//


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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-reembolso.xlsx');
			$writer->getCurrentSheet()->setName('Revisión');

			$headers = ['Reporte de revisión de reembolso','','','','','','','','','','', '', '', '','','','','','', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación del gasto', 'Tipo fiscal', 'Subtotal por concepto', 'IVA', 'Impuestos adicionales', 'Total por concepto'];
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
					$request->folio				= null;
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->status			= '';
					$request->date				= '';
					$request->total				= null;
					$request->paymentMethod 	= '';
					$request->bankName 			= '';
					$request->cardNumber 		= '';
					$request->clabe				= '';
					$request->account 			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptTaxes', 'conceptTotal', 'conceptAmount', 'conceptTax']))
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
		if(Auth::user()->module->where('id',121)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$project      = $request->project;
			$title_shear  = $request->title_shear;
			
			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio,
								refunds.title,
								IF(refunds.datetitle IS NULL,"No hay", DATE_FORMAT(refunds.datetitle, "%d-%m-%Y")) as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								refunds.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								refund_details.concept as conceptName,
								IF(refund_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								IF(refund_details.taxPayment=1,"Fiscal", "No Fiscal") as conceptFiscal,
								refund_details.amount as conceptAmount,
								refund_details.tax as conceptTax,
								IFNULL(taxes_refunds.amount_taxes,0) as conceptTaxes,
								refund_details.sAmount as conceptTotal
							')
							->leftJoin('refunds', 'refunds.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'refunds.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'refunds.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('projects as requestProject', 'requestProject.idproyect', 'request_models.idProject')
							->leftJoin('projects as reviewedProject', 'reviewedProject.idproyect', 'request_models.idProjectR')
							->leftJoin('refund_details', 'refund_details.idRefund', 'refunds.idRefund')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'refund_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'refund_details.idAccount')
							->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as amount_taxes from taxes_refunds group by idRefundDetail) as taxes_refunds'),'taxes_refunds.idRefundDetail','refund_details.idRefundDetail')
							->where('request_models.kind',9)
							->where('request_models.status',4)
							->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(121)->pluck('departament_id'))
							->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(121)->pluck('enterprise_id'))
							->whereIn('request_models.idProjectR',Auth::user()->inChargeProject(121)->pluck('project_id'))
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $project, $title_shear)
							{
								if ($enterpriseid != "") 
								{
									$query->where('request_models.idEnterpriseR',$enterpriseid);
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborateUser.name,elaborateUser.last_name,elaborateUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								}
								if($title_shear != "")
								{
									$query->where('refunds.title','LIKE','%'.preg_replace("/\s+/", "%", $title_shear).'%');
								}
								if($project != "")
								{
									$query->where('requestProject.idproyect',$project);
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($status != "")
								{
									$query->where('request_models.status',$status);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
							})
							->orderBy('request_models.reviewDate','DESC')
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
			$alignment    	= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-reembolso.xlsx');
			$writer->getCurrentSheet()->setName('Autorización');

			$headers = ['Reporte de autorización de reembolso','','','','','','','','','','', '', '', '','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación del gasto', 'Tipo fiscal', 'Subtotal por concepto', 'IVA', 'Impuestos adicionales', 'Total por concepto'];
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
					$request->folio		= null;
					$request->title		= '';
					$request->datetitle		= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName		= '';
					$request->status		= '';
					$request->date		= '';
					$request->total		= null;
					$request->paymentMethod		= '';
					$request->bankName		= '';
					$request->cardNumber		= '';
					$request->clabe		= '';
					$request->account		= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptTotal', 'conceptAmount', 'conceptTax', 'conceptTaxes']))
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
}
