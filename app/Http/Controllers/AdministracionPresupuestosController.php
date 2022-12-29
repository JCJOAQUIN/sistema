<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App;
use Alert;
use App\Jobs\ObraUploadBreakdownWages;
use App\BreakdownWagesDetails;
use App\Jobs\ObraUploadBudget;
use App\Jobs\ObraUploadObraProgram;
use App\Jobs\ObraUploadSobrecostos;
use App\Jobs\ObraUploadSupplies;
use App\Jobs\ObraUploadUnitPrices;
use Auth;
use Excel;
use PHPExcel_Cell;
use Carbon\Carbon;

class AdministracionPresupuestosController extends Controller
{
	private $module_id     = 10;
	private $obra_child_id = 223;
	private $ALL_BORDER = array(
		'borders' => array(
			'allborders' => array(
				'style' => 'thin',
				'color' => array('rgb' => '000000'),
			),
		),
	);
	private $BOTTOM_BORDER = array(
		'borders' => array(
			'bottom' => array(
				'style' => 'thin',
				'color' => array('rgb' => '000000'),
			),
		),
	);
	private $OUTLINE_BORDER = array(
		'borders' => array(
			'outline' => array(
				'style' => 'thin',
				'color' => array('rgb' => '000000'),
			),
		),
	);

	private $DOUBLE_BORDER = array(
		'borders' => array(
			'outline' => array(
				'style' => 'double',
				'color' => array('rgb' => '000000'),
			),
		),
	);
	private $DOUBLE_BORDER_RED = array(
		'borders' => array(
			'allborders' => array(
				'style' => 'double',
				'color' => array('rgb' => 'C00001'),
			),
		),
	);
	private $OUTLINE_BORDER_RED = array(
		'borders' => array(
			'outline' => array(
				'style' => 'double',
				'color' => array('rgb' => 'C00001'),
			),
		),
	);
	private $BOTTOM_BORDER_RED = array(
		'borders' => array(
			'horizontal' => array(
				'style' => 'thin',
				'color' => array('rgb' => 'C00001'),
			),
		),
	);
	private $RIGHT_BORDER_RED = array(
		'borders' => array(
			'vertical' => array(
				'style' => 'dashed',
				'color' => array('rgb' => 'C00001'),
			),
		),
	);

	private $FORMAT_DATE = array(
		'code' => 'dd/mm/yy'
	);
	private $FORMAT_MONEY = array(
		'code' => '$#,##0_-'
	);
	private $FORMAT_PERCENTAGE = array(
		'code' => '0.00%'
	);
	private $FORMAT_NUMBER_COMMA = array(
		'code' => '#,##0.00_-'
	);


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

	public function obraIndex()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$project_id = $request->project_id;
			$name       = $request->name;
			$start      = $request->startObra;
			$end        = $request->endObra;
			if ($start != null)
			{
				$date1   = strtotime($start);
				$mindate = date('Y-m-d',$date1);
				$start   = $mindate;
			}
			if($end != null)
			{
				$date2   = strtotime($end);
				$maxdate = date('Y-m-d',$date2);
				$end     = $maxdate;
			}
			$BudgetUploads = App\SupplieUploads::where(function($q) use ($project_id,$name,$start,$end)
			{
				if($project_id)
				{
					$q->where('idproyect',$project_id);
				}
				if($name)
				{
					$q->where('name','like',"%".preg_replace("/\s+/", "%", $name)."%");
				}
				if($start && $end == null)
				{
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
				}
				if($end && $start == null)
				{
					$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				}
				if($start && $end)
				{
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
						->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				}
			})->paginate(10);
			return view('administracion.presupuestos.busqueda',
				[
					'id'         => $data['father'],
					'title'      => $data['name'],
					'details'    => $data['details'],
					'child_id'   => $this->obra_child_id,
					'option_id'  => 220,
					'bgups'      => $BudgetUploads,
					'project_id' => $project_id,
					'name'       => $name,
					'start'      => $start,
					'end'        => $end,
				]);
		}
		else
		{
			return redirect('/');
		}
	}
