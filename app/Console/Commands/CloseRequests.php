<?php

namespace App\Console\Commands;

use App\RequestModel;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseRequests extends Command
{
	protected $signature = 'closeRequests:close';

	protected $description = 'Cerrar tickets';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$requests = RequestModel::where('kind',10)->where(function ($query) {				
				$query->whereIn('request_models.status',[5,21]);
			})
		->get();
		
		foreach ($requests as $request) {
			
			$outstandingBalance = $request->income->first()->amount;

			if($request->taxPayment == 1)
			{
				$outstandingBalance -= $request->bill->whereIn('status',[0,1,2])->sum('total');
			}
			else
			{
				$outstandingBalance -= $request->billNF->sum('total');
			}

			if($outstandingBalance <= 0)
			{
				$request->status = 20;
				$request->save();
			}
		}

	}
}
