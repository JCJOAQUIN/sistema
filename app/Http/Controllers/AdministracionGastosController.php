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
use Ilovepdf\CompressTask;
use Excel;
use Illuminate\Support\Facades\View;

use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionGastosController extends Controller
{
	private $module_id = 26;

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
	
	public function create()
	{
		if(Auth::user()->module->where('id',32)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			return view('administracion.gastos.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 32
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',32)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',33)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',33)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data		= App\Module::find($this->module_id);
			$requests	= App\RequestModel::where('kind',3)
						->whereIn('status',[5, 6, 7,10,11,12,13])
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
				return view('administracion.gastos.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 32,
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
		if(Auth::user()->module->where('id',32)->count()>0)
		{
			$t_request                  = new App\RequestModel();
			$t_request->kind            = 3;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 3;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			$t_request->idProject       = $request->project_id;
			$t_request->idElaborate     = Auth::user()->id;
			$t_request->code_wbs       	= (isset($request->wbs_id)) ? $request->wbs_id : null;
			$t_request->code_edt       	= (isset($request->edt_id)) ? $request->edt_id : null;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;

			$t_expenses                 = new App\Expenses();
			$t_expenses->idFolio        = $folio;
			$t_expenses->idKind         = $kind;
			$t_expenses->title 			= $request->title;
			$t_expenses->datetitle 		= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_expenses->resourceId     = $request->resources_id;
			$t_expenses->reference      = $request->reference;
			$t_expenses->currency      	= $request->currency;
			if ($request->method == 1) 
			{
				$t_expenses->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_expenses->idEmployee		= null;
			}
			$t_expenses->idpaymentMethod  = $request->method;
			$t_expenses->idUsers          = $request->user_id;
			$t_expenses->save();
			$expense                    = $t_expenses->idExpenses;
			$countAmount                = count($request->t_amount);
			$ivaParam                   = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
			$ivaParam2                  = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
			
			$total = $taxes = 0;

			for ($i=0; $i < $countAmount; $i++)
			{
				$ivaCalc                    = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;

				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;
				$taxesConcept 		= 0;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxesConcept += $request->$tamountadditional[$d];
						}
					}
				}
				

				$t_detailExpenses				= new App\ExpensesDetail();
				$t_detailExpenses->idExpenses	= $expense;
				$t_detailExpenses->document		= $request->t_document[$i];
				$t_detailExpenses->concept		= $request->t_concept[$i];
				$t_detailExpenses->amount		= $request->t_amount[$i];
				$t_detailExpenses->idAccount	= $request->t_account[$i];
				$t_detailExpenses->taxPayment	= $request->t_fiscal[$i]== "si" ? 1 : 0;
				$t_detailExpenses->tax			= $ivaCalc;
				$t_detailExpenses->sAmount		= $request->t_amount[$i]+$ivaCalc+$taxesConcept;
				$t_detailExpenses->typeTax		= $request->tivakind[$i];
				if ($request->t_idresourcedetail[$i] != "x") 
				{
					$t_detailExpenses->idresourcedetail = $request->t_idresourcedetail[$i];
				}
				$t_detailExpenses->save();
				$total                      += $request->t_amount[$i]+$ivaCalc;
				$idRD                       = $t_detailExpenses->idExpensesDetail;

				if ($request->t_idresourcedetail[0] != "") 
				{
					if ($request->t_idresourcedetail[$i] != "x") 
					{
						$t_resourcedetail               = App\ResourceDetail::find($request->t_idresourcedetail[$i]);
						$t_resourcedetail->statusRefund = 1;
						$t_resourcedetail->save();
					}
				}
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
						$doc			= $request->$tempPath;
						$new			= $request->$tempNew;
						$t_documents	= new App\ExpensesDocuments();

						if ($new[$d]==1)
						{
							$new_file_name = Files::rename($doc[$d],$folio);
							$t_documents->path	= $new_file_name;
						}
						else
						{
							$extention		= explode('.', $doc[$d]);
							$destinityName	= 'AdG'.round(microtime(true) * 1000).'_expensesDoc.'.$extention[1];
							$destinity		= '/docs/expenses/'.$destinityName;
							$origin			= '/docs/expenses/'.$doc[$d];
							\Storage::disk('public')->copy($origin,$destinity);
							$new_file_name = Files::rename($destinityName,$folio);
							$t_documents->path	= $new_file_name;
						}

						$t_documents->idExpensesDetail	= $idRD;
						$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
						$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
						$t_documents->amount			= $request->$tempAmount[$d];
						$t_documents->timepath			= $request->$tempTimepath[$d];
						$t_documents->date				= Carbon::createFromFormat('d-m-Y',$request->$tempDatepath[$d])->format('Y-m-d');
						$t_documents->name				= $request->$tempNameDoc[$d];
						$t_documents->users_id			= Auth::user()->id;
						$t_documents->save();
					}
				}

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 						+= $request->$tamountadditional[$d];
							$t_taxes 					= new App\TaxesExpenses();
							$t_taxes->name 				= $request->$tnameamount[$d];
							$t_taxes->amount 			= $request->$tamountadditional[$d];
							$t_taxes->idExpensesDetail 	= $idRD;
							$t_taxes->save();
						}
					}
				}
			}
			$t_expenses->reembolso    = $request->reembolso;
			$t_expenses->reintegro    = $request->reintegro;
			$t_expenses->total = $total+$taxes;
			$t_expenses->save();

			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 34);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',34);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',34);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails = App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',34)
						->where('user_has_department.departament_id',$request->department_id)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			$user   =  App\User::find($request->user_id);

			if ($emails != "") 
			{
				try
				{
					foreach ($emails as $email)
					{
						$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to             = $email->email;
						$kind           = "Comprobación de Gasto";
						$status         = "Revisar";
						$date           = Carbon::now();
						$url            = route('expenses.review.edit',['id'=>$folio]);
						$subject        = "Solicitud por Revisar";
						$requestUser    = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/expenses')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',33)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',33)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',33)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;
			$resource_id 	= $request->resource_id;

			$requests       = App\RequestModel::where('kind',3)
								->where(function($q) 
								{
									$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(33)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
								})
								->where(function ($q) 
								{
									$q->whereIn('idDepartment',Auth::user()->inChargeDep(33)->pluck('departament_id'))->orWhereNull('idDepartment');
								})
								->where(function ($q) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
									}
								})
								->where(function ($query) use ($name, $mindate, $maxdate, $folio, $status, $enterpriseid, $resource_id)
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
										$query->whereHas('requestUser', function($qUser) use($name)
										{
											$qUser->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
									if ($resource_id != "") 
									{
										$query->whereHas('expenses',function($query) use ($resource_id)
										{
											$query->where('resourceId',$resource_id);
										});
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);

			return view('administracion.gastos.busqueda',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 33,
					'requests'  => $requests,
					'folio'     => $folio,
					'name'      => $name,
					'status'    => $status,
					'mindate'   => $request->mindate,
					'maxdate'   => $request->maxdate,
					'enterpriseid' => $enterpriseid,
					'resource_id' => $resource_id
				]);
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
			$data                       = App\Module::find($this->module_id);
			$t_request                  = new App\RequestModel();
			$t_request->kind            = "3";
			$t_request->fDate           = Carbon::now();
			$t_request->status          = "2";
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			$t_request->idProject       = $request->project_id;
			$t_request->idElaborate     = Auth::user()->id;
			$t_request->code_wbs       	= (isset($request->wbs_id)) ? $request->wbs_id : null;
			$t_request->code_edt       	= (isset($request->edt_id)) ? $request->edt_id : null;
			$t_request->save();
			$folio                      = $t_request->folio;
			$kind                       = $t_request->kind;
			$t_expenses                   = new App\Expenses();
			$t_expenses->idFolio          = $folio;
			$t_expenses->idKind           = $kind;
			$t_expenses->title 			  = $request->title;
			$t_expenses->datetitle 		  = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_expenses->resourceId       = $request->resources_id;
			$t_expenses->reference        = $request->reference;
			$t_expenses->currency      	= $request->currency;
			if ($request->method == 1) 
			{
				$t_expenses->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_expenses->idEmployee		= null;
			}
			$t_expenses->idpaymentMethod  = $request->method;
			$t_expenses->idUsers          = $request->user_id;
			$t_expenses->save();
			$expense                     = $t_expenses->idExpenses;
			
			$total = $taxes = 0;
			if($request->t_amount != "")
			{
				$countAmount    = count($request->t_amount);
				$ivaParam       = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
				$ivaParam2      = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
				for ($i=0; $i < $countAmount; $i++)
				{
					$ivaCalc            = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;

					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					$taxesConcept 		= 0;

					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxesConcept += $request->$tamountadditional[$d];
							}
						}
					}

					$t_detailExpenses             	= new App\ExpensesDetail();
					$t_detailExpenses->idExpenses   = $expense;
					$t_detailExpenses->document   	= $request->t_document[$i];
					$t_detailExpenses->concept    	= $request->t_concept[$i];
					$t_detailExpenses->amount     	= $request->t_amount[$i];
					$t_detailExpenses->idAccount  	= $request->t_account[$i];
					$t_detailExpenses->taxPayment 	= $request->t_fiscal[$i]== "si" ? 1 : 0;
					$t_detailExpenses->tax        	= $ivaCalc;
					$t_detailExpenses->sAmount    	= $request->t_amount[$i]+$ivaCalc+$taxesConcept;
					$t_detailExpenses->typeTax		= $request->tivakind[$i];
					if ($request->t_idresourcedetail[$i] != "x") 
					{
						$t_detailExpenses->idresourcedetail = $request->t_idresourcedetail[$i];
					}

					$t_detailExpenses->save();
					$total                      += $request->t_amount[$i]+$ivaCalc;
					$idRD                       = $t_detailExpenses->idExpensesDetail;

					if ($request->t_idresourcedetail[0] != "") 
					{
						if ($request->t_idresourcedetail[$i] != "x") 
						{
							$t_resourcedetail               = App\ResourceDetail::find($request->t_idresourcedetail[$i]);
							$t_resourcedetail->statusRefund = 1;
							$t_resourcedetail->save();
						}
					}

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
							$doc			= $request->$tempPath;
							$new			= $request->$tempNew;
							$t_documents	= new App\ExpensesDocuments();

							if ($new[$d]==1)
							{
								$new_file_name = Files::rename($doc[$d],$folio);
								$t_documents->path	= $new_file_name;
							}
							else
							{
								$extention		= explode('.', $doc[$d]);
								$destinityName	= 'AdG'.round(microtime(true) * 1000).'_expensesDoc.'.$extention[1];
								$destinity		= '/docs/expenses/'.$destinityName;
								$origin			= '/docs/expenses/'.$doc[$d];
								\Storage::disk('public')->copy($origin,$destinity);
								$new_file_name = Files::rename($destinityName,$folio);
								$t_documents->path	= $new_file_name;
							}

							$t_documents->idExpensesDetail	= $idRD;
							$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
							$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
							$t_documents->amount			= $request->$tempAmount[$d];
							$t_documents->timepath			= $request->$tempTimepath[$d];
							$t_documents->date				= Carbon::createFromFormat('d-m-Y',$request->$tempDatepath[$d])->format('Y-m-d');
							$t_documents->name				= $request->$tempNameDoc[$d];
							$t_documents->users_id			= Auth::user()->id;
							$t_documents->save();
						}
					}
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$taxes 						+= $request->$tamountadditional[$d];
								$t_taxes 					= new App\TaxesExpenses();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idExpensesDetail 	= $idRD;
								$t_taxes->save();
							}
						}
					}
				}
			}
			$t_expenses->reembolso	= $request->reembolso;
			$t_expenses->reintegro 	= $request->reintegro;
			$t_expenses->total 		= $total+$taxes;
			$t_expenses->save();
			$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('expenses.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',33)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',33)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',33)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data           = App\Module::find($this->module_id); 
			$enterprises    = App\Enterprise::where('status','ACTIVE')->get(); 
			$areas          = App\Area::where('status','ACTIVE')->get(); 
			$departments    = App\Department::where('status','ACTIVE')->get(); 
			$projects       = App\Project::all();
			$request 		= App\RequestModel::where('kind',3)
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
			if ($request != "") 
			{
				return view('administracion.gastos.seguimiento',
					[
						'id'            => $data['father'],
						'title'         => $data['name'],
						'details'       => $data['details'],
						'child_id'      => $this->module_id,
						'option_id'     => 33,
						'projects'      => $projects, 
						'enterprises'   => $enterprises,
						'areas'         => $areas,
						'departments'   => $departments,
						'request'       => $request,
						'iva'           => $iva,
						'labels'        => $labels
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
			$data                       = App\Module::find($this->module_id);
			$t_request                  = App\RequestModel::find($id);
			$t_request->kind            = 3;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 3;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			$t_request->idProject       = $request->project_id;
			$t_request->save();
			$Expenses = App\Expenses::where('idFolio',$t_request->folio)
				->where('idKind',$t_request->kind)
				->get();
			$folio = $t_request->folio;
			foreach ($Expenses as $key => $value)
			{
				$idExpenses = $value->idExpenses;
			}
			$t_expenses               = App\Expenses::find($idExpenses);
			$t_expenses->title 		  = $request->title;
			$t_expenses->datetitle 	  = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_expenses->resourceId   = $request->resources_id;
			$t_expenses->reembolso    = $request->reembolso;
			$t_expenses->reintegro    = $request->reintegro;
			$t_expenses->reference    = $request->reference;
			$t_expenses->currency      	= $request->currency;
			if ($request->method == 1) 
			{
				$t_expenses->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_expenses->idEmployee		= null;
			}
			$t_expenses->idpaymentMethod  	= $request->method;
			$t_expenses->idUsers      		= $request->user_id;
			$total                  		= $t_expenses->total;
			$taxes 							= 0;

			if(isset($request->delete))
			{
				if ($request->delete[0] != "")
				{
					for ($i=0; $i < count($request->delete); $i++) 
					{ 
						if (App\ExpensesDetail::where('idExpensesDetail',$request->delete[$i])->count()>0) 
						{
							$filesDeleted = App\ExpensesDocuments::where('idExpensesDetail',$request->delete[$i])->get();
							foreach ($filesDeleted as $k => $v)
							{
								\Storage::disk('public')->delete('/docs/expenses/'.$v->path);
							}
							$del2= App\ExpensesDocuments::where('idExpensesDetail',$request->delete[$i])->delete();
							$total -=App\ExpensesDetail::find($request->delete[$i])->sAmount;
							$del1 = App\ExpensesDetail::where('idExpensesDetail',$request->delete[$i])->delete();   
						}
					}
				}
			}
			
			$ivaParam                   = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
			$ivaParam2                  = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;

			if (isset($request->t_amount)) 
			{
				$countAmount                = count($request->t_amount);
				for ($i=0; $i < $countAmount; $i++)
				{
					if ($request->idRDe[$i] == "x") 
					{
						$ivaCalc            = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;

						$tamountadditional 	= 'tamountadditional'.$i;
						$tnameamount 		= 'tnameamount'.$i;
						$taxesConcept 		= 0;

						if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
						{
							for ($d=0; $d < count($request->$tamountadditional); $d++) 
							{ 
								if ($request->$tamountadditional[$d] != "") 
								{
									$taxesConcept += $request->$tamountadditional[$d];
								}
							}
						}

						$t_detailExpenses             = new App\ExpensesDetail();
						$t_detailExpenses->idExpenses = $idExpenses;
						$t_detailExpenses->document   = $request->t_document[$i];
						$t_detailExpenses->concept    = $request->t_concept[$i];
						$t_detailExpenses->amount     = $request->t_amount[$i];
						$t_detailExpenses->idAccount  = $request->t_account[$i];
						$t_detailExpenses->taxPayment = $request->t_fiscal[$i] == "si" ? 1 : 0;
						$t_detailExpenses->tax        = $ivaCalc;
						$t_detailExpenses->sAmount    = $request->t_amount[$i]+$ivaCalc+$taxesConcept;
						$t_detailExpenses->typeTax		= $request->tivakind[$i];

						if ($request->t_idresourcedetail[$i] != "x") 
						{
							$t_detailExpenses->idresourcedetail = $request->t_idresourcedetail[$i];
						}
						$t_detailExpenses->save();
						$total                      += $request->t_amount[$i]+$ivaCalc;
						$idRD                       = $t_detailExpenses->idExpensesDetail;
						if ($request->t_idresourcedetail[0] != "") 
						{
							if ($request->t_idresourcedetail[$i] != "x") 
							{
								$t_resourcedetail               = App\ResourceDetail::find($request->t_idresourcedetail[$i]);
								$t_resourcedetail->statusRefund = 1;
								$t_resourcedetail->save();
							}
						}

						$tempPath			= 't_path'.$i;
						$tempNew			= 't_new'.$i;
						$tempFiscalFolio	= 't_fiscal_folio'.$i;
						$tempTicketNumber	= 't_ticket_number'.$i;
						$tempAmount			= 't_amount'.$i;
						$tempTimepath		= 't_timepath'.$i;
						$tempDatepath		= 't_datepath'.$i;
						$tempNameDoc		= 't_name_doc'.$i;
						if (isset($request->$tempPath)) 
						{
							for ($d=0; $d < count($request->$tempPath); $d++) 
							{
								$doc							= $request->$tempPath;
								$t_documents					= new App\ExpensesDocuments();
								$new_file_name = Files::rename($doc[$d],$folio);
								$t_documents->path				= $new_file_name;
								$t_documents->idExpensesDetail	= $idRD;
								$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
								$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
								$t_documents->amount			= $request->$tempAmount[$d];
								$t_documents->timepath			= $request->$tempTimepath[$d];
								$t_documents->date				= Carbon::createFromFormat('d-m-Y',$request->$tempDatepath[$d])->format('Y-m-d');
								$t_documents->name				= $request->$tempNameDoc[$d];
								$t_documents->users_id			= Auth::user()->id;
								$t_documents->save();
							}
						}

						if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
						{
							for ($d=0; $d < count($request->$tamountadditional); $d++) 
							{ 
								if ($request->$tamountadditional[$d] != "") 
								{
									$taxes 						+= $request->$tamountadditional[$d];
									$t_taxes 					= new App\TaxesExpenses();
									$t_taxes->name 				= $request->$tnameamount[$d];
									$t_taxes->amount 			= $request->$tamountadditional[$d];
									$t_taxes->idExpensesDetail 	= $idRD;
									$t_taxes->save();
								}
							}
						}
					}
				}
			}
			$t_expenses->total        = $total+$taxes;
			$t_expenses->reembolso    = $request->reembolso;
			$t_expenses->reintegro    = $request->reintegro;
			$t_expenses->save();
			
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 34);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',34);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',34);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails = App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',34)
						->where('user_has_department.departament_id',$request->department_id)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			$user   =  App\User::find($request->user_id);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to             = $email->email;
						$kind           = "Comprobación de Gasto";
						$status         = "Revisar";
						$date           = Carbon::now();
						$url            = route('expenses.review.edit',['id'=>$id]);
						$subject        = "Solicitud por Revisar";
						$requestUser    = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/expenses')->with('alert',$alert);
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
			$data                       = App\Module::find($this->module_id);
			$t_request                  = App\RequestModel::find($id);
			$t_request->kind            = 3;
			$t_request->fDate           = Carbon::now();
			$t_request->status          = 2;
			$t_request->idEnterprise    = $request->enterprise_id;
			$t_request->idArea          = $request->area_id;
			$t_request->idDepartment    = $request->department_id;
			$t_request->idRequest       = $request->user_id;
			$t_request->idProject       = $request->project_id;
			$t_request->save();
			$Expenses                     = App\Expenses::where('idFolio',$t_request->folio)
											->where('idKind',$t_request->kind)
											->get();
			$folio = $t_request->folio;
			foreach ($Expenses as $key => $value)
			{
				$idExpenses = $value->idExpenses;
			}
			$t_expenses               = App\Expenses::find($idExpenses);
			$t_expenses->title 		  = $request->title;
			$t_expenses->datetitle 	  = $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_expenses->resourceId   = $request->resources_id;
			$t_expenses->reembolso    = $request->reembolso;
			$t_expenses->reintegro    = $request->reintegro;
			$t_expenses->reference    = $request->reference;
			$t_expenses->currency      	= $request->currency;
			if ($request->method == 1) 
			{
				$t_expenses->idEmployee		= $request->idEmployee;
			}
			else
			{
				$t_expenses->idEmployee		= null;
			}
			$t_expenses->idpaymentMethod  	= $request->method;
			$t_expenses->idUsers      		= $request->user_id;
			$total                  		= $t_expenses->total;
			$taxes 							= 0;
			if(isset($request->delete))
			{

				if ($request->delete[0] != "")
				{
					for ($i=0; $i < count($request->delete); $i++) 
					{ 
						if (App\ExpensesDetail::where('idExpensesDetail',$request->delete[$i])->count()>0) 
						{
							$filesDeleted = App\ExpensesDocuments::where('idExpensesDetail',$request->delete[$i])->get();
							foreach ($filesDeleted as $k => $v)
							{
								\Storage::disk('public')->delete('/docs/expenses/'.$v->path);
							}
							$del2= App\ExpensesDocuments::where('idExpensesDetail',$request->delete[$i])->delete();
							$total -=App\ExpensesDetail::find($request->delete[$i])->sAmount;
							$del1 = App\ExpensesDetail::where('idExpensesDetail',$request->delete[$i])->delete();   
						}
					}
				}
			}

			if($request->t_amount != "")
			{
				$countAmount    = count($request->t_amount);
				$ivaParam       = App\Parameter::where('parameter_name','IVA')->first()->parameter_value;
				$ivaParam2      = App\Parameter::where('parameter_name','IVA2')->first()->parameter_value;
				for ($i=0; $i < $countAmount; $i++)
				{

					if ($request->idRDe[$i] == "x") 
					{
						$ivaCalc                    = $request->t_iva[$i] == "si" ? ($request->t_iva_kind[$i]=="a" ? ($request->t_amount[$i] * $ivaParam/100): ($request->t_amount[$i] * $ivaParam2/100)) : 0;

						$tamountadditional 	= 'tamountadditional'.$i;
						$tnameamount 		= 'tnameamount'.$i;
						$taxesConcept 		= 0;

						if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
						{
							for ($d=0; $d < count($request->$tamountadditional); $d++) 
							{ 
								if ($request->$tamountadditional[$d] != "") 
								{
									$taxesConcept += $request->$tamountadditional[$d];
								}
							}
						}

						$t_detailExpenses             = new App\ExpensesDetail();
						$t_detailExpenses->idExpenses = $idExpenses;
						$t_detailExpenses->document   = $request->t_document[$i];
						$t_detailExpenses->concept    = $request->t_concept[$i];
						$t_detailExpenses->amount     = $request->t_amount[$i];
						$t_detailExpenses->idAccount  = $request->t_account[$i];
						$t_detailExpenses->taxPayment = $request->t_fiscal[$i]== "si" ? 1 : 0;
						$t_detailExpenses->tax        = $ivaCalc;
						$t_detailExpenses->sAmount    = $request->t_amount[$i]+$ivaCalc+$taxesConcept;
						$t_detailExpenses->typeTax		= $request->tivakind[$i];

						if ($request->t_idresourcedetail[$i] != "x") 
						{
							$t_detailExpenses->idresourcedetail = $request->t_idresourcedetail[$i];
						}

						$t_detailExpenses->save();

						if ($request->t_idresourcedetail[0] != "") 
						{
							if ($request->t_idresourcedetail[$i] != "x") 
							{
								$t_resourcedetail               = App\ResourceDetail::find($request->t_idresourcedetail[$i]);
								$t_resourcedetail->statusRefund = 1;
								$t_resourcedetail->save();
							}
						}

						$total                      += $request->t_amount[$i]+$ivaCalc;
						$idED                       = $t_detailExpenses->idExpensesDetail;
						
						$tempPath			= 't_path'.$i;
						$tempNew			= 't_new'.$i;
						$tempFiscalFolio	= 't_fiscal_folio'.$i;
						$tempTicketNumber	= 't_ticket_number'.$i;
						$tempAmount			= 't_amount'.$i;
						$tempTimepath		= 't_timepath'.$i;
						$tempDatepath		= 't_datepath'.$i;
						$tempNameDoc		= 't_name_doc'.$i;

						if (isset($request->$tempPath))
						{
							for ($d=0; $d < count($request->$tempPath); $d++) 
							{
								$doc							= $request->$tempPath;
								$t_documents					= new App\ExpensesDocuments();
								$new_file_name = Files::rename($doc[$d],$folio);
								$t_documents->path				= $new_file_name;
								$t_documents->idExpensesDetail	= $idED;
								$t_documents->fiscal_folio		= $request->$tempFiscalFolio[$d];
								$t_documents->ticket_number		= $request->$tempTicketNumber[$d];
								$t_documents->amount			= $request->$tempAmount[$d];
								$t_documents->timepath			= $request->$tempTimepath[$d];
								$t_documents->date				= Carbon::createFromFormat('d-m-Y',$request->$tempDatepath[$d])->format('Y-m-d');
								$t_documents->name				= $request->$tempNameDoc[$d];
								$t_documents->users_id			= Auth::user()->id;
								$t_documents->save();
							}
						}
						
						$tamountadditional 	= 'tamountadditional'.$i;
						$tnameamount 		= 'tnameamount'.$i;
						if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
						{
							for ($d=0; $d < count($request->$tamountadditional); $d++) 
							{ 
								if ($request->$tamountadditional[$d] != "") 
								{
									$taxes 						+= $request->$tamountadditional[$d];
									$t_taxes 					= new App\TaxesExpenses();
									$t_taxes->name 				= $request->$tnameamount[$d];
									$t_taxes->amount 			= $request->$tamountadditional[$d];
									$t_taxes->idExpensesDetail 	= $idED;
									$t_taxes->save();
								}
							}
						}   
					}
				
				}
			}
			$t_expenses->total = $total+$taxes;
			$t_expenses->save();
			$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('expenses.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',34)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests   = App\RequestModel::where('kind',3)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(34)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(34)->pluck('departament_id'))
								->where(function ($query) use ($name, $mindate, $maxdate, $folio, $enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterprise',$enterpriseid);
									}
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($qUser) use($name)
											{
												$qUser->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($qElaborate) use($name)
											{
												$qElaborate->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
				 view('administracion.gastos.revision',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 34,
						'requests'  => $requests,
						'folio'     => $folio,
						'name'      => $name,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(34), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',34)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::where('status','ACTIVE')->get();
			$areas          = App\Area::where('status','ACTIVE')->get();
			$departments    = App\Department::where('status','ACTIVE')->get();
			$labels         = App\Label::orderName()->get();
			$projects       = App\Project::all();
			$request        = App\RequestModel::where('kind',3)
							->where('status',3)
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(34)->pluck('enterprise_id'))
							->whereIn('idDepartment',Auth::user()->inChargeDep(34)->pluck('departament_id'))
							->find($id);
			if ($request != "") 
			{
				return view('administracion.gastos.revisioncambio',
					[
						'id'            => $data['father'],
						'title'         => $data['name'],
						'details'       => $data['details'],
						'child_id'      => $this->module_id,
						'option_id'     => 34,
						'enterprises'   => $enterprises,
						'areas'         => $areas,
						'departments'   => $departments,
						'request'       => $request,
						'labels'        => $labels,
						'projects'      => $projects
					]
				);
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/expenses/review')->with('alert',$alert);
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
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				if ($request->status == "4") 
				{
					for ($i=0; $i < count($request->t_idExpensesDetail); $i++) 
					{ 
						$t_expensesDetail 				= App\ExpensesDetail::find($request->t_idExpensesDetail[$i]);
						$t_expensesDetail->idAccountR 	= $request->t_idAccountR[$i];
						$t_expensesDetail->save();

						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelExpense = new App\LabelDetailExpenses();
								$labelExpense->idlabels = $request->$idLabelsAssign[$d];
								$labelExpense->idExpensesDetail = $request->t_idExpensesDetail[$i];
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
						$review->labels()->attach($request->idLabels,array('request_kind'=>'3'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 35);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',35);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',35);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails = App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',35)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					$user   = App\User::find($review->idRequest);

					if ($emails != "") 
					{
						try
						{
							foreach ($emails as $email) 
							{
								$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to             = $email->email;
								$kind           = "Comprobación de Gasto";
								$status         = "Autorizar";
								$date           = Carbon::now();
								$url            = route('expenses.authorization.edit',['id'=>$id]);
								$subject        = "Solicitud por Autorizar";
								$requestUser    = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
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
					$review                 = App\RequestModel::find($id);
					$review->status         = $request->status;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentR;
					$review->reviewDate     = Carbon::now();
					$review->save();
					if (isset($request->idresourcedetail)) 
					{
						for ($i=0; $i < count($request->idresourcedetail); $i++) 
						{
							$t_resourcedetail               = App\ResourceDetail::find($request->idresourcedetail[$i]);
							$t_resourcedetail->statusRefund = 0;
							$t_resourcedetail->save();
						}
					}
					
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
								$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to             = $email->email;
								$kind           = "Comprobación de Gasto";
								$status         = "RECHAZADA";
								$date           = Carbon::now();
								$url            = route('expenses.follow.edit',['id'=>$id]);
								$subject        = "Estado de Solicitud";
								$requestUser    = null;
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
			return searchRedirect(34, $alert, 'administration/expenses');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',35)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests   = App\RequestModel::where('kind',3)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(35)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(35)->pluck('departament_id'))
								->where(function ($query) use ($name, $mindate, $maxdate, $folio, $enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterpriseR',$enterpriseid);
									}
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($qUser) use($name)
											{
												$qUser->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
										$query->whereBetween('reviewDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
									}
								})
								->orderBy('reviewDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			return response(
				view('administracion.gastos.autorizacion',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 35,
						'requests'  => $requests,
						'folio'     => $folio,
						'name'      => $name,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(35), 2880
			);
		}
		else
		{
			return redirect('/'); 
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',35)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::where('status','ACTIVE')->get();
			$areas          = App\Area::where('status','ACTIVE')->get();
			$departments    = App\Department::where('status','ACTIVE')->get();
			$labels         = DB::table('request_has_labels')
								->join('labels','idLabels','labels_idlabels')
								->select('labels.description as descr')
								->where('request_has_labels.request_folio',$id)
								->get();
			$projects       = App\Project::all();
			$request        = App\RequestModel::where('kind',3)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(35)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(35)->pluck('departament_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.gastos.autorizacioncambio',
					[
						'id'            => $data['father'],
						'title'         => $data['name'],
						'details'       => $data['details'],
						'child_id'      => $this->module_id,
						'option_id'     => 35,
						'enterprises'   => $enterprises,
						'areas'         => $areas,
						'departments'   => $departments,
						'request'       => $request,
						'labels'        => $labels,
						'projects'      => $projects
					]
				);
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/expenses/authorization')->with('alert',$alert);
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
			$data                           = App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert	= "swal('','".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$authorize                      = App\RequestModel::find($id);
				$authorize->status              = $request->status;
				$authorize->idAuthorize         = Auth::user()->id;
				$authorize->authorizeComment    = $request->authorizeCommentA;
				$authorize->authorizeDate       = Carbon::now();
				/*if ($request->status ==  5 && $request->reintegro>0) 
				{
					$authorize->code 			 = rand(10000000,99999999);
				}*/
				$authorize->save();

				if ($request->status == 7) 
				{
					if (isset($request->idresourcedetail)) 
					{
						for ($i=0; $i < count($request->idresourcedetail); $i++) 
						{
							$t_resourcedetail               = App\ResourceDetail::find($request->idresourcedetail[$i]);
							$t_resourcedetail->statusRefund = 0;
							$t_resourcedetail->save();
						}
					}
				}
									
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
				
				$emailPay       = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
									->where('user_has_modules.module_id',90)
									->where('users.active',1)
									->where('users.notification',1)
									->get();
				$user           = App\User::find($authorize->idRequest);
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to             = $email->email;
							$kind           = "Comprobación de Gasto";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date           = Carbon::now();
							$url            = route('expenses.follow.edit',['id'=>$id]);
							$subject        = "Estado de Solicitud";
							$requestUser    = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert	= "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success');";
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
								$name           = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to             = $email->email;
								$kind           = "Comprobación de Gasto";
								$status         = "Pendiente";
								$date           = Carbon::now();
								$url            = route('payments.review.edit',['id'=>$id]);
								$subject        = "Estado de Solicitud";
								$requestUser    = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
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
			return searchRedirect(35, $alert, 'administration/expenses');
		}
	}

	public function getResourceDetail(Request $request)
	{
		if($request->ajax())
		{
			$request    = App\RequestModel::find($request->folio);
			$html 		= '';
			$body 		= [];
			$modelBody 	= [];
			$modelHead	=[
				["value" => "Concepto","show" => "true"],
				["value" => "Clasificación de gasto"],
				["value" => "Importe"],
				["value" => "Acciones"]
			];
			if ($request != "") 
			{
				foreach ($request as $r) 
				{
					foreach(App\Resource::where('idFolio',$r->folio)->get() as $resource)
					{
						foreach ($resource->resourceDetail->where('statusRefund',0) as $resourceDetail)
						{
							
							$body = [ "classEx" => "tr_detail",
								[
									"show" => "true",
									"content" =>
									[
										[
											"label" => $resourceDetail->concept
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx"		=> "idresourcedetail-table",
											"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idresourcedetail."\""
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx"		=> "concept-table",
											"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->concept."\""
										]
									]
								],
								[
									"content" =>
									[
										[
											"label" => $resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description
										],
										[
											"kind" 			=> "components.inputs.input-text",
											"classEx" 		=> "account-table",
											"attributeEx" 	=> "type=\"hidden\" value=\"".$resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description."\""
										],
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "accountid-table",
											"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idAccAccR."\""
										]
									]	 
								],
								[
									"content" =>
									[
										[
											"label" => number_format($resourceDetail->amount,2)
										],
										[
											"kind"			=> "components.inputs.input-text",
											"classEx"		=> "amount-table",
											"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->amount."\""
										]
									]
								],
								[
									"content" =>
									[
										"kind"			=> "components.buttons.button",
										"variant"		=> "warning",
										"attributeEx"	=> "type=\"button\"",
										"classEx"		=> "add-concept",
										"label"			=> "<span class=\"icon-plus\"></span>"
									]
								]
							];
							$modelBody[] = $body;
							$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
								"modelBody"		=> $modelBody,
								"modelHead"		=> $modelHead,
								"noHead"		=> "true"
							])));
						}
					}
				}
			}
			else
			{
				$html .= '<div class="border p-2"><div id="not-found">NO SE ENCONTRARON CONCEPTOS</div></div>';
			}
			return Response($html);
		}               
	}

	public function getResourceTotal(Request $request)
	{
		if($request->ajax())
		{
			$request    = App\RequestModel::find($request->folio);
			$output = "";
			if ($request != "") 
			{
				foreach ($request as $r) 
				{
					foreach(App\Resource::where('idFolio',$r->folio)->get() as $resource)
					{
						$output .=  $resource->total;
					}
				}
			}
			else
			{
				$output .=  '';
			}
			return Response($output);
		}               
	}

	public function getResourceDetailDelete(Request $request)
	{
		if($request->ajax())
		{
			$html		= '';
			$body		= [];
			$modelBody	= [];
			$modelHead	= [
				["value" => "Concepto", "show" => "true"],
				["value" => "Clasificación de gasto"],
				["value" => "Importe"],
				["value" => "Acción"],
			];

			App\ResourceDetail::where('idresourcedetail',$request->idresourcedetail)
			->update(['statusRefund' => 0]);

			$del2   = App\ExpensesDocuments::where('idExpensesDetail',$request->idExpensesDetail)->delete();
			$del1   = App\ExpensesDetail::where('idExpensesDetail',$request->idExpensesDetail)->delete();

			foreach (App\ResourceDetail::where('idresourcedetail',$request->idresourcedetail)->get() as $resourceDetail)
			{
				$body = [  "classEx" => "tr_detail",
					[   
						"show" => "true",
						"content" =>
						[
							[
								"label" => $resourceDetail->concept
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx"		=> "idresourcedetail-table",
								"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idresourcedetail."\""
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx"		=> "concept-table",
								"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->concept."\""
							]
						]
					],
					[
						"content" =>
						[
							[
								"label" => $resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description
							],
							[
								"kind" 			=> "components.inputs.input-text",
								"classEx" 		=> "account-table",
								"attributeEx" 	=> "type=\"hidden\" value=\"".$resourceDetail->accountsReview->account.' '.$resourceDetail->accountsReview->description."\""
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "accountid-table",
								"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->idAccAccR."\""
							]
						]	 
					],
					[
						"content" =>
						[
							[
								"label" => number_format($resourceDetail->amount,2)
							],
							[
								"kind"			=> "components.inputs.input-text",
								"classEx"		=> "amount-table",
								"attributeEx"	=> "type=\"hidden\" value=\"".$resourceDetail->amount."\""
							]
						]
					],
					[
						"content" =>
						[
							"kind"			=> "components.buttons.button",
							"variant"		=> "warning",
							"attributeEx"	=> "type=\"button\"",
							"classEx"		=> "add-concept",
							"label"			=> "<span class=\"icon-plus\"></span>"
						]
					]
				];
				$modelBody[] = $body;

				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
					"classExBody" 	=> "get_Resource_Detail_Delete",
					"modelBody"		=> $modelBody,
					"modelHead"		=> $modelHead,
					"noHead"		=> "true"
				])));
			}
			return Response($html);
		}               
	}

	public function getBanks(Request $request){

		if ($request->ajax()) {
			
			$outputB    = "";
			$headerB    = "";
			$footerB    = "";
			$banks      = App\Employee::join('banks','employees.idBanks','banks.idBanks')
							->where('visible',1)
							->where('idUsers',$request->idUsers)
							->get();
			$countBanks = count($banks);
			if ($countBanks >= 1)
			{
				$html 		= '';
				$body		= [];
				$modelBody	= [];
				$html .= '<div class="m-4">';
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.title-divisor',['slot'=> 'Seleccione Una Cuenta'])));
				$html .= '</div>';

				$modelHead	= ["Acción","Banco","Alias","Número de tarjeta","CLABE","Número de cuenta"];
				foreach ($banks as $bank) 
				{
					$alias 		= $bank->alias		!=null ? $bank->alias		: '---';
					$cardNumber = $bank->cardNumber	!=null ? $bank->cardNumber	: '---';
					$clabe 		= $bank->clabe		!=null ? $bank->clabe		: '---';
					$account 	= $bank->account	!=null ? $bank->account		: '---';
				
					$body = [
						[
							"content" =>
							[
								[
									"kind"				=> "components.inputs.checkbox",
									"classEx"			=> "checkbox",
									"attributeEx"		=> "name=\"idEmployee\" id=\"idEmp".$bank->idEmployee."\"".' '."value=\"".$bank->idEmployee."\"",
									"classExLabel"		=> "request-validate",
									'label'				=> "<span class=\"icon-check\"></span>",
									"classExContainer"	=> "my-2",
									"radio"				=> true
								]
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
									"label" => $alias
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
									"label" => $cardNumber 
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"card[]\" placeholder=\"Ingrese un número de tarjeta\" value=\"".$cardNumber."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $clabe
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"clabe[]\" placeholder=\"Ingrese una CLABE\" value=\"".$clabe."\""
								]
							]
						],
						[
							"content" => 
							[
								[
									"label" => $account
								],
								[
									"kind"			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"account[]\" placeholder=\"Ingrese una cuenta bancaria\" value=\"".$account."\""
								]
							]
						]		
					];
					$modelBody[] = $body;
				}
				$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.alwaysVisibleTable", [
					"attributeEx" 	=> "id=\"table2\"",
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

	public function getDates(Request $request)
	{
	   if ($request->ajax()){
			$output 	= "";
			$row 		= "";
			$requests 	= App\RequestModel::find($request->folio);
			if ($requests != "") 
			{	
				foreach ($requests as $request) 
				{
					$wbsId 		= "";
					$varWBS 	= "";
					if(isset($request->wbs))
					{
						$varWBS =  $request->wbs->code_wbs;
						$wbsId = $request->wbs->id;
					}
					else
					{
						$varWBS = "---";
						$wbsId = "x";
					} 
					$varEDT = "";
					$edtId	= "";
					if(isset($request->edt))
					{
						$varEDT = $request->edt->fullName();
						$edtId = $request->edt->id;
					}
					else
					{
						$varEDT = "---";
						$edtId = "x";
					} 

					$modelTable = 
					[
						[
							"Empresa:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $request->requestEnterprise->name
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx"	=> "type=\"hidden\" name=\"enterprise_id\" value=\"".$request->requestEnterprise->id."\""
								]
							]
						],
						[
							"Dirección:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $request->requestDirection->name
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"area_id\" value=\"".$request->requestDirection->id."\""
								]
							]
						],
						[
							"Departamento:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $request->requestDepartment->name
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"department_id\" value=\"".$request->requestDepartment->id."\""
								]
							]
						],
						[
							"Proyecto:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $request->requestProject->proyectName
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"project_id\" value=\"".$request->requestProject->idproyect."\""
								]
							]
						],
						[
							"Código WBS:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $varWBS
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"wbs_id\" value=\"".$wbsId."\""
								]
							]
						],
						[
							"Código EDT:",
							[
								[
									"kind" 	=> "components.labels.label",
									"label" => $varEDT
								],
								[
									"kind" 			=> "components.inputs.input-text",
									"attributeEx" 	=> "type=\"hidden\" name=\"edt_id\" value=\"".$edtId."\""
								]
							]
						]
					];
					$output	.= view('components.templates.outputs.table-detail',['title' => 'Detalles de la Solicitud',	"modelTable"	=> $modelTable]);
				}
			}
			return Response(html_entity_decode($output));
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
				\Storage::disk('public')->delete('/docs/expenses/'.$request->realPath);
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_expensesDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/expenses/'.$name;
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
		if(Auth::user()->module->where('id',33)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',33)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',33)->first()->global_permission;
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
			$resource_id  	= $request->resource_id;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio,
								expenses.title,
								CONCAT_WS(" - ",resources.idFolio,resources.title) as reourceTitle,
								DATE_FORMAT(expenses.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								expenses.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								expenses_details.concept as conceptName,
								IF(expenses_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								IF(expenses_details.taxPayment=1,"Fiscal", "No fiscal") as conceptFiscal,
								expenses_details.amount as conceptSubAmount,
								expenses_details.tax as conceptTax,
								IFNULL(taxes_expenses.amount_taxes,0) as conceptTaxes,
								expenses_details.sAmount as conceptAmount,
								expenses.reintegro as conceptDrawback,
								expenses.reembolso as conceptRefund,
								IFNULL(paymentsTemp.paymentsAmountReal,0) as amountPaid
							')
							->leftJoin('expenses', 'expenses.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'expenses.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'expenses.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('expenses_details', 'expenses_details.idExpenses', 'expenses.idExpenses')
							->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as amount_taxes from taxes_expenses group by idExpensesDetail) as taxes_expenses'),'taxes_expenses.idExpensesDetail','expenses_details.idExpensesDetail')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'expenses_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'expenses_details.idAccount')
							->leftJoin(DB::raw('(SELECT idFolio, SUM(amount_real) as paymentsAmountReal from payments group by idFolio) as paymentsTemp'),'paymentsTemp.idFolio','request_models.folio')
							->leftJoin('resources', 'resources.idFolio', 'expenses.resourceId')
							->where('request_models.kind',3)
							->where(function($q) 
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(33)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
							})
							->where(function ($q) 
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(33)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
							})
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
								}
							})
							->where(function ($query) use ($name, $folio, $status, $mindate, $maxdate, $enterpriseid, $resource_id)
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
								if ($resource_id != "") 
								{
									$query->where('expenses.resourceId',$resource_id);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-gastos.xlsx');
			$writer->getCurrentSheet()->setName('Seguimiento');

			$headers = ['Reporte de seguimiento de gastos','','','','','','','','','','', '', '', '', '', '', '', '', '', '', '', '', '', '',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título', 'Recurso', 'Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación de gasto', 'Tipo fiscal', 'Subtotal', 'IVA','Impuestos Adicional', 'Total', 'Reintegro', 'Reembolso', 'Monto pagado'];
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
					$request->status			= '';
					$request->date				= '';
					$request->total				= null;
					$request->paymentMethod		= '';
					$request->bankName			= '';
					$request->cardNumber		= '';
					$request->clabe				= '';
					$request->account			= '';
					$request->conceptDrawback	= null;
					$request->conceptRefund		= null;
					$request->amountPaid		= null;
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total','conceptAmount', 'amountPaid', 'conceptSubAmount', 'conceptRefund', 'conceptDrawback', 'conceptTax','conceptTaxes']))
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
		if(Auth::user()->module->where('id',34)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio,
								expenses.title,
								CONCAT_WS(" - ",resources.idFolio,resources.title) as reourceTitle,
								DATE_FORMAT(expenses.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								expenses.total as total,
								payment_methods.method as paymentMethod,
								banks.description as bankName,
								employees.cardNumber as cardNumber,
								employees.clabe as clabe,
								employees.account as account,
								expenses_details.concept as conceptName,
								IF(expenses_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
								IF(expenses_details.taxPayment=1,"Fiscal", "No fiscal") as conceptFiscal,
								expenses_details.amount as conceptSubAmount,
								expenses_details.tax as conceptTax,
								IFNULL(taxes_expenses.amount_taxes,0) as taxes,
								expenses_details.sAmount as conceptAmount,
								expenses.reintegro as conceptDrawback,
								expenses.reembolso as conceptRefund
							')
							->leftJoin('expenses', 'expenses.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'expenses.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'expenses.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('expenses_details', 'expenses_details.idExpenses', 'expenses.idExpenses')
							->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as amount_taxes from taxes_expenses group by idExpensesDetail) as taxes_expenses'),'taxes_expenses.idExpensesDetail','expenses_details.idExpensesDetail')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'expenses_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'expenses_details.idAccount')
							->leftJoin('payments', 'payments.idFolio', 'request_models.folio')
							->leftJoin('resources', 'resources.idFolio', 'expenses.resourceId')
				->where('request_models.kind',3)
				->where('request_models.status',3)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(34)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(34)->pluck('departament_id'))
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-gastos.xlsx');
			$writer->getCurrentSheet()->setName('Revisión');

			$headers = ['Reporte de revisión de gastos','','','','','','','','','','', '', '', '', '', '', '', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título', 'Recurso', 'Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación de gasto', 'Tipo fiscal', 'Subtotal', 'IVA','Impuestos Adicional', 'Total', 'Reintegro', 'Reembolso'];
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
					$request->reourceTitle		= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
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
					if(in_array($k, ['total', 'conceptAmount', 'conceptSubAmount', 'conceptTax', 'taxes', 'conceptDrawback', 'conceptRefund']))
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
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$alignment);
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
		if(Auth::user()->module->where('id',35)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;
			$requests		= DB::table('request_models')->selectRaw(
							'
							request_models.folio,
							expenses.title,
							CONCAT_WS(" - ",resources.idFolio,resources.title) as reourceTitle,
							DATE_FORMAT(expenses.datetitle, "%d-%m-%Y") as datetitle,
							CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
							CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
							IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
							status_requests.description as status,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							expenses.total as total,
							payment_methods.method as paymentMethod,
							banks.description as bankName,
							employees.cardNumber as cardNumber,
							employees.clabe as clabe,
							employees.account as account,
							expenses_details.concept as conceptName,
							IF(expenses_details.idAccountR IS NULL,CONCAT_WS(" - ",conceptAccount.account,conceptAccount.description), CONCAT_WS(" - ",conceptAccountR.account,conceptAccountR.description)) as conceptAccount,
							IF(expenses_details.taxPayment=1,"Fiscal", "No fiscal") as conceptFiscal,
							expenses_details.amount as conceptSubAmount,
							expenses_details.tax as conceptTax,
							IFNULL(taxes_expenses.amount_taxes,0) as taxes,
							expenses_details.sAmount as conceptAmount,
							expenses.reintegro as conceptDrawback,
							expenses.reembolso as conceptRefund
							')
							->leftJoin('expenses', 'expenses.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('payment_methods', 'payment_methods.idpaymentMethod', 'expenses.idpaymentMethod')
							->leftJoin('employees', 'employees.idEmployee', 'expenses.idEmployee')
							->leftJoin('banks', 'banks.idBanks', 'employees.idBanks')
							->leftJoin('expenses_details', 'expenses_details.idExpenses', 'expenses.idExpenses')
							->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as amount_taxes from taxes_expenses group by idExpensesDetail) as taxes_expenses'),'taxes_expenses.idExpensesDetail','expenses_details.idExpensesDetail')
							->leftJoin('accounts as conceptAccountR', 'conceptAccountR.idAccAcc', 'expenses_details.idAccountR')
							->leftJoin('accounts as conceptAccount', 'conceptAccount.idAccAcc', 'expenses_details.idAccount')
							->leftJoin('payments', 'payments.idFolio', 'request_models.folio')
							->leftJoin('resources', 'resources.idFolio', 'expenses.resourceId')
							->where('request_models.kind',3)
							->where('request_models.status',4)
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(35)->pluck('enterprise_id'))
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(35)->pluck('departament_id'))
							->where(function ($query) use ($name, $folio, $mindate, $maxdate, $enterpriseid)
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

			$headers = ['Reporte de autorización de gastos','','','','','','','','','','', '', '', '', '', '', '', '', '', '', '', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título', 'Recurso', 'Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Monto','Método de pago', 'Banco', 'Número de tarjeta', 'CLABE', 'Número de cuenta', 'Concepto', 'Clasificación de gasto', 'Tipo fiscal', 'Subtotal', 'IVA','Impuestos Adicional', 'Total', 'Reintegro', 'Reembolso'];
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
					$request->reourceTitle		= '';
					$request->datetitle			= '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
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
					if(in_array($k, ['total', 'conceptAmount', 'conceptSubAmount', 'conceptTax', 'taxes', 'conceptDrawback', 'conceptRefund']))
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

	public function updateCode(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request 			= App\RequestModel::find($id);
			$t_request->free 	= 1;
			$t_request->save();
			$alert  = "swal('', 'Solicitud Liberada Exitosamente', 'success');";
			return redirect('administration/expenses/search')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validationDocs(Request $request)
	{
		/*
			ALTER TABLE `expensesDocuments` ADD `name` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `idExpensesDocuments`;
			ALTER TABLE `expensesDocuments` ADD `fiscal_folio` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `idExpensesDetail`, ADD `datepath` DATE NULL DEFAULT NULL AFTER `fiscal_folio`, ADD `timepath` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `datepath`, ADD `ticket_number` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `timepath`, ADD `amount` DECIMAL(16,2) NULL DEFAULT NULL AFTER `ticket_number`, ADD `users_id` INT NULL DEFAULT NULL AFTER `amount`;
			ALTER TABLE `expensesDocuments` ADD `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `users_id`;
			ALTER TABLE `expensesDocuments` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
		*/
		if($request->ajax())
		{
			$folio 				= $request->requestFolio;
			$check_docs_status	= App\RequestModel::where('folio', $folio)->whereIn('status',[2])->count();

			if($check_docs_status>0)
			{
				$folio	= $folio;
			}
			else
			{
				$folio	= "";
			}
			$position = [];
			for ($i=0; $i < count($request->amount); $i++)
			{ 
				$options               = [];
				$options['fiscal_val'] = $request->fiscal_folio[$i];
				$options['ticket_val'] = $request->ticket_number[$i];
				$options['date']       = Carbon::createFromFormat('d-m-Y',$request->datepath[$i])->format('Y-m-d');
				$options['time']       = $request->timepath[$i];
				$options['amount']     = $request->amount[$i];
				$check_docs            = App\Functions\DocsValidate::validate($options,$folio);

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
			return Response($position);
		}
	}
}
