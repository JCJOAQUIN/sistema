<?php

namespace App\Http\Controllers;
use App\AppClass\Excel\ExportExcel;
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
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
use PDF;
use App\Functions\Files;
use Excel;
use App\AppClass\Excel\ExcelExportClass;
use App\AppClass\Excel\SheetExcel;
use App\RequestModel;
use App\Requisition;
use DateTime;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class AdministracionRequisicionController extends Controller
{

	private $module_id = 228;

	public function index()
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function create(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.requisicion.alta_material',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 229
				]);
		}
		else
		{
			return redirect('/error');
		}
	}
	
	public function createNew(Request $request,$id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data            = App\Module::find($this->module_id);
			$request         = RequestModel::find($id);
			$requisition     = Requisition::where('id',$request->requisition->id)->first();
			$request->status = 2;
			$routeParams     = 
			[
				'id'              => $data['father'],
				'title'           => $data['name'],
				'details'         => $data['details'],
				'child_id'        => $this->module_id,
				'option_id'       => 229,
				'request'         => $request,
				'new_requisition' => true,
			];
			switch ($requisition->requisition_type)
			{
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
					return view('administracion.requisicion.alta_material',$routeParams);
					break;
				default:
					$alert	= "swal('', 'Hay un error en la requisición.', 'error');";
					return back()->with('alert',$alert);
					break;
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function material(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.requisicion.alta_material',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 229
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function service(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.requisicion.alta_servicio',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 229
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function nomina(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.requisicion.alta_nomina',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 229
				]);
		}
		else
		{
			return redirect('/error');
		}
	}


	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',229)->count() > 0) 
		{
			$generatedRequisitionNumber = null;
			if($request->project_id == 126 && $request->code_wbs != '')
			{
				$wbs_code = $request->code_wbs;
				
				$wbsModel = App\CatCodeWBS::find($request->code_wbs);
				$requisitionRequest = App\RequestModel::whereNotIn('status',[1,2])
				->whereHas('requisition', function($q) use($wbs_code)
				{
					$q->where('code_wbs',$wbs_code);
				})
				->count();
				$edtPart = null;
				if($request->code_edt != '')
				{
					$edtModel = App\CatCodeEDT::find($request->code_edt);
					$edtPart  = '-'.$edtModel->edt_number.'-'.$edtModel->phase;
				}
				$generatedRequisitionNumber = 'PIM-R2B-P6-'.$wbsModel->code.$edtPart.'-RQ-'.str_pad($requisitionRequest + 1, 4, '0',STR_PAD_LEFT);
			}
			switch ($request->requisition_type) 
			{
				case 1:
				case 5:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 3;
					$t_request->kind        = 19;
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();

					$number	= $count + 1;

					$requisition                   	= new App\Requisition();
					$requisition->title            	= $request->title;
					$requisition->date_request     	= Carbon::now();
					$requisition->number           	= $number;
					$requisition->date_comparation 	= $request->date_comparation;
					$requisition->date_obra        	= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio          	= $t_request->folio;
					$requisition->idKind           	= $t_request->kind;
					$requisition->urgent           	= $request->urgent;
					$requisition->code_wbs         	= $request->code_wbs;
					$requisition->code_edt         	= $request->code_edt;
					$requisition->requisition_type 	= $request->requisition_type;
					$requisition->subcontract_number= $request->subcontract_number;
					$requisition->buy_rent        	= $request->buy_rent;
					$requisition->validity         	= $request->validity;
					$requisition->generated_number 	= $generatedRequisitionNumber;
					$requisition->save();

					$idRequisition = $requisition->id;
					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$c_r = App\CatRequisitionName::where('name',$request->name[$i])->first();
							if(!$c_r)
							{
								$c_r = App\CatRequisitionName::create(['name' => $request->name[$i]]);
							} 

							$name_measurement = App\CatMeasurementUnit::where('description',$request->measurement[$i])->first();
							if(!$name_measurement)
							{
								$name_measurement = App\CatMeasurementUnit::create(['description' => $request->measurement[$i]]);
							} 

							$detail									= new App\RequisitionDetail();
							$detail->category						= $request->category[$i];
							$detail->cat_procurement_material_id	= $request->type[$i];
							$detail->part							= ($i + 1);
							$detail->quantity						= $request->quantity[$i];
							$detail->unit							= $request->unit[$i];
							$detail->name							= $c_r->name;
							$detail->measurement					= $name_measurement->description;
							$detail->description					= $request->description[$i];
							if ($request->requisition_type == 5) 
							{
								$detail->brand		= $request->brand[$i];
								$detail->model		= $request->model[$i];
								$detail->usage_time	= $request->usage_time[$i];
							}
							$detail->exists_warehouse				= $request->exists_warehouse[$i];
							$detail->idRequisition					= $idRequisition;
							$detail->save();
						}
					}

					$count = 1;
					foreach($t_request->requisition->details as $detail)
					{
						$detail->part	= $count;
						$detail->save();
						$count++;
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					break;

				case 3:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 3;
					$t_request->kind        = 19;
					if(isset($request->folio_requisition_rejected) && $request->folio_requisition_rejected != "")
					{
						$requisitionRejected = App\RequestModel::find($request->folio_requisition_rejected);
						if($requisitionRejected->disable_rejected == 0)
						{
							if($requisitionRejected->status == 6 || $requisitionRejected->status == 7)
							{
								$requisitionRejected->disable_rejected = 1;
								$requisitionRejected->save();
							}
						}
						else
						{
							$alert = "swal('', 'Ya ha sido creada una solicitud nueva a partir de la requisición seleccionada.', 'error');";
							return redirect()->route('requisition.search')->with('alert',$alert);
						}
					}
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();

					$number	= $count + 1;

					$requisition                   = new App\Requisition();
					$requisition->title            = $request->title;
					$requisition->date_request     = Carbon::now();
					$requisition->number           = $number;
					$requisition->date_comparation = $request->date_comparation;
					$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio          = $t_request->folio;
					$requisition->idKind           = $t_request->kind;
					$requisition->urgent           = $request->urgent;
					$requisition->code_wbs         = $request->code_wbs;
					$requisition->code_edt         = $request->code_edt;
					$requisition->requisition_type = $request->requisition_type;
					$requisition->generated_number = $generatedRequisitionNumber;
					$requisition->save();
					$idRequisition = $requisition->id;

					/*
						$requisition_staff							= new App\RequisitionStaff();
						$requisition_staff->boss_id					= $request->boss_id;
						$requisition_staff->staff_reason			= $request->staff_reason;
						$requisition_staff->staff_position			= $request->staff_position;
						$requisition_staff->staff_periodicity		= $request->staff_periodicity;
						$requisition_staff->staff_schedule_start	= $request->staff_schedule_start;
						$requisition_staff->staff_schedule_end		= $request->staff_schedule_end;
						$requisition_staff->staff_min_salary		= $request->staff_min_salary;
						$requisition_staff->staff_max_salary		= $request->staff_max_salary;
						$requisition_staff->staff_s_description		= $request->staff_s_description;
						$requisition_staff->staff_habilities		= $request->staff_habilities;
						$requisition_staff->staff_experience		= $request->staff_experience;
						$requisition_staff->requisition_id 			= $idRequisition;
						$requisition_staff->save();

						if (isset($request->staff_responsibilities) && count($request->staff_responsibilities)>0) 
						{
							for ($i=0; $i < count($request->staff_responsibilities); $i++) 
							{
								$requisition_staff_responsabilities	= new App\RequisitionStaffResponsibilities();
								$requisition_staff_responsabilities->staff_responsibilities	= $request->staff_responsibilities[$i];
								$requisition_staff_responsabilities->requisition_id 		= $idRequisition;
								$requisition_staff_responsabilities->save();
							}
						}

						if (isset($request->tdesirable) && count($request->tdesirable)>0) 
						{
							for ($i=0; $i < count($request->tdesirable); $i++) 
							{
								$requisition_staff_desirables					= new App\RequisitionStaffDesirables();
								$requisition_staff_desirables->desirable		= $request->tdesirable[$i];
								$requisition_staff_desirables->description		= $request->td_descr[$i];
								$requisition_staff_desirables->requisition_id	= $idRequisition;
								$requisition_staff_desirables->save();
							}
						}

						if (isset($request->tfunction) && count($request->tfunction)>0) 
						{
							for ($i=0; $i < count($request->tfunction); $i++) 
							{
								$requisition_staff_function					= new App\RequisitionStaffFunctions();
								$requisition_staff_function->function		= $request->tfunction[$i];
								$requisition_staff_function->description	= $request->tdescr[$i];
								$requisition_staff_function->requisition_id	= $idRequisition;
								$requisition_staff_function->save();
							}
						}
					*/
					for ($i=0; $i < count($request->rq_name); $i++) 
					{ 
						$employee							= new App\RequisitionEmployee();
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
						$employee->enterprise				= $request->rq_work_enterprise[$i];
						$employee->account					= $request->rq_work_account[$i];
						$employee->direction				= $request->rq_work_direction[$i];
						$employee->department				= $request->rq_work_department[$i];
						$employee->position					= $request->rq_work_position[$i];
						$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
						$employee->admissionDate			= $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_income_date[$i])->format('Y-m-d') : null;
						$employee->imssDate					= $request->rq_work_imss_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_imss_date[$i])->format('Y-m-d') : null;
						$employee->downDate					= $request->rq_work_down_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_down_date[$i])->format('Y-m-d') : null;
						$employee->endingDate				= $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_ending_date[$i])->format('Y-m-d') : null;
						$employee->reentryDate				= $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
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
						$employee->doc_requisition			= $request->rq_doc_requisition[$i];
						$employee->computer_required		= $request->rq_computer_required[$i];
						$employee->wbs_id 					= $request->code_wbs;

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
						$employee->requisition_id 		= $idRequisition;
						$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
						$employee->save();

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
								$empAcc              = new App\RequisitionEmployeeAccount();
								$empAcc->idEmployee  = $employee->id;
								$empAcc->beneficiary = $request->$beneficiary[$k];
								$empAcc->type        = $request->$type[$k];
								$empAcc->alias       = $request->$alias[$k];
								$empAcc->clabe       = $request->$clabe[$k];
								$empAcc->account     = $request->$account[$k];
								$empAcc->cardNumber  = $request->$cardNumber[$k];
								$empAcc->idCatBank   = $request->$idCatBank[$k];
								$empAcc->branch      = $request->$branch[$k];
								$empAcc->recorder    = Auth::user()->id;
								$empAcc->save();
							}
						}

						App\RequisitionEmployeeDocument::where('requisition_employee_id',$employee->id)->delete();
						$name_other_document = 'name_other_document_'.$i;
						$path_other_document = 'path_other_document_'.$i;
						if (isset($request->$name_other_document) && count($request->$name_other_document)>0) 
						{
							for ($d=0; $d < count($request->$name_other_document); $d++) 
							{ 
								$checkDoc 	= App\RequisitionEmployeeDocument::where('name',$request->$name_other_document[$d])
											->where('path',$request->$path_other_document[$d])
											->where('requisition_employee_id',$employee->id)
											->count();
								if ($checkDoc == 0) 
								{
									$other							= new App\RequisitionEmployeeDocument();
									$other->name					= $request->$name_other_document[$d];
									$other->path					= $request->$path_other_document[$d];
									$other->requisition_employee_id	= $employee->id;
									$other->save();
								}
							}
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}

				break;

				case 2:
				case 4:
				case 6:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 3;
					$t_request->kind        = 19;
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();
					$number	= $count + 1;

					$requisition                   = new App\Requisition();
					$requisition->title            = $request->title;
					$requisition->date_request     = Carbon::now();
					$requisition->number           = $number;
					$requisition->date_comparation = $request->date_comparation;
					$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio          = $t_request->folio;
					$requisition->idKind           = $t_request->kind;
					$requisition->urgent           = $request->urgent;
					$requisition->code_wbs         = $request->code_wbs;
					$requisition->code_edt         = $request->code_edt;
					$requisition->requisition_type = $request->requisition_type;
					$requisition->subcontract_number = $request->subcontract_number;
					$requisition->buy_rent         = $request->buy_rent;
					$requisition->validity         = $request->validity;
					$requisition->generated_number = $generatedRequisitionNumber;
					$requisition->save();
					$idRequisition = $requisition->id;

					if (isset($request->quantity) && count($request->quantity)>0)
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$detail                = new App\RequisitionDetail();
							$detail->part          = ($i + 1);
							$detail->unit          = $request->unit[$i];
							$detail->name          = $request->name[$i];
							$detail->quantity      = $request->quantity[$i];
							$detail->description   = $request->description[$i];
							if($request->requisition_type == 2)
							{
								$detail->period		= $request->period[$i];
								$detail->category	= $request->category[$i];
							}
							$detail->idRequisition = $idRequisition;
							$detail->save();
						}
					}

					$count = 1;
					foreach($t_request->requisition->details as $detail)
					{
						$detail->part	= $count;
						$detail->save();
						$count++;
					}
					
					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					break;
				
				default:
					# code...
					break;
			}
			if($generatedRequisitionNumber != '')
			{
				$alert = "swal('', 'Requisición enviada exitosamente con el número:\\n ".$generatedRequisitionNumber."', 'success');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			}
			return redirect()->route('requisition.search')->with('alert',$alert);
		}
	}

	public function save(Request $request)
	{
		if (Auth::user()->module->where('id',229)->count() > 0) 
		{
			switch ($request->requisition_type) 
			{
				case 1:
				case 5:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 2;
					$t_request->kind        = 19;
					$t_request->save();

					$requisition					= new App\Requisition();
					$requisition->title				= $request->title;
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio 			= $t_request->folio;
					$requisition->idKind 			= $t_request->kind;
					$requisition->urgent 			= $request->urgent;
					$requisition->code_wbs 			= $request->code_wbs;
					$requisition->code_edt 			= $request->code_edt;
					$requisition->requisition_type 	= $request->requisition_type;
					$requisition->buy_rent			= $request->buy_rent;
					$requisition->validity			= $request->validity;
					$requisition->save();
					$idRequisition = $requisition->id;

					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{

							$c_r = App\CatRequisitionName::where('name',$request->name[$i])->first();
							if(!$c_r)
							{
								$c_r = App\CatRequisitionName::create(['name' => $request->name[$i]]);
							} 
							$name_measurement = App\CatMeasurementUnit::where('description',$request->measurement[$i])->first();
							if(!$name_measurement)
							{
								$name_measurement = App\CatMeasurementUnit::create(['description' => $request->measurement[$i]]);
							} 
							$detail									= new App\RequisitionDetail();
							$detail->category						= $request->category[$i];
							$detail->cat_procurement_material_id	= $request->type[$i];
							$detail->part                           = ($i + 1);
							$detail->quantity						= $request->quantity[$i];
							$detail->unit							= $request->unit[$i];
							$detail->name							= $c_r->name;
							$detail->measurement					= $name_measurement->description;
							$detail->description					= $request->description[$i];
							if ($request->requisition_type == 5) 
							{
								$detail->brand		= $request->brand[$i];
								$detail->model		= $request->model[$i];
								$detail->usage_time	= $request->usage_time[$i];
							}
							$detail->exists_warehouse				= $request->exists_warehouse[$i];
							$detail->idRequisition					= $idRequisition;
							$detail->save();
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}

					if ($t_request->requisition->details()->exists()) 
					{
						$countDetail = $t_request->requisition->details->count();
					}
					else
					{
						$countDetail = 0;
					}

					$errors = 0;
					if ($request->requisition_type == 1) 
					{
						if(isset($request) && $request->csv_file_material != "" && $request->file('csv_file_material')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_material')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_material')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							
							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
								{
									$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
									if(!$c_r)
									{
										$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
									} 
									
									if (isset($art['tipo']) && trim($art['tipo']) != "") 
									{
										$check_type = App\CatProcurementMaterial::where('name','LIKE','%'.$art['tipo'].'%')->first();
										if ($check_type != "") 
										{
											$type_id = $check_type->id;
										}
										else
										{
											$type_id = null;
										}
									}
									else
									{
										$type_id = null;
									}

									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
									if ($check_category != "") 
									{
										$category_id = $check_category->id;
										$detail									= new App\RequisitionDetail();
										$detail->category						= $category_id;
										$detail->cat_procurement_material_id	= $type_id;
										$detail->part							= ($countDetail+$countRows+1);
										$detail->quantity						= $art['cantidad'];
										$detail->unit							= $art['unidad'];
										$detail->name							= $c_r->name;
										$detail->measurement					= $art['medida'];
										$detail->description					= $art['descripcion'];
										$detail->exists_warehouse				= $art['existencia_almacen'];
										$detail->idRequisition					= $idRequisition;
										$detail->save();

										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
							}
						}
					}
					elseif ($request->requisition_type == 5) 
					{
						if(isset($request) && $request->csv_file_machine != "" && $request->file('csv_file_machine')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_machine')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_machine')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['marca']) && trim($art['marca'])!="") && 
									(isset($art['modelo']) && trim($art['modelo'])!="") && 
									(isset($art['tiempo_utilizacion']) && trim($art['tiempo_utilizacion'])!="") && 
									(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
								{
									$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
									if(!$c_r)
									{
										$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
									} 
									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
									if ($check_category != "") 
									{
										$detail						= new App\RequisitionDetail();
										$detail->category			= $check_category->id;
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $c_r->name;
										$detail->measurement		= $art['medida'];
										$detail->description		= $art['descripcion'];
										$detail->brand				= $art['marca'];
										$detail->model				= $art['modelo'];
										$detail->usage_time			= $art['tiempo_utilizacion'];
										$detail->exists_warehouse	= $art['existencia_almacen'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();
										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
								
							}
						}
					}
					break;

				case 3:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 2;
					$t_request->kind        = 19;
					if(isset($request->folio_requisition_rejected) && $request->folio_requisition_rejected != "")
					{
						$requisitionRejected = App\RequestModel::find($request->folio_requisition_rejected);
						if($requisitionRejected->disable_rejected == 0)
						{
							if($requisitionRejected->status == 6 || $requisitionRejected->status == 7)
							{
								$requisitionRejected->disable_rejected = 1;
								$requisitionRejected->save();
							}
						}
						else
						{
							$alert = "swal('', 'Ya ha sido creada una solicitud nueva a partir de la requisición seleccionada.', 'error');";
							return redirect()->route('requisition.search')->with('alert',$alert);
						}
					}
					$t_request->save();

					$requisition                    = new App\Requisition();
					$requisition->title				= $request->title;
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio 			= $t_request->folio;
					$requisition->idKind 			= $t_request->kind;
					$requisition->urgent 			= $request->urgent;
					$requisition->code_wbs 			= $request->code_wbs;
					$requisition->code_edt 			= $request->code_edt;
					$requisition->requisition_type 	= $request->requisition_type;
					$requisition->save();
					$idRequisition = $requisition->id;

					/*
						$requisition_staff							= new App\RequisitionStaff();
						$requisition_staff->boss_id					= $request->boss_id;
						$requisition_staff->staff_reason			= $request->staff_reason;
						$requisition_staff->staff_position			= $request->staff_position;
						$requisition_staff->staff_periodicity		= $request->staff_periodicity;
						$requisition_staff->staff_schedule_start	= $request->staff_schedule_start;
						$requisition_staff->staff_schedule_end		= $request->staff_schedule_end;
						$requisition_staff->staff_min_salary		= $request->staff_min_salary;
						$requisition_staff->staff_max_salary		= $request->staff_max_salary;
						$requisition_staff->staff_s_description		= $request->staff_s_description;
						$requisition_staff->staff_habilities		= $request->staff_habilities;
						$requisition_staff->staff_experience		= $request->staff_experience;
						$requisition_staff->requisition_id 			= $idRequisition;
						$requisition_staff->save();

						if (isset($request->staff_responsibilities) && count($request->staff_responsibilities)>0) 
						{
							for ($i=0; $i < count($request->staff_responsibilities); $i++) 
							{ 
								$requisition_staff_responsabilities	= new App\RequisitionStaffResponsibilities();
								$requisition_staff_responsabilities->staff_responsibilities	= $request->staff_responsibilities[$i];
								$requisition_staff_responsabilities->requisition_id 		= $idRequisition;
								$requisition_staff_responsabilities->save();
							}
						}

						if (isset($request->tdesirable) && count($request->tdesirable)>0) 
						{
							for ($i=0; $i < count($request->tdesirable); $i++) 
							{
								$requisition_staff_desirables					= new App\RequisitionStaffDesirables();
								$requisition_staff_desirables->desirable		= $request->tdesirable[$i];
								$requisition_staff_desirables->description		= $request->td_descr[$i];
								$requisition_staff_desirables->requisition_id	= $idRequisition;
								$requisition_staff_desirables->save();
							}
						}

						if (isset($request->tfunction) && count($request->tfunction)>0) 
						{
							for ($i=0; $i < count($request->tfunction); $i++) 
							{
								$requisition_staff_function					= new App\RequisitionStaffFunctions();
								$requisition_staff_function->function		= $request->tfunction[$i];
								$requisition_staff_function->description	= $request->tdescr[$i];
								$requisition_staff_function->requisition_id	= $idRequisition;
								$requisition_staff_function->save();
							}
						}
					*/

					if (isset($request->rq_name) && count($request->rq_name)>0) 
					{
						// code...
						for ($i=0; $i < count($request->rq_name); $i++) 
						{ 
							$employee							= new App\RequisitionEmployee();
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
							$employee->enterprise				= $request->rq_work_enterprise[$i];
							$employee->account					= $request->rq_work_account[$i];
							$employee->direction				= $request->rq_work_direction[$i];
							$employee->department				= $request->rq_work_department[$i];
							$employee->position					= $request->rq_work_position[$i];
							$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
							$employee->admissionDate			= $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_income_date[$i])->format('Y-m-d') : null;
							$employee->imssDate					= $request->rq_work_imss_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_imss_date[$i])->format('Y-m-d') : null;
							$employee->downDate					= $request->rq_work_down_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_down_date[$i])->format('Y-m-d') : null;
							$employee->endingDate				= $request->rq_work_ending_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_ending_date[$i])->format('Y-m-d') : null;
							$employee->reentryDate				= $request->rq_work_reentry_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
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
							$employee->doc_requisition			= $request->rq_doc_requisition[$i];
							$employee->computer_required		= $request->rq_computer_required[$i];
							$employee->wbs_id 					= $request->code_wbs;

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
							$employee->requisition_id 		= $idRequisition;
							$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
							$employee->save();

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
									$empAcc              = new App\RequisitionEmployeeAccount();
									$empAcc->idEmployee  = $employee->id;
									$empAcc->beneficiary = $request->$beneficiary[$k];
									$empAcc->type        = $request->$type[$k];
									$empAcc->alias       = $request->$alias[$k];
									$empAcc->clabe       = $request->$clabe[$k];
									$empAcc->account     = $request->$account[$k];
									$empAcc->cardNumber  = $request->$cardNumber[$k];
									$empAcc->idCatBank   = $request->$idCatBank[$k];
									$empAcc->branch      = $request->$branch[$k];
									$empAcc->recorder    = Auth::user()->id;
									$empAcc->save();
								}
							}

							App\RequisitionEmployeeDocument::where('requisition_employee_id',$employee->id)->delete();
							$name_other_document = 'name_other_document_'.$i;
							$path_other_document = 'path_other_document_'.$i;
							if (isset($request->$name_other_document) && count($request->$name_other_document)>0) 
							{
								for ($d=0; $d < count($request->$name_other_document); $d++) 
								{ 
									$checkDoc 	= App\RequisitionEmployeeDocument::where('name',$request->$name_other_document[$d])
												->where('path',$request->$path_other_document[$d])
												->where('requisition_employee_id',$employee->id)
												->count();
									if ($checkDoc == 0) 
									{
										$other							= new App\RequisitionEmployeeDocument();
										$other->name					= $request->$name_other_document[$d];
										$other->path					= $request->$path_other_document[$d];
										$other->requisition_employee_id	= $employee->id;
										$other->save();
									}
								}
							}
						}
					}
					
					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					$errors = 0;

					
					if(($request->csv_file_personal != "" || !empty($request->csv_file_personal)))
					{	
						$valid = $request->file('csv_file_personal')->getClientOriginalExtension();
							
						if($valid != 'csv')
						{
							$alert	= "swal('', 'El archivo que intenta cargar no corresponde a la extensión CSV, favor de verificar su información.', 'error');";
							return back()->with('alert',$alert);	
						}

						$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_personal')->getClientOriginalExtension();
						\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_personal')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
						$path		= \Storage::disk('reserved')->path($name);
						$csvArr		= array();
						if (($handle = fopen($path, "r")) !== FALSE)
						{
							$first	= true;
							while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
							{
								if($first)
								{
									$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
									$first		= false;
								}
								$csvArr[]	= $data;
							}
							fclose($handle);
						}
						array_walk($csvArr, function(&$a) use ($csvArr)
						{
							$a = array_combine($csvArr[0], $a);
						});

						$headers = [
							'nombre',
							'apellido',
							'apellido2',
							'curp',
							'rfc',
							'email',
							'regimen_fiscal',
							'imss',
							'calle',
							'numero',
							'colonia',
							'cp',
							'ciudad',
							'estado',
							'personal_calificado',
							'en_reemplazo_de',
							'proposito_del_puesto',
							'requerimientos_del_puesto',
							'observaciones',
							'requiere_equipo_de_computo',
							'estado_laboral',
							'empresa',
							'clasificacion_gasto',
							'lugar_trabajo',
							'direccion',
							'departamento',
							'subdepartamento',
							'puesto',
							'jefe_inmediato',
							'posisicion_de_jefe_inmediato',
							'estatus_imss',
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
							'viaticos',
							'campamento',
							'sueldo_neto',
							'complemento',
							'fonacot',
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

						if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
						{
							$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'error')";
							return back()->with('alert',$alert);	
						}

						array_shift($csvArr);

						$countRows = 0;
						
						foreach ($csvArr as $e) 
						{
							if (isset($e['curp']) && trim($e['curp']) != "" ) 
							{
								$check_employee 	= App\RealEmployee::where('curp',trim($e['curp']))->count();
								$check_requisition 	= App\RequisitionEmployee::leftJoin('requisitions','requisition_employees.requisition_id','requisitions.id')
													->leftJoin('request_models','requisitions.idFolio','request_models.folio')
													->select('requisition_employees.curp')
													->whereNotIn('request_models.status',[5,6,7,23,28])
													->where('requisition_employees.curp',trim($e['curp']))
													->count();
							}
							else
							{
								$check_employee		= 1;
								$check_requisition	= 1;
							}

							if ((isset($e['nombre']) && trim($e['nombre']) != "")
								&& (isset($e['apellido']) && trim($e['apellido']) != "")
								&& (isset($e['curp']) && trim($e['curp']) != "" && $check_employee == 0 && $check_requisition == 0)
								&& (isset($e['calle']) && trim($e['calle']) != "")
								&& (isset($e['numero']) && trim($e['numero']) != "")
								&& (isset($e['colonia']) && trim($e['colonia']) != "")
								&& (isset($e['cp']) && trim($e['cp']) != "" && App\CatZipCode::where('zip_code',trim($e['cp']))->count()>0)
								&& (isset($e['ciudad']) && trim($e['ciudad']) != "")
								&& (isset($e['estado']) && trim($e['estado']) != "" && App\State::where('idstate',trim($e['estado']))->count()>0)
								&& (isset($e['estado_laboral']) && trim($e['estado_laboral']) != "" && App\State::where('idstate',trim($e['estado_laboral']))->count()>0)
								&& (isset($e['empresa']) && trim($e['empresa']) != "" && App\Enterprise::where('id',trim($e['empresa']))->count()>0)
								&& (isset($e['clasificacion_gasto']) && trim($e['clasificacion_gasto']) != "" && App\Account::where('idAccAcc',trim($e['clasificacion_gasto']))->count()>0)
								&& (isset($e['direccion']) && trim($e['direccion']) != "" && App\Area::where('id',trim($e['direccion']))->count()>0)
								&& (isset($e['puesto']) && trim($e['puesto']) != "")
								&& (isset($e['fecha_ingreso']) && trim($e['fecha_ingreso']) != "" && DateTime::createFromFormat('Y-m-d', trim($e['fecha_ingreso'])) !== false)
								&& (isset($e['tipo_contrato']) && trim($e['tipo_contrato']) != "" && App\CatContractType::where('id',trim($e['tipo_contrato']))->count()>0)
								&& (isset($e['regimen']) && trim($e['regimen']) != "" && App\CatRegimeType::where('id',trim($e['regimen']))->count()>0)
								&& (isset($e['periodicidad']) && trim($e['periodicidad']) != "" && App\CatPeriodicity::where('c_periodicity',trim($e['periodicidad']))->count()>0)
								&& (isset($e['registro_patronal']) && trim($e['registro_patronal']) != "" && App\EmployerRegister::where('employer_register',trim($e['registro_patronal']))->count()>0)
								&& (isset($e['forma_pago']) && trim($e['forma_pago']) != "" && App\PaymentMethod::where('idpaymentMethod',trim($e['forma_pago']))->count()>0)
								&& (isset($e['requiere_equipo_de_computo']) && trim($e['requiere_equipo_de_computo']) != "" && in_array(trim($e['requiere_equipo_de_computo']),[0,1]))
								&& (isset($e['regimen_fiscal']) && trim($e['regimen_fiscal']) != "" && App\CatTaxRegime::where('taxRegime',trim($e['regimen_fiscal']))->count()>0)
								&& (isset($e['personal_calificado']) && trim($e['personal_calificado']) != "" && in_array(trim($e['personal_calificado']),[0,1]))
							)
							{
								$employee							= new App\RequisitionEmployee();
								$employee->name						= isset($e['nombre']) && $e['nombre'] != "" ? $e['nombre'] : null;
								$employee->last_name				= isset($e['apellido']) && $e['apellido'] != "" ? $e['apellido'] : null;
								$employee->scnd_last_name			= isset($e['apellido2']) && $e['apellido2'] != "" ? $e['apellido2'] : null;
								$employee->email					= isset($e['email']) && $e['email'] != "" ? $e['email'] : null;
								$employee->curp						= isset($e['curp']) && $e['curp'] != "" ? $e['curp'] : null;
								$employee->rfc						= isset($e['rfc']) && $e['rfc'] != "" ? $e['rfc'] : null;
								$employee->tax_regime				= isset($e['regimen_fiscal']) && $e['regimen_fiscal'] != "" ? $e['regimen_fiscal'] : null;
								$employee->imss						= isset($e['imss']) && $e['imss'] != "" ? $e['imss'] : null;
								$employee->street					= isset($e['calle']) && $e['calle'] != "" ? $e['calle'] : null;
								$employee->number					= isset($e['numero']) && $e['numero'] != "" ? $e['numero'] : null;
								$employee->colony					= isset($e['colonia']) && $e['colonia'] != "" ? $e['colonia'] : null;
								$employee->cp						= isset($e['cp']) && $e['cp'] != "" ? $e['cp'] : null;
								$employee->city						= isset($e['ciudad']) && $e['ciudad'] != "" ? $e['ciudad'] : null;
								$employee->state_id					= isset($e['estado']) && $e['estado'] != "" ? $e['estado'] : null;
								$employee->state					= isset($e['estado_laboral']) && $e['estado_laboral'] != "" ? $e['estado_laboral'] : null;
								$employee->project					= $request->project_id;
								$employee->enterprise				= isset($e['empresa']) && $e['empresa'] != "" ? $e['empresa'] : null;
								$employee->account					= isset($e['clasificacion_gasto']) && $e['clasificacion_gasto'] != "" ? $e['clasificacion_gasto'] : null;
								$employee->direction				= isset($e['direccion']) && $e['direccion'] != "" ? $e['direccion'] : null;
								$employee->department				= isset($e['departamento']) && $e['departamento'] != "" ? $e['departamento'] : null;
								$employee->position					= isset($e['puesto']) && $e['puesto'] != "" ? $e['puesto'] : null;
								$employee->immediate_boss			= isset($e['jefe_inmediato']) && $e['jefe_inmediato'] != "" ? $e['jefe_inmediato'] : null;
								$employee->admissionDate			= isset($e['fecha_ingreso']) && $e['fecha_ingreso'] != "" ? $e['fecha_ingreso'] : null;
								$employee->imssDate					= isset($e['fecha_alta']) && $e['fecha_alta'] != "" ? $e['fecha_alta'] : null;
								$employee->downDate					= isset($e['fecha_baja']) && $e['fecha_baja'] != "" ? $e['fecha_baja'] : null;
								$employee->endingDate				= isset($e['fecha_termino']) && $e['fecha_termino'] != "" ? $e['fecha_termino'] : null;
								$employee->reentryDate				= isset($e['fecha_reingreso']) && $e['fecha_reingreso'] != "" ? $e['fecha_reingreso'] : null;
								$employee->workerType				= isset($e['tipo_contrato']) && $e['tipo_contrato'] != "" ? $e['tipo_contrato'] : null;
								$employee->regime_id				= isset($e['regimen']) && $e['regimen'] != "" ? $e['regimen'] : null;
								$employee->workerStatus				= isset($e['estatus']) && $e['estatus'] != "" ? $e['estatus'] : null;
								$employee->status_imss				= isset($e['estatus_imss']) && $e['estatus_imss'] != "" ? $e['estatus_imss'] : null;
								$employee->status_reason			= isset($e['razon_estatus']) && $e['razon_estatus'] != "" ? $e['razon_estatus'] : null;
								$employee->sdi						= isset($e['sdi']) && $e['sdi'] != "" ? $e['sdi'] : null;
								$employee->periodicity				= isset($e['periodicidad']) && $e['periodicidad'] != "" ? $e['periodicidad'] : null;
								$employee->employer_register		= isset($e['registro_patronal']) && $e['registro_patronal'] != "" ? $e['registro_patronal'] : null;
								$employee->paymentWay				= isset($e['forma_pago']) && $e['forma_pago'] != "" ? $e['forma_pago'] : null;
								$employee->netIncome				= isset($e['sueldo_neto']) && $e['sueldo_neto'] != "" ? $e['sueldo_neto'] : null;
								$employee->complement				= isset($e['complemento']) && $e['complemento'] != "" ? $e['complemento'] : null;
								$employee->fonacot					= isset($e['fonacot']) && $e['fonacot'] != "" ? $e['fonacot'] : null;
								$employee->nomina					= isset($e['porcentaje_nomina']) && $e['porcentaje_nomina'] != "" ? $e['porcentaje_nomina'] : null;
								$employee->bono						= isset($e['porcentaje_bono']) && $e['porcentaje_bono'] != "" ? $e['porcentaje_bono'] : null;
								$employee->infonavitCredit			= isset($e['credito_infonavit']) && $e['credito_infonavit'] != "" ? $e['credito_infonavit'] : null;
								$employee->infonavitDiscount		= isset($e['descuento_infonavit']) && $e['descuento_infonavit'] != "" ? $e['descuento_infonavit'] : null;
								$employee->infonavitDiscountType	= isset($e['tipo_descuento_infonavit']) && $e['tipo_descuento_infonavit'] != "" ? $e['tipo_descuento_infonavit'] : null;
								$employee->wbs_id					= $request->code_wbs;
								$employee->subdepartment_id			= isset($e['subdepartamento']) && $e['subdepartamento'] != "" ? $e['subdepartamento'] : null;
								$employee->viatics					= isset($e['viaticos']) && $e['viaticos'] != "" ? $e['viaticos'] : null;
								$employee->camping					= isset($e['campamento']) && $e['campamento'] != "" ? $e['campamento'] : null;
								$employee->replace					= isset($e['en_reemplazo_de']) && $e['en_reemplazo_de'] != "" ? $e['en_reemplazo_de'] : null;
								$employee->purpose					= isset($e['proposito_del_puesto']) && $e['proposito_del_puesto'] != "" ? $e['proposito_del_puesto'] : null;
								$employee->requeriments				= isset($e['requerimientos_del_puesto']) && $e['requerimientos_del_puesto'] != "" ? $e['requerimientos_del_puesto'] : null;
								$employee->observations				= isset($e['observaciones']) && $e['observaciones'] != "" ? $e['observaciones'] : null;
								$employee->position_immediate_boss	= isset($e['posisicion_de_jefe_inmediato']) && $e['posisicion_de_jefe_inmediato'] != "" ? $e['posisicion_de_jefe_inmediato'] : null;
								$employee->computer_required		= isset($e['requiere_equipo_de_computo']) && $e['requiere_equipo_de_computo'] != "" ? $e['requiere_equipo_de_computo'] : null;
								$employee->qualified_employee		= isset($e['personal_calificado']) && $e['personal_calificado'] != "" ? $e['personal_calificado'] : null;
								$employee->requisition_id			= $idRequisition;
								$employee->save();

								
								if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='' && App\CatBank::where('c_bank',$e['banco'])->count()>0)
								{
									$empAcc             = new App\RequisitionEmployeeAccount();
									$empAcc->idEmployee = $employee->id;
									$empAcc->alias      = empty(trim($e['alias'])) ? null : $e['alias'];
									$empAcc->clabe      = empty(trim($e['clabe'])) ? null : $e['clabe'];
									$empAcc->account    = empty(trim($e['cuenta'])) ? null : $e['cuenta'];
									$empAcc->cardNumber = empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
									$empAcc->idCatBank  = empty(trim($e['banco'])) ? null : $e['banco'];
									$empAcc->recorder   = Auth::user()->id;
									$empAcc->type       = 1;
									$empAcc->save();
								}

								$countRows++;
							}
							else
							{
								$errors++;
							}
						}
					}

				break;

				case 2:
				case 4:
				case 6:
					$t_request              = new App\RequestModel();
					$t_request->fDate       = Carbon::now();
					$t_request->idElaborate = Auth::user()->id;
					$t_request->idRequest   = $request->request_requisition;
					$t_request->idProject   = $request->project_id;
					$t_request->code_wbs    = $request->code_wbs;
					$t_request->code_edt    = $request->code_edt;
					$t_request->status      = 2;
					$t_request->kind        = 19;
					$t_request->save();

					$requisition					= new App\Requisition();
					$requisition->title				= $request->title;
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->idFolio			= $t_request->folio;
					$requisition->idKind			= $t_request->kind;
					$requisition->urgent			= $request->urgent;
					$requisition->code_wbs			= $request->code_wbs;
					$requisition->code_edt			= $request->code_edt;
					$requisition->requisition_type	= $request->requisition_type;
					$requisition->buy_rent			= $request->buy_rent;
					$requisition->validity			= $request->validity;
					$requisition->save();

					$idRequisition = $requisition->id;

					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$detail                = new App\RequisitionDetail();
							$detail->part          = ($i + 1);
							$detail->unit          = $request->unit[$i];
							$detail->name          = $request->name[$i];
							$detail->quantity      = $request->quantity[$i];
							$detail->description   = $request->description[$i];
							if($request->requisition_type == 2)
							{
								$detail->period		= $request->period[$i];
								$detail->category	= $request->category[$i];
							}
							$detail->idRequisition = $idRequisition;
							$detail->save();
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}

					if ($t_request->requisition->details()->exists()) 
					{
						$countDetail = $t_request->requisition->details->count();
					}
					else
					{
						$countDetail = 0;
					}

					$errors = 0;
					if ($request->requisition_type == 2) 
					{
						if(isset($request) && $request->csv_file_service != "" && $request->file('csv_file_service')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_service')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_service')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							
							foreach ($csvArr as $art) 
							{
							
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['periodo']) && trim($art['periodo'])!=""))
								{
								
									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
									if ($check_category != "") 
									{	
										$detail						= new App\RequisitionDetail();
										$detail->category			= $check_category->id;
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $art['nombre'];
										$detail->measurement		= $art['medida'];
										$detail->description		= $art['descripcion'];
										$detail->exists_warehouse	= $art['existencia_almacen'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();

										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
							}
						}
							
					}
					elseif ($request->requisition_type == 4) 
					{
						if(isset($request) && $request->csv_file_subcontract != "" && $request->file('csv_file_subcontract')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_subcontract')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_subcontract')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;

							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!=""))
								{
									$detail						= new App\RequisitionDetail();
									$detail->part				= ($countDetail+$countRows+1);
									$detail->quantity			= $art['cantidad'];
									$detail->unit				= $art['unidad'];
									$detail->name				= $art['nombre'];
									$detail->description		= $art['descripcion'];
									$detail->idRequisition		= $idRequisition;
									$detail->save();
									$countRows ++;
								}
								else
								{
									$errors++;
								}
								
							}

						}
					}
					elseif ($request->requisition_type == 6) 
					{
						if(isset($request) && $request->csv_file_comercial != "" && $request->file('csv_file_comercial')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_comercial')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_comercial')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;

							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!=""))
								{
									$detail						= new App\RequisitionDetail();
									$detail->part				= ($countDetail+$countRows+1);
									$detail->quantity			= $art['cantidad'];
									$detail->unit				= $art['unidad'];
									$detail->name				= $art['nombre'];
									$detail->description		= $art['descripcion'];
									$detail->idRequisition		= $idRequisition;
									$detail->save();
									$countRows ++;
								}
								else
								{
									$errors++;
								}
								
							}

						}
					}
					break;
				
				default:
					# code...
					break;
			}

			if ($errors > 0) 
			{
				$alert = "swal('Requisición Guardada Con Errores','".$errors." registros del archivo CSV no fueron guardados, por favor verifique que los datos capturados en el archivo sean correctos.', 'info');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			}
			return redirect()->route('requisition.edit',['id'=>$t_request->folio])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function saveFollow(Request $request,$id)
	{
		if (Auth::user()->module->where('id',229)->count() > 0) 
		{
			$t_request	= App\RequestModel::find($id);
			if ($t_request->requisition->requisition_type != $request->requisition_type) 
			{
				if (App\RequisitionDetail::where('idRequisition',$t_request->requisition->id)->count() > 0) 
				{
					App\RequisitionDetail::where('idRequisition',$t_request->requisition->id)->delete();
				}

				if ($t_request->requisition->requisition_type == 3) 
				{
					$employees_id = App\RequisitionEmployee::where('requisition_id',$t_request->requisition->id)->pluck('id');

					App\RequisitionEmployeeAccount::whereIn('idEmployee',$employees_id)->delete();
					App\RequisitionEmployee::where('requisition_id',$t_request->requisition->id)->delete();
				}
			}

			$arrayToDelete = explode(",",$request->to_delete);

			for($i = 0; $i < count($arrayToDelete)-1; $i++)
			{
				App\RequisitionDocuments::where('id',$arrayToDelete[$i])->delete();
			}
			
			switch ($request->requisition_type) 
			{
				case 1:
				case 5:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 2;
					$t_request->save();

					$requisition					= App\Requisition::find($t_request->requisition->id);
					$requisition->title				= $request->title;
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent			= $request->urgent;
					$requisition->code_wbs			= $request->code_wbs;
					$requisition->code_edt			= $request->code_edt;
					$requisition->requisition_type	= $request->requisition_type;
					$requisition->buy_rent			= $request->buy_rent;
					$requisition->validity			= $request->validity;
					$requisition->save();

					$idRequisition = $requisition->id;

					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}

					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$c_r = App\CatRequisitionName::where('name',$request->name[$i])->first();
							if(!$c_r)
							{
								$c_r = App\CatRequisitionName::create(['name' => $request->name[$i]]);
							} 

							$name_measurement = App\CatMeasurementUnit::where('description',$request->measurement[$i])->first();
							if(!$name_measurement)
							{
								$name_measurement = App\CatMeasurementUnit::create(['description' => $request->measurement[$i]]);
							} 

							$detail									= new App\RequisitionDetail();
							$detail->category						= $request->category[$i];
							$detail->cat_procurement_material_id	= $request->type[$i];
							$detail->part                           = ($i + 1);
							$detail->quantity						= $request->quantity[$i];
							$detail->unit							= $request->unit[$i];
							$detail->name							= $c_r->name;
							$detail->measurement					= $name_measurement->description;
							$detail->description					= $request->description[$i];
							if ($request->requisition_type == 5) 
							{
								$detail->brand		= $request->brand[$i];
								$detail->model		= $request->model[$i];
								$detail->usage_time	= $request->usage_time[$i];
							}
							$detail->exists_warehouse				= $request->exists_warehouse[$i];
							$detail->idRequisition					= $idRequisition;
							$detail->save();
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}

					if ($t_request->requisition->details()->exists()) 
					{
						$countDetail = $t_request->requisition->details->count();
					}
					else
					{
						$countDetail = 0;
					}

					$errors = 0;
					if ($request->requisition_type == 1) 
					{
						if(isset($request) && $request->csv_file_material != "" && $request->file('csv_file_material')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_material')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_material')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							foreach ($csvArr as $art) 
							{
								$flag = true;
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
								{
									$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
									if(!$c_r)
									{
										$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
									} 
									
									if(isset($art['categoria']) && trim($art['categoria']) == "Material de Procura")
									{
										if (isset($art['tipo']) && trim($art['tipo']) != "") 
										{
											$check_type = App\CatProcurementMaterial::where('name','LIKE','%'.$art['tipo'].'%')->first();
											if ($check_type != "") 
											{
												$type_id = $check_type->id;
											}
											else
											{
												$flag = false;
											}
										}
										else
										{
											$flag = false;
										}
									}
									else
									{
										$type_id = null;
									}

									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->where('status',1)->first();

									if ($check_category != "" && $flag) 
									{
										$category_id = $check_category->id;
										$detail									= new App\RequisitionDetail();
										$detail->category						= $category_id;
										$detail->cat_procurement_material_id	= $type_id;
										$detail->part							= ($countDetail+$countRows+1);
										$detail->quantity						= $art['cantidad'];
										$detail->unit							= $art['unidad'];
										$detail->name							= $c_r->name;
										$detail->measurement					= $art['medida'];
										$detail->description					= $art['descripcion'];
										// $detail->exists_warehouse				= $art['existencia_almacen'];
										$detail->exists_warehouse				= 0;
										$detail->idRequisition					= $idRequisition;
										$detail->save();

										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
							}
						}
					}
					elseif ($request->requisition_type == 5) 
					{
						if(isset($request) && $request->csv_file_machine != "" && $request->file('csv_file_machine')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_machine')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_machine')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['marca']) && trim($art['marca'])!="") && 
									(isset($art['modelo']) && trim($art['modelo'])!="") && 
									(isset($art['tiempo_utilizacion']) && trim($art['tiempo_utilizacion'])!="") && 
									(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
								{
									$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
									if(!$c_r)
									{
										$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
									} 
									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
									if ($check_category != "") 
									{
										$detail						= new App\RequisitionDetail();
										$detail->category			= $check_category->id;
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $c_r->name;
										$detail->measurement		= $art['medida'];
										$detail->description		= $art['descripcion'];
										$detail->brand				= $art['marca'];
										$detail->model				= $art['modelo'];
										$detail->usage_time			= $art['tiempo_utilizacion'];
										$detail->exists_warehouse	= $art['existencia_almacen'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();
										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
								
							}
						}
					}
					break;

				case 3:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 2;
					$t_request->save();

					$requisition                    = App\Requisition::find($t_request->requisition->id);
					$requisition->title				= $request->title;
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent			= $request->urgent;
					$requisition->code_wbs			= $request->code_wbs;
					$requisition->code_edt			= $request->code_edt;
					$requisition->requisition_type	= $request->requisition_type;
					$requisition->buy_rent			= $request->buy_rent;
					$requisition->validity			= $request->validity;
					$requisition->save();

					$idRequisition = $requisition->id;

					/*
						$requisition_staff							= App\RequisitionStaff::find($requisition->staff->id);
						$requisition_staff->boss_id					= $request->boss_id;
						$requisition_staff->staff_reason			= $request->staff_reason;
						$requisition_staff->staff_position			= $request->staff_position;
						$requisition_staff->staff_periodicity		= $request->staff_periodicity;
						$requisition_staff->staff_schedule_start	= $request->staff_schedule_start;
						$requisition_staff->staff_schedule_end		= $request->staff_schedule_end;
						$requisition_staff->staff_min_salary		= $request->staff_min_salary;
						$requisition_staff->staff_max_salary		= $request->staff_max_salary;
						$requisition_staff->staff_s_description		= $request->staff_s_description;
						$requisition_staff->staff_habilities		= $request->staff_habilities;
						$requisition_staff->staff_experience		= $request->staff_experience;
						$requisition_staff->requisition_id 			= $idRequisition;
						$requisition_staff->save();


						if (isset($request->delete_desirables) && count($request->delete_desirables)>0) 
						{
							$deleteDesirables = App\RequisitionStaffDesirables::whereIn('id',$request->delete_desirables)->delete();
						}

						if (isset($request->delete_functions) && count($request->delete_functions)>0) 
						{
							$deleteFunctions = App\RequisitionStaffFunctions::whereIn('id',$request->delete_functions)->delete();
						}

						App\RequisitionStaffResponsibilities::where('requisition_id',$idRequisition)->delete();

						if (isset($request->staff_responsibilities) && count($request->staff_responsibilities)>0) 
						{
							for ($i=0; $i < count($request->staff_responsibilities); $i++) 
							{ 
								$requisition_staff_responsabilities	= new App\RequisitionStaffResponsibilities();
								$requisition_staff_responsabilities->staff_responsibilities	= $request->staff_responsibilities[$i];
								$requisition_staff_responsabilities->requisition_id 		= $idRequisition;
								$requisition_staff_responsabilities->save();
							}
						}

						if (isset($request->tdesirable) && count($request->tdesirable)>0) 
						{
							for ($i=0; $i < count($request->tdesirable); $i++) 
							{
								$requisition_staff_desirables					= new App\RequisitionStaffDesirables();
								$requisition_staff_desirables->desirable		= $request->tdesirable[$i];
								$requisition_staff_desirables->description		= $request->td_descr[$i];
								$requisition_staff_desirables->requisition_id	= $idRequisition;
								$requisition_staff_desirables->save();
							}
						}

						if (isset($request->tfunction) && count($request->tfunction)>0) 
						{
							for ($i=0; $i < count($request->tfunction); $i++) 
							{
								$requisition_staff_function					= new App\RequisitionStaffFunctions();
								$requisition_staff_function->function		= $request->tfunction[$i];
								$requisition_staff_function->description	= $request->tdescr[$i];
								$requisition_staff_function->requisition_id	= $idRequisition;
								$requisition_staff_function->save();
							}
						}
					*/

					if (isset($request->delete_employee) && count($request->delete_employee)>0) 
					{
						App\RequisitionEmployeeAccount::whereIn('idEmployee',$request->delete_employee)->delete();
						App\RequisitionEmployee::whereIn('id',$request->delete_employee)->delete();
					}

					if (isset($request->rq_name) && count($request->rq_name) > 0) 
					{
						for ($i=0; $i < count($request->rq_name); $i++) 
						{ 
							if ($request->rq_employee_id[$i] == "x") 
							{
								$employee	= new App\RequisitionEmployee();
							}
							else
							{
								$employee	= App\RequisitionEmployee::find($request->rq_employee_id[$i]);

								App\RequisitionEmployeeAccount::where('idEmployee',$request->rq_employee_id[$i])->delete();
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
							$employee->enterprise				= $request->rq_work_enterprise[$i];
							$employee->account					= $request->rq_work_account[$i];
							$employee->direction				= $request->rq_work_direction[$i];
							$employee->department				= $request->rq_work_department[$i];
							$employee->position					= $request->rq_work_position[$i];
							$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
							$employee->admissionDate			= $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_income_date[$i])->format('Y-m-d') : null;
							$employee->imssDate					= $request->rq_work_imss_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_imss_date[$i])->format('Y-m-d') : null;
							$employee->downDate					= $request->rq_work_down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_down_date[$i])->format('Y-m-d') : null;
							$employee->endingDate				= $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_ending_date[$i])->format('Y-m-d') : null;
							$employee->reentryDate				= $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
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
							$employee->doc_requisition			= $request->rq_doc_requisition[$i];
							$employee->computer_required		= $request->rq_computer_required[$i];
							$employee->wbs_id 					= $request->code_wbs;

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
							$employee->requisition_id 		= $idRequisition;
							$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
							$employee->save();

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
									$empAcc              = new App\RequisitionEmployeeAccount();
									$empAcc->idEmployee  = $employee->id;
									$empAcc->beneficiary = $request->$beneficiary[$k];
									$empAcc->type        = $request->$type[$k];
									$empAcc->alias       = $request->$alias[$k];
									$empAcc->clabe       = $request->$clabe[$k];
									$empAcc->account     = $request->$account[$k];
									$empAcc->cardNumber  = $request->$cardNumber[$k];
									$empAcc->idCatBank   = $request->$idCatBank[$k];
									$empAcc->branch      = $request->$branch[$k];
									$empAcc->recorder    = Auth::user()->id;
									$empAcc->save();
								}
							}

							App\RequisitionEmployeeDocument::where('requisition_employee_id',$employee->id)->delete();
							$name_other_document = 'name_other_document_'.$i;
							$path_other_document = 'path_other_document_'.$i;
							if (isset($request->$name_other_document) && count($request->$name_other_document)>0) 
							{
								for ($d=0; $d < count($request->$name_other_document); $d++) 
								{ 
									$checkDoc 	= App\RequisitionEmployeeDocument::where('name',$request->$name_other_document[$d])
												->where('path',$request->$path_other_document[$d])
												->where('requisition_employee_id',$employee->id)
												->count();
									if ($checkDoc == 0) 
									{
										$other							= new App\RequisitionEmployeeDocument();
										$other->name					= $request->$name_other_document[$d];
										$other->path					= $request->$path_other_document[$d];
										$other->requisition_employee_id	= $employee->id;
										$other->save();
									}
								}
							}
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					$errors = 0;
					
					if(($request->csv_file_personal != "" || !empty($request->csv_file_personal)))
					{
						$valid = $request->file('csv_file_personal')->getClientOriginalExtension();
						
						if($valid != 'csv')
						{
							$alert = "swal('','".Lang::get("messages.extension_allowed",["param"=>'CSV'])."', 'error')";
							return back()->with('alert',$alert);	
						}

						$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_personal')->getClientOriginalExtension();
						\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_personal')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
						$path		= \Storage::disk('reserved')->path($name);
						$csvArr		= array();
						if (($handle = fopen($path, "r")) !== FALSE)
						{
							$first	= true;
							while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
							{
								if($first)
								{
									$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
									$first		= false;
								}
								$csvArr[]	= $data;
							}
							fclose($handle);
						}
						array_walk($csvArr, function(&$a) use ($csvArr)
						{
							$a = array_combine($csvArr[0], $a);
						});

						$headers = [
							'nombre',
							'apellido',
							'apellido2',
							'curp',
							'rfc',
							'email',
							'regimen_fiscal',
							'imss',
							'calle',
							'numero',
							'colonia',
							'cp',
							'ciudad',
							'estado',
							'personal_calificado',
							'en_reemplazo_de',
							'proposito_del_puesto',
							'requerimientos_del_puesto',
							'observaciones',
							'requiere_equipo_de_computo',
							'estado_laboral',
							'empresa',
							'clasificacion_gasto',
							'lugar_trabajo',
							'direccion',
							'departamento',
							'subdepartamento',
							'puesto',
							'jefe_inmediato',
							'posisicion_de_jefe_inmediato',
							'estatus_imss',
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
							'viaticos',
							'campamento',
							'sueldo_neto',
							'complemento',
							'fonacot',
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

						if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
						{
							$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'error')";
							return back()->with('alert',$alert);	
						}

						array_shift($csvArr);

						$countRows = 0;
						
						foreach ($csvArr as $e) 
						{
							/*
							if(!isset($e['nombre']) || trim($e['nombre']) == "")
							{
								return 'nombre= '.$e['nombre'];
							}
							if(!isset($e['apellido']) || trim($e['apellido']) == "")
							{
								return 'apellido= '.$e['apellido'];
							}
							if(!isset($e['curp']) || trim($e['curp']) == "")
							{
								return 'curp= '.$e['curp'];
							}
							if(!isset($e['calle']) || trim($e['calle']) == "")
							{
								return 'calle= '.$e['calle'];
							}
							if(!isset($e['numero']) || trim($e['numero']) == "")
							{
								return 'numero= '.$e['numero'];
							}
							if(!isset($e['colonia']) || trim($e['colonia']) == "")
							{
								return 'colonia= '.$e['colonia'];
							}
							if(!isset($e['cp']) || trim($e['cp']) == "" || App\CatZipCode::where('zip_code',trim($e['cp']))->count()==0)
							{
								return 'cp= '.$e['cp'];
							}
							if(!isset($e['ciudad']) || trim($e['ciudad']) == "")
							{
								return 'ciudad= '.$e['ciudad'];
							}
							if(!isset($e['estado']) || trim($e['estado']) == "" || App\State::where('idstate',trim($e['estado']))->count()==0)
							{
								return 'estado= '.$e['estado'];
							}
							if(!isset($e['estado_laboral']) || trim($e['estado_laboral']) == "" || App\State::where('idstate',trim($e['estado_laboral']))->count()==0)
							{
								return 'estado_laboral= '.$e['estado_laboral'];
							}
							if(!isset($e['empresa']) || trim($e['empresa']) == "" || App\Enterprise::where('id',trim($e['empresa']))->count()==0)
							{
								return 'empresa= '.$e['empresa'];
							}
							if(!isset($e['clasificacion_gasto']) || trim($e['clasificacion_gasto']) == "" || App\Account::where('idAccAcc',trim($e['clasificacion_gasto']))->count()==0)
							{
								return 'clasificacion_gasto= '.$e['clasificacion_gasto'];
							}
							if(!isset($e['direccion']) || trim($e['direccion']) == "" || App\Area::where('id',trim($e['direccion']))->count()==0)
							{
								return 'direccion= '.$e['direccion'];
							}
							if(!isset($e['puesto']) || trim($e['puesto']) == "")
							{
								return 'puesto= '.$e['puesto'];
							}
							if(!isset($e['fecha_ingreso']) || trim($e['fecha_ingreso']) == "" || DateTime::createFromFormat('Y-m-d', trim($e['fecha_ingreso'])) == false)
							{
								return 'fecha_ingreso= '.$e['fecha_ingreso'];
							}
							if(!isset($e['tipo_contrato']) || trim($e['tipo_contrato']) == "" || App\CatContractType::where('id',trim($e['tipo_contrato']))->count() == 0)
							{
								return 'tipo_contrato= '.$e['tipo_contrato'];
							}
							if(!isset($e['regimen']) || trim($e['regimen']) == "" || App\CatRegimeType::where('id',trim($e['regimen']))->count() == 0)
							{
								return 'regimen= '.$e['regimen'];
							}
							if(!isset($e['periodicidad']) || trim($e['periodicidad']) == "" || App\CatPeriodicity::where('c_periodicity',trim($e['periodicidad']))->count() == 0)
							{
								return 'periodicidad= '.$e['periodicidad'];
							}
							if(!isset($e['registro_patronal']) || trim($e['registro_patronal']) == "" || App\EmployerRegister::where('employer_register',trim($e['registro_patronal']))->count() == 0)
							{
								return 'registro_patronal= '.$e['registro_patronal'];
							}
							if(!isset($e['forma_pago']) || trim($e['forma_pago']) == "" || App\PaymentMethod::where('idpaymentMethod',trim($e['forma_pago']))->count() == 0)
							{
								return 'forma_pago= '.$e['forma_pago'];
							}
							if(!isset($e['requiere_equipo_de_computo']) || trim($e['requiere_equipo_de_computo']) == "" || !in_array(trim($e['requiere_equipo_de_computo']),[0,1]))
							{
								return 'requiere_equipo_de_computo= '.$e['requiere_equipo_de_computo'];
							}
							if(!isset($e['regimen_fiscal']) || trim($e['regimen_fiscal']) == "" || App\CatTaxRegime::where('taxRegime',trim($e['regimen_fiscal']))->count() == 0)
							{
								return 'regimen_fiscal= '.$e['regimen_fiscal'];
							}
							if(!isset($e['personal_calificado']) || trim($e['personal_calificado']) == "" || !in_array($e['personal_calificado'],[0,1]))
							{
								return 'personal_calificado= '.$e['personal_calificado'];
							}
							return 'x';
							*/

							if (isset($e['curp']) && trim($e['curp']) != "" ) 
							{
								$check_employee 	= App\RealEmployee::where('curp',trim($e['curp']))->count();
								$check_requisition 	= App\RequisitionEmployee::leftJoin('requisitions','requisition_employees.requisition_id','requisitions.id')
													->leftJoin('request_models','requisitions.idFolio','request_models.folio')
													->select('requisition_employees.curp')
													->whereNotIn('request_models.status',[5,6,7,23,28])
													->where('requisition_employees.curp',trim($e['curp']))
													->count();
							}
							else
							{
								$check_employee		= 1;
								$check_requisition	= 1;
							}

							if ((isset($e['nombre']) && trim($e['nombre']) != "")
								&& (isset($e['apellido']) && trim($e['apellido']) != "")
								&& (isset($e['curp']) && trim($e['curp']) != "" && $check_employee == 0 && $check_requisition == 0)
								&& (isset($e['calle']) && trim($e['calle']) != "")
								&& (isset($e['numero']) && trim($e['numero']) != "")
								&& (isset($e['colonia']) && trim($e['colonia']) != "")
								&& (isset($e['cp']) && trim($e['cp']) != "" && App\CatZipCode::where('zip_code',trim($e['cp']))->count()>0)
								&& (isset($e['ciudad']) && trim($e['ciudad']) != "")
								&& (isset($e['estado']) && trim($e['estado']) != "" && App\State::where('idstate',trim($e['estado']))->count()>0)
								&& (isset($e['estado_laboral']) && trim($e['estado_laboral']) != "" && App\State::where('idstate',trim($e['estado_laboral']))->count()>0)
								&& (isset($e['empresa']) && trim($e['empresa']) != "" && App\Enterprise::where('id',trim($e['empresa']))->count()>0)
								&& (isset($e['clasificacion_gasto']) && trim($e['clasificacion_gasto']) != "" && App\Account::where('idAccAcc',trim($e['clasificacion_gasto']))->count()>0)
								&& (isset($e['direccion']) && trim($e['direccion']) != "" && App\Area::where('id',trim($e['direccion']))->count()>0)
								&& (isset($e['puesto']) && trim($e['puesto']) != "")
								&& (isset($e['fecha_ingreso']) && trim($e['fecha_ingreso']) != "" && DateTime::createFromFormat('Y-m-d', trim($e['fecha_ingreso'])) !== false)
								&& (isset($e['tipo_contrato']) && trim($e['tipo_contrato']) != "" && App\CatContractType::where('id',trim($e['tipo_contrato']))->count()>0)
								&& (isset($e['regimen']) && trim($e['regimen']) != "" && App\CatRegimeType::where('id',trim($e['regimen']))->count()>0)
								&& (isset($e['periodicidad']) && trim($e['periodicidad']) != "" && App\CatPeriodicity::where('c_periodicity',trim($e['periodicidad']))->count()>0)
								&& (isset($e['registro_patronal']) && trim($e['registro_patronal']) != "" && App\EmployerRegister::where('employer_register',trim($e['registro_patronal']))->count()>0)
								&& (isset($e['forma_pago']) && trim($e['forma_pago']) != "" && App\PaymentMethod::where('idpaymentMethod',trim($e['forma_pago']))->count()>0)
								&& (isset($e['requiere_equipo_de_computo']) && trim($e['requiere_equipo_de_computo']) != "" && in_array(trim($e['requiere_equipo_de_computo']),[0,1]))
								&& (isset($e['regimen_fiscal']) && trim($e['regimen_fiscal']) != "" && App\CatTaxRegime::where('taxRegime',trim($e['regimen_fiscal']))->count()>0)
								&& (isset($e['personal_calificado']) && trim($e['personal_calificado']) != "" && in_array(trim($e['personal_calificado']),[0,1]))
							)
							{
								$employee							= new App\RequisitionEmployee();
								$employee->name						= isset($e['nombre']) && $e['nombre'] != "" ? $e['nombre'] : null;
								$employee->last_name				= isset($e['apellido']) && $e['apellido'] != "" ? $e['apellido'] : null;
								$employee->scnd_last_name			= isset($e['apellido2']) && $e['apellido2'] != "" ? $e['apellido2'] : null;
								$employee->email					= isset($e['email']) && $e['email'] != "" ? $e['email'] : null;
								$employee->curp						= isset($e['curp']) && $e['curp'] != "" ? $e['curp'] : null;
								$employee->rfc						= isset($e['rfc']) && $e['rfc'] != "" ? $e['rfc'] : null;
								$employee->tax_regime				= isset($e['regimen_fiscal']) && $e['regimen_fiscal'] != "" ? $e['regimen_fiscal'] : null;
								$employee->imss						= isset($e['imss']) && $e['imss'] != "" ? $e['imss'] : null;
								$employee->street					= isset($e['calle']) && $e['calle'] != "" ? $e['calle'] : null;
								$employee->number					= isset($e['numero']) && $e['numero'] != "" ? $e['numero'] : null;
								$employee->colony					= isset($e['colonia']) && $e['colonia'] != "" ? $e['colonia'] : null;
								$employee->cp						= isset($e['cp']) && $e['cp'] != "" ? $e['cp'] : null;
								$employee->city						= isset($e['ciudad']) && $e['ciudad'] != "" ? $e['ciudad'] : null;
								$employee->state_id					= isset($e['estado']) && $e['estado'] != "" ? $e['estado'] : null;
								$employee->state					= isset($e['estado_laboral']) && $e['estado_laboral'] != "" ? $e['estado_laboral'] : null;
								$employee->project					= $request->project_id;
								$employee->enterprise				= isset($e['empresa']) && $e['empresa'] != "" ? $e['empresa'] : null;
								$employee->account					= isset($e['clasificacion_gasto']) && $e['clasificacion_gasto'] != "" ? $e['clasificacion_gasto'] : null;
								$employee->direction				= isset($e['direccion']) && $e['direccion'] != "" ? $e['direccion'] : null;
								$employee->department				= isset($e['departamento']) && $e['departamento'] != "" ? $e['departamento'] : null;
								$employee->position					= isset($e['puesto']) && $e['puesto'] != "" ? $e['puesto'] : null;
								$employee->immediate_boss			= isset($e['jefe_inmediato']) && $e['jefe_inmediato'] != "" ? $e['jefe_inmediato'] : null;
								$employee->admissionDate			= isset($e['fecha_ingreso']) && $e['fecha_ingreso'] != "" ? $e['fecha_ingreso'] : null;
								$employee->imssDate					= isset($e['fecha_alta']) && $e['fecha_alta'] != "" ? $e['fecha_alta'] : null;
								$employee->downDate					= isset($e['fecha_baja']) && $e['fecha_baja'] != "" ? $e['fecha_baja'] : null;
								$employee->endingDate				= isset($e['fecha_termino']) && $e['fecha_termino'] != "" ? $e['fecha_termino'] : null;
								$employee->reentryDate				= isset($e['fecha_reingreso']) && $e['fecha_reingreso'] != "" ? $e['fecha_reingreso'] : null;
								$employee->workerType				= isset($e['tipo_contrato']) && $e['tipo_contrato'] != "" ? $e['tipo_contrato'] : null;
								$employee->regime_id				= isset($e['regimen']) && $e['regimen'] != "" ? $e['regimen'] : null;
								$employee->workerStatus				= isset($e['estatus']) && $e['estatus'] != "" ? $e['estatus'] : null;
								$employee->status_imss				= isset($e['estatus_imss']) && $e['estatus_imss'] != "" ? $e['estatus_imss'] : null;
								$employee->status_reason			= isset($e['razon_estatus']) && $e['razon_estatus'] != "" ? $e['razon_estatus'] : null;
								$employee->sdi						= isset($e['sdi']) && $e['sdi'] != "" ? $e['sdi'] : null;
								$employee->periodicity				= isset($e['periodicidad']) && $e['periodicidad'] != "" ? $e['periodicidad'] : null;
								$employee->employer_register		= isset($e['registro_patronal']) && $e['registro_patronal'] != "" ? $e['registro_patronal'] : null;
								$employee->paymentWay				= isset($e['forma_pago']) && $e['forma_pago'] != "" ? $e['forma_pago'] : null;
								$employee->netIncome				= isset($e['sueldo_neto']) && $e['sueldo_neto'] != "" ? $e['sueldo_neto'] : null;
								$employee->complement				= isset($e['complemento']) && $e['complemento'] != "" ? $e['complemento'] : null;
								$employee->fonacot					= isset($e['fonacot']) && $e['fonacot'] != "" ? $e['fonacot'] : null;
								$employee->nomina					= isset($e['porcentaje_nomina']) && $e['porcentaje_nomina'] != "" ? $e['porcentaje_nomina'] : null;
								$employee->bono						= isset($e['porcentaje_bono']) && $e['porcentaje_bono'] != "" ? $e['porcentaje_bono'] : null;
								$employee->infonavitCredit			= isset($e['credito_infonavit']) && $e['credito_infonavit'] != "" ? $e['credito_infonavit'] : null;
								$employee->infonavitDiscount		= isset($e['descuento_infonavit']) && $e['descuento_infonavit'] != "" ? $e['descuento_infonavit'] : null;
								$employee->infonavitDiscountType	= isset($e['tipo_descuento_infonavit']) && $e['tipo_descuento_infonavit'] != "" ? $e['tipo_descuento_infonavit'] : null;
								$employee->wbs_id					= $request->code_wbs;
								$employee->subdepartment_id			= isset($e['subdepartamento']) && $e['subdepartamento'] != "" ? $e['subdepartamento'] : null;
								$employee->viatics					= isset($e['viaticos']) && $e['viaticos'] != "" ? $e['viaticos'] : null;
								$employee->camping					= isset($e['campamento']) && $e['campamento'] != "" ? $e['campamento'] : null;
								$employee->replace					= isset($e['en_reemplazo_de']) && $e['en_reemplazo_de'] != "" ? $e['en_reemplazo_de'] : null;
								$employee->purpose					= isset($e['proposito_del_puesto']) && $e['proposito_del_puesto'] != "" ? $e['proposito_del_puesto'] : null;
								$employee->requeriments				= isset($e['requerimientos_del_puesto']) && $e['requerimientos_del_puesto'] != "" ? $e['requerimientos_del_puesto'] : null;
								$employee->observations				= isset($e['observaciones']) && $e['observaciones'] != "" ? $e['observaciones'] : null;
								$employee->position_immediate_boss	= isset($e['posisicion_de_jefe_inmediato']) && $e['posisicion_de_jefe_inmediato'] != "" ? $e['posisicion_de_jefe_inmediato'] : null;
								$employee->computer_required		= isset($e['requiere_equipo_de_computo']) && $e['requiere_equipo_de_computo'] != "" ? $e['requiere_equipo_de_computo'] : null;
								$employee->qualified_employee		= isset($e['personal_calificado']) && $e['personal_calificado'] != "" ? $e['personal_calificado'] : null;
								$employee->requisition_id			= $idRequisition;
								$employee->save();

								
								if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='' && App\CatBank::where('c_bank',$e['banco'])->count()>0)
								{
									$empAcc             = new App\RequisitionEmployeeAccount();
									$empAcc->idEmployee = $employee->id;
									$empAcc->alias      = empty(trim($e['alias'])) ? null : $e['alias'];
									$empAcc->clabe      = empty(trim($e['clabe'])) ? null : $e['clabe'];
									$empAcc->account    = empty(trim($e['cuenta'])) ? null : $e['cuenta'];
									$empAcc->cardNumber = empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
									$empAcc->idCatBank  = empty(trim($e['banco'])) ? null : $e['banco'];
									$empAcc->recorder   = Auth::user()->id;
									$empAcc->type       = 1;
									$empAcc->save();
								}

								$countRows++;
							}
							else
							{
								$errors++;
							}
						}
					}

				break;

				case 2:
				case 4:
				case 6:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 2;
					$t_request->save();

					$requisition					= App\Requisition::find($t_request->requisition->id);
					$requisition->title				= $request->title;
					$requisition->date_request		= Carbon::now();
					$requisition->date_comparation	= $request->date_comparation;
					$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent			= $request->urgent;
					$requisition->code_wbs			= $request->code_wbs;
					$requisition->code_edt			= $request->code_edt;
					$requisition->requisition_type	= $request->requisition_type;
					$requisition->buy_rent			= $request->buy_rent;
					$requisition->validity			= $request->validity;
					$requisition->save();
					$idRequisition 					= $requisition->id;
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}
					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$detail					= new App\RequisitionDetail();
							$detail->part			= ($i + 1);
							$detail->unit           = $request->unit[$i];
							$detail->name           = $request->name[$i];
							$detail->quantity		= $request->quantity[$i];
							$detail->description	= $request->description[$i];
							if($request->requisition_type == 2)
							{
								$detail->category	= $request->category[$i];
								$detail->period		= $request->period[$i];
							}
							$detail->idRequisition	= $idRequisition;
							$detail->save();
						}
					}
					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					
					if ($t_request->requisition->details()->exists()) 
					{
						$countDetail = $t_request->requisition->details->count();
					}
					else
					{
						$countDetail = 0;
					}

					$errors = 0;
					if ($request->requisition_type == 2) 
					{
						if(isset($request) && $request->csv_file_service != "" && $request->file('csv_file_service')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_service')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_service')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;
							
							foreach ($csvArr as $art) 
							{
							
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
									(isset($art['periodo']) && trim($art['periodo'])!=""))
								{
									$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
									if ($check_category != "") 
									{	
										$detail						= new App\RequisitionDetail();
										$detail->category			= $check_category->id;
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $art['nombre'];
										$detail->measurement		= $art['medida'];
										$detail->description		= $art['descripcion'];
										$detail->exists_warehouse	= $art['existencia_almacen'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();

										$countRows++;
									}
									else
									{
										$errors++;
									}
								}
								else
								{
									$errors++;
								}
							}
						}
							
					}
					elseif ($request->requisition_type == 4) 
					{
						if(isset($request) && $request->csv_file_subcontract != "" && $request->file('csv_file_subcontract')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_subcontract')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_subcontract')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;

							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!=""))
								{
									$detail						= new App\RequisitionDetail();
									$detail->part				= ($countDetail+$countRows+1);
									$detail->quantity			= $art['cantidad'];
									$detail->unit				= $art['unidad'];
									$detail->name				= $art['nombre'];
									$detail->description		= $art['descripcion'];
									$detail->idRequisition		= $idRequisition;
									$detail->save();
									$countRows ++;
								}
								else
								{
									$errors++;
								}
								
							}

						}
					}
					elseif ($request->requisition_type == 6) 
					{
						if(isset($request) && $request->csv_file_comercial != "" && $request->file('csv_file_comercial')->isValid())
						{
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_comercial')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_comercial')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
							array_shift($csvArr);

							$countRows = 0;

							foreach ($csvArr as $art) 
							{
								if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
									(isset($art['nombre']) && trim($art['nombre'])!="") && 
									(isset($art['unidad']) && trim($art['unidad'])!="") && 
									(isset($art['descripcion']) && trim($art['descripcion'])!=""))
								{
									$detail						= new App\RequisitionDetail();
									$detail->part				= ($countDetail+$countRows+1);
									$detail->quantity			= $art['cantidad'];
									$detail->unit				= $art['unidad'];
									$detail->name				= $art['nombre'];
									$detail->description		= $art['descripcion'];
									$detail->idRequisition		= $idRequisition;
									$detail->save();
									$countRows ++;
								}
								else
								{
									$errors++;
								}
								
							}

						}
					}
					break;
				
				default:
					# code...
					break;
			}		

			if ($errors > 0) 
			{
				$alert = "swal('Requisición Guardada Con Errores','".$errors." registros del archivo CSV no fueron guardados, por favor verifique que los datos capturados en el archivo sean correctos.', 'info');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			}
			// return $check_unit;
			return redirect()->route('requisition.edit',['id'=>$t_request->folio])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function update(Request $request,$id)
	{			
		

		if (Auth::user()->module->where('id',229)->count() > 0) 
		{
			$t_request	= App\RequestModel::find($id);
			if ($t_request->requisition->requisition_type != $request->requisition_type) 
			{
				if (App\RequisitionDetail::where('idRequisition',$t_request->requisition->id)->count() > 0) 
				{
					App\RequisitionDetail::where('idRequisition',$t_request->requisition->id)->delete();
				}

				if ($t_request->requisition->requisition_type == 3) 
				{
					$employees_id = App\RequisitionEmployee::where('requisition_id',$t_request->requisition->id)->pluck('id');

					App\RequisitionEmployeeAccount::whereIn('idEmployee',$employees_id)->delete();
					App\RequisitionEmployee::where('requisition_id',$t_request->requisition->id)->delete();
				}
			}

			$arrayToDelete = explode(",",$request->to_delete);

			for($i = 0; $i < count($arrayToDelete)-1; $i++)
			{
				App\RequisitionDocuments::where('id',$arrayToDelete[$i])->delete();
			}

			$generatedRequisitionNumber = null;
			if($request->project_id == 126 && $request->code_wbs != '')
			{
				$wbs_code = $request->code_wbs;
				$wbsModel = App\CatCodeWBS::find($request->code_wbs);
				$requisitionRequest = App\RequestModel::whereNotIn('status',[1,2])
				->whereHas('requisition', function($q) use($wbs_code)
				{
					$q->where('code_wbs',$wbs_code);
				})
				->count();
				$edtPart = null;
				if($request->code_edt != '')
				{
					$edtModel = App\CatCodeEDT::find($request->code_edt);
					$edtPart  = '-'.$edtModel->edt_number.'-'.$edtModel->phase;
				}
				$generatedRequisitionNumber = 'PIM-R2B-P6-'.$wbsModel->code.$edtPart.'-RQ-'.str_pad($requisitionRequest + 1, 4, '0',STR_PAD_LEFT);
			}
			switch ($request->requisition_type) 
			{
				case 1:
				case 5:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 3;
					$t_request->fDate     = Carbon::now();
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();
					$number = $count + 1;

					$requisition                   = App\Requisition::find($t_request->requisition->id);
					$requisition->title            = $request->title;
					$requisition->date_request     = Carbon::now();
					$requisition->number           = $number;
					$requisition->date_comparation = $request->date_comparation;
					$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent           = $request->urgent;
					$requisition->code_wbs         = $request->code_wbs;
					$requisition->code_edt         = $request->code_edt;
					$requisition->requisition_type = $request->requisition_type;
					$requisition->buy_rent         = $request->buy_rent;
					$requisition->validity         = $request->validity;
					$requisition->generated_number = $generatedRequisitionNumber;
					$requisition->save();

					$idRequisition = $requisition->id;
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}
					if (isset($request->quantity) && count($request->quantity)>0)
					{
						$detail_exist = App\RequisitionDetail::where('idRequisition',$idRequisition)->count();
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$c_r = App\CatRequisitionName::where('name',$request->name[$i])->first();
							if(!$c_r)
							{
								$c_r = App\CatRequisitionName::create(['name' => $request->name[$i]]);
							} 

							$name_measurement = App\CatMeasurementUnit::where('description',$request->measurement[$i])->first();
							if(!$name_measurement)
							{
								$name_measurement = App\CatMeasurementUnit::create(['description' => $request->measurement[$i]]);
							} 

							$detail									= new App\RequisitionDetail();
							$detail->category						= $request->category[$i];
							$detail->cat_procurement_material_id	= $request->type[$i];
							$detail->part							= ($detail_exist + 1 + $i);
							$detail->quantity						= $request->quantity[$i];
							$detail->unit							= $request->unit[$i];
							$detail->name							= $c_r->name;
							$detail->measurement					= $name_measurement->description;
							$detail->description					= $request->description[$i];
							if ($request->requisition_type == 5) 
							{
								$detail->brand		= $request->brand[$i];
								$detail->model		= $request->model[$i];
								$detail->usage_time	= $request->usage_time[$i];
							}
							$detail->exists_warehouse				= $request->exists_warehouse[$i];
							$detail->idRequisition					= $idRequisition;
							$detail->save();
						}
					}


					$count = 1;
					foreach($t_request->requisition->details as $detail)
					{
						$detail->part	= $count;
						$detail->save();
						$count++;
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					break;

				case 3:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 3;
					$t_request->fDate     = Carbon::now();
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();
					$number = $count + 1;

					$requisition                   = App\Requisition::find($t_request->requisition->id);
					$requisition->title            = $request->title;
					$requisition->date_request     = Carbon::now();
					$requisition->number           = $number;
					$requisition->date_comparation = $request->date_comparation;
					$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent           = $request->urgent;
					$requisition->code_wbs         = $request->code_wbs;
					$requisition->code_edt         = $request->code_edt;
					$requisition->requisition_type = $request->requisition_type;
					$requisition->buy_rent         = $request->buy_rent;
					$requisition->validity         = $request->validity;
					$requisition->generated_number = $generatedRequisitionNumber;
					$requisition->save();
					
					$idRequisition = $requisition->id;

					/*
						$requisition_staff							= App\RequisitionStaff::find($requisition->staff->id);
						$requisition_staff->boss_id					= $request->boss_id;
						$requisition_staff->staff_reason			= $request->staff_reason;
						$requisition_staff->staff_position			= $request->staff_position;
						$requisition_staff->staff_periodicity		= $request->staff_periodicity;
						$requisition_staff->staff_schedule_start	= $request->staff_schedule_start;
						$requisition_staff->staff_schedule_end		= $request->staff_schedule_end;
						$requisition_staff->staff_min_salary		= $request->staff_min_salary;
						$requisition_staff->staff_max_salary		= $request->staff_max_salary;
						$requisition_staff->staff_s_description		= $request->staff_s_description;
						$requisition_staff->staff_habilities		= $request->staff_habilities;
						$requisition_staff->staff_experience		= $request->staff_experience;
						$requisition_staff->requisition_id 			= $idRequisition;
						$requisition_staff->save();


						if (isset($request->delete_desirables) && count($request->delete_desirables)>0) 
						{
							$deleteDesirables = App\RequisitionStaffDesirables::whereIn('id',$request->delete_desirables)->delete();
						}

						if (isset($request->delete_functions) && count($request->delete_functions)>0) 
						{
							$deleteFunctions = App\RequisitionStaffFunctions::whereIn('id',$request->delete_functions)->delete();
						}

						App\RequisitionStaffResponsibilities::where('requisition_id',$idRequisition)->delete();

						if (isset($request->staff_responsibilities) && count($request->staff_responsibilities)>0) 
						{
							for ($i=0; $i < count($request->staff_responsibilities); $i++) 
							{ 
								$requisition_staff_responsabilities	= new App\RequisitionStaffResponsibilities();
								$requisition_staff_responsabilities->staff_responsibilities	= $request->staff_responsibilities[$i];
								$requisition_staff_responsabilities->requisition_id 		= $idRequisition;
								$requisition_staff_responsabilities->save();
							}
						}

						if (isset($request->tdesirable) && count($request->tdesirable)>0) 
						{
							for ($i=0; $i < count($request->tdesirable); $i++) 
							{
								$requisition_staff_desirables					= new App\RequisitionStaffDesirables();
								$requisition_staff_desirables->desirable		= $request->tdesirable[$i];
								$requisition_staff_desirables->description		= $request->td_descr[$i];
								$requisition_staff_desirables->requisition_id	= $idRequisition;
								$requisition_staff_desirables->save();
							}
						}

						if (isset($request->tfunction) && count($request->tfunction)>0) 
						{
							for ($i=0; $i < count($request->tfunction); $i++) 
							{
								$requisition_staff_function					= new App\RequisitionStaffFunctions();
								$requisition_staff_function->function		= $request->tfunction[$i];
								$requisition_staff_function->description	= $request->tdescr[$i];
								$requisition_staff_function->requisition_id	= $idRequisition;
								$requisition_staff_function->save();
							}
						}
					*/

					if (isset($request->delete_employee) && count($request->delete_employee)>0) 
					{
						App\RequisitionEmployeeAccount::whereIn('idEmployee',$request->delete_employee)->delete();
						App\RequisitionEmployee::whereIn('id',$request->delete_employee)->delete();
					}
					
					if (isset($request->rq_name) && count($request->rq_name)>0) 
					{
						for ($i=0; $i < count($request->rq_name); $i++) 
						{ 
							if ($request->rq_employee_id[$i] == "x") 
							{
								$employee	= new App\RequisitionEmployee();
							}
							else
							{
								$employee	= App\RequisitionEmployee::find($request->rq_employee_id[$i]);
								App\RequisitionEmployeeAccount::where('idEmployee',$request->rq_employee_id[$i])->delete();
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
							$employee->enterprise				= $request->rq_work_enterprise[$i];
							$employee->account					= $request->rq_work_account[$i];
							$employee->direction				= $request->rq_work_direction[$i];
							$employee->department				= $request->rq_work_department[$i];
							$employee->position					= $request->rq_work_position[$i];
							$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
							$employee->admissionDate			= $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_income_date[$i])->format('Y-m-d') : null;
							$employee->imssDate					= $request->rq_work_imss_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_imss_date[$i])->format('Y-m-d') : null;
							$employee->downDate					= $request->rq_work_down_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_down_date[$i])->format('Y-m-d') : null;
							$employee->endingDate				= $request->rq_work_ending_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_ending_date[$i])->format('Y-m-d') : null;
							$employee->reentryDate				= $request->rq_work_reentry_date[$i]	!= "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
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
							$employee->doc_requisition			= $request->rq_doc_requisition[$i];
							$employee->computer_required		= $request->rq_computer_required[$i];
							$employee->wbs_id 					= $request->code_wbs;

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
							$employee->requisition_id 		= $idRequisition;
							$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
							$employee->save();

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
									$empAcc              = new App\RequisitionEmployeeAccount();
									$empAcc->idEmployee  = $employee->id;
									$empAcc->beneficiary = $request->$beneficiary[$k];
									$empAcc->type        = $request->$type[$k];
									$empAcc->alias       = $request->$alias[$k];
									$empAcc->clabe       = $request->$clabe[$k];
									$empAcc->account     = $request->$account[$k];
									$empAcc->cardNumber  = $request->$cardNumber[$k];
									$empAcc->idCatBank   = $request->$idCatBank[$k];
									$empAcc->branch      = $request->$branch[$k];
									$empAcc->recorder    = Auth::user()->id;
									$empAcc->save();
								}
							}

							App\RequisitionEmployeeDocument::where('requisition_employee_id',$employee->id)->delete();
							$name_other_document = 'name_other_document_'.$i;
							$path_other_document = 'path_other_document_'.$i;
							if (isset($request->$name_other_document) && count($request->$name_other_document)>0) 
							{
								for ($d=0; $d < count($request->$name_other_document); $d++) 
								{ 
									$checkDoc 	= App\RequisitionEmployeeDocument::where('name',$request->$name_other_document[$d])
												->where('path',$request->$path_other_document[$d])
												->where('requisition_employee_id',$employee->id)
												->count();
									if ($checkDoc == 0) 
									{
										$other							= new App\RequisitionEmployeeDocument();
										$other->name					= $request->$name_other_document[$d];
										$other->path					= $request->$path_other_document[$d];
										$other->requisition_employee_id	= $employee->id;
										$other->save();
									}
								}
							}
						}
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}

				break;

				case 2:
				case 4:
				case 6:
					$t_request            = App\RequestModel::find($id);
					$t_request->idRequest = $request->request_requisition;
					$t_request->idProject = $request->project_id;
					$t_request->code_wbs  = $request->code_wbs;
					$t_request->code_edt  = $request->code_edt;
					$t_request->status    = 3;
					$t_request->fDate     = Carbon::now();
					$t_request->save();

					$count	= App\RequestModel::where('kind',19)
							->where('idProject',$request->project_id)
							->count();
					$number = $count + 1;

					$requisition                   = App\Requisition::find($t_request->requisition->id);
					$requisition->title            = $request->title;
					$requisition->date_request     = Carbon::now();
					$requisition->number           = $number;
					$requisition->date_comparation = $request->date_comparation;
					$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
					$requisition->urgent           = $request->urgent;
					$requisition->code_wbs         = $request->code_wbs;
					$requisition->code_edt         = $request->code_edt;
					$requisition->requisition_type = $request->requisition_type;
					$requisition->buy_rent         = $request->buy_rent;
					$requisition->validity         = $request->validity;
					$requisition->generated_number = $generatedRequisitionNumber;
					$requisition->save();
					$idRequisition 					= $requisition->id;
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}
					if (isset($request->quantity) && count($request->quantity)>0) 
					{
						$detail_exist = App\RequisitionDetail::where('idRequisition',$idRequisition)->count();
						for ($i=0; $i < count($request->quantity); $i++) 	
						{
							$detail					= new App\RequisitionDetail();
							$detail->part           = ($detail_exist + 1 + $i);
							$detail->unit           = $request->unit[$i];
							$detail->name           = $request->name[$i];
							$detail->quantity		= $request->quantity[$i];
							$detail->description	= $request->description[$i];
							if($request->requisition_type == 2)
							{
								$detail->period		= $request->period[$i];
								$detail->category	= $request->category[$i];
							}
							$detail->idRequisition	= $idRequisition;
							$detail->save();
						}
					}

					$count = 1;
					foreach($t_request->requisition->details as $detail)
					{
						$detail->part	= $count;
						$detail->save();
						$count++;
					}

					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $idRequisition;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					break;
				
				default:
					# code...
					break;
			}

			if($generatedRequisitionNumber != '')
			{
				$alert = "swal('', 'Requisición enviada exitosamente con el número:\\n ".$generatedRequisitionNumber."', 'success');";
			}
			else
			{
				$alert = "swal('', 'Requisición enviada exitosamente', 'success');";
			}
			return redirect()->route('requisition.search')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			if(Auth::user()->globalCheck->where('module_id',230)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',230)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request	!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_request)	: null;
			$maxdate_request = $request->maxdate_request	!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_request)	: null;
			$mindate_obra    = $request->mindate_obra		!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_obra)		: null;
			$maxdate_obra    = $request->maxdate_obra		!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_obra)		: null;
			$status          = $request->status;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$category        = $request->category;
			$employee 		 = $request->employee;
			$data				= App\Module::find($this->module_id);

			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->where('status','!=',23)
				->where(function($query)
				{
					$query->whereIn('idProject',Auth::user()->inChargeProject(230)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$category,$employee)
				{
					if ($employee != "") 
					{
						$query->whereHas('requisition.employees',function($q) use($employee)
						{
							$q->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
						});
					}
					if ($category != "") 
					{
						$query->whereHas('requisition.details',function($q) use($category)
						{
							$q->whereIn('category',$category);
						});
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($project_request != "") 
					{
						$query->whereIn('request_models.idProject',$project_request);
					}
					if ($number != "") 
					{
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);

			return view('administracion.requisicion.busqueda_seguimiento',
					[
						'id'              => $data['father'],
						'title'           => $data['name'],
						'details'         => $data['details'],
						'child_id'        => $this->module_id,
						'option_id'       => 230,
						'requests'        => $requests,
						'mindate_obra'    => $request->mindate_obra,
						'maxdate_obra'    => $request->maxdate_obra,
						'mindate_request' => $request->mindate_request,
						'maxdate_request' => $request->maxdate_request,
						'folio'           => $folio,
						'status'          => $status,
						'title_request'   => $title_request,
						'user_request'    => $user_request,
						'project_request' => $project_request,
						'number'          => $number,
						'wbs'             => $wbs,
						'edt'             => $edt,
						'type'            => $type,
						'category'		  => $category,
						'employee'		  => $employee
					]
				);
		}
		else
		{
			return redirect('/error');
		}
	}
	
	public function sheetExcel($excel,$nameSheet,array $header,array $content)
	{
		$excel->sheet($nameSheet,function($sheet) use ($header,$content)
		{
			//Juntando celdas del encabezado A Y B
			//$datos['encabezado']['contenido']['formato']['me']

			$sheet->mergeCells('A1:L1');
			//Estilos del Excel 
			$sheet->setStyle([
				'font' => [
					'name'	=> 'Calibri',
					'size'	=> 12
				],
				'alignment' => [
					'vertical' => 'center',
				]
			]);

			//Estilos del encabezado A
			$sheet->cell('A1:L2', function($cells){
				$cells->setBackground('#000000');
				$cells->setFontColor('#ffffff');
				$cells->setFontWeight('bold');
				$cells->setAlignment('center');
				$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
			});

			//Encabezado A y B
			$sheet->row(1,$header[0]);
			$sheet->row(2,$header[1]);
			
			foreach($content as $item)
			{
				$sheet->appendRow($item['row']);
				if($item['mixes']!=null)
				{
					$sheet->setMergeColumn(array(
						'columns' => array('A','B','C','D','E','F','G','H','I','J','K'),
						'rows' => array($item['mixes']),
					));
				}
			}

			$sheet->cell('A3:N'.(3+count($content)),function($cell)
			{
				$cell->setAlignment('justify');
			});
		});
		return $excel;
	}

	public function sheetExcelNested($excel,$nameSheet,array $header,array $content,$typeRequisition=null)
	{
		$excel->sheet($nameSheet,function($sheet) use ($header,$content,$typeRequisition)
		{
			//Juntando celdas del encabezado A Y B
			//$datos['encabezado']['contenido']['formato']['me']
			if($typeRequisition=="material")
			{
				$limite='T';
			}
			else
			{
				$limite='N';
			}

			$sheet->mergeCells('A1:K1');
			$sheet->mergeCells('L1:'.$limite.'1');

			//Estilos del Excel 
			$sheet->setStyle([
				'font' => [
					'name'	=> 'Calibri',
					'size'	=> 12
				],
				'alignment' => [
					'vertical' => 'center',
				]
			]);

			//Estilos del encabezado A
			$sheet->cell('A1:K2', function($cells)
			{
				$cells->setBackground('#000000');
				$cells->setFontColor('#ffffff');
				$cells->setFontWeight('bold');
				$cells->setAlignment('center');
				$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
			});

			//Estilos del encabezado B
			$sheet->cell('L1:'.$limite.'2', function($cells)
			{
				$cells->setBackground('#9B9B9B');
				$cells->setFontColor('#000000'); 
				$cells->setFontWeight('bold');
				$cells->setAlignment('center');
				$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
			});

			//Encabezado A y B
			$sheet->row(1,$header[0]);
			$sheet->row(2,$header[1]);
			
			foreach($content as $item)
			{
				$sheet->appendRow($item['row']);
				if($item['mixes']!=null)
				{
					$sheet->setMergeColumn(array(
						'columns' => array('A','B','C','D','E','F','G','H','I','J','K'),
						'rows' => array($item['mixes']),
					));
				}
			}
			$sheet->cell('A3:'.$limite.''.(3+count($content)),function($cell)
			{//a la hora de convertir en clase el 3 tiene que se una variable relacionada con los encabezados
				$cell->setAlignment('justify');
			});
		});
		return $excel;
	}
	
	public function edit($id)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			$request = App\RequestModel::find($id);
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.requisicion.alta_material',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 230,
						'request'	=> $request
					]);
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function personalDownload(RequestModel $id)
	{
		if($id->status == 2 && ($id->idRequest == Auth::user()->id || $id->idElaborate == Auth::user()->id) && $id->requisition->requisition_type == '03' && in_array($id->idProject,[124,126]) && $id->requisition->employees->count() > 0)
		{
			#return view('administracion.requisicion.personal_pdf',['request' => $id]);
			$pdf = PDF::loadView('administracion.requisicion.personal_pdf',['request' => $id]);
			return $pdf->download('requisicion_personal_'.$id->folio.'.pdf');
		}
		else
		{
			return abort(404);
		}
	}

	public function individualDocumentDownload(App\RequisitionEmployee $employee)
	{
		$pdf = PDF::loadView('administracion.requisicion.personal_pdf',['employee' => $employee]);
		return $pdf->download('requisicion_personal_'.$employee->name.'.pdf');
	}

	public function delete($id)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			if(Auth::user()->globalCheck->where('module_id',230)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',230)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			
			$request = App\RequestModel::where('folio',$id)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)
							->orWhere('idRequest',Auth::user()->id);
					}
				})
				->first();
			if($request != "")
			{
				$request->status = 23;
				$request->save();
				$alert	= "swal('', 'Solicitud eliminada exitosamente', 'success');";
				return back()->with('alert',$alert);
			}
			else
			{
				return abort(404);
			}
		}
	}

	public function cancel($id)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			if(Auth::user()->globalCheck->where('module_id',230)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',230)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$request = App\RequestModel::where('folio',$id)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)
							->orWhere('idRequest',Auth::user()->id);
					}
				})
				->first();
			if($request != "") 
			{
				$request->status = 28;
				$request->idCancelled = Auth::user()->id;
				$request->save();
				$alert	= "swal('', 'Solicitud cancelada exitosamente', 'success');";
				return back()->with('alert',$alert);
			}
			else
			{
				return abort(404);
			}
		}
	}

	public function review(Request $request)
	{
		if (Auth::user()->module->where('id',231)->count()>0) 
		{
			$data            = App\Module::find($this->module_id);
			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_request) : null;
			$maxdate_request = $request->maxdate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_request) : null;
			$mindate_obra    = $request->mindate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_obra) : null;
			$maxdate_obra    = $request->maxdate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_obra) : null;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$status			 = $request->status;
			$employee		 = $request->employee;
			if($status == '')
			{
				$status = 0;
			}
			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
				->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$employee)
				{
					if ($employee != "") 
					{
						$query->whereHas('requisition.employees',function($q) use($employee)
						{
							$q->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
						});
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "" && $maxdate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
					}
					if ($mindate_obra != "" && $maxdate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if ($project_request != "") 
					{
						$query->whereIn('request_models.idProject',$project_request);
					}
					if ($number != "") 
					{
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}	
					if($status == 0)
					{
						$query->where('request_models.status',3);
					}
					else
					{
						$query->whereNotIn('request_models.status',[2,3])
							->where('request_models.idCheck',Auth::user()->id);
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);

			return response(
				view('administracion.requisicion.busqueda_revision',
					[
						'id'              => $data['father'],
						'title'           => $data['name'],
						'details'         => $data['details'],
						'child_id'        => $this->module_id,
						'option_id'       => 231,
						'requests'        => $requests,
						'mindate_obra'    => $request->mindate_obra,
						'maxdate_obra'    => $request->maxdate_obra,
						'mindate_request' => $request->mindate_request,
						'maxdate_request' => $request->maxdate_request,
						'folio'           => $folio,
						'title_request'   => $title_request,
						'user_request'    => $user_request,
						'project_request' => $project_request,
						'number'          => $number,
						'wbs'             => $wbs,
						'edt'             => $edt,
						'type'            => $type,
						'status'		  => $status,
						'employee'		  => $employee,
						
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(231), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function Reviewedit($id)
	{
		if (Auth::user()->module->where('id',231)->count()>0) 
		{
			$request = App\RequestModel::find($id);
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.requisicion.alta_material',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 231,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function showReview($id)
	{
		if (Auth::user()->module->where('id',231)->count()>0) 
		{
			$request = App\RequestModel::where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
				->where('request_models.status',3)->find($id);

			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				switch ($request->status) 
				{
					case 3:
						return view('administracion.requisicion.editar_revision',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 231,
								'request'	=> $request
							]
						);
						break;

					default:
						break;
				}
				
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición o ya pasó por el proceso de Compras Locales', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}
	

	public function uploadDetails(Request $request)
	{
		if (Auth::user()->module->where('id',229)->count() > 0)
		{ 
				switch ($request->requisition_type) 
				{
					case 1:
					case 5:
						$t_request              = new App\RequestModel();
						$t_request->fDate       = Carbon::now();
						$t_request->idElaborate = Auth::user()->id;
						$t_request->idRequest   = $request->request_requisition;
						$t_request->code_wbs    = $request->code_wbs;
						$t_request->code_edt    = $request->code_edt;
						$t_request->status      = 2;
						$t_request->kind        = 19;
						$t_request->idProject   = $request->project_id;
						$t_request->save();

						$requisition					= new App\Requisition();
						$requisition->title				= $request->title;
						$requisition->date_request		= Carbon::now();
						$requisition->date_comparation	= $request->date_comparation;
						$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
						$requisition->idFolio			= $t_request->folio;
						$requisition->idKind			= $t_request->kind;
						$requisition->requisition_type	= $request->requisition_type;
						$requisition->code_wbs			= $request->code_wbs;
						$requisition->code_edt			= $request->code_edt;
						$requisition->buy_rent			= $request->buy_rent;
						$requisition->validity			= $request->validity;
						$requisition->save();
						$idRequisition = $requisition->id;

						if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
						{
							for ($i=0; $i < count($request->realPathRequisition); $i++) 
							{
								if ($request->realPathRequisition[$i] != "") 
								{
									$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
									$documents					= new App\RequisitionDocuments();
									$documents->name			= $request->nameDocumentRequisition[$i];
									$documents->ticket_number	= $request->ticket_number[$i];
									$documents->fiscal_folio	= $request->fiscal_folio[$i];
									$documents->timepath		= $request->timepath[$i];
									$documents->amount			= $request->amount[$i];
									$documents->datepath		= $request->datepath[$i];
									$documents->path			= $new_file_name;
									$documents->idRequisition	= $idRequisition;
									$documents->user_id			= Auth::user()->id;
									$documents->save();
								}
							}
						}

						if ($t_request->requisition->details()->exists()) 
						{
							$countDetail = $t_request->requisition->details->count();
						}
						else
						{
							$countDetail = 0;
						}

						$errors = 0;
						if ($request->requisition_type == 1) 
						{
							if(isset($request) && $request->csv_file_material != "" && $request->file('csv_file_material')->isValid())
							{
								$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_material')->getClientOriginalExtension();
								\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_material')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
								$path		= \Storage::disk('reserved')->path($name);
								$csvArr		= array();
								if (($handle = fopen($path, "r")) !== FALSE)
								{
									$first	= true;
									while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
									{
										if($first)
										{
											$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
											$first		= false;
										}
										$csvArr[]	= $data;
									}
									fclose($handle);
								}
								array_walk($csvArr, function(&$a) use ($csvArr)
								{
									$a = array_combine($csvArr[0], $a);
								});
								array_shift($csvArr);

								$countRows = 0;
								foreach ($csvArr as $art) 
								{
									$flag = true;
									if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
										(isset($art['nombre']) && trim($art['nombre'])!="") && 
										(isset($art['unidad']) && trim($art['unidad'])!="") && 
										(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
										(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
									{
										$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
										if(!$c_r)
										{
											$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
										} 
										
										
										if(isset($art['categoria']) && trim($art['categoria']) == "Material de Procura")
										{
											if (isset($art['tipo']) && trim($art['tipo']) != "") 
											{
												$check_type = App\CatProcurementMaterial::where('name','LIKE','%'.$art['tipo'].'%')->first();
												if ($check_type != "") 
												{
													$type_id = $check_type->id;
												}
												else
												{
													$flag = false;
												}
											}
											else
											{
												$flag = false;
											}
										}
										else
										{
											$type_id = null;
										}

										$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
										if ($check_category != "" && $flag) 
										{
											$category_id = $check_category->id;
											$detail									= new App\RequisitionDetail();
											$detail->category						= $category_id;
											$detail->cat_procurement_material_id	= $type_id;
											$detail->part							= ($countDetail+$countRows+1);
											$detail->quantity						= $art['cantidad'];
											$detail->unit							= $art['unidad'];
											$detail->name							= $c_r->name;
											$detail->measurement					= $art['medida'];
											$detail->description					= $art['descripcion'];
											// $detail->exists_warehouse				= $art['existencia_almacen'];
											$detail->exists_warehouse				= 0;
											$detail->idRequisition					= $idRequisition;
											$detail->save();

											$countRows++;
										}
										else
										{
											$errors++;
										}

									}
									else
									{
										$errors++;
									}
								}
							}
						}
						elseif ($request->requisition_type == 5) 
						{
							if(isset($request) && $request->csv_file_machine != "" && $request->file('csv_file_machine')->isValid())
							{
								$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_machine')->getClientOriginalExtension();
								\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_machine')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
								$path		= \Storage::disk('reserved')->path($name);
								$csvArr		= array();
								if (($handle = fopen($path, "r")) !== FALSE)
								{
									$first	= true;
									while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
									{
										if($first)
										{
											$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
											$first		= false;
										}
										$csvArr[]	= $data;
									}
									fclose($handle);
								}
								array_walk($csvArr, function(&$a) use ($csvArr)
								{
									$a = array_combine($csvArr[0], $a);
								});
								array_shift($csvArr);

								$countRows = 0;
								foreach ($csvArr as $art) 
								{
									if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
										(isset($art['nombre']) && trim($art['nombre'])!="") && 
										(isset($art['unidad']) && trim($art['unidad'])!="") && 
										(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
										(isset($art['marca']) && trim($art['marca'])!="") && 
										(isset($art['modelo']) && trim($art['modelo'])!="") && 
										(isset($art['tiempo_utilizacion']) && trim($art['tiempo_utilizacion'])!="") && 
										(isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!=""))
									{
										$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
										if(!$c_r)
										{
											$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
										} 
										$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
										if ($check_category != "") 
										{
											$detail						= new App\RequisitionDetail();
											$detail->category			= $check_category->id;
											$detail->part				= ($countDetail+$countRows+1);
											$detail->quantity			= $art['cantidad'];
											$detail->unit				= $art['unidad'];
											$detail->name				= $c_r->name;
											$detail->measurement		= $art['medida'];
											$detail->description		= $art['descripcion'];
											$detail->brand				= $art['marca'];
											$detail->model				= $art['modelo'];
											$detail->usage_time			= $art['tiempo_utilizacion'];
											$detail->exists_warehouse	= $art['existencia_almacen'];
											$detail->idRequisition		= $idRequisition;
											$detail->save();
											$countRows++;
										}
										else
										{
											$errors++;
										}
									}
									else
									{
										$errors++;
									}
									
								}
							}
						}
						break;

					case 2:
					case 4:
					case 6:
						$t_request              = new App\RequestModel();
						$t_request->fDate       = Carbon::now();
						$t_request->idElaborate = Auth::user()->id;
						$t_request->idRequest   = $request->request_requisition;
						$t_request->code_wbs    = $request->code_wbs;
						$t_request->code_edt    = $request->code_edt;
						$t_request->status      = 2;
						$t_request->kind        = 19;
						$t_request->idProject   = $request->project_id;
						$t_request->save();

						$requisition					= new App\Requisition();
						$requisition->title				= $request->title;
						$requisition->date_request		= Carbon::now();
						$requisition->date_comparation	= $request->date_comparation;
						$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
						$requisition->idFolio			= $t_request->folio;
						$requisition->idKind			= $t_request->kind;
						$requisition->requisition_type	= $request->requisition_type;
						$requisition->code_wbs			= $request->code_wbs;
						$requisition->code_edt			= $request->code_edt;
						$requisition->buy_rent			= $request->buy_rent;
						$requisition->validity			= $request->validity;
						$requisition->save();
						$idRequisition = $requisition->id;

						if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
						{
							for ($i=0; $i < count($request->realPathRequisition); $i++) 
							{
								if ($request->realPathRequisition[$i] != "") 
								{
									$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
									$documents					= new App\RequisitionDocuments();
									$documents->name			= $request->nameDocumentRequisition[$i];
									$documents->ticket_number	= $request->ticket_number[$i];
									$documents->fiscal_folio	= $request->fiscal_folio[$i];
									$documents->timepath		= $request->timepath[$i];
									$documents->amount			= $request->amount[$i];
									$documents->datepath		= $request->datepath[$i];
									$documents->path			= $new_file_name;
									$documents->idRequisition	= $idRequisition;
									$documents->user_id			= Auth::user()->id;
									$documents->save();
								}
							}
						}

						if ($t_request->requisition->details()->exists()) 
						{
							$countDetail = $t_request->requisition->details->count();
						}
						else
						{
							$countDetail = 0;
						}

						$errors = 0;
						if ($request->requisition_type == 2) 
						{
							if(isset($request) && $request->csv_file_service != "" && $request->file('csv_file_service')->isValid())
							{
								$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_service')->getClientOriginalExtension();
								\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_service')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
								$path		= \Storage::disk('reserved')->path($name);
								$csvArr		= array();
								if (($handle = fopen($path, "r")) !== FALSE)
								{
									$first	= true;
									while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
									{
										if($first)
										{
											$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
											$first		= false;
										}
										$csvArr[]	= $data;
									}
									fclose($handle);
								}
								array_walk($csvArr, function(&$a) use ($csvArr)
								{
									$a = array_combine($csvArr[0], $a);
								});
								array_shift($csvArr);

								$countRows = 0;
								
								foreach ($csvArr as $art) 
								{
								
									if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
										(isset($art['nombre']) && trim($art['nombre'])!="") && 
										(isset($art['unidad']) && trim($art['unidad'])!="") && 
										(isset($art['descripcion']) && trim($art['descripcion'])!="") && 
										(isset($art['periodo']) && trim($art['periodo'])!=""))
									{
										$check_category 	= App\CatWarehouseType::where('description','like','%'.$art['categoria'].'%')->first();
										if ($check_category != "") 
										{	
											$detail						= new App\RequisitionDetail();
											$detail->category			= $check_category->id;
											$detail->part				= ($countDetail+$countRows+1);
											$detail->quantity			= $art['cantidad'];
											$detail->unit				= $art['unidad'];
											$detail->name				= $art['nombre'];
											$detail->measurement		= $art['medida'];
											$detail->description		= $art['descripcion'];
											$detail->exists_warehouse	= $art['existencia_almacen'];
											$detail->idRequisition		= $idRequisition;
											$detail->save();

											$countRows++;
										}
										else
										{
											$errors++;
										}
									}
									else
									{
										$errors++;
									}
								}
							}
								
						}
						elseif ($request->requisition_type == 4) 
						{
							if(isset($request) && $request->csv_file_subcontract != "" && $request->file('csv_file_subcontract')->isValid())
							{
								$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_subcontract')->getClientOriginalExtension();
								\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_subcontract')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
								$path		= \Storage::disk('reserved')->path($name);
								$csvArr		= array();
								if (($handle = fopen($path, "r")) !== FALSE)
								{
									$first	= true;
									while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
									{
										if($first)
										{
											$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
											$first		= false;
										}
										$csvArr[]	= $data;
									}
									fclose($handle);
								}
								array_walk($csvArr, function(&$a) use ($csvArr)
								{
									$a = array_combine($csvArr[0], $a);
								});
								array_shift($csvArr);

								$countRows = 0;

								foreach ($csvArr as $art) 
								{
									if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
										(isset($art['nombre']) && trim($art['nombre'])!="") && 
										(isset($art['unidad']) && trim($art['unidad'])!="") && 
										(isset($art['descripcion']) && trim($art['descripcion'])!=""))
									{
										$detail						= new App\RequisitionDetail();
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $art['nombre'];
										$detail->description		= $art['descripcion'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();
										$countRows ++;
									}
									else
									{
										$errors++;
									}
									
								}

							}
						}
						elseif ($request->requisition_type == 6) 
						{
							if(isset($request) && $request->csv_file_comercial != "" && $request->file('csv_file_comercial')->isValid())
							{
								$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_comercial')->getClientOriginalExtension();
								\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_comercial')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
								$path		= \Storage::disk('reserved')->path($name);
								$csvArr		= array();
								if (($handle = fopen($path, "r")) !== FALSE)
								{
									$first	= true;
									while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
									{
										if($first)
										{
											$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
											$first		= false;
										}
										$csvArr[]	= $data;
									}
									fclose($handle);
								}
								array_walk($csvArr, function(&$a) use ($csvArr)
								{
									$a = array_combine($csvArr[0], $a);
								});
								array_shift($csvArr);

								$countRows = 0;

								foreach ($csvArr as $art) 
								{
									if ((isset($art['cantidad']) && trim($art['cantidad'])>0) && 
										(isset($art['nombre']) && trim($art['nombre'])!="") && 
										(isset($art['unidad']) && trim($art['unidad'])!="") && 
										(isset($art['descripcion']) && trim($art['descripcion'])!=""))
									{
										$detail						= new App\RequisitionDetail();
										$detail->part				= ($countDetail+$countRows+1);
										$detail->quantity			= $art['cantidad'];
										$detail->unit				= $art['unidad'];
										$detail->name				= $art['nombre'];
										$detail->description		= $art['descripcion'];
										$detail->idRequisition		= $idRequisition;
										$detail->save();
										$countRows ++;
									}
									else
									{
										$errors++;
									}
									
								}

							}
						}
						break;
					
					case 3:
						$t_request              = new App\RequestModel();
						$t_request->fDate       = Carbon::now();
						$t_request->idElaborate = Auth::user()->id;
						$t_request->idRequest   = $request->request_requisition;
						$t_request->code_wbs    = $request->code_wbs;
						$t_request->code_edt    = $request->code_edt;
						$t_request->status      = 2;
						$t_request->kind        = 19;
						$t_request->idProject   = $request->project_id;
						$t_request->save();

						$count	= App\RequestModel::where('kind',19)
								->where('idProject',$request->project_id)
								->count();

						$number	= $count + 1;

						$requisition					= new App\Requisition();
						$requisition->title				= $request->title;
						$requisition->date_request		= Carbon::now();
						$requisition->date_comparation	= $request->date_comparation;
						$requisition->date_obra			= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
						$requisition->idFolio			= $t_request->folio;
						$requisition->idKind			= $t_request->kind;
						$requisition->requisition_type	= $request->requisition_type;
						$requisition->code_wbs			= $request->code_wbs;
						$requisition->code_edt			= $request->code_edt;
						$requisition->buy_rent			= $request->buy_rent;
						$requisition->validity			= $request->validity;
						$requisition->urgent           = $request->urgent;
						$requisition->save();
						$idRequisition = $requisition->id;

						$errors = 0;
						
						if(($request->csv_file_personal != "" || !empty($request->csv_file_personal)))
						{
							$valid = $request->file('csv_file_personal')->getClientOriginalExtension();
						
							if($valid != 'csv')
							{
								$alert	= "swal('', 'El archivo que intenta cargar no corresponde a la extensión CSV, favor de verificar su información.', 'error');";
								return back()->with('alert',$alert);	
							}
							
							$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file_personal')->getClientOriginalExtension();
							\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file_personal')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
							$path		= \Storage::disk('reserved')->path($name);
							$csvArr		= array();
							if (($handle = fopen($path, "r")) !== FALSE)
							{
								$first	= true;
								while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
								{
									if($first)
									{
										$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
										$first		= false;
									}
									$csvArr[]	= $data;
								}
								fclose($handle);
							}
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});

							$headers = [
								'nombre',
								'apellido',
								'apellido2',
								'curp',
								'rfc',
								'email',
								'regimen_fiscal',
								'imss',
								'calle',
								'numero',
								'colonia',
								'cp',
								'ciudad',
								'estado',
								'personal_calificado',
								'en_reemplazo_de',
								'proposito_del_puesto',
								'requerimientos_del_puesto',
								'observaciones',
								'requiere_equipo_de_computo',
								'estado_laboral',
								'empresa',
								'clasificacion_gasto',
								'lugar_trabajo',
								'direccion',
								'departamento',
								'subdepartamento',
								'puesto',
								'jefe_inmediato',
								'posisicion_de_jefe_inmediato',
								'estatus_imss',
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
								'viaticos',
								'campamento',
								'sueldo_neto',
								'complemento',
								'fonacot',
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
	
							if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
							{
								$alert	= "swal('', 'Ocurrió un error al cargar el archivo, por favor verifique su información.', 'error');";
								return back()->with('alert',$alert);	
							}

							array_shift($csvArr);

							$countRows = 0;
							
							foreach ($csvArr as $e) 
							{
								if (isset($e['curp']) && trim($e['curp']) != "" ) 
								{
									$check_employee 	= App\RealEmployee::where('curp',trim($e['curp']))->count();
									$check_requisition 	= App\RequisitionEmployee::leftJoin('requisitions','requisition_employees.requisition_id','requisitions.id')
														->leftJoin('request_models','requisitions.idFolio','request_models.folio')
														->select('requisition_employees.curp')
														->whereNotIn('request_models.status',[5,6,7,23,28])
														->where('requisition_employees.curp',trim($e['curp']))
														->count();
								}
								else
								{
									$check_employee		= 1;
									$check_requisition	= 1;
								}

								if ((isset($e['nombre']) && trim($e['nombre']) != "")
									&& (isset($e['apellido']) && trim($e['apellido']) != "")
									&& (isset($e['curp']) && trim($e['curp']) != "" && $check_employee == 0 && $check_requisition == 0)
									&& (isset($e['calle']) && trim($e['calle']) != "")
									&& (isset($e['numero']) && trim($e['numero']) != "")
									&& (isset($e['colonia']) && trim($e['colonia']) != "")
									&& (isset($e['cp']) && trim($e['cp']) != "" && App\CatZipCode::where('zip_code',trim($e['cp']))->count()>0)
									&& (isset($e['ciudad']) && trim($e['ciudad']) != "")
									&& (isset($e['estado']) && trim($e['estado']) != "" && App\State::where('idstate',trim($e['estado']))->count()>0)
									&& (isset($e['estado_laboral']) && trim($e['estado_laboral']) != "" && App\State::where('idstate',trim($e['estado_laboral']))->count()>0)
									&& (isset($e['empresa']) && trim($e['empresa']) != "" && App\Enterprise::where('id',trim($e['empresa']))->count()>0)
									&& (isset($e['clasificacion_gasto']) && trim($e['clasificacion_gasto']) != "" && App\Account::where('idAccAcc',trim($e['clasificacion_gasto']))->count()>0)
									&& (isset($e['direccion']) && trim($e['direccion']) != "" && App\Area::where('id',trim($e['direccion']))->count()>0)
									&& (isset($e['puesto']) && trim($e['puesto']) != "")
									&& (isset($e['fecha_ingreso']) && trim($e['fecha_ingreso']) != "" && DateTime::createFromFormat('Y-m-d', trim($e['fecha_ingreso'])) !== false)
									&& (isset($e['tipo_contrato']) && trim($e['tipo_contrato']) != "" && App\CatContractType::where('id',trim($e['tipo_contrato']))->count()>0)
									&& (isset($e['regimen']) && trim($e['regimen']) != "" && App\CatRegimeType::where('id',trim($e['regimen']))->count()>0)
									&& (isset($e['periodicidad']) && trim($e['periodicidad']) != "" && App\CatPeriodicity::where('c_periodicity',trim($e['periodicidad']))->count()>0)
									&& (isset($e['registro_patronal']) && trim($e['registro_patronal']) != "" && App\EmployerRegister::where('employer_register',trim($e['registro_patronal']))->count()>0)
									&& (isset($e['forma_pago']) && trim($e['forma_pago']) != "" && App\PaymentMethod::where('idpaymentMethod',trim($e['forma_pago']))->count()>0)
									&& (isset($e['requiere_equipo_de_computo']) && trim($e['requiere_equipo_de_computo']) != "" && in_array(trim($e['requiere_equipo_de_computo']),[0,1]))
									&& (isset($e['regimen_fiscal']) && trim($e['regimen_fiscal']) != "" && App\CatTaxRegime::where('taxRegime',trim($e['regimen_fiscal']))->count()>0)
									&& (isset($e['personal_calificado']) && trim($e['personal_calificado']) != "" && in_array(trim($e['personal_calificado']),[0,1]))
								)
								{
									$employee							= new App\RequisitionEmployee();
									$employee->name						= isset($e['nombre']) && $e['nombre'] != "" ? $e['nombre'] : null;
									$employee->last_name				= isset($e['apellido']) && $e['apellido'] != "" ? $e['apellido'] : null;
									$employee->scnd_last_name			= isset($e['apellido2']) && $e['apellido2'] != "" ? $e['apellido2'] : null;
									$employee->email					= isset($e['email']) && $e['email'] != "" ? $e['email'] : null;
									$employee->curp						= isset($e['curp']) && $e['curp'] != "" ? $e['curp'] : null;
									$employee->rfc						= isset($e['rfc']) && $e['rfc'] != "" ? $e['rfc'] : null;
									$employee->tax_regime				= isset($e['regimen_fiscal']) && $e['regimen_fiscal'] != "" ? $e['regimen_fiscal'] : null;
									$employee->imss						= isset($e['imss']) && $e['imss'] != "" ? $e['imss'] : null;
									$employee->street					= isset($e['calle']) && $e['calle'] != "" ? $e['calle'] : null;
									$employee->number					= isset($e['numero']) && $e['numero'] != "" ? $e['numero'] : null;
									$employee->colony					= isset($e['colonia']) && $e['colonia'] != "" ? $e['colonia'] : null;
									$employee->cp						= isset($e['cp']) && $e['cp'] != "" ? $e['cp'] : null;
									$employee->city						= isset($e['ciudad']) && $e['ciudad'] != "" ? $e['ciudad'] : null;
									$employee->state_id					= isset($e['estado']) && $e['estado'] != "" ? $e['estado'] : null;
									$employee->state					= isset($e['estado_laboral']) && $e['estado_laboral'] != "" ? $e['estado_laboral'] : null;
									$employee->project					= $request->project_id;
									$employee->enterprise				= isset($e['empresa']) && $e['empresa'] != "" ? $e['empresa'] : null;
									$employee->account					= isset($e['clasificacion_gasto']) && $e['clasificacion_gasto'] != "" ? $e['clasificacion_gasto'] : null;
									$employee->direction				= isset($e['direccion']) && $e['direccion'] != "" ? $e['direccion'] : null;
									$employee->department				= isset($e['departamento']) && $e['departamento'] != "" ? $e['departamento'] : null;
									$employee->position					= isset($e['puesto']) && $e['puesto'] != "" ? $e['puesto'] : null;
									$employee->immediate_boss			= isset($e['jefe_inmediato']) && $e['jefe_inmediato'] != "" ? $e['jefe_inmediato'] : null;
									$employee->admissionDate			= isset($e['fecha_ingreso']) && $e['fecha_ingreso'] != "" ? $e['fecha_ingreso'] : null;
									$employee->imssDate					= isset($e['fecha_alta']) && $e['fecha_alta'] != "" ? $e['fecha_alta'] : null;
									$employee->downDate					= isset($e['fecha_baja']) && $e['fecha_baja'] != "" ? $e['fecha_baja'] : null;
									$employee->endingDate				= isset($e['fecha_termino']) && $e['fecha_termino'] != "" ? $e['fecha_termino'] : null;
									$employee->reentryDate				= isset($e['fecha_reingreso']) && $e['fecha_reingreso'] != "" ? $e['fecha_reingreso'] : null;
									$employee->workerType				= isset($e['tipo_contrato']) && $e['tipo_contrato'] != "" ? $e['tipo_contrato'] : null;
									$employee->regime_id				= isset($e['regimen']) && $e['regimen'] != "" ? $e['regimen'] : null;
									$employee->workerStatus				= isset($e['estatus']) && $e['estatus'] != "" ? $e['estatus'] : null;
									$employee->status_imss				= isset($e['estatus_imss']) && $e['estatus_imss'] != "" ? $e['estatus_imss'] : null;
									$employee->status_reason			= isset($e['razon_estatus']) && $e['razon_estatus'] != "" ? $e['razon_estatus'] : null;
									$employee->sdi						= isset($e['sdi']) && $e['sdi'] != "" ? $e['sdi'] : null;
									$employee->periodicity				= isset($e['periodicidad']) && $e['periodicidad'] != "" ? $e['periodicidad'] : null;
									$employee->employer_register		= isset($e['registro_patronal']) && $e['registro_patronal'] != "" ? $e['registro_patronal'] : null;
									$employee->paymentWay				= isset($e['forma_pago']) && $e['forma_pago'] != "" ? $e['forma_pago'] : null;
									$employee->netIncome				= isset($e['sueldo_neto']) && $e['sueldo_neto'] != "" ? $e['sueldo_neto'] : null;
									$employee->complement				= isset($e['complemento']) && $e['complemento'] != "" ? $e['complemento'] : null;
									$employee->fonacot					= isset($e['fonacot']) && $e['fonacot'] != "" ? $e['fonacot'] : null;
									$employee->nomina					= isset($e['porcentaje_nomina']) && $e['porcentaje_nomina'] != "" ? $e['porcentaje_nomina'] : null;
									$employee->bono						= isset($e['porcentaje_bono']) && $e['porcentaje_bono'] != "" ? $e['porcentaje_bono'] : null;
									$employee->infonavitCredit			= isset($e['credito_infonavit']) && $e['credito_infonavit'] != "" ? $e['credito_infonavit'] : null;
									$employee->infonavitDiscount		= isset($e['descuento_infonavit']) && $e['descuento_infonavit'] != "" ? $e['descuento_infonavit'] : null;
									$employee->infonavitDiscountType	= isset($e['tipo_descuento_infonavit']) && $e['tipo_descuento_infonavit'] != "" ? $e['tipo_descuento_infonavit'] : null;
									$employee->wbs_id					= $request->code_wbs;
									$employee->subdepartment_id			= isset($e['subdepartamento']) && $e['subdepartamento'] != "" ? $e['subdepartamento'] : null;
									$employee->viatics					= isset($e['viaticos']) && $e['viaticos'] != "" ? $e['viaticos'] : null;
									$employee->camping					= isset($e['campamento']) && $e['campamento'] != "" ? $e['campamento'] : null;
									$employee->replace					= isset($e['en_reemplazo_de']) && $e['en_reemplazo_de'] != "" ? $e['en_reemplazo_de'] : null;
									$employee->purpose					= isset($e['proposito_del_puesto']) && $e['proposito_del_puesto'] != "" ? $e['proposito_del_puesto'] : null;
									$employee->requeriments				= isset($e['requerimientos_del_puesto']) && $e['requerimientos_del_puesto'] != "" ? $e['requerimientos_del_puesto'] : null;
									$employee->observations				= isset($e['observaciones']) && $e['observaciones'] != "" ? $e['observaciones'] : null;
									$employee->position_immediate_boss	= isset($e['posisicion_de_jefe_inmediato']) && $e['posisicion_de_jefe_inmediato'] != "" ? $e['posisicion_de_jefe_inmediato'] : null;
									$employee->computer_required		= isset($e['requiere_equipo_de_computo']) && $e['requiere_equipo_de_computo'] != "" ? $e['requiere_equipo_de_computo'] : null;
									$employee->qualified_employee		= isset($e['personal_calificado']) && $e['personal_calificado'] != "" ? $e['personal_calificado'] : null;
									$employee->requisition_id			= $idRequisition;
									$employee->save();

									
									if(isset($e['alias']) && isset($e['banco']) && $e['alias']!='' && $e['banco']!='' && App\CatBank::where('c_bank',$e['banco'])->count()>0)
									{
										$empAcc             = new App\RequisitionEmployeeAccount();
										$empAcc->idEmployee = $employee->id;
										$empAcc->alias      = empty(trim($e['alias'])) ? null : $e['alias'];
										$empAcc->clabe      = empty(trim($e['clabe'])) ? null : $e['clabe'];
										$empAcc->account    = empty(trim($e['cuenta'])) ? null : $e['cuenta'];
										$empAcc->cardNumber = empty(trim($e['tarjeta'])) ? null : $e['tarjeta'];
										$empAcc->idCatBank  = empty(trim($e['banco'])) ? null : $e['banco'];
										$empAcc->recorder   = Auth::user()->id;
										$empAcc->type       = 1;
										$empAcc->save();
									}

									$countRows++;
								}
								else
								{
									$errors++;
								}
							}
						}

						if (isset($request->rq_name) && count($request->rq_name)) 
						{
							for ($i=0; $i < count($request->rq_name); $i++) 
							{ 
								$employee							= new App\RequisitionEmployee();
								$employee->name						= $request->rq_name[$i];
								$employee->last_name				= $request->rq_last_name[$i];
								$employee->scnd_last_name			= $request->rq_scnd_last_name[$i];
								$employee->curp						= $request->rq_curp[$i];
								$employee->rfc						= $request->rq_rfc[$i];
								$employee->tax_regime				= $request->rq_tax_regime[$i];
								$employee->imss						= $request->rq_imss[$i];
								$employee->email					= $request->rq_email[$i];
								$employee->street					= $request->rq_street[$i];
								$employee->number					= $request->rq_number_employee[$i];
								$employee->colony					= $request->rq_colony[$i];
								$employee->cp						= $request->rq_cp[$i];
								$employee->city						= $request->rq_city[$i];
								$employee->state_id					= $request->rq_state[$i];
								$employee->state					= $request->rq_work_state[$i];
								$employee->project					= $request->project_id;
								$employee->enterprise				= $request->rq_work_enterprise[$i];
								$employee->account					= $request->rq_work_account[$i];
								$employee->direction				= $request->rq_work_direction[$i];
								$employee->department				= $request->rq_work_department[$i];
								$employee->position					= $request->rq_work_position[$i];
								$employee->immediate_boss			= $request->rq_work_immediate_boss[$i];
								$employee->admissionDate			= $request->rq_work_income_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_income_date[$i])->format('Y-m-d') : null;
								$employee->imssDate					= $request->rq_work_imss_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_imss_date[$i])->format('Y-m-d') : null;
								$employee->downDate					= $request->rq_work_down_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_down_date[$i])->format('Y-m-d') : null;
								$employee->endingDate				= $request->rq_work_ending_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_ending_date[$i])->format('Y-m-d') : null;
								$employee->reentryDate				= $request->rq_work_reentry_date[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->rq_work_reentry_date[$i])->format('Y-m-d') : null;
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
								$employee->doc_requisition			= $request->rq_doc_requisition[$i];
								$employee->computer_required		= $request->rq_computer_required[$i];
								$employee->wbs_id 					= $request->code_wbs;

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
								$employee->requisition_id 		= $idRequisition;
								$employee->qualified_employee 	= $request->rq_qualified_employee[$i];
								$employee->save();

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
										$empAcc              = new App\RequisitionEmployeeAccount();
										$empAcc->idEmployee  = $employee->id;
										$empAcc->beneficiary = $request->$beneficiary[$k];
										$empAcc->type        = $request->$type[$k];
										$empAcc->alias       = $request->$alias[$k];
										$empAcc->clabe       = $request->$clabe[$k];
										$empAcc->account     = $request->$account[$k];
										$empAcc->cardNumber  = $request->$cardNumber[$k];
										$empAcc->idCatBank   = $request->$idCatBank[$k];
										$empAcc->branch      = $request->$branch[$k];
										$empAcc->recorder    = Auth::user()->id;
										$empAcc->save();
									}
								}

								App\RequisitionEmployeeDocument::where('requisition_employee_id',$employee->id)->delete();
								$name_other_document = 'name_other_document_'.$i;
								$path_other_document = 'path_other_document_'.$i;
								if (isset($request->$name_other_document) && count($request->$name_other_document)>0) 
								{
									for ($d=0; $d < count($request->$name_other_document); $d++) 
									{ 
										$checkDoc 	= App\RequisitionEmployeeDocument::where('name',$request->$name_other_document[$d])
													->where('path',$request->$path_other_document[$d])
													->where('requisition_employee_id',$employee->id)
													->count();
										if ($checkDoc == 0) 
										{
											$other							= new App\RequisitionEmployeeDocument();
											$other->name					= $request->$name_other_document[$d];
											$other->path					= $request->$path_other_document[$d];
											$other->requisition_employee_id	= $employee->id;
											$other->save();
										}
									}
								}
							}
						}

						if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
						{
							for ($i=0; $i < count($request->realPathRequisition); $i++) 
							{
								if ($request->realPathRequisition[$i] != "") 
								{
									$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
									$documents					= new App\RequisitionDocuments();
									$documents->name			= $request->nameDocumentRequisition[$i];
									$documents->ticket_number	= $request->ticket_number[$i];
									$documents->fiscal_folio	= $request->fiscal_folio[$i];
									$documents->timepath		= $request->timepath[$i];
									$documents->amount			= $request->amount[$i];
									$documents->datepath		= $request->datepath[$i];
									$documents->path			= $new_file_name;
									$documents->idRequisition	= $idRequisition;
									$documents->user_id			= Auth::user()->id;
									$documents->save();
								}
							}
						}
						break;
					default:
						# code...
						break;
				}
				if ($errors > 0) 
				{
					$alert = "swal('Requisición Guardada Con Errores','".$errors." registros del archivo CSV no fueron guardados, por favor verifique que los datos capturados en el archivo sean correctos.', 'info');";
				}
				else
				{
					$alert = "swal('','Requisición Guardada Exitosamente', 'success');";
				}
				return redirect()->route('requisition.edit',['id'=>$t_request->folio])->with('alert',$alert);
		}
			
	}

	public function storeProviderSecondary(Request $request,$id)
	{
		if (Auth::user()->module->whereIn('id',[231,232])->count()>0)
		{
			$tempRequest = App\RequestModel::find($id);
			if($tempRequest->status == 28)
			{
				$alert	= "swal('', 'La solicitud ha sido cancelada por el usuario', 'error');";
				return redirect()->route('requisition.authorization')->with('alert',$alert);
			}
			elseif($tempRequest->status != 4)
			{
				$alert	= "swal('', 'La solicitud ya no se encuentra en Autorización', 'error');";
				return redirect()->route('requisition.authorization')->with('alert',$alert);
			}
			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
				{
					$commentaries	= 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
					$type_currency	= 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

					$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
					$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
					$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
					$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
					

					$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
					$requisitionHasProvider->commentaries  = $request->$commentaries;
					$requisitionHasProvider->type_currency = $request->$type_currency;

					$requisitionHasProvider->delivery_time = $request->$delivery_time;
					$requisitionHasProvider->credit_time   = $request->$credit_time;
					$requisitionHasProvider->guarantee     = $request->$guarantee;
					$requisitionHasProvider->spare         = $request->$spare;

					$requisitionHasProvider->save();
				}
			}

			if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
					if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
					{
						$detail->exists_warehouse	= $request->exists_warehouse[$ird];
						$detail->save();
					}
				}
			}

			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
					{ 
						$unitPrice					= 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$subtotal					= 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$typeTax					= 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$iva						= 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$total						= 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$idProviderSecondaryPrice	= 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if ($request->$idProviderSecondaryPrice == "x") 
						{
							$providerPrice	= new App\ProviderSecondaryPrice();
						}
						else
						{
							$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
						}
						$providerPrice->unitPrice                = $request->$unitPrice;
						$providerPrice->subtotal                 = $request->$subtotal;
						$providerPrice->typeTax                  = $request->$typeTax;
						$providerPrice->iva                      = $request->$iva;
						$providerPrice->total                    = $request->$total;
						$providerPrice->user_id                  = Auth::user()->id;
						$providerPrice->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
						$providerPrice->idRequisitionHasProvider = $request->idRequisitionHasProvider[$ips];
						$providerPrice->save();

						$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
						{
							$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
						}

						if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_tax); $t++) 
							{ 
								if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
								{
									if ($request->$tax_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
									}
									
									$tax->name						= $request->$name_add_tax[$t];
									$tax->amount					= $request->$amount_add_tax[$t];
									$tax->type						= 1;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}

						if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_ret); $t++) 
							{ 
								if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
								{
									if ($request->$ret_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
									}
									$tax->name						= $request->$name_add_ret[$t];
									$tax->amount					= $request->$amount_add_ret[$t];
									$tax->type						= 2;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}
					}
				}
			}

			if ($request->prov == "buscar") 
			{
				if (isset($request->multiprovider) && count($request->multiprovider) && !isset($request->idProviderBtn)) 
				{
					for ($i=0; $i < count($request->multiprovider); $i++) 
					{ 
						$requisitionHP						= new App\RequisitionHasProvider();
						$requisitionHP->idProviderSecondary	= $request->multiprovider[$i];
						$requisitionHP->idRequisition		= $request->idRequisition;
						$requisitionHP->user_id				= Auth::user()->id;
						$requisitionHP->save();
					}
				}
				else
				{
					if (isset($request->idProviderBtn) && $request->idProviderBtn != "") 
					{
						$requisitionHP						= new App\RequisitionHasProvider();
						$requisitionHP->idProviderSecondary	= $request->idProviderBtn;
						$requisitionHP->idRequisition		= $request->idRequisition;
						$requisitionHP->user_id				= Auth::user()->id;
						$requisitionHP->save();
					}
				}
			}
			else
			{
				$provider 					= new App\ProviderSecondary();
				$provider->businessName		= $request->businessName;
				$provider->rfc				= $request->rfc;
				$provider->phone			= $request->phone;
				$provider->contact			= $request->contact;
				$provider->beneficiary		= $request->beneficiary;
				$provider->commentaries		= $request->commentaries;
				$provider->address			= $request->address;
				$provider->number			= $request->number;
				$provider->colony			= $request->colony;
				$provider->postalCode		= $request->postalCode;
				$provider->city				= $request->city;
				$provider->state_idstate	= $request->state_idstate;
				$provider->users_id			= Auth::user()->id;
				$provider->status 			= 2;
				$provider->save();

				if (isset($request->alias) && count($request->alias)>0) 
				{
					for ($i=0; $i < count($request->alias); $i++) 
					{
						$t_providerBank							= new App\ProviderSecondaryAccounts();
						$t_providerBank->idProviderSecondary	= $provider->id;
						$t_providerBank->idBanks				= $request->idBanks[$i];
						$t_providerBank->alias					= $request->alias[$i];
						$t_providerBank->account				= $request->account[$i];
						$t_providerBank->branch					= $request->branch[$i];
						$t_providerBank->reference				= $request->reference[$i];
						$t_providerBank->clabe					= $request->clabe[$i];
						$t_providerBank->currency				= $request->currency[$i];
						$t_providerBank->iban					= $request->iban[$i];
						$t_providerBank->bic_swift				= $request->bic_swift[$i];
						$t_providerBank->agreement				= $request->agreement[$i];
						$t_providerBank->save();
					}
				}

				$requisitionHP						= new App\RequisitionHasProvider();
				$requisitionHP->idProviderSecondary	= $provider->id;
				$requisitionHP->idRequisition		= $request->idRequisition;
				$requisitionHP->user_id				= Auth::user()->id;
				$requisitionHP->save();

				if (isset($request->realPathNewProvider) && count($request->realPathNewProvider)>0) 
				{
					for ($i=0; $i < count($request->realPathNewProvider); $i++) 
					{
						if ($request->realPathNewProvider[$i] != "") 
						{
							$new_file_name							= Files::rename($request->realPathNewProvider[$i],$id);
							$documents								= new App\RequisitionHasProviderDocuments();
							$documents->name						= $request->nameDocumentNewProvider[$i];
							$documents->path						= $new_file_name;
							$documents->idRequisitionHasProvider	= $requisitionHP->id;
							$documents->user_id						= Auth::user()->id;
							$documents->save();
						}
					}
				}
			}


			$alert = "swal('','Proveedor Agregado Exitosamente', 'success');";
			$t_request = App\RequestModel::find($id);
			switch ($t_request->status) 
			{
				case 3:
					return redirect()->route('requisition.review.show',['id'=>$id])->with('alert',$alert);
					break;

				case 4:
					return redirect()->route('requisition.authorization.show',['id'=>$id])->with('alert',$alert);
					break;

				case 5:
					return redirect()->route('requisition.review.show',['id'=>$id])->with('alert',$alert);
					break;
			}
		}
	}

	public function rejectReview(Request $request, $id)
	{
		if (Auth::user()->module->where('id',231)->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return searchRedirect(231, $alert, 'administration/requisition/review');
			}
			if ($t_request->status == 4 || $t_request->status == 6)
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return searchRedirect(231, $alert, 'administration/requisition/review');
			}
			$t_request				= App\RequestModel::find($id);
			$t_request->status		= 6;
			$t_request->idCheck		= Auth::user()->id;
			$t_request->checkComment= $request->revisionComment;
			$t_request->reviewDate	= Carbon::now();
			$t_request->save();
			$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
			return searchRedirect(231, $alert, 'administration/requisition/review');
		}
		else
		{
			return abort(404);
		}
	}

	public function rejectAuthorization(Request $request, $id, $module = null)
	{
		if (Auth::user()->module->whereIn('id',[276,232])->count() > 0)
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				if($module == "276")
				{
					return searchRedirect($module, $alert, 'administration/requisition/vote');
				}
				else
				{
					return searchRedirect($module, $alert, 'administration/requisition/authorization');
				}
			}
			if ($module == '' && ($t_request->status == 27 || $t_request->status == 7))
			{
				$alert = "swal('', 'La requisición ya fue autorizada', 'error');";
				if($module == "276")
				{
					return searchRedirect($module, $alert, 'administration/requisition/vote');
				}
				else
				{
					return searchRedirect($module, $alert, 'administration/requisition/authorization');
				}
			}
			if ($module == 232 && $t_request->status == 5)
			{
				$alert = "swal('', 'La requisición ya fue autorizada', 'error');";
				return searchRedirect(232, $alert, 'administration/requisition/authorization');
			}
			if ($module == 276 && $t_request->status == 5)
			{
				$alert = "swal('', 'La requisición ya fue autorizada', 'error');";
				return searchRedirect(276, $alert, 'administration/requisition/vote');
			}
			if ($module == 276 && $t_request->status == 7)
			{
				$alert = "swal('', 'La requisición ya fue rechazada', 'error');";
				return searchRedirect(276, $alert, 'administration/requisition/vote');
			}

			$t_request                = App\RequestModel::find($id);
			$t_request->status        = 7;
			$t_request->idAuthorize   = Auth::user()->id;
			$t_request->authorizeDate = Carbon::now();
			$t_request->authorizeComment  = $request->revisionComment;
			$t_request->save();
			$alert = "swal('','Requisición Rechazada Exitosamente', 'success');";
			if($module == "276")
			{
				return searchRedirect($module, $alert, 'administration/requisition/vote');
			}
			else
			{
				return searchRedirect($module, $alert, 'administration/requisition/authorization');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function rejectVote(Request $request, $id)
	{
		if (Auth::user()->module->whereIn('id',232)->count() > 0)
		{
			return self::rejectAuthorization($request, $id, 276);
		}
		else
		{
			return abort(404);
		}
	}

	public function saveAuthorization(Request $request, $id)
	{
		if (Auth::user()->module->whereIn('id',[231,232])->count() > 0)
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return searchRedirect(232, $alert, 'administration/requisition/authorization');
			}
			if ($t_request->status == 27 || $t_request->status == 7)
			{
				return searchRedirect(232, $alert, 'administration/requisition/authorization');
			}
			$t_request->authorizeComment  = $request->revisionComment;
			$t_request->save();

			$t_request = App\RequestModel::find($id);
			if ($t_request->requisition->requisition_type != 3) 
			{
				if (isset($request->idRequisitionDetail) && count($request->idRequisitionDetail)>0) 
				{
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					} 
					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath[$i];
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $t_request->requisition->id;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
					{
						for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
						{
							$commentaries	= 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
							$type_currency	= 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

							$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
							$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
							$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
							$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
							
							$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
							$requisitionHasProvider->commentaries  = $request->$commentaries;
							$requisitionHasProvider->type_currency = $request->$type_currency;

							$requisitionHasProvider->delivery_time = $request->$delivery_time;
							$requisitionHasProvider->credit_time   = $request->$credit_time;
							$requisitionHasProvider->guarantee     = $request->$guarantee;
							$requisitionHasProvider->spare         = $request->$spare;

							$requisitionHasProvider->save();
						}
					}
					if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
					{
						for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
						{ 
							$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
							if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
							{
								$detail->exists_warehouse	= $request->exists_warehouse[$ird];
								$detail->save();
							}
						}
					}

					if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
					{
						for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
						{ 
							for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
							{ 
								$unitPrice                = 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$subtotal                 = 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$typeTax                  = 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$iva                      = 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$total                    = 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$idProviderSecondaryPrice = 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

								if ($request->$idProviderSecondaryPrice == "x") 
								{
									$providerPrice	= new App\ProviderSecondaryPrice();
								}
								else
								{
									$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
								}
								$providerPrice->unitPrice                = $request->$unitPrice;
								$providerPrice->subtotal                 = $request->$subtotal;
								$providerPrice->typeTax                  = $request->$typeTax;
								$providerPrice->iva                      = $request->$iva;
								$providerPrice->total                    = $request->$total;
								$providerPrice->user_id                  = Auth::user()->id;
								$providerPrice->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
								$providerPrice->idRequisitionHasProvider = $request->idRequisitionHasProvider[$ips];
								$providerPrice->save();

								$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$taxes		= 0;
								$retentions	= 0;

								if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
								{
									$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
								}

								if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
								{
									for ($t=0; $t < count($request->$name_add_tax); $t++) 
									{ 
										if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
										{
											if ($request->$tax_id[$t] == "x") 
											{
												$tax = new App\ProviderSecondaryPriceTaxes();
											}
											else
											{
												$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
											}
											
											$tax->name						= $request->$name_add_tax[$t];
											$tax->amount					= $request->$amount_add_tax[$t];
											$tax->type						= 1;
											$tax->providerSecondaryPrice_id	= $providerPrice->id;
											$tax->save();
											$taxes += $request->$amount_add_tax[$t];
										}
									}
								}

								if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
								{
									for ($t=0; $t < count($request->$name_add_ret); $t++) 
									{ 
										if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
										{
											if ($request->$ret_id[$t] == "x") 
											{
												$tax = new App\ProviderSecondaryPriceTaxes();
											}
											else
											{
												$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
											}
											$tax->name						= $request->$name_add_ret[$t];
											$tax->amount					= $request->$amount_add_ret[$t];
											$tax->type						= 2;
											$tax->providerSecondaryPrice_id	= $providerPrice->id;
											$tax->save();
											$retentions += $request->$amount_add_ret[$t];
										}
									}
								}
								$providerPrice->taxes		= $taxes;
								$providerPrice->retentions	= $retentions;
								$providerPrice->save();
							}
						}
					}

					$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
					switch ($t_request->status) 
					{
						case 3:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
						case 4:
							return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
							break;
						case 5:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;

						case 27:
							return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
							break;
					}
				}
				else
				{
					$alert = "swal('','No puede mandar una requisición sin conceptos.', 'error');";
					switch ($t_request->status) 
					{
						case 3:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;

						case 4:
							return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
							break;

						case 5:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
						case 27:
							return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
							break;

						default:
							# code...
							break;
					}
				}
			}
			else
			{	
				if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
				{
					for ($i=0; $i < count($request->realPathRequisition); $i++) 
					{
						if ($request->realPathRequisition[$i] != "") 
						{
							$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
							$documents					= new App\RequisitionDocuments();
							$documents->name			= $request->nameDocumentRequisition[$i];
							$documents->ticket_number	= $request->ticket_number[$i];
							$documents->fiscal_folio	= $request->fiscal_folio[$i];
							$documents->timepath		= $request->timepath[$i];
							$documents->amount			= $request->amount[$i];
							$documents->datepath		= $request->datepath[$i];
							$documents->path			= $new_file_name;
							$documents->idRequisition	= $t_request->requisition->id;
							$documents->user_id			= Auth::user()->id;
							$documents->save();
						}
					}
				}
				$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
				switch ($t_request->status) 
				{
					case 3:
						return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
						break;

					case 4:
						return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
						break;

					case 5:
						return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
						break;

					case 27:
						return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
						break;

					default:
						# code...
						break;
				}
			}
		}
	}

	public function saveReview(Request $request, $id)
	{
		if (Auth::user()->module->whereIn('id',[231,232])->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return searchRedirect(231, $alert, 'administration/requisition/review');
			}
			if ($t_request->status == 4 || $t_request->status == 6)
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return searchRedirect(231, $alert, 'administration/requisition/review');
			}
			$s_request = App\RequestModel::find($id);
			$s_request->idCheck  = Auth::user()->id;
			$s_request->checkComment  = $request->revisionComment;
			$s_request->save();

			$t_request = App\RequestModel::find($id);
			if ($t_request->requisition->requisition_type != 3) 
			{
				if (isset($request->idRequisitionDetail) && count($request->idRequisitionDetail)>0) 
				{
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}
					if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
					{
						for ($i=0; $i < count($request->realPathRequisition); $i++) 
						{
							if ($request->realPathRequisition[$i] != "") 
							{
								$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
								$documents					= new App\RequisitionDocuments();
								$documents->name			= $request->nameDocumentRequisition[$i];
								$documents->ticket_number	= $request->ticket_number[$i];
								$documents->fiscal_folio	= $request->fiscal_folio[$i];
								$documents->timepath		= $request->timepath[$i];
								$documents->amount			= $request->amount[$i];
								$documents->datepath		= $request->datepath!="" ? Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d') : null;
								$documents->path			= $new_file_name;
								$documents->idRequisition	= $t_request->requisition->id;
								$documents->user_id			= Auth::user()->id;
								$documents->save();
							}
						}
					}
					if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
					{
						for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
						{
							$commentaries	= 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
							$type_currency	= 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

							$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
							$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
							$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
							$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
							
							$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
							$requisitionHasProvider->commentaries  = $request->$commentaries;
							$requisitionHasProvider->type_currency = $request->$type_currency;

							$requisitionHasProvider->delivery_time = $request->$delivery_time;
							$requisitionHasProvider->credit_time   = $request->$credit_time;
							$requisitionHasProvider->guarantee     = $request->$guarantee;
							$requisitionHasProvider->spare         = $request->$spare;

							$requisitionHasProvider->save();
						}
					}
					if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
					{
						for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
						{ 
							$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
							if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
							{
								$detail->exists_warehouse	= $request->exists_warehouse[$ird];
								$detail->save();
							}
						}
					}
					if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
					{
						for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
						{ 
							for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
							{ 
								$unitPrice                = 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$subtotal                 = 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$typeTax                  = 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$iva                      = 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$total                    = 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$idProviderSecondaryPrice = 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								if ($request->$idProviderSecondaryPrice == "x") 
								{
									$providerPrice	= new App\ProviderSecondaryPrice();
								}
								else
								{
									$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
								}
								$providerPrice->unitPrice                = $request->$unitPrice;
								$providerPrice->subtotal                 = $request->$subtotal;
								$providerPrice->typeTax                  = $request->$typeTax;
								$providerPrice->iva                      = $request->$iva;
								$providerPrice->total                    = $request->$total;
								$providerPrice->user_id                  = Auth::user()->id;
								$providerPrice->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
								$providerPrice->idRequisitionHasProvider = $request->idRequisitionHasProvider[$ips];
								$providerPrice->save();

								$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
								$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

								if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
								{
									$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
								}

								if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
								{
									for ($t=0; $t < count($request->$name_add_tax); $t++) 
									{ 
										if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
										{
											if ($request->$tax_id[$t] == "x") 
											{
												$tax = new App\ProviderSecondaryPriceTaxes();
											}
											else
											{
												$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
											}
											
											$tax->name						= $request->$name_add_tax[$t];
											$tax->amount					= $request->$amount_add_tax[$t];
											$tax->type						= 1;
											$tax->providerSecondaryPrice_id	= $providerPrice->id;
											$tax->save();
										}
									}
								}

								if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
								{
									for ($t=0; $t < count($request->$name_add_ret); $t++) 
									{ 
										if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
										{
											if ($request->$ret_id[$t] == "x") 
											{
												$tax = new App\ProviderSecondaryPriceTaxes();
											}
											else
											{
												$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
											}
											$tax->name						= $request->$name_add_ret[$t];
											$tax->amount					= $request->$amount_add_ret[$t];
											$tax->type						= 2;
											$tax->providerSecondaryPrice_id	= $providerPrice->id;
											$tax->save();
										}
									}
								}
							}
						}
					}

					$alert = "swal('','Requisición Guardada Exitosamente', 'success');";
					switch ($t_request->status) 
					{
						case 3:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;

						case 4:
							return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
							break;

						case 5:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
						
						case 27:
							return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
							break;

						default:
							# code...
							break;
					}
				}
				else
				{
					$alert = "swal('','No puede mandar una requisición sin conceptos.', 'error');";
					switch ($t_request->status) 
					{
						case 3:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;

						case 4:
							return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
							break;

						case 5:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
						
						case 27:
							return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
							break;

						default:
							# code...
							break;
					}
				}
			}
			else
			{	
				if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
				{
					for ($i=0; $i < count($request->realPathRequisition); $i++) 
					{
						if ($request->realPathRequisition[$i] != "") 
						{
							$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
							$documents					= new App\RequisitionDocuments();
							$documents->name			= $request->nameDocumentRequisition[$i];
							$documents->ticket_number	= $request->ticket_number[$i];
							$documents->fiscal_folio	= $request->fiscal_folio[$i];
							$documents->timepath		= $request->timepath[$i];
							$documents->amount			= $request->amount[$i];
							$documents->datepath		= Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
							$documents->path			= $new_file_name;
							$documents->idRequisition	= $t_request->requisition->id;
							$documents->user_id			= Auth::user()->id;
							$documents->save();
						}
					}
				}

				$alert = "swal('','Requisición Guardada Exitosamente', 'success');";
				switch ($t_request->status) 
				{
					case 3:
						return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
						break;
					case 4:
						return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
						break;

					case 5:
						return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
						break;

					case 27:
						return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
						break;

					default:
						# code...
						break;
				}
			}
		}
	}

	public function deleteProvider(Request $request,$id)
	{
		if (Auth::user()->module->whereIn('id',[231,232])->count()>0) 
		{
			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\RequisitionDetail::whereIn('id',$request->delete)->delete();
			}
			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
				{
					$commentaries                          = 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
					$type_currency                         = 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

					$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
					$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
					$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
					$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];

					$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
					$requisitionHasProvider->commentaries  = $request->$commentaries;
					$requisitionHasProvider->type_currency = $request->$type_currency;

					$requisitionHasProvider->delivery_time = $request->$delivery_time;
					$requisitionHasProvider->credit_time   = $request->$credit_time;
					$requisitionHasProvider->guarantee     = $request->$guarantee;
					$requisitionHasProvider->spare         = $request->$spare;

					$requisitionHasProvider->save();
				}
			}
			if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
					if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
					{
						$detail->exists_warehouse	= $request->exists_warehouse[$ird];
						$detail->save();
					}
				}
			}
			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
					{ 
						$unitPrice					= 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$subtotal					= 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$typeTax					= 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$iva						= 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$total						= 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$idProviderSecondaryPrice	= 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if ($request->$idProviderSecondaryPrice == "x") 
						{
							$providerPrice	= new App\ProviderSecondaryPrice();
						}
						else
						{
							$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
						}
						$providerPrice->unitPrice                = $request->$unitPrice;
						$providerPrice->subtotal                 = $request->$subtotal;
						$providerPrice->typeTax                  = $request->$typeTax;
						$providerPrice->iva                      = $request->$iva;
						$providerPrice->total                    = $request->$total;
						$providerPrice->user_id                  = Auth::user()->id;
						$providerPrice->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
						$providerPrice->idRequisitionHasProvider = $request->idRequisitionHasProvider[$ips];
						$providerPrice->save();

						$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
						{
							$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
						}

						if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_tax); $t++) 
							{ 
								if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
								{
									if ($request->$tax_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
									}
									
									$tax->name						= $request->$name_add_tax[$t];
									$tax->amount					= $request->$amount_add_tax[$t];
									$tax->type						= 1;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}

						if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_ret); $t++) 
							{ 
								if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
								{
									if ($request->$ret_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
									}
									$tax->name						= $request->$name_add_ret[$t];
									$tax->amount					= $request->$amount_add_ret[$t];
									$tax->type						= 2;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}
					}
				}
			}
			$providerSecondaryPrice_id = App\ProviderSecondaryPrice::select('id')->where('idRequisitionHasProvider',$id)->get();
			$deleteDocs 	= App\RequisitionHasProviderDocuments::where('idRequisitionHasProvider',$id)->delete();
			$deleteTaxes 	= $providerSecondaryPrice_id->count()>0 ? App\ProviderSecondaryPriceTaxes::whereIn('providerSecondaryPrice_id',$providerSecondaryPrice_id)->delete() : '';
			$deleteDetail 	= App\ProviderSecondaryPrice::where('idRequisitionHasProvider',$id)->delete();
			$deleteProvider = App\RequisitionHasProvider::find($id)->delete();
			$alert = "swal('','Proveedor Eliminado', 'success');";
			return back()->with('alert',$alert);
		}
	}
	
	public function updateReview(Request $request,$id)
	{
		
		if (Auth::user()->module->where('id',231)->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return redirect()->route('requisition.review')->with('alert',$alert);
			}

			if ($t_request->status == 4 || $t_request->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect()->route('requisition.review')->with('alert',$alert);
			}

			$t_request->idCheck			= Auth::user()->id;
			$t_request->checkComment	= $request->revisionComment;
			$t_request->reviewDate 		= Carbon::now();
			$t_request->save();

			if ($t_request->requisition->requisition_type != 3) 
			{	
				
				
				if (isset($request->idRequisitionDetail) && count($request->idRequisitionDetail)>0) 
				{					
					if (isset($request->delete) && count($request->delete)>0) 
					{
						App\RequisitionDetail::whereIn('id',$request->delete)->delete();
					}
					
					switch ($request->t_requisitionType) 
					{
						case 1:
						case 5:
							$t_request              = App\RequestModel::find($request->t_folio);
							$t_request->fDate       = Carbon::now();
							$t_request->idElaborate = Auth  ::user()->id;
							$t_request->idRequest   = $request->t_solicitant;
							$t_request->idProject   = $request->t_proyectName;
							$t_request->code_wbs    = $request->t_wbs;
							$t_request->code_edt    = $request->t_edt;
							$t_request->status      = 4;
							$t_request->kind        = 19;
							$t_request->save();
		
							$count	= App\RequestModel::where('kind',19)
									->where('idProject',$request->project_id)
									->count();
		
							$number	= $count + 1;
		
							$RealFolio						= $t_request->requisition->id;
							$requisition                   	= App\Requisition::find($RealFolio);
							$requisition->title            	= $request->t_title;
							$requisition->date_request     	= Carbon::now();
							$requisition->number           	= $request->t_number;
							$requisition->date_comparation 	= $request->date_comparation;
							$requisition->date_obra        	= $request->date_obra !="" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
							$requisition->idFolio          	= $t_request->folio;
							$requisition->idKind           	= $t_request->kind;
							$requisition->urgent           	= $request->t_urgent;
							$requisition->code_wbs         	= $request->t_wbs;
							$requisition->code_edt         	= $request->t_edt;
							$requisition->requisition_type 	= $request->t_requisitionType;
							$requisition->subcontract_number= $request->t_subcontract;
							$requisition->buy_rent        	= $request->t_buy_rent;
							$requisition->validity         	= $request->t_validity;
							$requisition->generated_number 	= $request->t_generated_number;
							$requisition->save();
		
							$idRequisition = $requisition->id;
							
							if (isset($request->t_quantity) && count($request->t_quantity)>0) 
							{
								App\RequisitionDetail::where('idRequisition',$idRequisition)->delete();
								for ($i=0; $i < count($request->t_quantity); $i++) 	
								{
									$c_r = App\CatRequisitionName::where('name',$request->t_name[$i])->first();
									if(!$c_r)
									{
										$c_r = App\CatRequisitionName::create(['name' => $request->t_name[$i]]);
									} 
		
									$name_measurement = App\CatMeasurementUnit::where('description',$request->t_measurement[$i])->first();
									if(!$name_measurement)
									{
										$name_measurement = App\CatMeasurementUnit::create(['description' => $request->t_measurement[$i]]);
									} 
									

									$detail									= new App\RequisitionDetail();
									$detail->category						= $request->t_category[$i];
									$detail->cat_procurement_material_id	= $request->t_type[$i];
									$detail->part							= ($i + 1);
									$detail->quantity						= $request->t_quantity[$i];
									$detail->unit							= $request->t_unit[$i];
									$detail->name							= $c_r->name;
									$detail->measurement					= $name_measurement->description;
									$detail->description					= $request->t_description[$i];
									if ($request->t_requisitionType == 5) 
									{
										$detail->brand		= $request->t_brand[$i];
										$detail->model		= $request->t_model[$i];
										$detail->usage_time	= $request->t_usage_time[$i];
									}
									$detail->exists_warehouse				= $request->t_exists_warehouse[$i];
									$detail->idRequisition					= $idRequisition;
									$detail->save();
								}
							}
		
							$count = 1;
							foreach($t_request->requisition->details as $detail)
							{
								$detail->part	= $count;
								$detail->save();
								$count++;
							}
		
							if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
							{
								for ($i=0; $i < count($request->realPathRequisition); $i++) 
								{
									if ($request->realPathRequisition[$i] != "") 
									{
										$new_file_name				= Files::rename($request->realPathRequisition[$i],$request->t_folio);
										$documents					= new App\RequisitionDocuments();
										$documents->name			= $request->nameDocumentRequisition[$i];
										$documents->ticket_number	= $request->ticket_number[$i];
										$documents->fiscal_folio	= $request->fiscal_folio[$i];
										$documents->timepath		= $request->timepath[$i];
										$documents->amount			= $request->amount[$i];
										$documents->datepath		= Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
										$documents->path			= $new_file_name;
										$documents->idRequisition	= $idRequisition;
										$documents->user_id			= Auth::user()->id;
										$documents->save();
									}
								}
							}
							break;		
						case 2:
						case 4:
						case 6:
							$t_request              = App\RequestModel::find($request->t_folio);
							$t_request->fDate       = Carbon::now();
							$t_request->idElaborate = Auth::user()->id;
							$t_request->idRequest   = $request->t_solicitant;
							$t_request->idProject   = $request->t_proyectName;
							$t_request->code_wbs    = $request->t_wbs;
							$t_request->code_edt    = $request->t_edt;
							$t_request->status      = 4;
							$t_request->kind        = 19;
							$t_request->save();
		
							$count	= App\RequestModel::where('kind',19)
									->where('idProject',$request->project_id)
									->count();
							$number	= $count + 1;
							$RealFolio					   	 = $t_request->requisition->id;
							$requisition                   	 = App\Requisition::find($RealFolio);
							$requisition->title            	 = $request->t_title;
							$requisition->date_request     	 = Carbon::now();
							$requisition->number           	 = $request->t_number;
							$requisition->date_comparation 	 = $request->date_comparation;
							$requisition->date_obra        	 = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
							$requisition->idFolio          	 = $t_request->folio;
							$requisition->idKind           	 = $t_request->kind;
							$requisition->urgent           	 = $request->t_urgent;
							$requisition->code_wbs         	 = $request->t_wbs;
							$requisition->code_edt         	 = $request->t_edt;
							$requisition->requisition_type 	 = $request->t_requisitionType;
							$requisition->subcontract_number = $request->t_subcontract;
							$requisition->buy_rent         	 = $request->t_buy_rent;
							$requisition->validity         	 = $request->t_validity;
							$requisition->generated_number 	 = $request->t_generated_number;
							$requisition->save();
							$idRequisition = $requisition->id;
		
							if (isset($request->t_quantity) && count($request->t_quantity)>0)
							{
								App\RequisitionDetail::where('idRequisition',$idRequisition)->delete();

								for ($i=0; $i < count($request->t_quantity); $i++) 	
								{
									$detail                = new App\RequisitionDetail();
									$detail->part          = ($i + 1);
									$detail->unit          = $request->t_unit[$i];
									$detail->name          = $request->t_name[$i];
									$detail->quantity      = $request->t_quantity[$i];
									$detail->description   = $request->t_description[$i];
									if($request->t_requisitionType == 2)
									{
										$detail->period		= $request->t_period[$i];
										$detail->category	= $request->t_category[$i];
									}
									$detail->idRequisition = $idRequisition;
									$detail->save();
								}
							}
		
							$count = 1;
							foreach($t_request->requisition->details as $detail)
							{
								$detail->part	= $count;
								$detail->save();
								$count++;
							}
							
							if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
							{
								for ($i=0; $i < count($request->realPathRequisition); $i++) 
								{
									if ($request->realPathRequisition[$i] != "") 
									{
										$new_file_name				= Files::rename($request->realPathRequisition[$i],$request->t_folio);
										$documents					= new App\RequisitionDocuments();
										$documents->name			= $request->nameDocumentRequisition[$i];
										$documents->ticket_number	= $request->ticket_number[$i];
										$documents->fiscal_folio	= $request->fiscal_folio[$i];
										$documents->timepath		= $request->timepath[$i];
										$documents->amount			= $request->amount[$i];
										$documents->datepath		= Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
										$documents->path			= $new_file_name;
										$documents->idRequisition	= $idRequisition;
										$documents->user_id			= Auth::user()->id;
										$documents->save();
									}
								}
							}
							break;
						
						default:
							# code...
							break;
					}
					if (isset($request->t_exists_warehouse) && count($request->t_exists_warehouse)) 
					{
						for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
						{ 
							$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
							if (isset($request->exists_warehouse[$ird]) && $request->t_exists_warehouse[$ird] != "") 
							{
								$detail->exists_warehouse	= $request->exists_warehouse[$ird];
								$detail->save();
							}
						}
					}
					if (isset($request->selected_idRequisitionDetail) && count($request->selected_idRequisitionDetail)>0) 
					{
						
						$generatedRequisitionNumber = null;
						if($request->t_proyectName == 126 && $request->t_wbs != "")
						{
							$wbs_code = $request->t_wbs;
							$wbsModel = App\CatCodeWBS::find($wbs_code);
							
							$requisitionRequest = App\RequestModel::whereNotIn('status',[1,2])
							->whereHas('requisition', function($q) use($wbs_code)
							{
								$q->where('code_wbs',$wbs_code);
							})
							->count();
							$edtPart = null;
							if($request->t_edt != "")
							{
								$edtModel = App\CatCodeEDT::find($request->t_edt);
								$edtPart  = '-'.$edtModel->edt_number.'-'.$edtModel->phase;
							}
							$generatedRequisitionNumber = 'PRO-R2B-P6-'.$wbsModel->code.$edtPart.'-RQ-'.str_pad($requisitionRequest + 1, 4, '0',STR_PAD_LEFT);
						}
						
						switch ($request->t_requisitionType) 
						{
							case 1:
							case 5:
								$t_request              = new App\RequestModel();
								$t_request->fDate       = Carbon::now();
								$t_request->idElaborate = Auth  ::user()->id;
								$t_request->idRequest   = $request->t_solicitant;
								$t_request->idProject   = $request->t_proyectName;
								$t_request->code_wbs    = $request->t_wbs;
								$t_request->code_edt    = $request->t_edt;
								$t_request->status      = 3;
								$t_request->kind        = 19;
								$t_request->save();

								$requisition                   	= new App\Requisition();
								$requisition->title            	= $request->t_title;
								$requisition->date_request     	= Carbon::now();
								$requisition->number           	= $request->t_number;
								$requisition->date_comparation 	= $request->date_comparation;
								$requisition->date_obra        	= $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
								$requisition->idFolio          	= $t_request->folio;
								$requisition->idKind           	= $t_request->kind;
								$requisition->urgent           	= $request->t_urgent;
								$requisition->code_wbs         	= $request->t_wbs;
								$requisition->code_edt         	= $request->t_edt;
								$requisition->requisition_type 	= $request->t_requisitionType;
								$requisition->subcontract_number= $request->t_subcontract;
								$requisition->buy_rent        	= $request->t_buy_rent;
								$requisition->validity         	= $request->t_validity;
								$requisition->generated_number 	= $generatedRequisitionNumber;
								$requisition->save();

								$idRequisition = $requisition->id;
								if (isset($request->selected_t_quantity) && count($request->selected_t_quantity)>0) 
								{
									for ($i=0; $i < count($request->selected_t_quantity); $i++) 	
									{
										$c_r = App\CatRequisitionName::where('name',$request->selected_t_name[$i])->first();
										if(!$c_r)
										{
											$c_r = App\CatRequisitionName::create(['name' => $request->selected_t_name[$i]]);
										} 

										$name_measurement = App\CatMeasurementUnit::where('description',$request->selected_t_measurement[$i])->first();
										if(!$name_measurement)
										{
											$name_measurement = App\CatMeasurementUnit::create(['description' => $request->selected_t_measurement[$i]]);
										} 

										$detail									= new App\RequisitionDetail();
										$detail->category						= $request->selected_t_category[$i];
										$detail->cat_procurement_material_id	= $request->selected_t_type[$i];
										$detail->part							= ($i + 1);
										$detail->quantity						= $request->selected_t_quantity[$i];
										$detail->unit							= $request->selected_t_unit[$i];
										$detail->name							= $c_r->name;
										$detail->measurement					= $name_measurement->description;
										$detail->description					= $request->selected_t_description[$i];
										if ($request->t_requisitionType == 5) 
										{
											$detail->brand		= $request->selected_t_brand[$i];
											$detail->model		= $request->selected_t_model[$i];
											$detail->usage_time	= $request->selected_t_usage_time[$i];
										}
										$detail->exists_warehouse				= $request->selected_t_exists_warehouse[$i];
										$detail->idRequisition					= $idRequisition;
										$detail->save();
									}
								}

								$count = 1;
								foreach($t_request->requisition->details as $detail)
								{
									$detail->part	= $count;
									$detail->save();
									$count++;
								}

								$documents = App\RequisitionDocuments::where('idRequisition', $request->idRequisition)->get();
								
								foreach($documents as $document)
								{
									$doc 					= "docs/requisition/".$document->path;
									$doc 					= explode('.',$doc);

									$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_requisitionDoc.';
									$name					= $nameWithoutExtention.$doc[1];
									$name					= $t_request->folio."_".$name;
									
									copy("docs/requisition/".$document->path, "docs/requisition/".$name);

									$updateDocuments = 	new App\RequisitionDocuments();
									$updateDocuments->name 			= $document->name;
									$updateDocuments->ticket_number	= $document->ticket_number;
									$updateDocuments->fiscal_folio	= $document->fiscal_folio;
									$updateDocuments->timepath		= $document->timepath;
									$updateDocuments->amount		= $document->amount;
									$updateDocuments->datepath		= $document->datepath;
									$updateDocuments->path 			= $name;
									$updateDocuments->user_id 		= $document->user_id;
									$updateDocuments->idRequisition = $idRequisition;
									$updateDocuments->created 		= $document->created;
									$updateDocuments->save();
									
								}

								
								break;						
							case 2:
							case 4:
							case 6:
								$t_request              = new App\RequestModel();
								$t_request->fDate       = Carbon::now();
								$t_request->idElaborate = Auth::user()->id;
								$t_request->idRequest   = $request->t_solicitant;
								$t_request->idProject   = $request->t_proyectName;
								$t_request->code_wbs    = $request->t_wbs;
								$t_request->code_edt    = $request->t_edt;
								$t_request->status      = 3;
								$t_request->kind        = 19;
								$t_request->save();
			
								$count	= App\RequestModel::where('kind',19)
										->where('idProject',$request->project_id)
										->count();
								$number	= $count + 1;
			
								$requisition                   = new App\Requisition();
								$requisition->title            = $request->t_title;
								$requisition->date_request     = Carbon::now();
								$requisition->number           = $request->t_number;
								$requisition->date_comparation = $request->date_comparation;
								$requisition->date_obra        = $request->date_obra != "" ? Carbon::createFromFormat('d-m-Y',$request->date_obra)->format('Y-m-d') : null;
								$requisition->idFolio          = $t_request->folio;
								$requisition->idKind           = $t_request->kind;
								$requisition->urgent           = $request->t_urgent;
								$requisition->code_wbs         = $request->t_wbs;
								$requisition->code_edt         = $request->t_edt;
								$requisition->requisition_type = $request->t_requisitionType;
								$requisition->subcontract_number = $request->t_subcontract;
								$requisition->buy_rent         = $request->t_buy_rent;
								$requisition->validity         = $request->t_validity;
								$requisition->generated_number = $generatedRequisitionNumber;
								$requisition->save();
								$idRequisition = $requisition->id;
			
								if (isset($request->selected_t_quantity) && count($request->selected_t_quantity)>0)
								{
									for ($i=0; $i < count($request->selected_t_quantity); $i++) 	
									{
										$detail                = new App\RequisitionDetail();
										$detail->part          = ($i + 1);
										$detail->unit          = $request->selected_t_unit[$i];
										$detail->name          = $request->selected_t_name[$i];
										$detail->quantity      = $request->selected_t_quantity[$i];
										$detail->description   = $request->selected_t_description[$i];
										if($request->t_requisitionType == 2)
										{
											$detail->period		= $request->selected_t_period[$i];
											$detail->category	= $request->selected_t_category[$i];
										}
										$detail->idRequisition = $idRequisition;
										$detail->save();
									}
								}
			
								$count = 1;
								foreach($t_request->requisition->details as $detail)
								{
									$detail->part	= $count;
									$detail->save();
									$count++;
								}
								$documents = App\RequisitionDocuments::where('idRequisition', $request->idRequisition)->get();
								
								foreach($documents as $document)
								{
									$doc 					= "docs/requisition/".$document->path;
									$doc 					= explode('.',$doc);

									$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_requisitionDoc.';
									$name					= $nameWithoutExtention.$doc[1];
									$name					= $t_request->folio."_".$name;
									
									copy("docs/requisition/".$document->path, "docs/requisition/".$name);

									$updateDocuments = 	new App\RequisitionDocuments();
									$updateDocuments->name 			= $document->name;
									$updateDocuments->ticket_number	= $document->ticket_number;
									$updateDocuments->fiscal_folio	= $document->fiscal_folio;
									$updateDocuments->timepath		= $document->timepath;
									$updateDocuments->amount		= $document->amount;
									$updateDocuments->datepath		= $document->datepath;
									$updateDocuments->path 			= $name;
									$updateDocuments->user_id 		= $document->user_id;
									$updateDocuments->idRequisition = $idRequisition;
									$updateDocuments->created 		= $document->created;
									$updateDocuments->save();
									
								}
								
								break;
							
							default:
								# code...
								break;
						}
						
					}
					$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
					return searchRedirect(231, $alert, 'administration/requisition/review');
				}
				else
				{
					$alert = "swal('','No puede mandar una requisición sin conceptos.', 'error');";
					switch ($t_request->status) 
					{
						case 3:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
						case 4:
							return searchRedirect(232, $alert, 'administration/requisition/'.$id.'/authorization');
							break;
	
						case 5:
							return searchRedirect(231, $alert, 'administration/requisition/'.$id.'/review');
							break;
	
						case 27:
							return searchRedirect(276, $alert, 'administration/requisition/'.$id.'/vote');
							break;
						
						default:
							# code...
							break;
					}
				}
			}
			else
			{
				$t_request->status		= 4;
				$t_request->idCheck		= Auth::user()->id;
				$t_request->reviewDate	= Carbon::now();
				$t_request->save();

				if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
				{
					for ($i=0; $i < count($request->realPathRequisition); $i++) 
					{
						if ($request->realPathRequisition[$i] != "") 
						{
							$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
							$documents					= new App\RequisitionDocuments();
							$documents->name			= $request->nameDocumentRequisition[$i];
							$documents->ticket_number	= $request->ticket_number[$i];
							$documents->fiscal_folio	= $request->fiscal_folio[$i];
							$documents->timepath		= $request->timepath[$i];
							$documents->amount			= $request->amount[$i];
							$documents->datepath		= Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d');
							$documents->path			= $new_file_name;
							$documents->idRequisition	= $t_request->requisition->id;
							$documents->user_id			= Auth::user()->id;
							$documents->save();
						}
					}
				}
				$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				return searchRedirect(231, $alert, 'administration/requisition/review');
			}
		}
	}

	public function authorization(Request $request)
	{
		if (Auth::user()->module->where('id',232)->count()>0) 
		{
			$data            = App\Module::find($this->module_id);
			$title_request   = $request->title_request;
			// $mindate_request = $request->mindate_request;
			// $maxdate_request = $request->maxdate_request;
			// $mindate_obra    = $request->mindate_obra;
			// $maxdate_obra    = $request->maxdate_obra;
			$mindate_request 	= $request->mindate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_request)->format('Y-m-d') : null;
			$maxdate_request 	= $request->maxdate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_request)->format('Y-m-d') : null;
			$mindate_obra    	= $request->mindate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_obra)->format('Y-m-d') : null;
			$maxdate_obra    	= $request->maxdate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_obra)->format('Y-m-d') : null;

			$status          = $request->status;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$employee        = $request->employee;
			
			if($status == '')
			{
				$status= [4,5];
			} 
			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(232)->pluck('project_id'))
				->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(232)->pluck('requisition_type_id'))
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$employee) 
				{
					if ($employee != "") 
					{
						$query->whereHas('requisition.employees',function($q) use($employee)
						{
							$q->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
						});
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "" && !in_array(1,$status))
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{ 
						$query->whereNotIn('request_models.status',[2,3,4,6])
						->where('request_models.idAuthorize',Auth::user()->id);
					}  
					if ($project_request != "") 
					{
						$query->whereIn('request_models.idProject',$project_request);
					}
					if ($number != "") 
					{
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}
					 
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.status','ASC')
				->orderBy('request_models.authorizeDate','DESC')
				->orderBy('request_models.reviewDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);

			return response(
				view('administracion.requisicion.busqueda_autorizacion',
					[
						'id'              => $data['father'],
						'title'           => $data['name'],
						'details'         => $data['details'],
						'child_id'        => $this->module_id,
						'option_id'       => 232,
						'requests'        => $requests,
						'mindate_obra'    => $request->mindate_obra, 
						'maxdate_obra'    => $request->maxdate_obra, 
						'mindate_request' => $request->mindate_request, 
						'maxdate_request' => $request->maxdate_request, 
						'folio'           => $folio,
						'status'          => $status,
						'title_request'   => $title_request,
						'user_request'    => $user_request,
						'project_request' => $project_request,
						'number'          => $number,
						'wbs'             => $wbs,
						'edt'             => $edt,
						'type'            => $type,
						'employee'        => $employee,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(232), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function authorizationEdit($id)
	{
		if (Auth::user()->module->where('id',232)->count()>0) 
		{
			$request = App\RequestModel::find($id);
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.requisicion.alta_material',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 232,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}
	
	public function showAuthorization($id)
	{
		if (Auth::user()->module->where('id',232)->count()>0) 
		{
			$request = App\RequestModel::where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(232)->pluck('project_id'))
				->whereIn('request_models.status',[4,5])->find($id);

			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				switch ($request->status) 
				{
					case 4:
						return view('administracion.requisicion.editar_autorizacion',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 232,
								'request'	=> $request
							]
						);
						break;
					
					case 5:
						return view('administracion.requisicion.generar_solicitud',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 232,
								'request'	=> $request
							]
						);
						break;

					default:
						break;
				}
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición o ya pasó por el proceso de Compras Locales', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function updateAuthorization(Request $request,$id)
	{
		if (Auth::user()->module->where('id',232)->count()>0) 
		{
			$t_request = App\RequestModel::find($id); 
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return redirect()->route('requisition.authorization')->with('alert',$alert);		
			}

			if ($t_request->status == 27 || $t_request->status == 7) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'success');";
				return redirect()->route('requisition.authorization')->with('alert',$alert);
			}

			if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
			{
				for ($i=0; $i < count($request->realPathRequisition); $i++) 
				{
					if ($request->realPathRequisition[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
						$documents					= new App\RequisitionDocuments();
						$documents->name			= $request->nameDocumentRequisition[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->path			= $new_file_name;
						$documents->idRequisition	= $t_request->requisition->id;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			if ($t_request->requisition->requisition_type != 3) 
			{
				if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
				{
					for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
					{
						$commentaries                          = 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
						$type_currency                         = 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

						$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
						$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
						$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
						$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
						
						$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
						$requisitionHasProvider->commentaries  = $request->$commentaries;
						$requisitionHasProvider->type_currency = $request->$type_currency;

						$requisitionHasProvider->delivery_time = $request->$delivery_time;
						$requisitionHasProvider->credit_time   = $request->$credit_time;
						$requisitionHasProvider->guarantee     = $request->$guarantee;
						$requisitionHasProvider->spare         = $request->$spare;

						$requisitionHasProvider->save();
					}
				}

				if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
				{
					for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
					{ 
						$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
						if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
						{
							$detail->exists_warehouse	= $request->exists_warehouse[$ird];
							$detail->save();
						}
					}
				}

				if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
				{
					for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
					{ 
						for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
						{ 
							$unitPrice					= 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$subtotal					= 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$typeTax					= 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$iva						= 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$total						= 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$idProviderSecondaryPrice	= 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

							if ($request->$idProviderSecondaryPrice == "x") 
							{
								$providerPrice	= new App\ProviderSecondaryPrice();
							}
							else
							{
								$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
							}
							$providerPrice->unitPrice                = $request->$unitPrice;
							$providerPrice->subtotal                 = $request->$subtotal;
							$providerPrice->typeTax                  = $request->$typeTax;
							$providerPrice->iva                      = $request->$iva;
							$providerPrice->total                    = $request->$total;
							$providerPrice->user_id                  = Auth::user()->id;
							$providerPrice->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
							$providerPrice->idRequisitionHasProvider = $request->idRequisitionHasProvider[$ips];
							$providerPrice->save();
							

							$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
							$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

							$taxes		= 0;
							$retentions	= 0;

							if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
							{
								$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
							}

							if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
							{
								for ($t=0; $t < count($request->$name_add_tax); $t++) 
								{ 
									if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
									{
										if ($request->$tax_id[$t] == "x") 
										{
											$tax = new App\ProviderSecondaryPriceTaxes();
										}
										else
										{
											$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
										}
										
										$tax->name						= $request->$name_add_tax[$t];
										$tax->amount					= $request->$amount_add_tax[$t];
										$tax->type						= 1;
										$tax->providerSecondaryPrice_id	= $providerPrice->id;
										$tax->save();
										$taxes += $request->$amount_add_tax[$t];
									}
								}
							}

							if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
							{
								for ($t=0; $t < count($request->$name_add_ret); $t++) 
								{ 
									if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
									{
										if ($request->$ret_id[$t] == "x") 
										{
											$tax = new App\ProviderSecondaryPriceTaxes();
										}
										else
										{
											$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
										}
										$tax->name						= $request->$name_add_ret[$t];
										$tax->amount					= $request->$amount_add_ret[$t];
										$tax->type						= 2;
										$tax->providerSecondaryPrice_id	= $providerPrice->id;
										$tax->save();
										$retentions += $request->$amount_add_ret[$t];
									}
								}
							}

							$providerPrice->taxes		= $taxes;
							$providerPrice->retentions	= $retentions;
							$providerPrice->save();

						}
					}
				}

				$t_request->status				= 27;
				$t_request->idAuthorize			= Auth::user()->id;
				$t_request->authorizeDate		= Carbon::now();
				$t_request->authorizeComment	= $request->revisionComment;

				$t_requisition						= App\Requisition::find($t_request->requisition->id);
				$t_requisition->date_comparation	= Carbon::now();
				$t_requisition->save();

				$t_request->save();


				$alert = "swal('','Requisición Enviada a Votación.', 'success');";
				return searchRedirect(232, $alert, 'administration/requisition/authorization');
					
			}
			else
			{
				/*
				if ($t_request->requisition->employees()->exists()) 
				{
					foreach ($t_request->requisition->employees as $key => $employee) 
					{
						$new_employee							= new App\RealEmployee();
						$new_employee->name						= $employee->name;
						$new_employee->last_name				= $employee->last_name;
						$new_employee->scnd_last_name			= $employee->scnd_last_name;
						$new_employee->curp						= $employee->curp;
						$new_employee->rfc						= $employee->rfc;
						$new_employee->tax_regime				= $employee->tax_regime;
						$new_employee->imss						= $employee->imss;
						$new_employee->email					= $employee->email;
						$new_employee->street					= $employee->street;
						$new_employee->number					= $employee->number;
						$new_employee->colony					= $employee->colony;
						$new_employee->cp						= $employee->cp;
						$new_employee->city						= $employee->city;
						$new_employee->state_id					= $employee->state;
						$new_employee->doc_birth_certificate	= $employee->doc_birth_certificate;
						$new_employee->doc_proof_of_address		= $employee->doc_proof_of_address;
						$new_employee->doc_nss					= $employee->doc_nss;
						$new_employee->doc_ine					= $employee->doc_ine;
						$new_employee->doc_curp					= $employee->doc_curp;
						$new_employee->doc_rfc					= $employee->doc_rfc;
						$new_employee->doc_cv					= $employee->doc_cv;
						$new_employee->doc_proof_of_studies		= $employee->doc_proof_of_studies;
						$new_employee->doc_professional_license	= $employee->doc_professional_license;
						$new_employee->sys_user					= 0;
						$new_employee->save();

						$new_worker_data                            = new App\WorkerData();
						$new_worker_data->idEmployee				= $new_employee->id;
						$new_worker_data->state						= $employee->state;
						$new_worker_data->project					= $employee->project;
						$new_worker_data->enterprise				= $employee->enterprise;
						$new_worker_data->account					= $employee->account;
						$new_worker_data->direction					= $employee->direction;
						$new_worker_data->department				= $employee->department;
						$new_worker_data->position					= $employee->position;
						$new_worker_data->immediate_boss			= $employee->immediate_boss;
						$new_worker_data->admissionDate				= $employee->admissionDate;
						$new_worker_data->imssDate					= $employee->imssDate;
						$new_worker_data->downDate					= $employee->downDate;
						$new_worker_data->endingDate				= $employee->endingDate;
						$new_worker_data->reentryDate				= $employee->reentryDate;
						$new_worker_data->workerType				= $employee->workerType;
						$new_worker_data->regime_id					= $employee->regime_id;
						$new_worker_data->workerStatus				= $employee->workerStatus;
						$new_worker_data->status_reason				= $employee->status_reason;
						$new_worker_data->status_imss				= $employee->status_imss;
						$new_worker_data->sdi						= $employee->sdi;
						$new_worker_data->periodicity				= $employee->periodicity;
						$new_worker_data->employer_register			= $employee->employer_register;
						$new_worker_data->paymentWay				= $employee->paymentWay;
						$new_worker_data->netIncome					= $employee->netIncome;
						$new_worker_data->complement				= $employee->complement;
						$new_worker_data->fonacot					= $employee->fonacot;
						$new_worker_data->recorder					= Auth::user()->id;
						$new_worker_data->infonavitCredit			= $employee->infonavitCredit;
						$new_worker_data->infonavitDiscount			= $employee->infonavitDiscount;
						$new_worker_data->infonavitDiscountType		= $employee->infonavitDiscountType;
						$new_worker_data->alimonyDiscount			= $employee->alimonyDiscount;
						$new_worker_data->alimonyDiscountType		= $employee->alimonyDiscountType;
						$new_worker_data->viatics					= $employee->viatics;
						$new_worker_data->camping					= $employee->camping;
						$new_worker_data->position_immediate_boss	= $employee->position_immediate_boss;
						$new_worker_data->save();

						if ($employee->wbs_id != "") 
						{
							$new_emp_wbs					= new App\EmployeeWBS;
							$new_emp_wbs->working_data_id	= $new_worker_data->id;
							$new_emp_wbs->cat_code_w_bs_id	= $employee->wbs_id;
							$new_emp_wbs->save();
						}

						if ($employee->subdepartment_id != "") 
						{
							$new_emp_sub					= new App\EmployeeSubdepartment();
							$new_emp_sub->working_data_id	= $new_worker_data->id;
							$new_emp_sub->subdepartment_id	= $employee->subdepartment_id;
							$new_emp_sub->save();
						}

					
						if($employee->bankData()->exists())
						{
							foreach ($employee->bankData as $k => $account)
							{
								$empAcc              = new App\EmployeeAccount();
								$empAcc->idEmployee  = $new_employee->id;
								$empAcc->beneficiary = $account->beneficiary;
								$empAcc->type        = $account->type;
								$empAcc->alias       = $account->alias;
								$empAcc->clabe       = $account->clabe;
								$empAcc->account     = $account->account;
								$empAcc->cardNumber  = $account->cardNumber;
								$empAcc->idCatBank   = $account->idCatBank;
								$empAcc->branch      = $account->branch;
								$empAcc->recorder    = Auth::user()->id;
								$empAcc->save();
							}
						}
					}
				}
				*/

				$t_request->status				= 5;
				$t_request->idAuthorize			= Auth::user()->id;
				$t_request->authorizeDate		= Carbon::now();
				$t_request->authorizeComment	= $request->revisionComment;
				$t_request->save();

				$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success');";

				$userEmail = App\User::find($t_request->idCheck);
				if ($userEmail->module->where('id',63)->count() > 0) 
				{
					if ($t_request->requisition->employees->where('computer_required',1)->count() > 0) 
					{
						foreach ($t_request->requisition->employees->where('computer_required',1) as $key => $employee) 
						{
							try
							{
								$name 			= $userEmail->fullName();
								$to 			= $userEmail->email;
								$kind 			= "Cómputo";
								$status 		= "Solicitar";
								$date 			= Carbon::now();
								$url 			= route('computer.create');
								$subject 		= "Solicitud de equipo de cómputo";
								$requestUser	= $employee->fullName();
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
								$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success');";
							}
							catch(\Exception $e)
							{
								$alert 	= "swal('', 'La requisición fue autorizada exitosamente, pero ocurrio un error al enviar el correo de notificación de solicitud de equipo de cómputo', 'success');";
							}
						}
					}
				}
				return searchRedirect(232, $alert, 'administration/requisition/authorization');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function generateRequest(Request $request, $id)
	{
		if (Auth::user()->module->where('id',232)->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			$t_request->status 			= 17;
			$t_request->save();
			$countRequest = 1;
			for ($i=0; $i < count($request->providers); $i++) 
			{ 
				$typeRequest		= 'typeRequest_'.$request->providers[$i];
				if ($request->$typeRequest == 1) 
				{
					$old_provider 	= App\ProviderSecondary::find($request->providers[$i]);

					$checkProvider 	= App\Provider::where('rfc',$old_provider->rfc)->where('status',2)->first();
					if ($checkProvider != "") 
					{
						$providerOld			= App\Provider::find($checkProvider->idProvider);
						$providerOld->status	= 0;
						$providerOld->save();

						$provider_data_id = $providerOld->provider_data_id;

						$t_provider                 = new App\Provider();
						$t_provider->businessName	= $old_provider->businessName;
						$t_provider->beneficiary	= $old_provider->beneficiary;
						$t_provider->phone			= $old_provider->phone;
						$t_provider->rfc			= $old_provider->rfc;
						$t_provider->contact		= $old_provider->contact;
						$t_provider->commentaries	= $old_provider->commentaries;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $old_provider->address;
						$t_provider->number			= $old_provider->number;
						$t_provider->colony			= $old_provider->colony;
						$t_provider->postalCode		= $old_provider->postalCode;
						$t_provider->city			= $old_provider->city;
						$t_provider->state_idstate	= $old_provider->state_idstate;
						$t_provider->provider_data_id = $provider_data_id;
						$t_provider->save();

						$idProvider 				= $t_provider->idProvider;

						if($old_provider->accounts()->exists())
						{
							foreach ($old_provider->accounts as $accountProv) 
							{
								$checkAccounts = App\ProviderBanks::where('banks_idBanks',$accountProv->idBanks)
								->where('alias',$accountProv->alias)
								->where('account',$accountProv->account)
								->where('branch',$accountProv->branch)
								->where('reference',$accountProv->reference)
								->where('clabe',$accountProv->clabe)
								->where('currency',$accountProv->currency)
								->where('agreement',$accountProv->agreement)
								->where('visible',1)
								->where('provider_data_id',$provider_data_id)
								->count();

								if ($checkAccounts == 0) 
								{
									$t_providerBank							= new App\ProviderBanks;
									$t_providerBank->provider_idProvider	= $idProvider;
									$t_providerBank->banks_idBanks			= $accountProv->idBanks;
									$t_providerBank->alias 					= $accountProv->alias;
									$t_providerBank->account				= $accountProv->account;
									$t_providerBank->branch					= $accountProv->branch;
									$t_providerBank->reference				= $accountProv->reference;
									$t_providerBank->clabe					= $accountProv->clabe;
									$t_providerBank->currency				= $accountProv->currency;
									$t_providerBank->agreement				= $accountProv->agreement;
									$t_providerBank->iban 					= $accountProv->iban;
									$t_providerBank->bic_swift 				= $accountProv->bic_swift;
									$t_providerBank->visible 				= 1;
									$t_providerBank->provider_data_id 		= $provider_data_id;
									$t_providerBank->save();
								}

							}
							$idProviderAccount = null;
						}
						else
						{
							$idProviderAccount = null;
						}
					}
					else
					{
						$t_provider_data 			= new App\ProviderData();
						$t_provider_data->users_id 	= Auth::user()->id;
						$t_provider_data->save();

						$provider_data_id 			= $t_provider_data->id;


						$t_provider					= new App\Provider();
						$t_provider->businessName	= $old_provider->businessName;
						$t_provider->beneficiary	= $old_provider->beneficiary;
						$t_provider->phone			= $old_provider->phone;
						$t_provider->rfc			= $old_provider->rfc;
						$t_provider->contact		= $old_provider->contact;
						$t_provider->commentaries	= $old_provider->commentaries;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $old_provider->address;
						$t_provider->number			= $old_provider->number;
						$t_provider->colony			= $old_provider->colony;
						$t_provider->postalCode		= $old_provider->postalCode;
						$t_provider->city			= $old_provider->city;
						$t_provider->state_idstate	= $old_provider->state_idstate;
						$t_provider->provider_data_id = $provider_data_id;
						$t_provider->save();

						$idProvider 				= $t_provider->idProvider;

						if($old_provider->accounts()->exists())
						{
							foreach ($old_provider->accounts as $accountProv) 
							{
								$t_providerBank							= new App\ProviderBanks;
								$t_providerBank->provider_idProvider	= $idProvider;
								$t_providerBank->banks_idBanks			= $accountProv->idBanks;
								$t_providerBank->alias 					= $accountProv->alias;
								$t_providerBank->account				= $accountProv->account;
								$t_providerBank->branch					= $accountProv->branch;
								$t_providerBank->reference				= $accountProv->reference;
								$t_providerBank->clabe					= $accountProv->clabe;
								$t_providerBank->currency				= $accountProv->currency;
								$t_providerBank->agreement				= $accountProv->agreement;
								$t_providerBank->iban 					= $accountProv->iban;
								$t_providerBank->bic_swift 				= $accountProv->bic_swift;
								$t_providerBank->visible 				= 1;
								$t_providerBank->provider_data_id 		= $provider_data_id;
								$t_providerBank->save();
							}
							$idProviderAccount = $t_provider->providerBank->first()->id;
						}
						else
						{
							$idProviderAccount = null;
						}

					}
					$new_request				= new App\RequestModel();
					$new_request->kind			= 1;
					$new_request->fDate			= Carbon::now();
					$new_request->status		= 2;
					$new_request->idRequest		= $t_request->idCheck;
					$new_request->idElaborate	= $t_request->idCheck;
					$new_request->idProject 	= $t_request->idProject;
					$new_request->remittance 	= 1;
					$new_request->idRequisition = $t_request->folio;
					$new_request->new_folio 	= $t_request->folio.'-'.$countRequest;

					switch ($t_request->requisition->requisition_type) 
					{
						case 1:
						case 5:
							$new_request->goToWarehouse = 1;
							break;

						case 2:
						case 3:
						case 4:
							$new_request->goToWarehouse = 0;
							break;
						
						default:
							$new_request->goToWarehouse = 0;
							break;
					}
					$new_request->save();

					$t_purchase							= new App\Purchase();
					$t_purchase->title 					= $t_request->requisition->title;
					$t_purchase->datetitle 				= $t_request->fDate;
					$t_purchase->numberOrder 			= '';
					$t_purchase->idFolio				= $new_request->folio;
					$t_purchase->idKind					= $new_request->kind;
					$t_purchase->notes					= '';
					$t_purchase->discount				= '';
					$t_purchase->idProvider				= $idProvider;
					$t_purchase->provider_has_banks_id 	= $idProviderAccount;
					$t_purchase->idRequisition 			= $t_request->folio;
					$t_purchase->provider_data_id 		= $provider_data_id;
					$t_purchase->save();

					$idPurchase = $t_purchase->idPurchase;
					if (App\RequisitionHasProviderDocuments::where('idRequisitionHasProvider',$request->idRequisitionHasProvider[$i])->count()>0) 
					{
						foreach (App\RequisitionHasProviderDocuments::where('idRequisitionHasProvider',$request->idRequisitionHasProvider[$i])->get() as $doc)
						{
							$existsDocReq = \Storage::disk('public')->exists('/docs/requisition/'.$doc->path);
							if ($existsDocReq) 
							{
								$existsDoc = \Storage::disk('public')->exists('/docs/purchase/'.$doc->path);
								if(!$existsDoc)
								{
									$destinity				= '/docs/purchase/'.$doc->path;
									\Storage::disk('public')->copy('/docs/requisition/'.$doc->path,$destinity);
								}
								$documents 					= new App\DocumentsPurchase();
								$documents->path 			= $doc->path;
								$documents->idPurchase 		= $idPurchase;
								$documents->name 			= $doc->name;
								$documents->save();
							}

						}
					}

					foreach($t_request->requisition->details->where('idRequisitionHasProvider',$request->idRequisitionHasProvider[$i]) as $detail)
					{
						
						$price		= $detail->priceWin($request->idRequisitionHasProvider[$i])->first();
						$iva		= (App\Parameter::where('parameter_name','IVA')->first()->parameter_value)/100;
						$iva2		= (App\Parameter::where('parameter_name','IVA2')->first()->parameter_value)/100;
						$ivaCalc	= 0;

						$typeTax = $price->typeTax;

						switch($typeTax)
						{
							case 'no':
								$ivaCalc = 0;
								break;
							case 'a':
								$ivaCalc = $detail->quantity*$price->unitPrice*$iva;
								break;
							case 'b':
								$ivaCalc = $detail->quantity*$price->unitPrice*$iva2;
								break;
						}

						$t_detailPurchase				= new App\DetailPurchase();
						$t_detailPurchase->idPurchase	= $idPurchase;
						$t_detailPurchase->quantity		= $detail->quantity;
						$t_detailPurchase->unit			= $detail->unit;
						$t_detailPurchase->description	= $detail->name.' - '.$detail->description;
						$t_detailPurchase->unit			= $detail->unit;
						
						$t_detailPurchase->unitPrice	= $price->unitPrice;
						$t_detailPurchase->tax			= $ivaCalc;
						$t_detailPurchase->discount		= 0;
						$t_detailPurchase->amount		= $price->total;
						$t_detailPurchase->typeTax		= $price->typeTax;
						$t_detailPurchase->subtotal		= $detail->quantity * $price->unitPrice;
						$t_detailPurchase->category		= $detail->category;
						$t_detailPurchase->commentaries = $detail->description;
						$t_detailPurchase->code 		= $detail->code;
						$t_detailPurchase->measurement 	= $detail->measurement;
						$t_detailPurchase->save();


						if ($price->taxesData()->exists()) 
						{
							foreach($price->taxesData as $tax)
							{ 
								$t_taxes					= new App\TaxesPurchase();
								$t_taxes->name				= $tax->name;
								$t_taxes->amount			= $tax->amount;
								$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
								$t_taxes->save();
							}
						}

						if ($price->retentionsData()->exists()) 
						{
							foreach($price->retentionsData as $retention)
							{ 
								$t_retentions					= new App\RetentionPurchase();
								$t_retentions->name				= $retention->name;
								$t_retentions->amount			= $retention->amount;
								$t_retentions->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
								$t_retentions->save();
							}
						}

						$type_currency = $detail->type_currency;
					}

					$sumPurchase                = App\Purchase::find($idPurchase);
					$sumPurchase->subtotales	= $sumPurchase->detailPurchase->sum('subtotal');
					$sumPurchase->tax			= $sumPurchase->detailPurchase->sum('tax');
					$sumPurchase->amount		= $sumPurchase->detailPurchase->sum('amount');
					$sumPurchase->typeCurrency 	= $type_currency;
					$sumPurchase->save();

					if ($t_request->requisition->documents()->exists()) 
					{
						foreach ($t_request->requisition->documents as $doc)
						{
							$existsDocReq = \Storage::disk('public')->exists('/docs/requisition/'.$doc->path);
							if ($existsDocReq) 
							{
								$existsDocPurchase = \Storage::disk('public')->exists('/docs/purchase/'.$doc->path);
								if(!$existsDocPurchase)
								{
									$destinity				= '/docs/purchase/'.$doc->path;
									\Storage::disk('public')->copy('/docs/requisition/'.$doc->path,$destinity);
								}
								$documents					= new App\DocumentsPurchase();
								$documents->path			= $doc->path;
								$documents->idPurchase		= $idPurchase;
								$documents->fiscal_folio	= $doc->fiscal_folio;
								$documents->datepath		= $doc->datepath;
								$documents->timepath		= $doc->timepath;
								$documents->ticket_number	= $doc->ticket_number;
								$documents->amount			= $doc->amount;
								$documents->name			= $doc->name;
								$documents->save();
							}

						}
					}

					$countRequest++;
				}

				if ($request->$typeRequest == 9) 
				{
					$new_request				= new App\RequestModel();
					$new_request->kind			= 9;
					$new_request->fDate			= Carbon::now();
					$new_request->status		= 2;
					$new_request->idRequest		= $t_request->idCheck;
					$new_request->idElaborate	= $t_request->idCheck;
					$new_request->idProject 	= $t_request->idProject;
					$new_request->remittance 	= 1;
					$new_request->idRequisition = $t_request->folio;
					$new_request->new_folio 	= $t_request->folio.'-'.$countRequest;
					$new_request->save();

					$t_refund					= new App\Refund();
					$t_refund->title 			= $t_request->requisition->title;
					$t_refund->datetitle 		= $t_request->fDate;
					$t_refund->idFolio			= $new_request->folio;
					$t_refund->idKind			= $new_request->kind;
					$t_refund->idRequisition 	= $t_request->folio;
					$t_refund->save();

					$idRefund = $t_refund->idRefund;
					foreach($t_request->requisition->details->where('idRequisitionHasProvider',$request->idRequisitionHasProvider[$i]) as $detail)
					{
						
						$price		= $detail->priceWin($request->idRequisitionHasProvider[$i])->first();
						$iva		= (App\Parameter::where('parameter_name','IVA')->first()->parameter_value)/100;
						$iva2		= (App\Parameter::where('parameter_name','IVA2')->first()->parameter_value)/100;
						$ivaCalc	= 0;

						$typeTax = $price->typeTax;

						switch($typeTax)
						{
							case 'no':
								$ivaCalc = 0;
								break;
							case 'a':
								$ivaCalc = $detail->quantity*$price->unitPrice*$iva;
								break;
							case 'b':
								$ivaCalc = $detail->quantity*$price->unitPrice*$iva2;
								break;
						}
						$t_detailRefund				= new App\RefundDetail();
						$t_detailRefund->idRefund	= $idRefund;
						$t_detailRefund->document	= '';
						$t_detailRefund->concept	= $detail->name.' - '.$detail->description;
						$t_detailRefund->quantity	= $detail->quantity;
						$t_detailRefund->amount		= $detail->quantity * $price->unitPrice;
						$t_detailRefund->idAccount	= $t_request->account;	
						$t_detailRefund->taxPayment	= $typeTax == "no" ? 0 : 1;
						$t_detailRefund->tax		= $ivaCalc;
						$t_detailRefund->sAmount	= $price->total;
						$t_detailRefund->typeTax	= $typeTax;
						$t_detailRefund->category	= $detail->category;
						$t_detailRefund->code 		= $detail->code;
						$t_detailRefund->measurement= $detail->measurement;
						$t_detailRefund->unit 		= $detail->unit;
						$t_detailRefund->save();

						if ($price->taxesData()->exists()) 
						{
							foreach($price->taxesData as $tax)
							{ 
								$t_taxes					= new App\TaxesRefund();
								$t_taxes->name				= $tax->name;
								$t_taxes->amount			= $tax->amount;
								$t_taxes->idRefundDetail	= $t_detailRefund->idRefundDetail;
								$t_taxes->save();
							}
						}

						$type_currency = $detail->type_currency;
					}

					$sumRefund				= App\Refund::find($idRefund);
					$sumRefund->total		= $sumRefund->refundDetail->sum('sAmount');
					$sumRefund->currency	= $type_currency;
					$sumRefund->save();

					$countRequest++;
				}
			}

			$alert	= "swal('','Las solicitudes fueron generadas exitosamente', 'success');";
			return searchRedirect(232, $alert, 'administration/requisition/authorization');
		}
	}

	public function export(Request $request,$id)
	{
		
		$t_request = App\RequestModel::find($id);

		$select = 'cat_warehouse_types.description as category,
				requisition_details.part as part,
				requisition_details.quantity as quantity,
				requisition_details.measurement as measurement,
				requisition_details.unit as unit,
				requisition_details.code as code,
				requisition_details.name as name,
				requisition_details.description as description,
				requisition_details.exists_warehouse as exists_warehouse,
				';
		foreach ($t_request->requisition->requisitionHasProvider->pluck('id') as $prov)
		{
			$select .= '
				p'.$prov.'.unitPrice as unitPrice'.$prov.',
				p'.$prov.'.subtotal as subtotal'.$prov.',
				p'.$prov.'.iva as iva'.$prov.',
				p'.$prov.'.taxes as taxes'.$prov.',
				p'.$prov.'.retentions as retentions'.$prov.',
				p'.$prov.'.total as total'.$prov.',
				IF(p'.$prov.'.idRequisitionHasProvider = requisition_details.idRequisitionHasProvider,1,0) as selectable'.$prov.',
				';
		}
		$select .= 'CONCAT(" ") as blank';

		$result = App\RequestModel::selectRaw($select)
			->leftJoin('requisitions','requisitions.idFolio','request_models.folio')
			->leftJoin('requisition_details','requisitions.id','requisition_details.idRequisition');
		
		foreach ($t_request->requisition->requisitionHasProvider->pluck('id') as $prov)
		{
			$result = $result->leftJoin('provider_secondary_prices as p'.$prov, function ($join) use($prov)
			{
				$join->on('requisition_details.id','=','p'.$prov.'.idRequisitionDetail')
					->where('p'.$prov.'.idRequisitionHasProvider','=',$prov);
			});
		}
		$result = $result->leftJoin('cat_warehouse_types','requisition_details.category','cat_warehouse_types.id')
			->where('request_models.folio',$id)
			->groupBy('requisition_details.id')
			->get();
		$header1 = ['Folio',$t_request->folio];
		$header2 = ['Fecha de Solicitud',$t_request->requisition->date_request];
		$header3 = ['Solicitante',$t_request->requestUser->fullName()];
		
		$titles = ['ARTÍCULOS','','','','','','','','',];


		Excel::create('Requisición #'.$id, function($excel) use ($t_request,$header1,$header2,$header3,$titles,$result)
		{	
			$excel->sheet('Requisición',function($sheet) use ($t_request,$header1,$header2,$header3,$titles,$result)
			{
				$sheet->setStyle([
						'font' => [
							'name'	=> 'Helvetica',
							'size'	=> 12
						],
						'alignment' => [
							'vertical' => 'center',
						]
				]);


				$sheet->setHeight(array(
					1     =>  20,
					2     =>  20,
					3     =>  20,
					4     =>  20,
					5     =>  20,
					6     =>  20,
					7     =>  28,
				));

				$subtitles = [
						'categoría',
						'partida',
						'cantidad',
						'medida',
						'unidad',
						'código',
						'nombre',
						'descripción',
						'existencia_en_almacen',
					];

					

				if($t_request->requisition->requisitionHasProvider()->exists())
				{
					foreach($t_request->requisition->requisitionHasProvider as $provider)
					{
						array_push($subtitles,'precio_unitario');
						array_push($subtitles,'subtotal');
						array_push($subtitles,'iva');
						array_push($subtitles,'impuesto_adicional');
						array_push($subtitles,'retenciones');
						array_push($subtitles,'total');
						array_push($subtitles,'comentarios');
					}
				}

				$sheet->mergeCells('A7:I7');

				$initRange	= 10;
				$providerHeadersRange = [];
				if($t_request->requisition->requisitionHasProvider()->exists())
				{
					$endRange	= 10; // I
					foreach($t_request->requisition->requisitionHasProvider as $provider)
					{
						$id = $provider->id;
						array_push($titles,$provider->providerData->businessName);
						$endRange++;
						for ($i=0; $i < 6; $i++) 
						{ 
							array_push($titles,'');
							$endRange++;
						}
						$selectable = false;
						if ($t_request->status == 5 || $t_request->status == 17) 
						{
							
							foreach ($result as $data) {
								if ($data['selectable'.$id] == 1) 
								{
									$selectable = true;
								}
							}
						}
						if($selectable)
						{
							$providerHeadersRange[] = ["selectable" => true,
							"range1" => App\Http\Controllers\AdministracionRequisicionController::serie($initRange).'7:'.''.App\Http\Controllers\AdministracionRequisicionController::serie($endRange-1).'7',
							"range2" => App\Http\Controllers\AdministracionRequisicionController::serie($initRange).'8:'.''.App\Http\Controllers\AdministracionRequisicionController::serie($endRange-1).'8'
						];
						}
						else
						{
							$providerHeadersRange[] = ["selectable" => false,
							"range1" => App\Http\Controllers\AdministracionRequisicionController::serie($initRange).'7:'.''.App\Http\Controllers\AdministracionRequisicionController::serie($endRange-1).'7',
							"range2" => App\Http\Controllers\AdministracionRequisicionController::serie($initRange).'8:'.''.App\Http\Controllers\AdministracionRequisicionController::serie($endRange-1).'8'
						];
						}

						$sheet->mergeCells(App\Http\Controllers\AdministracionRequisicionController::serie($initRange).'7:'.''.App\Http\Controllers\AdministracionRequisicionController::serie($endRange-1).'7');
						$initRange = $endRange;
					}
				}

				
				$sheet->row(1, ['']);
				$sheet->row(2, $header1);
				$sheet->row(3, $header2);
				$sheet->row(4, $header3);
				$sheet->row(5, ['']);
				$sheet->row(6, ['']);
				$sheet->row(7, $titles);
				$sheet->row(8, $subtitles);


				


				$sheet->setAutoSize(true);

				for ($i=1; $i <= 6; $i++) { 
					$sheet->row($i, function($row) {
						$row->setBackground('#FFFFFF');
						$row->setBorder('none', 'none', 'none', 'none');
					});
				}

				$sheet->cell('A2:A5',function($cells){
					$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
				});
				$sheet->cell('B2:B5',function($cells){
					$cells->setAlignment('left');
					$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => false));
				});
				
				$rangeHeaders		= 'A7:'.App\Http\Controllers\AdministracionRequisicionController::serie($initRange-1).'7';
				$rangeSubheaders	= 'A8:'.App\Http\Controllers\AdministracionRequisicionController::serie($initRange-1).'8';

				$sheet->cell($rangeHeaders, function($cells) 
				{
					$cells->setAlignment('center');
					$cells->setBackground('#ed704d');
					$cells->setFontColor('#ffffff');
					$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
				});

				$sheet->cell($rangeSubheaders, function($cells) 
				{
					$cells->setAlignment('center');
					$cells->setBackground('#eeb8a3');
					$cells->setFontColor('#FFFFFF');
					$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
				});

				foreach ($providerHeadersRange as $p) {
					if($p["selectable"])
					{
						$sheet->cell($p["range1"], function($cells) 
						{
							$cells->setAlignment('center');
							$cells->setBackground('#77b11a');
							$cells->setFontColor('#ffffff');
							$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
						});
						$sheet->cell($p["range2"], function($cells) 
						{
							$cells->setAlignment('center');
							$cells->setBackground('#92d050');
							$cells->setFontColor('#ffffff');
							$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
						});
					}
					else
					{
						$sheet->cell($p["range1"], function($cells) 
						{
							$cells->setAlignment('center');
							$cells->setBackground('#e4a905');
							$cells->setFontColor('#ffffff');
							$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
						});
						$sheet->cell($p["range2"], function($cells) 
						{
							$cells->setAlignment('center');
							$cells->setBackground('#f4d797');
							$cells->setFontColor('#ffffff');
							$cells->setFont(array('family' => 'Helvetica','size' => '12','bold' => true));
						});
					}
				}

				$countRow = 9;
				foreach ($result as $data) 
				{
					$row	= [];
					$row[]	= $data['category'];
					$row[]	= $data['part'];
					$row[]	= $data['quantity'];
					$row[]	= $data['measurement'];
					$row[]	= $data['unit'];
					$row[]	= $data['code'];
					$row[]	= $data['name'];
					$row[]	= $data['description'];
					$row[]	= $data['exists_warehouse'];

					$tempInit	= 9;
					$tempEnd 	= 9;
					$mergeComent = [];
					foreach ($t_request->requisition->requisitionHasProvider as $prov)
					{
						$id = $prov->id;
						
						$tempInit	= $tempEnd+1;
						$row[]		= $data['unitPrice'.$id];
						$row[]		= $data['subtotal'.$id];
						$row[]		= $data['iva'.$id];
						$row[]		= $data['taxes'.$id];
						$row[]		= $data['retentions'.$id];
						$row[]		= $data['total'.$id];
						$row[]		= $prov->commentaries.' ';

						$tempEnd = $tempInit+6;

						$mergeComent[] = $tempEnd;

						if ($t_request->status == 5 || $t_request->status == 17) 
						{
							if ($data['selectable'.$id] == 1) 
							{
								$rangeData = App\Http\Controllers\AdministracionRequisicionController::serie($tempInit).''.$countRow.':'.App\Http\Controllers\AdministracionRequisicionController::serie($tempEnd).''.$countRow;
							}
						}
					}
					$rangeL = App\Http\Controllers\AdministracionRequisicionController::serie(10).''.':'.App\Http\Controllers\AdministracionRequisicionController::serie($tempEnd).'';
					$sheet->setColumnFormat(array($rangeL => '"$"#,##0.00_-'));
					$sheet->appendRow($row);

					if (isset($rangeData)) 
					{
						$sheet->cell($rangeData, function($cells) 
						{
							$cells->setBackground('#ebf1de');
						});
					}
					$countRow++;
				}
				
				
				foreach ($mergeComent as $key => $value) {
					$sheet->mergeCells(App\Http\Controllers\AdministracionRequisicionController::serie($value).'9:'.App\Http\Controllers\AdministracionRequisicionController::serie($value).$sheet->getHighestRow());
				}

				

			});
		})->export('xlsx');
	}

	public function exportPdf(Request $request,$id)
	{
		$t_request = App\RequestModel::find($id);

		$select = 'cat_warehouse_types.description as category,
					requisition_details.part as part,
					requisition_details.quantity as quantity,
					requisition_details.unit as unit,
					requisition_details.measurement as measurement,
					requisition_details.code as code,
					requisition_details.name as name,
					requisition_details.description as description,
					requisition_details.exists_warehouse as exists_warehouse,
				';
		foreach ($t_request->requisition->requisitionHasProvider->pluck('id') as $prov)
		{
			$select .= '
				projects.proyectName,
				requisitions.urgent,
				requisitions.title,
				requisition_details.brand,
				requisition_details.model,
				requisition_details.usage_time,
				requisition_details.period,
				cat_code_w_bs.code_wbs,
				cat_code_e_d_ts.description as edtDescription,
				cat_code_e_d_ts.code as edtCode,
				requisition_details.cat_procurement_material_id,
				requisition_details.idRequisitionHasProvider,
				p'.$prov.'.unitPrice as unitPrice'.$prov.',
				p'.$prov.'.subtotal as subtotal'.$prov.',
				p'.$prov.'.iva as iva'.$prov.',
				p'.$prov.'.taxes as taxes'.$prov.',
				p'.$prov.'.retentions as retentions'.$prov.',
				p'.$prov.'.total as total'.$prov.',
				IF(p'.$prov.'.idRequisitionHasProvider = requisition_details.idRequisitionHasProvider,1,0) as selectable'.$prov.',
				';
		}
		$select .= 'CONCAT(" ") as blank';

		$result = App\RequestModel::selectRaw($select)
			->leftJoin('requisitions','requisitions.idFolio','request_models.folio')
			->leftJoin('cat_code_w_bs','requisitions.code_wbs','cat_code_w_bs.id')
			->leftJoin('cat_code_e_d_ts','requisitions.code_edt','cat_code_e_d_ts.id')
			->leftJoin('projects','request_models.idProject','projects.idproyect')
			->leftJoin('requisition_details','requisitions.id','requisition_details.idRequisition');
			
		foreach ($t_request->requisition->requisitionHasProvider->pluck('id') as $prov)
		{
			$result = $result->leftJoin('provider_secondary_prices as p'.$prov, function ($join) use($prov)
			{
				$join->on('requisition_details.id','=','p'.$prov.'.idRequisitionDetail')
					->where('p'.$prov.'.idRequisitionHasProvider','=',$prov);
			});
		}
		$result = $result->leftJoin('cat_warehouse_types','requisition_details.category','cat_warehouse_types.id')
			->where('request_models.folio',$id)
			->groupBy('requisition_details.id')
			->get();

		//return $result;

		$pdf = \App::make('dompdf.wrapper');
		$pdf->getDomPDF()->set_option('enable_php', true);

		$pdf->loadView('administracion.requisicion.pdf',[
			't_request' => $t_request, 
			'result'    => $result,
		])->setPaper('a4', 'landscape');

		return $pdf->download('Requisición #'.$id.'.pdf');

	}

	public function serie($num)
	{
		$result	= '';
		$prev	= $num/26;
		if($prev>1)
		{
			$prev	= floor($prev);
			$res	= $num%26;
			if($res == 0)
			{
				$prev	= $prev - 1;
			}
			$result	= chr(substr("000".($prev+64),-3));
			$num	= $num - ($prev*26);
		}
		$result	.= chr(substr("000".($num+64),-3));
		return $result;
	}

	public function searchProvider(Request $request)
	{
		if ($request->ajax()) 
		{
			$providers	= App\ProviderSecondary::where(function($query) use ($request)
						{
							$query->where('rfc','LIKE','%'.$request->text.'%')
								->orWhere('businessName','LIKE','%'.$request->text.'%');
						})
						->where(function($query) use ($request)
						{
							if (isset($request->idProvider) && count($request->idProvider)>0) 
							{
								$query->whereNotIn('id',$request->idProvider);
							}
						})
						->where('status',2)
						->paginate(10);

			$folio = $request->folio;

			return view('administracion.requisicion.parcial.resultado_proveedor',
			[
				'folio'		=> $folio,
				'providers'	=> $providers
			]);
		}
	}

	public function editProvider(Request $request)
	{
		if ($request->ajax()) 
		{
			$provider		= App\ProviderSecondary::find($request->provider_id);
			$requisition_id	= $request->requisition_id;
			$folio			= $request->folio;
			return view('administracion.requisicion.parcial.edicion_proveedor',
			[
				'provider'			=> $provider,
				'requisition_id'	=> $requisition_id,
				'folio'				=> $folio
			]);
		}
	}

	public function updateProviderSecondary(Request $request)
	{
		if ($request->ajax())
		{
			$provider 					= App\ProviderSecondary::find($request->idProvider);
			$provider->businessName		= $request->businessName;
			$provider->rfc				= $request->rfc;
			$provider->phone			= $request->phone;
			$provider->contact			= $request->contact;
			$provider->beneficiary		= $request->beneficiary;
			$provider->commentaries		= $request->commentaries;
			$provider->address			= $request->address;
			$provider->number			= $request->number;
			$provider->colony			= $request->colony;
			$provider->postalCode		= $request->postalCode;
			$provider->city				= $request->city;
			$provider->state_idstate	= $request->state_idstate;
			$provider->users_id			= Auth::user()->id;
			$provider->status 			= 2;
			$provider->save();

			if (isset($request->delete_account) && count($request->delete_account)>0) 
			{
				App\ProviderSecondaryAccounts::whereIn('id',$request->delete_account)->update(['visible'=>0]);
			}

			if (isset($request->alias) && count($request->alias)>0) 
			{

				for ($i=0; $i < count($request->alias); $i++) 
				{
					$t_providerBank							= new App\ProviderSecondaryAccounts();
					$t_providerBank->idProviderSecondary	= $provider->id;
					$t_providerBank->idBanks				= $request->idBanks[$i];
					$t_providerBank->alias					= $request->alias[$i];
					$t_providerBank->account				= $request->account[$i];
					$t_providerBank->branch					= $request->branch[$i];
					$t_providerBank->reference				= $request->reference[$i];
					$t_providerBank->clabe					= $request->clabe[$i];
					$t_providerBank->currency				= $request->currency[$i];
					$t_providerBank->iban					= $request->iban[$i];
					$t_providerBank->bic_swift				= $request->bic_swift[$i];
					$t_providerBank->agreement				= $request->agreement[$i];
					$t_providerBank->save();
				}
			}

			return Response('Exito');

		}
	}

	public function validationProvider(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array(
				'valid'		=> false,
				'class' 	=> 'error',
				'message'	=> 'Error.'
			);
			if(isset($request->reason))
			{
				$exist = App\ProviderSecondary::where('businessName','LIKE',$request->reason)->where('status',2)->count();
				if($exist>0)
				{
					if(isset($request->oldReason) && $request->oldReason != "" && $request->oldReason===$request->reason)
					{
						$response = array('valid' => true,'class'=>'valid','message' => '');
					}
					else
					{
						$response = array(
							'valid'		=> false,
							'class' 	=> 'error',
							'message'	=> 'La razón social ya se encuentra registrada.'
						);
					}
				}
				else
				{
					$response = array('valid' => true,'class'=>'valid','message' => '');
				}
				return Response($response);
			}

			if(isset($request->rfc))
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$exist = App\ProviderSecondary::where('rfc','LIKE',$request->rfc)->where('status',2)->count();
					if($exist>0)
					{
						
						if(isset($request->oldRfc) && $request->oldRfc != "" && $request->oldRfc===$request->rfc)
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
						$response = array('valid' => true,'class'=>'valid','message' => '');
					}				
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El RFC debe ser válido.'
					);
				}
				return Response($response);
			}
		}
	}

	public function rfcValidate(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array(
				'valid'		=> false,
				'class'		=> 'error',
				'message'	=> 'El campo es requerido.'
			);
			if(isset($request->rfc))
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$exist 	= App\RequisitionEmployee::leftJoin('requisitions','requisition_employees.requisition_id','requisitions.id')
						->leftJoin('request_models','requisitions.idFolio','request_models.folio')
						->select('requisition_employees.rfc')
						->whereNotIn('request_models.status',[5,6,7,23,28])
						->where('requisition_employees.rfc',$request->rfc)
						->where(function($query) use($request)
						{
							if (isset($request->folio) && $request->folio != "") 
							{
								$query->where('request_models.folio','!=',$request->folio);
							}
						})
						->get();
					if(count($exist)>0 && $request->oldRfc != $request->rfc)
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
								'message'	=> 'El RFC ya se ha registrado a una requisición anteriormente.',
							);
						}
					}
					else
					{
						$response = array('valid' => true,'class'=>'valid','message' => '');
					}				
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El RFC debe ser válido.'
					);
				}
			}
			return Response($response);
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
			if($request->realPathRequisition != '')
			{
				for ($i=0; $i < count($request->realPathRequisition); $i++) 
				{
					\Storage::disk('public')->delete('/docs/requisition/'.$request->realPathRequisition[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_requisitionDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/requisition/'.$name;
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

	public function viewDocumentsProvider(Request $request)
	{
		if ($request->ajax()) 
		{
			$requisitionHasProvider = App\RequisitionHasProvider::find($request->id);
			return view('administracion.requisicion.parcial.ver_documentos',['requisitionHasProvider'=>$requisitionHasProvider]);
		}
	}

	public function storeDocumentsProvider(Request $request,$id)
	{
		if (Auth::user()->module->whereIn('id',[231,232])->count()>0) 
		{
			$t_request = App\RequestModel::find($id);

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name							= Files::rename($request->realPath[$i],$t_request->folio);
						$documents								= new App\RequisitionHasProviderDocuments();
						$documents->name						= $request->nameDocument[$i];
						$documents->path						= $new_file_name;
						$documents->idRequisitionHasProvider	= $request->idRequisitionHasProviderDoc;
						$documents->user_id						= Auth::user()->id;
						$documents->save();
					}
				}
			}

			if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
			{
				for ($i=0; $i < count($request->realPathRequisition); $i++) 
				{
					if ($request->realPathRequisition[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
						$documents					= new App\RequisitionDocuments();
						$documents->name			= $request->nameDocumentRequisition[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->path			= $new_file_name;
						$documents->idRequisition	= $t_request->requisition->id;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}

			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
				{
					$commentaries 	= 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
					$type_currency 	= 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

					$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
					$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
					$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
					$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
					
					$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
					$requisitionHasProvider->commentaries  = $request->$commentaries;
					$requisitionHasProvider->type_currency = $request->$type_currency;

					$requisitionHasProvider->delivery_time = $request->$delivery_time;
					$requisitionHasProvider->credit_time   = $request->$credit_time;
					$requisitionHasProvider->guarantee     = $request->$guarantee;
					$requisitionHasProvider->spare         = $request->$spare;
					
					$requisitionHasProvider->save();
				}
			}

			if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
					if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
					{
						$detail->exists_warehouse	= $request->exists_warehouse[$ird];
						$detail->save();
					}
				}
			}

			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					for ($ips=0; $ips < count($request->idRequisitionHasProvider); $ips++) 
					{ 
						$unitPrice					= 'unitPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$subtotal					= 'subtotal_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$typeTax					= 'typeTax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$iva						= 'iva_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$total						= 'total_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$idProviderSecondaryPrice	= 'idProviderSecondaryPrice_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if ($request->$idProviderSecondaryPrice == "x") 
						{
							$providerPrice	= new App\ProviderSecondaryPrice();
						}
						else
						{
							$providerPrice	= App\ProviderSecondaryPrice::find($request->$idProviderSecondaryPrice);
						}
						$providerPrice->unitPrice			= $request->$unitPrice;
						$providerPrice->subtotal			= $request->$subtotal;
						$providerPrice->typeTax				= $request->$typeTax;
						$providerPrice->iva 				= $request->$iva;
						$providerPrice->total				= $request->$total;
						$providerPrice->user_id				= Auth::user()->id;
						$providerPrice->idRequisitionDetail	= $request->idRequisitionDetail[$ird];
						$providerPrice->idRequisitionHasProvider	= $request->idRequisitionHasProvider[$ips];
						$providerPrice->save();

						$name_add_tax	= 'name_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_tax	= 'amount_add_tax_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$name_add_ret	= 'name_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$amount_add_ret	= 'amount_add_ret_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$tax_id			= 'tax_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];
						$ret_id			= 'ret_id_'.$request->idRequisitionDetail[$ird].'_'.$request->idRequisitionHasProvider[$ips];

						if (isset($request->deleteTaxes) && count($request->deleteTaxes)>0) 
						{
							$deleteTaxes = App\ProviderSecondaryPriceTaxes::whereIn('id',$request->deleteTaxes)->delete();
						}

						if (isset($request->$name_add_tax) && count($request->$name_add_tax)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_tax); $t++) 
							{ 
								if ($request->$name_add_tax[$t] != "" && $request->$amount_add_tax[$t] != "") 
								{
									if ($request->$tax_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$tax_id[$t]);
									}
									
									$tax->name						= $request->$name_add_tax[$t];
									$tax->amount					= $request->$amount_add_tax[$t];
									$tax->type						= 1;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}

						if (isset($request->$name_add_ret) && count($request->$name_add_ret)>0 ) 
						{
							for ($t=0; $t < count($request->$name_add_ret); $t++) 
							{ 
								if ($request->$name_add_ret[$t] != "" && $request->$amount_add_ret[$t] != "") 
								{
									if ($request->$ret_id[$t] == "x") 
									{
										$tax = new App\ProviderSecondaryPriceTaxes();
									}
									else
									{
										$tax =  App\ProviderSecondaryPriceTaxes::find($request->$ret_id[$t]);
									}
									$tax->name						= $request->$name_add_ret[$t];
									$tax->amount					= $request->$amount_add_ret[$t];
									$tax->type						= 2;
									$tax->providerSecondaryPrice_id	= $providerPrice->id;
									$tax->save();
								}
							}
						}
					}
				}
			}

			$alert = "swal('','Documentos Cargados Exitosamente', 'success');";
			switch ($t_request->status) 
			{
				case 3:
					return redirect()->route('requisition.review.show',['id'=>$id])->with('alert',$alert);
					break;

				case 4:
					return redirect()->route('requisition.authorization.show',['id'=>$id])->with('alert',$alert);
					break;
				
				default:
					# code...
					break;
			}
		}
	}

	public function uploadDocuments(Request $request, $id)
	{
		$t_request		= App\RequestModel::find($id);
		$idRequisition	= $t_request->requisition->id;
		$countDocs		= 0;

		if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
		{
			for ($i=0; $i < count($request->realPathRequisition); $i++) 
			{
				if (isset($request->realPathRequisition[$i]) && $request->realPathRequisition[$i] != "") 
				{
					$new_file_name				= Files::rename($request->realPathRequisition[$i],$t_request->folio);
					$documents					= new App\RequisitionDocuments();
					$documents->name			= $request->nameDocumentRequisition[$i];
					$documents->ticket_number	= $request->ticket_number[$i];
					$documents->fiscal_folio	= $request->fiscal_folio[$i];
					$documents->timepath		= $request->timepath[$i];
					$documents->amount			= $request->amount[$i];
					$documents->datepath		= $request->datepath[$i];
					$documents->path			= $new_file_name;
					$documents->idRequisition	= $idRequisition;
					$documents->user_id			= Auth::user()->id;
					$documents->save();
				}
				else
				{
					$countDocs++;
				}
			}
			$alert = "swal('','Documentos cargados exitosamente', 'success')";
		}
		else
		{
			$alert = "swal('','".Lang::get("messages.file_null")."', 'error')";
		}

		return redirect()->route('requisition.edit',['id'=>$id])->with('alert',$alert);
	}

	public function validationCode(Request $request)
	{
		if ($request->ajax()) 
		{
			$code 	 	= $request->code;
			$conceptId 	= $request->conceptId;
			$existsCodeConcept = App\Warehouse::where('short_code',$code)->where('concept',$conceptId)->get();


			$response = [];
			if (count($existsCodeConcept) > 0) 
			{
				$response['validate'] 	= "true";
			}
			else
			{
				$existsCode = App\Warehouse::where('short_code',$code)->get();
				if (count($existsCode)>0) 
				{
					$concepts = "";
					foreach ($existsCode as $data) 
					{
						$concepts .= $data->cat_c->description.', ';
					}
					$response['concepts'] 	= $concepts;
					$response['validate'] 	= "false";
				}
				else
				{
					$response['validate'] 	= "true";
				}
				
			}
			return Response($response);
		}
	}

	public function getEDT(Request $request)
	{
		if ($request->ajax()) {
			$edts = App\CatCodeEDT::where('codewbs_id',$request->code_wbs)->get();
			if (count($edts) > 0) {
				return Response($edts);
			}
		}
	}
	
	//Exportaciones a Excel

	public function exportAuthorization(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			$title_request		= $request->title_request;
			$mindate_request	= $request->mindate_request;
			$maxdate_request	= $request->maxdate_request;
			$mindate_obra		= $request->mindate_obra;
			$maxdate_obra		= $request->maxdate_obra;
			$folio				= $request->folio;
			$user_request		= $request->user_request;
			$project_request 	= $request->project_request;
			$number 			= $request->number;
			$wbs             	= $request->wbs;
			$edt             	= $request->edt;
			$type            	= $request->type;
			$employee 			= $request->employee;
			$status 			= $request->status;
			$new_sheet 			= true;

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('771414')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('db5151')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('21bbbb')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('1a6206')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('requisiciones.xlsx');

			for ($i=1; $i < 7; $i++) 
			{ 	
				switch ($i) 
				{
					case 1:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMaterial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											cat_procurement_materials.name as procurementMaterialType,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->leftJoin('cat_procurement_materials','cat_procurement_materials.id','requisition_details.cat_procurement_material_id')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if(empty(array_diff([4,5],$status)))
											{
												$query->whereIn('request_models.status',[4,5]);
											}
											else 
											{
												$query->whereIn('request_models.status',$status);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMaterial)>0) 
							{
								$new_sheet = false;
								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Tipo','Cant','Medida','Unidad','Nombre','Descripción','Existencia en Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$writer->getCurrentSheet()->setName('Material');

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMaterial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 2:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestServices = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.period as period
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if(empty(array_diff([4,5],$status)))
											{
												$query->whereIn('request_models.status',[4,5]);
											}
											else 
											{
												$query->whereIn('request_models.status',$status);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestServices)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Servicios_Generales');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Servicios_Generales');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Unidad','Nombre','Descripción','Periodo'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestServices as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 3:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestPersonal = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											requisitions.date_obra as date_obra,
											CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisition_employees.name as name,
											requisition_employees.last_name as last_name,
											requisition_employees.scnd_last_name as scnd_last_name,
											requisition_employees.curp as curp,
											requisition_employees.rfc as rfc,
											cat_tax_regimes.description as taxRegime,
											requisition_employees.imss as imss,
											requisition_employees.street as street,
											requisition_employees.number as number,
											requisition_employees.colony as colony,
											requisition_employees.cp as cp,
											requisition_employees.city as city,
											states.description as states_employee,
											requisition_employees.email as email,
											requisition_employees.phone as phone,
											state_work.description as state_work,
											projects.proyectName as proyect_employee,
											cat_code_w_bs.code_wbs as wbs_employee,
											enterprises.name as enterprise_name,
											CONCAT(accounts.account," ",accounts.description) as account_employee,
											directions.name as direction_name,
											departments.name as department_name,
											subdepartments.name as subdepartment_name,
											requisition_employees.position as position,
											requisition_employees.immediate_boss as immediate_boss,
											requisition_employees.position_immediate_boss as position_immediate_boss,
											IF(requisition_employees.status_imss = 1,"Activo","Inactivo") as status_imss,
											requisition_employees.admissionDate as admissionDate,
											requisition_employees.imssDate as imssDate,
											requisition_employees.downDate as downDate,
											requisition_employees.endingDate as endingDate,
											requisition_employees.reentryDate as reentryDate,
											cat_contract_types.description as contract_type,
											cat_regime_types.description as regime_type,
											IF(requisition_employees.workerStatus = 1,"Activo",IF(requisition_employees.workerStatus = 2,"Baja pacial",IF(requisition_employees.workerStatus = 3,"Baja definitiva",IF(requisition_employees.workerStatus = 4,"Suspensión","Boletinado")))) as worker_status,
											requisition_employees.status_reason as status_reason,
											requisition_employees.sdi as sdi,
											cat_periodicities.description as periodicity,
											requisition_employees.employer_register,
											payment_methods.method as paymentWay,
											requisition_employees.netIncome,
											requisition_employees.viatics,
											requisition_employees.camping,
											requisition_employees.complement,
											requisition_employees.fonacot,
											requisition_employees.nomina,
											requisition_employees.bono,
											requisition_employees.infonavitCredit,
											requisition_employees.infonavitDiscount,
											requisition_employees.infonavitDiscountType,
											bank_data.alias,
											cat_banks.description as employee_bank,
											CONCAT(bank_data.clabe," ") as clabe,
											CONCAT(bank_data.account," ") as account,
											CONCAT(bank_data.cardNumber," ") as cardNumber,
											bank_data.branch

										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_employees','requisition_employees.requisition_id','requisitions.id')
										->leftJoin('states','states.idstate','requisition_employees.state_id')
										->leftJoin('states as state_work','state_work.idstate','requisition_employees.state')
										->leftJoin('cat_tax_regimes','cat_tax_regimes.taxRegime','requisition_employees.tax_regime')
										->leftJoin('enterprises','enterprises.id','requisition_employees.enterprise')
										->leftJoin('accounts','accounts.idAccAcc','requisition_employees.account')
										->leftJoin('areas as directions','directions.id','requisition_employees.direction')
										->leftJoin('departments','departments.id','requisition_employees.department')
										->leftJoin('subdepartments','subdepartments.id','requisition_employees.subdepartment_id')
										->leftJoin('cat_contract_types','requisition_employees.workerType','cat_contract_types.id')
										->leftJoin('cat_regime_types','requisition_employees.regime_id','cat_regime_types.id')
										->leftJoin('cat_periodicities','requisition_employees.periodicity','cat_periodicities.c_periodicity')
										->leftJoin('payment_methods','requisition_employees.paymentWay','payment_methods.idpaymentMethod')
										->leftJoin(DB::raw('(SELECT * FROM requisition_employee_accounts WHERE id IN(SELECT MIN(id) as id FROM requisition_employee_accounts GROUP BY idEmployee)) as bank_data'),'requisition_employees.id','bank_data.idEmployee')
										->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$employee)
										{
											if ($employee != "") 
											{
												$query->whereHas('requisition.employees',function($q) use($employee)
												{
													$q->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
												});
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->where(function($query) use ($status)
										{
											if($status != "" && !in_array(1,$status))
											{
												$query->whereIn('request_models.status',$status);
											}
											else
											{ 
												$query->whereNotIn('request_models.status',[4,5])
												->where('request_models.idAuthorize',Auth::user()->id);
											}  
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestPersonal)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Personal');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Personal');
								}

								$headers =
								[
									'folio', 'proyecto', 'wbs', 'fecha_en_obra', 'solicitante', 'titulo', 'nombre', 'apellido', 'apellido2', 'curp', 'rfc', 'regimen_fiscal', 'imss', 'calle', 'numero', 'colonia', 'cp', 'ciudad', 'estado', 'correo_electronico', 'numero_telefonico', 'estado_laboral', 'proyecto', 'wbs', 'empresa', 'clasificacion_gasto', 'direccion', 'departamento', 'subdepartamento', 'puesto', 'jefe_inmediato', 'posicion_jefe_inmediato', 'estatus_imss', 'fecha_ingreso', 'fecha_alta', 'fecha_baja', 'fecha_termino', 'fecha_reingreso', 'tipo_contrato', 'regimen', 'estatus', 'razon_estatus', 'sdi', 'periodicidad', 'registro_patronal', 'forma_pago', 'sueldo_neto', 'viaticos', 'campamento', 'complemento', 'fonacot', 'porcentaje_nomina', 'porcentaje_bono', 'credito_infonavit', 'descuento_infonavit', 'tipo_descuento_infonavit', 'alias', 'banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal'
								];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 5)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
									}
									elseif($k <= 20)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 55)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
									elseif($k <= 61)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestPersonal as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio			= null;
										$request->proyectName	= '';
										$request->wbs			= '';
										$request->date_obra		= '';
										$request->request_user	= '';
										$request->title			= '';
										
									}
									$tmpArr = [];
									foreach($request as $k => $r)
									{
										$tmpArr[] = WriterEntityFactory::createCell($r);
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
							}
						}
						break;	

					case 4:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestSubcontracts = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if(empty(array_diff([4,5],$status)))
											{
												$query->whereIn('request_models.status',[4,5]);
											}
											else 
											{
												$query->whereIn('request_models.status',$status);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestSubcontracts)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Subcontratos');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Subcontratos');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestSubcontracts as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 5:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMachine = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.brand as brand,
											requisition_details.model as model,
											requisition_details.usage_time as usage_time,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if(empty(array_diff([4,5],$status)))
											{
												$query->whereIn('request_models.status',[4,5]);
											}
											else 
											{
												$query->whereIn('request_models.status',$status);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMachine)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Maquinaria');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Maquinaria');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Medida','Unidad','Nombre','Descripción','Marca','Modelo','Tiempo de Utilización','Existencia Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMachine as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 6:
						if($type == "" || ($type != "" && in_array($i,$type)))
						{
							$requestComercial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if(empty(array_diff([4,5],$status)))
											{
												$query->whereIn('request_models.status',[4,5]);
											}
											else 
											{
												$query->whereIn('request_models.status',$status);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestComercial)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Comercial');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Comercial');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestComercial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					default:
						// code...
						break;
				}
			}
			
			return $writer->close();	
					
		}
	}
	
	public function exportAuthorizationComplete(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0) {
			$requests = $this->searchAuthorization($request)->get();	
			$content=array();
			foreach ($requests as $item) {
				$contentRow =array(
					$item->folio,
					$item->requestProject()->exists() ? $item->requestProject->proyectName : 'No hay' ,
					$item->requisition->wbs()->exists() ? $item->requisition->wbs->code_wbs : 'No hay',
					$item->requisition->edt()->exists() ? $item->requisition->edt->fullName() : 'No hay',
					$item->requisition->urgent == 1 ? 'Alta' : 'Baja',
					$item->requestUser()->exists() ? $item->requestUser->fullName() : 'Sin solicitante',
					$item->requisition->title,
					$item->requisition->number,
					$item->requisition->date_request,
					$item->requisition->date_obra,
					$item->requisition->typeRequisition()
				);
					$mixesCells = null;
					$content[]=array('row'=>$contentRow,'mixes'=>$mixesCells);
			}
				$exp		= new ExcelExportClass('Reporte-Requisición-Seguimiento-Completo');
				$objSheet	= new SheetExcel($content,'Requisición');
				$title 	= 'Requisicion';
				$subTitle	=['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Tipo'];
				$objSheet->AddHead($title,$subTitle);
				$exp->AddSheets($objSheet);
				$exp->DownloadExcel();
					
		}
	}

	public function exportReview(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			$title_request		= $request->title_request;
			$mindate_request 	= $request->mindate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_request) : null;
			$maxdate_request 	= $request->maxdate_request != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_request) : null;
			$mindate_obra    	= $request->mindate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->mindate_obra) : null;
			$maxdate_obra    	= $request->maxdate_obra != "" ? Carbon::createFromFormat('d-m-Y', $request->maxdate_obra) : null;
			$folio				= $request->folio;
			$user_request		= $request->user_request;
			$project_request 	= $request->project_request;
			$number 			= $request->number;
			$wbs             	= $request->wbs;
			$edt             	= $request->edt;
			$type            	= $request->type;
			$employee 			= $request->employee;
			$status			 	= $request->status;
			$new_sheet 			= true;

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('771414')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('db5151')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('21bbbb')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('1a6206')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('requisiciones.xlsx');

			if($request->status == '')
			{
				$status = 0;
			}
			for ($i=1; $i < 7; $i++) 
			{ 	
				switch ($i) 
				{
					case 1:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMaterial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											cat_procurement_materials.name as procurementMaterialType,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->leftJoin('cat_procurement_materials','cat_procurement_materials.id','requisition_details.cat_procurement_material_id')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if($status == 0)
											{
												$query->where('request_models.status',3);
											}
											else
											{
												$query->whereNotIn('request_models.status',[2,3])
													->where('request_models.idCheck',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMaterial)>0) 
							{
								$new_sheet = false;
								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Tipo','Cant','Medida','Unidad','Nombre','Descripción','Existencia en Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$writer->getCurrentSheet()->setName('Material');

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMaterial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 2:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestServices = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.period as period
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestServices)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Servicios_Generales');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Servicios_Generales');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Unidad','Nombre','Descripción','Periodo'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestServices as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 3:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestPersonal = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											requisitions.date_obra as date_obra,
											CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisition_employees.name as name,
											requisition_employees.last_name as last_name,
											requisition_employees.scnd_last_name as scnd_last_name,
											requisition_employees.curp as curp,
											requisition_employees.rfc as rfc,
											cat_tax_regimes.description as taxRegime,
											requisition_employees.imss as imss,
											requisition_employees.street as street,
											requisition_employees.number as number,
											requisition_employees.colony as colony,
											requisition_employees.cp as cp,
											requisition_employees.city as city,
											states.description as states_employee,
											requisition_employees.email as email,
											requisition_employees.phone as phone,
											state_work.description as state_work,
											projects.proyectName as proyect_employee,
											cat_code_w_bs.code_wbs as wbs_employee,
											enterprises.name as enterprise_name,
											CONCAT(accounts.account," ",accounts.description) as account_employee,
											directions.name as direction_name,
											departments.name as department_name,
											subdepartments.name as subdepartment_name,
											requisition_employees.position as position,
											requisition_employees.immediate_boss as immediate_boss,
											requisition_employees.position_immediate_boss as position_immediate_boss,
											IF(requisition_employees.status_imss = 1,"Activo","Inactivo") as status_imss,
											requisition_employees.admissionDate as admissionDate,
											requisition_employees.imssDate as imssDate,
											requisition_employees.downDate as downDate,
											requisition_employees.endingDate as endingDate,
											requisition_employees.reentryDate as reentryDate,
											cat_contract_types.description as contract_type,
											cat_regime_types.description as regime_type,
											IF(requisition_employees.workerStatus = 1,"Activo",IF(requisition_employees.workerStatus = 2,"Baja pacial",IF(requisition_employees.workerStatus = 3,"Baja definitiva",IF(requisition_employees.workerStatus = 4,"Suspensión","Boletinado")))) as worker_status,
											requisition_employees.status_reason as status_reason,
											requisition_employees.sdi as sdi,
											cat_periodicities.description as periodicity,
											requisition_employees.employer_register,
											payment_methods.method as paymentWay,
											requisition_employees.netIncome,
											requisition_employees.viatics,
											requisition_employees.camping,
											requisition_employees.complement,
											requisition_employees.fonacot,
											requisition_employees.nomina,
											requisition_employees.bono,
											requisition_employees.infonavitCredit,
											requisition_employees.infonavitDiscount,
											requisition_employees.infonavitDiscountType,
											bank_data.alias,
											cat_banks.description as employee_bank,
											CONCAT(bank_data.clabe," ") as clabe,
											CONCAT(bank_data.account," ") as account,
											CONCAT(bank_data.cardNumber," ") as cardNumber,
											bank_data.branch

										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_employees','requisition_employees.requisition_id','requisitions.id')
										->leftJoin('states','states.idstate','requisition_employees.state_id')
										->leftJoin('states as state_work','state_work.idstate','requisition_employees.state')
										->leftJoin('cat_tax_regimes','cat_tax_regimes.taxRegime','requisition_employees.tax_regime')
										->leftJoin('enterprises','enterprises.id','requisition_employees.enterprise')
										->leftJoin('accounts','accounts.idAccAcc','requisition_employees.account')
										->leftJoin('areas as directions','directions.id','requisition_employees.direction')
										->leftJoin('departments','departments.id','requisition_employees.department')
										->leftJoin('subdepartments','subdepartments.id','requisition_employees.subdepartment_id')
										->leftJoin('cat_contract_types','requisition_employees.workerType','cat_contract_types.id')
										->leftJoin('cat_regime_types','requisition_employees.regime_id','cat_regime_types.id')
										->leftJoin('cat_periodicities','requisition_employees.periodicity','cat_periodicities.c_periodicity')
										->leftJoin('payment_methods','requisition_employees.paymentWay','payment_methods.idpaymentMethod')
										->leftJoin(DB::raw('(SELECT * FROM requisition_employee_accounts WHERE id IN(SELECT MIN(id) as id FROM requisition_employee_accounts GROUP BY idEmployee)) as bank_data'),'requisition_employees.id','bank_data.idEmployee')
										->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if($status == 0)
											{
												$query->where('request_models.status',3);
											}
											else
											{
												$query->whereNotIn('request_models.status',[2,3])
													->where('request_models.idCheck',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$employee)
										{
											if ($employee != "") 
											{
												$query->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestPersonal)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Personal');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Personal');
								}

								$headers =
								[
									'folio', 'proyecto', 'wbs', 'fecha_en_obra', 'solicitante', 'titulo', 'nombre', 'apellido', 'apellido2', 'curp', 'rfc', 'regimen_fiscal', 'imss', 'calle', 'numero', 'colonia', 'cp', 'ciudad', 'estado', 'correo_electronico', 'numero_telefonico', 'estado_laboral', 'proyecto', 'wbs', 'empresa', 'clasificacion_gasto', 'direccion', 'departamento', 'subdepartamento', 'puesto', 'jefe_inmediato', 'posicion_jefe_inmediato', 'estatus_imss', 'fecha_ingreso', 'fecha_alta', 'fecha_baja', 'fecha_termino', 'fecha_reingreso', 'tipo_contrato', 'regimen', 'estatus', 'razon_estatus', 'sdi', 'periodicidad', 'registro_patronal', 'forma_pago', 'sueldo_neto', 'viaticos', 'campamento', 'complemento', 'fonacot', 'porcentaje_nomina', 'porcentaje_bono', 'credito_infonavit', 'descuento_infonavit', 'tipo_descuento_infonavit', 'alias', 'banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal'
								];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 5)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
									}
									elseif($k <= 20)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 55)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
									elseif($k <= 61)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestPersonal as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio			= null;
										$request->proyectName	= '';
										$request->wbs			= '';
										$request->date_obra		= '';
										$request->request_user	= '';
										$request->title			= '';
										
									}
									$tmpArr = [];
									foreach($request as $k => $r)
									{
										$tmpArr[] = WriterEntityFactory::createCell($r);
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
							}
						}
						break;	

					case 4:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestSubcontracts = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if($status == 0)
											{
												$query->where('request_models.status',3);
											}
											else
											{
												$query->whereNotIn('request_models.status',[2,3])
													->where('request_models.idCheck',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestSubcontracts)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Subcontratos');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Subcontratos');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestSubcontracts as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 5:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMachine = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.brand as brand,
											requisition_details.model as model,
											requisition_details.usage_time as usage_time,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if($status == 0)
											{
												$query->where('request_models.status',3);
											}
											else
											{
												$query->whereNotIn('request_models.status',[2,3])
													->where('request_models.idCheck',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMachine)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Maquinaria');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Maquinaria');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Medida','Unidad','Nombre','Descripción','Marca','Modelo','Tiempo de Utilización','Existencia Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMachine as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 6:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestComercial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($status)
										{
											if($status == 0)
											{
												$query->where('request_models.status',3);
											}
											else
											{
												$query->whereNotIn('request_models.status',[2,3])
													->where('request_models.idCheck',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type)
										{
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if($mindate_request != "" && $maxdate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if($mindate_obra != "" && $maxdate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestComercial)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Comercial');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Comercial');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestComercial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					default:
						// code...
						break;
				}
			}
			
			return $writer->close();
		}
	}

	public function exportReviewComplete(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0){
			$requests=$this->searchReview($request)->get();
			$content=array();
			foreach ($requests as $item) {
				$contentRow =array(
					$item->folio,
					$item->requestProject()->exists() ? $item->requestProject->proyectName : 'No hay' ,
					$item->requisition->wbs()->exists() ? $item->requisition->wbs->code_wbs : 'No hay',
					$item->requisition->edt()->exists() ? $item->requisition->edt->fullName() : 'No hay',
					$item->requisition->urgent == 1 ? 'Alta' : 'Baja',
					$item->requestUser()->exists() ? $item->requestUser->fullName() : 'Sin solicitante',
					$item->requisition->title,
					$item->requisition->number,
					$item->requisition->date_request,
					$item->requisition->date_obra,
					$item->requisition->typeRequisition()
				);
				$mixCells = null;
				$content[]=array('row'=>$contentRow,'mixes'=>$mixCells);
			}
			$exp		= new ExcelExportClass('Reporte-Requisición-Revisión-Completo');
			$objSheet	= new SheetExcel($content,'Requisición');
			$title 	= 'Requisicion';
			$subTitle	=['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Tipo'];
			$objSheet->AddHead($title,$subTitle);
			$exp->AddSheets($objSheet);
			$exp->DownloadExcel();
		}
	}

	public function exportTracing(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0) 
		{
			$title_request		= $request->title_request;
			$mindate_request	= $request->mindate_request !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_request)	: null;
			$maxdate_request	= $request->maxdate_request	!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_request)	: null;
			$mindate_obra		= $request->mindate_obra	!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_obra)		: null;
			$maxdate_obra		= $request->maxdate_obra	!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_obra)		: null;
			$folio				= $request->folio;
			$user_request		= $request->user_request;
			$project_request 	= $request->project_request;
			$number 			= $request->number;
			$wbs             	= $request->wbs;
			$edt             	= $request->edt;
			$type            	= $request->type;
			$employee 			= $request->employee;
			$status				= $request->status;
			$new_sheet 			= true;

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('771414')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('db5151')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('21bbbb')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('1a6206')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('requisiciones.xlsx');
			if(Auth::user()->globalCheck->where('module_id',230)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',230)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			for ($i=1; $i < 7; $i++) 
			{ 	
				switch ($i) 
				{
					case 1:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMaterial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											cat_procurement_materials.name as procurementMaterialType,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->leftJoin('cat_procurement_materials','cat_procurement_materials.id','requisition_details.cat_procurement_material_id')
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->where('request_models.status','!=',23)
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMaterial)>0) 
							{
								$new_sheet = false;
								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Tipo','Cant','Medida','Unidad','Nombre','Descripción','Existencia en Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$writer->getCurrentSheet()->setName('Material');

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMaterial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 2:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestServices = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.period as period
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestServices)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Servicios_Generales');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Servicios_Generales');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Unidad','Nombre','Descripción','Periodo'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestServices as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}		

						break;

					case 3:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestPersonal = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											requisitions.date_obra as date_obra,
											CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisition_employees.name as name,
											requisition_employees.last_name as last_name,
											requisition_employees.scnd_last_name as scnd_last_name,
											requisition_employees.curp as curp,
											requisition_employees.rfc as rfc,
											cat_tax_regimes.description as taxRegime,
											requisition_employees.imss as imss,
											requisition_employees.street as street,
											requisition_employees.number as number,
											requisition_employees.colony as colony,
											requisition_employees.cp as cp,
											requisition_employees.city as city,
											states.description as states_employee,
											requisition_employees.email as email,
											requisition_employees.phone as phone,
											state_work.description as state_work,
											projects.proyectName as proyect_employee,
											cat_code_w_bs.code_wbs as wbs_employee,
											enterprises.name as enterprise_name,
											CONCAT(accounts.account," ",accounts.description) as account_employee,
											directions.name as direction_name,
											departments.name as department_name,
											subdepartments.name as subdepartment_name,
											requisition_employees.position as position,
											requisition_employees.immediate_boss as immediate_boss,
											requisition_employees.position_immediate_boss as position_immediate_boss,
											IF(requisition_employees.status_imss = 1,"Activo","Inactivo") as status_imss,
											requisition_employees.admissionDate as admissionDate,
											requisition_employees.imssDate as imssDate,
											requisition_employees.downDate as downDate,
											requisition_employees.endingDate as endingDate,
											requisition_employees.reentryDate as reentryDate,
											cat_contract_types.description as contract_type,
											cat_regime_types.description as regime_type,
											IF(requisition_employees.workerStatus = 1,"Activo",IF(requisition_employees.workerStatus = 2,"Baja pacial",IF(requisition_employees.workerStatus = 3,"Baja definitiva",IF(requisition_employees.workerStatus = 4,"Suspensión","Boletinado")))) as worker_status,
											requisition_employees.status_reason as status_reason,
											requisition_employees.sdi as sdi,
											cat_periodicities.description as periodicity,
											requisition_employees.employer_register,
											payment_methods.method as paymentWay,
											requisition_employees.netIncome,
											requisition_employees.viatics,
											requisition_employees.camping,
											requisition_employees.complement,
											requisition_employees.fonacot,
											requisition_employees.nomina,
											requisition_employees.bono,
											requisition_employees.infonavitCredit,
											requisition_employees.infonavitDiscount,
											requisition_employees.infonavitDiscountType,
											bank_data.alias,
											cat_banks.description as employee_bank,
											CONCAT(bank_data.clabe," ") as clabe,
											CONCAT(bank_data.account," ") as account,
											CONCAT(bank_data.cardNumber," ") as cardNumber,
											bank_data.branch

										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_employees','requisition_employees.requisition_id','requisitions.id')
										->leftJoin('states','states.idstate','requisition_employees.state_id')
										->leftJoin('states as state_work','state_work.idstate','requisition_employees.state')
										->leftJoin('cat_tax_regimes','cat_tax_regimes.taxRegime','requisition_employees.tax_regime')
										->leftJoin('enterprises','enterprises.id','requisition_employees.enterprise')
										->leftJoin('accounts','accounts.idAccAcc','requisition_employees.account')
										->leftJoin('areas as directions','directions.id','requisition_employees.direction')
										->leftJoin('departments','departments.id','requisition_employees.department')
										->leftJoin('subdepartments','subdepartments.id','requisition_employees.subdepartment_id')
										->leftJoin('cat_contract_types','requisition_employees.workerType','cat_contract_types.id')
										->leftJoin('cat_regime_types','requisition_employees.regime_id','cat_regime_types.id')
										->leftJoin('cat_periodicities','requisition_employees.periodicity','cat_periodicities.c_periodicity')
										->leftJoin('payment_methods','requisition_employees.paymentWay','payment_methods.idpaymentMethod')
										->leftJoin(DB::raw('(SELECT * FROM requisition_employee_accounts WHERE id IN(SELECT MIN(id) as id FROM requisition_employee_accounts GROUP BY idEmployee)) as bank_data'),'requisition_employees.id','bank_data.idEmployee')
										->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$employee,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($employee != "") 
											{
												$query->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestPersonal)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Personal');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Personal');
								}

								$headers =
								[
									'folio', 'proyecto', 'wbs', 'fecha_en_obra', 'solicitante', 'titulo', 'nombre', 'apellido', 'apellido2', 'curp', 'rfc', 'regimen_fiscal', 'imss', 'calle', 'numero', 'colonia', 'cp', 'ciudad', 'estado', 'correo_electronico', 'numero_telefonico', 'estado_laboral', 'proyecto', 'wbs', 'empresa', 'clasificacion_gasto', 'direccion', 'departamento', 'subdepartamento', 'puesto', 'jefe_inmediato', 'posicion_jefe_inmediato', 'estatus_imss', 'fecha_ingreso', 'fecha_alta', 'fecha_baja', 'fecha_termino', 'fecha_reingreso', 'tipo_contrato', 'regimen', 'estatus', 'razon_estatus', 'sdi', 'periodicidad', 'registro_patronal', 'forma_pago', 'sueldo_neto', 'viaticos', 'campamento', 'complemento', 'fonacot', 'porcentaje_nomina', 'porcentaje_bono', 'credito_infonavit', 'descuento_infonavit', 'tipo_descuento_infonavit', 'alias', 'banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal'
								];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 5)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
									}
									elseif($k <= 20)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 55)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
									elseif($k <= 61)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestPersonal as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio			= null;
										$request->proyectName	= '';
										$request->wbs			= '';
										$request->date_obra		= '';
										$request->request_user	= '';
										$request->title			= '';
										
									}
									$tmpArr = [];
									foreach($request as $k => $r)
									{
										$tmpArr[] = WriterEntityFactory::createCell($r);
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
							}
						}
						break;	

					case 4:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestSubcontracts = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestSubcontracts)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Subcontratos');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Subcontratos');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestSubcontracts as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 5:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMachine = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.brand as brand,
											requisition_details.model as model,
											requisition_details.usage_time as usage_time,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMachine)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Maquinaria');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Maquinaria');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Medida','Unidad','Nombre','Descripción','Marca','Modelo','Tiempo de Utilización','Existencia Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMachine as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}	

						break;
					
					case 6:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestComercial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
										->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($q) use ($global_permission)
										{
											if ($global_permission == 0) 
											{
												$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
											}
										})
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestComercial)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Comercial');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Comercial');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestComercial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					default:
						// code...
						break;
				}
			}

			return $writer->close();
		}
	}

	public function exportTracingComplete(Request $request)
	{
		if (Auth::user()->module->where('id',230)->count()>0)
		{
				$requests=$this->searchTracing($request)->get();
				$content=array();
				foreach ($requests as $item) {
					$contentRow =array(
						$item->folio,
						$item->requestProject()->exists() ? $item->requestProject->proyectName : 'No hay' ,
						$item->requisition->wbs()->exists() ? $item->requisition->wbs->code_wbs : 'No hay',
						$item->requisition->edt()->exists() ? $item->requisition->edt->fullName() : 'No hay',
						$item->requisition->urgent == 1 ? 'Alta' : 'Baja',
						$item->requestUser()->exists() ? $item->requestUser->fullName() : 'Sin solicitante',
						$item->requisition->title,
						$item->requisition->number,
						$item->requisition->date_request,
						$item->requisition->date_obra,
						$item->requisition->typeRequisition(),
						$item->statusRequest->description,
					);
					$mixCells = null;
					$content[]=array('row'=>$contentRow,'mixes'=>$mixCells);
				}
				$exp		= new ExcelExportClass('Reporte-Requisición-Seguimiento-Completo');
				$objSheet	= new SheetExcel($content,'Requisición');
				$title 	= 'Requisicion';
				$subTitle	=['ID','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Folio','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Tipo','Estado'];
				$objSheet->AddHead($title,$subTitle);
				$exp->AddSheets($objSheet);
				$exp->DownloadExcel();
		}
	}

	public function searchReview(Request $request)
	{
		$title_request		= $request->title_request;
		$mindate_request	= $request->mindate_request;
		$maxdate_request	= $request->maxdate_request;
		$mindate_obra		= $request->mindate_obra;
		$maxdate_obra		= $request->maxdate_obra;
		$folio				= $request->folio;
		$user_request		= $request->user_request;
		$project_request 	= $request->project_request;
		$number 			= $request->number;
		$wbs             	= $request->wbs;
		$edt             	= $request->edt;
		$type            	= $request->type;

		return App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
			->where('request_models.kind',19)
			->where('request_models.status',3)
			->whereIn('idProject',Auth::user()->inChargeProject(231)->pluck('project_id'))
			->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(231)->pluck('requisition_type_id'))
			->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type){
				if ($user_request != "") 	$query->whereIn('request_models.idRequest',$user_request);
				if($title_request != "")	$query->where('requisitions.title','LIKE','%'.$title_request.'%');
				if ($mindate_request != "") $query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
				if ($mindate_obra != "") 	$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
				if($folio != "")			$query->where('request_models.folio',$folio);
				if ($project_request != "")	$query->whereIn('request_models.idProject',$project_request);
				if ($number != "") 			$query->where('requisitions.number','LIKE','%'.$number.'%');
				if($wbs != "")				$query->whereIn('requisitions.code_wbs',$wbs);
				if($edt != "")				$query->whereIn('requisitions.code_edt',$edt);
				if($type != "")				$query->whereIn('requisitions.requisition_type',$type);
			})
			->orderBy('request_models.fDate','DESC')
			->orderBy('request_models.folio','DESC');
	}

	public function searchTracing(Request $request)
	{
		$title_request   = $request->title_request;
		$mindate_request = $request->mindate_request;
		$maxdate_request = $request->maxdate_request;
		$mindate_obra    = $request->mindate_obra;
		$maxdate_obra    = $request->maxdate_obra;
		$status          = $request->status;
		$folio           = $request->folio;
		$user_request    = $request->user_request;
		$project_request = $request->project_request;
		$number          = $request->number;
		$wbs             = $request->wbs;
		$edt             = $request->edt;
		$type            = $request->type;
		$category        = $request->category;

		if(Auth::user()->globalCheck->where('module_id',230)->count()>0)
		{
			$global_permission =  Auth::user()->globalCheck->where('module_id',230)->first()->global_permission;
		}
		else
		{
			$global_permission = 0;
		}

		return App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
			->where('request_models.kind',19)
			->where('status','!=',23)
			->whereIn('idProject',Auth::user()->inChargeProject(230)->pluck('project_id'))
			->where(function ($q) use ($global_permission)
			{
				if ($global_permission == 0) 
				{
					$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
				}
			})
			->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$category){
				if ($user_request != "") 	$query->whereIn('request_models.idRequest',$user_request);
				if ($title_request != "")	$query->where('requisitions.title','LIKE','%'.$title_request.'%');
				if ($mindate_request != "") $query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
				if ($mindate_obra != "") 	$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
				if ($folio != "")			$query->where('request_models.folio',$folio);
				if ($status != "")			$query->whereIn('request_models.status',$status);
				if ($project_request != "") $query->whereIn('request_models.idProject',$project_request);
				if ($number != "") 			$query->where('requisitions.number','LIKE','%'.$number.'%');
				if ($wbs != "") 			$query->whereIn('requisitions.code_wbs',$wbs);
				if ($edt != "") 			$query->whereIn('requisitions.code_edt',$edt);
				if ($type != "")			$query->whereIn('requisitions.requisition_type',$type);
				if ($category != "") 
				{
					$query->whereHas('requisition.details',function($q) use($category)
					{
						$q->whereIn('category',$category);
					});
				}
			})
			->orderBy('request_models.fDate','DESC')
			->orderBy('request_models.folio','DESC');
	}

	public function searchAuthorization(Request $request)
	{
		$title_request   = $request->title_request;
		$mindate_request = $request->mindate_request;
		$maxdate_request = $request->maxdate_request;
		$mindate_obra    = $request->mindate_obra;
		$maxdate_obra    = $request->maxdate_obra;
		$status          = $request->status;
		$folio           = $request->folio;
		$user_request    = $request->user_request;
		$project_request = $request->project_request;
		$number          = $request->number;
		$wbs             = $request->wbs;
		$edt             = $request->edt;
		$type            = $request->type;

		if($status == '')
		{
			$status= [4,5];
		} 
		$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
			->where('request_models.kind',19)
			->where(function ($q) use($status)
			{
				if(!in_array(1,$status))
				{
					$q->where(function($q)
					{
						$q->where('requisitions.requisition_type',3)
							->where('request_models.status',4);
					})
					->orWhere(function($q)
					{
						$q->where('requisitions.requisition_type','!=',3);
					});
				}
			})
			->whereIn('idProject',Auth::user()->inChargeProject(232)->pluck('project_id'))
			->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(232)->pluck('requisition_type_id'))
			->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type) 
			{
				if ($user_request != "") 
				{
					$query->whereIn('request_models.idRequest',$user_request);
				}
				if($title_request != "")
				{
					$query->where('requisitions.title','LIKE','%'.$title_request.'%');
				}
				if ($mindate_request != "") 
				{
					$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
				}
				if ($mindate_obra != "") 
				{
					$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
				}
				if($folio != "")
				{
					$query->where('request_models.folio',$folio);
				}
				if($status != "" && !in_array(1,$status))
				{
					$query->whereIn('request_models.status',$status);
				}
				else
				{ 
					$query->whereNotIn('request_models.status',[2,3,4,5,6])
					->where('request_models.idAuthorize',Auth::user()->id);
				}  
				if ($project_request != "") 
				{
					$query->whereIn('request_models.idProject',$project_request);
				}
				if ($number != "") 
				{
					$query->where('requisitions.number','LIKE','%'.$number.'%');
				}
				if($wbs != "")
				{
					$query->whereIn('requisitions.code_wbs',$wbs);
				}
				if($edt != "")
				{
					$query->whereIn('requisitions.code_edt',$edt);
				}
				if($type != "")
				{
					$query->whereIn('requisitions.requisition_type',$type);
				}
				 
			})
			->orderBy('request_models.status','ASC')
			->orderBy('request_models.authorizeDate','DESC')
			->orderBy('request_models.reviewDate','DESC')
			->orderBy('request_models.fDate','DESC')
			->orderBy('request_models.folio','DESC');


		
		return $requests;
	}

	public function uploadArticles(Request $request, $id)
	{
		$t_request					= App\RequestModel::find($id);
		$idRequisition 				= $t_request->requisition->id;
		
		if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid())
		{
			$name		= '/massive_requisition/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
						$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
						$first		= false;
					}
					$csvArr[]	= $data;
				}
				fclose($handle);
			}
			array_walk($csvArr, function(&$a) use ($csvArr)
			{
				$a = array_combine($csvArr[0], $a);
			});
			array_shift($csvArr);

			foreach ($csvArr as $art) 
			{
				if ((isset($art['part']) && trim($art['part'])!="") && (isset($art['cantidad']) && trim($art['cantidad'])>0) && (isset($art['nombre']) && trim($art['nombre'])!="") && (isset($art['unidad']) && trim($art['unidad'])!="") && (isset($art['descripcion']) && trim($art['descripcion'])!="") && (isset($art['existencia_almacen']) && trim($art['existencia_almacen'])!="") && (isset($art['categoria']) && (trim($art['categoria'])=='1' || trim($art['categoria'])=='2' || trim($art['categoria'])=='3' || trim($art['categoria'])=='4' || trim($art['categoria'])=='5' || trim($art['categoria'])=='6' || trim($art['categoria'])=='7' || trim($art['categoria'])=='8')))
				{
					$c_r = App\CatRequisitionName::where('name',$art['nombre'])->first();
					if(!$c_r){
						$c_r = App\CatRequisitionName::create(['name' => $art['nombre']]);
					} 

					$name_measurement = App\CatMeasurementUnit::where('description',$art['medida'])->first();
					if(!$name_measurement)
					{
						$name_measurement = App\CatMeasurementUnit::create(['description' => $art['medida']]);
					} 
					$detail                   = new App\RequisitionDetail();
					$detail->category         = $art['categoria'];
					$detail->part             = $art['part'];
					$detail->quantity         = $art['cantidad'];
					$detail->unit             = $art['unidad'];
					$detail->name             = $c_r->name;
					$detail->measurement      = $name_measurement->description;
					$detail->description      = $art['descripcion'];
					$detail->exists_warehouse = $art['existencia_almacen'];
					$detail->idRequisition    = $idRequisition;
					$detail->save();
				}else{

					$errors++;
				}
			}
		}
	}

	public function getNumberRequisiction(Request $request)
	{
		if ($request->ajax()) 
		{
			if ($request->idproject != "") 
			{
				$count = App\RequestModel::where('kind',19)->where('idProject',$request->idproject)->count();
				$num = $count + 1;
			}
			else
			{
				$num = '';
			}
			
			return Response($num);
		}
	}

	public function getWBS(Request $request)
	{
		if ($request->ajax()) 
		{
			$wbs = App\CatCodeWBS::where('project_id',$request->idproject)->where('status',1)->orderBy('code_wbs','asc')->get();
			return Response($wbs);
		}
	}


	public function vote(Request $request)
	{
		if (Auth::user()->module->where('id',276)->count()>0) 
		{
			$data            = App\Module::find($this->module_id);
			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request	!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_request)	: null;
			$maxdate_request = $request->maxdate_request	!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_request)	: null;
			$mindate_obra    = $request->mindate_obra		!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_obra)		: null;
			$maxdate_obra    = $request->maxdate_obra		!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_obra)		: null;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$status 		 = $request->status;
			if($status =='')
			{
				$status =0;
			}
			
			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(276)->pluck('project_id'))
				->whereIn('requisitions.requisition_type',Auth::user()->inChargeReq(276)->pluck('requisition_type_id'))
				
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status)
				{
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					$query->where(function ($query) use ($mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra)
					{
						if ($mindate_request != "" && $maxdate_request != "") 
						{
							$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
						}
						if ($mindate_obra != "" && $maxdate_obra != "") 
						{
							$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
						}
					});
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if ($project_request != "") 
					{
						$query->whereIn('request_models.idProject',$project_request);
					}
					if ($number != "") 
					{
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}
					if($status == 0)
					{
						$query->whereIn('request_models.status',[27]);
					}
					else
					{
						$query->whereIn('request_models.status',[5,17]);
						$query->whereHas('requisition', function($query)
						{
							$query->whereHas('votingProvider', function($q)
							{
								$q->where('user_id', Auth::user()->id);
							});
						}); 
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);
			return response(
				view('administracion.requisicion.busqueda_votacion',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 276,
						'requests'			=> $requests,
						'mindate_obra'		=> $request->mindate_obra,
						'maxdate_obra'		=> $request->maxdate_obra,
						'mindate_request'	=> $request->mindate_request,
						'maxdate_request'	=> $request->maxdate_request,
						'folio'				=> $folio,
						'title_request'		=> $title_request,
						'user_request'		=> $user_request,
						'project_request'	=> $project_request,
						'number'			=> $number,
						'wbs'				=> $wbs,
						'edt'				=> $edt,
						'type'				=> $type,
						'status'			=> $status
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(276), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function Voteedit($id)
	{
		if (Auth::user()->module->where('id',276)->count()>0) 
		{
			$request = App\RequestModel::find($id);
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.requisicion.alta_material',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 276,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function showVote($id)
	{
		if (Auth::user()->module->where('id',276)->count()>0) 
		{
			$request = App\RequestModel::where('request_models.kind',19)
				->whereIn('idProject',Auth::user()->inChargeProject(276)->pluck('project_id'))
				->where('request_models.status',27)->find($id);

			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.requisicion.editar_votacion',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 276,
						'request'	=> $request
					]
				);
				
			}
			else
			{
				$alert	= "swal('', 'No existe la requisición o ya pasó por el proceso de Compras Locales.', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function updateVote(Request $request,$id)
	{
		if (Auth::user()->module->where('id',276)->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return redirect()->route('requisition.vote')->with('alert',$alert);
			}
			if ($t_request->status == 5)
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'success');";
				return redirect()->route('requisition.vote')->with('alert',$alert);
			}
			if ($t_request->status == 7)
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'success');";
				return redirect()->route('requisition.vote')->with('alert',$alert);
			}
			$t_request->idAuthorize			= Auth::user()->id;
			$t_request->authorizeComment	= $request->revisionComment;
			$t_request->authorizeDate		= Carbon::now();
			$t_request->save();

			if (isset($request->realPathRequisition) && count($request->realPathRequisition)>0) 
			{
				for ($i=0; $i < count($request->realPathRequisition); $i++) 
				{
					if ($request->realPathRequisition[$i] != "") 
					{
						$new_file_name            = Files::rename($request->realPathRequisition[$i],$t_request->folio);
						$documents					= new App\RequisitionDocuments();
						$documents->name			= $request->nameDocumentRequisition[$i];
						$documents->ticket_number	= $request->ticket_number[$i];
						$documents->fiscal_folio	= $request->fiscal_folio[$i];
						$documents->timepath		= $request->timepath[$i];
						$documents->amount			= $request->amount[$i];
						$documents->datepath		= $request->datepath[$i];
						$documents->path			= $new_file_name;
						$documents->idRequisition	= $t_request->requisition->id;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			if ($t_request->requisition->requisition_type != 3) 
			{
				if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
				{
					for ($i=0; $i < count($request->idRequisitionHasProvider); $i++) 
					{
						$commentaries                          = 'commentaries_provider_'.$request->idRequisitionHasProvider[$i];
						$type_currency                         = 'type_currency_provider_'.$request->idRequisitionHasProvider[$i];

						$delivery_time = 'delivery_time_'.$request->idRequisitionHasProvider[$i];
						$credit_time   = 'credit_time_'.$request->idRequisitionHasProvider[$i];
						$guarantee 	   = 'guarantee_'.$request->idRequisitionHasProvider[$i];
						$spare 	       = 'spare_'.$request->idRequisitionHasProvider[$i];
						
						$requisitionHasProvider                = App\RequisitionHasProvider::find($request->idRequisitionHasProvider[$i]);
						$requisitionHasProvider->commentaries  = $request->$commentaries;
						$requisitionHasProvider->type_currency = $request->$type_currency;

						$requisitionHasProvider->delivery_time = $request->$delivery_time;
						$requisitionHasProvider->credit_time   = $request->$credit_time;
						$requisitionHasProvider->guarantee     = $request->$guarantee;
						$requisitionHasProvider->spare         = $request->$spare;

						$requisitionHasProvider->save();
					}
				}

				if (isset($request->exists_warehouse) && count($request->exists_warehouse)) 
				{
					for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
					{ 
						$detail = App\RequisitionDetail::find($request->idRequisitionDetail[$ird]);
						if (isset($request->exists_warehouse[$ird]) && $request->exists_warehouse[$ird] != "") 
						{
							$detail->exists_warehouse	= $request->exists_warehouse[$ird];
							$detail->save();
						}
					}
				}

				if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
				{
					for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
					{ 
						$checkVote = App\VotingProvider::where('user_id',Auth::user()->id)->where('idRequisitionDetail',$request->idRequisitionDetail[$ird])->first();

						if ($checkVote != "") 
						{
							$voting                                   = 'voting_'.$request->idRequisitionDetail[$ird];
							$comment                                  = 'commentaries_'.$request->idRequisitionDetail[$ird];
							$votingProvider                           = App\VotingProvider::find($checkVote->id);
							$votingProvider->user_id                  = Auth::user()->id;
							$votingProvider->idRequisitionHasProvider = $request->$voting;
							$votingProvider->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
							$votingProvider->idRequisition            = $t_request->requisition->id;
							$votingProvider->commentaries             = $request->$comment;
							$votingProvider->save();
						}
						else
						{
							$voting                                   = 'voting_'.$request->idRequisitionDetail[$ird];
							$comment                                  = 'commentaries_'.$request->idRequisitionDetail[$ird];
							$votingProvider                           = new App\VotingProvider();
							$votingProvider->user_id                  = Auth::user()->id;
							$votingProvider->idRequisitionHasProvider = $request->$voting;
							$votingProvider->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
							$votingProvider->idRequisition            = $t_request->requisition->id;
							$votingProvider->commentaries             = $request->$comment;
							$votingProvider->save();
						}
					}
				}

				$flag = true;
				foreach ($t_request->requisition->details as $detail) 
				{
					foreach ($detail->votingProvider as $voteGlobal) 
					{
						foreach ($detail->votingProvider as $voteGlobalTemp) 
						{
							if ($voteGlobal->idRequisitionHasProvider != $voteGlobalTemp->idRequisitionHasProvider)
							{
								$flag = false;
							}
						}
					}
				}

				$totalUserAccess = App\User::leftJoin('user_has_modules','users.id','user_has_modules.user_id')
								->leftJoin('permission_projects','user_has_modules.iduser_has_module','permission_projects.user_has_module_iduser_has_module')
								->where('user_has_modules.module_id',276)
								->where('permission_projects.project_id',$t_request->idProject)
								->count();

				$checkVoteLocal = $t_request->requisition->votingProvider()->exists() ? $t_request->requisition->votingProvider()->where('user_id',Auth::user()->id)->exists() ? $t_request->requisition->votingProvider->where('user_id',Auth::user()->id)->count() : 0 : 0 ;

				$checkVoteOther = $t_request->requisition->votingProvider()->exists() ? $t_request->requisition->votingProvider()->where('user_id','!=',Auth::user()->id)->exists() ? $t_request->requisition->votingProvider->where('user_id','!=',Auth::user()->id)->count() : 0 : 0 ;

				$countVotes = $checkVoteLocal+$checkVoteOther;

				//if ($flag && ($countVotes == $totalUserAccess))
				if ($flag && ($countVotes > 0))
				{
					$alert                           = "swal('Requisición Autorizada Exitosamente','Ahora puede proceder a generar las solicitudes.', 'success');";
					$t_request->status               = 5;
					$t_request->idAuthorize          = Auth::user()->id;
					$t_request->authorizeDate        = Carbon::now();
					$t_requisition                   = App\Requisition::find($t_request->requisition->id);
					$t_requisition->date_comparation = Carbon::now();
					$t_requisition->save();
					$t_request->save();

					foreach ($t_request->requisition->details as $detailTemp) 
					{
						$idRequisitionHasProvider         = App\VotingProvider::where('idRequisitionDetail',$detailTemp->id)->where('idRequisition',$t_request->requisition->id)->first()->idRequisitionHasProvider;
						$type_currency                    = App\RequisitionHasProvider::find($idRequisitionHasProvider)->type_currency;
						$detail                           = App\RequisitionDetail::find($detailTemp->id);
						$detail->idRequisitionHasProvider = $idRequisitionHasProvider;
						$detail->type_currency            = $type_currency;
						$detail->save();
					}
					return searchRedirect(276, $alert, 'administration/requisition/vote');
				}
				else
				{
					$alert				= "swal('Advertencia','La solicitud no puede ser enviada, porque los votos no coinciden en todos los items y deben votar todos los usuarios en el cuadro comparativo.', 'warning');";
					$t_request->status	= 27;
					$t_request->save();
					return searchRedirect(276, $alert, 'administration/requisition/vote');
				}			
			}
			else
			{
				$t_request->status			= 5;
				$t_request->idAuthorize		= Auth::user()->id;
				$t_request->authorizeDate	= Carbon::now();
				$t_request->save();
				$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success');";
				return searchRedirect(276, $alert, 'administration/requisition/vote');
			}
			
		}
	}

	public function saveVote(Request $request, $id)
	{
		if (Auth::user()->module->where('id',276)->count()>0) 
		{
			$t_request = App\RequestModel::find($id);
			if ($t_request->status == 28)
			{
				$alert = "swal('','Requisición Cancelada por el Usuario', 'error');";
				return redirect()->route('requisition.vote')->with('alert',$alert);
			}
			$t_request->authorizeComment	= $request->revisionComment;
			$t_request->save();

			if (isset($request->idRequisitionHasProvider) && count($request->idRequisitionHasProvider)>0) 
			{
				for ($ird=0; $ird < count($request->idRequisitionDetail); $ird++) 
				{ 
					$checkVote = App\VotingProvider::where('user_id',Auth::user()->id)->where('idRequisitionDetail',$request->idRequisitionDetail[$ird])->first();

					if ($checkVote != "") 
					{
						$voting                                   = 'voting_'.$request->idRequisitionDetail[$ird];
						$comment                                  = 'commentaries_'.$request->idRequisitionDetail[$ird];
						$votingProvider                           = App\VotingProvider::find($checkVote->id);
						$votingProvider->user_id                  = Auth::user()->id;
						$votingProvider->idRequisitionHasProvider = $request->$voting;
						$votingProvider->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
						$votingProvider->idRequisition            = $t_request->requisition->id;
						$votingProvider->commentaries             = $request->$comment;
						$votingProvider->save();
					}
					else
					{
						$voting                                   = 'voting_'.$request->idRequisitionDetail[$ird];
						$comment                                  = 'commentaries_'.$request->idRequisitionDetail[$ird];
						$votingProvider                           = new App\VotingProvider();
						$votingProvider->user_id                  = Auth::user()->id;
						$votingProvider->idRequisitionHasProvider = $request->$voting;
						$votingProvider->idRequisitionDetail      = $request->idRequisitionDetail[$ird];
						$votingProvider->idRequisition            = $t_request->requisition->id;
						$votingProvider->commentaries             = $request->$comment;
						$votingProvider->save();
					}
				}
			}

			$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success');";
			switch ($t_request->status) 
			{
				case 3:
					return redirect()->route('requisition.review.show',['id'=>$id])->with('alert',$alert);
					break;

				case 4:
					return redirect()->route('requisition.authorization.show',['id'=>$id])->with('alert',$alert);
					break;

				case 5:
					return redirect()->route('requisition.review.show',['id'=>$id])->with('alert',$alert);
					break;

				case 27:
					return redirect()->route('requisition.vote.show',['id'=>$id])->with('alert',$alert);
					break;
		
				default:
					# code...
					break;
			}
		}
	}

	public function followGetWBS(Request $request)
	{
		if ($request->ajax()) 
		{
			$wbs = App\CatCodeWBS::whereIn('project_id',$request->idproject)->whereIn('status',[1])->orderBy('code_wbs','asc')->get();
			return Response($wbs);
		}
	}

	public function followGetEDT(Request $request)
	{
		if ($request->ajax()) 
		{
			$edts = App\CatCodeEDT::whereIn('codewbs_id',$request->idcode_wbs)->get();
			if (count($edts) > 0) 
			{
				return Response($edts);
			}
		}
	}

	public function getUnit(Request $request)
	{
		$units = App\Unit::whereHas('category_rq',function($q) use($request)
		{
			$q->where('rq_id',$request->rq);
			if(isset($request->category))
			{
				$q->where('category_id',$request->category);
			}
			else
			{
				$q->whereNull('category_id');
			}
		})->orderBy('name','asc')->get();
		if(isset($request->t_unit) && $request->t_unit != "")
		{
			$flag = true;
			foreach($units as $unit)
			{
				if($unit->name == $request->t_unit)
				{
					$flag = false;
				}
			}
			if($flag)
			{
				$newUnit = new App\Unit();
				$newUnit->name = $request->t_unit;
				$units[] = $newUnit;
			}
		}
		return view('administracion.requisicion.parcial.unit_options',['units' => $units]);
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
					$tempDatepath = $request->datepath[$i] != "" ? Carbon::createFromFormat('d-m-Y', $request->datepath[$i])->format('Y-m-d') : $request->datepath[$i];
					$tempTimepath = $request->timepath[$i] != "" ? Carbon::createFromFormat('H:i', $request->timepath[$i])->format('H:i:s') : $request->timepath[$i];
					$options				= [];
					$options['fiscal_val']	= $request->fiscal_folio[$i];
					$options['ticket_val']	= $request->ticket_number[$i];
					$options['date']		= $tempDatepath;
					$options['time']		= $tempTimepath;
					$options['amount']		= $request->amount[$i];

					$folio      = $request->requestFolio;
					$check_docs = App\Functions\DocsValidate::validate($options,$folio);
					
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

	public function viewDetailEmployee(Request $request)
	{
		if ($request->ajax()) 
		{
			$employee = App\RequisitionEmployee::find($request->employee_id);
			return view('administracion.requisicion.parcial.detalles_empleado',['employee'=>$employee]);
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[229,230])->count()>0)
		{
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$headerStyle  = (new StyleBuilder())->setFontName('Calibri')->setFontSize(16)->setFontBold()->build();
			$rowDark      = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('catalogos-empleados.xlsx');
			for ($i=0; $i <= 16; $i++)
			{
				switch ($i)
				{
					case 0:
						$title = 'Estados';
						$values = App\State::select('idstate','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Estado",$headerStyle)
						];
						break;
					case 1:
						$title = 'Proyectos';
						$values = App\Project::selectRaw('idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')->whereIn('idproyect',Auth::user()->inChargeProject(229)->pluck('project_id'))->where('status',1)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Proyecto",$headerStyle)
						];
						break;
					case 2:
						$title = 'WBS';
						$values = App\CatCodeWBS::selectRaw('id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')->join('projects','cat_code_w_bs.project_id','projects.idproyect')->where('cat_code_w_bs.status',1)->where('projects.status',1)->whereIn('projects.idproyect',Auth::user()->inChargeProject(229)->pluck('project_id'))->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("WBS",$headerStyle),
							WriterEntityFactory::createCell("Proyecto",$headerStyle)
						];
						break;
					case 3:
						$title = 'Empresa';
						$values = App\Enterprise::select('id','name')->where('id',5)->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Empresa",$headerStyle)
						];
						break;
					case 4:
						$title = 'Direcciones';
						$values = App\Area::select('id','name')->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Dirección",$headerStyle)
						];
						break;
					case 5:
						$title = 'Clasificación del gasto';
						$values = App\Account::selectRaw('idAccAcc, CONCAT(account," ",description," (",content,")"), enterprises.name')->where('selectable',1)->join('enterprises','accounts.idEnterprise','enterprises.id')->where('accounts.idEnterprise',5)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Clasificación",$headerStyle),
							WriterEntityFactory::createCell("Empresa",$headerStyle)
						];
						break;
					case 6:
						$title = 'Departamentos';
						$values = App\Department::select('id','name')->where('status','ACTIVE')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Departamento",$headerStyle)
						];
						break;
					case 7:
						$title = 'Subdepartamentos';
						$values = App\Subdepartment::select('id','name')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Subdepartamento",$headerStyle)
						];
						break;
					case 8:
						$title = 'Lugares de trabajo';
						$values = App\Place::select('id','place')->where('status',1)->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Lugar de trabajo",$headerStyle)
						];
						break;
					case 9:
						$title = 'Tipos de contrato';
						$values = App\CatContractType::select('id','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Tipo de contrato",$headerStyle)
						];
						break;
					case 10:
						$title = 'Regimenes';
						$values = App\CatRegimeType::select('id','description')->get();
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
						$values = App\CatPeriodicity::select('c_periodicity','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Periodicidad",$headerStyle)
						];
						break;
					case 13:
						$title = 'Formas de pago';
						$values = App\PaymentMethod::select('idpaymentMethod','method')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Forma de pago",$headerStyle)
						];
						break;
					case 14:
						$title = 'Bancos';
						$values = App\CatBank::select('c_bank','description')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Banco",$headerStyle)
						];
						break;
					case 15:
						$title = 'Código postal';
						$values = App\CatZipCode::select('zip_code','states.description')->join('states','state','c_state')->orderBy('state')->get();
						$headers = [
							WriterEntityFactory::createCell("ID",$headerStyle),
							WriterEntityFactory::createCell("Código postal",$headerStyle)
						];
						break;
					case 16:
						$title = 'Régimen fiscal';
						$values = App\CatTaxRegime::select('taxRegime','description')->where('physical','Sí')->get();
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
				$kindRow = true;
				foreach($values as $valTmp)
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function curpValidate(Request $request)
	{
		$response = array(
			'valid'   => false,
			'message'	=> 'El campo es requerido.',
			'class' => 'error'
		);
		if(preg_match("/^[A-Z]{1}[AEIOUX]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/i", $request->curp))
		{
			$check_requisition 	= App\RequisitionEmployee::leftJoin('requisitions','requisition_employees.requisition_id','requisitions.id')
				->leftJoin('request_models','requisitions.idFolio','request_models.folio')
				->select('requisition_employees.curp')
				->whereNotIn('request_models.status',[5,6,7,23,28])
				->where('requisition_employees.curp',$request->curp)
				->where(function($query) use($request)
				{
					if (isset($request->folio) && $request->folio != "") 
					{
						$query->where('request_models.folio','!=',$request->folio);
					}
				})
				->get();

			if (count($check_requisition)>0) 
			{
				if(isset($request->oldCurp) && $request->oldCurp===$request->curp)
				{
					$response = array('valid' => true,'class' => 'valid','message' => '');
				}
				else
				{
					$response = array(
						'valid'   => false,
						'message' => 'El empleado ya fue ingresado a una requisición anteriormente.',
						'class' => 'error'
					);
				}
			}
			else
			{
				$response = array('valid' => true,'class'=>'valid','message'=>'');
			}
		}
		elseif($request->curp!='')
		{
			$response = array(
				'valid'   => false,
				'message'	=> 'El CURP debe ser válido.',
				'class' => 'error'
			);
		}
		return Response($response);
	}

	public function viewDetailPurchase(Request $request)
	{
		if ($request->ajax()) 
		{
			$request = App\RequestModel::find($request->folio);
			return view('administracion.requisicion.parcial.modal_compra',['request'=>$request]);
		}
	}

	public function zipCode(Request $request)
	{
		if ($request->ajax())
		{
			if($request->search!= '')
			{
				$result = array();
				$clave = App\CatZipCode::where('zip_code','LIKE','%'.$request->search.'%')
				->get();
				foreach ($clave as $c)
				{
					$tempArray['id']	= $c->zip_code;
					$tempArray['text']	= $c->zip_code;
					$result['results'][]	= $tempArray;
				}
				if(count($clave)==0)
				{
					$result['results'] = [];
				}
				return Response($result);
			}
		}
	}
}
