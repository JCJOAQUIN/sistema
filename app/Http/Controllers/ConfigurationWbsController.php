<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App;
use Auth;
use Lang;
use App\Project;
use App\Module;
use App\CatCodeWBS;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;

class ConfigurationWbsController extends Controller
{
	private $module_id = 334;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=>$data['father'],
					'title'		=>$data['name'],
					'details'	=>$data['details'],
					'child_id'	=>$this->module_id
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',335)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$projects	= App\Project::orderName()->get();
			return view('configuracion.wbs.create',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'projects'      => $projects,
					'option_id'		=> 335
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',336)->count()>0)
		{
			$data	    = App\Module::find($this->module_id);
			$namewbs    = $request->name;
			$code       = $request->code;
			$projects 	= App\CatCodeWBS::where(function($query) use ($namewbs, $code)
			{
				if ($namewbs != "") 
				{
					$query->where('code_wbs','LIKE','%'.$namewbs.'%');
				}
				if ($code != "") 
				{
					$query->where('code','LIKE','%'.$code.'%');
				}
			})
			->orderBy('id', 'desc')
			->paginate(10);

			return response(
				view('configuracion.wbs.search',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 336,
					'namewbs'		=> $namewbs,
					'code'			=> $code,
					'projects' 		=> $projects
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(336), 2880
			);	
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$id_project = current($request->projects);
			$data	    = App\Module::find($this->module_id);
			$projects	= App\Project::orderName()->get();

			$count = App\CatCodeWBS::where('project_id', $id_project )
			->where('code', $request->code)
			->count();

			if($count == 0)
			{
				$project                = new App\CatCodeWBS();
				$project->code          = $request->code;
				$project->code_wbs      = $request->name;
				$project->project_id    = $id_project;
				$project->status        = 1;
				$project->save();

				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			}
			else
			{
				$alert = "swal('', 'Este código ya existe en el proyecto seleccionado, favor de verificar.', 'error');";
			}
				return redirect('configuration/wbs')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',336)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$wbs 	= App\CatCodeWBS::find($id);
			if($wbs != "")
			{
				return view('configuracion.wbs.edit',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 336,
						'wbs' 		=> $wbs
					]);
			}
			else
			{
				return redirect('/error');
			}
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
			$data 	= App\Module::find($this->module_id);
			$wbs	= App\CatCodeWBS::find($id);

			if($request->code == $wbs->code && $request->nameWbs == $wbs->code_wbs)
			{
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}

			if($request->code != $wbs->code || $request->nameWbs != $wbs->code_wbs)
			{
				$wbsExist = App\CatCodeWBS::where('project_id', $request->idProject)
					   ->where('code', $request->code)
					   ->get();
				
				if (count($wbsExist) > 0) {
					foreach ($wbsExist as $Ew){
						if ($Ew->code_wbs != $request->nameWbs && $Ew->code == $request->code && $Ew->id == $id)
						{
							$Newbs 				= App\CatCodeWBS::find($id);
							$Newbs->code_wbs	= $request->nameWbs;
							$Newbs->save();
							$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
							return back()->with('alert',$alert);
						}
						else 
						{
							$alert 	= "swal('', 'El código introducido ya existe en este proyecto, por favor verifique los datos.', 'error');";
							return view('configuracion.wbs.edit',
								[
									'id'		=> $data['father'],
									'title'		=> $data['name'],
									'details'	=> $data['details'],
									'child_id'	=> $this->module_id,
									'option_id'	=> 336,
									'wbs' 		=> $wbs
								])->with('alert',$alert);	
						}
					}	
				}
				else
				{
					$wbs->code 		= $request->code;
					$wbs->code_wbs	= $request->nameWbs;
					$wbs->save();

					$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
					return back()->with('alert',$alert);
				}
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$wbs			= App\CatCodeWBS::find($id);
			$wbs->status	= 0;
			$wbs->save();
			$alert 			= "swal('','Registro desactivado exitosamente','success');";

			return redirect('/configuration/wbs/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function up($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$wbs			= App\CatCodeWBS::find($id);
			$wbs->status	= 1;
			$wbs->save();
			$alert 			= "swal('','Registro activado correctamente','success');";

			return redirect('/configuration/wbs/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}


	public function massive()
	{
		if(Auth::user()->module->where('id',337)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('configuracion.wbs.massive',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 337
			]);
		}
		else
		{
			return abort(404);
		}
		
	}

	public function exportProjects()
	{
		if(Auth::user()->module->whereIn('id',[335,337])->count()>0)
		{
			$defaultStyle = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$headerStyle  = (new StyleBuilder())->setFontName('Calibri')->setFontSize(16)->setFontBold()->build();
			$writer       = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('proyectos-wbs.xlsx');

			$title 	= 'Proyectos';
			$values = Project::select('idproyect', 'proyectName')->where('status',1)->get();

			$headers = [
				WriterEntityFactory::createCell("ID",$headerStyle),
				WriterEntityFactory::createCell("Proyecto",$headerStyle)
			];

			$sheet 			= $writer->getCurrentSheet();
			$sheet->setName($title);
			$rowFromValues 	= WriterEntityFactory::createRow($headers);
			$writer->addRow($rowFromValues);

			foreach($values as $keyValue => $valTmp)
			{
				$tmpArr = [];
				foreach($valTmp->toArray() as $k => $v)
				{
					$tmpArr[] 	 	= WriterEntityFactory::createCell($v);
					$rowFromValues 	= WriterEntityFactory::createRow($tmpArr);
				}
				$writer->addRow($rowFromValues);
				unset($values[$keyValue]);
			}
			
			return $writer->close();
		}
	}

	public function massiveUpload(Request $request)
	{
		if(Auth::user()->module->where('id',337)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert = "swal('', '".Lang::get("messages.file_null")."', 'error');";
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
					$name		= '/massive_wbs/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first	= true;
						$count = 0;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first   = false;
							}
							$csvArr[] = $data;
							$count++;
							if($count == 11)
							{
								break;
							}
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
						'codigo_del_wbs',
						'nombre_del_wbs',
						'id_del_proyecto'
					];

					if($csvArr == null)
					{
						$alert	= "swal('', 'El archivo cargado no cuenta con registros, por favor verifique los datos e intente de nuevo.', 'error');";
						return back()->with('alert',$alert);
					}

					// Función para validar documentos diferentes
					if(empty($csvArr) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert = "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
						return back()->with('alert',$alert);	
					}

					$data = Module::find($this->module_id);
					return view('configuracion.wbs.verify_massive',
						[
							'id'        => $data['father'],
							'title'     => $data['name'],
							'details'   => $data['details'],
							'child_id'  => $this->module_id,
							'option_id' => 337,
							'csv'       => $csvArr,
							'fileName'  => $name,
							'delimiter' => $request->separator
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
			return abort(404);
		}
	}

	public function massiveCancel(Request $request)
	{
		if(Auth::user()->module->where('id',337)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('wbs.massive');
		}
		else
		{
			return abort(404);
		}
	}

	public function massiveContinue(Request $request)
	{
		if(Auth::user()->module->where('id',337)->count()>0)
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

			foreach ($csvArr as $key => $w) 
			{
				try {
					$exist = CatCodeWBS::where('project_id', $w['id_del_proyecto'])
					->where('code', $w['codigo_del_wbs'])
					->get();
					
					if (count($exist) > 0)
					{
						foreach ($exist as $Ew){
							if ($Ew->code_wbs != $w['nombre_del_wbs'])
							{
								$Newbs 				= App\CatCodeWBS::find($Ew->id);
								$Newbs->code_wbs	= $w['nombre_del_wbs'];
								$Newbs->save();
								$csvArr[$key]['status'] = 'WBS actualizado correctamente.';
								$csvArr[$key]['id'] = $Newbs->id;
							}
							else
							{
								$csvArr[$key]['status'] = 'WBS no registrado, ya existe.';
								$csvArr[$key]['id'] = '';
							}
						}			
					}
					elseif(count($exist) == 0)
					{
						if(!empty($w['codigo_del_wbs']) && !empty($w['nombre_del_wbs']) && !empty($w['id_del_proyecto']))
						{
							$existProject = App\Project::where('idproyect', $w['id_del_proyecto'])
								->get();

							if (strlen($w['codigo_del_wbs']) <= 5) {
								if (count($existProject) > 0) {
									$wbs 				= new CatCodeWBS();
									$wbs->code 			= $w['codigo_del_wbs'];
									$wbs->code_wbs 		= $w['nombre_del_wbs'];
									$wbs->project_id 	= $w['id_del_proyecto'];
									$wbs->status 		= 1;
									$wbs->save();

									$Newbs = App\CatCodeWBS::find($wbs->id);
									$csvArr[$key]['status'] = 'WBS registrado con exito.';
									$csvArr[$key]['id'] = $Newbs->id;	
								}
								else
								{
									$csvArr[$key]['status'] = 'WBS no registrado, ID de proyecto no existente.';
									$csvArr[$key]['id'] = '';
								}
							}
							else
							{
								$csvArr[$key]['status'] = 'WBS no registrado, código mayor a 5 caracteres.';
								$csvArr[$key]['id'] = '';
							}
						}
						elseif(empty($w['codigo_del_wbs']) || empty($w['nombre_del_wbs']) || empty($w['id_del_proyecto']))
						{
							$csvArr[$key]['status'] = 'WBS no registrado, campo vacío.';
							$csvArr[$key]['id'] = '';
						}
					}		
				} catch (\Exception $e) {
					$csvArr[$key]['status'] = 'Error';
					$csvArr[$key]['id']     = '';
				}	
			}
			$data    = Module::find($this->module_id);
			return view('configuracion.wbs.result_massive',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 337,
					'csv'       => $csvArr
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function validation(Request $request)
	{
		if (strlen($request->code) <= 5 && strlen($request->code) > 0) 
		{
			if(isset($request->oldCode))
			{
				$code = App\CatCodeWBS::find($request->oldCode);
				if($code->code===$request->code)
				{
					$response = array('valid' => true);
				}
				else
				{
					$exist = App\CatCodeWBS::where('code',$request->code)->where('project_id', $code->project_id)->where('id','!=',$code->id)->get();
					if(count($exist)>0)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'El código ya existe en este proyecto.'
						);
					}
					else
					{
						$response = array('valid' => true);
					}
				}
			}
			elseif(isset($request->proyect_id))
			{
				$code = App\CatCodeWBS::where('project_id',$request->proyect_id)->where('code',$request->code)->count();
				if($code>0)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El código ya existe en este proyecto.'
					);
				}
				else
				{
					$response = array('valid' => true);
				}
			}
		}
		elseif (strlen($request->code) < 1) {
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio.'
			);
		}
		elseif(strlen($request->code) > 5)
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo no debe exceder 5 caracteres.'
			);
		}
		else
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Error, intente de nuevo.'
			);
		}
		return Response($response);
	}
}
