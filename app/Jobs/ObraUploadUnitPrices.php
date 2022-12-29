<?php

namespace App\Jobs;

use App\UnitPricesDetails;
use Excel;
use App\UnitPricesUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ObraUploadUnitPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $UnitPricesUploads;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UnitPricesUploads $UnitPricesUploads)
    {
        $this->UnitPricesUploads = $UnitPricesUploads;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bgup = $this->UnitPricesUploads;
        $path = Storage::disk('public')->path($bgup->file);

        Excel::selectSheetsByIndex(0)->load($path, function ($reader) use ($bgup) {



            $reader->noHeading();

            $reader->takeColumns(8);


            $first = true;
            $search_codes = false;
            $parent = null;
            $type = 0;
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
                        $old_date    = new \DateTime($sheet[4]);
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
                //partida
                if ($first && $search_codes) {
                    $parent = UnitPricesDetails::create([
                        'code' => $sheet[1],
                        'concept' => $sheet[4],
                        'type' => 0,
                        'idUpload' => $bgup->id,
                    ]);
                    $type = 0; //partida
                    $first = false;
                    continue;
                }

                if ($search_codes) {


                    if ($type == 0 && $sheet[0] == 'Partida:') {
                        $parent = UnitPricesDetails::create([
                            'code' => $sheet[1],
                            'concept' => $sheet[4],
                            'type' => 0,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 0; //partida
                        $first = false;
                        continue;
                    }


                    if ($sheet[0] == 'Análisis:' && $type == 0) {
                        $parent = UnitPricesDetails::create([
                            'concept' => $sheet[1],
                            'measurement' => $sheet[3],
                            'amount' => $sheet[5],
                            'import' => $sheet[6],
                            'type' => 1,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $first = false;
                        $type = 1; //analisis
                        continue;
                    }

                    if ($type == 7 && $sheet[1] != 'Costo Directo:') {
                        $type = 2;
                        $parent = $parent->parent;
                    }

                    if ($type == 1) {
                        UnitPricesDetails::create([
                            'concept' => $sheet[0],
                            'type' => 2,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 2; //analisis titulo
                        continue;
                    }

                    if ($type == 2) {
                        $parent = UnitPricesDetails::create([
                            'concept' => $sheet[0],
                            'type' => 3,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 3; //grupo
                        continue;
                    }

                    if ($type == 3 && empty($sheet[0]) && empty($sheet[1]) && empty($sheet[2]) && empty($sheet[3]) && empty($sheet[4]) && empty($sheet[5]) && empty($sheet[6]) && empty($sheet[7])) {
                        $type = 6;
                        continue;
                    }

                    if ($type == 3 && !empty($sheet[0]) && !empty($sheet[1]) && !empty($sheet[2])) {
                        UnitPricesDetails::create([
                            'code' => $sheet[0],
                            'concept' => $sheet[1],
                            'measurement' => $sheet[2],
                            'price' => $sheet[3],
                            'op' => $sheet[4],
                            'amount' => $sheet[5],
                            'import' => $sheet[6],
                            'incidence' => floatval($sheet[7]) * 100,
                            'type' => 4, //concepto
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        continue;
                    }
                    if ($type == 3 && $sheet[1] == 'Importe:') {
                        UnitPricesDetails::create([
                            'import' => $sheet[6],
                            'type' => 5,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 5; //importe
                        continue;
                    }
                    if ($type == 5) {
                        UnitPricesDetails::create([
                            'concept' => $sheet[1],
                            'amount' => $sheet[5],
                            'import' => $sheet[6],
                            'incidence' => floatval($sheet[7]) * 100,
                            'type' => 6,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 6; //rendimiento
                    }

                    if ($type == 4 && $sheet[0] == 'SUBTOTAL:')
                        $type = 6;

                    if ($type == 6 && $sheet[0] == 'SUBTOTAL:') {

                        UnitPricesDetails::create([
                            'concept' => $sheet[1],
                            'type' => 7,
                            'import' => $sheet[6],
                            'incidence' => floatval($sheet[7]) * 100,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 7; //sub total
                        continue;
                    }

                    if ($type == 7) {
                        UnitPricesDetails::create([
                            'type' => 8,
                            'import' => $sheet[6],
                            'incidence' => floatval($sheet[7]) * 100,
                            'father' => $parent->id,
                            'idUpload' => $bgup->id,
                        ]);
                        $type = 0; //partida
                        continue;
                    }
                }
            }
        }, 'UTF-8');
        $bgup->status = 1;
        $bgup->save();

    }
}
