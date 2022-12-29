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

class ConfigurationWeatherConditionController extends Controller
{
    private $module_id = 346;
	
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
            return redirect('/'); 
        }
	}

    public function create()
    {
        if(Auth::user()->module->where('id',$this->module_id)->count()>0)
        {
			$data   = App\Module::find($this->module_id);
			return view('configuracion.condicion_climatologica.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
                    'option_id'	=> 347,
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
            $validate = App\CatWeatherConditions::where('name', $request->name)->first();
            
            if(!empty($validate) && $validate->id != $request->id)
            {
                $alert = "swal('','Por favor ingrese una condición climatológica diferente.','error');";
                return back()->with('alert',$alert);
            }
            else
            {
                $weather_condition                  = new App\CatWeatherConditions();
                $weather_condition->name           = $request->name;
                $weather_condition->save();

                $alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
                return redirect('configuration/weather-condition')->with('alert',$alert);
            }
        }
    }

    public function search(Request $request)
    {
        if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
            $data      = App\Module::find($this->module_id);
            $requests  = App\CatWeatherConditions::where('name','LIKE','%'.$request->name.'%')
            ->orderBy('id', 'desc')
            ->paginate(10);

            return response(
                view('configuracion.condicion_climatologica.busqueda',
                [
                    'id'            => $data['father'],
                    'title'         => $data['name'],
                    'details'       => $data['details'],
                    'child_id'      => $this->module_id,
                    'option_id'	    => 348,
                    "requests"      => $requests,
                    "name"          => $request->name,
                    "description"   => $request->description
                ])
            )
            ->cookie(
                'urlSearch', storeUrlCookie(348), 2880
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
            $requests  = App\CatWeatherConditions::find($id);
			$data   = App\Module::find($this->module_id);
			return view('configuracion.condicion_climatologica.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
                    'option_id'	=> 348,
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
            $validate = App\CatWeatherConditions::where('name', $request->name)->where('id', '!=', $id)->first();
            
            if(!empty($validate) && $validate->id != $id)
            {
                $alert = "swal('','Por favor ingrese una condición climatológica diferente.','error');";
                return back()->with('alert',$alert);
            }
            else
            {
                $weather_condition                  = App\CatWeatherConditions::find($id);
                $weather_condition->name            = $request->name;
                $weather_condition->save();

                $alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
                return back()->with('alert',$alert);
            }
        }
    }
}
