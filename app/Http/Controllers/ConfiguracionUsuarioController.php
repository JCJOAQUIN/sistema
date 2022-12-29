<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordChange;

class ConfiguracionUsuarioController extends Controller
{
	private $module_id = 6;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
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
			return abort(404);
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',12)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$roles			= App\Role::where('status','ACTIVE')->get();
			$enterprises	= App\Enterprise::orderName()->where('status','ACTIVE')->get();
			$areas			= App\Area::orderName()->where('status','ACTIVE')->get();
			$departments	= App\Department::orderName()->where('status','ACTIVE')->get();
			$banks			= App\Banks::orderName()->get();
			$kindbanks		= App\KindOfBanks::orderName()->get();
			$sections 		= App\SectionTickets::orderName()->get();
			return view('configuracion.usuario.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 12,
					'roles' 		=> $roles,
					'enterprises' 	=> $enterprises,
					'areas'			=> $areas,
					'departments'	=> $departments,
					'banks'			=> $banks,
					'kindbanks'		=> $kindbanks,
					'sections'		=> $sections
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',13)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$name 	= $request->name;
			$email 	= $request->email;
			$type 	= $request->type;
			$users  = App\User::where(function($query) use ($name,$email,$type)
				{
					if ($name != "") 
					{
						$query->where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if ($email != "") 
					{
						$query->where('email','LIKE','%'.$email.'%');
					}
					if ($type != "") 
					{
						$query->where('sys_user',$type);
					}
				})
				->paginate(10);
			return view('configuracion.usuario.busqueda',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 13,
					'users'     => $users,
					'name'      => $name,
					'email'     => $email,
					'type'      => $type
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function getData(Request $request)
	{
		if($request->ajax())
		{
			$output     = "";
			$header     = "";
			$footer     = "";
			$users      = App\User::where(DB::raw("CONCAT_WS(' ',name,last_name,scnd_last_name)"),'LIKE','%'.$request->search.'%')->get();
			$countUsers =  count($users);
			if ($countUsers >= 1)
			{
				$header = "<table id='table' class='table table-hover'><thead><tr><th>ID</th><th>Nombre</th><th>Correo Electr&oacute;nico</th><th>Estado</th><th>Acci&oacute;n</th></tr></thead><tbody>";
				$footer = "</tbody></table>";
				foreach ($users as $user)
				{
					$output .= "<tr>
									<td>".$user->id."</td>
									<td>".$user->name.' '.$user->last_name.' '.$user->scnd_last_name."</td>
									<td>".$user->email."</td>
									<td>".($user->status=="ACTIVE" || $user->status=="NO-BOLETIN" ? 'Activo': ($user->status=="RE-ENTRY" ||$user->status=="RE-ENTRY-NO-MAIL" ? 'Reingreso': ($user->status=="SUSPENDED"? 'Suspendido': 'Baja')))."</td>
									<td>
										<a href="."'".url::route('user.edit',$user->id)."'"."class='btn btn-green' alt='Editar' title='Editar'><span class='icon-pencil'></span></a>";
					if($user->status=="ACTIVE" || $user->status=="NO-BOLETIN" || $user->status=="RE-ENTRY" ||$user->status=="RE-ENTRY-NO-MAIL")
					{
						$output .= "
										<a href="."'".url::route('user.destroy',$user->id)."'"." class='btn-destroy-user btn btn-red' alt='Baja' title='Baja'><span class='icon-blocked'></span></a>
										<a href="."'".url::route('user.suspend',$user->id)."'"." class='btn-suspend-user btn btn-red' alt='Suspender' title='Suspender'><span class='icon-user-minus'></span></a>";
					}
					elseif($user->status=="SUSPENDED")
					{
						$output .= "
										<a href="."'".url::route('user.destroy',$user->id)."'"." class='btn-destroy-user btn btn-red' alt='Baja' title='Baja'><span class='icon-blocked'></span></a>
										<a href="."'".url::route('user.reentry',$user->id)."'"." class='btn-reentry-user btn btn-blue' alt='Reingresar' title='Reingresar'><span class='icon-user-check'></span></a>";
					}
					$output .= "
									</td>
								</tr>";
				}
				return Response($header.$output.$footer);
			}
			else
			{
				$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
				return Response($notfound); 
			}
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data                   = App\Module::find($this->module_id);
			$password               = substr(base64_encode(round(microtime(true) * 1000)),0,10);
			$user                   = new App\User();
			$user->name             = $request->name;
			$user->last_name        = $request->last_name;
			$user->scnd_last_name   = $request->scnd_last_name;
			$user->gender           = $request->gender;
			$user->phone            = $request->phone;
			$user->extension        = $request->extension;
			$user->email            = $request->email;
			$user->password         = bcrypt($password);
			$user->status           = "ACTIVE";
			$user->role_id          = $request->role;
			$user->area_id          = $request->area_id;
			$user->departament_id   = $request->department_id;
			$user->position         = $request->position;
			$user->cash             = isset($request->cash) ? 1:0;
			$user->cash_amount      = isset($request->cash) ? $request->cash_amount : NULL;
			$user->sys_user         = 1;
			$user->adglobal         = isset($request->adglobal) ? 1:0;
			$user->real_employee_id = $request->real_employee_id;
			$user->save();
			$user_id                = $user->id;
			$name                   = $request->name.' '.$request->last_name;
			$to                     = $request->email;
			$url                    = route('login');
			$subject                = "Contraseña de Ingreso";
			try
			{
				Mail::to($to)->send(new App\Mail\PasswordChange($password,$name,$url,$subject));
				$alert = "data = document.createElement('p'); data.innerHTML='Usuario Creado Exitosamente,<br>su contraseña fue enviada al correo ingresado'; swal({content: data, icon:'success'});";
			}
			catch(\Exception $e)
			{
				$alert = "data = document.createElement('p'); data.innerHTML='Usuario creado exitosamente,<br> pero ocurrió un error al enviar el correo electrónico con la contraseña de ingreso. La contraseña para ingresar es: ".$password."'; swal({content: data, icon:'success'});";
			}
			if ($request->section_id != null) 
			{
				$user->inReview()->attach($request->section_id);
			}
			if ($request->bank != null)
			{
				if ($request->bank[0] != null)
				{
					$count = count($request->bank);
					for ($i=0; $i < $count; $i++)
					{
						$employee             = new App\Employee();
						$employee->alias      = $request->alias[$i];
						$employee->cardNumber = $request->card[$i];
						$employee->clabe      = $request->clabe[$i];
						$employee->account    = $request->account[$i];
						$employee->idBanks    = $request->bank[$i];
						$employee->idUsers    = $user_id;
						$employee->save();
					}
				}
			}
			if ($request->enterprises != "")
			{
				$user->enterprise()->attach($request->enterprises,array('user_id'=>$user_id));
			}
			return redirect()->route('user.edit',$user->id)->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function show($id)
	{
		if(Auth::user()->module->where('id',13)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$roles					= App\Role::where('status','ACTIVE')->get();
			$enterprises			= App\Enterprise::orderName()->where('status','ACTIVE')->get();
			$user_has_enterprises	= DB::table('user_has_enterprise')->select('enterprise_id')->where('user_id',$id)->get();
			$areas					= App\Area::orderName()->where('status','ACTIVE')->get();
			$departments			= App\Department::orderName()->where('status','ACTIVE')->get();
			$user					= App\User::find($id);
			$banks 					= App\Banks::orderName()->get();
			$sections 				= App\SectionTickets::orderName()->get();
			if($user->status == 'DELETED')
			{
				return view('configuracion.usuario.mostrar',
					[
						'id'					=> $data['father'],
						'title'					=> $data['name'],
						'details'				=> $data['details'],
						'child_id'				=> $this->module_id,
						'option_id'				=> 13,
						'user' 					=> $user,
						'roles' 				=> $roles,
						'enterprises' 			=> $enterprises,
						'areas'					=> $areas,
						'departments'			=> $departments,
						'user_has_enterprises' 	=> $user_has_enterprises,
						'banks'					=> $banks,
						'sections' 				=> $sections
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

	public function edit($id)
	{
		if(Auth::user()->module->where('id',13)->count()>0)
		{
			$data                 = App\Module::find($this->module_id);
			$roles                = App\Role::where('status','ACTIVE')->get();
			$enterprises          = App\Enterprise::orderName()->where('status','ACTIVE')->get();
			$user_has_enterprises = DB::table('user_has_enterprise')->select('enterprise_id')->where('user_id',$id)->get();
			$areas                = App\Area::orderName()->where('status','ACTIVE')->get();
			$departments          = App\Department::orderName()->where('status','ACTIVE')->get();
			$user                 = App\User::find($id);
			$banks                = App\Banks::orderName()->get();
			$sections             = App\SectionTickets::orderName()->get();
			if($user != "")
			{
				return view('configuracion.usuario.cambio',
					[
						'id'                   => $data['father'],
						'title'                => $data['name'],
						'details'              => $data['details'],
						'child_id'             => $this->module_id,
						'option_id'            => 13,
						'user'                 => $user,
						'roles'                => $roles,
						'enterprises'          => $enterprises,
						'areas'                => $areas,
						'departments'          => $departments,
						'user_has_enterprises' => $user_has_enterprises,
						'banks'                => $banks,
						'sections'             => $sections
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

	public function validation(Request $request)
	{
		$response = array(
			'valid'		=> false,
			'message'	=> 'Error.'
		);

		$exist = App\User::where('email',$request->email)->where('email','!=','')->get();
		if(count($exist) > 0)
		{
			if(isset($request->oldUser) && $request->oldUser===$request->email)
			{
				$response = array('valid' => true);
			}
			else
			{
				$response = array(
					'valid'		=> false,
					'message'	=> 'El usuario ya se encuentra registrado.'
				);
			}
		}
		else
		{
			$response = array('valid' => true);
		}
		return Response($response);
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data					= App\Module::find($this->module_id);
			$user					= App\User::find($id);
			$user->name				= $request->name;
			$user->last_name		= $request->last_name;
			$user->scnd_last_name	= $request->scnd_last_name;
			$user->gender			= $request->gender;
			$user->phone			= $request->phone;
			$user->extension		= $request->extension;
			$user->email			= $request->email;
			$user->role_id			= $request->role;
			$user->area_id			= $request->area_id;
			$user->departament_id	= $request->department_id;
			$user->position			= $request->position;
			$user->cash				= isset($request->cash) ? 1:0;
			$user->cash_amount		= isset($request->cash) ? $request->cash_amount : NULL;
			$user->sys_user			= 1;
			$user->adglobal			= isset($request->adglobal) ? 1:0;
			$user->real_employee_id	= $request->real_employee_id;
			$user->save();

			$user->enterprise()->detach();
			$user->inReview()->detach();

			if ($request->enterprises != "")
			{
				$user->enterprise()->attach($request->enterprises,array('user_id'=>$id));
			}

			if ($request->section_id != null) 
			{
				$user->inReview()->attach($request->section_id);
			}
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
									$employee->alias 		= $request->alias[$i];
									$employee->cardNumber	= $request->card[$i];
									$employee->clabe 		= $request->clabe[$i];
									$employee->account 		= $request->account[$i];
									$employee->idBanks		= $request->bank[$i];
									$employee->idUsers		= $id;
									$employee->save();
								}
							}
							else
							{
								$employee				= new App\Employee();
								$employee->alias 		= $request->alias[$i];
								$employee->cardNumber	= $request->card[$i];
								$employee->clabe 		= $request->clabe[$i];
								$employee->account 		= $request->account[$i];
								$employee->idBanks		= $request->bank[$i];
								$employee->idUsers		= $id;
								$employee->save();
							}
						}
					}
				}
			}

			$alert = "swal('', 'Usuario Actualizado Exitosamente', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$user         = App\User::find($id);
			$user->status = 'DELETED';
			$user->active = 0;
			$user->save();
			$alert = "swal('','Usuario dado de baja correctamente','success');";
			return redirect('/configuration/user/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function delete($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$user         = App\User::find($id);
			$user->status = 'DELETED';
			$user->active = 0;
			$user->save();
			$alert = "swal('','Usuario dado de baja correctamente','success');";
			return redirect('/configuration/user/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function suspend($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$user         = App\User::find($id);
			$user->status = 'SUSPENDED';
			$user->active = 0;
			$user->save();
			$alert = "swal('','Usuario suspendido correctamente','success');";
			return redirect('/configuration/user/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public function reentry($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data         = App\Module::find($this->module_id);
			$user         = App\User::find($id);
			$user->status = 'RE-ENTRY';
			$user->active = 1;
			$user->save();
			$alert = "swal('','Usuario reingresado correctamente','success');";
			return redirect('/configuration/user/search')->with('alert',$alert);
		}
		else
		{
			return abort(404);
		}
	}

	public static function build_modules($father,$accessMod,$bool)
	{
		$modules = App\Module::where(function($q) use($father)
			{
				if($father == '')
				{
					$q->where('permissionRequire',0);
				}
			})
			->where('father', $father)
			->orderBy('itemOrder')
			->orderBy('name')
			->get();
		if(isset($modules) && count($modules)>0)
		{
			return view('partials.modules_list',['modules' => $modules, 'accessMod' => $accessMod, 'bool' => $bool]);
		}
		return '';
	}

	public function getMod(Request $request)
	{
		if($request->ajax())
		{
			$role     = App\User::find($request->user_id);
			$response = array();
			$modules  = $role->module;
			foreach ($modules as $key => $value)
			{
				$response[] = $value->id;
			}
			return Response($response);
		}
	}

	public function getEntDep(Request $request)
	{
		if ($request->ajax())
		{
			$modules = App\Role_has_module::where('module_id',$request->module_id)->get();
			$edits = '';
			if ($modules != null) 
			{
				$edits .= "<button class='follow-btn editModule' type='button'><span class='icon-pencil'></span></button>";
				foreach ($modules as $mod) 
				{
					foreach(App\Permission_role_enterprise::where('role_has_module_idrole_has_module',$mod->idrole_has_module)->get() as $permissionEnt)
					{
						$edits .= "<span>".
									"<input type='hidden' class='enterprises' name='enterprises_module_'".$request->module_id."'[]' value='".$permissionEnt->enterprise_id."'>".
									"</span>";
					}
					foreach(App\Permission_role_dep::where('role_has_module_idrole_has_module',$mod->idrole_has_module)->get() as $permissionDep)
					{
						$edits.= "<span>".
									"<input type='hidden' class='departments' name='departments_module_'".$request->module_id."'[]' value='".$permissionDep->departament_id."'>".
									"</span>";
					}
				}
			}
			return Response($edits);
		}
	}

	public function modulePermission(Request $request)
	{
		if ($request->ajax())
		{
			$response                       = array();
			$user                           = App\User::find($request->user);
			$response['enterprise']         = $user->inChargeEnt($request->module)->pluck('enterprise_id');
			$response['department']         = $user->inChargeDep($request->module)->pluck('departament_id');
			$response['project']            = $user->inChargeProject($request->module)->pluck('project_id');
			$response['requisition']        = $user->inChargeReq($request->module)->pluck('requisition_type_id');
			$response['upload_files']       = $user->canUploadFiles($request->module)->pluck('permission');
			$UHM                            = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
			$response['global_permission']  = $UHM->global_permission;
			$response['quality_permission'] = $UHM->quality_permission;
			return Response($response);
		}
	}

	public function modulePermissionUpdate(Request $request)
	{
		if ($request->ajax())
		{
			$user = App\User::find($request->user);
			$UHM  = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->get();
			if(count($UHM)>0)
			{
				foreach ($UHM as $data)
				{
					foreach($data->department as $dep)
					{
						$dep->delete();
					}
					foreach($data->enterprise as $ent)
					{
						$ent->delete();
					}
					foreach($data->requisition as $req)
					{
						$req->delete();
					}
					foreach($data->project as $pro)
					{
						$pro->delete();
					}
					foreach($data->uploadFile as $up)
					{
						$up->delete();
					}
					$data->delete();
				}
			}
			if($request->action == 'on')
			{
				$user->module()->attach($request->module);
				if ($request->global_permission == 1) 
				{
					$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
					$UHM->global_permission = $request->global_permission;
					$UHM->save();
				}
				if($request->additional != '')
				{
					$user->module()->detach($request->additional);
					$user->module()->attach($request->additional);
				}
				if(isset($request->enterprise) || isset($request->department))
				{
					$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
					if(isset($request->enterprise))
					{
						foreach ($request->enterprise as $ent)
						{
							$entM                                    = new App\PermissionEnt();
							$entM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
							$entM->enterprise_id                     = $ent;
							$entM->save();
						}
					}
					if(isset($request->department))
					{
						foreach ($request->department as $dep)
						{
							$depM                                    = new App\PermissionDep();
							$depM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
							$depM->departament_id                    = $dep;
							$depM->save();
						}
					}

					if ($request->permisssion_type == 7)
					{
						if (isset($request->project_request) && count($request->project_request)>0) 
						{
							foreach ($request->project_request as $project)
							{
								$projM                                    = new App\PermissionProject();
								$projM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
								$projM->project_id                        = $project;
								$projM->save();
							}
						}
					} 
				}
				if ($request->permisssion_type == 5) 
				{
					$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
					
					if(isset($request->upload_file))
					{
						foreach ($request->upload_file as $up)
						{
							$puf                      	= new App\PermissionUploadFile();
							$puf->user_has_module_id  	= $UHM->iduser_has_module;
							$puf->permission 			= $up;
							$puf->save();
						}
					}
					$user->save();
				}
				if($request->permisssion_type == 3 || $request->permisssion_type == 4)
				{
					$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
					if ($request->permisssion_type == 4)
					{
						if(isset($request->req_type))
						{
							foreach ($request->req_type as $req)
							{
								$reqM                      = new App\PermissionReq();
								$reqM->user_has_module_id  = $UHM->iduser_has_module;
								$reqM->requisition_type_id = $req;
								$reqM->save();
							}
						}
					}
					$user->save();
					if (isset($request->proj) && count($request->proj)>0) 
					{
						foreach ($request->proj as $project)
						{
							$projM                                    = new App\PermissionProject();
							$projM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
							$projM->project_id                        = $project;
							$projM->save();
						}
					}
				}
				if($request->permisssion_type == 6)
				{
					$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->module)->first();
					$UHM->quality_permission = $request->quality_file;
					$UHM->save();
					if (isset($request->proj) && count($request->proj)>0) 
					{
						foreach ($request->proj as $project)
						{
							$projM                                    = new App\PermissionProject();
							$projM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
							$projM->project_id                        = $project;
							$projM->save();
						}
					}
				}
			}
			else
			{
				$user->module()->detach($request->module);
				if($request->additional != '')
				{
					$user->module()->detach($request->additional);
				}
				$user->requisitionVote	= 0;
				$user->requisitionSend	= 0;
				$user->save();
			}
			return 'DONE';
		}
	}
	public function modulePermissionUpdateSimple(Request $request)
	{
		if ($request->ajax())
		{
			$user    = App\User::find($request->user);
			$modules = App\Module::where('permissionRequire',0)->pluck('id');
			foreach ($modules as $module)
			{
				$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$module)->get();
				if(count($UHM) > 0)
				{
					foreach($UHM as $data)
					{
						foreach($data->department as $dep)
						{
							$dep->delete();
						}
						foreach($data->enterprise as $ent)
						{
							$ent->delete();
						}
						foreach($data->requisition as $req)
						{
							$req->delete();
						}
						foreach($data->project as $pro)
						{
							$pro->delete();
						}
						foreach($data->uploadFile as $up)
						{
							$up->delete();
						}
						$data->delete();
					}
				}
			}
			//$user->module()->detach($modules);
			if($request->modules != '')
			{
				$user->module()->attach($request->modules);
				$hybridModules = App\Module::where('hybrid',1)->whereHas('childrenModule',function($q) use ($request)
				{
					$q->whereIn('id',$request->modules);
				})->pluck('id');
				foreach($hybridModules as $h)
				{
					if(!in_array($h,$request->modules))
					{
						$user->module()->attach($h);
					}
				}
			}
		}
		return 'DONE';
	}

	public function modulePermissionUpdateGlobal(Request $request)
	{
		if($request->ajax())
		{
			$user = App\User::find($request->user);
			if($request->modules != '' && count($request->modules) > 0)
			{
				for ($i=0; $i < count($request->modules); $i++) 
				{ 
					if($request->permisssion_type[$i] == 2 || $request->permisssion_type[$i] == 7)
					{
						$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->modules[$i])->get();
						if(count($UHM) > 0)
						{
							foreach($UHM as $data)
							{
								foreach($data->department as $dep)
								{
									$dep->delete();
								}
								foreach($data->enterprise as $ent)
								{
									$ent->delete();
								}
								foreach($data->requisition as $req)
								{
									$req->delete();
								}
								foreach($data->project as $pro)
								{
									$pro->delete();
								}
								foreach($data->uploadFile as $up)
								{
									$up->delete();
								}
								$data->delete();
							}
						}
						$user->module()->attach($request->modules[$i]);
						if($request->additional != '')
						{
							$user->module()->detach($request->additional);
							$user->module()->attach($request->additional);
						}
						if(isset($request->enterprise) || isset($request->department))
						{
							$UHM = App\User_has_module::where('user_id',$request->user)->where('module_id',$request->modules[$i])->first();
							if(isset($request->enterprise))
							{
								foreach($request->enterprise as $ent)
								{
									$entM                                    = new App\PermissionEnt();
									$entM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
									$entM->enterprise_id                     = $ent;
									$entM->save();
								}
							}
							if(isset($request->department))
							{
								foreach($request->department as $dep)
								{
									$depM                                    = new App\PermissionDep();
									$depM->user_has_module_iduser_has_module = $UHM->iduser_has_module;
									$depM->departament_id                    = $dep;
									$depM->save();
								}
							}
						}
					}
				}
			}
			return 'DONE';
		}
	}

	public function massiveStore(Request $request)
	{
		if(Auth::user()->module->where('id',12)->count()>0)
		{
			if($request->file('csv_file')->isValid())
			{
				$delimiters = [";" => 0, "," => 0];
				$handle     = fopen($request->file('csv_file'), "r");
				$firstLine  = fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count)
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				if($separator == $request->separator)
				{
					$name		= '/massive_employee/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first	= true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first   = false;
							}
							$csvArr[] = $data;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use ($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);

					foreach ($csvArr as $key => $user) 
					{	
						if (trim($user['correo']) != "") 
						{
							$checkUser = App\User::where('email',trim($user['correo']))->count();
							if ($checkUser==0) 
							{
								$nombre				= ucwords(strtolower($user['nombre']));
								$apellido_paterno	= ucwords(strtolower($user['apellido_paterno']));
								$apellido_materno	= ucwords(strtolower($user['apellido_materno']));
								$genero 			= strtolower($user['genero']);
								$puesto 			= ucwords(strtolower($user['puesto']));
								$correo 			= trim($user['correo']);
								$password 			= trim($user['password']);

								$new_user					= new App\User();
								$new_user->name				= $nombre;
								$new_user->last_name		= $apellido_paterno;
								$new_user->scnd_last_name	= $apellido_materno;
								$new_user->gender			= $genero;
								$new_user->email			= $correo;
								$new_user->password			= bcrypt($password);
								$new_user->status			= "ACTIVE";
								$new_user->area_id			= 3;
								$new_user->departament_id	= 11;
								$new_user->position			= $puesto;
								$new_user->cash				= 0;
								$new_user->cash_amount		= null;
								$new_user->sys_user			= 1;
								$new_user->adglobal			= 0;
								$new_user->save();

								$user_id		= $new_user->id;
								$enterprises	= [5];
								$new_user->enterprise()->attach($enterprises,array('user_id'=>$user_id));

								$modules	= [297,298,312,116,148,179,295,306];
								$additional	= [2,296,99,153,271,5];

								$new_user->module()->attach($additional);
								foreach ($modules as $module)
								{
									$new_user->module()->attach($module);

									$UHM = App\User_has_module::where('user_id',$user_id)->where('module_id',$module)->first();

									if ($module != 306) 
									{
										$projM										= new App\PermissionProject();
										$projM->user_has_module_iduser_has_module	= $UHM->iduser_has_module;
										$projM->project_id							= $user['proyecto'];
										$projM->save();
									}

									if ($module == 306) 
									{
										$puf						= new App\PermissionUploadFile();
										$puf->user_has_module_id	= $UHM->iduser_has_module;
										$puf->permission			= 0;
										$puf->save();
									}
								}
							}
						}
					}
				}
			}			
		}
	}
}
