<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\ControlIncidents;
use App\Module;
use App\RealEmployee;
use App\Project;
use App\CatCodeWBS;
use Auth;
use DateTime;
use Excel;
use App\ControlIncidentDocument;
use Ilovepdf\CompressTask;
use Carbon\Carbon;
use Lang;

class OperationIncidentControlController extends Controller
{
	//
	private $module_id = 47;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
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
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',48)->count()>0)
		{
			
			$data 	= App\Module::find($this->module_id);
			return view('operacion.control_incidentes.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 48
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',48)->count()>0)
		{
			for($i=0; $i<count($request->project_id); $i++)
			{
				$incident                   = new App\ControlIncidents();
				$incident->description      = $request->description[$i];
				$incident->date_incident    = Carbon::createFromFormat('d-m-Y', $request->date_incident[$i])->format('Y-m-d');
				$incident->impact_level     = $request->impact_level[$i];
				$incident->causes           = $request->causes[$i];
				$incident->recommendation   = $request->recommendation[$i];
				$incident->status           = $request->status[$i];
				$incident->communique       = $request->communique[$i];
				$incident->project_id       = $request->project_id[$i];
				$incident->wbs_id           = $request->code_wbs[$i];
				$incident->location			= $request->location_wbs[$i];
				$incident->employee 		= $request->employee[$i];
				$incident->user_id          = Auth::user()->id;
				$incident->save();
				$incident->incident_number  = $incident->id;
				$incident->save();
				$paths = 't_path'.($i+1);
				if (isset($request->$paths) && count($request->$paths)>0) 
				{
					for ($d=0; $d < count($request->$paths); $d++) 
					{ 
						if ($request->$paths[$d] != "") 
						{
							$new_doc						= new ControlIncidentDocument();
							$new_doc->path					= $request->$paths[$d];
							$new_doc->control_incident_id	= $incident->id;
							$new_doc->user_id				= Auth::user()->id;
							$new_doc->save();
						}
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect()->route('incident-control.index')->with('alert',$alert);	
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',94)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$location_wbs	= $request->location_wbs;
			$description	= $request->description;
			$recommendation	= $request->recommendation;
			$impact_level	= $request->impact_level;
			$employee		= $request->employee;
			$mindate		= $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate		= $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;

			if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}

			$incidents = ControlIncidents::whereIn('project_id', Auth::user()->inChargeProject(94)->pluck('project_id'))
			->where(function($query) use ($request,$description,$location_wbs,$impact_level,$employee,$maxdate,$mindate)
			{
				if($request->project_id != "")
				{
					$query->where('project_id',$request->project_id);
				}

				if($request->wbs_id != "")
				{
					$query->whereIn('wbs_id',$request->wbs_id);
				}
				if($description != '')
				{
					$query->where('description','LIKE','%'.$description.'%');
				}
				if($location_wbs != '')
				{
					$query->where('location','LIKE','%'.$location_wbs.'%');
				}
				if($impact_level != '')
				{
					$query->where('impact_level','LIKE','%'.$impact_level.'%');
				}
				if($employee != '')
				{
					$query->where('employee','LIKE','%'.$employee.'%');
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('date_incident',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
				}

			})
			->paginate(10);
			return view('operacion.control_incidentes.seguimiento',
			[
				'id'               => $data['father'],
				'title'            => $data['name'],
				'details'          => $data['details'],
				'child_id'         => $this->module_id,
				'option_id'        => 94,
				'project_id'	   => $request->project_id,
				'wbs_id'		   => $request->wbs_id,
				'location_wbs'	   => $location_wbs,
				'description'      => $description,
				'recommendation'   => $recommendation,
				'impact_level'     => $impact_level,
				'employee'		   => $employee,
				'mindate'          => $request->mindate,
				'maxdate'          => $request->maxdate,
				'incidents'        => $incidents,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if (Auth::user()->module->where('id',94)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$incident=App\ControlIncidents::find($id);
			if($incident != "")
			{
				return view('operacion.control_incidentes.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 94,
					'incident' => $incident
				]);
			}
			else
			{
				return redirect('/error');
			}
		}
		
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',94)->count()>0)
		{
			$incident = ControlIncidents::find($id);
			if ($incident != "")
			{
				$incident->description      = $request->description;
				$incident->date_incident    = Carbon::createFromFormat('d-m-Y', $request->date_incident)->format('Y-m-d');
				$incident->impact_level     = $request->impact_level;
				$incident->causes           = $request->causes;
				$incident->recommendation   = $request->recommendation;
				$incident->status           = $request->status;
				$incident->communique       = $request->communique;
				$incident->project_id       = $request->project_id;
				$incident->wbs_id           = $request->code_wbs;
				$incident->location			= $request->location_wbs;
				$incident->employee 		= $request->employee;
				$incident->user_id          = Auth::user()->id;
				$incident->incident_number  = $incident->id;
				$incident->save();
				if(isset($request->docPathDeleted) && count($request->docPathDeleted)>0)
				{
					ControlIncidentDocument::whereIn('path',$request->docPathDeleted)->delete();
				}
				if (isset($request->incident_path) && count($request->incident_path)>0) 
				{
					for ($i=0; $i < count($request->incident_path); $i++) 
					{ 
						if ($request->incident_path[$i] != "") 
						{
							$new_doc						= new ControlIncidentDocument();
							$new_doc->path					= $request->incident_path[$i];
							$new_doc->control_incident_id	= $incident->id;
							$new_doc->user_id				= Auth::user()->id;
							$new_doc->save();
						}
					}
				}
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
			}
			return redirect()->route('incident-control.index')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massive()
	{
		if(Auth::user()->module->where('id',300)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('operacion.control_incidentes.masivo',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 300
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id',300)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert	= "swal('', '".Lang::get("messages.file_null")."', 'error');";
				return back()->with('alert',$alert);	
			}

			$valid = $request->file('csv_file')->getClientOriginalExtension();
							
			if($valid != 'csv')
			{
				$alert	= "swal('', '".Lang::get("messages.extension_allowed",["param" => 'CSV'])."', 'error');";
				return back()->with('alert',$alert);	
			}
		 
			if($request->file('csv_file')->isValid())
			{
				$delimiters = [";" => 0, "," => 0];
				$handle 	= fopen($request->file('csv_file'), "r");
				$firstLine 	= fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count) 
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				if($separator == $request->separator)
				{
					$name		= '/massive_incident/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
					$headers = 
					[
						'proyecto',	
						'codigo_wbs',
						'localizacion',
						'fecha_incidente',
						'trabajador',
						'nivel_impacto',
						'estatus',
						'descripcion',
						'causas',
						'recomendacion',
						'comunicado',
					];
					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}
					$data = Module::find($this->module_id);
					return view('operacion.control_incidentes.verificar_masivo',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id'	=> $this->module_id,
							'option_id'	=> 300,
							'csv'		=> $csvArr,
							'fileName'	=> $name,
							'delimiter'	=> $request->separator
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
		if(Auth::user()->module->where('id',300)->count()>0)
		{
	 		$path   = \Storage::disk('reserved')->path($request->fileName);
			$csvArr = array();
	 		if(($handle = fopen($path,"r")) !== FALSE)
	 		{
	 			$first = true;
	 			while (($data = fgetcsv($handle, 1000, $request->delimiter)) !== FALSE)
	 			{
	 				if($first)
	 				{
						$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/','', $data[0]);
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
			$errors			= "";
			$countRecords	= 0;
	 		foreach($csvArr as $key => $e)
	 		{
				
	 			try
	 			{
					
					$projectWBSFlag = false;
					$employeeFlag = false;
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
					
					$level = 1;

					if($e['nivel_impacto'] == 'Bajo')
					{
						$level = 1;
					}
					elseif($e['nivel_impacto'] == 'Moderado')
					{
						$level = 2;
					}
					elseif($e['nivel_impacto'] == 'Grave')
					{
						$level = 3;
					}

					$cadena = $e['estatus'];
					$status = 1;

					if(stristr($cadena, 'abierto'))
					{
						$status = 1;
					}
					elseif(stristr($cadena, 'falta'))
					{
						$status = 1;
					}
					elseif(stristr($cadena, 'cerrado'))
					{
						$status = 2;
					}

					/*
					if(isset($e['trabajador']) && !empty(trim($e['trabajador'])))
					{
						if(RealEmployee::find($e['trabajador']) != "")
						{
							if(RealEmployee::find($e['trabajador'])->workerDataVisible()->where('project', $e['proyecto'])->exists())
							{
								$checkEmployee = RealEmployee::find($e['trabajador'])->id;
								$employeeFlag = true;
							}
						}
						else
						{
							$checkEmployee = "";
						}
					}
					else
					{
						$checkEmployee = "";
					}
					*/
					
					$originalDate	= $e['fecha_incidente'];
					$newDate		=  Carbon::createFromFormat('Y-m-d', $originalDate)->format('Y-m-d');
					if($newDate != "" && $level != "" && $status != "")
					{
						
						if(!empty(trim($e['descripcion'])) && !empty(trim($e['fecha_incidente'])))
						{
							$incident                   = new ControlIncidents();
							$incident->description      = $e['descripcion'];
							$incident->date_incident    = $newDate;
							$incident->employee 		= $e['trabajador'];
							$incident->project_id       = $e['proyecto'];
							$incident->wbs_id           = !empty(trim($e['codigo_wbs'])) ? $e['codigo_wbs'] : null;
							$incident->location         = !empty(trim($e['localizacion'])) ? $e['localizacion'] : null;
							$incident->impact_level     = $level;
							$incident->causes           = $e['causas'];
							$incident->recommendation   = $e['recomendacion'];
							$incident->status           = $status;
							$incident->communique       = $e['comunicado'];
							$incident->user_id          = Auth::user()->id;
							$incident->save();
							$countRecords++;
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
					
	 			}
	 		}
			if($errors != "")
			{
				$message = "Las filas ".$errors." no fueron registrada";
				$alert	= "swal('', '".$message."', 'error');";
			}
			else
			{
				$alert = "swal('','Los datos han sido cargados correctamente','success');";
			}

			if ($countRecords == 0) 
			{
				$alert = "swal('','No se registró ningún incidente, por favor verifique los datos de su archivo.','error');";
			}
			
			return redirect()->route('incident-control.follow')->with('alert', $alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',300)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('incident-control.index');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[94,300])->count() > 0)
		{
			Excel::create('Catalogos-Control de Incidentes', function($excel)
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
					foreach(Project::selectRaw('idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')->whereIn('idproyect',Auth::user()->inChargeProject(94)->pluck('project_id'))->where('status',1)->get() as $project)
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
					foreach(CatCodeWBS::selectRaw('id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')->join('projects','cat_code_w_bs.project_id','projects.idproyect')->where('cat_code_w_bs.status',1)->where('projects.status',1)->whereIn('projects.idproyect',Auth::user()->inChargeProject(94)->pluck('project_id'))->get() as $wbs)
					{
						$sheet->appendRow($wbs->toArray());
					}
				});
				/*
				$excel->sheet('Trabajadores',function($sheet)
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
						'Nombre',
						'Proyecto'
					]);
					foreach(RealEmployee::selectRaw('real_employees.id, CONCAT(last_name," ",scnd_last_name," ",name) as name,
					projects.proyectName')
					->Join('worker_datas','worker_datas.idEmployee','=','real_employees.id')
					->leftJoin('projects','projects.idproyect', '=', 'worker_datas.project')
					->where('worker_datas.visible',1)
					->whereIn('worker_datas.project',Auth::user()->inChargeProject(94)->pluck('project_id'))
					->orderBy('worker_datas.project','asc')
					->orderBy('real_employees.last_name','asc')
					->orderBy('real_employees.scnd_last_name','asc')
					->orderBy('real_employees.name','asc')
					->get() as $employee)
					{
						$sheet->appendRow($employee->toArray());
					}
				});
				*/
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function getEmployee(Request $request)
	{
		if($request->ajax())
		{
			$employee = App\RealEmployee::whereHas('workerDataVisible', function($q) use($request)
			{
				$q->whereIn('project',Auth::user()->inChargeProject(94)->pluck('project_id'))
				->where('project',$request->idproject);
			})->get();
			return Response($employee);
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',94)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			
			$description	= $request->description;
			$location_wbs	= $request->location_wbs;
			$impact_level	= $request->impact_level;
			$employee		= $request->employee;
			$mindate		= $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate		= $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;

			if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}
			
			$incidents = ControlIncidents::selectRaw(
				'
				control_incidents.id,
				control_incidents.description,
				control_incidents.date_incident,
				control_incidents.employee,
				projects.proyectName,
				IF(cat_code_w_bs.code_wbs != "", cat_code_w_bs.code_wbs,"Sin código") as code_wbs,
				control_incidents.location,
				control_incidents.impact_level,
				control_incidents.causes,
				control_incidents.recommendation,
				IF(control_incidents.status = 1, "En proceso", "Finalizado") as status,
				control_incidents.communique
				'
			)
			->leftJoin('projects','projects.idproyect', '=', 'control_incidents.project_id')
			->leftJoin('cat_code_w_bs','cat_code_w_bs.id', '=', 'control_incidents.wbs_id')
			->whereIn('control_incidents.project_id', Auth::user()->inChargeProject(94)->pluck('project_id'))
			->where(function ($query) use ($request,$description,$location_wbs,$employee,$impact_level,$mindate,$maxdate)
			{
				
				if($request->project_id != "")
				{
					$query->where('control_incidents.project_id',$request->project_id);
				}

				if($request->wbs_id != "")
				{
					$query->whereIn('wbs_id',$request->wbs_id);
				}
				if($description != '')
				{
					$query->where('control_incidents.description',$description);
				}
				if($location_wbs != '')
				{
					$query->where('location','LIKE','%'.$location_wbs.'%');
				}
				if($impact_level != '')
				{
					$query->where('control_incidents.impact_level','LIKE','%'.$impact_level.'%');
				}
				if($employee != '')
				{
					$query->where('control_incidents.employee','LIKE','%'.$employee.'%');
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('control_incidents.date_incident', [$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
				}
			})
			->orderBy('id','ASC')
			->get();

			Excel::create('Control-de-Incidentes', function($excel) use ($incidents)
			{
				$excel->sheet('Bitácora',function($sheet) use ($incidents)
				{
					$sheet->setStyle([
						'font' => 
						[
							'name'	=> 'Calibri',
							'size'	=> 12
						],
						'alignment' => 
						[
							'vertical' => 'center',
						]
					]);
					$sheet->setColumnFormat(array(
						'C' => 'yyyy-mm-dd'
					));
					
					$sheet->mergeCells('C1:I1');
					$sheet->mergeCells('C2:I2');
	
					$sheet->cell('A3:L3', function($cells)
					{
						$cells->setBackground('#F32B2B');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:L3', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});

					$sheet->row(1,['','','PROYECTA INDUSTRIAL DE MÉXICO']);
					$sheet->row(2,['','','BITÁCORA DE CONTROL DE INCIDENTES']);
					$sheet->row(3,['Número','Descripción de Incidente','Fecha de Incidente','Trabajador','Proyecto','Frente de Trabajo (WBS)', 'Localización', 'Nivel de Impacto','Causas','Recomendación','Estatus','Comunicado']);

					foreach ($incidents as $incident)
					{
						$sheet->appendRow($incident->toArray());	
					}
				});
			})->export('xls');
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
					\Storage::disk('public')->delete('/docs/incident-control/'.$request->realPath[$i]);
				}	
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/incident-control/'.$name;
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

	public function delete($id)
	{
		if (Auth::user()->module->where('id',94)->count()>0) 
		{
			$incident = ControlIncidents::find($id);
			if ($incident != "")
			{
				$incident->documents()->delete();
				$incident->delete();
				$alert = "swal('', '".Lang::get("messages.record_deleted")."', 'success');";
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
			}
			return redirect()->route('incident-control.follow')->with('alert',$alert);
		}
	}
}
