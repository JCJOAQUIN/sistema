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
use Lang;
use Carbon\Carbon;
use Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Excel;
use Ilovepdf\CompressTask;
use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionRecursoController extends Controller
{
	private $module_id = 84;
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
		if(Auth::user()->module->where('id',85)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$users			= App\User::orderName()->where('status','ACTIVE')->orderBy('name','asc')->orderBy('last_name','asc')->orderBy('scnd_last_name','asc')->where('sys_user',1)->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->orderBy('name','asc')->get();
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(85)->pluck('enterprise_id'))->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeDep(85)->pluck('departament_id'))->get();
			$projects		= App\Project::orderName()->orderBy('proyectName','asc')->get();
			return view('administracion.recurso.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 85,
					'enterprises' 	=> $enterprises,
					'areas'			=> $areas,
					'departments'	=> $departments, 
					'users' 		=> $users, 
					'projects' 		=> $projects
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',85)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',86)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',86)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data			= App\Module::find($this->module_id);
			$banks			= App\Banks::orderName()->get();
			$users			= App\User::orderName()->where('status','ACTIVE')->where('sys_user',1)->get();
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(85)->pluck('enterprise_id'))->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(85)->pluck('departament_id'))->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->get();
			$projects		= App\Project::orderName()->get();
			$labels			= DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			$request	= App\RequestModel::where('kind',8)
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
				$request->status = 2;
				return view('administracion.recurso.alta',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 85,
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'banks'			=> $banks,
						'users'			=> $users,
						'projects'		=> $projects,
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

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',85)->count()>0)
		{
			$data                       = App\Module::find($this->module_id);
			$t_request                  = new App\RequestModel();
			$t_request->kind            = 8;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 3;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			
			$t_request->code_edt		= $request->code_edt;
			$t_request->code_wbs		= $request->code_wbs;

			$t_request->idProject       = $request->project_id;
			$t_request->idElaborate     = Auth::user()->id;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;

			$t_resource                 = new App\Resource();
			$t_resource->title 			= $request->title;
			$t_resource->datetitle 		= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_resource->idFolio        = $folio;
			$t_resource->idKind         = $kind;
			$t_resource->total          = $request->total;
			$t_resource->reference 		= $request->reference;
			$t_resource->currency 		= $request->currency;
			$t_resource->idUsers 		= $request->user_id;
			if ($request->method == 1) 
			{
				$t_resource->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_resource->idEmployee		= null;
			}
			$t_resource->idpaymentMethod= $request->method;
			// aqui agregar nuevos campos, referencia, $request->idEmployee (cuenta donde se depositax);
			$t_resource->save();
			$resource                   = $t_resource->idresource;
			$countAmount                = count($request->t_amount);
			
			$totalRequest = "0";
			for ($i=0; $i < $countAmount; $i++)
			{
				$t_detailResource             = new App\ResourceDetail();
				$t_detailResource->idresource = $resource;
				$t_detailResource->concept    = $request->t_concept[$i];
				$t_detailResource->idAccAcc   = $request->t_account[$i];
				$t_detailResource->amount     = $request->t_amount[$i];
				$t_detailResource->save();

				$amount 		= $request->t_amount[$i];
				$totalRequest 	= bcadd($totalRequest,$amount,2);
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPath[$i],$folio);
						$documents					= new App\ResourceDocument();
						$documents->name			= $request->nameDocument[$i];
						$documents->path			= $new_file_name;
						$documents->resource_id		= $resource;
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}

			$t_resource			= App\Resource::find($resource);
			$t_resource->total	= bcadd($totalRequest,'0',2);
			$t_resource->save();
			
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 87);
						})
						->whereHas('inChargeDepGet',function($q) use($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',87);
						})
						->whereHas('inChargeEntGet',function($q) use($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',87);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',87)
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
						$kind 			= "Asignación de Recurso";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('resource.review.edit',['id'=>$folio]);
						$subject 		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/resource')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',86)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',86)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',86)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data           = App\Module::find($this->module_id);
			$name           = $request->name;
			$folio          = $request->folio;
			$status         = $request->status;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid 	= $request->enterpriseid;
			$requests       = App\RequestModel::where('kind',8)
								->where(function($query)
								{
									$query->whereIn('idEnterprise', Auth::user()->inChargeEnt(86)->pluck('enterprise_id'))
										->orWhereNull('idEnterprise');
								})
								->where(function($query)
								{
									$query->whereIn('idDepartment', Auth::user()->inChargeDep(86)->pluck('departament_id'))
										->orWhereNull('idDepartment');
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
									if ($enterpriseid != "") 
									{
										$query->where(function($queryE) use ($enterpriseid)
										{
											$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
										});
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

			return view('administracion.recurso.busqueda',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 86,
				'requests'	=> $requests,
				'folio'		=> $folio,
				'name'		=> $name,
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

	public function unsent(Request $request)
	{
		if(Auth::user()->module->where('id',85)->count()>0)
		{
			$data                       = App\Module::find($this->module_id);
			$t_request                  = new App\RequestModel();
			$t_request->kind            = 8;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 2;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			
			$t_request->code_edt		= $request->code_edt;
			$t_request->code_wbs		= $request->code_wbs;

			$t_request->idProject       = $request->project_id;
			$t_request->idElaborate     = Auth::user()->id;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;

			$t_resource                 = new App\Resource();
			$t_resource->title 			= $request->title;
			$t_resource->datetitle 		= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_resource->idFolio        = $folio;
			$t_resource->idKind         = $kind;
			$t_resource->reference 		= $request->reference;
			$t_resource->currency 		= $request->currency;
			$t_resource->idUsers 		= $request->user_id;
			if ($request->method == 1) 
			{
				$t_resource->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_resource->idEmployee		= null;
			}
			$t_resource->idpaymentMethod= $request->method;
			$t_resource->total          = $request->total;
			$t_resource->save();
			$resource                   = $t_resource->idresource;
			
			$totalRequest = "0";
			if($request->t_amount != "")
			{
				$countAmount                = count($request->t_amount);
				for ($i=0; $i < $countAmount; $i++)
				{
					$t_detailResource             = new App\ResourceDetail();
					$t_detailResource->idresource = $resource;
					$t_detailResource->concept    = $request->t_concept[$i];
					$t_detailResource->idAccAcc   = $request->t_account[$i];
					$t_detailResource->amount     = $request->t_amount[$i];
					$t_detailResource->save();

					$amount 		= $request->t_amount[$i];
					$totalRequest 	= bcadd($totalRequest,$amount,2);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPath[$i],$folio);
						$documents					= new App\ResourceDocument();
						$documents->name			= $request->nameDocument[$i];
						$documents->path			= $new_file_name;
						$documents->resource_id		= $resource;
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}

			$t_resource			= App\Resource::find($resource);
			$t_resource->total	= bcadd($totalRequest,'0',2);
			$t_resource->save();

			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('resource.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',86)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',86)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',86)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(86)->pluck('enterprise_id'))->get();
			$departments	= App\Department::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeDep(86)->pluck('departament_id'))->get();
			$areas			= App\Area::where('status','ACTIVE')->orderBy('name','asc')->get();
			$projects		= App\Project::orderBy('proyectName','asc');

			$request		= App\RequestModel::where('kind',8)
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
									}
								})
								->find($id);

			$labels         = DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			if ($request != "") 
			{
				return view('administracion.recurso.seguimiento',
					[
						'id' 			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 86,
						'projects' 		=> $projects, 
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
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

	public function updateFollow(Request $request, $id)
	{
		if(Auth::user()->module->where('id',86)->count()>0)
		{
			$data                       = App\Module::find($this->module_id);
			$t_request                  = App\RequestModel::find($id);
			$t_request->kind            = 8;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 3;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			
			$t_request->code_edt		= $request->code_edt;
			$t_request->code_wbs		= $request->code_wbs;

			$t_request->idProject       = $request->project_id;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;

			$res = App\Resource::where('idFolio',$id)->get();

			foreach ($res as $r) {
				$idresource = $r->idresource;
			}

			$del1 = App\ResourceDetail::where('idresource',$idresource)->delete();

			$t_resource                 = App\Resource::find($idresource);
			$t_resource->title 			= $request->title;
			$t_resource->datetitle 		= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_resource->idFolio        = $folio;
			$t_resource->idKind         = $kind;
			$t_resource->reference 		= $request->reference;
			$t_resource->currency 		= $request->currency;
			$t_resource->total          = $request->total;
			$t_resource->idUsers 		= $request->user_id;
			if ($request->method == 1) 
			{
				$t_resource->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_resource->idEmployee		= null;
			}
			
			$t_resource->idpaymentMethod= $request->method;
			$t_resource->save();            
			
			$totalRequest = "0";
			if($request->t_amount != "")
			{
				$countAmount                = count($request->t_amount);
				for ($i=0; $i < $countAmount; $i++)
				{
					$t_detailResource             = new App\ResourceDetail();
					$t_detailResource->idresource = $idresource;
					$t_detailResource->concept    = $request->t_concept[$i];
					$t_detailResource->idAccAcc   = $request->t_account[$i];
					$t_detailResource->amount     = $request->t_amount[$i];
					$t_detailResource->save();

					$amount 		= $request->t_amount[$i];
					$totalRequest 	= bcadd($totalRequest,$amount,2);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPath[$i],$folio);
						$documents					= new App\ResourceDocument();
						$documents->name			= $request->nameDocument[$i];
						$documents->path			= $new_file_name;
						$documents->resource_id		= $idresource;
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}

			$t_resource			= App\Resource::find($idresource);
			$t_resource->total	= bcadd($totalRequest,'0',2);
			$t_resource->save();
			
			$alert 	= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 87);
						})
						->whereHas('inChargeDepGet',function($q) use($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
							->where('module_id',87);
						})
						->whereHas('inChargeEntGet',function($q) use($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
							->where('module_id',87);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',87)
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
						$kind 			= "Asignación de Recurso";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('resource.review.edit',['id'=>$id]);
						$subject 		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/resource')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateUnsentFollow(Request $request, $id)
	{
		if(Auth::user()->module->where('id',86)->count()>0)
		{
			$data                       = App\Module::find($this->module_id);
			$t_request                  = App\RequestModel::find($id);
			$t_request->kind            = "8";
			$t_request->fDate           = Carbon::now();
			$t_request->status          = "2";
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			
			$t_request->code_edt		= $request->code_edt;
			$t_request->code_wbs		= $request->code_wbs;
			
			$t_request->idProject       = $request->project_id;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;

			$res = App\Resource::where('idFolio',$id)->get();

			foreach ($res as $r) 
			{
				$idresource = $r->idresource;
			}

			$del1 = App\ResourceDetail::where('idresource',$idresource)->delete();

			$t_resource                 = App\Resource::find($idresource);
			$t_resource->title 			= $request->title;
			$t_resource->datetitle 		= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_resource->idFolio        = $folio;
			$t_resource->idKind         = $kind;
			$t_resource->reference 		= $request->reference;
			$t_resource->currency 		= $request->currency;
			$t_resource->idpaymentMethod= $request->method;
			$t_resource->idUsers 		= $request->user_id;

			if ($request->method == 1) 
			{
				$t_resource->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_resource->idEmployee		= null;
			}
			$t_resource->total          = $request->total; 
			$t_resource->save();            
			
			$totalRequest = "0";
			if($request->t_amount != "")
			{
				$countAmount                = count($request->t_amount);
				for ($i=0; $i < $countAmount; $i++)
				{
					$t_detailResource             = new App\ResourceDetail();
					$t_detailResource->idresource = $idresource;
					$t_detailResource->concept    = $request->t_concept[$i];
					$t_detailResource->idAccAcc   = $request->t_account[$i];
					$t_detailResource->amount     = $request->t_amount[$i];
					$t_detailResource->save();

					$amount 		= $request->t_amount[$i];
					$totalRequest 	= bcadd($totalRequest,$amount,2);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPath[$i],$folio);
						$documents					= new App\ResourceDocument();
						$documents->name			= $request->nameDocument[$i];
						$documents->path			= $new_file_name;
						$documents->resource_id		= $idresource;
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}

			$t_resource			= App\Resource::find($idresource);
			$t_resource->total	= bcadd($totalRequest,'0',2);
			$t_resource->save();
			
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('resource.follow.edit',['id'=>$id])->with('alert',$alert);

		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',87)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$mindate   	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;

			$requests	= App\RequestModel::where('kind',8)
							->where('status',3)
							->whereIn('idDepartment',Auth::user()->inChargeDep(87)->pluck('departament_id'))
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(87)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
							{   
								if ($enterpriseid != "") 
								{
									$query->where('request_models.idEnterprise',$enterpriseid);
								}
								if($name != "")
								{
									$query->where(function($q) use($name)
									{
										$q->whereHas('requestUser', function($q) use($name)
										{
											$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
										})
										->orWhereHas('elaborateUser', function($q) use($name)
										{
											$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
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
				view('administracion.recurso.revision',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 87,
						'requests'	=> $requests,
						'folio'		=> $folio,
						'name'		=> $name,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(87), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',87)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeEnt(87)->pluck('enterprise_id'))->get();
			$departments	= App\Department::where('status','ACTIVE')->orderBy('name','asc')->whereIn('id',Auth::user()->inChargeDep(87)->pluck('departament_id'))->get();
			$areas			= App\Area::where('status','ACTIVE')->orderBy('name','asc')->get();
			$labels			= App\Label::orderBy('description','asc')->get();
			$projects		= App\Project::orderBy('proyectName','asc')->get();
			$request		= App\RequestModel::where('kind',8)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(87)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(87)->pluck('departament_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.recurso.revisioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 87,
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
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/resource/review')->with('alert',$alert);
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
			$data           = App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				if ($request->status == "4") 
				{
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
						$review->labels()->attach($request->idLabels,array('request_kind'=>'8'));
					}

					for ($i=0; $i < count($request->idRDeR); $i++)
					{
						$t_detailResource             = App\ResourceDetail::find($request->idRDeR[$i]);
						$t_detailResource->idAccAccR  = $request->t_accountR[$i];
						$t_detailResource->save();
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 88);
						})
						->whereHas('inChargeDepGet',function($q) use($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',88);
						})
						->whereHas('inChargeEntGet',function($q) use($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',88);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',88)
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
								$kind 			= "Asignación de Recurso";
								$status 		= "Autorizar";
								$date 			= Carbon::now();
								$url 			= route('resource.authorization.edit',['id'=>$id]);
								$subject 		= "Solicitud por Autorizar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review                 = App\RequestModel::find($id);
					$review->status         = $request->status;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentR;
					$review->reviewDate     = Carbon::now();
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
								$kind 			= "Asignación de Recurso";
								$status 		= "RECHAZADA";
								$date 			= Carbon::now();
								$url 			= route('resource.follow.edit',['id'=>$id]);
								$subject 		= "Estado de Solicitud";
								$requestUser	= null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));	
							}
							$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(87, $alert, 'administration/resource');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',88)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid 	= $request->enterpriseid;

			$requests	= App\RequestModel::where('kind',8)
								->where('status',4)
								->whereIn('idDepartment',Auth::user()->inChargeDep(88)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(88)->pluck('enterprise_id'))
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
				view('administracion.recurso.autorizacion',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 88,
						'requests'		=> $requests,
						'folio'			=> $folio,
						'name'			=> $name,
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'enterpriseid'	=> $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(88), 2880
			);
		}
		else
		{
			return redirect('/'); 
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',88)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(87)->pluck('enterprise_id'))->get();
			$departments	= App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(87)->pluck('departament_id'))->get();
			$areas			= App\Area::where('status','ACTIVE')->get();
			$labels			= DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			$projects		= App\Project::all();
			$request		= App\RequestModel::where('kind',8)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(88)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(88)->pluck('departament_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.recurso.autorizacioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 88,
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
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/resource/authorization')->with('alert',$alert);
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
			$data           = App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$authorize                      = App\RequestModel::find($id);
				$authorize->status              = $request->status;
				$authorize->idAuthorize         = Auth::user()->id;
				$authorize->authorizeComment    = $request->authorizeCommentA;
				$authorize->authorizeDate       = Carbon::now();
				$authorize->save();
									
				$alert 			= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
				
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
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to 			= $email->email;
							$kind 			= "Asignación de Recurso";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date 			= Carbon::now();
							$url 			= route('resource.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser 	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
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
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Asignación de Recurso";
								$status 		= "Pendiente";
								$date 			= Carbon::now();
								$url 			= route('payments.review.edit',['id'=>$id]);
								$subject 		= "Solicitud Pendiente de Pago";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(88, $alert, 'administration/resource');
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',86)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',86)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',86)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;			
			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								resources.title as title,
								DATE_FORMAT(resources.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								resources.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								resources.currency as currency,
								resource_details.concept as conceptName,
								IF(resource_details.idAccAccR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								resource_details.amount as conceptAmount,
								IFNULL(paymentsTemp.paymentsAmountReal,0) as amountPaid
							')
							->leftJoin('resources', 'resources.idFolio', 'request_models.folio')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'resources.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'resources.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('resource_details', 'resource_details.idresource', 'resources.idresource')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin(DB::raw('(SELECT idFolio, SUM(amount_real) as paymentsAmountReal from payments group by idFolio) as paymentsTemp'),'paymentsTemp.idFolio','request_models.folio')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'resource_details.idAccAccR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'resource_details.idAccAcc')
							->where('request_models.kind',8)
							->where(function($query)
							{
								$query->whereIn('request_models.idEnterprise', Auth::user()->inChargeEnt(86)->pluck('enterprise_id'))
									->orWhereNull('request_models.idEnterprise');
							})
							->where(function($query)
							{
								$query->whereIn('request_models.idDepartment', Auth::user()->inChargeDep(86)->pluck('departament_id'))
									->orWhereNull('request_models.idDepartment');
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-recurso.xlsx');
			$writer->getCurrentSheet()->setName('Seguimiento');

			$headers = ['Reporte de seguimiento de recursos','','','','','','','','','','', '', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Tipo de moneda', 'Concepto', 'Clasificación del gasto', 'Importe', 'Total Pagado'];
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
					$request->paymentMethod		= '';
					$request->currency			= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
					$request->amountPaid 		= null;
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptAmount', 'amountPaid']))
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
		if(Auth::user()->module->where('id',87)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests	= DB::table('request_models')->selectRaw(
						'
							request_models.folio as folio,
							resources.title as title,
							DATE_FORMAT(resources.datetitle, "%d-%m-%Y") as datetitle,
							CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
							CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
							IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
							status_requests.description as status,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							resources.total as total,
							payment_methods.method as paymentMethod,
							banks.description as bankName,
							employees.cardNumber as cardNumber,
							employees.clabe as clabe,
							employees.account as account,
							resources.currency as currency,
							resource_details.concept as conceptName,
							IF(resource_details.idAccAccR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
							resource_details.amount as conceptAmount
						')
						->leftJoin('resources', 'resources.idFolio', 'request_models.folio')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'resources.idpaymentMethod')
						->leftJoin('employees', 'employees.idEmployee', 'resources.idEmployee')
						->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
						->leftJoin('resource_details', 'resource_details.idresource', 'resources.idresource')
						->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'resource_details.idAccAccR')
						->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'resource_details.idAccAcc')
						->where('request_models.kind',8)
						->where('request_models.status',3)
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(87)->pluck('departament_id'))
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(87)->pluck('enterprise_id'))
						->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
						{   
							if ($enterpriseid != "") 
							{
								$query->where('request_models.idEnterprise',$enterpriseid);
							}
							if($name != "")
							{
								$query->where(function($queryU) use ($name)
								{
									$queryU->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								})
								->orWhere(function($queryU) use ($name)
								{
									$queryU->orWhere(DB::raw("CONCAT_WS(' ',elaborateUser.name,elaborateUser.last_name,elaborateUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-recurso.xlsx');
			$writer->getCurrentSheet()->setName('Revisión');

			$headers = ['Reporte de revisión de recursos','','','','','','','','','','', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Tipo de moneda', 'Concepto', 'Clasificación del gasto', 'Importe'];
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
					$request->total				= '';
					$request->paymentMethod		= '';
					$request->currency			= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptAmount']))
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
		if(Auth::user()->module->where('id',88)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								resources.title as title,
								DATE_FORMAT(resources.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								resources.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								resources.currency as currency,
								resource_details.concept as conceptName,
								IF(resource_details.idAccAccR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								resource_details.amount as conceptAmount
							')
							->leftJoin('resources', 'resources.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'resources.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'resources.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('resource_details', 'resource_details.idresource', 'resources.idresource')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'resource_details.idAccAccR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'resource_details.idAccAcc')
							->where('request_models.kind',8)
							->where('request_models.status',4)
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(88)->pluck('departament_id'))
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(88)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
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
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
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
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-recurso.xlsx');
			$writer->getCurrentSheet()->setName('Autorización');

			$headers = ['Reporte de autorización de recurso','','','','','','','','','','', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Tipo de moneda', 'Concepto', 'Clasificación del gasto', 'Importe'];
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
					$request->total				= '';
					$request->paymentMethod		= '';
					$request->currency			= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptAmount']))
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

	public function getAccountEmployee(Request $request)
	{
		if ($request->ajax()) 
		{
			$banks 	= App\Employee::join('banks','employees.idBanks','banks.idBanks')
					->where('visible',1)
					->where('idUsers',$request->idUsers)
					->get();
			return view('administracion.recurso.parcial.cuentas',['banks'=>$banks]);
		}
	}

	public function checkBalance(Request $request)
	{
		if ($request->ajax()) 
		{	
			$checkResources = App\RequestModel::where('kind',8)
							->whereIn('status',[5,10,11,12,18])
							->where('idRequest',$request->idUsers)
							->get();

			if (count($checkResources)>0) 
			{
				$arrayResource = [];
				foreach ($checkResources as $key => $t_request) 
				{
					$flag = true;
					foreach ($t_request->resource->first()->expensesRequest as $expenses)
					{
						if($expenses->requestModel->status!=2 && $expenses->requestModel->status!=6 && $expenses->requestModel->status!=7 && $expenses->requestModel->status!=13)
						{
							$flag = false;
						}
					}

					if ($flag) 
					{
						$arrayResource[$key]['folio']	= $t_request->folio;
						$arrayResource[$key]['title']	= $t_request->resource()->exists() ? $t_request->resource->first()->title : 'Sin título';
						$arrayResource[$key]['amount']	= $t_request->resource()->exists() ? $t_request->resource->first()->total : '0.00';
					}
				}

				if (count($arrayResource)>0) 
				{
					return view('administracion.recurso.parcial.recurso_sin_comprobar',['arrayResource'=>$arrayResource]);
				}
			}
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
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/resource/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_resource_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/resource/'.$name;
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

	public function validationDocs(Request $request)
	{
		if ($request->ajax()) 
		{
			$position = [];
			//return $request->fiscal_folio;
			for ($i=0; $i < count($request->datepath) ; $i++)
			{
				if((isset($request->fiscal_folio[$i]) &&  $request->fiscal_folio[$i] != null) || (isset($request->ticket_number[$i]) && $request->ticket_number[$i] != null))
				{
					$options				= [];
					$options['fiscal_val']	= $request->fiscal_folio[$i];
					$options['ticket_val']	= $request->ticket_number[$i];
					$options['date']		= $request->datepath[$i];
					$options['time']		= $request->timepath[$i];
					$options['amount']		= $request->amount[$i];
					$folio					= null;

					$check_docs            = App\Functions\DocsValidate::validate($options,$folio);

					if($check_docs>0)
					{
						if (isset($request->ticket_number[$i]) && $request->ticket_number[$i] != "") 
						{
							$position[] = $request->ticket_number[$i];
						}
						if(isset($request->fiscal_folio[$i]) && $request->fiscal_folio[$i] != "")
						{
							$position[] = $request->fiscal_folio[$i];
						}
					}
				}
			}
			return Response($position);
		}
	}
}
