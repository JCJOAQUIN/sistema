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
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ReportFinanceIncomeController extends Controller
{
	private $module_id = 130;
	public function incomeReport(Request $request)
	{
		if (Auth::user()->module->where('id',159)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$idEnterprise	= $request->idEnterprise;
			$idProject		= $request->idProject;
			$name			= $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status			= $request->status;
			$fiscal			= $request->fiscal;

			$requests       = App\RequestModel::where('kind',10)
								->whereIn('status',[4,5,10,11,12])
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(159)->pluck('enterprise_id'))
								->where(function ($query) use ($name, $mindate, $maxdate,$idEnterprise,$idProject,$status,$fiscal)
								{
									if ($idEnterprise != "")
									{
										$query->where(function($q) use($idEnterprise)
										{
											$q->whereIn('idEnterprise',$idEnterprise)->orWhereIn('idEnterpriseR',$idEnterprise);
										});
									}
									if ($idProject != "")
									{							
										$query->whereIn('idProject',$idProject);
									}
									if($name != "")
									{
										$query->whereHas('requestUser',function($q) use ($name)
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
									if ($fiscal != "") 
									{
										$query->where('taxPayment',$fiscal);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(15);

			return view('reporte.finanzas.ingresos',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 159,
					'idEnterprise'	=> $idEnterprise,
					'idProject'		=> $idProject,
					'name'			=> $name,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'status'		=> $status,
					'fiscal'		=> $fiscal,
					'requests'		=> $requests
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function incomeExcel(Request $request)
	{
		if (Auth::user()->module->where('id',159)->count()>0)
		{
			$idEnterprise	= $request->idEnterprise;
			$idProject		= $request->idProject;
			$name			= $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status			= $request->status;
			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								status_requests.description as status,
								incomes.title as title,
								IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
								CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
								CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								requestEnterprise.name as enterprise_name,
								requestProject.proyectName as project_name,
								CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
								DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
								CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
								DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
								clients.businessName as client_name,
								requestEnterprise.name as reasonSocial,
								banks.description as bank_name,
								banks_accounts.alias as alias,
								banks_accounts.account as account,
								banks_accounts.branch as branch,
								banks_accounts.reference as reference,
								banks_accounts.clabe as clabe,
								banks_accounts.currency as currency,
								banks_accounts.agreement as agreement,
								income_details.quantity as quantity,
								income_details.unit as unit,
								income_details.description as concept,
								income_details.unitPrice as unitPrice,
								income_details.subtotal as subtotal,
								income_details.tax as tax,
								IFNULL(taxes_incomes.taxes_amount,0) as taxes,
								IFNULL(retention_incomes.retentions_amount,0) as retentions,
								income_details.amount as amount,
								incomes.amount as totalRequest,
								incomes.amount as totalProjected,
								IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
								IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
								IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
								IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
							')
							->leftJoin('incomes','incomes.idFolio','request_models.folio')
							->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
							->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
							->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
							->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
							->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
							->leftJoin('clients','clients.idClient','incomes.idClient')
							->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
							->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
							->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
							->where('request_models.kind',10)
							->whereIn('request_models.status',[4,5,10,11,12])
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(159)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $mindate, $maxdate,$idEnterprise,$idProject,$status)
							{
								if ($idEnterprise != "")
								{	
									$query->where(function($q) use($idEnterprise)
									{
										$q->whereIn('request_models.idEnterprise',$idEnterprise)->orWhereIn('request_models.idEnterpriseR',$idEnterprise);
									});
								}
								if ($idProject != "")
								{
									$query->whereIn('request_models.idProject',$idProject);
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.$name.'%');
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

			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Autorización de Proyección de Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->status			= '';
					$request->title				= '';
					$request->tax_payment		= '';
					$request->request_user		= '';
					$request->elaborate_user	= '';
					$request->date				= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->reviewed_user		= '';
					$request->review_date		= '';
					$request->authorized_user	= '';
					$request->authorize_date	= '';
					$request->client_name		= '';
					$request->reasonSocial		= '';
					$request->bank_name			= '';
					$request->alias				= '';
					$request->account			= '';
					$request->branch			= '';
					$request->reference			= '';
					$request->clabe				= '';
					$request->currency			= '';
					$request->agreement			= '';
					$request->totalRequest		= '';
					$request->totalProjected	= '';
					$request->subtotalBill		= '';
					$request->trasBill			= '';
					$request->retBill			= '';
					$request->totalBill			= '';
					$request->subtotalPaid		= '';
					$request->trasPaid			= '';
					$request->retPaid			= '';
					$request->totalPaid			= '';
					$request->incomePendingPay	= '';
					$request->unbilledIncome	= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
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

	public function incomeExcelWithoutGrouping(Request $request)
	{
		if (Auth::user()->module->where('id',159)->count()>0)
		{
			$idEnterprise	= $request->idEnterprise;
			$idProject		= $request->idProject;
			$name			= $request->name;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$status			= $request->status;

			$requests		= DB::table('request_models')->selectRaw(
							'
								request_models.folio as folio,
								status_requests.description as status,
								incomes.title as title,
								IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
								CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
								CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
								DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
								requestEnterprise.name as enterprise_name,
								requestProject.proyectName as project_name,
								CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
								DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
								CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
								DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
								clients.businessName as client_name,
								requestEnterprise.name as reasonSocial,
								banks.description as bank_name,
								banks_accounts.alias as alias,
								banks_accounts.account as account,
								banks_accounts.branch as branch,
								banks_accounts.reference as reference,
								banks_accounts.clabe as clabe,
								banks_accounts.currency as currency,
								banks_accounts.agreement as agreement,
								income_details.quantity as quantity,
								income_details.unit as unit,
								income_details.description as concept,
								income_details.unitPrice as unitPrice,
								income_details.subtotal as subtotal,
								income_details.tax as tax,
								IFNULL(taxes_incomes.taxes_amount,0) as taxes,
								IFNULL(retention_incomes.retentions_amount,0) as retentions,
								income_details.amount as amount,
								incomes.amount as totalRequest,
								incomes.amount as totalProjected,
								IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
								IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
								IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
								IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
								IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
								IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
							')
							->leftJoin('incomes','incomes.idFolio','request_models.folio')
							->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
							->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
							->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
							->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
							->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
							->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
							->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
							->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
							->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
							->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
							->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
							->leftJoin('clients','clients.idClient','incomes.idClient')
							->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
							->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
							->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
							->where('request_models.kind',10)
							->whereIn('request_models.status',[4,5,10,11,12])
							->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(159)->pluck('enterprise_id'))
							->where(function ($query) use ($name, $mindate, $maxdate,$idEnterprise,$idProject,$status)
							{
								if ($idEnterprise != "")
								{								
									$query->where(function($q) use($idEnterprise)
									{
										$q->whereIn('request_models.idEnterprise',$idEnterprise)->orWhereIn('request_models.idEnterpriseR',$idEnterprise);
									});
								}
								if ($idProject != "")
								{								
									$query->whereIn('request_models.idProject',$idProject);
								}
								if($name != "")
								{
									$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.$name.'%');
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

			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Autorización de Proyección de Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if($k == 'quantity')
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
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

	public function incomeDetail(Request $request)
	{
		if($request->ajax())
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.finanzas.modal.income_detail')->with('request',$request);
		}
	}
}
