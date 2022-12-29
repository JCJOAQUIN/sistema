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

class ReportAdministrationResourceController extends Controller
{
	private $module_id = 96;
	public function resourceReport(Request $request)
	{
		if (Auth::user()->module->where('id',129)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
	
			$idEnterprise   = $request->idEnterprise; 
			$idArea         = $request->idArea;
			$idDepartment   = $request->idDepartment;
			$account        = $request->account;
			$name           = $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status         = $request->status;
			$folio          = $request->folio;
			$initRange      = "";
			$endRange       = "";

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

			$requests    = App\RequestModel::where('kind',8)
					->whereIn('status',[4,5,10,11,12])
					->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(129)->pluck('enterprise_id'))
					->whereIn('idDepartamentR',Auth::user()->inChargeDep(129)->pluck('departament_id'))
					->where(function ($query) use ($name,   $mindate,   $maxdate    ,$searchUser,$idEnterprise, $idArea,$idDepartment,$status,$folio,$initRange,$endRange)
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
							$query->whereIn('request_models.idAreaR', $idArea);
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
						if( $mindate != "" &&     $maxdate != "")
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
					->paginate(10);

			return view('reporte.administracion.recurso',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 129,
					
					'idEnterprise'  => $idEnterprise,
					'idArea'        => $idArea,
					'idDepartment'  => $idDepartment,
					'account'       => $account,
					'name'          => $name,
					'mindate'       => $request->mindate,
					'maxdate'       => $request->maxdate,
					'status'        => $status,
					'folio'         => $folio,
					'requests'      => $requests
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function resourceDetail(Request $request)
	{
		if($request->ajax())
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.modal_asignacion_recursos')->with('request',$request);
		}
	}

	public function resourceExcel(Request $request)
	{
		if (Auth::user()->module->where('id',129)->count()>0)
		{
			$idEnterprise   = $request->idEnterprise; 
			$idArea         = $request->idArea;
			$idDepartment   = $request->idDepartment;
			$account        = $request->account;
			$name           = $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status         = $request->status;
			$folio          = $request->folio;

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			$requests       = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as statusRequest,
								resources.title as title,
								IF(request_models.idRequest = NULL,"Sin Solicitante",CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name)) as requestUser,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborateUser,
								DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborateDate,
								request_enterprise.name as requestEnterprise,
								request_direction.name as requestDirection,
								request_department.name as requestDepartment,
								request_project.proyectName as requestProject,
								CONCAT("Varias") as requestAccount,
								CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as reviewUser,
								DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i") as reviewDate,
								review_enterprise.name as reviewEnterprise,
								review_direction.name as reviewDirection,
								review_department.name as reviewDepartment,
								review_project.proyectName as reviewProject,
								CONCAT("Varias") as reviewAccount,
								CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorizeUser,
								DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i") as authorizeDate,
								resources.total as resourceTotal,
								payment_methods.method as payment_method,
								banks.description as bank,
								employees.account as account,
								employees.cardNumber as card,
								employees.clabe as clabe,
								resources.currency as currency,
								resource_details.concept as concept,
								CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountReview,
								resource_details.amount as amount,
								req_labels.labels as labels,
								IF(checkup.folio IS NULL,"NO","SÍ") AS checkup,
								IF(checkup.folio IS NULL,"",checkup.expenses_folio) AS expenses_folio,
								IF(checkup.folio IS NULL,"",ROUND(checkup.total,2)) as expenses_amount,
								IF(checkup.folio IS NULL,"",ROUND(checkup.total - resources.total,2)) as diff_exp_resource,
								resources.total as totalPaid,
								resources.currency as currencyPaid
							')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('resources','resources.idFolio','request_models.folio')
							->leftJoin('resource_details','resource_details.idresource','resources.idresource')
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
							->leftJoin('accounts as ed_acc_r','resource_details.idAccAccR','ed_acc_r.idAccAcc')
							->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
							->leftJoin(DB::raw('(SELECT request_folio as folio, request_kind as kind, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM request_has_labels INNER JOIN labels ON request_has_labels.labels_idlabels = labels.idlabels GROUP BY request_folio, request_kind) AS req_labels'),function($q)
							{
								$q->on('request_models.folio','=','req_labels.folio')
								->on('request_models.kind','=','req_labels.kind');
							})
							->leftJoin('payment_methods','resources.idpaymentMethod','payment_methods.idpaymentMethod')
							->leftJoin('employees','resources.idEmployee','employees.idEmployee')
							->leftJoin('banks','employees.idBanks','banks.idBanks')
							->leftJoin(DB::raw('(SELECT expenses.idFolio as expenses_folio,expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total,expenses.idFolio) AS checkup'),'request_models.folio','checkup.folio')
							->where('request_models.kind',8)
							->whereIn('request_models.status',[4,5,10,11,12])
							->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(129)->pluck('enterprise_id'))
							->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(129)->pluck('departament_id'))
							->where(function ($query) use ($name,   $mindate,   $maxdate    ,$searchUser,$idEnterprise, $idArea,$idDepartment,$status,$folio)
							{
								if ($folio != "") 
								{
									$query->where('request_models.folio',$folio);
								}
								if ($idEnterprise != "")
								{                               
									$query->whereIn('request_models.idEnterpriseR',$idEnterprise);
								}
								if ($idArea != "")
								{                           
									$query->whereIn('request_models.idAreaR', $idArea);
								}
									
								if ($idDepartment != "")
								{                               
									$query->whereIn('request_models.idDepartamentR',$idDepartment);
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
								}
								if( $mindate != "" &&   $maxdate     != "")
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Asignación de Recurso.xlsx');

			$headers        = ['Reporte de Asignación de Recurso','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos la solicitud','','','','','','','','','','Etiquetas','Comprobación','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de Solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Monto Total','Método de pago','Banco','Cuenta','Tarjeta','CLABE','Moneda','Concepto','Clasificación de gasto','Importe','Etiquetas','Comprobación','Folio de la Comprobación','Monto de la Comprobación','Diferencia contra la Comprobación','Total a pagar','Moneda'];
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
					$request->requestAccount    = '';
					$request->reviewUser        = '';
					$request->reviewDate        = '';
					$request->reviewEnterprise  = '';
					$request->reviewDirection   = '';
					$request->reviewDepartment  = '';
					$request->reviewProject     = '';
					$request->reviewAccount     = '';
					$request->authorizeUser     = '';
					$request->authorizeDate     = '';
					$request->resourceTotal     = '';
					$request->payment_method    = '';
					$request->bank              = '';
					$request->account           = '';
					$request->card              = '';
					$request->clabe             = '';
					$request->currency          = '';
					$request->labels            = '';
					$request->checkup           = '';
					$request->expenses_folio    = '';
					$request->expenses_amount   = '';
					$request->diff_exp_resource = '';
					$request->totalPaid         = '';
					$request->currencyPaid      = '';
				}
				$row = [];
				foreach($request as $key => $value)
				{
					if(in_array($key,['resourceTotal','amount','expenses_amount','diff_exp_resource','totalPaid']))
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

	public function resourceExcelWithoutGrouping(Request $request)
	{   
		if (Auth::user()->module->where('id',129)->count()>0)
		{
			$idEnterprise   = $request->idEnterprise; 
			$idArea         = $request->idArea;
			$idDepartment   = $request->idDepartment;
			$account        = $request->account;
			$name           = $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status         = $request->status;
			$folio          = $request->folio;

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			$subHeaders     = ['Monto Total','Método de pago','Banco','Cuenta','Tarjeta','CLABE','Moneda','Concepto','Clasificación de gasto','Importe','Etiquetas','Comprobación','Folio de la Comprobación','Monto de la Comprobación','Diferencia contra la Comprobación','Total a pagar','Moneda'];
			$requests       = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as statusRequest,
								resources.title as title,
								IF(request_models.idRequest = NULL,"Sin Solicitante",CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name)) as requestUser,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborateUser,
								DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborateDate,
								request_enterprise.name as requestEnterprise,
								request_direction.name as requestDirection,
								request_department.name as requestDepartment,
								request_project.proyectName as requestProject,
								CONCAT("Varias") as requestAccount,
								CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as reviewUser,
								DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i") as reviewDate,
								review_enterprise.name as reviewEnterprise,
								review_direction.name as reviewDirection,
								review_department.name as reviewDepartment,
								review_project.proyectName as reviewProject,
								CONCAT("Varias") as reviewAccount,
								CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorizeUser,
								DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i") as authorizeDate,
								resources.total as resourceTotal,
								payment_methods.method as payment_method,
								banks.description as bank,
								employees.account as account,
								employees.cardNumber as card,
								employees.clabe as clabe,
								resources.currency as currency,
								resource_details.concept as concept,
								CONCAT_WS(" ",ed_acc_r.account,ed_acc_r.description,CONCAT("(",ed_acc_r.content,")")) as accountReview,
								resource_details.amount as amount,
								req_labels.labels as labels,
								IF(checkup.folio IS NULL,"NO","SÍ") AS checkup,
								IF(checkup.folio IS NULL,"",checkup.expenses_folio) AS expenses_folio,
								IF(checkup.folio IS NULL,"",ROUND(checkup.total,2)) as expenses_amount,
								IF(checkup.folio IS NULL,"",ROUND(checkup.total - resources.total,2)) as diff_exp_resource,
								resources.total as totalPaid,
								resources.currency as currencyPaid
							')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('resources','resources.idFolio','request_models.folio')
							->leftJoin('resource_details','resource_details.idresource','resources.idresource')
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
							->leftJoin('accounts as ed_acc_r','resource_details.idAccAccR','ed_acc_r.idAccAcc')
							->leftJoin('users as authorize_user','request_models.idAuthorize','authorize_user.id')
							->leftJoin(DB::raw('(SELECT request_folio as folio, request_kind as kind, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM request_has_labels INNER JOIN labels ON request_has_labels.labels_idlabels = labels.idlabels GROUP BY request_folio, request_kind) AS req_labels'),function($q)
							{
								$q->on('request_models.folio','=','req_labels.folio')
								->on('request_models.kind','=','req_labels.kind');
							})
							->leftJoin('payment_methods','resources.idpaymentMethod','payment_methods.idpaymentMethod')
							->leftJoin('employees','resources.idEmployee','employees.idEmployee')
							->leftJoin('banks','employees.idBanks','banks.idBanks')
							->leftJoin(DB::raw('(SELECT expenses.idFolio as expenses_folio,expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total,expenses.idFolio) AS checkup'),'request_models.folio','checkup.folio')
							->where('request_models.kind',8)
							->whereIn('request_models.status',[4,5,10,11,12])
							->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(129)->pluck('enterprise_id'))
							->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(129)->pluck('departament_id'))
							->where(function ($query) use ($name,   $mindate,   $maxdate    ,$searchUser,$idEnterprise, $idArea,$idDepartment,$status,$folio)
							{
								if ($folio != "") 
								{
									$query->where('request_models.folio',$folio);
								}
								if ($idEnterprise != "")
								{                               
									$query->whereIn('request_models.idEnterpriseR',$idEnterprise);
								}
								if ($idArea != "")
								{                           
									$query->whereIn('request_models.idAreaR', $idArea);
								}
									
								if ($idDepartment != "")
								{                               
									$query->whereIn('request_models.idDepartamentR',$idDepartment);
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
								}
								if( $mindate != "" &&   $maxdate     != "")
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Asignación de Recurso.xlsx');

			$headers        = ['Reporte de Asignación de Recurso','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos la solicitud','','','','','','','','','','Etiquetas','Comprobación','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de Solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Monto Total','Método de pago','Banco','Cuenta','Tarjeta','CLABE','Moneda','Concepto','Clasificación de gasto','Importe','Etiquetas','Comprobación','Folio de la Comprobación','Monto de la Comprobación','Diferencia contra la Comprobación','Total a pagar','Moneda'];
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
					if(in_array($key,['resourceTotal','amount','expenses_amount','diff_exp_resource','totalPaid']))
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
}
