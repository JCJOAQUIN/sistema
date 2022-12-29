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

class ReportAdministrationTicketController extends Controller
{
	private $module_id = 96;
	public function ticketsReport(Request $request)
	{
		if (Auth::user()->module->where('id',127)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$idticket	= $request->idticket;
			$subject	= $request->subject;
			$idType		= $request->idType;
			$idPriority	= $request->idPriority;
			$idStatus	= $request->idStatus;
			$idSection	= $request->idSection;
			$assign		= $request->assign;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

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

			$tickets = App\Ticket::whereNotNull('idTickets')
				->where(function ($query) use ($idticket,$subject,$idType,$idPriority,$idStatus,$idSection,$mindate,$maxdate,$assign)
				{
					if ($assign != "") 
					{
						if ($assign == "Sin asignar") 
						{
							$query->where('assigned_id',null);
						}
						else
						{
							$query->where('assigned_id',$assign);
						}
					}
					if ($idticket != "") 
					{
						$query->where('idtickets',$idticket);
					}
					if ($subject != "") 
					{
						$query->where('subject','like','%'.$subject.'%');
					}
					if ($idType != "") 
					{
						$query->where('idTypeTickets',$idType);
					}
					if ($idPriority != "") 
					{
						$query->where('idPriorityTickets',$idPriority);
					}
					if ($idSection != "") 
					{
						$query->whereIn('idSectionTickets',$idSection);
					}
					if ($idStatus != "") 
					{
						$query->where('idStatusTickets',$idStatus);
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('request_date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->orderBy('idtickets','DESC')
				->paginate(15);
			$data   = App\Module::find($this->module_id);
			return view('reporte.administracion.tickets',
			[
				'id'         => $data['father'],
				'title'      => $data['name'],
				'details'    => $data['details'],
				'child_id'   => $this->module_id,
				'option_id'  => 127,
				'tickets'    => $tickets,
				'idticket'   => $idticket,
				'subject'    => $subject,
				'idType'     => $idType,
				'idPriority' => $idPriority,
				'idStatus'   => $idStatus,
				'idSection'  => $idSection,
				'mindate'    => $mindate,
				'maxdate'    => $maxdate,
				'assign'     => $assign,
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function ticketsExcel(Request $request)
	{
		if (Auth::user()->module->where('id',127)->count()>0)
		{
			$idticket	= $request->idticket;
			$subject	= $request->subject;
			$idType		= $request->idType;
			$idPriority	= $request->idPriority;
			$idStatus	= $request->idStatus;
			$idSection	= $request->idSection;
			$mindate	= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate	= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;

			$tickets    = DB::table('tickets')
						->selectRaw('
							tickets.idTickets as idTickets,
							CONCAT_WS(" ",requestUser.name, requestUser.last_name, requestUser.scnd_last_name) as requestUser,
							tickets.subject as subject,
							tickets.question as question,
							DATE_FORMAT(tickets.request_date,"%d-%m-%Y %H:%i") as requestDate,
							section_tickets.section as section,
							ticket_types.type as type,
							ticket_priorities.priority as priority,
							ticket_statuses.status as status,
							CONCAT_WS(" ",assignedUser.name, assignedUser.last_name, assignedUser.scnd_last_name) as assignedUser,
							CONCAT_WS(" ",answerUser.name, answerUser.last_name, answerUser.scnd_last_name) as answerUser,
							ticket_answers.answer as answer,
							DATE_FORMAT(ticket_answers.date,"%d-%m-%Y %H:%i") as answerDate
						')
						->leftJoin('section_tickets','section_tickets.idsectionTickets','tickets.idSectionTickets')
						->leftJoin('ticket_types','ticket_types.idTypeTickets','tickets.idTypeTickets')
						->leftJoin('ticket_priorities','ticket_priorities.idPriorityTickets','tickets.idPriorityTickets')
						->leftJoin('ticket_statuses','ticket_statuses.idStatusTickets','tickets.idStatusTickets')
						->leftJoin('users as requestUser','requestUser.id','tickets.request_id')
						->leftJoin('users as assignedUser','assignedUser.id','tickets.assigned_id')
						->leftJoin('ticket_answers','ticket_answers.idTickets','tickets.idTickets')
						->leftJoin('users as answerUser','answerUser.id','ticket_answers.users_id')
						->whereNotNull('tickets.idTickets')
						->where(function ($query) use ($idticket,$subject,$idType,$idPriority,$idStatus,$idSection,$mindate,$maxdate)
						{
							if ($idticket != "") 
							{
								$query->where('tickets.idTickets',$idticket);
							}
							if ($subject != "") 
							{
								$query->where('tickets.subject','like','%'.$subject.'%');
							}
							if ($idType != "") 
							{
								$query->where('tickets.idTypeTickets',$idType);
							}
							if ($idPriority != "") 
							{
								$query->where('tickets.idPriorityTickets',$idPriority);
							}
							if ($idSection != "") 
							{
								$query->whereIn('tickets.idSectionTickets',$idSection);
							}
							if ($idStatus != "") 
							{
								$query->where('tickets.idStatusTickets',$idStatus);
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('tickets.request_date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
						->orderBy('tickets.idTickets','DESC')
						->get();


			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol3    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Tickets.xlsx');

			$headers        = ['Reporte de tickets','','','','','','','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Datos del ticket','','','','','','','','','','Respuestas','',''];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['# Ticket','Solicitante','Asunto','Pregunta/Duda','Fecha','Sección','Tipo','Prioridad','Estado','Asignado','Respuesta de','Respuesta','Fecha'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol3);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempIdTicket   = '';
			$kindRow        = true;
			foreach($tickets as $ticket)
			{
				if($tempIdTicket != $ticket->idTickets)
				{
					$tempIdTicket   = $ticket->idTickets;
					$kindRow        = !$kindRow;
				}
				else
				{
					$ticket->idTickets		= null;
					$ticket->requestUser	= '';
					$ticket->subject		= '';
					$ticket->question		= '';
					$ticket->requestDate	= '';
					$ticket->section		= '';
					$ticket->type			= '';
					$ticket->priority		= '';
					$ticket->status			= '';
					$ticket->assignedUser	= '';
				}

				$row = [];
				foreach($ticket as $key => $value)
				{
					$row[] = WriterEntityFactory::createCell($value);
				}

				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($row,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($row);
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
	
	public function ticketsDetail(Request $request)
	{
		if($request->ajax())
		{
			$idticket	= $request->idTicket;
			$ticket		= App\Ticket::find($idticket);
			return view('reporte.administracion.tickets_details',
			[
				'ticket'    => $ticket
			]);
		}
	}
}
