<?php

namespace App\Console\Commands;

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketStatus extends Command
{
	protected $signature = 'ticket:status';

	protected $description = 'Cerrar tickets';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$fecha      = date('Y-m-d');
		$nuevafecha = date('Y-m-d',strtotime('-7 day',strtotime($fecha)));
		$update 	= Ticket::whereBetween('request_date',[''.$nuevafecha.' '.date('00:00:00').'',''.$nuevafecha.' '.date('23:59:59').''])
						->whereIn('idStatusTickets',[2,3])
						->update(
							[
								'idStatusTickets' => 4,
							]);
	}
}
