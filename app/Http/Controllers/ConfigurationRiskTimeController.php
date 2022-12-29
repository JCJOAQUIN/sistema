<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Excel;
use Lang;

class ConfigurationRiskTimeController extends Controller
{
	private $module_id = 353;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'       => $data['father'],
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

	public function create()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('configuracion.tiempo_muerto.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id'	=> 354,
				]);
		}
		else
		{ 
			return redirect('/'); 
		}
		
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\CatTM::where('name', $request->name)->first();
			if(!empty($validate) && $validate->id != $request->id)
			{
				$alert = "swal('','Por favor ingrese una categoría del tiempo muerto diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$risk_time       = new App\CatTM();
				$risk_time->name = $request->name;
				$risk_time->save();
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/risk-time-category')->with('alert',$alert);
			}
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data     = App\Module::find($this->module_id);
			$requests = App\CatTM::where('name','LIKE','%'.$request->name.'%')
				->orderBy('id', 'desc')
				->paginate(10);
			return response(
				view('configuracion.tiempo_muerto.busqueda',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'	    => 355,
					"requests"      => $requests,
					"name"          => $request->name,
					"description"   => $request->description
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(355), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$requests = App\CatTM::find($id);
			$data     = App\Module::find($this->module_id);
			return view('configuracion.tiempo_muerto.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id'	=> 355,
					"request"  => $requests
				]);
		}
		else
		{ 
			return redirect('/'); 
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\CatTM::where('name', $request->name)->where('id', '!=', $id)->first();
			if(!empty($validate) && $validate->id != $id)
			{
				$alert = "swal('','Por favor ingrese una categoría del tiempo muerto diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$risk_time       = App\CatTM::find($id);
				$risk_time->name = $request->name;
				$risk_time->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function validation(Request $request)
	{
		if($request->ajax())
		{
			$response = array(
				'valid'   => false,
				'message' => 'El campo es requerido.'
			);
			if(isset($request->name))
			{
				$exists = App\CatTM::where('name', $request->name)
					->where(function($q) use($request)
					{
						if(isset($request->oldCategory))
						{
							$q->where('id','!=',$request->oldCategory);
						}
					})
					->count();
				if($exists > 0)
				{
					$response['message'] = 'La categoría ya se encuentra registrada.';
				}
				else
				{
					$response['valid'] = true;
					$response['message'] = '';
				}
			}
			return Response($response);
		}
	}
}
