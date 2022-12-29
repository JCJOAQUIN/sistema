<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Auth;
use App;
use Alert;
use Lang;
use Carbon\Carbon;

class ConfiguracionComunicadosController extends Controller
{
	private $module_id = 122;
	public function index()
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function releases()
	{
		$data = App\Module::find($this->module_id);
		return view('configuracion.comunicados.index',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id
			]);
	}

	public function create()
	{
		if (Auth::user()->module->where('id',123)->count()>0) 
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.comunicados.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 123
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',123)->count()>0) 
		{
			$release 			= new App\Releases();
			$release->title 	= $request->title;
			$release->content 	= $request->content;
			$release->date      = Carbon::now();
			$release->save();

			$alert			= "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/releases')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',124)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$titleRelease = $request->titleRelease;
			$content      = $request->content;
			$mindate      = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate) : null;
			$maxdate      = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate) : null;
			$releases     = App\Releases::where(function($query) use ($titleRelease,$content,$mindate,$maxdate)
				{
					if ($titleRelease != "") 
					{
						$query->where('title','like','%'.$titleRelease.'%');
					}
					if ($content != "") 
					{
						$query->where('content','like','%'.$content.'%');
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('date',[''.$mindate->format('Y-m-d 00:00:00'),''.$maxdate->format('Y-m-d 23:59:59').'']);
					}
				})
			->orderBy('idreleases','desc')
			->paginate(10);
			return response(
				view('configuracion.comunicados.edit',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 124,
					'releases' 	=> $releases,
					'titleRelease'=>$titleRelease,
					'content' 	=> $content,
					'mindate' 	=> $request->mindate,
					'maxdate' 	=> $request->maxdate
				])
			)->cookie(
				"urlSearch", storeUrlCookie(124), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function showRelease(Request $request,$id)
	{
		if(Auth::user()->module->where('id',124)->count()>0)
		{
			$data 	 = App\Module::find($this->module_id);
			$release = App\Releases::find($id);
			if ($release == "") 
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return back()->with('alert',$alert);
			}
			else
			{
				return view('configuracion.comunicados.show',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 124,
					'release'   => $release
				]);
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateRelease(Request $request,$id)
	{
		if(Auth::user()->module->where('id',124)->count()>0)
		{
			$data             = App\Module::find($this->module_id);
			$release          = App\Releases::find($id);
			if ($release != "")
			{
				$release->title   = $request->title;
				$release->content = $request->content;
				$release->date    = Carbon::now();
				$release->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return redirect(route('releases.search'))->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function history(Request $request)
	{
		
		$data         = App\Module::find($this->module_id);
		$titleRelease = $request->titleRelease;
		$content      = $request->content;
		$mindate      = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
		$maxdate      = $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
		$releases     = App\Releases::where(function($query) use ($titleRelease,$content,$mindate,$maxdate)
			{
				if ($titleRelease != "") 
				{
					$query->where('title','like','%'.$titleRelease.'%');
				}
				if ($content != "") 
				{
					$query->where('content','like','%'.$content.'%');
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
				}
			})
			->orderBy('date','desc')
			->paginate(10);
		return view('configuracion.comunicados.history',
			[
				'id'           => $data['father'],
				'title'        => $data['name'],
				'details'      => $data['details'],
				'child_id'     => $this->module_id,
				'option_id'    => 124,
				'releases'     => $releases,
				'titleRelease' => $titleRelease,
				'content'      => $content,
				'mindate'      => $mindate,
				'maxdate'      => $maxdate
			]);
	}

	public function deleteRelease($id)
	{
		$release = App\Releases::find($id);
		if ($release != "") 
		{
			$release->delete();
			$alert = "swal('', '".Lang::get("messages.record_deleted")."', 'success');";
		}
		else
		{
			$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'info');";
		}
		return redirect('configuration/releases/edit')->with('alert',$alert);
	}
}
