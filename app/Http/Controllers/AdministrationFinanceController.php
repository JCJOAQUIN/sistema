<?php

namespace App\Http\Controllers;

use App;
use App\Finance;
use App\Module;
use App\Payment;
use App\RequestModel;
use App\User;
use Auth;
use Lang;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use DB;

class AdministrationFinanceController extends Controller
{
	private $module_id = 197;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
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
		if (Auth::user()->module->where('id',198)->count()>0)
		{
			$data 	= Module::find($this->module_id);
			return view('administracion.gasto_financiero.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 198
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if (Auth::user()->module->where('id',198)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$request	= RequestModel::findOrFail($id);
			return view('administracion.gasto_financiero.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 198,
					'action'	=> 'new',
					'requests'	=> $request
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$t_request	= self::storeModel(null,$request, 3);
			$emails		= User::whereHas('module',function($q)
						{
							$q->where('id',200);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',200);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',200);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			$user 	=  User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Gastos Financieros";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= route('finance.review.edit',['id'=>$folio]);
						$subject 		= "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
				}
			}
			return redirect(route('finance.index'))->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function storeOnly(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= Module::find($this->module_id);
			self::storeModel(null,$request, 2);
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			return redirect(route('finance.index'))->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	private function storeModel($id, Request $request, $status)
	{
		if($id == '')
		{
			$requestModel	= new RequestModel();
		}
		else
		{
			$requestModel	= RequestModel::find($id);
		}
		$requestModel->folio		= $id;
		$requestModel->kind			= 18;
		$requestModel->taxPayment	= $request->fiscal;
		$requestModel->PaymentDate	= ($request->date != null ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d 00:00:00') : null);
		$requestModel->status		= $status;
		$requestModel->account		= $request->accountid;
		$requestModel->idEnterprise	= $request->enterpriseid;
		$requestModel->idArea		= $request->areaid;
		$requestModel->idDepartment	= $request->departmentid;
		$requestModel->idProject	= $request->projectid;
		$requestModel->idRequest	= $request->userid;
		$requestModel->idElaborate	= Auth::user()->id;
		$requestModel->fDate 		= Carbon::now();
		$requestModel->save();
		if($id == '')
		{
			$finance	= new Finance();
		}
		else
		{
			$finance	= $requestModel->finance;
		}
		$finance->idFolio		= $requestModel->folio;
		$finance->idKind		= $requestModel->kind;
		$finance->title			= $request->title;
		$finance->datetitle		= ($request->datetitle != null ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null);
		$finance->kind			= $request->kind;
		$finance->paymentMethod	= $request->payment_method;
		$finance->bank			= $request->bank;
		$finance->account		= $request->bank_account;
		$finance->card			= $request->bank_card;
		$finance->currency		= $request->currency;
		$finance->subtotal		= $request->subtotal;
		$finance->tax			= $request->iva;
		$finance->taxType		= $request->iva_kind;
		$finance->amount		= $request->amount;
		$finance->note			= $request->notes;
		if($request->date != null)
		{
			$week			= new \DateTime($request->date);
			$finance->week	= $week->format('W');
		}
		$finance->save();
		return $requestModel;
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',199)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',199)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',199)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = RequestModel::where('kind','18')
						->where(function($q) 
						{
							$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(199)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
						})
						->where(function ($q) 
						{
							$q->whereIn('idDepartment',Auth::user()->inChargeDep(199)->pluck('departament_id'))->orWhereNull('idDepartment');
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
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use ($name)
								{
									$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
								});
							}
							if($status != "")
							{
								$query->where('request_models.status',$status);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			return view('administracion.gasto_financiero.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 199,
					'requests'		=> $requests,
					'account'		=> $account, 
					'name'			=> $name, 
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'folio'			=> $folio,
					'status'		=> $status,
					'enterpriseid'	=> $enterpriseid
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function show($id)
	{
		if(Auth::user()->module->where('id',199)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',199)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',199)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= Module::find($this->module_id);
			$request		= RequestModel::where('kind',18)
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
									}
								})
								->findOrFail($id);

			return view('administracion.gasto_financiero.alta',
					[

						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 199,
						'requests'	=> $request
					]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',199)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',199)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',199)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= Module::find($this->module_id);
			$request		= RequestModel::where('kind',18)
								->where('status',2)
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
									}
								})
								->findOrFail($id);

			return view('administracion.gasto_financiero.alta',
				[

					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 199,
					'requests'	=> $request
				]);

		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',200)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$request		= RequestModel::where('kind',18)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(200)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(200)->pluck('departament_id'))
								->findOrFail($id);

			return view('administracion.gasto_financiero.accion',
				[

					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 200,
					'requests'	=> $request,
					'action'	=> 'review'
				]
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if(Auth::user()->module->where('id',201)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$request		= RequestModel::where('kind',18)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(201)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(201)->pluck('departament_id'))
								->findOrFail($id);

			return view('administracion.gasto_financiero.accion',
				[

					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 201,
					'requests'	=> $request,
					'action'	=> 'authorization'
				]
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$t_request	= self::storeModel($id,$request, 3);
			$emails		= User::whereHas('module',function($q)
						{
							$q->where('id',200);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',200);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',200);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			$user 	=  User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Gastos Financieros";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= route('finance.review.edit',['id'=>$folio]);
						$subject 		= "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
				}
			}
			return redirect(route('finance.index'))->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateOnly(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= Module::find($this->module_id);
			self::storeModel($id,$request,2);
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',200)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = RequestModel::where('kind','18')
						->where('status','3')
						->whereIn('idEnterprise',Auth::user()->inChargeEnt(200)->pluck('enterprise_id'))
						->whereIn('idDepartment',Auth::user()->inChargeDep(200)->pluck('departament_id'))
						->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio)
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
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use ($name)
								{
									$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
								});
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			return response(
				view('administracion.gasto_financiero.busqueda',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 200,
						'requests'		=> $requests,
						'account'		=> $account, 
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'enterpriseid'	=> $enterpriseid,
						'action'		=> 'review'
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(200), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',201)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = RequestModel::where('kind','18')
						->where('status','4')
						->whereIn('idEnterprise',Auth::user()->inChargeEnt(201)->pluck('enterprise_id'))
						->whereIn('idDepartment',Auth::user()->inChargeDep(201)->pluck('departament_id'))
						->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio)
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
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($name != "")
							{
								$query->whereHas('requestUser',function($q) use ($name)
								{
									$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
								});
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
							}
						})
						->orderBy('fDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);
			return response(
				view('administracion.gasto_financiero.busqueda',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 201,
						'requests'		=> $requests,
						'account'		=> $account, 
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'enterpriseid'	=> $enterpriseid,
						'action'		=> 'authorization'
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(201), 2880
			);
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
			$data	= Module::find($this->module_id);
			$checkStatus    = RequestModel::findOrFail($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$review	= RequestModel::findOrFail($id);
				if ($request->status == "4")
				{
					$review->status			= $request->status;
					$review->accountR		= $request->accountR;
					$review->idEnterpriseR	= $request->idEnterpriseR;
					$review->idDepartamentR	= $request->idDepartmentR;
					$review->idAreaR		= $request->idAreaR;
					$review->idProjectR		= $request->project_id;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();
					if ($request->idLabelsReview != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabelsReview,array('request_kind'=>'18'));
					}
					$emails = User::whereHas('module',function($q)
						{
							$q->where('id',201);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',201);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',201);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					$user 	= User::find($review->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Gastos Financieros";
								$status 		= "Autorizar";
								$date 			= Carbon::now();
								$url 			= route('finance.authorization.edit',['id'=>$id]);
								$subject 		= "Solicitud por Autorizar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= User::where('id',$review->idElaborate)
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
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Compra";
								$status 		= "RECHAZADA";
								$date 			= Carbon::now();
								$url 			= route('purchase.follow.edit',['id'=>$id]);
								$subject 		= "Estado de Solicitud";
								$requestUser	= null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
						}
					}
				}
			}
			return searchRedirect(200, $alert, 'administration/finance');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$checkStatus    = RequestModel::findOrFail($id);

			if ($checkStatus->status == 10 || $checkStatus->status == 7) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$data							= Module::find($this->module_id);
				$authorize						= RequestModel::findOrFail($id);
				$authorize->status				= $request->status;
				$authorize->idAuthorize			= Auth::user()->id;
				$authorize->authorizeComment	= $request->authorizeCommentA;
				$authorize->authorizeDate		= Carbon::now();
				$authorize->save();
				if ($request->status == 10)
				{
					$t_payment								= new Payment();
					$t_payment->amount 						= $authorize->finance->amount;
					$t_payment->account						= $authorize->accountR;
					$t_payment->paymentDate					= $authorize->PaymentDate;
					$t_payment->elaborateDate				= Carbon::now();
					$t_payment->idFolio						= $authorize->folio;
					$t_payment->idKind						= $authorize->kind;
					$t_payment->idRequest					= Auth::user()->id;
					$t_payment->idEnterprise				= $authorize->idEnterpriseR;
					$t_payment->commentaries				= '';
					$t_payment->exchange_rate				= 1;
					$t_payment->exchange_rate_description	= '';
					$t_payment->save();
				}
				$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";

				$emailRequest 			= "";
				if ($authorize->idElaborate == $authorize->idRequest) 
				{
					$emailRequest 	= User::where('id',$authorize->idElaborate)
									->where('notification',1)
									->get();
				}
				else
				{
					$emailRequest 	= User::where('id',$authorize->idElaborate)
									->orWhere('id',$authorize->idRequest)
									->where('notification',1)
									->get();
				}
				$user 			= User::find($authorize->idRequest);
				if ($emailRequest != "")
				{
					try
					{
						foreach ($emailRequest as $email)
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Gastos Financieros";
							if ($request->status == 5)
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date 			= Carbon::now();
							$url 			= route('finance.show',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser 	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
					}
					catch(\Exception $e)
					{
						$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
					}
				}
			}
			return searchRedirect(201, $alert, 'administration/finance');
		}
	}

