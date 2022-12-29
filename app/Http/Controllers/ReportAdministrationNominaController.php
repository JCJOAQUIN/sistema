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

class ReportAdministrationNominaController extends Controller
{
	private $module_id = 96;
	public function nominaReport(Request $request)
	{
		if (Auth::user()->module->where('id',181)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$titleRequest = $request->titleRequest;
			$name         = $request->name;
			$folio        = $request->folio;
			$department   = $request->department;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal       = $request->fiscal;
			$stat         = $request->stat;
			$typePayroll  = $request->type_payroll;
			$type_report  = $request->type_report;

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = $mindate->format('Y-m-d');
				$endRange   = $maxdate->format('Y-m-d');

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

			$requests     = App\RequestModel::where('kind',16)
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('status',$stat);
					}
					else
					{
						$q->whereIn('status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('taxPayment',$fiscal);
					}
				})
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('folio',$folio);
					}

					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}

					if ($typePayroll != "") 
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}

					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->paginate(15);
			return view('reporte.administracion.nomina',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 181,
					'requests'     => $requests,
					'titleRequest' => $titleRequest,
					'name'         => $name,
					'folio'        => $folio,
					'department'   => $department,
					'mindate'      => $mindate != '' ? $mindate->format('d-m-Y') : '',
					'maxdate'      => $maxdate != '' ? $maxdate->format('d-m-Y') : '',
					'fiscal'       => $fiscal,
					'stat'         => $stat,
					'typePayroll'  => $typePayroll,
					'type_report'  => $type_report
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function nominaExcel(Request $request)
	{
		if (Auth::user()->module->where('id',181)->count()>0)
		{
			$titleRequest = $request->titleRequest;
			$name         = $request->name;
			$folio        = $request->folio;
			$department   = $request->department;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$fiscal       = $request->fiscal;
			$stat         = $request->stat;
			$typePayroll  = $request->type_payroll;
			$type_report  = $request->type_report;
			$requests     = App\RequestModel::where('kind',16)
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('status',$stat);
					}
					else
					{
						$q->whereIn('status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('taxPayment',$fiscal);
					}
				})
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "") 
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->get();
			if(count($requests) == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return redirect()->route('report.nomina')->with('alert',$alert);
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$mhStyleCol6    = (new StyleBuilder())->setBackgroundColor('548235')->setFontColor(Color::WHITE)->build();
			$mhStyleCol7    = (new StyleBuilder())->setBackgroundColor('EC8500')->setFontColor(Color::WHITE)->build();
			$mhStyleCol8    = (new StyleBuilder())->setBackgroundColor('D8407D')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol6    = (new StyleBuilder())->setBackgroundColor('A9D08E')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol7    = (new StyleBuilder())->setBackgroundColor('F3B084')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol8    = (new StyleBuilder())->setBackgroundColor('E0B5C7')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			if($type_report == 1)
			{
				$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('reporte-nomina_normal.xlsx');
			}
			else
			{
				$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('reporte-nomina_reducido.xlsx');
			}
			$newSheet = false;
			$nfCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin('payment_methods','nomina_employee_n_fs.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('employee_accounts','nomina_employee_n_fs.idemployeeAccounts','=','employee_accounts.id')
				->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
				->leftJoin('cat_periodicities','nominas.idCatPeriodicity','=','cat_periodicities.c_periodicity')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
				->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from extras_nominas group by idnominaemployeenf) as extras_nominas'),'nomina_employee_n_fs.idnominaemployeenf','extras_nominas.idnominaemployeenf')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where('request_models.kind',16)
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($nfCount > 0)
			{
				$newSheet = true;
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Nóminas no fiscales');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','','','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS DE COMPLEMENTO','','','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 6)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 14)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 21)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 26)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr    = ['Folio','Estado','Correspondiente a','Título','Tipo','Rango de Fechas','Periodicidad','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','Referencia','Razón de pago','Descuento','Extra','Sueldo Neto No Fiscal','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 6)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 14)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 21)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 26)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						IF(
							nominas.idCatTypePayroll = "001",
							IF(
								nominas.idCatPeriodicity = "05",
								UPPER(DATE_FORMAT(nominas.from_date,"%b - %Y")),
								IF(
									nominas.idCatPeriodicity = "04",
									IF(
										nominas.to_date <= DATE_FORMAT(nominas.to_date,"%Y-%m-15"),
										UPPER(CONCAT("1q ",DATE_FORMAT(nominas.from_date,"%b - %Y"))),
										UPPER(CONCAT("2q ",DATE_FORMAT(nominas.from_date,"%b - %Y")))
									),
									UPPER(CONCAT("sem ",DATE_FORMAT(nominas.from_date,"%u")," - ",DATE_FORMAT(nominas.from_date,"%Y")))
								)
							),
							""
						) as periodRange,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						cat_type_payrolls.description as typeNomina,
						CONCAT_WS(" ",nominas.from_date, nominas.to_date) as rangeDate,
						cat_periodicities.description as periodicity,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						nomina_employee_n_fs.reference,
						nomina_employee_n_fs.reasonAmount,
						IF(discount_nomina.amount IS NULL,IF(nomina_employee_n_fs.discount IS NULL,0,nomina_employee_n_fs.discount),discount_nomina.amount) as discounts,
						IFNULL(extras_nominas.amount,0) as extras_nomina,
						nomina_employee_n_fs.amount,
						IFNULL(payment.amount,0) as pagado,
						ROUND(nomina_employee_n_fs.amount - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						nomina_employee_n_fs.amount';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->leftJoin('payment_methods','nomina_employee_n_fs.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('employee_accounts','nomina_employee_n_fs.idemployeeAccounts','=','employee_accounts.id')
					->join('cat_type_payrolls','nominas.idCatTypePayroll','=','cat_type_payrolls.id')
					->leftJoin('cat_periodicities','nominas.idCatPeriodicity','=','cat_periodicities.c_periodicity')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from discounts_nominas group by idnominaemployeenf) as discount_nomina'),'nomina_employee_n_fs.idnominaemployeenf','discount_nomina.idnominaemployeenf')
					->leftJoin(DB::raw('(select idnominaemployeenf, ROUND(SUM(amount),2) as amount from extras_nominas group by idnominaemployeenf) as extras_nominas'),'nomina_employee_n_fs.idnominaemployeenf','extras_nominas.idnominaemployeenf')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where('request_models.kind',16)
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						
						if ($typePayroll != "") 
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['discounts','extras_nomina','amount','pagado','por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$salaryCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin('payment_methods','salaries.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','salaries.idSalary','=','nomina_employee_accounts.idSalary')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin('cat_periodicities','nomina_employees.idCatPeriodicity','=','cat_periodicities.c_periodicity')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($salaryCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Sueldo');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','PERCEPCIONES','','','','','','RETENCIONES','','','','','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 3)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 11)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 18)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 24)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 30)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 36)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 37)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Correspondiente a','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Días Trabajados','Periodicidad','Rango de Fechas','Días para IMSS','Sueldo','Préstamo','Puntualidad','Asistencia','Subsidio','Total de Percepciones','IMSS','Infonavit','Fonacot','Préstamo','Retención de ISR','Total de Deducciones','Sueldo Neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 3)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 11)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 18)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 24)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 30)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 36)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 37)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						IF(
							nomina_employees.idCatPeriodicity = "05",
							UPPER(DATE_FORMAT(nomina_employees.from_date,"%b - %Y")),
							IF(
								nomina_employees.idCatPeriodicity = "04",
								IF(
									nomina_employees.to_date <= DATE_FORMAT(nomina_employees.to_date,"%Y-%m-15"),
									UPPER(CONCAT("1q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y"))),
									UPPER(CONCAT("2q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y")))
								),
								UPPER(CONCAT("sem ",DATE_FORMAT(nomina_employees.from_date,"%u")," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))
							)
						) as periodRange,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						salaries.sd as sd,
						salaries.sdi as sdi,
						salaries.workedDays as workedDays,
						cat_periodicities.description as periodicity,
						CONCAT_WS(" ",nomina_employees.from_date, nomina_employees.to_date) as rangeDate,
						salaries.daysForImss as daysForImss,
						salaries.salary as salary,
						salaries.loan_perception as loan_perception,
						salaries.puntuality as puntuality,
						salaries.assistance as assistance,
						salaries.subsidy as subsidy,
						salaries.totalPerceptions as totalPerceptions,
						salaries.imss as imss,
						salaries.infonavit as infonavit,
						salaries.fonacot as fonacot,
						salaries.loan_retention as loan_retention,
						salaries.isrRetentions as isrRetentions,
						salaries.totalRetentions as totalRetentions,
						salaries.netIncome as netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(salaries.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						salaries.netIncome as netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->leftJoin('payment_methods','salaries.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','salaries.idSalary','=','nomina_employee_accounts.idSalary')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin('cat_periodicities','nomina_employees.idCatPeriodicity','=','cat_periodicities.c_periodicity')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where('request_models.kind',16)
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd','sdi','salary', 'loan_perception', 'puntuality', 'assistance', 'subsidy', 'totalPerceptions', 'imss', 'infonavit', 'fonacot', 'loan_retention', 'isrRetentions', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$bonusCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin('payment_methods','bonuses.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','bonuses.idBonus','=','nomina_employee_accounts.idBonus')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($bonusCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Aguinaldo');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','PERCEPCIONES','','','RETENCIONES','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 22)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 25)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 27)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 28)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias', 'Banco', 'CLABE', 'Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Días para aguinaldo','Parte proporcional para aguinaldo','Aguinaldo exento','Aguinaldo gravable','Total','ISR','Total','Sueldo Neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 22)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 25)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 27)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 28)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						bonuses.sd,
						bonuses.sdi,
						bonuses.dateOfAdmission,
						bonuses.daysForBonuses,
						bonuses.proportionalPartForChristmasBonus,
						bonuses.exemptBonus,
						bonuses.taxableBonus,
						bonuses.totalPerceptions,
						bonuses.isr,
						bonuses.totalTaxes,
						bonuses.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(bonuses.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						bonuses.netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->leftJoin('payment_methods','bonuses.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','bonuses.idBonus','=','nomina_employee_accounts.idBonus')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd', 'sdi', 'exemptBonus', 'taxableBonus', 'totalPerceptions', 'isr', 'totalTaxes', 'netIncome', 'pagado', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$settlementCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where('nominas.idCatTypePayroll','003')
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($settlementCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Finiquito');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','','PERCEPCIONES','','','','','','','','','','RETENCIONES','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 35)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 37)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 38)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Fecha de baja','Años completos','Días trabajados','Días para vacaciones','Días para aguinaldo','Prima de antigüedad','Indemnización exenta','Indemnización gravada','Vacaciones','Aguinaldo exento','Aguinaldo gravable','Prima vacacional exenta','Prima vacacional gravada','Otras percepciones','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 35)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 37)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 38)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						liquidations.sd,
						liquidations.sdi,
						liquidations.admissionDate,
						liquidations.downDate,
						liquidations.fullYears,
						liquidations.workedDays,
						liquidations.holidayDays,
						liquidations.bonusDays,
						liquidations.seniorityPremium,
						liquidations.exemptCompensation,
						liquidations.taxedCompensation,
						liquidations.holidays,
						liquidations.exemptBonus,
						liquidations.taxableBonus,
						liquidations.holidayPremiumExempt,
						liquidations.holidayPremiumTaxed,
						liquidations.otherPerception,
						liquidations.totalPerceptions,
						liquidations.isr,
						liquidations.totalRetentions,
						liquidations.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(liquidations.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						liquidations.netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where('nominas.idCatTypePayroll','003')
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd', 'sdi', 'seniorityPremium', 'exemptCompensation', 'taxedCompensation', 'holidays', 'exemptBonus', 'taxableBonus', 'holidayPremiumExempt', 'holidayPremiumTaxed', 'otherPerception', 'totalPerceptions', 'isr', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$liquidationCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where('nominas.idCatTypePayroll','004')
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($liquidationCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Liquidación');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','','PERCEPCIONES','','','','','','','','','','','','RETENCIONES','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 37)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 39)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 40)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Fecha de baja','Años completos','Días trabajados','Días para vacaciones','Días para aguinaldo','Sueldo por liquidación','20 días por año de servicio','Prima de antigüedad','Indemnización exenta','Indemnización gravada','Vacaciones','Aguinaldo exento','Aguinaldo gravable','Prima vacacional exenta','Prima vacacional gravada','Otras percepciones','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 25)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 37)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 39)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 40)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						liquidations.sd,
						liquidations.sdi,
						liquidations.admissionDate,
						liquidations.downDate,
						liquidations.fullYears,
						liquidations.workedDays,
						liquidations.holidayDays,
						liquidations.bonusDays,
						liquidations.liquidationSalary,
						liquidations.twentyDaysPerYearOfServices,
						liquidations.seniorityPremium,
						liquidations.exemptCompensation,
						liquidations.taxedCompensation,
						liquidations.holidays,
						liquidations.exemptBonus,
						liquidations.taxableBonus,
						liquidations.holidayPremiumExempt,
						liquidations.holidayPremiumTaxed,
						liquidations.otherPerception,
						liquidations.totalPerceptions,
						liquidations.isr,
						liquidations.totalRetentions,
						liquidations.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(liquidations.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						liquidations.netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('payment_methods','liquidations.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','liquidations.idLiquidation','=','nomina_employee_accounts.idLiquidation')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where('nominas.idCatTypePayroll','004')
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd', 'sdi', 'liquidationSalary', 'twentyDaysPerYearOfServices', 'seniorityPremium', 'exemptCompensation', 'taxedCompensation', 'holidays', 'exemptBonus', 'taxableBonus', 'holidayPremiumExempt', 'holidayPremiumTaxed', 'otherPerception', 'totalPerceptions', 'isr', 'totalRetentions', 'netIncome', 'pagado', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$pvCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin('payment_methods','vacation_premia.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','vacation_premia.idvacationPremium','=','nomina_employee_accounts.idvacationPremium')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($pvCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Prima vacacional');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','PERCEPCIONES','','','','RETENCIONES','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 22)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 26)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 28)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 29)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Fecha de ingreso','Días trabajados','Días para vacaciones','Vacaciones','Prima vacacional exenta','Prima vacacional gravada','Total','ISR','Total','Sueldo neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 22)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 26)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 28)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 29)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						vacation_premia.sd,
						vacation_premia.sdi,
						vacation_premia.dateOfAdmission,
						vacation_premia.workedDays,
						vacation_premia.holidaysDays,
						vacation_premia.holidays,
						vacation_premia.exemptHolidayPremium,
						vacation_premia.holidayPremiumTaxed,
						vacation_premia.totalPerceptions,
						vacation_premia.isr,
						vacation_premia.totalTaxes,
						vacation_premia.netIncome,
						IFNULL(payment.amount,0) as pagado,
						ROUND(vacation_premia.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						vacation_premia.netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->leftJoin('payment_methods','vacation_premia.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','vacation_premia.idvacationPremium','=','nomina_employee_accounts.idvacationPremium')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd', 'sdi', 'holidays', 'exemptHolidayPremium', 'holidayPremiumTaxed', 'totalPerceptions', 'isr', 'totalTaxes', 'netIncome', 'pagado', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			$psCount  = App\RequestModel::selectRaw('COUNT(nominas.idFolio) as num')
				->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
				->join('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
						->on('request_models.kind','=','nominas.idKind');
				})
				->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->leftJoin('projects','worker_datas.project','=','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
				->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin('payment_methods','profit_sharings.idpaymentMethod','=','payment_methods.idpaymentMethod')
				->leftJoin('nomina_employee_accounts','profit_sharings.idprofitSharing','=','nomina_employee_accounts.idprofitSharing')
				->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
				->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
				->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
				->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->where(function($q) use ($stat)
				{
					if ($stat != "") 
					{
						$q->whereIn('request_models.status',$stat);
					}
					else
					{
						$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
					}
				})
				->where(function($q) use ($fiscal)
				{
					if ($fiscal != "") 
					{
						$q->whereIn('request_models.taxPayment',$fiscal);
					}
				})
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
				{
					if ($name != "")
					{
						$q->whereHas('requestUser', function($q) use($name)
						{
							$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
						});
					}
					if ($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if ($titleRequest != "") 
					{
						$q->whereHas('nomina',function($q) use($titleRequest)
						{
							$q->where('title','LIKE','%'.$titleRequest.'%');
						});
					}
					if ($typePayroll != "")
					{
						$q->whereHas('nomina',function($q) use($typePayroll)
						{
							$q->whereIn('idCatTypePayroll',$typePayroll);
						});
					}
					if ($department != "")
					{
						$q->whereIn('idDepartment',$department);
					}
					if($mindate != "" && $maxdate != "")
					{
						if (in_array('001',$typePayroll)) 
						{
							$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
							{
								if ($mindate != "") 
								{
									$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}

								if ($maxdate != "") 
								{
									$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								}
							});
							$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
						else
						{
							$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					}
				})
				->orderBy('authorizeDate','DESC')
				->orderBy('folio','DESC')
				->first()
				->num;
			if($psCount > 0)
			{
				if($newSheet)
				{
					$writer->addNewSheetAndMakeItCurrent();
				}
				else
				{
					$newSheet = true;
				}
				$sheet    = $writer->getCurrentSheet();
				$sheet->setName('Reparto de utilidades');
				if($type_report == 1)
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD','','','INFORMACIÓN PERSONAL','','','','','','','','DATOS DE PAGO','','','','','','','DATOS GENERALES','','','','','','','PERCEPCIONES','','','RETENCIONES','','NETO','PAGOS',''];
				}
				else
				{
					$mainHeaderArr = ['INFORMACIÓN DE LA SOLICITUD Y EMPLEADO','','','','','','DATOS DE PAGO','','','','','',''];
				}
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
						}
						elseif($k <= 24)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
						}
						elseif($k <= 27)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
						}
						elseif($k <= 29)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
						elseif($k <= 30)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
						}
						else
						{
							$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$headerArr = ['Folio','Estado','Título','Apellido Paterno','Apellido Materno','Nombre','Proyecto','Empresa','Subdepartamento','WBS','Registro patronal','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Sucursal','S.D.','S.D.I.','Días trabajados','Sueldo total','PTU por días','PTU por sueldos','PTU total','PTU exenta','PTU gravada','Total','Retención de ISR','Total','Sueldo neto','Pagado','Por pagar'];
				}
				else
				{
					$headerArr    = ['Folio','Estado','Empresa','Apellido Paterno','Apellido Materno','Nombre','Forma de pago','Alias','Banco','CLABE','Cuenta','Tarjeta','Monto'];
				}
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($type_report == 1)
					{
						if($k <= 2)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						elseif($k <= 10)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
						}
						elseif($k <= 17)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
						}
						elseif($k <= 24)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
						}
						elseif($k <= 27)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
						}
						elseif($k <= 29)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
						elseif($k <= 30)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
						}
					}
					else
					{
						if($k <= 5)
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
						}
						else
						{
							$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
						}
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				if($type_report == 1)
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						CONCAT_WS(" ",nominas.title,nominas.datetitle) as title,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						projects.proyectName as project,
						enterprises.name as enterprise,
						wd_departments.subdepartments,
						wd_wbs.wbs,
						worker_datas.employer_register,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						employee_accounts.branch as branch,
						profit_sharings.sd,
						profit_sharings.sdi,
						profit_sharings.workedDays,
						profit_sharings.totalSalary,
						profit_sharings.ptuForDays,
						profit_sharings.ptuForSalary,
						profit_sharings.totalPtu,
						profit_sharings.exemptPtu,
						profit_sharings.taxedPtu,
						profit_sharings.totalPerceptions,
						profit_sharings.isrRetentions,
						profit_sharings.totalRetentions,
						profit_sharings.netIncome,
						IFNULL(payment.amount,0) as payment,
						ROUND(profit_sharings.netIncome - IFNULL(payment.amount,0),2) as por_pagar';
				}
				else
				{
					$tmpSelect = 'nominas.idFolio as folio,
						status_requests.description as status,
						enterprises.name as enterprise,
						real_employees.last_name as last_name,
						real_employees.scnd_last_name as scnd_last_name,
						real_employees.name as name,
						payment_methods.method as paymentMethod,
						employee_accounts.alias as alias,
						cat_banks.description as bank,
						CONCAT(employee_accounts.clabe," ") as clabe,
						CONCAT(employee_accounts.account," ") as account,
						CONCAT(employee_accounts.cardNumber," ") as cardNumber,
						profit_sharings.netIncome';
				}
				$dataToWrite = App\RequestModel::selectRaw($tmpSelect)
					->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
					->join('nominas',function($q)
					{
						$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
					})
					->join('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('projects','worker_datas.project','=','projects.idproyect')
					->leftJoin('enterprises','worker_datas.enterprise','=','enterprises.id')
					->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->leftJoin('payment_methods','profit_sharings.idpaymentMethod','=','payment_methods.idpaymentMethod')
					->leftJoin('nomina_employee_accounts','profit_sharings.idprofitSharing','=','nomina_employee_accounts.idprofitSharing')
					->leftJoin('employee_accounts','nomina_employee_accounts.idEmployeeAccounts','=','employee_accounts.id')
					->leftJoin('cat_banks','employee_accounts.idCatBank','=','c_bank')
					->leftJoin(DB::raw('(select idnominaEmployee, ROUND(SUM(amount),2) as amount from payments group by idnominaEmployee) as payment'),'nomina_employees.idnominaEmployee','payment.idnominaEmployee')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
					->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
					->where(function($q) use ($stat)
					{
						if ($stat != "") 
						{
							$q->whereIn('request_models.status',$stat);
						}
						else
						{
							$q->whereIn('request_models.status',[4,5,10,11,12,15,18]);
						}
					})
					->where(function($q) use ($fiscal)
					{
						if ($fiscal != "") 
						{
							$q->whereIn('request_models.taxPayment',$fiscal);
						}
					})
					->where('request_models.kind',16)
					->where('request_models.taxPayment',1)
					->where(function ($q) use ($name, $folio, $titleRequest, $fiscal, $department, $mindate, $maxdate, $stat, $typePayroll)
					{
						if ($name != "")
						{
							$q->whereHas('requestUser', function($q) use($name)
							{
								$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
							});
						}
						if ($folio != "")
						{
							$q->where('request_models.folio',$folio);
						}
						if ($titleRequest != "") 
						{
							$q->whereHas('nomina',function($q) use($titleRequest)
							{
								$q->where('title','LIKE','%'.$titleRequest.'%');
							});
						}
						if ($typePayroll != "")
						{
							$q->whereHas('nomina',function($q) use($typePayroll)
							{
								$q->whereIn('idCatTypePayroll',$typePayroll);
							});
						}
						if ($department != "")
						{
							$q->whereIn('idDepartment',$department);
						}
						if($mindate != "" && $maxdate != "")
						{
							if (in_array('001',$typePayroll)) 
							{
								$q->whereHas('nominasReal',function($q) use($mindate,$maxdate)
								{
									if ($mindate != "") 
									{
										$q->whereBetween('nominas.from_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}

									if ($maxdate != "") 
									{
										$q->whereBetween('nominas.to_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								});
								$q->orWhereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
							else
							{
								$q->whereBetween('authorizeDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							}
						}
					})
					->orderBy('authorizeDate','DESC')
					->orderBy('folio','DESC')
					->get();
				$kindRow = true;
				foreach($dataToWrite as $dtw)
				{
					$tmpArr = [];
					foreach($dtw->toArray() as $k => $r)
					{
						if(in_array($k,['sd', 'sdi', 'totalSalary', 'ptuForDays', 'ptuForSalary', 'totalPtu', 'exemptPtu', 'taxedPtu', 'totalPerceptions', 'isrRetentions', 'totalRetentions', 'netIncome', 'payment', 'por_pagar']))
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
					$kindRow = !$kindRow;
				}
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function nominaDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$request = App\RequestModel::find($request->folio);
			return view('reporte.administracion.partial.vernomina',['request'=>$request]);
		}   
	}

	public function paymentsZip(App\RequestModel $req)
	{
		$zip_file	= '/tmp/payments_'.$req->folio.'.zip';
		$zip		= new \ZipArchive();
		if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
		{
			$zip->addEmptyDir('comprobantes');
			foreach ($req->nominasReal->first()->nominaEmployee as $nom)
			{
				foreach ($nom->payments as $p)
				{
					foreach ($p->documentsPayments->pluck('path') as $doc)
					{
						$zip->addFile(public_path('/docs/payments/'.$doc), '/comprobantes/'.$doc);
					}
				}
			}
			$zip->close();
			return response()->download($zip_file);
		}
	}

	public function cfdiZip(App\RequestModel $req)
	{
		$zip_file	= storage_path('app').'/zip/cfdi_'.$req->folio.'.zip';
		$zip		= new \ZipArchive();
		if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))
		{
			$zip->addEmptyDir('timbres');
			foreach ($req->nominasReal->first()->nominaEmployee as $nom)
			{
				foreach ($nom->nominaCFDI as $cfdi)
				{
					$zip->addFile(storage_path('/stamped/'.$cfdi->uuid.'.pdf'), '/timbres/'.$cfdi->uuid.'.pdf');
					$zip->addFile(storage_path('/stamped/'.$cfdi->uuid.'.xml'), '/timbres/'.$cfdi->uuid.'.xml');
				}
			}
			$zip->close();
			return response()->download($zip_file);
		}
	}

	public function receiptZip(App\RequestModel $req)
	{
		$zip_file	= '/tmp/receipt_'.$req->folio.'.zip';
		$zip		= new \ZipArchive();
		if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
		{
			$zip->addEmptyDir('receipts');
			foreach ($req->nominasReal->first()->nominaEmployee as $nom)
			{
				if($nom->nominasEmployeeNF->first()->payroll_receipt()->exists())
				{
					$receipt = $nom->nominasEmployeeNF->first()->payroll_receipt;
					$zip->addFile(storage_path($receipt->path), $receipt->path);
				}
			}
			$zip->close();
			return response()->download($zip_file);
		}
	}
}
