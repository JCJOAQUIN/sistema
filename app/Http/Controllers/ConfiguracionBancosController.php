<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Auth;
use Lang;
use Excel;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionBancosController extends Controller
{
	private $module_id = 135;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if(Auth::user()->module->where('id',136)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$enterprises	= App\Enterprise::where('status','ACTIVE')->get();
			return view('configuracion.bancos.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 136,
					'enterprises'	=> $enterprises,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$banks               = new App\BanksAccounts;
			$banks->idBanks      = $request->bank_id;
			$banks->alias        = $request->alias;
			$banks->account      = $request->account;
			$banks->branch       = $request->branch;
			$banks->reference    = $request->reference;
			$banks->clabe        = $request->clabe;
			$banks->currency     = $request->currency;
			$banks->agreement    = $request->agreement;
			$banks->idEnterprise = $request->enterprise_id;
			$banks->idAccAcc     = $request->account_id;
			$banks->save();
			$alert 	= "swal('', '".Lang::get('messages.record_created')."', 'success');";
			return redirect('configuration/banks')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function validateAccount(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			if ($request->ajax())
			{
				$response = array(
					'valid'   => false,
					'message' => 'El campo es requerido.'
				);
				if(isset($request->account))
				{
					if(preg_match("/^(\d{5,15})$/i", $request->account))
					{
						$exists = App\BanksAccounts::where('idBanks', $request->bank_id)
							->where('account', $request->account)
							->where(function($q) use($request)
							{
								if(isset($request->oldAccount))
								{
									$q->where('idbanksAccounts','!=',$request->oldAccount);
								}
							})
							->count();
						if ($exists > 0)
						{
							$response = array(
								'valid'		=> false,
								'message'	=> 'La cuenta ya ha sido registrada anteriormente.'
							);
						}
						else
						{
							$response = array(
								'valid'   => true,
								'message' => ''
							);
						}
					}
					else
					{
						$response = array(
							'valid'		=> false,
							'message'	=> 'La cuenta debe ser entre 5 y 15 dígitos.'
						);
					}
				}
				return Response($response);
			}
		}
	}

	public function validateClabe(Request $request)
	{
		if ($request->ajax())
		{
			$response = array(
				'valid'   => false,
				'message' => 'El campo es requerido.'
			);
			if($request->clabe != "")
			{
				if(preg_match("/^(\d{18})$/i",$request->clabe))
				{
					$clabes = App\BanksAccounts::where('clabe', $request->clabe)
						->where(function($q) use($request)
						{
							if(isset($request->oldClabe))
							{
								$q->where('idbanksAccounts','!=',$request->oldClabe);
							}
						})
						->get();
					if(count($clabes) > 0) 
					{
						$response['message'] = 'La CLABE ya se encuentra registrada.';
					} 
					else 
					{
						$response['valid']   = true;
						$response['message'] = '';
					}
				}
				else
				{
					$response['message'] = 'La CLABE ingresada es inválida.';
				}
			}
			return Response($response);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',137)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$account	= $request->account_id;
			$bank		= $request->bank_id;
			$enterprise	= $request->enterprise_id;

			$banksAccounts = App\BanksAccounts::where(function($query) use($account,$enterprise,$bank)
			{
				if ($account != '') 
				{
					$query->where('idAccAcc',$account);
				}
				if ($enterprise != '') 
				{
					$query->where('idEnterprise',$enterprise);
				}
				if ($bank != '') 
				{
					$query->where('idBanks',$bank);
				}
			})
			->orderBy('idbanksAccounts', 'DESC')
			->paginate(10);
			return response(
				view('configuracion.bancos.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 137,
					'bank'			=> $bank,
					'account'		=> $account, 
					'enterprise'	=> $enterprise,
					'banksAccounts' => $banksAccounts
				])
			)->cookie(
				"urlSearch", storeUrlCookie(137), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',137)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$enterprises    = App\Enterprise::where('status','ACTIVE')->get();
			$accountBank 	= App\BanksAccounts::find($id);
			return view('configuracion.bancos.cambio',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->module_id,
					'option_id'     => 137,
					'enterprises'   => $enterprises,
					'accountBank' 	=> $accountBank
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$banks               = App\BanksAccounts::find($id);
			$banks->idBanks      = $request->bank_id;
			$banks->alias        = $request->alias;
			$banks->account      = $request->account;
			$banks->branch       = $request->branch;
			$banks->reference    = $request->reference;
			$banks->clabe        = $request->clabe;
			$banks->currency     = $request->currency;
			$banks->agreement    = $request->agreement;
			$banks->idEnterprise = $request->enterprise_id;
			$banks->idAccAcc     = $request->account_id;
			$banks->save();
			$alert 	= "swal('', '".Lang::get('messages.record_updated')."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function export(Request $request)
	{
		if (Auth::user()->module->where('id', $this->module_id)->count() > 0) 
		{
			$data		= App\Module::find($this->module_id);
			$account    = $request->account_id;
			$bank	    = $request->bank_id;
			$enterprise	= $request->enterprise_id;

			$banksAccounts = DB::table('banks_accounts')->selectRaw(
							'
								enterprises.name,
								banks.description,
								CONCAT(accounts.account, " - ", accounts.description, " (",accounts.content,")") as accountName,
								banks_accounts.alias,
								banks_accounts.account,
								banks_accounts.clabe,
								banks_accounts.branch,
								banks_accounts.reference,
								banks_accounts.currency,
								banks_accounts.agreement
							')
							->leftJoin('enterprises', 'enterprises.id','banks_accounts.idEnterprise')
							->leftJoin('banks', 'banks.idBanks', 'banks_accounts.idBanks')
							->leftJoin('accounts', 'accounts.idAccAcc', 'banks_accounts.idAccAcc')
							->where(function ($query) use ($account, $enterprise, $bank) {
								if ($account != '') {
									$query->where('banks_accounts.idAccAcc', $account);
								}
								if ($enterprise != '') {
									$query->where('banks_accounts.idEnterprise', $enterprise);
								}
								if ($bank != '') {
									$query->where('banks_accounts.idBanks', $bank);
								}
							})
							->get();
		
			if(count($banksAccounts)==0 || $banksAccounts==null)
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('000000')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('104f64')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte de bancos.xlsx');
			$writer->getCurrentSheet()->setName('Relacion de bancos');
			
			$headers = ['Relacion Bancos','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['Empresa','Banco','Clasificación Gasto','Alias','Cuenta','CLABE','Sucursal','Referencia','Moneda','Convenio'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			foreach($banksAccounts as $request)
			{
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k, ['total', 'conceptTotal', 'conceptAmount', 'conceptTax', 'conceptTaxes']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r,$currencyFormat);
						}
					}
					else
					{
						$tmpArr[] = WriterEntityFactory::createCell($r);
					}
				}
				$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
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
