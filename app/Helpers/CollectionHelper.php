<?php
namespace App\Helpers;
use Auth;
use App;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class CollectionHelper
{
	public static function paginate(Collection $results, $pageSize, $page)
	{
		//$page = Paginator::resolveCurrentPage('page');
		$total = $results->count();
		return self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
			'path' => Paginator::resolveCurrentPath(),
			'pageName' => 'page',
		]);
	}

	/**
	 * Create a new length-aware paginator instance.
	 *
	 * @param  \Illuminate\Support\Collection  $items
	 * @param  int  $total
	 * @param  int  $perPage
	 * @param  int  $currentPage
	 * @param  array  $options
	 * @return \Illuminate\Pagination\LengthAwarePaginator
	 */
	protected static function paginator($items, $total, $perPage, $currentPage, $options)
	{
		return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
			'items', 'total', 'perPage', 'currentPage', 'options'
		));
	}

	public static function select(Request $request)
	{
		if (Auth::user()->module->where('id',$request->module_id)->count()>0)
		{
			$result['results'] = [];
			if(isset($request->model) && $request->ajax())
			{
				try
				{
					$paginate = 10;
					switch ($request->model)
					{
						case '1':  // Model: CatCodeWBS
						case '22': // Model: CatCodeWBS only status 1
						case '51': // Model: CatCodeWBS depends several Proyects
						case '54': // Model: CatCodeWBS - status 1 & statusSolicitud = 2
							$clave = App\CatCodeWBS::where('code_wbs','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->where(function ($q) use ($request)
								{
									if($request->model == "22")
									{
										$q->whereIn('status', [1]);
									}
									if(in_array($request->model,[1, 22]))
									{
										$q->where('project_id', $request->params_data['id']);
									}
									if($request->model == "51")
									{
										$q->whereIn('project_id', $request->params_data['extra']['extra']);
									}
									if($request->model == "54")
									{
										$q->where('project_id', $request->params_data['id']);
										if($request->params_data['extra']['status'] == 2)
										{
											$q->where('status',1);
										}
									}
								})
								->orderBy('code_wbs', 'asc')
								->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach ($clave as $c)
							{
								$tempArray['id']	= $c->id;
								$tempArray['text']	= $c->code_wbs;
								if(App\CatCodeEDT::where('codewbs_id', $c->id)->count() > 0)
								{
									$tempArray['flagEDT'] = true;
								}
								else
								{
									unset($tempArray['flagEDT']);
								}
								$result['results'][]= $tempArray;
							}
							break;
						case '2':	// Model: CatZipCode
							$clave = App\CatZipCode::where('zip_code','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach ($clave as $c)
							{
								$tempArray['id']     = $c->zip_code;
								$tempArray['text']   = $c->zip_code;
								$result['results'][] = $tempArray;
							}
							break;
						case '3': // Model: Account (all - selectable 0 and 1)
						case '4': // Model: Account (only 5102, 5303, 5403)
						case '5': // Model: Account (only 5 description','Consumibles, materiales y refacciones)
						case '6': // Model: Account (only 1)
						case '9': // Model: Account (only 12)
						case '10': // Model: Account (all - selectable 1)
						case '11': // Model: Account (only FUNCIONARIOS Y EMPLEADOS)
						case '12': // Model: Account (only 5)
						case '16': // Model: Account (only 1 && 2)
						case '18': // Model: Account (only 1 && 5)
						case '23': // Model: Account (only 4)
						case '32': // Model: Account (only 4 && 1)
						case '33': // Model: Account (only 1102, 1103, 1103)
						case '57': // Model: Account by warehousetype
						case '58': // Model: Account (only 5 and level 2)
						case '59': // Model: Account (all and level 1 - 2)
							if(in_array($request->model, [5]))
							{
								$allAccounts = App\Account::where('idEnterprise',$request->params_data['id'])
									->where('account','LIKE','5%')
									->where('selectable',1)
									->whereRaw('CONCAT(accounts.account," - ",accounts.description," (",accounts.content,")") LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
									->get();
								$accountsID = [];
								foreach ($allAccounts as $acc)
								{
									if(App\Account::orderNumber()->where('account',$acc->father)
											->where('idEnterprise',$request->params_data['id'])
											->where('account','like','5%')
											->where('selectable',0)
											->where('description','Consumibles, materiales y refacciones')
											->exists())
									{
										$accountsID[] = $acc->idAccAcc;
									}
								}
								$clave = App\Account::whereIn('idAccAcc',$accountsID)
									->paginate($paginate);
							}
							else if(in_array($request->model, [57]))
							{
								$acc = [];
								switch ($request->params_data['extra']['warehouseType'])
								{
									case 1:#papeleria
										$clave    = App\Account::where('idEnterprise',$request->params_data['id'])
											->where('account','1108002')
											->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
											->where('selectable',1)
											->orderBy('account','asc')
											->paginate($paginate);
										break;
									case 2:#herramienta
										$pagination = false;
										$accounts = App\Account::where('idEnterprise',$request->params_data['id'])
											->where(function($query)
											{
												$query->where('account','like','1202%')->orWhere('account','like','1204%');
											})
											->where('selectable',1)
											->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
											->orderBy('account','asc')
											->get();											
										foreach ($accounts as $account)
										{
											if(
													App\Account::
														where('account',$account->father)
														->where('idEnterprise',$request->params_data['id'])
														->where(function($query)
															{
																$query->where('account','like','1202%')->orWhere('account','like','1204%');
															})
														->where('selectable',0)
														->where(function($query)
															{
																$query->where('description','Mobiliario y equipo')->orWhere('description','Maquinaria y equipo');
															})
														->exists()
											)
											{
												array_push($acc,$account->idAccAcc);
											}
										}
										
										$clave = App\Account::whereIn('idAccAcc',$acc)
											->paginate($paginate);
										break;
									case 3:#Insumo
										$clave    = App\Account::where('idEnterprise',$request->params_data['id'])
											->where('account','1108001')
											->where('selectable',1)
											->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
											->orderBy('account','asc')
											->paginate($paginate);
										break;
									case 4:
									case 'computo':
										$clave    = App\Account::where('idEnterprise',$request->params_data['id'])
											->whereIn('account',[1202001,1202004,1202005,1202006,1202007,1202008,1202009])
											->where('selectable',1)
											->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
											->orderBy('account','asc')
											->paginate($paginate);
									break;
								}
							}
							else if(in_array($request->model, [58]))
							{
								$getAccount	= App\Account::where('idEnterprise',$request->params_data['id'])
											->where('account','LIKE','5%')
											->whereRaw('CONCAT(accounts.account," - ",accounts.description," (",accounts.content,")") LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
											->get();


								foreach ($getAccount as $acc) 
								{
									if($acc->level == 2)
									{
										$tempArray['id']	= $acc->account;
										$tempArray['text']	= $acc->account." - ".$acc->description." (".$acc->content.")";
										$result['results'][]= $tempArray;
									}
								}
							}
							else if(in_array($request->model,[59]))
							{
								$accounts 	= App\Account::where('idEnterprise',$request->params_data['id'])
												->whereRaw('CONCAT(accounts.account," - ",accounts.description," (",accounts.content,")") LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
												->get();
								foreach ($accounts as $acc) 
								{
									if ($acc->level == 1 || $acc->level == 2) 
									{
										$tempArray['id']		= $acc->account;
										$tempArray['text']		= $acc->account.' '.$acc->description;
										$result['results'][]	= $tempArray;
									}
								}
							}
							else
							{
								$clave = App\Account::orderNumber()->where('idEnterprise',$request->params_data['id'])
									->whereRaw('CONCAT(accounts.account," - ",accounts.description," (",accounts.content,")") LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
									->where(function($q) use($request)
									{
										if($request->model == '4')
										{
											$q->where('account','LIKE','5102%')
												->orWhere('account','LIKE','5303%')
												->orWhere('account','LIKE','5403%');
										}
										if($request->model =='6')
										{
											$q->where('account','LIKE','1%');
										}
										if($request->model =='9')
										{
											$q->where('account','LIKE','12%');
										}
										if($request->model == '11')
										{
											$q->where('description','FUNCIONARIOS Y EMPLEADOS');
										}
										if($request->model =='12')
										{
											$q->where('account','LIKE','5%');
										}
										if($request->model =='16')
										{
											$q->where('account','LIKE','1%')->orWhere('account','like','2%');
										}
										if($request->model =='18')
										{
											$q->where('account','LIKE','1%')->orWhere('account','like','5%');
										}
										if($request->model =='23')
										{
											$q->where('account','LIKE','4%');
										}
										if($request->model =='32')
										{
											$q->where('account','LIKE','4%')->orWhere('account','like','1%');
										}
										if($request->model == '33')
										{
											$q->where('account','LIKE','1102%')
												->orWhere('account','LIKE','1103%')
												->orWhere('account','LIKE','1104%');
										}
									})
									->where(function($q) use($request)
									{
										if(in_array($request->model,[4,6,9,10,11,12,16,18,23,32,33]))
										{
											$q->where('selectable',1);
										}
									})
									->paginate($paginate);
							}
							$lastPage = $clave->lastPage();
							foreach ($clave as $c)
							{
								$tempArray['id']	= $c->idAccAcc;
								$tempArray['text']	= $c->account." - ".$c->description." (".$c->content.")";
								$result['results'][]= $tempArray;
							}
							break;
						case '7':	// Model: RequestModel							
							$clave = App\RequestModel::where('kind',8)
								->whereIn('status',[5,10,11,12,18])
								->where('idRequest',$request->params_data['extra']['user'])
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(32)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(32)->pluck('departament_id'))
								->get();
							$foliosRequest = [];
							foreach ($clave as $key => $value)
							{
								if(strpos(strtoupper($value->resource->first()->title), strtoupper(preg_replace("/\s+/", "%", $request->search))) !== false)
								{
									if($value->resource->first()->expensesRequest->count()==0)
									{
										$foliosRequest[] = $value->folio;
									}
									else
									{
										$flag = true;
										foreach ($value->resource->first()->expensesRequest as $expenses)
										{
											if($expenses->requestModel->status!=2 && $expenses->requestModel->status!=6 && $expenses->requestModel->status!=7 && $expenses->requestModel->status!=13)
											{
												$flag = false;
											}
										}
										if($flag)
										{
											$foliosRequest[] = $value->folio;
										}
									}
								}
							}
							$requests = App\RequestModel::whereIn('folio',$foliosRequest)
								->paginate($paginate);
							$lastPage = $requests->lastPage();
							foreach ($requests as $r)
							{
								$tempArray['id']	= $r->folio;
								$tempArray['text']	= isset($r->resource->first()->title) ? $r->folio." - ".$r->resource->first()->title : null;
								$result['results'][]= $tempArray;
							}
							break;
						case '8': // Model: 
						case '56': // App\CatWarehouseType (All)
							$clave = App\CatWarehouseType::where('description', 'LIKE', '%'.preg_replace("/\s+/", "%", $request->search).'%')
								->where(function($query) use ($request)
								{
									if ($request->model == '8')
									{
										$query->select(['id','description'])
										->where('requisition_types_id',$request->params_data['id'])
										->where('status',1);
									}
								})
								->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach ($clave as $c)
							{
								$tempArray['id']	= $c->id;
								$tempArray['text']	= $c->description;
								$result['results'][]= $tempArray;
							}
							break;
						case '13':	// Users:
						case '36':	// Users (status only ACTIVE):
							$users =	App\User::orderName()->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->where("sys_user",1)
								->where(function($q) use($request)
								{
									if($request->model == '13')
									{
										$q->whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL']);
									}
									if($request->model == '36')
									{
										$q->whereIn('status',['ACTIVE']);
									}	
								})
								->paginate($paginate);
							$lastPage = $users->lastPage();
							foreach ($users as $user)
							{
								$tempArray['id']	= $user->id;
								$tempArray['text']	= $user->fullname();
								$result['results'][]= $tempArray;
							}
							break;
						case '14': // All Projects, Status 1
						case '17': // Projects that user have charged in module status [1,2]
						case '21': // Projects status [1,2]
						case '24': // All Projects
						case '41': // Projects status 1, user permissions
						case '49': // Projects charged to user
							$projects = App\Project::orderName()
								->where('proyectName','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->where(function($q) use($request)
								{
									if(in_array($request->model,[14, 17, 21, 41]))
									{
										$q->where(function($q) use($request)
										{
											$q->where('status',1);
											if(in_array($request->model,[17, 21]))
											{
												$q->orWhere('status',2);
											}
										});
									}
									if(in_array($request->model, [17, 41, 49]))
									{
										$q->whereIn('idproyect',Auth::user()->inChargeProject($request->params_data['extra']['option_id'])->pluck('project_id'));
									}
								})->paginate($paginate);
								$lastPage = $projects->lastPage();
							foreach ($projects as $p)
							{
								$tempArray['id']   = $p->idproyect;
								$tempArray['text'] = $p->proyectName;
								if(App\CatCodeWBS::where('project_id', $p->idproyect)->count() > 0)
								{
									$tempArray['flagWBS'] = true;
								}
								else
								{
									unset($tempArray['flagWBS']);
								}
								$result['results'][]= $tempArray;
							}
							break;
						case '15': // Model: CatCodeEDT
						case '52': // Model: CatCodeEDT depends several WBS
							$codes = App\CatCodeEDT::whereRaw('CONCAT(cat_code_e_d_ts.code," (",cat_code_e_d_ts.description,")") LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
								->where(function ($q) use ($request)
								{
									if($request->model == "15")
									{
										$q->where('codewbs_id', $request->params_data['id']);
									}
									if($request->model == "52")
									{
										$q->whereIn('codewbs_id', $request->params_data['extra']['extra']);
									}
								})
								->orderBy('description', 'asc')
								->paginate($paginate);
							$lastPage = $codes->lastPage();
							foreach ($codes as $c)
							{
								$tempArray['id']     = $c->id;
								$tempArray['text']   = $c->code." (".$c->description.")";
								$result['results'][] = $tempArray;
							}
							break;
						case '19': // Model: Label (all labels)
							$labels = App\Label::orderBy('description','asc')
								->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $labels->lastPage();
							foreach ($labels as $label)
							{
								$tempArray['id']	= $label->idlabels;
								$tempArray['text']	= $label->description;
								$result['results'][]= $tempArray;
							}
							break;
						case '20': // Model: RealEmployee
							$employees = App\RealEmployee::where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orderName()
								->paginate($paginate);
							$lastPage = $employees->lastPage();
							foreach ($employees as $employee)
							{
								$tempArray['id']		= $employee->id;
								$tempArray['text']		= $employee->orderedName();
								$result['results'][]	= $tempArray;
							}
							break;
						case '25': // Model: CatUnity
							$clave = App\CatUnity::where('keyUnit','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orWhere('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach ($clave as $c)
							{
								$tempArray['id']		= $c->keyUnit;
								$tempArray['text']		= $c->keyUnit.' '.$c->name;
								$result['results'][]	= $tempArray;
							}
							break;
						case '26': //Model: CatProdServ
							$clave = App\CatProdServ::where('keyProdServ','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orWhere('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach($clave as $c)
							{
								$tempArray['id']		= $c->keyProdServ;
								$tempArray['text']		= $c->keyProdServ.' '.$c->description;
								$result['results'][]	= $tempArray;
							}
							break;
						case '27': //Model: Banks
							$clave = App\Banks::orderName()->where('description', 'like', '%'.preg_replace("/\s+/", "%", $request->search).'%')->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach($clave as $bank)
							{
								$tempArray['id']		= $bank->idBanks;
								$tempArray['text']		= $bank->description;
								$result['results'][]	= $tempArray;
							}
							break;
						case '28': //Model: CatBank
							$clave    = App\CatBank::orderName()->where('description','like', '%'.preg_replace("/\s+/", "%", $request->search).'%')->paginate($paginate);
							$lastPage = $clave->lastPage();
							foreach ($clave as $b) 
							{
								$tempArray['id']		= $b->c_bank;
								$tempArray['text']		= $b->description;
								$result['results'][]	= $tempArray;
							}
							break;
						case '29': //Model: RequestModel kind(1, 3, 9) (Folios)
							$folios       = isset($request->params_data['id']) ? $request->params_data['id'] : null;
							$enterpriseid = $request->params_data['id'];
							if (isset($request->folios))
							{
								$requests = App\RequestModel::whereIn('kind',[1,3,9])
									->where('status',11)
									->whereNotIn('folio',$folios)
									->where(function($query) use($enterpriseid)
									{
										if ($enterpriseid != '') 
										{
											$query->where('idEnterpriseR',$enterpriseid);
										}
									})
									->paginate($paginate);
							}
							else
							{
								$requests = App\RequestModel::whereIn('kind',[1,3,9])
									->where('status',11)
									->where(function($query) use($enterpriseid)
									{
										if ($enterpriseid != '') 
										{
											$query->where('idEnterpriseR',$enterpriseid);
										}
									})
									->paginate($paginate);
							}
							$lastPage = $requests->lastPage();
							if (count($requests)>0) 
							{
								foreach ($requests as $r) 
								{
									$title = '';
									switch ($r->kind) 
									{
										case 1:
											$title = $r->purchases->first()->title != '' ? $r->purchases->first()->title : 'Sin título';
											break;
										case 3:
											$title = $r->expenses->first()->title != '' ? $r->expenses->first()->title : 'Sin título';
											break;
										case 9:
											$title = $r->refunds->first()->title != '' ? $r->refunds->first()->title : 'Sin título';
											break;
										default:
											# code...
											break;
									}
									$mainTitle = 'Solicitud de '.$r->requestkind->kind.' #'.$r->folio.' '.$title;
									if(strpos(strtoupper($mainTitle), strtoupper(preg_replace("/\s+/", "%", $request->search))) !== false)
									{
										$tempArray['id'] = $r->folio;
										$tempArray['text'] = $mainTitle;
										$result['results'][] = $tempArray;
									}
								}
							}
							
							break;
						case '30': //Model: WBS of Contract
							$wbs = DB::table('w_b_s_contract')
								->where('code_wbs', 'like', '%'.preg_replace("/\s+/", "%", $request->search).'%')
								->where('contract_id',$request->params_data['id'])
								->leftJoin('cat_code_w_bs', 'cat_code_w_bs.id', 'w_b_s_contract.wbs_id')
								->paginate($paginate);
							$lastPage = $wbs->lastPage();
							foreach ($wbs as $w) 
							{
								$tempArray['id']		= $w->id;
								$tempArray['text']		= $w->code_wbs;
								$result['results'][]	= $tempArray;
							}
							break;
						case '31': //Model: State
							$states   = App\State::orderName()->where('description','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')->paginate($paginate);
							$lastPage = $states->lastPage(); 
							foreach($states as $s)
							{
								$tempArray['id']		= $s->idstate;
								$tempArray['text']		= $s->description;
								$result['results'][]	= $tempArray;
							}
							break;
						case '34': // Model Contract with depends Project
							$contracts = App\Contract::where('project_id', $request->params_data['id'])
								->whereRaw('CONCAT(contracts.number," - ",contracts.name) LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
								->orderBy('name','asc')
								->paginate($paginate);
							$lastPage = $contracts->lastPage();
							foreach ($contracts as $contract)
							{
								$tempArray['id']     = $contract->id;
								$tempArray['text']   = $contract->number." - ".$contract->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '35': // Model Disciplines all records
							$disciplines = App\CatDiscipline::whereRaw('CONCAT(cat_disciplines.indicator," - ",cat_disciplines.name) LIKE "%'.preg_replace("/\s+/", "%", $request->search).'%"')
								->paginate($paginate);
							$lastPage = $disciplines->lastPage();
							foreach ($disciplines as $discipline)
							{
								$tempArray['id']	 = $discipline->id;
								$tempArray['text']	 = $discipline->indicator." - ".$discipline->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '37': // Model CatRequestRequisition
							$applicants = App\CatRequestRequisition::orderBy('name','ASC')
								->where('name', 'LIKE', '%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $applicants->lastPage();
							foreach ($applicants as $applicant)
							{
								$tempArray['id']     = $applicant->id;
								$tempArray['text']   = $applicant->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '38': // Model place
							$places = App\Place::orderName()
								->where('status',1)
								->where('place', 'LIKE', '%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $places->lastPage();
							foreach ($places as $place)
							{
								$tempArray['id']     = $place->id;
								$tempArray['text']   = $place->place;
								$result['results'][] = $tempArray;
							}
							break;
						case '39': // Model Subdepartment
							$subdepartments = App\Subdepartment::orderName()
								->where('status',1)
								->where('name', 'LIKE', '%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $subdepartments->lastPage();
							foreach ($subdepartments as $subdepartment)
							{
								$tempArray['id']     = $subdepartment->id;
								$tempArray['text']   = $subdepartment->name;
								$result['results'][] = $tempArray;
							}
							break;
						case'40': // Model BankAccount (All)
							$banksAccounts = App\BankAccount::where('alias','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $banksAccounts->lastPage();
							foreach ($banksAccounts as $bankAccount)
							{
								$tempArray['id']     = $bankAccount->id;
								$tempArray['text']   = $bankAccount->alias." - ".$bankAccount->account;
								$result['results'][] = $tempArray;
							}
							break;
						case '42': // Model CatContractItem depends Contract
							$contractsItems	= App\CatContractItem::where('contract_id', $request->params_data['id'])
								->where('contract_item','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $contractsItems->lastPage();
							foreach ($contractsItems as $contract)
							{
								$tempArray['id']     = $contract->id;
								$tempArray['text']   = $contract->contract_item;
								$result['results'][] = $tempArray;
							}
							break;
						case '43': // Model CatContractor depends WBS and Contract
							$contractors = App\Contractor::where('wbs_id', $request->params_data['id'])
								->where('contract_id', $request->params_data['extra']['extra'])
								->where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orderBy('name','asc')
								->paginate($paginate);
							$lastPage = $contractors->lastPage();
							foreach ($contractors as $contractor)
							{
								$tempArray['id']     = $contractor->id;
								$tempArray['text']   = $contractor->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '44': // Model Blueprints depends WBS and Contract
							$blueprints = App\Blueprints::where('wbs_id', $request->params_data['id'])
								->where('contract_id', $request->params_data['extra']['extra'])
								->where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orderBy('name','asc')
								->paginate($paginate);
							$lastPage = $blueprints->lastPage();
							foreach ($blueprints as $blueprint)
							{
								$tempArray['id']     = $blueprint->id;
								$tempArray['text']   = $blueprint->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '45': // Model CatMachinery all records
							$machineries = App\CatMachinery::where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $machineries->lastPage();
							foreach ($machineries as $machinery)
							{
								$tempArray['id']     = $machinery->id;
								$tempArray['text']   = $machinery->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '46': // Model CatIndustrialStaff all records
							$staffs = App\CatIndustrialStaff::where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $staffs->lastPage();
							foreach ($staffs as $staff)
							{
								$tempArray['id']     = $staff->id;
								$tempArray['text']   = $staff->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '47': // Model EmployerRegister depends enterprise
							$employeer_registers = App\EmployerRegister::where('enterprise_id',$request->params_data['id'])
								->where('employer_register','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							$lastPage = $employeer_registers->lastPage();
							foreach ($employeer_registers as $value) 
							{
								$tempArray['id']     = $value->employer_register;
								$tempArray['text']   = $value->employer_register;
								$result['results'][] = $tempArray;
							}
						break;
						case '48':	// Model: RequestModel (nomina employees)
							$requests = App\RequestModel::find($request->params_data['extra']['id']);
							$requests = $requests->nominasReal->first()->nominaEmployee;
							foreach($requests as $n)
							{
								if(strpos(strtoupper($n->employee->first()->fullName()), strtoupper(preg_replace("/\s+/", "%", $request->search))) !== false)
								{
									$tempArray['id']	 = $n->idnominaEmployee;
									$tempArray['text']	 = $n->employee->first()->fullName();
									$result['results'][] = $tempArray;
								}
							}
							break;
						case '53':	// Model: CatRequisitionName
							$requests = App\CatRequisitionName::where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->paginate($paginate);
							foreach($requests as $n)
							{
								$tempArray['id']	 = $n->id;
								$tempArray['text']	 = $n->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '50':	// Model: Contractors
							$contractors = App\Contractor::where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orderBy('name','asc')
								->paginate($paginate);
							$lastPage = $contractors->lastPage();
							foreach($contractors as $c)
							{
								$tempArray['id']	 = $c->id;
								$tempArray['text']	 = $c->name;
								$result['results'][] = $tempArray;
							}
							break;
						case '55':	// Model: CatAuditors
							$auditors = App\CatAuditor::where('name','LIKE','%'.preg_replace("/\s+/", "%", $request->search).'%')
								->orderBy('name','asc')
								->paginate($paginate);
							$lastPage = $auditors->lastPage();
							foreach($auditors as $a)
							{
								$tempArray['id']	 = $a->name;
								$tempArray['text']	 = $a->name;
								$result['results'][] = $tempArray;
							}
							break;

						case '60':	// Model: GroupingAccount
							$groupsAccount = App\GroupingAccount::where('idEnterprise',$request->params_data['id'])
											->where('name','LIKE', '%'.preg_replace("/\s+/", "%", $request->search).'%')
											->orderBy('name')
											->paginate($paginate);

							$lastPage = $groupsAccount->lastPage();
							foreach($groupsAccount as $group)
							{
								$tempArray['id']	 = $group->id;
								$tempArray['text']	 = $group->name;
								$result['results'][] = $tempArray;
							}
							break;
						default:
							break;
						// Último case 60

					}
					if($request->page < $lastPage)
					{
						$result['pagination'] = ['more' => true];
					}
				}
				catch(\Exception $e)
				{
					$result['error'] = [$e->getMessage()];
				}
			}
			return Response($result);
		}
	}
}