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

class ReportAdministrationExpensesController extends Controller
{
	private $module_id = 96;
	public function expensesReport(Request $request)
	{
		if (Auth::user()->module->where('id',98)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$folio        = $request->folio;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$name         = $request->name;
			$idEnterprise = $request->idEnterprise;
			$idArea       = $request->idArea;
			$idDepartment = $request->idDepartment;
			$status       = $request->status;
			$initRange    = "";
			$endRange     = "";
			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
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
			$requests = App\RequestModel::where('kind',3)
				->whereIn('status',[4,5,10,11,12,18])
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(98)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(98)->pluck('departament_id'))
				->where(function ($query) use ($folio, $idEnterprise, $idArea, $idDepartment, $name, $mindate, $maxdate, $status,$initRange,$endRange)
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
						$query->whereHas('requestUser',function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
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
			return view('reporte.administracion.comprobaciongastos',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 98,
					'requests'     => $requests,
					'folio'        => $folio, 
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'name'         => $name,
					'idEnterprise' => $idEnterprise,
					'idArea'       => $idArea,
					'idDepartment' => $idDepartment,
					'status'       => $status,
					
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function expensesExcel(Request $request)
	{
		if (Auth::user()->module->where('id',98)->count()>0)
		{
			$folio      = $request->folio;
			$enterprise = $request->idEnterprise;
			$direction  = $request->idArea;
			$department = $request->idDepartment;
			$name       = $request->name;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status     = $request->status;
			$requests   = DB::table('request_models')
				->selectRaw('
					request_models.folio as folio,
					status_requests.description as statusRequest,
					expenses.title as title,
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
					expenses_details.concept as concept,
					CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountName,
					IF(expenses_details.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					expenses_details.amount as amount,
					expenses_details.tax as tax,
					taxes_expenses.taxes_amount as taxesAmount,
					expenses_details.sAmount as sAmount,
					de_labels.labels as labels,
					expenses.resourceId as resourceId,
					resources.total as resourceTotal,
					(expenses.total-resources.total) as difference,
					IF(request_models.taxPayment = 1 AND expenses.reembolso > 0, "Pagado",
						IF(request_models.taxPayment = 1 AND expenses.reembolso > 0, "No Pagado",
							IF(expenses.reembolso = 0, "No Aplica","No Aplica")
						)
					) as checkPayment,
					IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 1, "Comprobado",
						IF(request_models.payment = 0 AND expenses.reintegro > 0 AND request_models.free = 0, "No Comprobado",
							IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 0, "No Comprobado",
								IF(expenses.reintegro = 0, "No Aplica","No Aplica")
							)
						)
					) as checked
				')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('expenses','expenses.idFolio','request_models.folio')
				->leftJoin('expenses_details','expenses_details.idExpenses','expenses.idExpenses')
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
				->leftJoin('accounts as ed_acc_r','expenses_details.idAccountR','ed_acc_r.idAccAcc')
				->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as taxes_amount FROM taxes_expenses GROUP BY idExpensesDetail) AS taxes_expenses'),'expenses_details.idExpensesDetail','taxes_expenses.idExpensesDetail')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_expenses INNER JOIN labels ON label_detail_expenses.idlabels = labels.idlabels GROUP BY idExpensesDetail) AS de_labels'),'expenses_details.idExpensesDetail','de_labels.idExpensesDetail')
				->leftJoin('resources','expenses.resourceId','resources.idFolio')
				->where('request_models.kind',3)
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(98)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(98)->pluck('departament_id'))
				->where(function ($query) use ($name, $mindate, $maxdate,$enterprise,$direction,$department,$status,$folio)
				{
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Comprobación de Gastos.xlsx');
			$headers        = ['Reporte de Comprobación de Gastos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','Datos de revisión','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Autorizada por','Fecha de autorización','Concepto','Clasificación del gasto','Fiscal','Subtotal','IVA','Impuesto Adicional','Importe','Etiquetas','Folio de Solicitud de Recurso','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro'];
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
					$request->requestEnterprise = '';
					$request->requestDirection  = '';
					$request->requestDepartment = '';
					$request->requestProject    = '';
					$request->authorizeUser     = '';
					$request->authorizeDate     = '';
					$request->resourceId        = '';
					$request->resourceTotal     = '';
					$request->difference        = '';
					$request->checkPayment      = '';
					$request->checked           = '';
				}
				$row = [];
				foreach($request as $key => $value)
				{
					if(in_array($key,['amount','tax','taxesAmount','sAmount','resourceTotal','difference']))
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

	public function expensesExcelWithoutGrouping(Request $request)
	{
		if (Auth::user()->module->where('id',98)->count()>0)
		{
			$folio      = $request->folio;
			$enterprise = $request->idEnterprise;
			$direction  = $request->idArea;
			$department = $request->idDepartment;
			$name       = $request->name;
			$status     = $request->status;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$requests   = DB::table('request_models')
				->selectRaw('
					request_models.folio as folio,
					status_requests.description as statusRequest,
					expenses.title as title,
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
					expenses_details.concept as concept,
					CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountName,
					IF(expenses_details.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					expenses_details.amount as amount,
					expenses_details.tax as tax,
					taxes_expenses.taxes_amount as taxesAmount,
					expenses_details.sAmount as sAmount,
					de_labels.labels as labels,
					expenses.resourceId as resourceId,
					resources.total as resourceTotal,
					(expenses.total-resources.total) as difference,
					IF(request_models.taxPayment = 1 AND expenses.reembolso > 0, "Pagado",
						IF(request_models.taxPayment = 1 AND expenses.reembolso > 0, "No Pagado",
							IF(expenses.reembolso = 0, "No Aplica","No Aplica")
						)
					) as checkPayment,
					IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 1, "Comprobado",
						IF(request_models.payment = 0 AND expenses.reintegro > 0 AND request_models.free = 0, "No Comprobado",
							IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 0, "No Comprobado",
								IF(expenses.reintegro = 0, "No Aplica","No Aplica")
							)
						)
					) as checked
				')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->leftJoin('expenses','expenses.idFolio','request_models.folio')
				->leftJoin('expenses_details','expenses_details.idExpenses','expenses.idExpenses')
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
				->leftJoin('accounts as ed_acc_r','expenses_details.idAccountR','ed_acc_r.idAccAcc')
				->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as taxes_amount FROM taxes_expenses GROUP BY idExpensesDetail) AS taxes_expenses'),'expenses_details.idExpensesDetail','taxes_expenses.idExpensesDetail')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_expenses INNER JOIN labels ON label_detail_expenses.idlabels = labels.idlabels GROUP BY idExpensesDetail) AS de_labels'),'expenses_details.idExpensesDetail','de_labels.idExpensesDetail')
				->leftJoin('resources','expenses.resourceId','resources.idFolio')
				->where('request_models.kind',3)
				->whereIn('request_models.status',[4,5,10,11,12])
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(98)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(98)->pluck('departament_id'))
				->where(function ($query) use ($name, $mindate, $maxdate,$enterprise,$direction,$department,$status,$folio)
				{
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Comprobación de Gastos.xlsx');
			$headers        = ['Reporte de Comprobación de Gastos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','Datos de revisión','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Autorizada por','Fecha de autorización','Concepto','Clasificación del gasto','Fiscal','Subtotal','IVA','Impuesto Adicional','Importe','Etiquetas','Folio de Solicitud de Recurso','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro'];
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
					if(in_array($key,['amount','tax','taxesAmount','sAmount','resourceTotal','difference']))
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

	public function expensesDetail(Request $request)
	{
		$details  = "";
		$taxes    = 0;
		$request  = App\RequestModel::find($request->folio);
		return view('reporte.administracion.partial.modal_comprobacion_gasto',['request'=>$request]);
	}
}
