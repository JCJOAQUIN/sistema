<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Lang;
use Excel;

class ConfigurationMachineryController extends Controller
{
	private $module_id = 349;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
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

	public function create()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('configuracion.maquinaria.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id'	=> 350,
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\CatMachinery::where('name', $request->name)->first();
			if(!empty($validate) && $validate->id != $request->id)
			{
				$alert = "swal('','Por favor ingrese una maquinaria diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$machinery       = new App\CatMachinery();
				$machinery->name = $request->name;
				$machinery->save();
				
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/machinery')->with('alert',$alert);
			}

		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data     = App\Module::find($this->module_id);
			$requests = App\CatMachinery::where('name','LIKE','%'.$request->name.'%')->orderBy('id', 'desc')->paginate(10);


			return response(
				view('configuracion.maquinaria.busqueda',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'	    => 351,
					"requests"      => $requests,
					"name"          => $request->name,
				])
			)->cookie(
                'urlSearch', storeUrlCookie(351), 2880
            );
		}
		else
		{
			return abort(404);
		}
	}
	
	public function edit($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$requests = App\CatMachinery::find($id);
			$data     = App\Module::find($this->module_id);
			return view('configuracion.maquinaria.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id'	=> 351,
					"request"  => $requests
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\CatMachinery::where('name', $request->name)->first();
			if(!empty($validate) && $validate->id != $id)
			{
				$alert = "swal('','Por favor ingrese una maquinaria diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$machinery       = App\CatMachinery::find($id);
				$machinery->name = $request->name;
				$machinery->save();

				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}
		}
	}
}
