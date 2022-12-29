<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Vehicle;
use App\VehicleDocument;
use App\VehicleFines;
use App\VehicleMechanicalService;
use App\VehicleOwner;
use App\VehicleTaxes;
use App\VehicleFuel;
use App\VehicleInsurance;
use App\Kilometers;
use App;
use Lang;
use Ilovepdf\CompressTask;
use App\Module;
use Carbon\Carbon;
use Excel;
use DB;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministrationVehiclesController extends Controller
{
	protected $module_id = 292;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{   
			$data = Module::find($this->module_id);
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
		if (Auth::user()->module->where('id',293)->count()>0) 
		{
			$data = Module::find($this->module_id);
			return view('administracion.vehiculos.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 293
			]);
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',294)->count()>0) 
		{
			$data = Module::find($this->module_id);

			$vehicles = Vehicle::where(function($query) use ($request)
			{
				if ($request->brand != "") 
				{
					$query->where('brand','like','%'.$request->brand.'%');
				}

				if ($request->model != "") 
				{
					$query->where('model','like','%'.$request->model.'%');
				}

				if ($request->vehicle_status != "") 
				{
					$query->where('vehicle_status','like','%'.$request->vehicle_status.'%');
				}

				if ($request->serial_number != "") 
				{
					$query->where('serial_number','like','%'.$request->serial_number.'%');
				}
			})
			->orderBy('id','DESC')
			->paginate(10);

			return view('administracion.vehiculos.busqueda',
			[
				'id'				=> $data['father'],
				'title'				=> $data['name'],
				'details'			=> $data['details'],
				'child_id'			=> $this->module_id,
				'option_id'			=> 294,
				'vehicles'			=> $vehicles,
				'brand'				=> $request->brand,
				'model'				=> $request->model,
				'vehicle_status'	=> $request->vehicle_status,
				'serial_number'		=> $request->serial_number,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function edit(Request $request,Vehicle $vehicle)
	{
		if (Auth::user()->module->where('id',294)->count()>0) 
		{
			$data = Module::find($this->module_id);

			return view('administracion.vehiculos.alta',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id' 	=> $this->module_id,
				'option_id' => 294,
				'vehicle'	=> $vehicle,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function store(Request $request)
	{
		
		if (Auth::user()->module->where('id',293)->count()>0) 
		{
			$new_vehicle					= new Vehicle();
			$new_vehicle->brand				= $request->brand;
			$new_vehicle->sub_brand			= $request->sub_brand;
			$new_vehicle->model				= $request->model;
			$new_vehicle->serial_number		= $request->serial_number;
			$new_vehicle->plates			= $request->plates;
			$new_vehicle->kilometer			= $request->kilometer;
			$new_vehicle->vehicle_status	= $request->vehicle_status;
			$new_vehicle->fuel_type			= $request->fuel_type;
			$new_vehicle->tag				= $request->tag;
			$new_vehicle->users_id			= Auth::user()->id;
			$new_vehicle->owner_type		= $request->owner_type;
			$new_vehicle->owner_external	= $request->owner_external;

			if ($request->owner_type == "fisica" && $request->owner_external == "existente") 
			{
				$new_vehicle->vehicles_owners_id 	= $request->owner_exists;
			}
			else if ($request->owner_type=="moral" && $request->owner_external == "existente") 
			{
				$new_vehicle->vehicles_owners_id	= $request->owner_exists;
			}
			else if ($request->owner_type=="fisica" && $request->owner_external == "nuevo")
			{
				$new_owner					= new VehicleOwner();
				$new_owner->name			= $request->physical_name;
				$new_owner->last_name		= $request->physical_last_name;
				$new_owner->scnd_last_name	= $request->physical_scnd_last_name;
				$new_owner->curp			= $request->physical_curp;
				$new_owner->rfc				= $request->physical_rfc;
				$new_owner->imss			= $request->physical_imss;
				$new_owner->email			= $request->physical_email;
				$new_owner->street			= $request->physical_street;
				$new_owner->number			= $request->physical_number;
				$new_owner->colony			= $request->physical_colony;
				$new_owner->cp				= $request->physical_cp;
				$new_owner->city			= $request->physical_city;
				$new_owner->state_id		= $request->physical_state;
				$new_owner->type 			= 1;
				$new_owner->users_id		= Auth::user()->id;
				$new_owner->save();

				$new_vehicle->vehicles_owners_id = $new_owner->id;
			}
			else if ($request->owner_type=="moral" && $request->owner_external == "nuevo")
			{
				$new_owner				= new VehicleOwner();
				$new_owner->name		= $request->moral_name;
				$new_owner->rfc			= $request->moral_rfc;
				$new_owner->email		= $request->moral_email;
				$new_owner->street		= $request->moral_street;
				$new_owner->number		= $request->moral_number;
				$new_owner->colony		= $request->moral_colony;
				$new_owner->cp			= $request->moral_cp;
				$new_owner->city		= $request->moral_city;
				$new_owner->state_id	= $request->moral_state;
				$new_owner->type 		= 2;
				$new_owner->users_id	= Auth::user()->id;
				$new_owner->save();

				$new_vehicle->vehicles_owners_id = $new_owner->id;
			}

			$new_vehicle->save();

			if (isset($request->t_init_date) && count($request->t_init_date)>0)
			{
				for ($i=0; $i < count($request->t_init_date); $i++)
				{
					$new_kilometer							= new Kilometers();
					$new_kilometer->date_kilometer			= $request->t_init_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_init_date[$i])->format('Y-m-d') : null;
					$new_kilometer->kilometer				= $request->t_init_kilometer[$i];
					$new_kilometer->vehicles_id				= $new_vehicle->id;
					$new_kilometer->save();
				}
			}

			/* ESPECIFICACIONES TECNICAS */
			if (isset($request->technical_specifications_document) && count($request->technical_specifications_document)>0) 
			{
				for ($i=0; $i < count($request->technical_specifications_document); $i++) 
				{ 
					if ($request->technical_specifications_path[$i] != "") 
					{
						$new_doc						= new VehicleDocument();
						$new_doc->name					= $request->technical_specifications_document[$i];
						$new_doc->path					= $request->technical_specifications_path[$i];
						$new_doc->cat_type_document_id	= 1;
						$new_doc->vehicles_id			= $new_vehicle->id;
						$new_doc->users_id				= Auth::user()->id;
						$new_doc->save();
					}
				}
			}

			/* PROPIETARIO */
			if (isset($request->owner_document) && count($request->owner_document)>0) 
			{
				for ($i=0; $i < count($request->owner_document); $i++) 
				{ 
					if ($request->owner_document[$i] != "") 
					{
						$new_doc						= new VehicleDocument();
						$new_doc->name					= $request->owner_document[$i];
						$new_doc->path					= $request->owner_path[$i];
						$new_doc->cat_type_document_id	= 2;
						$new_doc->vehicles_id			= $new_vehicle->id;
						$new_doc->users_id				= Auth::user()->id;
						$new_doc->save();
					}
				}
			}

			/* COMBUSTIBLE */
  			if (isset($request->t_vehicle_fuel_id) && count($request->t_vehicle_fuel_id)>0) 
  			{
	  			for ($i=0; $i < count($request->t_vehicle_fuel_id); $i++) 
	  			{ 
	  				if ($request->t_vehicle_fuel_id[$i] == "x") 
					{
						$new_fuel				= new VehicleFuel();
						$new_fuel->fuel_type	= $request->t_fuel_type[$i];
						$new_fuel->tag			= $request->t_tag[$i];
						$new_fuel->date			= $request->t_fuel_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fuel_date[$i])->format('Y-m-d') : null;
						$new_fuel->total		= $request->t_fuel_total[$i];
						$new_fuel->vehicles_id	= $new_vehicle->id;
						$new_fuel->users_id		= Auth::user()->id;
						$new_fuel->save();

						$num_doc = $i+1;
						$t_fuel_name_document	= "t_fuel_name_document".$num_doc;
						$t_fuel_path			= "t_fuel_path".$num_doc;

						
						if (isset($request->$t_fuel_name_document) && count($request->$t_fuel_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_fuel_name_document); $d++) 
							{ 
								$new_doc						= new VehicleDocument();
								$new_doc->name					= $request->$t_fuel_name_document[$d];
								$new_doc->path					= $request->$t_fuel_path[$d];
								$new_doc->cat_type_document_id	= 3;
								$new_doc->vehicles_fuel_id		= $new_fuel->id;
								$new_doc->users_id				= Auth::user()->id;
								$new_doc->save();
							}
						}
	  				}
	  			}
  			}

  			/* IMPUESTO */
  			if (isset($request->t_vehicle_taxes_id) && count($request->t_vehicle_taxes_id)>0) 
  			{
	  			for ($i=0; $i < count($request->t_vehicle_taxes_id); $i++) 
	  			{ 
	  				if ($request->t_vehicle_taxes_id[$i] == "x") 
	  				{
						$new_tax							= new VehicleTaxes();
						$new_tax->date_verification			= $request->t_date_verification[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_date_verification[$i])->format('Y-m-d') : null;
						$new_tax->next_date_verification	= $request->t_next_date_verification[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_next_date_verification[$i])->format('Y-m-d') : null;
						$new_tax->total						= $request->t_total_verification[$i];
						$new_tax->monto_gestoria			= $request->t_monto_gestoria[$i];
						$new_tax->vehicles_id 				= $new_vehicle->id;
						$new_tax->users_id					= Auth::user()->id;
						$new_tax->save();

						$num_doc = $i+1;
						$t_taxes_name_document	= "t_taxes_name_document".$num_doc;
						$t_taxes_path			= "t_taxes_path".$num_doc;
						$t_taxes_date			= "t_taxes_date".$num_doc;

						
						if (isset($request->$t_taxes_name_document) && count($request->$t_taxes_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_taxes_name_document); $d++) 
							{ 
								$new_doc						= new VehicleDocument();
								$new_doc->name					= $request->$t_taxes_name_document[$d];
								$new_doc->path					= $request->$t_taxes_path[$d];
								$new_doc->date					= Carbon::createFromFormat('d-m-Y',$request->$t_taxes_date[$d])->format('Y-m-d');
								$new_doc->cat_type_document_id	= 4;
								$new_doc->vehicles_taxes_id		= $new_tax->id;
								$new_doc->users_id				= Auth::user()->id;
								$new_doc->save();
							}
						}
	  				}
	  			}
  			}

  			/* MULTAS */
  			if (isset($request->t_vehicle_fine_id) && count($request->t_vehicle_fine_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_vehicle_fine_id); $i++) 
  				{ 
  					if ($request->t_vehicle_fine_id[$i] == "x") 
  					{
						$new_fine						= new VehicleFines();
						$new_fine->real_employee_id		= $request->t_fine_driver[$i];
						$new_fine->status				= $request->t_fine_status[$i];
						$new_fine->date					= $request->t_fine_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_date[$i])->format('Y-m-d') : null;
						$new_fine->payment_date			= $request->t_fine_payment_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_payment_date[$i])->format('Y-m-d') : null;
						$new_fine->payment_limit_date	= $request->t_fine_payment_limit_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_payment_limit_date[$i])->format('Y-m-d') : null;
						$new_fine->total				= $request->t_fine_total[$i];
						$new_fine->vehicles_id			= $new_vehicle->id;
						$new_fine->users_id				= Auth::user()->id;
						$new_fine->save();

						$num_doc = $i+1;
						$t_fines_name_document	= "t_fines_name_document".$num_doc;
						$t_fines_path			= "t_fines_path".$num_doc;

						if (isset($request->$t_fines_name_document) && count($request->$t_fines_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_fines_name_document); $d++) 
							{ 
								$new_doc						= new VehicleDocument();
								$new_doc->name					= $request->$t_fines_name_document[$d];
								$new_doc->path					= $request->$t_fines_path[$d];
								$new_doc->cat_type_document_id	= 5;
								$new_doc->vehicles_fines_id		= $new_fine->id;
								$new_doc->users_id				= Auth::user()->id;
								$new_doc->save();
							}
						}
  					}
  				}
  			}

  			/*  SEGURO  */
  			if (isset($request->t_insurance_id) && count($request->t_insurance_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_insurance_id); $i++) 
  				{ 
  					if ($request->t_insurance_id[$i] == "x") 
  					{
						$new_insurance						= new VehicleInsurance();
						$new_insurance->insurance_carrier	= $request->t_insurance_carrier[$i];
						$new_insurance->expiration_date		= $request->t_expiration_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_expiration_date[$i])->format('Y-m-d') : null;
						$new_insurance->total				= $request->t_insurance_total[$i];
						$new_insurance->vehicles_id			= $new_vehicle->id;
						$new_insurance->users_id			= Auth::user()->id;
						$new_insurance->save();

						$num_doc = $i+1;
						$t_insurance_name_document	= "t_insurance_name_document".$num_doc;
						$t_insurance_path			= "t_insurance_path".$num_doc;

						if (isset($request->$t_insurance_name_document) && count($request->$t_insurance_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_insurance_name_document); $d++) 
							{ 
								$new_doc									= new VehicleDocument();
								$new_doc->name								= $request->$t_insurance_name_document[$d];
								$new_doc->path								= $request->$t_insurance_path[$d];
								$new_doc->cat_type_document_id				= 6;
								$new_doc->vehicles_insurances_id			= $new_insurance->id;
								$new_doc->users_id							= Auth::user()->id;
								$new_doc->save();
							}
						}
  					}
  				}
  			}

  			/*  SERVICIOS MECANICOS  */
  			if (isset($request->t_mechanical_services_id) && count($request->t_mechanical_services_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_mechanical_services_id); $i++) 
  				{ 
  					if ($request->t_mechanical_services_id[$i] == "x") 
  					{
						$new_ms						= new VehicleMechanicalService();
						$new_ms->date_last_service	= $request->t_date_last_service[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_date_last_service[$i])->format('Y-m-d') : null;
						$new_ms->next_service_date	= $request->t_next_service_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_next_service_date[$i])->format('Y-m-d') : null;
						$new_ms->repairs			= $request->t_repairs[$i];
						$new_ms->total				= $request->t_mechanical_service_total[$i];
						$new_ms->vehicles_id 		= $new_vehicle->id;
						$new_ms->users_id 			= Auth::user()->id;
						$new_ms->save();

						$num_doc = $i+1;
						$t_ms_name_document	= "t_ms_name_document".$num_doc;
						$t_ms_path			= "t_ms_path".$num_doc;

						if (isset($request->$t_ms_name_document) && count($request->$t_ms_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_ms_name_document); $d++) 
							{ 
								$new_doc									= new VehicleDocument();
								$new_doc->name								= $request->$t_ms_name_document[$d];
								$new_doc->path								= $request->$t_ms_path[$d];
								$new_doc->cat_type_document_id				= 7;
								$new_doc->vehicles_mechanical_services_id	= $new_ms->id;
								$new_doc->users_id							= Auth::user()->id;
								$new_doc->save();
							}
						}
  					}
  				}
  			}
			$alert	= "swal('','".Lang::get("messages.record_created")."', 'success');";
  			return redirect('administration/vehicles/search')->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function update(Vehicle $vehicle, Request $request)
	{
		if (Auth::user()->module->where('id',294)->count()>0) 
		{
			$vehicle->brand				= $request->brand;
			$vehicle->sub_brand			= $request->sub_brand;
			$vehicle->model				= $request->model;
			$vehicle->serial_number		= $request->serial_number;
			$vehicle->plates			= $request->plates;
			$vehicle->kilometer			= $request->kilometer;
			$vehicle->vehicle_status	= $request->vehicle_status;
			$vehicle->fuel_type			= $request->fuel_type;
			$vehicle->tag				= $request->tag;
			$vehicle->users_id			= Auth::user()->id;
			$vehicle->owner_type		= $request->owner_type;
			$vehicle->owner_external	= $request->owner_external;

			if ($request->owner_type == "fisica" && $request->owner_external == "existente") 
			{
				$vehicle->vehicles_owners_id = $request->owner_exists;
			}
			else if ($request->owner_type=="moral" && $request->owner_external == "existente") 
			{
				$vehicle->vehicles_owners_id	= $request->owner_exists;
			}
			else if ($request->owner_type=="fisica" && $request->owner_external == "nuevo")
			{
				//if (isset($request->owner_new) && $request->owner_new != "") 
				//{
				//	// $vehicle->vehicles_owners_id = $request->owner_new;
				//	$vehicle_fisica_owner					= VehicleOwner::find($request->owner_new);
				//	$vehicle_fisica_owner->name				= $request->physical_name;
				//	$vehicle_fisica_owner->last_name		= $request->physical_last_name;
				//	$vehicle_fisica_owner->scnd_last_name	= $request->physical_scnd_last_name;
				//	$vehicle_fisica_owner->curp				= $request->physical_curp;
				//	$vehicle_fisica_owner->rfc				= $request->physical_rfc;
				//	$vehicle_fisica_owner->imss				= $request->physical_imss;
				//	$vehicle_fisica_owner->email			= $request->physical_email;
				//	$vehicle_fisica_owner->street			= $request->physical_street;
				//	$vehicle_fisica_owner->number			= $request->physical_number;
				//	$vehicle_fisica_owner->colony			= $request->physical_colony;
				//	$vehicle_fisica_owner->cp				= $request->physical_cp;
				//	$vehicle_fisica_owner->city				= $request->physical_city;
				//	$vehicle_fisica_owner->state_id			= $request->physical_state;
				//	$vehicle_fisica_owner->type 			= 1;
				//	$vehicle_fisica_owner->users_id			= Auth::user()->id;
				//	$vehicle_fisica_owner->save();
				//}
				//else
				//{
					$new_owner					= new VehicleOwner();
					$new_owner->name			= $request->physical_name;
					$new_owner->last_name		= $request->physical_last_name;
					$new_owner->scnd_last_name	= $request->physical_scnd_last_name;
					$new_owner->curp			= $request->physical_curp;
					$new_owner->rfc				= $request->physical_rfc;
					$new_owner->imss			= $request->physical_imss;
					$new_owner->email			= $request->physical_email;
					$new_owner->street			= $request->physical_street;
					$new_owner->number			= $request->physical_number;
					$new_owner->colony			= $request->physical_colony;
					$new_owner->cp				= $request->physical_cp;
					$new_owner->city			= $request->physical_city;
					$new_owner->state_id		= $request->physical_state;
					$new_owner->type 			= 1;
					$new_owner->users_id		= Auth::user()->id;
					$new_owner->save();

					$vehicle->vehicles_owners_id = $new_owner->id;
				//}
			}
			else if ($request->owner_type=="moral" && $request->owner_external == "nuevo")
			{
				//if (isset($request->owner_new) && $request->owner_new != "") 
				//{
				//	// $vehicle->vehicles_owners_id = $request->owner_new;
				//	$vehicle_moral_owner			= VehicleOwner::find($request->owner_new);
				//	$vehicle_moral_owner->name		= $request->moral_name;
				//	$vehicle_moral_owner->rfc		= $request->moral_rfc;
				//	$vehicle_moral_owner->email		= $request->moral_email;
				//	$vehicle_moral_owner->street	= $request->moral_street;
				//	$vehicle_moral_owner->number	= $request->moral_number;
				//	$vehicle_moral_owner->colony	= $request->moral_colony;
				//	$vehicle_moral_owner->cp		= $request->moral_cp;
				//	$vehicle_moral_owner->city		= $request->moral_city;
				//	$vehicle_moral_owner->state_id	= $request->moral_state;
				//	$vehicle_moral_owner->type 	= 2;
				//	$vehicle_moral_owner->users_id	= Auth::user()->id;
				//	$vehicle_moral_owner->save();
				//}
				//else
				//{
					$new_owner				= new VehicleOwner();
					$new_owner->name		= $request->moral_name;
					$new_owner->rfc			= $request->moral_rfc;
					$new_owner->email		= $request->moral_email;
					$new_owner->street		= $request->moral_street;
					$new_owner->number		= $request->moral_number;
					$new_owner->colony		= $request->moral_colony;
					$new_owner->cp			= $request->moral_cp;
					$new_owner->city		= $request->moral_city;
					$new_owner->state_id	= $request->moral_state;
					$new_owner->type 		= 2;
					$new_owner->users_id	= Auth::user()->id;
					$new_owner->save();

					$vehicle->vehicles_owners_id = $new_owner->id;
				//}
			}
			$vehicle->save();

			if (isset($request->t_id_kilometer) && count($request->t_id_kilometer)>0)
			{
				for ($i=0; $i < count($request->t_id_kilometer); $i++)
				{
					if ($request->t_id_kilometer[$i] == "X") 
					{
						$updateKilometer	= new Kilometers();
					}
					else
					{
						$updateKilometer	= Kilometers::find($request->t_id_kilometer[$i]);
					}
					$updateKilometer->date_kilometer	= $request->t_init_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_init_date[$i])->format('Y-m-d') : null;
					$updateKilometer->kilometer			= $request->t_init_kilometer[$i];
					$updateKilometer->vehicles_id		= $vehicle->id;
					$updateKilometer->save();
				}
			}
			if(isset($request->delete_document) && count($request->delete_document)>0)
			{
				for ($i=0; $i < count($request->delete_document); $i++) 
				{ 
					$docDelete = VehicleDocument::find($request->delete_document[$i]);
					\Storage::disk('public')->delete('/docs/vehicles/'.$docDelete->path);
					VehicleDocument::where('id',$request->delete_document[$i])->delete();
				}
			}

			if(isset($request->delete_vehicle_fuel) && count($request->delete_vehicle_fuel)>0)
			{
				for ($i=0; $i < count($request->delete_vehicle_fuel); $i++) 
				{
					$docsDelete = VehicleDocument::where('vehicles_fuel_id',$request->delete_vehicle_fuel[$i])->get();
					foreach($docsDelete as $doc)
					{
						\Storage::disk('public')->delete('/docs/vehicles/'.$doc->path);
						VehicleDocument::where('id',$doc->id)->delete();
					}
					VehicleFuel::where('id',$request->delete_vehicle_fuel[$i])->delete();
				}
			}
			
			if(isset($request->delete_vehicle_fine) && count($request->delete_vehicle_fine)>0)
			{
				for ($i=0; $i < count($request->delete_vehicle_fine); $i++) 
				{
					$docsDelete = VehicleDocument::where('vehicles_fines_id',$request->delete_vehicle_fine[$i])->get();
					foreach($docsDelete as $doc)
					{
						\Storage::disk('public')->delete('/docs/vehicles/'.$doc->path);
						VehicleDocument::where('id',$doc->id)->delete();
					}
					VehicleFines::where('id',$request->delete_vehicle_fine[$i])->delete();
				}
			}

			if(isset($request->delete_vehicle_taxes) && count($request->delete_vehicle_taxes)>0)
			{
				for ($i=0; $i < count($request->delete_vehicle_taxes); $i++) 
				{
					$docsDelete = VehicleDocument::where('vehicles_taxes_id',$request->delete_vehicle_taxes[$i])->get();
					foreach($docsDelete as $doc)
					{
						\Storage::disk('public')->delete('/docs/vehicles/'.$doc->path);
						VehicleDocument::where('id',$doc->id)->delete();
					}
					VehicleTaxes::where('id',$request->delete_vehicle_taxes[$i])->delete();
				}
			}

			if(isset($request->delete_insurance) && count($request->delete_insurance)>0)
			{
				for ($i=0; $i < count($request->delete_insurance); $i++) 
				{
					$docsDelete = VehicleDocument::where('vehicles_insurances_id',$request->delete_insurance[$i])->get();
					foreach($docsDelete as $doc)
					{
						\Storage::disk('public')->delete('/docs/vehicles/'.$doc->path);
						VehicleDocument::where('id',$doc->id)->delete();
					}
					VehicleInsurance::where('id',$request->delete_insurance[$i])->delete();
				}
			}

			if(isset($request->delete_mechanical_services) && count($request->delete_mechanical_services)>0)
			{
				for ($i=0; $i < count($request->delete_mechanical_services); $i++) 
				{
					$docsDelete = VehicleDocument::where('vehicles_mechanical_services_id',$request->delete_mechanical_services[$i])->get();
					foreach($docsDelete as $doc)
					{
						\Storage::disk('public')->delete('/docs/vehicles/'.$doc->path);
						VehicleDocument::where('id',$doc->id)->delete();
					}
					VehicleMechanicalService::where('id',$request->delete_mechanical_services[$i])->delete();
				}
			}

			if (isset($request->technical_specifications_document) && count($request->technical_specifications_document)>0) 
			{
				for ($i=0; $i < count($request->technical_specifications_document); $i++) 
				{ 
					if ($request->technical_specifications_path[$i] != "") 
					{
						$new_doc						= new VehicleDocument();
						$new_doc->name					= $request->technical_specifications_document[$i];
						$new_doc->path					= $request->technical_specifications_path[$i];
						$new_doc->cat_type_document_id	= 1;
						$new_doc->vehicles_id			= $vehicle->id;
						$new_doc->users_id				= Auth::user()->id;
						$new_doc->save();
					}
				}
			}

			/* PROPIETARIO */
			if (isset($request->owner_document) && count($request->owner_document)>0) 
			{
				for ($i=0; $i < count($request->owner_document); $i++) 
				{ 
					if ($request->owner_document[$i] != "") 
					{
						$new_doc						= new VehicleDocument();
						$new_doc->name					= $request->owner_document[$i];
						$new_doc->path					= $request->owner_path[$i];
						$new_doc->cat_type_document_id	= 2;
						$new_doc->vehicles_id			= $vehicle->id;
						$new_doc->users_id				= Auth::user()->id;
						$new_doc->save();
					}
				}
			}
			/* COMBUSTIBLE */
  			if (isset($request->t_vehicle_fuel_id) && count($request->t_vehicle_fuel_id)>0) 
  			{
	  			for ($i=0; $i < count($request->t_vehicle_fuel_id); $i++) 
	  			{ 
	  				if ($request->t_vehicle_fuel_id[$i] == "x") 
					{
						$new_fuel = new VehicleFuel();
					}
					else
					{
						$new_fuel = VehicleFuel::find($request->t_vehicle_fuel_id[$i]);
					}
						$new_fuel->fuel_type	= $request->t_fuel_type[$i];
						$new_fuel->tag			= $request->t_tag[$i];
						$new_fuel->date			= $request->t_fuel_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fuel_date[$i])->format('Y-m-d') : null;
						$new_fuel->total		= $request->t_fuel_total[$i];
						$new_fuel->vehicles_id	= $vehicle->id;
						$new_fuel->users_id		= Auth::user()->id;
						$new_fuel->save();

						$num_doc = $i+1;
						$t_fuel_id				= "t_fuel_id_document".$num_doc;
						$t_fuel_name_document	= "t_fuel_name_document".$num_doc;
						$t_fuel_path			= "t_fuel_path".$num_doc;
						
						if (isset($request->$t_fuel_name_document) && count($request->$t_fuel_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_fuel_name_document); $d++) 
							{ 
								if ($request->$t_fuel_id[$d] == "x")
								{
									$new_doc = new VehicleDocument();
								}
								else
								{
									$new_doc = VehicleDocument::find($request->$t_fuel_id[$d]);
								}
								$new_doc->name					= $request->$t_fuel_name_document[$d];
								$new_doc->path					= $request->$t_fuel_path[$d];
								$new_doc->cat_type_document_id	= 3;
								$new_doc->vehicles_fuel_id		= $new_fuel->id;
								$new_doc->users_id				= Auth::user()->id;
								$new_doc->save();
							}
						}
	  			}
  			}

  			/* IMPUESTO */
  			if (isset($request->t_vehicle_taxes_id) && count($request->t_vehicle_taxes_id)>0) 
  			{
	  			for ($i=0; $i < count($request->t_vehicle_taxes_id); $i++) 
	  			{ 
	  				if ($request->t_vehicle_taxes_id[$i] == "x") 
	  				{
						$new_tax = new VehicleTaxes();
					}
					else
					{
						$new_tax = VehicleTaxes::find($request->t_vehicle_taxes_id[$i]);
					}
					$new_tax->date_verification			= $request->t_date_verification[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_date_verification[$i])->format('Y-m-d') : null;
					$new_tax->next_date_verification	= $request->t_next_date_verification[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_next_date_verification[$i])->format('Y-m-d') : null;
					$new_tax->total						= $request->t_total_verification[$i];
					$new_tax->monto_gestoria			= $request->t_monto_gestoria[$i];
					$new_tax->vehicles_id 				= $vehicle->id;
					$new_tax->users_id					= Auth::user()->id;
					$new_tax->save();

					$num_doc = $i+1;
					$t_taxes_id				= "t_taxes_id_document".$num_doc;
					$t_taxes_name_document	= "t_taxes_name_document".$num_doc;
					$t_taxes_path			= "t_taxes_path".$num_doc;
					$t_taxes_date			= "t_taxes_date".$num_doc;

					
					if (isset($request->$t_taxes_name_document) && count($request->$t_taxes_name_document)>0) 
					{
						for ($d=0; $d < count($request->$t_taxes_name_document); $d++) 
						{ 
							if ($request->$t_taxes_id[$d] == "x")
							{
								$new_doc = new VehicleDocument();
							}
							else
							{
								$new_doc = VehicleDocument::find($request->$t_taxes_id[$d]);
							}
							$new_doc->name					= $request->$t_taxes_name_document[$d];
							$new_doc->path					= $request->$t_taxes_path[$d];
							$new_doc->date					= Carbon::createFromFormat('d-m-Y',$request->$t_taxes_date[$d])->format('Y-m-d');
							$new_doc->cat_type_document_id	= 4;
							$new_doc->vehicles_taxes_id		= $new_tax->id;
							$new_doc->users_id				= Auth::user()->id;
							$new_doc->save();
						}
					}
	  			}
  			}

  			/* MULTAS */
  			if (isset($request->t_vehicle_fine_id) && count($request->t_vehicle_fine_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_vehicle_fine_id); $i++) 
  				{ 
  					if ($request->t_vehicle_fine_id[$i] == "x") 
  					{
						$new_fine = new VehicleFines();
					}
					else
					{
						$new_fine = VehicleFines::find($request->t_vehicle_fine_id[$i]);
					}
					$new_fine->real_employee_id		= $request->t_fine_driver[$i];
					$new_fine->status				= $request->t_fine_status[$i];
					$new_fine->date					= $request->t_fine_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_date[$i])->format('Y-m-d') : null;
					$new_fine->payment_date			= $request->t_fine_payment_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_payment_date[$i])->format('Y-m-d') : null;
					$new_fine->payment_limit_date	= $request->t_fine_payment_limit_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_fine_payment_limit_date[$i])->format('Y-m-d') : null;
					$new_fine->total				= $request->t_fine_total[$i];
					$new_fine->vehicles_id			= $vehicle->id;
					$new_fine->users_id				= Auth::user()->id;
					$new_fine->save();

					$num_doc = $i+1;
					$t_fines_id				= "t_fines_id".$num_doc;
					$t_fines_name_document	= "t_fines_name_document".$num_doc;
					$t_fines_path			= "t_fines_path".$num_doc;

					if (isset($request->$t_fines_name_document) && count($request->$t_fines_name_document)>0) 
					{
						for ($d=0; $d < count($request->$t_fines_name_document); $d++) 
						{ 
							if($request->$t_fines_id[$d] == "x")
							{
								$new_doc = new VehicleDocument();
							}
							else
							{
								$new_doc = VehicleDocument::find($request->$t_fines_id[$d]);
							}
							$new_doc->name					= $request->$t_fines_name_document[$d];
							$new_doc->path					= $request->$t_fines_path[$d];
							$new_doc->cat_type_document_id	= 5;
							$new_doc->vehicles_fines_id		= $new_fine->id;
							$new_doc->users_id				= Auth::user()->id;
							$new_doc->save();
						}
					}
  				}
  			}

  			/*  SEGURO  */
  			if (isset($request->t_insurance_id) && count($request->t_insurance_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_insurance_id); $i++) 
  				{ 
  					if ($request->t_insurance_id[$i] == "x") 
  					{
						$new_insurance = new VehicleInsurance();
					}
					else
					{
						$new_insurance = VehicleInsurance::find($request->t_insurance_id[$i]);
					}
					$new_insurance->insurance_carrier	= $request->t_insurance_carrier[$i];
					$new_insurance->expiration_date		= $request->t_expiration_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_expiration_date[$i])->format('Y-m-d') : null;
					$new_insurance->total				= $request->t_insurance_total[$i];
					$new_insurance->vehicles_id			= $vehicle->id;
					$new_insurance->users_id			= Auth::user()->id;
					$new_insurance->save();

					$num_doc = $i+1;
					$t_insurance_id_document	= "t_insurance_id_document".$num_doc;
					$t_insurance_name_document	= "t_insurance_name_document".$num_doc;
					$t_insurance_path			= "t_insurance_path".$num_doc;

					if (isset($request->$t_insurance_name_document) && count($request->$t_insurance_name_document)>0) 
					{
						for ($d=0; $d < count($request->$t_insurance_name_document); $d++) 
						{
							if($request->$t_insurance_id_document[$d] == "x")
							{
								$new_doc = new VehicleDocument();
							}
							else
							{
								$new_doc = VehicleDocument::find($request->$t_insurance_id_document[$d]);
							}
							$new_doc->name								= $request->$t_insurance_name_document[$d];
							$new_doc->path								= $request->$t_insurance_path[$d];
							$new_doc->cat_type_document_id				= 6;
							$new_doc->vehicles_insurances_id			= $new_insurance->id;
							$new_doc->users_id							= Auth::user()->id;
							$new_doc->save();
						}
					}
  				}
  			}

  			/*  SERVICIOS MECANICOS  */
  			if (isset($request->t_mechanical_services_id) && count($request->t_mechanical_services_id)>0) 
  			{
  				for ($i=0; $i < count($request->t_mechanical_services_id); $i++) 
  				{ 
  					if ($request->t_mechanical_services_id[$i] == "x") 
  					{
						$new_ms = new VehicleMechanicalService();
					}
					else
					{
						$new_ms = VehicleMechanicalService::find($request->t_mechanical_services_id[$i]);
					}
						$new_ms->date_last_service	= $request->t_date_last_service[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_date_last_service[$i])->format('Y-m-d') : null;
						$new_ms->next_service_date	= $request->t_next_service_date[$i] != '' ? Carbon::createFromFormat('d-m-Y',$request->t_next_service_date[$i])->format('Y-m-d') : null;
						$new_ms->repairs			= $request->t_repairs[$i];
						$new_ms->total				= $request->t_mechanical_service_total[$i];
						$new_ms->vehicles_id 		= $vehicle->id;
						$new_ms->users_id 			= Auth::user()->id;
						$new_ms->save();

						$num_doc = $i+1;
						$t_ms_id_document	= "t_ms_id_document".$num_doc;
						$t_ms_name_document	= "t_ms_name_document".$num_doc;
						$t_ms_path			= "t_ms_path".$num_doc;

						if (isset($request->$t_ms_name_document) && count($request->$t_ms_name_document)>0) 
						{
							for ($d=0; $d < count($request->$t_ms_name_document); $d++) 
							{ 
								if($request->$t_ms_id_document[$d] == "x")
								{
									$new_doc = new VehicleDocument();
								}
								else
								{
									$new_doc = VehicleDocument::find($request->$t_ms_id_document[$d]);
								}
								$new_doc->name								= $request->$t_ms_name_document[$d];
								$new_doc->path								= $request->$t_ms_path[$d];
								$new_doc->cat_type_document_id				= 7;
								$new_doc->vehicles_mechanical_services_id	= $new_ms->id;
								$new_doc->users_id							= Auth::user()->id;
								$new_doc->save();
							}
						}
  					
  				}
  			}
  			$alert	= "swal('','".Lang::get("messages.record_updated")."', 'success');";
  			return redirect()->route('vehicle.edit',$vehicle->id)->with('alert',$alert);

		}
	}

	public function export(Request $request)
	{
		if (Auth::user()->module->where('id',294)->count()>0) 
		{
			$vehicles = DB::table('vehicles')->selectRaw(
						'
							vehicles.id, 
							vehicles.brand,
							vehicles.sub_brand,
							vehicles.model,
							vehicles.serial_number,
							vehicles.plates,
							vehicles.vehicle_status,
							vehicles.owner_type,
							CONCAT_WS(" ", vehicle_owners.name,vehicle_owners.last_name, vehicle_owners.scnd_last_name) as ownerName,
							vehicle_fuelsTemp.vehicle_fuels_Total as fuelsTotal,
							vehicle_taxesTemp.vehicle_taxes_Total as taxesTotal,
							vehicle_finesTemp.vehicle_fines_Total as finesTotal,
							vehicle_insurancesTemp.vehicle_insurances_Total as insuranceTotal,
							vehicle_mechanical_servicesTemp.vehicle_mechanical_services_Total as mechanicalTotal
						')
						->leftJoin('vehicle_owners', 'vehicle_owners.id', 'vehicles.vehicles_owners_id')
						->leftJoin(DB::raw('(SELECT vehicles_id, SUM(total) as vehicle_fuels_Total from vehicle_fuels group by vehicles_id) as vehicle_fuelsTemp'),'vehicle_fuelsTemp.vehicles_id','vehicles.id')
						->leftJoin(DB::raw('(SELECT vehicles_id, SUM(total)+SUM(monto_gestoria) as vehicle_taxes_Total from vehicle_taxes group by vehicles_id) as vehicle_taxesTemp'),'vehicle_taxesTemp.vehicles_id','vehicles.id')
						->leftJoin(DB::raw('(SELECT vehicles_id, SUM(total) as vehicle_fines_Total from vehicle_fines group by vehicles_id) as vehicle_finesTemp'),'vehicle_finesTemp.vehicles_id','vehicles.id')
						->leftJoin(DB::raw('(SELECT vehicles_id, SUM(total) as vehicle_insurances_Total from vehicle_insurances group by vehicles_id) as vehicle_insurancesTemp'),'vehicle_insurancesTemp.vehicles_id','vehicles.id')
						->leftJoin(DB::raw('(SELECT vehicles_id, SUM(total) as vehicle_mechanical_services_Total from vehicle_mechanical_services group by vehicles_id) as vehicle_mechanical_servicesTemp'),'vehicle_mechanical_servicesTemp.vehicles_id','vehicles.id')
						->where(function($query) use ($request)
						{
							if ($request->brand != "") 
							{
								$query->where('vehicles.brand','like','%'.$request->brand.'%');
							}

							if ($request->model != "") 
							{
								$query->where('vehicles.model','like','%'.$request->model.'%');
							}

							if ($request->vehicle_status != "") 
							{
								$query->where('vehicles.vehicle_status','like','%'.$request->vehicle_status.'%');
							}

							if ($request->serial_number != "") 
							{
								$query->where('vehicles.serial_number','like','%'.$request->serial_number.'%');
							}
						})
						->orderBy('id','DESC')
						->get();
			if(count($vehicles)==0 || $vehicles==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-Vehiculos.xlsx');
			$headers = ['Reporte de vehículos','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID', 'Marca', 'Submarca', 'Modelo', 'Número de serie', 'Placas','Estado', 'Tipo de Propietario', 'Nombre de Propietario', 'Total en Combustible', 'Total en Impuestos', 'Total en Multas', 'Total en Seguro', 'Total en Servicios Mecánicos'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);
			
			foreach($vehicles as $vehicle)
			{				
				$tmpArr = [];
				foreach($vehicle as $k => $r)
				{
					if(in_array($k,['fuelsTotal', 'taxesTotal', 'finesTotal', 'insuranceTotal', 'mechanicalTotal']))
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
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();		
		}
		else
		{
			return redirect('error');
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
			if($request->realPath != '')
			{
				// \Storage::disk('public')->delete('/docs/vehicles/'.$request->realPath);
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/vehicles/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_vehicles_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/vehicles/'.$name;
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

	public function validateSerialNumber(Request $request)
	{
		if ($request->ajax())
		{
			$check = Vehicle::where('serial_number',$request->serial_number)->get();
			if (count($check)>0)
			{
				if (!isset($request->oldSerialNumber) || (isset($request->oldSerialNumber) && $request->oldSerialNumber != $request->serial_number)) 
				{
					$response = array(
						'valid'		=> false,
						'class'		=> 'error',
						'message'	=> 'El número de serie ya se encuentra registrado.'
					);
				}
				else
				{
					$response = array(
						'valid'		=> true,
						'class'		=> 'valid',
						'message'	=> ''
					);
				}
			}
			else if($request->serial_number == '')
			{
				$response = array(
					'valid'		=> false,
					'class'		=> 'error',
					'message'	=> 'Este campo es requerido.'
				);
			}
			else
			{
				$response = array(
					'valid'		=> true,
					'class'		=> 'valid',
					'message'	=> ''
				);
			}
			return Response($response);
		}
	}

	public function getDataOwner(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[293,294])->count()>0) 
		{
			if ($request->ajax()) 
			{
				$response = "";
				if ($request->type_owner == "fisica") 
				{
					$response = DB::table('vehicle_owners')->selectRaw('id, CONCAT_WS(" ",name,last_name,scnd_last_name) as name')->where('type',1)->orderBy('last_name')->get();
				}
				else if($request->type_owner == "moral")
				{
					$response = DB::table('vehicle_owners')->selectRaw('id, name')->where('type',2)->orderBy('name')->get();
				}
				return Response($response);
			}
		}
	}

	public function validateRfc(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> true,
				'class'		=> 'valid',
				'message'	=> ''
			);

			if (isset($request->moral_rfc) && $request->moral_rfc != "") 
			{
				$rfc = $request->moral_rfc;
			}
			elseif(isset($request->physical_rfc) && $request->physical_rfc != "")
			{
				$rfc = $request->physical_rfc;
			}
			else
			{
				$rfc = "";
			}
			
			if ($rfc != "") 
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $rfc))
				{
					$check = VehicleOwner::where('rfc',$rfc)->get();
					
					if (count($check)>0)
					{
						if (!isset($request->oldRfc) || (isset($request->oldRfc) && $request->oldRfc != $rfc)) 
						{
							$response = array(
								'valid'		=> false,
								'class'		=> 'error',
								'message'	=> 'El RFC ya se encuentra registrado, por favor consulte la lista de propietarios existentes'
							);
						}
						else
						{
							$response = array(
								'valid'		=> true,
								'class'		=> 'valid',
								'message'	=> ''
							);
						}
					}
					else
					{
						$response = array(
							'valid'		=> true,
							'class'		=> 'valid',
							'message'	=> ''
						);
					}
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El RFC debe ser válido.'
					);
				}

			}
		
			return Response($response);
		}
	}

	public function validateCurp(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> true,
				'class'		=> 'valid',
				'message'	=> ''
			);
			
			if (isset($request->physical_curp) && $request->physical_curp != "") 
			{
				if(preg_match("/^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/i", $request->physical_curp))
				{
					$check = VehicleOwner::where('curp',$request->physical_curp)->get();
						
					if (count($check)>0)
					{
						if (!isset($request->oldCurp) || (isset($request->oldCurp) && $request->oldCurp != $request->physical_curp)) 
						{
							$response = array(
								'valid'		=> false,
								'class'		=> 'error',
								'message'	=> 'El CURP ya se encuentra registrado, por favor consulte la lista de propietarios existentes'
							);
						}
						else
						{
							$response = array(
								'valid'		=> true,
								'class'		=> 'valid',
								'message'	=> ''
							);
						}
					}
					else
					{
						$response = array(
							'valid'		=> true,
							'class'		=> 'valid',
							'message'	=> ''
						);
					}
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El CURP debe ser válido.'
					);
				}
			}
			return Response($response);
		}
	}
}
