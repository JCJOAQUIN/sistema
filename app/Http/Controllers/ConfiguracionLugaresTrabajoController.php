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
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionLugaresTrabajoController extends Controller{
	private $module_id = 176;
	
	public function index(){
		if(Auth::user()->module->where('id',$this->module_id)->count()>0){
			$data   = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else{ return redirect('/'); }
	}

	public function create(){
		if(Auth::user()->module->where('id',177)->count()>0){
			$data	= App\Module::find($this->module_id);
			return view('configuracion.lugares.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 177,
				]);
		}
		else{
			return redirect('/');
		}
	}

	public function store(Request $request){
		if(Auth::user()->module->where('id',$this->module_id)->count()>0){
			for ($i=0; $i < count($request->places); $i++) { 
				$t_places			= new App\Place();
				$t_places->place	= $request->places[$i];
				$t_places->status	= 1;
				$t_places->save();
			}


			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/places')->with('alert',$alert);
		}else{
			return redirect('/');
		}
	}

	public function show($id, Request $request){
		if($id == 'edit'){
			if(Auth::user()->module->where('id',178)->count()>0){
				$search		= $request->search;
				$places		= App\Place::where('status',1)->where(function ($sql) use ($search) {
					if($search != '')
					$sql->where('place','LIKE','%'.$search.'%');
				})
				->orderBy('id', 'desc')
				->paginate(10);
				$data		= App\Module::find($this->module_id);

				return response(
					view('configuracion.lugares.busqueda',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 178,
						'places'	=> $places,
						'search'	=> $search,
					])
				)->cookie(
					'urlSearch', storeUrlCookie(178), 2880
				);	
			}else{
				return redirect('/');
			}
		}else{
			return abort(404);
		}
	}

	/*public function getPlaces(Request $request)
	{

		if($request->ajax())
		{
			$output 		= "";
			$header 		= "";
			$footer 		= "";
			$places  	= App\Place::where('status',1)->where('place','LIKE','%'.$request->search.'%')
								->get();
			$countUsers 	= count($places);
				if ($countUsers >= 1)
				{
					$header = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
					$footer = "</tbody></table>";
					foreach ($places as $place)
					{
						$output.=	"<tr>".
								 	"<td>".$place->id."</td>".
									"<td>".$place->place."</td>".
									"<td><a title='Editar Empresa' href="."'".url::route('places.edit',$place->id)."'"."class='btn btn-green'><span class='icon-pencil'></span></a>".
									"</td>".
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
	}*/

	public function edit($id){
		if(Auth::user()->module->where('id',178)->count()>0){
			$places	= App\Place::find($id);
			$data	= App\Module::find($this->module_id);
			return view('configuracion.lugares.cambio',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 178,
					'places'	=> $places
				]);
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
		
			$t_places			= App\Place::find($id);
			$t_places->place	= $request->places;
			$t_places->status	= 1;
			$t_places->save();

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
			
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
		
			$t_places			= App\Place::find($id);
			$t_places->status	= 0;
			$t_places->save();

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect('configuration/places')->with('alert',$alert);
			
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
    {
		if ($request->ajax()) 
		{
			if($request->places != '')
			{
				$exist = App\Place::where('place',$request->places)
					->where(function($q) use($request)
					{
						if(isset($request->oldPlace))
						{
							$q->where('id','!=',$request->oldPlace);
						}
					})
					->get();
				if(count($exist)>0)
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Ya existe este lugar de trabajo.'
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
					'valid'		=> false,
					'message'	=> 'Campo obligatorio.'
				);
			}
			return Response($response);
		}  
    }

	public function export(Request $request)
	{
		if(Auth::user()->module->where('id',178)->count()>0)
		{
			$search		= $request->search;
			$places		= DB::table('places')->selectRaw(
						'
							places.id,
							places.place
						')
						->where('places.status',1)
						->where(function ($sql) use ($search) 
						{	
							if($search != '')	
								$sql->where('places.place','LIKE','%'.$search.'%');	
						})
						->get();

			if(count($places)==0 || is_null($places))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Lugar-de-Trabajo.xlsx');
			$writer->getCurrentSheet()->setName('Lugares de trabajo');

			$headers = ['Lugares de trabajo',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$rowDark);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Descripción'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$rowDark);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($places as $request)
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
}
