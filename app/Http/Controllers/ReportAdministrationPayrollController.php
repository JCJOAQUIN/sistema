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

class ReportAdministrationPayrollController extends Controller
{
	private $module_id = 96;
	public function payrollReport(Request $request)
	{
		if (Auth::user()->module->where('id',156)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$idEnterprise   = $request->idEnterprise;
			$idArea         = $request->idArea;
			$idDepartment   = $request->idDepartment;
			$account        = $request->account;
			$name           = $request->name;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$status         = $request->status;
			$folio          = $request->folio;

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

			$searchUser     = App\User::select('users.id')
								->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
								->get();

			$nominas        = App\NominaAppEmp::select(['idNominaApplication'])
								->where(function($query) use ($idEnterprise,$idArea,$idDepartment,$account)
								{
									if ($idEnterprise != "") 
									{
										$query->whereIn('idEnterprise',$idEnterprise);
									}
									if ($idDepartment != "") 
									{
										$query->whereIn('idDepartment',$idDepartment);
									}
									if ($idArea != "") 
									{
										$query->whereIn('idArea',$idArea);
									}
									if ($account != "") 
									{
										$query->where('idAccount',$account);
									}
								})
								->get();
			
			$requests       = App\RequestModel::where('kind',2)
								->whereIn('status',[4,5,10,11,12])
								->where(function ($query) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
								{
									if ($folio != "") 
									{
										$query->where('folio',$folio);
									}
									if ($nominas != "") 
									{
										$query->whereHas('nominas',function($query) use ($nominas)
										{
											$query->whereIn('idNominaApplication',$nominas);
										});
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
								->paginate(10);

			return view('reporte.administracion.complementonomina',
				[
					'id'            =>$data['father'],
					'title'         =>$data['name'],
					'details'       =>$data['details'],
					'child_id'      =>$this->module_id,
					'option_id'     =>156,
					'requests'      => $requests,
					'idEnterprise'  => $idEnterprise,
					'idArea'        => $idArea,
					'idDepartment'  => $idDepartment,
					'account'       => $account,
					'name'          => $name,
					'status'        => $status,
					'mindate'       => $request->mindate,
					'maxdate'       => $request->maxdate,
					'folio'         => $folio
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function payrollExcel(Request $request)
	{
		if (Auth::user()->module->where('id',156)->count()>0)
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
			// $enterprise  = $request->enterprise_export;
			// $direction   = $request->direction_export;
			// $department  = $request->department_export;
			// $account = $request->account_export;
			// $name        = $request->name_export;
			// $min     = null;
			// $max     = null;
			// $status      = $request->status_export;
			// $folio       = $request->folio_export;

			// if($request->min_export != null)
			// {
			//  $date1      = strtotime($request->min_export);
			//  $mindate    = date('Y-m-d',$date1);
			//  $date2      = strtotime($request->max_export);
			//  $maxdate    = date('Y-m-d',$date2);
			//  $min        = $mindate;
			//  $max        = $maxdate;
			// }

			$searchUser     = App\User::select('users.id')
							->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
							->get();

			$nominas        = App\NominaAppEmp::select(['idNominaApplication'])
							->where(function($query) use ($idEnterprise,$idArea,$idDepartment,$account)
							{
								if ($idEnterprise != "") 
								{
									$query->whereIn('idEnterprise',$idEnterprise);
								}
								if ($idDepartment != "") 
								{
									$query->whereIn('idDepartment',$idDepartment);
								}
								if ($idArea != "") 
								{
									$query->whereIn('idArea',$idArea);
								}
								if ($account != "") 
								{
									$query->whereIn('idAccount',$account);
								}
							})
							->get();
			Excel::create('Reporte-Complemento-de-nomina', function($excel) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
				{
					$excel->sheet('Reporte',function($sheet) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
					{
						$sheet->setStyle([
								'font' => [
									'name'  => 'Calibri',
									'size'  => 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);
						$sheet->setColumnFormat(array(
							'C' => 'yyyy-mm-dd',
							'G' => 'yyyy-mm-dd',
							'H' => '"$"#,##0.00_-',
							'M' => '0',
							'N' => '0',
							'O' => '0',
							'V' => '"$"#,##0.00_-',
						));
						$sheet->mergeCells('A1:V1');
						$sheet->cell('A1:V1', function($cells)
						{
							$cells->setBackground('#000000');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A2:V2', function($cells)
						{
							$cells->setBackground('#104f64');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:V2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});
						$sheet->row(1,['Reporte de Complemento de nómina']);
						$sheet->row(2,['Folio','Título','Fecha','Solicitante','Elaborado por','Estado','Fecha de elaboración','Monto','Nombre del Empleado','Empresa','Departamento','Dirección','Proyecto','Clasificación de gasto','Forma de pago','Banco','Tarjeta','Cuenta','Clave','Referencia','Razón de pago','Importe']);

				
						$requests = App\RequestModel::where('kind',2)
							->whereIn('status',[4,5,10,11,12])
							->where(function ($query) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
							{
								if ($folio != "") 
								{
									$query->where('folio',$folio);
								}
								if ($nominas != "") 
								{
									$query->whereHas('nominas',function($query) use ($nominas)
									{
										$query->whereIn('idNominaApplication',$nominas);
									});
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
							->get();
					$beginMerge = 2;
						foreach ($requests as $request)
						{
							$tempCount  = 0;
							$row        = [];
							$row[]      = $request->folio;
							$row[]      = $request->nominas->first()->title;
							$row[]      = $request->nominas->first()->datetitle;
							$row[]      = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
							$row[]      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
							$row[]      = $request->statusrequest->description;
							$row[]      = date('d-m-Y H:s',strtotime($request->fDate));
							$row[]      = $request->nominas->first()->amount;
							$first      = true;
							foreach($request->nominas->first()->noAppEmp as $noEmp)
							{
								if(!$first)
								{
									$row    = array();
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
									$row[]  = '';
								}
								else
								{
									$first  = false;
									$beginMerge++;
								}
								$row[]  = $noEmp->employee->name.' '.$noEmp->employee->last_name.' '.$noEmp->employee->scnd_last_name;
								$row[]  = $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
								$row[]  = $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
								$row[]  = $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
								$row[]  = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
								$row[]  = $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
								$row[]  = $noEmp->paymentMethod->method;
								$row[]  = $noEmp->bank;
								$row[]  = $noEmp->cardNumber.' ';
								$row[]  = $noEmp->account.' ';
								$row[]  = $noEmp->clabe.' ';
								$row[]  = $noEmp->reference;
								$row[]  = $noEmp->description;
								$row[]  = $noEmp->amount;
								$tempCount++;
								$sheet->appendRow($row);
							}
							$endMerge = $beginMerge+$tempCount-1;
							$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
							$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
							$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
							$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
							$sheet->mergeCells('E'.$beginMerge.':E'.$endMerge);
							$sheet->mergeCells('F'.$beginMerge.':F'.$endMerge);
							$sheet->mergeCells('G'.$beginMerge.':G'.$endMerge);
							$sheet->mergeCells('H'.$beginMerge.':H'.$endMerge);
							$beginMerge = $endMerge;
						}
				});
			})->export('xlsx');
		}
	}

	public function payrollExcelWithoutGrouping(Request $request)
	{
		if (Auth::user()->module->where('id',156)->count()>0)
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
			// $enterprise  = $request->enterprise_export;
			// $direction   = $request->direction_export;
			// $department  = $request->department_export;
			// $account = $request->account_export;
			// $name        = $request->name_export;
			// $min     = null;
			// $max     = null;
			// $status      = $request->status_export;
			// $folio       = $request->folio_export;

			// if($request->min_export != null)
			// {
			//  $date1      = strtotime($request->min_export);
			//  $mindate    = date('Y-m-d',$date1);
			//  $date2      = strtotime($request->max_export);
			//  $maxdate    = date('Y-m-d',$date2);
			//  $min        = $mindate;
			//  $max        = $maxdate;
			// }

			$searchUser     = App\User::select('users.id')
							->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
							->get();

			$nominas        = App\NominaAppEmp::select(['idNominaApplication'])
							->where(function($query) use ($idEnterprise,$idArea,$idDepartment,$account)
							{
								if ($idEnterprise != "") 
								{
									$query->whereIn('idEnterprise',$idEnterprise);
								}
								if ($idDepartment != "") 
								{
									$query->whereIn('idDepartment',$idDepartment);
								}
								if ($idArea != "") 
								{
									$query->whereIn('idArea',$idArea);
								}
								if ($account != "") 
								{
									$query->whereIn('idAccount',$account);
								}
							})
							->get();
			Excel::create('Reporte-Complemento-de-nomina-sin-agrupar', function($excel) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
				{
					$excel->sheet('Reporte',function($sheet) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
					{
						$sheet->setStyle([
								'font' => [
									'name'  => 'Calibri',
									'size'  => 12
								],
								'alignment' => [
									'vertical' => 'center',
								]
						]);
						$sheet->setColumnFormat(array(
							'C' => 'yyyy-mm-dd',
							'G' => 'yyyy-mm-dd',
							'H' => '"$"#,##0.00_-',
							'M' => '0',
							'N' => '0',
							'O' => '0',
							'V' => '"$"#,##0.00_-',
						));
						$sheet->mergeCells('A1:V1');
						$sheet->cell('A1:V1', function($cells)
						{
							$cells->setBackground('#000000');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A2:V2', function($cells)
						{
							$cells->setBackground('#104f64');
							$cells->setFontColor('#ffffff');
						});
						$sheet->cell('A1:V2', function($cells)
						{
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});
						$sheet->row(1,['Reporte de Complemento de nómina']);
						$sheet->row(2,['Folio','Título','Fecha','Solicitante','Elaborado por','Estado','Fecha de elaboración','Monto','Nombre del Empleado','Empresa','Departamento','Dirección','Proyecto','Clasificación de gasto','Forma de pago','Banco','Tarjeta','Cuenta','Clave','Referencia','Razón de pago','Importe']);

				
						$requests       = App\RequestModel::where('kind',2)
											->whereIn('status',[4,5,10,11,12])
											->where(function ($query) use ($name, $mindate, $maxdate,$searchUser,$nominas,$status,$folio)
											{
												if ($folio != "") 
												{
													$query->wherE('folio',$folio);
												}
												if ($nominas != "") 
												{
													$query->whereHas('nominas',function($query) use ($nominas)
													{
														$query->whereIn('idNominaApplication',$nominas);
													});
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
											->get();
					$beginMerge = 2;
						foreach ($requests as $request)
						{
							$tempCount  = 0;
							$row        = [];
							$row[]      = $request->folio;
							$row[]      = $request->nominas->first()->title;
							$row[]      = $request->nominas->first()->datetitle;
							$row[]      = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
							$row[]      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
							$row[]      = $request->statusrequest->description;
							$row[]      = date('d-m-Y H:s',strtotime($request->fDate));
							$row[]      = $request->nominas->first()->amount;
							$first      = true;
							foreach($request->nominas->first()->noAppEmp as $noEmp)
							{
								if(!$first)
								{
									$row    = array();
									$row[]  = $request->folio;
									$row[]  = $request->nominas->first()->title;
									$row[]  = $request->nominas->first()->datetitle;
									$row[]  = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
									$row[]  = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
									$row[]  = $request->statusrequest->description;
									$row[]  = date('d-m-Y H:s',strtotime($request->fDate));
									$row[]  = $request->nominas->first()->amount;
								}
								else
								{
									$first  = false;
									$beginMerge++;
								}
								$row[]  = $noEmp->employee->name.' '.$noEmp->employee->last_name.' '.$noEmp->employee->scnd_last_name;
								$row[]  = $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
								$row[]  = $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
								$row[]  = $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
								$row[]  = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
								$row[]  = $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
								$row[]  = $noEmp->paymentMethod->method;
								$row[]  = $noEmp->bank;
								$row[]  = $noEmp->cardNumber.' ';
								$row[]  = $noEmp->account.' ';
								$row[]  = $noEmp->clabe.' ';
								$row[]  = $noEmp->reference;
								$row[]  = $noEmp->description;
								$row[]  = $noEmp->amount;
								$tempCount++;
								$sheet->appendRow($row);
							}
						}
				});
			})->export('xlsx');
		}
	}

	public function payrollDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$details  = "";
			$request  = App\RequestModel::find($request->folio);

			return view('reporte.administracion.partial.modal_complemento_nomina',['request'=>$request]);
			
			$details = "";
					$details .= "<div class='modal-content'>".
								"<div class='modal-header'>".
								"<span class='close exit'>&times;</span>".
								"</div>".
								"<div class='modal-body'>".
								"<center>".
								"<strong>DATOS DEL EMPLEADO</strong>".
								"</center>".
								"<div class='divisor'>".
								"<div class='gray-divisor'></div>".
								"<div class='orange-divisor'></div>".
								"<div class='gray-divisor'></div>".
								"</div>".
								"<div class='form-container'>".
								"<div class='table-responsive'>".
								"<table id='table2' class='table-no-bordered'>".
								"<thead>".
								"<th># Empleado</th>".
								"<th>Nombre del Empleado</th>".
								"<th>Empresa</th>".
								"<th>Proyecto</th>".
								"<th hidden>Departamento</th>".
								"<th hidden>Dirección</th>".
								"<th hidden>Clasificación de gasto</th>".
								"<th>Forma de pago</th>".
								"<th hidden>Banco</th>".
								"<th style='display: none;'># Tarjeta</th>".
								"<th style='display: none;'>Cuenta</th>".
								"<th style='display: none;'>CLABE</th>".
								"<th>Referencia</th>".
								"<th>Importe</th>".
								"<th>Razon</th>".
								"<th>Acción</th>".
								"</thead>".
								"<tbody id='body-payroll' class='request-validate'>";

								foreach(App\NominaAppEmp::join('users','idUsers','id')->where('idNominaApplication',$request->nominas->first()->idNominaApplication)->get() as $noEmp)
								{
									$enterpriseName = $noEmp->enterprise()->exists() ? $noEmp->enterprise->name : 'No hay';
									$departmentName = $noEmp->department()->exists() ? $noEmp->department->name : 'No hay';
									$directionName  = $noEmp->area()->exists() ? $noEmp->area->name : 'No hay';
									$projectName    = $noEmp->project()->exists() ? $noEmp->project->proyectName : 'No hay';
									$accountName    = $noEmp->accounts()->exists() ? $noEmp->accounts->account.' '.$noEmp->accounts->description : 'No hay';
									
									$details .= "<tr>".
												"<td>". $noEmp->idUsers ."<input readonly class='input-table iduser' type='hidden' name='t_employee_number[]' value='". $noEmp->idUsers ."'></td>".
												"<td>". $noEmp->name ." ". $noEmp->last_name ." ". $noEmp->scnd_last_name ."<input readonly class='input-table name' type='hidden' value='". $noEmp->name ."'>".
												"<input readonly class='input-table last_name' type='hidden' value='". $noEmp->last_name ."'>".
												"<input readonly class='input-table scnd_last_name' type='hidden' value='". $noEmp->scnd_last_name ."'></td>".
												"<td>".$enterpriseName."<input readonly class='input-table enterprise' type='hidden' name='t_enterprise[]' value='".$enterpriseName."'></td>".
												"<td>".$projectName."<input readonly class='input-table project' type='hidden' name='t_project[]' value='".$projectName."'></td>".
												"<td hidden>".$departmentName."<input readonly class='input-table department' type='hidden' name='t_department[]' value='".$departmentName."'></td>".
												"<td hidden>".$directionName."<input readonly class='input-table area' type='hidden' name='t_direction[]' value='".$directionName."'></td>".
												"<td hidden>".$accountName."<input readonly class='input-table accounttext' type='hidden' name='t_accountid[]' value='".$accountName."'></td>".
												"<td>".$noEmp->paymentMethod->method."</td>".
												"<td hidden>". $noEmp->bank ."<input readonly value='". $noEmp->bank ."' class='input-table bank' type='hidden' name='t_bank[]'></td>".
												"<td hidden>". $noEmp->cardNumber ."<input value='". $noEmp->cardNumber ."' readonly class='input-table cardNumber' type='hidden' name='t_card_number[]'></td>".
												"<td hidden>". $noEmp->account ."<input value='". $noEmp->account ."' readonly class='input-table account' type='hidden' name='t_account[]'></td>".
												"<td hidden>". $noEmp->clabe ."<input value='". $noEmp->clabe ."' readonly value='' class='input-table clabe' type='hidden' name='t_clabe[]'></td>".
												"<td>". $noEmp->reference ."<input value='". $noEmp->reference ."' readonly class='input-table reference' type='hidden' name='t_reference[]'></td>".
												"<td>". $noEmp->amount ."<input value='". $noEmp->amount ."' readonly class='input-table importe' type='hidden' name='t_amount[]'></td>".
												"<td>". $noEmp->description ."<input readonly class='input-table description' type='hidden' name='t_reason_payment[]' value='". $noEmp->description ."'></td>".
												"<td><button class='btn btn-green' type='button' id='ver'>Ver datos</button></td>".
												"</tr>";
								}
								$details .= "</tbody>".
											"</table>".
											"</div>".
											"<br>".
											"</div>".
											"<div class='formulario' style='display: none; border: 1px solid #bbb6b6; padding: 10px; width: 600px; margin: 0px auto; border-radius: 10px;'>".
											"<table class='employee-details'>".
											"<tbody>".
											"<tr>".
											"<td><b>Nombre:</b></td>".
											"<td><label id='nameEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Empresa:</b></td>".
											"<td><label id='enterprise'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Departamento:</b></td>".
											"<td><label id='department'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Dirección:</b></td>".
											"<td><label id='area'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Proyecto:</b></td>".
											"<td><label id='project'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Clasificación del gasto:</b></td>".
											"<td><label id='accounttext'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Banco:</b></td>".
											"<td><label id='idBanksEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Número de Tarjeta:</b></td>".
											"<td><label id='card_numberEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Cuenta Bancaria:</b></td>".
											"<td><label id='accountEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>CLABE:</b></td>".
											"<td><label id='clabeEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Referencia:</b></td>".
											"<td><label id='referenceEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Importe:</b></td>".
											"<td><label id='amountEmp'></label></td>".
											"</tr>".
											"<tr>".
											"<td><b>Razón de pago:</b></td>".
											"<td><label id='reason_paymentEmp'></label></td>".
											"</tr>".
											"</tbody>".
											"</table>".
											"<div class='form-container'>".
											"<p>".
											"<button type='button' name='canc' id='exit' class='btn btn-green'>« Ocultar</button>".
											"</p>".
											"</div>".
											"</div>".
											"<div class='form-container'>".
											"<div class='total-diplayed'>".
											"<b>TOTAL:".$request->nominas->first()->amount."</b>".
											"</div>".
											"</div>";
								$details.= "<center><button type='button' class='btn btn-green exit' title='Ocultar'>« Ocultar</button></center><br>"."</div></div>";
				return Response($details);
		}
	}
}
