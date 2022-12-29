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

class ReportAdministrationMovementsAccountsController extends Controller
{
	private $module_id = 96;
	public function movementsAccountReport(Request $request)
	{
		if (Auth::user()->module->where('id',185)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$name		= $request->name;
			$folio		= $request->folio;
			$stat		= $request->stat;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$kind		= $request->kind;

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange	= $mindate;
				$endRange	= $maxdate;

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

			$searchUser 	= App\User::select('users.id')->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')->get();

			$requests = App\RequestModel::whereIn('kind',[11,12,13,14,15])
				->whereIn('status',[4,5,10,11,12])
				->where(function ($query) use ($name, $mindate, $maxdate, $folio,$searchUser,$kind,$stat)
				{
					if($name != "")
					{
						$query->where(function($query2) use ($searchUser)
						{
							$query2->whereIn('idRequest',$searchUser)->orWhereIn('idElaborate',$searchUser);
						});
					}
					if($folio != "")
					{
						$query->where('folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($kind != "") 
					{
						$query->whereIn('kind',$kind);
					}
					if ($stat != "") 
					{
						$query->whereIn('status',$stat);
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);

			return view('reporte.administracion.movimiento_entre_cuentas',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 185,
					'requests'	=> $requests,
					'name'		=> $name,
					'folio'		=> $folio,
					'stat'		=> $stat,
					'mindate'	=> $request->mindate,
					'maxdate'	=> $request->maxdate,
					'kind'		=> $kind,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function movementsAccountExcel(Request $request)
	{
		if (Auth::user()->module->where('id',185)->count()>0)
		{
			$name		= $request->name;
			$folio		= $request->folio;
			$stat		= $request->stat;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$kind		= $request->kind;

			$requestsAdjustment = DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								adjustments.title as title,
								adjustments.datetitle as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								adjustments.commentaries as commentaries,
								IFNULL(enterprise_origin_reviewed.name, enterprise_origin.name) as enterprise_origin,
								IFNULL(enterprise_destiny_reviewed.name, enterprise_destiny.name) as enterprise_destiny,
								IFNULL(direction_destiny_reviewed.name, direction_destiny.name) as direction_destiny,
								IFNULL(department_destiny_reviewed.name, department_destiny.name) as department_destiny,
								IF(account_destiny_reviewed.account IS NOT NULL,CONCAT_WS(" - ",account_destiny_reviewed.account,account_destiny_reviewed.description), CONCAT_WS(" - ",account_destiny.account,account_destiny.description)) as account_destiny,
								IFNULL(project_destiny_reviewed.proyectName, project_destiny.proyectName) as project_destiny,
								adjustments.currency as currency,
								DATE_FORMAT(adjustments.paymentDate, "%d-%m-%Y") as payment_date,
								payment_methods.method as payment_method,
								adjustments.amount as amount
							')
							->leftJoin('adjustments','adjustments.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','adjustments.idEnterpriseOrigin','enterprise_origin.id')
							->leftJoin('enterprises as enterprise_destiny','adjustments.idEnterpriseDestiny','enterprise_destiny.id')
							->leftJoin('areas as direction_destiny','adjustments.idAreaDestiny','direction_destiny.id')
							->leftJoin('departments as department_destiny','adjustments.idDepartamentDestiny','department_destiny.id')
							->leftJoin('accounts as account_destiny','adjustments.idAccAccDestiny','account_destiny.idAccAcc')
							->leftJoin('projects as project_destiny','adjustments.idProjectDestiny','project_destiny.idproyect')
							->leftJoin('enterprises as enterprise_origin_reviewed','adjustments.idEnterpriseOriginR','enterprise_origin_reviewed.id')
							->leftJoin('enterprises as enterprise_destiny_reviewed','adjustments.idEnterpriseDestinyR','enterprise_destiny_reviewed.id')
							->leftJoin('areas as direction_destiny_reviewed','adjustments.idAreaDestinyR','direction_destiny_reviewed.id')
							->leftJoin('departments as department_destiny_reviewed','adjustments.idDepartamentDestinyR','department_destiny_reviewed.id')
							->leftJoin('accounts as account_destiny_reviewed','adjustments.idAccAccDestinyR','account_destiny_reviewed.idAccAcc')
							->leftJoin('projects as project_destiny_reviewed','adjustments.idProjectDestinyR','project_destiny_reviewed.idproyect')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','adjustments.idpaymentMethod')
							->whereIn('request_models.kind',[11])
							->whereIn('request_models.status',[4,5,10,11,12])
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$stat)
							{
								if($name != "")
								{
									$query->where(function($q) use ($name)
									{
										$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
											->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
									});
								}
								if($folio != "")
								{
									$query->where('request_models.folio',$folio);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if ($stat != "") 
								{
									$query->whereIn('request_models.status',$stat);
								}
								if ($kind != "") 
								{
									$query->whereIn('request_models.kind',$kind);
								}
							})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsLoan 	= DB::table('request_models')
							->selectRaw('
								request_models.folio as folio,
								status_requests.description as status,
								loan_enterprises.title as title,
								loan_enterprises.datetitle as datetitle,
								CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
								CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
								enterprise_origin.name as enterprise_origin,
								CONCAT_WS(" ",account_origin.account,account_origin.description) as account_origin,
								enterprise_destiny.name as enterprise_destiny,
								CONCAT_WS(" ",account_destiny.account,account_destiny.description) as account_destiny,
								loan_enterprises.currency as currency,
								DATE_FORMAT(loan_enterprises.paymentDate, "%d-%m-%Y %H:%i") as payment_date,
								payment_methods.method as payment_method,
								loan_enterprises.amount as amount
							')
							->leftJoin('loan_enterprises','loan_enterprises.idFolio','request_models.folio')
							->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
							->leftJoin('users as request_user','request_models.idRequest','request_user.id')
							->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
							->leftJoin('enterprises as enterprise_origin','loan_enterprises.idEnterpriseOriginR','enterprise_origin.id')
							->leftJoin('accounts as account_origin','loan_enterprises.idAccAccOriginR','account_origin.idAccAcc')
							->leftJoin('enterprises as enterprise_destiny','loan_enterprises.idEnterpriseDestinyR','enterprise_destiny.id')
							->leftJoin('accounts as account_destiny','loan_enterprises.idAccAccDestinyR','account_destiny.idAccAcc')
							->leftJoin('payment_methods','payment_methods.idpaymentMethod','loan_enterprises.idpaymentMethod')
							->whereIn('request_models.kind',[12])
							->whereIn('request_models.status',[4,5,10,11,12])
							->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$stat)
								{
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
									if ($stat != "") 
									{
										$query->whereIn('request_models.status',$stat);
									}
									if ($kind != "") 
									{
										$query->whereIn('request_models.kind',$kind);
									}
								})
							->orderBy('request_models.fDate','DESC')
							->orderBy('request_models.folio','DESC')
							->get();

			$requestsPurchase 	= DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									status_requests.description as status,
									purchase_enterprises.title as title,
									purchase_enterprises.datetitle as datetitle,
									CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
									purchase_enterprises.numberOrder as number_order,
									enterprise_origin.name as enterprise_origin,
									area_origin.name as area_origin,
									department_origin.name as department_origin,
									CONCAT_WS(" ",account_origin.account,account_origin.description) as account_origin,
									project_origin.proyectName as project_origin,
									enterprise_destiny.name as enterprise_destiny,
									CONCAT_WS(" ",account_destiny.account,account_destiny.description) as account_destiny,
									project_destiny.proyectName as project_destiny,
									purchase_enterprise_details.quantity as quantity,
									purchase_enterprise_details.unit as unit,
									purchase_enterprise_details.description as description,
									purchase_enterprise_details.unitPrice as unitPrice,
									purchase_enterprise_details.tax as tax,
									pe_taxes.taxes_amount as taxes_amount,
									pe_retention.retention_amount as retention_amount,
									purchase_enterprise_details.amount as amount_detail,
									purchase_enterprises.typeCurrency as currency,
									DATE_FORMAT(purchase_enterprises.paymentDate, "%d-%m-%Y %H:%i") as payment_date,
									payment_methods.method as payment_method,
									purchase_enterprises.amount as amount
								')
								->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
								->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
								->leftJoin('users as request_user','request_models.idRequest','request_user.id')
								->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
								->leftJoin('enterprises as enterprise_origin','purchase_enterprises.idEnterpriseOriginR','enterprise_origin.id')
								->leftJoin('areas as area_origin','purchase_enterprises.idEnterpriseOriginR','area_origin.id')
								->leftJoin('departments as department_origin','purchase_enterprises.idEnterpriseOriginR','department_origin.id')
								->leftJoin('accounts as account_origin','purchase_enterprises.idAccAccOriginR','account_origin.idAccAcc')
								->leftJoin('projects as project_origin','purchase_enterprises.idEnterpriseOriginR','project_origin.idProyect')
								->leftJoin('enterprises as enterprise_destiny','purchase_enterprises.idEnterpriseDestinyR','enterprise_destiny.id')
								->leftJoin('accounts as account_destiny','purchase_enterprises.idAccAccDestinyR','account_destiny.idAccAcc')
								->leftJoin('projects as project_destiny','purchase_enterprises.idEnterpriseOriginR','project_destiny.idproyect')
								->leftJoin('payment_methods','payment_methods.idpaymentMethod','purchase_enterprises.idpaymentMethod')
								->leftJoin('purchase_enterprise_details','purchase_enterprise_details.idpurchaseEnterprise','purchase_enterprises.idpurchaseEnterprise')
								->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
								->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'purchase_enterprise_details.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
								->whereIn('request_models.kind',[13])
								->whereIn('request_models.status',[4,5,10,11,12])
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$stat)
									{
										if($name != "")
										{
											$query->where(function($q) use ($name)
											{
												$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
													->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
											});
										}
										if($folio != "")
										{
											$query->where('request_models.folio',$folio);
										}
										if($mindate != "" && $maxdate != "")
										{
											$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
										}
										if ($stat != "") 
										{
											$query->whereIn('request_models.status',$stat);
										}
										if ($kind != "") 
										{
											$query->whereIn('request_models.kind',$kind);
										}
									})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();

			$requestsGroups 	= DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									status_requests.description as status,
									groups.title as title,
									groups.datetitle as datetitle,
									CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
									groups.numberOrder as number_order,
									groups.operationType as operation_type,
									enterprise_origin.name as enterprise_origin,
									area_origin.name as area_origin,
									department_origin.name as department_origin,
									CONCAT_WS(" ",account_origin.account,account_origin.description) as account_origin,
									project_origin.proyectName as project_origin,
									enterprise_destiny.name as enterprise_destiny,
									CONCAT_WS(" ",account_destiny.account,account_destiny.description) as account_destiny,
									groups_details.quantity as quantity,
									groups_details.unit as unit,
									groups_details.description as description,
									groups_details.unitPrice as unitPrice,
									groups_details.tax as tax,
									groups_taxes.taxes_amount as taxes_amount,
									groups_retentions.retention_amount as retention_amount,
									groups_details.amount as amount_detail,
									groups.reference as reference,
									groups.typeCurrency as type_currency,
									groups.paymentDate as payment_date,
									payment_methods.method as payment_method,
									groups.statusBill as status_bill,
									groups.amount as amount,
									groups.commission as commission,
									groups.amountRetake as amount_retake
								')
								->leftJoin('groups','groups.idFolio','request_models.folio')
								->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
								->leftJoin('users as request_user','request_models.idRequest','request_user.id')
								->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
								->leftJoin('enterprises as enterprise_origin','groups.idEnterpriseOriginR','enterprise_origin.id')
								->leftJoin('areas as area_origin','groups.idEnterpriseOriginR','area_origin.id')
								->leftJoin('departments as department_origin','groups.idEnterpriseOriginR','department_origin.id')
								->leftJoin('accounts as account_origin','groups.idAccAccOriginR','account_origin.idAccAcc')
								->leftJoin('projects as project_origin','groups.idEnterpriseOriginR','project_origin.idproyect')
								->leftJoin('enterprises as enterprise_destiny','groups.idEnterpriseDestinyR','enterprise_destiny.id')
								->leftJoin('accounts as account_destiny','groups.idAccAccDestinyR','account_destiny.idAccAcc')
								->leftJoin('projects as project_destiny','groups.idEnterpriseOriginR','project_destiny.idproyect')
								->leftJoin('payment_methods','payment_methods.idpaymentMethod','groups.idpaymentMethod')
								->leftJoin('groups_details','groups_details.idgroups','groups.idgroups')
								->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS groups_taxes'),'groups_details.idgroupsDetail','groups_taxes.idgroupsDetail')
								->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS groups_retentions'),'groups_details.idgroupsDetail','groups_retentions.idgroupsDetail')
								->whereIn('request_models.kind',[14])
								->whereIn('request_models.status',[4,5,10,11,12])
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$stat)
								{
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
									if ($stat != "") 
									{
										$query->whereIn('request_models.status',$stat);
									}
									if ($kind != "") 
									{
										$query->whereIn('request_models.kind',$kind);
									}
								})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();

			$requestsMovements = DB::table('request_models')
								->selectRaw('
									request_models.folio as folio,
									status_requests.description as status,
									movements_enterprises.title as title,
									movements_enterprises.datetitle as datetitle,
									CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
									CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
									DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
									IF(request_models.taxPayment = 1,"Sí","No") as tax_payment,
									enterprise_origin.name as enterprise_origin,
									CONCAT_WS(" ",account_origin.account,account_origin.description) as account_origin,
									enterprise_destiny.name as enterprise_destiny,
									CONCAT_WS(" ",account_destiny.account,account_destiny.description) as account_destiny,
									movements_enterprises.typeCurrency as currency,
									DATE_FORMAT(movements_enterprises.paymentDate, "%d-%m-%Y %H:%i") as payment_date,
									payment_methods.method as payment_method,
									movements_enterprises.amount as amount
								')
								->leftJoin('movements_enterprises','movements_enterprises.idFolio','request_models.folio')
								->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
								->leftJoin('users as request_user','request_models.idRequest','request_user.id')
								->leftJoin('users as elaborate_user','request_models.idElaborate','elaborate_user.id')
								->leftJoin('enterprises as enterprise_origin','movements_enterprises.idEnterpriseOriginR','enterprise_origin.id')
								->leftJoin('accounts as account_origin','movements_enterprises.idAccAccOriginR','account_origin.idAccAcc')
								->leftJoin('enterprises as enterprise_destiny','movements_enterprises.idEnterpriseDestinyR','enterprise_destiny.id')
								->leftJoin('accounts as account_destiny','movements_enterprises.idAccAccDestinyR','account_destiny.idAccAcc')
								->leftJoin('payment_methods','payment_methods.idpaymentMethod','movements_enterprises.idpaymentMethod')
								->whereIn('request_models.kind',[15])
								->whereIn('request_models.status',[4,5,10,11,12])
								->where(function ($query) use ($name, $mindate, $maxdate, $folio,$kind,$stat)
								{
									if($name != "")
									{
										$query->where(function($q) use ($name)
										{
											$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%')
												->orWhere(DB::raw("CONCAT_WS(' ',elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name)"),'LIKE','%'.$name.'%');
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('request_models.reviewDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
									if ($stat != "") 
									{
										$query->whereIn('request_models.status',$stat);
									}
									if ($kind != "") 
									{
										$query->whereIn('request_models.kind',$kind);
									}
								})
								->orderBy('request_models.fDate','DESC')
								->orderBy('request_models.folio','DESC')
								->get();


			$new_sheet 		= true;
			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Movimientos Entre Cuentas.xlsx');

			if (count($requestsLoan)>0) 
			{
				$new_sheet		= false;
				$headers		= ['Préstamo Inter-Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$writer->getCurrentSheet()->setName('Préstamo Inter-Empresa');

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsLoan as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
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
			}

			if (count($requestsAdjustment)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Ajuste de Movimientos');
				}

				$headers		= ['Ajuste de Movimientos','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','Cuenta de Destino','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Comentarios','Empresa de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsAdjustment as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
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
			}

			if (count($requestsPurchase)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Compras Inter-Empresa');
				}

				$headers		= ['Compras Inter-Empresa','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','','Datos de la solicitud','','','','','','','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Empresa de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Dirección de destino','Departamento de destino','Clasificación del gasto de destino','Proyecto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsPurchase as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->project_destiny		= '';
						$request->currency				= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->amount				= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount','unitPrice','tax','taxes_amount','retention_amount','amount_detail']))
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
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
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
			}

			if (count($requestsGroups)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Grupos');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Grupos');
				}

				$headers		= ['Grupos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','','','Cuenta de Origen','','','','','Cuenta de Destino','','Datos de la solicitud','','','','','','','','Condiciones de pago','','','','','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Número de orden','Tipo de operación','Empresa de origen','Dirección de origen','Departamento de origen','Clasificación del gasto de origen','Proyecto de origen','Empresa de destino','Clasificación del gasto de destino','Cantidad','Unidad','Descripción','Precio Unitario','IVA','Impuesto Adicional','Retenciones','Total','Referencia/Número de Factura','Tipo de moneda','Fecha de pago','Forma de pago','Estado  de factura','Importe total a pagar','Comisión','Importe a retomar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsGroups as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}
					else
					{
						$request->folio					= null;
						$request->status				= '';
						$request->title					= '';
						$request->datetitle				= '';
						$request->request_user			= '';
						$request->elaborate_user		= '';
						$request->date					= '';
						$request->tax_payment			= '';
						$request->number_order			= '';
						$request->operation_type		= '';
						$request->enterprise_origin		= '';
						$request->area_origin			= '';
						$request->department_origin		= '';
						$request->account_origin		= '';
						$request->project_origin		= '';
						$request->enterprise_destiny	= '';
						$request->account_destiny		= '';
						$request->reference				= '';
						$request->type_currency			= '';
						$request->payment_date			= '';
						$request->payment_method		= '';
						$request->status_bill			= '';
						$request->amount				= '';
						$request->commission			= '';
						$request->amount_retake			= '';
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['unitPrice','tax','taxes_amount','retention_amount','amount_detail','amount','commission','amount_retake']))
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
						elseif(in_array($k,['quantity']))
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
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
			}

			if (count($requestsMovements)>0) 
			{
				if ($new_sheet) 
				{
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
					$new_sheet = false;
				}
				else
				{
					$newSheet = $writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName('Movimientos Misma Empresa');
				}

				$new_sheet		= false;
				$headers		= ['Movimientos Misma Empresa','','','','','','','','','','','','','','',''];
				$tempHeaders	= [];
				foreach($headers as $k => $header)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Datos generales','','','','','','','','Cuenta de Origen','','Cuenta de Destino','','Condiciones de pago','','',''];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol2);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$subHeaders		= ['Folio','Estado','Título','Fecha','Solicitante','Elaborado por','Fecha de elaboración','Fiscal','Empresa de origen','Clasificación del gasto de origen','Empresa de destino','Clasificación del gasto de destino','Tipo de moneda','Fecha de pago','Forma de pago','Importe total a pagar'];
				$tempSubHeader	= [];
				foreach($subHeaders as $k => $subHeader)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$mhStyleCol3);
				}
				$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
				$writer->addRow($rowFromValues);

				$tempFolio	= '';
				$kindRow	= true;

				foreach($requestsMovements as $request)
				{
					if($tempFolio != $request->folio)
					{
						$tempFolio = $request->folio;
						$kindRow = !$kindRow;
					}

					$tmpArr = [];
					foreach($request as $k => $r)
					{
						if(in_array($k,['amount']))
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
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function movementsAccountDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			
			$request	= App\RequestModel::whereIn('kind',[11,12,13,14,15])->find($request->idmovement);
			
			$movement	= [];
			$table_data	= [];
			$title		= $request->requestkind->kind;

			switch ($request->kind) 
			{
				case 11:
					return view('reporte.administracion.partial.movimiento_entre_cuentas.ajuste_movimientos',[
						'request' => $request,
					]);
					break;
				case 12:
					return view('reporte.administracion.partial.movimiento_entre_cuentas.prestamo',[
						'request' => $request,
					]);
					break;
				case 13:
					return view('reporte.administracion.partial.movimiento_entre_cuentas.compra_inter_empresas',[
						'request' => $request,
					]);
					break;
				case 14:
					return view('reporte.administracion.partial.movimiento_entre_cuentas.grupos',[
						'request' => $request,
					]);
					break;
				case 15:
					return view('reporte.administracion.partial.movimiento_entre_cuentas.movimiento_misma_empresa',[
						'request' => $request,
					]);
					break;
				default:
					break;
			}
		}
	}
}
