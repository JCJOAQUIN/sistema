<?php

namespace App\Jobs;

use App\BreakdownWagesDetails;
use Excel;
use App\BreakdownWagesUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ObraUploadBreakdownWages implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $BreakdownWagesUploads;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(BreakdownWagesUploads $BreakdownWagesUploads)
	{
		$this->BreakdownWagesUploads = $BreakdownWagesUploads;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$bgup = $this->BreakdownWagesUploads;
		$path = Storage::disk('public')->path($bgup->file);
		Excel::selectSheetsByIndex(0)->load($path, function ($reader) use ($bgup)
		{
			$reader->noHeading();
			$reader->takeColumns(9);
			$results = $reader->get();
			$first = true;
			$group_name = '';
			$search_codes = false;
			foreach ($reader->toArray() as $sheet)
			{
				if ($sheet[0] != "Código" && !$search_codes)
				{
					if ($sheet[0] == 'Cliente:')
						$bgup->client = $sheet[1];
					if ($sheet[0] == 'Concurso No:')
						$bgup->contestNo = $sheet[1];
					if ($sheet[0] == 'Obra:')
						$bgup->obra = $sheet[1];
					if ($sheet[0] == 'Lugar:')
						$bgup->place = $sheet[1];
					if ($sheet[0] == 'Ciudad:')
						$bgup->city = $sheet[1];
					if ($sheet[3] == 'Inicio obra:')
					{
						$old_date    = new \DateTime($sheet[4]);
						$new_date = $old_date->format('Y-m-d');
						$bgup->startObra = $new_date;
					}
					if ($sheet[5] == 'Fin obra:')
					{
						$old_date    = new \DateTime($sheet[6]);
						$new_date = $old_date->format('Y-m-d');
						$bgup->endObra = $new_date;
					}
					$bgup->save();
					continue;
				}

				if ($sheet[0] == "Código")
				{
					$search_codes = true;
					continue;
				}
				if ($first)
				{
					$group_name = $sheet[1];
					$first = false;
				}

				if (!$first && (empty($sheet[0]) && empty($sheet[2]) && empty($sheet[3]) && empty($sheet[4]) && empty($sheet[5]) && empty($sheet[6]) && empty($sheet[7])) && !empty($sheet[1]))
				{
					$group_name = $sheet[1];
				}

				if (!empty($sheet[0]) && !empty($sheet[1]) && !empty($sheet[2]) && !empty($sheet[3]) && !empty($sheet[4]) && !empty($sheet[5]))
				{
					BreakdownWagesDetails::create([
						'idUpload'         => $bgup->id,
						'groupName'        => $group_name,
						'code'             => $sheet[0],
						'concept'          => $sheet[1],
						'measurement'      => $sheet[2],
						'baseSalaryPerDay' => $sheet[3],
						'realSalaryFactor' => $sheet[4],
						'realSalary'       => $sheet[5],
						'viatics'          => $sheet[6],
						'feeding'          => $sheet[7],
						'totalSalary'      => $sheet[8],
					]);
				}
			}
		}, 'UTF-8');
		$bgup->status = 1;
		$bgup->save();
	}
}
