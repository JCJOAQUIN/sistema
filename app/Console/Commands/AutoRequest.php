<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\AutomaticRequests;
use App\RequestModel;
use App\Purchase;
use App\DetailPurchase;
use App\Resource;
use App\ResourceDetail;
use Carbon\Carbon;

class AutoRequest extends Command
{

	protected $signature = 'auto:request';

	protected $description = 'Crear solicitud automatica (quincenal y mensual)';

	public function __construct()
	{
		parent::__construct();
	}
	
	public function handle()
	{

		$requests  	= AutomaticRequests::where('status',1)->get();
		if (count($requests)>0) 
		{
			foreach ($requests as $request) 
			{
				switch ($request->periodicity) 
				{
					case 'monthlyOn':
						if(date('d') == $request->day_monthlyOn)
						{
							$kind = $request->kind;

							switch ($kind) 
							{
								case 1:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->taxPayment		= $request->taxPayment;
									$t_request->account 		= $request->idAccAcc;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject 		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->status 			= 2;
									$t_request->PaymentDate 	= $request->purchase->payment_date;
									$t_request->fDate 			= Carbon::now();
									$t_request->save();
									
									$t_purchase							= new Purchase();
									$t_purchase->title					= $request->purchase->title;
									$t_purchase->datetitle				= Carbon::now();
									$t_purchase->numberOrder			= $request->purchase->numberOrder;
									$t_purchase->reference				= $request->purchase->reference;
									$t_purchase->idProvider				= $request->purchase->idProvider;
									$t_purchase->notes					= $request->purchase->notes;
									$t_purchase->paymentMode			= $request->purchase->paymentMode;
									$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
									$t_purchase->billStatus				= $request->purchase->billStatus;
									$t_purchase->subtotales				= $request->purchase->subtotal;
									$t_purchase->tax					= $request->purchase->tax;
									$t_purchase->amount					= $request->purchase->amount;
									$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
									$t_purchase->idFolio 				= $t_request->folio;
									$t_purchase->idKind 				= $t_request->kind;
									$t_purchase->save();

									foreach ($request->purchase->detailPurchase as $detail) 
									{
										$t_detailPurchase				= new DetailPurchase();
										$t_detailPurchase->idPurchase	= $t_purchase->idPurchase;
										$t_detailPurchase->quantity		= $detail->quantity;
										$t_detailPurchase->unit			= $detail->unit;
										$t_detailPurchase->description	= $detail->description;
										$t_detailPurchase->unitPrice	= $detail->unitPrice;
										$t_detailPurchase->tax			= $detail->tax;
										$t_detailPurchase->discount		= $detail->discount;
										$t_detailPurchase->amount		= $detail->amount;
										$t_detailPurchase->typeTax		= $detail->typeTax;
										$t_detailPurchase->subtotal		= $detail->subtotal;
										$t_detailPurchase->save();

										if ($detail->retentions()->exists()) 
										{
											foreach ($detail->retentions as $ret) 
											{
												$t_ret						= new App\RetentionPurchase();
												$t_ret->name				= $ret->name;
												$t_ret->amount				= $ret->amount;
												$t_ret->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_ret->save();
											}
										}

										if ($detail->taxes()->exists()) 
										{
											foreach ($detail->taxes as $tax) 
											{
												$t_taxes                    = new App\TaxesPurchase();
												$t_taxes->name				= $tax->name;
												$t_taxes->amount			= $tax->amount;
												$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_taxes->save();
											}
										}
									}
									break;

								case 8:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->fDate			= Carbon::now();
									$t_request->status 			= 2;
									$t_request->save();
									
									$t_resource				= new Resource();
									$t_resource->title		= $request->resource->title;
									$t_resource->datetitle	= Carbon::now();
									$t_resource->total		= $request->resource->total;
									$t_resource->reference	= $request->resource->reference;
									$t_resource->currency	= $request->resource->currency;
									$t_resource->idFolio 	= $t_request->folio;
									$t_resource->idKind 	= $t_request->kind;
									$t_resource->idUsers 	= $request->idRequest;
									
									if ($request->resource->idpaymentMethod == 1) 
									{
										$t_resource->idEmployee	= $request->resource->idEmployee;
									}
									else
									{
										$t_resource->idEmployee	= null;
									}
									$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
									$t_resource->save();

									
									foreach ($request->resource->resourceDetail as $detail)
									{
										$t_detailResource				= new ResourceDetail();
										$t_detailResource->idresource	= $t_resource->idresource;
										$t_detailResource->concept		= $detail->concept;
										$t_detailResource->idAccAcc		= $detail->idAccAcc;
										$t_detailResource->amount		= $detail->amount;
										$t_detailResource->save();
									}
									break;
								
								default:
									# code...
									break;
							}
						}
						break;

					case 'twiceMonthly':
						
						if(date('d') == $request->day_twiceMonthly_one || date('d') == $request->day_twiceMonthly_two)
						{
							$kind = $request->kind;

							switch ($kind) 
							{
								case 1:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->taxPayment		= $request->taxPayment;
									$t_request->account 		= $request->idAccAcc;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject 		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->status 			= 2;
									$t_request->PaymentDate 	= $request->purchase->payment_date;
									$t_request->fDate 			= Carbon::now();
									$t_request->save();
									
									$t_purchase							= new Purchase();
									$t_purchase->title					= $request->purchase->title;
									$t_purchase->datetitle				= Carbon::now();
									$t_purchase->numberOrder			= $request->purchase->numberOrder;
									$t_purchase->reference				= $request->purchase->reference;
									$t_purchase->idProvider				= $request->purchase->idProvider;
									$t_purchase->notes					= $request->purchase->notes;
									$t_purchase->paymentMode			= $request->purchase->paymentMode;
									$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
									$t_purchase->billStatus				= $request->purchase->billStatus;
									$t_purchase->subtotales				= $request->purchase->subtotal;
									$t_purchase->tax					= $request->purchase->tax;
									$t_purchase->amount					= $request->purchase->amount;
									$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
									$t_purchase->idFolio 				= $t_request->folio;
									$t_purchase->idKind 				= $t_request->kind;
									$t_purchase->save();

									foreach ($request->purchase->detailPurchase as $detail) 
									{
										$t_detailPurchase					= new DetailPurchase();
										$t_detailPurchase->idPurchase		= $t_purchase->idPurchase;
										$t_detailPurchase->quantity			= $detail->quantity;
										$t_detailPurchase->unit				= $detail->unit;
										$t_detailPurchase->description		= $detail->description;
										$t_detailPurchase->unitPrice		= $detail->unitPrice;
										$t_detailPurchase->tax				= $detail->tax;
										$t_detailPurchase->discount			= $detail->discount;
										$t_detailPurchase->amount			= $detail->amount;
										$t_detailPurchase->typeTax			= $detail->typeTax;
										$t_detailPurchase->subtotal			= $detail->subtotal;
										$t_detailPurchase->save();

										if ($detail->retentions()->exists()) 
										{
											foreach ($detail->retentions as $ret) 
											{
												$t_ret						= new App\RetentionPurchase();
												$t_ret->name				= $ret->name;
												$t_ret->amount				= $ret->amount;
												$t_ret->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_ret->save();
											}
										}

										if ($detail->taxes()->exists()) 
										{
											foreach ($detail->taxes as $tax) 
											{
												$t_taxes					= new App\TaxesPurchase();
												$t_taxes->name				= $tax->name;
												$t_taxes->amount			= $tax->amount;
												$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_taxes->save();
											}
										}
									}
									break;

								case 8:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->fDate			= Carbon::now();
									$t_request->status 			= 2;
									$t_request->save();
									
									$t_resource				= new Resource();
									$t_resource->title		= $request->resource->title;
									$t_resource->datetitle	= Carbon::now();
									$t_resource->total		= $request->resource->total;
									$t_resource->reference	= $request->resource->reference;
									$t_resource->currency	= $request->resource->currency;
									$t_resource->idFolio 	= $t_request->folio;
									$t_resource->idKind 	= $t_request->kind;
									$t_resource->idUsers 	= $request->idRequest;
									
									if ($request->resource->idpaymentMethod == 1) 
									{
										$t_resource->idEmployee	= $request->resource->idEmployee;
									}
									else
									{
										$t_resource->idEmployee	= null;
									}
									$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
									$t_resource->save();

									
									foreach ($request->resource->resourceDetail as $detail)
									{
										$t_detailResource				= new ResourceDetail();
										$t_detailResource->idresource	= $t_resource->idresource;
										$t_detailResource->concept		= $detail->concept;
										$t_detailResource->idAccAcc		= $detail->idAccAcc;
										$t_detailResource->amount		= $detail->amount;
										$t_detailResource->save();
									}
									break;
								
								default:
									# code...
									break;
							}
						}
						break;

					case 'weeklyOn':
						
						if(date('N') == $request->day_weeklyOn)
						{
							$kind = $request->kind;

							switch ($kind) 
							{
								case 1:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->taxPayment		= $request->taxPayment;
									$t_request->account 		= $request->idAccAcc;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject 		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->status 			= 2;
									$t_request->PaymentDate 	= $request->purchase->payment_date;
									$t_request->fDate 			= Carbon::now();
									$t_request->save();
									
									$t_purchase							= new Purchase();
									$t_purchase->title					= $request->purchase->title;
									$t_purchase->datetitle				= Carbon::now();
									$t_purchase->numberOrder			= $request->purchase->numberOrder;
									$t_purchase->reference				= $request->purchase->reference;
									$t_purchase->idProvider				= $request->purchase->idProvider;
									$t_purchase->notes					= $request->purchase->notes;
									$t_purchase->paymentMode			= $request->purchase->paymentMode;
									$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
									$t_purchase->billStatus				= $request->purchase->billStatus;
									$t_purchase->subtotales				= $request->purchase->subtotal;
									$t_purchase->tax					= $request->purchase->tax;
									$t_purchase->amount					= $request->purchase->amount;
									$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
									$t_purchase->idFolio 				= $t_request->folio;
									$t_purchase->idKind 				= $t_request->kind;
									$t_purchase->save();

									foreach ($request->purchase->detailPurchase as $detail) 
									{
										$t_detailPurchase					= new DetailPurchase();
										$t_detailPurchase->idPurchase		= $t_purchase->idPurchase;
										$t_detailPurchase->quantity			= $detail->quantity;
										$t_detailPurchase->unit				= $detail->unit;
										$t_detailPurchase->description		= $detail->description;
										$t_detailPurchase->unitPrice		= $detail->unitPrice;
										$t_detailPurchase->tax				= $detail->tax;
										$t_detailPurchase->discount			= $detail->discount;
										$t_detailPurchase->amount			= $detail->amount;
										$t_detailPurchase->typeTax			= $detail->typeTax;
										$t_detailPurchase->subtotal			= $detail->subtotal;
										$t_detailPurchase->save();

										if ($detail->retentions()->exists()) 
										{
											foreach ($detail->retentions as $ret) 
											{
												$t_ret						= new App\RetentionPurchase();
												$t_ret->name				= $ret->name;
												$t_ret->amount				= $ret->amount;
												$t_ret->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_ret->save();
											}
										}

										if ($detail->taxes()->exists()) 
										{
											foreach ($detail->taxes as $tax) 
											{
												$t_taxes					= new App\TaxesPurchase();
												$t_taxes->name				= $tax->name;
												$t_taxes->amount			= $tax->amount;
												$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_taxes->save();
											}
										}
									}
									break;

								case 8:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->fDate			= Carbon::now();
									$t_request->status 			= 2;
									$t_request->save();
									
									$t_resource				= new Resource();
									$t_resource->title		= $request->resource->title;
									$t_resource->datetitle	= Carbon::now();
									$t_resource->total		= $request->resource->total;
									$t_resource->reference	= $request->resource->reference;
									$t_resource->currency	= $request->resource->currency;
									$t_resource->idFolio 	= $t_request->folio;
									$t_resource->idKind 	= $t_request->kind;
									$t_resource->idUsers 	= $request->idRequest;
									
									if ($request->resource->idpaymentMethod == 1) 
									{
										$t_resource->idEmployee	= $request->resource->idEmployee;
									}
									else
									{
										$t_resource->idEmployee	= null;
									}
									$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
									$t_resource->save();

									
									foreach ($request->resource->resourceDetail as $detail)
									{
										$t_detailResource				= new ResourceDetail();
										$t_detailResource->idresource	= $t_resource->idresource;
										$t_detailResource->concept		= $detail->concept;
										$t_detailResource->idAccAcc		= $detail->idAccAcc;
										$t_detailResource->amount		= $detail->amount;
										$t_detailResource->save();
									}
									break;
								
								default:
									# code...
									break;
							}
						}
						break;

					case 'yearly':
						
						if(date('m-d') == $request->day_yearly)
						{
							$kind = $request->kind;

							switch ($kind) 
							{
								case 1:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->taxPayment		= $request->taxPayment;
									$t_request->account 		= $request->idAccAcc;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject 		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->status 			= 2;
									$t_request->PaymentDate 	= $request->purchase->payment_date;
									$t_request->fDate 			= Carbon::now();
									$t_request->save();
									
									$t_purchase							= new Purchase();
									$t_purchase->title					= $request->purchase->title;
									$t_purchase->datetitle				= Carbon::now();
									$t_purchase->numberOrder			= $request->purchase->numberOrder;
									$t_purchase->reference				= $request->purchase->reference;
									$t_purchase->idProvider				= $request->purchase->idProvider;
									$t_purchase->notes					= $request->purchase->notes;
									$t_purchase->paymentMode			= $request->purchase->paymentMode;
									$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
									$t_purchase->billStatus				= $request->purchase->billStatus;
									$t_purchase->subtotales				= $request->purchase->subtotal;
									$t_purchase->tax					= $request->purchase->tax;
									$t_purchase->amount					= $request->purchase->amount;
									$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
									$t_purchase->idFolio 				= $t_request->folio;
									$t_purchase->idKind 				= $t_request->kind;
									$t_purchase->save();

									foreach ($request->purchase->detailPurchase as $detail) 
									{
										$t_detailPurchase					= new DetailPurchase();
										$t_detailPurchase->idPurchase		= $t_purchase->idPurchase;
										$t_detailPurchase->quantity			= $detail->quantity;
										$t_detailPurchase->unit				= $detail->unit;
										$t_detailPurchase->description		= $detail->description;
										$t_detailPurchase->unitPrice		= $detail->unitPrice;
										$t_detailPurchase->tax				= $detail->tax;
										$t_detailPurchase->discount			= $detail->discount;
										$t_detailPurchase->amount			= $detail->amount;
										$t_detailPurchase->typeTax			= $detail->typeTax;
										$t_detailPurchase->subtotal			= $detail->subtotal;
										$t_detailPurchase->save();

										if ($detail->retentions()->exists()) 
										{
											foreach ($detail->retentions as $ret) 
											{
												$t_ret						= new App\RetentionPurchase();
												$t_ret->name				= $ret->name;
												$t_ret->amount				= $ret->amount;
												$t_ret->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_ret->save();
											}
										}

										if ($detail->taxes()->exists()) 
										{
											foreach ($detail->taxes as $tax) 
											{
												$t_taxes					= new App\TaxesPurchase();
												$t_taxes->name				= $tax->name;
												$t_taxes->amount			= $tax->amount;
												$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
												$t_taxes->save();
											}
										}
									}
									break;

								case 8:
									$t_request					= new RequestModel();
									$t_request->kind			= $request->kind;
									$t_request->idEnterprise	= $request->idEnterprise;
									$t_request->idArea			= $request->idArea;
									$t_request->idDepartment	= $request->idDepartment;
									$t_request->idProject		= $request->idProject;
									$t_request->idRequest		= $request->idRequest;
									$t_request->idElaborate		= $request->idElaborate;
									$t_request->fDate			= Carbon::now();
									$t_request->status 			= 2;
									$t_request->save();
									
									$t_resource				= new Resource();
									$t_resource->title		= $request->resource->title;
									$t_resource->datetitle	= Carbon::now();
									$t_resource->total		= $request->resource->total;
									$t_resource->reference	= $request->resource->reference;
									$t_resource->currency	= $request->resource->currency;
									$t_resource->idFolio 	= $t_request->folio;
									$t_resource->idKind 	= $t_request->kind;
									$t_resource->idUsers 	= $request->idRequest;
									
									if ($request->resource->idpaymentMethod == 1) 
									{
										$t_resource->idEmployee	= $request->resource->idEmployee;
									}
									else
									{
										$t_resource->idEmployee	= null;
									}
									$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
									$t_resource->save();

									
									foreach ($request->resource->resourceDetail as $detail)
									{
										$t_detailResource				= new ResourceDetail();
										$t_detailResource->idresource	= $t_resource->idresource;
										$t_detailResource->concept		= $detail->concept;
										$t_detailResource->idAccAcc		= $detail->idAccAcc;
										$t_detailResource->amount		= $detail->amount;
										$t_detailResource->save();
									}
									break;
								
								default:
									# code...
									break;
							}
						}
						break;

					/*
					case 'dailyAt':
						
						$kind = $request->kind;

						switch ($kind) 
						{
							case 1:
								$t_request					= new RequestModel();
								$t_request->kind			= $request->kind;
								$t_request->taxPayment		= $request->taxPayment;
								$t_request->account 		= $request->idAccAcc;
								$t_request->idEnterprise	= $request->idEnterprise;
								$t_request->idArea			= $request->idArea;
								$t_request->idDepartment	= $request->idDepartment;
								$t_request->idProject 		= $request->idProject;
								$t_request->idRequest		= $request->idRequest;
								$t_request->idElaborate		= $request->idElaborate;
								$t_request->status 			= 2;
								$t_request->PaymentDate 	= $request->purchase->payment_date;
								$t_request->fDate 			= Carbon::now();
								$t_request->save();
								
								$t_purchase							= new Purchase();
								$t_purchase->title					= $request->purchase->title;
								$t_purchase->datetitle				= Carbon::now();
								$t_purchase->numberOrder			= $request->purchase->numberOrder;
								$t_purchase->reference				= $request->purchase->reference;
								$t_purchase->idProvider				= $request->purchase->idProvider;
								$t_purchase->notes					= $request->purchase->notes;
								$t_purchase->paymentMode			= $request->purchase->paymentMode;
								$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
								$t_purchase->billStatus				= $request->purchase->billStatus;
								$t_purchase->subtotales				= $request->purchase->subtotal;
								$t_purchase->tax					= $request->purchase->tax;
								$t_purchase->amount					= $request->purchase->amount;
								$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
								$t_purchase->idFolio 				= $t_request->folio;
								$t_purchase->idKind 				= $t_request->kind;
								$t_purchase->save();

								foreach ($request->purchase->detailPurchase as $detail) 
								{
									$t_detailPurchase					= new DetailPurchase();
									$t_detailPurchase->idPurchase		= $t_purchase->idPurchase;
									$t_detailPurchase->quantity			= $detail->quantity;
									$t_detailPurchase->unit				= $detail->unit;
									$t_detailPurchase->description		= $detail->description;
									$t_detailPurchase->unitPrice		= $detail->unitPrice;
									$t_detailPurchase->tax				= $detail->tax;
									$t_detailPurchase->discount			= $detail->discount;
									$t_detailPurchase->amount			= $detail->amount;
									$t_detailPurchase->typeTax			= $detail->typeTax;
									$t_detailPurchase->subtotal			= $detail->subtotal;
									$t_detailPurchase->save();

									if ($detail->retentions()->exists()) 
									{
										foreach ($detail->retentions as $ret) 
										{
											$t_ret						= new App\RetentionPurchase();
											$t_ret->name				= $ret->name;
											$t_ret->amount				= $ret->amount;
											$t_ret->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
											$t_ret->save();
										}
									}

									if ($detail->taxes()->exists()) 
									{
										foreach ($detail->taxes as $tax) 
										{
											$t_taxes					= new App\TaxesPurchase();
											$t_taxes->name				= $tax->name;
											$t_taxes->amount			= $tax->amount;
											$t_taxes->idDetailPurchase	= $t_detailPurchase->idDetailPurchase;
											$t_taxes->save();
										}
									}
								}
								break;

							case 8:
								$t_request					= new RequestModel();
								$t_request->kind			= $request->kind;
								$t_request->idEnterprise	= $request->idEnterprise;
								$t_request->idArea			= $request->idArea;
								$t_request->idDepartment	= $request->idDepartment;
								$t_request->idProject		= $request->idProject;
								$t_request->idRequest		= $request->idRequest;
								$t_request->idElaborate		= $request->idElaborate;
								$t_request->fDate			= Carbon::now();
								$t_request->status 			= 2;
								$t_request->save();
								
								$t_resource				= new Resource();
								$t_resource->title		= $request->resource->title;
								$t_resource->datetitle	= Carbon::now();
								$t_resource->total		= $request->resource->total;
								$t_resource->reference	= $request->resource->reference;
								$t_resource->currency	= $request->resource->currency;
								$t_resource->idFolio 	= $t_request->folio;
								$t_resource->idKind 	= $t_request->kind;
								$t_resource->idUsers 	= $request->idRequest;
								
								if ($request->resource->idpaymentMethod == 1) 
								{
									$t_resource->idEmployee	= $request->resource->idEmployee;
								}
								else
								{
									$t_resource->idEmployee	= null;
								}
								$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
								$t_resource->save();

								
								foreach ($request->resource->resourceDetail as $detail)
								{
									$t_detailResource				= new ResourceDetail();
									$t_detailResource->idresource	= $t_resource->idresource;
									$t_detailResource->concept		= $detail->concept;
									$t_detailResource->idAccAcc		= $detail->idAccAcc;
									$t_detailResource->amount		= $detail->amount;
									$t_detailResource->save();
								}
								break;
							
							default:
								# code...
								break;
						}
						break;
					*/
					
					default:
						# code...
						break;
				}
			}
		}

				
	}

