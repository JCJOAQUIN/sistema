<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Excel;
use Lang;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionDepartamentoController extends Controller
{
	private $module_id = 19;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
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
		if(Auth::user()->module->where('id',22)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.departamento.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id' => 22
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if(isset($request->oldName) && $request->oldName===$request->name)
		{
			$response = array('valid' => true);
		}
		else if($request->name == "")
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio.'
			);
		}
		else
		{
			$exist = App\Department::where('name',$request->name)->where('status','ACTIVE')
			->get();
			if(count($exist)>0)
			{
				$response = array(
					'valid'		=> false,
					'message'	=> 'El departamento ya existe.'
				);
			}
			else
			{
				$response = array('valid' => true);
			}
		}
		return Response($response);
	}
	
	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$count  		= count($request->name);
			for ($i=0; $i < $count; $i++) 
			{ 
				$department				= new App\Department();
				$department->name		= $request->name[$i];
				$department->details	= $request->details[$i];
				$department->save();	
			}
			$alert 	= "swal('','".Lang::get("messages.record_created")."','success');";
			return redirect('configuration/department')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function edit($id)
	{
		if(Auth::user()->module->where('id',23)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$department	= App\Department::find($id);
			if ($department != "")
			{
				return view('configuracion.departamento.cambio',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 23,
						'department' 	=> $department
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
			$data  					= App\Module::find($this->module_id);
			$department  			= App\Department::find($id);
			$department->name 		= $request->name;
			$department->details 	= $request->details;
			$department->save();
					
			$alert = "swal('','".Lang::get("messages.record_updated")."','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function inactive($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$department = App\Department::find($id);
			$department->status = 'INACTIVE';
			$department->save();
			$alert	=	"swal('','Departamento suspendido exitosamente','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return 0;
		}
	}

	public function reactive($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data 				= App\Module::find($this->module_id);
			$department 		= App\Department::find($id);
			$department->status = 'ACTIVE';
			$department->save();
			$alert	=	"swal('','Departamento reactivado exitosamente','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return 0;
		}
	}

	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',23)->count()>0)
		{
			# code...
			$data			= App\Module::find($this->module_id);
			$name			= $request->search;
			$departments	= DB::table('departments')->selectRaw(
							'
								departments.id as departmentId,
								departments.name as departmentName,
								IF(departments.status="ACTIVE", "Activo", "Inactivo") as departmentStatus
							')
							->where('departments.name','LIKE','%'.$name.'%')
							->get();
			if(count($departments)==0 || $departments==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de departamentos.xlsx');
			$writer->getCurrentSheet()->setName('Departamentos');

			$headers = ['Reporte de Departamentos','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Nombre', 'Estado'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($departments as $data)
			{
				$tmpArr = [];
				foreach($data as $k => $r)
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


	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',23)->count()>0)
		{
			$search      = $request->search;
			$departments = App\Department::where('name','LIKE','%'.$search.'%')
				->orderBy('created_at','DESC')
				->paginate(10);
			$countDepa		= count($departments);
			$data	= App\Module::find($this->module_id);
			return response(
				view('configuracion.departamento.busqueda',
				[
					'id'          => $data['father'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'child_id'    => $this->module_id,
					'option_id'   => 23,
					'departments' => $departments,
					'search'      => $search,
					'countDepa'   => $countDepa
				])
			)->cookie(
				"urlSearch", storeUrlCookie(23), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getData(Request $request)
	{
		if($request->ajax())
		{
			$output         = "";
			$header         = "";
			$footer         = "";
			$departments    = App\Department::where('name','LIKE','%'.$request->search.'%')
								->paginate(10);
			$countDepartments = count($departments);

			if($countDepartments >= 1)
			{
				$header = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
				$footer = "</tbody></table>";

				foreach ($departments as $department) 
				{
					$output.=   "<tr>".
								"<td>".$department->id."</td>".
								"<td>".$department->name."</td>".
								"<td><a title='Editar Departamento' href="."'".url::route('department.edit',$department->id)."'"."class='btn btn-green'><span class='icon-pencil'></span></a> ";
					if ($department->status == 'ACTIVE') 
					{
						$output .= "<a title='Suspender Departamento' href="."'".url::route('department.inactive',$department->id)."'"." class='department-delete btn btn-red'><span class='icon-bin'></span></a>";
					}
					if ($department->status == 'INACTIVE') 
					{
						$output .= "<a title='Reactivar Departamento' href="."'".url::route('department.reactive',$department->id)."'"." class='department-reactive btn btn-green'><span class='icon-checkmark'></span></a>";
					}
								
					$output.= 	"</td>".
								"</tr>";
				}
				return Response($header.$output.$footer);
			}
			else
			{
				$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
				return Response($notfound);
			}
		}
	}

}
