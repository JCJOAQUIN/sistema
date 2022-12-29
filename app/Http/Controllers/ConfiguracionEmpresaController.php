<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use App;
use Alert;
use Excel;
use Auth;
use Lang;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;

class ConfiguracionEmpresaController extends Controller
{
	private $module_id = 8;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			return view('layouts.child_module',
				[
					'id'		=> $data['father'],
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

	public function create()
	{
		if(Auth::user()->module->where('id',16)->count()>0)
		{
			$data	= App\Module::find($this->module_id);
			$data			= App\Module::find($this->module_id);
			$roles			= App\Role::where('status','ACTIVE')->get();
			$enterprises	= App\Enterprise::where('status','ACTIVE')->get();
			$areas			= App\Area::where('status','ACTIVE')->get();
			$departments	= App\Department::where('status','ACTIVE')->get();
			$banks			= App\Banks::all();
			$kindbanks		= App\KindOfBanks::all();
			return view('configuracion.empresa.alta',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 16,
					'roles' 		=> $roles,
					'enterprises' 	=> $enterprises,
					'areas'			=> $areas,
					'departments'	=> $departments,
					'banks'			=> $banks,
					'kindbanks'		=> $kindbanks
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function validation(Request $request)
	{
		if($request->name == '')
		{
			$response = array(
				'valid'		=> false,
				'message'	=> 'El campo es requerido.'
			);
		}
		else
		{
			$exist = App\Enterprise::where('name',$request->name)
				->where(function($q) use($request)
				{
					if(isset($request->oldEnterprise))
					{
						$q->where('id','!=',$request->oldEnterprise);
					}
				})
				->get();
			if(count($exist) > 0)
			{
				$response = array(
					'valid'   => false,
					'message' => 'La empresa ya se encuentra registrada.'
				);
			}
			else
			{
				$response = array('valid' => true);
			}
		}
		return Response($response);
	}

	public function rfcValidation(Request $request)
	{
		if($request->ajax())
		{
			$response = array(
				'valid'		=> false,
				'class' 	=> 'error',
				'message'	=> 'El campo es requerido.'
			);
			if(isset($request->rfc))
			{
				if(preg_match("/^([A-Z,Ñ,&]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[A-Z|\d]{3}){0,1}$/i", $request->rfc) || preg_match("/^XAXX1[0-9]{8}$/i", $request->rfc))
				{
					$rfc = App\Enterprise::where('rfc','LIKE',$request->rfc)
						->where(function($q) use($request)
						{
							if(isset($request->oldEnterprise))
							{
								$q->where('id','!=',$request->oldEnterprise);
							}
						})
						->count();
					if($rfc > 0)
					{
						$response = array(
							'valid'		=> false,
							'class' 	=> 'error',
							'message'	=> 'El RFC ya se encuentra registrado.'
						);
					}
					else
					{
						$response = array('valid' => true,'class'=>'valid','message' => '');
					}
				}
				else
				{
					$response = array(
						'valid'		=> false,
						'class' 	=> 'error',
						'message'	=> 'El RFC debe ser válido.'
					);
				}
			}
			return Response($response);
		}
	}

	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			if($request->file('path')->isValid())
			{
				$enterprise = App\Enterprise::create($request->all());
				if(isset($request->rp_id))
				{
					foreach ($request->rp_id as $key => $value)
					{
						$er						= new App\EmployerRegister();
						$er->enterprise_id		= $enterprise->id;
						$er->employer_register	= $request->employer_register[$key];
						$er->risk_number		= $request->risk_number[$key];
						$er->position_risk_id	= $request->position_risk[$key];
						$er->save();
					}
				}
				$alert    = "swal('', '".Lang::get("messages.record_created")."', 'success');";
				return redirect('configuration/enterprise')->with('alert',$alert);
			}
			else
			{
				$alert	= "swal('', '".Lang::get("messages.file_upload_error")."', 'error');";
				return redirect('configuration/enterprise')->with('alert',$alert);
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function edit($id)
	{
		if(Auth::user()->module->where('id',17)->count()>0)
		{
			$data		= App\Module::find($this->module_id);
			$enterprise	= App\Enterprise::find($id);
			if ($enterprise != "")
			{
				return view('configuracion.empresa.cambio',
					[
						'id' 		=> $data['father'],
						'title' 	=> $data['name'],
						'details' 	=> $data['details'],
						'child_id'	=> $this->module_id,
						'option_id' => 17,
						'enterprise'=> $enterprise
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function update(Request $request, $id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data       = App\Module::find($this->module_id);
			$enterprise = App\Enterprise::find($id)
				->fill($request->all());
			$enterprise->save();
			$noRemoveArr = array();
			if(isset($request->rp_id))
			{
				$noRemoveArr	= $request->rp_id;
			}
			$delete	= App\EmployerRegister::whereNotIn('id',$noRemoveArr)->where('enterprise_id',$id);
			foreach ($delete->get() as $del)
			{
				$del->delete();
			}
			if(isset($request->rp_id))
			{
				foreach ($request->rp_id as $key => $value)
				{
					if($value == 'x')
					{
						$er						= new App\EmployerRegister();
						$er->enterprise_id		= $id;
						$er->employer_register	= $request->employer_register[$key];
						$er->risk_number		= $request->risk_number[$key];
						$er->position_risk_id	= $request->position_risk[$key];
						$er->save();
					}
				}
			}
			$alert = "swal('', '".Lang::get("messages.record_updated")."', 'success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function inactive($id)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data		 		= App\Module::find($this->module_id);
			$enterprise	 		= App\Enterprise::find($id);
			$enterprise->status = 'INACTIVE';
			$enterprise->save();
			$alert	=	"swal('','Empresa suspendida exitosamente','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			
		}
	}

	public function reactive($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data 				= App\Module::find($this->module_id);
			$enterprise 		= App\Enterprise::find($id);
			$enterprise->status = 'ACTIVE';
			$enterprise->save();
			$alert	=	"swal('','Empresa reactivada exitosamente','success');";
			return back()->with('alert',$alert);
		}
		else
		{
			return 0;
		}
	}

	public function search(Request $request)
	{	
		if(Auth::user()->module->where('id',17)->count()>0)
		{
			$search      = $request->search;
			$enterprises = App\Enterprise::where(function($sql) use ($search)
			{
				if($search !=' ')
				{
					$sql->where('name','LIKE','%'.$search.'%');
				}
			})
			->orderBy('created_at','DESC')
			->paginate(10);
			$data			= App\Module::find($this->module_id);
			return response (
				view('configuracion.empresa.busqueda',
				[
					'id'			=> $data['father'],
					'title'			=> $data['name'],
					'details'		=> $data['details'],
					'child_id'		=> $this->module_id,
					'option_id'		=> 17,
					'enterprises'	=> $enterprises,
					'search'		=> $search,
				])
			)->cookie(
				"urlSearch", storeUrlCookie(17), 2880
			);
		}
		else
		{
			return redirect('/');
		}
	}

	//Exportar a Excel 
	public function export(Request $request){
		if(Auth::user()->module->where('id',17)->count()>0)
		{
			$search			= $request->search;
			$enterprises	= DB::table('enterprises')->selectRaw(
				'
					enterprises.id, 
					enterprises.name,
					enterprises.rfc,
					enterprises.address,
					enterprises.number,
					enterprises.colony,
					enterprises.postalCode,
					enterprises.city,
					enterprises.phone,
					states.description as stateName,
					cat_tax_regimes.description as taxRegimeName,
					employer_registers.employer_register,
					employer_registers.risk_number,
					cat_position_risks.description as positionRiskName
				')
				->leftJoin('states', 'states.idstate', 'enterprises.state_idstate')
				->leftJoin('cat_tax_regimes', 'cat_tax_regimes.taxRegime', 'enterprises.taxRegime')
				->leftJoin('employer_registers', 'employer_registers.enterprise_id', 'enterprises.id')
				->leftJoin('cat_position_risks', 'cat_position_risks.id', 'employer_registers.position_risk_id')
				->where(function($sql) use ($search)
				{	
					if($search !=' ') 
						$sql->where('enterprises.name','LIKE','%'.$search.'%');	
				})
				->get();

			if(count($enterprises)==0 || is_null($enterprises))
			{
				return redirect()->back()->with('alert',"swal('', '".Lang::get("messages.result_not_found")."', 'error');");
			}
			$defaultStyle   = (new StyleBuilder())->setFontName('Calibri')->setFontSize(12)->build();
			$currencyFormat = (new StyleBuilder())->setFormat('_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)')->build();
			$rowDark        = (new StyleBuilder())->setBackgroundColor('F0F0F0')->setCellAlignment(CellAlignment::LEFT)->build();
			$mhStyleCol1    = (new StyleBuilder())->setBackgroundColor('54a935')->setFontColor(Color::WHITE)->build();
			$mhStyleCol2    = (new StyleBuilder())->setBackgroundColor('f68031')->setFontColor(Color::WHITE)->build();
			$alignment		= (new StyleBuilder())->setCellAlignment(CellAlignment::LEFT)->build();
			$writer         = WriterEntityFactory::createXLSXWriter();
			$writer->setDefaultRowStyle($defaultStyle)->openToBrowser('Reporte-Empresas.xlsx');
			$writer->getCurrentSheet()->setName('Empresas registradas');

			$headers = ['EMPRESAS','','','','','','','','','','','Registro Patronal','',''];
			$tempHeaders      = [];
			foreach($headers as $k => $mh)
			{
				if($k>=11)
				{
					$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol2);
				}
				else
				{
					$tempHeaders[] = WriterEntityFactory::createCell($mh,$mhStyleCol1);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempHeaders);
			$writer->addRow($rowFromValues);

			$subHeader    = ['ID','Nombre', 'RFC', 'Dirección', 'Número', 'Colonia', 'Código-Postal', 'Ciudad', 'Teléfono', 'Estado', 'Regimen Fiscal','Registro', 'Prima de riesgo', 'Riesgo de puesto'];
			$tempSubHeader = [];
			foreach($subHeader as $k => $sh)
			{
				if($k>=11)
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol2);
				}
				else
				{
					$tempSubHeader[] = WriterEntityFactory::createCell($sh,$mhStyleCol1);
				}
			}
			$rowFromValues = WriterEntityFactory::createRow($tempSubHeader);
			$writer->addRow($rowFromValues);

			$tempId     = '';
			$kindRow       = true;
			foreach($enterprises as $request)
			{
				if($tempId != $request->id)
				{
					$tempId = $request->id;
					$kindRow = !$kindRow;
				}
				else
				{
					$request->id			= null;
					$request->name			= "";
					$request->rfc			= "";
					$request->address		= "";
					$request->number		= "";
					$request->colony		= "";
					$request->postalCode	= "";
					$request->city			= "";
					$request->phone			= "";
					$request->stateName		= "";
					$request->taxRegimeName	= "";
				}
				$tmpArr = [];
				foreach($request as $k => $r)
				{
					$tmpArr[] = WriterEntityFactory::createCell($r);
				}
				if($kindRow)
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr,$rowDark);
				}
				else
				{
					$rowFromValues = WriterEntityFactory::createRow($tmpArr, $alignment);
				}
				$writer->addRow($rowFromValues);
			}
			return $writer->close();
		}else
		{
			return redirect('error');
		}
	}
	/*public function getData(Request $request)
	{

		if($request->ajax())
		{
			$output 		= "";
			$header 		= "";
			$footer 		= "";
			$enterprises  	= App\Enterprise::where('name','LIKE','%'.$request->search.'%')
								->get();
			$countUsers 	= count($enterprises);
				if ($countUsers >= 1)
				{
					$header = "<table id='table' class='table table-striped'><thead class='thead-dark'><tr><th>ID</th><th>Nombre</th><th>RFC</th><th>Acci&oacute;n</th></tr></thead><tbody>";
					$footer = "</tbody></table>";
					foreach ($enterprises as $enterprise)
					{
						$output.=	"<tr>".
								 	"<td>".$enterprise->id."</td>".
									"<td>".$enterprise->name."</td>".
									"<td>".$enterprise->rfc."</td>".
									"<td>	";

						if ($enterprise->status == 'ACTIVE')
						{
							$output .= "<a title='Suspender Empresa' href="."'".url::route('enterprise.inactive',$enterprise->id)."'"." class='enterprise-delete btn btn-red'><span class='icon-bin'></span></a>";
						}
						if ($enterprise->status == 'INACTIVE')
						{
							$output .= "<a title='Reactivar Empresa' href="."'".url::route('enterprise.reactive',$enterprise->id)."'"." class='enterprise-reactive btn btn-green'><span class='icon-checkmark'></span></a>";
						}

						$output.= 	"</td>".
									"</tr>";
					}
					return Response($header.$output.$footer);
				}
				else
				{
					$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
					return Response($notfound);
				}
		}
	}*/
}