// lista de insumos
	public function paginate_search_supplies(Request $request)
	{
		$project_id = $request->project_id;
		$name       = $request->name;
		$start      = $request->startObra;
		$end        = $request->endObra;
		if ($start != null)
		{
			$date1   = strtotime($start);
			$mindate = date('Y-m-d',$date1);
			$start   = $mindate;
		}
		if($end != null)
		{
			$date2   = strtotime($end);
			$maxdate = date('Y-m-d',$date2);
			$end     = $maxdate;
		}
		$BudgetUploads = App\SupplieUploads::where(function($q) use ($project_id,$name,$start,$end)
		{
			if($project_id)
			{
				$q->where('idproyect',$project_id);
			}
			if($name)
			{
				$q->where('name','like',"%$name%");
			}
			if($start && $end == null)
			{
				$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
			}
			if($end && $start == null)
			{
				$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
			}
			
			if($start && $end)
			{
				$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
					->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
			}
		})->paginate(10);
		$html 		= '';
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value" => "#"],
				["value" => "Título"],
				["value" => "Proyecto"],
				["value" => "Inicio de obra"],
				["value" => "Fin de obra"],
				["value" => "Estado"],
				["value" => "Acción"],
			]
		];
		foreach($BudgetUploads as $key => $bgup)
		{
			$startObra    = \Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
			$endObra      = \Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
			$proyect_name = $bgup->proyect->proyectName;
			$body         = 
			[
				[
					"content"	=>	["label"	=>	$bgup->id],
				],
				[
					"content"	=>	["label"	=>	$bgup->name],
				],
				[
					"content"	=>	["label"	=>	$proyect_name],
				],
				[
					"content"	=>	["label"	=>	$bgup->startObra],
				],
				[
					"content"	=>	["label"	=>	$bgup->endObra],
				],
				[
					"content"	=>	["label"	=>	$bgup->status],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"buttonElement"	=>	"a",
							"attributeEx"	=>	"href=\"".route('supplies.create.validate',$bgup->id)."\"",
							"label"			=>	"<span class='icon-pencil'></span>",
							"classEx"		=>	"edit-item"
						],
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"success",
							"buttonElement"	=>  "a",
							"attributeEx"	=>	"href=\"".route('supplies.excel', $bgup->id)."\"",
							"label"			=>	"<span class='icon-file-excel'></span>",
							"classEx"		=>	"export"
						],
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"red",
							"buttonElement"	=>  "a",
							"attributeEx"	=>	"href=\"".route('supplies.delete', $bgup->id)."\"",
							"label"			=>	"<span class=\"icon-x\"></span>",
							"classEx"		=>	"export",
						],
					]
				],
			];
			$modelBody[]	=	$body;
		}
		$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
			'classEx'   => 'text-center',
			"modelBody" => $modelBody,
			"modelHead" => $modelHead
		])));
		if($BudgetUploads->count() == 0)
			// $html = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}

	public function create()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.insumos',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->obra_child_id,
					'option_id' => 219
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function suppliesUpload(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name = '/docs/supplies/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($request->file('file')));
			$path = \Storage::disk('public')->path($name);
			$bgup = App\SupplieUploads::create([
				'idproyect' => $request->get('project_id'),
				'file' => $name,
				'idCreate' => Auth::user()->id,
				'status' => 0,
				'name' => $request->get('name'),
			]);
			dispatch(new ObraUploadSupplies($bgup));
			return redirect()->route('supplies.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
	}
	

	public function suppliesValidate($budget_id)
	{
		$bgup = App\SupplieUploads::where('id',$budget_id)->first();
		$data	= App\Module::find($this->module_id);

		if(Auth::user()->module->where('id',$this->module_id)->count()>0 )
		{
			$budgetDetails = App\SupplieDetails::where('idUpload',$budget_id)->paginate(20);
			
			return view('administracion.presupuestos.editar_insumos',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->obra_child_id,
					'option_id'     => 219,
					'budget_id'     => $budget_id,
					'budgetUpload'  => App\SupplieUploads::where('id',$budget_id)->first(),
					'budgetDetails' => $budgetDetails,
				]);
		}
		else
		{
			return redirect('/');
		}
		

	}

	public function editSupplie(Request $request)
	{

		$bgup = App\SupplieDetails::where('id',$request->id)->first();


		$old_date	= new \DateTime($request->date);
		$new_date = $old_date->format('Y-m-d');



		$bgup->code 				= $request->code;
		$bgup->concept 			= $request->concept;
		$bgup->measurement 	= $request->measurement;
		$bgup->date 				= $new_date;
		$bgup->amount 			= $request->amount;
		$bgup->price 				= $request->price;
		$bgup->import 			= $request->import;
		$bgup->incidence 		= $request->incidence;
		
		$bgup->save();

		
		return \Response::JSON(array(
			'status'       => true
		));

	}

	public function paginate_supplies_arts(Request $request)
	{
		
		$SupplieUploads = App\SupplieUploads::where('id',$request->budgetUpload)->first();

		$budgetDetails = App\SupplieDetails::
			where('idUpload',$request->budgetUpload)
			->where(function($q) use($request){
				if($request->search != null)
					$q->where('groupName','like',"%$request->search%")
						->orWhere('code','like',"%$request->search%")
						->orWhere('concept','like',"%$request->search%")
						->orWhere('measurement','like',"%$request->search%");
			})
		->paginate(20);
		

		$html		=	"";
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Código"],
				["value"	=>	"Grupo"],
				["value"	=>	"Concepto"],
				["value"	=>	"Unidad"],
				["value"	=>	"Fecha"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Precio"],
				["value"	=>	"Importe"],
				["value"	=>	"% Incidencia"],
				["value"	=>	"Acción"]
			]
		];
		foreach($budgetDetails as $key => $bugt)
		{
			$amount		=	$this->returnString($bugt->amount);
			$price		=	$this->returnString($bugt->price);
			$import		=	$this->returnString($bugt->import);
			$incidence	=	$this->returnString($bugt->incidence);
			$date		=	\Carbon\Carbon::parse($bugt->date)->format('d-m-Y');
			$id			=	$budgetDetails->firstItem() + $key;

			$count = $key+ $budgetDetails->firstItem();
			$body	=
			[
				"attributeEx"	=>	"id=\"id-$bugt->id\"",
				"classEx"		=>	"id-row",
				[
					"content"	=>
					[
						["label"	=>	$count],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->id,
							"classEx"	=>	"hidden idd"
						],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$id,
							"classEx"	=>	"hidden"
						]
					]
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->code,
						"classEx"	=>	"code"
					],
				],
				[
					"content"	=>	["label"	=>	$bugt->groupName],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->concept,
						"classEx"	=>	"concept"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->measurement,
						"classEx"	=>	"measurement"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$date,
						"classEx"	=>	"date"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$amount,
						"classEx"	=>	"amount"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$price,
						"classEx"	=>	"price"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$import,
						"classEx"	=>	"import"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$incidence,
						"classEx"	=>	"incidence"
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"attributeEx"	=>	"id=\"edit\" type=\"button\"",
							"label"			=>	"<span class='icon-pencil'></span>",
							"classEx"		=>	"edit-item"
						]
					]
				],
			];
			$modelBody[]	=	$body;
		}
		$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
			"classEx" 			=>	"table",
			"attributeEx"		=>	"id=\"table\"",
			"attributeExBody"	=>	"id=\"table-body\"",
			"modelBody"			=>	$modelBody,
			"modelHead"			=>	$modelHead
		])));


		/* $table_body =
		'	<div class="table-responsive table-striped">
			<table id="table" class="table">
				<thead >
					<th>#</th>
					<th>Código</th>
					<th>Grupo</th>
					<th>Concepto</th>
					<th>Unidad</th>
					<th>Fecha</th>
					<th>Cantidad</th>
					<th>Precio</th>
					<th>Importe</th>
					<th>% Incidencia</th>
					<th>Acción</th>
				</thead>
				<tbody id="table-body">';

		foreach ($budgetDetails as $key => $bugt) {
			$amount = $this->returnString($bugt->amount);
			$price = $this->returnString($bugt->price);
			$import = $this->returnString($bugt->import);
			$incidence = $this->returnString($bugt->incidence);
			$date = \Carbon\Carbon::parse($bugt->date)->format('d-m-Y');
			$id = $budgetDetails->firstItem() + $key;

			$count = $key+ $budgetDetails->firstItem();

			$table_body .= 
			"<tr id='id-$bugt->id'>".
				"<td hidden>$id<label hidden class='id'>$bugt->id</label></td>".
				"<td>$count</td>".
				"<td><label class='code'>$bugt->code</label></td>".
				"<td>$bugt->groupName</td>".
				"<td><label class='concept'>$bugt->concept</label></td>".
				"<td><label class='measurement'>$bugt->measurement</label></td>".
				"<td><label class='date'>$date</label></td>".
				"<td><label class='amount'>$amount</label></td>".
				"<td><label class='price'>$price</label></td>".
				"<td><label class='import'>$import</label></td>".
				"<td><label class='incidence'>$incidence</label></td>".
				"<td>".
					"<button id='edit' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></button>".
				"</td>".
			"</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */
		
		return \Response::JSON(array(
			'SupplieUploads'       => $SupplieUploads, 
			'data'       => $budgetDetails, 
			'table'       => $html, 
			'pagination' => (string) $budgetDetails->links()
		));
		
	}
	function returnString($a) {
		
			return preg_replace('/(\.[0-9]+?)0*$/', '$1', $a);
		
		}

	public function finishSupplie(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\SupplieUploads::where('id',$request->BudgetID)->first();

			$bgup->idproyect 	= $request->project_id;
			$bgup->name 			= $request->name;

			$bgup->client 		= $request->client;
			$bgup->contestNo 	= $request->contestNo;
			$bgup->obra 			= $request->obra;
			$bgup->place 			= $request->place;
			$bgup->city 			= $request->city;

			$old_date					= new \DateTime($request->startObra);
			$new_date 				= $old_date->format('Y-m-d');
			$bgup->startObra = $new_date;

			$old_date	= new \DateTime($request->endObra);
			$new_date = $old_date->format('Y-m-d');
			$bgup->endObra = $new_date;

			$bgup->status 		= 1;
			$bgup->save();

			$data	= App\Module::find($this->module_id);

			$alert = "swal('', 'Listado de Insumos Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function SuppliesDelete($budget_id)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\SupplieUploads::find($budget_id);
			\Storage::disk('public')->delete($bgup->file);
			App\SupplieDetails::where('idUpload',$budget_id)->delete();
			App\SupplieUploads::where('id',$budget_id)->delete();
			$alert = "swal('', 'Listado de Insumos Eliminado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function SuppliesExcel(Request $request)
	{
		
		$BudgetUploads = App\SupplieUploads::where('id',$request->id)->first();

		Excel::create('Listado de Insumos', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('Listado de Insumos',function($sheet) use ($BudgetUploads)
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

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));

			$sheet->getStyle('A1:H5')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			$sheet->getStyle('A7:H7')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			

				$sheet->row(1,['Cliente:',$BudgetUploads->client]);
				$sheet->row(2,['Concurso No:',$BudgetUploads->contestNo,'','','',
				'Duración:',\Carbon\Carbon::parse($BudgetUploads->endObra)->diffInDays(\Carbon\Carbon::parse($BudgetUploads->startObra))]);
				$sheet->row(3,['Obra:',$BudgetUploads->obra]);
				$sheet->row(4,['Lugar:',$BudgetUploads->place]);
				$sheet->row(5,[
					'Ciudad:',$BudgetUploads->city,
					'',
					'Inicio obra:',\Carbon\Carbon::parse($BudgetUploads->startObra)->format('d/m/Y'),
					'Fin obra:',\Carbon\Carbon::parse($BudgetUploads->endObra)->format('d/m/Y'),
					]);


				$sheet->row(6,['']);
				$sheet->row(7,[
					'Código',
					'Concepto',
					'Unidad',
					'Fecha',
					'Cantidad',
					'Precio',
					'Importe',
					'% Incidencia',
				]);
				$parent = "";
				$countHeigt = 7;
				$sheet->setAutoSize(false);
				$sheet->setWidth(array(
					'A' => 15,
					'B'	=>  100,
					'D' => 15,
					'E'	=> 15,
					'F'	=> 15,
					'G'	=> 15,
					'H'	=> 15
				));

			
				foreach(App\SupplieDetails::where('idUpload',$BudgetUploads->id)->get() as $bgup)
				{
					$row 	= [];
					if($parent != $bgup->groupName)
					{
						$rowP = [];
						$parent = $bgup->groupName;
						$rowP[] = '';
						$rowP[] = $parent;
						$sheet->appendRow($rowP);
						$countHeigt++;
					}

					$row[] = $bgup->code;
					$row[] = $bgup->concept;
					$row[] = $bgup->measurement;
					$row[] = \Carbon\Carbon::parse($bgup->date)->format('d/m/Y');
					$row[] = $bgup->amount;
					$row[] = $bgup->price;
					$row[] = $bgup->import;
					$row[] = $bgup->incidence /100;
					

					$sheet->appendRow($row);

					$countHeigt++;
				}
				$sheet->getStyle("D8:D".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => 'yyyy-mm-dd'
				));
				$sheet->getStyle("F8:F".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("G8:G".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("H8:H".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '0.0%'
				));
				$sheet->getStyle("B8:H".$countHeigt)->getAlignment()->applyFromArray(array(
					'wrap' => TRUE
				));

				
			});
		})->export('xls');
	}

	/*
	|--------------------------------------------------------------------------
	| Budget functions
	|--------------------------------------------------------------------------
	*/
	public function createBudget()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.presupuestos',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 221
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function budgetUpload(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name		= '/docs/budgets/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($request->file('file')));
			$path		= \Storage::disk('public')->path($name);

			$bgup = App\BudgetUploads::create([
				'idproyect' => $request->get('project_id'),
				'file' => $name,
				'idCreate' => Auth::user()->id,
				'status' => 0,
				'name' => $request->get('name'),
			]);


			dispatch(new ObraUploadBudget($bgup));





			return redirect()->route('budget.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
		

		
	}

	public function budgetValidate($budget_id)
	{
		$bgup = App\BudgetUploads::where('id',$budget_id)->first();
		$data	= App\Module::find($this->module_id);

		if(Auth::user()->module->where('id',$this->module_id)->count()>0 )
		{
			$budgetDetails = App\BudgetDetails::where('idUpload',$budget_id)->paginate(20);
			
			return view('administracion.presupuestos.editar_presupuesto',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->obra_child_id,
					'option_id'     => 221,
					'budget_id'     => $budget_id,
					'budgetUpload'  => App\BudgetUploads::where('id',$budget_id)->first(),
					'budgetDetails' => $budgetDetails,
				]);
		}
		else
		{
			return redirect('/');
		}
		

	}

	public function editBudget(Request $request)
	{

		$bgup = App\BudgetDetails::where('id',$request->id)->first();

		$bgup->code 			= $request->code;
		$bgup->concept 			= $request->concept;
		$bgup->measurement 	= $request->measurement;
		$bgup->amount 			= $request->amount;
		$bgup->price 				= $request->price;
		$bgup->import 			= $request->import;
		$bgup->incidence 		= $request->incidence;
		
		$bgup->save();

		
		return \Response::JSON(array(
			'status'       => true
		));

	}

	public function paginate_budget_arts(Request $request)
	{
		
		$BudgetUploads = App\BudgetUploads::where('id',$request->budgetUpload)->first();
		
		$budgetDetails = App\BudgetDetails::
			where('idUpload',$request->budgetUpload)
			->where(function($q) use($request){
				if($request->search != null)
					$q->Where('code','like',"%$request->search%")
						->orWhere('concept','like',"%$request->search%")
						->orWhere('measurement','like',"%$request->search%");
			})
		->paginate(20);
		

		$html		=	"";
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Código"],
				["value"	=>	"Grupo"],
				["value"	=>	"Concepto"],
				["value"	=>	"Unidad"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Precio"],
				["value"	=>	"Importe"],
				["value"	=>	"% Incidencia"],
				["value"	=>	"Acción"]
			]
		];
		foreach($budgetDetails as $key => $bugt)
		{
			$amount		=	$this->returnString($bugt->amount);
			$price		=	$this->returnString($bugt->price);
			$import		=	$this->returnString($bugt->import);
			$incidence	=	$this->returnString($bugt->incidence);
			$id			=	$budgetDetails->firstItem() + $key;

			$code = $bugt->parent ? $bugt->parent->code : '';
			$body	=
			[
				"attributeEx"	=>	"id=\"id-$bugt->id\"",
				"classEx"		=>	"id-row",
				[
					"content"	=>
					[
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->code,
							"classEx"	=>	"code"
						],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->id,
							"classEx"	=>	"hidden idd"
						],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$id,
							"classEx"	=>	"hidden"
						]
					],
				],
				[
					"content"	=>	["label"	=>	$bugt->groupName],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->concept,
						"classEx"	=>	"concept"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->measurement,
						"classEx"	=>	"measurement"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$amount,
						"classEx"	=>	"amount"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$price,
						"classEx"	=>	"price"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$import,
						"classEx"	=>	"import"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$incidence,
						"classEx"	=>	"incidence"
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"secondary",
							"attributeEx"	=>	"id=\"edit\" type=\"button\"",
							"label"			=>	"<span class='icon-pencil'></span>",
							"classEx"		=>	"edit-item"
						]
					]
				]
			];
			$modelBody[]	=	$body;
		}
		$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
			"classEx" 			=> "table",
			"attributeEx"		=>	"id=\"table\"",
			"attributeExBody"	=>	"id=\"table-body\"",
			"modelBody"			=>	$modelBody,
			"modelHead"			=>	$modelHead
		])));



		/* $table_body =
		'	<div class="table-responsive table-striped">
			<table id="table" class="table">
				<thead >
					<th>Código</th>
					<th>Grupo</th>
					<th>Concepto</th>
					<th>Unidad</th>
					<th>Cantidad</th>
					<th>Precio</th>
					<th>Importe</th>
					<th>% Incidencia</th>
					<th>Acción</th>
				</thead>
				<tbody id="table-body">';

		foreach ($budgetDetails as $key => $bugt) {
			$amount = $this->returnString($bugt->amount);
			$price = $this->returnString($bugt->price);
			$import = $this->returnString($bugt->import);
			$incidence = $this->returnString($bugt->incidence);
			$id = $budgetDetails->firstItem() + $key;

			$code = $bugt->parent ? $bugt->parent->code : '';

			$table_body .= 
			"<tr id='id-$bugt->id'>".
				"<td hidden>$id<label hidden class='id'>$bugt->id</label></td>".
				"<td><label class='code'>$bugt->code</label></td>".
				"<td>$code</td>".
				"<td><label class='concept'>$bugt->concept</label></td>".
				"<td><label class='measurement'>$bugt->measurement</label></td>".
				"<td><label class='amount'>$amount</label></td>".
				"<td><label class='price'>$price</label></td>".
				"<td><label class='import'>$import</label></td>".
				"<td><label class='incidence'>$incidence</label></td>".
				"<td>".
					"<button id='edit' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></button>".
				"</td>".
			"</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */
		
		return \Response::JSON(array(
			'BudgetUploads'       => $BudgetUploads, 
			'data'       => $budgetDetails, 
			'table'       => $html,
			'pagination' => (string) $budgetDetails->links()
		));
		
	}

	public function paginate_search_budget(Request $request)
	{
		
			$project_id	=	$request->project_id;
			$name 		=	$request->name;
			$start 		=	$request->startObra;
			$end 		=	$request->endObra;

			if ($start != null)
			{
				$date1 		= strtotime($start);
				$mindate 	= date('Y-m-d',$date1);
				$start 		= $mindate;
			}
			if($end != null)
			{
				$date2 		= strtotime($end);
				$maxdate 	= date('Y-m-d',$date2);
				
				$end 			= $maxdate;
			}

			$BudgetUploads = App\BudgetUploads::where(function($q) use ($project_id,$name,$start,$end){
				
				if($project_id)
					$q->where('idproyect',$project_id);

				if($name)
					$q->where('name','like',"%$name%");

				if($start && $end == null)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
				
				if($end && $start == null)
					$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				
				if($start && $end)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
						->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');

			})->paginate(10);

			$html 		=	'';
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Inicio de obra"],
					["value"	=>	"Fin de obra"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"],
				]
			];
			foreach($BudgetUploads as $key => $bgup)
			{
				$startObra		=	\Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
				$endObra		=	\Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
				$proyect_name	=	$bgup->proyect->proyectName;
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bgup->id],
					],
					[
						"content"	=>	["label"	=>	$bgup->name],
					],
					[
						"content"	=>	["label"	=>	$proyect_name],
					],
					[
						"content"	=>	["label"	=>	$bgup->startObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->endObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->status],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('budget.create.validate',$bgup->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('budget.excel',$bgup->id)."\"",
								"label"			=>	"<span class='icon-file-excel'></span>",
								"classEx"		=>	"export"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('budget.delete', $bgup->id)."\"",
								"label"			=>	"<span class='icon-x '></span>",
								"classEx"		=>	"export"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				'classEx' 		=> 'text-center',
				"modelBody"		=> $modelBody,
				"modelHead"		=> $modelHead
			])));
		
		/* $table_body =
		'	<div class="table-responsive">
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th width="5%">#</th>
						<th width="30%">Título</th>
						<th width="30%">Proyecto</th>
						<th width="10%">Inicio de obra</th>
						<th width="10%">Fin de obra</th>
						<th width="10%">Estado</th>
						<th width="15%">Acción</th>
					</tr> 
				</thead>
			<tbody>';

		foreach ($BudgetUploads as $key => $bgup) {

			$startObra = \Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
			$endObra = \Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
			$proyect_name = $bgup->proyect->proyectName;

			$table_body .= 
			"<tr>
			<td>$bgup->id</td>
			<td>$bgup->name</td>
			<td>$proyect_name</td>
			<td>$bgup->startObra</td>
			<td>$bgup->endObra</td>
			<td>$bgup->status</td>
			<td>
				<a href='".route('budget.create.validate',$bgup->id)."' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></a>
				<form method='get' action='".route('budget.excel',$bgup->id)."' accept-charset='UTF-8'>
				<button class='btn btn-green export' type='submit'  ><span class='icon-file-excel'></span></button>
				</form>
				<form method='post' action='".route('budget.delete') ."'>

				<input type='text' name='_token' hidden value='".csrf_token()."'>
				<input type='text' name='BudgetID' hidden value='$bgup->id'>
				<button class='btn btn-red export' type='submit'  ><span class='icon-x '></span></button>
				</form>
			</td>
		</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */

		if($BudgetUploads->count() == 0)
			// $html = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}

	public function finishBudget(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\BudgetUploads::where('id',$request->BudgetID)->first();

			$bgup->idproyect 	= $request->project_id;
			$bgup->name 			= $request->name;

			$bgup->client 		= $request->client;
			$bgup->contestNo 	= $request->contestNo;
			$bgup->obra 			= $request->obra;
			$bgup->place 			= $request->place;

			$old_date					= new \DateTime($request->startObra);
			$new_date 				= $old_date->format('Y-m-d');
			$bgup->startObra = $new_date;

			$old_date	= new \DateTime($request->endObra);
			$new_date = $old_date->format('Y-m-d');
			$bgup->endObra = $new_date;

			$bgup->status 		= 1;
			$bgup->save();

			$data	= App\Module::find($this->module_id);

			$alert = "swal('', 'Presupuesto Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function BudgetsDelete($budget_id)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\BudgetUploads::find($budget_id);
			\Storage::disk('public')->delete($bgup->file);
			App\BudgetDetails::where('idUpload',$budget_id)->delete();
			App\BudgetUploads::where('id',$budget_id)->delete();
			$alert = "swal('', 'Presupuesto Eliminado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function BudgetExcel(Request $request)
	{
		

		$BudgetUploads = App\BudgetUploads::where('id',$request->id)->first();

		Excel::create('Presupuesto', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('Presupuesto',function($sheet) use ($BudgetUploads)
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

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));

			$sheet->getStyle('A1:G5')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			$sheet->getStyle('A7:G7')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));

				$sheet->row(1,['Cliente:',$BudgetUploads->client]);
				$sheet->row(2,['Concurso No:',$BudgetUploads->contestNo,'','','',
				'Duración:',\Carbon\Carbon::parse($BudgetUploads->endObra)->diffInDays(\Carbon\Carbon::parse($BudgetUploads->startObra))]);
				$sheet->row(3,['Obra:',$BudgetUploads->obra]);
				$sheet->row(4,['Lugar:',$BudgetUploads->place]);
				$sheet->row(5,[
					'Ciudad:',$BudgetUploads->city,
					'',
					'Inicio obra:',\Carbon\Carbon::parse($BudgetUploads->startObra)->format('d/m/Y'),
					'Fin obra:',\Carbon\Carbon::parse($BudgetUploads->endObra)->format('d/m/Y'),
					]);


				$sheet->row(6,['']);
				$sheet->row(7,[
					'Código',
					'Concepto',
					'Unidad',
					'Cantidad',
					'P. Unitario',
					'Importe',
					'%',
				]);
				$countHeigt = 7;
				$sheet->setAutoSize(false);
				$sheet->setWidth(array(
					'A' => 15,
					'B'	=>  100,
					'D' => 15,
					'E'	=> 15,
					'F'	=> 15,
					'G'	=> 15,
				));
				foreach(App\BudgetDetails::where('idUpload',$BudgetUploads->id)->whereNull('father')->get() as $bgup)
				{

					$rowP =[];
					$rowP[] = $bgup->code;
					$rowP[] = $bgup->concept;
					$sheet->appendRow($rowP);
					$countHeigt++;
					// if(strlen($bgup->concept)>30)
					// 		$sheet->getStyle("B".$countHeigt)->getAlignment()->setWrapText(true);
					if($bgup->childrens()->count() >0)
						$countHeigt = $this->rowsBudget($sheet,$bgup->childrens,$countHeigt);

				}
				$sheet->getStyle("A8:A".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '@' //text
				));
				$sheet->getStyle("E8:E".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("F8:F".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("G8:G".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '0.0%'
				));
				$sheet->getStyle("B8:H".$countHeigt)->getAlignment()->applyFromArray(array(
					'wrap' => TRUE
				));
			});
		})->export('xls');
	}

	public function rowsBudget($sheet,$bgup,$countHeigt)
	{
		foreach($bgup as $ch)
		{
			
			if($ch->childrens()->count() >0)
			{
				$row 	= [];
				$row[] = $ch->code;
				$row[] = $ch->concept;
				$sheet->appendRow($row);
				$countHeigt++;
				$countHeigt = $this->rowsBudget($sheet,$ch->childrens,$countHeigt);
			}else
			{
				$row 	= [];
				$row[] = $ch->code;
				$row[] = $ch->concept;
				$row[] = $ch->measurement;
				$row[] = $ch->amount;
				$row[] = $ch->price;
				$row[] = $ch->import;
				$row[] = $ch->incidence/100;
				$sheet->appendRow($row);
				$countHeigt++;
			}
		}
		return $countHeigt;
	}



		/*
	|--------------------------------------------------------------------------
	| BreakdownWages functions
	|--------------------------------------------------------------------------
	*/

	public function createBreakdownWages()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.desgloseSalarios',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 222
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function sendBreakdownWages(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name = '/docs/BreakdownWages/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			Storage::disk('public')->put($name,\File::get($request->file('file')));
			$path = Storage::disk('public')->path($name);
			$bgup = App\BreakdownWagesUploads::create([
				'idproyect' => $request->get('project_id'),
				'file'      => $name,
				'idCreate'  => Auth::user()->id,
				'status'    => 0,
				'name'      => $request->get('name'),
			]);
			dispatch(new ObraUploadBreakdownWages($bgup));
			return redirect()->route('BreakdownWages.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
	}


	public function validateBreakdownWages($budget_id)
	{
		$bgup	= App\BreakdownWagesUploads::where('id',$budget_id)->first();
		$data	= App\Module::find($this->module_id);

		if(Auth::user()->module->where('id',$this->module_id)->count()>0 )
		{
			$budgetDetails = App\BreakdownWagesDetails::where('idUpload',$budget_id)->paginate(20);
			
			return view('administracion.presupuestos.editar_desglose_salarios',
				[
					'id'            => $data['father'],
					'title'         => $data['name'],
					'details'       => $data['details'],
					'child_id'      => $this->obra_child_id,
					'option_id'     => 222,
					'budget_id'     => $budget_id,
					'budgetUpload'  => App\BreakdownWagesUploads::where('id',$budget_id)->first(),
					'budgetDetails' => $budgetDetails,
				]);
		}
		else
		{
			return redirect('/');
		}
		

	}

	
	public function paginate_breakdown_wages_arts(Request $request)
	{
		
		$BreakdownWagesUploads = App\BreakdownWagesUploads::where('id',$request->budgetUpload)->first();

		$budgetDetails = App\BreakdownWagesDetails::
			where('idUpload',$request->budgetUpload)
			->where(function($q) use($request){
				if($request->search != null)
					$q->where('code','like',"%$request->search%")
						->orWhere('concept','like',"%$request->search%")
						->orWhere('measurement','like',"%$request->search%");
			})
		->paginate(20);
		
			$html		=	"";
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Grupo"],
					["value"	=>	"Código"],
					["value"	=>	"Concepto"],
					["value"	=>	"Unidad"],
					["value"	=>	"Salario Base por Jornal"],
					["value"	=>	"Factor Salario Real"],
					["value"	=>	"Salario Real"],
					["value"	=>	"Viáticos"],
					["value"	=>	"Alimentación"],
					["value"	=>	"Salario Total"],
					["value"	=>	"Acción"]
				]
			];
			foreach($budgetDetails as $key => $bugt)
			{
				$baseSalaryPerDay	=	$this->returnString($bugt->baseSalaryPerDay);
				$realSalaryFactor	=	$this->returnString($bugt->realSalaryFactor);
				$realSalary			=	$this->returnString($bugt->realSalary);
				$viatics			=	$this->returnString($bugt->viatics);
				$feeding			=	$this->returnString($bugt->feeding);
				$totalSalary		=	$this->returnString($bugt->totalSalary);
				$id					=	$budgetDetails->firstItem() + $key;
				$count				=	$key+ $budgetDetails->firstItem();
				$body	=
				[
					"classEx"		=>	"id-row",
					"attributeEx"	=>	"id=\"id-$bugt->id\"",
					[
						"content"	=>
						[
							["label"	=>	$count],
							[
								"kind"		=>	"components.labels.label",
								"label"		=>	$bugt->id,
								"classEx"	=>	"hidden idd"
							],
							[
								"kind"		=>	"components.labels.label",
								"label"		=>	$id,
								"classEx"	=>	"hidden"
							]
						]
					],
					[
						"content"	=>	["label"	=>	$bugt->groupName],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->code,
							"classEx"	=>	"code"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->concept,
							"classEx"	=>	"concept"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->measurement,
							"classEx"	=>	"measurement"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$baseSalaryPerDay,
							"classEx"	=>	"baseSalaryPerDay"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$realSalaryFactor,
							"classEx"	=>	"realSalaryFactor"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$realSalary,
							"classEx"	=>	"realSalary"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$viatics,
							"classEx"	=>	"viatics"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$feeding,
							"classEx"	=>	"feeding"
						],
					],
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$totalSalary,
							"classEx"	=>	"totalSalary"
						],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"attributeEx"	=>	"id=\"edit\" type=\"button\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				"classEx" 			=>	"table",
				"attributeEx"		=>	"id=\"table\"",
				"attributeExBody"	=>	"id=\"table-body\"",
				"modelBody"			=>	$modelBody,
				"modelHead"			=>	$modelHead
			])));
		return \Response::JSON(array(
			'BreakdownWagesUploads'		=> $BreakdownWagesUploads, 
			'data'       				=> $budgetDetails, 
			'table'						=> $html, 
			'pagination'				=> (string) $budgetDetails->links()
		));
		
	}
	
	public function editBreakdownWages(Request $request)
	{

		$bgup = App\BreakdownWagesDetails::where('id',$request->id)->first();

		$bgup->code 						= $request->code;
		$bgup->concept 					= $request->concept;
		$bgup->measurement 			= $request->measurement;
		$bgup->baseSalaryPerDay = $request->baseSalaryPerDay;
		$bgup->realSalaryFactor = $request->realSalaryFactor;
		$bgup->realSalary 			= $request->realSalary;
		$bgup->viatics 					= $request->viatics;
		$bgup->feeding 					= $request->feeding;
		$bgup->totalSalary 			= $request->totalSalary;
		
		$bgup->save();

		
		return \Response::JSON(array(
			'status'       => true
		));

	}

	

	public function finishBreakdownWages(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\BreakdownWagesUploads::where('id',$request->BudgetID)->first();

			$bgup->idproyect 	= $request->project_id;
			$bgup->name 			= $request->name;

			$bgup->client 		= $request->client;
			$bgup->contestNo 	= $request->contestNo;
			$bgup->obra 			= $request->obra;
			$bgup->place 			= $request->place;
			$bgup->city 			= $request->city;

			$old_date					= new \DateTime($request->startObra);
			$new_date 				= $old_date->format('Y-m-d');
			$bgup->startObra = $new_date;

			$old_date	= new \DateTime($request->endObra);
			$new_date = $old_date->format('Y-m-d');
			$bgup->endObra = $new_date;

			$bgup->status 		= 1;
			$bgup->save();

			$data	= App\Module::find($this->module_id);

			$alert = "swal('', 'Desglose de Salarios Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
	public function BreakdownWagesDelete($budget_id)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\BreakdownWagesUploads::find($budget_id);
			\Storage::disk('public')->delete($bgup->file);
			App\BreakdownWagesDetails::where('idUpload',$budget_id)->delete();
			App\BreakdownWagesUploads::where('id',$budget_id)->delete();
			$alert = "swal('', 'Desglose de Salarios Eliminado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	
	public function paginate_search_BreakdownWages(Request $request)
	{
		
		$project_id = $request->project_id;
			$name 			= $request->name;
			$start 			= $request->startObra;
			$end 				= $request->endObra;

			if ($start != null)
			{
				$date1 		= strtotime($start);
				$mindate 	= date('Y-m-d',$date1);
				$start 		= $mindate;
			}
			if($end != null)
			{
				$date2 		= strtotime($end);
				$maxdate 	= date('Y-m-d',$date2);
				
				$end 			= $maxdate;
			}

			$BudgetUploads = App\BreakdownWagesUploads::where(function($q) use ($project_id,$name,$start,$end){
				
				if($project_id)
					$q->where('idproyect',$project_id);

				if($name)
					$q->where('name','like',"%$name%");

				if($start && $end == null)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
				
				if($end && $start == null)
					$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				
				if($start && $end)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
						->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');

			})->paginate(10);

			$html 		=	'';
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Inicio de obra"],
					["value"	=>	"Fin de obra"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"],
				]
			];
			foreach($BudgetUploads as $key => $bgup)
			{
				$startObra		=	\Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
				$endObra		=	\Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
				$proyect_name	=	$bgup->proyect->proyectName;
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bgup->id],
					],
					[
						"content"	=>	["label"	=>	$bgup->name],
					],
					[
						"content"	=>	["label"	=>	$proyect_name],
					],
					[
						"content"	=>	["label"	=>	$startObra],
					],
					[
						"content"	=>	["label"	=>	$endObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->status],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('BreakdownWages.create.validate',$bgup->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('BreakdownWages.excel',$bgup->id)."\"",
								"label"			=>	"<span class='icon-file-excel'></span>",
								"classEx"		=>	"export"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('BreakdownWages.delete',$bgup->id)."\"",
								"label"			=>	"<span class='icon-x'></span>",
								"classEx"		=>	"export",
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				'classEx' 		=>	'text-center',
				"modelBody"		=>	$modelBody,
				"modelHead"		=>	$modelHead
			])));

		if($BudgetUploads->count() == 0)
			// $html = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}

	public function BreakdownWagesExcel(Request $request)
	{
		$BudgetUploads = App\BreakdownWagesUploads::where('id',$request->id)->first();

		Excel::create('Desglose de salarios', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('Desglose de salarios',function($sheet) use ($BudgetUploads)
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

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));

			$sheet->getStyle('A1:I5')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			$sheet->getStyle('A7:I7')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));

				$sheet->row(1,['Cliente:',$BudgetUploads->client]);
				$sheet->row(2,['Concurso No:',$BudgetUploads->contestNo]);
				$sheet->row(3,['Obra:',$BudgetUploads->obra]);
				$sheet->row(4,['Lugar:',$BudgetUploads->place]);
				$sheet->row(5,[
					'Ciudad:',$BudgetUploads->city,
					'',
					'Inicio obra:',\Carbon\Carbon::parse($BudgetUploads->startObra)->format('d-m-Y'),
					'Fin obra:',\Carbon\Carbon::parse($BudgetUploads->endObra)->format('d-m-Y'),
					'Duración:',\Carbon\Carbon::parse($BudgetUploads->endObra)->diffInDays(\Carbon\Carbon::parse($BudgetUploads->startObra))
					]);


				$sheet->row(6,['']);
				$sheet->row(7,[
					'Código',
					'Concepto',
					'Unidad',
					'Salario Base por Jornal',
					'Factor Salario Real',
					'Salario Real',
					'Viáticos',
					'Alimentación',
					'Salario Total',
				]);
				$countHeigt = 7;
				foreach(App\BreakdownWagesDetails::where('idUpload',$BudgetUploads->id)->get() as $bgup)
				{
					$row 	= [];
					//$row[] = $bgup->groupName;
					$row[] = $bgup->code;
					$row[] = $bgup->concept;
					$row[] = $bgup->measurement;
					$row[] = $bgup->baseSalaryPerDay;
					$row[] = $bgup->realSalaryFactor;
					$row[] = $bgup->realSalary;
					$row[] = $bgup->viatics;
					$row[] = $bgup->feeding;
					$row[] = $bgup->totalSalary;

					$sheet->appendRow($row);
					$countHeigt++;
				}
				$sheet->getStyle("D8:D".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("E8:E".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("F8:F".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("G8:G".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("H8:H".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("I8:I".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
			});
		})->export('xls');
	}


	/*
	|--------------------------------------------------------------------------
	| UnitPrices functions
	|--------------------------------------------------------------------------
	*/
	public function UnitPricesCreate()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.precios_unitarios',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 224
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function UnitPricesSend(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name		= '/docs/UnitPrices/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($request->file('file')));
			$path		= \Storage::disk('public')->path($name);

			$bgup = App\UnitPricesUploads::create([
				'idproyect' => $request->get('project_id'),
				'file' => $name,
				'idCreate' => Auth::user()->id,
				'status' => 0,
				'name' => $request->get('name'),
			]);


			dispatch(new ObraUploadUnitPrices($bgup));

			return redirect()->route('UnitPrices.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function UnitPricesValidate($budget_id)
	{
		$data	= App\Module::find($this->module_id);

		if(Auth::user()->module->where('id',$this->module_id)->count()>0 )
		{
			
			
			return view('administracion.presupuestos.editar_precios_unitatios',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->obra_child_id,
					'option_id'    => 224,
					'budget_id'    => $budget_id,
					'budgetUpload' => App\UnitPricesUploads::where('id',$budget_id)->first(),
				]);
		}
		else
		{
			return redirect('/');
		}
		

	}

	
	public function UnitPricesPaginateArts(Request $request)
	{
		
		
		$UnitPricesUploads = App\UnitPricesUploads::where('id',$request->budgetUpload)->first();

		$budgetDetails = App\UnitPricesDetails::
			where('idUpload',$request->budgetUpload)
			->where(function($q) use($request){
				if($request->search != null)
					$q->where('code','like',"%$request->search%")
						->orWhere('concept','like',"%$request->search%")
						->orWhere('measurement','like',"%$request->search%");
			})
		->paginate(20);
		


		$table_body		=	"";
		$body			=	[];
		$modelBody		=	[];
		$modelHead		=
		[
			[
				["value"	=>	"#"],
				["value"	=>	"Tipo"],
				["value"	=>	"Código"],
				["value"	=>	"Concepto"],
				["value"	=>	"Unidad"],
				["value"	=>	"Cantidad"],
				["value"	=>	"Precio"],
				["value"	=>	"Importe"],
				["value"	=>	"% Incidencia"],
				["value"	=>	"Acción"]
			]
		];
		foreach($budgetDetails as $key => $bugt)
		{
			$amount		=	$this->returnString($bugt->amount);
			$price		=	$this->returnString($bugt->price);
			$import		=	$this->returnString($bugt->import);
			$incidence	=	$this->returnString($bugt->incidence);
			$id			=	$budgetDetails->firstItem() + $key;
			$code		=	$bugt->code;
			$tipo		=	'';

			switch ($bugt->type) {
				case 0: #0.- partida
					$tipo = 'Partida';
					# code...
					break;
				case 1: #1.- análisis
					$tipo = 'Análisis';
					# code...
					break;
				case 2: #2.- análisis título
					$tipo = 'Título';
					# code...
					break;
				case 3: #3.- grupo
					$tipo = 'Grupo';
					# code...
					break;
				case 4: #4.- concepto
					$tipo = 'Concepto';
					# code...
					break;
				case 5: #5.- importe
					$tipo = 'Importe';
					# code...
					break;
				case 6: #6.- rendimiento
					$tipo = 'Rendimiento';
					# code...
					break;
				case 7: #7.- subtotal
					$tipo = 'Subtotal';
					# code...
					break;
				case 8: #8.- costo directo
					$tipo = 'Costo directo';
					# code...
					break;
				
				default:
					# code...
					break;
			}

			$count	=	$key+ $budgetDetails->firstItem();
			$body	=
			[
				"attributeEx"	=>	"id=\"id-$bugt->id\"",
				"classEx"		=>	"id-row",
				[
					"content"	=>
					[
						["label"	=>	$count],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	15,
							"classEx"	=>	"hidden idd"
						],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$id,
							"classEx"	=>	"hidden"
						]
					]
				],
				[
					"content"	=>	["label"	=>	$tipo]
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$code,
						"classEx"	=>	"code"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->concept,
						"classEx"	=>	"concept"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->measurement,
						"classEx"	=>	"measurement"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$amount,
						"classEx"	=>	"amount"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$price,
						"classEx"	=>	"price"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$import,
						"classEx"	=>	"import"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$incidence,
						"classEx"	=>	"incidence"
					],
				],
				[
					"content"	=>
					[
						[
							"kind"			=>	"components.buttons.button",
							"variant"		=>	"success",
							"attributeEx"	=>	"id=\"edit\" type=\"button\"",
							"label"			=>	"<span class='icon-pencil'></span>",
							"classEx"		=>	"edit-item"
						]
					]
				],
			];
			$modelBody[]	=	$body;
		}
		$table_body .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
			"classEx" 			=>	"table",
			"attributeEx"		=>	"id=\"table\"",
			"attributeExBody"	=>	"id=\"table-body\"",
			"modelBody"			=>	$modelBody,
			"modelHead"			=>	$modelHead
		])));




		/* $table_body =
		'	<div class="table-responsive table-striped">
			<table id="table" class="table">
				<thead >
					<th>#</th>
					<th>Tipo</th>
					<th>Código</th>
					<th>Concepto</th>
					<th>Unidad</th>
					<th>Cantidad</th>
					<th>Precio</th>
					<th>Importe</th>
					<th>% Incidencia</th>
					<th>Acción</th>
				</thead>
				<tbody id="table-body">';

		foreach ($budgetDetails as $key => $bugt) {
			$amount = $this->returnString($bugt->amount);
			$price = $this->returnString($bugt->price);
			$import = $this->returnString($bugt->import);
			$incidence = $this->returnString($bugt->incidence);
			$id = $budgetDetails->firstItem() + $key;
			$code = $bugt->code;
			$tipo = '';

			switch ($bugt->type) {
				case 0: #0.- partida
					$tipo = 'Partida';
					# code...
					break;
				case 1: #1.- análisis
					$tipo = 'Análisis';
					# code...
					break;
				case 2: #2.- análisis título
					$tipo = 'Título';
					# code...
					break;
				case 3: #3.- grupo
					$tipo = 'Grupo';
					# code...
					break;
				case 4: #4.- concepto
					$tipo = 'Concepto';
					# code...
					break;
				case 5: #5.- importe
					$tipo = 'Importe';
					# code...
					break;
				case 6: #6.- rendimiento
					$tipo = 'Rendimiento';
					# code...
					break;
				case 7: #7.- subtotal
					$tipo = 'Subtotal';
					# code...
					break;
				case 8: #8.- costo directo
					$tipo = 'Costo directo';
					# code...
					break;
				
				default:
					# code...
					break;
			}

			$count = $key+ $budgetDetails->firstItem();
			$table_body .= 
			"<tr id='id-$bugt->id'>".
				"<td>$count</td>".
				"<td>$tipo</td>".
				"<td hidden>$id<label hidden class='id'>$bugt->id</label></td>".
				"<td><label class='code'>$code</label></td>".
				"<td><label class='concept'>$bugt->concept</label></td>".
				"<td><label class='measurement'>$bugt->measurement</label></td>".
				"<td><label class='amount'>$amount</label></td>".
				"<td><label class='price'>$price</label></td>".
				"<td><label class='import'>$import</label></td>".
				"<td><label class='incidence'>$incidence</label></td>".
				"<td>".
					"<button id='edit' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></button>".
				"</td>".
			"</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */
		
		return \Response::JSON(array(
			'UnitPricesUploads'       => $UnitPricesUploads, 
			'data'       => $budgetDetails, 
			'table'       => $table_body, 
			'pagination' => (string) $budgetDetails->links()
		));
		
	}

	public function UnitPricesEditArt(Request $request)
	{
		$bgup = App\UnitPricesDetails::where('id',$request->id)->first();

		$bgup->code 				= $request->code;
		$bgup->concept 			= $request->concept;
		$bgup->measurement 	= $request->measurement;
		$bgup->amount 			= $request->amount;
		$bgup->price 				= $request->price;
		$bgup->import 			= $request->import;
		$bgup->incidence 		= $request->incidence;
		
		$bgup->save();

		
		return \Response::JSON(array(
			'status'       => true
		));

	}

	public function UnitPricesFinish(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\UnitPricesUploads::where('id',$request->BudgetID)->first();

			$bgup->idproyect 	= $request->project_id;
			$bgup->name 			= $request->name;

			$bgup->client 		= $request->client;
			$bgup->contestNo 	= $request->contestNo;
			$bgup->obra 			= $request->obra;
			$bgup->place 			= $request->place;

			$old_date					= new \DateTime($request->startObra);
			$new_date 				= $old_date->format('Y-m-d');
			$bgup->startObra = $new_date;

			$old_date	= new \DateTime($request->endObra);
			$new_date = $old_date->format('Y-m-d');
			$bgup->endObra = $new_date;

			$bgup->status 		= 1;
			$bgup->save();

			$alert = "swal('', 'Precios Unitarios Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	
	public function UnitPricesFinishPaginateSearch(Request $request)
	{
		
		$project_id = $request->project_id;
			$name 			= $request->name;
			$start 			= $request->startObra;
			$end 				= $request->endObra;

			if ($start != null)
			{
				$date1 		= strtotime($start);
				$mindate 	= date('Y-m-d',$date1);
				$start 		= $mindate;
			}
			if($end != null)
			{
				$date2 		= strtotime($end);
				$maxdate 	= date('Y-m-d',$date2);
				
				$end 			= $maxdate;
			}

			$BudgetUploads = App\UnitPricesUploads::where(function($q) use ($project_id,$name,$start,$end){
				
				if($project_id)
					$q->where('idproyect',$project_id);

				if($name)
					$q->where('name','like',"%$name%");

				if($start && $end == null)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
				
				if($end && $start == null)
					$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				
				if($start && $end)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
						->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');

			})->paginate(10);

			$html 		= '';
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Inicio de obra"],
					["value"	=>	"Fin de obra"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"],
				]
			];
			foreach($BudgetUploads as $key => $bgup)
			{
				$startObra = \Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
				$endObra = \Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
				$proyect_name = $bgup->proyect->proyectName;
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bgup->id],
					],
					[						
						"content"	=>	["label"	=>	$bgup->name],
					],
					[
						"content"	=>	["label"	=>	$proyect_name],
					],
					[
						"content"	=>	["label"	=>	$bgup->startObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->endObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->status],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('UnitPrices.create.validate',$bgup->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('UnitPrices.excel',$bgup->id)."\"",
								"label"			=>	"<span class='icon-file-excel'></span>",
								"classEx"		=>	"export"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('UnitPrices.delete', $bgup->id)."\"",
								"label"			=>	"<span class='icon-x'></span>",
								"classEx"		=>	"export"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				'classEx' 		=> 'text-center',
				"modelBody"		=> $modelBody,
				"modelHead"		=> $modelHead
			])));
		
		/* $table_body =
		'	<div class="table-responsive">
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th width="5%">#</th>
						<th width="30%">Título</th>
						<th width="30%">Proyecto</th>
						<th width="10%">Inicio de obra</th>
						<th width="10%">Fin de obra</th>
						<th width="10%">Estado</th>
						<th width="15%">Acción</th>
					</tr> 
				</thead>
			<tbody>';

		foreach ($BudgetUploads as $key => $bgup) {

			$startObra = \Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
			$endObra = \Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
			$proyect_name = $bgup->proyect->proyectName;

			$table_body .= 
			"<tr>
			<td>$bgup->id</td>
			<td>$bgup->name</td>
			<td>$proyect_name</td>
			<td>$bgup->startObra</td>
			<td>$bgup->endObra</td>
			<td>$bgup->status</td>
			<td>
				<a href='".route('UnitPrices.create.validate',$bgup->id)."' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></a>
				<form method='get' action='".route('UnitPrices.excel',$bgup->id)."' accept-charset='UTF-8'>
				<button class='btn btn-green export' type='submit'  ><span class='icon-file-excel'></span></button>
				</form>
				<form method='post' action='".route('UnitPrices.delete') ."'>

				<input type='text' name='_token' hidden value='".csrf_token()."'>
				<input type='text' name='BudgetID' hidden value='$bgup->id'>
				<button class='btn btn-red export' type='submit'  ><span class='icon-x '></span></button>
				</form>
			</td>
		</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */

		if($BudgetUploads->count() == 0)
			// $html = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}


	public function UnitPricesExcel(Request $request)
	{
		

		$BudgetUploads = App\UnitPricesUploads::where('id',$request->id)->first();

		Excel::create('Precios Unitarios', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('Precios Unitarios',function($sheet) use ($BudgetUploads)
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

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));

			$sheet->getStyle('A1:H6')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			$sheet->getStyle('A8:H8')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));

				$sheet->row(1,['Cliente:',$BudgetUploads->client]);
				$sheet->row(2,['Concurso No:',$BudgetUploads->contestNo,'','','',
				'Duración:',\Carbon\Carbon::parse($BudgetUploads->endObra)->diffInDays(\Carbon\Carbon::parse($BudgetUploads->startObra))]);
				$sheet->row(3,['Obra:',$BudgetUploads->obra]);
				$sheet->row(4,['Lugar:',$BudgetUploads->place]);
				$sheet->row(5,[
					'',
					'',
					'','',
					'Inicio obra:',\Carbon\Carbon::parse($BudgetUploads->startObra)->format('d/m/Y'),
					]);
				$sheet->row(6,[
					'',
					'',
					'','',
					'Fin obra:',\Carbon\Carbon::parse($BudgetUploads->endObra)->format('d/m/Y'),
					]);


				$sheet->row(7,['']);
				$sheet->row(8,[
					'Código',
					'Concepto',
					'Unidad',
					'P. Unitario',
					'Op.',
					'Cantidad',
					'Importe',
					'%',
				]);
				$countHeigt = 7;
				$sheet->setAutoSize(false);
				$sheet->setWidth(array(
					'A' => 15,
					'B'	=>  100,
					'D' => 15,
					'E'	=> 15,
					'F'	=> 15,
					'G'	=> 15,
				));
				foreach(App\UnitPricesDetails::where('idUpload',$BudgetUploads->id)->where('type',0)->get() as $bgup)
				{

					$rowP = $this->UnitPricesGenerateRow($bgup);

					$sheet->appendRow($rowP);
					$countHeigt++;

					if($bgup->childrens()->count() >0)
						$countHeigt = $this->rowsUnitPrices($sheet,$bgup->childrens,$countHeigt);

				}
				$countHeigt++;
				$sheet->getStyle("A8:A".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '@' //text
				));
				$sheet->getStyle("E8:E".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				
				$sheet->getStyle("G8:G".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '$#,##0_-'
				));
				$sheet->getStyle("H8:H".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '0.0%'
				));
				$sheet->getStyle("B8:B".$countHeigt)->getAlignment()->applyFromArray(array(
					'wrap' => TRUE
				));
			});

		})->export('xls');
	}

	public function rowsUnitPrices($sheet,$bgup,$countHeigt)
	{
		foreach($bgup as $ch)
		{

			if($ch->childrens()->count() >0)
			{
				$rowP = $this->UnitPricesGenerateRow($ch);
				$sheet->appendRow($rowP);
				$countHeigt++;
				$countHeigt = $this->rowsUnitPrices($sheet,$ch->childrens,$countHeigt);
			}else
			{
				$rowP = $this->UnitPricesGenerateRow($ch);
				$sheet->appendRow($rowP);
				$countHeigt++;
			}
		}
		return $countHeigt;
	}

	public function UnitPricesGenerateRow($bgup)
	{
		$row = [];

		switch ($bgup->type) {
			case 0: #0.- partida
				$row[] = 'Partida';
				$row[] = $bgup->code;
				$row[] = 'Análisis No.:';
				$row[] = '';
				$row[] = $bgup->concept;
				break;
			case 1: #1.- análisis
				$row[] = 'Análisis:';
				$row[] = $bgup->concept;
				$row[] = '';
				$row[] = $bgup->measurement;
				break;
			case 2: #2.- análisis título
				$row[] = $bgup->concept;
				break;
			case 3: #3.- grupo
				$row[] = $bgup->concept;
				# code...
				break;
			case 4: #4.- concepto
				$row[] = $bgup->code;
				$row[] = $bgup->concept;
				$row[] = $bgup->measurement;
				$row[] = $bgup->price;
				$row[] = $bgup->op;
				$row[] = $bgup->amount;
				$row[] = $bgup->import;
				$row[] = $bgup->incidence / 100;
				# code...
				break;
			case 5: #5.- importe
				$row[] = '';
				$row[] = 'Importe:';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $bgup->import;
				break;
			case 6: #6.- rendimiento
				$row[] = '';
				$row[] = $bgup->concept;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $bgup->amount;
				$row[] = $bgup->import;
				$row[] = $bgup->incidence / 100;
				break;
			case 7: #7.- subtotal
				$row[] = 'SUBTOTAL:';
				$row[] = $bgup->concept;
				$row[] = $bgup->import;
				$row[] = $bgup->incidence / 100;
				break;
			case 8: #8.- costo directo
				$row[] = '';
				$row[] = 'Costo Directo:';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $bgup->import;
				$row[] = $bgup->incidence / 100;
				break;
			
			default:
				# code...
				break;
		}
		return $row;
	}

	public function UnitPricesDelete($budget_id)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\UnitPricesUploads::find($budget_id);
			if($bgup->status != 'Subiendo')
			{
				\Storage::disk('public')->delete($bgup->file);
				App\UnitPricesDetails::where('idUpload',$budget_id)->delete();
				App\UnitPricesUploads::where('id',$budget_id)->delete();
				$alert = "swal('', 'Precios Unitarios Eliminado Exitosamente', 'success');";
			}
			else
			{
				$alert = "swal('', 'Espere a que termine de subir para poder eliminar.', 'warning');";
			}
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	/*
	|--------------------------------------------------------------------------
	| ObraProgram functions
	|--------------------------------------------------------------------------
	*/
	
	public function ObraProgramCreate()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.programa_obra',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 226
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function ObraProgramSend(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name		= '/docs/ObraProgram/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($request->file('file')));
			$path		= \Storage::disk('public')->path($name);

			$bgup = App\ObraProgramUploads::create([
				'idproyect' => $request->get('project_id'),
				'file' => $name,
				'idCreate' => Auth::user()->id,
				'status' => 0,
				'name' => $request->get('name'),
			]);

			dispatch(new ObraUploadObraProgram($bgup));

			return redirect()->route('ObraProgram.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function ObraProgramValidate($budget_id)
	{
		$bgup = App\ObraProgramUploads::where('id',$budget_id)->first();
		$data	= App\Module::find($this->module_id);

		if(Auth::user()->module->where('id',$this->module_id)->count()>0 )
		{
			return view('administracion.presupuestos.editar_programa_obra',
				[
					'id'           => $data['father'],
					'title'        => $data['name'],
					'details'      => $data['details'],
					'child_id'     => $this->obra_child_id,
					'option_id'    => 226,
					'budget_id'    => $budget_id,
					'budgetUpload' => App\ObraProgramUploads::where('id',$budget_id)->first(),
				]);
		}
		else
		{
			return redirect('/');
		}
		

	}

	
	public function ObraProgramPaginateArts(Request $request)
	{
		

		$ObraProgramUploads = App\ObraProgramUploads::where('id',$request->budgetUpload)->first();

		$bgup = App\ObraProgramUploads::where('id',$request->budgetUpload)->first();

		$budgetDetails = App\ObraProgramConcept::
			where('idUpload',$request->budgetUpload)
			->where(function($q) use($request){
				if($request->search != null)
					$q->where('code','like',"%$request->search%")
						->orWhere('concept','like',"%$request->search%")
						->orWhere('measurement','like',"%$request->search%");
			})
		->paginate(20);
		
		
		// todo --------------------	Editando	--------------------
		$table_body	=	"";
		$modelHead	=	[];
		$body		=	[];
		$modelBody	=	[];
		$modelHead	=
		[
			[
				["value"	=>	"Grupo"],
				["value"	=>	"Código"],
				["value"	=>	"Concepto"],
				["value"	=>	"Unidad"],
			]
		];
		
		$bc = App\ObraProgramConcept::where('idUpload',$request->budgetUpload)->has('details')->first();
		$bcount = $bc ? $bc->details()->where('type',0)->count() : 0;
		

		for ($i=1; $i <= $bcount; $i++)
		{
			$heads	=	["value"	=>	$bgup->date_type." ".$i];
			$modelHead[0][]	=	$heads;
		}
		$heads	= ["value"	=>	"Acción"];
		$modelHead[0][]	=	$heads;
		foreach ($budgetDetails as $key => $bugt)
		{
			$group_name = $bugt->parent ? $bugt->parent->concept : '';
			$body	=
			[
				"attributeEx"	=>	"id=\"id-$bugt->id\"",
				"classEx"		=>	"row",
				[
					"content"	=>
					[
						[
							"kind"	=>	"components.labels.label",
							"label"	=>	$group_name
						],
						[
							"kind"		=>	"components.labels.label",
							"classEx"	=>	"id-row hidden",
							"label"		=>	$bugt->id,
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->code,
							"classEx"	=>	"code"
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->concept,
							"classEx"	=>	"concept"
						]
					],
				],
				[
					"content"	=>
					[
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->measurement,
							"classEx"	=>	"measurement"
						]
					],
				],
			];
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',0);
			})->get() as $child) {
	
				$amount = $this->returnString($child->amount);
				$body[]	=
				[
					"content"	=>
					[
						"label"	=>	$amount
					],
				];
			}
			
			$body[]	=
			[
				"content"	=>
				[
					"kind"			=>	"components.buttons.button",
					"variant"		=>	"success",
					"label"			=>	"<span class='icon-pencil'>",
					"attributeEx"	=>	"id=\"edit\" type=\"button\"",
					"classEx"		=>	"edit-item"
				],
			];
			$body2	=	[];
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',1);
			})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				$body2[]	=
				[
					"content"	=>
					[
						"label"	=>	$amount
					],
				];
			}
			$body3	=	[];
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',2);
			})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				$body3[]	=
				[
					"content"	=>
					[
						"label"	=>	$amount
					],
				];
			}
			$modelBody[]	=	$body;
			$modelBody[]	=	$body2;
			$modelBody[]	=	$body3;
			
		}

		// foreach($budgetDetails as $key => $bugt)
		// {
			
		// 	foreach ($bugt->details()->where( function($q)
		// 	{
		// 		$q->where('type',0);
		// 	})->get() as $child)
		// 	{
		// 		$amount = $this->returnString($child->amount);
		// 		print_r($amount);
		// 	}
		// }
		// return "asd";
		/* foreach($budgetDetails as $key => $bugt)
		{
			$group_name = $bugt->parent ? $bugt->parent->concept : '';
			// $details_count = count($bugt->details) > 0 ? 3 : 1;
			// $colspan = 1;
			// if($bugt->details()->where( function($q)
			// {
			// 	$q->where('type',0);
			// })->count() == 0)
			// $colspan = $bcount + 1;

			$modelBody	=
			[
				"attributeEx"	=>	"id=\"id-".$bugt->id."\"",
				[
					"show"		=>	"true",
					"content"	=>
					[
						[
							"kind"	=>	"components.labels.label",
							"label"	=>	$group_name
						],
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$bugt->id,
							"classEx"	=>	"hidden id"
						],
					],
				],
				[
					"show"		=>	"true",
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->code,
						"classEx"	=>	"code"
					],
				],
				[
					"show"		=>	"true",
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->concept,
						"classEx"	=>	"concept"
					],
				],
				[
					"content"	=>
					[
						"kind"		=>	"components.labels.label",
						"label"		=>	$bugt->measurement,
						"classEx"	=>	"measurement"
					],
				],
			];

			
			
			$modelBody[] = $body;
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',0);
			})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				
				$body2	=
				[
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$amount,
						],
					]
				];
				
				$modelBody[] = $body2;
			}
			
			dd($modelBody);
			$body[]	=
			[
				[
					"content"	=>
					[
						"kind"		=>	"components.buttons.button",
						"variant"	=>	"secondary",
						"label"		=>	"<span class='icon-pencil'></span>",
						"attributeEx"	=>	"id=\"edit\" type=\"button\"",
						"clasEx"	=>	"edit-item"
					],
				],
			];
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',1);
			})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				
				$body[]	=
				[
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$amount,
						],
					],
				];
			}
			foreach ($bugt->details()->where( function($q)
			{
				$q->where('type',2);
			})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				$body[]	=
				[
					[
						"content"	=>
						[
							"kind"		=>	"components.labels.label",
							"label"		=>	$amount,
						],
					],
				];
			}
		} */
		// $modelBody[]	=	$body;
