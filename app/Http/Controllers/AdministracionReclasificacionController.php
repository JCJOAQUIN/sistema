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
use Ilovepdf\CompressTask;
use Illuminate\Support\Facades\Cookie;

class AdministracionReclasificacionController extends Controller
{
	private $module_id = 207;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$folio			= $request->folio;
			$name			= $request->name;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$kind			= $request->kind;
			$enterpriseid	= $request->enterpriseid;
			$accountid		= $request->accountid;
			$departmentid	= $request->departmentid;
			$areaid			= $request->areaid;
			$projectid		= $request->projectid;
			$status			= $request->status;

			$requests = App\RequestModel::whereIn('status',[5,10,11,12,18])
						->whereIn('kind',[1,3,5,8,9,12,13,14,15,17])
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(208)->pluck('departament_id'))
										->orWhere('request_models.idDepartment',null);
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(208)->pluck('enterprise_id'))
										->orWhere('request_models.idEnterprise',null);
						})
						->where(function($q) use ($folio,$name,$mindate,$maxdate,$kind,$enterpriseid,$accountid,$departmentid,$areaid,$projectid,$status)
						{
							if ($folio != '') 
							{
								$q->where('folio',$folio);
							}
							if ($name != '') 
							{
								$q->whereHas('requestUser', function($queryU) use($name)
								{
									$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
								});
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$q->whereBetween('authorizeDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($kind != '')
							{
								$q->whereIn('kind',$kind);
							}
							if ($enterpriseid != '') 
							{
								$q->where(function($qe) use ($enterpriseid)
								{
									$qe->where('idEnterprise',$enterpriseid)->orWhere('idEnterpriseR',$enterpriseid);
								});
							}
							if ($accountid != '') 
							{
								$q->where(function($qa) use ($accountid)
								{
									$qa->where('account',$accountid)->orWhere('accountR',$accountid);
								});
							}
							if ($departmentid != '') 
							{
								$q->where(function($qd) use ($departmentid)
								{
									$qd->where('idDepartment',$departmentid)->orWhere('idDepartamentR',$departmentid);
								});
							}
							if ($areaid != '') 
							{
								$q->where(function($qa) use ($areaid)
								{
									$qa->where('idArea',$areaid)->orWhere('idAreaR',$areaid);
								});
							}
							if ($projectid != '') 
							{
								$q->where(function($qp) use ($projectid)
								{
									$qp->where('idProject',$projectid)->orWhere('idProjectR',$projectid);
								});
							}
							if ($status != '') 
							{
								$q->where('status',$status);
							}
						})
						->orderBy('authorizeDate','DESC')
						->orderBy('folio','DESC')
						->paginate(10);

			return response(
				view('administracion.reclasificacion.busqueda',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 208,
						'folio'			=> $folio,
						'name'			=> $name,
						'mindate'		=> $mindate,
						'maxdate'		=> $maxdate,
						'kind'			=> $kind,
						'enterpriseid'	=> $enterpriseid,
						'accountid'		=> $accountid,
						'departmentid'	=> $departmentid,
						'areaid'		=> $areaid,
						'projectid'		=> $projectid,
						'status'		=> $status,
						'requests'		=> $requests,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(208), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getAccount(Request $request)
	{
		if ($request->ajax()) 
		{
			$accounts = App\Account::orderNumber()->whereIn('idEnterprise',$request->enterpriseid)
						->where('selectable',1)
						->get();
			if (count($accounts)>0) 
			{
				return Response($accounts);
			}
		}
	}

	public function follow($id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$data		= App\Module::find($this->module_id);
			$request	= App\RequestModel::whereIn('status',[5,10,11,12])
							->whereIn('kind',[1,3,5,8,9,12,13,14,15,17])
							->where(function($permissionDep)
							{
								$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(208)->pluck('departament_id'))
											->orWhere('request_models.idDepartment',null);
							})
							->where(function($permissionEnt)
							{
								$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(208)->pluck('enterprise_id'))
											->orWhere('request_models.idEnterprise',null);
							})
							->find($id);

			if ($request != '') 
			{
				switch ($request->kind) 
				{
					case 1:
						return view('administracion.reclasificacion.compra',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 3:
						return view('administracion.reclasificacion.gasto',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 5:
						return view('administracion.reclasificacion.prestamo',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 8:
						return view('administracion.reclasificacion.recurso',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 9:
						return view('administracion.reclasificacion.reembolso',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 12:
						return view('administracion.reclasificacion.prestamo_empresa',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 13:
						return view('administracion.reclasificacion.compra_empresa',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 14:
						return view('administracion.reclasificacion.grupos',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;

					case 15:
						return view('administracion.reclasificacion.movimientos_empresa',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;
					case 17:
						return view('administracion.reclasificacion.registrocompra',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 208,
								'request'	=> $request
							]
						);
						break;
					
					default:
					return abort(404);
				}
			}
			else
			{
				return abort(404);
			}
		}
	}

	public function updateReclassificationPurchase(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_department	= $req->idDepartamentR;
			$old_direction	= $req->idAreaR;
			$old_project	= $req->idProjectR;
			$old_account	= $req->accountR;
			if ($req->idRequisition == "") 
			{
				$old_wbs		= $req->code_wbs;
				$old_edt		= $req->code_edt;
			}

			$req_has_recl				= new App\RequestHasReclassification();
			$req_has_recl->folio		= $folio;
			$req_has_recl->kind			= $kind;
			$req_has_recl->idEnterprise	= $old_enterprise;
			$req_has_recl->idDepartment	= $old_department;
			$req_has_recl->idArea		= $old_direction;
			$req_has_recl->idProject	= $old_project;
			if ($req->idRequisition == "") 
			{
				$req_has_recl->code_wbs		= $old_wbs;
				$req_has_recl->code_edt		= $old_edt;
			}
			$req_has_recl->idAccAcc		= $old_account; 
			$req_has_recl->idUser		= Auth::user()->id;
			$req_has_recl->date			= Carbon::now();
			$req_has_recl->commentaries	= $request->commentaries;
			$req_has_recl->save();

			$req->idDepartamentR	= $request->idDepartmentR;
			$req->idAreaR			= $request->idAreaR;
			$req->idProjectR		= $request->project_id;
			$req->idProject			= $request->project_id;
			$req->code_wbs			= $request->code_wbs;
			$req->code_edt			= $request->code_edt;
			$req->accountR			= $request->accountR;
			$req->save();

			if ($request->requisition_folio != "") 
			{
				$req			= App\RequestModel::find($request->requisition_folio);
				$folio			= $req->folio;
				$kind			= $req->kind;
				$old_project	= $req->idProject;
				$old_wbs		= $req->code_wbs;
				$old_edt		= $req->code_edt;

				$req_has_recl				= new App\RequestHasReclassification();
				$req_has_recl->folio		= $folio;
				$req_has_recl->kind			= $kind;
				$req_has_recl->idProject	= $old_project;
				$req_has_recl->code_wbs		= $old_wbs;
				$req_has_recl->code_edt		= $old_edt;
				$req_has_recl->idUser		= Auth::user()->id;
				$req_has_recl->date			= Carbon::now();
				$req_has_recl->commentaries	= $request->commentaries;	
				$req_has_recl->save();

				$req->idProject			= $request->project_id;
				$req->code_wbs			= $request->code_wbs;
				$req->code_edt			= $request->code_edt;
				$req->save();

				$req->requisition->code_wbs = $request->code_wbs;
				$req->requisition->code_edt = $request->code_edt;
				$req->requisition->save();
			}

			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReclassificationResource(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_department	= $req->idDepartamentR;
			$old_direction	= $req->idAreaR;
			$old_project	= $req->idProjectR;
			$old_wbs		= $req->code_wbs;
			$old_edt		= $req->code_edt;

			//$2y$10$BUiDWQle9mQ2VufUpmsvTeyPUYzFRUanCDCtLDlB.ozNrpsnScSg2

			for ($i=0; $i < count($request->idresourcedetail); $i++)
			{
				$t_detailResource             	= App\ResourceDetail::find($request->idresourcedetail[$i]);
				$old_account 					= $t_detailResource->idAccAccR;

				$req_has_recl					= new App\RequestHasReclassification();
				$req_has_recl->folio			= $folio;
				$req_has_recl->kind				= $kind;
				$req_has_recl->idEnterprise		= $old_enterprise;
				$req_has_recl->idDepartment		= $old_department;
				$req_has_recl->idArea			= $old_direction;
				$req_has_recl->idProject		= $old_project;
				$req_has_recl->code_wbs			= $old_wbs;
				$req_has_recl->code_edt			= $old_edt;
				$req_has_recl->idAccAcc			= $old_account; 
				$req_has_recl->idUser			= Auth::user()->id;
				$req_has_recl->date				= Carbon::now();
				$req_has_recl->commentaries		= $request->commentaries;	
				$req_has_recl->idresourcedetail	= $request->idresourcedetail[$i];
				$req_has_recl->save();


				$t_detailResource->idAccAccR  = $request->accountR[$i];
				$t_detailResource->save();
			}
			$req->idDepartamentR	= $request->idDepartmentR;
			$req->idAreaR			= $request->idAreaR;
			$req->idProjectR		= $request->project_id;
			$req->code_wbs			= $request->code_wbs;
			$req->code_edt			= $request->code_edt;
			$req->save();

			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReclassificationRefund(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_direction	= $req->idAreaR;
			$old_department	= $req->idDepartamentR;
			$old_project	= $req->idProjectR;
			$old_wbs		= $req->code_wbs;
			$old_edt		= $req->code_edt;

			for ($i=0; $i < count($request->idRefundDetail); $i++) 
			{ 
				$t_detailRefund					= App\RefundDetail::find($request->idRefundDetail[$i]);
				$old_account					= $t_detailRefund->idAccountR;
				
				$req_has_recl					= new App\RequestHasReclassification();
				$req_has_recl->folio			= $folio;
				$req_has_recl->kind				= $kind;
				$req_has_recl->idEnterprise		= $old_enterprise;
				$req_has_recl->idDepartment		= $old_department;
				$req_has_recl->idArea			= $old_direction;
				$req_has_recl->idProject		= $old_project;
				$req_has_recl->code_wbs			= $old_wbs;
				$req_has_recl->code_edt			= $old_edt;
				$req_has_recl->idAccAcc			= $old_account;
				$req_has_recl->idUser			= Auth::user()->id;
				$req_has_recl->date				= Carbon::now();
				$req_has_recl->commentaries		= $request->commentaries;
				$req_has_recl->idRefundDetail	= $request->idRefundDetail[$i];

				$req_has_recl->save();
				$t_detailRefund->idAccountR		= $request->accountR[$i];
				$t_detailRefund->save();
			}
			
			$req->idDepartamentR    = $request->idDepartamentR;
			$req->idAreaR			= $request->idAreaR;
			$req->idProjectR		= $request->project_id;
			$req->code_wbs			= $request->code_wbs;
			$req->code_edt			= $request->code_edt;
			$req->save();

			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReclassificationExpense(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_department	= $req->idDepartamentR;
			$old_direction	= $req->idAreaR;
			$old_project	= $req->idProjectR;
			$old_wbs		= $req->code_wbs;
			$old_edt		= $req->code_edt;

			for ($i=0; $i < count($request->idExpensesDetail); $i++) 
			{ 
				$t_detailExpenses				= App\ExpensesDetail::find($request->idExpensesDetail[$i]);
				$old_account					= $t_detailExpenses->idAccountR;
				
				$req_has_recl					= new App\RequestHasReclassification();
				$req_has_recl->folio			= $folio;
				$req_has_recl->kind				= $kind;
				$req_has_recl->idEnterprise		= $old_enterprise;
				$req_has_recl->idDepartment		= $old_department;
				$req_has_recl->idArea			= $old_direction;
				$req_has_recl->idProject		= $old_project;
				$req_has_recl->code_wbs			= $old_wbs;
				$req_has_recl->code_edt			= $old_edt;
				$req_has_recl->idAccAcc			= $old_account;
				$req_has_recl->idUser			= Auth::user()->id;
				$req_has_recl->date				= Carbon::now();
				$req_has_recl->commentaries		= $request->commentaries;
				$req_has_recl->idExpensesDetail	= $request->idExpensesDetail[$i];
				$req_has_recl->save();
				
				$t_detailExpenses->idAccountR	= $request->accountR[$i];
				$t_detailExpenses->save();
			}

			$req->idDepartamentR    = $request->idDepartmentR;
			$req->idAreaR			= $request->idAreaR;
			$req->idProjectR		= $request->project_id;
			$req->code_wbs			= $request->code_wbs;
			$req->code_edt			= $request->code_edt;
			$req->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReclassificationLoan(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_department	= $req->idDepartamentR;
			$old_direction	= $req->idAreaR;
			$old_account	= $req->accountR;

			$req_has_recl				= new App\RequestHasReclassification();
			$req_has_recl->folio		= $folio;
			$req_has_recl->kind			= $kind;
			$req_has_recl->idEnterprise	= $old_enterprise;
			$req_has_recl->idDepartment	= $old_department;
			$req_has_recl->idArea		= $old_direction;
			$req_has_recl->idAccAcc		= $old_account; 
			$req_has_recl->idUser 		= Auth::user()->id;
			$req_has_recl->date 		= Carbon::now();
			$req_has_recl->commentaries = $request->commentaries;	
			$req_has_recl->save();

			$req->idDepartamentR	= $request->idDepartmentR;
			$req->idAreaR			= $request->idAreaR;
			$req->accountR			= $request->accountR;
			$req->save();

			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}


	public function updateReclassificationPurchaseRecord(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);

			$folio			= $req->folio;
			$kind			= $req->kind;
			$old_enterprise	= $req->idEnterpriseR;
			$old_department	= $req->idDepartamentR;
			$old_direction	= $req->idAreaR;
			$old_project	= $req->idProjectR;
			$old_account	= $req->accountR;

			$req_has_recl				= new App\RequestHasReclassification();
			$req_has_recl->folio		= $folio;
			$req_has_recl->kind			= $kind;
			$req_has_recl->idEnterprise	= $old_enterprise;
			$req_has_recl->idDepartment	= $old_department;
			$req_has_recl->idArea		= $old_direction;
			$req_has_recl->idProject	= $old_project;
			$req_has_recl->idAccAcc		= $old_account; 
			$req_has_recl->idUser 		= Auth::user()->id;
			$req_has_recl->date 		= Carbon::now();
			$req_has_recl->commentaries = $request->commentaries;	
			$req_has_recl->save();

			$req->idDepartamentR	= $request->idDepartmentR;
			$req->idAreaR			= $request->idAreaR;
			$req->idProjectR		= $request->project_id;
			$req->accountR			= $request->accountR;
			$req->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReclassificationPurchaseEnterprise(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req		= App\RequestModel::find($id);
			$t_purchase	= App\PurchaseEnterprise::find($req->purchaseEnterprise->first()->idpurchaseEnterprise);
			
			$old_enterprise_origin	= $t_purchase->idEnterpriseOriginR;
			$old_department_origin	= $t_purchase->idDepartamentOriginR;
			$old_direction_origin	= $t_purchase->idAreaOriginR;
			$old_project_origin		= $t_purchase->idProjectOriginR;
			$old_account_origin		= $t_purchase->idAccAccOriginR;
			
			$old_enterprise_destiny	= $t_purchase->idEnterpriseDestinyR;
			$old_account_destiny	= $t_purchase->idAccAccDestinyR;
			$old_project_destiny	= $t_purchase->idProjectDestinyR;

			$req_has_recl						= new App\RequestHasReclassification();
			$req_has_recl->folio				= $req->folio;
			$req_has_recl->kind					= $req->kind;
			$req_has_recl->idEnterpriseOrigin	= $old_enterprise_origin;
			$req_has_recl->idDepartmentOrigin	= $old_department_origin;
			$req_has_recl->idAreaOrigin			= $old_direction_origin;
			$req_has_recl->idProjectOrigin		= $old_project_origin;
			$req_has_recl->idAccAccOrigin		= $old_account_origin; 
			
			$req_has_recl->idEnterpriseDestiny	= $old_enterprise_destiny;
			$req_has_recl->idProjectDestiny		= $old_project_destiny;
			$req_has_recl->idAccAccDestiny		= $old_account_destiny; 
			
			$req_has_recl->idUser				= Auth::user()->id;
			$req_has_recl->date					= Carbon::now();
			$req_has_recl->commentaries			= $request->commentaries;	
			$req_has_recl->save();

			$t_purchase->idEnterpriseOriginR	= $request->enterpriseid_origin;
			$t_purchase->idAreaOriginR			= $request->areaid_origin;
			$t_purchase->idDepartamentOriginR	= $request->departmentid_origin;
			$t_purchase->idAccAccOriginR		= $request->accountid_origin;
			$t_purchase->idProjectOriginR		= $request->projectid_origin;
			$t_purchase->idEnterpriseDestinyR	= $request->enterpriseid_destination;
			$t_purchase->idAccAccDestinyR		= $request->accountid_destination;
			$t_purchase->idProjectDestinyR		= $request->projectid_destination;
			$t_purchase->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('error');
		}
	}

	public function updateReclassificationGroups(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req		= App\RequestModel::find($id);
			$t_groups	= App\Groups::find($req->groups->first()->idgroups);
			
			$old_enterprise_origin	= $t_groups->idEnterpriseOriginR;
			$old_department_origin	= $t_groups->idDepartamentOriginR;
			$old_direction_origin	= $t_groups->idAreaOriginR;
			$old_project_origin		= $t_groups->idProjectOriginR;
			$old_account_origin		= $t_groups->idAccAccOriginR;
			
			$old_enterprise_destiny	= $t_groups->idEnterpriseDestinyR;
			$old_account_destiny	= $t_groups->idAccAccDestinyR;

			$req_has_recl						= new App\RequestHasReclassification();
			$req_has_recl->folio				= $req->folio;
			$req_has_recl->kind					= $req->kind;
			$req_has_recl->idEnterpriseOrigin	= $old_enterprise_origin;
			$req_has_recl->idDepartmentOrigin	= $old_department_origin;
			$req_has_recl->idAreaOrigin			= $old_direction_origin;
			$req_has_recl->idProjectOrigin		= $old_project_origin;
			$req_has_recl->idAccAccOrigin		= $old_account_origin; 
			
			$req_has_recl->idEnterpriseDestiny	= $old_enterprise_destiny;
			$req_has_recl->idAccAccDestiny		= $old_account_destiny; 
			
			$req_has_recl->idUser				= Auth::user()->id;
			$req_has_recl->date					= Carbon::now();
			$req_has_recl->commentaries			= $request->commentaries;	
			$req_has_recl->save();
			
			$t_groups->idEnterpriseOriginR		= $request->enterpriseid_origin;
			$t_groups->idAreaOriginR			= $request->areaid_origin;
			$t_groups->idDepartamentOriginR		= $request->departmentid_origin;
			$t_groups->idAccAccOriginR			= $request->accountid_origin;
			$t_groups->idProjectOriginR			= $request->projectid_origin;
			$t_groups->idEnterpriseDestinyR		= $request->enterpriseid_destination;
			$t_groups->idAccAccDestinyR			= $request->accountid_destination;
			$t_groups->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('error');
		}
	}

	public function updateReclassificationMovementsEnterprise(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req			= App\RequestModel::find($id);
			$t_movements	= App\MovementsEnterprise::find($req->movementsEnterprise->first()->idmovementsEnterprise);
			
			$old_enterprise_origin	= $t_movements->idEnterpriseOriginR;
			$old_account_origin		= $t_movements->idAccAccOriginR;
			$old_account_destiny	= $t_movements->idAccAccDestinyR;

			$req_has_recl						= new App\RequestHasReclassification();
			$req_has_recl->folio				= $req->folio;
			$req_has_recl->kind					= $req->kind;
			$req_has_recl->idEnterpriseOrigin	= $old_enterprise_origin;
			$req_has_recl->idAccAccOrigin		= $old_account_origin; 
			$req_has_recl->idAccAccDestiny		= $old_account_destiny; 
			
			$req_has_recl->idUser				= Auth::user()->id;
			$req_has_recl->date					= Carbon::now();
			$req_has_recl->commentaries			= $request->commentaries;	
			$req_has_recl->save();
			
			$t_movements->idEnterpriseOriginR		= $request->enterpriseid_origin;
			$t_movements->idAccAccOriginR			= $request->accountid_origin;
			$t_movements->idAccAccDestinyR			= $request->accountid_destination;
			$t_movements->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('error');
		}
	}

	public function updateReclassificationLoanEnterprise(Request $request,$id)
	{
		if (Auth::user()->module->where('id',208)->count()>0) 
		{
			$req	= App\RequestModel::find($id);
			$t_loan	= App\LoanEnterprise::find($req->loanEnterprise->first()->idloanEnterprise);
			
			$old_enterprise_origin	= $t_loan->idEnterpriseOriginR;
			$old_account_origin		= $t_loan->idAccAccOriginR;
			$old_enterprise_destiny	= $t_loan->idEnterpriseDestinyR;
			$old_account_destiny	= $t_loan->idAccAccDestinyR;

			$req_has_recl						= new App\RequestHasReclassification();
			$req_has_recl->folio				= $req->folio;
			$req_has_recl->kind					= $req->kind;
			$req_has_recl->idEnterpriseOrigin	= $old_enterprise_origin;
			$req_has_recl->idAccAccOrigin		= $old_account_origin; 
			$req_has_recl->idEnterpriseDestiny	= $old_enterprise_destiny;
			$req_has_recl->idAccAccDestiny		= $old_account_destiny; 
			
			$req_has_recl->idUser				= Auth::user()->id;
			$req_has_recl->date					= Carbon::now();
			$req_has_recl->commentaries			= $request->commentaries;	
			$req_has_recl->save();
			
			$t_loan->idEnterpriseOriginR		= $request->enterpriseid_origin;
			$t_loan->idAccAccOriginR			= $request->accountid_origin;
			$t_loan->idEnterpriseDestinyR		= $request->enterpriseid_destination;
			$t_loan->idAccAccDestinyR			= $request->accountid_destination;
			$t_loan->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return searchRedirect(208, $alert, '/administration/reclassification/search');
		}
		else
		{
			return redirect('error');
		}
	}

	public function store(Request $request)
	{
		//
	}

	public function show($id)
	{
		//
	}

	public function edit($id)
	{
		//
	}

	public function update(Request $request, $id)
	{
		//
	}

	public function destroy($id)
	{
		//
	}
}
