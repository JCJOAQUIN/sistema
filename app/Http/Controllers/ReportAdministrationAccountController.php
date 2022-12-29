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

class ReportAdministrationAccountController extends Controller
{
	private $module_id = 96;
	public function accountsReport(Request $request)
	{
		if (Auth::user()->module->where('id',115)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$idEnterprise = $request->idEnterprise;
			$account    = $request->account;
			$accounts   = App\Account::where(function ($query) use ($account,$idEnterprise)
				{
					if(!empty($account))
					{
						$query->whereIn('idAccAcc',$account);
					}
					if ($idEnterprise != "")
					{
						$query->where('idEnterprise',$idEnterprise);
					}
				})
				->orderBy('account','ASC')
				->paginate(15);
			return view('reporte.administracion.cuentas',
				[
					'id'         => $data['father'],
					'title'      => $data['name'],
					'details'    => $data['details'],
					'child_id'   => $this->module_id,
					'option_id'  => 115,
					'idEnterprise' => $idEnterprise,
					'account'    => $account,
					'accounts'   => $accounts
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function accountsExcel(Request $request)
	{
		if (Auth::user()->module->where('id',115)->count()>0)
		{
			$idEnterprise = $request->idEnterprise;
			$account      = $request->account;
			$accounts     = App\Account::selectRaw('
				CONCAT_WS(" ",account,description) as nameAccount,
				balance,
				selectable,
				identifier
			')
			->where(function ($query) use ($account,$idEnterprise)
			{
				if($account != "")
				{
						$query->whereIn('idAccAcc',$account);
				}
				if ($idEnterprise != "")
				{
					$query->where('idEnterprise',$idEnterprise);
				}
			})
			->orderBy('account','ASC')
			->get();
			
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->build();
			$smStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$smStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$smStyleCol3    = (new StyleBuilder())->setFontSize(14)->setFontColor(Color::RED)->build();
			$smStyleCol4    = (new StyleBuilder())->setFontSize(16)->setFontColor(Color::RED)->build();

			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de cuentas.xlsx');

			$headers        = ['Reporte de cuentas',''];
			$tempHeaders    = [];
			foreach($headers as $k => $header)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($header,$smStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeaders     = ['Cuenta','Balance'];
			$tempSubHeader  = [];
			foreach($subHeaders as $k => $subHeader)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($subHeader,$smStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($accounts as $account)
			{
				$row = [];

				if ($account->selectable == 0 && $account->identifier == 1) 
				{
					$row[] = WriterEntityFactory::createCell($account->nameAccount,$smStyleCol4);
					$row[] = WriterEntityFactory::createCell($account->balance,$smStyleCol4);
				}
				elseif ($account->selectable == 0 && $account->identifier != 1) 
				{
					$row[] = WriterEntityFactory::createCell($account->nameAccount,$smStyleCol3);
					$row[] = WriterEntityFactory::createCell($account->balance,$smStyleCol3);
				}
				else
				{
					$row[] = WriterEntityFactory::createCell($account->nameAccount);
					$row[] = WriterEntityFactory::createCell($account->balance);
				}
				$rowFromValues = WriterEntityFactory::createRow($row);
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
