<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	protected $commands = [
		'App\Console\Commands\NominaQueue',
		'App\Console\Commands\WeeklyPayroll',
		'App\Console\Commands\BiweeklyPayroll',
		'App\Console\Commands\TicketStatus',
		'App\Console\Commands\BalanceSheetQueue',
		'App\Console\Commands\Update',
		'App\Console\Commands\AutoRequest',
		'App\Console\Commands\SendNonConformityStatusNotification',
		'App\Console\Commands\UpdatePermissionComputer',
	];

	protected function schedule(Schedule $schedule)
	{
		$schedule->command('queue:nomina')
			->hourly();
		$schedule->command('payroll:weekly')
			->weeklyOn(1, '8:00');
		$schedule->command('payroll:biweekly')
			->dailyAt('8:00');
		$schedule->command('ticket:status')
			->dailyAt('8:00');
		$schedule->command('queue:balancesheet')
			->dailyAt('0:00');
		$schedule->command('queue:restart')
			->everyFiveMinutes();
		$schedule->command('queue:work --daemon --timeout=0')
			->everyMinute()
			->withoutOverlapping();
		$schedule->command('auto:request')
			->dailyAt('0:00');
		$schedule->command('ncNotification:send')
			->dailyAt('7:00');
		$schedule->command('update:permissionComputer')
			->dailyAt('2:30');
	}

	protected function commands()
	{
		$this->load(__DIR__.'/Commands');
		require base_path('routes/console.php');
	}
}