// dd($modelBody);
// $body = 
// [
	
// 		["show" => "true","content" => [["label" => "asdasd"]]],
// 		["show" => "true","content" => [["label" => "asdasd"]]],
// 		["show" => "true","content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
// 		["content" => [["label" => "asdasd"]]],
	
// ];
// $modelBody[] = $body;

		$table_body .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
			"modelBody"			=>	$modelBody,
			"modelHead"			=>	$modelHead,
			"attributeEx"		=>	"id=\"table\"",
			'classEx' 			=>	"table",
			"attributeExBody"	=>	"id=\"table-body\""
		])));
		// todo --------------------	Termina editando	--------------------


		// @@ --------------------	Original	--------------------
		// $table_body .=
		// '	<div class="table-responsive table-striped">
		// 	<table id="table" class="table">
		// 		<thead >
		// 			<th>Grupo</th>
		// 			<th>Código</th>
		// 			<th>Concepto</th>
		// 			<th>Unidad</th>'; 
		

		// $bc = App\ObraProgramConcept::
		// where('idUpload',$request->budgetUpload)
		// ->has('details')->first();

		// $bcount = $bc ? $bc->details()->where('type',0)->count() : 0;

		// for ($i=1; $i <= $bcount; $i++) { 
		// 	$table_body .= "<th>$bgup->date_type $i</th>";
		// }


		// $table_body .= '<th>Acción</th>
		// 		</thead>
		// 		<tbody id="table-body">';

		// foreach ($budgetDetails as $key => $bugt) {
			

		// 	$group_name = $bugt->parent ? $bugt->parent->concept : '';

		// 	$details_count = count($bugt->details) > 0 ? 3 : 1;

		// 	$colspan = 1;
		// 	if($bugt->details()->where( function($q) {
		// 		$q->where('type',0);
		// 	})->count() == 0)
		// 		$colspan = $bcount + 1;

		// 	$table_body .= 
		// 	"<tr id='id-$bugt->id' style='border:2px solid #000000;'>".
		// 		"<td hidden>$bugt->id<label hidden class='id'>$bugt->id</label></td>".
		// 		"<td rowspan='$details_count'><label class=''>$group_name</label></td>".
		// 		"<td rowspan='$details_count'><label class='code'>$bugt->code</label></td>".
		// 		"<td rowspan='$details_count'><label class='concept'>$bugt->concept</label></td>".
		// 		"<td rowspan='$details_count' colspan='$colspan'><label class='measurement'>$bugt->measurement</label></td>";
			
		// 	foreach ($bugt->details()->where( function($q) {
		// 		$q->where('type',0);
		// 	})->get() as $child) {

		// 		$amount = $this->returnString($child->amount);
		// 		$table_body .= "<td>$amount</td>";
		// 	}


		// 	$table_body .= "<td rowspan='$details_count'>".
		// 			"<button id='edit' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></button>".
		// 		"</td>".
		// 	"</tr>";
			
			
		// 	$table_body .= '<tr>';
		// 	foreach ($bugt->details()->where( function($q) {
		// 		$q->where('type',1);
		// 	})->get() as $child) {

		// 		$amount = $this->returnString($child->amount);
		// 		$table_body .= "<td>$amount</td>";
		// 	}
		// 	$table_body .= '</tr>';
			
		// 	$table_body .= '<tr>';
		// 	foreach ($bugt->details()->where( function($q) {
		// 		$q->where('type',2);
		// 	})->get() as $child) {

		// 		$amount = $this->returnString($child->amount);
		// 		$table_body .= "<td>$amount</td>";
		// 	}
		// 	$table_body .= '</tr>';


		// }
		// $table_body .=
		// '</tbody>
		// </table>
		// </div>';
		
		return \Response::JSON(array(
			'ObraProgramUploads'       => $ObraProgramUploads, 
			'data'       => $budgetDetails, 
			'table'       => $table_body, 
			'pagination' => (string) $budgetDetails->links()
		));
		
	}

	public function ObraProgramPaginateArtsEdit(Request $request)
	{
		$bugt = App\ObraProgramConcept::where('id',$request->id)->first();
		$BudgetUploads =  App\ObraProgramUploads::where('id',$bugt->idUpload)->first();

		$bcount = $bugt->details()->where( function($q) {
			$q->where('type',0);
		})->count();
		$type = $bcount > 0 ? 'details' : 'concept';


		// @@ ----------------	Cambios	----------------

		
		$modal2	=
		'<div class="w-full col-span-1 mb-4">';
			$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
				"label"	=>	"Código"
			])));
			$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
				"attributeEx"	=>	"type=\"text\" name=\"code\" placeholder=\"Ingrese un código\" data-validation=\"required\" value=\"".$bugt->code."\"",
				"classEx"		=>	"remove"
			])));

		$modal2	.=
		'	</div>
			<div class="w-full col-span-1 mb-4"> ';

			$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
				"label"	=>	"Concepto"
			])));
			$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
				"attributeEx"	=>	"type=\"text\" name=\"concept\" placeholder=\"Ingrese un concepto\" data-validation=\"required\" value=\"".$bugt->concept."\"",
				"classEx"		=>	"remove"
			])));

		$modal2	.=
		'	</div>';
		$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
			"attributeEx"	=>	"name=\"type\" value=\"".$type."\"",
			"classEx"		=>	"hidden"
		])));
		$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
			"attributeEx"	=>	"name=\"id\" value=\"".$bugt->id."\"",
			"classEx"		=>	"hidden"
		])));
		$optionsMeasurements = [];
		foreach(App\CatMeasurementTypes::whereNotNull('type')->get() as $m_types)
		{
			foreach ($m_types->childrens()->orderBy('child_order','asc')->get() as $child)
			{
				if ($child->abbreviation == $bugt->measurement)
				{
					$optionsMeasurements[]	=
					[
						"value" 		=>	$child->id,
						"description"	=>	$child->abbreviation,
						"selected"		=>	"selected"
					];
				}
				else
				{
					$optionsMeasurements[]	=
					[
						"value" 		=>	$child->id,
						"description"	=>	$child->abbreviation,
					];
				}
			}
		}
		$modal2	.=
		'	<div class="w-full col-span-1 mb-4"> ';

		$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
			"label"			=>	"Medida"
		])));
		$modal2 .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.select", [
			"attributeEx"	=>	"name=\"measurement\"  multiple=\"multiple\"",
			"classEx"		=>	"js-measurement removeselect",
			"options"		=>	$optionsMeasurements
		])));
		$modal2 .= '</div>';
		$modal1 = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.containers.container-form", [
			"content" => $modal2
		])));

		if ($bcount > 0)
		{
			$modelHead	=	[];
			$modelBody	=	[];
			for ($i=1; $i <= $bcount; $i++) { 
				$heads	= ["value"	=>	$BudgetUploads->date_type." ".$i];
				$modelHead[0][] = $heads;
			}
			$body = [];
			$rowCounter = 0;
			foreach($bugt->details()->where( function($q) {$q->where('type',0);})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				if($rowCounter <= 0)
				{
					$body[]	=
					[
						"show"		=>	"true",
						"content"	=>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				else
				{
					$body[]	=
					[
						"content" =>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				$rowCounter++;
				$modelBody[0]	=	$body;
			}
			$body = [];
			$rowCounter = 0;
			foreach($bugt->details()->where( function($q) {$q->where('type',1);})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				if($rowCounter <= 0)
				{
					$body[]	=
					[
						"show"		=>	"true",
						"content"	=>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				else
				{
					$body[]	=
					[
						"content" =>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				$rowCounter++;
				$modelBody[1]	=	$body;
			}
			$body = [];
			$rowCounter = 0;
			foreach($bugt->details()->where( function($q) {$q->where('type',2);})->get() as $child)
			{
				$amount = $this->returnString($child->amount);
				if($rowCounter <= 0)
				{
					$body[]	=
					[
						"show"	=>	"true",
						"content" =>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				else
				{
					$body[]	=
					[
						"content" =>
						[
							"kind"			=>	"components.inputs.input-text",
							"attributeEx"	=>	"name=\"$child->id\" value=\"".$amount."\"",
							"classEx"		=>	"remove amount w-40"
						],
					];
				}
				$rowCounter++;
				$modelBody[2]	=	$body;
			}
		}
		$modal = html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table",
		[
			"modelBody"		=> $modelBody,
			"modelHead"		=> $modelHead,
			"classEx"		=>	"mt-8"
		])));
		return \Response::JSON(array(
			'modal'       => $modal1.$modal, 
		));
	}

	public function ObraProgramArtEdit(Request $request)
	{
		
		$concept = App\ObraProgramConcept::where('id',$request->id)->update([
			'code'	=>				$request->code,
			'concept'	=>			$request->concept,
			'measurement'	=>	$request->measurement,
		]);
		

		foreach ($request->details as $key => $value) {
			App\ObraProgramDetails::where('id',$key)->update([
				'amount' => $value
			]);
		}


		
		return \Response::JSON(array(
			'status'       => true
		));

	}
	
	public function ObraPaginateSearch(Request $request)
	{
		
			$project_id = $request->project_id;
			$name 			= $request->name;
			$start 			= $request->startObra;
			$end 				= $request->endObra;

			if ($start != null)
			{
				$date1 		= strtotime($start);
				$mindate 	= date('Y-m-d',$date1);
				$start 		= $mindate;
			}
			if($end != null)
			{
				$date2 		= strtotime($end);
				$maxdate 	= date('Y-m-d',$date2);
				
				$end 			= $maxdate;
			}

			$BudgetUploads = App\ObraProgramUploads::where(function($q) use ($project_id,$name,$start,$end){
				
				if($project_id)
					$q->where('idproyect',$project_id);

				if($name)
					$q->where('name','like',"%$name%");

				if($start && $end == null)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'');
				
				if($end && $start == null)
					$q->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');
				
				if($start && $end)
					$q->whereDate('startObra','>=',''.$start.' '.date('00:00:00').'')
						->whereDate('endObra','<=',''.$end.' '.date('23:59:59').'');

			})->paginate(10);

			$html 		=	'';
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Inicio de obra"],
					["value"	=>	"Fin de obra"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"],
				]
			];
			foreach($BudgetUploads as $key => $bgup)
			{
				$startObra		=	\Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
				$endObra		=	\Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
				$proyect_name	=	$bgup->proyect->proyectName;
				$body	=
				[
					[						
						"content"	=>	["label"	=>	$bgup->id],
					],
					[
						"content"	=>	["label"	=>	$bgup->name],
					],
					[
						"content"	=>	["label"	=>	$proyect_name],
					],
					[
						"content"	=>	["label"	=>	$bgup->startObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->endObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->status],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('ObraProgram.create.validate',$bgup->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('ObraProgram.excel',$bgup->id)."\"",
								"label"			=>	"<span class='icon-file-excel'></span>",
								"classEx"		=>	"export"
							],
							[								
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('ObraProgram.delete', $bgup->id)."\"",
								"label"			=>	"<span class='icon-x '></span>",
								"classEx"		=>	"export",
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				'classEx' 		=> 'text-center',
				"modelBody"		=> $modelBody,
				"modelHead"		=> $modelHead
			])));
		
		/* $table_body =
		'	<div class="table-responsive">
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th width="5%">#</th>
						<th width="30%">Título</th>
						<th width="30%">Proyecto</th>
						<th width="10%">Inicio de obra</th>
						<th width="10%">Fin de obra</th>
						<th width="10%">Estado</th>
						<th width="15%">Acción</th>
					</tr> 
				</thead>
			<tbody>';

		foreach ($BudgetUploads as $key => $bgup) {

			$startObra = \Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
			$endObra = \Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
			$proyect_name = $bgup->proyect->proyectName;

			$table_body .= 
			"<tr>
			<td>$bgup->id</td>
			<td>$bgup->name</td>
			<td>$proyect_name</td>
			<td>$bgup->startObra</td>
			<td>$bgup->endObra</td>
			<td>$bgup->status</td>
			<td>
				<a href='".route('ObraProgram.create.validate',$bgup->id)."' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></a>
				<form method='get' action='".route('ObraProgram.excel',$bgup->id)."' accept-charset='UTF-8'>
				<button class='btn btn-green export' type='submit'  ><span class='icon-file-excel'></span></button>
				</form>
				<form method='post' action='".route('ObraProgram.delete') ."'>

				<input type='text' name='_token' hidden value='".csrf_token()."'>
				<input type='text' name='BudgetID' hidden value='$bgup->id'>
				<button class='btn btn-red export' type='submit'  ><span class='icon-x '></span></button>
				</form>
			</td>
		</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>';
 */
		if($BudgetUploads->count() == 0)
			// $table_body = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}

	public function ObraProgramExcel(Request $request)
	{
		

		$BudgetUploads = App\ObraProgramUploads::where('id',$request->id)->first();

		Excel::create('Programa de Obra', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('Programa de Obra',function($sheet) use ($BudgetUploads)
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

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));

			$sheet->getStyle('A1:G5')->applyFromArray(array(
				'borders' => array(
					'outline' => array(
						'style' => 'double',
						'color' => array('rgb' => '000000'),
					),
				),
			));
			

				$sheet->row(1,['Cliente:',$BudgetUploads->client]);
				$sheet->row(2,['Concurso No:',$BudgetUploads->contestNo,'','','',
				'Duración:',\Carbon\Carbon::parse($BudgetUploads->endObra)->diffInDays(\Carbon\Carbon::parse($BudgetUploads->startObra))]);
				$sheet->row(3,['Obra:',$BudgetUploads->obra]);
				$sheet->row(4,[
					'Lugar:',$BudgetUploads->place,
					'',
					'Inicio obra:',\Carbon\Carbon::parse($BudgetUploads->startObra)->format('d/m/Y'),
					]);
				$sheet->row(5,[
					'Ciudad:',$BudgetUploads->city,
					'',
					'Fin obra:',\Carbon\Carbon::parse($BudgetUploads->endObra)->format('d/m/Y'),
					]);


				$sheet->row(6,['']);
				$bc = App\ObraProgramConcept::
					where('idUpload',$BudgetUploads->id)
					->has('details')->first();
				$bcount = $bc ? $bc->details()->where('type',0)->count() : 0;

				$headers = [
					'Código',
					'Descripción',
					'Unidad',
				];

				for ($i=1; $i <= $bcount; $i++) {
					array_push($headers,"$BudgetUploads->date_type $i");
				}
				array_push($headers,"Total");
				$sheet->row(7,$headers);
				$countHeigt = 7;
				$currentRow = 7;
				$sheet->setAutoSize(false);
				$sheet->setWidth(array(
					'A' => 15,
					'B'	=>  100,
					'D' => 15,
					'E'	=> 15,
					'F'	=> 15,
					'G'	=> 15,
				));
				foreach(App\ObraProgramConcept::where('idUpload',$BudgetUploads->id)->whereNull('father')->get() as $bgup)
				{

					$rowP =[];
					$rowP[] = $bgup->code;
					$rowP[] = $bgup->concept;
					$rowP[] = $bgup->measurement;
					$sheet->appendRow($rowP);
					$countHeigt++;

					foreach ($bgup->childrens as $child) {
						$rowP = ['','',''];
						$rowP =[];
						$rowP[] = $child->code;
						$rowP[] = $child->concept;
						$rowP[] = $child->measurement;

						$t_amount = 0;
						foreach ($child->details()->where( function($q) {$q->where('type',0);})->get() as $d) {
							$rowP[] = $this->returnString($d->amount);
							$t_amount+= $d->amount;
						}
						$rowP[] = $this->returnString($t_amount);
						
						
						$sheet->appendRow($rowP);
						$countHeigt++;


						$rowName = 'D' . $countHeigt . ':' . $sheet->getHighestColumn() . $countHeigt;
						$sheet->getStyle($rowName)->getNumberFormat()->applyFromArray(array(
							'code' => '0.0%'
						));
						
						$t_cantidad = 0;
						if($child->details()->where( function($q) {$q->where('type',1);})->count() > 0)
						{
							$rowP =[];
							$rowP = ['','',''];
							foreach ($child->details()->where( function($q) {$q->where('type',1);})->get() as $d) {
								$rowP[] = $this->returnString($d->amount);
								$t_cantidad+= $d->amount;
							}
							$rowP[] = $this->returnString($t_cantidad);
							$sheet->appendRow($rowP);
							$countHeigt++;
							$rowName = 'D' . $countHeigt . ':' . $sheet->getHighestColumn() . $countHeigt;
							$sheet->getStyle($rowName)->getNumberFormat()->applyFromArray(array(
								'code' => '0'
							));
						}
						$t_price = 0;
						if($child->details()->where( function($q) {$q->where('type',2);})->count() >0)
						{
							$rowP =[];
							$rowP = ['','',''];
							foreach ($child->details()->where( function($q) {$q->where('type',2);})->get() as $d) {
								$rowP[] = $this->returnString($d->amount);
								$t_price+=$d->amount;
							}
							$rowP[] = $this->returnString($t_price);
							$sheet->appendRow($rowP);
							
							$countHeigt++;
							$rowName = 'D' . $countHeigt . ':' . $sheet->getHighestColumn() . $countHeigt;
							$sheet->getStyle($rowName)->getNumberFormat()->applyFromArray(array(
								'code' => '"$"#,##0.00_-'
							));
						}
						
					}


				}
				$sheet->getStyle("A8:A".$countHeigt)->getNumberFormat()->applyFromArray(array(
					'code' => '@' //text
				));
				$rowName = 'A7:' . $sheet->getHighestColumn().'7';
				$sheet->getStyle($rowName)->getBorders()->applyFromArray(
					array(
						'allborders' => array(
							'style' => 'medium',
							'color' => array(
								'rgb' => '000000'
								)
							)
						)
				);
				$sheet->getStyle($rowName)->applyFromArray(array(
					'borders' => array(
						'outline' => array(
							'style' => 'double',
							'color' => array('rgb' => '000000'),
						),
					),
				));

				//auto size all columns
				$letters = [];
				$letter = 'D';
				while ($letter !== $sheet->getHighestDataColumn()) {
						$letters[] = $letter++;
				}
				$letters[] = $letter++;


				foreach ($letters as $col) {
						$sheet->getColumnDimension($col)->setAutoSize(true);
				}
				
				$sheet->getStyle("B8:H".$countHeigt)->getAlignment()->applyFromArray(array(
					'wrap' => TRUE
				));
			});
		})->export('xls');
	}

	public function ObraProgramDelete($budget_id)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\ObraProgramUploads::find($budget_id);
			\Storage::disk('public')->delete($bgup->file);
			App\ObraProgramConcept::where('idUpload',$budget_id)->delete();
			App\ObraProgramUploads::where('id',$budget_id)->delete();
			$alert = "swal('', 'Programa de Obra Eliminado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function ObraProgramFinish(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\ObraProgramUploads::where('id',$request->BudgetID)->first();

			$bgup->name 			= $request->name;
			$bgup->idproyect 		= $request->project_id;

			$bgup->client 			= $request->client;
			$bgup->contestNo 		= $request->contestNo;
			$bgup->obra 			= $request->obra;
			$bgup->place 			= $request->place;
			$bgup->city 			= $request->city;
			$old_date				= new \DateTime($request->startObra);
			$new_date 				= $old_date->format('Y-m-d');
			$bgup->startObra 		= $new_date;

			$old_date	= new \DateTime($request->endObra);
			$new_date = $old_date->format('Y-m-d');
			$bgup->endObra = $new_date;

			$bgup->date_type = $request->date_type;

			$bgup->status 		= 1;
			$bgup->save();

			$alert = "swal('', 'Listado de Insumos Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}


	/*
	|--------------------------------------------------------------------------
	| Sobrecosto functions
	|--------------------------------------------------------------------------
	*/

	public function SobrecostoCreate()
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('administracion.presupuestos.sobrecosto',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 227
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function SobrecostoSend(Request $request)
	{
		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$name		= '/docs/SobreCosto/AdG'.time().'_'.Auth::user()->id.'.'.$request->file('file')->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($request->file('file')));

			$bgup = App\CostOverruns::create([
				'idproyect' => $request->get('project_id'),
				'file' 		=> $name,
				'idCreate' 	=> Auth::user()->id,
				'status'	=> 0,
				'name' 		=> $request->get('name'),
			]);

			dispatch(new ObraUploadSobrecostos($bgup));
			
			return redirect()->route('Sobrecosto.create.validate',['budget_id'=>$bgup->id]);
		}
		else
		{
			return redirect('/');
		}
	}

	

	public function SobrecostoValidate($budget_id)
	{
		
		$data   = App\Module::find($this->module_id);
		return view('administracion.presupuestos.editar_sobrecosto',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->obra_child_id,
					'option_id' => 227,
					'budget_id' => $budget_id,
					'stepp'     => 1,
				]);
	}

	public function SobrecostoGeneralesSave($budget_id,Request $request)
	{
		
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$budget_id)->first();
			$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$budget_id)->first();
			$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$budget_id)->first();
			$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$budget_id)->first();
			$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$budget_id)->first();
			$CostOverrunsNCGAnnouncement = App\CostOverrunsNCGAnnouncement::where('idUpload',$budget_id)->first();

			$CostOverrunsNCGEnterprise->razonsocial = $request->razonsocial;
			$CostOverrunsNCGEnterprise->domicilio = $request->domicilio;
			$CostOverrunsNCGEnterprise->colonia = $request->colonia;
			$CostOverrunsNCGEnterprise->ciudad = $request->ciudad;
			$CostOverrunsNCGEnterprise->estado = $request->estado;
			$CostOverrunsNCGEnterprise->rfc = $request->rfc;
			$CostOverrunsNCGEnterprise->telefono = $request->telefono;
			$CostOverrunsNCGEnterprise->email1 = $request->email1;
			$CostOverrunsNCGEnterprise->email2 = $request->email2;
			$CostOverrunsNCGEnterprise->email3 = $request->email3;
			$CostOverrunsNCGEnterprise->cmic = $request->cmic;
			$CostOverrunsNCGEnterprise->infonavit = $request->infonavit;
			$CostOverrunsNCGEnterprise->imss = $request->imss;
			$CostOverrunsNCGEnterprise->responsable = $request->responsable;
			$CostOverrunsNCGEnterprise->cargo = $request->cargo;
			$CostOverrunsNCGEnterprise->save();

			$CostOverrunsNCGCustomers->nombrecliente = $request->nombrecliente;
			$CostOverrunsNCGCustomers->area = $request->area;
			$CostOverrunsNCGCustomers->departamento = $request->departamento;
			$CostOverrunsNCGCustomers->direccioncliente = $request->direccioncliente;
			$CostOverrunsNCGCustomers->coloniacliente = $request->coloniacliente;
			$CostOverrunsNCGCustomers->codigopostalcliente = $request->codigopostalcliente;
			$CostOverrunsNCGCustomers->ciudadcliente = $request->ciudadcliente;
			$CostOverrunsNCGCustomers->telefonocliente = $request->telefonocliente;
			$CostOverrunsNCGCustomers->emailcliente = $request->emailcliente;
			$CostOverrunsNCGCustomers->contactocliente = $request->contactocliente;
			$CostOverrunsNCGCustomers->save();


			
			$CostOverrunsNCGCompetition->fechadeconcurso = \Carbon\Carbon::parse($request->fechadeconcurso)->format('Y-m-d');
			$CostOverrunsNCGCompetition->numerodeconcurso = $request->numerodeconcurso;
			$CostOverrunsNCGCompetition->direcciondeconcurso = $request->direcciondeconcurso;
			$CostOverrunsNCGCompetition->save();

			$CostOverrunsNCGConstruction->nombredelaobra = $request->nombredelaobra;
			$CostOverrunsNCGConstruction->direcciondelaobra = $request->direcciondelaobra;
			$CostOverrunsNCGConstruction->coloniadelaobra = $request->coloniadelaobra;
			$CostOverrunsNCGConstruction->ciudaddelaobra = $request->ciudaddelaobra;
			$CostOverrunsNCGConstruction->estadodelaobra = $request->estadodelaobra;
			$CostOverrunsNCGConstruction->codigopostaldelaobra = $request->codigopostaldelaobra;
			$CostOverrunsNCGConstruction->telefonodelaobra = $request->telefonodelaobra;
			$CostOverrunsNCGConstruction->emaildelaobra = $request->emaildelaobra;
			$CostOverrunsNCGConstruction->responsabledelaobra = $request->responsabledelaobra;
			$CostOverrunsNCGConstruction->cargoresponsabledelaobra = $request->cargoresponsabledelaobra;
			$CostOverrunsNCGConstruction->fechainicio = \Carbon\Carbon::parse($request->fechainicio)->format('Y-m-d');
			$CostOverrunsNCGConstruction->fechaterminacion = \Carbon\Carbon::parse($request->fechaterminacion)->format('Y-m-d');
			$CostOverrunsNCGConstruction->totalpresupuestoprimeramoneda = $request->totalpresupuestoprimeramoneda;
			$CostOverrunsNCGConstruction->totalpresupuestosegundamoneda = $request->totalpresupuestosegundamoneda;
			$CostOverrunsNCGConstruction->porcentajeivapresupuesto = $request->porcentajeivapresupuesto;
			$CostOverrunsNCGConstruction->save();

			$CostOverrunsNCGHeader->plazocalculado = $request->plazocalculado;
			$CostOverrunsNCGHeader->plazoreal = $request->plazoreal;
			$CostOverrunsNCGHeader->decimalesredondeo = $request->decimalesredondeo;
			$CostOverrunsNCGHeader->primeramoneda = $request->primeramoneda;
			$CostOverrunsNCGHeader->segundamoneda = $request->segundamoneda;
			$CostOverrunsNCGHeader->remateprimeramoneda = $request->remateprimeramoneda;
			$CostOverrunsNCGHeader->rematesegundamoneda = $request->rematesegundamoneda;
			$CostOverrunsNCGHeader->save();

			$CostOverrunsNCGAnnouncement->numconvocatoria = $request->numconvocatoria;
			$CostOverrunsNCGAnnouncement->fechaconvocatoria = \Carbon\Carbon::parse($request->fechaconvocatoria)->format('Y-m-d');
			$CostOverrunsNCGAnnouncement->tipodelicitacion = $request->tipodelicitacion;
			$CostOverrunsNCGAnnouncement->save();

			$alert = "swal('', 'Campos Generales Guardados Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 2,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 2,
					]);
		}

	}

	public function SobrecostoDatosObraSave($budget_id,Request $request)
	{

		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{
			$COCValues = App\COCValues::where('idUpload',$budget_id)->first();
			$COCValuesThatApply = App\COCValuesThatApply::where('idUpload',$budget_id)->first();
			$COCRequiredValues = App\COCRequiredValues::where('idUpload',$budget_id)->first();
			$COCAdvanceTypeTable = App\COCAdvanceTypeTable::where('idUpload',$budget_id)->first();
			$COCSecondAdvanceTypeTable = App\COCSecondAdvanceTypeTable::where('idUpload',$budget_id)->first();

			$COCValues->costodirectodelaobra = $request->costodirectodelaobra;
			$COCValues->importetotaldelamanodeobragravable = $request->importetotaldelamanodeobragravable;
			$COCValues->importetotaldelaobra = $request->importetotaldelaobra;
			$COCValues->factorparalaobtenciondelasfp = $request->factorparalaobtenciondelasfp;
			$COCValues->porcentajedeutilidadbrutapropuesta = $request->porcentajedeutilidadbrutapropuesta;
			$COCValues->tasadeinteresusada = $request->tasadeinteresusada;
			$COCValues->puntosdelbanco = $request->puntosdelbanco;
			$COCValues->indicadoreconomicodereferencia = $request->indicadoreconomicodereferencia;
			$COCValues->isr = $request->isr;
			$COCValues->ptu = $request->ptu;
			$COCValues->save();

			$COCValuesThatApply->tipodeanticipo = $request->tipodeanticipo;
			$COCValuesThatApply->modelodecalculodelfinanciamiento = $request->modelodecalculodelfinanciamiento;
			$COCValuesThatApply->interesesaconsiderarenelfinanciamiento = $request->interesesaconsiderarenelfinanciamiento;
			$COCValuesThatApply->tasaactiva = $request->tasaactiva;
			$COCValuesThatApply->calculodelcargoadicional = $request->calculodelcargoadicional;
			$COCValuesThatApply->diasaconsiderarenelaño = $request->diasaconsiderarenelaño;
			$COCValuesThatApply->save();

			$COCRequiredValues->anticipoaproveedoresaliniciodeobra = $request->anticipoaproveedoresaliniciodeobra;
			$COCRequiredValues->porcentajedeimpuestosobrenomina = $request->porcentajedeimpuestosobrenomina;
			$COCRequiredValues->presentaciondespuesdelcorte = $request->presentaciondespuesdelcorte;
			$COCRequiredValues->revisionyautorizacion = $request->revisionyautorizacion;
			$COCRequiredValues->diasparaelpago = $request->diasparaelpago;
			//$COCRequiredValues->totaldedias = $request->totaldedias; calculated
			$COCRequiredValues->periododecobroprimeraestimacion = $request->periododecobroprimeraestimacion;
			$COCRequiredValues->periododeentregasegundoanticipo = $request->periododeentregasegundoanticipo;
			$COCRequiredValues->redondeoparaprogramadepersonaltecnico = $request->redondeoparaprogramadepersonaltecnico;
			$COCRequiredValues->presentaciondelprogramadepersonaltecnico = $request->presentaciondelprogramadepersonaltecnico;
			$COCRequiredValues->horasjornada = $request->horasjornada;
			$COCRequiredValues->save();

			$COCAdvanceTypeTable->costodirectodelaobra = $request->costodirectodelaobra;
			$COCAdvanceTypeTable->indirectodeobra = $request->indirectodeobra;
			$COCAdvanceTypeTable->costodirectoindirecto = $request->costodirectoindirecto;
			$COCAdvanceTypeTable->montototaldelaobra = $request->montototaldelaobra;
			$COCAdvanceTypeTable->importeparafinanciamiento = $request->importeparafinanciamiento;
			$COCAdvanceTypeTable->importeejercer1 = $request->importeejercer1;
			$COCAdvanceTypeTable->importeejercer2 = $request->importeejercer2;
			$COCAdvanceTypeTable->save();

			$COCSecondAdvanceTypeTable->periodosprogramados = $request->periodosprogramados;
			$COCSecondAdvanceTypeTable->periodofinaldecobro = $request->periodofinaldecobro;
			$COCSecondAdvanceTypeTable->periododeamortizacion2doanticipo = $request->periododeamortizacion2doanticipo;
			$COCSecondAdvanceTypeTable->save();

			foreach ($request->unAnticipoNumero as $key => $value) {
				App\COCAnAdvance::where('id',$key)->update([
					'numero' => $request->unAnticipoNumero[$key],
					'anticipos' => $request->unAnticipoAnticipo[$key],
					'porcentaje' => $request->unAnticipoPorcentaje[$key],
				]);
			}
			foreach ($request->dosAnticipoNumero as $key => $value) {
				App\COConstructionTwoAdvance::where('id',$key)->update([
					'numero' => $request->dosAnticipoNumero[$key],
					'anticipos' => $request->dosAnticipoAnticipo[$key],
					'porcentaje' => $request->dosAnticipoPorcentaje[$key],
					'periododeentrega' => $request->dosAnticipoPeriodoEntrega[$key],
				]);
			}
			foreach ($request->masAnticipoNumero as $key => $value) {
				App\COConstructionBudgetExceed::where('id',$key)->update([
					'numero' => $request->masAnticipoNumero[$key],
					'anticipos' => $request->masAnticipoAnticipo[$key],
					'porcentaje' => $request->masAnticipoPorcentaje[$key],
					'importeaejercer' => $request->masAnticipoImporteEjercer[$key],
					'importedeanticipo' => $request->masAnticipoAnticipo[$key],
					'periododeentrega' => $request->masAnticipoPeriodoEntrega[$key],
				]);
			}
			foreach ($request->DObraAMIPAnticipo1 as $key => $value) {
				App\COConstructionAMIP::where('id',$key)->update([
					'anticipo1' => $request->DObraAMIPAnticipo1[$key],
					'anticipo2' => $request->DObraAMIPAnticipo2[$key],
					'monto1' => $request->DObraAMIPMonto1[$key],
					'monto2' => $request->DObraAMIPMonto2[$key],
					'importe1' => $request->DObraAMIPImporte1[$key],
					'importe2' => $request->DObraAMIPImporte2[$key],
					'periodo' => $request->DObraAMIPPeriodo[$key],
				]);
			}

		

			$alert = "swal('', 'Datos de Obra Guardados Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 3,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 3,
					]);
		}
	}

	public function SobrecostoProgramaSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{
			foreach ($request->PeriodOprogramado as $key => $value) {
				App\COPeriodProgram::where('id',$key)->update([
					'programado' => $request->PeriodOprogramado[$key],
					'titulo' => $request->PeriodOtitulo[$key],
					'diasnaturales' => $request->PeriodOdiasnaturales[$key],
					'diastotales' => $request->PeriodOdiastotales[$key],
					'factorano' => $request->PeriodOfactorano[$key],
					'ano' => $request->PeriodOano[$key],
					'importedelperiodo' => $request->PeriodOimportedelperiodo[$key],
				]);
			}
			
			foreach ($request->CostoPeriodOcostomateriales as $key => $value) {
				App\COCostPeriodProgram::where('id',$key)->update([
					'costomateriales' => $request->CostoPeriodOcostomateriales[$key],
					'costomanodeobra' => $request->CostoPeriodOcostomanodeobra[$key],
					'costoequipo' => $request->CostoPeriodOcostoequipo[$key],
					'costootrosinsumos' => $request->CostoPeriodOcostootrosinsumos[$key],
				]);
			}
			foreach ($request->AvancEparcial as $key => $value) {
				App\COAdvanceProgram::where('id',$key)->update([
					'parcial' => $request->AvancEparcial[$key],
					'acumulado' => $request->AvancEacumulado[$key],
				]);
			}


			$alert = "swal('', 'Programa Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 4,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        => $data['father'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'child_id'  => $this->obra_child_id,
						'option_id' => 227,
						'budget_id' => $budget_id,
						'stepp'     => 4,
					]);
		}

		

	}
	public function SobrecostoPlantillaSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			App\COGeneralTemplate::where('idUpload',$budget_id)->update([
				'factor1' => $request->factor1G,
				'factor2' => $request->factor2G,
				'porcentaje' => $request->porcentaje,
			]);
			App\SobrecostoPlantillaPOCampoGeneral::where('idUpload',$budget_id)->update([
				'montototal' => $request->GeneralCMontoTotal,
				'porcentaje' => $request->GeneralCPorcentaje,
			]);

			foreach ($request->category as $key => $value) {
				App\SobrecostoPlantillaPOCampo::where('id',$key)->update([
					'group' =>  array_key_exists($key,$request->group) ? $request->group[$key] : null,
					'category' => $request->category[$key],
					'amount' => $request->amount[$key],
					'salary' => $request->salary[$key],
					'import' => $request->import[$key],
					'factor1' => $request->factor1[$key],
					'factor2' => $request->factor2[$key],
				]);
			}
			App\COCentralStaffGeneralTemplate::where('idUpload',$budget_id)->update([
				'montototal' => $request->GeneralCentralMontoTotal,
				'porcentaje' => $request->GeneralCentralPorcentaje,
			]);

			foreach ($request->CentraLcategory as $key => $value) {
				App\COCentralStaffTemplate::where('id',$key)->update([
					'group' =>  array_key_exists($key,$request->CentraLgroup) ? $request->CentraLgroup[$key] : null,
					'category' => $request->CentraLcategory[$key],
					'amount' => $request->CentraLamount[$key],
					'salary' => $request->CentraLsalary[$key],
					'import' => $request->CentraLimport[$key],
					'factor1' => $request->CentraLfactor1[$key],
					'factor2' => $request->CentraLfactor2[$key],
				]);
			}
			foreach ($request->categoryCentral as $key => $value)
			{
				App\COCentralStaffListTemplate::where('id',$key)->update([
					'group' => array_key_exists($key,$request->groupCentral) ? $request->groupCentral[$key] : null,
					'category' => $request->categoryCentral[$key],
				]);
			}
			foreach ($request->categoryC as $key => $value)
			{
				App\COFieldStaffListTemplate::where('id',$key)->update([
					'group' => array_key_exists($key,$request->groupC) ? $request->groupC[$key] : null,
					'category' => $request->categoryC[$key],
				]);
			}
			$alert = "swal('', 'Plantilla Guardada Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 5,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 5,
					]);
		}

		

	}

	public function SobrecostoIndirectosDesglosadosSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{



			App\COIndirectItemizedGeneral::where('idUpload',$budget_id)->update([
				'montoobra' => $request->montoobra,
				'totales' => $request->totales,
				'indirecto' => $request->indirecto,
			]);

			foreach ($request->concepto as $key => $value) {
				App\COIndirectItemizedConcept::where('id',$key)->update([
					'concepto' => $request->concepto[$key],
					'monto1' => $request->monto1[$key],
					'porcentaje1' => $request->porcentaje1[$key],
					'monto2' => $request->monto2[$key],
					'porcentaje2' => $request->porcentaje2[$key],
				]);
			}


			$alert = "swal('', 'Indirectos Desglosados Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 6,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 6,
					]);
		}

		

	}
	public function SobrecostoResumenIndirectosSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{


			App\COSummaryGeneralIndirect::where('idUpload',$budget_id)->update([
				'montoobra' => $request->montoobra,
				'totales' => $request->totales,
				'indirecto' => $request->indirecto,
			]);

			foreach ($request->concepto as $key => $value) {
				App\COSummaryIndirectConcept::where('id',$key)->update([
					'concepto' => $request->concepto[$key],
					'monto1' => $request->monto1[$key],
					'porcentaje1' => $request->porcentaje1[$key],
					'monto2' => $request->monto2[$key],
					'porcentaje2' => $request->porcentaje2[$key],
					'montototal' => $request->montototal[$key],
					'porcentajetotal' => $request->porcentajetotal[$key],
				]);
			}


			$alert = "swal('', 'Resumen Indirectos Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 7,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 7,
					]);
		}

		

	}

	public function SobrecostoPersTecnicoSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->category as $key => $value) {
				App\COTechnicalStaffConcept::where('id',$key)->update([
					'category' => $request->category[$key],
					'measurement' => array_key_exists($key,$request->measurement) ? $request->measurement[$key] : null,
					'total' => array_key_exists($key,$request->total) ? $request->total[$key] : null,
				]);
			}
			foreach ($request->amount as $key => $value) {
				App\COTechnicalStaffYear::where('id',$key)->update([
					'amount' => $request->amount[$key],
				]);
			}


			$alert = "swal('', 'Pers. Técnico Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 8,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 8,
					]);
		}

		

	}
	public function SobrecostoPersTecnicoSalarioSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->category as $key => $value) {
				App\COTechnicalStaffSalaryConcept::where('id',$key)->update([
					'category' => $request->category[$key],
					'measurement' => array_key_exists($key,$request->measurement) ? $request->measurement[$key] : null,
					'import' => array_key_exists($key,$request->import) ? $request->import[$key] : null,
					'amount' => array_key_exists($key,$request->amount) ? $request->amount[$key] : null,
					'salary' => array_key_exists($key,$request->salary) ? $request->salary[$key] : null,
				]);
			}
			foreach ($request->amount as $key => $value) {
				App\COTechnicalStaffYearSalary::where('id',$key)->update([
					'amount' => $request->amount[$key],

				]);
			}


			$alert = "swal('', 'Pers. Técnico Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 9,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 9,
					]);
		}

		

	}

	
	public function SobrecostofinanCHorizontalSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			App\COGeneralFinancial::where('idUpload',$budget_id)->update([
				'indicadoreconomicodereferencia' => $request->indicadoreconomicodereferencia,
				'puntosdeintermediaciondelabanca' => $request->puntosdeintermediaciondelabanca,
				'tasadeinteresdiaria' => $request->tasadeinteresdiaria,
				'diasparapagodeestimaciones' => $request->diasparapagodeestimaciones,
				'aplicablealperiodo' => $request->aplicablealperiodo,
				'porcentajedefinancieamiento' => $request->porcentajedefinancieamiento,
			]);

			foreach ($request->concept as $key => $value) {
				App\COFinancialConcept::where('id',$key)->update([
					'concept' => $request->concept[$key],
				]);
			}
			foreach ($request->amount as $key => $value) {
				App\COFinancialMonth::where('id',$key)->update([
					'amount' => $request->amount[$key],
				]);
			}


			$alert = "swal('', 'Pers. Técnico Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 10,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 10,
					]);
		}
	}
	public function SobrecostoUtilidadSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->clave as $key => $value) {
				App\CODeterminationUtility::where('id',$key)->update([
					'clave' => $request->clave[$key],
					'concepto' => $request->concepto[$key],
					'formula' => $request->formula[$key],
					'importe' => $request->importe[$key],
					'porcentaje' => $request->porcentaje[$key],
				]);
			}


			$alert = "swal('', 'Utilidad Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 11,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 11,
					]);
		}
	}
	public function SobrecostoCargosAdicionalesSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->clave as $key => $value) {
				App\COAdditionalCharges::where('id',$key)->update([
					'clave' => $request->clave[$key],
					'concepto' => $request->concepto[$key],
					'formula' => $request->formula[$key],
					'importe' => $request->importe[$key],
					'porcentaje' => $request->porcentaje[$key],
				]);
			}


			$alert = "swal('', 'Cargos Adicionales Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 12,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 12,
					]);
		}
	}

	public function SobrecostoResumenSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->clave as $key => $value) {
				App\COSummaryConcept::where('id',$key)->update([
					'clave' => $request->clave[$key],
					'concepto' => $request->concepto[$key],
					'importe' => $request->importe[$key],
					'porcentaje' => $request->porcentaje[$key],
				]);
			}


			$alert = "swal('', 'Resumen Guardado Exitosamente', 'success');";
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 13,
					])->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 13,
					]);
		}
	}

	public function SobrecostoDocumentacionSave($budget_id,Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if($request->has('save'))
		{

			foreach ($request->EmpresaName as $key => $value) {

				App\COEnterpriseDocument::where('id',$key)->update([
					'name' => $value,
				]);
				App\COAdvanceDocumentation::where('idDocEmpresa',$key)->update([
					'unanticipo' => $request->unanticipo ? ( array_key_exists($key,$request->unanticipo) ? 1 : 0) : 0,
					'dosanticipo' => $request->dosanticipo ? ( array_key_exists($key,$request->dosanticipo) ? 1 : 0) : 0,
					'rebasa' => $request->rebasa ? ( array_key_exists($key,$request->rebasa) ? 1 : 0) : 0,
				]);
				
				App\COFinancingCalcDocument::where('idDocEmpresa',$key)->update([
					'importetotal' => $request->importetotal ? ( array_key_exists($key,$request->importetotal) ? 1 : 0) : 0,
					'costodirectoindirecto' => $request->costodirectoindirecto ? ( array_key_exists($key,$request->costodirectoindirecto) ? 1 : 0) : 0,
				]);
				
				App\COInterestsToConsiderDocument::where('idDocEmpresa',$key)->update([
					'negativos' => $request->negativos ? ( array_key_exists($key,$request->negativos) ? 1 : 0) : 0,
					'ambos' => $request->ambos ? ( array_key_exists($key,$request->ambos) ? 1 : 0) : 0,
					'tasaactiva' => $request->tasaactiva ? ( array_key_exists($key,$request->tasaactiva) ? 1 : 0) : 0,
					'tasapasiva' => $request->tasapasiva ? ( array_key_exists($key,$request->tasapasiva) ? 1 : 0) : 0,
				]);
				
				App\COAdditionalChargeCalcDocument::where('idDocEmpresa',$key)->update([
					'sobreelimporte' => $request->sobreelimporte ? ( array_key_exists($key,$request->sobreelimporte) ? 1 : 0) : 0,
					'costodirecto' => $request->costodirecto ? ( array_key_exists($key,$request->costodirecto) ? 1 : 0) : 0,
				]);
				
				App\CODaysToConsiderDocument::where('idDocEmpresa',$key)->update([
					'anofiscal' => $request->anofiscal ? ( array_key_exists($key,$request->anofiscal) ? 1 : 0) : 0,
					'anocomercial' => $request->anocomercial ? ( array_key_exists($key,$request->anocomercial) ? 1 : 0) : 0,
				]);
				
				App\COThousandDocument::where('idDocEmpresa',$key)->update([
					'casub' => $request->casub ? ( array_key_exists($key,$request->casub) ? 1 : 0) : 0,
					'caca' => $request->caca ? ( array_key_exists($key,$request->caca) ? 1 : 0) : 0,
				]);
				
			}

			foreach ($request->dias as $key => $value) {
				App\CODaysToPayDocument::where('id',$key)->update([
					'dias' => $value,
				]);
			}
			App\Sobrecostos::where('id',$budget_id)->update([
				'status' => 1,
			]);
			$alert = "swal('', 'Sobrecosto Enviado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return view('administracion.presupuestos.editar_sobrecosto',
					[
						'id'        	=> 	$data['father'],
						'title'     	=> 	$data['name'],
						'details'   	=> 	$data['details'],
						'child_id'		=> 	$this->obra_child_id,
						'option_id' 	=> 	227,
						'budget_id' 	=> $budget_id,
						'stepp' 			=> 13,
					]);
		}
	}

	
	public function SobrecostoPaginateSearch(Request $request)
	{
		
			$project_id = $request->project_id;
			$name 			= $request->name;
			$start 			= $request->startObra;
			$end 				= $request->endObra;

			if ($start != null)
			{
				$date1 		= strtotime($start);
				$mindate 	= date('Y-m-d',$date1);
				$start 		= $mindate;
			}
			if($end != null)
			{
				$date2 		= strtotime($end);
				$maxdate 	= date('Y-m-d',$date2);
				
				$end 			= $maxdate;
			}

			$BudgetUploads = App\CostOverruns::where(function($q) use ($project_id,$name,$start,$end){
				
				if($project_id)
					$q->where('idproyect',$project_id);

				if($name)
					$q->where('name','like',"%$name%");

					$q->whereHas('generalesObra',function($q) use($start,$end){
						if($start && $end == null)
							$q->whereDate('fechainicio','>=',''.$start.' '.date('00:00:00').'');
						
							if($end && $start == null)
							$q->whereDate('fechaterminacion','<=',''.$end.' '.date('23:59:59').'');
						
						if($start && $end)
							$q->whereDate('fechainicio','>=',''.$start.' '.date('00:00:00').'')
								->whereDate('fechaterminacion','<=',''.$end.' '.date('23:59:59').'');
				});

			})->paginate(10);

			$html 		=	'';
			$body		=	[];
			$modelBody	=	[];
			$modelHead	=
			[
				[
					["value"	=>	"#"],
					["value"	=>	"Título"],
					["value"	=>	"Proyecto"],
					["value"	=>	"Inicio de obra"],
					["value"	=>	"Fin de obra"],
					["value"	=>	"Estado"],
					["value"	=>	"Acción"],
				]
			];
			foreach($BudgetUploads as $key => $bgup)
			{
				$startObra		=	\Carbon\Carbon::parse($bgup->startObra)->format('d-m-Y');
				$endObra		=	\Carbon\Carbon::parse($bgup->endObra)->format('d-m-Y');
				$proyect_name	=	$bgup->proyect->proyectName;
				$body	=
				[
					[
						"content"	=>	["label"	=>	$bgup->id],
					],
					[
						"content"	=>	["label"	=>	$bgup->name],
					],
					[
						"content"	=>	["label"	=>	$proyect_name],
					],
					[
						"content"	=>	["label"	=>	$bgup->startObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->endObra],
					],
					[
						"content"	=>	["label"	=>	$bgup->status],
					],
					[
						"content"	=>
						[
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"secondary",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('Sobrecosto.create.validate',$bgup->id)."\"",
								"label"			=>	"<span class='icon-pencil'></span>",
								"classEx"		=>	"edit-item"
							],
							[
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"success",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('Sobrecosto.excel',$bgup->id)."\"",
								"label"			=>	"<span class='icon-file-excel'></span>",
								"classEx"		=>	"export"
							],
							[
								
								"kind"			=>	"components.buttons.button",
								"variant"		=>	"red",
								"buttonElement"	=>	"a",
								"attributeEx"	=>	"href=\"".route('Sobrecosto.delete', $bgup->id)."\"",
								"label"			=>	"<span class='icon-x '></span>",
								"classEx"		=>	"export"
							]
						]
					],
				];
				$modelBody[]	=	$body;
			}
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.tables.table", [
				'classEx'		=>	'text-center',
				"modelBody"		=>	$modelBody,
				"modelHead"		=>	$modelHead
			])));
		
		/* $table_body =
		'	<div class="table-responsive">
			<table id="table" class="table table-striped">
				<thead class="thead-dark">
					<tr>
						<th width="5%">#</th>
						<th width="30%">Título</th>
						<th width="30%">Proyecto</th>
						<th width="10%">Inicio de obra</th>
						<th width="10%">Fin de obra</th>
						<th width="10%">Estado</th>
						<th width="15%">Acción</th>
					</tr> 
				</thead>
			<tbody>';

		foreach ($BudgetUploads as $key => $bgup) {

			$startObra = $bgup->generalesObra ? \Carbon\Carbon::parse($bgup->generalesObra->fechainicio)->format('d-m-Y') : '';
			$endObra = $bgup->generalesObra ? \Carbon\Carbon::parse($bgup->generalesObra->fechaterminacion)->format('d-m-Y') : '';
			$proyect_name = $bgup->proyect->proyectName;

			$table_body .= 
			"<tr>
			<td>$bgup->id</td>
			<td>$bgup->name</td>
			<td>$proyect_name</td>
			<td>$startObra</td>
			<td>$endObra</td>
			<td>$bgup->status</td>
			<td>
				<a href='".route('Sobrecosto.create.validate',$bgup->id)."' class='btn btn-blue edit-item' type='button'><span class='icon-pencil'></span></a>
				<form method='get' action='".route('Sobrecosto.excel',$bgup->id)."' accept-charset='UTF-8'>
				<button class='btn btn-green export' type='submit'  ><span class='icon-file-excel'></span></button>
				</form>
				<form method='post' action='".route('Sobrecosto.delete') ."'>

				<input type='text' name='_token' hidden value='".csrf_token()."'>
				<input type='text' name='BudgetID' hidden value='$bgup->id'>
				<button class='btn btn-red export' type='submit'  ><span class='icon-x '></span></button>
				</form>
			</td>
		</tr>";
		}
		$table_body .=
		'</tbody>
		</table>
		</div>'; */

		if($BudgetUploads->count() == 0)
			// $table_body = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
			$html .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.not-found")));
		
		return \Response::JSON(array(
			'data'       => $BudgetUploads, 
			'table'       => $html, 
			'pagination' => (string) $BudgetUploads->links()
		));
		
	}

	
	public function SobrecostoDelete(Request $request)
	{

		if(Auth::user()->module->where('id',$this->obra_child_id)->count()>0)
		{
			$bgup = App\CostOverruns::where('id',$request->BudgetID)->first();
			
			\Storage::disk('public')->delete($bgup->file);

			App\CostOverruns::where('id',$request->BudgetID)->delete();
			

			$alert = "swal('', 'Sobrecosto Eliminado Exitosamente', 'success');";
			return redirect('/administration/budgets/obra')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function SobrecostoExcel(Request $request)
	{
		$BudgetUploads = App\CostOverruns::where('id',$request->id)->first();
		Excel::create('Sobrecostos', function($excel) use ($BudgetUploads)
		{
			$excel->sheet('N_Campos Generales',function($sheet) use ($BudgetUploads)
			{

				$sheet->setAllBorders('none');
				$sheet->setStyle(array(
					'fill' => array(
							'type'      =>  'solid',
							'color' => array('rgb' => 'ffffff'),
					)
				));



				$sheet->row(1,['']);
				$sheet->row(2,['DATOS GENERALES PARA IMPRESIÓN DE LOS REPORTES']);
				$sheet->mergeCells('A2:C2');
				$sheet->cell('A2', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});
				$sheet->row(3,['']);
				$sheet->row(4,['NOMBRE DE CELDA','DESCRIPCION','VALOR']);
				$sheet->cell('A4:C4', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#FFFF02');
				});

				$sheet->row(5,['DATOS DE LA EMPRESA']);

				$bgup = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'razonsocial';
					$row[] = 'Nombre de la empresa.';
					$row[] = $bgup->razonsocial;
					$sheet->appendRow($row);
					
					$row = [];
					$row[] = 'domicilio';
					$row[] = 'Domicilio de la empresa.';
					$row[] = $bgup->domicilio;
					$sheet->appendRow($row);
					
					
					$row = [];
					$row[] = 'colonia';
					$row[] = 'Colonia de la empresa';
					$row[] = $bgup->colonia;
					$sheet->appendRow($row);
					
					
					$row = [];
					$row[] = 'ciudad';
					$row[] = 'Ciudad donde se localiza la empresa.';
					$row[] = $bgup->ciudad;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'estado';
					$row[] = 'Entidad federativa o provincia donde se localiza la empresa';
					$row[] = $bgup->estado;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'rfc';
					$row[] = 'RFC de la empresa.';
					$row[] = $bgup->rfc;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'telefono';
					$row[] = 'Telefono(s) de la empresa.';
					$row[] = $bgup->telefono;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'email';
					$row[] = 'Correo electrónico de la empresa';
					$row[] = $bgup->email;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'cmic';
					$row[] = 'Registro CMIC de la empresa.';
					$row[] = $bgup->cmic;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'infonavit';
					$row[] = 'Registro INFONAVIT de la empresa.';
					$row[] = $bgup->infonavit;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'imss';
					$row[] = 'Registro IMSS de la empresa.';
					$row[] = $bgup->imss;
					$sheet->appendRow($row);


					$row = [];
					$row[] = 'responsable';
					$row[] = 'Nombre del responsable de la empresa (para firmas).';
					$row[] = $bgup->responsable;
					$sheet->appendRow($row);
					
					$row = [];
					$row[] = 'cargo';
					$row[] = 'Cargo del responsable (para firmas).';
					$row[] = $bgup->cargo;
					$sheet->appendRow($row);

				$bgup = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'DATOS DEL CLIENTE';
					$sheet->appendRow($row);

					$row = [];
					$row[] = 'nombrecliente';
					$row[] = 'Nombre del cliente.';
					$row[] = $bgup->nombrecliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'area';
					$row[] = 'Area del cliente que convoca.';
					$row[] = $bgup->area;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'departamento';
					$row[] = 'Departamento del cliente que licita.';
					$row[] = $bgup->departamento;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'direccioncliente';
					$row[] = 'Dirección del cliente.';
					$row[] = $bgup->direccioncliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'coloniacliente';
					$row[] = 'Colonia del cliente.';
					$row[] = $bgup->coloniacliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'codigopostalcliente';
					$row[] = 'Código postal del cliente.';
					$row[] = $bgup->codigopostalcliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'ciudadcliente';
					$row[] = 'Ciudad del cliente.';
					$row[] = $bgup->ciudadcliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'telefonocliente';
					$row[] = 'Teléfono del cliente.';
					$row[] = $bgup->telefonocliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'emailcliente';
					$row[] = 'e-Mail del cliente.';
					$row[] = $bgup->emailcliente;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'contactocliente';
					$row[] = 'Nombre del contacto con el cliente.';
					$row[] = $bgup->contactocliente;
				$sheet->appendRow($row);

				$bgup = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'DATOS DEL CONCURSO';
					$sheet->appendRow($row);

					
					$row = [];
					$row[] = 'fechadeconcurso';
					$row[] = 'Fecha del concurso.';
					$row[] = \Carbon\Carbon::parse($bgup->fechadeconcurso)->format('d/m/Y');
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);

					$row = [];
					$row[] = 'numerodeconcurso';
					$row[] = 'Número del concurso.';
					$row[] = $bgup->numerodeconcurso;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'direcciondeconcurso';
					$row[] = 'Ubicación del concurso (dirección).';
					$row[] = $bgup->direcciondeconcurso;
				$sheet->appendRow($row);

				$bgup = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'DATOS DE LA OBRA';
					$sheet->appendRow($row);

					
					$row = [];
					$row[] = 'nombredelaobra';
					$row[] = 'Nombre de la obra.';
					$row[] = $bgup->nombredelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'direcciondelaobra';
					$row[] = 'Dirección de la obra.';
					$row[] = $bgup->direcciondelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'coloniadelaobra';
					$row[] = 'Colonia de la obra.';
					$row[] = $bgup->coloniadelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'ciudaddelaobra';
					$row[] = 'Ciudad donde se localiza la obra.';
					$row[] = $bgup->ciudaddelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'estadodelaobra';
					$row[] = 'Estado o provincia donde se localiza la obra.';
					$row[] = $bgup->estadodelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'codigopostaldelaobra';
					$row[] = 'Código postal de la obra.';
					$row[] = $bgup->codigopostaldelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'telefonodelaobra';
					$row[] = 'Teléfono de la obra.';
					$row[] = $bgup->telefonodelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'emaildelaobra';
					$row[] = 'e-Mail de la obra.';
					$row[] = $bgup->emaildelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'responsabledelaobra';
					$row[] = 'Responsable de la obra.';
					$row[] = $bgup->responsabledelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'cargoresponsabledelaobra';
					$row[] = 'Cargo del responsable de la obra.';
					$row[] = $bgup->cargoresponsabledelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'fechainicio';
					$row[] = 'Fecha de inicio de la obra (con 1 en programa de obra).';
					$row[] = \Carbon\Carbon::parse($bgup->fechainicio)->format('d/m/Y');
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);
					
					$row = [];
					$row[] = 'fechaterminacion';
					$row[] = 'Fecha de terminación de la obra (con 1 en programa de obra).';
					$row[] = \Carbon\Carbon::parse($bgup->fechaterminacion)->format('d/m/Y');
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);

					$row = [];
					$row[] = 'totalpresupuestoprimeramoneda';
					$row[] = 'Total del presupuesto primera moneda.';
					$row[] = $bgup->totalpresupuestoprimeramoneda;
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
					$row = [];
					$row[] = 'totalpresupuestosegundamoneda';
					$row[] = 'Total del presupuesto segunda moneda.';
					$row[] = $bgup->totalpresupuestosegundamoneda;
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
					$row = [];
					$row[] = 'porcentajeivapresupuesto';
					$row[] = 'Porcentaje iva presupuesto.';
					$row[] = $bgup->porcentajeivapresupuesto/100;
				$sheet->appendRow($row);
				$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$bgup = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'DATOS ENCABEZADO';
					$sheet->appendRow($row);

					$row = [];
					$row[] = 'plazocalculado';
					$row[] = 'Duración de la obra en dias naturales.';
					$row[] = $bgup->plazocalculado;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'plazoreal';
					$row[] = 'Duración de la obra en dias habiles.';
					$row[] = $bgup->plazoreal;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'decimalesredondeo';
					$row[] = 'Decimales para redondeo de importes.';
					$row[] = $bgup->decimalesredondeo;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'primeramoneda';
					$row[] = 'Descripción de la moneda 1 en que se muestra el reporte.';
					$row[] = $bgup->primeramoneda;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'segundamoneda';
					$row[] = 'Descripción de la moneda 2 en que se muestra el reporte.';
					$row[] = $bgup->segundamoneda;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'remateprimeramoneda';
					$row[] = 'Remate de la moneda 1';
					$row[] = $bgup->remateprimeramoneda;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'rematesegundamoneda';
					$row[] = 'Remate de la moneda 2';
					$row[] = $bgup->rematesegundamoneda;
				$sheet->appendRow($row);
				
				$bgup = App\CostOverrunsNCGAnnouncement::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = 'DATOS DE LA CONVOCATORIA';
					$sheet->appendRow($row);

					$row = [];
					$row[] = 'numconvocatoria';
					$row[] = 'Numero de la convocatoria del concurso.';
					$row[] = $bgup->numconvocatoria;
					$sheet->appendRow($row);
					$row = [];
					$row[] = 'fechaconvocatoria';
					$row[] = 'Fecha de la convocatoria.';
					$row[] = \Carbon\Carbon::parse($bgup->fechaconvocatoria)->format('d/m/Y');
					$sheet->appendRow($row);
					$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);
					$row = [];
					$row[] = 'tipodelicitacion';
					$row[] = 'Tipo de licitacion';
					$row[] = $bgup->tipodelicitacion;
				$sheet->appendRow($row);

				$sheet->getStyle('A4:C61')->applyFromArray($this->ALL_BORDER);

				$sheet->cell('A5:C5', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A19:C19', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A30:C30', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A34:C34', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A50:C50', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A58:C58', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#76933B');
				});
				$sheet->cell('A6:C18', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('A20:C29', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('A31:C33', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('A35:C49', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('A51:C57', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('A59:C61', function($cells)
				{
					$cells->setBackground('#CCFFCC');
				});
				$sheet->cell('C4:C61', function($cells)
				{
					$cells->setFontWeight('bold');
				});

				$sheet->setWidth('A', 29);
				$sheet->setWidth('B', 74);
				$sheet->setWidth('C', 50);

				$sheet->getStyle('C6:C61')->getAlignment()->setWrapText(true);

			});

			$excel->sheet('DatosObra',function($sheet) use ($BudgetUploads)
			{
				$bgup = App\COCValues::where('idUpload',$BudgetUploads->id)->first();
				
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = 'VALORES DE LA OBRA				';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'COSTO DIRECTO DE LA OBRA :';
					$row[] = $bgup->costodirectodelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'IMPORTE TOTAL DE LA MANO DE OBRA GRAVABLE :';
					$row[] = $bgup->importetotaldelamanodeobragravable;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'IMPORTE TOTAL DE LA OBRA:';
					$row[] = $bgup->importetotaldelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'FACTOR PARA LA OBTENCIÓN DE LA SFP:';
					$row[] = $bgup->factorparalaobtenciondelasfp;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PORCENTAJE DE UTILIDAD BRUTA PROPUESTA:';
					$row[] = $bgup->porcentajedeutilidadbrutapropuesta;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'TASA DE INTERÉS USADA:';
					$row[] = $bgup->tasadeinteresusada;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PUNTOS DEL BANCO:';
					$row[] = $bgup->puntosdelbanco;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'INDICADOR ECONÓMICO DE REFERENCIA:';
					$row[] = $bgup->indicadoreconomicodereferencia;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'ISR (Impuesto Sobre la Renta):';
					$row[] = $bgup->isr;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PTU (Participacion de trabajadores en la utilidad):';
					$row[] = $bgup->ptu;
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COCValuesThatApply::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = '';
					$row[] = 'ELIJA LOS VALORES QUE APLICAN  ';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'TIPO DE ANTICIPO';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '1';
					$row[] = $bgup->tipodeanticipo == 1 ? 'Un ejercicio con un Anticipo' : ($bgup->tipodeanticipo == 2 ? 'Un ejercicio con 2 anticipos' : 'Rebasa un Ejercicio presupuestal');
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Un ejercicio con un Anticipo';
					$row[] = $bgup->tipodeanticipo;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Un ejercicio con 2 anticipos';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Rebasa un Ejercicio presupuestal';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'MODELO DE CALCULO DEL FINANCIAMIENTO';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '2';
					$row[] = $bgup->modelodecalculodelfinanciamiento == 1 ? 'Importe Total de Obra' : 'Costo Directo+Indirecto';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Importe Total de Obra';
					$row[] = $bgup->modelodecalculodelfinanciamiento;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Costo Directo+Indirecto';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'INTERESES A CONSIDERAR EN EL FINANCIAMIENTO';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '3';
					$row[] = $bgup->interesesaconsiderarenelfinanciamiento == 1 ? 'Solo intereses negativos' : 'Ambos Interes (+ y -)';
					$row[] = $bgup->tasaactiva == 1 ? 'Tasa Activa = 6.6438 %' : ($bgup->tasaactiva == 2 ? 'Tasa Pasiva = 6.6438 %' : '');
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'Solo intereses negativos';
					$row[] = $bgup->interesesaconsiderarenelfinanciamiento;
					$row[] = 'Tasa Activa = 6.6438 %';
					$row[] = $bgup->tasaactiva;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'Ambos Interes (+ y -)';
					$row[] = '';
					$row[] = 'Tasa Pasiva = 6.6438 %';
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'CALCULO DEL CARGO ADICIONAL';
					$row[] = '';
					$row[] = 'DIAS A CONSIDERAR EN EL AÑO';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '4';
					$row[] = $bgup->calculodelcargoadicional == 1 ? 'Sobre el Importe de Estimaciones' : 'Sobre el Costo directo de la Obra';
					$row[] = '';
					$row[] = $bgup->diasaconsiderarenelaño == 1 ? 'Año Fiscal (1 Ene al 31 Dic)' : 'Año Comercial (360 Dias)';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'Sobre el Importe de Estimaciones';
					$row[] = $bgup->calculodelcargoadicional;
					$row[] = 'Año Fiscal (1 Ene al 31 Dic)';
					$row[] = $bgup->diasaconsiderarenelaño;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'Sobre el Costo directo de la Obra';
					$row[] = '';
					$row[] = 'Año Comercial (360 Dias)';
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COCRequiredValues::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = '';
					$row[] = 'ESCRIBA LOS VALORES REQUERIDOS				';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'ANTICIPO A PROVEEDORES AL INICIO DE OBRA:';
					$row[] = $bgup->anticipoaproveedoresaliniciodeobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PORCENTAJE DE IMPUESTO SOBRE NÓMINA:';
					$row[] = $bgup->porcentajedeimpuestosobrenomina/100;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Presentación despues del corte:';
					$row[] = $bgup->presentaciondespuesdelcorte;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Revisión y Autorización:';
					$row[] = $bgup->revisionyautorizacion;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Dias para el pago:';
					$row[] = $bgup->diasparaelpago;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Total de Dias:';
					$row[] = $bgup->totaldedias;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PERIODO DE COBRO PRIMERA ESTIMACION:';
					$row[] = $bgup->periododecobroprimeraestimacion;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'PERIODO DE ENTREGA SEGUNDO ANTICIPO:';
					$row[] = $bgup->periododeentregasegundoanticipo;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Redondeo para Programa de Personal Tecnico:';
					$row[] = $bgup->redondeoparaprogramadepersonaltecnico;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'Presentacion del Programa de Personal Técnico:';
					$row[] = $bgup->presentaciondelprogramadepersonaltecnico == 1 ? 'No. de Personas' : ($bgup->presentaciondelprogramadepersonaltecnico == 2 ? 'No. de Jornales' : 'Horas Hombre');
					
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'No. de Personas';
					$row[] = $bgup->presentaciondelprogramadepersonaltecnico;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'No. de Jornales';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'Horas Hombre';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = 'horas Jornada';
					$row[] = $bgup->horasjornada;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COCAnAdvance::where('idUpload',$BudgetUploads->id)->get();

					$row = [];
					$row[] = '';
					$row[] = 'PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = 'NUMERO';
					$row[] = 'ANTICIPOS';
					$row[] = 'PORCENTAJE';
					$sheet->appendRow($row);
					foreach ($bgup as $bg) {
						$row = [];
						$row[] = '';
						$row[] = $bg->numero;
						$row[] = $bg->anticipos;
						$row[] = $bg->porcentaje/100;
						$sheet->appendRow($row);
				}
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COConstructionTwoAdvance::where('idUpload',$BudgetUploads->id)->get();

					$row = [];
					$row[] = '';
					$row[] = 'ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = 'NUMERO';
					$row[] = 'ANTICIPOS';
					$row[] = 'PORCENTAJE';
					$row[] = 'PERIODO DE ENTREGA';
					$sheet->appendRow($row);
					foreach ($bgup as $bg) {
						$row = [];
						$row[] = '';
						$row[] = $bg->numero;
						$row[] = $bg->anticipos;
						$row[] = $bg->porcentaje/100;
						$row[] = $bg->periododeentrega;
						$sheet->appendRow($row);
				}

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COConstructionBudgetExceed::where('idUpload',$BudgetUploads->id)->get();

					$row = [];
					$row[] = '';
					$row[] = 'PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = 'NUMERO';
					$row[] = 'ANTICIPOS';
					$row[] = 'PORCENTAJE';
					$row[] = 'IMPORTE A EJERCER';
					$row[] = 'IMPORTE DE ANTICIPO';
					$row[] = 'PERIODO DE ENTREGA';
					$sheet->appendRow($row);
					foreach ($bgup as $bg) {
						$row = [];
						$row[] = '';
						$row[] = $bg->numero;
						$row[] = $bg->anticipos;
						$row[] = $bg->porcentaje/100;
						$row[] = $bg->importeaejercer;
						$row[] = $bg->importedeanticipo;
						$row[] = $bg->periododeentrega;
						$sheet->appendRow($row);
				}

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COCAdvanceTypeTable::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = 'TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO				';
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'COSTO DIRECTO DE LA OBRA:';
					$row[] = $bgup->costodirectodelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'INDIRECTO DE OBRA:';
					$row[] = $bgup->indirectodeobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'COSTO DIRECTO +INDIRECTO:';
					$row[] = $bgup->costodirectoindirecto;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'MONTO TOTAL DE LA OBRA:';
					$row[] = $bgup->montototaldelaobra;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'IMPORTE PARA FINANCIAMIENTO:';
					$row[] = $bgup->importeparafinanciamiento;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'IMPORTE EJERCER1:';
					$row[] = $bgup->importeejercer1;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'IMPORTE EJERCER2:';
					$row[] = $bgup->importeejercer2;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
				$sheet->appendRow($row);

				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COCSecondAdvanceTypeTable::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = '';
					$row[] = 'TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO				';
					$sheet->appendRow($row);

					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'PERIODOS PROGRAMADOS:';
					$row[] = $bgup->periodosprogramados;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'PERIODO FINAL DE COBRO:';
					$row[] = $bgup->periodofinaldecobro;
					$sheet->appendRow($row);
					$row = [];
					$row[] = '';
					$row[] = '';
					$row[] = 'PERIODO DE AMORTIZACION 2do ANTICIPO:';
					$row[] = $bgup->periododeamortizacion2doanticipo;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$bgup = App\COConstructionAMIP::where('idUpload',$BudgetUploads->id)->get();
				$COCValuesThatApply = App\COCValuesThatApply::where('idUpload',$BudgetUploads->id)->first();

					$row = [];
					$row[] = '';
					$row [] = 'ANTICIPOS';
					$row [] = '';
					$row [] = 'MONTO A EJERCER';
					$row [] = '';
					$row [] = 'IMPORTE DE ANTICIPOS';
					$row [] = '';
					$row [] = 'PERIODO DE ENTREGA';
					$sheet->appendRow($row);
					
					$row = [];
					$row[] = '';
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? 'EJERCICIO' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EXHIBICION1" : "EJERCICIO1" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? '' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EXHIBICION1" : "EJERCICIO2" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? 'EJERCICIO' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EJERCICIO" : "EJERCICIO1" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? '' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EJERCICIO" : "EJERCICIO2" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? 'EJERCICIO' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EXHIBICION1" : "EJERCICIO1" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? '' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EXHIBICION2" : "EJERCICIO2" );
					$row [] = $COCValuesThatApply->tipodeanticipo == 1 ? '' : ( $COCValuesThatApply->tipodeanticipo == 2 ? "EXHIBICION2" : "EJERCICIO2" );
					$sheet->appendRow($row);

				foreach ($bgup as $bg) {
					$row = [];
					$row[] = '';
					$row[] = $bg->anticipo1;
					$row[] = $bg->anticipo2;
					$row[] = $bg->monto1;
					$row[] = $bg->monto2;
					$row[] = $bg->importe1;
					$row[] = $bg->importe2;
					$row[] = $bg->periodo;
					$sheet->appendRow($row);
				}
				

				#start VALORES DE LA OBRA
				$sheet->getStyle('B2:F14')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->mergeCells('B2:F2');
				$sheet->cell('B2', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#FCD5B4');
				});
				$sheet->getStyle('B2')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->cell('D3:D12', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('E3:E12', function($cells)
				{
					$cells->setAlignment('center');
				});
				$sheet->getStyle('E3:E5')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				#end VALORES DE LA OBRA
				
				#start ELIJA LOS VALORES QUE APLICAN 
				$sheet->getStyle('B16:F42')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->mergeCells('B16:F16');
				$sheet->cell('B16', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#B7DEE8');
				});
				
				$sheet->cell('D37', function($cells)
				{
					$cells->setAlignment('center');
				});
				$sheet->cell('F37', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->getStyle('B16')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getRowDimension(21)->setVisible(false);
				$sheet->getRowDimension(22)->setVisible(false);
				$sheet->getRowDimension(23)->setVisible(false);
				$sheet->getRowDimension(24)->setVisible(false);
				
				$sheet->getRowDimension(28)->setVisible(false);
				$sheet->getRowDimension(29)->setVisible(false);
				$sheet->getRowDimension(30)->setVisible(false);
				
				$sheet->getRowDimension(34)->setVisible(false);
				$sheet->getRowDimension(35)->setVisible(false);
				$sheet->getRowDimension(36)->setVisible(false);
				
				$sheet->getRowDimension(40)->setVisible(false);
				$sheet->getRowDimension(41)->setVisible(false);
				#end ELIJA LOS VALORES QUE APLICAN 
				
				#start ESCRIBA LOS VALORES REQUERIDOS
				$sheet->getStyle('B44:F67')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->mergeCells('B44:F44');
				$sheet->cell('B44', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#B3A3C7');
				});
				
				$sheet->cell('D45:D66', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->getStyle('B44')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->getRowDimension(64)->setVisible(false);
				$sheet->getRowDimension(65)->setVisible(false);
				$sheet->getRowDimension(66)->setVisible(false);

				$sheet->getStyle('E46:E46')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('E47:E47')->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				#end ESCRIBA LOS VALORES REQUERIDOS
				#start PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO
				$sheet->getStyle('B71:D72')->applyFromArray($this->ALL_BORDER);
				$sheet->cell('B71:D71', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#DA9896');
				});
				$sheet->cell('B72:D72', function($cells)
				{
					$cells->setBackground('#F3DDDC');
				});

				$sheet->getStyle('D72:D72')->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				#end ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS
				#start PARA OBRAS EN UN EJERCICIO PRESUPUESTAL CON UN ANTICIPO
				$sheet->getStyle('B76:E78')->applyFromArray($this->ALL_BORDER);
				$sheet->cell('B76:E76', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#DA9896');
				});
				$sheet->cell('B77:E78', function($cells)
				{
					$cells->setBackground('#F3DDDC');
				});

				$sheet->getStyle('D77:D78')->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				#end ANTICIPO EN UN EJERCICIO PRESUPUESTAL CON DOS ANTICIPOS
				#start PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL
				$sheet->getStyle('B82:G84')->applyFromArray($this->ALL_BORDER);
				$sheet->cell('B82:G82', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#DA9896');
				});
				$sheet->cell('B83:G84', function($cells)
				{
					$cells->setBackground('#F3DDDC');
				});

				$sheet->getStyle('D83:E84')->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('F83:F84')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);

				#end PARA OBRAS QUE REBASEN UN EJERCICIO PRESUPUESTAL
				#start TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO
				$sheet->getStyle('B87:F96')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->mergeCells('B87:F87');
				$sheet->cell('C89:C95', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('B87', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#C4BD97');
				});
				$sheet->getStyle('B87')->applyFromArray($this->DOUBLE_BORDER);
				
				
				$sheet->getStyle('D89:D95')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);

				#end TABLA DE DATOS DE ACUERDO A LA ELECCION DEL TIPO DE ANTICIPO
				#start TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO
				$sheet->getStyle('B98:F102')->applyFromArray($this->DOUBLE_BORDER);
				
				$sheet->mergeCells('B98:F98');
				$sheet->cell('C99:C101', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('B98', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#C4BD97');
				});
				$sheet->getStyle('B98')->applyFromArray($this->DOUBLE_BORDER);
				
				
				$sheet->getStyle('D99:D101')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);

				#end TABLA DE DATOS DE ACUERDO AL COBRO PRIMERA ESTIMACION Y ENTREGA 2do. ANTICIPO
				#start VALORES DE LA OBRA
				$sheet->getStyle('B104:H106')->applyFromArray($this->ALL_BORDER);
				
				
				$sheet->cell('B104:H104', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#8DB4E2');
				});
				
				$sheet->getStyle('B105:C105')->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				#end VALORES DE LA OBRA

				$sheet->setWidth('A', 1.17);
				$sheet->setWidth('B', 11.67);
				$sheet->setWidth('C', 19.67);
				$sheet->setWidth('D', 16.33);
				$sheet->setWidth('E', 20.5);
				$sheet->setWidth('F', 20.5);
				$sheet->setWidth('G', 21.33);
				$sheet->setWidth('H', 16.67);


			});


			$excel->sheet('Programa',function($sheet) use ($BudgetUploads)
			{
				$bgup = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '    FECHA DE INICIO:';
				$row[] = \Carbon\Carbon::parse($bgup->fechainicio)->format('d/m/Y');
				$row[] = '';
				$row[] = 'AÑO :';
				$row[] = \Carbon\Carbon::parse($bgup->fechaterminacion)->format('Y');
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '             FECHA DE TERMINACIÓN:';
				$row[] = \Carbon\Carbon::parse($bgup->fechaterminacion)->format('d/m/Y');
				$row[] = '';
				$row[] = 'DIAS DEL AÑO:';
				$vap = App\COCValuesThatApply::where('idUpload',$BudgetUploads->id)->first();
				$tipo_año = $vap->diasaconsiderarenelaño;
				$row[] = $tipo_año == 1 ? 356 : 360;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'PLAZO EN DIAS:';
				$encz = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();
				$row[] = $encz->plazocalculado;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$min_periodo = App\COPeriodProgram::where('idUpload',$BudgetUploads->id)->min('programado');
				$max_periodo = App\COPeriodProgram::where('idUpload',$BudgetUploads->id)->max('programado');
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'PeriodoInicial';
				$row[] = $min_periodo;
				$row[] = 'Periodo Final';
				$row[] = $max_periodo;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'PERIODO					';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'COSTO DIRECTO POR PERIODO				';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '% DE AVANCE	';
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'PROGRAMADO';
				$row[] = 'TITULO';
				$row[] = 'DIAS NATURALES';
				$row[] = 'DIAS TOTALES';
				$row[] = 'FACTOR';
				$row[] = 'AÑO';
				$row[] = 'IMPORTE DEL PERIODO';
				$row[] = 'TOTAL COSTO DIRECTO';
				$row[] = 'COSTO MATERIALES';
				$row[] = 'COSTO MANO DE OBRA';
				$row[] = 'COSTO EQUIPO';
				$row[] = 'COSTO OTROS INSUMOS';
				$row[] = 'PARCIAL';
				$row[] = 'ACUMULADO';
				$sheet->appendRow($row);

				foreach (App\COPeriodProgram::where('idUpload',$BudgetUploads->id)->get() as $bg) {
					$row = [];
					$row[] = '';
					$row[] = $bg->programado;
					$row[] = $bg->titulo;
					$row[] = $bg->diasnaturales;
					$row[] = $bg->diastotales;
					$row[] = $bg->factorano;
					$row[] = $bg->ano;
					$row[] = $bg->importedelperiodo;

					$cdp = App\COCostPeriodProgram::where('idProgramado',$bg->id)->first();
					$avance = App\COAdvanceProgram::where('idProgramado',$bg->id)->first();

					$row[] = $cdp->totalcostodirecto;
					$row[] = $cdp->costomateriales;
					$row[] = $cdp->costomanodeobra;
					$row[] = $cdp->costoequipo;
					$row[] = $cdp->costootrosinsumos;
					
					$row[] = $avance->parcial/100;
					$row[] = $avance->acumulado/100;
					$sheet->appendRow($row);

					$sheet->getColumnDimension('A')->setVisible(false);
					$sheet->setWidth('B', 13.67);
					$sheet->setWidth('C', 14.67);
					$sheet->setWidth('D', 10);
					$sheet->setWidth('E', 10);
					$sheet->setWidth('F', 10);
					$sheet->setWidth('G', 10);
					$sheet->setWidth('H', 13.5);
					$sheet->setWidth('I', 13.5);
					$sheet->setWidth('J', 11.3);
					$sheet->setWidth('K', 11.3);
					$sheet->setWidth('K', 12);
					$sheet->setWidth('L', 12);
					$sheet->setWidth('M', 11.67);
					$sheet->setWidth('N', 10);
					$sheet->setWidth('O', 10);
					
					$sheet->getStyle("B12:O".$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);

					$sheet->getStyle('B13:O'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
					$sheet->getStyle('B4:H7')->applyFromArray($this->DOUBLE_BORDER);
					$sheet->getStyle('B11:N12')->applyFromArray($this->DOUBLE_BORDER);
					$sheet->getStyle('H11:H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
					$sheet->mergeCells('B11:G11');
					$sheet->mergeCells('I11:M11');
					$sheet->mergeCells('N11:O11');
				
				
				$sheet->cell('B11:B11', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setFontWeight('bold');
				});
				$sheet->cell('I11:I11', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setFontWeight('bold');
				});
				$sheet->cell('N11:N11', function($cells)
				{
					$cells->setAlignment('center');
				});
				$sheet->cell('H12:H12', function($cells)
				{
					$cells->setFontWeight('bold');
				});

				$sheet->cell('B12:G12', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#FCE9D9');
				});
				$sheet->cell('I12:M12', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#FFFF02');
				});
				$sheet->cell('B12:O'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setValignment('center');
					$cells->setAlignment('center');
					$cells->setFontSize(8);
				});
				$sheet->getStyle("B12:O".$sheet->getHighestRow())->getAlignment()->setWrapText(true);
				
				
				$sheet->getStyle('H13:M'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_NUMBER_COMMA);
				$sheet->getStyle('N13:O'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->cell('B13:O'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontSize(8);
				});
				$sheet->cell('C4:C7', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('F4:F5', function($cells)
				{
					$cells->setAlignment('right');
				});
				
				}
				
			});

			$excel->sheet('a)Plantilla',function($sheet) use ($BudgetUploads)
			{

				$sheet->setWidth('A', 16.67);
				$sheet->setWidth('B', 30.67);
				$sheet->setWidth('C', 9.5);
				$sheet->setWidth('D', 15.83);
				$sheet->setWidth('E', 12.5);
				$sheet->setWidth('F', 10);
				$sheet->setWidth('G', 10);
				$sheet->setWidth('H', 10);
				$sheet->setWidth('I', 2);
				$sheet->setWidth('J', 11);

				$row = [];
				$row[] = 'PLANTILLA DE OFICINA CENTRAL Y CAMPO';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'Duracion de la Obra (en dias)';
				$bgup = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();
				$row[] = $bgup ->plazocalculado;
				$sheet->appendRow($row);

				$sheet->mergeCells('A1:E1');
				$sheet->cell('D2', function($cells)
				{
					$cells->setAlignment('right');
				});
				
				$row = [];
				$row[] = '';
				$row[] = 'PERSONAL DE OFICINA DE CAMPO';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'FACTORES	';
				$sheet->appendRow($row);
				$sheet->mergeCells('B3:E3');
				$sheet->mergeCells('G3:H3');

				$sheet->getStyle('B3')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G3')->applyFromArray($this->ALL_BORDER);
				
				$sheet->cell('A1', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});
				$sheet->cell('B'.$sheet->getHighestRow().':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFCC01');
				});
				$sheet->cell('G'.$sheet->getHighestRow().':H'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFCC01');
				});
				
				$row = [];
				$row[] = 'GASTOS TECNICOS';
				$row[] = 'Monto TotaL';
				$row[] = '';
				$row[] = '';
				$row[] = 'Porcentaje';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'Y ';
				$bgup = App\SobrecostoPlantillaPOCampoGeneral::where('idUpload',$BudgetUploads->id)->first();
				$general = App\COGeneralTemplate::where('idUpload',$BudgetUploads->id)->first();
				$row[] = $bgup->montototal;
				$row[] = '';
				$row[] = '';
				$row[] = $bgup->porcentaje /100;
				$row[] = '';
				$row[] = $general->factor1;
				$row[] = $general->factor2;
				$row[] = '';
				$row[] = $general->porcentaje /100;
				$sheet->appendRow($row);

				$sheet->getStyle('B'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('J'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$sheet->cell('B'.($sheet->getHighestRow()-1).':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#E3E3E3');
				});
				$sheet->cell('G'.$sheet->getHighestRow().':H'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFFF02');
				});
				$sheet->cell('J'.$sheet->getHighestRow().':J'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFCC01');
				});

				$sheet->getStyle('B'.($sheet->getHighestRow()-1).':B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('C'.($sheet->getHighestRow()-1).':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.($sheet->getHighestRow()-1).':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.$sheet->getHighestRow().':G'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('H'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('J'.$sheet->getHighestRow().':J'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				

				
				
				
				$row = [];
				$row[] = 'ADMINISTRATIVOS';
				$row[] = 'CATEGORIAS';
				$row[] = 'CANTIDAD';
				$row[] = 'SALARIO MENSUAL';
				$row[] = 'IMPORTE';
				$sheet->appendRow($row);
				$sheet->cell('A'.($sheet->getHighestRow()-3).':A'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#99CCFF');
				});
				$sheet->getStyle('A'.($sheet->getHighestRow()-3).':A'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('B'.$sheet->getHighestRow().':B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('D'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				$oldHighestRow = $sheet->getHighestRow() + 1;
				foreach (App\SobrecostoPlantillaPOCampo::where('idUpload',$BudgetUploads->id)->get() as $bg) {
					$row = [];
					$row[] = $bg->group;
					$row[] = $bg->category;
					$row[] = $bg->amount;
					$row[] = $bg->salary;
					$row[] = $bg->import;
					$row[] = '';
					$row[] = $bg->factor1;
					$row[] = $bg->factor2;
					$sheet->appendRow($row);
				}

				$sheet->getStyle('D'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);

				$sheet->getStyle('A'.$oldHighestRow.':A'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B'.$oldHighestRow.':B'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C'.$oldHighestRow.':C'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('D'.$oldHighestRow.':D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('G'.$oldHighestRow.':G'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);


				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = 'GASTOS DIRECTIVOS';
				$row[] = 'PERSONAL DE OFICINA CENTRAL';
				$sheet->appendRow($row);

				$sheet->getStyle('B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('B'.$sheet->getHighestRow().':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFCC01');
				});
				$sheet->mergeCells('B'.$sheet->getHighestRow().':E'.$sheet->getHighestRow());

				

				$row = [];
				$row[] = 'TECNICOS Y';
				$row[] = 'Monto TotaL';
				$row[] = '';
				$row[] = '';
				$row[] = 'Porcentaje';
				$sheet->appendRow($row);

				$row = [];
				$row[] = 'ADMINISTRATIVOS';
				$bgup = App\COCentralStaffGeneralTemplate::where('idUpload',$BudgetUploads->id)->first();

				$row[] = $bgup->montototal;
				$row[] = '';
				$row[] = '';
				$row[] = $bgup->porcentaje /100;
				$sheet->appendRow($row);

				$sheet->getStyle('B'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$sheet->cell('B'.($sheet->getHighestRow()-1).':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('center');
					$cells->setBackground('#E3E3E3');
				});
				
				$sheet->getStyle('B'.($sheet->getHighestRow()-1).':B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('C'.($sheet->getHighestRow()-1).':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.($sheet->getHighestRow()-1).':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);

				


				$row = [];
				$row[] = '';
				$row[] = 'CATEGORIAS';
				$row[] = 'CANTIDAD';
				$row[] = 'SALARIO MENSUAL';
				$row[] = 'IMPORTE';
				$sheet->appendRow($row);
				$sheet->cell('A'.($sheet->getHighestRow()-3).':A'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#99CCFF');
				});
				$sheet->getStyle('A'.($sheet->getHighestRow()-3).':A'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('B'.$sheet->getHighestRow().':B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('C'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('D'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				$oldHighestRow = $sheet->getHighestRow() + 1;
				foreach (App\COCentralStaffTemplate::where('idUpload',$BudgetUploads->id)->get() as $bg) {
					$row = [];
					$row[] = $bg->group;
					$row[] = $bg->category;
					$row[] = $bg->amount;
					$row[] = $bg->salary;
					$row[] = $bg->import;
					$row[] = '';
					$row[] = $bg->factor1;
					$row[] = $bg->factor2;
					$sheet->appendRow($row);
				}
				$sheet->getStyle('D'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);

				$sheet->getStyle('A'.$oldHighestRow.':E'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('G'.$oldHighestRow.':G'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'NOTA: del renglón 63 al 117 se encuentran ocultos. Son las categorias de personal de campo y oficina central.';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = 'LISTADO DE PERSONAL DE CAMPO';
				$sheet->appendRow($row);
				$sheet->getStyle('A'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->cell('A'.$sheet->getHighestRow().':D'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFFF99');
				});
				


				$oldHighestRow = $sheet->getHighestRow() + 1;
				foreach (App\COFieldStaffListTemplate::where('idUpload',$BudgetUploads->id)->get() as $bg) {
					$row = [];
					$row[] = $bg->group;
					$row[] = $bg->category;
					$sheet->appendRow($row);
				}
				$sheet->getStyle('A'.$oldHighestRow.':D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				
				$row = [];
				$row[] = '';
				$row[] = 'LISTADO DE PERSONAL DE OFICINA CENTRAL';
				$sheet->appendRow($row);
				$sheet->getStyle('A'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->cell('A'.$sheet->getHighestRow().':D'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#FFFF99');
				});

				$oldHighestRow = $sheet->getHighestRow() + 1;
				foreach (App\COCentralStaffListTemplate::where('idUpload',$BudgetUploads->id)->get() as $bg) {
					$row = [];
					$row[] = $bg->group;
					$row[] = $bg->category;
					$sheet->appendRow($row);
				}
				$sheet->getStyle('A'.$oldHighestRow.':D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				


			});

			$excel->sheet('b)Indirectos Desglosados',function($sheet) use ($BudgetUploads)
			{

				$sheet->setWidth('A', 4);
				$sheet->setWidth('B', 16.33);
				$sheet->setWidth('C', 16.33);
				$sheet->setWidth('D', 16.33);
				$sheet->setWidth('E', 12.33);
				$sheet->setWidth('F', 13.33);
				$sheet->setWidth('G', 12.17);
				$sheet->setWidth('H', 14.67);

			
				
				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$sheet->mergeCells('A1:H1');
				$sheet->cell('A1', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();

				$COCValues = App\COCValues::where('idUpload',$BudgetUploads->id)->first();
				$COIndirectItemizedGeneral = App\COIndirectItemizedGeneral::where('idUpload',$BudgetUploads->id)->first();
				$COIndirectItemizedConcept = App\COIndirectItemizedConcept::where('idUpload',$BudgetUploads->id)->get();
				$row = [];
				$row[] = '';
				$row[] = '                              Dependencia:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);

				
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '                              Concurso No.';
				$row[] = $CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = '';
				$row[] = 'FECHA:';
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '                              Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$sheet->getStyle('C6')->getAlignment()->setWrapText(true);
				$sheet->mergeCells('C6:G8');

				$row = [];
				$row[] = '';
				$row[] = '                              Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'INICIO:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechainicio)->format('d/m/Y');
				$row[] = 'TERMINACION:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechaterminacion)->format('d/m/Y');
				$row[] = 'PLAZO:';
				$row[] = $CostOverrunsNCGHeader->plazocalculado.' DN';
				$sheet->appendRow($row);

				$sheet->cell('B3:B9', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('E5', function($cells)
				{
					$cells->setAlignment('right');
				});

				$sheet->getStyle('C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);
				$sheet->getStyle('E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_DATE);
				$sheet->getStyle('A'.$sheet->getHighestRow().':C'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('D'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('F'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				
				$row = [];
				$row[] = 'DESGLOSE DE COSTOS INDIRECTOS';
				$sheet->appendRow($row);
				$sheet->mergeCells('A11:H11');
				$sheet->cell('A11', function($cells)
				{
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setBackground('#1F497D');
				});
				
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'MONTO DE LA OBRA A COSTO DIRECTO $ ';
				$row[] = $COIndirectItemizedGeneral->montoobra;
				$sheet->appendRow($row);
				$sheet->getStyle('G'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->cell('G'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontSize(8);
				});
				$sheet->cell('F'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'TOTAL DE COSTOS INDIRECTOS';
				$sheet->appendRow($row);

				$sheet->getStyle('E'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				$row = [];
				$row[] = '';
				$row[] = 'CONCEPTO';
				$row[] = '';
				$row[] = '';
				$row[] = 'ADMINISTRACION OFICINA CENTRAL';
				$row[] = '';
				$row[] = 'ADMINISTRACION OFICINA DE CAMPO ';
				$sheet->appendRow($row);
				$sheet->getStyle('E'.$sheet->getHighestRow().':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'MONTO';
				$row[] = 'PORCENTAJE';
				$row[] = 'MONTO';
				$row[] = 'PORCENTAJE';
				$sheet->appendRow($row);
				$sheet->getStyle('A'.($sheet->getHighestRow()-2).':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('F'.$sheet->getHighestRow().':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$oldHighestRow = $sheet->getHighestRow() + 1;
				foreach ($COIndirectItemizedConcept as $bg) {

					switch ($bg->type) {
						case 0:
							$row = [];
							$row[] = substr($bg->concepto,0,2);
							$row[] = substr($bg->concepto,3,strlen($bg->concepto));
							$row[] = '';
							$row[] = '';
							$row[] = $bg->monto1;
							$row[] = $bg->porcentaje1 /100;
							$row[] = $bg->monto2;
							$row[] = $bg->porcentaje2 /100;
							$sheet->appendRow($row);
							break;
						case 1:
							$row = [];
							$row[] = '';
							$row[] = '';
							$row[] = '';
							$row[] = 'SUBTOTALES';
							$row[] = $bg->monto1;
							$row[] = $bg->porcentaje1 /100;
							$row[] = $bg->monto2;
							$row[] = $bg->porcentaje2 /100;
							$sheet->appendRow($row);
							break;
						case 2:
							$row = [];
							$row[] = '';
							$row[] = '';
							$row[] = 'T O T A L E S';
							$row[] = '';
							$row[] = $bg->monto1;
							$row[] = $bg->porcentaje1 /100;
							$row[] = $bg->monto2;
							$row[] = $bg->porcentaje2 /100;
							$sheet->appendRow($row);
							break;
						
						default:
							# code...
							break;
					}
				}

				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('F'.$oldHighestRow.':F'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('G'.$oldHighestRow.':G'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$sheet->getStyle('A'.$oldHighestRow.':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('F'.$oldHighestRow.':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.$oldHighestRow.':G'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('A'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('F'.$sheet->getHighestRow().':F'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('G'.$sheet->getHighestRow().':G'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('H'.$sheet->getHighestRow().':H'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'TOTALES';
				$row[] = $COIndirectItemizedGeneral->totales;
				$row[] = '%  INDIRECTO ';
				$row[] = $COIndirectItemizedGeneral->indirecto /100;
				$sheet->appendRow($row);
				$sheet->getStyle('F'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('H'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->cargo;
				$sheet->appendRow($row);

				$sheet->getStyle('A'.($sheet->getHighestRow() -2).':D'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('E'.($sheet->getHighestRow() -2).':H'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				
				
			});

			$excel->sheet('c)Resumen Indirectos',function($sheet) use ($BudgetUploads)
			{

				$sheet->setWidth('B', 16.33);
				$sheet->setWidth('C', 16.33);
				$sheet->setWidth('D', 13.33);
				$sheet->setWidth('E', 13.33);
				$sheet->setWidth('F', 11.57);
				$sheet->setWidth('G', 13.33);
				$sheet->setWidth('H', 9.83);
				$sheet->setWidth('I', 9.67);


				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();
				
				$COSummaryGeneralIndirect = App\COSummaryGeneralIndirect::where('idUpload',$BudgetUploads->id)->first();
				$COSummaryIndirectConcept = App\COSummaryIndirectConcept::where('idUpload',$BudgetUploads->id)->get();

				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '                              Dependencia:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '                              Concurso No.';
				$row[] = $CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = '';
				$row[] = 'FECHA:';
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '                              Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '                              Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = 'INICIO:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechainicio)->format('d/m/Y');
				$row[] = 'TERMINACION:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechaterminacion)->format('d/m/Y');
				$row[] = 'PLAZO:';
				$row[] = $CostOverrunsNCGHeader->plazocalculado.' DIAS';
				$sheet->appendRow($row);

				
				$sheet->mergeCells('A1:I1');
				$sheet->getStyle('A12:C12')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('D12:E12')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('F12:I12')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('A1:I12')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('A1', function($cells)
				{
					$cells->setAlignment('center');
				});
				$sheet->mergeCells('C8:G10');
				$sheet->getStyle('C8')->getAlignment()->setWrapText(true);

				$sheet->cell('B3:B12', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('D7', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('D12', function($cells)
				{
					$cells->setAlignment('right');
				});

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'RESUMEN DE COSTOS INDIRECTOS';
				$sheet->appendRow($row);
				$sheet->mergeCells('A14:I14');

				$sheet->cell('A14', function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'MONTO DE LA OBRA A COSTO DIRECTO :';
				$row[] = $COSummaryGeneralIndirect->montoobra;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$sheet->cell('E15', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('F15', function($cells)
				{
					$cells->setFontSize(8);
				});
				$sheet->getStyle('F15')->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'ADMINISTRACION OFICINA CENTRAL';
				$row[] = '';
				$row[] = 'ADMINISTRACION DE CAMPO';
				$row[] = '';
				$row[] = 'TOTALES';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'CONCEPTO';
				$row[] = '';
				$row[] = 'MONTO';
				$row[] = '%';
				$row[] = 'MONTO';
				$row[] = '%';
				$row[] = 'MONTO';
				$row[] = '%';
				$sheet->appendRow($row);

				$sheet->cell('B'.$sheet->getHighestRow().':I'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setValignment('center');
					$cells->setAlignment('center');
				});

				$sheet->getStyle('A17:C18')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('D17:E18')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('F17:G18')->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('H17:I18')->applyFromArray($this->DOUBLE_BORDER);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$oldHighestRow = $sheet->getHighestRow() +1;
				foreach ($COSummaryIndirectConcept as $bg) {
					$row = [];
					$row[] = '';
					$sheet->appendRow($row);
					switch ($bg->type) {
						case 0:
							$row = [];
							$row[] = substr($bg->concepto,0,2);
							$row[] = substr($bg->concepto,3,strlen($bg->concepto));
							$row[] = '';
							$row[] = $bg->monto1;
							$row[] = $bg->porcentaje1 /100;
							$row[] = $bg->monto2;
							$row[] = $bg->porcentaje2 /100;
							$row[] = $bg->montototal;
							$row[] = $bg->porcentajetotal /100;
							
							$sheet->appendRow($row);
							break;
						case 2:
							$row = [];
							$row[] = '';
							$row[] = '';
							$row[] = $bg->concepto;
							$row[] = $bg->monto1;
							$row[] = $bg->porcentaje1 /100;
							$row[] = $bg->monto2;
							$row[] = $bg->porcentaje2 /100;
							$row[] = $bg->montototal;
							$row[] = $bg->porcentajetotal /100;
							
							$sheet->appendRow($row);
							break;
						
						default:
							# code...
							break;
					}
				}
				$sheet->cell('D'.$oldHighestRow.':I'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontSize(8);
				});
				$sheet->getStyle('D'.$oldHighestRow.':D'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('F'.$oldHighestRow.':F'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('G'.$oldHighestRow.':G'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('H'.$oldHighestRow.':H'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('I'.$oldHighestRow.':I'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				$sheet->getStyle('A'.$oldHighestRow.':C'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('D'.$oldHighestRow.':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('F'.$oldHighestRow.':G'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('H'.$oldHighestRow.':I'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);


				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'TOTALES';
				$row[] = $COSummaryGeneralIndirect->totales;
				$row[] = '';
				$row[] = '';
				$row[] = '%  INDIRECTO ';
				$row[] = $COSummaryGeneralIndirect->indirecto /100;
				$sheet->appendRow($row);
				$sheet->getStyle('E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_MONEY);
				$sheet->getStyle('I'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				
				$sheet->cell('D'.$sheet->getHighestRow().':I'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontSize(8);
				});
				$sheet->cell('E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('H'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$sheet->getStyle('A'.$sheet->getHighestRow().':I'.$sheet->getHighestRow())->applyFromArray($this->BOTTOM_BORDER);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);


				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->cargo;
				$sheet->appendRow($row);
				$sheet->getStyle('A'.($sheet->getHighestRow()-6).':I'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);


			});

			$excel->sheet('d)Pers.Técnico',function($sheet) use ($BudgetUploads)
			{
				//$sheet->setAutoSize(true);

				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();
				$sheet->setWidth('A', 15);
				$sheet->setWidth('B', 20);
				$sheet->setWidth('C', 12);
				$sheet->setWidth('D', 7);
				
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();

				$COCRequiredValues = App\COCRequiredValues::where('idUpload',$BudgetUploads->id)->first();
				$COTechnicalStaffConcept = App\COTechnicalStaffConcept::where('idUpload',$BudgetUploads->id)->get();

				$c = App\COTechnicalStaffConcept::where('idUpload',$BudgetUploads->id)->whereNotNull('total')->first();

				$row = [];
				$row[] = '              Dependencia:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '              Concurso No.';
				$row[] = $CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = '';
				$row[] = '';
				$row[] = 'Fecha:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGCompetition->fechadeconcurso)->format('d/m/Y');
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '              Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '              Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'Inicio:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechainicio)->format('d/m/Y');
				$row[] = 'Terminación:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechaterminacion)->format('d/m/Y');
				$row[] = 'Duración (dias)';
				$row[] = $CostOverrunsNCGHeader->plazocalculado;
				$sheet->appendRow($row);

				$sheet->mergeCells('B2:J4');
				$sheet->mergeCells('B6:J8');
				$sheet->getStyle('B2')->getAlignment()->setWrapText(true);
				$sheet->getStyle('B6')->getAlignment()->setWrapText(true);

				$sheet->cell('A2:A9', function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('E5', function($cells)
				{
					$cells->setAlignment('right');
				});
				

				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				
				$sheet->appendRow($row);
				$row = [];
				$row[] = 'RAZON SOCIAL DEL LICITANTE';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'REPRESENTANTE LEGAL';
				$sheet->appendRow($row);
				$row = [];
				$sheet->getStyle('A'.($sheet->getHighestRow()-1).':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('G'.($sheet->getHighestRow()-1).':M'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$row[] = 'PROGRAMA CALENDARIZADO DE UTILIZACION DE PERSONAL PROFESIONAL TECNICO, ADMINISTRATIVO Y DE SERVICIO ENCARGADO DE LA DIRECCION,';
				$sheet->appendRow($row);
				$row = [];

				$text = '  Horas Hombre';
				if($COCRequiredValues->presentaciondelprogramadepersonaltecnico == 1)
					$text = '  en No. De PERSONAS';
				if($COCRequiredValues->presentaciondelprogramadepersonaltecnico == 2)
					$text = ' EN JORNALES';
				$row[] = 'SUPERVISION Y ADMINISTRACION DE LOS TRABAJOS'.$text;
				$sheet->appendRow($row);
				$sheet->getStyle('A'.($sheet->getHighestRow()-1).':M'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'AÑO';
				foreach (App\COTechnicalStaffYear::where('idConcept',$c->id)->get() as $key => $value) {
					$row[] = $value->ano;
					
				}
				
				$sheet->appendRow($row);
				$row = [];
				$row[] = 'AREA DE TRABAJO';
				$row[] = 'CATEGORIA';
				$row[] = 'UNIDAD';
				$row[] = 'TOTAL';
				$countAnos = 0;
				$lastCol	= PHPExcel_Cell::stringFromColumnIndex($countAnos+3);
				$sheet->getStyle($lastCol.'14:'.$lastCol.'14')->applyFromArray($this->ALL_BORDER);
				foreach (App\COTechnicalStaffYear::where('idConcept',$c->id)->get() as $key => $value) {
					$row[] = $value->mes;
					$countAnos++;
					$lastCol	= PHPExcel_Cell::stringFromColumnIndex($countAnos+3);
					$sheet->getStyle($lastCol.'14:'.$lastCol.'14')->applyFromArray($this->ALL_BORDER);
				}
				$sheet->appendRow($row);

				$countAnos = 0;
				$lastCol = 'A';
				$lastCol	= PHPExcel_Cell::stringFromColumnIndex($countAnos+3);
				$sheet->getStyle($lastCol.'14:'.$lastCol.'14')->applyFromArray($this->ALL_BORDER);
				foreach (App\COTechnicalStaffYear::where('idConcept',$c->id)->get() as $key => $value) {
					$countAnos++;
					$lastCol	= PHPExcel_Cell::stringFromColumnIndex($countAnos+3);
					$sheet->getStyle($lastCol.'15:'.$lastCol.'15')->applyFromArray($this->ALL_BORDER);
				}
				$sheet->getStyle('A14:A15')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B14:B15')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C14:C15')->applyFromArray($this->ALL_BORDER);
				
				$COTechnicalStaffConcept = App\COTechnicalStaffConcept::where('idUpload',$BudgetUploads->id)->whereNull('parent')->get();
				foreach ($COTechnicalStaffConcept as $bg)
				{
					if($bg->childrens()->count() > 0)
					{
						$row = [];
						$row[] = $bg->category;
						$sheet->appendRow($row);
						foreach ($bg->childrens as $bg2) {
							if($bg->childrens()->count() > 0)
							{
								$row = [];
								$row[] = $bg2->category;
								$sheet->appendRow($row);
								foreach ($bg2->childrens as $bg3) {
									$row = [];
									$row[] = '';
									$row[] = $bg3->category;
									$row[] = $bg3->measurement;
									$row[] = $bg3->total;
									foreach (App\COTechnicalStaffYear::where('idConcept',$bg3->id)->get() as $key => $ch) {
										$row[] = $ch->amount;
									}
									$sheet->appendRow($row);
								}
							}
						}
					}
				}
				$sheet->getStyle('A16:'.$lastCol.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);

			});

			$excel->sheet('e)Pers.Técnico$',function($sheet) use ($BudgetUploads)
			{
				$sheet->setWidth('A', 14);
				$sheet->setWidth('B', 20);
				$sheet->setWidth('C', 11);
				$sheet->setWidth('D', 9);
				$sheet->setWidth('E', 7);
				$sheet->setWidth('F', 9);
				$sheet->setWidth('M', 14);
				$sheet->setWidth('N', 14);
				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

				
				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGHeader = App\CostOverrunsNCGHeader::where('idUpload',$BudgetUploads->id)->first();

				$COCRequiredValues = App\COCRequiredValues::where('idUpload',$BudgetUploads->id)->first();
				$COTechnicalStaffSalaryConcept = App\COTechnicalStaffSalaryConcept::where('idUpload',$BudgetUploads->id)->get();

				$c = App\COTechnicalStaffSalaryConcept::where('idUpload',$BudgetUploads->id)->whereNotNull('salary')->first();

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '              Dependencia:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '              Concurso No.';
				$row[] = $CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = '';
				$row[] = '';
				$row[] = 'Fecha:';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGCompetition->fechadeconcurso)->format('d/m/Y');
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '              Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'Inicio';
				$row[] = 'Terminacion';
				$row[] = 'Duracion (dias)';
				$sheet->appendRow($row);

				$sheet->mergeCells('E5:L7');

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '              Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechainicio)->format('d/m/Y');
				$row[] = \Carbon\Carbon::parse($CostOverrunsNCGConstruction->fechaterminacion)->format('d/m/Y');
				$row[] = $CostOverrunsNCGHeader->plazocalculado;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				
				$sheet->appendRow($row);
				$row = [];
				$row[] = 'RAZON SOCIAL DEL LICITANTE';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'REPRESENTANTE LEGAL';
				$sheet->appendRow($row);

				$sheet->getStyle('A10:G11')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('H10:N11')->applyFromArray($this->OUTLINE_BORDER);

				$row = [];
				$row[] = 'PROGRAMA DE EROGACION MENSUAL DE UTILIZACION DE PERSONAL PROFESIONAL TECNICO, ADMINISTRATIVO Y DE SERVICIO ENCARGADO DE LA DIRECCION,';
				$sheet->appendRow($row);
				$row = [];

				$text = '  Horas Hombre';
				if($COCRequiredValues->presentaciondelprogramadepersonaltecnico == 1)
					$text = '  en No. De PERSONAS';
				if($COCRequiredValues->presentaciondelprogramadepersonaltecnico == 2)
					$text = ' EN JORNALES';
				$row[] = 'SUPERVISION Y ADMINISTRACION DE LOS TRABAJOS'.$text;
				$sheet->appendRow($row);
				$sheet->getStyle('A12:N13')->applyFromArray($this->OUTLINE_BORDER);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'UNIDAD';
				$row[] = 'CANTIDAD';
				$row[] = 'SALARIO';
				$row[] = 'AÑO';
				foreach (App\COTechnicalStaffYearSalary::where('idConcept',$c->id)->get() as $key => $value) {
					$row[] = $value->ano;
				}
				$sheet->appendRow($row);
				$row = [];
				$row[] = 'AREA DE TRABAJO';
				$row[] = 'CATEGORIA';
				$row[] = '';
				$row[] = '';
				$row[] = 'DIARIO';
				$row[] = 'IMPORTE';

				foreach (App\COTechnicalStaffYearSalary::where('idConcept',$c->id)->get() as $key => $value) {
					$row[] = $value->mes;
				}
				$sheet->appendRow($row);
				$sheet->getStyle('A14:B14')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('A14:B14')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('A15:A15')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B15:B15')->applyFromArray($this->ALL_BORDER);
				$sheet->mergeCells('C14:C15');
				$sheet->mergeCells('D14:D15');
				$sheet->getStyle('C14')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C15')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('E14:E15')->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('F14')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('F15')->applyFromArray($this->ALL_BORDER);
				$countAnos = 0;
				$lastCol   = 'A';
				$lastCol   = PHPExcel_Cell::stringFromColumnIndex($countAnos+3);
				$sheet->getStyle($lastCol.'14:'.$lastCol.'14')->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle($lastCol.'15:'.$lastCol.'15')->applyFromArray($this->ALL_BORDER);
				foreach (App\COTechnicalStaffYearSalary::where('idConcept',$c->id)->get() as $key => $value)
				{
					$countAnos++;
					$lastCol = PHPExcel_Cell::stringFromColumnIndex($countAnos+5);
					$sheet->getStyle($lastCol.'14:'.$lastCol.'14')->applyFromArray($this->ALL_BORDER);
					$sheet->getStyle($lastCol.'15:'.$lastCol.'15')->applyFromArray($this->ALL_BORDER);
				}
				$COTechnicalStaffSalaryConcept = App\COTechnicalStaffSalaryConcept::where('idUpload',$BudgetUploads->id)->whereNull('parent')->get();
				foreach ($COTechnicalStaffSalaryConcept as $bg)
				{
					if($bg->childrens()->count() > 0)
					{
						$row = [];
						$row[] = $bg->category;
						$sheet->appendRow($row);
						foreach ($bg->childrens as $bg2) {
							if($bg2->type == 3)
							{
								$row = [];
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = 'Subtotal';
								$row[] = $bg2->import;
								$sheet->appendRow($row);
							}
							if($bg2->type == 4)
							{
								$row = [];
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = 'Subtotal por periodo';
								$row[] = $bg2->import;
								foreach (App\COTechnicalStaffYearSalary::where('idConcept',$bg2->id)->get() as $key => $ch) {
									$row[] = $ch->amount;
								}
								$sheet->appendRow($row);
							}
							if($bg2->type == 5)
							{
								$row = [];
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = '';
								$row[] = 'Subtotal acumulado';
								$row[] = $bg2->import;
								foreach (App\COTechnicalStaffYearSalary::where('idConcept',$bg2->id)->get() as $key => $ch) {
									$row[] = $ch->amount;
								}
								$sheet->appendRow($row);
							}
		
							if($bg2->childrens()->count() > 0)
							{
								$row = [];
								$row[] = $bg2->category;
								$sheet->appendRow($row);
								foreach ($bg2->childrens as $bg3) {
									$row = [];
									$row[] = '';
									$row[] = $bg3->category;
									$row[] = $bg3->measurement;
									$row[] = $bg3->amount;
									$row[] = $bg3->salary;
									$row[] = $bg3->import;
									foreach (App\COTechnicalStaffYearSalary::where('idConcept',$bg3->id)->get() as $key => $ch) {
										$row[] = $ch->id;
									}
									$sheet->appendRow($row);
								}
							}
						}
					}
				}

				$sheet->getStyle('A16:'.$lastCol.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);

				$sheet->cell('D4:D8', function($cells)
				{
					$cells->setAlignment('right');
				});


			});

			$excel->sheet('f)Financ_Horizontal',function($sheet) use ($BudgetUploads)
			{

				$sheet->setWidth('A', 0.5);
				$sheet->setWidth('B', 39);


				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();
				
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$COCRequiredValues = App\COCRequiredValues::where('idUpload',$BudgetUploads->id)->first();

				$COFinancialConcept = App\COFinancialConcept::where('idUpload',$BudgetUploads->id)->whereNull('parent')->get();
				$c = App\COFinancialConcept::where('idUpload',$BudgetUploads->id)->whereNotNull('parent')->first();
				$anos = App\COFinancialMonth::where('idConcept',$c->id)->count();
				$COGeneralFinancial = App\COGeneralFinancial::where('idUpload',$BudgetUploads->id)->first();

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'LICITACION '.$CostOverrunsNCGCompetition->numerodeconcurso.' QUE SE CELEBRARA EN ';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'LUGAR DE CELEBRACION '.$CostOverrunsNCGCompetition->numerodeconcurso;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'El dia:';
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);

				$sheet->mergeCells('F4:J6');
				$sheet->getStyle('F4')->getAlignment()->setWrapText(true);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->cargo;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = 'ANÁLISIS   DE   LOS  COSTOS   DE    FINANCIAMIENTO';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'PERIODO DE COBRO PRIMERA ESTIMACION:';
				$row[] = $COCRequiredValues->periododecobroprimeraestimacion;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'CONCEPTO';
				$row[] = 'P E R I O D O S';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$lastCol = 'A';
				$countAnos = 0;
				foreach (App\COFinancialMonth::where('idConcept',$c->id)->get() as $key => $value) {
					$lastCol	= PHPExcel_Cell::stringFromColumnIndex($countAnos+2);
					$sheet->getColumnDimension($lastCol)->setWidth(20);
					$countAnos++;
					$row[] = $value->mes;
				}
				$sheet->appendRow($row);
				$sheet->getStyle('C16:'.$lastCol.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C16:'.$lastCol.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('C16:'.$lastCol.'16', function($cells)
				{
					$cells->setAlignment('center');
				});

				$count_name = 0;
				foreach ($COFinancialConcept as $bg) {
					if($bg->childrens()->count() > 0)
					{
						$row = [];
						$row[] = '';
						$row[] = $bg->concept;
						$sheet->appendRow($row);

						switch ($count_name) {
							case 0:
								$sheet->cell('B'.$sheet->getHighestRow(), function($cells)
								{
									$cells->setAlignment('center');
									$cells->setBackground('#FFFFCC');
									$cells->setFontWeight('bold');
								});
								$count_name++;
							break;
							case 1:
								$sheet->cell('B'.$sheet->getHighestRow(), function($cells)
								{
									$cells->setAlignment('center');
									$cells->setBackground('#CCFFFF');
									$cells->setFontWeight('bold');
								});
								$count_name++;
								break;
							
							default:
								# code...
								break;
						}


						foreach ($bg->childrens as $bg2) {
							$row = [];
							$row[] = '';
							$row[] = $bg2->concept;
							foreach (App\COFinancialMonth::where('idConcept',$bg2->id)->get() as $key => $ch) {
								$row[] = $ch->amount;
							}
							$sheet->appendRow($row);
						}
					}
				}
				$sheet->getStyle('C17:'.$lastCol.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_NUMBER_COMMA);

				$sheet->getStyle('B13:B'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->getStyle('B13:'.$lastCol.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'INDICADOR ECONOMICO DE REFERENCIA :';
				$row[] = $COGeneralFinancial->indicadoreconomicodereferencia/100;
				$row[] = '';
				$row[] = '';
				$row[] = 'TASA DE INTERES DIARIA :';
				$row[] = $COGeneralFinancial->tasadeinteresdiaria/100;
				$sheet->appendRow($row);

				$sheet->getStyle('C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->getStyle('G'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->cell('B'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('F'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				
				$row = [];
				$row[] = '';
				$row[] = 'PUNTOS DE INTERMEDIACIÓN DE LA BANCA :';
				$row[] = $COGeneralFinancial->puntosdeintermediaciondelabanca/100;
				$row[] = '';
				$row[] = '';
				$row[] = 'DIAS PARA PAGO DE ESTIMACIONES :';
				$row[] = $COGeneralFinancial->diasparapagodeestimaciones;
				$sheet->appendRow($row);

				$sheet->getStyle('C'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				$sheet->cell('B'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				$sheet->cell('F'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '% APLICABLE AL PERIODO :';
				$row[] = $COGeneralFinancial->aplicablealperiodo/100;
				$sheet->appendRow($row);
				$sheet->getStyle('G'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
				
				$sheet->cell('F'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});
				
				$row = [];
				$row[] = '';
				$row[] = 'PORCENTAJE DE FINANCIEAMIENTO=';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = '';
				$row[] = $COGeneralFinancial->porcentajedefinancieamiento/100;
				$sheet->appendRow($row);

				$sheet->cell('B'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setAlignment('right');
				});


				$sheet->getStyle('I'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
			
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'ESTE PORCENTAJE ESTÁ CALCULADO SOBRE LA SUMA DEL COSTO DIRECTO MAS INDIRECTOS.';
				$sheet->appendRow($row);

				$sheet->cell('B11:'.$lastCol.'11', function($cells)
				{
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setBackground('#0070C0');
				});

				$sheet->mergeCells('C13:'.$lastCol.'13');
				$sheet->cell('C13', function($cells)
				{
					$cells->setAlignment('center');
				});
				$sheet->mergeCells('B13:B16');
				$sheet->cell('B13', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setValignment('center');
				});

				$sheet->getStyle('B13')->applyFromArray($this->DOUBLE_BORDER);
				


			});

			$excel->sheet('g)Utilidad',function($sheet) use ($BudgetUploads)
			{
				$sheet->setWidth('A', 6);
				$sheet->setWidth('B', 46);
				$sheet->setWidth('C', 46);
				$sheet->setWidth('D', 14);
				$sheet->setWidth('E', 11);
				
				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$COSummaryGeneralIndirect = App\CODeterminationUtility::where('idUpload',$BudgetUploads->id)->get();
				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = 'Cliente:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'Concurso '.$CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'DETERMINACION DEL CARGO POR UTILIDAD ';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'CLAVE';
				$row[] = 'C O N C E P T O';
				$row[] = 'F O R M U L A';
				$row[] = 'IMPORTE ';
				$row[] = '%';
				$sheet->appendRow($row);

				$sheet->getStyle('A'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('A'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('A'.$sheet->getHighestRow().':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$oldHighestRow = $sheet->getHighestRow();
				foreach ($COSummaryGeneralIndirect as $bg) {
					$row = [];
					$row[] = $bg->clave;
					$row[] = $bg->concepto;
					$row[] = $bg->formula;
					$row[] = $bg->importe;
					$row[] = $bg->porcentaje/100;
					$sheet->appendRow($row);
				}

				$sheet->getStyle('D'.$oldHighestRow.':D'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_NUMBER_COMMA);
				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);

				

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->cargo;
				$sheet->appendRow($row);

				$sheet->getStyle('A'.$oldHighestRow.':E'.($sheet->getHighestRow()-4))->applyFromArray($this->DOUBLE_BORDER);

				$sheet->getStyle('A'.($sheet->getHighestRow()-3).':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);


				$sheet->mergeCells('B9:C11');
				$sheet->getStyle('B9')->getAlignment()->setWrapText(true);

				
				$sheet->mergeCells('A14:E14');
				$sheet->cell('A14', function($cells)
				{
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setBackground('#0070C0');
					$cells->setAlignment('center');
				});
				
			});

			$excel->sheet('h)Cargos_Adicionales',function($sheet) use ($BudgetUploads)
			{
				
				$sheet->setWidth('A', 7);
				$sheet->setWidth('B', 52);
				$sheet->setWidth('C', 41);
				$sheet->setWidth('D', 16);
				$sheet->setWidth('E', 11);

				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();
				$COAdditionalCharges = App\COAdditionalCharges::where('idUpload',$BudgetUploads->id)->get();
				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = 'Cliente:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'Concurso '.$CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'DETERMINACION DEL CARGO POR UTILIDAD ';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = 'CLAVE';
				$row[] = 'C O N C E P T O';
				$row[] = 'F O R M U L A';
				$row[] = 'IMPORTE  ';
				$row[] = '%';
				$sheet->appendRow($row);

				$sheet->getStyle('A'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('D'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('A'.$sheet->getHighestRow().':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('A'.$sheet->getHighestRow().':E'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$oldHighestRow = $sheet->getHighestRow();
				foreach ($COAdditionalCharges as $bg) {
					$row = [];
					$row[] = $bg->clave;
					$row[] = $bg->concepto;
					$row[] = $bg->formula;
					$row[] = $bg->importe;
					$row[] = $bg->porcentaje/100;
					$sheet->appendRow($row);

					if(strlen($bg->concepto) > 20)
					{
						$sheet->getStyle('B'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
					}

				}
				$sheet->getStyle('D'.$oldHighestRow.':D'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_NUMBER_COMMA);
				$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);


				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->responsable;
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->cargo;
				$sheet->appendRow($row);

				$sheet->getStyle('A'.$oldHighestRow.':E'.($sheet->getHighestRow()-4))->applyFromArray($this->DOUBLE_BORDER);

				$sheet->getStyle('A'.($sheet->getHighestRow()-3).':E'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);


				$sheet->mergeCells('B9:C11');
				$sheet->getStyle('B9')->getAlignment()->setWrapText(true);

				
				$sheet->mergeCells('A14:E14');
				$sheet->cell('A14', function($cells)
				{
					$cells->setFontColor('#ffffff');
					$cells->setFontWeight('bold');
					$cells->setBackground('#0070C0');
					$cells->setAlignment('center');
				});

				
			});

			$excel->sheet('i)Resumen',function($sheet) use ($BudgetUploads)
			{
				
				$sheet->setWidth('A', 2);
				$sheet->setWidth('B', 7);
				$sheet->setWidth('C', 48);
				$sheet->setWidth('D', 14);
				$sheet->setWidth('E', 16);
				$sheet->setWidth('F', 11);

				$CostOverrunsNCGCustomers = App\CostOverrunsNCGCustomers::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGCompetition = App\CostOverrunsNCGCompetition::where('idUpload',$BudgetUploads->id)->first();
				$CostOverrunsNCGConstruction = App\CostOverrunsNCGConstruction::where('idUpload',$BudgetUploads->id)->first();

				$CostOverrunsNCGEnterprise = App\CostOverrunsNCGEnterprise::where('idUpload',$BudgetUploads->id)->first();

				$COSummaryConcept = App\COSummaryConcept::where('idUpload',$BudgetUploads->id)->get();
				
				$row = [];
				$row[] = '';
				$row[] = $CostOverrunsNCGEnterprise->razonsocial;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'Cliente:';
				$row[] = $CostOverrunsNCGCustomers->nombrecliente;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Concurso No. '.$CostOverrunsNCGCompetition->numerodeconcurso;
				$row[] = $CostOverrunsNCGCompetition->fechadeconcurso;
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'Obra:';
				$row[] = $CostOverrunsNCGConstruction->nombredelaobra;
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'Lugar:';
				$row[] = $CostOverrunsNCGConstruction->direcciondelaobra .','. $CostOverrunsNCGConstruction->ciudaddelaobra.','. $CostOverrunsNCGConstruction->estadodelaobra;
				$sheet->appendRow($row);

				$sheet->getStyle('B1'.':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);

				$sheet->mergeCells('C6:D8');
				$sheet->getStyle('C6')->getAlignment()->setWrapText(true);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'RESUMEN';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'CLAVE';
				$row[] = 'C O N C E P T O';
				$row[] = ' ';
				$row[] = 'IMPORTE ';
				$row[] = '%';
				$sheet->appendRow($row);


				$sheet->getStyle('B'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('C'.$sheet->getHighestRow().':D'.$sheet->getHighestRow())->applyFromArray($this->OUTLINE_BORDER);
				$sheet->getStyle('E'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('F'.$sheet->getHighestRow())->applyFromArray($this->ALL_BORDER);
				$sheet->getStyle('B'.$sheet->getHighestRow().':F'.$sheet->getHighestRow())->applyFromArray($this->DOUBLE_BORDER);
				$sheet->cell('B'.$sheet->getHighestRow().':F'.$sheet->getHighestRow(), function($cells)
				{
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
				});

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$oldHighestRow = $sheet->getHighestRow();
				
				foreach ($COSummaryConcept as $bg) {

					switch ($bg->concepto) {
						case 'FACTOR DE SOBRECOSTO':
							$row = [];
							$row[] = '';
							$row[] = '';
							$row[] = '';
							$row[] = '';
							$row[] = $bg->concepto;
							$row[] = $bg->importe;
							$sheet->appendRow($row);
							break;
						case 'PORCENTAJE':
							$row = [];
							$row[] = '';
							$row[] = '';
							$row[] = '';
							$row[] = '';
							$row[] = $bg->concepto;
							$row[] = $bg->porcentaje /100;
							$sheet->appendRow($row);
							$sheet->getStyle('E'.$oldHighestRow.':E'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_NUMBER_COMMA);
							$sheet->getStyle('F'.$sheet->getHighestRow().':F'.$sheet->getHighestRow())->getNumberFormat()->applyFromArray($this->FORMAT_PERCENTAGE);
							
							$sheet->getStyle('C'.$oldHighestRow.':E'.($sheet->getHighestRow()-1))->applyFromArray($this->OUTLINE_BORDER);
							$sheet->getStyle('C'.$oldHighestRow.':E'.($sheet->getHighestRow()))->applyFromArray($this->OUTLINE_BORDER);
							$sheet->getStyle('F'.$oldHighestRow.':F'.($sheet->getHighestRow()-1))->applyFromArray($this->OUTLINE_BORDER);
							$sheet->getStyle('F'.$oldHighestRow.':F'.($sheet->getHighestRow()))->applyFromArray($this->OUTLINE_BORDER);

							$sheet->getStyle('B'.$oldHighestRow.':F'.($sheet->getHighestRow()-2))->applyFromArray($this->DOUBLE_BORDER);
							$sheet->getStyle('B'.$oldHighestRow.':F'.($sheet->getHighestRow()))->applyFromArray($this->DOUBLE_BORDER);
							
							break;
						
						default:
							$row = [];
							$row[] = '';
							$row[] = $bg->clave;
							$row[] = $bg->concepto;
							$row[] = '';
							$row[] = $bg->importe;
							$row[] = $bg->porcentaje /100;
							$sheet->appendRow($row);
							break;
					}
					
				}



				
			});

			$excel->sheet('z)Documentacion',function($sheet) use ($BudgetUploads)
			{

				$sheet->setWidth('A', 2);
				$sheet->setWidth('B', 25);
				$sheet->setWidth('C', 23);

				$COEnterpriseDocument = App\COEnterpriseDocument::where('idUpload',$BudgetUploads->id)->get();

				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'PARAMETRO GENERAL';
				$row[] = 'PARAMETRO ESPECIFICO';

				
				foreach ($COEnterpriseDocument as $key => $bg) {
					$row[] = $bg->name;
		}
				$sheet->appendRow($row);

				$sheet->getStyle('B3:B3')->applyFromArray($this->DOUBLE_BORDER_RED);
				$sheet->getStyle('C3:C3')->applyFromArray($this->DOUBLE_BORDER_RED);
				$lastCol = 'A';
				foreach ($COEnterpriseDocument as $key => $bg) {
					$col = PHPExcel_Cell::stringFromColumnIndex($key+3);
					$lastCol = $col;
					$sheet->getStyle($col.'3')->applyFromArray($this->DOUBLE_BORDER_RED);
					$sheet->getColumnDimension($col)->setAutoSize(true);
					$sheet->cell($col.'3', function($cells)
					{
						$cells->setAlignment('center');
					});
				}
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Un ejercicio con un Anticipo';
				$v_unanticipo = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
					array_push($v_unanticipo,$c->unanticipo);
					$row[] = $c->unanticipo;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'TIPO DE ANTICIPO';
				$row[] = 'Un ejercicio con 2 anticipos';
				$v_dosanticipo = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
					array_push($v_dosanticipo,$c->dosanticipo);
					$row[] = $c->dosanticipo;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Rebasa un Ejercicio presupuestal';
				$v_rebasa = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COAdvanceDocumentation::where('idDocEmpresa',$bg->id)->first();
					array_push($v_rebasa,$c->rebasa);
					$row[] = $c->rebasa;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'MODELO DE CALCULO DEL FINANCIAMIENTO';
				$row[] = 'Importe Total de Obra';
				$v_importetotal = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COFinancingCalcDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_importetotal,$c->importetotal);
					$row[] = $c->importetotal;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Costo Directo+Indirecto';
				$v_costodirectoindirecto = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COFinancingCalcDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_costodirectoindirecto,$c->costodirectoindirecto);
					$row[] = $c->costodirectoindirecto;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Solo intereses negativos';
				$v_negativos = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_negativos,$c->negativos);
					$row[] = $c->negativos;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = 'INTERESES A CONSIDERAR EN EL FINANCIAMIENTO';
				$row[] = 'Ambos Interes (+ y -)';
				$v_ambos = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_ambos,$c->ambos);
					$row[] = $c->ambos;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Tasa Activa';
				$v_tasaactiva = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_tasaactiva,$c->tasaactiva);
					$row[] = $c->tasaactiva;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Tasa Pasiva';
				$v_tasapasiva = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COInterestsToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_tasapasiva,$c->tasapasiva);
					$row[] = $c->tasapasiva;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'CALCULO DEL CARGO ADICIONAL';
				$row[] = 'Sobre el Importe de Estimaciones';
				$v_sobreelimporte = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COAdditionalChargeCalcDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_sobreelimporte,$c->sobreelimporte);
					$row[] = $c->sobreelimporte;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Sobre el Costo directo de la Obra';
				$v_costodirecto = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COAdditionalChargeCalcDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_costodirecto,$c->costodirecto);
					$row[] = $c->costodirecto;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'DIAS A CONSIDERAR EN EL AÑO';
				$row[] = 'Año Fiscal (1 Ene al 31 Dic)';
				$v_anofiscal = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\CODaysToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_anofiscal,$c->anofiscal);
					$row[] = $c->anofiscal;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'Año Comercial (360 Dias)';
				$v_anocomercial = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\CODaysToConsiderDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_anocomercial,$c->anocomercial);
					$row[] = $c->anocomercial;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'DIAS A CONSIDERAR EN EL AÑO';
				$row[] = 'CA= Sub / (1-0.005) - Sub';
				$v_casub = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COThousandDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_casub,$c->casub);
					$row[] = $c->casub;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);

				$row = [];
				$row[] = '';
				$row[] = '';
				$row[] = 'CA= CA1* Sub ';
				$v_caca = [];
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\COThousandDocument::where('idDocEmpresa',$bg->id)->first();
					array_push($v_caca,$c->caca);
					$row[] = $c->caca;
		}
				$sheet->appendRow($row);
				$row = [];
				$row[] = '';
				$sheet->appendRow($row);
				
				$row = [];
				$row[] = '';
				$row[] = 'DIAS DE PAGO P/ESTIMACIONES';
				$row[] = '';
				foreach ($COEnterpriseDocument as $bg) {
					$c = App\CODaysToPayDocument::where('idDocEmpresa',$bg->id)->first();
					$row[] = $c->dias;
		}
				$sheet->appendRow($row);

				$sheet->getStyle('B4:C35')->getAlignment()->setWrapText(true);

				$sheet->getStyle('B4:B9')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B10:B10')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B11:B13')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B14:B21')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B14:B21')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B22:B22')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B23:B25')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B26:B26')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B27:B29')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B30:B30')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B31:B33')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B34:B34')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B35:B35')->applyFromArray($this->OUTLINE_BORDER_RED);


				$sheet->mergeCells('B11:B13');
				$sheet->mergeCells('B17:B19');
				$sheet->mergeCells('B23:B25');
				$sheet->mergeCells('B27:B29');
				$sheet->mergeCells('B31:B33');

				$sheet->cell('B4:B35', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setValignment('center');
				});
				$sheet->cell('C4:D35', function($cells)
				{
					$cells->setAlignment('center');
					$cells->setValignment('center');
				});
				
				

				$sheet->cell('B4:B9', function($cells){$cells->setBackground('#F2F2F2');});
				$sheet->cell('B11:B13', function($cells){$cells->setBackground('#F2F2F2');});
				$sheet->cell('B23:B25', function($cells){$cells->setBackground('#F2F2F2');});
				$sheet->cell('B27:B29', function($cells){$cells->setBackground('#F2F2F2');});
				$sheet->cell('B31:B33', function($cells){$cells->setBackground('#F2F2F2');});
				$sheet->cell('B35', function($cells){$cells->setBackground('#F2F2F2');});
				
				$sheet->getStyle('C4:'.$lastCol.'9')->applyFromArray($this->BOTTOM_BORDER_RED);
				$sheet->getStyle('C11:'.$lastCol.'13')->applyFromArray($this->BOTTOM_BORDER_RED);


				$sheet->getStyle('C15:'.$lastCol.'17')->applyFromArray($this->BOTTOM_BORDER_RED);

				$sheet->getStyle('D17:'.$lastCol.'19')->applyFromArray($this->BOTTOM_BORDER_RED);
				$sheet->getStyle('C19:'.$lastCol.'21')->applyFromArray($this->BOTTOM_BORDER_RED);
				$sheet->getStyle('C23:'.$lastCol.'25')->applyFromArray($this->BOTTOM_BORDER_RED);
				$sheet->getStyle('C26:'.$lastCol.'30')->applyFromArray($this->BOTTOM_BORDER_RED);
				$sheet->getStyle('C31:'.$lastCol.'33')->applyFromArray($this->BOTTOM_BORDER_RED);

				$sheet->getStyle('C4:'.$lastCol.'35')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B10:'.$lastCol.'10')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('C14:'.$lastCol.'14')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B22:'.$lastCol.'22')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B26:'.$lastCol.'26')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B30:'.$lastCol.'30')->applyFromArray($this->OUTLINE_BORDER_RED);
				$sheet->getStyle('B34:'.$lastCol.'34')->applyFromArray($this->OUTLINE_BORDER_RED);
				
				$sheet->getStyle('C4:'.$lastCol.'35')->applyFromArray($this->RIGHT_BORDER_RED);

				$colors = [
					[
						"array" => $v_unanticipo,
						"number" => 5
					],
					[
						"array" => $v_dosanticipo,
						"number" => 7
					],
					[
						"array" => $v_rebasa,
						"number" => 9
					],
					[
						"array" => $v_importetotal,
						"number" => 11
					],
					[
						"array" => $v_costodirectoindirecto,
						"number" => 13
					],
					[
						"array" => $v_negativos,
						"number" => 15
					],
					[
						"array" => $v_ambos,
						"number" => 17
					],
					[
						"array" => $v_tasaactiva,
						"number" => 19
					],
					[
						"array" => $v_tasapasiva,
						"number" => 21
					],
					[
						"array" => $v_sobreelimporte,
						"number" => 23
					],
					[
						"array" => $v_costodirecto,
						"number" => 25
					],
					[
						"array" => $v_anofiscal,
						"number" => 27
					],
					[
						"array" => $v_anocomercial,
						"number" => 29
					],
					[
						"array" => $v_casub,
						"number" => 31
					],
					[
						"array" => $v_caca,
						"number" => 33
					],
				];

				foreach( $colors as $c)
				{
					$number = $c["number"];
					foreach( $c["array"] as $key => $value)
					{
						$col = PHPExcel_Cell::stringFromColumnIndex($key+3);
						if($value == 1)
							$sheet->cell($col.$number, function($cells){$cells->setBackground('#FFFF02');});
						else
						$sheet->cell($col.$number, function($cells){$cells->setBackground('#F2F2F2');});
					}
				}

				for ($i=5; $i <= 33 ; $i+=2) { 
					$sheet->cell('c'.$i, function($cells){$cells->setBackground('#F2F2F2');});
				}

				$sheet->cell('D35:'.$lastCol.'35', function($cells)
				{
					$cells->setFontWeight('bold');
				});



			});

		})->export('xls');
	}


	public function SobrecostoStatus(Request $request)
	{
		$BudgetUploads = App\Sobrecostos::where('id',$request->budgetUpload)->first();
		return \Response::JSON(array(
			'BudgetUploads'       => $BudgetUploads,
		));
	}

	public function administrationIndex()
	{
		if(Auth::user()->module->where('id',243)->count()>0)
		{
			$data   = App\Module::find(243);
			return view('layouts.child_module',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => 243
				]);
		}
		else
		{
			return redirect('/');
		}
	}
	public function administrationCreate()
	{
		if(Auth::user()->module->where('id',243)->count()>0)
		{
			$data   = App\Module::find(243);
			return view('administracion.presupuestos.administracion.alta',
				[
					'id'		=> $data['father'],
					'title'		=> $data['name'],
					'details'	=> $data['details'],
					'child_id'	=> 	243,
					'option_id'	=> 	244,
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function administrationSearch(Request $request)
	{
		if(Auth::user()->module->where('id',243)->count()>0)
		{
			$data   		= App\Module::find(243);
			$mindate		= $request->mindate != "" ? Carbon::createFromFormat('d-m-Y',$request->mindate)->format('Y-m-d') : null;
			$maxdate		= $request->maxdate != "" ? Carbon::createFromFormat('d-m-Y',$request->maxdate)->format('Y-m-d') : null;
			$enterprise_id	= $request->enterprise_id;
			$department_id	= $request->department_id;
			$project_id		= $request->project_id;
			$weekOfYear		= $request->weekOfYear;
			$users_id 		= $request->users_id;
			$status 		= $request->status;
		
			$periodicity	= $request->periodicity;

			$budgets 		= App\AdministrativeBudget::where(function($query) use ($mindate,$maxdate,$enterprise_id,$department_id,$project_id,$weekOfYear,$users_id,$status,$periodicity)
							{
								if ($mindate != "") 
								{
									$query->where('initRange',$mindate);
								}
								if ($maxdate != "") 
								{
									$query->where('endRange',$maxdate);
								}
								if ($enterprise_id != "") 
								{
									$query->whereIn('enterprise_id',$enterprise_id);
								}
								if ($department_id != "") 
								{
									$query->whereIn('department_id',$department_id);
								}
								if ($project_id != "") 
								{
									$query->whereIn('project_id',$project_id);
								}
								if ($weekOfYear != "") 
								{
									$query->whereIn('weekOfYear',$weekOfYear);
								}
								if ($users_id != "") 
								{
									$query->whereIn('users_id',$users_id);
								}
								if ($periodicity != "") 
								{
									$query->whereIn('periodicity',[$periodicity]);
								}
								if ($status != "") 
								{
									$query->whereHas('detail',function($query) use($status)
									{
										$query->whereIn('status',$status);
									});
								}
							})
							->orderBy('id','DESC')
							->paginate(10);

			return view('administracion.presupuestos.administracion.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> 243,
					'option_id'		=> 245,
					'budgets'		=> $budgets,
					'mindate'		=> $request->mindate,
					'maxdate'		=> $request->maxdate,
					'enterprise_id'	=> $enterprise_id,
					'department_id'	=> $department_id,
					'project_id'	=> $project_id,
					'weekOfYear' 	=> $weekOfYear,
					'users_id' 		=> $users_id,
					'status' 		=> $status,
					'periodicity'	=> $periodicity
				]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function administrationEdit(App\AdministrativeBudget $budget)
	{
		if (Auth::user()->module->where('id',243)->count()>0) 
		{
			$data   		= App\Module::find(243);
			return view('administracion.presupuestos.administracion.edicion',
			[
				'id'			=> $data['father'],
				'title'			=> $data['name'],
				'details'		=> $data['details'],
				'child_id'		=> 243,
				'option_id'		=> 245,
				'budget'		=> $budget,
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function administrationUpdate(App\AdministrativeBudget $budget,Request $request)
	{
		if (Auth::user()->module->where('id',243)->count()>0) 
		{
			$budget->alert_percent 	= $request->alert_percent;
			$budget->save();

			if(isset($request->budget_id) && count($request->budget_id)>0)
			{
				for ($i=0; $i < count($request->budget_id); $i++) 
				{ 
					$amount					= 'amount_'.$request->budget_id[$i];
					$alert_percent			= 'alert_percent_'.$request->budget_id[$i];
					$update					= App\AdministrativeBudgetDetail::find($request->budget_id[$i]);
					$update->amount			= $request->$amount;
					$update->alert_percent	= $request->$alert_percent;

					$amountSpent	= $update->amount_spent;
					$amountBudget	= $request->$amount;
					$alertPercent	= $request->$alert_percent;

					if ($amountBudget == 0 && $amountSpent > 0) 
					{
						$update->status = 1;
					}
					else if($amountBudget > 0 && $amountSpent > 0)
					{
						$percentSpent = ($amountSpent*100)/($amountBudget);
						if ($percentSpent >= $alertPercent) 
						{
							$update->status = 1;
						}
						else
						{
							$update->status = 0;
						}
					}

					$update->save();
				}
			}

			$alert = "swal('','Presupuesto actualizado exitosamente','success');";
			return redirect()->route('budget.administration.edit',['budget'=>$budget])->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}


	public function downloadUploadLayoutBudget(Request $request)
	{
		if (Auth::user()->module->where('id',243)->count()>0) 
		{
			if (isset($request->download)) 
			{

				$enterprise		= $request->enterprise_download;
				$department 	= $request->department_download;
				$project 		= $request->project_download;
				$year 			= $request->year_download;

				$nameEnterprise	= App\Enterprise::find($enterprise)->name;
				
				$accounts		= App\Account::orderNumber()->where('idEnterprise',$enterprise)
									->where('selectable',1)
									->get();
				$titles		= [];
				$titles[]	= 'id';
				$titles[]	= 'cuenta';
				for ($i=1; $i < 53; $i++) 
				{ 
					$titles[] = 'monto_sem_'.$i;
				}
				
				$select = '
					account_id as idAccount,
					account as nameAccount,';
				for ($i=1; $i < 53; $i++)
				{ 
					$select .= '
						SUM(IF(administrative_budgets.weekOfYear = '.$i.', amount,0)) as amountWeek'.$i.',
					';
				}
				$select .= 'CONCAT(" ") as blank';
				$result = App\AdministrativeBudgetDetail::selectRaw($select)
					->leftJoin('administrative_budgets','administrative_budget_details.idAdministrativeBudget','administrative_budgets.id')
					->where('year',$year)
					->where('enterprise_id',$enterprise)
					->where('department_id',$department)
					->where('project_id',$project)
					->groupBy('account_id')
					->get();

				
				$result = collect($result)->groupBy('idAccount');
				if (count($accounts)>0)
				{
					Excel::create('Presupuesto para '.$nameEnterprise.'', function($excel) use ($enterprise,$project,$department,$year,$accounts,$titles,$result)
					{
						$excel->sheet('Presupuesto',function($sheet) use ($enterprise,$project,$department,$year,$accounts,$titles,$result)
						{
							$sheet->setWidth(array(
								'A'	=> 5,
								'B'	=> 80,
							));
							$sheet->setAutoSize(true);
							$sheet->setColumnFormat(array(
									'A' => '@',
								));
							$sheet->setStyle(
									[
										'font' => 
										[
											'name'	=>  'Calibri',
											'size'	=>  12,
											'color'	=> ['argb' => 'EB2B02'],
										]
									]);
							$sheet->mergeCells('A1:BB1');
							$sheet->cell('A1:BB1', function($cells) 
							{
								$cells->setBackground('#1F4E79');
								$cells->setFontColor('#ffffff');
								$cells->setFontWeight('bold');
								$cells->setAlignment('center');
								$cells->setFont(['family' => 'Calibri','size' => '22','bold' => true]);
							});
							$sheet->cell('A2:BB2', function($cells) 
							{
								$cells->setFontColor('#000000');
								$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
							});
							$sheet->row(1,['PRESUPUESTO POR CUENTA']);
							$sheet->row(2,$titles);
							$init = 3;
							foreach ($accounts as $acc)
							{
								$row	= [];
								$row[]	= $acc->idAccAcc;
								$row[]	= $acc->fullClasificacionName();
								
								if (count($result)>0 && $result[$acc->idAccAcc] != "") 
								{
									for ($i=1; $i < 53; $i++) 
									{ 
										$amountWeek	= 'amountWeek'.$i;
										$amounts	= $result[$acc->idAccAcc];
										$row[]		= $amounts[0][$amountWeek];
									}
								}
								else
								{
									for ($i=1; $i < 53; $i++) 
									{ 
										$row[] = 0;
									}
								}

								$sheet->appendRow($row);
								$init++;
							}
							$init--;
							$sheet->cell('A3:BB'.$init, function($cells) 
							{
								$cells->setFontColor('#000000');
								$cells->setFont(array('family' => 'Calibri','size' => '14'));
							});
						});
					})->export('xlsx');
				}
			}
			else
			{
				if ($request->file('path')->getClientOriginalExtension() == 'csv') 
				{
					$name = '/docs/budgets/'.$request->realPath;
					$path = \Storage::disk('public')->path($name);

					$csvData	= array();
					if (($handle = fopen($path, "r")) !== false)
					{
						$first	= true;
						while (($data = fgetcsv($handle, 1000, $request->separator)) !== false)
						{
							if($first)
							{
								$data[1]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[1]);
								$first		= false;
							}
							$csvData[]	= $data;
						}
						fclose($handle);
					}
					array_walk($csvData, function(&$a) use ($csvData)
					{
						$a = array_combine($csvData[1], $a);
					});
					array_shift($csvData);
					array_shift($csvData);
					
					$checkAccounts	= collect($csvData)->groupBy('id');
					$accounts		= App\Account::orderNumber()->where('idEnterprise',$request->enterprise_upload)
					->where('selectable',1)
					->get();		

					$countCheck = 0;
					foreach ($accounts as $account) 
					{
						if (!isset($checkAccounts[$account->idAccAcc])) 
						{
							$countCheck++;
						}
					}

					if ($countCheck == 0) 
					{
						$weeks = '';
						$errors = 0;
						for ($i=1; $i < 53; $i++) 
						{ 
							$week	= $i;
							$year	= $request->year_upload;

							$checkWeekYear 	= App\AdministrativeBudget::where('year',$year)->where('weekOfYear',$week)->where('enterprise_id',$request->enterprise_upload)->where('department_id',$request->department_upload)->where('project_id',$request->project_upload)->count();
							if ($checkWeekYear == 0) 
							{
								$initRange	= App\Http\Controllers\AdministracionPresupuestosController::initDate($year,$week);
								$endRange	= App\Http\Controllers\AdministracionPresupuestosController::endDate($year,$week);

								$budget					= new App\AdministrativeBudget();
								$budget->enterprise_id	= $request->enterprise_upload;
								$budget->department_id	= $request->department_upload;
								$budget->project_id		= $request->project_upload;
								$budget->path 			= $request->realPath;
								$budget->initRange 		= $initRange;
								$budget->endRange 		= $endRange;
								$budget->weekOfYear 	= $i;
								$budget->year 			= $year;
								$budget->alert_percent 	= $request->alert_percent;
								$budget->users_id 		= Auth::user()->id;
								$budget->save();

								$idAdministrativeBudget = $budget->id;

								foreach ($csvData as $art) 
								{
									if ((isset($art['id']) && trim($art['id'])!="") && (isset($art['cuenta']) && trim($art['cuenta'])!="") && (isset($art['monto_sem_'.$i]) && trim($art['monto_sem_'.$i])!=""))
									{
										$detail							= new App\AdministrativeBudgetDetail();
										$detail->account_id				= $art['id'];
										$detail->account				= $art['cuenta'];
										$detail->amount					= $art['monto_sem_'.$i];
										$detail->idAdministrativeBudget	= $idAdministrativeBudget;
										$detail->status 				= 0;
										$detail->amount_spent 			= 0;
										$detail->alert_percent 			= $request->alert_percent;
										$detail->save();
									}
									else
									{
										$budget->delete();
										break;
									}
								}
							}
							else
							{
								$errors++;
								$weeks .= 'semana '.$i.', ';
							}
						}
						if ($errors > 0) 
						{
							if ($weeks != "") 
							{
								$alert = "swal('','Se han cargado los presupuestos a excepción de los presupuestos para la ".$weeks." debido a que ya existen.','info');";
							}
						}
						else
						{
							$alert = "swal('','Presupuesto creado exitosamente','success');";
						}
						return redirect('/administration/budgets/administration')->with('alert',$alert);
					}
					else
					{
						$alert = "swal('','Las cuentas no pertecen a la empresa seleccionada.','error');";
						return redirect()->back()->with('alert',$alert);
					}
				}
				else
				{
					$alert = "swal('','El archivo debe ser .csv','info');";
					return redirect()->back()->with('alert',$alert);
				}
			}
		}
	}

	public function uploadFile(Request $request)
	{
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
					\Storage::disk('public')->delete('/docs/budgets/'.$request->realPath[$i]);
				}
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_budget.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/docs/budgets/'.$name;

				\Storage::disk('public')->put($destinity,mb_convert_encoding(\File::get($request->path),'UTF-8','UTF-8,ISO-8859-1,WINDOWS-1251'));

				$response['error']		= 'DONE';
				$response['path']		= $name;
				$response['message']	= '';
				$response['extention']	= strtolower($extention);

			}
			return Response($response);
		}
	}

	public function downloadBudget(App\AdministrativeBudget $budget)
	{
		if (Auth::user()->module->where('id',243)->count()>0) 
		{
			$nameEnterprise	= $budget->enterprise->name;
			$accounts		= $budget->detail;
			$week 			= $budget->weekOfYear;

			if (count($accounts)>0)
			{
				Excel::create('Presupuesto de '.$nameEnterprise.'', function($excel) use ($accounts,$week)
				{
					$excel->sheet('Presupuesto',function($sheet) use ($accounts,$week)
					{
						$sheet->setWidth(array(
							'A'	=> 10,
							'B'	=> 70,
							'C' => 15
						));
						$sheet->setColumnFormat(array(
								'A' => '0',
								'B' => '@',
								'C' => '0'
							));
						$sheet->setStyle(
								[
									'font' => 
									[
										'name'	=>  'Calibri',
										'size'	=>  12,
										'color'	=> ['argb' => 'EB2B02'],
									]
								]);
						$sheet->mergeCells('A1:C1');
						$sheet->cell('A1:C1', function($cells) 
						{
							$cells->setBackground('#1F4E79');
							$cells->setFontColor('#ffffff');
							$cells->setFontWeight('bold');
							$cells->setAlignment('center');
							$cells->setFont(['family' => 'Calibri','size' => '22','bold' => true]);
						});
						$sheet->cell('A2:C2', function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
						});
						$sheet->row(1,['PRESUPUESTO DE LA SEMANA '.$week]);
						$sheet->row(2,['id','cuenta','monto']);
						$init = 3;
						foreach ($accounts as $acc)
						{
							$row	= [];
							$row[] 	= $acc->id;
							$row[]	= $acc->account;
							$row[]	= $acc->amount;
							$sheet->appendRow($row);
							$init++;
						}
						$init--;
						$sheet->cell('A3:C'.$init, function($cells) 
						{
							$cells->setFontColor('#000000');
							$cells->setFont(array('family' => 'Calibri','size' => '14'));
						});
					});
				})->export('xlsx');
			}
		}
		else
		{
			return redirect('/error');
		}
	}

	public function updateFromFile(App\AdministrativeBudget $budget,Request $request)
	{
		if($request->file('path'))
		{
			if ($request->file('path')->getClientOriginalExtension() == 'csv') 
			{
				$name = '/docs/budgets/'.$request->realPath;
				$path = \Storage::disk('public')->path($name);

				$csvData	= array();
				if (($handle = fopen($path, "r")) !== false)
				{
					$first	= true;
					while (($data = fgetcsv($handle, 1000, $request->separator)) !== false)
					{
						if($first)
						{
							$data[1]	= preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[1]);
							$first		= false;
						}
						$csvData[]	= $data;
					}
					fclose($handle);
				}
				
				array_walk($csvData, function(&$a) use ($csvData)
				{
					$a = array_combine($csvData[1], $a);
				});
				array_shift($csvData);
				array_shift($csvData);

				$checkAccounts	= collect($csvData)->groupBy('id');

				$enterprise_id 	= $budget->enterprise_id;
				$accounts		= $budget->detail;

				$countCheck = 0;
				foreach ($accounts as $account) 
				{
					if (!isset($checkAccounts[$account->id])) 
					{
						$countCheck++;
					}
				}

				if ($countCheck == 0) 
				{
					$budget->alert_percent 	= $request->alert_percent;
					$budget->save();

					$errors		= 0;
					$message	= '';
					foreach ($csvData as $art) 
					{
						if ((isset($art['id']) && trim($art['id'])!="") && (isset($art['cuenta']) && trim($art['cuenta'])!="") && (isset($art['monto']) && trim($art['monto'])!=""))
						{
							$detail				= App\AdministrativeBudgetDetail::find($art['id']);
							$detail->account	= $art['cuenta'];
							$detail->amount		= $art['monto'];
							$detail->save();
						}
						else
						{
							$errors++;
							$message .= 'cuenta '.$art['cuenta'].', ';
						}
					}

					if ($errors > 0) 
					{
						$alert = "swal('','Se han cargado los presupuestos a excepción de los presupuestos para la ".$message.". Por favor revise que estén bien escritas.','info');";
					}
					else
					{
						$alert = "swal('','Presupuesto creado exitosamente','success');";
					}
					
					return redirect()->route('budget.administration.edit',['budget'=>$budget])->with('alert',$alert);
				}
				else
				{
					$alert = "swal('','Los ID no pertecen al presupuesto seleccionado.','error');";
					return redirect()->back()->with('alert',$alert);
				}
			}
		}
		else
		{
			$alert = "swal('','No se cargó ningún archivo.','error');";
			return redirect()->route('budget.administration.edit',['budget'=>$budget])->with('alert',$alert);
		}
	}

	public function initDate($year,$week)
	{
		$week = str_pad($week, 2, "0",STR_PAD_LEFT);
		return date('Y-m-d',strtotime($year.'W'.$week.'-1')); 
	}

	public function endDate($year,$week)
	{
		$week = str_pad($week, 2, "0",STR_PAD_LEFT);
		return date('Y-m-d',strtotime($year.'W'.$week.'-7')); 
	}

	public function updateBudgets()
	{
		$today      = Carbon::now();
		$weekOfYear = $today->weekOfYear;
		$year       = $today->year;
		$budgets    = App\AdministrativeBudget::where('year',date('Y'))->where('weekOfYear',date('W'))->get();

		if (count($budgets)>0) 
		{
			foreach ($budgets as $budget) 
			{
				foreach ($budget->detail as $detail) 
				{
					$requests 	= App\RequestModel::selectRaw(
						'
							ROUND(
								IF(request_models.kind = 1, purchases.amount, 
									IF(request_models.kind = 8, resource_details.amount, 
										IF(request_models.kind = 9, refund_details.sAmount, 
											IF(request_models.kind = 18, finances.amount, 
												IF(request_models.kind = 17, purchase_records.total, 
													IF(nominas.idCatTypePayroll = "001" AND nomina_employees.fiscal = 1, salaries.netIncome, 
														IF(nominas.idCatTypePayroll = "002" AND nomina_employees.fiscal = 1, bonuses.netIncome, 
															IF((nominas.idCatTypePayroll = "003" OR nominas.idCatTypePayroll = "004") AND nomina_employees.fiscal = 1, liquidations.netIncome, 
																IF(nominas.idCatTypePayroll = "005" AND nomina_employees.fiscal = 1, vacation_premia.netIncome, 
																	IF(nominas.idCatTypePayroll = "006" AND nomina_employees.fiscal = 1, profit_sharings.netIncome, nomina_employee_n_fs.amount
																	)
																)
															)
														)
													)
												)
											)
										)
									)
								)
								,2) AS amountTotal'
						)
						->leftJoin('purchases','request_models.folio','=','purchases.idFolio')
						->leftJoin('resources','request_models.folio','=','resources.idFolio')
						->leftJoin('resource_details','resources.idresource','=','resource_details.idresource')
						->leftJoin('accounts AS resAcc','resource_details.idAccAccR','=','resAcc.idAccAcc')
						->leftJoin('refunds','request_models.folio','=','refunds.idFolio')
						->leftJoin('refund_details','refunds.idRefund','=','refund_details.idRefund')
						->leftJoin('accounts AS refAcc','refund_details.idAccountR','=','refAcc.idAccAcc')
						->leftJoin('purchase_records','request_models.folio','=','purchase_records.idFolio')
						->leftJoin('purchase_record_details','purchase_records.id','=','purchase_record_details.idPurchaseRecord')
						->leftJoin('nominas','request_models.folio','=','nominas.idFolio')
						->leftJoin('nomina_employees','nominas.idnomina','=','nomina_employees.idnomina')
						->leftJoin('nomina_employee_n_fs','nomina_employees.idnominaEmployee','=','nomina_employee_n_fs.idnominaEmployee')
						->leftJoin('liquidations','nomina_employees.idnominaEmployee','=','liquidations.idnominaEmployee')
						->leftJoin('bonuses','nomina_employees.idnominaEmployee','=','bonuses.idnominaEmployee')
						->leftJoin('vacation_premia','nomina_employees.idnominaEmployee','=','vacation_premia.idnominaEmployee')
						->leftJoin('salaries','nomina_employees.idnominaEmployee','=','salaries.idnominaEmployee')
						->leftJoin('profit_sharings','nomina_employees.idnominaEmployee','=','profit_sharings.idnominaEmployee')
						->leftJoin('real_employees','nomina_employees.idrealEmployee','=','real_employees.id')
						->leftJoin('worker_datas','nomina_employees.idworkingData','=','worker_datas.id')
						->leftJoin('enterprises AS nomEnt','worker_datas.enterprise','=','nomEnt.id')
						->leftJoin('accounts as nomAccount','worker_datas.account','=','nomAccount.idAccAcc')
						->leftJoin('projects AS nomProy','worker_datas.project','=','nomProy.idproyect')
						->leftJoin('finances','request_models.folio','=','finances.idFolio')
						->whereIn('request_models.kind',[1,8,9,16,17,18])
						->whereIn('request_models.status',[2,3,4,5,10,11,12,18])
						->whereBetween('fDate',[''.$budget->initRange.' '.date('00:00:00').'',''.$budget->endRange.' '.date('23:59:59').''])
						->where( function($query) use ($budget) 
						{
							$query->where('request_models.idEnterprise',$budget->enterprise_id)->orWhere('worker_datas.enterprise',$budget->enterprise_id);
						})
						->where( function($query) use ($budget) 
						{
							$query->where('request_models.idProject',$budget->project_id)->orWhere('worker_datas.project',$budget->project_id);
						})
						->where( function($query) use ($budget) 
						{
							$query->where('request_models.idDepartment',$budget->department_id);
						})
						->where( function($query) use ($detail) 
						{
							$query->where('request_models.account',$detail->account_id)->orWhere('worker_datas.account',$detail->account_id)->orWhere('refund_details.idAccount',$detail->account_id)->orWhere('resource_details.idAccAcc',$detail->account_id);
						})
						->get();
					$amountSpent  = $requests->sum('amountTotal');
					$amountBudget = $detail->amount;
					$alertPercent = $detail->alert_percent;
					if ($amountBudget == 0 && $amountSpent > 0) 
					{
						$detail->status = 1;
					}
					else if($amountBudget > 0 && $amountSpent > 0)
					{
						$percentSpent = ($amountSpent*100)/($amountBudget);
						if ($percentSpent>=$alertPercent) 
						{
							$detail->status = 1;
						}
					}
					$detail->amount_spent	= $amountSpent;
					$detail->save();
				}
			}
		}
		return 1;
	}
}
