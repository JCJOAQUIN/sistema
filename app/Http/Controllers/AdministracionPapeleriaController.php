<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use Ilovepdf\CompressTask;
use PDF;
use App;
use Alert;
use Lang;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use App\RequestModel;
use Excel;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionPapeleriaController extends Controller
{
	private $module_id =51;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{

			/* ACTUALIZAR TODOS LOS TOTALES DE LAS SOLICITUDES DE PAPELERIA

			$requests = App\Stationery::all();

			foreach ($requests as $request) 
			{
				foreach ($request->detailStat as $det) 
				{
					if ($det->idwarehouse != null) 
					{
						$warehouse	= App\Warehouse::find($det->idwarehouse);
						$unitPrice	= $warehouse->amountUnit;

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

						$subtotal	= $det->quantity *$unitPrice;
						$iva		= $subtotal*$valueIva;
						$total		= $subtotal+$iva;

						$det->subtotal	= $subtotal;
						$det->iva		= $iva;
						$det->total		= $total;
						$det->save();
					}
				}

				$request->subtotal	= $request->detailStat()->sum('subtotal');
				$request->iva		= $request->detailStat()->sum('iva');
				$request->total		= $request->detailStat()->sum('total');
				$request->save();
			}

			*/

			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' 	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',52)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$banks          = App\Banks::orderName()->get();
			$users          = App\User::orderName()->where('status','ACTIVE')->where('sys_user',1)->get();
			$areas          = App\Area::orderName()->where('status','ACTIVE')->get();
			$enterprises    = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(52)->pluck('enterprise_id'))->get();
			$departments    = App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(52)->pluck('departament_id'))->get();
			$iva            = App\Parameter::where('parameter_name','IVA')->get();
			$projects       = App\Project::orderName()->get();
			return view('administracion.papeleria.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 52, 
					'enterprises' 	=> $enterprises,
					'areas'			=> $areas,
					'departments'	=> $departments,
					'banks'			=> $banks,
					'users'			=> $users,
					'iva'			=> $iva,
					'projects'		=> $projects
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',52)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',53)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',53)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data           = App\Module::find($this->module_id);
			$users          = App\User::where('status','ACTIVE')->where('sys_user',1)->get();
			$areas          = App\Area::where('status','ACTIVE')->get();
			$enterprises    = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(52)->pluck('enterprise_id'))->get();
			$departments    = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(52)->pluck('departament_id'))->get();
			$projects       = App\Project::all();
			$labels         = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			$request = App\RequestModel::where('kind',7)
				->whereIn('status',[5, 6, 7, 9,10,11,19])
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			$new_request = new App\RequestModel;
			if ($request != "") 
			{
				return view('administracion.papeleria.alta',
					[
						'id' 			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 52,
						'enterprises' 	=> $enterprises,
						'areas'			=> $areas,
						'departments'	=> $departments,
						'users' 		=> $users,
						'projects'		=> $projects,
						'request'		=> $request,
						'labels'		=> $labels,
						'new_request'	=> $new_request
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
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                          = App\Module::find($this->module_id);
			$t_request                     = new App\RequestModel();
			$t_request->kind               = 7;
			$t_request->status             = 3;
			$t_request->fDate              = Carbon::now();
			$t_request->idEnterprise       = $request->enterprise_id;
			$t_request->idArea             = $request->area_id;
			$t_request->idDepartment       = $request->department_id;
			$t_request->idRequest          = $request->user_id;
			$t_request->account            = $request->account_id;
			$t_request->idElaborate        = Auth::user()->id;
			$t_request->idProject          = $request->project_id;
			$t_request->save();
			$folio                         = $t_request->folio;
			$kind                          = $t_request->kind;
			$t_stat                        = new App\Stationery();
			$t_stat->title                 = $request->title;
			$t_stat->subcontractorProvider = $request->SubcontractorProvider;
			$t_stat->datetitle             = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_stat->idFolio               = $folio;
			$t_stat->idKind                = $kind;
			$t_stat->save();
			$idstat                        = $t_stat->idStationery;
			if (isset($request->tcategory) && count($request->tcategory)>0) 
			{
				$countDet = count($request->tquanty);
				for ($i=0; $i < $countDet; $i++) 
				{ 
					$t_detStat					= new App\DetailStationery();
					$t_detStat->category		= $request->tcategory[$i];
					$t_detStat->quantity		= $request->tquanty[$i];
					$t_detStat->product			= $request->tmaterial[$i];
					$t_detStat->short_code		= $request->tshort_code[$i];
					$t_detStat->long_code		= $request->tlong_code[$i];
					$t_detStat->commentaries	= $request->tcommentaries[$i];
					$t_detStat->idStat			= $idstat;
					$t_detStat->save();
				}
			}
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 54);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',54);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',54);
						})
						->where('active',1)
						->where('notification',1)
						->get();

			$user 	=  App\User::find($request->user_id);
			if ($emails != "") 
			{
				try
				{
					foreach ($emails as $email) 
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Papelería";
						$status			= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('stationery.review.edit',['id'=>$folio]);
						$subject		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}

			}
			return redirect('administration/stationery')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function unsent(Request $request)
	{
		
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request                     = new App\RequestModel();
			$t_request->kind               = 7;
			$t_request->status             = 2;
			$t_request->fDate              = Carbon::now();
			$t_request->idEnterprise       = $request->enterprise_id;
			$t_request->idArea             = $request->area_id;
			$t_request->idDepartment       = $request->department_id;
			$t_request->idRequest          = $request->user_id;
			$t_request->account            = $request->account_id;
			$t_request->idElaborate        = Auth::user()->id;
			$t_request->idProject          = $request->project_id;
			$t_request->save();
			$folio                         = $t_request->folio;
			$kind                          = $t_request->kind;
			$t_stat                        = new App\Stationery();
			$t_stat->title                 = $request->title;
			$t_stat->subcontractorProvider = $request->SubcontractorProvider;
			$t_stat->datetitle             = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_stat->idFolio               = $folio;
			$t_stat->idKind                = $kind;
			$t_stat->save();
			$idstat                        = $t_stat->idStationery;
			if (isset($request->tcategory) && count($request->tcategory)>0) 
			{
				$countDet = count($request->tquanty);
				for ($i=0; $i < $countDet; $i++) 
				{ 
					$t_detStat               = new App\DetailStationery();
					$t_detStat->category     = $request->tcategory[$i];
					$t_detStat->quantity     = $request->tquanty[$i];
					$t_detStat->product      = $request->tmaterial[$i];
					$t_detStat->short_code   = $request->tshort_code[$i];
					$t_detStat->long_code    = $request->tlong_code[$i];
					$t_detStat->commentaries = $request->tcommentaries[$i];
					$t_detStat->idStat       = $idstat;
					$t_detStat->save();
				}
			}
			
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('stationery.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',53)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',53)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',53)->first()->global_permission;
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
			$project_id   = $request->project_id;
			$category_id  = $request->category_id;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$requests       = App\RequestModel::where('kind','7')
				->where(function($q) 
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(53)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
				})
				->where(function ($q) 
				{
					$q->whereIn('idDepartment',Auth::user()->inChargeDep(53)->pluck('departament_id'))->orWhereNull('idDepartment');
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio, $status,$enterpriseid,$project_id,$category_id)
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
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
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
					if($project_id != "")
					{
						$query->where('request_models.idProject',$project_id);
					}
					if($category_id != "")
					{
						$query->where(function($q) use($category_id)
						{
							$q->where('request_models.idWarehouseType',$category_id)
								->orWhereHas('stationery',function($q) use($category_id)
								{
									$q->whereHas('detailStat',function($q) use($category_id)
									{
										$q->where('category',$category_id);
									});
								});
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate." 00:00:00", $maxdate." 23:59:59"]);
					}
				})
				
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return view('administracion.papeleria.busqueda',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 53,
				'requests'		=> $requests,
				'folio' 		=> $folio, 
				'name' 			=> $name, 
				'account' 		=> $account, 
				'mindate' 		=> $request->mindate, 
				'maxdate' 		=> $request->maxdate,
				'status'		=> $status,
				'enterpriseid' 	=> $enterpriseid,
				'project_id' 	=>$project_id,
				'category_id' 	=>$category_id,

			]);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function downloadDocument($id)
	{
		
		$request		= App\RequestModel::
			whereIn('status',[5])
			->whereHas('stationery',function($q) use($id)
			{
				$q->where('idFolio', $id);
			})
			->find($id);

		if ($request != "")
		{
			$pdf = PDF::loadView('administracion.papeleria.documento',['request'=>$request]);
			return $pdf->download('solicitud_almacen'.$request->folio.'.pdf');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function follow($id) 
	{ 
		if(Auth::user()->module->where('id',53)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',53)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',53)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data        = App\Module::find($this->module_id); 
			$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(53)->pluck('enterprise_id'))->get();
			$departments = App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(53)->pluck('departament_id'))->get();
			$areas       = App\Area::orderName()->where('status','ACTIVE')->get(); 
			$projects    = App\Project::orderName()->get();
			$labels      = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			if (Auth::user()->id == 43) 
			{
				$request = App\RequestModel::where('kind',7)->find($id);
			}
			else
			{
				$request = App\RequestModel::where('kind',7)
					->where(function ($q) use ($global_permission)
					{
						if ($global_permission == 0) 
						{
							$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
						}
					})
					->find($id);
			}
			if ($request != "") 
			{
				$stationery = App\Stationery::where('idFolio',$request->folio)->get();
				foreach ($stationery as $stat) 
				{
					$details = App\DetailStationery::where('idStat',$stat->idStationery)->get();
				}
				return view('administracion.papeleria.alta',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 53,
						'projects'    => $projects,
						'enterprises' => $enterprises,
						'areas'       => $areas,
						'departments' => $departments,
						'request'     => $request,
						'labels'      => $labels,
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
			$data                          = App\Module::find($this->module_id);
			$follow                        = App\RequestModel::find($id);
			$follow->fDate                 = Carbon::now();
			$follow->status                = 3;
			$follow->idEnterprise          = $request->enterprise_id;
			$follow->idArea                = $request->area_id;
			$follow->idDepartment          = $request->department_id;
			$follow->idRequest             = $request->user_id;
			$follow->account               = $request->account_id;
			$follow->idProject             = $request->project_id;
			$follow->save();
			$idStationery                  = $follow->stationery->first()->idStationery;
			$t_stat                        = App\Stationery::find($idStationery);
			$t_stat->title                 = $request->title;
			$t_stat->subcontractorProvider = $request->SubcontractorProvider;
			$t_stat->datetitle             = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_stat->save();
			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\DetailStationery::whereIn('idStatDetail',$request->delete)->delete();
			}
			if (isset($request->tcategory) && count($request->tcategory)>0) 
			{
				$countDet = count($request->tquanty);
				for ($i=0; $i < $countDet; $i++) 
				{ 
					if ($request->tidStatDetail[$i] == "x")
					{
						$t_detStat               = new App\DetailStationery();
						$t_detStat->category     = $request->tcategory[$i];
						$t_detStat->quantity     = $request->tquanty[$i];
						$t_detStat->product      = $request->tmaterial[$i];
						$t_detStat->short_code   = $request->tshort_code[$i];
						$t_detStat->long_code    = $request->tlong_code[$i];
						$t_detStat->commentaries = $request->tcommentaries[$i];
						$t_detStat->idStat       = $idStationery;
						$t_detStat->save();
					}
				}
			}
			$emails = App\User::whereHas('module',function($q)
				{
					$q->where('id', 54);
				})
				->whereHas('inChargeDepGet',function($q) use ($follow)
				{
					$q->where('departament_id', $follow->idDepartment)
						->where('module_id',54);
				})
				->whereHas('inChargeEntGet',function($q) use ($follow)
				{
					$q->where('enterprise_id', $follow->idEnterprise)
						->where('module_id',54);
				})
				->where('active',1)
				->where('notification',1)
				->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',54)
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
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Papelería";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('stationery.review.edit',['id'=>$id]);
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
			return redirect('administration/stationery')->with('alert',$alert);
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
			$data                          = App\Module::find($this->module_id);
			$follow                        = App\RequestModel::find($id);
			$follow->fDate                 = Carbon::now();
			$follow->status                = 2;
			$follow->idEnterprise          = $request->enterprise_id;
			$follow->idArea                = $request->area_id;
			$follow->idDepartment          = $request->department_id;
			$follow->idRequest             = $request->user_id;
			$follow->account               = $request->account_id;
			$follow->idProject             = $request->project_id;
			$follow->save();
			$idStationery                  = $follow->stationery->first()->idStationery;
			$t_stat                        = App\Stationery::find($idStationery);
			$t_stat->title                 = $request->title;
			$t_stat->subcontractorProvider = $request->SubcontractorProvider;
			$t_stat->datetitle             = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_stat->save();
			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\DetailStationery::whereIn('idStatDetail',$request->delete)->delete();
			}
			if (isset($request->tcategory) && count($request->tcategory)>0) 
			{
				$countDet = count($request->tquanty);
				for ($i=0; $i < $countDet; $i++) 
				{ 
					if ($request->tidStatDetail[$i] == "x")
					{
						$t_detStat               = new App\DetailStationery();
						$t_detStat->category     = $request->tcategory[$i];
						$t_detStat->quantity     = $request->tquanty[$i];
						$t_detStat->product      = $request->tmaterial[$i];
						$t_detStat->short_code   = $request->tshort_code[$i];
						$t_detStat->long_code    = $request->tlong_code[$i];
						$t_detStat->commentaries = $request->tcommentaries[$i];
						$t_detStat->idStat       = $idStationery;
						$t_detStat->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('stationery.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',54)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$project_id   = $request->project_id;
			$category_id  = $request->category_id;
			
			$requests	= App\RequestModel::where('kind',7)
				->where(function($query)
				{
					$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))
						->orWhereNull('idEnterprise');
				})
				->where('status',3)
				->whereIn('idDepartment',Auth::user()->inChargeDep(54)->pluck('departament_id'))
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio,$enterpriseid,$project_id,$category_id)
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
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $name).'%"');
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($project_id != "")
					{
						$query->where('request_models.idProject',$project_id);
					}
					if($category_id != "")
					{
						$query->where(function($q) use($category_id)
						{
							$q->where('request_models.idWarehouseType',$category_id)
								->orWhereHas('stationery',function($q) use($category_id)
								{
									$q->whereHas('detailStat',function($q) use($category_id)
									{
										$q->where('category',$category_id);
									});
								});
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate." 00:00:00", $maxdate." 23:59:59"]);
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.papeleria.revision',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 54,
						'requests'     => $requests,
						'folio'        => $folio, 
						'name'         => $name, 
						'account'      => $account, 
						'mindate'      => $request->mindate, 
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid,
						'project_id'   => $project_id,
						'category_id'  => $category_id,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(54), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',54)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))->get();
			$departments    = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(54)->pluck('departament_id'))->get();
			$areas          = App\Area::where('status','ACTIVE')->get();
			$labels         = App\Label::all();
			$projects       = App\Project::all();
			$request        = App\RequestModel::where('kind',7)
				->where('status',3)
				->whereIn('idDepartment',Auth::user()->inChargeDep(54)->pluck('departament_id'))
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))
				->find($id);
			if ($request != "") 
			{
				return view('administracion.papeleria.revisioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 54,
						'enterprises'	=> $enterprises,
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
				return redirect('administration/stationery/review')->with('alert',$alert);
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
					for ($i=0; $i < count($request->t_idStatDetail); $i++) 
					{ 
						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelExpense = new App\LabelDetailStationery();
								$labelExpense->idlabels = $request->$idLabelsAssign[$d];
								$labelExpense->idStatDetail = $request->t_idStatDetail[$i];
								$labelExpense->save();
							}
						}
					}
					
					$review                 = App\RequestModel::find($id);
					$review->status         = $request->status;
					$review->accountR       = $request->accountR;
					$review->idEnterpriseR  = $request->idEnterpriseR;
					$review->idDepartamentR = $request->idDepartmentR;
					$review->idAreaR        = $request->idAreaR;
					$review->idProjectR     = $request->project_id;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->idProject   		= $request->project_id;

					$review->save();
					if ($request->idLabels != "") 
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'7'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 55);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',55);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',55);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
							->join('user_has_modules','users.id','user_has_modules.user_id')
							->where('user_has_modules.module_id',55)
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
								$kind 			= "Papelería";
								$status 		= "Autorizar";
								$date 			= Carbon::now();
								$url 			= route('stationery.authorization.edit',['id'=>$id]);
								$subject 		= "Solicitud por Autorizar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
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
					$review                 = App\RequestModel::find($id);
					$review->status         = $request->status;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentR;
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
								$kind 			= "Papelería";
								$status 		= "RECHAZADA";
								$date 			= Carbon::now();
								$url 			= route('stationery.follow.edit',['id'=>$id]);
								$subject 		= "Estado de Solicitud";
								$requestUser	= null;
								//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
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
			return searchRedirect(54, $alert, 'administration/stationery');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',55)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$project_id   = $request->project_id;
			$category_id  = $request->category_id;
			
			$requests = App\RequestModel::where('kind',7)
				->where(function($query)
				{
					$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(55)->pluck('enterprise_id'))
						->orWhereNull('idEnterprise');
				})
				->where('status',4)
				->whereIn('idDepartment',Auth::user()->inChargeDep(55)->pluck('departament_id'))
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(55)->pluck('enterprise_id'))
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio,$enterpriseid,$project_id,$category_id)
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
					if($project_id != "")
					{
						$query->where('request_models.idProject',$project_id);
					}
					if($category_id != "")
					{
						$query->where(function($q) use($category_id)
						{
							$q->where('request_models.idWarehouseType',$category_id)
								->orWhereHas('stationery',function($q) use($category_id)
								{
									$q->whereHas('detailStat',function($q) use($category_id)
									{
										$q->where('category',$category_id);
									});
								});
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('reviewDate',[$mindate." 00:00:00", $maxdate." 23:59:59"]);
					}
				})
				->orderBy('reviewDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			 return response(
				view('administracion.papeleria.autorizacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 55,
						'requests'	=> $requests,
						'folio' 	=> $folio, 
						'name' 		=> $name, 
						'account' 	=> $account, 
						'mindate'   => $request->mindate, 
						'maxdate'   => $request->maxdate,
						'enterpriseid' => $enterpriseid,
						'project_id' => $project_id,
						'category_id' => $category_id,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(55), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',55)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(55)->pluck('enterprise_id'))->get();
			$departments    = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(55)->pluck('departament_id'))->get();
			$areas          = App\Area::where('status','ACTIVE')->get();
			
			$projects       = App\Project::all();
			$labels         = DB::table('request_has_labels')
							->join('labels','idLabels','labels_idlabels')
							->select('labels.description as descr')
							->where('request_has_labels.request_folio',$id)
							->get();
			$request        = App\RequestModel::where('kind',7)
							->where('status',4)
							->whereIn('idDepartment',Auth::user()->inChargeDep(55)->pluck('departament_id'))
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(55)->pluck('enterprise_id'))
							->find($id);
			if ($request != "") 
			{
				return view('administracion.papeleria.autorizacioncambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 55,
						'enterprises'	=> $enterprises,
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
				return redirect('administration/stationery/authorization')->with('alert',$alert);
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
				$authorize                   	= App\RequestModel::find($id);
				$authorize->status           	= $request->status;
				$authorize->idAuthorize      	= Auth::user()->id;
				$authorize->authorizeComment 	= $request->authorizeCommentA;
				$authorize->authorizeDate 		= Carbon::now();
				if ($request->status ==  5) 
				{
					$authorize->code 			 = rand(10000000,99999999);
				}
				$authorize->save();

				
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
				
				$emailDelivery 	= App\User::join('user_has_modules','users.id','user_has_modules.user_id')
									->where('user_has_modules.module_id',95)
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
							$kind 			= "Papelería";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date 			= Carbon::now();
							$url 			= route('stationery.follow.edit',['id'=>$id]);
							$subject 		= "Estado de Solicitud";
							$requestUser 	= null;
							//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert 			= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
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
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Papelería";
								$status 		= "Entregar";
								$date 			= Carbon::now();
								$url 			= route('stationery.delivery.edit',['id'=>$id]);
								$subject 		= "Articulos por Entregar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert 			= "swal('', '".Lang::get("messages.request_updated")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(55, $alert, 'administration/stationery');
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if (Auth::user()->module->where('id',95)->count()>0) 
		{
			if ($request->ajax())
			{
				$response = array(
					'valid'     => false,
					'message'   => 'Error.'
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
							'valid'		=> false,
							'message'	=> 'El código es incorrecto.'
						);
					}
				}
				else
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Este campo es obligatorio.'
					);
				}
				return Response($response);
			}
		}
	}


	public function delivery(Request $request)
	{
		if(Auth::user()->module->where('id',95)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$project_id   = $request->project_id;
			$category_id  = $request->category_id;
			
			$requests = App\RequestModel::where('kind',7)
				->where(function($query)
				{
					$query->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(95)->pluck('enterprise_id'));
				})
				->whereIn('status',[5,19])
				->where(function ($query) use ($account, $name, $mindate, $maxdate, $folio,$enterpriseid,$project_id,$category_id)
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
					if($project_id != "")
					{
						$query->where('request_models.idProject',$project_id);
					}
					if($category_id != "")
					{
						$query->where(function($q) use($category_id)
						{
							$q->where('request_models.idWarehouseType',$category_id)
								->orWhereHas('stationery',function($q) use($category_id)
								{
									$q->whereHas('detailStat',function($q) use($category_id)
									{
										$q->where('category',$category_id);
									});
								});
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('reviewDate',[$mindate." 00:00:00", $maxdate." 23:59:59"]);
					}
				})
				->orderBy('reviewDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.papeleria.entrega',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 95,
						'requests'     => $requests,
						'folio'        => $folio, 
						'name'         => $name, 
						'account'      => $account, 
						'mindate'      => $request->mindate, 
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid,
						'project_id'   => $project_id,
						'category_id'  => $category_id,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(95), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showDelivery($id)
	{
		if (Auth::user()->module->where('id',95)->count()>0) 
		{
			$data        = App\Module::find($this->module_id);
			$enterprises = App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(95)->pluck('enterprise_id'))->get();
			$departments = App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(95)->pluck('departament_id'))->get();
			$areas       = App\Area::where('status','ACTIVE')->get();
			$projects    = App\Project::all();
			$labels      = DB::table('request_has_labels')
				->join('labels','idLabels','labels_idlabels')
				->select('labels.description as descr')
				->where('request_has_labels.request_folio',$id)
				->get();
			$request = App\RequestModel::where('kind',7)
				->whereIn('status',[5,19])
				->find($id);
			if ($request != "") 
			{
				return view('administracion.papeleria.entregacambio',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => $this->module_id,
						'option_id'   => 95,
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

	public function updateDelivery(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 9) 
			{
				$alert = "swal('', '".Lang::get("messages.record_already_delivered")."', 'error');";
			}
			else
			{
				$authorize    = App\RequestModel::find($id);
				$emailRequest = App\User::where('id',$authorize->idRequest)->where('notification',1)->get();
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to          = $email->email;
							$kind        = "Papelería";
							$status      = 'ENTREGADO';
							$date        = Carbon::now();
							$url         = route('stationery.follow.edit',['id'=>$id]);
							$subject     = "Articulos Entregados";
							$requestUser = null;
							//Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.record_delivered")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.record.delivered_no_mail")."', 'success');";
					}
				}
				$authorize->save();
				for ($i=0; $i < count($request->tid_art); $i++) 
				{ 
					$warehouse           = App\Warehouse::find($request->tid_art[$i]);
					$warehouse->quantity = $request->tquanty[$i];
					$warehouse->save();
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
					if($warehouse->quantity == 0)
					{
						$warehouse->status = 0;
						$warehouse->save();
					}
					$detailStat = App\DetailStationery::find($request->tid_art_req[$i]);
					$subtotal   = $request->tid_art_delivery[$i]*$unitPrice;
					$iva        = $subtotal*$valueIva;
					$total      = $subtotal+$iva;
					if($request->tid_art_delivery[$i] < $detailStat->quantity )
					{
						$detailStat->quantity = $detailStat->quantity - $request->tid_art_delivery[$i];
						$detailStat->save();
						$stat = App\DetailStationery::create([
							'quantity'     => $request->tid_art_delivery[$i],
							'product'      => $detailStat->product,
							'short_code'   => $detailStat->short_code,
							'long_code'    => $detailStat->long_code,
							'commentaries' => $detailStat->commentaries,
							'category'     => $detailStat->category,
							'idStat'       => $detailStat->idStat,
							'idwarehouse'  => $request->tid_art[$i],
							'subtotal'     => $subtotal,
							'iva'          => $iva,
							'total'        => $total,
							'deliveryDate' => Carbon::now(),
						]);
						foreach ($detailStat->labels as $label)
						{
							$labelExpense               = new App\LabelDetailStationery();
							$labelExpense->idlabels     = $label->idlabels;
							$labelExpense->idStatDetail = $stat->idStatDetail;
							$labelExpense->save();
						}
					}
					else
					{
						$detailStat->subtotal     = $subtotal;
						$detailStat->iva          = $iva;
						$detailStat->total        = $total;
						$detailStat->quantity     = $request->tid_art_delivery[$i];
						$detailStat->idwarehouse  = $request->tid_art[$i];
						$detailStat->deliveryDate = Carbon::now();
						$detailStat->save();
					}
					if($warehouse->status == 0)
					{
						$lot           = $warehouse->lot;
						$totalArticles = $lot->warehouseStationary()->count();
						$deliverys     = $lot->warehouseStationary()->where('status',0)->count();
						if($totalArticles == $deliverys)
						{
							if($lot->idFolioPurchase)
							{
								$idf = $lot->idFolioPurchase->idFolio;
								App\Notifications::where('body',$idf)->update(['end' => Carbon::now()->subDays(1)]);
							}
						}
					}
				}
				$idStationery                = App\RequestModel::find($id)->stationery->first()->idStationery;
				$stationeryRequest           = App\Stationery::find($idStationery);
				$stationeryRequest->subtotal = $stationeryRequest->detailStat()->sum('subtotal');
				$stationeryRequest->iva      = $stationeryRequest->detailStat()->sum('iva');
				$stationeryRequest->total    = $stationeryRequest->detailStat()->sum('total');
				$stationeryRequest->save();
				$authorize                   = App\RequestModel::find($id);
				if($authorize->stationery->first()->detailStat()->whereNull('idwarehouse')->count() == 0)
				{
					$authorize->status       = 9;
					$authorize->deliveryDate = Carbon::now();
				}
				else
				{
					$authorize->status = 19;
					$authorize->code   = rand(10000000,99999999);
				}
				$authorize->save();
			}
			return searchRedirect(95, $alert, 'administration/stationery');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',53)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',53)->count()>0)
			{
				$global_permission = Auth::user()->globalCheck->where('module_id',53)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data           = App\Module::find($this->module_id);
			$name           = $request->name;
			$account        = $request->account;
			$folio          = $request->folio;
			$status         = $request->status;
			$mindate      	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid   = $request->enterpriseid;
			$project_id     = $request->project_id;
			$category_id    = $request->category_id;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								request_models.idRequisition,
								status_requests.description as status,
								stationeries.title as title,
								DATE_FORMAT(stationeries.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								IF(reviewedProject.proyectName IS NULL,requestProject.proyectName, reviewedProject.proyectName) as projectName,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(reviewedAccount.account IS NULL,CONCAT_WS(" - ",requestAccount.account, requestAccount.description), CONCAT_WS(" - ",reviewedAccount.account, reviewedAccount.description)) as account,
								IF(cat_warehouse_types.description IS NULL, details_category.description,cat_warehouse_types.description) as category,
								detail_stationeries.quantity as quantity,
								detail_stationeries.product as product,
								detail_stationeries.short_code as shortCode,
								detail_stationeries.long_code as longCode,
								detail_stationeries.commentaries as commentaries,
								cat_warehouse_concepts.description as productDelivery
							')
							->leftJoin('stationeries','stationeries.idFolio','request_models.folio')
							->leftJoin('detail_stationeries','detail_stationeries.idStat','stationeries.idStationery')
							->leftJoin('cat_warehouse_types','cat_warehouse_types.id','request_models.idWarehouseType')
							->leftJoin('cat_warehouse_types as details_category','details_category.id','detail_stationeries.category')
							->leftJoin('warehouses','warehouses.idwarehouse','detail_stationeries.idwarehouse')
							->leftJoin('cat_warehouse_concepts','cat_warehouse_concepts.id','warehouses.concept')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
							->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->where('request_models.kind',7)
							->where(function($query) 
							{
								$query->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(53)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
							})
							->where(function ($query) 
							{
								$query->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(53)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
							})
							->where(function ($query) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$query->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid,$project_id,$category_id, $account)
							{
								if ($enterpriseid != "") 
								{
									$query->where(function($q) use ($enterpriseid)
									{
										$q->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
									});
								}
								if($account != "")
								{
									$query->where(function($q) use($account)
									{
										$q->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
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
								if($project_id != "")
								{
									$query->where('request_models.idProject',$project_id);
								}
								if($category_id != "")
								{
									$query->where(function($q) use ($category_id)
									{
										$q->where('request_models.idWarehouseType',$category_id)->orWhere('detail_stationeries.category',$category_id);
									});
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
			//$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-solicitudes-de-almacén.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de solicitudes de almacén','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Folio de Requisición','Estado','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Fecha de elaboración','Clasificación del gasto','Categoría','Cantidad','Producto','Código corto','Código largo','Comentarios','Producto Entregado'];
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
					$request->idRequisition		= null;
					$request->status			= '';
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->projectName		= '';
					$request->date				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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
		if(Auth::user()->module->where('id',54)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$name         = $request->name;
			$account      = $request->account;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			
			$enterpriseid = $request->enterpriseid;
			$project_id   = $request->project_id;
			$category_id  = $request->category_id;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								status_requests.description as status,
								stationeries.title as title,
								DATE_FORMAT(stationeries.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								IF(reviewedProject.proyectName IS NULL,requestProject.proyectName, reviewedProject.proyectName) as projectName,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(reviewedAccount.account IS NULL,CONCAT_WS(" - ",requestAccount.account, requestAccount.description), CONCAT_WS(" - ",reviewedAccount.account, reviewedAccount.description)) as account,
								IF(cat_warehouse_types.description IS NULL, details_category.description,cat_warehouse_types.description) as category,
								detail_stationeries.quantity as quantity,
								detail_stationeries.product as product,
								detail_stationeries.short_code as shortCode,
								detail_stationeries.long_code as longCode,
								detail_stationeries.commentaries as commentaries
							')
							->leftJoin('stationeries','stationeries.idFolio','request_models.folio')
							->leftJoin('detail_stationeries','detail_stationeries.idStat','stationeries.idStationery')
							->leftJoin('cat_warehouse_types','cat_warehouse_types.id','request_models.idWarehouseType')
							->leftJoin('cat_warehouse_types as details_category','details_category.id','detail_stationeries.category')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
							->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->where('request_models.kind',7)
							->where(function($query)
							{
								$query->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))
									->orWhereNull('request_models.idEnterprise');
							})
							->where('request_models.status',3)
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(54)->pluck('departament_id'))
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(54)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $account, $folio, $status, $mindate, $maxdate, $enterpriseid,$project_id,$category_id)
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
									$query->where(function($q) use($account)
									{
										$q->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
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
								if($project_id != "")
								{
									$query->where('request_models.idProject',$project_id);
								}
								if($category_id != "")
								{
									$query->where(function($q) use ($category_id)
									{
										$q->where('request_models.idWarehouseType',$category_id)->orWhere('detail_stationeries.category',$category_id);
									});
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
			//$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-solicitudes-de-almacén.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de solicitudes de almacén','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader    = ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Fecha de elaboración','Clasificación del gasto','Categoría','Cantidad','Producto','Código corto','Código largo','Comentarios'];
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
					$request->status			= '';
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->projectName		= '';
					$request->date				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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
		if(Auth::user()->module->where('id',55)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate      	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;
			$project_id		= $request->project_id;
			$category_id	= $request->category_id;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								status_requests.description as status,
								stationeries.title as title,
								DATE_FORMAT(stationeries.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								IF(reviewedProject.proyectName IS NULL,requestProject.proyectName, reviewedProject.proyectName) as projectName,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(reviewedAccount.account IS NULL,CONCAT_WS(" - ",requestAccount.account, requestAccount.description), CONCAT_WS(" - ",reviewedAccount.account, reviewedAccount.description)) as account,
								IF(cat_warehouse_types.description IS NULL, details_category.description,cat_warehouse_types.description) as category,
								detail_stationeries.quantity as quantity,
								detail_stationeries.product as product,
								detail_stationeries.short_code as shortCode,
								detail_stationeries.long_code as longCode,
								detail_stationeries.commentaries as commentaries
							')
							->leftJoin('stationeries','stationeries.idFolio','request_models.folio')
							->leftJoin('detail_stationeries','detail_stationeries.idStat','stationeries.idStationery')
							->leftJoin('cat_warehouse_types','cat_warehouse_types.id','request_models.idWarehouseType')
							->leftJoin('cat_warehouse_types as details_category','details_category.id','detail_stationeries.category')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
							->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->where('request_models.kind',7)
							->where('request_models.status',4)
							->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(55)->pluck('departament_id'))
							->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(55)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid,$project_id,$category_id)
							{
								if ($enterpriseid != "") 
								{
									$query->where(function($queryE) use ($enterpriseid)
									{
										$queryE->where('request_models.idEnterpriseR',$enterpriseid);
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
								if($project_id != "")
								{
									$query->where('request_models.idProject',$project_id);
								}
								if($category_id != "")
								{
									$query->where(function($q) use ($category_id)
									{
										$q->where('request_models.idWarehouseType',$category_id)->orWhere('detail_stationeries.category',$category_id);
									});
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
			//$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-solicitudes-de-almacén.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de solicitudes de almacén','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader    = ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Fecha de elaboración','Clasificación del gasto','Categoría','Cantidad','Producto','Código corto','Código largo','Comentarios'];
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
					$request->status			= '';
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->projectName		= '';
					$request->date				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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

	public function exportDelivery(Request $request)
	{
		if(Auth::user()->module->where('id',95)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account      	= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate      	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;
			$project_id 	= $request->project_id;
			$category_id 	= $request->category_id;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								status_requests.description as status,
								stationeries.title as title,
								DATE_FORMAT(stationeries.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								IF(reviewedProject.proyectName IS NULL,requestProject.proyectName, reviewedProject.proyectName) as projectName,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(reviewedAccount.account IS NULL,CONCAT_WS(" - ",requestAccount.account, requestAccount.description), CONCAT_WS(" - ",reviewedAccount.account, reviewedAccount.description)) as account,
								IF(cat_warehouse_types.description IS NULL, details_category.description,cat_warehouse_types.description) as category,
								detail_stationeries.quantity as quantity,
								detail_stationeries.product as product,
								detail_stationeries.short_code as shortCode,
								detail_stationeries.long_code as longCode,
								detail_stationeries.commentaries as commentaries
							')
							->leftJoin('stationeries','stationeries.idFolio','request_models.folio')
							->leftJoin('detail_stationeries','detail_stationeries.idStat','stationeries.idStationery')
							->leftJoin('cat_warehouse_types','cat_warehouse_types.id','request_models.idWarehouseType')
							->leftJoin('cat_warehouse_types as details_category','details_category.id','detail_stationeries.category')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('accounts as requestAccount','requestAccount.idAccAcc','request_models.account')
							->leftJoin('accounts as reviewedAccount','reviewedAccount.idAccAcc','request_models.accountR')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('projects as reviewedProject','reviewedProject.idproyect','request_models.idProjectR')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->where('request_models.kind',7)
							->where(function($query)
							{
								$query->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(95)->pluck('enterprise_id'));
							})
							->whereIn('request_models.status',[5,19])
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid,$project_id,$category_id)
							{
								if ($enterpriseid != "") 
								{
									$query->where(function($queryE) use ($enterpriseid)
									{
										$queryE->where('request_models.idEnterpriseR',$enterpriseid);
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
								if($project_id != "")
								{
									$query->where('request_models.idProject',$project_id);
								}
								if($category_id != "")
								{
									$query->where(function($q) use ($category_id)
									{
										$q->where('request_models.idWarehouseType',$category_id)->orWhere('detail_stationeries.category',$category_id);
									});
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
			//$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Entrega-de-solicitudes-de-almacén.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de solicitudes de almacén','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeader    = ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Empresa','Proyecto','Fecha de elaboración','Clasificación del gasto','Categoría','Cantidad','Producto','Código corto','Código largo','Comentarios'];
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
					$request->status			= '';
					$request->title				= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->projectName		= '';
					$request->date				= '';
					$request->account			= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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

	public function ArticleRequest(Request $request)
	{
		
		if($request->ajax())
		{
			$articleR = "";
			$inventary = "";
			$kindSortRequests  = $request->kindSort == "" ? "quantity": $request->kindSort;
			$kindSortInventories = $request->kindSort == "" ? "quantity": $request->kindSort;
			$ascDescRequests   = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			$ascDescInventories  = $request->ascDesc  == "" ? "ASC"   : $request->ascDesc;
			switch($kindSortRequests)
			{
				case "categoria":
					$kindSortRequests = "request_kinds.kind";
					break;
				
				case "cantidad":
					$kindSortRequests = "quantity";
					break;

				case "concepto":
					$kindSortRequests = "quantity";
					// $kindSortPayments = DB::raw('CONCAT_WS(" ",accounts.account,accounts.description)');
					break;
			}

			switch($kindSortInventories)
			{
				case "categoria":
					$kindSortInventories = "cat_warehouse_types.description";
					break;
				
				case "cantidad":
					$kindSortInventories = "quantity";
					break;

				case "concepto":
					$kindSortInventories = "cat_warehouse_concepts.description";
					break;
				
					case "concepto":
					$kindSortInventories = "amountUnit";
					break;
			}

			$articleAll     = App\DetailStationery::select('detail_stationeries.*')
								->join('stationeries', 'stationeries.idStationery','detail_stationeries.idStat')
								->join('request_kinds','request_kinds.idrequestkind','stationeries.idKind')
								->join('request_models', 'stationeries.idFolio','request_models.folio')
								->where('request_models.kind',7)
								->whereIn('request_models.status',[5,19])
								->whereNull('idwarehouse')
								->where('request_models.folio',$request->folio)
								->where(function($query) use($request)
								{
									if($request->search != '')
									{
										$query->whereRaw('CONCAT(detail_stationeries.product) LIKE "%'.$request->search.'%"');
									}
								})
								->orderBy($kindSortRequests, $ascDescRequests)
								->get();

			$inventaryAll	= App\Warehouse::join('cat_warehouse_concepts', 'warehouses.concept','cat_warehouse_concepts.id')
								->join('cat_warehouse_types', 'warehouses.warehouseType','cat_warehouse_types.id')
								->where('quantity','!=',0)->where('warehouses.status',1)
								->whereHas('lot',function($q) use($request){
									$q->where('idEnterprise',$request->idEnterprise);
								})
								->where(function($query) use($request)
								{
								if($request->search != '')
								{
									$query->whereRaw('CONCAT(cat_warehouse_concepts.description) LIKE "%'.$request->search.'%"');
								}
							})
							->orderBy($kindSortInventories, $ascDescInventories)
							->paginate(10);
			if($request->selected != "")
			{
				$selectedInventary = App\Warehouse::join('cat_warehouse_concepts', 'warehouses.concept','cat_warehouse_concepts.id')
				->join('cat_warehouse_types', 'warehouses.warehouseType','cat_warehouse_types.id')
				->where('quantity','!=',0)->where('warehouses.status',1)
				->where('idwarehouse', $request->selected)
				->whereHas('lot',function($q) use($request){
					$q->where('idEnterprise',$request->idEnterprise);
				})
				->orderBy($kindSortInventories, $ascDescInventories)
				->get();
			}
			else
			{
				$selectedInventary = "";
			}
			
			$articleR = view('administracion.papeleria.table_request',['articleAll'=>$articleAll]);
			$inventary = view('administracion.papeleria.table_inventary',['inventaryAll'=>$inventaryAll,'selectedInventary'=>$selectedInventary]);
			
			$paginate = '<div class="result_pagination'.($inventaryAll->lastPage() == 1 ? 'hidden' : '').'">';
            $paginate .= html_entity_decode(preg_replace("/(\r)*(\n)*/", "", $inventaryAll->appends($_GET)->links()));
            $paginate .= '</div>';
			return json_encode([urlencode($articleR), urlencode($inventary), $paginate]);
			
			//return view('administracion.papeleria.tablas',['articleAll'=>$articleAll, 'inventaryAll'=>$inventaryAll])->render();
		}
	}
}
