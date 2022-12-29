<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Excel;

class ConstructionProcurementController extends Controller
{
	private $module_id = 309;

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
	public function upload()
	{
		if(Auth::user()->module->where('id',310)->count()>0)
		{
			$data  = App\Module::find(309);
			return view('obra.procuracion.upload',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => 309,
					'option_id' => 310
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function download()
	{
		if(Auth::user()->module->where('id',310)->count()>0)
		{
			return \Storage::disk('reserved')->download('/procurement/procuracion_plantilla.xlsx');
		}
		else
		{
			return abort(404);
		}
	}

	public function fileUpload(Request $request)
	{
		if(Auth::user()->module->where('id',310)->count()>0)
		{
			if($request->file('csv_file') == "")
			{
				$alert	= "swal('', 'Debe cargar un archivo, intente de nuevo por favor', 'error');";
				return back()->with('alert',$alert);	
			}
			if($request->file('csv_file')->isValid())
			{
				$delimiters = [";" => 0, "," => 0];
				$handle     = fopen($request->file('csv_file'), "r");
				$firstLine  = fgets($handle);
				fclose($handle); 
				foreach ($delimiters as $delimiter => &$count)
				{
					$count = count(str_getcsv($firstLine, $delimiter));
				}
				$separator = array_search(max($delimiters), $delimiters);
				if($separator == $request->separator)
				{
					$name		= '/procurement/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('csv_file')->getClientOriginalExtension();
					\Storage::disk('reserved')->put($name,mb_convert_encoding(\File::get($request->file('csv_file')),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));
					$path		= \Storage::disk('reserved')->path($name);
					$csvArr		= array();
					if (($handle = fopen($path, "r")) !== FALSE)
					{
						$first  = true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== FALSE)
						{
							if($first)
							{
								$data[0]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
								$first		= false;
							}
							$csvArr[]	= $data;
						}
						fclose($handle);
					}
					array_walk($csvArr, function(&$a) use ($csvArr)
					{
						$a = array_combine($csvArr[0], $a);
					});
					array_shift($csvArr);

					$headers = [
						'wbs',
						'nombre_wbs',
						'no_rq',
						'fecha_recepcion_rq',
						'comprador',
						'estatus',
						'pendiente_documental',
						'fecha_oc',
						'pedido_oc',
						'descripcion_pedido',
						'proveedor',
						'partida',
						'tag',
						'pulgadas_mat',
						'unidad',
						'cantidad',
						'concepto',
						'moneda',
						'precio_unitario_mxn',
						'importe_mxn',
						'iva_16',
						'total_partida_mxn',
						'total_oc_mxn',
						'precio_unitario_usd',
						'importe_usd',
						'iva_16_2',
						'total_partida_usd',
						'total_oc_usd',
						'incoterm',
						'hitos',
						'hito_descrtipcion',
						'factura',
						'fecha',
						'monto_con_iva',
						'total_facturado',
						'fecha_pago',
						'est',
						'fianza',
						'estatus_fianza',
						'entrega_contractual',
						'recepcion_en_sitio',
						'observaciones'
					];
					if(empty($csvArr[0]['wbs']) || empty($csvArr[0]['nombre_wbs']) || empty($csvArr[0]['no_rq']) || empty($csvArr[0]['pedido_oc']) || empty($csvArr[0]['proveedor']) || empty($csvArr[0]['concepto']) || array_diff($headers, array_keys($csvArr[0])))
					{
						$alert	= "swal('', 'Ocurrió un error al momento de cargar el archivo, por favor verifique su información.', 'error');";
						return back()->with('alert',$alert);	
					}
					
					$data = App\Module::find($this->module_id);
					return view('obra.procuracion.verificar_carga',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->module_id,
						'option_id' => 310,
						'csv'       => $csvArr,
						'fileName'  => $name,
						'delimiter' => $request->separator
					]);
				}
				else
				{
					$alert	= "swal('', 'Error en el separador, por favor verifique e intente de nuevo.', 'error');";
					return back()->with('alert',$alert);
				}
			}
			else
			{
				$alert	= "swal('', 'Ha ocurrido un error al cargar el archivo, intente de nuevo por favor', 'error');";
				return back()->with('alert',$alert);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function cancelUpload(Request $request)
	{
		if(Auth::user()->module->where('id',310)->count()>0)
		{
			\Storage::disk('reserved')->delete($request->fileName);
			return redirect()->route('construction.procurement.upload');
		}
		else
		{
			return abort(404);
		}
	}

	public function massiveContinue(Request $request)
	{
		if(Auth::user()->module->where('id',310)->count()>0)
		{
			$path   = \Storage::disk('reserved')->path($request->fileName);
			$csvArr = array();
			if(($handle = fopen($path, "r")) !== FALSE)
			{
				$first = true;
				while (($data = fgetcsv($handle, 0, $request->delimiter)) !== FALSE)
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
			array_shift($csvArr);
			foreach($csvArr as $key => $data)
			{
				try
				{
					$count = 0;
					foreach ($data as $dataKey => $value)
					{
						if(trim($value) == '')
						{
							$data[$dataKey] = null;
							$count++;
						}
					}
					if($count >= 42)
					{
						$csvArr[$key]['id']     = '';
						$csvArr[$key]['status'] =  'Fila vacía';
					}
					else
					{
						$exists = App\ProcurementData::where('wbs',$data['wbs'])
							->where('no_rq',$data['no_rq'])
							->where('proveedor',$data['proveedor'])
							->where('pedido_oc',$data['pedido_oc'])
							->where('partida',$data['partida'])
							->first();
						if($exists != '')
						{
							$data['user_id']    = Auth::user()->id;
							foreach ($data as $dataKey => $value)
							{
								if(trim($value) != '')
								{
									$exists->$dataKey = $data[$dataKey];
								}
							}
							$exists->save();
							$csvArr[$key]['id'] = $exists->id;
							$csvArr[$key]['status'] =  'Actualizado';
						}
						else
						{
							$data['user_id']    = Auth::user()->id;
							$savedData          = App\ProcurementData::create($data);
							$csvArr[$key]['id'] = $savedData->id;
							$csvArr[$key]['status'] =  'Guardado';
						}
					}
				}
				catch (\Exception $e)
				{
					$csvArr[$key]['id'] = '';
					$csvArr[$key]['status'] =  'Error';
				}
			}
			$data    = App\Module::find($this->module_id);
			return view('obra.procuracion.resultado_carga',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 310,
					'csv'       => $csvArr
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',311)->count()>0)
		{
			$data            = App\Module::find(309);
			$procurementData = App\ProcurementData::select('wbs', 'nombre_wbs', 'no_rq', 'fecha_recepcion_rq', 'comprador', 'estatus', 'pendiente_documental', 'fecha_oc', 'pedido_oc', 'descripcion_pedido', 'proveedor', 'partida', 'tag', 'pulgadas_mat', 'unidad', 'cantidad', 'concepto', 'moneda', 'precio_unitario_mxn', 'importe_mxn', 'iva_16', 'total_partida_mxn', 'total_oc_mxn', 'precio_unitario_usd', 'importe_usd', 'iva_16_2', 'total_partida_usd', 'total_oc_usd', 'incoterm', 'hitos', 'hito_descrtipcion', 'factura', 'fecha', 'monto_con_iva', 'total_facturado', 'fecha_pago', 'est', 'fianza', 'estatus_fianza', 'entrega_contractual', 'recepcion_en_sitio', 'observaciones')
				->where(function($q) use($request)
				{
					if($request->wbs != '')
					{
						$q->whereRaw('wbs LIKE "%'.$request->wbs.'%"');
					}
					if($request->rq != '')
					{
						$q->whereRaw('no_rq LIKE "%'.$request->rq.'%"');
					}
					if($request->oc != '')
					{
						$q->whereRaw('pedido_oc LIKE "%'.$request->oc.'%"');
					}
					if($request->comprador != '')
					{
						$q->whereRaw('comprador LIKE "%'.$request->comprador.'%"');
					}
					if($request->proveedor != '')
					{
						$q->whereRaw('proveedor LIKE "%'.$request->proveedor.'%"');
					}
					if($request->concepto != '')
					{
						$q->whereRaw('concepto LIKE "%'.$request->concepto.'%"');
					}
				})
				->orderBy('created_at', "desc")
				->paginate(15);
			return view('obra.procuracion.search',
				[
					'id'              => $data['father'],
					'title'           => $data['name'],
					'details'         => $data['details'],
					'child_id'        => 309,
					'option_id'       => 311,
					'wbs'              => $request->wbs,
					'rq'              => $request->rq,
					'oc'              => $request->oc,
					'comprador'       => $request->comprador,
					'proveedor'       => $request->proveedor,
					'concepto'        => $request->concepto,
					'procurementData' => $procurementData
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function searchExport(Request $request)
	{
		if(Auth::user()->module->where('id',311)->count() > 0)
		{
			$data = App\ProcurementData::select('wbs', 'nombre_wbs', 'no_rq', 'fecha_recepcion_rq', 'comprador', 'estatus', 'pendiente_documental', 'fecha_oc', 'pedido_oc', 'descripcion_pedido', 'proveedor', 'partida', 'tag', 'pulgadas_mat', 'unidad', 'cantidad', 'concepto', 'moneda', 'precio_unitario_mxn', 'importe_mxn', 'iva_16', 'total_partida_mxn', 'total_oc_mxn', 'precio_unitario_usd', 'importe_usd', 'iva_16_2', 'total_partida_usd', 'total_oc_usd', 'incoterm', 'hitos', 'hito_descrtipcion', 'factura', 'fecha', 'monto_con_iva', 'total_facturado', 'fecha_pago', 'est', 'fianza', 'estatus_fianza', 'entrega_contractual', 'recepcion_en_sitio', 'observaciones')
				->where(function($q) use($request)
				{
					if($request->wbs != '')
					{
						$q->whereRaw('wbs LIKE "%'.$request->wbs.'%"');
					}
					if($request->rq != '')
					{
						$q->whereRaw('no_rq LIKE "%'.$request->rq.'%"');
					}
					if($request->oc != '')
					{
						$q->whereRaw('pedido_oc LIKE "%'.$request->oc.'%"');
					}
					if($request->comprador != '')
					{
						$q->whereRaw('comprador LIKE "%'.$request->comprador.'%"');
					}
					if($request->proveedor != '')
					{
						$q->whereRaw('proveedor LIKE "%'.$request->proveedor.'%"');
					}
					if($request->concepto != '')
					{
						$q->whereRaw('concepto LIKE "%'.$request->concepto.'%"');
					}
				})
				->get();
			Excel::create('procuracion_plantilla', function($excel) use ($data)
			{
				$excel->sheet('Hoja1',function($sheet) use ($data)
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
					$sheet->cell('A1:AP1', function($cells)
					{
						$cells->setBackground('#253660');
						$cells->setFontColor('#ffffff');
					});
					$sheet->row(1,['wbs', 'nombre_wbs', 'no_rq', 'fecha_recepcion_rq', 'comprador', 'estatus', 'pendiente_documental', 'fecha_oc', 'pedido_oc', 'descripcion_pedido', 'proveedor', 'partida', 'tag', 'pulgadas_mat', 'unidad', 'cantidad', 'concepto', 'moneda', 'precio_unitario_mxn', 'importe_mxn', 'iva_16', 'total_partida_mxn', 'total_oc_mxn', 'precio_unitario_usd', 'importe_usd', 'iva_16_2', 'total_partida_usd', 'total_oc_usd', 'incoterm', 'hitos', 'hito_descrtipcion', 'factura', 'fecha', 'monto_con_iva', 'total_facturado', 'fecha_pago', 'est', 'fianza', 'estatus_fianza', 'entrega_contractual', 'recepcion_en_sitio', 'observaciones']);
					foreach ($data as $item)
					{
						$sheet->appendRow($item->toArray());
					}
				});
			})->export('xlsx');
		}
		else
		{
			return abort(404);
		}

	}
}
