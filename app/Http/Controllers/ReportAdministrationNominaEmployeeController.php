<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class ReportAdministrationNominaEmployeeController extends Controller
{
	private $module_id = 96;
	public function nominaEmployeeReport(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$data          = App\Module::find($this->module_id);
			$title         = $request->title;
			$enterprise    = $request->enterprise;
			$type          = $request->type;
			$fiscal        = $request->fiscal;
			$status        = $request->status;
			$employee      = $request->employee;
			$folio         = $request->folio;
			$wbs           = $request->wbs;
			$subdepartment = $request->subdepartment;
			$mindate       = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate       = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$project       = $request->project;

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = $mindate->format('Y-m-d');
				$endRange   = $maxdate->format('Y-m-d');

				if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate!="" && $maxdate!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$nominaEmployee = App\NominaEmployee::select('*')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')])
							->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
					}
					if ($wbs != "")
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->paginate(10);
			return view('reporte.administracion.nomina_empleado',
				[
					'id'             => $data['father'],
					'title'          => $data['name'],
					'details'        => $data['details'],
					'child_id'       => $this->module_id,
					'option_id'      => 190,
					'nominaEmployee' => $nominaEmployee,
					'title'          => $title,
					'enterprise'     => $enterprise,
					'type'           => $type,
					'fiscal'         => $fiscal,
					'status'         => $status,
					'employee'       => $employee,
					'folio'          => $folio,
					'mindate'        => $mindate != '' ? $mindate->format('Y-m-d'): null,
					'maxdate'        => $maxdate != '' ? $maxdate->format('Y-m-d'): null,
					'project'        => $project,
					'wbs'            => $wbs,
					'subdepartment'  => $subdepartment
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaEmployeeDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$nomina_employees   = App\NominaEmployee::find($request->idnominaEmployee);
			$nomina         = App\Nomina::find($request->idnomina);
			$typeNomina     = $nomina->idCatTypePayroll;
			$fiscal         = App\RequestModel::find($nomina->idFolio)->taxPayment;

			return view('reporte.administracion.partial.modal_nomina_empleado',[
				'nomina_employees'  =>$nomina_employees,
				'nomina'            =>$nomina,
				'typeNomina'        =>$typeNomina,
				'fiscal'            =>$fiscal
			]);
		}
	}

	public function nominaDisbursementSubdepartment(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$title          = $request->title;
			$enterprise     = $request->enterprise;
			$type           = $request->type;
			$fiscal         = $request->fiscal;
			$status         = $request->status;
			$employee       = $request->employee;
			$folio          = $request->folio;
			$wbs            = $request->wbs;
			$subdepartment  = $request->subdepartment;
			$mindate        = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate        = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project        = $request->project;
			$subdepartments = DB::table('nomina_employees')->selectRaw('wd_departments.subdepartments as subdepartment')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin(DB::raw('(SELECT subdepartments.name AS subdepartments, employee_subdepartments.working_data_id AS wd_id FROM employee_subdepartments INNER JOIN subdepartments ON subdepartment_id = subdepartments.id WHERE employee_subdepartments.id IN(SELECT MIN(employee_subdepartments.id) FROM employee_subdepartments GROUP BY employee_subdepartments.working_data_id)) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('wd_departments.subdepartments')
				->get();
			$employees = DB::table('nomina_employees')->selectRaw('wd_departments.subdepartments as subdepartment, real_employees.id')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin(DB::raw('(SELECT subdepartments.name AS subdepartments, employee_subdepartments.working_data_id AS wd_id FROM employee_subdepartments INNER JOIN subdepartments ON subdepartment_id = subdepartments.id WHERE employee_subdepartments.id IN(SELECT MIN(employee_subdepartments.id) FROM employee_subdepartments GROUP BY employee_subdepartments.working_data_id)) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('subdepartment')
				->groupBy('real_employees.id')
				->get();
			$weeks = DB::table('nomina_employees')->selectRaw('UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as week')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('week')
				->orderBy('nomina_employees.from_date')
				->get();
			$camping = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(nomina_employee_n_fs.amount),2) as amount,
					wd_departments.subdepartments as subdepartment
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT subdepartments.name AS subdepartments, employee_subdepartments.working_data_id AS wd_id FROM employee_subdepartments INNER JOIN subdepartments ON subdepartment_id = subdepartments.id WHERE employee_subdepartments.id IN(SELECT MIN(employee_subdepartments.id) FROM employee_subdepartments GROUP BY employee_subdepartments.working_data_id)) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->where(function($q)
				{
					$q->where('nominas.title','LIKE','%viático%')
						->orWhere('nominas.title','LIKE','%campamento%');
				})
				->groupBy('subdepartment')
				->groupBy('periodRange')
				->orderBy('nomina_employees.from_date')
				->get();
			$nonFiscal = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(nomina_employee_n_fs.amount),2) as amount,
					wd_departments.subdepartments as subdepartment
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT subdepartments.name AS subdepartments, employee_subdepartments.working_data_id AS wd_id FROM employee_subdepartments INNER JOIN subdepartments ON subdepartment_id = subdepartments.id WHERE employee_subdepartments.id IN(SELECT MIN(employee_subdepartments.id) FROM employee_subdepartments GROUP BY employee_subdepartments.working_data_id)) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->where(function($q)
				{
					$q->where('nominas.title','NOT LIKE','%viático%')
						->orWhere('nominas.title','NOT LIKE','%campamento%');
				})
				->groupBy('subdepartment')
				->groupBy('periodRange')
				->orderBy('nomina_employees.from_date')
				->get();
			$fiscal = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(salaries.totalPerceptions),2) as amount,
					wd_departments.subdepartments as subdepartment
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT subdepartments.name AS subdepartments, employee_subdepartments.working_data_id AS wd_id FROM employee_subdepartments INNER JOIN subdepartments ON subdepartment_id = subdepartments.id WHERE employee_subdepartments.id IN(SELECT MIN(employee_subdepartments.id) FROM employee_subdepartments GROUP BY employee_subdepartments.working_data_id)) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('subdepartment')
				->groupBy('periodRange')
				->orderBy('nomina_employees.from_date')
				->get();
			if(count($subdepartments) == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return back()->with('alert',$alert);
			}
			else
			{
				Excel::create('Erogaciones por subdepartamento',function($excel) use($subdepartments,$weeks,$camping,$nonFiscal,$fiscal,$employees)
				{
					if(count($subdepartments) == 0)
					{
						$excel->sheet('Sin subdepartamento',function($sheet) use ($weeks,$camping,$nonFiscal,$fiscal,$employees)
						{
							$sheet->setColumnFormat(array(
								'B' => '"$"#,##0.00_-',
								'C' => '"$"#,##0.00_-',
								'D' => '"$"#,##0.00_-',
								'E' => '"$"#,##0.00_-'
							));
							$sheet->cell('A2:E2', function($cells)
							{
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
								$cells->setBackground('#C9D8EF');
							});
							$sheet->cell('A2', function($cells)
							{
								$cells->setFontWeight('normal');
								$cells->setBackground('#D9D9DA');
							});
							$sheet->mergeCells('A1:E1');
							$sheet->cell('A1:E1', function($cells)
							{
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
							});
							foreach($weeks as $week)
							{
								$sheet->row(1,['Sin Subdepartamento']);
								$sheet->row(2,[$employees->count().' empleados','Viáticos y campamentos','Nóminas no fiscales','Total de persepciones fiscales','Total']);
								$tmpArr   = [];
								$tmpArr[] = $week->week;
								$tmpCamp  = $camping->where('periodRange',$week->week)->first() != '' ? $camping->where('periodRange',$week->week)->first()->amount : '';
								$tmpNF    = $nonFiscal->where('periodRange',$week->week)->first() != '' ? $nonFiscal->where('periodRange',$week->week)->first()->amount : '';
								$taxed    = $fiscal->where('periodRange',$week->week)->first() != '' ? $fiscal->where('periodRange',$week->week)->first()->amount : '';
								$tmpTotal = round(($tmpCamp != '' ? $tmpCamp : 0) + ($tmpNF != '' ? $tmpNF : 0) + ($taxed != '' ? $taxed : 0),2);
								$tmpArr[] = $tmpCamp;
								$tmpArr[] = $tmpNF;
								$tmpArr[] = $taxed;
								if($tmpCamp == '' && $tmpNF == '' && $taxed == '')
								{
									$tmpArr[] = '';
								}
								else
								{
									$tmpArr[] = $tmpTotal;
								}
								$sheet->appendRow($tmpArr);
							}
							$sheet->cell('A3:A'.($weeks->count() + 2), function($cells)
							{
								$cells->setBackground('#FAEADB');
								$cells->setFontWeight('bold');
							});
						});
					}
					else
					{
						foreach ($subdepartments as $subdepartment)
						{
							if($subdepartment->subdepartment == '')
							{
								$sheetName = 'Sin subdepartamento';
							}
							else
							{
								$sheetName = substr(Str::slug($subdepartment->subdepartment),0,30);
							}
							$excel->sheet($sheetName,function($sheet) use ($subdepartment,$weeks,$camping,$nonFiscal,$fiscal,$employees)
							{
								$sheet->setColumnFormat(array(
									'B' => '"$"#,##0.00_-',
									'C' => '"$"#,##0.00_-',
									'D' => '"$"#,##0.00_-',
									'E' => '"$"#,##0.00_-'
								));
								$sheet->cell('A2:E2', function($cells)
								{
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
									$cells->setBackground('#C9D8EF');
								});
								$sheet->cell('A2', function($cells)
								{
									$cells->setFontWeight('normal');
									$cells->setBackground('#D9D9DA');
								});
								$sheet->mergeCells('A1:E1');
								$sheet->cell('A1:E1', function($cells)
								{
									$cells->setFontWeight('bold');
									$cells->setAlignment('center');
									$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
								});
								foreach($weeks as $week)
								{
									$sheet->row(1,[($subdepartment->subdepartment == '' ? 'Sin Subdepartamento' : $subdepartment->subdepartment)]);
									$sheet->row(2,[$employees->where('subdepartment',$subdepartment->subdepartment)->count().' empleados','Viáticos y campamentos','Nóminas no fiscales','Total de persepciones fiscales','Total']);
									$tmpArr   = [];
									$tmpArr[] = $week->week;
									$tmpCamp  = $camping->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first() != '' ? $camping->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first()->amount : '';
									$tmpNF    = $nonFiscal->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first() != '' ? $nonFiscal->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first()->amount : '';
									$taxed    = $fiscal->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first() != '' ? $fiscal->where('periodRange',$week->week)->where('subdepartment',$subdepartment->subdepartment)->first()->amount : '';
									$tmpTotal = round(($tmpCamp != '' ? $tmpCamp : 0) + ($tmpNF != '' ? $tmpNF : 0) + ($taxed != '' ? $taxed : 0),2);
									$tmpArr[] = $tmpCamp;
									$tmpArr[] = $tmpNF;
									$tmpArr[] = $taxed;
									if($tmpCamp == '' && $tmpNF == '' && $taxed == '')
									{
										$tmpArr[] = '';
									}
									else
									{
										$tmpArr[] = $tmpTotal;
									}
									$sheet->appendRow($tmpArr);
								}
								$sheet->cell('A3:A'.($weeks->count() + 2), function($cells)
								{
									$cells->setBackground('#FAEADB');
									$cells->setFontWeight('bold');
								});
							});
						}
					}
				})->export('xlsx');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function nominaDisbursementWbs(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$title         = $request->title;
			$enterprise    = $request->enterprise;
			$type          = $request->type;
			$fiscal        = $request->fiscal;
			$status        = $request->status;
			$employee      = $request->employee;
			$folio         = $request->folio;
			$wbs           = $request->wbs;
			$subdepartment = $request->subdepartment;
			$mindate       = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate       = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project       = $request->project;
			$wbs_query = DB::table('nomina_employees')->selectRaw('nom_wbs.wbs as wbs')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->orderBy('nom_wbs.wbs')
				->groupBy('nom_wbs.wbs')
				->get();
			$employees = DB::table('nomina_employees')->selectRaw('nom_wbs.wbs as wbs, real_employees.id')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('nom_wbs.wbs')
				->groupBy('real_employees.id')
				->get();
			$weeks = DB::table('nomina_employees')->selectRaw('UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as week')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('week')
				->orderBy('nomina_employees.from_date')
				->get();
			$camping = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(nomina_employee_n_fs.amount),2) as amount,
					nom_wbs.wbs as wbs
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->where(function($q)
				{
					$q->where('nominas.title','LIKE','%viático%')
						->orWhere('nominas.title','LIKE','%campamento%');
				})
				->groupBy('nom_wbs.wbs')
				->groupBy(DB::raw('UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))'))
				->orderBy('nomina_employees.from_date')
				->get();
			$nonFiscal = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(nomina_employee_n_fs.amount),2) as amount,
					nom_wbs.wbs as wbs
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('nominas.idCatTypePayroll','001')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->where(function($q)
				{
					$q->where('nominas.title','NOT LIKE','%viático%')
						->orWhere('nominas.title','NOT LIKE','%campamento%');
				})
				->groupBy('nom_wbs.wbs')
				->groupBy(DB::raw('UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))'))
				->orderBy('nomina_employees.from_date')
				->get();
			$fiscal = DB::table('nomina_employees')->selectRaw('
					UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y"))) as periodRange,
					ROUND(SUM(salaries.totalPerceptions),2) as amount,
					nom_wbs.wbs as wbs
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "") 
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereIn('nom_wbs.wbs_id',$wbs);
					}
					if ($subdepartment != "")
					{
						$query->whereIn('worker_datas.id',DB::table('employee_subdepartments')->select('working_data_id')->whereIn('id',$subdepartment)->pluck('working_data_id'));
					}
				})
				->groupBy('nom_wbs.wbs')
				->groupBy(DB::raw('UPPER(CONCAT("sem ",IF(DATE_FORMAT(nomina_employees.from_date,"%u") = "00","01",DATE_FORMAT(nomina_employees.from_date,"%u"))," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))'))
				->orderBy('nomina_employees.from_date')
				->get();
			if(count($wbs_query) == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return redirect()->route('report.nomina-employee.index')->with('alert',$alert);
			}
			else
			{
				Excel::create('Erogaciones por WBS',function($excel) use($wbs_query,$weeks,$camping,$nonFiscal,$fiscal,$employees)
				{
					foreach ($wbs_query as $wbs_selected)
					{
						if($wbs_selected->wbs == '')
						{
							$sheetName = 'Sin WBS';
						}
						else
						{
							$sheetName = substr($wbs_selected->wbs,0,30);
						}
						$excel->sheet($sheetName,function($sheet) use ($wbs_selected,$weeks,$camping,$nonFiscal,$fiscal,$employees)
						{
							$sheet->setColumnFormat(array(
								'B' => '"$"#,##0.00_-',
								'C' => '"$"#,##0.00_-',
								'D' => '"$"#,##0.00_-',
								'E' => '"$"#,##0.00_-'
							));
							$sheet->cell('A2:E2', function($cells)
							{
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
								$cells->setBackground('#C9D8EF');
							});
							$sheet->cell('A2', function($cells)
							{
								$cells->setFontWeight('normal');
								$cells->setBackground('#D9D9DA');
							});
							$sheet->mergeCells('A1:E1');
							$sheet->cell('A1:E1', function($cells)
							{
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
							});
							foreach($weeks as $week)
							{
								$sheet->row(1,[($wbs_selected->wbs == '' ? 'Sin WBS' : $wbs_selected->wbs)]);
								$sheet->row(2,[$employees->where('wbs',$wbs_selected->wbs)->count().' empleados','Viáticos y campamentos','Nóminas no fiscales','Total de persepciones fiscales','Total']);
								$tmpArr   = [];
								$tmpArr[] = $week->week;
								$tmpCamp  = $camping->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first() != '' ? $camping->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first()->amount : '';
								$tmpNF    = $nonFiscal->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first() != '' ? $nonFiscal->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first()->amount : '';
								$taxed    = $fiscal->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first() != '' ? $fiscal->where('periodRange',$week->week)->where('wbs',$wbs_selected->wbs)->first()->amount : '';
								$tmpTotal = round(($tmpCamp != '' ? $tmpCamp : 0) + ($tmpNF != '' ? $tmpNF : 0) + ($taxed != '' ? $taxed : 0),2);
								$tmpArr[] = $tmpCamp;
								$tmpArr[] = $tmpNF;
								$tmpArr[] = $taxed;
								if($tmpCamp == '' && $tmpNF == '' && $taxed == '')
								{
									$tmpArr[] = '';
								}
								else
								{
									$tmpArr[] = $tmpTotal;
								}
								$sheet->appendRow($tmpArr);
							}
							$sheet->cell('A3:A'.($weeks->count() + 2), function($cells)
							{
								$cells->setBackground('#FAEADB');
								$cells->setFontWeight('bold');
							});
						});
					}
				})->export('xlsx');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function nominaEmployeeExcel(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$data              = App\Module::find($this->module_id);
			$title             = $request->title;
			$enterprise        = $request->enterprise;
			$type              = $request->type;
			$fiscal            = $request->fiscal;
			$status            = $request->status;
			$employee          = $request->employee;
			$folio             = $request->folio;
			$wbs               = $request->wbs;
			$subdepartment     = $request->subdepartment;
			$mindate           = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate           = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project           = $request->project;
			$nominaSalaryCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin('payment_methods','salaries.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','salaries.idSalary','=','nomina_employee_accounts.idSalary')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin('cat_periodicities','nomina_employees.idCatPeriodicity','=','cat_periodicities.c_periodicity')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaBonusCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin('payment_methods','bonuses.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','bonuses.idBonus','=','nomina_employee_accounts.idBonus')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaSettlementCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where('nominas.idCatTypePayroll','003')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaLiquidationCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where('nominas.idCatTypePayroll','004')
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaVPCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin('payment_methods','vacation_premia.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','vacation_premia.idvacationPremium','=','nomina_employee_accounts.idvacationPremium')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaPSCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin('payment_methods','profit_sharings.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','profit_sharings.idprofitSharing','=','nomina_employee_accounts.idprofitSharing')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominasNFCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin('payment_methods','nomina_employee_n_fs.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('employee_accounts','nomina_employee_n_fs.idemployeeAccounts','=','employee_accounts.id')
				->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin('cat_periodicities','nominas.idCatPeriodicity','=','cat_periodicities.c_periodicity')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
				->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from extras_nominas group by idnominaemployeenf) as extras_nominas'),'nomina_employee_n_fs.idnominaemployeenf','extras_nominas.idnominaemployeenf')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			if($nominaSalaryCount == 0 && $nominaBonusCount == 0 && $nominaSettlementCount == 0 && $nominaLiquidationCount == 0 && $nominaVPCount == 0 && $nominaPSCount == 0 && $nominasNFCount == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return redirect()->route('report.nomina-employee.index')->with('alert',$alert);
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$mhStyleCol6    = (new StyleBuilder())->setBackgroundColor('548235')->setFontColor(Color::WHITE)->build();
			$mhStyleCol7    = (new StyleBuilder())->setBackgroundColor('EC8500')->setFontColor(Color::WHITE)->build();
			$mhStyleCol8    = (new StyleBuilder())->setBackgroundColor('D8407D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol9    = (new StyleBuilder())->setBackgroundColor('C00001')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol6    = (new StyleBuilder())->setBackgroundColor('A9D08E')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol7    = (new StyleBuilder())->setBackgroundColor('F3B084')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol8    = (new StyleBuilder())->setBackgroundColor('E0B5C7')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol9    = (new StyleBuilder())->setBackgroundColor('C07971')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('nomina-por-empleado.xlsx');
			$newSheet       = false;
			$totalEmployees = array();
			if($nominasNFCount > 0)
			{
				$newSheet = true;
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Nóminas no fiscales');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','','','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS DE COMPLEMENTO','','','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 5)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 12)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 19)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 24)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr    = ['Folio','Correspondiente a','Título','Tipo','Rango de Fechas','Periodicidad','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','Referencia','Razón de pago','Descuento','Extra','Sueldo Neto No Fiscal','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 5)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 12)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 19)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 24)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						IF(
							nominas.idCatTypePayroll = "001",
							IF(
								nominas.idCatPeriodicity = "05",
								UPPER(DATE_FORMAT(nominas.from_date,"%b - %Y")),
								IF(
									nominas.idCatPeriodicity = "04",
									IF(
										nominas.to_date <= DATE_FORMAT(nominas.to_date,"%Y-%m-15"),
										UPPER(CONCAT("1q ",DATE_FORMAT(nominas.from_date,"%b - %Y"))),
										UPPER(CONCAT("2q ",DATE_FORMAT(nominas.from_date,"%b - %Y")))
									),
									UPPER(CONCAT("sem ",DATE_FORMAT(nominas.from_date,"%u")," - ",DATE_FORMAT(nominas.from_date,"%Y")))
								)
							),
							""
						) as periodRange,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						cat_type_payrolls.description as typeNomina,
						CONCAT_WS(" ",nominas.from_date, nominas.to_date) as rangeDate,
						cat_periodicities.description as periodicity,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						nomina_employee_n_fs.reference,
						nomina_employee_n_fs.reasonAmount,
						IF(discount_nomina.amount IS NULL,IF(nomina_employee_n_fs.discount IS NULL,0,nomina_employee_n_fs.discount),discount_nomina.amount) as discounts,
						IFNULL(extras_nominas.amount,0) as extras_nomina,
						nomina_employee_n_fs.amount,
						IFNULL(payment.amount,0) as pagado,
						ROUND(nomina_employee_n_fs.amount - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->leftJoin('payment_methods','nomina_employee_n_fs.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('employee_accounts','nomina_employee_n_fs.idemployeeAccounts','=','employee_accounts.id')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin('cat_periodicities','nominas.idCatPeriodicity','=','cat_periodicities.c_periodicity')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from extras_nominas group by idnominaemployeenf) as extras_nominas'),'nomina_employee_n_fs.idnominaemployeenf','extras_nominas.idnominaemployeenf')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['discounts','extras_nomina','amount','pagado','por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->amount;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->amount;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaSalaryCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Sueldo');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','PERCEPCIONES','','','','','','RETENCIONES','','','','','','NETO','PAGOS','','CUOTAS PATRONALES','','',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 2)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 9)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 16)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 22)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 28)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 34)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 35)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					elseif($k <= 37)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol9);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Correspondiente a','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Días Trabajados','Periodicidad','Rango de Fechas','Días para IMSS','Sueldo','Préstamo','Puntualidad','Asistencia','Subsidio','Total de Percepciones','IMSS','Infonavit','Fonacot','Préstamo','Retención de ISR','Total de Deducciones','Sueldo Neto','Pagado','Por pagar','IMSS Mensual','RCV Bimestral','INFONAVIT Bimestral','Total'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 2)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 9)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 16)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 22)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 28)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 34)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 35)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						IF(
							nomina_employees.idCatPeriodicity = "05",
							UPPER(DATE_FORMAT(nomina_employees.from_date,"%b - %Y")),
							IF(
								nomina_employees.idCatPeriodicity = "04",
								IF(
									nomina_employees.to_date <= DATE_FORMAT(nomina_employees.to_date,"%Y-%m-15"),
									UPPER(CONCAT("1q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y"))),
									UPPER(CONCAT("2q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y")))
								),
								UPPER(CONCAT("sem ",DATE_FORMAT(nomina_employees.from_date,"%u")," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))
							)
						) as periodRange,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						salaries.sd as sd,
						salaries.sdi as sdi,
						salaries.workedDays as workedDays,
						cat_periodicities.description as periodicity,
						CONCAT_WS(" ",nomina_employees.from_date, nomina_employees.to_date) as rangeDate,
						salaries.daysForImss as daysForImss,
						salaries.salary as salary,
						salaries.loan_perception as loan_perception,
						salaries.puntuality as puntuality,
						salaries.assistance as assistance,
						salaries.subsidy as subsidy,
						salaries.totalPerceptions as totalPerceptions,
						salaries.imss as imss,
						salaries.infonavit as infonavit,
						salaries.fonacot as fonacot,
						salaries.loan_retention as loan_retention,
						salaries.isrRetentions as isrRetentions,
						salaries.totalRetentions as totalRetentions,
						salaries.netIncome as netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(salaries.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						ROUND(
							ROUND((salaries.uma * .204) * salaries.daysForImss,2)
							+
							IF(
								(salaries.uma * 3) > salaries.sdi,
								0,
								ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)
							)
							+
							ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2),
							2
						) as imss_by_month,
						ROUND(
							ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2),
							2
						) as rcv_bimonth,
						ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2) as infonavit_month,
						ROUND(
							ROUND((salaries.uma * .204) * salaries.daysForImss,2)
							+
							IF(
								(salaries.uma * 3) > salaries.sdi,
								0,
								ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)
							)
							+
							ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2),
							2
						) as total_employer_contribution,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->leftJoin('payment_methods','salaries.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','salaries.idSalary','=','nomina_employee_accounts.idSalary')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin('cat_periodicities','nomina_employees.idCatPeriodicity','=','cat_periodicities.c_periodicity')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd','sdi','salary', 'loan_perception', 'puntuality', 'assistance', 'subsidy', 'totalPerceptions', 'imss', 'infonavit', 'fonacot', 'loan_retention', 'isrRetentions', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal'] += $dtw->total_employer_contribution;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = $dtw->total_employer_contribution;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaBonusCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Aguinaldo');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','PERCEPCIONES','','','RETENCIONES','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 1)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 20)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 23)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 25)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 26)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias', 'Banco', 'CLABE', 'Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Días para aguinaldo','Parte proporcional para aguinaldo','Aguinaldo exento','Aguinaldo gravable','Total','ISR','Total','Sueldo Neto','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 1)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 20)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 23)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 25)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 26)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						bonuses.sd,
						bonuses.sdi,
						bonuses.dateOfAdmission,
						bonuses.daysForBonuses,
						bonuses.proportionalPartForChristmasBonus,
						bonuses.exemptBonus,
						bonuses.taxableBonus,
						bonuses.totalPerceptions,
						bonuses.isr,
						bonuses.totalTaxes,
						bonuses.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(bonuses.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->leftJoin('payment_methods','bonuses.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','bonuses.idBonus','=','nomina_employee_accounts.idBonus')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd', 'sdi', 'exemptBonus', 'taxableBonus', 'totalPerceptions', 'isr', 'totalTaxes', 'netIncome', 'pagado', 'por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaSettlementCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Finiquito');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','','PERCEPCIONES','','','','','','','','','','RETENCIONES','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 1)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 33)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 35)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 36)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Fecha de baja','Años completos','Días trabajados','Días para vacaciones','Días para aguinaldo','Prima de antigüedad','Indemnización exenta','Indemnización gravada','Vacaciones','Aguinaldo exento','Aguinaldo gravable','Prima vacacional exenta','Prima vacacional gravada','Otras percepciones','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 1)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 33)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 35)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 36)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						liquidations.sd,
						liquidations.sdi,
						liquidations.admissionDate,
						liquidations.downDate,
						liquidations.fullYears,
						liquidations.workedDays,
						liquidations.holidayDays,
						liquidations.bonusDays,
						liquidations.seniorityPremium,
						liquidations.exemptCompensation,
						liquidations.taxedCompensation,
						liquidations.holidays,
						liquidations.exemptBonus,
						liquidations.taxableBonus,
						liquidations.holidayPremiumExempt,
						liquidations.holidayPremiumTaxed,
						liquidations.otherPerception,
						liquidations.totalPerceptions,
						liquidations.isr,
						liquidations.totalRetentions,
						liquidations.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(liquidations.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where('nominas.idCatTypePayroll','003')
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd', 'sdi', 'seniorityPremium', 'exemptCompensation', 'taxedCompensation', 'holidays', 'exemptBonus', 'taxableBonus', 'holidayPremiumExempt', 'holidayPremiumTaxed', 'otherPerception', 'totalPerceptions', 'isr', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaLiquidationCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Liquidación');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','','PERCEPCIONES','','','','','','','','','','','','RETENCIONES','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 1)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 35)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 37)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 38)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Fecha de baja','Años completos','Días trabajados','Días para vacaciones','Días para aguinaldo','Sueldo por liquidación','20 días por año de servicio','Prima de antigüedad','Indemnización exenta','Indemnización gravada','Vacaciones','Aguinaldo exento','Aguinaldo gravable','Prima vacacional exenta','Prima vacacional gravada','Otras percepciones','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 1)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 23)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 35)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 37)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 38)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						liquidations.sd,
						liquidations.sdi,
						liquidations.admissionDate,
						liquidations.downDate,
						liquidations.fullYears,
						liquidations.workedDays,
						liquidations.holidayDays,
						liquidations.bonusDays,
						liquidations.liquidationSalary,
						liquidations.twentyDaysPerYearOfServices,
						liquidations.seniorityPremium,
						liquidations.exemptCompensation,
						liquidations.taxedCompensation,
						liquidations.holidays,
						liquidations.exemptBonus,
						liquidations.taxableBonus,
						liquidations.holidayPremiumExempt,
						liquidations.holidayPremiumTaxed,
						liquidations.otherPerception,
						liquidations.totalPerceptions,
						liquidations.isr,
						liquidations.totalRetentions,
						liquidations.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(liquidations.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where('nominas.idCatTypePayroll','004')
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd', 'sdi', 'liquidationSalary', 'twentyDaysPerYearOfServices', 'seniorityPremium', 'exemptCompensation', 'taxedCompensation', 'holidays', 'exemptBonus', 'taxableBonus', 'holidayPremiumExempt', 'holidayPremiumTaxed', 'otherPerception', 'totalPerceptions', 'isr', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaVPCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Prima vacacional');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','PERCEPCIONES','','','','RETENCIONES','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 1)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 20)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 24)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 26)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 27)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Días trabajados','Días para vacaciones','Vacaciones','Prima vacacional exenta','Prima vacacional gravada','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 1)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 20)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 24)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 26)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 27)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						vacation_premia.sd,
						vacation_premia.sdi,
						vacation_premia.dateOfAdmission,
						vacation_premia.workedDays,
						vacation_premia.holidaysDays,
						vacation_premia.holidays,
						vacation_premia.exemptHolidayPremium,
						vacation_premia.holidayPremiumTaxed,
						vacation_premia.totalPerceptions,
						vacation_premia.isr,
						vacation_premia.totalTaxes,
						vacation_premia.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(vacation_premia.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->leftJoin('payment_methods','vacation_premia.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','vacation_premia.idvacationPremium','=','nomina_employee_accounts.idvacationPremium')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd', 'sdi', 'holidays', 'exemptHolidayPremium', 'holidayPremiumTaxed', 'totalPerceptions', 'isr', 'totalTaxes', 'netIncome', 'pagado', 'por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaPSCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Reparto de utilidades');
				$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','INFORMACIÓN PERSONAL','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','PERCEPCIONES','','','RETENCIONES','','NETO','PAGOS',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 1)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 22)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 25)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 27)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 28)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				$headerArr = ['Folio','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Días trabajados','Sueldo total','PTU por días','PTU por sueldos','PTU total','PTU exenta','PTU gravada','Total','Retención de ISR','Total','Sueldo neto','Pagado','Por pagar'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 1)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 8)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 15)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 22)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 25)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 27)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 28)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						profit_sharings.sd,
						profit_sharings.sdi,
						profit_sharings.workedDays,
						profit_sharings.totalSalary,
						profit_sharings.ptuForDays,
						profit_sharings.ptuForSalary,
						profit_sharings.totalPtu,
						profit_sharings.exemptPtu,
						profit_sharings.taxedPtu,
						profit_sharings.totalPerceptions,
						profit_sharings.isrRetentions,
						profit_sharings.totalRetentions,
						profit_sharings.netIncome,
						IFNULL(payment.amount,0) as payment,
						ROUND(profit_sharings.netIncome - IFNULL(payment.amount,0),2) as por_pagar,
						real_employees.id as employee_id
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->leftJoin('payment_methods','profit_sharings.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','profit_sharings.idprofitSharing','=','nomina_employee_accounts.idprofitSharing')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if($k != "employee_id")
						{
							if(in_array($k,['sd', 'sdi', 'totalSalary', 'ptuForDays', 'ptuForSalary', 'totalPtu', 'exemptPtu', 'taxedPtu', 'totalPerceptions', 'isrRetentions', 'totalRetentions', 'netIncome', 'payment', 'por_pagar']))
							{
								if($r != '')
								{
									$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
					}
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					if(isset($totalEmployees[$dtw->employee_id]))
					{
						$totalEmployees[$dtw->employee_id]['total'] += $dtw->totalPerceptions;
					}
					else
					{
						$totalEmployees[$dtw->employee_id]['last_name']      = $dtw->last_name;
						$totalEmployees[$dtw->employee_id]['scnd_last_name'] = $dtw->scnd_last_name;
						$totalEmployees[$dtw->employee_id]['name']           = $dtw->name;
						$totalEmployees[$dtw->employee_id]['total']          = $dtw->totalPerceptions;
						$totalEmployees[$dtw->employee_id]['patronal']       = 0;
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			$writer->addNewSheetAndMakeItCurrent();
			$sheet = $writer->getCurrentSheet();
			$sheet->setName('Totales');
			$mainHeaderArr = ['EMPLEADO','','','TOTAL','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 2)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr = ['Apellido Paterno','Apellido Materno','Nombre','Percepciones','Cuotas patronales','Total'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 2)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			foreach($totalEmployees as $dtw)
			{
				$tmpArr = [];
				foreach($dtw as $k => $r)
				{
					if($k == "total" || $k == "patronal")
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				$tmpArr[] = WriterEntityFactory::createCell((double)round($dtw['total'] + $dtw['patronal'],2), $currencyFormat);
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
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaEmployeeTable(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$data              = App\Module::find($this->module_id);
			$title             = $request->title;
			$enterprise        = $request->enterprise;
			$type              = $request->type;
			$fiscal            = $request->fiscal;
			$status            = $request->status;
			$employee          = $request->employee;
			$folio             = $request->folio;
			$wbs               = $request->wbs;
			$subdepartment     = $request->subdepartment;
			$mindate           = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate           = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project           = $request->project;
			$nominaSalaryCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaBonusCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaLiquidationCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaVPCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaPSCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominasNFCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			if($nominaSalaryCount == 0 && $nominaBonusCount == 0 && $nominaLiquidationCount == 0 && $nominaVPCount == 0 && $nominaPSCount == 0 && $nominasNFCount == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return redirect()->route('report.nomina-employee')->with('alert',$alert);
			}
			$totalArray = [];
			$monthsYearArray = [];
			if($nominasNFCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						IF(
							nominas.idCatTypePayroll = "001",
							DATE_FORMAT(
								DATE_ADD(
									nominas.from_date,
									INTERVAL(
										4 - 
										IF(
											DAYOFWEEK(nominas.from_date) = 1,
											8,
											DAYOFWEEK(nominas.from_date)
										)
									) DAY
								),
								"%M"
							),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%M"),
								DATE_FORMAT(nominas.datetitle,"%M")
							)
						) as month,
						IF(
							nominas.idCatTypePayroll = "001",
							DATE_FORMAT(
								DATE_ADD(
									nominas.from_date,
									INTERVAL(
										4 - 
										IF(
											DAYOFWEEK(nominas.from_date) = 1,
											8,
											DAYOFWEEK(nominas.from_date)
										)
									) DAY
								),
								"%m"
							),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%m"),
								DATE_FORMAT(nominas.datetitle,"%m")
							)
						) as monthNumber,
						IF(
							nominas.idCatTypePayroll = "001",
							DATE_FORMAT(
								DATE_ADD(
									nominas.from_date,
									INTERVAL(
										4 - 
										IF(
											DAYOFWEEK(nominas.from_date) = 1,
											8,
											DAYOFWEEK(nominas.from_date)
										)
									) DAY
								),
								"%Y"
							),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%Y"),
								DATE_FORMAT(nominas.datetitle,"%Y")
							)
						) as year,
						"No fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						ROUND(nomina_employee_n_fs.amount + IF(discount_nomina.amount IS NULL,IF(nomina_employee_n_fs.discount IS NULL,0,nomina_employee_n_fs.discount),discount_nomina.amount),2) as per,
						nomina_employee_n_fs.amount as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			unset($dataToWrite);
			if($nominaSalaryCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(
							DATE_ADD(
								nominas.from_date,
								INTERVAL(
									4 - 
									IF(
										DAYOFWEEK(nominas.from_date) = 1,
										8,
										DAYOFWEEK(nominas.from_date)
									)
								) DAY
							),
							"%M"
						) as month,
						DATE_FORMAT(
							DATE_ADD(
								nominas.from_date,
								INTERVAL(
									4 - 
									IF(
										DAYOFWEEK(nominas.from_date) = 1,
										8,
										DAYOFWEEK(nominas.from_date)
									)
								) DAY
							),
							"%m"
						) as monthNumber,
						DATE_FORMAT(
							DATE_ADD(
								nominas.from_date,
								INTERVAL(
									4 - 
									IF(
										DAYOFWEEK(nominas.from_date) = 1,
										8,
										DAYOFWEEK(nominas.from_date)
									)
								) DAY
							),
							"%Y"
						) as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						salaries.totalPerceptions as per,
						salaries.netIncome as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			unset($dataToWrite);
			if($nominaBonusCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%m") as monthNumber,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						bonuses.totalPerceptions as per,
						bonuses.netIncome as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			if($nominaLiquidationCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.down_date,"%M") as month,
						DATE_FORMAT(nominas.down_date,"%m") as monthNumber,
						DATE_FORMAT(nominas.down_date,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						liquidations.totalPerceptions as per,
						liquidations.netIncome as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			unset($dataToWrite);
			if($nominaVPCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%m") as monthNumber,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						vacation_premia.totalPerceptions as per,
						vacation_premia.netIncome as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			unset($dataToWrite);
			if($nominaPSCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%m") as monthNumber,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						profit_sharings.totalPerceptions as per,
						profit_sharings.netIncome as neto
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach($dataToWrite as $d)
				{
					$dateKey = (int) ($d->year.$d->monthNumber);
					$totalArray[$d->subdepartments]['title'] = ($d->subdepartments == "" ? "Sin subdepartamento" : $d->subdepartments);
					$monthsYearArray[$dateKey]['title'] = strtoupper($d->month).' '.$d->year;
					if (isset($totalArray[$d->subdepartments][$dateKey]['per']))
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $totalArray[$d->subdepartments][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray[$d->subdepartments][$dateKey]['neto']))
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $totalArray[$d->subdepartments][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['neto']))
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $totalArray[$d->subdepartments]['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['neto'] = $d->neto;
					}
					if (isset($totalArray[$d->subdepartments]['total']['per']))
					{
						$totalArray[$d->subdepartments]['total']['per'] = $totalArray[$d->subdepartments]['total']['per'] + $d->per;
					}
					else
					{
						$totalArray[$d->subdepartments]['total']['per'] = $d->per;
					}
					$totalArray['__total']['title'] = 'Total';
					if (isset($totalArray['__total'][$dateKey]['neto']))
					{
						$totalArray['__total'][$dateKey]['neto'] = $totalArray['__total'][$dateKey]['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total'][$dateKey]['neto'] = $d->neto;
					}
					if (isset($totalArray['__total'][$dateKey]['per']))
					{
						$totalArray['__total'][$dateKey]['per'] = $totalArray['__total'][$dateKey]['per'] + $d->per;
					}
					else
					{
						$totalArray['__total'][$dateKey]['per'] = $d->per;
					}
					if (isset($totalArray['__total']['total']['neto']))
					{
						$totalArray['__total']['total']['neto'] = $totalArray['__total']['total']['neto'] + $d->neto;
					}
					else
					{
						$totalArray['__total']['total']['neto'] = $d->neto;
					}
					if (isset($totalArray['__total']['total']['per']))
					{
						$totalArray['__total']['total']['per'] = $totalArray['__total']['total']['per'] + $d->per;
					}
					else
					{
						$totalArray['__total']['total']['per'] = $d->per;
					}
				}
			}
			unset($dataToWrite);
			ksort($monthsYearArray);
			ksort($totalArray);
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$percentFormat  = (new StyleBuilder())->setFormat('0.00%')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('tabla-nómina.xlsx');
			$sheet    = $writer->getCurrentSheet();
			$sheet->setName('Tabla Nómina');
			$tmpMHArr   = [];
			$tmpMHArr[] = WriterEntityFactory::createCell("Periodo",$mhStyleCol1);
			$alternateColor = true;
			foreach($totalArray as $k => $mh)
			{
				if($alternateColor)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh['title'],$mhStyleCol2);
					$tmpMHArr[] = WriterEntityFactory::createCell("",$mhStyleCol2);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh['title'],$mhStyleCol3);
					$tmpMHArr[] = WriterEntityFactory::createCell("",$mhStyleCol3);
				}
				$alternateColor = !$alternateColor;
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$tmpMHArr   = [];
			$tmpMHArr[] = WriterEntityFactory::createCell("",$smStyleCol1);
			$alternateColor = true;
			foreach($totalArray as $k => $mh)
			{
				if($alternateColor)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell("SUELDO",$smStyleCol2);
					$tmpMHArr[] = WriterEntityFactory::createCell("SUELDO NETO",$smStyleCol2);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell("SUELDO",$smStyleCol3);
					$tmpMHArr[] = WriterEntityFactory::createCell("SUELDO NETO",$smStyleCol3);
				}
				$alternateColor = !$alternateColor;
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$alternateColor = true;
			foreach ($monthsYearArray as $key => $value)
			{
				$tmpMHArr   = [];
				$tmpMHArr[] = WriterEntityFactory::createCell($value['title']);
				foreach($totalArray as $k => $mh)
				{
					if(isset($mh[$key]))
					{
						$tmpMHArr[] = WriterEntityFactory::createCell((double)$mh[$key]['per'],$currencyFormat);
						$tmpMHArr[] = WriterEntityFactory::createCell((double)$mh[$key]['neto'],$currencyFormat);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
						$tmpMHArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
				}
				if($alternateColor)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpMHArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				}
				$writer->addRow($rowFromValues);
				$alternateColor = !$alternateColor;
			}
			$tmpMHArr   = [];
			$tmpMHArr[] = WriterEntityFactory::createCell("Total ($)");
			foreach($totalArray as $k => $mh)
			{
				$tmpMHArr[] = WriterEntityFactory::createCell((double)$mh['total']['per'],$currencyFormat);
				$tmpMHArr[] = WriterEntityFactory::createCell((double)$mh['total']['neto'],$currencyFormat);
			}
			if($alternateColor)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			}
			$writer->addRow($rowFromValues);
			$alternateColor = !$alternateColor;
			$tmpMHArr   = [];
			$tmpMHArr[] = WriterEntityFactory::createCell("Total (%)");
			foreach($totalArray as $k => $mh)
			{
				$tmpMHArr[] = WriterEntityFactory::createCell(round($mh['total']['per'] / $totalArray['__total']['total']['per'],4),$percentFormat);
				$tmpMHArr[] = WriterEntityFactory::createCell(round($mh['total']['neto'] / $totalArray['__total']['total']['neto'],4),$percentFormat);
			}
			if($alternateColor)
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr,$rowDark);
			}
			else
			{
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			}
			$writer->addRow($rowFromValues);
			$alternateColor = !$alternateColor;
			return $writer->close();
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaEmployeeTableComplete(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$data              = App\Module::find($this->module_id);
			$title             = $request->title;
			$enterprise        = $request->enterprise;
			$type              = $request->type;
			$fiscal            = $request->fiscal;
			$status            = $request->status;
			$employee          = $request->employee;
			$folio             = $request->folio;
			$wbs               = $request->wbs;
			$subdepartment     = $request->subdepartment;
			$mindate           = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate           = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project           = $request->project;
			$nominaSalaryCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaBonusCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaLiquidationCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaVPCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominaPSCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			$nominasNFCount = App\NominaEmployee::selectRaw('COUNT(nominas.idFolio) as num')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
				{
					if ($title != "") 
					{
						$query->where('nominas.title','LIKE','%'.$title.'%');
					}
					if ($enterprise != "") 
					{
						$query->whereHas('workerData',function($q) use($enterprise)
						{
							$q->whereIn('enterprise',$enterprise);
						});
					}
					if ($project != "") 
					{
						$query->whereHas('workerData',function($q) use($project)
						{
							$q->whereIn('project',$project);
						});
					}
					if ($type != "") 
					{
						$query->whereIn('nominas.idCatTypePayroll',$type);
					}
					if ($fiscal != "") 
					{
						$query->whereIn('request_models.taxPayment',$fiscal);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($employee != "") 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
							->whereBetween('nominas.to_date',[$mindate,$maxdate]);
					}
					if ($wbs != "") 
					{
						$query->whereHas('workerData',function($q) use($wbs)
						{
							$q->whereHas('employeeHasWbs',function($q) use($wbs)
							{
								$q->whereIn('cat_code_w_bs.id',$wbs);
							});
						});
					}
					if ($subdepartment != "")
					{
						$query->whereHas('workerData',function($q) use($subdepartment)
						{
							$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
							{
								$q->whereIn('subdepartments.id',$subdepartment);
							});
						});
					}
				})
				->first()
				->num;
			if($nominaSalaryCount == 0 && $nominaBonusCount == 0 && $nominaLiquidationCount == 0 && $nominaVPCount == 0 && $nominaPSCount == 0 && $nominasNFCount == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return redirect()->route('report.nomina-employee')->with('alert',$alert);
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('tabla-nómina-desglosada.xlsx');
			$sheet    = $writer->getCurrentSheet();
			$sheet->setName('Tabla Nómina');
			$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','','','','INFORMACIÓN PERSONAL','','','','','','','NETO','','CUOTAS PATRONALES','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 5)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
				elseif($k <= 14)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['Folio','Título','Mes','Año','Fiscal/No fiscal/Nom035','Tipo','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Total percepciones','Neto','IMSS Mensual','RCV Bimestral','INFONAVIT Bimestral','Total'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 5)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
				}
				elseif($k <= 14)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			if($nominasNFCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						IF(
							nominas.idCatTypePayroll = "001",
							DATE_FORMAT(
								DATE_ADD(
									nominas.from_date,
									INTERVAL(
										4 - 
										IF(
											DAYOFWEEK(nominas.from_date) = 1,
											8,
											DAYOFWEEK(nominas.from_date)
										)
									) DAY
								),
								"%M"
							),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%M"),
								DATE_FORMAT(nominas.datetitle,"%M")
							)
						) as month,
						IF(
							nominas.idCatTypePayroll = "001",
							DATE_FORMAT(
								DATE_ADD(
									nominas.from_date,
									INTERVAL(
										4 - 
										IF(
											DAYOFWEEK(nominas.from_date) = 1,
											8,
											DAYOFWEEK(nominas.from_date)
										)
									) DAY
								),
								"%Y"
							),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%Y"),
								DATE_FORMAT(nominas.datetitle,"%Y")
							)
						) as year,
						IF(nominas.type_nomina = 2,"No fiscal", IF(nominas.type_nomina = 3,"Nom035","")) as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						ROUND(nomina_employee_n_fs.amount + IF(discount_nomina.amount IS NULL,IF(nomina_employee_n_fs.discount IS NULL,0,nomina_employee_n_fs.discount),discount_nomina.amount),2) as per,
						nomina_employee_n_fs.amount as neto,
						"" as imss_by_month,
						"" as rcv_bimonth,
						"" as infonavit_month,
						"" as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per','neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaSalaryCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(
							DATE_ADD(
								nominas.from_date,
								INTERVAL(
									4 - 
									IF(
										DAYOFWEEK(nominas.from_date) = 1,
										8,
										DAYOFWEEK(nominas.from_date)
									)
								) DAY
							),
							"%M"
						) as month,
						DATE_FORMAT(
							DATE_ADD(
								nominas.from_date,
								INTERVAL(
									4 - 
									IF(
										DAYOFWEEK(nominas.from_date) = 1,
										8,
										DAYOFWEEK(nominas.from_date)
									)
								) DAY
							),
							"%Y"
						) as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						salaries.totalPerceptions as per,
						salaries.netIncome as neto,
						ROUND(
							ROUND((salaries.uma * .204) * salaries.daysForImss,2)
							+
							IF(
								(salaries.uma * 3) > salaries.sdi,
								0,
								ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)
							)
							+
							ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2),
							2
						) as imss_by_month,
						ROUND(
							ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2),
							2
						) as rcv_bimonth,
						ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2) as infonavit_month,
						ROUND(
							ROUND((salaries.uma * .204) * salaries.daysForImss,2)
							+
							IF(
								(salaries.uma * 3) > salaries.sdi,
								0,
								ROUND(((salaries.sdi - (salaries.uma * 3)) * 0.011) * salaries.daysForImss,2)
							)
							+
							ROUND((salaries.sdi * 0.007) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0105) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * (salaries.risk_number / 100)) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0175) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.01) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.02) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.0315) * salaries.daysForImss,2)
							+
							ROUND((salaries.sdi * 0.05) * salaries.daysForImss,2),
							2
						) as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[5,10,11,12])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per', 'neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaBonusCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						bonuses.totalPerceptions as per,
						bonuses.netIncome as neto,
						"" as imss_by_month,
						"" as rcv_bimonth,
						"" as infonavit_month,
						"" as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per', 'neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaLiquidationCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.down_date,"%M") as month,
						DATE_FORMAT(nominas.down_date,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						liquidations.totalPerceptions as per,
						liquidations.netIncome as neto,
						"" as imss_by_month,
						"" as rcv_bimonth,
						"" as infonavit_month,
						"" as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per', 'neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaVPCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						vacation_premia.totalPerceptions as per,
						vacation_premia.netIncome as neto,
						"" as imss_by_month,
						"" as rcv_bimonth,
						"" as infonavit_month,
						"" as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per', 'neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
						$rowFromValues = WriterEntityFactory::createRow($tmpArr);
					}
					$writer->addRow($rowFromValues);
					$kindRow = !$kindRow;
				}
			}
			if($nominaPSCount > 0)
			{
				$dataToWrite = App\NominaEmployee::selectRaw('
						nominas.idFolio as folio,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						DATE_FORMAT(nominas.datetitle,"%M") as month,
						DATE_FORMAT(nominas.datetitle,"%Y") as year,
						"Fiscal" as f_nf,
						cat_type_payrolls.description as typeNomina,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						profit_sharings.totalPerceptions as per,
						profit_sharings.netIncome as neto,
						"" as imss_by_month,
						"" as rcv_bimonth,
						"" as infonavit_month,
						"" as total_employer_contribution
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['per', 'neto','imss_by_month','rcv_bimonth','infonavit_month','total_employer_contribution']))
						{
							if($r != '')
							{
								$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
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
			return redirect('/');
		}
	}

	public function nominaReceipts($case, Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$right = true;
			switch ($case)
			{
				case 1:
					$proyectsId = [126];
					$enterprisesId = [5];
					$minDate = "2021-01-01";
					$maxDate = "2022-12-31";
					$employeeId = [];
					$baseDir = 'recibos_dos_bocas';
					break;
				case 2:
					$proyectsId = [37];
					$enterprisesId = [1,2,3,4,6,7,8,9,10,11,12];
					$minDate = "2021-01-01";
					$maxDate = "2022-12-31";
					$employeeId = [];
					$baseDir = 'recibos_administrativo_central';
					break;
				case 3:
					$proyectsId = [37];
					$enterprisesId = [5];
					$minDate = "2021-01-01";
					$maxDate = "2022-12-31";
					$employeeId = [];
					$baseDir = 'recibos_administrativo_central_proyecta';
					break;
				case 4:
					$proyectsId = [];
					$enterprisesId = [];
					$minDate = "2021-01-01";
					$maxDate = "2022-12-31";
					$employeeId = [1037];
					$baseDir = 'recibos_empleados';
					break;
				default:
					$right = false;
					break;
			}
			if($right)
			{
				$nf = App\NominaEmployee::selectRaw('
						IF(
							nominas.idCatTypePayroll = "001",
							IF(
								nominas.idCatPeriodicity = "05",
								UPPER(DATE_FORMAT(nominas.from_date,"%b - %Y")),
								IF(
									nominas.idCatPeriodicity = "04",
									IF(
										nominas.to_date <= DATE_FORMAT(nominas.to_date,"%Y-%m-15"),
										UPPER(CONCAT("1q ",DATE_FORMAT(nominas.from_date,"%b - %Y"))),
										UPPER(CONCAT("2q ",DATE_FORMAT(nominas.from_date,"%b - %Y")))
									),
									UPPER(CONCAT("sem ",DATE_FORMAT(nominas.from_date,"%u")," - ",DATE_FORMAT(nominas.from_date,"%Y")))
								)
							),
							IF(
								nominas.idCatTypePayroll = "002",
								CONCAT("1Q DIC - ",YEAR(nominas.datetitle)),
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									DATE_FORMAT(nominas.down_date,"%M - %Y"),
									DATE_FORMAT(nominas.datetitle,"%M - %Y")
								)
							)
						) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						payroll_receipts.path as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('payroll_receipts','nomina_employee_n_fs.idnominaemployeenf','payroll_receipts.idnominaemployeenf')
					->whereIn('request_models.status',[5,10,11,12,18])
					->where('request_models.kind',16)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($nf as $key => $non)
				{
					if($non->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$non->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.$non->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$non->payment_doc));
							} catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$non->payment_doc);
						}
					}
					if($non->receipt != '')
					{
						if(\Storage::disk('reserved')->exists($non->receipt))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.str_replace("/receipts/","",$non->receipt), \Storage::disk('reserved')->readStream($non->receipt));
							} catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$non->receipt));
						}
					}
				}
				$salary = App\NominaEmployee::selectRaw('
						IF(
							nomina_employees.idCatPeriodicity = "05",
							UPPER(DATE_FORMAT(nomina_employees.from_date,"%b - %Y")),
							IF(
								nomina_employees.idCatPeriodicity = "04",
								IF(
									nomina_employees.to_date <= DATE_FORMAT(nomina_employees.to_date,"%Y-%m-15"),
									UPPER(CONCAT("1q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y"))),
									UPPER(CONCAT("2q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y")))
								),
								UPPER(CONCAT("sem ",DATE_FORMAT(nomina_employees.from_date,"%u")," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))
							)
						) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($salary as $key => $sue)
				{
					if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
						}
					}
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
							}catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
						}
					}
				}
				$bonus = App\NominaEmployee::selectRaw('
						CONCAT("1Q DIC - ",YEAR(nominas.datetitle)) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($bonus as $key => $sue)
				{
					if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
						}
					}
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.xml'));
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
							}catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.pdf'));
						}
					}
				}
				$liquidation = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($liquidation as $key => $sue)
				{
					if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
						}
					}
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.xml'));
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
							}catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.pdf'));
						}
					}
				}
				$vacation_premia = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($vacation_premia as $key => $sue)
				{
					if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
						}
					}
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.xml'));
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
							}catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.pdf'));
						}
					}
				}
				$profit_sharings = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
					{
						if(count($proyectsId) > 0)
						{
							$q->whereIn('worker_datas.project',$proyectsId);
						}
						if(count($enterprisesId) > 0)
						{
							$q->whereIn('worker_datas.enterprise',$enterprisesId);
						}
						if(count($employeeId) > 0)
						{
							$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
						}
						if($minDate != "" && $maxDate != "")
						{
							$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
								->whereBetween('nominas.to_date',[$minDate,$maxDate]);
						}
					})
					->get();
				foreach ($profit_sharings as $key => $sue)
				{
					if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
						}
					}
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
							}
							catch (\Throwable $th){}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.xml'));
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
							}catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$sue->receipt.'.pdf'));
						}
					}
				}
				return "Done";
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

	public function nominaEmployeeZipReceipts(Request $request)
	{
		if (Auth::user()->module->where('id',190)->count()>0)
		{
			$data          = App\Module::find($this->module_id);
			$title         = $request->title;
			$enterprise    = $request->enterprise;
			$type          = $request->type;
			$fiscal        = $request->fiscal;
			$status        = $request->status;
			$employee      = $request->employee;
			$folio         = $request->folio;
			$wbs           = $request->wbs;
			$subdepartment = $request->subdepartment;
			$mindate       = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00') : null;
			$maxdate       = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59') : null;
			$project       = $request->project;
			$idFile        = Str::uuid();
			$zip_file      = '/tmp/payments_'.$idFile.'.zip';
			$zip           = new \ZipArchive();
			if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
			{
				$zip->addEmptyDir('comprobantes');
				$nf = App\NominaEmployee::selectRaw('
						IF(
							nominas.idCatTypePayroll = "001",
							IF(
								nominas.idCatPeriodicity = "05",
								UPPER(DATE_FORMAT(nominas.from_date,"%b - %Y")),
								IF(
									nominas.idCatPeriodicity = "04",
									IF(
										nominas.to_date <= DATE_FORMAT(nominas.to_date,"%Y-%m-15"),
										UPPER(CONCAT("1q ",DATE_FORMAT(nominas.from_date,"%b - %Y"))),
										UPPER(CONCAT("2q ",DATE_FORMAT(nominas.from_date,"%b - %Y")))
									),
									UPPER(CONCAT("sem ",DATE_FORMAT(nominas.from_date,"%u")," - ",DATE_FORMAT(nominas.from_date,"%Y")))
								)
							),
							IF(
								nominas.idCatTypePayroll = "002",
								CONCAT("1Q DIC - ",YEAR(nominas.datetitle)),
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									DATE_FORMAT(nominas.down_date,"%M - %Y"),
									DATE_FORMAT(nominas.datetitle,"%M - %Y")
								)
							)
						) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						payroll_receipts.path as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('payroll_receipts','nomina_employee_n_fs.idnominaemployeenf','payroll_receipts.idnominaemployeenf')
					->whereIn('request_models.status',[5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',0)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($nf as $key => $non)
				{
					/* if($non->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$non->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$non->payment_doc), '/comprobantes/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.$non->payment_doc);
							} catch (\Throwable $th) {}
						}
					} */
					if($non->receipt != '')
					{
						if(\Storage::disk('reserved')->exists($non->receipt))
						{
							try {
								$zip->addFile(storage_path($non->receipt), '/comprobantes/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.str_replace("/receipts/","",$non->receipt));
							} catch (\Throwable $th) {}
						}
					}
				}
				$salary = App\NominaEmployee::selectRaw('
						IF(
							nomina_employees.idCatPeriodicity = "05",
							UPPER(DATE_FORMAT(nomina_employees.from_date,"%b - %Y")),
							IF(
								nomina_employees.idCatPeriodicity = "04",
								IF(
									nomina_employees.to_date <= DATE_FORMAT(nomina_employees.to_date,"%Y-%m-15"),
									UPPER(CONCAT("1q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y"))),
									UPPER(CONCAT("2q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y")))
								),
								UPPER(CONCAT("sem ",DATE_FORMAT(nomina_employees.from_date,"%u")," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))
							)
						) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($salary as $key => $sue)
				{
					/* if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$sue->payment_doc), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc);
							}
							catch (\Throwable $th){}
						}
					} */
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.xml'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml');
							}
							catch (\Throwable $th){}
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.pdf'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf');
							}catch (\Throwable $th) {}
						}
					}
				}
				$bonus = App\NominaEmployee::selectRaw('
						CONCAT("1Q DIC - ",YEAR(nominas.datetitle)) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($bonus as $key => $sue)
				{
					/* if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$sue->payment_doc), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc);
							}
							catch (\Throwable $th){}
						}
					} */
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.xml'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml');
							}
							catch (\Throwable $th){}
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.pdf'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf');
							}catch (\Throwable $th) {}
						}
					}
				}
				$liquidation = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($liquidation as $key => $sue)
				{
					/* if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$sue->payment_doc), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc);
							}
							catch (\Throwable $th){}
						}
					} */
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.xml'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml');
							}
							catch (\Throwable $th){}
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.pdf'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf');
							}catch (\Throwable $th) {}
						}
					}
				}
				$vacation_premia = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($vacation_premia as $key => $sue)
				{
					/* if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$sue->payment_doc), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc);
							}
							catch (\Throwable $th){}
						}
					} */
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.xml'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml');
							}
							catch (\Throwable $th){}
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.pdf'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf');
							}catch (\Throwable $th) {}
						}
					}
				}
				$profit_sharings = App\NominaEmployee::selectRaw('
						UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						documents_payments.path as "payment_doc",
						bills.uuid as "receipt"
					')
					->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
					->join('request_models','nominas.idFolio','=','request_models.folio')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
					->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
					->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
					->leftJoin('bills','employee_bill.idBill','bills.idBill')
					->whereIn('request_models.status',[4,5,10,11,12,18])
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function($query) use ($title,$enterprise,$type,$fiscal,$status,$employee,$folio,$mindate,$maxdate,$project,$wbs,$subdepartment)
					{
						if ($title != "") 
						{
							$query->where('nominas.title','LIKE','%'.$title.'%');
						}
						if ($enterprise != "") 
						{
							$query->whereHas('workerData',function($q) use($enterprise)
							{
								$q->whereIn('enterprise',$enterprise);
							});
						}
						if ($project != "") 
						{
							$query->whereHas('workerData',function($q) use($project)
							{
								$q->whereIn('project',$project);
							});
						}
						if ($type != "") 
						{
							$query->whereIn('nominas.idCatTypePayroll',$type);
						}
						if ($fiscal != "") 
						{
							$query->whereIn('request_models.taxPayment',$fiscal);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						if ($employee != "") 
						{
							$query->whereIn('nomina_employees.idrealEmployee',$employee);
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if ($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('nominas.from_date',[$mindate,$maxdate])
								->whereBetween('nominas.to_date',[$mindate,$maxdate]);
						}
						if ($wbs != "") 
						{
							$query->whereHas('workerData',function($q) use($wbs)
							{
								$q->whereHas('employeeHasWbs',function($q) use($wbs)
								{
									$q->whereIn('cat_code_w_bs.id',$wbs);
								});
							});
						}
						if ($subdepartment != "")
						{
							$query->whereHas('workerData',function($q) use($subdepartment)
							{
								$q->whereHas('employeeHasSubdepartment',function($q) use($subdepartment)
								{
									$q->whereIn('subdepartments.id',$subdepartment);
								});
							});
						}
					})
					->get();
				foreach ($profit_sharings as $key => $sue)
				{
					/* if($sue->payment_doc != '')
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
						{
							try {
								$zip->addFile(public_path('/docs/payments/'.$sue->payment_doc), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc);
							}
							catch (\Throwable $th){}
						}
					} */
					if($sue->receipt != '')
					{
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.xml'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml');
							}
							catch (\Throwable $th){}
						}
						if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
						{
							try {
								$zip->addFile(storage_path('stamped/'.$sue->receipt.'.pdf'), '/comprobantes/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf');
							}catch (\Throwable $th) {}
						}
					}
				}
				$zip->close();
				return response()->download($zip_file);
			}
		}
		else
		{
			return redirect('/');
		}
	}
}