	public function createPurchase($request)
	{
		$t_request					= new RequestModel();
		$t_request->kind			= $request->kind;
		$t_request->taxPayment		= $request->taxPayment;
		$t_request->idAccAcc 		= $request->idAccAcc;
		$t_request->idEnterprise	= $request->idEnterprise;
		$t_request->idArea			= $request->idArea;
		$t_request->idDepartment	= $request->idDepartment;
		$t_request->idProject 		= $request->idProject;
		$t_request->idRequest		= $request->idRequest;
		$t_request->idElaborate		= $request->idElaborate;
		$t_request->status 			= 2;
		$t_request->PaymentDate 	= $request->purchase->payment_date;
		$t_request->fDate 			= Carbon::now();
		$t_request->save();
		
		$t_purchase							= new Purchase();
		$t_purchase->title					= $request->purchase->title;
		$t_purchase->datetitle				= Carbon::now();
		$t_purchase->numberOrder			= $request->purchase->numberOrder;
		$t_purchase->reference				= $request->purchase->reference;
		$t_purchase->idProvider				= $request->purchase->idProvider;
		$t_purchase->notes					= $request->purchase->notes;
		$t_purchase->paymentMode			= $request->purchase->paymentMode;
		$t_purchase->typeCurrency			= $request->purchase->typeCurrency;
		$t_purchase->billStatus				= $request->purchase->billStatus;
		$t_purchase->subtotales				= $request->purchase->subtotal;
		$t_purchase->tax					= $request->purchase->tax;
		$t_purchase->amount					= $request->purchase->amount;
		$t_purchase->provider_has_banks_id 	= $request->purchase->provider_has_banks_id;
		$t_purchase->idFolio 				= $t_request->folio;
		$t_purchase->idKind 				= $t_request->kind;
		$t_purchase->save();

		foreach ($request->purchase->detailPurchase as $detail) 
		{
			$t_detailPurchase					= new DetailPurchase();
			$t_detailPurchase->idPurchase		= $t_purchase->idPurchase;
			$t_detailPurchase->quantity			= $detail->quantity;
			$t_detailPurchase->unit				= $detail->unit;
			$t_detailPurchase->description		= $detail->description;
			$t_detailPurchase->unitPrice		= $detail->unitPrice;
			$t_detailPurchase->tax				= $detail->tax;
			$t_detailPurchase->discount			= $detail->discount;
			$t_detailPurchase->amount			= $detail->amount;
			$t_detailPurchase->typeTax			= $detail->typeTax;
			$t_detailPurchase->subtotal			= $detail->subtotal;
			$t_detailPurchase->save();
		}
	}

