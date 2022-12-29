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
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionAreaController extends Controller
{
	private $module_id = 18;
	
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
		if(Auth::user()->module->where('id',20)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.area.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 20
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validationName(Request $request)
	{
		if (Auth::user()->module->where('id',20)->count() > 0)
		{
			if ($request->ajax())
			{
				$response = array(
					'valid'     => false,
					'message'   => 'Error.'
				);

				if ($request->name != "")
				{
					$exist = App\Area::where('name',$request->name)->where('status','ACTIVE')->count();
					if($exist > 0)
					{
						$response = array(
							'valid'     => false,
							'message'   => 'La dirección ya se encuentra registrada.'
						);						
					}
					else
					{
						$response = array('valid' => true);
					}
				}
				else
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Este campo es obligatorio.'
					);
				}
				return Response($response);
			}
		}
	}

	public function validation(Request $request)
	{
		if (Auth::user()->module->where('id',21)->count() > 0)
		{
			if ($request->ajax())
			{
				if ($request->name != "")
				{
					$exist = App\Area::where('name',$request->name)
						->where('status','ACTIVE')
						->where(function($q) use ($request)
						{
							if(isset($request->oldDirection))
							{
								$q->where('id', '!=', $request->oldDirection);
							}
						})
						->count();
					if($exist > 0)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'La dirección ya se encuentra registrada.'
						);
					}
					else
					{
						$response = array('valid' => true);
					}
				}
				else
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Este campo es obligatorio.'
					);
				}	
			}
		}
		return Response($response);
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\Area::where('name', $request->name)->count();
			if($validate > 0)
			{
				$alert = "swal('','La dirección fue previamente registrada, por favor ingrese una diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$area 				= new App\Area();
				$area->name 		= $request->name;
				$area->details 		= $request->detail;
				$area->responsable 	= $request->responsable;
				$area->save();
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			}
			return redirect('configuration/area')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function edit($id)
	{
		if(Auth::user()->module->where('id',21)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$area = App\Area::find($id);
			if ($area != "") 
			{
				return view('configuracion.area.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 21,
						'area' 		=> $area
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
			$validate = App\Area::where('name', $request->name)->where('id', '!=', $id)->count();
			if($validate > 0)
			{
				$alert = "swal('','La dirección fue previamente registrada, por favor ingrese una diferente.','error');";
			}
			else
			{
				$area 				 = App\Area::find($id);
				$area->name          = $request->name;
				$area->details       = $request->details;
				$area->responsable 	 = $request->responsable;
				$area->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			}
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$name			= $request->search;
			$areas	= DB::table('areas')->selectRaw(
					'
						areas.id areaId, 
						areas.name as areaName,
						areas.responsable areaResponsable,
						IF(areas.status="ACTIVE", "Activo", "Inactivo") as areaStatus
					')
					->where('areas.name','LIKE','%'.$name.'%')
					->orderBy('created_at','desc')
					->get();

			if(count($areas)==0 || $areas==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Direcciones.xlsx');
			$writer->getCurrentSheet()->setName('Direcciones');

			$headers = ['Direcciones','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Nombre','Responsable', 'Estado'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($areas as $data)
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

	public function inactive($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			$area 	= App\Area::find($id);
			if ($area->status != 'INACTIVE')
			{
				$area->status = 'INACTIVE';
				$area->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			}
			else
			{
				$alert = "swal('', 'Registro previamente suspendido.', 'error');";
			}
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function reactive($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data 			= App\Module::find($this->module_id);
			$area 			= App\Area::find($id);
			if ($area->status != 'ACTIVE')
			{
				$area->status = 'ACTIVE';
				$area->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			}
			else
			{
				$alert = "swal('', 'Registro previamente reactivado.', 'error');";
			}
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',21)->count()>0)
		{
			$search = $request->search;
			$areas	= App\Area::where('name','LIKE','%'.$search.'%')
				->orderBy('created_at','desc')
				->paginate(10);
			$countAreas	= count($areas);
			$data		= App\Module::find($this->module_id);

			return response(
				view('configuracion.area.busqueda',
				[
					'id'		=> $data['father'],
					'title' 	=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id'	=> 21,
					'areas'		=> $areas,
					'search'	=> $search,
					'countAreas'=> $countAreas 
				])
			)->cookie(
				"urlSearch", storeUrlCookie(21), 2880
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
			$output 		= "";
			$header 		= "";
			$footer 		= "";
			$areas  		= App\Area::where('name','LIKE','%'.$request->search.'%')
								->get();
			$countUsers 	= count($areas);
				if ($countUsers >= 1) 
				{
					$header = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
					$footer = "</tbody></table>";
					foreach ($areas as $area) {
						$output.="<tr>".
								 "<td>".$area->id."</td>".
								 "<td>".$area->name."</td>".
								 "<td><a title='Editar Dirección' href="."'".url::route('area.edit',$area->id)."'"."class='btn btn-green'><span class='icon-pencil'></span></a>";
						if ($area->status == 'ACTIVE') 
						{
							$output .= "<a title='Suspender Dirección' href="."'".url::route('area.inactive',$area->id)."'"." class='area-delete btn btn-red'><span class='icon-bin'></span></a>";
						}
						if ($area->status == 'INACTIVE') 
						{
							$output .= "<a title='Reactivar Dirección' href="."'".url::route('area.reactive',$area->id)."'"." class='area-reactive btn btn-green'><span class='icon-checkmark'></span></a>";
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
