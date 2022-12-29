<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use DateTime;
use Lang;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Module;
use App\Project;
use App\CatCodeWBS;
use App\NonConformitiesStatus;
use App\NonConformitiesStatusDocument;
use Carbon\Carbon;
use Genkgo\Xsl\Callback\FunctionInterface;
use Ilovepdf\CompressTask;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class OperationNonConformityStatusController extends Controller
{
	protected $module_id = 11;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{   
			$data = Module::find($this->module_id);
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
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',27)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('operacion.estado_no_conformidad.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 27
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function getWBS(Request $request)
	{
		if ($request->ajax()) 
		{
			$wbs = CatCodeWBS::where('project_id',$request->idproject)->where('status',1)->orderBy('code_wbs','asc')->get();
			return Response($wbs);
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',27)->count()>0) 
		{
			$data	= Module::find($this->module_id);

			$object 						= new NonConformitiesStatus();
			$object->project_id				= $request->project_id;
			$object->wbs_id					= $request->code_wbs;
			$object->description			= $request->description;
			$object->date					= $request->date!="" ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$object->location				= $request->location;
			$object->process_area			= $request->process_area;
			$object->non_conformity_origin	= $request->non_conformity_origin;
			$object->type_of_action			= $request->type_of_action;
			$object->action					= $request->action;
			$object->emited_by				= $request->emited_by;
			$object->status					= $request->status;
			$object->nc_report_number		= $request->nc_report_number;
			$object->close_date				= $request->close_date!="" ? Carbon::createFromFormat('d-m-Y',$request->close_date)->format('Y-m-d') : null;
			$object->observations			= $request->observations;
			$object->user_id				= Auth::user()->id;
			$object->save();

			if (isset($request->real_path) && count($request->real_path)>0) 
			{
				for ($i=0; $i < count($request->real_path); $i++) 
				{ 
					if ($request->real_path[$i] != "") 
					{
						$new_doc								= new NonConformitiesStatusDocument();
						$new_doc->path							= $request->real_path[$i];
						$new_doc->non_conformities_status_id	= $object->id;
						$new_doc->user_id						= Auth::user()->id;
						$new_doc->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('status-nc.follow')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
		
	}

	public function follow(Request $request)
	{
		
		if(Auth::user()->module->where('id',42)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$project_id		= $request->project_id;
			$code_wbs		= $request->code_wbs;
			$description	= $request->description;
			$type_of_action	= $request->type_of_action;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
            
            if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}

            $status_no_conformity = NonConformitiesStatus::whereIn('project_id', Auth::user()->inChargeProject(42)->pluck('project_id'))
            ->where(function ($query) use ($project_id,$code_wbs,$description,$type_of_action,$status,$mindate,$maxdate)
            {
				if($project_id != "")
                {
                    $query->where('non_conformities_statuses.project_id',$project_id);
                }
                if($code_wbs != "")
                {
                    $query->where('non_conformities_statuses.wbs_id',$code_wbs);
                }
				if($description != "")
                {
                    $query->where('non_conformities_statuses.description','LIKE','%'.$description.'%');
                }
				if($type_of_action != "")
                {
                    $query->where('non_conformities_statuses.type_of_action',$type_of_action);
                }
                if($status != "")
                {
                    $query->where('non_conformities_statuses.status',$status);
                }
				if($mindate != "" && $maxdate != "")
                {
                    $query->whereBetween('non_conformities_statuses.date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
                }
				if($mindate != "" && $maxdate != "")
                {
                    $query->whereBetween('non_conformities_statuses.close_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
                }
			})
			->orderBy('id', 'DESC')
			->paginate(10);
			return view('operacion.estado_no_conformidad.seguimiento',
			[
				'id'					=> $data['father'],
				'title'					=> $data['name'],
				'details'				=> $data['details'],
				'child_id'				=> $this->module_id,
				'option_id'				=> 42,
				'status_no_conformity' 	=> $status_no_conformity,
				'project_id'    		=> $project_id,
                'code_wbs'      		=> $code_wbs,
				'description'   		=> $description,
				'type_of_action'		=> $type_of_action,
				'status'				=> $status,
                'mindate'			   		=> $request->mindate,
                'maxdate'      		=> $request->maxdate,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',42)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$n_c_status	= NonConformitiesStatus::find($id);
			if($n_c_status != "")
			{
				return view('operacion.estado_no_conformidad.alta',
				[
					'id'        =>  $data['father'],
                    'title'     =>  $data['name'],
                    'details'   =>  $data['details'],
                    'child_id'  =>  $this->module_id,
                    'option_id' => 	42,
                    'n_c_status'  =>  $n_c_status
				]);
			}
		}
	}

	public function update(Request $request, NonConformitiesStatus $n_c_status)
	{
		if(Auth::user()->module->where('id', 42)->count()>0)
		{
			$n_c_status->project_id				= $request->project_id;
			$n_c_status->wbs_id					= $request->code_wbs;
			$n_c_status->description			= $request->description;
			$n_c_status->date					= $request->date!="" ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$n_c_status->location				= $request->location;
			$n_c_status->process_area			= $request->process_area;
			$n_c_status->non_conformity_origin	= $request->non_conformity_origin;
			$n_c_status->type_of_action			= $request->type_of_action;
			$n_c_status->action					= $request->action;
			$n_c_status->emited_by				= $request->emited_by;
			$n_c_status->status					= $request->status;
			$n_c_status->nc_report_number		= $request->nc_report_number;
			$n_c_status->close_date				= $request->close_date!="" ? Carbon::createFromFormat('d-m-Y',$request->close_date)->format('Y-m-d') : null;
			$n_c_status->observations			= $request->observations;
			$n_c_status->user_id				= Auth::user()->id;

			$n_c_status->save();
			if(isset($request->docPathDeleted) && count($request->docPathDeleted)>0)
			{
				NonConformitiesStatusDocument::whereIn('path',$request->docPathDeleted)->delete();
			}
			if (isset($request->real_path) && count($request->real_path)>0) 
			{
				for ($i=0; $i < count($request->real_path); $i++) 
				{ 
					if ($request->real_path[$i] != "") 
					{
						$new_doc								= new NonConformitiesStatusDocument();
						$new_doc->path							= $request->real_path[$i];
						$new_doc->non_conformities_status_id	= $n_c_status->id;
						$new_doc->user_id						= Auth::user()->id;
						$new_doc->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return back() ->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',42)->count()>0)
		{
			$data		= Module::find($this->module_id);
			$project_id		= $request->project_id;
			$code_wbs		= $request->code_wbs;
			$description	= $request->description;
			$type_of_action	= $request->type_of_action;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			 if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Debe ingresar un rango de fechas','error');";
				return back()->with('alert',$alert);
			}
			
			$status_no_conformity = DB::table('non_conformities_statuses')->selectRaw(
				'
					projects.proyectName,
					IF(cat_code_w_bs.code_wbs != "" ,cat_code_w_bs.code_wbs,"Sin código") as code_wbs,
					non_conformities_statuses.description,
					non_conformities_statuses.date,
					non_conformities_statuses.location,
					non_conformities_statuses.process_area,
					non_conformities_statuses.non_conformity_origin,
					IF(non_conformities_statuses.type_of_action = 1, "No Confirmidad",IF(non_conformities_statuses.type_of_action = 2, "Acción Correctiva","Oportunidad de Mejora")) AS type_of_action,
					non_conformities_statuses.action,
					non_conformities_statuses.emited_by,
					IF(non_conformities_statuses.status = 1,"Activo",IF(non_conformities_statuses.status = 2,"En Proceso","Finalizado")) AS status,
					non_conformities_statuses.nc_report_number,
					non_conformities_statuses.close_date,
					non_conformities_statuses.observations
				')
				->leftJoin('projects','projects.idproyect', '=', 'non_conformities_statuses.project_id')
				->leftJoin('cat_code_w_bs','cat_code_w_bs.id', '=', 'non_conformities_statuses.wbs_id')
				->whereIn('non_conformities_statuses.project_id', Auth::user()->inChargeProject(42)->pluck('project_id'))
				->where(function ($query) use ($project_id,$code_wbs,$description,$type_of_action,$status,$mindate,$maxdate)
				{
					if($project_id != "")
					{
						$query->where('non_conformities_statuses.project_id',$project_id);
					}
					if($code_wbs != "")
					{
						$query->where('non_conformities_statuses.wbs_id',$code_wbs);
					}
					if($description != "")
					{
						$query->where('non_conformities_statuses.description','LIKE','%'.$description.'%');
					}
					if($type_of_action != "")
					{
						$query->where('non_conformities_statuses.type_of_action',$type_of_action);
					}
					if($status != "")
					{
						$query->where('non_conformities_statuses.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('non_conformities_statuses.date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
					}if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('non_conformities_statuses.close_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
					}
				})
				->get();
				
			if(count($status_no_conformity)==0 || $status_no_conformity==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$dateFormat   	= (new StyleBuilder())->setFormat('d-m-yy');
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-Estado-de-no-conformidades.xlsx');
			$writer->getCurrentSheet()->setName('REPORTE');

			$headers		= ['REPORTE DE ESTADO DE NO CONFORMIDADES','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ["Proyecto","Código WBS","Descripción de No Confirmidad / Oportunidad de mejora","Fecha","Localización","Proceso y/o área","No conformidad/oportunidad de mejora originada por","Tipo de Acción	","Acción","Emitida por","Estatus","Número de reporte de no conformidad","Fecha de cierre de reporte de no conformidad","Observaciones"];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempId     	= '';
			$kindRow		= true;
			foreach($status_no_conformity as $request)
			{
				
				if($tempId != $request->proyectName)
				{
					$tempId = $request->proyectName;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->proyectName		= '';
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
			return $writer->close();
        }
        else
        {
            return redirect('error');
        }
	}
	
	public function massive()
	{
		if(Auth::user()->module->where('id',299)->count()>0)
		{
			$data = Module::find($this->module_id);
			return view('operacion.estado_no_conformidad.masivo',
			[
				'id'        => $data['father'],
                'title'     => $data['name'],
                'details'   => $data['details'],
                'child_id'  => $this->module_id,
                'option_id' => 299,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function massiveUpload(Request $request)
    {
        if(Auth::user()->module->where('id', 299)->count()>0)
        {
            if($request->file('csv_file') == "")
            {
				$alert = "swal('', '".Lang::get("messages.file_null")."', 'error');";
                return back()->with('alert',$alert);
            }

            $valid = $request->file('csv_file')->getClientOriginalExtension();
							
			if($valid != 'csv')
			{
				$alert = "swal('', '".Lang::get("messages.extension_allowed",["param"=>'CSV'])."', 'error');";
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
                        $name		= '/massive_ncstatus/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
						array_shift($csvArr);

						$headers = [
							'proyecto',
							'codigo_wbs',
							'descripcion',
							'fecha',
							'localizacion',
							'proceso_area',
							'no_conformidad_origen',
							'tipo_de_accion',
							'accion',
							'emitida_por',
							'estatus',
							'numero_reporte_nc',
							'fecha_de_cierre',
							'observaciones'
						];

						if($csvArr == null)
						{
							$alert	= "swal('', 'El archivo cargado no cuenta con registros, por favor verifique los datos e intente de nuevo.', 'error');";
							return back()->with('alert',$alert);
						}

						// Función para validar documentos diferentes
						if(array_diff($headers, array_keys($csvArr[0])))
						{
							$alert = "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
							return back()->with('alert',$alert);	
						}
						$data = Module::find($this->module_id);
						return view('operacion.estado_no_conformidad.verificar_masivo',
							[
								'id'		=> $data['father'],
								'title'		=> $data['name'],
								'details'	=> $data['details'],
								'child_id'	=> $this->module_id,
								'option_id'	=> 299,
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
		if(Auth::user()->module->where('id',299)->count()>0)
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

					$date = $e['fecha'];
                    $close_date = $e['fecha_de_cierre'];
					$dateFlag = false;

					if ($date > $close_date)
                    {
                        $dateFlag = true;
                    }

					if($checkProject != "" && (($checkwbs != "" && $projectWBSFlag) || ($checkwbs == "" && !$projectWBSFlag)) 
                    && DateTime::createFromFormat('Y-m-d', $date) !== false && DateTime::createFromFormat('Y-m-d', $close_date) !== false
                    && !$dateFlag) 
                    {
                        if(!empty(trim($e['descripcion'])) &&
                        !empty(trim($date)) &&
                        !empty(trim($e['localizacion'])) &&
                        !empty(trim($e['proceso_area'])) &&
                        !empty(trim($e['no_conformidad_origen'])) &&
                        !empty(trim($e['tipo_de_accion'])) &&
                        !empty(trim($e['accion'])) &&
                        !empty(trim($e['emitida_por'])) &&
                        !empty(trim($e['estatus'])) &&
                        !empty(trim($e['numero_reporte_nc'])) &&
                        !empty(trim($close_date)) &&
                        !empty(trim($e['observaciones'])))
                        {
                            $status_no_conformity   = new NonConformitiesStatus();

                            $status_no_conformity->project_id       		= $e['proyecto'];
                            $status_no_conformity->wbs_id           		= !empty(trim($e['codigo_wbs'])) ? $e['codigo_wbs'] : null;
                            $status_no_conformity->description            	= $e['descripcion'];
                            $status_no_conformity->date       				= $date;
                            $status_no_conformity->location        			= $e['localizacion'];
                            $status_no_conformity->process_area       		= $e['proceso_area'];
                            $status_no_conformity->non_conformity_origin	= $e['no_conformidad_origen'];
                            $status_no_conformity->type_of_action         	= $e['tipo_de_accion'];
                            $status_no_conformity->action         			= $e['accion'];
                            $status_no_conformity->emited_by             	= $e['emitida_por'];
                            $status_no_conformity->status  					= $e['estatus'];
                            $status_no_conformity->nc_report_number    		= $e['numero_reporte_nc'];
                            $status_no_conformity->close_date      			= $close_date;
                            $status_no_conformity->observations      		= $e['observaciones'];
                            $status_no_conformity->user_id          		= Auth::user()->id;
                            $status_no_conformity->save();
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
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
            }
            return redirect()->route('status-nc.follow')->with('alert', $alert);
        }		
		else
		{
			return redirect('/');
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',299)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
            return redirect()->route('status-nc.massive');
        }
        else
        {
            return redirect('/');
        }
		
	}

	public function exportCatalogs()
	{
		if(Auth::user()->module->whereIn('id',[42,299])->count()>0)
		{
			Excel::create('Catalogos-Estado de no conformidades', function($excel)
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
                        ->inChargeProject(42)
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
                        ->whereIn('projects.idproyect',Auth::user()->inChargeProject(42)
                        ->pluck('project_id'))
                        ->get() as $wbs)
					{
						$sheet->appendRow($wbs->toArray());
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
						'Tipo de Accion'
					]);
					
					$data =  [['1','No conformidad'],['2','Acción Correctiva'],['3','Oportunidad de mejora']];

					foreach($data as $key=>$value)
					{
						$row = [];
						$row[] = $value[0];
						$row[] = $value[1];
						$sheet->appendRow($row);
					}
				});
				$excel->sheet('Estados',function($sheet)
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
						'Estado'
					]);
					
					$data =  [['1','Activo'],['2','En proceso'],['3','Finalizado']];

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
					\Storage::disk('public')->delete('/docs/status-nc/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/status-nc/'.$name;
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

	public function exportPDF(Request $request)
	{
		if(Auth::user()->module->where('id',42)->count()>0)
		{
			$project_id		= $request->project_id;
			$code_wbs		= $request->code_wbs;
			$description	= $request->description;
			$type_of_action	= $request->type_of_action;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;

			if (($mindate == "" && $maxdate != "") || ($mindate != "" && $maxdate == "")) 
			{
				$alert = "swal('','Por favor ingrese un rango de fechas','error');";
				return back()->with('alert',$alert);
			}

            $status_no_conformity = NonConformitiesStatus::whereIn('project_id', Auth::user()->inChargeProject(42)->pluck('project_id'))
            ->where(function ($query) use ($project_id,$code_wbs,$description,$type_of_action,$status,$mindate,$maxdate)
            {
				if($project_id != "")
                {
                    $query->where('non_conformities_statuses.project_id',$project_id);
                }
                if($code_wbs != "")
                {
                    $query->where('non_conformities_statuses.wbs_id',$code_wbs);
                }
				if($description != "")
                {
                    $query->where('non_conformities_statuses.description','LIKE','%'.$description.'%');
                }
				if($type_of_action != "")
                {
                    $query->where('non_conformities_statuses.type_of_action',$type_of_action);
                }
                if($status != "")
                {
                    $query->where('non_conformities_statuses.status',$status);
                }
				if($mindate != "" && $maxdate != "")
                {
                    $query->whereBetween('non_conformities_statuses.date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
                }
				if($mindate != "" && $maxdate != "")
                {
                    $query->whereBetween('non_conformities_statuses.close_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 00:00:00')]);
                }
			})
			->orderBy('id', 'DESC')
			->get();

			//return view('operacion.estado_no_conformidad.documento',['status_no_conformity'=>$status_no_conformity]);

			$pdf = \App::make('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->loadView('operacion.estado_no_conformidad.documento',['status_no_conformity'=>$status_no_conformity])->setPaper('a4','landscape');
			return $pdf->download('no_conformidad.pdf');
		}
		else
		{
			return redirect('/');
		}
	}
}
