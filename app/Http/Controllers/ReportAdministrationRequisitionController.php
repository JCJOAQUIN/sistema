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
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Carbon\Carbon;

class ReportAdministrationRequisitionController extends Controller
{
	private $module_id = 96;
	public function requisition(Request $request)
	{
		if (Auth::user()->module->where('id', 236)->count() > 0) 
		{
			$data				= App\Module::find($this->module_id);
			$title_request		= $request->title_request;
			$mindate_request	= $request->mindate_request!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_request)->format('Y-m-d') : null;
			$maxdate_request	= $request->maxdate_request!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_request)->format('Y-m-d') : null;
			$mindate_obra		= $request->mindate_obra!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_obra)->format('Y-m-d') : null;
			$maxdate_obra		= $request->maxdate_obra!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_obra)->format('Y-m-d') : null;
			$status				= $request->status;
			$folio				= $request->folio;
			$user_request		= $request->user_request;
			$project_request	= $request->project_request;
			$number				= $request->number;
			$wbs				= $request->wbs;
			$edt				= $request->edt;
			$type				= $request->type;
			$category			= $request->category;
			$data				= App\Module::find($this->module_id);

			if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request=="") || ($mindate_request!="" && $maxdate_request!=""))
			{
				$mindate_request    = $request->mindate_request;
				$maxdate_request    = $request->maxdate_request;

				if(($mindate_request=="" && $maxdate_request!="") || ($mindate_request!="" && $maxdate_request==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_request!="" && $maxdate_request!="" && $maxdate_request < $mindate_request) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra=="") || ($mindate_obra!="" && $maxdate_obra!=""))
			{
				$mindate_obra   = $request->mindate_obra;
				$maxdate_obra   = $request->maxdate_obra;

				if(($mindate_obra=="" && $maxdate_obra!="") || ($mindate_obra!="" && $maxdate_obra==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate_obra!="" && $maxdate_obra!="" && $maxdate_obra < $mindate_obra) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$requests = App\RequestModel::leftJoin('requisitions','request_models.folio','requisitions.idFolio')
				->where('request_models.kind',19)
				->where('status','!=',23)
				->where(function($query)
				{
					$query->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio, $status,$project_request,$number,$wbs,$edt,$type,$category)
				{
					if ($category != "") 
					{
						$query->whereHas('requisition',function($q) use($category)
						{
							$q->whereHas('details',function($q2) use($category)
							{
								$q2->whereIn('category',$category);
							});
						});
					}
					if ($user_request != "") 
					{
						$query->whereIn('request_models.idRequest',$user_request);
					}
					if($title_request != "")
					{
						$query->where('requisitions.title','LIKE','%'.$title_request.'%');
					}
					if ($mindate_request != "") 
					{
						$query->whereBetween('requisitions.date_request',[''.$mindate_request.' '.date('00:00:00').'',''.$maxdate_request.' '.date('23:59:59').'']);
					}
					if ($mindate_obra != "") 
					{
						$query->whereBetween('requisitions.date_obra',[''.$mindate_obra.' '.date('00:00:00').'',''.$maxdate_obra.' '.date('23:59:59').'']);
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if ($project_request != "") 
					{
						$query->whereIn('request_models.idProject',$project_request);
					}
					if ($number != "") 
					{
						$query->where('requisitions.number','LIKE','%'.$number.'%');
					}
					if($wbs != "")
					{
						$query->whereIn('requisitions.code_wbs',$wbs);
					}
					if($edt != "")
					{
						$query->whereIn('requisitions.code_edt',$edt);
					}
					if($type != "")
					{
						$query->whereIn('requisitions.requisition_type',$type);
					}
				})
				->orderBy('request_models.fDate','DESC')
				->orderBy('request_models.folio','DESC')
				->paginate(10);
			$data = App\Module::find($this->module_id);
			return view(
				'reporte.administracion.requisition',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => $this->module_id,
					'option_id'       => 236,
					'requests'        => $requests,
					'mindate_obra'    => $mindate_obra,
					'maxdate_obra'    => $maxdate_obra,
					'mindate_request' => $mindate_request,
					'maxdate_request' => $maxdate_request,
					'folio'           => $folio,
					'status'          => $status,
					'title_request'   => $title_request,
					'user_request'    => $user_request,
					'project_request' => $project_request,
					'number'          => $number,
					'wbs'             => $wbs,
					'edt'             => $edt,
					'type'            => $type,
					'category'        => $category
				]
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function requisitionExcel(Request $request)
	{
		if (Auth::user()->module->where('id',236)->count()>0) 
		{
			$title_request		= $request->title_request;
			$mindate_request	= $request->mindate_request!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_request)->format('Y-m-d') : null;
			$maxdate_request	= $request->maxdate_request!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_request)->format('Y-m-d') : null;
			$mindate_obra		= $request->mindate_obra!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate_obra)->format('Y-m-d') : null;
			$maxdate_obra		= $request->maxdate_obra!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate_obra)->format('Y-m-d') : null;
			$status				= $request->status;
			$folio				= $request->folio;
			$user_request		= $request->user_request;
			$project_request	= $request->project_request;
			$number				= $request->number;
			$wbs				= $request->wbs;
			$edt				= $request->edt;
			$type				= $request->type;
			$employee			= $request->employee;
			$category			= $request->category;
			$new_sheet			= true;

			$defaultStyle	= (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setCellAlignment(CellAlignment::LEFT)->build();
			$currencyFormat	= (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark		= (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1	= (new StyleBuilder())->setBackgroundColor('771414')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol2	= (new StyleBuilder())->setBackgroundColor('db5151')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol3	= (new StyleBuilder())->setBackgroundColor('21bbbb')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$mhStyleCol4	= (new StyleBuilder())->setBackgroundColor('1a6206')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer			= WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('requisiciones.xlsx');
			for ($i=1; $i < 7; $i++) 
			{ 	
				switch ($i) 
				{
					case 1:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMaterial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											cat_procurement_materials.name as procurementMaterialType,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->leftJoin('cat_procurement_materials','cat_procurement_materials.id','requisition_details.cat_procurement_material_id')
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('request_models.status','!=',23)
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMaterial)>0) 
							{
								$new_sheet = false;
								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Tipo','Cant','Medida','Unidad','Nombre','Descripción','Existencia en Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$writer->getCurrentSheet()->setName('Material');

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMaterial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					case 2:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestServices = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.period as period
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestServices)>0) 
							{
								if ($new_sheet) 
								{
									$writer->getCurrentSheet()->setName('Servicios_Generales');
									$new_sheet = false;
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Servicios_Generales');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Unidad','Nombre','Descripción','Periodo'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestServices as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}		

						break;

					case 3:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestPersonal = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											requisitions.date_obra as date_obra,
											CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisition_employees.name as name,
											requisition_employees.last_name as last_name,
											requisition_employees.scnd_last_name as scnd_last_name,
											requisition_employees.curp as curp,
											requisition_employees.rfc as rfc,
											cat_tax_regimes.description as taxRegime,
											requisition_employees.imss as imss,
											requisition_employees.street as street,
											requisition_employees.number as number,
											requisition_employees.colony as colony,
											requisition_employees.cp as cp,
											requisition_employees.city as city,
											states.description as states_employee,
											requisition_employees.email as email,
											requisition_employees.phone as phone,
											state_work.description as state_work,
											projects.proyectName as proyect_employee,
											cat_code_w_bs.code_wbs as wbs_employee,
											enterprises.name as enterprise_name,
											CONCAT(accounts.account," ",accounts.description) as account_employee,
											directions.name as direction_name,
											departments.name as department_name,
											subdepartments.name as subdepartment_name,
											requisition_employees.position as position,
											requisition_employees.immediate_boss as immediate_boss,
											requisition_employees.position_immediate_boss as position_immediate_boss,
											IF(requisition_employees.status_imss = 1,"Activo","Inactivo") as status_imss,
											requisition_employees.admissionDate as admissionDate,
											requisition_employees.imssDate as imssDate,
											requisition_employees.downDate as downDate,
											requisition_employees.endingDate as endingDate,
											requisition_employees.reentryDate as reentryDate,
											cat_contract_types.description as contract_type,
											cat_regime_types.description as regime_type,
											IF(requisition_employees.workerStatus = 1,"Activo",IF(requisition_employees.workerStatus = 2,"Baja pacial",IF(requisition_employees.workerStatus = 3,"Baja definitiva",IF(requisition_employees.workerStatus = 4,"Suspensión","Boletinado")))) as worker_status,
											requisition_employees.status_reason as status_reason,
											requisition_employees.sdi as sdi,
											cat_periodicities.description as periodicity,
											requisition_employees.employer_register,
											payment_methods.method as paymentWay,
											requisition_employees.netIncome,
											requisition_employees.viatics,
											requisition_employees.camping,
											requisition_employees.complement,
											requisition_employees.fonacot,
											requisition_employees.nomina,
											requisition_employees.bono,
											requisition_employees.infonavitCredit,
											requisition_employees.infonavitDiscount,
											requisition_employees.infonavitDiscountType,
											bank_data.alias,
											cat_banks.description as employee_bank,
											CONCAT(bank_data.clabe," ") as clabe,
											CONCAT(bank_data.account," ") as account,
											CONCAT(bank_data.cardNumber," ") as cardNumber,
											bank_data.branch

										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_employees','requisition_employees.requisition_id','requisitions.id')
										->leftJoin('states','states.idstate','requisition_employees.state_id')
										->leftJoin('states as state_work','state_work.idstate','requisition_employees.state')
										->leftJoin('cat_tax_regimes','cat_tax_regimes.taxRegime','requisition_employees.tax_regime')
										->leftJoin('enterprises','enterprises.id','requisition_employees.enterprise')
										->leftJoin('accounts','accounts.idAccAcc','requisition_employees.account')
										->leftJoin('areas as directions','directions.id','requisition_employees.direction')
										->leftJoin('departments','departments.id','requisition_employees.department')
										->leftJoin('subdepartments','subdepartments.id','requisition_employees.subdepartment_id')
										->leftJoin('cat_contract_types','requisition_employees.workerType','cat_contract_types.id')
										->leftJoin('cat_regime_types','requisition_employees.regime_id','cat_regime_types.id')
										->leftJoin('cat_periodicities','requisition_employees.periodicity','cat_periodicities.c_periodicity')
										->leftJoin('payment_methods','requisition_employees.paymentWay','payment_methods.idpaymentMethod')
										->leftJoin(DB::raw('(SELECT * FROM requisition_employee_accounts WHERE id IN(SELECT MIN(id) as id FROM requisition_employee_accounts GROUP BY idEmployee)) as bank_data'),'requisition_employees.id','bank_data.idEmployee')
										->leftJoin('cat_banks','bank_data.idCatBank','cat_banks.c_bank')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$employee,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($employee != "") 
											{
												$query->where(DB::raw("CONCAT_WS(' ',requisition_employees.name,requisition_employees.last_name,requisition_employees.scnd_last_name)"),'LIKE','%'.$employee.'%');
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestPersonal)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Personal');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Personal');
								}

								$headers =
								[
									'folio', 'proyecto', 'wbs', 'fecha_en_obra', 'solicitante', 'titulo', 'nombre', 'apellido', 'apellido2', 'curp', 'rfc', 'regimen_fiscal', 'imss', 'calle', 'numero', 'colonia', 'cp', 'ciudad', 'estado', 'correo_electronico', 'numero_telefonico', 'estado_laboral', 'proyecto', 'wbs', 'empresa', 'clasificacion_gasto', 'direccion', 'departamento', 'subdepartamento', 'puesto', 'jefe_inmediato', 'posicion_jefe_inmediato', 'estatus_imss', 'fecha_ingreso', 'fecha_alta', 'fecha_baja', 'fecha_termino', 'fecha_reingreso', 'tipo_contrato', 'regimen', 'estatus', 'razon_estatus', 'sdi', 'periodicidad', 'registro_patronal', 'forma_pago', 'sueldo_neto', 'viaticos', 'campamento', 'complemento', 'fonacot', 'porcentaje_nomina', 'porcentaje_bono', 'credito_infonavit', 'descuento_infonavit', 'tipo_descuento_infonavit', 'alias', 'banco', 'clabe', 'cuenta', 'tarjeta', 'sucursal'
								];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 5)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
									}
									elseif($k <= 20)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 55)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
									elseif($k <= 61)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestPersonal as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio			= null;
										$request->proyectName	= '';
										$request->wbs			= '';
										$request->date_obra		= '';
										$request->request_user	= '';
										$request->title			= '';
										
									}
									$tmpArr = [];
									foreach($request as $k => $r)
									{
										$tmpArr[] = WriterEntityFactory::createCell($r);
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
							}
						}
						break;	

					case 4:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestSubcontracts = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestSubcontracts)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Subcontratos');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Subcontratos');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestSubcontracts as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;
					
					case 5:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestMachine = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											cat_warehouse_types.description as categoryData,
											requisition_details.quantity as quantity,
											requisition_details.measurement as measurement,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description,
											requisition_details.brand as brand,
											requisition_details.model as model,
											requisition_details.usage_time as usage_time,
											requisition_details.exists_warehouse as exists_warehouse
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->leftJoin('cat_warehouse_types','cat_warehouse_types.id','requisition_details.category')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
											
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestMachine)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Maquinaria');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Maquinaria');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Categoría','Cantidad','Medida','Unidad','Nombre','Descripción','Marca','Modelo','Tiempo de Utilización','Existencia Almacén'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);

								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestMachine as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}	

						break;
					
					case 6:
						if(!isset($type) || ($type != "" && in_array($i,$type)))
						{
							$requestComercial = DB::table('request_models')->selectRaw('
											request_models.folio as folio,
											projects.proyectName as proyectName,
											cat_code_w_bs.code_wbs as wbs,
											cat_code_e_d_ts.code as edt,
											IF(requisitions.urgent = 1,"Alta","Baja") as urgent,
											CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) as request_user,
											requisitions.title as title,
											requisitions.number as number,
											requisitions.generated_number as generated_number,
											requisitions.date_request as date_request,
											status_requests.description as status_requests,
											requisition_details.part as part,
											requisition_details.quantity as quantity,
											requisition_details.unit as unit,
											requisition_details.name as name,
											requisition_details.description as description
										')
										->leftJoin('requisitions','request_models.folio','requisitions.idFolio')
										->leftJoin('requisition_details','requisition_details.idRequisition','requisitions.id')
										->leftJoin('projects','projects.idproyect','request_models.idProject')
										->leftJoin('users','users.id','request_models.idRequest')
										->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
										->leftJoin('cat_code_w_bs','cat_code_w_bs.id','requisitions.code_wbs')
										->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','requisitions.code_edt')
										->where('request_models.status','!=',23)
										->whereIn('idProject',Auth::user()->inChargeProject(236)->pluck('project_id'))
										->where('requisitions.requisition_type',$i)
										->where(function ($query) use ($title_request, $user_request, $mindate_request, $maxdate_request, $mindate_obra, $maxdate_obra, $folio,$project_request,$number,$wbs,$edt,$type,$status,$category)
										{
											if($status != "")
											{
												$query->whereIn('request_models.status',$status);
											}
											if ($user_request != "")
											{
												$query->whereIn('request_models.idRequest',$user_request);
											}
											if($title_request != "")
											{
												$query->where('requisitions.title','LIKE','%'.$title_request.'%');
											}
											if ($mindate_request != "")
											{
												$query->whereBetween('requisitions.date_request',[$mindate_request->format('Y-m-d 00:00:00'), $maxdate_request->format('Y-m-d 23:59:59')]);
											}
											if ($mindate_obra != "")
											{
												$query->whereBetween('requisitions.date_obra',[$mindate_obra->format('Y-m-d 00:00:00'), $maxdate_obra->format('Y-m-d 23:59:59')]);
											}
											if($folio != "")
											{
												$query->where('request_models.folio',$folio);
											}
											if ($project_request != "")
											{
												$query->whereIn('request_models.idProject',$project_request);
											}
											if ($number != "")
											{
												$query->where('requisitions.number','LIKE','%'.$number.'%');
											}
											if($wbs != "")
											{
												$query->whereIn('requisitions.code_wbs',$wbs);
											}
											if($edt != "")
											{
												$query->whereIn('requisitions.code_edt',$edt);
											}
											if ($category != "") 
											{
												$query->whereIn('requisition_details.category',$category);
											}
										})
										->orderBy('request_models.fDate','DESC')
										->orderBy('request_models.folio','DESC')
										->get();

							if (count($requestComercial)>0) 
							{
								if ($new_sheet) 
								{
									$new_sheet = false;
									$writer->getCurrentSheet()->setName('Comercial');
								}
								else
								{
									$newSheet = $writer->addNewSheetAndMakeItCurrent();
									$writer->getCurrentSheet()->setName('Comercial');
								}

								$headers = ['Folio','Proyecto','Subproyecto/Código WBS','Código EDT','Prioridad','Solicitante','Título','Número','Fecha Solicitó','Fecha Obra','Estado','Partida','Cantidad','Unidad','Nombre','Descripción'];

								$tempHeader      = [];
								foreach($headers as $k => $header)
								{
									if($k <= 10)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
									}
									elseif($k <= 21)
									{
										$tempHeader[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
									}
								}
								$rowFromValues = WriterEntityFactory::createRow($tempHeader);
								$writer->addRow($rowFromValues);
								
								$tempFolio     = '';
								$kindRow       = true;
								foreach($requestComercial as $request)
								{
									if($tempFolio != $request->folio)
									{
										$tempFolio = $request->folio;
										$kindRow = !$kindRow;
									}
									else
									{
										$request->folio				= null;
										$request->proyectName		= '';
										$request->wbs				= '';
										$request->edt				= '';
										$request->urgent			= '';
										$request->request_user		= '';
										$request->title				= '';
										$request->number			= '';
										$request->generated_number	= '';
										$request->date_request		= '';
										$request->status_requests	= '';
										
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
							}
						}

						break;

					default:
						// code...
						break;
				}
			}

			return $writer->close();
		}
		else
		{
			return redirect('/error');
		}
	}
}

