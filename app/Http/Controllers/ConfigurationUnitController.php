<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Lang;

class ConfigurationUnitController extends Controller
{
	private $module_id = 277;
	
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=>$data['father'],
					'title'		=>$data['name'],
					'details'	=>$data['details'],
					'child_id'	=>$this->module_id
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',278)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('configuracion.unit.create',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 278
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
			$unit       = new App\Unit;
			$unit->name = $request->unit_name;
			$unit->save();
			foreach($request->rqType as $k => $rqType)
			{
				$catRQ              = new App\CategoryRqUnit;
				$catRQ->unit_id     = $unit->id;
				$catRQ->rq_id       = $rqType;
				$catRQ->category_id = $request->category[$k];
				$catRQ->save();
			}
			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/unit')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function validateUnit(Request $request)
	{
		if($request->ajax())
		{
			if($request->unit_name == '')
			{
				$response = array(
					'valid'		=> false,
					'message'	=> 'Este campo es obligatorio'
				);
			}
			else
			{
				$exist = App\Unit::where('name',$request->unit_name)->get();
				if(count($exist)>0)
				{
					if(isset($request->oldUnit) && strtoupper($request->oldUnit)===strtoupper($request->unit_name))
					{
						$response = array('valid' => true);
					}
					else
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'La unidad ya se encuentra registrada.'
						);
					}
				}
				else
				{
					$response = array('valid' => true);
				}
			}
			return Response($response);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',279)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			$unit  = $request->unit;
			$units = App\Unit  ::where('name','LIKE','%'.$unit.'%')->orderBy('id', 'desc')->paginate(10);
			return response(
				view('configuracion.unit.search',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 279,
					'units'     => $units,
					'unit'      => $unit
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(279), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}

	public function edit(App\Unit $unit)
	{
		if(Auth::user()->module->where('id',279)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('configuracion.unit.create',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 279,
					'unit'      => $unit
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function update(App\Unit $unit, Request $request)
	{
		if(Auth::user()->module->where('id',279)->count()>0)
		{
			$unit->name = $request->unit_name;
			$unit->save();
			$unit->category_rq()->delete();
			foreach($request->rqType as $k => $rqType)
			{
				$catRQ              = new App\CategoryRqUnit;
				$catRQ->unit_id     = $unit->id;
				$catRQ->rq_id       = $rqType;
				$catRQ->category_id = $request->category[$k];
				$catRQ->save();
			}
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}
}
