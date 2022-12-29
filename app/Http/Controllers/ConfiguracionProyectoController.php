<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Auth;
use App;
use Alert;
use Excel;
use Lang;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionProyectoController extends Controller
{
	private $module_id = 56;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
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
		if(Auth::user()->module->where('id',57)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.proyecto.alta',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details' 	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 57
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validateProject(Request $request)
	{
		if ($request->ajax())
		{
			if(isset($request->idproyect))
			{
				$projecNumber = App\Project::where('proyectNumber',$request->project)
				->where('status',1)
				->where('idproyect','!=',$request->idproyect)
				->whereNotNull('proyectNumber')
				->get();
			}
			else
			{
				$projecNumber = App\Project::where('proyectNumber',$request->project)
				->where('status',1)
				->whereNotNull('proyectNumber')
				->get();
			}
	
			if (count($projecNumber)>0)
			{
				return Response("true");
			}
		}
	}

	public function validation(Request $request)
	{
		if($request->projectNumber == '')
		{
			$response = array(
				'valid'     => false,
				'message'   => 'Este campo es obligatorio'
			);
		}
		else
		{	
			if(isset($request->oldNumber) && $request->oldNumber == $request->projectNumber)
			{
				$response = array('valid' => true);
			}
			else
			{
				$number = App\Project::where('proyectNumber',$request->projectNumber)
								->where('status',1)
								->get();
				if(count($number)>0)
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Ya existe este número de proyecto.'
					);
				}
				else
				{
					$response = array('valid' => true);
				}
			}
		}
		return Response($response);
	}

	public function subValidation(Request $request)
	{
		if($request->form_projectNumber == '')
		{
			$response = array(
				'valid'     => false,
				'message'   => 'Este campo es obligatorio'
			);
		}
		else
		{	
			if(isset($request->oldNumber) && $request->oldNumber == $request->form_projectNumber)
			{
				$response = array('valid' => true);
			}
			else
			{
				$number = App\Project::where('proyectNumber',$request->form_projectNumber)
								->where('status',1)
								->get();
				if(count($number)>0)
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Ya existe este número de proyecto.'
					);
				}
				else
				{
					$response = array('valid' => true);
				}
			}
		}
		return Response($response);
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                   = App\Module::find($this->module_id);
			$project                = new App\Project();
			$project->proyectNumber = $request->projectNumber;
			$project->proyectName   = $request->projectName;
			$project->projectCode   = $request->projectCode;
			$project->description   = $request->description;
			$project->place         = $request->place;
			$project->kindOfProyect = $request->kindProject;
			$project->obra          = $request->obra;
			$project->placeObra     = $request->placeObra;
			$project->city          = $request->city;
			if($request->startObra != "")
			{
				$old_date           = new \DateTime($request->startObra);
				$new_date           = $old_date->format('Y-m-d');
				$project->startObra = $new_date;
			}
			if($request->endObra != "")
			{
				$old_date         = new \DateTime($request->endObra);
				$new_date         = $old_date->format('Y-m-d');
				$project->endObra = $new_date;
			}
			$project->client      = $request->client;
			$project->contestNo   = $request->contestNo;
			$project->status      = $request->status;
			$project->father      = null;
			$project->requisition = $request->requisition;
			$project->type        = 1;
			if($request->latitude != "" && $request->longitude != "")
			{
				$project->latitude  = $request->latitude;
				$project->longitude = $request->longitude;
				$project->distance  = $request->distance;
			}
			$project->save();
			$idFather = $project->idproyect;
			if (isset($request->idSubProject) && count($request->idSubProject)>0) 
			{
				for ($i=0; $i < count($request->idSubProject); $i++) 
				{ 
					$subProject         = App\Project::find($request->idSubProject[$i]);
					$subProject->father = $idFather;
					$subProject->type   = 2;
					$subProject->save();
				}
			}

			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/project')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',58)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$project    = App\Project::find($id);
			if ($project != "")
			{	
				return view('configuracion.proyecto.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 58,
						'project'	=> $project
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$checkProjects	= App\Project::where('proyectNumber',$request->projectNumber)->where('status',1)->where('idproyect','!=',$id)->count();

			if ($checkProjects > 0) 
			{
				$alert = "swal('', 'El proyecto no puede ser reactivado debido a que ya existe otro proyecto activo con el mismo número.', 'error');";
				return back()->with('alert',$alert);
			}

			$data                   = App\Module::find($this->module_id);
			$project                = App\Project::find($id);
			$project->proyectNumber = $request->projectNumber;
			$project->proyectName   = $request->projectName;
			$project->projectCode   = $request->projectCode;
			$project->description   = $request->description;
			$project->place         = $request->place;
			$project->kindOfProyect = $request->kindProject;
			$project->obra          = $request->obra;
			$project->placeObra     = $request->placeObra;
			$project->city          = $request->city;
			if($request->startObra != "")
			{
				$old_date           = new \DateTime($request->startObra);
				$new_date           = $old_date->format('Y-m-d');
				$project->startObra = $new_date;
			}
			if($request->endObra != "")
			{
				$old_date         = new \DateTime($request->endObra);
				$new_date         = $old_date->format('Y-m-d');
				$project->endObra = $new_date;
			}
			$project->client      = $request->client;
			$project->contestNo   = $request->contestNo;
			$project->status      = $request->status;
			$project->requisition = $request->requisition;
			if($request->latitude != "" && $request->longitude != "")
			{
				$project->latitude  = $request->latitude;
				$project->longitude = $request->longitude;
				$project->distance  = $request->distance;
			}
			else
			{
				$project->latitude  = null;
				$project->longitude = null;
				$project->distance  = null;
			}
			$project->save();
			$idFather = $project->idproyect;

			if (isset($request->idSubProject) && count($request->idSubProject)>0) 
			{
				for ($i=0; $i < count($request->idSubProject); $i++) 
				{ 
					$subProject 		= App\Project::find($request->idSubProject[$i]);
					$subProject->father = $idFather;
					$subProject->type 	= 2;
					$subProject->save();
				}
			}

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect('configuration/project/'.$id.'/edit')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data            = App\Module::find($this->module_id);
			$project         = App\Project::find($id);
			$project->status = 4;
			$project->save();
			$alert = "swal('','Proyecto concluido satisfactoriamente','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function repair($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$project			= App\Project::find($id);
			$project->status	= 1;
			$project->save();
			$alert = "swal('','Proyecto reactivado satisfactoriamente','success');";
			return redirect('/configuration/project/search')->with('alert',$alert);
		}
		else
		{
			return 0;
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',58)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$name	= $request->name;
			$number	= $request->number;
			$code	= $request->code;
			$status	= $request->status;

			$projects	= App\Project::where(function($query) use ($name,$number,$code,$status)
						{
							if ($name != "") 
							{
								$query->where('proyectName','LIKE','%'.$name.'%');
							}
							if ($number != "") 
							{
								$query->where('proyectNumber','LIKE','%'.$number.'%');
							}
							if ($code != "")
							{
								$query->where('projectCode','LIKE','%'.$code.'%');
							}
							if ($status != "") 
							{
								$query->where('status','LIKE','%'.$status.'%');
							}
						})
						->orderBy('idproyect', 'desc')
						->paginate(15);

			return response(
				view('configuracion.proyecto.busqueda',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 58,
					'projects'  => $projects,
					'name'		=> $name,
					'number'	=> $number,
					'code'		=> $code,
					'status'	=> $status,
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(58), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',58)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$name	= $request->name;
			$number	= $request->number;
			$code	= $request->code;
			$status	= $request->status;

			$projects	= DB::table('projects')->selectRaw(
						'
							projects.idproyect,
							projects.proyectNumber,
							projects.proyectName,
							projects.projectCode,
							CASE
								WHEN (projects.status = 1) THEN "Activo"
								WHEN (projects.status = 2) THEN "Pospuesto"
								WHEN (projects.status = 3) THEN "Cancelado"
								WHEN (projects.status = 4) THEN "Finalizado"
								ELSE ""
							END as projectStatus
							
						')
						->where(function($query) use ($name,$number,$code,$status)
						{
							if ($name != "") 
							{
								$query->where('projects.proyectName','LIKE','%'.$name.'%');
							}
							if ($number != "") 
							{
								$query->where('projects.proyectNumber','LIKE','%'.$number.'%');
							}
							if ($code != "")
							{
								$query->where('projects.projectCode','LIKE','%'.$code.'%');
							}
							if ($status != "") 
							{
								$query->where('projects.status','LIKE','%'.$status.'%');
							}
						})
						->get();

			if(count($projects)==0 || is_null($projects))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Lista de proyectos.xlsx');
			$writer->getCurrentSheet()->setName('Proyectos');

			$headers = ['Proyectos','', '', '', ''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$rowDark);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Número de Proyecto', 'Nombre', 'Código', 'Estado'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($projects as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();			
		}
		else
		{
			return redirect('error');
		}
	}

	public function storeSubProject(Request $request)
	{
		if ($request->ajax()) 
		{
			$project				= new App\Project();
			$project->proyectNumber	= $request->form_projectNumber;
			$project->proyectName	= $request->form_projectName;
			$project->projectCode	= $request->form_projectCode;
			$project->description	= $request->form_description;
			$project->place			= $request->form_place;
			$project->kindOfProyect	= $request->form_kindProject;
			
			$project->obra			= $request->form_obra;
			$project->placeObra		= $request->form_placeObra;
			$project->city			= $request->form_city;

			if($request->form_startObra != "")
			{
				$old_date			= new \DateTime($request->form_startObra);
				$new_date			= $old_date->format('Y-m-d');
				
				$project->startObra	= $new_date;
			}
			if($request->form_endObra != "")
			{
				$old_date			= new \DateTime($request->form_endObra);
				$new_date			= $old_date->format('Y-m-d');
				$project->endObra	= $new_date;
			}

			$project->client		= $request->form_client;
			$project->contestNo		= $request->form_contestNo;
			$project->father		= null;
			$project->requisition	= $request->form_requisition;
			$project->status		= $request->form_status;
			$project->type 			= 2;
			$project->save();

			$data			= [];
			$data['id']		= $project->idproyect;
			$data['clave']	= $project->projectCode;
			$data['nombre']	= $project->proyectName;

			return Response($data);
		}
	}

	public function deleteSubProject(Request $request)
	{
		if($request->ajax())
		{
			$project = App\Project::find($request->id);
			$project->delete();

			return Response('si');
		}
	}
}
