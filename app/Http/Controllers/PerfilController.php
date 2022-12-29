<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App;
use Hash;
use Auth;

class PerfilController extends Controller
{
	private $module_id = 5;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('layouts.profile',
				[
					'id'		=> $data['id'],
					'title'		=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function accountValidate(Request $request)
	{
		if ($request->ajax())
		{
			$banks       = $request->bank_description;
			$account     = $request->account_number;
			$clabe       = $request->clabe_interbanck;
			$accountBank = App\Employee::where('idBanks', $banks)
				->where('account', $account)
				->whereNotNull('account')
				->where('visible',1)
				->get();
			$clabeInterbank = App\Employee::where('clabe', $clabe)
				->whereNotNull('clabe')
				->where('visible',1)
				->get();
			if (count($accountBank)>0 && count($clabeInterbank) == 0) 
			{
				return Response('1');
			}
			else if (count($accountBank)==0 && count($clabeInterbank)>0)
			{
				return Response('2');
			}
			else if (count($accountBank)>0 && count($clabeInterbank)>0)
			{
				return Response('3');
			}
			else if (count($accountBank)==0 && count($clabeInterbank)==0)
			{
				return Response('4');
			}
		}
	}

	public function update(Request $request, $id)
	{
		$data				= App\Module::find($this->module_id);
		$user				= App\User::find(Auth::user()->id);
		$user->phone		= $request->phone;
		$user->extension	= $request->extension;
		$user->notification = $request->mails;
		$user->save();
		if(isset($request->delete))
		{
			if ($request->delete[0] != "")
			{
				for ($i=0; $i < count($request->delete); $i++)
				{
					if ($request->delete[$i] != "x") 
					{
						$del = App\Employee::find($request->delete[$i]);
						$del->visible = 0;
						$del->save();
					}
				}
			}
		}

		if ($request->bank != null) 
		{
			if ($request->bank[0] != null) 
			{
				$count = count($request->bank);
				for ($i=0; $i < $count; $i++) 
				{

					if ($request->card[$i] != "" || $request->clabe[$i] != "" || $request->account[$i]) 
					{
						if ($request->idEmployee[$i] != "x") 
						{
							$old = App\Employee::find($request->idEmployee[$i]);
							if (strcmp($old->idBanks, intval($request->bank[$i]))!=0 || strcmp($old->clabe, $request->clabe[$i])!=0 || strcmp($old->account, $request->account[$i])!=0 || strcmp($old->cardNumber, $request->card[$i])!=0) 
							{
								$old->visible = 0;
								$old->save();
								
								$employee				= new App\Employee();
								$employee->alias	= $request->alias[$i];
								$employee->cardNumber	= $request->card[$i];
								$employee->clabe 		= $request->clabe[$i];
								$employee->account 		= $request->account[$i];
								$employee->idBanks		= $request->bank[$i];
								$employee->idUsers		= Auth::user()->id;
								$employee->save();
							}
						}
						else
						{
							$employee				= new App\Employee();
							$employee->alias	= $request->alias[$i];
							$employee->cardNumber	= $request->card[$i];
							$employee->clabe 		= $request->clabe[$i];
							$employee->account 		= $request->account[$i];
							$employee->idBanks		= $request->bank[$i];
							$employee->idUsers		= Auth::user()->id;
							$employee->save();
						}						
					}
				}
			}
		}
		$alert = "swal('', 'Cambios Actualizados Exitosamente', 'success');";
		$banks 	= App\Banks::all();
		return redirect('/profile')->with(['alert'=>$alert,'banks'=>$banks]);
	}

	public function changepass()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('layouts.password',
				[
					'id'		=> $data['id'],
					'title'		=> $data['name'],
					'details' 	=> $data['details'],
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function updatepass(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$user            = App\User::find(Auth::user()->id);
			$data            = App\Module::find($this->module_id);
			$currentPassword = $request->password_current;
			$newPassword     = $request->password;
			$confirmPassword = $request->password_confirmation;
			if(Hash::check($currentPassword, $user->password) && $newPassword == $confirmPassword)
			{
				$user->password = bcrypt($newPassword);
				$user->save();
				$alert          = "swal('', 'Contraseña Actualizada Exitosamente', 'success');";
				return redirect('/profile')->with(['alert'=>$alert]);
			}
			elseif(!Hash::check($currentPassword, $user->password))
			{
				$alert = "swal('', 'La contraseña actual es incorrecta, favor de verificar', 'error');";
				return back()->with(['alert' => $alert]);
			}
			elseif($newPassword != $confirmPassword)
			{
				$alert = "swal('', 'Las contraseñas no coinciden, favor de verificar', 'error');";
				return back()->with(['alert' => $alert]);
			}
			else
			{
				$alert = "swal('', 'No se pudo actualizar la contraseña', 'error');";
				return back()->with(['alert' => $alert]);
			}
		}
		else
		{
			return abort(404);
		}
	}
}
