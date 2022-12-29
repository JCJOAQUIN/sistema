<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App;
use Auth;
use Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Excel;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionComputoController extends Controller
{
	private $module_id = 62;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
		if(Auth::user()->module->where('id',63)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			return view('administracion.computo.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 63
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',63)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',64)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',64)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data       = App\Module::find($this->module_id);
			$thisModule = App\Module::find(63);
			$requests   = App\RequestModel::whereIn('status',[5,6,7,9,19])
				->where('kind',6)
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
				return view('administracion.computo.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $thisModule['details'],
						'child_id'  => $this->module_id,
						'option_id' => 63,
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

	public function store(Request $request)
	{	
		if(Auth::user()->module->where('id',63)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$t_request               = new App\RequestModel();
			$t_request->kind         = 6;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 3;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->account      = $request->account_id;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;
			$t_computer                 = new App\Computer();
			$t_computer->idFolio        = $folio;
			$t_computer->idKind         = $kind;
			$t_computer->title          = $request->title;			
			$t_computer->datetitle      = Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d');
			$t_computer->entry          = $request->entry;
			$t_computer->entry_date     = $request->entry_date != "" ? Carbon::createFromFormat('d-m-Y', $request->entry_date)->format('Y-m-d') : null;
			$t_computer->device         = $request->device;
			$t_computer->role_id        = $request->role_id;
			$t_computer->position       = $request->position;
			$t_computer->other_software = $request->other_software;
			$t_computer->save();
			$idComputer = $t_computer->idComputer;
			$t_computer->software()->attach($request->software_check);
			if (count($request->email_account)>0) 
			{
				for ($i=0; $i < count($request->email_account); $i++) 
				{ 
					$t_computerAccount  = new App\ComputerEmailsAccounts();
					$t_computerAccount->email_account = $request->email_account[$i];
					$t_computerAccount->alias_account = $request->alias_account[$i];
					$t_computerAccount->idComputer    = $idComputer;
					$t_computerAccount->save();
				}
			}
			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 65);
				})
				->whereHas('inChargeDepGet',function($q) use ($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',65);
				})
				->whereHas('inChargeEntGet',function($q) use ($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',65);
				})
				->whereHas('inChargeProjectGet',function($q) use ($t_request)
				{
					$q->where('project_id', $t_request->idProject)
						->where('module_id',65);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->user_id);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Cómputo";
						$status      = "Revisar";
						$date        = Carbon::now();
						$url         = route('computer.review.edit',['id'=>$folio]);
						$subject     = "Solicitud por Revisar";
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/computer')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',64)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',64)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',64)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = App\RequestModel::where('kind','6')
				->where(function($q) 
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(64)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
				})
				->where(function ($q) 
				{
					$q->whereIn('idDepartment',Auth::user()->inChargeDep(64)->pluck('departament_id'))->orWhereNull('idDepartment');
				})
				->where(function ($q) 
				{
					$q->whereIn('idProject',Auth::user()->inChargeProject(64)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($name, $mindate, $maxdate, $folio, $status,$enterpriseid)
				{
					if($name != "")
					{
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if ($enterpriseid != "") 
					{
						$query->where('request_models.idEnterprise',$enterpriseid);
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
			return view('administracion.computo.busqueda',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 64,
					'requests'     => $requests,
					'name'         => $name,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'folio'        => $folio,
					'status'       => $status,
					'enterpriseid' => $enterpriseid
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getSoftware(Request $request)
	{
		if($request->ajax())
		{
			$switch_component = "";
			$switch_checked   = "";
			$software         = App\Software::where('kind',$request->kind)->get();
			if(count($software)>0)
			{
				$switch_component .= '<ul>';
				foreach ($software as $key => $value)
				{
					$switch_component .= '<li>';
					if($value->required)
					{
						$switch_checked	.= "checked=\"checked\" readonly=\"true\"";
					}
					$switch_component .= html_entity_decode((String)view("components.inputs.switch",[
						"classEx"     => "software_id",
						"attributeEx" => "type=\"checkbox\" name=\"software_check[]\" id=\"software_$value->idsoftware\" $switch_checked value=\"$value->idsoftware\" forvalue=\"software_$value->idsoftware\"",
						"slot"        => $value->name,
					]));
					$switch_component .= '</li>';
					$switch_checked ="";
				}
				$switch_component	.= '</ul>';
			}
			return Response($switch_component);
		}
	}

	public function unsent(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request               = new App\RequestModel();
			$t_request->kind         = 6;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 2;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->idElaborate  = Auth::user()->id;
			$t_request->account      = $request->account_id;
			$t_request->save();
			$folio                  = $t_request->folio;
			$kind                   = $t_request->kind;
			$t_computer             = new App\Computer();
			$t_computer->idFolio    = $folio;
			$t_computer->idKind     = $kind;
			$t_computer->title      = $request->title;
			$t_computer->datetitle  = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_computer->entry      = $request->entry;
			$t_computer->entry_date = $request->entry_date != "" ? Carbon::createFromFormat('d-m-Y', $request->entry_date)->format('Y-m-d') : null;
			$t_computer->device     = $request->device;
			$t_computer->role_id    = $request->role_id;
			$t_computer->position   = $request->position;
			$t_computer->other_software = $request->other_software;
			$t_computer->save();
			$idComputer = $t_computer->idComputer;
			$t_computer->software()->detach();
			$t_computer->software()->attach($request->software_check);

			if ($request->email_account != "") 
			{
				if (count($request->email_account)>0) 
				{
					for ($i=0; $i < count($request->email_account); $i++) 
					{ 
						$t_computerAccount                = new App\ComputerEmailsAccounts();
						$t_computerAccount->email_account = $request->email_account[$i];
						$t_computerAccount->alias_account = $request->alias_account[$i];
						$t_computerAccount->idComputer    = $idComputer;
						$t_computerAccount->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('computer.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',64)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',64)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',64)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data       = App\Module::find($this->module_id);
			$thisModule = App\Module::find(64);
			$request    = App\RequestModel::where('kind',6)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			if ($request != "") 
			{
				return view('administracion.computo.seguimiento',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $thisModule['details'],
						'child_id'  => $this->module_id,
						'option_id' => 64,
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

	public function updateFollow(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$t_request               = App\RequestModel::find($id);
			$t_request->kind         = 6;
			$t_request->fDate        = Carbon::now();
			$t_request->status       = 3;
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->account      = $request->account_id;
			$t_request->save();
			$Computer = App\Computer::where('idFolio',$t_request->folio)
				->where('idKind',$t_request->kind)
				->get();
			foreach ($Computer as $key => $value)
			{
				$idComputer = $value->idComputer;
			}
			$t_computer                 = App\Computer::find($idComputer);
			$t_computer->title          = $request->title;
			$t_computer->datetitle      = Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d');
			$t_computer->entry          = $request->entry;
			$t_computer->entry_date     = $request->entry_date != "" ? Carbon::createFromFormat('d-m-Y', $request->entry_date)->format('Y-m-d') : null;
			$t_computer->device         = $request->device;
			$t_computer->role_id        = $request->role_id;
			$t_computer->position       = $request->position;
			$t_computer->other_software = $request->other_software;
			$t_computer->save();
			$idComputer = $t_computer->idComputer;
			$t_computer->software()->detach();
			$t_computer->software()->attach($request->software_check);
			if (isset($request->delete) && count($request->delete)>0) 
			{
				for ($i=0; $i < count($request->delete); $i++)
				{ 
					$t_delete = App\ComputerEmailsAccounts::find($request->delete[$i]);
					$t_delete->delete();
				}
			}
			if ($request->email_account != "") 
			{
				if (isset($request->email_account) && count($request->email_account)>0) 
				{
					for ($i=0; $i < count($request->email_account); $i++) 
					{ 
						if(isset($request->idcomputerEmailsAccounts[$i]) && $request->idcomputerEmailsAccounts[$i] == "x")
						{
							$t_computerAccount                = new App\ComputerEmailsAccounts();
							$t_computerAccount->email_account = $request->email_account[$i];
							$t_computerAccount->alias_account = $request->alias_account[$i];
							$t_computerAccount->idComputer    = $idComputer;
							$t_computerAccount->save();
						}
					}
				}
			}
			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 65);
				})
				->whereHas('inChargeDepGet',function($q) use ($t_request)
				{
					$q->where('departament_id', $t_request->idDepartment)
						->where('module_id',65);
				})
				->whereHas('inChargeEntGet',function($q) use ($t_request)
				{
					$q->where('enterprise_id', $t_request->idEnterprise)
						->where('module_id',65);
				})
				->whereHas('inChargeProjectGet',function($q) use ($t_request)
				{
					$q->where('project_id', $t_request->idProject)
						->where('module_id',65);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			$user = App\User::find($request->user_id);
			if ($emails != "") 
			{
				try
				{
					foreach ($emails as $email) 
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Cómputo";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('computer.review.edit',['id'=>$id]);
						$subject 		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/computer')->with('alert',$alert);
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
			$t_request->kind         = "6";
			$t_request->fDate        = Carbon::now();
			$t_request->status       = "2";
			$t_request->idEnterprise = $request->enterprise_id;
			$t_request->idArea       = $request->area_id;
			$t_request->idDepartment = $request->department_id;
			$t_request->idRequest    = $request->user_id;
			$t_request->idProject    = $request->project_id;
			$t_request->account      = $request->account_id;
			$t_request->save();
			$Computer = App\Computer::where('idFolio',$t_request->folio)
				->where('idKind',$t_request->kind)
				->get();
			foreach ($Computer as $key => $value)
			{
				$idComputer = $value->idComputer;
			}
			$t_computer                 = App\Computer::find($idComputer);
			$t_computer->title          = $request->title;
			$t_computer->datetitle      = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_computer->entry          = $request->entry;
			$t_computer->entry_date     = $request->entry_date != "" ? Carbon::createFromFormat('d-m-Y', $request->entry_date)->format('Y-m-d') : null;
			$t_computer->device         = $request->device;
			$t_computer->role_id        = $request->role_id;
			$t_computer->position       = $request->position;
			$t_computer->other_software = $request->other_software;
			$t_computer->save();
			$idComputer = $t_computer->idComputer;
			$t_computer->software()->detach();
			$t_computer->software()->attach($request->software_check);
			if (isset($request->delete) && count($request->delete)>0) 
			{
				for ($i=0; $i < count($request->delete); $i++)
				{ 
					$t_delete = App\ComputerEmailsAccounts::find($request->delete[$i]);
					$t_delete->delete();
				}
			}
			if ($request->email_account != "") 
			{
				if (isset($request->email_account) && count($request->email_account)>0) 
				{
					for ($i=0; $i < count($request->email_account); $i++) 
					{ 
						if(isset($request->idcomputerEmailsAccounts[$i]) && $request->idcomputerEmailsAccounts[$i] == "x")
						{
							$t_computerAccount                = new App\ComputerEmailsAccounts();
							$t_computerAccount->email_account = $request->email_account[$i];
							$t_computerAccount->alias_account = $request->alias_account[$i];
							$t_computerAccount->idComputer    = $idComputer;
							$t_computerAccount->save();
						}
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('computer.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',65)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = App\RequestModel::where('kind','6')
				->where('status','3')
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(65)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(65)->pluck('departament_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(65)->pluck('project_id'))
				->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
				{
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
					if ($enterpriseid != "") 
					{
						$query->where('request_models.idEnterprise',$enterpriseid);
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
				view('administracion.computo.revision',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 65,
						'requests'     => $requests,
						'folio'        => $folio, 
						'name'         => $name, 
						'mindate'      => $request->mindate,
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(65), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',65)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$thisModule = App\Module::find(65);
			$request    = App\RequestModel::where('kind',6)
				->where('status',3)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(65)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(65)->pluck('departament_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(65)->pluck('project_id'))
				->find($id);
			if ($request != "") 
			{
				return view('administracion.computo.revisioncambio',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $thisModule['details'],
						'child_id'  => $this->module_id,
						'option_id' => 65,
						'request'   => $request
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/computer/review')->with('alert',$alert);
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
				$review	= App\RequestModel::find($id);
				if ($request->status == "4") 
				{
					$review->status         = $request->status;
					$review->reviewDate     = Carbon::now();
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkComment;
					$review->accountR       = $review->account;
					$review->idEnterpriseR  = $review->idEnterprise;
					$review->idDepartamentR = $review->idDepartment;
					$review->idProjectR     = $review->idProject;
					$review->save();
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 66);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartment)
								->where('module_id',66);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterprise)
								->where('module_id',66);
						})
						->whereHas('inChargeProjectGet',function($q) use ($review)
						{
							$q->where('project_id', $review->idProject)
								->where('module_id',66);
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
								$kind        = "Cómputo";
								$status      = "Autorizar";
								$date        = Carbon::now();
								$url         = route('computer.authorization.edit',['id'=>$id]);
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
				elseif ($request->status == "6")
				{
					$review->status       = $request->status;
					$review->idCheck      = Auth::user()->id;
					$review->checkComment = $request->checkComment;
					$review->reviewDate   = Carbon::now();
					$review->save();
					$emailRequest = "";
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
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Cómputo";
								$status 		= "RECHAZADA";
								$date 			= Carbon::now();
								$url 			= route('computer.follow.edit',['id'=>$id]);
								$subject 		= "Estado de Solicitud";
								$requestUser	= null;
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
			return searchRedirect(65, $alert, 'administration/computer');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',66)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = App\RequestModel::where('kind',6)
				->where('status',4)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(66)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(66)->pluck('departament_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(66)->pluck('project_id'))
				->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
				{
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
					if ($enterpriseid != "") 
					{
						$query->where('request_models.idEnterprise',$enterpriseid);
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
				view('administracion.computo.autorizacion',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 66,
						'requests'     => $requests,
						'folio'        => $folio,
						'name'         => $name,
						'mindate'      => $request->mindate,
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(66), 2880
			);
		}
		else
		{
			return redirect('/'); 
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',66)->count()>0) 
		{
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::where('kind',6)
				->where('status',4)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(66)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(66)->pluck('departament_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(66)->pluck('project_id'))
				->find($id);
			if ($request != "") 
			{
				return view('administracion.computo.autorizacioncambio',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 66
					],
					['request'=>$request]
				);
			}
			else
			{
				$alert 	= "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/computer/authorization')->with('alert',$alert);
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
				$authorize->authorizeComment = $request->authorizeComment;
				$authorize->authorizeDate    = Carbon::now();
				if ($request->status ==  5) 
				{
					$authorize->code = rand(10000000,99999999);
				}
				$authorize->save();
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
				$emailDelivery = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',114)
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
							$kind = "Cómputo";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date        = Carbon::now();
							$url         = route('computer.follow.edit',['id'=>$id]);
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
				if ($request->status == 5)
				{
					if ($emailDelivery != "") 
					{
						try
						{
							foreach ($emailDelivery as $email) 
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Computo";
								$status      = "Entregar";
								$date        = Carbon::now();
								$url         = route('computer.delivery.edit',['id'=>$id]);
								$subject     = "Articulos por Entregar";
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
			return searchRedirect(66, $alert, 'administration/computer');
		}
	}

	public function delivery(Request $request)
	{
		if(Auth::user()->module->where('id',114)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = App\RequestModel::where('kind',6)
				->where('status',5)
				->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
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
				view('administracion.computo.entrega',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 114,
						'requests'     => $requests,
						'folio'        => $folio, 
						'name'         => $name, 
						'mindate'      => $request->mindate, 
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(114), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showDelivery($id)
	{
		if (Auth::user()->module->where('id',114)->count()>0) 
		{
			$data        = App\Module::find($this->module_id);
			$enterprises = App\Enterprise::where('status','ACTIVE')->get();
			$areas       = App\Area::where('status','ACTIVE')->get();
			$departments = App\Department::where('status','ACTIVE')->get();
			$projects    = App\Project::all();
			$labels      = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			$request = App\RequestModel::where('kind',6)
				->where('status',5)
				->find($id);
			if ($request != "") 
			{
				return view('administracion.computo.entregacambio',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 114,
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
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function requestsdelivery(Request $request)
	{
		if (Auth::user()->module->where('id',114)->count() > 0)
		{
			if($request->ajax())
			{
				$articlesWarehouse = "";
				$kindSortWarehouse = $request->kindSort == "" ? "id"	: $request->kindSort;
				$ascDescWarehouse  = $request->ascDesc  == "" ? "ASC"  : $request->ascDesc;
				$flagSearch		   = $request->search 	== "" ? false : true;
				$equipmentRequest  = App\RequestModel::where('kind',6)
					->where('status',5)
					->find($request->folio);
				$warehouseRequest = App\ComputerEquipment::where("quantity","!=",0)->where("type",$equipmentRequest->computer->first()->device)
					->where("idEnterprise",$equipmentRequest->idEnterprise)
					->where(function($query) use($request)
					{
						if($request->selected != '')
						{
							$query->where('id','!=',$request->selected);
						}
						if($request->search != '')
						{
							$query->where('brand', 'LIKE', '%'.$request->search.'%');
						}
					})
					->orderBy($kindSortWarehouse, $ascDescWarehouse)
					->paginate(5);
				if($request->selected != "")
				{
					$warehouseRequestSelected = App\ComputerEquipment::find($request->selected);
				}
				else
				{
					$warehouseRequestSelected = "";
				}
				$articlesWarehouse = html_entity_decode( preg_replace("/(\r)*(\n)*/", "", view('administracion.computo.tables_delivery.articles_warehouse',
				[
					'warehouseRequest' 			=> $warehouseRequest, 
					'warehouseRequestSelected' 	=> $warehouseRequestSelected,
					'flagSearch'				=> $flagSearch,
				])));
				return Response($articlesWarehouse);
			}
		}
	}

	public function updateDelivery($id, Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 9)
			{
				$alert 	= "swal('', '".Lang::get("messages.record_already_delivered")."', 'error');";
			}
			else
			{
				$authorize         = App\RequestModel::find($id);
				$authorize->status = 9;
				$emailRequest      = App\User::where('id',$authorize->idRequest)->where('notification',1)->get();
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to          = $email->email;
							$kind        = "Computo";
							$status      = 'ENTREGADO';
							$date        = Carbon::now();
							$url         = route('computer.follow.edit',['id'=>$id]);
							$subject     = "Equipo Entregado";
							$requestUser = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.record_delivered")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert = "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}
				for ($i=0; $i < count($request->tid_art); $i++) 
				{ 
					$warehouse = App\ComputerEquipment::find($request->tid_art[$i]);
					$unitPrice = $warehouse->amountUnit;
					switch ($warehouse->typeTax) 
					{
						case 'a':
							$valueIva = App\Parameter::where('parameter_name','IVA')->first()->parameter_value/100;
							break;
						case 'b':
							$valueIva = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value/100;
							break;
						default:
							$valueIva = 0;
							break;
					}
					$authorize->computer;
					$computer                      = App\Computer::find($authorize->computer[0]->idComputer);
					$subtotal                      = ($warehouse->quantity - $request->tquanty[$i]) * $unitPrice;
					$iva                           = $subtotal*$valueIva;
					$total                         = $subtotal+$iva;
					$computer->subtotal            = $subtotal;
					$computer->iva                 = $iva;
					$computer->total               = $total;
					$computer->idComputerEquipment = $request->tid_art[$i];
					$computer->save();
					$warehouse->quantity = $request->tquanty[$i];
					$warehouse->save();
					if($warehouse->quantity == 0)
					{
						$warehouse->status = 0;
						$warehouse->save();
					}
				}
				$authorize->save();
			}
			return searchRedirect(114, $alert, 'administration/computer');
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if (Auth::user()->module->where('id',114)->count()>0) 
		{
			if ($request->ajax())
			{
				$response = array(
					'valid'   => false,
					'message' => 'Error.'
				);
				if(isset($request->code) && $request->code != '')
				{
					if($request->oldCode == $request->code)
					{
						$response = array('valid' => true);
					}
					else
					{
						$response = array(
							'valid'   => false,
							'message' => 'El código es incorrecto.'
						);
					}
				}
				else
				{
					$response = array(
						'valid'   => false,
						'message' => 'Este campo es obligatorio.'
					);
				}
				return Response($response);
			}
		}	
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',64)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',64)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',64)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					computers.title as title,
					IF(computers.title IS NULL,"No hay", computers.title) as title,
					IF(computers.datetitle IS NULL,"No hay", DATE_FORMAT(computers.datetitle, "%d-%m-%Y")) as datetitle,
					IF(requestUser.name IS NULL,"No hay solicitante", CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name)),
					CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name),
					IF(reviewedEnterprise.name IS NULL, IF(requestEnterprise.name IS NULL, "No hay", requestEnterprise.name), reviewedEnterprise.name) as enterpriseName,
					IF(projects.proyectName IS NULL, "No hay", projects.proyectName) as projectName,
					status_requests.description as status,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					IF(computers.device = 1,"Smartphone",
						IF(computers.device = 2, "Tablet", 
							IF(computers.device = 3, "Laptop", 
								IF(computers.device = 4, "Computadora", "")))) as device
				')
				->leftJoin('computers','computers.idFolio','request_models.folio')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
				->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
				->leftJoin('projects','projects.idproyect','request_models.idProject')
				->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
				->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
				->where('request_models.kind',6)
				->where(function($q) 
				{
					$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(64)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
				})
				->where(function ($q) 
				{
					$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(64)->pluck('departament_id'))->orWhereNull('idDepartment');
				})
				->where(function ($q) 
				{
					$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(64)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid)
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
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1  = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2  = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment    = (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-cómputo.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$headers = ['Reporte de seguimiento de cómputo','','','','','','','','',''];
			$tempHeaders = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader     = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Estado','Fecha de elaboración','Dispositivo'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio = '';
			$kindRow   = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow   = !$kindRow;
				}
				$tempArray = [];
				foreach($request as $k => $req)
				{
					$tempArray[] = WriterEntityFactory::createCell($req);
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
		else
		{
			return redirect('error');
		}
	}

	public function exportReview(Request $request)
	{
		if(Auth::user()->module->where('id',65)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					computers.title as title,
					DATE_FORMAT(computers.datetitle, "%d-%m-%Y") as datetitle,
					CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
					CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
					IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
					projects.proyectName as projectName,
					status_requests.description as status,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					IF(computers.device = 1,"Smartphone",
						IF(computers.device = 2, "Tablet", 
							IF(computers.device = 3, "Laptop", 
								IF(computers.device = 4, "Computadora", "")))) as device
				')
				->leftJoin('computers','computers.idFolio','request_models.folio')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
				->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
				->leftJoin('projects','projects.idproyect','request_models.idProject')
				->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
				->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
				->where('request_models.kind',6)
				->where('request_models.status',3)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(65)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(65)->pluck('departament_id'))
				->whereIn('request_models.idProject',Auth::user()->inChargeProject(65)->pluck('project_id'))
				->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid)
				{
					if($enterpriseid != "") 
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
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1  = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2  = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment    = (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-cómputo.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$headers     = ['Reporte de revisión de cómputo','','','','','','','','',''];
			$tempHeaders = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader     = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Estado','Fecha de elaboración','Dispositivo'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio = '';
			$kindRow   = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				$tempArray = [];
				foreach($request as $k => $req)
				{
					$tempArray[] = WriterEntityFactory::createCell($req);
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
		else
		{
			return redirect('error');
		}
	}

	public function exportAuthorize(Request $request)
	{
		if(Auth::user()->module->where('id',66)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					computers.title as title,
					DATE_FORMAT(computers.datetitle, "%d-%m-%Y") as datetitle,
					CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
					CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
					IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
					projects.proyectName as projectName,
					status_requests.description as status,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as date,
					IF(computers.device = 1,"Smartphone",
						IF(computers.device = 2, "Tablet", 
							IF(computers.device = 3, "Laptop", 
								IF(computers.device = 4, "Computadora", "")))) as device
				')
				->leftJoin('computers','computers.idFolio','request_models.folio')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
				->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
				->leftJoin('projects','projects.idproyect','request_models.idProject')
				->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
				->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
				->where('request_models.kind',6)
				->where('request_models.status',4)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(66)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(66)->pluck('departament_id'))
				->whereIn('request_models.idProject',Auth::user()->inChargeProject(66)->pluck('project_id'))
				->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid)
				{
					if($enterpriseid != "") 
					{
						$query->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
					}
					if($name != "")
					{
						$query->where(function($q) use ($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%')
								->orWhere(DB::raw("CONCAT_WS(' ',elaborateUser.name,elaborateUser.last_name,elaborateUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
						$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('request_models.reviewDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1  = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2  = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment    = (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-cómputo.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$headers     = ['Reporte de autorización de cómputo','','','','','','','','',''];
			$tempHeaders = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader     = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Estado','Fecha de revisión','Dispositivo'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio = '';
			$kindRow   = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow   = !$kindRow;
				}
				$tempArray = [];
				foreach($request as $k => $req)
				{
					$tempArray[] = WriterEntityFactory::createCell($req);
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
		else
		{
			return redirect('error');
		}
	}

	public function exportDelivery(Request $request)
	{
		if(Auth::user()->module->where('id',114)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$requests     = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					computers.title as title,
					DATE_FORMAT(computers.datetitle, "%d-%m-%Y") as datetitle,
					CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name),
					CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name),
					IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
					projects.proyectName as projectName,
					status_requests.description as status,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as date,
					IF(computers.device = 1,"Smartphone",
						IF(computers.device = 2, "Tablet", 
							IF(computers.device = 3, "Laptop", 
								IF(computers.device = 4, "Computadora", "")))) as device
				')
				->leftJoin('computers','computers.idFolio','request_models.folio')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
				->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
				->leftJoin('projects','projects.idproyect','request_models.idProject')
				->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
				->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
				->where('request_models.kind',6)
				->where('request_models.status',5)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(114)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(114)->pluck('departament_id'))
				->whereIn('request_models.idProject',Auth::user()->inChargeProject(114)->pluck('project_id'))
				->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid)
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
						$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%')->orWhere(DB::raw("CONCAT_WS(' ',elaborateUser.name,elaborateUser.last_name,elaborateUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
						$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('request_models.reviewDate','DESC')
				->orderBy('request_models.folio','DESC')
				->get();
			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1  = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2  = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment    = (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Entrega-de-cómputo.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');
			$headers     = ['Reporte de entrega de cómputo','','','','','','','','',''];
			$tempHeaders = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader     = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Estado','Fecha de Autorización','Dispositivo'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			$tempFolio = '';
			$kindRow   = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				$tempArray = [];
				foreach($request as $k => $req)
				{
					$tempArray[] = WriterEntityFactory::createCell($req);
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
		else
		{
			return redirect('error');
		}
	}
}
