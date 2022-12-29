<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Auth;
use App;
use CatDiscipline;
use Lang;

class ConfigurationDisciplineController extends Controller
{
	private $module_id = 343;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
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

	public function create()
	{
		if(Auth::user()->module->where('id',344)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.discipline.alta',[
				'id'		=> $data['father'],
				'title'		=> $data['name'],
				'details'	=> $data['details'],
				'child_id'	=> $this->module_id,
				'option_id'	=> 344
			]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search()
	{
		if(Auth::user()->module->where('id',345)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.discipline.busqueda',
			[
			'id'		=> $data['father'],
			'title'		=> $data['name'],
			'details'	=> $data['details'],
			'child_id'	=> $this->module_id,
			'option_id'	=> 345
			]);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{            
			$discipline             = new App\CatDiscipline();
			$discipline->indicator  = $request->indicator;
			$discipline->name       = $request->descriptiondiscipline;
			$discipline->save();

			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/discipline')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function follow(Request $request)
	{
		if(Auth::user()->module->where('id',345)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$indicator      = $request->indicator;
			$description    = $request->descriptiondiscipline;

			$discipline     = App\CatDiscipline::where(function($query) use ($indicator)
			{
				if($indicator != "")
				{
					$query->where('indicator','like','%'.$indicator.'%');
				}    
			})
			->orderBy('id','desc')
			->paginate(10);

			return response (
				view('configuracion.discipline.busqueda',
				[
					'id'		    =>	$data['father'],
					'title'			=>	$data['name'],
					'details'		=>	$data['details'],
					'child_id'		=>	$this->module_id,
					'option_id'		=>	345,
					'discipline'    =>  $discipline,
					'indicator'     =>  $indicator,
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(345), 2880
			);
		}
	}

	public function edit(Request $request, $id)
	{
		if(Auth::user()->module->where('id',345))
		{
			$data       = App\Module::find($this->module_id);
			$discipline  = App\CatDiscipline::find($id);
			return view('configuracion.discipline.alta',
			[
				'id'		    =>	$data['father'],
				'title'		    =>	$data['name'],
				'details'	    =>	$data['details'],
				'child_id'	    =>	$this->module_id,
				'option_id'	    =>	345,
				'discipline'    =>	$discipline
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
			$exists = App\CatDiscipline::where('indicator',$request->indicator)->where('id',$id)->first();
			if(!empty($exists) && $exists->id != $request->id)
			{
				$alert = "swal('','El indicador ya se encuentra registrado, por favor seleccione otro.','error');";
				return back()->with('alert',$alert);
			}
			else
			{
				$discipline                 = App\CatDiscipline::find($id);
				$discipline->indicator      = $request->indicator;
				$discipline->name           = $request->descriptiondiscipline;
				$discipline->save();
	
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return redirect()->route('discipline.edit',$id)->with('alert',$alert);
			}
		}
		else
		{
			redirect('/');
		}
	}

	public function validateIndicator(Request $request)
	{
		if ($request->ajax()) 
		{
			$response = array(
				'valid'		=> false,
				'class'		=> 'error',
				'message'	=> 'Este campo es obligatorio.'	
			);
			if(isset($request->indicator))
			{
				if(isset($request->oldIndicator))
				{
					$exist = App\CatDiscipline::where('indicator',$request->indicator)->where('id','!=',$request->oldIndicator)->get();
				}
				else
				{
					$exist = App\CatDiscipline::where('indicator',$request->indicator)->get();
				}
				if(count($exist)>0)
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El indicador ya existe.'
					);
				}
				else
				{
					$response = array('valid' => true,'class'=>'valid','message' => '');
				}
			}
			return Response($response);
		}
	}
}
