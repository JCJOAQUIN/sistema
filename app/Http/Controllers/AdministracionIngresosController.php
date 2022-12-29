<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Lang;
use Alert;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notificacion;
use Ilovepdf\CompressTask;
use PDF;
use Excel;
use App\Functions\Files;
use App\RequestHasRequest;
use App\RequestModel;
use Illuminate\Support\Facades\Cookie;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionIngresosController extends Controller
{
	private $module_id = 138;
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
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',139)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('administracion.ingresos.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 139
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newRequest($id,$child = 0)
	{
		if(Auth::user()->module->where('id',139)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',140)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',140)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data			= App\Module::find($this->module_id);
			$requests 		= App\RequestModel::whereIn('status',[5,6,7,10,11,12,13,20])
								->where('kind',10)
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
				return view('administracion.ingresos.alta',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 139,
						'requests'  => $requests,
						'child'     => $child
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

	public function getBanks(Request $request)
	{
		if ($request->ajax()) 
		{
			$table	=	"";
			$banks	=	App\BanksAccounts::whereIn('idEnterprise',$request->idEnterprise)
					->get();
			$countBanks = count($banks);
			if ($countBanks >= 1) 
			{
				$response = (string) view("components.labels.title-divisor",["slot" => "SELECCIONE UNA CUENTA"]);
				$modelHead	=	[];
				$body		=	[];
				$modelBody	=	[];
				$modelHead	=
				[
					[
						["value"	=>	""],
						["value"	=>	"Banco"],
						["value"	=>	"Alias"],
						["value"	=>	"Cuenta"],
						["value"	=>	"Sucursal"],
						["value"	=>	"Referencia"],
						["value"	=>	"CLABE"],
						["value"	=>	"Moneda"],
						["value"	=>	"Convenio"],
					]
				];
				foreach($banks as $bank)
				{
					$body	=
					[
						"classEx"	=>	"tr_banks_body",
						[
							"content"	=>
							[
								[
									"kind"			=> "components.inputs.checkbox",
									"classEx"		=>	"my-2 checkbox",
									"attributeEx"	=>	"id=\"idBA_$bank->idbanksAccounts\" name=\"idbanksAccounts\" value=\"".$bank->idbanksAccounts."\"",
									"classExLabel"	=>	"request-validate",
									"label"			=>	"<span class='icon-check'></span>",
									"radio"			=>	true
								]
							]
						],
						[
							"content"	=>	["label"	=>	$bank->bank->description],
						],
						[
							"content"	=>	["label"	=>	$bank->alias!=null ? $bank->alias : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->account!=null ? $bank->account : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->branch!=null ? $bank->branch : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->reference!=null ? $bank->reference : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->clabe!=null ? $bank->clabe : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->currency!=null ? $bank->currency : '---'],
						],
						[
							"content"	=>	["label"	=>	$bank->agreement!=null ? $bank->agreement : '---'],
						],
					];
					$modelBody[]	=	$body;
				}
				$table	.=html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
				[
					"modelHead"			=>	$modelHead,
					"modelBody"			=>	$modelBody,
					"attributeEx"		=>	"id=\"table2\"",
					"classExBody"		=>	"request-validate text-center",
					"attributeExBody"	=>	"id=\"banks-body\""
				])));
				return Response(html_entity_decode($response.$table));
			}
			else
			{
				$notfound = '<div id="not-found" class="alert alert-danger">NO HAY CUENTAS REGISTRADAS</div>';
				return Response($notfound);
			}
		}
	}

	public function getClients(Request $request)
	{
		if($request->ajax())
		{
			$clients  = App\Clients::where(function($query) use ($request)
			{
				$query->where('rfc','LIKE','%'.$request->search.'%')
				->orWhere('businessName','LIKE','%'.$request->search.'%');
			})
			->where('status',2)
			->get();
			if (count($clients) > 0)
			{
					$table	=	"";

					$modelHead	=	[];
					$body		=	[];
					$modelBody	=	[];
					$modelHead	=
					[
						[
							["value"	=>	"ID",],
							["value"	=>	"Nombre",],
							["value"	=>	"RFC"],
							["value"	=>	"Acción"],
						]
					];
					foreach($clients as $client)
					{
						$providerJSON['client']   = $client;
						$body	=
						[
							[
								"content"	=>	["label"	=>	$client->idClient],
							],
							[
								"content"	=>	["label"	=>	$client->businessName],
							],
							[
								"content"	=>	["label"	=>	$client->rfc],
							],
							[
								"content"	=>
								[
									[
										"kind"			=>	"components.buttons.button",
										"variant"		=>	"success",
										"classEx"		=>	"edit",
										"label"			=>	"Seleccionar",
										"attributeEx"	=>	"type=\"button\" value=\"".$client->idClient."\""
									],
									[
										"kind"			=>	"components.inputs.input-text",
										"attributeEx"	=>	"type=\"hidden\" id=\"client_".$client->idClient."\" value=\"".base64_encode(json_encode($providerJSON))."\""
									]
								],
							],
						];
						$modelBody[]	=	$body;
					}
					$table .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.tables.table',
					[
						"modelHead" => $modelHead,
						"modelBody" => $modelBody,
						"attributeEx" => "id='table-client'",
						"classEx" => "table",
						"classExBody"	=>	"request-validate"
					])));
				return Response(html_entity_decode($table));
			}
			else
			{
				$notfound = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view('components.labels.not-found',["classEx" => "flag-not-found", "text" => "No se encontraron clientes registrados"])));
				return Response($notfound);
			}
		}
	}

	public function validation(Request $request)
	{
		$response = array(
			'valid'		=> false,
			'message'	=> 'El campo es requerido.'
		);
		if($request->reason)
		{
			$exist = App\Clients::where('businessName','LIKE',$request->reason)->where('status',2)->count();
			if($exist>0)
			{
				if(isset($request->oldReason) && $request->oldReason===$request->reason)
				{
					$response = array('valid' => true);
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La razón social ya se encuentra registrada.'
					);
				}
			}
			else
			{
				$response = array('valid' => true);
			}
		}

		if($request->rfc)
		{
			if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
			{
				$exist = App\Clients::where('rfc','LIKE',$request->rfc)->where('status',2)->count();
				if($exist>0 && $request->oldRfc != $request->rfc)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El RFC ya se encuentra registrado.'
					);
				}
				else
				{
					$response = array('valid' => true,'message' => '');
				}
			}
			else
			{
				$response = array(
					'valid'		=> false,
					'message'	=> 'El RFC debe ser válido.'
				);
			}	
		}

		return Response($response);
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 10;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 3;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idProject 		= $request->projectid;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			if ($request->prov == "nuevo")
			{
				$t_client					= new App\Clients();
				$t_client->businessName		= $request->reason;
				$t_client->email			= $request->email;
				$t_client->phone			= $request->phone;
				$t_client->rfc				= $request->rfc;
				$t_client->contact			= $request->contact;
				$t_client->commentaries		= $request->other;
				$t_client->status			= 2;
				$t_client->users_id			= Auth::user()->id; //quien lo dio de alta
				$t_client->address			= $request->address;
				$t_client->number			= $request->number;
				$t_client->colony			= $request->colony;
				$t_client->postalCode		= $request->cp;
				$t_client->city				= $request->city;
				$t_client->state_idstate	= $request->state;
				$t_client->save();

				$client_id                    = $t_client->idClient;
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldClient			= App\Clients::find($request->idClient);
					if($oldClient->status==0)
					{
						$oldClient->businessName	= $request->reason;
						$oldClient->email			= $request->email;
						$oldClient->phone			= $request->phone;
						$oldClient->rfc				= $request->rfc;
						$oldClient->contact			= $request->contact;
						$oldClient->commentaries	= $request->other;
						$oldClient->status			= 2;
						$oldClient->users_id		= Auth::user()->id;
						$oldClient->address			= $request->address;
						$oldClient->number			= $request->number;
						$oldClient->colony			= $request->colony;
						$oldClient->postalCode		= $request->cp;
						$oldClient->city			= $request->city;
						$oldClient->state_idstate	= $request->state;
						$oldClient->save();
						$client_id					= $oldClient->idClient;
					}
					else
					{
						//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
						$oldClient->status			= 1;
						$oldClient->save();
						$t_client					= new App\Clients();
						$t_client->businessName		= $request->reason;
						$t_client->email			= $request->email;
						$t_client->phone			= $request->phone;
						$t_client->rfc				= $request->rfc;
						$t_client->contact			= $request->contact;
						$t_client->commentaries		= $request->other;
						$t_client->status			= 2;
						$t_client->users_id			= Auth::user()->id;
						$t_client->address			= $request->address;
						$t_client->number			= $request->number;
						$t_client->colony			= $request->colony;
						$t_client->postalCode		= $request->cp;
						$t_client->city				= $request->city;
						$t_client->state_idstate	= $request->state;
						$t_client->save();

						$client_id					= $t_client->idClient;
						
					}
				}
				else
				{
					$client_id			= $request->idClient;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
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
			}

			$total						= ($subtotales+$iva+$taxes)-$retentions;
			$t_income					= new App\Income();
			$t_income->title			= $request->title;
			$t_income->datetitle		= $request->datetitle !="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_income->reference		= $request->referenceIncome;
			$t_income->idClient			= $client_id;
			$t_income->idFolio			= $folio;
			$t_income->idKind			= $kind;
			$t_income->notes			= $request->note;
			$t_income->discount			= $request->descuento;
			$t_income->paymentMode		= $request->pay_mode;
			$t_income->typeCurrency		= $request->type_currency;
			$t_income->billstatus		= $request->status_bill;
			$t_income->subtotales		= $subtotales;
			$t_income->tax				= $iva;
			$t_income->amount			= $total;
			$t_income->idbanksAccounts	= $request->idbanksAccounts;
			$t_income->save();

			$income					= $t_income->idIncome;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name = Files::rename($request->realPath[$i],$folio);

					$documents 					= new App\DocumentsIncome();
					$documents->path 			= $new_file_name;
					$documents->name 			= $request->nameDocument[$i];
					$documents->idIncome 		= $income;
					$documents->save();
				}
			}
			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; $i < count($request->tamount); $i++)
				{
					$t_detailIncome					= new App\IncomeDetail();
					$t_detailIncome->idIncome		= $income;
					$t_detailIncome->quantity		= $request->tquanty[$i];
					$t_detailIncome->unit			= $request->tunit[$i];
					$t_detailIncome->description	= $request->tdescr[$i];
					$t_detailIncome->unitPrice		= $request->tprice[$i];
					$t_detailIncome->tax			= $request->tiva[$i];
					$t_detailIncome->discount		= $request->tdiscount[$i];
					$t_detailIncome->amount			= $request->tamount[$i];
					$t_detailIncome->typeTax		= $request->tivakind[$i];
					$t_detailIncome->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailIncome->save();

					$idincomeDetail     = $t_detailIncome->idincomeDetail;
					$tamountadditional 	= 'tamountadditional'.$i;
					$tnameamount 		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes 					= new App\TaxesIncome();
								$t_taxes->name 				= $request->$tnameamount[$d];
								$t_taxes->amount 			= $request->$tamountadditional[$d];
								$t_taxes->idincomeDetail 	= $idincomeDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention 	= 'tamountretention'.$i;
					$tnameretention 	= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\RetentionIncome();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idincomeDetail 	= $idincomeDetail;
								$t_retention->save();
							}
						}
					}
				}
			}

			if($request->has("createChild"))
			{
				RequestHasRequest::create([
					'folio' => $request->createChild,
					'children' => $t_request->folio,
				]);
			}

			$emails = App\User::whereHas('module',function($q)
			{
				$q->where('id', 141);
			})
			->whereHas('inChargeEntGet',function($q) use ($t_request)
			{
				$q->where('enterprise_id', $t_request->idEnterprise)
					->where('module_id',141);
			})
			->where('active',1)
			->where('notification',1)
			->get();

			$user 	=  App\User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Ingresos";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= route('income.review.edit',['id'=>$folio]);
						$subject 		= "Solicitud por Revisar";
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('','".Lang::get("messages.request_sent")."', 'success')";
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
				}
			}
			return redirect('administration/income')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',140)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',140)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',140)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data         = App\Module::find($this->module_id);
			$projectid    = $request->projectid;
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;
			
			$requests = App\RequestModel::where('kind',10)
					->where(function($q) 
					{
						$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(140)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
					})
					->where(function ($q) use ($global_permission)
					{
						if ($global_permission == 0) 
						{
							$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
						}
					})
					->where(function ($query) use ($enterpriseid, $projectid, $name, $mindate, $maxdate, $folio, $status)
					{
						if ($enterpriseid != "") 
						{
							$query->where(function($queryE) use ($enterpriseid)
							{
								$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
							});
						}
						if ($projectid != "")
						{								
							$query->where('request_models.idProject',$projectid);
						}	
						if($name != "")
						{
							$query->whereHas('requestUser',function($q) use($name)
							{
								$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							});
						}
						if($folio != "")
						{
							$query->where('request_models.folio',$folio);
						}
						if($status != "")
						{
							$query->where('request_models.status',$status);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						}
					})
					->orderBy('fDate','DESC')
					->orderBy('folio','DESC')
					->paginate(10);

			return view('administracion.ingresos.busqueda',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 140,
				'requests'		=> $requests,
				'projectid'		=> $projectid, 
				'name'			=> $name, 
				'mindate'		=> $request->mindate,
				'maxdate'		=> $request->maxdate,
				'folio'			=> $folio,
				'status'		=> $status,
				'enterpriseid'	=> $enterpriseid
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow($id)
	{
		if(Auth::user()->module->where('id',140)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',140)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',140)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$data		= App\Module::find($this->module_id);
			$request	= App\RequestModel::where('kind',10)
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
				return view('administracion.ingresos.seguimiento',
					[

						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 140,
						'request'	=> $request
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

	public function unsent(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= new App\RequestModel();
			$t_request->kind			= 10;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 2;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idProject 		= $request->projectid;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			$folio		= $t_request->folio;
			$kind		= $t_request->kind;
			$client_id	= null;

			if ($request->prov == "nuevo")
			{
				$t_client					= new App\Clients();
				$t_client->businessName		= $request->reason;
				$t_client->email			= $request->email;
				$t_client->phone			= $request->phone;
				$t_client->rfc				= $request->rfc;
				$t_client->contact			= $request->contact;
				$t_client->commentaries		= $request->other;
				$t_client->status			= 0;
				$t_client->users_id			= Auth::user()->id; //quien lo dio de alta
				$t_client->address			= $request->address;
				$t_client->number			= $request->number;
				$t_client->colony			= $request->colony;
				$t_client->postalCode		= $request->cp;
				$t_client->city				= $request->city;
				$t_client->state_idstate	= $request->state;
				$t_client->save();

				$client_id                    = $t_client->idClient;
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldClient			= App\Clients::find($request->idClient);
					if($oldClient->status==0)
					{
						$oldClient->businessName	= $request->reason;
						$oldClient->email			= $request->email;
						$oldClient->phone			= $request->phone;
						$oldClient->rfc				= $request->rfc;
						$oldClient->contact			= $request->contact;
						$oldClient->commentaries	= $request->other;
						$oldClient->status			= 0;
						$oldClient->users_id		= Auth::user()->id;
						$oldClient->address			= $request->address;
						$oldClient->number			= $request->number;
						$oldClient->colony			= $request->colony;
						$oldClient->postalCode		= $request->cp;
						$oldClient->city			= $request->city;
						$oldClient->state_idstate	= $request->state;
						$oldClient->save();
						$client_id					= $oldClient->idClient;
					}
					else
					{
						//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
						$oldClient->status			= 1;
						$oldClient->save();
						$t_client					= new App\Clients();
						$t_client->businessName		= $request->reason;
						$t_client->email			= $request->email;
						$t_client->phone			= $request->phone;
						$t_client->rfc				= $request->rfc;
						$t_client->contact			= $request->contact;
						$t_client->commentaries		= $request->other;
						$t_client->status			= 2;
						$t_client->users_id			= Auth::user()->id;
						$t_client->address			= $request->address;
						$t_client->number			= $request->number;
						$t_client->colony			= $request->colony;
						$t_client->postalCode		= $request->cp;
						$t_client->city				= $request->city;
						$t_client->state_idstate	= $request->state;
						$t_client->save();

						$client_id					= $t_client->idClient;
						
					}
				}
				else
				{
					$client_id			= $request->idClient;
				}
			}
			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
				for ($i=0;isset($request->tquanty) && $i < count($request->tquanty); $i++)
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
			}

			$total						= ($subtotales+$iva+$taxes)-$retentions;
			$t_income					= new App\Income();
			$t_income->title			= $request->title;
			$t_income->datetitle		= $request->datetitle !="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_income->reference		= $request->referenceIncome;
			$t_income->idClient			= $client_id;
			$t_income->idFolio			= $folio;
			$t_income->idKind			= $kind;
			$t_income->notes			= $request->note;
			$t_income->discount			= $request->descuento;
			$t_income->paymentMode		= $request->pay_mode;
			$t_income->typeCurrency		= $request->type_currency;
			$t_income->billstatus		= $request->status_bill;
			$t_income->subtotales		= $subtotales;
			$t_income->tax				= $iva;
			$t_income->amount			= $total;
			$t_income->idbanksAccounts	= $request->idbanksAccounts;
			$t_income->save();

			$income					= $t_income->idIncome;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name = Files::rename($request->realPath[$i],$folio);

					$documents 					= new App\DocumentsIncome();
					$documents->path 			= $new_file_name;
					$documents->name 			= $request->nameDocument[$i];
					$documents->idIncome 		= $income;
					$documents->save();
				}
			}

			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
				{
					$t_detailIncome					= new App\IncomeDetail();
					$t_detailIncome->idIncome		= $income;
					$t_detailIncome->quantity		= $request->tquanty[$i];
					$t_detailIncome->unit			= $request->tunit[$i];
					$t_detailIncome->description	= $request->tdescr[$i];
					$t_detailIncome->unitPrice		= $request->tprice[$i];
					$t_detailIncome->tax			= $request->tiva[$i];
					$t_detailIncome->discount		= $request->tdiscount[$i];
					$t_detailIncome->amount			= $request->tamount[$i];
					$t_detailIncome->typeTax		= $request->tivakind[$i];
					$t_detailIncome->subtotal		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailIncome->save();

					$idincomeDetail        = $t_detailIncome->idincomeDetail;
					$tamountadditional	= 'tamountadditional'.$i;
					$tnameamount		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes					= new App\TaxesIncome();
								$t_taxes->name				= $request->$tnameamount[$d];
								$t_taxes->amount			= $request->$tamountadditional[$d];
								$t_taxes->idincomeDetail	= $idincomeDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention	= 'tamountretention'.$i;
					$tnameretention		= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\RetentionIncome();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idincomeDetail 	= $idincomeDetail;
								$t_retention->save();
							}
						}
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			return redirect()->route('income.follow.edit',['id'=>$folio])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateUnsentFollow(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= 10;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 2;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idProject 		= $request->projectid;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			$folio		= $t_request->folio;
			$kind		= $t_request->kind;
			$client_id	= null;

			if ($request->prov == "nuevo")
			{
				$t_client					= new App\Clients();
				$t_client->businessName		= $request->reason;
				$t_client->email			= $request->email;
				$t_client->phone			= $request->phone;
				$t_client->rfc				= $request->rfc;
				$t_client->contact			= $request->contact;
				$t_client->commentaries		= $request->other;
				$t_client->status			= 0;
				$t_client->users_id			= Auth::user()->id; //quien lo dio de alta
				$t_client->address			= $request->address;
				$t_client->number			= $request->number;
				$t_client->colony			= $request->colony;
				$t_client->postalCode		= $request->cp;
				$t_client->city				= $request->city;
				$t_client->state_idstate	= $request->state;
				$t_client->save();

				$client_id                    = $t_client->idClient;
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldClient			= App\Clients::find($request->idClient);
					if($oldClient->status==0)
					{
						$oldClient->businessName	= $request->reason;
						$oldClient->email			= $request->email;
						$oldClient->phone			= $request->phone;
						$oldClient->rfc				= $request->rfc;
						$oldClient->contact			= $request->contact;
						$oldClient->commentaries	= $request->other;
						$oldClient->status			= 0;
						$oldClient->users_id		= Auth::user()->id;
						$oldClient->address			= $request->address;
						$oldClient->number			= $request->number;
						$oldClient->colony			= $request->colony;
						$oldClient->postalCode		= $request->cp;
						$oldClient->city			= $request->city;
						$oldClient->state_idstate	= $request->state;
						$oldClient->save();
						$client_id					= $oldClient->idClient;
					}
					else
					{
						//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
						$oldClient->status			= 1;
						$oldClient->save();
						$t_client					= new App\Clients();
						$t_client->businessName		= $request->reason;
						$t_client->email			= $request->email;
						$t_client->phone			= $request->phone;
						$t_client->rfc				= $request->rfc;
						$t_client->contact			= $request->contact;
						$t_client->commentaries		= $request->other;
						$t_client->status			= 2;
						$t_client->users_id			= Auth::user()->id;
						$t_client->address			= $request->address;
						$t_client->number			= $request->number;
						$t_client->colony			= $request->colony;
						$t_client->postalCode		= $request->cp;
						$t_client->city				= $request->city;
						$t_client->state_idstate	= $request->state;
						$t_client->save();

						$client_id					= $t_client->idClient;
						
					}
				}
				else
				{
					$client_id			= $request->idClient;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;

			$idIncome = App\Income::where('idFolio',$id)->first()->idIncome;

			foreach(App\IncomeDetail::where('idIncome',$idIncome)->get() as $detailID)
			{
				App\TaxesIncome::where('idincomeDetail',$detailID->idincomeDetail)->delete();
				App\RetentionIncome::where('idincomeDetail',$detailID->idincomeDetail)->delete();
				$detailID->delete();
			}

			if(isset($request->tquanty) && count($request->tquanty)>0)
			{
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
			}

			$total						= ($subtotales+$iva+$taxes)-$retentions;
			$t_income					= App\Income::find($idIncome);
			$t_income->title			= $request->title;
			$t_income->datetitle		= $request->datetitle!="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_income->reference		= $request->referenceIncome;
			$t_income->idClient			= $client_id;
			$t_income->idFolio			= $folio;
			$t_income->idKind			= $kind;
			$t_income->notes			= $request->note;
			$t_income->discount			= $request->descuento;
			$t_income->paymentMode		= $request->pay_mode;
			$t_income->typeCurrency		= $request->type_currency;
			$t_income->billstatus		= $request->status_bill;
			$t_income->subtotales		= $subtotales;
			$t_income->tax				= $iva;
			$t_income->amount			= $total;
			$t_income->idbanksAccounts	= $request->idbanksAccounts;
			$t_income->save();
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name = Files::rename($request->realPath[$i],$folio);

					$documents 					= new App\DocumentsIncome();
					$documents->path 			= $new_file_name;
					$documents->name 			= $request->nameDocument[$i];
					$documents->idIncome 		= $idIncome;
					$documents->save();
				}
			}

			if(isset($request->tamount) && count($request->tamount)>0)
			{
				for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
				{
					$t_detailIncome					= new App\IncomeDetail();
					$t_detailIncome->idIncome		= $idIncome;
					$t_detailIncome->quantity		= $request->tquanty[$i];
					$t_detailIncome->unit			= $request->tunit[$i];
					$t_detailIncome->description	= $request->tdescr[$i];
					$t_detailIncome->unitPrice		= $request->tprice[$i];
					$t_detailIncome->tax			= $request->tiva[$i];
					$t_detailIncome->discount		= $request->tdiscount[$i];
					$t_detailIncome->amount			= $request->tamount[$i];
					$t_detailIncome->typeTax		= $request->tivakind[$i];
					$t_detailIncome->subtotal 		= $request->tquanty[$i] * $request->tprice[$i];
					$t_detailIncome->save();

					$idincomeDetail		= $t_detailIncome->idincomeDetail;
					$tamountadditional	= 'tamountadditional'.$i;
					$tnameamount		= 'tnameamount'.$i;
					if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
					{
						for ($d=0; $d < count($request->$tamountadditional); $d++) 
						{ 
							if ($request->$tamountadditional[$d] != "") 
							{
								$t_taxes					= new App\TaxesIncome();
								$t_taxes->name				= $request->$tnameamount[$d];
								$t_taxes->amount			= $request->$tamountadditional[$d];
								$t_taxes->idincomeDetail	= $idincomeDetail;
								$t_taxes->save();
							}
						}
					}

					$tamountretention	= 'tamountretention'.$i;
					$tnameretention		= 'tnameretention'.$i;
					if (isset($request->$tamountretention) && $request->$tamountretention != "") 
					{
						for ($d=0; $d < count($request->$tamountretention); $d++) 
						{ 
							if ($request->$tamountretention[$d] != "") 
							{
								$t_retention 					= new App\RetentionIncome();
								$t_retention->name 				= $request->$tnameretention[$d];
								$t_retention->amount 			= $request->$tamountretention[$d];
								$t_retention->idincomeDetail 	= $idincomeDetail;
								$t_retention->save();
							}
						}
					}
				}
			}
			$alert = "swal('','".Lang::get("messages.request_saved")."', 'success')";
			return redirect()->route('income.follow.edit',['id'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updateFollow(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data						= App\Module::find($this->module_id);
			$t_request					= App\RequestModel::find($id);
			$t_request->kind			= 10;
			$t_request->taxPayment		= $request->fiscal;
			$t_request->fDate			= Carbon::now();
			$t_request->PaymentDate		= $request->date !='' ? Carbon::createFromFormat('d-m-Y',$request->date)->format('Y-m-d') : null;
			$t_request->status			= 3;
			$t_request->idEnterprise	= $request->enterpriseid;
			$t_request->idProject 		= $request->projectid;
			$t_request->idRequest		= $request->userid;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			$folio						= $t_request->folio;
			$kind						= $t_request->kind;

			if ($request->prov == "nuevo")
			{
				$t_client					= new App\Clients();
				$t_client->businessName		= $request->reason;
				$t_client->email			= $request->email;
				$t_client->phone			= $request->phone;
				$t_client->rfc				= $request->rfc;
				$t_client->contact			= $request->contact;
				$t_client->commentaries		= $request->other;
				$t_client->status			= 2;
				$t_client->users_id			= Auth::user()->id; //quien lo dio de alta
				$t_client->address			= $request->address;
				$t_client->number			= $request->number;
				$t_client->colony			= $request->colony;
				$t_client->postalCode		= $request->cp;
				$t_client->city				= $request->city;
				$t_client->state_idstate	= $request->state;
				$t_client->save();

				$client_id                    = $t_client->idClient;
			}
			elseif($request->prov == "buscar")
			{
				if (isset($request->edit))
				{
					$oldClient			= App\Clients::find($request->idClient);
					if($oldClient->status==0)
					{
						$oldClient->businessName	= $request->reason;
						$oldClient->email			= $request->email;
						$oldClient->phone			= $request->phone;
						$oldClient->rfc				= $request->rfc;
						$oldClient->contact			= $request->contact;
						$oldClient->commentaries	= $request->other;
						$oldClient->status			= 2;
						$oldClient->users_id		= Auth::user()->id;
						$oldClient->address			= $request->address;
						$oldClient->number			= $request->number;
						$oldClient->colony			= $request->colony;
						$oldClient->postalCode		= $request->cp;
						$oldClient->city			= $request->city;
						$oldClient->state_idstate	= $request->state;
						$oldClient->save();
						$client_id					= $oldClient->idClient;
					}
					else
					{
						//PROVEEDOR EXISTENTE CAMBIA DE ESTADO POR MODIFICARSE
						$oldClient->status			= 1;
						$oldClient->save();
						$t_client					= new App\Clients();
						$t_client->businessName		= $request->reason;
						$t_client->email			= $request->email;
						$t_client->phone			= $request->phone;
						$t_client->rfc				= $request->rfc;
						$t_client->contact			= $request->contact;
						$t_client->commentaries		= $request->other;
						$t_client->status			= 2;
						$t_client->users_id			= Auth::user()->id;
						$t_client->address			= $request->address;
						$t_client->number			= $request->number;
						$t_client->colony			= $request->colony;
						$t_client->postalCode		= $request->cp;
						$t_client->city				= $request->city;
						$t_client->state_idstate	= $request->state;
						$t_client->save();

						$client_id					= $t_client->idClient;
						
					}
				}
				else
				{
					$client_id			= $request->idClient;
				}
			}

			$subtotales	= 0;
			$iva		= 0;
			$taxes 		= 0;
			$retentions = 0;
			$idIncome = App\Income::where('idFolio',$id)->first()->idIncome;

			foreach(App\IncomeDetail::where('idIncome',$idIncome)->get() as $detailID)
			{
				App\TaxesIncome::where('idincomeDetail',$detailID->idincomeDetail)->delete();
				App\RetentionIncome::where('idincomeDetail',$detailID->idincomeDetail)->delete();
				$detailID->delete();
			}

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

			$total						= ($subtotales+$iva+$taxes)-$retentions;
			$t_income					= App\Income::find($idIncome);
			$t_income->title			= $request->title;
			$t_income->datetitle		= $request->datetitle!="" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$t_income->reference		= $request->referenceIncome;
			$t_income->idClient			= $client_id;
			$t_income->idFolio			= $folio;
			$t_income->idKind			= $kind;
			$t_income->notes			= $request->note;
			$t_income->discount			= $request->descuento;
			$t_income->paymentMode		= $request->pay_mode;
			$t_income->typeCurrency		= $request->type_currency;
			$t_income->billstatus		= $request->status_bill;
			$t_income->subtotales		= $subtotales;
			$t_income->tax				= $iva;
			$t_income->amount			= $total;
			$t_income->idbanksAccounts	= $request->idbanksAccounts;
			$t_income->save();
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name = Files::rename($request->realPath[$i],$folio);

					$documents 					= new App\DocumentsIncome();
					$documents->path 			= $new_file_name;
					$documents->name 			= $request->nameDocument[$i];
					$documents->idIncome 		= $idIncome;
					$documents->save();
				}
			}

			for ($i=0; isset($request->tamount) && $i < count($request->tamount); $i++)
			{
				$t_detailIncome					= new App\IncomeDetail();
				$t_detailIncome->idIncome		= $idIncome;
				$t_detailIncome->quantity		= $request->tquanty[$i];
				$t_detailIncome->unit			= $request->tunit[$i];
				$t_detailIncome->description	= $request->tdescr[$i];
				$t_detailIncome->unitPrice		= $request->tprice[$i];
				$t_detailIncome->tax			= $request->tiva[$i];
				$t_detailIncome->discount		= $request->tdiscount[$i];
				$t_detailIncome->amount			= $request->tamount[$i];
				$t_detailIncome->typeTax		= $request->tivakind[$i];
				$t_detailIncome->subtotal 		= $request->tquanty[$i] * $request->tprice[$i];
				$t_detailIncome->save();

				$idincomeDetail		= $t_detailIncome->idincomeDetail;
				$tamountadditional	= 'tamountadditional'.$i;
				$tnameamount		= 'tnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes					= new App\TaxesIncome();
							$t_taxes->name				= $request->$tnameamount[$d];
							$t_taxes->amount			= $request->$tamountadditional[$d];
							$t_taxes->idincomeDetail	= $idincomeDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention	= 'tamountretention'.$i;
				$tnameretention		= 'tnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention 					= new App\RetentionIncome();
							$t_retention->name 				= $request->$tnameretention[$d];
							$t_retention->amount 			= $request->$tamountretention[$d];
							$t_retention->idincomeDetail 	= $idincomeDetail;
							$t_retention->save();
						}
					}
				}
			}

			
			$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 141);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterprise)
								->where('module_id',141);
						})
						->where('active',1)
						->where('notification',1)
						->get();

			$user 	= App\User::find($request->userid);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
						$to 			= $email->email;
						$kind 			= "Compra";
						$status 		= "Revisar";
						$date 			= Carbon::now();
						$url 			= route('income.review.edit',['id'=>$id]);
						$subject 		= "Solicitud por Revisar";
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
					}
					$alert = "swal('','".Lang::get("messages.request_updated")."', 'success')";
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.request_sent_no_mail")."', 'success')";
				}
			}
			return redirect('administration/income')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function review(Request $request)
	{
		if(Auth::user()->module->where('id',141)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$projectid 	= $request->projectid;
			$name 		= $request->name;
			$folio 		= $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate   	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;

			$requests		= App\RequestModel::where('kind',10)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(141)->pluck('enterprise_id'))
								->where(function ($query) use ($projectid, $name, $mindate, $maxdate, $folio,$enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterprise',$enterpriseid);
									}
									if($projectid != "")
									{
										$query->where('request_models.idProject',$projectid);
									}
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			return response(
				view('administracion.ingresos.revision',
					[

						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 141,
						'requests'	=> $requests,
						'projectid'	=> $projectid,
						'name'		=> $name,
						'mindate'	=> $request->mindate,
						'maxdate'	=> $request->maxdate,
						'folio'		=> $folio,
						'enterpriseid' => $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(141), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showReview($id)
	{
		if(Auth::user()->module->where('id',141)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::where('kind',10)
								->where('status',3)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(141)->pluck('enterprise_id'))
								->find($id);
			if ($request != "")
			{
				return view('administracion.ingresos.revisioncambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 141,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_ruled")."', 'error')";
				$alert = "swal('', 'La solicitud ya fue evaluada en el proceso de Revisión.', 'error');";
				return redirect('administration/income/review')->with('alert',$alert);
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
			$data			= App\Module::find($this->module_id);
			$checkStatus	= App\RequestModel::find($id);

			if ($checkStatus->status == 4 || $checkStatus->status == 6) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == "4")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentA;
					$review->reviewDate		= Carbon::now();
					$review->save();
					$emails = App\User::whereHas('module',function($q)
							{
								$q->where('id', 37);
							})
							->whereHas('inChargeEntGet',function($q) use ($review)
							{
								$q->where('enterprise_id', $review->idEnterpriseR)
									->where('module_id',37);
							})
							->where('active',1)
							->where('notification',1)
							->get();
							
					$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to				= $email->email;
								$kind			= "Ingresos";
								$status			= "Validar Pago";
								$date			= Carbon::now();
								$url			= route('income.authorization.edit',['id'=>$id]);
								$subject		= "Solicitud por Validar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success')";
						}
					}
				}
				elseif ($request->status == "6")
				{
					$review->status			= $request->status;
					$review->idCheck		= Auth::user()->id;
					$review->checkComment	= $request->checkCommentR;
					$review->reviewDate		= Carbon::now();
					$review->save();

					$emailRequest 			= "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
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
								$name			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to				= $email->email;
								$kind			= "Ingresos";
								$status			= "RECHAZADA";
								$date			= Carbon::now();
								$url			= route('income.follow.edit',['id'=>$id]);
								$subject		= "Estado de Solicitud";
								$requestUser	= null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success')";
						}
					}
				}
				
			}
			return searchRedirect(141, $alert, 'administration/income');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if(Auth::user()->module->where('id',142)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$projectid 	= $request->projectid;
			$name 		= $request->name;
			$folio 		= $request->folio;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate   	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid = $request->enterpriseid;

			$requests		= App\RequestModel::where('kind',10)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(142)->pluck('enterprise_id'))
								->where(function ($query) use ($projectid, $name, $mindate, $maxdate, $folio,$enterpriseid)
								{
									if ($enterpriseid != "") 
									{
										$query->where('request_models.idEnterprise',$enterpriseid);
									}
									if($projectid != "")
									{
										$query->where('request_models.idProject',$projectid);
										
									}
									if($name != "")
									{
										$query->where(function($query) use($name)
										{
											$query->whereHas('requestUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											})
											->orWhereHas('elaborateUser', function($queryU) use($name)
											{
												$queryU->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
											});
										});
									}
									if($folio != "")
									{
										$query->where('request_models.folio',$folio);
									}
									if($mindate != "" && $maxdate != "")
									{
										$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
									}
								})
								->orderBy('fDate','DESC')
								->orderBy('folio','DESC')
								->paginate(10);
			return response(
				view('administracion.ingresos.autorizacion',
				[

					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 142,
					'requests'	=> $requests,
					'projectid'	=> $projectid,
					'name'		=> $name,
					'mindate'	=> $request->mindate,
					'maxdate'	=> $request->maxdate,
					'folio'		=> $folio,
					'enterpriseid' => $enterpriseid
				]
			)
			)
			->cookie(
				'urlSearch', storeUrlCookie(142), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAuthorize($id)
	{
		if(Auth::user()->module->where('id',142)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$request		= App\RequestModel::where('kind',10)
								->where('status',4)
								->whereIn('idEnterprise',Auth::user()->inChargeEnt(142)->pluck('enterprise_id'))
								->find($id);
			if ($request != "")
			{
				return view('administracion.ingresos.autorizacioncambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 142,
						'request'	=> $request
					]
				);
			}
			else
			{
				$alert = "swal('','".Lang::get("messages.request_ruled")."', 'error')";

				$alert = "swal('', 'La solicitud ya fue evaluada en el proceso de Autorización.', 'error');";
				return redirect('administration/income/authorization')->with('alert',$alert);
			}

		}
		else
		{
			return redirect('/');
		}
	}

	public function updateAuthorize(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$checkStatus	= App\RequestModel::find($id);

			if ($checkStatus->status == 5 || $checkStatus->status == 7) 
			{
				$alert = "swal('','".Lang::get("messages.request_already_ruled")."', 'error')";
			}
			else
			{
				$review	= App\RequestModel::find($id);
				if ($request->status == 5)
				{
					$review->status				= $request->status;
					$review->idAuthorize		= Auth::user()->id;
					$review->authorizeComment	= $request->checkCommentA;
					$review->authorizeDate		= Carbon::now();
					$review->save();

					$emails = App\User::whereHas('module',function($q)
							{
								$q->where('id', 143);
							})
							->whereHas('inChargeEntGet',function($q) use ($review)
							{
								$q->where('enterprise_id', $review->idEnterpriseR)
									->where('module_id',143);
							})
							->where('active',1)
							->where('notification',1)
							->get();
							
					$user 	= App\User::find($review->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to				= $email->email;
								$kind			= "Ingresos";
								$status			= "Validar Pago";
								$date			= Carbon::now();
								$url			= route('income.projection.edit',['id'=>$id]);
								$subject		= "Solicitud por Validar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success')";
						}
					}
				}
				elseif ($request->status == 7)
				{
					$review->status				= $request->status;
					$review->idAuthorize		= Auth::user()->id;
					$review->authorizeComment	= $request->checkCommentR;
					$review->authorizeDate		= Carbon::now();
					$review->save();

					$emailRequest             = "";

					if ($review->idElaborate == $review->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$review->idElaborate)
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
								$name			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to				= $email->email;
								$kind			= "Ingresos";
								$status			= "RECHAZADA";
								$date			= Carbon::now();
								$url			= route('income.follow.edit',['id'=>$id]);
								$subject		= "Estado de Solicitud";
								$requestUser	= null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('','".Lang::get("messages.request_ruled")."', 'success')";
						}
						catch(\Exception $e)
						{
							$alert = "swal('','".Lang::get("messages.request_ruled_no_mail")."', 'success')";
						}
					}
				}
				
			}
			return searchRedirect(142, $alert, 'administration/income');
		}
		else
		{
			return redirect('/');
		}
	}

	public function downloadDocument($id)
	{
		$request		= App\RequestModel::where('kind',10)
							->whereIn('status',[4,5,10,11,13,14])
							->whereIn('idEnterprise',Auth::user()->inChargeEnt(140)->pluck('enterprise_id'))
							->find($id);

		if ($request != "")
		{
			$pdf = PDF::loadView('administracion.ingresos.documento',['request'=>$request]);
			return $pdf->download('solicitud_ingresos_'.$request->folio.'.pdf');
		}
		else
		{
			return redirect('/error');
		}
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
					\Storage::disk('public')->delete('/docs/income/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/income/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData	= file_get_contents($request->path);
						$resultData	= \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']	= $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']	= 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']	= 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
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
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console']	= $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}
	public function projection(Request $request)
	{
		if(Auth::user()->module->where('id',143)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = App\RequestModel::where('kind',10)
				->whereIn('idEnterprise',Auth::user()->inChargeEnt(143)->pluck('enterprise_id'))
				->where(function ($query) use ($enterpriseid, $account, $name, $mindate, $maxdate, $folio, $status)
				{
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
							$query2->where('request_models.account',$account)->orWhere('request_models.accountR',$account);
						});
					}
					if($name != "")
					{
						$query->whereHas('requestUser', function($queryU) use($name)
						{
							$queryU->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($folio != "")
					{
						$query->where('request_models.folio',$folio);
					}
					if($status != "")
					{
						$query->where('request_models.status',$status);
					}
					else
					{
						$query->whereIn('request_models.status',[5,13,21]);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
					}
				})
				->orderBy('fDate','DESC')
				->orderBy('folio','DESC')
				->paginate(10);
			return response(
				view('administracion.ingresos.ingreso',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 143,
						'requests'		=> $requests,
						'account'		=> $account, 
						'name'			=> $name, 
						'mindate'		=> $request->mindate,
						'maxdate'		=> $request->maxdate,
						'folio'			=> $folio,
						'status'		=> $status,
						'enterpriseid'	=> $enterpriseid
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(143), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportAuthorized(Request $request)
	{
		if(Auth::user()->module->where('id',143)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$account		= $request->account;
			$name			= $request->name;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate		= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = DB::table('request_models')->selectRaw(
						'
							request_models.folio as folio,
							status_requests.description as status,
							incomes.title as title,
							IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
							CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
							CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							requestEnterprise.name as enterprise_name,
							requestProject.proyectName as project_name,
							CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
							DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
							CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
							DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
							clients.businessName as client_name,
							requestEnterprise.name as reasonSocial,
							banks.description as bank_name,
							banks_accounts.alias as alias,
							banks_accounts.account as account,
							banks_accounts.branch as branch,
							banks_accounts.reference as reference,
							banks_accounts.clabe as clabe,
							banks_accounts.currency as currency,
							banks_accounts.agreement as agreement,
							income_details.quantity as quantity,
							income_details.unit as unit,
							income_details.description as concept,
							income_details.unitPrice as unitPrice,
							income_details.subtotal as subtotal,
							income_details.tax as tax,
							IFNULL(taxes_incomes.taxes_amount,0) as taxes,
							IFNULL(retention_incomes.retentions_amount,0) as retentions,
							income_details.amount as amount,
							incomes.amount as totalRequest,
							incomes.amount as totalProjected,
							IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
							IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
							IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
							IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
						')
						->leftJoin('incomes','incomes.idFolio','request_models.folio')
						->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
						->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
						->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
						->leftJoin('clients','clients.idClient','incomes.idClient')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
						->where('request_models.kind',10)
						->whereIn('request_models.status',[5,13,21])
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(143)->pluck('enterprise_id'))
						->where(function ($query) use ($enterpriseid, $name, $mindate, $maxdate, $folio, $status)
						{
							if ($enterpriseid != "") 
							{
								$query->where(function($queryE) use ($enterpriseid)
								{
									$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
								});
							}
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($status != "")
							{
								$query->where('request_models.status',$status);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();

			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Autorización de Proyección de Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->status			= '';
					$request->title				= '';
					$request->tax_payment		= '';
					$request->request_user		= '';
					$request->elaborate_user	= '';
					$request->date				= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->reviewed_user		= '';
					$request->review_date		= '';
					$request->authorized_user	= '';
					$request->authorize_date	= '';
					$request->client_name		= '';
					$request->reasonSocial		= '';
					$request->bank_name			= '';
					$request->alias				= '';
					$request->account			= '';
					$request->branch			= '';
					$request->reference			= '';
					$request->clabe				= '';
					$request->currency			= '';
					$request->agreement			= '';
					$request->totalRequest		= '';
					$request->totalProjected	= '';
					$request->subtotalBill		= '';
					$request->trasBill			= '';
					$request->retBill			= '';
					$request->totalBill			= '';
					$request->subtotalPaid		= '';
					$request->trasPaid			= '';
					$request->retPaid			= '';
					$request->totalPaid			= '';
					$request->incomePendingPay	= '';
					$request->unbilledIncome	= '';
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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

	public function projectionIncome(App\RequestModel $requestModel)
	{
		if(Auth::user()->module->where('id',143)->count()>0 && $requestModel->kind==10 && ($requestModel->status==5 || $requestModel->status==13 || $requestModel->status== 21))
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.ingresos.ingresos',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 143,
						'requestModel'	=> $requestModel
					]
				);

		}
		else
		{
			return abort(404);
		}
	}

	public function projectionIncomeBill(App\RequestModel $requestModel, App\Bill $bill)
	{
		if(Auth::user()->module->where('id',143)->count()>0 && $requestModel->kind==10 && ($requestModel->status==5 || $requestModel->status==13 || $requestModel->status== 21))
		{
			if($requestModel->bill->where('id',$bill->id)->count() > 0)
			{
				$data = App\Module::find($this->module_id);
				$bill->status = null;
				return view('administracion.ingresos.ingresos',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->module_id,
					'option_id'    => 143,
					'requestModel' => $requestModel,
					'bill'         => $bill
				]);
			}
			else
			{
				return abort(404);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function projectionIncomeSave(Request $request, $submodule_id)
	{
		$t_request 	= App\RequestModel::find($request->folio_request);
		switch($t_request->kind)
		{
			case 10:
				$totalRequest = $t_request->income->first()->amount;
				break;
				
			case 11:
				$totalRequest = $t_request->adjustment->first()->amount;
				break;

			case 13:
				$totalRequest = $t_request->purchaseEnterprise->first()->amount;
				break;

			case 14:
				$totalRequest = $t_request->groups->first()->amount;
				break;
		}
		$totalBills = App\Bill::where('folioRequest',$t_request->folio)->whereIn('status',[0,1,2])->where(function($q){$q->where('statusCFDI','Vigente')->orWhereNull('statusCFDI');})->where("type","!=","E")->sum('total') + ($request->cfdi_kind != "E" ? $request->cfdi_total : -($request->cfdi_total));
		if (round($totalBills,2) <= round($totalRequest,2)) 
		{
			$bill          = new App\Bill();
			$bill->version = $request->bill_version;
			$id            = App\Http\Controllers\AdministracionFacturacionController::saveBillCFDI($request,$bill);
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return searchRedirect($submodule_id, $alert, 'back');
		}
		else
		{
			$alert 	= "swal('', 'El total de la suma de las facturas pendientes de timbrar, timbradas y conciliadas, debe coincidir con el total de la solicitud.', 'info');";
			return searchRedirect($submodule_id, $alert, 'back');
		}
	}

	public function projectionIncomeNFSave(Request $request)
	{
		
		$t_request = App\RequestModel::where('folio',$request->folio)->first();

		switch($t_request->kind)
		{
			case 10:
				$totalRequest = $t_request->income->first()->amount;
				break;
				
			case 11:
				$totalRequest = $t_request->adjustment->first()->amount;
				break;

			case 13:
				$totalRequest = $t_request->purchaseEnterprise->first()->amount;
				break;

			case 14:
				$totalRequest = $t_request->groups->first()->amount;
				break;
		}

		$totalBills = $t_request->billNF->sum('total') + $request->cfdi_total;
		if(round($totalRequest,2) < round($totalBills,2))
		{
			$alert 	= "swal('', 'Los ingresos no deben ser mayor al total de la solicitud.', 'error');";
			return searchRedirect(143, $alert, 'back');
		}

		$bill                     = new App\NonFiscalBill();
		$bill->rfc                = $request->rfc_emitter;
		$bill->businessName       = $request->business_name_emitter;
		$bill->clientRfc          = $request->rfc_receiver;
		$bill->clientBusinessName = $request->business_name_receiver;
		$bill->expeditionDate     = Carbon::Now();
		$bill->folio              = $request->folio;
		$bill->conditions         = $request->conditions;
		$bill->paymentMethod      = $request->cfdi_payment_method;
		$bill->paymentWay         = $request->cfdi_payment_way;
		$bill->currency           = 'MXN';
		$bill->subtotal           = $request->subtotal;
		$bill->discount           = $request->discount_cfdi;
		$bill->total              = $request->cfdi_total;
		$bill->save();
		foreach ($request->quantity as $k => $v)
		{
			$details				= new App\NonFiscalBillDetail();
			$details->quantity		= $v;
			$details->description	= $request->description[$k];
			$details->value			= $request->valueCFDI[$k];
			$details->amount		= $request->amount[$k];
			$details->discount		= $request->discount[$k];
			$details->idBill		= $bill->idBill;//ID BILL
			$details->save();
		}
		$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
		return searchRedirect(143, $alert, 'back');
	}

	public function catProdServ(Request $request)
	{
		if ($request->ajax())
		{
			if($request->search!= '')
			{
				$result = array();
				$clave = App\CatProdServ::where('keyProdServ','LIKE','%'.$request->search.'%')
				->orWhere('description','LIKE','%'.$request->search.'%')
				->get();
				foreach ($clave as $c)
				{
					$tempArray['id']	= $c->keyProdServ;
					$tempArray['text']	= $c->keyProdServ.' '.$c->description;
					$result['results'][]	= $tempArray;
				}
				return Response($result);
			}
		}
	}
	public function catUnity(Request $request)
	{
		if ($request->ajax())
		{
			if($request->search!= '')
			{
				$result = array();
				$clave = App\CatUnity::where('keyUnit','LIKE','%'.$request->search.'%')
				->orWhere('name','LIKE','%'.$request->search.'%')
				->get();
				foreach ($clave as $c)
				{
					$tempArray['id']	= $c->keyUnit;
					$tempArray['text']	= $c->keyUnit.' '.$c->name;
					$result['results'][]	= $tempArray;
				}
				return Response($result);
			}
		}
	}

	public function projectionDetail(Request $request)
	{
		if ($request->ajax())
		{
			$type = $request->type;
			if($request->id!= '')
			{
				$bill = App\Bill::find($request->id);
				$req  = App\RequestModel::where('folio',$request->requestModel)->first();
				return view('partials.bill_details',['bill' => $bill,'req' => $req,'type' => '1']);
			}
		}
	}

	public function projectionReplicate(Request $request)
	{
		if ($request->ajax())
		{
			if($request->id!= '')
			{
				$bill = App\Bill::with('billDetail.taxes.cfdiTax')->find($request->id);
				return response($bill);
			}
		}
	}

	public function projectionDetailNf(Request $request)
	{
		if ($request->ajax())
		{
			$type = $request->type;
			if($request->id!= '')
			{
				$bill = App\NonFiscalBill::find($request->id);
				$req  = App\RequestModel::where('folio',$request->requestModel)->first();
				return view('partials.bill_details',['bill' => $bill, 'req' => $req, 'type'=> '2']);
			}
		}
	}

	public function exportFollow(Request $request)
	{
		if(Auth::user()->module->where('id',140)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',140)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',140)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$projectid    = $request->projectid;
			$name         = $request->name;
			$folio        = $request->folio;
			$status       = $request->status;
			$mindate      = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate      = $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid = $request->enterpriseid;
			if ($request->mindate != "") 
			{
				$mindate = date('Y-m-d',strtotime($request->mindate));
				$maxdate = date('Y-m-d',strtotime($request->maxdate));
			}

			$requests = DB::table('request_models')->selectRaw(
						'
							request_models.folio as folio,
							status_requests.description as status,
							incomes.title as title,
							IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
							CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
							CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							requestEnterprise.name as enterprise_name,
							requestProject.proyectName as project_name,
							CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
							DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
							CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
							DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
							clients.businessName as client_name,
							requestEnterprise.name as reasonSocial,
							banks.description as bank_name,
							banks_accounts.alias as alias,
							banks_accounts.account as account,
							banks_accounts.branch as branch,
							banks_accounts.reference as reference,
							banks_accounts.clabe as clabe,
							banks_accounts.currency as currency,
							banks_accounts.agreement as agreement,
							income_details.quantity as quantity,
							income_details.unit as unit,
							income_details.description as concept,
							income_details.unitPrice as unitPrice,
							income_details.subtotal as subtotal,
							income_details.tax as tax,
							IFNULL(taxes_incomes.taxes_amount,0) as taxes,
							IFNULL(retention_incomes.retentions_amount,0) as retentions,
							income_details.amount as amount,
							incomes.amount as totalRequest,
							incomes.amount as totalProjected,
							IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
							IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
							IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
							IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
						')
						->leftJoin('incomes','incomes.idFolio','request_models.folio')
						->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
						->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
						->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
						->leftJoin('clients','clients.idClient','incomes.idClient')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
						->where('request_models.kind',10)
						->where(function ($q) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$q->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
							}
						})
						->where(function($q) 
						{
							$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(140)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
						})
						->where(function ($query) use ($enterpriseid, $projectid, $name, $mindate, $maxdate, $folio, $status)
						{
							if ($enterpriseid != "") 
							{
								$query->where(function($queryE) use ($enterpriseid)
								{
									$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
								});
							}
							if ($projectid != "")
							{								
								$query->where('request_models.idProject',$projectid);
							}	
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($status != "")
							{
								$query->where('request_models.status',$status);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();
			if(count($requests)==0 || is_null($requests))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Seguimiento de Proyección de Ingresosn.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->status			= '';
					$request->title				= '';
					$request->tax_payment		= '';
					$request->request_user		= '';
					$request->elaborate_user	= '';
					$request->date				= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->reviewed_user		= '';
					$request->review_date		= '';
					$request->authorized_user	= '';
					$request->authorize_date	= '';
					$request->client_name		= '';
					$request->reasonSocial		= '';
					$request->bank_name			= '';
					$request->alias				= '';
					$request->account			= '';
					$request->branch			= '';
					$request->reference			= '';
					$request->clabe				= '';
					$request->currency			= '';
					$request->agreement			= '';
					$request->totalRequest		= '';
					$request->totalProjected	= '';
					$request->subtotalBill		= '';
					$request->trasBill			= '';
					$request->retBill			= '';
					$request->totalBill			= '';
					$request->subtotalPaid		= '';
					$request->trasPaid			= '';
					$request->retPaid			= '';
					$request->totalPaid			= '';
					$request->incomePendingPay	= '';
					$request->unbilledIncome	= '';
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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
		if(Auth::user()->module->where('id',141)->count()>0)
		{
			$projectid 		= $request->projectid;
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate    	= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate    	= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = DB::table('request_models')->selectRaw(
						'
							request_models.folio as folio,
							status_requests.description as status,
							incomes.title as title,
							IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
							CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
							CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							requestEnterprise.name as enterprise_name,
							requestProject.proyectName as project_name,
							CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
							DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
							CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
							DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
							clients.businessName as client_name,
							requestEnterprise.name as reasonSocial,
							banks.description as bank_name,
							banks_accounts.alias as alias,
							banks_accounts.account as account,
							banks_accounts.branch as branch,
							banks_accounts.reference as reference,
							banks_accounts.clabe as clabe,
							banks_accounts.currency as currency,
							banks_accounts.agreement as agreement,
							income_details.quantity as quantity,
							income_details.unit as unit,
							income_details.description as concept,
							income_details.unitPrice as unitPrice,
							income_details.subtotal as subtotal,
							income_details.tax as tax,
							IFNULL(taxes_incomes.taxes_amount,0) as taxes,
							IFNULL(retention_incomes.retentions_amount,0) as retentions,
							income_details.amount as amount,
							incomes.amount as totalRequest,
							incomes.amount as totalProjected,
							IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
							IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
							IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
							IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
						')
						->leftJoin('incomes','incomes.idFolio','request_models.folio')
						->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
						->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
						->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
						->leftJoin('clients','clients.idClient','incomes.idClient')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
						->where('request_models.kind',10)
						->where('request_models.status',3)
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(141)->pluck('enterprise_id'))
						->where(function ($query) use ($enterpriseid, $projectid, $name, $mindate, $maxdate, $folio)
						{
							if ($enterpriseid != "") 
							{
								$query->where(function($queryE) use ($enterpriseid)
								{
									$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
								});
							}
							if ($projectid != "")
							{								
								$query->where('request_models.idProject',$projectid);
							}	
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();

			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Revisión de Proyección de Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->status			= '';
					$request->title				= '';
					$request->tax_payment		= '';
					$request->request_user		= '';
					$request->elaborate_user	= '';
					$request->date				= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->reviewed_user		= '';
					$request->review_date		= '';
					$request->authorized_user	= '';
					$request->authorize_date	= '';
					$request->client_name		= '';
					$request->reasonSocial		= '';
					$request->bank_name			= '';
					$request->alias				= '';
					$request->account			= '';
					$request->branch			= '';
					$request->reference			= '';
					$request->clabe				= '';
					$request->currency			= '';
					$request->agreement			= '';
					$request->totalRequest		= '';
					$request->totalProjected	= '';
					$request->subtotalBill		= '';
					$request->trasBill			= '';
					$request->retBill			= '';
					$request->totalBill			= '';
					$request->subtotalPaid		= '';
					$request->trasPaid			= '';
					$request->retPaid			= '';
					$request->totalPaid			= '';
					$request->incomePendingPay	= '';
					$request->unbilledIncome	= '';
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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
		if(Auth::user()->module->where('id',142)->count()>0)
		{
			$projectid 		= $request->projectid;
			$name 			= $request->name;
			$folio 			= $request->folio;
			$mindate    	= $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
			$maxdate    	= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$enterpriseid	= $request->enterpriseid;

			$requests = DB::table('request_models')->selectRaw(
						'
							request_models.folio as folio,
							status_requests.description as status,
							incomes.title as title,
							IF(request_models.taxPayment = 1, "Fiscal","No Fiscal") as tax_payment,
							CONCAT_WS(" ", requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as request_user,
							CONCAT_WS(" ", elaborateUser.name, elaborateUser.last_name, elaborateUser.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y %H:%i") as date,
							requestEnterprise.name as enterprise_name,
							requestProject.proyectName as project_name,
							CONCAT_WS(" ", reviewedUser.name, reviewedUser.last_name, reviewedUser.scnd_last_name) as reviewed_user,
							DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y %H:%i") as review_date,
							CONCAT_WS(" ", authorizedUser.name, authorizedUser.last_name, authorizedUser.scnd_last_name) as authorized_user,
							DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y %H:%i") as authorize_date,
							clients.businessName as client_name,
							requestEnterprise.name as reasonSocial,
							banks.description as bank_name,
							banks_accounts.alias as alias,
							banks_accounts.account as account,
							banks_accounts.branch as branch,
							banks_accounts.reference as reference,
							banks_accounts.clabe as clabe,
							banks_accounts.currency as currency,
							banks_accounts.agreement as agreement,
							income_details.quantity as quantity,
							income_details.unit as unit,
							income_details.description as concept,
							income_details.unitPrice as unitPrice,
							income_details.subtotal as subtotal,
							income_details.tax as tax,
							IFNULL(taxes_incomes.taxes_amount,0) as taxes,
							IFNULL(retention_incomes.retentions_amount,0) as retentions,
							income_details.amount as amount,
							incomes.amount as totalRequest,
							incomes.amount as totalProjected,
							IF(request_models.taxPayment = 1, IFNULL(billed.subtotalBill,0), "No Aplica") as subtotalBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.trasBill,0), "No Aplica") as trasBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.retBill,0), "No Aplica") as retBill,
							IF(request_models.taxPayment = 1, IFNULL(billed.totalBill,0), "No Aplica") as totalBill,
							IF(request_models.taxPayment = 1, IFNULL(paid.subtotalPaid,0), IFNULL(paidNF.subtotalPaid,0)) as subtotalPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.trasPaid,0), "No Aplica") as trasPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.retPaid,0), "No Aplica") as retPaid,
							IF(request_models.taxPayment = 1, IFNULL(paid.totalPaid,0), IFNULL(paidNF.totalPaid,0)) as totalPaid,
							IF(request_models.taxPayment = 1, (incomes.amount - IFNULL(paid.totalPaid,0)), (incomes.amount - IFNULL(paidNF.totalPaid,0))) as incomePendingPay,
							IF(request_models.taxPayment = 1, (incomes.amount - (IFNULL(billed.totalBill,0) + IFNULL(paid.totalPaid,0))), "No Aplica") as unbilledIncome
						')
						->leftJoin('incomes','incomes.idFolio','request_models.folio')
						->leftJoin('income_details','income_details.idIncome','incomes.idIncome')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as taxes_amount FROM taxes_incomes GROUP BY idincomeDetail) AS taxes_incomes'),'taxes_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin(DB::raw('(SELECT idincomeDetail, SUM(amount) as retentions_amount FROM retention_incomes GROUP BY idincomeDetail) AS retention_incomes'),'retention_incomes.idincomeDetail','income_details.idincomeDetail')
						->leftJoin('users as requestUser','requestUser.id','request_models.idRequest')
						->leftJoin('users as elaborateUser','elaborateUser.id','request_models.idElaborate')
						->leftJoin('users as reviewedUser','reviewedUser.id','request_models.idCheck')
						->leftJoin('users as authorizedUser','authorizedUser.id','request_models.idAuthorize')
						->leftJoin('enterprises as requestEnterprise','requestEnterprise.id','request_models.idEnterprise')
						->leftJoin('projects as requestProject','requestProject.idproyect','request_models.idProjectR')
						->leftJoin('status_requests','status_requests.idrequestStatus','request_models.status')
						->leftJoin('banks_accounts','banks_accounts.idbanksAccounts','incomes.idbanksAccounts')
						->leftJoin('banks','banks.idBanks','banks_accounts.idBanks')
						->leftJoin('clients','clients.idClient','incomes.idClient')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalBill, SUM(tras) as trasBill, SUM(ret) as retBill, SUM(total) as totalBill FROM bills WHERE status = 1 GROUP BY folioRequest) AS billed'),'billed.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folioRequest, SUM(subtotal) as subtotalPaid, SUM(tras) as trasPaid, SUM(ret) as retPaid, SUM(total) as totalPaid FROM bills WHERE status = 2 GROUP BY folioRequest) AS paid'),'paid.folioRequest','request_models.folio')
						->leftJoin(DB::raw('(SELECT folio, SUM(subtotal) as subtotalPaid, SUM(total) as totalPaid FROM non_fiscal_bills WHERE status = 1 GROUP BY folio) AS paidNF'),'paidNF.folio','request_models.folio')
						->where('request_models.kind',10)
						->where('request_models.status',4)
						->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(142)->pluck('enterprise_id'))
						->where(function ($query) use ($enterpriseid, $projectid, $name, $mindate, $maxdate, $folio)
						{
							if ($enterpriseid != "") 
							{
								$query->where(function($queryE) use ($enterpriseid)
								{
									$queryE->where('request_models.idEnterprise',$enterpriseid)->orWhere('request_models.idEnterpriseR',$enterpriseid);
								});
							}
							if ($projectid != "")
							{								
								$query->where('request_models.idProject',$projectid);
							}	
							if($name != "")
							{
								$query->where(DB::raw("CONCAT_WS(' ',requestUser.name,requestUser.last_name,requestUser.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
							}
							if($folio != "")
							{
								$query->where('request_models.folio',$folio);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('request_models.fDate','DESC')
						->orderBy('request_models.folio','DESC')
						->get();

			if(count($requests)==0 || $requests==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Autorización de Proyección de Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Solicitudes');

			$headers = ['Reporte de Proyección de Ingresos','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Datos de la solicitud','','','','Datos de solicitante','','','','','Datos de revisión','','Datos de autorización','','Datos Bancarios de Empresa','','','','','','','','','','Datos de la solicitud','','','','','','','','','','Ingresos proyectados','Ingresos facturados','','','','Ingresos Pagados','','','','Ingresos por pagar','Ingresos por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Folio','Estado de Solicitud','Título','Fiscal/No fiscal','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Proyecto','Revisada por','Fecha de revisión','Autorizada por','Fecha de autorización','Cliente','Razón Social','Banco','Alias','Cuenta','Sucursal','Referencia','CLABE','Moneda','Convenio','Cantidad','Unidad','Descripción','Precio Unitario','Subtotal','IVA','Impuesto Adicional','Retenciones','Importe','Importe Total','Monto proyectado','Subtotal','Traslados','Retenciones','Monto facturado','Subtotal','Traslados','Retenciones','Monto pagado','Monto por pagar','Monto por facturar'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->status			= '';
					$request->title				= '';
					$request->tax_payment		= '';
					$request->request_user		= '';
					$request->elaborate_user	= '';
					$request->date				= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->reviewed_user		= '';
					$request->review_date		= '';
					$request->authorized_user	= '';
					$request->authorize_date	= '';
					$request->client_name		= '';
					$request->reasonSocial		= '';
					$request->bank_name			= '';
					$request->alias				= '';
					$request->account			= '';
					$request->branch			= '';
					$request->reference			= '';
					$request->clabe				= '';
					$request->currency			= '';
					$request->agreement			= '';
					$request->totalRequest		= '';
					$request->totalProjected	= '';
					$request->subtotalBill		= '';
					$request->trasBill			= '';
					$request->retBill			= '';
					$request->totalBill			= '';
					$request->subtotalPaid		= '';
					$request->trasPaid			= '';
					$request->retPaid			= '';
					$request->totalPaid			= '';
					$request->incomePendingPay	= '';
					$request->unbilledIncome	= '';
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
					elseif(in_array($k,['unitPrice','subtotal','tax','taxes','retentions','amount','totalRequest','totalProjected','subtotalBill','trasBill','retBill','totalBill','subtotalPaid','trasPaid','retPaid','totalPaid','incomePendingPay','unbilledIncome']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
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
	public function prefactura(App\Bill $bill)
	{
		if($bill->status == 0)
		{
			$pdf	= PDF::loadView('administracion.facturacion.'.$bill->rfc,['bill'=>$bill]);
			return $pdf->download('prefactura-'.$bill->rfc.'.pdf');
		}
		else
		{
			return abort(404);
		}
	}

	public function uploadDocuments($id, Request $request)
	{
		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			$income 				= App\Income::where('idFolio',$id)->get();
			$updateBill 			= App\Income::find($income->first()->idIncome);
			$updateBill->billStatus = $request->status_bill;
			$updateBill->save();
			for ($i=0; $i < count($request->realPath); $i++) 
			{ 
				$new_file_name = Files::rename($request->realPath[$i],$id);

				$documents 					= new App\DocumentsIncome();
				$documents->path 			= $new_file_name;
				$documents->idIncome 		= $income->first()->idIncome;
				$documents->name 			= $request->nameDocument[$i];
				$documents->save();
			}
			
			$alert = "swal('','".Lang::get("messages.files_updated")."', 'success')";
			$alert 	= "swal('', 'Documentos Enviados Exitosamente', 'success');";
			return redirect('administration/income')->with('alert',$alert);
		}
		else
		{
			$alert = "swal('','".Lang::get("messages.file_upload_error")."', 'info')";
			$alert 	= "swal('', 'No se carga ningún documento', 'info');";
			return redirect()->back()->with('alert',$alert);
		}
	}

	public function addConcept($id,Request $request)
	{

		if(Auth::user()->module->where('id',140)->count()>0)
		{
			$t_request = App\RequestModel::find($id);
		
			$subtotales = 0;
			$iva        = 0;
			$taxes      = 0;
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

				$subtotales += (($request->tquanty[$i] * $request->tprice[$i])-$request->tdiscount[$i]);
				$iva        += $request->tiva[$i];
			}
			for ($i=0;isset($request->ntquanty) && $i < count($request->ntquanty); $i++)
			{
				$tamountadditional 	= 'ntamountadditional'.$i;

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

				$tamountretention = 'ntamountretention'.$i;
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

				$subtotales += (($request->ntquanty[$i] * $request->ntprice[$i])-$request->ntdiscount[$i]);
				$iva        += $request->ntiva[$i];
			}
			
			$total    = ($subtotales+$iva+$taxes)-$retentions;
			$idIncome = App\Income::where('idFolio',$id)->first()->idIncome;

			isset(App\IncomeDetail::where('idIncome',$idIncome)->first()->idincomeDetail) ? $detailID = App\IncomeDetail::where('idIncome',$idIncome)->first()->idincomeDetail : $detailID = null;

			$t_income                  = App\Income::find($idIncome);
			$t_income->notes           = $request->note;
			$t_income->subtotales      = $subtotales;
			$t_income->tax             = $iva;
			$t_income->amount          = $total;
			$t_income->save();

			for ($i=0; isset($request->ntamount) && $i < count($request->ntamount); $i++)
			{
				$t_detailIncome              = new App\IncomeDetail();
				$t_detailIncome->idIncome    = $idIncome;
				$t_detailIncome->quantity    = $request->ntquanty[$i];
				$t_detailIncome->unit        = $request->ntunit[$i];
				$t_detailIncome->description = $request->ntdescr[$i];
				$t_detailIncome->unitPrice   = $request->ntprice[$i];
				$t_detailIncome->tax         = $request->ntiva[$i];
				$t_detailIncome->discount    = $request->ntdiscount[$i];
				$t_detailIncome->amount      = $request->ntamount[$i];
				$t_detailIncome->typeTax     = $request->ntivakind[$i];
				$t_detailIncome->subtotal    = $request->ntquanty[$i] * $request->ntprice[$i];
				$t_detailIncome->save();

				$idincomeDetail		= $t_detailIncome->idincomeDetail;
				$tamountadditional	= 'ntamountadditional'.$i;
				$tnameamount		= 'ntnameamount'.$i;
				if (isset($request->$tamountadditional) && $request->$tamountadditional != "") 
				{
					for ($d=0; $d < count($request->$tamountadditional); $d++) 
					{ 
						if ($request->$tamountadditional[$d] != "") 
						{
							$t_taxes                 = new App\TaxesIncome();
							$t_taxes->name           = $request->$tnameamount[$d];
							$t_taxes->amount         = $request->$tamountadditional[$d];
							$t_taxes->idincomeDetail = $idincomeDetail;
							$t_taxes->save();
						}
					}
				}

				$tamountretention	= 'ntamountretention'.$i;
				$tnameretention		= 'ntnameretention'.$i;
				if (isset($request->$tamountretention) && $request->$tamountretention != "") 
				{
					for ($d=0; $d < count($request->$tamountretention); $d++) 
					{ 
						if ($request->$tamountretention[$d] != "") 
						{
							$t_retention 					= new App\RetentionIncome();
							$t_retention->name 				= $request->$tnameretention[$d];
							$t_retention->amount 			= $request->$tamountretention[$d];
							$t_retention->idincomeDetail 	= $idincomeDetail;
							$t_retention->save();
						}
					}
				}
			}

			if($t_request->status == 20)
			{
				$t_request->status = 21;
				$t_request->save();
			}
			$alert 	= "swal('', 'Conceptos Agregados Exitosamente', 'success');";
			return redirect('administration/income')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
		

	}

	public function bad($id)
	{
		if(Auth::user()->module->where('id',143)->count()>0)
		{
			$t_request = App\RequestModel::find($id);
			$t_request->status = 22;
			$t_request->save();

			$alert 	= "swal('', 'Estado Actualizado Exitosamente', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
}
