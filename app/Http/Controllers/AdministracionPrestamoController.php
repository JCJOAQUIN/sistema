<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use PDF;
use App;
use Lang;
use Alert;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
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

class AdministracionPrestamoController extends Controller
{
	private $module_id = 67;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
		if(Auth::user()->module->where('id',68)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$users          = App\User::orderName()->where('status','ACTIVE')->where('sys_user',1)->get();
			$areas          = App\Area::orderName()->where('status','ACTIVE')->get();
			$enterprises    = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(68)->pluck('enterprise_id'))->get();
			$departments    = App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(68)->pluck('departament_id'))->get();
			$projects       = App\Project::orderName()->get();
			$banks          = App\Banks::orderName()->get();
			$kindbanks      = App\KindOfBanks::orderName()->get();
			return view('administracion.prestamo.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 68,
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
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',68)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',69)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',69)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= App\Module::find($this->module_id);
			$users			= App\User::where('status','ACTIVE')->where('sys_user',1)->get();
			$areas			= App\Area::where('status','ACTIVE')->get();
			$enterprises    = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(68)->pluck('enterprise_id'))->get();
			$departments    = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(68)->pluck('departament_id'))->get();
			$projects		= App\Project::all();
			$banks			= App\Banks::all();
			$labels 		= DB::table('request_has_labels')
							->join('labels','idLabels','labels_idlabels')
							->select('labels.description as descr')
							->where('request_has_labels.request_folio',$id)
							->get();
			$request 		= App\RequestModel::where('kind',5)
							->whereIn('status',[5, 6, 7,10,11,12,13])
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
				return view('administracion.prestamo.alta',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 68,
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments, 
						'users' 		=> $users,
						'projects'		=> $projects,
						'banks'			=> $banks,
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
			return redirect('/');
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
				$header = "<table id='table' class='table table-hover'><thead><tr><th>ID</th><th>Nombre</th><th>Apellido Paterno</th><th>Apellido Materno</th><th>Acción</th></tr></thead><tbody class='request-validate'>";
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
			$banks 	= App\Employee::join('banks','employees.idBanks','banks.idBanks')
				->where('visible',1)
				->where('idUsers',$request->idUsers)
				->get();
			$countBanks = count($banks);
			if ($countBanks >= 1) 
			{
				$html		= '';
				$body		= [];
				$modelBody	= [];
				$html .= '<div class="m-4">';
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.title-divisor',['slot'=> 'Seleccione Una Cuenta'])));
				$html .= '</div>';
				$modelHead = [
					[
						["value" => "Acción"],
						["value" => "Banco"],
						["value" => "Alias"],
						["value" => "Número de tarjeta"],
						["value" => "CLABE"],
						["value" => "Número de cuenta"]
					]
				];

				foreach ($banks as $bank) 
				{
					$body =
					[	"classEx" => "tr_bank",
						[
							"content" =>
							[
								"kind"				=> "components.inputs.checkbox",
								"classEx"			=> "checkbox",
								"attributeEx"		=> "name=\"idEmployee\" id=\"idEmp".$bank->idEmployee."\"".' '."value=\"".$bank->idEmployee."\"",
								"classExLabel"		=> "request-validate",
								"label"				=> "<span class=\"icon-check\"></span>",
								"classExContainer"	=> "my-2",
								"radio"				=> true
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->description
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"bank[]\" placeholder=\"Ingrese un banco\" value=\"".$bank->description."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->alias != null ? $bank->alias : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"alias[]\" placeholder=\"Ingrese un alias\" value=\"".$bank->alias."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->cardNumber != null ? $bank->cardNumber : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"card[]\" placeholder=\"Ingrese un número de tarjeta\" value=\"".$bank->cardNumber."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->clabe != null ? $bank->clabe : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\" placeholder=\"Ingrese una CLABE\" value=\"".$bank->clabe."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $bank->account != null ? $bank->account : '---'
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"account[]\" placeholder=\"Ingrese un cuenta bancaria\" value=\"".$bank->account."\""
								]
							]
						]		
					];
					$modelBody[] = $body;
				}
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
					"attributeEx" 	=> "id=\"table2\"",
					"classExBody"	=> "request-validate",
					"modelBody"		=> $modelBody,
					"modelHead"		=> $modelHead,
				])));;
				return Response($html);
			}
			else
			{
				$notfound = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.not-found',
					[
						"text" => "No se han encontrado cuentas registradas."
					])
				));
				return Response($notfound);
			}
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 5;
			$t_request->status			= 3;
			$t_request->fDate			= Carbon::now();
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest 		= $request->user_id;
			$t_request->account         = $request->account_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			$t_loan						= new App\Loan();
			$t_loan->idUsers			= $request->user_id;
			$t_loan->idFolio			= $folio;
			$t_loan->idKind				= $kind;
			$t_loan->title 		  		= $request->title;
			$t_loan->datetitle 	  		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->reference 			= $request->reference;
			$t_loan->amount 			= $request->amount;
			if ($request->method == 1) 
			{
				$t_loan->idEmployee 		= $request->idEmployee;
			}
			else
			{
				$t_loan->idEmployee 		= null;
			}
			$t_loan->idpaymentMethod 	= $request->method;
			$t_loan->save(); 


			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 70);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',70);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',70);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',70)
		   				->where('user_has_department.departament_id',$request->department_id)
		   				->where('users.active',1)
		   				->where('users.notification',1)
		   				->get();*/
		   	$user 	=  App\User::find($request->user_id);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Préstamo Personal";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= route('loan.review.edit',['id'=>$folio]);
						$subject 		= "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
            return redirect('administration/loan')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function unsent(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$t_request					= new App\RequestModel();
			$t_request->kind			= 5;
			$t_request->status			= 2;
			$t_request->fDate			= Carbon::now();
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest 		= $request->user_id;
			$t_request->account         = $request->account_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			
			$t_loan						= new App\Loan();
			$t_loan->idUsers			= $request->user_id;
			$t_loan->idFolio			= $folio;
			$t_loan->idKind				= $kind;
			$t_loan->title 		  		= $request->title;
			$t_loan->datetitle 	  		= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->reference 			= $request->reference;
			$t_loan->amount 			= $request->amount;
			if ($request->method == 1) 
			{
				$t_loan->idEmployee 		= $request->idEmployee;
			}
			else
			{
				$t_loan->idEmployee 		= null;
			}
			$t_loan->idpaymentMethod 	= $request->method;
			$t_loan->save();


			$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('loan.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',69)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',69)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',69)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data           = App\Module::find($this->module_id);
			$account 		= $request->account;
			$name 			= $request->name;
			$folio 			= $request->folio;
			$status 		= $request->status;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
		    $maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		    $enterpriseid 	= $request->enterpriseid;

			$requests		= App\RequestModel::where('kind','5')
								->where(function($q) 
								{
									$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(69)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
								})
								->where(function ($q) 
								{
									$q->whereIn('idDepartment',Auth::user()->inChargeDep(69)->pluck('departament_id'))->orWhereNull('idDepartment');
								})
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $status, $enterpriseid)
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
										$query->where(function($query2) use($account)
										{
											$query2->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
										});
									}
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->whereHas('requestUser', function($qRequest) use($name)
											{
												$qRequest->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($qElaborate) use($name)
											{
												$qElaborate->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
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
		
			return view('administracion.prestamo.busqueda',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 69,
					'requests'	=> $requests,
					'account'	=> $account,
					'name'		=> $name,
					'folio'		=> $folio, 	
					'status'	=> $status,
					'mindate'	=> $request->mindate,
					'maxdate'	=> $request->maxdate,
					'enterpriseid' => $enterpriseid
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{ 
		if(Auth::user()->module->where('id',69)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',69)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',69)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data       	= App\Module::find($this->module_id); 
		    $enterprises    = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(69)->pluck('enterprise_id'))->get();
			$departments    = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(69)->pluck('departament_id'))->get();
		    $areas          = App\Area::where('status','ACTIVE')->get(); 
		    $projects 		= App\Project::all();
			$banks 			= App\Banks::all();
			$labels 		= DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
		    $request    	= App\RequestModel::where('kind',5)
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
				return view('administracion.prestamo.seguimiento',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 69,
						'projects' 		=> $projects, 
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'request'		=> $request,
						'banks'			=> $banks,
						'labels'		=> $labels,
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
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data 					= App\Module::find($this->module_id);
			$follow 				= App\RequestModel::find($id);
			$follow->fDate 			= Carbon::now();
			$follow->status 		= 3;
			$follow->idEnterprise 	= $request->enterprise_id;
			$follow->idArea 		= $request->area_id;
			$follow->idDepartment 	= $request->department_id;
			$follow->idRequest 		= $request->user_id;
			$follow->account        = $request->account_id;
			$follow->save();

			foreach (App\Loan::where('idFolio',$id)->get() as $loan) 
			{
				$idLoan 			= $loan->idLoan;
			}

			$t_loan					= App\Loan::find($idLoan);
			$t_loan->idUsers		= $request->user_id;
			$t_loan->idFolio		= $id;
			$t_loan->idKind			= 5;
			$t_loan->title 		  	= $request->title;
			$t_loan->datetitle 	  	= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->reference 		= $request->reference;
			$t_loan->amount 		= $request->amount;
			
			if ($request->method == 1) 
			{
				$t_loan->idEmployee 		= $request->idEmployee;
			}
			else
			{
				$t_loan->idEmployee 		= null;
			}

			$t_loan->idpaymentMethod= $request->method;
			$t_loan->save();

			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 70);
						})
						->whereHas('inChargeDepGet',function($q) use ($follow)
						{
							$q->where('departament_id', $follow->idDepartment)
								->where('module_id',70);
						})
						->whereHas('inChargeEntGet',function($q) use ($follow)
						{
							$q->where('enterprise_id', $follow->idEnterprise)
								->where('module_id',70);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',70)
		   				->where('user_has_department.departament_id',$request->department_id)
		   				->where('users.active',1)
		   				->where('users.notification',1)
		   				->get();*/
		   	$user 	= App\User::find($request->user_id);
			if ($emails != "")
			{
				try
				{
				   	foreach ($emails as $email)
				   	{
						$name			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to				= $email->email;
						$kind			= "Préstamo";
						$status			= "Revisar";
						$date			= Carbon::now();
						$url			= route('loan.review.edit',['id'=>$id]);
						$subject		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				   		Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				   	}
					$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
            return redirect('administration/loan')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateUnsentFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$follow 				= App\RequestModel::find($id);
			$follow->fDate 			= Carbon::now();
			$follow->status 		= 2;
			$follow->idEnterprise 	= $request->enterprise_id;
			$follow->idArea 		= $request->area_id;
			$follow->idDepartment 	= $request->department_id;
			$follow->idRequest 		= $request->user_id;
			$follow->account        = $request->account_id;
			$follow->save();

			foreach (App\Loan::where('idFolio',$id)->get() as $loan) 
			{
				$idLoan 			= $loan->idLoan;
			}

			$t_loan					= App\Loan::find($idLoan);
			$t_loan->idUsers		= $request->user_id;
			$t_loan->idFolio		= $id;
			$t_loan->idKind			= 5;
			$t_loan->title 		  	= $request->title;
			$t_loan->datetitle 	  	= $request->datetitle != '' ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_loan->reference 		= $request->reference;
			$t_loan->amount 		= $request->amount;
			if ($request->method == 1) 
			{
				$t_loan->idEmployee 		= $request->idEmployee;
			}
			else
			{
				$t_loan->idEmployee 		= null;
			}
			$t_loan->idpaymentMethod= $request->method;
			$t_loan->save();
			
			$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('loan.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',70)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$account 		= $request->account;
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
		    $maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		    $enterpriseid 	= $request->enterpriseid;

			$requests		= App\RequestModel::where('kind',5)
								->where('status',3)
								->whereIn('idDepartment',Auth::user()->inChargeDep(70)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(70)->pluck('enterprise_id'))
								->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterprise',$enterpriseid);
									}
									if($account != "")
									{
										 $query->where('request_models.account',$account);
									}
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->whereHas('requestUser', function($qRequest) use($name)
											{
												$qRequest->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($qElaborate) use($name)
											{
												$qElaborate->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
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
				view('administracion.prestamo.revision',
					[
						'id'		=>$data['father'],
						'title'		=>$data['name'],
						'details'	=>$data['details'],
						'child_id'	=>$this->module_id,
						'option_id'	=>70,
						'requests'	=>$requests,
						'account'	=> $account,
						'name'		=> $name,
						'folio'		=> $folio, 	
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(70), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',70)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(70)->pluck('enterprise_id'))->get();
			$departments    = App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(70)->pluck('departament_id'))->get();
			$areas			= App\Area::where('status','ACTIVE')->get();
			$labels 		= App\Label::orderName()->get();
			$projects		= App\Project::orderName()->get();
			$request		= App\RequestModel::where('kind',5)
								->where('status',3)
								->whereIn('idDepartment',Auth::user()->inChargeDep(70)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(70)->pluck('enterprise_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.prestamo.revisioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 70,
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'request'		=> $request,
						'labels'		=> $labels,
						'projects'		=> $projects
					]
				);
				
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/loan/review')->with('alert',$alert);
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
			$data 			= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				if ($request->status == "4") 
				{
					$review 				= App\RequestModel::find($id);
					$review->status 		= $request->status;
					$review->accountR  		= $request->accountR;
					$review->idEnterpriseR  = $request->idEnterpriseR;
					$review->idDepartamentR = $request->idDepartmentR;
					$review->idAreaR  		= $request->idAreaR;
					$review->idProjectR 	= $request->project_idR;
					$review->idCheck  		= Auth::user()->id;
					$review->checkComment  	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();
					if ($request->idLabels != "") 
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'5'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 71);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',71);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',71);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',71)
			   					->where('user_has_department.departament_id',$review->idDepartamentR)
			   					->where('users.active',1)
			   					->where('users.notification',1)
			   					->get();*/
			   		$user 	= App\User::find($review->idRequest);
				   	if ($emails != "")
					{
						try
						{
							foreach ($emails as $email) 
						   	{
						   		$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						   		$to 			= $email->email;
						   		$kind 			= "Préstamo";
						   		$status 		= "Autorizar";
						   		$date 			= Carbon::now();
						   		$url 			= route('loan.authorization.edit',['id'=>$id]);
						   		$subject 		= "Solicitud por Autorizar";
					   			$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					   			Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						   	}
							   $alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review 				= App\RequestModel::find($id);
					$review->status 		= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment 	= $request->checkCommentR;
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
						try
						{
							foreach ($emailRequest as $email) 
						   	{
						   		$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						   		$to 			= $email->email;
						   		$kind 			= "Préstamo";
								$status 		= "RECHAZADA";
						   		$date 			= Carbon::now();
						   		$url 			= route('loan.follow.edit',['id'=>$id]);
						   		$subject 		= "Estado de Solicitud";
					   			$requestUser	= null;
					   			Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						   	}
							   $alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}

					}
				}
			}
			return searchRedirect(70, $alert, 'administration/loan');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',71)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$account 		= $request->account;
			$name 			= $request->name;
			$folio 			= $request->folio;
			$status 		= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
		    $enterpriseid 	= $request->enterpriseid;

			$requests		= App\RequestModel::where('kind','5')
								->whereIn('status',[4, 8])
								->whereIn('idDepartment',Auth::user()->inChargeDep(71)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(71)->pluck('enterprise_id'))
								->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $status, $enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterpriseR',$enterpriseid);
									}
									if($account != "")
									{
										 $query->where('request_models.accountR',$account);
									}
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->whereHas('requestUser', function($qRequest) use($name)
											{
												$qRequest->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($qElaborate) use($name)
											{
												$qElaborate->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
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
								->orderBy('reviewDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			return response(
				view('administracion.prestamo.autorizacion',
					[
						'id'		=>$data['father'],
						'title'		=>$data['name'],
						'details'	=>$data['details'],
						'child_id'	=>$this->module_id,
						'option_id'	=>71,
						'requests'	=>$requests,
						'account'	=> $account,
						'name'		=> $name,
						'folio'		=> $folio,
						'status'	=> $status,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid'=>$enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(71), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',71)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->get();
			$areas			= App\Area::where('status','ACTIVE')->get();
			$departments	= App\Department::where('status','ACTIVE')->get();
			
			$projects		= App\Project::all();
			$labels 		= DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			$request		= App\RequestModel::where('kind',5)
								->whereIn('status',[4, 8])
								->whereIn('idDepartment',Auth::user()->inChargeDep(71)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(71)->pluck('enterprise_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.prestamo.autorizacioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 71,
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'request'		=> $request,
						'labels'		=> $labels,
						'projects'		=> $projects
					]
				);
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/loan/authorization')->with('alert',$alert);
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
			$data 			= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', 'La solicitud ya ha sido revisada.', 'error');";
			}
			else
			{
				if (isset($request->path) && $request->path != "") 
				{
					$authorize 				 	= App\RequestModel::find($id);
					$authorize->status 		 	= 5;
					$authorize->idAuthorize	 	= Auth::user()->id;
					$authorize->authorizeDate 	= Carbon::now();
					$authorize->save();
	 				$idLoan = 0;
				 	foreach($authorize->loan as $loan)
				 	{
				 		$idLoan = $loan->idLoan;
				 	}
				 	$doc 				= $request->path;
					$t_loan 			= App\Loan::find($idLoan);
					$new_file_name = Files::rename($request->path,$t_loan->idFolio);
					$t_loan->path		= $new_file_name;
					$t_loan->save();
					$emailPay 			= App\User::join('user_has_modules','users.id','user_has_modules.user_id')
											->where('user_has_modules.module_id',90)
											->where('users.active',1)
											->where('users.notification',1)
											->get();
					$user 				= App\User::find($authorize->idRequest);
					
			   		if ($emailPay != "") 
				   	{
				   		try
						{
							foreach ($emailPay as $email) 
					   		{
					   			$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						   		$to 			= $email->email;
						   		$kind 			= "Préstamo";
						   		$status 		= "Pendiente";
						   		$date 			= Carbon::now();
						   		$url 			= route('payments.review.edit',['id'=>$id]);
						   		$subject 		= "Solicitud Pendiente de Pago";
					   			$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					   			Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					   		}
							   $alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
				   	}
				}
				else
				{
					$authorize 					 = App\RequestModel::find($id);
					$authorize->status 			 = $request->status;
					$authorize->idAuthorize		 = Auth::user()->id;
					$authorize->authorizeComment = $request->authorizeCommentA;
					$authorize->authorizeDate 	 = Carbon::now();
					$authorize->save();
					$emailRequest 				= "";
					
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

					if ($request->status == 7) 
					{
						if ($emailRequest != "") 
					   	{
					   		try
					   		{
						   		foreach ($emailRequest as $email) 
							   	{
							   		$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							   		$to 			= $email->email;
							   		$kind 			= "Préstamo";
							   		$status 		= "RECHAZADA";
							   		$date 			= Carbon::now();
							   		$url 			= route('loan.follow.edit',['id'=>$id]);
					   				$subject 		= "Estado de Solicitud";
					   				$requestUser 	= null;
					   				Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							   	}
							   	$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
							}
							catch(\Exception $e)
							{
								$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
							}
					   	}
					}
					if ($request->status == 8) 
					{
						if ($emailRequest != "") 
					   	{
						   	try
						   	{
						   		foreach ($emailRequest as $email) 
							   	{
							   		$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							   		$to 			= $email->email;
							   		$kind 			= "Préstamo";
							   		$status 		= "AUTORIZADA";
							   		$date 			= Carbon::now();
							   		$url 			= route('loan.follow.edit',['id'=>$id]);
					   				$subject 		= "Estado de Solicitud";
					   				$requestUser 	= null;
					   				Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							   	}
							   	$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
						   	}
						   	catch(\Exception $e)
							{
								$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
							}
					   	}
					}
				}				
			}
            return searchRedirect(71, $alert, 'administration/loan');
		}
		else
		{
			return redirect('/');
		}
	}

	public function document()
	{
		return view('administracion.prestamo.documento');
	}

	public function downloadDocument($id)
	{
		$requests 	= App\RequestModel::where('kind',5)
						->where('status',8)
						->whereIn('idDepartment',Auth::user()->inChargeDep(71)->pluck('departament_id'))
						->whereIn('idEnterprise',Auth::user()->inChargeEnt(71)->pluck('enterprise_id'))
						->find($id);
		if ($requests != "") 
		{
			$pdf = PDF::loadView('administracion.prestamo.documento',['requests'=>$requests]);
			return $pdf->download('documento_prestamo.pdf');
		}
		else
		{
			return redirect('/error');
		}
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
				\Storage::disk('public')->delete('/docs/loan/'.$request->realPath);
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_loanDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/loan/'.$name;
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

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',69)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',69)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',69)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;

			$requests		=  DB::table('request_models')->selectRaw(
								'
									request_models.folio,
									loans.title,
									DATE_FORMAT(loans.datetitle, "%d-%m-%Y") as datetitle,
									CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
									CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
									IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
									IF(request_models.accountR IS NULL, CONCAT_WS(" - ", account.account,account.description), CONCAT_WS(" - ", accountR.account,accountR.description)) as requestAccount,
									status_requests.description as status,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									loans.amount as total,
									payment_methods.method as paymentMethod,
									banks.description as bankName,
									employees.cardNumber as cardNumber,
									employees.clabe as clabe,
									employees.account as account
								')
								->leftJoin('loans', 'loans.idFolio', 'request_models.folio')
								->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
								->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
								->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
								->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
								->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
								->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'loans.idpaymentMethod')
								->leftJoin('accounts as account', 'account.idAccAcc', 'request_models.account')
								->leftJoin('accounts as accountR', 'accountR.idAccAcc', 'request_models.accountR')
								->leftJoin('employees', 'employees.idEmployee', 'loans.idEmployee')
								->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
								->where('request_models.kind',5)
								->where(function($q) 
								{
									$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(69)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
								})
								->where(function ($q) 
								{
									$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(69)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
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
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-préstamo.xlsx');
			$writer->getCurrentSheet()->setName('Seguimiento');

			$headers = ['Reporte de seguimiento de préstamo','','','','','','','','','','', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título', 'Fecha','Solicitante','Elaborado por','Empresa', 'Clasificación del gasto','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta'];
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
					$request->reourceTitle		= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->requestAccount	= '';
					$request->status			= '';
					$request->date				= '';
					$request->total				= null;
					$request->paymentMethod		= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total']))
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

	public function exportReview(Request $request)
	{
		if(Auth::user()->module->where('id',70)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;

			$requests		=  DB::table('request_models')->selectRaw(
						'
							request_models.folio,
							loans.title,
							DATE_FORMAT(loans.datetitle, "%d-%m-%Y") as datetitle,
							CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
							CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
							IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
							IF(request_models.accountR IS NULL, CONCAT_WS(" - ", account.account,account.description), CONCAT_WS(" - ", accountR.account,accountR.description)) as requestAccount,
							status_requests.description as status,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							loans.amount as total,
							payment_methods.method as paymentMethod,
							banks.description as bankName,
							employees.cardNumber as cardNumber,
							employees.clabe as clabe,
							employees.account as account
						')
						->leftJoin('loans', 'loans.idFolio', 'request_models.folio')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'loans.idpaymentMethod')
						->leftJoin('employees', 'employees.idEmployee', 'loans.idEmployee')
						->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
						->leftJoin('accounts as accountR', 'accountR.idAccAcc', 'request_models.accountR')
						->leftJoin('accounts as account', 'account.idAccAcc', 'request_models.account')
						->where('request_models.kind',5)
						->where('request_models.status',3)
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(70)->pluck('departament_id'))
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(70)->pluck('enterprise_id'))
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
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-préstamo.xlsx');
			$writer->getCurrentSheet()->setName('Revisión');

			$headers = ['Reporte de revisión de préstamo','','','','','','','','','','','', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Clasificación del gasto','Estado','Fecha de elaboración','Monto','Método de pago','Banco','Tarjeta','CLABE','Cuenta'];
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
					$request->requestAccount	= '';
					$request->status			= '';
					$request->date				= '';
					$request->total				= '';
					$request->paymentMethod		= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total']))
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
		if(Auth::user()->module->where('id',71)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;

			$requests	= DB::table('request_models')->selectRaw(
						'
							request_models.folio,
							loans.title,
							DATE_FORMAT(loans.datetitle, "%d-%m-%Y") as datetitle,
							CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
							CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
							IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
							IF(request_models.accountR IS NULL, CONCAT_WS(" - ", account.account,account.description), CONCAT_WS(" - ", accountR.account,accountR.description)) as requestAccount,
							status_requests.description as status,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							loans.amount as total,
							payment_methods.method as paymentMethod,
							banks.description as bankName,
							employees.cardNumber as cardNumber,
							employees.clabe as clabe,
							employees.account as account
						')
						->leftJoin('loans', 'loans.idFolio', 'request_models.folio')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'loans.idpaymentMethod')
						->leftJoin('employees', 'employees.idEmployee', 'loans.idEmployee')
						->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
						->leftJoin('accounts as account', 'account.idAccAcc', 'request_models.account')
						->leftJoin('accounts as accountR', 'accountR.idAccAcc', 'request_models.accountR')
						->where('request_models.kind',5)
						->whereIN('request_models.status',[4,8])
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(71)->pluck('departament_id'))
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(71)->pluck('enterprise_id'))
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
								$query->whereBetween('reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('reviewDate','DESC')
						->orderBy('folio','DESC')
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-préstamo.xlsx');
			$writer->getCurrentSheet()->setName('Autorización');

			$headers = ['Reporte de autorización de préstamo','','','','','','','','','','', '', '', '',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Clasificación del gasto','Estado','Fecha de elaboración','Monto','Método de pago','Banco','Tarjeta','CLABE','Cuenta'];
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
					$request->requestAccount	= '';
					$request->status			= '';
					$request->date				= '';
					$request->total				= '';
					$request->paymentMethod		= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total']))
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
