<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Module;
use App\JobPosition;
use Lang;

//23518
class ConfigurationJobPositionsController extends Controller
{
	private $module_id = 273;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= Module::find($this->module_id);
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
			return redirect('error');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 	= Module::find($this->module_id);
			return view('configuracion.puestos_de_trabajo.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 274
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$job_position					= new JobPosition();
			$job_position->name				= $request->name;
			$job_position->description		= $request->description;
			$job_position->immediate_boss	= $request->immediate_boss;
			$job_position->user_id			= Auth::user()->id;
			$job_position->save();

			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/job-positions')->with('alert',$alert);
			
		}
		else
		{
			return redirect('error');
		}
	}

	public function edit(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= Module::find($this->module_id);
			$job_positions 	= JobPosition::where(function($query) use ($request)
			{
				if ($request->name != "") 
				{
					$query->where('name','like','%'.$request->name.'%');
				}
			})
			->orderBy('id', 'desc')
			->paginate(10);

			return response (
				view('configuracion.puestos_de_trabajo.edicion',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 275,
					'job_positions' => $job_positions,
					'name' 			=> $request->name
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(275), 2880
			);
		}
		else
		{
			return redirect('error');
		}
	}

	public function show(JobPosition $job_position)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= Module::find($this->module_id);

			return view('configuracion.puestos_de_trabajo.alta',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 275,
				'job_position'	=> $job_position
			]);
		}
		else
		{
			return redirect('error');
		}
	}

	public function update(JobPosition $job_position, Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$job_position->name				= $request->name;
			$job_position->description		= $request->description;
			$job_position->immediate_boss	= $request->immediate_boss;
			$job_position->user_id			= Auth::user()->id;
			$job_position->save();

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect()->route('job-positions.show',['job_position'=>$job_position->id])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function validation(Request $request)
	{
		if($request->name == '')
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio.'
			);
		}
		else
		{
			$exist = JobPosition::where('name',$request->name)
				->where(function($q) use($request)
				{
					if(isset($request->oldJob))
					{
						$q->where('id','!=',$request->oldJob);
					}
				})
				->get();
			if(count($exist) > 0)
			{
				$response = array(
					'valid'   => false,
					'message' => 'El puesto de trabajo ya existe.'
				);
			}
			else
			{
				$response = array('valid' => true);
			}
		}
		return Response($response);
	}
}
