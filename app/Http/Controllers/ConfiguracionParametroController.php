<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Module;
use App\Parameter;
use App\ParameterVacation;
use App\ParameterISR;
use App\ParameterSubsidy;

class ConfiguracionParametroController extends Controller
{
	private $module_id = 9;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = Module::find($this->module_id);
			return view('configuracion.parametros.index',
				[
					'id' 		=> $data['father'],
					'title' 	=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			foreach ($request->parameter as $key => $value)
			{
				$param					= Parameter::find($key);
				$param->parameter_value	= $value;
				$param->save();
			}
			foreach ($request->paramVac as $key => $value)
			{
				$param			= ParameterVacation::find($key);
				$param->days	= $value;
				$param->save();
			}
			foreach ($request->paramIsrInf as $key => $value)
			{
				$param				= ParameterISR::find($key);
				$param->inferior	= $value;
				$param->superior	= $request->paramIsrSup[$key];
				$param->quota		= $request->paramIsrQuo[$key];
				$param->excess		= $request->paramIsrExc[$key];
				$param->save();
			}
			foreach ($request->paramSubInf as $key => $value)
			{
				$param				= ParameterSubsidy::find($key);
				$param->inferior	= $value;
				$param->superior	= $request->paramIsrSup[$key];
				$param->subsidy		= $request->paramSubSub[$key];
				$param->save();
			}
			$alert     = "swal('', 'Parámetros actualizados', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
}
