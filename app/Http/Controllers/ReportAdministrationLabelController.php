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

class ReportAdministrationLabelController extends Controller
{
	private $module_id = 96;
	public function labelsReport(Request $request)
	{
		if (Auth::user()->module->where('id',133)->count()>0)
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
						$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(133)->pluck('enterprise_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('enterprise',Auth::user()->inChargeEnt(133)->pluck('enterprise_id'));
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
						$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(133)->pluck('departament_id'))
							->orWhereHas('nomina',function($q)
							{
								$q->whereHas('nominaEmployee',function($q)
								{
									$q->whereHas('workerData',function($q)
									{
										$q->whereIn('department',Auth::user()->inChargeDep(133)->pluck('departament_id'));
									});
								});
							})
							->orWhereNull('request_models.idDepartment');
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
			return view('reporte.administracion.maestro',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 133,
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

	public function labelsExcel(Request $request)
	{
		$min               = null;
		$max               = null;
		$labelArray        = $request->labels;
		$kindArray         = $request->kind;
		$statusArray       = $request->status;
		$enterpriseArray   = $request->enterprise;
		$departmentArray   = $request->department;
		$areaArray         = $request->area;
		$projectArray      = $request->project;
		$name              = $request->name;
		$folio             = $request->folio;
		$mindate           = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
		$maxdate           = $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
		$mindate_review    = $request->mindate_review!='' ? date('Y-m-d',strtotime($request->mindate_review)) : null;
		$maxdate_review    = $request->maxdate_review!='' ? date('Y-m-d',strtotime($request->maxdate_review)) : null;
		$mindate_authorize = $request->mindate_authorize!='' ? date('Y-m-d',strtotime($request->mindate_authorize)) : null;
		$maxdate_authorize = $request->maxdate_authorize!='' ? date('Y-m-d',strtotime($request->maxdate_authorize)) : null;
		$results           = array();
		$print             = array();
		$key               = 0; 

		if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
		{
			$initRange  = Carbon::parse($mindate)->format('Y-m-d');
			$endRange   = Carbon::parse($maxdate)->format('Y-m-d');

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
		elseif(($mindate_review=="" && $maxdate_review!="") || ($mindate_review!="" && $maxdate_review=="") || ($mindate_review!="" && $maxdate_review!=""))
		{
			$initRange_review   = Carbon::parse($mindate_review)->format('Y-m-d');
			$endRange_review    = Carbon::parse($maxdate_review)->format('Y-m-d');

			if(($mindate_review=="" && $maxdate_review!="") || ($mindate_review!="" && $maxdate_review==""))
			{
				$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
				return back()->with(['alert'=>$alert]);
			}
			if ($mindate_review!="" && $maxdate_review!="" && $endRange_review < $initRange_review) 
			{
				$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
				return back()->with(['alert'=>$alert]);
			}
		}
		elseif(($mindate_authorize=="" && $maxdate_authorize!="") || ($mindate_authorize!="" && $maxdate_authorize=="") || ($mindate_authorize!="" && $maxdate_authorize!=""))
		{
			$initRange_authorize    = Carbon::parse($mindate_authorize)->format('Y-m-d');
			$endRange_authorize     = Carbon::parse($maxdate_authorize)->format('Y-m-d');

			if(($mindate_authorize=="" && $maxdate_authorize!="") || ($mindate_authorize!="" && $maxdate_authorize==""))
			{
				$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
				return back()->with(['alert'=>$alert]);
			}
			if ($mindate_authorize!="" && $maxdate_authorize!="" && $endRange_authorize < $initRange_authorize) 
			{
				$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
				return back()->with(['alert'=>$alert]);
			}
		}

		$requests          = App\RequestModel::whereIn('kind',[1,2,3,8,9,11,12,13,14,15,16,17])
			->whereIn('status',[4,5,6,7,10,11,12,13,18])
			->where(function($permissionDep)
			{
				$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(133)->pluck('departament_id'))
					->orWhere('idDepartment',null);
			})
			->where(function($permissionEnt)
			{
				$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(133)->pluck('enterprise_id'))
					->orWhere('idEnterprise',null);
			})
			->where(function($query) use ($kindArray,$statusArray,$enterpriseArray,$departmentArray,$areaArray,$projectArray,$name,$folio,$mindate,$maxdate,$mindate_review,$maxdate_review,$mindate_authorize,$maxdate_authorize)
			{
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
				if ($folio != '') 
				{
					$query->where('folio',$folio);
				}
				if ($kindArray != '') 
				{
					$query->whereIn('kind',$kindArray);
				}
				if ($enterpriseArray != '') 
				{
					$query->whereIn('idEnterprise',$enterpriseArray);
				}
				if ($departmentArray != '') 
				{
					$query->whereIn('idDepartment',$departmentArray);
				}
				if ($areaArray != '') 
				{
					$query->whereIn('idArea',$areaArray);
				}
				if ($statusArray != '') 
				{
					$query->whereIn('status',$statusArray);
				}
				if ($projectArray != '') 
				{
					$query->whereIn('idProject',$projectArray);
				}
				if($name != "")
				{
					$query->whereHas('requestUser', function($q) use($name)
					{
						$q->whereRaw('CONCAT_WS(" ",name,last_name,scnd_last_name) LIKE "%'.$name.'%"');
					});
				}
			})
			->get();
		foreach ($requests as $request) 
		{
			switch ($request->kind) 
			{
				case 1:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->purchases->first()->title.' - '.$request->purchases->first()->datetitle;
					$results[$key]['numberOrder']   = $request->purchases->first()->numberOrder;
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->requestEnterprise->name;
					$results[$key]['requestDirection']   = $request->requestDirection->name;
					$results[$key]['requestDepartment']  = $request->requestDepartment->name;
					$results[$key]['requestProject']     = $request->requestProject->proyectName;
					$results[$key]['requestAccount']     = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']  = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment'] = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']    = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->purchases->first()->amount;
					$results[$key]['providerName']       = $request->purchases->first()->provider()->exists() ? $request->purchases->first()->provider->businessName : '';
					$results[$key]['reference']          = $request->purchases->first()->reference;
					$results[$key]['paymentMode']        = $request->purchases->first()->paymentMode;
					if($request->purchases->first()->provider_has_banks_id!='')
					{
						$results[$key]['bankName']      = $request->purchases->first()->bankData->bank->description;
						$results[$key]['bankAccount']   = $request->purchases->first()->bankData->account.' ';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = $request->purchases->first()->bankData->branch;
						$results[$key]['bankReference'] = $request->purchases->first()->bankData->reference;
						$results[$key]['bankClabe']     = $request->purchases->first()->bankData->clabe.' ';
						$results[$key]['bankCurrency']  = $request->purchases->first()->bankData->currency;
						$results[$key]['bankAgreement'] = $request->purchases->first()->bankData->agreement;
					}
					else
					{
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';
					}
					foreach ($request->purchases->first()->detailPurchase as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label)
						{
							$tempLabels .= $label->description.', ';
						}
						$idDetailPurchase = $detail->idDetailPurchase;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idDetailPurchase) { $q->whereIn('label_detail_purchases.idlabels',$labelArray)->where('idDetailPurchase',$idDetailPurchase); })->count()>0))
						{
							$print[$request->folio]         = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = $detail->quantity;
							$tempArray['detailUnit']        = $detail->unit;
							$tempArray['detailDescription'] = $detail->description;
							$tempArray['detailAccount']     = '';
							$tempArray['detailUnitPrice']   = $detail->unitPrice;
							$tempArray['detailSubtotal']    = $detail->subtotal;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$tempArray['detailTaxesConcept'] = $taxesConcept;
							$retentionConcept                = 0;
							foreach($detail->retentions as $ret)
							{
								$retentionConcept+=$ret->amount;
							}
							$tempArray['detailRetentionConcept'] = $retentionConcept;
							$tempArray['detailAmount']           = $detail->amount;
							$tempArray['detailLabel']            = $tempLabels;
							$tempArray['detailAmountResource']   = '';
							$tempArray['diferenceRequest']       = '';
							$tempArray['reembolso']              = '';
							$tempArray['reintegro']              = '';
							$results[$key]['concepts'][]         = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->purchases->first()->amount;
					$results[$key]['paymentTypeCurrency'] = $request->purchases->first()->typeCurrency;
					if ($request->paymentsRequest()->exists())
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';   
					}
					$key++;
					break;
				case 2:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->nominas->first()->title.' - '.$request->nominas->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = 'Varias';
					$results[$key]['requestDirection']   = 'Varios';
					$results[$key]['requestDepartment']  = 'Varios';
					$results[$key]['requestProject']     = 'Varios';
					$results[$key]['requestAccount']     = 'Varias';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = 'No hay';
					$results[$key]['reviewedDirection']  = 'No hay';
					$results[$key]['reviewedDepartment'] = 'No hay';
					$results[$key]['reviewedProject']    = 'No hay';
					$results[$key]['reviewedAccount']    = 'No hay';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->nominas->first()->amount;
					$results[$key]['providerName']       = '';
					$results[$key]['reference']          = '';
					$results[$key]['paymentMode']        = '';
					$results[$key]['bankName']           = '';
					$results[$key]['bankAccount']        = '';
					$results[$key]['bankCard']           = '';
					$results[$key]['bankBranch']         = '';
					$results[$key]['bankReference']      = '';
					$results[$key]['bankClabe']          = '';
					$results[$key]['bankCurrency']       = '';
					$results[$key]['bankAgreement']      = '';
					$tempArray                           = array();
					$tempArray['taxPayment']             = '';
					$tempArray['detailQuantity']         = '';
					$tempArray['detailUnit']             = '';
					$tempArray['detailDescription']      = '';
					$tempArray['detailAccount']          = '';
					$tempArray['detailUnitPrice']        = '';
					$tempArray['detailSubtotal']         = '';
					$tempArray['detailTax']              = '';
					$tempArray['detailTaxesConcept']     = '';
					$tempArray['detailRetentionConcept'] = '';
					$tempArray['detailAmount']           = '';
					$tempArray['detailLabel']            = '';
					$tempArray['detailAmountResource']   = '';
					$tempArray['diferenceRequest']       = '';
					$tempArray['reembolso']              = 'No Aplica';
					$tempArray['reintegro']              = 'No Aplica';
					$results[$key]['concepts'][]         = $tempArray;
					$key++;             
					break;
				case 3:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = $request->expenses->first()->resourceId;
					$results[$key]['title']         = $request->expenses->first()->title.' - '.$request->expenses->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->requestEnterprise->name;
					$results[$key]['requestDirection']   = $request->requestDirection->name;
					$results[$key]['requestDepartment']  = $request->requestDepartment->name;
					$results[$key]['requestProject']     = $request->requestProject->proyectName;
					$results[$key]['requestAccount']     = 'Varias';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']  = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment'] = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']    = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->reviewedEnterprise()->exists() ? 'Varias' : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;  
					$results[$key]['amount']             = $request->expenses->first()->total;
					$results[$key]['providerName']       = '';
					$results[$key]['reference']          = $request->expenses->first()->reference;
					$results[$key]['paymentMode']        = $request->expenses->first()->paymentMethod->method;
					if($request->expenses->first()->idEmployee!='')
					{
						$results[$key]['bankName']      = $request->expenses->first()->bankData->bank->description;
						$results[$key]['bankAccount']   = $request->expenses->first()->bankData->account.' ';
						$results[$key]['bankCard']      = $request->expenses->first()->bankData->cardNumber.' ';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = $request->expenses->first()->bankData->clabe.' ';
						$results[$key]['bankCurrency']  = $request->expenses->first()->currency;
						$results[$key]['bankAgreement'] = '';
					}
					else
					{
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = $request->expenses->first()->currency;
						$results[$key]['bankAgreement'] = '';
					}
					foreach ($request->expenses->first()->expensesDetail as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label) 
						{
							$tempLabels .= $label->description.', ';
						}
						$idExpensesDetail = $detail->idExpensesDetail;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idExpensesDetail) { $q->whereIn('label_detail_expenses.idlabels',$labelArray)->where('idExpensesDetail',$idExpensesDetail); })->count()>0))
						{
							$print[$request->folio]         = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $detail->taxPayment==1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = '';
							$tempArray['detailUnit']        = '';
							$tempArray['detailDescription'] = $detail->concept;
							$tempArray['detailAccount']     = $detail->accountR()->exists() ? $detail->accountR->account.' '.$detail->accountR->description.' ('.$detail->accountR->content.')' : $detail->account->account.' '.$detail->account->description.' ('.$detail->account->content.')';
							$tempArray['detailUnitPrice']   = '';
							$tempArray['detailSubtotal']    = $detail->amount;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept+=$tax->amount;
							}
							$tempArray['detailTaxesConcept']     = $taxesConcept;
							$tempArray['detailRetentionConcept'] = '';
							$tempArray['detailAmount']           = $detail->sAmount;
							$tempArray['detailLabel']            = $tempLabels;
							$totalResource                       = App\RequestModel::find($request->expenses->first()->resourceId)->resource->first()->total;
							$totalExpense                        = $request->expenses->first()->total;
							$tempArray['detailAmountResource']   = $totalResource;
							$tempArray['diferenceRequest']       = $totalExpense-$totalResource;
							if ($request->payment == 1 && $request->expenses->first()->reembolso>0) 
							{
								$tempArray['reembolso'] = 'Pagado';
							}
							elseif ($request->payment == 0 && $request->expenses->first()->reembolso>0) 
							{
								$tempArray['reembolso'] = 'No Pagado';
							}
							elseif ($request->expenses->first()->reembolso==0) 
							{
								$tempArray['reembolso'] = 'No Aplica';
							}
							else
							{
								$tempArray['reembolso'] = 'No Aplica';
							}
							if ($request->payment == 1 && $request->expenses->first()->reintegro>0 && $request->free == 1) 
							{
								$tempArray['reintegro'] = 'Comprobado';
							}
							elseif ($request->payment == 0 && $request->expenses->first()->reintegro>0 && $request->free == 0) 
							{
								$tempArray['reintegro'] = 'No Comprobado';
							}
							elseif ($request->payment == 1 && $request->expenses->first()->reintegro>0 && $request->free == 0) 
							{
								$tempArray['reintegro'] = 'No Comprobado';
							}
							elseif ($request->expenses->first()->reintegro==0) 
							{
								$tempArray['reintegro'] = 'No Aplica';
							}
							else
							{
								$tempArray['reintegro'] = 'No Aplica';
							}
							$results[$key]['concepts'][] = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->expenses->first()->total;
					$results[$key]['paymentTypeCurrency'] = $request->expenses->first()->currency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';   
					}
					$key++;             
					break;
				case 8:
					$results[$key]['folio']  = $request->folio;
					$results[$key]['status'] = $request->statusrequest->description;
					$results[$key]['kind']   = $request->requestkind->kind;
					$expense                 = App\RequestModel::join('expenses','request_models.folio','expenses.idFolio')->whereIn('status',[4,5,10,11,12])->where('resourceId',$request->folio)->first();
					$check                   = '';
					if ($expense != null)
					{
						$check = "SÍ";
					}
					else
					{
						$check = "NO";
					}
					$results[$key]['check']         = $check;
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->resource->first()->title.' '.$request->resource->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->requestEnterprise->name;
					$results[$key]['requestDirection']   = $request->requestDirection->name;
					$results[$key]['requestDepartment']  = $request->requestDepartment->name;
					$results[$key]['requestProject']     = $request->requestProject->proyectName;
					$results[$key]['requestAccount']     = 'Varias';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']  = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment'] = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']    = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->reviewedEnterprise()->exists() ? 'Varias' : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->resource->first()->total;
					$results[$key]['providerName']       = '';
					$results[$key]['reference']          = '';
					if($request->resource->first()->idpaymentMethod!='')
					{
						$results[$key]['paymentMode'] = $request->resource->first()->paymentMethod->method;
						if($request->resource->first()->idpaymentMethod==1)
						{
							$results[$key]['bankName']      = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->bank->description : '';
							$results[$key]['bankAccount']   = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->account.' ' : '';
							$results[$key]['bankCard']      = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->cardNumber.' ' : '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = $request->resource->first()->bankData()->exists() ? $request->resource->first()->bankData->clabe.' ' : '';
							$results[$key]['bankCurrency']  = $request->resource->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
						else
						{
							$results[$key]['bankName']      = '';
							$results[$key]['bankAccount']   = '';
							$results[$key]['bankCard']      = '';
							$results[$key]['bankBranch']    = '';
							$results[$key]['bankReference'] = '';
							$results[$key]['bankClabe']     = '';
							$results[$key]['bankCurrency']  = $request->resource->first()->currency;
							$results[$key]['bankAgreement'] = '';
						}
					}
					else
					{
						$results[$key]['bankName']      = 'Sin método de pago';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = $request->resource->first()->currency;
						$results[$key]['bankAgreement'] = '';
					}
					$tempLabels = '';
					foreach($request->labels as $label)
					{
						$tempLabels .= $label->description.', ';
					}
					$folio = $request->folio;
					if($labelArray=='' || ($labelArray!='' && $request->whereHas('labelsReport', function($q) use ($labelArray, $folio) { $q->whereIn('request_has_labels.labels_idlabels',$labelArray)->where('folio',$folio); })->count()>0))
					{
						$print[$request->folio] = $request->folio;
						foreach($request->resource->first()->resourceDetail as $detail)
						{
							$tempArray                           = array();
							$tempArray['taxPayment']             = '';
							$tempArray['detailQuantity']         = '';
							$tempArray['detailUnit']             = '';
							$tempArray['detailDescription']      = $detail->concept;
							$tempArray['detailAccount']          = $detail->accountsReview()->exists() ? $detail->accountsReview->account.' '.$detail->accountsReview->description.' ('.$detail->accountsReview->content.')' : $detail->accounts->account.' '.$detail->accounts->description.' ('.$detail->accounts->content.')';
							$tempArray['detailUnitPrice']        = '';
							$tempArray['detailSubtotal']         = '';
							$tempArray['detailTax']              = '';
							$tempArray['detailTaxesConcept']     = '';
							$tempArray['detailRetentionConcept'] = '';
							$tempArray['detailAmount']           = $detail->amount;
							$tempArray['detailLabel']            = $tempLabels;
							if ($check == "SÍ") 
							{
								$tempArray['detailAmountResource'] = $expense['total'];
								$tempArray['diferenceRequest']     = $expense['total']-$request->resource->first()->total;
							}
							else
							{
								$tempArray['detailAmountResource'] = '';
								$tempArray['diferenceRequest']     = '';
							}
							$tempArray['reembolso']      = '';
							$tempArray['reintegro']      = '';
							$results[$key]['concepts'][] = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->resource->first()->total;
					$results[$key]['paymentTypeCurrency'] = $request->resource->first()->currency;
					if ($request->paymentsRequest()->exists())
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';   
					}
					$key++;
					break;
				case 9:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->refunds->first()->title.' - '.$request->refunds->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->requestEnterprise->name;
					$results[$key]['requestDirection']   = $request->requestDirection->name;
					$results[$key]['requestDepartment']  = $request->requestDepartment->name;
					$results[$key]['requestProject']     = $request->requestProject->proyectName;
					$results[$key]['requestAccount']     = 'Varias';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']  = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment'] = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']    = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->reviewedProject()->exists() ? 'Varias' : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->refunds->first()->total;
					$results[$key]['providerName']       = '';
					$results[$key]['reference']          = $request->refunds->first()->reference;
					$results[$key]['paymentMode']        = $request->refunds->first()->paymentMethod->method;
					if($request->refunds->first()->idEmployee!='')
					{
						$results[$key]['bankName']      = $request->refunds->first()->bankData->bank->description;
						$results[$key]['bankAccount']   = $request->refunds->first()->bankData->account.' ';
						$results[$key]['bankCard']      = $request->refunds->first()->bankData->cardNumber.' ';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = $request->refunds->first()->bankData->clabe.' ';
						$results[$key]['bankCurrency']  = $request->refunds->first()->currency;
						$results[$key]['bankAgreement'] = '';
					}
					else
					{
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = $request->refunds->first()->currency;
						$results[$key]['bankAgreement'] = '';
					}
					foreach ($request->refunds->first()->refundDetail as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label) 
						{
							$tempLabels .= $label->description.', ';
						}
						$idRefundDetail = $detail->idRefundDetail;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idRefundDetail) { $q->whereIn('label_detail_refunds.idlabels',$labelArray)->where('idRefundDetail',$idRefundDetail); })->count()>0))
						{
							$print[$request->folio]         = $request->folio;
							$tempArray                      = array();
							$tempArray['taxPayment']        = $detail->taxPayment==1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']    = '';
							$tempArray['detailUnit']        = '';
							$tempArray['detailDescription'] = $detail->concept;
							$tempArray['detailAccount']     = $detail->accountR()->exists() ? $detail->accountR->account.' '.$detail->accountR->description.' ('.$detail->accountR->content.')' : $detail->account->account.' '.$detail->account->description.' ('.$detail->account->content.')';
							$tempArray['detailUnitPrice']   = '';
							$tempArray['detailSubtotal']    = $detail->amount;
							$tempArray['detailTax']         = $detail->tax;
							$taxesConcept                   = 0;
							foreach($detail->taxes as $tax)
							{
								$taxesConcept += $tax->amount;
							}
							$tempArray['detailTaxesConcept']     = $taxesConcept;
							$tempArray['detailRetentionConcept'] = '';
							$tempArray['detailAmount']           = $detail->sAmount;
							$tempArray['detailLabel']            = $tempLabels;
							$tempArray['detailAmountResource']   = '';
							$tempArray['diferenceRequest']       = '';
							$tempArray['reembolso']              = '';
							$tempArray['reintegro']              = '';
							$results[$key]['concepts'][]         = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->refunds->first()->total;
					$results[$key]['paymentTypeCurrency'] = $request->refunds->first()->currency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';
					}
					$key++;
					break;
				case 11:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->adjustment->first()->title.' - '.$request->adjustment->first()->datetitle;
					$results[$key]['numberOrder']   = $request->adjustment->first()->numberOrder;
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']               = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
					$results[$key]['elaborateUser']             = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']             = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']         = $request->requestEnterprise->name;
					$results[$key]['requestDirection']          = $request->requestDirection->name;
					$results[$key]['requestDepartment']         = $request->requestDepartment->name;
					$results[$key]['requestProject']            = $request->requestProject->proyectName;
					$results[$key]['requestAccount']            = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
					$results[$key]['reviewedUser']              = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']                = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise']        = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']         = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment']        = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']           = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']           = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
					$results[$key]['checkComment']              = $request->checkComment;
					$results[$key]['authorizedUser']            = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']             = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']          = $request->authorizeComment;
					$results[$key]['amount']                    = $request->adjustment->first()->amount;
					$results[$key]['providerName']              = '';
					$results[$key]['reference']                 = '';
					$results[$key]['paymentMode']               = '';
					$results[$key]['bankName']                  = '';
					$results[$key]['bankAccount']               = '';
					$results[$key]['bankCard']                  = '';
					$results[$key]['bankBranch']                = '';
					$results[$key]['bankReference']             = '';
					$results[$key]['bankClabe']                 = '';
					$results[$key]['bankCurrency']              = '';
					$results[$key]['bankAgreement']             = '';
					$print[$request->folio]                     = $request->folio;
					$tempArray                                  = array();
					$tempArray['taxPayment']                    = '';
					$tempArray['detailQuantity']                = '';
					$tempArray['detailUnit']                    = '';
					$tempArray['detailDescription']             = '';
					$tempArray['detailAccount']                 = '';
					$tempArray['detailUnitPrice']               = '';
					$tempArray['detailSubtotal']                = '';
					$tempArray['detailTax']                     = '';
					$tempArray['detailTaxesConcept']            = '';
					$tempArray['detailRetentionConcept']        = '';
					$tempArray['detailAmount']                  = '';
					$tempArray['detailLabel']                   = '';
					$tempArray['detailAmountResource']          = '';
					$tempArray['diferenceRequest']              = '';
					$tempArray['reembolso']                     = '';
					$tempArray['reintegro']                     = '';
					$results[$key]['concepts'][]                = $tempArray;
					$results[$key]['exchange_rate']             = '';
					$results[$key]['exchange_rate_description'] = '';
					$results[$key]['amount']                    = '';
					$key++;
					break;
				case 12:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->loanEnterprise->first()->title.' - '.$request->loanEnterprise->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']         = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
					$results[$key]['elaborateUser']       = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']       = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']   = $request->loanEnterprise->first()->enterpriseOrigin()->exists() ? $request->loanEnterprise->first()->enterpriseOrigin->name : '';
					$results[$key]['requestDirection']    = '';
					$results[$key]['requestDepartment']   = '';
					$results[$key]['requestProject']      = '';
					$results[$key]['requestAccount']      = $request->loanEnterprise->first()->accountOrigin()->exists() ? $request->loanEnterprise->first()->accountOrigin->account.' - '.$request->loanEnterprise->first()->accountOrigin->description : '';
					$results[$key]['reviewedUser']        = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']          = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise']  = $request->loanEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->loanEnterprise->first()->enterpriseOriginReviewed->name : '';
					$results[$key]['reviewedDirection']   = '';
					$results[$key]['reviewedDepartment']  = '';
					$results[$key]['reviewedProject']     = '';
					$results[$key]['reviewedAccount']     = $request->loanEnterprise->first()->accountOriginReviewed()->exists() ? $request->loanEnterprise->first()->accountOriginReviewed->account.' - '.$request->loanEnterprise->first()->accountOriginReviewed->description : '';
					$results[$key]['checkComment']        = $request->checkComment;
					$results[$key]['authorizedUser']      = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']       = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']    = $request->authorizeComment;
					$results[$key]['amount']              = $request->loanEnterprise->first()->amount;
					$results[$key]['providerName']        = '';
					$results[$key]['reference']           = '';
					$results[$key]['paymentMode']         = $request->loanEnterprise->first()->paymentMethod()->exists() ? $request->loanEnterprise->first()->paymentMethod->method : '';
					$results[$key]['bankName']            = '';
					$results[$key]['bankAccount']         = '';
					$results[$key]['bankCard']            = '';
					$results[$key]['bankBranch']          = '';
					$results[$key]['bankReference']       = '';
					$results[$key]['bankClabe']           = '';
					$results[$key]['bankCurrency']        = '';
					$results[$key]['bankAgreement']       = '';
					$print[$request->folio]               = $request->folio;
					$tempArray                            = array();
					$tempArray['taxPayment']              = '';
					$tempArray['detailQuantity']          = '';
					$tempArray['detailUnit']              = '';
					$tempArray['detailDescription']       = '';
					$tempArray['detailAccount']           = '';
					$tempArray['detailUnitPrice']         = '';
					$tempArray['detailSubtotal']          = '';
					$tempArray['detailTax']               = '';
					$tempArray['detailTaxesConcept']      = '';
					$tempArray['detailRetentionConcept']  = '';
					$tempArray['detailAmount']            = '';
					$tempArray['detailLabel']             = '';
					$tempArray['detailAmountResource']    = '';
					$tempArray['diferenceRequest']        = '';
					$tempArray['reembolso']               = '';
					$tempArray['reintegro']               = '';
					$results[$key]['concepts'][]          = $tempArray;
					$results[$key]['paymentTotal']        = $request->loanEnterprise->first()->amount;
					$results[$key]['paymentTypeCurrency'] = $request->loanEnterprise->first()->currency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';   
					}
					$key++;
					break;
				case 13:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->purchaseEnterprise->first()->title.' - '.$request->purchaseEnterprise->first()->datetitle;
					$results[$key]['numberOrder']   = $request->purchaseEnterprise->first()->numberOrder;
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->purchaseEnterprise->first()->enterpriseOrigin()->exists() ? $request->purchaseEnterprise->first()->enterpriseOrigin->name : '';
					$results[$key]['requestDirection']   = $request->purchaseEnterprise->first()->areaOrigin()->exists() ? $request->purchaseEnterprise->first()->areaOrigin->name : '';
					$results[$key]['requestDepartment']  = $request->purchaseEnterprise->first()->departmentOrigin()->exists() ? $request->purchaseEnterprise->first()->departmentOrigin->name : '';
					$results[$key]['requestProject']     = $request->purchaseEnterprise->first()->projectOrigin()->exists() ? $request->purchaseEnterprise->first()->projectOrigin->proyectName : '';
					$results[$key]['requestAccount']     = $request->purchaseEnterprise->first()->accountOrigin()->exists() ? $request->purchaseEnterprise->first()->accountOrigin->account.' - '.$request->purchaseEnterprise->first()->accountOrigin->description : '';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->purchaseEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->enterpriseOriginReviewed->name : '';
					$results[$key]['reviewedDirection']  = $request->purchaseEnterprise->first()->areaOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->areaOriginReviewed->name : '';
					$results[$key]['reviewedDepartment'] = $request->purchaseEnterprise->first()->departmentOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->departmentOriginReviewed->name : '';
					$results[$key]['reviewedProject']    = $request->purchaseEnterprise->first()->projectOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->projectOriginReviewed->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->purchaseEnterprise->first()->accountOriginReviewed()->exists() ? $request->purchaseEnterprise->first()->accountOriginReviewed->account.' - '.$request->purchaseEnterprise->first()->accountOriginReviewed->description : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->purchaseEnterprise->first()->amount;
					$results[$key]['providerName']       = '';
					$results[$key]['reference']          = $request->purchaseEnterprise->first()->reference;
					$results[$key]['paymentMode']        = $request->purchaseEnterprise->first()->paymentMethod->method;
					if($request->purchaseEnterprise->first()->idbanksAccounts!='')
					{
						$results[$key]['bankName']      = $request->purchaseEnterprise->first()->banks->bank->description;
						$results[$key]['bankAccount']   = $request->purchaseEnterprise->first()->banks->account.' ';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = $request->purchaseEnterprise->first()->banks->branch;
						$results[$key]['bankReference'] = $request->purchaseEnterprise->first()->banks->reference;
						$results[$key]['bankClabe']     = $request->purchaseEnterprise->first()->banks->clabe.' ';
						$results[$key]['bankCurrency']  = $request->purchaseEnterprise->first()->banks->currency;
						$results[$key]['bankAgreement'] = $request->purchaseEnterprise->first()->banks->agreement;
					}
					else
					{
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';
					}
					foreach ($request->purchaseEnterprise->first()->detailPurchaseEnterprise as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label)
						{
							$tempLabels .= $label->description.', ';
						}
						$idDetailPurchase = $detail->idPurchaseEnterpriseDetail;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idDetailPurchase) { $q->whereIn('purchase_enterprise_detail_labels.idlabels',$labelArray)->where('idPurchaseEnterpriseDetail',$idDetailPurchase); })->count()>0))
						{
							$print[$request->folio]              = $request->folio;
							$tempArray                           = array();
							$tempArray['taxPayment']             = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']         = $detail->quantity;
							$tempArray['detailUnit']             = $detail->unit;
							$tempArray['detailDescription']      = $detail->description;
							$tempArray['detailAccount']          = '';
							$tempArray['detailUnitPrice']        = $detail->unitPrice;
							$tempArray['detailSubtotal']         = $detail->subtotal;
							$tempArray['detailTax']              = $detail->tax;
							$tempArray['detailTaxesConcept']     = $detail->taxes()->sum('amount');
							$tempArray['detailRetentionConcept'] = $detail->retentions()->sum('amount');
							$tempArray['detailAmount']           = $detail->amount;
							$tempArray['detailLabel']            = $tempLabels;
							$tempArray['detailAmountResource']   = '';
							$tempArray['diferenceRequest']       = '';
							$tempArray['reembolso']              = '';
							$tempArray['reintegro']              = '';
							$results[$key]['concepts'][]         = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->purchaseEnterprise->first()->amount;
					$results[$key]['paymentTypeCurrency'] = $request->purchaseEnterprise->first()->typeCurrency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';
					}
					$key++;
					break;
				case 14:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->groups->first()->title.' - '.$request->groups->first()->datetitle;
					$results[$key]['numberOrder']   = $request->groups->first()->numberOrder;
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->groups->first()->enterpriseOrigin()->exists() ? $request->groups->first()->enterpriseOrigin->name : '';
					$results[$key]['requestDirection']   = $request->groups->first()->areaOrigin()->exists() ? $request->groups->first()->areaOrigin->name : '';
					$results[$key]['requestDepartment']  = $request->groups->first()->departmentOrigin()->exists() ? $request->groups->first()->departmentOrigin->name : '';
					$results[$key]['requestProject']     = $request->groups->first()->projectOrigin()->exists() ? $request->groups->first()->projectOrigin->proyectName : '';
					$results[$key]['requestAccount']     = $request->groups->first()->accountOrigin()->exists() ? $request->groups->first()->accountOrigin->account.' - '.$request->groups->first()->accountOrigin->description : '';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->groups->first()->enterpriseOriginReviewed()->exists() ? $request->groups->first()->enterpriseOriginReviewed->name : '';
					$results[$key]['reviewedDirection']  = $request->groups->first()->areaOriginReviewed()->exists() ? $request->groups->first()->areaOriginReviewed->name : '';
					$results[$key]['reviewedDepartment'] = $request->groups->first()->departmentOriginReviewed()->exists() ? $request->groups->first()->departmentOriginReviewed->name : '';
					$results[$key]['reviewedProject']    = $request->groups->first()->projectOriginReviewed()->exists() ? $request->groups->first()->projectOriginReviewed->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->groups->first()->accountOriginReviewed()->exists() ? $request->groups->first()->accountOriginReviewed->account.' - '.$request->groups->first()->accountOriginReviewed->description : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->groups->first()->amount;
					$results[$key]['providerName']       = $request->groups->first()->provider()->exists() ? $request->groups->first()->provider->businessName : '';
					$results[$key]['reference']          = $request->groups->first()->reference;
					$results[$key]['paymentMode']        = $request->groups->first()->paymentMethod->method;
					if($request->groups->first()->provider_has_banks_id!='')
					{
						$results[$key]['bankName']      = $request->groups->first()->bankData->bank->description;
						$results[$key]['bankAccount']   = $request->groups->first()->bankData->account.' ';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = $request->groups->first()->bankData->branch;
						$results[$key]['bankReference'] = $request->groups->first()->bankData->reference;
						$results[$key]['bankClabe']     = $request->groups->first()->bankData->clabe.' ';
						$results[$key]['bankCurrency']  = $request->groups->first()->bankData->currency;
						$results[$key]['bankAgreement'] = $request->groups->first()->bankData->agreement;
					}
					else
					{
						$results[$key]['bankName']      = '';
						$results[$key]['bankAccount']   = '';
						$results[$key]['bankCard']      = '';
						$results[$key]['bankBranch']    = '';
						$results[$key]['bankReference'] = '';
						$results[$key]['bankClabe']     = '';
						$results[$key]['bankCurrency']  = '';
						$results[$key]['bankAgreement'] = '';
					}
					foreach ($request->groups->first()->detailGroups as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label)
						{
							$tempLabels .= $label->description.', ';
						}
						$idgroupsDetail = $detail->idgroupsDetail;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idgroupsDetail) { $q->whereIn('groups_detail_labels.idlabels',$labelArray)->where('idgroupsDetail',$idgroupsDetail); })->count()>0))
						{
							$print[$request->folio]              = $request->folio;
							$tempArray                           = array();
							$tempArray['taxPayment']             = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']         = $detail->quantity;
							$tempArray['detailUnit']             = $detail->unit;
							$tempArray['detailDescription']      = $detail->description;
							$tempArray['detailAccount']          = '';
							$tempArray['detailUnitPrice']        = $detail->unitPrice;
							$tempArray['detailSubtotal']         = $detail->subtotal;
							$tempArray['detailTax']              = $detail->tax;
							$tempArray['detailTaxesConcept']     = $detail->taxes()->sum('amount');
							$tempArray['detailRetentionConcept'] = $detail->retentions()->sum('amount');
							$tempArray['detailAmount']           = $detail->amount;
							$tempArray['detailLabel']            = $tempLabels;
							$tempArray['detailAmountResource']   = '';
							$tempArray['diferenceRequest']       = '';
							$tempArray['reembolso']              = '';
							$tempArray['reintegro']              = '';
							$results[$key]['concepts'][]         = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->groups->first()->amount;
					$results[$key]['paymentTypeCurrency'] = $request->groups->first()->typeCurrency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';
					}
					$key++;
					break;
				case 15:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->movementsEnterprise->first()->title.' - '.$request->movementsEnterprise->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']         = $request->requestUser()->exists() ? $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name : '';
					$results[$key]['elaborateUser']       = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']       = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']   = $request->movementsEnterprise->first()->enterpriseOrigin()->exists() ? $request->movementsEnterprise->first()->enterpriseOrigin->name : '';
					$results[$key]['requestDirection']    = '';
					$results[$key]['requestDepartment']   = '';
					$results[$key]['requestProject']      = '';
					$results[$key]['requestAccount']      = $request->movementsEnterprise->first()->accountOrigin()->exists() ? $request->movementsEnterprise->first()->accountOrigin->account.' - '.$request->movementsEnterprise->first()->accountOrigin->description : '';
					$results[$key]['reviewedUser']        = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']          = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise']  = $request->movementsEnterprise->first()->enterpriseOriginReviewed()->exists() ? $request->movementsEnterprise->first()->enterpriseOriginReviewed->name : '';
					$results[$key]['reviewedDirection']   = '';
					$results[$key]['reviewedDepartment']  = '';
					$results[$key]['reviewedProject']     = '';
					$results[$key]['reviewedAccount']     = $request->movementsEnterprise->first()->accountOriginReviewed()->exists() ? $request->movementsEnterprise->first()->accountOriginReviewed->account.' - '.$request->movementsEnterprise->first()->accountOriginReviewed->description : '';
					$results[$key]['checkComment']        = $request->checkComment;
					$results[$key]['authorizedUser']      = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']       = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']    = $request->authorizeComment;
					$results[$key]['amount']              = $request->movementsEnterprise->first()->amount;
					$results[$key]['providerName']        = '';
					$results[$key]['reference']           = '';
					$results[$key]['paymentMode']         = $request->movementsEnterprise->first()->paymentMethod()->exists() ? $request->movementsEnterprise->first()->paymentMethod->method : '';
					$results[$key]['bankName']            = '';
					$results[$key]['bankAccount']         = '';
					$results[$key]['bankCard']            = '';
					$results[$key]['bankBranch']          = '';
					$results[$key]['bankReference']       = '';
					$results[$key]['bankClabe']           = '';
					$results[$key]['bankCurrency']        = '';
					$results[$key]['bankAgreement']       = '';
					$print[$request->folio]               = $request->folio;
					$tempArray                            = array();
					$tempArray['taxPayment']              = '';
					$tempArray['detailQuantity']          = '';
					$tempArray['detailUnit']              = '';
					$tempArray['detailDescription']       = '';
					$tempArray['detailAccount']           = '';
					$tempArray['detailUnitPrice']         = '';
					$tempArray['detailSubtotal']          = '';
					$tempArray['detailTax']               = '';
					$tempArray['detailTaxesConcept']      = '';
					$tempArray['detailRetentionConcept']  = '';
					$tempArray['detailAmount']            = '';
					$tempArray['detailLabel']             = '';
					$tempArray['detailAmountResource']    = '';
					$tempArray['diferenceRequest']        = '';
					$tempArray['reembolso']               = '';
					$tempArray['reintegro']               = '';
					$results[$key]['concepts'][]          = $tempArray;
					$results[$key]['paymentTotal']        = $request->movementsEnterprise->first()->amount;
					$results[$key]['paymentTypeCurrency'] = $request->movementsEnterprise->first()->currency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';
					}
					$key++;
					break;
				case 16:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->nominasReal->first()->title.' - '.$request->nominasReal->first()->datetitle;
					$results[$key]['numberOrder']   = '';
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']         = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']       = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']       = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']   = 'Varias';
					$results[$key]['requestDirection']    = '';
					$results[$key]['requestDepartment']   = '';
					$results[$key]['requestProject']      = '';
					$results[$key]['requestAccount']      = '';
					$results[$key]['reviewedUser']        = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']          = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise']  = '';
					$results[$key]['reviewedDirection']   = '';
					$results[$key]['reviewedDepartment']  = '';
					$results[$key]['reviewedProject']     = '';
					$results[$key]['reviewedAccount']     = '';
					$results[$key]['checkComment']        = $request->checkComment;
					$results[$key]['authorizedUser']      = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']       = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']    = $request->authorizeComment;
					$results[$key]['amount']              = $request->nominasReal->first()->amount;
					$results[$key]['providerName']        = '';
					$results[$key]['reference']           = '';
					$results[$key]['paymentMode']         = '';
					$results[$key]['bankName']            = '';
					$results[$key]['bankAccount']         = '';
					$results[$key]['bankCard']            = '';
					$results[$key]['bankBranch']          = '';
					$results[$key]['bankReference']       = '';
					$results[$key]['bankClabe']           = '';
					$results[$key]['bankCurrency']        = '';
					$results[$key]['bankAgreement']       = '';
					$tempArray                            = array();
					$tempArray['taxPayment']              = '';
					$tempArray['detailQuantity']          = '';
					$tempArray['detailUnit']              = '';
					$tempArray['detailDescription']       = '';
					$tempArray['detailAccount']           = '';
					$tempArray['detailUnitPrice']         = '';
					$tempArray['detailSubtotal']          = '';
					$tempArray['detailTax']               = '';
					$tempArray['detailTaxesConcept']      = '';
					$tempArray['detailRetentionConcept']  = '';
					$tempArray['detailAmount']            = '';
					$tempArray['detailLabel']             = '';
					$tempArray['detailAmountResource']    = '';
					$tempArray['diferenceRequest']        = '';
					$tempArray['reembolso']               = 'No Aplica';
					$tempArray['reintegro']               = 'No Aplica';
					$results[$key]['concepts'][]          = $tempArray;
					$results[$key]['paymentTotal']        = '';
					$results[$key]['paymentTypeCurrency'] = '';
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';
					}
					$key++;     
					break;
				case 17:
					$results[$key]['folio']         = $request->folio;
					$results[$key]['status']        = $request->statusrequest->description;
					$results[$key]['kind']          = $request->requestkind->kind;
					$results[$key]['check']         = '';
					$results[$key]['folioResource'] = '';
					$results[$key]['title']         = $request->purchaseRecord->title.' - '.$request->purchaseRecord->datetitle;
					$results[$key]['numberOrder']   = $request->purchaseRecord->numberOrder;
					if($request->idRequisition != '')
					{
						if($request->fromRequisition->code_wbs != '')
						{
							$results[$key]['wbs'] = $request->fromRequisition->wbs->code_wbs;
						}
						else
						{
							$results[$key]['wbs'] = '';
						}
						if($request->fromRequisition->code_edt != '')
						{
							$results[$key]['edt'] = $request->fromRequisition->edt->code.' '.$request->fromRequisition->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					elseif($request->code_wbs != '')
					{
						$results[$key]['wbs'] = $request->wbs->code_wbs;
						if($request->code_edt != '')
						{
							$results[$key]['edt'] = $request->edt->code.' '.$request->edt->description;
						}
						else
						{
							$results[$key]['edt'] = '';
						}
					}
					else
					{
						$results[$key]['wbs'] = '';
						$results[$key]['edt'] = '';
					}
					$results[$key]['requestUser']        = $request->requestUser->name.' '.$request->requestUser->last_name.' '.$request->requestUser->scnd_last_name;
					$results[$key]['elaborateUser']      = $request->elaborateUser->name.' '.$request->elaborateUser->last_name.' '.$request->elaborateUser->scnd_last_name;
					$results[$key]['elaborateDate']      = date('d-m-Y H:s',strtotime($request->fDate));
					$results[$key]['requestEnterprise']  = $request->requestEnterprise->name;
					$results[$key]['requestDirection']   = $request->requestDirection->name;
					$results[$key]['requestDepartment']  = $request->requestDepartment->name;
					$results[$key]['requestProject']     = $request->requestProject->proyectName;
					$results[$key]['requestAccount']     = $request->accounts->account.' '.$request->accounts->description.'('.$request->accounts->content.')';
					$results[$key]['reviewedUser']       = $request->reviewedUser()->exists() ? $request->reviewedUser->name.' '.$request->reviewedUser->last_name.' '.$request->reviewedUser->scnd_last_name : '';
					$results[$key]['reviewDate']         = $request->reviewDate!=null ? date('d-m-Y H:s',strtotime($request->reviewDate)) : '';
					$results[$key]['reviewedEnterprise'] = $request->reviewedEnterprise()->exists() ? $request->reviewedEnterprise->name : '';
					$results[$key]['reviewedDirection']  = $request->reviewedDirection()->exists() ? $request->reviewedDirection->name : '';
					$results[$key]['reviewedDepartment'] = $request->reviewedDepartment()->exists() ? $request->reviewedDepartment->name : '';
					$results[$key]['reviewedProject']    = $request->reviewedProject()->exists() ? $request->reviewedProject->proyectName : '';
					$results[$key]['reviewedAccount']    = $request->accountsReview()->exists() ? $request->accountsReview->account.' '.$request->accountsReview->description.'('.$request->accountsReview->content.')' : '';
					$results[$key]['checkComment']       = $request->checkComment;
					$results[$key]['authorizedUser']     = $request->authorizedUser()->exists() ? $request->authorizedUser->name.' '.$request->authorizedUser->last_name.' '.$request->authorizedUser->scnd_last_name : '';
					$results[$key]['authorizeDate']      = $request->authorizeDate!=null ? date('d-m-Y H:s',strtotime($request->authorizeDate)) : '';
					$results[$key]['authorizeComment']   = $request->authorizeComment;
					$results[$key]['amount']             = $request->purchaseRecord->total;
					$results[$key]['providerName']       = $request->purchaseRecord->provider;
					$results[$key]['reference']          = $request->purchaseRecord->reference;
					$results[$key]['paymentMode']        = $request->purchaseRecord->paymentMethod;
					$results[$key]['bankName']           = '';
					$results[$key]['bankAccount']        = '';
					$results[$key]['bankCard']           = '';
					$results[$key]['bankBranch']         = '';
					$results[$key]['bankReference']      = '';
					$results[$key]['bankClabe']          = '';
					$results[$key]['bankCurrency']       = '';
					$results[$key]['bankAgreement']      = '';
					foreach ($request->purchaseRecord->detailPurchase as $detail) 
					{
						$tempLabels = '';
						foreach ($detail->labelsReport as $label)
						{
							$tempLabels .= $label->description.', ';
						}
						$idDetailPurchase = $detail->id;
						if($labelArray=='' || ($labelArray!='' && $detail->whereHas('labelsReport', function($q) use ($labelArray, $idDetailPurchase) { $q->whereIn('purchase_record_labels.idLabel',$labelArray)->where('idPurchaseRecordDetail',$idDetailPurchase); })->count()>0))
						{
							$print[$request->folio]              = $request->folio;
							$tempArray                           = array();
							$tempArray['taxPayment']             = $request->taxPayment == 1 ? 'Fiscal' : 'No Fiscal';
							$tempArray['detailQuantity']         = $detail->quantity;
							$tempArray['detailUnit']             = $detail->unit;
							$tempArray['detailDescription']      = $detail->description;
							$tempArray['detailAccount']          = '';
							$tempArray['detailUnitPrice']        = $detail->unitPrice;
							$tempArray['detailSubtotal']         = $detail->subtotal;
							$tempArray['detailTax']              = $detail->tax;
							$tempArray['detailTaxesConcept']     = $detail->taxes()->sum('amount');;
							$tempArray['detailRetentionConcept'] = $detail->retentions()->sum('amount');;
							$tempArray['detailAmount']           = $detail->total;
							$tempArray['detailLabel']            = $tempLabels;
							$tempArray['detailAmountResource']   = '';
							$tempArray['diferenceRequest']       = '';
							$tempArray['reembolso']              = '';
							$tempArray['reintegro']              = '';
							$results[$key]['concepts'][]         = $tempArray;
						}
					}
					$results[$key]['paymentTotal']        = $request->purchaseRecord->total;
					$results[$key]['paymentTypeCurrency'] = $request->purchaseRecord->typeCurrency;
					if ($request->paymentsRequest()->exists()) 
					{
						$results[$key]['exchange_rate']             = $request->paymentsRequest->first()->exchange_rate;
						$results[$key]['exchange_rate_description'] = $request->paymentsRequest->first()->exchange_rate_description;
						$results[$key]['amount']                    = $request->paymentsRequest->sum('amount');
					}
					else
					{
						$results[$key]['exchange_rate']             = '';
						$results[$key]['exchange_rate_description'] = '';
						$results[$key]['amount']                    = '';   
					}
					$key++;
					break;
			}
		}
		Excel::create('Reporte Maestro', function($excel) use ($results,$print)
		{
			$excel->sheet('Reporte',function($sheet) use ($results,$print)
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
					'AA' => '"$"#,##0.00_-',
					'AF' => '@',
					'AE' => '@',
					'AH' => '@',
					'AI' => '@',
					'AJ' => '@',
					'AR' => '"$"#,##0.00_-',
					'AS' => '"$"#,##0.00_-',
					'AT' => '"$"#,##0.00_-',
					'AU' => '"$"#,##0.00_-',
					'AV' => '"$"#,##0.00_-',
					'AW' => '"$"#,##0.00_-',
					'AY' => '"$"#,##0.00_-',
					'AZ' => '"$"#,##0.00_-',
					'BC' => '"$"#,##0.00_-',
					'BE' => '"$"#,##0.00_-',
					'BF' => '"$"#,##0.00_-',
					'BG' => '"$"#,##0.00_-',
					'BH' => '"$"#,##0.00_-',
					'BI' => '"$"#,##0.00_-',
				));
				$sheet->mergeCells('A1:BH1');
				$sheet->mergeCells('A2:I2');
				$sheet->mergeCells('J2:Q2');
				$sheet->mergeCells('R2:Y2');
				$sheet->mergeCells('Z2:AO2');
				$sheet->mergeCells('AP2:AY2');
				$sheet->mergeCells('BA2:BF2');
				$sheet->mergeCells('BG2:BI2');
				$sheet->cell('A1:BI1', function($cells)
				{
					$cells->setBackground('#000000');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A2:BI2', function($cells)
				{
					$cells->setBackground('#1d353d');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A3:BI3', function($cells)
				{
					$cells->setBackground('#104f64');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A1:BI3', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
				});
				$sheet->row(1,['Reporte Maestro']);
				$sheet->row(2,['Datos de la solicitud','','','','','','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','','Datos de autorización','','','Datos la solicitud','','','','','','','','','','','','','Conceptos','','','','','','','','','','Etiquetas','','','','','','','Pagos realizados','','']);
				$sheet->row(3,['Folio','Estado de solicitud','Tipo','Comprobación','Folio de la solicitud de recurso','Título','Número de orden','WBS','EDT','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Comentarios','Autorizada por','Fecha de autorización','Comentarios','Monto Total de Solicitud','Razón Social','Referencia','Método de pago','Banco','Cuenta','Tarjeta','Sucursal','Referencia','CLABE','Moneda','Convenio','Fiscal/No Fiscal','Cantidad','Unidad','Concepto','Clasificación de gasto','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe Total','Etiquetas','Monto de la solicitud','Diferencia contra la solicitud','Reembolso','Reintegro','Total a pagar','Moneda','Tasa de Cambio','Descripción','Total Pagado']);
				$beginMerge = 3;
				foreach ($results as $result)
				{
					if (isset($result['concepts'])) 
					{
						$row   = [];
						$row[] = $result['folio'];
						$row[] = $result['status'];
						$row[] = $result['kind'];
						$row[] = $result['check'];
						$row[] = $result['folioResource'];
						$row[] = $result['title'];
						$row[] = $result['numberOrder'];
						$row[] = $result['wbs'];
						$row[] = $result['edt'];
						$row[] = $result['requestUser'];
						$row[] = $result['elaborateUser'];
						$row[] = $result['elaborateDate'];
						$row[] = $result['requestEnterprise'];
						$row[] = $result['requestDirection'];
						$row[] = $result['requestDepartment'];
						$row[] = $result['requestProject'];
						$row[] = $result['requestAccount'];
						$row[] = $result['reviewedUser'];
						$row[] = $result['reviewDate'];
						$row[] = $result['reviewedEnterprise'];
						$row[] = $result['reviewedDirection'];
						$row[] = $result['reviewedDepartment'];
						$row[] = $result['reviewedProject'];
						$row[] = $result['reviewedAccount'];
						$row[] = $result['checkComment'];
						$row[] = $result['authorizedUser'];
						$row[] = $result['authorizeDate'];
						$row[] = $result['authorizeComment'];
						$row[] = $result['amount'];
						$row[] = $result['providerName'];
						$row[] = $result['reference'];
						$row[] = $result['paymentMode'];
						$row[] = $result['bankName'];
						$row[] = $result['bankAccount'];
						$row[] = $result['bankCard'];
						$row[] = $result['bankBranch'];
						$row[] = $result['bankReference'];
						$row[] = $result['bankClabe'];
						$row[] = $result['bankCurrency'];
						$row[] = $result['bankAgreement'];
						$first = true;
						foreach($result['concepts'] as $concept)
						{
							if (!$first)
							{
								$row   = array();
								$row[] = $result['folio'];
								$row[] = $result['status'];
								$row[] = $result['kind'];
								$row[] = $result['check'];
								$row[] = $result['folioResource'];
								$row[] = $result['title'];
								$row[] = $result['numberOrder'];
								$row[] = $result['wbs'];
								$row[] = $result['edt'];
								$row[] = $result['requestUser'];
								$row[] = $result['elaborateUser'];
								$row[] = $result['elaborateDate'];
								$row[] = $result['requestEnterprise'];
								$row[] = $result['requestDirection'];
								$row[] = $result['requestDepartment'];
								$row[] = $result['requestProject'];
								$row[] = $result['requestAccount'];
								$row[] = $result['reviewedUser'];
								$row[] = $result['reviewDate'];
								$row[] = $result['reviewedEnterprise'];
								$row[] = $result['reviewedDirection'];
								$row[] = $result['reviewedDepartment'];
								$row[] = $result['reviewedProject'];
								$row[] = $result['reviewedAccount'];
								$row[] = $result['checkComment'];
								$row[] = $result['authorizedUser'];
								$row[] = $result['authorizeDate'];
								$row[] = $result['authorizeComment'];
								$row[] = $result['amount'];
								$row[] = $result['providerName'];
								$row[] = $result['reference'];
								$row[] = $result['paymentMode'];
								$row[] = $result['bankName'];
								$row[] = $result['bankAccount'];
								$row[] = $result['bankCard'];
								$row[] = $result['bankBranch'];
								$row[] = $result['bankReference'];
								$row[] = $result['bankClabe'];
								$row[] = $result['bankCurrency'];
								$row[] = $result['bankAgreement'];
							}
							else
							{
								$first = false;
								$beginMerge++;
							}
							$row[] = $concept['taxPayment'];
							$row[] = $concept['detailQuantity'];    
							$row[] = $concept['detailUnit'];        
							$row[] = $concept['detailDescription']; 
							$row[] = $concept['detailAccount'];     
							$row[] = $concept['detailUnitPrice'];   
							$row[] = $concept['detailSubtotal'];
							$row[] = $concept['detailTax'];     
							$row[] = $concept['detailTaxesConcept'];        
							$row[] = $concept['detailRetentionConcept'];    
							$row[] = $concept['detailAmount'];              
							$row[] = $concept['detailLabel'];
							$row[] = $concept['detailAmountResource'];
							$row[] = $concept['diferenceRequest'];
							$row[] = $concept['reembolso'];
							$row[] = $concept['reintegro'];
							if (array_key_exists('paymentTotal', $result))
							{
								$row[] = $result['paymentTotal'];
							}
							else
							{
								$row[] = '';
							}
							if (array_key_exists('paymentTypeCurrency', $result))
							{
								$row[] = $result['paymentTypeCurrency'];
							}
							else
							{
								$row[] = '';
							}
							if (array_key_exists('exchange_rate', $result))
							{
								$row[] = $result['exchange_rate'];
							}
							else
							{
								$row[] = '';
							}
							if (array_key_exists('exchange_rate', $result))
							{
								$row[] = $result['exchange_rate_description'];
							}
							else
							{
								$row[] = '';
							}
							if (array_key_exists('exchange_rate', $result))
							{
								$row[] = $result['amount'];
							}
							else
							{
								$row[] = '';
							}
							$sheet->appendRow($row);
						}
					}
				}
			});
		})->export('xls');
	}
}
