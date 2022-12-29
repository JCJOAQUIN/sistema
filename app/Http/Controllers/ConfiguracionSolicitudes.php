<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App;
use Alert;
use Auth;
use Lang;
use Carbon\Carbon;
use Ilovepdf\CompressTask;
use PDF;
use Excel;
use App\Functions\Files;

class ConfiguracionSolicitudes extends Controller
{
	private $module_id = 239;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',240)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('configuracion.solicitudes.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 240
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function selectRequest(Request $request)
	{
		if ($request->ajax()) 
		{
			$kind = $request->kind;
			switch ($kind) 
			{
				case 1:
					return view('configuracion.solicitudes.complementos.compra');
					break;

				case 8:
					return view('configuracion.solicitudes.complementos.recurso');
					break;

				case 9:
					return view('configuracion.solicitudes.complementos.reembolso');
					break;
				
				default:
					# code...
					break;
			}
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$kind = $request->kind;

			switch ($kind) 
			{
				case 1:
					if ($request->fiscal == 1 && $request->rfc != '') 
					{
						$rfc = $request->rfc;
					}	
					elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
					{
						$data	= App\Module::find($this->module_id);
						$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
						return view('configuracion.solicitudes.alta',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id' 	=> $this->module_id,
							'option_id' => 240,
							'alert' 	=> $alert
						]);
					}
					elseif($request->fiscal == 0 && $request->rfc != '')
					{
						$rfc = $request->rfc;
					}
					elseif($request->fiscal == 0 && $request->rfc == '')
					{
						$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
					}
					//return $request->realPath;
					$time						= $request->date!= null ? new \DateTime($request->date) : null;
					$newformat					= $request->date!= null ? $time->format('Y-m-d') : null;
					$data						= App\Module::find($this->module_id);

					$t_request							= new App\AutomaticRequests();
					$t_request->kind					= $request->kind;
					$t_request->taxPayment				= $request->fiscal;
					$t_request->idAccAcc				= $request->accountid;
					$t_request->idEnterprise			= $request->enterpriseid;
					$t_request->idArea					= $request->areaid;
					$t_request->idDepartment			= $request->departmentid;
					$t_request->idProject				= $request->projectid;
					$t_request->idRequest				= $request->userid;
					$t_request->idElaborate				= Auth::user()->id;
					$t_request->periodicity				= $request->periodicity;
					$t_request->day_monthlyOn			= $request->day_monthlyOn;
					$t_request->day_twiceMonthly_one	= $request->day_twiceMonthly_one;
					$t_request->day_twiceMonthly_two	= $request->day_twiceMonthly_two;
					$t_request->day_yearly 				= $request->day_yearly;
					$t_request->day_weeklyOn 			= $request->day_weeklyOn;
					$t_request->status					= 1;
					$t_request->save();

					$kind					= $t_request->kind;
					$provider_has_banks_id	= NULL;
					$provider_data_id 			= $request->provider_data_id;

					if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
					{
						App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
							'visible'=>'0'
						]);
					}

