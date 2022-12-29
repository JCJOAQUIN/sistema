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

class ReportAdministrationMovementController extends Controller
{
	private $module_id = 96;
	public function movementsReport(Request $request)
	{
		if (Auth::user()->module->where('id',187)->count()>0) 
		{
			$data			= App\Module::find($this->module_id);
			$mov			= $request->mov;
			$account		= $request->account;
			$enterpriseid	= $request->enterpriseid;
			$mindate		= $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$kind			= $request->kind;
			$conciliation	= $request->conciliation;

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

			$movements  = App\Movement::whereIn('idEnterprise',Auth::user()->inChargeEnt(187)->pluck('enterprise_id'))
							->where(function($query) use ($mindate,$maxdate,$mov,$enterpriseid,$account,$kind,$conciliation)
							{
								if ($mov != "") 
								{
									$query->where('description','LIKE','%'.$mov.'%');
								}
								if ($enterpriseid != "") 
								{
									$query->where('idEnterprise',$enterpriseid);
								}
								if ($account != "") 
								{
									$query->where('idAccount',$account);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('movementDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($conciliation != '' && $conciliation != 'all')
								{
									$query->where('statusConciliation',$conciliation);
								}
								if($kind != '')
								{
									$query->where(function($q) use($kind)
									{
										if(in_array('undefined', $kind))
										{
											$kindTemp = array_diff($kind, ['undefined']);
											$q->whereNull('movementType')
											->whereIn('movementType',$kindTemp);
										}
										else
										{
											$q->whereIn('movementType',$kind);
										}
									});
								}
							})
							->orderBy('movementDate','DESC')
							->paginate(10);

			return view('reporte.administracion.movimientos',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 187,
					'movements'		=> $movements,
					'enterpriseid'	=> $enterpriseid,
					'account'		=> $account,
					'mov'			=> $mov,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'kind'			=> $kind,
					'conciliation'	=> $conciliation
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function movementsExport(Request $request)
	{
		if (Auth::user()->module->where('id',187)->count()>0) 
		{
			$data           = App\Module::find($this->module_id);
			$mov            = $request->mov;
			$account        = $request->account;
			$enterpriseid   = $request->enterpriseid;
			$mindate        = $request->mindate!='' ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate        = $request->maxdate!='' ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$kind           = $request->kind;
			$conciliation   = $request->conciliation;
			$movements      = DB::table('movements')
							->selectRaw('
								enterprises.name as enterpriseName,
								CONCAT_WS(" ",accounts.account," ",accounts.description) as accountName,
								movements.movementType as movementType, 
								movements.description as movementDescription,
								DATE_FORMAT(movements.movementDate, "%d-%m-%Y") as movementDate,
								movements.commentaries as commentaries, 
								movements.amount as movementAmount
							')
							->leftJoin('enterprises','enterprises.id','movements.idEnterprise')
							->leftJoin('accounts','accounts.idAccAcc','movements.idAccount')
							->whereIn('movements.idEnterprise',Auth::user()->inChargeEnt(187)->pluck('enterprise_id'))
							->where(function($query) use ($mindate,$maxdate,$mov,$enterpriseid,$account,$kind,$conciliation)
							{
								if ($mov != "") 
								{
									$query->where('movements.description','LIKE','%'.$mov.'%');
								}
								if ($enterpriseid != "") 
								{
									$query->where('movements.idEnterprise',$enterpriseid);
								}
								if ($account != "") 
								{
									$query->where('movements.idAccount',$account);
								}
								if($mindate != "" && $maxdate != "")
								{
									$query->whereBetween('movements.movementDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
								}
								if($conciliation != '' && $conciliation != 'all')
								{
									$query->where('movements.statusConciliation',$conciliation);
								}
								if($kind != '')
								{
									$query->where(function($q) use($kind)
									{
										if(in_array('undefined', $kind))
										{
											$kindTemp = array_diff($kind, ['undefined']);
											$q->whereNull('movements.movementType')
											->whereIn('movements.movementType',$kindTemp);
										}
										else
										{
											$q->whereIn('movements.movementType',$kind);
										}
									});
								}
							})
							->orderBy('movements.movementDate','DESC')
							->get();
							
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->setCellAlignment(CellAlignment::CENTER)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de Movimientos.xlsx');

			$headers        = ['Reporte de Movimientos','','','','','',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Empresa','Clasificación del gasto','Tipo de movimiento','Descripción','Fecha','Comentarios','Importe'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($movements as $movement)
			{
				$tmpArr = [];
				foreach($movement as $k => $r)
				{
					if(in_array($k,['movementAmount']))
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
		else
		{
			return redirect('/');
		}
	}

	public function movementsDetail(Request $request)
	{
		if ($request->ajax()) 
		{
			$movement = App\Movement::find($request->idmovement);
			return view('reporte.administracion.partial.modal_movimiento')->with('movement',$movement);
		}
	}
}
