<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;

class ConfiguracionRolController extends Controller
{
	private $module_id = 7;
	
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
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',14)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('configuracion.role.alta',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 14
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search()
	{
		if(Auth::user()->module->where('id',15)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('configuracion.role.busqueda',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id,
					'option_id'	=> 15
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
    {
        if(isset($request->oldRole) && $request->oldRole===$request->name)
        {
            $response = array('valid' => true);
        }
        else
        {
            $role = App\Role::where('name',$request->name)
            				->where('status','ACTIVE')
                            ->get();
            if(count($role)>0)
            {
                $response = array(
                    'valid'     => false,
                    'message'   => 'Ya existe este rol.'
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
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$role			= new App\Role();
			$role->name		= $request->name;
			$role->details	= $request->details;
			$role->status	= 'ACTIVE';
			$role->save();
			$role_id		= $role->id; 

			if (isset($request->moduleCheck) && $request->moduleCheck != null) 
			{
				for ($i=0; $i < count($request->moduleCheck); $i++) 
				{ 
					$moduleCheck 			= new App\Role_has_module();
					$moduleCheck->role_id 	= $role_id;
					$moduleCheck->module_id = $request->moduleCheck[$i];
					$moduleCheck->save();
				}
			}

			if (isset($request->module) && $request->module!=null) 
			{
				for ($j=0; $j < count($request->module); $j++) 
				{ 
					$submodule 			= App\Module::find($request->module[$j])->father;
					$father 			= App\Module::find($submodule)->father;

					if (App\Role_has_module::where('role_id',$role_id)->where('module_id',$father)->count()==0) 
					{
						$module 			= new App\Role_has_module();
						$module->role_id 	= $role_id;
						$module->module_id 	= $father;
						$module->save();
					}

					if (App\Role_has_module::where('role_id',$role_id)->where('module_id',$submodule)->count()==0) 
					{
						$module 			= new App\Role_has_module();
						$module->role_id 	= $role_id;
						$module->module_id 	= $submodule;
						$module->save();
					}


					$module 			= new App\Role_has_module();
					$module->role_id 	= $role_id;
					$module->module_id 	= $request->module[$j];
					$module->save();

					$idrole_has_module 	= $module->idrole_has_module;

					$tempEnterprise 	= 'enterprises_module_'.$request->module[$j];
					$tempDepartment 	= 'departments_module_'.$request->module[$j];

					if (isset($request->$tempEnterprise) && $request->$tempEnterprise!=null) 
					{
						for ($e=0; $e < count($request->$tempEnterprise); $e++) 
						{ 
							$permissionEnt 										= new App\Permission_role_enterprise();
							$permissionEnt->role_has_module_idrole_has_module 	= $idrole_has_module;
							$permissionEnt->enterprise_id 						= $request->$tempEnterprise[$e];
							$permissionEnt->save();
						}
					}
					
					if (isset($request->$tempDepartment) && $request->$tempDepartment!=null) 
					{
						for ($d=0; $d < count($request->$tempDepartment); $d++) 
						{ 
							$permissionDep 										= new App\Permission_role_dep();
							$permissionDep->role_has_module_idrole_has_module 	= $idrole_has_module;
							$permissionDep->departament_id 						= $request->$tempDepartment[$d];
							$permissionDep->save();
						}
					}
				}
			}
			
			
			$alert			= "swal('', 'Rol Creado Exitosamente', 'success');";
			return redirect('configuration/role')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function show($id)
	{
		return redirect('/');
	}


	public function edit($id)
	{
		if(Auth::user()->module->where('id',15)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$role	= App\Role::find($id);
			if ($role != "")
			{
				return view('configuracion.role.cambio',
					[
						'id'		=> $data['father'],
						'title'		=> $data['name'],
						'details'	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id'	=> 15,
						'role'		=> $role
					]);
			}
			else
			{
				return redirect('/error');
			}
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
			$data			 = App\Module::find($this->module_id);
			$role			 = App\Role::find($id);
			$role->name      = $request->name;
			$role->details   = $request->details;
			$role->save();

			foreach (App\Role_has_module::where('role_id',$id)->get() as $rhm) 
			{
			 	App\Permission_role_dep::where('role_has_module_idrole_has_module',$rhm->idrole_has_module)->delete();
			 	App\Permission_role_enterprise::where('role_has_module_idrole_has_module',$rhm->idrole_has_module)->delete();
			}

			App\Role_has_module::where('role_id',$id)->delete();

			$role_id		= $id; 

			if (isset($request->moduleCheck) && $request->moduleCheck != null) 
			{
				for ($i=0; $i < count($request->moduleCheck); $i++) 
				{ 
					$moduleCheck 			= new App\Role_has_module();
					$moduleCheck->role_id 	= $role_id;
					$moduleCheck->module_id = $request->moduleCheck[$i];
					$moduleCheck->save();
				}
			}

			if (isset($request->module) && $request->module!=null) 
			{
				for ($j=0; $j < count($request->module); $j++) 
				{ 
					$submodule 			= App\Module::find($request->module[$j])->father;
					$father 			= App\Module::find($submodule)->father;

					if (App\Role_has_module::where('role_id',$role_id)->where('module_id',$father)->count()==0) 
					{
						$module 			= new App\Role_has_module();
						$module->role_id 	= $role_id;
						$module->module_id 	= $father;
						$module->save();
					}

					if (App\Role_has_module::where('role_id',$role_id)->where('module_id',$submodule)->count()==0) 
					{
						$module 			= new App\Role_has_module();
						$module->role_id 	= $role_id;
						$module->module_id 	= $submodule;
						$module->save();
					}


					$module 			= new App\Role_has_module();
					$module->role_id 	= $role_id;
					$module->module_id 	= $request->module[$j];
					$module->save();

					$idrole_has_module 	= $module->idrole_has_module;

					$tempEnterprise 	= 'enterprises_module_'.$request->module[$j];
					$tempDepartment 	= 'departments_module_'.$request->module[$j];

					if (isset($request->$tempEnterprise) && $request->$tempEnterprise!=null) 
					{
						for ($e=0; $e < count($request->$tempEnterprise); $e++) 
						{ 
							$permissionEnt 										= new App\Permission_role_enterprise();
							$permissionEnt->role_has_module_idrole_has_module 	= $idrole_has_module;
							$permissionEnt->enterprise_id 						= $request->$tempEnterprise[$e];
							$permissionEnt->save();
						}
					}
					
					if (isset($request->$tempDepartment) && $request->$tempDepartment!=null) 
					{
						for ($d=0; $d < count($request->$tempDepartment); $d++) 
						{ 
							$permissionDep 										= new App\Permission_role_dep();
							$permissionDep->role_has_module_idrole_has_module 	= $idrole_has_module;
							$permissionDep->departament_id 						= $request->$tempDepartment[$d];
							$permissionDep->save();
						}
					}
				}
			}
			
			$alert		= "swal('','Rol Actualizado Exitosamente','success');";
			return redirect('configuration/role')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$role			= App\Role::find($id);
			$role->status 	= 'INACTIVE';
			$role->save();
			
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function inactive($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$role			= App\Role::find($id);
			$role->status 	= 'INACTIVE';
			$role->save();
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function reactive($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0) 
		{
			$data 			= App\Module::find($this->module_id);
			$role 			= App\Role::find($id);
			$role->status 	= 'ACTIVE';
			$role->save();
			return 1;
		}
		else
		{
			return 0;
		}
	}

	public function getData(Request $request)
	{
		if($request->ajax())
		{
			$output		= "";
			$header		= "";
			$footer		= "";
			$roles		= App\Role::where('name','LIKE','%'.$request->search.'%')
						->get();
			$countRoles	= count($roles);
			if ($countRoles >= 1) 
			{
				$header = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
				$footer = "</tbody></table>";
				foreach ($roles as $role) 
				{
					$output.="<tr>".
							 "<td>".$role->id."</td>".
							 "<td>".$role->name."</td>".
							 "<td>".
							 "<a title='Editar Rol' href="."'".url::route('role.edit',$role->id)."'"."class='btn btn-green'><span class='icon-pencil'></span></a>";

					if ($role->id != 1) 
					{
					 	if ($role->status == 'ACTIVE') 
						{
							$output .= "<a title='Suspender Rol' href="."'".url::route('role.inactive',$role->id)."'"." class='role-delete btn btn-red'><span class='icon-bin'></span></a>";
						}
						if ($role->status == 'INACTIVE') 
						{
							$output .= "<a title='Reactivar Rol' href="."'".url::route('role.reactive',$role->id)."'"." class='role-reactive btn btn-green'><span class='icon-checkmark'></span></a>";
						}
					}
					$output.="</tr>";
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

	public static function build_modules($father)
	{
		$result		= '';
		$modules	= App\Module::all()
					->where('permissionRequire',0)
					->where('father',$father);
		if(isset($modules) && count($modules)>0)
		{
			$result .= '<ul>';
			foreach ($modules as $key => $value)
			{
				$result .= '<li><input name="moduleCheck[]" type="checkbox" value="'.$value['id'].'" id="module_'.$value['id'].'"><label class="switch"  for="module_'.$value['id'].'"><span class="slider round"></span>'.$value['name'].'</label>'.App\Http\Controllers\ConfiguracionRolController::build_modules($value['id']).'</li>';
			}
			$result .= '</ul>';
		}
		return $result;
	}

	public static function build_modules_permission($father)
	{
		$result		= '';
		$modules	= App\Module::all()
					->where('permissionRequire',1)
					->where('father',$father);
		if(isset($modules) && count($modules)>0)
		{
			$result .= '<ul>';
			foreach ($modules as $key => $value)
			{
				$result .= '<li><input class="newmodules" type="checkbox" value="'.$value['id'].'" id="module_'.$value['id'].'"><label class="switch" for="module_'.$value['id'].'"><span class="slider round"></span>'.$value['name'].'</label>'.App\Http\Controllers\ConfiguracionRolController::build_modules_permission($value['id']).'</li>';
			}
			$result .= '</ul>';
		}
		return $result;
	}

	public function getModules(Request $request)
	{
		if($request->ajax())
		{
			$role		= App\Role::find($request->role_id);
			$response	= array();
			foreach ($role as $key => $value)
			{
				$temp	= $value->module;
				foreach ($temp as $key => $value)
				{
					$response[] = $value->id;
				}
			}
			return Response($response);
		}
	}

	public function getMod(Request $request)
	{
		if($request->ajax())
		{
			$role		= App\Role::find($request->role_id);
			$response	= array();
			$modules	= $role->module;
			foreach ($modules as $key => $value)
			{
				$response[] = $value->id;
			}
			return Response($response);
		}
	}
}
