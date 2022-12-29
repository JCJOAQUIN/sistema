<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class ReportAdministrationStationeryController extends Controller
{
	private $module_id = 96;
	public function stationeryReport(Request $request)
	{
		if (Auth::user()->module->where('id',134)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$enterprise	= $request->idEnterprise;
			$direction	= $request->idArea;
			$department	= $request->idDepartment;
			$name		= $request->name;
			$status		= $request->status;
			$folio		= $request->folio;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

			$searchUser = App\User::select('users.id')
						->where(DB::raw("CONCAT_WS(' ',users.name, users.last_name, users.scnd_last_name)"),'LIKE','%'.$name.'%')
						->get();

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = new \DateTime($mindate);
				$endRange   = new \DateTime($maxdate);

				if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate!="" && $maxdate!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}           

			$requests = App\RequestModel::where('kind',7)
						->whereIn('status',[3,5,9])
						->whereIn('idEnterprise',Auth::user()->inChargeEnt(134)->pluck('enterprise_id'))
						->whereIn('idDepartment',Auth::user()->inChargeDep(134)->pluck('departament_id'))
						->where(function ($query) use ($status, $name, $mindate, $maxdate, $searchUser,$direction,$enterprise,$department,$folio)
						{
							if ($folio != "") 
							{
								$query->where('folio', $folio);
							}
							if ($enterprise != "") 
							{
								$query->whereIn('request_models.idEnterprise', $enterprise);
							}
							if ($direction != "") 
							{
								$query->whereIn('request_models.idArea', $direction);
							}
							if ($department != "") 
							{
								$query->whereIn('request_models.idDepartment', $department);
							}
							if ($name != "") 
							{
								$query->whereIn('request_models.idRequest', $searchUser);
							}
							if ($mindate != "" && $maxdate != "") 
							{
								$query->whereBetween('fDate', ['' . $mindate . ' ' . date('00:00:00') . '', '' . $maxdate . ' ' . date('23:59:59') . '']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status', $status);
							}
						})
						->orderBy('fDate', 'DESC')
						->orderBy('folio', 'DESC')
						->paginate(10);

				// return $requests;
				return view('reporte.administracion.papeleria',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 134,
					'idEnterprise'  => $enterprise,
					'idArea'        => $direction,
					'idDepartment'  => $department,
					'name'          => $name,
					'status'        => $status,
					'folio'         => $folio,
					'requests'      => $requests,
					'mindate'       => $request->mindate,
					'maxdate'       => $request->maxdate
				]);
		}   
		else
		{
			return redirect('/');
		}

	}

	public function stationeryExcel(Request $request)
	{

		if (Auth::user()->module->where('id',134)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$enterprise	= $request->idEnterprise;
			$direction	= $request->idArea;
			$department	= $request->idDepartment;
			$name		= $request->name;
			$status		= $request->status;
			$folio		= $request->folio;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

			$searchUser = App\User::select('users.id')
						->where(DB::raw("CONCAT_WS(' ',users.name, users.last_name, users.scnd_last_name)"),'LIKE','%'.$name.'%')
						->get();

			$requests   = DB::table('request_models')
						->selectRaw('
							request_models.folio as folio,
							status_requests.description as statusRequest,
							stationeries.title as title,
							stationeries.datetitle as datetitle,
							IF(request_models.idRequest = NULL,"Sin Solicitante",CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name)) as requestUser,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborateUser,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborateDate,
							review_enterprise.name as reviewEnterprise,
							CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
							detail_stationeries.quantity as quantity,
							detail_stationeries.product as product,
							detail_stationeries.short_code as short_code,
							detail_stationeries.commentaries as commentaries,
							cat_warehouse_concepts.description as productDelivery
						')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('stationeries','stationeries.idFolio','request_models.folio')
						->leftJoin('detail_stationeries','detail_stationeries.idStat','stationeries.idStationery')
						->leftJoin('users as request_user','request_models.idRequest','request_user.id')
						->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
						->leftJoin('enterprises as review_enterprise','request_models.idEnterpriseR','review_enterprise.id')
						->leftJoin('accounts as review_account','request_models.accountR','review_account.idAccAcc')
						->leftJoin('warehouses','warehouses.idwarehouse','detail_stationeries.idwarehouse')
						->leftJoin('cat_warehouse_concepts','cat_warehouse_concepts.id','warehouses.concept')
						->where('request_models.kind',7)
						->whereIn('request_models.status',[3,5,9])
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(134)->pluck('enterprise_id'))
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(134)->pluck('departament_id'))
						->where(function ($query) use ($status, $name, $mindate, $maxdate, $searchUser,$direction,$enterprise,$department,$folio)
						{

							if ($folio != "") 
							{
								$query->where('request_models.folio', $folio);
							}
							if ($enterprise != "") 
							{
								$query->whereIn('request_models.idEnterprise', $enterprise);
							}
							if ($direction != "") 
							{
								$query->whereIn('request_models.idArea', $direction);
							}
							if ($department != "") 
							{
								$query->whereIn('request_models.idDepartment', $department);
							}
							if ($name != "") 
							{
								$query->whereIn('request_models.idRequest', $searchUser);
							}
							if ($mindate != "" && $maxdate != "") 
							{
								$query->whereBetween('request_models.fDate', ['' . $mindate . ' ' . date('00:00:00') . '', '' . $maxdate . ' ' . date('23:59:59') . '']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status', $status);
							}
						})
						->orderBy('request_models.fDate', 'DESC')
						->orderBy('request_models.folio', 'DESC')
						->get();

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de papelería.xlsx');

			$headers        = ['Reporte de papelería','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Empresa','Fecha de elaboración','Clasificación del gasto','Cantidad','Producto','SKU','Comentarios','Producto Entregado'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio  = $request->folio;
					$kindRow    = !$kindRow;
				}
				else
				{
					$request->folio             = null;
					$request->statusRequest     = '';
					$request->title             = '';
					$request->datetitle         = '';
					$request->requestUser       = '';
					$request->elaborateUser     = '';
					$request->elaborateDate     = '';
					$request->reviewEnterprise  = '';
					$request->review_account    = '';
				}

				$row = [];
				foreach($request as $key => $value)
				{
					if(in_array($key,['quantity']))
					{
						$row[] = WriterEntityFactory::createCell($value);
					}
					else
					{
						$row[] = WriterEntityFactory::createCell($value);
					}
				}

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($row,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($row);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		} 
		else 
		{
			return redirect('/');
		}

	}

	public function stationeryDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.modal_stationery')->with('request',$request);
		}
	}
}
