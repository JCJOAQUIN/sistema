<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Module;
use App\SearchNew;
use App\NewsNotification;
use App\NewsResult;
use Illuminate\Support\Facades\Http;
use App\Mail\SendNewsNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use DB;

class AdministrationNewsControlller extends Controller
{
	protected $module_id = 360;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = Module::find($this->module_id);
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
			return redirect('error');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$initRange	= $request->initRange	!= '' ? Carbon::createFromFormat('d-m-Y', $request->initRange)->format('Y-m-d')	: null;
			$endRange	= $request->endRange 	!= '' ? Carbon::createFromFormat('d-m-Y', $request->endRange)->format('Y-m-d')	: null;
			$search		= $request->search;

			if (isset($search) && $search != "" && isset($initRange) && $initRange != "" && isset($endRange) && $endRange != "") 
			{
				$searchNew	= str_replace(" ","+",$search);
				$url 		= "https://newscatcher.p.rapidapi.com/v1/search_enterprise?q=".$searchNew."&lang=es&sort_by=date&from=".$initRange."&to=".$endRange."&country=mx&topic=news&page=1&page_size=50&media=True";
				//$url		= "https://google-search3.p.rapidapi.com/api/v1/news/q=".$searchNew."+after%3A".$initRange."+before%3A".$endRange."&num=10&lr=lang_es&hl=es&cr=MX&as_qdr=d";
				$new		= new SearchNew();
				$object		= $new->getData($url);
			}
			else
			{
				$object = [];
			}

			$data  = Module::find($this->module_id);

			return view('administracion.news.search',
			[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 361,
				'object'	=> $object,
				'search'	=> $search,
				'initRange'	=> $request->initRange,
				'endRange'	=> $request->endRange,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function notifications(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			
			$notifications 	= NewsNotification::where('user_id',Auth::user()->id)
							->orderBy('status')
							->orderBy('description')
							->get();

			$data  			= Module::find($this->module_id);
			return view('administracion.news.notifications',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 362,
				'notifications'	=> $notifications,
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function notificationStore(Request $request)
	{
		if(Auth::user()->module->where('id',362)->count()>0)
		{
			$new_notification				= new NewsNotification();
			$new_notification->description	= $request->description;
			$new_notification->user_id		= Auth::user()->id;
			$new_notification->status		= 1;
			$new_notification->save();

			$alert = "swal('', 'Notificación creada exitosamente.', 'success');" ;
			return redirect()->route('news-api.notifications')->with('alert',$alert);
		}
	}

	public function notificationInactive(NewsNotification $notification)
	{
		if (Auth::user()->module->where('id',362)->count()>0) 
		{
			$notification->status = 2;
			$notification->save();	
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function notificationActive(NewsNotification $notification)
	{
		if (Auth::user()->module->where('id',362)->count()>0) 
		{
			$notification->status = 1;
			$notification->save();
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function sendMails()
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

		return 'Enviado';
	}
}
