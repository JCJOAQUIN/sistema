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

class ConfiguracionEstadoSolicitudController extends Controller
{
	private $module_id = 43;
	public function index(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			return redirect()->route('status.search', ['request' => $request->search]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$search			= $request->search;
			$statusRequest 	= App\StatusRequest::where(function($sql) use ($search){
				if($search != '') $sql->where('description','LIKE','%'.$search.'%');
			})
			->orderBy('idrequestStatus', 'desc')
			->paginate(10);
			$data           = App\Module::find($this->module_id);
			return response(
				view('configuracion.solicitudestado.busqueda',
				[
					'id'			=> $data['father'],
					'title'  		=> $data['name'],
					'details' 		=> $data['details'],
					'child_id' 		=> $this->module_id,
					'statusRequest' => $statusRequest,
					'search' 		=> $search,
				])
			)
			->cookie(
                'urlSearch', storeUrlCookie(43), 2880
            );  
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		
		$data					= App\Module::find($this->module_id);
		$status					= new App\StatusRequest();
		$status->description	= $request->description;
		$status->save();
		
		$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
		return redirect('configuration/status')->with('alert',$alert);
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',43)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$status	= App\StatusRequest::find($id);
			if($status != "")
			{
				return view('configuracion.solicitudestado.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'status' 	=> $status
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
			$data					= App\Module::find($this->module_id);
			$status					= App\StatusRequest::find($id);			
			$status->description	= $request->description;
			$description			= App\StatusRequest::where("description", $request->description)->where("idrequestStatus", "!=", $id)->count();		
			if($description > 0)
			{
				$alert	= "swal('', 'Error, el nombre ingresado ya existe', 'error');";
				return redirect('configuration/status')->with('alert',$alert);
			}else
			{
				$status->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function export(Request $request){
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$search			= $request->search;
			$statusRequest 	= DB::table('status_requests')->selectRaw(
				'
					status_requests.idrequestStatus,
					status_requests.description
				')
				->where(function($sql) use ($search)
				{	
					if($search != '') 
						$sql->where('status_requests.description','LIKE','%'.$search.'%');	
				})
				->get();

			if(count($statusRequest)==0 || is_null($statusRequest))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Estados-Solicitud.xlsx');
			$writer->getCurrentSheet()->setName('Estados de Solicitud');

			$headers = ['ESTADOS DE SOLICITUD',''];
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

			foreach($statusRequest as $request)
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
