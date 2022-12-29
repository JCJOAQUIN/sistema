<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Lang;
use Auth;

class ConfigurationContractController extends Controller
{
	private $module_id = 321;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
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
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',323)->count()>0)
		{
			$data	=	App\Module::find($this->module_id);
			return view('configuracion.contratos.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	323
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
			if (App\Contract::where('number',$request->contract_number)->where('project_id',$request->contract_project)->count() > 0)
			{
				$alert	= "swal('', 'Error, el número de contrato ingresado ya existe, por favor de verifique los datos.', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			else
			{
				$contracts				=	new	App\Contract();
				$contracts->number		=	$request->contract_number;
				$contracts->name		=	$request->contract_name;
				$contracts->project_id	=	$request->contract_project;
				$contracts->save();
				$contracts->wbs()->attach($request->contract_wbs);
	
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/contract')->with('alert',$alert);
			}
		}
		else {
			return redirect('/');
		}
	}

	public function follow(Request $request)
	{
		if (Auth::user()->module->where('id',325)->count()>0)
		{
			$data				=	App\Module::find($this->module_id);
			$contract_number	=	$request->contract_number;
			$contract_name		=	$request->contract_name;
			$contracts			=	App\Contract::where(function($query) use ($contract_number,$contract_name)
			{
				if ($contract_number != "") 
				{
					$query->where('number','like','%'.$contract_number.'%');
				}
				if ($contract_name != "") 
				{
					$query->where('name','like','%'.$contract_name.'%');
				}
			})
			->orderBy('id','desc')
			->paginate(10);

			return response(
				view('configuracion.contratos.seguimiento',
				[
					'id'				=>	$data['father'],
					'title'				=>	$data['name'],
					'details'			=>	$data['details'],
					'child_id'			=>	$this->module_id,
					'option_id'			=>	325,
					'contracts'			=>	$contracts,
					'contract_number'	=>	$contract_number,
					'contract_name'		=>	$contract_name
				])
			)->cookie(
				"urlSearch", storeUrlCookie(325), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit(Request $request, $id)
	{
		if (Auth::user()->module->where('id',325)->count()>0)
		{
			$data		=	App\Module::find($this->module_id);
			$contract	=	App\Contract::find($id);
			return view('configuracion.contratos.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	325,
				'contract'	=>	$contract
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
			$validate = App\Contract::where('number',$request->contract_number)->where('project_id',$request->contract_project)->where('id','!=',$id)->count();
			if ($validate > 0)
			{
				$alert	= "swal('', 'Error, el número de contrato ingresado ya existe, por favor verifique los datos.', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			else
			{
				$contracts_update				=	App\Contract::find($id);
				$contracts_update->number		=	$request->contract_number;
				$contracts_update->name			=	$request->contract_name;
				$contracts_update->project_id	=	$request->contract_project;
				$contracts_update->save();
				$contracts_update->wbs()->detach();
				$contracts_update->wbs()->attach($request->contract_wbs);
	
				$alert    =	"swal('', '".Lang::get("messages.record_updated")."', 'success');";
				return redirect()->route('contract.edit',$id)->with('alert',$alert);
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
			'message'	=> 'Este campo es obligatorio'
		);
		if (isset($request->project_id) && $request->project_id != "") 
		{
			if (isset($request->contract_number) && $request->contract_number != "") 
			{
				$validate = App\Contract::where('number',$request->contract_number)->where('project_id',$request->project_id)->where('id','!=',"")->get();
				if (count($validate)>0)
				{
					if(isset($request->oldContract) && $request->oldContract===$request->contract_number)
					{
						$response = array('valid' => true);
					}
					else
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'El número de contrato ya se encuentra registrado.'
						);
					}
				}
				else
				{
					$response = array('valid' => true);
				}
			}
		}
		else
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Error, por favor seleccione un proyecto'
			);
		}
		return Response($response);
	}
}
