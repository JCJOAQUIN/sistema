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

class ConfigurationProjectStagesController extends Controller
{
	private $module_id = 338;
	
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
			$data = App\Module::find($this->module_id);
			return view('configuracion.fases_proyecto.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 339,
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
			$validate = App\ProjectStages::where('name', $request->name)->first();
			if(!empty($validate) && $validate->id != $request->id)
			{
				$alert = "swal('','La fase de proyecto acaba de ser registrada, por favor ingrese una diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$project_stage              = new App\ProjectStages();
				$project_stage->name        = $request->name;
				$project_stage->description = $request->description;
				$project_stage->save();
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/project-stages')->with('alert',$alert);
			}
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data     = App\Module::find($this->module_id);
			$requests = App\ProjectStages::where(function($query) use ($request)
				{
					if($request->name !=' ')
					{
						$query->where('name','LIKE','%'.$request->name.'%');
					}
					if($request->description !=' ')
					{
						$query->where('description','LIKE','%'.$request->description.'%');
					}
				})
				->orderBy('id', 'desc')
				->paginate(10);
			return response(
				view('configuracion.fases_proyecto.busqueda',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'	    => 352,
					"requests"      => $requests,
					"name"          => $request->name,
					"description"   => $request->description
				])
			) 
			->cookie(
				'urlSearch', storeUrlCookie(352), 2880
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
			$requests = App\ProjectStages::find($id);
			$data     = App\Module::find($this->module_id);
			return view('configuracion.fases_proyecto.alta',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id'	=> 352,
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
			$validate = App\ProjectStages::where('name', $request->name)->where('id', '!=', $id)->first();
			if(!empty($validate) && $validate->id != $request->id)
			{
				$alert = "swal('','La fase de proyecto acaba de ser registrada, por favor ingrese una diferente.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$project_stage                  = App\ProjectStages::find($id);
				$project_stage->name            = $request->name;
				$project_stage->description     = $request->description;
				$project_stage->save();
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function validation(Request $request)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count() > 0)
		{
			if ($request->ajax())
			{
				if ($request->name != "")
				{
					$exist = App\ProjectStages::where('name',$request->name)
						->where(function($q) use ($request)
						{
							if(isset($request->oldStage))
							{
								$q->where('id', '!=', $request->oldStage);
							}
						})
						->count();
					if($exist > 0)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'La fase ya se encuentra registrada.'
						);
					}
					else
					{
						$response = array('valid' => true);
					}
				}
				else
				{
					$response = array(
						'valid'     => false,
						'message'   => 'Este campo es obligatorio.'
					);
				}	
			}
		}
		return Response($response);
	}
}
