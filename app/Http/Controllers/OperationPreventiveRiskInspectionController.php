<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\AdditionalDataPreventiveRiskInspection;
use App\Console\Commands\Update;
use App\PreventiveRiskInspection;
use App\PreventiveRiskInspectionDetail;
use Excel;
use PDF;
use DateTime;
use Auth;
use Carbon\Carbon;
use Lang;

class OperationPreventiveRiskInspectionController extends Controller
{
	private $module_id = 330;

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
		if(Auth::user()->module->where('id',331)->count()>0)
		{
			$data           = App\Module::find($this->module_id);

			return view('operacion.inspecciones_preventivas_riesgo.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 331
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store (Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);

			$t_preventive					= new App\PreventiveRiskInspection();
			$t_preventive->project_id		= $request->project_id;
			$t_preventive->wbs_id			= $request->code_wbs;
			$t_preventive->contractor_id	= $request->contractor;
			$t_preventive->area				= $request->area;
			$t_preventive->date				= Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
			$t_preventive->heading			= $request->heading;
			$t_preventive->supervisor_name	= $request->supervisor;
			$t_preventive->responsible_name	= $request->responsible;
			$t_preventive->observation		= $request->observation;
			$t_preventive->user_id			= Auth::user()->id;
			$t_preventive->save();

			$count = count($request->tcategory);

			for($i=0; $i < $count; $i++)
			{
				$id_preventive	= $t_preventive->id;

				$t_additional									= new PreventiveRiskInspectionDetail();
				$t_additional->preventive_risk_inspection_id	= $id_preventive;
				$t_additional->category_id						= $request->tcategory[$i];
				$t_additional->subcategory_id					= $request->tsubcategory[$i];
				$t_additional->act								= $request->tact[$i];
				$t_additional->severity							= $request->tseverity[$i];
				$t_additional->hour								= $request->ttime[$i];
				$t_additional->discipline						= $request->tdiscipline[$i];
				$t_additional->condition						= $request->tcondition[$i];
				$t_additional->action							= $request->taction[$i];
				$t_additional->observer							= $request->tobserver[$i];
				$t_additional->responsible						= $request->tresponsible2[$i];
				$t_additional->status							= $request->tstatus[$i];
				$t_additional->dateend							= (($request->tdateend[$i] != "") ? (Carbon::createFromFormat('d-m-Y', $request->tdateend[$i])->format('Y-m-d')) : null);
				$t_additional->save();
			}
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return back()->with('alert', $alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow(Request $request)
	{		
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			$data        = App\Module::find($this->module_id);

			$project_id		= $request->project_id;
			$code_wbs		= $request->code_wbs;
			$contractor		= $request->contractor;
			$area			= $request->area;
			$start_date		= (($request->start_date != '') ? Carbon::createFromFormat('d-m-Y', $request->start_date) : null);
			$end_date		= (($request->end_date != '') ? Carbon::createFromFormat('d-m-Y', $request->end_date) : null);	

			if (($start_date == "" && $end_date != "") || ($start_date != "" && $end_date == "")) 
			{
				$alert = "swal('','Por favor ingrese un rango de fechas.','error');";
				return back()->with('alert',$alert);
			}

			$preventives = App\PreventiveRiskInspection::whereIn('project_id', Auth::user()->inChargeProject(333)->pluck('project_id'))
			->where(function($query) use ($project_id,$code_wbs,$contractor,$area,$start_date,$end_date)
			{
				if($project_id != "")
				{
					$query->where('preventive_risk_inspection.project_id',$project_id);
				}
				if($code_wbs != "")
				{
					$query->where('preventive_risk_inspection.wbs_id',$code_wbs);
				}
				if($contractor != "")
				{
					$query->where('preventive_risk_inspection.contractor_id',$contractor);
				}
				if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('preventive_risk_inspection.date',[$start_date->format('Y-m-d 00:00:00'), $end_date->format('Y-m-d 23:59:59')]);
				}
				if($area != "")
				{
					$query->where('preventive_risk_inspection.area','LIKE','%'.$area.'%');
				}

			})
			->orderBy('id', 'DESC')
			->paginate(10);

			return view('operacion.inspecciones_preventivas_riesgo.seguimiento',
			[
				'id'            => $data['father'],
				'title'         => $data['name'],
				'details'       => $data['details'],
				'child_id'      => $this->module_id,
				'option_id'     => 333,
				'preventives'	=> $preventives,
				'project_id'    => $project_id,
				'code_wbs'      => $code_wbs,
				'contractor'	=> $contractor,
				'area'			=> $area,
				'start_date'    => $request->start_date,
				'end_date'      => $request->end_date,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			
			$preventive   = App\PreventiveRiskInspection::find($id);
			
			if($preventive != "")
			{
				return view('operacion.inspecciones_preventivas_riesgo.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 333,
					'preventive'	=> $preventive,
				]);
			}
			else
			{
				return redirect('error');
			}
		}
	}

	public function update(Request $request, $id)
	{		
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			if($request->edit_data == "x")
			{
				$preventive = PreventiveRiskInspection::find($id);
				$preventive->project_id         = $request->project_id;
				$preventive->wbs_id             = $request->code_wbs;
				$preventive->contractor_id      = $request->contractor;
				$preventive->area		        = $request->area;
				$preventive->date               = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
				$preventive->heading		    = $request->heading;
				$preventive->supervisor_name    = $request->supervisor;
				$preventive->responsible_name   = $request->responsible;
				$preventive->observation       	= $request->observation;
				$preventive->user_id            = Auth::user()->id;
				$preventive->save();
			}
			if(isset($request->to_delete))
			{
				for($i=0; $i < count($request->to_delete); $i++)
				{
					PreventiveRiskInspectionDetail::find($request->to_delete[$i])->delete();
				}
			}
			$count = count($request->tcategory);

			for($i=0; $i < $count; $i++)
			{

				if($request->id_preventive[$i] == "x" || $request->id_preventive[$i] == null)
				{
					$t_additional					= new PreventiveRiskInspectionDetail();
					$t_additional->preventive_risk_inspection_id	= $id;
				}
				else
				{
					$t_additional	= PreventiveRiskInspectionDetail::find($request->id_preventive[$i]);
				}
				
				$t_additional->category_id		= $request->tcategory[$i];
				$t_additional->subcategory_id	= $request->tsubcategory[$i];
				$t_additional->act				= $request->tact[$i];
				$t_additional->severity			= $request->tseverity[$i];
				$t_additional->hour				= $request->ttime[$i];
				$t_additional->discipline		= $request->tdiscipline[$i];
				$t_additional->condition		= $request->tcondition[$i];
				$t_additional->action			= $request->taction[$i];
				$t_additional->observer			= $request->tobserver[$i];
				$t_additional->responsible		= $request->tresponsible2[$i];
				$t_additional->status			= $request->tstatus[$i];
				$t_additional->dateend			= (($request->tdateend[$i] != "") ? (Carbon::createFromFormat('d-m-Y', $request->tdateend[$i])->format('Y-m-d')) : null);
				$t_additional->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
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
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$project_id		= $request->project_id;
			$code_wbs		= $request->code_wbs;
			$contractor		= $request->contractor;
			$area			= $request->area;
			$start_date		= (($request->start_date != '') ? Carbon::createFromFormat('d-m-Y', $request->start_date) : null);
			$end_date		= (($request->end_date != '') ? Carbon::createFromFormat('d-m-Y', $request->end_date) : null);
			if (($start_date == "" && $end_date != "") || ($start_date != "" && $end_date == "")) 
			{
				$alert = "swal('','Por favor ingrese un rango de fechas.','error');";
				return back()->with('alert',$alert);
			}
			$preventives = PreventiveRiskInspection::selectRaw(
			'
				projects.proyectName,
				IF(cat_code_w_bs.code_wbs != "" ,cat_code_w_bs.code_wbs,"Sin código") as code_wbs,
				IF(contractors.name != "" ,contractors.name,"Sin contratista") as contractor,
				preventive_risk_inspection.area,
				preventive_risk_inspection.date,
				preventive_risk_inspection.heading,
				preventive_risk_inspection.supervisor_name,
				preventive_risk_inspection.responsible_name,
				preventive_risk_inspection.observation,
				audit_categories.id,
				audit_subcategories.name,
				preventive_risk_inspection_detail.act,
				preventive_risk_inspection_detail.severity,
				preventive_risk_inspection_detail.hour,
				preventive_risk_inspection_detail.discipline,
				preventive_risk_inspection_detail.condition,
				preventive_risk_inspection_detail.action,
				preventive_risk_inspection_detail.observer,
				preventive_risk_inspection_detail.responsible,
				preventive_risk_inspection_detail.status,
				preventive_risk_inspection_detail.dateend
			')
			->leftJoin('projects','projects.idproyect','preventive_risk_inspection.project_id')
			->leftJoin('cat_code_w_bs','cat_code_w_bs.id','preventive_risk_inspection.wbs_id')
			->leftJoin('contractors','contractors.id','preventive_risk_inspection.contractor_id')
			->leftJoin('preventive_risk_inspection_detail','preventive_risk_inspection_detail.preventive_risk_inspection_id', 'preventive_risk_inspection.id')
			->leftJoin('audit_categories','audit_categories.id', 'preventive_risk_inspection_detail.category_id')
			->leftJoin('audit_subcategories','audit_subcategories.id','preventive_risk_inspection_detail.subcategory_id')
			->whereIn('preventive_risk_inspection.project_id', Auth::user()->inChargeProject(333)->pluck('project_id'))
			->where(function($query) use ($project_id,$code_wbs,$contractor,$area,$start_date,$end_date)
			{
				if($project_id != "")
				{
					$query->where('preventive_risk_inspection.project_id',$project_id);
				}
				if($code_wbs != "")
				{
					$query->where('preventive_risk_inspection.wbs_id',$code_wbs);
				}
				if($contractor != "")
				{
					$query->where('preventive_risk_inspection.contractor_id',$contractor);
				}
				if($start_date != "" && $end_date != "")
				{
					$query->whereBetween('preventive_risk_inspection.date',[$start_date->format('Y-m-d 00:00:00'), $end_date->format('Y-m-d 23:59:59')]);
				}
				if($area != "")
				{
					$query->where('preventive_risk_inspection.area','LIKE','%'.$area.'%');
				}
			})
			->get();

			Excel::create('Reporte-de-inspeccion-preventiva', function($excel) use ($preventives)
			{
				$excel->sheet('Reporte inspeccion',function($sheet) use($preventives)
				{
					$sheet->setStyle([
						'font' => [
							'name'	=> 'Calibri',
							'size'	=> 10
						],
						'alignment' => [
							'vertical' => 'center',
						]
					]);
					$sheet->mergeCells('A1:U1');							
					$sheet->cell('A1:U1', function($cells)
					{
						$cells->setBackground('#343a40');
						$cells->setFontColor('#ffffff');
					});

					$sheet->cell('A2:U2', function($cells)
					{
						$cells->setBackground('#343a40');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:U2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '12','bold' => true));
					});
					$sheet->row(1,['REPORTE DE INSPECCIÓN PREVENTIVA DE RIESGO']);
					$sheet->row(2,['Proyecto','Código WBS','Contratista','Lugar/Área','Fecha','Rubro','Supervisor SSPA','Responsable SSPA','Observaciones','Categoría','Subcategoría','Acto/Condición','Factor Severidad','Hora','Disciplina','Descripción Acto/Condición','Acción correctiva/preventiva','Observador','Responsable','Estatus','Fecha de cierre']);    
					foreach($preventives as $preventive)
					{
						$sheet->appendRow($preventive->toArray());
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
		if(Auth::user()->module->where('id',332)->count()>0)
		{
			$data= App\Module::find($this->module_id);
			return view('operacion.inspecciones_preventivas_riesgo.masivo',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 332,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id', 332)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert = "swal('', '".Lang::get("messages.file_null")."', 'error');";
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
						$name		= '/massive_preventive/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
							$alert = "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
							return back()->with('alert',$alert);
						}
						
						$headers = [
							'proyecto',
							'codigo_wbs',
							'contratista',
							'area',
							'fecha',
							'rubro',
							'supervisor_sspa',
							'responsable_sspa',
							'observaciones',
							'categoria',
							'subcategoria',
							'acto_condicion',
							'factor_severidad',
							'hora',
							'disciplina',
							'descripcion_acto_condicion',
							'acciones',
							'observador',
							'responsable',
							'estatus',
							'fecha_cierre'
						];

						if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
						{
							$alert = "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
							return back()->with('alert',$alert);	
						}

						array_shift($csvArr);

						$data = App\Module::find($this->module_id);
						return view('operacion.inspecciones_preventivas_riesgo.verifivar_masivo',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 332,
								'csv'		=> $csvArr,
								'fileName'	=> $name,
								'delimiter'	=> $request->separator
							]);
					}
					else
					{
						$alert = "swal('', '".Lang::get("messages.separator_error")."', 'error');";
						return back()->with('alert',$alert);
					}
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
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
		if(Auth::user()->module->where('id',332)->count()>0)
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
			$errors			= "";

			$tempProject		= "";
			$tempWbs			= "";
			$tempContractor		= "";
			$tempArea			= "";
			$tempDate			= "";
			$tempHeading		= "";
			$tempSupervisor		= "";
			$tempResponsible	= "";
			$tempObservation	= "";

			foreach($csvArr as $key => $e)
			{
				try
				{
					$projectWBSFlag = false;
					if(isset($e['proyecto']) && !empty(trim($e['proyecto'])))
					{
						if(App\Project::find($e['proyecto']) != '')
						{
							$checkProject = App\Project::find($e['proyecto'])->idproyect;
							if(App\Project::find($e['proyecto'])->codeWBS()->exists())
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

					$date = $e['fecha'];
					$date_close = $e['fecha_cierre'];

					if(isset($e['codigo_wbs']) && $e['codigo_wbs'] != "")
					{
						if(App\CatCodeWBS::where('project_id',$e['proyecto'])->where('id', $e['codigo_wbs'])->first() != '')
						{
							$checkwbs = App\CatCodeWBS::where('project_id',$e['proyecto'])->where('id', $e['codigo_wbs'])->first()->id;
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
					
					if($checkProject != "" && (($checkwbs != "" && $projectWBSFlag) || ($checkwbs == "" && !$projectWBSFlag) || ($checkwbs == "" && $projectWBSFlag)) && DateTime::createFromFormat('Y-m-d', $date) !== false)
					{
						if($tempProject != $e['proyecto'] || $tempWbs != $e['codigo_wbs'] || $tempContractor != $e['contratista'] || $tempArea != $e['area'] || $tempDate != $date || $tempHeading != $e['rubro'] || $tempSupervisor != $e['supervisor_sspa'] || $tempResponsible != $e['responsable_sspa'] || $tempObservation != $e['observaciones'])
						{
							$preventive	= new PreventiveRiskInspection();
							$preventive->project_id			= $e['proyecto'];
							$preventive->wbs_id				= !empty(trim($e['codigo_wbs'])) ? $e['codigo_wbs'] : null;
							$preventive->contractor_id		= !empty(trim($e['contratista'])) ? $e['contratista'] : null;
							$preventive->area				= $e['area'];
							$preventive->date				= $date;
							$preventive->heading			= !empty(trim($e['rubro'])) ? $e['rubro'] : null;
							$preventive->supervisor_name	= !empty(trim($e['supervisor_sspa'])) ? $e['supervisor_sspa'] : null;
							$preventive->responsible_name	= !empty(trim($e['responsable_sspa'])) ? $e['responsable_sspa'] : null;
							$preventive->observation		= !empty(trim($e['observaciones'])) ? $e['observaciones'] : null;
							$preventive->user_id          	= Auth::user()->id;
							$preventive->save();

							$id_preventive		= $preventive->id;
							$tempProject		= $e['proyecto'];
							$tempWbs			= $e['codigo_wbs'];
							$tempContractor		= $e['contratista'];
							$tempArea			= $e['area'];
							$tempDate			= $date;
							$tempHeading		= $e['rubro'];
							$tempSupervisor		= $e['supervisor_sspa'];
							$tempResponsible	= $e['responsable_sspa'];
							$tempObservation	= $e['observaciones'];
						}

						$details = new PreventiveRiskInspectionDetail();
						$details->preventive_risk_inspection_id	= $id_preventive;
						$details->category_id	= $e['categoria'];
						$details->subcategory_id= !empty(trim($e['subcategoria'])) ? $e['subcategoria'] : null;
						$details->act			= !empty(trim($e['acto_condicion'])) ? $e['acto_condicion'] : null;
						$details->severity		= !empty(trim($e['factor_severidad'])) ? $e['factor_severidad'] : null;
						$details->hour			= !empty(trim($e['hora'])) ? $e['hora'] : null;
						$details->discipline	= !empty(trim($e['disciplina'])) ? $e['disciplina'] : null;
						$details->condition		= !empty(trim($e['descripcion_acto_condicion'])) ? $e['descripcion_acto_condicion'] : null;
						$details->action		= !empty(trim($e['acciones'])) ? $e['acciones'] : null;
						$details->observer		= $e['observador'];
						$details->responsible   = !empty(trim($e['responsable'])) ? $e['responsable'] : null;
						$details->status        = $e['estatus'];
						$details->dateend       = !empty(trim($e['fecha_cierre'])) && $e['estatus'] == '1' ? $e['fecha_cierre'] : null;
						$details->save();

					}
					else
					{
						$errors .= $key+1 .",";
					}
				}
				catch(\Exception $e)
				{
				   //return $e;					
				}
			}

			if($errors != "")
			{
				$message = "Los datos de las filas ".$errors." no fueron registrados, debido a que son erroneos, por favor verifica la información";
				$alert	= "swal('', '".$message."', 'error');";
			}
			else
			{
				$alert = "swal('','Los datos han sido cargados correctamente','success');";
			}
			
			return redirect()->route('preventive.follow')->with('alert', $alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',332)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('preventive.massive');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[332,333])->count()>0)
		{
			Excel::create('Catalogos-Inspecccion-Preventiva-Riesgo', function($excel)
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
					foreach(App\Project::selectRaw(
						'idproyect, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as name')
						->whereIn('idproyect',Auth::user()
						->inChargeProject(333)
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
					foreach(App\CatCodeWBS::selectRaw(
						'id, code_wbs, CONCAT(IFNULL(proyectNumber,"")," - ",proyectName) as proyect')
						->join('projects','cat_code_w_bs.project_id','projects.idproyect')
						->where('cat_code_w_bs.status',1)
						->where('projects.status',1)
						->whereIn('projects.idproyect',Auth::user()->inChargeProject(333)
						->pluck('project_id'))
						->get() as $wbs)
					{
						$sheet->appendRow($wbs->toArray());
					}
				});
				$excel->sheet('Contratistas',function($sheet)
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
						'Contratista'
					]);
					foreach(App\Contractor::select('id', 'name')
						->get() as $contractor)
					{
						$sheet->appendRow($contractor->toArray());
					}
				});
				$excel->sheet('Categoría',function($sheet)
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
						'Categoría'
					]);
					foreach(App\AuditCategory::select('id', 'name')
						->get() as $category)
					{
						$sheet->appendRow($category->toArray());
					}
				});
				$excel->sheet('Subcategorías',function($sheet)
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
						'ID_Subcategoria',
						'Subcategoría',
						'ID-Categoría'
					]);
					foreach(App\AuditSubcategory::selectRaw(
						'audit_subcategories.id, audit_subcategories.name, CONCAT(audit_categories.name) as category')
						->join('audit_categories','audit_subcategories.audit_category_id','audit_categories.id')
						->get() as $subcategory)
					{
						$sheet->appendRow($subcategory->toArray());
					}
				});
				$excel->sheet('Rubro',function($sheet)
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
						'Rubro'
					]);
					
					$data =  [['1','Seguridad'],['2','Ambiental'],['3','Salud Ocupacional']];

					foreach($data as $key=>$value)
					{
						$row = [];
						$row[] = $value[0];
						$row[] = $value[1];
						$sheet->appendRow($row);
					}
				});
				$excel->sheet('Acto-Condicion',function($sheet)
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
						'Acto/Condicion'
					]);
					
					$data =  [['1','Acto'],['2','Condicion']];

					foreach($data as $key=>$value)
					{
						$row = [];
						$row[] = $value[0];
						$row[] = $value[1];
						$sheet->appendRow($row);
					}
				});
				$excel->sheet('Factor de Severidad',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
								'name' => 'Calibri',
								'size' => 12
							)
						));
					$sheet->cell('A1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,[
						'Factor de severidad'
					]);
					
					$data =  [['1/3'],['1'],['3']];

					foreach($data as $key=>$value)
					{
						$row = [];
						$row[] = $value[0];
						$sheet->appendRow($row);
					}
				});
				$excel->sheet('Tipo de acción',function($sheet)
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
						'Estatus'
					]);
					
					$data =  [['0','Abierto'],['1','Cerrado']];

					foreach($data as $key=>$value)
					{
						$row = [];
						$row[] = $value[0];
						$row[] = $value[1];
						$sheet->appendRow($row);
					}
				});
			})->export('xlsx');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportDosBocas(PreventiveRiskInspection $preventive)
	{
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$pdf	= PDF::loadView('operacion.inspecciones_preventivas_riesgo.documentos.documento_dos_bocas',['preventive'=>$preventive])->setPaper('A4', 'landscape');
			return $pdf->download('inspeccion'.$preventive->id.'.pdf');
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportTula(PreventiveRiskInspection $preventive)
	{
		if(Auth::user()->module->where('id',333)->count()>0)
		{
			$data	= App\Module::find($this->module_id);	
			$subcategories = App\AuditSubcategory::get();
			$subcategories =  $subcategories->groupBy('audit_category_id');		
			$pdf	= PDF::loadView('operacion.inspecciones_preventivas_riesgo.documentos.documento_tula',['preventive'=>$preventive, 'subcategories' => $subcategories])->setPaper('A4', 'landscape');
			return $pdf->download('inspeccion'.$preventive->id.'.pdf');
		}
		else
		{
			return redirect('/');
		}
	}

	public function getSubCategory(Request $request)
	{
		if ($request->ajax()) 
		{
			$subCat = App\AuditSubcategory::where('audit_category_id',$request->id_category)->orderBy('id','asc')->get();
			return Response($subCat);
		}
	}
}