	public function createResource($request)
	{
		$t_request					= new RequestModel();
		$t_request->kind			= $request->kind;
		$t_request->idEnterprise	= $request->idEnterprise;
		$t_request->idArea			= $request->idArea;
		$t_request->idDepartment	= $request->idDepartment;
		$t_request->idProject		= $request->idProject;
		$t_request->idRequest		= $request->idRequest;
		$t_request->idElaborate		= $request->idElaborate;
		$t_request->fDate			= Carbon::now();
		$t_request->status 			= 2;
		$t_request->save();
		
		$t_resource				= new Resource();
		$t_resource->title		= $request->resource->title;
		$t_resource->datetitle	= Carbon::now();
		$t_resource->total		= $request->resource->total;
		$t_resource->reference	= $request->resource->reference;
		$t_resource->currency	= $request->resource->currency;
		$t_resource->idFolio 	= $t_request->folio;
		$t_resource->idKind 	= $t_request->kind;
		$t_resource->idUsers 	= $request->idRequest;
		
		if ($request->resource->idpaymentMethod == 1) 
		{
			$t_resource->idEmployee	= $request->resource->idEmployee;
		}
		else
		{
			$t_resource->idEmployee	= null;
		}
		$t_resource->idpaymentMethod = $request->resource->idpaymentMethod;
		$t_resource->save();

		
		foreach ($request->resource->resourceDetail as $detail)
		{
			$t_detailResource				= new ResourceDetail();
			$t_detailResource->idresource	= $t_resource->idresource;
			$t_detailResource->concept		= $detail->concept;
			$t_detailResource->idAccAcc		= $detail->idAccAcc;
			$t_detailResource->amount		= $detail->amount;
			$t_detailResource->save();
		}
	}
}
