<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use App;
use App\Functions\Files;
use App\OtherIncome;
use Illuminate\Support\Facades\Mail;
use Excel;
use Illuminate\Support\Facades\App as FacadesApp;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class AdministrationOtherIncomeController extends Controller
{
	private $module_id = 246;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id' 		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function create()
	{
		if (Auth::user()->module->where('id',247)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('administracion.otros_ingresos.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 247
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function store(Request $request)
	{
		if (Auth::user()->module->where('id',247)->count()>0) 
		{
			$new_request = App\RequestModel::create(
				[
					'idEnterprise'	=> $request->enterprise_id,
					'idProject'		=> $request->project_id,
					'PaymentDate'	=> $request->payment_date,
					'kind'			=> 20,
					'taxPayment'	=> $request->taxPayment,
					'fDate'			=> Carbon::now(),
					'status'		=> 3,
					'idRequest'		=> $request->request_id,
					'idElaborate'	=> Auth::user()->id,
				]);

			$new_income = App\OtherIncome::create(
				[
					'title'				=> $request->title,
					'datetitle'			=> $request->datetitle,
					'idbanksAccounts'	=> $request->idbanksAccounts,
					'type_income'		=> $request->type_income,
					'subtotal'			=> $request->subtotal,
					'total_iva'			=> $request->total_iva,
					'total_taxes'		=> $request->total_taxes,
					'total_retentions'	=> $request->total_retentions,
					'total'				=> $request->total,
					'type_currency'		=> $request->type_currency,
					'pay_mode'			=> $request->pay_mode,
					'status_bill'		=> $request->status_bill,
					'reference'			=> $request->reference,
					'idFolio'			=> $new_request->folio,
					'idKind'			=> $new_request->kind,
					'borrower' 			=> $request->borrower,
				]);

			if (isset($request->deleteConcepts) && count($request->deleteConcepts)>0) 
			{
				App\OtherIncomeDetail::whereIn('id',$request->deleteConcepts[])->delete();
				App\OtherIncomeDetailTaxes::whereIn('idOtherIncomeDetail',$request->deleteConcepts[])->delete();
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$new_request->folio);
					$documents 		= App\OtherIncomeDocuments::create(
						[
							'path'			=> $new_file_name,
							'idOtherIncome'	=> $new_income->id,
							'name'			=> $request->nameDocument[$i],
						]);
				}
			}

			if (isset($request->quantity_data) && count($request->quantity_data)>0) 
			{
				for ($i=0; $i < count($request->quantity_data); $i++)
				{
					$new_detail	= App\OtherIncomeDetail::create(
						[
							'idOtherIncome'		=> $new_income->id,
							'quantity'			=> $request->quantity_data[$i],
							'unit'				=> $request->unit_data[$i],
							'description'		=> $request->description_data[$i],
							'unit_price'		=> $request->unit_price_data[$i],
							'tax'				=> $request->tax_data[$i],
							'type_tax'			=> $request->type_tax_data[$i],
							'subtotal'			=> $request->subtotal_data[$i],
							'total_retentions'	=> $request->total_retentions_data[$i],
							'total_taxes'		=> $request->total_taxes_data[$i],
							'total'				=> $request->total_data[$i],
						]);
					

					$t_nameTax		= 't_nameTax'.$i;
					$t_amountTax	= 't_amountTax'.$i;
					if (isset($request->$t_amountTax) && count($request->$t_amountTax)>0) 
					{
						for ($d=0; $d < count($request->$t_amountTax); $d++) 
						{ 
							if ($request->$t_amountTax[$d] != "") 
							{
								$new_tax = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameTax[$d],
									'total'					=> $request->$t_amountTax[$d],
									'type'					=> 'I',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}

					$t_nameRetention 	= 't_nameRetention'.$i;
					$t_amountRetention 	= 't_amountRetention'.$i;
					if (isset($request->$t_amountRetention) && count($request->$t_amountRetention)>0) 
					{
						for ($d=0; $d < count($request->$t_amountRetention); $d++) 
						{ 
							if ($request->$t_amountRetention[$d] != "") 
							{
								$new_retention = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameRetention[$d],
									'total'					=> $request->$t_amountRetention[$d],
									'type'					=> 'R',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}
				}
			}
			$alert = 'swal("","Solicitud Enviada Exitosamente","success");';
			return redirect()->route('other-income.edit')->with('alert',$alert);
		}
	}

	public function save(Request $request)
	{
		if (Auth::user()->module->where('id',247)->count()>0) 
		{

			$new_request = App\RequestModel::create(
				[
					'idEnterprise'	=> $request->enterprise_id,
					'idProject'		=> $request->project_id,
					'PaymentDate'	=> $request->payment_date,
					'kind'			=> 20,
					'taxPayment'	=> $request->taxPayment,
					'fDate'			=> Carbon::now(),
					'status'		=> 2,
					'idRequest'		=> $request->request_id,
					'idElaborate'	=> Auth::user()->id,
				]);

			$new_income = App\OtherIncome::create(
				[
					'title'				=> $request->title,
					'datetitle'			=> $request->datetitle,
					'idbanksAccounts'	=> $request->idbanksAccounts,
					'type_income'		=> $request->type_income,
					'subtotal'			=> $request->subtotal,
					'total_iva'			=> $request->total_iva,
					'total_taxes'		=> $request->total_taxes,
					'total_retentions'	=> $request->total_retentions,
					'total'				=> $request->total,
					'type_currency'		=> $request->type_currency,
					'pay_mode'			=> $request->pay_mode,
					'status_bill'		=> $request->status_bill,
					'reference'			=> $request->reference,
					'idFolio'			=> $new_request->folio,
					'idKind'			=> $new_request->kind,
					'borrower' 			=> $request->borrower,
				]);

			if (isset($request->deleteConcepts) && count($request->deleteConcepts)>0) 
			{
				App\OtherIncomeDetail::whereIn('id',$request->deleteConcepts[])->delete();
				App\OtherIncomeDetailTaxes::whereIn('idOtherIncomeDetail',$request->deleteConcepts[])->delete();
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$new_request->folio);
					$documents 		= App\OtherIncomeDocuments::create(
						[
							'path'			=> $new_file_name,
							'idOtherIncome'	=> $new_income->id,
							'name'			=> $request->nameDocument[$i],
						]);
				}
			}

			if (isset($request->quantity_data) && count($request->quantity_data)>0) 
			{
				for ($i=0; $i < count($request->quantity_data); $i++)
				{
					$new_detail	= App\OtherIncomeDetail::create(
						[
							'idOtherIncome'		=> $new_income->id,
							'quantity'			=> $request->quantity_data[$i],
							'unit'				=> $request->unit_data[$i],
							'description'		=> $request->description_data[$i],
							'unit_price'		=> $request->unit_price_data[$i],
							'tax'				=> $request->tax_data[$i],
							'type_tax'			=> $request->type_tax_data[$i],
							'subtotal'			=> $request->subtotal_data[$i],
							'total_retentions'	=> $request->total_retentions_data[$i],
							'total_taxes'		=> $request->total_taxes_data[$i],
							'total'				=> $request->total_data[$i],
						]);
					

					$t_nameTax		= 't_nameTax'.$i;
					$t_amountTax	= 't_amountTax'.$i;
					if (isset($request->$t_amountTax) && count($request->$t_amountTax)>0) 
					{
						for ($d=0; $d < count($request->$t_amountTax); $d++) 
						{ 
							if ($request->$t_amountTax[$d] != "") 
							{
								$new_tax = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameTax[$d],
									'total'					=> $request->$t_amountTax[$d],
									'type'					=> 'I',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}

					$t_nameRetention 	= 't_nameRetention'.$i;
					$t_amountRetention 	= 't_amountRetention'.$i;
					if (isset($request->$t_amountRetention) && count($request->$t_amountRetention)>0) 
					{
						for ($d=0; $d < count($request->$t_amountRetention); $d++) 
						{ 
							if ($request->$t_amountRetention[$d] != "") 
							{
								$new_retention = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameRetention[$d],
									'total'					=> $request->$t_amountRetention[$d],
									'type'					=> 'R',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}
				}
			}
			$alert = 'swal("","Solicitud Guardada Exitosamente","success");';
			return redirect()->route('other-income.edit-income',['request'=>$new_request->folio])->with('alert',$alert);
		}
	}

	public function saveUpdate(App\RequestModel $t_request,Request $request)
	{

		if (Auth::user()->module->where('id',248)->count()>0) 
		{
			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idProject		= $request->project_id;
			$t_request->PaymentDate		= $request->payment_date;
			$t_request->kind			= 20;
			$t_request->taxPayment		= $request->taxPayment;
			$t_request->status			= 2;
			$t_request->idRequest		= $request->request_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			
			$t_request->otherIncome->title				= $request->title;
			$t_request->otherIncome->datetitle			= $request->datetitle;
			$t_request->otherIncome->idbanksAccounts	= $request->idbanksAccounts;
			$t_request->otherIncome->type_income		= $request->type_income;
			$t_request->otherIncome->subtotal			= $request->subtotal;
			$t_request->otherIncome->total_iva			= $request->total_iva;
			$t_request->otherIncome->total_taxes		= $request->total_taxes;
			$t_request->otherIncome->total_retentions	= $request->total_retentions;
			$t_request->otherIncome->total				= $request->total;
			$t_request->otherIncome->type_currency		= $request->type_currency;
			$t_request->otherIncome->pay_mode			= $request->pay_mode;
			$t_request->otherIncome->status_bill		= $request->status_bill;
			$t_request->otherIncome->reference			= $request->reference;
			$t_request->otherIncome->idFolio			= $t_request->folio;
			$t_request->otherIncome->idKind				= $t_request->kind;
			$t_request->otherIncome->borrower			= $request->borrower;
			$t_request->otherIncome->save();

			if (isset($request->deleteConcepts) && count($request->deleteConcepts)>0) 
			{
				App\OtherIncomeDetailTaxes::whereIn('idOtherIncomeDetail',$request->deleteConcepts)->delete();
				App\OtherIncomeDetail::whereIn('id',$request->deleteConcepts)->delete();
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$t_request->folio);
					$documents 		= App\OtherIncomeDocuments::create(
						[
							'path'			=> $new_file_name,
							'idOtherIncome'	=> $t_request->otherIncome->id,
							'name'			=> $request->nameDocument[$i],
						]);
				}
			}

			if (isset($request->quantity_data) && count($request->quantity_data)>0) 
			{
				
				for ($i=0; $i < count($request->quantity_data); $i++)
				{
					if($request->idDetail[$i] == "x")
					{
						
						$t_detail                   = new App\OtherIncomeDetail;
						$t_detail->idOtherIncome	= $t_request->otherIncome->id;
						$t_detail->quantity         = $request->quantity_data[$i];
						$t_detail->unit             = $request->unit_data[$i];
						$t_detail->description      = $request->description_data[$i];
						$t_detail->unit_price       = $request->unit_price_data[$i];
						$t_detail->tax              = $request->tax_data[$i];
						$t_detail->type_tax         = $request->type_tax_data[$i];
						$t_detail->subtotal         = $request->subtotal_data[$i];
						$t_detail->total_retentions = $request->total_retentions_data[$i];
						$t_detail->total_taxes      = $request->total_taxes_data[$i];
						$t_detail->total            = $request->total_data[$i];
						$t_detail->save();
						
					}
					else
					{
						$t_detail                   = App\OtherIncomeDetail::find($request->idDetail[$i]);
						$t_detail->idOtherIncome	= $t_request->otherIncome->id;
						$t_detail->quantity         = $request->quantity_data[$i];
						$t_detail->unit             = $request->unit_data[$i];
						$t_detail->description      = $request->description_data[$i];
						$t_detail->unit_price       = $request->unit_price_data[$i];
						$t_detail->tax              = $request->tax_data[$i];
						$t_detail->type_tax         = $request->type_tax_data[$i];
						$t_detail->subtotal         = $request->subtotal_data[$i];
						$t_detail->total_retentions = $request->total_retentions_data[$i];
						$t_detail->total_taxes      = $request->total_taxes_data[$i];
						$t_detail->total            = $request->total_data[$i];
						$t_detail->save();
					}
					
						
					$t_nameTax		= 't_nameTax'.$i;
					$t_amountTax	= 't_amountTax'.$i;
					if (isset($request->$t_amountTax) && count($request->$t_amountTax)>0) 
					{
						for ($d=0; $d < count($request->$t_amountTax); $d++) 
						{ 
							if ($request->$t_amountTax[$d] != "") 
							{
								$new_tax = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameTax[$d],
									'total'					=> $request->$t_amountTax[$d],
									'type'					=> 'I',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}

					$t_nameRetention 	= 't_nameRetention'.$i;
					$t_amountRetention 	= 't_amountRetention'.$i;
					if (isset($request->$t_amountRetention) && count($request->$t_amountRetention)>0) 
					{
						for ($d=0; $d < count($request->$t_amountRetention); $d++) 
						{ 
							if ($request->$t_amountRetention[$d] != "") 
							{
								$new_retention = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameRetention[$d],
									'total'					=> $request->$t_amountRetention[$d],
									'type'					=> 'R',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}
				}
			}
			$alert = 'swal("","Solicitud Guardada Exitosamente","success");';
			return redirect()->route('other-income.edit-income',['request'=>$t_request->folio])->with('alert',$alert);
		}	
	}

	public function update(App\RequestModel $t_request,Request $request)
	{
		if (Auth::user()->module->where('id',248)->count()>0) 
		{

			$t_request->idEnterprise	= $request->enterprise_id;
			$t_request->idProject		= $request->project_id;
			$t_request->PaymentDate		= $request->payment_date;
			$t_request->kind			= 20;
			$t_request->taxPayment		= $request->taxPayment;
			$t_request->status			= 3;
			$t_request->idRequest		= $request->request_id;
			$t_request->idElaborate		= Auth::user()->id;
			$t_request->save();

			
			$t_request->otherIncome->title				= $request->title;
			$t_request->otherIncome->datetitle			= $request->datetitle;
			$t_request->otherIncome->idbanksAccounts	= $request->idbanksAccounts;
			$t_request->otherIncome->type_income		= $request->type_income;
			$t_request->otherIncome->subtotal			= $request->subtotal;
			$t_request->otherIncome->total_iva			= $request->total_iva;
			$t_request->otherIncome->total_taxes		= $request->total_taxes;
			$t_request->otherIncome->total_retentions	= $request->total_retentions;
			$t_request->otherIncome->total				= $request->total;
			$t_request->otherIncome->type_currency		= $request->type_currency;
			$t_request->otherIncome->pay_mode			= $request->pay_mode;
			$t_request->otherIncome->status_bill		= $request->status_bill;
			$t_request->otherIncome->reference			= $request->reference;
			$t_request->otherIncome->idFolio			= $t_request->folio;
			$t_request->otherIncome->idKind				= $t_request->kind;
			$t_request->otherIncome->borrower			= $request->borrower;
			$t_request->otherIncome->save();

			if (isset($request->deleteConcepts) && count($request->deleteConcepts)>0) 
			{
				App\OtherIncomeDetailTaxes::whereIn('idOtherIncomeDetail',$request->deleteConcepts)->delete();
				App\OtherIncomeDetail::whereIn('id',$request->deleteConcepts)->delete();
			}

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					$new_file_name 	= Files::rename($request->realPath[$i],$t_request->folio);
					$documents 		= App\OtherIncomeDocuments::create(
						[
							'path'			=> $new_file_name,
							'idOtherIncome'	=> $t_request->otherIncome->id,
							'name'			=> $request->nameDocument[$i],
						]);
				}
			}

			if (isset($request->quantity_data) && count($request->quantity_data)>0) 
			{
				for ($i=0; $i < count($request->quantity_data); $i++)
				{
			
						$new_detail	= App\OtherIncomeDetail::create(
							[
								'idOtherIncome'		=> $t_request->otherIncome->id,
								'quantity'			=> $request->quantity_data[$i],
								'unit'				=> $request->unit_data[$i],
								'description'		=> $request->description_data[$i],
								'unit_price'		=> $request->unit_price_data[$i],
								'tax'				=> $request->tax_data[$i],
								'type_tax'			=> $request->type_tax_data[$i],
								'subtotal'			=> $request->subtotal_data[$i],
								'total_retentions'	=> $request->total_retentions_data[$i],
								'total_taxes'		=> $request->total_taxes_data[$i],
								'total'				=> $request->total_data[$i],
							]);
				
					$t_nameTax		= 't_nameTax'.$i;
					$t_amountTax	= 't_amountTax'.$i;
					if (isset($request->$t_amountTax) && count($request->$t_amountTax)>0) 
					{
						for ($d=0; $d < count($request->$t_amountTax); $d++) 
						{ 
							if ($request->$t_amountTax[$d] != "") 
							{
								$new_tax = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameTax[$d],
									'total'					=> $request->$t_amountTax[$d],
									'type'					=> 'I',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}

					$t_nameRetention 	= 't_nameRetention'.$i;
					$t_amountRetention 	= 't_amountRetention'.$i;
					if (isset($request->$t_amountRetention) && count($request->$t_amountRetention)>0) 
					{
						for ($d=0; $d < count($request->$t_amountRetention); $d++) 
						{ 
							if ($request->$t_amountRetention[$d] != "") 
							{
								$new_retention = App\OtherIncomeDetailTaxes::create(
								[
									'description'			=> $request->$t_nameRetention[$d],
									'total'					=> $request->$t_amountRetention[$d],
									'type'					=> 'R',
									'idOtherIncomeDetail'	=> $new_detail->id,
								]);
							}
						}
					}
				}
			}
			$alert = 'swal("","Solicitud Enviada Exitosamente","success");';
			return redirect()->route('other-income.edit')->with('alert',$alert);
		}	
	}

	public function edit(Request $request)
	{
		if (Auth::user()->module->where('id',248)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',248)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',248)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$requests = App\RequestModel::where('kind',20)
					->where(function($query)
					{
						$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(248)->pluck('enterprise_id'))
							->orWhereNull('idEnterprise');
					})
					->where(function ($q) use ($global_permission)
					{
						if ($global_permission == 0) 
						{
							$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
						}
					})
					->where(function ($query) use ($request_id, $mindate, $maxdate, $folio, $status,$project_id,$enterprise_id,$title_request)
					{
						if ($enterprise_id != "") 
						{
							$query->where(function($queryE) use ($enterprise_id)
							{
								$queryE->whereIn('idEnterprise',$enterprise_id)->orWhereIn('idEnterpriseR',$enterprise_id);
							});
						}
						if ($project_id != "") 
						{
							$query->where(function($queryE) use ($project_id)
							{
								$queryE->whereIn('idProject',$project_id)->orWhereIn('idProjectR',$project_id);
							});
						}
						if($request_id != "")
						{
							$query->whereIn('idRequest',$request_id);
						}
						if($folio != "")
						{
							$query->where('folio',$folio);
						}
						if($status != "")
						{
							$query->whereIn('status',$status);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						}
						if($title_request != "")
						{
							$query->whereHas('otherIncome',function($q) use ($title_request)
							{
								$q->where('title','LIKE','%'.$title_request.'%');
							});
						}
					})
					
					->orderBy('fDate','DESC')
					->orderBy('folio','DESC')
					->paginate(10);

			return view('administracion.otros_ingresos.seguimiento',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> $this->module_id,
				'option_id'		=> 248,
				'requests'		=> $requests,
				'request_id'	=> $request_id,
				'folio'			=> $folio,
				'status'		=> $status,
				'mindate'		=> $mindate,
				'maxdate'		=> $maxdate,
				'enterprise_id'	=> $enterprise_id,
				'project_id'	=> $project_id,
				'title_request'	=> $title_request
			]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function exportFollow(Request $request)
	{
		if (Auth::user()->module->where('id',248)->count()>0)
		{
			if(Auth::user()->globalCheck->where('module_id',248)->count()>0)
			{
				$global_permission =  Auth::user()->globalCheck->where('module_id',248)->first()->global_permission;
			}
			else
			{
				$global_permission = 0;
			}
			
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$status			= $request->status;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$other_income = DB::table('other_incomes')->selectRaw(
				'
					request_models.folio as folio,
					other_incomes.title as title,
					other_incomes.datetitle as datetitle,
					CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
					enterprises.name as enterprise_name,
					projects.proyectName as project_name,
					other_incomes.borrower as borrower,
					IF(other_incomes.type_income = 1,
					"Préstamo de terceros", 
						IF(other_incomes.type_income = 2, 
						"Reembolso/reintegro", 
							IF(other_incomes.type_income = 3,
							"Devoluciones", 
								IF(other_incomes.type_income = 4,
								"Ganancias por Inversión", " "
								)
							)
						)
					) as type_income,
					status_requests.description as status,
					other_incomes.reference as reference,
					banks_accounts.alias as alias,
					other_income_details.description as description,
					other_income_details.unit as unit,
					other_income_details.unit_price as unit_price,
					other_income_details.quantity as quantity,
					other_income_details.subtotal as subtotal,
					other_income_details.tax as tax,
					other_income_details.total_taxes as total_taxes,
					other_income_details.total_retentions as total_retentions,
					other_income_details.total as total,
					other_incomes.type_currency as type_currency,
					other_incomes.pay_mode as pay_mode,
					other_incomes.status_bill as status_bill
				'
			)
			->leftJoin('request_models','request_models.folio', '=', 'other_incomes.idFolio')
			->leftJoin('users','users.id', '=','request_models.idRequest')
			->leftJoin('enterprises', 'enterprises.id', '=', 'request_models.idEnterprise')
			->leftJoin('projects', 'projects.idproyect', '=', 'request_models.idProject')
			->leftJoin('status_requests', 'status_requests.idrequestStatus','=','request_models.status')
			->leftJoin('banks_accounts', 'banks_accounts.idbanksAccounts','=', 'other_incomes.idbanksAccounts')
			->leftJoin('other_income_details','other_income_details.idOtherIncome', '=', 'other_incomes.id')
			->where(function ($q) use ($global_permission)
			{
				if ($global_permission == 0) 
				{
					$q->where('idElaborate',Auth::user()->id)->orWhere('idRequest',Auth::user()->id);
				}
			})
			->where(function ($query) use ($request_id, $mindate, $maxdate, $folio, $status,$project_id,$enterprise_id,$title_request)
			{
				if ($enterprise_id != "") 
				{
					$query->whereIn('request_models.idEnterprise',$enterprise_id);
				}
				if ($project_id != "") 
				{
					$query->whereIn('request_models.idProject',$project_id);
					
				}
				if($request_id != "")
				{
					$query->whereIn('request_models.idRequest',$request_id);
				}
				if($folio != "")
				{
					$query->where('request_models.folio',$folio);
				}
				if($status != "")
				{
					$query->whereIn('request_models.status',$status);
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
				}
				if($title_request != "")
				{
					$query->where('other_incomes.title','LIKE','%'.$title_request.'%');
				}
			})
			->orderBy('folio','DESC')
			->get();

			if(count($other_income)==0 || $other_income==null)
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Otros-Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Registro de Ingresos');

			$headers = ['Reporte de Seguimiento de Otros Ingresos','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título','Fecha','Solicitante','Empresa','Proyecto','Prestatario','Tipo de Ingreso','Estatus','Referencia/No. Factura','Cuenta Bancaria','Descripción','Unidad','Precio Unitario','Cantidad','Subtotal','Total IVA','Total Impuestos','Total Retenciones','Total','Tipo de Moneda','Forma de Pago','Estado de Factura'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($other_income as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->title				= '';
					$request->datetitle			= '';
					$request->request_user		= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->borrower			= '';
					$request->type_income		= '';
					$request->status			= '';
					$request->reference			= '';
					$request->alias				= '';
					$request->type_currency		= '';
					$request->pay_mode			= '';
					$request->status_bill		= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['unit_price','subtotal','tax','total_taxes','total_retentions','total']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'quantity')
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
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
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportReview(Request $request)
	{
		if (Auth::user()->module->where('id',249)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$other_income = DB::table('other_incomes')->selectRaw(
				'
					request_models.folio as folio,
					other_incomes.title as title,
					other_incomes.datetitle as datetitle,
					CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
					enterprises.name as enterprise_name,
					projects.proyectName as project_name,
					other_incomes.borrower as borrower,
					IF(other_incomes.type_income = 1,
					"Préstamo de terceros", 
						IF(other_incomes.type_income = 2, 
						"Reembolso/reintegro", 
							IF(other_incomes.type_income = 3,
							"Devoluciones", 
								IF(other_incomes.type_income = 4,
								"Ganancias por Inversión", " "
								)
							)
						)
					) as type_income,
					status_requests.description as status,
					other_incomes.reference as reference,
					banks_accounts.alias as alias,
					other_income_details.description as description,
					other_income_details.unit as unit,
					other_income_details.unit_price as unit_price,
					other_income_details.quantity as quantity,
					other_income_details.subtotal as subtotal,
					other_income_details.tax as tax,
					other_income_details.total_taxes as total_taxes,
					other_income_details.total_retentions as total_retentions,
					other_income_details.total as total,
					other_incomes.type_currency as type_currency,
					other_incomes.pay_mode as pay_mode,
					other_incomes.status_bill as status_bill
				'
			)
			->leftJoin('request_models','request_models.folio', '=', 'other_incomes.idFolio')
			->leftJoin('users','users.id', '=','request_models.idRequest')
			->leftJoin('enterprises', 'enterprises.id', '=', 'request_models.idEnterprise')
			->leftJoin('projects', 'projects.idproyect', '=', 'request_models.idProject')
			->leftJoin('status_requests', 'status_requests.idrequestStatus','=','request_models.status')
			->leftJoin('banks_accounts', 'banks_accounts.idbanksAccounts','=', 'other_incomes.idbanksAccounts')
			->leftJoin('other_income_details','other_income_details.idOtherIncome', '=', 'other_incomes.id')
			->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(249)->pluck('enterprise_id'))
			->where('request_models.status','=','3')
			->where(function ($query) use ($request_id, $mindate, $maxdate, $folio,$project_id,$enterprise_id,$title_request)
			{
				if ($enterprise_id != "") 
				{
					$query->whereIn('request_models.idEnterprise',$enterprise_id);
				}
				if ($project_id != "") 
				{
					$query->whereIn('request_models.idProject',$project_id);
				}
				if($request_id != "")
				{
					$query->whereIn('request_models.idRequest',$request_id);
				}
				if($folio != "")
				{
					$query->where('request_models.folio',$folio);
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
				}
				if($title_request != "")
				{
					$query->where('other_incomes.title','LIKE','%'.$title_request.'%');
				}
			})
			->orderBy('folio','DESC')
			->get();

			if(count($other_income)==0 || $other_income==null)
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Revisión-Otros-Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Registro de Ingresos');

			$headers = ['Reporte de Revisión de Otros Ingresos','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título','Fecha','Solicitante','Empresa','Proyecto','Prestatario','Tipo de Ingreso','Estatus','Referencia/No. Factura','Cuenta Bancaria','Descripción','Unidad','Precio Unitario','Cantidad','Subtotal','Total IVA','Total Impuestos','Total Retenciones','Total','Tipo de Moneda','Forma de Pago','Estado de Factura'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($other_income as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->title				= '';
					$request->datetitle			= '';
					$request->request_user		= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->borrower			= '';
					$request->type_income		= '';
					$request->status			= '';
					$request->reference			= '';
					$request->alias				= '';
					$request->type_currency		= '';
					$request->pay_mode			= '';
					$request->status_bill		= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['unit_price','subtotal','tax','total_taxes','total_retentions','total']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'quantity')
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
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
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}
		else
		{
			return redirect('error');
		}
	}

	public function exportAuthorization(Request $request)
	{
		if (Auth::user()->module->where('id',250)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$other_income = DB::table('other_incomes')->selectRaw(
				'
					request_models.folio as folio,
					other_incomes.title as title,
					other_incomes.datetitle as datetitle,
					CONCAT_WS(" ", users.name,users.last_name,users.scnd_last_name) as request_user,
					enterprises.name as enterprise_name,
					projects.proyectName as project_name,
					other_incomes.borrower as borrower,
					IF(other_incomes.type_income = 1,
					"Préstamo de terceros", 
						IF(other_incomes.type_income = 2, 
						"Reembolso/reintegro", 
							IF(other_incomes.type_income = 3,
							"Devoluciones", 
								IF(other_incomes.type_income = 4,
								"Ganancias por Inversión", " "
								)
							)
						)
					) as type_income,
					status_requests.description as status,
					other_incomes.reference as reference,
					banks_accounts.alias as alias,
					other_income_details.description as description,
					other_income_details.unit as unit,
					other_income_details.unit_price as unit_price,
					other_income_details.quantity as quantity,
					other_income_details.subtotal as subtotal,
					other_income_details.tax as tax,
					other_income_details.total_taxes as total_taxes,
					other_income_details.total_retentions as total_retentions,
					other_income_details.total as total,
					other_incomes.type_currency as type_currency,
					other_incomes.pay_mode as pay_mode,
					other_incomes.status_bill as status_bill
				'
			)
			->leftJoin('request_models','request_models.folio', '=', 'other_incomes.idFolio')
			->leftJoin('users','users.id', '=','request_models.idRequest')
			->leftJoin('enterprises', 'enterprises.id', '=', 'request_models.idEnterprise')
			->leftJoin('projects', 'projects.idproyect', '=', 'request_models.idProject')
			->leftJoin('status_requests', 'status_requests.idrequestStatus','=','request_models.status')
			->leftJoin('banks_accounts', 'banks_accounts.idbanksAccounts','=', 'other_incomes.idbanksAccounts')
			->leftJoin('other_income_details','other_income_details.idOtherIncome', '=', 'other_incomes.id')
			->whereIn('request_models.idEnterprise',Auth::user()->inChargeEnt(249)->pluck('enterprise_id'))
			->where('request_models.status','=','4')
			->where(function ($query) use ($request_id, $mindate, $maxdate, $folio,$project_id,$enterprise_id,$title_request)
			{
				if ($enterprise_id != "") 
				{
					$query->whereIn('request_models.idEnterprise',$enterprise_id);
				}
				if ($project_id != "") 
				{
					$query->whereIn('request_models.idProject',$project_id);
				}
				if($request_id != "")
				{
					$query->whereIn('request_models.idRequest',$request_id);
				}
				if($folio != "")
				{
					$query->where('request_models.folio',$folio);
				}
				if($mindate != "" && $maxdate != "")
				{
					$query->whereBetween('request_models.fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
				}
				if($title_request != "")
				{
					$query->where('other_incomes.title','LIKE','%'.$title_request.'%');
				}
			})
			->orderBy('folio','DESC')
			->get();

			if(count($other_income)==0 || $other_income==null)
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
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Autorización-Otros-Ingresos.xlsx');
			$writer->getCurrentSheet()->setName('Registro de Ingresos');

			$headers = ['Reporte de Autorización de Otros Ingresos','','','','','','','','','','','','','','','','','','','','','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);
			
			$subHeader    = ['Folio','Título','Fecha','Solicitante','Empresa','Proyecto','Prestatario','Tipo de Ingreso','Estatus','Referencia/No. Factura','Cuenta Bancaria','Descripción','Unidad','Precio Unitario','Cantidad','Subtotal','Total IVA','Total Impuestos','Total Retenciones','Total','Tipo de Moneda','Forma de Pago','Estado de Factura'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempFolio     = '';
			$kindRow       = true;
			foreach($other_income as $request)
			{
				if($tempFolio != $request->folio)
				{
					$tempFolio = $request->folio;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->folio				= null;
					$request->title				= '';
					$request->datetitle			= '';
					$request->request_user		= '';
					$request->enterprise_name	= '';
					$request->project_name		= '';
					$request->borrower			= '';
					$request->type_income		= '';
					$request->status			= '';
					$request->reference			= '';
					$request->alias				= '';
					$request->type_currency		= '';
					$request->pay_mode			= '';
					$request->status_bill		= '';
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					if(in_array($k,['unit_price','subtotal','tax','total_taxes','total_retentions','total']))
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r,$currencyFormat);
						}
						else
						{
							$tmpArr[] = WriterEntityFactory::createCell($r);
						}
					}
					elseif($k == 'quantity')
					{
						if($r != '')
						{
							$tmpArr[] = WriterEntityFactory::createCell((double)$r);
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
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();

		}
		else
		{
			return redirect('error');
		}
	}

	public function editIncome(App\RequestModel $request)
	{
		if (Auth::user()->module->where('id',248)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('administracion.otros_ingresos.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 248,
					'request' 	=> $request
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getBanks(Request $request)
	{
		if ($request->ajax()) 
		{
			$banksAccounts 	= App\BanksAccounts::whereIn('idEnterprise',$request->idEnterprise)
							->get();
			return view('administracion.otros_ingresos.parcial.cuentas',['banksAccounts'=>$banksAccounts]);
		}
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{ 
					\Storage::disk('public')->delete('/docs/other-income/'.$request->realPath[$i]);
				}
				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_doc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/other-income/'.$name;
				if($extention=='png' || $extention=='jpg' || $extention=='jpeg')
				{
					try
					{
						$sourceData	= file_get_contents($request->path);
						$resultData	= \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::disk('public')->put($destinity,$resultData);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= strtolower($extention);
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message']	= $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message']	= 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message']	= 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				else
				{
					try
					{
						$myTask = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::disk('public')->put('/docs/uncompressed_pdf/'.$name,\File::get($request->path));
						$file = $myTask->addFile(public_path().'/docs/uncompressed_pdf/'.$name);
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($nameWithoutExtention);
						$myTask->download(public_path().'/docs/compressed_pdf');
						\Storage::disk('public')->move('/docs/compressed_pdf/'.$name,$destinity);
						\Storage::disk('public')->delete(['/docs/uncompressed_pdf/'.$name,'/docs/compressed_pdf/'.$name]);
						$response['error']		= 'DONE';
						$response['path']		= $name;
						$response['message']	= '';
						$response['extention']	= $extention;
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message']	= 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message']	= 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console']	= $e->getMessage();
					}
				}
			}
			return Response($response);
		}
	}

	public function uploadDocuments($id,Request $request)
	{
		if (Auth::user()->module->where('id',166)->count()>0)
		{
			$t_request		= App\RequestModel::find($id);
			$idOtherIncome	= $t_request->otherIncome->id;

			if (isset($request->realPath) && count($request->realPath)>0) 
			{
				for ($i=0; $i < count($request->realPath); $i++) 
				{
					if ($request->realPath[$i] != "") 
					{
						$new_file_name				= Files::rename($request->realPath[$i],$t_request->folio);
						$documents					= new App\OtherIncomeDocuments();
						$documents->name			= $request->nameDocument[$i];
						$documents->path			= $new_file_name;
						$documents->idOtherIncome	= $idOtherIncome;
						$documents->save();
					}
				}
			}

			$alert = "swal('','Documentos Cargados Exitosamente', 'success');";
			return redirect()->route('other-income.edit-income',['request'=>$id])->with('alert',$alert);
		}
		else
		{
			return redirect('error');
		}
	}

	public function review(Request $request)
	{
		if (Auth::user()->module->where('id',249)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$requests = App\RequestModel::where('kind',20)
					->where('status',3)
					->where(function($query)
					{
						$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(249)->pluck('enterprise_id'))
							->orWhereNull('idEnterprise');
					})
					->where(function ($query) use ($request_id, $mindate, $maxdate, $folio,$project_id,$enterprise_id,$title_request)
					{
						if ($enterprise_id != "") 
						{
							$query->where(function($queryE) use ($enterprise_id)
							{
								$queryE->whereIn('idEnterprise',$enterprise_id)->orWhereIn('idEnterpriseR',$enterprise_id);
							});
						}
						if ($project_id != "") 
						{
							$query->where(function($queryE) use ($project_id)
							{
								$queryE->whereIn('idProject',$project_id)->orWhereIn('idProjectR',$project_id);
							});
						}
						if($request_id != "")
						{
							$query->whereIn('idRequest',$request_id);
						}
						if($folio != "")
						{
							$query->where('folio',$folio);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						}
						if($title_request != "")
						{
							$query->whereHas('otherIncome',function($q) use ($title_request)
							{
								$q->where('title','LIKE','%'.$title_request.'%');
							});
						}
					})
					
					->orderBy('fDate','DESC')
					->orderBy('folio','DESC')
					->paginate(10);

			return response(
				view('administracion.otros_ingresos.busqueda_revision',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 249,
						'requests'		=> $requests,
						'request_id'	=> $request_id,
						'folio'			=> $folio,
						'mindate'		=> $mindate,
						'maxdate'		=> $maxdate,
						'enterprise_id'	=> $enterprise_id,
						'project_id'	=> $project_id,
						'title_request'	=> $title_request
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(249), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function showReview(App\RequestModel $request)
	{
		if (Auth::user()->module->where('id',249)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('administracion.otros_ingresos.editar_revision',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 249,
					'request' 	=> $request
				]
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateReview(App\RequestModel $t_request, Request $request)
	{
		if(Auth::user()->module->where('id',249)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			if ($t_request->status == 4 || $t_request->status == 6) 
			{
				$alert = "swal('', 'La solicitud ya ha sido revisada.', 'error');";
			}
			else
			{
				if ($request->status == 4)
				{
					$t_request->status  		= $request->status;
					$t_request->idCheck			= Auth::user()->id;
					$t_request->checkComment	= $request->commentAccept;
					$t_request->reviewDate		= Carbon::now();
					$t_request->save();
					
					$emails = App\User::whereHas('module',function($q)
						{
							$q->where('id', 250);
						})
						->whereHas('inChargeDepGet',function($q) use ($t_request)
						{
							$q->where('departament_id', $t_request->idDepartamentR)
								->where('module_id',250);
						})
						->whereHas('inChargeEntGet',function($q) use ($t_request)
						{
							$q->where('enterprise_id', $t_request->idEnterpriseR)
								->where('module_id',250);
						})
						->where('active',1)
						->where('notification',1)
						->get();
					
					$user 	= App\User::find($t_request->idRequest);
					if ($emails != "")
					{
						try
						{
							foreach ($emails as $email)
							{
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Otros Ingresos";
								$status 		= "Autorizar";
								$date 			= Carbon::now();
								$url 			= route('other-income.authorization.show',['request'=>$t_request->folio]);
								$subject 		= "Solicitud por Autorizar";
								$requestUser	= $user->name.' '.$user->last_name.' '.$user->scnd_last_name;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', 'La solicitud fue enviada exitosamente, pero ocurrió un error al enviar el correo de notificación.', 'success');";
						}
					}
				}
				elseif ($request->status == 6)
				{
					$t_request->status			= $request->status;
					$t_request->idCheck			= Auth::user()->id;
					$t_request->checkComment	= $request->commentReject;
					$t_request->reviewDate		= Carbon::now();
					$t_request->save();

					$emailRequest = "";
					if ($t_request->idElaborate == $t_request->idRequest) 
					{
						$emailRequest 	= App\User::where('id',$t_request->idElaborate)
										->where('notification',1)
										->get();
					}
					else
					{
						$emailRequest 	= App\User::where('id',$t_request->idElaborate)
										->orWhere('id',$t_request->idRequest)
										->where('notification',1)
										->get();
					}
					
					if ($emailRequest != "")
					{
						try
						{
							foreach ($emailRequest as $email)
							{
								$name 			= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
								$to 			= $email->email;
								$kind 			= "Compra";
								$status 		= "RECHAZADA";
								$date 			= Carbon::now();
								$url 			= route('other-income.edit-income',['request'=>$t_request->folio]);
								$subject 		= "Estado de Solicitud";
								$requestUser	= null;
								Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
							}
							$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
						}
						catch(\Exception $e)
						{
							$alert 	= "swal('', 'La solicitud fue enviada exitosamente, pero ocurrió un error al enviar el correo de notificación.', 'success');";
						}

					}
				}
				
			}
			return searchRedirect(249, $alert, 'administration/other-income/review');
		}
		else
		{
			return redirect('/');
		}
	}

	public function authorization(Request $request)
	{
		if (Auth::user()->module->where('id',250)->count()>0)
		{
			$data 			= App\Module::find($this->module_id);
			$request_id 	= $request->request_id;
			$folio			= $request->folio;
			$mindate		= $request->mindate!='' ? $request->mindate : null;
			$maxdate		= $request->maxdate!='' ? $request->maxdate : null;
			$enterprise_id	= $request->enterprise_id;
			$project_id 	= $request->project_id;
			$title_request 	= $request->title_request;

			$requests = App\RequestModel::where('kind',20)
					->where('status',4)
					->where(function($query)
					{
						$query->whereIn('idEnterprise',Auth::user()->inChargeEnt(249)->pluck('enterprise_id'))
							->orWhereNull('idEnterprise');
					})
					->where(function ($query) use ($request_id, $mindate, $maxdate, $folio,$project_id,$enterprise_id,$title_request)
					{
						if ($enterprise_id != "") 
						{
							$query->where(function($queryE) use ($enterprise_id)
							{
								$queryE->whereIn('idEnterprise',$enterprise_id)->orWhereIn('idEnterpriseR',$enterprise_id);
							});
						}
						if ($project_id != "") 
						{
							$query->where(function($queryE) use ($project_id)
							{
								$queryE->whereIn('idProject',$project_id)->orWhereIn('idProjectR',$project_id);
							});
						}
						if($request_id != "")
						{
							$query->whereIn('idRequest',$request_id);
						}
						if($folio != "")
						{
							$query->where('folio',$folio);
						}
						if($mindate != "" && $maxdate != "")
						{
							$query->whereBetween('fDate',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						}
						if($title_request != "")
						{
							$query->whereHas('otherIncome',function($q) use ($title_request)
							{
								$q->where('title','LIKE','%'.$title_request.'%');
							});
						}
					})
					
					->orderBy('fDate','DESC')
					->orderBy('folio','DESC')
					->paginate(10);

			return response(
				view('administracion.otros_ingresos.busqueda_autorizacion',
					[
						'id'			=> $data['father'],
						'title'			=> $data['name'],
						'details'		=> $data['details'],
						'child_id'		=> $this->module_id,
						'option_id'		=> 250,
						'requests'		=> $requests,
						'request_id'	=> $request_id,
						'folio'			=> $folio,
						'mindate'		=> $mindate,
						'maxdate'		=> $maxdate,
						'enterprise_id'	=> $enterprise_id,
						'project_id'	=> $project_id,
						'title_request' => $title_request
					]
				)
			)
			->cookie(
				'urlSearch', storeUrlCookie(250), 2880
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function showAuthorization(App\RequestModel $request)
	{
		if (Auth::user()->module->where('id',250)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			return view('administracion.otros_ingresos.editar_autorizacion',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id' 	=> $this->module_id,
					'option_id' => 250,
					'request' 	=> $request
				]
			);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateAuthorization(App\RequestModel $t_request, Request $request)
	{
		if (Auth::user()->module->where('id',250)->count()>0)
		{
			$data 	= App\Module::find($this->module_id);
			if ($t_request->status == 5 || $t_request->status == 7) 
			{
				$alert = "swal('', 'La solicitud ya ha sido revisada.', 'error');";
			}
			else
			{
				$t_request->status				= $request->status;
				$t_request->idAuthorize			= Auth::user()->id;
				$t_request->authorizeComment	= $request->authorizeCommentA;
				$t_request->authorizeDate		= Carbon::now();
				$t_request->save();	
				
				$alert			= "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
				$emailRequest 	= "";
						
				if ($t_request->idElaborate == $t_request->idRequest) 
				{
					$emailRequest 	= App\User::where('id',$t_request->idElaborate)
									->where('notification',1)
									->get();
				}
				else
				{
					$emailRequest 	= App\User::where('id',$t_request->idElaborate)
									->orWhere('id',$t_request->idRequest)
									->where('notification',1)
									->get();
				}

				
				if ($emailRequest != "") 
				{
					try
					{
						foreach ($emailRequest as $email) 
						{
							$name	= $email->name.' '.$email->last_name.' '.$email->scnd_last_name;
							$to		= $email->email;
							$kind	= "Otros Ingresos";
							if ($request->status == 5) 
							{
								$status = "AUTORIZADA";
							}
							else
							{
								$status = "RECHAZADA";
							}
							$date			= Carbon::now();
							$url			= route('other-income.edit',['id'=>$id]);
							$subject		= "Estado de Solicitud";
							$requestUser	= null;
							Mail::to($to)->send(new App\Mail\Notificacion($name,$kind,$status,$date,$url,$subject,$requestUser));
						}
						$alert = "swal('', 'Solicitud Actualizada Exitosamente', 'success');";
					}
					catch(\Exception $e)
					{
						$alert 	= "swal('', 'La solicitud fue enviada exitosamente, pero ocurrió un error al enviar el correo de notificación.', 'success');";
					}
				}
			}
			return searchRedirect(250, $alert, 'administration/other-income/authorization');
		}
		else
		{
			return redirect('/error');
		}
	}

	public function newRequest(App\RequestModel $t_request)
	{
		if (Auth::user()->module->where('id',247)->count()>0)
		{
			$t_request->status = 2;
			$new_request = 1;
			$data 	= App\Module::find($this->module_id);
			return view('administracion.otros_ingresos.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 247,
					'request'		=> $t_request,
					'new_request'	=> $new_request
				]);
		}
		else
		{
			return redirect('/');
		}
	}
}
