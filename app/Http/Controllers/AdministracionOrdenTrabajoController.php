<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App;
use Auth;
use Lang;
use Carbon\Carbon;
use Ilovepdf\CompressTask;
use App\Functions\Files;

class AdministracionOrdenTrabajoController extends Controller
{
	private $module_id = 259;

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
			return view('administracion.orden_trabajo.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 260
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',260)) 
		{
			$t_request              = new App\RequestModel();
			$t_request->fDate       = Carbon::now();
			$t_request->idElaborate = Auth::user()->id;
			$t_request->idRequest   = Auth::user()->id;
			$t_request->idProject   = $request->project_id;
			$t_request->status      = 2;
			$t_request->kind        = 22;
			$t_request->save();

			$workOrder                 = new App\WorkOrder();
			$workOrder->title          = $request->title;
			$workOrder->elaborate_date = Carbon::now();
			$workOrder->number         = $request->number;
			$workOrder->date_obra      = Carbon::parse($request->date_obra)->format('Y-m-d');
			$workOrder->idFolio        = $t_request->folio;
			$workOrder->urgent         = $request->urgent;
			

			$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
			if(!$applicant)
			{
				$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
			} 

			$workOrder->applicant = $applicant->id;
			$workOrder->save();

			$idWorkOrder = $workOrder->id;

			if (isset($request->quantity) && count($request->quantity)>0) 
			{
				for ($i=0; $i < count($request->quantity); $i++) 	
				{
					$detail              = new App\WorkOrderDetail();
					$detail->part        = $request->part[$i];
					$detail->quantity    = $request->quantity[$i];
					$detail->unit        = $request->unit[$i];
					$detail->description = $request->description[$i];
					$detail->idWorkOrder = $idWorkOrder;
					$detail->save();
				}
			}

			if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
			{
				for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
				{
					if ($request->realPathWorkOrder[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
						$documents					= new App\WorkOrderDocuments();
						$documents->name			= $request->nameDocumentWorkOrder[$i];
						$documents->path			= $new_file_name;
						$documents->idWorkOrder	= $idWorkOrder;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
			return redirect()->route('work_order.search')->with('alert',$alert);
		}
	}

	public function save(Request $request)
	{
		if (Auth::user()->module->where('id',260)) 
		{
			$t_request              = new App\RequestModel();
			$t_request->fDate       = Carbon::now();
			$t_request->idElaborate = Auth::user()->id;
			$t_request->idRequest   = Auth::user()->id;
			$t_request->idProject   = $request->project_id;
			$t_request->status      = 2;
			$t_request->kind        = 22;
			$t_request->save();

			$workOrder                 = new App\WorkOrder();
			$workOrder->title          = $request->title;
			$workOrder->elaborate_date = Carbon::now();
			$workOrder->number         = $request->number;
			$workOrder->date_obra      = Carbon::parse($request->date_obra)->format('Y-m-d');
			$workOrder->idFolio        = $t_request->folio;
			$workOrder->urgent         = $request->urgent;
			

			$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
			if(!$applicant)
			{
				$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
			} 

			$workOrder->applicant = $applicant->id;
			$workOrder->save();

			$idWorkOrder = $workOrder->id;

			if (isset($request->quantity) && count($request->quantity)>0) 
			{
				for ($i=0; $i < count($request->quantity); $i++) 	
				{
					$detail              = new App\WorkOrderDetail();
					$detail->part        = $request->part[$i];
					$detail->quantity    = $request->quantity[$i];
					$detail->unit        = $request->unit[$i];
					$detail->description = $request->description[$i];
					$detail->idWorkOrder = $idWorkOrder;
					$detail->save();
				}
			}

			if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
			{
				for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
				{
					if ($request->realPathWorkOrder[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
						$documents					= new App\WorkOrderDocuments();
						$documents->name			= $request->nameDocumentWorkOrder[$i];
						$documents->path			= $new_file_name;
						$documents->idWorkOrder	= $idWorkOrder;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			
			$errors = 0;
			if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid())
			{
				try
				{
					$name		= '/massive_work_order/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.extension_allowed",["param"=>'CSV'])."', 'error')";
					return redirect()->back()->withAlert($alert);
				}
				array_shift($csvArr);

				foreach ($csvArr as $art) 
				{
					if (
						(isset($art['part']) && trim($art['part'])!="") &&
						(isset($art['cantidad']) && trim($art['cantidad'])>0) &&
						(isset($art['unidad']) && trim($art['unidad'])!="") &&
						(isset($art['descripcion']) && trim($art['descripcion'])!=""))
					{
						$detail              = new App\WorkOrderDetail();
						$detail->part        = $art["part"];
						$detail->quantity    = $art["cantidad"];
						$detail->unit        = $art["unidad"];
						$detail->description = $art["descripcion"];
						$detail->idWorkOrder = $idWorkOrder;
						$detail->save();
					}
					else
					{
						$errors++;
					}
				}
			}

			if ($errors > 0) 
			{
				$alert = "swal('Orden de Trabajo Guardada Con Errores','".$errors." registros del archivo CSV no se guardaron debido a que los campos estaban vacios', 'info');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			}

			return redirect()->route('work_order.edit',['id'=>$t_request->folio])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function saveFollow(Request $request,$id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data					=	App\Module::find($this->module_id);
			$t_request				=	App\RequestModel::find($id);
			$t_request->fDate		=	Carbon::now();
			$t_request->idElaborate	=	Auth::user()->id;
			$t_request->idRequest	=	Auth::user()->id;
			$t_request->idProject	=	$request->project_id;
			$t_request->status		=	2;
			$t_request->kind		=	22;
			$t_request->save();

			$workOrderId				=	$t_request->workOrder->id;
			$workOrder					=	App\WorkOrder::find($workOrderId);
			$workOrder->title			=	$request->title;
			$workOrder->elaborate_date	=	Carbon::now();
			$workOrder->number			=	$request->number;
			$workOrder->date_obra		=	$request->date_obra;
			$workOrder->idFolio			=	$t_request->folio;
			$workOrder->urgent			=	$request->urgent;

			$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
			if(!$applicant)
			{
				$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
			} 

			$workOrder->applicant = $applicant->id;
			$workOrder->save();

			$idWorkOrder = $workOrder->id;

			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\WorkOrderDetail::whereIn('id',$request->delete)->delete();
			}

			if (isset($request->quantity) && count($request->quantity)>0) 
			{
				for ($i=0; $i < count($request->quantity); $i++) 	
				{
					$detail              = new App\WorkOrderDetail();
					$detail->part        = $request->part[$i];
					$detail->quantity    = $request->quantity[$i];
					$detail->unit        = $request->unit[$i];
					$detail->description = $request->description[$i];
					$detail->idWorkOrder = $idWorkOrder;
					$detail->save();
				}
			}

			if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
			{
				for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
				{
					if ($request->realPathWorkOrder[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
						$documents					= new App\WorkOrderDocuments();
						$documents->name			= $request->nameDocumentWorkOrder[$i];
						$documents->path			= $new_file_name;
						$documents->idWorkOrder	= $idWorkOrder;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			
			$errors = 0;
			if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid())
			{
				try
				{
					$name		= '/massive_work_order/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.extension_allowed",["param"=>'CSV'])."', 'error')";
					return redirect()->back()->withAlert($alert);
				}
					array_shift($csvArr);
				foreach ($csvArr as $art) 
				{
					if (
						(isset($art['part']) && trim($art['part'])!="") &&
						(isset($art['cantidad']) && trim($art['cantidad'])>0) &&
						(isset($art['unidad']) && trim($art['unidad'])!="") &&
						(isset($art['descripcion']) && trim($art['descripcion'])!=""))
					{
						$detail              = new App\WorkOrderDetail();
						$detail->part        = $art["part"];
						$detail->quantity    = $art["cantidad"];
						$detail->unit        = $art["unidad"];
						$detail->description = $art["descripcion"];
						$detail->idWorkOrder = $idWorkOrder;
						$detail->save();
					}
					else
					{
						$errors++;
					}
				}
			}

			if ($errors > 0) 
			{
				$alert = "swal('Orden de Trabajo Actualizada Con Errores','".$errors." registros del archivo CSV no se guardaron debido a que los campos estaban vacios', 'info');";
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			}

			return redirect()->route('work_order.edit',['id'=>$t_request->folio])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function update(Request $request,$id)
	{
		if (Auth::user()->module->where('id',260)) 
		{
			$t_request              = App\RequestModel::find($id);
			$t_request->idProject   = $request->project_id;
			$t_request->status      = 2;
			$t_request->save();

			$workOrder                 = App\WorkOrder::find($t_request->workOrder->id);
			$workOrder->title          = $request->title;
			$workOrder->number         = $request->number;
			$workOrder->date_obra      = $request->date_obra;
			$workOrder->idFolio        = $t_request->folio;
			$workOrder->urgent         = $request->urgent;
			
			$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
			if(!$applicant)
			{
				$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
			} 

			$workOrder->applicant = $applicant->id;
			$workOrder->save();

			$idWorkOrder = $workOrder->id;

			if (isset($request->delete) && count($request->delete)>0) 
			{
				App\WorkOrderDetail::whereIn('id',$request->delete)->delete();
			}

			if (isset($request->quantity) && count($request->quantity)>0) 
			{
				for ($i=0; $i < count($request->quantity); $i++) 	
				{
					$detail              = new App\WorkOrderDetail();
					$detail->part        = $request->part[$i];
					$detail->quantity    = $request->quantity[$i];
					$detail->unit        = $request->unit[$i];
					$detail->description = $request->description[$i];
					$detail->idWorkOrder = $idWorkOrder;
					$detail->save();
				}
			}

			if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
			{
				for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
				{
					if ($request->realPathWorkOrder[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
						$documents					= new App\WorkOrderDocuments();
						$documents->name			= $request->nameDocumentWorkOrder[$i];
						$documents->path			= $new_file_name;
						$documents->idWorkOrder	= $idWorkOrder;
						$documents->user_id			= Auth::user()->id;
						$documents->save();
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
			return redirect()->route('work_order.search')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',261)->count()>0) 
		{
			$data            = App\Module::find($this->module_id);
			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request;
			$maxdate_request = $request->maxdate_request;
			$mindate_obra    = $request->mindate_obra;
			$maxdate_obra    = $request->maxdate_obra;
			$status          = $request->status;
			$folio           = $request->folio;
			$applicant    = $request->applicant;
			$project_request = $request->project_request;
			$number          = $request->number;
			$requests = App\RequestModel::where('request_models.kind',22)
						
						->where(function ($query)
						{
							if (Auth::user()->id != 43) 
							{
								$query->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
							}
						})
						->where(function ($query) use ($title_request, $applicant, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number)
						{
							if ($applicant != "") 
							{
								$query->whereHas('workOrder',function($q) use($applicant){
									$q->whereIn('applicant',[$applicant]);
								});
							}
							if($title_request != "")
							{
								$query->whereHas('workOrder',function($q) use($title_request){
									$q->where('title','LIKE','%'.preg_replace("/\s+/", "%", $title_request).'%');
								});
							}
							if ($mindate_request != "") 
							{
								$query->whereHas('workOrder',function($q) use($mindate_request,$maxdate_request){
									$q->whereBetween('elaborate_date',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
								});
							}
							if ($mindate_obra != "") 
							{
								$query->whereHas('workOrder',function($q) use($mindate_obra,$maxdate_obra){
									$q->whereBetween('date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
								});
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
								$query->whereHas('workOrder',function($q) use($number){
									$q->where('number','LIKE','%'.$number.'%');
								});
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->paginate(10);

			return view('administracion.orden_trabajo.busqueda',
			[
				'id'              => $data['father'],
				'title'           => $data['name'],
				'details'         => $data['details'],
				'child_id'        => $this->module_id,
				'option_id'       => 261,
				'requests'        => $requests,
				'mindate_obra'    => $mindate_obra,
				'maxdate_obra'    => $maxdate_obra,
				'mindate_request' => $mindate_request,
				'maxdate_request' => $maxdate_request,
				'folio'           => $folio,
				'status'          => $status,
				'title_request'   => $title_request,
				'applicant'       => $applicant,
				'project_request' => $project_request,
				'number'          => $number,
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function edit($id)
	{
		if (Auth::user()->module->where('id',260)->count()>0) 
		{
			$request = App\RequestModel::find($id);

			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.orden_trabajo.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 260,
					'request'	=> $request
				]);
			}
			else
			{
				return abort(404);
			}
		}
	}
	
	public function uploadDetails(Request $request)
	{
		if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid())
		{
			try
			{
				$name		= '/massive_work_order/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
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
			}
			catch(\Exception $e)
			{
				$alert = "swal('','".Lang::get("messages.extension_allowed",["param"=>'CSV'])."', 'error')";
				return redirect()->back()->withAlert($alert);
			}
			array_shift($csvArr);

			$multiple = 0;
			foreach ($csvArr as $art) 
			{
				if (isset($art['orden_trabajo']) && trim($art['orden_trabajo'])!="")
				{
					$multiple = 1;
					break;
				}
			}
			
			if ($multiple == 1) 
			{
				$workOrders = collect($csvArr)->groupBy('orden_trabajo');
				$totalWorkOrders = $errorWorkOrders = 0;
				foreach ($workOrders as $workOrder) 
				{
					$continue = false;
					foreach ($workOrder as $art) 
					{
						if (isset($art['orden_trabajo']) && trim($art['orden_trabajo'])!="")
						{
							$continue = true;
							break;
						}
						else
						{
							$errorWorkOrders ++;
						}
					}
					if ($continue) 
					{
						$t_request							= new App\RequestModel();
						$t_request->fDate					= Carbon::now();
						$t_request->idElaborate				= Auth::user()->id;
						$t_request->idRequest				= Auth::user()->id;
						$t_request->idProject				= $request->project_id;
						$t_request->status					= 2;
						$t_request->kind					= 22;
						$t_request->save();

						$wO                 = new App\WorkOrder();
						$wO->title          = $request->title;
						$wO->elaborate_date = Carbon::now();
						$wO->number         = $request->number;
						$wO->date_obra      = $request->date_obra;
						$wO->idFolio        = $t_request->folio;
						$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
						if(!$applicant)
						{
							$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
						} 
						$wO->applicant = $applicant->name;
						$wO->save();
						$idWorkOrder = $wO->id;

						if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
						{
							for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
							{
								if ($request->realPathWorkOrder[$i] != "") 
								{
									$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
									$documents					= new App\WorkOrderDocuments();
									$documents->name			= $request->nameDocumentWorkOrder[$i];
									$documents->path			= $new_file_name;
									$documents->idWorkOrder	= $idWorkOrder;
									$documents->user_id			= Auth::user()->id;
									$documents->save();
								}
							}
						}
						$errors = 0;
						foreach ($workOrder as $art) 
						{
							if (
									(isset($art['orden_trabajo']) && trim($art['orden_trabajo'])!="") &&
									(isset($art['part']) && trim($art['part'])!="") &&
									(isset($art['cantidad']) && trim($art['cantidad'])>0) &&
									(isset($art['unidad']) && trim($art['unidad'])!="") &&
									(isset($art['descripcion']) && trim($art['descripcion'])!="") )
							{
								$detail              = new App\WorkOrderDetail();
								$detail->part        = $art["part"];
								$detail->quantity    = $art["cantidad"];
								$detail->unit        = $art["unidad"];
								$detail->description = $art["descripcion"];
								$detail->idWorkOrder = $idWorkOrder;
								$detail->save();
							}
							else
							{
								$errors++;
							}
						}
						$totalWorkOrders ++;
					}
				}
				if ($errorWorkOrders > 0) 
				{
					$alert = "swal('','Se han generado ".$totalWorkOrders." orden(es) de trabajo exitosamente, pero se encontraron registros sin asignación de orden de trabajo, por favor revise su archivo previamente cargado.', 'info');";
				}
				else
				{
					$alert = "swal('','Se han generado ".$totalWorkOrders." ordenes de trabajo exitosamente', 'success');";
				}
				return redirect()->route('work_order.search')->with('alert',$alert);
			}
			else
			{
				$t_request							= new App\RequestModel();
				$t_request->fDate					= Carbon::now();
				$t_request->idElaborate				= Auth::user()->id;
				$t_request->idRequest				= Auth::user()->id;
				$t_request->idProject				= $request->project_id;
				$t_request->status					= 2;
				$t_request->kind					= 22;
				$t_request->save();

				$wO                 = new App\WorkOrder();
				$wO->title          = $request->title;
				$wO->elaborate_date = Carbon::now();
				$wO->number         = $request->number;
				$wO->date_obra      = $request->date_obra;
				$wO->idFolio        = $t_request->folio;
				$applicant = App\CatRequestRequisition::where('name',$request->applicant)->first();
				if(!$applicant)
				{
					$applicant = App\CatRequestRequisition::create(['name' => $request->applicant]);
				} 
				$wO->applicant = $applicant->name;
				$wO->save();
				$idWorkOrder = $wO->id;
				if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
				{
					for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
					{
						if ($request->realPathWorkOrder[$i] != "") 
						{
							$new_file_name				= Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
							$documents					= new App\WorkOrderDocuments();
							$documents->name			= $request->nameDocumentWorkOrder[$i];
							$documents->path			= $new_file_name;
							$documents->idWorkOrder	= $idWorkOrder;
							$documents->user_id			= Auth::user()->id;
							$documents->save();
						}
					}
				}
				$errors = 0;
				foreach ($csvArr as $art) 
				{
					if (
						(isset($art['part']) && trim($art['part'])!="") &&
						(isset($art['cantidad']) && trim($art['cantidad'])>0) &&
						(isset($art['unidad']) && trim($art['unidad'])!="") &&
						(isset($art['descripcion']) && trim($art['descripcion'])!=""))
					{
						$detail              = new App\WorkOrderDetail();
						$detail->part        = $art["part"];
						$detail->quantity    = $art["cantidad"];
						$detail->unit        = $art["unidad"];
						$detail->description = $art["descripcion"];
						$detail->idWorkOrder = $idWorkOrder;
						$detail->save();
					}
					else
					{
						$errors++;
					}
				}
				if ($errors > 0) 
				{
					$alert = "swal('Orden de Trabajo Guardada Con Errores','".$errors." registros del archivo CSV no se guardaron debido a que los campos estaban vacios', 'info');";
				}
				else
				{
					$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
				}
				return redirect()->route('work_order.edit',['id'=>$t_request->folio])->with('alert',$alert);
			}
		}
		else
		{
			$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'error')";
			return back()->with('alert',$alert);
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
					\Storage::disk('public')->delete('/docs/work_order/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_workOrderDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/work_order/'.$name;
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

	public function uploadDocuments(Request $request, $id)
	{
		$t_request     = App\RequestModel::find($id);
		$idWorkOrder = $t_request->workOrder->id;

		if (isset($request->realPathWorkOrder) && count($request->realPathWorkOrder)>0) 
		{
			for ($i=0; $i < count($request->realPathWorkOrder); $i++) 
			{
				if ($request->realPathWorkOrder[$i] != "") 
				{
					$new_file_name          = Files::rename($request->realPathWorkOrder[$i],$t_request->folio);
					$documents              = new App\WorkOrderDocuments();
					$documents->name        = $request->nameDocumentWorkOrder[$i];
					$documents->path        = $new_file_name;
					$documents->idWorkOrder = $idWorkOrder;
					$documents->user_id     = Auth::user()->id;
					$documents->save();
				}
			}
		}
		$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
		return redirect()->route('work_order.edit',['id'=>$id])->with('alert',$alert);
	}
}
