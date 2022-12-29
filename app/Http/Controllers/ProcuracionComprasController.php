<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Ilovepdf\CompressTask;
use PDF;
use Excel;
use App\Functions\Files;

class ProcuracionComprasController extends Controller
{
	private $module_id = 252;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseCreate()
	{
		if (Auth::user()->module->where('id',253)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.procuracion_compras.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 253
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseSave(Request $request)
	{
		if (Auth::user()->module->where('id',253)->count() > 0) 
		{
			$status = $request->status != "" ? $request->status : 24;


			$buyer = App\CatBuyer::where('name',$request->buyer)->first();
			if(!$buyer)
			{
				$buyer = App\CatBuyer::create(['name' => $request->buyer]);
			}

			$expeditor = App\CatExpeditor::where('name',$request->expeditor)->first();
			if(!$expeditor)
			{
				$expeditor = App\CatExpeditor::create(['name' => $request->expeditor]);
			} 

			$new_purchase = App\ProcurementPurchase::create(
			[
				'account'			=> $request->account,
				'numberOrder'		=> $request->numberOrder,
				'numberCO'			=> $request->numberCO,
				'descriptionShort'	=> $request->descriptionShort,
				'status'			=> $status,
				'date_request'		=> $request->date_request,
				'date_obra'			=> $request->date_obra,
				'date_promise'		=> $request->date_promise,
				'date_close'		=> $request->date_close,
				'destination'		=> $request->destination,
				'site'				=> $request->site,
				'code_wbs'			=> $request->code_wbs,
				'type_currency'		=> $request->type_currency,
				'descriptionLong'	=> $request->descriptionLong,
				'provider'			=> $request->provider,
				'ubicationProvider'	=> $request->ubicationProvider,
				'contactProvider'	=> $request->contactProvider,
				'phoneProvider'		=> $request->phoneProvider,
				'emailProvider'		=> $request->emailProvider,
				'total_request'		=> $request->total_request,
				'project_id'		=> $request->project_id,
				'idElaborate'		=> Auth::user()->id,
				'idKind'			=> 21,
				'buyer'				=> $buyer->name,
				'expeditor'			=> $expeditor->name,
				'engineer'			=> $request->engineer,
				'contract' 			=> $request->contract,
			]);

			if (isset($request->total_concept) && count($request->total_concept)>0) 
			{
				for ($i=0; $i < count($request->total_concept); $i++)
				{ 
					$new_detail = App\ProcurementPurchaseDetail::create(
					[
						'part'					=> $request->part[$i],
						'code'					=> $request->code[$i],
						'unit'					=> $request->unit[$i],
						'description'			=> $request->description[$i],
						'quantity'				=> $request->quantity[$i],
						'price'					=> $request->price[$i],
						'total_concept'			=> $request->total_concept[$i],
						'type_currency'			=> $request->type_currency_concept[$i],
						'date_one'				=> $request->date_one[$i],
						'date_two'				=> $request->date_two[$i],
						'idprocurementPurchase'	=> $new_purchase->id,
					]);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++)
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$new_purchase->id);
					$new_detail 	= App\ProcurementPurchaseDocuments::create(
					[
						'name'					=> $request->nameDocument[$i],
						'path'					=> $new_file_name,
						'users_id'				=> Auth::user()->id,
						'idprocurementPurchase'	=> $new_purchase->id,
					]);
				}
			}

			if (isset($request->remark) && isset($request->date_remark) && count($request->remark)>0 && count($request->date_remark)>0) 
			{
				for ($i=0; $i < count($request->remark); $i++)
				{ 
					if ($request->remark[$i] != "" && $request->date_remark[$i] != "") 
					{
						$new_detail = App\ProcurementPurchaseRemarks::create(
						[
							'remark'				=> $request->remark[$i],
							'date'					=> $request->date_remark[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $new_purchase->id,
						]);
					}
				}
			}

			if (isset($request->seq_num_t) && count($request->seq_num_t)>0) 
			{
				for ($i=0; $i < count($request->seq_num_t); $i++) 
				{ 
					if ($request->milestone_t[$i] != "") 
					{
						$new_milestone = App\ProcurementMilestone::create(
						[
							'seq_num'				=> $request->seq_num_t[$i],
							'milestone'				=> $request->milestone_t[$i],
							'schedule'				=> $request->schedule_t[$i],
							'status'				=> $request->status_milestone_t[$i],
							'complete_status'		=> $request->complete_status_t[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $new_purchase->id,
						]);
					}
				}
			}

			$new_history = App\ProcurementHistory::create(
			[
				'folio'				=> $new_purchase->id,
				'folio_original'	=> $new_purchase->id,
				'users_id'			=> Auth::user()->id,
			]);

			$alert 	= "swal('', 'Solicitud Guardada Exitosamente', 'success');";
			return redirect()->route('procurement-purchases.purchase-edit',$new_purchase->id)->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseFollow(Request $request)
	{
		if (Auth::user()->module->where('id',254)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
								->where(function ($query)
								{
									if (Auth::user()->id != 43) 
									{
										$query->where('idElaborate',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
								{
									if($account != "")
									{
										$query->whereIn('account',$account);
									}
									if($numberOrder != "")
									{
										$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
									}
									if($status != "")
									{
										$query->whereIn('status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('id','DESC')
								->paginate(10);

			return view('administracion.procuracion_compras.busqueda',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 254,
					'requests'			=> $requests,
					'account'			=> $account,
					'numberOrder'		=> $numberOrder,
					'status'			=> $status,
					'mindate'			=> $mindate,
					'maxdate'			=> $maxdate,
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseEdit(App\ProcurementPurchase $request)
	{
		if (Auth::user()->module->where('id',254)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.procuracion_compras.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 254,
					'request'	=> $request
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseUpdate(App\ProcurementPurchase $purchase,Request $request)
	{
		if (Auth::user()->module->where('id',254)->count() > 0) 
		{

			$status = $request->status != "" ? $request->status : 24;

			$purchase->status	= 24;
			$purchase->visible	= 0;
			$purchase->save();


			$buyer = App\CatBuyer::where('name',$request->buyer)->first();
			if(!$buyer)
			{
				$buyer = App\CatBuyer::create(['name' => $request->buyer]);
			}

			$expeditor = App\CatExpeditor::where('name',$request->expeditor)->first();
			if(!$expeditor)
			{
				$expeditor = App\CatExpeditor::create(['name' => $request->expeditor]);
			} 

			$new_purchase = App\ProcurementPurchase::create(
			[
				'account'			=> $request->account,
				'numberOrder'		=> $request->numberOrder,
				'numberCO'			=> $request->numberCO,
				'descriptionShort'	=> $request->descriptionShort,
				'status'			=> $status,
				'date_request'		=> $request->date_request,
				'date_obra'			=> $request->date_obra,
				'date_promise'		=> $request->date_promise,
				'date_close'		=> $request->date_close,
				'destination'		=> $request->destination,
				'site'				=> $request->site,
				'code_wbs'			=> $request->code_wbs,
				'type_currency'		=> $request->type_currency,
				'descriptionLong'	=> $request->descriptionLong,
				'provider'			=> $request->provider,
				'ubicationProvider'	=> $request->ubicationProvider,
				'contactProvider'	=> $request->contactProvider,
				'phoneProvider'		=> $request->phoneProvider,
				'emailProvider'		=> $request->emailProvider,
				'total_request'		=> $request->total_request,
				'project_id'		=> $request->project_id,
				'idElaborate'		=> Auth::user()->id,
				'idKind'			=> 21,
				'buyer'				=> $buyer->name,
				'expeditor'			=> $expeditor->name,
				'engineer'			=> $request->engineer,
				'contract' 			=> $request->contract,
			]);

			if (isset($request->total_concept) && count($request->total_concept)>0) 
			{
				for ($i=0; $i < count($request->total_concept); $i++)
				{ 
					$new_detail = App\ProcurementPurchaseDetail::create(
					[
						'part'					=> $request->part[$i],
						'code'					=> $request->code[$i],
						'unit'					=> $request->unit[$i],
						'description'			=> $request->description[$i],
						'quantity'				=> $request->quantity[$i],
						'price'					=> $request->price[$i],
						'total_concept'			=> $request->total_concept[$i],
						'type_currency'			=> $request->type_currency_concept[$i],
						'date_one'				=> $request->date_one[$i],
						'date_two'				=> $request->date_two[$i],
						'idprocurementPurchase'	=> $new_purchase->id,
					]);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++)
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$new_purchase->id);
					$new_detail 	= App\ProcurementPurchaseDocuments::create(
					[
						'name'					=> $request->nameDocument[$i],
						'path'					=> $new_file_name,
						'users_id'				=> Auth::user()->id,
						'idprocurementPurchase'	=> $new_purchase->id,
					]);
				}
			}

			if (isset($request->remark) && isset($request->date_remark) && count($request->remark)>0 && count($request->date_remark)>0) 
			{
				for ($i=0; $i < count($request->remark); $i++)
				{ 
					if ($request->remark[$i] != "" && $request->date_remark[$i] != "") 
					{
						$new_detail = App\ProcurementPurchaseRemarks::create(
						[
							'remark'				=> $request->remark[$i],
							'date'					=> $request->date_remark[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $new_purchase->id,
						]);
					}
				}
			}

			if (isset($request->seq_num_t) && count($request->seq_num_t)>0) 
			{
				for ($i=0; $i < count($request->seq_num_t); $i++) 
				{ 
					if ($request->milestone_t[$i] != "") 
					{
						$new_milestone = App\ProcurementMilestone::create(
						[
							'seq_num'				=> $request->seq_num_t[$i],
							'milestone'				=> $request->milestone_t[$i],
							'schedule'				=> $request->schedule_t[$i],
							'status'				=> $request->status_milestone_t[$i],
							'complete_status'		=> $request->complete_status_t[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $new_purchase->id,
						]);
					}
				}
			}

			if ($purchase->history()->exists()) 
			{
				$folio_original = $purchase->history->first()->folio_original;
			}
			else
			{
				$folio_original = $purchase->id;
			}

			$new_history = App\ProcurementHistory::create(
			[
				'folio'				=> $new_purchase->id,
				'folio_original'	=> $folio_original,
				'users_id'			=> Auth::user()->id
			]);

			/*

			$update_request->idEnterprise	= $request->enterprise_id;
			$update_request->idProject		= $request->project_id;
			$update_request->kind			= 21;
			$update_request->status			= $status;
			$update_request->idRequest		= Auth::user()->id;
			$update_request->save();

			$update_request->procurementPurchase->account			= $request->account;
			$update_request->procurementPurchase->numberOrder		= $request->numberOrder;
			$update_request->procurementPurchase->numberCO			= $request->numberCO;
			$update_request->procurementPurchase->descriptionShort	= $request->descriptionShort;
			$update_request->procurementPurchase->status			= $status;
			$update_request->procurementPurchase->date_request		= $request->date_request;
			$update_request->procurementPurchase->date_obra			= $request->date_obra;
			$update_request->procurementPurchase->date_promise		= $request->date_promise;
			$update_request->procurementPurchase->date_close		= $request->date_close;
			$update_request->procurementPurchase->destination		= $request->destination;
			$update_request->procurementPurchase->code_wbs			= $request->code_wbs;
			$update_request->procurementPurchase->type_currency		= $request->type_currency;
			$update_request->procurementPurchase->descriptionLong	= $request->descriptionLong;
			$update_request->procurementPurchase->provider			= $request->provider;
			$update_request->procurementPurchase->ubicationProvider	= $request->ubicationProvider;
			$update_request->procurementPurchase->contactProvider	= $request->contactProvider;
			$update_request->procurementPurchase->phoneProvider		= $request->phoneProvider;
			$update_request->procurementPurchase->emailProvider		= $request->emailProvider;
			$update_request->procurementPurchase->total_request		= $request->total_request;
			$update_request->procurementPurchase->save();

			if (isset($request->delete) && count($request->delete)) 
			{
				App\ProcurementPurchaseDetail::destroy($request->delete);
			}

			if (isset($request->total_concept) && count($request->total_concept)>0) 
			{
				for ($i=0; $i < count($request->total_concept); $i++)
				{ 
					$new_detail = App\ProcurementPurchaseDetail::create(
					[
						'part'					=> $request->part[$i],
						'code'					=> $request->code[$i],
						'unit'					=> $request->unit[$i],
						'description'			=> $request->description[$i],
						'quantity'				=> $request->quantity[$i],
						'price'					=> $request->price[$i],
						'total_concept'			=> $request->total_concept[$i],
						'type_currency'			=> $request->type_currency_concept[$i],
						'date_one'				=> $request->date_one[$i],
						'date_two'				=> $request->date_two[$i],
						'idprocurementPurchase'	=> $update_request->procurementPurchase->id,
					]);
				}
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++)
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$update_request->folio);
					$new_detail 	= App\ProcurementPurchaseDocuments::create(
					[
						'name'					=> $request->nameDocument[$i],
						'path'					=> $new_file_name,
						'users_id'				=> Auth::user()->id,
						'idprocurementPurchase'	=> $update_request->procurementPurchase->id,
					]);
				}
			}

			if (isset($request->remark) && isset($request->date_remark) && count($request->remark)>0 && count($request->date_remark)>0) 
			{
				for ($i=0; $i < count($request->remark); $i++)
				{ 
					if ($request->remark[$i] != "" && $request->date_remark[$i] != "") 
					{
						$new_detail = App\ProcurementPurchaseRemarks::create(
						[
							'remark'				=> $request->remark[$i],
							'date'					=> $request->date_remark[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $update_request->procurementPurchase->id,
						]);
					}
				}
			}
			*/

			$alert 	= "swal('', 'Solicitud Enviada Exitosamente', 'success');";
			return redirect()->route('procurement-purchases.purchase-edit',$new_purchase->id)->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseDownload(App\ProcurementPurchase $request)
	{
		if (Auth::user()->module->whereIn('id',[254,255,256,257])->count() > 0) 
		{
			if ($request != "")
			{
				$pdf = \App::make('dompdf.wrapper');
				$pdf->getDomPDF()->set_option("enable_php", true);
				$pdf->loadView('administracion.procuracion_compras.pdf.orden',['request'=>$request])->setPaper('a4');
				
				return $pdf->download('orden.pdf');
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function saveRemarks(App\ProcurementPurchase $purchase, Request $request)
	{
		if (Auth::user()->module->where('id',254)->count() > 0) 
		{
			if (isset($request->remark) && isset($request->date_remark) && count($request->remark)>0 && count($request->date_remark)>0) 
			{
				for ($i=0; $i < count($request->remark); $i++)
				{ 
					if ($request->remark[$i] != "" && $request->date_remark[$i] != "") 
					{
						$new_detail = App\ProcurementPurchaseRemarks::create(
						[
							'remark'				=> $request->remark[$i],
							'date'					=> $request->date_remark[$i],
							'users_id'				=> Auth::user()->id,
							'idprocurementPurchase'	=> $purchase->id,
						]);
					}
				}
			}
			$alert 	= "swal('', 'Observaciones Guardadas Exitosamente', 'success');";
			return redirect()->route('procurement-purchases.purchase-edit',$purchase->id)->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseExport(Request $request)
	{
		if (Auth::user()->module->where('id',254)->count() > 0) 
		{
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
								->where(function ($query)
								{
									if (Auth::user()->id != 43) 
									{
										$query->where('idElaborate',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
								{
									if($account != "")
									{
										$query->whereIn('account',$account);
									}
									if($numberOrder != "")
									{
										$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
									}
									if($status != "")
									{
										$query->whereIn('status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('id','DESC')
								->get();

			$pdf = \App::make('dompdf.wrapper');
			$pdf->getDomPDF()->set_option("enable_php", true);
			$pdf->loadView('administracion.procuracion_compras.pdf.ordenes',['requests'=>$requests])->setPaper('a4');
			
			return $pdf->download('ordenes.pdf');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function purchaseView(App\ProcurementPurchase $request)
	{
		if (Auth::user()->module->where('id',254)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.procuracion_compras.ver',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 254,
					'request'	=> $request
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function viewDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$request = App\ProcurementPurchase::find($request->folio);
			if ($request != "") 
			{
				return view('administracion.procuracion_compras.details',['request'=>$request]);
			}
		}
	}

	public function cancelPurchase(App\ProcurementPurchase $request)
	{
		if (Auth::user()->module->where('id',254)->count()>0) 
		{
			$request->status = 25;
			$request->save();

			$alert 	= "swal('', 'Orden de Compra Cancelada Exitosamente', 'success');";
			return redirect()->route('procurement-purchases.purchase-follow')->with('alert',$alert);
		}
	}

	public function warehouse(Request $request)
	{
		if (Auth::user()->module->where('id',255)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
								->where('full_load_warehouse',0)
								->where('status',26)
								->where(function ($query)
								{
									if (Auth::user()->id != 43) 
									{
										$query->where('idElaborate',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
								{
									if($account != "")
									{
										$query->whereIn('account',$account);
									}
									if($numberOrder != "")
									{
										$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
									}
									if($status != "")
									{
										$query->whereIn('status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('id','DESC')
								->paginate(10);
			return view('administracion.procuracion_compras.almacen',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 255,
					'requests'			=> $requests,
					'account'			=> $account,
					'numberOrder'		=> $numberOrder,
					'status'			=> $status,
					'mindate'			=> $mindate,
					'maxdate'			=> $maxdate,
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function warehouseCreate(App\ProcurementPurchase $purchase)
	{
		if (Auth::user()->module->where('id',255)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.procuracion_compras.almacen_alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 255,
					'purchase'	=> $purchase
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function warehouseSave(Request $request)
	{
		if (Auth::user()->module->where('id',255)->count()>0)
		{
			$purchase = App\ProcurementPurchase::find($request->folio);
			if (isset($request->pending_items) && count($request->pending_items)>0) 
			{
				$purchase->full_load_warehouse = 0;
			}
			else
			{
				$purchase->full_load_warehouse = 1;
			}
			$purchase->save();

			if (isset($request->id_detail) && count($request->id_detail)>0) 
			{
				for ($i=0; $i < count($request->id_detail); $i++) 
				{ 

					$detail = App\ProcurementPurchaseDetail::find($request->id_detail[$i]);
					$detail->warehouseStatus = 1;
					$detail->save();

					$warehouse = App\ProcurementWarehouse::create(
					[
						'description'					=> $request->description[$i],
						'measure'						=> $request->measure[$i],
						'code_mat'						=> $request->code_mat[$i],
						'quantity_not_damaged'			=> $request->quantity_not_damaged[$i],
						'damaged'						=> $request->damaged[$i],
						'quantity'						=> $request->quantity[$i],
						'unit_price'					=> $request->unit_price[$i],
						'total_art'						=> $request->total_art[$i],
						'date_entry'					=> $request->date_entry[$i],
						'commentaries'					=> $request->commentaries[$i],
						'idProcurementPurchaseDetail'	=> $request->id_detail[$i],
						'users_id'						=> Auth::user()->id,
					]);
				}
			}
			$alert 	= "swal('', 'Artículos Cargados Exitosamente', 'success');";
			return redirect()->route('procurement-purchases.warehouse')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function report(Request $request)
	{
		if (Auth::user()->module->where('id',256)->count()>0)
		{
			$data				= App\Module::find($this->module_id);
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
								->whereIn('status',[24,26])
								->where(function ($query)
								{
									if (Auth::user()->id != 43) 
									{
										$query->where('idElaborate',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
								{
									if($account != "")
									{
										$query->whereIn('account',$account);
									}
									if($numberOrder != "")
									{
										$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
									}
									if($status != "")
									{
										$query->whereIn('status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('id','DESC')
								->paginate(10);
			return view('administracion.procuracion_compras.reportes',
				[
					'id'				=> $data['father'],
					'title'				=> $data['name'],
					'details'			=> $data['details'],
					'child_id'			=> $this->module_id,
					'option_id'			=> 256,
					'requests'			=> $requests,
					'account'			=> $account,
					'numberOrder'		=> $numberOrder,
					'status'			=> $status,
					'mindate'			=> $mindate,
					'maxdate'			=> $maxdate,
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function reportView(App\ProcurementPurchase $request)
	{
		if (Auth::user()->module->where('id',256)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.procuracion_compras.ver',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 256,
					'request'	=> $request
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function reportDTR(Request $request)
	{
		if (Auth::user()->module->where('id',256)->count()>0) 
		{
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
								->where('status',24)
								->where(function ($query)
								{
									if (Auth::user()->id != 43) 
									{
										$query->where('idElaborate',Auth::user()->id);
									}
								})
								->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
								{
									if($account != "")
									{
										$query->whereIn('account',$account);
									}
									if($numberOrder != "")
									{
										$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
									}
									if($status != "")
									{
										$query->whereIn('status',$status);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
									}
								})
								->orderBy('id','DESC')
								->get();
			if($requests->count()>0)
			{
				$projects 	= App\Project::whereIn('idproyect',$requests->pluck('project_id'))
							->get();
				$pdf  	= \App::make('dompdf.wrapper');

				$pdf->getDomPDF()->set_option("enable_php", true);

				

				$pdf->loadView('administracion.procuracion_compras.pdf.dtr',
					[
						'requests' => $requests, 
						'projects' => $projects
					]);

				return $pdf->download('Reporte_DTR.pdf');
			}
			else
			{
				$alert 	= "swal('', 'No hay solicitudes en estado «Liberado» con los filtros aplicados.', 'error');";
				return redirect()->route('procurement-purchases.report')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('error');
		}
	}

	public function reportEPSR(Request $request)
	{
		if (Auth::user()->module->where('id',256)->count()>0) 
		{
			
		}
		else
		{
			return redirect('error');
		}
	}

	public function reportMSR(Request $request)
	{
		if (Auth::user()->module->where('id',256)->count()>0) 
		{
			$account			= $request->account;
			$numberOrder		= $request->numberOrder;
			$status				= $request->status;
			$mindate			= $request->mindate!='' ? $request->mindate : null;
			$maxdate			= $request->maxdate!='' ? $request->maxdate : null;
			$requests 			= App\ProcurementPurchase::where('visible',1)
				->where('status',26)
				->where(function ($query)
				{
					if (Auth::user()->id != 43) 
					{
						$query->where('idElaborate',Auth::user()->id);
					}
				})
				->where(function ($query) use ($account, $mindate, $maxdate, $numberOrder, $status)
				{
					if($account != "")
					{
						$query->whereIn('account',$account);
					}
					if($numberOrder != "")
					{
						$query->where('numberOrder','LIKE','%'.$numberOrder.'%');
					}
					if($status != "")
					{
						$query->whereIn('status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('date_request',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('id','DESC')
				->get();
			if($requests->count() > 0)
			{
				$projects = App\Project::whereIn('idproyect',$requests->pluck('project_id'))->get();
				$pdf = \App::make('dompdf.wrapper');
				$pdf->getDomPDF()->set_option("enable_php", true);
				$pdf->loadView('administracion.procuracion_compras.pdf.msr',['requests' => $requests, 'projects' => $projects])->setPaper('a4', 'landscape');
				return $pdf->download('reporte_msr.pdf');
			}
			else
			{
				$alert 	= "swal('', 'No hay solicitudes en estado «Liberado» con los filtros aplicados.', 'error');";
				return redirect()->route('procurement-purchases.report')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('error');
		}
	}

	public function warehouseSearch(Request $request)
	{
		if (Auth::user()->module->where('id',257)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$description	= $request->description;
			$code			= $request->code;
			$mindate		= $request->mindate;
			$maxdate		= $request->maxdate;
			$measure		= $request->measure;
			$inventory 		= App\ProcurementWarehouse::where(function($query) use ($description,$code,$mindate,$maxdate,$measure)
							{
								if($description != "")
								{
									$query->where('description',$description);
								}
								if($code != "")
								{
									$query->where('code_mat',$code);
								}
								if($measure != "")
								{
									$query->where('measure',$measure);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('date_entry',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}

							})
							->paginate(10);

			return view('administracion.procuracion_compras.inventario',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 257,
					'inventory'		=> $inventory,
					'description'	=> $description,
					'code'			=> $code,
					'mindate'		=> $mindate,
					'maxdate'		=> $maxdate,
					'measure'		=> $measure,
				]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function warehouseExport(Request $request)
	{
		$description	= $request->description;
		$code			= $request->code;
		$mindate		= $request->mindate;
		$maxdate		= $request->maxdate;
		$measure		= $request->measure;
		$inventory 		= App\ProcurementWarehouse::where(function($query) use ($description,$code,$mindate,$maxdate,$measure)
						{
							if($description != "")
							{
								$query->where('description',$description);
							}
							if($code != "")
							{
								$query->where('code_mat',$code);
							}
							if($measure != "")
							{
								$query->where('measure',$measure);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('date_entry',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}

						})
						->get();

		Excel::create('Inventario',function($excel) use($inventory)
		{
			$excel->sheet('Inventario',function($sheet) use($inventory)
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
				$sheet->mergeCells('A1:J1');
				$sheet->cell('A1:J1', function($cells)
				{
					$cells->setBackground('#000000');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A2:J2', function($cells)
				{
					$cells->setBackground('#1d353d');
					$cells->setFontColor('#ffffff');
				});
				$sheet->cell('A1:J2', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
				});
				$sheet->row(1,['CONSORCIO IDINSA-PROYECTA']);
				$sheet->row(2,['Código','Descripción','Medida','Cantidad Sin Dañar','Cantidad Dañada','Cantidad Recibida','Precio Unitario','Total','Fecha Entrada','Comentarios']);
				foreach($inventory as $inv)
				{
					$row 	= [];
					$row[]	= $inv->code_mat;
					$row[] 	= $inv->description;
					$row[] 	= $inv->measure;
					$row[] 	= $inv->quantity_not_damaged;
					$row[] 	= $inv->damaged;
					$row[] 	= $inv->quantity;
					$row[] 	= $inv->unit_price;
					$row[] 	= $inv->total_art;
					$row[] 	= $inv->date_entry;
					$row[] 	= $inv->commentaries;
					
					$sheet->appendRow($row);

				}
			});
		})->export('xlsx');
	}

}
