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

class ReportAdministrationRefundController extends Controller
{
	private $module_id = 96;
	public function refundReport(Request $request)
	{
		if (Auth::user()->module->where('id',125)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$idEnterprise	= $request->idEnterprise;
			$idArea			= $request->idArea;
			$idDepartment	= $request->idDepartment;
			$name			= $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$min			= null;
			$max			= null;
			$status			= $request->status;
			$folio			= $request->folio;

			if($mindate=="" && $maxdate!="" || $mindate!="" && $maxdate=="")
			{
				$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
				return back()->with(['alert'=>$alert]);
			}

			$searchUser = App\User::select('users.id')
						->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
						->get();

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = $mindate;
				$endRange   = $maxdate;

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

			$requests       = App\RequestModel::where('kind',9)
							->whereIn('status',[4,5,10,11,12,18])
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(125)->pluck('enterprise_id'))
							->whereIn('idDepartment',Auth::user()->inChargeDep(125)->pluck('departament_id'))
							->where(function ($query) use ($name, $mindate, $maxdate,$searchUser,$idEnterprise,$idArea,$idDepartment,$status,$folio)
							{
								if ($folio != "") 
								{
									$query->where('folio',$folio);
								}
								if ($idEnterprise != "")
								{                               
									$query->whereIn('request_models.idEnterpriseR',$idEnterprise);
								}
								if ($idArea != "")
								{                           
									$query->whereIn('request_models.idAreaR',$idArea);
								}
								if ($idDepartment != "")
								{                               
									$query->whereIn('request_models.idDepartamentR',$idDepartment);
								}
								if($name != "")
								{
									$query->whereHas('requestUser', function($q) use($name)
									{
										$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
									});
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if ($status != "") 
								{
									$query->whereIn('status',$status);
								}
							})
							->orderBy('fDate','DESC')
							->orderBy('folio','DESC')
							->paginate(15);
			return view('reporte.administracion.reembolso',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 125,
					'requests'		=> $requests, 
					'name'			=> $name, 
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'folio'			=> $folio,
					'status'		=> $status,
					'idEnterprise'	=> $idEnterprise,
					'idArea'		=> $idArea,
					'idDepartment'	=> $idDepartment
					
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function refundExcel(Request $request)
	{
		if (Auth::user()->module->where('id',125)->count()>0)
		{
			$enterprise	= $request->idEnterprise;
			$direction	= $request->idArea;
			$department	= $request->idDepartment;
			$name		= $request->name;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$min		= null;
			$max		= null;
			$status		= $request->status;
			$folio		= $request->folio;

			$requests   = DB::table('request_models')
						->selectRaw('
							request_models.folio as folio,
							status_requests.description as statusRequest,
							CONCAT_WS(" - ",refunds.title,refunds.datetitle) as title,
							IF(request_models.idRequest = NULL,"Sin Solicitante",CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name)) as requestUser,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborateUser,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborateDate,
							request_enterprise.name as requestEnterprise,
							request_direction.name as requestDirection,
							request_department.name as requestDepartment,
							request_project.proyectName as requestProject,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as reviewUser,
							DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i") as reviewDate,
							review_enterprise.name as reviewEnterprise,
							review_direction.name as reviewDirection,
							review_department.name as reviewDepartment,
							review_project.proyectName as reviewProject,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorizeUser,
							DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i") as authorizeDate,
							refund_details.concept as concept,
							CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountName,
							IF(refund_details.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
							refund_details.amount as amount,
							refund_details.tax as tax,
							taxes_refunds.taxes_amount as taxesAmount,
							refund_retentions.retentions_amount as retentionsAmount,
							refund_details.sAmount as sAmount,
							detail_labels.labels as labels,
							refunds.total as totalRefund,
							refunds.currency as currency
						')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('refunds','refunds.idFolio','request_models.folio')
						->leftJoin('refund_details','refund_details.idRefund','refunds.idRefund')
						->leftJoin('users as request_user','request_models.idRequest','request_user.id')
						->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
						->leftJoin('enterprises as request_enterprise','request_models.idEnterprise','request_enterprise.id')
						->leftJoin('areas as request_direction','request_models.idArea','request_direction.id')
						->leftJoin('departments as request_department','request_models.idDepartment','request_department.id')
						->leftJoin('projects as request_project','request_models.idProject','request_project.idproyect')
						->leftJoin('users as review_user','request_models.idCheck','review_user.id')
						->leftJoin('enterprises as review_enterprise','request_models.idEnterpriseR','review_enterprise.id')
						->leftJoin('areas as review_direction','request_models.idAreaR','review_direction.id')
						->leftJoin('departments as review_department','request_models.idDepartamentR','review_department.id')
						->leftJoin('projects as review_project','request_models.idProjectR','review_project.idproyect')
						->leftJoin('accounts as ed_acc_r','refund_details.idAccountR','ed_acc_r.idAccAcc')
						->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as taxes_amount FROM taxes_refunds GROUP BY idRefundDetail) AS taxes_refunds'),'refund_details.idRefundDetail','taxes_refunds.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as retentions_amount FROM refund_retentions GROUP BY idRefundDetail) AS refund_retentions'),'refund_details.idRefundDetail','refund_retentions.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_refunds INNER JOIN labels ON label_detail_refunds.idlabels = labels.idlabels GROUP BY idRefundDetail) AS detail_labels'),'refund_details.idRefundDetail','detail_labels.idRefundDetail')
						->where('request_models.kind',9)
						->whereIn('request_models.status',[4,5,10,11,12,18])
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(125)->pluck('enterprise_id'))
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(125)->pluck('departament_id'))
						->where(function ($query) use ($name, $mindate, $maxdate,$enterprise,$direction,$department,$status,$folio)
						{
							
							if ($folio != "") 
							{
								$query->where('folio',$folio);
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterpriseR',$enterprise);
							}
							if ($direction != "")
							{                           
								$query->whereIn('request_models.idAreaR',$direction);
							}
							if ($department != "")
							{                               
								$query->whereIn('request_models.idDepartamentR',$department);
							}
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Reembolsos.xlsx');

			$headers        = ['Reporte de Reembolsos','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','Datos de revisión','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de Solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Autorizada por','Fecha de autorización','Concepto','Clasificación del gasto','Fiscal/No Fiscal','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a pagar','Moneda'];
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
					$request->requestUser       = '';
					$request->elaborateUser     = '';
					$request->elaborateDate     = '';
					$request->requestEnterprise = '';
					$request->requestDirection  = '';
					$request->requestDepartment = '';
					$request->requestProject    = '';
					$request->reviewUser        = '';
					$request->reviewDate        = '';
					$request->reviewEnterprise  = '';
					$request->reviewDirection   = '';
					$request->reviewDepartment  = '';
					$request->reviewProject     = '';
					$request->authorizeUser     = '';
					$request->authorizeDate     = '';
					$request->totalRefund       = '';
					$request->currency          = '';
				}
				$row = [];
				foreach($request as $key => $value)
				{
					if(in_array($key,['amount','tax','taxesAmount','retentionsAmount','sAmount','totalRefund']))
					{
						if($value != '')
						{
							$row[] = WriterEntityFactory::createCell((double)$value, $currencyFormat);
						}
						else
						{
							$row[] = WriterEntityFactory::createCell($value);
						}
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
			return redirect('error');
		}
	}

	public function refundExcelWithoutGrouping(Request $request)
	{
		if (Auth::user()->module->where('id',125)->count()>0)
		{
			$enterprise	= $request->idEnterprise;
			$direction	= $request->idArea;
			$department	= $request->idDepartment;
			$name		= $request->name;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$min		= null;
			$max		= null;
			$status		= $request->status;
			$folio		= $request->folio;          

			$requests   = DB::table('request_models')
						->selectRaw('
							request_models.folio as folio,
							status_requests.description as statusRequest,
							CONCAT_WS(" - ",refunds.title,refunds.datetitle) as title,
							IF(request_models.idRequest = NULL,"Sin Solicitante",CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name)) as requestUser,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborateUser,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborateDate,
							request_enterprise.name as requestEnterprise,
							request_direction.name as requestDirection,
							request_department.name as requestDepartment,
							request_project.proyectName as requestProject,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as reviewUser,
							DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i") as reviewDate,
							review_enterprise.name as reviewEnterprise,
							review_direction.name as reviewDirection,
							review_department.name as reviewDepartment,
							review_project.proyectName as reviewProject,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorizeUser,
							DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i") as authorizeDate,
							refund_details.concept as concept,
							CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountName,
							IF(refund_details.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
							refund_details.amount as amount,
							refund_details.tax as tax,
							taxes_refunds.taxes_amount as taxesAmount,
							refund_retentions.retentions_amount as retentionsAmount,
							refund_details.sAmount as sAmount,
							detail_labels.labels as labels,
							refunds.total as totalRefund,
							refunds.currency as currency
						')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('refunds','refunds.idFolio','request_models.folio')
						->leftJoin('refund_details','refund_details.idRefund','refunds.idRefund')
						->leftJoin('users as request_user','request_models.idRequest','request_user.id')
						->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
						->leftJoin('enterprises as request_enterprise','request_models.idEnterprise','request_enterprise.id')
						->leftJoin('areas as request_direction','request_models.idArea','request_direction.id')
						->leftJoin('departments as request_department','request_models.idDepartment','request_department.id')
						->leftJoin('projects as request_project','request_models.idProject','request_project.idproyect')
						->leftJoin('users as review_user','request_models.idCheck','review_user.id')
						->leftJoin('enterprises as review_enterprise','request_models.idEnterpriseR','review_enterprise.id')
						->leftJoin('areas as review_direction','request_models.idAreaR','review_direction.id')
						->leftJoin('departments as review_department','request_models.idDepartamentR','review_department.id')
						->leftJoin('projects as review_project','request_models.idProjectR','review_project.idproyect')
						->leftJoin('accounts as ed_acc_r','refund_details.idAccountR','ed_acc_r.idAccAcc')
						->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as taxes_amount FROM taxes_refunds GROUP BY idRefundDetail) AS taxes_refunds'),'refund_details.idRefundDetail','taxes_refunds.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as retentions_amount FROM refund_retentions GROUP BY idRefundDetail) AS refund_retentions'),'refund_details.idRefundDetail','refund_retentions.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_refunds INNER JOIN labels ON label_detail_refunds.idlabels = labels.idlabels GROUP BY idRefundDetail) AS detail_labels'),'refund_details.idRefundDetail','detail_labels.idRefundDetail')
						->where('request_models.kind',9)
						->whereIn('request_models.status',[4,5,10,11,12,18])
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(125)->pluck('enterprise_id'))
						->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(125)->pluck('departament_id'))
						->where(function ($query) use ($name, $mindate, $maxdate,$enterprise,$direction,$department,$status,$folio)
						{
							
							if ($folio != "") 
							{
								$query->where('folio',$folio);
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterpriseR',$enterprise);
							}
							if ($direction != "")
							{                           
								$query->whereIn('request_models.idAreaR',$direction);
							}
							if ($department != "")
							{                               
								$query->whereIn('request_models.idDepartamentR',$department);
							}
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Reembolsos.xlsx');

			$headers        = ['Reporte de Reembolsos','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','Datos de revisión','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de Solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Autorizada por','Fecha de autorización','Concepto','Clasificación del gasto','Fiscal/No Fiscal','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a pagar','Moneda'];
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

				$row = [];
				foreach($request as $key => $value)
				{
					if(in_array($key,['amount','tax','taxesAmount','retentionsAmount','sAmount','totalRefund']))
					{
						if($value != '')
						{
							$row[] = WriterEntityFactory::createCell((double)$value, $currencyFormat);
						}
						else
						{
							$row[] = WriterEntityFactory::createCell($value);
						}
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
			return redirect('error');
		}
	}

	public function refundDetail(Request $request)
	{
		if($request->ajax())
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.modal_reembolso')->with('request',$request);
		}
	}
}
