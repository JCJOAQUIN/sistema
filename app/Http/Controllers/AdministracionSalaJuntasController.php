<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App;
use App\Boardroom;
use App\BoardroomElements;
use App\BoardroomReservations;
use App\Enterprise;
use App\Module;
use App\User;
use DateTime;
use Auth;
use Carbon\Carbon;
use Excel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministracionSalaJuntasController extends Controller
{
	private $module_id = 265;

	public function index()
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
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
			return redirect('/error');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',266)->count()>0)
		{
			$data        = Module::find($this->module_id);
			$option_id   = 266;
			$enterprises = Enterprise::where('status','ACTIVE')
				->whereIn('id',Auth::user()->inChargeEnt($option_id)
				->pluck('enterprise_id'))
				->orderBy('name','asc')
			->get();

			return view('administracion.sala_juntas.alta',
				[
					'id'          => $data['father'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'child_id'    => $this->module_id,
					'option_id'   => $option_id,
					'enterprises' => $enterprises,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{

		if(Auth::user()->module->where('id',266)->count() == 0)
		{
			return redirect('/');
		}

		$br = Boardroom::create([
			"name"			=> $request->name,
			"description"	=> $request->description,
			"property_id"	=> $request->property_id,
			"enterprise_id"	=> $request->enterprise_id,
			"max_capacity"	=> $request->max_capacity,
		]);

		foreach ($request->element as $key => $el)
		{
			BoardroomElements::create([
				"quantity"     => $request->quantity[$key],
				"element_id"   => $el,
				"boardroom_id" => $br->id,
				"description"  => $request->element_description[$key]
			]);
		}

		$alert 	= "swal('', 'Sala de Juntas Creada Exitosamente.', 'success');";
		return redirect('administration/general_services/boardroom')->with('alert',$alert);

	}


	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',267)->count() == 0)
		{
			return redirect('/');
		}

		$boardrooms = Boardroom::whereIn("enterprise_id", Auth::user()->inChargeEnt(267)->pluck('enterprise_id'))
			->where(function($q) use($request){
				if($request->id != null){
					$q->where('id',$request->id);
				}
				if($request->name != null){
					$q->where('name','like',"%".preg_replace("/\s+/", "%", $request->name)."%");
				}
				if($request->location != null){
					$q->where('property_id',$request->location);
				}
				if($request->enterprise_id != null){
					$q->where('enterprise_id',"$request->enterprise_id");
				}
			})
			->with('enterprise')
			->paginate(10);

		$data          = Module::find($this->module_id);
		$id_boardroom  = $request->id;
		$name          = $request->name;
		$location      = $request->location;
		$enterprise_id = $request->enterprise_id;
		$option_id     = 267;

		$enterprises   = Enterprise::whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))
			->orderby('name','asc')
		->get();

		return view('administracion.sala_juntas.busqueda',
			[
				'id'            => $data['father'],
				'title'         => $data['name'],
				'details'       => $data['details'],
				'child_id'      => $this->module_id,
				'option_id'     => 267,
				'boardrooms'    => $boardrooms,
				"id_boardroom"  => $id_boardroom,
				"name"          => $name,
				"location"      => $location,
				"enterprise_id" => $enterprise_id,
				"enterprises" => $enterprises,
			]);
	}


	public function update($id)
	{
		if(Auth::user()->module->where('id',267)->count() == 0)
		{
			return redirect('/');
		}

		$boardroom   = Boardroom::findOrFail($id);
		$data        = Module::find($this->module_id);
		$option_id   = 267;
		$enterprises = Enterprise::where('status','ACTIVE')
			->whereIn('id',Auth::user()->inChargeEnt($option_id)
			->pluck('enterprise_id'))
			->orderBy('name','asc')
		->get();


		return view('administracion.sala_juntas.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> $option_id,
				'boardroom'		=> $boardroom,
				'enterprises'	=> $enterprises
			]);

	}

	public function save(Request $request)
	{
		if(Auth::user()->module->where('id',266)->count() == 0)
		{
			return redirect('/');
		}


		$br = Boardroom::where("id",$request->br)->first();
		$br->update(
		[
			"name"			=> $request->name,
			"description"	=> $request->description,
			"property_id"	=> $request->property_id,
			"enterprise_id"	=> $request->enterprise_id,
			"max_capacity"	=> $request->max_capacity,
		]);

		if($request->has("element"))
		{
			foreach ($request->element as $key => $el)
			{
				BoardroomElements::create([
					"quantity"		=> $request->quantity[$key],
					"element_id"	=> $el,
					"boardroom_id"	=> $br->id,
					"description"	=> $request->element_description[$key]
				]);
			}
		}

		if($request->has("deleteElements"))
		{
			BoardroomElements::whereIn("id",$request->deleteElements)->delete();
		}

		$alert 	= "swal('', 'Sala de Juntas Actualizada Exitosamente.', 'success');";
		return redirect('administration/general_services/boardroom')->with('alert',$alert);

	}


	public function reservationSearch(Request $request)
	{

		if(Auth::user()->module->where('id',268)->count() == 0)
		{
			return redirect('/');
		}

		$boardrooms = Boardroom::whereIn("enterprise_id", Auth::user()->inChargeEnt(268)->pluck('enterprise_id'))
			->where(function($q) use($request)
			{
				if($request->id != null)
				{
					$q->where('id',$request->id);
				}
				if($request->name != null)
				{
					$q->where('name','like',"%".preg_replace("/\s+/", "%", $request->name)."%");
				}
				if($request->location != null)
				{
					$q->where('property_id',$request->location);
				}
				if($request->enterprise_id != null)
				{
					$q->where('enterprise_id',"$request->enterprise_id");
				}
			})
			->with('enterprise')
			->paginate(10);

		$data          = App\Module::find($this->module_id);
		$id_boardroom  = $request->id;
		$name          = $request->name;
		$location      = $request->location;
		$enterprise_id = $request->enterprise_id;
		$option_id     = 268;
		$enterprises   = Enterprise::whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))
						->orderby('name','asc')
						->get();

		return view('administracion.sala_juntas.busqueda_reservacion',
			[
				'id'            => $data['father'],
				'title'         => $data['name'],
				'details'       => $data['details'],
				'child_id'      => $this->module_id,
				'option_id'     => $option_id,
				'boardrooms'    => $boardrooms,
				"id_boardroom"  => $id_boardroom,
				"name"          => $name,
				"location"      => $location,
				"enterprise_id" => $enterprise_id,
				"enterprises"   => $enterprises,
			]);
	}

	public function reservationBoardroom(Request $request, $id)
	{

		if(Auth::user()->module->where('id',268)->count() == 0)
		{
			return redirect('/');
		}

		$boardroom = Boardroom::with('reservations')->findOrFail($id);

		$data          = App\Module::find($this->module_id);


		$usersModal = User::whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])
					->where('sys_user',1)
					->orderBy('name','asc')
					->orderBy('last_name','asc')
					->orderBy('scnd_last_name','asc')
					->get();

		return view('administracion.sala_juntas.reservacion',
			[
				'id'         => $data['father'],
				'title'      => $data['name'],
				'details'    => $data['details'],
				'child_id'   => $this->module_id,
				'option_id'  => 268,
				'boardroom'  => $boardroom,
				'usersModal' => $usersModal,
			]);
	}

	public function storeReservationBoardroom(Request $request)
	{
		if($request->cancelReservation == 1 || $request->reservation_id == "")
		{
			if(Auth::user()->module->where('id',268)->count() == 0)
			{
				return redirect('/error');
			}
			
			$start_date	= Carbon::createFromFormat("d-m-Y H:i", $request->select_dates_start);
			$end_date	= Carbon::createFromFormat("d-m-Y H:i", $request->select_dates_end);
			$today_date = Carbon::now()->format('Y-m-d H:i');
			
			if($request->reservation_id == "")
			{
				if($start_date->lte($today_date))
				{
					$alert = "swal('', 'La fecha de inicio debe ser posterior a la fecha y hora actual, por favor verifique sus datos.', 'error');";
					return redirect()->back()->with('alert',$alert);
				}
			}

			if($end_date->lte($start_date))
			{
				$alert = "swal('', 'La fecha de finalización debe ser posterior la fecha inicial, por favor verifique sus datos.', 'error');";
				return redirect()->back()->with('alert',$alert);
			}

			$start_date	= $start_date->format('Y-m-d H:i:00');
			$end_date	= $end_date->format('Y-m-d H:i:00');
			
			$reservations = BoardroomReservations::where(function($q) use($start_date, $end_date)
			{
				$q->where(function($q) use($start_date, $end_date)
				{
					$q->whereRaw('"'.$start_date.'" BETWEEN start AND end')
						->whereRaw('"'.$end_date.'" BETWEEN start AND end');
				})
				->orWhere(function($q) use($start_date, $end_date)
				{
					$q->whereRaw('start BETWEEN "'.$start_date.'" AND "'.$end_date.'"')
						->whereRaw('end BETWEEN "'.$start_date.'" AND "'.$end_date.'"');
				})
				->orWhere(function($q) use($start_date, $end_date)
				{
					$q->where("start","<",$end_date)
						->where("end",">",$end_date);
				})
				->orWhere(function($q) use($start_date, $end_date)
				{
					$q->where("end",">",$start_date)
						->where("start","<",$start_date);
				});

			})
			->where('boardroom_id',$request->room_id)
			->where('status',1)
			->where(function($q) use ($request)
			{
				if($request->reservation_id != null)
				{
					$q->where('id','!=',$request->reservation_id);
				}
			})
			->count();
			
			$flagBooked = false;

			if($reservations > 0)
			{
				$flagBooked = true;
			}
			
			if($flagBooked)
			{
				$alert = "swal('', 'No es posible realizar la reservación, por favor revise las fechas seleccionadas.', 'error');";
				return redirect()->back()->with('alert',$alert);
			}

			if($request->reservation_id != null)
			{
				BoardroomReservations::find($request->reservation_id)->update(
				[
					'start'        => $start_date,
					'end'          => $end_date,
					'reason'       => $request->reason,
					'observations' => $request->observations,
					'id_request'   => $request->user_id,
					'boardroom_id' => $request->room_id,
				]);
				$alert = "swal('', 'Reservación Actualizada Exitosamente.', 'success');";
				return redirect()->back()->with('alert',$alert);
			}
			else
			{
				BoardroomReservations::create(
				[
					'start'			=> $start_date,
					'end'			=> $end_date,
					'boardroom_id'	=> $request->room_id,
					'reason'		=> $request->reason,
					'observations'	=> $request->observations,
					'id_request'	=> $request->user_id,
					'id_elaborate'	=> Auth::user()->id,
				]);
				$alert = "swal('', 'Reservación Creada Exitosamente.', 'success');";
				return redirect()->back()->with('alert',$alert);
			}

		}
		else
		{
			if(Auth::user()->module->where('id',269)->count() == 0)
			{
				return redirect('/');
			}

			$reservation = BoardroomReservations::findOrFail($request->reservation_id);

			if($reservation->status == 0)
			{
				$alert = "swal('', 'La reservación ya ha sido cancelada, por favor verifique sus datos.', 'error');";
				return redirect()->back()->with('alert',$alert);
			}

			$reservation->update([
				'cancel_description' => $request->cancel_description,
				'status'             => 0,
			]);
			
			$alert = "swal('', 'Reservación cancelada exitosamente.', 'success');";
			return redirect()->back()->with('alert',$alert);
		}
	}

	public function administrationSearch(Request $request)
	{
		if(Auth::user()->module->where('id',269)->count() == 0)
		{
			return redirect('/');
		}
		$option_id 	   = 269;
		$id_boardroom  = $request->id;
		$name          = $request->name;
		$location      = $request->location;
		$enterprise_id = $request->enterprise_id;
		$status        = $request->status;
		$mindate       = $request->mindate;
		$maxdate       = $request->maxdate;
		$fmindate	   = new DateTime($mindate);
		$startDate     = $mindate ? Carbon::createFromFormat("d-m-Y",$fmindate->format('d-m-Y')) : null;
		$fmaxdate	   = new DateTime($maxdate);
		$endDate       = $maxdate ? Carbon::createFromFormat("d-m-Y",$fmaxdate->format('d-m-Y')) : null;

		$boardrooms = Boardroom::whereIn("enterprise_id", Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))
			->where(function($q) use($request)
			{
				if($request->status != null)
				{
					$q->whereHas('reservations',function($query) use($request)
					{
						$query->where('status', $request->status);
					});
				}
				if($request->id != null)
				{
					$q->where('id',$request->id);
				}
				if($request->name != null)
				{
					$q->where('name','like',"%".preg_replace("/\s+/", "%", $request->name)."%");
				}
				if($request->location != null)
				{
					$q->where('property_id',$request->location);
				}
				if($request->enterprise_id != null)
				{
					$q->where('enterprise_id',"$request->enterprise_id");
				}
			})
			->get();		

		$data 	= App\Module::find($this->module_id);

		$enterprises 	= Enterprise::whereIn('id',Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))
						->orderby('name','asc')
						->get();

		$modalBoardrooms = Boardroom::whereIn("enterprise_id",Auth::user()->inChargeEnt($option_id)->pluck('enterprise_id'))->get();
		$modalUsers      = User::whereIn('status',['NO-MAIL','ACTIVE','RE-ENTRY','RE-ENTRY-NO-MAIL'])
						->where('sys_user',1)
						->orderBy('name','asc')
						->orderBy('last_name','asc')
						->orderBy('scnd_last_name','asc')
						->get();

		return view('administracion.sala_juntas.busqueda_administracion',
			[
				"id"				=> $data["father"],
				"title"				=> $data["name"],
				"details"			=> $data["details"],
				"child_id"			=> $this->module_id,
				"option_id"			=> $option_id,
				"boardrooms"		=> $boardrooms,
				"id_boardroom"	    => $id_boardroom,
				"name"				=> $name,
				"location"			=> $location,
				"enterprise_id"		=> $enterprise_id,
				"startDate"			=> $startDate,
				"status"			=> $status,
				"endDate"			=> $endDate,
				"mindate"			=> $mindate,
				"maxdate"			=> $maxdate,
				"enterprises"		=> $enterprises,
				"modalBoardrooms"	=> $modalBoardrooms,
				"modalUsers"		=> $modalUsers,
			]);
	}

	public function exportReservations(Request $request)
	{
		if(Auth::user()->module->where('id',269)->count() == 0)
		{
			return redirect('error');
		}
		
		$minDate     = isset($request->mindate) ? Carbon::createFromFormat("d-m-Y", $request->mindate)->format('Y-m-d 00:00:00') : "";
		$maxDate     = isset($request->maxdate) ? Carbon::createFromFormat("d-m-Y", $request->maxdate)->format('Y-m-d 23:59:59') : "";

		$boardrooms = DB::table('boardrooms')->selectRaw(
						'
							boardrooms.name as boardroomName,
							CONCAT_WS(" ", users.name, users.last_name, users.scnd_last_name) as boardroomRequestName,
							boardrooms.max_capacity as boardroomMaxCapacity,
							IF(boardroom_reservations.status=1, "Activo", "Cancelado") as boardroomStatus,
							DATE_FORMAT(boardroom_reservations.start, "%d-%m-%Y %H:%i:%s") as boardroomStartDate,
							DATE_FORMAT(boardroom_reservations.end, "%d-%m-%Y %H:%i:%s") as boardroomEndDate,
							boardroom_reservations.reason as boardroomReason,
							boardroom_reservations.observations as boardroomObservation,
							boardroom_reservations.cancel_description as boardroomCancelDescription,
							properties.property as boardroomLocation,
							enterprises.name as enterpriseName
						')
						->leftJoin('enterprises', 'enterprises.id', 'boardrooms.enterprise_id')
						->leftJoin('properties', 'properties.id', 'boardrooms.property_id')
						->leftJoin('boardroom_reservations', 'boardroom_reservations.boardroom_id', 'boardrooms.id')
						->leftJoin('users', 'users.id', 'boardroom_reservations.id_request')
						->whereIn("boardrooms.enterprise_id", Auth::user()->inChargeEnt(269)->pluck('enterprise_id'))
						->where(function($q) use($request, $minDate, $maxDate)
						{
							if($request->status != null)
							{
								$q->where('boardroom_reservations.status', $request->status);
							}
							if($request->id != null)
							{
								$q->where('boardrooms.id', $request->id);
							}
							if($request->name != null)
							{
								$q->where('boardrooms.name','like',"%".preg_replace("/\s+/", "%", $request->name)."%");
							}
							if($request->location != null)
							{
								$q->where('boardrooms.property_id', $request->location);
							}
							if($request->enterprise_id != null)
							{
								$q->where('boardrooms.enterprise_id', $request->enterprise_id);
							}
						})
						->where(function($queryDate) use($minDate, $maxDate)
						{
							if($minDate != "" && $maxDate != "")
							{
								$queryDate->where(function($queryD) use($minDate, $maxDate)
								{
									$queryD->whereRaw('"'.$minDate.'" BETWEEN start AND end')
										->whereRaw('"'.$maxDate.'" BETWEEN start AND end');
								})
								->orWhere(function($queryD) use($minDate, $maxDate)
								{
									$queryD->whereRaw('start BETWEEN "'.$minDate.'" AND "'.$maxDate.'"')
										->whereRaw('end BETWEEN "'.$minDate.'" AND "'.$maxDate.'"');
								})
								->orWhere(function($queryD) use($minDate, $maxDate)
								{
									$queryD->where("start","<=",$maxDate)
										->where("end",">",$maxDate);
								})
								->orWhere(function($queryD) use($minDate, $maxDate)
								{
									$queryD->where("end",">=",$minDate)
										->where("start","<",$minDate);
								});
							}
						})
						->orderBy('boardrooms.name','asc')
						->get();
			if(count($boardrooms)==0 || $boardrooms==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-sala-de-juntas.xlsx');
			$headers = ['Reporte de sala de juntas','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    =
			[
				'Sala',
				'Solicitante',
				'Capacidad',
				'Estado',
				'Inicio',
				'Fin',
				'Motivo',
				'Observaciones/Comentarios',
				'Motivo de cancelación',
				'Ubicación',
				'Empresa'
			];

			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($boardrooms as $data)
			{
				$tmpArr = [];
				foreach($data as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
	}

	public function exportBoardroomFollow(Request $request)
	{
		$boardrooms = DB::table('boardrooms')->selectRaw(
					'
						boardrooms.name as boardroomName,
						properties.property as boardroomLocation,
						enterprises.name as boardroomEnterpise,
						boardrooms.max_capacity as boardroomCapacity,
						boardrooms.description as boardroomDescription
					')
					->leftJoin('properties', 'properties.id', 'boardrooms.property_id')
					->leftJoin('enterprises', 'enterprises.id', 'boardrooms.enterprise_id')
					->where(function($q) use($request){
						if($request->id != null){
							$q->where('boardrooms.id',$request->id);
						}
						if($request->name != null){
							$q->where('boardrooms.name','like',"%".preg_replace("/\s+/", "%", $request->name)."%");
						}
						if($request->location != null){
							$q->where('boardrooms.property_id',$request->location);
						}
						if($request->enterprise_id != null){
							$q->where('boardrooms.enterprise_id',"$request->enterprise_id");
						}
					})
					->get();
			if(count($boardrooms)==0 || $boardrooms==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-de-seguimiento-de-sala-de-juntas.xlsx');
			$headers = ['Reporte de salas de juntas','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    =
			[
				'Sala',
				'Ubicación',
				'Empresa',
				'Capacidad',
				'Descripción'
			];

			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($boardrooms as $data)
			{
				$tmpArr = [];
				foreach($data as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
	}
}
