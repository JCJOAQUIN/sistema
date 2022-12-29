<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Lang;
use Auth;
use Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationNewTicket;
use App\Mail\NotificationStatusTicket;
use App\Functions\Files;
use Illuminate\Support\Facades\Cookie;

class TicketsController extends Controller
{
	private $module_id = 105;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$fecha      = date('Y-m-d');
			$nuevafecha = date('Y-m-d',strtotime('-7 day',strtotime($fecha)));
			$data   = App\Module::find($this->module_id);

			return view('layouts.child_module',
				[
					'id'       => $data['id'],
					'title'    => $data['name'],
					'details'  => $data['details'],
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	
	}

	public function newTickets()
	{
		if(Auth::user()->module->where('id',107)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('tickets.new',
				[
					'id'        => $data['id'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'option_id' => 107,
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function newTicketsSave(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$ticket 					= new App\Ticket();
			$ticket->subject 			= $request->subject;
			$ticket->question 			= $request->question;
			$ticket->request_date   	= Carbon::now();
			$ticket->request_id			= Auth::user()->id;
			$ticket->idTypeTickets 		= $request->type;
			$ticket->idPriorityTickets 	= $request->priority;
			$ticket->idStatusTickets 	= 1;
			$ticket->idSectionTickets 	= $request->section;
			$ticket->save();

			$idTickets 					= $ticket->idTickets;
			
			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$documents 					= new App\DocumentsTickets();
					$new_file_name = Files::rename($request->realPath[$i],$idTickets);
					$documents->path 			= $new_file_name;
					$documents->idTickets 		= $idTickets;
					$documents->save();
				}
			}

			$emails	= App\User::join('user_review_ticket','users.id','user_review_ticket.user_id')
						->where('user_review_ticket.section_tickets_idsectionTickets',$request->section)
						->where('users.active',1)
						->where('users.notification',1)
						->where('users.sys_user',1)
						->get();

			$user 	=  App\User::find(Auth::user()->id);
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
						$url 			= route('tickets.all');
						$subject 		= "Nuevo Ticket";
						Mail::to($email->email)->send(new App\Mail\NotificationNewTicket($requestUser,$url,$subject));
					}
					$alert = "swal('','".Lang::get("messages.record_created_number",["param"=>$ticket->idTickets])."', 'success')";
				}
				catch(\Exception $e)
				{
					$alert = "swal('','".Lang::get("messages.record_created_no_mail")."', 'success')";
				}
			}
			try
			{
				$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				$url 			= route('tickets.follow');
				$status 		= "Pendiente";
				$subject 		= "Estado Ticket";
				$num 			= $ticket->idTickets;
				Mail::to($user->email)->send(new App\Mail\NotificationStatusTicket($requestUser,$url,$subject,$num,$status));
				$alert = "swal('','".Lang::get("messages.record_created_number",["param"=>$ticket->idTickets])."', 'success')";
			}
			catch(\Exception $e)
			{
				$alert = "swal('','".Lang::get("messages.record_created_no_mail")."', 'success')";
			}

			return redirect('tickets')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function allTickets(Request $request)
	{	
		if(Auth::user()->module->where('id',106)->count()>0)
		{
			$data   		= App\Module::find($this->module_id);
			$idticket 		= $request->id;
			$subject 		= $request->subject;
			$type 			= $request->type;
			$priority 		= $request->priority;
			$status 		= $request->status;
			$section 		= $request->section;
			$assign 		= $request->assign;
			$mindate      	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;

			$idSection = [];
			foreach(Auth::user()->inReview as $review)
			{ 
				$idSection[] = $review->idsectionTickets; 
			}

			$sections 	= App\SectionTickets::whereIn('idsectionTickets',$idSection)->get();

			$tickets 	= App\Ticket::selectRaw('tickets.*, IFNULL(answers.ans_date, tickets.request_date) as ticket_date')
				->leftJoin(DB::raw('(select idTickets, MAX(date) AS ans_date FROM ticket_answers GROUP BY idTickets) AS answers'),'tickets.idTickets','answers.idTickets')
				->whereNotNull('tickets.idTickets')
				->whereIn('tickets.idSectionTickets',$idSection)
				->where(function ($query) use ($idticket,$subject,$type,$priority,$status,$section,$mindate,$maxdate,$assign)
					{
						if ($assign != "") 
						{
							if ($assign == "Sin asignar") 
							{
								$query->where('tickets.assigned_id',null);
							}
							else
							{
								$query->where('tickets.assigned_id',$assign);
							}
						}
						if ($idticket != "") 
						{
							$query->where('tickets.idTickets',$idticket);
						}
						if ($subject != "") 
						{
							$query->where('tickets.subject','like','%'.$subject.'%');
						}
						if ($type != "") 
						{
							$query->where('tickets.idTypeTickets',$type);
						}
						if ($priority != "") 
						{
							$query->where('tickets.idPriorityTickets',$priority);
						}
						if ($status != "") 
						{
							$query->where('tickets.idStatusTickets',$status);
						}
						if ($section != "") 
						{
							$query->where('tickets.idSectionTickets',$section);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->where(function($query) use ($mindate, $maxdate)
							{
								$query->whereBetween('tickets.request_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								$query->orWhereBetween('answers.ans_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							});
						}
					})
				->orderBy('ticket_date','DESC')
				->paginate(10);
				// checar como agrupar por status y fecha
			return view('tickets.all',
				[
					'id'        => $data['id'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'option_id' => 106,
					'child_id'	=> $this->module_id,
					'tickets' 	=> $tickets,
					'idticket' 	=> $idticket,
					'subject' 	=> $subject,
					'type' 		=> $type,
					'priority'  => $priority,
					'status' 	=> $status,
					'section'	=> $section,
					'mindate'	=> $request->mindate,
					'maxdate' 	=> $request->maxdate,
					'assign' 	=> $assign,
					'sections'	=> $sections
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function assignedTicketUpdate(Request $request,$id)
	{
		if(Auth::user()->module->where('id',108)->count()>0)
		{
			$data   				= App\Module::find($this->module_id);
			$ticket 				= App\Ticket::find($id);
			
			if($ticket->assigned_id != "")
			{
				$alert  = "swal('', 'El ticket ha sido asignado anteriormente', 'error');";
			}
			else
			{
				$ticket->assigned_id	= Auth::user()->id;
				$ticket->save();
				$alert  = "swal('', 'Ticket Asignado Exitosamente', 'success');";
			}
			return redirect('tickets/assigned/'.$id)->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function assignedTicket(Request $request)
	{
		if(Auth::user()->module->where('id',108)->count()>0)
		{
			$data   	= App\Module::find($this->module_id);
			$idticket 	= $request->id;
			$subject 	= $request->subject;
			$type 		= $request->type;
			$priority 	= $request->priority;
			$status 	= $request->status;
			$section 	= $request->section;
			$mindate      	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;

			$idSection = [];
			foreach(Auth::user()->inReview as $review)
			{ 
				$idSection[] = $review->idsectionTickets; 
			}

			$sections 	= App\SectionTickets::whereIn('idsectionTickets',$idSection)->get();

			$tickets 	= App\Ticket::selectRaw('tickets.*, IFNULL(answers.ans_date, tickets.request_date) as ticket_date')
				->leftJoin(DB::raw('(select idTickets, MAX(date) AS ans_date FROM ticket_answers GROUP BY idTickets) AS answers'),'tickets.idTickets','answers.idTickets')
				->where('assigned_id',Auth::user()->id)
				->whereIn('tickets.idSectionTickets',$idSection)
				->where(function ($query) use ($idticket,$subject,$type,$priority,$status,$mindate,$maxdate,$section)
					{
						if ($idticket != "") 
						{
							$query->where('tickets.idTickets',$idticket);
						}
						if ($subject != "") 
						{
							$query->where('tickets.subject','like','%'.$subject.'%');
						}
						if ($type != "") 
						{
							$query->where('tickets.idTypeTickets',$type);
						}
						if ($priority != "") 
						{
							$query->where('tickets.idPriorityTickets',$priority);
						}
						if ($status != "") 
						{
							$query->where('tickets.idStatusTickets',$status);
						}
						if ($section != "") 
						{
							$query->where('tickets.idSectionTickets',$section);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->where(function($query) use ($mindate, $maxdate)
							{
								$query->whereBetween('tickets.request_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								$query->orWhereBetween('answers.ans_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							});
						}
					})
				->orderBy('ticket_date','DESC')
				->paginate(10);
			return response(
				view('tickets.assigned',
					[
						'id'        => $data['id'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'option_id' => 108,
						'child_id'	=> $this->module_id,
						'tickets' 	=> $tickets,
						'idticket' 	=> $idticket,
						'subject' 	=> $subject,
						'type' 		=> $type,
						'priority'  => $priority,
						'status' 	=> $status,
						'section'	=> $section,
						'mindate'	=> $request->mindate,
						'maxdate' 	=> $request->maxdate,
						'sections'	=> $sections,
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(108), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showAssignedTicket($id)
	{
		if(Auth::user()->module->where('id',108)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			$ticket = App\Ticket::where('assigned_id',Auth::user()->id)->find($id);
			if ($ticket != "") 
			{
				return view('tickets.show-assigned',
					[
						'id'        => $data['id'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'option_id' => 108,
						'child_id'	=> $this->module_id,
						'ticket' 	=> $ticket,
					]
				);
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

	public function solvedAssignedTicket(Request $request, $id)
	{
		$data 				= App\Module::find($this->module_id);
		$ticket 			= App\Ticket::find($id);
		$ticket->idStatusTickets = 3;
		$ticket->save();

		$answer  			= new App\TicketAnswer();
		$answer->answer 	= $request->answer;
		$answer->date 		= Carbon::now();
		$answer->idTickets 	= $id;
		$answer->users_id 	= Auth::user()->id;
		$answer->save();

		$idAnswerTickets 	= $answer->idAnswerTickets;

		if (isset($request->realPath) && count($request->realPath)>0) 
		{
			for ($i=0; $i < count($request->realPath); $i++) 
			{ 
				$documents 					= new App\DocumentsTickets();
				$new_file_name = Files::rename($request->realPath[$i],$id);
				$documents->path 			= $new_file_name;
				$documents->idAnswerTickets = $idAnswerTickets;
				$documents->save();
			}
		}

		try
		{
			$user 			=  App\User::find($ticket->request_id);
			$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
			$subject 		= "Estado Ticket";
			$num 			= $id;

			//Mail::to($user->email)->send(new App\Mail\NotificationStatusRequestTicket($requestUser,$subject,$num));
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
		}
		catch(\Exception $e)
		{
			$alert = "swal('','".Lang::get("messages.record_created_no_mail")."', 'success')";
		}
		return searchRedirect(108, $alert, 'tickets/assigned/'.$id);
	}

	public function followTicket(Request $request)
	{
		if(Auth::user()->module->where('id',109)->count()>0)
		{
			$data   	= App\Module::find($this->module_id);
			$idticket 	= $request->id;
			$subject 	= $request->subject;
			$type 		= $request->type;
			$priority 	= $request->priority;
			$status 	= $request->status;
			$section 	= $request->section;
			$mindate    = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate    = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;

			$sections 	= App\SectionTickets::all();

			if (Auth::user()->id == 43) 
			{
				$tickets 	= App\Ticket::selectRaw('tickets.*, IFNULL(answers.ans_date, tickets.request_date) as ticket_date')
				->leftJoin(DB::raw('(select idTickets, MAX(date) AS ans_date FROM ticket_answers GROUP BY idTickets) AS answers'),'tickets.idTickets','answers.idTickets')
				->where(function ($query) use ($idticket,$subject,$type,$priority,$status,$mindate,$maxdate,$section)
					{
						if ($idticket != "") 
						{
							$query->where('tickets.idTickets',$idticket);
						}
						if ($subject != "") 
						{
							$query->where('tickets.subject','like','%'.$subject.'%');
						}
						if ($type != "") 
						{
							$query->where('tickets.idTypeTickets',$type);
						}
						if ($priority != "") 
						{
							$query->where('tickets.idPriorityTickets',$priority);
						}
						if ($status != "") 
						{
							$query->where('tickets.idStatusTickets',$status);
						}
						if ($section != "") 
						{
							$query->where('tickets.idSectionTickets',$section);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->where(function($query) use ($mindate, $maxdate)
							{
								$query->whereBetween('tickets.request_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
								$query->orWhereBetween('answers.ans_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							});
						}
					})
				->orderBy('ticket_date','DESC')
				->paginate(10);
			}
			else
			{
				$tickets = App\Ticket::selectRaw('tickets.*, IFNULL(answers.ans_date, tickets.request_date) as ticket_date')
				->leftJoin(DB::raw('(select idTickets, MAX(date) AS ans_date FROM ticket_answers GROUP BY idTickets) AS answers'),'tickets.idTickets','answers.idTickets')
				->where(function ($query) use ($idticket,$subject,$type,$priority,$status,$mindate,$maxdate,$section)
				{
					if(Auth::user()->id != 43)
					{
						$query->where('tickets.request_id',Auth::user()->id);
					}
					if ($idticket != "") 
					{
						$query->where('tickets.idTickets',$idticket);
					}
					if ($subject != "") 
					{
						$query->where('tickets.subject','like','%'.$subject.'%');
					}
					if ($type != "") 
					{
						$query->where('tickets.idTypeTickets',$type);
					}
					if ($priority != "") 
					{
						$query->where('tickets.idPriorityTickets',$priority);
					}
					if ($status != "") 
					{
						$query->where('tickets.idStatusTickets',$status);
					}
					if ($section != "") 
					{
						$query->where('tickets.idSectionTickets',$section);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->where(function($query) use ($mindate, $maxdate)
						{
							$query->whereBetween('tickets.request_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
							$query->orWhereBetween('answers.ans_date',[$mindate->format('Y-m-d 00:00:00'),$maxdate->format('Y-m-d 23:59:59')]);
						});
						
					}
				})
				->orderBy('ticket_date','DESC')
				->paginate(10);
			}

			return view('tickets.follow',
					[
						'id'        => $data['id'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'option_id' => 109,
						'child_id'	=> $this->module_id,
						'tickets' 	=> $tickets,
						'idticket' 	=> $idticket,
						'subject' 	=> $subject,
						'type' 		=> $type,
						'priority'  => $priority,
						'status' 	=> $status,
						'section'	=> $section,
						'mindate'	=> $request->mindate,
						'maxdate' 	=> $request->maxdate,
						'sections'	=> $sections
					]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function showFollowTicket($id)
	{
		$data   = App\Module::find($this->module_id);
		if(Auth::user()->module->where('id',109)->count()>0)
		{	
			if (Auth::user()->id == 43)
			{
				$ticket = App\Ticket::find($id);
			}
			else
			{
				$ticket = App\Ticket::where('request_id',Auth::user()->id)->find($id);
			}

			if($ticket != "")
			{
				return view('tickets.show-follow',
				[
					'id'        =>$data['id'],
					'title'     =>$data['name'],
					'details'   =>$data['details'],
					'option_id' =>109,
					'child_id'	=> $this->module_id,
					'ticket' 	=>$ticket,
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

	public function updateFollowTicket(Request $request, $id)
	{
		$data 				= App\Module::find($this->module_id);
		if ($request->status == 1) 
		{
			$ticket 			= App\Ticket::find($id);
			$ticket->idStatusTickets = 2;
			$ticket->save();
		
			$answer  			= new App\TicketAnswer();
			$answer->answer 	= $request->answer;
			$answer->date 		= Carbon::now();
			$answer->idTickets 	= $id;
			$answer->users_id 	= Auth::user()->id;
			$answer->save();
			$idAnswerTickets 	= $answer->idAnswerTickets;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					$documents 					= new App\DocumentsTickets();
					$new_file_name = Files::rename($request->realPath[$i],$id);
					$documents->path 			= $new_file_name;
					$documents->idAnswerTickets = $idAnswerTickets;
					$documents->save();
				}
			}
			
			try
			{
				$user 			=  App\User::find($ticket->assigned_id);
				$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
				$subject 		= "Estado Ticket";
				$num 			= $id;

				Mail::to($user->email)->send(new App\Mail\NotificationStatusRequestTicket($requestUser,$subject,$num));
				$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			}
			catch(\Exception $e)
			{
				$alert = "swal('','".Lang::get("messages.record_created_no_mail")."', 'success')";
			}
		}
		elseif ($request->status == 2) 
		{
			$ticket 					= App\Ticket::find($id);
			$ticket->idStatusTickets 	= 4;
			$ticket->save();
			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
		} 
		else
		{
			$answer  			= new App\TicketAnswer();
			$answer->answer 	= $request->answer;
			$answer->date 		= Carbon::now();
			$answer->idTickets 	= $id;
			$answer->users_id 	= Auth::user()->id;
			$answer->save();
			$idAnswerTickets 	= $answer->idAnswerTickets;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$documents 					= new App\DocumentsTickets();
					$new_file_name = Files::rename($request->realPath[$i],$id);
					$documents->path 			= $new_file_name;
					$documents->idAnswerTickets = $idAnswerTickets;
					$documents->save();
				}
			}
			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
		}
		

		return redirect('tickets/follow/'.$id)->with('alert',$alert);
	}

	public function reopenTicket($id)
	{
		if(Auth::user()->module->where('id',109)->count()>0)
		{

			$reopen 					= App\Ticket::find($id);
			$reopen->idStatusTickets 	= 1;
			$reopen->save();
			$alert 		= "swal('','Ticket abierto','success');";
			return redirect('tickets/follow/'.$id)->with('alert',$alert);
		}
	}

	public function allTicketsView($id)
	{
		$data   = App\Module::find($this->module_id);
		if(Auth::user()->module->where('id',106)->count()>0)
		{
			$ticket = App\Ticket::where('assigned_id',null)->find($id);
			if($ticket != "")
			{
				return view('tickets.view',
					[
						'id'        =>$data['id'],
						'title'     =>$data['name'],
						'details'   =>$data['details'],
						'option_id' =>106,
						'child_id'	=>$this->module_id,
						'ticket' 	=>$ticket,
					]);
			}
			else
			{
				return redirect()->back()->with('alert', "swal('', 'El ticket ya ha sido asignado', 'error');");
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function uploader(Request $request)
	{
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
					\Storage::disk('public')->delete('/docs/tickets/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_ticketDocument.';
				$name					= $nameWithoutExtention.$extention;

				$destinity				= '/docs/tickets/'.$name;

				\Storage::disk('public')->put($destinity,\File::get($request->path));

				$response['error']		= 'DONE';
				$response['path']		= $name;
				$response['message']	= '';
				$response['extention']	= strtolower($extention);

			}
			return Response($response);
		}
	}

	public function ReAsignTicketUpdate(Request $request,$id)
	{
		if(Auth::user()->module->where('id',106)->count()>0)
		{
			$ticket 					= App\Ticket::find($id);
			$ticket->idSectionTickets 	= $request->section;
			$ticket->save();

			$emails		= App\User::join('user_review_ticket','users.id','user_review_ticket.user_id')
						->where('user_review_ticket.section_tickets_idsectionTickets',$request->section)
						->where('users.active',1)
						->where('users.notification',1)
						->where('users.sys_user',1)
						->get();

			$user	= App\User::find($ticket->request_id);
			$alert	= "swal('', 'El Ticket fue reasignado exitosamente', 'success');";
			
			if ($emails != "")
			{
				try
				{
					foreach ($emails as $email)
					{
						$requestUser	= $user->fullName();
						$url			= route('tickets.all');
						$subject		= "Nuevo Ticket";
						
						Mail::to($email->email)->send(new App\Mail\NotificationNewTicket($requestUser,$url,$subject));
					}
					$alert  = "swal('', 'El Ticket fue reasignado exitosamente', 'success');";
				}
				catch(\Exception $e)
				{
					$alert 	= "swal('', 'El ticket fue reasignado exitosamente, pero ocurrió un error al enviar el correo de notificación.', 'success');";
				}
			}

			return redirect('tickets')->with('alert',$alert);
		}
	}
}
