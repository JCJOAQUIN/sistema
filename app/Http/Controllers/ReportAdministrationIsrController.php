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

class ReportAdministrationIsrController extends Controller
{
	private $module_id = 96;
	public function isrReport(Request $request)
	{
		if (Auth::user()->module->where('id',195)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$employee       = $request->employee;
			$title_request  = $request->title_request;
			$type           = $request->type;
			$folio          = $request->folio;
			$enterprise     = $request->enterprise;

			if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate=="") || ($mindate!="" && $maxdate!=""))
			{
				$initRange  = $mindate;
				$endRange   = $maxdate;

				if(($mindate=="" && $maxdate!="") || ($mindate!="" && $maxdate==""))
				{
					$alert = "swal('', 'Por favor delimite por un rango de fecha para proceder.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
				if ($mindate!="" && $maxdate!="" && $endRange < $initRange) 
				{
					$alert = "swal('', 'La fecha inicial no puede ser mayor a la fecha final.', 'error');";
					return back()->with(['alert'=>$alert]);
				}
			}

			$requests = App\RequestModel::leftJoin('nominas','nominas.idFolio','request_models.folio')
						->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
						->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
						->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
						->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
						->leftJoin('salaries','salaries.idnominaEmployee','nomina_employees.idnominaEmployee')
						->leftJoin('users','users.id','=','request_models.idRequest')
						->leftJoin('cat_type_payrolls','cat_type_payrolls.id','=','nominas.idCatTypePayroll')
						->select('request_models.folio','nominas.title','users.name','users.last_name','users.scnd_last_name','cat_type_payrolls.description','real_employees.name as name_emp','real_employees.last_name as last_name_emp','real_employees.scnd_last_name as scnd_last_name_emp','nominas.idnomina','nomina_employees.idnominaEmployee','salaries.subsidyCaused','nomina_employees.from_date','nomina_employees.to_date','nomina_employees.idworkingData','enterprises.name as enterpriseName')
						->where('request_models.kind',16)
						->whereIn('request_models.status',[5,10,11,12])
						->where('request_models.taxPayment',1)
						->where('salaries.subsidyCaused','>',0)
						->where(function($query) use ($mindate,$maxdate,$employee,$title_request,$folio,$type,$enterprise)
						{
							if ($mindate != '' && $mindate != '') 
							{
								$query->where('nomina_employees.from_date',$mindate)
										->where('nomina_employees.to_date',$maxdate);
							}
							if ($employee != '') 
							{
								$query->whereIn('nomina_employees.idrealEmployee',$employee);
							}
							if ($title_request != '') 
							{
								$query->where('nominas.title','LIKE','%'.$title_request.'%');
							}
							if ($type != '' ) 
							{
								$query->where('nominas.idCatTypePayroll',$type);
							}
							if ($folio != '') 
							{
								$query->where('request_models.folio',$folio);
							}
							if ($enterprise != '') 
							{
								$query->whereIn('worker_datas.enterprise',$enterprise);
							}
						})
						->orderBy('request_models.folio','DESC')
						->paginate(25);
			return view('reporte.administracion.isr',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 195,
					'requests'      => $requests,
					'mindate'       => $request->mindate,
					'maxdate'       => $request->maxdate,
					'employee'      => $employee,
					'title_request' => $title_request,
					'type'          => $type,
					'folio'         => $folio,
					'enterprise'    => $enterprise
				]);
		}
	}

	public function isrExcel(Request $request)
	{
		if (Auth::user()->module->where('id',195)->count()>0) 
		{
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$employee       = $request->employee;
			$title_request  = $request->title_request;
			$folio          = $request->folio;
			$enterprise     = $request->enterprise;

			$requests = DB::table('request_models')
				->selectRaw('request_models.folio as folio,
					nominas.title as title,
					CONCAT_WS(" - ",nomina_employees.from_date,nomina_employees.to_date) AS periodicity,
					CONCAT_WS(" ",real_employees.last_name,real_employees.scnd_last_name,real_employees.name) as fullName,
					enterprises.name as enterpriseName,
					salaries.subsidyCaused as subsidyCaused
				')
				->leftJoin('nominas','nominas.idFolio','request_models.folio')
				->leftJoin('nomina_employees','nomina_employees.idnomina','nominas.idnomina')
				->leftJoin('real_employees','real_employees.id','nomina_employees.idrealEmployee')
				->leftJoin('worker_datas','worker_datas.id','nomina_employees.idworkingData')
				->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
				->leftJoin('salaries','salaries.idnominaEmployee','nomina_employees.idnominaEmployee')
				->where('request_models.kind',16)
				->whereIn('request_models.status',[5,10,11,12])
				->where('request_models.taxPayment',1)
				->where('salaries.subsidyCaused','>',0)
				->where(function($query) use ($mindate,$maxdate,$employee,$title_request,$folio,$enterprise)
				{
					if ($mindate != '' && $mindate != '') 
					{
						$query->where('nomina_employees.from_date',$mindate)->where('nomina_employees.to_date',$maxdate);
					}
					if ($employee != '') 
					{
						$query->whereIn('nomina_employees.idrealEmployee',$employee);
					}
					if ($title_request != '') 
					{
						$query->where('nominas.title','LIKE','%'.$title_request.'%');
					}
					if ($folio != '') 
					{
						$query->where('request_models.folio',$folio);
					}
					if ($enterprise!='') 
					{
						$query->whereIn('worker_datas.enterprise',$enterprise);
					}
				})
				->orderBy('request_models.folio','DESC')
				->get();


			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte ISR.xlsx');

			$headers        = ['Reporte de ISR Causado','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Título','Periodo','Empleado','Empresa','Subsidio Causado'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($requests as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['subsidyCaused']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r, $currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}

				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
	}
}
