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
use Illuminate\Support\Facades\Cookie;

class AdministracionComplementoNominaController extends Controller
{
	private $module_id =25;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details' 	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',30)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$users			= App\User::orderName()->where('status','ACTIVE')->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->get();
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(30)->pluck('enterprise_id'))->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(30)->pluck('departament_id'))->get();
			$projects		= App\Project::orderName()->get();
			$banks			= App\Banks::orderName()->get();
			$kindbanks		= App\KindOfBanks::orderName()->get();
			return view('administracion.complementonomina.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 30, 
					'enterprises' 	=> $enterprises,
					'areas'			=> $areas,
					'departments'	=> $departments,
					'users' 		=> $users,
					'projects'		=> $projects,
					'banks'			=> $banks,
					'kindbanks'		=> $kindbanks
				]);
		}
		else
		{
			return abort(404);
		}
	}
	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',30)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$users			= App\User::orderName()->where('status','ACTIVE')->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->get();
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(30)->pluck('enterprise_id'))->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(30)->pluck('departament_id'))->get();
			$projects		= App\Project::orderName()->get();
			$banks			= App\Banks::orderName()->get();
			$kindbanks		= App\KindOfBanks::orderName()->get();
			$labels 		= DB::table('request_has_labels')
							->join('labels','idLabels','labels_idlabels')
							->select('labels.description as descr')
							->where('request_has_labels.request_folio',$id)
							->get();
			$request 		= App\RequestModel::whereIn('status',[5, 6, 7,10,11,12,13])
							->where('kind',2)
							->where(function ($query)
							{
								$query->where('idElaborate',Auth::user()->id)
									->orWhere('idRequest',Auth::user()->id);
							})
							->find($id);
			if ($request != "") 
			{
				return view('administracion.complementonomina.alta',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 30, 
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'users' 		=> $users,
						'projects'		=> $projects,
						'banks'			=> $banks,
						'kindbanks'		=> $kindbanks,
						'request'		=> $request,
						'labels'		=> $labels
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

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 2;
			$t_request->status			= 3;
			$t_request->fDate			= Carbon::now();
			
			$t_request->idRequest 		= $request->user_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$t_nominaApp				= new App\NominaApplication();
			$t_nominaApp->title 		= $request->title;
			$t_nominaApp->datetitle 	= $request->datetitle;
			$t_nominaApp->idFolio		= $folio;
			$t_nominaApp->idKind		= $kind;
			$t_nominaApp->amount 		= $request->total;
			$t_nominaApp->save();
			$idNominaApplication		= $t_nominaApp->idNominaApplication;

			$countN						= count($request->t_employee_number);
			for ($i=0; $i < $countN; $i++) 
			{ 
				if ($request->t_idpaymentmethod[$i] == '1') 
				{
					$t_nominaAppEmp                      = new App\NominaAppEmp();
					$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
					$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
					$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
					$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
					$t_nominaAppEmp->idArea              = $request->t_direction[$i];
					$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
					$t_nominaAppEmp->idProject           = $request->t_project[$i];
					$t_nominaAppEmp->bank                = $request->t_bank[$i];
					$t_nominaAppEmp->account             = $request->t_account[$i];
					$t_nominaAppEmp->clabe               = $request->t_clabe[$i];
					$t_nominaAppEmp->cardNumber          = $request->t_card_number[$i];
					$t_nominaAppEmp->reference           = $request->t_reference[$i];
					$t_nominaAppEmp->amount              = $request->t_amount[$i];
					$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
					$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
					$t_nominaAppEmp->save();
				}
				else
				{
					$t_nominaAppEmp                      = new App\NominaAppEmp();
					$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
					$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
					$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
					$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
					$t_nominaAppEmp->idArea              = $request->t_direction[$i];
					$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
					$t_nominaAppEmp->idProject           = $request->t_project[$i];
					$t_nominaAppEmp->reference           = $request->t_reference[$i];
					$t_nominaAppEmp->amount              = $request->t_amount[$i];
					$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
					$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
					$t_nominaAppEmp->save();
				}
			}
			$alert = "swal('', 'Solicitud Creada Exitosamente', 'success');";
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 49);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',49)
						->where('user_has_department.departament_id',$request->department_id)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			$user 	=  App\User::find($request->user_id);
			if ($emails != "") 
			{
				foreach ($emails as $email)
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Complemento de Nómina";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$url 			= route('payroll.review.edit',['id'=>$folio]);
					$subject 		= "Solicitud por Revisar";
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}
			return redirect('administration/payroll')->with('alert',$alert);
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
			$output 	= "";
			$accounts 	= App\Account::where('idEnterprise',$request->enterpriseid)
							->where('selectable',1)
							->get();
			if (count($accounts) > 0) 
			{
				return Response($accounts);
			}
		}
	}

	public function getEmployee(Request $request)
	{
			if($request->ajax())
			{
				$output     = "";
				$header     = "";
				$footer     = "";
				$users      = App\User::where('status','active')
									->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$request->search.'%')
									->get(); 
				$countUsers = count($users);
				if ($countUsers >= 1) 
				{
					$header = "<br><br><center><strong>SELECCIONE UN EMPLEADO</strong></center><div class='divisor'><div class='gray-divisor'></div><div class='orange-divisor'></div><div class='gray-divisor'></div></div><br><table id='table' class='table table-hover'><thead><tr><th>ID</th><th>Nombre</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Acción</th></tr></thead><tbody class='request-validate'>";
					$footer = "</tbody></table>";
					foreach ($users as $user) 
					{
						$output.=   "<tr>".
									"<td><span id='id".$user->id."'>".$user->id."</span></td>".
									"<td><span id='name".$user->id."'>".$user->name."</span></td>".
									"<td><span id='last_name".$user->id."'>".$user->last_name."</span></td>".
									"<td><span id='scnd_last_name".$user->id."'>".$user->scnd_last_name."</span></td>".
									"<td><button type='button' class='btn btn-green edit' value='".$user->id."'>Ver Cuentas</button</td>".
									"</tr>";
							
					}
					return Response($header.$output.$footer);
				}
				else
				{
					$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
					return Response($notfound);
				}
			}
	}

	public function getBanks(Request $request)
	{
		if ($request->ajax()) 
		{
			$outputB    = "";
			$headerB    = "";
			$footerB    = "";
			$banks 		= App\Employee::join('banks','employees.idBanks','banks.idBanks')
						->where('idUsers',$request->employee_number)
						->where('visible',1)
						->get();
			$countBanks = count($banks);
			if ($countBanks >= 1) 
			{
				$headerB = "<br><br><center><strong>SELECCIONE UNA CUENTA</strong></center><div class='divisor'><div class='gray-divisor'></div><div class='orange-divisor'></div><div class='gray-divisor'></div></div><br> <table id='table2' class='table-no-bordered'><thead class='table-no-background'><tr><th class='table-no-background'>Banco</th><th class='table-no-background'>Alias</th><th class='table-no-background'>Número de tarjeta</th><th class='table-no-background'>CLABE</th><th class='table-no-background'>Número de cuenta</th><th class='table-no-background'>Seleccionar</th></tr></thead><tbody class='request-validate'>";
				$footerB = "</tbody></table>";
				foreach ($banks as $bank) 
				{
					$alias 		= $bank->alias!=null ? $bank->alias : '-----';
					$cardNumber = $bank->cardNumber!=null ? $bank->cardNumber : '-----';
					$clabe 		= $bank->clabe!=null ? $bank->clabe : '-----';
					$account 	= $bank->account!=null ? $bank->account : '-----';

					$outputB.= "<tr>".
								"<td>".$bank->description."".
								"<input type='hidden' name='bank[]' class='input-text input-extrasmall4 bank' placeholder='Ingrese un banco' value='".$bank->description."'>".
								"</td>".
								"<td>".$alias."".
								"<input type='hidden' name='alias[]' class='input-text input-extrasmall4 alias' placeholder='Ingrese un alias' value='".$bank->alias."'>".
								"</td>".
								"<td>".$cardNumber."".
								"<input type='hidden' name='card[]' class='input-text input-extrasmall4 card_number' placeholder='Ingrese un número de tarjeta' value='".$cardNumber."'>".
								"</td>".
								"<td>".$clabe."".
								"<input type='hidden' name='clabe[]' class='input-text input-extrasmall4 clabe' placeholder='Ingrese una CLABE' value='".$clabe."'>".
								"</td>".
								"<td>".$account."".
								"<input type='hidden' name='account[]' class='input-text input-extrasmall4 account' placeholder='Ingrese una cuenta bancaria' value='".$account."'>".
								"</td>".
								"<td>".
								"<input id='idEmp".$bank->idEmployee."' type='radio' name='idemp' class='checkbox' value='".$bank->idEmployee."' class='btn btn-green'>".
								"<label class='check-small request-validate' for='idEmp".$bank->idEmployee."'><span class='icon-checkmark'></span></label>".
								"</td>".
								"</tr>";
				}
				return Response($headerB.$outputB.$footerB);
			}
			else
			{
				$notfound = '<div id="not-found" style="display:block;">NO HAY CUENTA REGISTRADA</div>';
				return Response($notfound);
			}
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',31)->count()>0)
		{
			$data 		= App\Module::find($this->module_id);
			$name 		= $request->name;
			$folio 		= $request->folio;
			$status 	= $request->status;
			$mindate   	= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate    = $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;

			if ($request->mindate != "") 
			{
				$mindate 	= date('Y-m-d',strtotime($request->mindate));
				$maxdate 	= date('Y-m-d',strtotime($request->maxdate));
			}

			$searchUser 	= App\User::select('users.id')->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')->get();


			$requests		= App\RequestModel::where('kind','2')
								->where(function ($query)
								{
									$query->where('idElaborate',Auth::user()->id)
										->orWhere('idRequest',Auth::user()->id);
								})
								->where(function ($query) use ($name, $mindate, $maxdate, $folio, $status,$searchUser)
								{
									if($name != "")
									{
										$query->whereIn('request_models.idRequest',$searchUser);
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
										$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);

			return view('administracion.complementonomina.busqueda',
				[
					'id'		=> $data['father'],
					'title' 	=> $data['name'],
					'details' 	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 31,
					'requests'	=> $requests,
					'folio'		=> $folio,
					'name'		=> $name,
					'status'	=> $status,
					'mindate'	=> $mindate,
					'maxdate'	=> $maxdate,
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function follow($id) 
	{ 
		if(Auth::user()->module->where('id',31)->count()>0)
		{
			$data			= App\Module::find($this->module_id); 
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(31)->pluck('enterprise_id'))->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(31)->pluck('departament_id'))->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->get(); 
			$projects		= App\Project::orderName()->get();
			$banks			= App\Banks::orderName()->get();
			$labels			= DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			$request    	= App\RequestModel::where('kind',2)
							->where(function ($query)
							{
								$query->where('idElaborate',Auth::user()->id)
									->orWhere('idRequest',Auth::user()->id);
							})
							->find($id);
			if ($request != "") 
			{
				 return view('administracion.complementonomina.seguimiento',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 31,
						'projects' 		=> $projects, 
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'request'		=> $request,
						'banks'			=> $banks,
						'labels'		=> $labels
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

	public function unsent(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request					= new App\RequestModel();
			$t_request->kind			= 2;
			$t_request->status			= 2;
			$t_request->fDate			= Carbon::now();
			$t_request->account 		= $request->accountid;
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest 		= $request->user_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->idProject 		= $request->projectid;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$t_nominaApp				= new App\NominaApplication();
			$t_nominaApp->title 		= $request->title;
			$t_nominaApp->datetitle 	= $request->datetitle;
			$t_nominaApp->idFolio		= $folio;
			$t_nominaApp->idKind		= $kind;
			$t_nominaApp->amount 		= $request->total;
			$t_nominaApp->save();
			$idNominaApplication		= $t_nominaApp->idNominaApplication;

			if ($request->t_employee_number == "") 
			{
				$t_nominaAppEmp 						= new App\NominaAppEmp();
				$t_nominaAppEmp->idNominaApplication 	= $idNominaApplication;
				$t_nominaAppEmp->save();
			}
			else
			{
				$countN						= count($request->t_employee_number);
				for ($i=0; $i < $countN; $i++) 
				{ 
					if ($request->t_idpaymentmethod[$i] == '1') 
					{
						$t_nominaAppEmp                      = new App\NominaAppEmp();
						$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
						$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
						$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
						$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
						$t_nominaAppEmp->idArea              = $request->t_direction[$i];
						$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
						$t_nominaAppEmp->idProject           = $request->t_project[$i];
						$t_nominaAppEmp->bank                = $request->t_bank[$i];
						$t_nominaAppEmp->account             = $request->t_account[$i];
						$t_nominaAppEmp->clabe               = $request->t_clabe[$i];
						$t_nominaAppEmp->cardNumber          = $request->t_card_number[$i];
						$t_nominaAppEmp->reference           = $request->t_reference[$i];
						$t_nominaAppEmp->amount              = $request->t_amount[$i];
						$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
						$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
						$t_nominaAppEmp->save();
					}
					else
					{
						$t_nominaAppEmp                      = new App\NominaAppEmp();
						$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
						$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
						$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
						$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
						$t_nominaAppEmp->idArea              = $request->t_direction[$i];
						$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
						$t_nominaAppEmp->idProject           = $request->t_project[$i];
						$t_nominaAppEmp->reference           = $request->t_reference[$i];
						$t_nominaAppEmp->amount              = $request->t_amount[$i];
						$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
						$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
						$t_nominaAppEmp->save();
					}
				}
			}
			
			$alert = "swal('', 'Solicitud Guardada Exitosamente', 'success');";
			return redirect()->route('payroll.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function updateFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data 					= App\Module::find($this->module_id);
			$follow 				= App\RequestModel::find($id);
			$follow->fDate 			= Carbon::now();
			$follow->status 		= 3;
			$follow->account 		= $request->accountid;
			$follow->idEnterprise 	= $request->enterprise_id;
			$follow->idArea 		= $request->area_id;
			$follow->idDepartment 	= $request->department_id;
			$follow->idRequest 		= $request->user_id;
			$follow->idProject 		= $request->projectid;
			$follow->save();

			$t_nominaApp 			= App\NominaApplication::find($request->idNominaApplication);
			$t_nominaApp->title 	= $request->title;
			$t_nominaApp->datetitle = $request->datetitle;
			$t_nominaApp->amount 	= $request->total;
			$t_nominaApp->idFolio	= $id;
			$t_nominaApp->idKind	= 2;
			$t_nominaApp->save();

			$delete 		= App\NominaAppEmp::where('idNominaApplication',$request->idNominaApplication)
							->delete();
			$idNominaApplication = $request->idNominaApplication;
			$countN						= count($request->t_employee_number);
			for ($i=0; $i < $countN; $i++) 
			{ 
				if ($request->t_idpaymentmethod[$i] == '1') 
				{
					$t_nominaAppEmp                      = new App\NominaAppEmp();
					$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
					$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
					$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
					$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
					$t_nominaAppEmp->idArea              = $request->t_direction[$i];
					$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
					$t_nominaAppEmp->idProject           = $request->t_project[$i];
					$t_nominaAppEmp->bank                = $request->t_bank[$i];
					$t_nominaAppEmp->account             = $request->t_account[$i];
					$t_nominaAppEmp->clabe               = $request->t_clabe[$i];
					$t_nominaAppEmp->cardNumber          = $request->t_card_number[$i];
					$t_nominaAppEmp->reference           = $request->t_reference[$i];
					$t_nominaAppEmp->amount              = $request->t_amount[$i];
					$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
					$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
					$t_nominaAppEmp->save();
				}
				else
				{
					$t_nominaAppEmp                      = new App\NominaAppEmp();
					$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
					$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
					$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
					$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
					$t_nominaAppEmp->idArea              = $request->t_direction[$i];
					$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
					$t_nominaAppEmp->idProject           = $request->t_project[$i];
					$t_nominaAppEmp->reference           = $request->t_reference[$i];
					$t_nominaAppEmp->amount              = $request->t_amount[$i];
					$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
					$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
					$t_nominaAppEmp->save();
				}
			}
			$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 49);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
					->join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',49)
					->where('user_has_department.departament_id',$request->department_id)
					->where('users.active',1)
					->where('users.notification',1)
					->get();*/
			$user 	= App\User::find($request->user_id);

			if ($emails != "") 
			{
				foreach ($emails as $email) 
				{
					$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					$to 			= $email->email;
					$kind 			= "Complemento de Nómina";
					$status 		= "Revisar";
					$date 			= Carbon::now();
					$url 			= route('payroll.review.edit',['id'=>$id]);
					$subject 		= "Solicitud por Revisar";
					$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				}
			}
			return redirect('administration/payroll')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function updateUnsentFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$follow 				= App\RequestModel::find($id);
			$follow->fDate 			= Carbon::now();
			$follow->status 		= 2;
			$follow->account 		= $request->accountid;
			$follow->idEnterprise 	= $request->enterprise_id;
			$follow->idArea 		= $request->area_id;
			$follow->idDepartment 	= $request->department_id;
			$follow->idRequest 		= $request->user_id;
			$follow->idProject 		= $request->projectid;
			$follow->save();

			$t_nominaApp 			= App\NominaApplication::find($request->idNominaApplication);
			$t_nominaApp->title 	= $request->title;
			$t_nominaApp->datetitle = $request->datetitle;
			$t_nominaApp->amount 	= $request->total;
			$t_nominaApp->idFolio	= $id;
			$t_nominaApp->idKind	= 2;
			$t_nominaApp->save();

			$delete 		= App\NominaAppEmp::where('idNominaApplication',$request->idNominaApplication)
							->delete();
			$idNominaApplication = $request->idNominaApplication;
			if ($request->t_employee_number == "") 
			{
				$t_nominaAppEmp 						= new App\NominaAppEmp();
				$t_nominaAppEmp->idNominaApplication 	= $idNominaApplication;
				$t_nominaAppEmp->save();
			}
			else
			{
				$countN						= count($request->t_employee_number);
				for ($i=0; $i < $countN; $i++) 
				{ 
					if ($request->t_idpaymentmethod[$i] == '1') 
					{
						$t_nominaAppEmp                      = new App\NominaAppEmp();
						$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
						$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
						$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
						$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
						$t_nominaAppEmp->idArea              = $request->t_direction[$i];
						$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
						$t_nominaAppEmp->idProject           = $request->t_project[$i];
						$t_nominaAppEmp->bank                = $request->t_bank[$i];
						$t_nominaAppEmp->account             = $request->t_account[$i];
						$t_nominaAppEmp->clabe               = $request->t_clabe[$i];
						$t_nominaAppEmp->cardNumber          = $request->t_card_number[$i];
						$t_nominaAppEmp->reference           = $request->t_reference[$i];
						$t_nominaAppEmp->amount              = $request->t_amount[$i];
						$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
						$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
						$t_nominaAppEmp->save();
					}
					else
					{
						$t_nominaAppEmp                      = new App\NominaAppEmp();
						$t_nominaAppEmp->idNominaApplication = $idNominaApplication;
						$t_nominaAppEmp->idUsers             = $request->t_employee_number[$i];
						$t_nominaAppEmp->idAccount           = $request->t_accountid[$i];
						$t_nominaAppEmp->idEnterprise        = $request->t_enterprise[$i];
						$t_nominaAppEmp->idArea              = $request->t_direction[$i];
						$t_nominaAppEmp->idDepartment        = $request->t_department[$i];
						$t_nominaAppEmp->idProject           = $request->t_project[$i];
						$t_nominaAppEmp->reference           = $request->t_reference[$i];
						$t_nominaAppEmp->amount              = $request->t_amount[$i];
						$t_nominaAppEmp->description         = $request->t_reason_payment[$i];
						$t_nominaAppEmp->idpaymentMethod     = $request->t_idpaymentmethod[$i];
						$t_nominaAppEmp->save();
					}
				}	
			}
			
			$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
			return redirect()->route('payroll.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',49)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate    	= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate    	= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;

			if ($request->mindate != "") 
			{
				$mindate 	= date('Y-m-d',strtotime($request->mindate));
				$maxdate 	= date('Y-m-d',strtotime($request->maxdate));
			}

			$searchUser 	= App\User::select('users.id')->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')->get();

			$requests		= App\RequestModel::where('kind',2)
								->where('status',3)
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$searchUser)
								{
									if($name != "")
									{
										$query->where(function($query2) use ($searchUser)
										{
											$query2->whereIn('request_models.idRequest',$searchUser)->orWhereIn('request_models.idElaborate',$searchUser);
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			
			return response(
				view('administracion.complementonomina.revision',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 49,
						'requests'	=> $requests,
						'folio' 	=> $folio, 
						'name' 		=> $name, 
						'mindate' 	=> $mindate, 
						'maxdate' 	=> $maxdate,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(49), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',49)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::where('kind',2)
							->where('status',3)
							->find($id);
			if ($request != "") 
			{
				return view('administracion.complementonomina.revisioncambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 49,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert = "swal('', 'La solicitud ya fue evaluada en el proceso de Revisión.', 'error');";
				return redirect('administration/payroll/review')->with('alert',$alert);
			}
			
		}
		else
		{
			return abort(404);
		}
	}

	public function updateReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', 'La solicitud ya ha sido revisada.', 'error');";
			}
			else
			{
				if ($request->status == "4") 
				{
					$review 		 		= App\RequestModel::find($id);
					$review->status		 	= $request->status;
					$review->accountR 		= $request->accountR;
					$review->idEnterpriseR 	= $request->idEnterpriseR;
					$review->idDepartamentR	= $request->idDepartmentR;
					$review->idAreaR 		= $request->idAreaR;
					$review->idProjectR		= $request->project_idR;
					$review->idCheck 		= Auth::user()->id;
					$review->reviewDate 	= Carbon::now();
					$review->checkComment 	= $request->checkCommentA;
					$review->save();

					if ($request->idLabels != "") 
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'2'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 50);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',50)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					$user 	= App\User::find($review->idRequest);
					if ($emails != "") 
					{
						foreach ($emails as $email) 
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Complemento de Nómina";
							$status 		= "Autorizar";
							$date 			= Carbon::now();
							$url 			= route('payroll.authorization.edit',['id'=>$id]);
							$subject 		= "Solicitud por Autorizar";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review 		 		= App\RequestModel::find($id);
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate 	= Carbon::now();
					$review->save();
					$emailRequest 			= "";
					
					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->orWhere('id',$review->idRequest)
										->where('notification',1)
										->get();
					}
					if ($emailRequest != "") 
					{
						foreach ($emailRequest as $email) 
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Complemento de Nómina";
							$status 		= "RECHAZADA";
							$date 			= Carbon::now();
							$url 			= route('payroll.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}
				}
				$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
			}
			return searchRedirect(49, $alert, 'administration/payroll');
		}
		else
		{
			return abort(404);
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',50)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name 		= $request->name;
			$folio 		= $request->folio;
			$mindate    = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate    = $request->maxdatedate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;

			if ($request->mindate != "") 
			{
				$mindate 	= date('Y-m-d',strtotime($request->mindate));
				$maxdate 	= date('Y-m-d',strtotime($request->maxdate));
			}

			$searchUser 	= App\User::select('users.id')->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')->get();


			$requests		= App\RequestModel::where('kind',2)
								->where('status',4)
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$searchUser)
								{
									if($name != "")
									{
										$query->whereIn('request_models.idRequest',$searchUser)->orWhereIn('request_models.idElaborate',$searchUser);
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('reviewDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			return response(
				view('administracion.complementonomina.autorizacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id' 	=> $this->module_id,
						'option_id'	=> 50,
						'requests'	=> $requests,
						'folio' 	=> $folio, 
						'name' 		=> $name, 
						'mindate' 	=> $mindate, 
						'maxdate' 	=> $maxdate,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(50), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',50)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::where('kind',2)
							->where('status',4)
							->find($id);
			if($request != "")
			{
				return view('administracion.complementonomina.autorizacioncambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 50,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert = "swal('', 'La solicitud ya fue evaluada en el proceso de Autorización.', 'error');";
				return redirect('administration/payroll/authorization')->with('alert',$alert);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$checkStatus 	= App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', 'La solicitud ya ha sido revisada.', 'error');";
			}
			else
			{
				$authorize						= App\RequestModel::find($id);
				$authorize->status				= $request->status;
				$authorize->idAuthorize			= Auth::user()->id;
				$authorize->authorizeComment	= $request->authorizeCommentA;
				$authorize->authorizeDate 		= Carbon::now();
				$authorize->save();
				$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";

				$emailRequest 			= "";
					
				if ($authorize->idElaborate == $authorize->idRequest) 
				{
					$emailRequest 	= App\User::where('id',$authorize->idElaborate)
									->where('notification',1)
									->get();
				}
				else
				{
					$emailRequest 	= App\User::where('id',$authorize->idElaborate)
									->orWhere('id',$authorize->idRequest)
									->where('notification',1)
									->get();
				}
				
				$emailPay 		= App\User::join('user_has_modules','users.id','user_has_modules.user_id')
									->where('user_has_modules.module_id',90)
									->where('users.active',1)
									->where('users.notification',1)
									->get();
				$user 			= App\User::find($authorize->idRequest);
				if ($emailRequest != "") 
				{
					foreach ($emailRequest as $email) 
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Complemento de Nómina";
						if ($request->status == 5) 
						{
							$status = "AUTORIZADA";
						}
						else
						{
							$status = "RECHAZADA";
						}
						$date 			= Carbon::now();
						$url 			= route('payroll.follow.edit',['id'=>$id]);
						$subject 		= "Estado de Solicitud";
						$requestUser 	= null;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
				}
				if ($request->status == 5)
				{
					if ($emailPay != "") 
					{
						foreach ($emailPay as $email) 
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Complemento de Nómina";
							$status 		= "Pendiente";
							$date 			= Carbon::now();
							$url 			= route('payments.review.edit',['id'=>$id]);
							$subject 		= "Solicitud Pendiente de Pago";
							$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}
				}
			}
			return searchRedirect(50, $alert, 'administration/payroll');
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',31)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			Excel::create('Autorización-de-nomina', function($excel) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
			{
				$excel->sheet('Autorización',function($sheet) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
				{
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'C' => 'yyyy-mm-dd',
						'G' => 'yyyy-mm-dd',
						'H' => '$#,##0_-',
						'M' => '0',
						'N' => '0',
						'O' => '0',
						'V' => '$#,##0_-',
					));
					$sheet->mergeCells('A1:V1');
					$sheet->cell('A1:V1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:V2', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:V2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Reporte de seguimiento de nomina']);
					$sheet->row(2,['Folio','Título','Fecha','Solicitante','Elaborado por','Estado','Fecha de elaboración','Monto','Nombre del Empleado','Empresa','Departamento','Dirección','Proyecto','Clasificación de gasto','Forma de pago','Banco','Tarjeta','Cuenta','Clave','Referencia','Razón de pago','Importe']);

					$requests	= App\RequestModel::where('kind','2')
								->where(function ($query)
								{
									$query->where('idElaborate',Auth::user()->id)
										->orWhere('idRequest',Auth::user()->id);
								})
								->where(function ($query) use ($name, $mindate, $maxdate, $folio, $status,$searchUser)
								{
									if($name != "")
									{
										$query->whereIn('request_models.idRequest',$searchUser);
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
										$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->get();
					$beginMerge	= 2;
					foreach ($requests as $request)
					{
						$tempCount	= 0;
						$row		= [];
						$row[]		= $request->folio;
						$row[]		= $request->nominas->first()->title;
						$row[]		= $request->nominas->first()->datetitle;
						$row[]		= $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$row[]		= $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$row[]		= $request->statusrequest->description;
						$row[]		= date('d-m-Y H:s',strtotime($request->fDate));
						$row[]		= $request->nominas->first()->amount;
						$first		= true;
						foreach($request->nominas->first()->noAppEmp as $noEmp)
						{
							if(!$first)
							{
								$row	= array();
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							else
							{
								$first	= false;
								$beginMerge++;
							}
							$row[]	= $noEmp->employee->name.' '.$noEmp->employee->last_name.' '.$noEmp->employee->scnd_last_name;
							$row[] 	= $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
							$row[] 	= $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
							$row[] 	= $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
							$row[]  = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
							$row[] 	= $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
							$row[]	= $noEmp->paymentMethod->method;
							$row[]	= $noEmp->bank;
							$row[]	= $noEmp->cardNumber;
							$row[]	= $noEmp->account;
							$row[]	= $noEmp->clabe;
							$row[]	= $noEmp->reference;
							$row[]	= $noEmp->description;
							$row[]	= $noEmp->amount;
							$tempCount++;
							$sheet->appendRow($row);
						}
						$endMerge = $beginMerge+$tempCount-1;
						$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
						$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
						$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
						$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
						$sheet->mergeCells('E'.$beginMerge.':E'.$endMerge);
						$sheet->mergeCells('F'.$beginMerge.':F'.$endMerge);
						$sheet->mergeCells('G'.$beginMerge.':G'.$endMerge);
						$sheet->mergeCells('H'.$beginMerge.':H'.$endMerge);
						$beginMerge = $endMerge;
					}
				});
			})->export('xls');
		}
		else
		{
			return abort(404);
		}
	}

	public function exportReview(Request $request)
	{
		if(Auth::user()->module->where('id',49)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			Excel::create('Revision-de-nomina', function($excel) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
			{
				$excel->sheet('Revisión',function($sheet) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
				{
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'C' => 'yyyy-mm-dd',
						'G' => 'yyyy-mm-dd',
						'H' => '$#,##0_-',
						'M' => '0',
						'N' => '0',
						'O' => '0',
						'V' => '$#,##0_-',
					));
					$sheet->mergeCells('A1:V1');
					$sheet->cell('A1:V1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:V2', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:V2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Reporte de seguimiento de nomina']);
					$sheet->row(2,['Folio','Título','Fecha','Solicitante','Elaborado por','Estado','Fecha de elaboración','Monto','Nombre del Empleado','Empresa','Departamento','Dirección','Proyecto','Clasificación de gasto','Forma de pago','Banco','Tarjeta','Cuenta','Clave','Referencia','Razón de pago','Importe']);

					$requests	= App\RequestModel::where('kind',2)
								->where('status',3)
								->where(function ($query) use ($name, $folio, $mindate, $maxdate, $searchUser)
								{
									if($name != "")
									{
										$query->whereIn('request_models.idRequest',$searchUser);
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->get();
					$beginMerge	= 2;
					foreach ($requests as $request)
					{
						$tempCount	= 0;
						$row		= [];
						$row[]		= $request->folio;
						$row[]		= $request->nominas->first()->title;
						$row[]		= $request->nominas->first()->datetitle;
						$row[]		= $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$row[]		= $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$row[]		= $request->statusrequest->description;
						$row[]		= date('d-m-Y H:s',strtotime($request->fDate));
						$row[]		= $request->nominas->first()->amount;
						$first		= true;
						foreach($request->nominas->first()->noAppEmp as $noEmp)
						{
							if(!$first)
							{
								$row	= array();
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							else
							{
								$first	= false;
								$beginMerge++;
							}
							$row[]	= $noEmp->employee->name.' '.$noEmp->employee->last_name.' '.$noEmp->employee->scnd_last_name;
							$row[] 	= $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
							$row[] 	= $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
							$row[] 	= $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
							$row[]  = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
							$row[] 	= $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
							$row[]	= $noEmp->paymentMethod->method;
							$row[]	= $noEmp->bank;
							$row[]	= $noEmp->cardNumber;
							$row[]	= $noEmp->account;
							$row[]	= $noEmp->clabe;
							$row[]	= $noEmp->reference;
							$row[]	= $noEmp->description;
							$row[]	= $noEmp->amount;
							$tempCount++;
							$sheet->appendRow($row);
						}
						$endMerge = $beginMerge+$tempCount-1;
						$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
						$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
						$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
						$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
						$sheet->mergeCells('E'.$beginMerge.':E'.$endMerge);
						$sheet->mergeCells('F'.$beginMerge.':F'.$endMerge);
						$sheet->mergeCells('G'.$beginMerge.':G'.$endMerge);
						$sheet->mergeCells('H'.$beginMerge.':H'.$endMerge);
						$beginMerge = $endMerge;
					}
				});
			})->export('xls');
		}
		else
		{
			return abort(404);
		}
	}

	public function exportAuthorize(Request $request)
	{
		if(Auth::user()->module->where('id',50)->count()>0)
		{
			
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			Excel::create('Autorización-de-nomina', function($excel) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
			{
				$excel->sheet('Autorización',function($sheet) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $searchUser)
				{
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'C' => 'yyyy-mm-dd',
						'G' => 'yyyy-mm-dd',
						'H' => '$#,##0_-',
						'M' => '0',
						'N' => '0',
						'O' => '0',
						'V' => '$#,##0_-',
					));
					$sheet->mergeCells('A1:V1');
					$sheet->cell('A1:V1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:V2', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:V2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Reporte de seguimiento de nomina']);
					$sheet->row(2,['Folio','Título','Fecha','Solicitante','Elaborado por','Estado','Fecha de elaboración','Monto','Nombre del Empleado','Empresa','Departamento','Dirección','Proyecto','Clasificación de gasto','Forma de pago','Banco','Tarjeta','Cuenta','Clave','Referencia','Razón de pago','Importe']);

					$requests	= App\RequestModel::where('kind',2)
								->where('status',4)
								->where(function ($query) use ($name, $folio, $mindate, $maxdate, $searchUser)
								{
									if($name != "")
									{
										$query->whereIn('request_models.idRequest',$searchUser);
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->get();
					$beginMerge	= 2;
					foreach ($requests as $request)
					{
						$tempCount	= 0;
						$row		= [];
						$row[]		= $request->folio;
						$row[]		= $request->nominas->first()->title;
						$row[]		= $request->nominas->first()->datetitle;
						$row[]		= $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
						$row[]		= $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
						$row[]		= $request->statusrequest->description;
						$row[]		= date('d-m-Y H:s',strtotime($request->fDate));
						$row[]		= $request->nominas->first()->amount;
						$first		= true;
						foreach($request->nominas->first()->noAppEmp as $noEmp)
						{
							if(!$first)
							{
								$row	= array();
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							else
							{
								$first	= false;
								$beginMerge++;
							}
							$row[]	= $noEmp->employee->name.' '.$noEmp->employee->last_name.' '.$noEmp->employee->scnd_last_name;
							$row[] 	= $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
							$row[] 	= $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
							$row[] 	= $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
							$row[]  = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
							$row[] 	= $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
							$row[]	= $noEmp->paymentMethod->method;
							$row[]	= $noEmp->bank;
							$row[]	= $noEmp->cardNumber;
							$row[]	= $noEmp->account;
							$row[]	= $noEmp->clabe;
							$row[]	= $noEmp->reference;
							$row[]	= $noEmp->description;
							$row[]	= $noEmp->amount;
							$tempCount++;
							$sheet->appendRow($row);
						}
						$endMerge = $beginMerge+$tempCount-1;
						$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
						$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
						$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
						$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
						$sheet->mergeCells('E'.$beginMerge.':E'.$endMerge);
						$sheet->mergeCells('F'.$beginMerge.':F'.$endMerge);
						$sheet->mergeCells('G'.$beginMerge.':G'.$endMerge);
						$sheet->mergeCells('H'.$beginMerge.':H'.$endMerge);
						$beginMerge = $endMerge;
					}
				});
			})->export('xls');
		}
		else
		{
			return abort(404);
		}
	}
}