					if ($request->prov == "nuevo")
					{
						$t_provider_data 			= new App\ProviderData();
						$t_provider_data->users_id 	= Auth::user()->id;
						$t_provider_data->save();

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $t_provider_data->id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								$t_providerBank							= new App\ProviderBanks;
								$t_providerBank->provider_idProvider	= $provider_id;
								$t_providerBank->alias 					= $request->alias[$i];
								$t_providerBank->banks_idBanks			= $request->bank[$i];
								$t_providerBank->account				= $request->account[$i];
								$t_providerBank->branch					= $request->branch_office[$i];
								$t_providerBank->reference				= $request->reference[$i];
								$t_providerBank->clabe					= $request->clabe[$i];
								$t_providerBank->currency				= $request->currency[$i];
								$t_providerBank->agreement				= $request->agreement[$i];
								$t_providerBank->provider_data_id 		= $t_provider_data->id;
								$t_providerBank->save();

								if ($request->pay_mode == "Transferencia") 
								{
									if ($request->checked[$i] == 1)
									{
										$provider_has_banks_id = $t_providerBank->id;
									}
								}
							}
						}
					}
					elseif($request->prov == "buscar")
					{
						if (isset($request->edit))
						{
							$oldProvider			= App\Provider::find($request->idProvider);
							if($oldProvider->status==0)
							{
								$oldProvider->businessName	= $request->reason;
								$oldProvider->beneficiary	= $request->beneficiary;
								$oldProvider->phone			= $request->phone;
								$oldProvider->rfc			= $rfc;
								$oldProvider->contact		= $request->contact;
								$oldProvider->commentaries	= $request->other;
								$oldProvider->status		= 0;
								$oldProvider->users_id		= Auth::user()->id;
								$oldProvider->address		= $request->address;
								$oldProvider->number		= $request->number;
								$oldProvider->colony		= $request->colony;
								$oldProvider->postalCode	= $request->cp;
								$oldProvider->city			= $request->city;
								$oldProvider->state_idstate	= $request->state;
								$oldProvider->save();
								$provider_id				= $oldProvider->idProvider;

								if(isset($request->providerBank))
								{
									for ($i=0; $i < count($request->providerBank); $i++)
									{
										if ($request->providerBank[$i] == "x") 
										{
											$t_providerBank							= new App\ProviderBanks;
											$t_providerBank->provider_idProvider	= $provider_id;
											$t_providerBank->banks_idBanks			= $request->bank[$i];
											$t_providerBank->alias					= $request->alias[$i];
											$t_providerBank->account				= $request->account[$i];
											$t_providerBank->branch					= $request->branch_office[$i];
											$t_providerBank->reference				= $request->reference[$i];
											$t_providerBank->clabe					= $request->clabe[$i];
											$t_providerBank->currency				= $request->currency[$i];
											$t_providerBank->agreement				= $request->agreement[$i];
											$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
											$t_providerBank->save();

											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id = $t_providerBank->id;
												}
											}
										}
										else
										{
											$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id 	= $t_providerBank->id;
												}
											}
										}
									}
								}
							}
							else
							{
								//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
								$oldProvider->status		= 1;
								$oldProvider->save();
								$provider_data_id 			= $oldProvider->provider_data_id;

								$t_provider					= new App\Provider();
								$t_provider->businessName	= $request->reason;
								$t_provider->beneficiary	= $request->beneficiary;
								$t_provider->phone			= $request->phone;
								$t_provider->rfc			= $rfc;
								$t_provider->contact		= $request->contact;
								$t_provider->commentaries	= $request->other;
								$t_provider->status			= 2;
								$t_provider->users_id		= Auth::user()->id;
								$t_provider->address		= $request->address;
								$t_provider->number			= $request->number;
								$t_provider->colony			= $request->colony;
								$t_provider->postalCode		= $request->cp;
								$t_provider->city			= $request->city;
								$t_provider->state_idstate	= $request->state;
								$t_provider->provider_data_id	= $provider_data_id;
								$t_provider->save();
								//SE GUARDA EL ID DEL PROVEEDOR PARA PURCHASE
								$provider_id				= $t_provider->idProvider;
								$provider_data_id 			= $t_provider->provider_data_id;
								if(isset($request->providerBank))
								{
									for ($i=0; $i < count($request->providerBank); $i++)
									{
										if ($request->providerBank[$i] == "x")
										{
											$t_providerBank							= new App\ProviderBanks;
											$t_providerBank->provider_idProvider	= $provider_id;
											$t_providerBank->banks_idBanks			= $request->bank[$i];
											$t_providerBank->alias					= $request->alias[$i];
											$t_providerBank->account				= $request->account[$i];
											$t_providerBank->branch					= $request->branch_office[$i];
											$t_providerBank->reference				= $request->reference[$i];
											$t_providerBank->clabe					= $request->clabe[$i];
											$t_providerBank->currency				= $request->currency[$i];
											$t_providerBank->agreement				= $request->agreement[$i];
											$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
											$t_providerBank->save();

											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id = $t_providerBank->id;
												}
											}
										}
										else
										{
											$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id 	= $t_providerBank->id;
												}
											}
										}
									}
								}
							}
						}
						else
						{
							$provider_id			= $request->idProvider;
							$provider_has_banks_id 	= $request->provider_has_banks_id;
						}
					}

					$t_purchase							= new App\PurchaseTemp();
					$t_purchase->title					= $request->title;
					$t_purchase->numberOrder			= $request->numberOrder;
					$t_purchase->reference				= $request->referencePuchase;
					$t_purchase->idProvider				= $provider_id;
					$t_purchase->notes					= $request->note;
					$t_purchase->paymentMode			= $request->pay_mode;
					$t_purchase->typeCurrency			= $request->type_currency;
					$t_purchase->billStatus				= $request->status_bill;
					$t_purchase->subtotal				= $request->subtotal;
					$t_purchase->tax					= $request->totaliva;
					$t_purchase->amount					= $request->total;
					$t_purchase->provider_has_banks_id	= $provider_has_banks_id;
					$t_purchase->idAutomaticRequests	= $t_request->id;
					$t_purchase->payment_date 			= $request->payment_date != '' ? Carbon::createFromFormat('d-m-Y',$request->payment_date)->format('Y-m-d')	: null;
					$t_purchase->provider_data_id 		= $provider_data_id;
					$t_purchase->save();

					$purchase = $t_purchase->id;
					for ($i=0; $i < count($request->tamount); $i++)
					{
						$t_detailPurchase					= new App\DetailPurchaseTemp();
						$t_detailPurchase->idPurchaseTemp	= $purchase;
						$t_detailPurchase->quantity			= $request->tquanty[$i];
						$t_detailPurchase->unit				= $request->tunit[$i];
						$t_detailPurchase->description		= $request->tdescr[$i];
						$t_detailPurchase->unitPrice		= $request->tprice[$i];
						$t_detailPurchase->tax				= $request->tiva[$i];
						$t_detailPurchase->discount			= $request->tdiscount[$i];
						$t_detailPurchase->amount			= $request->tamount[$i];
						$t_detailPurchase->typeTax			= $request->tivakind[$i];
						$t_detailPurchase->subtotal			= $request->tquanty[$i] * $request->tprice[$i];
						$t_detailPurchase->save();

						$idDetailPurchase 		= $t_detailPurchase->id;
							$tamountadditional 	= 'tamountadditional'.$i;
							$tnameamount 		= 'tnameamount'.$i;
							if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
							{
								for ($d=0; $d < count($request->$tamountadditional); $d++) 
								{ 
									if ($request->$tamountadditional[$d] != "") 
									{
										$t_taxes						= new App\TaxesPurchaseTemp();
										$t_taxes->name					= $request->$tnameamount[$d];
										$t_taxes->amount				= $request->$tamountadditional[$d];
										$t_taxes->idDetailPurchaseTemp	= $idDetailPurchase;
										$t_taxes->save();
									}
								}
							}

							$tamountretention     = 'tamountretention'.$i;
							$tnameretention 	= 'tnameretention'.$i;
							if (isset($request->$tamountretention) && $request->$tamountretention != "") 
							{
								for ($d=0; $d < count($request->$tamountretention); $d++) 
								{ 
									if ($request->$tamountretention[$d] != "") 
									{
										$t_retention						= new App\RetentionPurchaseTemp();
										$t_retention->name					= $request->$tnameretention[$d];
										$t_retention->amount				= $request->$tamountretention[$d];
										$t_retention->idDetailPurchaseTemp	= $idDetailPurchase;
										$t_retention->save();
									}
								}
							}
					}
					$alert = "swal('','".Lang::get("messages.request_sent")."', 'success');";
					break;

				case 8:
					$t_request							= new App\AutomaticRequests();
					$t_request->kind					= $request->kind;
					$t_request->idEnterprise			= $request->enterpriseid;
					$t_request->idArea					= $request->areaid;
					$t_request->idDepartment			= $request->departmentid;
					$t_request->idProject				= $request->projectid;
					$t_request->idRequest				= $request->userid;
					$t_request->idElaborate				= Auth::user()->id;
					$t_request->periodicity				= $request->periodicity;
					$t_request->day_monthlyOn			= $request->day_monthlyOn;
					$t_request->day_twiceMonthly_one	= $request->day_twiceMonthly_one;
					$t_request->day_twiceMonthly_two	= $request->day_twiceMonthly_two;
					$t_request->day_yearly 				= $request->day_yearly;
					$t_request->day_weeklyOn 			= $request->day_weeklyOn;
					$t_request->status					= 1;
					$t_request->save();

					$kind                   = $t_request->kind;
					
					$t_resource				= new App\ResourceTemp();
					$t_resource->title		= $request->title;
					$t_resource->total		= $request->total_resource;
					$t_resource->reference	= $request->reference;
					$t_resource->currency	= $request->type_currency;
					
					if ($request->method == 1) 
					{
						$t_resource->idEmployee	= $request->idEmployee;
					}
					else
					{
						$t_resource->idEmployee	= null;
					}
					$t_resource->idpaymentMethod 		= $request->method;
					$t_resource->idAutomaticRequests 	= $t_request->id;
					$t_resource->save();

					$resource		= $t_resource->id;
					$countAmount	= count($request->t_amount);
					
					for ($i=0; $i < $countAmount; $i++)
					{
						$t_detailResource					= new App\ResourceDetailTemp();
						$t_detailResource->idResourceTemp	= $resource;
						$t_detailResource->concept			= $request->t_concept[$i];
						$t_detailResource->idAccAcc			= $request->t_account[$i];
						$t_detailResource->amount			= $request->t_amount[$i];
						$t_detailResource->save();
					}
					$alert = "swal('','".Lang::get("messages.request_sent")."', 'success');";
					break;
				
				default:
					# code...
					break;
			}

			return redirect('configuration/requests')->with('alert',$alert);
		}
	}

	public function edit(Request $request)
	{
		if (Auth::user()->module->where('id',241)->count()>0) 
		{
			$requests = App\AutomaticRequests::where(function($query) use ($request)
			{
				if ($request->enterpriseid != "")  
				{
					$query->whereIn('idEnterprise',$request->enterpriseid);
				}
				if ($request->account != "") 
				{
					$query->whereIn('idAccAcc',$request->account);
				}
				if ($request->kind != "") 
				{
					$query->whereIn('kind',$request->kind);
				}
				if($request->mindate != "" && $request->maxdate != "")
				{
					$query->whereBetween('created_at',
					[
						Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d 00:00:00'),
						Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d 23:59:59')
					]);
				}
				if ($request->userid) 
				{
					$query->whereIn('idRequest',$request->userid);
				}
			})
			->orderBy('id','desc')
			->paginate(10);

			$data = App\Module::find($this->module_id);
			return response(
				view('configuracion.solicitudes.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 241,
					'enterpriseid'	=> $request->enterpriseid,
					'account'		=> $request->account,
					'kind'			=> $request->kind,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'userid'		=> $request->userid,
					'requests'		=> $requests
				])
			)
			->cookie('urlSearch',storeUrlCookie(241), 2880);
		}
	}

	public function show($id)
	{
		if (Auth::user()->module->where('id',241)->count()>0) 
		{
			$request 	= App\AutomaticRequests::find($id);
			$data 		= App\Module::find($this->module_id);
			return view('configuracion.solicitudes.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 241,
				'request'	=> $request
			]);
		}
	}

	public function update(Request $request,$id)
	{
		if (Auth::user()->module->where('id',241)->count()>0)
		{
			$kind = $request->kind;

			switch ($kind) 
			{
				case 1:
					if ($request->fiscal == 1 && $request->rfc != '') 
					{
						$rfc = $request->rfc;
					}	
					elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
					{
						$data	= App\Module::find($this->module_id);
						$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
						return view('configuracion.solicitudes.alta',
						[
							'id'		=> $data['father'],
							'title'		=> $data['name'],
							'details'	=> $data['details'],
							'child_id' 	=> $this->module_id,
							'option_id' => 240,
							'alert' 	=> $alert
						]);
					}
					elseif($request->fiscal == 0 && $request->rfc != '')
					{
						$rfc = $request->rfc;
					}
					elseif($request->fiscal == 0 && $request->rfc == '')
					{
						$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
					}
					//return $request->realPath;
					$time						= $request->date!= null ? new \DateTime($request->date) : null;
					$newformat					= $request->date!= null ? $time->format('Y-m-d') : null;
					$data						= App\Module::find($this->module_id);

					$t_request							= App\AutomaticRequests::find($id);
					$t_request->kind					= $request->kind;
					$t_request->taxPayment				= $request->fiscal;
					$t_request->idAccAcc				= $request->accountid;
					$t_request->idEnterprise			= $request->enterpriseid;
					$t_request->idArea					= $request->areaid;
					$t_request->idDepartment			= $request->departmentid;
					$t_request->idProject				= $request->projectid;
					$t_request->idRequest				= $request->userid;
					$t_request->idElaborate				= Auth::user()->id;
					$t_request->periodicity				= $request->periodicity;
					$t_request->day_monthlyOn			= $request->day_monthlyOn;
					$t_request->day_twiceMonthly_one	= $request->day_twiceMonthly_one;
					$t_request->day_twiceMonthly_two	= $request->day_twiceMonthly_two;
					$t_request->day_yearly 				= $request->day_yearly;
					$t_request->day_weeklyOn 			= $request->day_weeklyOn;
					$t_request->status					= 1;
					$t_request->save();

					$kind					= $t_request->kind;
					$provider_has_banks_id	= NULL;
					$provider_data_id 		= $request->provider_data_id;

					if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
					{
						App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
							'visible'=>'0'
						]);
					}

					if ($request->prov == "nuevo")
					{
						$t_provider_data 			= new App\ProviderData();
						$t_provider_data->users_id 	= Auth::user()->id;
						$t_provider_data->save();

						$t_provider					= new App\Provider();
						$t_provider->businessName	= $request->reason;
						$t_provider->beneficiary	= $request->beneficiary;
						$t_provider->phone			= $request->phone;
						$t_provider->rfc			= $rfc;
						$t_provider->contact		= $request->contact;
						$t_provider->commentaries	= $request->other;
						$t_provider->status			= 2;
						$t_provider->users_id		= Auth::user()->id;
						$t_provider->address		= $request->address;
						$t_provider->number			= $request->number;
						$t_provider->colony			= $request->colony;
						$t_provider->postalCode		= $request->cp;
						$t_provider->city			= $request->city;
						$t_provider->state_idstate	= $request->state;
						$t_provider->provider_data_id	= $t_provider_data->id;
						$t_provider->save();
						$provider_id				= $t_provider->idProvider;
						$provider_data_id 			= $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								$t_providerBank							= new App\ProviderBanks;
								$t_providerBank->provider_idProvider	= $provider_id;
								$t_providerBank->alias 					= $request->alias[$i];
								$t_providerBank->banks_idBanks			= $request->bank[$i];
								$t_providerBank->account				= $request->account[$i];
								$t_providerBank->branch					= $request->branch_office[$i];
								$t_providerBank->reference				= $request->reference[$i];
								$t_providerBank->clabe					= $request->clabe[$i];
								$t_providerBank->currency				= $request->currency[$i];
								$t_providerBank->agreement				= $request->agreement[$i];
								$t_providerBank->provider_data_id 		= $t_provider_data->id;
								$t_providerBank->save();

								if ($request->pay_mode == "Transferencia") 
								{
									if ($request->checked[$i] == 1)
									{
										$provider_has_banks_id = $t_providerBank->id;
									}
								}
							}
						}
					}
					elseif($request->prov == "buscar")
					{
						if (isset($request->edit))
						{
							$oldProvider			= App\Provider::find($request->idProvider);
							if($oldProvider->status==0)
							{
								$oldProvider->businessName	= $request->reason;
								$oldProvider->beneficiary	= $request->beneficiary;
								$oldProvider->phone			= $request->phone;
								$oldProvider->rfc			= $rfc;
								$oldProvider->contact		= $request->contact;
								$oldProvider->commentaries	= $request->other;
								$oldProvider->status		= 2;
								$oldProvider->users_id		= Auth::user()->id;
								$oldProvider->address		= $request->address;
								$oldProvider->number		= $request->number;
								$oldProvider->colony		= $request->colony;
								$oldProvider->postalCode	= $request->cp;
								$oldProvider->city			= $request->city;
								$oldProvider->state_idstate	= $request->state;
								$oldProvider->save();
								$provider_id				= $oldProvider->idProvider;

								if(isset($request->providerBank))
								{
									for ($i=0; $i < count($request->providerBank); $i++)
									{
										if ($request->providerBank[$i] == "x")
										{
											$t_providerBank							= new App\ProviderBanks;
											$t_providerBank->provider_idProvider	= $provider_id;
											$t_providerBank->banks_idBanks			= $request->bank[$i];
											$t_providerBank->alias					= $request->alias[$i];
											$t_providerBank->account				= $request->account[$i];
											$t_providerBank->branch					= $request->branch_office[$i];
											$t_providerBank->reference				= $request->reference[$i];
											$t_providerBank->clabe					= $request->clabe[$i];
											$t_providerBank->currency				= $request->currency[$i];
											$t_providerBank->agreement				= $request->agreement[$i];
											$t_providerBank->provider_data_id 		= $oldProvider->provider_data_id;
											$t_providerBank->save();

											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id = $t_providerBank->id;
												}
											}
										}
										else
										{
											$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id 	= $t_providerBank->id;
												}
											}
										}
									}
								}
							}
							else
							{
								//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
								$oldProvider->status		= 1;
								$oldProvider->save();
								$provider_data_id 			= $oldProvider->provider_data_id;

								$t_provider					= new App\Provider();
								$t_provider->businessName	= $request->reason;
								$t_provider->beneficiary	= $request->beneficiary;
								$t_provider->phone			= $request->phone;
								$t_provider->rfc			= $rfc;
								$t_provider->contact		= $request->contact;
								$t_provider->commentaries	= $request->other;
								$t_provider->status			= 2;
								$t_provider->users_id		= Auth::user()->id;
								$t_provider->address		= $request->address;
								$t_provider->number			= $request->number;
								$t_provider->colony			= $request->colony;
								$t_provider->postalCode		= $request->cp;
								$t_provider->city			= $request->city;
								$t_provider->state_idstate	= $request->state;
								$t_provider->provider_data_id	= $provider_data_id;
								$t_provider->save();
								//SE GUARDA EL ID DEL PROVEEDOR PARA PURCHASE
								$provider_id				= $t_provider->idProvider;
								$provider_data_id 			= $t_provider->provider_data_id;
								if(isset($request->providerBank))
								{
									for ($i=0; $i < count($request->providerBank); $i++)
									{
										if ($request->providerBank[$i] == "x")
										{
											$t_providerBank							= new App\ProviderBanks;
											$t_providerBank->provider_idProvider	= $provider_id;
											$t_providerBank->banks_idBanks			= $request->bank[$i];
											$t_providerBank->alias					= $request->alias[$i];
											$t_providerBank->account				= $request->account[$i];
											$t_providerBank->branch					= $request->branch_office[$i];
											$t_providerBank->reference				= $request->reference[$i];
											$t_providerBank->clabe					= $request->clabe[$i];
											$t_providerBank->currency				= $request->currency[$i];
											$t_providerBank->agreement				= $request->agreement[$i];
											$t_providerBank->provider_data_id 		= $provider_data_id;
											$t_providerBank->save();
											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id = $t_providerBank->id;
												}
											}
										}
										else
										{
											$t_providerBank	= App\ProviderBanks::find($request->providerBank[$i]);
											if ($request->pay_mode == "Transferencia") 
											{
												if ($request->checked[$i] == 1) 
												{
													$provider_has_banks_id 	= $t_providerBank->id;
												}
											}
										}
									}
								}
							}
						}
						else
						{
							$provider_id			= $request->idProvider;
							$provider_has_banks_id 	= $request->provider_has_banks_id;
						}
					}

					$t_purchase							= App\PurchaseTemp::find($t_request->purchase->id);
					$t_purchase->title					= $request->title;
					$t_purchase->numberOrder			= $request->numberOrder;
					$t_purchase->reference				= $request->referencePuchase;
					$t_purchase->idProvider				= $provider_id;
					$t_purchase->notes					= $request->note;
					$t_purchase->paymentMode			= $request->pay_mode;
					$t_purchase->typeCurrency			= $request->type_currency;
					$t_purchase->billStatus				= $request->status_bill;
					$t_purchase->subtotal				= $request->subtotal;
					$t_purchase->tax					= $request->totaliva;
					$t_purchase->amount					= $request->total;
					$t_purchase->provider_has_banks_id	= $provider_has_banks_id;
					$t_purchase->idAutomaticRequests	= $t_request->id;
					$t_purchase->payment_date 			= $request->payment_date != '' ? Carbon::createFromFormat('d-m-Y',$request->payment_date)->format('Y-m-d')	: null;
					$t_purchase->provider_data_id 		= $provider_data_id;
					$t_purchase->save();

					if (isset($request->delete) && count($request->delete)>0)
					{
						App\TaxesPurchaseTemp::whereIn('idDetailPurchaseTemp',$request->delete)->delete();
						App\RetentionPurchaseTemp::whereIn('idDetailPurchaseTemp',$request->delete)->delete();
						App\DetailPurchaseTemp::whereIn('id',$request->delete)->delete();
					}

					$purchase = $t_purchase->id;
					for ($i=0; $i < count($request->tamount); $i++)
					{
						if ($request->idDetail[$i] == "x") 
						{
							$t_detailPurchase = new App\DetailPurchaseTemp();
						}
						else
						{
							$t_detailPurchase = App\DetailPurchaseTemp::find($request->idDetail[$i]);
						}
						$t_detailPurchase->idPurchaseTemp	= $purchase;
						$t_detailPurchase->quantity			= $request->tquanty[$i];
						$t_detailPurchase->unit				= $request->tunit[$i];
						$t_detailPurchase->description		= $request->tdescr[$i];
						$t_detailPurchase->unitPrice		= $request->tprice[$i];
						$t_detailPurchase->tax				= $request->tiva[$i];
						$t_detailPurchase->discount			= $request->tdiscount[$i];
						$t_detailPurchase->amount			= $request->tamount[$i];
						$t_detailPurchase->typeTax			= $request->tivakind[$i];
						$t_detailPurchase->subtotal			= $request->tquanty[$i] * $request->tprice[$i];
						$t_detailPurchase->save();

						$idDetailPurchase 	= $t_detailPurchase->id;
						$tamountadditional 	= 'tamountadditional'.$i;
						$tnameamount 		= 'tnameamount'.$i;
						App\TaxesPurchaseTemp::where('idDetailPurchaseTemp',$request->idDetail[$i])->delete();
						if (isset($request->$tamountadditional) && $request->$tamountadditional != "")
						{
							for ($d=0; $d < count($request->$tamountadditional); $d++) 
							{ 
								if ($request->$tamountadditional[$d] != "") 
								{
									$t_taxes						= new App\TaxesPurchaseTemp();
									$t_taxes->name					= $request->$tnameamount[$d];
									$t_taxes->amount				= $request->$tamountadditional[$d];
									$t_taxes->idDetailPurchaseTemp	= $idDetailPurchase;
									$t_taxes->save();
								}
							}
						}
						$tamountretention	= 'tamountretention'.$i;
						$tnameretention		= 'tnameretention'.$i;
						App\RetentionPurchaseTemp::where('idDetailPurchaseTemp',$request->idDetail[$i])->delete();
						if (isset($request->$tamountretention) && $request->$tamountretention != "") 
						{
							for ($d=0; $d < count($request->$tamountretention); $d++) 
							{ 
								if ($request->$tamountretention[$d] != "")
								{
									$t_retention						= new App\RetentionPurchaseTemp();
									$t_retention->name					= $request->$tnameretention[$d];
									$t_retention->amount				= $request->$tamountretention[$d];
									$t_retention->idDetailPurchaseTemp	= $idDetailPurchase;
									$t_retention->save();
								}
							}
						}
					}
					$alert = "swal('','".Lang::get("messages.request_updated")."', 'success');";
					break;

				case 8:
					$t_request							= App\AutomaticRequests::find($id);
					$t_request->kind					= $request->kind;
					$t_request->idEnterprise			= $request->enterpriseid;
					$t_request->idArea					= $request->areaid;
					$t_request->idDepartment			= $request->departmentid;
					$t_request->idProject				= $request->projectid;
					$t_request->idRequest				= $request->userid;
					$t_request->idElaborate				= Auth::user()->id;
					$t_request->periodicity				= $request->periodicity;
					$t_request->day_monthlyOn			= $request->day_monthlyOn;
					$t_request->day_twiceMonthly_one	= $request->day_twiceMonthly_one;
					$t_request->day_twiceMonthly_two	= $request->day_twiceMonthly_two;
					$t_request->day_yearly 				= $request->day_yearly;
					$t_request->day_weeklyOn 			= $request->day_weeklyOn;
					$t_request->status					= 1;
					$t_request->save();

					$kind					= $t_request->kind;
					
					$t_resource				= App\ResourceTemp::find($t_request->resource->id);
					$t_resource->title		= $request->title;
					$t_resource->total		= $request->total_resource;
					$t_resource->reference	= $request->reference;
					$t_resource->currency	= $request->type_currency;
					
					if ($request->method == 1) 
					{
						$t_resource->idEmployee	= $request->idEmployee;
					}
					else
					{
						$t_resource->idEmployee	= null;
					}
					$t_resource->idpaymentMethod		= $request->method;
					$t_resource->idAutomaticRequests	= $t_request->id;
					$t_resource->save();

					$resource		= $t_resource->id;
					$countAmount	= count($request->t_amount);

					if ($request->delete != "" && count($request->delete))
					{
						App\ResourceDetailTemp::whereIn('id',$request->delete)->delete();
					}
					
					for ($i=0; $i < $countAmount; $i++)
					{
						if ($request->idDetail[$i] == "x")
						{
							$t_detailResource					= new App\ResourceDetailTemp();
							$t_detailResource->idResourceTemp	= $resource;
							$t_detailResource->concept			= $request->t_concept[$i];
							$t_detailResource->idAccAcc			= $request->t_account[$i];
							$t_detailResource->amount			= $request->t_amount[$i];
							$t_detailResource->save();
						}
					}
					$alert = "swal('','".Lang::get("messages.request_updated")."', 'success');";
					break;
				
				default:
					# code...
					break;
			}
			return redirect('configuration/requests/show/'.$id)->with('alert',$alert);
		}
	}

	public function inactiveRequest($id)
	{
		if (Auth::user()->module->where('id',241)->count()>0) 
		{
			$request			= App\AutomaticRequests::find($id);
			$request->status	= 0;
			$request->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success');";
			return redirect('configuration/requests/edit')->with('alert',$alert);
		}
	}

	public function activeRequest($id)
	{
		if (Auth::user()->module->where('id',241)->count()>0) 
		{
			$request			= App\AutomaticRequests::find($id);
			$request->status	= 1;
			$request->save();
			$alert = "swal('','".Lang::get("messages.request_updated")."', 'success');";
			return redirect('configuration/requests/edit')->with('alert',$alert);
		}
	}
}
