<?php

namespace App\Http\Controllers;

use App\Blueprints;
use Illuminate\Http\Request;
use App;
use Auth;
use App\Module;
use App\CatCodeWBS;
use App\CatContractItem;
use App\Contractor;
use App\Contract;
use App\CatTM;
use Ilovepdf\CompressTask;
use App\Functions\Files;
use Illuminate\Support\Facades\DB;
use PDF;
use Excel;
use Carbon\Carbon;
use Lang;

class OperationPCDailyReportController extends Controller
{
	private $module_id = 313;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',314)->count()>0)
		{
			$data 	= Module::find($this->module_id);
			$tmCat  = CatTM::get();
			return view('operacion.control_proyecto.reporte_diario.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id' 	=> $this->module_id,
				'tmCat'		=> $tmCat,
				'option_id' => 314
			]);
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',314)->count()>0)
		{
			$t_request                         = new App\PCDailyReport();
			$t_request->user_elaborate_id      = Auth::user()->id;
			$t_request->project_id             = $request->project_id;
			$t_request->contract_id            = $request->contract_id;
			$t_request->date                   = Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d');
			$t_request->wbs_id                 = $request->code_wbs;
			$t_request->weather_conditions_id  = $request->weather;
			$t_request->discipline_id          = $request->discipline;
			$t_request->work_hours_from        = $request->worker_hours_from;
			$t_request->work_hours_to          = $request->worker_hours_to;
			$t_request->tm_internal_hours_from = $request->internal_tm_from;
			$t_request->tm_internal_hours_to   = $request->internal_tm_to;
			$t_request->tm_internal_id         = $request->internal_tm;
			$t_request->tm_client_hours_from   = $request->customer_tm_from;
			$t_request->tm_client_hours_to     = $request->customer_tm_to;
			$t_request->tm_client_id           = $request->customer_tm;
			$t_request->comments               = $request->comment;
			$t_request->status                 = $request->status;
			$t_request->project                = 'R2B';
			$t_request->package                = 'P6';
			$t_request->kind_doc               = 'RD';
			$t_request->name_file              = $t_request->project.''.$t_request->package.''.$t_request->kind_doc;
			$t_request->save();
			$id = $t_request->id;
			for($i = 0; $i < count($request->t_pda_contract); $i++)
			{
				if($request->idpcdrDetail[$i] == 'x')
				{
					$t_pcdr_detail                     = new App\PCDailyReportDetail();
					$t_pcdr_detail->contract_item_id   = $request->t_pda_contract[$i];
					$t_pcdr_detail->quantity           = $request->t_quantity[$i];
					$t_pcdr_detail->amount             = $request->t_amount[$i];
					$t_pcdr_detail->contractor_id      = $request->t_contractor[$i];
					$t_pcdr_detail->area               = $request->t_area[$i];
					$t_pcdr_detail->place_area         = $request->t_place_area[$i];
					$t_pcdr_detail->num_ppt            = $request->t_ppt[$i];
					$t_pcdr_detail->blueprint_id       = $request->t_blueprint[$i];
					$t_pcdr_detail->comments           = $request->t_observs[$i];
					$t_pcdr_detail->accumulated        = $request->t_accumulated[$i];
					$t_pcdr_detail->pc_daily_report_id = $id;
					$t_pcdr_detail->save();

					$id_pcdr_detail                    = $t_pcdr_detail->id;

					if($request->t_doc_quality[$i] != null)
					{
						$t_documents                  = new App\PCDailyReportDocuments();
						$t_documents->path            = $request->t_doc_quality[$i];
						$t_documents->kind            = 'DOC_CALIDAD';
						$t_documents->pcdr_details_id = $id_pcdr_detail;
						$t_documents->save();	
					}
					$t_documents                  = new App\PCDailyReportDocuments();
					$t_documents->path            = $request->t_image_activity[$i];
					$t_documents->kind            = 'ADJ_IMAGEN';
					$t_documents->pcdr_details_id = $id_pcdr_detail;
					$t_documents->save();
				}
			}
			for($i = 0; $i < count($request->t_meh_quantity); $i++) 
			{
				if($request->pcdrMEH[$i] == 'x')
				{
					$t_pcdr_meh                     = new App\PCDailyReportMeh();
					$t_pcdr_meh->quantity           = $request->t_meh_quantity[$i];
					$t_pcdr_meh->machinery_id       = $request->t_meh_desc[$i];
					$t_pcdr_meh->pc_daily_report_id = $id;
					$t_pcdr_meh->save();
				}
			}
			for($i = 0; $i < count($request->t_staff_quantity); $i++) 
			{
				if($request->pcdrStaff[$i] == 'x')
				{
					$t_pcdr_staff                      = new App\PCDailyReportStaff();
					$t_pcdr_staff->quantity            = $request->t_staff_quantity[$i];
					$t_pcdr_staff->industrial_staff_id = $request->t_staff_desc[$i];
					$t_pcdr_staff->hours               = $request->t_staff_quantity_hours[$i];
					$t_pcdr_staff->pc_daily_report_id  = $id;
					$t_pcdr_staff->save();
				}
			}
			for($i = 0; $i < count($request->t_signature_name); $i++) 
			{
				if($request->pcdrSignatures[$i] == 'x')
				{
					$t_pcdr_signature                     = new App\PCDailyReportSignature();
					$t_pcdr_signature->name               = $request->t_signature_name[$i];
					$t_pcdr_signature->position           = $request->t_signature_position[$i];
					$t_pcdr_signature->pc_daily_report_id = $id;
					$t_pcdr_signature->save();
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('operation/project-control/daily-report')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function follow($id) 
	{
		if(Auth::user()->module->where('id',315)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$thisModule = App\Module::find(315);
			$tmCat 		= CatTM::get();
			$request    = App\PCDailyReport::whereIn('pc_daily_report.project_id',Auth::user()->inChargeProject(315)->pluck('project_id'))->find($id);
			if ($request != "") 
			{
				return view('operacion.control_proyecto.reporte_diario.alta',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details' 	=> $thisModule['details'],
					'child_id' 	=> $this->module_id,
					'option_id'	=> 315,
					'request' 	=> $request,
					'tmCat'		=> $tmCat,
				]);
			}
			else
			{
				return abort(404);
			}
			
		}
		else
		{
			return abort(404);
		}
	}

	public function delete($id) 
	{
		if(Auth::user()->module->where('id',315)->count()>0)
		{
			App\PCDailyReport::find($id)
			->update([
				'status' => "2",
			]);
			$alert = "swal('', '".Lang::get("messages.record_deleted")."', 'success');";
			return redirect('operation/project-control/daily-report/search')->with('alert',$alert);			
		}
		else
		{
			return abort(404);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{		
			$data                              = App\Module::find($this->module_id);
			$t_request                         = App\PCDailyReport::find($id);
			if($t_request->status != 2)
			{
				$t_request->project_id             = $request->project_id;
				$t_request->contract_id            = $request->contract_id;
				$t_request->date                   = $request->datetitle;
				$t_request->wbs_id                 = $request->code_wbs;
				$t_request->weather_conditions_id  = $request->weather;
				$t_request->discipline_id          = $request->discipline;
				$t_request->work_hours_from        = $request->worker_hours_from;
				$t_request->work_hours_to          = $request->worker_hours_to;
				$t_request->tm_internal_hours_from = $request->internal_tm_from;
				$t_request->tm_internal_hours_to   = $request->internal_tm_to;
				$t_request->tm_internal_id         = $request->internal_tm;
				$t_request->tm_client_hours_from   = $request->customer_tm_from;
				$t_request->tm_client_hours_to     = $request->customer_tm_to;
				$t_request->tm_client_id           = $request->customer_tm;
				$t_request->comments               = $request->comment;
				$t_request->status                 = $request->status;
				$t_request->project                = 'R2B';
				$t_request->package                = 'P6';
				$t_request->kind_doc               = 'RD';
				$t_request->name_file              = $t_request->project.''.$t_request->package.''.$t_request->kind_doc;
				$t_request->save();
				$id                                = $t_request->id;
				if(isset($request->deleteActivity))
				{
					$countConceptsDelete = count($request->deleteActivity);
					if ($countConceptsDelete > 0)
					{
						for ($i=0; $i < $countConceptsDelete; $i++) 
						{ 		
							if (App\PCDailyReportDetail::where('id',$request->deleteActivity[$i])->count() > 0) 
							{
								$filesDeleted = App\PCDailyReportDocuments::where('pcdr_details_id',$request->deleteActivity[$i])->get();
								foreach ($filesDeleted as $file)
								{
									\Storage::disk('public')->delete('/docs/daily_report_operations/'.$file->path);
								}
								App\PCDailyReportDocuments::where('pcdr_details_id',$request->deleteActivity[$i])->delete();
								App\PCDailyReportDetail::where('id',$request->deleteActivity[$i])->delete();	
							}
						}
					}
				}
				if(isset($request->deleteDocs))
				{
					$countDocsDelete = count($request->deleteDocs);
					if ($countDocsDelete > 0)
					{
						for ($i=0; $i < $countDocsDelete; $i++) 
						{
							\Storage::disk('public')->delete('/docs/daily_report_operations/'.$request->deleteDocs[$i]);
							$deletePath     = App\PCDailyReportDocuments::where('path',$request->deleteDocs[$i])->first();
							$nullPath       = App\PCDailyReportDocuments::find($deletePath['id']);
							$nullPath->path = null;
							$nullPath->save();
						}
					}
				}
				if(isset($request->deleteMEH))
				{
					$countMEHDelete = count($request->deleteMEH);
					if ($countMEHDelete > 0)
					{
						for ($i=0; $i < $countMEHDelete; $i++) 
						{ 		
							if (App\PCDailyReportMeh::where('id',$request->deleteMEH[$i])->count() > 0) 
							{
								App\PCDailyReportMeh::where('id',$request->deleteMEH[$i])->delete();	
							}
						}
					}
				}
				if(isset($request->deleteStaff))
				{
					$countStaffDelete = count($request->deleteStaff);
					if ($countStaffDelete > 0)
					{
						for ($i=0; $i < $countStaffDelete; $i++) 
						{ 		
							if (App\PCDailyReportStaff::where('id',$request->deleteStaff[$i])->count() > 0) 
							{
								App\PCDailyReportStaff::where('id',$request->deleteStaff[$i])->delete();	
							}
						}
					}
				}
				if(isset($request->deleteSignature))
				{
					$countSignatureDelete = count($request->deleteSignature);
					if ($countSignatureDelete > 0)
					{
						for ($i=0; $i < $countSignatureDelete; $i++) 
						{ 		
							if (App\PCDailyReportSignature::where('id',$request->deleteSignature[$i])->count() > 0) 
							{
								App\PCDailyReportSignature::where('id',$request->deleteSignature[$i])->delete();	
							}
						}
					}
				}
				for($i = 0; $i < count($request->t_pda_contract); $i++)
				{
					if($request->idpcdrDetail[$i] == 'x')
					{
						$t_pcdr_detail = new App\PCDailyReportDetail();
					}
					else
					{
						$t_pcdr_detail = App\PCDailyReportDetail::find($request->idpcdrDetail[$i]);
					}
					$t_pcdr_detail->contract_item_id   = $request->t_pda_contract[$i];
					$t_pcdr_detail->quantity           = $request->t_quantity[$i];
					$t_pcdr_detail->amount             = $request->t_amount[$i];
					$t_pcdr_detail->contractor_id      = $request->t_contractor[$i];
					$t_pcdr_detail->area               = $request->t_area[$i];
					$t_pcdr_detail->place_area         = $request->t_place_area[$i];
					$t_pcdr_detail->num_ppt            = $request->t_ppt[$i];
					$t_pcdr_detail->blueprint_id       = $request->t_blueprint[$i];
					$t_pcdr_detail->comments           = $request->t_observs[$i];
					$t_pcdr_detail->accumulated        = $request->t_accumulated[$i];
					$t_pcdr_detail->pc_daily_report_id = $id;
					$t_pcdr_detail->save();

					$id_pcdr_detail = $t_pcdr_detail->id;
					if($request->t_doc_quality[$i] != null)
					{
						if ($request->id_doc_quality[$i] == 'x')
						{
							$t_documents = new App\PCDailyReportDocuments();
						}
						else
						{
							$t_documents = App\PCDailyReportDocuments::find($request->id_doc_quality[$i]);
						}
						$t_documents->path            = $request->t_doc_quality[$i];
						$t_documents->kind            = 'DOC_CALIDAD';
						$t_documents->pcdr_details_id = $id_pcdr_detail;
						$t_documents->save();
					}
					else
					{
						if ($request->id_doc_quality[$i] != 'x')
						{
							App\PCDailyReportDocuments::where('id',$request->id_doc_quality[$i])->delete();
						}
					}

					if ($request->id_image_activity[$i] == 'x')
					{
						$t_documents = new App\PCDailyReportDocuments();
					}
					else
					{
						$t_documents = App\PCDailyReportDocuments::find($request->id_image_activity[$i]);
					}
					$t_documents->path            = $request->t_image_activity[$i];
					$t_documents->kind            = 'ADJ_IMAGEN';
					$t_documents->pcdr_details_id = $id_pcdr_detail;
					$t_documents->save();
				}
				for($i = 0; $i < count($request->t_meh_quantity); $i++) 
				{
					if($request->pcdrMEH[$i] == 'x')
					{
						$t_pcdr_meh = new App\PCDailyReportMeh();	
					}
					else
					{
						$t_pcdr_meh	= App\PCDailyReportMeh::find($request->pcdrMEH[$i]);
					}

					$t_pcdr_meh->quantity           = $request->t_meh_quantity[$i];
					$t_pcdr_meh->machinery_id       = $request->t_meh_desc[$i];
					$t_pcdr_meh->pc_daily_report_id = $id;
					$t_pcdr_meh->save();
				}
				for($i = 0; $i < count($request->t_staff_quantity); $i++) 
				{
					if($request->pcdrStaff[$i] == 'x')
					{
						$t_pcdr_staff = new App\PCDailyReportStaff();
					}
					else
					{
						$t_pcdr_staff = App\PCDailyReportStaff::find($request->pcdrStaff[$i]);
					}

					$t_pcdr_staff->quantity				= $request->t_staff_quantity[$i];
					$t_pcdr_staff->industrial_staff_id	= $request->t_staff_desc[$i];
					$t_pcdr_staff->hours				= $request->t_staff_quantity_hours[$i];
					$t_pcdr_staff->pc_daily_report_id	= $id;
					$t_pcdr_staff->save();
				}
				for($i = 0; $i < count($request->t_signature_name); $i++) 
				{
					if($request->pcdrSignatures[$i] == 'x')
					{
						$t_pcdr_signature = new App\PCDailyReportSignature();
					}
					else
					{
						$t_pcdr_signature = App\PCDailyReportSignature::find($request->pcdrSignatures[$i]);
					}
					$t_pcdr_signature->name               = $request->t_signature_name[$i];
					$t_pcdr_signature->position           = $request->t_signature_position[$i];
					$t_pcdr_signature->pc_daily_report_id = $id;
					$t_pcdr_signature->save();
				}
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return redirect()->route('project-control.daily-report.edit',$id)->with('alert',$alert);

			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return redirect()->route('project-control.daily-report.search')->with('alert',$alert);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',315)->count()>0)
		{
			$id_report  = $request->id_report != '' ? $request->id_report: null;
			$num_report = $request->num_report != '' ? $request->num_report: null;
			$name       = $request->name != '' ? $request->name: null;
			$mindate    = $request->mindate != '' ? $request->mindate: null;
			$maxdate    = $request->maxdate != '' ? $request->maxdate: null;
			$contract   = $request->contract != '' ? $request->contract: null;
			$project    = $request->project != '' ? $request->project: null;
			$code_wbs   = $request->code_wbs != '' ? $request->code_wbs: null;
			$weather    = $request->weather != '' ? $request->weather: null;
			$discipline = $request->discipline != '' ? $request->discipline: null;
			$status     = $request->status != '' ? $request->status: null;
			$data       = Module::find($this->module_id);
			$requests   = App\PCDailyReport::select('pc_daily_report.*')
				->leftjoin('cat_code_w_bs','pc_daily_report.wbs_id','=','cat_code_w_bs.id')
				->leftjoin('cat_disciplines','pc_daily_report.discipline_id','=','cat_disciplines.id')
				->whereIn('pc_daily_report.project_id',Auth::user()->inChargeProject(315)->pluck('project_id'))
				->where('pc_daily_report.status','NOT LIKE','2')
				->where(function ($query) use ($id_report, $num_report, $name, $mindate, $maxdate, $contract, $project, $code_wbs, $weather, $discipline, $status)
				{
					if($id_report != "")
					{
						$query->where('pc_daily_report.id',$id_report);
					}
					if($num_report != "")
					{	
						$query->where(DB::raw("CONCAT_WS('-',pc_daily_report.project,pc_daily_report.package,cat_code_w_bs.code,cat_disciplines.indicator,pc_daily_report.kind_doc,pc_daily_report.id)"),'LIKE','%'.$num_report.'%');
					}
					if($name != "")
					{
						$query->whereHas('elaborateUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('pc_daily_report.created_at',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($contract != "") 
					{
						$query->where('pc_daily_report.contract_id',$contract);
					}
					if ($project != "") 
					{
						$query->where('pc_daily_report.project_id',$project);
					}
					if ($code_wbs != "") 
					{
						$query->where('pc_daily_report.wbs_id',$code_wbs);
					}
					if ($weather != "") 
					{
						$query->where('pc_daily_report.weather_conditions_id',$weather);
					}
					if ($discipline != "") 
					{
						$query->where('pc_daily_report.discipline_id',$discipline);
					}
					if($status != "")
					{
						$query->where('pc_daily_report.status',$status);
					}
				})
				->orderBy('pc_daily_report.created_at','DESC')
				->paginate(10);
			return view('operacion.control_proyecto.reporte_diario.busqueda',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id' 		=> $this->module_id,
				'requests'		=> $requests,
				'num_report'	=> $num_report,
				'name'			=> $name,         	
				'mindate'		=> $mindate,
				'maxdate'		=> $maxdate,
				'contract'		=> $contract,
				'project'		=> $project,
				'code_wbs'		=> $code_wbs,
				'weather'		=> $weather,
				'discipline'	=> $discipline,
				'status'		=> $status,
				'option_id' 	=> 315
			]);
		}
		else
		{
			return abort(404);
		}
	}

	public function exportFollow(Request $request)
	{
		if (Auth::user()->module->where('id',315)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$id_report     	= $request->id_report != '' ? $request->id_report: null;
			$num_report     = $request->num_report != '' ? $request->num_report: null;
			$name         	= $request->name != '' ? $request->name: null;
			$mindate        = $request->mindate != '' ? $request->mindate: null;
			$maxdate        = $request->maxdate != '' ? $request->maxdate: null;
			$contract		= $request->contract != '' ? $request->contract: null;
			$project		= $request->project != '' ? $request->project: null;
			$code_wbs		= $request->code_wbs != '' ? $request->code_wbs: null;
			$weather		= $request->weather != '' ? $request->weather: null;
			$discipline		= $request->discipline != '' ? $request->discipline: null;
			$status         = $request->status != '' ? $request->status: null;

			Excel::create('Proyecta Industrial de México', function($excel) use ($id_report, $num_report, $name, $mindate, $maxdate, $contract, $project, $code_wbs, $weather, $discipline, $status)
			{
				$excel->sheet('Reportes Diarios',function($sheet) use ($id_report, $num_report, $name, $mindate, $maxdate, $contract, $project, $code_wbs, $weather, $discipline, $status)
				{
					$sheet->setStyle(array(
						'font' => array(
							'name'	=> 'Calibri',
							'size'	=> 12
						)
					));
					$sheet->setColumnFormat(array(
						'B' => 'yyyy-mm-dd',
					));
					$sheet->mergeCells('A1:H1');
					$sheet->cell('A1:H1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:H2', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:H2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Proyecta Industrial de México']);
					$sheet->row(2,['Id','Fecha','WBS','Disciplina','Elaboro','No. De Reporte','Paquete','Estatus']);

					$requests = App\PCDailyReport::select('pc_daily_report.*')
						->leftjoin('cat_code_w_bs','pc_daily_report.wbs_id','=','cat_code_w_bs.id')
						->leftjoin('cat_disciplines','pc_daily_report.discipline_id','=','cat_disciplines.id')
						->whereIn('pc_daily_report.project_id',Auth::user()->inChargeProject(315)->pluck('project_id'))
						->where('pc_daily_report.status','NOT LIKE','2')
						->where(function ($query) use ($id_report, $num_report, $name, $mindate, $maxdate, $contract, $project, $code_wbs, $weather, $discipline, $status)
						{
							if($id_report != "")
							{
								$query->where('pc_daily_report.id',$id_report);
							}
							if($num_report != "")
							{	
								$query->where(DB::raw("CONCAT_WS('-',pc_daily_report.project,pc_daily_report.package,cat_code_w_bs.code,cat_disciplines.indicator,pc_daily_report.kind_doc,pc_daily_report.id)"),'LIKE','%'.$num_report.'%');
							}
							if($name != "")
							{
								$query->whereHas('elaborateUser', function($q) use($name)
								{
									$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
								});
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('pc_daily_report.created_at',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($contract != "") 
							{
								$query->where('pc_daily_report.contract_id',$contract);
							}
							if ($project != "") 
							{
								$query->where('pc_daily_report.project_id',$project);
							}
							if ($code_wbs != "") 
							{
								$query->where('pc_daily_report.wbs_id',$code_wbs);
							}
							if ($weather != "") 
							{
								$query->where('pc_daily_report.weather_conditions_id',$weather);
							}
							if ($discipline != "") 
							{
								$query->where('pc_daily_report.discipline_id',$discipline);
							}
							if($status != "")
							{
								$query->where('pc_daily_report.status',$status);
							}
						})
						->orderBy('pc_daily_report.created_at','DESC')
						->get();
					foreach ($requests as $request)
					{
						$row	= [];
						$row[]	= $request->id;
						$date	= new \DateTime($request->created_at);
						$row[]	= $date->format('d/m/Y');
						$row[]	= $request->wbs->code;
						$row[]	= $request->discipline->name;
						$row[]	= $request->elaborateUser->fullName();
						$row[]	= $request->noReport();
						$row[]	= $request->package;
						$row[]	= $request->status == 1 ? 'ABIERTO' : 'CERRADO';
						$sheet->appendRow($row);
					}
				});
			})->export('xls');
		}
		else
		{
			return redirect('/');
		}
	}

	public function wbs_search(Request $request)
	{
		if($request->ajax())
		{
			
			$wbs = Contract::find($request->idcontract)
					->wbs;
			return Response($wbs);
		}
	}

	public function contracts_search(Request $request)
	{
		if($request->ajax())
		{
			$contracts = Contract::where('project_id',$request->idproject)
							->get();
			return Response($contracts);
		}
	}

	public function contract_item_search(Request $request)
	{
		if($request->ajax())
		{
			$contract = CatContractItem::where('contract_id',$request->idcontract)
						->get();
			return Response($contract);
		}
	}

	public function contract_item_search_data(Request $request)
	{
		if($request->ajax())
		{
			$contract = CatContractItem::find($request->contract);
			return Response($contract);
		}
	}

	public function contractor_search(Request $request)
	{
		if($request->ajax())
		{
			$contractor = Contractor::where('wbs_id', $request->wbs)
							->where('contract_id',$request->contract)
							->orderBy('name','asc')
							->get();
			return Response($contractor);
		}
	}

	public function blueprints_search(Request $request)
	{
		if($request->ajax())
		{
			$blueprints = Blueprints::where('wbs_id',$request->wbs)
							->where('contract_id',$request->contract)
							->orderBy('name','asc')
							->get();
			return Response($blueprints);
		}
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'   => 'ERROR',
			'message' => 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				\Storage::disk('public')->delete('/docs/daily_report_operations/'.$request->realPath);
			}
			if($request->file('path'))
			{
				$extention            = strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention = 'AdG'.round(microtime(true) * 1000).'_PCdailyReportDoc.';
				$name                 = $nameWithoutExtention.$extention;
				$destinity = '/docs/daily_report_operations/'.$name;

				if($request->kind == "image")
				{
					try
					{
						if (!empty($request->path) && file_exists($request->path)) 
						{
							$sourceData	           = file_get_contents($request->path);
							$resultData	           = \Tinify\fromBuffer($sourceData)->toBuffer();
							\Storage::disk('public')->put($destinity,$resultData);
							$response['error']     = 'DONE';
							$response['path']      = $name;
							$response['message']   = '';
							$response['extention'] = $extention;
						}
						else
						{
							$response['message'] = 'Ocurrió un problema, por favor verifique su archivo.';
						}
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message'] = $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message'] = 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message'] = 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					
				}
				if($request->kind == "pdf")
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
						$response['error']     = 'DONE';
						$response['path']      = $name;
						$response['message']   = '';
						$response['extention'] = $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console'] = $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function reportPDF($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$requests = App\PCDailyReport::find($id);
			
			if ($requests != "" && $requests->status != 2)
			{
				$pdf = \App::make('dompdf.wrapper');
				$pdf->setPaper('letter');
				$pdf->getDomPDF()->set_option("enable_php", true);
				$pdf->loadView('operacion.control_proyecto.reporte_diario.pc_daily_report_pdf',['requests'=>$requests]);
				return $pdf->download($requests->noReport().'.pdf');
			}
			else
			{
				return abort(404);
			}	
		}
		else
		{
			return abort(404);
		}
	}
}
