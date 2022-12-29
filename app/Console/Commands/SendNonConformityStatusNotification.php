<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\NonConformitiesStatus;
use App\Mail\SendNonConformityNotification;
use App\User;

class SendNonConformityStatusNotification extends Command
{
	protected $signature = 'ncNotification:send';

	protected $description = 'Command for send an email of non conformity notification';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$datas	=	[];
		$nonConformities	=	NonConformitiesStatus::where(function($q)
		{
			$q->where('status',2);
		})
		->where(function($q)
		{
			$date	=	Carbon::now();
			$date = $date->format('Y-m-d');
			$q->where('close_date','<=',$date);
		})
		->get();

		foreach($nonConformities as $nc)
		{
			$separator			=	'/';
			$mailString			=	$nc->non_conformity_origin;
			$emailsSeparated	=	explode($separator,$mailString);

			if ($mailString != "") 
			{
				for ($i=0; $i < count($emailsSeparated); $i++) 
				{ 
					$datas[$i]['email']				= $emailsSeparated[$i];
					$datas[$i]['nc_report_number']	= $nc->nc_report_number;
					$datas[$i]['status']			= $nc->statusData();
					$datas[$i]['close_date']		= $nc->close_date;

					$user = User::where('email',$emailsSeparated[$i])->first();
					if ($user != "") 
					{
						$datas[$i]['name'] = $user->fullName();
					}
					else
					{
						$datas[$i]['name'] = "";
					}
					$datas[$i]['subject'] =   "Notificación de no conformidad";
				}
			}
		}

		if (count($datas)>0)
		{
			foreach($datas as $key=>$data)
			{
				if (filter_var($data['email'], FILTER_VALIDATE_EMAIL))
				{
					Mail::to($data['email'])->send(new SendNonConformityNotification($data['nc_report_number'],$data['status'],$data['close_date'],$data['name'],$data['subject']));
					sleep(1);
				}
			}
			$this->info('Correo(s) enviado(s) exitosamente');
		}
		else
		{
			$this->info('No hay correos por enviar');
		}

	}
}
