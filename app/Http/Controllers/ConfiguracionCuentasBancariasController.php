<?php

namespace App\Http\Controllers;

use App\Module;
use App\Account;
use App\BankAccount;
use Auth;
use Lang;
use Illuminate\Http\Request;

class ConfiguracionCuentasBancariasController extends Controller
{
    private $module_id = 209;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = Module::find($this->module_id);
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
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',210)->count()>0)
		{
			$data	= Module::find($this->module_id);
			return view('configuracion.cuentas_bancarias.alta',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 210,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',210)->count()>0)
		{
			$bankAccount						= new BankAccount;
			$bankAccount->alias					= $request->alias;
			$bankAccount->id_enterprise			= $request->enterprise_id;
			$bankAccount->id_accounting_account	= $request->account_id;
			$bankAccount->id_bank				= $request->bank_id;
			$bankAccount->currency				= $request->type_currency;
			$bankAccount->clabe					= $request->clabe;
			$bankAccount->account				= $request->account;
			$bankAccount->kind					= $request->kind;
			$bankAccount->description			= $request->description;
			$bankAccount->status				= $request->status;
			$bankAccount->save();
			$alert	= "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect()->route('bank.acount.index')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',211)->count()>0)
		{
			$data         	= Module::find($this->module_id);
			$alias        	= $request->alias;
			$clabe        	= $request->clabe;
			$account      	= $request->account;
			$enterpriseid   = $request->enterpriseid;
			$bankAccounts 	= BankAccount::where(function($query) use($alias, $clabe, $account, $enterpriseid)
			{
				if($alias != '')
				{
					$query->where('alias','LIKE','%'.$alias.'%');
				}
				if($clabe != '')
				{
					$query->where('clabe','LIKE','%'.$clabe.'%');
				}
				if($account != '')
				{
					$query->where('account','LIKE','%'.$account.'%');
				}
				if($enterpriseid != '')
				{
					$query->where('id_enterprise',$enterpriseid);
				}
			})
			->orderBy('id', 'desc')
			->paginate(10);
			
			return response (
				view('configuracion.cuentas_bancarias.busqueda',
				[
					'id'           	=> $data['father'],
					'title'        	=> $data['name'],
					'details'      	=> $data['details'],
					'child_id'     	=> $this->module_id,
					'option_id'    	=> 211,
					'bankAccounts' 	=> $bankAccounts,
					'alias'        	=> $alias,
					'clabe'        	=> $clabe,
					'account'      	=> $account,
					'enterpriseid'	=> $enterpriseid
					
				])
			)->cookie(
				"urlSearch", storeUrlCookie(211), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}
	public function validateClabe(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> true,
				'message'	=> ''
			);
			if(isset($request->clabe) && $request->clabe !== null)
			{
				$clabe	= BankAccount::where('clabe','LIKE', $request->clabe)->count();

				if(strlen(trim($request->clabe)) > 18 || strlen(trim($request->clabe)) < 18) 
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La CLABE debe ser de 18 dígitos.'
					);
				}
				elseif($clabe>0 && $request->oldClabe != $request->clabe)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La CLABE ya se encuentra registrada.'
					);
				}
				else
				{
					$response = array('valid' => true,'message' => '');
				}
			}
			return response($response);
		}
	}
	public function validateAccount(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'		=> true,
				'message'	=> ''
			);
			if(isset($request->account) && $request->account !== null)
			{
				$account	= BankAccount::where('account','LIKE', $request->account)->count();
				
				if(strlen(trim($request->account)) < 5 || strlen(trim($request->account)) > 15) 
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La cuenta debe ser entre 5 y 15 dígitos.'
					);
				}
				elseif($account>0 && $request->oldAccount != $request->account)
				{
					$response = array(
						'valid'		=> false,
						'message'	=> 'La cuenta ya se encuentra registrada.'
					);
				}
				else
				{
					$response = array('valid' => true,'message' => '');
				}
			}
			return response($response);
		}
	}
	public function edit(BankAccount $bank_account)
	{
		if(Auth::user()->module->where('id',211)->count()>0)
		{
			$data = Module::find($this->module_id);
			return view('configuracion.cuentas_bancarias.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 211,
					'bank_account'	=> $bank_account
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, BankAccount $bank_account)
	{
		if(Auth::user()->module->where('id',211)->count()>0)
		{
			$bank_account->alias					= $request->alias;
			$bank_account->id_enterprise			= $request->enterprise_id;
			$bank_account->id_accounting_account	= $request->account_id;
			$bank_account->id_bank					= $request->bank_id;
			$bank_account->currency					= $request->type_currency;
			$bank_account->clabe					= $request->clabe;
			$bank_account->account					= $request->account;
			$bank_account->kind						= $request->kind;
			$bank_account->description				= $request->description;
			$bank_account->status					= $request->status;
			$bank_account->save();
			$alert									= "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
}
