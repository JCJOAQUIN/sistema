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

class ReportAdministrationExpensesRequestController extends Controller
{
	private $module_id = 96;
	public function expensesRequestReport(Request $request)
	{
		if (Auth::user()->module->where('id',128)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$enterprise			= $request->enterprise;
			$direction			= $request->direction;
			$department			= $request->department;
			$project			= $request->project;
			$account			= $request->account;
			$name				= $request->name;
			$kind				= $request->kind;
			$status				= $request->status;
			$folio				= $request->folio;
			$wbs				= $request->wbs;
			$title_search		= $request->title_search;
			$mindate			= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$mindate_review		= $request->mindate_review!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_review)->format('Y-m-d') : null;
			$maxdate_review		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_review)->format('Y-m-d') : null;
			$mindate_authorize	= $request->mindate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_authorize)->format('Y-m-d') : null;
			$maxdate_authorize	= $request->maxdate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_authorize)->format('Y-m-d') : null;

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

			if(($mindate_review=="" && $maxdate_review!="") || ($mindate_review!="" && $maxdate_review=="") || ($mindate_review!="" && $maxdate_review!=""))
			{
				$initRange  = $mindate_review;
				$endRange   = $maxdate_review;

				if(($mindate_review=="" && $maxdate_review!="") || ($mindate_review!="" && $maxdate_review==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_review!="" && $maxdate_review!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			if(($mindate_authorize=="" && $maxdate_authorize!="") || ($mindate_authorize!="" && $maxdate_authorize=="") || ($mindate_authorize!="" && $maxdate_authorize!=""))
			{
				$initRange  = $mindate_authorize;
				$endRange   = $maxdate_authorize;

				if(($mindate_authorize=="" && $maxdate_authorize!="") || ($mindate_authorize!="" && $maxdate_authorize==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_authorize!="" && $maxdate_authorize!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}
			
			$requests          = App\RequestModel::whereIn('kind',[1,2,3,8,9,11,12,13,14,15,16,17])
				->where(function($permissionEnt)
				{
					$permissionEnt->where(function($q)
					{
						$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
									});
								});
							})
							->orWhereNull('request_models.idEnterprise');
					});
				})
				->where(function($permissionDep)
				{
					$permissionDep->where(function($q)
					{
						$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('department',Auth::user()->inChargeDep(128)->pluck('departament_id'));
									});
								});
							})
							->orWhereNull('request_models.idDepartment');
					});
				})
				->where(function($permissionDep)
				{
					$permissionDep->where(function($q)
					{
						$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('project',Auth::user()->inChargeProject(128)->pluck('project_id'));
									});
								});
							})
							->orWhereNull('request_models.idProject');
					});
				})
				->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$kind,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$project,$wbs,$title_search)
				{
					if($title_search != '')
					{
						$query->where(function($q) use($title_search)
						{
							$q->whereHas('purchases',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('expenses',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('refunds',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('resource',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('nomina',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('purchaseRecord',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('loanEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('purchaseEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('groups',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('movementsEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							});
						});
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($folio != "") 
					{
						$query->where('folio',$folio);
					}
					if ($kind != "")
					{
						$query->whereIn('request_models.kind',$kind);
					}
					if ($enterprise != "")
					{
						$query->where(function($q) use($enterprise)
						{
							$q->whereIn('request_models.idEnterprise',$enterprise)
								->orWhereHas('nomina',function($q) use($enterprise)
								{
									$q->whereHas('nominaEmployee',function($q) use($enterprise)
									{
										$q->whereHas('workerData',function($q) use($enterprise)
										{
											$q->whereIn('enterprise',$enterprise);
										});
									});
								});
						});
					}
					if ($project != "")
					{
						$query->where(function($q) use($project)
						{
							$q->whereIn('request_models.idProject',$project)
								->orWhereHas('nomina',function($q) use($project)
								{
									$q->whereHas('nominaEmployee',function($q) use($project)
									{
										$q->whereHas('workerData',function($q) use($project)
										{
											$q->whereIn('project',$project);
										});
									});
								});
						});
					}
					if ($direction != "")
					{
						$query->where(function($q) use($direction)
						{
							$q->whereIn('request_models.idArea',$direction)
								->orWhereHas('nomina',function($q) use($direction)
								{
									$q->whereHas('nominaEmployee',function($q) use($direction)
									{
										$q->whereHas('workerData',function($q) use($direction)
										{
											$q->whereIn('direction',$direction);
										});
									});
								});
						});
					}
					if ($department != "")
					{
						$query->where(function($q) use($department)
						{
							$q->whereIn('request_models.idDepartment',$department)
								->orWhereHas('nomina',function($q) use($department)
								{
									$q->whereHas('nominaEmployee',function($q) use($department)
									{
										$q->whereHas('workerData',function($q) use($department)
										{
											$q->whereIn('department',$department);
										});
									});
								});
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser',function($q) use ($name)
						{
							$q->whereRaw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name) LIKE '%$name%'");
						});
					}
					if ($status != "") 
					{
						$query->whereIn('status',$status);
					}
					else
					{
						$query->whereIn('status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->where(function($q) use ($wbs)
						{
							$q->whereIn('request_models.code_wbs',$wbs)
								->orWhereHas('fromRequisition', function($q) use ($wbs)
								{
									$q->whereIn('code_wbs',$wbs);
								})
								->orWhereHas('nomina',function($q) use($wbs)
								{
									$q->whereHas('nominaEmployee',function($q) use($wbs)
									{
										$q->whereHas('workerData',function($q) use($wbs)
										{
											$q->whereHas('employeeHasWbs',function($q) use($wbs)
											{
												$q->whereIn('employee_w_b_s.cat_code_w_bs_id',$wbs);
											});
										});
									});
								});
						});
					}
				})
				->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
				->whereNull('adjustment_folios.idFolio')
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(15);
			return view('reporte.administracion.gastos',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 128,
					'enterprise'		=> $enterprise,
					'direction'			=> $direction,
					'department'		=> $department,
					'project'			=> $project,
					'account'			=> $account,
					'name'				=> $name,
					'kind'				=> $kind,
					'status'			=> $status,
					'folio'				=> $folio,
					'mindate'			=> $request->mindate,
					'maxdate'			=> $request->maxdate,
					'mindate_review'	=> $request->mindate_review,
					'maxdate_review'	=> $request->maxdate_review,
					'mindate_authorize'	=> $request->mindate_authorize,
					'maxdate_authorize'	=> $request->maxdate_authorize,
					'requests'			=> $requests,
					'wbs'				=> $wbs,
					'title_search'		=> $title_search
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function expensesWbs(Request $request)
	{
		$wbs = App\CatCodeWBS::where('project_id',$request->id[0])->get();
		if($wbs != '')
		{
			$options = '';
			foreach ($wbs as $v)
			{
				$options .= '<option value ="'.$v->id.'">'.$v->code_wbs.'</option>';
			}
			return $options;
		}
		else
		{
			return '';
		}
	}

	public function expensesRequestDetail(Request $request)
	{
		$details    = "";
		$taxes      = 0;
		$request    = App\RequestModel::find($request->folio);
		$table_data = [];

		switch ($request->kind) 
		{
			case 1:
				return view('reporte.administracion.partial.modal_compras',['request'=>$request]);
				
				break;

			case 2:
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
				break;

			case 3:
				return view('reporte.administracion.partial.modal_comprobacion_gasto',['request'=>$request]);
				break;

			case 8:
				return view('reporte.administracion.partial.modal_asignacion_recursos',['request'=>$request]);
				break;

			case 9:
				return view('reporte.administracion.partial.modal_reembolso',['request'=>$request]);
				break;

			case 11:
				return view('reporte.administracion.partial.movimiento_entre_cuentas.ajuste_movimientos',['request' => $request]);
				break;

			case 12:
				return view('reporte.administracion.partial.movimiento_entre_cuentas.prestamo',['request' => $request]);
				break;

			case 13:
				return view('reporte.administracion.partial.movimiento_entre_cuentas.compra_inter_empresas',['request' => $request]);
				break;

			case 14:
				return view('reporte.administracion.partial.movimiento_entre_cuentas.grupos',['request' => $request]);
				break;

			case 15:
				return view('reporte.administracion.partial.movimiento_entre_cuentas.movimiento_misma_empresa',['request' => $request]);
				break;

			case 16:
				return view('reporte.administracion.partial.vernomina')->with('request',$request);
				break;

			case 17:
				return view('reporte.administracion.partial.modal_registro_compra')->with('request',$request);
				break;

			default:
				# code...
				break;
		}
	}

	public function expensesRequestWbsTotalExcelReport(Request $request)
	{
		if (Auth::user()->module->where('id',128)->count()>0)
		{
			$enterprise			= $request->enterprise;
			$direction			= $request->direction;
			$department			= $request->department;
			$project			= $request->project;
			$account			= $request->account;
			$name				= $request->name;
			$kind				= $request->kind;
			$status				= $request->status;
			$folio				= $request->folio;
			$wbs				= $request->wbs;
			$title_search		= $request->title_search;
			$mindate			= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$mindate_review		= $request->mindate_review!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_review)->format('Y-m-d') : null;
			$maxdate_review		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_review)->format('Y-m-d') : null;
			$mindate_authorize	= $request->mindate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_authorize)->format('Y-m-d') : null;
			$maxdate_authorize	= $request->maxdate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_authorize)->format('Y-m-d') : null;
			
			$wbs_query			= DB::table('request_models')->selectRaw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs)) as wbs, GROUP_CONCAT(request_models.folio) as id')
				->whereIn('request_models.kind',[1,2,3,8,9,11,12,13,14,15,16,17])
				->where(function($permissionEnt)
				{
					$permissionEnt->where(function($q)
					{
						$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
							->orWhereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
					});
				})
				->where(function($permissionDep)
				{
					$permissionDep->where(function($q)
					{
						$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
							->orWhereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'))
							->orWhereNull('request_models.idDepartment');
					});
				})
				->where(function($permissionProject)
				{
					$permissionProject->where(function($q)
					{
						$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
							->orWhereIn('worker_datas.project',Auth::user()->inChargeProject(128)->pluck('project_id'))
							->orWhereNull('request_models.idProject');
					});
				})
				->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$kind,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$project,$wbs,$title_search)
				{
					if($title_search != '')
					{
						$query->where(function($q) use($title_search)
						{
							$q->where('purchases.title','LIKE','%'.$title_search.'%')
								->orWhere('expenses.title','LIKE','%'.$title_search.'%')
								->orWhere('resources.title','LIKE','%'.$title_search.'%')
								->orWhere('loan_enterprises.title','LIKE','%'.$title_search.'%')
								->orWhere('purchase_enterprises.title','LIKE','%'.$title_search.'%')
								->orWhere('groups.title','LIKE','%'.$title_search.'%')
								->orWhere('movements_enterprises.title','LIKE','%'.$title_search.'%')
								->orWhere('purchase_records.title','LIKE','%'.$title_search.'%')
								->orWhere('refunds.title','LIKE','%'.$title_search.'%')
								->orWhere('nominas.title','LIKE','%'.$title_search.'%');
						});
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($kind != "")
					{
						$query->whereIn('request_models.kind',$kind);
					}
					if ($enterprise != "")
					{
						$query->where(function($q) use($enterprise)
						{
							$q->whereIn('request_models.idEnterprise',$enterprise)
								->orWhereIn('worker_datas.enterprise',$enterprise);
						});
					}
					if ($project != "")
					{
						$query->where(function($q) use($project)
						{
							$q->whereIn('request_models.idProject',$project)
								->orWhereIn('worker_datas.project',$project);
						});
					}
					if ($direction != "")
					{
						$query->where(function($q) use($direction)
						{
							$q->whereIn('request_models.idArea',$direction)
								->orWhereIn('worker_datas.direction',$direction);
						});
					}
					if ($department != "")
					{
						$query->where(function($q) use($department)
						{
							$q->whereIn('request_models.idDepartment',$department)
								->orWhereIn('worker_datas.department',$department);
						});
					}
					if($name != "")
					{
						$query->whereRaw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE '%$name%'");
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->where(function($q) use ($wbs)
						{
							$q->whereIn('request_models.code_wbs',$wbs)
								->orWhereIn('req.code_wbs',$wbs)
								->orWhereIn('nom_wbs.wbs_id',$wbs);
						});
					}
				})
				->orderBy('request_models.kind','ASC')
				->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
				->leftJoin('request_models as req','request_models.idRequisition','req.folio')
				->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
				->leftJoin('users as request_user','request_models.idRequest','request_user.id')
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
					->on('request_models.kind','=','purchases.idKind');
				})
				->leftJoin('expenses',function($q)
				{
					$q->on('request_models.folio','=','expenses.idFolio')
					->on('request_models.kind','=','expenses.idKind');
				})
				->leftJoin('resources',function($q)
				{
					$q->on('request_models.folio','=','resources.idFolio')
					->on('request_models.kind','=','resources.idKind');
				})
				->leftJoin('loan_enterprises',function($q)
				{
					$q->on('request_models.folio','=','loan_enterprises.idFolio')
					->on('request_models.kind','=','loan_enterprises.idKind');
				})
				->leftJoin('purchase_enterprises',function($q)
				{
					$q->on('request_models.folio','=','purchase_enterprises.idFolio')
					->on('request_models.kind','=','purchase_enterprises.idKind');
				})
				->leftJoin('groups',function($q)
				{
					$q->on('request_models.folio','=','groups.idFolio')
					->on('request_models.kind','=','groups.idKind');
				})
				->leftJoin('movements_enterprises',function($q)
				{
					$q->on('request_models.folio','=','movements_enterprises.idFolio')
					->on('request_models.kind','=','movements_enterprises.idKind');
				})
				->leftJoin('purchase_records',function($q)
				{
					$q->on('request_models.folio','=','purchase_records.idFolio')
					->on('request_models.kind','=','purchase_records.idKind');
				})
				->leftJoin('refunds',function($q)
				{
					$q->on('request_models.folio','=','refunds.idFolio')
					->on('request_models.kind','=','refunds.idKind');
				})
				->leftJoin('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
					->on('request_models.kind','=','nominas.idKind');
				})
				->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
				->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
				->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->orderBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'),'ASC')
				->groupBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'))
				->get();
			if($wbs_query->count() == 0)
			{
				$alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
				return back()->with('alert',$alert);
			}
			$wbs_query = $wbs_query->sortBy('wbs');
			$wbs_query = $wbs_query->values()->all();
			$border = (new BorderBuilder())
				->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$darkCell        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setFontBold()->build();
			$lightRow       = (new StyleBuilder())->setBorder($border)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Gastos-totales-por-WBS.xlsx');
			$writer->getCurrentSheet()->setName('Gastos-totales');
			$mainHeaderArr = ['','MXN Obra','USD Obra','Nómina'];
			$tmpMHArr = [];
			$tmpTotal = [0,0,0];
			foreach($mainHeaderArr as $k => $mh)
			{
				$tmpMHArr[] = WriterEntityFactory::createCell($mh,$darkCell);
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr,$lightRow);
			$writer->addRow($rowFromValues);
			foreach($wbs_query as $key => $wbs_selected)
			{
				if($wbs_selected->wbs == "" || $wbs_selected->wbs == null)
				{
					$requests = DB::table('request_models')->selectRaw(
							'IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,nom_wbs.wbs)) as wbs,
							IF(request_models.kind = 16,"Nómina","Monto") as totalKind,
							IF(
								request_models.kind = 1,
								purchases.typeCurrency,
								IF(
									request_models.kind = 3,
									expenses.currency,
									IF(
										request_models.kind = 8,
										resources.currency,
										IF(
											request_models.kind = 9,
											refunds.currency,
											IF(
												request_models.kind = 12,
												loan_enterprises.currency,
												IF(
													request_models.kind = 13,
													purchase_enterprises.typeCurrency,
													IF(
														request_models.kind = 14,
														groups.typeCurrency,
														IF(
															request_models.kind = 15,
															movements_enterprises.typeCurrency,
															IF(
																request_models.kind = 16,
																"MXN",
																IF(
																	request_models.kind = 17,
																	purchase_records.typeCurrency,
																	""
																)
															)
														)
													)
												)
											)
										)
									)
								)
							) as currency,
							IF(
								request_models.kind = 16,
								SUM(
									IF(
										nominas.type_nomina != 1,
										nomina_employee_n_fs.amount,
										IF(
											nominas.idCatTypePayroll = "001",
											salaries.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "002",
												bonuses.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
													liquidations.totalPerceptions,
													IF(
														nominas.idCatTypePayroll = "005",
														vacation_premia.totalPerceptions,
														IF(
															nominas.idCatTypePayroll = "006",
															profit_sharings.totalPerceptions,
															0
														)
													)
												)
											)
										)
									)
								),
								SUM(IFNULL(p.payment_amount,0))
							) as paid_amount
						')
						->whereIn('request_models.kind',[1,2,3,8,9,11,12,13,14,15,16,17])
						->where(function($permissionEnt)
						{
							$permissionEnt->where(function($q)
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
									->orWhereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
							});
						})
						->where(function($permissionDep)
						{
							$permissionDep->where(function($q)
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
									->orWhereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'))
									->orWhereNull('request_models.idDepartment');
							});
						})
						->where(function($permissionProject)
						{
							$permissionProject->where(function($q)
							{
								$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
									->orWhereIn('worker_datas.project',Auth::user()->inChargeProject(128)->pluck('project_id'))
									->orWhereNull('request_models.idProject');
							});
						})
						->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$kind,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$project,$wbs,$title_search)
						{
							if($title_search != '')
							{
								$query->where(function($q) use($title_search)
								{
									$q->where('purchases.title','LIKE','%'.$title_search.'%')
										->orWhere('expenses.title','LIKE','%'.$title_search.'%')
										->orWhere('resources.title','LIKE','%'.$title_search.'%')
										->orWhere('loan_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('purchase_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('groups.title','LIKE','%'.$title_search.'%')
										->orWhere('movements_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('purchase_records.title','LIKE','%'.$title_search.'%')
										->orWhere('refunds.title','LIKE','%'.$title_search.'%')
										->orWhere('nominas.title','LIKE','%'.$title_search.'%');
								});
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if ($kind != "")
							{
								$query->whereIn('request_models.kind',$kind);
							}
							if ($enterprise != "")
							{
								$query->where(function($q) use($enterprise)
								{
									$q->whereIn('request_models.idEnterprise',$enterprise)
										->orWhereIn('worker_datas.enterprise',$enterprise);
								});
							}
							if ($project != "")
							{
								$query->where(function($q) use($project)
								{
									$q->whereIn('request_models.idProject',$project)
										->orWhereIn('worker_datas.project',$project);
								});
							}
							if ($direction != "")
							{
								$query->where(function($q) use($direction)
								{
									$q->whereIn('request_models.idArea',$direction)
										->orWhereIn('worker_datas.direction',$direction);
								});
							}
							if ($department != "")
							{
								$query->where(function($q) use($department)
								{
									$q->whereIn('request_models.idDepartment',$department)
										->orWhereIn('worker_datas.department',$department);
								});
							}
							if($name != "")
							{
								$q->whereRaw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE '%$name%'");
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
							if($wbs != "")
							{
								$query->where(function($q) use ($wbs)
								{
									$q->whereIn('request_models.code_wbs',$wbs)
										->orWhereIn('req.code_wbs',$wbs)
										->orWhereIn('nom_wbs.wbs_id',$wbs);
								});
							}
						})
						->whereRaw('IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,nom_wbs.wbs)) IS NULL')
						->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
						->orderBy('request_models.kind','ASC')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('request_models as req','request_models.idRequisition','req.folio')
						->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
						->leftJoin('users as request_user','request_models.idRequest','request_user.id')
						->leftJoin('purchases',function($q)
						{
							$q->on('request_models.folio','=','purchases.idFolio')
							->on('request_models.kind','=','purchases.idKind');
						})
						->leftJoin('expenses',function($q)
						{
							$q->on('request_models.folio','=','expenses.idFolio')
							->on('request_models.kind','=','expenses.idKind');
						})
						->leftJoin('resources',function($q)
						{
							$q->on('request_models.folio','=','resources.idFolio')
							->on('request_models.kind','=','resources.idKind');
						})
						->leftJoin('loan_enterprises',function($q)
						{
							$q->on('request_models.folio','=','loan_enterprises.idFolio')
							->on('request_models.kind','=','loan_enterprises.idKind');
						})
						->leftJoin('purchase_enterprises',function($q)
						{
							$q->on('request_models.folio','=','purchase_enterprises.idFolio')
							->on('request_models.kind','=','purchase_enterprises.idKind');
						})
						->leftJoin('groups',function($q)
						{
							$q->on('request_models.folio','=','groups.idFolio')
							->on('request_models.kind','=','groups.idKind');
						})
						->leftJoin('movements_enterprises',function($q)
						{
							$q->on('request_models.folio','=','movements_enterprises.idFolio')
							->on('request_models.kind','=','movements_enterprises.idKind');
						})
						->leftJoin('purchase_records',function($q)
						{
							$q->on('request_models.folio','=','purchase_records.idFolio')
							->on('request_models.kind','=','purchase_records.idKind');
						})
						->leftJoin('refunds',function($q)
						{
							$q->on('request_models.folio','=','refunds.idFolio')
							->on('request_models.kind','=','refunds.idKind');
						})
						->leftJoin('nominas',function($q)
						{
							$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
						})
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
						->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
						->orderBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'),'ASC')
						->groupBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'))
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, SUM(ROUND(amount/IFNULL(exchange_rate,1),2)) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						// ->leftJoin(
						//  DB::raw('(SELECT idFolio, idKind, idnominaEmployee, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, idnominaEmployee) AS p_nom'),function($q)
						//  {
						//      $q->on('request_models.folio','=','p_nom.idFolio')
						//      ->on('request_models.kind','=','p_nom.idKind')
						//      ->on('nomina_employees.idnominaEmployee','=','p_nom.idnominaEmployee');
						//  }
						// )
						->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->whereNull('adjustment_folios.idFolio')
						->groupBy('totalKind')
						->groupBy('currency')
						->get();
					$requests = collect($requests);
					$tmpArr = [];
					$tmpArr[] = WriterEntityFactory::createCell('Sin WBS',$darkCell);
					if($requests->where('totalKind','Monto')->where('currency','MXN')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Monto')->where('currency','MXN')->first()->paid_amount,$currencyFormat);
						$tmpTotal[0] = $tmpTotal[0] + $requests->where('totalKind','Monto')->where('currency','MXN')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					if($requests->where('totalKind','Monto')->where('currency','USD')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Monto')->where('currency','USD')->first()->paid_amount,$currencyFormat);
						$tmpTotal[1] = $tmpTotal[1] + $requests->where('totalKind','Monto')->where('currency','USD')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					if($requests->where('totalKind','Nómina')->where('currency','MXN')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Nómina')->where('currency','MXN')->first()->paid_amount,$currencyFormat);
						$tmpTotal[2] = $tmpTotal[2] + $requests->where('totalKind','Nómina')->where('currency','MXN')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$lightRow);
					$writer->addRow($rowFromValues);
				}
				else
				{
					$requests = DB::table('request_models')->selectRaw(
							'IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,nom_wbs.wbs)) as wbs,
							IF(request_models.kind = 16,"Nómina","Monto") as totalKind,
							IF(
								request_models.kind = 1,
								purchases.typeCurrency,
								IF(
									request_models.kind = 3,
									expenses.currency,
									IF(
										request_models.kind = 8,
										resources.currency,
										IF(
											request_models.kind = 9,
											refunds.currency,
											IF(
												request_models.kind = 12,
												loan_enterprises.currency,
												IF(
													request_models.kind = 13,
													purchase_enterprises.typeCurrency,
													IF(
														request_models.kind = 14,
														groups.typeCurrency,
														IF(
															request_models.kind = 15,
															movements_enterprises.typeCurrency,
															IF(
																request_models.kind = 16,
																"MXN",
																IF(
																	request_models.kind = 17,
																	purchase_records.typeCurrency,
																	""
																)
															)
														)
													)
												)
											)
										)
									)
								)
							) as currency,
							IF(
								request_models.kind = 16,
								SUM(
									IF(
										nominas.type_nomina != 1,
										nomina_employee_n_fs.amount,
										IF(
											nominas.idCatTypePayroll = "001",
											salaries.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "002",
												bonuses.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
													liquidations.totalPerceptions,
													IF(
														nominas.idCatTypePayroll = "005",
														vacation_premia.totalPerceptions,
														IF(
															nominas.idCatTypePayroll = "006",
															profit_sharings.totalPerceptions,
															0
														)
													)
												)
											)
										)
									)
								),
								SUM(IFNULL(p.payment_amount,0))
							) as paid_amount
						')
						->whereIn('request_models.kind',[1,2,3,8,9,11,12,13,14,15,16,17])
						->where(function($permissionEnt)
						{
							$permissionEnt->where(function($q)
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
									->orWhereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
							});
						})
						->where(function($permissionDep)
						{
							$permissionDep->where(function($q)
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
									->orWhereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'))
									->orWhereNull('request_models.idDepartment');
							});
						})
						->where(function($permissionProject)
						{
							$permissionProject->where(function($q)
							{
								$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
									->orWhereIn('worker_datas.project',Auth::user()->inChargeProject(128)->pluck('project_id'))
									->orWhereNull('request_models.idProject');
							});
						})
						->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$kind,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$project,$wbs,$title_search)
						{
							if($title_search != '')
							{
								$query->where(function($q) use($title_search)
								{
									$q->where('purchases.title','LIKE','%'.$title_search.'%')
										->orWhere('expenses.title','LIKE','%'.$title_search.'%')
										->orWhere('resources.title','LIKE','%'.$title_search.'%')
										->orWhere('loan_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('purchase_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('groups.title','LIKE','%'.$title_search.'%')
										->orWhere('movements_enterprises.title','LIKE','%'.$title_search.'%')
										->orWhere('purchase_records.title','LIKE','%'.$title_search.'%')
										->orWhere('refunds.title','LIKE','%'.$title_search.'%')
										->orWhere('nominas.title','LIKE','%'.$title_search.'%');
								});
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if ($kind != "")
							{
								$query->whereIn('request_models.kind',$kind);
							}
							if ($enterprise != "")
							{
								$query->where(function($q) use($enterprise)
								{
									$q->whereIn('request_models.idEnterprise',$enterprise)
										->orWhereIn('worker_datas.enterprise',$enterprise);
								});
							}
							if ($project != "")
							{
								$query->where(function($q) use($project)
								{
									$q->whereIn('request_models.idProject',$project)
										->orWhereIn('worker_datas.project',$project);
								});
							}
							if ($direction != "")
							{
								$query->where(function($q) use($direction)
								{
									$q->whereIn('request_models.idArea',$direction)
										->orWhereIn('worker_datas.direction',$direction);
								});
							}
							if ($department != "")
							{
								$query->where(function($q) use($department)
								{
									$q->whereIn('request_models.idDepartment',$department)
										->orWhereIn('worker_datas.department',$department);
								});
							}
							if($name != "")
							{
								$q->whereRaw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE '%$name%'");
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
							if($wbs != "")
							{
								$query->where(function($q) use ($wbs)
								{
									$q->whereIn('request_models.code_wbs',$wbs)
										->orWhereIn('req.code_wbs',$wbs)
										->orWhereIn('nom_wbs.wbs_id',$wbs);
								});
							}
						})
						->whereRaw('IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,nom_wbs.wbs)) = "'.$wbs_selected->wbs.'"')
						->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
						->orderBy('request_models.kind','ASC')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('request_models as req','request_models.idRequisition','req.folio')
						->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
						->leftJoin('users as request_user','request_models.idRequest','request_user.id')
						->leftJoin('purchases',function($q)
						{
							$q->on('request_models.folio','=','purchases.idFolio')
							->on('request_models.kind','=','purchases.idKind');
						})
						->leftJoin('expenses',function($q)
						{
							$q->on('request_models.folio','=','expenses.idFolio')
							->on('request_models.kind','=','expenses.idKind');
						})
						->leftJoin('resources',function($q)
						{
							$q->on('request_models.folio','=','resources.idFolio')
							->on('request_models.kind','=','resources.idKind');
						})
						->leftJoin('loan_enterprises',function($q)
						{
							$q->on('request_models.folio','=','loan_enterprises.idFolio')
							->on('request_models.kind','=','loan_enterprises.idKind');
						})
						->leftJoin('purchase_enterprises',function($q)
						{
							$q->on('request_models.folio','=','purchase_enterprises.idFolio')
							->on('request_models.kind','=','purchase_enterprises.idKind');
						})
						->leftJoin('groups',function($q)
						{
							$q->on('request_models.folio','=','groups.idFolio')
							->on('request_models.kind','=','groups.idKind');
						})
						->leftJoin('movements_enterprises',function($q)
						{
							$q->on('request_models.folio','=','movements_enterprises.idFolio')
							->on('request_models.kind','=','movements_enterprises.idKind');
						})
						->leftJoin('purchase_records',function($q)
						{
							$q->on('request_models.folio','=','purchase_records.idFolio')
							->on('request_models.kind','=','purchase_records.idKind');
						})
						->leftJoin('refunds',function($q)
						{
							$q->on('request_models.folio','=','refunds.idFolio')
							->on('request_models.kind','=','refunds.idKind');
						})
						->leftJoin('nominas',function($q)
						{
							$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
						})
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
						->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id, employee_w_b_s.cat_code_w_bs_id as wbs_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
						->orderBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'),'ASC')
						->groupBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'))
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, SUM(ROUND(amount/IFNULL(exchange_rate,1),2)) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						// ->leftJoin(
						//  DB::raw('(SELECT idFolio, idKind, idnominaEmployee, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, idnominaEmployee) AS p_nom'),function($q)
						//  {
						//      $q->on('request_models.folio','=','p_nom.idFolio')
						//      ->on('request_models.kind','=','p_nom.idKind')
						//      ->on('nomina_employees.idnominaEmployee','=','p_nom.idnominaEmployee');
						//  }
						// )
						->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->whereNull('adjustment_folios.idFolio')
						->groupBy('totalKind')
						->groupBy('currency')
						->get();
					$requests = collect($requests);
					$tmpArr = [];
					$tmpArr[] = WriterEntityFactory::createCell($wbs_selected->wbs,$darkCell);
					if($requests->where('totalKind','Monto')->where('currency','MXN')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Monto')->where('currency','MXN')->first()->paid_amount,$currencyFormat);
						$tmpTotal[0] = $tmpTotal[0] + $requests->where('totalKind','Monto')->where('currency','MXN')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					if($requests->where('totalKind','Monto')->where('currency','USD')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Monto')->where('currency','USD')->first()->paid_amount,$currencyFormat);
						$tmpTotal[1] = $tmpTotal[1] + $requests->where('totalKind','Monto')->where('currency','USD')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					if($requests->where('totalKind','Nómina')->where('currency','MXN')->count() > 0)
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)$requests->where('totalKind','Nómina')->where('currency','MXN')->first()->paid_amount,$currencyFormat);
						$tmpTotal[2] = $tmpTotal[2] + $requests->where('totalKind','Nómina')->where('currency','MXN')->first()->paid_amount;
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell((double)0,$currencyFormat);
					}
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$lightRow);
					$writer->addRow($rowFromValues);
				}
			}
			$tmpArr = [];
			$tmpArr[] = WriterEntityFactory::createCell('TOTALES',$darkCell);
			$tmpArr[] = WriterEntityFactory::createCell((double)round($tmpTotal[0],2),$currencyFormat);
			$tmpArr[] = WriterEntityFactory::createCell((double)round($tmpTotal[1],2),$currencyFormat);
			$tmpArr[] = WriterEntityFactory::createCell((double)round($tmpTotal[2],2),$currencyFormat);
			$rowFromValues = WriterEntityFactory::createRow($tmpArr,$lightRow);
			$writer->addRow($rowFromValues);
			return $writer->close();
		}
		else
		{
			return abort(404);
		}
	}

	public function expensesRequestWbsExcelReport(Request $request)
	{
		if (Auth::user()->module->where('id',128)->count()>0)
		{
			$enterprise			= $request->enterprise;
			$direction			= $request->direction;
			$department			= $request->department;
			$project			= $request->project;
			$account			= $request->account;
			$name				= $request->name;
			$kind				= $request->kind;
			$status				= $request->status;
			$folio				= $request->folio;
			$wbs				= $request->wbs;
			$title_search		= $request->title_search;
			$mindate			= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$mindate_review		= $request->mindate_review!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_review)->format('Y-m-d') : null;
			$maxdate_review		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_review)->format('Y-m-d') : null;
			$mindate_authorize	= $request->mindate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_authorize)->format('Y-m-d') : null;
			$maxdate_authorize	= $request->maxdate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_authorize)->format('Y-m-d') : null;
			$wbs_query = App\RequestModel::selectRaw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs)) as wbs')
				->whereIn('request_models.kind',[1,2,3,8,9,11,12,13,14,15,16,17])
				->where(function($permissionEnt)
				{
					$permissionEnt->where(function($q)
					{
						$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
									});
								});
							})
							->orWhereNull('request_models.idEnterprise');
					});
				})
				->where(function($permissionDep)
				{
					$permissionDep->where(function($q)
					{
						$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('department',Auth::user()->inChargeDep(128)->pluck('departament_id'));
									});
								});
							})
							->orWhereNull('request_models.idDepartment');
					});
				})
				->where(function($permissionProject)
				{
					$permissionProject->where(function($q)
					{
						$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('project',Auth::user()->inChargeProject(128)->pluck('project_id'));
									});
								});
							})
							->orWhereNull('request_models.idProject');
					});
				})
				->where(function ($query) use ($name,$enterprise,$direction,$department,$status,$kind,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize,$project,$wbs,$title_search)
				{
					if($title_search != '')
					{
						$query->where(function($q) use($title_search)
						{
							$q->whereHas('purchases',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('expenses',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('refunds',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('resource',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('nomina',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('purchaseRecord',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('loanEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('purchaseEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('groups',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							})
							->orWhereHas('movementsEnterprise',function($q) use($title_search)
							{
								$q->where('title','LIKE','%'.$title_search.'%');
							});
						});
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($kind != "")
					{
						$query->whereIn('request_models.kind',$kind);
					}
					if ($enterprise != "")
					{
						$query->where(function($q) use($enterprise)
						{
							$q->whereIn('request_models.idEnterprise',$enterprise)
								->orWhereHas('nomina',function($q) use($enterprise)
								{
									$q->whereHas('nominaEmployee',function($q) use($enterprise)
									{
										$q->whereHas('workerData',function($q) use($enterprise)
										{
											$q->whereIn('enterprise',$enterprise);
										});
									});
								});
						});
					}
					if ($project != "")
					{
						$query->where(function($q) use($project)
						{
							$q->whereIn('request_models.idProject',$project)
								->orWhereHas('nomina',function($q) use($project)
								{
									$q->whereHas('nominaEmployee',function($q) use($project)
									{
										$q->whereHas('workerData',function($q) use($project)
										{
											$q->whereIn('project',$project);
										});
									});
								});
						});
					}
					if ($direction != "")
					{
						$query->where(function($q) use($direction)
						{
							$q->whereIn('request_models.idArea',$direction)
								->orWhereHas('nomina',function($q) use($direction)
								{
									$q->whereHas('nominaEmployee',function($q) use($direction)
									{
										$q->whereHas('workerData',function($q) use($direction)
										{
											$q->whereIn('direction',$direction);
										});
									});
								});
						});
					}
					if ($department != "")
					{
						$query->where(function($q) use($department)
						{
							$q->whereIn('request_models.idDepartment',$department)
								->orWhereHas('nomina',function($q) use($department)
								{
									$q->whereHas('nominaEmployee',function($q) use($department)
									{
										$q->whereHas('workerData',function($q) use($department)
										{
											$q->whereIn('department',$department);
										});
									});
								});
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser',function($q) use ($name)
						{
							$q->whereRaw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name) LIKE '%$name%'");
						});
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->where(function($q) use ($wbs)
						{
							$q->whereIn('request_models.code_wbs',$wbs)
								->orWhereHas('fromRequisition', function($q) use ($wbs)
								{
									$q->whereIn('code_wbs',$wbs);
								})
								->orWhereHas('nomina',function($q) use($wbs)
								{
									$q->whereHas('nominaEmployee',function($q) use($wbs)
									{
										$q->whereHas('workerData',function($q) use($wbs)
										{
											$q->whereHas('employeeHasWbs',function($q) use($wbs)
											{
												$q->whereIn('employee_w_b_s.cat_code_w_bs_id',$wbs);
											});
										});
									});
								});
						});
					}
				})
				->orderBy('request_models.kind','ASC')
				->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
				->leftJoin('request_models as req','request_models.idRequisition','req.folio')
				->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
				->leftJoin('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
					->on('request_models.kind','=','nominas.idKind');
				})
				->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
				->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
				->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as nom_wbs'),'nom_wbs.wd_id','worker_datas.id')
				->groupBy(DB::raw('IF(nom_wbs.wbs IS NOT NULL, nom_wbs.wbs, IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs))'))
				->get();
			$wbs_query = $wbs_query->sortBy('wbs');
			$wbs_query = $wbs_query->values()->all();
			$noneBorder = (new BorderBuilder())
				->setBorderTop(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderBottom(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$rowLight       = (new StyleBuilder())->setBackgroundColor('FFFFFF')->setBorder($noneBorder)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$mhStyleCol6    = (new StyleBuilder())->setBackgroundColor('548235')->setFontColor(Color::WHITE)->build();
			$mhStyleCol7    = (new StyleBuilder())->setBackgroundColor('EC8500')->setFontColor(Color::WHITE)->build();
			$mhStyleCol8    = (new StyleBuilder())->setBackgroundColor('D8407D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol9    = (new StyleBuilder())->setBackgroundColor('C00001')->setFontColor(Color::WHITE)->build();
			$mhStyleCol10   = (new StyleBuilder())->setBackgroundColor('BF8F01')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol6    = (new StyleBuilder())->setBackgroundColor('A9D08E')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol7    = (new StyleBuilder())->setBackgroundColor('F3B084')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol8    = (new StyleBuilder())->setBackgroundColor('E0B5C7')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol9    = (new StyleBuilder())->setBackgroundColor('C07971')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol10   = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Gastos-por-WBS.xlsx');
			foreach($wbs_query as $key => $wbs_selected)
			{
				$sheetName = 'Sin WBS';
				if($wbs_selected->wbs != '')
				{
					$sheetName = substr($wbs_selected->wbs,0,30);
				}
				if($key == 0)
				{
					$writer->getCurrentSheet()->setName($sheetName);
				}
				else
				{
					$writer->addNewSheetAndMakeItCurrent();
					$writer->getCurrentSheet()->setName($sheetName);
				}
				unset($sheetName);
				$mainHeaderArr = ['Datos de la solicitud','','','','','','','','','','','Datos del solicitante','','','','','','','','Datos de revisión','','','','','','','','Datos de autorización','','','Datos la solicitud','','','','','','','','','','','','','','','','','Conceptos','','','','','','','','','','Etiquetas','Montos relacionados con otras solicitudes','','','','Total','','Pagos realizados','',''];
				$tmpMHArr      = [];
				foreach($mainHeaderArr as $k => $mh)
				{
					if($k <= 10)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
					}
					elseif($k <= 18)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
					}
					elseif($k <= 26)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
					}
					elseif($k <= 29)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
					}
					elseif($k <= 46)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
					}
					elseif($k <= 56)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
					}
					elseif($k <= 57)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
					}
					elseif($k <= 61)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
					}
					elseif($k <= 63)
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol9);
					}
					else
					{
						$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol10);
					}
				}
				unset($mainHeaderArr);
				$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
				unset($tmpMHArr);
				$writer->addRow($rowFromValues);
				unset($rowFromValues);
				$headerArr    = ['Folio','Folio de requisición','Estado de solicitud','Tipo','Comprobación','Folio de la solicitud de recurso','Título','Número de orden','WBS','EDT','Número de estimación','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Comentarios','Autorizada por','Fecha de autorización','Comentarios','Empresa Origen','Clasificación de Gasto Origen','Empresa Destino','Clasificación de Gasto Destino','Monto Total','Razón Social','Referencia','Método de pago','Banco','Cuenta','Tarjeta','Sucursal','Referencia','CLABE','Moneda','Convenio','Fiscal/No Fiscal','Cantidad','Unidad','Concepto','Clasificación de gasto','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro','Total a pagar','Moneda','Tasa de Cambio','Descripción','Total Pagado'];
				$tmpHeaderArr = [];
				foreach($headerArr as $k => $sh)
				{
					if($k <= 10)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
					}
					elseif($k <= 18)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
					}
					elseif($k <= 26)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
					}
					elseif($k <= 29)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
					}
					elseif($k <= 46)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
					}
					elseif($k <= 56)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
					}
					elseif($k <= 57)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
					}
					elseif($k <= 61)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
					}
					elseif($k <= 63)
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol9);
					}
					else
					{
						$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol10);
					}
				}
				unset($headerArr);
				$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
				unset($tmpHeaderArr);
				$writer->addRow($rowFromValues);
				unset($rowFromValues);
				$tmpFolio = '';
				$kindRow  = true;
				if($wbs_selected->wbs == '' || $wbs_selected->wbs == null)
				{
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							"" as idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							"" AS checkup,
							"" AS resource_folio,
							IF(request_models.kind = 12,CONCAT(loan_enterprises.title," - ",loan_enterprises.datetitle),IF(request_models.kind = 13,CONCAT(purchase_enterprises.title," - ",purchase_enterprises.datetitle),IF(request_models.kind = 14,CONCAT(groups.title," - ",groups.datetitle),""))) as title,
							IF(request_models.kind = 13,purchase_enterprises.numberOrder,IF(request_models.kind = 14,groups.numberOrder,"")) as order_number,
							"" as wbs,
							"" as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_enterprise,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_direction,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_department,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_project,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_enterprise,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_direction,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_department,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_project,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_account,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as authorize_Comment,
							IF(request_models.kind = 12,IF(loan_enterprises.idEnterpriseOriginR IS NULL,le_origin_ent.name,le_origin_ent_r.name),IF(request_models.kind = 13,IF(purchase_enterprises.idEnterpriseOriginR IS NULL,pe_origin_ent.name,pe_origin_ent_r.name),IF(request_models.kind = 14,IF(groups.idEnterpriseOriginR IS NULL,g_origin_ent.name,g_origin_ent_r.name),"No Aplica"))) as origin_enterprise,
							IF(request_models.kind = 12,IF(loan_enterprises.idAccAccOriginR IS NULL,CONCAT(le_origin_acc.account," ",le_origin_acc.description," (",le_origin_acc.content,")"),CONCAT(le_origin_acc_r.account," ",le_origin_acc_r.description," (",le_origin_acc_r.content,")")),IF(request_models.kind = 13,IF(purchase_enterprises.idAccAccOriginR IS NULL,CONCAT(pe_origin_acc.account," ",pe_origin_acc.description," (",pe_origin_acc.content,")"),CONCAT(pe_origin_acc_r.account," ",pe_origin_acc_r.description," (",pe_origin_acc_r.content,")")),IF(request_models.kind = 14,IF(groups.idAccAccOriginR IS NULL,CONCAT(g_origin_acc.account," ",g_origin_acc.description," (",g_origin_acc.content,")"),CONCAT(g_origin_acc_r.account," ",g_origin_acc_r.description," (",g_origin_acc_r.content,")")),"No Aplica"))) as origin_account,
							IF(request_models.kind = 12,IF(loan_enterprises.idEnterpriseDestinyR IS NULL,le_destiny_ent.name,le_destiny_ent_r.name),IF(request_models.kind = 13,IF(purchase_enterprises.idEnterpriseDestinyR IS NULL,pe_destiny_ent.name,pe_destiny_ent_r.name),IF(request_models.kind = 14,IF(groups.idEnterpriseDestinyR IS NULL,g_destiny_ent.name,g_destiny_ent_r.name),"No Aplica"))) as destination_enterprise,
							IF(request_models.kind = 12,IF(loan_enterprises.idAccAccDestinyR IS NULL,CONCAT(le_destiny_acc.account," ",le_destiny_acc.description," (",le_destiny_acc.content,")"),CONCAT(le_destiny_acc_r.account," ",le_destiny_acc_r.description," (",le_destiny_acc_r.content,")")),IF(request_models.kind = 13,IF(purchase_enterprises.idAccAccDestinyR IS NULL,CONCAT(pe_destiny_acc.account," ",pe_destiny_acc.description," (",pe_destiny_acc.content,")"),CONCAT(pe_destiny_acc_r.account," ",pe_destiny_acc_r.description," (",pe_destiny_acc_r.content,")")),IF(request_models.kind = 14,IF(groups.idAccAccDestinyR IS NULL,CONCAT(g_destiny_acc.account," ",g_destiny_acc.description," (",g_destiny_acc.content,")"),CONCAT(g_destiny_acc_r.account," ",g_destiny_acc_r.description," (",g_destiny_acc_r.content,")")),"No Aplica"))) as destination_account,
							IF(request_models.kind = 12,loan_enterprises.amount,IF(request_models.kind = 13,purchase_enterprises.amount,IF(request_models.kind = 14,IF(groups.operationType = "Salida",groups.amount,""),""))) as amount,
							IF(request_models.kind = 14,g_provider.businessName,"") as business_name,
							IF(request_models.kind = 13,purchase_enterprises.reference,IF(request_models.kind = 14,groups.reference,"")) as reference,
							IF(request_models.kind = 12,le_method.method,IF(request_models.kind = 13,pe_method.method,IF(request_models.kind = 14,g_method.method,""))) as payment_method,
							IF(request_models.kind = 13,pe_bank.description,IF(request_models.kind = 14,g_bank_data.description,"")) as provider_bank,
							IF(request_models.kind = 13,pe_bank_data.account,IF(request_models.kind = 14,g_banks.account,"")) as provider_account,
							"" as provider_card,
							IF(request_models.kind = 13,pe_bank_data.branch,IF(request_models.kind = 14,g_banks.branch,"")) as provider_branch,
							IF(request_models.kind = 13,pe_bank_data.reference,IF(request_models.kind = 14,g_banks.reference,"")) as provider_reference,
							IF(request_models.kind = 13,pe_bank_data.clabe,IF(request_models.kind = 14,g_banks.clabe,"")) as provider_clabe,
							IF(request_models.kind = 13,pe_bank_data.currency,IF(request_models.kind = 14,g_banks.currency,"")) as provider_currency,
							IF(request_models.kind = 13,pe_bank_data.agreement,IF(request_models.kind = 14,g_banks.agreement,"")) as provider_agreement,
							IF(request_models.taxPayment = 1,"Fiscal","No Fiscal") as tax_payment,
							IF(request_models.kind = 13,pe_d.quantity,IF(request_models.kind = 14,g_d.quantity,"")) as d_quantity,
							IF(request_models.kind = 13,pe_d.unit,IF(request_models.kind = 14,g_d.unit,"")) as d_unit,
							IF(request_models.kind = 13,pe_d.description,IF(request_models.kind = 14,g_d.description,"")) as d_description,
							"" as d_account,
							IF(request_models.kind = 13,pe_d.unitPrice,IF(request_models.kind = 14,g_d.unitPrice,"")) as d_unit_price,
							IF(request_models.kind = 13,pe_d.subtotal,IF(request_models.kind = 14,g_d.subtotal,"")) as d_subtotal,
							IF(request_models.kind = 13,pe_d.tax,IF(request_models.kind = 14,g_d.tax,"")) as d_tax,
							IFNULL(IF(request_models.kind = 13,pe_taxes.taxes_amount,IF(request_models.kind = 14,g_taxes.taxes_amount,0)),0) as d_aditional_taxes,
							IFNULL(IF(request_models.kind = 13,pe_retention.retention_amount,IF(request_models.kind = 14,g_retention.retention_amount,0)),0) as d_aditional_retention,
							IF(request_models.kind = 13,pe_d.amount,IF(request_models.kind = 14,g_d.amount,"")) as d_amount,
							IF(request_models.kind = 13,dpe_labels.labels,IF(request_models.kind = 14,dg_labels.labels,"")) as labels,
							"" as request_amount,
							"" as diff_against_request,
							"" as refund,
							"" as repay,
							IF(request_models.kind = 12,loan_enterprises.amount,IF(request_models.kind = 13,purchase_enterprises.amount,IF(request_models.kind = 14,groups.amount,""))) as total_pay,
							IF(request_models.kind = 12,loan_enterprises.currency,IF(request_models.kind = 13,purchase_enterprises.typeCurrency,IF(request_models.kind = 14,groups.typeCurrency,""))) as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
							)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
								->orWhereNull('request_models.idDepartment');
						})
						->where(function($permissionProject)
						{
							$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
								->orWhereNull('request_models.idProject');
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
								->orWhereNull('request_models.idEnterprise');
						})
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != "")
							{
								$query->whereRaw('IF(request_models.kind = 12,CONCAT(loan_enterprises.title," - ",loan_enterprises.datetitle),IF(request_models.kind = 13,CONCAT(purchase_enterprises.title," - ",purchase_enterprises.datetitle),IF(request_models.kind = 14,CONCAT(groups.title," - ",groups.datetitle),""))) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "" && $kind != "todas")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[11,12,13,14]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[11,12,13,14]); //,15,16,17
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterprise',$enterprise);
							}
							if ($project != "")
							{                               
								$query->whereIn('request_models.idProject',$project);
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
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->orderBy('request_models.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
						->leftJoin('users as request_user','idRequest','request_user.id')
						->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
						->leftJoin('users as review_user','idCheck','review_user.id')
						->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						->leftJoin('loan_enterprises',function($q)
						{
							$q->on('request_models.folio','=','loan_enterprises.idFolio')
							->on('request_models.kind','=','loan_enterprises.idKind');
						})
						->leftJoin('enterprises as le_origin_ent','loan_enterprises.idEnterpriseOrigin','le_origin_ent.id')
						->leftJoin('enterprises as le_origin_ent_r','loan_enterprises.idEnterpriseOriginR','le_origin_ent_r.id')
						->leftJoin('accounts as le_origin_acc','loan_enterprises.idAccAccOrigin','le_origin_acc.idAccAcc')
						->leftJoin('accounts as le_origin_acc_r','loan_enterprises.idAccAccOriginR','le_origin_acc_r.idAccAcc')
						->leftJoin('enterprises as le_destiny_ent','loan_enterprises.idEnterpriseDestiny','le_destiny_ent.id')
						->leftJoin('enterprises as le_destiny_ent_r','loan_enterprises.idEnterpriseDestinyR','le_destiny_ent_r.id')
						->leftJoin('accounts as le_destiny_acc','loan_enterprises.idAccAccDestiny','le_destiny_acc.idAccAcc')
						->leftJoin('accounts as le_destiny_acc_r','loan_enterprises.idAccAccDestinyR','le_destiny_acc_r.idAccAcc')
						->leftJoin('payment_methods as le_method','loan_enterprises.idpaymentMethod','le_method.idpaymentMethod')
						->leftJoin('purchase_enterprises',function($q)
						{
							$q->on('request_models.folio','=','purchase_enterprises.idFolio')
							->on('request_models.kind','=','purchase_enterprises.idKind');
						})
						->leftJoin('enterprises as pe_origin_ent','purchase_enterprises.idEnterpriseOrigin','pe_origin_ent.id')
						->leftJoin('enterprises as pe_origin_ent_r','purchase_enterprises.idEnterpriseOriginR','pe_origin_ent_r.id')
						->leftJoin('accounts as pe_origin_acc','purchase_enterprises.idAccAccOrigin','pe_origin_acc.idAccAcc')
						->leftJoin('accounts as pe_origin_acc_r','purchase_enterprises.idAccAccOriginR','pe_origin_acc_r.idAccAcc')
						->leftJoin('enterprises as pe_destiny_ent','purchase_enterprises.idEnterpriseDestiny','pe_destiny_ent.id')
						->leftJoin('enterprises as pe_destiny_ent_r','purchase_enterprises.idEnterpriseDestinyR','pe_destiny_ent_r.id')
						->leftJoin('accounts as pe_destiny_acc','purchase_enterprises.idAccAccDestiny','pe_destiny_acc.idAccAcc')
						->leftJoin('accounts as pe_destiny_acc_r','purchase_enterprises.idAccAccDestinyR','pe_destiny_acc_r.idAccAcc')
						->leftJoin('payment_methods as pe_method','purchase_enterprises.idpaymentMethod','pe_method.idpaymentMethod')
						->leftJoin('banks_accounts as pe_bank_data','purchase_enterprises.idbanksAccounts','pe_bank_data.idbanksAccounts')
						->leftJoin('banks as pe_bank','pe_bank_data.idBanks','pe_bank.idBanks')
						->leftJoin('purchase_enterprise_details as pe_d','purchase_enterprises.idpurchaseEnterprise','pe_d.idpurchaseEnterprise')
						->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'pe_d.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'pe_d.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_enterprise_detail_labels INNER JOIN labels ON purchase_enterprise_detail_labels.idlabels = labels.idlabels GROUP BY idPurchaseEnterpriseDetail) AS dpe_labels'),'pe_d.idPurchaseEnterpriseDetail','dpe_labels.idPurchaseEnterpriseDetail')
						->leftJoin('groups',function($q)
						{
							$q->on('request_models.folio','=','groups.idFolio')
							->on('request_models.kind','=','groups.idKind');
						})
						->leftJoin('enterprises as g_origin_ent','groups.idEnterpriseOrigin','g_origin_ent.id')
						->leftJoin('enterprises as g_origin_ent_r','groups.idEnterpriseOriginR','g_origin_ent_r.id')
						->leftJoin('accounts as g_origin_acc','groups.idAccAccOrigin','g_origin_acc.idAccAcc')
						->leftJoin('accounts as g_origin_acc_r','groups.idAccAccOriginR','g_origin_acc_r.idAccAcc')
						->leftJoin('enterprises as g_destiny_ent','groups.idEnterpriseDestiny','g_destiny_ent.id')
						->leftJoin('enterprises as g_destiny_ent_r','groups.idEnterpriseDestinyR','g_destiny_ent_r.id')
						->leftJoin('accounts as g_destiny_acc','groups.idAccAccDestiny','g_destiny_acc.idAccAcc')
						->leftJoin('accounts as g_destiny_acc_r','groups.idAccAccDestinyR','g_destiny_acc_r.idAccAcc')
						->leftJoin('providers as g_provider','groups.idProvider','g_provider.idProvider')
						->leftJoin('payment_methods as g_method','groups.idpaymentMethod','g_method.idpaymentMethod')
						->leftJoin('provider_banks as g_banks','groups.provider_has_banks_id','g_banks.id')
						->leftJoin('banks as g_bank_data','g_banks.banks_idBanks','g_bank_data.idBanks')
						->leftJoin('groups_details as g_d','groups.idgroups','g_d.idgroups')
						->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS g_taxes'),'g_d.idgroupsDetail','g_taxes.idgroupsDetail')
						->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS g_retention'),'g_d.idgroupsDetail','g_retention.idgroupsDetail')
						->leftJoin(DB::raw('(SELECT idgroupsDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM groups_detail_labels INNER JOIN labels ON groups_detail_labels.idlabels = labels.idlabels GROUP BY idgroupsDetail) AS dg_labels'),'g_d.idgroupsDetail','dg_labels.idgroupsDetail')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                     = null;
							$row->idRequisition             = null;
							$row->status                    = '';
							$row->kind                      = '';
							$row->title                     = '';
							$row->order_number              = '';
							$row->estimate_number           = '';
							$row->request_user              = '';
							$row->elaborate_user            = '';
							$row->elaborate_date            = '';
							$row->request_enterprise        = '';
							$row->request_direction         = '';
							$row->request_department        = '';
							$row->request_project           = '';
							$row->request_account           = '';
							$row->review_user               = '';
							$row->review_date               = '';
							$row->review_enterprise         = '';
							$row->review_direction          = '';
							$row->review_department         = '';
							$row->review_project            = '';
							$row->review_account            = '';
							$row->authorize_user            = '';
							$row->authorize_date            = '';
							$row->origin_enterprise         = '';
							$row->origin_account            = '';
							$row->destination_enterprise    = '';
							$row->destination_account       = '';
							$row->amount                    = '';
							$row->business_name             = '';
							$row->reference                 = '';
							$row->payment_method            = '';
							$row->provider_bank             = '';
							$row->provider_account          = '';
							$row->provider_branch           = '';
							$row->provider_reference        = '';
							$row->provider_clabe            = '';
							$row->provider_currency         = '';
							$row->provider_agreement        = '';
							$row->tax_payment               = '';
							$row->total_pay                 = null;
							$row->currency                  = '';
							$row->exchange_rate             = '';
							$row->exchange_rate_description = '';
							$row->paid_amount               = null;
							$row->review_checkComment       = '';
							$row->authorize_Comment         = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							request_models.idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							IF(request_models.kind = 8,IF(checkup.folio IS NULL,"NO","SÍ"),"") AS checkup,
							IF(request_models.kind = 3,expenses.resourceId,"") AS resource_fol,
							IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) as title,
							IF(request_models.kind = 1,purchases.numberOrder,"") as order_number,
							IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs) as wbs,
							IF(request_models.idRequisition IS NOT NULL,CONCAT_WS(" ",edt_req.code,edt_req.description),CONCAT_WS(" ",edt.code,edt.description)) as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as elaborate_date,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_enterprise.name, IF(request_models.kind = 2,"Varias","")) as request_enterprise,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_direction.name, IF(request_models.kind = 2,"Varias","")) as request_direction,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_department.name, IF(request_models.kind = 2,"Varios","")) as request_department,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_project.proyectName, IF(request_models.kind = 2,"Varios","")) as request_project,
							IF(request_models.kind = 1, CONCAT(request_account.account,"(",request_account.description,")"), IF(request_models.kind = 2 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9, "Varias","")) as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL, "No Aplica", DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i")) as review_date,
							IF(request_models.kind != 2,review_enterprise.name,"No hay") as review_enterprise,
							IF(request_models.kind != 2,review_direction.name,"No hay") as review_direction,
							IF(request_models.kind != 2,review_department.name,"No hay") as review_department,
							IF(request_models.kind != 2,review_project.proyectName,"No hay") as review_project,
							IF(request_models.kind != 2,IF(request_models.kind = 8 OR request_models.kind = 9,IF(request_models.idEnterpriseR IS NULL,"","Varias"),CONCAT(review_account.account,"(",review_account.description,")")),"No hay") as review_account,
							IF(request_models.kind != 2,request_models.checkComment,"No hay") as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							IF(request_models.kind != 2,request_models.authorizeComment,"No hay") as authorize_Comment,
							"No Aplica" as origin_enterprise,
							"No Aplica" as origin_account,
							"No Aplica" as destination_enterprise,
							"No Aplica" as destination_account,
							IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as amount,
							IF(request_models.kind = 1,purchase_provider.businessName,"") as business_name,
							IF(request_models.kind = 1,purchases.reference,IF(request_models.kind = 3,expenses.reference,IF(request_models.kind = 9,refunds.reference,""))) as reference,
							IF(request_models.kind = 1,purchases.paymentMode,IF(request_models.kind = 3,expenses_method.method,IF(request_models.kind = 8,resource_method.method,IF(request_models.kind = 9,refund_method.method,"")))) as payment_method,
							IF(request_models.kind = 1,purchase_provider_bank_data.description,IF(request_models.kind = 3,expenses_employee_bank.description,IF(request_models.kind = 8,IF(resources.idpaymentMethod IS NULL,"Sin método de pago",resource_employee_bank.description),IF(request_models.kind = 9,refund_employee_bank.description,"")))) as provider_bank,
							IF(request_models.kind = 1,purchase_provider_bank.account,IF(request_models.kind = 3,expenses_employee.account,IF(request_models.kind = 8,resource_employee.account,IF(request_models.kind = 9,refund_employee.account,"")))) as provider_account,
							IF(request_models.kind = 3,expenses_employee.cardNumber,IF(request_models.kind = 8,resource_employee.cardNumber,IF(request_models.kind = 9,refund_employee.cardNumber,""))) as provider_card,
							IF(request_models.kind = 1,purchase_provider_bank.branch,"") as provider_branch,
							IF(request_models.kind = 1,purchase_provider_bank.reference,"") as provider_reference,
							IF(request_models.kind = 1,purchase_provider_bank.clabe,IF(request_models.kind = 3,expenses_employee.clabe,IF(request_models.kind = 8,resource_employee.clabe,IF(request_models.kind = 9,refund_employee.clabe,"")))) as provider_clabe,
							IF(request_models.kind = 1,purchase_provider_bank.currency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as provider_currency,
							IF(request_models.kind = 1,purchase_provider_bank.agreement,"") as provider_agreement,
							IF(request_models.kind = 2 OR request_models.kind = 8,"",IF(request_models.kind = 3,IF(expenses_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.kind = 9,IF(refund_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.taxPayment = 1,"Fiscal","No Fiscal")))) as tax_payment,
							IF(request_models.kind = 1,detail_purchases.quantity,"") as d_quantity,
							IF(request_models.kind = 1,detail_purchases.unit,"") as d_unit,
							IF(request_models.kind = 1,detail_purchases.description,IF(request_models.kind = 3,expenses_details.concept,IF(request_models.kind = 8,resource_details.concept,IF(request_models.kind = 9,refund_details.concept,"")))) as d_description,
							IF(request_models.kind = 3,IF(expenses_details.idAccountR IS NULL,CONCAT(ed_acc.account," ",ed_acc.description," (",ed_acc.content,")"),CONCAT(ed_acc_r.account," ",ed_acc_r.description," (",ed_acc_r.content,")")),IF(request_models.kind = 8,IF(resource_details.idAccAccR IS NULL,CONCAT(rd_acc.account," ",rd_acc.description," (",rd_acc.content,")"),CONCAT(rd_acc_r.account," ",rd_acc_r.description," (",rd_acc_r.content,")")),IF(request_models.kind = 9,IF(refund_details.idAccountR IS NULL,CONCAT(red_acc.account," ",red_acc.description," (",red_acc.content,")"),CONCAT(red_acc_r.account," ",red_acc_r.description," (",red_acc_r.content,")")),""))) as d_account,
							IF(request_models.kind = 1,detail_purchases.unitPrice,"") as d_unit_price,
							IF(request_models.kind = 1,detail_purchases.subtotal,IF(request_models.kind = 3,expenses_details.amount,IF(request_models.kind = 9,refund_details.amount,""))) as d_subtotal,
							IF(request_models.kind = 1,detail_purchases.tax,IF(request_models.kind = 3,expenses_details.tax,IF(request_models.kind = 9,refund_details.tax,""))) as d_tax,
							IFNULL(IF(request_models.kind = 1,taxes_purchase.taxes_amount,IF(request_models.kind = 3,taxes_expenses.taxes_amount,IF(request_models.kind = 9,taxes_refund.taxes_amount,""))),0) as d_aditional_taxes,
							IFNULL(IF(request_models.kind = 1,retention_purchase.retention_amount,0),0) as d_aditional_retention,
							IF(request_models.kind = 1,detail_purchases.amount,IF(request_models.kind = 3,expenses_details.sAmount,IF(request_models.kind = 8,resource_details.amount,IF(request_models.kind = 9,refund_details.sAmount,"")))) as d_amount,
							IF(request_models.kind = 1,dp_labels.labels,IF(request_models.kind = 3,de_labels.labels,IF(request_models.kind = 8,req_labels.labels,IF(request_models.kind = 9,dre_labels.labels,"")))) as labels,
							IF(request_models.kind = 3,expenses_resource.total,IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total,2)),"")) as request_amount,
							IF(request_models.kind = 3,ROUND(expenses.total - expenses_resource.total,2),IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total - resources.total,2)),"")) as diff_against_request,
							IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reembolso > 0,"Pagado",IF(request_models.payment = 0 AND expenses.reembolso > 0,"No Pagado","No Aplica")),"") as refund,
							IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 1,"Comprobado",IF(request_models.payment = 0 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado",IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado","No Aplica"))),"") as repay,
							IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as total_pay,
							IF(request_models.kind = 1,purchases.typeCurrency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
								->orWhereNull('request_models.idDepartment');
						})
						->where(function($permissionProject)
						{
							$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
								->orWhereNull('request_models.idProject');
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
								->orWhereNull('request_models.idEnterprise');
						})
						->whereNull('adjustment_folios.idFolio')
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != "")
							{
								$query->whereRaw('IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[1,2,3,8,9]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[1,2,3,8,9]); //11,12,13,14,15,16,17
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterprise',$enterprise);
							}
							if ($project != "")
							{                               
								$query->whereIn('request_models.idProject',$project);
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
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "")
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->whereRaw('IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,"")) IS NULL')
						->orderBy('request_models.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
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
						->leftJoin('purchases',function($q)
						{
							$q->on('request_models.folio','=','purchases.idFolio')
							->on('request_models.kind','=','purchases.idKind');
						})
						->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
						->leftJoin('provider_banks as purchase_provider_bank','purchases.provider_has_banks_id','purchase_provider_bank.id')
						->leftJoin('banks as purchase_provider_bank_data','purchase_provider_bank.banks_idBanks','purchase_provider_bank_data.idBanks')
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
						->leftJoin('nomina_applications as nomina',function($q)
						{
							$q->on('request_models.folio','=','nomina.idFolio')
							->on('request_models.kind','=','nomina.idKind');
						})
						->leftJoin('expenses',function($q)
						{
							$q->on('request_models.folio','=','expenses.idFolio')
							->on('request_models.kind','=','expenses.idKind');
						})
						->leftJoin('payment_methods as expenses_method','expenses.idpaymentMethod','expenses_method.idpaymentMethod')
						->leftJoin('employees as expenses_employee','expenses.idEmployee','expenses_employee.idEmployee')
						->leftJoin('banks as expenses_employee_bank','expenses_employee.idBanks','expenses_employee_bank.idBanks')
						->leftJoin('expenses_details','expenses.idExpenses','expenses_details.idExpenses')
						->leftJoin('accounts as ed_acc_r','expenses_details.idAccountR','ed_acc_r.idAccAcc')
						->leftJoin('accounts as ed_acc','expenses_details.idAccount','ed_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as taxes_amount FROM taxes_expenses GROUP BY idExpensesDetail) AS taxes_expenses'),'expenses_details.idExpensesDetail','taxes_expenses.idExpensesDetail')
						->leftJoin(DB::raw('(SELECT idExpensesDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_expenses INNER JOIN labels ON label_detail_expenses.idlabels = labels.idlabels GROUP BY idExpensesDetail) AS de_labels'),'expenses_details.idExpensesDetail','de_labels.idExpensesDetail')
						->leftJoin('resources as expenses_resource','expenses.resourceId','expenses_resource.idFolio')
						->leftJoin(DB::raw('(SELECT expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total) AS checkup'),'request_models.folio','checkup.folio')
						->leftJoin('resources',function($q)
						{
							$q->on('request_models.folio','=','resources.idFolio')
							->on('request_models.kind','=','resources.idKind');
						})
						->leftJoin('payment_methods as resource_method','resources.idpaymentMethod','resource_method.idpaymentMethod')
						->leftJoin('employees as resource_employee','resources.idEmployee','resource_employee.idEmployee')
						->leftJoin('banks as resource_employee_bank','resource_employee.idBanks','resource_employee_bank.idBanks')
						->leftJoin('resource_details','resources.idresource','resource_details.idresource')
						->leftJoin('accounts as rd_acc_r','resource_details.idAccAccR','rd_acc_r.idAccAcc')
						->leftJoin('accounts as rd_acc','resource_details.idAccAcc','rd_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT request_folio as folio, request_kind as kind, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM request_has_labels INNER JOIN labels ON request_has_labels.labels_idlabels = labels.idlabels GROUP BY request_folio, request_kind) AS req_labels'),function($q)
						{
							$q->on('request_models.folio','=','req_labels.folio')
							->on('request_models.kind','=','req_labels.kind');
						})
						->leftJoin('refunds',function($q)
						{
							$q->on('request_models.folio','=','refunds.idFolio')
							->on('request_models.kind','=','refunds.idKind');
						})
						->leftJoin('payment_methods as refund_method','refunds.idpaymentMethod','refund_method.idpaymentMethod')
						->leftJoin('employees as refund_employee','refunds.idEmployee','refund_employee.idEmployee')
						->leftJoin('banks as refund_employee_bank','refund_employee.idBanks','refund_employee_bank.idBanks')
						->leftJoin('refund_details','refunds.idRefund','refund_details.idRefund')
						->leftJoin('accounts as red_acc_r','refund_details.idAccountR','red_acc_r.idAccAcc')
						->leftJoin('accounts as red_acc','refund_details.idAccount','red_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as taxes_amount FROM taxes_refunds GROUP BY idRefundDetail) AS taxes_refund'),'refund_details.idRefundDetail','taxes_refund.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_refunds INNER JOIN labels ON label_detail_refunds.idlabels = labels.idlabels GROUP BY idRefundDetail) AS dre_labels'),'refund_details.idRefundDetail','dre_labels.idRefundDetail')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
						->leftJoin('request_models as req','request_models.idRequisition','req.folio')
						->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
						->leftJoin('cat_code_e_d_ts as edt_req','req.code_edt','edt_req.id')
						->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                     = null;
							$row->idRequisition             = null;
							$row->status                    = '';
							$row->kind                      = '';
							$row->checkup                   = '';
							$row->resource_fol              = '';
							$row->title                     = '';
							$row->order_number              = '';
							$row->wbs                       = '';
							$row->edt                       = '';
							$row->estimate_number           = '';
							$row->request_user              = '';
							$row->elaborate_user            = '';
							$row->elaborate_date            = '';
							$row->request_enterprise        = '';
							$row->request_direction         = '';
							$row->request_department        = '';
							$row->request_project           = '';
							$row->request_account           = '';
							$row->review_user               = '';
							$row->review_date               = '';
							$row->review_enterprise         = '';
							$row->review_direction          = '';
							$row->review_department         = '';
							$row->review_project            = '';
							$row->review_account            = '';
							$row->authorize_user            = '';
							$row->authorize_date            = '';
							$row->origin_enterprise         = '';
							$row->origin_account            = '';
							$row->destination_enterprise    = '';
							$row->destination_account       = '';
							$row->amount                    = '';
							$row->business_name             = '';
							$row->reference                 = '';
							$row->payment_method            = '';
							$row->provider_bank             = '';
							$row->provider_account          = '';
							$row->provider_card             = '';
							$row->provider_branch           = '';
							$row->provider_reference        = '';
							$row->provider_clabe            = '';
							$row->provider_currency         = '';
							$row->provider_agreement        = '';
							$row->tax_payment               = '';
							$row->labels                    = '';
							$row->request_amount            = '';
							$row->diff_against_request      = '';
							$row->refund                    = '';
							$row->repay                     = '';
							$row->total_pay                 = null;
							$row->currency                  = '';
							$row->exchange_rate             = '';
							$row->exchange_rate_description = '';
							$row->paid_amount               = null;
							$row->review_checkComment       = '';
							$row->authorize_Comment         = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							request_models.idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							"" AS checkup,
							"" AS resource_folio,
							IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) as title,
							IF(request_models.kind = 17,purchase_records.numberOrder,"") as order_number,
							wbs.code_wbs as wbs,
							CONCAT_WS(" ",edt.code,edt.description) as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_enterprise.name,"")) as request_enterprise,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_direction.name,"")) as request_direction,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_department.name,"")) as request_department,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_project.proyectName,"")) as request_project,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,CONCAT(request_account.account," ",request_account.description," (",request_account.content,")"),"")) as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_enterprise.name,"No hay")) as review_enterprise,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_direction.name,"No hay")) as review_direction,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_department.name,"No hay")) as review_department,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17, review_project.proyectName, "No hay")) as review_project,
							IF(request_models.kind = 15, "Varias", IF(request_models.kind = 17, CONCAT(review_account.account," ",review_account.description," (",review_account.content,")"), "No hay")) as review_account,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.checkComment,"No hay")) as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.authorizeComment,"No hay")) as authorize_Comment,
							IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseOriginR IS NULL,me_origin_ent.name,me_origin_ent_r.name),"No Aplica") as origin_enterprise,
							IF(request_models.kind = 15,IF(movements_enterprises.idAccAccOriginR IS NULL,CONCAT(me_origin_acc.account," ",me_origin_acc.description," (",me_origin_acc.content,")"),CONCAT(me_origin_acc_r.account," ",me_origin_acc_r.description," (",me_origin_acc_r.content,")")),"No Aplica") as origin_account,
							IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseDestinyR IS NULL,me_destiny_ent.name,me_destiny_ent_r.name),"No Aplica") as destination_enterprise,
							IF(request_models.kind = 15,IF(movements_enterprises.idAccAccDestinyR IS NULL,CONCAT(me_destiny_acc.account," ",me_destiny_acc.description," (",me_destiny_acc.content,")"),CONCAT(me_destiny_acc_r.account," ",me_destiny_acc_r.description," (",me_destiny_acc_r.content,")")),"No Aplica") as destination_account,
							IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as amount,
							IF(request_models.kind = 17,purchase_records.provider,"") as business_name,
							IF(request_models.kind = 17,purchase_records.reference,"") as reference,
							IF(request_models.kind = 15,me_method.method,IF(request_models.kind = 17,purchase_records.paymentMethod,"")) as payment_method,
							"" as provider_bank,
							"" as provider_account,
							"" as provider_card,
							"" as provider_branch,
							"" as provider_reference,
							"" as provider_clabe,
							"" as provider_currency,
							"" as provider_agreement,
							IF(request_models.taxPayment = 1,"Fiscal","No Fiscal") as tax_payment,
							IF(request_models.kind = 17,pr_d.quantity,"") as d_quantity,
							IF(request_models.kind = 17,pr_d.unit,"") as d_unit,
							IF(request_models.kind = 17,pr_d.description,"") as d_description,
							"" as d_account,
							IF(request_models.kind = 17,pr_d.unitPrice,"") as d_unit_price,
							IF(request_models.kind = 17,pr_d.subtotal,"") as d_subtotal,
							IF(request_models.kind = 17,pr_d.tax,"") as d_tax,
							IFNULL(IF(request_models.kind = 17,pr_taxes.taxes_amount,0),0) as d_aditional_taxes,
							IFNULL(IF(request_models.kind = 17,pr_retention.retention_amount,0),0) as d_aditional_retention,
							IF(request_models.kind = 17,pr_d.total,"") as d_amount,
							IF(request_models.kind = 17,pr_d_labels.labels,"") as labels,
							"" as request_amount,
							"" as diff_against_request,
							"" as refund,
							"" as repay,
							IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as total_pay,
							IF(request_models.kind = 15,movements_enterprises.typeCurrency,IF(request_models.kind = 17,purchase_records.typeCurrency,"")) as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
								->orWhereNull('request_models.idDepartment');
						})
						->where(function($permissionProject)
						{
							$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
								->orWhereNull('request_models.idProject');
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
								->orWhereNull('request_models.idEnterprise');
						})
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != '')
							{
								$query->whereRaw('IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) LIKE "'.$title_search.'"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[15,17]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[15,17]);
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterprise',$enterprise);
							}
							if ($project != "")
							{                               
								$query->whereIn('request_models.idProject',$project);
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
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
							if($wbs != "")
							{
								$query->where(function($q) use ($wbs)
								{
									$q->whereIn('request_models.code_wbs',$wbs);
								});
							}
						})
						->whereRaw('wbs.code_wbs IS NULL')
						->orderBy('request_models.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
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
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						->leftJoin('movements_enterprises',function($q)
						{
							$q->on('request_models.folio','=','movements_enterprises.idFolio')
							->on('request_models.kind','=','movements_enterprises.idKind');
						})
						->leftJoin('enterprises as me_origin_ent','movements_enterprises.idEnterpriseOrigin','me_origin_ent.id')
						->leftJoin('enterprises as me_origin_ent_r','movements_enterprises.idEnterpriseOriginR','me_origin_ent_r.id')
						->leftJoin('accounts as me_origin_acc','movements_enterprises.idAccAccOrigin','me_origin_acc.idAccAcc')
						->leftJoin('accounts as me_origin_acc_r','movements_enterprises.idAccAccOriginR','me_origin_acc_r.idAccAcc')
						->leftJoin('enterprises as me_destiny_ent','movements_enterprises.idEnterpriseDestiny','me_destiny_ent.id')
						->leftJoin('enterprises as me_destiny_ent_r','movements_enterprises.idEnterpriseDestinyR','me_destiny_ent_r.id')
						->leftJoin('accounts as me_destiny_acc','movements_enterprises.idAccAccDestiny','me_destiny_acc.idAccAcc')
						->leftJoin('accounts as me_destiny_acc_r','movements_enterprises.idAccAccDestinyR','me_destiny_acc_r.idAccAcc')
						->leftJoin('payment_methods as me_method','movements_enterprises.idpaymentMethod','me_method.idpaymentMethod')
						->leftJoin('purchase_records',function($q)
						{
							$q->on('request_models.folio','=','purchase_records.idFolio')
							->on('request_models.kind','=','purchase_records.idKind');
						})
						->leftJoin('purchase_record_details as pr_d','purchase_records.id','pr_d.idPurchaseRecord')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS pr_taxes'),'pr_d.id','pr_taxes.idPurchaseRecordDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS pr_retention'),'pr_d.id','pr_retention.idPurchaseRecordDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS pr_d_labels'),'pr_d.id','pr_d_labels.idPurchaseRecordDetail')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                     = null;
							$row->idRequisition             = null;
							$row->status                    = '';
							$row->kind                      = '';
							$row->title                     = '';
							$row->order_number              = '';
							$row->estimate_number           = '';
							$row->request_user              = '';
							$row->elaborate_user            = '';
							$row->elaborate_date            = '';
							$row->request_enterprise        = '';
							$row->request_direction         = '';
							$row->request_department        = '';
							$row->request_project           = '';
							$row->request_account           = '';
							$row->review_user               = '';
							$row->review_date               = '';
							$row->review_enterprise         = '';
							$row->review_direction          = '';
							$row->review_department         = '';
							$row->review_project            = '';
							$row->review_account            = '';
							$row->authorize_user            = '';
							$row->authorize_date            = '';
							$row->origin_enterprise         = '';
							$row->origin_account            = '';
							$row->destination_enterprise    = '';
							$row->destination_account       = '';
							$row->amount                    = '';
							$row->business_name             = '';
							$row->reference                 = '';
							$row->payment_method            = '';
							$row->tax_payment               = '';
							$row->total_pay                 = null;
							$row->currency                  = '';
							$row->exchange_rate             = '';
							$row->exchange_rate_description = '';
							$row->paid_amount               = null;
							$row->review_checkComment       = '';
							$row->authorize_Comment         = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							"" as idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							"" AS checkup,
							"" AS resource_folio,
							CONCAT(nominas.title," - ",nominas.datetitle) as title,
							"" as order_number,
							wd_wbs.wbs as wbs,
							"" as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
							enterprises.name as request_enterprise,
							areas.name as request_direction,
							departments.name as request_department,
							projects.proyectName as request_project,
							CONCAT(accounts.account," ",accounts.description," (",accounts.content,")") as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
							"" as review_enterprise,
							"" as review_direction,
							"" as review_department,
							"" as review_project,
							"" as review_account,
							request_models.checkComment as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							request_models.authorizeComment as authorize_Comment,
							"No Aplica" as origin_enterprise,
							"No Aplica" as origin_account,
							"No Aplica" as destination_enterprise,
							"No Aplica" as destination_account,
							nominas.amount as amount,
							CONCAT_WS(" ",real_employees.last_name,real_employees.scnd_last_name,real_employees.name) as business_name,
							"" as reference,
							payment_methods.method as payment_method,
							"" as provider_bank,
							"" as provider_account,
							"" as provider_card,
							"" as provider_branch,
							"" as provider_reference,
							"" as provider_clabe,
							"" as provider_currency,
							"" as provider_agreement,
							IF(nominas.type_nomina = 1,"Fiscal","No Fiscal") as tax_payment,
							"1" as d_quantity,
							"" as d_unit,
							cat_type_payrolls.description as d_description,
							"" as d_account,
							IF(
								nominas.type_nomina != 1,
								(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalPerceptions,
													0
												)
											)
										)
									)
								)
							)
							as d_unit_price,
							IF(
								nominas.type_nomina != 1,
								(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalPerceptions,
													0
												)
											)
										)
									)
								)
							) as d_subtotal,
							"" as d_tax,
							"" as d_aditional_taxes,
							IF(
								nominas.type_nomina != 1,
								IFNULL(discounts.amount,0),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalRetentions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalTaxes,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalRetentions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalTaxes,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalRetentions,
													0
												)
											)
										)
									)
								)
							) as d_aditional_retention,
							IF(
								nominas.type_nomina != 1,
								nomina_employee_n_fs.amount,
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.netIncome,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.netIncome,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.netIncome,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.netIncome,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.netIncome,
													0
												)
											)
										)
									)
								)
							) as d_amount,
							"" as labels,
							"" as request_amount,
							"" as diff_against_request,
							"" as refund,
							"" as repay,
							IF(
								nominas.type_nomina != 1,
								nomina_employee_n_fs.amount,
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.netIncome,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.netIncome,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.netIncome,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.netIncome,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.netIncome,
													0
												)
											)
										)
									)
								)
							) as total_pay,
							"MXN" as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'));
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
						})
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != '')
							{
								$query->whereRaw('CONCAT(nominas.title," - ",nominas.datetitle) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "" && $kind != "todas")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[16]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[16]);
							}
							if ($enterprise != "")
							{
								$query->whereIn('worker_datas.enterprise',$enterprise);
							}
							if ($project != "")
							{
								$query->whereIn('worker_datas.project',$project);
							}
							if ($direction != "")
							{
								$query->whereIn('worker_datas.direction',$direction);
							}
							if ($department != "")
							{
								$query->whereIn('worker_datas.department',$department);
							}
							if($name != "")
							{
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->whereRaw('wd_wbs.wbs IS NULL')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
						->leftJoin('users as request_user','idRequest','request_user.id')
						->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
						->leftJoin('users as review_user','idCheck','review_user.id')
						->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
						->leftJoin('nominas',function($q)
						{
							$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
						})
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
						->leftJoin('projects','worker_datas.project','projects.idproyect')
						->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
						->leftJoin('accounts','worker_datas.account','accounts.idAccAcc')
						->leftJoin('areas','worker_datas.direction','areas.id')
						->leftJoin('departments','worker_datas.department','departments.id')
						->leftJoin('payment_methods','worker_datas.paymentWay','payment_methods.idpaymentMethod')
						->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
						->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','cat_type_payrolls.id')
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, idnominaEmployee, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idnominaEmployee, idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind')
								->on('nomina_employees.idnominaEmployee','=','p.idnominaEmployee');
							}
						)
						->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM extras_nominas GROUP BY idnominaemployeenf) as extras'),'nomina_employee_n_fs.idnominaemployeenf','extras.idnominaemployeenf')
						->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM discounts_nominas GROUP BY idnominaemployeenf) as discounts'),'nomina_employee_n_fs.idnominaemployeenf','discounts.idnominaemployeenf')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                  = null;
							$row->idRequisition          = '';
							$row->status                 = '';
							$row->kind                   = '';
							$row->title                  = '';
							$row->order_number           = '';
							$row->estimate_number        = '';
							$row->request_user           = '';
							$row->elaborate_user         = '';
							$row->elaborate_date         = '';
							$row->review_user            = '';
							$row->review_date            = '';
							$row->review_enterprise      = '';
							$row->review_direction       = '';
							$row->review_department      = '';
							$row->review_project         = '';
							$row->review_account         = '';
							$row->authorize_user         = '';
							$row->authorize_date         = '';
							$row->origin_enterprise      = '';
							$row->origin_account         = '';
							$row->destination_enterprise = '';
							$row->destination_account    = '';
							$row->reference              = '';
							$row->amount                 = '';
							$row->tax_payment            = '';
							$row->review_checkComment    = '';
							$row->authorize_Comment      = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
				}
				else
				{
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							request_models.idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							IF(request_models.kind = 8,IF(checkup.folio IS NULL,"NO","SÍ"),"") AS checkup,
							IF(request_models.kind = 3,expenses.resourceId,"") AS resource_fol,
							IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) as title,
							IF(request_models.kind = 1,purchases.numberOrder,"") as order_number,
							IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs) as wbs,
							IF(request_models.idRequisition IS NOT NULL,CONCAT_WS(" ",edt_req.code,edt_req.description),CONCAT_WS(" ",edt.code,edt.description)) as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as elaborate_date,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_enterprise.name, IF(request_models.kind = 2,"Varias","")) as request_enterprise,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_direction.name, IF(request_models.kind = 2,"Varias","")) as request_direction,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_department.name, IF(request_models.kind = 2,"Varios","")) as request_department,
							IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_project.proyectName, IF(request_models.kind = 2,"Varios","")) as request_project,
							IF(request_models.kind = 1, CONCAT(request_account.account,"(",request_account.description,")"), IF(request_models.kind = 2 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9, "Varias","")) as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL, "No Aplica", DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i")) as review_date,
							IF(request_models.kind != 2,review_enterprise.name,"No hay") as review_enterprise,
							IF(request_models.kind != 2,review_direction.name,"No hay") as review_direction,
							IF(request_models.kind != 2,review_department.name,"No hay") as review_department,
							IF(request_models.kind != 2,review_project.proyectName,"No hay") as review_project,
							IF(request_models.kind != 2,IF(request_models.kind = 8 OR request_models.kind = 9,IF(request_models.idEnterpriseR IS NULL,"","Varias"),CONCAT(review_account.account,"(",review_account.description,")")),"No hay") as review_account,
							IF(request_models.kind != 2,request_models.checkComment,"No hay") as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							IF(request_models.kind != 2,request_models.authorizeComment,"No hay") as authorize_Comment,
							"No Aplica" as origin_enterprise,
							"No Aplica" as origin_account,
							"No Aplica" as destination_enterprise,
							"No Aplica" as destination_account,
							IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as amount,
							IF(request_models.kind = 1,purchase_provider.businessName,"") as business_name,
							IF(request_models.kind = 1,purchases.reference,IF(request_models.kind = 3,expenses.reference,IF(request_models.kind = 9,refunds.reference,""))) as reference,
							IF(request_models.kind = 1,purchases.paymentMode,IF(request_models.kind = 3,expenses_method.method,IF(request_models.kind = 8,resource_method.method,IF(request_models.kind = 9,refund_method.method,"")))) as payment_method,
							IF(request_models.kind = 1,purchase_provider_bank_data.description,IF(request_models.kind = 3,expenses_employee_bank.description,IF(request_models.kind = 8,IF(resources.idpaymentMethod IS NULL,"Sin método de pago",resource_employee_bank.description),IF(request_models.kind = 9,refund_employee_bank.description,"")))) as provider_bank,
							IF(request_models.kind = 1,purchase_provider_bank.account,IF(request_models.kind = 3,expenses_employee.account,IF(request_models.kind = 8,resource_employee.account,IF(request_models.kind = 9,refund_employee.account,"")))) as provider_account,
							IF(request_models.kind = 3,expenses_employee.cardNumber,IF(request_models.kind = 8,resource_employee.cardNumber,IF(request_models.kind = 9,refund_employee.cardNumber,""))) as provider_card,
							IF(request_models.kind = 1,purchase_provider_bank.branch,"") as provider_branch,
							IF(request_models.kind = 1,purchase_provider_bank.reference,"") as provider_reference,
							IF(request_models.kind = 1,purchase_provider_bank.clabe,IF(request_models.kind = 3,expenses_employee.clabe,IF(request_models.kind = 8,resource_employee.clabe,IF(request_models.kind = 9,refund_employee.clabe,"")))) as provider_clabe,
							IF(request_models.kind = 1,purchase_provider_bank.currency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as provider_currency,
							IF(request_models.kind = 1,purchase_provider_bank.agreement,"") as provider_agreement,
							IF(request_models.kind = 2 OR request_models.kind = 8,"",IF(request_models.kind = 3,IF(expenses_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.kind = 9,IF(refund_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.taxPayment = 1,"Fiscal","No Fiscal")))) as tax_payment,
							IF(request_models.kind = 1,detail_purchases.quantity,"") as d_quantity,
							IF(request_models.kind = 1,detail_purchases.unit,"") as d_unit,
							IF(request_models.kind = 1,detail_purchases.description,IF(request_models.kind = 3,expenses_details.concept,IF(request_models.kind = 8,resource_details.concept,IF(request_models.kind = 9,refund_details.concept,"")))) as d_description,
							IF(request_models.kind = 3,IF(expenses_details.idAccountR IS NULL,CONCAT(ed_acc.account," ",ed_acc.description," (",ed_acc.content,")"),CONCAT(ed_acc_r.account," ",ed_acc_r.description," (",ed_acc_r.content,")")),IF(request_models.kind = 8,IF(resource_details.idAccAccR IS NULL,CONCAT(rd_acc.account," ",rd_acc.description," (",rd_acc.content,")"),CONCAT(rd_acc_r.account," ",rd_acc_r.description," (",rd_acc_r.content,")")),IF(request_models.kind = 9,IF(refund_details.idAccountR IS NULL,CONCAT(red_acc.account," ",red_acc.description," (",red_acc.content,")"),CONCAT(red_acc_r.account," ",red_acc_r.description," (",red_acc_r.content,")")),""))) as d_account,
							IF(request_models.kind = 1,detail_purchases.unitPrice,"") as d_unit_price,
							IF(request_models.kind = 1,detail_purchases.subtotal,IF(request_models.kind = 3,expenses_details.amount,IF(request_models.kind = 9,refund_details.amount,""))) as d_subtotal,
							IF(request_models.kind = 1,detail_purchases.tax,IF(request_models.kind = 3,expenses_details.tax,IF(request_models.kind = 9,refund_details.tax,""))) as d_tax,
							IFNULL(IF(request_models.kind = 1,taxes_purchase.taxes_amount,IF(request_models.kind = 3,taxes_expenses.taxes_amount,IF(request_models.kind = 9,taxes_refund.taxes_amount,""))),0) as d_aditional_taxes,
							IFNULL(IF(request_models.kind = 1,retention_purchase.retention_amount,0),0) as d_aditional_retention,
							IF(request_models.kind = 1,detail_purchases.amount,IF(request_models.kind = 3,expenses_details.sAmount,IF(request_models.kind = 8,resource_details.amount,IF(request_models.kind = 9,refund_details.sAmount,"")))) as d_amount,
							IF(request_models.kind = 1,dp_labels.labels,IF(request_models.kind = 3,de_labels.labels,IF(request_models.kind = 8,req_labels.labels,IF(request_models.kind = 9,dre_labels.labels,"")))) as labels,
							IF(request_models.kind = 3,expenses_resource.total,IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total,2)),"")) as request_amount,
							IF(request_models.kind = 3,ROUND(expenses.total - expenses_resource.total,2),IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total - resources.total,2)),"")) as diff_against_request,
							IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reembolso > 0,"Pagado",IF(request_models.payment = 0 AND expenses.reembolso > 0,"No Pagado","No Aplica")),"") as refund,
							IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 1,"Comprobado",IF(request_models.payment = 0 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado",IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado","No Aplica"))),"") as repay,
							IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as total_pay,
							IF(request_models.kind = 1,purchases.typeCurrency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
								->orWhereNull('request_models.idDepartment');
						})
						->where(function($permissionProject)
						{
							$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
								->orWhereNull('request_models.idProject');
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
								->orWhereNull('request_models.idEnterprise');
						})
						->whereNull('adjustment_folios.idFolio')
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != '')
							{
								$query->whereRaw('IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[1,2,3,8,9]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[1,2,3,8,9]); //11,12,13,14,15,16,17
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterprise',$enterprise);
							}
							if ($project != "")
							{                               
								$query->whereIn('request_models.idProject',$project);
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
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "")
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->whereRaw('IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,IFNULL(wbs.code_wbs,"")) LIKE "'.$wbs_selected->wbs.'"')
						->orderBy('request_models.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
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
						->leftJoin('purchases',function($q)
						{
							$q->on('request_models.folio','=','purchases.idFolio')
							->on('request_models.kind','=','purchases.idKind');
						})
						->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
						->leftJoin('provider_banks as purchase_provider_bank','purchases.provider_has_banks_id','purchase_provider_bank.id')
						->leftJoin('banks as purchase_provider_bank_data','purchase_provider_bank.banks_idBanks','purchase_provider_bank_data.idBanks')
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
						->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
						->leftJoin('nomina_applications as nomina',function($q)
						{
							$q->on('request_models.folio','=','nomina.idFolio')
							->on('request_models.kind','=','nomina.idKind');
						})
						->leftJoin('expenses',function($q)
						{
							$q->on('request_models.folio','=','expenses.idFolio')
							->on('request_models.kind','=','expenses.idKind');
						})
						->leftJoin('payment_methods as expenses_method','expenses.idpaymentMethod','expenses_method.idpaymentMethod')
						->leftJoin('employees as expenses_employee','expenses.idEmployee','expenses_employee.idEmployee')
						->leftJoin('banks as expenses_employee_bank','expenses_employee.idBanks','expenses_employee_bank.idBanks')
						->leftJoin('expenses_details','expenses.idExpenses','expenses_details.idExpenses')
						->leftJoin('accounts as ed_acc_r','expenses_details.idAccountR','ed_acc_r.idAccAcc')
						->leftJoin('accounts as ed_acc','expenses_details.idAccount','ed_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as taxes_amount FROM taxes_expenses GROUP BY idExpensesDetail) AS taxes_expenses'),'expenses_details.idExpensesDetail','taxes_expenses.idExpensesDetail')
						->leftJoin(DB::raw('(SELECT idExpensesDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_expenses INNER JOIN labels ON label_detail_expenses.idlabels = labels.idlabels GROUP BY idExpensesDetail) AS de_labels'),'expenses_details.idExpensesDetail','de_labels.idExpensesDetail')
						->leftJoin('resources as expenses_resource','expenses.resourceId','expenses_resource.idFolio')
						->leftJoin(DB::raw('(SELECT expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total) AS checkup'),'request_models.folio','checkup.folio')
						->leftJoin('resources',function($q)
						{
							$q->on('request_models.folio','=','resources.idFolio')
							->on('request_models.kind','=','resources.idKind');
						})
						->leftJoin('payment_methods as resource_method','resources.idpaymentMethod','resource_method.idpaymentMethod')
						->leftJoin('employees as resource_employee','resources.idEmployee','resource_employee.idEmployee')
						->leftJoin('banks as resource_employee_bank','resource_employee.idBanks','resource_employee_bank.idBanks')
						->leftJoin('resource_details','resources.idresource','resource_details.idresource')
						->leftJoin('accounts as rd_acc_r','resource_details.idAccAccR','rd_acc_r.idAccAcc')
						->leftJoin('accounts as rd_acc','resource_details.idAccAcc','rd_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT request_folio as folio, request_kind as kind, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM request_has_labels INNER JOIN labels ON request_has_labels.labels_idlabels = labels.idlabels GROUP BY request_folio, request_kind) AS req_labels'),function($q)
						{
							$q->on('request_models.folio','=','req_labels.folio')
							->on('request_models.kind','=','req_labels.kind');
						})
						->leftJoin('refunds',function($q)
						{
							$q->on('request_models.folio','=','refunds.idFolio')
							->on('request_models.kind','=','refunds.idKind');
						})
						->leftJoin('payment_methods as refund_method','refunds.idpaymentMethod','refund_method.idpaymentMethod')
						->leftJoin('employees as refund_employee','refunds.idEmployee','refund_employee.idEmployee')
						->leftJoin('banks as refund_employee_bank','refund_employee.idBanks','refund_employee_bank.idBanks')
						->leftJoin('refund_details','refunds.idRefund','refund_details.idRefund')
						->leftJoin('accounts as red_acc_r','refund_details.idAccountR','red_acc_r.idAccAcc')
						->leftJoin('accounts as red_acc','refund_details.idAccount','red_acc.idAccAcc')
						->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as taxes_amount FROM taxes_refunds GROUP BY idRefundDetail) AS taxes_refund'),'refund_details.idRefundDetail','taxes_refund.idRefundDetail')
						->leftJoin(DB::raw('(SELECT idRefundDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_refunds INNER JOIN labels ON label_detail_refunds.idlabels = labels.idlabels GROUP BY idRefundDetail) AS dre_labels'),'refund_details.idRefundDetail','dre_labels.idRefundDetail')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
						->leftJoin('request_models as req','request_models.idRequisition','req.folio')
						->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
						->leftJoin('cat_code_e_d_ts as edt_req','req.code_edt','edt_req.id')
						->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                     = null;
							$row->idRequisition             = null;
							$row->status                    = '';
							$row->kind                      = '';
							$row->checkup                   = '';
							$row->resource_fol              = '';
							$row->title                     = '';
							$row->order_number              = '';
							$row->wbs                       = '';
							$row->edt                       = '';
							$row->estimate_number           = '';
							$row->request_user              = '';
							$row->elaborate_user            = '';
							$row->elaborate_date            = '';
							$row->request_enterprise        = '';
							$row->request_direction         = '';
							$row->request_department        = '';
							$row->request_project           = '';
							$row->request_account           = '';
							$row->review_user               = '';
							$row->review_date               = '';
							$row->review_enterprise         = '';
							$row->review_direction          = '';
							$row->review_department         = '';
							$row->review_project            = '';
							$row->review_account            = '';
							$row->authorize_user            = '';
							$row->authorize_date            = '';
							$row->origin_enterprise         = '';
							$row->origin_account            = '';
							$row->destination_enterprise    = '';
							$row->destination_account       = '';
							$row->amount                    = '';
							$row->business_name             = '';
							$row->reference                 = '';
							$row->payment_method            = '';
							$row->provider_bank             = '';
							$row->provider_account          = '';
							$row->provider_card             = '';
							$row->provider_branch           = '';
							$row->provider_reference        = '';
							$row->provider_clabe            = '';
							$row->provider_currency         = '';
							$row->provider_agreement        = '';
							$row->tax_payment               = '';
							$row->labels                    = '';
							$row->request_amount            = '';
							$row->diff_against_request      = '';
							$row->refund                    = '';
							$row->repay                     = '';
							$row->total_pay                 = null;
							$row->currency                  = '';
							$row->exchange_rate             = '';
							$row->exchange_rate_description = '';
							$row->paid_amount               = null;
							$row->review_checkComment       = '';
							$row->authorize_Comment         = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							request_models.idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							"" AS checkup,
							"" AS resource_folio,
							IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) as title,
							IF(request_models.kind = 17,purchase_records.numberOrder,"") as order_number,
							wbs.code_wbs as wbs,
							CONCAT_WS(" ",edt.code,edt.description) as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_enterprise.name,"")) as request_enterprise,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_direction.name,"")) as request_direction,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_department.name,"")) as request_department,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_project.proyectName,"")) as request_project,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,CONCAT(request_account.account," ",request_account.description," (",request_account.content,")"),"")) as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_enterprise.name,"No hay")) as review_enterprise,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_direction.name,"No hay")) as review_direction,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_department.name,"No hay")) as review_department,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17, review_project.proyectName, "No hay")) as review_project,
							IF(request_models.kind = 15, "Varias", IF(request_models.kind = 17, CONCAT(review_account.account," ",review_account.description," (",review_account.content,")"), "No hay")) as review_account,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.checkComment,"No hay")) as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.authorizeComment,"No hay")) as authorize_Comment,
							IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseOriginR IS NULL,me_origin_ent.name,me_origin_ent_r.name),"No Aplica") as origin_enterprise,
							IF(request_models.kind = 15,IF(movements_enterprises.idAccAccOriginR IS NULL,CONCAT(me_origin_acc.account," ",me_origin_acc.description," (",me_origin_acc.content,")"),CONCAT(me_origin_acc_r.account," ",me_origin_acc_r.description," (",me_origin_acc_r.content,")")),"No Aplica") as origin_account,
							IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseDestinyR IS NULL,me_destiny_ent.name,me_destiny_ent_r.name),"No Aplica") as destination_enterprise,
							IF(request_models.kind = 15,IF(movements_enterprises.idAccAccDestinyR IS NULL,CONCAT(me_destiny_acc.account," ",me_destiny_acc.description," (",me_destiny_acc.content,")"),CONCAT(me_destiny_acc_r.account," ",me_destiny_acc_r.description," (",me_destiny_acc_r.content,")")),"No Aplica") as destination_account,
							IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as amount,
							IF(request_models.kind = 17,purchase_records.provider,"") as business_name,
							IF(request_models.kind = 17,purchase_records.reference,"") as reference,
							IF(request_models.kind = 15,me_method.method,IF(request_models.kind = 17,purchase_records.paymentMethod,"")) as payment_method,
							"" as provider_bank,
							"" as provider_account,
							"" as provider_card,
							"" as provider_branch,
							"" as provider_reference,
							"" as provider_clabe,
							"" as provider_currency,
							"" as provider_agreement,
							IF(request_models.taxPayment = 1,"Fiscal","No Fiscal") as tax_payment,
							IF(request_models.kind = 17,pr_d.quantity,"") as d_quantity,
							IF(request_models.kind = 17,pr_d.unit,"") as d_unit,
							IF(request_models.kind = 17,pr_d.description,"") as d_description,
							"" as d_account,
							IF(request_models.kind = 17,pr_d.unitPrice,"") as d_unit_price,
							IF(request_models.kind = 17,pr_d.subtotal,"") as d_subtotal,
							IF(request_models.kind = 17,pr_d.tax,"") as d_tax,
							IFNULL(IF(request_models.kind = 17,pr_taxes.taxes_amount,0),0) as d_aditional_taxes,
							IFNULL(IF(request_models.kind = 17,pr_retention.retention_amount,0),0) as d_aditional_retention,
							IF(request_models.kind = 17,pr_d.total,"") as d_amount,
							IF(request_models.kind = 17,pr_d_labels.labels,"") as labels,
							"" as request_amount,
							"" as diff_against_request,
							"" as refund,
							"" as repay,
							IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as total_pay,
							IF(request_models.kind = 15,movements_enterprises.typeCurrency,IF(request_models.kind = 17,purchase_records.typeCurrency,"")) as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
								->orWhereNull('request_models.idDepartment');
						})
						->where(function($permissionProject)
						{
							$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
								->orWhereNull('request_models.idProject');
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
								->orWhereNull('request_models.idEnterprise');
						})
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != '')
							{
								$query->whereRaw('IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[15,17]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[15,17]);
							}
							if ($enterprise != "")
							{                               
								$query->whereIn('request_models.idEnterprise',$enterprise);
							}
							if ($project != "")
							{                               
								$query->whereIn('request_models.idProject',$project);
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
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->whereRaw('IFNULL(wbs.code_wbs,"") LIKE "'.$wbs_selected->wbs.'"')
						->orderBy('request_models.kind','ASC')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
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
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind');
							}
						)
						->leftJoin('movements_enterprises',function($q)
						{
							$q->on('request_models.folio','=','movements_enterprises.idFolio')
							->on('request_models.kind','=','movements_enterprises.idKind');
						})
						->leftJoin('enterprises as me_origin_ent','movements_enterprises.idEnterpriseOrigin','me_origin_ent.id')
						->leftJoin('enterprises as me_origin_ent_r','movements_enterprises.idEnterpriseOriginR','me_origin_ent_r.id')
						->leftJoin('accounts as me_origin_acc','movements_enterprises.idAccAccOrigin','me_origin_acc.idAccAcc')
						->leftJoin('accounts as me_origin_acc_r','movements_enterprises.idAccAccOriginR','me_origin_acc_r.idAccAcc')
						->leftJoin('enterprises as me_destiny_ent','movements_enterprises.idEnterpriseDestiny','me_destiny_ent.id')
						->leftJoin('enterprises as me_destiny_ent_r','movements_enterprises.idEnterpriseDestinyR','me_destiny_ent_r.id')
						->leftJoin('accounts as me_destiny_acc','movements_enterprises.idAccAccDestiny','me_destiny_acc.idAccAcc')
						->leftJoin('accounts as me_destiny_acc_r','movements_enterprises.idAccAccDestinyR','me_destiny_acc_r.idAccAcc')
						->leftJoin('payment_methods as me_method','movements_enterprises.idpaymentMethod','me_method.idpaymentMethod')
						->leftJoin('purchase_records',function($q)
						{
							$q->on('request_models.folio','=','purchase_records.idFolio')
							->on('request_models.kind','=','purchase_records.idKind');
						})
						->leftJoin('purchase_record_details as pr_d','purchase_records.id','pr_d.idPurchaseRecord')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS pr_taxes'),'pr_d.id','pr_taxes.idPurchaseRecordDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS pr_retention'),'pr_d.id','pr_retention.idPurchaseRecordDetail')
						->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS pr_d_labels'),'pr_d.id','pr_d_labels.idPurchaseRecordDetail')
						->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
						->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
						->orderBy('request_models.folio')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                     = null;
							$row->idRequisition             = null;
							$row->status                    = '';
							$row->kind                      = '';
							$row->title                     = '';
							$row->order_number              = '';
							$row->estimate_number           = '';
							$row->request_user              = '';
							$row->elaborate_user            = '';
							$row->elaborate_date            = '';
							$row->request_enterprise        = '';
							$row->request_direction         = '';
							$row->request_department        = '';
							$row->request_project           = '';
							$row->request_account           = '';
							$row->review_user               = '';
							$row->review_date               = '';
							$row->review_enterprise         = '';
							$row->review_direction          = '';
							$row->review_department         = '';
							$row->review_project            = '';
							$row->review_account            = '';
							$row->authorize_user            = '';
							$row->authorize_date            = '';
							$row->origin_enterprise         = '';
							$row->origin_account            = '';
							$row->destination_enterprise    = '';
							$row->destination_account       = '';
							$row->amount                    = '';
							$row->business_name             = '';
							$row->reference                 = '';
							$row->payment_method            = '';
							$row->tax_payment               = '';
							$row->total_pay                 = null;
							$row->currency                  = '';
							$row->exchange_rate             = '';
							$row->exchange_rate_description = '';
							$row->paid_amount               = null;
							$row->review_checkComment       = '';
							$row->authorize_Comment         = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
					$requests  = DB::table('request_models')
						->selectRaw(
							'request_models.folio,
							"" as idRequisition,
							status_requests.description as status,
							request_kinds.kind as kind,
							"" AS checkup,
							"" AS resource_folio,
							CONCAT(nominas.title," - ",nominas.datetitle) as title,
							"" as order_number,
							wd_wbs.wbs as wbs,
							"" as edt,
							request_models.estimate_number as estimate_number,
							CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
							enterprises.name as request_enterprise,
							areas.name as request_direction,
							departments.name as request_department,
							projects.proyectName as request_project,
							CONCAT(accounts.account," ",accounts.description," (",accounts.content,")") as request_account,
							CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
							IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
							"" as review_enterprise,
							"" as review_direction,
							"" as review_department,
							"" as review_project,
							"" as review_account,
							request_models.checkComment as review_checkComment,
							CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
							IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
							request_models.authorizeComment as authorize_Comment,
							"No Aplica" as origin_enterprise,
							"No Aplica" as origin_account,
							"No Aplica" as destination_enterprise,
							"No Aplica" as destination_account,
							nominas.amount as amount,
							CONCAT_WS(" ",real_employees.last_name,real_employees.scnd_last_name,real_employees.name) as business_name,
							"" as reference,
							payment_methods.method as payment_method,
							"" as provider_bank,
							"" as provider_account,
							"" as provider_card,
							"" as provider_branch,
							"" as provider_reference,
							"" as provider_clabe,
							"" as provider_currency,
							"" as provider_agreement,
							IF(nominas.type_nomina = 1,"Fiscal","No Fiscal") as tax_payment,
							"1" as d_quantity,
							"" as d_unit,
							cat_type_payrolls.description as d_description,
							"" as d_account,
							IF(
								nominas.type_nomina != 1,
								(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalPerceptions,
													0
												)
											)
										)
									)
								)
							)
							as d_unit_price,
							IF(
								nominas.type_nomina != 1,
								(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalPerceptions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalPerceptions,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalPerceptions,
													0
												)
											)
										)
									)
								)
							) as d_subtotal,
							"" as d_tax,
							"" as d_aditional_taxes,
							IF(
								nominas.type_nomina != 1,
								IFNULL(discounts.amount,0),
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.totalRetentions,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.totalTaxes,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.totalRetentions,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.totalTaxes,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.totalRetentions,
													0
												)
											)
										)
									)
								)
							) as d_aditional_retention,
							IF(
								nominas.type_nomina != 1,
								nomina_employee_n_fs.amount,
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.netIncome,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.netIncome,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.netIncome,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.netIncome,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.netIncome,
													0
												)
											)
										)
									)
								)
							) as d_amount,
							"" as labels,
							"" as request_amount,
							"" as diff_against_request,
							"" as refund,
							"" as repay,
							IF(
								nominas.type_nomina != 1,
								nomina_employee_n_fs.amount,
								IF(
									nominas.idCatTypePayroll = "001",
									salaries.netIncome,
									IF(
										nominas.idCatTypePayroll = "002",
										bonuses.netIncome,
										IF(
											nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
											liquidations.netIncome,
											IF(
												nominas.idCatTypePayroll = "005",
												vacation_premia.netIncome,
												IF(
													nominas.idCatTypePayroll = "006",
													profit_sharings.netIncome,
													0
												)
											)
										)
									)
								)
							) as total_pay,
							"MXN" as currency,
							p.payment_exchange_rate as exchange_rate,
							p.payment_exchange_rate_description as exchange_rate_description,
							IFNULL(p.payment_amount,0) as paid_amount'
						)
						->where(function($permissionDep)
						{
							$permissionDep->whereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'));
						})
						->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
						})
						->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
						{
							if($title_search != '')
							{
								$query->whereRaw('CONCAT(nominas.title," - ",nominas.datetitle) LIKE "%'.$title_search.'%"');
							}
							if ($folio != "") 
							{
								$query->where('request_models.folio',$folio);
							}
							if($account != "")
							{
								$query->where('request_models.accountR',$account);
							}
							if ($kind != "" && $kind != "todas")
							{
								$tmpKind = array();
								foreach($kind as $k)
								{
									if(in_array($k,[16]))
									{
										$tmpKind[] = $k;
									}
								}
								$query->whereIn('request_models.kind',$tmpKind);
							}
							else
							{
								$query->whereIn('request_models.kind',[16]);
							}
							if ($enterprise != "")
							{
								$query->whereIn('worker_datas.enterprise',$enterprise);
							}
							if ($project != "")
							{
								$query->whereIn('worker_datas.project',$project);
							}
							if ($direction != "")
							{
								$query->whereIn('worker_datas.direction',$direction);
							}
							if ($department != "")
							{
								$query->whereIn('worker_datas.department',$department);
							}
							if($name != "")
							{
								$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
							}
							if ($mindate != '' && $maxdate != '') 
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
							if ($mindate_review != '' && $maxdate_review != '') 
							{
								$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
							}
							if ($mindate_authorize != '' && $maxdate_authorize != '') 
							{
								$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
							}
							if ($status != "") 
							{
								$query->whereIn('request_models.status',$status);
							}
							else
							{
								$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
							}
						})
						->whereRaw('wd_wbs.wbs LIKE "'.$wbs_selected->wbs.'"')
						->orderBy('request_models.folio','ASC')
						->join('status_requests','request_models.status','idrequestStatus')
						->join('request_kinds','request_models.kind','idrequestkind')
						->leftJoin('users as request_user','idRequest','request_user.id')
						->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
						->leftJoin('users as review_user','idCheck','review_user.id')
						->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
						->leftJoin('nominas',function($q)
						{
							$q->on('request_models.folio','=','nominas.idFolio')
							->on('request_models.kind','=','nominas.idKind');
						})
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
						->leftJoin('projects','worker_datas.project','projects.idproyect')
						->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
						->leftJoin('accounts','worker_datas.account','accounts.idAccAcc')
						->leftJoin('areas','worker_datas.direction','areas.id')
						->leftJoin('departments','worker_datas.department','departments.id')
						->leftJoin('payment_methods','worker_datas.paymentWay','payment_methods.idpaymentMethod')
						->leftJoin(DB::raw('(SELECT cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
						->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','cat_type_payrolls.id')
						->leftJoin(
							DB::raw('(SELECT idFolio, idKind, idnominaEmployee, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idnominaEmployee, idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
							{
								$q->on('request_models.folio','=','p.idFolio')
								->on('request_models.kind','=','p.idKind')
								->on('nomina_employees.idnominaEmployee','=','p.idnominaEmployee');
							}
						)
						->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM extras_nominas GROUP BY idnominaemployeenf) as extras'),'nomina_employee_n_fs.idnominaemployeenf','extras.idnominaemployeenf')
						->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM discounts_nominas GROUP BY idnominaemployeenf) as discounts'),'nomina_employee_n_fs.idnominaemployeenf','discounts.idnominaemployeenf')
						->get();
					foreach($requests as $rowKey => $row)
					{
						if($tmpFolio != $row->folio)
						{
							$tmpFolio = $row->folio;
							$kindRow  = !$kindRow;
						}
						else
						{
							$row->folio                  = null;
							$row->idRequisition          = '';
							$row->status                 = '';
							$row->kind                   = '';
							$row->title                  = '';
							$row->order_number           = '';
							$row->estimate_number        = '';
							$row->request_user           = '';
							$row->elaborate_user         = '';
							$row->elaborate_date         = '';
							$row->review_user            = '';
							$row->review_date            = '';
							$row->review_enterprise      = '';
							$row->review_direction       = '';
							$row->review_department      = '';
							$row->review_project         = '';
							$row->review_account         = '';
							$row->authorize_user         = '';
							$row->authorize_date         = '';
							$row->origin_enterprise      = '';
							$row->origin_account         = '';
							$row->destination_enterprise = '';
							$row->destination_account    = '';
							$row->reference              = '';
							$row->amount                 = '';
							$row->tax_payment            = '';
							$row->review_checkComment    = '';
							$row->authorize_Comment      = '';
						}
						$tmpArr = [];
						foreach($row as $k => $r)
						{
							if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
							elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
							$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
						}
						unset($tmpArr);
						$writer->addRow($rowFromValues);
						unset($rowFromValues);
						unset($requests[$rowKey]);
					}
				}
			}
			return $writer->close();
		}
		else
		{
			return abort(404);
		}
	}

	public function expensesRequestExcelWithoutGrouping(Request $request)
	{
		if (Auth::user()->module->where('id',128)->count()>0)
		{
			$enterprise			= $request->enterprise;
			$direction			= $request->direction;
			$department			= $request->department;
			$project			= $request->project;
			$account			= $request->account;
			$name				= $request->name;
			$kind				= $request->kind;
			$status				= $request->status;
			$folio				= $request->folio;
			$wbs				= $request->wbs;
			$title_search		= $request->title_search;
			$mindate			= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate			= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$mindate_review		= $request->mindate_review!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_review)->format('Y-m-d') : null;
			$maxdate_review		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_review)->format('Y-m-d') : null;
			$mindate_authorize	= $request->mindate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_authorize)->format('Y-m-d') : null;
			$maxdate_authorize	= $request->maxdate_authorize!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_authorize)->format('Y-m-d') : null;
			$noneBorder        = (new BorderBuilder())
				->setBorderTop(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderBottom(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft(Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$rowLight       = (new StyleBuilder())->setBackgroundColor('FFFFFF')->setBorder($noneBorder)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$mhStyleCol6    = (new StyleBuilder())->setBackgroundColor('548235')->setFontColor(Color::WHITE)->build();
			$mhStyleCol7    = (new StyleBuilder())->setBackgroundColor('EC8500')->setFontColor(Color::WHITE)->build();
			$mhStyleCol8    = (new StyleBuilder())->setBackgroundColor('D8407D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol9    = (new StyleBuilder())->setBackgroundColor('C00001')->setFontColor(Color::WHITE)->build();
			$mhStyleCol10   = (new StyleBuilder())->setBackgroundColor('BF8F01')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol6    = (new StyleBuilder())->setBackgroundColor('A9D08E')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol7    = (new StyleBuilder())->setBackgroundColor('F3B084')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol8    = (new StyleBuilder())->setBackgroundColor('E0B5C7')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol9    = (new StyleBuilder())->setBackgroundColor('C07971')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol10   = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Gastos.xlsx');
			$sheet = $writer->getCurrentSheet();
			$sheet->setName('Gastos');
			$mainHeaderArr = ['Datos de la solicitud','','','','','','','','','','','Datos del solicitante','','','','','','','','Datos de revisión','','','','','','','','Datos de autorización','','','Datos la solicitud','','','','','','','','','','','','','','','','','Conceptos','','','','','','','','','','Etiquetas','Montos relacionados con otras solicitudes','','','','Total','','Pagos realizados','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 10)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				elseif($k <= 18)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
				elseif($k <= 26)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
				}
				elseif($k <= 29)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
				elseif($k <= 46)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
				}
				elseif($k <= 56)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol6);
				}
				elseif($k <= 57)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol7);
				}
				elseif($k <= 61)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol8);
				}
				elseif($k <= 63)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol9);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol10);
				}
			}
			unset($mainHeaderArr);
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			unset($tmpMHArr);
			$writer->addRow($rowFromValues);
			unset($rowFromValues);
			$headerArr    = ['Folio','Folio de Requisición','Estado de solicitud','Tipo','Comprobación','Folio de la solicitud de recurso','Título','Número de orden','WBS','EDT','Número de estimación','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Comentarios','Autorizada por','Fecha de autorización','Comentarios','Empresa Origen','Clasificación de Gasto Origen','Empresa Destino','Clasificación de Gasto Destino','Monto Total','Razón Social','Referencia','Método de pago','Banco','Cuenta','Tarjeta','Sucursal','Referencia','CLABE','Moneda','Convenio','Fiscal/No Fiscal','Cantidad','Unidad','Concepto','Clasificación de gasto','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro','Total a pagar','Moneda','Tasa de Cambio','Descripción','Total Pagado'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 10)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				elseif($k <= 18)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
				}
				elseif($k <= 26)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
				}
				elseif($k <= 29)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
				elseif($k <= 46)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
				}
				elseif($k <= 56)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol6);
				}
				elseif($k <= 57)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol7);
				}
				elseif($k <= 61)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol8);
				}
				elseif($k <= 63)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol9);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol10);
				}
			}
			unset($headerArr);
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			unset($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			unset($rowFromValues);
			$requests = DB::table('request_models')
				->selectRaw(
					'request_models.folio,
					request_models.idRequisition,
					status_requests.description as status,
					request_kinds.kind as kind,
					IF(request_models.kind = 8,IF(checkup.folio IS NULL,"NO","SÍ"),"") AS checkup,
					IF(request_models.kind = 3,expenses.resourceId,"") AS resource_fol,
					IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) as title,
					IF(request_models.kind = 1,purchases.numberOrder,"") as order_number,
					IF(request_models.idRequisition IS NOT NULL,wbs_req.code_wbs,wbs.code_wbs) as wbs,
					IF(request_models.idRequisition IS NOT NULL,CONCAT_WS(" ",edt_req.code,edt_req.description),CONCAT_WS(" ",edt.code,edt.description)) as edt,
					request_models.estimate_number as estimate_number,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as elaborate_date,
					IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_enterprise.name, IF(request_models.kind = 2,"Varias","")) as request_enterprise,
					IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_direction.name, IF(request_models.kind = 2,"Varias","")) as request_direction,
					IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_department.name, IF(request_models.kind = 2,"Varios","")) as request_department,
					IF(request_models.kind = 1 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9,request_project.proyectName, IF(request_models.kind = 2,"Varios","")) as request_project,
					IF(request_models.kind = 1, CONCAT(request_account.account,"(",request_account.description,")"), IF(request_models.kind = 2 OR request_models.kind = 3 OR request_models.kind = 8 OR request_models.kind = 9, "Varias","")) as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					IF(request_models.reviewDate IS NULL, "No Aplica", DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i")) as review_date,
					IF(request_models.kind != 2,review_enterprise.name,"No hay") as review_enterprise,
					IF(request_models.kind != 2,review_direction.name,"No hay") as review_direction,
					IF(request_models.kind != 2,review_department.name,"No hay") as review_department,
					IF(request_models.kind != 2,review_project.proyectName,"No hay") as review_project,
					IF(request_models.kind != 2,IF(request_models.kind = 8 OR request_models.kind = 9,IF(request_models.idEnterpriseR IS NULL,"","Varias"),CONCAT(review_account.account,"(",review_account.description,")")),"No hay") as review_account,
					IF(request_models.kind != 2,request_models.checkComment,"No hay") as review_checkComment,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
					IF(request_models.kind != 2,request_models.authorizeComment,"No hay") as authorize_Comment,
					"No Aplica" as origin_enterprise,
					"No Aplica" as origin_account,
					"No Aplica" as destination_enterprise,
					"No Aplica" as destination_account,
					IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as amount,
					IF(request_models.kind = 1,purchase_provider.businessName,"") as business_name,
					IF(request_models.kind = 1,purchases.reference,IF(request_models.kind = 3,expenses.reference,IF(request_models.kind = 9,refunds.reference,""))) as reference,
					IF(request_models.kind = 1,purchases.paymentMode,IF(request_models.kind = 3,expenses_method.method,IF(request_models.kind = 8,resource_method.method,IF(request_models.kind = 9,refund_method.method,"")))) as payment_method,
					IF(request_models.kind = 1,purchase_provider_bank_data.description,IF(request_models.kind = 3,expenses_employee_bank.description,IF(request_models.kind = 8,IF(resources.idpaymentMethod IS NULL,"Sin método de pago",resource_employee_bank.description),IF(request_models.kind = 9,refund_employee_bank.description,"")))) as provider_bank,
					IF(request_models.kind = 1,purchase_provider_bank.account,IF(request_models.kind = 3,expenses_employee.account,IF(request_models.kind = 8,resource_employee.account,IF(request_models.kind = 9,refund_employee.account,"")))) as provider_account,
					IF(request_models.kind = 3,expenses_employee.cardNumber,IF(request_models.kind = 8,resource_employee.cardNumber,IF(request_models.kind = 9,refund_employee.cardNumber,""))) as provider_card,
					IF(request_models.kind = 1,purchase_provider_bank.branch,"") as provider_branch,
					IF(request_models.kind = 1,purchase_provider_bank.reference,"") as provider_reference,
					IF(request_models.kind = 1,purchase_provider_bank.clabe,IF(request_models.kind = 3,expenses_employee.clabe,IF(request_models.kind = 8,resource_employee.clabe,IF(request_models.kind = 9,refund_employee.clabe,"")))) as provider_clabe,
					IF(request_models.kind = 1,purchase_provider_bank.currency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as provider_currency,
					IF(request_models.kind = 1,purchase_provider_bank.agreement,"") as provider_agreement,
					IF(request_models.kind = 2 OR request_models.kind = 8,"",IF(request_models.kind = 3,IF(expenses_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.kind = 9,IF(refund_details.taxPayment = 1,"Fiscal","No Fiscal"),IF(request_models.taxPayment = 1,"Fiscal","No Fiscal")))) as tax_payment,
					IF(request_models.kind = 1,detail_purchases.quantity,"") as d_quantity,
					IF(request_models.kind = 1,detail_purchases.unit,"") as d_unit,
					IF(request_models.kind = 1,detail_purchases.description,IF(request_models.kind = 3,expenses_details.concept,IF(request_models.kind = 8,resource_details.concept,IF(request_models.kind = 9,refund_details.concept,"")))) as d_description,
					IF(request_models.kind = 3,IF(expenses_details.idAccountR IS NULL,CONCAT(ed_acc.account," ",ed_acc.description," (",ed_acc.content,")"),CONCAT(ed_acc_r.account," ",ed_acc_r.description," (",ed_acc_r.content,")")),IF(request_models.kind = 8,IF(resource_details.idAccAccR IS NULL,CONCAT(rd_acc.account," ",rd_acc.description," (",rd_acc.content,")"),CONCAT(rd_acc_r.account," ",rd_acc_r.description," (",rd_acc_r.content,")")),IF(request_models.kind = 9,IF(refund_details.idAccountR IS NULL,CONCAT(red_acc.account," ",red_acc.description," (",red_acc.content,")"),CONCAT(red_acc_r.account," ",red_acc_r.description," (",red_acc_r.content,")")),""))) as d_account,
					IF(request_models.kind = 1,detail_purchases.unitPrice,"") as d_unit_price,
					IF(request_models.kind = 1,detail_purchases.subtotal,IF(request_models.kind = 3,expenses_details.amount,IF(request_models.kind = 9,refund_details.amount,""))) as d_subtotal,
					IF(request_models.kind = 1,detail_purchases.tax,IF(request_models.kind = 3,expenses_details.tax,IF(request_models.kind = 9,refund_details.tax,""))) as d_tax,
					IFNULL(IF(request_models.kind = 1,taxes_purchase.taxes_amount,IF(request_models.kind = 3,taxes_expenses.taxes_amount,IF(request_models.kind = 9,taxes_refund.taxes_amount,""))),0) as d_aditional_taxes,
					IFNULL(IF(request_models.kind = 1,retention_purchase.retention_amount,0),0) as d_aditional_retention,
					IF(request_models.kind = 1,detail_purchases.amount,IF(request_models.kind = 3,expenses_details.sAmount,IF(request_models.kind = 8,resource_details.amount,IF(request_models.kind = 9,refund_details.sAmount,"")))) as d_amount,
					IF(request_models.kind = 1,dp_labels.labels,IF(request_models.kind = 3,de_labels.labels,IF(request_models.kind = 8,req_labels.labels,IF(request_models.kind = 9,dre_labels.labels,"")))) as labels,
					IF(request_models.kind = 3,expenses_resource.total,IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total,2)),"")) as request_amount,
					IF(request_models.kind = 3,ROUND(expenses.total - expenses_resource.total,2),IF(request_models.kind = 8,IF(checkup.folio IS NULL,"",ROUND(checkup.total - resources.total,2)),"")) as diff_against_request,
					IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reembolso > 0,"Pagado",IF(request_models.payment = 0 AND expenses.reembolso > 0,"No Pagado","No Aplica")),"") as refund,
					IF(request_models.kind = 3,IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 1,"Comprobado",IF(request_models.payment = 0 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado",IF(request_models.payment = 1 AND expenses.reintegro > 0 AND request_models.free = 0,"No Comprobado","No Aplica"))),"") as repay,
					IF(request_models.kind = 1,purchases.amount,IF(request_models.kind = 2,nomina.amount,IF(request_models.kind = 3,expenses.total,IF(request_models.kind = 8,resources.total,IF(request_models.kind = 9,refunds.total,""))))) as total_pay,
					IF(request_models.kind = 1,purchases.typeCurrency,IF(request_models.kind = 3,expenses.currency,IF(request_models.kind = 8,resources.currency,IF(request_models.kind = 9,refunds.currency,"")))) as currency,
					p.payment_exchange_rate as exchange_rate,
					p.payment_exchange_rate_description as exchange_rate_description,
					IFNULL(p.payment_amount,0) as paid_amount'
				)
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
						->orWhereNull('request_models.idDepartment');
				})
				->where(function($permissionProject)
				{
					$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
						->orWhereNull('request_models.idProject');
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
						->orWhereNull('request_models.idEnterprise');
				})
				->whereNull('adjustment_folios.idFolio')
				->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
				{
					if($title_search != '')
					{
						$query->whereRaw('IF(request_models.kind = 1,CONCAT(purchases.title," - ",purchases.datetitle),IF(request_models.kind = 2,CONCAT(nomina.title," - ",nomina.datetitle),IF(request_models.kind = 3,CONCAT(expenses.title," - ",expenses.datetitle),IF(request_models.kind = 8,CONCAT(resources.title," - ",resources.datetitle),IF(request_models.kind = 9,CONCAT(refunds.title," - ",refunds.datetitle),""))))) LIKE "%'.$title_search.'%"');
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if ($kind != "")
					{
						$tmpKind = array();
						foreach($kind as $k)
						{
							if(in_array($k,[1,2,3,8,9]))
							{
								$tmpKind[] = $k;
							}
						}
						$query->whereIn('request_models.kind',$tmpKind);
					}
					else
					{
						$query->whereIn('request_models.kind',[1,2,3,8,9]); //11,12,13,14,15,16,17
					}
					if ($enterprise != "")
					{                               
						$query->whereIn('request_models.idEnterprise',$enterprise);
					}
					if ($project != "")
					{                               
						$query->whereIn('request_models.idProject',$project);
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
						$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->where(function($q) use ($wbs)
						{
							$q->whereIn('request_models.code_wbs',$wbs)
								->orWhereIn('req.code_wbs', $wbs);
						});
					}
				})
				->orderBy('request_models.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->join('status_requests','request_models.status','idrequestStatus')
				->join('request_kinds','request_models.kind','idrequestkind')
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
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
					->on('request_models.kind','=','purchases.idKind');
				})
				->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
				->leftJoin('provider_banks as purchase_provider_bank','purchases.provider_has_banks_id','purchase_provider_bank.id')
				->leftJoin('banks as purchase_provider_bank_data','purchase_provider_bank.banks_idBanks','purchase_provider_bank_data.idBanks')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->leftJoin('nomina_applications as nomina',function($q)
				{
					$q->on('request_models.folio','=','nomina.idFolio')
					->on('request_models.kind','=','nomina.idKind');
				})
				->leftJoin('expenses',function($q)
				{
					$q->on('request_models.folio','=','expenses.idFolio')
					->on('request_models.kind','=','expenses.idKind');
				})
				->leftJoin('payment_methods as expenses_method','expenses.idpaymentMethod','expenses_method.idpaymentMethod')
				->leftJoin('employees as expenses_employee','expenses.idEmployee','expenses_employee.idEmployee')
				->leftJoin('banks as expenses_employee_bank','expenses_employee.idBanks','expenses_employee_bank.idBanks')
				->leftJoin('expenses_details','expenses.idExpenses','expenses_details.idExpenses')
				->leftJoin('accounts as ed_acc_r','expenses_details.idAccountR','ed_acc_r.idAccAcc')
				->leftJoin('accounts as ed_acc','expenses_details.idAccount','ed_acc.idAccAcc')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, SUM(amount) as taxes_amount FROM taxes_expenses GROUP BY idExpensesDetail) AS taxes_expenses'),'expenses_details.idExpensesDetail','taxes_expenses.idExpensesDetail')
				->leftJoin(DB::raw('(SELECT idExpensesDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_expenses INNER JOIN labels ON label_detail_expenses.idlabels = labels.idlabels GROUP BY idExpensesDetail) AS de_labels'),'expenses_details.idExpensesDetail','de_labels.idExpensesDetail')
				->leftJoin('resources as expenses_resource','expenses.resourceId','expenses_resource.idFolio')
				->leftJoin(DB::raw('(SELECT expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total) AS checkup'),'request_models.folio','checkup.folio')
				->leftJoin('resources',function($q)
				{
					$q->on('request_models.folio','=','resources.idFolio')
					->on('request_models.kind','=','resources.idKind');
				})
				->leftJoin('payment_methods as resource_method','resources.idpaymentMethod','resource_method.idpaymentMethod')
				->leftJoin('employees as resource_employee','resources.idEmployee','resource_employee.idEmployee')
				->leftJoin('banks as resource_employee_bank','resource_employee.idBanks','resource_employee_bank.idBanks')
				->leftJoin('resource_details','resources.idresource','resource_details.idresource')
				->leftJoin('accounts as rd_acc_r','resource_details.idAccAccR','rd_acc_r.idAccAcc')
				->leftJoin('accounts as rd_acc','resource_details.idAccAcc','rd_acc.idAccAcc')
				->leftJoin(DB::raw('(SELECT request_folio as folio, request_kind as kind, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM request_has_labels INNER JOIN labels ON request_has_labels.labels_idlabels = labels.idlabels GROUP BY request_folio, request_kind) AS req_labels'),function($q)
				{
					$q->on('request_models.folio','=','req_labels.folio')
					->on('request_models.kind','=','req_labels.kind');
				})
				->leftJoin('refunds',function($q)
				{
					$q->on('request_models.folio','=','refunds.idFolio')
					->on('request_models.kind','=','refunds.idKind');
				})
				->leftJoin('payment_methods as refund_method','refunds.idpaymentMethod','refund_method.idpaymentMethod')
				->leftJoin('employees as refund_employee','refunds.idEmployee','refund_employee.idEmployee')
				->leftJoin('banks as refund_employee_bank','refund_employee.idBanks','refund_employee_bank.idBanks')
				->leftJoin('refund_details','refunds.idRefund','refund_details.idRefund')
				->leftJoin('accounts as red_acc_r','refund_details.idAccountR','red_acc_r.idAccAcc')
				->leftJoin('accounts as red_acc','refund_details.idAccount','red_acc.idAccAcc')
				->leftJoin(DB::raw('(SELECT idRefundDetail, SUM(amount) as taxes_amount FROM taxes_refunds GROUP BY idRefundDetail) AS taxes_refund'),'refund_details.idRefundDetail','taxes_refund.idRefundDetail')
				->leftJoin(DB::raw('(SELECT idRefundDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_refunds INNER JOIN labels ON label_detail_refunds.idlabels = labels.idlabels GROUP BY idRefundDetail) AS dre_labels'),'refund_details.idRefundDetail','dre_labels.idRefundDetail')
				->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
				->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
				->leftJoin('request_models as req','request_models.idRequisition','req.folio')
				->leftJoin('cat_code_w_bs as wbs_req','req.code_wbs','wbs_req.id')
				->leftJoin('cat_code_e_d_ts as edt_req','req.code_edt','edt_req.id')
				->leftJoin('adjustment_folios','request_models.folio','adjustment_folios.idFolio')
				->get();
			$tmpFolio = '';
			$kindRow  = true;
			foreach($requests as $keyRow => $row)
			{
				if($tmpFolio != $row->folio)
				{
					$tmpFolio = $row->folio;
					$kindRow  = !$kindRow;
				}
				else
				{
					$row->folio                     = null;
					$row->idRequisition              = '';
					$row->status                    = '';
					$row->kind                      = '';
					$row->checkup                   = '';
					$row->resource_fol              = '';
					$row->title                     = '';
					$row->order_number              = '';
					$row->wbs                       = '';
					$row->edt                       = '';
					$row->estimate_number           = '';
					$row->request_user              = '';
					$row->elaborate_user            = '';
					$row->elaborate_date            = '';
					$row->request_enterprise        = '';
					$row->request_direction         = '';
					$row->request_department        = '';
					$row->request_project           = '';
					$row->request_account           = '';
					$row->review_user               = '';
					$row->review_date               = '';
					$row->review_enterprise         = '';
					$row->review_direction          = '';
					$row->review_department         = '';
					$row->review_project            = '';
					$row->review_account            = '';
					$row->authorize_user            = '';
					$row->authorize_date            = '';
					$row->origin_enterprise         = '';
					$row->origin_account            = '';
					$row->destination_enterprise    = '';
					$row->destination_account       = '';
					$row->amount                    = '';
					$row->business_name             = '';
					$row->reference                 = '';
					$row->payment_method            = '';
					$row->provider_bank             = '';
					$row->provider_account          = '';
					$row->provider_card             = '';
					$row->provider_branch           = '';
					$row->provider_reference        = '';
					$row->provider_clabe            = '';
					$row->provider_currency         = '';
					$row->provider_agreement        = '';
					$row->tax_payment               = '';
					$row->labels                    = '';
					$row->request_amount            = '';
					$row->diff_against_request      = '';
					$row->refund                    = '';
					$row->repay                     = '';
					$row->total_pay                 = null;
					$row->currency                  = '';
					$row->exchange_rate             = '';
					$row->exchange_rate_description = '';
					$row->paid_amount               = null;
					$row->review_checkComment       = '';
					$row->authorize_Comment         = '';
				}
				$tmpArr = [];
				foreach($row as $k => $r)
				{
					if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
					elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
				unset($requests[$keyRow]);
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
				}
				unset($tmpArr);
				$writer->addRow($rowFromValues);
				unset($rowFromValues);
			}
			if($wbs == "")
			{
				$requests = DB::table('request_models')
					->selectRaw(
						'request_models.folio,
						request_models.idRequisition,
						status_requests.description as status,
						request_kinds.kind as kind,
						"" AS checkup,
						"" AS resource_folio,
						IF(request_models.kind = 12,CONCAT(loan_enterprises.title," - ",loan_enterprises.datetitle),IF(request_models.kind = 13,CONCAT(purchase_enterprises.title," - ",purchase_enterprises.datetitle),IF(request_models.kind = 14,CONCAT(groups.title," - ",groups.datetitle),""))) as title,
						IF(request_models.kind = 13,purchase_enterprises.numberOrder,IF(request_models.kind = 14,groups.numberOrder,"")) as order_number,
						"" as wbs,
						"" as edt,
						request_models.estimate_number as estimate_number,
						CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
						CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
						DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_enterprise,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_direction,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_department,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_project,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","") as request_account,
						CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
						IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_enterprise,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_direction,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_department,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_project,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_account,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as review_checkComment,
						CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
						IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
						IF(request_models.kind = 12 OR request_models.kind = 13 OR request_models.kind = 14,"Varias","No hay") as authorize_Comment,
						IF(request_models.kind = 12,IF(loan_enterprises.idEnterpriseOriginR IS NULL,le_origin_ent.name,le_origin_ent_r.name),IF(request_models.kind = 13,IF(purchase_enterprises.idEnterpriseOriginR IS NULL,pe_origin_ent.name,pe_origin_ent_r.name),IF(request_models.kind = 14,IF(groups.idEnterpriseOriginR IS NULL,g_origin_ent.name,g_origin_ent_r.name),"No Aplica"))) as origin_enterprise,
						IF(request_models.kind = 12,IF(loan_enterprises.idAccAccOriginR IS NULL,CONCAT(le_origin_acc.account," ",le_origin_acc.description," (",le_origin_acc.content,")"),CONCAT(le_origin_acc_r.account," ",le_origin_acc_r.description," (",le_origin_acc_r.content,")")),IF(request_models.kind = 13,IF(purchase_enterprises.idAccAccOriginR IS NULL,CONCAT(pe_origin_acc.account," ",pe_origin_acc.description," (",pe_origin_acc.content,")"),CONCAT(pe_origin_acc_r.account," ",pe_origin_acc_r.description," (",pe_origin_acc_r.content,")")),IF(request_models.kind = 14,IF(groups.idAccAccOriginR IS NULL,CONCAT(g_origin_acc.account," ",g_origin_acc.description," (",g_origin_acc.content,")"),CONCAT(g_origin_acc_r.account," ",g_origin_acc_r.description," (",g_origin_acc_r.content,")")),"No Aplica"))) as origin_account,
						IF(request_models.kind = 12,IF(loan_enterprises.idEnterpriseDestinyR IS NULL,le_destiny_ent.name,le_destiny_ent_r.name),IF(request_models.kind = 13,IF(purchase_enterprises.idEnterpriseDestinyR IS NULL,pe_destiny_ent.name,pe_destiny_ent_r.name),IF(request_models.kind = 14,IF(groups.idEnterpriseDestinyR IS NULL,g_destiny_ent.name,g_destiny_ent_r.name),"No Aplica"))) as destination_enterprise,
						IF(request_models.kind = 12,IF(loan_enterprises.idAccAccDestinyR IS NULL,CONCAT(le_destiny_acc.account," ",le_destiny_acc.description," (",le_destiny_acc.content,")"),CONCAT(le_destiny_acc_r.account," ",le_destiny_acc_r.description," (",le_destiny_acc_r.content,")")),IF(request_models.kind = 13,IF(purchase_enterprises.idAccAccDestinyR IS NULL,CONCAT(pe_destiny_acc.account," ",pe_destiny_acc.description," (",pe_destiny_acc.content,")"),CONCAT(pe_destiny_acc_r.account," ",pe_destiny_acc_r.description," (",pe_destiny_acc_r.content,")")),IF(request_models.kind = 14,IF(groups.idAccAccDestinyR IS NULL,CONCAT(g_destiny_acc.account," ",g_destiny_acc.description," (",g_destiny_acc.content,")"),CONCAT(g_destiny_acc_r.account," ",g_destiny_acc_r.description," (",g_destiny_acc_r.content,")")),"No Aplica"))) as destination_account,
						IF(request_models.kind = 12,loan_enterprises.amount,IF(request_models.kind = 13,purchase_enterprises.amount,IF(request_models.kind = 14,IF(groups.operationType = "Salida",groups.amount,""),""))) as amount,
						IF(request_models.kind = 14,g_provider.businessName,"") as business_name,
						IF(request_models.kind = 13,purchase_enterprises.reference,IF(request_models.kind = 14,groups.reference,"")) as reference,
						IF(request_models.kind = 12,le_method.method,IF(request_models.kind = 13,pe_method.method,IF(request_models.kind = 14,g_method.method,""))) as payment_method,
						IF(request_models.kind = 13,pe_bank.description,IF(request_models.kind = 14,g_bank_data.description,"")) as provider_bank,
						IF(request_models.kind = 13,pe_bank_data.account,IF(request_models.kind = 14,g_banks.account,"")) as provider_account,
						"" as provider_card,
						IF(request_models.kind = 13,pe_bank_data.branch,IF(request_models.kind = 14,g_banks.branch,"")) as provider_branch,
						IF(request_models.kind = 13,pe_bank_data.reference,IF(request_models.kind = 14,g_banks.reference,"")) as provider_reference,
						IF(request_models.kind = 13,pe_bank_data.clabe,IF(request_models.kind = 14,g_banks.clabe,"")) as provider_clabe,
						IF(request_models.kind = 13,pe_bank_data.currency,IF(request_models.kind = 14,g_banks.currency,"")) as provider_currency,
						IF(request_models.kind = 13,pe_bank_data.agreement,IF(request_models.kind = 14,g_banks.agreement,"")) as provider_agreement,
						IF(request_models.taxPayment = 1,"Fiscal","No Fiscal") as tax_payment,
						IF(request_models.kind = 13,pe_d.quantity,IF(request_models.kind = 14,g_d.quantity,"")) as d_quantity,
						IF(request_models.kind = 13,pe_d.unit,IF(request_models.kind = 14,g_d.unit,"")) as d_unit,
						IF(request_models.kind = 13,pe_d.description,IF(request_models.kind = 14,g_d.description,"")) as d_description,
						"" as d_account,
						IF(request_models.kind = 13,pe_d.unitPrice,IF(request_models.kind = 14,g_d.unitPrice,"")) as d_unit_price,
						IF(request_models.kind = 13,pe_d.subtotal,IF(request_models.kind = 14,g_d.subtotal,"")) as d_subtotal,
						IF(request_models.kind = 13,pe_d.tax,IF(request_models.kind = 14,g_d.tax,"")) as d_tax,
						IFNULL(IF(request_models.kind = 13,pe_taxes.taxes_amount,IF(request_models.kind = 14,g_taxes.taxes_amount,0)),0) as d_aditional_taxes,
						IFNULL(IF(request_models.kind = 13,pe_retention.retention_amount,IF(request_models.kind = 14,g_retention.retention_amount,0)),0) as d_aditional_retention,
						IF(request_models.kind = 13,pe_d.amount,IF(request_models.kind = 14,g_d.amount,"")) as d_amount,
						IF(request_models.kind = 13,dpe_labels.labels,IF(request_models.kind = 14,dg_labels.labels,"")) as labels,
						"" as request_amount,
						"" as diff_against_request,
						"" as refund,
						"" as repay,
						IF(request_models.kind = 12,loan_enterprises.amount,IF(request_models.kind = 13,purchase_enterprises.amount,IF(request_models.kind = 14,groups.amount,""))) as total_pay,
						IF(request_models.kind = 12,loan_enterprises.currency,IF(request_models.kind = 13,purchase_enterprises.typeCurrency,IF(request_models.kind = 14,groups.typeCurrency,""))) as currency,
						p.payment_exchange_rate as exchange_rate,
						p.payment_exchange_rate_description as exchange_rate_description,
						IFNULL(p.payment_amount,0) as paid_amount'
						)
					->where(function($permissionDep)
					{
						$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
							->orWhereNull('request_models.idDepartment');
					})
					->where(function($permissionProject)
					{
						$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
							->orWhereNull('request_models.idProject');
					})
					->where(function($permissionEnt)
					{
						$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
							->orWhereNull('request_models.idEnterprise');
					})
					->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
					{
						if($title_search != '')
						{
							$query->whereRaw('IF(request_models.kind = 12,CONCAT(loan_enterprises.title," - ",loan_enterprises.datetitle),IF(request_models.kind = 13,CONCAT(purchase_enterprises.title," - ",purchase_enterprises.datetitle),IF(request_models.kind = 14,CONCAT(groups.title," - ",groups.datetitle),""))) LIKE "%'.$title_search.'%"');
						}
						if ($folio != "") 
						{
							$query->where('request_models.folio',$folio);
						}
						if($account != "")
						{
							$query->where('request_models.accountR',$account);
						}
						if ($kind != "")
						{
							$tmpKind = array();
							foreach($kind as $k)
							{
								if(in_array($k,[11,12,13,14]))
								{
									$tmpKind[] = $k;
								}
							}
							$query->whereIn('request_models.kind',$tmpKind);
						}
						else
						{
							$query->whereIn('request_models.kind',[11,12,13,14]); //,15,16,17
						}
						if ($enterprise != "")
						{                               
							$query->whereIn('request_models.idEnterprise',$enterprise);
						}
						if ($project != "")
						{                               
							$query->whereIn('request_models.idProject',$project);
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
							$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
						}
						if ($mindate != '' && $maxdate != '') 
						{
							$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						}
						if ($mindate_review != '' && $maxdate_review != '') 
						{
							$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
						}
						if ($mindate_authorize != '' && $maxdate_authorize != '') 
						{
							$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
						}
						if ($status != "") 
						{
							$query->whereIn('request_models.status',$status);
						}
						else
						{
							$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
						}
					})
					->orderBy('request_models.kind','ASC')
					->orderBy('request_models.folio','ASC')
					->join('status_requests','request_models.status','idrequestStatus')
					->join('request_kinds','request_models.kind','idrequestkind')
					->leftJoin('users as request_user','idRequest','request_user.id')
					->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
					->leftJoin('users as review_user','idCheck','review_user.id')
					->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
					->leftJoin(
						DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
						{
							$q->on('request_models.folio','=','p.idFolio')
							->on('request_models.kind','=','p.idKind');
						}
					)
					->leftJoin('loan_enterprises',function($q)
					{
						$q->on('request_models.folio','=','loan_enterprises.idFolio')
						->on('request_models.kind','=','loan_enterprises.idKind');
					})
					->leftJoin('enterprises as le_origin_ent','loan_enterprises.idEnterpriseOrigin','le_origin_ent.id')
					->leftJoin('enterprises as le_origin_ent_r','loan_enterprises.idEnterpriseOriginR','le_origin_ent_r.id')
					->leftJoin('accounts as le_origin_acc','loan_enterprises.idAccAccOrigin','le_origin_acc.idAccAcc')
					->leftJoin('accounts as le_origin_acc_r','loan_enterprises.idAccAccOriginR','le_origin_acc_r.idAccAcc')
					->leftJoin('enterprises as le_destiny_ent','loan_enterprises.idEnterpriseDestiny','le_destiny_ent.id')
					->leftJoin('enterprises as le_destiny_ent_r','loan_enterprises.idEnterpriseDestinyR','le_destiny_ent_r.id')
					->leftJoin('accounts as le_destiny_acc','loan_enterprises.idAccAccDestiny','le_destiny_acc.idAccAcc')
					->leftJoin('accounts as le_destiny_acc_r','loan_enterprises.idAccAccDestinyR','le_destiny_acc_r.idAccAcc')
					->leftJoin('payment_methods as le_method','loan_enterprises.idpaymentMethod','le_method.idpaymentMethod')
					->leftJoin('purchase_enterprises',function($q)
					{
						$q->on('request_models.folio','=','purchase_enterprises.idFolio')
						->on('request_models.kind','=','purchase_enterprises.idKind');
					})
					->leftJoin('enterprises as pe_origin_ent','purchase_enterprises.idEnterpriseOrigin','pe_origin_ent.id')
					->leftJoin('enterprises as pe_origin_ent_r','purchase_enterprises.idEnterpriseOriginR','pe_origin_ent_r.id')
					->leftJoin('accounts as pe_origin_acc','purchase_enterprises.idAccAccOrigin','pe_origin_acc.idAccAcc')
					->leftJoin('accounts as pe_origin_acc_r','purchase_enterprises.idAccAccOriginR','pe_origin_acc_r.idAccAcc')
					->leftJoin('enterprises as pe_destiny_ent','purchase_enterprises.idEnterpriseDestiny','pe_destiny_ent.id')
					->leftJoin('enterprises as pe_destiny_ent_r','purchase_enterprises.idEnterpriseDestinyR','pe_destiny_ent_r.id')
					->leftJoin('accounts as pe_destiny_acc','purchase_enterprises.idAccAccDestiny','pe_destiny_acc.idAccAcc')
					->leftJoin('accounts as pe_destiny_acc_r','purchase_enterprises.idAccAccDestinyR','pe_destiny_acc_r.idAccAcc')
					->leftJoin('payment_methods as pe_method','purchase_enterprises.idpaymentMethod','pe_method.idpaymentMethod')
					->leftJoin('banks_accounts as pe_bank_data','purchase_enterprises.idbanksAccounts','pe_bank_data.idbanksAccounts')
					->leftJoin('banks as pe_bank','pe_bank_data.idBanks','pe_bank.idBanks')
					->leftJoin('purchase_enterprise_details as pe_d','purchase_enterprises.idpurchaseEnterprise','pe_d.idpurchaseEnterprise')
					->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as taxes_amount FROM purchase_enterprise_taxes GROUP BY idPurchaseEnterpriseDetail) AS pe_taxes'),'pe_d.idPurchaseEnterpriseDetail','pe_taxes.idPurchaseEnterpriseDetail')
					->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, SUM(amount) as retention_amount FROM purchase_enterprise_retentions GROUP BY idPurchaseEnterpriseDetail) AS pe_retention'),'pe_d.idPurchaseEnterpriseDetail','pe_retention.idPurchaseEnterpriseDetail')
					->leftJoin(DB::raw('(SELECT idPurchaseEnterpriseDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_enterprise_detail_labels INNER JOIN labels ON purchase_enterprise_detail_labels.idlabels = labels.idlabels GROUP BY idPurchaseEnterpriseDetail) AS dpe_labels'),'pe_d.idPurchaseEnterpriseDetail','dpe_labels.idPurchaseEnterpriseDetail')
					->leftJoin('groups',function($q)
					{
						$q->on('request_models.folio','=','groups.idFolio')
						->on('request_models.kind','=','groups.idKind');
					})
					->leftJoin('enterprises as g_origin_ent','groups.idEnterpriseOrigin','g_origin_ent.id')
					->leftJoin('enterprises as g_origin_ent_r','groups.idEnterpriseOriginR','g_origin_ent_r.id')
					->leftJoin('accounts as g_origin_acc','groups.idAccAccOrigin','g_origin_acc.idAccAcc')
					->leftJoin('accounts as g_origin_acc_r','groups.idAccAccOriginR','g_origin_acc_r.idAccAcc')
					->leftJoin('enterprises as g_destiny_ent','groups.idEnterpriseDestiny','g_destiny_ent.id')
					->leftJoin('enterprises as g_destiny_ent_r','groups.idEnterpriseDestinyR','g_destiny_ent_r.id')
					->leftJoin('accounts as g_destiny_acc','groups.idAccAccDestiny','g_destiny_acc.idAccAcc')
					->leftJoin('accounts as g_destiny_acc_r','groups.idAccAccDestinyR','g_destiny_acc_r.idAccAcc')
					->leftJoin('providers as g_provider','groups.idProvider','g_provider.idProvider')
					->leftJoin('payment_methods as g_method','groups.idpaymentMethod','g_method.idpaymentMethod')
					->leftJoin('provider_banks as g_banks','groups.provider_has_banks_id','g_banks.id')
					->leftJoin('banks as g_bank_data','g_banks.banks_idBanks','g_bank_data.idBanks')
					->leftJoin('groups_details as g_d','groups.idgroups','g_d.idgroups')
					->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as taxes_amount FROM groups_taxes GROUP BY idgroupsDetail) AS g_taxes'),'g_d.idgroupsDetail','g_taxes.idgroupsDetail')
					->leftJoin(DB::raw('(SELECT idgroupsDetail, SUM(amount) as retention_amount FROM groups_retentions GROUP BY idgroupsDetail) AS g_retention'),'g_d.idgroupsDetail','g_retention.idgroupsDetail')
					->leftJoin(DB::raw('(SELECT idgroupsDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM groups_detail_labels INNER JOIN labels ON groups_detail_labels.idlabels = labels.idlabels GROUP BY idgroupsDetail) AS dg_labels'),'g_d.idgroupsDetail','dg_labels.idgroupsDetail')
					->get();
				foreach($requests as $keyRow => $row)
				{
					if($tmpFolio != $row->folio)
					{
						$tmpFolio = $row->folio;
						$kindRow  = !$kindRow;
					}
					else
					{
						$row->folio                     = null;
						$row->idRequisition             = '';
						$row->status                    = '';
						$row->kind                      = '';
						$row->title                     = '';
						$row->order_number              = '';
						$row->estimate_number           = '';
						$row->request_user              = '';
						$row->elaborate_user            = '';
						$row->elaborate_date            = '';
						$row->request_enterprise        = '';
						$row->request_direction         = '';
						$row->request_department        = '';
						$row->request_project           = '';
						$row->request_account           = '';
						$row->review_user               = '';
						$row->review_date               = '';
						$row->review_enterprise         = '';
						$row->review_direction          = '';
						$row->review_department         = '';
						$row->review_project            = '';
						$row->review_account            = '';
						$row->authorize_user            = '';
						$row->authorize_date            = '';
						$row->origin_enterprise         = '';
						$row->origin_account            = '';
						$row->destination_enterprise    = '';
						$row->destination_account       = '';
						$row->amount                    = '';
						$row->business_name             = '';
						$row->reference                 = '';
						$row->payment_method            = '';
						$row->provider_bank             = '';
						$row->provider_account          = '';
						$row->provider_branch           = '';
						$row->provider_reference        = '';
						$row->provider_clabe            = '';
						$row->provider_currency         = '';
						$row->provider_agreement        = '';
						$row->tax_payment               = '';
						$row->total_pay                 = null;
						$row->currency                  = '';
						$row->exchange_rate             = '';
						$row->exchange_rate_description = '';
						$row->paid_amount               = null;
						$row->review_checkComment       = '';
						$row->authorize_Comment         = '';
					}
					$tmpArr = [];
					foreach($row as $k => $r)
					{
						if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
						elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
					unset($requests[$keyRow]);
					if($kindRow)
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
					}
					else
					{
						$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
					}
					unset($tmpArr);
					$writer->addRow($rowFromValues);
					unset($rowFromValues);
				}
			}
			$requests = DB::table('request_models')
				->selectRaw(
					'request_models.folio,
					request_models.idRequisition,
					status_requests.description as status,
					request_kinds.kind as kind,
					"" AS checkup,
					"" AS resource_folio,
					IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) as title,
					IF(request_models.kind = 17,purchase_records.numberOrder,"") as order_number,
					wbs.code_wbs as wbs,
					CONCAT_WS(" ",edt.code,edt.description) as edt,
					request_models.estimate_number as estimate_number,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_enterprise.name,"")) as request_enterprise,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_direction.name,"")) as request_direction,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_department.name,"")) as request_department,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_project.proyectName,"")) as request_project,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,CONCAT(request_account.account," ",request_account.description," (",request_account.content,")"),"")) as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_enterprise.name,"No hay")) as review_enterprise,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_direction.name,"No hay")) as review_direction,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,review_department.name,"No hay")) as review_department,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17, review_project.proyectName, "No hay")) as review_project,
					IF(request_models.kind = 15, "Varias", IF(request_models.kind = 17, CONCAT(review_account.account," ",review_account.description," (",review_account.content,")"), "No hay")) as review_account,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.checkComment,"No hay")) as review_checkComment,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
					IF(request_models.kind = 15,"Varias",IF(request_models.kind = 17,request_models.authorizeComment,"No hay")) as authorize_Comment,
					IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseOriginR IS NULL,me_origin_ent.name,me_origin_ent_r.name),"No Aplica") as origin_enterprise,
					IF(request_models.kind = 15,IF(movements_enterprises.idAccAccOriginR IS NULL,CONCAT(me_origin_acc.account," ",me_origin_acc.description," (",me_origin_acc.content,")"),CONCAT(me_origin_acc_r.account," ",me_origin_acc_r.description," (",me_origin_acc_r.content,")")),"No Aplica") as origin_account,
					IF(request_models.kind = 15,IF(movements_enterprises.idEnterpriseDestinyR IS NULL,me_destiny_ent.name,me_destiny_ent_r.name),"No Aplica") as destination_enterprise,
					IF(request_models.kind = 15,IF(movements_enterprises.idAccAccDestinyR IS NULL,CONCAT(me_destiny_acc.account," ",me_destiny_acc.description," (",me_destiny_acc.content,")"),CONCAT(me_destiny_acc_r.account," ",me_destiny_acc_r.description," (",me_destiny_acc_r.content,")")),"No Aplica") as destination_account,
					IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as amount,
					IF(request_models.kind = 17,purchase_records.provider,"") as business_name,
					IF(request_models.kind = 17,purchase_records.reference,"") as reference,
					IF(request_models.kind = 15,me_method.method,IF(request_models.kind = 17,purchase_records.paymentMethod,"")) as payment_method,
					"" as provider_bank,
					"" as provider_account,
					"" as provider_card,
					"" as provider_branch,
					"" as provider_reference,
					"" as provider_clabe,
					"" as provider_currency,
					"" as provider_agreement,
					IF(request_models.taxPayment = 1,"Fiscal","No Fiscal") as tax_payment,
					IF(request_models.kind = 17,pr_d.quantity,"") as d_quantity,
					IF(request_models.kind = 17,pr_d.unit,"") as d_unit,
					IF(request_models.kind = 17,pr_d.description,"") as d_description,
					"" as d_account,
					IF(request_models.kind = 17,pr_d.unitPrice,"") as d_unit_price,
					IF(request_models.kind = 17,pr_d.subtotal,"") as d_subtotal,
					IF(request_models.kind = 17,pr_d.tax,"") as d_tax,
					IFNULL(IF(request_models.kind = 17,pr_taxes.taxes_amount,0),0) as d_aditional_taxes,
					IFNULL(IF(request_models.kind = 17,pr_retention.retention_amount,0),0) as d_aditional_retention,
					IF(request_models.kind = 17,pr_d.total,"") as d_amount,
					IF(request_models.kind = 17,pr_d_labels.labels,"") as labels,
					"" as request_amount,
					"" as diff_against_request,
					"" as refund,
					"" as repay,
					IF(request_models.kind = 15,movements_enterprises.amount,IF(request_models.kind = 17,purchase_records.total,"")) as total_pay,
					IF(request_models.kind = 15,movements_enterprises.typeCurrency,IF(request_models.kind = 17,purchase_records.typeCurrency,"")) as currency,
					p.payment_exchange_rate as exchange_rate,
					p.payment_exchange_rate_description as exchange_rate_description,
					IFNULL(p.payment_amount,0) as paid_amount'
				)
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(128)->pluck('departament_id'))
						->orWhereNull('request_models.idDepartment');
				})
				->where(function($permissionProject)
				{
					$permissionProject->whereIn('request_models.idProject',Auth::user()->inChargeProject(128)->pluck('project_id'))
						->orWhereNull('request_models.idProject');
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'))
						->orWhereNull('request_models.idEnterprise');
				})
				->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
				{
					if($title_search != '')
					{
						$query->whereRaw('IF(request_models.kind = 15,CONCAT(movements_enterprises.title," - ",movements_enterprises.datetitle),IF(request_models.kind = 17,CONCAT(purchase_records.title," - ",purchase_records.datetitle),"")) LIKE "%'.$title_search.'%"');
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if ($kind != "")
					{
						$tmpKind = array();
						foreach($kind as $k)
						{
							if(in_array($k,[15,17]))
							{
								$tmpKind[] = $k;
							}
						}
						$query->whereIn('request_models.kind',$tmpKind);
					}
					else
					{
						$query->whereIn('request_models.kind',[15,17]);
					}
					if ($enterprise != "")
					{                               
						$query->whereIn('request_models.idEnterprise',$enterprise);
					}
					if ($project != "")
					{                               
						$query->whereIn('request_models.idProject',$project);
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
						$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->where(function($q) use ($wbs)
						{
							$q->whereIn('request_models.code_wbs',$wbs);
						});
					}
				})
				->orderBy('request_models.kind','ASC')
				->orderBy('request_models.folio','ASC')
				->join('status_requests','request_models.status','idrequestStatus')
				->join('request_kinds','request_models.kind','idrequestkind')
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
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->leftJoin('movements_enterprises',function($q)
				{
					$q->on('request_models.folio','=','movements_enterprises.idFolio')
					->on('request_models.kind','=','movements_enterprises.idKind');
				})
				->leftJoin('enterprises as me_origin_ent','movements_enterprises.idEnterpriseOrigin','me_origin_ent.id')
				->leftJoin('enterprises as me_origin_ent_r','movements_enterprises.idEnterpriseOriginR','me_origin_ent_r.id')
				->leftJoin('accounts as me_origin_acc','movements_enterprises.idAccAccOrigin','me_origin_acc.idAccAcc')
				->leftJoin('accounts as me_origin_acc_r','movements_enterprises.idAccAccOriginR','me_origin_acc_r.idAccAcc')
				->leftJoin('enterprises as me_destiny_ent','movements_enterprises.idEnterpriseDestiny','me_destiny_ent.id')
				->leftJoin('enterprises as me_destiny_ent_r','movements_enterprises.idEnterpriseDestinyR','me_destiny_ent_r.id')
				->leftJoin('accounts as me_destiny_acc','movements_enterprises.idAccAccDestiny','me_destiny_acc.idAccAcc')
				->leftJoin('accounts as me_destiny_acc_r','movements_enterprises.idAccAccDestinyR','me_destiny_acc_r.idAccAcc')
				->leftJoin('payment_methods as me_method','movements_enterprises.idpaymentMethod','me_method.idpaymentMethod')
				->leftJoin('purchase_records',function($q)
				{
					$q->on('request_models.folio','=','purchase_records.idFolio')
					->on('request_models.kind','=','purchase_records.idKind');
				})
				->leftJoin('purchase_record_details as pr_d','purchase_records.id','pr_d.idPurchaseRecord')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as taxes_amount FROM purchase_record_taxes GROUP BY idPurchaseRecordDetail) AS pr_taxes'),'pr_d.id','pr_taxes.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, SUM(amount) as retention_amount FROM purchase_record_retentions GROUP BY idPurchaseRecordDetail) AS pr_retention'),'pr_d.id','pr_retention.idPurchaseRecordDetail')
				->leftJoin(DB::raw('(SELECT idPurchaseRecordDetail, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM purchase_record_labels INNER JOIN labels ON purchase_record_labels.idLabel = labels.idlabels GROUP BY idPurchaseRecordDetail) AS pr_d_labels'),'pr_d.id','pr_d_labels.idPurchaseRecordDetail')
				->leftJoin('cat_code_w_bs as wbs','request_models.code_wbs','wbs.id')
				->leftJoin('cat_code_e_d_ts as edt','request_models.code_edt','edt.id')
				->get();
			foreach($requests as $keyRow => $row)
			{
				if($tmpFolio != $row->folio)
				{
					$tmpFolio = $row->folio;
					$kindRow  = !$kindRow;
				}
				else
				{
					$row->folio                     = null;
					$row->idRequisition             = '';
					$row->status                    = '';
					$row->kind                      = '';
					$row->title                     = '';
					$row->order_number              = '';
					$row->estimate_number           = '';
					$row->request_user              = '';
					$row->elaborate_user            = '';
					$row->elaborate_date            = '';
					$row->request_enterprise        = '';
					$row->request_direction         = '';
					$row->request_department        = '';
					$row->request_project           = '';
					$row->request_account           = '';
					$row->review_user               = '';
					$row->review_date               = '';
					$row->review_enterprise         = '';
					$row->review_direction          = '';
					$row->review_department         = '';
					$row->review_project            = '';
					$row->review_account            = '';
					$row->authorize_user            = '';
					$row->authorize_date            = '';
					$row->origin_enterprise         = '';
					$row->origin_account            = '';
					$row->destination_enterprise    = '';
					$row->destination_account       = '';
					$row->amount                    = '';
					$row->business_name             = '';
					$row->reference                 = '';
					$row->payment_method            = '';
					$row->tax_payment               = '';
					$row->total_pay                 = null;
					$row->currency                  = '';
					$row->exchange_rate             = '';
					$row->exchange_rate_description = '';
					$row->paid_amount               = null;
					$row->review_checkComment       = '';
					$row->authorize_Comment         = '';
				}
				$tmpArr = [];
				foreach($row as $k => $r)
				{
					if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
					elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
				unset($requests[$keyRow]);
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
				}
				unset($tmpArr);
				$writer->addRow($rowFromValues);
				unset($rowFromValues);
			}
			$requests = DB::table('request_models')
				->selectRaw(
					'request_models.folio,
					"" as idRequisition,
					status_requests.description as status,
					request_kinds.kind as kind,
					"" AS checkup,
					"" AS resource_folio,
					CONCAT(nominas.title," - ",nominas.datetitle) as title,
					"" as order_number,
					wd_wbs.wbs as wbs,
					"" as edt,
					request_models.estimate_number as estimate_number,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate,"%d-%m-%Y %H:%i") as elaborate_date,
					enterprises.name as request_enterprise,
					areas.name as request_direction,
					departments.name as request_department,
					projects.proyectName as request_project,
					CONCAT(accounts.account," ",accounts.description," (",accounts.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					IF(request_models.reviewDate IS NULL,"No Aplica",DATE_FORMAT(request_models.reviewDate,"%d-%m-%Y %H:%i")) as review_date,
					"" as review_enterprise,
					"" as review_direction,
					"" as review_department,
					"" as review_project,
					"" as review_account,
					request_models.checkComment as review_checkComment,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					IF(request_models.authorizeDate IS NULL,"No Aplica",DATE_FORMAT(request_models.authorizeDate,"%d-%m-%Y %H:%i")) as authorize_date,
					request_models.authorizeComment as authorize_Comment,
					"No Aplica" as origin_enterprise,
					"No Aplica" as origin_account,
					"No Aplica" as destination_enterprise,
					"No Aplica" as destination_account,
					nominas.amount as amount,
					CONCAT_WS(" ",real_employees.last_name,real_employees.scnd_last_name,real_employees.name) as business_name,
					"" as reference,
					payment_methods.method as payment_method,
					"" as provider_bank,
					"" as provider_account,
					"" as provider_card,
					"" as provider_branch,
					"" as provider_reference,
					"" as provider_clabe,
					"" as provider_currency,
					"" as provider_agreement,
					IF(nominas.type_nomina = 1,"Fiscal","No Fiscal") as tax_payment,
					"1" as d_quantity,
					"" as d_unit,
					cat_type_payrolls.description as d_description,
					"" as d_account,
					IF(
						nominas.type_nomina != 1,
						(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
						IF(
							nominas.idCatTypePayroll = "001",
							salaries.totalPerceptions,
							IF(
								nominas.idCatTypePayroll = "002",
								bonuses.totalPerceptions,
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									liquidations.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "005",
										vacation_premia.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "006",
											profit_sharings.totalPerceptions,
											0
										)
									)
								)
							)
						)
					)
					as d_unit_price,
					IF(
						nominas.type_nomina != 1,
						(IFNULL(extras.amount,0) + nomina_employee_n_fs.complementPartial),
						IF(
							nominas.idCatTypePayroll = "001",
							salaries.totalPerceptions,
							IF(
								nominas.idCatTypePayroll = "002",
								bonuses.totalPerceptions,
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									liquidations.totalPerceptions,
									IF(
										nominas.idCatTypePayroll = "005",
										vacation_premia.totalPerceptions,
										IF(
											nominas.idCatTypePayroll = "006",
											profit_sharings.totalPerceptions,
											0
										)
									)
								)
							)
						)
					) as d_subtotal,
					"" as d_tax,
					"" as d_aditional_taxes,
					IF(
						nominas.type_nomina != 1,
						IFNULL(discounts.amount,0),
						IF(
							nominas.idCatTypePayroll = "001",
							salaries.totalRetentions,
							IF(
								nominas.idCatTypePayroll = "002",
								bonuses.totalTaxes,
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									liquidations.totalRetentions,
									IF(
										nominas.idCatTypePayroll = "005",
										vacation_premia.totalTaxes,
										IF(
											nominas.idCatTypePayroll = "006",
											profit_sharings.totalRetentions,
											0
										)
									)
								)
							)
						)
					) as d_aditional_retention,
					IF(
						nominas.type_nomina != 1,
						nomina_employee_n_fs.amount,
						IF(
							nominas.idCatTypePayroll = "001",
							salaries.netIncome,
							IF(
								nominas.idCatTypePayroll = "002",
								bonuses.netIncome,
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									liquidations.netIncome,
									IF(
										nominas.idCatTypePayroll = "005",
										vacation_premia.netIncome,
										IF(
											nominas.idCatTypePayroll = "006",
											profit_sharings.netIncome,
											0
										)
									)
								)
							)
						)
					) as d_amount,
					"" as labels,
					"" as request_amount,
					"" as diff_against_request,
					"" as refund,
					"" as repay,
					IF(
						nominas.type_nomina != 1,
						nomina_employee_n_fs.amount,
						IF(
							nominas.idCatTypePayroll = "001",
							salaries.netIncome,
							IF(
								nominas.idCatTypePayroll = "002",
								bonuses.netIncome,
								IF(
									nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
									liquidations.netIncome,
									IF(
										nominas.idCatTypePayroll = "005",
										vacation_premia.netIncome,
										IF(
											nominas.idCatTypePayroll = "006",
											profit_sharings.netIncome,
											0
										)
									)
								)
							)
						)
					) as total_pay,
					"MXN" as currency,
					p.payment_exchange_rate as exchange_rate,
					p.payment_exchange_rate_description as exchange_rate_description,
					IFNULL(p.payment_amount,0) as paid_amount'
				)
				->where(function($permissionDep)
				{
					$permissionDep->whereIn('worker_datas.department',Auth::user()->inChargeDep(128)->pluck('departament_id'));
				})
				->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('worker_datas.enterprise',Auth::user()->inChargeEnt(128)->pluck('enterprise_id'));
				})
				->where(function ($query) use ($account, $name, $enterprise, $direction, $department, $status, $kind, $folio, $mindate, $maxdate, $mindate_review, $maxdate_review, $mindate_authorize, $maxdate_authorize, $project, $wbs, $title_search)
				{
					if($title_search != '')
					{
						$query->whereRaw('CONCAT(nominas.title," - ",nominas.datetitle) LIKE "%'.$title_search.'%"');
					}
					if ($folio != "") 
					{
						$query->where('request_models.folio',$folio);
					}
					if($account != "")
					{
						$query->where('request_models.accountR',$account);
					}
					if ($kind != "" && $kind != "todas")
					{
						$tmpKind = array();
						foreach($kind as $k)
						{
							if(in_array($k,[16]))
							{
								$tmpKind[] = $k;
							}
						}
						$query->whereIn('request_models.kind',$tmpKind);
					}
					else
					{
						$query->whereIn('request_models.kind',[16]);
					}
					if ($enterprise != "")
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
					if ($project != "")
					{
						$query->whereIn('worker_datas.project',$project);
					}
					if ($direction != "")
					{
						$query->whereIn('worker_datas.direction',$direction);
					}
					if ($department != "")
					{
						$query->whereIn('worker_datas.department',$department);
					}
					if($name != "")
					{
						$query->whereRaw('CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) LIKE "%'.$name.'%"');
					}
					if ($mindate != '' && $maxdate != '') 
					{
						$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if ($mindate_review != '' && $maxdate_review != '') 
					{
						$query->whereBetween('request_models.reviewDate',[''.$mindate_review.' '.date('00:00:00').'',''.$maxdate_review.' '.date('23:59:59').'']);
					}
					if ($mindate_authorize != '' && $maxdate_authorize != '') 
					{
						$query->whereBetween('request_models.authorizeDate',[''.$mindate_authorize.' '.date('00:00:00').'',''.$maxdate_authorize.' '.date('23:59:59').'']);
					}
					if ($status != "") 
					{
						$query->whereIn('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[4,5,6,7,10,11,12,13,18]);
					}
					if($wbs != "")
					{
						$query->whereIn('wd_wbs.id',$wbs);
					}
				})
				->orderBy('request_models.folio','ASC')
				->join('status_requests','request_models.status','idrequestStatus')
				->join('request_kinds','request_models.kind','idrequestkind')
				->leftJoin('users as request_user','idRequest','request_user.id')
				->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
				->leftJoin('users as review_user','idCheck','review_user.id')
				->leftJoin('users as authorize_user','idAuthorize','authorize_user.id')
				->leftJoin('nominas',function($q)
				{
					$q->on('request_models.folio','=','nominas.idFolio')
					->on('request_models.kind','=','nominas.idKind');
				})
				->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
				->leftJoin('real_employees','nomina_employees.idrealEmployee','real_employees.id')
				->leftJoin('worker_datas','nomina_employees.idworkingData','worker_datas.id')
				->leftJoin('projects','worker_datas.project','projects.idproyect')
				->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
				->leftJoin('accounts','worker_datas.account','accounts.idAccAcc')
				->leftJoin('areas','worker_datas.direction','areas.id')
				->leftJoin('departments','worker_datas.department','departments.id')
				->leftJoin('payment_methods','worker_datas.paymentWay','payment_methods.idpaymentMethod')
				->leftJoin(DB::raw('(SELECT cat_code_w_bs.id as id, cat_code_w_bs.code_wbs as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id INNER JOIN (SELECT IF(indirect_count > 0, indirect_id, min_id) as id, wd_id FROM (SELECT SUM(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",1,0)) AS indirect_count, GROUP_CONCAT(IF(cat_code_w_bs.code_wbs LIKE "%indirecto%",employee_w_b_s.id,NULL)) AS indirect_id, MIN(employee_w_b_s.id) min_id, employee_w_b_s.working_data_id AS wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id = cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as SELECTOR) AS wbs_cond ON employee_w_b_s.id = wbs_cond.id AND employee_w_b_s.working_data_id = wbs_cond.wd_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
				->leftJoin('cat_type_payrolls','nominas.idCatTypePayroll','cat_type_payrolls.id')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, idnominaEmployee, exchange_rate as payment_exchange_rate, exchange_rate_description as payment_exchange_rate_description, SUM(amount) as payment_amount FROM payments GROUP BY idnominaEmployee, idFolio, idKind, payment_exchange_rate, payment_exchange_rate_description) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind')
						->on('nomina_employees.idnominaEmployee','=','p.idnominaEmployee');
					}
				)
				->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
				->leftJoin('bonuses','nomina_employees.idnominaEmployee','bonuses.idnominaEmployee')
				->leftJoin('liquidations','nomina_employees.idnominaEmployee','liquidations.idnominaEmployee')
				->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','vacation_premia.idnominaEmployee')
				->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','profit_sharings.idnominaEmployee')
				->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM extras_nominas GROUP BY idnominaemployeenf) as extras'),'nomina_employee_n_fs.idnominaemployeenf','extras.idnominaemployeenf')
				->leftJoin(DB::raw('(SELECT SUM(amount) as amount, idnominaemployeenf FROM discounts_nominas GROUP BY idnominaemployeenf) as discounts'),'nomina_employee_n_fs.idnominaemployeenf','discounts.idnominaemployeenf')
				->get();
			foreach($requests as $keyRow => $row)
			{
				if($tmpFolio != $row->folio)
				{
					$tmpFolio = $row->folio;
					$kindRow  = !$kindRow;
				}
				else
				{
					$row->folio                     = null;
					$row->idRequisition             = '';
					$row->status                    = '';
					$row->kind                      = '';
					$row->title                     = '';
					$row->order_number              = '';
					$row->estimate_number           = '';
					$row->request_user              = '';
					$row->elaborate_user            = '';
					$row->elaborate_date            = '';
					$row->review_user               = '';
					$row->review_date               = '';
					$row->review_enterprise         = '';
					$row->review_direction          = '';
					$row->review_department         = '';
					$row->review_project            = '';
					$row->review_account            = '';
					$row->authorize_user            = '';
					$row->authorize_date            = '';
					$row->origin_enterprise         = '';
					$row->origin_account            = '';
					$row->destination_enterprise    = '';
					$row->destination_account       = '';
					$row->reference                 = '';
					$row->amount                    = '';
					$row->tax_payment               = '';
					$row->review_checkComment       = '';
					$row->authorize_Comment         = '';
				}
				$tmpArr = [];
				foreach($row as $k => $r)
				{
					if(in_array($k,['amount','d_unit_price','d_subtotal', 'd_tax', 'd_aditional_taxes', 'd_aditional_retention', 'd_amount', 'paid_amount', 'total_pay','request_amount','diff_against_request','refund']))
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
					elseif($k == 'd_quantity' || $k == 'exchange_rate')
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
				unset($requests[$keyRow]);
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowLight);
				}
				unset($tmpArr);
				$writer->addRow($rowFromValues);
				unset($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return abort(404);
		}
	}

}
