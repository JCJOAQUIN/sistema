<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SearchNew;
use App\NewsNotification;
use App\NewsResult;
use App\Mail\SendNewsNotification;
use Illuminate\Support\Facades\Mail;

class SendNewsNotifications extends Command
{
	protected $signature = 'news:notifications';

	protected $description = 'Envío de notificaciones por correo electrónico sobre noticias.';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$notifications 	= NewsNotification::selectRaw('
				news_notifications.description as description,
				GROUP_CONCAT(users.email SEPARATOR ",") as emails,
				GROUP_CONCAT(CONCAT_WS(" ",users.name,users.last_name,users.scnd_last_name) SEPARATOR ",") as names
			')
			->leftJoin('users','users.id','news_notifications.user_id')
			->where('news_notifications.status',1)
			->groupBy('news_notifications.description')
			->get();

		//return $notifications;

		foreach ($notifications as $key => $notification) 
		{
			$initRange	= date('Y-m-d',strtotime("-1 days"));
			$endRange	= date('Y-m-d'); 
			$searchNew	= str_replace(" ","+",$notification->description);
			$url		= "https://newscatcher.p.rapidapi.com/v1/search_enterprise?q=".$searchNew."&lang=es&sort_by=date&from=".$initRange."&to=".$endRange."&country=mx&topic=news&page=1&page_size=50&media=True";
			$news		= new SearchNew();
			$resultNews	= $news->getData($url);
			$flag		= false;

			foreach ($resultNews as $keyNew => $new) 
			{
				if (NewsResult::where('url',$new['link'])->count() == 0) 
				{
					$result			= new NewsResult();
					$result->url	= $new['link'];
					$result->save();

					$flag = true;
				}
				else
				{
					unset($resultNews[$keyNew]);
				}
			}
			if ($flag) 
			{
				try 
				{	
					$emails	= explode(',', $notification->emails);
					$names	= explode(',', $notification->names);
					for ($i=0; $i < count($emails); $i++) 
					{ 
						$subject		= "Alerta de Noticas";
						$name			= $names[$i];
						$description	= $notification->description;
						$to				= $emails[$i];
						Mail::to($to)->send(new SendNewsNotification($resultNews,$subject,$name,$description));
					}
				} 
				catch (Exception $e) 
				{
					
				}
			}
		}
	}
}
