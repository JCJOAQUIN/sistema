<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Alert;
use App\FlightLodging;
use App\RequestModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Ilovepdf\CompressTask;
use Excel;
use Exception;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Lang;

class AdministrationFlightsLodgingController extends Controller
{
	protected $module_id = 284;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{   
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
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
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.vuelos_hoteles.alta',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 285
			]);
		}
	}
	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			
			$requestModel				 		= new App\RequestModel();
			$requestModel->kind 				= 23;
			$requestModel->fDate 				= Carbon::now();
			$requestModel->idElaborate 			= Auth::user()->id;
			$requestModel->idRequest 			= $request->userid;
			$requestModel->status 				= 3;
			$requestModel->account 				= $request->accountid;
			$requestModel->idEnterprise 		= $request->enterpriseid;
			$requestModel->idArea 				= $request->areaid;
			$requestModel->idDepartment 		= $request->departmentid;
			$requestModel->idProject 			= $request->project_id;
			$requestModel->code_wbs 			= $request->code_wbs;
			$requestModel->code_edt 			= $request->code_edt;
			$requestModel->save();
			
			$flight_lodging					= new App\FlightLodging();
			$flight_lodging->title			= $request->title;
			$flight_lodging->date			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$flight_lodging->folio_request	= $requestModel->folio;
			$flight_lodging->pemex_pti		= $request->solicited_by;
			$flight_lodging->reference		= $request->reference;
			$flight_lodging->payment_method	= $request->pay_mode;
			$flight_lodging->currency		= $request->type_currency;
			$flight_lodging->bill_status	= $request->status_bill;
			$flight_lodging->subtotal		= $request->subtotal_flight;
			$flight_lodging->iva			= $request->iva_flight;
			$flight_lodging->taxes			= $request->taxes_flight;
			$flight_lodging->retentions		= $request->retentions_flight;
			$flight_lodging->total			= $request->total_flight;
			$flight_lodging->save();
			if(isset($request->tpassenger) && count($request->tpassenger) > 0)
			{
				for($i = 0; $i < count($request->ttipo); $i++)
				{
					$flight_details = new App\FlightLodgingDetails();
					$flight_details->type_flight 				= $request->ttipo[$i];
					$flight_details->flight_lodging_id			= $flight_lodging->id; 
					$flight_details->job_position 				= $request->tpassengerPosition[$i]; 
					$flight_details->passenger_name 			= $request->tpassenger[$i]; 
					$flight_details->born_date 					= $request->tburn[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tburn[$i])->format('Y-m-d') : null;
					$flight_details->airline 					= $request->tairline[$i];
					$flight_details->route 						= $request->troute[$i];
					$flight_details->departure_date 			= $request->tdateFlight[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateFlight[$i])->format('Y-m-d') : null;
					$flight_details->departure_hour 			= $request->thourFlight[$i] != "" ? Carbon::createFromFormat('H:i', $request->timepath[$i])->format('H:i:s') : $request->timepath[$i];
					$flight_details->airline_back 				= $request->tairlineBack[$i];
					$flight_details->route_back 				= $request->trouteBack[$i];
					$flight_details->departure_date_back 		= $request->tdateFlightBack[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateFlightBack[$i])->format('Y-m-d') : null;
					$flight_details->departure_hour_back 		= $request->thourFlightBack[$i];
					$flight_details->journey_description 		= $request->tdescription[$i];
					$flight_details->direct_superior 			= $request->tbossName[$i];
					$flight_details->last_family_journey_date 	= $request->tlastTravel[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tlastTravel[$i])->format('Y-m-d') : null;
					$flight_details->checked_baggage 			= $request->tbaggage[$i];
					$flight_details->hosting 					= $request->thostPlace[$i];
					$flight_details->singin_date 				= $request->tdateIn[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateIn[$i])->format('Y-m-d') : null;
					$flight_details->output_date 				= $request->tdateOut[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateOut[$i])->format('Y-m-d') : null;
					$flight_details->iva 						= $request->tiva[$i];
					$flight_details->taxes 						= $request->ttaxes[$i];
					$flight_details->retentions 				= $request->tretentions[$i];
					$flight_details->subtotal 					= $request->tsubtotal[$i];
					$flight_details->total 						= $request->ttotal[$i];
					$flight_details->save();

					if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->count()>0) 
					{
						App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->delete();
					}

					$t_name_tax			= 't_name_tax'.$i;
					$t_amount_tax		= 't_amount_tax'.$i;
					$t_name_retention	= 't_name_retention'.$i;
					$t_amount_retention	= 't_amount_retention'.$i;
					$total_tax 			= 0;
					$total_retention 	= 0;

					if (isset($request->$t_name_tax) && $request->$t_name_tax != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_tax); $d++) 
						{ 
							if ($request->$t_name_tax[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_tax[$d];
								$t_taxes->amount 			= $request->$t_amount_tax[$d];
								$t_taxes->type 				= 1;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

					if (isset($request->$t_name_retention) && $request->$t_name_retention != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_retention); $d++) 
						{ 
							if ($request->$t_name_retention[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_retention[$d];
								$t_taxes->amount 			= $request->$t_amount_retention[$d];
								$t_taxes->type 				= 2;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

				}
			}
			if(isset($request->realPath) && count($request->realPath)>0)
			{
				for($i= 0; $i < count($request->realPath); $i++)
				{
					$flight_documents 						= new App\FlightLodgingDocuments();
					$flight_documents->name 				= $request->nameDocument[$i];
					$flight_documents->path 				= $request->realPath[$i];
					$flight_documents->users_id 			= Auth::user()->id;
					$flight_documents->flight_lodging_id  	= $flight_lodging->id; 
					$flight_documents->save();	
				}
			}

			$alert = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			return redirect()->route('flights-lodging.search')->with('alert',$alert);
		}
	}
	public function unsend(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$requestModel				 		= new App\RequestModel();
			$requestModel->kind 				= 23;
			$requestModel->fDate 				= Carbon::now();
			$requestModel->idRequest 			= $request->userid;
			$requestModel->idElaborate 			= Auth::user()->id;
			$requestModel->status 				= 2;
			$requestModel->account 				= $request->accountid;
			$requestModel->idEnterprise 		= $request->enterpriseid;
			$requestModel->idArea 				= $request->areaid;
			$requestModel->idDepartment 		= $request->departmentid;
			$requestModel->idProject 			= $request->project_id;
			$requestModel->code_wbs 			= $request->code_wbs;
			$requestModel->code_edt 			= $request->code_edt;
			$requestModel->save();

			$flight_lodging					= new App\FlightLodging();
			$flight_lodging->title			= $request->title;
			$flight_lodging->date			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$flight_lodging->folio_request	= $requestModel->folio;
			$flight_lodging->pemex_pti		= $request->solicited_by;
			$flight_lodging->reference		= $request->reference;
			$flight_lodging->payment_method	= $request->pay_mode;
			$flight_lodging->currency		= $request->type_currency;
			$flight_lodging->bill_status	= $request->status_bill;
			$flight_lodging->subtotal		= $request->subtotal_flight;
			$flight_lodging->iva			= $request->iva_flight;
			$flight_lodging->taxes			= $request->taxes_flight;
			$flight_lodging->retentions		= $request->retentions_flight;
			$flight_lodging->total			= $request->total_flight;
			$flight_lodging->save();
			if(isset($request->tpassenger) && count($request->tpassenger) > 0)
			{
				for($i = 0; $i < count($request->ttipo); $i++)
				{
					$flight_details = new App\FlightLodgingDetails();
					$flight_details->type_flight 				= $request->ttipo[$i];
					$flight_details->flight_lodging_id			= $flight_lodging->id; 
					$flight_details->job_position 				= $request->tpassengerPosition[$i]; 
					$flight_details->passenger_name 			= $request->tpassenger[$i]; 
					$flight_details->born_date 					= Carbon::createFromFormat('d-m-Y',$request->tburn[$i])->format('Y-m-d');; 
					$flight_details->airline 					= $request->tairline[$i];
					$flight_details->route 						= $request->troute[$i];
					$flight_details->departure_date 			= Carbon::createFromFormat('d-m-Y', $request->tdateFlight[$i])->format('Y-m-d');
					$flight_details->departure_hour 			= $request->thourFlight[$i];
					$flight_details->airline_back 				= $request->tairlineBack[$i];
					$flight_details->route_back 				= $request->trouteBack[$i];
					$flight_details->departure_date_back 		= $request->tdateFlightBack[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateFlightBack[$i])->format('Y-m-d') : null;
					$flight_details->departure_hour_back 		= $request->thourFlightBack[$i];
					$flight_details->journey_description 		= $request->tdescription[$i];
					$flight_details->direct_superior 			= $request->tbossName[$i];
					$flight_details->last_family_journey_date 	= Carbon::createFromFormat('d-m-Y',$request->tlastTravel[$i])->format('Y-m-d');
					$flight_details->checked_baggage 			= $request->tbaggage[$i];
					$flight_details->hosting 					= $request->thostPlace[$i];
					$flight_details->singin_date 				= $request->tdateIn[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateIn[$i])->format('Y-m-d') : null;
					$flight_details->output_date 				= $request->tdateOut[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateOut[$i])->format('Y-m-d') : null;
					$flight_details->iva 						= $request->tiva[$i];
					$flight_details->taxes 						= $request->ttaxes[$i];
					$flight_details->retentions 				= $request->tretentions[$i];
					$flight_details->subtotal 					= $request->tsubtotal[$i];
					$flight_details->total 						= $request->ttotal[$i];
					$flight_details->save();
					

					if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->count()>0) 
					{
						App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->delete();
					}

					$t_name_tax			= 't_name_tax'.$i;
					$t_amount_tax		= 't_amount_tax'.$i;
					$t_name_retention	= 't_name_retention'.$i;
					$t_amount_retention	= 't_amount_retention'.$i;
					$total_tax 			= 0;
					$total_retention 	= 0;

					if (isset($request->$t_name_tax) && $request->$t_name_tax != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_tax); $d++) 
						{ 
							if ($request->$t_name_tax[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_tax[$d];
								$t_taxes->amount 			= $request->$t_amount_tax[$d];
								$t_taxes->type 				= 1;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

					if (isset($request->$t_name_retention) && $request->$t_name_retention != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_retention); $d++) 
						{ 
							if ($request->$t_name_retention[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_retention[$d];
								$t_taxes->amount 			= $request->$t_amount_retention[$d];
								$t_taxes->type 				= 2;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

				}
			}
			if(isset($request->realPath) && count($request->realPath)>0)
			{
				for($i= 0; $i < count($request->realPath); $i++)
				{
					$flight_documents 						= new App\FlightLodgingDocuments();
					$flight_documents->name 				= $request->nameDocument[$i];
					$flight_documents->path 				= $request->realPath[$i];
					$flight_documents->users_id 			= Auth::user()->id;
					$flight_documents->flight_lodging_id  	= $flight_lodging->id; 
					$flight_documents->save();	
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('flights-lodging.follow.edit', ['id'=>$requestModel->folio])->with('alert',$alert);
		}
	}
	public function sendToReview(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$deleteArray	= [];
			$val			= $request->deleted_rows;
			$deleteArray	= explode( ',', $val);
			for($i = 0; $i <count($deleteArray); $i++)
			{

				if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$deleteArray[$i])->count()>0) 
				{
					App\FlightLodgingTaxes::where('flight_lodging_details_id',$deleteArray[$i])->delete();
				}
				App\FlightLodgingDetails::where('id', $deleteArray[$i])->delete();
			}

			$data	= App\Module::find($this->module_id);
			$requestModel				 		= App\RequestModel::find($id);
			$requestModel->kind 				= 23;
			$requestModel->fDate 				= Carbon::now();
			$requestModel->idRequest 			= $request->userid;
			$requestModel->idElaborate 			= Auth::user()->id;
			$requestModel->status 				= 3;
			$requestModel->account 				= $request->accountid;
			$requestModel->idEnterprise 		= $request->enterpriseid;
			$requestModel->idArea 				= $request->areaid;
			$requestModel->idDepartment 		= $request->departmentid;
			$requestModel->idProject 			= $request->project_id;
			$requestModel->code_wbs 			= $request->code_wbs;
			$requestModel->code_edt 			= $request->code_edt;
			$requestModel->save();
			$flight_id = $requestModel->flightsLodging->id;
			$flight_lodging					= App\FlightLodging::find($flight_id);
			$flight_lodging->title			= $request->title;
			$flight_lodging->date			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y',$request->datetitle)->format('Y-m-d') : null;
			$flight_lodging->folio_request	= $requestModel->folio;
			$flight_lodging->pemex_pti		= $request->solicited_by;
			$flight_lodging->reference		= $request->reference;
			$flight_lodging->payment_method	= $request->pay_mode;
			$flight_lodging->currency		= $request->type_currency;
			$flight_lodging->bill_status	= $request->status_bill;
			$flight_lodging->subtotal		= $request->subtotal_flight;
			$flight_lodging->iva			= $request->iva_flight;
			$flight_lodging->taxes			= $request->taxes_flight;
			$flight_lodging->retentions		= $request->retentions_flight;
			$flight_lodging->total			= $request->total_flight;
			$flight_lodging->save();
			if(isset($request->tpassenger) && count($request->tpassenger) > 0)
			{
				for($i = 0; $i < count($request->ttipo); $i++)
				{
					if($request->flight_details_id[$i] == "x")
					{
						$flight_details =new App\FlightLodgingDetails();
					}
					else
					{
						$flight_details = App\FlightLodgingDetails::find($request->flight_details_id[$i]);
					}
					$flight_details->type_flight 				= $request->ttipo[$i];
					$flight_details->flight_lodging_id			= $flight_lodging->id; 
					$flight_details->job_position 				= $request->tpassengerPosition[$i]; 
					$flight_details->passenger_name 			= $request->tpassenger[$i]; 
					$flight_details->born_date 					= Carbon::createFromFormat('d-m-Y',$request->tburn[$i])->format('Y-m-d');;
					$flight_details->airline 					= $request->tairline[$i];
					$flight_details->route 						= $request->troute[$i];
					$flight_details->departure_date 			= Carbon::createFromFormat('d-m-Y',$request->tdateFlight[$i])->format('Y-m-d');
					$flight_details->departure_hour 			= $request->thourFlight[$i];
					$flight_details->airline_back 				= $request->tairlineBack[$i];
					$flight_details->route_back 				= $request->trouteBack[$i];
					$flight_details->departure_date_back 		= $request->tdateFlightBack[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateFlightBack[$i])->format('Y-m-d') : null;
					$flight_details->departure_hour_back 		= $request->thourFlightBack[$i];
					$flight_details->journey_description 		= $request->tdescription[$i];
					$flight_details->direct_superior 			= $request->tbossName[$i];
					$flight_details->last_family_journey_date 	= Carbon::createFromFormat('d-m-Y',$request->tlastTravel[$i])->format('Y-m-d');
					$flight_details->checked_baggage 			= $request->tbaggage[$i];
					$flight_details->hosting 					= $request->thostPlace[$i];
					$flight_details->singin_date 				= $request->tdateIn[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateIn[$i])->format('Y-m-d') : null;
					$flight_details->output_date 				= $request->tdateOut[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateOut[$i])->format('Y-m-d') : null;
					$flight_details->iva 						= $request->tiva[$i];
					$flight_details->taxes 						= $request->ttaxes[$i];
					$flight_details->retentions 				= $request->tretentions[$i];
					$flight_details->subtotal 					= $request->tsubtotal[$i];
					$flight_details->total 						= $request->ttotal[$i];
					$flight_details->save();


					if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->count()>0) 
					{
						App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->delete();
					}

					$t_name_tax			= 't_name_tax'.$i;
					$t_amount_tax		= 't_amount_tax'.$i;
					$t_name_retention	= 't_name_retention'.$i;
					$t_amount_retention	= 't_amount_retention'.$i;
					$total_tax 			= 0;
					$total_retention 	= 0;

					if (isset($request->$t_name_tax) && $request->$t_name_tax != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_tax); $d++) 
						{ 
							if ($request->$t_name_tax[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_tax[$d];
								$t_taxes->amount 			= $request->$t_amount_tax[$d];
								$t_taxes->type 				= 1;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

					if (isset($request->$t_name_retention) && $request->$t_name_retention != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_retention); $d++) 
						{ 
							if ($request->$t_name_retention[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_retention[$d];
								$t_taxes->amount 			= $request->$t_amount_retention[$d];
								$t_taxes->type 				= 2;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}
				}
			}
			if(isset($request->realPath) && count($request->realPath)>0)
			{
				for($i= 0; $i < count($request->realPath); $i++)
				{
					
					$flight_documents 						= new App\FlightLodgingDocuments();
					$flight_documents->name 				= $request->nameDocument[$i];
					$flight_documents->path 				= $request->realPath[$i];
					$flight_documents->users_id 			= Auth::user()->id;
					$flight_documents->flight_lodging_id  	= $flight_lodging->id; 
					$flight_documents->save();	
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_sent")."', 'success');";
			return redirect()->route('flights-lodging.search', ['id'=>$requestModel->folio])->with('alert',$alert);
		}
	}
	public function updateUnsend(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$deleteArray	= [];
			$val			= $request->deleted_rows;
			$deleteArray	= explode( ',', $val);
			for($i = 0; $i <count($deleteArray); $i++)
			{
				if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$deleteArray[$i])->count()>0) 
				{
					App\FlightLodgingTaxes::where('flight_lodging_details_id',$deleteArray[$i])->delete();
				}
				App\FlightLodgingDetails::where('id', $deleteArray[$i])->delete();
			}
			$data	= App\Module::find($this->module_id);
			$requestModel				 		= App\RequestModel::find($id);
			$requestModel->kind 				= 23;
			$requestModel->fDate 				= Carbon::now();
			$requestModel->idRequest 			= $request->userid;
			$requestModel->idElaborate 			= Auth::user()->id;
			$requestModel->status 				= 2;
			$requestModel->account 				= $request->accountid;
			$requestModel->idEnterprise 		= $request->enterpriseid;
			$requestModel->idArea 				= $request->areaid;
			$requestModel->idDepartment 		= $request->departmentid;
			$requestModel->idProject 			= $request->project_id;
			$requestModel->code_wbs 			= $request->code_wbs;
			$requestModel->code_edt 			= $request->code_edt;
			$requestModel->save();
			
			$flight_id = $requestModel->flightsLodging->id;
			$flight_lodging					= App\FlightLodging::find($flight_id);
			$flight_lodging->title			= $request->title;
			$flight_lodging->date			= $request->datetitle != "" ? Carbon::createFromFormat('d-m-Y', $request->datetitle)->format('Y-m-d') : null;
			$flight_lodging->folio_request	= $requestModel->folio;
			$flight_lodging->pemex_pti		= $request->solicited_by;
			$flight_lodging->reference		= $request->reference;
			$flight_lodging->payment_method	= $request->pay_mode;
			$flight_lodging->currency		= $request->type_currency;
			$flight_lodging->bill_status	= $request->status_bill;
			$flight_lodging->subtotal		= $request->subtotal_flight;
			$flight_lodging->iva			= $request->iva_flight;
			$flight_lodging->taxes			= $request->taxes_flight;
			$flight_lodging->retentions		= $request->retentions_flight;
			$flight_lodging->total			= $request->total_flight;
			$flight_lodging->save();
			if(isset($request->tpassenger) && count($request->tpassenger) > 0)
			{
				for($i = 0; $i < count($request->ttipo); $i++)
				{
					if($request->flight_details_id[$i] == "x")
					{
						$flight_details = new App\FlightLodgingDetails();
					}
					else
					{
						$flight_details = App\FlightLodgingDetails::find($request->flight_details_id[$i]);
					}
					$flight_details->type_flight 				= $request->ttipo[$i];
					$flight_details->flight_lodging_id			= $flight_lodging->id; 
					$flight_details->job_position 				= $request->tpassengerPosition[$i]; 
					$flight_details->passenger_name 			= $request->tpassenger[$i]; 
					$flight_details->born_date 					= Carbon::createFromFormat('d-m-Y',$request->tburn[$i])->format('Y-m-d');;
					$flight_details->airline 					= $request->tairline[$i];
					$flight_details->route 						= $request->troute[$i];
					$flight_details->departure_date 			= Carbon::createFromFormat('d-m-Y',$request->tdateFlight[$i])->format('Y-m-d');
					$flight_details->departure_hour 			= $request->thourFlight[$i];
					$flight_details->airline_back 				= $request->tairlineBack[$i];
					$flight_details->route_back 				= $request->trouteBack[$i];
					$flight_details->departure_date_back 		= $request->tdateFlightBack[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateFlightBack[$i])->format('Y-m-d') : null;
					$flight_details->departure_hour_back 		= $request->thourFlightBack[$i];
					$flight_details->journey_description 		= $request->tdescription[$i];
					$flight_details->direct_superior 			= $request->tbossName[$i];
					$flight_details->last_family_journey_date 	= Carbon::createFromFormat('d-m-Y',$request->tlastTravel[$i])->format('Y-m-d');
					$flight_details->checked_baggage 			= $request->tbaggage[$i];
					$flight_details->hosting 					= $request->thostPlace[$i];
					$flight_details->singin_date 				= $request->tdateIn[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateIn[$i])->format('Y-m-d') : null;
					$flight_details->output_date 				= $request->tdateOut[$i] != "" ? Carbon::createFromFormat('d-m-Y',$request->tdateOut[$i])->format('Y-m-d') : null;
					$flight_details->iva 						= $request->tiva[$i];
					$flight_details->taxes 						= $request->ttaxes[$i];
					$flight_details->retentions 				= $request->tretentions[$i];
					$flight_details->subtotal 					= $request->tsubtotal[$i];
					$flight_details->total 						= $request->ttotal[$i];
					$flight_details->save();

					if (App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->count()>0) 
					{
						App\FlightLodgingTaxes::where('flight_lodging_details_id',$flight_details->id)->delete();
					}

					$t_name_tax			= 't_name_tax'.$i;
					$t_amount_tax		= 't_amount_tax'.$i;
					$t_name_retention	= 't_name_retention'.$i;
					$t_amount_retention	= 't_amount_retention'.$i;
					$total_tax 			= 0;
					$total_retention 	= 0;

					if (isset($request->$t_name_tax) && $request->$t_name_tax != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_tax); $d++) 
						{ 
							if ($request->$t_name_tax[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_tax[$d];
								$t_taxes->amount 			= $request->$t_amount_tax[$d];
								$t_taxes->type 				= 1;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}

					if (isset($request->$t_name_retention) && $request->$t_name_retention != "") 
					{ 
						for ($d=0; $d < count($request->$t_name_retention); $d++) 
						{ 
							if ($request->$t_name_retention[$d] != "") 
							{ 
								$t_taxes 					= new App\FlightLodgingTaxes();
								$t_taxes->name 				= $request->$t_name_retention[$d];
								$t_taxes->amount 			= $request->$t_amount_retention[$d];
								$t_taxes->type 				= 2;
								$t_taxes->flight_lodging_details_id = $flight_details->id;
								$t_taxes->save();
							}
						}
					}
				}
			}
			if(isset($request->realPath) && count($request->realPath)>0)
			{
				for($i= 0; $i < count($request->realPath); $i++)
				{
					$flight_documents 						= new App\FlightLodgingDocuments();
					$flight_documents->name 				= $request->nameDocument[$i];
					$flight_documents->path 				= $request->realPath[$i];
					$flight_documents->users_id 			= Auth::user()->id;
					$flight_documents->flight_lodging_id  	= $flight_lodging->id; 
					$flight_documents->save();	
				}
			}
			$alert = "swal('', '".Lang::get("messages.request_saved")."', 'success');";
			return redirect()->route('flights-lodging.follow.edit', ['id'=>$requestModel->folio])->with('alert',$alert);
		}
	}
	public function loadNewDocument(Request $request, App\RequestModel $request_model)
	{
		if(isset($request->realPath) && count($request->realPath)>0)
		{
			for($i= 0; $i < count($request->realPath); $i++)
			{
				$flight_documents 						= new App\FlightLodgingDocuments();
				$flight_documents->name 				= $request->nameDocument[$i];
				$flight_documents->path 				= $request->realPath[$i];
				$flight_documents->users_id 			= Auth::user()->id;
				$flight_documents->flight_lodging_id  	= $request_model->flightsLodging->id; 
				$flight_documents->save();	
			}
			$alert	= "swal('', 'Se ha agregado un nuevo documento', 'success');";
			return redirect()->route('flights-lodging.follow.edit', ['id'=>$request_model->folio])->with('alert',$alert);
		}
		else
		{
			$alert	= "swal('', 'No agregó ningún documento', 'info');";
			return redirect()->route('flights-lodging.follow.edit', ['id'=>$request_model->folio])->with('alert',$alert);
		}
	}
	public function edit($id)
	{
		if (Auth::user()->module->where('id',286)->count()>0) 
		{
			$request = App\RequestModel::find($id);
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.vuelos_hoteles.alta',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 286,
						'request'	=> $request,
						'alert' 	=> ''
					]);
			}
			else
			{
				$alert	= "swal('', 'No existe el vuelo', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function newFlight(Request $request, $id)
	{
		if (Auth::user()->module->where('id',286)->count()>0) 
		{
			$request			= App\RequestModel::find($id);
			$request->status	= 2;
			if ($request != "") 
			{
				$data = App\Module::find($this->module_id);
				return view('administracion.vuelos_hoteles.alta',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 285,
						'request'		=> $request,
						'alert'			=> '',
						'new_request'	=> true
					]);
			}
			else
			{
				$alert	= "swal('', 'No existe el vuelo', 'error');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			if(Auth::user()->globalCheck->where('module_id',286)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',286)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data = App\Module::find($this->module_id);
			$fligths_request = FlightLodging::orderBy('folio_request', 'DESC')
				->where(function ($q) use ($request,$global_permission) 
				{
					if (!empty($request->folio)) 
					{
						$q->where('folio_request', 'LIKE', "%{$request->folio}%");
					}

					$q->whereHas('request', function (Builder $q) use ($request,$global_permission) 
					{
						$q->where(function ($query) use ($global_permission)
						{
							if ($global_permission == 0) 
							{
								$query->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
							}
						});

						$q->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(286)->pluck('departament_id'))->orWhereNull('idDepartment');
						});

						$q->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(286)->pluck('enterprise_id'))->orWhereNull('idEnterprise');
						});
				
						if(isset($request->status))
						{
							if(count($request->status) > 0) 
							{
								$q->whereIn('status', $request->status);
							}
						}
						if (!empty($request->enterpriseid)) 
						{
							$q->where('idEnterprise', $request->enterpriseid);
						}

						if (!empty($request->projectId)) 
						{
							$q->where('idProject', $request->projectId);
						}

						if (!empty($request->user_request)) 
						{
							$q->where('idRequest', $request->user_request);
						}

						if (!empty($request->mindate) && !empty($request->maxdate)) 
						{
							$q->whereBetween('fDate',[''.Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
						}
					});
					
					if (!empty($request->passenger_name) || !empty($request->type_fligth)) 
					{
						$q->whereHas('details', function (Builder $q) use ($request) 
						{
							if (!empty($request->passenger_name)) 
							{
								$q->where('passenger_name', 'LIKE', "%{$request->passenger_name}%");
							}
							if (!empty($request->type_fligth)) 
							{
								$q->where('type_flight', $request->type_fligth);
							}
						});
					}
				})
				->paginate(10);
			return response(
				view('administracion.vuelos_hoteles.formSearch',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 286,
						'requests_fligths'	=> $fligths_request,
						'folio'				=> $request->folio,
						'mindate'			=> $request->mindate,
						'maxdate'			=> $request->maxdate,
						'enterpriseid'		=> $request->enterpriseid,
						'projectId'			=> $request->projectId,
						'user_request'		=> $request->user_request,
						'status'			=> $request->status,
						'passenger_name'	=> $request->passenger_name,
						'type_fligth'		=> $request->type_fligth
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(286), 2880
			);
		}
	}
	public function review(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$fligths_request = FlightLodging::orderBy('folio_request', 'DESC')
				->where(function ($q) use ($request) 
				{
					if (!empty($request->folio)) 
					{
						$q->where('folio_request', 'LIKE', "%{$request->folio}%");
					}

					$q->whereHas('request', function (Builder $q) use ($request) 
					{
						$q->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(287)->pluck('departament_id'));
						});

						$q->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(287)->pluck('enterprise_id'));
						});

						$q->where('status', 3);
						
						if (!empty($request->enterpriseid)) 
						{
							$q->where('idEnterprise', $request->enterpriseid);
						}
						
						if (!empty($request->projectId)) 
						{
							$q->where('idProject', $request->projectId);
						}
						if (!empty($request->user_request)) 
						{
							$q->where('idRequest', $request->user_request);
						}
						if (!empty($request->mindate) && !empty($request->maxdate)) 
						{
							$q->whereBetween('fDate',[''.Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
						}

					});
					if (!empty($request->passenger_name) || !empty($request->type_fligth)) 
					{
						$q->whereHas('details', function (Builder $q) use ($request) 
						{
							if (!empty($request->passenger_name)) 
							{
								$q->where('passenger_name', 'LIKE', "%{$request->passenger_name}%");
							}
							if (!empty($request->type_fligth)) 
							{
								$q->where('type_flight', $request->type_fligth);
							}
						});
					}
				})->paginate(10);
			return response(
				view('administracion.vuelos_hoteles.formSearch',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 287,
						'requests_fligths'	=> $fligths_request,
						'folio'				=> $request->folio,
						'mindate'			=> $request->mindate,
						'maxdate'			=> $request->maxdate,
						'enterpriseid'		=> $request->enterpriseid,
						'projectId'			=> $request->projectId,
						'user_request'		=> $request->user_request,
						'status'			=> "",
						'passenger_name'	=> $request->passenger_name,
						'type_fligth'		=> $request->type_fligth
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(287), 2880
			);
		}
	}

	public function authorization(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$fligths_request = FlightLodging::orderBy('folio_request', 'DESC')
				->where(function ($q) use ($request) 
				{
					if (!empty($request->folio)) 
					{
						$q->where('folio_request', 'LIKE', "%{$request->folio}%");
					}

					$q->whereHas('request', function (Builder $q) use ($request) 
					{
						$q->where(function($permissionDep)
						{
							$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(288)->pluck('departament_id'));
						});

						$q->where(function($permissionEnt)
						{
							$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(288)->pluck('enterprise_id'));
						});

						$q->where('status', 4);

						if (!empty($request->enterpriseid)) 
						{
							$q->where('idEnterprise', $request->enterpriseid);
						}
						if (!empty($request->projectId)) 
						{
							$q->where('idProject', $request->projectId);
						}
						if (!empty($request->user_request)) 
						{
							$q->where('idRequest', $request->user_request);
						}
						if (!empty($request->mindate) && !empty($request->maxdate)) 
						{
							$q->whereBetween('fDate',[''.Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
						}
					});
					if (!empty($request->passenger_name) || !empty($request->type_fligth)) 
					{
						$q->whereHas('details', function (Builder $q) use ($request) 
						{
							if (!empty($request->passenger_name)) 
							{
								$q->where('passenger_name', 'LIKE', "%{$request->passenger_name}%");
							}
							if (!empty($request->type_fligth)) 
							{
								$q->where('type_flight', $request->type_fligth);
							}
						});
					}
				})->paginate(10);
				
			return response(
				view('administracion.vuelos_hoteles.autorizacion',
					[
						'id'				=> $data['father'],
						'title'				=> $data['name'],
						'details'			=> $data['details'],
						'child_id'			=> $this->module_id,
						'option_id'			=> 288,
						'requests_fligths'	=> $fligths_request,
						'folio'				=> $request->folio,
						'mindate'			=> $request->mindate,
						'maxdate'			=> $request->maxdate,
						'enterpriseid'		=> $request->enterpriseid,
						'projectId'			=> $request->projectId,
						'user_request'		=> $request->user_request,
						'status'			=> "",
						'passenger_name'	=> $request->passenger_name,
						'type_fligth'		=> $request->type_fligth
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(288), 2880
			);
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
					\Storage::disk('public')->delete('/docs/flights_lodging/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_flight_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/flights_lodging/'.$name;
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


	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			if(Auth::user()->globalCheck->where('module_id',286)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',286)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}

			$requests = DB::table('request_models')
						->selectRaw('
							request_models.folio as folio,
							status_requests.description as status,
							flight_lodgings.title as title,
							CONCAT_WS(" ", request_user.name, request_user.last_name, request_user.scnd_last_name) as request_user,
							CONCAT_WS(" ", elaborate_user.name, elaborate_user.last_name, elaborate_user.scnd_last_name) as elaborate_user,
							DATE_FORMAT(request_models.fDate, "%d-%m-%Y") as date_request,
							request_enterprise.name as request_enterprise,
							request_direction.name as request_direction,
							request_department.name as request_department,
							CONCAT_WS(" ",request_account.account, request_account.description) as request_account,
							request_project.proyectName as project,
							cat_code_w_bs.code_wbs as code_wbs,
							cat_code_e_d_ts.description as code_edt,
							IF(flight_lodgings.pemex_pti IS NOT NULL,IF(flight_lodgings.pemex_pti = 1, "Sí","No"),"No definido") as request_by_pti,
							flight_lodgings.reference as reference,
							payment_methods.method as payment_method,
							flight_lodgings.currency as currency,
							CONCAT_WS(" ", reviewed_user.name, reviewed_user.last_name, reviewed_user.scnd_last_name) as reviewed_user,
							DATE_FORMAT(request_models.reviewDate, "%d-%m-%Y") as review_date,
							request_models.checkComment as check_comment,
							CONCAT_WS(" ", authorized_user.name, authorized_user.last_name, authorized_user.scnd_last_name) as authorized_user,
							DATE_FORMAT(request_models.authorizeDate, "%d-%m-%Y") as authorize_date,
							request_models.authorizeComment as authorize_comment,
							IF(flight_lodging_details.type_flight IS NOT NULL, IF(flight_lodging_details.type_flight = 1, "Sencillo", "Redondo"), "") as type_flight,
							flight_lodging_details.journey_description as journey_description,
							flight_lodging_details.passenger_name as passenger_name,
							flight_lodging_details.job_position as job_position,
							flight_lodging_details.born_date as born_date,
							flight_lodging_details.airline as airline,
							flight_lodging_details.route as route,
							flight_lodging_details.departure_date as departure_date,
							flight_lodging_details.departure_hour as departure_hour,
							flight_lodging_details.airline_back as airline_back,
							flight_lodging_details.route_back as route_back,
							flight_lodging_details.departure_date_back as departure_date_back,
							flight_lodging_details.departure_hour_back as departure_hour_back,
							flight_lodging_details.direct_superior as direct_superior,
							flight_lodging_details.checked_baggage as checked_baggage,
							flight_lodging_details.last_family_journey_date as last_family_journey_date,
							flight_lodging_details.hosting as hosting,
							flight_lodging_details.singin_date as singin_date,
							flight_lodging_details.output_date as output_date,
							flight_lodging_details.subtotal as subtotal,
							flight_lodging_details.iva as iva,
							flight_lodging_details.taxes as taxes,
							flight_lodging_details.retentions as retentions,
							flight_lodging_details.total as total
						')
						->leftJoin('status_requests','request_models.status','status_requests.idrequestStatus')
						->leftJoin('users as request_user','idRequest','request_user.id')
						->leftJoin('users as elaborate_user','idElaborate','elaborate_user.id')
						->leftJoin('users as reviewed_user','idCheck','reviewed_user.id')
						->leftJoin('users as authorized_user','idAuthorize','authorized_user.id')
						->leftJoin('enterprises as request_enterprise','request_models.idEnterprise','request_enterprise.id')
						->leftJoin('areas as request_direction','idArea','request_direction.id')
						->leftJoin('departments as request_department','idDepartment','request_department.id')
						->leftJoin('projects as request_project','idProject','request_project.idproyect')
						->leftJoin('cat_code_w_bs','cat_code_w_bs.id','request_models.code_wbs')
						->leftJoin('cat_code_e_d_ts','cat_code_e_d_ts.id','request_models.code_edt')
						->leftJoin('accounts as request_account','request_models.account','request_account.idAccAcc')
						->leftJoin('flight_lodgings','flight_lodgings.folio_request','request_models.folio')
						->leftJoin('payment_methods','payment_methods.idpaymentMethod','flight_lodgings.payment_method')
						->leftJoin('flight_lodging_details','flight_lodging_details.flight_lodging_id','flight_lodgings.id')
						->leftJoin(DB::raw('(SELECT flight_lodging_details_id, sum(amount) as amount_taxes from flight_lodging_taxes WHERE type = 1 GROUP BY flight_lodging_details_id) as flight_lodging_taxes'),'flight_lodging_taxes.flight_lodging_details_id','flight_lodging_details.id')
						->leftJoin(DB::raw('(SELECT flight_lodging_details_id, sum(amount) as amount_retentions from flight_lodging_taxes WHERE type = 2 GROUP BY flight_lodging_details_id) as flight_lodging_retentions'),'flight_lodging_retentions.flight_lodging_details_id','flight_lodging_details.id')
						->where('request_models.kind',23)
						->where(function($q) use($request)
						{
							if($request->option_id == 286)
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt($request->option_id)->pluck('enterprise_id'))->orWhereNull('request_models.idEnterprise');
							}
							else
							{
								$q->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt($request->option_id)->pluck('enterprise_id'));
							}
						})
						->where(function($q) use($request)
						{
							if($request->option_id == 286)
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep($request->option_id)->pluck('departament_id'))->orWhereNull('request_models.idDepartment');
							}
							else
							{
								$q->whereIn('request_models.idDepartment',Auth::user()->inChargeDep($request->option_id)->pluck('departament_id'));
							}
						})
						->where(function($q) use ($global_permission,$request)
						{
							if($request->option_id == 286)
							{
								$q->where(function ($query) use ($global_permission)
								{
									if ($global_permission == 0) 
									{
										$query->where('request_models.idElaborate',Auth::user()->id)->orWhere('request_models.idRequest',Auth::user()->id);
									}
								});
							}
						})
						->where(function($q) use($request)
						{
							if($request->option_id == 288)
							{
								$q->where('request_models.status', 4);
							}
							else if($request->option_id == 287)
							{
								$q->where('request_models.status', 3);
							}
							else if($request->option_id == 286)
							{
								if ($request->status != "") 
								{
									$q->whereIn('request_models.status', $request->status);
								}
							}
						})
						->where(function($query) use($request)
						{
							if($request->folio != "")
							{
								$query->where('request_models.folio',$request->folio);
							}
							if ($request->passenger_name != "") 
							{
								$query->where('flight_lodging_details.passenger_name','like','%'.$request->passenger_name.'%');
							}
							if ($request->enterpriseid != "") 
							{
								$query->where('request_models.idEnterprise',$request->enterpriseid);
							}
							if ($request->user_request != "") 
							{
								$query->where('request_models.idRequest', $request->user_request);
							}
							if ($request->projectId != "") 
							{
								$query->where('request_models.idProject',$request->projectId);
							}
							if ($request->type_fligth != "") 
							{
								$query->where('flight_lodging_details.type_flight', $request->type_fligth);
							}
							if($request->mindate != "" && $request->maxdate != "")
							{
								$query->whereBetween('request_models.fDate',[''.Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d').' '.date('00:00:00').'',''.Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d').' '.date('23:59:59').'']);
							}
						})
						->get();


			if(count($requests)==0 || $requests==null)
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
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Vuelos-hospedajes.xlsx');
			$headers = ['Información General','','','','','','','','','','','','','','','','','Datos de revisión','','','Datos de autorización','','','Datos de vuelos y hospedajes solicitados','','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $header)
			{
				if($k <= 16)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol1);
				}
				elseif($k <= 19)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol2);
				}
				elseif($k <= 22)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol3);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($header,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			$subHeaders    = ['Folio','Estado de Solicitud','Título','Solicitante','Elaborado por','Fecha de elaboración','Empresa','Dirección','Departamento','Clasificación de gasto','Proyecto','WBS','EDT','PEMEX/PTI','Referencia','Forma de pago','Moneda','Revisado por','Fecha de Revisión','Comentarios','Autorizado por','Fecha de Autorización','Comentarios','Tipo de vuelo','Descripción/Motivo','Nombre del pasajero','Cargo','Fecha nacimiento','Aerolínea','Ruta','Fecha de partida','Hora de partida','Aerolínea','Ruta','Fecha de regreso','Hora de regreso','Nombre del jefe directo','Equipaje documentado','Fecha de ultimo viaje familiar','Hospedaje','Fecha de ingreso','Fecha de salida','Subtotal','IVA','Impuesto Adicional','Retenciones','Total'];
			$tempSubHeaders = [];
			foreach($subHeaders as $k => $subheader)
			{
				if($k <= 16)
				{
					$tempSubHeaders[] = WriterEntityFactory::createCell($subheader,$mhStyleCol1);
				}
				elseif($k <= 19)
				{
					$tempSubHeaders[] = WriterEntityFactory::createCell($subheader,$mhStyleCol2);
				}
				elseif($k <= 22)
				{
					$tempSubHeaders[] = WriterEntityFactory::createCell($subheader,$mhStyleCol3);
				}
				else
				{
					$tempSubHeaders[] = WriterEntityFactory::createCell($subheader,$mhStyleCol4);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeaders);
			$writer->addRow($rowFromValues);
			$tempFolio     = '';
			$kindRow       = true;
			foreach($requests as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
					if($request->subtotal == '')
					{
						$request->subtotal = '0.0';
					}
					if($request->iva == '')
					{
						$request->iva = '0.0';
					}
					if($request->taxes == '')
					{
						$request->taxes = '0.0';
					}
					if($request->retentions == '')
					{
						$request->retentions = '0.0';
					}
					if($request->total == '')
					{
						$request->total = '0.0';
					}
				}
				else
				{
					$request->folio					= null;
					$request->status				= '';
					$request->title					= '';
					$request->request_user			= '';
					$request->elaborate_user		= '';
					$request->date_request			= '';
					$request->request_enterprise	= '';
					$request->request_direction		= '';
					$request->request_department	= '';
					$request->request_account		= '';
					$request->project				= '';
					$request->code_wbs				= '';
					$request->code_edt				= '';
					$request->request_by_pti		= '';
					$request->reference				= '';
					$request->payment_method		= '';
					$request->currency				= '';
					$request->reviewed_user			= '';
					$request->review_date			= '';
					$request->check_comment			= '';
					$request->authorized_user		= '';
					$request->authorize_date		= '';
					$request->authorize_comment		= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['subtotal','iva','taxes','retentions','total']))
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

	public function showReview($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request_flight = App\FlightLodging::whereHas('request', function (Builder $q) 
			{
				$q->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(287)->pluck('departament_id'));
				});
				$q->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(287)->pluck('enterprise_id'));
				});
				$q->where('status', 3);
			})
			->where('folio_request',$id)
			->first();
			return view('administracion.vuelos_hoteles.detalles',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => $this->module_id,
					'option_id'       => 287,
					'requests_flight' => $request_flight
				]
			);
		}
	}

	public function showAuthorization($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request_flight = App\FlightLodging::whereHas('request', function (Builder $q) 
			{
				$q->where(function($permissionDep)
				{
					$permissionDep->whereIn('idDepartment',Auth::user()->inChargeDep(288)->pluck('departament_id'));
				});
				$q->where(function($permissionEnt)
				{
					$permissionEnt->whereIn('idEnterprise',Auth::user()->inChargeEnt(288)->pluck('enterprise_id'));
				});
				$q->where('status', 4);
			})
			->where('folio_request',$id)
			->first();
			return view('administracion.vuelos_hoteles.detalles',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => $this->module_id,
					'option_id'       => 288,
					'requests_flight' => $request_flight
				]
			);
		}
	}

	public function changeStatus(Request $request, $submodule)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$request_flight = App\RequestModel::find($request->folio);
			if($request->status == 4 || $request->status == 6)
			{
				if($request_flight->status == 3)
				{
					$request_flight->status       = $request->status;
					$request_flight->reviewDate   = new Carbon();
					$request_flight->checkComment = $request->comment;
					$request_flight->idCheck      = Auth::user()->id;
					$request_flight->save();
					$alert = "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				}
				else if($request_flight->status == 4 || $request_flight->status == 5)
				{
					$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				}
				else if($request_flight->status == 6 || $request_flight->status == 7)
				{
					$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				}
				else
				{
					$alert = 'swal("","Error, opciones no válidas", "error")';
				}
				
				if($submodule == 287)
				{
					return searchRedirect($submodule, $alert, 'administration/flights-lodging/review');
				}
				else
				{
					return searchRedirect($submodule, $alert, 'administration/flights-lodging/authorization');
				}
			}
			else if($request->status == 5 || $request->status == 7)
			{
				if($request_flight->status == 4)
				{
					$request_flight->status           = $request->status;
					$request_flight->authorizeDate    = new Carbon();
					$request_flight->idAuthorize      = Auth::user()->id;
					$request_flight->authorizeComment = $request->comment;
					$request_flight->save();
					$alert 	= "swal('', '".Lang::get("messages.request_ruled")."', 'success');";
				}
				else if($request_flight->status == 6 || $request_flight->status == 7)
				{
					$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				}
				else if($request_flight->status == 5)
				{
					$alert = "swal('', '".Lang::get("messages.request_already_ruled")."', 'error');";
				}
				else
				{
					$alert = 'swal("","Error, opciones no válidas", "error")';
				}
				if($submodule == 287)
				{
					return searchRedirect($submodule, $alert, 'administration/flights-lodging/review');
				}
				else
				{
					return searchRedirect($submodule, $alert, 'administration/flights-lodging/authorization');
				}
			}
			else
			{
				$alert = 'swal("", "Por favor seleccione si desea aprobar o rechazar la solicitud.", "error")';
				return back()->with('alert',$alert);
			}
		}
	}

	public function details($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			$request_flight = App\FlightLodging::where('folio_request',$id)
			->whereHas('request', function (Builder $q) 
			{
				$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(288)->pluck('enterprise_id'))->orWhereNull('idEnterprise')
					->whereIn('idDepartment',Auth::user()->inChargeDep(288)->pluck('departament_id'))->orWhereNull('idDepartment');
			})
			->first();
			return view('administracion.vuelos_hoteles.detalles',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => $this->module_id,
					'option_id'       => 286,
					'requests_flight' => $request_flight
				]
			);
		}
	}

	public function exportPdf(RequestModel $flight_request)
	{
		//return view('administracion.vuelos_hoteles.pdf',['flight_request' => $flight_request]);

		$pdf = \App::make('dompdf.wrapper');
		$pdf->getDomPDF()->set_option('enable_php', true);

		$pdf->loadView('administracion.vuelos_hoteles.pdf',[
			'flight_request' => $flight_request
		])->setPaper('a4');

		return $pdf->download('Solicitud #'.$flight_request->folio.'.pdf');
	}
}
