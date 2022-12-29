<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Auth;
use App;
use Lang;

class ConfiguracionResponsabilidadesController extends Controller
{
	private $module_id = 77;
	
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',78)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.responsabilidades.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 78
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function validation(Request $request)
    {
		if($request->responsibility == '')
		{
			$response = array(
				'valid'   => false,
				'message' => 'Este campo es obligatorio'
			);
		}
		else
		{
			if(isset($request->oldRespo) && $request->oldRespo===$request->responsibility)
			{
				$response = array('valid' => true);
			}
			else
			{
				$resp = App\Responsibility::where('responsibility',$request->responsibility)->get();
				if(count($resp)>0)
				{
					$response = array(
						'valid'   => false,
						'message' => 'Ya existe esta responsabilidad.'
					);
				}
				else
				{
					$response = array('valid' => true);
				}
			}
		}
		
        return Response($response);
    }

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',78)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			$count = count($request->responsibility);
			for ($i=0; $i < $count; $i++) 
			{ 
				$resp					= new App\Responsibility();
				$resp->responsibility	= $request->responsibility[$i];
				$resp->description		= $request->description[$i];
				$resp->save();
			}
			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/responsibility')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',79)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$responsibility = App\Responsibility::find($id);
			if ($responsibility != "")
			{
				return view('configuracion.responsabilidades.cambio',
					[
						'id'             => $data['father'],
						'title'          => $data['name'],
						'details'        => $data['details'],
						'child_id'       => $this->module_id,
						'option_id'      => 79,
						'responsibility' => $responsibility
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',79)->count()>0)
		{
			$data                    = App\Module::find($this->module_id);
			$account                 = App\Responsibility::find($id);
			$account->responsibility = $request->responsibility;
			$account->description    = $request->description;
			$account->save();
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',79)->count()>0)
		{
			$data             = App\Module::find($this->module_id);
			$name             = $request->name;
			$description      = $request->description;
			$responsibilities = App\Responsibility::where(function($query) use ($name,$description)
				{
					if ($name != "") 
					{
						$query->where('responsibility','LIKE','%'.$name.'%');
					}
					if ($description != "") 
					{
						$query->orWhere('description','LIKE','%'.$description.'%');
					}
				})
				->orderby('id', 'desc')
				->paginate(10);

			return response(
				view('configuracion.responsabilidades.busqueda',
				[
					'id'               => $data['father'],
					'title'            => $data['name'],
					'details'          => $data['details'],
					'child_id'         => $this->module_id,
					'option_id'        => 79,
					'responsibilities' => $responsibilities,
					'name'             => $name,
					'description'      => $description
				])
			)->cookie(
                'urlSearch', storeUrlCookie(79), 2880
            );
		}
		else
		{
			return abort(404);
		}
	}
}
