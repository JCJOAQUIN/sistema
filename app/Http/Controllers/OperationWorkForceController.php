<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Module;
use App\CatCodeWBS;
use Excel;
use App\WorkForce;
use App\Project;
use DateTime;
use Carbon\Carbon;
use Lang;

class OperationWorkForceController extends Controller
{
	protected $module_id = 153;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{   
			$data = Module::find($this->module_id);
			return view('layouts.child_module',
			[
				'id'		=> $data['father'],
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
		if (Auth::user()->module->where('id',179)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('operacion.fuerza_trabajo.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 179
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',179)->count()>0) 
		{
			if (isset($request->work_force_id) && count($request->work_force_id)>0) 
			{
				for ($i=0; $i < count($request->work_force_id); $i++) 
				{
					$work_force						= new WorkForce();
					$work_force->project_id			= $request->project_id[$i];
					$work_force->wbs_id				= $request->wbs_id[$i] == "undefined" ? null : $request->wbs_id[$i];
					$work_force->location			= $request->location_wbs[$i];
					$work_force->description		= $request->description[$i];
					$work_force->provider			= $request->provider[$i];
					$work_force->work_force			= $request->work_force[$i];
					$work_force->total_workers		= $request->total_workers[$i];
					$work_force->man_hours_per_day	= $request->man_hours_per_day[$i];
					$work_force->date 				= $request->date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->date[$i])->format('Y-m-d') : null;
					$work_force->user_id 			= Auth::user()->id;
					$work_force->save();
				}
				$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
				return redirect('operation/work-force')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function follow(Request $request)
	{
		if (Auth::user()->module->where('id',295)->count()>0) 
		{
			$data 			= Module::find($this->module_id);
			$min_date		= $request->min_date !='' ? Carbon::createFromFormat('d-m-Y',$request->min_date) : null;
			$max_date		= $request->max_date !='' ? Carbon::createFromFormat('d-m-Y',$request->max_date) : null;
			
			$work_forces 	= WorkForce::whereIn('project_id',Auth::user()->inChargeProject(295)->pluck('project_id'))
							->where(function($query) use($request,$min_date,$max_date)
							{
								if($request->project_id != "")
								{
									$query->where('project_id',$request->project_id);
								}

								if($request->wbs_id != "")
								{
									$query->whereIn('work_forces.wbs_id',$request->wbs_id);
								}
								
								if($request->location_wbs != "")
								{
									$query->where('location','LIKE','%'.$request->location_wbs.'%');
								}

								if($request->description != "")
								{
									$query->where('description','LIKE','%'.$request->description.'%');
								}

								if($min_date != "" && $max_date != "")
								{
									$query->whereBetween('date',[$min_date->format('Y-m-d 00:00:00'), $max_date->format('Y-m-d 23:59:59')]);
								}
							})
							->orderBy('id','DESC')
							->paginate(10);

			return response(
				view('operacion.fuerza_trabajo.seguimiento',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 295,
					'project_id'	=> $request->project_id,
					'wbs_id'		=> $request->wbs_id,
					'location_wbs'	=> $request->location_wbs,
					'description'	=> $request->description,
					'min_date'		=> $request->min_date,
					'max_date'		=> $request->max_date,
					'work_forces'	=> $work_forces
				])
			)
			->cookie('urlSearch',storeUrlCookie(295), 2880);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function edit(WorkForce $work_force)
	{
		if (Auth::user()->module->where('id',295)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('operacion.fuerza_trabajo.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 295,
				'work_force'	=> $work_force
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function update(WorkForce $work_force, Request $request)
	{
		if (Auth::user()->module->where('id',295)->count()>0) 
		{
			$work_force->project_id			= $request->project_id;
			$work_force->wbs_id				= $request->wbs_id;
			$work_force->location			= $request->location_wbs;
			$work_force->description		= $request->description;
			$work_force->provider			= $request->provider;
			$work_force->work_force			= $request->work_force;
			$work_force->total_workers		= $request->total_workers;
			$work_force->man_hours_per_day	= $request->man_hours_per_day;
			$work_force->date 				= $request->date != '' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$work_force->user_id 			= Auth::user()->id;
			$work_force->save();

			$alert	= "swal('','".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',295)->count()>0) 
		{
			$min_date		= $request->min_date !='' ? Carbon::createFromFormat('d-m-Y',$request->min_date) : null;
			$max_date		= $request->max_date !='' ? Carbon::createFromFormat('d-m-Y',$request->max_date) : null;
			
			$work_forces 	= WorkForce::selectRaw('
								projects.proyectName,
								cat_code_w_bs.code_wbs,
								work_forces.location,
								work_forces.date,
								work_forces.description,
								work_forces.provider,
								work_forces.work_force,
								work_forces.total_workers,
								work_forces.man_hours_per_day
							')
							->leftJoin('projects','projects.idproyect','work_forces.project_id')
							->leftJoin('cat_code_w_bs','cat_code_w_bs.id','work_forces.wbs_id')
							->whereIn('work_forces.project_id',Auth::user()->inChargeProject(295)->pluck('project_id'))
							->where(function($query) use($request,$min_date,$max_date)
							{
								if($request->project_id != "")
								{
									$query->where('work_forces.project_id',$request->project_id);
								}

								if($request->wbs_id != "")
								{
									$query->whereIn('work_forces.wbs_id',$request->wbs_id);
								}

								if($request->location_wbs != "")
								{
									$query->where('location','LIKE','%'.$request->location_wbs.'%');
								}

								if($request->description != "")
								{
									$query->where('work_forces.description','LIKE','%'.$request->description.'%');
								}

								if($min_date != "" && $max_date != "")
								{
									$query->whereBetween('work_forces.date',[$min_date->format('Y-m-d 00:00:00'), $max_date->format('Y-m-d 23:59:59')]);
								}
							})
							->get();

			Excel::create('Fuerza de Trabajo', function($excel) use ($work_forces)
			{
				if (count($work_forces)>0) 
				{
					$excel->sheet('Fuerza de Trabajo',function($sheet) use ($work_forces)
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

						$sheet->mergeCells('A1:I1');

						$sheet->cell('A1:I1', function($cells)
						{
							$cells->setBackground('#2e9546');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A2:I2', function($cells)
						{
							$cells->setBackground('#11aa35');
							$cells->setFontColor('#ffffff');
						});
						
						$sheet->cell('A1:I2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});
						$sheet->row(1,['Información General','','','','','','','']);
						$sheet->row(2,[
							'Proyecto',
							'WBS',
							'Localización',
							'Fecha',
							'Descripción de Actividad',
							'Contratista/Subcontratista',
							'Fuerza de Trabajo',
							'Total de Trabajadores',
							'Horas Hombre por Día',
						]);

						$beginMerge = 2;
						foreach ($work_forces as $work_force) 
						{
							$sheet->appendRow($work_force->toArray());
						}
					});
				}
			})->export('xlsx');
		}
		else
		{
			return redirect('error');
		}
	}

	public function massiveCreate()
	{
		if (Auth::user()->module->where('id',302)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('operacion.fuerza_trabajo.alta_masiva',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 302
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id',302)->count()>0)
		{	
			if($request->file('csv_file') == "")
			{
				$alert	= "swal('','".Lang::get("messages.file_null")."', 'error');";
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
				$handle		= fopen($request->file('csv_file'), "r");
				$firstLine	= fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count) 
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				
				if($separator == $request->separator)
				{
					$name		= '/massive_work_force/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
						$alert	= "swal('','".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);
					}
					array_shift($csvArr);
					$headers = [
						'proyecto',
						'wbs',
						'localizacion',
						'descripcion_de_actividad',
						'contratista_subcontratista',
						'fuerza_de_trabajo',
						'total_de_trabajadores',
						'horas_hombre_por_dia',
						'fecha'
					];
					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}
					$data = Module::find($this->module_id);
					return view('operacion.fuerza_trabajo.verificar_masivo',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 302,
						'csv'		=> $csvArr,
						'fileName'	=> $name,
						'delimiter'	=> $request->separator
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
		if(Auth::user()->module->where('id',302)->count()>0)
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
			$errors = "";
			foreach ($csvArr as $key => $wf)
			{
				try
				{
					$projectWBSFlag = false;
                    if(isset($wf['proyecto']) && !empty(trim($wf['proyecto'])))
                    {
                        if(Project::find($wf['proyecto']) != '')
                        {
                            $checkProject = Project::find($wf['proyecto'])->idproyect;
                            if(Project::find($wf['proyecto'])->codeWBS()->exists())
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

					if(isset($wf['wbs']) && $wf['wbs'] != "")
                    {
                        if(CatCodeWBS::where('project_id',$wf['proyecto'])->where('id', $wf['wbs'])->first() != '')
                        {
                            $checkwbs = CatCodeWBS::where('project_id',$wf['proyecto'])->where('id', $wf['wbs'])->first()->id;
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
					$totalHours		= $wf['total_de_trabajadores'] * 8;
					$originalDate 	= $wf['fecha'];
					$date			= new \DateTime($originalDate);
					$newDate 		= $date->format('Y-m-d');
					if($checkProject != "" && (($checkwbs != "" && $projectWBSFlag) || ($checkwbs == "" && !$projectWBSFlag)) && $totalHours == $wf['horas_hombre_por_dia'])
					{
						if(!empty(trim($wf['total_de_trabajadores'])) && !empty(trim($wf['horas_hombre_por_dia'])) && !empty(trim($wf['localizacion'])) && !empty(trim($newDate)) && !empty(trim($wf['descripcion_de_actividad'])))
						{
							$work_force 					= new WorkForce();
							$work_force->project_id			= $wf['proyecto'];
							$work_force->wbs_id				= !empty(trim($wf['wbs'])) ? $wf['wbs'] : null;
							$work_force->location			= $wf['localizacion'];
							$work_force->description		= $wf['descripcion_de_actividad'];
							$work_force->provider			= $wf['contratista_subcontratista'];
							$work_force->work_force			= $wf['fuerza_de_trabajo'];
							$work_force->total_workers		= $wf['total_de_trabajadores'];
							$work_force->man_hours_per_day	= $wf['horas_hombre_por_dia'];
							$work_force->date 				= $newDate;
							$work_force->user_id 			= Auth::user()->id;					
							$work_force->save();
						}
						else
                        {
                            $errors .= $key+1;
                        }
					}
					else
					{
						$errors .= $key+1 .",";
					}
				}
				catch (\Exception $e)
				{	
				}
			}
			if($errors != "")
            {
				$message	= "La fila ".$errors." no se registro, por favor verifique la información."; 
                $alert		= "swal('', '".$message."', 'error');";
            }
            else
            {
                $alert = "swal('','Los datos han sido cargados correctamente','success');";
            }
			return redirect()->route('work-force.follow')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',302)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('work-force.massive');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->where('id',302)->count()>0)
		{
			Excel::create('Catalogos-Fuerza-de-Trabajo', function($excel)
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
					foreach(Project::selectRaw('idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')->where('status',1)->where('idproyect',Auth::user()->inChargeProject(302)->pluck('project_id'))->get() as $project)
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
					foreach(CatCodeWBS::selectRaw('id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')->join('projects','cat_code_w_bs.project_id','projects.idproyect')->where('cat_code_w_bs.status',1)->where('projects.status',1)->where('cat_code_w_bs.project_id',Auth::user()->inChargeProject(302)->pluck('project_id'))->get() as $wbs)
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
}
