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

class ReportAdministrationWarehouseController extends Controller
{
	private $module_id = 96;
	public function warehouseReport(Request $request)
	{
		if (Auth::user()->module->where('id',100)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$cat			= $request->cat;
			$place_id		= $request->place_id;
			$idEnterprise	= $request->idEnterprise;
			$account_id		= $request->account_id;
			$concept		= $request->concept;
			$mindate		= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$warehouses 	= collect();
			$computers 		= collect();

			if (isset($cat) && $cat != "" && $cat != "computo") 
			{
				$warehouses = App\Warehouse::where(function($query) use ($idEnterprise, $mindate, $maxdate, $place_id, $account_id, $cat)
				{
					if ($idEnterprise != "")
					{
						if ($idEnterprise != "todas")
						{
							$query->whereHas('lot',function($q) use($idEnterprise)
							{
								$q->where('lots.idEnterprise',$idEnterprise);
							});
						}
					}
					if ($account_id != "")
					{
						if ($account_id != "todas")
						{
							$query->whereHas('lot',function($q) use($account_id)
							{
								$q->where('lots.account',$account_id);
							});
						}
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereHas('lot',function($q) use($mindate,$maxdate)
						{
							$q->whereBetween('lots.date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
						});
					}
					if ($place_id != null )
					{
						$query->where('place_location',$place_id);
					}
					$query->whereHas('lot',function($q) use($mindate,$maxdate)
					{
						$q->where('lots.status',2);
					});
					if($cat)
					{
						$query->where('warehouseType',$cat);
					}
				})
				->whereHas('cat_c', function($query) use($concept)
				{
					if($concept != "")
					{
						$query->where('description','LIKE','%'.$concept.'%');
					}
				})
				->groupBy('concept','place_location')
				->selectRaw('
					idwarehouse,
					warehouseType,
					place_location,
					quantity,
					warehouses.quantity,
					sum(warehouses.quantity) as quantity,
					concept')
				->where('status',1)
				->where('quantity','>',0)
				->paginate(10);
			}
			elseif (isset($cat) && $cat != "" && $cat == "computo") 
			{
				$computers = App\ComputerEquipment::where(function($query) use  ($idEnterprise, $mindate, $maxdate, $place_id, $account_id, $concept)
				{
					if($concept != "")
					{
						$query->where('brand',$concept);
					}
					if($idEnterprise != "")
					{
						if ($type != "todos") 
						{
							$query->where('idEnterprise',$idEnterprise);
						}
					}
					if($account_id != "")
					{
						if ($account != "todos") 
						{
							$query->where('account',$account_id);
						}
					}
					if($place_id != "")
					{
						if ($place_id != "todos") 
						{
							$query->where('place_location',$place_id);
						}
					}
					if ($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
					}
				})
				->where('status',1)
				->where('quantity','>',0)
				->with('enterprise','accounts','location')
				->paginate(10);
			}

			return view('reporte.administracion.almacen',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 100,
					'computers'		=> $computers,
					'warehouses'	=> $warehouses,
					'cat'			=> $cat,
					'place_id'		=> $place_id,
					'idEnterprise'	=> $idEnterprise,
					'account_id'	=> $account_id,
					'concept'		=> $concept,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
				]);
		}
	}

	public function warehouseTable(Request $request)
	{
		$table      = "";
		$enterprise = $request->idEnterprise;
		$name       = $request->name;
		$material   = $request->material;
		$max        = null;
		$min        = null;

		if ($request->mindate != null)
		{
			$date1      = strtotime($request->mindate);
			$mindate    = date('Y-m-d',$date1);
			$date2      = strtotime($request->maxdate);
			$maxdate    = date('Y-m-d',$date2);
			$min        = $mindate;
			$max        = $maxdate;
		}

		$searchUser     = App\User::select('users.id')
							->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')
							->get();

		$lots       = App\Lot::join('warehouse','lots.idlot','warehouses.idLot')
						->where(function($query) use ($enterprise,$name,$searchUser,$min,$max)
						{
							if($name != "")
							{
								$query->whereIn('lots.idElaborate',$searchUser);
							}
							if ($enterprise != "")
							{
								if ($enterprise != "todas")
								{
									$query->where('lots.idEnterprise',$enterprise);
								}
							}
							if ($min != "" && $max != "")
							{
								$query->whereBetween('lots.date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
							}
						})
						->where('material','LIKE','%'.$material.'%')
						->groupBy('material')
						->selectRaw('*, sum(warehouses.quantity) as quantity')
						->where('status',1)
						->get();

		if ($enterprise == null && $name == null && $min == null && $max == null && $material == null)
		{
			$table .= "";
		}
		elseif(count($lots) > 0)
		{
			$table .=   "<form method='get' action='".route('report.warehouse.excel')."' accept-charset='UTF-8' id='formsearch'>";
			$table .=   "<input type='hidden' name='enterprise_export' value='".$enterprise."'>";
			$table .=   "<input type='hidden' name='name_export' value='".$name."'>";
			$table .=   "<input type='hidden' name='min_export' value='".$min."'>";
			$table .=   "<input type='hidden' name='max_export' value='".$max."'>";
			$table .=   "<input type='hidden' name='material_export' value='".$material."'>";
			$table .=   "<div style='float: right'><label class='label-form'>Exportar a Excel </label><button class='btn btn-green export' type='submit'><span class='icon-file-excel'></span></button></div></form>";
			$table .=   "<div class='table-responsive'>".
						"<table class='table table-striped' id='table-warehouse'>".
						"<thead class='thead-dark'>".
						"<th>Cantidad</th>".
						"<th>Producto/Material</th>".
						"<th>Acción</th>".
						"</thead>";
						foreach($lots as $lot)
						{
							$table .=   "<tr>".
										"<td>".
										"". $lot->quantity ."".
										"</td>".
										"<td>".
										"". $lot->material ."".
										"<input type='hidden' class='material' value='". $lot->material ."'>".
										"</td>".
										"<td>".
										"<button type='button' class='btn follow-btn detail-stationery' title='Detalles'>".
										"<span class='icon-search'></span>".
										"</button>".
										"</td>".
										"</tr>";
						}
			$table .=   "</table>".
						"</div>".
						"<div id='detail'></div>".
						"<br>";
		}
		else
		{
			$table .= "<div id='not-found' style='display:block;'>Resultado no encontrado</div>";
		}

		return Response($table);
	}

	public function warehouseExcel(Request $request)
	{
		$enterprise = $request->enterprise_export;
		$name       = $request->name_export;
		$material   = $request->material_export;
		$min        = null;
		$max        = null;

		if ($request->min_export != null)
		{
			$date1      = strtotime($request->min_export);
			$mindate    = date('Y-m-d',$date1);
			$date2      = strtotime($request->max_export);
			$maxdate    = date('Y-m-d',$date2);
			$min        = $mindate;
			$max        = $maxdate;
		}

		$searchUser     = App\User::select('users.id')->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.$name.'%')->get();

		Excel::create('Reporte-Inventario', function($excel) use ($name, $min, $max, $enterprise, $material, $searchUser)
		{
			$excel->sheet('Datos',function($sheet) use ($name, $min, $max, $enterprise, $material, $searchUser)
			{
				$sheet->setStyle([
							'font' => [
								'name'  => 'Calibri',
								'size'  => 12
							],
							'alignment' => [
								'vertical' => 'center',
							]
					]);
					$sheet->setColumnFormat(array(
						'C' => '"$"#,##0.00_-',
						'D' => '"$"#,##0.00_-',
						'G' => '"$"#,##0.00_-',
					));
					$sheet->mergeCells('A1:G1');

					$sheet->cell('A1:G1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:G2', function($cells)
					{
						$cells->setBackground('#1d353d');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:G2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});

				$sheet->row(1,['Repoorte de inventario']);
				$sheet->row(2,['Lote','Empresa','Inversión Total ($)','Inversión en Artículos ($)','Cantidad','Artículo','Importe por Artículos ($)']);
				
				$lots       = App\Lot::where(function($query) use ($enterprise,$name,$searchUser,$min,$max)
								{
									if($name != "")
									{
										$query->whereIn('lots.idElaborate',$searchUser);
									}
									if ($enterprise != "")
									{
										if ($enterprise != "todas")
										{
											$query->where('lots.idEnterprise',$enterprise);
										}
									}
									if ($min != "" && $max != "")
									{
										$query->whereBetween('lots.date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
									}
								})
								->get();
				$beginMerge = 2;
				foreach ($lots as $lot)
				{
					$tempCount = 0;
					$row    = [];
					$row[] = $lot->idlot;
					$row[] = $lot->enterprise->name;
					$row[] = $lot->total;
					$row[] = $lot->articles;

					$first  = true;

					$merge = false;
					foreach($lot->warehouseStationary as $art)
					{
						if ($art->status != 0) 
						{
							if (!$first)
							{
								$row    = array();
								$row[]  = '';
								$row[]  = '';
								$row[]  = '';
								$row[]  = '';
							}
							else
							{
								$first = false;
								$beginMerge++;
								$merge = true;
							}
							$row[] = $art->quantity;
							$row[] = $art->material;
							$row[] = $art->amount;
							$tempCount++;
							
							$sheet->appendRow($row);
						}
						
					}
					if($merge){
						$endMerge = $beginMerge+$tempCount-1;
						$sheet->mergeCells('A'.$beginMerge.':A'.$endMerge);
						$sheet->mergeCells('B'.$beginMerge.':B'.$endMerge);
						$sheet->mergeCells('C'.$beginMerge.':C'.$endMerge);
						$sheet->mergeCells('D'.$beginMerge.':D'.$endMerge);
						$beginMerge = $endMerge;
					}
				}
			});
		})->export('xls');
	}

	public function warehouseDetail(Request $request)
	{
		$warehouses = App\Warehouse::select('warehouses.*')
			->where(function($query) use ($request)
			{
				$query->where('warehouses.warehouseType',$request->warehouse_kind)
				->where('places.place',$request->place)
				->whereHas('cat_c', function($query) use($request)
				{
					$query->where('description',$request->concept_warehouse);
				});
				if ($request->enterprise != "")
				{
					$query->whereHas('lot',function($q) use($request)
					{
						$q->where('lots.idEnterprise',$request->enterprise);
					});
				}
				if ($request->account_id != "")
				{
					$query->whereHas('lot',function($q) use($request)
					{
						$q->where('lots.account',$request->account_id);
					});
				}
				$min = null;
				$max = null;
				if ($request->mindate != null)
				{
					$date1      = strtotime($request->mindate);
					$mindate    = date('Y-m-d',$date1);
					$date2      = strtotime($request->maxdate);
					$maxdate    = date('Y-m-d',$date2);
					$min        = $mindate;
					$max        = $maxdate;
				}
				if ($min != "" && $max != "")
				{
					$query->whereHas('lot',function($q) use($min,$max)
					{
						$q->whereBetween('lots.date',[''.$min.' '.date('00:00:00').'',''.$max.' '.date('23:59:59').'']);
					});
				}
			})
			->whereHas('lot', function($q)
			{
				$q->where('status',2);
			})
			->leftJoin('cat_warehouse_concepts','warehouses.concept','cat_warehouse_concepts.id')
			->leftJoin('places','warehouses.place_location','places.id')
			->where('quantity','>',0)
			->get();
		$edit = ($request->has('edit') ? false : true);
		return view('almacen.modal_warehouse',['warehouses'=>$warehouses,'edit'=>$edit]);
	}
}
