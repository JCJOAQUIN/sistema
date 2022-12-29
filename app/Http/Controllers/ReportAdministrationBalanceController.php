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

class ReportAdministrationBalanceController extends Controller
{
	private $module_id = 96;
	public function balanceReport(Request $request)
	{
		if (Auth::user()->module->where('id',101)->count()>0)
		{
			$name  = $request->name;
			$box   = $request->box;
			$users = App\User::where(function($q) use ($name, $box)
				{
					if ($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($box != '' && $box != 'all')
					{
						$q->where('cash',$box);
					}
				})
				->orderBy('id','ASC')
				->orderBy('cash_amount','ASC')
				->paginate(15);
			
			$data = App\Module::find($this->module_id);
			return view('reporte.administracion.saldos',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 101,
					'name'      => $name,
					'users'     => $users,
					'box'       => $box
				]);
		}
	}

	public function balanceExcel(Request $request)
	{
		if (Auth::user()->module->where('id',101)->count()>0)
		{
			$name    = $request->name;
			$box     = $request->box;
			$results = DB::table('users')
				->selectRaw(
					'users.id as user,
					SUM(IF(request_models.kind = 8, resources.total, 0)) as resource_total,
					SUM(IF(request_models.kind = 8, p.payment_amount, 0)) as resource_paid,
					(SUM(IF(request_models.kind = 8, resources.total, 0)) - SUM(IF(request_models.kind = 8, p.payment_amount, 0))) as resource_unpaid,
					SUM(IF(request_models.kind = 3, expenses.total, 0)) as expenses_total,
					SUM(IF(request_models.kind = 3, expenses.reembolso,0)) as expenses_refund_to_pay,
					SUM(IF(request_models.kind = 3 AND expenses.reembolso > 0, p.payment_amount, 0)) as expenses_refund_paid,
					SUM(IF(request_models.kind = 3, expenses.reintegro,0)) as expenses_reinstate_to_pay,
					SUM(IF(request_models.kind = 3 AND expenses.reintegro > 0, p.payment_amount, 0)) as expenses_reinstate_paid,
					(
						SUM(IF(request_models.kind = 8, resources.total, 0)) -
						SUM(IF(request_models.kind = 3, expenses.total, 0)) +
						SUM(IF(request_models.kind = 3, expenses.reembolso,0)) -
						SUM(IF(request_models.kind = 3, expenses.reintegro,0))
					) as to_check,
					GROUP_CONCAT(IF(request_models.kind = 8 AND comp.resourceId IS NULL, request_models.folio, NULL) SEPARATOR ", ") as folios,
					SUM(IF(request_models.kind = 9, refunds.total, 0)) as refund_total,
					SUM(IF(request_models.kind = 9, p.payment_amount, 0)) as refund_paid,
					GROUP_CONCAT(IF(request_models.kind = 9 AND refunds.total < p.payment_amount,request_models.folio, NULL) SEPARATOR ", ") as refund_paid_more,
					(SUM(IF(request_models.kind = 9, refunds.total, 0)) - SUM(IF(request_models.kind = 9, p.payment_amount, 0))) as refund_unpaid
					'
				)
				->leftJoin('request_models','users.id','request_models.idRequest')
				->leftJoin('resources','request_models.folio','resources.idFolio')
				->leftJoin('expenses','request_models.folio','expenses.idFolio')
				->leftJoin('refunds','request_models.folio','refunds.idFolio')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'), function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->leftJoin(
					DB::raw('(SELECT exp.resourceId FROM request_models as rq LEFT JOIN expenses as exp ON rq.folio = exp.idFolio WHERE rq.kind = 3 AND rq.status IN(5,10,11,12,18)) as comp'),
					'request_models.folio',
					'comp.resourceId'
				)
				->where(function($q)
				{
					$q->whereIn('request_models.kind',[3,8,9])
						->whereIn('request_models.status',[5,10,11,12,18]);
				})
				->where(function($q) use ($name, $box)
				{
					if ($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($box != '' && $box != 'all')
					{
						$q->where('cash',$box);
					}
				})
				->groupBy('request_models.idRequest')
				->orderBy('request_models.idRequest')
				->get();
			$users   = App\User::where(function($q) use ($name, $box)
				{
					if ($name != "")
					{
						$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%');
					}
					if($box != '' && $box != 'all')
					{
						$q->where('cash',$box);
					}
				})
				->get();
			$allBorders = (new BorderBuilder())
				->setBorderTop('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderBottom('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$startBorder = (new BorderBuilder())
				->setBorderTop('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight('D4D4D5', Border::WIDTH_THIN, Border::STYLE_NONE)
				->setBorderBottom('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->build();
			$middleBorder = (new BorderBuilder())
				->setBorderTop('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight('D4D4D5', Border::WIDTH_THIN, Border::STYLE_NONE)
				->setBorderBottom('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft('D4D4D5', Border::WIDTH_THIN, Border::STYLE_NONE)
				->build();
			$endBorder = (new BorderBuilder())
				->setBorderTop('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderRight('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderBottom('D4D4D5', Border::WIDTH_THIN, Border::STYLE_SOLID)
				->setBorderLeft('D4D4D5', Border::WIDTH_THIN, Border::STYLE_NONE)
				->build();
			$noneBorder = (new BorderBuilder())
				->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_NONE)
				->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_NONE)
				->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_NONE)
				->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_NONE)
				->build();
			$defaultStyle  = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->setBorder($allBorders)->build();
			$writer        = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Saldos.xlsx');
			$mhStyle     = [
				(new StyleBuilder())->setBackgroundColor('FFFFFF')->setFontColor(Color::BLACK)->setFontBold()->setBorder($noneBorder)->build(),
				[
					(new StyleBuilder())->setBackgroundColor('F4B23D')->setFontColor(Color::WHITE)->setFontBold()->setBorder($startBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('F4B23D')->setFontColor(Color::WHITE)->setFontBold()->setBorder($middleBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('F4B23D')->setFontColor(Color::WHITE)->setFontBold()->setBorder($endBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('D4792F')->setFontColor(Color::WHITE)->setFontBold()->setBorder($startBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('D4792F')->setFontColor(Color::WHITE)->setFontBold()->setBorder($middleBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('D4792F')->setFontColor(Color::WHITE)->setFontBold()->setBorder($endBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('7EAB55')->setFontColor(Color::WHITE)->setFontBold()->setBorder($startBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('7EAB55')->setFontColor(Color::WHITE)->setFontBold()->setBorder($middleBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('7EAB55')->setFontColor(Color::WHITE)->setFontBold()->setBorder($endBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('B13A1E')->setFontColor(Color::WHITE)->setFontBold()->setBorder($startBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('B13A1E')->setFontColor(Color::WHITE)->setFontBold()->setBorder($middleBorder)->build(),
					(new StyleBuilder())->setBackgroundColor('B13A1E')->setFontColor(Color::WHITE)->setFontBold()->setBorder($endBorder)->build()
				],
				[
					(new StyleBuilder())->setBackgroundColor('FDF2CF')->setFontColor(Color::BLACK)->setFontBold()->setCellAlignment(CellAlignment::CENTER)->setBorder($allBorders)->build(),
					(new StyleBuilder())->setBackgroundColor('F9E5D8')->setFontColor(Color::BLACK)->setFontBold()->setCellAlignment(CellAlignment::CENTER)->setBorder($allBorders)->build(),
					(new StyleBuilder())->setBackgroundColor('E4EFDC')->setFontColor(Color::BLACK)->setFontBold()->setCellAlignment(CellAlignment::CENTER)->setBorder($allBorders)->build(),
					(new StyleBuilder())->setBackgroundColor('F9DFD5')->setFontColor(Color::BLACK)->setFontBold()->setCellAlignment(CellAlignment::CENTER)->setBorder($allBorders)->build()
				]
			];
			$darkRow        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$mainHeaderArr  = [
				['','','','','','','','','Reporte de saldos de usuarios','','','','','','','',''],
				['','Usuario','','','Reembolso','','','','','Asignación de recurso','','','','','Comprobación de gasto','',''],
				['ID','Nombre','Caja chica','Autorizado','Pagado','Por pagar','Folios con pagos mayores','Autorizado','Pagado','Por pagar','Por comprobar','Folios por comprobar','Autorizado','Por reembolsar','Reembolsado','Por reintegrar','Reintegrado']
			];
			foreach ($mainHeaderArr as $key => $header)
			{
				if($key == 0)
				{
					$row = WriterEntityFactory::createRowFromArray($header, $mhStyle[$key]);
				}
				elseif($key == 1)
				{
					$tmpMHArr = [];
					foreach($header as $k => $h)
					{
						switch(true)
						{
							case $k == 0:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][0]);
								break;
							case $k < 2:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][1]);
								break;
							case $k == 2:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][2]);
								break;
							case $k == 3:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][3]);
								break;
							case $k < 6:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][4]);
								break;
							case $k == 6:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][5]);
								break;
							case $k == 7:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][6]);
								break;
							case $k < 11:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][7]);
								break;
							case $k == 11:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][8]);
								break;
							case $k == 12:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][9]);
								break;
							case $k < 16:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][10]);
								break;
							case $k == 16:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][11]);
								break;
						}
					}
					$row = WriterEntityFactory::createRow($tmpMHArr);
				}
				else
				{
					$tmpMHArr = [];
					foreach($header as $k => $h)
					{
						switch(true)
						{
							case $k <= 2:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][0]);
								break;
							case $k <= 6:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][1]);
								break;
							case $k <= 11:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][2]);
								break;
							case $k <= 16:
								$tmpMHArr[] = WriterEntityFactory::createCell($h,$mhStyle[$key][3]);
								break;
						}
					}
					$row = WriterEntityFactory::createRow($tmpMHArr);
				}
				$writer->addRow($row);
			}
			$rowChange = true;
			foreach($users as $user)
			{
				$tmpArr = [];
				$tmpArr[] = WriterEntityFactory::createCell($user->id);
				$tmpArr[] = WriterEntityFactory::createCell($user->fullName());
				if($user->cash == 1)
				{
					$tmpArr[] = WriterEntityFactory::createCell((double)$user->cash_amount, $currencyFormat);
				}
				else
				{
					$tmpArr[] = WriterEntityFactory::createCell('--');
				}
				$userData = $results->where('user',$user->id)->first();
				if($userData != '')
				{
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->refund_total, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->refund_paid, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->refund_unpaid, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell($userData->refund_paid_more);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->resource_total, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->resource_paid, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->resource_unpaid, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->to_check, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell($userData->folios);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->expenses_total, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->expenses_refund_to_pay, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->expenses_refund_paid, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->expenses_reinstate_to_pay, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell((double)$userData->expenses_reinstate_paid, $currencyFormat);
				}
				else
				{
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell('');
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell('');
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
					$tmpArr[] = WriterEntityFactory::createCell(0, $currencyFormat);
				}
				if($rowChange)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$darkRow);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr);
				}
				$rowChange = !$rowChange;
				$writer->addRow($rowFromValues);
			}
			$sheet = $writer->getCurrentSheet();
			$sheet->setName('Saldos');
			return $writer->close();
		}
	}

	public function balanceDetail(Request $request)
	{
		if (Auth::user()->module->where('id',101)->count()>0)
		{
			$user   = App\User::find($request->id);
			$result = DB::table('request_models')
				->selectRaw(
					'SUM(IF(request_models.kind = 8, resources.total, 0)) as resource_total,
					SUM(IF(request_models.kind = 8, p.payment_amount, 0)) as resource_paid,
					(SUM(IF(request_models.kind = 8, resources.total, 0)) - SUM(IF(request_models.kind = 8, p.payment_amount, 0))) as resource_unpaid,
					SUM(IF(request_models.kind = 3, expenses.total, 0)) as expenses_total,
					SUM(IF(request_models.kind = 3, expenses.reembolso,0)) as expenses_refund_to_pay,
					SUM(IF(request_models.kind = 3 AND expenses.reembolso > 0, p.payment_amount, 0)) as expenses_refund_paid,
					SUM(IF(request_models.kind = 3, expenses.reintegro,0)) as expenses_reinstate_to_pay,
					SUM(IF(request_models.kind = 3 AND expenses.reintegro > 0, p.payment_amount, 0)) as expenses_reinstate_paid,
					(
						SUM(IF(request_models.kind = 8, resources.total, 0)) -
						SUM(IF(request_models.kind = 3, expenses.total, 0)) +
						SUM(IF(request_models.kind = 3, expenses.reembolso,0)) -
						SUM(IF(request_models.kind = 3, expenses.reintegro,0))
					) as to_check,
					GROUP_CONCAT(IF(request_models.kind = 8 AND comp.resourceId IS NULL, request_models.folio, NULL) SEPARATOR ", ") as folios,
					SUM(IF(request_models.kind = 9, refunds.total, 0)) as refund_total,
					SUM(IF(request_models.kind = 9, p.payment_amount, 0)) as refund_paid,
					GROUP_CONCAT(IF(request_models.kind = 9 AND refunds.total < p.payment_amount,request_models.folio, NULL) SEPARATOR ", ") as refund_paid_more,
					(SUM(IF(request_models.kind = 9, refunds.total, 0)) - SUM(IF(request_models.kind = 9, p.payment_amount, 0))) as refund_unpaid
					'
				)
				->leftJoin('resources','request_models.folio','resources.idFolio')
				->leftJoin('expenses','request_models.folio','expenses.idFolio')
				->leftJoin('refunds','request_models.folio','refunds.idFolio')
				->leftJoin(
					DB::raw('(SELECT idFolio, idKind, SUM(amount) as payment_amount FROM payments GROUP BY idFolio, idKind) AS p'), function($q)
					{
						$q->on('request_models.folio','=','p.idFolio')
						->on('request_models.kind','=','p.idKind');
					}
				)
				->leftJoin(
					DB::raw('(SELECT exp.resourceId FROM request_models as rq LEFT JOIN expenses as exp ON rq.folio = exp.idFolio WHERE rq.kind = 3 AND rq.status IN(5,10,11,12,18)) as comp'),
					'request_models.folio',
					'comp.resourceId'
				)
				->where('request_models.idRequest',$request->id)
				->where(function($q)
				{
					$q->whereIn('request_models.kind',[3,8,9])
						->whereIn('request_models.status',[5,10,11,12,18]);
				})
				->first();
			return view('reporte.administracion.saldos_info',['result' => $result, 'user' => $user]);
		}
	}
}
