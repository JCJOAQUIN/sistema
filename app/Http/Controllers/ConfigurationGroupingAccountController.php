<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App;
use Alert;
use Lang;
use Auth;

class ConfigurationGroupingAccountController extends Controller
{
	private $module_id = 214;

    public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
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

	public function create(Request $request)
	{
		if(Auth::user()->module->where('id',215)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			return view('configuracion.agrupaciones_cuentas.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 215,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',215)->count()>0) 
		{
			if (count($request->idAccAcc)>0) 
			{
				$group               = new App\GroupingAccount();
				$group->name         = $request->name;
				$group->idEnterprise = $request->idEnterprise;
				$group->idUser       = Auth::user()->id;
				$group->save();
				for ($i=0; $i < count($request->idAccAcc); $i++) 
				{
					$group_has_account                    = new App\GroupingHasAccount();
					$group_has_account->idEnterprise      = $request->idEnterprise;
					$group_has_account->idAccAcc          = $request->idAccAcc[$i];
					$group_has_account->idGroupingAccount = $group->id;
					$group_has_account->save();
				}
				$alert = "swal('', '".Lang::get("messages.record_created")."','success');";
				return redirect()->route('account-concentrated.index')->with('alert',$alert);
			}
			else
			{
				$alert = "swal('','Seleccione al menos una cuenta.','success');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',216)->count()>0)
		{
			$data          = App\Module::find($this->module_id);
			$enterprise_id = $request->enterprise_id;
			$name          = $request->name;
			$groups        = App\GroupingAccount::where(function($query) use ($enterprise_id,$name)
				{
					if ($name != "") 
					{
						$query->where('name',$name);
					}
					if ($enterprise_id != "") 
					{
						$query->whereIn('idEnterprise',$enterprise_id);
					}
				})
				->orderBy('name','desc')
				->paginate(10);
			return response (
				view('configuracion.agrupaciones_cuentas.busqueda',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 216,
					'groups'        => $groups,
					'enterprise_id' => $enterprise_id,
					'name'          => $name
				])
			)->cookie(
				"urlSearch", storeUrlCookie(216), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit(Request $request,$id)
	{
		if(Auth::user()->module->where('id',216)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$group 	= App\GroupingAccount::find($id);
			if ($group !="")
			{
				return view('configuracion.agrupaciones_cuentas.alta',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 216,
						'group' 		=> $group,
					]);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return back()->with('alert',$alert);
			}
			
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request,$id)
	{
		if (Auth::user()->module->where('id',216)->count()>0) 
		{
			$validate = App\GroupingAccount::where('name',$request->name)->where('id','!=',$id)->count();
			if ($validate > 0)
			{
				$alert	= "swal('', 'Error, el nombre ingresado ya existe, por favor verifique los datos.', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			if (count($request->idAccAcc)>0) 
			{
				$group					= App\GroupingAccount::find($id);
				$group->name			= $request->name;
				$group->idEnterprise	= $request->idEnterprise;
				$group->idUser 			= Auth::user()->id;
				$group->save();	

				$idGroupingAccount = $group->id;

				App\GroupingHasAccount::where('idGroupingAccount',$id)->delete();

				for ($i=0; $i < count($request->idAccAcc); $i++) 
				{ 
					$group_has_account						= new App\GroupingHasAccount();
					$group_has_account->idEnterprise		= $request->idEnterprise;
					$group_has_account->idAccAcc			= $request->idAccAcc[$i];
					$group_has_account->idGroupingAccount	= $idGroupingAccount;
					$group_has_account->save();
				}
				$alert = "swal('', '".Lang::get("messages.record_updated")."','success');";
				return redirect('configuration/account-concentrated/edit/'.$id)->with('alert',$alert);
			}
			else
			{
				$alert = "swal('','Seleccione al menos una cuenta.','success');";
				return back()->with('alert',$alert);
			}
		}
	}

	public function delete($id)
	{
		if (Auth::user()->module->where('id',216)->count()>0) 
		{
			$group	=	App\GroupingAccount::find($id);
			if ($group !="")
			{
				App\GroupingHasAccount::where('idGroupingAccount',$id)->delete();
				$group ->delete();
				$alert = "swal('', '".Lang::get("messages.record_deleted")."','success');";
				return back()->with('alert',$alert);
			}
			else
			{
				$alert = "swal('', '".Lang::get("messages.record_previously_deleted")."', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function getAccounts(Request $request)
	{
		if($request->ajax())
		{
			$accountExist = App\GroupingHasAccount::select('idAccAcc')
				->where('idEnterprise',$request->idEnterprise)
				->get();
			$accounts = App\Account::where('idEnterprise',$request->idEnterprise)
				->where('account','like','5%')
				->whereNotIn('idAccAcc',$accountExist)
				->orderBy('account','ASC')
				->get();
			if (count($accounts) > 0) 
			{
				return view('configuracion.agrupaciones_cuentas.partial.tr_cuentas',['accounts'=>$accounts]);
			}
		}
	}

	public function validation(Request $request)
	{
		$response = array(
			'valid'		=> false,
			'message'	=> 'Error, por favor seleccione una empresa'
		);
		if (isset($request->enterprise_Id) && $request->enterprise_Id != "" && isset($request->name) && $request->name != "") 
		{
			$validate = App\GroupingAccount::where('name',$request->name)->where('idEnterprise',$request->enterprise_Id)->where('name','!=',"")->get();
			if (count($validate)>0)
			{
				if(isset($request->oldConcentred) && $request->oldConcentred===$request->name)
				{
					$response = array('valid' => true);
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'Error, el nombre ingresado ya existe, favor de verificar'
					);
				}
			}
			else
			{
				$response = array('valid' => true);
			}
		}
		elseif (isset($request->name) && $request->name == "")
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio'
			);
		}
		return Response($response);
	}
}
