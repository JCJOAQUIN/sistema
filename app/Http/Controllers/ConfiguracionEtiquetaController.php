<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Excel;
use Lang;
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionEtiquetaController extends Controller
{
	private $module_id = 38;
	public function index()
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'       => $data['father'],
					'title'    => $data['name'],
					'details'  => $data['details'],
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',39)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			return view('configuracion.etiqueta.alta',
				[
					'id'		=>$data['father'],
					'title'		=>$data['name'],
					'details'	=>$data['details'],
					'child_id'	=>$this->module_id,
					'option_id'	=>39
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function validation(Request $request)
	{
		if ($request->ajax()) 
		{
			if($request->description != '')
			{
				if(isset($request->oldLabel) && $request->oldLabel == $request->description)
				{
					$response = array('valid' => true);
				}
				else
				{
					$exist = App\Label::where('description',$request->description)->get();
					if(count($exist)>0)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'Esta etiqueta ya existe.'
						);
					}
					else
					{
						$response = array('valid' => true);
					}
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

	public function store(Request $request)
	{
		$data = App\Module::find($this->module_id);
		for ($i=0; $i < count($request->description); $i++) 
		{ 
			$label              = new App\Label();
			$label->description = $request->description[$i];
			$label->save();
		}

		$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
		return redirect('configuration/labels')->with('alert',$alert);
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',40)->count()>0)
		{
			$search = $request->search;
			$label  = App\Label::where(function($sql) use ($search)
				{
					if($search != '')
					{
						$sql->where('description','LIKE','%'.$search.'%');
					}
				})
				->orderBy('idlabels', 'desc')
				->paginate(10);
			$data = App\Module::find($this->module_id);
			
			return response(
				view('configuracion.etiqueta.busqueda',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 40,
					'search'    => $search,
					'label'     => $label,

				])
			)->cookie(
                'urlSearch', storeUrlCookie(40), 2880
            );	
		}
		else
		{
			return abort(404);
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',40)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			$label = App\Label::find($id);
			if ($label != "")
			{
				return view('configuracion.etiqueta.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 40,
						'label' 	=> $label
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
			$data				= App\Module::find($this->module_id);
			$label				= App\Label::find($id);
			$label->description	= $request->description;
			$label->save();

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function export(Request $request){
		if(Auth::user()->module->where('id',40)->count()>0)
		{
			$search			= $request->search;
			$label			= DB::table('labels')->selectRaw(
							'
								labels.idlabels,
								labels.description
							')
							->where(function($sql) use ($search)
							{	
								if($search != '') 
									$sql->where('labels.description','LIKE','%'.$search.'%');	
							})
							->get();

			if(count($label)==0 || is_null($label))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-Etiquetas.xlsx');
			$writer->getCurrentSheet()->setName('Etiquetas');

			$headers = ['ETIQUETAS',''];
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

			foreach($label as $request)
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

	public function destroy($id)
	{
		return redirect('/');
	}
}
