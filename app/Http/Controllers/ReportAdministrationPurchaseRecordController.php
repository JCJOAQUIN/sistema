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

class ReportAdministrationPurchaseRecordController extends Controller
{
	private $module_id = 96;
	public function purchaseRecordReport(Request $request)
	{
		if(Auth::user()->module->where('id',258)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$enterprise	= $request->enterprise;
			$direction	= $request->direction;
			$department	= $request->department;
			$name		= $request->name;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status		= $request->status;
			$folio		= $request->folio;

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

			$requests   = App\RequestModel::where('kind',17)
				->whereIn('status',[4,5,10,11,12])
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(258)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(258)->pluck('departament_id'))
				->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$folio,$mindate,$maxdate)
				{
					if ($folio != "") 
					{
						$query->where('folio',$folio);
					}
					if ($enterprise != "")
					{                               
						$query->whereIn('request_models.idEnterprise',$enterprise);
					}
					if ($direction != "")
					{                           
						$query->whereIn('request_models.idArea',$direction);
					}
					if ($department != "")
					{                               
						$query->whereIn('request_models.idDepartment',$department);
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
				->paginate(10);
			return view('reporte.administracion.registro_compras',
				[
					'id'         => $data['father'],
					'title'      => $data['name'],
					'details'    => $data['details'],
					'child_id'   => $this->module_id,
					'option_id'  => 258,
					'requests'   => $requests,
					'enterprise' => $enterprise,
					'direction'  => $direction,
					'department' => $department,
					'name'       => $name,
					'mindate'    => $request->mindate,
					'maxdate'    => $request->maxdate,
					'status'     => $status,
					'folio'      => $folio,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function purchaseRecordExport(Request $request)
	{
		if(Auth::user()->module->where('id',258)->count()>0)
		{
			$enterprise	= $request->enterprise;
			$direction	= $request->direction;
			$department	= $request->department;
			$name		= $request->name;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status		= $request->status;
			$folio		= $request->folio;
			

			$requests = DB::table('request_models')->selectRaw('
					request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchase_records.title," - ",purchase_records.datetitle) as title,
					purchase_records.numberOrder as number_order,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					request_enterprise.name as request_enterprise,
					request_direction.name as request_direction,
					request_department.name as request_department,
					request_project.proyectName as request_project,
					CONCAT(request_account.account, " ", request_account.description," (",request_account.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
					review_enterprise.name as review_enterprise,
					review_direction.name as review_direction,
					review_department.name as review_department,
					review_project.proyectName as review_project,
					CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
					IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as taxPayment,
					purchase_records.provider as provider,
					purchase_records.reference as reference,
					purchase_records.paymentMethod as paymentMethod,
					purchase_record_details.quantity as detail_quantity,
					purchase_record_details.unit as detail_unit,
					purchase_record_details.description as detail_description,
					purchase_record_details.unitPrice as detail_unit_price,
					purchase_record_details.subtotal as detail_subtotal,
					purchase_record_details.tax as detail_tax,
					IFNULL(purchase_record_taxes.taxes_amount,0) as detail_taxes,
					IFNULL(purchase_record_retentions.retention_amount,0) as detail_retentions,
					purchase_record_details.total as detail_amount,
					purchase_record_labels.labels as detail_labels,
					IFNULL(payment.payment_amount,0) as payment_amount,
					purchase_records.typeCurrency as currency
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchase_records','purchase_records.idFolio','request_models.folio')
				->leftJoin('purchase_record_details','purchase_records.id','purchase_record_details.idPurchaseRecord')
				->leftJoin('users as request_user','idRequest','request_user.id')
				->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
				->leftJoin('enterprises as request_enterprise','request_models.idEnterprise','request_enterprise.id')
				->leftJoin('areas as request_direction','idArea','request_direction.id')
				->leftJoin('departments as request_department','idDepartment','request_department.id')
				->leftJoin('projects as request_project','idProject','request_project.idproyect')
				->leftJoin('accounts as request_account','request_models.account','request_account.idAccAcc')
				->leftJoin('users as review_user','idCheck','review_user.id')
				->leftJoin('enterprises as review_enterprise','request_models.idEnterpriseR','review_enterprise.id')
				->leftJoin('areas as review_direction','idAreaR','review_direction.id')
				->leftJoin('departments as review_department','idDepartamentR','review_department.id')
				->leftJoin('projects as review_project','idProjectR','review_project.idproyect')
				->leftJoin('accounts as review_account','request_models.accountR','review_account.idAccAcc')
				->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS purchase_record_labels'),'purchase_record_details.id','purchase_record_labels.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS purchase_record_taxes'),'purchase_record_details.id','purchase_record_taxes.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS purchase_record_retentions'),'purchase_record_details.id','purchase_record_retentions.idPurchaseRecordDetail')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS payment'),function($q)
					{
						$q->on('request_models.folio','=','payment.idFolio')
						->on('request_models.kind','=','payment.idKind');
					})
				->where('request_models.kind',17)
				->whereIn('request_models.status',[4,5,10,11,12])
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(258)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(258)->pluck('departament_id'))
				->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$folio,$mindate,$maxdate)
				{
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($enterprise != "")
					{                               
						$query->whereIn('request_models.idEnterprise',$enterprise);
					}
					if ($direction != "")
					{                           
						$query->whereIn('request_models.idArea',$direction);
					}
					if ($department != "")
					{                               
						$query->whereIn('request_models.idDepartment',$department);
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Registro de Compras.xlsx');

			$headers        = ['Reporte de Registro de Compras','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total Pagado','Moneda'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio  = '';
			$kindRow    = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio                 = null;
					$request->status                = '';
					$request->title                 = '';
					$request->number_order          = '';
					$request->request_user          = '';
					$request->elaborate_user        = '';
					$request->date                  = '';
					$request->request_enterprise    = '';
					$request->request_direction     = '';
					$request->request_department    = '';
					$request->request_project       = '';
					$request->request_account       = '';
					$request->review_user           = '';
					$request->review_date           = '';
					$request->review_enterprise     = '';
					$request->review_direction      = '';
					$request->review_department     = '';
					$request->review_project        = '';
					$request->review_account        = '';
					$request->authorize_user        = '';
					$request->authorize_date        = '';
					$request->taxPayment            = '';
					$request->provider              = '';
					$request->reference             = '';
					$request->paymentMethod         = '';
					$request->payment_amount        = '';
					$request->currency              = '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['detail_unit_price','detail_subtotal','detail_tax','detail_taxes','detail_retentions','detail_amount','payment_amount']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'detail_quantity')
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
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

	public function purchaseRecordDetail(Request $request)
	{
		if ($request->ajax())
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.modal_registro_compra')->with('request',$request);
		}
	}

}
