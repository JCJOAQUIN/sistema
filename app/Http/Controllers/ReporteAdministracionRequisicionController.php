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
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Carbon\Carbon;

class ReporteAdministracionRequisicionController extends Controller
{
	private $module_id = 96;

	public function requisition(Request $request)
	{
		if (Auth::user()->module->where('id', 236)->count() > 0) 
		{
			$data            = App\Module::find($this->module_id);
			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request;
			$maxdate_request = $request->maxdate_request;
			$mindate_obra    = $request->mindate_obra;
			$maxdate_obra    = $request->maxdate_obra;
			$status          = $request->status;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$category        = $request->category;
			$data				= App\Module::find($this->module_id);

			if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request=="") || ($mindate_request!="" && $maxdate_request!=""))
			{
				$mindate_request	= $request->mindate_request != "" ? Carbon::parse($request->mindate_request)->format('Y-m-d') : '';
				$maxdate_request	= $request->maxdate_request != "" ? Carbon::parse($request->maxdate_request)->format('Y-m-d') : '';

				if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_request!="" && $maxdate_request!="" && $maxdate_request < $mindate_request) 
				{
				    $alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra=="") || ($mindate_obra!="" && $maxdate_obra!=""))
			{
				$mindate_obra	= $request->mindate_obra != "" ? Carbon::parse($request->mindate_obra)->format('Y-m-d') : '';
				$maxdate_obra	= $request->maxdate_obra != "" ? Carbon::parse($request->maxdate_obra)->format('Y-m-d') : '';

				if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_obra!="" && $maxdate_obra!="" && $maxdate_obra < $mindate_obra) 
				{
				    $alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->where('status','!=',23)
				->where(function($query)
				{
					$query->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$category)
				{
					if ($category != "") 
					{
						$query->whereHas('requisition',function($q) use($category)
						{
							$q->whereHas('details',function($q2) use($category)
							{
								$q2->whereIn('category',$category);
							});
						});
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
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
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);
			$data = App\Module::find($this->module_id);
			return view(
				'reporte.administracion.requisition',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => $this->module_id,
					'option_id'       => 236,
					'requests'        => $requests,
					'mindate_obra'    => $mindate_obra,
					'maxdate_obra'    => $maxdate_obra,
					'mindate_request' => $mindate_request,
					'maxdate_request' => $maxdate_request,
					'folio'           => $folio,
					'status'          => $status,
					'title_request'   => $title_request,
					'user_request'    => $user_request,
					'project_request' => $project_request,
					'number'          => $number,
					'wbs'             => $wbs,
					'edt'             => $edt,
					'type'            => $type,
					'category'		  => $category
				]
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function requisitionExcel(Request $request)
	{
		if (Auth::user()->module->where('id', 236)->count() > 0)
		{
			$title_request   = $request->title_request;
			$mindate_request = $request->mindate_request;
			$maxdate_request = $request->maxdate_request;
			$mindate_obra    = $request->mindate_obra;
			$maxdate_obra    = $request->maxdate_obra;
			$status          = $request->status;
			$folio           = $request->folio;
			$user_request    = $request->user_request;
			$project_request = $request->project_request;
			$number          = $request->number;
			$wbs             = $request->wbs;
			$edt             = $request->edt;
			$type            = $request->type;
			$category        = $request->category;

			if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request=="") || ($mindate_request!="" && $maxdate_request!=""))
			{
				$mindate_request	= $request->mindate_request != "" ? Carbon::parse($request->mindate_request)->format('Y-m-d') : '';
				$maxdate_request	= $request->maxdate_request != "" ? Carbon::parse($request->maxdate_request)->format('Y-m-d') : '';

				if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_request!="" && $maxdate_request!="" && $maxdate_request < $mindate_request) 
				{
				    $alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra=="") || ($mindate_obra!="" && $maxdate_obra!=""))
			{
				$mindate_obra	= $request->mindate_obra != "" ? Carbon::parse($request->mindate_obra)->format('Y-m-d') : '';
				$maxdate_obra	= $request->maxdate_obra != "" ? Carbon::parse($request->maxdate_obra)->format('Y-m-d') : '';

				if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_obra!="" && $maxdate_obra!="" && $maxdate_obra < $mindate_obra) 
				{
				    $alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$requests = DB::table('request_models')
				->selectRaw('
					request_models.folio as folio,
					requisitions.title as title,
					CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
					projects.proyectName as proyectName,
					status_requests.description as status_requests,
					requisitions.date_request as date_request,
					requisition_details.code as code,
					requisition_details.quantity as quantity,
					requisition_details.measurement as measurement,
					requisition_details.unit as unit,
					requisition_details.description as description,
					cat_warehouse_types.description as categoryDescription,
					IF(request_models.status = 17,
						IF(purchases.idFolio IS NOT NULL AND refunds.idFolio IS NULL,
							IF(purchase_budget.status IS NOT NULL,IF(purchase_budget.status = 1, "Aprobado","Rechazado"),"Pendiente"),
								IF(purchases.idFolio IS NULL AND refunds.idFolio IS NOT NULL, 
									IF(refund_budget.status IS NOT NULL,IF(refund_budget.status = 1, "Aprobado","Rechazado"),"Pendiente"),"")
						), "Pendiente"
					) as statusBudget,
					IF(request_models.status = 17,
						IF(purchases.idFolio IS NOT NULL AND refunds.idFolio IS NULL,
							IF(requisitions.requisition_type = 1,IF(detail_purchases.statusWarehouse = 0, "Pendiente","SÍ"),"No Aplica"),
								IF(purchases.idFolio IS NULL AND refunds.idFolio IS NOT NULL, "No Aplica","No Aplica")
						), "Pendiente"
					) as warehouseStatus,
					IF(request_models.status = 17,
						IF(purchases.idFolio IS NOT NULL AND refunds.idFolio IS NULL,
							IF(requisitions.requisition_type = 1,IF(detail_stationeries.idwarehouse IS NOT NULL, "Entregado","Pendiente"),"Pendiente"),
								"No Aplica"
						), "Pendiente"
					) as stationeryStatus
				')
				->leftJoin('projects','projects.idproyect','request_models.idProject')
				->leftJoin('users','users.id','request_models.idRequest')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('requisitions', 'request_models.folio', 'requisitions.idFolio')
				->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
				->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
				->leftJoin('purchases','purchases.idRequisition','requisitions.idFolio')
				->leftJoin('detail_purchases','detail_purchases.idPurchase','purchases.idPurchase')
				->leftJoin('detail_stationeries','detail_stationeries.idDetailPurchase','detail_purchases.idDetailPurchase')
				->leftJoin('refunds','refunds.idRequisition','requisitions.idFolio')
				->leftJoin('budgets as purchase_budget','purchase_budget.request_id','purchases.idFolio')
				->leftJoin('budgets as refund_budget','refund_budget.request_id','refunds.idFolio')
				->whereIn('request_models.idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
				->where('request_models.kind', 19)
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$category)
				{
					if ($category != "") 
					{
						$query->whereIn('requisition_details.category',$category);
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
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
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Completo-Requisición.xlsx');

			$headers		= ['Datos requisición', '', '', '', '', '', 'Datos conceptos','','','', '', '', '', '', ''];
			$tempHeaders	= [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders		= ['Folio', 'Título', 'Solicitante', 'Proyecto', 'Estado', 'Fecha de elaboración','Código','Cantidad','Medida','Unidad', 'Nombre', 'Categoría', 'Presupuestos', 'Almacén', 'Entregado'];
			$tempSubHeader	= [];
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
					$tempFolio	= $request->folio;
					$kindRow	= !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->title				= '';
					$request->request_user		= '';
					$request->proyectName		= '';
					$request->status_requests	= '';
					$request->date_request		= '';
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
			return redirect('/error');
		}
	}
}
