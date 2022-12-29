<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Module;
use App\RequisitionEmployee;
use DB;
use Lang;
use Carbon\Carbon;
use App\RequestModel;
use App\RealEmployee;
use App\WorkerData;
use App\EmployeeAccount;
use App\StaffEmployee;
use App\StaffAccount;
use App\CatTaxRegime;
use App\CatZipCode;
use App\RealEmployeeDocument;
use Excel;
use PHPExcel_Cell;
use PHPExcel_Style_Protection;

class AdministrationEmployeeController extends Controller
{
	protected $module_id = 317;

	public function index()
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data  = Module::find($this->module_id);
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
			return redirect('/error');
		}
	}

	public function pending(Request $request)
	{
		if (Auth::user()->module->where('id',318)->count()>0) 
		{
			$data 		= Module::find($this->module_id);
			
			$pending = RequestModel::
				selectRaw('request_models.kind,request_models.folio,projects.proyectName,IFNULL(requisition_employees.id, staff_employees.id) as employee_id,IFNULL(requisition_employees.name, staff_employees.name) as name,IFNULL(requisition_employees.last_name, staff_employees.last_name) as last_name,IFNULL(requisition_employees.scnd_last_name, staff_employees.scnd_last_name) as scnd_last_name,IFNULL(requisition_employees.admissionDate, staff_employees.admissionDate) as admissionDate,IFNULL(requisition_employees.position, staff_employees.position) as position,IFNULL(requisition_employees.curp, staff_employees.curp) as curp')
				->leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
				->leftJoin('requisition_employees', 'requisitions.id', 'requisition_employees.requisition_id')
				->leftJoin('staff', 'request_models.folio', 'staff.idFolio')
				->leftJoin('staff_employees', 'staff.idStaff', 'staff_employees.staff_id')
				->leftJoin('projects','request_models.idProject','projects.idproyect')
				->where('request_models.status',5)
				->where(function($q)
				{
					$q->where('staff_employees.status_personal',0)
						->orWhere('requisition_employees.status_personal',0);
				})
				->where(function($query) use ($request)
				{
					if ($request->name != "") 
					{
						$query->where(function($q) use ($request)
						{
							$q->whereRaw("CONCAT_WS(\" \",requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name) LIKE \"%".preg_replace("/\s+/", "%", $request->name)."%\"")
								->orWhereRaw("CONCAT_WS(\" \",staff_employees.name,staff_employees.last_name,staff_employees.scnd_last_name) LIKE \"%".preg_replace("/\s+/", "%", $request->name)."%\"");
						});
					}

					if ($request->position != "") 
					{
						$query->where(function($q) use ($request)
						{
							$q->where('requisition_employees.position','LIKE','%'.$request->position.'%')
								->orWhere('staff_employees.position','LIKE','%'.$request->position.'%');
						});
					}

					if ($request->curp != "") 
					{
						$query->where(function($q) use ($request)
						{
							$q->where('requisition_employees.curp','LIKE','%'.$request->curp.'%')
								->orWhere('staff_employees.curp','LIKE','%'.$request->curp.'%');
						});
					}
					if ($request->project_id != "") 
					{
						$query->whereIn('request_models.idProject',$request->project_id);
					}
					if ($request->folio != "") 
					{
						$query->where('request_models.folio',$request->folio);
					}
				})
				->orderBy('request_models.folio','DESC')
				->paginate(10);
			return view('administracion.empleados.pendientes',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 318,
				'name'			=> $request->name,
				'folio'			=> $request->folio,
				'project_id'	=> $request->project_id,
				'position'		=> $request->position,
				'curp'			=> $request->curp,
				'pending'		=> $pending
			]);	
		}
		else
		{
			return redirect('error');
		}
	}

	public function editEmployee($employee_id, RequestModel $request_model)
	{	
		if (Auth::user()->module->where('id',318)->count()>0) 
		{
			$data	= Module::find($this->module_id);
			if ($request_model->kind == 19) 
			{
				$employee_edit = RequisitionEmployee::find($employee_id);
			}
			if($request_model->kind == 4)
			{
				$employee_edit = StaffEmployee::find($employee_id);
			}

			return view('administracion.empleados.editar',
			[
				'employee_edit'	=> $employee_edit,
				'request_model'	=> $request_model,
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 318,
			]);
		}
		else
		{
			return redirect('error');
		}
	}


	public function approvedEmployee(Request $request)
	{
		if(Auth::user()->module->where('id',318)->count()>0)
		{
			$check_employee		= RealEmployee::where('curp',$request->curp)->get();
			$real_employee_id	= $check_employee->count() > 0 ? $check_employee->first()->id : "";

			$request_model = RequestModel::find($request->folio);
			if ($request_model->kind == 19) 
			{
				$employee_edit = RequisitionEmployee::find($request->employee);
				if ($employee_edit->status_personal == 1) 
				{
					$alert	= "swal('','".Lang::get("messages.employee_approved")."', 'info');";
					return redirect()->route('administration.employee.pending')->with('alert',$alert);
				}
				$employee_edit->status_personal = 1;
				$employee_edit->save();
			}
			if ($request_model->kind == 4) 
			{
				$employee_edit = StaffEmployee::find($request->employee);
				if ($employee_edit->status_personal == 1) 
				{
					$alert	= "swal('','".Lang::get("messages.employee_approved")."', 'info');";
					return redirect()->route('administration.employee.pending')->with('alert',$alert);
				}
				$employee_edit->status_personal = 1;
				$employee_edit->save();
			}
			if ($real_employee_id != "") 
			{
				$employee						= RealEmployee::find($real_employee_id);

				WorkerData::where('idEmployee',$employee->id)
				->update(
				[
					'visible' => 0
				]);
			}
			else
			{
				$employee						= new RealEmployee();
			}
			$employee->name						= $request->name;
			$employee->last_name				= $request->last_name;
			$employee->scnd_last_name			= $request->scnd_last_name;
			$employee->curp						= $request->curp;
			$employee->rfc						= $request->rfc;
			$employee->tax_regime				= $request->tax_regime;
			$employee->imss						= $request->imss;
			$employee->email					= $request->email;
			$employee->phone					= $request->phone;
			$employee->street					= $request->street;
			$employee->number					= $request->number;
			$employee->colony					= $request->colony;
			$employee->cp						= $request->cp;
			$employee->city						= $request->city;
			$employee->state_id					= $request->state;
			$employee->sys_user					= $request->sys_user;
			$employee->doc_birth_certificate	= $request->doc_birth_certificate;
			$employee->doc_proof_of_address		= $request->doc_proof_of_address;
			$employee->doc_nss					= $request->doc_nss;
			$employee->doc_ine					= $request->doc_ine;
			$employee->doc_curp					= $request->doc_curp;
			$employee->doc_rfc					= $request->doc_rfc;
			$employee->doc_cv					= $request->doc_cv;
			$employee->doc_proof_of_studies		= $request->doc_proof_of_studies;
			$employee->doc_professional_license	= $request->doc_professional_license;
			$employee->doc_requisition			= $request->doc_requisition;
			$employee->replace					= $request->replace;
			$employee->qualified_employee		= $request->qualified_employee;
			$employee->purpose					= $request->purpose;
			$employee->requeriments				= $request->requeriments;
			$employee->observations				= $request->observations;
			$employee->save();
			$working                    = new WorkerData();
			$working->idEmployee        = $employee->id;
			$working->state             = $request->work_state;
			$working->project           = $request->work_project;
			$working->enterprise        = $request->work_enterprise;
			$working->account           = $request->work_account;
			$working->direction         = $request->work_direction;
			$working->department        = $request->work_department;
			$working->position          = $request->work_position;
			$working->immediate_boss    = $request->work_immediate_boss;
			$working->admissionDate     = $request->work_income_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_income_date)->format('Y-m-d')	: null;
			$working->imssDate          = $request->work_imss_date		!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_imss_date)->format('Y-m-d')	: null;
			$working->downDate          = $request->work_down_date		!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_down_date)->format('Y-m-d')	: null;
			$working->endingDate        = $request->work_ending_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_ending_date)->format('Y-m-d')	: null;
			$working->reentryDate       = $request->work_reentry_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_reentry_date)->format('Y-m-d')	: null;
			$working->workerType        = $request->work_type_employee;
			$working->regime_id         = $request->regime_employee;
			$working->workerStatus      = $request->work_status_employee;
			$working->status_reason     = $request->work_status_reason;
			$working->status_imss       = $request->work_status_imss;
			$working->sdi               = $request->work_sdi;
			$working->periodicity       = $request->work_periodicity;
			$working->employer_register = $request->work_employer_register;
			$working->paymentWay        = $request->work_payment_way;
			$working->netIncome         = $request->work_net_income;
			$working->complement        = $request->work_complement;
			$working->fonacot           = $request->work_fonacot;
			$working->nomina            = $request->work_nomina;
			$working->bono              = $request->work_bonus;
			$working->recorder          = Auth::user()->id;
			$working->admissionDateOld  = $request->work_income_date_old != "" ? Carbon::createFromFormat('d-m-Y', $request->work_income_date_old)->format('Y-m-d')	: null;
			$working->enterpriseOld     = $request->work_enterprise_old;
			$working->viatics           = $request->viatics;
			$working->camping           = $request->camping;
			$working->position_immediate_boss = $request->position_immediate_boss;

			if(isset($request->infonavit))
			{
				$working->infonavitCredit       = $request->work_infonavit_credit;
				$working->infonavitDiscount     = $request->work_infonavit_discount;
				$working->infonavitDiscountType = $request->work_infonavit_discount_type;
			}
			if(isset($request->alimony))
			{
				$working->alimonyDiscount     = $request->work_alimony_discount;
				$working->alimonyDiscountType = $request->work_alimony_discount_type;
			}
			$working->save();
			if(isset($request->work_place) && count($request->work_place) > 0)
			{
				$working->places()->attach($request->work_place);
			}
			if(isset($request->work_wbs) && count($request->work_wbs) > 0)
			{
				$working->employeeHasWbs()->attach($request->work_wbs);
			}
			if(isset($request->work_subdepartment) && count($request->work_subdepartment) > 0)
			{
				$working->employeeHasSubdepartment()->attach($request->work_subdepartment);
			}
			if(isset($request->idEmployeeBank) && count($request->idEmployeeBank) > 0)
			{
				foreach ($request->idEmployeeBank as $k => $e)
				{
					if($request->idEmployeeBank[$k] == 'x')
					{
						$check_account 	= EmployeeAccount::where('account',$request->account[$k])
							->where('clabe',$request->clabe[$k])
							->where('idCatBank',$request->bank[$k])
							->where('cardNumber',$request->card[$k])
							->count();

						if ($check_account == 0) 
						{
							$empAcc              = new EmployeeAccount();
							$empAcc->idEmployee  = $employee->id;
							$empAcc->beneficiary = $request->beneficiary[$k];
							$empAcc->type        = $request->type_account[$k];
							$empAcc->alias       = $request->alias[$k];
							$empAcc->clabe       = $request->clabe[$k];
							$empAcc->account     = $request->account[$k];
							$empAcc->cardNumber  = $request->card[$k];
							$empAcc->idCatBank   = $request->bank[$k];
							$empAcc->branch      = $request->branch[$k];
							$empAcc->recorder    = Auth::user()->id;
							$empAcc->visible     = 1;
							$empAcc->save();
						}
					}
				}
			}
			/*
			if ($request_model->kind == 19 && $employee_edit->documents()->exists()) 
			{
				foreach($employee_edit->documents as $doc)
				{ 
					$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
								->where('path',$doc->path)
								->where('real_employee_id',$employee->id)
								->count();
					if ($checkDoc == 0) 
					{
						$other						= new RealEmployeeDocument();
						$other->name				= $doc->name;
						$other->path				= $doc->path;
						$other->real_employee_id	= $employee->id;
						$other->save();
					}
				}
			}
			if($request_model->kind == 4 && $employee_edit->staffDocuments->count() > 0)
			{
				foreach($employee_edit->staffDocuments as $doc)
				{ 
					$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
								->where('path',$doc->path)
								->where('real_employee_id',$employee->id)
								->count();
					if ($checkDoc == 0) 
					{
						$other						= new RealEmployeeDocument();
						$other->name				= $doc->name;
						$other->path				= $doc->path;
						$other->real_employee_id	= $employee->id;
						$other->save();
					}
				}
			}
			*/
			if (isset($request->name_other_document) && count($request->name_other_document)>0) 
			{
				for ($i=0; $i < count($request->name_other_document); $i++) 
				{ 
					$checkDoc 	= RealEmployeeDocument::where('name',$request->name_other_document[$i])
						->where('path',$request->path_other_document[$i])
						->where('real_employee_id',$employee->id)
						->count();
					if ($checkDoc == 0) 
					{
						$other                   = new RealEmployeeDocument();
						$other->name             = $request->name_other_document[$i];
						$other->path             = $request->path_other_document[$i];
						$other->real_employee_id = $employee->id;
						$other->save();
					}
				}
			}

			if(isset($request->delete_other_documents) && count($request->delete_other_documents) > 0)
			{
				for($i = 0; $i < count($request->delete_other_documents); $i++)
				{
					RealEmployeeDocument::where('path', $request->delete_other_documents[$i])->delete();
				}
			}

			$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
			return redirect()->route('administration.employee.pending')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function updateEmployee(Request $request, RealEmployee $employee)
	{
		if(Auth::user()->module->where('id',318)->count()>0)
		{
			$request_model = RequestModel::find($request->folio);
			if ($request_model->kind == 19) 
			{
				$employee_edit = RequisitionEmployee::find($request->employee);
				if ($employee_edit->status_personal == 1) 
				{
					$alert	= "swal('','".Lang::get("messages.employee_approved")."', 'info');";
					return redirect()->route('administration.employee.pending')->with('alert',$alert);
				}
				$employee_edit->status_personal = 1;
				$employee_edit->save();
			}
			if ($request_model->kind == 4) 
			{
				$employee_edit = StaffEmployee::find($request->employee);
				if ($employee_edit->status_personal == 1) 
				{
					$alert	= "swal('','".Lang::get("messages.employee_approved")."', 'info');";
					return redirect()->route('administration.employee.pending')->with('alert',$alert);
				}
				$employee_edit->status_personal = 1;
				$employee_edit->save();
			}

			$employee							= RealEmployee::find($employee->id);
			$employee->name						= $request->name;
			$employee->last_name				= $request->last_name;
			$employee->scnd_last_name			= $request->scnd_last_name;
			$employee->curp						= $request->curp;
			$employee->rfc						= $request->rfc;
			$employee->tax_regime				= $request->tax_regime;
			$employee->imss						= $request->imss;
			$employee->street					= $request->street;
			$employee->email					= $request->email;
			$employee->phone					= $request->phone;
			$employee->number					= $request->number;
			$employee->colony					= $request->colony;
			$employee->cp						= $request->cp;
			$employee->city						= $request->city;
			$employee->state_id					= $request->state;
			$employee->sys_user					= $request->sys_user;
			$employee->doc_birth_certificate	= $request->doc_birth_certificate;
			$employee->doc_proof_of_address		= $request->doc_proof_of_address;
			$employee->doc_nss					= $request->doc_nss;
			$employee->doc_ine					= $request->doc_ine;
			$employee->doc_curp					= $request->doc_curp;
			$employee->doc_rfc					= $request->doc_rfc;
			$employee->doc_cv					= $request->doc_cv;
			$employee->doc_proof_of_studies		= $request->doc_proof_of_studies;
			$employee->doc_professional_license	= $request->doc_professional_license;
			$employee->doc_requisition			= $request->doc_requisition;
			$employee->replace					= $request->replace;
			$employee->qualified_employee		= $request->qualified_employee;
			$employee->purpose					= $request->purpose;
			$employee->requeriments				= $request->requeriments;
			$employee->observations				= $request->observations;
			$employee->save();
			
			WorkerData::where('idEmployee',$employee->id)
			->update(
			[
				'visible' => 0
			]);

			$working							= new WorkerData();
			$working->idEmployee				= $employee->id;
			$working->state						= $request->work_state;
			$working->project					= $request->work_project;
			$working->enterprise				= $request->work_enterprise;
			$working->account					= $request->work_account;
			$working->direction					= $request->work_direction;
			$working->department				= $request->work_department;
			$working->position					= $request->work_position;
			$working->immediate_boss			= $request->work_immediate_boss;
			$working->admissionDate				= $request->work_income_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_income_date)->format('Y-m-d')	: null;
			$working->imssDate					= $request->work_imss_date		!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_imss_date)->format('Y-m-d')	: null;
			$working->downDate					= $request->work_down_date		!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_down_date)->format('Y-m-d')	: null;
			$working->endingDate				= $request->work_ending_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_ending_date)->format('Y-m-d')	: null;
			$working->reentryDate				= $request->work_reentry_date	!= "" ? Carbon::createFromFormat('d-m-Y', $request->work_reentry_date)->format('Y-m-d')	: null;
			$working->workerType				= $request->work_type_employee;
			$working->regime_id					= $request->regime_employee;
			$working->workerStatus				= $request->work_status_employee;
			$working->status_reason				= $request->work_status_reason;
			$working->status_imss				= $request->work_status_imss;
			$working->sdi						= $request->work_sdi;
			$working->periodicity				= $request->work_periodicity;
			$working->employer_register			= $request->work_employer_register;
			$working->paymentWay				= $request->work_payment_way;
			$working->netIncome					= $request->work_net_income;
			$working->complement				= $request->work_complement;
			$working->fonacot					= $request->work_fonacot;
			$working->nomina					= $request->work_nomina;
			$working->bono						= $request->work_bonus;
			$working->recorder					= Auth::user()->id;
			$working->admissionDateOld			= $request->work_income_date_old != "" ? Carbon::createFromFormat('d-m-Y', $request->work_income_date_old)->format('Y-m-d')	: null;
			$working->enterpriseOld				= $request->work_enterprise_old;
			$working->viatics					= $request->viatics;
			$working->camping					= $request->camping;
			$working->position_immediate_boss	= $request->position_immediate_boss;
			if(isset($request->infonavit))
			{
				$working->infonavitCredit		= $request->work_infonavit_credit;
				$working->infonavitDiscount		= $request->work_infonavit_discount;
				$working->infonavitDiscountType	= $request->work_infonavit_discount_type;
			}
			if(isset($request->alimony))
			{
				$working->alimonyDiscount		= $request->work_alimony_discount;
				$working->alimonyDiscountType	= $request->work_alimony_discount_type;
			}
			$working->save();
			if(isset($request->work_place) && count($request->work_place) > 0)
			{
				$working->places()->attach($request->work_place);
			}
			if(isset($request->work_wbs) && count($request->work_wbs) > 0)
			{
				$working->employeeHasWbs()->attach($request->work_wbs);
			}
			if(isset($request->work_subdepartment) && count($request->work_subdepartment) > 0)
			{
				$working->employeeHasSubdepartment()->attach($request->work_subdepartment);
			}

			
			if(isset($request->idEmployeeBank) && count($request->idEmployeeBank) > 0)
			{
				foreach ($request->idEmployeeBank as $k => $e)
				{
					if($request->idEmployeeBank[$k] == 'x')
					{
						$check_account 	= EmployeeAccount::where('account',$request->account[$k])
										->where('clabe',$request->clabe[$k])
										->where('idCatBank',$request->bank[$k])
										->where('cardNumber',$request->card[$k])
										->count();

						if ($check_account == 0) 
						{
							$empAcc					= new EmployeeAccount();
							$empAcc->idEmployee		= $employee->id;
							$empAcc->beneficiary	= $request->beneficiary[$k];
							$empAcc->type			= $request->type_account[$k];
							$empAcc->alias			= $request->alias[$k];
							$empAcc->clabe			= $request->clabe[$k];
							$empAcc->account		= $request->account[$k];
							$empAcc->cardNumber		= $request->card[$k];
							$empAcc->idCatBank		= $request->bank[$k];
							$empAcc->branch			= $request->branch[$k];
							$empAcc->recorder		= Auth::user()->id;
							$empAcc->visible		= 1;
							$empAcc->save();
						}
					}
				}
			}

			// if ($request_model->kind == 19 && $employee_edit->documents()->exists()) 
			// {
			// 	foreach($employee_edit->documents as $doc)
			// 	{ 
			// 		$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
			// 					->where('path',$doc->path)
			// 					->where('real_employee_id',$employee->id)
			// 					->count();
			// 		if ($checkDoc == 0) 
			// 		{
			// 			$other						= new RealEmployeeDocument();
			// 			$other->name				= $doc->name;
			// 			$other->path				= $doc->path;
			// 			$other->real_employee_id	= $employee->id;
			// 			$other->save();
			// 		}
			// 	}
			// }
			// if($request_model->kind == 4 && $employee_edit->staffDocuments->count() > 0)
			// {
			// 	foreach($employee_edit->staffDocuments as $doc)
			// 	{ 
			// 		$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
			// 					->where('path',$doc->path)
			// 					->where('real_employee_id',$employee->id)
			// 					->count();
			// 		if ($checkDoc == 0) 
			// 		{
			// 			$other						= new RealEmployeeDocument();
			// 			$other->name				= $doc->name;
			// 			$other->path				= $doc->path;
			// 			$other->real_employee_id	= $employee->id;
			// 			$other->save();
			// 		}
			// 	}
			// }

			if (isset($request->name_other_document) && count($request->name_other_document)>0) 
			{
				for ($i=0; $i < count($request->name_other_document); $i++) 
				{ 
					$checkDoc 	= RealEmployeeDocument::where('name',$request->name_other_document[$i])
								->where('path',$request->path_other_document[$i])
								->where('real_employee_id',$employee->id)
								->count();
					if ($checkDoc == 0) 
					{
						$other						= new RealEmployeeDocument();
						$other->name				= $request->name_other_document[$i];
						$other->path				= $request->path_other_document[$i];
						$other->real_employee_id	= $employee->id;
						$other->save();
					}
				}
			}

			if(isset($request->delete_other_documents) && count($request->delete_other_documents) > 0)
			{
				for($i = 0; $i < count($request->delete_other_documents); $i++)
				{
					RealEmployeeDocument::where('path', $request->delete_other_documents[$i])->delete();
				}
			}
			$alert	= "swal('','".Lang::get("messages.record_updated")."', 'success');";
			return redirect()->route('administration.employee.pending')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function approved(Request $request)
	{
		if (Auth::user()->module->where('id',319)->count()>0) 
		{
			$data 		= Module::find($this->module_id);

			$approved 	=	RequestModel::
			selectRaw('request_models.kind,
				request_models.folio,
				projects.proyectName,
				IFNULL(requisition_employees.id, staff_employees.id) as employee_id, 
				IF(requisition_employees.id IS NULL, staff_employee.name, req_employee.name) as name, 
				IF(requisition_employees.id IS NULL, staff_employee.last_name, req_employee.last_name) as last_name, 
				IF(requisition_employees.id IS NULL, staff_employee.scnd_last_name, req_employee.scnd_last_name) as scnd_last_name, 
				IF(requisition_employees.id IS NULL, staff_employees.admissionDate, requisition_employees.admissionDate) as admissionDate, 
				IF(requisition_employees.id IS NULL, staff_employees.position, requisition_employees.position) as position, 
				IF(requisition_employees.id IS NULL, staff_employees.curp, requisition_employees.curp) as curp')
			->leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
			->leftJoin('requisition_employees', 'requisitions.id', 'requisition_employees.requisition_id')
			->leftJoin('staff', 'request_models.folio', 'staff.idFolio')
			->leftJoin('staff_employees', 'staff.idStaff', 'staff_employees.staff_id')
			->leftJoin('projects','request_models.idProject','projects.idproyect')
			->leftJoin('real_employees as req_employee', 'requisition_employees.curp', 'req_employee.curp')
			->leftJoin('real_employees as staff_employee', 'staff_employees.curp', 'staff_employee.curp')
			->where('request_models.status',5)
			->where(function($q)
			{
				$q->where('staff_employees.status_personal',1)
					->orWhere('requisition_employees.status_personal',1);
			})
			->where(function($query) use ($request)
			{
				if ($request->name != "") 
				{
					$query->where(function($q) use ($request)
					{
						$q->whereRaw('CONCAT_WS(" ",requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $request->name).'%"')
							->orWhereRaw('CONCAT_WS(" ",staff_employees.name,staff_employees.last_name,staff_employees.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $request->name).'%"');
					});
				}

				if ($request->position != "") 
				{
					$query->where(function($q) use ($request)
					{
						$q->where('requisition_employees.position','LIKE','%'.$request->position.'%')
							->orWhere('staff_employees.position','LIKE','%'.$request->position.'%');
					});
				}

				if ($request->curp != "") 
				{
					$query->where(function($q) use ($request)
					{
						$q->where('requisition_employees.curp','LIKE','%'.$request->curp.'%')
							->orWhere('staff_employees.curp','LIKE','%'.$request->curp.'%');
					});
				}
				if ($request->project_id != "") 
				{
					$query->whereIn('request_models.idProject',$request->project_id);
				}
				if ($request->folio != "") 
				{
					$query->where('request_models.folio',$request->folio);
				}
			})
			->orderBy('request_models.folio','DESC')
			->paginate(10);

			return view('administracion.empleados.aprobados',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 319,
				'name'			=> $request->name,
				'folio'			=> $request->folio,
				'project_id'	=> $request->project_id,
				'position'		=> $request->position,
				'curp'			=> $request->curp,
				'approved'		=> $approved
			]);	
		}
	}

	public function approvedView(RealEmployee $employee)
	{
		if (Auth::user()->module->where('id',319)->count()>0) 
		{
			$data 		= Module::find($this->module_id);
			return view('administracion.empleados.ver',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 319,
				'employee'	=> $employee,
			]);	
		}
		else
		{
			return redirect('error');
		}
	}

	public function pendingExport(Request $request)
	{
		if (Auth::user()->module->where('id',318)->count()>0) 
		{
			$pending 	= RequestModel::selectRaw(
							'
								IFNULL(requisition_employees.name, staff_employees.name) as name,
								IFNULL(requisition_employees.last_name, staff_employees.last_name) as last_name,
								IFNULL(requisition_employees.scnd_last_name, staff_employees.scnd_last_name) as scnd_last_name,
								IFNULL(requisition_employees.curp, staff_employees.curp) as curp,
								IFNULL(requisition_employees.rfc, staff_employees.rfc) as rfc,
								IFNULL(requisition_employees.tax_regime, staff_employees.tax_regime) as tax_regime,
								IFNULL(requisition_employees.imss, staff_employees.imss) as imss,
								IFNULL(requisition_employees.street, staff_employees.street) as street,
								IFNULL(requisition_employees.number, staff_employees.number) as number,
								IFNULL(requisition_employees.colony, staff_employees.colony) as colony,
								IFNULL(requisition_employees.cp, staff_employees.cp) as cp,
								IFNULL(requisition_employees.city, staff_employees.city) as city,
								IFNULL(requisition_employees.state_id, staff_employees.state_id) as state_id,
								IFNULL(requisition_employees.email, staff_employees.email) as email,
								IFNULL(requisition_employees.phone, staff_employees.phone) as phone,
								IFNULL(requisition_employees.state, staff_employees.state) as state,
								IFNULL(requisition_employees.project, staff_employees.project) as project,
								IFNULL(requisition_employees.wbs_id, staff_employees.wbs_id) as wbs_id,
								IFNULL(requisition_employees.enterprise, staff_employees.enterprise) as enterprise,
								IFNULL(requisition_employees.account, staff_employees.account) as accounting_account,
								CONCAT("","") as places,
								IFNULL(requisition_employees.direction, staff_employees.direction) as direction,
								IFNULL(requisition_employees.department, staff_employees.department) as department,
								IFNULL(requisition_employees.subdepartment_id, staff_employees.subdepartment_id) as subdepartment_id,
								IFNULL(requisition_employees.position, staff_employees.position) as position,
								IFNULL(requisition_employees.immediate_boss, staff_employees.immediate_boss) as immediate_boss,
								IFNULL(requisition_employees.position_immediate_boss, staff_employees.position_immediate_boss) as position_immediate_boss,
								IFNULL(requisition_employees.status_imss, staff_employees.status_imss) as status_imss,
								IFNULL(requisition_employees.admissionDate, staff_employees.admissionDate) as admissionDate,
								IFNULL(requisition_employees.imssDate, staff_employees.imssDate) as imssDate,
								IFNULL(requisition_employees.downDate, staff_employees.downDate) as downDate,
								IFNULL(requisition_employees.endingDate, staff_employees.endingDate) as endingDate,
								IFNULL(requisition_employees.reentryDate, staff_employees.reentryDate) as reentryDate,
								IFNULL(requisition_employees.workerType, staff_employees.workerType) as workerType,
								IFNULL(requisition_employees.regime_id, staff_employees.regime_id) as regime_id,
								IFNULL(requisition_employees.workerStatus, staff_employees.workerStatus) as workerStatus,
								IFNULL(requisition_employees.status_reason, staff_employees.status_reason) as status_reason,
								IFNULL(requisition_employees.sdi, staff_employees.sdi) as sdi,
								IFNULL(requisition_employees.periodicity, staff_employees.periodicity) as periodicity,
								IFNULL(requisition_employees.employer_register, staff_employees.employer_register) as employer_register,
								IFNULL(requisition_employees.paymentWay, staff_employees.paymentWay) as paymentWay,
								IFNULL(requisition_employees.netIncome, staff_employees.netIncome) as netIncome,
								IFNULL(requisition_employees.viatics, staff_employees.viatics) as viatics,
								IFNULL(requisition_employees.camping, staff_employees.camping) as camping,
								IFNULL(requisition_employees.complement, staff_employees.complement) as complement,
								IFNULL(requisition_employees.fonacot, staff_employees.fonacot) as fonacot,
								IFNULL(requisition_employees.nomina, staff_employees.nomina) as nomina,
								IFNULL(requisition_employees.bono, staff_employees.bono) as bono,
								IFNULL(requisition_employees.infonavitCredit, staff_employees.infonavitCredit) as infonavitCredit,
								IFNULL(requisition_employees.infonavitDiscount, staff_employees.infonavitDiscount) as infonavitDiscount,
								IFNULL(requisition_employees.infonavitDiscountType, staff_employees.infonavitDiscountType) as infonavitDiscountType,
								IFNULL(bank_data.alias, bank_data_staff.alias) as alias,
								IFNULL(bank_data.idCatBank, bank_data_staff.id_catbank) as idCatBank,
								IFNULL(CONCAT(bank_data.clabe," "), CONCAT(bank_data_staff.clabe," ")) as clabe,
								IFNULL(CONCAT(bank_data.account," "), CONCAT(bank_data_staff.account," ")) as account,
								IFNULL(CONCAT(bank_data.cardNumber," "), CONCAT(bank_data_staff.cardNumber," ")) as cardNumber,
								IFNULL(bank_data.branch, bank_data_staff.branch) as branch
							'
							)
						->leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
						->leftJoin('requisition_employees', 'requisitions.id', 'requisition_employees.requisition_id')
						->leftJoin('staff', 'request_models.folio', 'staff.idFolio')
						->leftJoin('staff_employees', 'staff.idStaff', 'staff_employees.staff_id')
						->leftJoin('projects','request_models.idProject','projects.idproyect')
						->leftJoin(DB::raw('(SELECT * FROM requisition_employee_accounts WHERE id IN(SELECT MIN(id) as id FROM requisition_employee_accounts GROUP BY idEmployee)) as bank_data'),'requisition_employees.id','bank_data.idEmployee')
						->leftJoin(DB::raw('(SELECT * FROM staff_accounts WHERE id IN(SELECT MIN(id) as id FROM staff_accounts GROUP BY id_staff_employee)) as bank_data_staff'),'staff_employees.id','bank_data_staff.id_staff_employee')
						->where('request_models.status',5)
						->where(function($q)
						{
							$q->where('staff_employees.status_personal',0)
								->orWhere('requisition_employees.status_personal',0);
						})
						->where(function($query) use ($request)
						{
							if ($request->name != "") 
							{
								$query->where(function($q) use ($request)
								{
									$q->whereRaw('CONCAT_WS(" ",requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $request->name).'%"')
										->orWhereRaw('CONCAT_WS(" ",staff_employees.name,staff_employees.last_name,staff_employees.scnd_last_name) LIKE "%'.preg_replace("/\s+/", "%", $request->name).'%"');
								});
							}

							if ($request->position != "") 
							{
								$query->where(function($q) use ($request)
								{
									$q->where('requisition_employees.position','LIKE','%'.$request->position.'%')
										->orWhere('staff_employees.position','LIKE','%'.$request->position.'%');
								});
							}

							if ($request->curp != "") 
							{
								$query->where(function($q) use ($request)
								{
									$q->where('requisition_employees.curp','LIKE','%'.$request->curp.'%')
										->orWhere('staff_employees.curp','LIKE','%'.$request->curp.'%');
								});
							}
							if ($request->project_id != "") 
							{
								$query->whereIn('request_models.idProject',$request->project_id);
							}
							if ($request->folio != "") 
							{
								$query->where('request_models.folio',$request->folio);
							}
						})
						->orderBy('request_models.folio','DESC')
						->get();


			Excel::create('Reporte-Empleados-Plantilla', function($excel) use ($pending)
			{
				$excel->sheet('Empleados Registrados',function($sheet) use ($pending)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->setColumnFormat(array(
						'Z'		=> '@',
						'AA'	=> '@',
						'AD'	=> '@',
						'AP' 	=> '@',
						'AQ'	=> '@',
						'AR'	=> '@',
						'AS'	=> '@',
						'BA'	=> '@',
						'BB'	=> '@',
						'BC'	=> '@',
						'AT'	=> '@',
					));
					$sheet->cell('A1:B1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('C1', function($cells)
					{
						$cells->setBackground('#34b511');
					});
					$sheet->cell('D1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('E1:E1', function($cells)
					{
						$cells->setBackground('#34b511');
					});
					$sheet->cell('F1:O1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('P1:T1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('U1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('V1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('W1:X1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('Y1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('Z1:AA1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AB1:AC1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AD1:AG1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AH1:AJ1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AK1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AL1:AS1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AT1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AU1:AV1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AW1:AY1', function($cells)
					{
						$cells->setBackground('#db5151');
					});

					$sheet->cell('AZ1:BE1', function($cells)
					{
						$cells->setBackground('#21bbbb');
					});
					$sheet->cell('A1:BE1', function($cells)
					{
						$cells->setFontColor('#ffffff');
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->setFreeze('D2');

					$sheet->row(1,[
							'nombre',
							'apellido',
							'apellido2',
							'curp',
							'rfc',
							'regimen_fiscal',
							'imss',
							'calle',
							'numero',
							'colonia',
							'cp',
							'ciudad',
							'estado',
							'correo_electronico',
							'numero_telefonico',
							'estado_laboral',
							'proyecto',
							'wbs',
							'empresa',
							'clasificacion_gasto',
							'lugar_trabajo',
							'direccion',
							'departamento',
							'subdepartamento',
							'puesto',
							'jefe_inmediato',
							'posicion_jefe_inmediato',
							'estatus_imss',
							'fecha_ingreso',
							'fecha_alta',
							'fecha_baja',
							'fecha_termino',
							'fecha_reingreso',
							'tipo_contrato',
							'regimen',
							'estatus',
							'razon_estatus',
							'sdi',
							'periodicidad',
							'registro_patronal',
							'forma_pago',
							'sueldo_neto',
							'viaticos',
							'campamento',
							'complemento',
							'fonacot',
							'porcentaje_nomina',
							'porcentaje_bono',
							'credito_infonavit',
							'descuento_infonavit',
							'tipo_descuento_infonavit',
							'alias',
							'banco',
							'clabe',
							'cuenta',
							'tarjeta',
							'sucursal',
						]);
					foreach ($pending as $employee) 
					{
						$sheet->appendRow($employee->toArray());
					}
				});
			})->export('xlsx');

		}
		else
		{
			return redirect('error');
		}
	}

	public function approvedMassive()
	{
		if (Auth::user()->module->where('id',320)->count()>0) 
		{
			$data 		= Module::find($this->module_id);
			return view('administracion.empleados.masivo',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 320,
			]);	
		}
		else
		{
			return redirect('error');
		}
	}

	public function massiveUpload(Request $request)
	{
		
		if(Auth::user()->module->where('id',320)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert	= "swal('','".Lang::get("messages.file_null")."', 'error');";
				return back()->with('alert',$alert);	
			}
			if($request->file('csv_file')->isValid())
			{
				$extention	= strtolower($request->file('csv_file')->getClientOriginalExtension());
				if($extention != 'csv')
				{
					$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
					return back()->with('alert',$alert);
				}
				$delimiters = [";" => 0, "," => 0];
				$handle     = fopen($request->file('csv_file'), "r");
				$firstLine  = fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count)
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				if($separator == $request->separator)
				{
					$name		= '/massive_employee/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first	= true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first   = false;
							}
							$csvArr[] = $data;
						}
						fclose($handle);
					}
					try
					{
						array_walk($csvArr, function(&$a) use ($csvArr)
						{
							$a = array_combine($csvArr[0], $a);
						});
					}
					catch(\Exception $e)
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);
					}
					array_shift($csvArr);
					
					$headers = [
						'nombre',
						'apellido',
						'apellido2',
						'curp',
						'rfc',
						'regimen_fiscal',
						'imss',
						'calle',
						'numero',
						'colonia',
						'cp',
						'ciudad',
						'estado',
						'correo_electronico',
						'numero_telefonico',
						'estado_laboral',
						'proyecto',
						'wbs',
						'empresa',
						'clasificacion_gasto',
						'lugar_trabajo',
						'direccion',
						'departamento',
						'subdepartamento',
						'puesto',
						'jefe_inmediato',
						'posicion_jefe_inmediato',
						'estatus_imss',
						'fecha_ingreso',
						'fecha_alta',
						'fecha_baja',
						'fecha_termino',
						'fecha_reingreso',
						'tipo_contrato',
						'regimen',
						'estatus',
						'razon_estatus',
						'sdi',
						'periodicidad',
						'registro_patronal',
						'forma_pago',
						'sueldo_neto',
						'viaticos',
						'campamento',
						'complemento',
						'fonacot',
						'porcentaje_nomina',
						'porcentaje_bono',
						'credito_infonavit',
						'descuento_infonavit',
						'tipo_descuento_infonavit',
						'alias',
						'banco',
						'clabe',
						'cuenta',
						'tarjeta',
						'sucursal',
					];

					// Función para validar documentos diferentes
					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					$data = Module::find($this->module_id);
					return view('administracion.empleados.verificar_masivo',
						[
							'id'        => $data['father'],
							'title'     => $data['name'],
							'details'   => $data['details'],
							'child_id'  => $this->module_id,
							'option_id' => 320,
							'csv'       => $csvArr,
							'fileName'  => $name,
							'delimiter' => $request->separator
						]);
				}
				else
				{
					$alert	= "swal('','".Lang::get("messages.separator_error")."', 'error');";
					return back()->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveContinue(Request $request)
	{
		if(Auth::user()->module->where('id',320)->count()>0)
		{
			$path	= \Storage::disk('reserved')->path($request->fileName);
			$csvArr	= array();
			if(($handle = fopen($path, "r")) !== FALSE)
			{
				$first = true;
				while (($data = fgetcsv($handle, 1000, $request->delimiter)) !== FALSE)
				{
					if($first)
					{
						$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
						$first   = false;
					}
					$csvArr[] = $data;
				}
				fclose($handle);
			}
			array_walk($csvArr, function(&$a) use ($csvArr)
			{
				$a = array_combine($csvArr[0], $a);
			});
			array_shift($csvArr);
			$updatedEmployee	= array();
			$savedEmployee		= array();
			$errorEmployee		= array();
			foreach ($csvArr as $key => $e)
			{
				try
				{
					if (isset($e['curp']) && trim($e['curp'])!= "") 
					{
						$checkRequisitionEmployee = RequestModel::
												selectRaw('request_models.kind,IFNULL(requisition_employees.id, staff_employees.id) as id, IFNULL(requisition_employees.status_personal, staff_employees.status_personal) as status_personal')
												->leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
												->leftJoin('requisition_employees', 'requisitions.id', 'requisition_employees.requisition_id')
												->leftJoin('staff', 'request_models.folio', 'staff.idFolio')
												->leftJoin('staff_employees', 'staff.idStaff', 'staff_employees.staff_id')
												->where('request_models.status',5)
												->where(function($q) use($e)
												{
													$q->where('staff_employees.status_personal',0)
														->orWhere('requisition_employees.status_personal',0);
												})
												->where(function($q) use($e)
												{
													$q->where('requisition_employees.curp',$e['curp'])
														->orWhere('staff_employees.curp',$e['curp']);
												})
												->get();
												
						$existEmployee				= RealEmployee::where('curp',$e['curp'])->get();							
					}
					else
					{
						
						$checkRequisitionEmployee = "";
					}
					
					if($checkRequisitionEmployee != "" && $checkRequisitionEmployee->first()->status_personal == 0) 
					{
						
						if($checkRequisitionEmployee->first()->kind == "19")
						{
							$updateEmployee						= RequisitionEmployee::find($checkRequisitionEmployee->first()->id);
							$updateEmployee->status_personal	= 1;
							$updateEmployee->save();
						}
						if($checkRequisitionEmployee->first()->kind == "4")
						{
							$updateEmployee						= StaffEmployee::find($checkRequisitionEmployee->first()->id);
							$updateEmployee->status_personal	= 1;
							$updateEmployee->save();
						}
						
						if(count($existEmployee) > 0)
						{
							$employee	= $existEmployee->first();
							$oldWorker	= $employee->workerDataVisible->first();
							if($oldWorker != null && $oldWorker->workerStatus == 5)
							{
								$csvArr[$key]['status']	= 'Empleado boletinado';
							}
							else
							{
								/*
								elseif(isset($e['empresa']) && $e['empresa'] != '' && $oldWorker != null && $oldWorker->enterprise != null && $oldWorker->enterprise != $e['empresa'] && ($oldWorker->downDate == '' || $oldWorker->workerStatus == 1))
								{
									$csvArr[$key]['status']	= 'No actualizado (primero debe darlo de baja de la empresa actual)';
								}
								*/
								$employee->name						= !isset($e['nombre']) || empty(trim($e['nombre'])) ? $employee->name : $e['nombre'];
								$employee->last_name				= !isset($e['apellido']) || empty(trim($e['apellido'])) ? $employee->last_name : $e['apellido'];
								$employee->scnd_last_name			= !isset($e['apellido2']) || empty(trim($e['apellido2'])) ? $employee->scnd_last_name : $e['apellido2'];
								$employee->curp						= !isset($e['curp']) || empty(trim($e['curp'])) ? $employee->curp : $e['curp'];
								$employee->rfc						= !isset($e['rfc']) || empty(trim($e['rfc'])) ? $employee->rfc : str_replace('-','',$e['rfc']);
								$employee->tax_regime				= !isset($e['regimen_fiscal']) || empty(trim($e['regimen_fiscal'])) ? $employee->tax_regime : (CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() > 0 ? $e['regimen_fiscal'] : null);
								$employee->imss						= !isset($e['imss']) || empty(trim($e['imss'])) ? $employee->imss : $e['imss'];
								$employee->street					= !isset($e['calle']) || empty(trim($e['calle'])) ? $employee->street : $e['calle'];
								$employee->number					= !isset($e['numero']) || empty(trim($e['numero'])) ? $employee->number : $e['numero'];
								$employee->colony					= !isset($e['colonia']) || empty(trim($e['colonia'])) ? $employee->colony : $e['colonia'];
								$employee->cp						= !isset($e['cp']) || empty(trim($e['cp'])) ? $employee->cp : (CatZipCode::where('zip_code',$e['cp'])->count() > 0 ? $e['cp'] : null);
								$employee->city						= !isset($e['ciudad']) || empty(trim($e['ciudad'])) ? $employee->city : $e['ciudad'];
								$employee->state_id					= !isset($e['estado']) || empty(trim($e['estado'])) ? $employee->state_id : $e['estado'];
								$employee->email					= !isset($e['correo_electronico']) || empty(trim($e['correo_electronico'])) ? $employee->email : $e['correo_electronico'];
								$employee->phone					= !isset($e['numero_telefonico']) || empty(trim($e['numero_telefonico'])) ? $employee->phone : $e['numero_telefonico'];
								$employee->doc_birth_certificate	= $updateEmployee->doc_birth_certificate;
								$employee->doc_proof_of_address		= $updateEmployee->doc_proof_of_address;
								$employee->doc_nss					= $updateEmployee->doc_nss;
								$employee->doc_ine					= $updateEmployee->doc_ine;
								$employee->doc_curp					= $updateEmployee->doc_curp;
								$employee->doc_rfc					= $updateEmployee->doc_rfc;
								$employee->doc_cv					= $updateEmployee->doc_cv;
								$employee->doc_proof_of_studies		= $updateEmployee->doc_proof_of_studies;
								$employee->doc_professional_license	= $updateEmployee->doc_professional_license;
								$employee->doc_requisition			= $updateEmployee->doc_requisition;
								$employee->replace					= $updateEmployee->replace;
								$employee->qualified_employee		= $updateEmployee->qualified_employee;
								$employee->purpose					= $updateEmployee->purpose;
								$employee->requeriments				= $updateEmployee->requeriments;
								$employee->observations				= $updateEmployee->observations;
								$employee->save();

								if ($checkRequisitionEmployee->first()->kind == 19 && $updateEmployee->documents()->exists()) 
								{
									foreach($updateEmployee->documents as $doc)
									{ 
										$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
													->where('path',$doc->path)
													->where('real_employee_id',$employee->id)
													->count();
										if ($checkDoc == 0) 
										{
											$other						= new RealEmployeeDocument();
											$other->name				= $doc->name;
											$other->path				= $doc->path;
											$other->real_employee_id	= $employee->id;
											$other->save();
										}
									}
								}

								if ($checkRequisitionEmployee->first()->kind == 4 && $updateEmployee->staffDocuments->count() > 0) 
								{
									foreach($updateEmployee->staffDocuments as $doc)
									{ 
										$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
													->where('path',$doc->path)
													->where('real_employee_id',$employee->id)
													->count();
										if ($checkDoc == 0) 
										{
											$other						= new RealEmployeeDocument();
											$other->name				= $doc->name;
											$other->path				= $doc->path;
											$other->real_employee_id	= $employee->id;
											$other->save();
										}
									}
								}

								$csvArr[$key]['nombre']		= $employee->name;
								$csvArr[$key]['apellido']	= $employee->last_name;
								$csvArr[$key]['apellido2']	= $employee->scnd_last_name;
								if($oldWorker != null)
								{
									$working            = $oldWorker->replicate();
									$oldWorker->visible = 0;
									$oldWorker->save();
								}
								else
								{
									$working = new WorkerData();
								}
								$working->idEmployee				= $employee->id;
								$working->state						= !isset($e['estado_laboral']) || empty(trim($e['estado_laboral'])) ? $working->state : $e['estado_laboral'];
								$working->project					= !isset($e['proyecto']) || empty(trim($e['proyecto'])) ? $working->project : $e['proyecto'];
								$working->enterprise				= !isset($e['empresa']) || empty(trim($e['empresa'])) ? $working->enterprise : $e['empresa'];
								$working->account					= !isset($e['clasificacion_gasto']) || empty(trim($e['clasificacion_gasto'])) ? $working->account : $e['clasificacion_gasto'];
								$working->direction					= !isset($e['direccion']) || empty(trim($e['direccion'])) ? $working->direction : $e['direccion'];
								$working->department				= !isset($e['departamento']) || empty(trim($e['departamento'])) ? $working->department : $e['departamento'];
								$working->position					= !isset($e['puesto']) || empty(trim($e['puesto'])) ? $working->position : $e['puesto'];
								$working->immediate_boss			= !isset($e['jefe_inmediato']) || empty(trim($e['jefe_inmediato'])) ? $working->immediate_boss : ucwords($e['jefe_inmediato']);
								$working->position_immediate_boss	= !isset($e['posicion_jefe_inmediato']) || empty(trim($e['posicion_jefe_inmediato'])) ? $working->position_immediate_boss : ucwords($e['posicion_jefe_inmediato']);
								$working->admissionDate				= !isset($e['fecha_ingreso']) || empty(trim($e['fecha_ingreso'])) ? $working->admissionDate : $e['fecha_ingreso'];
								$working->imssDate					= !isset($e['fecha_alta']) || empty(trim($e['fecha_alta'])) ? $working->imssDate : $e['fecha_alta'];
								$working->downDate					= !isset($e['fecha_baja']) || empty(trim($e['fecha_baja'])) ? $working->downDate : $e['fecha_baja'];
								$working->endingDate				= !isset($e['fecha_termino']) || empty(trim($e['fecha_termino'])) ? $working->endingDate : $e['fecha_termino'];
								$working->reentryDate				= !isset($e['fecha_reingreso']) || empty(trim($e['fecha_reingreso'])) ? $working->reentryDate : $e['fecha_reingreso'];
								$working->workerType				= !isset($e['tipo_contrato']) || empty(trim($e['tipo_contrato'])) ? $working->workerType : $e['tipo_contrato'];
								$working->regime_id					= !isset($e['regimen']) || empty(trim($e['regimen'])) ? $working->regime_id : $e['regimen'];
								$working->workerStatus				= !isset($e['estatus']) || empty(trim($e['estatus'])) ? $working->workerStatus : $e['estatus'];
								$working->status_imss				= !isset($e['estatus_imss']) || trim($e['estatus_imss']) ? $working->status_imss : $e['estatus_imss'];
								$working->status_reason				= !isset($e['razon_estatus']) || empty(trim($e['razon_estatus'])) ? $working->status_reason : $e['razon_estatus'];
								$working->sdi						= !isset($e['sdi']) || empty(trim($e['sdi'])) ? $working->sdi : $e['sdi'];
								$working->periodicity				= !isset($e['periodicidad']) || empty(trim($e['periodicidad'])) ? $working->periodicity : $e['periodicidad'];
								$working->employer_register			= !isset($e['registro_patronal']) || empty(trim($e['registro_patronal'])) ? $working->employer_register : $e['registro_patronal'];
								$working->paymentWay				= !isset($e['forma_pago']) || empty(trim($e['forma_pago'])) ? $working->paymentWay : $e['forma_pago'];
								$working->netIncome					= !isset($e['sueldo_neto']) || empty(trim($e['sueldo_neto'])) ? $working->netIncome : $e['sueldo_neto'];
								$working->complement				= !isset($e['complemento']) || empty(trim($e['complemento'])) ? $working->complement : $e['complemento'];
								$working->viatics					= !isset($e['viaticos']) || empty(trim($e['viaticos'])) ? $working->viatics : $e['viaticos'];
								$working->camping					= !isset($e['campamento']) || empty(trim($e['campamento'])) ? $working->camping : $e['campamento'];
								$working->fonacot					= !isset($e['fonacot']) || empty(trim($e['fonacot'])) ? $working->fonacot : $e['fonacot'];
								$working->nomina					= !isset($e['porcentaje_nomina']) || empty(trim($e['porcentaje_nomina'])) ? $working->nomina : intval($e['porcentaje_nomina']);
								$working->bono						= !isset($e['porcentaje_bono']) || empty(trim($e['porcentaje_bono'])) ? $working->bono : intval($e['porcentaje_bono']);
								$working->recorder					= Auth::user()->id;
								$working->infonavitCredit			= !isset($e['credito_infonavit']) || empty(trim($e['credito_infonavit'])) ? $working->infonavitCredit : $e['credito_infonavit'];
								$working->infonavitDiscount			= !isset($e['descuento_infonavit']) || empty(trim($e['descuento_infonavit'])) ? $working->infonavitDiscount : floatval($e['descuento_infonavit']);
								$working->infonavitDiscountType		= !isset($e['tipo_descuento_infonavit']) || empty(trim($e['tipo_descuento_infonavit'])) ? $working->infonavitDiscountType : intval($e['tipo_descuento_infonavit']);
								$working->save();
								if(isset($e['lugar_trabajo']) && $e['lugar_trabajo'] != '')
								{
									$working->places()->detach();
									$working->places()->attach([$e['lugar_trabajo']]);
								}
								if(isset($e['wbs']) && $e['wbs'] != '')
								{
									$arrayWbs = explode(',', $e['wbs']);
									$working->employeeHasWbs()->detach();
									$working->employeeHasWbs()->attach($arrayWbs);
								}
								if(isset($e['subdepartamento']) && $e['subdepartamento'] != '')
								{
									$arraySubdepartment = explode(',', $e['subdepartamento']);
									$working->employeeHasSubdepartment()->detach();
									$working->employeeHasSubdepartment()->attach($arraySubdepartment);
								}
								if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='')
								{
									$empAcc				= new EmployeeAccount();
									$empAcc->idEmployee	= $employee->id;
									$empAcc->alias		= empty(trim($e['alias'])) ? null : $e['alias'];
									$empAcc->clabe		= empty(trim($e['clabe'])) ? null : $e['clabe'];
									$empAcc->account	= empty(trim($e['cuenta'])) ? null : $e['cuenta'];
									$empAcc->cardNumber	= empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
									$empAcc->idCatBank	= empty(trim($e['banco'])) ? null : $e['banco'];
									$empAcc->recorder	= Auth::user()->id;
									$empAcc->type		= 1;
									$empAcc->save();
								}
								/*
								if(!in_array($employee->id, $updatedEmployee))
								{
									$updatedEmployee[]   = $employee->id;
									$massive             = new MassiveEmployee();
									$massive->idEmployee = $employee->id;
									$massive->idCreator  = Auth::user()->id;
									$massive->csv        = $request->fileName;
									$massive->save();
								}
								*/
								if(
									is_null($employee->name) ||
									is_null($employee->last_name) ||
									is_null($employee->curp) ||
									is_null($employee->street) ||
									is_null($employee->number) ||
									is_null($employee->colony) ||
									is_null($employee->cp) ||
									is_null($employee->city) ||
									is_null($employee->state_id) ||
									is_null($employee->email) ||
									is_null($employee->phone) ||
									is_null($working->idEmployee) ||
									is_null($working->state) ||
									is_null($working->enterprise) ||
									is_null($working->account) ||
									is_null($working->direction) ||
									is_null($working->position) ||
									is_null($working->admissionDate) ||
									is_null($working->workerType) ||
									is_null($working->regime_id) ||
									is_null($working->workerStatus) ||
									is_null($working->periodicity) ||
									is_null($working->employer_register) ||
									is_null($working->paymentWay) ||
									is_null($working->nomina) ||
									is_null($working->bono) ||
									($e['regimen_fiscal'] != '' && CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() == 0) ||
									($e['cp'] != '' && CatZipCode::where('zip_code',$e['cp'])->count() == 0)
								)
								{
									$csvArr[$key]['status']	= 'Actualizado con errores';
								}
								else
								{
									$csvArr[$key]['status']	= 'Actualizado';
								}
							}
							$csvArr[$key]['id'] = $employee->id;
						}
						else
						{
							if(!empty(trim($e['curp'])))
							{
								$employee							= new RealEmployee();
								$employee->name						= empty(trim($e['nombre'])) ? null : $e['nombre'];
								$employee->last_name				= empty(trim($e['apellido'])) ? null : $e['apellido'];
								$employee->scnd_last_name			= empty(trim($e['apellido2'])) ? null : $e['apellido2'];
								$employee->curp						= empty(trim($e['curp'])) ? null : $e['curp'];
								$employee->rfc						= empty(trim($e['rfc'])) ? null : str_replace('-','',$e['rfc']);
								$employee->tax_regime				= empty(trim($e['regimen_fiscal'])) ? null : (CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() > 0 ? $e['regimen_fiscal'] : null);
								$employee->imss						= empty(trim($e['imss'])) ? null : $e['imss'];
								$employee->street					= empty(trim($e['calle'])) ? null : $e['calle'];
								$employee->number					= empty(trim($e['numero'])) ? null : $e['numero'];
								$employee->colony					= empty(trim($e['colonia'])) ? null : $e['colonia'];
								$employee->cp						= empty(trim($e['cp'])) ? null : (CatZipCode::where('zip_code',$e['cp'])->count() > 0 ? $e['cp'] : null);
								$employee->city						= empty(trim($e['ciudad'])) ? null : $e['ciudad'];
								$employee->state_id					= empty(trim($e['estado'])) ? null : $e['estado'];
								$employee->email					= empty(trim($e['correo_electronico'])) ? null : $e['correo_electronico'];
								$employee->phone					= empty(trim($e['numero_telefonico'])) ? null : $e['numero_telefonico'];
								$employee->doc_birth_certificate	= $updateEmployee->doc_birth_certificate;
								$employee->doc_proof_of_address		= $updateEmployee->doc_proof_of_address;
								$employee->doc_nss					= $updateEmployee->doc_nss;
								$employee->doc_ine					= $updateEmployee->doc_ine;
								$employee->doc_curp					= $updateEmployee->doc_curp;
								$employee->doc_rfc					= $updateEmployee->doc_rfc;
								$employee->doc_cv					= $updateEmployee->doc_cv;
								$employee->doc_proof_of_studies		= $updateEmployee->doc_proof_of_studies;
								$employee->doc_professional_license	= $updateEmployee->doc_professional_license;
								$employee->doc_requisition			= $updateEmployee->doc_requisition;
								$employee->replace					= $updateEmployee->replace;
								$employee->qualified_employee		= $updateEmployee->qualified_employee;
								$employee->purpose					= $updateEmployee->purpose;
								$employee->requeriments				= $updateEmployee->requeriments;
								$employee->observations				= $updateEmployee->observations;
								$employee->save();

								if ($checkRequisitionEmployee->first()->kind == 19 && $updateEmployee->documents()->exists()) 
								{
									foreach($updateEmployee->documents as $doc)
									{ 
										$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
													->where('path',$doc->path)
													->where('real_employee_id',$employee->id)
													->count();
										if ($checkDoc == 0) 
										{
											$other						= new RealEmployeeDocument();
											$other->name				= $doc->name;
											$other->path				= $doc->path;
											$other->real_employee_id	= $employee->id;
											$other->save();
										}
									}
								}

								if ($checkRequisitionEmployee->first()->kind == 4 && $updateEmployee->staffDocuments->count() > 0) 
								{
									foreach($updateEmployee->staffDocuments as $doc)
									{ 
										$checkDoc 	= RealEmployeeDocument::where('name',$doc->name)
													->where('path',$doc->path)
													->where('real_employee_id',$employee->id)
													->count();
										if ($checkDoc == 0) 
										{
											$other						= new RealEmployeeDocument();
											$other->name				= $doc->name;
											$other->path				= $doc->path;
											$other->real_employee_id	= $employee->id;
											$other->save();
										}
									}
								}
								
								$working                            = new WorkerData();
								$working->idEmployee				= $employee->id;
								$working->state						= empty(trim($e['estado_laboral'])) ? null : $e['estado_laboral'];
								$working->project					= empty(trim($e['proyecto'])) ? null : $e['proyecto'];
								$working->enterprise				= empty(trim($e['empresa'])) ? null : $e['empresa'];
								$working->account					= empty(trim($e['clasificacion_gasto'])) ? null : $e['clasificacion_gasto'];
								$working->direction					= empty(trim($e['direccion'])) ? null : $e['direccion'];
								$working->department				= empty(trim($e['departamento'])) ? null : $e['departamento'];
								$working->position					= empty(trim($e['puesto'])) ? null : $e['puesto'];
								$working->immediate_boss			= empty(trim($e['jefe_inmediato'])) ? null : ucwords($e['jefe_inmediato']);
								$working->position_immediate_boss	= empty(trim($e['posicion_jefe_inmediato'])) ? null : ucwords($e['posicion_jefe_inmediato']);
								$working->admissionDate				= empty(trim($e['fecha_ingreso'])) ? null : $e['fecha_ingreso'];
								$working->imssDate					= empty(trim($e['fecha_alta'])) ? null : $e['fecha_alta'];
								$working->downDate					= empty(trim($e['fecha_baja'])) ? null : $e['fecha_baja'];
								$working->endingDate				= empty(trim($e['fecha_termino'])) ? null : $e['fecha_termino'];
								$working->reentryDate				= empty(trim($e['fecha_reingreso'])) ? null : $e['fecha_reingreso'];
								$working->workerType				= empty(trim($e['tipo_contrato'])) ? null : $e['tipo_contrato'];
								$working->regime_id					= empty(trim($e['regimen'])) ? null : $e['regimen'];
								$working->status_imss				= empty(trim($e['estatus_imss'])) ? null : $e['estatus_imss'];
								$working->workerStatus				= empty(trim($e['estatus'])) ? null : $e['estatus'];
								$working->status_reason				= empty(trim($e['razon_estatus'])) ? null : $e['razon_estatus'];
								$working->sdi						= empty(trim($e['sdi'])) ? null : $e['sdi'];
								$working->periodicity				= empty(trim($e['periodicidad'])) ? null : $e['periodicidad'];
								$working->employer_register			= empty(trim($e['registro_patronal'])) ? null : $e['registro_patronal'];
								$working->paymentWay				= empty(trim($e['forma_pago'])) ? null : $e['forma_pago'];
								$working->netIncome					= empty(trim($e['sueldo_neto'])) ? null : $e['sueldo_neto'];
								$working->complement				= empty(trim($e['complemento'])) ? null : $e['complemento'];
								$working->viatics					= empty(trim($e['viaticos'])) ? null : $e['viaticos'];
								$working->camping					= empty(trim($e['campamento'])) ? null : $e['campamento'];
								$working->fonacot					= empty(trim($e['fonacot'])) ? null : $e['fonacot'];
								$working->nomina					= empty(trim($e['porcentaje_nomina'])) ? null : intval($e['porcentaje_nomina']);
								$working->bono						= empty(trim($e['porcentaje_bono'])) ? null : intval($e['porcentaje_bono']);
								$working->recorder					= Auth::user()->id;
								$working->infonavitCredit			= empty(trim($e['credito_infonavit'])) ? null : $e['credito_infonavit'];
								$working->infonavitDiscount			= empty(trim($e['descuento_infonavit'])) ? null : floatval($e['descuento_infonavit']);
								$working->infonavitDiscountType		= empty(trim($e['tipo_descuento_infonavit'])) ? null : intval($e['tipo_descuento_infonavit']);
								$working->save();
								if($e['lugar_trabajo'] != '')
								{
									$working->places()->attach([$e['lugar_trabajo']]);
								}
								if(isset($e['wbs']) && $e['wbs'] != '')
								{
									$arrayWbs = explode(',', $e['wbs']);
									$working->employeeHasWbs()->attach($arrayWbs);
								}
								if(isset($e['subdepartamento']) && $e['subdepartamento'] != '')
								{
									$arraySubdepartment = explode(',', $e['subdepartamento']);
									$working->employeeHasSubdepartment()->attach($arraySubdepartment);
								}
								if($e['alias'] != '' && $e['banco'] != '')
								{
									$empAcc				= new EmployeeAccount();
									$empAcc->idEmployee	= $employee->id;
									$empAcc->alias		= empty(trim($e['alias'])) ? null : $e['alias'];
									$empAcc->clabe		= empty(trim($e['clabe'])) ? null : $e['clabe'];
									$empAcc->account	= empty(trim($e['cuenta'])) ? null : $e['cuenta'];
									$empAcc->cardNumber	= empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
									$empAcc->idCatBank	= empty(trim($e['banco'])) ? null : $e['banco'];
									$empAcc->recorder	= Auth::user()->id;
									$empAcc->type		= 1;
									$empAcc->save();
								}
								/*
								if(!in_array($employee->id, $savedEmployee))
								{
									$savedEmployee[]     = $employee->id;
									$massive             = new MassiveEmployee();
									$massive->idEmployee = $employee->id;
									$massive->idCreator  = Auth::user()->id;
									$massive->csv        = $request->fileName;
									$massive->save();
								}
								*/
								if(
									is_null($employee->name) ||
									is_null($employee->last_name) ||
									is_null($employee->curp) ||
									is_null($employee->street) ||
									is_null($employee->number) ||
									is_null($employee->colony) ||
									is_null($employee->cp) ||
									is_null($employee->city) ||
									is_null($employee->state_id) ||
									is_null($employee->email) ||
									is_null($employee->phone) ||
									is_null($working->idEmployee) ||
									is_null($working->state) ||
									is_null($working->enterprise) ||
									is_null($working->account) ||
									is_null($working->direction) ||
									is_null($working->position) ||
									is_null($working->admissionDate) ||
									is_null($working->workerType) ||
									is_null($working->regime_id) ||
									is_null($working->workerStatus) ||
									is_null($working->periodicity) ||
									is_null($working->employer_register) ||
									is_null($working->paymentWay) ||
									is_null($working->nomina) ||
									is_null($working->bono) ||
									($e['regimen_fiscal'] != '' && CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() == 0) ||
									($e['cp'] != '' && CatZipCode::where('zip_code',$e['cp'])->count() == 0)
								)
								{
									$csvArr[$key]['status']	= 'Nuevo con errores';
								}
								else
								{
									$csvArr[$key]['status']	= 'Nuevo';
								}
								$csvArr[$key]['id'] = $employee->id;
							}
							else
							{
								$csvArr[$key]['status'] = 'No registrado';
								$csvArr[$key]['id']     = '';
							}
						}
					}
					else
					{
						$csvArr[$key]['status']	= 'Empleado aprobado anteriormente';
						$csvArr[$key]['id']		= '';
					}

				}
				catch (\Exception $e)
				{
					
					if(isset($employee) && $employee != "" && !in_array($employee->id, $errorEmployee))
					{
						$errorEmployee[] = $employee->id;
						$csvArr[$key]['status'] = 'Guardado con errores';
						$csvArr[$key]['id']     = $employee->id;
					}
					else
					{
						$csvArr[$key]['status'] = 'Error';
						$csvArr[$key]['id']     = '';
					}	
					//return $e;
				}
			}
			$data    = Module::find($this->module_id);
			return view('administracion.empleados.resultado_masivo',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 320,
					'csv'       => $csvArr
					
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',320)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('administration.employee.approved-massive');
		}
		else
		{
			return redirect('/');
		}
	}

	public function curpValidate(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> false,
				'class'		=> 'error',
				'message'	=> 'El campo es requerido.'
			);

			if (isset($request->curp) && $request->curp != "") 
			{
				if(preg_match("/^[A-Z]{1}[AEIOUX]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/i", $request->curp))
				{
					$response = array(
						'valid'		=> true,
						'class'		=> 'valid',
						'message'	=> ''
					);
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El CURP debe ser válido.'
					);
				}
			}

			return Response($response);
		}
	}

	public function rfcValidate(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array('valid' => true,'message' => '');
			if(isset($request->rfc) && $request->rfc != "")
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$response = array('valid' => true,'message' => '');
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El RFC debe ser válido.'
					);
				}
			}
			
			return Response($response);
		}
	}
}
