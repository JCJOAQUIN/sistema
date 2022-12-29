<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Lang;
use App\CatCodeWBS;
use App\Project;
use App\Activities;
use Carbon\Carbon;
use Excel;
use DateTime;
use Alert;
use Cron\HoursField;
use Illuminate\Support\Facades\App as FacadesApp;
use Illuminate\Support\Facades\DB;

class OperationActivitiesProgramationController extends Controller
{
	private $module_id =99;


	public function index()
	{
		if(Auth::user()->module->where('id', $this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
			[
				'id'        =>  $data['father'],
				'title'     =>  $data['name'],
				'details'   =>  $data['details'],
				'child_id'  =>  $this->module_id
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',116)->count()>0)
		{
			$data           = App\Module::find($this->module_id);

			return view('operacion.programacion_actividades.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 116
			]);
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
			$data               = App\Module::find($this->module_id);

		   $count    = count($request->tproject);

		   for($i=0; $i < $count; $i++)
		   {
				$t_activity = new App\Activities();
				$t_activity->project_id         = $request->tproject[$i];
				$t_activity->wbs_id             = $request->tcode_wbs[$i];
				$t_activity->folio              = $request->tfolio[$i];
				$t_activity->contractor         = $request->tcontractor[$i];
				$t_activity->specialty          = $request->tspecialty[$i];
				$t_activity->start_date         = $request->tstart_date[$i] !="" ? Carbon::createFromFormat('d-m-Y',$request->tstart_date[$i])->format('Y-m-d') : null;
				$t_activity->start_hour         = $request->tschedule_start[$i];
				$t_activity->end_date           = $request->tend_date[$i] !="" ? Carbon::createFromFormat('d-m-Y',$request->tend_date[$i])->format('Y-m-d') : null;
				$t_activity->end_hour           = $request->tschedule_end[$i];
				$t_activity->area               = $request->tarea[$i];
				$t_activity->personal_number    = $request->tnumber[$i];
				$t_activity->status_code        = $request->tstatus[$i];
				$t_activity->description        = $request->tdescription[$i];
				$t_activity->user_id            = Auth::user()->id;
				
				$t_activity->save();

				$count_act		= $i+1;
				$tresource	= 'tresource_'.$count_act;
				$tcauses	= 'tcauses_'.$count_act;

				if (isset($request->$tresource) && count($request->$tresource)>0) 
				{
					for ($r=0; $r < count($request->$tresource); $r++) 
					{ 
						$resource					= new App\ActivityHasResource();
						$resource->resource_code	= $request->$tresource[$r];
						$resource->activity_id		= $t_activity->id;
						$resource->save();
					}
				}

				if (isset($request->$tcauses) && count($request->$tcauses)>0) 
				{
					for ($c=0; $c < count($request->$tcauses); $c++) 
					{ 
						$cause					= new App\ActivityHasCause();
						$cause->causes_code		= $request->$tcauses[$c];
						$cause->activity_id		= $t_activity->id;
						$cause->save();
					}
				}
		    }
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return back()->with('alert', $alert);
		}
		else
		{           
			return redirect('/');
		}
	}

	public function follow(Request $request)
	{
		
		if(Auth::user()->module->where('id',148)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$description = $request->description;
			$project_id  = $request->project_id;
			$code_wbs    = $request->code_wbs;
			$start_date  = $request->start_date!='' ? date('Y-m-d',strtotime($request->start_date)) : null;
			$end_date    = $request->end_date!='' ? date('Y-m-d',strtotime($request->end_date)) : null;
			$area        = $request->area;
			$folio       = $request->folio;

			if (($start_date == "" && $end_date != "") || ($start_date != "" && $end_date == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}
			
			$activities = App\Activities::whereIn('project_id', Auth::user()->inChargeProject(148)->pluck('project_id'))
			->where(function ($query) use ($description,$project_id,$code_wbs,$folio,$start_date,$end_date,$area)
			{
				if($description != "")
				{
					$query->where('activities.description','LIKE','%'.$description.'%');
				}
				if($project_id != "")
				{
					$query->where('activities.project_id',$project_id);
				}
				if($code_wbs != "")
				{
					$query->where('activities.wbs_id',$code_wbs);
				}
				if($folio != "")
				{
					$query->where('activities.folio',$folio);
				}
				if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('activities.start_date',[''.$start_date.' '.date('00:00:00').'',''.$end_date.' '.date('00:00:00').'']);
				}if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('activities.end_date',[''.$start_date.' '.date('00:00:00').'',''.$end_date.' '.date('00:00:00').'']);
				}
				if($area != "")
				{
					$query->where('activities.area','LIKE','%'.$area.'%');
				}
			})    
			->orderBy('id', 'DESC')
			->paginate(10);
			
			return view('operacion.programacion_actividades.seguimiento',
			[
				'id'            => $data['father'],
				'title'         => $data['name'],
				'details'       => $data['details'],
				'child_id'      => $this->module_id,
				'option_id'     => 148,
				'activities'    => $activities,
				'project_id'    => $project_id,
				'code_wbs'      => $code_wbs,
				'folio'         => $folio,
				'description'   => $description,
				'start_date'    => $start_date,
				'end_date'      => $end_date,
				'area'          => $area,                
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',148)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$activity   = App\Activities::find($id);
			if($activity != "")
			{
				return view('operacion.programacion_actividades.alta',
				[
					'id'        =>  $data['father'],
					'title'     =>  $data['name'],
					'details'   =>  $data['details'],
					'child_id'  =>  $this->module_id,
					'option_id' => 148,
					'activity'  =>  $activity
				]);
			}
			else
			{
				return redirect('/error');
			}
		}
	}

	public function update(Request $request, Activities $activity)
	{
		if(Auth::user()->module->where('id', 148)->count()>0)
		{  
			$start_date				=	$request->mindate !="" ? Carbon::createFromFormat('d-m-Y H:i',$request->mindate)->format('Y-m-d') : null;
			$staff_schedule_start	=	$request->mindate !="" ? Carbon::createFromFormat('d-m-Y H:i',$request->mindate)->format('H:i:00') : null;
			$end_date				=	$request->maxdate !="" ? Carbon::createFromFormat('d-m-Y H:i',$request->maxdate)->format('Y-m-d') : null;
			$staff_schedule_end		=	$request->maxdate !="" ? Carbon::createFromFormat('d-m-Y H:i',$request->maxdate)->format('H:i:00') : null;
			if($start_date > $end_date)
			{
				$alert = "swal('','La fecha inicial no puede ser mayor a la fecha final','error')";
			}
			elseif($staff_schedule_start > $staff_schedule_end)
			{
				$alert = "swal('','La hora inicial no puede ser mayor a la hora final','error')";
			}
			else
			{
				$activity->project_id      = $request->project_id;
				$activity->wbs_id          = $request->code_wbs;
				$activity->folio           = $request->folio;
				$activity->contractor      = $request->contractor;
				$activity->specialty       = $request->specialty;
				$activity->start_date      = $start_date;
				$activity->start_hour      = $staff_schedule_start;
				$activity->end_date        = $end_date;
				$activity->end_hour        = $staff_schedule_end;
				$activity->area            = $request->area;
				$activity->personal_number = $request->number;
				$activity->status_code     = $request->status;
				$activity->description     = $request->description;
				$activity->user_id         = Auth::user()->id;
				
				$activity->save();

				if (App\ActivityHasCause::where('activity_id',$activity->id)->count()>0)
				{
					App\ActivityHasCause::where('activity_id',$activity->id)->delete();
				}

				if (App\ActivityHasResource::where('activity_id',$activity->id)->count()>0)
				{
					App\ActivityHasResource::where('activity_id',$activity->id)->delete();
				}

				if (isset($request->resource) && count($request->resource)>0) 
				{
					for ($r=0; $r < count($request->resource); $r++) 
					{ 
						$resource					= new App\ActivityHasResource();
						$resource->resource_code	= $request->resource[$r];
						$resource->activity_id		= $activity->id;
						$resource->save();
					}
				}

				if (isset($request->causes_non_compliance) && count($request->causes_non_compliance)>0) 
				{
					for ($r=0; $r < count($request->causes_non_compliance); $r++) 
					{ 
						$cause				= new App\ActivityHasCause();
						$cause->causes_code	= $request->causes_non_compliance[$r];
						$cause->activity_id	= $activity->id;
						$cause->save();
					}
				}
				$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
				
			}
			return back()->with('alert', $alert);        
		}
		else
		{
			return redirect('/');
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',148)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$description = $request->description;
			$project_id  = $request->project_id;
			$code_wbs    = $request->code_wbs;
			$start_date  = $request->start_date!='' ? date('Y-m-d',strtotime($request->start_date)) : null;
			$end_date    = $request->end_date!='' ? date('Y-m-d',strtotime($request->end_date)) : null;
			$area        = $request->area;
			$folio       = $request->folio; 

			if (($start_date == "" && $end_date != "") || ($start_date != "" && $end_date == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}
			
			
			$activities = Activities::selectRaw(
			'
				projects.proyectName,
				IF(cat_code_w_bs.code_wbs != "" ,cat_code_w_bs.code_wbs,"Sin código") as code_wbs,
				activities.folio,
				activities.description,
				activities.contractor,
				activities.specialty,
				activities.start_date,
				activities.start_hour,
				activities.end_date,
				activities.end_hour,
				activities.area,
				activities.personal_number,
				resources.resource_code,
				activities.status_code,
				causes.causes_code
			')
			->leftJoin('projects','projects.idproyect', '=', 'activities.project_id')
			->leftJoin('cat_code_w_bs','cat_code_w_bs.id', '=', 'activities.wbs_id')
			->leftJoin(DB::raw('(SELECT activity_id, GROUP_CONCAT(activity_has_resources.resource_code SEPARATOR ", ") as resource_code FROM activity_has_resources INNER JOIN activities ON activity_has_resources.activity_id = activities.id GROUP BY activity_id) as resources'),'activities.id','resources.activity_id')
			->leftJoin(DB::raw('(SELECT activity_id, GROUP_CONCAT(activity_has_causes.causes_code SEPARATOR ", ") as causes_code FROM activity_has_causes INNER JOIN activities ON activity_has_causes.activity_id = activities.id GROUP BY activity_id) as causes'),'activities.id','causes.activity_id')
			->whereIn('activities.project_id', Auth::user()->inChargeProject(148)->pluck('project_id'))
			->where(function ($query) use ($description,$project_id,$code_wbs,$folio,$start_date,$end_date,$area)
			{
				if($description != "")
				{
					$query->where('activities.description','LIKE','%'.$description.'%');
					
				}
				if($project_id != "")
				{
					$query->where('activities.project_id',$project_id);
				}
				if($code_wbs != "")
				{
					$query->where('activities.wbs_id',$code_wbs);
				}
				if($folio != "")
				{
					$query->where('activities.folio',$folio);
				}
				if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('activities.start_date',[''.$start_date.' '.date('00:00:00').'',''.$end_date.' '.date('00:00:00').'']);
				}if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('activities.end_date',[''.$start_date.' '.date('00:00:00').'',''.$end_date.' '.date('00:00:00').'']);
				}
				if($area != "")
				{
					
					$query->where('activities.area',$area);
				}
			})
			->get();

			Excel::create('Reporte-de-actividades', function($excel) use ($activities)
			{
				$excel->sheet('Reporte',function($sheet) use($activities)
				{
					$sheet->setStyle([
						'font' => [
							'name'	=> 'Calibri',
							'size'	=> 12
						],
						'alignment' => [
							'vertical' => 'center',
						]
					]);
					$sheet->setColumnFormat(array(
						'G' => 'yyyy-mm-dd',
						'I' => 'yyyy-mm-dd',
						'H' => 'HH:ii',
						'J' => 'HH:ii',
					));
					$sheet->mergeCells('A1:O1');
					$sheet->mergeCells('A2:O2');
					$sheet->mergeCells('A3:O3');
					$sheet->cell('A1:O1', function($cells)
					{
						$cells->setBackground('#B00000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:O2', function($cells)
					{
						$cells->setBackground('#ffffff');
						$cells->setFontColor('#000000');
					});
					$sheet->cell('A3:O3', function($cells)
					{
						$cells->setBackground('#ffffff');
						$cells->setFontColor('#000000');
					});
					$sheet->cell('A4:O4', function($cells)
					{
						$cells->setBackground('#B00000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:O1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '14','bold' => true));
					});
					$sheet->cell('A2:O3', function($cells)
					{
						$cells->setFontWeight('sans');
						$cells->setAlignment('left');
						$cells->setFont(array('family' => 'Calibri','size' => '11', 'sans' => true));
					});
					$sheet->cell('A4:O4', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});

					$sheet->row(1,['REPORTE DE PROGRAMACION DE ACTIVIDADES']);
					$sheet->row(2,['Causas de Incumplimiento: CAEX.- Causas Externas,   FADS.- Falta Análisis de Seguridad,   MAF.- Falta de Mecanismos Auxiliares de Fabricación,  FDMA.- Falta de Material,   FDPE.- Falta de Personal,   TPSC.- Trabajos Previos Sin Concluir, ENPL.- Error En Planeación, TPUR.- Trabajos por Urgencias,   PE.- Por Emergencia']);
					$sheet->row(3,['Recursos: A.-Andamio, GT.-Grua o Titan, , CV.-Camión de Volteo , MS.-Maquina de Soldar, BA.-Bomba de Achique, GN.- Generador, CR.-Camioneta de Redilas, RT.-Retroexcavadora,  COA.- Cilindtros de Oxiacetileno, CA.- Cilindros de Argon, O.-Otro especificar, NA.- No Aplica   --/--   Estatus: I.-Inició, C.- Continua, NI.-No Inició, T.-Termino']);
					$sheet->row(4,['Proyecto','Código WBS','Folio permiso de trabajo','Descripción de las actividades en el Proyecto(Área)','Contratista','Especialidad','Fecha programada de inicio','Hora programada de inicio','Fecha programada de finalización','Hora programada de finalización','Área/Ubicación','No. de personal','Recursos','Estatus','Causas de Incumplimiento']);    
					foreach($activities as $activity)
					{
						$sheet->appendRow($activity->toArray());
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function massive()
	{
		if(Auth::user()->module->where('id',301)->count()>0)
		{
			$data= App\Module::find($this->module_id);
			return view('operacion.programacion_actividades.masivo',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 301,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id', 301)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert = "swal('','".Lang::get("messages.file_null")."', 'error')";
				return back()->with('alert',$alert);
			}

			$valid = $request->file('csv_file')->getClientOriginalExtension();
							
			if($valid != 'csv')
			{
				$alert = "swal('','".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error')";
				return back()->with('alert',$alert);	
			}

			if($request->file('csv_file')->isValid())
			{
				$delimiters = [";" => 0, "," => 0];

				$handle = fopen($request->file('csv_file'),"r");
				$firstLine = fgets($handle);
				fclose($handle);

				foreach($delimiters as $delimiter => &$count)
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}

					$separator = array_search(max($delimiters), $delimiters);

					if($separator == $request->separator)
					{
						$name		= '/massive_activity/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
						try
						{
							array_walk($csvArr, function(&$a) use ($csvArr)
							{
								$a = array_combine($csvArr[0], $a);
							});
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'error')";
							return back()->with('alert',$alert);
						}
						array_shift($csvArr);
						$data = App\Module::find($this->module_id);
						return view('operacion.programacion_actividades.verificar_masivo',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 301,
								'csv'		=> $csvArr,
								'fileName'	=> $name,
								'delimiter'	=> $request->separator
							]);
					}
					else
					{
						$alert = "swal('','".Lang::get("messages.separator_error")."', 'error')";
						return back()->with('alert',$alert);
					}
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'error')";
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
		if(Auth::user()->module->where('id',301)->count()>0)
		{
			$path = \Storage::disk('reserved')->path($request->fileName);
			$csvArr = array();
			if(($handle = fopen($path, "r")) !== FALSE)
			{
				$first = true;
				while (($data = fgetcsv($handle, 1000, $request->delimiter)) !== FALSE)
				{
					if($first)
					{
						$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
						$first = false;
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
			$errors = "";
			foreach($csvArr as $key => $e)
			{
				try
				{
					$projectWBSFlag = false;
					if(isset($e['proyecto']) && !empty(trim($e['proyecto'])))
					{
						if(Project::find($e['proyecto']) != '')
						{
							$checkProject = Project::find($e['proyecto'])->idproyect;
							if(Project::find($e['proyecto'])->codeWBS()->exists())
							{
								$projectWBSFlag = true;
							}
						}
						else
						{
							$checkProject = "";
						}
					}
					else
					{
						$checkProject = "";
					}

					if(isset($e['codigo_wbs']) && $e['codigo_wbs'] != "")
					{
						if(CatCodeWBS::where('project_id',$e['proyecto'])->where('id', $e['codigo_wbs'])->first() != '')
						{
							$checkwbs = CatCodeWBS::where('project_id',$e['proyecto'])->where('id', $e['codigo_wbs'])->first()->id;
						}
						else
						{
							$projectWBSFlag = true;
							$checkwbs = "";
						}
					}
					else
					{
						$checkwbs = "";
					}
					$s_date = $e["fecha_inicio"] != "" ? Carbon::createFromFormat('d/m/Y', $e["fecha_inicio"])->format('Y-m-d') : "";
					$e_date = $e["fecha_finalizacion"] != "" ? Carbon::createFromFormat('d/m/Y', $e["fecha_finalizacion"])->format('Y-m-d') : "";
					$s_hour = Carbon::createFromFormat('H:s', $e["hora_inicio"])->format('H:s');
					$e_hour = Carbon::createFromFormat('H:s', $e["hora_finalizacion"])->format('H:s');
					$dateFlag = false;
					$hourFlag = false;
					if ($s_date > $e_date)
					{
						$dateFlag = true;
					}
					if($s_hour > $e_hour)
					{
						$hourFlag = true;
					}
					if($checkProject != "" && (($checkwbs != "" && $projectWBSFlag) || ($checkwbs == "" && !$projectWBSFlag)) && $s_date != "" && $e_date != "" && !$dateFlag && !$hourFlag) 
					{
						if(!empty(trim($e['folio_permiso_de_trabajo'])) &&
						!empty(trim($e['contratista'])) &&
						!empty(trim($e['especialidad'])) &&
						!empty(trim($s_date)) &&
						!empty(trim($e['hora_inicio'])) &&
						!empty(trim($e_date)) &&
						!empty(trim($e['hora_finalizacion'])) &&
						!empty(trim($e['area_ubicacion'])) &&
						!empty(trim($e['num_personal'])) &&
						!empty(trim($e['recursos'])) &&
						!empty(trim($e['estatus'])) &&
						!empty(trim($e['causas_incumplimiento'])) &&
						!empty(trim($e['descripcion'])))
						{
							$activity   = new Activities();

							$activity->project_id       = $e['proyecto'];
							$activity->wbs_id           = !empty(trim($e['codigo_wbs'])) ? $e['codigo_wbs'] : null;
							$activity->folio            = $e['folio_permiso_de_trabajo'];
							$activity->contractor       = $e['contratista'];
							$activity->specialty        = $e['especialidad'];
							$activity->start_date       = $s_date;
							$activity->start_hour       = $e['hora_inicio'];
							$activity->end_date         = $e_date;
							$activity->end_hour         = $e['hora_finalizacion'];
							$activity->area             = $e['area_ubicacion'];
							$activity->personal_number  = $e['num_personal'];
							$activity->resource_code    = $e['recursos'];
							$activity->status_code      = $e['estatus'];
							$activity->causes_code      = $e['causas_incumplimiento'];
							$activity->description      = $e['descripcion'];
							$activity->user_id          = Auth::user()->id;
							$activity->save();
						}
						else
						{
							$errors .= $key+2;
						}
					}
					else
					{
						$errors .= $key+2 .",";
					}       
				}
				catch(\Exception $e)
				{
				//    return $e;					
				}
			}
			if($errors != "")
			{
				$message = "Las filas ".$errors." no fueron registrada";
				$alert	= "swal('', '".$message."', 'error');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			}
			
			return redirect()->route('activitiesprogramation.follow')->with('alert', $alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',301)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('activitiesprogramation.massive');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[148,301])->count()>0)
		{
			Excel::create('Catalogos-Programación de Actividades', function($excel)
			{
				$excel->sheet('Proyectos',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->cell('A1:B1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,[
						'ID',
						'Proyecto'
					]);
					foreach(Project::selectRaw(
						'idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')
						->whereIn('idproyect',Auth::user()
						->inChargeProject(148)
						->pluck('project_id'))
						->where('status',1)
						->get() as $project)
					{
						$sheet->appendRow($project->toArray());
					}
				});
				$excel->sheet('WBS',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->cell('A1:C1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,[
						'ID',
						'WBS',
						'Proyecto'
					]);
					foreach(CatCodeWBS::selectRaw(
						'id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')
						->join('projects','cat_code_w_bs.project_id','projects.idproyect')
						->where('cat_code_w_bs.status',1)
						->where('projects.status',1)
						->whereIn('projects.idproyect',Auth::user()->inChargeProject(148)
						->pluck('project_id'))
						->get() as $wbs)
					{
						$sheet->appendRow($wbs->toArray());
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
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
}
