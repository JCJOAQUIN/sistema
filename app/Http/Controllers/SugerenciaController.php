<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\Suggestions;
use Alert;
use Auth;
use Excel;


class SugerenciaController extends Controller
{
	private $module_id = 126;

	public function index()
	{
		$data   = App\Module::find($this->module_id);
		return view('sugerencias.index',
			[
				'id'       => $data['id'],
				'title'    => $data['name'],
				'details'  => $data['details'],
				'child_id' => $this->module_id,
			]);
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$suggestion             = new App\Suggestions();
			$suggestion->subject    = $request->subject;
			$suggestion->suggestion = $request->suggestion;
			$suggestion->idUsers    = Auth::user()->id;
			$suggestion->save();

			$alert = "swal('', 'Gracias por su comentario.', 'success');";
			return redirect('/suggestions')->with('alert',$alert);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function view(Request $request)
	{
		$data = App\Module::find($this->module_id);
		if (Auth::user()->id==43 || Auth::user()->id==11 || Auth::user()->id==16) 
		{
			$suggestions = Suggestions::orderBy('date','DESC')
							->paginate('10');
			return view('sugerencias.ver',
			[
				'id'        	=> $data['id'],
				'title'     	=> $data['name'],
				'details'   	=> $data['details'],
				'suggestions' 	=> $suggestions,
			]);
		}
		else
		{
			return redirect('/error');
		}
	}

	public function export(Request $request)
	{
		$data   = App\Module::find($this->module_id);
		if (Auth::user()->id==43 || Auth::user()->id==11 || Auth::user()->id==16) 
		{
			Excel::create('Reporte de Sugerencias', function($excel)
			{
				$excel->sheet('Sugerencias',function($sheet)
				{
					$sheet->setStyle(array(
						'font' => array(
							'name'  => 'Calibri',
							'size'  => 12
						)
					));
					$sheet->mergeCells('A1:C1');
					$sheet->cell('A1:C1', function($cells)
					{
						$cells->setBackground('#000000');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A2:C2', function($cells)
					{
						$cells->setBackground('#104f64');
						$cells->setFontColor('#ffffff');
					});
					$sheet->cell('A1:C2', function($cells)
					{
						$cells->setFontWeight('bold');
						$cells->setAlignment('center');
						$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
					});
					$sheet->row(1,['Reporte de Sugerencias']);
					$sheet->row(2,['Asunto','Sugerencia/Comentario','Fecha']);
					$suggestions   = App\Suggestions::all();
					foreach ($suggestions as $suggestion)
					{
						$row    = [];
						$row[]  = $suggestion->subject;
						$row[]  = $suggestion->suggestion;
						$row[]  = $suggestion->date;
						$sheet->appendRow($row);
					}
				});
			})->export('xls');
		}
		else
		{
			return redirect('/error');
		}
	}
}
