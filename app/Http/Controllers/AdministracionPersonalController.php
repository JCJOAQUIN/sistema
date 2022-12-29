<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use Lang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Functions\Files;
use Ilovepdf\CompressTask;
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

class AdministracionPersonalController extends Controller
{
	private $module_id = 72;
	
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',['id'=>$data['father'],'title'=>$data['name'],'details'=>$data['details'],'child_id'=>$this->module_id]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',73)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$thisModule			= App\Module::find(73);
			$enterprises    	= App\Enterprise::orderName()
												->where('status','ACTIVE')
												->whereIn('id',Auth::user()->inChargeEnt(73)->pluck('enterprise_id'))
												->get();
			$areas				= App\Area::orderName()->where('status','ACTIVE')->get();
			$projects			= App\Project::orderName()->get();
			$responsibilities	= App\Responsibility::orderName()->get();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();
			$roles				= App\Role::where('status','ACTIVE')->get();
			$departments    	= App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(73)->pluck('departament_id'))->get();
			return view('administracion.personal.alta',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $thisModule['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 73,
					'areas'				=> $areas,
					'responsibilities'	=> $responsibilities,
					'projects'			=> $projects,
					'minSalary'			=> $minSalary,
					'maxSalary'			=> $maxSalary,
					'roles' 			=> $roles,
					'departments' 		=> $departments,
					'enterprises'		=> $enterprises
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',73)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',74)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',74)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data				= App\Module::find($this->module_id);
			$thisModule			= App\Module::find(73);
			$users				= App\User::where('status','ACTIVE')->where('sys_user',1)->get();
			$areas				= App\Area::where('status','ACTIVE')->get();
			$enterprises    	= App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(73)->pluck('enterprise_id'))->get();
			$departments    	= App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(73)->pluck('departament_id'))->get();
			$projects			= App\Project::all();
			$responsibilities	= App\Responsibility::all();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();
			$roles				= App\Role::where('status','ACTIVE')->get();
			
			$requests			= App\RequestModel::where('kind',4)
								->whereIn('status',[5,6,7,10,11,12])
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
				return view('administracion.personal.alta',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $thisModule['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 73,
						'enterprises'		=> $enterprises,
						'areas'				=> $areas,
						'departments'		=> $departments,
						'users'				=> $users,
						'responsibilities'	=> $responsibilities,
						'projects'			=> $projects,
						'minSalary'			=> $minSalary,
						'maxSalary'			=> $maxSalary,
						'roles' 			=> $roles,
						'requests'			=> $requests
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
					\Storage::disk('public')->delete('/docs/staff/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_staffDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/staff/'.$name;
				
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
			return Response($response);
		}
	}


	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 4;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 3;
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest		= $request->user_id;
			$t_request->idProject		= $request->project_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$t_staff					= new App\Staff();
			$t_staff->idFolio			= $folio;
			$t_staff->idKind			= $kind;
			$t_staff->title 		  	= $request->title;
			$t_staff->datetitle 	  	= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_staff->boss				= $request->boss_id;
			$t_staff->schedule_start	= $request->schedule_start;
			$t_staff->schedule_end		= $request->schedule_end;
			$t_staff->minSalary			= $request->minSalary;
			$t_staff->maxSalary			= $request->maxSalary;
			$t_staff->reason			= $request->reason;
			$t_staff->role_id			= $request->role_id;
			$t_staff->position			= $request->position;
			$t_staff->periodicity		= $request->periodicity;
			$t_staff->description		= $request->s_description;
			$t_staff->habilities		= $request->habilities;
			$t_staff->experience		= $request->experience;
			$t_staff->save();
			
			$idStaff					= $t_staff->idStaff;
			if (isset($request->responsibilities) && $request->responsibilities != "") 
			{
				$t_staff->responsibility()->attach($request->responsibilities);
			}
			if(isset($request->tfunction))
			{
				for ($i=0; $i <count($request->tfunction); $i++)
				{
					$t_function					= new App\StaffFunction();
					$t_function->idStaff		= $idStaff;
					$t_function->function		= $request->tfunction[$i];
					$t_function->description	= $request->tdescr[$i];
					$t_function->save();
				}
			}
			if(isset($request->tdesirable))
			{
				for ($i=0; $i <count($request->tdesirable); $i++)
				{
					$t_desirable				= new App\StaffDesirable();
					$t_desirable->idStaff		= $idStaff;
					$t_desirable->desirable		= $request->tdesirable[$i];
					$t_desirable->description	= $request->td_descr[$i];
					$t_desirable->save();
				}
			}

			if (isset($request->rq_name) && count($request->rq_name)) 
			{
				for ($i=0; $i < count($request->rq_name); $i++) 
				{ 
					$employee							= new App\StaffEmployee();
					$employee->name						= $request->rq_name[$i];
					$employee->last_name				= $request->rq_last_name[$i];
					$employee->scnd_last_name			= $request->rq_scnd_last_name[$i];
					$employee->curp						= $request->rq_curp[$i];
					$employee->rfc						= $request->rq_rfc[$i];
					$employee->tax_regime				= $request->rq_tax_regime[$i];
					$employee->imss						= $request->rq_imss[$i];
					$employee->email					= $request->rq_email[$i];
					$employee->phone					= $request->rq_phone[$i];
					$employee->street					= $request->rq_street[$i];
					$employee->number					= $request->rq_number_employee[$i];
					$employee->colony					= $request->rq_colony[$i];
					$employee->cp						= $request->rq_cp[$i];
					$employee->city						= $request->rq_city[$i];
					$employee->state_id					= $request->rq_state[$i];
					$employee->state					= $request->rq_work_state[$i];
					$employee->project					= $request->project_id;
					$employee->wbs_id					= isset($request->rq_work_wbs[$i]) ? $request->rq_work_wbs[$i] : null;
					$employee->enterprise				= $request->rq_work_enterprise[$i];
					$employee->account					= $request->rq_work_account[$i];
					$employee->direction				= $request->rq_work_direction[$i];
					$employee->department				= $request->rq_work_department[$i];
					$employee->position					= $request->rq_work_position[$i];
					$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
					$employee->admissionDate			= $request->rq_work_income_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_income_date[$i])->format('Y-m-d')	: null;
					$employee->imssDate					= $request->rq_work_imss_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_imss_date[$i])->format('Y-m-d') 	: null;
					$employee->downDate					= $request->rq_work_down_date[$i] 		!= "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_down_date[$i])->format('Y-m-d')		: null;
					$employee->endingDate				= $request->rq_work_ending_date[$i]		!= "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_ending_date[$i])->format('Y-m-d')	: null;
					$employee->reentryDate				= $request->rq_work_reentry_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_reentry_date[$i])->format('Y-m-d')	: null;
					$employee->workerType				= $request->rq_work_type_employee[$i];
					$employee->regime_id				= $request->rq_regime_employee[$i];
					$employee->workerStatus				= $request->rq_work_status_employee[$i];
					$employee->status_reason			= $request->rq_work_status_reason[$i];
					$employee->status_imss				= $request->rq_work_status_imss[$i];
					$employee->sdi						= $request->rq_work_sdi[$i];
					$employee->periodicity				= $request->rq_work_periodicity[$i];
					$employee->employer_register		= $request->rq_work_employer_register[$i];
					$employee->paymentWay				= $request->rq_work_payment_way[$i];
					$employee->netIncome				= $request->rq_work_net_income[$i];
					$employee->complement				= $request->rq_work_complement[$i];
					$employee->fonacot					= $request->rq_work_fonacot[$i];
					$employee->viatics					= $request->rq_work_viatics[$i];
					$employee->camping					= $request->rq_work_camping[$i];
					$employee->replace					= $request->rq_replace[$i];
					$employee->purpose					= $request->rq_purpose[$i];
					$employee->requeriments				= $request->rq_requeriments[$i];
					$employee->observations				= $request->rq_observations[$i];
					$employee->position_immediate_boss	= $request->rq_work_position_immediate_boss[$i];
					$employee->subdepartment_id			= $request->rq_work_subdepartment[$i];
					$employee->doc_birth_certificate	= $request->rq_doc_birth_certificate[$i];
					$employee->doc_proof_of_address		= $request->rq_doc_proof_of_address[$i];
					$employee->doc_nss					= $request->rq_doc_nss[$i];
					$employee->doc_ine					= $request->rq_doc_ine[$i];
					$employee->doc_curp					= $request->rq_doc_curp[$i];
					$employee->doc_rfc					= $request->rq_doc_rfc[$i];
					$employee->doc_cv					= $request->rq_doc_cv[$i];
					$employee->doc_proof_of_studies		= $request->rq_doc_proof_of_studies[$i];
					$employee->doc_professional_license	= $request->rq_doc_professional_license[$i];
					$employee->computer_required		= $request->rq_computer_required[$i];
					$employee->staff_id					= $t_staff->idStaff;

					if($request->rq_work_infonavit_credit[$i] != ""&& $request->rq_work_infonavit_discount[$i] != "" && $request->rq_work_infonavit_discount_type[$i] != "")
					{
						$employee->infonavitCredit       = $request->rq_work_infonavit_credit[$i];
						$employee->infonavitDiscount     = $request->rq_work_infonavit_discount[$i];
						$employee->infonavitDiscountType = $request->rq_work_infonavit_discount_type[$i];
					}
					if($request->rq_work_alimony_discount[$i] != "" && $request->rq_work_alimony_discount_type[$i] != "")
					{
						$employee->alimonyDiscount     = $request->rq_work_alimony_discount[$i];
						$employee->alimonyDiscountType = $request->rq_work_alimony_discount_type[$i];
					}
					$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
					
					
					$docs = 
					[
						"doc_requisition", 
						"doc_professional_license", 
						"doc_proof_of_studies", 
						"doc_cv", 
						"doc_rfc", 
						"doc_curp", 
						"doc_ine", 
						"doc_nss", 
						"doc_proof_of_address", 
						"doc_birth_certificate"
					];

					foreach($docs as $kind_doc)
					{
						$doc_name 			= "rq_".$kind_doc;
						$employee->$kind_doc= $request->$doc_name[$i];
					}

					$employee->save();

					$field = "name_other_document_".$i;
					
					if(isset($request->$field) && $request->$field != "")
					{
						for($j = 0; $j < count($request->$field); $j++)
						{
							$name_doc 								= "name_other_document_".$i;
							$path_doc 								= "path_other_document_".$i;
							$t_staff_documents						=	new App\StaffDocuments();
							$t_staff_documents->name				=	$request->$name_doc[$j];
							$t_staff_documents->path				=	$request->$path_doc[$j];
							$t_staff_documents->id_staff_employee	=	$employee->id;
							$t_staff_documents->save();
						}
					}

					$beneficiary	= 'beneficiary_'.$i;
					$type			= 'type_'.$i;
					$alias			= 'alias_'.$i;
					$clabe			= 'clabe_'.$i;
					$account		= 'account_'.$i;
					$cardNumber		= 'cardNumber_'.$i;
					$idCatBank		= 'idCatBank_'.$i;
					$branch			= 'branch_'.$i;
					$idEmployee		= 'idEmployee_'.$i;

					if(isset($request->$idEmployee) && count($request->$idEmployee) > 0)
					{
						foreach ($request->$idEmployee as $k => $e)
						{
							$empAcc              		= new App\StaffAccounts();
							$empAcc->id_staff_employee	= $employee->id;
							$empAcc->beneficiary 		= $request->$beneficiary[$k];
							$empAcc->type        		= $request->$type[$k];
							$empAcc->alias       		= $request->$alias[$k];
							$empAcc->clabe       		= $request->$clabe[$k];
							$empAcc->account     		= $request->$account[$k];
							$empAcc->cardNumber  		= $request->$cardNumber[$k];
							$empAcc->id_catbank  		= $request->$idCatBank[$k];
							$empAcc->branch      		= $request->$branch[$k];
							$empAcc->recorder    		= Auth::user()->id;
							$empAcc->save();
						}
					}
					
				}
			}
			
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 75);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',75);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',75);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',75)
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
				   		$kind 			= "Personal";
				   		$status 		= "Revisar";
				   		$date 			= Carbon::now();
				   		$url 			= route('staff.review.edit',['id'=>$folio]);
				   		$subject 		= "Solicitud por Revisar";
				   		$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				   		Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
				   	}
				   	$alert 	= 	$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			   	}
			   	catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/staff')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function viewDetailEmployee(Request $request)
	{
		if ($request->ajax()) 
		{
			$employee = App\StaffEmployee::find($request->employee_id);
			return view('administracion.requisicion.parcial.detalles_empleado',['employee'=>$employee]);
		}
	}
	
	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',74)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',74)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',74)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data           = App\Module::find($this->module_id);
			$name 			= $request->name;
			$folio 			= $request->folio;
			$status 		= $request->status;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid 	= $request->enterpriseid;
			//16 y 17
			$requests		= App\RequestModel::where('kind','4')
								->where(function($q) 
								{
									$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(74)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
								})
								->where(function ($q) 
								{
									$q->whereIn('idDepartment',Auth::user()->inChargeDep(74)->pluck('departament_id'))->orWhereNull('idDepartment');
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

			return view('administracion.personal.busqueda',
				[
					'id'		=>$data['father'],
					'title'		=>$data['name'],
					'details'	=>$data['details'],
					'child_id'	=>$this->module_id,
					'option_id'	=>74,
					'requests'	=>$requests,
					'folio' 	=> $folio, 
					'name' 		=> $name, 
					'mindate' 	=> $request->mindate, 
					'maxdate' 	=> $request->maxdate,
					'status'	=> $status,
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
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request					= new App\RequestModel();
			$t_request->kind			= 4;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 2;
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest		= $request->user_id;
			$t_request->idProject		= $request->project_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio						= $t_request->folio;
			$kind						= $t_request->kind;
			$t_staff					= new App\Staff();
			$t_staff->idFolio			= $folio;
			$t_staff->idKind			= $kind;
			$t_staff->title 		  	= $request->title;
			$t_staff->datetitle 	  	= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_staff->boss				= $request->boss_id;
			$t_staff->schedule_start	= $request->schedule_start;
			$t_staff->schedule_end		= $request->schedule_end;
			$t_staff->minSalary			= $request->minSalary;
			$t_staff->maxSalary			= $request->maxSalary;
			$t_staff->reason			= $request->reason;
			$t_staff->role_id			= $request->role_id;
			$t_staff->position			= $request->position;
			$t_staff->periodicity		= $request->periodicity;
			$t_staff->description		= $request->s_description;
			$t_staff->habilities		= $request->habilities;
			$t_staff->experience		= $request->experience;
			$t_staff->save();
			
			$idStaff					= $t_staff->idStaff;
			if (isset($request->responsibilities) && $request->responsibilities != "") 
			{
				$t_staff->responsibility()->attach($request->responsibilities);
			}
			
			if(isset($request->tfunction) && $request->tfunction!='')
			{
				for ($i=0; $i <count($request->tfunction); $i++)
				{
					$t_function					= new App\StaffFunction();
					$t_function->idStaff		= $idStaff;
					$t_function->function		= $request->tfunction[$i];
					$t_function->description	= $request->tdescr[$i];
					$t_function->save();
				}
			}
			if(isset($request->tdesirable) && $request->tdesirable)
			{
				for ($i=0; $i <count($request->tdesirable); $i++)
				{
					$t_desirable				= new App\StaffDesirable();
					$t_desirable->idStaff		= $idStaff;
					$t_desirable->desirable		= $request->tdesirable[$i];
					$t_desirable->description	= $request->td_descr[$i];
					$t_desirable->save();
				}
			}

			if (isset($request->rq_name) && count($request->rq_name)) 
			{
				for ($i=0; $i < count($request->rq_name); $i++) 
				{
					$employee							= new App\StaffEmployee();				
					$employee->name						= $request->rq_name[$i];
					$employee->last_name				= $request->rq_last_name[$i];
					$employee->scnd_last_name			= $request->rq_scnd_last_name[$i];
					$employee->curp						= $request->rq_curp[$i];
					$employee->rfc						= $request->rq_rfc[$i];
					$employee->tax_regime				= $request->rq_tax_regime[$i];
					$employee->imss						= $request->rq_imss[$i];
					$employee->email					= $request->rq_email[$i];
					$employee->phone					= $request->rq_phone[$i];
					$employee->street					= $request->rq_street[$i];
					$employee->number					= $request->rq_number_employee[$i];
					$employee->colony					= $request->rq_colony[$i];
					$employee->cp						= $request->rq_cp[$i];
					$employee->city						= $request->rq_city[$i];
					$employee->state_id					= $request->rq_state[$i];
					$employee->state					= $request->rq_work_state[$i];
					$employee->project					= $request->project_id;
					$employee->wbs_id					= isset($request->rq_work_wbs[$i]) ? $request->rq_work_wbs[$i] : null;
					$employee->enterprise				= $request->rq_work_enterprise[$i];
					$employee->account					= $request->rq_work_account[$i];
					$employee->direction				= $request->rq_work_direction[$i];
					$employee->department				= $request->rq_work_department[$i];
					$employee->position					= $request->rq_work_position[$i];
					$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
					$employee->admissionDate			= isset($request->rq_work_income_date[$i]) && $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_income_date[$i])->format('Y-m-d') : null;
					$employee->imssDate					= isset($request->rq_work_imss_date[$i]) && $request->rq_work_imss_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_imss_date[$i])->format('Y-m-d') : null;
					$employee->downDate					= isset($request->rq_work_down_date[$i]) && $request->rq_work_down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_down_date[$i])->format('Y-m-d') : null;
					$employee->endingDate				= isset($request->rq_work_ending_date[$i]) && $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_ending_date[$i])->format('Y-m-d') : null;
					$employee->reentryDate				= isset($request->rq_work_reentry_date[$i]) && $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
					$employee->workerType				= $request->rq_work_type_employee[$i];
					$employee->regime_id				= $request->rq_regime_employee[$i];
					$employee->workerStatus				= $request->rq_work_status_employee[$i];
					$employee->status_reason			= $request->rq_work_status_reason[$i];
					$employee->status_imss				= $request->rq_work_status_imss[$i];
					$employee->sdi						= $request->rq_work_sdi[$i];
					$employee->periodicity				= $request->rq_work_periodicity[$i];
					$employee->employer_register		= $request->rq_work_employer_register[$i];
					$employee->paymentWay				= $request->rq_work_payment_way[$i];
					$employee->netIncome				= $request->rq_work_net_income[$i];
					$employee->complement				= $request->rq_work_complement[$i];
					$employee->fonacot					= $request->rq_work_fonacot[$i];
					$employee->viatics					= $request->rq_work_viatics[$i];
					$employee->camping					= $request->rq_work_camping[$i];
					$employee->replace					= $request->rq_replace[$i];
					$employee->purpose					= $request->rq_purpose[$i];
					$employee->requeriments				= $request->rq_requeriments[$i];
					$employee->observations				= $request->rq_observations[$i];
					$employee->position_immediate_boss	= $request->rq_work_position_immediate_boss[$i];
					$employee->subdepartment_id			= $request->rq_work_subdepartment[$i];
					$employee->doc_birth_certificate	= $request->rq_doc_birth_certificate[$i];
					$employee->doc_proof_of_address		= $request->rq_doc_proof_of_address[$i];
					$employee->doc_nss					= $request->rq_doc_nss[$i];
					$employee->doc_ine					= $request->rq_doc_ine[$i];
					$employee->doc_curp					= $request->rq_doc_curp[$i];
					$employee->doc_rfc					= $request->rq_doc_rfc[$i];
					$employee->doc_cv					= $request->rq_doc_cv[$i];
					$employee->doc_proof_of_studies		= $request->rq_doc_proof_of_studies[$i];
					$employee->doc_professional_license	= $request->rq_doc_professional_license[$i];
					$employee->computer_required		= $request->rq_computer_required[$i];
					$employee->staff_id					= $t_staff->idStaff;

					if($request->rq_work_infonavit_credit[$i] != ""&& $request->rq_work_infonavit_discount[$i] != "" && $request->rq_work_infonavit_discount_type[$i] != "")
					{
						$employee->infonavitCredit       = $request->rq_work_infonavit_credit[$i];
						$employee->infonavitDiscount     = $request->rq_work_infonavit_discount[$i];
						$employee->infonavitDiscountType = $request->rq_work_infonavit_discount_type[$i];
					}
					if($request->rq_work_alimony_discount[$i] != "" && $request->rq_work_alimony_discount_type[$i] != "")
					{
						$employee->alimonyDiscount     = $request->rq_work_alimony_discount[$i];
						$employee->alimonyDiscountType = $request->rq_work_alimony_discount_type[$i];
					}
					$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
					
					$docs = 
					[
						"doc_requisition", 
						"doc_professional_license", 
						"doc_proof_of_studies", 
						"doc_cv", 
						"doc_rfc", 
						"doc_curp", 
						"doc_ine", 
						"doc_nss", 
						"doc_proof_of_address", 
						"doc_birth_certificate"
					];

					foreach($docs as $kind_doc)
					{
						$doc_name 			 = "rq_".$kind_doc;
						$employee->$kind_doc = $request->$doc_name[$i];
					}

					$employee->save();

					$field = "name_other_document_".$i;
					
					if(isset($request->$field) && $request->$field != "")
					{
						for($j = 0; $j < count($request->$field); $j++)
						{
							$name_doc 								= "name_other_document_".$i;
							$path_doc 								= "path_other_document_".$i;
							$t_staff_documents						=	new App\StaffDocuments();
							$t_staff_documents->name				=	$request->$name_doc[$j];
							$t_staff_documents->path				=	$request->$path_doc[$j];
							$t_staff_documents->id_staff_employee	=	$employee->id;
							$t_staff_documents->save();
						}
					}

					$beneficiary	= 'beneficiary_'.$i;
					$type			= 'type_'.$i;
					$alias			= 'alias_'.$i;
					$clabe			= 'clabe_'.$i;
					$account		= 'account_'.$i;
					$cardNumber		= 'cardNumber_'.$i;
					$idCatBank		= 'idCatBank_'.$i;
					$branch			= 'branch_'.$i;
					$idEmployee		= 'idEmployee_'.$i;

					if(isset($request->$idEmployee) && count($request->$idEmployee) > 0)
					{
						foreach ($request->$idEmployee as $k => $e)
						{
							$empAcc              		= new App\StaffAccounts();
							$empAcc->id_staff_employee	= $employee->id;
							$empAcc->beneficiary 		= $request->$beneficiary[$k];
							$empAcc->type        		= $request->$type[$k];
							$empAcc->alias       		= $request->$alias[$k];
							$empAcc->clabe       		= $request->$clabe[$k];
							$empAcc->account     		= $request->$account[$k];
							$empAcc->cardNumber  		= $request->$cardNumber[$k];
							$empAcc->id_catbank  		= $request->$idCatBank[$k];
							$empAcc->branch      		= $request->$branch[$k];
							$empAcc->recorder    		= Auth::user()->id;
							$empAcc->save();
						}
					}
				}
			}

			if(isset($request->delete_employee) && $request->delete_employee != "")
			{
				foreach($request->delete_employee as $id_employee)
				{
					App\StaffDocuments::where('id_staff_employee',$id_employee)->delete();
					App\StaffAccounts::where('id_staff_employee',$id_employee)->delete();
					App\StaffEmployee::find($id_employee)->delete();
				}
			}

			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('staff.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',74)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',74)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',74)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data				= App\Module::find($this->module_id);
			$thisModule			= App\Module::find(74);
			$users				= App\User::where('status','ACTIVE')->get();
			$areas				= App\Area::where('status','ACTIVE')->get();
			$enterprises    	= App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(74)->pluck('enterprise_id'))->get();
			$departments    	= App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(74)->pluck('departament_id'))->get();
			$projects			= App\Project::all();
			$responsibilities	= App\Responsibility::all();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();
			$roles				= App\Role::where('status','ACTIVE')->get();

			
			$request	= App\RequestModel::where('kind',4)
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
				return view('administracion.personal.seguimiento',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $thisModule['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 74,
						'enterprises'		=> $enterprises,
						'areas'				=> $areas,
						'departments'		=> $departments,
						'users'				=> $users,
						'responsibilities'	=> $responsibilities,
						'projects'			=> $projects,
						'minSalary'			=> $minSalary,
						'maxSalary'			=> $maxSalary,
						'roles' 			=> $roles,
						'request'			=> $request
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
			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= 4;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 3;
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest		= $request->user_id;
			$t_request->idProject		= $request->project_id;
			$t_request->save();
			$Staff						= App\Staff::where('idFolio',$t_request->folio)
											->where('idKind',$t_request->kind)
											->get();
			foreach ($Staff as $key => $value)
			{
				$idStaff = $value->idStaff;
			}
			$t_staff					= App\Staff::find($idStaff);
			$t_staff->title 		  	= $request->title;
			$t_staff->datetitle 	  	= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_staff->boss				= $request->boss_id;
			$t_staff->schedule_start	= $request->schedule_start;
			$t_staff->schedule_end		= $request->schedule_end;
			$t_staff->minSalary			= $request->minSalary;
			$t_staff->maxSalary			= $request->maxSalary;
			$t_staff->reason			= $request->reason;
			$t_staff->role_id			= $request->role_id;
			$t_staff->position			= $request->position;
			$t_staff->periodicity		= $request->periodicity;
			$t_staff->description		= $request->s_description;
			$t_staff->habilities		= $request->habilities;
			$t_staff->experience		= $request->experience;
			$t_staff->save();
			$t_staff->responsibility()->detach();
			if (isset($request->responsibilities) && $request->responsibilities != "") 
			{
				$t_staff->responsibility()->attach($request->responsibilities);
			}
			
			if(isset($request->tfunction))
			{
				App\StaffFunction::where('idStaff',$idStaff)->delete();
				for ($i=0; $i <count($request->tfunction); $i++)
				{
					$t_function					= new App\StaffFunction();
					$t_function->idStaff		= $idStaff;
					$t_function->function		= $request->tfunction[$i];
					$t_function->description	= $request->tdescr[$i];
					$t_function->save();
				}
			}

			if(isset($request->tdesirable))
			{
				App\StaffDesirable::where('idStaff',$idStaff)->delete();
				for ($i=0; $i <count($request->tdesirable); $i++)
				{
					$t_desirable				= new App\StaffDesirable();
					$t_desirable->idStaff		= $idStaff;
					$t_desirable->desirable		= $request->tdesirable[$i];
					$t_desirable->description	= $request->td_descr[$i];
					$t_desirable->save();
				}
			}

			if (isset($request->rq_name) && count($request->rq_name)) 
			{	
				if(isset($request->rq_employee_id))
				{
					foreach($request->rq_employee_id as $employee_id)
					{
						App\StaffDocuments::where('id_staff_employee',$employee_id)->delete();
					}
				}
				for ($i=0; $i < count($request->rq_name); $i++) 
				{
					if ($request->rq_employee_id[$i] == "x") 
					{
						$employee	= new App\StaffEmployee();
					}
					else
					{
						$employee	= App\StaffEmployee::find($request->rq_employee_id[$i]);

						App\StaffAccounts::where('id_staff_employee',$request->rq_employee_id[$i])->delete();
					}
					$employee->name						= $request->rq_name[$i];
					$employee->last_name				= $request->rq_last_name[$i];
					$employee->scnd_last_name			= $request->rq_scnd_last_name[$i];
					$employee->curp						= $request->rq_curp[$i];
					$employee->rfc						= $request->rq_rfc[$i];
					$employee->tax_regime				= $request->rq_tax_regime[$i];
					$employee->imss						= $request->rq_imss[$i];
					$employee->email					= $request->rq_email[$i];
					$employee->phone					= $request->rq_phone[$i];
					$employee->street					= $request->rq_street[$i];
					$employee->number					= $request->rq_number_employee[$i];
					$employee->colony					= $request->rq_colony[$i];
					$employee->cp						= $request->rq_cp[$i];
					$employee->city						= $request->rq_city[$i];
					$employee->state_id					= $request->rq_state[$i];
					$employee->state					= $request->rq_work_state[$i];
					$employee->project					= $request->project_id;
					$employee->wbs_id					= isset($request->rq_work_wbs[$i]) ? $request->rq_work_wbs[$i] : null;
					$employee->enterprise				= $request->rq_work_enterprise[$i];
					$employee->account					= $request->rq_work_account[$i];
					$employee->direction				= $request->rq_work_direction[$i];
					$employee->department				= $request->rq_work_department[$i];
					$employee->position					= $request->rq_work_position[$i];
					$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
					$employee->admissionDate			= isset($request->rq_work_income_date[$i]) && $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_income_date[$i])->format('Y-m-d') : null;
					$employee->imssDate					= isset($request->rq_work_imss_date[$i]) && $request->rq_work_imss_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_imss_date[$i])->format('Y-m-d') : null;
					$employee->downDate					= isset($request->rq_work_down_date[$i]) && $request->rq_work_down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_down_date[$i])->format('Y-m-d') : null;
					$employee->endingDate				= isset($request->rq_work_ending_date[$i]) && $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_ending_date[$i])->format('Y-m-d') : null;
					$employee->reentryDate				= isset($request->rq_work_reentry_date[$i]) && $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
					$employee->workerType				= $request->rq_work_type_employee[$i];
					$employee->regime_id				= $request->rq_regime_employee[$i];
					$employee->workerStatus				= $request->rq_work_status_employee[$i];
					$employee->status_reason			= $request->rq_work_status_reason[$i];
					$employee->status_imss				= $request->rq_work_status_imss[$i];
					$employee->sdi						= $request->rq_work_sdi[$i];
					$employee->periodicity				= $request->rq_work_periodicity[$i];
					$employee->employer_register		= $request->rq_work_employer_register[$i];
					$employee->paymentWay				= $request->rq_work_payment_way[$i];
					$employee->netIncome				= $request->rq_work_net_income[$i];
					$employee->complement				= $request->rq_work_complement[$i];
					$employee->fonacot					= $request->rq_work_fonacot[$i];
					$employee->viatics					= $request->rq_work_viatics[$i];
					$employee->camping					= $request->rq_work_camping[$i];
					$employee->replace					= $request->rq_replace[$i];
					$employee->purpose					= $request->rq_purpose[$i];
					$employee->requeriments				= $request->rq_requeriments[$i];
					$employee->observations				= $request->rq_observations[$i];
					$employee->position_immediate_boss	= $request->rq_work_position_immediate_boss[$i];
					$employee->subdepartment_id			= $request->rq_work_subdepartment[$i];
					$employee->doc_birth_certificate	= $request->rq_doc_birth_certificate[$i];
					$employee->doc_proof_of_address		= $request->rq_doc_proof_of_address[$i];
					$employee->doc_nss					= $request->rq_doc_nss[$i];
					$employee->doc_ine					= $request->rq_doc_ine[$i];
					$employee->doc_curp					= $request->rq_doc_curp[$i];
					$employee->doc_rfc					= $request->rq_doc_rfc[$i];
					$employee->doc_cv					= $request->rq_doc_cv[$i];
					$employee->doc_proof_of_studies		= $request->rq_doc_proof_of_studies[$i];
					$employee->doc_professional_license	= $request->rq_doc_professional_license[$i];
					$employee->computer_required		= $request->rq_computer_required[$i];
					$employee->staff_id					= $t_staff->idStaff;

					if($request->rq_work_infonavit_credit[$i] != ""&& $request->rq_work_infonavit_discount[$i] != "" && $request->rq_work_infonavit_discount_type[$i] != "")
					{
						$employee->infonavitCredit       = $request->rq_work_infonavit_credit[$i];
						$employee->infonavitDiscount     = $request->rq_work_infonavit_discount[$i];
						$employee->infonavitDiscountType = $request->rq_work_infonavit_discount_type[$i];
					}
					if($request->rq_work_alimony_discount[$i] != "" && $request->rq_work_alimony_discount_type[$i] != "")
					{
						$employee->alimonyDiscount     = $request->rq_work_alimony_discount[$i];
						$employee->alimonyDiscountType = $request->rq_work_alimony_discount_type[$i];
					}
					$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
					
					
					$docs = 
					[
						"doc_requisition", 
						"doc_professional_license", 
						"doc_proof_of_studies", 
						"doc_cv", 
						"doc_rfc", 
						"doc_curp", 
						"doc_ine", 
						"doc_nss", 
						"doc_proof_of_address", 
						"doc_birth_certificate"
					];

					foreach($docs as $kind_doc)
					{
						$doc_name 				= "rq_".$kind_doc;
						$employee->$kind_doc	= $request->$doc_name[$i];
					}

					$employee->save();

					$field = "name_other_document_".$i;
					
					if(isset($request->$field) && $request->$field != "")
					{
						for($j = 0; $j < count($request->$field); $j++)
						{
							$name_doc 								= "name_other_document_".$i;
							$path_doc 								= "path_other_document_".$i;
							$t_staff_documents						=	new App\StaffDocuments();
							$t_staff_documents->name				=	$request->$name_doc[$j];
							$t_staff_documents->path				=	$request->$path_doc[$j];
							$t_staff_documents->id_staff_employee	=	$employee->id;
							$t_staff_documents->save();
						}
					}
					
					$beneficiary	= 'beneficiary_'.$i;
					$type			= 'type_'.$i;
					$alias			= 'alias_'.$i;
					$clabe			= 'clabe_'.$i;
					$account		= 'account_'.$i;
					$cardNumber		= 'cardNumber_'.$i;
					$idCatBank		= 'idCatBank_'.$i;
					$branch			= 'branch_'.$i;
					$idEmployee		= 'idEmployee_'.$i;

					if(isset($request->$idEmployee) && count($request->$idEmployee) > 0)
					{
						foreach ($request->$idEmployee as $k => $e)
						{
							$empAcc              = new App\StaffAccounts();
							$empAcc->id_staff_employee  = $employee->id;
							$empAcc->beneficiary = $request->$beneficiary[$k];
							$empAcc->type        = $request->$type[$k];
							$empAcc->alias       = $request->$alias[$k];
							$empAcc->clabe       = $request->$clabe[$k];
							$empAcc->account     = $request->$account[$k];
							$empAcc->cardNumber  = $request->$cardNumber[$k];
							$empAcc->id_catbank  = $request->$idCatBank[$k];
							$empAcc->branch      = $request->$branch[$k];
							$empAcc->recorder    = Auth::user()->id;
							$empAcc->save();
						}
					}

				}
			}

			if(isset($request->delete_employee) && $request->delete_employee != "")
			{
				foreach($request->delete_employee as $id_employee)
				{
					App\StaffDocuments::where('id_staff_employee',$id_employee)->delete();
					App\StaffAccounts::where('id_staff_employee',$id_employee)->delete();
					App\StaffEmployee::find($id_employee)->delete();
				}
			}
			
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 75);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',75);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',75);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',75)
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
				   		$kind 			= "Personal";
				   		$status 		= "Revisar";
				   		$date 			= Carbon::now();
				   		$url 			= route('staff.review.edit',['id'=>$id]);
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
			return redirect('administration/staff')->with('alert',$alert);
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
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= 4;
			$t_request->fDate			= Carbon::now();
			$t_request->status			= 2;
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idArea			= $request->area_id;
			$t_request->idDepartment	= $request->department_id;
			$t_request->idRequest		= $request->user_id;
			$t_request->idProject		= $request->project_id;
			$t_request->save();
			$Staff						= App\Staff::where('idFolio',$t_request->folio)
													->where('idKind',$t_request->kind)
													->get();
			foreach ($Staff as $key => $value)
			{
				$idStaff = $value->idStaff;
			}
			$t_staff					= App\Staff::find($idStaff);
			$t_staff->title 		  	= $request->title;
			$t_staff->datetitle 	  	= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$t_staff->boss				= $request->boss_id;
			$t_staff->schedule_start	= $request->schedule_start;
			$t_staff->schedule_end		= $request->schedule_end;
			$t_staff->minSalary			= $request->minSalary;
			$t_staff->maxSalary			= $request->maxSalary;
			$t_staff->reason			= $request->reason;
			$t_staff->role_id			= $request->role_id;
			$t_staff->position			= $request->position;
			$t_staff->periodicity		= $request->periodicity;
			$t_staff->description		= $request->s_description;
			$t_staff->habilities		= $request->habilities;
			$t_staff->experience		= $request->experience;
			$t_staff->save();

			$t_staff->responsibility()->detach();
			if (isset($request->responsibilities) && $request->responsibilities != "") 
			{
				$t_staff->responsibility()->attach($request->responsibilities);
			}
			if(isset($request->tfunction))
			{
				App\StaffFunction::where('idStaff',$idStaff)->delete();
				if($request->tfunction!='')
				{
					for ($i=0; $i <count($request->tfunction); $i++)
					{
						$t_function					= new App\StaffFunction();
						$t_function->idStaff		= $idStaff;
						$t_function->function		= $request->tfunction[$i];
						$t_function->description	= $request->tdescr[$i];
						$t_function->save();
					}
				}
			}
			if(isset($request->tdesirable))
			{
				App\StaffDesirable::where('idStaff',$idStaff)->delete();
				if($request->tdesirable)
				{
					for ($i=0; $i <count($request->tdesirable); $i++)
					{
						$t_desirable				= new App\StaffDesirable();
						$t_desirable->idStaff		= $idStaff;
						$t_desirable->desirable		= $request->tdesirable[$i];
						$t_desirable->description	= $request->td_descr[$i];
						$t_desirable->save();
					}
				}
			}

			
			if (isset($request->rq_name) && count($request->rq_name)) 
			{
				if(isset($request->rq_employee_id))
				{
					foreach($request->rq_employee_id as $employee_id)
					{
						App\StaffDocuments::where('id_staff_employee',$employee_id)->delete();
					}
				}
				for ($i=0; $i < count($request->rq_name); $i++)
				{
					if ($request->rq_employee_id[$i] == "x") 
					{
						$employee	= new App\StaffEmployee();
					}
					else
					{
						$employee	= App\StaffEmployee::find($request->rq_employee_id[$i]);
						App\StaffAccounts::where('id_staff_employee',$request->rq_employee_id[$i])->delete();
					}
					$employee->name						= $request->rq_name[$i];
					$employee->last_name				= $request->rq_last_name[$i];
					$employee->scnd_last_name			= $request->rq_scnd_last_name[$i];
					$employee->curp						= $request->rq_curp[$i];
					$employee->rfc						= $request->rq_rfc[$i];
					$employee->tax_regime				= $request->rq_tax_regime[$i];
					$employee->imss						= $request->rq_imss[$i];
					$employee->email					= $request->rq_email[$i];
					$employee->phone					= $request->rq_phone[$i];
					$employee->street					= $request->rq_street[$i];
					$employee->number					= $request->rq_number_employee[$i];
					$employee->colony					= $request->rq_colony[$i];
					$employee->cp						= $request->rq_cp[$i];
					$employee->city						= $request->rq_city[$i];
					$employee->state_id					= $request->rq_state[$i];
					$employee->state					= $request->rq_work_state[$i];
					$employee->project					= $request->project_id;
					$employee->wbs_id					= isset($request->rq_work_wbs[$i]) ? $request->rq_work_wbs[$i] : null;
					$employee->enterprise				= $request->rq_work_enterprise[$i];
					$employee->account					= $request->rq_work_account[$i];
					$employee->direction				= $request->rq_work_direction[$i];
					$employee->department				= $request->rq_work_department[$i];
					$employee->position					= $request->rq_work_position[$i];
					$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
					$employee->admissionDate			= isset($request->rq_work_income_date[$i]) && $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_income_date[$i])->format('Y-m-d') : null;
					$employee->imssDate					= isset($request->rq_work_imss_date[$i]) && $request->rq_work_imss_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_imss_date[$i])->format('Y-m-d') : null;
					$employee->downDate					= isset($request->rq_work_down_date[$i]) && $request->rq_work_down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_down_date[$i])->format('Y-m-d') : null;
					$employee->endingDate				= isset($request->rq_work_ending_date[$i]) && $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_ending_date[$i])->format('Y-m-d') : null;
					$employee->reentryDate				= isset($request->rq_work_reentry_date[$i]) && $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
					$employee->workerType				= $request->rq_work_type_employee[$i];
					$employee->regime_id				= $request->rq_regime_employee[$i];
					$employee->workerStatus				= $request->rq_work_status_employee[$i];
					$employee->status_reason			= $request->rq_work_status_reason[$i];
					$employee->status_imss				= $request->rq_work_status_imss[$i];
					$employee->sdi						= $request->rq_work_sdi[$i];
					$employee->periodicity				= $request->rq_work_periodicity[$i];
					$employee->employer_register		= $request->rq_work_employer_register[$i];
					$employee->paymentWay				= $request->rq_work_payment_way[$i];
					$employee->netIncome				= $request->rq_work_net_income[$i];
					$employee->complement				= $request->rq_work_complement[$i];
					$employee->fonacot					= $request->rq_work_fonacot[$i];
					$employee->viatics					= $request->rq_work_viatics[$i];
					$employee->camping					= $request->rq_work_camping[$i];
					$employee->replace					= $request->rq_replace[$i];
					$employee->purpose					= $request->rq_purpose[$i];
					$employee->requeriments				= $request->rq_requeriments[$i];
					$employee->observations				= $request->rq_observations[$i];
					$employee->position_immediate_boss	= $request->rq_work_position_immediate_boss[$i];
					$employee->subdepartment_id			= $request->rq_work_subdepartment[$i];
					$employee->doc_birth_certificate	= $request->rq_doc_birth_certificate[$i];
					$employee->doc_proof_of_address		= $request->rq_doc_proof_of_address[$i];
					$employee->doc_nss					= $request->rq_doc_nss[$i];
					$employee->doc_ine					= $request->rq_doc_ine[$i];
					$employee->doc_curp					= $request->rq_doc_curp[$i];
					$employee->doc_rfc					= $request->rq_doc_rfc[$i];
					$employee->doc_cv					= $request->rq_doc_cv[$i];
					$employee->doc_proof_of_studies		= $request->rq_doc_proof_of_studies[$i];
					$employee->doc_professional_license	= $request->rq_doc_professional_license[$i];
					$employee->computer_required		= $request->rq_computer_required[$i];
					$employee->staff_id					= $t_staff->idStaff;

					if($request->rq_work_infonavit_credit[$i] != ""&& $request->rq_work_infonavit_discount[$i] != "" && $request->rq_work_infonavit_discount_type[$i] != "")
					{
						$employee->infonavitCredit       = $request->rq_work_infonavit_credit[$i];
						$employee->infonavitDiscount     = $request->rq_work_infonavit_discount[$i];
						$employee->infonavitDiscountType = $request->rq_work_infonavit_discount_type[$i];
					}
					if($request->rq_work_alimony_discount[$i] != "" && $request->rq_work_alimony_discount_type[$i] != "")
					{
						$employee->alimonyDiscount     = $request->rq_work_alimony_discount[$i];
						$employee->alimonyDiscountType = $request->rq_work_alimony_discount_type[$i];
					}
					$employee->qualified_employee 	= $request->rq_qualified_employee[$i];

					$docs = 
					[
						"doc_requisition", 
						"doc_professional_license", 
						"doc_proof_of_studies", 
						"doc_cv", 
						"doc_rfc", 
						"doc_curp", 
						"doc_ine", 
						"doc_nss", 
						"doc_proof_of_address", 
						"doc_birth_certificate"
					];
					
					foreach($docs as $kind_doc)
					{
						$doc_name 			= "rq_".$kind_doc;						
						$employee->$kind_doc= $request->$doc_name[$i];
					}

					$employee->save();
					$field = "name_other_document_".$i;
					
					if(isset($request->$field) && $request->$field != "")
					{
						for($j = 0; $j < count($request->$field); $j++)
						{
							$name_doc 								= "name_other_document_".$i;
							$path_doc 								= "path_other_document_".$i;
							$t_staff_documents						=	new App\StaffDocuments();
							$t_staff_documents->name				=	$request->$name_doc[$j];
							$t_staff_documents->path				=	$request->$path_doc[$j];
							$t_staff_documents->id_staff_employee	=	$employee->id;
							$t_staff_documents->save();
						}
					}

					$beneficiary	= 'beneficiary_'.$i;
					$type			= 'type_'.$i;
					$alias			= 'alias_'.$i;
					$clabe			= 'clabe_'.$i;
					$account		= 'account_'.$i;
					$cardNumber		= 'cardNumber_'.$i;
					$idCatBank		= 'idCatBank_'.$i;
					$branch			= 'branch_'.$i;
					$idEmployee		= 'idEmployee_'.$i;

					if(isset($request->$idEmployee) && count($request->$idEmployee) > 0)
					{
						foreach ($request->$idEmployee as $k => $e)
						{
							$empAcc              		= new App\StaffAccounts();
							$empAcc->id_staff_employee	= $employee->id;
							$empAcc->beneficiary 		= $request->$beneficiary[$k];
							$empAcc->type        		= $request->$type[$k];
							$empAcc->alias       		= $request->$alias[$k];
							$empAcc->clabe       		= $request->$clabe[$k];
							$empAcc->account     		= $request->$account[$k];
							$empAcc->cardNumber  		= $request->$cardNumber[$k];
							$empAcc->id_catbank  		= $request->$idCatBank[$k];
							$empAcc->branch      		= $request->$branch[$k];
							$empAcc->recorder    		= Auth::user()->id;
							$empAcc->save();
						}
					}
				}
				
			}
			
			if(isset($request->delete_employee) && $request->delete_employee != "")
			{
				foreach($request->delete_employee as $id_employee)
				{
					App\StaffDocuments::where('id_staff_employee',$id_employee)->delete();
					App\StaffAccounts::where('id_staff_employee',$id_employee)->delete();
					App\StaffEmployee::find($id_employee)->delete();
				}
			}

			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('staff.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',75)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid 	= $request->enterpriseid;

			$requests		= App\RequestModel::where('kind',4)
								->where('status',3)
								->whereIn('idDepartment',Auth::user()->inChargeDep(75)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(75)->pluck('enterprise_id'))
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterprise',$enterpriseid);
									}
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
				view('administracion.personal.revision',
					[
						'id'		   => $data['father'],
						'title'		   => $data['name'],
						'details'	   => $data['details'],
						'child_id'	   => $this->module_id,
						'option_id'	   => 75,
						'requests'	   => $requests,
						'folio' 	   => $folio, 
						'name' 		   => $name, 
						'mindate' 	   => $request->mindate, 
						'maxdate' 	   => $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(75), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',75)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$thisModule			= App\Module::find(75);
			$users				= App\User::orderName()->where('status','ACTIVE')->get();
			$areas				= App\Area::orderName()->where('status','ACTIVE')->get();
			$enterprises   	 	= App\Enterprise::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(75)->pluck('enterprise_id'))->get();
			$departments    	= App\Department::orderName()->where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(75)->pluck('departament_id'))->get();
			$projects			= App\Project::orderName()->get();
			$responsibilities	= App\Responsibility::orderName()->get();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();
			$roles				= App\Role::where('status','ACTIVE')->get();
			$labels				= App\Label::orderName()->get();
			$request			= App\RequestModel::where('kind',4)
								->where('status',3)
								->whereIn('idDepartment',Auth::user()->inChargeDep(75)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(75)->pluck('enterprise_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.personal.revisioncambio',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $thisModule['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 75,
						'enterprises'		=> $enterprises,
						'areas'				=> $areas,
						'departments'		=> $departments,
						'users'				=> $users,
						'responsibilities'	=> $responsibilities,
						'projects'			=> $projects,
						'minSalary'			=> $minSalary,
						'maxSalary'			=> $maxSalary,
						'roles' 			=> $roles,
						'request'			=> $request,
						'labels'			=> $labels
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/staff/review')->with('alert',$alert);
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
			$data	= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4") 
				{
					$review->status			= $request->status;
					$review->idEnterpriseR	= $request->idEnterpriseR;
					$review->idDepartamentR	= $request->idDepartmentR;
					$review->idAreaR		= $request->idAreaR;
					$review->idProjectR		= $request->project_id;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate 	= Carbon::now();
					$review->save();

					if ($request->idLabels != "") 
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'4'));
					}

					$staff = App\RequestModel::find($id);
					foreach($staff->staff as $staff_employees)
					{
						foreach($staff_employees->staffEmployees as $employee)
						{
							$t_staff_employee 			= App\staffEmployee::find($employee->id);
							$t_staff_employee->project	= $request->project_id;
							$t_staff_employee->save();
						}
					}
					
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 76);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',76);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',76);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',76)
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
						   		$kind 			= "Personal";
						   		$status 		= "Autorizar";
						   		$date 			= Carbon::now();
						   		$url 			= route('staff.authorization.edit',['id'=>$id]);
						   		$subject 		= "Solicitud por Autorizar";
						   		$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
					   			Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						   	}
						   	$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				   		}
				   		catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
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
						   		$kind 			= "Computo";
								$status 		= "RECHAZADA";
						   		$date 			= Carbon::now();
						   		$url 			= route('staff.follow.edit',['id'=>$id]);
						   		$subject 		= "Estado de Solicitud";
						   		$requestUser	= null;
					   			Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						   	}
						   	$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				   		}
				   		catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
				   	}
				}

				
			}
			return searchRedirect(75, $alert, 'administration/staff');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',76)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate   		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid 	= $request->enterpriseid;

			$requests		= App\RequestModel::where('kind',4)
								->where('status',4)
								->whereIn('idDepartment',Auth::user()->inChargeDep(76)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(76)->pluck('enterprise_id'))
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
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
				view('administracion.personal.autorizacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 76,
						'requests'	=> $requests,
						'folio' 	=> $folio, 
						'name' 		=> $name, 
						'mindate' 	=> $request->mindate, 
						'maxdate' 	=> $request->maxdate,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(76), 2880
			);
		}
		else
		{
			return redirect('/'); 
		}
	}

	public function account(Request $request)
	{
		if($request->ajax())
		{
			$result             = array();
			$result['accounts'] = array();
			$result['er']       = array();
			$accounts           = App\Account::where('idEnterprise',$request->enterprise)
				->where(function($q)
				{
					$q->where('account','LIKE','5102%')
					->orWhere('account','LIKE','5303%')
					->orWhere('account','LIKE','5403%');
				})
				->where('selectable',1);
			$result['accounts'] = $accounts->get();
			$enterprise         = App\Enterprise::find($request->enterprise);
			foreach ($enterprise->employerRegister as $er)
			{
				$result['er'][] = $er->employer_register;
			}
			return response($result);
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',76)->count()>0) 
		{
			$data				= App\Module::find($this->module_id);
			$enterprises		= App\Enterprise::where('status','ACTIVE')->get();
			$areas				= App\Area::where('status','ACTIVE')->get();
			$departments		= App\Department::where('status','ACTIVE')->get();
			$projects			= App\Project::all();
			$responsibilities	= App\Responsibility::all();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();
			$roles				= App\Role::where('status','ACTIVE')->get();
			$request			= App\RequestModel::where('kind',4)
								->where('status',4)
								->whereIn('idDepartment',Auth::user()->inChargeDep(76)->pluck('departament_id'))
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(76)->pluck('enterprise_id'))
								->find($id);
			if ($request != "") 
			{
				return view('administracion.personal.autorizacioncambio',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 76,
						'enterprises' 		=> $enterprises,
						'areas'				=> $areas,
						'departments'		=> $departments,
						'request'			=> $request,
						'roles' 			=> $roles,
						'responsibilities'	=> $responsibilities,
						'minSalary'			=> $minSalary,
						'maxSalary'			=> $maxSalary,
						'projects'			=> $projects
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'success');";
				return redirect('administration/staff/authorization')->with('alert',$alert);
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
			$data			= App\Module::find($this->module_id);
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'success');";
			}
			else
			{
				$authorize						= App\RequestModel::find($id);
				$authorize->status				= $request->status;
				$authorize->idAuthorize			= Auth::user()->id;
				$authorize->authorizeComment	= $request->authorizeCommentA;
				$authorize->authorizeDate 		= Carbon::now();
				$authorize->save();

				$alert 			= "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
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
				
				$user 			= App\User::find($authorize->idRequest);
			   	if ($emailRequest != "") 
			   	{
			   		try
			   		{
			   			foreach ($emailRequest as $email) 
					   	{
					   		$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
					   		$to 			= $email->email;
					   		$kind 			= "Personal";
					   		if ($request->status == 5) 
					   		{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
					   		$date 			= Carbon::now();
					   		$url 			= route('staff.follow.edit',['id'=>$id]);
					   		$subject 		= "Estado de Solicitud";
					   		$requestUser 	= null;
					   		Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					   	}
					   	$alert 			= "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
			   		}
			   		catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
			   	}
			}
			return searchRedirect(76, $alert, 'administration/staff');
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',74)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',74)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',74)->first()->global_permission;
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
								request_models.folio, 
								staff.title,
								DATE_FORMAT(staff.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								CONCAT_WS(" ",staff_employees.name, staff_employees.last_name, staff_employees.scnd_last_name) as employeeName,
								staff_employees.position as employeePosition
							')
							->leftJoin('staff', 'staff.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('staff_employees', 'staff_employees.staff_id', 'staff.idStaff')
							->where('request_models.kind',4)
							->where(function($q) 
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(74)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
							})
							->where(function ($q) 
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(74)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
							})
							->where(function ($q) use ($global_permission)
							{
								if ($global_permission == 0) 
								{
									$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
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
									$query->where(function($queryU) use ($name)
									{
										$queryU->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-personal.xlsx');
			$writer->getCurrentSheet()->setName('Seguimiento');

			$headers = ['Reporte de seguimiento de personal','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Nombre empleado', 'Puesto'];
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
					$request->title		        = '';
					$request->datetitle		    = '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->status		    = '';
					$request->date		        = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, []))
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
		if(Auth::user()->module->where('id',75)->count()>0)
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
								request_models.folio, 
								staff.title,
								DATE_FORMAT(staff.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								CONCAT_WS(" ",staff_employees.name, staff_employees.last_name, staff_employees.scnd_last_name) as employeeName,
								staff_employees.position as employeePosition
							')
							->leftJoin('staff', 'staff.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('staff_employees', 'staff_employees.staff_id', 'staff.idStaff')
							->where('request_models.kind',4)
							->where('request_models.status',3)
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(75)->pluck('departament_id'))
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(75)->pluck('enterprise_id'))
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
									$query->where(function($queryU) use ($name)
									{
										$queryU->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
									$query->whereBetween('request_models.fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-personal.xlsx');
			$writer->getCurrentSheet()->setName('Revisión');

			$headers = ['Reporte de revisión de personal','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Nombre empleado', 'Puesto'];
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
					$request->title		        = '';
					$request->datetitle		    = '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->status		    = '';
					$request->date		        = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, []))
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
		if(Auth::user()->module->where('id',76)->count()>0)
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
								request_models.folio, 
								staff.title,
								DATE_FORMAT(staff.datetitle, "%d-%m-%Y") as datetitle,
								CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
								CONCAT_WS(" ",elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborateUser,
								IF(reviewedEnterprise.name IS NULL,requestEnterprise.name, reviewedEnterprise.name) as enterpriseName,
								status_requests.description as status,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								CONCAT_WS(" ",staff_employees.name, staff_employees.last_name, staff_employees.scnd_last_name) as employeeName,
								staff_employees.position as employeePosition
							')
							->leftJoin('staff', 'staff.idFolio', 'request_models.folio')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('enterprises as reviewedEnterprise','reviewedEnterprise.id','request_models.idEnterpriseR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('staff_employees', 'staff_employees.staff_id', 'staff.idStaff')
							->where('request_models.kind',4)
							->where('request_models.status',4)
							->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(76)->pluck('departament_id'))
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(76)->pluck('enterprise_id'))
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
									$query->where(function($queryU) use ($name)
									{
										$queryU->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
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
									$query->whereBetween('request_models..fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-personal.xlsx');
			$writer->getCurrentSheet()->setName('Autorización');

			$headers = ['Reporte de autorización de personal','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Título','Fecha','Solicitante','Elaborado por','Empresa','Estado','Fecha de elaboración','Nombre empleado', 'Puesto'];
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
					$request->title		        = '';
					$request->datetitle		    = '';
					$request->requestUser		= '';
					$request->elaborateUser		= '';
					$request->enterpriseName	= '';
					$request->status		    = '';
					$request->date		        = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, []))
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
