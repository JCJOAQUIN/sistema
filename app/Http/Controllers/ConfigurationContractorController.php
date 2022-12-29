<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use Lang;

class ConfigurationContractorController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	private $module_id = 322;
	public function index()
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

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		if (Auth::user()->module->where('id',324)->count()>0)
		{
			$data	=	App\Module::find($this->module_id);
			return view('configuracion.contratistas.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	324
			]);
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
			if (App\Contractor::where('name',$request->contractor_name)->withTrashed()->count() > 0)
			{
				$alert	= "swal('', 'Error, el nombre ingresado ya existe, por favor verifique los datos.', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			else
			{
				$contractor					=	new	App\Contractor();
				$contractor->name			=	$request->contractor_name;
				$contractor->status			=	$request->contractor_status;
				$contractor->contract_id	=	$request->contract_id;
				$contractor->wbs_id			=	$request->contract_wbs;
				$contractor->save();
				$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/contractor')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function follow(Request $request)
	{
		if (Auth::user()->module->where('id',326)->count()>0)
		{
			$data				=	App\Module::find($this->module_id);
			$contractor_name	=	$request->contractor_name;
			$contractor_status	=	$request->contractor_status;
			if ($contractor_status == "4")
			{
				$contractors	=	App\Contractor::onlyTrashed()->where(function($query) use ($contractor_name,$contractor_status)
				{
					if ($contractor_name != "")
					{
						$query->where('name','like','%'.$contractor_name.'%');
					}
				})
				->orderBy('id','desc')
				->paginate(10);
			}
			else if ($contractor_status != "")
			{
				$contractors	=	App\Contractor::where(function($query) use ($contractor_name,$contractor_status)
				{
					if ($contractor_name != "")
					{
						$query->where('name','like','%'.$contractor_name.'%');
					}
					if ($contractor_status != "")
					{
						$query->where('status', $contractor_status);
					}
				})
				->orderBy('id','desc')
				->paginate(10);
			}
			else
			{
				$contractors	=	App\Contractor::withTrashed()->where(function($query) use ($contractor_name,$contractor_status)
				{
					if ($contractor_name != "")
					{
						$query->where('name','like','%'.$contractor_name.'%');
					}
					if ($contractor_status != "")
					{
						$query->where('status', $contractor_status);
					}
				})
				->orderBy('id','desc')
				->paginate(10);
			}
			 
			return response(
				view('configuracion.contratistas.seguimiento',
				[
					'id'				=>	$data['father'],
					'title'				=>	$data['name'],
					'details'			=>	$data['details'],
					'child_id'			=>	$this->module_id,
					'option_id'			=>	326,
					'contractors'		=>	$contractors,
					'contractor_name'	=>	$contractor_name,
					'contractor_status'	=>	$contractor_status
				])
			)->cookie(
				"urlSearch", storeUrlCookie(326), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function reactive($id)
	{
		if (Auth::user()->module->where('id',326)->count()>0)
		{
			$data				=	App\Module::find($this->module_id);
			$contractor			=	App\Contractor::withTrashed()->find($id);
			if ($contractor != "") 
			{
				$contractor->restore();
				$alert = "swal('','Contratista rehabilitado exitosamente.','success');";
			} 
			else 
			{
				$alert = "swal('','Contratista previamente rehabilitado.','error');";
			}
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function inactive($id)
	{
		if (Auth::user()->module->where('id',326)->count()>0)
		{
			$data				=	App\Module::find($this->module_id);
			$contractor			=	App\Contractor::find($id);
			if ($contractor != "") 
			{
				$contractor->delete();
				$alert = "swal('','Contratista deshabilitado exitosamente.','success');";
			} 
			else 
			{
				$alert = "swal('','Contratista previamente deshabilitado.','error');";
			}
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id)
	{
		if(Auth::user()->module->where('id',325)->count()>0)
		{
			$data		=	App\Module::find($this->module_id);
			$contractor	=	App\Contractor::withTrashed()->find($id);
			return view('configuracion.contratistas.alta',
			[
				'id'			=>	$data['father'],
				'title'			=>	$data['name'],
				'details'		=>	$data['details'],
				'child_id'		=>	$this->module_id,
				'option_id'		=>	326,
				'contractor'	=>	$contractor
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
			$checkDelete = App\Contractor::find($id);
			if ($checkDelete == "")
			{
				
				$alert = "swal('','Contratista previamente deshabilitado.','error');";
				return redirect(route('contractor.follow'))->with('alert',$alert);
			}
			else
			{
				$validate = App\Contractor::where('name',$request->contractor_name)->withTrashed()->where('id','!=',$id)->count();
				if ($validate > 0)
				{
					$alert	= "swal('', 'Error, el nombre ingresado ya existe, por favor verifique los datos.', 'error');";
					return back()->with('alert',$alert)->withInput();
				}
				else
				{
					$old_name							=	$request->old_name;
					$contractors_update					=	App\Contractor::find($id);
					$contractors_update->name			=	$request->contractor_name;
					$contractors_update->status			=	$request->contractor_status;
					$contractors_update->contract_id	=	$request->contract_id;
					$contractors_update->wbs_id			=	$request->contract_wbs;
					$contractors_update->save();
	
					$alert	=	"swal('', '".Lang::get("messages.record_updated")."', 'success');";
					return redirect()->route('contractor.edit',$id)->with('alert',$alert);
				}
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if (Auth::user()->module->whereIn('id',[324, 325])->count() > 0)
		{
			if ($request->ajax())
			{
				if ($request->contractor_name != "")
				{
					$exist = App\Contractor::where('name',$request->contractor_name)
						->where('name','!=','')
						->where(function($q) use ($request)
						{
							if(isset($request->oldContractor))
							{
								$q->where('id', '!=', $request->oldContractor);
							}
						})
						->withTrashed()
						->count();
					if($exist > 0)
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'El contratista ya se encuentra registrado.'
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
