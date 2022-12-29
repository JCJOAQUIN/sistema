<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Genkgo\Xsl\XsltProcessor;
use Carbon\Carbon;
use PDF;
use Lang;
use Illuminate\Support\Facades\Mail;
use Excel;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionFacturacionController extends Controller
{
	private $module_id = 146;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
			return redirect('/');
		}
	}

	public function stamped(Request $request)
	{
		if(Auth::user()->module->where('id',154)->count() > 0)
		{
			$folio               = $request->folio;
			$folioRequest        = $request->folioRequest;
			$concept             = $request->concept;
			$mindate             = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate             = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid        = $request->enterpriseid;
			$kind                = $request->kind;
			$clientRfc           = $request->clientRfc;
			$employerRegister_id = $request->employerRegister_id;
			$periodicity         = $request->periodicity;
			$weekOfYear          = $request->weekOfYear;
			$year                = date('Y');
			$initRange           = App\Http\Controllers\AdministracionFacturacionController::initDate($year,$weekOfYear);
			$endRange            = App\Http\Controllers\AdministracionFacturacionController::endDate($year,$weekOfYear);
			//$project_id 		 = $request->project_id;
			$pending             = App\Bill::whereIn('status',[1,2])
				->whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc'))
				->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$folioRequest,$clientRfc,$employerRegister_id,$weekOfYear,$initRange,$endRange,$periodicity,$concept)
				{
					//if($project_id != '')
					//{
					//	$q->whereIn('idProject', $project_id);
					//}
					if($clientRfc != '')
					{
						$q->where('clientRfc', 'LIKE', '%'.preg_replace("/\s+/", "%", $clientRfc).'%')
							->orWhere('clientBusinessName', 'LIKE', '%'.preg_replace("/\s+/", "%", $clientRfc).'%');
					}
					if($folio != '')
					{
						$q->where('folio', $folio);
					}
					if($folioRequest != '')
					{
						$q->where('folioRequest', $folioRequest);
					}
					if($concept != '')
					{
						$q->whereHas('billDetail', function ($q) use ($concept)
						{
							$q->whereRaw('description LIKE "%'.$concept.'%"');
						});
					}
					if($enterpriseid != "")
					{
						$q->where('rfc', App\Enterprise::find($enterpriseid)->rfc);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('expeditionDateCFDI', [$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($weekOfYear != "")
					{
						$q->whereBetween('expeditionDateCFDI', [''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
					}
					if($kind != "")
					{
						$q->whereIn('type',$kind);
					}
					if($employerRegister_id != "")
					{
						$q->whereHas('nomina', function($nom) use ($employerRegister_id)
						{
							$nom->whereIn('employer_register', $employerRegister_id);
						});	
					}
					if($periodicity != "")
					{
						$q->whereHas('nominaReceiver', function($nom) use ($periodicity)
						{
							$nom->whereIn('periodicity', $periodicity);
						});
					}
				})
				->orderBy('expeditionDate','DESC')
				->paginate(20);
			$data = App\Module::find($this->module_id);
			return view('administracion.facturacion.timbrado',
				[
					'id'                  => $data['father'],
					'title'               => $data['name'],
					'details'             => $data['details'],
					'child_id'            => $this->module_id,
					'option_id'           => 154,
					'pending'             => $pending,
					'folio'               => $folio,
					'folioRequest'        => $folioRequest,
					'concept'             => $concept,
					'mindate'			  => $request->mindate,
					'maxdate'			  => $request->maxdate,
					'enterpriseid'        => $enterpriseid,
					'kind'                => $kind,
					'clientRfc'           => $clientRfc,
					'employerRegister_id' => $employerRegister_id,
					'weekOfYear'          => $weekOfYear,
					'periodicity'         => $periodicity,
					//'project_id'		  => $project_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function reportStampedConsolidated(Request $request)
	{
		if(Auth::user()->module->where('id',154)->count()>0)
		{
			$folio               = $request->folio;
			$folioRequest        = $request->folioRequest;
			$concept             = $request->concept;
			$mindate			 = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			 = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid        = $request->enterpriseid;
			$kind                = $request->kind;
			$clientRfc           = $request->clientRfc;
			$employerRegister_id = $request->employerRegister_id;
			$periodicity         = $request->periodicity;
			$weekOfYear          = $request->weekOfYear;
			$year                = date('Y');
			$initRange           = App\Http\Controllers\AdministracionFacturacionController::initDate($year,$weekOfYear);
			$endRange            = App\Http\Controllers\AdministracionFacturacionController::endDate($year,$weekOfYear);
			//$project_id 		 = $request->project_id;
			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bills.stampDate as stampDate,
								bills.subtotal as subtotal,
								bills.discount as discount,
								bills.tras as tras,
								bills.ret as ret,
								bills.total as total,
								cat_payment_methods.description,
								bills.folio as folio,
								bills.serie as serie,
								bills.uuid as uuid,
								bills.statusCFDI as statusCFDI,
								related_bills.idRelated as rel_idRelated,
								billRelated.uuid as rel_uuid,
								billRelated.statusCFDI as rel_statusCFDI,
								related_bills.amount as rel_amount,
								paymentMethodRelated.description as rel_description
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('cat_payment_methods','cat_payment_methods.paymentMethod','bills.paymentMethod')
							->leftJoin('related_bills','related_bills.idBill','bills.idBill')
							->leftJoin('bills as billRelated','billRelated.idBill','related_bills.idRelated')
							->leftJoin('cat_payment_methods as paymentMethodRelated','paymentMethodRelated.paymentMethod','billRelated.paymentMethod')
							->whereIn('bills.status',[1,2])
							->whereIn('bills.rfc',App\Enterprise::whereIn('id', Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio, $mindate, $maxdate, $enterpriseid, $kind, $folioRequest, $clientRfc, $employerRegister_id, $weekOfYear, $initRange, $endRange, $periodicity,$concept)
							{
								if($clientRfc != '')
								{
									$q->where('bills.clientRfc', 'LIKE', '%'.preg_replace("/\s+/", "%", $clientRfc).'%')
										->orWhere('bills.clientBusinessName', 'LIKE','%'.preg_replace("/\s+/", "%", $clientRfc).'%');
								}
								if($folio != '')
								{
									$q->where('bills.folio', $folio);
								}
								if($folioRequest != '')
								{
									$q->where('bills.folioRequest', $folioRequest);
								}
								if($concept != '')
								{
									$q->whereHas('billDetail', function ($q) use ($concept)
									{
										$q->whereRaw('description LIKE "%'.$concept.'%"');
									});
								}
								if($enterpriseid != "")
								{
									$q->where('bills.rfc', App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.expeditionDateCFDI', [''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($weekOfYear != "")
								{
									$q->whereBetween('bills.expeditionDateCFDI', [''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type', $kind);
								}
								if($employerRegister_id != "")
								{
									$q->whereHas('nomina', function($nom) use ($employerRegister_id)
									{
										$nom->whereIn('employer_register', $employerRegister_id);
									});	
								}
								if($periodicity != "")
								{
									$q->whereHas('nominaReceiver', function($nom) use ($periodicity)
									{
										$nom->whereIn('periodicity', $periodicity);
									});	
								}
							})
							->orderBy('bills.expeditionDateCFDI', 'DESC')
							->get();

			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Consolidado.xlsx');
			$writer->getCurrentSheet()->setName('Timbrado');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CDFI', 'Folio Solicitud', 'Proyecto', 'Emisor (RFC)', 'Receptor (RFC)', 'Tipo', 'Fecha', 'Subtotal', 'Descuento', 'Trasladados', 'Retenidos', 'Total', 'Método de Pago', 'Folio Fiscal', 'Serie', 'UUID','Estatus', 'CDFI Relacionado(s)', 'UUID Relacionado','Estatus','Monto pagado','Método de Pago'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill     = '';
			$kindRow       = true;
			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';
					$bill->stampDate		= '';
					$bill->subtotal			= '';
					$bill->discount			= '';
					$bill->tras				= '';
					$bill->ret				= '';
					$bill->total			= '';
					$bill->description		= '';
					$bill->folio			= '';
					$bill->serie			= '';
					$bill->uuid				= '';
					$bill->statusCFDI		= '';
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['subtotal','discount','tras','ret','total','rel_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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

	public function reportStampedDetailed(Request $request)
	{
		if(Auth::user()->module->where('id',154)->count()>0)
		{
			$folio               = $request->folio;
			$folioRequest        = $request->folioRequest;
			$concept             = $request->concept;
			$mindate			 = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			 = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid        = $request->enterpriseid;
			$kind                = $request->kind;
			$clientRfc           = $request->clientRfc;
			$employerRegister_id = $request->employerRegister_id;
			$weekOfYear          = $request->weekOfYear;
			$periodicity         = $request->periodicity;
			$year                = date('Y');
			$initRange           = App\Http\Controllers\AdministracionFacturacionController::initDate($year,$weekOfYear);
			$endRange            = App\Http\Controllers\AdministracionFacturacionController::endDate($year,$weekOfYear);

			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								bills.folio as folio,
								bills.serie as serie,
								bills.uuid as uuid,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bill_details.idBillDetail as idBillDetail,
								bill_details.keyProdServ as keyProdServ,
								bill_details.keyUnit as keyUnit,
								bill_details.quantity as quantity,
								bill_details.description as description,
								bill_details.value as value,
								bill_details.amount as amount,
								bill_details.discount as discount,
								bill_taxes.type as taxes_type,
								cat_taxes.description as cfdi_tax,
								bill_taxes.quota as taxes_quota,
								bill_taxes.quotaValue as taxes_quotaValue,
								bill_taxes.amount as taxes_amount
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('bill_details','bill_details.idBill','bills.idBill')
							->leftJoin('bill_taxes','bill_taxes.idBillDetail','bill_details.idBillDetail')
							->leftJoin('cat_taxes','cat_taxes.tax','bill_taxes.tax')
							->whereIn('bills.status',[1,2])
							->whereIn('bills.rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$folioRequest,$clientRfc,$employerRegister_id,$weekOfYear,$initRange,$endRange,$periodicity,$concept)
							{
								if($clientRfc!='')
								{
									$q->where('bills.clientRfc','LIKE','%'.preg_replace("/\s+/", "%", $clientRfc).'%')
										->orWhere('bills.clientBusinessName','LIKE','%'.preg_replace("/\s+/", "%", $clientRfc).'%');
								}
								if($folio!='')
								{
									$q->where('bills.folio',$folio);
								}
								if($folioRequest!='')
								{
									$q->where('bills.folioRequest',$folioRequest);
								}
								if($concept != '')
								{
									$q->whereHas('billDetail', function ($q) use ($concept)
									{
										$q->whereRaw('description LIKE "%'.$concept.'%"');
									});
								}
								if ($enterpriseid != "") 
								{
									$q->where('bills.rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.expeditionDateCFDI',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($weekOfYear != "")
								{
									$q->whereBetween('bills.expeditionDateCFDI',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type',$kind);
								}
								if ($employerRegister_id != "") 
								{
									$q->whereHas('nomina',function($nom) use ($employerRegister_id)
									{
										$nom->whereIn('employer_register',$employerRegister_id);
									});	
								}
								if($periodicity != "")
								{
									$q->whereHas('nominaReceiver',function($nom) use ($periodicity)
									{
										$nom->whereIn('periodicity',$periodicity);
									});	
								}
							})
							->orderBy('bills.expeditionDateCFDI','DESC')
							->get();

			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Detallado.xlsx');
			$writer->getCurrentSheet()->setName('Timbrado');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CFDI','Folio Solicitud','Folio Fiscal','Serie','UUID','Proyecto','Emisor (RFC)','Receptor (RFC)','Tipo', 'ID Concepto','Clave de producto o servicio', 'Clave de unidad', 'Cantidad', 'Descripción', 'Unitario', 'Importe', 'Descuento', 'Tipo', 'Impuesto', 'Tasa', 'Valor de Tasa', 'Importe'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill			= '';
			$tempIdBillDetail	= '';
			$kindRow			= true;

			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';
					$bill->folio			= '';
					$bill->serie			= '';
					$bill->uuid				= '';

					if ($tempIdBillDetail != $bill->idBillDetail)
					{
						$tempIdBillDetail = $bill->idBillDetail;
					}
					else
					{
						$bill->idBillDetail	= null;
						$bill->keyProdServ	= '';
						$bill->keyUnit		= '';
						$bill->quantity		= '';
						$bill->description	= '';
						$bill->value		= '';
						$bill->amount		= '';
						$bill->discount		= '';
					}
					
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['value','amount','discount','taxes_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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

	public function cancelled(Request $request)
	{
		if(Auth::user()->module->where('id',155)->count()>0)
		{
			$folio        = $request->folio;
			$mindate      = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$status       = $request->status;
			$clientRfc    = $request->clientRfc;
			$pending      = App\Bill::whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc'))
				->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$status,$clientRfc)
				{
					if($clientRfc!='')
					{
						$q->where('clientRfc','LIKE','%'.$clientRfc.'%')
							->orWhere('clientBusinessName','LIKE','%'.$clientRfc.'%');
					}
					if($folio!='')
					{
						$q->where('folio',$folio);
					}
					if ($enterpriseid != "") 
					{
						$q->where('rfc',App\Enterprise::find($enterpriseid)->rfc);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('CancelledDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($status!='')
					{
						$q->whereIn('status',$status);
					}
					else
					{
						$q->whereIn('status',[3,4]);
					}
					if($kind != "")
					{
						$q->whereIn('type',$kind);
					}
				})
				->orderBy('cancelRequestDate','DESC')
				->paginate(20);
			$data			= App\Module::find($this->module_id);
			return view('administracion.facturacion.cancelado',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 155,
					'pending'      => $pending,
					'folio'        => $folio,
					'mindate'	   => $request->mindate,
					'maxdate'	   => $request->maxdate,
					'enterpriseid' => $enterpriseid,
					'kind'         => $kind,
					'status'       => $status,
					'clientRfc'    => $clientRfc
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function reportCancelledConsolidated(Request $request)
	{
		if(Auth::user()->module->where('id',155)->count()>0)
		{
			$folio        = $request->folio;
			$mindate	  = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	  = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$status       = $request->status;
			$clientRfc    = $request->clientRfc;
			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bills.stampDate as stampDate,
								bills.subtotal as subtotal,
								bills.discount as discount,
								bills.tras as tras,
								bills.ret as ret,
								bills.total as total,
								cat_payment_methods.description,
								bills.folio as folio,
								bills.serie as serie,
								bills.uuid as uuid,
								bills.statusCFDI as statusCFDI,
								related_bills.idRelated as rel_idRelated,
								billRelated.uuid as rel_uuid,
								billRelated.statusCFDI as rel_statusCFDI,
								related_bills.amount as rel_amount,
								paymentMethodRelated.description as rel_description
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('cat_payment_methods','cat_payment_methods.paymentMethod','bills.paymentMethod')
							->leftJoin('related_bills','related_bills.idBill','bills.idBill')
							->leftJoin('bills as billRelated','billRelated.idBill','related_bills.idRelated')
							->leftJoin('cat_payment_methods as paymentMethodRelated','paymentMethodRelated.paymentMethod','billRelated.paymentMethod')
							->whereIn('bills.rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$status,$clientRfc)
							{
								if($clientRfc!='')
								{
									$q->where('bills.clientRfc','LIKE','%'.$clientRfc.'%')
										->orWhere('bills.clientBusinessName','LIKE','%'.$clientRfc.'%');
								}
								if($folio!='')
								{
									$q->where('bills.folio',$folio);
								}
								if ($enterpriseid != "") 
								{
									$q->where('bills.rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.CancelledDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($status!='')
								{
									$q->whereIn('bills.status',$status);
								}
								else
								{
									$q->whereIn('bills.status',[3,4]);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type',$kind);
								}
							})
							->orderBy('bills.cancelRequestDate','DESC')
							->get();

			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Consolidado.xlsx');
			$writer->getCurrentSheet()->setName('Cancelados');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CDFI', 'Folio Solicitud', 'Proyecto', 'Emisor (RFC)', 'Receptor (RFC)', 'Tipo', 'Fecha', 'Subtotal', 'Descuento', 'Trasladados', 'Retenidos', 'Total', 'Método de Pago', 'Folio Fiscal', 'Serie', 'UUID','Estatus', 'CDFI Relacionado(s)', 'UUID Relacionado','Estatus','Monto pagado','Método de Pago'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill     = '';
			$kindRow       = true;
			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';
					$bill->stampDate		= '';
					$bill->subtotal			= '';
					$bill->discount			= '';
					$bill->tras				= '';
					$bill->ret				= '';
					$bill->total			= '';
					$bill->description		= '';
					$bill->folio			= '';
					$bill->serie			= '';
					$bill->uuid				= '';
					$bill->statusCFDI		= '';
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['subtotal','discount','tras','ret','total','rel_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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

	public function reportCancelledDetailed(Request $request)
	{
		if(Auth::user()->module->where('id',155)->count()>0)
		{
			$folio        = $request->folio;
			$mindate	  = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	  = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$status       = $request->status;
			$clientRfc    = $request->clientRfc;
			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								bills.folio as folio,
								bills.serie as serie,
								bills.uuid as uuid,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bill_details.idBillDetail as idBillDetail,
								bill_details.keyProdServ as keyProdServ,
								bill_details.keyUnit as keyUnit,
								bill_details.quantity as quantity,
								bill_details.description as description,
								bill_details.value as value,
								bill_details.amount as amount,
								bill_details.discount as discount,
								bill_taxes.type as taxes_type,
								cat_taxes.description as cfdi_tax,
								bill_taxes.quota as taxes_quota,
								bill_taxes.quotaValue as taxes_quotaValue,
								bill_taxes.amount as taxes_amount
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('bill_details','bill_details.idBill','bills.idBill')
							->leftJoin('bill_taxes','bill_taxes.idBillDetail','bill_details.idBillDetail')
							->leftJoin('cat_taxes','cat_taxes.tax','bill_taxes.tax')
							->whereIn('bills.rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$status,$clientRfc)
							{
								if($clientRfc!='')
								{
									$q->where('bills.clientRfc','LIKE','%'.$clientRfc.'%')
										->orWhere('bills.clientBusinessName','LIKE','%'.$clientRfc.'%');
								}
								if($folio!='')
								{
									$q->where('bills.folio',$folio);
								}
								if ($enterpriseid != "") 
								{
									$q->where('bills.rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.CancelledDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($status!='')
								{
									$q->whereIn('bills.status',$status);
								}
								else
								{
									$q->whereIn('bills.status',[3,4]);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type',$kind);
								}
							})
							->orderBy('bills.cancelRequestDate','DESC')
							->get();
			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Detallado.xlsx');
			$writer->getCurrentSheet()->setName('Cancelado');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CFDI','Folio Solicitud','Folio Fiscal','Serie','UUID','Proyecto','Emisor (RFC)','Receptor (RFC)','Tipo', 'ID Concepto','Clave de producto o servicio', 'Clave de unidad', 'Cantidad', 'Descripción', 'Unitario', 'Importe', 'Descuento', 'Tipo', 'Impuesto', 'Tasa', 'Valor de Tasa', 'Importe'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill			= '';
			$tempIdBillDetail	= '';
			$kindRow			= true;

			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';
					$bill->folio			= '';
					$bill->serie			= '';
					$bill->uuid				= '';

					if ($tempIdBillDetail != $bill->idBillDetail)
					{
						$tempIdBillDetail = $bill->idBillDetail;
					}
					else
					{
						$bill->idBillDetail	= null;
						$bill->keyProdServ	= '';
						$bill->keyUnit		= '';
						$bill->quantity		= '';
						$bill->description	= '';
						$bill->value		= '';
						$bill->amount		= '';
						$bill->discount		= '';
					}
					
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['value','amount','discount','taxes_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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

	public function cfdiRelated(Request $request)
	{
		if($request->ajax())
		{
			$choosen = '';
			if($request->choosen != '')
			{
				$choosen = App\Bill::where('statusCFDI','Vigente')
				->whereIn('idBill',array_keys($request->choosen))
				->get();
			}
			return view('partials.cfdi_search', ['choosen' => $choosen, 'choosen_rel' => $request->choosen, 'cfdi_kind' => $request->cfdi_kind, 'cfdi_version' => $request->cfdi_version, 'income' => $request->income, 'option_id' => $request->option_id]);
		}
	}

	public function cfdiRelatedSearch(Request $request)
	{
		if($request->ajax())
		{
			$rfc        = $request->emiter_cfdi_search;
			$date_start = $request->min_date_cfdi != '' ? Carbon::createFromFormat('d-m-Y',$request->min_date_cfdi) : null;
			$date_end   = $request->max_date_cfdi != '' ? Carbon::createFromFormat('d-m-Y',$request->max_date_cfdi) : null;
			$receptor   = $request->receptor_cfdi_search;
			$selected   = '';
			$choosen    = $request->cfdi_rel;
			$option_id  = $request->option_id;
			$income     = $request->income;
			if($request->cfdi_rel != '')
			{
				$selected = App\Bill::where('uuid','!=',NULL)
				->whereIn('idBill',$request->cfdi_rel)
				->get();
			}
			if(isset($income) && $income != '')
			{
				$requestModel = App\RequestModel::find($income);
				switch ($requestModel->kind)
				{
					case 11:
						$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->adjustment->first()->enterpriseOrigin->id)->pluck('rfc');
						break;
					case 13:
						$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->purchaseEnterprise->first()->enterpriseDestiny->id)->pluck('rfc');
						break;
					case 14:
						$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->groups->first()->enterpriseDestiny->id)->pluck('rfc');
						break;
					default:
						$enterprises = App\Enterprise::orderName()->where('status','ACTIVE')->where('id',$requestModel->idEnterprise)->pluck('rfc');
						break;
				}
			}
			else
			{
				$enterprises = App\Enterprise::whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->pluck('rfc');
			}
			$result	= App\Bill::where('uuid','!=',NULL)
				->whereIn('rfc', $enterprises)
				->where(function($q) use ($rfc, $date_start, $date_end, $receptor, $choosen, $income)
				{
					if($rfc != '')
					{
						$q->whereIn('rfc',$rfc);
					}
					if($date_start != '' && $date_end != '')
					{
						$q->whereBetween('stampDate',[$date_start->format('Y-m-d 00:00:00'),$date_end->format('Y-m-d 23:59:59')]);
					}
					if($receptor != '')
					{
						$q->where('clientRfc',$receptor);
					}
					if($choosen != '')
					{
						$q->whereNotIn('idBill',$choosen);
					}
					if($income != '')
					{
						$q->where('folioRequest',$income);
					}
				})
				->paginate(15);
			return view('partials.cfdi_search_result',['selected' => $selected, 'selected_opt' => $request->cfdi_rel_kind, 'result' => $result, 'cfdi_kind' => $request->cfdi_kind, 'cfdi_version' =>  $request->cfdi_version]);
		}
	}

	public function downloadPDF($uuid)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->orWhereIn('id',Auth::user()->inChargeEnt(167)->pluck('enterprise_id'))->pluck('rfc')),true);
		//return $restriction;
		if(Auth::user()->module->whereIn('id',[154,167])->count()>0 && in_array(App\Bill::where('uuid',$uuid)->first()->rfc, $restriction))
		{
			if(\Storage::disk('reserved')->exists('/stamped/'.$uuid.'.pdf'))
			{
				return \Storage::disk('reserved')->download('/stamped/'.$uuid.'.pdf');
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function downloadCancelledPDF($uuid)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',155)->count()>0 && in_array(App\Bill::where('uuid',$uuid)->first()->rfc, $restriction))
		{
			if(\Storage::disk('reserved')->exists('/cancelled/'.$uuid.'_acuse.pdf'))
			{
				return \Storage::disk('reserved')->download('/cancelled/'.$uuid.'_acuse.pdf');
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function downloadXML($uuid)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->orWhereIn('id',Auth::user()->inChargeEnt(167)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->whereIn('id',[154,167])->count()>0 && in_array(App\Bill::where('uuid',$uuid)->first()->rfc, $restriction))
		{
			if(\Storage::disk('reserved')->exists('/stamped/'.$uuid.'.xml'))
			{
				return \Storage::disk('reserved')->download('/stamped/'.$uuid.'.xml', $uuid.'.xml', ['Content-Description' =>  'File Transfer','Content-Type' => 'application/xml','Content-Disposition' => 'attachment; filename='.$uuid.'.xml']);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function downloadCancelledXML($uuid)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',155)->count()>0 && in_array(App\Bill::where('uuid',$uuid)->first()->rfc, $restriction))
		{
			if(\Storage::disk('reserved')->exists('/cancelled/'.$uuid.'_acuse.xml'))
			{
				return \Storage::disk('reserved')->download('/cancelled/'.$uuid.'_acuse.xml', $uuid.'_acuse.xml', ['Content-Description' =>  'File Transfer','Content-Type' => 'application/xml','Content-Disposition' => 'attachment; filename='.$uuid.'_acuse.xml']);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function stampedView(App\Bill $bill)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',154)->count()>0 && in_array($bill->rfc, $restriction) && in_array($bill->status, [1,2]))
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.facturacion.ver',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 154,
					'bill'		=> $bill
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function cancelBill(App\Bill $bill, Request $request)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',154)->count()>0 && in_array($bill->rfc, $restriction))
		{
			if(\Storage::disk('reserved')->exists('/cer/'.$bill->rfc.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$bill->rfc.'.key.pem'))
			{
				if($bill->status == 1 && $bill->statusCFDI == 'Vigente')
				{
					if($bill->cancelRequestDate == null)
					{
						$bill->cancelRequestDate	= Carbon::Now()->subMinute(10);
						$bill->save();
					}
					$bill->status				= 5;
					$bill->save();
					$objToCancel				= array();
					$objToCancel['username']	= 'PIM110705A78';
					if(app()->env == 'production')
					{
						$objToCancel['password']	= '2K9c3KvPGHsRqMk36-H8';
						$strUrl						= 'https://sistema.timbox.com.mx/cancelacion/wsdl';
					}
					else
					{
						$objToCancel['password']	= 'GF7vdJNwdxJbxB1ShwX7';
						$strUrl						= 'https://staging.ws.timbox.com.mx/cancelacion/wsdl';
					}
					$objToCancel['rfc_emisor']	= $bill->rfc;
					$objToCancel['folios']		= array(
						array(
							"uuid"            => $bill->uuid,
							"rfc_receptor"    => $bill->clientRfc,
							"total"           => $bill->total,
							"motivo"          => $request->reason,
							"folio_sustituto" => $request->fiscal_folio,
						)
					);
					$objToCancel['cert_pem']	= \Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.cer.pem');
					$objToCancel['llave_pem']	= \Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.key.pem');
					$objWebService				= new \SoapClient($strUrl, array('trace' => 1,'use' => SOAP_LITERAL));
					try
					{
						$responseWS		= $objWebService->__soapCall("cancelar_cfdi", $objToCancel);
						$xmlResponse	= new \DOMDocument();
						$xmlResponse->loadXML($responseWS->folios_cancelacion);
						$uuid			= $xmlResponse->getElementsByTagName('uuid');
						$code			= $xmlResponse->getElementsByTagName('codigo');
						$msj			= $xmlResponse->getElementsByTagName('mensaje');
						switch($code[0]->textContent)
						{
							case '201':
								if($msj[0]->textContent == 'Cancelado Exitosamente')
								{
									$bill->status			= 4;
									$bill->statusCFDI		= 'Cancelado';
									$bill->statusCancelCFDI	= 'Cancelado sin aceptación';
									$bill->save();
								}
								else
								{
									$bill->status			= 3;
									$bill->statusCFDI		= 'Vigente';
									$bill->save();
								}
								$xmlCancelled = new \DOMDocument();
								$xmlCancelled->loadXML($responseWS->acuse_cancelacion);
								$signature                  = $xmlCancelled->firstChild->getElementsByTagName('SignatureValue');
								$acuse                      = $xmlCancelled->firstChild;
								$bill->signatureValueCancel = $signature[0]->textContent;
								$bill->CancelledDate        = str_replace('T', ' ', $acuse->getAttribute('Fecha'));
								$bill->cancellation_reason  = $request->reason;
								$bill->substitute_folio     = $request->fiscal_folio;
								$bill->save();
								$pdf						= PDF::loadView('administracion.facturacion.acuse',['bill'=>$bill]);
								\Storage::disk('reserved')->put('/cancelled/'.$bill->uuid.'_acuse.xml',$responseWS->acuse_cancelacion);
								\Storage::disk('reserved')->put('/cancelled/'.$bill->uuid.'_acuse.pdf',$pdf->stream());
								$alert	= "swal({title:'CFDI cancelado', text:'El CFDI ha sido cancelado.', icon:'success', html:true});";
								break;
							case '202':
								$alert	= "swal({title:'CFDI cancelado: ".$code[0]->textContent."', text:'El CFDI ya se encontraba cancelado.', icon:'warning', html:true});";
								break;
							case '203':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'UUID no corresponde al RFC del Emisor.', icon:'error', html:true});";
								break;
							case '204':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'UUID no aplicable para cancelación.', icon:'error', html:true});";
								break;
							case '205':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'UUID No existe. Si el comprobante acaba de ser timbrado, por favor espere unos minutos para poder cancelarlo.', icon:'error', html:true});";
								break;
							case '206':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'UUID no corresponde a un CFDI del Sector Primario.', icon:'error', html:true});";
								break;
							case '207':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'No se especificó el motivo de cancelación o el motivo no es válido.', icon:'error', html:true});";
								break;
							case '208':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Folio Sustitución invalido.', icon:'error', html:true});";
								break;
							case '209':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Folio Sustitución no requerido.', icon:'error', html:true});";
								break;
							case '210':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'La fecha de solicitud de cancelación es mayor a la fecha de declaración.', icon:'error', html:true});";
								break;
							case '211':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'La fecha de solicitud de cancelación límite para factura global.', icon:'error', html:true});";
								break;
							case '212':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Relación no válida o inexistente.', icon:'error', html:true});";
								break;
							case '304':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Certificado Revocado o Caduco.', icon:'error', html:true});";
								break;
							case 'CANC104':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'UUID no corresponde al RFC del Receptor.', icon:'error', html:true});";
								break;
							case 'CANC105':
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Total no corresponde al CFDI.', icon:'error', html:true});";
								break;
							
							default:
								$alert	= "swal({title:'Error: ".$code[0]->textContent."', text:'Ocurrió un error durante el proceso, por favor verifique con soporte técnico.', icon:'error', html:true});";
								break;
						}
						if($code[0]->textContent != '201' && $code[0]->textContent != '202')
						{
							$bill->status	= 1;
							$bill->save();
						}
						return redirect()->route('bill.stamped',$bill->idBill)->with('alert',$alert);
					}
					catch (\Exception $exception)
					{
						$bill->status	= 1;
						$bill->save();
						$alert    = "swal({title:'Error: ".$exception->getCode()."', text:\"".nl2br($exception->getMessage(),true)."\", icon:'error', html:true});";
						return redirect()->route('bill.stamped',$bill->idBill)->with('alert',$alert);
					}
				}
				else
				{
					$alert	= "swal('', 'El comprobante ya se encuentra cancelado', 'error');";
					return redirect()->route('bill.stamped',$bill->idBill)->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('', 'Ocurrió un error con la configuración de los certificados, por favor contacte a soporte.', 'error');";
				return redirect()->route('bill.stamped',$bill->idBill)->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function cancelledView(App\Bill $bill)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',155)->count()>0 && in_array($bill->rfc, $restriction) && in_array($bill->status, [3,4]))
		{
			$data  = App\Module::find($this->module_id);
			return view('administracion.facturacion.ver',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 155,
					'bill'		=> $bill
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function cancelledStatusUpdate(App\Bill $bill)
	{
		$restriction = json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(155)->pluck('enterprise_id'))->pluck('rfc')),true);
		if(Auth::user()->module->where('id',155)->count()>0 && in_array($bill->rfc, $restriction) && $bill->status==3)
		{
			if(\Storage::disk('reserved')->exists('/cer/'.$bill->rfc.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$bill->rfc.'.key.pem'))
			{
				if($bill->status == 3 && $bill->statusCFDI == 'Vigente')
				{
					$objToCheck				= array();
					$objToCheck['username']	= 'PIM110705A78';
					if(app()->env == 'production')
					{
						$objToCheck['password']	= '2K9c3KvPGHsRqMk36-H8';
						$strUrl						= 'https://sistema.timbox.com.mx/cancelacion/wsdl';
					}
					else
					{
						$objToCheck['password']	= 'GF7vdJNwdxJbxB1ShwX7';
						$strUrl						= 'https://staging.ws.timbox.com.mx/cancelacion/wsdl';
					}
					$objToCheck['uuid']			= $bill->uuid;
					$objToCheck['rfc_emisor']	= $bill->rfc;
					$objToCheck['rfc_receptor']	= $bill->clientRfc;
					$objToCheck['total']		= $bill->total;
					$objWebService				= new \SoapClient($strUrl, array('trace' => 1,'use' => SOAP_LITERAL));
					try
					{
						$responseWS		= $objWebService->__soapCall("consultar_estatus", $objToCheck);
						if($responseWS->estatus_cancelacion == 'Cancelado con aceptación')
						{
							$bill->status			= 4;
							$bill->statusCFDI		= $responseWS->estado;
							$bill->statusCancelCFDI	= $responseWS->estatus_cancelacion;
							$bill->save();
							$pdf = PDF::loadView('administracion.facturacion.acuse',['bill'=>$bill]);
							\Storage::disk('reserved')->put('/cancelled/'.$bill->uuid.'_acuse.pdf',$pdf->stream());
							$alert					= "swal('', 'Se ha autorizado la cancelación del comprobante.', 'success');";
							return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
						}
						elseif($responseWS->estatus_cancelacion == 'En proceso')
						{
							$alert					= "swal('', 'El CFDI aún se encuentra en proceso de cacelación.', 'warning');";
							return back()->with('alert',$alert);
						}
						elseif($responseWS->estatus_cancelacion == 'Solicitud rechazada')
						{
							$bill->status			= 1;
							$bill->statusCFDI		= $responseWS->estado;
							$bill->statusCancelCFDI	= $responseWS->estatus_cancelacion;
							$bill->save();
							$alert					= "swal('', 'Se ha rechazado la cancelación del comprobante y éste pasará a estar «Vigente»', 'warning');";
							return redirect()->route('bill.cancelled')->with('alert',$alert);
						}
						elseif($responseWS->estado == 'Cancelado')
						{
							$bill->status			= 4;
							$bill->statusCFDI		= $responseWS->estado;
							$bill->statusCancelCFDI	= $responseWS->estatus_cancelacion;
							$bill->save();
							$pdf = PDF::loadView('administracion.facturacion.acuse',['bill'=>$bill]);
							\Storage::disk('reserved')->put('/cancelled/'.$bill->uuid.'_acuse.pdf',$pdf->stream());
							$alert					= "swal('', 'Se ha autorizado la cancelación del comprobante', 'success');";
							return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
						}
						else
						{
							$alert = "swal('', '".$responseWS->codigo_estatus."', 'info');";
							return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
						}
					}
					catch (\Exception $exception)
					{
						$alert	= "swal({title:'Error: ".$exception->getCode()."', text:\"".nl2br($exception->getMessage(),true)."\", icon:'error', html:true});";
						return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
					}
				}
				else
				{
					$alert	= "swal('', 'El comprobante ya se encuentra cancelado', 'error');";
					return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('', 'Ocurrió un error con la configuración de los certificados, por favor contacte a soporte.', 'error');";
				return redirect()->route('bill.cancelled.view',$bill->idBill)->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdi()
	{
		if(Auth::user()->module->where('id',158)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('administracion.facturacion.cfdi',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 158
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdiPendingStamp(App\Bill $bill)
	{
		if(Auth::user()->module->where('id',157)->count()>0)
		{
			if($bill->type != 'N' && in_array($bill->status,[0,6,7]))
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.facturacion.cfdi',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 157,
						'bill'		=> $bill
					]
				);
			}
			else
			{
				return abort(404);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdiPending(Request $request)
	{
		if(Auth::user()->module->where('id',157)->count()>0)
		{
			$folio        = $request->folio;
			$mindate      = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$clientRfc    = $request->clientRfc;
			$pending      = App\Bill::where('status',0)
				->where('type','!=','N')
				->whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(157)->pluck('enterprise_id'))->pluck('rfc'))
				->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$clientRfc)
				{
					if($clientRfc!='')
					{
						$q->where('clientRfc','LIKE','%'.$clientRfc.'%')
							->orWhere('clientBusinessName','LIKE','%'.$clientRfc.'%');
					}
					if($folio!='')
					{
						$q->where('folioRequest',$folio);
					}
					if ($enterpriseid != "") 
					{
						$q->where('rfc',App\Enterprise::find($enterpriseid)->rfc);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('expeditionDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($kind != "")
					{
						$q->whereIn('type',$kind);
					}
				})
				->orderBy('expeditionDate','DESC')
				->paginate(20);
			$data = App\Module::find($this->module_id);
			return response(
				view('administracion.facturacion.cfdi_pendiente',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 157,
						'pending'      => $pending,
						'folio'        => $folio,
						'mindate'      => $request->mindate,
						'maxdate'      => $request->maxdate,
						'enterpriseid' => $enterpriseid,
						'kind'         => $kind,
						'clientRfc'    => $clientRfc
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(157), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function reportPendingConsolidated(Request $request)
	{
		if(Auth::user()->module->where('id',157)->count()>0)
		{
			$folio        = $request->folio;
			$folioRequest = $request->folioRequest;
			$mindate	  = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	  = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$clientRfc    = $request->clientRfc;
			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bills.stampDate as stampDate,
								bills.subtotal as subtotal,
								bills.discount as discount,
								bills.tras as tras,
								bills.ret as ret,
								bills.total as total,
								cat_payment_methods.description,
								bills.folio as folio,
								bills.serie as serie,
								bills.uuid as uuid,
								bills.statusCFDI as statusCFDI,
								related_bills.idRelated as rel_idRelated,
								billRelated.uuid as rel_uuid,
								billRelated.statusCFDI as rel_statusCFDI,
								related_bills.amount as rel_amount,
								paymentMethodRelated.description as rel_description
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('cat_payment_methods','cat_payment_methods.paymentMethod','bills.paymentMethod')
							->leftJoin('related_bills','related_bills.idBill','bills.idBill')
							->leftJoin('bills as billRelated','billRelated.idBill','related_bills.idRelated')
							->leftJoin('cat_payment_methods as paymentMethodRelated','paymentMethodRelated.paymentMethod','billRelated.paymentMethod')
							->where('bills.status',0)
							->where('bills.type','!=','N')
							->whereIn('bills.rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(157)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$clientRfc)
							{
								if($clientRfc!='')
								{
									$q->where('bills.clientRfc','LIKE','%'.$clientRfc.'%')
										->orWhere('bills.clientBusinessName','LIKE','%'.$clientRfc.'%');
								}
								if($folio!='')
								{
									$q->where('bills.folioRequest',$folio);
								}
								if ($enterpriseid != "") 
								{
									$q->where('bills.rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.expeditionDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type',$kind);
								}
							})
							->orderBy('bills.idBill','DESC')
							->get();

			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Consolidado.xlsx');
			$writer->getCurrentSheet()->setName('CDFI Pendiente');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CDFI', 'Folio Solicitud', 'Proyecto', 'Emisor (RFC)', 'Receptor (RFC)', 'Tipo', 'Fecha', 'Subtotal', 'Descuento', 'Trasladados', 'Retenidos', 'Total', 'Método de Pago', 'Folio Fiscal', 'Serie', 'UUID','Estatus', 'CDFI Relacionado(s)', 'UUID Relacionado','Estatus','Monto pagado','Método de Pago'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill     = '';
			$kindRow       = true;
			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';
					$bill->stampDate		= '';
					$bill->subtotal			= '';
					$bill->discount			= '';
					$bill->tras				= '';
					$bill->ret				= '';
					$bill->total			= '';
					$bill->description		= '';
					$bill->folio			= '';
					$bill->serie			= '';
					$bill->uuid				= '';
					$bill->statusCFDI		= '';
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['subtotal','discount','tras','ret','total','rel_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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

	public function reportPendingDetailed(Request $request)
	{
		if(Auth::user()->module->where('id',157)->count()>0)
		{
			$folio        = $request->folio;
			$folioRequest = $request->folioRequest;
			$mindate	  = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	  = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid = $request->enterpriseid;
			$kind         = $request->kind;
			$clientRfc    = $request->clientRfc;

			$bills 	= App\Bill::selectRaw('
								bills.idBill as idBill,
								bills.folioRequest as folioRequest,
								IF(bills.folioRequest IS NOT NULL, 
									IF(requestProject.proyectName IS NOT NULL, requestProject.proyectName,
										IF(request_models.kind = 13, projectIncomePurchaseE.proyectName, IF(request_models.kind = 16, projectIncomeGroups.proyectName, ""))), 
									IF(projectBill.proyectName IS NOT NULL, projectBill.proyectName, "") 
								) as project_name,
								bills.rfc as rfc,
								bills.clientRfc as clientRfc,
								cat_type_bills.description as cat_type_bills,
								bill_details.idBillDetail as idBillDetail,
								bill_details.keyProdServ as keyProdServ,
								bill_details.keyUnit as keyUnit,
								bill_details.quantity as quantity,
								bill_details.description as description,
								bill_details.value as value,
								bill_details.amount as amount,
								bill_details.discount as discount,
								bill_taxes.type as taxes_type,
								cat_taxes.description as cfdi_tax,
								bill_taxes.quota as taxes_quota,
								bill_taxes.quotaValue as taxes_quotaValue,
								bill_taxes.amount as taxes_amount
							')
							->leftJoin('cat_type_bills','cat_type_bills.typeVoucher','bills.type')
							->leftJoin('request_models','request_models.folio','bills.folioRequest')
							->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProject')
							->leftJoin('groups','groups.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomeGroups','projectIncomeGroups.idproyect','groups.idProjectOriginR')
							->leftJoin('purchase_enterprises','purchase_enterprises.idFolio','request_models.folio')
							->leftJoin('projects as projectIncomePurchaseE','projectIncomePurchaseE.idproyect','purchase_enterprises.idProjectOriginR')
							->leftJoin('projects as projectBill','projectBill.idproyect','bills.idProject')
							->leftJoin('bill_details','bill_details.idBill','bills.idBill')
							->leftJoin('bill_taxes','bill_taxes.idBillDetail','bill_details.idBillDetail')
							->leftJoin('cat_taxes','cat_taxes.tax','bill_taxes.tax')
							->where('bills.status',0)
							->where('bills.type','!=','N')
							->whereIn('bills.rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(157)->pluck('enterprise_id'))->pluck('rfc'))
							->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$clientRfc)
							{
								if($clientRfc!='')
								{
									$q->where('bills.clientRfc','LIKE','%'.$clientRfc.'%')
										->orWhere('bills.clientBusinessName','LIKE','%'.$clientRfc.'%');
								}
								if($folio!='')
								{
									$q->where('bills.folioRequest',$folio);
								}
								if ($enterpriseid != "") 
								{
									$q->where('bills.rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != "" && $maxdate != "")
								{
									$q->whereBetween('bills.expeditionDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($kind != "")
								{
									$q->whereIn('bills.type',$kind);
								}
							})
							->orderBy('bills.idBill','DESC')
							->get();

			if(count($bills)==0 || is_null($bills))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Detallado.xlsx');
			$writer->getCurrentSheet()->setName('CDFI Pendiente');

			$headers = ['Reporte', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['ID CFDI', 'Folio Solicitud', 'Proyecto', 'Emisor (RFC)', 'Receptor (RFC)', 'Tipo','ID Concepto', 'Clave de producto o servicio', 'Clave de unidad', 'Cantidad', 'Descripción', 'Unitario', 'Importe', 'Descuento', 'Tipo', 'Impuesto', 'Tasa', 'Valor de Tasa', 'Importe'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdBill			= '';
			$tempIdBillDetail	= '';
			$kindRow			= true;

			foreach($bills as $bill)
			{
				if($tempIdBill != $bill->idBill)
				{
					$tempIdBill = $bill->idBill;
					$kindRow = !$kindRow;
				}
				else
				{
					$bill->idBill			= null;
					$bill->folioRequest		= '';
					$bill->project_name		= '';
					$bill->rfc				= '';
					$bill->clientRfc		= '';
					$bill->cat_type_bills	= '';

					if ($tempIdBillDetail != $bill->idBillDetail)
					{
						$tempIdBillDetail = $bill->idBillDetail;
					}
					else
					{
						$bill->idBillDetail	= null;
						$bill->keyProdServ	= '';
						$bill->keyUnit		= '';
						$bill->quantity		= '';
						$bill->description	= '';
						$bill->value		= '';
						$bill->amount		= '';
						$bill->discount		= '';
					}
					
				}
				$tempArray = [];
				foreach($bill->toArray() as $k => $b)
				{
					if(in_array($k,['value','amount','discount','taxes_amount']))
					{
						if($b != '')
						{
							$tempArray[] = WriterEntityFactory::createCell((double)$b,$currencyFormat);
						}
						else
						{
							$tempArray[] = WriterEntityFactory::createCell($b);
						}
					}
					else
					{
						$tempArray[] = WriterEntityFactory::createCell($b);
					}
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tempArray, $alignment);
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


	public function nominaPending(Request $request)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			$mindate      = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			$employee     = $request->employee;
			$status       = $request->status;
			$pending      = App\Bill::whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(180)->pluck('enterprise_id'))->pluck('rfc'))
				->where('type','N')
				->where('folioRequest',NULL)
				->where(function ($q) use($mindate,$maxdate,$enterpriseid,$employee,$status)
				{
					if($enterpriseid != '') 
					{
						$q->where('rfc',App\Enterprise::find($enterpriseid)->rfc);
					}
					if($mindate != '' && $maxdate != '')
					{
						$q->whereBetween('expeditionDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if($status != '')
					{
						$q->whereIn('status',$status);
					}
					else
					{
						$q->whereIn('status',[0,6,7]);
					}
					if($employee != '')
					{
						$q->where('clientBusinessName','LIKE','%'.preg_replace("/\s+/", "%", $employee).'%');
					}
				})
				->orderBy('expeditionDate','DESC')
				->paginate(20);
			$data			= App\Module::find($this->module_id);
			return response(
				view('administracion.facturacion.nomina_pendiente',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 180,
						'pending'		=> $pending,
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'enterpriseid'	=> $enterpriseid,
						'employee'		=> $employee,
						'status'		=> $status
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(180), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportNominaPending(Request $request)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			$mindate		= $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterpriseid	= $request->enterpriseid;
			$employee		= $request->employee;
			$status			= $request->status;
			Excel::create('Reporte de CFDI Nómina', function($excel) use ($mindate, $maxdate, $enterpriseid, $employee, $status)
			{
				$excel->sheet('Nómina',function($sheet) use ($mindate, $maxdate, $enterpriseid, $employee, $status)
					{
						$sheet->setStyle([
								'font' => [
									'name'	=> 'Calibri',
									'size'	=> 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);
						$sheet->setColumnFormat(array(
							'R'		=> '"$"#,##0.00_-',
							'S'		=> '"$"#,##0.00_-',
							'T'		=> '"$"#,##0.00_-',
							'W'		=> 'dd/mm/yy',
							'Z'		=> '"$"#,##0.00_-',
							'AG'	=> 'dd/mm/yy',
							'AH'	=> 'dd/mm/yy',
							'AI'	=> 'dd/mm/yy',
							'AN'	=> '"$"#,##0.00_-',
							'AO'	=> '"$"#,##0.00_-',
							'AS'	=> '"$"#,##0.00_-',
							'AW'	=> '"$"#,##0.00_-',
							'AX'	=> '"$"#,##0.00_-',
							'AY'	=> '"$"#,##0.00_-',
							'AZ'	=> '"$"#,##0.00_-',
						));
						$sheet->mergeCells('A1:AZ1');
						$sheet->mergeCells('A2:T3');
						$sheet->mergeCells('U2:AW2');
						$sheet->mergeCells('U3:AE3');
						$sheet->mergeCells('AF3:AJ3');
						$sheet->mergeCells('AK3:AO3');
						$sheet->mergeCells('AP3:AS3');
						$sheet->mergeCells('AT3:AW3');
						$sheet->mergeCells('AX2:AZ3');
						$sheet->cell('A1:AZ1', function($cells)
						{
							$cells->setBackground('#000000');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A2:T4', function($cells)
						{
							$cells->setBackground('#1d353d');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('AX2:AZ4', function($cells)
						{
							$cells->setBackground('#1d353d');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('U2:AW2', function($cells)
						{
							$cells->setBackground('#1d3c38');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('U3:AE4', function($cells)
						{
							$cells->setBackground('#8275f1');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('AF3:AJ4', function($cells)
						{
							$cells->setBackground('#f8cd5c');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('AK3:AO4', function($cells)
						{
							$cells->setBackground('#7fc544');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('AP3:AS4', function($cells)
						{
							$cells->setBackground('#EE881F');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('AT3:AW4', function($cells)
						{
							$cells->setBackground('#e44c5d');
							$cells->setFontColor('#ffffff');
						});

						$sheet->cell('A1:AZ4', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});

						$sheet->row(1,['Reporte de CFDI de Nómina']);
						$sheet->row(2,['Datos generales del CFDI','','','','','','','','','','','','','','','','','','','','Datos de Nómina','','','','','','','','','','','','','','','','','','','','','','','','','','','','','Datos generales del CFDI','','']);
						$sheet->row(3,['','','','','','','','','','','','','','','','','','','','','Datos complementarios del receptor','','','','','','','','','','','Datos generales de la nómina','','','','','Percepciones','','','','','Deducciones','','','','Otros pagos','','','','','','']);
						$sheet->row(4,['RFC Emisor','Razón social emisor','Régimen fiscal','RFC receptor','Razón social receptor','Uso de CFDI','Tipo de CFDI','Código postal','Forma de pago','Método de pago','Folio','Serie','Condiciones de pago','Clave del producto o servicio','Clave de unidad','Cantidad','Descripción','Valor unitario','Importe','Descuento','CURP','Número de seguridad social','Fecha de inicio de relación laboral','Antigüedad','Riesgo de puesto','Salario diario integrado','Tipo contrato','Tipo régimen','Número de empleado','Periodicidad del pago','Clave entidad federativa','Tipo de nómina','Fecha de pago','Fecha inicial de pago','Fecha final de pago','Número de días pagados','Tipo de percepción','Clave','Concepto','Importe gravado','Importe exento','Tipo de deducción','Clave','Concepto','Importe','Tipo otro pago','Clave','Concepto','Importe','Subtotal','Descuento','Total']);
						$pending	= App\Bill::whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(180)->pluck('enterprise_id'))->pluck('rfc'))
							->where('type','N')
							->where('folioRequest',NULL)
							->where(function ($q) use($mindate,$maxdate,$enterpriseid,$employee,$status)
							{
								if($enterpriseid != '') 
								{
									$q->where('rfc',App\Enterprise::find($enterpriseid)->rfc);
								}
								if($mindate != '' && $maxdate != '')
								{
									$q->whereBetween('expeditionDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($status != '' && $status != 'all')
								{
									$q->where('status',$status);
								}
								else
								{
									$q->whereIn('status',[0,6,7]);
								}
								if($employee != '')
								{
									$q->where('clientBusinessName','LIKE','%'.preg_replace("/\s+/", "%", $employee).'%');
								}
							})
							->get();
						$beginMerge = 4;
						foreach ($pending as $bill)
						{
							$maxTemp	= 0;
							$row		= [];
							$row[]		= $bill->rfc;
							$row[]		= $bill->businessName;
							$row[]		= $bill->taxRegime.' - '.$bill->cfdiTaxRegime->description;
							$row[]		= $bill->clientRfc;
							$row[]		= $bill->clientBusinessName;
							$row[]		= $bill->cfdiUse->description;
							$row[]		= 'Nómina';
							$row[]		= $bill->postalCode;
							$row[]		= $bill->paymentWay.' '.$bill->cfdiPaymentWay->description;
							$row[]		= $bill->paymentMethod.' '.$bill->cfdiPaymentMethod->description;
							$row[]		= $bill->folio;
							$row[]		= $bill->serie;
							$row[]		= $bill->conditions;
							if($bill->billDetail()->exists())
							{
								$row[]	= $bill->billDetail->first()->keyProdServ;
								$row[]	= $bill->billDetail->first()->keyUnit;
								$row[]	= $bill->billDetail->first()->quantity;
								$row[]	= $bill->billDetail->first()->description;
								$row[]	= $bill->billDetail->first()->value;
								$row[]	= $bill->billDetail->first()->amount;
								$row[]	= $bill->billDetail->first()->discount;
							}
							else
							{
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							if($bill->nominaReceiver()->exists())
							{
								$row[]	= $bill->nominaReceiver->curp;
								$row[]	= $bill->nominaReceiver->nss;
								$temp	= '';
								if($bill->nominaReceiver->laboralDateStart != '')
								{
									$temp = new \DateTime($bill->nominaReceiver->laboralDateStart);
									$temp = $temp->format('Y/m/d');
								}
								$row[]	= $temp;
								$row[]	= $bill->nominaReceiver->antiquity;
								$row[]	= $bill->nominaReceiver->nominaPositionRisk->description;
								$row[]	= $bill->nominaReceiver->sdi;
								$row[]	= $bill->nominaReceiver->nominaContract->description;
								$row[]	= $bill->nominaReceiver->nominaRegime->description;
								$row[]	= $bill->nominaReceiver->employee_id;
								$row[]	= $bill->nominaReceiver->nominaPeriodicity->description;
								$row[]	= $bill->nominaReceiver->c_state;
							}
							else
							{
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							if($bill->nomina()->exists())
							{
								$row[] = ($bill->nomina->type == 'O' ? 'Ordinaria' : 'Extraordinaria');
								$temp	= '';
								if($bill->nomina->paymentDate != '')
								{
									$temp = new \DateTime($bill->nomina->paymentDate);
									$temp = $temp->format('Y/m/d');
								}
								$row[]	= $temp;
								$temp	= '';
								if($bill->nomina->paymentStartDate != '')
								{
									$temp	= new \DateTime($bill->nomina->paymentStartDate);
									$temp	= $temp->format('Y/m/d');
								}
								$row[]	= $temp;
								$temp	= '';
								if($bill->nomina->paymentEndDate != '')
								{
									$temp = new \DateTime($bill->nomina->paymentEndDate);
									$temp = $temp->format('Y/m/d');
								}
								$row[]	= $temp;
								$row[]	= $bill->nomina->paymentDays;
							}
							else
							{
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
								$row[]	= '';
							}
							$perCount	= $bill->nomina->nominaPerception->count();
							$dedCount	= $bill->nomina->nominaDeduction->count();
							$otrCount	= $bill->nomina->nominaOtherPayment->count();
							$maxTemp	= $perCount;
							if($dedCount > $maxTemp)
							{
								$maxTemp	= $dedCount;
							}
							if($otrCount > $maxTemp)
							{
								$maxTemp	= $otrCount;
							}
							for ($i = 0; $i < $maxTemp; $i++)
							{ 
								if ($i != 0)
								{
									$row	= array();
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
								}
								else
								{
									$beginMerge++;
								}
								if($i < $perCount)
								{
									$row[]	= $bill->nomina->nominaPerception[$i]->type.' - '.$bill->nomina->nominaPerception[$i]->perception->description;
									$row[]	= $bill->nomina->nominaPerception[$i]->perceptionKey;
									$row[]	= $bill->nomina->nominaPerception[$i]->concept;
									$row[]	= $bill->nomina->nominaPerception[$i]->taxedAmount;
									$row[]	= $bill->nomina->nominaPerception[$i]->exemptAmount;
								}
								else
								{
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
								}
								if($i < $dedCount)
								{
									$row[]	= $bill->nomina->nominaDeduction[$i]->type.' - '.$bill->nomina->nominaDeduction[$i]->deduction->description;
									$row[]	= $bill->nomina->nominaDeduction[$i]->deductionKey;
									$row[]	= $bill->nomina->nominaDeduction[$i]->concept;
									$row[]	= $bill->nomina->nominaDeduction[$i]->amount;
								}
								else
								{
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
								}
								if($i < $otrCount)
								{
									$row[]	= $bill->nomina->nominaOtherPayment[$i]->type.' - '.$bill->nomina->nominaOtherPayment[$i]->otherPayment->description;
									$row[]	= $bill->nomina->nominaOtherPayment[$i]->otherPaymentKey;
									$row[]	= $bill->nomina->nominaOtherPayment[$i]->concept;
									$row[]	= $bill->nomina->nominaOtherPayment[$i]->amount;
								}
								else
								{
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
								}
								if($i == 0)
								{
									$row[]	= $bill->subtotal;
									$row[]	= $bill->discount;
									$row[]	= $bill->total;
								}
								else
								{
									$row[]	= '';
									$row[]	= '';
									$row[]	= '';
								}
								$sheet->appendRow($row);
							}
							$endMerge	= $beginMerge + $maxTemp-1;
							$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
							$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
							$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
							$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
							$sheet->mergeCells('E'.$beginMerge.':E'.$endMerge);
							$sheet->mergeCells('F'.$beginMerge.':F'.$endMerge);
							$sheet->mergeCells('G'.$beginMerge.':G'.$endMerge);
							$sheet->mergeCells('H'.$beginMerge.':H'.$endMerge);
							$sheet->mergeCells('I'.$beginMerge.':I'.$endMerge);
							$sheet->mergeCells('J'.$beginMerge.':J'.$endMerge);
							$sheet->mergeCells('K'.$beginMerge.':K'.$endMerge);
							$sheet->mergeCells('L'.$beginMerge.':L'.$endMerge);
							$sheet->mergeCells('M'.$beginMerge.':M'.$endMerge);
							$sheet->mergeCells('N'.$beginMerge.':N'.$endMerge);
							$sheet->mergeCells('O'.$beginMerge.':O'.$endMerge);
							$sheet->mergeCells('P'.$beginMerge.':P'.$endMerge);
							$sheet->mergeCells('Q'.$beginMerge.':Q'.$endMerge);
							$sheet->mergeCells('R'.$beginMerge.':R'.$endMerge);
							$sheet->mergeCells('S'.$beginMerge.':S'.$endMerge);
							$sheet->mergeCells('T'.$beginMerge.':T'.$endMerge);
							$sheet->mergeCells('U'.$beginMerge.':U'.$endMerge);
							$sheet->mergeCells('V'.$beginMerge.':V'.$endMerge);
							$sheet->mergeCells('W'.$beginMerge.':W'.$endMerge);
							$sheet->mergeCells('X'.$beginMerge.':X'.$endMerge);
							$sheet->mergeCells('Y'.$beginMerge.':Y'.$endMerge);
							$sheet->mergeCells('Z'.$beginMerge.':Z'.$endMerge);
							$sheet->mergeCells('AA'.$beginMerge.':AA'.$endMerge);
							$sheet->mergeCells('AB'.$beginMerge.':AB'.$endMerge);
							$sheet->mergeCells('AC'.$beginMerge.':AC'.$endMerge);
							$sheet->mergeCells('AD'.$beginMerge.':AD'.$endMerge);
							$sheet->mergeCells('AE'.$beginMerge.':AE'.$endMerge);
							$sheet->mergeCells('AF'.$beginMerge.':AF'.$endMerge);
							$sheet->mergeCells('AG'.$beginMerge.':AG'.$endMerge);
							$sheet->mergeCells('AH'.$beginMerge.':AH'.$endMerge);
							$sheet->mergeCells('AI'.$beginMerge.':AI'.$endMerge);
							$sheet->mergeCells('AJ'.$beginMerge.':AJ'.$endMerge);
							$sheet->mergeCells('AX'.$beginMerge.':AX'.$endMerge);
							$sheet->mergeCells('AY'.$beginMerge.':AY'.$endMerge);
							$sheet->mergeCells('AZ'.$beginMerge.':AZ'.$endMerge);
							$beginMerge	= $endMerge;
						}
					});
			})->export('xls');
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaPendingStamp(App\Bill $bill)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			if($bill->type == 'N')
			{
				$data			= App\Module::find($this->module_id);
				return view('administracion.facturacion.nomina',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 180,
						'bill'		=> $bill
					]
				);
			}
			else
			{
				return redirect('/');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaSaveSaved(Request $request, App\Bill $bill)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			$bill->clientRfc          = $request->rfc_receiver;
			$bill->clientBusinessName = $request->business_name_receiver;
			$bill->postalCode         = $request->cp_cfdi;
			$bill->serie              = $request->serie;
			if($bill->version == '4.0')
			{
				$bill->receiver_tax_regime = $request->regime_receiver;
				$bill->receiver_zip_code   = $request->cp_receiver_cfdi;
				$bill->conditions          = null;
				$bill->paymentWay          = null;
			}
			else
			{
				$bill->conditions			= $request->conditions;
			}
			$bill->save();
			$nominaReceiver          = $bill->nominaReceiver;
			$nominaReceiver->curp    = $request->nomina_curp;
			$nominaReceiver->nss     = $request->nss;
			$nominaReceiver->c_state = $request->nomina_state;
			$nominaReceiver->save();
			$nomina       = $bill->nomina;
			$nomina->type = $request->nomina_type;
			$nomina->save();
			$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
			return searchRedirect(180, $alert, 'back');
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaStampSaved(Request $request, App\Bill $bill)
	{
		$restriction	= json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(180)->pluck('enterprise_id'))->pluck('rfc')),true);
		$noCertificado	= App\Enterprise::where('rfc',$bill->rfc)->first()->noCertificado;
		if(Auth::user()->module->where('id',180)->count()>0 && in_array($bill->rfc, $restriction) && $bill->statusCFDI==null && $noCertificado != null)
		{
			$rfc                      = $bill->rfc;
			$bill->clientRfc          = $request->rfc_receiver;
			$bill->clientBusinessName = $request->business_name_receiver;
			$bill->postalCode         = $request->cp_cfdi;
			$bill->serie              = $request->serie;
			if($bill->version == '4.0')
			{
				$bill->receiver_tax_regime = $request->regime_receiver;
				$bill->receiver_zip_code   = $request->cp_receiver_cfdi;
				$bill->conditions          = null;
				$bill->paymentWay          = null;
			}
			else
			{
				$bill->conditions = $request->conditions;
			}
			$bill->save();
			$nominaReceiver				= $bill->nominaReceiver;
			$nominaReceiver->curp		= $request->nomina_curp;
			$nominaReceiver->nss		= $request->nss;
			$nominaReceiver->c_state	= $request->nomina_state;
			$nominaReceiver->save();
			$nomina       = $bill->nomina;
			$nomina->type = $request->nomina_type;
			$nomina->save();
			$request->email_cfdi = '';
			if(\Storage::disk('reserved')->exists('/cer/'.$rfc.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$rfc.'.key.pem'))
			{
				return $this->cfdiStampPac($request, $bill->idBill, $noCertificado,'bill.nomina.pending','bill.nomina.pending.stamp', 180);
			}
			else
			{
				$alert	= "swal('', 'Ocurrió un error con la configuración de los certificados, por favor contacte a soporte.', 'error');";
				return redirect()->route('bill.nomina.pending.stamp', $bill->idBill)->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaAddQueue(Request $request, App\Bill $bill)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			$bill->clientRfc			= $request->rfc_receiver;
			$bill->clientBusinessName	= $request->business_name_receiver;
			$bill->postalCode			= $request->cp_cfdi;
			$bill->serie				= $request->serie;
			$bill->conditions			= $request->conditions;
			$bill->status				= 6;
			$bill->save();
			$nominaReceiver				= $bill->nominaReceiver;
			$nominaReceiver->curp		= $request->nomina_curp;
			$nominaReceiver->c_state	= $request->nomina_state;
			$nominaReceiver->save();
			$nomina						= $bill->nomina;
			$nomina->type				= $request->nomina_type;
			$nomina->save();
			$alert    = "swal('', 'Nómina agregada a la cola de timbrado.', 'success');";
			return searchRedirect(180, $alert, 'back');
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaAddQueueMassive(Request $request)
	{
		if(Auth::user()->module->where('id',180)->count()>0)
		{
			if($request->ajax())
			{
				foreach ($request->id as $id)
				{
					$bill			= App\Bill::find($id);
					$bill->status	= 6;
					$bill->save();
				}
			}
		}
	}

	public function cfdiSave(Request $request)
	{
		if(Auth::user()->module->where('id',158)->count()>0)
		{
			$bill          = new App\Bill();
			$bill->version = str_replace('_','.',env('CFDI_VERSION','3_3'));
			$id            = $this->saveBillCFDI($request,$bill);
			$alert	= "swal('','".Lang::get("messages.request_saved")."', 'success');";	
			return redirect()->route('bill.cfdi.pending.stamp', $bill->idBill)->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdiStamp(Request $request)
	{
		$restriction	= json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(158)->pluck('enterprise_id'))->pluck('rfc')),true);
		$noCertificado	= App\Enterprise::where('rfc',$request->rfc_emitter)->first()->noCertificado;
		if(Auth::user()->module->where('id',158)->count()>0 && in_array($request->rfc_emitter, $restriction))
		{
			$rfc           = $request->rfc_emitter;
			$bill          = new App\Bill();
			$bill->version = str_replace('_','.',env('CFDI_VERSION','3_3'));
			$id            = $this->saveBillCFDI($request,$bill);
			if(\Storage::disk('reserved')->exists('/cer/'.$rfc.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$rfc.'.key.pem'))
			{
				return $this->cfdiStampPac($request, $id, $noCertificado);
			}
			else
			{
				$alert	= "swal('', 'Ocurrió un error con la configuración de los certificados, por favor contacte a soporte.', 'error');";
				return redirect()->route('bill.cfdi.pending.stamp', $bill->idBill)->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdiSaveSaved(Request $request, App\Bill $bill)
	{
		if(Auth::user()->module->where('id',157)->count()>0)
		{
			foreach ($bill->billDetail as $d)
			{
				foreach ($d->taxes as $t)
				{
					$t->delete();
				}
				$d->delete();
			}
			foreach ($bill->paymentComplement as $p)
			{
				$p->delete();
			}
			if($bill->cfdiRelated()->exists())
			{
				foreach($bill->cfdiRelated as $r)
				{
					if($r->taxes()->exists())
					{
						foreach($r->taxes as $t)
						{
							$t->delete();
						}
					}
					$r->delete();
				}
			}
			$id		= $this->saveBillCFDI($request,$bill);
			$alert	= "swal('','".Lang::get("messages.request_updated")."', 'success');";
			return searchRedirect(157, $alert, 'back');
		}
		else
		{
			return redirect('/');
		}
	}

	public function cfdiStampSaved(Request $request, App\Bill $bill)
	{
		$restriction	= json_decode(json_encode(App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(157)->pluck('enterprise_id'))->pluck('rfc')),true);
		$noCertificado	= App\Enterprise::where('rfc',$request->rfc_emitter)->first()->noCertificado;
		if(Auth::user()->module->where('id',157)->count() > 0 && in_array($bill->rfc, $restriction) && $bill->statusCFDI==null && $noCertificado != null)
		{
			$rfc	= $request->rfc_emitter;
			foreach ($bill->billDetail as $d)
			{
				foreach ($d->taxes as $t)
				{
					$t->delete();
				}
				$d->delete();
			}
			foreach ($bill->paymentComplement as $p)
			{
				$p->delete();
			}
			if($bill->cfdiRelated()->exists())
			{
				foreach($bill->cfdiRelated as $r)
				{
					if($r->taxes()->exists())
					{
						foreach($r->taxes as $t)
						{
							$t->delete();
						}
					}
					$r->delete();
				}
			}
			$bill	= App\Bill::find($bill->idBill);
			$id		= $this->saveBillCFDI($request,$bill);
			if(\Storage::disk('reserved')->exists('/cer/'.$rfc.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$rfc.'.key.pem'))
			{
				return $this->cfdiStampPac($request, $id, $noCertificado, "", "", 157);
			}
			else
			{
				$alert	= "swal('', 'Ocurrió un error con la configuración de los certificados, por favor contacte a soporte.', 'error');";
				return redirect()->route('bill.cfdi.pending.stamp', $bill->idBill)->with('alert',$alert);
			}
		}
		else
		{
			$alert = '';
			if(Auth::user()->module->where('id',157)->count() == 0)
			{
				$alert	= "swal('', 'No cuenta con los permisos necesarios para proceder con la acción, por favor verifique con soporte.', 'error');";
			}
			if(!in_array($bill->rfc, $restriction))
			{
				$alert	= "swal('', 'No cuenta con los permisos necesarios para proceder con la acción, por favor verifique con soporte.', 'error');";
			}
			if($bill->statusCFDI != null)
			{
				$alert	= "swal('', 'El estado del CFDI a timbrar ha cambiado y no puede proceder, por favor verifique nuevamente.', 'error');";
			}
			if($noCertificado == null)
			{
				$alert	= "swal('', 'La empresa no cuenta con registro de certificado para timbrado, por favor verifique con soporte.', 'error');";
			}
			return searchRedirect(157, $alert, 'home');
		}
	}

	static function saveBillCFDI($request,$bill)
	{
		$bill->idProject    = $request->project_id;
		$bill->rfc          = $request->rfc_emitter;
		if($request->folio_request != '')
		{
			$bill->folioRequest = $request->folio_request;
		}
		$bill->businessName = $request->business_name_emitter;
		$taxRegime          = App\Enterprise::where('rfc',$request->rfc_emitter)->first()->taxRegime;
		if($taxRegime != '')
		{
			$bill->taxRegime = $taxRegime;
		}
		$bill->issuer_address     = $request->issuer_address_cfdi;
		$bill->clientRfc          = $request->rfc_receiver;
		$bill->clientBusinessName = $request->business_name_receiver;
		$bill->receiver_address   = $request->receiver_address_cfdi;
		if($bill->version == '4.0')
		{
			$bill->receiver_tax_regime = $request->regime_receiver;
			$bill->receiver_zip_code   = $request->cp_receiver_cfdi;
			if($request->cfdi_kind == 'P')
			{
				$bill->export = '01';
			}
			else
			{
				$bill->export = $request->cfdi_export;
			}
		}
		$bill->expeditionDate     = Carbon::Now()->subMinute(6);
		$bill->postalCode         = $request->cp_cfdi;
		$bill->serie              = $request->serie;
		$bill->conditions         = $request->conditions;
		$bill->type               = $request->cfdi_kind;
		$bill->paymentMethod      = $request->cfdi_payment_method;
		$bill->paymentWay         = $request->cfdi_payment_way;
		if($request->cfdi_kind == 'P' || $request->cfdi_kind == 'T')
		{
			$bill->currency = 'XXX';
		}
		else
		{
			$bill->currency = $request->currency_cfdi;
		}
		$bill->exchange = $request->exchange;
		$bill->subtotal = $request->subtotal;
		$bill->discount = $request->discount_cfdi;
		$bill->tras     = $request->tras_total;
		$bill->ret      = $request->ret_total;
		$bill->total    = $request->cfdi_total;
		if($request->cfdi_kind == 'P')
		{
			if($bill->version == '4.0')
			{
				$bill->useBill = 'CP01';
			}
			else
			{
				$bill->useBill = 'P01';
			}
		}
		else
		{
			$bill->useBill = $request->cfdi_use;
		}
		if(isset($request->related_kind_cfdi))
		{
			$bill->related = $request->related_kind_cfdi;
		}
		else
		{
			$bill->related = NULL;
		}
		$bill->save();
		if(isset($request->cfdi_item))
		{
			foreach ($request->cfdi_item as $k => $v)
			{
				$details              = new App\BillDetail();
				$details->keyProdServ = $request->product_id[$k];
				$details->keyUnit     = $request->unity_id[$k];
				if($bill->version == '4.0')
				{
					$details->cat_tax_object_id = $request->tax_object_id[$k];
				}
				$details->quantity    = $request->quantity[$k];
				$details->description = $request->description[$k];
				$details->value       = $request->valueCFDI[$k];
				$details->amount      = $request->amount[$k];
				$details->discount    = $request->discount[$k];
				$details->idBill      = $bill->idBill;//ID BILL
				$details->save();
				if(isset($request->ret[$v]) && is_array($request->ret[$v]))
				{
					foreach ($request->ret[$v] as $retK => $retV)
					{
						$taxes               = new App\BillTaxes();
						$taxes->base         = $details->amount - $details->discount;
						$taxes->quota        = $request->ret_fee[$v][$retK];
						$taxes->quotaValue   = $request->ret_tax_fee[$v][$retK];
						$taxes->amount       = $request->ret_total_tax[$v][$retK];
						$taxes->tax          = $request->ret[$v][$retK];
						$taxes->type         = 'Retención';
						$taxes->idBillDetail = $details->idBillDetail;//ID DETAIL BILL
						$taxes->save();
					}
				}
				if(isset($request->tras[$v]) && is_array($request->tras[$v]))
				{
					foreach ($request->tras[$v] as $trasK => $trasV)
					{
						$taxes               = new App\BillTaxes();
						$taxes->base         = $details->amount - $details->discount;
						$taxes->quota        = $request->tras_fee[$v][$trasK];
						$taxes->quotaValue   = $request->tras_tax_fee[$v][$trasK];
						$taxes->amount       = $request->tras_total_tax[$v][$trasK];
						$taxes->tax          = $request->tras[$v][$trasK];
						$taxes->type         = 'Traslado';
						$taxes->idBillDetail = $details->idBillDetail;//ID DETAIL BILL
						$taxes->save();
					}
				}
			}
		}
		if($request->cfdi_kind == 'P' && !isset($request->cfdi_related_id))
		{
			$payComplement              = new App\BillPayment();
			$payComplement->idBill      = $bill->idBill;
			$datePayment				= $request->cfdi_payment_date != "" ? Carbon::createFromFormat('d-m-Y',$request->cfdi_payment_date)->format('Y-m-d') : null;
			$payComplement->paymentDate = $datePayment;
			$payComplement->paymentWay  = $request->cfdi_payment_payment_way;
			$payComplement->currency    = $request->cfdi_payment_currency;
			$payComplement->exchange    = $request->cfdi_payment_exchange;
			$payComplement->amount      = $request->cfdi_payment_amount;
			$payComplement->save();
		}
		if(isset($request->cfdi_related_id))
		{
			if($request->cfdi_kind == 'P')
			{
				$payComplement = new App\BillPayment();
				$payComplement->idBill      = $bill->idBill;
				$datePayment				= $request->cfdi_payment_date != "" ? Carbon::createFromFormat('d-m-Y',$request->cfdi_payment_date)->format('Y-m-d') : null;
				$payComplement->paymentDate = $datePayment;
				$payComplement->paymentWay  = $request->cfdi_payment_payment_way;
				$payComplement->currency    = $request->cfdi_payment_currency;
				$payComplement->exchange    = $request->cfdi_payment_exchange;
				$payComplement->amount      = $request->cfdi_payment_amount;
				$payComplement->save();
				foreach($request->cfdi_payment_related_id as $k => $rel)
				{
					$related                = new App\RelatedBill();
					$related->idBill        = $bill->idBill;
					$related->idRelated     = $rel;
					$related->partial       = $request->cfdi_payment_partial_number[$k];
					$related->prevBalance   = $request->cfdi_payment_last_amount[$k];
					$related->amount        = $request->cfdi_payment_comp_amount[$k];
					$related->unpaidBalance = $request->cfdi_payment_insolute[$k];
					if($bill->version == '4.0')
					{
						$related->cat_tax_object_id = $request->cfdi_payment_objeto_imp[$k];
						$related->cat_relation_id   = $request->cfdi_related_kind[$k];
					}
					$related->save();
					if($bill->version == '4.0')
					{
						if($request->cfdi_payment_related_taxes[$k] != '')
						{
							$paymentTaxes = json_decode($request->cfdi_payment_related_taxes[$k],true);
							if(count($paymentTaxes) > 0)
							{
								foreach($paymentTaxes as $pTax)
								{
									$taxes                  = new App\BillTaxes();
									$taxes->base            = $pTax['base'];
									$taxes->quota           = $pTax['fee'][0];
									$taxes->quotaValue      = $pTax['tax_fee'];
									$taxes->amount          = $pTax['tax_total'];
									$taxes->tax             = $pTax['tax_name'][0];
									$taxes->type            = $pTax['tax_kind'][0];
									$taxes->related_bill_id = $related->id;
									$taxes->save();
								}
							}
						}
					}
				}
			}
			else
			{
				foreach($request->cfdi_related_id as $k => $rel)
				{
					$related              = new App\RelatedBill();
					$related->idBill      = $bill->idBill;
					$related->idRelated   = $rel;
					if($bill->version == '4.0')
					{
						$related->cat_relation_id   = $request->cfdi_related_kind[$k];
					}
					$related->save();
				}
			}
		}
		return $bill->idBill;
	}

	public function sat(Request $request)
	{
		$url           = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
		$objWebService = new \SoapClient($url, array('trace' => 1,'use' => SOAP_LITERAL));
		return  $objWebService->__getFunctions();
		return  $objWebService->__soapCall('ConsultaResponse');
		$data           = App\Module::find($this->module_id);
		$rfc_enterprise = $request->rfc_enterprise;
		return view('administracion.facturacion.sat',
		[
			'id'             => $data['father'],
			'title'          => $data['name'],
			'details'        => $data['details'],
			'child_id'       => $this->module_id,
			'option_id'      => 242,
			'rfc_enterprise' => $rfc_enterprise
		]);
		if ($rfc_enterprise != "") 
		{
			if(\Storage::disk('reserved')->exists('/cer/'.$rfc_enterprise.'.cer.pem') && \Storage::disk('reserved')->exists('/cer/'.$rfc_enterprise.'.key.pem'))
			{
				$objWebService = new \SoapClient($strUrl, array('trace' => 1,'use' => SOAP_LITERAL));
			}
		}
	}

	static function cfdiStampPac($request, $id,$noCertificado,$route = 'bill.cfdi.pending',$routeErr = 'bill.cfdi.pending.stamp', $submodule = "")
	{
		$bill	= App\Bill::find($id);
		if($bill->expeditionDateCFDI == '' || $bill->expeditionDateCFDI == null)
		{
			$bill->expeditionDateCFDI = Carbon::Now()->subMinute(10);
			$bill->folio              = $bill->cfdiFolio;
			$bill->noCertificate      = $noCertificado;
			$bill->save();
			$bill       = App\Bill::find($id);
			$xslDoc     = new \DOMDocument();
			$xmlDoc     = new \DOMDocument();
			$transpiler = new XsltProcessor();
			if($bill->version == '4.0')
			{
				$xslDoc->load(\Storage::disk('reserved')->getDriver()->getAdapter()->getPathPrefix().'/v40/cadenaoriginal.xslt');
			}
			else
			{
				$xslDoc->load(\Storage::disk('reserved')->getDriver()->getAdapter()->getPathPrefix().'/v33/cadenaoriginal_3_3.xslt');
			}
			$objCer = str_replace('-----BEGIN CERTIFICATE-----','',str_replace('-----END CERTIFICATE-----','',preg_replace("/\r|\n/", "", \Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.cer.pem'))));
			$xmlDoc->loadXML(view('administracion.facturacion.xml', ['bill' => $bill,'noCertificado' => $noCertificado]));
			$transpiler->importStylesheet($xslDoc);
			$originalChain = $transpiler->transformToXML($xmlDoc);
			$privKey       = openssl_get_privatekey(\Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.key.pem'));
			openssl_sign($originalChain,$certificate,$privKey,OPENSSL_ALGO_SHA256);
			$stamp                  = base64_encode($certificate);
			$XML                    = view('administracion.facturacion.xml', ['bill' => $bill,'noCertificado' => $noCertificado,'sello' => $stamp,'certificado' => $objCer]);
			$objToStamp             = array();
			$objToStamp['username'] = 'PIM110705A78';
			if(app()->env == 'production')
			{
				$objToStamp['password'] = '2K9c3KvPGHsRqMk36-H8';
				$strUrl                 = 'https://sistema.timbox.com.mx/timbrado_cfdi33/wsdl';
			}
			else
			{
				$objToStamp['password'] = 'GF7vdJNwdxJbxB1ShwX7';
				$strUrl                 = 'https://staging.ws.timbox.com.mx/timbrado_cfdi33/wsdl';
			}
			$objWebService      = new \SoapClient($strUrl, array('trace' => 1,'use' => SOAP_LITERAL));
			$objToStamp['sxml'] = base64_encode($XML);
			try
			{
				$responseWS = $objWebService->__soapCall("timbrar_cfdi",$objToStamp);
				$xmlStamped = new \DOMDocument();
				$xmlStamped->loadXML($responseWS->xml);
				$tfd                    = $xmlStamped->getElementsByTagName('TimbreFiscalDigital');
				$bill->uuid             = $tfd[0]->getAttribute('UUID');
				$bill->satCertificateNo = $tfd[0]->getAttribute('NoCertificadoSAT');
				$bill->stampDate        = str_replace('T', ' ', $tfd[0]->getAttribute('FechaTimbrado'));
				$bill->originalChain    = $originalChain;
				$bill->digitalStampCFDI = $tfd[0]->getAttribute('SelloCFD');
				$bill->digitalStampSAT  = $tfd[0]->getAttribute('SelloSAT');
				$bill->status           = 1;
				$bill->statusCFDI       = 'Vigente';
				$bill->save();
				\Storage::disk('reserved')->put('/stamped/'.$bill->uuid.'.xml',$responseWS->xml);
				$pdf = PDF::loadView('administracion.facturacion.'.$bill->rfc,['bill'=>$bill]);
				\Storage::disk('reserved')->put('/stamped/'.$bill->uuid.'.pdf',$pdf->stream());
				$pdfFile = '/stamped/'.$bill->uuid.'.pdf';
				$xmlFile = '/stamped/'.$bill->uuid.'.xml';
				if($request->email_cfdi != '')
				{
					foreach($request->email_cfdi as $to)
					{
						Mail::to($to)->send(new App\Mail\NotificationCFDI('Notificación de CFDI',$xmlFile,$pdfFile));
					}
				}
				#saldo pendiente - cerrar solicitud
				if($bill->requestHasBill()->exists())
				{
					$t_request = $bill->requestHasBill;
					if($t_request->kind == 10)
					{
						$outstandingBalance = $t_request->income->first()->amount;
						if($t_request->taxPayment == 1)
						{
							$outstandingBalance -= $t_request->bill->whereIn('status',[0,1,2])->sum('total');
						}
						else
						{
							$outstandingBalance -= $t_request->billNF->sum('total');
						}
						if($outstandingBalance <= 0)
						{
							$t_request->status = 20;
							$t_request->save();
						}
					}
				}
				$alert     = "swal('', 'Comprobante timbrado', 'success');";
				if($submodule != "")
				{
					return searchRedirect($submodule, $alert, route('bill.cfdi.pending'));
				}
				else
				{
					return redirect()->route($route)->with('alert',$alert);
				}
			}
			catch (\Exception $exception)
			{
				$bill->expeditionDateCFDI = null;
				$bill->folio              = null;
				$bill->save();
				$alert = "swal({title:'Error: ".$exception->getCode()."', text:\"".nl2br($exception->getMessage(),true)."\", icon:'error', html:true});";
				if($submodule != "")
				{
					return searchRedirect($submodule, $alert, 'back');
				}
				else
				{
					return redirect()->route($routeErr,$id)->with('alert',$alert);
				}
			}
		}
		else
		{
			$alert = "swal('', 'El Comprobante ya ha sido timbrado o está en proceso.', 'error');";
			if($submodule != "")
			{
				return searchRedirect($submodule, $alert, 'back');
			}
			else
			{
				return redirect()->route($routeErr,$id)->with('alert',$alert);
			}
		}
	}

	public function downloadDocuments(Request $request)
	{
		$folio               = $request->folio;
		$folioRequest        = $request->folioRequest;
		$mindate			 = $request->mindate !='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
		$maxdate			 = $request->maxdate !='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
		$enterpriseid        = $request->enterpriseid;
		$kind                = $request->kind;
		$clientRfc           = $request->clientRfc;
		$employerRegister_id = $request->employerRegister_id;
		$periodicity         = $request->periodicity;
		
		$week					= $request->weekOfYear;
		$year  					= date('Y');
		$initRange				= App\Http\Controllers\AdministracionFacturacionController::initDate($year,$week);
		$endRange				= App\Http\Controllers\AdministracionFacturacionController::endDate($year,$week);

		$files		= App\Bill::whereIn('status',[1,2])
			->whereIn('rfc',App\Enterprise::whereIn('id',Auth::user()->inChargeEnt(154)->pluck('enterprise_id'))->pluck('rfc'))
			->where(function ($q) use($folio,$mindate,$maxdate,$enterpriseid,$kind,$folioRequest,$clientRfc,$employerRegister_id,$week,$initRange,$endRange,$periodicity)
			{
				if($clientRfc!='')
				{
					$q->where('clientRfc','LIKE','%'.preg_replace("/\s+/", "%", $clientRfc).'%')
						->orWhere('clientBusinessName','LIKE','%'.preg_replace("/\s+/", "%", $clientRfc).'%');
				}
				if($folio!='')
				{
					$q->where('folio',$folio);
				}
				if($folioRequest!='')
				{
					$q->where('folioRequest',$folioRequest);
				}
				if ($enterpriseid != "") 
				{
					$q->where('rfc',App\Enterprise::find($enterpriseid)->rfc);
				}
				if($mindate != "" && $maxdate != "")
				{
					$q->whereBetween('expeditionDateCFDI',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
				}
				if($week != "")
				{
					$q->whereBetween('expeditionDateCFDI',[''.$initRange.' '.date('00:00:00').'',''.$endRange.' '.date('23:59:59').'']);
				}
				if($kind != "")
				{
					$q->whereIn('type',$kind);
				}
				if ($employerRegister_id != "") 
				{
					$q->whereHas('nomina',function($nom) use ($employerRegister_id)
					{
						$nom->whereIn('employer_register',$employerRegister_id);
					});	
				}
				if($periodicity != "")
				{
					$q->whereHas('nominaReceiver',function($nom) use ($periodicity)
					{
						$nom->whereIn('periodicity',$periodicity);
					});	
				}
			})
			->select('bills.uuid')
			->orderBy('expeditionDateCFDI','DESC')
			->get();

			$zip_file	= '/tmp/cfdi.zip';
			$zip		= new \ZipArchive();
			if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
			{
				$zip->addEmptyDir('timbres');
				foreach ($files as $cfdi)
				{
					$zip->addFile(storage_path('/stamped/'.$cfdi->uuid.'.pdf'), '/timbres/'.$cfdi->uuid.'.pdf');
					$zip->addFile(storage_path('/stamped/'.$cfdi->uuid.'.xml'), '/timbres/'.$cfdi->uuid.'.xml');
				}
				$zip->close();
				return response()->download($zip_file);
			}
	}

	public function initDate($year,$week)
	{
		switch ($week) 
		{
			case 1:
				$week = '01';
				break;

			case 2:
				$week = '02';
				break;

			case 3:
				$week = '03';
				break;

			case 4:
				$week = '04';
				break;

			case 5:
				$week = '05';
				break;

			case 6:
				$week = '06';
				break;

			case 7:
				$week = '07';
				break;

			case 8:
				$week = '08';
				break;

			case 9:
				$week = '09';
				break;
			
			default:
				$week = $week;
				break;
		}

		return date('Y-m-d',strtotime($year.'W'.$week.'-1')); 
	}

	public function endDate($year,$week)
	{
		switch ($week) 
		{
			case 1:
				$week = '01';
				break;

			case 2:
				$week = '02';
				break;

			case 3:
				$week = '03';
				break;

			case 4:
				$week = '04';
				break;

			case 5:
				$week = '05';
				break;

			case 6:
				$week = '06';
				break;

			case 7:
				$week = '07';
				break;

			case 8:
				$week = '08';
				break;

			case 9:
				$week = '09';
				break;
			
			default:
				$week = $week;
				break;
		}
		return date('Y-m-d',strtotime($year.'W'.$week.'-7')); 
	}

}
