<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Excel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Carbon\Carbon;

class ReportAdministrationAttendanceController extends Controller
{
    private $module_id = 96;
    public function attendance(Request $requestSearch)
    {
        if (Auth::user()->module->where('id',359)->count()>0) 
        {
            $data = App\Module::find($this->module_id);
            $attendances = DB::table('real_employees')
                ->selectRaw('
                real_employees.id,
                real_employees.name,
                real_employees.last_name,
                real_employees.scnd_last_name,
                enterprises.name as enterprise,
                projects.proyectName as project,
                IFNULL(attendances.num_attendances,0) as num_attendances
                ')
                ->join('worker_datas',function($q)
                {
                    $q->on('real_employees.id','=','worker_datas.idEmployee')
                        ->where('worker_datas.workerStatus',1)
                        ->where('worker_datas.visible',1);
                })
                ->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
                ->leftJoin('projects','worker_datas.project','projects.idproyect')
                ->leftJoin(
                    DB::raw('
                    (
                        SELECT
                            COUNT(id) as num_attendances,
                            employee_id
                        FROM
                            employee_attendances
                        '.(($requestSearch->maxdate != '' && $requestSearch->mindate != '') ? 'WHERE created_at BETWEEN "'.(Carbon::createFromFormat('d-m-Y',$requestSearch->mindate)->format('Y-m-d 00:00:00')).'" AND "'.(Carbon::createFromFormat('d-m-Y',$requestSearch->maxdate)->format('Y-m-d 23:59:59')).'"' : '').'
                        GROUP BY
                            employee_id
                    ) as attendances'),'real_employees.id','attendances.employee_id')
                ->orderBy('id')
                ->where(function($q) use($requestSearch)
                {
                    if($requestSearch->employee != '')
                    {
                        $q->whereIn('real_employees.id',$requestSearch->employee);
                    }
                    if($requestSearch->enterprise != '')
                    {
                        $q->whereIn('worker_datas.enterprise',$requestSearch->enterprise);
                    }
                    if($requestSearch->department != '')
                    {
                        $q->whereIn('worker_datas.department',$requestSearch->department);
                    }
                    if($requestSearch->attendance != '')
                    {
                        if($requestSearch->attendance == 1)
                        {
                            $q->whereRaw('IFNULL(attendances.num_attendances,0) > 0');
                        }
                        else
                        {
                            $q->whereRaw('IFNULL(attendances.num_attendances,0) = 0');
                        }
                    }
                })
                ->paginate(15);
            return view('reporte.administracion.attendance',
            [
                'id'            => $data['father'],
                'title'         => $data['name'],
                'details'       => $data['details'],
                'child_id'      => $this->module_id,
                'option_id'     => 359,
                'requestSearch' => $requestSearch,
                'attendances'   => $attendances
            ]);
        }
    }

    public function attendanceExcel(Request $requestSearch)
    {
        if (Auth::user()->module->where('id',359)->count()>0) 
        {
            $data = App\Module::find($this->module_id);
            $attendances = DB::table('real_employees')
                ->selectRaw('
                    real_employees.id,
                    real_employees.last_name,
                    real_employees.scnd_last_name,
                    real_employees.name,
                    enterprises.name as enterprise,
                    projects.proyectName as project,
                    wd_departments.subdepartments,
                    wd_wbs.wbs,
                    IFNULL(attendances.num_attendances,0) as num_attendances,
                    employee_attendances.latitude,
                    employee_attendances.longitude,
                    CONCAT("https://www.google.com/maps?q=",employee_attendances.latitude,",",employee_attendances.longitude) as map,
                    DATE(employee_attendances.created_at) as date_created,
                    TIME(employee_attendances.created_at) as time_created
                ')
                ->join('worker_datas',function($q)
                {
                    $q->on('real_employees.id','=','worker_datas.idEmployee')
                        ->where('worker_datas.workerStatus',1)
                        ->where('worker_datas.visible',1);
                })
                ->leftJoin('enterprises','worker_datas.enterprise','enterprises.id')
                ->leftJoin('projects','worker_datas.project','projects.idproyect')
                ->leftJoin(
                    DB::raw('
                    (
                        SELECT
                            COUNT(id) as num_attendances,
                            employee_id
                        FROM
                            employee_attendances
                        '.(($requestSearch->maxdate != '' && $requestSearch->mindate != '') ? 'WHERE created_at BETWEEN "'.(Carbon::createFromFormat('d-m-Y',$requestSearch->mindate)->format('Y-m-d 00:00:00')).'" AND "'.(Carbon::createFromFormat('d-m-Y',$requestSearch->maxdate)->format('Y-m-d 23:59:59')).'"' : '').'
                        GROUP BY
                            employee_id
                    ) as attendances'),'real_employees.id','attendances.employee_id')
                ->leftJoin('employee_attendances','real_employees.id','employee_attendances.employee_id')
                ->leftJoin(DB::raw('(SELECT GROUP_CONCAT(subdepartments.name SEPARATOR ", ") as subdepartments, employee_subdepartments.working_data_id as wd_id FROM employee_subdepartments INNER JOIN subdepartments on subdepartment_id=subdepartments.id GROUP BY employee_subdepartments.working_data_id) as wd_departments'),'wd_departments.wd_id','worker_datas.id')
                ->leftJoin(DB::raw('(SELECT GROUP_CONCAT(cat_code_w_bs.code_wbs SEPARATOR ", ") as wbs, employee_w_b_s.working_data_id as wd_id FROM employee_w_b_s INNER JOIN cat_code_w_bs ON cat_code_w_bs_id=cat_code_w_bs.id GROUP BY employee_w_b_s.working_data_id) as wd_wbs'),'wd_wbs.wd_id','worker_datas.id')
                ->orderBy('id')
                ->where(function($q) use($requestSearch)
                {
                    if($requestSearch->employee != '')
                    {
                        $q->whereIn('real_employees.id',$requestSearch->employee);
                    }
                    if($requestSearch->enterprise != '')
                    {
                        $q->whereIn('worker_datas.enterprise',$requestSearch->enterprise);
                    }
                    if($requestSearch->department != '')
                    {
                        $q->whereIn('worker_datas.department',$requestSearch->department);
                    }
                    if($requestSearch->attendance != '')
                    {
                        if($requestSearch->attendance == 1)
                        {
                            $q->whereRaw('IFNULL(attendances.num_attendances,0) > 0');
                        }
                        else
                        {
                            $q->whereRaw('IFNULL(attendances.num_attendances,0) = 0');
                        }
                    }
                })
                ->get();
            if(count($attendances) == 0)
            {
                $alert = "swal('','No se encuentran resultados del filtro ingresado.', 'error');";
                return back()->with('alert',$alert);
            }
            $defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
            $rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
            $mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('ED704D')->setFontColor(Color::WHITE)->build();
            $mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('E4A905')->setFontColor(Color::WHITE)->build();
            $smStyleCol1    = (new StyleBuilder())->setBackgroundColor('F5AE9C')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
            $smStyleCol2    = (new StyleBuilder())->setBackgroundColor('F5CD65')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
            $writer         = WriterEntityFactory::createXLSXWriter();
            $writer->setDefaultRowStyle($defaultStyle)->openToBrowser('reporte-asistencias.xlsx');
            $sheet = $writer->getCurrentSheet();
            $sheet->setName('Asistencias');
            $mainHeaderArr = ['INFORMACIÓN DEL EMPLEADO','','','','','','','','INFORMACIÓN DE ASISTENCIAS','','','','',''];
            $tmpMHArr      = [];
            foreach($mainHeaderArr as $k => $mh)
            {
                if($k <= 7)
                {
                    $tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
                }
                else
                {
                    $tmpMHArr[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
                }
            }
            $rowFromValues = WriterEntityFactory::createRow($tmpMHArr);
            $writer->addRow($rowFromValues);
            $headerArr = ['ID Empleado','Apellido Paterno','Apellido Materno','Nombre','Empresa','Proyecto','Subdepartamento','WBS','Total asistencias del periodo','Latitud','Longitud','URL al mapa','Fecha de asistencia','Hora de asistencia'];
            $tmpHeaderArr = [];
            foreach($headerArr as $k => $sh)
            {
                if($k <= 7)
                {
                    $tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol1);
                }
                else
                {
                    $tmpHeaderArr[] = WriterEntityFactory::createCell($sh,$smStyleCol2);
                }
            }
            $rowFromValues = WriterEntityFactory::createRow($tmpHeaderArr);
            $writer->addRow($rowFromValues);
        }
        $employeeId = null;
        $kindRow = false;
        foreach($attendances as $att)
        {
            if($employeeId != $att->id)
            {
                $employeeId = $att->id;
                $kindRow = !$kindRow;
            }
            $tmpArr = [];
            foreach($att as $k => $r)
            {
                $tmpArr[] = WriterEntityFactory::createCell($r);
            }
            if($kindRow)
            {
                $rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
            }
            else
            {
                $rowFromValues = WriterEntityFactory::createRow($tmpArr);
            }
            $writer->addRow($rowFromValues);
        }
        return $writer->close();
    }
}
