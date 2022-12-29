<?php

namespace App\Jobs;

use App\BreakdownWagesDetails;
use Excel;
use App\BreakdownWagesUploads;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use App\ControlDoc;
use App\ControlInternal;
use App\ControlRequisition;
use App\ControlPurchaseOrder;
use App\ControlRemittance;
use App\ControlBank;

class InternalControl implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $name;
	protected $short_name;
	protected $csvArr;

	public function __construct($name,$short_name,$csvArr)
	{
		$this->name       = $name;
		$this->short_name = $short_name;
		$this->csvArr     = $csvArr;
	}

	public function handle()
	{
		$name       = $this->name;
		$short_name = $this->short_name;
		$csvArr     = $this->csvArr;
		foreach($csvArr as $reqt){
			$this->createControlInternal($reqt,['name'=>$name,'short_name'=>$short_name]);
		}

	}

	public function createControlInternal($request,$documento=null)
	{
		if($documento!=null)
		{
			$controlDocs = ControlDoc::where('name',$documento['name'])->first();
			if(!isset($controlDocs))
			{
				$controlDocs = ControlDoc::create(['name'=> $documento['name'],'short_name'=> $documento['short_name'],]);
			}
		}

		$controlRequisition=ControlRequisition::create([
			'data_remittances' 	=> $request['fecha_remesa'],
			'cost_center' 		=> $request['centro_costos'],
			'WBS' 				=> $request['wbs'],
			'frentes' 			=> $request['frentes'],
			'EDT' 				=> $request['edt'],
			'cost_type' 		=> $request['tipo_costo'],
			'cost_description' 	=> $request['descripcion_costo'],
			'work_area' 		=> $request['area_trabajo'],
			'data_requisition' 	=> $request['fecha_requisicion'],
			'requisition' 		=> $request['requisicion'],
			'applicant' 		=> $request['solicitante'],
		]);
		$controlPurchaseOrder=ControlPurchaseOrder::create([
			'data'		=> $request['fecha_oc'],
			'number'	=> $request['numero_oc'],
			'provider'	=> $request['proveedor'],
		]);
		$controlRemittance=ControlRemittance::create([
			'remittances' 		=> $request['remesa'],
			'data' 				=> $request['fecha'],
			'invoice' 			=> $request['factura'],
			'invoice_amount'	=> $request['importe_fact'],
			'credit_note' 		=> $request['nota_credito'],
			'subtotal' 			=> $request['subtotal'],
			'discount' 			=> $request['descuento'],
			'IVA' 				=> $request['IVA'],
			'total'				=> $request['total'],
		]);
		$controlBank=ControlBank::create([
			'data' 			=> $request['fecha_banco'],
			'TRASF_CH' 		=> $request['TRASF_CH'],
			'amount' 		=> $request['importe'],
			'observations' 	=> $request['observaciones'],
			'note' 			=> $request['nota'],
		]);
		$controlInternal=ControlInternal::create([
			'control_requisitions_id' 		=> $controlRequisition->id,
			'control_purchase_orders_id' 	=> $controlPurchaseOrder->id,
			'control_remittances_id' 		=> $controlRemittance->id,
			'control_banks_id' 				=> $controlBank->id,
			'control_docs_id' 				=> $controlDocs->id,
		]);
		return $controlInternal->id;
	}

}
