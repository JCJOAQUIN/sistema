<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\NominaEmployee;

class UpdateReceipts extends Command
{
	protected $signature = 'receipts:update';

	protected $description = 'Actualizar recibos para que sean mostrados en el explorador de archivos público';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$proyectsId    = [];
		$enterprisesId = [];
		$minDate       = "";
		$maxDate       = "";
		$employeeId    = [];
		$baseDir       = "";
		for ($i=1; $i < 5; $i++)
		{
			$this->info('Working on case '.$i);
			switch ($i)
			{
				case 1:
					$proyectsId    = [126];
					$enterprisesId = [5];
					$minDate       = "2021-01-01";
					$maxDate       = "2023-12-31";
					$employeeId    = [];
					$baseDir       = "recibos_dos_bocas";
					break;
				case 2:
					$proyectsId    = [37];
					$enterprisesId = [1,2,3,4,6,7,8,9,10,11,12];
					$minDate       = "2021-01-01";
					$maxDate       = "2023-12-31";
					$employeeId    = [];
					$baseDir       = "recibos_administrativo_central";
					break;
				case 3:
					$proyectsId    = [37];
					$enterprisesId = [5];
					$minDate       = "2021-01-01";
					$maxDate       = "2023-12-31";
					$employeeId    = [];
					$baseDir       = "recibos_administrativo_central_proyecta";
					break;
				case 4:
					$proyectsId    = [];
					$enterprisesId = [];
					$minDate       = "2021-01-01";
					$maxDate       = "2023-12-31";
					$employeeId    = [1037];
					$baseDir       = "recibos_administrativo_central_proyecta";
					break;
			}
			$nf = NominaEmployee::selectRaw('
					IF(
						nominas.idCatTypePayroll = "001",
						IF(
							nominas.idCatPeriodicity = "05",
							UPPER(DATE_FORMAT(nominas.from_date,"%b - %Y")),
							IF(
								nominas.idCatPeriodicity = "04",
								IF(
									nominas.to_date <= DATE_FORMAT(nominas.to_date,"%Y-%m-15"),
									UPPER(CONCAT("1q ",DATE_FORMAT(nominas.from_date,"%b - %Y"))),
									UPPER(CONCAT("2q ",DATE_FORMAT(nominas.from_date,"%b - %Y")))
								),
								UPPER(CONCAT("sem ",DATE_FORMAT(nominas.from_date,"%u")," - ",DATE_FORMAT(nominas.from_date,"%Y")))
							)
						),
						IF(
							nominas.idCatTypePayroll = "002",
							CONCAT("1Q DIC - ",YEAR(nominas.datetitle)),
							IF(
								nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004",
								DATE_FORMAT(nominas.down_date,"%M - %Y"),
								DATE_FORMAT(nominas.datetitle,"%M - %Y")
							)
						)
					) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					payroll_receipts.path as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('payroll_receipts','nomina_employee_n_fs.idnominaemployeenf','payroll_receipts.idnominaemployeenf')
				->whereIn('request_models.status',[5,10,11,12,18])
				->where('request_models.kind',16)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($nf as $key => $non)
			{
				if($non->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$non->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.$non->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$non->payment_doc));
						} catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$non->payment_doc);
					}
				}
				if($non->receipt != '')
				{
					if(\Storage::disk('reserved')->exists($non->receipt))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$non->last_name.' '.$non->scnd_last_name.' '.$non->name.'/'.$non->periodRange.'/'.str_replace("/receipts/","",$non->receipt), \Storage::disk('reserved')->readStream($non->receipt));
						} catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',str_replace("/receipts/","",$non->receipt));
					}
				}
			}
			$salary = NominaEmployee::selectRaw('
					IF(
						nomina_employees.idCatPeriodicity = "05",
						UPPER(DATE_FORMAT(nomina_employees.from_date,"%b - %Y")),
						IF(
							nomina_employees.idCatPeriodicity = "04",
							IF(
								nomina_employees.to_date <= DATE_FORMAT(nomina_employees.to_date,"%Y-%m-15"),
								UPPER(CONCAT("1q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y"))),
								UPPER(CONCAT("2q ",DATE_FORMAT(nomina_employees.from_date,"%b - %Y")))
							),
							UPPER(CONCAT("sem ",DATE_FORMAT(nomina_employees.from_date,"%u")," - ",DATE_FORMAT(nomina_employees.from_date,"%Y")))
						)
					) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					bills.uuid as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->whereIn('request_models.status',[5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($salary as $key => $sue)
			{
				if($sue->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
					}
				}
				if($sue->receipt != '')
				{
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
					}
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
						}catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
					}
				}
			}
			$bonus = NominaEmployee::selectRaw('
					CONCAT("1Q DIC - ",YEAR(nominas.datetitle)) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					bills.uuid as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($bonus as $key => $sue)
			{
				if($sue->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
					}
				}
				if($sue->receipt != '')
				{
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
					}
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
						}catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
					}
				}
			}
			$liquidation = NominaEmployee::selectRaw('
					UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					bills.uuid as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($liquidation as $key => $sue)
			{
				if($sue->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
					}
				}
				if($sue->receipt != '')
				{
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
					}
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
						}catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
					}
				}
			}
			$vacation_premia = NominaEmployee::selectRaw('
					UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					bills.uuid as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($vacation_premia as $key => $sue)
			{
				if($sue->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
					}
				}
				if($sue->receipt != '')
				{
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
					}
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
						}catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
					}
				}
			}
			$profit_sharings = NominaEmployee::selectRaw('
					UPPER(DATE_FORMAT(nominas.datetitle,"%M - %Y")) as periodRange,
					real_employees.last_name as last_name,
					real_employees.scnd_last_name as scnd_last_name,
					real_employees.name as name,
					documents_payments.path as "payment_doc",
					bills.uuid as "receipt"
				')
				->join('nominas','nomina_employees.idnomina','=','nominas.idnomina')
				->join('request_models','nominas.idFolio','=','request_models.folio')
				->join('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
				->join('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
				->join('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
				->leftJoin('payments','nomina_employees.idnominaEmployee','payments.idnominaEmployee')
				->leftJoin('documents_payments','payments.idpayment','documents_payments.idpayment')
				->leftJoin('employee_bill','nomina_employees.idnominaEmployee','employee_bill.idNominaEmployee')
				->leftJoin('bills','employee_bill.idBill','bills.idBill')
				->whereIn('request_models.status',[4,5,10,11,12,18])
				->where('request_models.kind',16)
				->where('request_models.taxPayment',1)
				->where(function($q) use($minDate, $maxDate, $proyectsId, $enterprisesId, $employeeId)
				{
					if(count($proyectsId) > 0)
					{
						$q->whereIn('worker_datas.project',$proyectsId);
					}
					if(count($enterprisesId) > 0)
					{
						$q->whereIn('worker_datas.enterprise',$enterprisesId);
					}
					if(count($employeeId) > 0)
					{
						$q->whereIn('nomina_employees.idrealEmployee',$employeeId);
					}
					if($minDate != "" && $maxDate != "")
					{
						$q->whereBetween('nominas.from_date',[$minDate,$maxDate])
							->whereBetween('nominas.to_date',[$minDate,$maxDate]);
					}
				})
				->get();
			foreach ($profit_sharings as $key => $sue)
			{
				if($sue->payment_doc != '')
				{
					if(\Storage::disk('public')->exists('docs/payments/'.$sue->payment_doc))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->payment_doc, \Storage::disk('public')->readStream('docs/payments/'.$sue->payment_doc));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->payment_doc);
					}
				}
				if($sue->receipt != '')
				{
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.xml'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.xml', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.xml'));
						}
						catch (\Throwable $th){}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.xml');
					}
					if(\Storage::disk('reserved')->exists('stamped/'.$sue->receipt.'.pdf'))
					{
						try {
							\Storage::disk('public')->writeStream('receipts/data/'.$baseDir.'/'.$sue->last_name.' '.$sue->scnd_last_name.' '.$sue->name.'/'.$sue->periodRange.'/'.$sue->receipt.'.pdf', \Storage::disk('reserved')->readStream('stamped/'.$sue->receipt.'.pdf'));
						}catch (\Throwable $th) {}
					}
					else
					{
						\Storage::disk('reserved')->append('missing.txt',$sue->receipt.'.pdf');
					}
				}
			}
		}
	}
}
