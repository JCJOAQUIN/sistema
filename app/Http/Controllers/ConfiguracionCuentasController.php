<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Auth;
use App;
use Lang;
use Alert;

class ConfiguracionCuentasController extends Controller
{
	private $module_id = 59;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',60)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('configuracion.cuentas.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 60
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function validation(Request $request)
	{
		if(isset($request->oldNumber))
		{
			$account = App\Account::find($request->oldNumber);
			if($account->account==$request->account && $account->idEnterprise==$request->enterprise_id)
			{
				$response = array('valid' => true);
				return $response;
			}
		}
		if($request->enterprise_id==0)
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Primero debe seleccionar una empresa.'
			);
		}
		else if($request->account == "")
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio.'
			);
		}
		else
		{
			$account = App\Account::where('account',$request->account)
				->where('idEnterprise',$request->enterprise_id)
				->get();
			if(count($account)>0)
			{
				$response = array(
					'valid'		=> false,
					'message'	=> 'El número de cuenta ya existe para esta empresa.'
				);
			}
			else
			{
				$response = array('valid' => true);
			}
		}
		return Response($response);
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',60)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$count = count($request->account);
			for ($i=0; $i < $count; $i++) 
			{
				$account               = new App\Account();
				$account->account      = $request->account[$i];
				$account->description  = $request->description[$i];
				$account->content      = $request->content[$i];
				$account->balance      = $request->balance[$i];
				$account->selectable   = $request->selectable[$i];
				$account->idEnterprise = $request->idEnterprise[$i];
				$account->save();
			}
			$alert	= "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',61)->count()>0)
		{
			$data    = App\Module::find($this->module_id);
			$account = App\Account::find($id);
			if ($account != "")
			{
				return view('configuracion.cuentas.cambio',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 61,
						'account'   => $account
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',61)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$account				= App\Account::find($id);
			$account->account		= $request->account;
			$account->description	= $request->description;
			$account->content 		= $request->content;
			$account->balance 		= $request->balance;
			$account->selectable 	= $request->selectable;
			$account->idEnterprise  = $request->enterprise_id;
			$account->save();

			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',61)->count()>0)
		{
			$data          = App\Module::find($this->module_id);
			$acc           = $request->acc;
			$accountNumber = $request->accountNumber;
			$enterpriseid  = $request->enterpriseid;
			$accounts = App\Account::where(function($query) use ($acc,$accountNumber,$enterpriseid)
			{
				if ($accountNumber != "") 
				{
					$query->where('account','LIKE','%'.$accountNumber.'%');
				}
				if ($acc != "") 
				{
					$query->where('description','LIKE','%'.$acc.'%');
				}
				if ($enterpriseid != "") 
				{
					$query->where('idEnterprise',$enterpriseid);
				}
			})
			->orderBy('idAccAcc', 'DESC')
			->paginate(10);
			return response(
				view('configuracion.cuentas.busqueda',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 61,
					'acc'           => $acc,
					'accountNumber' => $accountNumber,
					'enterpriseid'  => $enterpriseid,
					'accounts'      => $accounts
				])
			)->cookie(
				"urlSearch", storeUrlCookie(61), 2880
			);
		}
		else
		{
			return abort(404);
		}
	}
}
