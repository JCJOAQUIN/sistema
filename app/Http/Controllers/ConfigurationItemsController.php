<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App;
use Auth;
use Lang;

class ConfigurationItemsController extends Controller
{
	private $module_id = 356;
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
		if (Auth::user()->module->where('id',357)->count()>0)
		{
			$data	=	App\Module::find($this->module_id);
			return view('configuracion.partidas.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	357
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
			$id_contract	=	$request->contractId;
			if ($request->tpda != "")
			{
				for ($i=0; $i < count($request->tpda); $i++)
				{
					$pda_contract					=	new App\CatContractItem();
					$pda_contract->contract_item	=	$request->tpda[$i];
					$pda_contract->activity			=	$request->tactivity[$i];
					$pda_contract->unit				=	$request->tunit[$i];
					$pda_contract->pu				=	$request->tpu[$i];
					$pda_contract->amount			=	$request->tamount[$i];
					$pda_contract->contract_id		=	$id_contract;
					$pda_contract->save();
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_created")."', 'success');";
			return redirect('configuration/items')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if (Auth::user()->module->where('id',358)->count()>0)
		{
			$data			=	App\Module::find($this->module_id);
			$item_name		=	$request->item_name;
			$item_activity	=	$request->item_activity;
			$item_contract	=	$request->item_contract;
			$items	=	App\CatContractItem::where(function($query) use($item_name,$item_activity,$item_contract)
			{
				if ($item_name != "") 
				{
					$query->where('contract_item','like','%'.$item_name.'%');
				}
				if ($item_activity != "") 
				{
					$query->where('activity','like','%'.$item_activity.'%');
				}
				if ($item_contract != "") 
				{
					$query->whereHas('contractData', function($query) use($item_contract)
					{
						$query->where('contracts.name','like','%'.$item_contract.'%');
					});
				}
			})
			->orderBy('id','desc')
			->paginate(10);

			return response(
				view('configuracion.partidas.busqueda',
				[
					'id'			=>	$data['father'],
					'title'			=>	$data['name'],
					'details'		=>	$data['details'],
					'child_id'		=>	$this->module_id,
					'option_id'		=>	358,
					'items'			=>	$items,
					'item_name'		=>	$item_name,
					'item_activity'	=>	$item_activity,
					'item_contract'	=>	$item_contract
				])
			)
			->cookie(
				'urlSearch', storeUrlCookie(358), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if (Auth::user()->module->where('id',358)->count()>0)
		{
			$data	=	App\Module::find($this->module_id);
			$items	=	App\CatContractItem::find($id);
			return view('configuracion.partidas.alta',
			[
				'id'		=>	$data['father'],
				'title'		=>	$data['name'],
				'details'	=>	$data['details'],
				'child_id'	=>	$this->module_id,
				'option_id'	=>	358,
				'items'	=>	$items
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
			$validate = App\CatContractItem::where('contract_item',$request->tpda)->where('id','!=',$id)->count();
			if ($validate > 0)
			{
				$alert	= "swal('', 'Error, el nombre ingresado ya existe, por favor verifique los datos.', 'error');";
				return back()->with('alert',$alert)->withInput();
			}
			$pda_contract_update					=	App\CatContractItem::find($request->tcpda);
			$pda_contract_update->contract_item		=	$request->tpda;
			$pda_contract_update->activity			=	$request->tactivity;
			$pda_contract_update->unit				=	$request->tunit;
			$pda_contract_update->pu				=	$request->tpu;
			$pda_contract_update->amount			=	$request->tamount;
			$pda_contract_update->save();
			
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return redirect()->route('items.edit',$request->tcpda)->with('alert',$alert);
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
			'message'	=> 'Error, por favor seleccione un contrato'
		);
		if (isset($request->contract_id) && $request->contract_id != "" && isset($request->tpda) && $request->tpda != "") 
		{
			$validate = App\CatContractItem::where('contract_item',$request->tpda)->where('contract_id',$request->contract_id)->where('contract_item','!=',"")->get();
			if (count($validate)>0)
			{
				if(isset($request->oldItem) && $request->oldItem===$request->tpda)
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
		elseif (isset($request->contract_id) && $request->contract_id != "" && !isset($request->tpda) && !$request->tpda != "") 
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'Este campo es obligatorio'
			);
		}
		return Response($response);
	}
}
