<?php

namespace App\Http\Controllers;

use App\RealEmployee;
use App\VehicleOwner;
use App\Module;
use App\Account;
use App\WorkerData;
use App\EmployeeAccount;
use App\MassiveEmployee;
use App\Enterprise;
use App\CatPeriodicity;
use App\PaymentMethod;
use App\User;
use App\Role;
use App\Area;
use App\Department;
use App\Subdepartment;
use App\Banks;
use App\KindOfBanks;
use App\SectionTickets;
use App\CatCodeWBS;
use App\State;
use App\Project;
use App\Place;
use App\CatContractType;
use App\CatRegimeType;
use App\CatBank;
use App\CatZipCode;
use App\CatTaxRegime;
use App\RealEmployeeDocument;
use Auth;
use Illuminate\Http\Request;
use Excel;
use Lang;
use Illuminate\Support\Facades\DB;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Carbon\Carbon;
use App\Staff;

class ConfiguracionEmpleadoController extends Controller
{
	private $module_id = 160;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'       => $data['father'],
					'title'    => $data['name'],
					'details'  => $data['details'],
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',161)->count()>0)
		{
			$data			= Module::find($this->module_id);
			return view('configuracion.empleado.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 161
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',161)->count()>0)
		{
			$employee							= new RealEmployee();
			$employee->name						= $request->name;
			$employee->last_name				= $request->last_name;
			$employee->scnd_last_name			= $request->scnd_last_name;
			$employee->curp						= $request->curp;
			$employee->rfc						= $request->rfc;
			$employee->tax_regime				= $request->tax_regime;
			$employee->imss						= $request->imss;
			$employee->email					= $request->email;
			$employee->street					= $request->street;
			$employee->number					= $request->number;
			$employee->phone					= $request->phone;
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
			$employee->replace					= $request->replace;
			$employee->qualified_employee		= $request->qualified_employee;
			$employee->purpose					= $request->purpose;
			$employee->requeriments				= $request->requeriments;
			$employee->observations				= $request->observations;
			$employee->save();

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
			$working->admissionDate     = $request->work_income_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date)->format('Y-m-d') : null;
			$working->imssDate          = $request->work_imss_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_imss_date)->format('Y-m-d') : null;
			$working->downDate          = $request->work_down_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_down_date)->format('Y-m-d') : null;
			$working->endingDate        = $request->work_ending_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_ending_date)->format('Y-m-d') : null;
			$working->reentryDate       = $request->work_reentry_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_reentry_date)->format('Y-m-d') : null;
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
			$working->admissionDateOld  = $request->work_income_date_old != "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date_old)->format('Y-m-d') : null;
			$working->enterpriseOld     = $request->work_enterprise_old;
			$working->viatics					= $request->work_viatics;
			$working->camping					= $request->work_camping;
			$working->position_immediate_boss	= $request->work_position_immediate_boss;

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
					$empAcc->save();
				}
			}
			if ($request->sys_user == 1)
			{
				$new_user                   = new User;
				$new_user->name             = $request->name;
				$new_user->last_name        = $request->last_name;
				$new_user->scnd_last_name   = $request->scnd_last_name;
				$new_user->area_id          = $request->work_direction;
				$new_user->departament_id   = $request->work_department;
				$new_user->position         = $request->work_position;
				$new_user->cash             = $request->work_enterprise;
				$new_user->email            = $request->email;
				$new_user->real_employee_id = $employee->id;
				$alert 	                    = "swal('', 'Empleado registrado satisfactoriamente. Ahora debe continuar capturando los datos para el usuario.', 'success');";
				$data                       = Module::find(6);
				$roles                      = Role::where('status','ACTIVE')->get();
				$enterprises                = Enterprise::orderName()->where('status','ACTIVE')->get();
				$areas                      = Area::orderName()->where('status','ACTIVE')->get();
				$departments                = Department::orderName()->where('status','ACTIVE')->get();
				$banks                      = Banks::orderName()->get();
				$kindbanks                  = KindOfBanks::orderName()->get();
				$sections                   = SectionTickets::orderName()->get();
				return view('configuracion.usuario.alta',
					[
						'id'          => $data['father'],
						'title'       => $data['name'],
						'details'     => $data['details'],
						'child_id'    => 6,
						'option_id'   => 12,
						'roles'       => $roles,
						'enterprises' => $enterprises,
						'areas'       => $areas,
						'departments' => $departments,
						'banks'       => $banks,
						'kindbanks'   => $kindbanks,
						'sections'    => $sections,
						'alert'       => $alert,
						'new_user'    => $new_user
					]);
			}
			else
			{
				$alert 	= "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect()->route('employee.index')->with('alert',$alert);
			}
		}
	}

	public function edit(RealEmployee $employee)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$data = Module::find($this->module_id);
			return view('configuracion.empleado.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 163,
					'employee'  => $employee,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, RealEmployee $employee)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$employee                           = RealEmployee::find($employee->id);
			$employee->name                     = $request->name;
			$employee->last_name                = $request->last_name;
			$employee->scnd_last_name           = $request->scnd_last_name;
			$employee->curp                     = $request->curp;
			$employee->rfc                      = $request->rfc;
			$employee->tax_regime               = $request->tax_regime;
			$employee->imss                     = $request->imss;
			$employee->street                   = $request->street;
			$employee->email                    = $request->email;
			$employee->number                   = $request->number;
			$employee->phone                    = $request->phone;
			$employee->colony                   = $request->colony;
			$employee->cp                       = $request->cp;
			$employee->city                     = $request->city;
			$employee->state_id                 = $request->state;
			$employee->sys_user                 = $request->sys_user;
			$employee->doc_birth_certificate    = $request->doc_birth_certificate;
			$employee->doc_proof_of_address     = $request->doc_proof_of_address;
			$employee->doc_nss                  = $request->doc_nss;
			$employee->doc_ine                  = $request->doc_ine;
			$employee->doc_curp                 = $request->doc_curp;
			$employee->doc_rfc                  = $request->doc_rfc;
			$employee->doc_cv                   = $request->doc_cv;
			$employee->doc_proof_of_studies     = $request->doc_proof_of_studies;
			$employee->doc_professional_license = $request->doc_professional_license;
			$employee->doc_requisition	         = $request->doc_requisition;
			$employee->replace                  = $request->replace;
			$employee->qualified_employee       = $request->qualified_employee;
			$employee->purpose                  = $request->purpose;
			$employee->requeriments             = $request->requeriments;
			$employee->observations             = $request->observations;
			$employee->save();

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
			if(isset($request->edit_data))
			{
				if($request->edit_data != 'x')
				{
					$oldWorker          = WorkerData::find($request->edit_data);
					$oldWorker->visible = 0;
					$oldWorker->save();
				}
				$working             = new WorkerData();
				$working->idEmployee = $employee->id;
				$working->state      = $request->work_state;
				$working->project    = $request->work_project;
				if(isset($request->work_enterprise))
				{
					$working->enterprise = $request->work_enterprise;
				}
				else
				{
					$working->enterprise = $oldWorker->enterprise;
				}
				$working->account           = $request->work_account;
				$working->direction         = $request->work_direction;
				$working->department        = $request->work_department;
				$working->position          = $request->work_position;
				$working->immediate_boss    = $request->work_immediate_boss;
				$working->admissionDate     = $request->work_income_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date)->format('Y-m-d') : null;
				$working->imssDate          = $request->work_imss_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_imss_date)->format('Y-m-d') : null;
				$working->downDate          = $request->work_down_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_down_date)->format('Y-m-d') : null;
				$working->endingDate        = $request->work_ending_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_ending_date)->format('Y-m-d') : null;
				$working->reentryDate       = $request->work_reentry_date != "" ? Carbon::createFromFormat('d-m-Y',$request->work_reentry_date)->format('Y-m-d') : null;
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
				$working->admissionDateOld  = $request->work_income_date_old != "" ? Carbon::createFromFormat('d-m-Y',$request->work_income_date_old)->format('Y-m-d') : null;
				$working->enterpriseOld     = $request->work_enterprise_old;
				$working->viatics					= $request->work_viatics;
				$working->camping					= $request->work_camping;
				$working->position_immediate_boss	= $request->work_position_immediate_boss;
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
			}
			$updater = $request->idEmployeeBank;
			if($request->idEmployeeBank == '')
			{
				$updater = ['x'];
			}
			foreach ($employee->bankData()->whereNotIn('id',$updater)->get() as $del)
			{
				$del->visible = 0;
				$del->save();
			}
			if(isset($request->idEmployeeBank) && count($request->idEmployeeBank) > 0)
			{
				foreach ($request->idEmployeeBank as $k => $e)
				{
					if($e == 'x')
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
						$empAcc->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect(route("employee.edit", $employee))->with('alert',$alert);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$data       = Module::find($this->module_id);
			$name       = $request->name;
			$curp       = $request->curp;
			$status     = $request->status;
			$enterprise = $request->enterprise;
			$project    = $request->project;
			$employee   = RealEmployee::where(function($query) use ($name,$curp,$enterprise,$status,$project)
				{
					if ($name != "") 
					{
						$query->where(\DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if ($curp != "") 
					{
						$query->where('curp','LIKE','%'.$curp.'%');
					}
					if ($enterprise != "" || $status != "" || $project != "")
					{
						$query->whereHas('workerData',function($q) use ($enterprise,$status,$project)
						{
							$q->where('visible',1);
							if($enterprise != "")
							{
								$q->where('enterprise', $enterprise);
							}
							if($project != "")
							{
								$q->where('project', $project);
							}
							if($status != "")
							{
								$q->where('workerStatus', $status);
							}
						});
					}
				})
				->orderBy('id', 'desc')
				->paginate(10);
			return response(
				view('configuracion.empleado.busqueda',
				[
					'id'         => $data['father'],
					'title'      => $data['name'],
					'details'    => $data['details'],
					'child_id'   => $this->module_id,
					'option_id'  => 163,
					'employees'  => $employee,
					'name'       => $name,
					'curp'       => $curp,
					'enterprise' => $enterprise,
					'project'    => $project,
					'status'     => $status
				])
			)->cookie(
				"urlSearch", storeUrlCookie(163), 2880
			);
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
			$curp				=	$request->curp;
			if(preg_match("/^[A-Z]{1}[AEIOUX]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/i", $request->curp))
			{
				$curpRealEmployee 	= 	RealEmployee::where('curp',$curp)
				->whereNotNull('curp')
				->get();
				$curpOwner = VehicleOwner::where('curp',$curp)
				->whereNotNull('curp')
				->get();
				if (count($curpRealEmployee)>0 && $request->oldCurp != $request->curp)
				{
					$response = array(
						'valid'		=> false,
						'class'		=> 'error',
						'message'	=> 'El CURP ya se encuentra registrado.'
					);
					// return Response('RealEmployee');
				}
				else
				{
					$response = array(
						'valid'		=> true,
						'class'		=> 'valid',
						'message'	=> ''
					);
					// return Response('notExist');
				}
			}
			else
			{
				$response = array(
					'valid'		=> false,
					'class' 	=> 'error',
					'message'	=> 'El CURP debe ser válido.'
				);
			}
			return Response($response);
		}
	}

	public function rfcValidate(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo es requerido.'
			);
			if(isset($request->rfc))
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					if(isset($request->folio))
					{
						$staff = Staff::where('idFolio','!=',$request->folio)
							->whereHas('staffEmployees', function ($query) use ($request)
							{
								$query->where('rfc', $request->rfc);
							})
							->get();
						if(count($staff) > 0)
						{
							$response = array(
								'valid'		=> false,
								'message'	=> 'El RFC ya se encuentra registrado.'
							);
						}
						else
						{
							$response = array('valid' => true,'message' => '');
						}
					}
					else
					{
						$exist = RealEmployee::where('rfc',$request->rfc)->get();
						if(count($exist)>0)
						{
							if(isset($request->oldRfc) && $request->oldRfc===$request->rfc)
							{
								$response = array('valid' => true,'class'=>'valid','message' => '');
							}
							else
							{
								$response = array(
									'valid'		=> false,
									'class' 	=> 'error',
									'message'	=> 'El RFC ya se encuentra registrado.'
								);
							}
						}
						else
						{
							$response = array('valid' => true,'message' => '');
						}
					}		
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El RFC debe ser válido.'
					);
				}
			}
			else
			{
				$response = array('valid' => true,'message' => '');
			}
			return Response($response);
		}
	}

	public function emailValidate(Request $request)
	{
		if($request->ajax())
		{
			$response = array(
				'valid'   => false,
				'message' => 'Error.'
			);
			if(isset($request->email) && $request->email != '')
			{
				$existEmail = RealEmployee::where('email','LIKE',$request->email)->count();
				if($request->oldEmail == $request->email)
				{
					$response = array(
						'valid' => true
					);
				}
				else if($existEmail > 0)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'Ya existe un empleado con ese correo.'
					);
				}
				else
				{
					$response = array(
						'valid' => true
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

	public function accountValidate(Request $request)
	{
		if($request->ajax())
		{
			$account = EmployeeAccount::where('account',$request->account)
				->where('idCatBank',$request->bankid)
				->whereNotNull('account')
				->get();
			return Response($account);
		}
	}

	public function clabeValidate(Request $request)
	{
		if($request->ajax())
		{
			$clabe = EmployeeAccount::where('clabe',$request->clabe)->get();
			return Response($clabe);
		}
	}

	public function cardValidate(Request $request)
	{
		if($request->ajax())
		{
			$card = EmployeeAccount::where('cardNumber',$request->card)->get();
			return Response($card);
		}
	}

	public function massive()
	{
		if(Auth::user()->module->where('id',162)->count()>0)
		{
			$data			= Module::find($this->module_id);
			return view('configuracion.empleado.masivo',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 162
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id',162)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert	= "swal('', '".Lang::get("messages.file_null")."', 'error');";
				return back()->with('alert',$alert);	
			}
			if($request->file('csv_file')->isValid())
			{
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
						$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
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
						'estado_laboral',
						'proyecto',
						'wbs',
						'empresa',
						'clasificacion_gasto',
						'lugar_trabajo',
						'direccion',
						'departamento',
						'puesto',
						'jefe_inmediato',
						'fecha_ingreso',
						'fecha_alta',
						'fecha_baja',
						'fecha_termino',
						'fecha_reingreso',
						'tipo_contrato',
						'regimen',
						'estatus',
						'sdi',
						'periodicidad',
						'registro_patronal',
						'forma_pago',
						'sueldo_neto',
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
						'sucursal'
					];

					// Función para validar documentos diferentes
					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					$data = Module::find($this->module_id);
					return view('configuracion.empleado.verificar_masivo',
						[
							'id'        => $data['father'],
							'title'     => $data['name'],
							'details'   => $data['details'],
							'child_id'  => $this->module_id,
							'option_id' => 162,
							'csv'       => $csvArr,
							'fileName'  => $name,
							'delimiter' => $request->separator
						]);
				}
				else
				{
					$alert	= "swal('', '".Lang::get("messages.separator_error")."', 'error');";
					return back()->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
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
		if(Auth::user()->module->where('id',162)->count()>0)
		{
			$path   = \Storage::disk('reserved')->path($request->fileName);
			$csvArr = array();
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
			$updatedEmployee = array();
			$savedEmployee   = array();
			$errorEmployee   = array();
			foreach ($csvArr as $key => $e)
			{
				try
				{
					$exist	= RealEmployee::where('curp',$e['curp'])->get();
					if(count($exist) > 0)
					{
						$employee  = $exist->first();
						$oldWorker = $employee->workerDataVisible->first();
						if($oldWorker != null && $oldWorker->workerStatus == 5)
						{
							$csvArr[$key]['status']	= 'Empleado boletinado';
						}
						elseif(isset($e['empresa']) && $e['empresa'] != '' && $oldWorker != null && $oldWorker->enterprise != null && $oldWorker->enterprise != $e['empresa'] && ($oldWorker->downDate == '' || $oldWorker->workerStatus == 1))
						{
							$csvArr[$key]['status']	= 'No actualizado (primero debe darlo de baja de la empresa actual)';
						}
						else
						{
							$employee->name				= !isset($e['nombre']) || empty(trim($e['nombre'])) ? $employee->name : $e['nombre'];
							$employee->last_name		= !isset($e['apellido']) || empty(trim($e['apellido'])) ? $employee->last_name : $e['apellido'];
							$employee->scnd_last_name	= !isset($e['apellido2']) || empty(trim($e['apellido2'])) ? $employee->scnd_last_name : $e['apellido2'];
							$employee->curp				= !isset($e['curp']) || empty(trim($e['curp'])) ? $employee->curp : $e['curp'];
							$employee->rfc				= !isset($e['rfc']) || empty(trim($e['rfc'])) ? $employee->rfc : str_replace('-','',$e['rfc']);
							$employee->tax_regime		= !isset($e['regimen_fiscal']) || empty(trim($e['regimen_fiscal'])) ? $employee->tax_regime : (CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() > 0 ? $e['regimen_fiscal'] : null);
							$employee->imss				= !isset($e['imss']) || empty(trim($e['imss'])) ? $employee->imss : $e['imss'];
							$employee->street			= !isset($e['calle']) || empty(trim($e['calle'])) ? $employee->street : $e['calle'];
							$employee->number			= !isset($e['numero']) || empty(trim($e['numero'])) ? $employee->number : $e['numero'];
							$employee->colony			= !isset($e['colonia']) || empty(trim($e['colonia'])) ? $employee->colony : $e['colonia'];
							$employee->cp				= !isset($e['cp']) || empty(trim($e['cp'])) ? $employee->cp : (CatZipCode::where('zip_code',$e['cp'])->count() > 0 ? $e['cp'] : null);
							$employee->city				= !isset($e['ciudad']) || empty(trim($e['ciudad'])) ? $employee->city : $e['ciudad'];
							$employee->state_id			= !isset($e['estado']) || empty(trim($e['estado'])) ? $employee->state_id : $e['estado'];
							$employee->email			= !isset($e['correo_electronico']) || empty(trim($e['correo_electronico'])) ? $employee->email : $e['correo_electronico'];
							$employee->phone			= !isset($e['numero_telefonico']) || empty(trim($e['numero_telefonico'])) ? $employee->phone : $e['numero_telefonico'];
							$employee->replace			= !isset($e['en_reemplazo_de']) || empty(trim($e['en_reemplazo_de'])) ? $employee->replace : $e['en_reemplazo_de'];
							$employee->purpose			= !isset($e['proposito_del_puesto']) || empty(trim($e['proposito_del_puesto'])) ? $employee->purpose : $e['proposito_del_puesto'];
							$employee->requeriments		= !isset($e['requerimientos_del_puesto']) || empty(trim($e['requerimientos_del_puesto'])) ? $employee->requeriments : $e['requerimientos_del_puesto'];
							$employee->observations		= !isset($e['observaciones']) || empty(trim($e['observaciones'])) ? $employee->observations : $e['observaciones'];
							$employee->save();
							
							$csvArr[$key]['nombre']    = $employee->name;
							$csvArr[$key]['apellido']  = $employee->last_name;
							$csvArr[$key]['apellido2'] = $employee->scnd_last_name;
							
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
							
							$working->idEmployee            = $employee->id;
							$working->state                 = !isset($e['estado_laboral']) || empty(trim($e['estado_laboral'])) ? $working->state : $e['estado_laboral'];
							$working->project               = !isset($e['proyecto']) || empty(trim($e['proyecto'])) ? $working->project : $e['proyecto'];
							$working->enterprise            = !isset($e['empresa']) || empty(trim($e['empresa'])) ? $working->enterprise : $e['empresa'];
							$existsAccount = Account::where('idEnterprise',$e['empresa'])
								->where('idAccAcc',$e['clasificacion_gasto'])
								->get();
							if(count($existsAccount) > 0 )
							{
								$working->account			= $e['clasificacion_gasto'];
							}
							$working->direction             = !isset($e['direccion']) || empty(trim($e['direccion'])) ? $working->direction : $e['direccion'];
							$working->department            = !isset($e['departamento']) || empty(trim($e['departamento'])) ? $working->department : $e['departamento'];
							$working->position              = !isset($e['puesto']) || empty(trim($e['puesto'])) ? $working->position : $e['puesto'];
							$working->immediate_boss        = !isset($e['jefe_inmediato']) || empty(trim($e['jefe_inmediato'])) ? $working->immediate_boss : ucwords($e['jefe_inmediato']);
							$working->position_immediate_boss = !isset($e['posicion_jefe_inmediato']) || empty(trim($e['posicion_jefe_inmediato'])) ? $working->position_immediate_boss : ucwords($e['posicion_jefe_inmediato']);
							$working->admissionDate         = !isset($e['fecha_ingreso']) || empty(trim($e['fecha_ingreso'])) ? $working->admissionDate : $e['fecha_ingreso'];
							$working->imssDate              = !isset($e['fecha_alta']) || empty(trim($e['fecha_alta'])) ? $working->imssDate : $e['fecha_alta'];
							$working->downDate              = !isset($e['fecha_baja']) || empty(trim($e['fecha_baja'])) ? $working->downDate : $e['fecha_baja'];
							$working->endingDate            = !isset($e['fecha_termino']) || empty(trim($e['fecha_termino'])) ? $working->endingDate : $e['fecha_termino'];
							$working->reentryDate           = !isset($e['fecha_reingreso']) || empty(trim($e['fecha_reingreso'])) ? $working->reentryDate : $e['fecha_reingreso'];
							$working->workerType            = !isset($e['tipo_contrato']) || empty(trim($e['tipo_contrato'])) ? $working->workerType : $e['tipo_contrato'];
							$working->regime_id             = !isset($e['regimen']) || empty(trim($e['regimen'])) ? $working->regime_id : $e['regimen'];
							$working->workerStatus          = !isset($e['estatus']) || empty(trim($e['estatus'])) ? $working->workerStatus : $e['estatus'];
							$working->status_imss           = !isset($e['estatus_imss']) || trim($e['estatus_imss']) ? $working->status_imss : $e['estatus_imss'];
							$working->status_reason         = !isset($e['razon_estatus']) || empty(trim($e['razon_estatus'])) ? $working->status_reason : $e['razon_estatus'];
							$working->sdi                   = !isset($e['sdi']) || empty(trim($e['sdi'])) ? $working->sdi : $e['sdi'];
							$working->periodicity           = !isset($e['periodicidad']) || empty(trim($e['periodicidad'])) ? $working->periodicity : $e['periodicidad'];
							$working->employer_register     = !isset($e['registro_patronal']) || empty(trim($e['registro_patronal'])) ? $working->employer_register : $e['registro_patronal'];
							$working->paymentWay            = !isset($e['forma_pago']) || empty(trim($e['forma_pago'])) ? $working->paymentWay : $e['forma_pago'];
							$working->netIncome             = !isset($e['sueldo_neto']) || empty(trim($e['sueldo_neto'])) ? $working->netIncome : $e['sueldo_neto'];
							$working->complement            = !isset($e['complemento']) || empty(trim($e['complemento'])) ? $working->complement : $e['complemento'];
							$working->viatics           	= !isset($e['viaticos']) || empty(trim($e['viaticos'])) ? $working->viatics : $e['viaticos'];
							$working->camping            	= !isset($e['campamento']) || empty(trim($e['campamento'])) ? $working->camping : $e['campamento'];
							$working->fonacot               = !isset($e['fonacot']) || empty(trim($e['fonacot'])) ? $working->fonacot : $e['fonacot'];
							$working->nomina                = !isset($e['porcentaje_nomina']) || empty(trim($e['porcentaje_nomina'])) ? $working->nomina : intval($e['porcentaje_nomina']);
							$working->bono                  = !isset($e['porcentaje_bono']) || empty(trim($e['porcentaje_bono'])) && $e['porcentaje_bono'] != 0 ? $working->bono : intval($e['porcentaje_bono']);
							$working->recorder              = Auth::user()->id;
							$working->infonavitCredit       = !isset($e['credito_infonavit']) || empty(trim($e['credito_infonavit'])) ? $working->infonavitCredit : $e['credito_infonavit'];
							$working->infonavitDiscount     = !isset($e['descuento_infonavit']) || empty(trim($e['descuento_infonavit'])) ? $working->infonavitDiscount : floatval($e['descuento_infonavit']);
							$working->infonavitDiscountType = !isset($e['tipo_descuento_infonavit']) || empty(trim($e['tipo_descuento_infonavit'])) ? $working->infonavitDiscountType : intval($e['tipo_descuento_infonavit']);
							$working->save();
							if(isset($e['lugar_trabajo']) && $e['lugar_trabajo'] != '')
							{
								$working->places()->detach();
								$working->places()->attach([$e['lugar_trabajo']]);
							}
							$arrayWbs 	= [];
							$existsWBS	= [];
							if(isset($e['wbs']) && isset($e['proyecto']) && $e['wbs'] != '' && $e['proyecto'] != '')
							{				
								$arrayWbs 	= explode(',', $e['wbs']);
								$existsWBS	= CatCodeWBS::select('id')
									->where('project_id',$e['proyecto'])
									->whereIn('id', $arrayWbs)
									->get();
								if(count($existsWBS) > 0)
								{
									$working->employeeHasWbs()->detach();
									$working->employeeHasWbs()->attach($existsWBS);
								}
								elseif($oldWorker != null && $oldWorker->employeeHasWbs()->exists())
								{
									$working->employeeHasWbs()->attach($oldWorker->employeeHasWbs->pluck('id'));
								}
							}
							elseif($oldWorker != null && $oldWorker->employeeHasWbs()->exists())
							{
								$working->employeeHasWbs()->attach($oldWorker->employeeHasWbs->pluck('id'));
							}
							if(isset($e['subdepartamento']) && $e['subdepartamento'] != '')
							{
								$arraySubdepartment = explode(',', $e['subdepartamento']);
								$working->employeeHasSubdepartment()->detach();
								$working->employeeHasSubdepartment()->attach($arraySubdepartment);
							}
							if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='')
							{
								$accountExist	= EmployeeAccount::where('account',trim($e['cuenta']))->where('idCatBank',trim($e['banco']))->where('visible',1)->get();
								$clabeExist		= EmployeeAccount::where('clabe',trim($e['clabe']))->where('visible',1)->get();
								$cardExist		= EmployeeAccount::where('cardNumber',trim($e['tarjeta']))->where('visible',1)->get();
								$empAcc             = new EmployeeAccount();
								$empAcc->idEmployee = $employee->id;
								$empAcc->alias      = empty(trim($e['alias'])) ? null : $e['alias'];
								$empAcc->clabe      = empty(trim($e['clabe'])) ? null : trim($e['clabe']);
								$empAcc->account    = empty(trim($e['cuenta'])) ? null : trim($e['cuenta']);
								$empAcc->cardNumber = empty(trim($e['tarjeta'])) ? null : trim($e['tarjeta']);
								$empAcc->branch     = empty(trim($e['sucursal'])) ? null : $e['sucursal'];
								$empAcc->idCatBank  = empty(trim($e['banco'])) ? null : $e['banco'];
								$empAcc->recorder   = Auth::user()->id;
								$empAcc->type       = 1;
					
								$flagAccount 	= false;
								$flagCardNumber = false;
								$flagClabe 		= false;

								if(trim($empAcc->account) !='' || trim($empAcc->clabe) !='' || trim($empAcc->cardNumber)!='')
								{
									if(trim($empAcc->account) !='')
									{
										
										if(count($accountExist) == null && strlen(trim($empAcc->account)) <= 15 && strlen(trim($empAcc->account)) >= 5)
										{
											$flagAccount = true;
										}
									}
									else
									{
										$flagAccount = true;
									}
	
									if(trim($empAcc->clabe) != '')
									{
										if(count($clabeExist) == null && strlen(trim($empAcc->clabe)) == 18)
										{
											
											$flagClabe = true;
										}
									}
									else
									{
										$flagClabe = true;
									}
	
									if(trim($empAcc->cardNumber) !='')
									{
										if(count($cardExist) == null && strlen(trim($empAcc->cardNumber)) == 16)
										{
											
											$flagCardNumber = true;
										}
									}
									else
									{
										$flagCardNumber = true;
									}
								}
								if($flagAccount == true && $flagCardNumber == true && $flagClabe == true)
								{
									$empAcc->save();
								}
								
							}
							if(!in_array($employee->id, $updatedEmployee))
							{
								$updatedEmployee[]   = $employee->id;
								$massive             = new MassiveEmployee();
								$massive->idEmployee = $employee->id;
								$massive->idCreator  = Auth::user()->id;
								$massive->csv        = $request->fileName;
								$massive->save();
							}
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
								($e['cp'] != '' && CatZipCode::where('zip_code',$e['cp'])->count() == 0) ||
								(isset($e['wbs']) && $e['wbs'] != '' && count($arrayWbs) != count($existsWBS)) ||
								count($existsAccount) == ''
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
							$employee					= new RealEmployee();
							$employee->name				= empty(trim($e['nombre'])) ? null : $e['nombre'];
							$employee->last_name		= empty(trim($e['apellido'])) ? null : $e['apellido'];
							$employee->scnd_last_name	= empty(trim($e['apellido2'])) ? null : $e['apellido2'];
							$employee->curp				= empty(trim($e['curp'])) ? null : $e['curp'];
							$employee->rfc				= empty(trim($e['rfc'])) ? null : str_replace('-','',$e['rfc']);
							$employee->tax_regime		= empty(trim($e['regimen_fiscal'])) ? null : (CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() > 0 ? $e['regimen_fiscal'] : null);
							$employee->imss				= empty(trim($e['imss'])) ? null : $e['imss'];
							$employee->street			= empty(trim($e['calle'])) ? null : $e['calle'];
							$employee->number			= empty(trim($e['numero'])) ? null : $e['numero'];
							$employee->colony			= empty(trim($e['colonia'])) ? null : $e['colonia'];
							$employee->cp				= empty(trim($e['cp'])) ? null : (CatZipCode::where('zip_code',$e['cp'])->count() > 0 ? $e['cp'] : null);
							$employee->city				= empty(trim($e['ciudad'])) ? null : $e['ciudad'];
							$employee->state_id			= empty(trim($e['estado'])) ? null : $e['estado'];
							$employee->email			= empty(trim($e['correo_electronico'])) ? null : $e['correo_electronico'];
							$employee->phone			= empty(trim($e['numero_telefonico'])) ? null : $e['numero_telefonico'];
							$employee->replace			= empty(trim($e['en_reemplazo_de'])) ? null : $e['en_reemplazo_de'];
							$employee->purpose			= empty(trim($e['proposito_del_puesto'])) ? null : $e['proposito_del_puesto'];
							$employee->requeriments		= empty(trim($e['requerimientos_del_puesto'])) ? null : $e['requerimientos_del_puesto'];
							$employee->observations		= empty(trim($e['observaciones'])) ? null : $e['observaciones'];
							$employee->save();
							$working                        = new WorkerData();
							$working->idEmployee            = $employee->id;
							$working->state                 = empty(trim($e['estado_laboral'])) ? null : $e['estado_laboral'];
							$working->project               = empty(trim($e['proyecto'])) ? null : $e['proyecto'];
							$working->enterprise            = empty(trim($e['empresa'])) ? null : $e['empresa'];
							$existsAccount = Account::where('idEnterprise',$e['empresa'])
								->where('idAccAcc',$e['clasificacion_gasto'])
								->get();
							if(count($existsAccount) > 0 )
							{
								$working->account			= $e['clasificacion_gasto'];
							}
							$working->direction             = empty(trim($e['direccion'])) ? null : $e['direccion'];
							$working->department            = empty(trim($e['departamento'])) ? null : $e['departamento'];
							$working->position              = empty(trim($e['puesto'])) ? null : $e['puesto'];
							$working->immediate_boss        = empty(trim($e['jefe_inmediato'])) ? null : ucwords($e['jefe_inmediato']);
							$working->position_immediate_boss = empty(trim($e['posicion_jefe_inmediato'])) ? null : ucwords($e['posicion_jefe_inmediato']);
							$working->admissionDate         = empty(trim($e['fecha_ingreso'])) ? null : $e['fecha_ingreso'];
							$working->imssDate              = empty(trim($e['fecha_alta'])) ? null : $e['fecha_alta'];
							$working->downDate              = empty(trim($e['fecha_baja'])) ? null : $e['fecha_baja'];
							$working->endingDate            = empty(trim($e['fecha_termino'])) ? null : $e['fecha_termino'];
							$working->reentryDate           = empty(trim($e['fecha_reingreso'])) ? null : $e['fecha_reingreso'];
							$working->workerType            = empty(trim($e['tipo_contrato'])) ? null : $e['tipo_contrato'];
							$working->regime_id             = empty(trim($e['regimen'])) ? null : $e['regimen'];
							$working->status_imss           = empty(trim($e['estatus_imss'])) ? null : $e['estatus_imss'];
							$working->workerStatus          = empty(trim($e['estatus'])) ? null : $e['estatus'];
							$working->status_reason         = empty(trim($e['razon_estatus'])) ? null : $e['razon_estatus'];
							$working->sdi                   = empty(trim($e['sdi'])) ? null : $e['sdi'];
							$working->periodicity           = empty(trim($e['periodicidad'])) ? null : $e['periodicidad'];
							$working->employer_register     = empty(trim($e['registro_patronal'])) ? null : $e['registro_patronal'];
							$working->paymentWay            = empty(trim($e['forma_pago'])) ? null : $e['forma_pago'];
							$working->netIncome             = empty(trim($e['sueldo_neto'])) ? null : $e['sueldo_neto'];
							$working->complement            = empty(trim($e['complemento'])) ? null : $e['complemento'];
							$working->viatics            	= empty(trim($e['viaticos'])) ? null : $e['viaticos'];
							$working->camping            	= empty(trim($e['campamento'])) ? null : $e['campamento'];
							$working->fonacot               = empty(trim($e['fonacot'])) ? null : $e['fonacot'];
							$working->nomina                = empty(trim($e['porcentaje_nomina'])) ? null : intval($e['porcentaje_nomina']);
							$working->bono                  = empty(trim($e['porcentaje_bono'])) && $e['porcentaje_bono'] != 0 ? null : intval($e['porcentaje_bono']);
							$working->recorder              = Auth::user()->id;
							$working->infonavitCredit       = empty(trim($e['credito_infonavit'])) ? null : $e['credito_infonavit'];
							$working->infonavitDiscount     = empty(trim($e['descuento_infonavit'])) ? null : floatval($e['descuento_infonavit']);
							$working->infonavitDiscountType = empty(trim($e['tipo_descuento_infonavit'])) ? null : intval($e['tipo_descuento_infonavit']);
							$working->save();
							if($e['lugar_trabajo'] != '')
							{
								$working->places()->attach([$e['lugar_trabajo']]);
							}
							$arrayWbs 	= [];
							$existsWBS	= [];
							if(isset($e['wbs']) && isset($e['proyecto']) && $e['wbs'] != '' && $e['proyecto'] != '')
							{
								$arrayWbs 	= explode(',', $e['wbs']);
								$existsWBS	= CatCodeWBS::select('id')
									->where('project_id',$e['proyecto'])
									->whereIn('id',$arrayWbs)
									->get();
								if(count($existsWBS) > 0)
								{
									$working->employeeHasWbs()->attach($existsWBS);
								}
							}
							if(isset($e['subdepartamento']) && $e['subdepartamento'] != '')
							{
								$arraySubdepartment = explode(',', $e['subdepartamento']);
								$working->employeeHasSubdepartment()->attach($arraySubdepartment);
							}
							if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='')
							{
								$accountExist	= EmployeeAccount::where('account',trim($e['cuenta']))->where('idCatBank',trim($e['banco']))->where('visible',1)->get();
								$clabeExist		= EmployeeAccount::where('clabe',trim($e['clabe']))->where('visible',1)->get();
								$cardExist		= EmployeeAccount::where('cardNumber',trim($e['tarjeta']))->where('visible',1)->get();
								$empAcc             = new EmployeeAccount();
								$empAcc->idEmployee = $employee->id;
								$empAcc->alias      = empty(trim($e['alias'])) ? null : $e['alias'];
								$empAcc->clabe      = empty(trim($e['clabe'])) ? null : $e['clabe'];
								$empAcc->account    = empty(trim($e['cuenta'])) ? null : $e['cuenta'];
								$empAcc->cardNumber = empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
								$empAcc->branch     = empty(trim($e['sucursal'])) ? null : $e['sucursal'];
								$empAcc->idCatBank  = empty(trim($e['banco'])) ? null : $e['banco'];
								$empAcc->recorder   = Auth::user()->id;
								$empAcc->type       = 1;
								
								
								$flagAccount 	= false;
								$flagCardNumber = false;
								$flagClabe 		= false;

								if(trim($empAcc->account) !='' || trim($empAcc->clabe) !='' || trim($empAcc->cardNumber)!='')
								{
									if(trim($empAcc->account) !='')
									{
										if(count($accountExist) == null && strlen(trim($empAcc->account)) <= 15 && strlen(trim($empAcc->account)) >= 5)
										{
											$flagAccount = true;
										}
									}
									else
									{
										$flagAccount = true;
									}
	
									if(trim($empAcc->clabe) != '')
									{
										if(count($clabeExist) == null && strlen(trim($empAcc->clabe)) == 18)
										{
											$flagClabe = true;
										}
									}
									else
									{
										$flagClabe = true;
									}
	
									if(trim($empAcc->cardNumber) !='')
									{
										if(count($cardExist) == null && strlen(trim($empAcc->cardNumber)) == 16)
										{
											$flagCardNumber = true;
										}
									}
									else
									{
										$flagCardNumber = true;
									}
								}
								if($flagAccount == true && $flagCardNumber == true && $flagClabe == true)
								{
									$empAcc->save();
								}
								
							}
							if(!in_array($employee->id, $savedEmployee))
							{
								$savedEmployee[]     = $employee->id;
								$massive             = new MassiveEmployee();
								$massive->idEmployee = $employee->id;
								$massive->idCreator  = Auth::user()->id;
								$massive->csv        = $request->fileName;
								$massive->save();
							}
							/*
							if(is_null($employee->name))
							{
								return '$employee->name';
							}
							if(is_null($employee->last_name))
							{
								return '$employee->last_name';
							}
							if(is_null($employee->curp))
							{
								return '$employee->curp';
							}
							if(is_null($employee->street))
							{
								return '$employee->street';
							}
							if(is_null($employee->number))
							{
								return '$employee->number';
							}
							if(is_null($employee->colony))
							{
								return '$employee->colony';
							}
							if(is_null($employee->cp))
							{
								return '$employee->cp';
							}
							if(is_null($employee->city))
							{
								return '$employee->city';
							}
							if(is_null($employee->state_id))
							{
								return '$employee->state_id';
							}
							if(is_null($employee->email))
							{
								return '$employee->email';
							}
							if(is_null($working->idEmployee))
							{
								return '$working->idEmployee';
							}
							if(is_null($working->state))
							{
								return '$working->state';
							}
							if(is_null($working->enterprise))
							{
								return '$working->enterprise';
							}
							if(is_null($working->account))
							{
								return '$working->account';
							}
							if(is_null($working->direction))
							{
								return '$working->direction';
							}
							if(is_null($working->position))
							{
								return '$working->position';
							}
							if(is_null($working->admissionDate))
							{
								return '$working->admissionDate';
							}
							if(is_null($working->workerType))
							{
								return '$working->workerType';
							}
							if(is_null($working->regime_id))
							{
								return '$working->regime_id';
							}
							if(is_null($working->workerStatus))
							{
								return '$working->workerStatus';
							}
							if(is_null($working->periodicity))
							{
								return '$working->periodicity';
							}
							if(is_null($working->employer_register))
							{
								return '$working->employer_register';
							}
							if(is_null($working->paymentWay))
							{
								return '$working->paymentWay';
							}
							if(is_null($working->nomina))
							{
								return '$working->nomina';
							}
							if(is_null($working->bono))
							{
								return '$working->bono';
							}
							if (($e['regimen_fiscal'] != '' && CatTaxRegime::where('taxRegime',$e['regimen_fiscal'])->where('physical','Sí')->count() == 0)) 
							{
								return 'taxRegime';
							}
							if (($e['cp'] != '' && CatZipCode::where('zip_code',$e['cp'])->count() == 0)) 
							{
								return 'cp';
							}
							if ((isset($e['wbs']) && $e['wbs'] != '' && count($arrayWbs) != count($existsWBS))) 
							{
								return 'wbs';
							}
							if (count($existsAccount)) 
							{
								return 'existsAccount';
							}
							return 'exito';
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
								($e['cp'] != '' && CatZipCode::where('zip_code',$e['cp'])->count() == 0) ||
								(isset($e['wbs']) && $e['wbs'] != '' && count($arrayWbs) != count($existsWBS)) ||
								count($existsAccount) == ''
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
				catch (\Exception $e)
				{
					if(!in_array($employee->id, $errorEmployee))
					{
						$errorEmployee[] = $employee->id;
						if($employee->id!='')
						{
							$massive             = new MassiveEmployee();
							$massive->idEmployee = $employee->id;
							$massive->idCreator  = Auth::user()->id;
							$massive->csv        = $request->fileName;
							$massive->save();
						}
						
					}
					if($employee->id != '')
					{
						$csvArr[$key]['status'] = 'Guardado con errores';
						$csvArr[$key]['id']     = $employee->id;
					}
					else
					{
						$csvArr[$key]['status'] = 'Error';
						$csvArr[$key]['id']     = '';
					}	
				}
			}
			$data    = Module::find($this->module_id);
			return view('configuracion.empleado.resultado_masivo',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 162,
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
		if(Auth::user()->module->where('id',162)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('employee.massive');
		}
		else
		{
			return redirect('/');
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$name       = $request->name;
			$curp       = $request->curp;
			$status     = $request->status;
			$enterprise = $request->enterprise;
			$project    = $request->project;
			$employees  = DB::table('real_employees')->selectRaw(
				'
					real_employees.name,
					real_employees.last_name,
					real_employees.scnd_last_name,
					real_employees.curp,
					real_employees.rfc,
					real_employees.tax_regime,
					real_employees.imss,
					real_employees.street,
					real_employees.number,
					real_employees.colony,
					real_employees.cp,
					real_employees.city,
					real_employees.state_id,
					real_employees.phone,
					real_employees.email,
					real_employees.replace,
					real_employees.purpose,
					real_employees.requeriments,
					real_employees.observations,
					worker_data.state,
					worker_data.project,
					wbsData.wbsData,
					worker_data.enterprise,
					worker_data.account as accounting_account,
					places.places,
					worker_data.direction,
					worker_data.department,
					subdepartmentData.subdepartmentData,
					worker_data.position,
					worker_data.position_immediate_boss,
					worker_data.immediate_boss,
					worker_data.status_imss,
					worker_data.admissionDate,
					worker_data.imssDate,
					worker_data.downDate,
					worker_data.endingDate,
					worker_data.reentryDate,
					worker_data.workerType,
					worker_data.regime_id,
					worker_data.workerStatus,
					worker_data.status_reason,
					worker_data.sdi,
					worker_data.periodicity,
					worker_data.employer_register,
					worker_data.paymentWay,
					worker_data.netIncome,
					worker_data.viatics,
					worker_data.camping,
					worker_data.complement,
					worker_data.fonacot,
					worker_data.nomina,
					worker_data.bono,
					worker_data.infonavitCredit,
					worker_data.infonavitDiscount,
					worker_data.infonavitDiscountType,
					bank_data.alias,
					bank_data.idCatBank,
					CONCAT(bank_data.clabe," ") as clabe,
					CONCAT(bank_data.account," ") as account,
					CONCAT(bank_data.cardNumber," ") as cardNumber,
					bank_data.branch
				')
				->leftJoin(DB::raw('(select * from worker_datas WHERE visible = 1) as worker_data'),'real_employees.id','worker_data.idEmployee')
				->leftJoin(DB::raw('(SELECT * FROM employee_accounts WHERE id IN(SELECT MIN(id) as id FROM employee_accounts WHERE visible = 1 GROUP BY idEmployee)) as bank_data'),'real_employees.id','bank_data.idEmployee')
				->leftJoin(DB::raw('(SELECT idWorkingData, GROUP_CONCAT(idPlace SEPARATOR ", ") as places FROM worker_data_places GROUP BY idWorkingData) as places'),'worker_data.id','places.idWorkingData')
				->leftJoin(DB::raw('(SELECT working_data_id, GROUP_CONCAT(cat_code_w_bs_id SEPARATOR ", ") as wbsData FROM employee_w_b_s GROUP BY working_data_id) as wbsData'),'worker_data.id','wbsData.working_data_id')
				->leftJoin(DB::raw('(SELECT working_data_id, GROUP_CONCAT(subdepartment_id SEPARATOR ", ") as subdepartmentData FROM employee_subdepartments GROUP BY working_data_id) as subdepartmentData'),'worker_data.id','subdepartmentData.working_data_id')
				->where(function($query) use ($name,$curp,$enterprise,$status,$project)
				{
					if ($name != "") 
					{
						$query->where(\DB::raw("CONCAT_WS(' ',real_employees.name,real_employees.last_name,real_employees.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if ($curp != "") 
					{
						$query->where('real_employees.curp','LIKE','%'.$curp.'%');
					}
					if ($enterprise != "" || $status != "" || $project != "")
					{
						if($enterprise != "")
						{
							$query->where('worker_data.enterprise', $enterprise);
						}
						if($project != "")
						{
							$query->where('worker_data.project', $project);
						}
						if($status != "")
						{
							$query->where('worker_data.workerStatus', $status);
						}
					}
				})
				->get();
			if(count($employees)==0 || is_null($employees))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol     = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-empleados.xlsx');
			$writer->getCurrentSheet()->setName('Empleados registrados');

			$subHeader    = [
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
								'numero_telefonico',
								'correo_electronico',
								'en_reemplazo_de',
								'proposito_del_puesto',
								'requerimientos_del_puesto',
								'observaciones',
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
								'posicion_jefe_inmediato',
								'jefe_inmediato',
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
								'sucursal'
							];
			
			$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if($k >= 12)
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				if($k >= 45)
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('33A8F2')->setFontColor(Color::WHITE)->build();
				}
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($employees as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportComplete(Request $request)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$name       = $request->name;
			$curp       = $request->curp;
			$status     = $request->status;
			$enterprise = $request->enterprise;
			$project    = $request->project;
			$employees  = DB::table('real_employees')->selectRaw(
						'
							real_employees.name,
							real_employees.last_name,
							real_employees.scnd_last_name,
							real_employees.curp,
							real_employees.rfc,
							real_employees.imss,
							real_employees.street,
							real_employees.number,
							real_employees.colony,
							real_employees.cp,
							real_employees.city,
							states.description as state,
							work_state.description as work_state,
							projects.proyectName as project,
							wbs.wbs as wbs,
							subdepartments.subdepartment as subdepartment,
							enterprises.name as enterprise,
							CONCAT(accounts.account," - ",accounts.description," (",accounts.content,")") as accounting_account,
							places.places as places,
							areas.name as area,
							departments.name as department,
							worker_data.position,
							worker_data.immediate_boss,
							DATE_FORMAT(worker_data.admissionDate, "%d-%m-%Y") as admissionDate,
							DATE_FORMAT(worker_data.imssDate, "%d-%m-%Y") as imssDate,
							DATE_FORMAT(worker_data.downDate, "%d-%m-%Y") as downDate,
							DATE_FORMAT(worker_data.endingDate, "%d-%m-%Y") as endingDate,
							DATE_FORMAT(worker_data.reentryDate, "%d-%m-%Y") as reentryDate,
							cat_contract_types.description as contract_type,
							cat_regime_types.description as regime_type,
							IF(
								worker_data.workerStatus = 1,
								"Activo",
								IF(
									worker_data.workerStatus = 2,
									"Baja pacial",
									IF(
										worker_data.workerStatus = 3,
										"Baja definitiva",
										IF(
											worker_data.workerStatus = 4,
											"Suspensión",
											"Boletinado"
										)
									)
								)
							) as worker_status,
							worker_data.sdi,
							cat_periodicities.description as periodicity,
							worker_data.employer_register,
							payment_methods.method as paymentWay,
							worker_data.netIncome,
							worker_data.viatics,
							worker_data.camping,
							worker_data.complement,
							worker_data.fonacot,
							worker_data.nomina,
							worker_data.bono,
							worker_data.infonavitCredit,
							worker_data.infonavitDiscount,
							worker_data.infonavitDiscountType,
							bank_data.alias,
							cat_banks.description as employee_bank,
							CONCAT(bank_data.clabe," ") as clabe,
							CONCAT(bank_data.account," ") as account,
							CONCAT(bank_data.cardNumber," ") as cardNumber,
							bank_data.branch,
							IF(real_employees.doc_birth_certificate IS NULL,"","X") as doc_birth_certificate,
							IF(real_employees.doc_proof_of_address IS NULL,"","X") as doc_proof_of_address,
							IF(real_employees.doc_nss IS NULL,"","X") as doc_nss,
							IF(real_employees.doc_ine IS NULL,"","X") as doc_ine,
							IF(real_employees.doc_curp IS NULL,"","X") as doc_curp,
							IF(real_employees.doc_rfc IS NULL,"","X") as doc_rfc,
							IF(real_employees.doc_cv IS NULL,"","X") as doc_cv,
							IF(real_employees.doc_proof_of_studies IS NULL,"","X") as doc_proof_of_studies,
							IF(real_employees.doc_professional_license IS NULL,"","X") as doc_professional_license

						')
						->leftJoin(DB::raw('(select * from worker_datas WHERE visible = 1) as worker_data'),'real_employees.id','worker_data.idEmployee')
						->leftJoin(DB::raw('(SELECT * FROM employee_accounts WHERE id IN(SELECT MIN(id) as id FROM employee_accounts WHERE visible = 1 GROUP BY idEmployee)) as bank_data'),'real_employees.id','bank_data.idEmployee')
						->leftJoin(DB::raw('(SELECT idWorkingData, GROUP_CONCAT(place SEPARATOR ", ") as places FROM worker_data_places INNER JOIN places ON worker_data_places.idPlace=places.id GROUP BY idWorkingData) as places'),'worker_data.id','places.idWorkingData')
						->leftJoin('states','real_employees.state_id','states.idstate')
						->leftJoin('states as work_state','worker_data.state','work_state.idstate')
						->leftJoin('projects','worker_data.project','projects.idproyect')
						->leftJoin(
							DB::raw('(SELECT GROUP_CONCAT(code_wbs SEPARATOR ", ") as wbs, working_data_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON employee_w_b_s.cat_code_w_bs_id=cat_code_w_bs.id GROUP BY working_data_id) as wbs'),
							'worker_data.id',
							'wbs.working_data_id'
						)
						->leftJoin('enterprises','worker_data.enterprise','enterprises.id')
						->leftJoin('accounts','worker_data.account','accounts.idAccAcc')
						->leftJoin('areas','worker_data.direction','areas.id')
						->leftJoin('departments','worker_data.department','departments.id')
						->leftJoin(DB::raw('(SELECT working_data_id, GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartment FROM employee_subdepartments JOIN subdepartments ON employee_subdepartments.subdepartment_id = subdepartments.id GROUP BY working_data_id) as subdepartments'),'worker_data.id','subdepartments.working_data_id')
						->leftJoin('cat_contract_types','worker_data.workerType','cat_contract_types.id')
						->leftJoin('cat_regime_types','worker_data.regime_id','cat_regime_types.id')
						->leftJoin('cat_periodicities','worker_data.periodicity','cat_periodicities.c_periodicity')
						->leftJoin('payment_methods','worker_data.paymentWay','payment_methods.idpaymentMethod')
						->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
						->where(function($query) use ($name,$curp,$enterprise,$status,$project)
						{
							if ($name != "") 
							{
								$query->where(\DB::raw("CONCAT_WS(' ',real_employees.name,real_employees.last_name,real_employees.scnd_last_name)"),'LIKE','%'.$name.'%');
							}
							if ($curp != "") 
							{
								$query->where('real_employees.curp','LIKE','%'.$curp.'%');
							}
							if ($enterprise != "" || $status != "" || $project != "")
							{
								// $query->whereHas('workerData',function($q) use ($enterprise,$status,$project)
								// {
									// $q->where('visible',1);
									if($enterprise != "")
									{
										$query->where('worker_data.enterprise', $enterprise);
									}
									if($project != "")
									{
										$query->where('worker_data.project', $project);
									}
									if($status != "")
									{
										$query->where('worker_data.workerStatus', $status);
									}
								// });
							}
						})
						->get();

			if(count($employees)==0 || is_null($employees))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol     = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-empleados.xlsx');
			$writer->getCurrentSheet()->setName('Empleados registrados');

			$subHeader = 
			[
				'Nombre',
				'Apellido paterno',
				'Apellido materno',
				'CURP',
				'RFC',
				'IMSS',
				'Calle',
				'Número',
				'Colonia',
				'CP',
				'Ciudad',
				'Estado donde radica',
				'Estado donde labora',
				'Projecto',
				'WBS',
				'Subdepartamento',
				'Empresa',
				'Clasificación del gasto',
				'Lugar de trabajo',
				'Dirección',
				'Departamento',
				'Puesto',
				'Jefe inmediato',
				'Fecha de ingreso',
				'Fecha de alta',
				'Fecha de baja',
				'Fecha de término',
				'Fecha de reingreso',
				'Tipo de contrato',
				'Régimen',
				'Estado',
				'SDI',
				'Periodicidad',
				'Registro patronal',
				'Forma de pago',
				'Sueldo neto',
				'Viáticos',
				'campamento',
				'Complemento',
				'Fonacot',
				'Porcentaje de nomina',
				'Porcentaje de bono',
				'Crédito infonavit',
				'Descuento de infonavit',
				'Tipo de descuento infonavit',
				'Alias',
				'Banco',
				'CLABE',
				'Cuenta',
				'Tarjeta',
				'Sucursal',
				'Acta de Nacimiento',
				'Comprobante de Domicilio',
				'Número de Seguro Social',
				'INE',
				'CURP',
				'RFC',
				'Curriculum Vitae',
				'Comprobante de Estudios',
				'Cédula Profesional'
			];
			
			$headers = array_fill(0, count($subHeader), '');
			$headers[0] = "Empleados registrados";
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$rowDark);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if($k >= 12)
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
				}
				if($k >= 45)
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('33A8F2')->setFontColor(Color::WHITE)->build();
				}
				if($k >= 51)
				{
					$mhStyleCol    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
				}
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($employees as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['sdi', 'netIncome', 'complement', 'fonacot', 'infonavitDiscount']))
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
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[162,163])->count()>0)
		{
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$headerStyle  = (new StyleBuilder())->setFontName('Calibri')
												->setFontSize(16)
												->setFontBold()
												->setFontColor(Color::WHITE)
												->setCellAlignment(CellAlignment::LEFT)
           										->setBackgroundColor('1a6206')
												->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$rowWhite     = (new StyleBuilder())->setBackgroundColor(Color::WHITE)->setCellAlignment(CellAlignment::LEFT)->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('catalogos-empleados.xlsx');
			for ($i=0; $i <= 16; $i++)
			{
				switch ($i)
				{
					case 0:
						$title = 'Estados';
						$values = State::select('idstate','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Estado",$headerStyle)
						];
						break;
					case 1:
						$title = 'Proyectos';
						$values = Project::selectRaw('idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')->where('status',1)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Proyecto",$headerStyle)
						];
						break;
					case 2:
						$title = 'WBS';
						$values = CatCodeWBS::selectRaw('id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')->join('projects','cat_code_w_bs.project_id','projects.idproyect')->where('cat_code_w_bs.status',1)->where('projects.status',1)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("WBS",$headerStyle),
							WriterEntityFactory::createCell("Proyecto",$headerStyle)
						];
						break;
					case 3:
						$title = 'Empresas';
						$values = Enterprise::select('id','name')->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Empresa",$headerStyle)
						];
						break;
					case 4:
						$title = 'Direcciones';
						$values = Area::select('id','name')->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Dirección",$headerStyle)
						];
						break;
					case 5:
						$title = 'Clasificación del gasto';
						$values = Account::selectRaw('idAccAcc, CONCAT(account," ",description," (",content,")"), enterprises.name')->where('selectable',1)->join('enterprises','accounts.idEnterprise','enterprises.id')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Clasificación",$headerStyle),
							WriterEntityFactory::createCell("Empresa",$headerStyle)
						];
						break;
					case 6:
						$title = 'Departamentos';
						$values = Department::select('id','name')->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Departamento",$headerStyle)
						];
						break;
					case 7:
						$title = 'Subdepartamentos';
						$values = Subdepartment::select('id','name')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Subdepartamento",$headerStyle)
						];
						break;
					case 8:
						$title = 'Lugares de trabajo';
						$values = Place::select('id','place')->where('status',1)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Lugar de trabajo",$headerStyle)
						];
						break;
					case 9:
						$title = 'Tipos de contrato';
						$values = CatContractType::select('id','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Tipo de contrato",$headerStyle)
						];
						break;
					case 10:
						$title = 'Regimenes';
						$values = CatRegimeType::select('id','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Régimen",$headerStyle)
						];
						break;
					case 11:
						$title = 'Estado de empleado';
						$values = collect([
							collect([1,"Activo"]),
							collect([2,"Baja pacial"]),
							collect([3,"Baja definitiva"]),
							collect([4,"Suspensión"]),
							collect([5,"Boletinado"]),
						]);
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Estado de empleado",$headerStyle)
						];
						break;
					case 12:
						$title = 'Periodicidades';
						$values = CatPeriodicity::select('c_periodicity','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Periodicidad",$headerStyle)
						];
						break;
					case 13:
						$title = 'Formas de pago';
						$values = PaymentMethod::select('idpaymentMethod','method')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Forma de pago",$headerStyle)
						];
						break;
					case 14:
						$title = 'Bancos';
						$values = CatBank::select('c_bank','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Banco",$headerStyle)
						];
						break;
					case 15:
						$title = 'Código postal';
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Código postal",$headerStyle)
						];
						break;
					case 16:
						$title = 'Régimen fiscal';
						$values = CatTaxRegime::select('taxRegime','description')->where('physical','Sí')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Régimen fiscal",$headerStyle)
						];
						break;
				}
				if($i > 0)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				$sheet = $writer->getCurrentSheet();
				$sheet->setName($title);
				$rowFromValues = WriterEntityFactory::createRow($headers);
				$writer->addRow($rowFromValues);
				if($i == 15)
				{
					$divisor = 10000;
					$top = ceil(CatZipCode::select('zip_code','states.description')->join('states','state','c_state')->orderBy('state')->count() / $divisor);
					for ($j = 0; $j < $top; $j++)
					{
						$offsetTmp = $j * $divisor;
						$limitTmp  = $divisor - 1;
						$values    = CatZipCode::select('zip_code','states.description')->join('states','state','c_state')->orderBy('state')->offset($offsetTmp)->limit($limitTmp)->get();
						$kindRow   = true;
						foreach($values as $keyValue => $valTmp)
						{
							$tmpArr = [];
							foreach ($valTmp->toArray() as $k => $v)
							{
								$tmpArr[] = WriterEntityFactory::createCell($v);
							}
							if($kindRow)
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
							}
							else
							{
								$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowWhite);
							}
							$writer->addRow($rowFromValues);
							unset($values[$keyValue]);
							$kindRow = !$kindRow;
						}
					}
					unset($values);
				}
				else
				{
					$kindRow = true;
					foreach($values as $keyValue => $valTmp)
					{
						$tmpArr = [];
						foreach ($valTmp->toArray() as $k => $v)
						{
							$tmpArr[] = WriterEntityFactory::createCell($v);
						}
						if($kindRow)
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
						}
						else
						{
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowWhite);
						}
						$writer->addRow($rowFromValues);
						unset($values[$keyValue]);
						$kindRow = !$kindRow;
					}
					unset($values);
				}
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportMovement(Request $request)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$name		= $request->name;
			$curp		= $request->email;
			$status		= $request->status;
			$enterprise = $request->enterprise;
			$employees  = DB::table('real_employees')->selectRaw(
						'
							CONCAT_WS(" ", real_employees.name, real_employees.last_name, real_employees.scnd_last_name),
							CONCAT(worker_data.employer_register,real_employees.imss,SUBSTRING(real_employees.curp, 1, 10)),
							CONCAT(UPPER(real_employees.curp), REPLACE(UPPER(CONCAT_WS("$", real_employees.last_name, real_employees.scnd_last_name, real_employees.name)), "Ñ", "/")),
							"00000000" as data
						')
						->leftJoin(DB::raw('(select * from worker_datas WHERE visible = 1) as worker_data'),'real_employees.id','worker_data.idEmployee')
						->where(function($query) use ($name,$curp,$enterprise,$status)
						{
							if ($name != "") 
							{
								$query->where(DB::raw("CONCAT_WS(' ',real_employees.name,real_employees.last_name,real_employees.scnd_last_name)"),'LIKE','%'.$name.'%');
							}
							if ($curp != "") 
							{
								$query->where('real_employees.curp','LIKE','%'.$curp.'%');
							}
							if ($enterprise != "" || $status != "")  
							{
								if($enterprise != "")
								{
									$query->where('worker_data.enterprise', $enterprise);
								}
								if($status != "")
								{
									$query->where('worker_data.workerStatus', $status);
								}
							}
						})
						->get();
			if(count($employees)==0 || is_null($employees))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos IMSS.xlsx');
			$writer->getCurrentSheet()->setName('Empleados');

			foreach($employees as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportLayout(Request $request)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{			
			$employees   = RealEmployee::selectRaw(
				'
					real_employees.name,
					real_employees.last_name,
					real_employees.scnd_last_name,
					real_employees.curp,
					real_employees.rfc,
					real_employees.tax_regime,
					real_employees.imss,
					real_employees.street,
					real_employees.number,
					real_employees.colony,
					real_employees.cp,
					real_employees.city,
					real_employees.state_id,
					worker_data.state,
					worker_data.project,
					worker_data.wbs_id,
					worker_data.enterprise,
					worker_data.account as accounting_account,
					places.places,
					worker_data.direction,
					worker_data.department,
					worker_data.position,
					worker_data.immediate_boss,
					worker_data.admissionDate,
					worker_data.imssDate,
					worker_data.downDate,
					worker_data.endingDate,
					worker_data.reentryDate,
					worker_data.workerType,
					worker_data.regime_id,
					worker_data.workerStatus,
					worker_data.sdi,
					worker_data.periodicity,
					worker_data.employer_register,
					worker_data.paymentWay,
					worker_data.netIncome,
					worker_data.viatics,
					worker_data.camping,
					worker_data.complement,
					worker_data.fonacot,
					worker_data.nomina,
					worker_data.bono,
					worker_data.infonavitCredit,
					worker_data.infonavitDiscount,
					worker_data.infonavitDiscountType,
					bank_data.alias,
					bank_data.idCatBank,
					CONCAT(bank_data.clabe," ") as clabe,
					CONCAT(bank_data.account," ") as account,
					CONCAT(bank_data.cardNumber," ") as cardNumber,
					bank_data.branch
				')
				->leftJoin(DB::raw('(select * from worker_datas WHERE visible = 1) as worker_data'),'real_employees.id','worker_data.idEmployee')
				->leftJoin(DB::raw('(SELECT * FROM employee_accounts WHERE id IN(SELECT MIN(id) as id FROM employee_accounts WHERE visible = 1 GROUP BY idEmployee)) as bank_data'),'real_employees.id','bank_data.idEmployee')
				->leftJoin(DB::raw('(SELECT idWorkingData, GROUP_CONCAT(idPlace SEPARATOR ", ") as places FROM worker_data_places GROUP BY idWorkingData) as places'),'worker_data.id','places.idWorkingData')
				->get();

			Excel::create('Reporte-Empleados-Plantilla', function($excel) use ($employees)
			{
				$excel->sheet('Empleados Registrados',function($sheet) use ($employees)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->setColumnFormat(array(
						'AA' => '@',
						'AB' => '@',
						'AE' => '@',
						'AQ' => '@',
						'AR' => '@',
						'AS' => '@',
						'AT' => '@',
						'AU' => '@',
					));
					$sheet->cell('A1:B1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('D1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('H1:M1', function($cells)
					{
						$cells->setBackground('#1a6206');
					});
					$sheet->cell('C1', function($cells)
					{
						$cells->setBackground('#34b511');
					});
					$sheet->cell('E1:G1', function($cells)
					{
						$cells->setBackground('#34b511');
					});
					$sheet->cell('N1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('Q1:R1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('T1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('V1:X1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AC1:AK1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('AM1:AN1', function($cells)
					{
						$cells->setBackground('#771414');
					});
					$sheet->cell('O1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('P1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('S1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('U1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('W1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('Y1:AB1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AL1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AO1:AQ1', function($cells)
					{
						$cells->setBackground('#db5151');
					});
					$sheet->cell('AR1:AW1', function($cells)
					{
						$cells->setBackground('#21bbbb');
					});
					$sheet->cell('A1:AW1', function($cells)
					{
						$cells->setFontColor('#ffffff');
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
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
								'estado_laboral',
								'proyecto',
								'wbs',
								'empresa',
								'clasificacion_gasto',
								'lugar_trabajo',
								'direccion',
								'departamento',
								'puesto',
								'jefe_inmediato',
								'fecha_ingreso',
								'fecha_alta',
								'fecha_baja',
								'fecha_termino',
								'fecha_reingreso',
								'tipo_contrato',
								'regimen',
								'estatus',
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
								'sucursal'
							]);
					foreach ($employees as $employee) 
					{
						$sheet->appendRow($employee->toArray());
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function historic(RealEmployee $employee)
	{
		if(Auth::user()->module->where('id',163)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$workerData	= WorkerData::where('idEmployee',$employee->id)->orderBy('created_at','DESC')->paginate(10);
			return view('configuracion.empleado.historial',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 163,
					'employee'		=> $employee,
					'workerData'	=> $workerData,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function documents(RealEmployee $employee)
	{
		if(Auth::user()->id == 43)
		{
			$data = Module::find($this->module_id);
			return view('configuracion.empleado.documents',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 'x',
					'employee'  => $employee
				]);
		}
		else
		{
			return abort(404);
		}
	}
	public function updateDocs(RealEmployee $employee, Request $request)
	{
		if(Auth::user()->id == 43)
		{
			$employee->doc_birth_certificate    = $request->doc_birth_certificate;
			$employee->doc_proof_of_address     = $request->doc_proof_of_address;
			$employee->doc_nss                  = $request->doc_nss;
			$employee->doc_ine                  = $request->doc_ine;
			$employee->doc_curp                 = $request->doc_curp;
			$employee->doc_rfc                  = $request->doc_rfc;
			$employee->doc_cv                   = $request->doc_cv;
			$employee->doc_proof_of_studies     = $request->doc_proof_of_studies;
			$employee->doc_professional_license = $request->doc_professional_license;
			$employee->doc_requisition          = $request->doc_requisition;
			$employee->save();
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
			$alert = "swal('', '".Lang::get("messages.files_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	/*public function reactive($id)
	{
		$employee				= RealEmployee::find($id);
		$oldWorker				= $employee->workerDataVisible->first();
		$newData				= $oldWorker->replicate();
		$oldWorker->visible		= 0;
		$oldWorker->save();
		
		$newData->visible 		= 1;
		$newData->workerStatus 	= 1;
		$newData->status_reason = null;
		$newData->admissionDate	= null;
		$newData->imssDate		= null;
		$newData->downDate 		= null;
		$newData->save();

		$reactive = 'true';


		$alert 		= "swal('','Empleado reactivado existosamente','success');";
		$data		= Module::find($this->module_id);
		return view('configuracion.empleado.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 161,
					'alert' 		=> $alert,
					'reactive' 		=> $reactive,
					'employee'		=> $employee,
				]);
		return redirect('configuration/employee/'.$id.'/edit')->with(['alert'=>$alert,'reactive'=>$reactive]);
	}*/
}
