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

class ReportAdministrationComputerController extends Controller
{
	private $module_id = 96;
	public function computerReport(Request $request)
	{
		if (Auth::user()->module->where('id',116)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			return view('reporte.administracion.computo',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id,
					'option_id' => 116
				]);
		}
	}

	public function computerTable(Request $request)
	{
		$table  = "";
		$type   = $request->type;

		$equipments = App\ComputerEquipment::where(function($query) use ($type)
					{
						if($type != "")
						{
							if ($type != "todos") 
							{
								$query->where('type',$type);
							}
						}
					})
					->where('status',1)
					->get();
		if ($type == null)
		{
			$table .= "";
		}
		elseif(count($equipments) > 0)
		{
			$table .=   "<form method='get' action='".route('report.computer.excel')."' accept-charset='UTF-8' id='formsearch'>";
			$table .=   "<input type='hidden' name='type_export' value='".$type."'>";
			$table .=   "<div style='float: right'><label class='label-form'>Exportar a Excel </label><button class='btn btn-green export' type='submit'><span class='icon-file-excel'></span></button></div></form>";
			$table .=   "<div class='table-responsive'>".
						"<table class='table table-striped' id='table-computer'>".
						"<thead class='thead-dark'>".
						"<th>Cantidad</th>".
						"<th>Producto/Material</th>".
						"<th>Marca</th>".
						"<th>Acción</th>".
						"</thead>";
						foreach($equipments as $equipment)
						{
							switch ($equipment->type) 
							{
								case "1":
									$equip = "Smartphone";
									break;

								case "2":
									$equip = "Tablet";
									break;

								case "3":
									$equip = "Laptop";
									break;

								case "4":
									$equip = "Desktop";
									break;
								
								default:
									break;
							}
							$table .=   "<tr>".
										"<td>".
										"". $equipment->quantity ."".
										"</td>".
										"<td>".
										"". $equip ."".
										"<input type='hidden' class='equip' value='". $equip ."'>".
										"</td>".
										"<td>".
										"". $equipment->brand ."".
										"<input type='hidden' class='id' value='". $equipment->id ."'>".
										"</td>".
										"<td>".
										"<button type='button' class='btn follow-btn detail-computer' title='Detalles'>".
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

	public function computerExcel(Request $request)
	{
		$table  = "";
		$type   = $request->type_export;

		$equipments = App\ComputerEquipment::where(function($query) use ($type)
					{
						if($type != "")
						{
							if ($type != "todos") 
							{
								$query->where('type',$type);
							}
						}
					})
					->get();

		Excel::create('Reporte-Inventario-Computo', function($excel) use ($type)
		{
			$excel->sheet('Datos',function($sheet) use ($type)
			{
				$sheet->setStyle(array('font'=>array('name'=>'Calibri','size'=>12)));
				$sheet->mergeCells('A1:K1');
				$sheet->cell('A1:K1', function($cells) {
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(array('family'=>'Calibri','size'=>16,'bold'=>true));
							});
				$sheet->cell('A2:K2', function($cells) {
							  $cells->setFontWeight('bold');
							  $cells->setAlignment('center');
							  $cells->setFont(array('family' => 'Calibri','size' => 14,'bold' => true));
							});
				$sheet->row(1,['Repoorte de inventario de equipo de cómputo']);
				$sheet->row(2,['ID','Cantidad','Tipo','Marca','Almacenamiento','Procesador','Memoria RAM','SKU','Comentarios','Importe Unitario','Importe Total']);
				$equipments = App\ComputerEquipment::where(function($query) use ($type)
								{
									if ($type != "")
									{
										if ($type != "todos")
										{
											$query->where('type',$type);
										}
									}
								})
								->get();
				foreach ($equipments as $equipment)
				{
					switch ($equipment->type) 
							{
								case "1":
									$equip = "Smartphone";
									break;

								case "2":
									$equip = "Tablet";
									break;

								case "3":
									$equip = "Laptop";
									break;

								case "4":
									$equip = "Desktop";
									break;
								
								default:
									break;
							}
					$row        = [];
					$row[0]     = $equipment->id;
					$row[1]     = $equipment->quantity;
					$row[2]     = $equip;
					$row[3]     = $equipment->brand;
					$row[4]     = $equipment->storage;
					$row[5]     = $equipment->processor;
					$row[6]     = $equipment->ram;
					$row[7]     = $equipment->sku;
					$row[8]     = $equipment->commentaries;
					$row[9]     = $equipment->amountUnit;
					$row[10]    = $equipment->amountTotal;
					$sheet->appendRow($row);
				}
			});
		})->export('xls');
	}

	public function computerDetail(Request $request)
	{
		$details = "";
		$equipment = App\ComputerEquipment::find($request->id);
		switch ($equipment->type) 
		{
			case "1":
				$equip = "Smartphone";
				break;

			case "2":
				$equip = "Tablet";
				break;

			case "3":
				$equip = "Laptop";
				break;

			case "4":
				$equip = "Desktop";
				break;
			
			default:
				break;
		}
		$details .= "<div class='modal-content'>".
					"<div class='modal-header'>".
					"<span class='close exit-computer'>&times;</span>".
					"</div>".
					"<div class='modal-body'>".
					"<center>".
					"<strong>DETALLES DE EQUIPO</strong>".
					"</center>".
					"<div class='divisor'>".
					"<div class='gray-divisor'></div>".
					"<div class='orange-divisor'></div>".
					"<div class='gray-divisor'></div>".
					"</div>".
					"<table class='employee-details'>".
					"<tbody>".
					
					"<tr>".
					"<td><b>Empresa:</b></td>".
					"<td><label>".($equipment->idEnterprise ? $equipment->enterprise->name : '')."</label></td>".
					"</tr>".
					
					"<tr>".
					"<td><b>Cuenta:</b></td>".
					"<td><label>".($equipment->account ? $equipment->accounts->account.' '.$equipment->accounts->description.' ('.$equipment->accounts->content.')' : '')."</label></td>".
					"</tr>".
					
					"<tr>".
					"<td><b>Ubicación/sed:</b></td>".
					"<td><label>".($equipment->place_location ? $equipment->location->place: '')."</label></td>".
					"</tr>".

					"<tr>".
					"<td><b>Cantidad:</b></td>".
					"<td><label>".$equipment->quantity."</label></td>".
					"</tr>".
					"<tr>".

					"<td><b>Tipo:</b></td>".
					"<td><label>".$equip."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Marca:</b></td>".
					"<td><label>".$equipment->brand."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Almacenamiento:</b></td>".
					"<td><label>".$equipment->storage."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Procesador:</b></td>".
					"<td><label>".$equipment->processor."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Memoria RAM:</b></td>".
					"<td><label>".$equipment->ram."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>SKU:</b></td>".
					"<td><label>".$equipment->sku."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Comentarios:</b></td>".
					"<td><label>".$equipment->commentaries."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Importe por unidad:</b></td>".
					"<td><label>$".number_format($equipment->amountUnit,2)."</label></td>".
					"</tr>".
					"<tr>".
					"<td><b>Importe Total:</b></td>".
					"<td><label>$".number_format($equipment->amountTotal,2)."</label></td>".
					"</tr>".
					"</tbody>".
					"</table>".
					"</div>";

		$details .= "<center><button type='button' class='btn btn-green exit-computer' title='Ocultar'>« Ocultar</button></center><br>".
					"</div></div>";
		return Response($details);
	}
}
