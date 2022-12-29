<?php

namespace App\Console\Commands;

use App\Prenomina;
use App\RealEmployee;
use App\EmployerRegister;
use App\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WeeklyPayroll extends Command
{
	protected $signature = 'payroll:weekly';

	protected $description = 'Generar nóminas semanalmente';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{

		$now		= Carbon::now();
		$employees = RealEmployee::join('worker_datas','real_employees.id','=','idEmployee')
		->join('accounts','worker_datas.account','=','accounts.idAccAcc')
		->where('worker_datas.visible',1)
		->where('worker_datas.workerStatus',1)
		->where('worker_datas.periodicity','LIKE','02')
		->where('accounts.account','LIKE','51%')
		->whereNull('worker_datas.employer_register')
		->pluck('real_employees.id');
		if($employees->count() > 0)
		{
			$prenomina						= new Prenomina();
			$prenomina->title				= 'NOM OBRA SEM '.$now->weekOfYear.'-'.$now->year.' SIN RP';
			$prenomina->datetitle			= $now;
			$prenomina->date				= $now;
			$prenomina->idCatTypePayroll	= '001';
			$prenomina->kind				= 1;
			$prenomina->status				= 0;
			$prenomina->project_id 			= null;
			$prenomina->save();
			$prenomina->employee()->attach($employees);
		}
		$employees = RealEmployee::join('worker_datas','real_employees.id','=','idEmployee')
		->join('accounts','worker_datas.account','=','accounts.idAccAcc')
		->where('worker_datas.visible',1)
		->where('worker_datas.workerStatus',1)
		->where('worker_datas.periodicity','LIKE','02')
		->where('accounts.account','NOT LIKE','51%')
		->whereNull('worker_datas.employer_register')
		->pluck('real_employees.id');
		if($employees->count() > 0)
		{
			$prenomina						= new Prenomina();
			$prenomina->title				= 'NOM ADMINISTRATIVA SEM '.$now->weekOfYear.'-'.$now->year.' SIN RP';
			$prenomina->datetitle			= $now;
			$prenomina->date				= $now;
			$prenomina->idCatTypePayroll	= '001';
			$prenomina->kind				= 2;
			$prenomina->status				= 1;
			$prenomina->project_id 			= null;
			$prenomina->save();
			$prenomina->employee()->attach($employees);
		}

		foreach (EmployerRegister::all() as $reg) 
		{
			foreach(Project::all() as $project)
			{
				$employees = RealEmployee::join('worker_datas','real_employees.id','=','idEmployee')
				->join('accounts','worker_datas.account','=','accounts.idAccAcc')
				->where('worker_datas.visible',1)
				->where('worker_datas.workerStatus',1)
				->where('worker_datas.periodicity','LIKE','02')
				->where('accounts.account','LIKE','51%')
				->where('worker_datas.employer_register',$reg->employer_register)
				->where('worker_datas.project',$project->idproyect)
				->pluck('real_employees.id');
				if($employees->count() > 0)
				{
					$prenomina						= new Prenomina();
					$prenomina->title				= 'NOM OBRA '.$project->proyectName.' SEM '.$now->weekOfYear.'-'.$now->year.' RP '.$reg->employer_register;
					$prenomina->datetitle			= $now;
					$prenomina->date				= $now;
					$prenomina->idCatTypePayroll	= '001';
					$prenomina->kind				= 1;
					$prenomina->status				= 0;
					$prenomina->project_id 			= $project->idproyect;
					$prenomina->employer_register 	= $reg->employer_register;
					$prenomina->save();
					$prenomina->employee()->attach($employees);
				}
				$employees = RealEmployee::join('worker_datas','real_employees.id','=','idEmployee')
				->join('accounts','worker_datas.account','=','accounts.idAccAcc')
				->where('worker_datas.visible',1)
				->where('worker_datas.workerStatus',1)
				->where('worker_datas.periodicity','LIKE','02')
				->where('accounts.account','NOT LIKE','51%')
				->where('worker_datas.employer_register',$reg->employer_register)
				->where('worker_datas.project',$project->idproyect)
				->pluck('real_employees.id');
				if($employees->count() > 0)
				{
					$prenomina						= new Prenomina();
					$prenomina->title				= 'NOM ADMINISTRATIVA '.$project->proyectName.' SEM '.$now->weekOfYear.'-'.$now->year.' RP '.$reg->employer_register;
					$prenomina->datetitle			= $now;
					$prenomina->date				= $now;
					$prenomina->idCatTypePayroll	= '001';
					$prenomina->kind				= 2;
					$prenomina->status				= 1;
					$prenomina->project_id 			= $project->idproyect;
					$prenomina->employer_register 	= $reg->employer_register;
					$prenomina->save();
					$prenomina->employee()->attach($employees);
				}	
			}
		}
	}
}