	public function export($action, Request $request)
	{
		$account		= $request->account;
		$name			= $request->name;
		$folio			= $request->folio;
		$status			= $request->status;
		$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
		$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		$enterpriseid	= $request->enterpriseid;

		switch($action)
		{
			case 'follow':
				if(Auth::user()->module->where('id',199)->count()>0)
				{
					if(Auth::user()->globalCheck->where('module_id',199)->count()>0)
					{
						$global_permission =  Auth::user()->globalCheck->where('module_id',199)->first()->global_permission;
					}
					else
					{
						$global_permission = 0;
					}
					$title		= 'Seguimiento';

					$requests 	= DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									CONCAT_WS(" ",finances.title, finances.datetitle) as title,
									CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
									IF(request_models.idEnterpriseR IS NOT NULL, reviewedEnterprise.name, requestEnterprise.name) as enterprise_name,
									IF(request_models.idAreaR IS NOT NULL, reviewedDirection.name, requestDirection.name) as direction_name,
									IF(request_models.idDepartamentR IS NOT NULL, reviewedDepartment.name, requestDepartment.name) as department_name,
									IF(request_models.idProjectR IS NOT NULL, reviewedProject.proyectName, requestProject.proyectName) as project_name,
									IF(request_models.accountR IS NOT NULL, CONCAT_WS(" ",reviewedAccount.account, reviewedAccount.description), CONCAT_WS(" ",requestAccount.account, requestAccount.description)) as account,
									status_requests.description as status,
									finances.note as note,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									finances.amount as amount_request,
									IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as fiscal,
									finances.kind as kind,
									DATE_FORMAT(request_models.PaymentDate, "%d-%m-%Y") as paymentDate,
									finances.paymentMethod as paymentMethod,
									banks.description as bankDescription,
									IF(bank_accounts.account IS NOT NULL,bank_accounts.account,bank_accounts.clabe) as accountBank,
									credit_cards.credit_card as credit_card,
									finances.week as week
								')
								->leftJoin('finances','finances.idFolio','request_models.folio')
								->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
								->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
								->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
								->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
								->leftJoin('areas as reviewedDirection','reviewedDirection.id','request_models.idAreaR')
								->leftJoin('areas as requestDirection','requestDirection.id','request_models.idArea')
								->leftJoin('departments as reviewedDepartment','reviewedDepartment.id','request_models.idDepartamentR')
								->leftJoin('departments as requestDepartment','requestDepartment.id','request_models.idDepartment')
								->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
								->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
								->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
								->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
								->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
								->leftJoin('banks','banks.idBanks','finances.bank')
								->leftJoin('bank_accounts','bank_accounts.id','finances.account')
								->leftJoin('credit_cards','credit_cards.idcreditCard','finances.card')
								->where('request_models.kind','18')
								->where(function($q) 
								{
									$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(199)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
								})
								->where(function ($q) 
								{
									$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(199)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
								})
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
									}
								})
								->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status)
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
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($name != "")
									{
										$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
									}
									if($status != "")
									{
										$query->where('request_models.status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
									}
								})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();
				}
				else
				{
					return redirect('/error');
				}
				break;
			case 'review':
				if(Auth::user()->module->where('id',200)->count()>0)
				{
					$title		= 'Revisión';
					$requests 	= DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									CONCAT_WS(" ",finances.title, finances.datetitle) as title,
									CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
									IF(request_models.idEnterpriseR IS NOT NULL, reviewedEnterprise.name, requestEnterprise.name) as enterprise_name,
									IF(request_models.idAreaR IS NOT NULL, reviewedDirection.name, requestDirection.name) as direction_name,
									IF(request_models.idDepartamentR IS NOT NULL, reviewedDepartment.name, requestDepartment.name) as department_name,
									IF(request_models.idProjectR IS NOT NULL, reviewedProject.proyectName, requestProject.proyectName) as project_name,
									IF(request_models.accountR IS NOT NULL, CONCAT_WS(" ",reviewedAccount.account, reviewedAccount.description), CONCAT_WS(" ",requestAccount.account, requestAccount.description)) as account,
									status_requests.description as status,
									finances.note as note,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									finances.amount as amount_request,
									IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as fiscal,
									finances.kind as kind,
									DATE_FORMAT(request_models.PaymentDate, "%d-%m-%Y") as paymentDate,
									finances.paymentMethod as paymentMethod,
									banks.description as bankDescription,
									IF(bank_accounts.account IS NOT NULL,bank_accounts.account,bank_accounts.clabe) as accountBank,
									credit_cards.credit_card as credit_card,
									finances.week as week
								')
								->leftJoin('finances','finances.idFolio','request_models.folio')
								->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
								->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
								->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
								->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
								->leftJoin('areas as reviewedDirection','reviewedDirection.id','request_models.idAreaR')
								->leftJoin('areas as requestDirection','requestDirection.id','request_models.idArea')
								->leftJoin('departments as reviewedDepartment','reviewedDepartment.id','request_models.idDepartamentR')
								->leftJoin('departments as requestDepartment','requestDepartment.id','request_models.idDepartment')
								->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
								->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
								->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
								->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
								->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
								->leftJoin('banks','banks.idBanks','finances.bank')
								->leftJoin('bank_accounts','bank_accounts.id','finances.account')
								->leftJoin('credit_cards','credit_cards.idcreditCard','finances.card')
								->where('request_models.kind','18')
								->where('request_models.status','3')
								->where(function($q) 
								{
									$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(200)->pluck('enterprise_id'));
								})
								->where(function ($q) 
								{
									$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(200)->pluck('departament_id'));
								})
								->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status)
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
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($name != "")
									{
										$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
									}
								})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();
				}
				else
				{
					return redirect('/error');
				}
				break;
			case 'authorization':
				if(Auth::user()->module->where('id',200)->count()>0)
				{
					$title		= 'Autorización';
					$requests 	= DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									CONCAT_WS(" ",finances.title, finances.datetitle) as title,
									CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
									IF(request_models.idEnterpriseR IS NOT NULL, reviewedEnterprise.name, requestEnterprise.name) as enterprise_name,
									IF(request_models.idAreaR IS NOT NULL, reviewedDirection.name, requestDirection.name) as direction_name,
									IF(request_models.idDepartamentR IS NOT NULL, reviewedDepartment.name, requestDepartment.name) as department_name,
									IF(request_models.idProjectR IS NOT NULL, reviewedProject.proyectName, requestProject.proyectName) as project_name,
									IF(request_models.accountR IS NOT NULL, CONCAT_WS(" ",reviewedAccount.account, reviewedAccount.description), CONCAT_WS(" ",requestAccount.account, requestAccount.description)) as account,
									status_requests.description as status,
									finances.note as note,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									finances.amount as amount_request,
									IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as fiscal,
									finances.kind as kind,
									DATE_FORMAT(request_models.PaymentDate, "%d-%m-%Y") as paymentDate,
									finances.paymentMethod as paymentMethod,
									banks.description as bankDescription,
									IF(bank_accounts.account IS NOT NULL,bank_accounts.account,bank_accounts.clabe) as accountBank,
									credit_cards.credit_card as credit_card,
									finances.week as week
								')
								->leftJoin('finances','finances.idFolio','request_models.folio')
								->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
								->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
								->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
								->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
								->leftJoin('areas as reviewedDirection','reviewedDirection.id','request_models.idAreaR')
								->leftJoin('areas as requestDirection','requestDirection.id','request_models.idArea')
								->leftJoin('departments as reviewedDepartment','reviewedDepartment.id','request_models.idDepartamentR')
								->leftJoin('departments as requestDepartment','requestDepartment.id','request_models.idDepartment')
								->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
								->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
								->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
								->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
								->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
								->leftJoin('banks','banks.idBanks','finances.bank')
								->leftJoin('bank_accounts','bank_accounts.id','finances.account')
								->leftJoin('credit_cards','credit_cards.idcreditCard','finances.card')
								->where('request_models.kind','18')
								->where('request_models.status','4')
								->where(function($q) 
								{
									$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(201)->pluck('enterprise_id'));
								})
								->where(function ($q) 
								{
									$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(201)->pluck('departament_id'));
								})
								->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status)
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
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($name != "")
									{
										$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
									}
								})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();
				}
				else
				{
					return redirect('/error');
				}
				break;
			
			default:
				return redirect('/error');
				break;
		}
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
		$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de '.$title.' de Gastos Financieros.xlsx');
		$writer->getCurrentSheet()->setName('Solicitudes');
		$headers = ['Reporte de '.$title.' de Gastos Financieros','','','','','','','','','','','','','','','','','','','',''];
		$tempHeaders      = [];
		foreach($headers as $k => $mh)
		{
			$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
		}
		$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
		$writer->addRow($rowFromValues);
		$subHeader    = ['Folio','Título y fecha','Solicitante','Elaborado por','Empresa','Dirección','Departamento','Proyecto','Clasificación del gasto','Estado','Notas','Fecha de elaboración','Monto','Fiscal/No Fiscal','Tipo','Fecha de pago','Método de pago','Banco','Cuenta','Tarjeta','Semana'];
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
				$tempFolio	= $request->folio;
				$kindRow	= !$kindRow;
			}

			$tempArray = [];
			foreach($request as $key => $req)
			{
				if($key == 'amount_request')
				{
					if($req != '')
					{
						$tempArray[] = WriterEntityFactory::createCell((double)$req,$currencyFormat);
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($req);
					}
				}
				else
				{
					$tempArray[] = WriterEntityFactory::createCell($req);
				}
			}

			if($kindRow)
			{
				$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
			}
			$writer->addRow($rowFromValues);
		}
		return $writer->close();
	}
}
