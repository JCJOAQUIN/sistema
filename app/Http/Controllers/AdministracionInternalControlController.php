<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Excel;
use App\ControlDoc;
use App\ControlInternal;
use App\ControlRequisition;
use App\ControlPurchaseOrder;
use App\ControlRemittance;
use App\ControlBank;
use App\Functions\Files;
use App\Jobs\InternalControl;
use App\AppClass\Excel\ExcelExportClass;
use App\AppClass\Excel\SheetExcel;

class AdministracionInternalControlController extends Controller
{
	private $module_id = 262;
	private $pathDocs='/docs/internalControl/';

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data  = App\Module::find($this->module_id);
			return view('layouts.child_module',[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $requests)
	{
		if(Auth::user()->module->where('id',264)->count()>0)
		{
			(!isset($requests->requisicion_search) && !isset($requests->oc_search) && !isset($requests->remesa_search) && !isset($requests->banco_search)) ? $requisicion_search = true : $requisicion_search = $requests->requisicion_search;
			isset($requests->tableCotrolInternal) ? $tableCotrolInternal = $requests->tableCotrolInternal : $tableCotrolInternal = 1;
			isset($requests->state_search) ? $state_search = $requests->state_search : $state_search = true;
			$doc_search       = $requests->doc_search;
			$oc_search        = $requests->oc_search;
			$remesa_search    = $requests->remesa_search;
			$banco_search     = $requests->banco_search;
			$wbs_search       = $requests->wbs_search;
			$cost_type_search = $requests->cost_type_search;
			$provider_search  = $requests->provider_search;
			$id_search        = $requests->id_search;
			$requests         = $this->consSearch($requests)->orderBy('id', 'DESC')->paginate(10);
			$data             = App\Module::find($this->module_id);
			return view('administracion.control_interno.busqueda',[
				'id'                  => $data['father'],
				'title'               => $data['name'],
				'details'             => $data['details'],
				'child_id'            => $this->module_id,
				'requests'            => $requests,
				'tableCotrolInternal' => $tableCotrolInternal,
				'id_search'           => $id_search,
				'state_search'        => $state_search,
				'wbs_search'          => $wbs_search,
				'cost_type_search'    => $cost_type_search,
				'provider_search'     => $provider_search,
				'requisicion_search'  => $requisicion_search,
				'oc_search'           => $oc_search,
				'remesa_search'       => $remesa_search,
				'banco_search'        => $banco_search,
				'doc_search'          => $doc_search,
				'option_id'           => 264, 
			]);
		}
	}

	//Descargar platilla de control interno masivo
	public function show(Request $request,$id)
	{
		return \Storage::disk('reserved')->download('/massive_requisition/plantilla_control_interno.xlsm');
	}

	public function create()
	{
		if(Auth::user()->module->where('id',263)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.control_interno.alta',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 263, 
			]);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$this->createControlInternal($request);
			return redirect('/administration/internal_control/follow');
		}
	}

	public function storeMasive(Request $request)
	{
		if(isset($request) && $request->csv_file != "" && $request->file('csv_file')->isValid())
		{
			$name2 = '/massive_requisition/Control_Interno'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
			\Storage::disk('reserved')->put($name2,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
			$path   = \Storage::disk('reserved')->path($name2);
			$csvArr = array();
			if (($handle = fopen($path, "r")) !== FALSE)
			{
				$first	= true;
				while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
				{
					if($first)
					{
						$data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
						$first   = false;
					}
					$csvArr[] = $data;
				}
				fclose($handle);
			}
			array_walk($csvArr, function(&$a) use ($csvArr)
			{
				$a = array_combine($csvArr[0], $a);
			});
			$name       = 'ControlInterno_Registros('.(count($csvArr)-1).')_'.date('Y-m-d').'_'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
			$short_name = 'Control '.date('Y-m-d').' No. Registros ('.(count($csvArr)-1).')';
			array_shift($csvArr);
			dispatch(new InternalControl($name,$short_name,$csvArr));
			\Storage::disk('public')->put($this->pathDocs.$name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
			\Storage::disk('reserved')->delete($name2);
			$alert = "swal('Documento cargado exitosamente', 'Nota: La infomación puede demorar en visualizarse dependiendo de la cantidad de registros.', 'success');";
			return redirect('/administration/internal_control/follow')->with('alert',$alert);
		}
	}

	public function edit($id)
	{
		$request = ControlInternal::where('id',$id)->first();
		if(Auth::user()->module->where('id',263)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('administracion.control_interno.alta',
			[
				'id'        => $data['father'],
				'title'     => $data['name'],
				'details'   => $data['details'],
				'child_id'  => $this->module_id,
				'option_id' => 263, 
				'request'   => $request, 
			]);
		}
	}

	public function update(Request $request,$id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			ControlRequisition::where('id',$id)->update(
			[
				'data_remittances' => $request->fecha_remesa,
				'cost_center'      => $request->centro_costos,
				'WBS'              => $request->wbs,
				'frentes'          => $request->frentes,
				'EDT'              => $request->edt,
				'cost_type'        => $request->tipo_costo,
				'cost_description' => $request->descripcion_costo,
				'work_area'        => $request->area_trabajo,
				'data_requisition' => $request->fecha_requisicion,
				'requisition'      => $request->requisicion,
				'applicant'        => $request->solicitante,
			]);
			ControlPurchaseOrder::where('id',$id)->update(
				[
				'data'     => $request->fecha_oc,
				'number'   => $request->numero_oc,
				'provider' => $request->proveedor,
			]);
			ControlRemittance::where('id',$id)->update(
			[
				'remittances'    => $request->remesa,
				'data'           => $request->fecha,
				'invoice'        => $request->factura,
				'invoice_amount' => $request->importe_fact,
				'credit_note'    => $request->nota_credito,
				'subtotal'       => $request->subtotal,
				'discount'       => $request->descuento,
				'IVA'            => $request->IVA,
				'total'          => $request->total,
			]);
			ControlBank::where('id',$id)->update(
			[
				'data'         => $request->fecha_banco,
				'TRASF_CH'     => $request->TRASF_CH,
				'amount'       => $request->importe,
				'observations' => $request->observaciones,
				'note'         => $request->nota,
			]);
			return redirect('/administration/internal_control/follow');
		}
	}

	public function destroy($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$controlInternal=ControlInternal::where('id',$id)->first();
			$controlInternal->update(
			[
				'state' => !$controlInternal->state,
			]);
			return redirect('/administration/internal_control/follow');
		}
	}

	public function export(Request $requests)
	{
		if (Auth::user()->module->where('id',264)->count()>0)
		{
			$requisicion_search  = $requests->requisicion_search;
			$oc_search           = $requests->oc_search;
			$remesa_search       = $requests->remesa_search;
			$banco_search        = $requests->banco_search;
			$requests            = $this->consSearch($requests)->orderBy('id', 'DESC')->get();
			$exp                 = new ExcelExportClass('Acumulado_de_Remesa');
			$titleRequisition    = 'REQUISICION';
			$titleOC             = 'ORDEN DE COMPRA';
			$titleRemesa         = 'REMESA';
			$titleBanck          = 'BANCOS';
			$subTitleRequisition = ['Fecha Remesa','Centro de Costos','WBS','Frentes','EDT','Tipo de Costo','Descripcion de Costo','Area de Trabajo','Fecha de Requisicion','Requisicion','Solicitante'];
			$subTitleOC          = ['Fechas','Numero','Proveedor'];
			$subTitleRemesa      = ['Remesa','Fecha','Factura','Importe FACT','Nota Credito','Subtotal','Retencion/Descuento','IVA','Total'];
			$subTitleBanck       = ['Fecha','TRASF-CH','Importe','Observaciones','Nota'];
			$content             = array();
			foreach ($requests as $item)
			{
				$contentRow = array();
				if($requisicion_search)
				{
					$contentRow[] = $item->controlRequisition->data_remittances;
					$contentRow[] = $item->controlRequisition->cost_center;
					$contentRow[] = $item->controlRequisition->WBS;
					$contentRow[] = $item->controlRequisition->frentes;
					$contentRow[] = $item->controlRequisition->EDT;
					$contentRow[] = $item->controlRequisition->cost_type;
					$contentRow[] = $item->controlRequisition->cost_description;
					$contentRow[] = $item->controlRequisition->work_area;
					$contentRow[] = $item->controlRequisition->data_requisition;
					$contentRow[] = $item->controlRequisition->requisition;
					$contentRow[] = $item->controlRequisition->applicant;
				}
				if($oc_search)
				{
					$contentRow[] = $item->controlPurchaseOrder->data;
					$contentRow[] = $item->controlPurchaseOrder->number;
					$contentRow[] = $item->controlPurchaseOrder->provider;
				}
				if($remesa_search)
				{
					$contentRow[] = $item->controlRemittance->remittances;
					$contentRow[] = $item->controlRemittance->data;
					$contentRow[] = $item->controlRemittance->invoice;
					$contentRow[] = $item->controlRemittance->invoice_amount;
					$contentRow[] = $item->controlRemittance->credit_note;
					$contentRow[] = $item->controlRemittance->subtotal;
					$contentRow[] = $item->controlRemittance->discount;
					$contentRow[] = $item->controlRemittance->IVA;
					$contentRow[] = $item->controlRemittance->total;
				}
				if($banco_search)
				{
					$contentRow[] = $item->controlBank->data;
					$contentRow[] = $item->controlBank->TRASF_CH;
					$contentRow[] = $item->controlBank->amount;
					$contentRow[] = $item->controlBank->observations;
					$contentRow[] = $item->controlBank->note;
				}
				$mixCells = null;
				$content[] = array('row'=>$contentRow,'mixes'=>$mixCells);
			}
			$objSheet = new SheetExcel($content,'Acumulado_de_Remesa');
			$colors   = [['#B0C4DE','#000000'],['#87CEFA','#000000']];
			$colorNum = 0;
			if($requisicion_search)
			{
				$objSheet->AddHead($titleRequisition,$subTitleRequisition,$colors[$colorNum][0],$colors[$colorNum][1]);
				($colorNum==0) ? $colorNum = 1 : $colorNum = 0; 
			}
			if($oc_search)
			{
				$objSheet->AddHead($titleOC,$subTitleOC,$colors[$colorNum][0],$colors[$colorNum][1]);
				($colorNum==0) ? $colorNum = 1 : $colorNum = 0; 
			}
			if($remesa_search)
			{
				$objSheet->AddHead($titleRemesa,$subTitleRemesa,$colors[$colorNum][0],$colors[$colorNum][1]);
				($colorNum==0) ? $colorNum = 1 : $colorNum = 0; 
			}
			if($banco_search)
			{
				$objSheet->AddHead($titleBanck,$subTitleBanck,$colors[$colorNum][0],$colors[$colorNum][1]);
				($colorNum==0) ? $colorNum = 1 : $colorNum = 0; 
			}
			$exp->AddSheets($objSheet);
			$exp->DownloadExcel();
		}
	}

	public function consSearch(Request $requests)
	{
		if(isset($requests->id_search))
		{
			$id_search = $requests->id_search;
		}
		else
		{
			$id_search = '';
		}
		if(isset($requests->state_search))
		{
			$state_search = $requests->state_search;
		}
		else
		{
			$state_search = true;
		}
		$doc_search       = $requests->doc_search;
		$wbs_search       = $requests->wbs_search;
		$cost_type_search = $requests->cost_type_search;
		$provider_search  = $requests->provider_search;
		return $requests  = ControlInternal::where(function ($query) use ($id_search,$doc_search,$state_search,$wbs_search,$cost_type_search,$provider_search)
		{
			if($id_search!='')
			{
				$query->where('id',$id_search);
			}
			if($state_search!='')
			{
				$query->where('state',$state_search);
			}
			if($doc_search!='')
			{
				$query->where('control_docs_id',$doc_search);
			}
			if($wbs_search!='')
			{
				$query->whereHas('controlRequisition',function ($query) use ($wbs_search)
				{
					$query->where('WBS',$wbs_search);
				});
			}
			if($cost_type_search!='')
			{
				$query->whereHas('controlRequisition',function ($query) use ($cost_type_search){
					$query->where('cost_type',$cost_type_search);
				});
			}	
			if($provider_search!='')
			{
				$query->whereHas('controlPurchaseOrder',function ($query) use ($provider_search){
					$query->where('provider',$provider_search);
				});
			}
		});
	}

	public function createControlInternal($request)
	{
		$controlRequisition=ControlRequisition::create(
		[
			'data_remittances' => $request['fecha_remesa'],
			'cost_center'      => $request['centro_costos'],
			'WBS'              => $request['wbs'],
			'frentes'          => $request['frentes'],
			'EDT'              => $request['edt'],
			'cost_type'        => $request['tipo_costo'],
			'cost_description' => $request['descripcion_costo'],
			'work_area'        => $request['area_trabajo'],
			'data_requisition' => $request['fecha_requisicion'],
			'requisition'      => $request['requisicion'],
			'applicant'        => $request['solicitante'],
		]);
		$controlPurchaseOrder=ControlPurchaseOrder::create(
		[
			'data'     => $request['fecha_oc'],
			'number'   => $request['numero_oc'],
			'provider' => $request['proveedor'],
		]);
		$controlRemittance=ControlRemittance::create(
		[
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
		$controlBank=ControlBank::create(
		[
			'data' 			=> $request['fecha_banco'],
			'TRASF_CH' 		=> $request['TRASF_CH'],
			'amount' 		=> $request['importe'],
			'observations' 	=> $request['observaciones'],
			'note' 			=> $request['nota'],
		]);
		$controlInternal=ControlInternal::create(
		[
			'control_requisitions_id'    => $controlRequisition->id,
			'control_purchase_orders_id' => $controlPurchaseOrder->id,
			'control_remittances_id'     => $controlRemittance->id,
			'control_banks_id'           => $controlBank->id,
			'control_docs_id'            => null,
		]);
		return $controlInternal->id;
	}
}
