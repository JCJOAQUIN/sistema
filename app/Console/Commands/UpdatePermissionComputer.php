<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User_has_module;
use App\PermissionProject;
use App\Project;

class UpdatePermissionComputer extends Command
{
	protected $signature = 'update:permissionComputer';

	protected $description = 'Actualizar permisos del módulo de gastos';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$users_has_modules = User_has_module::whereIn('module_id',[28,29,36,37,118,119,120,121])->get();

		foreach ($users_has_modules as $uhm) 
		{
			foreach(Project::all() as $project)
			{
				$new_permission										= new PermissionProject();
				$new_permission->project_id							= $project->idproyect;
				$new_permission->user_has_module_iduser_has_module	= $uhm->iduser_has_module;
				$new_permission->save();
			}
		}

		$this->info('Permisos actualizados exitosamente');
	}
}
