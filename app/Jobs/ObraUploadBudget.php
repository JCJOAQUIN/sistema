<?php

namespace App\Jobs;

use App\BudgetDetails;
use Excel;
use App\BudgetUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ObraUploadBudget implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $BudgetUploads;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BudgetUploads $BudgetUploads)
    {
        $this->BudgetUploads = $BudgetUploads;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bgup = $this->BudgetUploads;
        $path = Storage::disk('public')->path($bgup->file);
        Excel::selectSheetsByIndex(0)->load($path, function ($reader) use ($bgup) {



            $reader->noHeading();

            $reader->takeColumns(7);


            $results = $reader->get();


            $group_name = '';
            $search_codes = false;
            $father = null;
            foreach ($reader->toArray() as $sheet) {


                if ($sheet[0] != "Código" && !$search_codes) {
                    if ($sheet[0] == 'Cliente:')
                        $bgup->client = $sheet[1];
                    if ($sheet[0] == 'Concurso No.')
                        $bgup->contestNo = $sheet[1];
                    if ($sheet[0] == 'Obra:')
                        $bgup->obra = $sheet[1];
                    if ($sheet[0] == 'Lugar:')
                        $bgup->place = $sheet[1];
                    if ($sheet[5] == 'Inicio Obra:') {
                        $old_date    = new \DateTime($sheet[6]);
                        $new_date = $old_date->format('Y-m-d');
                        $bgup->startObra = $new_date;
                    }
                    if ($sheet[5] == 'Fin Obra:') {
                        $old_date    = new \DateTime($sheet[6]);
                        $new_date = $old_date->format('Y-m-d');
                        $bgup->endObra = $new_date;
                    }
                    $bgup->save();
                    continue;
                }

                if ($sheet[0] == "Código") {
                    $search_codes = true;
                    continue;
                }
                $concept = str_replace("\r", "", $sheet[1]);
                $concept = str_replace("\n", "", $concept);
                $concept = str_replace("\xc2\xa0", " ", $concept);
                $concept = str_replace("_x000D_", " ", $concept);

                if (
                    $search_codes && (!empty($sheet[0]) && !empty($sheet[1]))
                    && (empty($sheet[2]) && empty($sheet[3]) && empty($sheet[4]) && empty($sheet[5]) && empty($sheet[6]))
                ) {


                    if (!is_null($father) && ($father->code[0] == $sheet[0][0])) {
                        if (strlen($sheet[0]) > strlen($father->code)) {
                            $father = BudgetDetails::create([
                                'idUpload' => $bgup->id,
                                'code' => $sheet[0],
                                'concept' => $concept,
                                'father' => $father->id,
                            ]);
                        } else if (strlen($sheet[0]) == strlen($father->code)) {
                            $father = $father->parent;

                            $father = BudgetDetails::create([
                                'idUpload' => $bgup->id,
                                'code' => $sheet[0],
                                'concept' => $concept,
                                'father' => $father->id,
                            ]);
                        }
                    } else {

                        $father = BudgetDetails::create([
                            'idUpload' => $bgup->id,
                            'code' => $sheet[0],
                            'concept' => $concept,
                        ]);
                    }
                }

                if ($search_codes && !empty($sheet[0])    && !empty($sheet[1]) && !empty($sheet[2]) && !empty($sheet[4])) {
                    BudgetDetails::create([
                        'idUpload' => $bgup->id,
                        'code' => $sheet[0],
                        'concept' => $concept,
                        'measurement' => $sheet[2],
                        'amount' => $sheet[3],
                        'price' => $sheet[4],
                        'import' => $sheet[5],
                        'incidence' => floatval($sheet[6]) * 100,
                        'father' => $father->id,
                    ]);
                }
            }
        });
        $bgup->status = 1;
        $bgup->save();
    }
}
