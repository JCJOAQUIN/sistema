<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Carbon\Carbon;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Illuminate\Support\Str as Str;

class ReportAdministrationEmployeeTypeNominaController extends Controller
{
	private $module_id = 96;
	public function employeeNominaReport(Request $request)
	{
		if (Auth::user()->module->where('id',251)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$enterprise  = $request->enterprise;
			$register    = $request->register;
			$periodicity = $request->periodicity;
			$type        = $request->type;
			$employees = App\RealEmployee::whereHas('workerData',function($q) use($enterprise, $register, $periodicity, $type)
			{
				$q->where('visible',1)
					->where('workerStatus',1)
					->where('enterprise',$enterprise)
					->where('employer_register',$register)
					->where('periodicity',$periodicity)
					->whereHas('accounts',function($q) use($type)
					{
						if($type == "1")
						{
							$q->where('account','LIKE','51%');
						}
						elseif($type == "2")
						{
							$q->where('account','NOT LIKE','51%');
						}
					});
			})
			->orderBy('scnd_last_name','asc')
			->paginate(10);
			return view('reporte.administracion.empleado_nomina',
				[
					'id'          => $data['father'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'child_id'    => $this->module_id,
					'option_id'   => 251,
					'enterprise'  => $enterprise,
					'register'    => $register,
					'periodicity' => $periodicity,
					'type'        => $type,
					'employees'   => $employees
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getEmployerRegister(Request $request)
	{
		if ($request->ajax())
		{
				$rp     = App\EmployerRegister::where('enterprise_id',$request->enterpsieId)->get();
				return Response($rp);
		}
	}

	public function employeeNominaExcel(Request $request)
	{
		if (Auth::user()->module->where('id',251)->count()>0)
		{
			$enterprise     = $request->enterprise;
			$register       = $request->register;
			$periodicity    = $request->periodicity;
			$type           = $request->type;

			$employees  = DB::table('real_employees')
						->selectRaw('
							CONCAT_WS(" ",real_employees.name, real_employees.last_name, real_employees.scnd_last_name) as fullName,
							real_employees.curp as curp,
							real_employees.rfc as rfc,
							worker_datas.employer_register as employer_register,
							worker_datas.sdi as sdi
						')
						->leftJoin('worker_datas','worker_datas.idEmployee','real_employees.id')
						->leftJoin('accounts','accounts.idAccAcc','worker_datas.account')
						->where('worker_datas.visible',1)
						->where('worker_datas.workerStatus',1)
						->where('worker_datas.enterprise',$enterprise)
						->where('worker_datas.employer_register',$register)
						->where('worker_datas.periodicity',$periodicity)
						->where(function($q) use($type)
						{
							if($type == "1")
							{
								$q->where('accounts.account','LIKE','51%');
							}
							elseif($type == "2")
							{
								$q->where('accounts.account','NOT LIKE','51%');
							}
						})
						->orderBy('scnd_last_name','asc')
						->get();


			$periodicityName = App\CatPeriodicity::find($periodicity)->description;

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$nameFile = "Reporte - Nómina por Empleado ".($type == 1 ? "Obra" : "Administrativa")." - ".$periodicityName." ".$register;
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser($nameFile.'.xlsx');

			$headers        = ['Empleados por nómina','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Nombre','Curp','RFC','Registro Patronal','SDI'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($employees as $employee)
			{
				$tmpArr = [];
				foreach($employee as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}
}
