<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use Lang;

class ConfigurationBlueprintsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	private $module_id = 327;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
			[
				'id' 		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		if (Auth::user()->module->where('id',328)->count()>0)
		{
			$data	=	App\Module::find($this->module_id);
			return view('configuracion.planos.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	328
			]);
		}
		else
		{
			return redirect('/');
		}
	}
	
	
	public function follow(Request $request)
	{
		if (Auth::user()->module->where('id',329)->count()>0)
		{
			$data			=	App\Module::find($this->module_id);
			$blueprint_name	=	$request->blueprint_name;
			$blueprints 	=	App\Blueprints::where(function($query) use ($blueprint_name)
			{
				if ($blueprint_name != "") 
				{
					$query->where('name','like','%'.$blueprint_name.'%');
				}
			})
			->orderBy('id','desc')->paginate(10);
			return response(
				view('configuracion.planos.seguimiento',
				[
					'id'				=>	$data['father'],
					'title'				=>	$data['name'],
					'details'			=>	$data['details'],
					'child_id'			=>	$this->module_id,
					'option_id'			=>	329,
					'blueprints'		=>	$blueprints,
					'blueprint_name'	=>	$blueprint_name,
				])
			)
			->cookie(
                'urlSearch', storeUrlCookie(329), 2880
            );			
		}
		else
		{
			return redirect('/');
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if (App\Blueprints::where('name',$request->blueprint_name)->count() > 0)
			{
				$alert	= "swal('', 'Error, el nombre ingresado ya existe, favor de verificar', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			else
			{
				$blueprints					=	new	App\Blueprints();
				$blueprints->name			=	$request->blueprint_name;
				$blueprints->contract_id	=	$request->contract_id;
				$blueprints->wbs_id			=	$request->contract_wbs;
				$blueprints->save();

				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/blueprints')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		if(Auth::user()->module->where('id',329)->count()>0)
		{
			$data		=	App\Module::find($this->module_id);
			$blueprints	=	App\Blueprints::find($id);
			return view('configuracion.planos.alta',
			[
				'id'			=>	$data['father'],
				'title'			=>	$data['name'],
				'details'		=>	$data['details'],
				'child_id'		=>	$this->module_id,
				'option_id'		=>	329,
				'blueprints'	=>	$blueprints
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$validate = App\Blueprints::where('name',$request->blueprint_name)->where('id','!=',$id)->count();
			if ($validate > 0) {
				$alert	= "swal('', 'Error, el nombre ingresado ya existe, favor de verificar', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			else
			{
				$blueprints					=	App\Blueprints::find($id);
				$blueprints->name			=	$request->blueprint_name;
				$blueprints->contract_id	=	$request->contract_id;
				$blueprints->wbs_id			=	$request->contract_wbs;
				$blueprints->save();
	
				$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return redirect()->route('blueprints.edit',$id)->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		$response = array(
			'valid'		=> false,
			'message'	=> 'Error.'
		);
		if($request->blueprint_name == '')
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio.'
			);
		}
		else
		{
			$validate = App\Blueprints::where('name',$request->blueprint_name)->where('name','!=',"")->get();
			if (count($validate)>0)
			{
				if(isset($request->oldBlueprints) && $request->oldBlueprints===$request->blueprint_name)
				{
					$response = array('valid' => true);
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'El nombre del plano ya se encuentra registrado.'
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

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
}
