<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;
use Carbon\Carbon;
use PDF;
use Excel;
use Illuminate\Support\Str as Str;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
	private $father_module = [
			'administration' => 1,
			'operation'      => 2,
			'report'         => 3,
			'configuration'  => 4,
			'tools'          => 271,
			'construction'   => 308,
			];

	public function index()
	{
		return view('home');
	}

	public function table()
	{
		return view('table');
	}

	public function updateTax(Request $request)
	{
		foreach (App\Provider::all() as $provider) 
		{
			$new_prov						= new App\ProviderData();
			$new_prov->businessName			= $provider->businessName;
			$new_prov->beneficiary			= $provider->beneficiary;
			$new_prov->phone				= $provider->phone;
			$new_prov->rfc					= $provider->rfc;
			$new_prov->contact				= $provider->contact;
			$new_prov->commentaries			= $provider->commentaries;
			$new_prov->status				= $provider->status;
			$new_prov->users_id				= $provider->users_id;
			$new_prov->address				= $provider->address;
			$new_prov->number				= $provider->number;
			$new_prov->colony				= $provider->colony;
			$new_prov->postalCode			= $provider->postalCode;
			$new_prov->city					= $provider->city;
			$new_prov->state_idstate		= $provider->state_idstate;
			$new_prov->provider_idProvider	= $provider->idProvider;
			$new_prov->save();
		}
		$alert = "swal('','Actualizacion existosa','success');";
		return redirect('/home')->with('alert',$alert); 
		/*
		$employees = App\RealEmployee::all();
		foreach ($employees as $employee) 
		{
			$oldRecordInactive 	= $employee->workerData->where('admissionDate','!=',null)->where('enterprise','!=',null)->where('workerStatus',2)->first();
			$oldRecordActive 	= $employee->workerData->where('admissionDate','!=',null)->where('enterprise','!=',null)->where('workerStatus',1)->first();
			$nowRecordActive 	= $employee->workerDataVisible->where('admissionDate','!=',null)->where('enterprise','!=',null)->first();

			foreach ($employee->workerData as $worker) 
			{
				$flag = true;
				if ($oldRecordInactive == '' && $oldRecordActive == '')
				{
					$flag = false;
				} 
				if ($flag) 
				{
					if ($oldRecordInactive != '') 
					{
						$worker->enterpriseOld 		= $oldRecordInactive->enterprise;

						if (new \DateTime($nowRecordActive->admissionDate) <= new \DateTime($oldRecordInactive->admissionDate)) 
						{
							$worker->admissionDateOld 	= $nowRecordActive->admissionDate;
						}
						else
						{
							$worker->admissionDateOld 	= $oldRecordInactive->admissionDate;
						}

						$worker->save();
					}
					else
					{
						$worker->enterpriseOld 		= $oldRecordActive->enterprise;

						if (new \DateTime($nowRecordActive->admissionDate) <= new \DateTime($oldRecordActive->admissionDate)) 
						{
							$worker->admissionDateOld 	= $nowRecordActive->admissionDate;
						}
						else
						{
							$worker->admissionDateOld 	= $oldRecordActive->admissionDate;
						}
						
						$worker->save();
					}
				}
			}
		}
		$alert = "swal('','Actualizacion existosa','success');";
		return redirect('/home')->with('alert',$alert);
		
		foreach (App\Nomina::all() as $nomina) 
		{
			if (App\RequestModel::find($nomina->idFolio)->taxPayment == 1) 
			{
				switch ($nomina->idCatTypePayroll) 
				{
					case '001':
						foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
						{
							if ($nominaemployee->salary()->exists() && $nominaemployee->payments()->exists()) 
							{
								$salary = round($nominaemployee->salary->first()->netIncome,2);
								$payments = round($nominaemployee->payments->sum('amount'),2);

								if ($salary == $payments) 
								{
									$nominaemployee->payment = 1;
									$nominaemployee->save();
								}
							}
						}
						break;

					case '002':
						foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
						{
							if ($nominaemployee->bonus()->exists() && $nominaemployee->payments()->exists()) 
							{
								$bonus = round($nominaemployee->bonus->first()->netIncome,2);
								$payments = round($nominaemployee->payments->sum('amount'),2);

								if ($bonus == $payments) 
								{
									$nominaemployee->payment = 1;
									$nominaemployee->save();
								}
							}
						}
						break;

					case '003':
					case '004':
						foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
						{
							if ($nominaemployee->liquidation()->exists() && $nominaemployee->payments()->exists()) 
							{
								$liquidation = round($nominaemployee->liquidation->first()->netIncome,2);
								$payments = round($nominaemployee->payments->sum('amount'),2);

								if ($liquidation == $payments) 
								{
									$nominaemployee->payment = 1;
									$nominaemployee->save();
								}
							}
						}
						break;

					case '005':
						foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
						{
							if ($nominaemployee->vacationPremium()->exists() && $nominaemployee->payments()->exists()) 
							{
								$vacationPremium = round($nominaemployee->vacationPremium->first()->netIncome,2);
								$payments = round($nominaemployee->payments->sum('amount'),2);

								if ($vacationPremium == $payments) 
								{
									$nominaemployee->payment = 1;
									$nominaemployee->save();
								}
							}
						}
						break;

					case '006':
						foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
						{
							if ($nominaemployee->profitSharing()->exists() && $nominaemployee->payments()->exists()) 
							{
								$profitSharing = round($nominaemployee->profitSharing->first()->netIncome,2);
								$payments = round($nominaemployee->payments->sum('amount'),2);

								if ($profitSharing == $payments) 
								{
									$nominaemployee->payment = 1;
									$nominaemployee->save();
								}
							}
						}
						break;
					default:
						# code...
						break;
				}
			}
			else
			{
				foreach ($nomina->nominaEmployee->where('payment',0) as $nominaemployee) 
				{
					if ($nominaemployee->nominasEmployeeNF()->exists() && $nominaemployee->payments()->exists()) 
					{
						$nominasEmployeeNF = round($nominaemployee->nominasEmployeeNF->first()->amount,2);
						$payments = round($nominaemployee->payments->sum('amount'),2);

						if ($nominasEmployeeNF == $payments) 
						{
							$nominaemployee->payment = 1;
							$nominaemployee->save();
						}
					}
				}
			}
		}*/
	}

	public function changeStatusAutomatic()
	{
		$fecha      = date('Y-m-d');
		$nuevafecha = date('Y-m-d',strtotime('-7 day',strtotime($fecha)));
		$update 	= App\Ticket::whereBetween('request_date',[''.$nuevafecha.' '.date('00:00:00').'',''.$nuevafecha.' '.date('23:59:59').''])
			->whereIn('idStatusTickets',[2,3])
			->update(
				[
					'idStatusTickets' => 4,
				]);
	}

	public function administration()
	{
		return $this->parent_module($this->father_module['administration']);
	}

	public function operation()
	{
		return $this->parent_module($this->father_module['operation']);
	}

	public function report()
	{
		return $this->parent_module($this->father_module['report']);
	}

	public function configuration()
	{
		return $this->parent_module($this->father_module['configuration']);
	}

	public function tools()
	{
		return $this->parent_module($this->father_module['tools']);
	}

	public function construction()
	{
		return $this->parent_module($this->father_module['construction']);
	}

	public function parent_module($id)
	{
		if(Auth::user()->module->where('id',$id)->count()>0)
		{
			$data = App\Module::find($id);
			return view('layouts.parent_module',
				[
					'id'		=>$data['id'],
					'title'		=>$data['name'],
					'details'	=>$data['details']
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function tutorial()
	{
		return view('others.tutorial');
	}

	public function cancelled(App\Bill $bill)
	{
		if($bill->status == 4)
		{
			$pdf = PDF::loadView('administracion.facturacion.acuse',['bill'=>$bill]);
			\Storage::disk('reserved')->put('/cancelled/'.$bill->uuid.'_acuse.pdf',$pdf->stream());
			return $bill->uuid.'_acuse.pdf generado';
		}
		else
		{
			return abort(404);
		}
	}

	public function stamped()
	{
		$bills = App\Bill::whereIn('idBill',[22577])->get();
		foreach ($bills as $bill) 
		{
			$pdf					= PDF::loadView('administracion.facturacion.'.$bill->rfc,['bill'=>$bill]);
			\Storage::disk('reserved')->put('/stamped/'.$bill->uuid.'.pdf',$pdf->stream());
			$pdfFile	= '/stamped/'.$bill->uuid.'.pdf';
		}

		return 'a';

		/*
		
		foreach (App\RequestModel::where('kind',16)->get() as $request) 
		{
			if ($request->taxPayment == 1) 
			{
				$type_nomina = 1;
			}	
			else
			{
				$type_nomina = 2;
			}

			$request->nominasReal->first()->type_nomina = $type_nomina;
			$request->nominasReal->first()->save();
		}
	
		
		foreach (App\RealEmployee::all() as $employee) 
		{
			
			$requestsFiscal = App\RequestModel::leftJoin('nominas','request_models.folio','nominas.idFolio')
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('salaries','nomina_employees.idnominaEmployee','salaries.idnominaEmployee')
						->where('request_models.kind',16)
						->where('nominas.idCatTypePayroll','001')
						->whereYear('nominas.from_date','2020')
						->whereIn('request_models.status',[5,10,11,12,18])
						->where('nomina_employees.idrealEmployee',$employee->id)
						->where('request_models.taxPayment',1)
						->select('salaries.workedDays','salaries.netIncome')
						->get();

			$requestsNF = App\RequestModel::leftJoin('nominas','request_models.folio','nominas.idFolio')
						->leftJoin('nomina_employees','nominas.idnomina','nomina_employees.idnomina')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','nomina_employee_n_fs.idnominaEmployee')
						->where('request_models.kind',16)
						->where('nominas.idCatTypePayroll','001')
						->whereYear('nominas.from_date','2020')
						->whereIn('request_models.status',[5,10,11,12,18])
						->where('nomina_employees.idrealEmployee',$employee->id)
						->where('request_models.taxPayment',0)
						->select('nomina_employee_n_fs.amount')
						->sum('nomina_employee_n_fs.amount');

			$employee->workedDays = $requestsFiscal->sum('salaries.workedDays');
			$employee->salaryFiscal = $requestsFiscal->sum('salaries.netIncome');
			$employee->salaryNoFiscal = $requestsNF;
			$employee->save();
		}
		return 'wu';
		
		$employeesObra = App\RealEmployee::leftJoin('worker_datas','real_employees.id','=','idEmployee')
		->leftJoin('accounts','worker_datas.account','=','accounts.idAccAcc')
		->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
		->where('worker_datas.visible',1)
		->where('accounts.account','LIKE','51%')
		->select('real_employees.name','real_employees.last_name','real_employees.scnd_last_name','worker_datas.imssDate','worker_datas.downDate','real_employees.workedDays','real_employees.salaryFiscal','real_employees.salaryNoFiscal','enterprises.name as enterpriseName','accounts.account','accounts.description as accountName','worker_datas.admissionDate')
		->get();

		$employeesAdmin = App\RealEmployee::leftJoin('worker_datas','real_employees.id','=','idEmployee')
		->leftJoin('accounts','worker_datas.account','=','accounts.idAccAcc')
		->leftJoin('enterprises','enterprises.id','worker_datas.enterprise')
		->where('worker_datas.visible',1)
		->where('accounts.account','NOT LIKE','51%')
		->select('real_employees.name','real_employees.last_name','real_employees.scnd_last_name','worker_datas.imssDate','worker_datas.downDate','real_employees.workedDays','real_employees.salaryFiscal','real_employees.salaryNoFiscal','enterprises.name as enterpriseName','accounts.account','accounts.description as accountName','worker_datas.admissionDate')
		->get();

		Excel::create('Lista Empleados', function($excel) use ($employeesObra,$employeesAdmin)
			{
				$excel->sheet('Empleados',function($sheet) use ($employeesObra,$employeesAdmin)
				{
					$sheet->setStyle([
							'font' => [
								'name'	=> 'Calibri',
								'size'	=> 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->mergeCells('A1:I1');
					$sheet->setColumnFormat(array(
							'F'	=> '@',
						));
					$sheet->cell('A1:I2', function($cells)
					{
						$cells->setBackground('#343a40');
						$cells->setFontColor('#ffffff');
					
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['INFORMACIÓN EMPLEADOS 2020']);
					$sheet->row(2,[
							'Empleado',
							'Empresa',
							'Fecha de ingreso',
							'Fecha de baja',
							'Area',
							'Cuenta',
							'Días Trabajados',
							'Sueldo anual fiscal', 
							'Sueldo anual externo',
						]);

					foreach ($employeesObra as $employee)
					{
						$row	= [];
						$row[]	= $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name;
						$row[]	= $employee->enterpriseName;
						$row[] 	= $employee->imssDate;
						$row[] 	= $employee->downDate;
						$row[] 	= 'Obra';
						$row[] 	= $employee->account.' '.$employee->accountName;
						$row[]	= $employee->workedDays;
						$row[]	= $employee->salaryFiscal;
						$row[]	= $employee->salaryNoFiscal;
						$sheet->appendRow($row);
					}

					foreach ($employeesAdmin as $employee)
					{
						$row	= [];
						$row[]	= $employee->name.' '.$employee->last_name.' '.$employee->scnd_last_name;
						$row[]	= $employee->enterpriseName;
						$row[] 	= $employee->imssDate;
						$row[] 	= $employee->downDate;
						$row[] 	= 'Administrativo';
						$row[] 	= $employee->account.' '.$employee->accountName;
						$row[]	= $employee->workedDays;
						$row[]	= $employee->salaryFiscal;
						$row[]	= $employee->salaryNoFiscal;
						$sheet->appendRow($row);
					}
				});
			})->export('xlsx');

		*/
	}

	public function purchase_docs($folios)
	{
		$foliosArr = explode(',',$folios);
		$zip_file  = '/tmp/documents.zip';
		$zip       = new \ZipArchive();
		if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
		{
			foreach($foliosArr as $f)
			{
				$req = App\RequestModel::find($f);
				if($req != '' && $req->kind == 1)
				{
					$pur = $req->purchases->first();
					$zip->addEmptyDir('documents');
					foreach ($pur->documents as $doc)
					{
						$zip->addFile(public_path('/docs/purchase/'.$doc->path), '/documents/Folio '.$req->folio.'/Documentación/'.$doc->path);
					}
					if($req->paymentsRequest()->exists())
					{
						foreach ($req->paymentsRequest as $pay)
						{
							if($pay->documentsPayments()->exists())
							{
								foreach($pay->documentsPayments as $payDoc)
								{
									$zip->addFile(public_path('/docs/payments/'.$payDoc->path), '/documents/Folio '.$req->folio.'/Pagos/'.$payDoc->path);
								}
							}
						}
					}
				}
			}
		}
		$zip->close();
		return response()->download($zip_file);
	}

	public function provider_docs($folios)
	{
		$foliosArr = explode(',',$folios);
		$providers = App\Provider::whereIn('provider_data_id',$foliosArr)->get();
		$baseDir = 'proveedores_pagos';
		foreach($providers as $provider)
		{
			foreach($provider->requests as $purchase)
			{
				foreach($purchase->requestModel->paymentsRequest as $payment)
				{
					foreach($payment->documentsPayments as $doc)
					{
						if(\Storage::disk('public')->exists('docs/payments/'.$doc->path))
						{
							try {
								\Storage::disk('reserved')->writeStream($baseDir.'/'.Str::slug($provider->businessName).'/'.$doc->path, \Storage::disk('public')->readStream('docs/payments/'.$doc->path));
							} catch (\Throwable $th) {}
						}
						else
						{
							\Storage::disk('reserved')->append('missing_provider.txt',$doc->path);
						}
					}
				}
			}
		}
		return "☠️";
	}
}
