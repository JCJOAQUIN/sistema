<?php

namespace App\Jobs;


use Excel;
use App\ObraProgramUploads;
use App\ObraProgramConcept;
use App\ObraProgramDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ObraUploadObraProgram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ObraProgramUploads;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ObraProgramUploads $ObraProgramUploads)
    {
        $this->ObraProgramUploads = $ObraProgramUploads;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bgup = $this->ObraProgramUploads;
        $path = Storage::disk('public')->path($bgup->file);
        Excel::selectSheetsByIndex(0)->load($path, function ($reader) use ($bgup) {



            $reader->noHeading();



            $results = $reader->get();

            $search_codes = false;
            $group_name = '';
            $concept;
            $parent;
            $type = 0;
            $date_count = 0;
            $reader->formatDates(false);

            foreach ($reader->toArray() as $sheet) {

                if ($sheet[0] != "Código" && !$search_codes) {
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
                    if ($sheet[3] == 'Inicio obra:') {
                        $old_date    = new \DateTime($sheet[4]);
                        $new_date = $old_date->format('Y-m-d');
                        $bgup->startObra = $new_date;
                    }
                    if ($sheet[3] == 'Fin obra:') {
                        $old_date    = new \DateTime($sheet[4]);
                        $new_date = $old_date->format('Y-m-d');
                        $bgup->endObra = $new_date;
                    }
                    $bgup->save();
                    continue;
                }

                if ($sheet[0] == "Código") {

                    $date_count = count($sheet);
                    $search_codes = true;
                    if (str_contains(strtolower($sheet[3]), 'mes')) {
                        $bgup->date_type = 'Mes';
                        $bgup->save();
                    } else if (str_contains(strtolower($sheet[3]), 'semana')) {
                        $bgup->date_type = 'Semana';
                        $bgup->save();
                    }
                    continue;
                }




                if ($search_codes) {
                    if (empty($sheet[0]) && !empty($sheet[1]) && empty($sheet[2]) && empty($sheet[3])) {
                        $parent = ObraProgramConcept::create([
                            'idUpload'                     => $bgup->id,
                            'concept'                        => $sheet[1],
                        ]);
                    }

                    if ((!empty($sheet[0])    && !empty($sheet[1]) && !empty($sheet[2]) && !empty($sheet[3]))
                        || $type > 0
                    ) {
                        if ($type == 0) {
                            $concept = str_replace("\r", "", $sheet[1]);
                            $concept = str_replace("\n", "", $concept);
                            $concept = str_replace("\xc2\xa0", " ", $concept);
                            $concept = str_replace("_x000D_", " ", $concept);

                            $concept = ObraProgramConcept::create([
                                'idUpload'    => $bgup->id,
                                'father'            => $parent->id,
                                'code'                 => $sheet[0],
                                'concept'         => $concept,
                                'measurement' => $sheet[2],
                            ]);
                        }


                        for ($i = 3; $i < $date_count - 1; $i++) {

                            ObraProgramDetails::create([
                                'idObraProgramConcept'    => $concept->id,
                                'amount' => $type == 0 ?  ($sheet[$i] * 100) : $sheet[$i],
                                'type' => $type,
                                'order' => $i - 2,
                            ]);
                        }
                        switch ($type) {
                            case 0:
                                $type = 1;
                                break;
                            case 1:
                                $type = 2;
                                break;
                            case 2:
                                $type = 0;
                                break;

                            default:
                                # code...
                                break;
                        }
                    }
                }
            }
        }, 'UTF-8');
        $bgup->status = 1;
        $bgup->save();
    }
}
