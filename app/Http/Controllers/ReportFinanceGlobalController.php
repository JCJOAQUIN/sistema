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
use PHPExcel_Cell;
use Carbon\Carbon;

class ReportFinanceGlobalController extends Controller
{
	private $module_id = 130;
	public function globalIndex()
	{
		if (Auth::user()->module->where('id',206)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			return view('reporte.finanzas.global',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 206
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function globalExport(Request $request)
	{
		if (Auth::user()->module->where('id',206)->count()>0)
		{
			$name				= $request->name;
			$mindate			= $request->mindate;
			$maxdate			= $request->maxdate;
			$status				= $request->status;
			$kind				= $request->kind;
			$mindate_review		= $request->mindate_review;
			$maxdate_review		= $request->maxdate_review;
			$mindate_authorize	= $request->mindate_authorize;
			$maxdate_authorize	= $request->maxdate_authorize;
			$enterprise			= $request->idEnterprise;
			$direction			= $request->idArea;
			$department			= $request->idDepartment;
			$requests	= App\RequestModel::selectRaw('
						request_models.folio AS folio,
						status_requests.description AS estatus,
						IF(request_models.kind = 16, cat_type_payrolls.description, request_kinds.kind) AS tipo,
						CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) AS solicitante,
						CONCAT_WS(" ",elab.name,elab.last_name,elab.scnd_last_name) AS elaborado,
						IF(request_models.kind = 16, nomEnt.name, enterprises.name) AS empresa,
						IF(request_models.kind = 16, nominaDir.name, areas.name) AS direccion,
						IF(request_models.kind = 16, nominaDep.name, departments.name) AS departamento,
						IF(request_models.kind = 16, nomProy.proyectName, projects.proyectName) AS proyecto,
						IF(request_models.kind = 1, providers.businessName, IF(request_models.kind = 16, CONCAT_WS(" ", real_employees.name, real_employees.last_name, real_employees.scnd_last_name), IF(request_models.kind = 17, purchase_records.provider, CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name)))) AS razon,
						IF(request_models.kind = 1, purchases.paymentMode, IF(request_models.kind = 8, resourcePayment.method, IF(request_models.kind = 9, refundPayment.method, IF(request_models.kind = 16, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salPayment.method, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonPayment.method, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liqPayment.method, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacPayment.method, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profPayment.method, nomNFPayment.method))))), IF(request_models.kind = 17, regComPayment.method, IF(request_models.kind = 18, finances.paymentMethod, "")))))) AS forma_pago,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 8, CONCAT(resAcc.account," ",resAcc.description," (",resAcc.content,")"), IF(request_models.kind = 9, CONCAT(refAcc.account," ",refAcc.description," (",refAcc.content,")"), IF(request_models.kind = 9, CONCAT(accounts.account," ",accounts.description," (",accounts.content,")"), IF(request_models.kind = 16, CONCAT(nomAccount.account," ",nomAccount.description," (",nomAccount.content,")"), ""))))) AS accounts,
						IF(request_models.kind = 1 OR request_models.kind = 17 OR request_models.kind = 18, IF(request_models.taxPayment = 1, "Fiscal", "No fiscal"), IF(request_models.kind = 9, IF(refund_details.taxPayment = 1, "Fiscal", "No fiscal"), IF(request_models.kind = 16, IF(nomina_employees.fiscal = 1, "Fiscal", "No fiscal"), ""))) AS fiscal,
						DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i:%s") AS autorizacion,
						IF(request_models.kind = 1, detail_purchases.description, IF(request_models.kind = 8, resource_details.concept, IF(request_models.kind = 9, refund_details.concept, IF(request_models.kind = 18, finances.kind, IF(request_models.kind = 16, CONCAT_WS(" ", cat_type_payrolls.description, "-", real_employees.name, real_employees.last_name, real_employees.scnd_last_name), IF(request_models.kind = 17, purchase_record_details.description, "")))))) AS concept,
						IF(request_models.kind = 18, request_models.PaymentDate, authorizeDate) AS pago_fecha,
						ROUND(IF(request_models.kind = 1, detail_purchases.amount, IF(request_models.kind = 8, resource_details.amount, IF(request_models.kind = 9, refund_details.sAmount, IF(request_models.kind = 18, finances.amount, IF(request_models.kind = 17, purchase_record_details.total, IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount)))))))))),2) AS amount,
						IF(request_models.kind = 18, finances.week, WEEK(authorizeDate,6)) AS semana
						'
				)
				->join('status_requests','request_models.status','=','status_requests.idrequestStatus')
				->join('request_kinds','request_models.kind','=','request_kinds.idrequestkind')
				->join('users','request_models.idRequest','=','users.id')
				->join('users as elab','request_models.idElaborate','=','elab.id')
				->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
				->leftJoin('providers','purchases.idProvider','=','providers.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','=','detail_purchases.idPurchase')
				->leftJoin('resources','request_models.folio','=','resources.idFolio')
				->leftJoin('payment_methods AS resourcePayment','resources.idpaymentMethod','=','resourcePayment.idpaymentMethod')
				->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
				->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
				->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
				->leftJoin('payment_methods AS refundPayment','refunds.idpaymentMethod','=','refundPayment.idpaymentMethod')
				->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
				->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
				->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
				->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
				->leftJoin('payment_methods AS regComPayment','purchase_records.paymentMethod','=','regComPayment.idpaymentMethod')
				->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
				->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin('payment_methods AS nomNFPayment','nomina_employee_n_fs.idpaymentMethod','=','nomNFPayment.idpaymentMethod')
				->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payment_methods AS liqPayment','liquidations.idpaymentMethod','=','liqPayment.idpaymentMethod')
				->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin('payment_methods AS bonPayment','bonuses.idpaymentMethod','=','bonPayment.idpaymentMethod')
				->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin('payment_methods AS vacPayment','vacation_premia.idpaymentMethod','=','vacPayment.idpaymentMethod')
				->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin('payment_methods AS salPayment','salaries.idpaymentMethod','=','salPayment.idpaymentMethod')
				->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin('payment_methods AS profPayment','profit_sharings.idpaymentMethod','=','profPayment.idpaymentMethod')
				->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
				->leftJoin('areas AS nominaDir','worker_datas.direction','=','nominaDir.id')
				->leftJoin('departments AS nominaDep','worker_datas.department','=','nominaDep.id')
				->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
				->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
				->leftJoin('finances','request_models.folio','=','finances.idFolio')
				->leftJoin('enterprises','request_models.idEnterpriseR','=','enterprises.id')
				->leftJoin('areas','request_models.idAreaR','=','areas.id')
				->leftJoin('departments','request_models.idDepartamentR','=','departments.id')
				->leftJoin('projects','request_models.idProjectR','=','projects.idproyect')
				->leftJoin('accounts','request_models.accountR','=','accounts.idAccAcc')
				->whereIn('request_models.kind',[1,8,9,16,17,18])
				->whereIn('request_models.status',[5,10,11,12])
				->where(function ($query) use ($name,$mindate,$maxdate,$status,$kind,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$enterprise,$direction,$department)
				{
					if($name != "")
					{
						$query->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$mindate = Carbon::createFromFormat('d-m-Y', $mindate)->format('Y-m-d');
						$maxdate = Carbon::createFromFormat('d-m-Y', $maxdate)->format('Y-m-d');
						$query->whereBetween('request_models.fDate',[$mindate." ".date('00:00:00'),$maxdate." ".date('00:00:00')]);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$mindate_review = Carbon::createFromFormat('d-m-Y', $mindate_review)->format('Y-m-d');
						$maxdate_review = Carbon::createFromFormat('d-m-Y', $maxdate_review)->format('Y-m-d');
						$query->whereBetween('request_models.reviewDate',[$mindate_review." ".date('00:00:00'),$maxdate_review." ".date('00:00:00')]);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$mindate_authorize = Carbon::createFromFormat('d-m-Y', $mindate_authorize)->format('Y-m-d');
						$maxdate_authorize = Carbon::createFromFormat('d-m-Y', $maxdate_authorize)->format('Y-m-d');
						$query->whereBetween('request_models.authorizeDate',[$mindate_authorize." ".date('00:00:00'),$maxdate_authorize." ".date('00:00:00')]);
					}
					if($status != '')
					{
						$query->whereIn('request_models.status',$status);
					}
					if($kind != '')
					{
						$query->whereIn('request_models.kind',$kind);
					}
					if($enterprise != '')
					{
						$query->where(function ($q) use($enterprise)
						{
							$q->whereIn('request_models.idEnterpriseR',$enterprise)
							->orWhereIn('worker_datas.enterprise',$enterprise);
						});
					}
					if($direction != '')
					{
						$query->where(function ($q) use($direction)
						{
							$q->whereIn('request_models.idAreaR',$direction)
							->orWhereIn('worker_datas.direction',$direction);
						});
					}
					if($department != '')
					{
						$query->where(function ($q) use($department)
						{
							$q->whereIn('request_models.idDepartamentR',$department)
							->orWhereIn('worker_datas.department',$department);
						});
					}
				})
				->orderBy('request_kinds.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->get();

			Excel::create('Reporte Global', function($excel) use ($requests)
			{
				$excel->sheet('Totales',function($sheet) use ($requests)
				{
					$weekData		= array();
					$weeks			= array();
					$projectData	= array();
					$projects		= array();
					$kindExpense	= ['NOMINA','GASTOS'];
					$nominaKind		= App\CatTypePayroll::pluck('description')->toArray();
					foreach ($requests as $data)
					{
						$tempKind = (in_array($data->tipo, $nominaKind) ? 'NOMINA' : 'GASTOS');
						if(isset($projectData[$data->proyecto][$data->semana][$tempKind]))
						{
							$projectData[$data->proyecto][$data->semana][$tempKind]	= round($projectData[$data->proyecto][$data->semana][$tempKind] + $data->amount,2);
						}
						else
						{
							$projectData[$data->proyecto][$data->semana][$tempKind]	= round($data->amount,2);
						}

						if(isset($weekData[$tempKind][$data->semana]))
						{
							$weekData[$tempKind][$data->semana]	= round($weekData[$tempKind][$data->semana] + $data->amount,2);
						}
						else
						{
							$weekData[$tempKind][$data->semana]	= round($data->amount,2);
						}
						$weeks[$data->semana]		= $data->semana;
						$projects[$data->proyecto]	= $data->proyecto;
					}
					sort($weeks);
					sort($projects);
					$lastCol	= PHPExcel_Cell::stringFromColumnIndex(count($weeks)+1);
					$temp = array();
					$temp[] = 'PROYECTOS';
					$temp[] = 'TIPO';
					foreach ($weeks as $key => $week)
					{
						$temp[]	= $week;
					}
					$sheet->row(1,$temp);
					$tempCount	= 2;
					$colorFlag	= true;
					$sheet->setAllBorders('thin');
					foreach ($projects as $key => $proyect)
					{
						$startProy	= $tempCount;
						foreach ($kindExpense as $key => $kind)
						{
							$temp	= array();
							$temp[]	= $proyect;
							$temp[]	= $kind;
							foreach ($weeks as $key => $week)
							{
								if(isset($projectData[$proyect][$week][$kind]))
								{
									$temp[]	= $projectData[$proyect][$week][$kind];
								}
								else
								{
									$temp[]	= '';
								}
							}
							$sheet->row($tempCount,$temp);
							$tempCount++;
						}
						$endProy	= $tempCount;
						$endProy--;
						if($colorFlag)
						{
							$sheet->cell('A'.$startProy.':'.$lastCol.$endProy, function($cells)
							{
								$cells->setBackground('#B1CE94');
							});
							$colorFlag = false;
						}
						else
						{
							$colorFlag = true;
						}
					}
					$startSub    = $tempCount;
					$arrTotal	= array();
					$arrTotal[]	= 'TOTAL';
					$arrTotal[]	= '';
					foreach ($kindExpense as $key => $kind)
					{
						$temp		= array();
						$temp[]		= 'SUBTOTAL '.$kind;
						$temp[]		= $kind;
						foreach ($weeks as $key => $week)
						{
							if(isset($weekData[$kind][$week]))
							{
								$temp[]	= $weekData[$kind][$week];
							}
							else
							{
								$temp[]	= '';
							}
						}
						$sheet->row($tempCount,$temp);
						$tempCount++;
					}
					foreach ($weeks as $key => $week)
					{
						$nomina		= (isset($weekData['NOMINA'][$week]) ? $weekData['NOMINA'][$week] : 0);
						$gastos		= (isset($weekData['GASTOS'][$week]) ? $weekData['GASTOS'][$week] : 0);
						$arrTotal[]	= round($nomina + $gastos,2);
					}
					$endSub = $tempCount;
					$tempCount++;
					$sheet->row($tempCount,$arrTotal);
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->cell('A'.$startSub.':'.$lastCol.$endSub, function($cells)
					{
						$cells->setBackground('#C2D7EC');
					});
					$sheet->cell('A1:'.$lastCol.'1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						$cells->setBackground('#243761');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A'.$tempCount.':'.$lastCol.$tempCount, function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						$cells->setBackground('#243761');
						$cells->setFontColor('#ffffff');
					});
					$sheet->setFreeze('C2');
				});
				$excel->sheet('Gastos',function($sheet) use ($requests)
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
					$sheet->cell('A1:R1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						$cells->setBackground('#243761');
						$cells->setFontColor('#ffffff');
					});
					$sheet->setColumnFormat(array(
						'Q'	=> '#,##0.00',
					));
					$sheet->row(1,['Folio','Estado','Tipo','Solicitante','Elaborado por','Empresa','Dirección','Departamento','Proyecto','Razón Social','Forma de pago','Clasificación del gasto','Fiscal/No Fiscal','Fecha de autorización','Concepto','Fecha de pago','Total','Semana']);
					$sheet->fromModel($requests,'','A2',false,false);
				});

			})->export('xlsx');
		}
		else
		{
			return abort(404);
		}
	}

}
