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

class ReportAdministrationGroupComissionsController extends Controller
{
	private $module_id = 96;
	public function groupCommissions(Request $request)
	{
		if (Auth::user()->module->where('id',196)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$title_request  = $request->title_request;
			$folio          = $request->folio;
			$operation      = $request->operation;

			$requests = App\RequestModel::where('request_models.kind',14)
						->where(function($query) use ($title_request,$folio,$operation)
						{
							if ($folio != '') 
							{
								$query->where('request_models.folio',$folio);
							}
							if($title_request != '')
							{
								$query->whereHas('groups',function($q) use ($title_request)
								{
									$q->where('title','LIKE','%'.$title_request.'%');
								});
							}
							if($operation != '')
							{
								$query->whereHas('groups',function($q) use ($operation)
								{
									$q->whereIn('operationType',$operation);
								});
							}
						})
						->orderBy('request_models.folio','DESC')
						->paginate(25);
			return view('reporte.administracion.comisiones_grupos',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 196,
					'requests'      => $requests,
					'title_request' => $title_request,
					'folio'         => $folio,
					'operation'     => $operation
				]);
		}
	}

	public function groupCommissionsExcel(Request $request)
	{
		if (Auth::user()->module->where('id',196)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$title_request  = $request->title_request;
			$folio          = $request->folio;
			$operation      = $request->operation;

			$requests = DB::table('request_models')
						->selectRaw('
							request_models.folio as folio,
							CONCAT_WS(" - ",groups.title,groups.datetitle) as title,
							groups.operationType as operationType,
							groups.amountMovement as amountMovement,
							groups.commission as commission,
							groups.amountRetake as amountRetake
						')
						->leftJoin('groups','groups.idFolio','request_models.folio')
						->where('request_models.kind',14)
						->where(function($query) use ($title_request,$folio,$operation)
						{
							if ($folio != '') 
							{
								$query->where('request_models.folio',$folio);
							}
							if($title_request != '')
							{
								$query->where('groups.title','LIKE','%'.$title_request.'%');
							}
							if($operation != '')
							{
								$query->whereIn('groups.operationType',$operation);
							}
						})
						->orderBy('request_models.folio','DESC')
						->get();    

			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('1d353d')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte Comisiones de Grupos.xlsx');

			$headers        = ['Reporte de Comisiones de Grupos','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Folio','Título','Tipo de operación','Importe total','Comisión','Importe a retomar'];
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
				foreach($request as $key => $value)
				{
					if(in_array($key,['amountMovement','commission','amountRetake']))
					{
						if($value != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$value, $currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($value);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($value);
					}
				}

				$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
	}
}
