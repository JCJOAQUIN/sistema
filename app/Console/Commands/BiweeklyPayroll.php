<?php

namespace App\Console\Commands;

use App\Prenomina;
use App\RealEmployee;
use App\EmployerRegister;
use App\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BiweeklyPayroll extends Command
{
	protected $signature = 'payroll:biweekly';

	protected $description = 'Generar nóminas quincenales';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$now		= Carbon::now();
		$secondQ	= Carbon::now()->endOfMonth();
		$seven		= Carbon::now()->addDays(7);
		$firstQ		= Carbon::createFromDate($now->year, $now->month, 15);
		if($firstQ->diff($seven)->days == 0 || $secondQ->diff($seven)->days == 0)
		{
			if($firstQ->diff($seven)->days == 0)
			{
				$quin	= 'Q1';
			}
			else
			{
				$quin	= 'Q2';
			}
			$employees = RealEmployee::join('worker_datas','real_employees.id','=','idEmployee')
			->join('accounts','worker_datas.account','=','accounts.idAccAcc')
			->where('worker_datas.visible',1)
			->where('worker_datas.workerStatus',1)
			->where('worker_datas.periodicity','LIKE','04')
			->where('accounts.account','LIKE','51%')
			->whereNull('worker_datas.employer_register')
			->pluck('real_employees.id');
			if($employees->count() > 0)
			{
				$prenomina						= new Prenomina();
				$prenomina->title				= 'NOM OBRA '.$quin.' '.$now->month.'-'.$now->year.' SIN RP ';
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
			->where('worker_datas.periodicity','LIKE','04')
			->where('accounts.account','NOT LIKE','51%')
			->whereNull('worker_datas.employer_register')
			->pluck('real_employees.id');
			if($employees->count() > 0)
			{
				$prenomina						= new Prenomina();
				$prenomina->title				= 'NOM ADMINISTRATIVA '.$quin.' '.$now->month.'-'.$now->year.' SIN RP ';
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
					->where('worker_datas.periodicity','LIKE','04')
					->where('accounts.account','LIKE','51%')
					->where('worker_datas.employer_register',$reg->employer_register)
					->where('worker_datas.project',$project->idproyect)
					->pluck('real_employees.id');
					if($employees->count() > 0)
					{
						$prenomina						= new Prenomina();
						$prenomina->title				= 'NOM OBRA '.$project->proyectName.' '.$quin.' '.$now->month.'-'.$now->year.' RP '.$reg->employer_register;
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
					->where('worker_datas.periodicity','LIKE','04')
					->where('accounts.account','NOT LIKE','51%')
					->where('worker_datas.employer_register',$reg->employer_register)
					->where('worker_datas.project',$project->idproyect)
					->pluck('real_employees.id');
					if($employees->count() > 0)
					{
						$prenomina						= new Prenomina();
						$prenomina->title				= 'NOM ADMINISTRATIVA '.$project->proyectName.' '.$quin.' '.$now->month.'-'.$now->year.' RP '.$reg->employer_register;
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
}
