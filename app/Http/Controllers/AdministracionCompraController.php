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
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
use PDF;
use Excel;
use App\Functions\Files;
use App\DocumentsPartials;
use App\PartialPayment;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Facades\Cookie;
use Lang;

class AdministracionCompraController extends Controller
{
	private $module_id = 24;

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
		if (Auth::user()->module->where('id',28)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('administracion.compra.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 28
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id)
	{
		if(Auth::user()->module->where('id',28)->count()>0)
		{
			// $test = DB::table('request')
			// 	->selectRaw('refunddocuments.path, request_models.status')
			// 	->join('refunds', 'refunds.idFolio', 'request_models.folio')
			// 	->join('refunddetail', 'refunddetail.idRefund', 'refunds.idRefund')
			// 	->join('refunddocuments', 'refunddocuments.idRefundDetail', 'refunddetail.idRefundDetail')
			// 	->where('refunds.idFolio', $id)
			// 	->whereIn('request_models.status', [5])
			// 	->get();
				
			// if($test!='[]'){
			// 	$flag=1;
			// }else{
			// 	$flag=2;
			// }
			if(Auth::user()->globalCheck->where('module_id',29)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',29)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data     = App\Module::find($this->module_id);
			$requests = App\RequestModel::whereIn('status',[5, 6, 7,10,11,12,13])
				->where('kind',1)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			if($requests != "")
			{
				return view('administracion.compra.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 28,
						'requests'  => $requests
					]);
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

	public function validationDocs(Request $request)
	{
		if($request->ajax())
		{
			$fiscal_val = $request->fiscal_value;
			$num_ticket = $request->num_ticket;
			$timepath   = $request->timepath;
			$monto      = $request->monto;
			$datepath   = $request->datepath;
			$folio      = null;
			$date       = Carbon::CreateFromFormat('d-m-Y', $datepath)->format('Y-m-d');
			$time       = new \DateTime($request->timepath);
			$timepath   = $time->format('H:i:s');
			if($request->fiscal_value!=''||$request->num_ticket!='')
			{
				$options               = [];
				$options['fiscal_val'] = $request->fiscal_value;
				$options['ticket_val'] = $request->num_ticket;
				$options['date']       = $date;
				$options['time']       = $timepath;
				$options['amount']     = $request->monto;
				$check_docs            = App\Functions\DocsValidate::validate($options,$folio);			
				if($check_docs>0)
				{
					return Response('false');
				}
			}
			return Response('true');
		}
	}

	public function validationDocsPartial(Request $request)
	{
		if($request->ajax())
		{
			
			$position = [];
			for ($i=0; $i < count($request->datepath_partial); $i++)
			{ 
				$folio					= $request->folio;
				$options				= [];
				$date					= Carbon::CreateFromFormat('d-m-Y', $request->datepath_partial[$i])->format('Y-m-d');
				$time					= new \DateTime($request->timepath_partial[$i]);
				$timepath				= $time->format('H:i:s');
				$options['time']		= $timepath;
				$options['fiscal_val']	= $request->fiscal_value_partial[$i];
				$options['ticket_val']	= $request->num_ticket_partial[$i];
				$options['date']		= $date;
				$options['amount']		= $request->monto_partial[$i];
				$check_docs				= App\Functions\DocsValidate::validate($options,$folio);

				if($check_docs>0)
				{
					if (isset($request->num_ticket_partial[$i]) && $request->num_ticket_partial[$i] != "") 
					{
						$position[] = $i;
					}
					if(isset($request->fiscal_value_partial[$i]) && $request->fiscal_value_partial[$i] != "")
					{
						$position[] = $i;
					}
				}
			}
				
			return Response($position);
		}
	}

	public function store(Request $request)
	{
		// return $request->$tamountadditional." --- ".$request->$tamountretention;
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && $request->rfc != '') 
			{
				$rfc = $request->rfc;
			}
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$data	= App\Module::find($this->module_id);
				$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				return view('administracion.compra.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 28,
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
			$newformat 					= $request->date != "" ? Carbon::parse($request->date)->format('Y-m-d') : $request->date ;
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= "1";
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $newformat;
			$t_request->status			= "2";
			$t_request->account 		= $request->accountid;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idArea			= $request->areaid;
			$t_request->idDepartment	= $request->departmentid;
			$t_request->idProject 		= $request->projectid;
			$t_request->code_wbs 		= $request->code_wbs;
			$t_request->code_edt 		= $request->code_edt;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio                   = $t_request->folio;
			$kind                    = $t_request->kind;
			$provider_has_banks_id   = NULL;
			$provider_id             = NULL;
			$provider_data_id        = $request->provider_data_id;
			if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
			{
				App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
					'visible'=>'0'
				]);
			}
			if ($request->prov == "nuevo")
			{
				$t_provider_data              = new App\ProviderData();
				$t_provider_data->users_id    = Auth::user()->id;
				$t_provider_data->save();
				$t_provider                   = new App\Provider();
				$t_provider->businessName     = $request->reason;
				$t_provider->beneficiary      = $request->beneficiary;
				$t_provider->phone            = $request->phone;
				$t_provider->rfc              = $rfc;
				$t_provider->contact          = $request->contact;
				$t_provider->commentaries     = $request->other;
				$t_provider->status           = 2;
				$t_provider->users_id         = Auth::user()->id;
				$t_provider->address          = $request->address;
				$t_provider->number           = $request->number;
				$t_provider->colony           = $request->colony;
				$t_provider->postalCode       = $request->cp;
				$t_provider->city             = $request->city;
				$t_provider->state_idstate    = $request->state;
				$t_provider->provider_data_id = $t_provider_data->id;
				$t_provider->save();
				$provider_id                  = $t_provider->idProvider;
				$provider_data_id             = $t_provider->provider_data_id;
				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank                      = new App\ProviderBanks;
						$t_providerBank->provider_idProvider = $provider_id;
						$t_providerBank->banks_idBanks       = $request->bank[$i];
						$t_providerBank->alias               = $request->alias[$i];
						$t_providerBank->account             = $request->account[$i];
						$t_providerBank->branch              = $request->branch_office[$i];
						$t_providerBank->reference           = $request->reference[$i];
						$t_providerBank->clabe               = $request->clabe[$i];
						$t_providerBank->currency            = $request->currency[$i];
						$t_providerBank->agreement           = $request->agreement[$i];
						$t_providerBank->iban                = $request->iban[$i];
						$t_providerBank->bic_swift           = $request->bic_swift[$i];
						$t_providerBank->provider_data_id    = $t_provider_data->id;
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
						$oldProvider->businessName  = $request->reason;
						$oldProvider->beneficiary   = $request->beneficiary;
						$oldProvider->phone         = $request->phone;
						$oldProvider->rfc           = $rfc;
						$oldProvider->contact       = $request->contact;
						$oldProvider->commentaries  = $request->other;
						$oldProvider->status        = 0;
						$oldProvider->users_id      = Auth::user()->id;
						$oldProvider->address       = $request->address;
						$oldProvider->number        = $request->number;
						$oldProvider->colony        = $request->colony;
						$oldProvider->postalCode    = $request->cp;
						$oldProvider->city          = $request->city;
						$oldProvider->state_idstate = $request->state;
						$oldProvider->save();
						$provider_id                = $oldProvider->idProvider;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
						$oldProvider->status          = 1;
						$oldProvider->save();
						$provider_data_id             = $oldProvider->provider_data_id;
						$t_provider                   = new App\Provider();
						$t_provider->businessName     = $request->reason;
						$t_provider->beneficiary      = $request->beneficiary;
						$t_provider->phone            = $request->phone;
						$t_provider->rfc              = $rfc;
						$t_provider->contact          = $request->contact;
						$t_provider->commentaries     = $request->other;
						$t_provider->status           = 2;
						$t_provider->users_id         = Auth::user()->id;
						$t_provider->address          = $request->address;
						$t_provider->number           = $request->number;
						$t_provider->colony           = $request->colony;
						$t_provider->postalCode       = $request->cp;
						$t_provider->city             = $request->city;
						$t_provider->state_idstate    = $request->state;
						$t_provider->provider_data_id = $provider_data_id;
						$t_provider->save();
						$provider_id                  = $t_provider->idProvider;
						$provider_data_id             = $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
					$provider_id           = $request->idProvider;
					$provider_has_banks_id = $request->provider_has_banks_id;
				}
			}
			$subtotales = 0;
			$iva        = 0;
			$taxes      = 0;
			$retentions = 0;
			for ($i=0; $i < count($request->tquanty); $i++)
			{
				$tamountadditional = 'tamountadditional'.$i;
				$tnameamount       = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}
				$subtotales += (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva        += $request->tiva[$i];
			}
			$time_title                        = $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
			$new_format_title                  = $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
			$total                             = ($subtotales+$iva+$taxes)-$retentions;
			$t_purchase                        = new App\Purchase();
			$t_purchase->title                 = $request->title;
			$t_purchase->datetitle             = $new_format_title;
			$t_purchase->numberOrder           = $request->numberOrder;
			$t_purchase->reference             = $request->referencePuchase;
			$t_purchase->idProvider            = $provider_id;
			$t_purchase->idFolio               = $folio;
			$t_purchase->idKind                = $kind;
			$t_purchase->notes                 = $request->note;
			$t_purchase->discount              = $request->descuento;
			$t_purchase->paymentMode           = $request->pay_mode;
			$t_purchase->typeCurrency          = $request->type_currency;
			$t_purchase->billstatus            = $request->status_bill;
			$t_purchase->subtotales            = $subtotales;
			$t_purchase->tax                   = $iva;
			$t_purchase->amount                = $total;
			$t_purchase->provider_has_banks_id = $provider_has_banks_id;
			$t_purchase->provider_data_id      = $provider_data_id;
			$t_purchase->save();
			$purchase                          = $t_purchase->idPurchase;
			// HOLA AQUI SE GUARDAN LAS PARCIALIDADES
			$errorPartial = $this->savePartial($t_purchase,$request);
			if (isset($request->realPath) && count($request->realPath)>0)
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name            = Files::rename($request->realPath[$i],$folio);
						$documents                = new App\DocumentsPurchase();
						$documents->fiscal_folio  = $request->folio_fiscal[$i];
						$documents->ticket_number = $request->num_ticket[$i];
						$documents->amount        = $request->monto[$i];
						$documents->timepath      = $request->timepath[$i];
						$date                     = Carbon::parse($request->datepath[$i])->format('Y-m-d');
						$documents->datepath      = $date;
						$documents->path          = $new_file_name;
						$documents->idPurchase    = $purchase;
						$documents->name          = $request->nameDocument[$i];
						$documents->save();
					}
				}
			}
			for ($i=0; $i < count($request->tamount); $i++)
			{
				$t_detailPurchase              = new App\DetailPurchase();
				$t_detailPurchase->idPurchase  = $purchase;
				$t_detailPurchase->quantity    = $request->tquanty[$i];
				$t_detailPurchase->unit        = $request->tunit[$i];
				$t_detailPurchase->description = $request->tdescr[$i];
				$t_detailPurchase->unitPrice   = $request->tprice[$i];
				$t_detailPurchase->tax         = $request->tiva[$i];
				$t_detailPurchase->discount    = $request->tdiscount[$i];
				$t_detailPurchase->amount      = $request->tamount[$i];
				$t_detailPurchase->typeTax     = $request->tivakind[$i];
				$t_detailPurchase->subtotal    = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();
				$idDetailPurchase              = $t_detailPurchase->idDetailPurchase;
				$tamountadditional             = 'tamountadditional'.$i;
				$tnameamount                   = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                   = new App\TaxesPurchase();
							$t_taxes->name             = $request->$tnameamount[$d];
							$t_taxes->amount           = $request->$tamountadditional[$d];
							$t_taxes->idDetailPurchase = $idDetailPurchase;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                   = new App\RetentionPurchase();
							$t_retention->name             = $request->$tnameretention[$d];
							$t_retention->amount           = $request->$tamountretention[$d];
							$t_retention->idDetailPurchase = $idDetailPurchase;
							$t_retention->save();
						}
					}
				}
			}
			$checkProvider = App\Provider::find($provider_id);
			if ($checkProvider == "" || ($checkProvider != "" && $checkProvider->status != 2)) 
			{
				$alert = "swal('', 'Los datos del proveedor están incompletos, por favor verifique los datos capturados.', 'error');" ;
				return redirect()->route('purchase.follow.edit',['id'=>$folio])->with('alert',$alert);
			}
			else
			{
				$t_request->status	= 3;
				$t_request->save();
				$provider_data_id 	= $checkProvider->provider_data_id;
				$updateProviderData = App\Provider::where('provider_data_id',$provider_data_id)->where('status',2)->where('idProvider','!=',$provider_id)->update(
				[
					'status' => 1,
				]);
			}

			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->whereHas('inChargeProjectGet',function($q) use ($t_request)
						{
							$q->where('project_id', $t_request->idProject)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
						->join('user_has_modules','users.id','user_has_modules.user_id')
						->where('user_has_modules.module_id',36)
						->where('user_has_department.departament_id',$request->departmentid)
						->where('users.active',1)
						->where('users.notification',1)
						->get();*/
			$user 	=  App\User::find($request->userid);
			$alert = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades, favor de revisar su registro.', 'success');" : "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Compra";
						$status      = "Revisar";
						$date        = Carbon::now();
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url         = route('purchase.review.edit',['id'=>$folio]);
						$subject     = "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades, favor de revisar su registro.', 'success');" : "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades y ocurrió un error al enviar el correo de notificación, favor de revisar su registro de parcialidades.', 'success');" : "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}
			return redirect('administration/purchase/search')->with('alert',$alert);
		}
		else{
			return redirect('/');
		}
	}
	
	//Areglar lo de las fechas no puedne ser el mismo dia en la vista y  aqui que la suma de los onto no superen el total del moelo
	public function savePartial($model,$request)
	{
		$error           = false; //Variables de Errores
		$partial_id      = $request->partial_id;
		$partial_payment = $request->partial_payment;
		$partial_type    = $request->partial_type;
		$partial_date    = $request->partial_date;
		$partial_delete  = $request->delete_partial;
		if(isset($partial_delete) && $partial_delete != "")
		{
			for($d=0; $d < count($partial_delete); $d++ )
			{
				$filesDeleted = App\DocumentsPartials::where('partial_id',$partial_delete[$d])->get();
				foreach($filesDeleted as $k => $v)
				{
					\Storage::disk('public')->delete('/docs/purchase/'.$v->path);
				}
				$deleteDocs2 = App\DocumentsPartials::where('partial_id',$partial_delete[$d])->delete();
				$partialP    = PartialPayment::where('id',$partial_delete[$d])->delete();
			}
		}
		if(isset($partial_id))
		{	
			for($i=0;$i<count($partial_id);$i++)
			{
				if($partial_id[$i]!="null")
				{
					$partialPayment = PartialPayment::where('id',$partial_id[$i])
						->where('date_delivery',null)
						->where('purchase_id',$model->idPurchase)
						->first();

					if($partialPayment)
					{
						if($partial_payment[$i]>=0)
						{
							$data = Carbon::parse($partial_date[$i])->format('Y-m-d');
							$partialPayment->update(
							[
								'payment'			=> $partial_payment[$i], 
								'tipe'				=> $partial_type[$i], 
								'date_requested'	=> $data
							]);
							$deleteDocs  = App\DocumentsPartials::where('partial_id',$partialPayment->id)->delete();
							$numDocument = $i+1;
							$path_p      = 'path_p'.$numDocument;
							$name_p      = 'name_p'.$numDocument;
							$folio_p     = 'folio_p'.$numDocument;
							$ticket_p    = 'ticket_p'.$numDocument;
							$monto_p     = 'monto_p'.$numDocument;
							$timepath_p  = 'timepath_p'.$numDocument;
							$datepath_p  = 'datepath_p'.$numDocument;
							$num_p       = 'num_p'.$numDocument;
							if(isset($request->$path_p))
							{
								for($doc=0; $doc < count($request->$path_p); $doc++)
								{
									$documents = new App\DocumentsPartials();
									$nameDoc   = $request->$path_p;
									$num       = $request->$num_p;
									if($num[$doc]=="0")
									{
										$name = explode('_',$nameDoc[$doc]);
										if($name[0] == $model->idFolio)
										{
											$documents->path = $nameDoc[$doc];
										}
										else
										{
											$new_file_name   = Files::rename($nameDoc[$doc], $model->idFolio);
											$documents->path = $new_file_name;
										}
									}
									else
									{
										$name = explode('_',$nameDoc[$doc]);
										if($name[0] == $model->idFolio)
										{
											$documents->path = $nameDoc[$doc];
										}
										else
										{
											$new_file_name   = Files::rename($nameDoc[$doc], $model->idFolio);
											$documents->path = $new_file_name;
										}
										
									}
									$documents->partial_id    = $partialPayment->id;
									$documents->name          = $request->$name_p[$doc];
									$documents->fiscal_folio  = $request->$folio_p[$doc];
									$documents->ticket_number = $request->$ticket_p[$doc];
									$documents->amount        = $request->$monto_p[$doc];
									$documents->timepath      = $request->$timepath_p[$doc];
									$date                     = Carbon::parse($request->$datepath_p[$doc])->format('Y-m-d');
									$documents->datepath      = $date;
									$documents->save();	
								}
							}
						}
						else
						{
							$error = true;
						}
					}
				}
				else
				{
					if($partial_payment[$i]>0)
					{
						$data = Carbon::parse($partial_date[$i])->format('Y-m-d');
						$partialPayment = PartialPayment::create(
						[
							'payment'        => $partial_payment[$i], 
							'tipe'           => $partial_type[$i], 
							'date_requested' => $data,
							'purchase_id'    => $model->idPurchase,
						]);
						
						$numDocument = $i+1;
						$path_p      = 'path_p'.$numDocument;
						$name_p      = 'name_p'.$numDocument;
						$folio_p     = 'folio_p'.$numDocument;
						$ticket_p    = 'ticket_p'.$numDocument;
						$monto_p     = 'monto_p'.$numDocument;
						$timepath_p  = 'timepath_p'.$numDocument;
						$datepath_p  = 'datepath_p'.$numDocument;
						if(isset($request->$path_p))
						{
							for($doc=0; $doc < count($request->$path_p); $doc++)
							{
								if($request->$path_p[$doc] != "")
								{
									$new_file_name            = Files::rename($request->$path_p[$doc], $model->idFolio);
									$documents                = new App\DocumentsPartials();
									$documents->partial_id    = $partialPayment->id;
									$documents->path          = $new_file_name;
									$documents->name          = $request->$name_p[$doc];
									$documents->fiscal_folio  = $request->$folio_p[$doc];
									$documents->ticket_number = $request->$ticket_p[$doc];
									$documents->amount        = $request->$monto_p[$doc];
									$documents->timepath      = $request->$timepath_p[$doc];
									$date                     = Carbon::parse($request->$datepath_p[$doc])->format('Y-m-d');
									$documents->datepath      = $date;
									$documents->save();
								}
							}
						}
					}
				}
			}
		}
		return $error;
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',29)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$account        = $request->account;
			$name           = $request->name;
			$folio          = $request->folio;
			$status         = $request->status;
			$mindate        = $request->mindate != '' ? $request->mindate: null;
			$maxdate        = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid   = $request->enterpriseid;
			$documents      = $request->documents;
			$provider       = $request->provider;
			$title_request 	= $request->title_request;
			$project_id 	= $request->project_id;
			if(Auth::user()->globalCheck->where('module_id',29)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',29)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$requests	= App\RequestModel::where(function($q) 
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(29)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
				})
				->where(function ($q) 
				{
					$q->whereIn('idDepartment',Auth::user()->inChargeDep(29)->pluck('departament_id'))->orWhereNull('idDepartment');
				})
				->where(function ($q) 
				{
					$q->whereIn('idProject',Auth::user()->inChargeProject(29)->pluck('project_id'))->orWhereNull('idProject');
				})
				->where('kind',1)
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro') 
						{
							$q->whereHas('purchases',function($q)
							{
								$q->whereNotIn('billStatus',['Pendiente','Entregado','No Aplica']);
							});
						}
						else
						{
							$q->whereHas('purchases',function($q) use($documents)
							{
								$q->where('billStatus',$documents);
							});
						}
					}
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status,$title_request,$project_id)
				{
					if ($title_request != "") 
					{
						$query->whereHas('purchases',function($q) use($title_request)
						{
							$q->where('title','LIKE','%'.$title_request.'%');
						});
					}

					if ($project_id != "") 
					{
						$query->where(function($queryE) use ($project_id)
						{
							$queryE->where('request_models.idProject',$project_id)->orWhere('request_models.idProjectR',$project_id);
						});
					}

					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query2) use ($account)
						{	
							$query2->whereIn('request_models.account',$account)->orWhereIn('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->where(function($query) use($name)
						{
							$query->whereHas('requestUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
							})
							->orWhereHas('elaborateUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
							});
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->where(function($query) use($provider)
				{
					if($provider != "") 
					{
						$query->whereHas('purchases', function($q) use($provider)
						{
							$q->whereHas('provider',function($q) use($provider)
							{
								$q->where('businessName','LIKE','%'.$provider.'%')
									->orWhere('rfc','LIKE','%'.$provider.'%');
							});
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return view('administracion.compra.busqueda',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 29,
					'requests'     => $requests,
					'account'      => $account, 
					'name'         => $name, 
					'mindate'      => $mindate,
					'maxdate'      => $maxdate,
					'folio'        => $folio,
					'status'       => $status,
					'enterpriseid' => $enterpriseid,
					'documents'    => $documents,
					'provider'     => $provider,
					'title_request'=> $title_request,
					'project_id' 	=> $project_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getProviders(Request $request)
	{
		if($request->ajax())
		{
			$output    = "";
			$header    = "";
			$footer    = "";
			$paginate  = "";
			$providers = App\Provider::where(function($query) use ($request)
				{
					$query->where('rfc','LIKE','%'.$request->search.'%')
						->orWhere('businessName','LIKE','%'.$request->search.'%');
				})
				->where(function($query) use ($request)
				{
					if (isset($request->provider_data_id) && $request->provider_data_id != "") 
					{
						$query->whereNotIn('provider_data_id',[$request->provider_data_id]);
					}
				})
				->where('status',2)
				->orderBy('idProvider','DESC')
				->paginate(10);
				return view('administracion.compra.form.table-provider',['providers'=>$providers]);
		}
	}
	
	public function getAccount(Request $request)
	{
		if($request->ajax())
		{
			$output 	= "";
			$accounts 	= App\Account::orderNumber()->where('idEnterprise',$request->enterpriseid)
							->where('selectable',1)
							->get();
			if (count($accounts) > 0) 
			{
				return Response($accounts);
			}
		}
	}
	public function unsent(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && ($request->rfc != '' || $request->rfc == '' || $request->rfc == null)) 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert	= "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				$data	= App\Module::find($this->module_id);
				return view('administracion.compra.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 28,
					'alert'     => $alert
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

			$newformat 					= $request->date != "" ? Carbon::parse($request->date)->format('Y-m-d') : $request->date ;
			$t_request					= new App\RequestModel();
			$t_request->kind			= "1";
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $newformat;
			$t_request->status			= "2";
			$t_request->account			= $request->accountid;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idArea			= $request->areaid;
			$t_request->idDepartment	= $request->departmentid;
			$t_request->idProject		= $request->projectid;
			$t_request->code_wbs 		= $request->code_wbs;
			$t_request->code_edt 		= $request->code_edt;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();
			$folio                   = $t_request->folio;
			$kind                    = $t_request->kind;
			$provider_id             = NULL;
			$provider_has_banks_id   = NULL;
			$provider_data_id        = $request->provider_data_id;
			if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
			{
				App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
					'visible'=>'0'
				]);
			}
			if ($request->prov == "nuevo")
			{
				$t_provider_data              = new App\ProviderData();
				$t_provider_data->users_id    = Auth::user()->id;
				$t_provider_data->save();
				$t_provider                   = new App\Provider();
				$t_provider->businessName     = $request->reason;
				$t_provider->beneficiary      = $request->beneficiary;
				$t_provider->phone            = $request->phone;
				$t_provider->rfc              = $rfc;
				$t_provider->contact          = $request->contact;
				$t_provider->commentaries     = $request->other;
				$t_provider->status           = 0;
				$t_provider->users_id         = Auth::user()->id;
				$t_provider->address          = $request->address;
				$t_provider->number           = $request->number;
				$t_provider->colony           = $request->colony;
				$t_provider->postalCode       = $request->cp;
				$t_provider->city             = $request->city;
				$t_provider->state_idstate    = $request->state;
				$t_provider->provider_data_id = $t_provider_data->id;
				$t_provider->save();
				$provider_id                  = $t_provider->idProvider;
				$provider_data_id             = $t_provider->provider_data_id;
				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank                      = new App\ProviderBanks;
						$t_providerBank->provider_idProvider = $provider_id;
						$t_providerBank->banks_idBanks       = $request->bank[$i];
						$t_providerBank->alias               = $request->alias[$i];
						$t_providerBank->account             = $request->account[$i];
						$t_providerBank->branch              = $request->branch_office[$i];
						$t_providerBank->reference           = $request->reference[$i];
						$t_providerBank->clabe               = $request->clabe[$i];
						$t_providerBank->currency            = $request->currency[$i];
						$t_providerBank->iban                = $request->iban[$i];
						$t_providerBank->bic_swift           = $request->bic_swift[$i];
						$t_providerBank->agreement           = $request->agreement[$i];
						$t_providerBank->provider_data_id    = $t_provider_data->id;
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
					$oldProvider = App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName  = $request->reason;
						$oldProvider->beneficiary   = $request->beneficiary;
						$oldProvider->phone         = $request->phone;
						$oldProvider->rfc           = $rfc;
						$oldProvider->contact       = $request->contact;
						$oldProvider->commentaries  = $request->other;
						$oldProvider->status        = 0;
						$oldProvider->users_id      = Auth::user()->id;
						$oldProvider->address       = $request->address;
						$oldProvider->number        = $request->number;
						$oldProvider->colony        = $request->colony;
						$oldProvider->postalCode    = $request->cp;
						$oldProvider->city          = $request->city;
						$oldProvider->state_idstate = $request->state;
						$oldProvider->save();
						$provider_id                = $oldProvider->idProvider;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
											$provider_has_banks_id = $t_providerBank->id;
										}
									}
								}
							}
						}
					}
					else
					{
						$provider_data_id             = $oldProvider->provider_data_id;
						$t_provider                   = new App\Provider();
						$t_provider->businessName     = $request->reason;
						$t_provider->beneficiary      = $request->beneficiary;
						$t_provider->phone            = $request->phone;
						$t_provider->rfc              = $rfc;
						$t_provider->contact          = $request->contact;
						$t_provider->commentaries     = $request->other;
						$t_provider->status           = 0;
						$t_provider->users_id         = Auth::user()->id;
						$t_provider->address          = $request->address;
						$t_provider->number           = $request->number;
						$t_provider->colony           = $request->colony;
						$t_provider->postalCode       = $request->cp;
						$t_provider->city             = $request->city;
						$t_provider->state_idstate    = $request->state;
						$t_provider->provider_data_id = $provider_data_id;
						$t_provider->save();
						$provider_id                  = $t_provider->idProvider;
						$provider_data_id             = $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
					$provider_id           = $request->idProvider;
					$provider_has_banks_id = $request->provider_has_banks_id;
				}
			}
			$subtotales = 0;
			$iva        = 0;
			$taxes      = 0;
			$retentions = 0;
			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional = 'tamountadditional'.$i;
				$tnameamount       = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions += $request->$tamountretention[$d];
						}
					}
				}
				$subtotales += (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva        += $request->tiva[$i];
			}
			$time_title                        = $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
			$new_format_title                  = $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
			$total                             = ($subtotales+$iva+$taxes)-$retentions;
			$t_purchase                        = new App\Purchase();
			$t_purchase->title                 = $request->title;
			$t_purchase->datetitle             = $new_format_title;
			$t_purchase->numberOrder           = $request->numberOrder;
			$t_purchase->reference             = $request->referencePuchase;
			$t_purchase->idProvider            = $provider_id;
			$t_purchase->idFolio               = $folio;
			$t_purchase->idKind                = $kind;
			$t_purchase->notes                 = $request->note;
			$t_purchase->discount              = $request->descuento;
			$t_purchase->paymentMode           = $request->pay_mode;
			$t_purchase->typeCurrency          = $request->type_currency;
			$t_purchase->billstatus            = $request->status_bill;
			$t_purchase->subtotales            = $subtotales;
			$t_purchase->tax                   = $iva;
			$t_purchase->amount                = $total;
			$t_purchase->provider_has_banks_id = $provider_has_banks_id;
			$t_purchase->provider_data_id      = $provider_data_id;
			$t_purchase->save();
			$purchase                          = $t_purchase->idPurchase;
			// HOLA AQUI SE GUARDAN LAS PARCIALIDADES
			$errorPartial = $this->savePartial($t_purchase,$request);
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$new_file_name            = Files::rename($request->realPath[$i],$folio);
						$documents                = new App\DocumentsPurchase();
						$documents->fiscal_folio  = $request->folio_fiscal[$i];
						$documents->ticket_number = $request->num_ticket[$i];
						$documents->amount        = $request->monto[$i];
						$documents->timepath      = $request->timepath[$i];
						$date                     = Carbon::parse($request->datepath[$i])->format('Y-m-d');
						$documents->datepath      = $date;
						$documents->path          = $new_file_name;
						$documents->idPurchase    = $purchase;
						$documents->name          = $request->nameDocument[$i];
						$documents->save();
					}
				}
			}

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase              = new App\DetailPurchase();
				$t_detailPurchase->idPurchase  = $purchase;
				$t_detailPurchase->quantity    = $request->tquanty[$i];
				$t_detailPurchase->unit        = $request->tunit[$i];
				$t_detailPurchase->description = $request->tdescr[$i];
				$t_detailPurchase->unitPrice   = $request->tprice[$i];
				$t_detailPurchase->tax         = $request->tiva[$i];
				$t_detailPurchase->discount    = $request->tdiscount[$i];
				$t_detailPurchase->amount      = $request->tamount[$i];
				$t_detailPurchase->typeTax     = $request->tivakind[$i];
				$t_detailPurchase->subtotal    = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();
				$idDetailPurchase              = $t_detailPurchase->idDetailPurchase;
				$tamountadditional             = 'tamountadditional'.$i;
				$tnameamount                   = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                   = new App\TaxesPurchase();
							$t_taxes->name             = $request->$tnameamount[$d];
							$t_taxes->amount           = $request->$tamountadditional[$d];
							$t_taxes->idDetailPurchase = $idDetailPurchase;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                   = new App\RetentionPurchase();
							$t_retention->name             = $request->$tnameretention[$d];
							$t_retention->amount           = $request->$tamountretention[$d];
							$t_retention->idDetailPurchase = $idDetailPurchase;
							$t_retention->save();
						}
					}
				}
			}
			$alert = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades, favor de revisar su registro.', 'success');" : "swal('', '".Lang::get("messages.request_saved")."', 'success');" ;
			return redirect('administration/purchase')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id)
	{
		if(Auth::user()->module->where('id',29)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			if(Auth::user()->globalCheck->where('module_id',29)->count()>0)
			{
				$global_permission = Auth::user()->globalCheck->where('module_id',29)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$request = App\RequestModel::where('kind',1)
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->find($id);
			if($request != "")
			{
				return view('administracion.compra.seguimiento',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 29,
						'request'   => $request
					]);
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

	public function updateFollow(Request $request, $id)
	{
		// return $request;
		// return $request->$tamountadditional." --- ".$request->$tamountretention;

		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->fiscal == 1 && $request->rfc != '') 
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert = "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				return redirect()->route('purchase.follow.edit',['id'=>$id])->with('alert',$alert);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$newformat 					= $request->date != "" ? Carbon::parse($request->date)->format('Y-m-d') : $request->date ;
			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= "1";
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $newformat;
			//$t_request->status			= 3;
			$t_request->account			= $request->accountid;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idArea			= $request->areaid;
			$t_request->idDepartment	= $request->departmentid;
			$t_request->idProject		= $request->projectid;
			$t_request->code_wbs 		= $request->code_wbs;
			$t_request->code_edt 		= $request->code_edt;
			$t_request->idRequest		= $request->userid;
			$t_request->save();
			if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
			{
				App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
					'visible'=>'0'
				]);
			}
			$folio                 = $t_request->folio;
			$kind                  = $t_request->kind;
			$provider_has_banks_id = NULL;
			$provider_id           = NULL;
			$provider_data_id      = $request->provider_data_id;
			if ($request->prov == "nuevo")
			{
				$t_provider_data              = new App\ProviderData();
				$t_provider_data->users_id    = Auth::user()->id;
				$t_provider_data->save();
				$t_provider                   = new App\Provider();
				$t_provider->businessName     = $request->reason;
				$t_provider->beneficiary      = $request->beneficiary;
				$t_provider->phone            = $request->phone;
				$t_provider->rfc              = $rfc;
				$t_provider->contact          = $request->contact;
				$t_provider->commentaries     = $request->other;
				$t_provider->status           = 2;
				$t_provider->users_id         = Auth::user()->id;
				$t_provider->address          = $request->address;
				$t_provider->number           = $request->number;
				$t_provider->colony           = $request->colony;
				$t_provider->postalCode       = $request->cp;
				$t_provider->city             = $request->city;
				$t_provider->state_idstate    = $request->state;
				$t_provider->provider_data_id = $t_provider_data->id;
				$t_provider->save();
				$provider_id                  = $t_provider->idProvider;
				$provider_data_id             = $t_provider->provider_data_id;
				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank                      = new App\ProviderBanks;
						$t_providerBank->provider_idProvider = $provider_id;
						$t_providerBank->banks_idBanks       = $request->bank[$i];
						$t_providerBank->alias               = $request->alias[$i];
						$t_providerBank->account             = $request->account[$i];
						$t_providerBank->branch              = $request->branch_office[$i];
						$t_providerBank->reference           = $request->reference[$i];
						$t_providerBank->clabe               = $request->clabe[$i];
						$t_providerBank->currency            = $request->currency[$i];
						$t_providerBank->agreement           = $request->agreement[$i];
						$t_providerBank->iban                = $request->iban[$i];
						$t_providerBank->bic_swift           = $request->bic_swift[$i];
						$t_providerBank->provider_data_id    = $t_provider_data->id;
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
					$oldProvider = App\Provider::find($request->idProvider);
					if($oldProvider->status==0)
					{
						$oldProvider->businessName  = $request->reason;
						$oldProvider->beneficiary   = $request->beneficiary;
						$oldProvider->phone         = $request->phone;
						$oldProvider->rfc           = $rfc;
						$oldProvider->contact       = $request->contact;
						$oldProvider->commentaries  = $request->other;
						$oldProvider->status        = 2;
						$oldProvider->users_id      = Auth::user()->id;
						$oldProvider->address       = $request->address;
						$oldProvider->number        = $request->number;
						$oldProvider->colony        = $request->colony;
						$oldProvider->postalCode    = $request->cp;
						$oldProvider->city          = $request->city;
						$oldProvider->state_idstate = $request->state;
						$oldProvider->save();
						$provider_id                = $oldProvider->idProvider;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
						$oldProvider->status          = 1;
						$oldProvider->save();
						$provider_data_id             = $oldProvider->provider_data_id;
						$t_provider                   = new App\Provider();
						$t_provider->businessName     = $request->reason;
						$t_provider->beneficiary      = $request->beneficiary;
						$t_provider->phone            = $request->phone;
						$t_provider->rfc              = $rfc;
						$t_provider->contact          = $request->contact;
						$t_provider->commentaries     = $request->other;
						$t_provider->status           = 2;
						$t_provider->users_id         = Auth::user()->id;
						$t_provider->address          = $request->address;
						$t_provider->number           = $request->number;
						$t_provider->colony           = $request->colony;
						$t_provider->postalCode       = $request->cp;
						$t_provider->city             = $request->city;
						$t_provider->state_idstate    = $request->state;
						$t_provider->provider_data_id = $provider_data_id;
						$t_provider->save();
						$provider_id                  = $t_provider->idProvider;
						$provider_data_id             = $t_provider->provider_data_id;
						if(isset($request->providerBank))
						{
							for ($i=0; $i < count($request->providerBank); $i++)
							{
								if ($request->providerBank[$i] == "x") 
								{	
									$t_providerBank                      = new App\ProviderBanks;
									$t_providerBank->provider_idProvider = $provider_id;
									$t_providerBank->banks_idBanks       = $request->bank[$i];
									$t_providerBank->alias               = $request->alias[$i];
									$t_providerBank->account             = $request->account[$i];
									$t_providerBank->branch              = $request->branch_office[$i];
									$t_providerBank->reference           = $request->reference[$i];
									$t_providerBank->clabe               = $request->clabe[$i];
									$t_providerBank->currency            = $request->currency[$i];
									$t_providerBank->agreement           = $request->agreement[$i];
									$t_providerBank->iban                = $request->iban[$i];
									$t_providerBank->bic_swift           = $request->bic_swift[$i];
									$t_providerBank->provider_data_id    = $oldProvider->provider_data_id;
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
					$provider_id           = $request->idProvider;
					$provider_has_banks_id = $request->provider_has_banks_id;
				}
			}
			$subtotales = 0;
			$iva        = 0;
			$taxes      = 0;
			$retentions = 0;
			for ($i=0; $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;
				$tnameamount 		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}
				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}
			$total      = ($subtotales+$iva+$taxes)-$retentions;
			$purchaseID = App\Purchase::where('idFolio',$folio)->first()->idPurchase;
			App\DetailPurchase::select('idDetailPurchase')->where('idPurchase',$purchaseID)->count()>0 ? $detailID = App\DetailPurchase::select('idDetailPurchase')->where('idPurchase',$purchaseID)->get() : $detailID = null;
			$time_title                        = $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
			$new_format_title                  = $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
			$t_purchase                        = App\Purchase::find($purchaseID);
			$t_purchase->title                 = $request->title;
			$t_purchase->datetitle             = $new_format_title;
			$t_purchase->numberOrder           = $request->numberOrder;
			$t_purchase->reference             = $request->referencePuchase;
			$t_purchase->idProvider            = $provider_id;
			$t_purchase->idFolio               = $folio;
			$t_purchase->idKind                = $kind;
			$t_purchase->notes                 = $request->note;
			$t_purchase->discount              = $request->descuento;
			$t_purchase->paymentMode           = $request->pay_mode;
			$t_purchase->typeCurrency          = $request->type_currency;
			$t_purchase->billstatus            = $request->status_bill;
			$t_purchase->subtotales            = $subtotales;
			$t_purchase->tax                   = $iva;
			$t_purchase->amount                = $total;
			$t_purchase->provider_has_banks_id = $provider_has_banks_id;
			$t_purchase->provider_data_id      = $provider_data_id;
			$t_purchase->save();
			//aqui el regreso
			$errorPartial = $this->savePartial($t_purchase,$request);
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					if ($request->realPath[$i] != "") 
					{
						$new_file_name            = Files::rename($request->realPath[$i],$folio);
						$documents                = new App\DocumentsPurchase();
						$documents->fiscal_folio  = $request->folio_fiscal[$i];
						$documents->ticket_number = $request->num_ticket[$i];
						$documents->amount        = $request->monto[$i];
						$documents->timepath      = $request->timepath[$i];
						$date                     = Carbon::parse($request->datepath[$i])->format('Y-m-d');
						$documents->datepath      = $date;
						$documents->path          = $new_file_name;
						$documents->idPurchase    = $purchaseID;
						$documents->name          = $request->nameDocument[$i];
						$documents->save();
					}
				}
			}

			$deleteTaxes		= $detailID != "" ? App\TaxesPurchase::whereIn('idDetailPurchase',$detailID)->delete() : '';
			$deleteRetentions	= $detailID != "" ? App\RetentionPurchase::whereIn('idDetailPurchase',$detailID)->delete() : '';
			$delete				= $purchaseID != "" ? App\DetailPurchase::where('idPurchase',$purchaseID)->delete() : '';

			for ($i=0; $i < count($request->tamount); $i++)
			{
				$t_detailPurchase               = new App\DetailPurchase();
				$t_detailPurchase->idPurchase   = $purchaseID;
				$t_detailPurchase->category     = $request->tcategory[$i];
				$t_detailPurchase->measurement  = $request->tmeasurement[$i];
				$t_detailPurchase->code         = $request->tcode[$i];
				$t_detailPurchase->commentaries = $request->tcommentaries[$i];
				$t_detailPurchase->quantity     = $request->tquanty[$i];
				$t_detailPurchase->unit         = $request->tunit[$i];
				$t_detailPurchase->description  = $request->tdescr[$i];
				$t_detailPurchase->unitPrice    = $request->tprice[$i];
				$t_detailPurchase->tax          = $request->tiva[$i];
				$t_detailPurchase->discount     = $request->tdiscount[$i];
				$t_detailPurchase->amount       = $request->tamount[$i];
				$t_detailPurchase->typeTax      = $request->tivakind[$i];
				$t_detailPurchase->subtotal     = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();
				$idDetailPurchase               = $t_detailPurchase->idDetailPurchase;
				$tamountadditional              = 'tamountadditional'.$i;
				$tnameamount                    = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                   = new App\TaxesPurchase();
							$t_taxes->name             = $request->$tnameamount[$d];
							$t_taxes->amount           = $request->$tamountadditional[$d];
							$t_taxes->idDetailPurchase = $idDetailPurchase;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                   = new App\RetentionPurchase();
							$t_retention->name             = $request->$tnameretention[$d];
							$t_retention->amount           = $request->$tamountretention[$d];
							$t_retention->idDetailPurchase = $idDetailPurchase;
							$t_retention->save();
						}
					}
				}
			}
			$checkProvider = App\Provider::find($provider_id);
			if ($checkProvider == "" || ($checkProvider != "" && $checkProvider->status != 2)) 
			{
				$alert = "swal('', 'Los datos del proveedor están incompletos, por favor verifique los datos capturados.', 'error');" ;
				return redirect()->route('purchase.follow.edit',['id'=>$id])->with('alert',$alert);
			}
			else
			{
				$t_request->status = 3;
				$t_request->save();
				$provider_data_id   = $checkProvider->provider_data_id;
				$updateProviderData = App\Provider::where('provider_data_id',$provider_data_id)->where('status',2)->where('idProvider','!=',$provider_id)->update(
				[
					'status' => 1,
				]);
			}
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 36);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartment)
								->where('module_id',36);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',36);
						})
						->whereHas('inChargeProjectGet',function($q) use ($t_request)
						{
							$q->where('project_id', $t_request->idProject)
								->where('module_id',36);
						})
						->where('active',1)
						->where('notification',1)
						->get();
			/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
					->join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',36)
					->where('user_has_department.departament_id',$request->departmentid)
					->where('users.active',1)
					->where('users.notification',1)
					->get();*/

			$user = App\User::find($request->userid);
			if ($emails != "" && $t_request->purchases->first()->idRequisition == "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to          = $email->email;
						$kind        = "Compra";
						$status      = "Revisar";
						$date        = Carbon::now();
						$url         = route('purchase.review.edit',['id'=>$id]);
						$subject     = "Solicitud por Revisar";
						$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert 	= "swal('', '".Lang::get("messages.request_sent")."', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
				}
			}

			if($t_request->purchases->first()->idRequisition != "")
			{
					$t_request->status           = 5;
					$t_request->accountR         = $request->accountid;
					$t_request->idEnterpriseR    = $request->enterpriseid;
					$t_request->idDepartamentR   = $request->departmentid;
					$t_request->idAreaR          = $request->areaid;
					$t_request->idProjectR       = $request->projectid;
					$t_request->idCheck          = Auth::user()->id;
					$t_request->checkComment     = "";
					$t_request->reviewDate       = Carbon::now();
					$t_request->idAuthorize      = Auth::user()->id;
					$t_request->authorizeComment = "";
					$t_request->authorizeDate    = Carbon::now();
					$t_request->save();
					//GENERAR SOLICITUDES DE PAPELERIA O COMPUTO DEPENDIENDO DE LA CATEGORIA Y QUE VENGA DE UNA REQUISICION
					$t_request = App\RequestModel::find($id);
					if ($t_request->remittance == 1 && $t_request->goToWarehouse == 1) 
					{
						// SOLICITUDES DE PAPELERIA
						if ($t_request->purchases->first()->detailPurchase->count() > 0) 
						{
							$new_request                   = new App\RequestModel();
							$new_request->idRequisition    = $t_request->purchases->first()->idRequisition;
							$new_request->kind             = 7;
							$new_request->fDate            = Carbon::now();
							$new_request->reviewDate       = Carbon::now();
							$new_request->authorizeDate    = Carbon::now();
							$new_request->status           = 5;
							$new_request->idEnterprise     = $t_request->idEnterpriseR;
							$new_request->idArea           = $t_request->idAreaR;
							$new_request->idDepartment     = $t_request->idDepartamentR;
							$new_request->idProject        = $t_request->idProjectR;
							$new_request->account          = $t_request->accountR;
							$new_request->idEnterpriseR    = $t_request->idEnterpriseR;
							$new_request->idAreaR          = $t_request->idAreaR;
							$new_request->idDepartamentR   = $t_request->idDepartamentR;
							$new_request->idProjectR       = $t_request->idProjectR;
							$new_request->accountR         = $t_request->accountR;
							$new_request->idElaborate      = $t_request->idElaborate;
							$new_request->idRequest        = $t_request->idRequest;
							$new_request->idCheck          = $t_request->idCheck;
							$new_request->idAuthorize      = $t_request->idAuthorize;
							$new_request->checkComment     = "Se generó a partir de la solicitud de compra #".$t_request->new_folio."";
							$new_request->authorizeComment = "Se generó a partir de la solicitud de compra #".$t_request->new_folio."";
							$new_request->code             = rand(10000000,99999999);
							$new_request->new_folio        = $t_request->new_folio;
							$new_request->save();
							$folio                         = $new_request->folio;
							$kind                          = $new_request->kind;
							$time_title                    = $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
							$new_format_title              = $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
							$t_stat                        = new App\Stationery();
							$t_stat->title                 = $t_request->purchases->first()->title;
							$t_stat->datetitle             = $t_request->purchases->first()->new_format_title;
							$t_stat->idFolio               = $folio;
							$t_stat->idKind                = $kind;
							$t_stat->save();
							$idstat                        = $t_stat->idStationery;
							foreach($t_request->purchases->first()->detailPurchase as $detail)
							{ 
								$t_detStat                   = new App\DetailStationery();
								$t_detStat->idDetailPurchase = $detail->idDetailPurchase;
								$t_detStat->quantity         = $detail->quantity;
								$t_detStat->product          = $detail->description;
								$t_detStat->short_code       = $detail->code;
								$t_detStat->long_code        = '';
								$t_detStat->idStat           = $idstat;
								$t_detStat->category         = $detail->category;
								$t_detStat->measurement      = $detail->measurement;
								$t_detStat->commentaries     = $detail->commentaries;
								$t_detStat->iva              = $detail->tax;
								$t_detStat->subtotal         = $detail->subtotal;
								$t_detStat->total            = $detail->amount;
								$t_detStat->save();
							}
						}
					}
				$emailRequest = "";
				$emailRequest = App\User::where('id',$t_request->idElaborate)
					->where('notification',1)
					->get();
				$emailPay = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',90)
					->where('users.active',1)
					->where('users.notification',1)
					->get();
				$user = App\User::find($t_request->idRequest);
				if ($emailRequest != "")
				{
					try
					{
						foreach ($emailRequest as $email)
						{
							$name = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to   = $email->email;
							$kind = "Compra";
							if ($t_request->status == 5)
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date        = Carbon::now();
							$url         = route('purchase.follow.edit',['id'=>$id]);
							$subject     = "Estado de Solicitud";
							$requestUser = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}
				if ($emailPay != "")
				{
					try
					{	
						foreach ($emailPay as $email)
						{
							$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to          = $email->email;
							$kind        = "Compra";
							$status      = "Pendiente";
							$date        = Carbon::now();
							$url         = route('payments.review.edit',['id'=>$id]);
							$subject     = "Solicitud Pendiente de Pago";
							$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}	
			}
			return redirect('administration/purchase')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updatePartialFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$t_request    = App\RequestModel::find($id);
			$folio        = $t_request->folio;
			$purchaseID   = App\Purchase::where('idFolio',$folio)->first()->idPurchase;		
			$t_purchase   = App\Purchase::find($purchaseID);
			$errorPartial = $this->savePartial($t_purchase,$request);
			$alert        = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades, por favor revise su registro.', 'success');" : "swal('', '".Lang::get("messages.request_updated")."', 'success');";
			return redirect()->route('purchase.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateUnsentFollow(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{	
			if ($request->fiscal == 1 && ($request->rfc != '' || $request->rfc == '' || $request->rfc == null))
			{
				$rfc = $request->rfc;
			}	
			elseif ($request->fiscal == 1 && (isset($request->idProvider) && $request->idProvider != '') && (isset($request->rfc) && $request->rfc == '')) 
			{
				$alert = "swal('', 'Lo sentimos ocurrió un problema, la solicitud Fiscal tiene que llevar RFC obligatorio.', 'error');";
				return redirect()->route('purchase.follow.edit',['id'=>$id])->with('alert',$alert);
			}
			elseif($request->fiscal == 0 && $request->rfc != '')
			{
				$rfc = $request->rfc;
			}
			elseif($request->fiscal == 0 && $request->rfc == '')
			{
				$rfc = 'XAXX1'.str_pad(App\Provider::where('rfc','like','%XAXX1%')->count(), 8, "0", STR_PAD_LEFT);
			}

			$newformat 					= $request->date != "" ? Carbon::parse($request->date)->format('Y-m-d') : $request->date ;
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= "1";
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $newformat;
			$t_request->status			= "2";
			$t_request->account			= $request->accountid;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idArea			= $request->areaid;
			$t_request->idDepartment	= $request->departmentid;
			$t_request->idProject		= $request->projectid;
			$t_request->code_wbs 		= $request->code_wbs;
			$t_request->code_edt 		= $request->code_edt;
			$t_request->idRequest		= $request->userid;

			$t_request->save();
			if (isset($request->deleteAccount) && count($request->deleteAccount)>0) 
			{
				App\ProviderBanks::whereIn('id',$request->deleteAccount)->update([
					'visible'=>'0'
				]);
			}
			$folio                 = $t_request->folio;
			$kind                  = $t_request->kind;
			$provider_id           = NULL;
			$provider_has_banks_id = NULL;
			$provider_data_id      = $request->provider_data_id;
			if ($request->prov == "nuevo")
			{
				$t_provider_data              = new App\ProviderData();
				$t_provider_data->users_id    = Auth::user()->id;
				$t_provider_data->save();
				$t_provider                   = new App\Provider();
				$t_provider->businessName     = $request->reason;
				$t_provider->beneficiary      = $request->beneficiary;
				$t_provider->phone            = $request->phone;
				$t_provider->rfc              = $rfc;
				$t_provider->contact          = $request->contact;
				$t_provider->commentaries     = $request->other;
				$t_provider->status           = 0;
				$t_provider->users_id         = Auth::user()->id;
				$t_provider->address          = $request->address;
				$t_provider->number           = $request->number;
				$t_provider->colony           = $request->colony;
				$t_provider->postalCode       = $request->cp;
				$t_provider->city             = $request->city;
				$t_provider->state_idstate    = $request->state;
				$t_provider->provider_data_id = $t_provider_data->id;
				$t_provider->save();
				$provider_id                  = $t_provider->idProvider;
				$provider_data_id             = $t_provider->provider_data_id;
				if(isset($request->providerBank))
				{
					for ($i=0; $i < count($request->providerBank); $i++)
					{
						$t_providerBank							= new App\ProviderBanks;
						$t_providerBank->provider_idProvider	= $provider_id;
						$t_providerBank->banks_idBanks			= $request->bank[$i];
						$t_providerBank->alias 					= $request->alias[$i];
						$t_providerBank->account				= $request->account[$i];
						$t_providerBank->branch					= $request->branch_office[$i];
						$t_providerBank->reference				= $request->reference[$i];
						$t_providerBank->clabe					= $request->clabe[$i];
						$t_providerBank->currency				= $request->currency[$i];
						$t_providerBank->agreement				= $request->agreement[$i];
						$t_providerBank->iban 					= $request->iban[$i];
						$t_providerBank->bic_swift 				= $request->bic_swift[$i];
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
					if($oldProvider != "" && $oldProvider->status==0)
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
									$t_providerBank->alias 					= $request->alias[$i];
									$t_providerBank->account				= $request->account[$i];
									$t_providerBank->branch					= $request->branch_office[$i];
									$t_providerBank->reference				= $request->reference[$i];
									$t_providerBank->clabe					= $request->clabe[$i];
									$t_providerBank->currency				= $request->currency[$i];
									$t_providerBank->agreement				= $request->agreement[$i];
									$t_providerBank->iban 					= $request->iban[$i];
									$t_providerBank->bic_swift 				= $request->bic_swift[$i];
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
						if ($oldProvider != "") 
						{
							$provider_data_id 			= $oldProvider->provider_data_id;

							$t_provider					= new App\Provider();
							$t_provider->businessName	= $request->reason;
							$t_provider->beneficiary	= $request->beneficiary;
							$t_provider->phone			= $request->phone;
							$t_provider->rfc			= $rfc;
							$t_provider->contact		= $request->contact;
							$t_provider->commentaries	= $request->other;
							$t_provider->status			= 0;
							$t_provider->users_id		= Auth::user()->id;
							$t_provider->address		= $request->address;
							$t_provider->number			= $request->number;
							$t_provider->colony			= $request->colony;
							$t_provider->postalCode		= $request->cp;
							$t_provider->city			= $request->city;
							$t_provider->state_idstate	= $request->state;
							$t_provider->provider_data_id	= $provider_data_id;
							$t_provider->save();
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
										$t_providerBank->alias 					= $request->alias[$i];
										$t_providerBank->account				= $request->account[$i];
										$t_providerBank->branch					= $request->branch_office[$i];
										$t_providerBank->reference				= $request->reference[$i];
										$t_providerBank->clabe					= $request->clabe[$i];
										$t_providerBank->currency				= $request->currency[$i];
										$t_providerBank->agreement				= $request->agreement[$i];
										$t_providerBank->iban 					= $request->iban[$i];
										$t_providerBank->bic_swift 				= $request->bic_swift[$i];
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
				}
				else
				{
					$provider_id	= $request->idProvider;
					$provider_has_banks_id 	= $request->provider_has_banks_id;
				}
			}
			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
			{
				$tamountadditional 	= 'tamountadditional'.$i;

				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$taxes 	+= $request->$tamountadditional[$d];
						}
					}
				}

				$tamountretention = 'tamountretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$retentions 	+= $request->$tamountretention[$d];
						}
					}
				}
				$subtotales	+= (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva		+= $request->tiva[$i];
			}
			
			$total		= ($subtotales+$iva+$taxes)-$retentions;
			$purchaseID	= $t_request->purchases->first()->idPurchase;

			App\DetailPurchase::select('idDetailPurchase')->where('idPurchase',$purchaseID)->count()>0 ? $detailID = App\DetailPurchase::select('idDetailPurchase')->where('idPurchase',$purchaseID)->get() : $detailID = null;

			$time_title							= $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
			$new_format_title					= $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
			$t_purchase							= App\Purchase::find($purchaseID);
			$t_purchase->title 					= $request->title;
			$t_purchase->datetitle 				= $new_format_title;
			$t_purchase->numberOrder 			= $request->numberOrder;
			$t_purchase->reference				= $request->referencePuchase;
			$t_purchase->idProvider				= $provider_id;
			$t_purchase->idFolio				= $folio;
			$t_purchase->idKind					= $kind;
			$t_purchase->notes					= $request->note;
			$t_purchase->discount				= $request->descuento;
			$t_purchase->paymentMode			= $request->pay_mode;
			$t_purchase->typeCurrency			= $request->type_currency;
			$t_purchase->billstatus				= $request->status_bill;
			$t_purchase->subtotales				= $subtotales;
			$t_purchase->tax					= $iva;
			$t_purchase->amount					= $total;
			$t_purchase->provider_has_banks_id 	= $provider_has_banks_id;
			$t_purchase->provider_data_id 		= $provider_data_id;
			$t_purchase->save();
			$errorPartial                      = $this->savePartial($t_purchase,$request);
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$extends        = explode('.', $request->realPath[$i]);
					$newName        = 'solicitud#'.$folio.'_'.round(microtime(true) * 1000).$extends[1];
					$destinityRoute = '/docs/purchase/'.$newName;
					//\Storage::move('/docs/purchase/'.$request->realPath[$i],$destinityRoute);
					if ($request->realPath[$i] != "") 
					{
						$new_file_name            = Files::rename($request->realPath[$i],$folio);
						$documents                = new App\DocumentsPurchase();
						$documents->fiscal_folio  = $request->folio_fiscal[$i];
						$documents->ticket_number = $request->num_ticket[$i];
						$documents->amount        = $request->monto[$i];
						$documents->timepath      = $request->timepath[$i];
						$date                     = Carbon::parse($request->datepath[$i])->format('Y-m-d');
						$documents->datepath      = $date;
						$documents->path          = $new_file_name;
						$documents->idPurchase    = $purchaseID;
						$documents->name          = $request->nameDocument[$i];
						$documents->save();
					}
				}
			}


			$deleteTaxes		= $detailID != "" ? App\TaxesPurchase::whereIn('idDetailPurchase',$detailID)->delete() : '';
			$deleteRetentions	= $detailID != "" ? App\RetentionPurchase::whereIn('idDetailPurchase',$detailID)->delete() : '';
			$delete				= $purchaseID != "" ? App\DetailPurchase::where('idPurchase',$purchaseID)->delete() : '';

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailPurchase               = new App\DetailPurchase();
				$t_detailPurchase->idPurchase   = $purchaseID;
				$t_detailPurchase->category     = $request->tcategory[$i];
				$t_detailPurchase->measurement  = $request->tmeasurement[$i];
				$t_detailPurchase->code         = $request->tcode[$i];
				$t_detailPurchase->commentaries = $request->tcommentaries[$i];
				$t_detailPurchase->quantity     = $request->tquanty[$i];
				$t_detailPurchase->unit         = $request->tunit[$i];
				$t_detailPurchase->description  = $request->tdescr[$i];
				$t_detailPurchase->unitPrice    = $request->tprice[$i];
				$t_detailPurchase->tax          = $request->tiva[$i];
				$t_detailPurchase->discount     = $request->tdiscount[$i];
				$t_detailPurchase->amount       = $request->tamount[$i];
				$t_detailPurchase->typeTax      = $request->tivakind[$i];
				$t_detailPurchase->subtotal     = $request->tquanty[$i] * $request->tprice[$i];
				$t_detailPurchase->save();
				$idDetailPurchase               = $t_detailPurchase->idDetailPurchase;
				$tamountadditional              = 'tamountadditional'.$i;
				$tnameamount                    = 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                   = new App\TaxesPurchase();
							$t_taxes->name             = $request->$tnameamount[$d];
							$t_taxes->amount           = $request->$tamountadditional[$d];
							$t_taxes->idDetailPurchase = $idDetailPurchase;
							$t_taxes->save();
						}
					}
				}
				$tamountretention = 'tamountretention'.$i;
				$tnameretention   = 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention                   = new App\RetentionPurchase();
							$t_retention->name             = $request->$tnameretention[$d];
							$t_retention->amount           = $request->$tamountretention[$d];
							$t_retention->idDetailPurchase = $idDetailPurchase;
							$t_retention->save();
						}
					}
				}
			}
			$alert = ($errorPartial)? "swal('Solicitud Guardada', 'Alerta se encontraron inconsitencias en sus parcialidades, por favor de revise su registro.', 'success');" : "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('purchase.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',36)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$account      = $request->account;
			$name         = $request->name;
			$folio        = $request->folio;
			$mindate      = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y',$request->mindate): null;
			$maxdate      = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y',$request->maxdate): null;
			$enterpriseid = $request->enterpriseid;
			$type         = $request->type;
			$provider     = $request->provider;
			$requests     = App\RequestModel::where('kind',1)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(36)->pluck('enterprise_id'))
				->whereIn('idDepartment',Auth::user()->inChargeDep(36)->pluck('departament_id'))
				->whereIn('idProject',Auth::user()->inChargeProject(36)->pluck('project_id'))
				->where('status',3)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid)
				{
					if ($enterpriseid != "") 
					{
						$q->where('idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('account',$account);
					}
					if($name != "")
					{
						$q->whereHas('requestUser',function($q) use ($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$q->where('folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->where(function ($q) use($provider)
				{
					if ($provider != "") 
					{
						$q->whereHas('purchases',function($q) use($provider)
						{
							$q->whereHas('provider', function($q) use($provider)
							{
								$q->where('businessName','LIKE','%'.$provider.'%')->orWhere('rfc','LIKE','%'.$provider.'%');
							});
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.compra.revision',
					[
						'id'           => $data['father'],
						'title'        => $data['name'],
						'details'      => $data['details'],
						'child_id'     => $this->module_id,
						'option_id'    => 36,
						'requests'     => $requests,
						'account'      => $account,
						'name'         => $name,
						'mindate'      => $request->mindate,
						'maxdate'      => $request->maxdate,
						'folio'        => $folio,
						'enterpriseid' => $enterpriseid,
						'type'         => $type,
						'provider'     => $provider,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(36), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',36)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::where('kind',1)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(36)->pluck('enterprise_id'))
								->whereIn('idDepartment',Auth::user()->inChargeDep(36)->pluck('departament_id'))
								->whereIn('idProject',Auth::user()->inChargeProject(36)->pluck('project_id'))
								->find($id);
			if ($request != "")
			{
				return view('administracion.compra.revisioncambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 36,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');"; 
				return redirect('administration/purchase/review')->with('alert',$alert);
			}

		}
		else
		{
			return redirect('/');
		}
	}

	public function updateReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data        = App\Module::find($this->module_id);
			$checkStatus = App\RequestModel::find($id);
			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{
					for ($i=0; $i < count($request->t_idDetailPurchase); $i++) 
					{ 
						$idLabelsAssign = 'idLabelsAssign'.$i;
						if ($request->$idLabelsAssign != "") 
						{
							for ($d=0; $d < count($request->$idLabelsAssign); $d++) 
							{ 
								$labelPurchase                   = new App\LabelDetailPurchase();
								$labelPurchase->idlabels         = $request->$idLabelsAssign[$d];
								$labelPurchase->idDetailPurchase = $request->t_idDetailPurchase[$i];
								$labelPurchase->save();
							}
						}
					}
					$review->status         = $request->status;
					$review->accountR       = $request->accountR;
					$review->idEnterpriseR  = $request->idEnterpriseR;
					$review->idDepartamentR = $request->idDepartmentR;
					$review->idAreaR        = $request->idAreaR;
					$review->idProjectR     = $request->project_id;
					$review->idCheck        = Auth::user()->id;
					$review->checkComment   = $request->checkCommentA;
					$review->reviewDate     = Carbon::now();
					$review->save();
					if ($request->idLabels != "")
					{
						$review->labels()->detach();
						$review->labels()->attach($request->idLabels,array('request_kind'=>'1'));
					}
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 37);
						})
						->whereHas('inChargeDepGet',function($q) use ($review)
						{
							$q->where('departament_id', $review->idDepartamentR)
								->where('module_id',37);
						})
						->whereHas('inChargeEntGet',function($q) use ($review)
						{
							$q->where('enterprise_id', $review->idEnterpriseR)
								->where('module_id',37);
						})
						->whereHas('inChargeProjectGet',function($q) use ($review)
						{
							$q->where('project_id', $review->idProjectR)
								->where('module_id',37);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					/*$emails	= App\User::join('user_has_department','users.id','user_has_department.user_id')
								->join('user_has_modules','users.id','user_has_modules.user_id')
								->where('user_has_modules.module_id',37)
								->where('user_has_department.departament_id',$review->idDepartamentR)
								->where('users.active',1)
								->where('users.notification',1)
								->get();*/
					$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Compra";
								$status      = "Autorizar";
								$date        = Carbon::now();
								$url         = route('purchase.authorization.edit',['id'=>$id]);
								$subject     = "Solicitud por Autorizar";
								$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_ruled_no_mail")."', 'success');";
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review->status       = $request->status;
					$review->idCheck      = Auth::user()->id;
					$review->checkComment = $request->checkCommentR;
					$review->reviewDate   = Carbon::now();
					$review->save();
					$emailRequest         = "";
					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest = App\User::where('id',$review->idElaborate)
							->where('notification',1)
							->get();
					}
					else
					{
						$emailRequest = App\User::where('id',$review->idElaborate)
							->orWhere('id',$review->idRequest)
							->where('notification',1)
							->get();
					}
					if ($emailRequest != "")
					{
						try
						{
							foreach ($emailRequest as $email)
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Compra";
								$status      = "RECHAZADA";
								$date        = Carbon::now();
								$url         = route('purchase.follow.edit',['id'=>$id]);
								$subject     = "Estado de Solicitud";
								$requestUser = null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_ruled_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(36, $alert, 'administration/purchase');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',37)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;
			$type			= $request->type;
			$provider 		= $request->provider;
			$requests		= App\RequestModel::where('kind',1)
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(37)->pluck('enterprise_id'))
				->whereIn('idDepartamentR',Auth::user()->inChargeDep(37)->pluck('departament_id'))
				->whereIn('idProjectR',Auth::user()->inChargeProject(37)->pluck('project_id'))
				->where('status',4)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid)
				{
					if ($enterpriseid != "") 
					{
						$q->where('idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('account',$account);
					}
					if($name != "")
					{
						$q->whereHas('requestUser',function($q) use ($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$q->where('folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->where(function ($q) use($provider)
				{
					if ($provider != "") 
					{
						$q->whereHas('purchases',function($q) use($provider)
						{
							$q->whereHas('provider', function($q) use($provider)
							{
								$q->where('businessName','LIKE','%'.$provider.'%')->orWhere('rfc','LIKE','%'.$provider.'%');
							});
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.compra.autorizacion',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 37,
						'requests'		=> $requests,
						'account'		=> $account,
						'name'			=> $name,
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'enterpriseid'	=> $enterpriseid,
						'type'			=> $type,
						'provider' 		=> $provider,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(37), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if (Auth::user()->module->where('id',37)->count()>0)
		{
			$data    = App\Module::find($this->module_id);
			$request = App\RequestModel::where('kind',1)
				->where('status',4)
				->whereIn('idEnterpriseR',Auth::user()->inChargeEnt(37)->pluck('enterprise_id'))
				->whereIn('idDepartamentR',Auth::user()->inChargeDep(37)->pluck('departament_id'))
				->whereIn('idProjectR',Auth::user()->inChargeProject(37)->pluck('project_id'))
				->find($id);
			if ($request != "")
			{
				return view('administracion.compra.autorizacioncambio',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 37,
						'request'   => $request
					]
				);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				return redirect('administration/purchase/authorization')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',37)->count() > 0)
		{
			$checkStatus    = App\RequestModel::find($id);
			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
			}
			else
			{
				$data                        = App\Module::find($this->module_id);
				$authorize                   = App\RequestModel::find($id);
				$authorize->status           = $request->status;
				$authorize->idAuthorize      = Auth::user()->id;
				$authorize->authorizeComment = $request->authorizeCommentA;
				$authorize->authorizeDate    = Carbon::now();
				$authorize->save();
				$alert                       = "swal('', '".Lang::get("messages.ruled")."', 'success');";
				$requisition_id              = $authorize->purchases->first()->idRequisition;
				if ($request->status == 5) 
				{
					//GENERAR SOLICITUDES DE PAPELERIA O COMPUTO DEPENDIENDO DE LA CATEGORIA Y QUE VENGA DE UNA REQUISICION
					$t_request = App\RequestModel::find($id);
					if ($t_request->remittance == 1 && $t_request->goToWarehouse == 1) 
					{
						// SOLICITUDES DE PAPELERIA
						if ($t_request->purchases->first()->detailPurchase->count() > 0) 
						{
							$new_request                   = new App\RequestModel();
							$new_request->idRequisition    = $requisition_id;
							$new_request->kind             = 7;
							$new_request->fDate            = Carbon::now();
							$new_request->reviewDate       = Carbon::now();
							$new_request->authorizeDate    = Carbon::now();
							$new_request->status           = 5;
							$new_request->idEnterprise     = $t_request->idEnterpriseR;
							$new_request->idArea           = $t_request->idAreaR;
							$new_request->idDepartment     = $t_request->idDepartamentR;
							$new_request->idProject        = $t_request->idProjectR;
							$new_request->account          = $t_request->accountR;
							$new_request->idEnterpriseR    = $t_request->idEnterpriseR;
							$new_request->idAreaR          = $t_request->idAreaR;
							$new_request->idDepartamentR   = $t_request->idDepartamentR;
							$new_request->idProjectR       = $t_request->idProjectR;
							$new_request->accountR         = $t_request->accountR;
							$new_request->idElaborate      = $t_request->idElaborate;
							$new_request->idRequest        = $t_request->idRequest;
							$new_request->idCheck          = $t_request->idCheck;
							$new_request->idAuthorize      = $t_request->idAuthorize;
							$new_request->checkComment     = "Se generó a partir de la solicitud de compra #".$t_request->new_folio."";
							$new_request->authorizeComment = "Se generó a partir de la solicitud de compra #".$t_request->new_folio."";
							$new_request->code             = rand(10000000,99999999);
							$new_request->new_folio        = $t_request->new_folio;
							$new_request->save();
							$folio                         = $new_request->folio;
							$kind                          = $new_request->kind;
							$t_stat                        = new App\Stationery();
							$t_stat->title                 = $t_request->purchases->first()->title;
							$time_title                    = $request->datetitle!= null ? Carbon::parse($request->datetitle) : null;
							$new_format_title              = $request->datetitle!= null ? $time_title->format('Y-m-d') : null;
							$t_stat->datetitle             = $t_request->purchases->first()->new_format_title;
							$t_stat->idFolio               = $folio;
							$t_stat->idKind                = $kind;
							$t_stat->save();
							$idstat                        = $t_stat->idStationery;
							foreach($t_request->purchases->first()->detailPurchase as $detail)
							{ 
								$t_detStat                   = new App\DetailStationery();
								$t_detStat->idDetailPurchase = $detail->idDetailPurchase;
								$t_detStat->quantity         = $detail->quantity;
								$t_detStat->product          = $detail->description;
								$t_detStat->short_code       = $detail->code;
								$t_detStat->long_code        = '';
								$t_detStat->idStat           = $idstat;
								$t_detStat->category         = $detail->category;
								$t_detStat->measurement      = $detail->measurement;
								$t_detStat->commentaries     = $detail->commentaries;
								$t_detStat->iva              = $detail->tax;
								$t_detStat->subtotal         = $detail->subtotal;
								$t_detStat->total            = $detail->amount;
								$t_detStat->save();
							}
						}

						// SOLICITUDES DE COMPUTO
						/*if ($t_request->purchases->first()->detailPurchase->where('category',4)->count() > 0)
						{
							foreach ($t_request->purchases->first()->detailPurchase->where('category',4) as $computerDetail) 
							{
								$new_request                    = new App\RequestModel();
								$new_request->idRequisition		= $requisition_id;
								$new_request->kind				= 6;
								$new_request->fDate				= Carbon::now();
								$new_request->reviewDate 		= Carbon::now();
								$new_request->authorizeDate		= Carbon::now();
								$new_request->status			= 5;

								$new_request->idEnterprise		= $t_request->idEnterpriseR;
								$new_request->idArea			= $t_request->idAreaR;
								$new_request->idDepartment		= $t_request->idDepartamentR;
								$new_request->idProject			= $t_request->idProjectR;
								$new_request->account			= $t_request->accountR;

								$new_request->idEnterpriseR		= $t_request->idEnterpriseR;
								$new_request->idAreaR			= $t_request->idAreaR;
								$new_request->idDepartamentR	= $t_request->idDepartamentR;
								$new_request->idProjectR		= $t_request->idProjectR;
								$new_request->accountR			= $t_request->accountR;

								$new_request->idElaborate		= $t_request->idElaborate;
								$new_request->idRequest			= $t_request->idRequest;
								$new_request->idCheck			= $t_request->idCheck;
								$new_request->idAuthorize		= $t_request->idAuthorize;
								$new_request->checkComment  	= "Se generó a partir de la solicitud de compra #".$id."";
								$new_request->authorizeComment  = "Se generó a partir de la solicitud de compra #".$id."";
								$new_request->code 			 	= rand(10000000,99999999);
								$new_request->save();

								$folio						= $new_request->folio;
								$kind						= $new_request->kind;
								$t_computer					= new App\Computer();
								$t_computer->idFolio		= $folio;
								$t_computer->idKind			= $kind;
								$t_computer->idDetailPurchase		= $computerDetail->idDetailPurchase;
								$t_computer->title 		  	= $computerDetail->description;
								$t_computer->datetitle 	  	= $t_request->purchases->first()->datetitle;
								$t_computer->entry			= 1;
								$t_computer->entry_date		= $t_request->authorizeDate;
								$t_computer->device			= 4;
								$t_computer->position		= App\User::find($t_request->idRequest)->position;
								$t_computer->save();
								$idComputer					= $t_computer->idComputer;
								$t_computer->software()->attach([1]);

								$t_computerAccount  			  = new App\ComputerEmailsAccounts();
								$t_computerAccount->email_account = App\User::find($t_request->idRequest)->email;
								$t_computerAccount->alias_account = App\User::find($t_request->idRequest)->name;
								$t_computerAccount->idComputer 	  = $idComputer;
								$t_computerAccount->save();
							}
						}*/
					}
				}
				$emailRequest = "";
				if ($authorize->idElaborate == $authorize->idRequest) 
				{
					$emailRequest = App\User::where('id',$authorize->idElaborate)
						->where('notification',1)
						->get();
				}
				else
				{
					$emailRequest = App\User::where('id',$authorize->idElaborate)
						->orWhere('id',$authorize->idRequest)
						->where('notification',1)
						->get();
				}
				$emailPay = App\User::join('user_has_modules','users.id','user_has_modules.user_id')
					->where('user_has_modules.module_id',90)
					->where('users.active',1)
					->where('users.notification',1)
					->get();
				$user = App\User::find($authorize->idRequest);
				if ($emailRequest != "")
				{
					try
					{
						foreach ($emailRequest as $email)
						{
							$name = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to   = $email->email;
							$kind = "Compra";
							if ($request->status == 5)
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date        = Carbon::now();
							$url         = route('purchase.follow.edit',['id'=>$id]);
							$subject     = "Estado de Solicitud";
							$requestUser = null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
					}
				}
				if ($request->status == 5)
				{
					if ($emailPay != "")
					{
						try
						{	
							foreach ($emailPay as $email)
							{
								$name        = $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to          = $email->email;
								$kind        = "Compra";
								$status      = "Pendiente";
								$date        = Carbon::now();
								$url         = route('payments.review.edit',['id'=>$id]);
								$subject     = "Solicitud Pendiente de Pago";
								$requestUser = $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', '".Lang::get("messages.request_sent_no_mail")."', 'success');";
						}
					}
				}
			}
			return searchRedirect(37, $alert, 'administration/purchase');
		}
	}

	public function updateBill(Request $request, $id)
	{
		$t_request    = App\RequestModel::find($id);
		$folio        = $t_request->folio;
		$t_purchase   = App\Purchase::where('idFolio',$folio)->first();
		$errorPartial = $this->savePartial($t_purchase,$request);
		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			$purchase               = App\Purchase::where('idFolio',$id)->get();
			$updateBill             = App\Purchase::find($purchase->first()->idPurchase);
			$updateBill->billStatus = $request->status_bill;
			$updateBill->save();
			for ($i=0; $i < count($request->realPath); $i++)
			{
				$date = Carbon::parse($request->datepath[$i])->format('Y-m-d');
				if ($request->realPath[$i] != "") 
				{
					$new_file_name            = Files::rename($request->realPath[$i],$id);
					$documents                = new App\DocumentsPurchase();
					$documents->fiscal_folio  = $request->folio_fiscal[$i];
					$documents->ticket_number = $request->num_ticket[$i];
					$documents->amount        = $request->monto[$i];
					$documents->timepath      = $request->timepath[$i];
					$documents->datepath      = $date;
					$documents->path          = $new_file_name;
					$documents->idPurchase    = $purchase->first()->idPurchase;
					$documents->name          = $request->nameDocument[$i];
					$documents->save();
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect('administration/purchase')->with('alert',$alert);
		}
		else
		{
			$purchase               = App\Purchase::where('idFolio',$id)->get();
			$updateBill             = App\Purchase::find($purchase->first()->idPurchase);
			$updateBill->billStatus = $request->status_bill;
			$updateBill->save();
			$alert 	                = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect('administration/purchase')->with('alert',$alert);
		}

		$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
		return redirect('administration/purchase')->with('alert',$alert);
		
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/purchase/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention            = strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention = 'AdG'.round(microtime(true) * 1000).'_purchaseDoc.';
				$name                 = $nameWithoutExtention.$extention;
				$destinity            = '/docs/purchase/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData            = file_get_contents($request->path);
						$resultData            = \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']     = 'DONE';
						$response['path']      = $name;
						$response['message']   = '';
						$response['extention'] = strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message'] = $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message'] = 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más largo, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message'] = 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					try
					{
						$myTask = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::disk('public')->put('/docs/uncompressed_pdf/'.$name,\File::get($request->path));
						$file = $myTask->addFile(public_path().'/docs/uncompressed_pdf/'.$name);
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($nameWithoutExtention);
						$myTask->download(public_path().'/docs/compressed_pdf');
						\Storage::disk('public')->move('/docs/compressed_pdf/'.$name,$destinity);
						\Storage::disk('public')->delete(['/docs/uncompressed_pdf/'.$name,'/docs/compressed_pdf/'.$name]);
						$response['error']     = 'DONE';
						$response['path']      = $name;
						$response['message']   = '';
						$response['extention'] = $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console'] = $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function downloadDocument($id)
	{
		$request = App\RequestModel::where('kind',1)
			->whereIn('status',[4,5,10,11])
			->whereIn('idEnterprise',Auth::user()->inChargeEnt(29)->pluck('enterprise_id'))
			->whereIn('idDepartment',Auth::user()->inChargeDep(29)->pluck('departament_id'))
			->whereIn('idProject',Auth::user()->inChargeProject(29)->pluck('project_id'))
			->find($id);
		if ($request != "")
		{
			//return view('administracion.compra.documento',['request'=>$request]);
			$pdf = PDF::loadView('administracion.compra.documento',['request'=>$request]);
			return $pdf->download('solicitud_compra_'.$request->folio.'.pdf');
		}
		else
		{
			return redirect('/error');
		}
	}
	
	public function catZip(Request $request)
	{
		if ($request->ajax())
		{
			if($request->search!= '')
			{
				$result = array();
				$clave = App\CatZipCode::where('zip_code','LIKE','%'.$request->search.'%')
				->get();
				foreach ($clave as $c)
				{
					$tempArray['id']	= $c->zip_code;
					$tempArray['text']	= $c->zip_code;
					$result['results'][]	= $tempArray;
				}
				if(count($clave)==0)
				{
					$result['results'] = [];
				}
				return Response($result);
			}
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',29)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',29)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',29)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data             = App\Module::find($this->module_id);
			$account          = $request->account;
			$name             = $request->name;
			$folio            = $request->folio;
			$status           = $request->status;
			$mindate          = $request->mindate != '' ? $request->mindate: null;
			$maxdate          = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid     = $request->enterpriseid;
			$documents        = $request->documents;
			$provider         = $request->provider;
			$title_request 	  = $request->title_request;
			$project_id 	  = $request->project_id;
			$requestsPurchase = App\RequestModel::selectRaw(
					'request_models.folio as folio,
					request_models.idRequisition as idRequisition,
					status_requests.description as status,
					CONCAT(purchases.title," - ",purchases.datetitle) as title,
					purchases.numberOrder as number_order,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					request_enterprise.name as request_enterprise,
					request_direction.name as request_direction,
					request_department.name as request_department,
					request_project.proyectName as request_project,
					CONCAT(request_account.account, " ", request_account.description," (",request_account.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
					review_enterprise.name as review_enterprise,
					review_direction.name as review_direction,
					review_department.name as review_department,
					review_project.proyectName as review_project,
					CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
					IF(request_models.taxPayment = 1, "Fiscal", "No Fiscal") as tax,
					purchase_provider.businessName as provider,
					purchases.reference as reference,
					purchases.paymentMode as payment_way,
					detail_purchases.quantity as detail_quantity,
					detail_purchases.unit as detail_unit,
					detail_purchases.description as detail_description,
					detail_purchases.unitPrice as detail_unit_price,
					detail_purchases.subtotal as detail_subtotal,
					detail_purchases.tax as detail_tax,
					taxes_purchase.taxes_amount as detail_taxes,
					retention_purchase.retention_amount as detail_retentions,
					detail_purchases.amount as detail_amount,
					dp_labels.labels as detail_labels,
					purchases.amount as amount,
					purchases.typeCurrency as currency,
					p.payment_amount as paid_amount
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
						->on('request_models.kind','=','purchases.idKind');
				})
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
				->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->where(function($q) 
				{
					$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(29)->pluck('enterprise_id'))
						->orWhereNull('request_models.idEnterprise');
				})
				->where(function ($q) 
				{
					$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(29)->pluck('departament_id'))
						->orWhereNull('request_models.idDepartment');
				})
				->where(function ($q) 
				{
					$q->whereIn('request_models.idProject',Auth::user()->inChargeProject(29)->pluck('project_id'))
						->orWhereNull('request_models.idProject');
				})
				->where('request_models.kind',1)
				->where(function($q) use ($documents)
				{
					if ($documents != '') 
					{
						if ($documents == 'Otro')
						{
							$q->whereHas('purchases',function($q)
							{
								$q->whereNotIn('billStatus',['Pendiente','Entregado','No Aplica']);
							});
						}
						else
						{
							$q->whereHas('purchases',function($q) use($documents)
							{
								$q->where('billStatus',$documents);
							});
						}
					}
				})
				->where(function ($q) use ($global_permission)
				{
					if ($global_permission == 0) 
					{
						$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
					}
				})
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status,$title_request,$project_id)
				{
					if ($title_request != "") 
					{
						$query->whereHas('purchases',function($q) use($title_request)
						{
							$q->where('title','LIKE','%'.$title_request.'%');
						});
					}
					if ($project_id != "") 
					{
						$query->where(function($queryE) use ($project_id)
						{
							$queryE->where('request_models.idProject',$project_id)->orWhere('request_models.idProjectR',$project_id);
						});
					}
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
						});
					}
					if($account != "")
					{
						$query->where(function($query2) use ($account)
						{
							$query2->whereIn('request_models.account',$account)->orWhereIn('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->where(function($query) use($name)
						{
							$query->whereHas('requestUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
							})
							->orWhereHas('elaborateUser', function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
							});
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->whereIn('request_models.status',$status);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->where(function($query) use($provider)
				{
					if($provider != "") 
					{
						$query->whereHas('purchases', function($q) use($provider)
						{
							$q->whereHas('provider',function($q) use($provider)
							{
								$q->where('businessName','LIKE','%'.$provider.'%')
									->orWhere('rfc','LIKE','%'.$provider.'%');
							});
						});
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->get();
			if(count($requestsPurchase)==0 || is_null($requestsPurchase))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Seguimiento-de-compras.xlsx');
			$mainHeaderArr = ['Datos de la solicitud','','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
			{
				if($k <= 4)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
				elseif($k <= 19)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol3);
				}
				elseif($k <= 21)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['Folio','Folio de Requisición','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a Pagar','Moneda','Total pagado'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
			{
				if($k <= 4)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
				}
				elseif($k <= 12)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
				}
				elseif($k <= 19)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol3);
				}
				elseif($k <= 21)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requestsPurchase as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
					if($request->paid_amount == '')
					{
						$request->paid_amount = '0.0';
					}
				}
				else
				{
					$request->folio              = null;
					$request->idRequisition      = '';
					$request->status             = '';
					$request->title              = '';
					$request->number_order       = '';
					$request->request_user       = '';
					$request->elaborate_user     = '';
					$request->date               = '';
					$request->request_enterprise = '';
					$request->request_direction  = '';
					$request->request_department = '';
					$request->request_project    = '';
					$request->request_account    = '';
					$request->review_user        = '';
					$request->review_date        = '';
					$request->review_enterprise  = '';
					$request->review_direction   = '';
					$request->review_department  = '';
					$request->review_project     = '';
					$request->review_account     = '';
					$request->authorize_user     = '';
					$request->authorize_date     = '';
					$request->tax                = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->payment_way        = '';
					$request->amount             = '';
					$request->currency           = '';
					$request->paid_amount        = null;
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k,['amount','detail_unit_price','detail_subtotal', 'detail_tax', 'detail_taxes', 'detail_retentions', 'detail_amount', 'paid_amount']))
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
					elseif($k == 'detail_quantity')
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
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportReview(Request $request)
	{
		if(Auth::user()->module->where('id',36)->count()>0)
		{
			$data             = App\Module::find($this->module_id);
			$account          = $request->account;
			$name             = $request->name;
			$folio            = $request->folio;
			$mindate          = $request->mindate != '' ? $request->mindate: null;
			$maxdate          = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid     = $request->enterpriseid;
			$type             = $request->type;
			$provider         = $request->provider;

			$requestsPurchase = App\RequestModel::selectRaw(
					'request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchases.title," - ",purchases.datetitle) as title,
					purchases.numberOrder as number_order,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					request_enterprise.name as request_enterprise,
					request_direction.name as request_direction,
					request_department.name as request_department,
					request_project.proyectName as request_project,
					CONCAT(request_account.account, " ", request_account.description," (",request_account.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
					review_enterprise.name as review_enterprise,
					review_direction.name as review_direction,
					review_department.name as review_department,
					review_project.proyectName as review_project,
					CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
					IF(request_models.taxPayment = 1, "Fiscal", "No Fiscal") as tax,
					purchase_provider.businessName as provider,
					purchases.reference as reference,
					purchases.paymentMode as payment_way,
					detail_purchases.quantity as detail_quantity,
					detail_purchases.unit as detail_unit,
					detail_purchases.description as detail_description,
					detail_purchases.unitPrice as detail_unit_price,
					detail_purchases.subtotal as detail_subtotal,
					detail_purchases.tax as detail_tax,
					taxes_purchase.taxes_amount as detail_taxes,
					retention_purchase.retention_amount as detail_retentions,
					detail_purchases.amount as detail_amount,
					dp_labels.labels as detail_labels,
					purchases.amount as amount,
					purchases.typeCurrency as currency
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
						->on('request_models.kind','=','purchases.idKind');
				})
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
				->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->where('request_models.kind',1)
				->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(36)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartment',Auth::user()->inChargeDep(36)->pluck('departament_id'))
				->whereIn('request_models.idProject',Auth::user()->inChargeProject(36)->pluck('project_id'))
				->where('request_models.status',3)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid,$provider)
				{
					if ($enterpriseid != "") 
					{
						$q->where('request_models.idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('request_models.account',$account);
					}
					if($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if($provider != "") 
					{
						$q->where('purchase_provider.businessName','LIKE','%'.$provider.'%')
							->orWhere('purchase_provider.rfc','LIKE','%'.$provider.'%');
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->get();
			if(count($requestsPurchase)==0 || is_null($requestsPurchase))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-de-compras.xlsx');
			$mainHeaderArr = ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
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
				elseif($k <= 20)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a Pagar','Moneda'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
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
				elseif($k <= 20)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requestsPurchase as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
					$request->status             = '';
					$request->title              = '';
					$request->number_order       = '';
					$request->request_user       = '';
					$request->elaborate_user     = '';
					$request->date               = '';
					$request->request_enterprise = '';
					$request->request_direction  = '';
					$request->request_department = '';
					$request->request_project    = '';
					$request->request_account    = '';
					$request->review_user        = '';
					$request->review_date        = '';
					$request->review_enterprise  = '';
					$request->review_direction   = '';
					$request->review_department  = '';
					$request->review_project     = '';
					$request->review_account     = '';
					$request->authorize_user     = '';
					$request->authorize_date     = '';
					$request->tax                = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->payment_way        = '';
					$request->amount             = '';
					$request->currency           = '';
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k,['amount','detail_unit_price','detail_subtotal', 'detail_tax', 'detail_taxes', 'detail_retentions', 'detail_amount']))
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
					elseif($k == 'detail_quantity')
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
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportAuthorize(Request $request)
	{
		if(Auth::user()->module->where('id',37)->count()>0)
		{
			$data             = App\Module::find($this->module_id);
			$account          = $request->account;
			$name             = $request->name;
			$folio            = $request->folio;
			$mindate          = $request->mindate != '' ? $request->mindate: null;
			$maxdate          = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid     = $request->enterpriseid;
			$provider         = $request->provider;
			$requestsPurchase = App\RequestModel::selectRaw(
					'request_models.folio as folio,
					status_requests.description as status,
					CONCAT(purchases.title," - ",purchases.datetitle) as title,
					purchases.numberOrder as number_order,
					CONCAT_WS(" ",request_user.name,request_user.last_name,request_user.scnd_last_name) as request_user,
					CONCAT_WS(" ",elaborate_user.name,elaborate_user.last_name,elaborate_user.scnd_last_name) as elaborate_user,
					DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
					request_enterprise.name as request_enterprise,
					request_direction.name as request_direction,
					request_department.name as request_department,
					request_project.proyectName as request_project,
					CONCAT(request_account.account, " ", request_account.description," (",request_account.content,")") as request_account,
					CONCAT_WS(" ",review_user.name,review_user.last_name,review_user.scnd_last_name) as review_user,
					DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
					review_enterprise.name as review_enterprise,
					review_direction.name as review_direction,
					review_department.name as review_department,
					review_project.proyectName as review_project,
					CONCAT(review_account.account, " ", review_account.description," (",review_account.content,")") as review_account,
					CONCAT_WS(" ",authorize_user.name,authorize_user.last_name,authorize_user.scnd_last_name) as authorize_user,
					DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
					IF(request_models.taxPayment = 1, "Fiscal", "No Fiscal") as tax,
					purchase_provider.businessName as provider,
					purchases.reference as reference,
					purchases.paymentMode as payment_way,
					detail_purchases.quantity as detail_quantity,
					detail_purchases.unit as detail_unit,
					detail_purchases.description as detail_description,
					detail_purchases.unitPrice as detail_unit_price,
					detail_purchases.subtotal as detail_subtotal,
					detail_purchases.tax as detail_tax,
					taxes_purchase.taxes_amount as detail_taxes,
					retention_purchase.retention_amount as detail_retentions,
					detail_purchases.amount as detail_amount,
					dp_labels.labels as detail_labels,
					purchases.amount as amount,
					purchases.typeCurrency as currency
					'
				)
				->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
				->leftJoin('purchases',function($q)
				{
					$q->on('request_models.folio','=','purchases.idFolio')
						->on('request_models.kind','=','purchases.idKind');
				})
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
				->leftJoin('providers as purchase_provider','purchases.idProvider','purchase_provider.idProvider')
				->leftJoin('detail_purchases','purchases.idPurchase','detail_purchases.idPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, GROUP_CONCAT(labels.description SEPARATOR ", ") as labels FROM label_detail_purchases INNER JOIN labels ON label_detail_purchases.idlabels = labels.idlabels GROUP BY idDetailPurchase) AS dp_labels'),'detail_purchases.idDetailPurchase','dp_labels.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as taxes_amount FROM taxes_purchases GROUP BY idDetailPurchase) AS taxes_purchase'),'detail_purchases.idDetailPurchase','taxes_purchase.idDetailPurchase')
				->leftJoin(DB::raw('(SELECT idDetailPurchase, SUM(amount) as retention_amount FROM retention_purchases GROUP BY idDetailPurchase) AS retention_purchase'),'detail_purchases.idDetailPurchase','retention_purchase.idDetailPurchase')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'),function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->where('request_models.kind',1)
				->whereIn('request_models.idEnterpriseR',Auth::user()->inChargeEnt(37)->pluck('enterprise_id'))
				->whereIn('request_models.idDepartamentR',Auth::user()->inChargeDep(37)->pluck('departament_id'))
				->whereIn('request_models.idProjectR',Auth::user()->inChargeProject(37)->pluck('project_id'))
				->where('request_models.status',4)
				->where(function ($q) use ($account, $name, $mindate, $maxdate, $folio, $enterpriseid,$provider)
				{
					if ($enterpriseid != "") 
					{
						$q->where('request_models.idEnterprise',$enterpriseid);
					}
					if($account != "")
					{
						$q->where('request_models.account',$account);
					}
					if($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',request_user.name,request_user.last_name,request_user.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($folio != "")
					{
						$q->where('request_models.folio',$folio);
					}
					if($mindate != "" && $maxdate != "")
					{
						$q->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
					if($provider != "") 
					{
						$q->where('purchase_provider.businessName','LIKE','%'.$provider.'%')
							->orWhere('purchase_provider.rfc','LIKE','%'.$provider.'%');
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->get();
			if(count($requestsPurchase)==0 || is_null($requestsPurchase))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
			$mhStyleCol3    = (new StyleBuilder())->setBackgroundColor('70A03F')->setFontColor(Color::WHITE)->build();
			$mhStyleCol4    = (new StyleBuilder())->setBackgroundColor('5C96D2')->setFontColor(Color::WHITE)->build();
			$mhStyleCol5    = (new StyleBuilder())->setBackgroundColor('B562C1')->setFontColor(Color::WHITE)->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('B1C997')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol4    = (new StyleBuilder())->setBackgroundColor('A6C0E3')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol5    = (new StyleBuilder())->setBackgroundColor('E8B1EC')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-de-compras.xlsx');
			$mainHeaderArr = ['Datos de la solicitud','','','','Datos de solicitante','','','','','','','','Datos de revisión','','','','','','','Datos de autorización','','Datos de la solicitud','','','','','','','','','','','','','','',''];
			$tmpMHArr      = [];
			foreach($mainHeaderArr as $k => $mh)
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
				elseif($k <= 20)
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol4);
				}
				else
				{
					$tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
			$writer->addRow($rowFromValues);
			$headerArr    = ['Folio','Estado de Solicitud','Título','Número de orden','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Revisada por','Fecha de revisión','Empresa','Dirección','Departamento','Proyecto','Clasificación de gasto','Autorizada por','Fecha de autorización','Fiscal/No Fiscal','Proveedor','Referencia','Método de pago','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Etiquetas','Total a Pagar','Moneda'];
			$tmpHeaderArr = [];
			foreach($headerArr as $k => $sh)
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
				elseif($k <= 20)
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol4);
				}
				else
				{
					$tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol5);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requestsPurchase as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio              = null;
					$request->status             = '';
					$request->title              = '';
					$request->number_order       = '';
					$request->request_user       = '';
					$request->elaborate_user     = '';
					$request->date               = '';
					$request->request_enterprise = '';
					$request->request_direction  = '';
					$request->request_department = '';
					$request->request_project    = '';
					$request->request_account    = '';
					$request->review_user        = '';
					$request->review_date        = '';
					$request->review_enterprise  = '';
					$request->review_direction   = '';
					$request->review_department  = '';
					$request->review_project     = '';
					$request->review_account     = '';
					$request->authorize_user     = '';
					$request->authorize_date     = '';
					$request->tax                = '';
					$request->provider           = '';
					$request->reference          = '';
					$request->payment_way        = '';
					$request->amount             = '';
					$request->currency           = '';
				}
				$tmpArr = [];
				foreach($request->toArray() as $k => $r)
				{
					if(in_array($k,['amount','detail_unit_price','detail_subtotal', 'detail_tax', 'detail_taxes', 'detail_retentions', 'detail_amount']))
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
					elseif($k == 'detail_quantity')
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
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function requisitionValidation(Request $request)
	{
		if ($request->ajax()) 
		{
			$idproyect	= $request->idproyect;
			
			$project	= App\Project::find($idproyect);

			if ($project->requisition == 1) 
			{
				return Response('Sí');
			}
			else
			{
				return Response('No');
			}
		}
	}

	public function checkBudget(Request $request)
	{
		if ($request->ajax()) 
		{
			$today 			= Carbon::now();
			$weekOfYear		= $today->weekOfYear;
			$year 			= $today->year;
			$enterprise_id	= $request->enterprise_id;
			$department_id	= $request->department_id;
			$project_id		= $request->project_id;
			$account_id 	= $request->account_id;
			$budget			= App\AdministrativeBudget::where('year',$year)->where('weekOfYear',$weekOfYear)->where('enterprise_id',$enterprise_id)->where('department_id',$department_id)->where('project_id',$project_id)->first();
			if ($budget != "") 
			{
				$mindate		= $budget->initRange;
				$maxdate		= $budget->endRange;
				$amountBudget 	= $budget->check->where('account_id',$account_id)->first()->amount;
				$requests 		= App\RequestModel::selectRaw('
					ROUND(
						IF(request_models.kind = 1, purchases.amount, 
							IF(request_models.kind = 8, resource_details.amount, 
								IF(request_models.kind = 9, refund_details.sAmount, 
									IF(request_models.kind = 18, finances.amount, 
										IF(request_models.kind = 17, purchase_records.total, 
											IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, 
												IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, 
													IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, 
														IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, 
															IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount
															)
														)
													)
												)
											)
										)
									)
								)
							)
						)
						,2) AS amountTotal'
					)
					->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
					->leftJoin('resources','request_models.folio','=','resources.idFolio')
					->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
					->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
					->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
					->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
					->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
					->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
					->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
					->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
					->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
					->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
					->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
					->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
					->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
					->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
					->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
					->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
					->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
					->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
					->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
					->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
					->leftJoin('finances','request_models.folio','=','finances.idFolio')
					->whereIn('request_models.kind',[1,8,9,16,17,18])
					->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
					->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').''])
					->where( function($query) use ($enterprise_id) 
					{
						if($enterprise_id != null)
						{
							$query->where('request_models.idEnterprise',$enterprise_id)->orWhere('worker_datas.enterprise',$enterprise_id);
						}
					})
					->where( function($query) use ($project_id) 
					{
						if($project_id != null)
						{
							$query->where('request_models.idProject',$project_id)->orWhere('worker_datas.project',$project_id);
						}
					})
					->where( function($query) use ($department_id) 
					{
						if($department_id != null)
						{
							$query->where('request_models.idDepartment',$department_id);
						}
					})
					->where( function($query) use ($account_id) 
					{
						if($account_id != null)
						{
							$query->where('request_models.account',$account_id)->orWhere('worker_datas.account',$account_id)->orWhere('refund_details.idAccount',$account_id)->orWhere('resource_details.idAccAcc',$account_id);
						}
					})
					->get();
				$amountRequests = $requests->sum('amountTotal');
				$available 		= $amountBudget - $amountRequests;
				$alertPercent 	= $budget->alert_percent;

				if ($available > 0) 
				{
					$percentSpent = ($amountRequests*100)/($amountBudget);
					if ($percentSpent>=$alertPercent) 
					{
						$alert 	= 'porcentage_excedido';
					}
					else
					{
						$alert 	= 'disponible';
					}
				}
				else
				{
					$alert = 'no_disponible';
				}
				return Response($alert);
			}
			else
			{
				$alert = 'no_encontrado';
				return Response($alert);
			}
		}
	}

	public function exportProvider(Request $request)
	{
		if(Auth::user()->module->where('id',29)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$account        = $request->account;
			$name           = $request->name;
			$folio          = $request->folio;
			$status         = $request->status;
			$mindate        = $request->mindate != '' ? $request->mindate: null;
			$maxdate        = $request->maxdate != '' ? $request->maxdate: null;
			$enterpriseid   = $request->enterpriseid;
			$documents      = $request->documents;
			$provider       = $request->provider;
			$title_request 	= $request->title_request;
			$project_id 	= $request->project_id;
			$nameProject = App\Project::find($project_id)->proyectName;

			$requests       = App\RequestModel::selectRaw('purchases.idProvider as idProvider, providers.rfc, providers.businessName')
				->leftJoin('purchases','purchases.idFolio','request_models.folio')
				->leftJoin('providers','providers.idProvider','purchases.idProvider')
				->where('kind',1)
				->where('request_models.idProject',$project_id)->orWhere('request_models.idProjectR',$project_id)
				->where('providers.businessName','!=',null)
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->groupBy('providers.businessName')
				->get();


			Excel::create('Proveedores Por Proyecto', function($excel) use ($requests,$nameProject)
			{
				$excel->sheet('Proveedores',function($sheet) use ($requests)
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
					
					$sheet->cell('A1:C1', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['ID','RFC','Razón Social']);

					foreach ($requests as $request)
					{
						$sheet->appendRow($request->toArray());
					}
				});
			})
			->export('xlsx');

		}
		else
		{
			return redirect('/');
		}
	}
}
